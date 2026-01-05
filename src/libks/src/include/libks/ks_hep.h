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

static const ks_port_t KS_HEPV3_DEFAULT_PORT = 9062; /* TCP */
static const uint32_t KS_HEPV3_DEFAULT_NODE_ID = 200;

typedef enum {
	KS_HEPV3_DIR_SEND = 0,
	KS_HEPV3_DIR_RECV = 1,
} ks_hepv3_direction_t;

typedef struct {
	int ip_family;
	char *local_ip;
	char *remote_ip;
	uint16_t local_port;
	uint16_t remote_port;
	ks_hepv3_direction_t direction;
	uint32_t capture_id;
	uint8_t protocol_type_id;
	char *payload;
	size_t payload_size;
} ks_hepv3_capture_params_t;

typedef struct {
	char *server;
	ks_port_t port;
	ks_bool_t use_tls;
	ks_pool_t *pool;
} ks_hepv3_socket_params_t;

typedef struct ks_hepv3_socket_s ks_hepv3_socket_t;

KS_DECLARE(ks_status_t) ks_hepv3_socket_init(ks_hepv3_socket_params_t *params, ks_hepv3_socket_t **out_hep_socketP);
KS_DECLARE(void) ks_hepv3_socket_destroy(ks_hepv3_socket_t **hep_socketP);
KS_DECLARE(ks_status_t) ks_hepv3_socket_write(ks_hepv3_socket_t *hepv3_socket, void *data, ks_size_t *bytes);
KS_DECLARE(ks_size_t) ks_hepv3_capture_create(const ks_hepv3_capture_params_t *hep_params, char **out_buffer);

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
