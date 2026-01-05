/*
 * Copyright (c) 2018-2025 SignalWire, Inc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

#include "libks/ks.h"

#ifdef _MSC_VER
/* warning C4706: assignment within conditional expression*/
#pragma warning(disable: 4706)
#endif

struct ks_tls_s {
	uint8_t down;
	ks_socket_t sock;
	ks_tls_type_t type;

	SSL *ssl;
	SSL_CTX *ssl_ctx;
	ks_bool_t ssl_io_error; /* This flag indicates that we should not attempt to shut down the connection with SSL_shutdown. */
	ks_bool_t secure_established;

	ks_tls_init_callback_t init_callback;

	char *req_host;
	char peer_cipher_name[128];
};

struct ks_tls_shared_ctx_s {
	SSL_CTX *ssl_ctx;
};

#define KS_TLS_SHUTDOWN_BUFLEN 1024

static const uint32_t KS_TLS_WRITE_RETRY_MS = 10;
static const uint32_t KS_TLS_READ_RETRY_MS = 10;
static const uint32_t KS_TLS_INIT_RETRY_MS = 10;
static const uint32_t KS_TLS_SHUTDOWN_RETRY_MS = 10;

static const uint32_t KS_TLS_DEFAULT_CONN_TIMEOUT_MS = 5000;
static const uint32_t KS_TLS_DEFAULT_INIT_TIMEOUT_MS = 5000;
static const uint32_t KS_TLS_SHUTDOWN_TIMEOUT_MS = 2000;

static const char *KS_TLS_DEFAULT_CIPHER_LIST = "HIGH:!DSS:!aNULL@STRENGTH";

static int log_ssl_errors(const char *err, size_t len, void *u)
{
	ks_log(KS_LOG_ERROR, "  %s", err);	/* ssl adds a \n */

	return 0;
}

/* Returns SUCCESS only when all `bytes` written. */
KS_DECLARE(ks_status_t) ks_tls_write(ks_tls_t *ktls, const void *data, ks_size_t *bytes)
{
	size_t written;

	if (!ktls || !data || !bytes || !*bytes) {
		ks_log(KS_LOG_ERROR, "Write: Invalid (empty) parameter!\n");

		return KS_STATUS_ARG_NULL;
	}

	/* We don't use SSL_MODE_ENABLE_PARTIAL_WRITE, so `written` can't be less than `bytes`. */
	ERR_clear_error();

	if (!SSL_write_ex(ktls->ssl, data, *bytes, &written)) {
		int ssl_err = SSL_get_error(ktls->ssl, 0);

		if (KS_SSL_ERROR_WANT_READ_WRITE(ssl_err)) {
			return KS_STATUS_RETRY;
		}

		if (KS_SSL_IO_ERROR(ssl_err)) {
			ktls->ssl_io_error = KS_TRUE;
		}

		return KS_STATUS_FAIL;
	}

	*bytes = written;

	return KS_STATUS_SUCCESS;
}

/* `timout_ms` is a total max time spent between retries, so it isn't precise. 0 - infinite.*/
KS_DECLARE(ks_status_t) ks_tls_write_timeout(ks_tls_t *ktls, void *data, ks_size_t *bytes, uint32_t timeout_ms)
{
	uint32_t num_retries = timeout_ms / KS_TLS_WRITE_RETRY_MS;
	uint32_t retry = 0;

	for (;;) {
		/* We must always retry with the same `data` and `bytes` values. */
		ks_status_t status = ks_tls_write(ktls, data, bytes);

		if (status != KS_STATUS_RETRY) {
			return status;
		}

		if (timeout_ms && (++retry > num_retries)) {
			break;
		}

		ks_sleep_ms(KS_TLS_WRITE_RETRY_MS);
	}

	return KS_STATUS_TIMEOUT;
}

