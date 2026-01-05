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
#ifndef KS_BUFFER_H
#define KS_BUFFER_H

#include "ks.h"

KS_BEGIN_EXTERN_C

/**
 * @defgroup ks_buffer Buffer Routines
 * @ingroup buffer
 * The purpose of this module is to make a plain buffering interface that can be used for read/write buffers
 * throughout the application.
 * @{
 */
struct ks_buffer;
typedef struct ks_buffer ks_buffer_t;

/*! \brief Allocate a new dynamic ks_buffer 
 * \param buffer returned pointer to the new buffer
 * \param blocksize length to realloc by as data is added
 * \param start_len ammount of memory to reserve initially
 * \param max_len length the buffer is allowed to grow to
 * \return status
 */
KS_DECLARE(ks_status_t) ks_buffer_create(ks_buffer_t **buffer, ks_size_t blocksize, ks_size_t start_len, ks_size_t max_len);

/*! \brief Get the length of a ks_buffer_t 
 * \param buffer any buffer of type ks_buffer_t
 * \return int size of the buffer.
 */
KS_DECLARE(ks_size_t) ks_buffer_len(ks_buffer_t *buffer);

/*! \brief Get the freespace of a ks_buffer_t 
 * \param buffer any buffer of type ks_buffer_t
 * \return int freespace in the buffer.
 */
KS_DECLARE(ks_size_t) ks_buffer_freespace(ks_buffer_t *buffer);

/*! \brief Get the in use amount of a ks_buffer_t 
 * \param buffer any buffer of type ks_buffer_t
 * \return int ammount of buffer curently in use
 */
KS_DECLARE(ks_size_t) ks_buffer_inuse(ks_buffer_t *buffer);

/*! \brief Read data from a ks_buffer_t up to the ammount of datalen if it is available.  Remove read data from buffer. 
 * \param buffer any buffer of type ks_buffer_t
 * \param data pointer to the read data to be returned
 * \param datalen amount of data to be returned
 * \return int ammount of data actually read
 */
KS_DECLARE(ks_size_t) ks_buffer_read(ks_buffer_t *buffer, void *data, ks_size_t datalen);

KS_DECLARE(ks_size_t) ks_buffer_read_packet(ks_buffer_t *buffer, void *data, ks_size_t maxlen);
KS_DECLARE(ks_size_t) ks_buffer_packet_count(ks_buffer_t *buffer);

/*! \brief Read data endlessly from a ks_buffer_t 
 * \param buffer any buffer of type ks_buffer_t
 * \param data pointer to the read data to be returned
 * \param datalen amount of data to be returned
 * \return int ammount of data actually read
 * \note Once you have read all the data from the buffer it will loop around.
 */
KS_DECLARE(ks_size_t) ks_buffer_read_loop(ks_buffer_t *buffer, void *data, ks_size_t datalen);

/*! \brief Assign a number of loops to read
 * \param buffer any buffer of type ks_buffer_t
 * \param loops the number of loops (-1 for infinite)
 */
KS_DECLARE(void) ks_buffer_set_loops(ks_buffer_t *buffer, int32_t loops);

/*! \brief Write data into a ks_buffer_t up to the length of datalen
 * \param buffer any buffer of type ks_buffer_t
 * \param data pointer to the data to be written
 * \param datalen amount of data to be written
 * \return int amount of buffer used after the write, or 0 if no space available
 */
KS_DECLARE(ks_size_t) ks_buffer_write(ks_buffer_t *buffer, const void *data, ks_size_t datalen);

/*! \brief Remove data from the buffer
 * \param buffer any buffer of type ks_buffer_t
 * \param datalen amount of data to be removed
 * \return int size of buffer, or 0 if unable to toss that much data
 */
KS_DECLARE(ks_size_t) ks_buffer_toss(ks_buffer_t *buffer, ks_size_t datalen);

/*! \brief Remove all data from the buffer
 * \param buffer any buffer of type ks_buffer_t
 */
KS_DECLARE(void) ks_buffer_zero(ks_buffer_t *buffer);

/*! \brief Destroy the buffer
 * \param buffer buffer to destroy
 * \note only neccessary on dynamic buffers (noop on pooled ones)
 */
KS_DECLARE(void) ks_buffer_destroy(ks_buffer_t **buffer);

/*! \brief Seek to offset from the beginning of the buffer
 * \param buffer buffer to seek
 * \param datalen offset in bytes
 * \return new position
 */
KS_DECLARE(ks_size_t) ks_buffer_seek(ks_buffer_t *buffer, ks_size_t datalen);

/** @} */

KS_DECLARE(ks_size_t) ks_buffer_zwrite(ks_buffer_t *buffer, const void *data, ks_size_t datalen);

KS_END_EXTERN_C
#endif
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
