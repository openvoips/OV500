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

typedef struct ks_dht_nodeid_s { uint8_t id[20]; } ks_dht_nodeid_t;

struct x {
	int i;
	ks_pool_t *pool;
};

static void *test1_thread(ks_thread_t *thread, void *data)
{
	struct x *mydata = (struct x *) data;

	ks_log(KS_LOG_DEBUG, "Thread %d\n", mydata->i);
	ks_sleep(100000);
	ks_pool_free(&mydata);
	return NULL;
}

static int test1()
{
	ks_pool_t *pool;
	ks_thread_pool_t *tp = NULL;
	int i = 0;

	ks_pool_open(&pool);

	ks_thread_pool_create(&tp, 2, 10, KS_THREAD_DEFAULT_STACK, KS_PRI_DEFAULT, 5);

	for (i = 0; i < 500; i++) {
		struct x *data = ks_pool_alloc(pool, sizeof(*data));
		data->i = i;
		data->pool = pool;
		ks_sleep(1);
		ks_thread_pool_add_job(tp, test1_thread, data);
	}


	while(ks_thread_pool_backlog(tp)) {
		ks_sleep(100000);
	}

//	ks_sleep(10000000);

	ks_thread_pool_destroy(&tp);
	ks_pool_close(&pool);

	return 1;
}

int main(int argc, char **argv)
{
	ks_init();

	plan(1);

	ok(test1());

	ks_shutdown();

	done_testing();
}
