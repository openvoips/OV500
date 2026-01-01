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
#include "libks/internal/ks_mutex.h"

#ifdef WIN32
#include <process.h>
#else
#include <pthread.h>
#endif

#ifdef KS_DEBUG_MUTEX
#include "libks/ks_atomic.h"

static void __post_mutex_cond_wait(ks_mutex_t *mutex, ks_mutex_lock_record_t *record)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);

	/* Verify the pthread mutex was restored to our own lock on this thread */
	ks_assert(mutex->mutex.__data.__owner == utid);

	if (!(mutex->flags & KS_MUTEX_FLAG_NON_RECURSIVE)) {
		ks_assert(mutex->mutex.__data.__count == 1);
	}

	/* Ok copy the old record in */
	mutex->record = *record;
}

static ks_mutex_lock_record_t __pre_mutex_cond_wait(ks_mutex_t *mutex)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);
	ks_mutex_lock_record_t record = mutex->record;

	/* Assert that there are no recursive ownerships present, no system condition api on any platform
	 * unrolls the mutex for you on a condition wait */
	if (!(mutex->flags & KS_MUTEX_FLAG_NON_RECURSIVE)) {
		ks_assert(mutex->record.owner_count == 1);
	}

	ks_assert(mutex->record.owner_id == tid);

	mutex->record.owner_id = 0;
	mutex->record.owner_unique_id = 0;
	mutex->record.owner_count = 0;

	return record;
}

static void __report_mutex_lock_released(ks_mutex_t *mutex, const char *file, int line, const char *tag)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);

	printf("MUTEX_LOCK_RELEASE THR: %"KS_PID_FMT"[%i] MUTEX ADDR: %p COUNT: %zu LOCATION: %s:%d (%s)\n",
		 mutex->record.owner_id,
		 mutex->record.owner_unique_id,
		 (void *)mutex,
		 mutex->record.owner_count,
		 file,
		 line,
		 tag
	);

	ks_assert(mutex->record.owner_unique_id == utid);
	ks_assert(mutex->record.owner_id == tid);
	ks_assert(mutex->record.owner_count != 0);

	/* pthreads won't maintain a count if recursive */
	if (!(mutex->flags & KS_MUTEX_FLAG_NON_RECURSIVE)) {
		ks_assert(mutex->record.owner_count == mutex->mutex.__data.__count);
	}

	ks_assert(mutex->record.owner_unique_id == mutex->mutex.__data.__owner);

	mutex->record.owner_count--;
}

static void __report_mutex_lock_aquired(ks_mutex_t *mutex, const char *file, int line, const char *tag)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);

	if (mutex->record.owner_count) {
		ks_assert(mutex->record.owner_unique_id == utid);
		ks_assert(mutex->record.owner_id == tid);
	} else {
		mutex->record.owner_id = tid;
		mutex->record.owner_unique_id = utid;

		mutex->record.owner_file = file;
		mutex->record.owner_line = line;
		mutex->record.owner_tag = tag;
	}

	mutex->record.owner_count++;
	ks_assert(mutex->record.owner_count != 0);

	printf("MUTEX_LOCK_AQUIRE THR: %"KS_PID_FMT"[%i] MUTEX ADDR: %p COUNT: %zu LOCATION: %s:%d (%s)\n",
		 mutex->record.owner_id,
		 mutex->record.owner_unique_id,
		 (void *)mutex,
		 mutex->record.owner_count,
		 file,
		 line,
		 tag
	);

	ks_assert(mutex->record.owner_unique_id == utid);
	ks_assert(mutex->record.owner_id == tid);

	/* pthreads won't maintain a count if recursive */
	if (!(mutex->flags & KS_MUTEX_FLAG_NON_RECURSIVE)) {
		ks_assert(mutex->record.owner_count == mutex->mutex.__data.__count);
	}
	ks_assert(mutex->record.owner_unique_id == mutex->mutex.__data.__owner);
}

#endif

