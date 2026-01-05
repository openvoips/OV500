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
#include "cJSON/cJSON.h"

// Create apis
KS_DECLARE(ks_json_t *) ks_json_create_array(void)
{
	return kJSON_CreateArray();
}

KS_DECLARE(ks_json_t *) ks_json_create_object(void)
{
	return kJSON_CreateObject();
}

KS_DECLARE(ks_json_t *) ks_json_create_number(double number)
{
	return kJSON_CreateNumber(number);
}

KS_DECLARE(ks_json_t *) ks_json_create_string_fmt(const char *fmt, ...)
{
	va_list ap;
	char *str;
	ks_json_t *item;

	va_start(ap, fmt);
	str = ks_vmprintf(fmt, ap);
	va_end(ap);

	if (!str) return NULL;

	item = kJSON_CreateString(str);

	free(str);	/* Managed by glibc for the formatting allocation */

	return item;
}

KS_DECLARE(ks_json_t *) ks_json_create_string(const char *string)
{
	return kJSON_CreateString(string);
}

KS_DECLARE(ks_json_t *) ks_json_create_false(void)
{
	return kJSON_CreateFalse();
}

KS_DECLARE(ks_json_t *) ks_json_create_true(void)
{
	return kJSON_CreateTrue();
}

KS_DECLARE(ks_json_t *) ks_json_create_null(void)
{
	return kJSON_CreateNull();
}

KS_DECLARE(ks_json_t *) ks_json_create_bool(ks_bool_t value)
{
	return kJSON_CreateBool(value == KS_TRUE);
}

// Parse apis
KS_DECLARE(ks_json_t *) ks_json_parse(const char *value)
{
	return kJSON_Parse(value);
}

// Add apis
KS_DECLARE(void) ks_json_add_item_to_array(ks_json_t *array, ks_json_t *item)
{
	kJSON_AddItemToArray(array, item);
}

KS_DECLARE(ks_json_t *) ks_json_add_array_to_array(ks_json_t *array)
{
	ks_json_t *new_array = ks_json_create_array();
	ks_json_add_item_to_array(array, new_array);
	return new_array;
}

KS_DECLARE(ks_json_t *) ks_json_add_object_to_array(ks_json_t *array)
{
	ks_json_t *new_object = ks_json_create_object();
	ks_json_add_item_to_array(array, new_object);
	return new_object;
}

KS_DECLARE(void) ks_json_add_string_to_array(ks_json_t *array, const char *string)
{
	ks_json_add_item_to_array(array, ks_json_create_string(string));
}

KS_DECLARE(void) ks_json_add_number_to_array(ks_json_t *array, double number)
{
	ks_json_add_item_to_array(array, ks_json_create_number(number));
}

KS_DECLARE(void) ks_json_add_true_to_array(ks_json_t *array)
{
	ks_json_add_item_to_array(array, ks_json_create_true());
}

KS_DECLARE(void) ks_json_add_false_to_array(ks_json_t *array)
{
	ks_json_add_item_to_array(array, ks_json_create_false());
}

KS_DECLARE(void) ks_json_add_bool_to_array(ks_json_t *array, ks_bool_t value)
{
	if (value == KS_TRUE) {
		ks_json_add_true_to_array(array);
	} else {
		ks_json_add_false_to_array(array);
	}
}

KS_DECLARE(void) ks_json_add_item_to_object(ks_json_t *object, const char *string, ks_json_t *item)
{
	// TODO check if item parent is NULL
	kJSON_AddItemToObject(object, string, item);
}

KS_DECLARE(ks_json_t *) ks_json_add_array_to_object(ks_json_t *object, const char *string)
{
	ks_json_t *new_array = ks_json_create_array();
	ks_json_add_item_to_object(object, string, new_array);
	return new_array;
}

KS_DECLARE(ks_json_t *) ks_json_add_object_to_object(ks_json_t *object, const char *string)
{
	ks_json_t *new_object = ks_json_create_object();
	ks_json_add_item_to_object(object, string, new_object);
	return new_object;
}

KS_DECLARE(void) ks_json_add_true_to_object(ks_json_t *object, const char *string)
{
	ks_json_add_item_to_object(object, string, ks_json_create_true());
}

