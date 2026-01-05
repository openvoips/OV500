// Copyright (c) 2007, 2024, Oracle and/or its affiliates.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License, version 2.0, as
// published by the Free Software Foundation.
//
// This program is designed to work with certain software (including
// but not limited to OpenSSL) that is licensed under separate terms, as
// designated in a particular file or component or in included license
// documentation. The authors of MySQL hereby grant you an additional
// permission to link the program and your derivative works with the
// separately licensed software that they have either included with
// the program or referenced in the documentation.
//
// Without limiting anything contained in the foregoing, this file,
// which is part of Connector/ODBC, is also subject to the
// Universal FOSS Exception, version 1.0, a copy of which can be found at
// https://oss.oracle.com/licenses/universal-foss-exception.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU General Public License, version 2.0, for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

/**
  @file  stringutil.c
  @brief Function prototypes from stringutil.c.
*/

#ifndef _STRINGUTIL_H
#define _STRINGUTIL_H

#include "../MYODBC_MYSQL.h"

#ifdef __cplusplus
extern "C" {
#endif

#include <string.h>
#include <sql.h>
#include <sqlext.h>

#include "unicode_transcode.h"

#define myodbc_min(a, b) ((a) < (b) ? (a) : (b))
#define myodbc_max(a, b) ((a) > (b) ? (a) : (b))

#ifdef HAVE_LPCWSTR
# define MyODBC_LPCWSTR LPCWSTR
#else
# define MyODBC_LPCWSTR LPWSTR
#endif

#define MAX_BYTES_PER_UTF8_CP 4 /* max 4 bytes per utf8 codepoint */

extern CHARSET_INFO *utf8_charset_info;
extern CHARSET_INFO *utf16_charset_info;
extern const char* transport_charset;

struct CHARSET_COLLATION_INFO
{
  unsigned int id;
  const char *cs_name;
  const char *coll_name;
  unsigned int maxlen;
};

/**
 Determine whether a charset number represents a UTF-8 collation.
*/
#define is_utf8_charset(number) \
  (number == 33 || number == 83 || \
   (number >= 192 && number <= 215) || number == 253 || \
   number == 45 || number == 46 || \
   number == 76 || \
   (number >= 224 && number <= 247) || \
   (number >= 255 && number <= 309))


int utf16toutf32(UTF16 *i, UTF32 *u);
int utf32toutf16(UTF32 i, UTF16 *u);
int utf8toutf32(UTF8 *i, UTF32 *u);
int utf32toutf8(UTF32 i, UTF8 *c);


/* Conversions */
SQLWCHAR *sqlchar_as_sqlwchar(CHARSET_INFO *charset_info, SQLCHAR *str,
                              SQLINTEGER *len, uint *errors);
SQLCHAR *sqlwchar_as_sqlchar(CHARSET_INFO *charset_info, SQLWCHAR *str,
                             SQLINTEGER *len, uint *errors);
SQLCHAR *sqlwchar_as_utf8_ext(const SQLWCHAR *str, SQLINTEGER *len,
                              SQLCHAR *buff, uint buff_max, int *utf8mb4_used);
SQLCHAR *sqlwchar_as_utf8(const SQLWCHAR *str, SQLINTEGER *len);
SQLSMALLINT utf8_as_sqlwchar(SQLWCHAR *out, SQLINTEGER out_max, SQLCHAR *in,
                             SQLINTEGER in_len);

SQLCHAR *sqlchar_as_sqlchar(CHARSET_INFO *from_charset,
                            CHARSET_INFO *to_charset,
                            SQLCHAR *str, SQLINTEGER *len, uint *errors);
SQLINTEGER sqlwchar_as_sqlchar_buf(CHARSET_INFO *charset_info,
                                   SQLCHAR *out, SQLINTEGER out_bytes,
                                   SQLWCHAR *str, SQLINTEGER len, uint *errors);
uint32
copy_and_convert(char *to, uint32 to_length, CHARSET_INFO *to_cs,
                 const char *from, uint32 from_length, CHARSET_INFO *from_cs,
                 uint32 *used_bytes, uint32 *used_chars, uint *errors);


/* wcs* replacements */
int sqlwcharcasecmp(const SQLWCHAR *s1, const SQLWCHAR *s2);
const SQLWCHAR *sqlwcharchr(const SQLWCHAR *wstr, SQLWCHAR wchr);
size_t sqlwcharlen(const SQLWCHAR *wstr);
SQLWCHAR *sqlwchardup(const SQLWCHAR *wstr, SQLINTEGER charlen);
unsigned long sqlwchartoul(const SQLWCHAR *wstr);
void sqlwcharfromul(SQLWCHAR *wstr, unsigned long v);
size_t sqlwcharncat2(SQLWCHAR *dest, const SQLWCHAR *src, size_t *n);
SQLWCHAR *sqlwcharncpy(SQLWCHAR *dest, const SQLWCHAR *src, size_t n);
SQLWCHAR *wchar_t_as_sqlwchar(wchar_t *from, SQLWCHAR *to, size_t len);

char * myodbc_strlwr(char *target, size_t len);
SQLCHAR* sqlwchar_as_utf8_simple(SQLWCHAR *s);
char *myodbc_stpmov(char *dst, const char *src);
char *myodbc_ll2str(longlong val, char *dst, int radix);
char *myodbc_d2str(double val, char *buf, size_t buf_size,
                   bool max_precision = true);
typedef int(*qsort_cmp)(const void *, const void *);

void myodbc_qsort(void *base_ptr, size_t count, size_t size, qsort_cmp cmp);
char *myodbc_int10_to_str(long int val, char *dst, int radix);
bool myodbc_append_mem_std(std::string &str, const char *append, size_t length);

bool myodbc_append_os_quoted_std(std::string &str, const char *append, ...);
void myodbc_dynstr_free(DYNAMIC_STRING *str);
bool myodbc_dynstr_realloc(DYNAMIC_STRING *str, size_t additional_size);

unsigned int get_charset_maxlen(unsigned int num);
void delocalize_radix(char* buffer);
double myodbc_strtod(const char *str, int len);

#ifdef __cplusplus
}
#endif

typedef std::basic_string<SQLWCHAR, std::char_traits<SQLWCHAR>, std::allocator<SQLWCHAR>> SQLWSTRING;
SQLWSTRING escape_brackets(const SQLWSTRING &val, bool add_start_end);

#endif /* _STRINGUTIL_H */

