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

KS_BEGIN_EXTERN_C

typedef union{
	uint32_t v4;
	struct in6_addr v6;
} ks_ip_t;


#define switch_network_list_validate_ip(_list, _ip) switch_network_list_validate_ip_token(_list, _ip, NULL);

#define switch_test_subnet(_ip, _net, _mask) (_mask ? ((_net & _mask) == (_ip & _mask)) : _net ? _net == _ip : 1)

KS_DECLARE(int) ks_parse_cidr(const char *string, ks_ip_t *ip, ks_ip_t *mask, uint32_t *bitp);
KS_DECLARE(ks_status_t) ks_network_list_create(ks_network_list_t **list, const char *name, ks_bool_t default_type,
														   ks_pool_t *pool);
KS_DECLARE(ks_status_t) ks_network_list_add_cidr_token(ks_network_list_t *list, const char *cidr_str, ks_bool_t ok, const char *token);
#define ks_network_list_add_cidr(_list, _cidr_str, _ok) ks_network_list_add_cidr_token(_list, _cidr_str, _ok, NULL)

KS_DECLARE(char *) ks_network_ipv4_mapped_ipv6_addr(const char* ip_str);
KS_DECLARE(ks_status_t) ks_network_list_add_host_mask(ks_network_list_t *list, const char *host, const char *mask_str, ks_bool_t ok);
KS_DECLARE(ks_bool_t) ks_network_list_validate_ip_token(ks_network_list_t *list, uint32_t ip, const char **token);
KS_DECLARE(ks_bool_t) ks_network_list_validate_ip6_token(ks_network_list_t *list, ks_ip_t ip, const char **token);

#define ks_network_list_validate_ip(_list, _ip) ks_network_list_validate_ip_token(_list, _ip, NULL);

#define ks_test_subnet(_ip, _net, _mask) (_mask ? ((_net & _mask) == (_ip & _mask)) : _net ? _net == _ip : 1)

KS_DECLARE(ks_bool_t) ks_check_network_list_ip_cidr(const char *ip_str, const char *cidr_str);
KS_DECLARE(ks_bool_t) ks_check_network_list_ip_token(const char *ip_str, ks_network_list_t *list, const char **token);
#define ks_check_network_list_ip(_i, _l) ks_check_network_list_ip_token(_i, _l, NULL)

#define ks_inet_pton inet_pton

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

