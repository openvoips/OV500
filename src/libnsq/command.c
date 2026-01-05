#include "nsq.h"

#include <assert.h>
#include <string.h>
#include <stdio.h>
#include <stdlib.h>


static void nsq_buffer_add(struct Buffer *buf, const char *name,
                           const struct NSQCmdParams params[], size_t psize,
                           const char *body, const size_t body_length)
{
    char b[64];
    size_t l;

    buffer_add(buf, name, strlen(name));

    if (NULL != params) {
        for (size_t i = 0; i < psize; i++) {
            buffer_add(buf, " ", 1);

            switch (params[i].t) {
                case NSQ_PARAM_TYPE_INT:
                    l = sprintf(b, "%d", *((int *)params[i].v));
                    buffer_add(buf, b, l);
                    break;
                case NSQ_PARAM_TYPE_CHAR:
                    buffer_add(buf, params[i].v, strlen((char *)params[i].v));
                    break;
            }
        }
    }
    buffer_add(buf, "\n", 1);

    if (NULL != body) {
        uint32_t vv = htonl((uint32_t)body_length);
        buffer_add(buf, &vv, 4);
        buffer_add(buf, body, body_length);
    }
}

void nsq_subscribe(struct Buffer *buf, const char *topic, const char *channel)
{
    const char *name = "SUB";
    const struct NSQCmdParams params[2] = {
        {(void *)topic, NSQ_PARAM_TYPE_CHAR},
        {(void *)channel, NSQ_PARAM_TYPE_CHAR},
    };
    nsq_buffer_add(buf, name, params, 2, NULL, 0);
}

void nsq_ready(struct Buffer *buf, int count)
{
    const char *name = "RDY";
    const struct NSQCmdParams params[1] = {
        {&count, NSQ_PARAM_TYPE_INT},
    };
    nsq_buffer_add(buf, name, params, 1, NULL, 0);
}

void nsq_finish(struct Buffer *buf, const char *id)
{
    const char *name = "FIN";
    const struct NSQCmdParams params[1] = {
        {(void *)id, NSQ_PARAM_TYPE_CHAR},
    };
    nsq_buffer_add(buf, name, params, 1, NULL, 0);
}

void nsq_requeue(struct Buffer *buf, const char *id, int timeout_ms)
{
    const char *name = "REQ";
    const struct NSQCmdParams params[2] = {
        {(void *)id, NSQ_PARAM_TYPE_CHAR},
        {&timeout_ms, NSQ_PARAM_TYPE_INT},
    };
    nsq_buffer_add(buf, name, params, 2, NULL, 0);
}

void nsq_nop(struct Buffer *buf)
{
    nsq_buffer_add(buf, "NOP", NULL, 0, NULL, 0);
}

void nsq_publish(struct Buffer *buf, const char *topic, const char *body)
{
    const char *name = "PUB";
    const struct NSQCmdParams params[1] = {
        {(void *)topic, NSQ_PARAM_TYPE_CHAR},
    };
    nsq_buffer_add(buf, name, params, 1, body, strlen(body));
}

void nsq_defer_publish(struct Buffer *buf, const char *topic, const char *body, int defer_time_sec)
{
    const char *name = "DPUB";
    const struct NSQCmdParams params[2] = {
        {(void *)topic, NSQ_PARAM_TYPE_CHAR},
        {&defer_time_sec, NSQ_PARAM_TYPE_INT},
    };
    nsq_buffer_add(buf, name, params, 2, body, strlen(body));
}

void nsq_multi_publish(struct Buffer *buf, const char *topic, const char **body, const size_t body_size)
{
    const char *name = "MPUB";
    const struct NSQCmdParams params[1] = {
        {(void *)topic, NSQ_PARAM_TYPE_CHAR},
    };

    size_t s = 4;
    for (size_t i = 0; i < body_size; i++) {
        s += strlen(body[i]) + 4;
    }
    char *b = malloc(s * sizeof(char));
    assert(NULL != b);

    size_t n = 0;
    uint32_t v = 0;
    v = htonl((uint32_t)body_size);
    memcpy(b + n, &v, 4);
    n += 4;

    size_t l = 0;
    for (size_t i = 0; i < body_size; i++) {
        l = strlen(body[i]);
        v = htonl((uint32_t)l);
        memcpy(b + n, &v, 4);
        n += 4;

        l = strlen(body[i]);
        memcpy(b + n, body[i], l);
        n += l;
    }

    nsq_buffer_add(buf, name, params, 1, b, s);
}

void nsq_touch(struct Buffer *buf, const char *id)
{
    const char *name = "TOUCH";
    const struct NSQCmdParams params[1] = {
        {(void *)id, NSQ_PARAM_TYPE_CHAR},
    };
    nsq_buffer_add(buf, name, params, 1, NULL, 0);
}

void nsq_cleanly_close_connection(struct Buffer *buf)
{
    const char *name = "CLS";
    nsq_buffer_add(buf, name, NULL, 0, NULL, 0);
}

void nsq_auth(struct Buffer *buf, const char *secret)
{
    const char *name = "AUTH";
    nsq_buffer_add(buf, name, NULL, 0, secret, strlen(secret));
}

//TODO: should handle object to json string
void nsq_identify(struct Buffer *buf, const char *json_body)
{
    const char *name = "IDENTIFY";
    nsq_buffer_add(buf, name, NULL, 1, json_body, strlen(json_body));
}
