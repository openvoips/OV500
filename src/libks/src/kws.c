/*
 * Copyright (c) 2018-2023 SignalWire, Inc
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

#define WS_BLOCK 10000	/* ms, blocks read operation for 10 seconds */
#define WS_SOFT_BLOCK 1000 /* ms, blocks read operation for 1 second */
#define WS_NOBLOCK 0

#define WS_INIT_SANITY 5000
#define WS_WRITE_SANITY 200

#define SHA1_HASH_SIZE 20

static const char c64[65] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

//static ks_ssize_t ws_send_buf(kws_t *kws, kws_opcode_t oc);
//static ks_ssize_t ws_feed_buf(kws_t *kws, void *data, ks_size_t bytes);


struct kws_s {
	ks_socket_t sock;
	kws_type_t type;
	char *buffer;
	char *bbuffer;
	char *body;
	char *uri;
	ks_size_t buflen;
	ks_size_t bbuflen;
	ks_ssize_t datalen;
	ks_ssize_t wdatalen;
	char *payload;
	ks_ssize_t plen;
	ks_ssize_t rplen;
	ks_ssize_t packetlen;
	SSL *ssl;
	int handshake;
	uint8_t down;
	int secure;
	SSL_CTX *ssl_ctx;
	int destroy_ssl_ctx;
	int block;
	int sanity;
	int secure_established;
	int logical_established;
	char cipher_name[128];
	kws_flag_t flags;
	int x;
	int ssl_io_error;
	void *write_buffer;
	ks_size_t write_buffer_len;
	char *req_uri;
	char *req_host;
	char *req_proto;

	ks_bool_t certified_client;
	char **sans;
	ks_size_t sans_count;
	ks_size_t unprocessed_buffer_len; /* extra data remains unprocessed */
	char *unprocessed_position;

	kws_init_callback_t init_callback;
	ks_json_t *params;

	ks_ssize_t payload_size_max;
};



static int cheezy_get_var(char *data, char *name, char *buf, ks_size_t buflen)
{
  char *p=data;

  /* the old way didnt make sure that variable values were used for the name hunt
   * and didnt ensure that only a full match of the variable name was used
   */
  ks_assert(buflen > 0);

  do {
    if(!strncasecmp(p,name,strlen(name)) && *(p+strlen(name))==':') break;
  } while((p = (strstr(p,"\n")+1))!=(char *)1);


  if (p && p != (char *)1 && *p!='\0') {
    char *v, *e = 0;

    v = strchr(p, ':');
    if (v) {
      v++;
      while(v && *v == ' ') {
	v++;
      }
      if (v)  {
	e = strchr(v, '\r');
	if (!e) {
	  e = strchr(v, '\n');
	}
      }

      if (v && e) {
	size_t cplen;
	ks_size_t len = e - v;

	if (len > buflen - 1) {
	  cplen = buflen - 1;
	} else {
	  cplen = len;
	}

	strncpy(buf, v, cplen);
	*(buf+cplen) = '\0';
	return 1;
      }

    }
  }
  return 0;
}

static int b64encode(unsigned char *in, ks_size_t ilen, unsigned char *out, ks_size_t olen)
{
	int y=0,bytes=0;
	ks_size_t x=0;
	unsigned int b=0,l=0;

	if(olen) {
	}

	for(x=0;x<ilen;x++) {
		b = (b<<8) + in[x];
		l += 8;
		while (l >= 6) {
			out[bytes++] = c64[(b>>(l-=6))%64];
			if(++y!=72) {
				continue;
			}
			//out[bytes++] = '\n';
			y=0;
		}
	}

	if (l > 0) {
		out[bytes++] = c64[((b%16)<<(6-l))%64];
	}
	if (l != 0) while (l < 6) {
		out[bytes++] = '=', l += 2;
	}

	return 0;
}

static void sha1_digest(unsigned char *digest, char *in)
{
	SHA_CTX sha;

	SHA1_Init(&sha);
	SHA1_Update(&sha, in, strlen(in));
	SHA1_Final(digest, &sha);

}

/* fix me when we get real rand funcs in ks */
static void gen_nonce(unsigned char *buf, uint16_t len)
{
	int max = 255;
	uint16_t x;
	ks_time_t time_now = ks_time_now();
	srand((unsigned int)(((time_now >> 32) ^ time_now) & 0xffffffff));

	for (x = 0; x < len; x++) {
		int j = (int) (max * 1.0 * rand() / (RAND_MAX + 1.0));
		buf[x] = (char) j;
	}
}

static int verify_accept(kws_t *kws, const unsigned char *enonce, const char *accept)
{
	char input[256] = { 0 };
	unsigned char output[SHA1_HASH_SIZE] = { 0 };
	char b64[256] = { 0 };

	snprintf(input, sizeof(input), "%s%s", enonce, WEBSOCKET_GUID);
	sha1_digest(output, input);
	b64encode((unsigned char *)output, SHA1_HASH_SIZE, (unsigned char *)b64, sizeof(b64));

	return !strcmp(b64, accept);
}

static int ws_client_handshake(kws_t *kws)
{
	unsigned char nonce[16] = { 0 };
	unsigned char enonce[128] = { 0 };
	char *req = NULL;
	char *frame_end = NULL;
	char *extra_headers = NULL;

	gen_nonce(nonce, sizeof(nonce));
	b64encode(nonce, sizeof(nonce), enonce, sizeof(enonce));

	if (kws->params) {
		ks_json_t *headers = ks_json_get_object_item(kws->params, "headers");

		if (headers) {
			ks_json_t *param;

			KS_JSON_ARRAY_FOREACH(param, headers) {
				const char *k = ks_json_get_object_string(param, "key", NULL);
				const char *v = ks_json_get_object_string(param, "value", NULL);

				if (k && v) {
					if (extra_headers) {
						char *tmp = extra_headers;
						extra_headers = ks_mprintf("%s%s: %s\r\n", tmp, k, v);
						ks_safe_free(tmp);
					} else {
						extra_headers = ks_mprintf("%s: %s\r\n", k, v);
					}
				}
			}
		}
	}

	req = ks_mprintf("GET %s HTTP/1.1\r\n"
				"Host: %s\r\n"
				"Upgrade: websocket\r\n"
				"Connection: Upgrade\r\n"
				"Sec-WebSocket-Key: %s\r\n"
				"Sec-WebSocket-Version: 13\r\n"
				"%s%s%s%s"
				"\r\n",
				kws->req_uri, kws->req_host, enonce,
				kws->req_proto ? "Sec-WebSocket-Protocol: " : "",
				kws->req_proto ? kws->req_proto : "",
				kws->req_proto ? "\r\n" : "",
				extra_headers ? extra_headers : "");

	if (extra_headers) free(extra_headers);

	kws_raw_write(kws, req, strlen(req));

	ks_safe_free(req);

	ks_ssize_t bytes;

	do {
		bytes = kws_string_read(kws, kws->buffer + kws->datalen, kws->buflen - kws->datalen, WS_BLOCK);
	} while (bytes > 0 && !strstr((char *)kws->buffer, "\r\n\r\n"));

	if (bytes > 0) {
		char accept[128] = { 0 };

		frame_end = strstr((char *)kws->buffer, "\r\n\r\n");
		if (frame_end) frame_end += 4;

		cheezy_get_var(kws->buffer, "Sec-WebSocket-Accept", accept, sizeof(accept));

		if (ks_zstr_buf(accept) || !verify_accept(kws, enonce, (char *)accept)) {
			return -1;
		}
	} else {
		return -1;
	}

	/* check if any more data */
	if (frame_end && frame_end - kws->buffer < bytes) {
		kws->unprocessed_buffer_len = bytes - (frame_end - kws->buffer);
		kws->unprocessed_position = frame_end;
	}

	kws->handshake = 1;

	return 0;
}

