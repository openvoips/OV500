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
  @brief String utility functions, mostly focused on SQLWCHAR and charset
         manipulations.
*/


#include "stringutil.h"
#include <limits>

CHARSET_INFO *utf8_charset_info = NULL;
CHARSET_INFO *utf16_charset_info = NULL;

const char *transport_charset = "utf8mb4";

// Scientific floating point notation:
// the value is represented always with only one digit before the decimal point,
// followed by the decimal point and as many decimal digits as the precision.
// Finally, this notation always includes an exponential part consisting on
// the letter e followed by an optional sign and three exponential digits.
//
// https://cplusplus.com/reference/ios/scientific/
static inline bool is_valid_float_char(char c)
{
  return ('0' <= c && c <= '9') ||
    c == 'e' || c == 'E' ||
    c == '+' || c == '-';
}


/**
  Replace a locale dependent decimal point by C decimal dot

  NOTE: Recommended for use only for scientific exponential
        representation of floating point numbers
        such as (-1.2345678E+9).

  @param[in, out] buffer containing the string value of float/double
*/
void delocalize_radix(char* buffer)
{
  // Fast check:  if the buffer has a normal decimal point, assume no
  // translation is needed.
  if (strchr(buffer, '.') != nullptr) return;

  // Find the first unknown character.
  while (is_valid_float_char(*buffer)) ++buffer;

  if (*buffer == '\0') {
    // No radix character found.
    return;
  }

  // We are now pointing at the locale-specific radix character.  Replace it
  // with '.'.
  *buffer = '.';
  ++buffer;

  if (!is_valid_float_char(*buffer) && *buffer != '\0') {
    // It appears the radix was a multi-byte character.  We need to remove the
    // extra bytes.
    char* target = buffer;
    do { ++buffer; } while (!is_valid_float_char(*buffer) && *buffer != '\0');
    memmove(target, buffer, strlen(buffer) + 1);
  }
}


/**
  Duplicate a SQLCHAR in the specified character set as a SQLWCHAR.

  @param[in]      charset_info  Character set to convert into
  @param[in]      str           String to convert
  @param[in,out]  len           Pointer to length of source (in bytes) or
                                destination string (in chars)
  @param[out]     errors        Pointer to count of errors in conversion

  @return  Pointer to a newly allocated SQLWCHAR, or @c NULL
*/
SQLWCHAR *sqlchar_as_sqlwchar(CHARSET_INFO *charset_info, SQLCHAR *str,
                              SQLINTEGER *len, uint *errors)
{
  SQLCHAR *pos, *str_end;
  SQLWCHAR *out;
  SQLINTEGER i, out_bytes;
  my_bool free_str= 0;

  if (str && *len == SQL_NTS)
  {
    *len = (SQLINTEGER)strlen((char *)str);
  }
  if (!str || *len == 0)
  {
    *len= 0;
    return NULL;
  }

  if (!is_utf8_charset(charset_info->number))
  {
    uint32 used_bytes, used_chars;
    size_t u8_max= (*len / charset_info->mbminlen *
                    utf8_charset_info->mbmaxlen + 1);
    SQLCHAR *u8= (SQLCHAR *)myodbc_malloc(u8_max, MYF(0));

    if (!u8)
    {
      *len= -1;
      return NULL;
    }

    *len= copy_and_convert((char *)u8, (uint32)u8_max, utf8_charset_info,
                           (char *)str, *len, charset_info,
                           &used_bytes, &used_chars, errors);
    str= u8;
    free_str= 1;
  }

  str_end= str + *len;

  out_bytes= (*len + 1) * sizeof(SQLWCHAR);

  out= (SQLWCHAR *)myodbc_malloc(out_bytes, MYF(0));
  if (!out)
  {
    *len= -1;
    return NULL;
  }

  for (pos= str, i= 0; pos < str_end && *pos != 0; )
  {
    if (sizeof(SQLWCHAR) == 4)
    {
      int consumed= utf8toutf32(pos, (UTF32 *)(out + i++));
      pos+= consumed;
      if (consumed == 0)
      {
        *errors+= 1;
        break;
      }
    }
    else
    {
      UTF32 u32;
      int consumed= utf8toutf32(pos, &u32);
      pos+= consumed;
      if (consumed == 0)
      {
        *errors+= 1;
        break;
      }
      i+= utf32toutf16(u32, (UTF16 *)(out + i));
    }
  }

  *len= i;
  out[i]= 0;

  if (free_str)
  {
    x_free(str);
  }

  return out;
}


/**
  Duplicate a SQLWCHAR as a SQLCHAR in the specified character set.

  @param[in]      charset_info  Character set to convert into
  @param[in]      str           String to convert
  @param[in,out]  len           Pointer to length of source (in chars) or
                                destination string (in bytes)
  @param[out]     errors        Pointer to count of errors in conversion

  @return  Pointer to a newly allocated SQLCHAR, or @c NULL
*/
SQLCHAR *sqlwchar_as_sqlchar(CHARSET_INFO *charset_info, SQLWCHAR *str,
                             SQLINTEGER *len, uint *errors)
{
  SQLWCHAR *str_end;
  SQLCHAR *out;
  SQLINTEGER i, u8_len, out_bytes;
  UTF8 u8[MAX_BYTES_PER_UTF8_CP + 1];
  uint32 used_bytes, used_chars;

  *errors= 0;

  if (is_utf8_charset(charset_info->number))
    return sqlwchar_as_utf8(str, len);

  if (*len == SQL_NTS)
    *len = (SQLINTEGER)sqlwcharlen(str);
  if (!str || *len == 0)
  {
    *len= 0;
    return NULL;
  }

  out_bytes= *len * charset_info->mbmaxlen * sizeof(SQLCHAR) + 1;
  out= (SQLCHAR *)myodbc_malloc(out_bytes, MYF(0));
  if (!out)
  {
    *len= -1;
    return NULL;
  }

  str_end= str + *len;

  for (i= 0; str < str_end; )
  {
    if (sizeof(SQLWCHAR) == 4)
    {
      u8_len= utf32toutf8((UTF32)*str++, u8);
    }
    else
    {
      UTF32 u32;
      int consumed= utf16toutf32((UTF16 *)str, &u32);
      str+= consumed;
      if (!consumed)
      {
        *errors+= 1;
        break;
      }
      u8_len= utf32toutf8(u32, u8);
    }

    i+= copy_and_convert((char *)out + i, out_bytes - i, charset_info,
                         (char *)u8, u8_len, utf8_charset_info, &used_bytes,
                         &used_chars, errors);
  }

  *len= i;
  out[i]= '\0';
  return out;
}


/**
  A bit extended version of sqlwchar_as_utf8 to be used by it and in other
  places where sqlwchar_as_utf8 could not be used

  @param[in]      str           String to convert
  @param[in,out]  len           Pointer to length of source (in chars) or
                                destination string (in bytes).
  @param[in]      buff          Buffer to put the result string if it fits
  @param[in]      buff_max      max size(in bytes) of the buff.
  @param[out]     utf8mb4_used  has 4 bytes utf8 characters been used

  @return  Pointer to a newly allocated SQLCHAR, or @c NULL
*/
SQLCHAR *sqlwchar_as_utf8_ext(const SQLWCHAR *str, SQLINTEGER *len,
                              SQLCHAR *buff, uint buff_max, int *utf8mb4_used)
{
  const SQLWCHAR *str_end;
  UTF8 *u8;
  int utf8len, dummy;
  SQLINTEGER i;
  SQLINTEGER len_val = 0;

  if (!len) {
    len = &len_val;
    *len = (SQLINTEGER)sqlwcharlen(str);
  }

  if (!str || *len <= 0)
  {
    *len= 0;
    return buff;
  }

  if (utf8mb4_used == NULL)
  {
    utf8mb4_used= &dummy;
  }

  if (buff == NULL || buff_max < (uint)(*len * MAX_BYTES_PER_UTF8_CP))
  {
    u8= (UTF8 *)myodbc_malloc(sizeof(UTF8) * MAX_BYTES_PER_UTF8_CP * *len + 1,
                        MYF(0));
  }
  else
  {
    u8= buff;
  }

  if (!u8)
  {
    *len= -1;
    return NULL;
  }

  str_end= str + *len;

  if (sizeof(SQLWCHAR) == 4)
  {
    for (i= 0; str < str_end; )
    {
      i+= (utf8len= utf32toutf8((UTF32)*str++, u8 + i));

      /*
        utf8mb4 is a superset of utf8, only supplemental characters
        which require four bytes differs in storage characteristics (length)
        between utf8 and utf8mb4.
      */
      if (utf8len == 4)
      {
        *utf8mb4_used= 1;
      }
    }
  }
  else
  {
    for (i= 0; str < str_end; )
    {
      UTF32 u32;
      int consumed= utf16toutf32((UTF16 *)str, &u32);
      if (!consumed)
      {
        break;
      }

      str+= consumed;

      i+= (utf8len= utf32toutf8(u32, u8 + i));
      /*
        utf8mb4 is a superset of utf8, only supplemental characters
        which require four bytes differs in storage characteristics (length)
        between utf8 and utf8mb4.
      */
      if (utf8len == 4)
      {
        *utf8mb4_used= 1;
      }
    }
  }

  *len= i;
  return u8;
}


/**
  Duplicate a SQLWCHAR as a SQLCHAR encoded as UTF-8.

  @param[in]      str           String to convert
  @param[in,out]  len           Pointer to length of source (in chars) or
                                destination string (in bytes)

  @return  Pointer to a newly allocated SQLCHAR, or @c NULL
*/
SQLCHAR *sqlwchar_as_utf8(const SQLWCHAR *str, SQLINTEGER *len)
{
  SQLCHAR *res;

  if (*len == SQL_NTS)
  {
    *len = (SQLINTEGER)sqlwcharlen(str);
  }

  if (!str || *len <= 0)
  {
    *len= 0;
    return NULL;
  }

  res= sqlwchar_as_utf8_ext(str, len, NULL, 0, NULL);

  /* If we could not allocate memory */
  if (res != NULL)
  {
    res[*len]= '\0';
  }

  return res;
}


SQLCHAR* sqlwchar_as_utf8_simple(SQLWCHAR *s)
{
  SQLINTEGER len= SQL_NTS;
  return sqlwchar_as_utf8(s, &len);
}


/**
  Convert a SQLCHAR encoded as UTF-8 into a SQLWCHAR.

  @param[out]     out           Pointer to SQLWCHAR buffer
  @param[in]      out_max       Length of @c out buffer
  @param[in]      in            Pointer to SQLCHAR string (utf-8 encoded)
  @param[in]      in_len        Length of @c in (in bytes)

  @return  Number of characters stored in the @c out buffer
*/
SQLSMALLINT utf8_as_sqlwchar(SQLWCHAR *out, SQLINTEGER out_max, SQLCHAR *in,
                             SQLINTEGER in_len)
{
  SQLINTEGER i;
  SQLWCHAR *pos, *out_end;

  for (i= 0, pos= out, out_end= out + out_max; i < in_len && pos < out_end; )
  {
    if (sizeof(SQLWCHAR) == 4)
    {
      int consumed= utf8toutf32(in + i, (UTF32 *)pos++);
      i+= consumed;
      if (!consumed)
        break;
    }
    else
    {
      UTF32 u32;
      int consumed= utf8toutf32(in + i, &u32);
      i+= consumed;
      if (!consumed)
        break;
      pos+= utf32toutf16(u32, (UTF16 *)pos);
    }
  }

  if (pos)
    *pos= 0;
  return (SQLSMALLINT)(pos - out);
}


/**
  Duplicate a SQLCHAR as a SQLCHAR in the specified character set.

  @param[in]      from_charset  Character set to convert from
  @param[in]      to_charset    Character set to convert into
  @param[in]      str           String to convert
  @param[in,out]  len           Pointer to length of source (in chars) or
                                destination string (in bytes)
  @param[out]     errors        Pointer to count of errors in conversion

  @return  Pointer to a newly allocated SQLCHAR, or @c NULL
*/
SQLCHAR *sqlchar_as_sqlchar(CHARSET_INFO *from_charset,
                            CHARSET_INFO *to_charset,
                            SQLCHAR *str, SQLINTEGER *len, uint *errors)
{
  uint32 used_bytes, used_chars, bytes;
  SQLCHAR *conv;

  if (*len == SQL_NTS)
    *len = (SQLINTEGER)strlen((char *)str);

  bytes= (*len / from_charset->mbminlen * to_charset->mbmaxlen);
  conv= (SQLCHAR *)myodbc_malloc(bytes + 1, MYF(0));
  if (!conv)
  {
    *len= -1;
    return NULL;
  }

  *len= copy_and_convert((char *)conv, bytes, to_charset,
                         (char *)str, *len,
                         from_charset, &used_bytes,
                         &used_chars, errors);

  conv[*len]= '\0';

  return conv;
}


