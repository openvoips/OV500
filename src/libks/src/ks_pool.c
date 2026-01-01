/*
 * Memory pool routines.
 *
 * Copyright 1996 by Gray Watson.
 *
 * This file is part of the ks_pool package.
 *
 * Permission to use, copy, modify, and distribute this software for
 * any purpose and without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all
 * copies, and that the name of Gray Watson not be used in advertising
 * or publicity pertaining to distribution of the document or software
 * without specific, written prior permission.
 *
 * Gray Watson makes no representations about the suitability of the
 * software described herein for any purpose.  It is provided "as is"
 * without express or implied warranty.
 *
 * The author may be reached via http://256.com/gray/
 *
 * $Id: ks_mpool.c,v 1.5 2006/05/31 20:28:31 gray Exp $
 */

/*
 * Memory-pool allocation routines.  I got sick of the GNU mmalloc
 * library which was close to what we needed but did not exactly do
 * what I wanted.
 *
 */

#include "libks/ks.h"
#include "libks/internal/ks_pool.h"

typedef struct ks_debug_pool_pack_ctx_s ks_debug_pool_pack_ctx_t;

static KS_THREAD_LOCAL uint32_t g_default_scanned_value = 0;

static ks_status_t check_pool(const ks_pool_t *pool);
static ks_status_t check_fence(const void *addr);
static void write_fence(void *addr);

#define CHECK_PREFIX(p) { \
	ks_assert(p->magic1 == KS_POOL_PREFIX_MAGIC && \
			  p->magic2 == KS_POOL_PREFIX_MAGIC && \
			  p->magic3 == KS_POOL_PREFIX_MAGIC && \
			  p->magic4 == KS_POOL_PREFIX_MAGIC && \
			  p->magic5 == KS_POOL_PREFIX_MAGIC); \
}

static void perform_pool_cleanup_on_free(ks_pool_prefix_t *prefix)
{
	void *addr;

	ks_assert(prefix);
	ks_assert(prefix->pool);

	if (prefix->pool->cleaning_up) return;

	addr = (void *)((uintptr_t)prefix + KS_POOL_PREFIX_SIZE);

	if (prefix->cleanup_callback) {
//		ks_log(KS_LOG_DEBUG, "Performining callback based cleanup on prefix addr: %p\n", (void *)addr);
		prefix->cleanup_callback(addr, prefix->cleanup_arg, KS_MPCL_ANNOUNCE, KS_MPCL_FREE);
		prefix->cleanup_callback(addr, prefix->cleanup_arg, KS_MPCL_TEARDOWN, KS_MPCL_FREE);
		prefix->cleanup_callback(addr, prefix->cleanup_arg, KS_MPCL_DESTROY, KS_MPCL_FREE);
	} else {
//		ks_log(KS_LOG_DEBUG, "Performining non-callback based cleanup on prefix addr: %p\n", (void *)addr);
	}
}

static void perform_pool_cleanup(ks_pool_t *pool)
{
	ks_pool_prefix_t *prefix;
	ks_pool_prefix_t *next;

	if (pool->cleaning_up) {
		return;
	}
	pool->cleaning_up = KS_TRUE;


	/* Assign next on each iteration here as if the cleanup itself frees the prefix
	 * we will crash since the loop will try to access next in a now released prefix */
	for (prefix = pool->first; prefix; prefix = next) {
		next = prefix->next;

		if (pool->log_on_close) {
		#if KS_DEBUG_POOL
			ks_log(KS_LOG_WARNING, "Un-released pool item at location: %s:%lu:%s of size: %lu", prefix->file, prefix->line, prefix->tag, prefix->size);
		#else
			ks_log(KS_LOG_WARNING, "Un-released pool item of size: %lu", prefix->size);
		#endif
		}

		if (!prefix->cleanup_callback)
			continue;

		prefix->cleanup_callback((void *)((uintptr_t)prefix + KS_POOL_PREFIX_SIZE), prefix->cleanup_arg, KS_MPCL_ANNOUNCE, KS_MPCL_GLOBAL_FREE);
	}

	for (prefix = pool->first; prefix; prefix = next) {

		next = prefix->next;

		if (!prefix->cleanup_callback)
			continue;

		prefix->cleanup_callback((void *)((uintptr_t)prefix + KS_POOL_PREFIX_SIZE), prefix->cleanup_arg, KS_MPCL_TEARDOWN, KS_MPCL_GLOBAL_FREE);
	}

	for (prefix = pool->first; prefix; prefix = next) {

		next = prefix->next;

		if (!prefix->cleanup_callback)
			continue;

		prefix->cleanup_callback((void *)((uintptr_t)prefix + KS_POOL_PREFIX_SIZE), prefix->cleanup_arg, KS_MPCL_DESTROY, KS_MPCL_GLOBAL_FREE);
	}
}

KS_DECLARE(ks_status_t) ks_pool_remove_cleanup(void *ptr)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_pool_prefix_t *prefix = NULL;

	ks_assert(ptr);

	prefix = (ks_pool_prefix_t *)((uintptr_t)ptr - KS_POOL_PREFIX_SIZE);

	CHECK_PREFIX(prefix);

	prefix->cleanup_arg = NULL;
	prefix->cleanup_callback = NULL;

	return ret;
}

KS_DECLARE(ks_status_t) ks_pool_set_cleanup(void *ptr, void *arg, ks_pool_cleanup_callback_t callback)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_pool_prefix_t *prefix = NULL;

	ks_assert(ptr);
	ks_assert(callback);

	prefix = (ks_pool_prefix_t *)((uintptr_t)ptr - KS_POOL_PREFIX_SIZE);

	CHECK_PREFIX(prefix);

	prefix->cleanup_arg = arg;
	prefix->cleanup_callback = callback;

	return ret;
}



/****************************** local utilities ******************************/