KS_DECLARE(void) ks_json_add_false_to_object(ks_json_t *object, const char *string)
{
	ks_json_add_item_to_object(object, string, ks_json_create_false());
}

KS_DECLARE(void) ks_json_add_bool_to_object(ks_json_t *object, const char *string, ks_bool_t value)
{
	if (value == KS_TRUE) {
		ks_json_add_true_to_object(object, string);
	} else {
		ks_json_add_false_to_object(object, string);
	}
}

KS_DECLARE(void) ks_json_add_number_to_object(ks_json_t *object, const char *name, double number)
{
	ks_json_add_item_to_object(object, name, ks_json_create_number(number));
}

KS_DECLARE(void) ks_json_add_string_to_object(ks_json_t *object, const char *name, const char *string)
{
	ks_json_add_item_to_object(object, name, ks_json_create_string(string));
}

KS_DECLARE(ks_json_t *) ks_json_duplicate(ks_json_t *c, ks_bool_t recurse)
{
	return kJSON_Duplicate(c, recurse);
}

KS_DECLARE(void) ks_json_delete(ks_json_t **c)
{
	if (!c || !*c)
		return;

	kJSON_Delete(*c);
	*c = NULL;
}

KS_DECLARE(void) ks_json_delete_item_from_array(ks_json_t *array, int index)
{
	kJSON_DeleteItemFromArray(array, index);
}

KS_DECLARE(void) ks_json_delete_item_from_object(ks_json_t *obj, const char *key)
{
	kJSON_DeleteItemFromObject(obj, key);
}

// Get apis
KS_DECLARE(ks_json_t *) ks_json_get_array_item(ks_json_t *array, int index)
{
	return kJSON_GetArrayItem(array, index);
}

KS_DECLARE(ks_bool_t) ks_json_get_array_bool(ks_json_t *array, int index, ks_bool_t def)
{
	ks_bool_t retval = def;
	ks_json_t *item = ks_json_get_array_item(array, index);
	ks_json_value_bool(item, &retval);
	return retval;
}

KS_DECLARE(const char *) ks_json_get_array_string(ks_json_t *array, int index, const char* def)
{
	const char *retval = def;
	ks_json_t *item = ks_json_get_array_item(array, index);
	ks_json_value_string(item, &retval);
	return retval;
}

KS_DECLARE(int) ks_json_get_array_number_int(ks_json_t *array, int index, int def)
{
	int retval = def;
	ks_json_t *item = ks_json_get_array_item(array, index);
	ks_json_value_number_int(item, &retval);
	return retval;
}

KS_DECLARE(double) ks_json_get_array_number_double(ks_json_t *array, int index, double def)
{
	double retval = def;
	ks_json_t *item = ks_json_get_array_item(array, index);
	ks_json_value_number_double(item, &retval);
	return retval;
}

KS_DECLARE(int) ks_json_get_array_size(ks_json_t *array)
{
	return kJSON_GetArraySize(array);
}

KS_DECLARE(ks_json_t *) ks_json_get_object_item(ks_json_t *object, const char *string)
{
	return kJSON_GetObjectItemCaseSensitive(object, string);
}

KS_DECLARE(ks_bool_t) ks_json_get_object_bool(ks_json_t *object, const char *string, ks_bool_t def)
{
	ks_bool_t retval = def;
	ks_json_value_bool(ks_json_get_object_item(object, string), &retval);
	return retval;
}

KS_DECLARE(const char *) ks_json_get_object_string(ks_json_t *object, const char *key, const char *def)
{
	const char *retval = def;
	ks_json_value_string(ks_json_get_object_item(object, key), &retval);
	return retval;
}

KS_DECLARE(int) ks_json_get_object_number_int(ks_json_t *object, const char *key, int def)
{
	int retval = def;
	ks_json_value_number_int(ks_json_get_object_item(object, key), &retval);
	return retval;
}

KS_DECLARE(double) ks_json_get_object_number_double(ks_json_t *object, const char *key, double def)
{
	double retval = def;
	ks_json_value_number_double(ks_json_get_object_item(object, key), &retval);
	return retval;
}

