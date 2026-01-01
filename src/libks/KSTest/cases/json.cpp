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

#include "KSTest.hpp"
#include "cJSON/cJSON.h"
#include "catch/catch.hpp"

using namespace signalwire::pal;
using namespace signalwire::pal::async;

// Coverage for:
//	KS_JSON_TYPE_INVALID
//	KS_JSON_TYPE_FALSE
//	KS_JSON_TYPE_TRUE
//	KS_JSON_TYPE_NULL
//	KS_JSON_TYPE_NUMBER
//	KS_JSON_TYPE_STRING
//	KS_JSON_TYPE_ARRAY
//	KS_JSON_TYPE_OBJECT
//	KS_JSON_TYPE_RAW
TEST_CASE("json_types")
{
	REQUIRE(KS_JSON_TYPE_INVALID == static_cast<KS_JSON_TYPES>(cJSON_Invalid));
	REQUIRE(KS_JSON_TYPE_FALSE == static_cast<KS_JSON_TYPES>(cJSON_False));
	REQUIRE(KS_JSON_TYPE_TRUE == static_cast<KS_JSON_TYPES>(cJSON_True));
	REQUIRE(KS_JSON_TYPE_NULL == static_cast<KS_JSON_TYPES>(cJSON_NULL));
	REQUIRE(KS_JSON_TYPE_NUMBER == static_cast<KS_JSON_TYPES>(cJSON_Number));
	REQUIRE(KS_JSON_TYPE_STRING == static_cast<KS_JSON_TYPES>(cJSON_String));
	REQUIRE(KS_JSON_TYPE_ARRAY == static_cast<KS_JSON_TYPES>(cJSON_Array));
	REQUIRE(KS_JSON_TYPE_OBJECT == static_cast<KS_JSON_TYPES>(cJSON_Object));
	REQUIRE(KS_JSON_TYPE_RAW == static_cast<KS_JSON_TYPES>(cJSON_Raw));
}

// Coverage for:
//	ks_json_init
//	ks_json_deinit
TEST_CASE("json_init")
{
	// Always leave this scope initialized...
	auto cleanup = util::Scope(ks_json_init);

	// By default ks binds the pool to all json allocations
	// this can be disabled by calling ks_json_deinit, lets
	// test that assumption
	auto str1 = ks_json_create_string("bobo");

	// This will crash if so...
	ks_free(str1);

	// Ok now lets deinit and re-allocate one, should be free-able
	// by glibc free
	ks_json_deinit();

	str1 = ks_json_create_string("bobo");

	// This will crash if so...
	free(str1);

	// Now re-init to verify that works
	ks_json_init();

	str1 = ks_json_create_string("bobo");

	// This will crash if it didn't work...
	ks_free(str1);
}

// Coverage for:
//	ks_json_create_string
//	ks_json_create_string_fmt
//	ks_json_create_number
//	ks_json_create_array
//	ks_json_create_object
//	ks_json_create_false
//	ks_json_create_true
//	ks_json_create_bool
//	ks_json_create_null
TEST_CASE("json_create")
{
	// ks_json_create_string
	auto j = ks_json_create_string("bobo");
	REQUIRE(j->type == cJSON_String);
	ks_json_delete(&j);

	// ks_json_create_fmt
	j = ks_json_create_string_fmt("A Format %s Of Stuff", "(say wut)");
	REQUIRE(j->type == cJSON_String);
	REQUIRE(ks_json_type_is_string(j));
	REQUIRE("A Format (say wut) Of Stuff"s == j->valuestring);
	ks_json_delete(&j);

	// ks_json_create_number
	j = ks_json_create_number(42);
	REQUIRE(j->type == cJSON_Number);
	REQUIRE(ks_json_type_is_number(j));
	REQUIRE(42 == j->valueint);
	ks_json_delete(&j);

	// ks_json_create_array
	j = ks_json_create_array();
	REQUIRE(j->type == cJSON_Array);
	REQUIRE(ks_json_type_is_array(j));
	ks_json_delete(&j);

	// ks_json_create_object
	j = ks_json_create_object();
	REQUIRE(j->type == cJSON_Object);
	REQUIRE(ks_json_type_is_object(j));
	ks_json_delete(&j);

	// ks_json_create_true
	j = ks_json_create_true();
	REQUIRE(j->type == cJSON_True);
	ks_json_delete(&j);

	// ks_json_create_false
	j = ks_json_create_false();
	REQUIRE(j->type == cJSON_False);
	ks_json_delete(&j);

	// ks_json_create_bool
	j = ks_json_create_bool(KS_TRUE);
	REQUIRE(j->type == cJSON_True);
	ks_json_delete(&j);
	j = ks_json_create_bool(KS_FALSE);
	REQUIRE(j->type == cJSON_False);
	ks_json_delete(&j);

	// ks_json_create_null
	j = ks_json_create_null();
	REQUIRE(j->type == cJSON_NULL);
	ks_json_delete(&j);
}

