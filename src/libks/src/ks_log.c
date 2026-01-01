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
#include "libks/ks_atomic.h"

static const char *LEVEL_NAMES[] = {
	"EMERG",
	"ALERT",
	"CRIT",
	"ERROR",
	"WARN",
	"NOTICE",
	"INFO",
	"DEBUG",
	NULL
};

static ks_mutex_t *g_log_mutex;

static int ks_log_level = 7;
static ks_log_prefix_t ks_log_prefix = KS_LOG_PREFIX_DEFAULT;
static ks_bool_t ks_log_jsonified = KS_FALSE;
static char const* ks_log_json_enclose_name = NULL;

KS_DECLARE(void) ks_log_sanitize_string(char *str)
{
	unsigned char *ptr, *s = (void*)str;
	while (*s != '\0') {

		// Ignore tabs (json pretty print) and new lines (legacy)
        switch (*s) {
            case '\n':
            case '\t':
                break;
            default:
                if (!isprint((int)*s))
                    *s = '.';
                break;
        }

		s++;
	}
}

KS_DECLARE(int) ks_log_level_by_name(const char *name) {
	int level = -1;
	for (int index = 0; LEVEL_NAMES[index]; ++index) {
		if (!ks_safe_strcasecmp(name, LEVEL_NAMES[index])) {
			level = index;
			break;
		}
	}
	return level;
}

static const char *cut_path(const char *in)
{
	const char *p, *ret = in;
	char delims[] = "/\\";
	char *i;

	for (i = delims; *i; i++) {
		p = in;
		while ((p = strchr(p, *i)) != 0) {
			ret = ++p;
		}
	}
	return ret;
}

static ks_size_t g_wakeup_stdout_fails = 0;
static ks_size_t g_wakeup_stdout_successes = 0;

#ifndef WIN32
static void __set_blocking(int fd, int blocking)
{
	/* Save the current flags */
	int flags = fcntl(fd, F_GETFL, 0);
	if (flags == -1)
		return;
	if (blocking)
		flags &= ~O_NONBLOCK;
	else
		flags |= O_NONBLOCK;
	fcntl(fd, F_SETFL, flags);
}

static ks_bool_t wakeup_stdout()
{
	char procpath[256];
	FILE *fp = NULL;
	fd_set set;
	struct timeval timeout;
	ks_bool_t done_reading = KS_FALSE;
	char buf[1024];
	size_t skipped = 0;
	
	snprintf(procpath, sizeof(procpath), "/proc/%d/fd/%d", getpid(), fileno(stdout));
	if ((fp = fopen(procpath, "r")) == NULL) {
		g_wakeup_stdout_fails++;
		return KS_FALSE;
	}

	timeout.tv_sec = 0;
	timeout.tv_usec = 0;

	while (!done_reading) {
		FD_ZERO(&set);
		FD_SET(fileno(fp), &set);
		
		if (select(fileno(fp) + 1, &set, NULL, NULL, &timeout) != 1) {
			done_reading = KS_TRUE;
		} else {
			size_t consumed = fread(buf, 1, sizeof(buf), fp);
			skipped += consumed;
			done_reading = consumed < sizeof(buf);
		}
	}
	fclose(fp);

	g_wakeup_stdout_successes++;
	return KS_TRUE;
}
#endif