KS_DECLARE(ks_status_t) ks_tls_read(ks_tls_t *ktls, void *data, ks_size_t *bytes)
{
	size_t readbytes;

	if (!ktls || !data || !bytes || !*bytes) {
		ks_log(KS_LOG_ERROR, "Read: Invalid (empty) parameter! [%p/%p/%p]\n", ktls, data, bytes);

		return KS_STATUS_ARG_NULL;
	}

	ERR_clear_error();

	if (!SSL_read_ex(ktls->ssl, data, (size_t)*bytes, &readbytes)) {
		int ssl_err = SSL_get_error(ktls->ssl, 0);

		if (ssl_err == SSL_ERROR_ZERO_RETURN) {
			return KS_STATUS_BREAK;
		} else if (KS_SSL_ERROR_WANT_READ_WRITE(ssl_err)) {
			return KS_STATUS_RETRY;
		}

		if (KS_SSL_IO_ERROR(ssl_err)) {
			ktls->ssl_io_error = KS_TRUE;
		}

		ks_log(KS_LOG_ERROR, "Failed to READ from connection with ssl error code: %d (%s)\n", ssl_err, ERR_error_string(ssl_err, NULL));

		return KS_STATUS_FAIL;
	}

	/* 1 or more bytes read */
	*bytes = readbytes;

	return KS_STATUS_SUCCESS;
}

/* `timout_ms` is a total max time spent between retries, so it isn't precise. 0 - infinite. */
KS_DECLARE(ks_status_t) ks_tls_read_timeout(ks_tls_t *ktls, void *data, ks_size_t *bytes, uint32_t timeout_ms)
{
	uint32_t num_retries = timeout_ms / KS_TLS_READ_RETRY_MS;
	uint32_t retry = 0;

	for (;;) {
		ks_status_t status = ks_tls_read(ktls, data, bytes);

		if (status != KS_STATUS_RETRY) {
			return status;
		}

		if (timeout_ms && (++retry > num_retries)) {
			break;
		}

		ks_sleep_ms(KS_TLS_READ_RETRY_MS);
	}

	return KS_STATUS_TIMEOUT;
}


