#include <stdio.h>
#include <assert.h>
#include "evbuffsock.h"

int main(int argc, char **argv)
{
    struct Buffer *buf;

    buf = new_buffer(16, 64);
    buffer_add(buf, "asdf", 4);
    buffer_add(buf, "1234567890", 10);
    buffer_add(buf, "ghjk", 4);

    buffer_drain(buf, 8);
    buffer_add(buf, "1234567890", 10);
    buffer_add(buf, "!@#$%^", 6);

    assert(buf->length == 32);
    assert(strncmp("567890ghjk1234567890!@#$%^", buf->data, buf->offset) == 0);
    printf("OK\n");
    return 0;
}
