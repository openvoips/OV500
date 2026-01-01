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

ks_hash_t *g_loaded = NULL;

#ifdef WIN32
#include <windows.h>
#include <stdio.h>

KS_DECLARE(ks_status_t) ks_dso_destroy(ks_dso_lib_t *lib) {
	if (lib && *lib) {
		FreeLibrary(*lib);
		*lib = NULL;
	}
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_dso_lib_t) ks_dso_open(const char *path, char **err) {
    HINSTANCE lib;
#ifdef UNICODE
	size_t len = strlen(path) + 1;
	wchar_t *wpath = malloc(len * 2);

	size_t converted;
	mbstowcs_s(&converted, wpath, len, path, _TRUNCATE);
#else
	const char * wpath = path;
#endif
	lib = LoadLibraryEx(wpath, NULL, 0);

	if (!lib) {
		LoadLibraryEx(wpath, NULL, LOAD_WITH_ALTERED_SEARCH_PATH);
	}

	if (!lib) {
		DWORD error = GetLastError();
		char tmp[80];
		sprintf(tmp, "dll open error [%lu]\n", error);
		*err = strdup(tmp);
	}

#ifdef UNICODE
	free(wpath);
#endif
	return lib;
}

KS_DECLARE(void*) ks_dso_get_sym(ks_dso_lib_t lib, const char *sym, char **err) {
	FARPROC func = GetProcAddress(lib, sym);
	if (!func) {
		DWORD error = GetLastError();
		char tmp[80];
		sprintf(tmp, "dll sym error [%lu]\n", error);
		*err = strdup(tmp);
	}
	return (void *)(intptr_t)func; // this should really be addr - ks_dso_func_data
}

#else

/*
** {========================================================================
** This is an implementation of loadlib based on the dlfcn interface.
** The dlfcn interface is available in Linux, SunOS, Solaris, IRIX, FreeBSD,
** NetBSD, AIX 4.2, HPUX 11, and  probably most other Unix flavors, at least
** as an emulation layer on top of native functions.
** =========================================================================
*/

#include <dlfcn.h>

KS_DECLARE(ks_status_t) ks_dso_destroy(ks_dso_lib_t *lib) {
	int rc;
	if (lib && *lib) {
		rc = dlclose(*lib);
		if (rc) {
			//ks_log(KS_LOG_ERROR, "Failed to close lib %p: %s\n", *lib, dlerror());
			return KS_STATUS_FAIL;
		}
		//ks_log(KS_LOG_DEBUG, "lib %p was closed with success\n", *lib);
		*lib = NULL;
		return KS_STATUS_SUCCESS;
	}
	//ks_log(KS_LOG_ERROR, "Invalid pointer provided to ks_dso_destroy\n");
	return KS_STATUS_FAIL;
}

KS_DECLARE(ks_dso_lib_t) ks_dso_open(const char *path, char **err) {
	void *lib = dlopen(path, RTLD_NOW | RTLD_LOCAL);
	if (lib == NULL) {
		*err = strdup(dlerror());
	}
	return lib;
}

KS_DECLARE(void *) ks_dso_get_sym(ks_dso_lib_t lib, const char *sym, char **err) {
	void *func = dlsym(lib, sym);
	if (!func) {
		*err = strdup(dlerror());
	}
	return func;
}
#endif


static void ks_dso_cleanup(void *ptr, void *arg, ks_pool_cleanup_action_t action, ks_pool_cleanup_type_t type)
{
	ks_dso_t *dso = (ks_dso_t *)ptr;

	switch (action) {
	case KS_MPCL_ANNOUNCE:
		break;
	case KS_MPCL_TEARDOWN:
		ks_pool_close(&dso->pool);
		ks_pool_free(&dso->name);
		ks_dso_destroy(&dso->lib);
		break;
	case KS_MPCL_DESTROY:
		break;
	}
}