static void ks_tls_close(ks_tls_t *ktls)
{
	char shutdown_buffer[KS_TLS_SHUTDOWN_BUFLEN] = {0};
	uint32_t num_retries = KS_TLS_SHUTDOWN_TIMEOUT_MS / KS_TLS_SHUTDOWN_RETRY_MS;
	uint32_t retry = 0;

	if (ktls->down) {
		return;
	}

	ktls->down = 1;

	if (ktls->sock == KS_SOCK_INVALID) {
		return;
	}

	/* ks_tls_init() may call ks_tls_destroy() -> ks_tls_close() on error, so we may not have ssl here. */
	if (!ktls->ssl) {
		goto close_end;
	}

	/* First invocation of SSL_shutdown() would normally return 0 and just try to send SSL protocol close request (close_notify_alert).
	   It is recommended to do bidirectional shutdown and also to read all the remaining data sent by the client
	   before it indicates EOF (SSL_ERROR_ZERO_RETURN). To avoid stuck in this process in case of dead peers,
	   we wait for KS_TLS_SHUTDOWN_TIMEOUT_MS time before we give up.
	*/
	int code = 0, rcode = 0;
	int ssl_error = 0;

	/* SSL layer was never established or underlying IO error occured */
	if (!ktls->secure_established || ktls->ssl_io_error) {
		ks_log(KS_LOG_DEBUG, "Can't shutdown TLS. Secure is not established [%d] or TLS IO error [%d].\n", ktls->secure_established, ktls->ssl_io_error);
		goto close_end;
	}

	/* connection has been already closed */
	if (SSL_get_shutdown(ktls->ssl) & SSL_SENT_SHUTDOWN) {
		ks_log(KS_LOG_DEBUG, "Can't shutdown TLS. Already closed.\n");
		goto close_end;
	}

	/* peer closes the connection */
	if (SSL_get_shutdown(ktls->ssl) & SSL_RECEIVED_SHUTDOWN) {
		ERR_clear_error();
		SSL_shutdown(ktls->ssl);
		ks_log(KS_LOG_DEBUG, "Peer closed the connection.\n");
		goto close_end;
	}

	/* us closes the connection. We do bidirection shutdown handshake */
	for (;;) {
		ERR_clear_error();
		code = SSL_shutdown(ktls->ssl);

		if (code > 0) {
			break;
		}

		ssl_error = SSL_get_error(ktls->ssl, 0);

		/* rely on ssl_error only when code < 0 */
		if ((code == 0) || (ssl_error == SSL_ERROR_WANT_READ)) {

			for (;;) {
				size_t readbytes;

				ERR_clear_error();

				if (!SSL_read_ex(ktls->ssl, shutdown_buffer, KS_TLS_SHUTDOWN_BUFLEN, &readbytes)) {
					ssl_error = SSL_get_error(ktls->ssl, 0);

					if (ssl_error == SSL_ERROR_WANT_READ) {

						if (++retry > num_retries) {
							goto close_end;
						}

						ks_sleep_ms(KS_TLS_SHUTDOWN_RETRY_MS);

						continue;  /* retry SSL_read() */
					} else if ((ssl_error == SSL_ERROR_ZERO_RETURN) || (ssl_error == SSL_ERROR_WANT_WRITE)) {
						break;     /* retry SSL_shutdown() */
					}

					goto close_end; /* other errors - close socket */
				}

				if (++retry > num_retries) {
					break;
				}
			};

		} else if (ssl_error != SSL_ERROR_WANT_WRITE) {
			break;  /* close socket */
		}

		if (++retry > num_retries) {
			break;  /* close socket */
		}

		ks_sleep_ms(KS_TLS_SHUTDOWN_RETRY_MS);
	};

 close_end:
	/* restore to blocking here, so any further read/writes will block */
	ks_socket_option(ktls->sock, KS_SO_NONBLOCK, KS_FALSE);

	/* Always close the socket */
	if (ktls->sock != KS_SOCK_INVALID) {
		ks_log(KS_LOG_DEBUG, "Shuting down TCP socket...\n");
		/* signal socket to shutdown() before close(): FIN-ACK-FIN-ACK insead of RST-RST
		   do not really handle errors here since it all going to die anyway.
		   all buffered writes if any(like SSL_shutdown() ones) will still be sent.
		 */
#ifndef WIN32
		shutdown(ktls->sock, SHUT_RDWR);
		close(ktls->sock);
#else
		shutdown(ktls->sock, SD_BOTH);
		closesocket(ktls->sock);
#endif
	}

	ktls->sock = KS_SOCK_INVALID;

	return;
}

KS_DECLARE(void) ks_tls_destroy(ks_tls_t **ktlsP)
{
	ks_tls_t *ktls = NULL;

	if (!ktlsP) {
		ks_log(KS_LOG_ERROR, "Invalid (empty) pointer!\n");

		return;
	}

	if (!(ktls = *ktlsP)) {
		return;
	}

	*ktlsP = NULL;

	if (!ktls->down) {
		ks_tls_close(ktls);
	}

	if (ktls->down > 1) {
		return;
	}

	ktls->down = 2;

	if (ktls->ssl) {
		SSL_free(ktls->ssl);
		ktls->ssl = NULL;
	}

	/* We never free server's shared SSL_CTX */
	if ((ktls->type == KS_TLS_CLIENT) && ktls->ssl_ctx) {
		SSL_CTX_free(ktls->ssl_ctx);
		ktls->ssl_ctx = NULL;
	}

	if (ktls->req_host) {
		ks_pool_free(&ktls->req_host);
		ktls->req_host = NULL;
	}

	ks_pool_free(&ktls);
	ktls = NULL;
}

static inline void get_cipher_name(ks_tls_t *ktls)
{
	if (ktls->ssl) {
		const char *cipher_name = SSL_get_cipher_name(ktls->ssl);

		ks_log(KS_LOG_INFO, "SSL negotiation succeeded, negotiated cipher is: %s\n", SSL_get_cipher_name(ktls->ssl));
		strncpy(ktls->peer_cipher_name, cipher_name, sizeof(ktls->peer_cipher_name) - 1);
	} else {
		memset(ktls->peer_cipher_name, 0, sizeof(ktls->peer_cipher_name));
	}
}

