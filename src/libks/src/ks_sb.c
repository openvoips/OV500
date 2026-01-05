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
#include "libks/ks_sb.h"

struct ks_sb_s {
	ks_bool_t pool_owner;
	char *data;
	ks_size_t size;
	ks_size_t used;
};

static void ks_sb_cleanup(void *ptr, void *arg, ks_pool_cleanup_action_t action, ks_pool_cleanup_type_t type)
{
	ks_sb_t *sb = (ks_sb_t *)ptr;

	switch(action) {
	case KS_MPCL_ANNOUNCE:
		break;
	case KS_MPCL_TEARDOWN:
		if (!sb->pool_owner && sb->data) ks_pool_free(&sb->data);
		break;
	case KS_MPCL_DESTROY:
		break;
	}

}

KS_DECLARE(ks_status_t) ks_sb_create(ks_sb_t **sbP, ks_pool_t *pool, ks_size_t preallocated)
{
	ks_sb_t *sb = NULL;
	ks_bool_t pool_owner = KS_FALSE;

	ks_assert(sbP);

	if ((pool_owner = !pool)) ks_pool_open(&pool);
	if (preallocated == 0) preallocated = KS_PRINT_BUF_SIZE * 2;

	sb = ks_pool_alloc(pool, sizeof(ks_sb_t));
	sb->pool_owner = pool_owner;
	sb->data = ks_pool_alloc(pool, preallocated);
	sb->size = preallocated;
	sb->used = 1;

	ks_pool_set_cleanup(sb, NULL, ks_sb_cleanup);

	*sbP = sb;

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_sb_destroy(ks_sb_t **sbP)
{
	ks_sb_t *sb = NULL;

	ks_assert(sbP);
	ks_assert(*sbP);

	sb = *sbP;
	*sbP = NULL;

	if (sb->pool_owner) {
		ks_pool_t *pool = ks_pool_get(sb);
		ks_pool_close(&pool);
	} else ks_pool_free(&sb);

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(const char *) ks_sb_cstr(ks_sb_t *sb)
{
	ks_assert(sb);
	return sb->data;
}

KS_DECLARE(ks_size_t) ks_sb_length(ks_sb_t *sb)
{
	ks_assert(sb);
	return sb->used - 1;
}

KS_DECLARE(ks_status_t) ks_sb_accommodate(ks_sb_t *sb, ks_size_t len)
{
	ks_status_t ret = KS_STATUS_SUCCESS;

	ks_assert(sb);

	if (len == 0) goto done;

	if ((sb->used + len) > sb->size) {
		ks_size_t needed = (sb->used + len) - sb->size;
		if (needed < KS_PRINT_BUF_SIZE) needed = KS_PRINT_BUF_SIZE;
		sb->size += needed;
		if (!sb->data) sb->data = ks_pool_alloc(ks_pool_get(sb), sb->size);
		else {
			sb->data = ks_pool_resize(sb->data, sb->size);
			if (!sb->data) ret = KS_STATUS_FAIL;
		}
	}

done:
	return ret;
}

KS_DECLARE(ks_status_t) ks_sb_append(ks_sb_t *sb, const char *str)
{
	ks_status_t ret = KS_STATUS_SUCCESS;

	ks_assert(sb);

	if (str) ret = ks_sb_append_ex(sb, str, strlen(str));

	return ret;
}

KS_DECLARE(ks_status_t) ks_sb_append_ex(ks_sb_t *sb, const char *str, ks_size_t len)
{
	ks_status_t ret = KS_STATUS_SUCCESS;

	ks_assert(sb);

	if (!str || len == 0) goto done;

	if (ks_sb_accommodate(sb, len) != KS_STATUS_SUCCESS) {
		ret = KS_STATUS_FAIL;
		goto done;
	}

	memcpy(sb->data + (sb->used - 1), str, len + 1);
	sb->used += len;

done:

	return ret;
}

KS_DECLARE(ks_status_t) ks_sb_printf(ks_sb_t *sb, const char *fmt, ...)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	va_list ap;
	ks_size_t used = 0;
	char *result = NULL;

	ks_assert(sb);
	ks_assert(fmt);

	used = sb->used - 1;

	if (ks_sb_accommodate(sb, KS_PRINT_BUF_SIZE) != KS_STATUS_SUCCESS) {
		ret = KS_STATUS_FAIL;
		goto done;
	}

	va_start(ap, fmt);
	result = ks_vsnprintfv(sb->data + used, (int)(sb->size - used), fmt, ap);
	va_end(ap);

	sb->used += strlen(result);

done:
	return ret;
}

KS_DECLARE(ks_status_t) ks_sb_json(ks_sb_t *sb, ks_json_t *json)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
	char *str = NULL;

	ks_assert(sb);
	ks_assert(json);

	str = ks_json_print(json);
	if (!str) {
		ret = KS_STATUS_FAIL;
		goto done;
	}

	ks_sb_append(sb, str);

done:
	if (str) ks_pool_free(&str);

	return ret;
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
