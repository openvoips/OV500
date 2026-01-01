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

#ifndef _KS_PLATFORM_H_
#define _KS_PLATFORM_H_

KS_BEGIN_EXTERN_C

#if !defined(KS_PLAT_LIN) && !defined(KS_PLAT_WIN) && !defined(KS_PLAT_MAC)
#ifdef __linux__
#define KS_PLAT_LIN
#else
#ifdef WIN32
#define KS_PLAT_WIN
#else
#ifdef __APPLE__
#define KS_PLAT_MAC
#endif
#endif
#endif
#endif

#if !defined(_XOPEN_SOURCE) && !defined(__FreeBSD__) && !defined(__NetBSD__) && !defined(__OpenBSD__) && !defined(__APPLE__)
#define _XOPEN_SOURCE 600
#endif

#if defined(__linux__) && !defined(_DEFAULT_SOURCE)
#define _DEFAULT_SOURCE 1
#endif

#if !defined(__WINDOWS__) && (defined(WIN32) || defined(WIN64) || defined(_MSC_VER) || defined(_WIN32))
#define __WINDOWS__
#define __KS_FUNC__ __FUNCTION__
#ifndef WIN32_LEAN_AND_MEAN
#define WIN32_LEAN_AND_MEAN
#endif
#ifndef _WINSOCK_DEPRECATED_NO_WARNINGS
#define _WINSOCK_DEPRECATED_NO_WARNINGS
#endif
#define _CRTDBG_MAP_ALLOC
#else
#define __KS_FUNC__ (const char *)__func__
#endif

#ifndef _GNU_SOURCE
#define _GNU_SOURCE
#endif

#include <stdint.h>

#if UINTPTR_MAX == 0xffffffffffffffff
#define KS_64BIT 1
#endif

#if (defined(__BYTE_ORDER) && defined(__LITTLE_ENDIAN) && \
     __BYTE_ORDER == __LITTLE_ENDIAN) || \
    (defined(i386) || defined(__i386__) || defined(__i486__) || \
     defined(__i586__) || defined(__i686__) || defined(vax) || defined(MIPSEL))
# define KS_LITTLE_ENDIAN 1
# define KS_BIG_ENDIAN 0
#elif (defined(__BYTE_ORDER) && defined(__BIG_ENDIAN) && \
       __BYTE_ORDER == __BIG_ENDIAN) || \
      (defined(sparc) || defined(POWERPC) || defined(mc68000) || defined(sel))
# define KS_LITTLE_ENDIAN 0
# define KS_BIG_ENDIAN 1
#else
# define KS_LITTLE_ENDIAN 0
# define KS_BIG_ENDIAN 0
#endif


#include <stdarg.h>
#include <time.h>
#include <stdarg.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <math.h>
#include <assert.h>
#include <errno.h>
#include <fcntl.h>
#include <ctype.h>
#include <float.h>
#include <limits.h>
#include <sys/types.h>

#ifndef __WINDOWS__
#include <sys/time.h>
#include <sys/select.h>
#include <netinet/tcp.h>
#include <sys/signal.h>
#include <unistd.h>
#include <strings.h>
#include <stdint.h>
#include <sys/ioctl.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <netdb.h>
#include <arpa/inet.h>
#include <sys/syscall.h>
#else
#include <rpc.h>
#endif

#ifdef _MSC_VER
#pragma comment(lib, "Ws2_32.lib")

#include <io.h>

#ifndef open
#define open _open
#endif

#ifndef close
#define close _close
#endif

#ifndef read
#define read _read
#endif

#ifndef write
#define write _write
#endif

#ifndef __inline__
#define __inline__ __inline
#endif

#if (_MSC_VER >= 1400)			/* VC8+ */
#ifndef _CRT_SECURE_NO_DEPRECATE
#define _CRT_SECURE_NO_DEPRECATE
#endif
#ifndef _CRT_NONSTDC_NO_DEPRECATE
#define _CRT_NONSTDC_NO_DEPRECATE
#endif
#endif

#ifndef strcasecmp
#define strcasecmp(s1, s2) _stricmp(s1, s2)
#endif

#ifndef strncasecmp
#define strncasecmp(s1, s2, n) _strnicmp(s1, s2, n)
#endif