static void ks_mutex_cleanup(void *ptr, void *arg, ks_pool_cleanup_action_t action, ks_pool_cleanup_type_t type)
{
	ks_mutex_t *mutex = (ks_mutex_t *) ptr;

#if KS_DEBUG_MUTEX
	ks_assert(mutex->record.owner_count == 0);
#endif

	switch(action) {
	case KS_MPCL_ANNOUNCE:
		break;
	case KS_MPCL_TEARDOWN:
		break;
	case KS_MPCL_DESTROY:
#ifdef WIN32
		if (mutex->type == KS_MUTEX_TYPE_NON_RECURSIVE) {
			CloseHandle(mutex->handle);
		} else {
			DeleteCriticalSection(&mutex->mutex);
		}
#else
		pthread_mutex_destroy(&mutex->mutex);
#endif
		break;
	}
}

KS_DECLARE(ks_status_t) ks_mutex_destroy(ks_mutex_t **mutexP)
{
	ks_mutex_t *mutex;

	ks_assert(mutexP);

	mutex = *mutexP;
	*mutexP = NULL;

	if (!mutex) return KS_STATUS_FAIL;

#if KS_DEBUG_MUTEX
	ks_assert(mutex->record.owner_count == 0);
#endif

	if (mutex->malloc) {
#ifdef WIN32
		if (mutex->type == KS_MUTEX_TYPE_NON_RECURSIVE) {
			CloseHandle(mutex->handle);
		} else {
			DeleteCriticalSection(&mutex->mutex);
		}
#else
		pthread_mutex_destroy(&mutex->mutex);
#endif
		if (mutex->flags & KS_MUTEX_FLAG_RAW_ALLOC) {
			free(mutex);
		} else {
			ks_free(mutex);
		}
	} else {
		ks_pool_free(&mutex);
	}

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_mutex_create(ks_mutex_t **mutex, unsigned int flags, ks_pool_t *pool, const char *file, int line, const char *tag)
{
	ks_status_t status = KS_STATUS_FAIL;
#ifndef WIN32
	pthread_mutexattr_t attr;
#endif
	ks_mutex_t *check = NULL;

	if (pool) {
		ks_assert(!(flags & KS_MUTEX_FLAG_RAW_ALLOC));
		if (!(check = (ks_mutex_t *) __ks_pool_alloc(pool, sizeof(**mutex), file, line, tag))) {
			goto done;
		}
	} else {
		if (flags & KS_MUTEX_FLAG_RAW_ALLOC) {
			check = malloc(sizeof(**mutex));
		} else {
			check = __ks_malloc(sizeof(**mutex), file, line, tag);
		}
		memset(check, 0, sizeof(**mutex));
		check->malloc = 1;
	}

	check->type = KS_MUTEX_TYPE_DEFAULT;
	check->flags = flags;

#ifdef WIN32
	if (flags & KS_MUTEX_FLAG_NON_RECURSIVE) {
		check->type = KS_MUTEX_TYPE_NON_RECURSIVE;
		check->handle = CreateEvent(NULL, FALSE, TRUE, NULL);
	} else {
		InitializeCriticalSection(&check->mutex);
	}
#else
	if (flags & KS_MUTEX_FLAG_NON_RECURSIVE) {
		if (pthread_mutex_init(&check->mutex, NULL))
			goto done;

	} else {
		if (pthread_mutexattr_init(&attr))
			goto done;

		if (pthread_mutexattr_settype(&attr, PTHREAD_MUTEX_RECURSIVE))
			goto fail;

		if (pthread_mutex_init(&check->mutex, &attr))
			goto fail;
	}

	goto success;

  fail:
	pthread_mutexattr_destroy(&attr);
	goto done;

  success:
#endif
	*mutex = check;
	status = KS_STATUS_SUCCESS;

	if (pool) {
		ks_pool_set_cleanup(check, NULL, ks_mutex_cleanup);
	}

  done:
	return status;
}

KS_DECLARE(ks_status_t) __ks_mutex_lock(ks_mutex_t *mutex, const char *file, int line, const char *tag)
{
#ifdef WIN32
	if (mutex->type == KS_MUTEX_TYPE_NON_RECURSIVE) {
        DWORD ret = WaitForSingleObject(mutex->handle, INFINITE);
		if ((ret != WAIT_OBJECT_0) && (ret != WAIT_ABANDONED)) {
            return KS_STATUS_FAIL;
		}
	} else {
		EnterCriticalSection(&mutex->mutex);
	}

#else
	if (pthread_mutex_lock(&mutex->mutex)) {
#ifdef KS_DEBUG_MUTEX
		ks_assert("!Lock failure");
#endif
		return KS_STATUS_FAIL;
	}
#endif

#ifdef KS_DEBUG_MUTEX
	__report_mutex_lock_aquired(mutex, file, line, tag);

	ks_assert(mutex->mutex.__data.__owner == mutex->record.owner_unique_id);
	ks_assert(mutex->mutex.__data.__count == mutex->record.owner_count);

#endif
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_mutex_trylock(ks_mutex_t *mutex, const char *file, int line, const char *tag)
{
#ifdef WIN32
	if (mutex->type == KS_MUTEX_TYPE_NON_RECURSIVE) {
        DWORD ret = WaitForSingleObject(mutex->handle, 0);
		if ((ret != WAIT_OBJECT_0) && (ret != WAIT_ABANDONED)) {
            return KS_STATUS_FAIL;
		}
	} else {
		if (!TryEnterCriticalSection(&mutex->mutex))
			return KS_STATUS_FAIL;
	}
#else
	errno = 0;
	if (pthread_mutex_trylock(&mutex->mutex))
		return KS_STATUS_FAIL;
#endif

#ifdef KS_DEBUG_MUTEX
	printf("POST_MUTEX_TRY_LOCK THR: %"KS_PID_FMT"[%i] MUTEX ADDR: %p ERRNO: %d PTHREAD_COUNT: %d PTHREAD_OWNER: [%d] LOCATION: %s:%d (%s)\n",
		 mutex->record.owner_id,
		 mutex->record.owner_unique_id,
		 (void *)mutex,
		 errno,
		 mutex->mutex.__data.__count,
		 mutex->mutex.__data.__owner,
		 file,
		 line,
		 tag
	);

	__report_mutex_lock_aquired(mutex, file, line, tag);
#endif
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_mutex_unlock(ks_mutex_t *mutex, const char *file, int line, const char *tag)
{
#ifdef KS_DEBUG_MUTEX
	__report_mutex_lock_released(mutex, file, line, tag);
#endif

#ifdef WIN32
	if (mutex->type == KS_MUTEX_TYPE_NON_RECURSIVE) {
        if (!SetEvent(mutex->handle)) {
			return KS_STATUS_FAIL;
		}
	} else {
		LeaveCriticalSection(&mutex->mutex);
	}
#else
	if (pthread_mutex_unlock(&mutex->mutex)) {
#ifdef KS_DEBUG_MUTEX
		ks_assert(!"Mutex lock failed");
#endif
		return KS_STATUS_FAIL;
	}
#endif
	return KS_STATUS_SUCCESS;
}

struct ks_cond {
	ks_mutex_t *mutex;
#ifdef WIN32
	CONDITION_VARIABLE cond;
#else
	pthread_cond_t cond;
#endif
	uint8_t static_mutex;
};

static void ks_cond_cleanup(void *ptr, void *arg, ks_pool_cleanup_action_t action, ks_pool_cleanup_type_t type)
{
	ks_cond_t *cond = (ks_cond_t *) ptr;

	switch(action) {
	case KS_MPCL_ANNOUNCE:
		break;
	case KS_MPCL_TEARDOWN:
		break;
	case KS_MPCL_DESTROY:
		if (!cond->static_mutex) {
			ks_mutex_destroy(&cond->mutex);
		}
#ifndef WIN32
		pthread_cond_destroy(&cond->cond);
#endif
		break;
	}
}

KS_DECLARE(ks_status_t) __ks_cond_create_ex(ks_cond_t **cond, ks_pool_t *pool, ks_mutex_t *mutex, const char *file, int line, const char *tag)
{
	ks_status_t status = KS_STATUS_FAIL;
	ks_cond_t *check = NULL;

	*cond = NULL;

	if (!pool)
		pool = ks_global_pool();

	if (!(check = (ks_cond_t *) __ks_pool_alloc(pool, sizeof(**cond), file, line, tag))) {
		goto done;
	}

	if (mutex) {
		check->mutex = mutex;
		check->static_mutex = 1;
	} else {
		if (__ks_mutex_create(&check->mutex, KS_MUTEX_FLAG_DEFAULT, pool, file, line, tag) != KS_STATUS_SUCCESS) {
			goto done;
		}
	}

#ifdef WIN32
	InitializeConditionVariable(&check->cond);
#else
	if (pthread_cond_init(&check->cond, NULL)) {
		if (!check->static_mutex) {
			ks_mutex_destroy(&check->mutex);
		}
		goto done;
	}
#endif

	*cond = check;
	status = KS_STATUS_SUCCESS;
	ks_pool_set_cleanup(check, NULL, ks_cond_cleanup);

  done:
	return status;
}

KS_DECLARE(ks_mutex_t *) ks_cond_get_mutex(ks_cond_t *cond)
{
	return cond->mutex;
}

KS_DECLARE(ks_status_t) __ks_cond_create(ks_cond_t **cond, ks_pool_t *pool, const char *file, int line, const char *tag)
{
	return __ks_cond_create_ex(cond, pool, NULL, file, line, tag);
}

KS_DECLARE(ks_status_t) __ks_cond_lock(ks_cond_t *cond, const char *file, int line, const char *tag)
{
	return __ks_mutex_lock(cond->mutex, file, line, tag);
}

KS_DECLARE(ks_status_t) __ks_cond_trylock(ks_cond_t *cond, const char *file, int line, const char *tag)
{
	return __ks_mutex_trylock(cond->mutex, file, line, tag);
}

KS_DECLARE(ks_status_t) __ks_cond_unlock(ks_cond_t *cond, const char *file, int line, const char *tag)
{
	return __ks_mutex_unlock(cond->mutex, file, line, tag);
}

KS_DECLARE(ks_status_t) __ks_cond_signal(ks_cond_t *cond, const char *file, int line, const char *tag)
{
	__ks_cond_lock(cond, file, line, tag);
#ifdef WIN32
	WakeConditionVariable(&cond->cond);
#else
	pthread_cond_signal(&cond->cond);
#endif
	__ks_cond_unlock(cond, file, line, tag);
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_cond_broadcast(ks_cond_t *cond, const char *file, int line, const char *tag)
{
	__ks_cond_lock(cond, file, line, tag);
#ifdef WIN32
	WakeAllConditionVariable(&cond->cond);
#else
	pthread_cond_broadcast(&cond->cond);
#endif
	__ks_cond_unlock(cond, file, line, tag);
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_cond_try_signal(ks_cond_t *cond, const char *file, int line, const char *tag)
{
	if (__ks_cond_trylock(cond, file, line, tag) != KS_STATUS_SUCCESS) {
		return KS_STATUS_FAIL;
	}
#ifdef WIN32
	WakeConditionVariable(&cond->cond);
#else
	pthread_cond_signal(&cond->cond);
#endif
	__ks_cond_unlock(cond, file, line, tag);
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_cond_try_broadcast(ks_cond_t *cond, const char *file, int line, const char *tag)
{
	if (__ks_cond_trylock(cond, file, line, tag) != KS_STATUS_SUCCESS) {
		return KS_STATUS_FAIL;
	}
#ifdef WIN32
	WakeAllConditionVariable(&cond->cond);
#else
	pthread_cond_broadcast(&cond->cond);
#endif
	__ks_cond_unlock(cond, file, line, tag);
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_cond_wait(ks_cond_t *cond)
{
#ifdef KS_DEBUG_MUTEX
	ks_mutex_lock_record_t record = __pre_mutex_cond_wait(cond->mutex);
#endif

#ifdef WIN32
	SleepConditionVariableCS(&cond->cond, &cond->mutex->mutex, INFINITE);
#else
	pthread_cond_wait(&cond->cond, &cond->mutex->mutex);
#endif

#ifdef KS_DEBUG_MUTEX
	__post_mutex_cond_wait(cond->mutex, &record);
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_cond_timedwait(ks_cond_t *cond, ks_time_t ms)
{
#ifdef KS_DEBUG_MUTEX
	ks_mutex_lock_record_t record = __pre_mutex_cond_wait(cond->mutex);
#endif

#ifdef WIN32
	BOOL res = SleepConditionVariableCS(&cond->cond, &cond->mutex->mutex, (DWORD)ms);

#ifdef KS_DEBUG_MUTEX
	__post_mutex_cond_wait(cond->mutex, &record);
#endif

	if (!res) {
		if (GetLastError() == ERROR_TIMEOUT) {
			return KS_STATUS_TIMEOUT;
		} else {
			return KS_STATUS_FAIL;
		}
	}
#else
	struct timespec ts;
	ks_time_t n = ks_time_now() + (ms * 1000);
	int r = 0;

	ts.tv_sec   = ks_time_sec(n);
	ts.tv_nsec  = ks_time_nsec(n);

	r = pthread_cond_timedwait(&cond->cond, &cond->mutex->mutex, &ts);

#ifdef KS_DEBUG_MUTEX
	__post_mutex_cond_wait(cond->mutex, &record);
#endif

	if (r) {
		switch(r) {
		case ETIMEDOUT:
			return KS_STATUS_TIMEOUT;
		default:
			return KS_STATUS_FAIL;
		}
	}
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_cond_destroy(ks_cond_t **cond)
{
	ks_cond_t *condp = *cond;

	if (!condp) {
		return KS_STATUS_FAIL;
	}

	*cond = NULL;

	return ks_pool_free(&condp);
}

#ifdef KS_DEBUG_MUTEX
typedef enum {
	READ_AQUIRE,
	READ_RELEASE,
	WRITE_AQUIRE,
	WRITE_RELEASE,
} KS_RWL_RECORD_TYPE;

typedef struct ks_rwl_lock_record_s {
	KS_RWL_RECORD_TYPE type;
	ks_pid_t ownerid;
	const char *file;
	int line;
	const char *tag;
} ks_rwl_lock_record_t;

/* Default the number of max records we'll track when KS_DEBUG_MUTEX is enabled.
 * This allows us to avoid any use of malloc while also keeping the operations as
 * fast as possible during memory tracking sessions. */
#ifndef KS_DEBUG_MUTEX_MAX_RECORDS
	#define KS_DEBUG_MUTEX_MAX_RECORDS 1000
#endif

#endif

struct ks_rwl {
#ifdef WIN32
	SRWLOCK rwlock;
	ks_hash_t *read_lock_list;
	ks_mutex_t *read_lock_mutex;
	ks_mutex_t *write_lock_mutex;
#else
	pthread_rwlock_t rwlock;
#endif
	ks_pid_t write_locker;
	uint32_t wlc;

#ifdef KS_DEBUG_MUTEX
	uint64_t read_lock_count, write_lock_count;
	uint32_t record_lock_flag;
	ks_rwl_lock_record_t records[KS_DEBUG_MUTEX_MAX_RECORDS];
	uint64_t next_record_offset;
#endif
};

#ifdef KS_DEBUG_MUTEX
static ks_rwl_lock_record_t * __allocate_thread_lock_record(ks_rwl_t *rwlock, KS_RWL_RECORD_TYPE type, const char *file, int line, const char *tag)
{
	ks_rwl_lock_record_t *record;

	if (rwlock->next_record_offset == KS_DEBUG_MUTEX_MAX_RECORDS) {
		rwlock->next_record_offset = 0;
	}

	record = &rwlock->records[rwlock->next_record_offset++];
	memset(record, 0, sizeof(ks_rwl_lock_record_t));

	record->tag = tag;
	record->line = line;
	record->file = file;
	record->ownerid = ks_thread_self_id();
	record->type = type;
}

static void __report_rwl_read_lock_aquired(ks_rwl_t *rwlock, const char *parent_tag, const char *file, int line, const char *tag)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);

	while (ks_atomic_increment_uint32(&rwlock->record_lock_flag)) {
		ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
	}

	__allocate_thread_lock_record(rwlock, READ_AQUIRE, file, line, tag);
	rwlock->read_lock_count++;
	ks_assert(rwlock->read_lock_count != 0);

	printf("RWL_LOCK_READ_LOCK_AQUIRED THR: %"KS_PID_FMT"[%i] RWL ADDR: %p READ COUNT: %zu WRITE COUNT: %zu METHOD: %s LOCATION: %s:%d (%s)\n",
		 tid,
		 utid,
		 (void *)rwlock,
		 rwlock->read_lock_count,
		 rwlock->write_lock_count,
		 parent_tag,
		 file,
		 line,
		 tag
	);

	if (rwlock->read_lock_count >= 50) {
		printf("WARNING EXCESSIVE READ (%zu >= 50) LOCKS FOUND ON RWLOCK %p\n", rwlock->read_lock_count, (void *)rwlock);
	}

	ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
}

static void __report_rwl_read_lock_released(ks_rwl_t *rwlock, const char *parent_tag, const char *file, int line, const char *tag)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);

	while (ks_atomic_increment_uint32(&rwlock->record_lock_flag)) {
		ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
	}

	ks_assert(rwlock->read_lock_count != 0);
	rwlock->read_lock_count--;
	__allocate_thread_lock_record(rwlock, READ_RELEASE, file, line, tag);

	printf("RWL_LOCK_READ_LOCK_RELEASED THR: %"KS_PID_FMT"[%i] RWL ADDR: %p READ COUNT: %zu WRITE COUNT: %zu METHOD: %s LOCATION: %s:%d (%s)\n",
		 tid,
		 utid,
		 (void *)rwlock,
		 rwlock->read_lock_count,
		 rwlock->write_lock_count,
		 parent_tag,
		 file,
		 line,
		 tag
	);

	ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
}

static void __report_rwl_write_lock_aquired(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);

	while (ks_atomic_increment_uint32(&rwlock->record_lock_flag)) {
		ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
	}

	printf("RWL_LOCK_WRITE_LOCK_AQUIRE THR: %"KS_PID_FMT"[%i] RWL ADDR: %p READ COUNT: %zu WRITE COUNT: %zu LOCATION: %s:%d (%s)\n",
		 tid,
		 utid,
		 (void *)rwlock,
		 rwlock->read_lock_count,
		 rwlock->write_lock_count,
		 file,
		 line,
		 tag
	);

	__allocate_thread_lock_record(rwlock, WRITE_AQUIRE, file, line, tag);
	rwlock->write_lock_count++;
	ks_assert(rwlock->write_lock_count != 0);

	ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
}

static void __report_rwl_write_lock_released(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{
	ks_pid_t tid = ks_thread_self_id();
	pid_t utid = syscall(SYS_gettid);

	while (ks_atomic_increment_uint32(&rwlock->record_lock_flag)) {
		ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
	}

	printf("RWL_LOCK_WRITE_LOCK_RELEASE THR: %"KS_PID_FMT"[%i] RWL ADDR: %p READ COUNT: %zu WRITE COUNT: %zu LOCATION: %s:%d (%s)\n",
		 tid,
		 utid,
		 (void *)rwlock,
		 rwlock->read_lock_count,
		 rwlock->write_lock_count,
		 file,
		 line,
		 tag
	);

	ks_assert(rwlock->write_lock_count != 0);
	rwlock->write_lock_count--;
	__allocate_thread_lock_record(rwlock, WRITE_RELEASE, file, line, tag);

	ks_atomic_decrement_uint32(&rwlock->record_lock_flag);
}
#endif

static void ks_rwl_cleanup(void *ptr, void *arg, ks_pool_cleanup_action_t action, ks_pool_cleanup_type_t type)
{
#ifndef WIN32
	ks_rwl_t *rwlock = (ks_rwl_t *) ptr;
#endif

	switch(action) {
	case KS_MPCL_ANNOUNCE:
		break;
	case KS_MPCL_TEARDOWN:
		break;
	case KS_MPCL_DESTROY:
#ifndef WIN32
		pthread_rwlock_destroy(&rwlock->rwlock);
#endif
		break;
	}
}

KS_DECLARE(ks_status_t) __ks_rwl_create(ks_rwl_t **rwlock, ks_pool_t *pool, const char *file, int line, const char *tag)
{
	ks_status_t status = KS_STATUS_FAIL;
	ks_rwl_t *check = NULL;
	*rwlock = NULL;

	if (!pool) {
		goto done;
	}

	if (!(check = (ks_rwl_t *) __ks_pool_alloc(pool, sizeof(**rwlock), file, line, tag))) {
		goto done;
	}

#ifdef WIN32

	if (ks_hash_create(&check->read_lock_list, KS_HASH_MODE_PTR, KS_HASH_FLAG_NONE, pool) != KS_STATUS_SUCCESS) {
		goto done;
	}

	if (ks_mutex_create(&check->read_lock_mutex, KS_MUTEX_FLAG_DEFAULT, pool) != KS_STATUS_SUCCESS) {
		goto done;
	}

	if (ks_mutex_create(&check->write_lock_mutex, KS_MUTEX_FLAG_DEFAULT, pool) != KS_STATUS_SUCCESS) {
		goto done;
	}

	InitializeSRWLock(&check->rwlock);
#else
	if ((pthread_rwlock_init(&check->rwlock, NULL))) {
		goto done;
	}
#endif

#ifdef KS_DEBUG_MUTEX
	check->record_lock_flag = 0;
#endif

	*rwlock = check;
	status = KS_STATUS_SUCCESS;
	ks_pool_set_cleanup(check, NULL, ks_rwl_cleanup);
 done:
	return status;
}

KS_DECLARE(ks_status_t) __ks_rwl_read_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{
#ifdef WIN32

	__ks_mutex_lock(rwlock->write_lock_mutex, file, line, tag);
	__ks_mutex_lock(rwlock->read_lock_mutex, file, line, tag);

	int count = (int)(intptr_t)ks_hash_remove(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id());

	if (count) {
		ks_hash_insert(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id(), (void *)(intptr_t)++count);
		__ks_mutex_unlock(rwlock->read_lock_mutex, file, line, tag);
		__ks_mutex_unlock(rwlock->write_lock_mutex, file, line, tag);

#ifdef KS_DEBUG_MUTEX
	__report_rwl_read_lock_aquired(rwlock, __FUNCTION__, file, line, tag);
#endif

		return KS_STATUS_SUCCESS;
	}

	AcquireSRWLockShared(&rwlock->rwlock);
#else
	pthread_rwlock_rdlock(&rwlock->rwlock);
#endif

#ifdef WIN32
	ks_hash_insert(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id(), (void *)(intptr_t)(int)1);
	__ks_mutex_unlock(rwlock->read_lock_mutex, file, line, tag);
	__ks_mutex_unlock(rwlock->write_lock_mutex, file, line, tag);
#endif

#ifdef KS_DEBUG_MUTEX
	__report_rwl_read_lock_aquired(rwlock, __FUNCTION__, file, line, tag);
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_rwl_write_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{

	int me = (rwlock->write_locker == ks_thread_self_id());

	if (me) {
		rwlock->wlc++;

#ifdef KS_DEBUG_MUTEX
		__report_rwl_write_lock_aquired(rwlock, file, line, tag);
#endif
		return KS_STATUS_SUCCESS;
	}

#ifdef WIN32
	ks_mutex_lock(rwlock->write_lock_mutex);
	AcquireSRWLockExclusive(&rwlock->rwlock);
#else
	pthread_rwlock_wrlock(&rwlock->rwlock);
#endif
	rwlock->write_locker = ks_thread_self_id();

#ifdef KS_DEBUG_MUTEX
	__report_rwl_write_lock_aquired(rwlock, file, line, tag);
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_rwl_try_read_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{
#ifdef WIN32
	if (__ks_mutex_trylock(rwlock->write_lock_mutex, file, line, tag) != KS_STATUS_SUCCESS) {
		return KS_STATUS_FAIL;
	}

	__ks_mutex_lock(rwlock->read_lock_mutex, file, line, tag);

	int count = (int)(intptr_t)ks_hash_remove(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id());

	if (count) {
		ks_hash_insert(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id(), (void *)(intptr_t)++count);
		__ks_mutex_unlock(rwlock->read_lock_mutex, file, line, tag);
		__ks_mutex_unlock(rwlock->write_lock_mutex, file, line, tag);

#ifdef KS_DEBUG_MUTEX
		__report_rwl_read_lock_aquired(rwlock, __FUNCTION__, file, line, tag);
#endif
		return KS_STATUS_SUCCESS;
	}

	if (!TryAcquireSRWLockShared(&rwlock->rwlock)) {
		__ks_mutex_unlock(rwlock->read_lock_mutex, file, line, tag);
		__ks_mutex_unlock(rwlock->write_lock_mutex, file, line, tag);
		return KS_STATUS_FAIL;
	}
#else
	if (pthread_rwlock_tryrdlock(&rwlock->rwlock)) {
		return KS_STATUS_FAIL;
	}

#ifdef KS_DEBUG_MUTEX
	__report_rwl_read_lock_aquired(rwlock, __FUNCTION__, file, line, tag);
#endif

#endif

#ifdef WIN32
	ks_hash_insert(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id(), (void *)(intptr_t)(int)1);
	ks_mutex_unlock(rwlock->read_lock_mutex);
	ks_mutex_unlock(rwlock->write_lock_mutex);
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_rwl_try_write_lock(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{
	int me = (rwlock->write_locker == ks_thread_self_id());

	if (me) {
		rwlock->wlc++;
#ifdef KS_DEBUG_MUTEX
		__report_rwl_write_lock_aquired(rwlock, file, line, tag);
#endif
		return KS_STATUS_SUCCESS;
	}

#ifdef WIN32
	if (!TryAcquireSRWLockExclusive(&rwlock->rwlock)) {
		return KS_STATUS_FAIL;
	}
#else
	if (pthread_rwlock_trywrlock(&rwlock->rwlock)) {
		return KS_STATUS_FAIL;
	}
#endif

	rwlock->write_locker = ks_thread_self_id();

#ifdef KS_DEBUG_MUTEX
	__report_rwl_write_lock_aquired(rwlock, file, line, tag);
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_rwl_read_unlock(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{
#ifdef WIN32
	__ks_mutex_lock(rwlock->read_lock_mutex, file, line, tag);

	int count = (int)(intptr_t)ks_hash_remove(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id());

	if (count > 1) {
		ks_hash_insert(rwlock->read_lock_list, (void *)(intptr_t)ks_thread_self_id(), (void *)(intptr_t)--count);

#ifdef KS_DEBUG_MUTEX
		__report_rwl_read_lock_released(rwlock, __FUNCTION__, file, line, tag);
#endif
		__ks_mutex_unlock(rwlock->read_lock_mutex, file, line, tag);
		return KS_STATUS_SUCCESS;
	}

#ifdef KS_DEBUG_MUTEX
	__report_rwl_read_lock_released(rwlock, __FUNCTION__, file, line, tag);
#endif

	ReleaseSRWLockShared(&rwlock->rwlock);
	__ks_mutex_unlock(rwlock->read_lock_mutex, file, line, tag);
#else

#ifdef KS_DEBUG_MUTEX
	__report_rwl_read_lock_released(rwlock, __FUNCTION__, file, line, tag);
#endif

	pthread_rwlock_unlock(&rwlock->rwlock);
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) __ks_rwl_write_unlock(ks_rwl_t *rwlock, const char *file, int line, const char *tag)
{
	int me = (rwlock->write_locker == ks_thread_self_id());

	if (me && rwlock->wlc > 0) {
#ifdef KS_DEBUG_MUTEX
		__report_rwl_write_lock_released(rwlock, file, line, tag);
#endif
		rwlock->wlc--;

		return KS_STATUS_SUCCESS;
	}

	rwlock->write_locker = 0;

#ifdef KS_DEBUG_MUTEX
	__report_rwl_write_lock_released(rwlock, file, line, tag);
#endif

#ifdef WIN32
	ReleaseSRWLockExclusive(&rwlock->rwlock);
	ks_mutex_unlock(rwlock->write_lock_mutex);
#else
	pthread_rwlock_unlock(&rwlock->rwlock);
#endif

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_rwl_destroy(ks_rwl_t **rwlock)
{
	ks_rwl_t *rwlockp = *rwlock;


	if (!rwlockp) {
		return KS_STATUS_FAIL;
	}

#ifdef KS_DEBUG_MUTEX
	ks_assert(rwlockp->record_lock_flag == 0);
#endif

	*rwlock = NULL;

	return ks_pool_free(&rwlockp);
}


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