static int ws_server_handshake(kws_t *kws)
{
	char key[256] = "";
	char version[5] = "";
	char proto[256] = "";
	char proto_buf[384] = "";
	char input[512] = "";
	unsigned char output[SHA1_HASH_SIZE] = "";
	char b64[256] = "";
	char respond[1024] = "";
	ks_ssize_t bytes;
	char *p, *e = 0;

	if (kws->sock == KS_SOCK_INVALID) {
		return -3;
	}

	while((bytes = kws_string_read(kws, kws->buffer + kws->datalen, kws->buflen - kws->datalen, WS_BLOCK)) > 0) {
		kws->datalen += bytes;
		if (strstr(kws->buffer, "\r\n\r\n") || strstr(kws->buffer, "\n\n")) {
			break;
		}
	}

	if (bytes < 0 || ((ks_size_t)bytes) > kws->buflen - 1) {
		goto err;
	}

	*(kws->buffer + kws->datalen) = '\0';

	if (strncasecmp(kws->buffer, "GET ", 4)) {
		goto err;
	}

	p = kws->buffer + 4;

	e = strchr(p, ' ');
	if (!e) {
		goto err;
	}

	kws->uri = ks_pool_alloc(ks_pool_get(kws), (unsigned long)(e-p) + 1);
	strncpy(kws->uri, p, e-p);
	*(kws->uri + (e-p)) = '\0';

	cheezy_get_var(kws->buffer, "Sec-WebSocket-Key", key, sizeof(key));
	cheezy_get_var(kws->buffer, "Sec-WebSocket-Version", version, sizeof(version));
	cheezy_get_var(kws->buffer, "Sec-WebSocket-Protocol", proto, sizeof(proto));

	if (!*key) {
		goto err;
	}

	snprintf(input, sizeof(input), "%s%s", key, WEBSOCKET_GUID);
	sha1_digest(output, input);
	b64encode((unsigned char *)output, SHA1_HASH_SIZE, (unsigned char *)b64, sizeof(b64));

	if (*proto) {
		snprintf(proto_buf, sizeof(proto_buf), "Sec-WebSocket-Protocol: %s\r\n", proto);
	}

	snprintf(respond, sizeof(respond),
			 "HTTP/1.1 101 Switching Protocols\r\n"
			 "Upgrade: websocket\r\n"
			 "Connection: Upgrade\r\n"
			 "Sec-WebSocket-Accept: %s\r\n"
			 "%s\r\n",
			 b64,
			 proto_buf);
	respond[511] = 0;

	if (kws_raw_write(kws, respond, strlen(respond)) != (ks_ssize_t)strlen(respond)) {
		goto err;
	}

	kws->handshake = 1;
	kws->flags &= ~KWS_HTTP;

	return 0;

 err:

	if (!(kws->flags & KWS_STAY_OPEN)) {

		if (bytes > 0) {
			snprintf(respond, sizeof(respond), "HTTP/1.1 400 Bad Request\r\n"
					 "Sec-WebSocket-Version: 13\r\n\r\n");
			respond[511] = 0;

			kws_raw_write(kws, respond, strlen(respond));
		}

		kws_close(kws, WS_NONE);
	} else if (kws->flags & KWS_HTTP) {
		kws->handshake = 1;
		return 0;
	}

	return -1;

}

#define SSL_IO_ERROR(err) (err == SSL_ERROR_SYSCALL || err == SSL_ERROR_SSL)
#define SSL_ERROR_WANT_READ_WRITE(err) (err == SSL_ERROR_WANT_READ || err == SSL_ERROR_WANT_WRITE)

KS_DECLARE(ks_ssize_t) kws_raw_read(kws_t *kws, void *data, ks_size_t bytes, int block)
{
	int r;
	int ssl_err = 0;
	int block_n = block / 10;

	if (kws->unprocessed_buffer_len > 0) {
		if (kws->unprocessed_buffer_len > bytes) {
			memmove((char *)data, kws->unprocessed_position, bytes);
			kws->unprocessed_position += bytes;
			kws->unprocessed_buffer_len -= bytes;
			return bytes;
		} else {
			ssize_t len = kws->unprocessed_buffer_len;
			memmove((char *)data, kws->unprocessed_position, len);
			kws->unprocessed_buffer_len = 0;
			kws->unprocessed_position = NULL;
			return len;
		}
	}

	kws->x++;
	if (kws->x > 250) ks_sleep_ms(1);

	if (kws->ssl) {
		do {
			ERR_clear_error();
			r = SSL_read(kws->ssl, data, (int)bytes);
			if (r == 0) {
				ssl_err = SSL_get_error(kws->ssl, r);
				if (ssl_err != SSL_ERROR_ZERO_RETURN) {
					ks_log(KS_LOG_WARNING, "Weird SSL_read error: %d\n", ssl_err);
					if (SSL_IO_ERROR(ssl_err)) {
						kws->ssl_io_error = 1;
					}
				}
			}

			if (r < 0) {
				ssl_err = SSL_get_error(kws->ssl, r);

				if (SSL_ERROR_WANT_READ_WRITE(ssl_err)) {
					if (!block) {
						r = -2;
						goto end;
					}
					kws->x++;
					ks_sleep_ms(10);
				} else {
					if (SSL_IO_ERROR(ssl_err)) {
						kws->ssl_io_error = 1;
					}

					r = -1;
					goto end;
				}
			}

		} while (r < 0 && SSL_ERROR_WANT_READ_WRITE(ssl_err) && kws->x < block_n);

		goto end;
	}

	do {
		r = recv(kws->sock, data, (int)bytes, 0);

		if (r == -1) {
			if (!block && ks_errno_is_blocking(ks_errno())) {
				r = -2;
				goto end;
			}

			if (block) {
				kws->x++;
				ks_sleep_ms(10);
			}
		}
	} while (r == -1 && ks_errno_is_blocking(ks_errno()) && kws->x < block_n);

 end:

	if (kws->x >= 10000 || (block && kws->x >= block_n)) {
		r = -1;
	}

	if (r > 0 && r < bytes) {
		*((char *)data + r) = '\0';
	}

	if (r >= 0) {
		kws->x = 0;
	}

	return r;
}

/*
 * Blocking read until bytes have been received, failure, or too many retries.
 */
static ks_ssize_t kws_raw_read_blocking(kws_t *kws, char *data, ks_size_t max_bytes, int max_retries)
{
	ks_ssize_t total_bytes_read = 0;
	int zero_reads = 0;
	while (total_bytes_read < max_bytes && zero_reads < max_retries)
	{
		int bytes_read = kws_raw_read(kws, data + total_bytes_read, max_bytes - total_bytes_read, WS_BLOCK);
		if (bytes_read == 0)
		{
			zero_reads++;
			continue;
		}
		else if (bytes_read < 0)
		{
			break;
		}
		total_bytes_read += (ks_ssize_t)bytes_read;
		zero_reads = 0;
	}
	return total_bytes_read;
}

/**
 * Read from websocket and store as NULL terminated string. Up to buffer_size - 1 bytes will be read from websocket. Contents of str_buffer is NULL terminated if >0 bytes are read from the websocket.
 */
KS_DECLARE(ks_ssize_t) kws_string_read(kws_t *kws, char *str_buffer, ks_size_t buffer_size, int block)
{
	if (buffer_size < 1) {
		return -1;
	}
	str_buffer[buffer_size - 1] = '\0';
	if (buffer_size < 2) {
		return 0;
	}
	return kws_raw_read(kws, str_buffer, buffer_size - 1, block);
}

/*
 * Blocking read as from websocket and store as NULL terminated string until buffer_size - 1 have been received, failure, or too many retries. Contents of str_buffer is NULL terminated if >0 bytes are read from the websocket.
 */
static ks_ssize_t kws_string_read_blocking(kws_t *kws, char *str_buffer, ks_size_t buffer_size, int max_retries)
{
	if (buffer_size < 1) {
		return -1;
	}
	str_buffer[buffer_size - 1] = '\0';
	if (buffer_size < 2) {
		return 0;
	}
	return kws_raw_read_blocking(kws, str_buffer, buffer_size - 1, max_retries);
}

