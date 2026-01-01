/*
 * Memory pool defines.
 *
 * Copyright 1996 by Gray Watson.
 *
 * This file is part of the ks_mpool package.
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
 * $Id: ks_mpool.h,v 1.4 2006/05/31 20:26:11 gray Exp $
 */

#ifndef __KS_POOL_H__
#define __KS_POOL_H__

#include "ks.h"

KS_BEGIN_EXTERN_C

/*
 * ks_pool flags to ks_pool_alloc or ks_pool_set_attr
 */

typedef enum {
	KS_POOL_FLAG_DEFAULT = 0
} ks_pool_flag_t;

/*
 * Ks_Pool function IDs for the ks_pool_log_func callback function.
 */
#define KS_POOL_FUNC_CLOSE 1	/* ks_pool_close function called */
#define KS_POOL_FUNC_CLEAR 2	/* ks_pool_clear function called */
#define KS_POOL_FUNC_ALLOC 3	/* ks_pool_alloc function called */
#define KS_POOL_FUNC_CALLOC 4	/* ks_pool_calloc function called */
#define KS_POOL_FUNC_FREE  5	/* ks_pool_free function called */
#define KS_POOL_FUNC_RESIZE 6	/* ks_pool_resize function called */
#define KS_POOL_FUNC_INCREF 7	/* reference count incremented */
#define KS_POOL_FUNC_DECREF 8	/* reference count decremented */

 /*
  * On machines with a small stack size, you can redefine the
  * KS_PRINT_BUF_SIZE to be less than 350.  But beware - for
  * smaller values some %f conversions may go into an infinite loop.
 */
#ifndef KS_PRINT_BUF_SIZE
# define KS_PRINT_BUF_SIZE 350
#endif
#define etBUFSIZE KS_PRINT_BUF_SIZE	/* Size of the output buffer */

/*
 * void ks_pool_log_func_t
 *
 * DESCRIPTION:
 *
 * Ks_Pool transaction log function.
 *
 * RETURNS:
 *
 * None.
 *
 * ARGUMENT:
 *
 * pool -> Associated ks_pool address.
 *
 * func_id -> Integer function ID which identifies which ks_pool
 * function is being called.
 *
 * byte_size -> Optionally specified byte size.
 *
 * ele_n -> Optionally specified element number.  For ks_pool_calloc
 * only.
 *
 * new_addr -> Optionally specified new address.  For ks_pool_alloc,
 * ks_pool_calloc, and ks_pool_resize only.
 *
 * old_addr -> Optionally specified old address.  For ks_pool_resize and
 * ks_pool_free only.
 *
 * old_byte_size -> Optionally specified old byte size.  For
 * ks_pool_resize only.
 */
typedef void (*ks_pool_log_func_t) (const void *pool,
									 const int func_id,
									 const ks_size_t byte_size,
									 const ks_size_t ele_n, const void *old_addr, const void *new_addr, const ks_size_t old_byte_size);

/*
 * ks_pool_t *ks_pool_open
 *
 * COMPONENTS:
 * 	ks_pool_open_ex
 * 	ks_pool_tagged_open
 *
 * DESCRIPTION:
 *
 * Open/allocate a new memory pool.
 *
 * RETURNS:
 *
 * Success - KS_STATUS_SUCCESS
 *
 * Failure - ks_status_t error code
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

KS_DECLARE(ks_status_t) ks_pool_tagged_open(ks_pool_t **poolP, const char *fileP, int line, const char *tagP);

#define ks_pool_open(poolP) ks_pool_tagged_open((poolP), __FILE__, __LINE__, __KS_FUNC__)
#define ks_pool_open_ex(poolP, tag) ks_pool_tagged_open((poolP), __FILE__, __LINE__, NULL, tag)

#ifdef KS_DEBUG_POOL

/*
 * ks_pool_pack_stats
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
 *
 * NOTES:
 */

typedef enum {
	KS_DEBUG_POOL_PACK_TYPE_GLOBAL,
	KS_DEBUG_POOL_PACK_TYPE_POOL,
	KS_DEBUG_POOL_PACK_TYPE_POOL_HEAP
} ks_debug_pool_pack_type_t;

KS_DECLARE(ks_json_t *) ks_debug_pool_pack_stats(ks_debug_pool_pack_type_t type, ks_bool_t new_only);