KS_DECLARE(ks_size_t) ks_log_format_output(char *buf, ks_size_t bufSize, const char *file, const char *func, int line, int level, const char *fmt, va_list ap)
{
	const char *fp;
	char *data = NULL;
	int ret;
	int used = 0;

	fp = cut_path(file);

	ret = ks_vasprintf(&data, fmt, ap);
	ks_log_sanitize_string(data);

	buf[0] = '\0';
	used += 1;

	if (ret != -1) {
		if (g_wakeup_stdout_fails > 0) {
			used += snprintf(buf + used - 1, bufSize - used, "[LF:%zu] ", g_wakeup_stdout_fails);
			if (used >= bufSize) goto done;
		}
		if (g_wakeup_stdout_successes > 0) {
			used += snprintf(buf + used - 1, bufSize - used, "[LS:%zu] ", g_wakeup_stdout_successes);
			if (used >= bufSize) goto done;
		}
		if (ks_log_prefix & KS_LOG_PREFIX_LEVEL) {
			char tbuf[9];
			snprintf(tbuf, sizeof(tbuf), "[%s]", LEVEL_NAMES[level]);
			used += snprintf(buf + used - 1, bufSize - used, "%8s ", tbuf);
			if (used >= bufSize) goto done;
		}
		if (ks_log_prefix & (KS_LOG_PREFIX_DATE | KS_LOG_PREFIX_TIME)) {
			char tbuf[100];
			time_t now = time(0);
			struct tm nowtm;

#ifdef WIN32
			localtime_s(&nowtm, &now);
#else
			localtime_r(&now, &nowtm);
#endif

			if (ks_log_prefix & KS_LOG_PREFIX_DATE) {
				strftime(tbuf, sizeof(tbuf), "%Y-%m-%d", &nowtm);
				used += snprintf(buf + used - 1, bufSize - used, "%s ", tbuf);
				if (used >= bufSize) goto done;
			}
			if (ks_log_prefix & KS_LOG_PREFIX_TIME) {
				strftime(tbuf, sizeof(tbuf), "%H:%M:%S", &nowtm);
				used += snprintf(buf + used - 1, bufSize - used, "%s ", tbuf);
				if (used >= bufSize) goto done;
			}
		}
		if (ks_log_prefix & KS_LOG_PREFIX_THREAD) {
#ifdef KS_PLAT_MAC
			uint64_t id = (uint64_t)ks_thread_self_id();
#else
			uint32_t id = (uint32_t)ks_thread_self_id();
#ifdef __GNU__
			id = (uint32_t)syscall(SYS_gettid);
#endif
#endif
			used += snprintf(buf + used - 1, bufSize - used, "#%"KS_PID_FMT" ", id);
			if (used >= bufSize) goto done;
		}
		if (ks_log_prefix & KS_LOG_PREFIX_FILE) {
			used += snprintf(buf + used - 1, bufSize - used, "%32.32s", fp);
			if (ks_log_prefix & KS_LOG_PREFIX_LINE) {
				used += snprintf(buf + used - 1, bufSize - used, ":%-5d", line);
				if (used >= bufSize) goto done;
			}
			used += snprintf(buf + used - 1, bufSize - used, " ");
			if (used >= bufSize) goto done;
		}
		if (ks_log_prefix & KS_LOG_PREFIX_FUNC) {
			used += snprintf(buf + used - 1, bufSize - used, "%-48.48s ", func);
			if (used >= bufSize) goto done;
		}

		used += snprintf(buf + used - 1, bufSize - used, "%s", data);

done:
		// Cap if snprintf exceeded its bounds (will return what may have been printed, not
		// the result of the cap)
		if (used >= (bufSize - 1)) {
			// reserve 1 character for newline if needed
			used = bufSize - 1;
			// reterminate
			buf[used - 1] = '\0';
		}

		if (used >= 2) {
			if (buf[used - 2] != '\n') {
				buf[used - 1] = '\n';
				buf[used] = '\0';
				++used;
			}
		}
	}
	if (data) free(data);
	return used - 1;
}

