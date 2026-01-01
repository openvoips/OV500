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
  @file  unicode_transcode.c
  @brief Unicode transcoding functions. Raw conversions.
*/

#ifndef ODBCTAP
# include "stringutil.h"
#endif

/**
  Convert UTF-16 code unit(s) to a UTF-32 character. For characters in the
  Basic Multilingual Plane, one UTF-16 code unit maps to one UTF-32 character,
  but characters in other planes may require two UTF-16 code units.

  @param[in] i  Pointer to UTF-16 code units
  @param[in] u  Pointer to UTF-32 character

  @return Number of UTF-16 code units consumed.
*/
int utf16toutf32(UTF16 *i, UTF32 *u)
{
  if (*i >= 0xd800 && *i <= 0xdbff)
  {
    *u= 0x10000 | ((*i++ & 0x3ff) << 10);
    if (*i < 0xdc00 || *i > 0xdfff) /* invalid */
      return 0;
    *u|= *i & 0x3ff;
    return 2;
  }
  else
  {
    *u= *i;
    return 1;
  }
}


/**
  Convert UTF-32 character to UTF-16 code unit(s).

  @param[in] i  UTF-32 character
  @param[in] u  Pointer to UTF-16 code units

  @return Number of UTF-16 code units produced.
*/
int utf32toutf16(UTF32 i, UTF16 *u)
{
  if (i < 0xffff)
  {
    *u= (UTF16)(i & 0xffff);
    return 1;
  }
  else if(i < 0x10ffff)
  {
    i-= 0x10000;
    *u++= 0xd800 | (i >> 10);
    *u= 0xdc00 | (i & 0x3ff);
    return 2;
  }
  return 0;
}


/**
  Convert UTF-8 octets to a UTF-32 character. It may take up to four
  UTF-8 octets to encode one UTF-32 character.

  @param[in] i  Pointer to UTF-8 octets
  @param[in] u  Pointer to UTF-32 character

  @return Number of UTF-8 octets consumed, or 0 if an invalid character was
  encountered.
*/
int utf8toutf32(UTF8 *i, UTF32 *u)
{
  int len, x;

  if (*i < 0x80)
  {
    *u= *i;
    return 1;
  }
  else if (*i < 0xe0)
  {
    len= 2;
    *u= *i & 0x1f;
  }
  else if (*i < 0xf0)
  {
    len= 3;
    *u= *i & 0x0f;
  }
  else
  {
    len= 4;
    *u= *i & 0x07;
  }

  x= len;
  while (--x)
  {
    *u<<= 6;
    *u|= *++i & 0x3f;
    if (*i >> 6 != 2) /* invalid */
      return 0;
  }

  return len;
}


/**
  Convert a UTF-32 character into UTF-8 octets. It may take four UTF-8
  octets to encode one UTF-32 character.

  @param[in] i  UTF-32 characer
  @param[in] u  Pointer to UTF-8 octets

  @return Number of UTF-8 octets produced.
*/
int utf32toutf8(UTF32 i, UTF8 *c)
{
  int len= 0, x;

  if (i < 0x80)
  {
    *c= (UTF8)(i & 0x7f);
    return 1;
  }
  else if (i < 0x800)
  {
    *c++= (3 << 6) | (i >> 6);
    len= 2;
  }
  else if (i < 0x10000)
  {
    *c++= (7 << 5) | (i >> 12);
    len= 3;
  }
  else if (i < 0x10ffff)
  {
    *c++= (0xf << 4) | (i >> 18);
    len= 4;
  }

  x= len;
  if (x)
    while (--x)
    {
      *c++= (1 << 7) | ((i >> (6 * (x - 1))) & 0x3f);
    }

  return len;
}


#ifdef UCTEST

#include <assert.h>
#include <string.h>
#include <stdio.h>

typedef struct {
  UTF8 u8[4];
  UTF32 u32;
  int cnt;
} t_8_32;

typedef struct {
  UTF16 u16[2];
  UTF32 u32;
  int cnt;
} t_16_32;

void t1()
{
  int i, j;
  t_8_32 t1[]= {
      {{0, 0, 0, 0}, 0, 1},
      {{0x3c, 0, 0, 0}, 0x3c, 1},
      {{0xc3, 0xbe, 0, 0}, 0xfe, 2},
      {{0xe0, 0xa4, 0x96, 0}, 0x916, 3},
      {{0xf0, 0x90, 0x85, 0xad}, 0x1016d, 4}
  };
  printf("***** T1 -> utf32<->utf8 *****\n");
  for (i= 0; i < sizeof(t1) / sizeof(t_8_32); ++i)
  {
    int cnt;
    t_8_32 t= t1[i];
    UTF8 res[4];
    UTF32 resu;
    memset(res, 0, 4);
    printf("Convert %x\n", t.u32);
    cnt= utf32toutf8(t.u32, res);
    assert(cnt == t.cnt);
    for (j= 0; j < 4; ++j)
    {
      printf("Res[%d] = 0x%x (expect 0x%x)\n", j, res[j], t.u8[j]);
      assert(res[j] == t.u8[j]);
    }
    printf("Ok. Now back\n");
    cnt= utf8toutf32(t.u8, &resu);
    printf("ResU = %x\n", resu);
    assert(cnt == t.cnt);
    assert(resu == t.u32);
  }
}

void t2()
{
  int i, j;
  t_16_32 t1[]= {
      {{0, 0}, 0, 1},
      {{0x7a, 0}, 0x7a, 1},
      {{0x6c34, 0}, 0x6c34, 1},
      {{0xd834, 0xdd1e}, 0x1d11e, 2}
  };
  printf("***** T2 -> utf32<->utf16 *****\n");
  for (i= 0; i < sizeof(t1) / sizeof(t_16_32); ++i)
  {
    int cnt;
    t_16_32 t= t1[i];
    UTF16 res[2];
    UTF32 resu;
    memset(res, 0, 2 * 2);
    printf("Convert %x\n", t.u32);
    cnt= utf32toutf16(t.u32, res);
    assert(cnt == t.cnt);
    for (j = 0; j < 2; ++j)
    {
      printf("Res[%d] = 0x%x (expect 0x%x)\n", j, res[j], t.u16[j]);
      assert(res[j] == t.u16[j]);
    }
    printf("Ok. Now back\n");
    cnt= utf16toutf32(t.u16, &resu);
    printf("ResU = %x\n", resu);
    assert(cnt == t.cnt);
    assert(resu == t.u32);
  }
}

int main(int argc, char **argv)
{
  t1();
  t2();
  exit(0);
}
#endif /* UCTEST */