/**
  Convert a SQLWCHAR to a SQLCHAR in the specified character set. This
  variation uses a pre-allocated buffer.

  @param[in]      charset_info  Character set to convert into
  @param[out]     out           Pointer to SQLWCHAR buffer
  @param[in]      out_bytes     Length of @c out buffer
  @param[in]      str           String to convert
  @param[in]      len           Length of @c in (in SQLWCHAR's)
  @param[out]     errors        Pointer to count of errors in conversion

  @return  Number of characters stored in the @c out buffer
*/
SQLINTEGER sqlwchar_as_sqlchar_buf(CHARSET_INFO *charset_info,
                                   SQLCHAR *out, SQLINTEGER out_bytes,
                                   SQLWCHAR *str, SQLINTEGER len, uint *errors)
{
  SQLWCHAR *str_end;
  SQLINTEGER i, u8_len;
  UTF8 u8[MAX_BYTES_PER_UTF8_CP + 1];
  uint32 used_bytes, used_chars;

  *errors= 0;

  if (len == SQL_NTS)
    len = (SQLINTEGER)sqlwcharlen(str);
  if (!str || len == 0)
    return 0;

  str_end = str + myodbc_min(len, out_bytes);

  for (i= 0; str < str_end; )
  {
    if (sizeof(SQLWCHAR) == 4)
    {
      u8_len= utf32toutf8((UTF32)*str++, u8);
    }
    else
    {
      UTF32 u32;
      int consumed= utf16toutf32((UTF16 *)str, &u32);
      str+= consumed;
      if (!consumed)
      {
        *errors+= 1;
        break;
      }
      u8_len= utf32toutf8(u32, u8);
    }

    i+= copy_and_convert((char *)out + i, out_bytes - i, charset_info,
                         (char *)u8, u8_len, utf8_charset_info, &used_bytes,
                         &used_chars, errors);
  }

  // NULL-terminate only if we have space in out buffer
  if (i < out_bytes)
    out[i]= '\0';

  return i;
}


/**
  Copy a string from one character set to another. Taken from sql_string.cc
  in the MySQL Server source code, since we don't export this functionality
  in libmysql yet.

  @c to must be at least as big as @c from_length * @c to_cs->mbmaxlen

  @param[in,out] to           Store result here
  @param[in]     to_cs        Character set of result string
  @param[in]     from         Copy from here
  @param[in]     from_length  Length of string in @c from (in bytes)
  @param[in]     from_cs      Character set of string in @c from
  @param[out]    used_bytes   Buffer for returning number of bytes consumed
                              from @c from
  @param[out]    used_chars   Buffer for returning number of chars consumed
                              from @c from
  @param[in,out] errors       Pointer to value where number of errors
                              encountered is added.

  @retval Length of bytes copied to @c to
*/
uint32
copy_and_convert(char *to, uint32 to_length, CHARSET_INFO *to_cs,
                 const char *from, uint32 from_length, CHARSET_INFO *from_cs,
                 uint32 *used_bytes, uint32 *used_chars, uint *errors)
{
  int         from_cnvres, to_cnvres;
  my_wc_t     wc;
  const uchar *from_end= (const uchar*) from+from_length;
  char *to_start= to;
  uchar *to_end= (uchar*) to+to_length;
  auto mb_wc= from_cs->cset->mb_wc;
  auto wc_mb= to_cs->cset->wc_mb;
  uint error_count= 0;

  *used_bytes= *used_chars= 0;

  while (1)
  {
    if ((from_cnvres= (*mb_wc)(from_cs, &wc, (uchar*) from, from_end)) > 0)
      from+= from_cnvres;
    else if (from_cnvres == MY_CS_ILSEQ)
    {
      ++error_count;
      ++from;
      wc= '?';
    }
    else if (from_cnvres > MY_CS_TOOSMALL)
    {
      /*
        A correct multibyte sequence detected
        But it doesn't have Unicode mapping.
      */
      ++error_count;
      from+= (-from_cnvres);
      wc= '?';
    }
    else
      break; /* Not enough characters */

outp:
    if ((to_cnvres= (*wc_mb)(to_cs, wc, (uchar*) to, to_end)) > 0)
      to+= to_cnvres;
    else if (to_cnvres == MY_CS_ILUNI && wc != '?')
    {
      ++error_count;
      wc= '?';
      goto outp;
    }
    else
      break;

    *used_bytes+= from_cnvres;
    *used_chars+= 1;
  }
  if (errors)
    *errors+= error_count;

  return (uint32) (to - to_start);
}




/*
 * Compare two SQLWCHAR strings ignoring case. This is only
 * case-insensitive over the ASCII range of characters.
 *
 * @return 0 if the strings are the same, 1 if they are not.
 */
int sqlwcharcasecmp(const SQLWCHAR *s1, const SQLWCHAR *s2)
{
  SQLWCHAR c1, c2;
  while (*s1 && *s2)
  {
    c1= *s1;
    c2= *s2;
    /* capitalize both strings */
    if (c1 >= 'a')
      c1 -= ('a' - 'A');
    if (c2 >= 'a')
      c2 -= ('a' - 'A');
    if (c1 != c2)
      return 1;
    ++s1;
    ++s2;
  }

  /* one will be null, so both must be */
  return *s1 != *s2;
}


/*
 * Locate a SQLWCHAR in a SQLWCHAR string.
 *
 * @return Position of char if found, otherwise NULL.
 */
const SQLWCHAR *sqlwcharchr(const SQLWCHAR *wstr, SQLWCHAR wchr)
{
  while (*wstr)
  {
    if (*wstr == wchr)
    {
      return wstr;
    }

    ++wstr;
  }

  return NULL;
}


/*
 * Calculate the length of a SQLWCHAR string.
 *
 * @return The number of SQLWCHAR units in the string.
 */
size_t sqlwcharlen(const SQLWCHAR *wstr)
{
  size_t len= 0;
  while (wstr && *wstr++)
    ++len;
  return len;
}


/*
 * Duplicate a SQLWCHAR string. Memory is allocated with myodbc_malloc()
 * and should be freed with my_free() or the x_free() macro.
 *
 * @return A pointer to a new string.
 */
SQLWCHAR *sqlwchardup(const SQLWCHAR *wstr, SQLINTEGER charlen)
{
  size_t chars = (charlen == SQL_NTS ? sqlwcharlen(wstr) : charlen);
  SQLWCHAR *res= (SQLWCHAR *)myodbc_malloc((chars + 1) * sizeof(SQLWCHAR), MYF(0));
  if (!res)
    return NULL;
  memcpy(res, wstr, chars * sizeof(SQLWCHAR));
  res[chars]= 0;
  return res;
}


/*
 * Convert a SQLWCHAR string to a long integer.
 *
 * @return The integer result of the conversion or 0 if the
 *         string could not be parsed.
 */
unsigned long sqlwchartoul(const SQLWCHAR *wstr){
  unsigned long res= 0;
  SQLWCHAR c;

  if (!wstr)
    return 0;

  while ((c = *wstr))
  {
    if (c < '0' || c > '9')
      break;
    res*= 10;
    res+= c - '0';
    ++wstr;
  }

  return res;
}


/*
 * Convert a long integer to a SQLWCHAR string.
 */
void sqlwcharfromul(SQLWCHAR *wstr, unsigned long v)
{
  int chars;
  unsigned long v1;
  for(chars= 0, v1= v; v1 > 0; ++chars, v1 /= 10);
  wstr[chars]= (SQLWCHAR)0;
  for(v1= v; v1 > 0; v1 /= 10)
    wstr[--chars]= (SQLWCHAR)('0' + (v1 % 10));
}


/*
 * Concatenate two strings. This differs from the convential
 * strncat() in that the parameter n is reduced by the number
 * of characters used.
 *
 * Returns the number of characters copied.
 */
size_t sqlwcharncat2(SQLWCHAR *dest, const SQLWCHAR *src, size_t *n)
{
  SQLWCHAR *orig_dest;
  if (!n || !*n)
    return 0;
  orig_dest= (dest += sqlwcharlen(dest));
  while (*src && *n && (*n)--)
    *dest++= *src++;
  if (*n)
    *dest= 0;
  else
    *(dest - 1)= 0;
  return dest - orig_dest;
}


/*
 * Copy up to 'n' characters (including NULL) from src to dest.
 */
SQLWCHAR *sqlwcharncpy(SQLWCHAR *dest, const SQLWCHAR *src, size_t n)
{
  if (!dest || !src)
    return NULL;
  while (*src && n--)
    *dest++= *src++;
  if (n)
    *dest= 0;
  else
    *(dest - 1)= 0;
  return dest;
}


/* Converts all characters in string to lowercase */
char * myodbc_strlwr(char *target, size_t len)
{
  unsigned char *c= (unsigned char *)target;
  if ((size_t)-1 == len)
    len= (int)strlen(target);

  while (len-- > 0)
  {
    *c= tolower(*c);
    ++c;
  }

  return target;
}