// Coverage for:
//  ks_json_parse
TEST_CASE("json_parse")
{
	// ks_json_parse
	auto j = ks_json_parse(R"({
			"glossary": {
				"title": "example glossary",
				"GlossDiv": {
					"title": "S",
					"GlossList": {
						"GlossEntry": {
							"ID": "SGML",
							"SortAs": "SGML",
							"GlossTerm": "Standard Generalized Markup Language",
							"Acronym": "SGML",
							"Abbrev": "ISO 8879:1986",
							"GlossDef": {
								"para": "A meta-markup language, used to create markup languages such as DocBook.",
								"GlossSeeAlso": ["GML", "XML"]
							},
							"GlossSee": "markup"
						}
					}
				}
			}
		})"
	);

	// @@ TODO validate
	ks_json_delete(&j);
}

// Coverage for:
//  ks_json_add_item_to_array
//  ks_json_add_number_to_array
//  ks_json_add_string_to_array
//  ks_json_add_true_to_array
//  ks_json_add_false_to_array
//  ks_json_add_item_to_object
//  ks_json_add_number_to_object
//  ks_json_add_string_to_object
//  ks_json_add_true_to_object
//  ks_json_add_false_to_object
//  ks_json_add_uuid_to_array
//  ks_json_add_uuid_to_object
//  ks_json_get_object_uuid
//  ks_json_get_array_uuid
TEST_CASE("json_add")
{
	// ks_json_add_item_to_array
	auto j = ks_json_create_array();
	REQUIRE(ks_json_get_array_size(j) == 0);
	ks_json_add_item_to_array(j, ks_json_create_string("bobo"));
	REQUIRE(ks_json_get_array_size(j) == 1);
	ks_json_add_item_to_array(j, ks_json_create_string("frodo"));
	REQUIRE(ks_json_get_array_size(j) == 2);
	REQUIRE("bobo"s == ks_json_get_array_item(j, 0)->valuestring);
	REQUIRE("frodo"s == ks_json_get_array_item(j, 1)->valuestring);
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_array_item(j, 2));
#endif
	ks_json_delete(&j);

	// ks_json_add_number_to_array
	j = ks_json_create_array();
	ks_json_add_number_to_array(j, 42);
	REQUIRE(ks_json_type_is_number(ks_json_get_array_item(j, 0)));
	REQUIRE(ks_json_get_array_number_int(j, 0) == 42);
	ks_json_add_number_to_array(j,  42.5);
	REQUIRE(ks_json_get_array_number_double(j, 1) == 42.5);
	ks_json_delete(&j);

	// ks_json_add_string_to_array
	j = ks_json_create_array();
	ks_json_add_string_to_array(j, "42");
	REQUIRE(ks_json_type_is_string(ks_json_get_array_item(j, 0)));
	REQUIRE("42"s == ks_json_get_array_cstr(j, 0));
	ks_json_delete(&j);

	// ks_json_add_true_to_array
	j = ks_json_create_array();
	ks_json_add_true_to_array(j);
	REQUIRE(ks_json_type_is_true(ks_json_get_array_item(j, 0)));
	REQUIRE(KS_TRUE == ks_json_get_array_bool(j, 0));
	ks_json_delete(&j);

	// ks_json_add_false_to_array
	j = ks_json_create_array();
	ks_json_add_false_to_array(j);
	REQUIRE(ks_json_type_is_false(ks_json_get_array_item(j, 0)));
	REQUIRE(KS_FALSE == ks_json_get_array_bool(j, 0));
	ks_json_delete(&j);

	// ks_json_add_item_to_object
	j = ks_json_create_object();
	ks_json_add_item_to_object(j, "key", ks_json_create_string("value"));
	REQUIRE("value"s == ks_json_get_object_cstr(j, "key"));
	ks_json_add_item_to_object(j, "key2", ks_json_create_string("value2"));
	REQUIRE("value2"s == ks_json_get_object_cstr(j, "key2"));
	ks_json_add_item_to_object(j, "key", ks_json_create_string("value"));
	REQUIRE("value"s == ks_json_get_object_cstr(j, "key"));
	ks_json_delete(&j);

	// ks_json_add_number_to_object
	j = ks_json_create_object();
	ks_json_add_number_to_object(j, "answer_to_life", 42);
	REQUIRE(ks_json_type_is_number(ks_json_get_object_item(j, "answer_to_life")));
	REQUIRE(ks_json_get_object_number_int(j, "answer_to_life") == 42);
	ks_json_add_number_to_object(j, "answer_to_life_2", 42.5);
	REQUIRE(ks_json_get_object_number_double(j, "answer_to_life_2") == 42.5);
	ks_json_delete(&j);

	// ks_json_add_string_to_object
	j = ks_json_create_object();
	ks_json_add_string_to_object(j, "answer_to_life", "42");
	REQUIRE(ks_json_type_is_string(ks_json_get_object_item(j, "answer_to_life")));
	REQUIRE("42"s == ks_json_get_object_cstr(j, "answer_to_life"));
	ks_json_delete(&j);

	// ks_json_add_true_to_object
	j = ks_json_create_object();
	ks_json_add_true_to_object(j, "answer_to_life");
	REQUIRE(ks_json_type_is_true(ks_json_get_object_item(j, "answer_to_life")));
	REQUIRE(KS_TRUE == ks_json_get_object_bool(j, "answer_to_life"));
	ks_json_delete(&j);

	// ks_json_add_false_to_object
	j = ks_json_create_object();
	ks_json_add_false_to_object(j, "answer_to_life");
	REQUIRE(ks_json_type_is_false(ks_json_get_object_item(j, "answer_to_life")));
	REQUIRE(KS_FALSE == ks_json_get_object_bool(j, "answer_to_life"));
	ks_json_delete(&j);

	// ks_json_add_uuid_to_array
	// ks_json_get_array_uuid
	j = ks_json_create_array();
	uuid_t uuid;
	REQUIRE(ks_json_add_uuid_to_array(j, *ks_uuid(&uuid)));
	auto new_uuid = ks_json_get_array_uuid(j, 0);
	REQUIRE(uuid == new_uuid);
	ks_json_delete(&j);

	// ks_json_add_uuid_to_object
	// ks_json_get_object_uuid
	j = ks_json_create_object();
	REQUIRE(ks_json_add_uuid_to_object(j, "bobo", *ks_uuid(&uuid)));
	new_uuid = ks_json_get_object_uuid(j, "bobo");
	REQUIRE(uuid == new_uuid);
	ks_json_delete(&j);
}

