/*
  Basic UTF-8 manipulation routines
  by Jeff Bezanson
  placed in the public domain Fall 2005

  This code is designed to provide the utilities you need to manipulate
  UTF-8 as an internal string encoding. These functions do not perform the
  error checking normally needed when handling UTF-8 data, so if you happen
  to be from the Unicode Consortium you will want to flay me alive.
  I do this because error checking can be performed at the boundaries (I/O),
  with these routines reserved for higher performance on data known to be
  valid.
*/

#include "libks/ks.h"

/* is c the start of a utf8 sequence? */
#define isutf(c) (((c)&0xC0)!=0x80)

/* convert UTF-8 data to wide character */
KS_DECLARE(int) ks_u8_toucs(uint32_t *dest, int sz, char *src, int srcsz);

/* the opposite conversion */
KS_DECLARE(int) ks_u8_toutf8(char *dest, int sz, uint32_t *src, int srcsz);

/* single character to UTF-8 */
KS_DECLARE(int) ks_u8_wc_toutf8(char *dest, uint32_t ch);

/* character number to byte offset */
KS_DECLARE(int) ks_u8_offset(char *str, int charnum);

/* byte offset to character number */
KS_DECLARE(int) ks_u8_charnum(char *s, int offset);

/* return next character, updating an index variable */
KS_DECLARE(uint32_t) ks_u8_nextchar(char *s, int *i);

/* move to next character */
KS_DECLARE(void) ks_u8_inc(char *s, int *i);

/* move to previous character */
KS_DECLARE(void) ks_u8_dec(char *s, int *i);

/* returns length of next utf-8 sequence */
KS_DECLARE(int) ks_u8_seqlen(char *s);

/* assuming src points to the character after a backslash, read an
   escape sequence, storing the result in dest and returning the number of
   input characters processed */
KS_DECLARE(int) ks_u8_read_escape_sequence(char *src, uint32_t *dest);

/* given a wide character, convert it to an ASCII escape sequence stored in
   buf, where buf is "sz" bytes. returns the number of characters output.*/
KS_DECLARE(int) ks_u8_escape_wchar(char *buf, int sz, uint32_t ch);

/* convert a string "src" containing escape sequences to UTF-8 */
KS_DECLARE(int) ks_u8_unescape(char *buf, int sz, char *src);

/* convert UTF-8 "src" to ASCII with escape sequences.
   if escape_quotes is nonzero, quote characters will be preceded by
   backslashes as well. */
KS_DECLARE(int) ks_u8_escape(char *buf, int sz, char *src, int escape_quotes);

/* utility predicates used by the above */
KS_DECLARE(int) octal_digit(char c);
KS_DECLARE(int) hex_digit(char c);

/* return a pointer to the first occurrence of ch in s, or NULL if not
   found. character index of found character returned in *charn. */
KS_DECLARE(char *) ks_u8_strchr(char *s, uint32_t ch, int *charn);

/* same as the above, but searches a buffer of a given size instead of
   a NUL-terminated string. */
KS_DECLARE(char *) ks_u8_memchr(char *s, uint32_t ch, size_t sz, int *charn);

/* count the number of characters in a UTF-8 string */
KS_DECLARE(int) ks_u8_strlen(char *s);

KS_DECLARE(int) ks_u8_is_locale_utf8(char *locale);

KS_DECLARE(uint32_t) ks_u8_get_char(char *s, int *i);