struct CHARSET_COLLATION_INFO charset_collation_info[] = {
  {0, "NONE", "NONE", 0},
  {1, "big5", "big5_chinese_ci", 2},
  {2, "latin2", "latin2_czech_cs", 1},
  {3, "dec8", "dec8_swedish_ci", 1},
  {4, "cp850", "cp850_general_ci", 1},
  {5, "latin1", "latin1_german1_ci", 1},
  {6, "hp8", "hp8_english_ci", 1},
  {7, "koi8r", "koi8r_general_ci", 1},
  {8, "latin1", "latin1_swedish_ci", 1},
  {9, "latin2", "latin2_general_ci", 1},
  {10, "swe7", "swe7_swedish_ci", 1},
  {11, "ascii", "ascii_general_ci", 1},
  {12, "ujis", "ujis_japanese_ci", 3},
  {13, "sjis", "sjis_japanese_ci", 2},
  {14, "cp1251", "cp1251_bulgarian_ci", 1},
  {15, "latin1", "latin1_danish_ci", 1},
  {16, "hebrew", "hebrew_general_ci", 1},
  {17, "NONE", "NONE", 0},
  {18, "tis620", "tis620_thai_ci", 1},
  {19, "euckr", "euckr_korean_ci", 2},
  {20, "latin7", "latin7_estonian_cs", 1},
  {21, "latin2", "latin2_hungarian_ci", 1},
  {22, "koi8u", "koi8u_general_ci", 1},
  {23, "cp1251", "cp1251_ukrainian_ci", 1},
  {24, "gb2312", "gb2312_chinese_ci", 2},
  {25, "greek", "greek_general_ci", 1},
  {26, "cp1250", "cp1250_general_ci", 1},
  {27, "latin2", "latin2_croatian_ci", 1},
  {28, "gbk", "gbk_chinese_ci", 2},
  {29, "cp1257", "cp1257_lithuanian_ci", 1},
  {30, "latin5", "latin5_turkish_ci", 1},
  {31, "latin1", "latin1_german2_ci", 1},
  {32, "armscii8", "armscii8_general_ci", 1},
  {33, "utf8", "utf8_general_ci", 3},
  {34, "cp1250", "cp1250_czech_cs", 1},
  {35, "ucs2", "ucs2_general_ci", 2},
  {36, "cp866", "cp866_general_ci", 1},
  {37, "keybcs2", "keybcs2_general_ci", 1},
  {38, "macce", "macce_general_ci", 1},
  {39, "macroman", "macroman_general_ci", 1},
  {40, "cp852", "cp852_general_ci", 1},
  {41, "latin7", "latin7_general_ci", 1},
  {42, "latin7", "latin7_general_cs", 1},
  {43, "macce", "macce_bin", 1},
  {44, "cp1250", "cp1250_croatian_ci", 1},
  {45, "utf8mb4", "utf8mb4_general_ci", 4},
  {46, "utf8mb4", "utf8mb4_bin", 4},
  {47, "latin1", "latin1_bin", 1},
  {48, "latin1", "latin1_general_ci", 1},
  {49, "latin1", "latin1_general_cs", 1},
  {50, "cp1251", "cp1251_bin", 1},
  {51, "cp1251", "cp1251_general_ci", 1},
  {52, "cp1251", "cp1251_general_cs", 1},
  {53, "macroman", "macroman_bin", 1},
  {54, "utf16", "utf16_general_ci", 4},
  {55, "utf16", "utf16_bin", 4},
  {56, "utf16le", "utf16le_general_ci", 4},
  {57, "cp1256", "cp1256_general_ci", 1},
  {58, "cp1257", "cp1257_bin", 1},
  {59, "cp1257", "cp1257_general_ci", 1},
  {60, "utf32", "utf32_general_ci", 4},
  {61, "utf32", "utf32_bin", 4},
  {62, "utf16le", "utf16le_bin", 4},
  {63, "binary", "binary", 1},
  {64, "armscii8", "armscii8_bin", 1},
  {65, "ascii", "ascii_bin", 1},
  {66, "cp1250", "cp1250_bin", 1},
  {67, "cp1256", "cp1256_bin", 1},
  {68, "cp866", "cp866_bin", 1},
  {69, "dec8", "dec8_bin", 1},
  {70, "greek", "greek_bin", 1},
  {71, "hebrew", "hebrew_bin", 1},
  {72, "hp8", "hp8_bin", 1},
  {73, "keybcs2", "keybcs2_bin", 1},
  {74, "koi8r", "koi8r_bin", 1},
  {75, "koi8u", "koi8u_bin", 1},
  {76, "utf8", "utf8_tolower_ci", 3},
  {77, "latin2", "latin2_bin", 1},
  {78, "latin5", "latin5_bin", 1},
  {79, "latin7", "latin7_bin", 1},
  {80, "cp850", "cp850_bin", 1},
  {81, "cp852", "cp852_bin", 1},
  {82, "swe7", "swe7_bin", 1},
  {83, "utf8", "utf8_bin", 3},
  {84, "big5", "big5_bin", 2},
  {85, "euckr", "euckr_bin", 2},
  {86, "gb2312", "gb2312_bin", 2},
  {87, "gbk", "gbk_bin", 2},
  {88, "sjis", "sjis_bin", 2},
  {89, "tis620", "tis620_bin", 1},
  {90, "ucs2", "ucs2_bin", 2},
  {91, "ujis", "ujis_bin", 3},
  {92, "geostd8", "geostd8_general_ci", 1},
  {93, "geostd8", "geostd8_bin", 1},
  {94, "latin1", "latin1_spanish_ci", 1},
  {95, "cp932", "cp932_japanese_ci", 2},
  {96, "cp932", "cp932_bin", 2},
  {97, "eucjpms", "eucjpms_japanese_ci", 3},
  {98, "eucjpms", "eucjpms_bin", 3},
  {99, "cp1250", "cp1250_polish_ci", 1},
  {100, "NONE", "NONE", 0},
  {101, "utf16", "utf16_unicode_ci", 4},
  {102, "utf16", "utf16_icelandic_ci", 4},
  {103, "utf16", "utf16_latvian_ci", 4},
  {104, "utf16", "utf16_romanian_ci", 4},
  {105, "utf16", "utf16_slovenian_ci", 4},
  {106, "utf16", "utf16_polish_ci", 4},
  {107, "utf16", "utf16_estonian_ci", 4},
  {108, "utf16", "utf16_spanish_ci", 4},
  {109, "utf16", "utf16_swedish_ci", 4},
  {110, "utf16", "utf16_turkish_ci", 4},
  {111, "utf16", "utf16_czech_ci", 4},
  {112, "utf16", "utf16_danish_ci", 4},
  {113, "utf16", "utf16_lithuanian_ci", 4},
  {114, "utf16", "utf16_slovak_ci", 4},
  {115, "utf16", "utf16_spanish2_ci", 4},
  {116, "utf16", "utf16_roman_ci", 4},
  {117, "utf16", "utf16_persian_ci", 4},
  {118, "utf16", "utf16_esperanto_ci", 4},
  {119, "utf16", "utf16_hungarian_ci", 4},
  {120, "utf16", "utf16_sinhala_ci", 4},
  {121, "utf16", "utf16_german2_ci", 4},
  {122, "utf16", "utf16_croatian_ci", 4},
  {123, "utf16", "utf16_unicode_520_ci", 4},
  {124, "utf16", "utf16_vietnamese_ci", 4},
  {125, "NONE", "NONE", 0},
  {126, "NONE", "NONE", 0},
  {127, "NONE", "NONE", 0},
  {128, "ucs2", "ucs2_unicode_ci", 2},
  {129, "ucs2", "ucs2_icelandic_ci", 2},
  {130, "ucs2", "ucs2_latvian_ci", 2},
  {131, "ucs2", "ucs2_romanian_ci", 2},
  {132, "ucs2", "ucs2_slovenian_ci", 2},
  {133, "ucs2", "ucs2_polish_ci", 2},
  {134, "ucs2", "ucs2_estonian_ci", 2},
  {135, "ucs2", "ucs2_spanish_ci", 2},
  {136, "ucs2", "ucs2_swedish_ci", 2},
  {137, "ucs2", "ucs2_turkish_ci", 2},
  {138, "ucs2", "ucs2_czech_ci", 2},
  {139, "ucs2", "ucs2_danish_ci", 2},
  {140, "ucs2", "ucs2_lithuanian_ci", 2},
  {141, "ucs2", "ucs2_slovak_ci", 2},
  {142, "ucs2", "ucs2_spanish2_ci", 2},
  {143, "ucs2", "ucs2_roman_ci", 2},
  {144, "ucs2", "ucs2_persian_ci", 2},
  {145, "ucs2", "ucs2_esperanto_ci", 2},
  {146, "ucs2", "ucs2_hungarian_ci", 2},
  {147, "ucs2", "ucs2_sinhala_ci", 2},
  {148, "ucs2", "ucs2_german2_ci", 2},
  {149, "ucs2", "ucs2_croatian_ci", 2},
  {150, "ucs2", "ucs2_unicode_520_ci", 2},
  {151, "ucs2", "ucs2_vietnamese_ci", 2},
  {152, "NONE", "NONE", 0},
  {153, "NONE", "NONE", 0},
  {154, "NONE", "NONE", 0},
  {155, "NONE", "NONE", 0},
  {156, "NONE", "NONE", 0},
  {157, "NONE", "NONE", 0},
  {158, "NONE", "NONE", 0},
  {159, "ucs2", "ucs2_general_mysql500_ci", 2},
  {160, "utf32", "utf32_unicode_ci", 4},
  {161, "utf32", "utf32_icelandic_ci", 4},
  {162, "utf32", "utf32_latvian_ci", 4},
  {163, "utf32", "utf32_romanian_ci", 4},
  {164, "utf32", "utf32_slovenian_ci", 4},
  {165, "utf32", "utf32_polish_ci", 4},
  {166, "utf32", "utf32_estonian_ci", 4},
  {167, "utf32", "utf32_spanish_ci", 4},
  {168, "utf32", "utf32_swedish_ci", 4},
  {169, "utf32", "utf32_turkish_ci", 4},
  {170, "utf32", "utf32_czech_ci", 4},
  {171, "utf32", "utf32_danish_ci", 4},
  {172, "utf32", "utf32_lithuanian_ci", 4},
  {173, "utf32", "utf32_slovak_ci", 4},
  {174, "utf32", "utf32_spanish2_ci", 4},
  {175, "utf32", "utf32_roman_ci", 4},
  {176, "utf32", "utf32_persian_ci", 4},
  {177, "utf32", "utf32_esperanto_ci", 4},
  {178, "utf32", "utf32_hungarian_ci", 4},
  {179, "utf32", "utf32_sinhala_ci", 4},
  {180, "utf32", "utf32_german2_ci", 4},
  {181, "utf32", "utf32_croatian_ci", 4},
  {182, "utf32", "utf32_unicode_520_ci", 4},
  {183, "utf32", "utf32_vietnamese_ci", 4},
  {184, "NONE", "NONE", 0},
  {185, "NONE", "NONE", 0},
  {186, "NONE", "NONE", 0},
  {187, "NONE", "NONE", 0},
  {188, "NONE", "NONE", 0},
  {189, "NONE", "NONE", 0},
  {190, "NONE", "NONE", 0},
  {191, "NONE", "NONE", 0},
  {192, "utf8", "utf8_unicode_ci", 3},
  {193, "utf8", "utf8_icelandic_ci", 3},
  {194, "utf8", "utf8_latvian_ci", 3},
  {195, "utf8", "utf8_romanian_ci", 3},
  {196, "utf8", "utf8_slovenian_ci", 3},
  {197, "utf8", "utf8_polish_ci", 3},
  {198, "utf8", "utf8_estonian_ci", 3},
  {199, "utf8", "utf8_spanish_ci", 3},
  {200, "utf8", "utf8_swedish_ci", 3},
  {201, "utf8", "utf8_turkish_ci", 3},
  {202, "utf8", "utf8_czech_ci", 3},
  {203, "utf8", "utf8_danish_ci", 3},
  {204, "utf8", "utf8_lithuanian_ci", 3},
  {205, "utf8", "utf8_slovak_ci", 3},
  {206, "utf8", "utf8_spanish2_ci", 3},
  {207, "utf8", "utf8_roman_ci", 3},
  {208, "utf8", "utf8_persian_ci", 3},
  {209, "utf8", "utf8_esperanto_ci", 3},
  {210, "utf8", "utf8_hungarian_ci", 3},
  {211, "utf8", "utf8_sinhala_ci", 3},
  {212, "utf8", "utf8_german2_ci", 3},
  {213, "utf8", "utf8_croatian_ci", 3},
  {214, "utf8", "utf8_unicode_520_ci", 3},
  {215, "utf8", "utf8_vietnamese_ci", 3},
  {216, "NONE", "NONE", 0},
  {217, "NONE", "NONE", 0},
  {218, "NONE", "NONE", 0},
  {219, "NONE", "NONE", 0},
  {220, "NONE", "NONE", 0},
  {221, "NONE", "NONE", 0},
  {222, "NONE", "NONE", 0},
  {223, "utf8", "utf8_general_mysql500_ci", 3},
  {224, "utf8mb4", "utf8mb4_unicode_ci", 4},
  {225, "utf8mb4", "utf8mb4_icelandic_ci", 4},
  {226, "utf8mb4", "utf8mb4_latvian_ci", 4},
  {227, "utf8mb4", "utf8mb4_romanian_ci", 4},
  {228, "utf8mb4", "utf8mb4_slovenian_ci", 4},
  {229, "utf8mb4", "utf8mb4_polish_ci", 4},
  {230, "utf8mb4", "utf8mb4_estonian_ci", 4},
  {231, "utf8mb4", "utf8mb4_spanish_ci", 4},
  {232, "utf8mb4", "utf8mb4_swedish_ci", 4},
  {233, "utf8mb4", "utf8mb4_turkish_ci", 4},
  {234, "utf8mb4", "utf8mb4_czech_ci", 4},
  {235, "utf8mb4", "utf8mb4_danish_ci", 4},
  {236, "utf8mb4", "utf8mb4_lithuanian_ci", 4},
  {237, "utf8mb4", "utf8mb4_slovak_ci", 4},
  {238, "utf8mb4", "utf8mb4_spanish2_ci", 4},
  {239, "utf8mb4", "utf8mb4_roman_ci", 4},
  {240, "utf8mb4", "utf8mb4_persian_ci", 4},
  {241, "utf8mb4", "utf8mb4_esperanto_ci", 4},
  {242, "utf8mb4", "utf8mb4_hungarian_ci", 4},
  {243, "utf8mb4", "utf8mb4_sinhala_ci", 4},
  {244, "utf8mb4", "utf8mb4_german2_ci", 4},
  {245, "utf8mb4", "utf8mb4_croatian_ci", 4},
  {246, "utf8mb4", "utf8mb4_unicode_520_ci", 4},
  {247, "utf8mb4", "utf8mb4_vietnamese_ci", 4},
  {248, "gb18030", "gb18030_chinese_ci", 4},
  {249, "gb18030", "gb18030_bin", 4},
  {250, "gb18030", "gb18030_unicode_520_ci", 4},
  {251, "NONE", "NONE", 0},
  {252, "NONE", "NONE", 0},
  {253, "NONE", "NONE", 0},
  {254, "NONE", "NONE", 0},
  {255, "utf8mb4", "utf8mb4_0900_ai_ci", 4},
  {256, "utf8mb4", "utf8mb4_de_pb_0900_ai_ci", 4},
  {257, "utf8mb4", "utf8mb4_is_0900_ai_ci", 4},
  {258, "utf8mb4", "utf8mb4_lv_0900_ai_ci", 4},
  {259, "utf8mb4", "utf8mb4_ro_0900_ai_ci", 4},
  {260, "utf8mb4", "utf8mb4_sl_0900_ai_ci", 4},
  {261, "utf8mb4", "utf8mb4_pl_0900_ai_ci", 4},
  {262, "utf8mb4", "utf8mb4_et_0900_ai_ci", 4},
  {263, "utf8mb4", "utf8mb4_es_0900_ai_ci", 4},
  {264, "utf8mb4", "utf8mb4_sv_0900_ai_ci", 4},
  {265, "utf8mb4", "utf8mb4_tr_0900_ai_ci", 4},
  {266, "utf8mb4", "utf8mb4_cs_0900_ai_ci", 4},
  {267, "utf8mb4", "utf8mb4_da_0900_ai_ci", 4},
  {268, "utf8mb4", "utf8mb4_lt_0900_ai_ci", 4},
  {269, "utf8mb4", "utf8mb4_sk_0900_ai_ci", 4},
  {270, "utf8mb4", "utf8mb4_es_trad_0900_ai_ci", 4},
  {271, "utf8mb4", "utf8mb4_la_0900_ai_ci", 4},
  {272, "NONE", "NONE", 0},
  {273, "utf8mb4", "utf8mb4_eo_0900_ai_ci", 4},
  {274, "utf8mb4", "utf8mb4_hu_0900_ai_ci", 4},
  {275, "utf8mb4", "utf8mb4_hr_0900_ai_ci", 4},
  {276, "NONE", "NONE", 0},
  {277, "utf8mb4", "utf8mb4_vi_0900_ai_ci", 4},
  {278, "utf8mb4", "utf8mb4_0900_as_cs", 4},
  {279, "utf8mb4", "utf8mb4_de_pb_0900_as_cs", 4},
  {280, "utf8mb4", "utf8mb4_is_0900_as_cs", 4},
  {281, "utf8mb4", "utf8mb4_lv_0900_as_cs", 4},
  {282, "utf8mb4", "utf8mb4_ro_0900_as_cs", 4},
  {283, "utf8mb4", "utf8mb4_sl_0900_as_cs", 4},
  {284, "utf8mb4", "utf8mb4_pl_0900_as_cs", 4},
  {285, "utf8mb4", "utf8mb4_et_0900_as_cs", 4},
  {286, "utf8mb4", "utf8mb4_es_0900_as_cs", 4},
  {287, "utf8mb4", "utf8mb4_sv_0900_as_cs", 4},
  {288, "utf8mb4", "utf8mb4_tr_0900_as_cs", 4},
  {289, "utf8mb4", "utf8mb4_cs_0900_as_cs", 4},
  {290, "utf8mb4", "utf8mb4_da_0900_as_cs", 4},
  {291, "utf8mb4", "utf8mb4_lt_0900_as_cs", 4},
  {292, "utf8mb4", "utf8mb4_sk_0900_as_cs", 4},
  {293, "utf8mb4", "utf8mb4_es_trad_0900_as_cs", 4},
  {294, "utf8mb4", "utf8mb4_la_0900_as_cs", 4},
  {295, "NONE", "NONE", 0},
  {296, "utf8mb4", "utf8mb4_eo_0900_as_cs", 4},
  {297, "utf8mb4", "utf8mb4_hu_0900_as_cs", 4},
  {298, "utf8mb4", "utf8mb4_hr_0900_as_cs", 4},
  {299, "NONE", "NONE", 0},
  {300, "utf8mb4", "utf8mb4_vi_0900_as_cs", 4},
  {301, "NONE", "NONE", 0},
  {302, "NONE", "NONE", 0},
  {303, "utf8mb4", "utf8mb4_ja_0900_as_cs", 4},
  {304, "utf8mb4", "utf8mb4_ja_0900_as_cs_ks", 4},
  {305, "utf8mb4", "utf8mb4_0900_as_ci", 4},
  {306, "utf8mb4", "utf8mb4_ru_0900_ai_ci", 4},
  {307, "utf8mb4", "utf8mb4_ru_0900_as_cs", 4},
  {308, "utf8mb4", "utf8mb4_zh_0900_as_cs", 4},
  {309, "utf8mb4", "utf8mb4_0900_bin", 4},
  {310, "utf8mb4", "utf8mb4_nb_0900_ai_ci", 4},
  {311, "utf8mb4", "utf8mb4_nb_0900_as_cs", 4},
  {312, "utf8mb4", "utf8mb4_nn_0900_ai_ci", 4},
  {313, "utf8mb4", "utf8mb4_nn_0900_as_cs", 4},
  {314, "utf8mb4", "utf8mb4_sr_latn_0900_ai_ci", 4},
  {315, "utf8mb4", "utf8mb4_sr_latn_0900_as_cs", 4},
  {316, "utf8mb4", "utf8mb4_bs_0900_ai_ci", 4},
  {317, "utf8mb4", "utf8mb4_bs_0900_as_cs", 4},
  {318, "utf8mb4", "utf8mb4_bg_0900_ai_ci", 4},
  {319, "utf8mb4", "utf8mb4_bg_0900_as_cs", 4},
  {320, "utf8mb4", "utf8mb4_gl_0900_ai_ci", 4},
  {321, "utf8mb4", "utf8mb4_gl_0900_as_cs", 4},
  {322, "utf8mb4", "utf8mb4_mn_cyrl_0900_ai_ci", 4},
  {323, "utf8mb4", "utf8mb4_mn_cyrl_0900_as_cs", 4}
};