KS_DECLARE(ks_ssize_t) kws_raw_write(kws_t *kws, void *data, ks_size_t bytes)
{
	int r;
	int sanity = WS_WRITE_SANITY;
	int ssl_err = 0;
	ks_size_t wrote = 0;

	if (kws->ssl) {
		do {
			ERR_clear_error();
			r = SSL_write(kws->ssl, (void *)((unsigned char *)data + wrote), (int)(bytes - wrote));

			if (r == 0) {
				ssl_err = SSL_get_error(kws->ssl, r);
				if (SSL_IO_ERROR(ssl_err)) {
					kws->ssl_io_error = 1;
				}

				ssl_err = 42;
				break;
			}

			if (r > 0) {
				wrote += r;
			}

			if (sanity < WS_WRITE_SANITY) {
				int ms = 1;

				if (kws->block) {
					if (sanity < WS_WRITE_SANITY * 3 / 4) {
						ms = 50;
					} else if (sanity < WS_WRITE_SANITY / 2) {
						ms = 25;
					}
				}

				ks_sleep_ms(ms);
			}

			if (r < 0) {
				ssl_err = SSL_get_error(kws->ssl, r);

				if (!SSL_ERROR_WANT_READ_WRITE(ssl_err)) {
					if (SSL_IO_ERROR(ssl_err)) {
						kws->ssl_io_error = 1;
					}

					break;
				}

				ssl_err = 0;
			}

		} while (--sanity > 0 && wrote < bytes);

		if (!sanity) ssl_err = 56;

		if (ssl_err) {
			r = ssl_err * -1;
		}

		return r > 0 ? wrote : r;
	}

	do {
		r = send(kws->sock, (void *)((unsigned char *)data + wrote), (int)(bytes - wrote), 0);

		if (r > 0) {
			wrote += r;
		}

		if (sanity < WS_WRITE_SANITY) {
			int ms = 1;

			if (kws->block) {
				if (sanity < WS_WRITE_SANITY * 3 / 4) {
					ms = 50;
				} else if (sanity < WS_WRITE_SANITY / 2) {
					ms = 25;
				}
			}
			ks_sleep_ms(ms);
		}

		if (r == -1) {
			if (!ks_errno_is_blocking(ks_errno())) {
				break;
			}
		}

	} while (--sanity > 0 && wrote < bytes);

	//if (r<0) {
		//printf("wRITE FAIL: %s\n", strerror(errno));
	//}

	return r >= 0 ? wrote : r;
}

static void restore_socket(ks_socket_t sock)
{
	ks_socket_option(sock, KS_SO_NONBLOCK, KS_FALSE);
}

static int __log_ssl_errors(const char *err, size_t len, void *u)
{
	ks_log(KS_LOG_ERROR, "  %s", err);	// ssl adds a \n
	return 0;
}

static int establish_client_logical_layer(kws_t *kws)
{

	if (!kws->sanity) {
		return -1;
	}

	if (kws->logical_established) {
		return 0;
	}

	if (kws->secure && !kws->secure_established) {
		int code;

		if (!kws->ssl) {
			kws->ssl = SSL_new(kws->ssl_ctx);
			if (!kws->ssl) {
				unsigned long ssl_new_error = ERR_peek_error();
				ks_log(KS_LOG_ERROR, "Failed to initiate SSL with error [%lu]\n", ssl_new_error);
				return -1;
			}

			SSL_set_fd(kws->ssl, (int)kws->sock);

			if (kws->init_callback) kws->init_callback(kws, kws->ssl);
		}

		/* Provide the server name, allowing SNI to work. */
		SSL_set_tlsext_host_name(kws->ssl, kws->req_host);

		do {
			ERR_clear_error();
			code = SSL_connect(kws->ssl);

			if (code == 1) {
				kws->secure_established = 1;
				break;
			}

			if (code == 0) {
				return -1;
			}

			if (code < 0) {
				int ssl_err = SSL_get_error(kws->ssl, code);
				if (code < 0 && !SSL_ERROR_WANT_READ_WRITE(ssl_err)) {
					ks_log(KS_LOG_ERROR, "Failed to negotiate ssl connection with ssl error code: %d (%s)\n", ssl_err, ERR_error_string(ssl_err, NULL));
					ERR_print_errors_cb(__log_ssl_errors, NULL);
					return -1;
				}
			}

			if (kws->block) {
				ks_sleep_ms(10);
			} else {
				ks_sleep_ms(1);
			}

			kws->sanity--;

			if (!kws->block) {
				return -2;
			}

		} while (kws->sanity > 0);

		if (!kws->sanity) {
			return -1;
		}
	}

	while (!kws->down && !kws->handshake) {
		int r = ws_client_handshake(kws);

		if (r < 0) {
			kws->down = 1;
			return -1;
		}

		if (!kws->handshake && !kws->block) {
			return -2;
		}

	}

	kws->logical_established = 1;
	if (kws->ssl) {
		strncpy(kws->cipher_name, SSL_get_cipher_name(kws->ssl), sizeof(kws->cipher_name) - 1);
		ks_log(KS_LOG_INFO, "SSL negotiation succeeded, negotiated cipher is: %s\n", kws->cipher_name);

		if (ks_json_get_object_bool(kws->params, "ssl_validate_certificate", KS_FALSE)) {
			X509 *cert = SSL_get_peer_certificate(kws->ssl);

			if (!cert) {
				ks_log(KS_LOG_ERROR, "SSL negotiation failed, no certificate\n");

				return -1;
			}

			if (SSL_get_verify_result(kws->ssl) != X509_V_OK) {
				ks_log(KS_LOG_ERROR, "SSL negotiation failed, invalid certificate\n");
				X509_free(cert);

				return -1;
			}

			X509_free(cert);
		}

	} else {
		memset(kws->cipher_name, 0, sizeof(kws->cipher_name));
	}

	return 0;
}

KS_DECLARE(ks_status_t) kws_get_cipher_name(kws_t *kws, char *name, ks_size_t name_len)
{
	if (kws->ssl) {
		strncpy(name, kws->cipher_name, name_len);
		return KS_STATUS_SUCCESS;
	}
	else
		return KS_STATUS_INVALID_ARGUMENT;
}

static int establish_server_logical_layer(kws_t *kws)
{

	if (!kws->sanity) {
		return -1;
	}

	if (kws->logical_established) {
		return 0;
	}

	if (kws->secure && !kws->secure_established) {
		int code;

		if (!kws->ssl) {
			kws->ssl = SSL_new(kws->ssl_ctx);
			if (!kws->ssl) {
				unsigned long ssl_new_error = ERR_peek_error();
				ks_log(KS_LOG_ERROR, "Failed to initiate SSL with error [%lu]\n", ssl_new_error);
				return -1;
			}

			SSL_set_fd(kws->ssl, (int)kws->sock);
		}

		do {
			ERR_clear_error();
			code = SSL_accept(kws->ssl);

			if (code == 1) {
				kws->secure_established = 1;
				break;
			}

			if (code == 0) {
				return -1;
			}

			if (code < 0) {
				int ssl_err = SSL_get_error(kws->ssl, code);
				if (code < 0 && !SSL_ERROR_WANT_READ_WRITE(ssl_err)) {
					ks_log(KS_LOG_ERROR, "Failed to negotiate ssl connection with ssl error code: %d (%s)\n", ssl_err, ERR_error_string(ssl_err, NULL));
					ERR_print_errors_cb(__log_ssl_errors, NULL);
					return -1;
				}
			}

			if (kws->block) {
				ks_sleep_ms(10);
			} else {
				ks_sleep_ms(1);
			}

			kws->sanity--;

			if (!kws->block) {
				return -2;
			}

		} while (kws->sanity > 0);

		if (!kws->sanity) {
			return -1;
		}

	}

	while (!kws->down && !kws->handshake) {
		int r = ws_server_handshake(kws);

		if (r < 0) {
			kws->down = 1;
			return -1;
		}

		if (!kws->handshake && !kws->block) {
			return -2;
		}

	}

	kws->logical_established = 1;
	if (kws->ssl) {
		ks_log(KS_LOG_INFO, "SSL negotiation succeeded, negotiated cipher is: %s\n", SSL_get_cipher_name(kws->ssl));
	}

	return 0;
}

