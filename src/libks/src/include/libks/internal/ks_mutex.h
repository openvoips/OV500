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

typedef enum {
	KS_MUTEX_TYPE_DEFAULT,
	KS_MUTEX_TYPE_NON_RECURSIVE
} ks_mutex_type_t;

#ifdef KS_DEBUG_MUTEX
typedef struct ks_mutex_lock_record_s {
	uint64_t owner_count;
	ks_pid_t owner_id;
	pid_t owner_unique_id;
	const char *owner_file;
	int owner_line;
	const char *owner_tag;
} ks_mutex_lock_record_t;
#endif

struct ks_mutex {
#ifdef WIN32
	CRITICAL_SECTION mutex;
	HANDLE handle;
#else
	pthread_mutex_t mutex;
#endif
	ks_mutex_type_t type;
	uint32_t flags;
	uint8_t malloc;
#ifdef KS_DEBUG_MUTEX
	ks_mutex_lock_record_t record;
#endif
};
