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
 *
 * ks_json_check.c -- Validator functions for ks_json.
 *
 */
#include "libks/ks.h"
#ifndef KS_PLAT_WIN
#include <uuid/uuid.h>
#endif
#include "cJSON/cJSON.h"

/* todo move util functions to ks_utils.c */

/*!
  \brief Test for NULL or zero length string
  \param s the string to test
  \return true value if the string is NULL or zero length
*/
_Check_return_ static inline int _zstr(_In_opt_z_ const char *s)
{
	return !s || *s == '\0';
}
#ifdef _PREFAST_
#define zstr(x) (_zstr(x) ? 1 : __analysis_assume(x),0)
#else
#define zstr(x) _zstr(x)
#endif

static ks_bool_t ks_is_number(const char *str)
{
	const char *p;
	ks_bool_t r = KS_TRUE;

	if (*str == '-' || *str == '+') {
		str++;
	}

	for (p = str; p && *p; p++) {
		if (!(*p == '.' || (*p > 47 && *p < 58))) {
			r = KS_FALSE;
			break;
		}
	}

	return r;
}

KS_DECLARE(int) ks_json_check_string_is_not_negative(ks_json_t* item)
{
	return ks_json_type_is_string(item) && ks_is_number(item->valuestring) && atoi(item->valuestring) >= 0;
}

KS_DECLARE(int) ks_json_check_string_is_positive(ks_json_t* item)
{
	return ks_json_type_is_string(item) && ks_is_number(item->valuestring) && atoi(item->valuestring) > 0;
}

KS_DECLARE(int) ks_json_check_string_is_positive_or_neg_one(ks_json_t* item)
{
	return ks_json_type_is_string(item) && ks_is_number(item->valuestring) && (atoi(item->valuestring) > 0 || atoi(item->valuestring) == -1);
}

KS_DECLARE(int) ks_json_check_string_is_decimal_between_zero_and_one(ks_json_t* item)
{
	return ks_json_type_is_string(item) && ks_is_number(item->valuestring) && (atof(item->valuestring) >= 0.0f && atof(item->valuestring) <= 1.0f);
}

KS_DECLARE(int) ks_json_check_number_is_not_negative(ks_json_t* item)
{
	return ks_json_type_is_number(item) && item->valueint >= 0;
}

KS_DECLARE(int) ks_json_check_number_is_positive(ks_json_t* item)
{
	return ks_json_type_is_number(item) && item->valueint > 0;
}

KS_DECLARE(int) ks_json_check_number_is_positive_or_neg_one(ks_json_t* item)
{
	return ks_json_type_is_number(item) && (item->valueint > 0 || item->valueint == -1);
}

KS_DECLARE(int) ks_json_check_number_is_decimal_between_zero_and_one(ks_json_t* item)
{
	return ks_json_type_is_number(item) && (item->valuedouble > 0.0f && item->valueint <= 1.0f);
}

KS_DECLARE(int) ks_json_check_number_is_8_bit_unsigned(ks_json_t *item)
{
	return ks_json_type_is_number(item) && item->valueint >= 0 && item->valueint <= 255;
}

KS_DECLARE(int) ks_json_check_number_is_16_bit_unsigned(ks_json_t *item)
{
	return ks_json_type_is_number(item) && item->valueint >= 0 && item->valueint <= 65535;
}

KS_DECLARE(int) ks_json_check_number_is_ip_port(ks_json_t *item)
{
	return ks_json_type_is_number(item) && item->valueint >= 1 && item->valueint <= 65535;
}

KS_DECLARE(int) ks_json_check_string_is_any_or_empty(ks_json_t* item)
{
	return ks_json_type_is_string(item);
}

KS_DECLARE(int) ks_json_check_string_is_any(ks_json_t* item)
{
	return ks_json_type_is_string(item) && !zstr(item->valuestring);
}

KS_DECLARE(int) ks_json_check_string_is_any_nullable(ks_json_t* item)
{
	return ks_json_type_is_null(item) || ks_json_type_is_string(item);
}

KS_DECLARE(int) ks_json_check_string_starts_with_insensitive(ks_json_t *item, const char *match)
{
	if (!ks_json_type_is_string(item) || zstr(item->valuestring) || zstr(match)) {
		return 0;
	}
	size_t item_len = strlen(item->valuestring);
	size_t match_len = strlen(match);
	if (item_len < match_len) {
		return 0;
	}
	return !strncasecmp(item->valuestring, match, match_len);
}

KS_DECLARE(int) ks_json_check_string_starts_with(ks_json_t *item, const char *match)
{
	if (!ks_json_type_is_string(item) || zstr(item->valuestring) || zstr(match)) {
		return 0;
	}
	size_t item_len = strlen(item->valuestring);
	size_t match_len = strlen(match);
	if (item_len < match_len) {
		return 0;
	}
	return !strncmp(item->valuestring, match, match_len);
}

KS_DECLARE(int) ks_json_check_string_ends_with(ks_json_t *item, const char *match)
{
	if (!ks_json_type_is_string(item) || zstr(item->valuestring) || zstr(match)) {
		return 0;
	}
	size_t item_len = strlen(item->valuestring);
	size_t match_len = strlen(match);
	if (item_len < match_len) {
		return 0;
	}
	return !strncmp(item->valuestring + (item_len - match_len), match, match_len);
}

