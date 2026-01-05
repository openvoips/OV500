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

#ifndef _KS_THREADMUTEX_H
#define _KS_THREADMUTEX_H

#include "ks.h"

KS_BEGIN_EXTERN_C

#ifdef KS_PLAT_WIN
#include <process.h>
#define KS_THREAD_CALLING_CONVENTION __stdcall
#else
#include <pthread.h>
#define KS_THREAD_CALLING_CONVENTION
#endif

#define KS_THREAD_DEFAULT_STACK (512 * 1024)

typedef struct ks_thread ks_thread_t;
typedef void *(*ks_thread_function_t) (ks_thread_t *, void *);

#ifdef KS_PLAT_WIN
	typedef void * ks_thread_os_handle_t;
	typedef DWORD ks_pid_t;
	#define KS_PID_FMT "8.8" PRIx32
#elif defined(KS_PLAT_MAC)
	typedef uint64_t ks_pid_t;
	typedef pthread_t ks_thread_os_handle_t;
	#define KS_PID_FMT "16.16" PRIx64
#else
	typedef pid_t ks_pid_t;
	typedef pthread_t ks_thread_os_handle_t;
	#define KS_PID_FMT "8.8" PRIX32
#endif

typedef enum {
	KS_PRI_DEFAULT = 0,
	KS_PRI_LOW = 1,
	KS_PRI_NORMAL = 10,
	KS_PRI_IMPORTANT = 50,
	KS_PRI_REALTIME = 99,
} ks_thread_priority_t;

typedef enum {
	KS_THREAD_FLAG_DEFAULT   = 0,
	KS_THREAD_FLAG_DETACHED = (1 << 0)
} ks_thread_flags_t;

KS_DECLARE(int) ks_thread_set_priority(int nice_val);
KS_DECLARE(ks_thread_os_handle_t) ks_thread_self(void);
KS_DECLARE(ks_pid_t) ks_thread_self_id(void);
KS_DECLARE(ks_thread_os_handle_t) ks_thread_os_handle(ks_thread_t *thread);
KS_DECLARE(ks_status_t) ks_thread_destroy(ks_thread_t **threadp);

KS_DECLARE(ks_bool_t) ks_thread_stop_requested(ks_thread_t *thread);
KS_DECLARE(ks_status_t) ks_thread_request_stop(ks_thread_t *thread);

/* We declare thread create this way to automatically stash the original function that spanwed the name
 * which we use as the 'tag' (and set as the thread name on compatible systems) */
KS_DECLARE(ks_status_t) __ks_thread_create_ex(ks_thread_t **thread, ks_thread_function_t func, void *data,
										 uint32_t flags, size_t stack_size, ks_thread_priority_t priority, ks_pool_t *pool, const char *file, int line, const char *tag);

#define ks_thread_create_ex(thread, func, data, flags, stack_size, priority, pool) \
	__ks_thread_create_ex(thread, func, data, flags, stack_size, priority, pool, __FILE__, __LINE__, __KS_FUNC__)

#define ks_thread_create_tag(thread, func, data, pool, tag)	\
	__ks_thread_create_ex(thread, func, data, KS_THREAD_FLAG_DEFAULT, KS_THREAD_DEFAULT_STACK, KS_PRI_DEFAULT, pool, __FILE__, __LINE__, tag)

#define ks_thread_create(thread, func, data, pool)						\
	__ks_thread_create_ex(thread, func, data, KS_THREAD_FLAG_DEFAULT, KS_THREAD_DEFAULT_STACK, KS_PRI_DEFAULT, pool, __FILE__, __LINE__,  __KS_FUNC__)

KS_DECLARE(ks_status_t) ks_thread_join(ks_thread_t *thread);
KS_DECLARE(uint8_t) ks_thread_priority(ks_thread_t *thread);

typedef enum {
	KS_MUTEX_FLAG_DEFAULT       = 0,
	KS_MUTEX_FLAG_NON_RECURSIVE = (1 << 0),
	KS_MUTEX_FLAG_RAW_ALLOC = (2 << 0)
} ks_mutex_flags_t;

typedef struct ks_mutex ks_mutex_t;

KS_DECLARE(ks_status_t) __ks_mutex_create(ks_mutex_t **mutex, unsigned int flags, ks_pool_t *pool, const char *file, int line, const char *tag);
#define ks_mutex_create(mutex, flags, pool) __ks_mutex_create(mutex, flags, pool, __FILE__, __LINE__, __KS_FUNC__)

KS_DECLARE(ks_status_t) ks_mutex_destroy(ks_mutex_t **mutex);

