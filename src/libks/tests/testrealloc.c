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

#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "tap.h"

int main(int argc, char **argv)
{
	ks_pool_t *pool;
	uint32_t *buf = NULL;
	intptr_t ptr = 0;

	ks_init();

	plan(4);

	ks_pool_open(&pool);

	buf = (uint32_t *)ks_pool_alloc(pool, sizeof(uint32_t) * 1);
	ok(buf != NULL);

	ptr = (intptr_t)buf;

	buf = (uint32_t *)ks_pool_resize(buf, sizeof(uint32_t) * 1);
	ok(buf != NULL);

	ok((intptr_t)buf == ptr);

	buf = (uint32_t *)ks_pool_resize(buf, sizeof(uint32_t) * 2);
	ok(buf != NULL);

	ks_pool_free(&buf);

	ks_pool_close(&pool);

	ks_shutdown();

	done_testing();
}
