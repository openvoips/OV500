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

typedef struct kJSON ks_json_t;

typedef enum {
	KS_JSON_TYPE_INVALID = 0,
	KS_JSON_TYPE_FALSE   = KS_BIT_FLAG(0),
	KS_JSON_TYPE_TRUE    = KS_BIT_FLAG(1),
	KS_JSON_TYPE_NULL    = KS_BIT_FLAG(2),
	KS_JSON_TYPE_NUMBER  = KS_BIT_FLAG(3),
	KS_JSON_TYPE_STRING  = KS_BIT_FLAG(4),
	KS_JSON_TYPE_ARRAY   = KS_BIT_FLAG(5),
	KS_JSON_TYPE_OBJECT  = KS_BIT_FLAG(6),
	KS_JSON_TYPE_RAW     = KS_BIT_FLAG(7)
} ks_json_type_t;

KS_DECLARE(ks_json_t *) ks_json_create_string(const char *string);
KS_DECLARE(ks_json_t *) ks_json_create_string_fmt(const char *fmt, ...);
KS_DECLARE(ks_json_t *) ks_json_create_number(double number);
KS_DECLARE(ks_json_t *) ks_json_create_array(void);
KS_DECLARE(ks_json_t *) ks_json_create_object(void);
KS_DECLARE(ks_json_t *) ks_json_create_null(void);
KS_DECLARE(ks_json_t *) ks_json_create_true(void);
KS_DECLARE(ks_json_t *) ks_json_create_false(void);
KS_DECLARE(ks_json_t *) ks_json_create_bool(ks_bool_t value);

KS_DECLARE(ks_json_t *) ks_json_parse(const char *value);

KS_DECLARE(void) ks_json_add_item_to_array(ks_json_t *array, ks_json_t *item);
KS_DECLARE(ks_json_t *) ks_json_add_array_to_array(ks_json_t *array);
KS_DECLARE(ks_json_t *) ks_json_add_object_to_array(ks_json_t *array);
KS_DECLARE(void) ks_json_add_number_to_array(ks_json_t * array, double number);
KS_DECLARE(void) ks_json_add_string_to_array(ks_json_t * array, const char *string);
KS_DECLARE(void) ks_json_add_true_to_array(ks_json_t * array);
KS_DECLARE(void) ks_json_add_false_to_array(ks_json_t *array);
KS_DECLARE(void) ks_json_add_bool_to_array(ks_json_t * array, ks_bool_t value);
KS_DECLARE(ks_json_t *) ks_json_add_array_to_object(ks_json_t *object, const char *name);
KS_DECLARE(ks_json_t *) ks_json_add_object_to_object(ks_json_t *object, const char *name);
KS_DECLARE(void) ks_json_add_item_to_object(ks_json_t *object, const char *string, ks_json_t *item);
KS_DECLARE(void) ks_json_add_number_to_object(ks_json_t *object, const char *name, double number);
KS_DECLARE(void) ks_json_add_string_to_object(ks_json_t *object, const char *name, const char *string);
KS_DECLARE(void) ks_json_add_false_to_object(ks_json_t *object, const char *name);
KS_DECLARE(void) ks_json_add_true_to_object(ks_json_t *object, const char *name);
KS_DECLARE(void) ks_json_add_bool_to_object(ks_json_t *array, const char *name, ks_bool_t value);

KS_DECLARE(ks_json_t *) ks_json_duplicate(ks_json_t *item, ks_bool_t recurse);

KS_DECLARE(void) ks_json_delete(ks_json_t **c);
KS_DECLARE(void) ks_json_delete_item_from_array(ks_json_t *array, int index);
KS_DECLARE(void) ks_json_delete_item_from_object(ks_json_t *object, const char * const key);

KS_DECLARE(ks_json_t *) ks_json_get_object_item(ks_json_t *object, const char *string);
KS_DECLARE(ks_bool_t) ks_json_get_object_bool(ks_json_t *object, const char *string, ks_bool_t def);
KS_DECLARE(const char *) ks_json_get_object_string(ks_json_t *object, const char *key, const char *def);
KS_DECLARE(int) ks_json_get_object_number_int(ks_json_t *object, const char * key, int def);
KS_DECLARE(double) ks_json_get_object_number_double(ks_json_t *object, const char *key, double def);

KS_DECLARE(ks_json_t *) ks_json_get_array_item(ks_json_t *array, int index);
KS_DECLARE(int) ks_json_get_array_size(ks_json_t *array);
KS_DECLARE(ks_bool_t) ks_json_get_array_bool(ks_json_t *array, int index, ks_bool_t def);
KS_DECLARE(const char *) ks_json_get_array_string(ks_json_t *array, int index, const char *def);
KS_DECLARE(int) ks_json_get_array_number_int(ks_json_t *array, int index, int def);
KS_DECLARE(double) ks_json_get_array_number_double(ks_json_t *array, int index, double def);

KS_DECLARE(char *) ks_json_print(ks_json_t *item);
KS_DECLARE(char *) ks_json_print_unformatted(ks_json_t *item);

KS_DECLARE(ks_status_t) ks_json_name(ks_json_t *item, const char **name);
KS_DECLARE(ks_status_t) ks_json_value_string(ks_json_t *item, const char **value);
KS_DECLARE(ks_status_t) ks_json_value_number_int(ks_json_t *item, int *value);
KS_DECLARE(ks_status_t) ks_json_value_number_double(ks_json_t *item, double *value);
KS_DECLARE(ks_status_t) ks_json_value_bool(ks_json_t *item, ks_bool_t *value);

KS_DECLARE(const char *) ks_json_get_name(ks_json_t *item);
KS_DECLARE(const char *) ks_json_get_string(ks_json_t *item, const char *def);
KS_DECLARE(int) ks_json_get_number_int(ks_json_t *item, int def);
KS_DECLARE(double) ks_json_get_number_double(ks_json_t *item, double def);
KS_DECLARE(ks_bool_t) ks_json_get_bool(ks_json_t *item, ks_bool_t def);

KS_DECLARE(ks_json_type_t) ks_json_type_get(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is(ks_json_t *item, ks_json_type_t type);
KS_DECLARE(ks_bool_t) ks_json_type_is_array(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is_string(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is_number(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is_null(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is_object(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is_false(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is_true(ks_json_t *item);
KS_DECLARE(ks_bool_t) ks_json_type_is_bool(ks_json_t *item);

KS_DECLARE(ks_json_t *) ks_json_enum_child(ks_json_t *item);
KS_DECLARE(ks_json_t *) ks_json_enum_next(ks_json_t *item);

#define KS_JSON_ARRAY_FOREACH(element, array) for(element = ks_json_enum_child((array))	\
			; element != NULL; element = ks_json_enum_next(element))

#define KS_JSON_PRINT(_h, _j) do { \
		char *_json = ks_json_print(_j); \
		ks_log(KS_LOG_INFO, "--- %s ---\n%s\n---\n", _h, _json); \
		free(_json); \
	} while (0)

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
