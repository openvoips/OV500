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
#include <tap.h>

typedef struct  {
	int family;
	ks_pool_t *pool;
	ks_sockaddr_t server_addr;
	ks_socket_t server_socket;
	ks_bool_t ready;
	ks_tls_shared_ctx_t *shared_ctx;
} tls_data_t;

static const int TLS_TEST_PORT = 8090;
static const uint32_t TLS_TEST_READ_TIMEOUT_MS = 5000;
static const uint32_t TLS_TEST_WRITE_TIMEOUT_MS = 5000;
static char TLS_TEST_PAYLOAD[] = "TESTING.TLS.PAYLOAD...............................................................................TESTING.TLS.PAYLOAD.END";

#define TLS_TEST_READ_BUF_LEN 1024
#define TLS_TEST_SERVER_CERT "testtls_server.pem"

void server_callback(ks_socket_t server_sock, ks_socket_t client_sock, ks_sockaddr_t *addr, void *user_data)
{
	ks_status_t status;
	ks_tls_t *ktls = NULL;
	ks_tls_accept_params_t params = {0};
	tls_data_t *tls_data = (tls_data_t *) user_data;
	uint8_t read_buf[TLS_TEST_READ_BUF_LEN] = {0};
	ks_size_t read_chunk_size = TLS_TEST_READ_BUF_LEN;
	ks_size_t read_bytes = 0;

	ks_log(KS_LOG_DEBUG, "SERVER: Accepting client from %s:%u [%d]...\n", addr->host, addr->port, client_sock);

	params.shared_ctx = tls_data->shared_ctx;
	params.family = tls_data->family;
	params.init_timeout_ms = 2000;
	params.peer_socket = client_sock;

	status = ks_tls_accept(&ktls, &params, tls_data->pool);

	if (status != KS_STATUS_SUCCESS) {
		ks_log(KS_LOG_ERROR, "SERVER: Accept fail\n");
		goto end;
	}

	do {
		uint8_t *read_p = read_buf + read_bytes;
		// read_chunk_size = TLS_TEST_READ_BUF_LEN - read_bytes;
		read_chunk_size = 30; /* Force segmented reads */

		status =  ks_tls_read_timeout(ktls, read_p, &read_chunk_size, TLS_TEST_READ_TIMEOUT_MS);

		if (status != KS_STATUS_SUCCESS) {
			ks_log(KS_LOG_ERROR, "SERVER: Read fail - ks_status: [%d] TLS status: [%s]\n", status, strerror(ks_errno()));
			goto end;
		}

		read_bytes += read_chunk_size;

		ks_log(KS_LOG_DEBUG, "SERVER: Read [%ld] bytes\n", (long)read_chunk_size);

	} while(read_bytes < sizeof(TLS_TEST_PAYLOAD));

	if (strncmp((char *)read_buf, TLS_TEST_PAYLOAD, sizeof(TLS_TEST_PAYLOAD))) {
		ks_log(KS_LOG_ERROR, "SERVER: Payload mismatch\n");
		goto end;
	}

	if (ks_tls_write_timeout(ktls, read_buf, &read_bytes, TLS_TEST_WRITE_TIMEOUT_MS) != KS_STATUS_SUCCESS) {
		ks_log(KS_LOG_ERROR, "SERVER: Write fail\n");
	}

	ks_log(KS_LOG_DEBUG, "SERVER: Wrote [%ld] bytes\n", (long)read_bytes);

 end:

	ks_tls_destroy(&ktls);
	ks_log(KS_LOG_DEBUG, "SERVER: Callback complete\n");
}

static void *tcp_sock_server(ks_thread_t *thread, void *thread_data)
{
	tls_data_t *tls_data = (tls_data_t *) thread_data;
	ks_tls_server_ctx_params_t ctx_params = {0};
	ks_tls_shared_ctx_t *shared_ctx = NULL;


	ctx_params.chain_file = "./" TLS_TEST_SERVER_CERT;
	ctx_params.cert_file = "./" TLS_TEST_SERVER_CERT;
	ctx_params.key_file = "./" TLS_TEST_SERVER_CERT;
	// ctx_params.debug = KS_TRUE;

	if (ks_tls_create_shared_server_ctx(&shared_ctx, &ctx_params, tls_data->pool) != KS_STATUS_SUCCESS) {
		ks_log(KS_LOG_DEBUG, "CAN'T CREATE SHARED SERVER CONTEXT\n");
		return NULL;
	}

	tls_data->shared_ctx = shared_ctx;
	tls_data->ready = 1;

	ks_listen_sock(tls_data->server_socket, &tls_data->server_addr, 0, server_callback, tls_data);

	ks_tls_destroy_shared_server_ctx(&shared_ctx);

	ks_log(KS_LOG_DEBUG, "TLS SERVER THREAD DONE\n");

	return NULL;
}

