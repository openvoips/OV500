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
 *
 * Based on HEPv3 implementation in sofia-sip library by Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 */

#include "libks/ks.h"
#include "libks/internal/ks_hep.h"

#define KS_HEPV3_CONNECT_TIMIEOUT_MS 10000
#define KS_HEPV3_WRITE_TIMIEOUT_MS 10000

#define KS_HEPV3_HEADER_ID  "\x48\x45\x50\x33"

static const uint16_t KS_HEPV3_VENDOR_ID_GENERIC = 0x0000;

static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_IP_FAMILY      = 0x0001;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_IP_PROTO       = 0x0002;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_SRC_IP4        = 0x0003;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_DST_IP4        = 0x0004;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_SRC_IP6        = 0x0005;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_DST_IP6        = 0x0006;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_SRC_PORT       = 0x0007;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_DST_PORT       = 0x0008;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_TIMESTAMP_SEC  = 0x0009;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_TIMESTAMP_USEC = 0x000a;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_PROTO_TYPE     = 0x000b;  /* SIP/RTP/H323... */
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_AGENT_ID       = 0x000c;
static const uint16_t KS_HEPV3_GENERIC_TYPE_ID_PAYLOAD        = 0x000f;


KS_DECLARE(ks_size_t) ks_hepv3_capture_create(const ks_hepv3_capture_params_t *hep_params, char **out_buffer)
{
	ks_hepv3_generic_t *hg = NULL;
	ks_hepv3_chunk_ip4_t src_ip4 = {{0}}, dst_ip4 = {{0}};
	ks_hepv3_chunk_ip6_t src_ip6 = {{0}}, dst_ip6 = {{0}};
	ks_hepv3_chunk_t payload_chunk;

	size_t buflen = 0, iplen = 0, total_len = 0;
	ks_time_t now_usec = 0, now_sec = 0;

	uint8_t is_send;

	ks_assert(hep_params);
	ks_assert(out_buffer);

	if (!hep_params->payload || !hep_params->payload_size) {
		ks_log(KS_LOG_ERROR, "hepv3: Empty payload.\n");

		return 0;
	}

	is_send = (hep_params->direction == KS_HEPV3_DIR_SEND);

	/* Buffer for ethernet frame */
	hg = malloc(sizeof(ks_hepv3_generic_t));
	if (!hg) {
		ks_log(KS_LOG_ERROR, "hepv3: No memory for generic header.\n");

		return 0;
	}

	memset(hg, 0, sizeof(ks_hepv3_generic_t));

	/* Header set */
	memcpy(hg->header.id, KS_HEPV3_HEADER_ID, 4);

	/* IP proto */
	hg->ip_family.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->ip_family.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_IP_FAMILY);
	hg->ip_family.chunk.length = htons(sizeof(hg->ip_family));
	hg->ip_family.data = hep_params->ip_family;

	/* Proto ID */
	hg->ip_proto.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->ip_proto.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_IP_PROTO);
	hg->ip_proto.chunk.length = htons(sizeof(hg->ip_proto));
	hg->ip_proto.data = IPPROTO_TCP;


	/* Src port */
	hg->src_port.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->src_port.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_SRC_PORT);
	hg->src_port.chunk.length = htons(sizeof(hg->src_port));
	hg->src_port.data = htons(is_send ? hep_params->local_port : hep_params->remote_port);

	/* Dst port */
	hg->dst_port.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->dst_port.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_DST_PORT);
	hg->dst_port.chunk.length = htons(sizeof(hg->dst_port));
	hg->dst_port.data = htons(is_send ? hep_params->remote_port : hep_params->local_port);

	now_usec = ks_time_now();
	now_sec = now_usec / 1000000;

	/* Timestamp sec */
	hg->time_sec.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->time_sec.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_TIMESTAMP_SEC);
	hg->time_sec.chunk.length = htons(sizeof(hg->time_sec));
	hg->time_sec.data = htonl((uint32_t)now_sec);

	/* Timestamp usec */
	hg->time_usec.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->time_usec.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_TIMESTAMP_USEC);
	hg->time_usec.chunk.length = htons(sizeof(hg->time_usec));
	hg->time_usec.data = htonl((uint32_t)(now_usec - (now_sec * 1000000)));

	/* Protocol type */
	hg->proto_t.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->proto_t.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_PROTO_TYPE);
	hg->proto_t.chunk.length = htons(sizeof(hg->proto_t));
	hg->proto_t.data = hep_params->protocol_type_id;

	/* Capture ID */
	hg->capt_id.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	hg->capt_id.chunk.type_id   = htons(KS_HEPV3_GENERIC_TYPE_ID_AGENT_ID);
	hg->capt_id.chunk.length = htons(sizeof(hg->capt_id));
	hg->capt_id.data = htonl(hep_params->capture_id);

	/* Copy destination and source IPs */
	if(hep_params->ip_family == AF_INET) {
		struct in_addr local_addr = {0};
		struct in_addr remote_addr = {0};

		local_addr.s_addr = inet_addr(hep_params->local_ip);
		remote_addr.s_addr = inet_addr(hep_params->remote_ip);

		/* Src IP */
		src_ip4.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
		src_ip4.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_SRC_IP4);
		src_ip4.chunk.length = htons(sizeof(src_ip4));
		src_ip4.data = is_send ? local_addr : remote_addr;

		/* Dst IP */
		dst_ip4.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
		dst_ip4.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_DST_IP4);
		dst_ip4.chunk.length = htons(sizeof(dst_ip4));
		dst_ip4.data = is_send ? remote_addr : local_addr;

		iplen = sizeof(dst_ip4) + sizeof(src_ip4);
	} else {
		struct in6_addr local_addr = {0};
		struct in6_addr remote_addr = {0};

		inet_pton(AF_INET6, hep_params->local_ip, &local_addr);
		inet_pton(AF_INET6, hep_params->remote_ip, &remote_addr);

		/* Src IPv6 */
		src_ip6.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
		src_ip6.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_SRC_IP6);
		src_ip6.chunk.length = htons(sizeof(src_ip6));
		memcpy(&src_ip6.data, is_send ? &local_addr : &remote_addr, sizeof(struct in6_addr));

		/* Dst IPv6 */
		dst_ip6.chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
		dst_ip6.chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_DST_IP6);
		dst_ip6.chunk.length = htons(sizeof(dst_ip6));
		memcpy(&dst_ip6.data, is_send ? &remote_addr : &local_addr, sizeof(struct in6_addr));

		iplen = sizeof(dst_ip6) + sizeof(src_ip6);
	}

	/* Payload */
	payload_chunk.vendor_id = htons(KS_HEPV3_VENDOR_ID_GENERIC);
	payload_chunk.type_id = htons(KS_HEPV3_GENERIC_TYPE_ID_PAYLOAD);
	payload_chunk.length = htons((uint16_t)(sizeof(payload_chunk) + hep_params->payload_size));

	total_len = sizeof(ks_hepv3_generic_t) + hep_params->payload_size + iplen + sizeof(ks_hepv3_chunk_t);

	/* Total */
	hg->header.length = htons((uint16_t)total_len);

	/* Fill the output buffer */

	*out_buffer = malloc(total_len);

	if (*out_buffer == NULL){
		ks_log(KS_LOG_ERROR, "hepv3: No memory for buffer\n");
		goto done;
	}

	/* Generic chunk */
	memcpy((void*) *out_buffer, hg, sizeof(ks_hepv3_generic_t));
	buflen = sizeof(ks_hepv3_generic_t);

	/* IP addresses chunks */
	if(hep_params->ip_family == AF_INET) {
		/* Src IPv4 */
		memcpy(*out_buffer + buflen, &src_ip4, sizeof(ks_hepv3_chunk_ip4_t));
		buflen += sizeof(ks_hepv3_chunk_ip4_t);

		memcpy(*out_buffer + buflen, &dst_ip4, sizeof(ks_hepv3_chunk_ip4_t));
		buflen += sizeof(ks_hepv3_chunk_ip4_t);
	} else {
		/* Src IPv6 */
		memcpy(*out_buffer + buflen, &src_ip6, sizeof(ks_hepv3_chunk_ip6_t));
		buflen += sizeof(ks_hepv3_chunk_ip6_t);

		memcpy(*out_buffer + buflen, &dst_ip6, sizeof(ks_hepv3_chunk_ip6_t));
		buflen += sizeof(ks_hepv3_chunk_ip6_t);
	}

	/* Payload chunk (header) */
	memcpy(*out_buffer + buflen, &payload_chunk,  sizeof(ks_hepv3_chunk_t));
	buflen += sizeof(ks_hepv3_chunk_t);

	/* Payload itself */
	memcpy(*out_buffer + buflen, hep_params->payload, hep_params->payload_size);
	buflen += hep_params->payload_size;