/* Client/Server side. */
static ks_status_t establish_peer_tls(ks_tls_t *ktls, uint32_t timeout_ms)
{
	ks_bool_t is_client;
	uint32_t num_retries;
	uint32_t retry = 0;

	if (!ktls) {
		ks_log(KS_LOG_ERROR, "Establish: Invalid (empty) pointer!\n");

		return KS_STATUS_ARG_NULL;
	}

	if (ktls->secure_established) {
		return KS_STATUS_SUCCESS;
	}

	is_client = (ktls->type == KS_TLS_CLIENT);

	if (!ktls->ssl) {
		ktls->ssl = SSL_new(ktls->ssl_ctx);
		if (!ktls->ssl) {
			ks_log(KS_LOG_ERROR, "Failed to initiate SSL with error [%lu]\n", ERR_peek_error());

			return KS_STATUS_FAIL;
		}

		if (!SSL_set_fd(ktls->ssl, (int)ktls->sock)) {
			ks_log(KS_LOG_ERROR, "Failed to connect the SSL object with a file descriptor [%lu]\n", ERR_peek_error());

			return KS_STATUS_FAIL;
		};

		if (ktls->init_callback) {
			ktls->init_callback(ktls, ktls->ssl);
		}
	}

	if (is_client) {
		/* Provide the server name, allowing SNI to work. */
		if (!SSL_set_tlsext_host_name(ktls->ssl, ktls->req_host)) {
			ks_log(KS_LOG_ERROR, "Failed to set the SNI\n");

			return KS_STATUS_FAIL;
		};

		if (!SSL_set1_host(ktls->ssl, ktls->req_host)) {
			ks_log(KS_LOG_ERROR, "Failed to set the certificate verification hostname\n");

			return KS_STATUS_FAIL;
		}
	}

	if (!timeout_ms) {
		timeout_ms = KS_TLS_DEFAULT_INIT_TIMEOUT_MS;
	}

	num_retries = timeout_ms / KS_TLS_INIT_RETRY_MS;

	for (;;) {
		int ssl_err;
		int code;

		ERR_clear_error();

		code = (is_client ? SSL_connect(ktls->ssl) : SSL_accept(ktls->ssl));

		if (code == 1) {
			ktls->secure_established = KS_TRUE;

			get_cipher_name(ktls);

			return KS_STATUS_SUCCESS;
		}

		ssl_err = SSL_get_error(ktls->ssl, code);

		if (!KS_SSL_ERROR_WANT_READ_WRITE(ssl_err)) {
			ks_log(KS_LOG_ERROR, "Failed to negotiate ssl connection with ssl error code: %d (%s)\n", ssl_err, ERR_error_string(ssl_err, NULL));
			ERR_print_errors_cb(log_ssl_errors, NULL);

			return KS_STATUS_FAIL;
		}

		if (++retry > num_retries) {
			break;
		}

		ks_sleep_ms(KS_TLS_INIT_RETRY_MS);
	};

	ks_log(KS_LOG_INFO, "Timeout.\n");

	return KS_STATUS_TIMEOUT;
}

static void ssl_info_callback(const SSL *s, int where, int ret)
{
	const char *str;
	int w;

	w = where & ~SSL_ST_MASK;

	if (w & SSL_ST_CONNECT)
		str = "CONNECT";
	else if (w & SSL_ST_ACCEPT)
		str = "ACCEPT";
	else
		str = "NONE";

	if (where & SSL_CB_LOOP) {
		ks_log(KS_LOG_DEBUG, "TLS_DEBUG: [%s] [%s]\n", str, SSL_state_string_long(s));
	} else if (where & SSL_CB_ALERT) {
		str = (where & SSL_CB_READ) ? "read" : "write";
		ks_log(KS_LOG_DEBUG, "TLS_DEBUG: SSL3 alert [%s] [%s:%s]\n", str, SSL_alert_type_string_long(ret), SSL_alert_desc_string_long(ret));
	} else if (where & SSL_CB_EXIT) {
		if (ret == 0) {
			ks_log(KS_LOG_DEBUG, "TLS_DEBUG: [%s] failed in [%s]\n", str, SSL_state_string_long(s));
		} else if (ret < 0) {
			ks_log(KS_LOG_DEBUG, "TLS_DEBUG: [%s] error in [%s]\n", str, SSL_state_string_long(s));
		}
	}
}