// Coverage for:
//  ks_json_duplicate
TEST_CASE("json_dupe")
{
	// ks_json_duplicate
	auto j1 = ks_json_create_object();
	ks_json_add_string_to_object(j1, "another_day", "another_dollar");
	auto j2 = ks_json_duplicate(j1, KS_TRUE);
	REQUIRE("another_dollar"s == ks_json_get_object_cstr(j1, "another_day"));
	REQUIRE("another_dollar"s == ks_json_get_object_cstr(j2, "another_day"));
	ks_json_delete(&j1);
	REQUIRE("another_dollar"s == ks_json_get_object_cstr(j2, "another_day"));
	ks_json_delete(&j2);
}

// Coverage for:
//  ks_json_delete
//  ks_json_delete_item_from_array
//  ks_json_delete_item_from_object
TEST_CASE("json_delete")
{
	// ks_json_delete
	auto j = ks_json_create_object();

	// Modify the hook to ensure delete is called
	cJSON_Hooks hooks = {
			[](size_t size)->auto {
				return ks_malloc(size);
			},
			[](void *data)->auto {
				ks_free(data);
				throw true;
			}
		};
	cJSON_InitHooks(&hooks);

	// Now try to delete we should catch true
	try {
		ks_json_delete(&j);
		REQUIRE(!"Should've thrown");
	} catch (bool) {
		// Success
		REQUIRE(true);
	}

	// Restore the hooks
	ks_json_init();

	// ks_json_delete_item_from_array
	j = ks_json_create_array();
	ks_json_add_item_to_array(j, ks_json_create_string("hello"));
	ks_json_add_item_to_array(j, ks_json_create_string("there"));
	REQUIRE("hello"s == ks_json_get_array_cstr(j, 0));
	REQUIRE("there"s == ks_json_get_array_cstr(j, 1));
	ks_json_delete_item_from_array(j, 0);
	REQUIRE("there"s == ks_json_get_array_cstr(j, 0));
	ks_json_delete_item_from_array(j, 0);
	ks_json_delete(&j);

	// ks_json_delete_item_from_object
	j = ks_json_create_object();
	ks_json_add_item_to_object(j, "yup", ks_json_create_string("hello"));
	REQUIRE("hello"s == ks_json_get_object_cstr(j, "yup"));
	ks_json_delete_item_from_object(j, "yup");
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_object_item(j, "yup"));
#endif
}