/*
* static ks_status_t check_pool
*
* DESCRIPTION:
*
* Check the validity of pool checksums.
*
* RETURNS:
*
* Success - KS_STATUS_SUCCESS
*
* Failure - Ks_Pool error code
*
* ARGUMENTS:
*
* pool -> A pointer to a pool.
*/
static ks_status_t check_pool(const ks_pool_t *pool)
{
	ks_assert(pool);

	if (pool->magic1 != KS_POOL_MAGIC) return KS_STATUS_PNT;
	if (pool->magic2 != KS_POOL_MAGIC) return KS_STATUS_POOL_OVER;

	return KS_STATUS_SUCCESS;
}

/*
 * static ks_status_t check_fence
 *
 * DESCRIPTION:
 *
 * Check the validity of the fence checksums.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - Ks_Pool error code
 *
 * ARGUMENTS:
 *
 * addr -> A pointer directly to the fence.
 */
static ks_status_t check_fence(const void *addr)
{
	const ks_byte_t *mem_p;

	mem_p = (ks_byte_t *)addr;

	if (*mem_p == KS_POOL_FENCE_MAGIC0 && *(mem_p + 1) == KS_POOL_FENCE_MAGIC1)
		return KS_STATUS_SUCCESS;

	ks_debug_break();
	return KS_STATUS_PNT_OVER;
}

/*
 * static void write_fence
 *
 * DESCRIPTION:
 *
 * Write the magic ID to the address.
 *
 * RETURNS:
 *
 * None.
 *
 * ARGUMENTS:
 *
 * addr -> Address where to write the magic.
 */
static void write_fence(void *addr)
{
	*((ks_byte_t *)addr) = KS_POOL_FENCE_MAGIC0;
	*((ks_byte_t *)addr + 1) = KS_POOL_FENCE_MAGIC1;
}



/*
 * static void *alloc_mem
 *
 * DESCRIPTION:
 *
 * Allocate space for bytes inside of an already open memory pool.
 *
 * RETURNS:
 *
 * Success - Pointer to the address to use.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool.
 *
 * byte_size -> Number of bytes to allocate in the pool.  Must be >0.
 *
 * error_p <- Pointer to ks_status_t which, if not NULL, will be set with
 * a ks_pool error code.
 */
static void *alloc_mem(ks_pool_t *pool, const ks_size_t size, const char *file, int line, const char *tag, ks_status_t *error_p)
{
	ks_size_t required;
	void *start = NULL;
	void *addr = NULL;
	void *fence = NULL;
	ks_pool_prefix_t *prefix = NULL;

	ks_assert(pool);
	ks_assert(size);

	required = KS_POOL_PREFIX_SIZE + size + KS_POOL_FENCE_SIZE;
	start = malloc(required);
	ks_assert(start);
	memset(start, 0, required); // @todo consider readding the NO_ZERO flag option, which would reduce this to only zero out PREFIX_SIZE instead of the entire allocation.

	prefix = (ks_pool_prefix_t *)start;
#ifdef KS_DEBUG_POOL
	prefix->scanned = g_default_scanned_value;
#endif
	addr = (void *)((ks_byte_t *)start + KS_POOL_PREFIX_SIZE);
	fence = (void *)((ks_byte_t *)addr + size);

	prefix->magic1 = KS_POOL_PREFIX_MAGIC;
	prefix->size = size;
	prefix->magic2 = KS_POOL_PREFIX_MAGIC;
	prefix->refs = 1;
	prefix->next = pool->first;

#ifdef KS_DEBUG_POOL
	prefix->file = file;
	prefix->line = line;
	prefix->tag = tag;
#endif

	if (pool->first) pool->first->prev = prefix;
	pool->first = prefix;
	if (!pool->last) pool->last = prefix;
	prefix->magic3 = KS_POOL_PREFIX_MAGIC;
	prefix->magic4 = KS_POOL_PREFIX_MAGIC;
	prefix->pool = pool;
	prefix->magic5 = KS_POOL_PREFIX_MAGIC;

	write_fence(fence);

	if (pool->log_func != NULL) {
		pool->log_func(pool, KS_POOL_FUNC_INCREF, prefix->size, prefix->refs, NULL, addr, 0);
	}

	pool->alloc_c++;
	pool->user_alloc += prefix->size;
	if (pool->user_alloc > pool->max_alloc) {
		pool->max_alloc = pool->user_alloc;
	}

	SET_POINTER(error_p, KS_STATUS_SUCCESS);
	return addr;
}

/*
 * static int free_mem
 *
 * DESCRIPTION:
 *
 * Free an address from a memory pool.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - Ks_Pool error code
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool.
 *
 * addr -> Address to free.
 *
 */
static ks_status_t free_mem(void *addr)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	void *start = NULL;
	void *fence = NULL;
	ks_pool_prefix_t *prefix = NULL;
	ks_pool_t *pool = NULL;

	ks_assert(addr);

	start = (void *)((uintptr_t)addr - KS_POOL_PREFIX_SIZE);
	prefix = (ks_pool_prefix_t *)start;

	CHECK_PREFIX(prefix);

	pool = prefix->pool;

	if (prefix->refs > 0) {
		prefix->refs--;

		if (pool->log_func != NULL) {
			pool->log_func(pool, KS_POOL_FUNC_DECREF, prefix->size, prefix->refs, addr, NULL, 0);
		}
	}

	if (prefix->refs > 0) {
		return KS_STATUS_REFS_EXIST;
	}

	fence = (void *)((uintptr_t)addr + prefix->size);
	ret = check_fence(fence);

	perform_pool_cleanup_on_free(prefix);

	if (!prefix->prev && !prefix->next) pool->first = pool->last = NULL;
	else if (!prefix->prev) {
		pool->first = prefix->next;
		pool->first->prev = NULL;
	}
	else if (!prefix->next) {
		pool->last = prefix->prev;
		pool->last->next = NULL;
	} else {
		prefix->prev->next = prefix->next;
		prefix->next->prev = prefix->prev;
	}

	pool->alloc_c--;
	pool->user_alloc -= prefix->size;

	free(start);

	return ret;
}

