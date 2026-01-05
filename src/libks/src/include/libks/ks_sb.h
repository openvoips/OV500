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
#ifndef __KS_SB_H__
#define __KS_SB_H__

#include "ks.h"

KS_BEGIN_EXTERN_C

typedef struct ks_sb_s ks_sb_t;

KS_DECLARE(ks_status_t) ks_sb_create(ks_sb_t **sbP, ks_pool_t *pool, ks_size_t preallocated);
KS_DECLARE(ks_status_t) ks_sb_destroy(ks_sb_t **sbP);
KS_DECLARE(const char *) ks_sb_cstr(ks_sb_t *sb);
KS_DECLARE(ks_size_t) ks_sb_length(ks_sb_t *sb);
KS_DECLARE(ks_status_t) ks_sb_accommodate(ks_sb_t *sb, ks_size_t len);
KS_DECLARE(ks_status_t) ks_sb_append(ks_sb_t *sb, const char *str);
KS_DECLARE(ks_status_t) ks_sb_append_ex(ks_sb_t *sb, const char *str, ks_size_t len);
KS_DECLARE(ks_status_t) ks_sb_printf(ks_sb_t *sb, const char *fmt, ...);
KS_DECLARE(ks_status_t) ks_sb_json(ks_sb_t *sb, ks_json_t *json);

KS_END_EXTERN_C

#endif

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
