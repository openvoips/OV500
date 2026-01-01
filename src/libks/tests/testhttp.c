/*
 * Copyright (c) 2020-2021 SignalWire, Inc
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

#ifdef _WINDOWS_
#undef unlink
#define unlink _unlink
#endif

static char v4[48] = "";
static char v6[48] = "";
static int mask = 0;
static int tcp_port = 8090;


static char __MSG[] = "TESTING................................................................................/TESTING";


typedef struct ssl_profile_s {
	const SSL_METHOD *ssl_method;
	SSL_CTX *ssl_ctx;
	char cert[512];
	char key[512];
	char chain[512];
} ssl_profile_t;

static int init_ssl(ssl_profile_t *profile)
{
	const char *err = "";

	profile->ssl_ctx = SSL_CTX_new(profile->ssl_method);         /* create context */
	assert(profile->ssl_ctx);

	/* Disable SSLv2 */
	SSL_CTX_set_options(profile->ssl_ctx, SSL_OP_NO_SSLv2);
	/* Disable SSLv3 */
	SSL_CTX_set_options(profile->ssl_ctx, SSL_OP_NO_SSLv3);
	/* Disable TLSv1 */
	SSL_CTX_set_options(profile->ssl_ctx, SSL_OP_NO_TLSv1);
	/* Disable Compression CRIME (Compression Ratio Info-leak Made Easy) */
	SSL_CTX_set_options(profile->ssl_ctx, SSL_OP_NO_COMPRESSION);

	/* set the local certificate from CertFile */
	if (!ks_zstr(profile->chain)) {
		if (!SSL_CTX_use_certificate_chain_file(profile->ssl_ctx, profile->chain)) {
			err = "CERT CHAIN FILE ERROR";
			goto fail;
		}
	}

	if (!SSL_CTX_use_certificate_file(profile->ssl_ctx, profile->cert, SSL_FILETYPE_PEM)) {
		err = "CERT FILE ERROR";
		goto fail;
	}

	/* set the private key from KeyFile */

	if (!SSL_CTX_use_PrivateKey_file(profile->ssl_ctx, profile->key, SSL_FILETYPE_PEM)) {
		err = "PRIVATE KEY FILE ERROR";
		goto fail;
	}

	/* verify private key */
	if ( !SSL_CTX_check_private_key(profile->ssl_ctx) ) {
		err = "PRIVATE KEY FILE ERROR";
		goto fail;
	}

	SSL_CTX_set_cipher_list(profile->ssl_ctx, "HIGH:!DSS:!aNULL@STRENGTH");

	return 1;

 fail:
	ks_log(KS_LOG_ERROR, "SSL ERR: %s\n", err);

	return 0;

}

struct tcp_data {
	ks_socket_t sock;
	ks_sockaddr_t addr;
	int ready;
	char *ip;
	ks_pool_t *pool;
	int ssl;
	ssl_profile_t client_profile;
	ssl_profile_t server_profile;
};

