// copied from github.com/mreiferson/libevbuffsock
// (same original author as libnsq)
#ifndef __buffered_socket_h
#define __buffered_socket_h

#include <fcntl.h>
#include <stdarg.h>
#include <sys/types.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <unistd.h>
#include <sys/socket.h>
#include <netdb.h>
#include <sys/ioctl.h>
#include <ev.h>

#define EVBUFFSOCK_VERSION "0.1.1"

#define BUFFER_HAS_DATA(b)  ((b)->offset)
#define BUFFER_USED(b)      ((b)->data - (b)->orig + (b)->offset)
#define BUFFER_AVAILABLE(b) ((b)->length - BUFFER_USED(b))

struct Buffer {
    char *data;
    char *orig;
    size_t offset;
    size_t length;
    size_t capacity;
};

struct Buffer *new_buffer(size_t length, size_t capacity);
void free_buffer(struct Buffer *buf);
void buffer_reset(struct Buffer *buf);
int buffer_add(struct Buffer *buf, const void *source, size_t length);
void buffer_drain(struct Buffer *buf, size_t length);
int buffer_read_fd(struct Buffer *buf, int fd);
int buffer_write_fd(struct Buffer *buf, int fd);
int buffer_expand(struct Buffer *buf, size_t need);

enum BufferedSocketStates {
    BS_INIT,
    BS_CONNECTING,
    BS_CONNECTED,
    BS_DISCONNECTED
};

struct BufferedSocket {
    char *address;
    int port;
    int fd;
    int state;
    struct ev_io read_ev;
    struct ev_io write_ev;
    struct Buffer *read_buf;
    struct Buffer *write_buf;
    struct ev_timer read_bytes_timer_ev;
    size_t read_bytes_n;
    void (*read_bytes_callback)(struct BufferedSocket *buffsock, void *arg);
    void *read_bytes_arg;
    struct ev_loop *loop;
    void (*connect_callback)(struct BufferedSocket *buffsock, void *arg);
    void (*close_callback)(struct BufferedSocket *buffsock, void *arg);
    void (*read_callback)(struct BufferedSocket *buffsock, struct Buffer *buf, void *arg);
    void (*write_callback)(struct BufferedSocket *buffsock, void *arg);
    void (*error_callback)(struct BufferedSocket *buffsock, void *arg);
    void *cbarg;
};

struct BufferedSocket *new_buffered_socket(struct ev_loop *loop, const char *address, int port,
        size_t read_buf_len, size_t read_buf_capacity, size_t write_buf_len, size_t write_buf_capacity,
        void (*connect_callback)(struct BufferedSocket *buffsock, void *arg),
        void (*close_callback)(struct BufferedSocket *buffsock, void *arg),
        void (*read_callback)(struct BufferedSocket *buffsock, struct Buffer *buf, void *arg),
        void (*write_callback)(struct BufferedSocket *buffsock, void *arg),
        void (*error_callback)(struct BufferedSocket *buffsock, void *arg),
        void *cbarg);
void free_buffered_socket(struct BufferedSocket *socket);
int buffered_socket_connect(struct BufferedSocket *buffsock);
void buffered_socket_close(struct BufferedSocket *socket);
size_t buffered_socket_write(struct BufferedSocket *buffsock, void *data, size_t len);
size_t buffered_socket_write_buffer(struct BufferedSocket *buffsock, struct Buffer *buf);
void buffered_socket_read_bytes(struct BufferedSocket *buffsock, size_t n,
                                void (*data_callback)(struct BufferedSocket *buffsock, void *arg), void *arg);

#endif