// Coverage for:
//  ks_json_get_object_item
//  ks_json_get_object_cstr
//  ks_json_get_object_number_int
//  ks_json_get_object_number_double
//  ks_json_get_object_bool
TEST_CASE("json_get_object")
{
	// ks_json_get_object_item
	auto j = ks_json_create_object();
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_object_item(j, "does_not_exist"));
#endif
	ks_json_add_item_to_object(j, "does_exist", ks_json_create_string("Yup"));
	REQUIRE("Yup"s == ks_json_get_object_cstr(j, "does_exist"));
	ks_json_delete(&j);

	// ks_json_get_object_cstr
	j = ks_json_create_object();
	ks_json_add_item_to_object(j, "key", ks_json_create_string("A Test String"));
	REQUIRE("A Test String"s == ks_json_get_object_cstr(j, "key"));
	ks_json_delete(&j);

	// ks_json_get_object_number_int
	j = ks_json_create_object();
	ks_json_add_item_to_object(j, "key", ks_json_create_number(42));
	REQUIRE(42 == ks_json_get_object_number_int(j, "key"));
	ks_json_delete(&j);

	// ks_json_get_object_number_double
	j = ks_json_create_object();
	ks_json_add_item_to_object(j, "key", ks_json_create_number(42.5));
	REQUIRE(42.5 == ks_json_get_object_number_double(j, "key"));
	ks_json_delete(&j);

	// ks_json_get_object_bool
	j = ks_json_create_object();
	ks_json_add_false_to_object(j, "bobo");
	ks_json_add_true_to_object(j, "bobo2");
	REQUIRE(KS_FALSE == ks_json_get_object_bool(j, "bobo"));
	REQUIRE(KS_TRUE == ks_json_get_object_bool(j, "bobo2"));
	ks_json_delete(&j);
}

