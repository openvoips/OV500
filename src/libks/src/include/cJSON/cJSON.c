/*
  Copyright (c) 2009-2017 Dave Gamble and cJSON contributors

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
*/

/* cJSON */
/* JSON parser in C. */

/* disable warnings about old C89 functions in MSVC */
#if !defined(_CRT_SECURE_NO_DEPRECATE) && defined(_MSC_VER)
#define _CRT_SECURE_NO_DEPRECATE
#endif

#ifdef __GNUC__
#pragma GCC visibility push(default)
#endif
#if defined(_MSC_VER)
#pragma warning (push)
/* disable warning about single line comments in system headers */
#pragma warning (disable : 4001)
#endif

#include <string.h>
#include <stdio.h>
#include <math.h>
#include <stdlib.h>
#include <limits.h>
#include <ctype.h>

#ifdef ENABLE_LOCALES
#include <locale.h>
#endif

#if defined(_MSC_VER)
#pragma warning (pop)
#endif
#ifdef __GNUC__
#pragma GCC visibility pop
#endif

#include "cJSON.h"

/* define our own boolean type */
#define CJSON_TRUE ((kJSON_bool)1)
#define CJSON_FALSE ((kJSON_bool)0)

typedef struct {
    const unsigned char *json;
    size_t position;
} error;
static error global_error = { NULL, 0 };

CJSON_PUBLIC(const char *) kJSON_GetErrorPtr(void)
{
    return (const char*) (global_error.json + global_error.position);
}

CJSON_PUBLIC(char *) kJSON_GetStringValue(kJSON *item) {
    if (!kJSON_IsString(item)) {
        return NULL;
    }

    return item->valuestring;
}

/* This is a safeguard to prevent copy-pasters from using incompatible C and header files */
#if (CJSON_VERSION_MAJOR != 1) || (CJSON_VERSION_MINOR != 7) || (CJSON_VERSION_PATCH != 6)
    #error cJSON.h and cJSON.c have different versions. Make sure that both have the same.
#endif

CJSON_PUBLIC(const char*) kJSON_Version(void)
{
    static char version[15];
    sprintf(version, "%i.%i.%i", CJSON_VERSION_MAJOR, CJSON_VERSION_MINOR, CJSON_VERSION_PATCH);

    return version;
}

/* Case insensitive string comparison, doesn't consider two NULL pointers equal though */
static int case_insensitive_strcmp(const unsigned char *string1, const unsigned char *string2)
{
    if ((string1 == NULL) || (string2 == NULL))
    {
        return 1;
    }

    if (string1 == string2)
    {
        return 0;
    }

    for(; tolower(*string1) == tolower(*string2); (void)string1++, string2++)
    {
        if (*string1 == '\0')
        {
            return 0;
        }
    }

    return tolower(*string1) - tolower(*string2);
}

typedef struct internal_hooks
{
    void *(*allocate)(size_t size);
    void (*deallocate)(void *pointer);
    void *(*reallocate)(void *pointer, size_t size);
} internal_hooks;

#if defined(_MSC_VER)
/* work around MSVC error C2322: '...' address of dillimport '...' is not static */
static void *internal_malloc(size_t size)
{
	return calloc(1, size);
}
static void internal_free(void *pointer)
{
    free(pointer);
}
static void *internal_realloc(void *pointer, size_t size)
{
    return realloc(pointer, size);
}
#else
#define internal_malloc malloc
#define internal_free free
#define internal_realloc realloc
#endif

static internal_hooks global_hooks = { internal_malloc, internal_free, internal_realloc };

static unsigned char* kJSON_strdup(const unsigned char* string, const internal_hooks * const hooks)
{
    size_t length = 0;
    unsigned char *copy = NULL;

    if (string == NULL)
    {
        return NULL;
    }

    length = strlen((const char*)string) + sizeof("");
    copy = (unsigned char*)hooks->allocate(length);
    if (copy == NULL)
    {
        return NULL;
    }
    memcpy(copy, string, length);

    return copy;
}

CJSON_PUBLIC(void) kJSON_InitHooks(kJSON_Hooks* hooks)
{
    if (hooks == NULL)
    {
        /* Reset hooks */
        global_hooks.allocate = malloc;
        global_hooks.deallocate = free;
        global_hooks.reallocate = realloc;
        return;
    }

	global_hooks.allocate = hooks->malloc_fn;
	global_hooks.deallocate = hooks->free_fn;
	global_hooks.reallocate = hooks->realloc_fn;
}

/* Internal constructor. */
static kJSON *kJSON_New_Item(const internal_hooks * const hooks)
{
    kJSON *node = (kJSON*)hooks->allocate(sizeof(kJSON));
    if (node)
    {
        memset(node, '\0', sizeof(kJSON));
    }
    return node;
}

/* Delete a kJSON structure. */
CJSON_PUBLIC(void) kJSON_Delete(kJSON *item)
{
    kJSON *next = NULL;
    while (item != NULL)
    {
        next = item->next;
        if (!(item->type & kJSON_IsReference) && (item->child != NULL))
        {
            kJSON_Delete(item->child);
        }
        if (!(item->type & kJSON_IsReference) && (item->valuestring != NULL))
        {
            global_hooks.deallocate(item->valuestring);
        }
        if (!(item->type & kJSON_StringIsConst) && (item->string != NULL))
        {
            global_hooks.deallocate(item->string);
        }
        global_hooks.deallocate(item);
        item = next;
    }
}

/* get the decimal point character of the current locale */
static unsigned char get_decimal_point(void)
{
#ifdef ENABLE_LOCALES
    struct lconv *lconv = localeconv();
    return (unsigned char) lconv->decimal_point[0];
#else
    return '.';
#endif
}

typedef struct
{
    const unsigned char *content;
    size_t length;
    size_t offset;
    size_t depth; /* How deeply nested (in arrays/objects) is the input at the current offset. */
    internal_hooks hooks;
} parse_buffer;

/* check if the given size is left to read in a given parse buffer (starting with 1) */
#define can_read(buffer, size) ((buffer != NULL) && (((buffer)->offset + size) <= (buffer)->length))
/* check if the buffer can be accessed at the given index (starting with 0) */
#define can_access_at_index(buffer, index) ((buffer != NULL) && (((buffer)->offset + index) < (buffer)->length))
#define cannot_access_at_index(buffer, index) (!can_access_at_index(buffer, index))
/* get a pointer to the buffer at the position */
#define buffer_at_offset(buffer) ((buffer)->content + (buffer)->offset)

/* Parse the input text to generate a number, and populate the result into item. */
static kJSON_bool parse_number(kJSON * const item, parse_buffer * const input_buffer)
{
    double number = 0;
    unsigned char *after_end = NULL;
    unsigned char number_c_string[64];
    unsigned char decimal_point = get_decimal_point();
    size_t i = 0;

    if ((input_buffer == NULL) || (input_buffer->content == NULL))
    {
        return CJSON_FALSE;
    }

    /* copy the number into a temporary buffer and replace '.' with the decimal point
     * of the current locale (for strtod)
     * This also takes care of '\0' not necessarily being available for marking the end of the input */
    for (i = 0; (i < (sizeof(number_c_string) - 1)) && can_access_at_index(input_buffer, i); i++)
    {
        switch (buffer_at_offset(input_buffer)[i])
        {
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
            case '+':
            case '-':
            case 'e':
            case 'E':
                number_c_string[i] = buffer_at_offset(input_buffer)[i];
                break;

            case '.':
                number_c_string[i] = decimal_point;
                break;

            default:
                goto loop_end;
        }
    }
loop_end:
    number_c_string[i] = '\0';

    number = strtod((const char*)number_c_string, (char**)&after_end);
    if (number_c_string == after_end)
    {
        return CJSON_FALSE; /* parse_error */
    }

    item->valuedouble = number;

    /* use saturation in case of overflow */
    if (number >= INT_MAX)
    {
        item->valueint = INT_MAX;
    }
    else if (number <= INT_MIN)
    {
        item->valueint = INT_MIN;
    }
    else
    {
        item->valueint = (int)number;
    }

    item->type = kJSON_Number;

    input_buffer->offset += (size_t)(after_end - number_c_string);
    return CJSON_TRUE;
}

/* don't ask me, but the original kJSON_SetNumberValue returns an integer or double */
CJSON_PUBLIC(double) kJSON_SetNumberHelper(kJSON *object, double number)
{
    if (number >= INT_MAX)
    {
        object->valueint = INT_MAX;
    }
    else if (number <= INT_MIN)
    {
        object->valueint = INT_MIN;
    }
    else
    {
        object->valueint = (int)number;
    }

    return object->valuedouble = number;
}

typedef struct
{
    unsigned char *buffer;
    size_t length;
    size_t offset;
    size_t depth; /* current nesting depth (for formatted printing) */
    kJSON_bool noalloc;
    kJSON_bool format; /* is this print a formatted print */
    internal_hooks hooks;
} printbuffer;