static int establish_logical_layer(kws_t *kws)
{
	if (kws->type == KWS_CLIENT) {
		return establish_client_logical_layer(kws);
	} else {
		return establish_server_logical_layer(kws);
	}
}

KS_DECLARE(ks_status_t) kws_init_ex(kws_t **kwsP, ks_socket_t sock, SSL_CTX *ssl_ctx, const char *client_data, kws_flag_t flags, ks_pool_t *pool, ks_json_t *params)
{
	kws_t *kws;

	if (*kwsP) kws = *kwsP;
	else kws = ks_pool_alloc(pool, sizeof(*kws));

	kws->flags = flags;
	kws->unprocessed_buffer_len = 0;
	kws->unprocessed_position = NULL;
	kws->params = ks_json_duplicate(params, KS_TRUE);
	kws->payload_size_max = ks_json_get_object_number_int(params, "payload_size_max", 0);

	if ((flags & KWS_BLOCK)) {
		kws->block = WS_BLOCK;
	}

	if (client_data) {
		char *p = NULL;
		kws->req_uri = ks_pstrdup(pool, client_data);

		if ((p = strchr(kws->req_uri, ':'))) {
			*p++ = '\0';
			kws->req_host = p;
			if ((p = strchr(kws->req_host, ':'))) {
				*p++ = '\0';
				kws->req_proto = p;
			}
		}

		kws->type = KWS_CLIENT;
	} else {
		kws->type = KWS_SERVER;
		kws->flags |= KWS_FLAG_DONTMASK;
	}

	kws->sock = sock;
	kws->sanity = WS_INIT_SANITY;
	kws->ssl_ctx = ssl_ctx;

	kws->buflen = 1024 * 64;
	kws->bbuflen = kws->buflen;

	kws->buffer = ks_pool_alloc(pool, (unsigned long)kws->buflen);
	kws->bbuffer = ks_pool_alloc(pool, (unsigned long)kws->bbuflen);
	//printf("init %p %ld\n", (void *) kws->bbuffer, kws->bbuflen);
	//memset(kws->buffer, 0, kws->buflen);
	//memset(kws->bbuffer, 0, kws->bbuflen);

	kws->secure = ssl_ctx ? 1 : 0;

	ks_socket_common_setup(sock);

	if (establish_logical_layer(kws) == -1) {
		ks_log(KS_LOG_ERROR, "Failed to establish logical layer\n");
		goto err;
	}

	if (kws->down) {
		ks_log(KS_LOG_ERROR, "Link down\n");
		goto err;
	}

	if (kws->type == KWS_SERVER)
	{
		X509 *cert = SSL_get_peer_certificate(kws->ssl);

		if (cert && SSL_get_verify_result(kws->ssl) == X509_V_OK) {
			GENERAL_NAMES *sans = X509_get_ext_d2i(cert, NID_subject_alt_name, NULL, NULL);

			kws->certified_client = KS_TRUE;
			if (sans) {
				kws->sans_count = (ks_size_t)sk_GENERAL_NAME_num(sans);
				if (kws->sans_count) kws->sans = ks_pool_calloc(pool, kws->sans_count, sizeof(char *));
				for (ks_size_t i = 0; i < kws->sans_count; i++) {
					const GENERAL_NAME *gname = sk_GENERAL_NAME_value(sans, (int)i);
#if OPENSSL_VERSION_NUMBER >= 0x10100000
					const unsigned char *name = ASN1_STRING_get0_data((const ASN1_STRING *)gname->d.dNSName);
#else
					char *name = (char *)ASN1_STRING_data(gname->d.dNSName);
#endif
					kws->sans[i] = ks_pstrdup(pool, (char *)name);

				}
				sk_GENERAL_NAME_pop_free(sans, GENERAL_NAME_free);
			}
		}

		if (cert) X509_free(cert);
	}

	*kwsP = kws;

	return KS_STATUS_SUCCESS;

 err:
	kws_destroy(kwsP);

	return KS_STATUS_FAIL;
}

KS_DECLARE(void) kws_set_init_callback(kws_t *kws, kws_init_callback_t callback)
{
	ks_assert(kws);

	kws->init_callback = callback;
}