void server_callback(ks_socket_t server_sock, ks_socket_t client_sock, ks_sockaddr_t *addr, void *user_data)
{
	struct tcp_data *tcp_data = (struct tcp_data *) user_data;
	ks_size_t bytes;
	kws_t *kws = NULL;
	kws_opcode_t oc;
	uint8_t *data;
	int keepalive = 0;
	kws_request_t *request = NULL;

	if (tcp_data->ssl) {
		tcp_data->server_profile.ssl_method = SSLv23_server_method();
		ks_set_string(tcp_data->server_profile.cert, "./testhttp.pem");
		ks_set_string(tcp_data->server_profile.key, "./testhttp.pem");
		ks_set_string(tcp_data->server_profile.chain, "./testhttp.pem");
		init_ssl(&tcp_data->server_profile);
	}

	printf("WS %s SERVER SOCK %d connection from %s:%u\n", tcp_data->ssl ? "SSL" : "PLAIN", (int)server_sock, addr->host, addr->port);

	int flags = KWS_BLOCK | KWS_STAY_OPEN | KWS_HTTP;

	if (kws_init(&kws, client_sock, tcp_data->server_profile.ssl_ctx, NULL, flags, tcp_data->pool) != KS_STATUS_SUCCESS) {
		printf("WS SERVER CREATE FAIL\n");
		goto end;
	}

	if (!kws_test_flag(kws, KWS_HTTP)) {
		printf("Non HTTP Request\n");
		goto end;
	}

new_req:

	if (kws_parse_header(kws, &request) != KS_STATUS_SUCCESS) {
		printf("Parse Header Error\n");
		goto end;
	}

	printf("request uri: %s\n", request->uri);
	kws_request_dump(request);

	if (!strncmp(request->method, "GET", 3)) {
		char data[512];
		ks_snprintf(data, sizeof(data),
			"HTTP/1.1 200 OK\r\n"
			"Content-Length: 0\r\n"
			"Content-Type: text/plain\r\n"
			"Allow: HEAD,GET,POST,PUT,DELETE,PATCH,OPTIONS\r\n"
			"Server: libks\r\n\r\n"
			"OK libks");
		kws_raw_write(kws, data, strlen(data));
		goto end;
	}

	if (request->content_length && request->content_length > 20) {
		char *data = "HTTP/1.1 413 Request Entity Too Large\r\n"
			"Content-Length: 0\r\n\r\n";
		kws_raw_write(kws, data, strlen(data));
		goto end;
	}

	if (!strncmp(request->method, "POST", 4) && request->content_length && request->content_type &&
		!strncmp(request->content_type, "application/x-www-form-urlencoded", 33)) {

		char buffer[1024];
		ks_ssize_t len = 0, bytes = 0;

		while(bytes < (ks_ssize_t)request->content_length) {
			len = request->content_length - bytes;

#define WS_BLOCK 1

			if ((len = kws_string_read(kws, buffer + bytes, len + 1, WS_BLOCK)) < 0) {
				printf("Read error %d\n", (int)len);
				goto end;
			}

			bytes += len;
		}

		*(buffer + bytes) = '\0';

		kws_parse_qs(request, buffer);
		kws_request_dump(request);
		const char *a = kws_request_get_header(request, "a");
		const char *x = kws_request_get_header(request, "x");
		const char *k = a ? "a" : "x";
		int clen = a ? strlen(a) : strlen(x);

		char data[512];
		ks_snprintf(data, sizeof(data),
			"HTTP/1.1 200 OK\r\n"
			"Content-Length: %d\r\n"
			"Content-Type: text/plain\r\n"
			"Allow: HEAD,GET,POST,PUT,DELETE,PATCH,OPTIONS\r\n"
			"Server: libks\r\n\r\n"
			"%s=%s", clen,  k, a ? a : x);
		kws_raw_write(kws, data, strlen(data));
		goto end;
	}

	if (!strncmp(request->method, "POST", 4) && request->content_length && request->content_type &&
		!strncmp(request->content_type, "application/json", 16)) {

		uint8_t *buffer;
		ks_ssize_t len = 1024;

		if ((len = kws_read_buffer(kws, &buffer, 1024, 1)) < 0) {
			printf("Read error %d\n", (int)len);
			goto end;
		}

		ks_assert(buffer);
		ks_json_t *json = ks_json_parse(buffer);
		ks_assert(json);
		char *s = ks_json_print(json);
		printf("Receved JSON: %s\n", s);

		char data[512];
		ks_snprintf(data, sizeof(data),
			"HTTP/1.1 200 OK\r\n"
			"Content-Length: %d\r\n"
			"Content-Type: text/plain\r\n"
			"Allow: HEAD,GET,POST,PUT,DELETE,PATCH,OPTIONS\r\n"
			"Server: libks\r\n\r\n"
			"%s", strlen(s), s);
		kws_raw_write(kws, data, strlen(data));
		goto end;
	}

 end:

	if (request) {
		keepalive = request->keepalive;
		printf("===================== free\n");
		kws_request_free(&request);
	}

	if (keepalive) {
		int pflags = kws_wait_sock(kws, 1000, KS_POLL_READ);
		if (pflags > 0 && (pflags & KS_POLL_READ)) {
			if (kws_keepalive(kws) == KS_STATUS_SUCCESS) {
				printf("socket is going to handle a new request\n");
				goto new_req;
			}
		}
	}

	ks_socket_close(&client_sock);

	kws_destroy(&kws);

	if (tcp_data->ssl) {
		SSL_CTX_free(tcp_data->server_profile.ssl_ctx);
	}

	printf("WS SERVER COMPLETE\n");
}

