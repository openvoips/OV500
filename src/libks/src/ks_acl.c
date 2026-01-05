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

struct ks_network_node {
	ks_ip_t ip;
	ks_ip_t mask;
	uint32_t bits;
	int family;
	ks_bool_t ok;
	char *token;
	char *str;
	struct ks_network_node *next;
};
typedef struct ks_network_node ks_network_node_t;

struct ks_network_list {
	struct ks_network_node *node_head;
	ks_bool_t default_type;
	char *name;
};


KS_DECLARE(ks_status_t) ks_network_list_create(ks_network_list_t **list, const char *name, ks_bool_t default_type,
														   ks_pool_t *pool)
{
	ks_network_list_t *new_list;

	if (!pool) {
		ks_pool_open(&pool);
	}

	new_list = ks_pool_alloc(pool, sizeof(**list));
	new_list->default_type = default_type;
	new_list->name = ks_pstrdup(pool, name);

	*list = new_list;

	return KS_STATUS_SUCCESS;
}

#define IN6_AND_MASK(result, ip, mask) \
	((uint32_t *) (result))[0] =((const uint32_t *) (ip))[0] & ((const uint32_t *)(mask))[0]; \
	((uint32_t *) (result))[1] =((const uint32_t *) (ip))[1] & ((const uint32_t *)(mask))[1]; \
	((uint32_t *) (result))[2] =((const uint32_t *) (ip))[2] & ((const uint32_t *)(mask))[2]; \
	((uint32_t *) (result))[3] =((const uint32_t *) (ip))[3] & ((const uint32_t *)(mask))[3];
KS_DECLARE(ks_bool_t) ks_testv6_subnet(ks_ip_t _ip, ks_ip_t _net, ks_ip_t _mask) {
		if (!IN6_IS_ADDR_UNSPECIFIED(&_mask.v6)) {
			struct in6_addr a, b;
			IN6_AND_MASK(&a, &_net, &_mask);
			IN6_AND_MASK(&b, &_ip, &_mask);
			return !memcmp(&a,&b, sizeof(struct in6_addr));
		} else {
			if (!IN6_IS_ADDR_UNSPECIFIED(&_net.v6)) {
				return !memcmp(&_net,&_ip,sizeof(struct in6_addr));
			}
			else return KS_TRUE;
		}
}
KS_DECLARE(ks_bool_t) ks_network_list_validate_ip6_token(ks_network_list_t *list, ks_ip_t ip, const char **token)
{
	ks_network_node_t *node;
	ks_bool_t ok = list->default_type;
	uint32_t bits = 0;

	for (node = list->node_head; node; node = node->next) {
		if (node->family == AF_INET) continue;

		if (node->bits >= bits && ks_testv6_subnet(ip, node->ip, node->mask)) {
			if (node->ok) {
				ok = KS_TRUE;
			} else {
				ok = KS_FALSE;
			}

			bits = node->bits;

			if (token) {
				*token = node->token;
			}
		}
	}

	return ok;
}

KS_DECLARE(ks_bool_t) ks_network_list_validate_ip_token(ks_network_list_t *list, uint32_t ip, const char **token)
{
	ks_network_node_t *node;
	ks_bool_t ok = list->default_type;
	uint32_t bits = 0;

	for (node = list->node_head; node; node = node->next) {
		if (node->family == AF_INET6) continue; /* want AF_INET */
		if (node->bits >= bits && ks_test_subnet(ip, node->ip.v4, node->mask.v4)) {
			if (node->ok) {
				ok = KS_TRUE;
			} else {
				ok = KS_FALSE;
			}

			bits = node->bits;

			if (token) {
				*token = node->token;
			}
		}
	}

	return ok;
}

KS_DECLARE(char *) ks_network_ipv4_mapped_ipv6_addr(const char* ip_str)
{
	/* ipv4 mapped ipv6 address */

	if (strncasecmp(ip_str, "::ffff:", 7)) {
		return NULL;
	}

	return strdup(ip_str + 7);
}


KS_DECLARE(int) ks_parse_cidr(const char *string, ks_ip_t *ip, ks_ip_t *mask, uint32_t *bitp)
{
	char host[128];
	char *bit_str;
	int32_t bits;
	const char *ipv6;
	ks_ip_t *maskv = mask;
	ks_ip_t *ipv = ip;

	ks_copy_string(host, string, sizeof(host)-1);
	bit_str = strchr(host, '/');

	if (!bit_str) {
		return -1;
	}

	*bit_str++ = '\0';
	bits = atoi(bit_str);
	ipv6 = strchr(string, ':');
	if (ipv6) {
		int i,n;
		if (bits < 0 || bits > 128) {
			return -2;
		}
		bits = atoi(bit_str);
		ks_inet_pton(AF_INET6, host, (unsigned char *)ip);
		for (n=bits,i=0 ;i < 16; i++){
			if (n >= 8) {
				maskv->v6.s6_addr[i] = 0xFF;
				n -= 8;
			} else if (n < 8) {
				maskv->v6.s6_addr[i] = 0xFF & ~(0xFF >> n);
				n -= n;
			} else if (n == 0) {
				maskv->v6.s6_addr[i] = 0x00;
			}
		}
	} else {
		if (bits < 0 || bits > 32) {
			return -2;
		}

		bits = atoi(bit_str);
		ks_inet_pton(AF_INET, host, (unsigned char *)ip);
		ipv->v4 = htonl(ipv->v4);

		maskv->v4 = 0xFFFFFFFF & ~(0xFFFFFFFF >> bits);
	}
	*bitp = bits;

	return 0;
}




