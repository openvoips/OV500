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

KS_BEGIN_EXTERN_C

/* HEPv3 types */

#if (defined __SUNPRO_CC) || defined(__SUNPRO_C) || defined(_MSC_VER)
#define PACKED
#endif
#ifndef PACKED
#define PACKED __attribute__ ((__packed__))
#endif

#ifdef _MSC_VER
#pragma pack(push, 1)
#endif

struct ks_hepv3_chunk {
	uint16_t vendor_id;
	uint16_t type_id;
	uint16_t length;
} PACKED;

typedef struct ks_hepv3_chunk ks_hepv3_chunk_t;

struct ks_hepv3_chunk_uint8 {
	ks_hepv3_chunk_t chunk;
	uint8_t data;
} PACKED;

typedef struct ks_hepv3_chunk_uint8 ks_hepv3_chunk_uint8_t;

struct ks_hepv3_chunk_uint16 {
	ks_hepv3_chunk_t chunk;
	uint16_t data;
} PACKED;

typedef struct ks_hepv3_chunk_uint16 ks_hepv3_chunk_uint16_t;

struct ks_hepv3_chunk_uint32 {
	ks_hepv3_chunk_t chunk;
	uint32_t data;
} PACKED;

typedef struct ks_hepv3_chunk_uint32 ks_hepv3_chunk_uint32_t;

struct ks_hepv3_chunk_str {
	ks_hepv3_chunk_t chunk;
	char *data;
} PACKED;

typedef struct ks_hepv3_chunk_str ks_hepv3_chunk_str_t;

struct ks_hepv3_chunk_ip4 {
	ks_hepv3_chunk_t chunk;
	struct in_addr data;
} PACKED;

typedef struct ks_hepv3_chunk_ip4 ks_hepv3_chunk_ip4_t;

struct ks_hepv3_chunk_ip6 {
	ks_hepv3_chunk_t chunk;
	struct in6_addr data;
} PACKED;

typedef struct ks_hepv3_chunk_ip6 ks_hepv3_chunk_ip6_t;

struct ks_hepv3_chunk_payload {
	ks_hepv3_chunk_t chunk;
	char *data;
} PACKED;

typedef struct ks_hepv3_chunk_payload ks_hepv3_chunk_payload_t;

struct ks_hepv3_ctrl {
	char id[4];
	uint16_t length;
} PACKED;

typedef struct ks_hepv3_ctrl ks_hepv3_ctrl_t;

struct ks_hepv3_generic {
	ks_hepv3_ctrl_t         header;
	ks_hepv3_chunk_uint8_t  ip_family;
	ks_hepv3_chunk_uint8_t  ip_proto;
	ks_hepv3_chunk_uint16_t src_port;
	ks_hepv3_chunk_uint16_t dst_port;
	ks_hepv3_chunk_uint32_t time_sec;
	ks_hepv3_chunk_uint32_t time_usec;
	ks_hepv3_chunk_uint8_t  proto_t;
	ks_hepv3_chunk_uint32_t capt_id;
} PACKED;

#ifdef _MSC_VER
#pragma pack(pop)
#endif

typedef struct ks_hepv3_generic ks_hepv3_generic_t;

struct ks_hepv3_socket_s {
	ks_socket_t raw_socket;
	ks_tls_t *tls_socket;
};

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
