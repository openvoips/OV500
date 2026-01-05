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

#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "tap.h"

struct human_test_entry_s {
	ks_size_t size;
	int max_precision;
	const char *value;
};

static void test_human_size()
{
	static const struct human_test_entry_s entries[] = {
		{ 1024ull, 1, "1.0kB" },
		{ 1024ull * 1024, 1, "1.0MB" },
		{ 1024ull * 1024 * 1024, 1, "1.0GB" },
		{ 1024ull * 1024 * 1024 * 1024, 1, "1.0TB" },
		{ 1024ull * 1024 * 1024 * 1024 * 1024, 1, "1.0PB" },
		{ 1024ull * 1024 * 1024 * 1024 * 1024 * 1024, 1, "1.0EB" },
//		{ 1024ull * 1024 * 1024 * 1024 * 1024 * 1024 * 1024, 1, "1.0ZB" },
//		{ 1024ull * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024, 1, "1.0YB" },

		{ 1024ull, 0, "1kB" },
		{ 1024ull * 1024, 0, "1MB" },
		{ 1024ull * 1024 * 1024, 0, "1GB" },
		{ 1024ull * 1024 * 1024 * 1024, 0, "1TB" },
		{ 1024ull * 1024 * 1024 * 1024 * 1024, 0, "1PB" },
//		{ 1024ull * 1024 * 1024 * 1024 * 1024 * 1024, 0, "1EB" },
//		{ 1024ull * 1024 * 1024 * 1024 * 1024 * 1024 * 1024, 0, "1ZB" },
//		{ 1024ull * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024, 0, "1YB" },

		{0}
	};

	for (int32_t index = 0; entries[index].size != 0; index++) {

		char workspace[1024];

		ks_human_readable_size(entries[index].size,
			   	entries[index].max_precision, sizeof(workspace), workspace);

		printf("Checking if: %s == %s\n", workspace, entries[index].value);
		ok(!strcmp(workspace, entries[index].value));
	}
}

int main(int argc, char **argv)
{
	ks_init();
	test_human_size();
}