/* realloc printbuffer if necessary to have at least "needed" bytes more */
static unsigned char* ensure(printbuffer * const p, size_t needed)
{
    unsigned char *newbuffer = NULL;
    size_t newsize = 0;

    if ((p == NULL) || (p->buffer == NULL))
    {
        return NULL;
    }

    if ((p->length > 0) && (p->offset >= p->length))
    {
        /* make sure that offset is valid */
        return NULL;
    }

    if (needed > INT_MAX)
    {
        /* sizes bigger than INT_MAX are currently not supported */
        return NULL;
    }

    needed += p->offset + 1;
    if (needed <= p->length)
    {
        return p->buffer + p->offset;
    }

    if (p->noalloc) {
        return NULL;
    }

    /* calculate new buffer size */
    if (needed > (INT_MAX / 2))
    {
        /* overflow of int, use INT_MAX if possible */
        if (needed <= INT_MAX)
        {
            newsize = INT_MAX;
        }
        else
        {
            return NULL;
        }
    }
    else
    {
        newsize = needed * 2;
    }

    if (p->hooks.reallocate != NULL)
    {
        /* reallocate with realloc if available */
        newbuffer = (unsigned char*)p->hooks.reallocate(p->buffer, newsize);
        if (newbuffer == NULL)
        {
            p->hooks.deallocate(p->buffer);
            p->length = 0;
            p->buffer = NULL;

            return NULL;
        }
    }
    else
    {
        /* otherwise reallocate manually */
        newbuffer = (unsigned char*)p->hooks.allocate(newsize);
        if (!newbuffer)
        {
            p->hooks.deallocate(p->buffer);
            p->length = 0;
            p->buffer = NULL;

            return NULL;
        }
        if (newbuffer)
        {
            memcpy(newbuffer, p->buffer, p->offset + 1);
        }
        p->hooks.deallocate(p->buffer);
    }
    p->length = newsize;
    p->buffer = newbuffer;

    return newbuffer + p->offset;
}

/* calculate the new length of the string in a printbuffer and update the offset */
static void update_offset(printbuffer * const buffer)
{
    const unsigned char *buffer_pointer = NULL;
    if ((buffer == NULL) || (buffer->buffer == NULL))
    {
        return;
    }
    buffer_pointer = buffer->buffer + buffer->offset;

    buffer->offset += strlen((const char*)buffer_pointer);
}

/* Render the number nicely from the given item into a string. */
static kJSON_bool print_number(const kJSON * const item, printbuffer * const output_buffer)
{
    unsigned char *output_pointer = NULL;
    double d = item->valuedouble;
    int length = 0;
    size_t i = 0;
    unsigned char number_buffer[26]; /* temporary buffer to print the number into */
    unsigned char decimal_point = get_decimal_point();

    if (output_buffer == NULL)
    {
        return CJSON_FALSE;
    }

    /* This checks for NaN and Infinity */
    if ((d * 0) != 0)
    {
        length = sprintf((char*)number_buffer, "null");
    }
    else
    {
		double i = 0;
		if ((int)!modf(d, &i)) {
			length = sprintf((char*)number_buffer, "%ld", (long int)d);
		} else {
			length = sprintf((char*)number_buffer, "%lf", d);
		}
    }

    /* sprintf failed or buffer overrun occured */
    if ((length < 0) || (length > (int)(sizeof(number_buffer) - 1)))
    {
        return CJSON_FALSE;
    }

    /* reserve appropriate space in the output */
    output_pointer = ensure(output_buffer, (size_t)length + sizeof(""));
    if (output_pointer == NULL)
    {
        return CJSON_FALSE;
    }

    /* copy the printed number to the output and replace locale
     * dependent decimal point with '.' */
    for (i = 0; i < ((size_t)length); i++)
    {
        if (number_buffer[i] == decimal_point)
        {
            output_pointer[i] = '.';
            continue;
        }

        output_pointer[i] = number_buffer[i];
    }
    output_pointer[i] = '\0';

    output_buffer->offset += (size_t)length;

    return CJSON_TRUE;
}

/* parse 4 digit hexadecimal number */
static unsigned parse_hex4(const unsigned char * const input)
{
    unsigned int h = 0;
    size_t i = 0;

    for (i = 0; i < 4; i++)
    {
        /* parse digit */
        if ((input[i] >= '0') && (input[i] <= '9'))
        {
            h += (unsigned int) input[i] - '0';
        }
        else if ((input[i] >= 'A') && (input[i] <= 'F'))
        {
            h += (unsigned int) 10 + input[i] - 'A';
        }
        else if ((input[i] >= 'a') && (input[i] <= 'f'))
        {
            h += (unsigned int) 10 + input[i] - 'a';
        }
        else /* invalid */
        {
            return 0;
        }

        if (i < 3)
        {
            /* shift left to make place for the next nibble */
            h = h << 4;
        }
    }

    return h;
}

/* converts a UTF-16 literal to UTF-8
 * A literal can be one or two sequences of the form \uXXXX */
static unsigned char utf16_literal_to_utf8(const unsigned char * const input_pointer, const unsigned char * const input_end, unsigned char **output_pointer)
{
    long unsigned int codepoint = 0;
    unsigned int first_code = 0;
    const unsigned char *first_sequence = input_pointer;
    unsigned char utf8_length = 0;
    unsigned char utf8_position = 0;
    unsigned char sequence_length = 0;
    unsigned char first_byte_mark = 0;

    if ((input_end - first_sequence) < 6)
    {
        /* input ends unexpectedly */
        goto fail;
    }

    /* get the first utf16 sequence */
    first_code = parse_hex4(first_sequence + 2);

    /* check that the code is valid */
    if (((first_code >= 0xDC00) && (first_code <= 0xDFFF)))
    {
        goto fail;
    }

    /* UTF16 surrogate pair */
    if ((first_code >= 0xD800) && (first_code <= 0xDBFF))
    {
        const unsigned char *second_sequence = first_sequence + 6;
        unsigned int second_code = 0;
        sequence_length = 12; /* \uXXXX\uXXXX */

        if ((input_end - second_sequence) < 6)
        {
            /* input ends unexpectedly */
            goto fail;
        }

        if ((second_sequence[0] != '\\') || (second_sequence[1] != 'u'))
        {
            /* missing second half of the surrogate pair */
            goto fail;
        }

        /* get the second utf16 sequence */
        second_code = parse_hex4(second_sequence + 2);
        /* check that the code is valid */
        if ((second_code < 0xDC00) || (second_code > 0xDFFF))
        {
            /* invalid second half of the surrogate pair */
            goto fail;
        }


        /* calculate the unicode codepoint from the surrogate pair */
        codepoint = 0x10000 + (((first_code & 0x3FF) << 10) | (second_code & 0x3FF));
    }
    else
    {
        sequence_length = 6; /* \uXXXX */
        codepoint = first_code;
    }

    /* encode as UTF-8
     * takes at maximum 4 bytes to encode:
     * 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx */
    if (codepoint < 0x80)
    {
        /* normal ascii, encoding 0xxxxxxx */
        utf8_length = 1;
    }
    else if (codepoint < 0x800)
    {
        /* two bytes, encoding 110xxxxx 10xxxxxx */
        utf8_length = 2;
        first_byte_mark = 0xC0; /* 11000000 */
    }
    else if (codepoint < 0x10000)
    {
        /* three bytes, encoding 1110xxxx 10xxxxxx 10xxxxxx */
        utf8_length = 3;
        first_byte_mark = 0xE0; /* 11100000 */
    }
    else if (codepoint <= 0x10FFFF)
    {
        /* four bytes, encoding 1110xxxx 10xxxxxx 10xxxxxx 10xxxxxx */
        utf8_length = 4;
        first_byte_mark = 0xF0; /* 11110000 */
    }
    else
    {
        /* invalid unicode codepoint */
        goto fail;
    }

    /* encode as utf8 */
    for (utf8_position = (unsigned char)(utf8_length - 1); utf8_position > 0; utf8_position--)
    {
        /* 10xxxxxx */
        (*output_pointer)[utf8_position] = (unsigned char)((codepoint | 0x80) & 0xBF);
        codepoint >>= 6;
    }
    /* encode first byte */
    if (utf8_length > 1)
    {
        (*output_pointer)[0] = (unsigned char)((codepoint | first_byte_mark) & 0xFF);
    }
    else
    {
        (*output_pointer)[0] = (unsigned char)(codepoint & 0x7F);
    }

    *output_pointer += utf8_length;

    return sequence_length;

fail:
    return 0;
}

