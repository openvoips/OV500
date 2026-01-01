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

#include "ks.h"

#ifndef _KS_DSO_H
#define _KS_DSO_H

KS_BEGIN_EXTERN_C

//typedef void (*ks_func_ptr_t) (void);
typedef void * ks_dso_lib_t;
typedef struct ks_dso_s ks_dso_t;
typedef struct ks_dso_callbacks_s ks_dso_callbacks_t;

struct ks_dso_s {
	ks_dso_lib_t lib;
	char *name;
	ks_dso_callbacks_t *callbacks;
	ks_pool_t *pool;
	void *data1;
	void *data2;
};

#define KS_DSO_CALLBACK_ARGS (ks_dso_t *dso)

typedef ks_status_t (*ks_dso_callback_t) KS_DSO_CALLBACK_ARGS;

struct ks_dso_callbacks_s {
	ks_dso_callback_t load;
	ks_dso_callback_t unload;
};

#define KS_DSO_LOAD_FUNCTION(name) ks_status_t name KS_DSO_CALLBACK_ARGS
#define KS_DSO_UNLOAD_FUNCTION(name) ks_status_t name KS_DSO_CALLBACK_ARGS


#define KS_DSO_DEFINITION(name, load, unload)				\
KS_DECLARE_DATA ks_dso_callbacks_t name##_dso_callbacks = {	\
	load,													\
	unload													\
}

KS_DECLARE(void) ks_dso_shutdown(void);

KS_DECLARE(ks_status_t) ks_dso_destroy(ks_dso_lib_t *lib);
KS_DECLARE(ks_dso_lib_t) ks_dso_open(const char *path, char **err);
KS_DECLARE(void *) ks_dso_get_sym(ks_dso_lib_t lib, const char *sym, char **err);
//KS_DECLARE(char *) ks_build_dso_path(const char *name, char *path, ks_size_t len);

KS_DECLARE(ks_status_t) ks_dso_load(const char *name, void *data1, void *data2);
KS_DECLARE(ks_status_t) ks_dso_unload(const char *name);

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

