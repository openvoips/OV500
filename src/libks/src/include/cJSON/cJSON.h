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

#ifndef cJSON__h
#define cJSON__h

#ifdef __cplusplus
extern "C"
{
#endif

/* project version */
#define CJSON_VERSION_MAJOR 1
#define CJSON_VERSION_MINOR 7
#define CJSON_VERSION_PATCH 6

#include <stddef.h>

/* kJSON Types: */
typedef enum {
	kJSON_Invalid = (0),
	kJSON_False   =  (1 << 0),
	kJSON_True    =  (1 << 1),
	kJSON_NULL    =  (1 << 2),
	kJSON_Number  = (1 << 3),
	kJSON_String  = (1 << 4),
	kJSON_Array   = (1 << 5),
	kJSON_Object  = (1 << 6),
	kJSON_Raw     = (1 << 7) /* raw json */
} kJSON_TYPES;

#define kJSON_IsReference 256
#define kJSON_StringIsConst 512

/* The kJSON structure: */
typedef struct kJSON
{
    /* next/prev allow you to walk array/object chains. Alternatively, use GetArraySize/GetArrayItem/GetObjectItem */
    struct kJSON *next;
    struct kJSON *prev;
    /* An array or object item will have a child pointer pointing to a chain of the items in the array/object. */
    struct kJSON *child;

    /* The type of the item, as above. */
    kJSON_TYPES type;

    /* The item's string, if type==kJSON_String  and type == kJSON_Raw */
    char *valuestring;
    /* writing to valueint is DEPRECATED, use kJSON_SetNumberValue instead */
    int valueint;
    /* The item's number, if type==kJSON_Number */
    double valuedouble;

    /* The item's name string, if this item is the child of, or is in the list of subitems of an object. */
    char *string;
} kJSON;

typedef struct kJSON_Hooks
{
      void *(*malloc_fn)(size_t sz);
      void (*free_fn)(void *ptr);
      void *(*realloc_fn)(void *, size_t sz);
} kJSON_Hooks;

typedef int kJSON_bool;

#if !defined(__WINDOWS__) && (defined(WIN32) || defined(WIN64) || defined(_MSC_VER) || defined(_WIN32))
#define __WINDOWS__
#endif
#ifdef __WINDOWS__

/* When compiling for windows, we specify a specific calling convention to avoid issues where we are being called from a project with a different default calling convention.  For windows you have 2 define options:

CJSON_HIDE_SYMBOLS - Define this in the case where you don't want to ever dllexport symbols
CJSON_EXPORT_SYMBOLS - Define this on library build when you want to dllexport symbols (default)
CJSON_IMPORT_SYMBOLS - Define this if you want to dllimport symbol

For *nix builds that support visibility attribute, you can define similar behavior by

setting default visibility to hidden by adding
-fvisibility=hidden (for gcc)
or
-xldscope=hidden (for sun cc)
to CFLAGS

then using the CJSON_API_VISIBILITY flag to "export" the same symbols the way CJSON_EXPORT_SYMBOLS does

*/

/* export symbols by default, this is necessary for copy pasting the C and header file */
#if !defined(CJSON_HIDE_SYMBOLS) && !defined(CJSON_IMPORT_SYMBOLS) && !defined(CJSON_EXPORT_SYMBOLS)
#define CJSON_EXPORT_SYMBOLS
#endif

#if defined(CJSON_HIDE_SYMBOLS)
#define CJSON_PUBLIC(type)   type __stdcall
#elif defined(CJSON_EXPORT_SYMBOLS)
#define CJSON_PUBLIC(type)   __declspec(dllexport) type __stdcall
#elif defined(CJSON_IMPORT_SYMBOLS)
#define CJSON_PUBLIC(type)   __declspec(dllimport) type __stdcall
#endif
#else /* !WIN32 */
#if (defined(__GNUC__) || defined(__SUNPRO_CC) || defined (__SUNPRO_C)) && defined(CJSON_API_VISIBILITY)
#define CJSON_PUBLIC(type)   __attribute__((visibility("default"))) type
#else
#define CJSON_PUBLIC(type) type
#endif
#endif

/* Limits how deeply nested arrays/objects can be before kJSON rejects to parse them.
 * This is to prevent stack overflows. */
#ifndef CJSON_NESTING_LIMIT
#define CJSON_NESTING_LIMIT 1000
#endif

/* returns the version of kJSON as a string */
CJSON_PUBLIC(const char*) kJSON_Version(void);

/* Supply malloc, realloc and free functions to kJSON */
CJSON_PUBLIC(void) kJSON_InitHooks(kJSON_Hooks* hooks);

/* Memory Management: the caller is always responsible to free the results from all variants of kJSON_Parse (with kJSON_Delete) and kJSON_Print (with stdlib free, kJSON_Hooks.free_fn, or kJSON_free as appropriate). The exception is kJSON_PrintPreallocated, where the caller has full responsibility of the buffer. */
/* Supply a block of JSON, and this returns a kJSON object you can interrogate. */
CJSON_PUBLIC(kJSON *) kJSON_Parse(const char *value);
/* ParseWithOpts allows you to require (and check) that the JSON is null terminated, and to retrieve the pointer to the final byte parsed. */
/* If you supply a ptr in return_parse_end and parsing fails, then return_parse_end will contain a pointer to the error so will match kJSON_GetErrorPtr(). */
CJSON_PUBLIC(kJSON *) kJSON_ParseWithOpts(const char *value, const char **return_parse_end, kJSON_bool require_null_terminated);

/* Render a kJSON entity to text for transfer/storage. */
CJSON_PUBLIC(char *) kJSON_Print(const kJSON *item);
/* Render a kJSON entity to text for transfer/storage without any formatting. */
CJSON_PUBLIC(char *) kJSON_PrintUnformatted(const kJSON *item);
/* Render a kJSON entity to text using a buffered strategy. prebuffer is a guess at the final size. guessing well reduces reallocation. fmt=0 gives unformatted, =1 gives formatted */
CJSON_PUBLIC(char *) kJSON_PrintBuffered(const kJSON *item, int prebuffer, kJSON_bool fmt);
/* Render a kJSON entity to text using a buffer already allocated in memory with given length. Returns 1 on success and 0 on failure. */
/* NOTE: kJSON is not always 100% accurate in estimating how much memory it will use, so to be safe allocate 5 bytes more than you actually need */
CJSON_PUBLIC(kJSON_bool) kJSON_PrintPreallocated(kJSON *item, char *buffer, const int length, const kJSON_bool format);
/* Delete a kJSON entity and all subentities. */
CJSON_PUBLIC(void) kJSON_Delete(kJSON *c);

/* Returns the number of items in an array (or object). */
CJSON_PUBLIC(int) kJSON_GetArraySize(const kJSON *array);
/* Retrieve item number "index" from array "array". Returns NULL if unsuccessful. */
CJSON_PUBLIC(kJSON *) kJSON_GetArrayItem(const kJSON *array, int index);
/* Get item "string" from object. Case insensitive. */
CJSON_PUBLIC(kJSON *) kJSON_GetObjectItem(const kJSON * const object, const char * const string);
CJSON_PUBLIC(kJSON *) kJSON_GetObjectItemCaseSensitive(const kJSON * const object, const char * const string);
CJSON_PUBLIC(kJSON_bool) kJSON_HasObjectItem(const kJSON *object, const char *string);
/* For analysing failed parses. This returns a pointer to the parse error. You'll probably need to look a few chars back to make sense of it. Defined when kJSON_Parse() returns 0. 0 when kJSON_Parse() succeeds. */
CJSON_PUBLIC(const char *) kJSON_GetErrorPtr(void);

/* Check if the item is a string and return its valuestring */
CJSON_PUBLIC(char *) kJSON_GetStringValue(kJSON *item);

/* These functions check the type of an item */
CJSON_PUBLIC(kJSON_bool) kJSON_IsInvalid(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsFalse(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsTrue(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsBool(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsNull(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsNumber(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsString(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsArray(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsObject(const kJSON * const item);
CJSON_PUBLIC(kJSON_bool) kJSON_IsRaw(const kJSON * const item);

/* These calls create a kJSON item of the appropriate type. */
CJSON_PUBLIC(kJSON *) kJSON_CreateNull(void);
CJSON_PUBLIC(kJSON *) kJSON_CreateTrue(void);
CJSON_PUBLIC(kJSON *) kJSON_CreateFalse(void);
CJSON_PUBLIC(kJSON *) kJSON_CreateBool(kJSON_bool boolean);
CJSON_PUBLIC(kJSON *) kJSON_CreateNumber(double num);
CJSON_PUBLIC(kJSON *) kJSON_CreateString(const char *string);
/* raw json */
CJSON_PUBLIC(kJSON *) kJSON_CreateRaw(const char *raw);
CJSON_PUBLIC(kJSON *) kJSON_CreateArray(void);
CJSON_PUBLIC(kJSON *) kJSON_CreateObject(void);

/* Create a string where valuestring references a string so
 * it will not be freed by kJSON_Delete */
CJSON_PUBLIC(kJSON *) kJSON_CreateStringReference(const char *string);
/* Create an object/arrray that only references it's elements so
 * they will not be freed by kJSON_Delete */
CJSON_PUBLIC(kJSON *) kJSON_CreateObjectReference(const kJSON *child);
CJSON_PUBLIC(kJSON *) kJSON_CreateArrayReference(const kJSON *child);

/* These utilities create an Array of count items. */
CJSON_PUBLIC(kJSON *) kJSON_CreateIntArray(const int *numbers, int count);
CJSON_PUBLIC(kJSON *) kJSON_CreateFloatArray(const float *numbers, int count);
CJSON_PUBLIC(kJSON *) kJSON_CreateDoubleArray(const double *numbers, int count);
CJSON_PUBLIC(kJSON *) kJSON_CreateStringArray(const char **strings, int count);

/* Append item to the specified array/object. */
CJSON_PUBLIC(void) kJSON_AddItemToArray(kJSON *array, kJSON *item);
CJSON_PUBLIC(void) kJSON_AddItemToObject(kJSON *object, const char *string, kJSON *item);
/* Use this when string is definitely const (i.e. a literal, or as good as), and will definitely survive the kJSON object.
 * WARNING: When this function was used, make sure to always check that (item->type & kJSON_StringIsConst) is zero before
 * writing to `item->string` */
CJSON_PUBLIC(void) kJSON_AddItemToObjectCS(kJSON *object, const char *string, kJSON *item);
/* Append reference to item to the specified array/object. Use this when you want to add an existing kJSON to a new kJSON, but don't want to corrupt your existing kJSON. */
CJSON_PUBLIC(void) kJSON_AddItemReferenceToArray(kJSON *array, kJSON *item);
CJSON_PUBLIC(void) kJSON_AddItemReferenceToObject(kJSON *object, const char *string, kJSON *item);

/* Remove/Detatch items from Arrays/Objects. */
CJSON_PUBLIC(kJSON *) kJSON_DetachItemViaPointer(kJSON *parent, kJSON * const item);
CJSON_PUBLIC(kJSON *) kJSON_DetachItemFromArray(kJSON *array, int which);
CJSON_PUBLIC(void) kJSON_DeleteItemFromArray(kJSON *array, int which);
CJSON_PUBLIC(kJSON *) kJSON_DetachItemFromObject(kJSON *object, const char *string);
CJSON_PUBLIC(kJSON *) kJSON_DetachItemFromObjectCaseSensitive(kJSON *object, const char *string);
CJSON_PUBLIC(void) kJSON_DeleteItemFromObject(kJSON *object, const char *string);
CJSON_PUBLIC(void) kJSON_DeleteItemFromObjectCaseSensitive(kJSON *object, const char *string);

/* Update array items. */
CJSON_PUBLIC(void) kJSON_InsertItemInArray(kJSON *array, int which, kJSON *newitem); /* Shifts pre-existing items to the right. */
CJSON_PUBLIC(kJSON_bool) kJSON_ReplaceItemViaPointer(kJSON * const parent, kJSON * const item, kJSON * replacement);
CJSON_PUBLIC(void) kJSON_ReplaceItemInArray(kJSON *array, int which, kJSON *newitem);
CJSON_PUBLIC(void) kJSON_ReplaceItemInObject(kJSON *object,const char *string,kJSON *newitem);
CJSON_PUBLIC(void) kJSON_ReplaceItemInObjectCaseSensitive(kJSON *object,const char *string,kJSON *newitem);

/* Duplicate a kJSON item */
CJSON_PUBLIC(kJSON *) kJSON_Duplicate(const kJSON *item, kJSON_bool recurse);
/* Duplicate will create a new, identical kJSON item to the one you pass, in new memory that will
need to be released. With recurse!=0, it will duplicate any children connected to the item.
The item->next and ->prev pointers are always zero on return from Duplicate. */
/* Recursively compare two kJSON items for equality. If either a or b is NULL or invalid, they will be considered unequal.
 * case_sensitive determines if object keys are treated case sensitive (1) or case insensitive (0) */
CJSON_PUBLIC(kJSON_bool) kJSON_Compare(const kJSON * const a, const kJSON * const b, const kJSON_bool case_sensitive);


CJSON_PUBLIC(void) kJSON_Minify(char *json);

/* Helper functions for creating and adding items to an object at the same time.
 * They return the added item or NULL on failure. */
CJSON_PUBLIC(kJSON*) kJSON_AddNullToObject(kJSON * const object, const char * const name);
CJSON_PUBLIC(kJSON*) kJSON_AddTrueToObject(kJSON * const object, const char * const name);
CJSON_PUBLIC(kJSON*) kJSON_AddFalseToObject(kJSON * const object, const char * const name);
CJSON_PUBLIC(kJSON*) kJSON_AddBoolToObject(kJSON * const object, const char * const name, const kJSON_bool boolean);
CJSON_PUBLIC(kJSON*) kJSON_AddNumberToObject(kJSON * const object, const char * const name, const double number);
CJSON_PUBLIC(kJSON*) kJSON_AddStringToObject(kJSON * const object, const char * const name, const char * const string);
CJSON_PUBLIC(kJSON*) kJSON_AddRawToObject(kJSON * const object, const char * const name, const char * const raw);
CJSON_PUBLIC(kJSON*) kJSON_AddObjectToObject(kJSON * const object, const char * const name);
CJSON_PUBLIC(kJSON*) kJSON_AddArrayToObject(kJSON * const object, const char * const name);

/* When assigning an integer value, it needs to be propagated to valuedouble too. */
#define kJSON_SetIntValue(object, number) ((object) ? (object)->valueint = (object)->valuedouble = (number) : (number))
/* helper for the kJSON_SetNumberValue macro */
CJSON_PUBLIC(double) kJSON_SetNumberHelper(kJSON *object, double number);
#define kJSON_SetNumberValue(object, number) ((object != NULL) ? kJSON_SetNumberHelper(object, (double)number) : (number))

/* Macro for iterating over an array or object */
#define kJSON_ArrayForEach(element, array) for(element = (array != NULL) ? (array)->child : NULL; element != NULL; element = element->next)

/* malloc/free objects using the malloc/free functions that have been set with kJSON_InitHooks */
CJSON_PUBLIC(void *) kJSON_malloc(size_t size);
CJSON_PUBLIC(void) kJSON_free(void *object);

#ifdef __cplusplus
}
#endif

#endif
