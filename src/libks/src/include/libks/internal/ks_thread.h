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

 #include "libks/ks_atomic.h"

 #pragma once

struct ks_thread {
	const char *tag;	/* Must be constant literal string */
	ks_pid_t id;		/* The system thread id (same thing ks_thread_self_id returns) */

#ifdef KS_PLAT_WIN
	void *handle;
#else
	pthread_t handle;
	pthread_attr_t attribute;
#endif
	void *private_data;
	ks_thread_function_t function;
	size_t stack_size;
	uint32_t flags;

	ks_bool_t stop_requested;

	uint8_t priority;
	void *return_data;
	ks_pool_t *pool_to_destroy;

	ks_mutex_t *mutex;
	ks_bool_t in_use;
};