static SSL_CTX *do_create_client_ctx(const ks_tls_connect_params_t *client_params)
{
	SSL_CTX *ssl_ctx = NULL;
	ks_tls_verify_peer_t verify;

	ssl_ctx = SSL_CTX_new(TLS_client_method());

	if (!ssl_ctx) {
		return NULL;
	}

	verify = (client_params->verify_peer) ? client_params->verify_peer : KS_TLS_DEFAULT_VERIRY_PEER;

	if (verify != KS_TLS_VERIFY_DISABLED) {
		/* Enable certs validation. */
		SSL_CTX_set_verify(ssl_ctx, SSL_VERIFY_PEER, NULL);
		/* OpenSSL rejects all certs without this call. */
		SSL_CTX_set_default_verify_paths(ssl_ctx);
	}

	if (client_params->debug) {
		SSL_CTX_set_info_callback(ssl_ctx, ssl_info_callback);
	}

	return ssl_ctx;
}

static SSL_CTX *do_create_server_ctx(const ks_tls_server_ctx_params_t *server_params)
{
	SSL_CTX *ssl_ctx = NULL;
	const char *cipher_list = NULL;
	unsigned long ssl_err;
	char err_buf[256] = {0};
	ks_tls_shared_ctx_t *shared_ctx = NULL;

	if (!server_params || !server_params->key_file || !server_params->cert_file) {
		ks_log(KS_LOG_ERROR, "Can't create SSL_CTX\n");

		return NULL;
	}

	ERR_clear_error();

	ssl_ctx = SSL_CTX_new(TLS_server_method());

	if (!ssl_ctx) {
		ssl_err = ERR_peek_error();
		ks_log(KS_LOG_ERROR, "Can't create SSL_CTX, error [%lu] (%s)\n", ssl_err, ERR_error_string(ssl_err, err_buf));

		return NULL;
	}

	/* Disable SSLv2, SSLv3, TLSv1 */
	SSL_CTX_set_min_proto_version(ssl_ctx, TLS1_1_VERSION);

	/* Disable Compression CRIME (Compression Ratio Info-leak Made Easy) */
	SSL_CTX_set_options(ssl_ctx, SSL_OP_NO_COMPRESSION);

	/* set the local certificate from CertFile */
	if (server_params->chain_file) {
		if (!SSL_CTX_use_certificate_chain_file(ssl_ctx, server_params->chain_file)) {
			ks_log(KS_LOG_ERROR, "Chain file error [%s]\n", server_params->chain_file);
			goto fail;
		}
	}

	if (!SSL_CTX_use_certificate_file(ssl_ctx, server_params->cert_file, SSL_FILETYPE_PEM)) {
		ks_log(KS_LOG_ERROR, "Cert file error [%s]\n", server_params->cert_file);
		goto fail;
	}

	/* set the private key from KeyFile */
	if (!SSL_CTX_use_PrivateKey_file(ssl_ctx, server_params->key_file, SSL_FILETYPE_PEM)) {
		ks_log(KS_LOG_ERROR, "Key file error [%s]\n", server_params->key_file);
		goto fail;
	}

	/* verify private key */
	if (!SSL_CTX_check_private_key(ssl_ctx)) {
		ks_log(KS_LOG_ERROR, "Can't check private key\n");
		goto fail;
	}

	cipher_list = (server_params->cipher_list ? server_params->cipher_list : KS_TLS_DEFAULT_CIPHER_LIST);

	if (!SSL_CTX_set_cipher_list(ssl_ctx, cipher_list)) {
		ks_log(KS_LOG_ERROR, "Can't set cipher list [%s]\n", cipher_list);
		goto fail;
	}

	if (server_params->debug) {
		SSL_CTX_set_info_callback(ssl_ctx, ssl_info_callback);
	}

	return ssl_ctx;

 fail:

	ssl_err = ERR_peek_error();
	ks_log(KS_LOG_ERROR, "SSL error code: %lu (%s)\n", ssl_err, ERR_error_string(ssl_err, err_buf));

	SSL_CTX_free(ssl_ctx);

	return NULL;
}