unsigned int get_charset_maxlen(unsigned int num)
{
  if (num < 324)
    return charset_collation_info[num].maxlen;

  return 0;
}

/********************************
This charset mapping function is cut out from server repo: sql_common/client.c
*********************************/

typedef enum my_cs_match_type_enum
{
  /* MySQL and OS charsets are fully compatible */
  my_cs_exact,
  /* MySQL charset is very close to OS charset  */
  my_cs_approx,
  /*
    MySQL knows this charset, but it is not supported as client character set.
  */
  my_cs_unsupp
} my_cs_match_type;


typedef struct str2str_st
{
  const char        *os_name;
  const char        *my_name;
  my_cs_match_type  param;
} MY_CSET_OS_NAME;

static const MY_CSET_OS_NAME charsets[]=
{
#ifdef __WIN__
  {"cp437",          "cp850",    my_cs_approx},
  {"cp850",          "cp850",    my_cs_exact},
  {"cp852",          "cp852",    my_cs_exact},
  {"cp858",          "cp850",    my_cs_approx},
  {"cp866",          "cp866",    my_cs_exact},
  {"cp874",          "tis620",   my_cs_approx},
  {"cp932",          "cp932",    my_cs_exact},
  {"cp936",          "gbk",      my_cs_approx},
  {"cp949",          "euckr",    my_cs_approx},
  {"cp950",          "big5",     my_cs_exact},
  {"cp1200",         "utf16le",  my_cs_unsupp},
  {"cp1201",         "utf16",    my_cs_unsupp},
  {"cp1250",         "cp1250",   my_cs_exact},
  {"cp1251",         "cp1251",   my_cs_exact},
  {"cp1252",         "latin1",   my_cs_exact},
  {"cp1253",         "greek",    my_cs_exact},
  {"cp1254",         "latin5",   my_cs_exact},
  {"cp1255",         "hebrew",   my_cs_approx},
  {"cp1256",         "cp1256",   my_cs_exact},
  {"cp1257",         "cp1257",   my_cs_exact},
  {"cp10000",        "macroman", my_cs_exact},
  {"cp10001",        "sjis",     my_cs_approx},
  {"cp10002",        "big5",     my_cs_approx},
  {"cp10008",        "gb2312",   my_cs_approx},
  {"cp10021",        "tis620",   my_cs_approx},
  {"cp10029",        "macce",    my_cs_exact},
  {"cp12001",        "utf32",    my_cs_unsupp},
  {"cp20107",        "swe7",     my_cs_exact},
  {"cp20127",        "latin1",   my_cs_approx},
  {"cp20866",        "koi8r",    my_cs_exact},
  {"cp20932",        "ujis",     my_cs_exact},
  {"cp20936",        "gb2312",   my_cs_approx},
  {"cp20949",        "euckr",    my_cs_approx},
  {"cp21866",        "koi8u",    my_cs_exact},
  {"cp28591",        "latin1",   my_cs_approx},
  {"cp28592",        "latin2",   my_cs_exact},
  {"cp28597",        "greek",    my_cs_exact},
  {"cp28598",        "hebrew",   my_cs_exact},
  {"cp28599",        "latin5",   my_cs_exact},
  {"cp28603",        "latin7",   my_cs_exact},
#ifdef UNCOMMENT_THIS_WHEN_WL_4579_IS_DONE
  {"cp28605",        "latin9",   my_cs_exact},
#endif
  {"cp38598",        "hebrew",   my_cs_exact},
  {"cp51932",        "ujis",     my_cs_exact},
  {"cp51936",        "gb2312",   my_cs_exact},
  {"cp51949",        "euckr",    my_cs_exact},
  {"cp51950",        "big5",     my_cs_exact},
#ifdef UNCOMMENT_THIS_WHEN_WL_WL_4024_IS_DONE
  {"cp54936",        "gb18030",  my_cs_exact},
#endif
  {"cp65001",        "utf8",     my_cs_exact},

#else /* not Windows */

  {"646",            "latin1",   my_cs_approx}, /* Default on Solaris */
  {"ANSI_X3.4-1968", "latin1",   my_cs_approx},
  {"ansi1251",       "cp1251",   my_cs_exact},
  {"armscii8",       "armscii8", my_cs_exact},
  {"armscii-8",      "armscii8", my_cs_exact},
  {"ASCII",          "latin1",   my_cs_approx},
  {"Big5",           "big5",     my_cs_exact},
  {"cp1251",         "cp1251",   my_cs_exact},
  {"cp1255",         "hebrew",   my_cs_approx},
  {"CP866",          "cp866",    my_cs_exact},
  {"eucCN",          "gb2312",   my_cs_exact},
  {"euc-CN",         "gb2312",   my_cs_exact},
  {"eucJP",          "ujis",     my_cs_exact},
  {"euc-JP",         "ujis",     my_cs_exact},
  {"eucKR",          "euckr",    my_cs_exact},
  {"euc-KR",         "euckr",    my_cs_exact},
#ifdef UNCOMMENT_THIS_WHEN_WL_WL_4024_IS_DONE
  {"gb18030",        "gb18030",  my_cs_exact},
#endif
  {"gb2312",         "gb2312",   my_cs_exact},
  {"gbk",            "gbk",      my_cs_exact},
  {"georgianps",     "geostd8",  my_cs_exact},
  {"georgian-ps",    "geostd8",  my_cs_exact},
  {"IBM-1252",       "cp1252",   my_cs_exact},

  {"iso88591",       "latin1",   my_cs_approx},
  {"ISO_8859-1",     "latin1",   my_cs_approx},
  {"ISO8859-1",      "latin1",   my_cs_approx},
  {"ISO-8859-1",     "latin1",   my_cs_approx},

  {"iso885913",      "latin7",   my_cs_exact},
  {"ISO_8859-13",    "latin7",   my_cs_exact},
  {"ISO8859-13",     "latin7",   my_cs_exact},
  {"ISO-8859-13",    "latin7",   my_cs_exact},

#ifdef UNCOMMENT_THIS_WHEN_WL_4579_IS_DONE
  {"iso885915",      "latin9",   my_cs_exact},
  {"ISO_8859-15",    "latin9",   my_cs_exact},
  {"ISO8859-15",     "latin9",   my_cs_exact},
  {"ISO-8859-15",    "latin9",   my_cs_exact},
#endif

  {"iso88592",       "latin2",   my_cs_exact},
  {"ISO_8859-2",     "latin2",   my_cs_exact},
  {"ISO8859-2",      "latin2",   my_cs_exact},
  {"ISO-8859-2",     "latin2",   my_cs_exact},

  {"iso88597",       "greek",    my_cs_exact},
  {"ISO_8859-7",     "greek",    my_cs_exact},
  {"ISO8859-7",      "greek",    my_cs_exact},
  {"ISO-8859-7",     "greek",    my_cs_exact},

  {"iso88598",       "hebrew",   my_cs_exact},
  {"ISO_8859-8",     "hebrew",   my_cs_exact},
  {"ISO8859-8",      "hebrew",   my_cs_exact},
  {"ISO-8859-8",     "hebrew",   my_cs_exact},

  {"iso88599",       "latin5",   my_cs_exact},
  {"ISO_8859-9",     "latin5",   my_cs_exact},
  {"ISO8859-9",      "latin5",   my_cs_exact},
  {"ISO-8859-9",     "latin5",   my_cs_exact},

  {"koi8r",          "koi8r",    my_cs_exact},
  {"KOI8-R",         "koi8r",    my_cs_exact},
  {"koi8u",          "koi8u",    my_cs_exact},
  {"KOI8-U",         "koi8u",    my_cs_exact},

  {"roman8",         "hp8",      my_cs_exact}, /* Default on HP UX */

  {"Shift_JIS",      "sjis",     my_cs_exact},
  {"SJIS",           "sjis",     my_cs_exact},
  {"shiftjisx0213",  "sjis",     my_cs_exact},

  {"tis620",         "tis620",   my_cs_exact},
  {"tis-620",        "tis620",   my_cs_exact},

  {"ujis",           "ujis",     my_cs_exact},

  {"US-ASCII",       "latin1",   my_cs_approx},

  {"utf8",           "utf8",     my_cs_exact},
  {"utf-8",          "utf8",     my_cs_exact},
#endif
  {NULL,             NULL,       (my_cs_match_type)0}
};

#if(!MYSQLCLIENT_STATIC_LINKING)
const char *
my_os_charset_to_mysql_charset(const char *csname)
{
  const MY_CSET_OS_NAME *csp;
  for (csp= charsets; csp->os_name; ++csp)
  {
    if (!my_strcasecmp(&my_charset_latin1, csp->os_name, csname))
    {
      switch (csp->param)
      {
      case my_cs_exact:
        return csp->my_name;

      case my_cs_approx:
        /*
          Maybe we should print a warning eventually:
          character set correspondence is not exact.
        */
        return csp->my_name;

      default:
        goto def;
      }
    }
  }

def:
  csname= MYSQL_DEFAULT_CHARSET_NAME;

  return csname;
}
#endif

/*
 Converts from wchar_t to SQLWCHAR and copies the result into the provided
 buffer
*/
SQLWCHAR *wchar_t_as_sqlwchar(wchar_t *from, SQLWCHAR *to, size_t len)
{
  /* The function deals mostly with short strings */
  if (len > 1023)
    len= 1023;

  if (sizeof(wchar_t) == sizeof(SQLWCHAR))
  {
    memcpy(to, from, len * sizeof(wchar_t));
    to[len] = 0;
    return to;
  }
  else
  {
    size_t i;
    SQLWCHAR *out= to;
    for (i= 0; i < len; i++)
      to+= utf32toutf16((UTF32)from[i], (UTF16 *)to);
    *to= 0;
    return out;
  }
}