static void default_logger(const char *file, const char *func, int line, int level, const char *fmt, ...)
{
	va_list ap;
	char buf[32768];
	ks_size_t len;

	if (level < 0 || level > 7) {
		level = 7;
	}
	if (level > ks_log_level) return;
	
	va_start(ap, fmt);

	if (ks_log_jsonified) {
		char *data = NULL;
		ks_vasprintf(&data, fmt, ap);
		len = strlen(data);
		if (len > 0) {
			char tbuf[256];
			ks_json_t *response = ks_json_create_object();
			ks_json_t *json = response;

			if(ks_log_json_enclose_name) {
				json = ks_json_add_object_to_object(response, ks_log_json_enclose_name);
			}
		
			ks_json_add_string_to_object(json, "message", data);

			// TODO: Add prefix data as fields
			ks_json_add_string_to_object(json, "level", LEVEL_NAMES[level]);

			{
				time_t now = time(0);
				struct tm nowtm;

#ifdef WIN32
				localtime_s(&nowtm, &now);
#else
				localtime_r(&now, &nowtm);
#endif

				strftime(tbuf, sizeof(tbuf), "%Y-%m-%d %H:%M:%S", &nowtm);

				ks_json_add_string_to_object(json, "timestamp", tbuf);
			}

			{
#ifdef KS_PLAT_MAC
				uint64_t id = (uint64_t)ks_thread_self_id();
#else
				uint32_t id = (uint32_t)ks_thread_self_id();
#ifdef __GNU__
				id = (uint32_t)syscall(SYS_gettid);
#endif
#endif
				snprintf(tbuf, sizeof(tbuf), "#%"KS_PID_FMT, id);
				ks_json_add_string_to_object(json, "thread", tbuf);
			}

			ks_json_add_string_to_object(json, "file", file);
			ks_json_add_string_to_object(json, "func", func);
			ks_json_add_number_to_object(json, "line", line);

			char *tmp = ks_json_print_unformatted(response);

			ks_mutex_lock(g_log_mutex);
			fprintf(stdout, "%s\n", tmp);
			ks_mutex_unlock(g_log_mutex);
			
			free(tmp); // cleanup
			ks_json_delete(&json);
		}
		if (data) free(data);
	} else {
		len = ks_log_format_output(buf, sizeof(buf), file, func, line, level, fmt, ap);

		if (len > 0) {
			ks_mutex_lock(g_log_mutex);
			ks_size_t total = len;
		
			//fprintf(stdout, "[%s] %s:%d %s() %s", LEVEL_NAMES[level], fp, line, func, data);
#if KS_PLAT_WIN
			printf("%s", buf);			// Comment out and leave OutputDebugStringA on to catch timing related issues
			// (console is much slower then OutputDebugStringA on windows)
			OutputDebugStringA(buf);

#else
			{
				ks_bool_t done = KS_FALSE;
				ks_bool_t wakeup = KS_FALSE;
				ks_size_t written = 0;

				__set_blocking(fileno(stdout), KS_FALSE);
			
				while (!done) {
					ks_size_t thisWrite = fwrite(buf + written, 1, total - written, stdout);
					if (thisWrite > 0) {
						written += thisWrite;
						if (written == total) done = KS_TRUE;
					} else {
						if (wakeup) {
							// already tried to wakeup once before, skip logging... yuck
							break;
						}
						if (!wakeup_stdout()) {
							// couldn't wake up stdout? skip logging... yuck
							break;
						}
						// consumed stdout, try writing again
						wakeup = KS_TRUE;
					}
				}
				
				__set_blocking(fileno(stdout), KS_TRUE);
			}
#endif
			ks_mutex_unlock(g_log_mutex);
		}
	}

	va_end(ap);
}

static ks_logger_t ks_logger = default_logger;

KS_DECLARE(void) ks_global_set_logger(ks_logger_t logger)
{
	ks_logger = logger;
}

KS_DECLARE(void) ks_global_set_default_logger_prefix(ks_log_prefix_t prefix)
{
	ks_log_prefix = prefix;
}

KS_DECLARE(void) ks_global_set_log_level(int level)
{
  ks_log_level = level;
}

KS_DECLARE(void) ks_log_jsonify(void)
{
	ks_log_jsonified = KS_TRUE;
}

KS_DECLARE(void) ks_log_json_set_enclosing_name(char const*name)
{
	ks_log_json_enclose_name = name;
}

KS_DECLARE(void) ks_log_init(void)
{
	ks_mutex_create(&g_log_mutex, KS_MUTEX_FLAG_DEFAULT | KS_MUTEX_FLAG_RAW_ALLOC, NULL);
}

KS_DECLARE(void) ks_log_shutdown(void)
{
	ks_mutex_destroy(&g_log_mutex);
}

KS_DECLARE(void) ks_log(const char *file, const char *func, int line, int level, const char *fmt, ...)
{
	char *data;
	va_list ap;

	if (!ks_logger) return;

	va_start(ap, fmt);

	if (ks_vasprintf(&data, fmt, ap) != -1) {
		ks_logger(file, func, line, level, "%s", data);
		//fprintf(stdout, "[%s] %s:%d %s() %s", LEVEL_NAMES[level], fp, line, func, data);
		//fprintf(stdout, "%s", buf);
		free(data);
	}

	va_end(ap);
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
