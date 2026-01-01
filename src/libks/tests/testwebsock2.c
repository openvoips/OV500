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

/*
	./testwebsock2 wss://echo.websocket.org/echo
	./testwebsock2 ws://laml:9393/websocket
*/

#include "libks/ks.h"
#include <tap.h>

#ifdef _WINDOWS_
#undef unlink
#define unlink _unlink
#endif

#define SHA1_HASH_SIZE 20
#define WEBSOCKET_GUID "258EAFA5-E914-47DA-95CA-C5AB0DC85B11"
#define B64BUFFLEN 1024
#define LISTEN_PORT 8090

#define __MSG "\"welcome\""

static const char c64[65] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

struct tcp_data {
	ks_socket_t sock;
	ks_sockaddr_t addr;
	int ready;
	char *ip;
};

static int b64encode(unsigned char *in, size_t ilen, unsigned char *out, size_t olen)
{
	int y=0,bytes=0;
	size_t x=0;
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

#ifdef NO_OPENSSL
static void sha1_digest(char *digest, unsigned char *in)
{
	SHA1Context sha;
	char *p;
	int x;

	SHA1Init(&sha);
	SHA1Update(&sha, in, strlen(in));
	SHA1Final(&sha, digest);
}

#else

static void sha1_digest(unsigned char *digest, char *in)
{
	SHA_CTX sha;

	SHA1_Init(&sha);
	SHA1_Update(&sha, in, strlen(in));
	SHA1_Final(digest, &sha);
}
#endif

static inline uint64_t BSwap64(uint64_t x) {
#if defined(__x86_64__)
  uint64_t swapped_bytes;
  __asm__ volatile("bswapq %0" : "=r"(swapped_bytes) : "0"(x));
  return swapped_bytes;
#elif defined(_MSC_VER)
  return (uint64_t)_byteswap_uint64(x);
#else   // generic code for swapping 64-bit values (suggested by bdb@)                                                                                          
  x = ((x & 0xffffffff00000000ull) >> 32) | ((x & 0x00000000ffffffffull) << 32);
  x = ((x & 0xffff0000ffff0000ull) >> 16) | ((x & 0x0000ffff0000ffffull) << 16);
  x = ((x & 0xff00ff00ff00ff00ull) >> 8) | ((x & 0x00ff00ff00ff00ffull) << 8);
  return x;
#endif
}

static uint64_t hton64(uint64_t val)
{
	if (KS_BIG_ENDIAN) return (val);
	else return BSwap64(val);
}

static uint64_t ntoh64(uint64_t val)
{
	if (KS_BIG_ENDIAN) return (val);
	else return BSwap64(val);
}

static ssize_t append_text_frame(void *bp)
{
	uint8_t hdr[14] = { 0 };
	size_t hlen = 2;
	ssize_t raw_ret = 0;
	char *data = __MSG;
	size_t bytes = strlen(data);

	hdr[0] = (uint8_t)(WSOC_TEXT | 0x80);

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

	memcpy(bp, (void *) &hdr[0], hlen);
	memcpy((unsigned char *)bp + hlen, data, bytes);
	*(uint8_t *)((unsigned char *)bp + hlen + bytes) = '\0';

	return hlen + bytes;
}

static void server_callback(ks_socket_t server_sock, ks_socket_t client_sock, ks_sockaddr_t *addr, void *user_data)
{
	//struct tcp_data *tcp_data = (struct tcp_data *) user_data;
	char buf[8192] = "";
	ks_status_t status;
	ks_size_t bytes;
	char key[1024] = "";
	char input[512] = "";
	unsigned char output[SHA1_HASH_SIZE] = "";
	char b64[256] = "";

	printf("TCP SERVER SOCK %d connection from %s:%u\n", (int)server_sock, addr->host, addr->port);

	do {
		bytes = sizeof(buf);;
		status = ks_socket_recv(client_sock, buf, &bytes);
		if (status != KS_STATUS_SUCCESS) {
			printf("TCP SERVER BAIL %s\n", strerror(ks_errno()));
			break;
		}
		printf("TCP SERVER READ %ld bytes [%s]\n", (long)bytes, buf);
	} while(ks_zstr_buf(buf) || !strstr(buf, "\r\n\r\n"));

	char *k = strstr(buf, "Sec-WebSocket-Key:");
	ks_assert(k);
	k += strlen("Sec-WebSocket-Key:");

	if (*k == ' ') k++;

	char *p = strchr(k, '\r');
	ks_assert(p);
	*p++ = '\0';

	strncpy(key, k, sizeof(key));

	snprintf(input, sizeof(input), "%s%s", key, WEBSOCKET_GUID);
	sha1_digest(output, input);
	b64encode((unsigned char *)output, SHA1_HASH_SIZE, (unsigned char *)b64, sizeof(b64));

	snprintf(buf, sizeof(buf),
			 "HTTP/1.1 101 Switching Protocols\r\n"
			 "Upgrade: websocket\r\n"
			 "Connection: Upgrade\r\n"
			 "Sec-WebSocket-Accept: %s\r\n\r\n",
			 b64);

	bytes = strlen(buf);
	bytes += append_text_frame(buf + strlen(buf));

	printf("%s\n", buf);

	ks_socket_send(client_sock, buf, &bytes);
	printf("TCP SERVER WRITE %ld bytes\n", (long)bytes);

	ks_socket_close(&client_sock);

	printf("TCP SERVER COMPLETE\n");
}

static void *tcp_sock_server(ks_thread_t *thread, void *thread_data)
{
	struct tcp_data *tcp_data = (struct tcp_data *) thread_data;

	tcp_data->ready = 1;
	ks_listen_sock(tcp_data->sock, &tcp_data->addr, 0, server_callback, tcp_data);

	printf("TCP THREAD DONE\n");

	return NULL;
}

static int test_ws(char *url);

static void start_tcp_server_and_test_ws(char *ip)
{
	ks_thread_t *thread_p = NULL;
	ks_pool_t *pool;
	int family = AF_INET;
	ks_socket_t cl_sock = KS_SOCK_INVALID;
	struct tcp_data tcp_data = { 0 };
	int sanity = 100;

	ks_pool_open(&pool);

	if (ks_addr_set(&tcp_data.addr, ip, LISTEN_PORT, family) != KS_STATUS_SUCCESS) {
		printf("TCP CLIENT Can't set ADDR\n");
		goto end;
	}

	if ((tcp_data.sock = socket(family, SOCK_STREAM, IPPROTO_TCP)) == KS_SOCK_INVALID) {
		printf("TCP CLIENT Can't create sock family %d\n", family);
		goto end;
	}

	ks_socket_option(tcp_data.sock, SO_REUSEADDR, KS_TRUE);
	ks_socket_option(tcp_data.sock, TCP_NODELAY, KS_TRUE);

	tcp_data.ip = ip;

	ks_thread_create(&thread_p, tcp_sock_server, &tcp_data, pool);

	while(!tcp_data.ready && --sanity > 0) {
		ks_sleep(10000);
	}

	char url[1024];
	snprintf(url, sizeof(url), "ws://127.0.0.1:%d/test", LISTEN_PORT);
	test_ws(url);

end:
	if (tcp_data.sock != KS_SOCK_INVALID) {
		ks_socket_shutdown(tcp_data.sock, 2);
		ks_socket_close(&tcp_data.sock);
	}

	if (thread_p) {
		ks_thread_join(thread_p);
	}

	ks_socket_close(&cl_sock);

	ks_pool_close(&pool);
}

static int test_ws(char *url)
{
	kws_t *kws = NULL;
	ks_pool_t *pool;
	kws_opcode_t oc;
	uint8_t *rdata;
	ks_json_t *req = ks_json_create_object();
	ks_json_add_string_to_object(req, "url", url);

	ks_json_t *headers = ks_json_create_array();
	ks_json_add_item_to_object(req, "headers", headers);
	ks_json_t *param = ks_json_create_object();
	ks_json_add_string_to_object(param, "key", "X-Auth-Token");
	ks_json_add_string_to_object(param, "value", "xxxx");
	ks_json_add_item_to_array(headers, param);
	param = ks_json_create_object();
	ks_json_add_string_to_object(param, "key", "Agent");
	ks_json_add_string_to_object(param, "value", "libks");
	ks_json_add_item_to_array(headers, param);

	ks_global_set_log_level(7);

	ks_pool_open(&pool);
	ks_assert(pool);

	ok(kws_connect_ex(&kws, req, KWS_BLOCK | KWS_CLOSE_SOCK, pool, NULL, 3000) == KS_STATUS_SUCCESS);
	printf("websocket connected to [%s]\n", url);
	ks_json_delete(&req);

	ks_ssize_t bytes;

	kws_write_frame(kws, WSOC_TEXT, __MSG, strlen(__MSG));

	int32_t poll_flags = 0;

	while (1) {
		poll_flags = kws_wait_sock(kws, 50, KS_POLL_READ | KS_POLL_ERROR);
		if (poll_flags == KS_POLL_READ) break;
	}

	bytes = kws_read_frame(kws, &oc, &rdata);
	printf("read bytes=%d oc=%d [%s]\n", (int)bytes, oc, (char *)rdata);

	ok(oc == WSOC_TEXT);
	if (bytes < 0 || oc != WSOC_TEXT || !rdata || !strstr((char *)rdata, "\"welcome\"")) {
		printf("read bytes=%d oc=%d [%s]\n", (int)bytes, oc, (char *)rdata);
	}

	ok(rdata != NULL && strstr((char *)rdata, __MSG) != NULL);

	kws_destroy(&kws);

	ks_pool_close(&pool);

	return 1;
}

int main(int argc, char *argv[])
{
	char *url = NULL;

	ks_init();
	ks_log_jsonify();

	plan(3);

	if (argc > 1 && strstr(argv[1], "ws") == argv[1]) {
		url = argv[1];
		test_ws(url);
	} else {
		start_tcp_server_and_test_ws("127.0.0.1");
	}

	ks_shutdown();

	done_testing();
}
