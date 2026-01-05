#include "json.h"

nsq_json_tokener_t *nsq_json_tokener_new()
{
#ifdef WITH_JANSSON
    return NULL;
#else
    return json_tokener_new();
#endif
}

void nsq_json_tokener_free(nsq_json_tokener_t *jstok)
{
#ifndef WITH_JANSSON
    json_tokener_free(jstok);
#endif
}

int nsq_json_decref(nsq_json_t *jsobj)
{
#ifdef WITH_JANSSON
    json_decref(jsobj);
    return 0;
#else
    return json_object_put(jsobj);
#endif
}

nsq_json_t *nsq_json_loadb(const char *buf, const nsq_json_size_t buflen, const size_t flags, nsq_json_tokener_t *jstok)
{
#ifdef WITH_JANSSON
    return json_loadb(buf, (size_t)buflen, flags, NULL);
#else
    return json_tokener_parse_ex(jstok, buf, (int)buflen);
#endif
}

nsq_json_size_t nsq_json_array_length(nsq_json_t *array)
{
#ifdef WITH_JANSSON
    return json_array_size(array);
#else
    return json_object_array_length(array);
#endif
}

nsq_json_t *nsq_json_array_get(nsq_json_t *array, const nsq_json_size_t idx)
{
#ifdef WITH_JANSSON
    return json_array_get(array, idx);
#else
    return json_object_array_get_idx(array, idx);
#endif
}

int nsq_json_object_get(nsq_json_t *jsobj, const char *key, nsq_json_t **value)
{
#ifdef WITH_JANSSON
    *value = json_object_get(jsobj, key);
    return *value != NULL;
#else
    return json_object_object_get_ex(jsobj, key, value);
#endif
}

const char *nsq_json_string_value(nsq_json_t *jsobj)
{
#ifdef WITH_JANSSON
    return json_string_value(jsobj);
#else
    return json_object_get_string(jsobj);
#endif
}

nsq_json_int_t nsq_json_int_value(nsq_json_t *jsobj)
{
#ifdef WITH_JANSSON
    return json_integer_value(jsobj);
#else
    return json_object_get_int(jsobj);
#endif
}
