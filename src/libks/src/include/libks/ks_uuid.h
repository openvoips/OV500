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

#pragma once

KS_BEGIN_EXTERN_C

KS_DECLARE(ks_uuid_t *) ks_uuid(ks_uuid_t *uuid);

KS_DECLARE(char *) __ks_uuid_str(ks_pool_t *pool, ks_uuid_t *uuid, const char *file, int line, const char *tag);
#define ks_uuid_str(pool, uuid) __ks_uuid_str(pool, uuid, __FILE__, __LINE__, __KS_FUNC__)

KS_DECLARE(const char *) __ks_uuid_null_str(ks_pool_t *pool, const char *file, int line, const char *tag);
#define ks_uuid_null_str(pool) __ks_uuid_null_str(pool, __FILE__, __LINE__, __KS_FUNC__)

KS_DECLARE(ks_uuid_t) ks_uuid_from_str(const char * const string);
KS_DECLARE(ks_bool_t) ks_uuid_is_null(const ks_uuid_t *uuid);
KS_DECLARE(const char *) ks_uuid_null_thr_str(void);
KS_DECLARE(ks_uuid_t) ks_uuid_null(void);
KS_DECLARE(ks_uuid_t *) ks_uuid_dup(ks_pool_t *pool, ks_uuid_t *uuid);
KS_DECLARE(const char *) ks_uuid_thr_str(const ks_uuid_t *uuid);

#define ks_uuid_cmp(x, y)	memcmp(x, y, sizeof(ks_uuid_t))

KS_END_EXTERN_C
