#include "nsq.h"

#include <time.h>
#include <string.h>

#include "http.h"
#include "utlist.h"

#define DEFAULT_LOOKUPD_INTERVAL     5.
#define DEFAULT_COMMAND_BUF_LEN      4096
#define DEFAULT_COMMAND_BUF_CAPACITY 4096
#define DEFAULT_READ_BUF_LEN         16 * 1024
#define DEFAULT_READ_BUF_CAPACITY    16 * 1024
#define DEFAULT_WRITE_BUF_LEN        16 * 1024
#define DEFAULT_WRITE_BUF_CAPACITY   16 * 1024


static void nsq_reader_connect_cb(struct NSQDConnection *conn, void *arg)
{
    struct NSQReader *rdr = (struct NSQReader *)arg;

    _DEBUG("%s: %p\n", __FUNCTION__, rdr);

    if (rdr->connect_callback) {
        rdr->connect_callback(rdr, conn);
    }

    // subscribe
    buffer_reset(conn->command_buf);
    nsq_subscribe(conn->command_buf, rdr->topic, rdr->channel);
    buffered_socket_write_buffer(conn->bs, conn->command_buf);

    // send initial RDY
    buffer_reset(conn->command_buf);
    nsq_ready(conn->command_buf, rdr->max_in_flight);
    buffered_socket_write_buffer(conn->bs, conn->command_buf);
}

static void nsq_reader_msg_cb(struct NSQDConnection *conn, struct NSQMessage *msg, void *arg)
{
    struct NSQReader *rdr = (struct NSQReader *)arg;

    _DEBUG("%s: %p %p\n", __FUNCTION__, msg, rdr);

    if (rdr->msg_callback) {
        msg->id[sizeof(msg->id) - 1] = '\0';
        rdr->msg_callback(rdr, conn, msg, rdr->ctx);
    }
}

static void nsq_reader_close_cb(struct NSQDConnection *conn, void *arg)
{
    struct NSQReader *rdr = (struct NSQReader *)arg;

    _DEBUG("%s: %p\n", __FUNCTION__, rdr);

    if (rdr->close_callback) {
        rdr->close_callback(rdr, conn);
    }

    LL_DELETE(rdr->conns, conn);

    // There is no lookupd, try to reconnect to nsqd directly
    if (rdr->lookupd == NULL) {
        ev_timer_again(conn->loop, conn->reconnect_timer);
    } else {
        free_nsqd_connection(conn);
    }
}

void nsq_lookupd_request_cb(struct HttpRequest *req, struct HttpResponse *resp, void *arg);

static void nsq_reader_reconnect_cb(EV_P_ struct ev_timer *w, int revents)
{
    struct NSQDConnection *conn = (struct NSQDConnection *)w->data;
    struct NSQReader *rdr = (struct NSQReader *)conn->arg;

    if (rdr->lookupd == NULL) {
        _DEBUG("%s: There is no lookupd, try to reconnect to nsqd directly\n", __FUNCTION__);
        nsq_reader_connect_to_nsqd(rdr, conn->address, conn->port);
    }

    free_nsqd_connection(conn);
}

static void nsq_reader_lookupd_poll_cb(EV_P_ struct ev_timer *w, int revents)
{
    struct NSQReader *rdr = (struct NSQReader *)w->data;
    struct NSQLookupdEndpoint *nsqlookupd_endpoint;
    struct HttpRequest *req;
    int i, idx, count = 0;
    char buf[256];

    LL_FOREACH(rdr->lookupd, nsqlookupd_endpoint) {
        count++;
    }
    if (count == 0) {
        goto end;
    }
    idx = rand() % count;

    _DEBUG("%s: rdr %p (chose %d)\n", __FUNCTION__, rdr, idx);

    i = 0;
    LL_FOREACH(rdr->lookupd, nsqlookupd_endpoint) {
        if (i++ == idx) {
            sprintf(buf, "http://%s:%d/lookup?topic=%s", nsqlookupd_endpoint->address,
                    nsqlookupd_endpoint->port, rdr->topic);
            req = new_http_request(buf, nsq_lookupd_request_cb, rdr);
            http_client_get((struct HttpClient *)rdr->httpc, req);
            break;
        }
    }

end:
    ev_timer_again(rdr->loop, &rdr->lookupd_poll_timer);
}

