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

#ifdef __cplusplus
#define KS_BEGIN_EXTERN_C       extern "C" {
#define KS_END_EXTERN_C         }
#else
#define KS_BEGIN_EXTERN_C
#define KS_END_EXTERN_C
#endif

/* libks version as a string */
#define KS_VERSION "2.0.8"

/* libks version as a number */
#define KS_VERSION_NUM 20008

/* Use this to allow enabling TCP_KEEPIDLE and TCP_KEEPINTVL socket options */
//#define KS_KEEP_IDLE_INTVL 1

#include "libks/ks_platform.h"
#include "libks/ks_types.h"

/*
 * bitflag tools
 */
#define KS_BIT_FLAG(x)		(1 << (x))
#define KS_BIT_SET(v,f)		((v) |= (f))
#define KS_BIT_CLEAR(v,f)	((v) &= ~(f))
#define KS_BIT_IS_SET(v,f)	((v) & (f))
#define KS_BIT_TOGGLE(v,f)	((v) ^= (f))

KS_BEGIN_EXTERN_C

KS_DECLARE(ks_status_t) ks_init(void);
KS_DECLARE(ks_status_t) ks_shutdown(void);
KS_DECLARE(ks_pool_t *) ks_global_pool(void);

KS_END_EXTERN_C

#include "libks/ks_log.h"
#include "libks/ks_env.h"
#include "libks/ks_string.h"
#include "libks/ks_printf.h"
#include "libks/ks_json.h"
#include "libks/ks_json_check.h"
#include "libks/ks_json_schema.h"
#include "libks/ks_pool.h"
#include "libks/ks_threadmutex.h"
#include "libks/ks_debug.h"
#include "libks/ks_json.h"
#include "libks/ks_thread_pool.h"
#include "libks/ks_hash.h"
#include "libks/ks_config.h"
#include "libks/ks_q.h"
#include "libks/ks_buffer.h"
#include "libks/ks_time.h"
#include "libks/ks_socket.h"
#include "libks/ks_dso.h"
#include "libks/simclist.h"
#include "libks/ks_ssl.h"
#include "libks/kws.h"
#include "libks/ks_tls.h"
#include "libks/ks_hep.h"
#include "libks/ks_uuid.h"
#include "libks/ks_acl.h"
#include "libks/ks_base64.h"
#include "libks/ks_time.h"
#include "libks/ks_sb.h"
#include "libks/ks_utf8.h"
#include "libks/ks_atomic.h"
#include "libks/ks_metrics.h"

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