/* These are declared this way so we can track the locks during bug hunts with KS_DEBUG_MUTEX definition */
KS_DECLARE(ks_status_t) __ks_mutex_lock(ks_mutex_t *mutex, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_mutex_trylock(ks_mutex_t *mute, const char *file, int line, const char *tagx);
KS_DECLARE(ks_status_t) __ks_mutex_unlock(ks_mutex_t *mute, const char *file, int line, const char *tagx);

#define ks_mutex_lock(mutex)		__ks_mutex_lock(mutex, __FILE__, __LINE__, __KS_FUNC__)
#define ks_mutex_trylock(mutex) 	__ks_mutex_trylock(mutex, __FILE__, __LINE__, __KS_FUNC__)
#define ks_mutex_unlock(mutex)  	__ks_mutex_unlock(mutex, __FILE__, __LINE__, __KS_FUNC__)

typedef struct ks_cond ks_cond_t;

KS_DECLARE(ks_status_t) __ks_cond_create(ks_cond_t **cond, ks_pool_t *pool, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_cond_create_ex(ks_cond_t **cond, ks_pool_t *pool, ks_mutex_t *mutex, const char *file, int line, const char *tag);

#define ks_cond_create(cond, pool)	__ks_cond_create(cond, pool, __FILE__, __LINE__, __KS_FUNC__)
#define ks_cond_create_ex(cond, pool, mutex)	__ks_cond_create_ex(cond, pool, mutex, __FILE__, __LINE__, __KS_FUNC__)

KS_DECLARE(ks_status_t) ks_cond_wait(ks_cond_t *cond);
KS_DECLARE(ks_status_t) ks_cond_timedwait(ks_cond_t *cond, ks_time_t ms);
KS_DECLARE(ks_status_t) ks_cond_destroy(ks_cond_t **cond);
KS_DECLARE(ks_mutex_t *) ks_cond_get_mutex(ks_cond_t *cond);
KS_DECLARE(void) ks_thread_stats(uint32_t *active_attached, uint32_t *active_detached);
KS_DECLARE(void *) ks_thread_get_return_data(ks_thread_t *thread);
KS_DECLARE(void) ks_thread_set_return_data(ks_thread_t *thread, void *return_data);

/* These are declared this way so we can track the locks during bug hunts with KS_DEBUG_MUTEX definition */
KS_DECLARE(ks_status_t) __ks_cond_lock(ks_cond_t *cond, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_cond_trylock(ks_cond_t *cond, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_cond_unlock(ks_cond_t *cond, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_cond_signal(ks_cond_t *cond, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_cond_broadcast(ks_cond_t *cond, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_cond_try_signal(ks_cond_t *cond, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_cond_try_broadcast(ks_cond_t *cond, const char *file, int line, const char *tag);

#define ks_cond_lock(cond) 			__ks_cond_lock(cond, __FILE__, __LINE__, __KS_FUNC__)
#define ks_cond_trylock(cond) 		__ks_cond_trylock(cond, __FILE__, __LINE__, __KS_FUNC__)
#define ks_cond_unlock(cond) 		__ks_cond_unlock(cond, __FILE__, __LINE__, __KS_FUNC__)
#define ks_cond_signal(cond) 		__ks_cond_signal(cond, __FILE__, __LINE__, __KS_FUNC__)
#define	ks_cond_broadcast(cond) 	__ks_cond_broadcast(cond, __FILE__, __LINE__, __KS_FUNC__)
#define ks_cond_try_signal(cond) 	__ks_cond_try_signal(cond, __FILE__, __LINE__, __KS_FUNC__)
#define ks_cond_try_broadcast(cond) __ks_cond_try_broadcast(cond, __FILE__, __LINE__, __KS_FUNC__)

typedef struct ks_rwl ks_rwl_t;

KS_DECLARE(ks_status_t) __ks_rwl_create(ks_rwl_t **rwlock, ks_pool_t *pool, const char *file, int line, const char *tag);
#define ks_rwl_create(rwlock, pool)	__ks_rwl_create(rwlock, pool, __FILE__, __LINE__, __KS_FUNC__)

KS_DECLARE(ks_status_t) ks_rwl_destroy(ks_rwl_t **rwlock);

/* These are declared this way so we can track the locks during bug hunts with KS_DEBUG_MUTEX definition */
KS_DECLARE(ks_status_t) __ks_rwl_read_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_rwl_write_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_rwl_try_read_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_rwl_try_write_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_rwl_read_unlock(ks_rwl_t *rwlock, const char *file, int line, const char *tag);
KS_DECLARE(ks_status_t) __ks_rwl_write_unlock(ks_rwl_t *rwlock, const char *file, int line, const char *tag);

#define ks_rwl_read_lock(rwlock)  		__ks_rwl_read_lock(rwlock, __FILE__, __LINE__, __KS_FUNC__)
#define	ks_rwl_write_lock(rwlock) 		__ks_rwl_write_lock(rwlock, __FILE__, __LINE__, __KS_FUNC__)
#define ks_rwl_try_read_lock(rwlock) 	__ks_rwl_try_read_lock(rwlock, __FILE__, __LINE__, __KS_FUNC__)
#define ks_rwl_try_write_lock(rwlock) 	__ks_rwl_try_write_lock(rwlock, __FILE__, __LINE__, __KS_FUNC__)
#define ks_rwl_read_unlock(rwlock) 		__ks_rwl_read_unlock(rwlock, __FILE__, __LINE__, __KS_FUNC__)
#define ks_rwl_write_unlock(rwlock) 	__ks_rwl_write_unlock(rwlock, __FILE__, __LINE__, __KS_FUNC__)

#define IS64BIT (SIZE_MAX == UINT64_MAX)

/* Define a helper macro until the standards figure this out */
#ifdef WIN32
	#define KS_THREAD_LOCAL __declspec(thread)
#else
	#define KS_THREAD_LOCAL __thread
#endif

KS_END_EXTERN_C

#endif							/* defined(_KS_THREADMUTEX_H) */

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