/***************************** exported routines *****************************/

/*
 * ks_pool_t *ks_pool_open
 *
 * DESCRIPTION:
 *
 * Open/allocate a new memory pool.
 *
 * RETURNS:
 *
 * Success - Pool pointer which must be passed to ks_pool_close to
 * deallocate.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * flags -> Flags to set attributes of the memory pool.  See the top
 * of ks_pool.h.
 * file  <- pointer to const literal string macro __FILE__
 * line  <- integer value of file line __LINE__ macro
 * tag   <- p:wointer to const literal string to associate with pool
 *
 * error_p <- Pointer to ks_status_t which, if not NULL, will be set with
 * a ks_pool error code.
 */
static ks_pool_t *ks_pool_raw_open(const ks_size_t flags, const char *file, int line, const char *tag, ks_status_t *error_p)
{
	ks_pool_t *pool = NULL;

	pool = malloc(sizeof(ks_pool_t));
	ks_assert(pool);
	memset(pool, 0, sizeof(ks_pool_t));

	pool->magic1 = KS_POOL_MAGIC;
	pool->flags = flags;
	pool->line = line;
	pool->file = file;
	pool->tag = tag;
	pool->magic2 = KS_POOL_MAGIC;

	SET_POINTER(error_p, KS_STATUS_SUCCESS);
	return pool;
}

/*
 * ks_pool_t *ks_pool_open
 *
 * DESCRIPTION:
 *
 * Open/allocate a new memory pool.
 *
 * RETURNS:
 *
 * Success - KS_SUCCESS
 *
 * Failure - KS_FAIL
 *
 * ARGUMENTS:
 *
 * poolP <- pointer to new pool that will be set on success
 * file  <- pointer to const literal string macro __FILE__
 * line  <- integer value of file line __LINE__ macro
 * tag   <- pointer to const literal string to associate with pool
 *
 * NOTES:
 *
 * For tracking memory leaks, ks_pool_open is a macro which
 * will automatically include the file/line where the pool was
 * allocated.
 */
KS_DECLARE(ks_status_t) ks_pool_tagged_open(ks_pool_t **poolP, const char *file, int line, const char *tag)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_pool_t *pool = NULL;

	ks_assert(poolP);

	pool = ks_pool_raw_open(KS_POOL_FLAG_DEFAULT, file, line, tag, &ret);

	*poolP = pool;

	ret = __ks_mutex_create(&pool->mutex, KS_MUTEX_FLAG_DEFAULT | KS_MUTEX_FLAG_RAW_ALLOC, NULL, file, line, tag);

	return ret;
}

#ifdef KS_DEBUG_POOL

/**
 * Iterates the individual heap blocks in a pool, if new_only is true and there are no new entries
 * it will return NULL to indicate to the caller not to show that pool.
 */
static ks_json_t * __pack_pool_stats(ks_pool_t *pool, ks_debug_pool_pack_type_t type, ks_bool_t new_only)
{
	ks_json_t *heap_stats_object = NULL;
	ks_status_t err = KS_STATUS_SUCCESS;
	uint32_t index = 0;

	heap_stats_object = ks_json_create_object();

	if (NULL == heap_stats_object) {
		return NULL;
	}

	ks_mutex_lock(pool->mutex);

	for (ks_pool_prefix_t *prefix = pool->first; prefix; prefix = prefix->next) {
		char workspace[256] = {0}, workspace2[256] = {0}, workspace3[256] = {0};
		ks_json_t *pool_heap_group_object = NULL, *count_number = NULL, *heap_stat_array = NULL;
		ks_byte_t *addr = (void *)((ks_byte_t *)prefix + KS_POOL_PREFIX_SIZE);

		/* Always skip items we are allocating as part of this json apis */
		if (prefix->scanned == 2) {
			continue;
		}

		if (new_only && prefix->scanned) {
			continue;
		}

		prefix->scanned = KS_TRUE;

		/* First ensure this pool allocation group exists */
		snprintf(
			workspace,
			sizeof(workspace),
			"%s:%d Size: %s",
			prefix->file,
			prefix->line,
			ks_human_readable_size(
				prefix->size,
				1,
				sizeof(workspace2),
				workspace2
			)
		);

		if (!(pool_heap_group_object= ks_json_get_object_item(heap_stats_object, workspace))) {
			pool_heap_group_object = ks_json_add_object_to_object(heap_stats_object, workspace);
		}

		/* Now make sure the heap stats array exists */
		if (type == KS_DEBUG_POOL_PACK_TYPE_POOL_HEAP) {
			if (!(heap_stat_array = ks_json_get_object_item(pool_heap_group_object, "allocation_pointers"))) {
				heap_stat_array = ks_json_add_object_to_object(pool_heap_group_object, "allocation_pointers");
			}
		}

		/* Put a little binary preview in there so we can figure out what it is */
		for (int pos = 0; pos < prefix->size && pos < 70; pos++) {
			char byte = *(((char *)addr) + pos);

			/* Print out ascii characters, periods otherwise */
			if (byte < 128 && byte > 32) {
				workspace3[pos] = byte;
			} else if (byte == '\0' && pos + 1 == prefix->size) {
				workspace3[pos] = byte;
				break;
			} else {
				workspace3[pos] = '.';
			}
		}

		/* Format our ptr key for the object */
		snprintf(workspace2, sizeof(workspace2), "%p", (void *)addr);

		ks_json_add_item_to_object(heap_stat_array, workspace2, ks_json_create_string(ks_thr_sprintf("[%s]", workspace3)));
		index++;
	}

	/* If we didn't add anything and they only wanted to see new blocks only return NULL to indicate that to the caller */
	if (index == 0 && new_only) {
		ks_json_delete(&heap_stats_object);
		heap_stats_object = NULL;
	}

error:

	ks_mutex_unlock(pool->mutex);

	if (KS_STATUS_SUCCESS != err) {
		ks_json_delete(&heap_stats_object);
		return NULL;
	}

	return heap_stats_object;
}

