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

#include "tap.h"

int main(int argc, char **argv)
{
	char a[8196 * 5];
	memset(a, 'a', sizeof(a));
	a[sizeof(a) - 1] = '\0';
	ks_init();
	ks_global_set_log_level(KS_LOG_LEVEL_DEBUG);
	ks_log(a, a, 9999999, KS_LOG_LEVEL_DEBUG, a);
	ks_log(a, a, 9999999, KS_LOG_LEVEL_INFO, a);
	ks_log(a, a, 9999999, KS_LOG_LEVEL_WARNING, a);
	ks_log(a, a, 9999999, KS_LOG_LEVEL_ERROR, a);
	ks_log(a, a, 9999999, KS_LOG_LEVEL_CRIT, a);
	ks_log(a, a, 9999999, KS_LOG_LEVEL_ALERT, a);
	ks_log(a, a, 9999999, KS_LOG_LEVEL_EMERG, a);
}