KS_DECLARE(ks_status_t) kws_create(kws_t **kwsP, ks_pool_t *pool)
{
	kws_t *kws;

	kws = ks_pool_alloc(pool, sizeof(*kws));
	*kwsP = kws;

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(void) kws_destroy(kws_t **kwsP)
{
	kws_t *kws;
	ks_assert(kwsP);

	if (!(kws = *kwsP)) {
		return;
	}

	*kwsP = NULL;

	if (!kws->down) {
		kws_close(kws, WS_NONE);
	}

	if (kws->down > 1) {
		return;
	}

	kws->down = 2;

	if (kws->write_buffer) {
		ks_pool_free(&kws->write_buffer);
		kws->write_buffer = NULL;
		kws->write_buffer_len = 0;
	}

	if (kws->ssl) {
		SSL_free(kws->ssl);
		kws->ssl = NULL;
	}

	if (kws->destroy_ssl_ctx && kws->ssl_ctx) {
		SSL_CTX_free(kws->ssl_ctx);
	}

	if (kws->buffer) ks_pool_free(&kws->buffer);
	if (kws->bbuffer) ks_pool_free(&kws->bbuffer);

	kws->buffer = kws->bbuffer = NULL;
	if (kws->params) ks_json_delete(&kws->params);

	ks_pool_free(&kws);
	kws = NULL;
}

KS_DECLARE(ks_ssize_t) kws_close(kws_t *kws, int16_t reason)
{

	if (kws->down) {
		return -1;
	}

	kws->down = 1;

	if (kws->uri) {
		ks_pool_free(&kws->uri);
		kws->uri = NULL;
	}

	if (kws->handshake && kws->sock != KS_SOCK_INVALID) {
		uint16_t *u16;
		int16_t got_reason = reason ? reason : WS_NORMAL_CLOSE /* regular close initiated by us */;

		if (kws->type == KWS_CLIENT) {
			const uint8_t maskb = 0x80;
			uint8_t size = 0x02, fr[8] = {WSOC_CLOSE | 0x80, size | maskb, 0, 0, 0, 0, 0, 0}, masking_key[4], i;
			uint8_t *p;

			u16 = (uint16_t *) &fr[6];
			*u16 = htons((int16_t)got_reason); 
			p = (uint8_t *)u16; /*use p for masking the reason which is the payload */

			gen_nonce(masking_key, 4);
			memcpy((uint8_t *)fr + 2, &masking_key, 4);

			for (i = 0; i < size; i++) {
				*(p + i) = (*((uint8_t *)p + i)) ^ (*(masking_key + (i % 4)));
			}

			kws_raw_write(kws, fr, 8);
		} else {
			uint8_t fr[4] = {WSOC_CLOSE | 0x80, 2, 0};

			u16 = (uint16_t *) &fr[2];
			*u16 = htons((int16_t)got_reason);
			kws_raw_write(kws, fr, 4);
		}
	}

	if (kws->ssl && kws->sock != KS_SOCK_INVALID) {
		/* first invocation of SSL_shutdown() would normally return 0 and just try to send SSL protocol close request (close_notify_alert).
		   we just slightly polite, since we want to close socket fast and
		   not bother waiting for SSL protocol close response before closing socket,
		   since we want cleanup to be done fast for scenarios like:
		   client change NAT (like jump from one WiFi to another) and now unreachable from old ip:port, however
		   immidiately reconnect with new ip:port but old session id (and thus should replace the old session/channel)
		   However it is recommended to do bidirectional shutdown
		   and also to read all the remaining data sent by the client
		   before it indicates EOF (SSL_ERROR_ZERO_RETURN).
		   To avoid stuck in this process in case of dead peers,
		   we wait for WS_SOFT_BLOCK time (1s) before we give up.
		*/
		int code = 0, rcode = 0;
		int ssl_error = 0;
		int n = 0, block_n = WS_SOFT_BLOCK / 10;

		/* SSL layer was never established or underlying IO error occured */
		if (!kws->secure_established || kws->ssl_io_error) {
			goto end;
		}

		/* connection has been already closed */
		if (SSL_get_shutdown(kws->ssl) & SSL_SENT_SHUTDOWN) {
			goto end;
		}

		/* peer closes the connection */
		if (SSL_get_shutdown(kws->ssl) & SSL_RECEIVED_SHUTDOWN) {
			ERR_clear_error();
			SSL_shutdown(kws->ssl);
			goto end;
		}

		/* us closes the connection. We do bidirection shutdown handshake */
		for(;;) {
			ERR_clear_error();
			code = SSL_shutdown(kws->ssl);
			ssl_error = SSL_get_error(kws->ssl, code);
			if (code <= 0 && ssl_error == SSL_ERROR_WANT_READ) {
				/* need to make sure there are no more data to read */
				for(;;) {
					ERR_clear_error();
					if ((rcode = SSL_read(kws->ssl, kws->buffer, 9)) <= 0) {
						ssl_error = SSL_get_error(kws->ssl, rcode);
						if (ssl_error == SSL_ERROR_ZERO_RETURN) {
							break;
						} else if (SSL_IO_ERROR(ssl_error)) {
							goto end;
						} else if (ssl_error == SSL_ERROR_WANT_READ) {
							if (++n == block_n) {
								goto end;
							}

							ks_sleep_ms(10);
						} else {
							goto end;
						}
					}
				}
			} else if (code == 0 || (code < 0 && ssl_error == SSL_ERROR_WANT_WRITE)) {
				if (++n == block_n) {
					goto end;
				}

				ks_sleep_ms(10);
			} else { /* code != 0 */
				goto end;
			}
		}
	}

 end:
	/* restore to blocking here, so any further read/writes will block */
	restore_socket(kws->sock);

	if ((kws->flags & KWS_CLOSE_SOCK) && kws->sock != KS_SOCK_INVALID) {
		/* signal socket to shutdown() before close(): FIN-ACK-FIN-ACK insead of RST-RST
		   do not really handle errors here since it all going to die anyway.
		   all buffered writes if any(like SSL_shutdown() ones) will still be sent.
		 */
#ifndef WIN32
		shutdown(kws->sock, SHUT_RDWR);
		close(kws->sock);
#else
		shutdown(kws->sock, SD_BOTH);
		closesocket(kws->sock);
#endif
	}

	kws->sock = KS_SOCK_INVALID;

	return reason * -1;

}

#ifndef WIN32
#if defined(HAVE_BYTESWAP_H)
#include <byteswap.h>
#elif defined(HAVE_SYS_ENDIAN_H)
#include <sys/endian.h>
#elif defined (__APPLE__)
#include <libkern/OSByteOrder.h>
#define bswap_16 OSSwapInt16
#define bswap_32 OSSwapInt32
#define bswap_64 OSSwapInt64
#elif defined (__UCLIBC__)
#else
#ifndef bswap_16
	#define bswap_16(value) ((((value) & 0xff) << 8) | ((value) >> 8))
	#define bswap_32(value) (((uint32_t)bswap_16((uint16_t)((value) & 0xffff)) << 16) | (uint32_t)bswap_16((uint16_t)((value) >> 16)))
	#define bswap_64(value) (((uint64_t)bswap_32((uint32_t)((value) & 0xffffffff)) << 32) | (uint64_t)bswap_32((uint32_t)((value) >> 32)))
#endif
#endif
#endif

uint64_t hton64(uint64_t val)
{
#if __BYTE_ORDER == __BIG_ENDIAN
	return (val);
#else
	return bswap_64(val);
#endif
}

uint64_t ntoh64(uint64_t val)
{
#if __BYTE_ORDER == __BIG_ENDIAN
	return (val);
#else
	return bswap_64(val);
#endif
}

KS_DECLARE(ks_bool_t) kws_certified_client(kws_t *kws)
{
	ks_assert(kws);
	return kws->certified_client;
}

KS_DECLARE(ks_status_t) kws_peer_sans(kws_t *kws, char *buf, ks_size_t buflen)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	X509 *cert = NULL;

	ks_assert(kws);
	ks_assert(buf);
	ks_assert(buflen);

	cert = SSL_get_peer_certificate(kws->ssl);
	if (!cert) {
		ret = KS_STATUS_FAIL;
		goto done;
	}

	if (SSL_get_verify_result(kws->ssl) != X509_V_OK) {
		ret = KS_STATUS_FAIL;
		goto done;
	}

	//if (X509_NAME_get_text_by_NID(X509_get_subject_name(cert), NID_commonName, buf, (int)buflen) < 0) {
	//	ret = KS_STATUS_FAIL;
	//	goto done;
	//}

	GENERAL_NAMES *san_names = X509_get_ext_d2i(cert, NID_subject_alt_name, NULL, NULL);
	if (san_names) {
		int san_names_nb = sk_GENERAL_NAME_num(san_names);
		for (int i = 0; i < san_names_nb; i++) {
			const GENERAL_NAME *current_name = sk_GENERAL_NAME_value(san_names, i);

#if OPENSSL_VERSION_NUMBER >= 0x10100000
			const unsigned char *name = ASN1_STRING_get0_data((const ASN1_STRING *)current_name->d.dNSName);
#else
			char *name = (char *)ASN1_STRING_data(current_name->d.dNSName);
#endif

			if (name) continue;
		}
		sk_GENERAL_NAME_pop_free(san_names, GENERAL_NAME_free);
	}
done:
	if (cert) X509_free(cert);

	return ret;
}

