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

#ifndef _KS_DEBUG_H_
#define _KS_DEBUG_H_

KS_BEGIN_EXTERN_C

#define ks_abort_fmt(fmt, ...)					\
		do {									\
			const char *msg = ks_thr_sprintf(fmt, __VA_ARGS__);	\
			ks_log(KS_LOG_CRIT, "\n\nABORT: (%s)\nLOCATION:%s %s:%d\nTHREAD ID: %" KS_PID_FMT "\n\n", msg, __KS_FUNC__, __FILE__, __LINE__, ks_thread_self_id()); \
			ks_log(KS_LOG_CRIT, "BACKTRACE:");	\
			ks_debug_dump_backtrace();			\
			abort();							\
		} while (KS_FALSE)

#define ks_abort(msg)							\
		do {									\
			ks_log(KS_LOG_CRIT, "\n\nABORT: (%s)\nLOCATION:%s %s:%d\nTHREAD ID: %" KS_PID_FMT "\n\n", msg, __KS_FUNC__, __FILE__, __LINE__, ks_thread_self_id()); \
			ks_log(KS_LOG_CRIT, "BACKTRACE:");	\
			ks_debug_dump_backtrace();			\
			abort();							\
		} while (KS_FALSE)

#define ks_assertd(expr)					\
	do {									\
		if (!(expr)) {						\
			ks_abort_fmt(					\
				"ASSERTION FAILURE '%s'",	\
				#expr						\
			);								\
		}									\
	} while (KS_FALSE)

#if NDEBUG
	#define ks_assert(expr)
#else
	#define ks_assert(expr) ks_assertd(expr)
#endif

KS_DECLARE(void) ks_debug_dump_backtrace(void);

KS_DECLARE(void) ks_debug_break(void);

KS_END_EXTERN_C

#endif
