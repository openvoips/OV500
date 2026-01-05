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

#ifdef WIN32
#include <VersionHelpers.h>

static CRITICAL_SECTION timer_section;
static ks_time_t win32_tick_time_since_start = -1;
static DWORD win32_last_get_time_tick = 0;

static uint8_t win32_use_qpc = 0;
static uint64_t win32_qpc_freq = 0;

static inline void win32_init_timers(void)
{
	OSVERSIONINFOEX version_info; /* Used to fetch current OS version from Windows */
	InitializeCriticalSection(&timer_section);
	EnterCriticalSection(&timer_section);

	ZeroMemory(&version_info, sizeof(OSVERSIONINFOEX));
	version_info.dwOSVersionInfoSize = sizeof(OSVERSIONINFOEX);

	/* Check if we should use timeGetTime() (pre-Vista) or QueryPerformanceCounter() (Vista and later) */

	//if (GetVersionEx((OSVERSIONINFO*) &version_info)) {
	if (IsWindowsVistaOrGreater()) {
		//if (version_info.dwPlatformId == VER_PLATFORM_WIN32_NT && version_info.dwMajorVersion >= 6) {
			if (QueryPerformanceFrequency((LARGE_INTEGER*)&win32_qpc_freq) && win32_qpc_freq > 0) {
				/* At least Vista, and QueryPerformanceFrequency() suceeded, enable qpc */
				win32_use_qpc = 1;
			} else {
				/* At least Vista, but QueryPerformanceFrequency() failed, disable qpc */
				win32_use_qpc = 0;
			}
		//} else {
			/* Older then Vista, disable qpc */
			//win32_use_qpc = 0;
		//}
	} else {
		/* Unknown version - we want at least Vista, disable qpc */
		win32_use_qpc = 0;
	}

	if (win32_use_qpc) {
		uint64_t count = 0;

		if (!QueryPerformanceCounter((LARGE_INTEGER*)&count) || count == 0) {
			/* Call to QueryPerformanceCounter() failed, disable qpc again */
			win32_use_qpc = 0;
		}
	}

	if (!win32_use_qpc) {
		/* This will enable timeGetTime() instead, qpc init failed */
		win32_last_get_time_tick = timeGetTime();
		win32_tick_time_since_start = win32_last_get_time_tick;
	}

	LeaveCriticalSection(&timer_section);
}

KS_DECLARE(ks_time_t) ks_time_now(void)
{
	ks_time_t now;

	if (win32_use_qpc) {
		/* Use QueryPerformanceCounter */
		uint64_t count = 0;
		QueryPerformanceCounter((LARGE_INTEGER*)&count);
		now = ((count * 1000000) / win32_qpc_freq);
	} else {
		/* Use good old timeGetTime() */
		DWORD tick_now;
		DWORD tick_diff;

		tick_now = timeGetTime();
		if (win32_tick_time_since_start != -1) {
			EnterCriticalSection(&timer_section);
			/* just add diff (to make it work more than 50 days). */
			tick_diff = tick_now - win32_last_get_time_tick;
			win32_tick_time_since_start += tick_diff;

			win32_last_get_time_tick = tick_now;
			now = (win32_tick_time_since_start * 1000);
				LeaveCriticalSection(&timer_section);
		} else {
			/* If someone is calling us before timer is initialized,
			 * return the current tick
			 */
			now = (tick_now * 1000);
		}
	}

	return now;
}

KS_DECLARE(ks_time_t) ks_time_now_sec(void)
{
	ks_time_t now;

	if (win32_use_qpc) {
		/* Use QueryPerformanceCounter */
		uint64_t count = 0;
		QueryPerformanceCounter((LARGE_INTEGER*)&count);
		now = (count / win32_qpc_freq);
	} else {
		/* Use good old timeGetTime() */
		DWORD tick_now;
		DWORD tick_diff;

		tick_now = timeGetTime();
		if (win32_tick_time_since_start != -1) {
			EnterCriticalSection(&timer_section);
			/* just add diff (to make it work more than 50 days). */
			tick_diff = tick_now - win32_last_get_time_tick;
			win32_tick_time_since_start += tick_diff;

			win32_last_get_time_tick = tick_now;
			now = (win32_tick_time_since_start / 1000);
				LeaveCriticalSection(&timer_section);
		} else {
			/* If someone is calling us before timer is initialized,
			 * return the current tick
			 */
			now = (tick_now / 1000);
		}
	}

	return now;
}

KS_DECLARE(void) ks_sleep(ks_time_t microsec)
{

	LARGE_INTEGER perfCnt, start, now;

	QueryPerformanceFrequency(&perfCnt);
	QueryPerformanceCounter(&start);

	do {
		QueryPerformanceCounter((LARGE_INTEGER*) &now);
		if (!SwitchToThread()) Sleep(1);
	} while ((now.QuadPart - start.QuadPart) / (float)(perfCnt.QuadPart) * 1000 * 1000 < (DWORD)microsec);

}

#else //!WINDOWS, UNIX ETC
KS_DECLARE(ks_time_t) ks_time_now(void)
{
	ks_time_t now;

#if (defined(HAVE_CLOCK_GETTIME) && defined(CLOCK_REALTIME))
	struct timespec ts;
	clock_gettime(CLOCK_REALTIME, &ts);
	now = (int64_t)ts.tv_sec * 1000000 + ((int64_t)ts.tv_nsec / 1000);
#else
	struct timeval tv;
	gettimeofday(&tv, NULL);
	now = tv.tv_sec * 1000000 + tv.tv_usec;
#endif

	return now;
}

KS_DECLARE(ks_time_t) ks_time_now_sec(void)
{
	ks_time_t now;

#if (defined(HAVE_CLOCK_GETTIME) && defined(CLOCK_REALTIME))
	struct timespec ts;
	clock_gettime(CLOCK_REALTIME, &ts);
	now = (int64_t)ts.tv_sec;
#else
	struct timeval tv;
	gettimeofday(&tv, NULL);
	now = tv.tv_sec;
#endif

	return now;
}

#if !defined(HAVE_CLOCK_NANOSLEEP) && !defined(__APPLE__)
static void generic_sleep(ks_time_t microsec)
{
#ifdef HAVE_USLEEP
	usleep(microsec);
#else
	struct timeval tv;
	tv.tv_usec = ks_time_usec(microsec);
	tv.tv_sec = ks_time_sec(microsec);
	select(0, NULL, NULL, NULL, &tv);
#endif
}
#endif

KS_DECLARE(void) ks_sleep(ks_time_t microsec)
{
#if defined(HAVE_CLOCK_NANOSLEEP) || defined(__APPLE__)
	struct timespec ts;
#endif

#if defined(HAVE_CLOCK_NANOSLEEP)
	ts.tv_sec = ks_time_sec(microsec);
	ts.tv_nsec = ks_time_nsec(microsec);
	clock_nanosleep(CLOCK_MONOTONIC, 0, &ts, NULL);
#elif defined(__APPLE__)
	ts.tv_sec = ks_time_sec(microsec);
	ts.tv_nsec = ks_time_usec(microsec) * 900;
	nanosleep(&ts, NULL);
#else
	generic_sleep(microsec);
#endif

#if defined(__APPLE__)
	sched_yield();
#endif

}

#endif

KS_DECLARE(void) ks_time_init(void)
{
#ifdef _WINDOWS_
	win32_init_timers();
#endif
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