KS_DECLARE(const char *) ks_json_get_name(ks_json_t *item)
{
	if (item) {
		return item->string;
	}
	return NULL;
}

KS_DECLARE(const char *) ks_json_get_string(ks_json_t *item, const char *def)
{
	const char *val = def;
	ks_json_value_string(item, &val);
	return val;
}

KS_DECLARE(int) ks_json_get_number_int(ks_json_t *item, int def)
{
	int val = def;
	ks_json_value_number_int(item, &val);
	return val;
}

KS_DECLARE(double) ks_json_get_number_double(ks_json_t *item, double def)
{
	double val = def;
	ks_json_value_number_double(item, &val);
	return val;
}

KS_DECLARE(ks_bool_t) ks_json_get_bool(ks_json_t *item, ks_bool_t def)
{
	ks_bool_t val = def;
	ks_json_value_bool(item, &val);
	return val;
}

KS_DECLARE(char *) ks_json_print(ks_json_t *item)
{
	return kJSON_Print(item);
}

KS_DECLARE(char *) ks_json_print_unformatted(ks_json_t *item)
{
	return kJSON_PrintUnformatted(item);
}

KS_DECLARE(ks_json_type_t) ks_json_type_get(ks_json_t *item)
{
	if (!item)
		return KS_JSON_TYPE_INVALID;
	return item->type;
}

KS_DECLARE(ks_bool_t) ks_json_type_is(ks_json_t *item, ks_json_type_t type)
{
	return ks_json_type_get(item) == type;
}

KS_DECLARE(ks_bool_t) ks_json_type_is_array(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_ARRAY);
}

KS_DECLARE(ks_bool_t) ks_json_type_is_string(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_STRING);
}

KS_DECLARE(ks_bool_t) ks_json_type_is_number(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_NUMBER);
}

KS_DECLARE(ks_bool_t) ks_json_type_is_null(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_NULL);
}

KS_DECLARE(ks_bool_t) ks_json_type_is_object(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_OBJECT);
}

KS_DECLARE(ks_bool_t) ks_json_type_is_false(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_FALSE);
}

KS_DECLARE(ks_bool_t) ks_json_type_is_true(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_TRUE);
}

KS_DECLARE(ks_bool_t) ks_json_type_is_bool(ks_json_t *item)
{
	return ks_json_type_is(item, KS_JSON_TYPE_FALSE) || ks_json_type_is(item, KS_JSON_TYPE_TRUE);
}

KS_DECLARE(ks_status_t) ks_json_name(ks_json_t *item, const char **name)
{
	if (!item->string || !*item->string) {
		return KS_STATUS_FAIL;
	}
	*name = item->string;
	return KS_STATUS_SUCCESS;
}

// Value apis
KS_DECLARE(ks_status_t) ks_json_value_string(ks_json_t *item, const char **value)
{
	if (!ks_json_type_is_string(item)) {
		return KS_STATUS_FAIL;
	}
	*value = item->valuestring;
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_json_value_number_int(ks_json_t *item, int *value)
{
	if (!ks_json_type_is_number(item)) {
		return KS_STATUS_FAIL;
	}
	*value = item->valueint;
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_json_value_number_double(ks_json_t *item, double *value)
{
	if (!ks_json_type_is_number(item)) {
		return KS_STATUS_FAIL;
	}
	*value = item->valuedouble;
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_status_t) ks_json_value_bool(ks_json_t *item, ks_bool_t *value)
{
	if (!ks_json_type_is_bool(item)) {
		return KS_STATUS_FAIL;
	}
	if (item->type == KS_JSON_TYPE_TRUE) {
		*value = KS_TRUE;
	} else {
		*value = KS_FALSE;
	}
	return KS_STATUS_SUCCESS;
}

KS_DECLARE(ks_json_t *) ks_json_enum_child(ks_json_t *item)
{
	if (!item) {
		return NULL;
	}
	return item->child;
}

KS_DECLARE(ks_json_t *) ks_json_enum_next(ks_json_t *item)
{
	if (!item) {
		return NULL;
	}
	return item->next;
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