KS_DECLARE(ks_ssize_t) kws_read_frame(kws_t *kws, kws_opcode_t *oc, uint8_t **data)
{

	ks_ssize_t need = 2;
	char *maskp;
	int ll = 0;
	int frag = 0;
	int blen;

	kws->body = kws->bbuffer;
	kws->packetlen = 0;

	*oc = WSOC_INVALID;

 again:
	need = 2;
	maskp = NULL;
	*data = NULL;

	ll = establish_logical_layer(kws);

	if (ll < 0) {
		ks_log(KS_LOG_ERROR, "Read frame error from logical layer: ll = %d\n", ll);
		return ll;
	}

	if (kws->down) {
		ks_log(KS_LOG_ERROR, "Read frame error because kws is down");
		return -1;
	}

	if (!kws->handshake) {
		ks_log(KS_LOG_ERROR, "Read frame error because kws handshake is incomplete");
		return kws_close(kws, WS_NONE);
	}

	if ((kws->datalen = kws_string_read(kws, kws->buffer, 9 + 1, kws->block)) < 0) { // read 9 bytes into NULL terminated 10 byte buffer
		ks_log(KS_LOG_ERROR, "Read frame error because kws_string_read returned %ld\n", kws->datalen);
		if (kws->datalen == -2) {
			return -2;
		}
		return kws_close(kws, WS_NONE);
	}

	if (kws->datalen < need) {
		ssize_t bytes = kws_string_read(kws, kws->buffer + kws->datalen, 9 - kws->datalen, WS_BLOCK);

		if (bytes < 0 || (kws->datalen += bytes) < need) {
			/* too small - protocol err */
			ks_log(KS_LOG_ERROR, "Read frame error because kws_string_read: bytes = %ld, datalen = %ld, needed = %ld\n", bytes, kws->datalen, need);
			return kws_close(kws, WS_NONE);
		}
	}

	*oc = *kws->buffer & 0xf;

	switch(*oc) {
	case WSOC_CLOSE:
		{
			/* Nominal case, debug output only */
			ks_log(KS_LOG_DEBUG, "Read frame OPCODE = WSOC_CLOSE\n");
			kws->plen = kws->buffer[1] & 0x7f;
			*data = (uint8_t *) &kws->buffer[2];
			return kws_close(kws, WS_RECV_CLOSE);
		}
		break;
	case WSOC_CONTINUATION:
	case WSOC_TEXT:
	case WSOC_BINARY:
	case WSOC_PING:
	case WSOC_PONG:
		{
			int fin = (kws->buffer[0] >> 7) & 1;
			int mask = (kws->buffer[1] >> 7) & 1;


			if (!fin && *oc != WSOC_CONTINUATION) {
				frag = 1;
			} else if (fin && *oc == WSOC_CONTINUATION) {
				frag = 0;
			}

			if (mask) {
				need += 4;

				if (need > kws->datalen) {
					ks_ssize_t bytes = kws_string_read_blocking(kws, kws->buffer + kws->datalen, need - kws->datalen + 1, 10);
					if (bytes < 0 || (kws->datalen += bytes) < need) {
						/* too small - protocol err */
						ks_log(KS_LOG_ERROR, "Read frame error because not enough data for mask\n");
						*oc = WSOC_CLOSE;
						return kws_close(kws, WS_NONE);
					}
				}
			}

			kws->plen = kws->buffer[1] & 0x7f;
			kws->payload = &kws->buffer[2];

			if (kws->plen == 127) {
				uint64_t *u64;
				ks_ssize_t more = 0;

				need += 8;

				if (need > kws->datalen) {
					ks_ssize_t bytes = kws_string_read_blocking(kws, kws->buffer + kws->datalen, need - kws->datalen + 1, 10);
					if (bytes < 0 || (kws->datalen += bytes) < need) {
						/* too small - protocol err */
						ks_log(KS_LOG_ERROR, "Read frame error because kws_string_read: more = %ld, need = %ld, datalen = %ld\n", more, need, kws->datalen);
						*oc = WSOC_CLOSE;
						return kws_close(kws, WS_NONE);
					}
				}

				u64 = (uint64_t *) kws->payload;
				kws->payload += 8;
				kws->plen = (ks_ssize_t)ntoh64(*u64);
			} else if (kws->plen == 126) {
				uint16_t *u16;

				need += 2;

				if (need > kws->datalen) {
					ks_ssize_t bytes = kws_string_read_blocking(kws, kws->buffer + kws->datalen, need - kws->datalen + 1, 10);
					if (bytes < 0 || (kws->datalen += bytes) < need) {
						/* too small - protocol err */
						ks_log(KS_LOG_ERROR, "Read frame error because kws_string_read: not enough data for packet length\n");
						*oc = WSOC_CLOSE;
						return kws_close(kws, WS_NONE);
					}
				}

				u16 = (uint16_t *) kws->payload;
				kws->payload += 2;
				kws->plen = ntohs(*u16);
			}

			if (mask) {
				maskp = (char *)kws->payload;
				kws->payload += 4;
			}

			need = (kws->plen - (kws->datalen - need));

			if (need < 0) {
				/* invalid read - protocol err .. */
				ks_log(KS_LOG_ERROR, "Read frame error because need = %ld\n", need);
				*oc = WSOC_CLOSE;
				return kws_close(kws, WS_NONE);
			}

			/* size already written to the body */
			blen = (int)(kws->body - kws->bbuffer);

			/* The bbuffer for the body of the message should always be 1 larger than the total size (for null term) */
			if (blen + kws->plen >= (ks_ssize_t)kws->bbuflen) {
				void *tmp;

				/* must be a sum of the size already written to the body (blen) plus the size to be written (kws->plen) */
				kws->bbuflen = blen + kws->plen; /* total size */

				/* and 1 more for NULL term */
				kws->bbuflen++;

				if (kws->payload_size_max && kws->bbuflen > kws->payload_size_max) {
					/* size limit */
					ks_log(KS_LOG_ERROR, "Read frame error because: payload length is too big\n");
					*oc = WSOC_CLOSE;
					return kws_close(kws, WS_NONE);
				}

				// make room for entire payload plus null terminator
				if ((tmp = ks_pool_resize(kws->bbuffer, (unsigned long)kws->bbuflen))) {
					kws->bbuffer = tmp;
				} else {
					abort();
				}

				kws->body = kws->bbuffer + blen;
			}

			kws->rplen = kws->plen - need;

			if (kws->rplen) {
				ks_assert((kws->body + kws->rplen) <= (kws->bbuffer + kws->bbuflen));
				memcpy(kws->body, kws->payload, kws->rplen);
			}

			ks_assert((kws->body + kws->plen) <= (kws->bbuffer + kws->bbuflen));

			while(need) {
				ks_ssize_t r = kws_string_read(kws, kws->body + kws->rplen, need + 1, WS_BLOCK);

				if (r < 1) {
					/* invalid read - protocol err .. */
					ks_log(KS_LOG_ERROR, "Read frame error because r = %ld\n", r);
					*oc = WSOC_CLOSE;
					return kws_close(kws, WS_NONE);
				}

				kws->datalen += r;
				kws->rplen += r;
				need -= r;
			}

			if (mask && maskp) {
				ks_ssize_t i;

				for (i = 0; i < kws->plen; i++) {
					kws->body[i] ^= maskp[i % 4];
				}
			}

			if (*oc == WSOC_TEXT) {
				*(kws->body+kws->rplen) = '\0';
			}

			kws->packetlen += kws->rplen;
			kws->body += kws->rplen;

			if (frag) {
				goto again;
			}

			*data = (uint8_t *)kws->bbuffer;

			//printf("READ[%ld][%d]-----------------------------:\n[%s]\n-------------------------------\n", kws->packetlen, *oc, (char *)*data);


			return kws->packetlen;
		}
		break;
	default:
		{
			/* invalid op code - protocol err .. */
			ks_log(KS_LOG_ERROR, "Read frame error because unknown opcode = %ld\n", *oc);
			*oc = WSOC_CLOSE;
			return kws_close(kws, WS_PROTO_ERR);
		}
		break;
	}
}

#if 0
static ks_ssize_t ws_feed_buf(kws_t *kws, void *data, ks_size_t bytes)
{

	if (bytes + kws->wdatalen > kws->buflen) {
		return -1;
	}

	memcpy(kws->wbuffer + kws->wdatalen, data, bytes);

	kws->wdatalen += bytes;

	return bytes;
}

static ks_ssize_t ws_send_buf(kws_t *kws, kws_opcode_t oc)
{
	ks_ssize_t r = 0;

	if (!kws->wdatalen) {
		return -1;
	}

	r = ws_write_frame(kws, oc, kws->wbuffer, kws->wdatalen);

	kws->wdatalen = 0;

	return r;
}
#endif