char *myodbc_stpmov(char *dst, const char *src)
{
  while ((*dst++ = *src++));
  return dst - 1;
}

char *myodbc_d2str(double val, char *buf, size_t buf_size, bool max_precision) {
  myodbc_snprintf(buf, buf_size, max_precision ? "%.17e" : "%.15e", val);
  delocalize_radix(buf);
  return buf;
}

char *myodbc_ll2str(longlong val, char *dst, int radix)
{
  char buffer[65];
  char _dig_vec_upper[] =
    "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  char *p;
  long long_val;
  char *dig_vec = _dig_vec_upper;
  ulonglong uval = (ulonglong)val;

  if (radix < 0)
  {
    if (radix < -36 || radix > -2) return (char*)0;
    if (val < 0) {
      *dst++ = '-';
      /* Avoid integer overflow in (-val) for LLONG_MIN (BUG#31799). */
      uval = (ulonglong)0 - uval;
    }
    radix = -radix;
  }
  else
  {
    if (radix > 36 || radix < 2) return (char*)0;
  }
  if (uval == 0)
  {
    *dst++ = '0';
    *dst = '\0';
    return dst;
  }
  p = &buffer[sizeof(buffer)-1];
  *p = '\0';

  while (uval > (ulonglong)LONG_MAX)
  {
    ulonglong quo = uval / (uint)radix;
    uint rem = (uint)(uval - quo* (uint)radix);
    *--p = dig_vec[rem];
    uval = quo;
  }
  long_val = (long)uval;
  while (long_val != 0)
  {
    long quo = long_val / radix;
    *--p = dig_vec[(uchar)(long_val - quo*radix)];
    long_val = quo;
  }
  while ((*dst++ = *p++) != 0);
  return dst - 1;
}

/* We need to use qsort with 2 different compare functions */
#ifdef QSORT_EXTRA_CMP_ARGUMENT
#define CMP(A,B) ((*cmp)(cmp_argument,(A),(B)))
#else
#define CMP(A,B) ((*cmp)((A),(B)))
#endif

#define SWAP(A, B, size,swap_ptrs)			\
do {							\
   if (swap_ptrs)					\
   {							\
     char **a = (char**) (A), **b = (char**) (B);  \
     char *tmp = *a; *a++ = *b; *b++ = tmp;		\
   }							\
   else							\
   {							\
     char *a = (A), *b = (B);			\
     char *end= a+size;				\
     do							\
     {							\
       char tmp = *a; *a++ = *b; *b++ = tmp;		\
     } while (a < end);					\
   }							\
} while (0)

/* Put the median in the middle argument */
#define MEDIAN(low, mid, high)				\
{							\
    if (CMP(high,low) < 0)				\
      SWAP(high, low, size, ptr_cmp);			\
    if (CMP(mid, low) < 0)				\
      SWAP(mid, low, size, ptr_cmp);			\
    else if (CMP(high, mid) < 0)			\
      SWAP(mid, high, size, ptr_cmp);			\
}

/* The following node is used to store ranges to avoid recursive calls */

typedef struct st_stack
{
  char *low, *high;
} stack_node;

#define PUSH(LOW,HIGH)  {stack_ptr->low = LOW; stack_ptr++->high = HIGH;}
#define POP(LOW,HIGH)   {LOW = (--stack_ptr)->low; HIGH = stack_ptr->high;}

/* The following stack size is enough for ulong ~0 elements */
#define STACK_SIZE	(8 * sizeof(unsigned long int))
#define THRESHOLD_FOR_INSERT_SORT 10

/****************************************************************************
** 'standard' quicksort with the following extensions:
**
** Can be compiled with the qsort2_cmp compare function
** Store ranges on stack to avoid recursion
** Use insert sort on small ranges
** Optimize for sorting of pointers (used often by MySQL)
** Use median comparison to find partition element
*****************************************************************************/

void myodbc_qsort(void *base_ptr, size_t count, size_t size, qsort_cmp cmp)
{
  char *low, *high, *pivot;
  stack_node stack[STACK_SIZE], *stack_ptr;
  my_bool ptr_cmp;
  /* Handle the simple case first */
  /* This will also make the rest of the code simpler */
  if (count <= 1)
    return;

  low = (char*)base_ptr;
  high = low + size * (count - 1);
  stack_ptr = stack + 1;
  pivot = (char *)my_alloca((int)size);
  ptr_cmp = size == sizeof(char*) && !((low - (char*)0)& (sizeof(char*) - 1));

  /* The following loop sorts elements between high and low */
  do
  {
    char *low_ptr, *high_ptr, *mid;

    count = ((size_t)(high - low) / size) + 1;
    /* If count is small, then an insert sort is faster than qsort */
    if (count < THRESHOLD_FOR_INSERT_SORT)
    {
      for (low_ptr = low + size; low_ptr <= high; low_ptr += size)
      {
        char *ptr;
        for (ptr = low_ptr; ptr > low && CMP(ptr - size, ptr) > 0;
             ptr -= size)
          SWAP(ptr, ptr - size, size, ptr_cmp);
      }
      POP(low, high);
      continue;
    }

    /* Try to find a good middle element */
    mid = low + size * (count >> 1);
    if (count > 40)				/* Must be bigger than 24 */
    {
      size_t step = size* (count / 8);
      MEDIAN(low, low + step, low + step * 2);
      MEDIAN(mid - step, mid, mid + step);
      MEDIAN(high - 2 * step, high - step, high);
      /* Put best median in 'mid' */
      MEDIAN(low + step, mid, high - step);
      low_ptr = low;
      high_ptr = high;
    }
    else
    {
      MEDIAN(low, mid, high);
      /* The low and high argument are already in sorted against 'pivot' */
      low_ptr = low + size;
      high_ptr = high - size;
    }
    memcpy(pivot, mid, size);

    do
    {
      while (CMP(low_ptr, pivot) < 0)
        low_ptr += size;
      while (CMP(pivot, high_ptr) < 0)
        high_ptr -= size;

      if (low_ptr < high_ptr)
      {
        SWAP(low_ptr, high_ptr, size, ptr_cmp);
        low_ptr += size;
        high_ptr -= size;
      }
      else
      {
        if (low_ptr == high_ptr)
        {
          low_ptr += size;
          high_ptr -= size;
        }
        break;
      }
    } while (low_ptr <= high_ptr);

    /*
    Prepare for next iteration.
    Skip partitions of size 1 as these doesn't have to be sorted
    Push the larger partition and sort the smaller one first.
    This ensures that the stack is keept small.
    */

    if ((int)(high_ptr - low) <= 0)
    {
      if ((int)(high - low_ptr) <= 0)
      {
        POP(low, high);			/* Nothing more to sort */
      }
      else
        low = low_ptr;			/* Ignore small left part. */
    }
    else if ((int)(high - low_ptr) <= 0)
      high = high_ptr;			/* Ignore small right part. */
    else if ((high_ptr - low) > (high - low_ptr))
    {
      PUSH(low, high_ptr);		/* Push larger left part */
      low = low_ptr;
    }
    else
    {
      PUSH(low_ptr, high);		/* Push larger right part */
      high = high_ptr;
    }
  } while (stack_ptr > stack);
  return;
}

/*
  Converts integer to its string representation in decimal notation.

  SYNOPSIS
    myodbc_int10_to_str()
      val     - value to convert
      dst     - points to buffer where string representation should be stored
      radix   - flag that shows whenever val should be taken as signed or not

  DESCRIPTION
    This is version of int2str() function which is optimized for normal case
    of radix 10/-10. It takes only sign of radix parameter into account and
    not its absolute value.

  RETURN VALUE
    Pointer to ending NUL character.
*/

char *myodbc_int10_to_str(long int val, char *dst, int radix) {
  char buffer[65];
  char *p;
  long int new_val;
  unsigned long int uval = (unsigned long int)val;

  if (radix < 0) /* -10 */
  {
    if (val < 0) {
      *dst++ = '-';
      /* Avoid integer overflow in (-val) for LLONG_MIN (BUG#31799). */
      uval = (unsigned long int)0 - uval;
    }
  }

  p = &buffer[sizeof(buffer) - 1];
  *p = '\0';
  new_val = (long)(uval / 10);
  *--p = '0' + (char)(uval - (unsigned long)new_val * 10);
  val = new_val;

  while (val != 0) {
    new_val = val / 10;
    *--p = '0' + (char)(val - new_val * 10);
    val = new_val;
  }
  while ((*dst++ = *p++) != 0)
    ;
  return dst - 1;
}


bool myodbc_append_mem_std(std::string &str, const char *append, size_t length)
{
  str.append(append, length);
  return false;
}


bool myodbc_append_os_quoted_std(std::string &str, const char *append, ...) {
#ifdef _WIN32
  const char *quote_str = "\"";
  const uint quote_len = 1;
#else
  const char *quote_str = "\'";
  const uint quote_len = 1;
#endif /* _WIN32 */
  va_list dirty_text;
  str.reserve(str.length() + 128);
  str.append(quote_str, quote_len); /* Leading quote */
  va_start(dirty_text, append);
  while (append != NullS) {
    const char *cur_pos = append;
    const char *next_pos = cur_pos;

    /* Search for quote in each string and replace with escaped quote */
    while (*(next_pos = strcend(cur_pos, quote_str[0])) != '\0') {
      str.append(cur_pos, (uint)(next_pos - cur_pos)).
          append("\\", 1).append(quote_str, quote_len);
      cur_pos = next_pos + 1;
    }
    str.append(cur_pos, (uint)(next_pos - cur_pos));
    append = va_arg(dirty_text, char *);
  }
  va_end(dirty_text);
  str.append(quote_str, quote_len); /* Trailing quote */

  return false;
}

/*
 * Escape curly brackets.
 */
SQLWSTRING escape_brackets(const SQLWSTRING &val, bool add_start_end)
{
  SQLWSTRING src = val;
  if (!add_start_end &&
      (src.empty() || src.find((SQLWCHAR)'}') == SQLWSTRING::npos)
    )
    return src;

  SQLWSTRING temp;
  if (add_start_end)
    temp = { (SQLWCHAR)'{' };
  // Reserver extra in case we have to escape every bracket
  temp.reserve(src.length() * 2);

  for (SQLWCHAR c : src)
  {
    if (c == (SQLWCHAR)'}')
      temp.append({(SQLWCHAR)'}', (SQLWCHAR)'}'});
    else
      temp.append(&c, 1);
  }
  if (add_start_end)
    temp.append({(SQLWCHAR)'}'});
  return temp;
}

#ifndef EOVERFLOW
#define EOVERFLOW 84
#endif

#define DTOA_BUFF_SIZE (460 * sizeof(void *))

#define MY_ALIGN(A, L) (((A) + (L)-1) & ~((L)-1))

static double myodbc_strtod_int(const char *, const char **, int *, char *, size_t);

/**
   @brief
   Converts string to double (string does not have to be zero-terminated)

   @details
   This is a wrapper around dtoa's version of strtod().

   @param str     input string
   @param length  length of the input string or SQL_NTS if the string
                  is null-terminated and it needs to be measured

   @return        The resulting double value. In case of underflow, 0.0 is
                  returned. In case overflow, signed DBL_MAX is returned.
*/

double myodbc_strtod(const char *str, int length) {
  char buf[DTOA_BUFF_SIZE];
  double res;
  int error = 0;
  assert(str != nullptr);
  const char *end = str + (length == SQL_NTS ? strlen(str) : length);
  res = myodbc_strtod_int(str, &end, &error, buf, sizeof(buf));
  return (error == 0) ? res : (res < 0 ? -DBL_MAX : DBL_MAX);
}


/****************************************************************
 *
 * The author of this software is David M. Gay.
 *
 * Copyright (c) 1991, 2000, 2001 by Lucent Technologies.
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose without fee is hereby granted, provided that this entire notice
 * is included in all copies of any software which is or includes a copy
 * or modification of this software and in all copies of the supporting
 * documentation for such software.
 *
 * THIS SOFTWARE IS BEING PROVIDED "AS IS", WITHOUT ANY EXPRESS OR IMPLIED
 * WARRANTY.  IN PARTICULAR, NEITHER THE AUTHOR NOR LUCENT MAKES ANY
 * REPRESENTATION OR WARRANTY OF ANY KIND CONCERNING THE MERCHANTABILITY
 * OF THIS SOFTWARE OR ITS FITNESS FOR ANY PARTICULAR PURPOSE.
 *
 ***************************************************************/
/* Please send bug reports to David M. Gay (dmg at acm dot org,
 * with " at " changed at "@" and " dot " changed to ".").      */

