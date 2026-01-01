#ifndef __json_h
#define __json_h

#ifdef WITH_JANSSON

#include <jansson.h>

typedef json_t      nsq_json_t;
typedef size_t      nsq_json_size_t;
typedef json_int_t  nsq_json_int_t;
typedef void        nsq_json_tokener_t;

#else

#include <json-c/json.h>

typedef struct json_object     nsq_json_t;
typedef size_t                 nsq_json_size_t;
typedef int32_t                nsq_json_int_t;
typedef struct json_tokener    nsq_json_tokener_t;

#endif

nsq_json_tokener_t *nsq_json_tokener_new();
void nsq_json_tokener_free(nsq_json_tokener_t *jstok);
int nsq_json_decref(nsq_json_t *jsobj);
nsq_json_t *nsq_json_loadb(const char *buf, const nsq_json_size_t buflen, const size_t flags, nsq_json_tokener_t *jstok);
nsq_json_size_t nsq_json_array_length(nsq_json_t *array);
nsq_json_t *nsq_json_array_get(nsq_json_t *array, const nsq_json_size_t idx);
int nsq_json_object_get(nsq_json_t *jsobj, const char *key, nsq_json_t **value);
const char *nsq_json_string_value(nsq_json_t *jsojb);
nsq_json_int_t nsq_json_int_value(nsq_json_t *jsobj);

#endif /* ifndef __json_h */