KS_DECLARE(ks_ssize_t) kws_write_frame(kws_t *kws, kws_opcode_t oc, const void *data, ks_size_t bytes)
{
	uint8_t hdr[14] = { 0 };
	ks_size_t hlen = 2;
	uint8_t *bp;
	ks_ssize_t raw_ret = 0;
	int mask = (kws->flags & KWS_FLAG_DONTMASK) ? 0 : 1;

	if (kws->down) {
		return -1;
	}

	//printf("WRITE[%ld]-----------------------------:\n[%s]\n-----------------------------------\n", bytes, (char *) data);

	hdr[0] = (uint8_t)(oc | 0x80);

	if (bytes < 126) {
		hdr[1] = (uint8_t)bytes;
	} else if (bytes < 0x10000) {
		uint16_t *u16;

		hdr[1] = 126;
		hlen += 2;

		u16 = (uint16_t *) &hdr[2];
		*u16 = htons((uint16_t) bytes);

	} else {
		uint64_t *u64;

		hdr[1] = 127;
		hlen += 8;

		u64 = (uint64_t *) &hdr[2];
		*u64 = hton64(bytes);
	}

	if (kws->write_buffer_len < (hlen + bytes + 1 + mask * 4)) {
		void *tmp;

		kws->write_buffer_len = hlen + bytes + 1 + mask * 4;
		if (!kws->write_buffer) kws->write_buffer = ks_pool_alloc(ks_pool_get(kws), (unsigned long)kws->write_buffer_len);
		else if ((tmp = ks_pool_resize(kws->write_buffer, (unsigned long)kws->write_buffer_len))) {
			kws->write_buffer = tmp;
		} else {
			abort();
		}
	}

	bp = (uint8_t *) kws->write_buffer;
	memcpy(bp, (void *) &hdr[0], hlen);

	if (mask) {
		ks_size_t i;
		uint8_t masking_key[4];

		gen_nonce(masking_key, 4);

		*(bp + 1) |= 0x80;
		memcpy(bp + hlen, masking_key, 4);
		hlen += 4;

		for (i = 0; i < bytes; i++) {
			*(bp + hlen + i) = (*((uint8_t *)data + i)) ^ (*(masking_key + (i % 4)));
		}
	} else {
		memcpy(bp + hlen, data, bytes);
	}

	raw_ret = kws_raw_write(kws, bp, (hlen + bytes));

	if (raw_ret <= 0 || raw_ret != (ks_ssize_t) (hlen + bytes)) {
		return raw_ret;
	}

	return bytes;
}

KS_DECLARE(ks_status_t) kws_get_buffer(kws_t *kws, char **bufP, ks_size_t *buflen)
{
	*bufP = kws->buffer;
	*buflen = kws->datalen;

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_size_t) kws_sans_count(kws_t *kws)
{
	ks_assert(kws);

	return kws->sans_count;
}

KS_DECLARE(const char *) kws_sans_get(kws_t *kws, ks_size_t index)
{
	ks_assert(kws);
	if (index >= kws->sans_count) return NULL;
	return kws->sans[index];
}

KS_DECLARE(ks_status_t) kws_connect(kws_t **kwsP, ks_json_t *params, kws_flag_t flags, ks_pool_t *pool)
{
	return kws_connect_ex(kwsP, params, flags, pool, NULL, 30000);
}

KS_DECLARE(ks_status_t) kws_connect_ex(kws_t **kwsP, ks_json_t *params, kws_flag_t flags, ks_pool_t *pool, SSL_CTX *ssl_ctx, uint32_t timeout_ms)
{
	ks_sockaddr_t addr = { 0 };
	ks_socket_t cl_sock = KS_SOCK_INVALID;
	int family = AF_INET;
	const char *ip = "127.0.0.1";
	ks_port_t port = 443;
	// char buf[50] = "";
	const char *url = ks_json_get_object_string(params, "url", NULL);
	// const char *headers = ks_json_get_object_string(params, "headers", NULL);
	const char *host = NULL;
	const char *protocol = ks_json_get_object_string(params, "protocol", NULL);
	const char *path = NULL;
	char *p = NULL;
	const char *client_data = NULL;
	int destroy_ssl_ctx = 0;
	ks_status_t status;

	if (!url) {
		ks_json_t *tmp;

		path = ks_json_get_object_string(params, "path", NULL);
		host = ks_json_get_object_string(params, "host", NULL);
		tmp = ks_json_get_object_item(params, "port");
		ks_json_value_number_int(tmp, (int *)&port);
	} else {
		if (!strncmp(url, "wss://", 6)) {
			if (!ssl_ctx) {
#if OPENSSL_VERSION_NUMBER >= 0x10100000
				ssl_ctx = SSL_CTX_new(TLS_client_method());
#else
				ssl_ctx = SSL_CTX_new(TLSv1_2_client_method());
#endif
				if (!ssl_ctx) {
					unsigned long ssl_ctx_error = ERR_peek_error();
					ks_log(KS_LOG_ERROR, "Failed to initiate SSL context with ssl error [%lu].\n", ssl_ctx_error);
					return KS_STATUS_FAIL;
				}

				SSL_CTX_set_default_verify_paths(ssl_ctx);

				destroy_ssl_ctx++;
			}

			p = (char *)url + 6;
		} else if (!strncmp(url, "ws://", 5))  {
			p = (char *)url + 5;
			port = 80;
		} else {
			*kwsP = NULL;
			return KS_STATUS_FAIL;
		}

		host = ks_pstrdup(pool, p);

		// if (*host == '[') // todo ipv6

		if ((p = strchr(host, ':'))) {
			*p++ = '\0';
			if (p) {
				port = (ks_port_t)atoi(p);
			}
		} else {
			p = (char *)host;
		}

		p = strchr(p, '/');

		if (p) {
			path = ks_pstrdup(pool, p);
			*p = '\0';
		} else {
			path = "/";
		}
	}

	if (!host || !path) {
		status = KS_STATUS_FAIL;
		goto err;
	}

	status = ks_addr_getbyname(host, port, AF_UNSPEC, &addr);
	if (status != KS_STATUS_SUCCESS) {
		goto err;
	}

	cl_sock = ks_socket_connect_ex(SOCK_STREAM, IPPROTO_TCP, &addr, timeout_ms);

	if (protocol) {
		client_data = ks_psprintf(pool, "%s:%s:%s", path, host, protocol);
	} else {
		client_data = ks_psprintf(pool, "%s:%s", path, host);
	}

	if (kws_init_ex(kwsP, cl_sock, ssl_ctx, client_data, flags, pool, params) != KS_STATUS_SUCCESS) {
		status = KS_STATUS_FAIL;
		goto err;
	}

	if (destroy_ssl_ctx) {
		(*kwsP)->destroy_ssl_ctx = 1;
	}

	return KS_STATUS_SUCCESS;

err:
	if (destroy_ssl_ctx) {
		SSL_CTX_free(ssl_ctx);
	}

	return status;
}

KS_DECLARE(int) kws_wait_sock(kws_t *kws, uint32_t ms, ks_poll_t flags)
{
	if (kws->sock == KS_SOCK_INVALID) return KS_POLL_ERROR;

	if (kws->unprocessed_buffer_len > 0) return KS_POLL_READ;

	if (kws->ssl && SSL_pending(kws->ssl) > 0) return KS_POLL_READ;

	return ks_wait_sock(kws->sock, ms, flags);
}

KS_DECLARE(int) kws_test_flag(kws_t *kws, kws_flag_t flag)
{
	return kws->flags & flag;
}
KS_DECLARE(int) kws_set_flag(kws_t *kws, kws_flag_t flag)
{
	return kws->flags |= flag;
}
KS_DECLARE(int) kws_clear_flag(kws_t *kws, kws_flag_t flag)
{
	return kws->flags &= ~flag;
}