/*
  Original copy of the software is located at http://www.netlib.org/fp/dtoa.c
  It was adjusted to serve MySQL server needs:
  * strtod() was modified to not expect a zero-terminated string.
    It now honors 'se' (end of string) argument as the input parameter,
    not just as the output one.
  * in dtoa(), in case of overflow/underflow/NaN result string now contains "0";
    decpt is set to DTOA_OVERFLOW to indicate overflow.
  * support for VAX, IBM mainframe and 16-bit hardware removed
  * we always assume that 64-bit integer type is available
  * support for Kernigan-Ritchie style headers (pre-ANSI compilers)
    removed
  * all gcc warnings ironed out
  * we always assume multithreaded environment, so we had to change
    memory allocation procedures to use stack in most cases;
    malloc is used as the last resort.
  * pow5mult rewritten to use pre-calculated pow5 list instead of
    the one generated on the fly.
*/

/*
  On a machine with IEEE extended-precision registers, it is
  necessary to specify double-precision (53-bit) rounding precision
  before invoking strtod or dtoa.  If the machine uses (the equivalent
  of) Intel 80x87 arithmetic, the call
       _control87(PC_53, MCW_PC);
  does this with many compilers.  Whether this or another call is
  appropriate depends on the compiler; for this to work, it may be
  necessary to #include "float.h" or another system-dependent header
  file.
*/

/*
  #define Honor_FLT_ROUNDS if FLT_ROUNDS can assume the values 2 or 3
       and dtoa should round accordingly.
  #define Check_FLT_ROUNDS if FLT_ROUNDS can assume the values 2 or 3
       and Honor_FLT_ROUNDS is not #defined.

  TODO: check if we can get rid of the above two
*/

typedef int32 Long;
typedef uint32 ULong;
typedef int64 LLong;
typedef uint64 ULLong;

typedef union {
  double d;
  ULong L[2];
} U;

#if defined(IS_BIG_ENDIAN)
#define word0(x) (x)->L[0]
#define word1(x) (x)->L[1]
#else
#define word0(x) (x)->L[1]
#define word1(x) (x)->L[0]
#endif

#define dval(x) (x)->d

#define Exp_shift 20
#define Exp_shift1 20
#define Exp_msk1 0x100000
#define Exp_mask 0x7ff00000
#define P 53
#define Bias 1023
#define Emin (-1022)
#define Exp_1 0x3ff00000
#define Exp_11 0x3ff00000
#define Ebits 11
#define Frac_mask 0xfffff
#define Frac_mask1 0xfffff
#define Ten_pmax 22
#define Bletch 0x10
#define Bndry_mask 0xfffff
#define Bndry_mask1 0xfffff
#define LSB 1
#define Sign_bit 0x80000000
#define Log2P 1
#define Tiny1 1
#define Quick_max 14
#define Int_max 14

#ifndef Flt_Rounds
#ifdef FLT_ROUNDS
#define Flt_Rounds FLT_ROUNDS
#else
#define Flt_Rounds 1
#endif
#endif /*Flt_Rounds*/

#ifdef Honor_FLT_ROUNDS
#define Rounding rounding
#undef Check_FLT_ROUNDS
#define Check_FLT_ROUNDS
#endif

#define rounded_product(a, b) a *= b
#define rounded_quotient(a, b) a /= b

#define Big0 (Frac_mask1 | Exp_msk1 * (DBL_MAX_EXP + Bias - 1))
#define Big1 0xffffffff
#define FFFFFFFF 0xffffffffUL

/* This is tested to be enough for dtoa */

#define Kmax 15

#define Bcopy(x, y)                          \
  memcpy((char *)&x->sign, (char *)&y->sign, \
         2 * sizeof(int) + y->wds * sizeof(ULong))

/* Arbitrary-length integer */

typedef struct Bigint {
  union {
    ULong *x;            /* points right after this Bigint object */
    struct Bigint *next; /* to maintain free lists */
  } p;
  int k;      /* 2^k = maxwds */
  int maxwds; /* maximum length in 32-bit words */
  int sign;   /* not zero if number is negative */
  int wds;    /* current length in 32-bit words */
} Bigint;

/* A simple stack-memory based allocator for Bigints */

typedef struct Stack_alloc {
  char *begin;
  char *free;
  char *end;
  /*
    Having list of free blocks lets us reduce maximum required amount
    of memory from ~4000 bytes to < 1680 (tested on x86).
  */
  Bigint *freelist[Kmax + 1];
} Stack_alloc;

/*
  Try to allocate object on stack, and resort to malloc if all
  stack memory is used. Ensure allocated objects to be aligned by the pointer
  size in order to not break the alignment rules when storing a pointer to a
  Bigint.
*/

static Bigint *Balloc(int k, Stack_alloc *alloc) {
  Bigint *rv;
  assert(k <= Kmax);
  if (k <= Kmax && alloc->freelist[k]) {
    rv = alloc->freelist[k];
    alloc->freelist[k] = rv->p.next;
  } else {
    int x, len;

    x = 1 << k;
    len = MY_ALIGN(sizeof(Bigint) + x * sizeof(ULong), sizeof(char *));

    if (alloc->free + len <= alloc->end) {
      rv = (Bigint *)alloc->free;
      alloc->free += len;
    } else
      rv = (Bigint *)malloc(len);

    rv->k = k;
    rv->maxwds = x;
  }
  rv->sign = rv->wds = 0;
  rv->p.x = (ULong *)(rv + 1);
  return rv;
}

/*
  If object was allocated on stack, try putting it to the free
  list. Otherwise call free().
*/

static void Bfree(Bigint *v, Stack_alloc *alloc) {
  char *gptr = (char *)v; /* generic pointer */
  if (gptr < alloc->begin || gptr >= alloc->end)
    free(gptr);
  else if (v->k <= Kmax) {
    /*
      Maintain free lists only for stack objects: this way we don't
      have to bother with freeing lists in the end of dtoa;
      heap should not be used normally anyway.
    */
    v->p.next = alloc->freelist[v->k];
    alloc->freelist[v->k] = v;
  }
}

/* Bigint arithmetic functions */

/* Multiply by m and add a */

static Bigint *multadd(Bigint *b, int m, int a, Stack_alloc *alloc) {
  int i, wds;
  ULong *x;
  ULLong carry, y;
  Bigint *b1;

  wds = b->wds;
  x = b->p.x;
  i = 0;
  carry = a;
  do {
    y = *x * (ULLong)m + carry;
    carry = y >> 32;
    *x++ = (ULong)(y & FFFFFFFF);
  } while (++i < wds);
  if (carry) {
    if (wds >= b->maxwds) {
      b1 = Balloc(b->k + 1, alloc);
      Bcopy(b1, b);
      Bfree(b, alloc);
      b = b1;
    }
    b->p.x[wds++] = (ULong)carry;
    b->wds = wds;
  }
  return b;
}

/**
  Converts a string to Bigint.

  Now we have nd0 digits, starting at s, followed by a
  decimal point, followed by nd-nd0 digits.
  Unless nd0 == nd, in which case we have a number of the form:
     ".xxxxxx"    or    "xxxxxx."

  @param s     Input string, already partially parsed by my_strtod_int().
  @param nd0   Number of digits before decimal point.
  @param nd    Total number of digits.
  @param y9    Pre-computed value of the first nine digits.
  @param alloc Stack allocator for Bigints.
 */
static Bigint *s2b(const char *s, int nd0, int nd, ULong y9,
                   Stack_alloc *alloc) {
  Bigint *b;
  int i, k;
  Long x, y;

  x = (nd + 8) / 9;
  for (k = 0, y = 1; x > y; y <<= 1, k++)
    ;
  b = Balloc(k, alloc);
  b->p.x[0] = y9;
  b->wds = 1;

  i = 9;
  if (9 < nd0) {
    s += 9;
    do b = multadd(b, 10, *s++ - '0', alloc);
    while (++i < nd0);
    s++; /* skip '.' */
  } else
    s += 10;
  /* now do the fractional part */
  for (; i < nd; i++) b = multadd(b, 10, *s++ - '0', alloc);
  return b;
}

static int hi0bits(ULong x) {
  int k = 0;

  if (!(x & 0xffff0000)) {
    k = 16;
    x <<= 16;
  }
  if (!(x & 0xff000000)) {
    k += 8;
    x <<= 8;
  }
  if (!(x & 0xf0000000)) {
    k += 4;
    x <<= 4;
  }
  if (!(x & 0xc0000000)) {
    k += 2;
    x <<= 2;
  }
  if (!(x & 0x80000000)) {
    k++;
    if (!(x & 0x40000000)) return 32;
  }
  return k;
}

static int lo0bits(ULong *y) {
  int k;
  ULong x = *y;

  if (x & 7) {
    if (x & 1) return 0;
    if (x & 2) {
      *y = x >> 1;
      return 1;
    }
    *y = x >> 2;
    return 2;
  }
  k = 0;
  if (!(x & 0xffff)) {
    k = 16;
    x >>= 16;
  }
  if (!(x & 0xff)) {
    k += 8;
    x >>= 8;
  }
  if (!(x & 0xf)) {
    k += 4;
    x >>= 4;
  }
  if (!(x & 0x3)) {
    k += 2;
    x >>= 2;
  }
  if (!(x & 1)) {
    k++;
    x >>= 1;
    if (!x) return 32;
  }
  *y = x;
  return k;
}

/* Convert integer to Bigint number */

static Bigint *i2b(int i, Stack_alloc *alloc) {
  Bigint *b;

  b = Balloc(1, alloc);
  b->p.x[0] = i;
  b->wds = 1;
  return b;
}

/* Multiply two Bigint numbers */

static Bigint *mult(Bigint *a, Bigint *b, Stack_alloc *alloc) {
  Bigint *c;
  int k, wa, wb, wc;
  ULong *x, *xa, *xae, *xb, *xbe, *xc, *xc0;
  ULong y;
  ULLong carry, z;

  if (a->wds < b->wds) {
    c = a;
    a = b;
    b = c;
  }
  k = a->k;
  wa = a->wds;
  wb = b->wds;
  wc = wa + wb;
  if (wc > a->maxwds) k++;
  c = Balloc(k, alloc);
  for (x = c->p.x, xa = x + wc; x < xa; x++) *x = 0;
  xa = a->p.x;
  xae = xa + wa;
  xb = b->p.x;
  xbe = xb + wb;
  xc0 = c->p.x;
  for (; xb < xbe; xc0++) {
    if ((y = *xb++)) {
      x = xa;
      xc = xc0;
      carry = 0;
      do {
        z = *x++ * (ULLong)y + *xc + carry;
        carry = z >> 32;
        *xc++ = (ULong)(z & FFFFFFFF);
      } while (x < xae);
      *xc = (ULong)carry;
    }
  }
  for (xc0 = c->p.x, xc = xc0 + wc; wc > 0 && !*--xc; --wc)
    ;
  c->wds = wc;
  return c;
}

/*
  Precalculated array of powers of 5: tested to be enough for
  vasting majority of dtoa_r cases.
*/

static ULong powers5[] = {
    625UL,

    390625UL,

    2264035265UL, 35UL,

    2242703233UL, 762134875UL,  1262UL,

    3211403009UL, 1849224548UL, 3668416493UL, 3913284084UL, 1593091UL,

    781532673UL,  64985353UL,   253049085UL,  594863151UL,  3553621484UL,
    3288652808UL, 3167596762UL, 2788392729UL, 3911132675UL, 590UL,

    2553183233UL, 3201533787UL, 3638140786UL, 303378311UL,  1809731782UL,
    3477761648UL, 3583367183UL, 649228654UL,  2915460784UL, 487929380UL,
    1011012442UL, 1677677582UL, 3428152256UL, 1710878487UL, 1438394610UL,
    2161952759UL, 4100910556UL, 1608314830UL, 349175UL};

static Bigint p5_a[] = {
    /*  { x } - k - maxwds - sign - wds */
    {{powers5}, 1, 1, 0, 1},       {{powers5 + 1}, 1, 1, 0, 1},
    {{powers5 + 2}, 1, 2, 0, 2},   {{powers5 + 4}, 2, 3, 0, 3},
    {{powers5 + 7}, 3, 5, 0, 5},   {{powers5 + 12}, 4, 10, 0, 10},
    {{powers5 + 22}, 5, 19, 0, 19}};

#define P5A_MAX (sizeof(p5_a) / sizeof(*p5_a) - 1)

static Bigint *pow5mult(Bigint *b, int k, Stack_alloc *alloc) {
  Bigint *b1, *p5, *p51 = nullptr;
  int i;
  static int p05[3] = {5, 25, 125};
  bool overflow = false;

  if ((i = k & 3)) b = multadd(b, p05[i - 1], 0, alloc);

  if (!(k >>= 2)) return b;
  p5 = p5_a;
  for (;;) {
    if (k & 1) {
      b1 = mult(b, p5, alloc);
      Bfree(b, alloc);
      b = b1;
    }
    if (!(k >>= 1)) break;
    /* Calculate next power of 5 */
    if (overflow) {
      p51 = mult(p5, p5, alloc);
      Bfree(p5, alloc);
      p5 = p51;
    } else if (p5 < p5_a + P5A_MAX)
      ++p5;
    else if (p5 == p5_a + P5A_MAX) {
      p5 = mult(p5, p5, alloc);
      overflow = true;
    }
  }
  if (p51) Bfree(p51, alloc);
  return b;
}