#endif

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

KS_DECLARE(ks_status_t) ks_pool_close(ks_pool_t **poolP);

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
 * Failure - ks_status_t error code
 *
 * ARGUMENTS:
 *
 * pool <-> Pointer to our memory pool.
 */

KS_DECLARE(ks_status_t) ks_pool_clear(ks_pool_t *pool);

KS_DECLARE(ks_bool_t) ks_pool_verify(void *addr);
KS_DECLARE(void) ks_pool_pool_verify(ks_pool_t *pool);
KS_DECLARE(ks_pool_t *) ks_pool_get(void *addr);

KS_DECLARE(void) ks_pool_log_on_close(ks_pool_t *pool);

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
 * size -> Number of bytes to allocate in the pool.  Must be >0.
 *
 */
KS_DECLARE(void *) __ks_pool_alloc(ks_pool_t *pool, const ks_size_t size, const char *file, int line, const char *tag);

#define ks_pool_alloc(pool, size) __ks_pool_alloc(pool, size, __FILE__, __LINE__, __KS_FUNC__)

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
 * pool -> Pointer to the memory pool.
 *
 * size -> Number of bytes to allocate in the pool.  Must be >0.
 *
 * error_p <- Pointer to integer which, if not NULL, will be set with
 * a ks_pool error code.
 */
KS_DECLARE(void *) __ks_pool_alloc_ex(ks_pool_t *pool, const ks_size_t size, const char *file, int line, const char *tag, ks_status_t *error_p);

#define ks_pool_alloc_ex(pool, size, error_p) __ks_pool_alloc_ex(pool, size, __FILE__, __LINE__, __KS_FUNC__, error_p)

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
 * pool -> Pointer to the memory pool.
 *
 * ele_n -> Number of elements to allocate.
 *
 * ele_size -> Number of bytes per element being allocated.
 *
 */
KS_DECLARE(void *) __ks_pool_calloc(ks_pool_t *pool, const ks_size_t ele_n, const ks_size_t ele_size, const char *file, int line, const char *tag);

#define ks_pool_calloc(pool, ele_n, ele_size) __ks_pool_calloc(pool, ele_n, ele_size, __FILE__, __LINE__, __KS_FUNC__)

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
 * pool -> Pointer to the memory pool.
 *
 * ele_n -> Number of elements to allocate.
 *
 * ele_size -> Number of bytes per element being allocated.
 *
 * error_p <- Pointer to integer which, if not NULL, will be set with
 * a ks_pool error code.
 */
KS_DECLARE(void *) __ks_pool_calloc_ex(ks_pool_t *pool, const ks_size_t ele_n, const ks_size_t ele_size, const char *file, int line, const char *tag, ks_status_t *error_p);

#define ks_pool_calloc_ex(pool, ele_n, ele_size, error_p) __ks_pool_calloc_ex(pool, ele_n, ele_size, __FILE__, __LINE__, __KS_FUNC__, error_p)

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
 * addr <-> Address to free.
 *
 */

KS_DECLARE(ks_status_t) ks_pool_free_ex(void **addrP);


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

KS_DECLARE(void *) ks_pool_ref_ex(void *addr, ks_status_t *error_p);

#define ks_pool_ref(_x) ks_pool_ref_ex(_x, NULL)

/*
 * void *__ks_pool_resize
 *
 * DESCRIPTION:
 *
 * Reallocate an address in a memory pool to a new size.
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
 * file/line/tag -> Contextual information for use with KS_DEBUG_POOL
 *
 */
KS_DECLARE(void *) __ks_pool_resize(void *old_addr, const ks_size_t new_size, const char *file, int line, const char *tag);
#define ks_pool_resize(old_addr, new_size) __ks_pool_resize(old_addr, new_size, __FILE__, __LINE__, __KS_FUNC__)

/*
 * void *__ks_pool_resize_ex
 *
 * DESCRIPTION:
 *
 * Reallocate an address in a memory pool to a new size.
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
 * file/line/tag -> Contextual information for use with KS_DEBUG_POOL
 */