static ks_status_t __pack_pool_callback(struct ks_pool_s *pool, ks_debug_pool_pack_ctx_t *ctx)
{
	ks_json_t *pool_object = NULL, *heap_stats_object = NULL;
	char workspace[1024] = {0};
	ks_status_t err = KS_STATUS_SUCCESS;

	pool_object = ks_json_create_object();

	if (NULL == pool_object) {
		return KS_STATUS_NO_MEM;
	}

	// Fill it in
	ks_json_add_item_to_object(pool_object, "flags", ks_json_create_string_fmt("%ld", pool->flags));
	ks_json_add_item_to_object(pool_object, "user_alloc", ks_json_create_string(ks_human_readable_size(pool->user_alloc, 1, sizeof(workspace), workspace)));
	ks_json_add_item_to_object(pool_object, "max_alloc", ks_json_create_string(ks_human_readable_size(pool->max_alloc, 1, sizeof(workspace), workspace)));

	if (ctx->type == KS_DEBUG_POOL_PACK_TYPE_POOL || ctx->type == KS_DEBUG_POOL_PACK_TYPE_POOL_HEAP) {
		heap_stats_object = __pack_pool_stats(pool, ctx->type, ctx->new_only);

		/* Skip this pool if no heap stats were returned due to the new_only check */
		if (!heap_stats_object) {
			if (ctx->new_only) {
				ks_json_delete(&pool_object);
				pool_object = NULL;
			} else {
				err = KS_STATUS_FAIL;
				goto error;
			}
		} else {
			ks_json_add_item_to_object(pool_object, "heap_stats", heap_stats_object);
		}
	}

	if (pool_object) {
		/* Add the inner pool object with the tag name + file + line, for the unique
		 * key so that pool stats will never collide */
		snprintf(workspace, sizeof(workspace), "%s - %s:%d alloc_c: %zu address: %p", pool->tag, pool->file, pool->line, pool->alloc_c, (void *)pool);
		ks_json_add_item_to_object(ctx->pools_object, workspace, pool_object);
	}

error:
	if (KS_STATUS_SUCCESS != err) {
		ks_json_delete(&pool_object);
		ks_json_delete(&heap_stats_object);
	}

	return err;
}

static ks_status_t __pack_pool_summary(ks_json_t *object, ks_debug_pool_pack_ctx_t *summary)
{
	ks_json_t *summary_object = ks_json_create_object();
	ks_status_t err = KS_STATUS_SUCCESS;
	char workspace[256] = {0};

	if (NULL == summary_object) {
		err = KS_STATUS_NO_MEM;
		return err;
	}

	ks_json_add_item_to_object(summary_object, "alloc_c", ks_json_create_string_fmt("%ld", summary->alloc_c));
	ks_json_add_item_to_object(summary_object, "user_alloc", ks_json_create_string(ks_human_readable_size(summary->user_alloc, 1, sizeof(workspace), workspace)));
	ks_json_add_item_to_object(summary_object, "max_alloc", ks_json_create_string(ks_human_readable_size(summary->max_alloc, 1, sizeof(workspace), workspace)));
	ks_json_add_item_to_object(summary_object, "total_count", ks_json_create_string_fmt("%lu", summary->total_count));
	ks_json_add_item_to_object(object, "summary", summary_object);

error:
	if (KS_STATUS_SUCCESS != err) {
		ks_json_delete(&summary_object);
	}

	return err;
}

/*
 * ks_debug_pool_pack_stats
 *
 * DESCRIPTION:
 *
 * Packs and returns a ks_json_t array that describes debug info about the
 * pool allocation statistics in ks. Only available when built with KS_DEBUG_POOL.
 *
 * RETURNS:
 *
 * Success - ks_json_t * Allocated json payload.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 * ks_debug_pool_pack_type_t - The type of pack to create.
 * ks_bool_t - If true will only return items it hasn't returned before, useful for seeing whats new.
 *
 * NOTES:
 */

KS_DECLARE(ks_json_t *) ks_debug_pool_pack_stats(ks_debug_pool_pack_type_t type, ks_bool_t new_only)
{
	ks_json_t *object = NULL;
	ks_json_t *pools_object = NULL;
	ks_debug_pool_pack_ctx_t ctx = {0};
	ks_status_t err = KS_STATUS_SUCCESS;

	ctx.type = type;
	ctx.new_only = new_only;

	g_default_scanned_value = 2;

	object = ks_json_create_object();

	if (NULL == object) {
		err = KS_STATUS_NO_MEM;
		goto error;
	}

	/* Optionally include the per pool stat
	 * Note: Per heap stats implies per pool stats
	 */
	if (type == KS_DEBUG_POOL_PACK_TYPE_POOL || type == KS_DEBUG_POOL_PACK_TYPE_POOL_HEAP) {
		pools_object = ks_json_create_object();

		if (NULL == pools_object) {
			err = KS_STATUS_NO_MEM;
			goto error;
		}

		/* Stash this in the ctx */
		ctx.pools_object = pools_object;
	}

	if (KS_STATUS_SUCCESS != global_debug_pool_iterate(__pack_pool_callback, &ctx)) {
		err = KS_STATUS_NO_MEM;
		goto error;
	}

	if (KS_STATUS_SUCCESS != __pack_pool_summary(object, &ctx)) {
		err = KS_STATUS_NO_MEM;
		goto error;
	}

	if (pools_object) {
		ks_json_add_item_to_object(object, "pools", pools_object);
	}

error:
	if (KS_STATUS_SUCCESS != err) {
		ks_json_delete(&object);
		ks_json_delete(&pools_object);
		g_default_scanned_value = 0;
		return NULL;
	}

	g_default_scanned_value = 0;

	return object;
}
#endif