done:
	if (hg) {
		free(hg);
	}

	return buflen;
}

static ks_status_t ks_hepv3_socket_init_tls(ks_hepv3_socket_params_t *params, ks_hepv3_socket_t **out_hep_socketP)
{
	ks_hepv3_socket_t *hep_sock = NULL;
	ks_tls_t *ktls = NULL;
	ks_tls_connect_params_t tls_conn_params = { 0 };

	*out_hep_socketP = NULL;
	tls_conn_params.host = params->server;
	tls_conn_params.port = params->port;
	tls_conn_params.connect_timeout_ms = KS_HEPV3_CONNECT_TIMIEOUT_MS;
	tls_conn_params.init_timeout_ms = KS_HEPV3_CONNECT_TIMIEOUT_MS;

	hep_sock = ks_pool_alloc(params->pool, sizeof(ks_hepv3_socket_t));

	if (!hep_sock) {
		return KS_STATUS_ALLOC;
	}

	if (ks_tls_connect(&ktls, &tls_conn_params, params->pool) != KS_STATUS_SUCCESS) {
		ks_pool_free(&hep_sock);

		return KS_STATUS_GENERR;
	};

	hep_sock->raw_socket = KS_SOCK_INVALID;
	hep_sock->tls_socket = ktls;
	*out_hep_socketP = hep_sock;

	return KS_STATUS_SUCCESS;
}

