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

#ifndef _KS_TIME_H_
#define _KS_TIME_H_

#include "ks.h"

KS_BEGIN_EXTERN_C

#define KS_USEC_PER_SEC		1000000	 /* number of micro seconds in a second */
#define KS_USEC_PER_MSEC	1000	 /* number of micro seconds in a millisecond */

#define ks_time_sec(time) ((time) / KS_USEC_PER_SEC)
#define ks_time_ms(time) ((time) / KS_USEC_PER_MSEC)
#define ks_time_usec(time) ((time) % KS_USEC_PER_SEC)
#define ks_time_nsec(time) (((time) % KS_USEC_PER_SEC) * 1000)
#define ks_sleep_ms(_t) ks_sleep(_t * 1000)

KS_DECLARE(void) ks_time_init(void);
KS_DECLARE(ks_time_t) ks_time_now(void);
KS_DECLARE(ks_time_t) ks_time_now_sec(void);
KS_DECLARE(void) ks_sleep(ks_time_t microsec);

KS_END_EXTERN_C

#endif							/* defined(_KS_TIME_H_) */

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