/*
 * int ks_pool_raw_close
 *
 * DESCRIPTION:
 *
 * Close/free a memory allocation pool previously opened with
 * ks_pool_open.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - Ks_Pool error code
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to our memory pool.
 */
static ks_status_t ks_pool_raw_close(ks_pool_t *pool)
{
	ks_status_t ret = KS_STATUS_SUCCESS;

	if (ret = ks_pool_clear(pool)) {
		ks_log(KS_LOG_ERROR, "Pool close was not successful for pool at address: %p status: %d\n", (void *)pool, ret);
		goto done;
	}

	if (pool->log_func != NULL) {
		pool->log_func(pool, KS_POOL_FUNC_CLOSE, 0, 0, NULL, NULL, 0);
	}

	ks_mutex_destroy(&pool->mutex);

	free(pool);

done:
	ks_assert(ret == KS_STATUS_SUCCESS);
	return ret;
}


/*
 * ks_status_t ks_pool_close
 *
 * DESCRIPTION:
 *
 * Close/free a memory allocation pool previously opened with
 * ks_pool_open.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - ks_status_t error code
 *
 * ARGUMENTS:
 *
 * poolP <-> Pointer to pointer of our memory pool.
 */

KS_DECLARE(ks_status_t) ks_pool_close(ks_pool_t **poolP)
{
	ks_status_t ret = KS_STATUS_SUCCESS;

	if (!poolP || !*poolP)
		return ret;

	if ((ret = ks_pool_raw_close(*poolP)) == KS_STATUS_SUCCESS)
		*poolP = NULL;

	return ret;
}

/*
 * int ks_pool_clear
 *
 * DESCRIPTION:
 *
 * Wipe an opened memory pool clean so we can start again.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - Ks_Pool error code
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to our memory pool.
 */
KS_DECLARE(ks_status_t) ks_pool_clear(ks_pool_t *pool)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_pool_prefix_t *prefix, *nprefix;

	ks_assert(pool);

	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) goto done;

	if (pool->log_func != NULL) {
		pool->log_func(pool, KS_POOL_FUNC_CLEAR, 0, 0, NULL, NULL, 0);
	}

	ks_mutex_lock(pool->mutex);

	perform_pool_cleanup(pool);

	for (prefix = pool->first; prefix; prefix = nprefix) {
		nprefix = prefix->next;
		free(prefix);
	}
	pool->first = pool->last = NULL;

	ks_mutex_unlock(pool->mutex);

done:
	ks_assert(ret == KS_STATUS_SUCCESS);
	return ret;
}

// @todo fill in documentation
KS_DECLARE(void) ks_pool_pool_verify(ks_pool_t *pool)
{
	ks_mutex_lock(pool->mutex);

	for (ks_pool_prefix_t *prefix = pool->first; prefix; prefix = prefix->next) {
		ks_assertd(ks_pool_verify((void *)((uintptr_t)prefix + KS_POOL_PREFIX_SIZE)));
	}

	ks_mutex_unlock(pool->mutex);
}

// @todo fill in documentation
KS_DECLARE(ks_bool_t) ks_pool_verify(void *addr)
{
	void *fence = NULL;
	ks_pool_prefix_t *prefix = (ks_pool_prefix_t *)((uintptr_t)addr - KS_POOL_PREFIX_SIZE);
	if (!addr) return KS_FALSE;
	CHECK_PREFIX(prefix);
	fence = (void *)((uintptr_t)addr + prefix->size);
	if (check_fence(fence))
		return KS_FALSE;
	return KS_TRUE;
}

// @todo fill in documentation
KS_DECLARE(ks_pool_t *) ks_pool_get(void *addr)
{
	ks_assert(addr);
#ifdef KS_DEBUG_POOL
	ks_pool_prefix_t *prefix = (ks_pool_prefix_t *)((uintptr_t)addr - KS_POOL_PREFIX_SIZE);
	ks_status_t ret = KS_STATUS_SUCCESS;

	CHECK_PREFIX(prefix);

	ret = check_pool(prefix->pool);
	ks_assert(ret == KS_STATUS_SUCCESS);
#endif
	return ((ks_pool_prefix_t *)((uintptr_t)addr - KS_POOL_PREFIX_SIZE))->pool;
}

/*
 * void *__ks_pool_alloc_ex
 *
 * COMPONENTS:
 * ks_pool_alloc_ex
 *
 * DESCRIPTION:
 *
 * Allocate space for bytes inside of an already open memory pool.
 *
 * RETURNS:
 *
 * Success - Pointer to the address to use.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool (NULL will use global).
 *
 * size -> Number of bytes to allocate in the pool.  Must be >0.
 *
 * error_p <- Pointer to integer which, if not NULL, will be set with
 * a ks_pool error code.
 */
KS_DECLARE(void *) __ks_pool_alloc_ex(ks_pool_t *pool, const ks_size_t size, const char *file, int line, const char *tag, ks_status_t *error_p)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	void *addr = NULL;

	/* Default to the global pool if null provided */
	if (!pool) {
		pool = ks_global_pool();
	}

	ks_assert(pool);
	ks_assert(size);

	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) goto done;

	ks_mutex_lock(pool->mutex);
	addr = alloc_mem(pool, size, file, line, tag, &ret);
	ks_mutex_unlock(pool->mutex);

	if (pool->log_func != NULL) {
		pool->log_func(pool, KS_POOL_FUNC_ALLOC, size, 0, addr, NULL, 0);
	}

	ks_assert(addr);

	ks_pool_prefix_t *prefix = (ks_pool_prefix_t *)((uintptr_t)addr - KS_POOL_PREFIX_SIZE);
	CHECK_PREFIX(prefix);

done:
	ks_assert(ret == KS_STATUS_SUCCESS);
	return addr;
}

