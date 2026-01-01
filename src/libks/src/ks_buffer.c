/*
 * Copyright (c) 2018-2023 SignalWire, Inc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */


#include "libks/ks.h"

static unsigned buffer_id = 0;

struct ks_buffer {
	unsigned char *data;
	unsigned char *head;
	ks_size_t used;
	ks_size_t actually_used;
	ks_size_t datalen;
	ks_size_t max_len;
	ks_size_t blocksize;
	unsigned id;
	int loops;
};


KS_DECLARE(ks_status_t) ks_buffer_create(ks_buffer_t **buffer, ks_size_t blocksize, ks_size_t start_len, ks_size_t max_len)
{
	ks_buffer_t *new_buffer;

	new_buffer = malloc(sizeof(*new_buffer));
	if (new_buffer) {
		memset(new_buffer, 0, sizeof(*new_buffer));

		if (start_len) {
			new_buffer->data = malloc(start_len);
			if (!new_buffer->data) {
				free(new_buffer);
				return KS_STATUS_FAIL;
			}
			memset(new_buffer->data, 0, start_len);
		}

		new_buffer->max_len = max_len;
		new_buffer->datalen = start_len;
		new_buffer->id = buffer_id++;
		new_buffer->blocksize = blocksize;
		new_buffer->head = new_buffer->data;

		*buffer = new_buffer;
		return KS_STATUS_SUCCESS;
	}

	return KS_STATUS_FAIL;
}

KS_DECLARE(ks_size_t) ks_buffer_len(ks_buffer_t *buffer)
{

	ks_assert(buffer != NULL);

	return buffer->datalen;

}


KS_DECLARE(ks_size_t) ks_buffer_freespace(ks_buffer_t *buffer)
{
	ks_assert(buffer != NULL);

	if (buffer->max_len) {
		return (ks_size_t) (buffer->max_len - buffer->used);
	}
	return 1000000;

}

KS_DECLARE(ks_size_t) ks_buffer_inuse(ks_buffer_t *buffer)
{
	ks_assert(buffer != NULL);

	return buffer->used;
}

KS_DECLARE(ks_size_t) ks_buffer_seek(ks_buffer_t *buffer, ks_size_t datalen)
{
	ks_size_t reading = 0;

	ks_assert(buffer != NULL);

	if (buffer->used < 1) {
		buffer->used = 0;
		return 0;
	} else if (buffer->used >= datalen) {
		reading = datalen;
	} else {
		reading = buffer->used;
	}

	buffer->used = buffer->actually_used - reading;
	buffer->head = buffer->data + reading;

	return reading;
}

KS_DECLARE(ks_size_t) ks_buffer_toss(ks_buffer_t *buffer, ks_size_t datalen)
{
	ks_size_t reading = 0;

	ks_assert(buffer != NULL);

	if (buffer->used < 1) {
		buffer->used = 0;
		return 0;
	} else if (buffer->used >= datalen) {
		reading = datalen;
	} else {
		reading = buffer->used;
	}

	buffer->used -= reading;
	buffer->head += reading;

	return buffer->used;
}

KS_DECLARE(void) ks_buffer_set_loops(ks_buffer_t *buffer, int loops)
{
	buffer->loops = loops;
}

KS_DECLARE(ks_size_t) ks_buffer_read_loop(ks_buffer_t *buffer, void *data, ks_size_t datalen)
{
	ks_size_t len;
	if ((len = ks_buffer_read(buffer, data, datalen)) < datalen) {
		if (buffer->loops == 0) {
			return len;
		}
		buffer->head = buffer->data;
		buffer->used = buffer->actually_used;
		len = ks_buffer_read(buffer, (char *) data + len, datalen - len);
		buffer->loops--;
	}
	return len;
}

KS_DECLARE(ks_size_t) ks_buffer_read(ks_buffer_t *buffer, void *data, ks_size_t datalen)
{
	ks_size_t reading = 0;

	ks_assert(buffer != NULL);
	ks_assert(buffer->head != NULL);
	ks_assert(data != NULL);


	if (buffer->used < 1) {
		buffer->used = 0;
		return 0;
	} else if (buffer->used >= datalen) {
		reading = datalen;
	} else {
		reading = buffer->used;
	}

	memcpy(data, buffer->head, reading);
	buffer->used -= reading;
	buffer->head += reading;

	/* if (buffer->id == 4) printf("%u o %d = %d\n", buffer->id, (unsigned)reading, (unsigned)buffer->used); */
	return reading;
}