/* Parse the input text into an unescaped cinput, and populate item. */
static kJSON_bool parse_string(kJSON * const item, parse_buffer * const input_buffer)
{
    const unsigned char *input_pointer = buffer_at_offset(input_buffer) + 1;
    const unsigned char *input_end = buffer_at_offset(input_buffer) + 1;
    unsigned char *output_pointer = NULL;
    unsigned char *output = NULL;

    /* not a string */
    if (buffer_at_offset(input_buffer)[0] != '\"')
    {
        goto fail;
    }

    {
        /* calculate approximate size of the output (overestimate) */
        size_t allocation_length = 0;
        size_t skipped_bytes = 0;
        while (((size_t)(input_end - input_buffer->content) < input_buffer->length) && (*input_end != '\"'))
        {
            /* is escape sequence */
            if (input_end[0] == '\\')
            {
                if ((size_t)(input_end + 1 - input_buffer->content) >= input_buffer->length)
                {
                    /* prevent buffer overflow when last input character is a backslash */
                    goto fail;
                }
                skipped_bytes++;
                input_end++;
            }
            input_end++;
        }
        if (((size_t)(input_end - input_buffer->content) >= input_buffer->length) || (*input_end != '\"'))
        {
            goto fail; /* string ended unexpectedly */
        }

        /* This is at most how much we need for the output */
        allocation_length = (size_t) (input_end - buffer_at_offset(input_buffer)) - skipped_bytes;
        output = (unsigned char*)input_buffer->hooks.allocate(allocation_length + sizeof(""));
        if (output == NULL)
        {
            goto fail; /* allocation failure */
        }
    }

    output_pointer = output;
    /* loop through the string literal */
    while (input_pointer < input_end)
    {
        if (*input_pointer != '\\')
        {
            *output_pointer++ = *input_pointer++;
        }
        /* escape sequence */
        else
        {
            unsigned char sequence_length = 2;
            if ((input_end - input_pointer) < 1)
            {
                goto fail;
            }

            switch (input_pointer[1])
            {
                case 'b':
                    *output_pointer++ = '\b';
                    break;
                case 'f':
                    *output_pointer++ = '\f';
                    break;
                case 'n':
                    *output_pointer++ = '\n';
                    break;
                case 'r':
                    *output_pointer++ = '\r';
                    break;
                case 't':
                    *output_pointer++ = '\t';
                    break;
                case '\"':
                case '\\':
                case '/':
                    *output_pointer++ = input_pointer[1];
                    break;

                /* UTF-16 literal */
                case 'u':
                    sequence_length = utf16_literal_to_utf8(input_pointer, input_end, &output_pointer);
                    if (sequence_length == 0)
                    {
                        /* failed to convert UTF16-literal to UTF-8 */
                        goto fail;
                    }
                    break;

                default:
                    goto fail;
            }
            input_pointer += sequence_length;
        }
    }

    /* zero terminate the output */
    *output_pointer = '\0';

    item->type = kJSON_String;
    item->valuestring = (char*)output;

    input_buffer->offset = (size_t) (input_end - input_buffer->content);
    input_buffer->offset++;

    return CJSON_TRUE;

fail:
    if (output != NULL)
    {
        input_buffer->hooks.deallocate(output);
    }

    if (input_pointer != NULL)
    {
        input_buffer->offset = (size_t)(input_pointer - input_buffer->content);
    }

    return CJSON_FALSE;
}

/* Render the cstring provided to an escaped version that can be printed. */
static kJSON_bool print_string_ptr(const unsigned char * const input, printbuffer * const output_buffer)
{
    const unsigned char *input_pointer = NULL;
    unsigned char *output = NULL;
    unsigned char *output_pointer = NULL;
    size_t output_length = 0;
    /* numbers of additional characters needed for escaping */
    size_t escape_characters = 0;

    if (output_buffer == NULL)
    {
        return CJSON_FALSE;
    }

    /* empty string */
    if (input == NULL)
    {
        output = ensure(output_buffer, sizeof("\"\""));
        if (output == NULL)
        {
            return CJSON_FALSE;
        }
        strcpy((char*)output, "\"\"");

        return CJSON_TRUE;
    }

    /* set "flag" to 1 if something needs to be escaped */
    for (input_pointer = input; *input_pointer; input_pointer++)
    {
        switch (*input_pointer)
        {
            case '\"':
            case '\\':
            case '\b':
            case '\f':
            case '\n':
            case '\r':
            case '\t':
                /* one character escape sequence */
                escape_characters++;
                break;
            default:
                if (*input_pointer < 32)
                {
                    /* UTF-16 escape sequence uXXXX */
                    escape_characters += 5;
                }
                break;
        }
    }
    output_length = (size_t)(input_pointer - input) + escape_characters;

    output = ensure(output_buffer, output_length + sizeof("\"\""));
    if (output == NULL)
    {
        return CJSON_FALSE;
    }

    /* no characters have to be escaped */
    if (escape_characters == 0)
    {
        output[0] = '\"';
        memcpy(output + 1, input, output_length);
        output[output_length + 1] = '\"';
        output[output_length + 2] = '\0';

        return CJSON_TRUE;
    }

    output[0] = '\"';
    output_pointer = output + 1;
    /* copy the string */
    for (input_pointer = input; *input_pointer != '\0'; (void)input_pointer++, output_pointer++)
    {
        if ((*input_pointer > 31) && (*input_pointer != '\"') && (*input_pointer != '\\'))
        {
            /* normal character, copy */
            *output_pointer = *input_pointer;
        }
        else
        {
            /* character needs to be escaped */
            *output_pointer++ = '\\';
            switch (*input_pointer)
            {
                case '\\':
                    *output_pointer = '\\';
                    break;
                case '\"':
                    *output_pointer = '\"';
                    break;
                case '\b':
                    *output_pointer = 'b';
                    break;
                case '\f':
                    *output_pointer = 'f';
                    break;
                case '\n':
                    *output_pointer = 'n';
                    break;
                case '\r':
                    *output_pointer = 'r';
                    break;
                case '\t':
                    *output_pointer = 't';
                    break;
                default:
                    /* escape and print as unicode codepoint */
                    sprintf((char*)output_pointer, "u%04x", *input_pointer);
                    output_pointer += 4;
                    break;
            }
        }
    }
    output[output_length + 1] = '\"';
    output[output_length + 2] = '\0';

    return CJSON_TRUE;
}

/* Invoke print_string_ptr (which is useful) on an item. */
static kJSON_bool print_string(const kJSON * const item, printbuffer * const p)
{
    return print_string_ptr((unsigned char*)item->valuestring, p);
}

/* Predeclare these prototypes. */
static kJSON_bool parse_value(kJSON * const item, parse_buffer * const input_buffer);
static kJSON_bool print_value(const kJSON * const item, printbuffer * const output_buffer);
static kJSON_bool parse_array(kJSON * const item, parse_buffer * const input_buffer);
static kJSON_bool print_array(const kJSON * const item, printbuffer * const output_buffer);
static kJSON_bool parse_object(kJSON * const item, parse_buffer * const input_buffer);
static kJSON_bool print_object(const kJSON * const item, printbuffer * const output_buffer);

/* Utility to jump whitespace and cr/lf */
static parse_buffer *buffer_skip_whitespace(parse_buffer * const buffer)
{
    if ((buffer == NULL) || (buffer->content == NULL))
    {
        return NULL;
    }

    while (can_access_at_index(buffer, 0) && (buffer_at_offset(buffer)[0] <= 32))
    {
       buffer->offset++;
    }

    if (buffer->offset == buffer->length)
    {
        buffer->offset--;
    }

    return buffer;
}

/* skip the UTF-8 BOM (byte order mark) if it is at the beginning of a buffer */
static parse_buffer *skip_utf8_bom(parse_buffer * const buffer)
{
    if ((buffer == NULL) || (buffer->content == NULL) || (buffer->offset != 0))
    {
        return NULL;
    }

    if (can_access_at_index(buffer, 4) && (strncmp((const char*)buffer_at_offset(buffer), "\xEF\xBB\xBF", 3) == 0))
    {
        buffer->offset += 3;
    }

    return buffer;
}