/*
 * void *__ks_pool_alloc
 *
 * COMPONENTS:
 * ks_pool_alloc
 *
 * DESCRIPTION:
 *
 * Allocate space for bytes inside of an already open memory pool.
 *
 * RETURNS:
 *
 * Success - Pointer to the address to use.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool.
 *
 *
 * size -> Number of bytes to allocate in the pool.  Must be >0.
 *
 */
KS_DECLARE(void *) __ks_pool_alloc(ks_pool_t *pool, const ks_size_t size, const char *file, int line, const char *tag)
{
	return __ks_pool_alloc_ex(pool, size, file, line, tag, NULL);
}

/*
 * void *__ks_pool_calloc_ex
 *
 * COMPONENTS:
 * ks_pool_calloc_ex
 *
 * DESCRIPTION:
 *
 * Allocate space for elements of bytes in the memory pool and zero
 * the space afterwards.
 *
 * RETURNS:
 *
 * Success - Pointer to the address to use.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool.  If NULL then it will do a
 * normal calloc.
 *
 * ele_n -> Number of elements to allocate.
 *
 * ele_size -> Number of bytes per element being allocated.
 *
 * error_p <- Pointer to integer which, if not NULL, will be set with
 * a ks_pool error code.
 */
KS_DECLARE(void *) __ks_pool_calloc_ex(ks_pool_t *pool, const ks_size_t ele_n, const ks_size_t ele_size, const char *file, int line, const char *tag, ks_status_t *error_p)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	void *addr = NULL;
	ks_size_t size;

	/* Default to the global pool if one wasn't specified */
	if (!pool) {
		pool = ks_global_pool();
	}

	ks_assert(pool);
	ks_assert(ele_n);
	ks_assert(ele_size);

	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) goto done;

	size = ele_n * ele_size;

	ks_mutex_lock(pool->mutex);
	addr = alloc_mem(pool, size, file, line, tag, &ret);
	// @todo consider readding the NO_ZERO flag option, in which case must zero the user-space here based on expected calloc behaviour... memset(addr, 0, size);
	ks_mutex_unlock(pool->mutex);

	if (pool->log_func != NULL) {
		pool->log_func(pool, KS_POOL_FUNC_CALLOC, ele_size, ele_n, addr, NULL, 0);
	}

	ks_assert(addr);

done:
	ks_assert(ret == KS_STATUS_SUCCESS);

	return addr;
}

/*
 * void *__ks_pool_calloc
 *
 * COMPONENTS:
 * ks_pool_calloc
 *
 * DESCRIPTION:
 *
 * Allocate space for elements of bytes in the memory pool and zero
 * the space afterwards.
 *
 * RETURNS:
 *
 * Success - Pointer to the address to use.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool.  If NULL then it will do a
 * normal calloc.
 *
 * ele_n -> Number of elements to allocate.
 *
 * ele_size -> Number of bytes per element being allocated.
 *
 */
KS_DECLARE(void *) __ks_pool_calloc(ks_pool_t *pool, const ks_size_t ele_n, const ks_size_t ele_size, const char *file, int line, const char *tag)
{
	return __ks_pool_calloc_ex(pool, ele_n, ele_size, file, line, tag, NULL);
}

/*
 * int ks_pool_free
 *
 * DESCRIPTION:
 *
 * Free an address from a memory pool.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - ks_status_t error code
 *
 * ARGUMENTS:
 *
 * addr <-> Pointer to pointer of Address to free.
 *
 */
KS_DECLARE(ks_status_t) ks_pool_free_ex(void **addrP)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_pool_prefix_t *prefix;
	ks_pool_t *pool;
	void *addr = NULL;

	if (!addrP || !*addrP)
		return KS_STATUS_SUCCESS;
	addr = *addrP;

	prefix = (ks_pool_prefix_t *)((uintptr_t)addr - KS_POOL_PREFIX_SIZE);
	CHECK_PREFIX(prefix);

	pool = prefix->pool;
	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) goto done;

	ks_mutex_lock(pool->mutex);

	if (pool->log_func != NULL) {
		pool->log_func(pool, prefix->refs == 1 ? KS_POOL_FUNC_FREE : KS_POOL_FUNC_DECREF, prefix->size, prefix->refs - 1, addr, NULL, 0);
	}

	ret = free_mem(addr);
	ks_mutex_unlock(pool->mutex);

done:
	if (ret != KS_STATUS_REFS_EXIST) {
		ks_assert(ret == KS_STATUS_SUCCESS);
		*addrP = NULL;
	}

	return ret;
}

/*
 * void *ks_pool_ref_ex
 *
 * DESCRIPTION:
 *
 * Ref count increment an address in a memory pool.
 *
 * RETURNS:
 *
 * Success - The same pointer
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * addr -> The addr to ref
 *
 * error_p <- Pointer to integer which, if not NULL, will be set with
 * a ks_pool error code.
 */
KS_DECLARE(void *) ks_pool_ref_ex(void *addr, ks_status_t *error_p)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_pool_prefix_t *prefix = NULL;
	ks_pool_t *pool = NULL;
	ks_size_t refs;

	ks_assert(addr);

	prefix = (ks_pool_prefix_t *)((uintptr_t)addr - KS_POOL_PREFIX_SIZE);
	CHECK_PREFIX(prefix);

	pool = prefix->pool;
	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) goto done;

	ks_mutex_lock(pool->mutex);
	refs = ++prefix->refs;
	ks_mutex_unlock(pool->mutex);

	if (pool->log_func != NULL) {
		pool->log_func(pool, KS_POOL_FUNC_INCREF, prefix->size, refs, addr, NULL, 0);
	}

done:
	ks_assert(ret == KS_STATUS_SUCCESS);

	return addr;
}

