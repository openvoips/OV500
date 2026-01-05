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
	int64_t now, then;
	int diff;
	int i;

	ks_init();

	plan(2);

	then = ks_time_now();

	ks_sleep(2000000);

	now = ks_time_now();

	diff = (int)((now - then) / 1000);
	printf("DIFF %ums\n", diff);

#ifdef KS_PLAT_MAC
	/* the clock on osx seems to be particularly bad at being accurate, we need a bit more room for error*/
	ok(diff > 1990 && diff < 2200);
#else
	ok(diff > 1990 && diff < 2010);
#endif

	then = ks_time_now();

	for (i = 0; i < 100; i++) {
		ks_sleep(20000);
	}

	now = ks_time_now();

	diff = (int)((now - then) / 1000);
	printf("DIFF %ums\n", diff);

#ifdef KS_PLAT_MAC
	/* the clock on osx seems to be particularly bad at being accurate, we need a bit more room for error*/
	/* GHA macos arm runners seem too slow */
	ok( diff > 1900 && diff < 12000 );
#else
	ok( diff > 1950 && diff < 2050 );
#endif
	ks_shutdown();
	done_testing();
}
