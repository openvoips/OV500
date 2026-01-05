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

#pragma once

#define WEBSOCKET_GUID "258EAFA5-E914-47DA-95CA-C5AB0DC85B11"
#define B64BUFFLEN 1024
#define KWS_MAX_HEADERS 64

KS_BEGIN_EXTERN_C

typedef enum {
	WS_NONE = 0,
	WS_RECV_CLOSE = 1000,
	WS_PROTO_ERR = 1002,
	WS_DATA_TOO_BIG = 1009
} kws_cause_t;

#define WS_NORMAL_CLOSE 1000

typedef enum {
	WSOC_CONTINUATION = 0x0,
	WSOC_TEXT = 0x1,
	WSOC_BINARY = 0x2,
	WSOC_CLOSE = 0x8,
	WSOC_PING = 0x9,
	WSOC_PONG = 0xA,
	WSOC_INVALID = 0xF	/* Per the rfc this is the highest reserved valuel treat it as invalid */
} kws_opcode_t;

typedef enum {
	KWS_CLIENT,
	KWS_SERVER
} kws_type_t;

typedef enum {
	KWS_NONE = 0,
	KWS_CLOSE_SOCK = (1 << 0),
	KWS_BLOCK = (1 << 1),
	KWS_STAY_OPEN = (1 << 2),
	KWS_FLAG_DONTMASK = (1 << 3),
	KWS_HTTP = (1 << 4) /* fallback to HTTP */
} kws_flag_t;

typedef struct kws_request_s {
	const char *method;        /* GET POST PUT DELETE OPTIONS PATCH HEAD */
	const char *uri;
	const char *qs;            /* query string*/
	const char *host;
	ks_port_t port;
	const char *from;
	const char *user_agent;
	const char *referer;
	const char *user;
	ks_bool_t keepalive;
	const char *content_type;
	const char *authorization;
	ks_size_t content_length;
	ks_size_t bytes_header;
	ks_size_t bytes_read;
	ks_size_t bytes_buffered;
	const char *headers_k[KWS_MAX_HEADERS];
	const char *headers_v[KWS_MAX_HEADERS];
	ks_size_t total_headers;
	void *user_data;           /* private user data */

	/* private members used by the parser internally */
	char *_buffer;
} kws_request_t;


struct kws_s;
typedef struct kws_s kws_t;

typedef void (*kws_init_callback_t)(kws_t *kws, SSL* ssl);

KS_DECLARE(ks_ssize_t) kws_read_frame(kws_t *kws, kws_opcode_t *oc, uint8_t **data);
KS_DECLARE(ks_ssize_t) kws_write_frame(kws_t *kws, kws_opcode_t oc, const void *data, ks_size_t bytes);
KS_DECLARE(ks_ssize_t) kws_raw_read(kws_t *kws, void *data, ks_size_t bytes, int block);
KS_DECLARE(ks_ssize_t) kws_raw_write(kws_t *kws, void *data, ks_size_t bytes);
KS_DECLARE(ks_status_t) kws_init_ex(kws_t **kwsP, ks_socket_t sock, SSL_CTX *ssl_ctx, const char *client_data, kws_flag_t flags, ks_pool_t *pool, ks_json_t *params);
#define kws_init(kwsP, sock, ssl_ctx, client_data, flags, pool) kws_init_ex(kwsP, sock, ssl_ctx, client_data, flags, pool, NULL)
KS_DECLARE(ks_ssize_t) kws_string_read(kws_t *kws, char *str_buffer, ks_size_t buffer_size, int block);
KS_DECLARE(ks_ssize_t) kws_close(kws_t *kws, int16_t reason);
KS_DECLARE(ks_status_t) kws_create(kws_t **kwsP, ks_pool_t *pool);
KS_DECLARE(void) kws_destroy(kws_t **kwsP);
KS_DECLARE(void) kws_set_init_callback(kws_t *kws, kws_init_callback_t callback);
KS_DECLARE(ks_status_t) kws_connect(kws_t **kwsP, ks_json_t *params, kws_flag_t flags, ks_pool_t *pool);
KS_DECLARE(ks_status_t) kws_connect_ex(kws_t **kwsP, ks_json_t *params, kws_flag_t flags, ks_pool_t *pool, SSL_CTX *ssl_ctx, uint32_t timeout_ms);
KS_DECLARE(ks_status_t) kws_get_buffer(kws_t *kws, char **bufP, ks_size_t *buflen);
KS_DECLARE(ks_status_t) kws_get_cipher_name(kws_t *kws, char *name, ks_size_t name_len);
KS_DECLARE(ks_bool_t) kws_certified_client(kws_t *kws);
KS_DECLARE(ks_size_t) kws_sans_count(kws_t *kws);
KS_DECLARE(const char *) kws_sans_get(kws_t *kws, ks_size_t index);
KS_DECLARE(int) kws_wait_sock(kws_t *kws, uint32_t ms, ks_poll_t flags);
KS_DECLARE(int) kws_test_flag(kws_t *kws, kws_flag_t);
KS_DECLARE(int) kws_set_flag(kws_t *kws, kws_flag_t);
KS_DECLARE(int) kws_clear_flag(kws_t *kws, kws_flag_t);
/**
 * parse http headers in a buffer
 * return status of success or not
 * \param[in]	buffer the buffer start from the very begining of the http request, e.g. 'GET '
 * \param[in]	datalen the buffer length
 * \param[out]	the http request pointer or null, need destroy later if got non-NULL pointer
 * \return	SWITCH_STATUS_SUCCESS | SWITCH_STATUS_FALSE
 */
KS_DECLARE(ks_status_t) kws_parse_header(kws_t *kws, kws_request_t **request);
KS_DECLARE(void) kws_request_free(kws_request_t **request);
KS_DECLARE(void) kws_request_reset(kws_request_t *request);
/**
 * \param[in] request the request
 * \return    the returned pointer must be freed externally
 */
KS_DECLARE(char *) kws_request_dump(kws_request_t *request);
KS_DECLARE(ks_status_t) kws_parse_qs(kws_request_t *request, char *qs);
KS_DECLARE(ks_ssize_t) kws_read_buffer(kws_t *kws, uint8_t **data, ks_size_t bytes, int block);
KS_DECLARE(ks_status_t) kws_keepalive(kws_t *kws);
KS_DECLARE(const char *) kws_request_get_header(kws_request_t *request, const char *key);

KS_END_EXTERN_C

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
