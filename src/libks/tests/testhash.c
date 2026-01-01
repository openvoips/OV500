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

int test1(void)
{
	ks_pool_t *pool;
	ks_hash_t *hash;
	int i, sum1 = 0, sum2 = 0;

	ks_pool_open(&pool);
	ks_hash_create(&hash, KS_HASH_MODE_DEFAULT, KS_HASH_FREE_BOTH | KS_HASH_FLAG_RWLOCK, pool);

	for (i = 1; i < 1001; i++) {
		char *key = ks_pprintf(pool, "KEY %d", i);
		char *val = ks_pprintf(pool, "%d", i);
		ks_hash_insert(hash, key, val);
		sum1 += i;
	}



	ks_hash_iterator_t *itt;

	ks_hash_write_lock(hash);
	for (itt = ks_hash_first(hash, KS_UNLOCKED); itt; ) {
		const void *key;
		void *val;

		ks_hash_this(itt, &key, NULL, &val);

		printf("%s=%s\n", (char *)key, (char *)val);
		sum2 += atoi(val);

		itt = ks_hash_next(&itt);
		ks_hash_remove(hash, (char *)key);
	}
	ks_hash_write_unlock(hash);

	ks_hash_destroy(&hash);

	ks_pool_close(&pool);

	return (sum1 == sum2);
}

#define MAX 100

static void *test2_thread(ks_thread_t *thread, void *data)
{
	ks_hash_iterator_t *itt;
	ks_hash_t *hash = (ks_hash_t *) data;

	while(!ks_thread_stop_requested(thread)) {
		for (itt = ks_hash_first(hash, KS_READLOCKED); itt; itt = ks_hash_next(&itt)) {
			const void *key;
			void *val;

			ks_hash_this(itt, &key, NULL, &val);

			printf("%"KS_PID_FMT" ITT %s=%s\n", ks_thread_self_id(), (char *)key, (char *)val);
		}
		ks_sleep(100000);
	}


	return NULL;
}

int test2(void)
{
	ks_thread_t *threads[MAX];
	int ttl = 5;
	int runs = 5;
	ks_pool_t *pool;
	ks_hash_t *hash;
	int i;
	ks_hash_iterator_t *itt;

	ks_pool_open(&pool);
	ks_hash_create(&hash, KS_HASH_MODE_DEFAULT, KS_HASH_FREE_BOTH | KS_HASH_FLAG_RWLOCK, pool);

	for (i = 0; i < ttl; i++) {
		ks_thread_create(&threads[i], test2_thread, hash, pool);
	}

	for(i = 0; i < runs; i++) {
		int x = rand() % 5;
		int j;

		for (j = 0; j < 100; j++) {
			char *key = ks_pprintf(pool, "KEY %d", j);
			char *val = ks_pprintf(pool, "%d", j);
			ks_hash_insert(hash, key, val);
		}

		ks_sleep(x * 1000000);

		ks_hash_write_lock(hash);
		for (itt = ks_hash_first(hash, KS_UNLOCKED); itt; ) {
			const void *key;
			void *val;

			ks_hash_this(itt, &key, NULL, &val);

			printf("DEL %s=%s\n", (char *)key, (char *)val);

			itt = ks_hash_next(&itt);
			ks_hash_remove(hash, (char *)key);
		}
		ks_hash_write_unlock(hash);

	}

	for (i = 0; i < ttl; i++) {
		ks_thread_request_stop(threads[i]);
	}

	for (i = 0; i < ttl; i++) {
		ks_thread_join(threads[i]);
		if (ks_thread_destroy(&threads[i]) != KS_STATUS_SUCCESS) return 0;
	}


	ks_hash_destroy(&hash);
	ks_pool_close(&pool);

	return 1;
}

#define TEST3_SIZE 20
int test3(void)
{
	ks_pool_t *pool;
	ks_hash_t *hash;
	ks_byte_t data[TEST3_SIZE] = { 52, 116, 29, 120, 56, 135, 31, 196, 165, 219, 102, 169, 217, 228, 24, 163, 203, 93, 98, 71 };
	ks_byte_t data2[TEST3_SIZE] = { 248, 96, 216, 171, 94, 116, 77, 48, 114, 0, 49, 61, 93, 229, 224, 10, 6, 8, 112, 248 };
	ks_byte_t data3[TEST3_SIZE] = { 171, 58, 43, 4, 49, 222, 42, 253, 18, 122, 230, 51, 180, 66, 154, 130, 114, 117, 172, 193 };
	char *A, *B, *C;

	ks_pool_open(&pool);
	ks_hash_create(&hash, KS_HASH_MODE_ARBITRARY, KS_HASH_FLAG_NOLOCK, pool);
	ks_hash_set_keysize(hash, TEST3_SIZE);

	ks_hash_insert(hash, data, "FOO");
	ks_hash_insert(hash, data2, "BAR");
	ks_hash_insert(hash, data3, "BAZ");


	A = (char *)ks_hash_search(hash, data, KS_UNLOCKED);
	B = (char *)ks_hash_search(hash, data2, KS_UNLOCKED);
	C = (char *)ks_hash_search(hash, data3, KS_UNLOCKED);


	printf("RESULT [%s][%s][%s]\n", A, B, C);

	ks_hash_destroy(&hash);

	ks_pool_close(&pool);

	return !strcmp(A, "FOO") && !strcmp(B, "BAR") && !strcmp(C, "BAZ");

}


int main(int argc, char **argv)
{

	ks_init();
	ks_global_set_log_level(KS_LOG_LEVEL_DEBUG);

	plan(3);

	ok(test1());
	ok(test2());
	ok(test3());

	ks_shutdown();

	done_testing();
}
