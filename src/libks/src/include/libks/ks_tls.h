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

#pragma once

#include "ks.h"

KS_BEGIN_EXTERN_C

#define KS_SSL_IO_ERROR(err) (err == SSL_ERROR_SYSCALL || err == SSL_ERROR_SSL)
#define KS_SSL_ERROR_WANT_READ_WRITE(err) (err == SSL_ERROR_WANT_READ || err == SSL_ERROR_WANT_WRITE)

typedef enum {
	KS_TLS_CLIENT,
	KS_TLS_SERVER
} ks_tls_type_t;

typedef enum {
	KS_TLS_VERIFY_USE_DEFAULT = 0,
	KS_TLS_VERIFY_ENABLED,
	KS_TLS_VERIFY_DISABLED
} ks_tls_verify_peer_t;

#define KS_TLS_DEFAULT_VERIRY_PEER KS_TLS_VERIFY_ENABLED

/* Hide shared SSL_CTX to prevent any modifications. */
typedef struct ks_tls_shared_ctx_s ks_tls_shared_ctx_t;
typedef struct ks_tls_s ks_tls_t;

typedef struct {
	char *host;
	ks_port_t port;

	uint32_t connect_timeout_ms;  /* Default equals KS_TLS_DEFAULT_CONN_TIMEOUT_MS */
	uint32_t init_timeout_ms;     /* Default equals KS_TLS_DEFAULT_INIT_TIMEOUT_MS */

	ks_tls_verify_peer_t verify_peer;
	ks_bool_t debug;

} ks_tls_connect_params_t;

typedef struct {
	ks_tls_shared_ctx_t *shared_ctx;
	ks_socket_t peer_socket;

	int family;
	uint32_t init_timeout_ms;  /* Default equals KS_TLS_DEFAULT_CONN_TIMEOUT_MS*/
} ks_tls_accept_params_t;

typedef struct {
	char *chain_file;
	char *cert_file;
	char *key_file;
	char *cipher_list;
	ks_bool_t debug;
} ks_tls_server_ctx_params_t;

typedef void (*ks_tls_init_callback_t)(ks_tls_t *ktls, SSL* ssl);

/* Client APIs */
KS_DECLARE(ks_status_t) ks_tls_connect(ks_tls_t **ktlsP, ks_tls_connect_params_t *params, ks_pool_t *pool);

/* Server APIs */
KS_DECLARE(ks_status_t) ks_tls_accept(ks_tls_t **ktlsP, ks_tls_accept_params_t *params, ks_pool_t *pool);
KS_DECLARE(ks_status_t) ks_tls_create_shared_server_ctx(ks_tls_shared_ctx_t **shared_ctxP, ks_tls_server_ctx_params_t *params, ks_pool_t *pool);
KS_DECLARE(ks_status_t) ks_tls_destroy_shared_server_ctx(ks_tls_shared_ctx_t **shared_ctxP);

/* Common APIs */
KS_DECLARE(ks_status_t) ks_tls_write(ks_tls_t *ktls, const void *data, ks_size_t *bytes);
KS_DECLARE(ks_status_t) ks_tls_write_timeout(ks_tls_t *ktls, void *data, ks_size_t *bytes, uint32_t timeout_ms);
KS_DECLARE(ks_status_t) ks_tls_read(ks_tls_t *ktls, void *data, ks_size_t *bytes);
KS_DECLARE(ks_status_t) ks_tls_read_timeout(ks_tls_t *ktls, void *data, ks_size_t *bytes, uint32_t timeout_ms);

KS_DECLARE(int) ks_tls_wait_sock(ks_tls_t *ktls, uint32_t ms, ks_poll_t flags);
KS_DECLARE(void) ks_tls_destroy(ks_tls_t **ktlsP);

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