static int test_tls(char *ip)
{
	int result = 0;
	ks_socket_t server_sock = KS_SOCK_INVALID;
	int family = strchr(ip, ':') ? AF_INET6 : AF_INET;
	tls_data_t tls_data = {0};
	ks_pool_t *pool = NULL;
	ks_thread_t *thread_p = NULL;
	int sanity = 100;
	ks_tls_t *ktls = NULL;
	ks_tls_connect_params_t client_params = {0};
	ks_status_t status = KS_STATUS_FAIL;
	ks_size_t write_bytes = sizeof(TLS_TEST_PAYLOAD);
	char data[1024] = {0};
	ks_size_t read_bytes = 1024;

	if (ks_addr_set(&tls_data.server_addr, ip, TLS_TEST_PORT, family) != KS_STATUS_SUCCESS) {
		ks_log(KS_LOG_ERROR, "TSL CLIENT Can't set ADDR\n");
		goto end;
	}

	server_sock = socket(family, SOCK_STREAM, IPPROTO_TCP);

	if (server_sock == KS_SOCK_INVALID) {
		ks_log(KS_LOG_ERROR, "TLS CLIENT Can't create sock family %d\n", family);
		goto end;
	}

	ks_pool_open(&pool);

	tls_data.pool = pool;
	tls_data.family = family;
	tls_data.server_socket = server_sock;

	ks_socket_option(tls_data.server_socket, TCP_NODELAY, KS_TRUE);

	ks_thread_create(&thread_p, tcp_sock_server, &tls_data, pool);

	while(!tls_data.ready && --sanity > 0) {
		ks_sleep_ms(10);
	}

	/* Client connection */
	client_params.host = ip;
	client_params.port = TLS_TEST_PORT;
	client_params.verify_peer = KS_TLS_VERIFY_DISABLED;
	client_params.init_timeout_ms = 5000;
	client_params.connect_timeout_ms = 5000;
	// client_params.debug = KS_TRUE;

	/* Create a couple of connections. */
	for (int i = 0; i < 2; i ++) {
		if (ks_tls_connect(&ktls, &client_params, pool) != KS_STATUS_SUCCESS) {
			ks_log(KS_LOG_ERROR, "TLS CLIENT Can't connect to host [%s]\n", ip);
			goto end;
		}

		if (ks_tls_write_timeout(ktls, TLS_TEST_PAYLOAD, &write_bytes, TLS_TEST_WRITE_TIMEOUT_MS) != KS_STATUS_SUCCESS) {
			ks_log(KS_LOG_ERROR, "TLS CLIENT Can't write [%s]\n", ip);
			goto end;
		}

		if (ks_tls_read_timeout(ktls, data, &read_bytes, TLS_TEST_READ_TIMEOUT_MS) != KS_STATUS_SUCCESS) {
			ks_log(KS_LOG_ERROR, "TLS CLIENT Can't read\n");
			goto end;
		}

		ks_log(KS_LOG_DEBUG, "TLS CLIENT Wrote [%ld] bytes\n", (long)write_bytes);
		ks_log(KS_LOG_DEBUG, "TLS CLIENT Read [%ld] bytes\n", (long)read_bytes);

		if (strncmp(data, TLS_TEST_PAYLOAD, sizeof(TLS_TEST_PAYLOAD))) {
			goto end;
		}

		ks_tls_destroy(&ktls);
	}

	result = 1;

end:
	ks_tls_destroy(&ktls);

	if (server_sock != KS_SOCK_INVALID) {
		ks_socket_shutdown(server_sock, SHUT_RDWR);
		ks_socket_close(&server_sock);
	}

	if (thread_p) {
		ks_thread_join(thread_p);
	}

	if (pool) {
		ks_pool_close(&pool);
	}
	return result;
}

int main(void)
{
	int have_v4, have_v6;
	char v4[48] = "";
	char v6[48] = "";
	int mask = 0;

	ks_init();
	ks_global_set_log_level(7);

	ks_find_local_ip(v4, sizeof(v4), &mask, AF_INET, NULL);
	ks_find_local_ip(v6, sizeof(v6), NULL, AF_INET6, NULL);

	ks_log(KS_LOG_DEBUG, "IPS: v4: [%s] v6: [%s]\n", v4, v6);

	have_v4 = ks_zstr_buf(v4) ? 0 : 1;
	have_v6 = ks_zstr_buf(v6) ? 0 : 1;

	plan(have_v4 + have_v6 + 1);

	ok(have_v4 || have_v6);

	if (have_v4 || have_v6) {
		ks_gen_cert(".", TLS_TEST_SERVER_CERT);
	}

	if (have_v4) {
		ok(test_tls(v4));
	}

	if (have_v6) {
		ok(test_tls(v6));
	}

	unlink("./" TLS_TEST_SERVER_CERT);
	ks_shutdown();

	done_testing();
}