static Bigint *lshift(Bigint *b, int k, Stack_alloc *alloc) {
  int i, k1, n, n1;
  Bigint *b1;
  ULong *x, *x1, *xe, z;

  n = k >> 5;
  k1 = b->k;
  n1 = n + b->wds + 1;
  for (i = b->maxwds; n1 > i; i <<= 1) k1++;
  b1 = Balloc(k1, alloc);
  x1 = b1->p.x;
  for (i = 0; i < n; i++) *x1++ = 0;
  x = b->p.x;
  xe = x + b->wds;
  if (k &= 0x1f) {
    k1 = 32 - k;
    z = 0;
    do {
      *x1++ = *x << k | z;
      z = *x++ >> k1;
    } while (x < xe);
    if ((*x1 = z)) ++n1;
  } else
    do *x1++ = *x++;
    while (x < xe);
  b1->wds = n1 - 1;
  Bfree(b, alloc);
  return b1;
}

static int cmp(Bigint *a, Bigint *b) {
  ULong *xa, *xa0, *xb, *xb0;
  int i, j;

  i = a->wds;
  j = b->wds;
  if (i -= j) return i;
  xa0 = a->p.x;
  xa = xa0 + j;
  xb0 = b->p.x;
  xb = xb0 + j;
  for (;;) {
    if (*--xa != *--xb) return *xa < *xb ? -1 : 1;
    if (xa <= xa0) break;
  }
  return 0;
}

static Bigint *diff(Bigint *a, Bigint *b, Stack_alloc *alloc) {
  Bigint *c;
  int i, wa, wb;
  ULong *xa, *xae, *xb, *xbe, *xc;
  ULLong borrow, y;

  i = cmp(a, b);
  if (!i) {
    c = Balloc(0, alloc);
    c->wds = 1;
    c->p.x[0] = 0;
    return c;
  }
  if (i < 0) {
    c = a;
    a = b;
    b = c;
    i = 1;
  } else
    i = 0;
  c = Balloc(a->k, alloc);
  c->sign = i;
  wa = a->wds;
  xa = a->p.x;
  xae = xa + wa;
  wb = b->wds;
  xb = b->p.x;
  xbe = xb + wb;
  xc = c->p.x;
  borrow = 0;
  do {
    y = (ULLong)*xa++ - *xb++ - borrow;
    borrow = y >> 32 & (ULong)1;
    *xc++ = (ULong)(y & FFFFFFFF);
  } while (xb < xbe);
  while (xa < xae) {
    y = *xa++ - borrow;
    borrow = y >> 32 & (ULong)1;
    *xc++ = (ULong)(y & FFFFFFFF);
  }
  while (!*--xc) wa--;
  c->wds = wa;
  return c;
}

static double ulp(U *x) {
  Long L;
  U u;

  L = (word0(x) & Exp_mask) - (P - 1) * Exp_msk1;
  word0(&u) = L;
  word1(&u) = 0;
  return dval(&u);
}

static double b2d(Bigint *a, int *e) {
  ULong *xa, *xa0, w, y, z;
  int k;
  U d;
#define d0 word0(&d)
#define d1 word1(&d)

  xa0 = a->p.x;
  xa = xa0 + a->wds;
  y = *--xa;
  k = hi0bits(y);
  *e = 32 - k;
  if (k < Ebits) {
    d0 = Exp_1 | y >> (Ebits - k);
    w = xa > xa0 ? *--xa : 0;
    d1 = y << ((32 - Ebits) + k) | w >> (Ebits - k);
    goto ret_d;
  }
  z = xa > xa0 ? *--xa : 0;
  if (k -= Ebits) {
    d0 = Exp_1 | y << k | z >> (32 - k);
    y = xa > xa0 ? *--xa : 0;
    d1 = z << k | y >> (32 - k);
  } else {
    d0 = Exp_1 | y;
    d1 = z;
  }
ret_d:
#undef d0
#undef d1
  return dval(&d);
}

static Bigint *d2b(U *d, int *e, int *bits, Stack_alloc *alloc) {
  Bigint *b;
  int de, k;
  ULong *x, y, z;
  int i;
#define d0 word0(d)
#define d1 word1(d)

  b = Balloc(1, alloc);
  x = b->p.x;

  z = d0 & Frac_mask;
  d0 &= 0x7fffffff; /* clear sign bit, which we ignore */
  if ((de = (int)(d0 >> Exp_shift))) z |= Exp_msk1;
  if ((y = d1)) {
    if ((k = lo0bits(&y))) {
      x[0] = y | z << (32 - k);
      z >>= k;
    } else
      x[0] = y;
    i = b->wds = (x[1] = z) ? 2 : 1;
  } else {
    k = lo0bits(&z);
    x[0] = z;
    i = b->wds = 1;
    k += 32;
  }
  if (de) {
    *e = de - Bias - (P - 1) + k;
    *bits = P - k;
  } else {
    *e = de - Bias - (P - 1) + 1 + k;
    *bits = 32 * i - hi0bits(x[i - 1]);
  }
  return b;
#undef d0
#undef d1
}

static double ratio2(Bigint *a, Bigint *b) {
  U da, db;
  int k, ka, kb;

  dval(&da) = b2d(a, &ka);
  dval(&db) = b2d(b, &kb);
  k = ka - kb + 32 * (a->wds - b->wds);
  if (k > 0)
    word0(&da) += k * Exp_msk1;
  else {
    k = -k;
    word0(&db) += k * Exp_msk1;
  }
  return dval(&da) / dval(&db);
}

static const double tens[] = {1e0,  1e1,  1e2,  1e3,  1e4,  1e5,  1e6,  1e7,
                              1e8,  1e9,  1e10, 1e11, 1e12, 1e13, 1e14, 1e15,
                              1e16, 1e17, 1e18, 1e19, 1e20, 1e21, 1e22};

static const double bigtens[] = {1e16, 1e32, 1e64, 1e128, 1e256};
static const double tinytens[] = {
    1e-16, 1e-32, 1e-64, 1e-128,
    9007199254740992. * 9007199254740992.e-256 /* = 2^106 * 1e-53 */
};
/*
  The factor of 2^53 in tinytens[4] helps us avoid setting the underflow
  flag unnecessarily.  It leads to a song and dance at the end of strtod.
*/
#define Scale_Bit 0x10
#define n_bigtens 5

/*
  strtod for IEEE--arithmetic machines.

  This strtod returns a nearest machine number to the input decimal
  string (or sets errno to EOVERFLOW). Ties are broken by the IEEE round-even
  rule.

  Inspired loosely by William D. Clinger's paper "How to Read Floating
  Point Numbers Accurately" [Proc. ACM SIGPLAN '90, pp. 92-101].

  Modifications:

   1. We only require IEEE (not IEEE double-extended).
   2. We get by with floating-point arithmetic in a case that
     Clinger missed -- when we're computing d * 10^n
     for a small integer d and the integer n is not too
     much larger than 22 (the maximum integer k for which
     we can represent 10^k exactly), we may be able to
     compute (d*10^k) * 10^(e-k) with just one roundoff.
   3. Rather than a bit-at-a-time adjustment of the binary
     result in the hard case, we use floating-point
     arithmetic to determine the adjustment to within
     one bit; only in really hard cases do we need to
     compute a second residual.
   4. Because of 3., we don't need a large table of powers of 10
     for ten-to-e (just some small tables, e.g. of 10^k
     for 0 <= k <= 22).
*/