static ks_status_t ks_hepv3_socket_init_raw(ks_hepv3_socket_params_t *params, ks_hepv3_socket_t **out_hep_socketP)
{
	ks_hepv3_socket_t *hep_sock = NULL;
	ks_sockaddr_t capt_addr = {0};
	ks_socket_t tcp_socket = KS_SOCK_INVALID;

	*out_hep_socketP = NULL;

	if (ks_addr_getbyname(params->server, params->port, AF_UNSPEC, &capt_addr) != KS_STATUS_SUCCESS) {
		ks_log(KS_LOG_ERROR, "Can't resolve host [%s]!\n", params->server);

		return KS_STATUS_FAIL;
	}

	hep_sock = ks_pool_alloc(params->pool, sizeof(ks_hepv3_socket_t));

	if (!hep_sock) {
		return KS_STATUS_ALLOC;
	}

	tcp_socket = ks_socket_connect_ex(SOCK_STREAM, IPPROTO_TCP, &capt_addr, KS_HEPV3_CONNECT_TIMIEOUT_MS);

	if (tcp_socket == KS_SOCK_INVALID) {
		ks_log(KS_LOG_ERROR, "Can't connect to [%s]!\n", capt_addr.host);
		ks_pool_free(&hep_sock);

		return KS_STATUS_FAIL;
	}

	ks_socket_common_setup(tcp_socket);

	hep_sock->raw_socket = tcp_socket;
	hep_sock->tls_socket = NULL;
	*out_hep_socketP = hep_sock;

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_hepv3_socket_init(ks_hepv3_socket_params_t *params, ks_hepv3_socket_t **out_hep_socketP)
{
	ks_assert(params);
	ks_assert(out_hep_socketP);

	if (!params->server || !params->port || !params->pool) {
		ks_log(KS_LOG_ERROR, "hepv3: Required argument isn't set. Server [%p], Port [%d], Pool [%p]. \n", params->server, params->port, params->pool);

		return KS_STATUS_ARG_NULL;
	}

	if (params->use_tls) {
		return ks_hepv3_socket_init_tls(params, out_hep_socketP);
	} else {
		return ks_hepv3_socket_init_raw(params, out_hep_socketP);
	}
}

KS_DECLARE(void) ks_hepv3_socket_destroy(ks_hepv3_socket_t **hep_socketP)
{
	ks_hepv3_socket_t *hep_socket;

	if (!hep_socketP || !*hep_socketP) {
		ks_log(KS_LOG_WARNING, "hepv3: Invalid destroy params.\n");

		return;
	}

	hep_socket = *hep_socketP;

	*hep_socketP = NULL;

	if (hep_socket->tls_socket) {
		ks_tls_destroy(&hep_socket->tls_socket);
	} else {
		ks_socket_shutdown(hep_socket->raw_socket, SHUT_RDWR);
		ks_socket_close(&hep_socket->raw_socket);
	}

	ks_pool_free(&hep_socket);
}

KS_DECLARE(ks_status_t) ks_hepv3_socket_write(ks_hepv3_socket_t *hep_socket, void *data, ks_size_t *bytes)
{
	if (!hep_socket || !bytes || !*bytes) {
		ks_log(KS_LOG_ERROR, "hepv3: Invalid write params.\n");

		return KS_STATUS_ARG_NULL;
	}

	if (hep_socket->tls_socket) {
		return ks_tls_write_timeout(hep_socket->tls_socket, data, bytes, KS_HEPV3_WRITE_TIMIEOUT_MS);
	} else {
		return ks_socket_send(hep_socket->raw_socket, data, bytes);
	}
}


/* For Emacs:
* Local Variables:
* mode:c
* indent-tabs-mode:t
* tab-width:4
* c-basic-offset:4
* End:
* For VIM:
* vim:set softtabstop=4 shiftwidth=4 tabstop=4 noet
*/