static void *tcp_sock_server(ks_thread_t *thread, void *thread_data)
{
	struct tcp_data *tcp_data = (struct tcp_data *) thread_data;

	tcp_data->ready = 1;
	ks_listen_sock(tcp_data->sock, &tcp_data->addr, 0, server_callback, tcp_data);

	printf("WS THREAD DONE\n");

	return NULL;
}

static int test_get(char *ip, int ssl)
{
	ks_thread_t *thread_p = NULL;
	ks_pool_t *pool;
	ks_sockaddr_t addr;
	int family = AF_INET;
	ks_socket_t cl_sock = KS_SOCK_INVALID;
	struct tcp_data tcp_data = { 0 };
	int r = 1, sanity = 100;
	kws_t *kws = NULL;

	ks_pool_open(&pool);

	tcp_data.pool = pool;

	if (ssl) {
		tcp_data.ssl = 1;
		tcp_data.client_profile.ssl_method = SSLv23_client_method();
		ks_set_string(tcp_data.client_profile.cert, "./testhttp.pem");
		ks_set_string(tcp_data.client_profile.key, "./testhttp.pem");
		ks_set_string(tcp_data.client_profile.chain, "./testhttp.pem");
		init_ssl(&tcp_data.client_profile);
	}

	if (strchr(ip, ':')) {
		family = AF_INET6;
	}

	if (ks_addr_set(&tcp_data.addr, ip, tcp_port, family) != KS_STATUS_SUCCESS) {
		r = 0;
		printf("WS CLIENT Can't set ADDR\n");
		goto end;
	}

	if ((tcp_data.sock = socket(family, SOCK_STREAM, IPPROTO_TCP)) == KS_SOCK_INVALID) {
		r = 0;
		printf("WS CLIENT Can't create sock family %d\n", family);
		goto end;
	}

	ks_socket_option(tcp_data.sock, SO_REUSEADDR, KS_TRUE);
	ks_socket_option(tcp_data.sock, TCP_NODELAY, KS_TRUE);

	tcp_data.ip = ip;

	ks_thread_create(&thread_p, tcp_sock_server, &tcp_data, pool);

	while(!tcp_data.ready && --sanity > 0) {
		ks_sleep(10000);
	}

	ks_addr_set(&addr, ip, tcp_port, family);
	cl_sock = ks_socket_connect(SOCK_STREAM, IPPROTO_TCP, &addr);

	printf("WS %s CLIENT SOCKET %d %s %d\n", ssl ? "SSL" : "PLAIN", (int)cl_sock, addr.host, addr.port);

	char data[512];
	ks_snprintf(data, sizeof(data),
		"GET / HTTP/1.1\r\n"
		"HOST: localhost\r\n\r\n");

	ks_size_t len = strlen(data);
	ks_socket_send(cl_sock, data, &len);
	ks_sleep_ms(200);

	char buffer[1024];
	len = sizeof(buffer);

	ks_socket_recv(cl_sock, buffer, &len);

	if (len < 100) {
		r = 0;
		printf("ERROR read\n");
	}

	printf("HTTP GET CLIENT READ %ld bytes [%s]\n", (long)len, buffer);
	if (strstr(buffer, "libks") == NULL) {
		r = 0;
	}

 end:

	kws_destroy(&kws);

	if (ssl) {
		SSL_CTX_free(tcp_data.client_profile.ssl_ctx);
	}

	if (tcp_data.sock != KS_SOCK_INVALID) {
		ks_socket_shutdown(tcp_data.sock, 2);
		ks_socket_close(&tcp_data.sock);
	}

	if (thread_p) {
		ks_thread_join(thread_p);
	}

	ks_socket_close(&cl_sock);

	ks_pool_close(&pool);

	return r;
}