KS_DECLARE(void *) __ks_pool_resize_ex(void *old_addr, const ks_size_t new_size, ks_status_t *error_p, const char *file, int line, const char *tag);
#define ks_pool_resize_ex(old_addr, new_size, error_p)	__ks_pool_resize_ex(old_addr, new_size, error_p, __FILE__, __LINE__, __KS_FUNC__)

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
KS_DECLARE(ks_status_t) ks_pool_stats(const ks_pool_t *pool, ks_size_t *num_alloced_p, ks_size_t *user_alloced_p, ks_size_t *max_alloced_p, ks_size_t *tot_alloced_p);

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
KS_DECLARE(ks_status_t) ks_pool_set_log_func(ks_pool_t *pool, ks_pool_log_func_t log_func);

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
 * error -> Error number that we are converting.
 */
KS_DECLARE(const char *) ks_pool_strerror(const ks_status_t error);

KS_DECLARE(ks_status_t) ks_pool_set_cleanup(void *ptr, void *arg, ks_pool_cleanup_callback_t callback);
KS_DECLARE(ks_status_t) ks_pool_remove_cleanup(void *ptr);

#define ks_pool_free(_x) ks_pool_free_ex((void **)_x)

/*<<<<<<<<<<   This is end of the auto-generated output from fillproto. */

/* We define these as macros so we can track their allocation points */
KS_DECLARE(char *) __ks_pstrdup(ks_pool_t *pool, const char *str, const char *file, int line, const char *tag);
KS_DECLARE(char *) __ks_pstrndup(ks_pool_t *pool, const char *str, ks_size_t len, const char *file, int line, const char *tag);
KS_DECLARE(char *) __ks_pstrmemdup(ks_pool_t *pool, const char *str, ks_size_t len, const char *file, int line, const char *tag);
KS_DECLARE(void *) __ks_pmemdup(ks_pool_t *pool, const void *buf, ks_size_t len, const char *file, int line, const char *tag);
KS_DECLARE(char *) __ks_pstrcat(const char *file, int line, const char *tag, ks_pool_t *pool, ...);
KS_DECLARE(char *) __ks_psprintf(const char *file, int line, const char *tag, ks_pool_t *pool, const char *fmt, ...);

#define ks_pstrdup(pool, str)  __ks_pstrdup(pool, str, __FILE__, __LINE__, __KS_FUNC__)
#define ks_pstrndup(pool, str, len) __ks_pstrndup(pool, str, len, __FILE__, __LINE__, __KS_FUNC__)
#define ks_pstrmemdup(pool, str, len) __ks_pstrmemdup(pool, str, len, __FILE__, __LINE__, __KS_FUNC__)
#define ks_pmemdup(pool, buf, len) __ks_pmemdup(pool, buf, len, __FILE__, __LINE__, __KS_FUNC__)
#define ks_pstrcat(pool, ...) __ks_pstrcat(__FILE__, __LINE__, __KS_FUNC__, pool, __VA_ARGS__)
#define ks_psprintf(pool, fmt, ...) __ks_psprintf(__FILE__, __LINE__, __KS_FUNC__, pool, fmt, __VA_ARGS__)
#define ks_pexplode(pool, string, delimiter) __ks_pexplode(pool, string, delimiter, __FILE__, __LINE__, __KS_FUNC__)

/* For non pool allocations we still create wrappers so that when needed we can track the allocations should there be a leak */
KS_DECLARE(void*) __ks_malloc(ks_size_t size, const char *file, int line, const char *tag);
KS_DECLARE(void*) __ks_realloc(void *mem, ks_size_t new_size, const char *file, int line, const char *tag);
KS_DECLARE(void*) __ks_calloc(size_t count, ks_size_t elem_size, const char *file, int line, const char *tag);

#define ks_calloc(count, elem_size)	__ks_calloc(count, elem_size, __FILE__, __LINE__, __KS_FUNC__)
#define ks_realloc(mem, new_size) __ks_realloc(mem, new_size, __FILE__, __LINE__, __KS_FUNC__)
#define ks_malloc(size)	__ks_malloc(size, __FILE__, __LINE__, __KS_FUNC__)

#define ks_safe_free(_x) do { if (_x) { free(_x); _x = NULL; } } while (KS_FALSE)

KS_DECLARE(void) ks_free(void *data);

KS_END_EXTERN_C

#endif /* ! __KS_POOL_H__ */

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