// Coverage for:
//  ks_json_get_array_item
//  ks_json_get_array_size
//  ks_json_get_array_cstr
//  ks_json_get_array_number_int
//  ks_json_get_array_number_double
//  ks_json_get_array_bool
TEST_CASE("json_get_array")
{
	// ks_json_get_array_item
	// ks_json_get_array_size
	auto j = ks_json_create_array();
	REQUIRE(ks_json_get_array_size(j) == 0);
	ks_json_add_item_to_array(j, ks_json_create_string("bobo"));
	REQUIRE(ks_json_get_array_size(j) == 1);
	ks_json_add_item_to_array(j, ks_json_create_string("frodo"));
	REQUIRE(ks_json_get_array_size(j) == 2);
	REQUIRE("bobo"s == ks_json_get_array_item(j, 0)->valuestring);
	REQUIRE("frodo"s == ks_json_get_array_item(j, 1)->valuestring);
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_array_item(j, 2));
#endif
	ks_json_delete(&j);

	// ks_json_get_array_cstr
	j = ks_json_create_array();
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_array_item(j, 0));
#endif
	ks_json_add_item_to_array(j, ks_json_create_string("Yup"));
	REQUIRE("Yup"s == ks_json_get_array_cstr(j, 0));
	ks_json_add_item_to_array(j, ks_json_create_string("Yup2"));
	REQUIRE("Yup2"s == ks_json_get_array_cstr(j, 1));
	ks_json_delete(&j);

	// ks_json_get_array_number_double
	j = ks_json_create_array();
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_array_item(j, 0));
#endif
	ks_json_add_item_to_array(j, ks_json_create_number(51.21));
	REQUIRE(51.21 == ks_json_get_array_number_double(j, 0));
	ks_json_delete(&j);

	// ks_json_get_array_number_int
	j = ks_json_create_array();
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_array_item(j, 0));
#endif
	ks_json_add_item_to_array(j, ks_json_create_number(616));
	REQUIRE(616 == ks_json_get_array_number_int(j, 0));
	ks_json_delete(&j);

	// ks_json_get_array_bool
	j = ks_json_create_array();
#if !defined(KS_BUILD_DEBUG)
	REQUIRE(nullptr == ks_json_get_array_item(j, 0));
#endif
	ks_json_add_item_to_array(j, ks_json_create_bool(KS_TRUE));
	REQUIRE(KS_TRUE == ks_json_get_array_bool(j, 0));
	ks_json_add_item_to_array(j, ks_json_create_bool(KS_FALSE));
	REQUIRE(KS_FALSE == ks_json_get_array_bool(j, 1));
	ks_json_delete(&j);

	// ks_json_get_array_uuid
}

// Coverage for:
//  ks_json_print
//  ks_json_print_unformatted
TEST_CASE("json_print")
{
	// ks_json_print
	auto j = ks_json_parse(R"({"menu": {"id": "file", "value": "File" } })");
	REQUIRE(ks_json_get_object_item(j, "menu")->type == cJSON_Object);
	auto result = ks_json_print(j);
	auto j2 = ks_json_parse(result);
	REQUIRE(ks_json_get_object_item(j2, "menu")->type == cJSON_Object);
	auto result2 = ks_json_print(j2);
	REQUIRE(std::string_view(result) == std::string_view(result2));
	ks_json_free(&result);
	ks_json_free(&result2);
	ks_json_delete(&j2);
	ks_json_delete(&j);

	// ks_json_print
	j = ks_json_parse(R"({"menu": {"id": "file", "value": "File" } })");
	REQUIRE(ks_json_get_object_item(j, "menu")->type == cJSON_Object);
	result = ks_json_print_unformatted(j);
	j2 = ks_json_parse(result);
	REQUIRE(ks_json_get_object_item(j2, "menu")->type == cJSON_Object);
	result2 = ks_json_print_unformatted(j2);
	REQUIRE(std::string_view(result) == std::string_view(result2));
	ks_json_free(&result);
	ks_json_free(&result2);
	ks_json_delete(&j2);
	ks_json_delete(&j);
}

// Coverage for:
//  ks_json_type_get
//  ks_json_type_is
//  ks_json_type_is_array
//  ks_json_type_is_string
//  ks_json_type_is_number
//  ks_json_type_is_null
//  ks_json_type_is_object
//  ks_json_type_is_false
//  ks_json_type_is_true
TEST_CASE("json_type")
{
	auto j = ks_json_create_object();
	REQUIRE(ks_json_type_get(j) == KS_JSON_TYPE_OBJECT);
	REQUIRE(ks_json_type_is_object(j));
	ks_json_delete(&j);

	j = ks_json_create_array();
	REQUIRE(ks_json_type_get(j) == KS_JSON_TYPE_ARRAY);
	REQUIRE(ks_json_type_is_array(j));
	ks_json_delete(&j);

	j = ks_json_create_false();
	REQUIRE(ks_json_type_get(j) == KS_JSON_TYPE_FALSE);
	REQUIRE(ks_json_type_is_false(j));
	ks_json_delete(&j);

	j = ks_json_create_true();
	REQUIRE(ks_json_type_get(j) == KS_JSON_TYPE_TRUE);
	REQUIRE(ks_json_type_is_true(j));
	ks_json_delete(&j);

	j = ks_json_create_string("hallo");
	REQUIRE(ks_json_type_get(j) == KS_JSON_TYPE_STRING);
	REQUIRE(ks_json_type_is_string(j));
	ks_json_delete(&j);

	j = ks_json_create_number(42);
	REQUIRE(ks_json_type_get(j) == KS_JSON_TYPE_NUMBER);
	REQUIRE(ks_json_type_is_number(j));
	ks_json_delete(&j);

	j = ks_json_create_null();
	REQUIRE(ks_json_type_get(j) == KS_JSON_TYPE_NULL);
	REQUIRE(ks_json_type_is_null(j));
	ks_json_delete(&j);
}