KS_DECLARE(ks_size_t) ks_buffer_packet_count(ks_buffer_t *buffer)
{
	char *pe, *p, *e, *head = (char *) buffer->head;
	ks_size_t x = 0;

	ks_assert(buffer != NULL);

	e = (head + buffer->used);

	for (p = head; p && p < e && *p; p++) {
		if (*p == '\n') {
			pe = p + 1;
			if (*pe == '\r')
				pe++;
			if (pe <= e && *pe == '\n') {
				p = pe++;
				x++;
			}
		}
	}

	return x;
}

KS_DECLARE(ks_size_t) ks_buffer_read_packet(ks_buffer_t *buffer, void *data, ks_size_t maxlen)
{
	char *pe, *p, *e, *head = (char *) buffer->head;
	ks_size_t datalen = 0;

	ks_assert(buffer != NULL);
	ks_assert(data != NULL);

	e = (head + buffer->used);

	for (p = head; p && p < e && *p; p++) {
		if (*p == '\n') {
			pe = p + 1;
			if (*pe == '\r')
				pe++;
			if (pe <= e && *pe == '\n') {
				pe++;
				datalen = pe - head;
				if (datalen > maxlen) {
					datalen = maxlen;
				}
				break;
			}
		}
	}

	return ks_buffer_read(buffer, data, datalen);
}

KS_DECLARE(ks_size_t) ks_buffer_write(ks_buffer_t *buffer, const void *data, ks_size_t datalen)
{
	ks_size_t freespace, actual_freespace;

	ks_assert(buffer != NULL);
	ks_assert(data != NULL);
	ks_assert(buffer->data != NULL);

	if (!datalen) {
		return buffer->used;
	}

	actual_freespace = buffer->datalen - buffer->actually_used;
	if (actual_freespace < datalen && (!buffer->max_len || (buffer->used + datalen <= buffer->max_len))) {
		memmove(buffer->data, buffer->head, buffer->used);
		buffer->head = buffer->data;
		buffer->actually_used = buffer->used;
	}

	freespace = buffer->datalen - buffer->used;

	/*
	   if (buffer->data != buffer->head) {
	   memmove(buffer->data, buffer->head, buffer->used);
	   buffer->head = buffer->data;
	   }
	 */

	if (freespace < datalen) {
		ks_size_t new_size, new_block_size;
		void *data1;

		new_size = buffer->datalen + datalen;
		new_block_size = buffer->datalen + buffer->blocksize;

		if (new_block_size > new_size) {
			new_size = new_block_size;
		}
		buffer->head = buffer->data;
		data1 = realloc(buffer->data, new_size);
		if (!data1) {
			return 0;
		}
		buffer->data = data1;
		buffer->head = buffer->data;
		buffer->datalen = new_size;
	}


	freespace = buffer->datalen - buffer->used;

	if (freespace < datalen) {
		return 0;
	} else {
		memcpy(buffer->head + buffer->used, data, datalen);
		buffer->used += datalen;
		buffer->actually_used += datalen;
	}
	/* if (buffer->id == 4) printf("%u i %d = %d\n", buffer->id, (unsigned)datalen, (unsigned)buffer->used); */

	return buffer->used;
}

KS_DECLARE(void) ks_buffer_zero(ks_buffer_t *buffer)
{
	ks_assert(buffer != NULL);
	ks_assert(buffer->data != NULL);

	buffer->used = 0;
	buffer->actually_used = 0;
	buffer->head = buffer->data;
}

KS_DECLARE(ks_size_t) ks_buffer_zwrite(ks_buffer_t *buffer, const void *data, ks_size_t datalen)
{
	ks_size_t w;

	if (!(w = ks_buffer_write(buffer, data, datalen))) {
		ks_buffer_zero(buffer);
		return ks_buffer_write(buffer, data, datalen);
	}

	return w;
}

KS_DECLARE(void) ks_buffer_destroy(ks_buffer_t **buffer)
{
	if (*buffer) {
		free((*buffer)->data);
		free(*buffer);
	}

	*buffer = NULL;
}

/* For Emacs:
 * Local Variables:
 * mode:c
 * indent-tabs-mode:t
 * tab-width:4
 * c-basic-offset:4
 * End:
 * For VIM:
 * vim:set softtabstop=4 shiftwidth=4 tabstop=4 noet:
 */