static int test_post(char *ip)
{
	ks_thread_t *thread_p = NULL;
	ks_pool_t *pool;
	ks_sockaddr_t addr;
	int family = AF_INET;
	ks_socket_t cl_sock = KS_SOCK_INVALID;
	struct tcp_data tcp_data = { 0 };
	int r = 1, sanity = 100;
	kws_t *kws = NULL;

	ks_pool_open(&pool);

	tcp_data.pool = pool;

	if (strchr(ip, ':')) {
		family = AF_INET6;
	}

	if (ks_addr_set(&tcp_data.addr, ip, tcp_port, family) != KS_STATUS_SUCCESS) {
		r = 0;
		printf("WS CLIENT Can't set ADDR\n");
		goto end;
	}

	if ((tcp_data.sock = socket(family, SOCK_STREAM, IPPROTO_TCP)) == KS_SOCK_INVALID) {
		r = 0;
		printf("WS CLIENT Can't create sock family %d\n", family);
		goto end;
	}

	ks_socket_option(tcp_data.sock, SO_REUSEADDR, KS_TRUE);
	ks_socket_option(tcp_data.sock, TCP_NODELAY, KS_TRUE);

	tcp_data.ip = ip;

	ks_thread_create(&thread_p, tcp_sock_server, &tcp_data, pool);

	while(!tcp_data.ready && --sanity > 0) {
		ks_sleep(10000);
	}

	ks_addr_set(&addr, ip, tcp_port, family);
	cl_sock = ks_socket_connect(SOCK_STREAM, IPPROTO_TCP, &addr);

	printf("HTTP CLIENT SOCKET %d %s %d\n", (int)cl_sock, addr.host, addr.port);

	char data[512];
	char buffer[1024];
	const char *post_data = "a=1&b=2";
	ks_snprintf(data, sizeof(data),
		"POST /post HTTP/1.1\r\n"
		"Content-Length: %d\r\n"
		"Content-Type: application/x-www-form-urlencoded\r\n"
		"HOST: localhost\r\n\r\n"
		"%s", strlen(post_data), post_data);

	ks_size_t len = strlen(data);
	ks_socket_send(cl_sock, data, &len);
	ks_sleep_ms(200);
	len = sizeof(buffer);
	ks_socket_recv(cl_sock, buffer, &len);

	if (len < 100) {
		r = 0;
		printf("ERROR read\n");
	}

	printf("HTTP POST CLIENT READ %ld bytes [%s]\n", (long)len, buffer);

	if (strstr(buffer, "a=1") == NULL) {
		r = 0;
	}

 end:

	kws_destroy(&kws);

	if (tcp_data.sock != KS_SOCK_INVALID) {
		ks_socket_shutdown(tcp_data.sock, 2);
		ks_socket_close(&tcp_data.sock);
	}

	if (thread_p) {
		ks_thread_join(thread_p);
	}

	ks_socket_close(&cl_sock);

	ks_pool_close(&pool);

	return r;
}

