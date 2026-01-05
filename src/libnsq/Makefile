PREFIX=/usr/local
DESTDIR=
LIBDIR=${PREFIX}/lib
INCDIR=${PREFIX}/include

CFLAGS += --std=c99 -D_XOPEN_SOURCE=600 -g -O2 -DDEBUG -fPIC
CFLAGS += -Wall -Wextra -Wwrite-strings -Wshadow -Wno-unused-parameter
LIBS=-lev -lcurl
AR=ar
AR_FLAGS=rc
RANLIB=ranlib

ifeq (1, $(WITH_JANSSON))
LIBS+=-ljansson
CFLAGS+=-DWITH_JANSSON
else
LIBS+=-ljson-c
endif

LIBNSQ_HEADERS = \
nsq.h \
http.h

LIBNSQ_SOURCES = \
command.c \
message.c \
reader.c \
http.c \
json.c \
nsqlookupd.c \
nsqd_connection.c \
buffer.c \
buffered_socket.c

all: libnsq
libnsq: libnsq.a

%.o: %.c
	$(CC) -o $@ -c $< -I. $(CFLAGS)

libnsq.a: $(patsubst %.c, %.o, ${LIBNSQ_SOURCES})
	$(AR) $(AR_FLAGS) $@ $^
	$(RANLIB) $@

fmt: ${LIBNSQ_SOURCES} ${LIBNSQ_HEADERS}
	astyle --style=1tbs \
		--lineend=linux \
		--convert-tabs \
		--preserve-date \
		--pad-header \
		--indent-switches \
		--align-pointer=name \
		--align-reference=name \
		--pad-oper \
		-n $^

test: test-nsqd test-lookupd test-evbuffsock

test-nsqd: test.c libnsq.a
	$(CC) -o $@ $^ -I. $(CFLAGS) $(LIBS) -DNSQD_STANDALONE

test-lookupd: test.c libnsq.a
	$(CC) -o $@ $^ -I. $(CFLAGS) $(LIBS)

test-evbuffsock: test_evbuffsock.c buffer.c
	$(CC) -o $@ $^ $(CFLAGS) -lev

clean:
	rm -rvf libnsq.a test-nsqd test-lookupd test-evbuffsock *.dSYM *.o

.PHONY: install clean all test

install:
	install -m 755 -d ${DESTDIR}${INCDIR}
	install -m 755 -d ${DESTDIR}${LIBDIR}
	install -m 755 libnsq.a ${DESTDIR}${LIBDIR}/libnsq.a
	install -m 755 nsq.h ${DESTDIR}${INCDIR}/nsq.h
	install -m 755 evbuffsock.h ${DESTDIR}${INCDIR}/evbuffsock.h