// Coverage for:
//  ks_json_value_string
//  ks_json_value_number_int
//  ks_json_value_number_intptr
//  ks_json_value_number_double
//  ks_json_value_number_doubleptr
//  ks_json_value_number_bool
//  ks_json_value_uuid
//  ks_json_create_uuid
TEST_CASE("json_value")
{
	// ks_json_value_string
	auto j = ks_json_create_string("Hallo");
	REQUIRE("Hallo"s == ks_json_value_string(j));
	ks_json_delete(&j);

	// ks_json_value_number_int
	j = ks_json_create_number(42);
	REQUIRE(42 == ks_json_value_number_int(j));
	ks_json_delete(&j);

	// ks_json_value_number_intptr
	j = ks_json_create_number(42);
	REQUIRE(42 == *ks_json_value_number_intptr(j));
	ks_json_delete(&j);

	// ks_json_value_number_double
	j = ks_json_create_number(42.5);
	REQUIRE(42.5 == ks_json_value_number_double(j));
	ks_json_delete(&j);

	// ks_json_value_number_doubleptr
	j = ks_json_create_number(42.5);
	REQUIRE(42.5 == *ks_json_value_number_doubleptr(j));
	ks_json_delete(&j);

	// ks_json_value_bool
	j = ks_json_create_bool(KS_TRUE);
	REQUIRE(KS_TRUE == ks_json_value_bool(j));
	ks_json_delete(&j);
	j = ks_json_create_bool(KS_FALSE);
	REQUIRE(KS_FALSE == ks_json_value_bool(j));
	ks_json_delete(&j);

	// ks_json_value_uuid
	// ks_json_create_uuid
	uuid_t uuid;
	j = ks_json_create_uuid(*ks_uuid(&uuid));
	REQUIRE(j);
	std::string uuid_str = ks_uuid_str(nullptr, &uuid);
	REQUIRE(uuid_str == j->valuestring);
	REQUIRE(ks_json_value_uuid(j) == uuid);
}

// Coverage for:
//  KS_JSON_ARRAY_FOREACH
//  ks_json_enum_next
//  ks_json_enum_child
TEST_CASE("json_enum")
{
	// ks_json_enum_next
	// ks_json_enum_child
	// KS_JSON_ARRAY_FOREACH
	auto j = ks_json_create_array();
	ks_json_add_string_to_array(j, "hallo");
	ks_json_add_string_to_array(j, "hallo?");
	ks_json_add_string_to_array(j, "hallo!?!?");
	auto object = ks_json_add_item_to_array(j, ks_json_create_object());
	ks_json_add_item_to_object(object, "key", ks_json_create_string("value"));

	uint32_t index = 0;
	ks_json_t *element;
	KS_JSON_ARRAY_FOREACH(element, j) {
			switch(index++) {
			case 0:
				REQUIRE("hallo"s == ks_json_value_string(element));
				break;
			case 1:
				REQUIRE("hallo?"s == ks_json_value_string(element));
				break;
			case 2:
				REQUIRE("hallo!?!?"s == ks_json_value_string(element));
				break;
			case 3:
				REQUIRE(ks_json_type_is_object(element));
				break;
			case 4:
				REQUIRE("value"s == ks_json_value_string(element));
				break;
			default:
				REQUIRE(!"Unexpected element");
		}
	}
}