KS_DECLARE(ks_status_t) ks_tls_create_shared_server_ctx(ks_tls_shared_ctx_t **shared_ctxP, ks_tls_server_ctx_params_t *params, ks_pool_t *pool)
{
	ks_tls_shared_ctx_t *shared_ctx = NULL;
	SSL_CTX *ssl_ctx = NULL;

	shared_ctx = ks_pool_alloc(pool, sizeof(ks_tls_shared_ctx_t));

	if (!shared_ctx) {
		ks_log(KS_LOG_ERROR, "Can't alloc shared TLS context.\n");

		return KS_STATUS_FAIL;
	}

	ssl_ctx = do_create_server_ctx(params);

	if (!ssl_ctx) {
		ks_pool_free(&shared_ctx);

		return KS_STATUS_FAIL;
	}

	shared_ctx->ssl_ctx = ssl_ctx;
	*shared_ctxP = shared_ctx;

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_tls_destroy_shared_server_ctx(ks_tls_shared_ctx_t **shared_ctxP)
{
	ks_tls_shared_ctx_t *shared_ctx = NULL;

	if (!shared_ctxP) {
		ks_log(KS_LOG_ERROR, "Invalid pointer.\n");

		return KS_STATUS_ARG_NULL;
	}

	shared_ctx = *shared_ctxP;

	if (!shared_ctx) {
		ks_log(KS_LOG_ERROR, "Shared contex is NULL.\n");

		return KS_STATUS_ARG_NULL;
	}

	SSL_CTX_free(shared_ctx->ssl_ctx);
	ks_pool_free(&shared_ctx);
	*shared_ctxP = NULL;

	return KS_STATUS_SUCCESS;
}

/* Only one of `client_params` or `server_params` must be provided. */
static ks_status_t ks_tls_init(ks_tls_t **ktlsP, ks_socket_t sock, ks_tls_connect_params_t *client_params, ks_tls_accept_params_t *server_params, ks_pool_t *pool)
{
	ks_tls_t *ktls = NULL;
	SSL_CTX *ssl_ctx = NULL;
	ks_bool_t is_client;
	uint32_t timeout_ms;

	if (!ktlsP) {
		ks_log(KS_LOG_ERROR, "Invalid target pointer for TLS context.\n");

		return KS_STATUS_ARG_NULL;
	}

	if (!client_params && !server_params) {
		ks_log(KS_LOG_ERROR, "Either client params or server params must be set.\n");

		return KS_STATUS_ARG_NULL;
	}

	if (sock == KS_SOCK_INVALID) {
		ks_log(KS_LOG_ERROR, "Invalid socket.\n");

		return KS_STATUS_FAIL;
	}

	if (server_params && !server_params->shared_ctx) {
		ks_log(KS_LOG_ERROR, "No shared SSL context.\n");

		return KS_STATUS_FAIL;
	}

	ktls = ks_pool_alloc(pool, sizeof(ks_tls_t));

	ktls->type = (client_params ? KS_TLS_CLIENT : KS_TLS_SERVER);
	ktls->sock = sock;

	is_client = (ktls->type == KS_TLS_CLIENT);

	if (is_client) {
		if (client_params->host) {
			ktls->req_host = ks_pstrdup(pool, client_params->host);
		}

		ssl_ctx = do_create_client_ctx(client_params);

		if (!ssl_ctx) {
			unsigned long ssl_ctx_error = ERR_peek_error();
			ks_log(KS_LOG_ERROR, "Failed to initiate SSL context with ssl error [%lu].\n", ssl_ctx_error);
			goto err;
		}
	} else { /* KS_TLS_SERVER */
		ssl_ctx = server_params->shared_ctx->ssl_ctx;
	}

	/* NONBLOCK + NODELAY + KEEPALIVE */
	ks_socket_common_setup(sock);

	ktls->ssl_ctx = ssl_ctx;

	timeout_ms = (is_client ? client_params->init_timeout_ms : server_params->init_timeout_ms);

	if (establish_peer_tls(ktls, timeout_ms) != KS_STATUS_SUCCESS) {
		ks_log(KS_LOG_ERROR, "[%s] Failed to establish TLS layer\n", (is_client ? "client" : "server"));
		goto err;
	}

	*ktlsP = ktls;

	return KS_STATUS_SUCCESS;

 err:
	ks_tls_destroy(&ktls);

	return KS_STATUS_FAIL;
}

KS_DECLARE(ks_status_t) ks_tls_connect(ks_tls_t **ktlsP, ks_tls_connect_params_t *params, ks_pool_t *pool)
{
#if OPENSSL_VERSION_NUMBER < 0x10100000
	/* OpenSSL < 1.1  */
	return KS_STATUS_ARG_INVALID;
#else
	ks_sockaddr_t addr = { 0 };
	ks_socket_t sock = KS_SOCK_INVALID;
	ks_port_t port;
	char *host = NULL;
	ks_status_t status;
	uint32_t sock_timeout_ms;

	if (!ktlsP || !params || !pool) {
		ks_log(KS_LOG_ERROR, "Connect: Invalid (empty) parameter! (ktlsP [%p], params [%p], pool [%p])\n", ktlsP, params, pool);

		return KS_STATUS_ARG_NULL;
	}

	host = params->host;
	port = params->port ? params->port : 443;
	sock_timeout_ms = (params->connect_timeout_ms ? params->connect_timeout_ms : KS_TLS_DEFAULT_CONN_TIMEOUT_MS);

	if (!host) {
		ks_log(KS_LOG_ERROR, "No host specified!\n");

		return KS_STATUS_FAIL;
	}

	status = ks_addr_getbyname(host, port, AF_UNSPEC, &addr);

	if (status != KS_STATUS_SUCCESS) {
		ks_log(KS_LOG_ERROR, "Can't resolve host [%s]!\n", host);

		return status;
	}

	/* if (timeout > 0) then ks_socket_connect_ex() modifies the NONBLOCK flag.
	 * It sets the NONBLOCK mode, then connect(), then waits for READ/WRITE-ready. And then sets socket to blocking mode.
	 * If timeout isn't set, then ks_socket_connect_ex() doesn't touch the NONBLOCK flag. Isn't possible here since we
	 * force the default timeout in such case.
	 */
	sock = ks_socket_connect_ex(SOCK_STREAM, IPPROTO_TCP, &addr, sock_timeout_ms);

	return ks_tls_init(ktlsP, sock, params, NULL, pool); //->host, params->flags, params->init_timeout_ms, pool);
#endif
}

KS_DECLARE(ks_status_t) ks_tls_accept(ks_tls_t **ktlsP, ks_tls_accept_params_t *params, ks_pool_t *pool)
{
	if (!ktlsP || !params || !pool) {
		ks_log(KS_LOG_ERROR, "Accept: Invalid (empty) parameter! (ktlsP [%p], params [%p], pool [%p])\n", ktlsP, params, pool);

		return KS_STATUS_ARG_NULL;
	}

	return ks_tls_init(ktlsP, params->peer_socket, NULL, params, pool);
}

KS_DECLARE(int) ks_tls_wait_sock(ks_tls_t *ktls, uint32_t ms, ks_poll_t flags)
{
	if (ktls->sock == KS_SOCK_INVALID) {
		return KS_POLL_ERROR;
	}

	if (ktls->ssl && SSL_has_pending(ktls->ssl)) {
		return KS_POLL_READ;
	}

	return ks_wait_sock(ktls->sock, ms, flags);
}

/* For Emacs:
 * Local Variables:
 * mode:c
 * indent-tabs-mode:t
 * tab-width:4
 * c-basic-offset:4
 * End:
 * For VIM:
 * vim:set softtabstop=4 shiftwidth=4 tabstop=4 noet:
 */