#if (_MSC_VER < 1900)			/* VC 2015 */
#ifndef snprintf
#define snprintf _snprintf
#endif
#endif

#ifndef S_IRUSR
#define S_IRUSR _S_IREAD
#endif

#ifndef S_IWUSR
#define S_IWUSR _S_IWRITE
#endif

#endif  /* _MSC_VER */

#ifndef __WINDOWS__
#define KS_SOCK_INVALID -1
	typedef int ks_socket_t;
	typedef ssize_t ks_ssize_t;
	typedef int ks_filehandle_t;
#endif

#if defined(_MSC_VER) && defined(_WIN32)
#define KS_UNUSED
#else
#define KS_UNUSED __attribute__((unused))
#endif

#ifdef __WINDOWS__
#if defined(KS_DECLARE_STATIC)
#define KS_DECLARE(type)			type __stdcall
#define KS_DECLARE_NONSTD(type)		type __cdecl
#define KS_DECLARE_DATA
#elif defined(KS_EXPORTS)
#define KS_DECLARE(type)			__declspec(dllexport) type __stdcall
#define KS_DECLARE_NONSTD(type)		__declspec(dllexport) type __cdecl
#define KS_DECLARE_DATA				__declspec(dllexport)
#else
#define KS_DECLARE(type)			__declspec(dllimport) type __stdcall
#define KS_DECLARE_NONSTD(type)		__declspec(dllimport) type __cdecl
#define KS_DECLARE_DATA				__declspec(dllimport)
#endif
#else							// !WIN32
#if (defined(__GNUC__) || defined(__SUNPRO_CC) || defined (__SUNPRO_C)) && defined(KS_API_VISIBILITY)
#define KS_DECLARE(type)		__attribute__((visibility("default"))) type
#define KS_DECLARE_NONSTD(type)	__attribute__((visibility("default"))) type
#define KS_DECLARE_DATA		__attribute__((visibility("default")))
#else
#define KS_DECLARE(type) type
#define KS_DECLARE_NONSTD(type) type
#define KS_DECLARE_DATA
#endif
#endif

#ifndef __ATTR_SAL
	/* used for msvc code analysis */
	/* http://msdn2.microsoft.com/en-us/library/ms235402.aspx */
#define _In_
#define _In_z_
#define _In_opt_z_
#define _In_opt_
#define _Printf_format_string_
#define _Ret_opt_z_
#define _Ret_z_
#define _Out_opt_
#define _Out_
#define _Check_return_
#define _Inout_
#define _Inout_opt_
#define _In_bytecount_(x)
#define _Out_opt_bytecapcount_(x)
#define _Out_bytecapcount_(x)
#define _Ret_
#define _Post_z_
#define _Out_cap_(x)
#define _Out_z_cap_(x)
#define _Out_ptrdiff_cap_(x)
#define _Out_opt_ptrdiff_cap_(x)
#define _Post_count_(x)
#endif

KS_END_EXTERN_C

#ifdef __WINDOWS__
#ifndef _WIN32_WINNT
/* Restrict the server to a subset of Windows NT 4.0 header files by default
*/
//#define _WIN32_WINNT 0x0400
#endif
#ifndef NOUSER
#define NOUSER
#endif
#ifndef NOMCX
#define NOMCX
#endif
#ifndef NOIME
#define NOIME
#endif
#include <windows.h>
#include <mmsystem.h>
/*
* Add a _very_few_ declarations missing from the restricted set of headers
* (If this list becomes extensive, re-enable the required headers above!)
* winsock headers were excluded by WIN32_LEAN_AND_MEAN, so include them now
*/
#define SW_HIDE             0
#ifndef _WIN32_WCE
#include <winsock2.h>
#include <mswsock.h>
#pragma warning(push)
#pragma warning(disable : 4365)
#include <ws2tcpip.h>
#pragma warning(pop)
#else
#include <winsock.h>
#endif
#include <crtdbg.h>

typedef SOCKET ks_socket_t;
typedef intptr_t ks_ssize_t;
typedef int ks_filehandle_t;

#define KS_SOCK_INVALID INVALID_SOCKET
#define strerror_r(num, buf, size) strerror_s(buf, size, num)

#ifndef strdup
#define strdup _strdup
#endif
#endif

#endif							/* defined(_KS_PLATFORM_H_) */
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
