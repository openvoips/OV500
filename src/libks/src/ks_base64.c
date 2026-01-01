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
#include "libks/ks_base64.h"

static const char ks_b64_table[65] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";


KS_DECLARE(ks_status_t) ks_b64_encode(unsigned char *in, ks_size_t ilen, unsigned char *out, ks_size_t olen)
{
	int y = 0, bytes = 0;
	size_t x = 0;
	unsigned int b = 0, l = 0;

	for (x = 0; x < ilen; x++) {
		b = (b << 8) + in[x];
		l += 8;

		while (l >= 6) {
			out[bytes++] = ks_b64_table[(b >> (l -= 6)) % 64];
			if (bytes >= (int)olen - 1) {
				goto end;
			}
			if (++y != 72) {
				continue;
			}
			/* out[bytes++] = '\n'; */
			y = 0;
		}
	}

	if (l > 0) {
		out[bytes++] = ks_b64_table[((b % 16) << (6 - l)) % 64];
	}
	if (l != 0) {
		while (l < 6 && bytes < (int)olen - 1) {
			out[bytes++] = '=', l += 2;
		}
	}

  end:

	out[bytes] = '\0';

	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_size_t) ks_b64_decode(char *in, char *out, ks_size_t olen)
{

	char l64[256];
	int b = 0, c, l = 0, i;
	char *ip, *op = out;
	size_t ol = 0;

	for (i = 0; i < 256; i++) {
		l64[i] = -1;
	}

	for (i = 0; i < 64; i++) {
		l64[(int) ks_b64_table[i]] = (char) i;
	}

	for (ip = in; ip && *ip; ip++) {
		c = l64[(int) *ip];
		if (c == -1) {
			continue;
		}

		b = (b << 6) + c;
		l += 6;

		while (l >= 8) {
			op[ol++] = (char) ((b >> (l -= 8)) % 256);
			if (ol >= olen - 2) {
				goto end;
			}
		}
	}

  end:

	op[ol++] = '\0';

	return ol;
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
