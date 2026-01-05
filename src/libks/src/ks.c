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
#include "libks/ks_atomic.h"
#include "libks/internal/ks_json_schema.h"

/* The one and only global pool */
static ks_pool_t *g_pool;

/* This keeps track of the number of calls to init, since we allow layered inits
 * we will null up the 2+ inits until the loaded count drops to zero on deinit. */
static ks_size_t g_init_count;

/* We must lock to ensure we don't let secondary initializers return prior to the first
 * being ready */
static ks_spinlock_t g_init_lock;

KS_DECLARE(ks_status_t) ks_init(void)
{
	unsigned int pid = 0;

	/* Lock to atomically check init count, and to prevent returning before
	 * a threaded first initer gets finishes */
	ks_spinlock_acquire(&g_init_lock);

	/* If we're not first, done */
	ks_assert(g_init_count >= 0);
	if (++g_init_count != 1) goto done;

#if !defined(KS_PLAT_WIN)
	signal(SIGPIPE, SIG_IGN);
#endif

	ks_time_init();
	ks_log_init();

#ifdef __WINDOWS__
	pid = _getpid();
#else
	pid = getpid();
#endif
	srand(pid * (unsigned int)(intptr_t)&g_pool + (unsigned int)time(NULL));
	ks_global_pool();
	ks_ssl_init_ssl_locks();
	ks_json_schema_init();

#ifdef __WINDOWS__
	WSADATA wsaData;
	WORD wVersionRequested = MAKEWORD(2, 2);
	if (WSAStartup(wVersionRequested, &wsaData)) {
		abort();
	}
#endif

done:
	ks_spinlock_release(&g_init_lock);

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_shutdown(void)
{
	ks_status_t status = KS_STATUS_SUCCESS;

	ks_spinlock_acquire(&g_init_lock);

	/* If we're not last, done */
	ks_assert(g_init_count != 0);
	if (--g_init_count != 0) goto done;

	ks_dso_shutdown();
	ks_json_schema_shutdown();

#ifdef __WINDOWS__
	WSACleanup();
#endif

	ks_ssl_destroy_ssl_locks();

	if (g_pool) {
		status = ks_pool_close(&g_pool);
	}

	ks_log_shutdown();

done:

	ks_spinlock_release(&g_init_lock);

	return status;
}

KS_DECLARE(ks_pool_t *) ks_global_pool(void)
{
	ks_status_t status;

	if (!g_pool) {

		static ks_spinlock_t pool_alloc_lock;

		ks_spinlock_acquire(&pool_alloc_lock);

		if (!g_pool) {

			if (ks_pool_open(&g_pool) != KS_STATUS_SUCCESS) {
				abort();
			}

			/* Since we are the global pool, any allocations left in it on shutdown
			 * should be concerning, and will get logged */
			ks_pool_log_on_close(g_pool);
		}

		ks_spinlock_release(&pool_alloc_lock);
	}

	return g_pool;
}

KS_ENUM_NAMES(STATUS_NAMES, STATUS_STRINGS)
KS_STR2ENUM(ks_str2ks_status, ks_status2str, ks_status_t, STATUS_NAMES, KS_STATUS_COUNT)

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
