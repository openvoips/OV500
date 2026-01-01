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

#ifndef _KS_SOCKET_H_
#define _KS_SOCKET_H_

#include "ks.h"

#ifndef WIN32
#include <poll.h>
#endif

KS_BEGIN_EXTERN_C

#define KS_SO_NONBLOCK 2999

#ifdef WIN32
#define SHUT_RDWR SD_BOTH

static __inline int ks_errno(void)
{
	return WSAGetLastError();
}

static __inline int ks_errno_is_blocking(int errcode)
{
	return errcode == WSAEWOULDBLOCK || errcode == WSAEINPROGRESS || errcode == 35 || errcode == 730035;
}

static __inline int ks_errno_is_interupt(int errcode)
{
	return 0;
}

#else

static inline int ks_errno(void)
{
	return errno;
}

static inline int ks_errno_is_blocking(int errcode)
{
  return errcode == EAGAIN || errcode == EWOULDBLOCK || errcode == EINPROGRESS || errcode == EINTR || errcode == ETIMEDOUT || errcode == 35 || errcode == 730035;
}

static inline int ks_errno_is_interupt(int errcode)
{
	return errcode == EINTR;
}

#endif

static __inline int ks_socket_valid(ks_socket_t s) {
	return s != KS_SOCK_INVALID;
}

#define KS_SA_INIT {AF_INET};

KS_DECLARE(ks_status_t) ks_socket_send(ks_socket_t sock, void *data, ks_size_t *datalen);
KS_DECLARE(ks_status_t) ks_socket_recv(ks_socket_t sock, void *data, ks_size_t *datalen);
KS_DECLARE(ks_status_t) ks_socket_sendto(ks_socket_t sock, void *data, ks_size_t *datalen, ks_sockaddr_t *addr);
KS_DECLARE(ks_status_t) ks_socket_recvfrom(ks_socket_t sock, void *data, ks_size_t *datalen, ks_sockaddr_t *addr);

typedef struct pollfd *ks_ppollfd_t;
KS_DECLARE(int) ks_poll(ks_ppollfd_t fds, uint32_t nfds, int timeout);
KS_DECLARE(ks_status_t) ks_socket_option(ks_socket_t socket, int option_name, ks_bool_t enabled);
KS_DECLARE(ks_status_t) ks_socket_sndbuf(ks_socket_t socket, int bufsize);
KS_DECLARE(ks_status_t) ks_socket_rcvbuf(ks_socket_t socket, int bufsize);
KS_DECLARE(int) ks_wait_sock(ks_socket_t sock, uint32_t ms, ks_poll_t flags);

KS_DECLARE(ks_socket_t) ks_socket_connect(int type, int protocol, ks_sockaddr_t *addr);
KS_DECLARE(ks_socket_t) ks_socket_connect_ex(int type, int protocol, ks_sockaddr_t *addr, uint32_t nb_timeout);
KS_DECLARE(void) ks_socket_common_setup(ks_socket_t sock);
KS_DECLARE(ks_status_t) ks_addr_bind(ks_socket_t server_sock, const ks_sockaddr_t *addr);
KS_DECLARE(const char *) ks_addr_get_host(ks_sockaddr_t *addr);
KS_DECLARE(ks_port_t) ks_addr_get_port(ks_sockaddr_t *addr);
KS_DECLARE(int) ks_addr_cmp(const ks_sockaddr_t *sa1, const ks_sockaddr_t *sa2);
KS_DECLARE(ks_status_t) ks_addr_copy(ks_sockaddr_t *addr, const ks_sockaddr_t *src_addr);
KS_DECLARE(ks_status_t) ks_addr_set(ks_sockaddr_t *addr, const char *host, ks_port_t port, int family);
KS_DECLARE(ks_status_t) ks_addr_set_raw(ks_sockaddr_t *addr, const void *data, ks_port_t port, int family);
KS_DECLARE(ks_status_t) ks_addr_raw_data(const ks_sockaddr_t *addr, void **data, ks_size_t *datalen);
KS_DECLARE(ks_status_t) ks_listen(const char *host, ks_port_t port, int family, int backlog, ks_listen_callback_t callback, void *user_data);
KS_DECLARE(ks_status_t) ks_socket_shutdown(ks_socket_t sock, int how);
KS_DECLARE(ks_status_t) ks_socket_close(ks_socket_t *sock);
KS_DECLARE(ks_status_t) ks_ip_route(char *buf, int len, const char *route_ip);
KS_DECLARE(ks_status_t) ks_find_local_ip(char *buf, int len, int *mask, int family, const char *route_ip);
KS_DECLARE(ks_status_t) ks_listen_sock(ks_socket_t server_sock, ks_sockaddr_t *addr, int backlog, ks_listen_callback_t callback, void *user_data);
KS_DECLARE(ks_status_t) ks_addr_getbyname(const char *name, ks_port_t port, int family, ks_sockaddr_t *result);

KS_END_EXTERN_C

#endif /* defined(_KS_SOCKET_H_) */

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