/* Parse an object - create a new root, and populate. */
CJSON_PUBLIC(kJSON *) kJSON_ParseWithOpts(const char *value, const char **return_parse_end, kJSON_bool require_null_terminated)
{
    parse_buffer buffer = { 0, 0, 0, 0, { 0, 0, 0 } };
    kJSON *item = NULL;

    /* reset error position */
    global_error.json = NULL;
    global_error.position = 0;

    if (value == NULL)
    {
        goto fail;
    }

    buffer.content = (const unsigned char*)value;
    buffer.length = strlen((const char*)value) + sizeof("");
    buffer.offset = 0;
    buffer.hooks = global_hooks;

    item = kJSON_New_Item(&global_hooks);
    if (item == NULL) /* memory fail */
    {
        goto fail;
    }

    if (!parse_value(item, buffer_skip_whitespace(skip_utf8_bom(&buffer))))
    {
        /* parse failure. ep is set. */
        goto fail;
    }

    /* if we require null-terminated JSON without appended garbage, skip and then check for a null terminator */
    if (require_null_terminated)
    {
        buffer_skip_whitespace(&buffer);
        if ((buffer.offset >= buffer.length) || buffer_at_offset(&buffer)[0] != '\0')
        {
            goto fail;
        }
    }
    if (return_parse_end)
    {
        *return_parse_end = (const char*)buffer_at_offset(&buffer);
    }

    return item;

fail:
    if (item != NULL)
    {
        kJSON_Delete(item);
    }

    if (value != NULL)
    {
        error local_error;
        local_error.json = (const unsigned char*)value;
        local_error.position = 0;

        if (buffer.offset < buffer.length)
        {
            local_error.position = buffer.offset;
        }
        else if (buffer.length > 0)
        {
            local_error.position = buffer.length - 1;
        }

        if (return_parse_end != NULL)
        {
            *return_parse_end = (const char*)local_error.json + local_error.position;
        }

        global_error = local_error;
    }

    return NULL;
}

/* Default options for kJSON_Parse */
CJSON_PUBLIC(kJSON *) kJSON_Parse(const char *value)
{
    return kJSON_ParseWithOpts(value, 0, 0);
}

#define cjson_min(a, b) ((a < b) ? a : b)

static unsigned char *print(const kJSON * const item, kJSON_bool format, const internal_hooks * const hooks)
{
    static const size_t default_buffer_size = 256;
    printbuffer buffer[1];
    unsigned char *printed = NULL;

    memset(buffer, 0, sizeof(buffer));

    /* create buffer */
    buffer->buffer = (unsigned char*) hooks->allocate(default_buffer_size);
    buffer->length = default_buffer_size;
    buffer->format = format;
    buffer->hooks = *hooks;
    if (buffer->buffer == NULL)
    {
        goto fail;
    }

    /* print the value */
    if (!print_value(item, buffer))
    {
        goto fail;
    }
    update_offset(buffer);

    /* check if reallocate is available */
    if (hooks->reallocate != NULL)
    {
        printed = (unsigned char*) hooks->reallocate(buffer->buffer, buffer->offset + 1);
        buffer->buffer = NULL;
        if (printed == NULL) {
            goto fail;
        }
    }
    else /* otherwise copy the JSON over to a new buffer */
    {
        printed = (unsigned char*) hooks->allocate(buffer->offset + 1);
        if (printed == NULL)
        {
            goto fail;
        }
        memcpy(printed, buffer->buffer, cjson_min(buffer->length, buffer->offset + 1));
        printed[buffer->offset] = '\0'; /* just to be sure */

        /* free the buffer */
        hooks->deallocate(buffer->buffer);
    }

    return printed;

fail:
    if (buffer->buffer != NULL)
    {
        hooks->deallocate(buffer->buffer);
    }

    if (printed != NULL)
    {
        hooks->deallocate(printed);
    }

    return NULL;
}

/* Render a kJSON item/entity/structure to text. */
CJSON_PUBLIC(char *) kJSON_Print(const kJSON *item)
{
    return (char*)print(item, CJSON_TRUE, &global_hooks);
}

CJSON_PUBLIC(char *) kJSON_PrintUnformatted(const kJSON *item)
{
    return (char*)print(item, CJSON_FALSE, &global_hooks);
}

CJSON_PUBLIC(char *) kJSON_PrintBuffered(const kJSON *item, int prebuffer, kJSON_bool fmt)
{
    printbuffer p = { 0, 0, 0, 0, 0, 0, { 0, 0, 0 } };

    if (prebuffer < 0)
    {
        return NULL;
    }

    p.buffer = (unsigned char*)global_hooks.allocate((size_t)prebuffer);
    if (!p.buffer)
    {
        return NULL;
    }

    p.length = (size_t)prebuffer;
    p.offset = 0;
    p.noalloc = CJSON_FALSE;
    p.format = fmt;
    p.hooks = global_hooks;

    if (!print_value(item, &p))
    {
        global_hooks.deallocate(p.buffer);
        return NULL;
    }

    return (char*)p.buffer;
}

CJSON_PUBLIC(kJSON_bool) kJSON_PrintPreallocated(kJSON *item, char *buf, const int len, const kJSON_bool fmt)
{
    printbuffer p = { 0, 0, 0, 0, 0, 0, { 0, 0, 0 } };

    if ((len < 0) || (buf == NULL))
    {
        return CJSON_FALSE;
    }

    p.buffer = (unsigned char*)buf;
    p.length = (size_t)len;
    p.offset = 0;
    p.noalloc = CJSON_TRUE;
    p.format = fmt;
    p.hooks = global_hooks;

    return print_value(item, &p);
}

/* Parser core - when encountering text, process appropriately. */
static kJSON_bool parse_value(kJSON * const item, parse_buffer * const input_buffer)
{
    if ((input_buffer == NULL) || (input_buffer->content == NULL))
    {
        return CJSON_FALSE; /* no input */
    }

    /* parse the different types of values */
    /* null */
    if (can_read(input_buffer, 4) && (strncmp((const char*)buffer_at_offset(input_buffer), "null", 4) == 0))
    {
        item->type = kJSON_NULL;
        input_buffer->offset += 4;
        return CJSON_TRUE;
    }
    /* CJSON_FALSE */
    if (can_read(input_buffer, 5) && (strncmp((const char*)buffer_at_offset(input_buffer), "false", 5) == 0))
    {
        item->type = kJSON_False;
        input_buffer->offset += 5;
        return CJSON_TRUE;
    }
    /* CJSON_TRUE */
    if (can_read(input_buffer, 4) && (strncmp((const char*)buffer_at_offset(input_buffer), "true", 4) == 0))
    {
        item->type = kJSON_True;
        item->valueint = 1;
        input_buffer->offset += 4;
        return CJSON_TRUE;
    }
    /* string */
    if (can_access_at_index(input_buffer, 0) && (buffer_at_offset(input_buffer)[0] == '\"'))
    {
        return parse_string(item, input_buffer);
    }
    /* number */
    if (can_access_at_index(input_buffer, 0) && ((buffer_at_offset(input_buffer)[0] == '-') || ((buffer_at_offset(input_buffer)[0] >= '0') && (buffer_at_offset(input_buffer)[0] <= '9'))))
    {
        return parse_number(item, input_buffer);
    }
    /* array */
    if (can_access_at_index(input_buffer, 0) && (buffer_at_offset(input_buffer)[0] == '['))
    {
        return parse_array(item, input_buffer);
    }
    /* object */
    if (can_access_at_index(input_buffer, 0) && (buffer_at_offset(input_buffer)[0] == '{'))
    {
        return parse_object(item, input_buffer);
    }

    return CJSON_FALSE;
}

/* Render a value to text. */
static kJSON_bool print_value(const kJSON * const item, printbuffer * const output_buffer)
{
    unsigned char *output = NULL;

    if ((item == NULL) || (output_buffer == NULL))
    {
        return CJSON_FALSE;
    }

    switch ((item->type) & 0xFF)
    {
        case kJSON_NULL:
            output = ensure(output_buffer, 5);
            if (output == NULL)
            {
                return CJSON_FALSE;
            }
            strcpy((char*)output, "null");
            return CJSON_TRUE;

        case kJSON_False:
            output = ensure(output_buffer, 6);
            if (output == NULL)
            {
                return CJSON_FALSE;
            }
            strcpy((char*)output, "false");
            return CJSON_TRUE;

        case kJSON_True:
            output = ensure(output_buffer, 5);
            if (output == NULL)
            {
                return CJSON_FALSE;
            }
            strcpy((char*)output, "true");
            return CJSON_TRUE;

        case kJSON_Number:
            return print_number(item, output_buffer);

        case kJSON_Raw:
        {
            size_t raw_length = 0;
            if (item->valuestring == NULL)
            {
                return CJSON_FALSE;
            }

            raw_length = strlen(item->valuestring) + sizeof("");
            output = ensure(output_buffer, raw_length);
            if (output == NULL)
            {
                return CJSON_FALSE;
            }
            memcpy(output, item->valuestring, raw_length);
            return CJSON_TRUE;
        }

        case kJSON_String:
            return print_string(item, output_buffer);

        case kJSON_Array:
            return print_array(item, output_buffer);

        case kJSON_Object:
            return print_object(item, output_buffer);

        default:
            return CJSON_FALSE;
    }
}