static double myodbc_strtod_int(const char *s00, const char **se, int *error,
                            char *buf, size_t buf_size) {
  int scale;
  int bb2, bb5, bbe, bd2, bd5, bbbits, bs2, c = 0, dsign, e, e1, esign, i, j, k,
                                            nd, nd0, nf, nz, nz0, sign;
  const char *s, *s0, *s1, *end = *se;
  double aadj, aadj1;
  U aadj2, adj, rv, rv0;
  Long L;
  ULong y, z;
  Bigint *bb = nullptr, *bb1, *bd = nullptr, *bd0, *bs = nullptr,
         *delta = nullptr;
#ifdef Honor_FLT_ROUNDS
  int rounding;
#endif
  Stack_alloc alloc;

  *error = 0;

  alloc.begin = alloc.free = buf;
  alloc.end = buf + buf_size;
  memset(alloc.freelist, 0, sizeof(alloc.freelist));

  sign = nz0 = nz = 0;
  dval(&rv) = 0.;
  for (s = s00; s < end; s++) switch (*s) {
      case '-':
        sign = 1;
        [[fallthrough]];
      case '+':
        s++;
        goto break2;
      case '\t':
      case '\n':
      case '\v':
      case '\f':
      case '\r':
      case ' ':
        continue;
      default:
        goto break2;
    }
break2:
  if (s >= end) goto ret0;

  // Gobble up leading zeros.
  if (*s == '0') {
    nz0 = 1;
    while (++s < end && *s == '0')
      ;
    if (s >= end) goto ret;
  }
  s0 = s;
  y = z = 0;
  for (nd = nf = 0; s < end && (c = *s) >= '0' && c <= '9'; nd++, s++)
    if (nd < 9)
      y = 10 * y + c - '0';
    else if (nd < 16)
      z = 10 * z + c - '0';
  nd0 = nd;
  if (s < end && c == '.') {
    if (++s < end) c = *s;
    // Only leading zeros, now count number of leading zeros after the '.'
    if (!nd) {
      for (; s < end; ++s) {
        c = *s;
        if (c != '0') break;
        nz++;
      }
      if (s < end && c > '0' && c <= '9') {
        s0 = s;
        nf += nz;
        nz = 0;
      } else
        goto dig_done;
    }
    for (; s < end; ++s) {
      c = *s;
      if (c < '0' || c > '9') break;

      // We have seen some digits, but not enough of them are non-zero.
      // Gobble up all the rest of the digits, and look for exponent.
      if (nd > 0 && nz > DBL_MAX_10_EXP) {
        continue;
      }

      /*
        Here we are parsing the fractional part.
        We can stop counting digits after a while: the extra digits
        will not contribute to the actual result produced by s2b().
        We have to continue scanning, in case there is an exponent part.
       */
      if (nd < 2 * DBL_DIG) {
        nz++;
        if (c -= '0') {
          nf += nz;
          for (i = 1; i < nz; i++)
            if (nd++ < 9)
              y *= 10;
            else if (nd <= DBL_DIG + 1)
              z *= 10;
          if (nd++ < 9)
            y = 10 * y + c;
          else if (nd <= DBL_DIG + 1)
            z = 10 * z + c;
          nz = 0;
        }
      }
    }
  }
dig_done:
  e = 0;
  if (s < end && (c == 'e' || c == 'E')) {
    if (!nd && !nz && !nz0) goto ret0;
    s00 = s;
    esign = 0;
    if (++s < end) switch (c = *s) {
        case '-':
          esign = 1;
          [[fallthrough]];
        case '+':
          if (++s < end) c = *s;
      }
    if (s < end && c >= '0' && c <= '9') {
      while (s < end && c == '0') c = *++s;
      if (s < end && c > '0' && c <= '9') {
        L = c - '0';
        s1 = s;
        // Avoid overflow in loop body below.
        while (++s < end && (c = *s) >= '0' && c <= '9' &&
               L < (std::numeric_limits<Long>::max() - 255) / 10) {
          L = 10 * L + c - '0';
        }
        if (s - s1 > 8 || L > 19999)
          /* Avoid confusion from exponents
           * so large that e might overflow.
           */
          e = 19999; /* safe for 16 bit ints */
        else
          e = (int)L;
        if (esign) e = -e;
      } else
        e = 0;
    } else
      s = s00;
  }
  if (!nd) {
    if (!nz && !nz0) {
    ret0:
      s = s00;
      sign = 0;
    }
    goto ret;
  }
  e1 = e -= nf;

  /*
    Now we have nd0 digits, starting at s0, followed by a
    decimal point, followed by nd-nd0 digits.  The number we're
    after is the integer represented by those digits times
    10**e
   */

  if (!nd0) nd0 = nd;
  k = nd < DBL_DIG + 1 ? nd : DBL_DIG + 1;
  dval(&rv) = y;
  if (k > 9) {
    dval(&rv) = tens[k - 9] * dval(&rv) + z;
  }
  bd0 = nullptr;
  if (nd <= DBL_DIG
#ifndef Honor_FLT_ROUNDS
      && Flt_Rounds == 1
#endif
  ) {
    if (!e) goto ret;
    if (e > 0) {
      if (e <= Ten_pmax) {
#ifdef Honor_FLT_ROUNDS
        /* round correctly FLT_ROUNDS = 2 or 3 */
        if (sign) {
          rv.d = -rv.d;
          sign = 0;
        }
#endif
        /* rv = */ rounded_product(dval(&rv), tens[e]);
        goto ret;
      }
      i = DBL_DIG - nd;
      if (e <= Ten_pmax + i) {
        /*
          A fancier test would sometimes let us do
          this for larger i values.
        */
#ifdef Honor_FLT_ROUNDS
        /* round correctly FLT_ROUNDS = 2 or 3 */
        if (sign) {
          rv.d = -rv.d;
          sign = 0;
        }
#endif
        e -= i;
        dval(&rv) *= tens[i];
        /* rv = */ rounded_product(dval(&rv), tens[e]);
        goto ret;
      }
    }
#ifndef Inaccurate_Divide
    else if (e >= -Ten_pmax) {
#ifdef Honor_FLT_ROUNDS
      /* round correctly FLT_ROUNDS = 2 or 3 */
      if (sign) {
        rv.d = -rv.d;
        sign = 0;
      }
#endif
      /* rv = */ rounded_quotient(dval(&rv), tens[-e]);
      goto ret;
    }
#endif
  }
  e1 += nd - k;

  scale = 0;
#ifdef Honor_FLT_ROUNDS
  if ((rounding = Flt_Rounds) >= 2) {
    if (sign)
      rounding = rounding == 2 ? 0 : 2;
    else if (rounding != 2)
      rounding = 0;
  }
#endif

  /* Get starting approximation = rv * 10**e1 */

  if (e1 > 0) {
    if ((i = e1 & 15)) dval(&rv) *= tens[i];
    if (e1 &= ~15) {
      if (e1 > DBL_MAX_10_EXP) {
      ovfl:
        *error = EOVERFLOW;
        /* Can't trust HUGE_VAL */
#ifdef Honor_FLT_ROUNDS
        switch (rounding) {
          case 0: /* toward 0 */
          case 3: /* toward -infinity */
            word0(&rv) = Big0;
            word1(&rv) = Big1;
            break;
          default:
            word0(&rv) = Exp_mask;
            word1(&rv) = 0;
        }
#else  /*Honor_FLT_ROUNDS*/
        word0(&rv) = Exp_mask;
        word1(&rv) = 0;
#endif /*Honor_FLT_ROUNDS*/
        if (bd0) goto retfree;
        goto ret;
      }
      e1 >>= 4;
      for (j = 0; e1 > 1; j++, e1 >>= 1)
        if (e1 & 1) dval(&rv) *= bigtens[j];
      /* The last multiplication could overflow. */
      word0(&rv) -= P * Exp_msk1;
      dval(&rv) *= bigtens[j];
      if ((z = word0(&rv) & Exp_mask) > Exp_msk1 * (DBL_MAX_EXP + Bias - P))
        goto ovfl;
      if (z > Exp_msk1 * (DBL_MAX_EXP + Bias - 1 - P)) {
        /* set to largest number (Can't trust DBL_MAX) */
        word0(&rv) = Big0;
        word1(&rv) = Big1;
      } else
        word0(&rv) += P * Exp_msk1;
    }
  } else if (e1 < 0) {
    e1 = -e1;
    if ((i = e1 & 15)) dval(&rv) /= tens[i];
    if ((e1 >>= 4)) {
      if (e1 >= 1 << n_bigtens) goto undfl;
      if (e1 & Scale_Bit) scale = 2 * P;
      for (j = 0; e1 > 0; j++, e1 >>= 1)
        if (e1 & 1) dval(&rv) *= tinytens[j];
      if (scale &&
          (j = 2 * P + 1 - ((word0(&rv) & Exp_mask) >> Exp_shift)) > 0) {
        /* scaled rv is denormal; zap j low bits */
        if (j >= 32) {
          word1(&rv) = 0;
          if (j >= 53)
            word0(&rv) = (P + 2) * Exp_msk1;
          else
            word0(&rv) &= 0xffffffff << (j - 32);
        } else
          word1(&rv) &= 0xffffffff << j;
      }
      if (!dval(&rv)) {
      undfl:
        dval(&rv) = 0.;
        if (bd0) goto retfree;
        goto ret;
      }
    }
  }

  /* Now the hard part -- adjusting rv to the correct value.*/

  /* Put digits into bd: true value = bd * 10^e */

  bd0 = s2b(s0, nd0, nd, y, &alloc);

  for (;;) {
    bd = Balloc(bd0->k, &alloc);
    Bcopy(bd, bd0);
    bb = d2b(&rv, &bbe, &bbbits, &alloc); /* rv = bb * 2^bbe */
    bs = i2b(1, &alloc);

    if (e >= 0) {
      bb2 = bb5 = 0;
      bd2 = bd5 = e;
    } else {
      bb2 = bb5 = -e;
      bd2 = bd5 = 0;
    }
    if (bbe >= 0)
      bb2 += bbe;
    else
      bd2 -= bbe;
    bs2 = bb2;
#ifdef Honor_FLT_ROUNDS
    if (rounding != 1) bs2++;
#endif
    j = bbe - scale;
    i = j + bbbits - 1; /* logb(rv) */
    if (i < Emin)       /* denormal */
      j += P - Emin;
    else
      j = P + 1 - bbbits;
    bb2 += j;
    bd2 += j;
    bd2 += scale;
    i = bb2 < bd2 ? bb2 : bd2;
    if (i > bs2) i = bs2;
    if (i > 0) {
      bb2 -= i;
      bd2 -= i;
      bs2 -= i;
    }
    if (bb5 > 0) {
      bs = pow5mult(bs, bb5, &alloc);
      bb1 = mult(bs, bb, &alloc);
      Bfree(bb, &alloc);
      bb = bb1;
    }
    if (bb2 > 0) bb = lshift(bb, bb2, &alloc);
    if (bd5 > 0) bd = pow5mult(bd, bd5, &alloc);
    if (bd2 > 0) bd = lshift(bd, bd2, &alloc);
    if (bs2 > 0) bs = lshift(bs, bs2, &alloc);
    delta = diff(bb, bd, &alloc);
    dsign = delta->sign;
    delta->sign = 0;
    i = cmp(delta, bs);
#ifdef Honor_FLT_ROUNDS
    if (rounding != 1) {
      if (i < 0) {
        /* Error is less than an ulp */
        if (!delta->p.x[0] && delta->wds <= 1) {
          /* exact */
          break;
        }
        if (rounding) {
          if (dsign) {
            adj.d = 1.;
            goto apply_adj;
          }
        } else if (!dsign) {
          adj.d = -1.;
          if (!word1(&rv) && !(word0(&rv) & Frac_mask)) {
            y = word0(&rv) & Exp_mask;
            if (!scale || y > 2 * P * Exp_msk1) {
              delta = lshift(delta, Log2P, &alloc);
              if (cmp(delta, bs) <= 0) adj.d = -0.5;
            }
          }
        apply_adj:
          if (scale && (y = word0(&rv) & Exp_mask) <= 2 * P * Exp_msk1)
            word0(&adj) += (2 * P + 1) * Exp_msk1 - y;
          dval(&rv) += adj.d * ulp(&rv);
        }
        break;
      }
      adj.d = ratio2(delta, bs);
      if (adj.d < 1.) adj.d = 1.;
      if (adj.d <= 0x7ffffffe) {
        /* adj = rounding ? ceil(adj) : floor(adj); */
        y = adj.d;
        if (y != adj.d) {
          if (!((rounding >> 1) ^ dsign)) y++;
          adj.d = y;
        }
      }
      if (scale && (y = word0(&rv) & Exp_mask) <= 2 * P * Exp_msk1)
        word0(&adj) += (2 * P + 1) * Exp_msk1 - y;
      adj.d *= ulp(&rv);
      if (dsign)
        dval(&rv) += adj.d;
      else
        dval(&rv) -= adj.d;
      goto cont;
    }
#endif /*Honor_FLT_ROUNDS*/

    if (i < 0) {
      /*
        Error is less than half an ulp -- check for special case of mantissa
        a power of two.
      */
      if (dsign || word1(&rv) || word0(&rv) & Bndry_mask ||
          (word0(&rv) & Exp_mask) <= (2 * P + 1) * Exp_msk1) {
        break;
      }
      if (!delta->p.x[0] && delta->wds <= 1) {
        /* exact result */
        break;
      }
      delta = lshift(delta, Log2P, &alloc);
      if (cmp(delta, bs) > 0) goto drop_down;
      break;
    }
    if (i == 0) {
      /* exactly half-way between */
      if (dsign) {
        if ((word0(&rv) & Bndry_mask1) == Bndry_mask1 &&
            word1(&rv) ==
                ((scale && (y = word0(&rv) & Exp_mask) <= 2 * P * Exp_msk1)
                     ? (0xffffffff &
                        (0xffffffff << (2 * P + 1 - (y >> Exp_shift))))
                     : 0xffffffff)) {
          /*boundary case -- increment exponent*/
          word0(&rv) = (word0(&rv) & Exp_mask) + Exp_msk1;
          word1(&rv) = 0;
          dsign = 0;
          break;
        }
      } else if (!(word0(&rv) & Bndry_mask) && !word1(&rv)) {
      drop_down:
        /* boundary case -- decrement exponent */
        if (scale) {
          L = word0(&rv) & Exp_mask;
          if (L <= (2 * P + 1) * Exp_msk1) {
            if (L > (P + 2) * Exp_msk1) /* round even ==> accept rv */
              break;
            /* rv = smallest denormal */
            goto undfl;
          }
        }
        L = (word0(&rv) & Exp_mask) - Exp_msk1;
        word0(&rv) = L | Bndry_mask1;
        word1(&rv) = 0xffffffff;
        break;
      }
      if (!(word1(&rv) & LSB)) break;
      if (dsign)
        dval(&rv) += ulp(&rv);
      else {
        dval(&rv) -= ulp(&rv);
        if (!dval(&rv)) goto undfl;
      }
      dsign = 1 - dsign;
      break;
    }
    if ((aadj = ratio2(delta, bs)) <= 2.) {
      if (dsign)
        aadj = aadj1 = 1.;
      else if (word1(&rv) || word0(&rv) & Bndry_mask) {
        if (word1(&rv) == Tiny1 && !word0(&rv)) goto undfl;
        aadj = 1.;
        aadj1 = -1.;
      } else {
        /* special case -- power of FLT_RADIX to be rounded down... */
        if (aadj < 2. / FLT_RADIX)
          aadj = 1. / FLT_RADIX;
        else
          aadj *= 0.5;
        aadj1 = -aadj;
      }
    } else {
      aadj *= 0.5;
      aadj1 = dsign ? aadj : -aadj;
#ifdef Check_FLT_ROUNDS
      switch (Rounding) {
        case 2: /* towards +infinity */
          aadj1 -= 0.5;
          break;
        case 0: /* towards 0 */
        case 3: /* towards -infinity */
          aadj1 += 0.5;
      }
#else
      if (Flt_Rounds == 0) aadj1 += 0.5;
#endif /*Check_FLT_ROUNDS*/
    }
    y = word0(&rv) & Exp_mask;

    /* Check for overflow */

    if (y == Exp_msk1 * (DBL_MAX_EXP + Bias - 1)) {
      dval(&rv0) = dval(&rv);
      word0(&rv) -= P * Exp_msk1;
      adj.d = aadj1 * ulp(&rv);
      dval(&rv) += adj.d;
      if ((word0(&rv) & Exp_mask) >= Exp_msk1 * (DBL_MAX_EXP + Bias - P)) {
        if (word0(&rv0) == Big0 && word1(&rv0) == Big1) goto ovfl;
        word0(&rv) = Big0;
        word1(&rv) = Big1;
        goto cont;
      } else
        word0(&rv) += P * Exp_msk1;
    } else {
      if (scale && y <= 2 * P * Exp_msk1) {
        if (aadj <= 0x7fffffff) {
          if ((z = (ULong)aadj) <= 0) z = 1;
          aadj = z;
          aadj1 = dsign ? aadj : -aadj;
        }
        dval(&aadj2) = aadj1;
        word0(&aadj2) += (2 * P + 1) * Exp_msk1 - y;
        aadj1 = dval(&aadj2);
        adj.d = aadj1 * ulp(&rv);
        dval(&rv) += adj.d;
        if (rv.d == 0.) goto undfl;
      } else {
        adj.d = aadj1 * ulp(&rv);
        dval(&rv) += adj.d;
      }
    }
    z = word0(&rv) & Exp_mask;
    if (!scale)
      if (y == z) {
        /* Can we stop now? */
        L = (Long)aadj;
        aadj -= L;
        /* The tolerances below are conservative. */
        if (dsign || word1(&rv) || word0(&rv) & Bndry_mask) {
          if (aadj < .4999999 || aadj > .5000001) break;
        } else if (aadj < .4999999 / FLT_RADIX)
          break;
      }
  cont:
    Bfree(bb, &alloc);
    Bfree(bd, &alloc);
    Bfree(bs, &alloc);
    Bfree(delta, &alloc);
  }
  if (scale) {
    word0(&rv0) = Exp_1 - 2 * P * Exp_msk1;
    word1(&rv0) = 0;
    dval(&rv) *= dval(&rv0);
  }
retfree:
  Bfree(bb, &alloc);
  Bfree(bd, &alloc);
  Bfree(bs, &alloc);
  Bfree(bd0, &alloc);
  Bfree(delta, &alloc);
ret:
  *se = s;
  return sign ? -dval(&rv) : dval(&rv);
}
