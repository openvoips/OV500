/*
 * Private header definitions for memory pool.
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


#define KS_POOL_MAGIC		 0xDEADBEEF	/* magic for struct */

#define KS_POOL_PREFIX_MAGIC 0xDEADBEEF

#define KS_POOL_FENCE_MAGIC0		 (ks_byte_t)(0xFAU)	/* 1st magic mem byte */
#define KS_POOL_FENCE_MAGIC1		 (ks_byte_t)(0xD3U)	/* 2nd magic mem byte */

#define KS_POOL_FENCE_SIZE			 2		/* fence space */

typedef struct ks_pool_prefix_s ks_pool_prefix_t;

struct ks_pool_prefix_s {
	ks_size_t magic1;
	ks_size_t size;
	ks_size_t magic2;
	ks_size_t refs;
	ks_pool_prefix_t *prev;
	ks_pool_prefix_t *next;
	ks_size_t magic3;
#ifdef KS_DEBUG_POOL
	int line;
	const char *file;
	const char *tag;
	ks_bool_t scanned;
#endif
	ks_pool_cleanup_callback_t cleanup_callback;
	void *cleanup_arg;
	ks_size_t magic4;
	ks_pool_t *pool;
	ks_size_t magic5;
};

#define KS_POOL_PREFIX_SIZE sizeof(ks_pool_prefix_t)

#define SET_POINTER(pnt, val)					\
	do {										\
		if ((pnt) != NULL) {					\
			(*(pnt)) = (val);					\
		}										\
	} while(0)

struct ks_pool_s {
	ks_size_t magic1; /* magic number for struct */
	ks_size_t flags; /* flags for the struct */
	ks_size_t alloc_c; /* number of allocations */
	ks_size_t user_alloc; /* user bytes allocated */
	ks_size_t max_alloc; /* maximum user bytes allocated */
	ks_pool_log_func_t log_func; /* log callback function */
	ks_pool_prefix_t *first; /* first memory allocation we are using */
	ks_pool_prefix_t *last; /* last memory allocation we are using */
	ks_bool_t log_on_close; /* when true will log all un-released allocations on close, used for the global pool */
	int line; /* line pool was allocated on (from ks_pool_open macro) */
	const char *file; /* ptr to constant literal string from __FILE__ macro */
	const char *tag; /* ptr to constant literal string from ks_pool_open macro */
#ifdef KS_DEBUG_POOL
	/* When KS_DEBUG_POOL is enabled, we can iterate pools live to diagnose them */
	struct ks_pool_s *debug_first;
	struct ks_pool_s *debug_last;
	struct ks_pool_s *debug_prev;
	struct ks_pool_s *debug_next;
#endif
	ks_size_t magic2; /* upper magic for overwrite sanity */
	ks_mutex_t *mutex;
	ks_bool_t cleaning_up;
};