/*
 * void *__ks_pool_resize_ex
 *
 * DESCRIPTION:
 *
 * Reallocate an address in a memory pool to a new size.  This is
 *
 * RETURNS:
 *
 * Success - Pointer to the address to use.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * old_addr -> Previously allocated address.
 *
 * new_size -> New size of the allocation.
 *
 * error_p <- Pointer to integer which, if not NULL, will be set with
 * a ks_pool error code.
 *
 * file/line/tag <- Contextual information for use with KS_DEBUG_POOL
 */
KS_DECLARE(void *) __ks_pool_resize_ex(void *old_addr, const ks_size_t new_size, ks_status_t *error_p, const char *file, int line, const char *tag)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_size_t old_size;
	ks_pool_prefix_t *prefix = NULL;
	ks_pool_t *pool = NULL;
	void *new_addr = NULL;
	ks_size_t required;

	ks_assert(old_addr);
	ks_assert(new_size);

	prefix = (ks_pool_prefix_t *)((uintptr_t)old_addr - KS_POOL_PREFIX_SIZE);
	CHECK_PREFIX(prefix);

	pool = prefix->pool;
	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) {
		SET_POINTER(error_p, ret);
		return NULL;
	}

	ks_mutex_lock(pool->mutex);

	if (prefix->refs > 1) {
		ret = KS_STATUS_NOT_ALLOWED;
		goto done;
	}
	if (new_size == prefix->size) {
		new_addr = old_addr;
		goto done;
	}

	old_size = prefix->size;

	required = KS_POOL_PREFIX_SIZE + new_size + KS_POOL_FENCE_SIZE;
	new_addr = realloc((void *)prefix, required);
	ks_assert(new_addr);

	prefix = (ks_pool_prefix_t *)new_addr;

	prefix->size = new_size;

	new_addr = (void *)((uintptr_t)new_addr + KS_POOL_PREFIX_SIZE);
	write_fence((void *)((uintptr_t)new_addr + new_size));

	if (prefix->prev) prefix->prev->next = prefix;
	else pool->first = prefix;
	if (prefix->next) prefix->next->prev = prefix;
	else pool->last = prefix;

	if (pool->log_func != NULL) {
		pool->log_func(pool, KS_POOL_FUNC_RESIZE, new_size, 0, old_addr, new_addr, old_size);
	}

done:
	ks_mutex_unlock(pool->mutex);

	ks_assert(ret == KS_STATUS_SUCCESS);

	return new_addr;
}

/*
 * void *__ks_pool_resize
 *
 * DESCRIPTION:
 *
 * Reallocate an address in a mmeory pool to a new size.  This is
 * different from realloc in that it needs the old address' size.
 *
 * RETURNS:
 *
 * Success - Pointer to the address to use.
 *
 * Failure - NULL
 *
 * ARGUMENTS:
 *
 * old_addr -> Previously allocated address.
 *
 * new_size -> New size of the allocation.
 *
 * file/line/tag -> Contextual info for use with KS_DEBUG_POOL
 *
 */
KS_DECLARE(void *) __ks_pool_resize(void *old_addr, const ks_size_t new_size, const char *file, int line, const char *tag)
{
	return __ks_pool_resize_ex(old_addr, new_size, NULL, file, line, tag);
}

/*
 * int ks_pool_stats
 *
 * DESCRIPTION:
 *
 * Return stats from the memory pool.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - ks_status_t error code
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool.
 *
 * num_alloced_p <- Pointer to an unsigned long which, if not NULL,
 * will be set to the number of pointers currently allocated in pool.
 *
 * user_alloced_p <- Pointer to an unsigned long which, if not NULL,
 * will be set to the number of user bytes allocated in this pool.
 *
 * max_alloced_p <- Pointer to an unsigned long which, if not NULL,
 * will be set to the maximum number of user bytes that have been
 * allocated in this pool.
 *
 * tot_alloced_p <- Pointer to an unsigned long which, if not NULL,
 * will be set to the total amount of space (including administrative
 * overhead) used by the pool.
 */
KS_DECLARE(ks_status_t) ks_pool_stats(const ks_pool_t *pool, ks_size_t *num_alloced_p, ks_size_t *user_alloced_p, ks_size_t *max_alloced_p, ks_size_t *tot_alloced_p)
{
	ks_status_t ret = KS_STATUS_SUCCESS;

	ks_assert(pool);

	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) goto done;

	SET_POINTER(num_alloced_p, pool->alloc_c);
	SET_POINTER(user_alloced_p, pool->user_alloc);
	SET_POINTER(max_alloced_p, pool->max_alloc);
	SET_POINTER(tot_alloced_p, pool->user_alloc + (pool->alloc_c * (KS_POOL_PREFIX_SIZE + KS_POOL_FENCE_SIZE)));

done:
	ks_assert(ret == KS_STATUS_SUCCESS);
	return ret;
}

/*
 * int ks_pool_set_log_func
 *
 * DESCRIPTION:
 *
 * Set a logging callback function to be called whenever there was a
 * memory transaction.  See ks_pool_log_func_t.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - ks_status_t error code
 *
 * ARGUMENTS:
 *
 * pool -> Pointer to the memory pool.
 *
 * log_func -> Log function (defined in ks_pool.h) which will be called
 * with each ks_pool transaction.
 */
KS_DECLARE(ks_status_t) ks_pool_set_log_func(ks_pool_t *pool, ks_pool_log_func_t log_func)
{
	ks_status_t ret = KS_STATUS_SUCCESS;

	ks_assert(pool);
	ks_assert(log_func);

	if ((ret = check_pool(pool)) != KS_STATUS_SUCCESS) goto done;

	pool->log_func = log_func;

done:
	ks_assert(ret == KS_STATUS_SUCCESS);
	return ret;
}

/*
 * const char *ks_pool_strerror
 *
 * DESCRIPTION:
 *
 * Return the corresponding string for the error number.
 *
 * RETURNS:
 *
 * Success - String equivalient of the error.
 *
 * Failure - String "invalid error code"
 *
 * ARGUMENTS:
 *
 * error -> ks_status_t that we are converting.
 */
