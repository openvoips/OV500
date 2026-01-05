// copied from github.com/mreiferson/libevbuffsock
// (same original author as libnsq)
#include "evbuffsock.h"

#ifdef DEBUG
#define _DEBUG(...) fprintf(stdout, __VA_ARGS__)
#else
#define _DEBUG(...) do {;} while (0)
#endif

struct Buffer *new_buffer(size_t length, size_t capacity)
{
    struct Buffer *buf;

    buf = malloc(sizeof(struct Buffer));
    buf->orig = malloc(length);
    buf->data = buf->orig;
    buf->offset = 0;
    buf->length = length;
    buf->capacity = capacity;

    return buf;
}

void free_buffer(struct Buffer *buf)
{
    if (buf) {
        free(buf->orig);
        free(buf);
    }
}

void buffer_reset(struct Buffer *buf)
{
    buf->data = buf->orig;
    buf->offset = 0;
}

int buffer_expand(struct Buffer *buf, size_t need)
{
    size_t pos = buf->data - buf->orig;
    size_t expand = 0;
    size_t new_size = 0;

    _DEBUG("%s: need %lu, pos %lu\n", __FUNCTION__, need, pos);

    if (need <= pos) {
        _DEBUG("%s: re-aligning\n", __FUNCTION__);
        // re-align
        memmove(buf->orig, buf->data, buf->offset);
        buf->data = buf->orig;
        return 1;
    }

    expand = buf->length;
    while (expand < need) {
        expand = expand * 2;
    }

    _DEBUG("%s: expanding by %lu\n", __FUNCTION__, expand);

    new_size = buf->length + expand;
    if (buf->capacity > 0 && new_size > buf->capacity) {
        return 0;
    }

    buf->orig = realloc(buf->orig, new_size);
    buf->data = buf->orig + pos;
    buf->length = new_size;

    return 1;
}

int buffer_add(struct Buffer *buf, const void *source, size_t length)
{
    size_t used = buf->data - buf->orig + buf->offset;
    int32_t need = used + length - buf->length;

    _DEBUG("%s: adding %lu - used %lu, need %d\n", __FUNCTION__, length, used, need);

    if (need > 0) {
        if (!buffer_expand(buf, need)) {
            return 0;
        }
    }

    memcpy(buf->data + buf->offset, source, length);
    buf->offset += length;

    return 1;
}

void buffer_drain(struct Buffer *buf, size_t length)
{
    if (length >= buf->offset) {
        buffer_reset(buf);
    } else {
        buf->data += length;
        buf->offset -= length;
    }
}

int buffer_read_fd(struct Buffer *buf, int fd)
{
    int n;
    int need;

    if (ioctl(fd, FIONREAD, &n) == -1 || n > 4096) {
        n = 4096;
    }

    need = n - BUFFER_AVAILABLE(buf);
    if (need > 0 && !buffer_expand(buf, need)) {
        return -1;
    }

    n = recv(fd, buf->data + buf->offset, n, 0);
    if (n > 0) {
        buf->offset += n;
    }

    return n;
}

int buffer_write_fd(struct Buffer *buf, int fd)
{
    int n;
    n = send(fd, buf->data, buf->offset, 0);
    if (n > 0) {
        buffer_drain(buf, n);
    }
    return n;
}
