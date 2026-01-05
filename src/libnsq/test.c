#include "nsq.h"

static void message_handler(struct NSQReader *rdr, struct NSQDConnection *conn, struct NSQMessage *msg, void *ctx)
{
    _DEBUG("%s: %lld, %d, %s, %lu, %.*s\n", __FUNCTION__, msg->timestamp, msg->attempts, msg->id,
           msg->body_length, (int)msg->body_length, msg->body);
    int ret = 0;
    //TestNsqMsgContext * test_ctx = (TestNsqMsgContext *)ctx;
    //int ret= ctx->process(msg->body, msg->body_length);

    buffer_reset(conn->command_buf);

    if (ret < 0) {
        nsq_requeue(conn->command_buf, msg->id, 100);
    } else {
        nsq_finish(conn->command_buf, msg->id);
    }
    buffered_socket_write_buffer(conn->bs, conn->command_buf);

    buffer_reset(conn->command_buf);
    nsq_ready(conn->command_buf, rdr->max_in_flight);
    buffered_socket_write_buffer(conn->bs, conn->command_buf);

    free_nsq_message(msg);
}

int main(int argc, char **argv)
{
    if (argc < 4) {
        printf("not enough args from command line\n");
        return 1;
    }
    struct NSQReader *rdr;
    struct ev_loop *loop;
    void *ctx = NULL; //(void *)(new TestNsqMsgContext());

    loop = ev_default_loop(0);
    rdr = new_nsq_reader(loop, argv[2], argv[3], ctx, NULL, NULL, NULL, message_handler);

#ifdef NSQD_STANDALONE
    nsq_reader_connect_to_nsqd(rdr, argv[1], 4150);
//    nsq_reader_connect_to_nsqd(rdr, "127.0.0.1", 14150);
#else
    nsq_reader_add_nsqlookupd_endpoint(rdr, argv[1], 4161);
#endif
    nsq_run(loop);

    return 0;
}