KS_DECLARE(const char *) ks_pool_strerror(const ks_status_t error)
{
	switch (error) {
	case KS_STATUS_SUCCESS:
		return "no error";
		break;
	case KS_STATUS_ARG_NULL:
		return "function argument is null";
		break;
	case KS_STATUS_ARG_INVALID:
		return "function argument is invalid";
		break;
	case KS_STATUS_PNT:
		return "invalid ks_pool pointer";
		break;
	case KS_STATUS_POOL_OVER:
		return "ks_pool structure was overwritten";
		break;
	case KS_STATUS_PAGE_SIZE:
		return "could not get system page-size";
		break;
	case KS_STATUS_OPEN_ZERO:
		return "could not open /dev/zero";
		break;
	case KS_STATUS_NO_MEM:
		return "no memory available";
		break;
	case KS_STATUS_SIZE:
		return "error processing requested size";
		break;
	case KS_STATUS_TOO_BIG:
		return "allocation exceeds pool max size";
		break;
	case KS_STATUS_MEM:
		return "invalid memory address";
		break;
	case KS_STATUS_MEM_OVER:
		return "memory lower bounds overwritten";
		break;
	case KS_STATUS_NOT_FOUND:
		return "memory block not found in pool";
		break;
	case KS_STATUS_IS_FREE:
		return "memory address has already been freed";
		break;
	case KS_STATUS_BLOCK_STAT:
		return "invalid internal block status";
		break;
	case KS_STATUS_FREE_ADDR:
		return "invalid internal free address";
		break;
	case KS_STATUS_NO_PAGES:
		return "no available pages left in pool";
		break;
	case KS_STATUS_ALLOC:
		return "system alloc function failed";
		break;
	case KS_STATUS_PNT_OVER:
		return "user pointer admin space overwritten";
		break;
	case KS_STATUS_INVALID_POINTER:
		return "pointer is not valid";
		break;
	default:
		break;
	}

	return "invalid error code";
}

KS_DECLARE(char *) __ks_pstrdup(ks_pool_t *pool, const char *str, const char *file, int line, const char *tag)
{
	char *result;
	ks_size_t len;

	if (!str) {
		return NULL;
	}

	len = (ks_size_t)strlen(str) + 1;
	result = __ks_pool_alloc(pool, len, file, line, tag);
	memcpy(result, str, len);

	return result;
}

KS_DECLARE(char *) __ks_pstrndup(ks_pool_t *pool, const char *str, ks_size_t len, const char *file, int line, const char *tag)
{
	char *result;
	const char *end;

	if (!str) {
		return NULL;
	}

	end = memchr(str, '\0', len);

	if (!end) {
		len = end - str;
	}

	result = ks_pool_alloc(pool, len + 1);
	memcpy(result, str, len);
	result[len] = '\0';

	return result;
}

KS_DECLARE(char *) __ks_pstrmemdup(ks_pool_t *pool, const char *str, ks_size_t len, const char *file, int line, const char *tag)
{
	char *result;

	if (!str) {
		return NULL;
	}

	result = __ks_pool_alloc(pool, len + 1, file, line, tag);
	memcpy(result, str, len);
	result[len] = '\0';

	return result;
}

KS_DECLARE(void *) __ks_pmemdup(ks_pool_t *pool, const void *buf, ks_size_t len, const char *file, int line, const char *tag)
{
	void *result;

	if (!buf) {
		return NULL;
	}

	result = __ks_pool_alloc(pool, len, file, line, tag);
	memcpy(result, buf, len);

	return result;
}

KS_DECLARE(char *) __ks_pstrcat(const char *file, int line, const char *tag, ks_pool_t *pool, ...)
{
	char *endp, *argp;
	char *result;
	ks_size_t lengths[10] = { 0 };
	int i = 0;
	ks_size_t len = 0;
	va_list ap;

	va_start(ap, pool);

	/* get lengths so we know what to allocate, cache some so we don't have to double strlen those */

	while ((argp = va_arg(ap, char *))) {
		ks_size_t arglen = strlen(argp);
		if (i < 10) lengths[i++] = arglen;
		len += arglen;
	}

	va_end(ap);

	result = (char *) __ks_pool_alloc(pool, len + 1, file, line, tag);
	endp = result;

	va_start(ap, pool);

	i = 0;

	while ((argp = va_arg(ap, char *))) {
		len = (i < 10) ? lengths[i++] : strlen(argp);
		memcpy(endp, argp, len);
		endp += len;
	}

	va_end(ap);

	*endp = '\0';

	return result;
}

KS_DECLARE(char *) __ks_psprintf(const char *file, int line, const char *tag, ks_pool_t *pool, const char *fmt, ...)
{
	va_list ap;
	char *result;
	va_start(ap, fmt);
	result = __ks_vpprintf(pool, fmt, ap, file, line, tag);
	va_end(ap);

	return result;
}

KS_DECLARE(void*) __ks_malloc(ks_size_t size, const char *file, int line, const char *tag)
{
	return __ks_pool_alloc(ks_global_pool(), size, file, line, tag);
}

KS_DECLARE(void*) __ks_realloc(void *mem, ks_size_t new_size, const char *file, int line, const char *tag)
{
	return __ks_pool_resize(mem, new_size, file, line, tag);
}

KS_DECLARE(void*) __ks_calloc(size_t count, ks_size_t elem_size, const char *file, int line, const char *tag)
{
	return __ks_pool_calloc(ks_global_pool(), count, elem_size, file, line, tag);
}

KS_DECLARE(void) ks_pool_log_on_close(ks_pool_t *pool)
{
	pool->log_on_close = KS_TRUE;
}

KS_DECLARE(void) ks_free(void *data)
{
	ks_pool_free(&data);
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