static int is_dtmf_digit(char digit)
{
	switch (digit) {
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
		case 'A':
		case 'a':
		case 'B':
		case 'b':
		case 'C':
		case 'c':
		case 'D':
		case 'd':
		case '*':
		case '#':
			return 1;
	}
	return 0;
}

KS_DECLARE(int) ks_json_check_string_is_dtmf_digit(ks_json_t* item)
{
	return ks_json_type_is_string(item) && !zstr(item->valuestring) && strlen(item->valuestring) == 1 && is_dtmf_digit(item->valuestring[0]);
}

KS_DECLARE(int) ks_json_check_string_is_dtmf_digits(ks_json_t* item)
{
	int i;
	int len;

	if (!ks_json_type_is_string(item) || zstr(item->valuestring)) {
		return 0;
	}

	len = strlen(item->valuestring);

	for (i = 0; i < len; i++) {
		if (!is_dtmf_digit(item->valuestring[i])) {
			return 0;
		}
	}
	return 1;
}

static int string_matches(const char *value, const char *rule)
{
	if (rule && *rule && value && *value && !strchr(value, ',')) {
		const char *begin = strstr(rule, value);
		const char *end = begin + strlen(value);
		if (!begin) {
			return 0;
		}
		if ((begin == rule || *(begin - 1) == ',') && (*end == ',' || *end == '\0')) {
				return 1;
		}
		/* substring matched... try farther down the string */
		return string_matches(value, end);
	}
	return 0;
}

KS_DECLARE(int) ks_json_check_string_is_uuid(ks_json_t* item)
{
	if (!ks_json_type_is_string(item) || zstr(item->valuestring) || strlen(item->valuestring) != 36) {
		return 0;
	}
	ks_uuid_t parsed = { 0 };

#ifdef KS_PLAT_WIN
	return UuidFromStringA((unsigned char *)item->valuestring, &parsed) == RPC_S_OK;
#else
	return uuid_parse(item->valuestring, (unsigned char *)&parsed) == 0;
#endif
}

KS_DECLARE(int) ks_json_check_string_is_e164(ks_json_t* item)
{
	return ks_json_type_is_string(item) &&
		!zstr(item->valuestring) &&
		strlen(item->valuestring) > 4 &&
		strlen(item->valuestring) < 20 &&
		item->valuestring[0] == '+' &&
		ks_is_number(item->valuestring) &&
		!strchr(item->valuestring + 1, '.');
}

KS_DECLARE(int) ks_json_check_string_matches(ks_json_t *item, const char *rule)
{
	if (!ks_json_type_is_string(item) || zstr(item->valuestring)) {
		return 0;
	}
	return string_matches(item->valuestring, rule);
}

KS_DECLARE(int) ks_json_check_string_is_https(ks_json_t *item)
{
	return ks_json_check_string_starts_with_insensitive(item, "https://");
}

KS_DECLARE(int) ks_json_check_string_is_http(ks_json_t *item)
{
	return ks_json_check_string_starts_with_insensitive(item, "http://");
}

KS_DECLARE(int) ks_json_check_string_is_http_or_https(ks_json_t *item)
{
	return ks_json_check_string_is_http(item) || ks_json_check_string_is_https(item);
}

KS_DECLARE(int) ks_json_check_string_is_ws_uri(ks_json_t *item)
{
	return ks_json_check_string_starts_with_insensitive(item, "ws://");
}

KS_DECLARE(int) ks_json_check_string_is_wss_uri(ks_json_t *item)
{
	return ks_json_check_string_starts_with_insensitive(item, "wss://");
}

KS_DECLARE(int) ks_json_check_string_is_ws_or_wss_uri(ks_json_t *item)
{
	return ks_json_check_string_is_ws_uri(item) || ks_json_check_string_is_wss_uri(item);
}

KS_DECLARE(int) ks_json_check_object(ks_json_t *json, const char *item_names)
{
	if (!ks_json_type_is_object(json)) {
		return 0;
	}
	if (zstr(item_names)) {
		return 1;
	}
	ks_json_t *item = NULL;
	for (item = ks_json_enum_child(json); item; item = ks_json_enum_next(item)) {
		if (zstr(item->string) || !string_matches(item->string, item_names)) {
			return 0;
		}
	}
	return 1;
}

KS_DECLARE(int) ks_json_check_array_items(ks_json_t *json, ks_json_check_function check, const char **error_msg)
{
	if (!ks_json_type_is_array(json)) {
		return 0;
	}
	ks_json_t *item = NULL;
	for (item = ks_json_enum_child(json); item; item = ks_json_enum_next(item)) {
		if (!check(item, error_msg)) {
			return 0;
		}
	}
	return 1;
}

KS_DECLARE(int) ks_json_check_string_array(ks_json_t *json, ks_json_simple_check_function check)
{
	if (!ks_json_type_is_array(json)) {
		return 0;
	}
	ks_json_t *item = NULL;
	for (item = ks_json_enum_child(json); item; item = ks_json_enum_next(item)) {
		if (!check(item)) {
			return 0;
		}
	}
	return 1;
}

KS_DECLARE(int) ks_json_check_is_any(ks_json_t *item)
{
	return 1;
}

KS_DECLARE(int) ks_json_check_is_array(ks_json_t *item)
{
	return ks_json_type_is_array(item);
}