static int test_post_json_read_buffer(char *ip)
{
	ks_thread_t *thread_p = NULL;
	ks_pool_t *pool;
	ks_sockaddr_t addr;
	int family = AF_INET;
	ks_socket_t cl_sock = KS_SOCK_INVALID;
	struct tcp_data tcp_data = { 0 };
	int r = 1, sanity = 100;
	kws_t *kws = NULL;

	ks_pool_open(&pool);

	tcp_data.pool = pool;

	if (strchr(ip, ':')) {
		family = AF_INET6;
	}

	if (ks_addr_set(&tcp_data.addr, ip, tcp_port, family) != KS_STATUS_SUCCESS) {
		r = 0;
		printf("WS CLIENT Can't set ADDR\n");
		goto end;
	}

	if ((tcp_data.sock = socket(family, SOCK_STREAM, IPPROTO_TCP)) == KS_SOCK_INVALID) {
		r = 0;
		printf("WS CLIENT Can't create sock family %d\n", family);
		goto end;
	}

	ks_socket_option(tcp_data.sock, SO_REUSEADDR, KS_TRUE);
	ks_socket_option(tcp_data.sock, TCP_NODELAY, KS_TRUE);

	tcp_data.ip = ip;

	ks_thread_create(&thread_p, tcp_sock_server, &tcp_data, pool);

	while(!tcp_data.ready && --sanity > 0) {
		ks_sleep(10000);
	}

	ks_addr_set(&addr, ip, tcp_port, family);
	cl_sock = ks_socket_connect(SOCK_STREAM, IPPROTO_TCP, &addr);

	printf("HTTP CLIENT SOCKET %d %s %d\n", (int)cl_sock, addr.host, addr.port);

	char data[512];
	char buffer[1024];
	const char *post_data = "{\"json\": true}";
	ks_snprintf(data, sizeof(data),
		"POST /json HTTP/1.1\r\n"
		"Content-Length: %d\r\n"
		"Content-Type: application/json\r\n"
		"HOST: localhost\r\n\r\n"
		"%s", strlen(post_data), post_data);

	ks_size_t len = strlen(data);
	ks_socket_send(cl_sock, data, &len);
	ks_sleep_ms(200);
	len = sizeof(buffer);
	ks_socket_recv(cl_sock, buffer, &len);

	if (len < 100) {
		r = 0;
		printf("ERROR read\n");
	}

	printf("HTTP POST CLIENT READ %ld bytes [%s]\n", (long)len, buffer);

	if (strstr(buffer, "json") == NULL) {
		r = 0;
	}

 end:

	kws_destroy(&kws);

	if (tcp_data.sock != KS_SOCK_INVALID) {
		ks_socket_shutdown(tcp_data.sock, 2);
		ks_socket_close(&tcp_data.sock);
	}

	if (thread_p) {
		ks_thread_join(thread_p);
	}

	ks_socket_close(&cl_sock);

	ks_pool_close(&pool);

	return r;
}

