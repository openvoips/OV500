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

KS_DECLARE(ks_status_t) ks_metrics_cpu(double *cpu, int samples, int interval)
{
	ks_status_t ret = KS_STATUS_SUCCESS;
#ifdef KS_PLAT_WIN
	ret = KS_STATUS_NOT_ALLOWED;
	*cpu = 0;
#else
	int64_t firstSum = 0;
	int64_t firstIdle = 0;
	int64_t totalSum = 0;
	int64_t totalIdle = 0;
	int totalSamples = 0;

	ks_assert(cpu);
	ks_assert(samples >= 2);
	ks_assert(interval >= 1);

	while(samples > 0) {
		char line[256];
		char* token;
		int tokenIndex = 0;
		FILE* fp = fopen("/proc/stat", "r");
		int64_t sum = 0;
		int64_t idle = 0;

		if (!fp) {
			ret = KS_STATUS_NOT_FOUND;
			*cpu = 0;
			goto done;
		}
		
		fgets(line, 256, fp);
		fclose(fp);

		// skip first token "cpu"
		token = strtok(line, " ");

		// add remaining tokens
		while(token != NULL) {
			token = strtok(NULL, " ");
			if(token != NULL) {
				if(tokenIndex == 3) idle = atoll(token);
				else sum += atoll(token);

				tokenIndex++;
			}
		}

		if (firstSum == 0) firstSum = sum;
		if (firstIdle == 0) firstIdle = idle;

		sum -= firstSum;
		idle -= firstIdle;

		totalSum += sum;
		totalIdle += idle;
		
		samples--;
		totalSamples++;
		if (samples == 0 && totalSum == 0) ++samples;
	}

	*cpu = ((double)totalSum / (totalIdle + totalSum)) * 100;
done:
#endif	
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