/* Build an array from input text. */
static kJSON_bool parse_array(kJSON * const item, parse_buffer * const input_buffer)
{
    kJSON *head = NULL; /* head of the linked list */
    kJSON *current_item = NULL;

    if (input_buffer->depth >= CJSON_NESTING_LIMIT)
    {
        return CJSON_FALSE; /* to deeply nested */
    }
    input_buffer->depth++;

    if (buffer_at_offset(input_buffer)[0] != '[')
    {
        /* not an array */
        goto fail;
    }

    input_buffer->offset++;
    buffer_skip_whitespace(input_buffer);
    if (can_access_at_index(input_buffer, 0) && (buffer_at_offset(input_buffer)[0] == ']'))
    {
        /* empty array */
        goto success;
    }

    /* check if we skipped to the end of the buffer */
    if (cannot_access_at_index(input_buffer, 0))
    {
        input_buffer->offset--;
        goto fail;
    }

    /* step back to character in front of the first element */
    input_buffer->offset--;
    /* loop through the comma separated array elements */
    do
    {
        /* allocate next item */
        kJSON *new_item = kJSON_New_Item(&(input_buffer->hooks));
        if (new_item == NULL)
        {
            goto fail; /* allocation failure */
        }

        /* attach next item to list */
        if (head == NULL)
        {
            /* start the linked list */
            current_item = head = new_item;
        }
        else
        {
            /* add to the end and advance */
            current_item->next = new_item;
            new_item->prev = current_item;
            current_item = new_item;
        }

        /* parse next value */
        input_buffer->offset++;
        buffer_skip_whitespace(input_buffer);
        if (!parse_value(current_item, input_buffer))
        {
            goto fail; /* failed to parse value */
        }
        buffer_skip_whitespace(input_buffer);
    }
    while (can_access_at_index(input_buffer, 0) && (buffer_at_offset(input_buffer)[0] == ','));

    if (cannot_access_at_index(input_buffer, 0) || buffer_at_offset(input_buffer)[0] != ']')
    {
        goto fail; /* expected end of array */
    }

success:
    input_buffer->depth--;

    item->type = kJSON_Array;
    item->child = head;

    input_buffer->offset++;

    return CJSON_TRUE;

fail:
    if (head != NULL)
    {
        kJSON_Delete(head);
    }

    return CJSON_FALSE;
}

/* Render an array to text */
static kJSON_bool print_array(const kJSON * const item, printbuffer * const output_buffer)
{
    unsigned char *output_pointer = NULL;
    size_t length = 0;
    kJSON *current_element = item->child;

    if (output_buffer == NULL)
    {
        return CJSON_FALSE;
    }

    /* Compose the output array. */
    /* opening square bracket */
    output_pointer = ensure(output_buffer, 1);
    if (output_pointer == NULL)
    {
        return CJSON_FALSE;
    }

    *output_pointer = '[';
    output_buffer->offset++;
    output_buffer->depth++;

    while (current_element != NULL)
    {
        if (!print_value(current_element, output_buffer))
        {
            return CJSON_FALSE;
        }
        update_offset(output_buffer);
        if (current_element->next)
        {
            length = (size_t) (output_buffer->format ? 2 : 1);
            output_pointer = ensure(output_buffer, length + 1);
            if (output_pointer == NULL)
            {
                return CJSON_FALSE;
            }
            *output_pointer++ = ',';
            if(output_buffer->format)
            {
                *output_pointer++ = ' ';
            }
            *output_pointer = '\0';
            output_buffer->offset += length;
        }
        current_element = current_element->next;
    }

    output_pointer = ensure(output_buffer, 2);
    if (output_pointer == NULL)
    {
        return CJSON_FALSE;
    }
    *output_pointer++ = ']';
    *output_pointer = '\0';
    output_buffer->depth--;

    return CJSON_TRUE;
}

/* Build an object from the text. */
static kJSON_bool parse_object(kJSON * const item, parse_buffer * const input_buffer)
{
    kJSON *head = NULL; /* linked list head */
    kJSON *current_item = NULL;

    if (input_buffer->depth >= CJSON_NESTING_LIMIT)
    {
        return CJSON_FALSE; /* to deeply nested */
    }
    input_buffer->depth++;

    if (cannot_access_at_index(input_buffer, 0) || (buffer_at_offset(input_buffer)[0] != '{'))
    {
        goto fail; /* not an object */
    }

    input_buffer->offset++;
    buffer_skip_whitespace(input_buffer);
    if (can_access_at_index(input_buffer, 0) && (buffer_at_offset(input_buffer)[0] == '}'))
    {
        goto success; /* empty object */
    }

    /* check if we skipped to the end of the buffer */
    if (cannot_access_at_index(input_buffer, 0))
    {
        input_buffer->offset--;
        goto fail;
    }

    /* step back to character in front of the first element */
    input_buffer->offset--;
    /* loop through the comma separated array elements */
    do
    {
        /* allocate next item */
        kJSON *new_item = kJSON_New_Item(&(input_buffer->hooks));
        if (new_item == NULL)
        {
            goto fail; /* allocation failure */
        }

        /* attach next item to list */
        if (head == NULL)
        {
            /* start the linked list */
            current_item = head = new_item;
        }
        else
        {
            /* add to the end and advance */
            current_item->next = new_item;
            new_item->prev = current_item;
            current_item = new_item;
        }

        /* parse the name of the child */
        input_buffer->offset++;
        buffer_skip_whitespace(input_buffer);
        if (!parse_string(current_item, input_buffer))
        {
            goto fail; /* faile to parse name */
        }
        buffer_skip_whitespace(input_buffer);

        /* swap valuestring and string, because we parsed the name */
        current_item->string = current_item->valuestring;
        current_item->valuestring = NULL;

        if (cannot_access_at_index(input_buffer, 0) || (buffer_at_offset(input_buffer)[0] != ':'))
        {
            goto fail; /* invalid object */
        }

        /* parse the value */
        input_buffer->offset++;
        buffer_skip_whitespace(input_buffer);
        if (!parse_value(current_item, input_buffer))
        {
            goto fail; /* failed to parse value */
        }
        buffer_skip_whitespace(input_buffer);
    }
    while (can_access_at_index(input_buffer, 0) && (buffer_at_offset(input_buffer)[0] == ','));

    if (cannot_access_at_index(input_buffer, 0) || (buffer_at_offset(input_buffer)[0] != '}'))
    {
        goto fail; /* expected end of object */
    }

success:
    input_buffer->depth--;

    item->type = kJSON_Object;
    item->child = head;

    input_buffer->offset++;
    return CJSON_TRUE;

fail:
    if (head != NULL)
    {
        kJSON_Delete(head);
    }

    return CJSON_FALSE;
}

/* Render an object to text. */
static kJSON_bool print_object(const kJSON * const item, printbuffer * const output_buffer)
{
    unsigned char *output_pointer = NULL;
    size_t length = 0;
    kJSON *current_item = item->child;

    if (output_buffer == NULL)
    {
        return CJSON_FALSE;
    }

    /* Compose the output: */
    length = (size_t) (output_buffer->format ? 2 : 1); /* fmt: {\n */
    output_pointer = ensure(output_buffer, length + 1);
    if (output_pointer == NULL)
    {
        return CJSON_FALSE;
    }

    *output_pointer++ = '{';
    output_buffer->depth++;
    if (output_buffer->format)
    {
        *output_pointer++ = '\n';
    }
    output_buffer->offset += length;

    while (current_item)
    {
        if (output_buffer->format)
        {
            size_t i;
            output_pointer = ensure(output_buffer, output_buffer->depth);
            if (output_pointer == NULL)
            {
                return CJSON_FALSE;
            }
            for (i = 0; i < output_buffer->depth; i++)
            {
                *output_pointer++ = '\t';
            }
            output_buffer->offset += output_buffer->depth;
        }

        /* print key */
        if (!print_string_ptr((unsigned char*)current_item->string, output_buffer))
        {
            return CJSON_FALSE;
        }
        update_offset(output_buffer);

        length = (size_t) (output_buffer->format ? 2 : 1);
        output_pointer = ensure(output_buffer, length);
        if (output_pointer == NULL)
        {
            return CJSON_FALSE;
        }
        *output_pointer++ = ':';
        if (output_buffer->format)
        {
            *output_pointer++ = '\t';
        }
        output_buffer->offset += length;

        /* print value */
        if (!print_value(current_item, output_buffer))
        {
            return CJSON_FALSE;
        }
        update_offset(output_buffer);

        /* print comma if not last */
        length = (size_t) ((output_buffer->format ? 1 : 0) + (current_item->next ? 1 : 0));
        output_pointer = ensure(output_buffer, length + 1);
        if (output_pointer == NULL)
        {
            return CJSON_FALSE;
        }
        if (current_item->next)
        {
            *output_pointer++ = ',';
        }

        if (output_buffer->format)
        {
            *output_pointer++ = '\n';
        }
        *output_pointer = '\0';
        output_buffer->offset += length;

        current_item = current_item->next;
    }

    output_pointer = ensure(output_buffer, output_buffer->format ? (output_buffer->depth + 1) : 2);
    if (output_pointer == NULL)
    {
        return CJSON_FALSE;
    }
    if (output_buffer->format)
    {
        size_t i;
        for (i = 0; i < (output_buffer->depth - 1); i++)
        {
            *output_pointer++ = '\t';
        }
    }
    *output_pointer++ = '}';
    *output_pointer = '\0';
    output_buffer->depth--;

    return CJSON_TRUE;
}