KS_DECLARE(void) ks_dso_shutdown(void)
{
	if (g_loaded) {
		ks_pool_t *pool = ks_pool_get(g_loaded);

		for (ks_hash_iterator_t *it = ks_hash_first(g_loaded, KS_UNLOCKED); it; it = ks_hash_next(&it)) {
			const char *key = NULL;
			ks_dso_t *dso = NULL;

			ks_hash_this(it, (const void **)&key, NULL, (void **)&dso);
			dso->callbacks->unload(dso);
		}

		ks_pool_close(&pool);
	}
}

ks_status_t ks_dso_makesymbol(const char *path, char *filename)
{
	const char *ext = strrchr(path, '.');
	const char *name = strrchr(path, '/');
	ks_size_t len = 0;

	if (!name) name = strrchr(path, '\\');

	if (name) name++;

	if (!name) name = path;

	len = strlen(name);
	if (ext && ext > name) len = ext - name;

	strncpy(filename, name, len);
	filename[len] = '\0';

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_dso_load(const char *name, void *data1, void *data2) {
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_dso_lib_t lib;
	char *err = NULL;
	char symname[1500];
	char filename[1024];
	ks_dso_callbacks_t *callbacks = NULL;
	ks_pool_t *pool = NULL;
	ks_dso_t *dso = NULL;

	ks_assert(name);

	if (name[0] == '\0') {
		ks_log(KS_LOG_DEBUG, "No module name provided\n");
		return KS_STATUS_FAIL;
	}

	lib = ks_dso_open(name, &err);

	if (err) {
		ks_log(KS_LOG_DEBUG, "Failed to load module '%s': %s\n", name, err);
		free(err);
		return KS_STATUS_FAIL;
	}

	ks_dso_makesymbol(name, filename);
	snprintf(symname, sizeof(symname), "%s_dso_callbacks", filename);

	callbacks = (ks_dso_callbacks_t *)ks_dso_get_sym(lib, symname, &err);

	if (err) {
		ks_log(KS_LOG_DEBUG, "Failed to load module '%s': %s\n", name, err);
		free(err);
		return KS_STATUS_FAIL;
	}

	if (!g_loaded) {
		ks_pool_open(&pool);
		ks_hash_create(&g_loaded, KS_HASH_MODE_CASE_INSENSITIVE, KS_HASH_FLAG_FREE_VALUE | KS_HASH_FLAG_RWLOCK, pool);
	} else pool = ks_pool_get(g_loaded);

	ks_hash_write_lock(g_loaded);

	if (ks_hash_search(g_loaded, (void *)filename, KS_UNLOCKED) != NULL) {
		ret = KS_STATUS_DUPLICATE_OPERATION;
		goto done;
	}

	dso = (ks_dso_t *)ks_pool_alloc(pool, sizeof(ks_dso_t));
	dso->lib = lib;
	dso->name = ks_pstrdup(pool, filename);
	dso->callbacks = callbacks;

	ks_pool_open(&dso->pool);
	dso->data1 = data1;
	dso->data2 = data2;

	ks_pool_set_cleanup(dso, NULL, ks_dso_cleanup);

	if ((ret = callbacks->load(dso)) != KS_STATUS_SUCCESS) {
		ks_pool_free(&dso);
	} else {
		ks_hash_insert(g_loaded, (void *)dso->name, (void *)dso);
	}

done:
	ks_hash_write_unlock(g_loaded);

	return ret;
}

KS_DECLARE(ks_status_t) ks_dso_unload(const char *name) {
	ks_status_t ret = KS_STATUS_SUCCESS;
	ks_dso_t *dso = NULL;

	if (!g_loaded) return KS_STATUS_FAIL;

	ks_hash_write_lock(g_loaded);
	dso = (ks_dso_t *)ks_hash_search(g_loaded, (void *)name, KS_UNLOCKED);
	if (dso) {
		ret = dso->callbacks->unload(dso);
		ks_hash_remove(g_loaded, (void *)name);
	} else ret = KS_STATUS_NOT_FOUND;
	ks_hash_write_unlock(g_loaded);

	return ret;
}

/* }====================================================== */

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
