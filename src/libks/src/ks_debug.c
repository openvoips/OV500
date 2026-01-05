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

#if KS_PLAT_MAC
#include <signal.h>
#endif

#if defined(HAVE_LIBBACKTRACE)
#include "backtrace.h"

static int __full_callback(void *data __attribute__((unused)), uintptr_t pc, const char *filename, int lineno, const char *function)
{
	ks_log(KS_LOG_CRIT, "0x%lx %s \t%s:%d\n", (unsigned long) pc,
		function == NULL ? "???" : function ,
		function == NULL ? "???" : filename, lineno);

	return (function != NULL && strcmp(function, "main") == 0) ? 1 : 0;
}

static void __error_callback(void *data, const char *msg, int errnum)
{
   ks_log(KS_LOG_CRIT, "Something went wrong in libbacktrace: %s\n", msg);
}

/**
 * ks_debug_dump_backtrace - Dumps the current callstack to a critical log
 * with ks_log.
 */
KS_DECLARE(void) ks_debug_dump_backtrace(void)
{
	struct backtrace_state *lbstate;
	lbstate = backtrace_create_state(NULL, 1, __error_callback, NULL);
	backtrace_full(lbstate, 0, __full_callback, __error_callback, 0);
}
#else
KS_DECLARE(void) ks_debug_dump_backtrace(void) { }
#endif

/**
 * This function will cause an attached debugger to break. In the case of
 * windows, it may cause a JIT debug session.
 */
KS_DECLARE(void) ks_debug_break(void)
{
#if KS_PLAT_WIN
	DebugBreak();
#else
	kill(getpid(), SIGINT);
#endif
}
