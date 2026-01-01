## libnsq

async C client library for [NSQ][1]

### Status

This is currently pretty low-level, but functional.  The raw building blocks for communicating
asynchronously via the NSQ TCP protocol are in place as well as a basic "reader" abstraction that facilitates
subscribing and receiving messages via callback.

TODO:

 * maintaining `RDY` count automatically
 * feature negotiation
 * better abstractions for responding to messages in your handlers

### Build

`libnsq` depends on `json-c` by default, but you can choose `jansson` for replacement.

```sh
WITH_JANSSON=1 make
```

### Dependencies

 * [libev][2]
 * [libevbuffsock][3]
 * [json-c][4] or [jansson][5]

[1]: https://github.com/bitly/nsq
[2]: http://software.schmorp.de/pkg/libev
[3]: https://github.com/mreiferson/libevbuffsock
[4]: https://github.com/json-c/json-c
[5]: http://www.digip.org/jansson/