/* clean the uri to protect us from vulnerability attack */
static ks_status_t clean_uri(char *uri)
{
	int argc;
	char *argv[64];
	int last, i, len, uri_len = 0;

	argc = ks_separate_string(uri, '/', argv, sizeof(argv) / sizeof(argv[0]));

	if (argc == sizeof(argv)) { /* too deep */
		return KS_STATUS_FAIL;
	}

	last = 1;
	for(i = 1; i < argc; i++) {
		if (*argv[i] == '\0' || !strcmp(argv[i], ".")) {
			/* ignore //// or /././././ */
		} else if (!strcmp(argv[i], "..")) {
			/* got /../, go up one level */
			if (last > 1) last--;
		} else {
			argv[last++] = argv[i];
		}
	}

	for(i = 1; i < last; i++) {
		len = strlen(argv[i]);
		sprintf(uri + uri_len, "/%s", argv[i]);
		uri_len += (len + 1);
	}

	if (*uri == '\0') {
		*uri++ = '/';
		*uri = '\0';
	}

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) kws_parse_qs(kws_request_t *request, char *qs)
{
	char *q;
	char *next;
	char *name, *val;

	if (qs) {
		q = qs;
	} else { /*parse our own qs, dup to avoid modify the original string */
		q = strdup(request->qs);
	}

	if (!q) return KS_STATUS_FAIL;

	next = q;

	while (q && request->total_headers < KWS_MAX_HEADERS) {
		char *p;

		if ((next = strchr(next, '&'))) {
			*next++ = '\0';
		}

		for (p = q; p && *p; p++) {
			if (*p == '+') *p = ' ';
		}

		ks_url_decode(q);

		name = q;
		if ((val = strchr(name, '='))) {
			*val++ = '\0';
			request->headers_k[request->total_headers] = strdup(name);
			request->headers_v[request->total_headers] = strdup(val);
			request->total_headers++;
		}
		q = next;
	}

	if (!qs) {
		ks_safe_free(q);
	}

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) kws_parse_header(kws_t *kws, kws_request_t **requestP)
{
	char *buffer = kws->buffer;
	ks_size_t datalen = kws->datalen;
	ks_status_t status = KS_STATUS_FAIL;
	char *p = (char *)buffer;
	int i = 10;
	char *http = NULL;
	int header_count;
	char *headers[64] = { 0 };
	int argc;
	char *argv[2] = { 0 };
	char *body = NULL;

	if (datalen < 16)	return status; /* minimum GET / HTTP/1.1\r\n */

	while(i--) { // sanity check
		if (*p++ == ' ') break;
	}

	if (i == 0) return status;

	if ((body = strstr(buffer, "\r\n\r\n"))) {
		*body = '\0';
		body += 4;
	} else if (( body = strstr(buffer, "\n\n"))) {
		*body = '\0';
		body += 2;
	} else {
		return status;
	}

	ks_assert(requestP);
	kws_request_t *request = *requestP;

	if (!request) request = malloc(sizeof(kws_request_t));
	if (!request) return status;
	memset(request, 0, sizeof(kws_request_t));
	*requestP = request;

	request->_buffer = strdup(buffer);
	ks_assert(request->_buffer);
	request->method = request->_buffer;
	request->bytes_buffered = datalen;
	request->bytes_header = body - buffer;
	request->bytes_read = body - buffer;

	p = strchr(request->method, ' ');

	if (!p) goto err;

	*p++ = '\0';

	if (*p != '/') goto err; /* must start from '/' */

	request->uri = p;
	p = strchr(request->uri, ' ');

	if (!p) goto err;

	*p++ = '\0';
	http = p;

	p = strchr(request->uri, '?');

	if (p) {
		*p++ = '\0';
		request->qs = p;
	}

	if (clean_uri((char *)request->uri) != KS_STATUS_SUCCESS) {
		goto err;
	}

	if (!strncmp(http, "HTTP/1.1", 8)) {
		request->keepalive = KS_TRUE;
	} else if (strncmp(http, "HTTP/1.0", 8)) {
		goto err;
	}

	p = strchr(http, '\n');

	if (p) {
		*p++ = '\0'; // now the first header
	} else {
		goto noheader;
	}

	header_count = ks_separate_string(p, '\n', headers, sizeof(headers)/ sizeof(headers[0]));

	if (header_count < 1) goto err;

	for (i = 0; i < header_count; i++) {
		char *header, *value;
		int len;

		argc = ks_separate_string(headers[i], ':', argv, 2);

		if (argc != 2) goto err;

		header = argv[0];
		value = argv[1];

		while (*value == ' ') value++;

		len = strlen(value);

		if (len && *(value + len - 1) == '\r') *(value + len - 1) = '\0';

		request->headers_k[i] = strdup(header);
		request->headers_v[i] = strdup(value);

		if (!strncasecmp(header, "User-Agent", 10)) {
			request->user_agent = value;
		} else if (!strncasecmp(header, "Host", 4)) {
			request->host = value;
			p = strchr(value, ':');

			if (p) {
				*p++ = '\0';

				if (*p) request->port = (ks_port_t)atoi(p);
			}
		} else if (!strncasecmp(header, "Content-Type", 12)) {
			request->content_type = value;
		} else if (!strncasecmp(header, "Content-Length", 14)) {
			request->content_length = atoi(value);
		} else if (!strncasecmp(header, "Referer", 7)) {
			request->referer = value;
		} else if (!strncasecmp(header, "Authorization", 7)) {
			request->authorization = value;
		}
	}

	request->total_headers = i;
	if (datalen >= body - buffer) {
		kws->datalen = datalen -= (body - buffer);
	}

	if (datalen > 0) {
		// shift remining bytes to start of buffer including ending '\0'
		memmove(buffer, body, datalen + 1);
		kws->unprocessed_buffer_len = datalen;
		kws->unprocessed_position = kws->buffer;
	}

noheader:

	if (request->qs) {
		kws_parse_qs(request, NULL);
	}

	return KS_STATUS_SUCCESS;

err:
	kws_request_free(requestP);
	return status;
}

KS_DECLARE(void) kws_request_free(kws_request_t **request)
{
	if (!request || !*request) return;
	kws_request_reset(*request);
	free(*request);
	*request = NULL;
}

KS_DECLARE(char *) kws_request_dump(kws_request_t *request)
{
	if (!request || !request->method) return NULL;

	printf("method: %s\n", request->method);

	if (request->uri) printf("uri: %s\n", request->uri);
	if (request->qs)  printf("qs: %s\n", request->qs);
	if (request->host) printf("host: %s\n", request->host);
	if (request->port) printf("port: %d\n", request->port);
	if (request->from) printf("from: %s\n", request->from);
	if (request->user_agent) printf("user_agent: %s\n", request->user_agent);
	if (request->referer) printf("referer: %s\n", request->referer);
	if (request->user) printf("user: %s\n", request->user);
	printf("keepalive: %d\n", request->keepalive);
	if (request->content_type) printf("content_type: %s\n", request->content_type);
	if (request->content_length) printf("content_length: %u\n", (uint32_t)request->content_length);
	if (request->authorization) printf("authorization: %s\n", request->authorization);

	printf("headers:\n-------------------------\n");

	int i;

	for (i = 0; i < KWS_MAX_HEADERS; i++) {
		if (!request->headers_k[i] || !request->headers_v[i]) break;
		printf("%s: %s\n", request->headers_k[i], request->headers_v[i]);
	}

	return NULL;
}

KS_DECLARE(void) kws_request_reset(kws_request_t *request)
{
	int i;

	if (!request) return;
	if (request->_buffer) {
		free(request->_buffer);
		request->_buffer = NULL;
	}

	for (i = 0; i < KWS_MAX_HEADERS; i++) {
		if (!request->headers_k[i] || !request->headers_v[i]) break;
		free((void *)request->headers_k[i]);
		request->headers_k[i] = NULL;
		free((void *)request->headers_v[i]);
		request->headers_v[i] = NULL;
	}

	request->total_headers = 0;
}

KS_DECLARE(ks_ssize_t) kws_read_buffer(kws_t *kws, uint8_t **data, ks_size_t bytes, int block)
{
	if (bytes > kws->buflen) {
		bytes = kws->buflen;
	}
	*data = kws->buffer;
	return kws_string_read(kws, kws->buffer, bytes, block);
}

KS_DECLARE(ks_status_t) kws_keepalive(kws_t *kws)
{
	ks_ssize_t bytes = 0;;
	kws->datalen = 0;

	while ((bytes = kws_string_read(kws, kws->buffer + kws->datalen, kws->buflen - kws->datalen, WS_BLOCK)) > 0) {
		kws->datalen += bytes;
		if (strstr(kws->buffer, "\r\n\r\n") || strstr(kws->buffer, "\n\n")) {
			return KS_STATUS_SUCCESS;
		}
	}

	return KS_STATUS_FAIL;
}

KS_DECLARE(const char *) kws_request_get_header(kws_request_t *request, const char *key)
{
	int i;

	for (i = 0; i < KWS_MAX_HEADERS; i++) {
		if (request->headers_k[i] && !strcmp(request->headers_k[i], key)) {
			return request->headers_v[i];
		}
	}

	return NULL;
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