static int test_keepalive(char *ip)
{
	ks_thread_t *thread_p = NULL;
	ks_pool_t *pool;
	ks_sockaddr_t addr;
	int family = AF_INET;
	ks_socket_t cl_sock = KS_SOCK_INVALID;
	struct tcp_data tcp_data = { 0 };
	int r = 1, sanity = 100;
	kws_t *kws = NULL;

	ks_pool_open(&pool);

	tcp_data.pool = pool;

	if (strchr(ip, ':')) {
		family = AF_INET6;
	}

	if (ks_addr_set(&tcp_data.addr, ip, tcp_port, family) != KS_STATUS_SUCCESS) {
		r = 0;
		printf("WS CLIENT Can't set ADDR\n");
		goto end;
	}

	if ((tcp_data.sock = socket(family, SOCK_STREAM, IPPROTO_TCP)) == KS_SOCK_INVALID) {
		r = 0;
		printf("WS CLIENT Can't create sock family %d\n", family);
		goto end;
	}

	ks_socket_option(tcp_data.sock, SO_REUSEADDR, KS_TRUE);
	ks_socket_option(tcp_data.sock, TCP_NODELAY, KS_TRUE);

	tcp_data.ip = ip;

	ks_thread_create(&thread_p, tcp_sock_server, &tcp_data, pool);

	while(!tcp_data.ready && --sanity > 0) {
		ks_sleep(10000);
	}

	ks_addr_set(&addr, ip, tcp_port, family);
	cl_sock = ks_socket_connect(SOCK_STREAM, IPPROTO_TCP, &addr);

	printf("HTTP CLIENT SOCKET %d %s %d\n", (int)cl_sock, addr.host, addr.port);

	char data[512];
	char buffer[1024];
	const char *post_data = "a=1&b=2";
	ks_snprintf(data, sizeof(data),
		"POST /post HTTP/1.1\r\n"
		"Content-Length: %d\r\n"
		"Content-Type: application/x-www-form-urlencoded\r\n"
		"HOST: localhost\r\n\r\n"
		"%s", strlen(post_data), post_data);

	ks_size_t len = strlen(data);
	ks_socket_send(cl_sock, data, &len);
	ks_sleep_ms(200);
	len = sizeof(buffer);
	ks_socket_recv(cl_sock, buffer, &len);

	if (len < 100) {
		r = 0;
		printf("ERROR read\n");
	}

	printf("HTTP POST CLIENT READ %ld bytes [%s]\n", (long)len, buffer);

	if (strstr(buffer, "a=1") == NULL) {
		r = 0;
	}

	post_data = "x=1&y=2";
	ks_snprintf(data, sizeof(data),
		"POST /post HTTP/1.1\r\n"
		"Content-Length: %d\r\n"
		"Content-Type: application/x-www-form-urlencoded\r\n"
		"HOST: localhost\r\n\r\n"
		"%s", strlen(post_data), post_data);

	len = strlen(data);
	ks_socket_send(cl_sock, data, &len);
	ks_sleep_ms(200);
	len = sizeof(buffer);
	ks_socket_recv(cl_sock, buffer, &len);

	if (len < 100) {
		r = 0;
		printf("ERROR read\n");
	}

	printf("HTTP POST CLIENT READ %ld bytes [%s]\n", (long)len, buffer);

	if (strstr(buffer, "x=1") == NULL) {
		r = 0;
	}

 end:

	kws_destroy(&kws);

	if (tcp_data.sock != KS_SOCK_INVALID) {
		ks_socket_shutdown(tcp_data.sock, 2);
		ks_socket_close(&tcp_data.sock);
	}

	if (thread_p) {
		ks_thread_join(thread_p);
	}

	ks_socket_close(&cl_sock);

	ks_pool_close(&pool);

	return r;
}

int main(void)
{
	int have_v4 = 0, have_v6 = 0;
	ks_find_local_ip(v4, sizeof(v4), &mask, AF_INET, NULL);
	ks_find_local_ip(v6, sizeof(v6), NULL, AF_INET6, NULL);
	ks_init();

	printf("IPS: v4: [%s] v6: [%s]\n", v4, v6);

	have_v4 = ks_zstr_buf(v4) ? 0 : 1;
	// have_v6 = ks_zstr_buf(v6) ? 0 : 1;

	plan((have_v4 * 5) + (have_v6 * 4) + 1);

	ok(have_v4 || have_v6);

	if (have_v4 || have_v6) {
		ks_gen_cert(".", "testhttp.pem");
	}

	if (have_v4) {
		ok(test_get(v4, 0));
		ok(test_get(v4, 1));
		ok(test_post(v4));
		ok(test_keepalive(v4));
		ok(test_post_json_read_buffer(v4));
	}

	if (have_v6) {
		ok(test_get(v6, 0));
		ok(test_get(v6, 1));
		ok(test_post(v6));
		ok(test_keepalive(v6));
	}

	unlink("./testhttp.pem");
	ks_shutdown();

	done_testing();
}