KS_DECLARE(ks_status_t) ks_network_list_perform_add_cidr_token(ks_network_list_t *list, const char *cidr_str, ks_bool_t ok,
																		   const char *token)
{
	ks_pool_t *pool = NULL;
	ks_ip_t ip, mask;
	uint32_t bits;
	ks_network_node_t *node;
	char *ipv4 = NULL;

	if ((ipv4 = ks_network_ipv4_mapped_ipv6_addr(cidr_str))) {
		cidr_str = ipv4;
	}

	if (ks_parse_cidr(cidr_str, &ip, &mask, &bits)) {
		ks_log(KS_LOG_ERROR, "Error Adding %s (%s) [%s] to list %s\n",
						  cidr_str, ok ? "allow" : "deny", ks_str_nil(token), list->name);
		ks_safe_free(ipv4);
		return KS_STATUS_GENERR;
	}

	pool = ks_pool_get(list);

	node = ks_pool_alloc(pool, sizeof(*node));

	node->ip = ip;
	node->mask = mask;
	node->ok = ok;
	node->bits = bits;
	node->str = ks_pstrdup(pool, cidr_str);

	if (strchr(cidr_str,':')) {
		node->family = AF_INET6;
	} else {
		node->family = AF_INET;
	}

	if (!ks_zstr(token)) {
		node->token = ks_pstrdup(pool, token);
	}

	node->next = list->node_head;
	list->node_head = node;

	ks_log(KS_LOG_NOTICE, "Adding %s (%s) [%s] to list %s\n",
					  cidr_str, ok ? "allow" : "deny", ks_str_nil(token), list->name);

	ks_safe_free(ipv4);
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_network_list_add_cidr_token(ks_network_list_t *list, const char *cidr_str, ks_bool_t ok, const char *token)
{
	char *cidr_str_dup = NULL;
	ks_status_t status = KS_STATUS_SUCCESS;

	if (strchr(cidr_str, ',')) {
		char *argv[32] = { 0 };
		int i, argc;
		cidr_str_dup = strdup(cidr_str);

		ks_assert(cidr_str_dup);
		if ((argc = ks_separate_string(cidr_str_dup, ',', argv, (sizeof(argv) / sizeof(argv[0]))))) {
			for (i = 0; i < argc; i++) {
				ks_status_t this_status;
				if ((this_status = ks_network_list_perform_add_cidr_token(list, argv[i], ok, token)) != KS_STATUS_SUCCESS) {
					status = this_status;
				}
			}
		}
	} else {
		status = ks_network_list_perform_add_cidr_token(list, cidr_str, ok, token);
	}

	ks_safe_free(cidr_str_dup);
	return status;
}

KS_DECLARE(ks_status_t) ks_network_list_add_host_mask(ks_network_list_t *list, const char *host, const char *mask_str, ks_bool_t ok)
{
	ks_pool_t *pool = NULL;
	ks_ip_t ip, mask;
	ks_network_node_t *node;

	ks_inet_pton(AF_INET, host, &ip);
	ks_inet_pton(AF_INET, mask_str, &mask);

	pool = ks_pool_get(list);

	node = ks_pool_alloc(pool, sizeof(*node));

	node->ip.v4 = ntohl(ip.v4);
	node->mask.v4 = ntohl(mask.v4);
	node->ok = ok;

	/* http://graphics.stanford.edu/~seander/bithacks.html */
	mask.v4 = mask.v4 - ((mask.v4 >> 1) & 0x55555555);
	mask.v4 = (mask.v4 & 0x33333333) + ((mask.v4 >> 2) & 0x33333333);
	node->bits = (((mask.v4 + (mask.v4 >> 4)) & 0xF0F0F0F) * 0x1010101) >> 24;

	node->str = ks_psprintf(pool, "%s:%s", host, mask_str);

	node->next = list->node_head;
	list->node_head = node;

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_bool_t) ks_check_network_list_ip_cidr(const char *ip_str, const char *cidr_str)
{
	ks_ip_t  ip, mask, net;
	uint32_t bits;
	char *ipv6 = strchr(ip_str,':');
	ks_bool_t ok = KS_FALSE;
	char *ipv4 = NULL;

	if ((ipv4 = ks_network_ipv4_mapped_ipv6_addr(ip_str))) {
		ip_str = ipv4;
		ipv6 = NULL;
	}

	if (ipv6) {
		ks_inet_pton(AF_INET6, ip_str, &ip);
	} else {
		ks_inet_pton(AF_INET, ip_str, &ip);
		ip.v4 = htonl(ip.v4);
	}

	if (ks_parse_cidr(cidr_str, &net, &mask, &bits) != 0) {
		goto done;
	}

	if (ipv6) {
		ok = ks_testv6_subnet(ip, net, mask);
	} else {
		ok = ks_test_subnet(ip.v4, net.v4, mask.v4);
	}

done:
	ks_safe_free(ipv4);

	return ok;
}

KS_DECLARE(ks_bool_t) ks_check_network_list_ip_token(const char *ip_str, ks_network_list_t *list, const char **token)
{
	ks_ip_t ip;
	char *ipv6 = strchr(ip_str,':');
	ks_bool_t ok = KS_FALSE;
	char *ipv4 = NULL;

	if ((ipv4 = ks_network_ipv4_mapped_ipv6_addr(ip_str))) {
		ip_str = ipv4;
		ipv6 = NULL;
	}

	if (ipv6) {
		ks_inet_pton(AF_INET6, ip_str, &ip);
	} else {
		ks_inet_pton(AF_INET, ip_str, &ip);
		ip.v4 = htonl(ip.v4);
	}


	if (ipv6) {
		ok = ks_network_list_validate_ip6_token(list, ip, token);
	} else {
		ok = ks_network_list_validate_ip_token(list, ip.v4, token);
	}

	ks_safe_free(ipv4);

	return ok;
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
