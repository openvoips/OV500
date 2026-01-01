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

#pragma once

KS_BEGIN_EXTERN_C

KS_DECLARE(size_t) ks_url_encode(const char *url, char *buf, size_t len);
KS_DECLARE(char *) ks_url_decode(char *s);
KS_DECLARE(const char *) ks_stristr(const char *instr, const char *str);
KS_DECLARE(int) ks_toupper(int c);
KS_DECLARE(int) ks_tolower(int c);
KS_DECLARE(char *) ks_copy_string(char *from_str, const char *to_str, ks_size_t from_str_len);
KS_DECLARE(int) ks_snprintf(char *buffer, size_t count, const char *fmt, ...);
KS_DECLARE(unsigned int) ks_separate_string_string(char *buf, const char *delim, char **array, unsigned int arraylen);
KS_DECLARE(unsigned int) ks_separate_string(char *buf, char delim, char **array, unsigned int arraylen);
KS_DECLARE(char *) ks_hex_string(const unsigned char *data, ks_size_t len, char *buffer);
KS_DECLARE(char*) ks_human_readable_size(ks_size_t size, int max_precision, ks_size_t len, char *buffer);
KS_DECLARE(char*) ks_human_readable_size_double(double size, int max_precision, ks_size_t len, char *buffer);
KS_DECLARE(void) ks_random_string(char *buf, uint16_t len, char *set);
KS_DECLARE(const char *) ks_thr_sprintf(const char *fmt, ...);
KS_DECLARE(int) ks_vasprintf(char **ret, const char *fmt, va_list ap);

#define ks_str_nil(s) (s ? s : "")
#define ks_zstr_buf(s) (*(s) == '\0')
#define ks_set_string(_x, _y) ks_copy_string(_x, _y, sizeof(_x))

static __inline__ int ks_safe_strcasecmp(const char *s1, const char *s2) {
	if (!(s1 && s2)) {
		return 1;
	}

	return strcasecmp(s1, s2);
}

/*!
  \brief Test for NULL or zero length string
  \param s the string to test
  \return true value if the string is NULL or zero length
*/
_Check_return_ static __inline int _ks_zstr(_In_opt_z_ const char *s)
{
	return !s || *s == '\0';
}

#ifdef _PREFAST_
#define ks_zstr(x) (_ks_zstr(x) ? 1 : __analysis_assume(x),0)
#else
#define ks_zstr(x) _ks_zstr(x)
#endif
#define ks_strlen_zero(x) ks_zstr(x)
#define ks_strlen_zero_buf(x) ks_zstr_buf(x)

KS_END_EXTERN_C

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

