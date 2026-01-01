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

KS_DECLARE(ks_uuid_t *) ks_uuid(ks_uuid_t *uuid)
{
#ifdef KS_PLAT_WIN
    UuidCreate(uuid);
#else
    uuid_generate_random((unsigned char *)uuid);
#endif
	return uuid;
}

KS_DECLARE(char *) __ks_uuid_str(ks_pool_t *pool, ks_uuid_t *uuid, const char *file, int line, const char *tag)
{
#ifdef KS_PLAT_WIN
    unsigned char *str, *uuidstr;
    UuidToStringA(uuid, &str);
	uuidstr = __ks_pstrdup(pool, (const char *)str, file, line, tag);
    RpcStringFreeA(&str);
	return uuidstr;
#else
    char str[37] = { 0 };
    uuid_unparse((unsigned char *)uuid, str);
	return __ks_pstrdup(pool, str, file, line, tag);
#endif
}

KS_DECLARE(ks_uuid_t *) ks_uuid_dup(ks_pool_t *pool, ks_uuid_t *uuid)
{
	ks_uuid_t *clone;

	if (!(clone = ks_pool_alloc(pool, sizeof(ks_uuid_t))))
		return NULL;

	memcpy(clone, uuid, sizeof(ks_uuid_t));
	return clone;
}

KS_DECLARE(ks_bool_t) ks_uuid_is_null(const ks_uuid_t *uuid) {
	static ks_uuid_t null_uuid = {0};
	return memcmp(uuid, &null_uuid, sizeof(ks_uuid_t)) == 0;
}

KS_DECLARE(const char *) __ks_uuid_null_str(ks_pool_t *pool, const char *file, int line, const char *tag)
{
	static ks_uuid_t null_uuid = {0};
	return __ks_uuid_str(pool, &null_uuid, file, line, tag);
}

KS_DECLARE(const char *) ks_uuid_null_thr_str(void)
{
	static ks_uuid_t null_uuid = {0};
	return ks_uuid_thr_str(&null_uuid);
}

KS_DECLARE(const char *) ks_uuid_thr_str(const ks_uuid_t *uuid)
{
	static KS_THREAD_LOCAL char uuid_str[37];

#ifdef KS_PLAT_WIN
    unsigned char *str;
    UuidToStringA(uuid, &str);
	strncpy(uuid_str, str, sizeof(uuid_str));
    RpcStringFreeA(&str);
	return uuid_str;
#else
    uuid_unparse((unsigned char *)uuid, uuid_str);
	return uuid_str;
#endif
}

KS_DECLARE(ks_uuid_t) ks_uuid_from_str(const char * const string)
{
	ks_uuid_t uuid = {0};
#ifdef KS_PLAT_WIN
	if (UuidFromStringA((unsigned char *)string, &uuid) != RPC_S_OK) {
		return uuid;	 /* Null uuid indicates failure */
	}
	return uuid;
#else
	if (uuid_parse(string, (unsigned char *)&uuid) != 0) {
		return uuid; /* Null uuid indicates failure */
	}
#endif
	return uuid;
}

KS_DECLARE(ks_uuid_t) ks_uuid_null(void)
{
	ks_uuid_t uuid = {0};
	return uuid;
}