struct NSQReader *new_nsq_reader(struct ev_loop *loop, const char *topic, const char *channel, void *ctx,
                                 struct NSQReaderCfg *cfg,
                                 void (*connect_callback)(struct NSQReader *rdr, struct NSQDConnection *conn),
                                 void (*close_callback)(struct NSQReader *rdr, struct NSQDConnection *conn),
                                 void (*msg_callback)(struct NSQReader *rdr, struct NSQDConnection *conn, struct NSQMessage *msg, void *ctx))
{
    struct NSQReader *rdr;

    rdr = (struct NSQReader *)malloc(sizeof(struct NSQReader));
    rdr->cfg = (struct NSQReaderCfg *)malloc(sizeof(struct NSQReaderCfg));
    if (cfg == NULL) {
        rdr->cfg->lookupd_interval     = DEFAULT_LOOKUPD_INTERVAL;
        rdr->cfg->command_buf_len      = DEFAULT_COMMAND_BUF_LEN;
        rdr->cfg->command_buf_capacity = DEFAULT_COMMAND_BUF_CAPACITY;
        rdr->cfg->read_buf_len         = DEFAULT_READ_BUF_LEN;
        rdr->cfg->read_buf_capacity    = DEFAULT_READ_BUF_CAPACITY;
        rdr->cfg->write_buf_len        = DEFAULT_WRITE_BUF_LEN;
        rdr->cfg->write_buf_capacity   = DEFAULT_WRITE_BUF_CAPACITY;
    } else {
        rdr->cfg->lookupd_interval     = cfg->lookupd_interval     <= 0 ? DEFAULT_LOOKUPD_INTERVAL     : cfg->lookupd_interval;
        rdr->cfg->command_buf_len      = cfg->command_buf_len      <= 0 ? DEFAULT_COMMAND_BUF_LEN      : cfg->command_buf_len;
        rdr->cfg->command_buf_capacity = cfg->command_buf_capacity <= 0 ? DEFAULT_COMMAND_BUF_CAPACITY : cfg->command_buf_capacity;
        rdr->cfg->read_buf_len         = cfg->read_buf_len         <= 0 ? DEFAULT_READ_BUF_LEN         : cfg->read_buf_len;
        rdr->cfg->read_buf_capacity    = cfg->read_buf_capacity    <= 0 ? DEFAULT_READ_BUF_CAPACITY    : cfg->read_buf_capacity;
        rdr->cfg->write_buf_len        = cfg->write_buf_len        <= 0 ? DEFAULT_WRITE_BUF_LEN        : cfg->write_buf_len;
        rdr->cfg->write_buf_capacity   = cfg->write_buf_capacity   <= 0 ? DEFAULT_WRITE_BUF_CAPACITY   : cfg->write_buf_capacity;
    }
    rdr->topic = strdup(topic);
    rdr->channel = strdup(channel);
    rdr->max_in_flight = 1;
    rdr->connect_callback = connect_callback;
    rdr->close_callback = close_callback;
    rdr->msg_callback = msg_callback;
    rdr->ctx = ctx;
    rdr->conns = NULL;
    rdr->lookupd = NULL;
    rdr->loop = loop;

    rdr->httpc = new_http_client(rdr->loop);

    return rdr;
}

void free_nsq_reader(struct NSQReader *rdr)
{
    struct NSQDConnection *conn;
    struct NSQLookupdEndpoint *nsqlookupd_endpoint;

    if (rdr) {
        // TODO: this should probably trigger disconnections and then keep
        // trying to clean up until everything upstream is finished
        LL_FOREACH(rdr->conns, conn) {
            nsqd_connection_disconnect(conn);
        }
        LL_FOREACH(rdr->lookupd, nsqlookupd_endpoint) {
            free_nsqlookupd_endpoint(nsqlookupd_endpoint);
        }
        free(rdr->topic);
        free(rdr->channel);
        free(rdr->cfg);
        free(rdr);
    }
}

int nsq_reader_add_nsqlookupd_endpoint(struct NSQReader *rdr, const char *address, int port)
{
    struct NSQLookupdEndpoint *nsqlookupd_endpoint;
    struct NSQDConnection *conn;

    if (rdr->lookupd == NULL) {
        // Stop reconnect timers, use lookupd timer instead
        LL_FOREACH(rdr->conns, conn) {
            nsqd_connection_stop_timer(conn);
        }

        ev_timer_init(&rdr->lookupd_poll_timer, nsq_reader_lookupd_poll_cb, 0., rdr->cfg->lookupd_interval);
        rdr->lookupd_poll_timer.data = rdr;
        ev_timer_again(rdr->loop, &rdr->lookupd_poll_timer);
    }

    nsqlookupd_endpoint = new_nsqlookupd_endpoint(address, port);
    LL_APPEND(rdr->lookupd, nsqlookupd_endpoint);

    return 1;
}

int nsq_reader_connect_to_nsqd(struct NSQReader *rdr, const char *address, int port)
{
    struct NSQDConnection *conn;
    int rc;

    conn = new_nsqd_connection(rdr->loop, address, port,
                               nsq_reader_connect_cb, nsq_reader_close_cb, nsq_reader_msg_cb, rdr);
    rc = nsqd_connection_connect(conn);
    if (rc > 0) {
        LL_APPEND(rdr->conns, conn);
    }

    if (rdr->lookupd == NULL) {
        nsqd_connection_init_timer(conn, nsq_reader_reconnect_cb);
    }

    return rc;
}

void nsq_run(struct ev_loop *loop)
{
    srand((unsigned)time(NULL));
    ev_loop(loop, 0);
}