/* Get Array size/item / object item. */
CJSON_PUBLIC(int) kJSON_GetArraySize(const kJSON *array)
{
    kJSON *child = NULL;
    size_t size = 0;

    if (array == NULL)
    {
        return 0;
    }

    child = array->child;

    while(child != NULL)
    {
        size++;
        child = child->next;
    }

    return (int)size;
}

static kJSON* get_array_item(const kJSON *array, size_t index)
{
    kJSON *current_child = NULL;

    if (array == NULL)
    {
        return NULL;
    }

    current_child = array->child;
    while ((current_child != NULL) && (index > 0))
    {
        index--;
        current_child = current_child->next;
    }

    return current_child;
}

CJSON_PUBLIC(kJSON *) kJSON_GetArrayItem(const kJSON *array, int index)
{
    if (index < 0)
    {
        return NULL;
    }

    return get_array_item(array, (size_t)index);
}

static kJSON *get_object_item(const kJSON * const object, const char * const name, const kJSON_bool case_sensitive)
{
    kJSON *current_element = NULL;

    if ((object == NULL) || (name == NULL))
    {
        return NULL;
    }

    current_element = object->child;
    if (case_sensitive)
    {
        while ((current_element != NULL) && (strcmp(name, current_element->string) != 0))
        {
            current_element = current_element->next;
        }
    }
    else
    {
        while ((current_element != NULL) && (case_insensitive_strcmp((const unsigned char*)name, (const unsigned char*)(current_element->string)) != 0))
        {
            current_element = current_element->next;
        }
    }

    return current_element;
}

CJSON_PUBLIC(kJSON *) kJSON_GetObjectItem(const kJSON * const object, const char * const string)
{
    return get_object_item(object, string, CJSON_FALSE);
}

CJSON_PUBLIC(kJSON *) kJSON_GetObjectItemCaseSensitive(const kJSON * const object, const char * const string)
{
    return get_object_item(object, string, CJSON_TRUE);
}

CJSON_PUBLIC(kJSON_bool) kJSON_HasObjectItem(const kJSON *object, const char *string)
{
    return kJSON_GetObjectItem(object, string) ? 1 : 0;
}

/* Utility for array list handling. */
static void suffix_object(kJSON *prev, kJSON *item)
{
    prev->next = item;
    item->prev = prev;
}

/* Utility for handling references. */
static kJSON *create_reference(const kJSON *item, const internal_hooks * const hooks)
{
    kJSON *reference = NULL;
    if (item == NULL)
    {
        return NULL;
    }

    reference = kJSON_New_Item(hooks);
    if (reference == NULL)
    {
        return NULL;
    }

    memcpy(reference, item, sizeof(kJSON));
    reference->string = NULL;
    reference->type |= kJSON_IsReference;
    reference->next = reference->prev = NULL;
    return reference;
}

static kJSON_bool add_item_to_array(kJSON *array, kJSON *item)
{
    kJSON *child = NULL;

    if ((item == NULL) || (array == NULL))
    {
        return CJSON_FALSE;
    }

    child = array->child;

    if (child == NULL)
    {
        /* list is empty, start new one */
        array->child = item;
    }
    else
    {
        /* append to the end */
        while (child->next)
        {
            child = child->next;
        }
        suffix_object(child, item);
    }

    return CJSON_TRUE;
}

/* Add item to array/object. */
CJSON_PUBLIC(void) kJSON_AddItemToArray(kJSON *array, kJSON *item)
{
    add_item_to_array(array, item);
}

#if defined(__clang__) || (defined(__GNUC__)  && ((__GNUC__ > 4) || ((__GNUC__ == 4) && (__GNUC_MINOR__ > 5))))
    #pragma GCC diagnostic push
#endif
#ifdef __GNUC__
#pragma GCC diagnostic ignored "-Wcast-qual"
#endif
/* helper function to cast away const */
static void* cast_away_const(const void* string)
{
    return (void*)string;
}
#if defined(__clang__) || (defined(__GNUC__)  && ((__GNUC__ > 4) || ((__GNUC__ == 4) && (__GNUC_MINOR__ > 5))))
    #pragma GCC diagnostic pop
#endif


static kJSON_bool add_item_to_object(kJSON * const object, const char * const string, kJSON * const item, const internal_hooks * const hooks, const kJSON_bool constant_key)
{
    char *new_key = NULL;
    int new_type = kJSON_Invalid;

    if ((object == NULL) || (string == NULL) || (item == NULL))
    {
        return CJSON_FALSE;
    }

    if (constant_key)
    {
        new_key = (char*)cast_away_const(string);
        new_type = item->type | kJSON_StringIsConst;
    }
    else
    {
        new_key = (char*)kJSON_strdup((const unsigned char*)string, hooks);
        if (new_key == NULL)
        {
            return CJSON_FALSE;
        }

        new_type = item->type & ~kJSON_StringIsConst;
    }

    if (!(item->type & kJSON_StringIsConst) && (item->string != NULL))
    {
        hooks->deallocate(item->string);
    }

    item->string = new_key;
    item->type = new_type;

    return add_item_to_array(object, item);
}

CJSON_PUBLIC(void) kJSON_AddItemToObject(kJSON *object, const char *string, kJSON *item)
{
    add_item_to_object(object, string, item, &global_hooks, CJSON_FALSE);
}

/* Add an item to an object with constant string as key */
CJSON_PUBLIC(void) kJSON_AddItemToObjectCS(kJSON *object, const char *string, kJSON *item)
{
    add_item_to_object(object, string, item, &global_hooks, CJSON_TRUE);
}

CJSON_PUBLIC(void) kJSON_AddItemReferenceToArray(kJSON *array, kJSON *item)
{
    if (array == NULL)
    {
        return;
    }

    add_item_to_array(array, create_reference(item, &global_hooks));
}

CJSON_PUBLIC(void) kJSON_AddItemReferenceToObject(kJSON *object, const char *string, kJSON *item)
{
    if ((object == NULL) || (string == NULL))
    {
        return;
    }

    add_item_to_object(object, string, create_reference(item, &global_hooks), &global_hooks, CJSON_FALSE);
}