// Coverage for:
//  ks_json_lookup
//  ks_json_valookup
//	ks_json_lookup_cstr
//	ks_json_lookup_number_doubleptr
//	ks_json_lookup_number_intptr
//	ks_json_lookup_array_item
//	ks_json_lookup_uuid
TEST_CASE("json_lookup")
{
	// ks_json_address_item
	auto top_obj = ks_json_create_object();
	REQUIRE(top_obj);

	auto sub_obj = ks_json_create_object();
	REQUIRE(sub_obj);

	auto sub_array = ks_json_create_array();
	ks_json_add_number_to_array(sub_array, 42.5);					// 0
	ks_json_add_number_to_array(sub_array, 42);						// 1
	ks_json_add_string_to_array(sub_array, "I live in an array");	// 2

	auto string_item = ks_json_add_string_to_object(sub_obj, "string_is_here", "You found me!");
	REQUIRE(ks_json_add_item_to_object(sub_obj, "array_is_here", sub_array));
	REQUIRE(string_item);
	REQUIRE(ks_json_add_number_to_object(sub_obj, "double_is_here", 42.5));
	uuid_t uuid;
	ks_uuid(&uuid);
	REQUIRE(ks_json_add_uuid_to_object(sub_obj, "uuid_is_here", uuid));
	REQUIRE(ks_json_add_number_to_object(sub_obj, "int_is_here", 42));

	REQUIRE(ks_json_add_item_to_object(top_obj, "top_level_key", sub_obj));

	// Now try to address it!
	REQUIRE(ks_json_lookup(top_obj, 0)  == top_obj);
	REQUIRE(ks_json_lookup(top_obj, 1, "top_level_key") == sub_obj);
	REQUIRE("You found me!"s == ks_json_lookup(top_obj, 2, "top_level_key", "string_is_here")->valuestring);

	REQUIRE(ks_json_lookup(top_obj, 1, "blahasr") == NULL);
	REQUIRE(ks_json_lookup(top_obj, 3, "bhasd", "asdhsad", "asdsah") == NULL);

	auto lookup = [&](ks_json_t *obj, int components, ...)->ks_json_t * {
		va_list argptr;
		va_start(argptr, components);

		auto item = ks_json_valookup(obj, components, argptr);

		va_end(argptr);

		return item;
	};

	REQUIRE(lookup(top_obj, 0)  == top_obj);
	REQUIRE(lookup(top_obj, 1, "top_level_key") == sub_obj);
	REQUIRE("You found me!"s == lookup(top_obj, 2, "top_level_key", "string_is_here")->valuestring);

	REQUIRE(lookup(top_obj, 1, "blahasr") == NULL);
	REQUIRE(lookup(top_obj, 3, "bhasd", "asdhsad", "asdsah") == NULL);

	REQUIRE("You found me!"s == ks_json_lookup_cstr(top_obj, 2, "top_level_key", "string_is_here"));
	REQUIRE(*ks_json_lookup_number_doubleptr(top_obj, 2, "top_level_key", "double_is_here") == 42.5);
	REQUIRE(*ks_json_lookup_number_intptr(top_obj, 2, "top_level_key", "int_is_here") == 42);
	REQUIRE(ks_json_lookup_array_item(top_obj, 0, 2, "top_level_key", "array_is_here")->valuedouble == 42.5);
	REQUIRE(ks_json_lookup_array_item(top_obj, 1, 2, "top_level_key", "array_is_here")->valueint == 42);
	REQUIRE("I live in an array"s == ks_json_lookup_array_item(top_obj, 2, 2, "top_level_key", "array_is_here")->valuestring);

	auto print = ks_json_lookup_print(top_obj, 2, "top_level_key", "array_is_here");
	REQUIRE(print);
	REQUIRE("[42.5, 42, \"I live in an array\"]"s == print);

	ks_json_free(&print);

	print = ks_json_lookup_print_unformatted(top_obj, 2, "top_level_key", "array_is_here");
	REQUIRE(print);
	REQUIRE("[42.5,42,\"I live in an array\"]"s == print);
	ks_json_free(&print);

	// ks_json_lookup_uuid
	REQUIRE(ks_json_lookup_uuid(top_obj, 2, "top_level_key", "uuid_is_here") == uuid);
}