CJSON_PUBLIC(kJSON*) kJSON_AddNullToObject(kJSON * const object, const char * const name)
{
    kJSON *null = kJSON_CreateNull();
    if (add_item_to_object(object, name, null, &global_hooks, CJSON_FALSE))
    {
        return null;
    }

    kJSON_Delete(null);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddTrueToObject(kJSON * const object, const char * const name)
{
    kJSON *CJSON_TRUE_item = kJSON_CreateTrue();
    if (add_item_to_object(object, name, CJSON_TRUE_item, &global_hooks, CJSON_FALSE))
    {
        return CJSON_TRUE_item;
    }

    kJSON_Delete(CJSON_TRUE_item);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddFalseToObject(kJSON * const object, const char * const name)
{
    kJSON *CJSON_FALSE_item = kJSON_CreateFalse();
    if (add_item_to_object(object, name, CJSON_FALSE_item, &global_hooks, CJSON_FALSE))
    {
        return CJSON_FALSE_item;
    }

    kJSON_Delete(CJSON_FALSE_item);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddBoolToObject(kJSON * const object, const char * const name, const kJSON_bool boolean)
{
    kJSON *bool_item = kJSON_CreateBool(boolean);
    if (add_item_to_object(object, name, bool_item, &global_hooks, CJSON_FALSE))
    {
        return bool_item;
    }

    kJSON_Delete(bool_item);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddNumberToObject(kJSON * const object, const char * const name, const double number)
{
    kJSON *number_item = kJSON_CreateNumber(number);
    if (add_item_to_object(object, name, number_item, &global_hooks, CJSON_FALSE))
    {
        return number_item;
    }

    kJSON_Delete(number_item);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddStringToObject(kJSON * const object, const char * const name, const char * const string)
{
    kJSON *string_item = kJSON_CreateString(string);
    if (add_item_to_object(object, name, string_item, &global_hooks, CJSON_FALSE))
    {
        return string_item;
    }

    kJSON_Delete(string_item);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddRawToObject(kJSON * const object, const char * const name, const char * const raw)
{
    kJSON *raw_item = kJSON_CreateRaw(raw);
    if (add_item_to_object(object, name, raw_item, &global_hooks, CJSON_FALSE))
    {
        return raw_item;
    }

    kJSON_Delete(raw_item);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddObjectToObject(kJSON * const object, const char * const name)
{
    kJSON *object_item = kJSON_CreateObject();
    if (add_item_to_object(object, name, object_item, &global_hooks, CJSON_FALSE))
    {
        return object_item;
    }

    kJSON_Delete(object_item);
    return NULL;
}

CJSON_PUBLIC(kJSON*) kJSON_AddArrayToObject(kJSON * const object, const char * const name)
{
    kJSON *array = kJSON_CreateArray();
    if (add_item_to_object(object, name, array, &global_hooks, CJSON_FALSE))
    {
        return array;
    }

    kJSON_Delete(array);
    return NULL;
}

CJSON_PUBLIC(kJSON *) kJSON_DetachItemViaPointer(kJSON *parent, kJSON * const item)
{
    if ((parent == NULL) || (item == NULL))
    {
        return NULL;
    }

    if (item->prev != NULL)
    {
        /* not the first element */
        item->prev->next = item->next;
    }
    if (item->next != NULL)
    {
        /* not the last element */
        item->next->prev = item->prev;
    }

    if (item == parent->child)
    {
        /* first element */
        parent->child = item->next;
    }
    /* make sure the detached item doesn't point anywhere anymore */
    item->prev = NULL;
    item->next = NULL;

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_DetachItemFromArray(kJSON *array, int which)
{
    if (which < 0)
    {
        return NULL;
    }

    return kJSON_DetachItemViaPointer(array, get_array_item(array, (size_t)which));
}

CJSON_PUBLIC(void) kJSON_DeleteItemFromArray(kJSON *array, int which)
{
    kJSON_Delete(kJSON_DetachItemFromArray(array, which));
}

CJSON_PUBLIC(kJSON *) kJSON_DetachItemFromObject(kJSON *object, const char *string)
{
    kJSON *to_detach = kJSON_GetObjectItem(object, string);

    return kJSON_DetachItemViaPointer(object, to_detach);
}

CJSON_PUBLIC(kJSON *) kJSON_DetachItemFromObjectCaseSensitive(kJSON *object, const char *string)
{
    kJSON *to_detach = kJSON_GetObjectItemCaseSensitive(object, string);

    return kJSON_DetachItemViaPointer(object, to_detach);
}

CJSON_PUBLIC(void) kJSON_DeleteItemFromObject(kJSON *object, const char *string)
{
    kJSON_Delete(kJSON_DetachItemFromObject(object, string));
}

CJSON_PUBLIC(void) kJSON_DeleteItemFromObjectCaseSensitive(kJSON *object, const char *string)
{
    kJSON_Delete(kJSON_DetachItemFromObjectCaseSensitive(object, string));
}

/* Replace array/object items with new ones. */
CJSON_PUBLIC(void) kJSON_InsertItemInArray(kJSON *array, int which, kJSON *newitem)
{
    kJSON *after_inserted = NULL;

    if (which < 0)
    {
        return;
    }

    after_inserted = get_array_item(array, (size_t)which);
    if (after_inserted == NULL)
    {
        add_item_to_array(array, newitem);
        return;
    }

    newitem->next = after_inserted;
    newitem->prev = after_inserted->prev;
    after_inserted->prev = newitem;
    if (after_inserted == array->child)
    {
        array->child = newitem;
    }
    else
    {
        newitem->prev->next = newitem;
    }
}

CJSON_PUBLIC(kJSON_bool) kJSON_ReplaceItemViaPointer(kJSON * const parent, kJSON * const item, kJSON * replacement)
{
    if ((parent == NULL) || (replacement == NULL) || (item == NULL))
    {
        return CJSON_FALSE;
    }

    if (replacement == item)
    {
        return CJSON_TRUE;
    }

    replacement->next = item->next;
    replacement->prev = item->prev;

    if (replacement->next != NULL)
    {
        replacement->next->prev = replacement;
    }
    if (replacement->prev != NULL)
    {
        replacement->prev->next = replacement;
    }
    if (parent->child == item)
    {
        parent->child = replacement;
    }

    item->next = NULL;
    item->prev = NULL;
    kJSON_Delete(item);

    return CJSON_TRUE;
}

CJSON_PUBLIC(void) kJSON_ReplaceItemInArray(kJSON *array, int which, kJSON *newitem)
{
    if (which < 0)
    {
        return;
    }

    kJSON_ReplaceItemViaPointer(array, get_array_item(array, (size_t)which), newitem);
}

static kJSON_bool replace_item_in_object(kJSON *object, const char *string, kJSON *replacement, kJSON_bool case_sensitive)
{
    if ((replacement == NULL) || (string == NULL))
    {
        return CJSON_FALSE;
    }

    /* replace the name in the replacement */
    if (!(replacement->type & kJSON_StringIsConst) && (replacement->string != NULL))
    {
        kJSON_free(replacement->string);
    }
    replacement->string = (char*)kJSON_strdup((const unsigned char*)string, &global_hooks);
    replacement->type &= ~kJSON_StringIsConst;

    kJSON_ReplaceItemViaPointer(object, get_object_item(object, string, case_sensitive), replacement);

    return CJSON_TRUE;
}

CJSON_PUBLIC(void) kJSON_ReplaceItemInObject(kJSON *object, const char *string, kJSON *newitem)
{
    replace_item_in_object(object, string, newitem, CJSON_FALSE);
}

CJSON_PUBLIC(void) kJSON_ReplaceItemInObjectCaseSensitive(kJSON *object, const char *string, kJSON *newitem)
{
    replace_item_in_object(object, string, newitem, CJSON_TRUE);
}

/* Create basic types: */
CJSON_PUBLIC(kJSON *) kJSON_CreateNull(void)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type = kJSON_NULL;
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateTrue(void)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type = kJSON_True;
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateFalse(void)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type = kJSON_False;
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateBool(kJSON_bool b)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type = b ? kJSON_True : kJSON_False;
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateNumber(double num)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type = kJSON_Number;
        item->valuedouble = num;

        /* use saturation in case of overflow */
        if (num >= INT_MAX)
        {
            item->valueint = INT_MAX;
        }
        else if (num <= INT_MIN)
        {
            item->valueint = INT_MIN;
        }
        else
        {
            item->valueint = (int)num;
        }
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateString(const char *string)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type = kJSON_String;
        item->valuestring = (char*)kJSON_strdup((const unsigned char*)string, &global_hooks);
        if(!item->valuestring)
        {
            kJSON_Delete(item);
            return NULL;
        }
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateStringReference(const char *string)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if (item != NULL)
    {
        item->type = kJSON_String | kJSON_IsReference;
        item->valuestring = (char*)cast_away_const(string);
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateObjectReference(const kJSON *child)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if (item != NULL) {
        item->type = kJSON_Object | kJSON_IsReference;
        item->child = (kJSON*)cast_away_const(child);
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateArrayReference(const kJSON *child) {
    kJSON *item = kJSON_New_Item(&global_hooks);
    if (item != NULL) {
        item->type = kJSON_Array | kJSON_IsReference;
        item->child = (kJSON*)cast_away_const(child);
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateRaw(const char *raw)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type = kJSON_Raw;
        item->valuestring = (char*)kJSON_strdup((const unsigned char*)raw, &global_hooks);
        if(!item->valuestring)
        {
            kJSON_Delete(item);
            return NULL;
        }
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateArray(void)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if(item)
    {
        item->type=kJSON_Array;
    }

    return item;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateObject(void)
{
    kJSON *item = kJSON_New_Item(&global_hooks);
    if (item)
    {
        item->type = kJSON_Object;
    }

    return item;
}

/* Create Arrays: */
CJSON_PUBLIC(kJSON *) kJSON_CreateIntArray(const int *numbers, int count)
{
    size_t i = 0;
    kJSON *n = NULL;
    kJSON *p = NULL;
    kJSON *a = NULL;

    if ((count < 0) || (numbers == NULL))
    {
        return NULL;
    }

    a = kJSON_CreateArray();
    for(i = 0; a && (i < (size_t)count); i++)
    {
        n = kJSON_CreateNumber(numbers[i]);
        if (!n)
        {
            kJSON_Delete(a);
            return NULL;
        }
        if(!i)
        {
            a->child = n;
        }
        else
        {
            suffix_object(p, n);
        }
        p = n;
    }

    return a;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateFloatArray(const float *numbers, int count)
{
    size_t i = 0;
    kJSON *n = NULL;
    kJSON *p = NULL;
    kJSON *a = NULL;

    if ((count < 0) || (numbers == NULL))
    {
        return NULL;
    }

    a = kJSON_CreateArray();

    for(i = 0; a && (i < (size_t)count); i++)
    {
        n = kJSON_CreateNumber((double)numbers[i]);
        if(!n)
        {
            kJSON_Delete(a);
            return NULL;
        }
        if(!i)
        {
            a->child = n;
        }
        else
        {
            suffix_object(p, n);
        }
        p = n;
    }

    return a;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateDoubleArray(const double *numbers, int count)
{
    size_t i = 0;
    kJSON *n = NULL;
    kJSON *p = NULL;
    kJSON *a = NULL;

    if ((count < 0) || (numbers == NULL))
    {
        return NULL;
    }

    a = kJSON_CreateArray();

    for(i = 0;a && (i < (size_t)count); i++)
    {
        n = kJSON_CreateNumber(numbers[i]);
        if(!n)
        {
            kJSON_Delete(a);
            return NULL;
        }
        if(!i)
        {
            a->child = n;
        }
        else
        {
            suffix_object(p, n);
        }
        p = n;
    }

    return a;
}

CJSON_PUBLIC(kJSON *) kJSON_CreateStringArray(const char **strings, int count)
{
    size_t i = 0;
    kJSON *n = NULL;
    kJSON *p = NULL;
    kJSON *a = NULL;

    if ((count < 0) || (strings == NULL))
    {
        return NULL;
    }

    a = kJSON_CreateArray();

    for (i = 0; a && (i < (size_t)count); i++)
    {
        n = kJSON_CreateString(strings[i]);
        if(!n)
        {
            kJSON_Delete(a);
            return NULL;
        }
        if(!i)
        {
            a->child = n;
        }
        else
        {
            suffix_object(p,n);
        }
        p = n;
    }

    return a;
}

/* Duplication */
CJSON_PUBLIC(kJSON *) kJSON_Duplicate(const kJSON *item, kJSON_bool recurse)
{
    kJSON *newitem = NULL;
    kJSON *child = NULL;
    kJSON *next = NULL;
    kJSON *newchild = NULL;

    /* Bail on bad ptr */
    if (!item)
    {
        goto fail;
    }
    /* Create new item */
    newitem = kJSON_New_Item(&global_hooks);
    if (!newitem)
    {
        goto fail;
    }
    /* Copy over all vars */
    newitem->type = item->type & (~kJSON_IsReference);
    newitem->valueint = item->valueint;
    newitem->valuedouble = item->valuedouble;
    if (item->valuestring)
    {
        newitem->valuestring = (char*)kJSON_strdup((unsigned char*)item->valuestring, &global_hooks);
        if (!newitem->valuestring)
        {
            goto fail;
        }
    }
    if (item->string)
    {
        newitem->string = (item->type&kJSON_StringIsConst) ? item->string : (char*)kJSON_strdup((unsigned char*)item->string, &global_hooks);
        if (!newitem->string)
        {
            goto fail;
        }
    }
    /* If non-recursive, then we're done! */
    if (!recurse)
    {
        return newitem;
    }
    /* Walk the ->next chain for the child. */
    child = item->child;
    while (child != NULL)
    {
        newchild = kJSON_Duplicate(child, CJSON_TRUE); /* Duplicate (with recurse) each item in the ->next chain */
        if (!newchild)
        {
            goto fail;
        }
        if (next != NULL)
        {
            /* If newitem->child already set, then crosswire ->prev and ->next and move on */
            next->next = newchild;
            newchild->prev = next;
            next = newchild;
        }
        else
        {
            /* Set newitem->child and move to it */
            newitem->child = newchild;
            next = newchild;
        }
        child = child->next;
    }

    return newitem;

fail:
    if (newitem != NULL)
    {
        kJSON_Delete(newitem);
    }

    return NULL;
}

CJSON_PUBLIC(void) kJSON_Minify(char *json)
{
    unsigned char *into = (unsigned char*)json;

    if (json == NULL)
    {
        return;
    }

    while (*json)
    {
        if (*json == ' ')
        {
            json++;
        }
        else if (*json == '\t')
        {
            /* Whitespace characters. */
            json++;
        }
        else if (*json == '\r')
        {
            json++;
        }
        else if (*json=='\n')
        {
            json++;
        }
        else if ((*json == '/') && (json[1] == '/'))
        {
            /* double-slash comments, to end of line. */
            while (*json && (*json != '\n'))
            {
                json++;
            }
        }
        else if ((*json == '/') && (json[1] == '*'))
        {
            /* multiline comments. */
            while (*json && !((*json == '*') && (json[1] == '/')))
            {
                json++;
            }
            json += 2;
        }
        else if (*json == '\"')
        {
            /* string literals, which are \" sensitive. */
            *into++ = (unsigned char)*json++;
            while (*json && (*json != '\"'))
            {
                if (*json == '\\')
                {
                    *into++ = (unsigned char)*json++;
                }
                *into++ = (unsigned char)*json++;
            }
            *into++ = (unsigned char)*json++;
        }
        else
        {
            /* All other characters. */
            *into++ = (unsigned char)*json++;
        }
    }

    /* and null-terminate. */
    *into = '\0';
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsInvalid(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_Invalid;
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsFalse(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_False;
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsTrue(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xff) == kJSON_True;
}


CJSON_PUBLIC(kJSON_bool) kJSON_IsBool(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & (kJSON_True | kJSON_False)) != 0;
}
CJSON_PUBLIC(kJSON_bool) kJSON_IsNull(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_NULL;
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsNumber(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_Number;
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsString(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_String;
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsArray(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_Array;
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsObject(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_Object;
}

CJSON_PUBLIC(kJSON_bool) kJSON_IsRaw(const kJSON * const item)
{
    if (item == NULL)
    {
        return CJSON_FALSE;
    }

    return (item->type & 0xFF) == kJSON_Raw;
}

CJSON_PUBLIC(kJSON_bool) kJSON_Compare(const kJSON * const a, const kJSON * const b, const kJSON_bool case_sensitive)
{
    if ((a == NULL) || (b == NULL) || ((a->type & 0xFF) != (b->type & 0xFF)) || kJSON_IsInvalid(a))
    {
        return CJSON_FALSE;
    }

    /* check if type is valid */
    switch (a->type & 0xFF)
    {
        case kJSON_False:
        case kJSON_True:
        case kJSON_NULL:
        case kJSON_Number:
        case kJSON_String:
        case kJSON_Raw:
        case kJSON_Array:
        case kJSON_Object:
            break;

        default:
            return CJSON_FALSE;
    }

    /* identical objects are equal */
    if (a == b)
    {
        return CJSON_TRUE;
    }

    switch (a->type & 0xFF)
    {
        /* in these cases and equal type is enough */
        case kJSON_False:
        case kJSON_True:
        case kJSON_NULL:
            return CJSON_TRUE;

        case kJSON_Number:
            if (a->valuedouble == b->valuedouble)
            {
                return CJSON_TRUE;
            }
            return CJSON_FALSE;

        case kJSON_String:
        case kJSON_Raw:
            if ((a->valuestring == NULL) || (b->valuestring == NULL))
            {
                return CJSON_FALSE;
            }
            if (strcmp(a->valuestring, b->valuestring) == 0)
            {
                return CJSON_TRUE;
            }

            return CJSON_FALSE;

        case kJSON_Array:
        {
            kJSON *a_element = a->child;
            kJSON *b_element = b->child;

            for (; (a_element != NULL) && (b_element != NULL);)
            {
                if (!kJSON_Compare(a_element, b_element, case_sensitive))
                {
                    return CJSON_FALSE;
                }

                a_element = a_element->next;
                b_element = b_element->next;
            }

            /* one of the arrays is longer than the other */
            if (a_element != b_element) {
                return CJSON_FALSE;
            }

            return CJSON_TRUE;
        }

        case kJSON_Object:
        {
            kJSON *a_element = NULL;
            kJSON *b_element = NULL;
            kJSON_ArrayForEach(a_element, a)
            {
                /* TODO This has O(n^2) runtime, which is horrible! */
                b_element = get_object_item(b, a_element->string, case_sensitive);
                if (b_element == NULL)
                {
                    return CJSON_FALSE;
                }

                if (!kJSON_Compare(a_element, b_element, case_sensitive))
                {
                    return CJSON_FALSE;
                }
            }

            /* doing this twice, once on a and b to prevent CJSON_TRUE comparison if a subset of b
             * TODO: Do this the proper way, this is just a fix for now */
            kJSON_ArrayForEach(b_element, b)
            {
                a_element = get_object_item(a, b_element->string, case_sensitive);
                if (a_element == NULL)
                {
                    return CJSON_FALSE;
                }

                if (!kJSON_Compare(b_element, a_element, case_sensitive))
                {
                    return CJSON_FALSE;
                }
            }

            return CJSON_TRUE;
        }

        default:
            return CJSON_FALSE;
    }
}

CJSON_PUBLIC(void *) kJSON_malloc(size_t size)
{
    return global_hooks.allocate(size);
}

CJSON_PUBLIC(void) kJSON_free(void *object)
{
    global_hooks.deallocate(object);
}
