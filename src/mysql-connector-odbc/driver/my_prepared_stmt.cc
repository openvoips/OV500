// Copyright (c) 2012, 2024, Oracle and/or its affiliates.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License, version 2.0, as
// published by the Free Software Foundation.
//
// This program is designed to work with certain software (including
// but not limited to OpenSSL) that is licensed under separate terms, as
// designated in a particular file or component or in included license
// documentation. The authors of MySQL hereby grant you an additional
// permission to link the program and your derivative works with the
// separately licensed software that they have either included with
// the program or referenced in the documentation.
//
// Without limiting anything contained in the foregoing, this file,
// which is part of Connector/ODBC, is also subject to the
// Universal FOSS Exception, version 1.0, a copy of which can be found at
// https://oss.oracle.com/licenses/universal-foss-exception.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU General Public License, version 2.0, for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

/**
  @file  ssps.c
  @brief Functions to support use of Server Side Prepared Statements.
*/

#include "driver.h"
#include "errmsg.h"


/* {{{ my_l_to_a() -I- */
static char * my_l_to_a(char * buf, size_t buf_size, long long a)
{
  myodbc_snprintf(buf, buf_size, "%lld", (long long) a);
  return buf;
}
/* }}} */


/* {{{ my_ul_to_a() -I- */
static char * my_ul_to_a(char * buf, size_t buf_size, unsigned long long a)
{
  myodbc_snprintf(buf, buf_size, "%llu", (unsigned long long) a);
  return buf;
}
/* }}} */


/* {{{ ssps_init() -I- */
void ssps_init(STMT *stmt)
{
  stmt->ssps= mysql_stmt_init(stmt->dbc->mysql);

  stmt->result_bind = 0;
}
/* }}} */


char * numeric2binary(char * dst, long long src, unsigned int byte_count)
{
  char byte;

  while (byte_count)
  {
    byte= src & 0xff;
    *(dst+(--byte_count))= byte;
    src= src >> 8;
  }

  return dst;
}


/**
  @returns TRUE if the resultset is SP OUT params
  Basically it makes sense with prepared statements only
  */
BOOL ssps_get_out_params(STMT *stmt)
{
  /* If we use prepared statement, and the query is CALL and we have any
    user's parameter described as INOUT or OUT and that is only result */
  if (is_call_procedure(&stmt->query))
  {
    MYSQL_ROW values= NULL;
    DESCREC   *iprec, *aprec;
    uint      counter= 0;
    int       i, out_params;

    /*Since OUT parameters can be completely different - we have to free current
      bind and bind new */

    free_result_bind(stmt);
    /* Thus function interface has to be changed */
    if (stmt->ssps_bind_result() == 0)
    {
      try
      {
        values = stmt->fetch_row();
      }
      catch(MYERROR&)
      {
        return FALSE;
      }

      /* We need this for fetch_varlength_columns pointed by fix_fields, so it omits
         streamed parameters */
      out_params= got_out_parameters(stmt);

      if (out_params & GOT_OUT_STREAM_PARAMETERS)
      {
        stmt->out_params_state= OPS_STREAMS_PENDING;
        stmt->current_param= ~0L;
        stmt->reset_getdata_position();
      }
      else//(out_params & GOT_OUT_PARAMETERS)
      {
        stmt->out_params_state= OPS_PREFETCHED;
      }

      if (stmt->fix_fields)
      {
        values= (*stmt->fix_fields)(stmt,values);
      }
    }

    assert(values);

    if (values)
    {
      stmt->current_values= values;

      if (out_params)
      {
        for (i= 0;
             i < myodbc_min(stmt->ipd->rcount(), stmt->apd->rcount()) && counter < stmt->field_count();
             ++i)
        {
          /* Making bit field look "normally" */
          if (stmt->result_bind[counter].buffer_type == MYSQL_TYPE_BIT)
          {
            MYSQL_FIELD *field= mysql_fetch_field_direct(stmt->result, counter);
            unsigned long long numeric;

            assert(field->type == MYSQL_TYPE_BIT);
            /* terminating with NULL */
            values[counter][*stmt->result_bind[counter].length]= '\0';
            numeric= strtoull(values[counter], NULL, 10);

            *stmt->result_bind[counter].length= (field->length+7)/8;
            numeric2binary(values[counter], numeric,
                          *stmt->result_bind[counter].length);

          }

          iprec= desc_get_rec(stmt->ipd, i, FALSE);
          aprec= desc_get_rec(stmt->apd, i, FALSE);
          assert(iprec && aprec);

          if (iprec->parameter_type == SQL_PARAM_INPUT_OUTPUT
           || iprec->parameter_type == SQL_PARAM_OUTPUT
  #ifndef USE_IODBC
           || iprec->parameter_type == SQL_PARAM_INPUT_OUTPUT_STREAM
           || iprec->parameter_type == SQL_PARAM_OUTPUT_STREAM
  #endif
          )
          {
            if (aprec->data_ptr)
            {
              unsigned long length= *stmt->result_bind[counter].length;
              char *target= NULL;
              SQLLEN *octet_length_ptr= NULL;
              SQLLEN *indicator_ptr= NULL;
              SQLINTEGER default_size;

              if (aprec->octet_length_ptr)
              {
                octet_length_ptr= (SQLLEN*)ptr_offset_adjust(aprec->octet_length_ptr,
                                              stmt->apd->bind_offset_ptr,
                                              stmt->apd->bind_type,
                                              sizeof(SQLLEN), 0);
              }

              indicator_ptr= (SQLLEN*)ptr_offset_adjust(aprec->indicator_ptr,
                                           stmt->apd->bind_offset_ptr,
                                           stmt->apd->bind_type,
                                           sizeof(SQLLEN), 0);

              default_size = bind_length(aprec->concise_type,
                                        (ulong)aprec->octet_length);
              target= (char*)ptr_offset_adjust(aprec->data_ptr, stmt->apd->bind_offset_ptr,
                                    stmt->apd->bind_type, default_size, 0);

              stmt->reset_getdata_position();

              if (iprec->parameter_type == SQL_PARAM_INPUT_OUTPUT
               || iprec->parameter_type == SQL_PARAM_OUTPUT)
              {
                sql_get_data(stmt, aprec->concise_type, counter,
                             target, aprec->octet_length, indicator_ptr,
                             values[counter], length, aprec);

                /* TODO: solve that globally */
                if (octet_length_ptr != NULL && indicator_ptr != NULL
                  && octet_length_ptr != indicator_ptr
                  && *indicator_ptr != SQL_NULL_DATA)
                {
                  *octet_length_ptr= *indicator_ptr;
                }
              }
              else if (octet_length_ptr != NULL)
              {
                /* Putting full number of bytes in the stream. A bit dirtyhackish.
                   Only good since only binary type is supported... */
                *octet_length_ptr= *stmt->result_bind[counter].length;
              }
            }

            ++counter;
          }
        }
      }
    }
    else /*values != NULL */
    {
      /* Something went wrong */
      stmt->out_params_state= OPS_UNKNOWN;
    }

    if (stmt->out_params_state != OPS_STREAMS_PENDING)
    {
      /* This MAGICAL fetch is required. If there are streams - it has to be after
         streams are all done, perhaps when stmt->out_params_state is changed from
         OPS_STREAMS_PENDING */
      mysql_stmt_fetch(stmt->ssps);
    }

    return TRUE;
  }
  return FALSE;
}


int ssps_get_result(STMT *stmt)
{
  try
  {
    if (stmt->result)
    {
      if (!if_forward_cache(stmt))
      {
        return mysql_stmt_store_result(stmt->ssps);
      }
      else
      {
        /*
          There is no way of telling beforehand if the result set is the
          normal result set or out parameters.

          In order to get the server status GOT_OUT_PARAMETERS we need
          to read at least two rows from the result set.
          1st row is the data for the OUT parameters, 2nd read attempt should
          return no data and pick up the EOF and the server status.
        */

        size_t field_count = stmt->field_count();
        // Try fetching 1st row, return if no data is available.
        if(stmt->fetch_row(true) == nullptr)
          return 0;
        stmt->m_row_storage.set_size(1, field_count);
        stmt->m_row_storage.set_data(stmt->result_bind);
        // Add 2nd row if it is fetched
        if (stmt->fetch_row(true))
        {
          stmt->m_row_storage.next_row();
          stmt->m_row_storage.set_data(stmt->result_bind);
        }
        // Set row counter to start before reading rows
        stmt->m_row_storage.first_row();
      }
    }
  }
  catch(MYERROR &e)
  {
    return e.retcode;
  }
  catch(...)
  {
    return SQL_ERROR;
  }

  return 0;
}


void free_result_bind(STMT *stmt)
{
  if (stmt->result_bind != NULL)
  {
    auto field_cnt = stmt->field_count();

    /* buffer was allocated for each column */
    for (size_t i = 0; i < field_cnt; i++)
    {
      x_free(stmt->result_bind[i].buffer);

      if (stmt->lengths)
      {
        stmt->lengths[i]= 0;
      }
    }

    x_free(stmt->result_bind);
    stmt->result_bind= 0;
    stmt->array.reset();
  }
}


void ssps_close(STMT *stmt)
{
  if (stmt->ssps != NULL)
  {
    free_result_bind(stmt);

    /*
      No need to check the result of this operation.
      It can fail because the connection to the server is lost, which
      is still ok because the memory is freed anyway.
    */
    mysql_stmt_close(stmt->ssps);
    stmt->ssps= NULL;
  }
  stmt->buf_set_pos(0);
}


SQLRETURN ssps_fetch_chunk(STMT *stmt, char *dest, unsigned long dest_bytes, unsigned long *avail_bytes)
{
  MYSQL_BIND bind;
  my_bool is_null, error= 0;

  bind.buffer= dest;
  bind.buffer_length= dest_bytes;
  bind.length= &bind.length_value;
  bind.is_null= &is_null;
  bind.error= &error;

  if (mysql_stmt_fetch_column(stmt->ssps, &bind, stmt->getdata.column, stmt->getdata.src_offset))
  {
    switch (mysql_stmt_errno(stmt->ssps))
    {
      case  CR_INVALID_PARAMETER_NO:
        /* Shouldn't really happen here*/
        return stmt->set_error("07009", "Invalid descriptor index", 0);

      case CR_NO_DATA: return SQL_NO_DATA;

      default: stmt->set_error("HY000", "Internal error", 0);
    }
  }
  else
  {
    *avail_bytes= (SQLULEN)bind.length_value - stmt->getdata.src_offset;
    stmt->getdata.src_offset+= myodbc_min((SQLULEN)dest_bytes, *avail_bytes);

    if (*bind.error)
    {
      stmt->set_error("01004", NULL, 0);
      return SQL_SUCCESS_WITH_INFO;
    }

    if (*avail_bytes == 0)
    {
      /* http://msdn.microsoft.com/en-us/library/ms715441%28v=vs.85%29.aspx
         "The last call to SQLGetData must always return the length of the data, not zero or SQL_NO_TOTAL." */
      *avail_bytes= (SQLULEN)bind.length_value;
      /* "Returns SQL_NO_DATA if it has already returned all of the data for the column." */
      return SQL_NO_DATA;
    }
  }

  return SQL_SUCCESS;
}


bool is_varlen_type(enum enum_field_types type)
{
  return (type == MYSQL_TYPE_BLOB ||
          type == MYSQL_TYPE_TINY_BLOB ||
          type == MYSQL_TYPE_MEDIUM_BLOB ||
          type == MYSQL_TYPE_LONG_BLOB ||
          type == MYSQL_TYPE_VAR_STRING ||
          type == MYSQL_TYPE_JSON);
}

/* The structure and following allocation function are borrowed from c/c++ and adopted */
typedef struct tagBST
{
  char * buffer = NULL;
  size_t size = 0;
  enum enum_field_types type;

  tagBST(char *b, size_t s, enum enum_field_types t) :
    buffer(b), size(s), type(t)
  {}

  /*
    To bind blob parameters the fake 1 byte allocation has to be made
    otherwise libmysqlclient throws the assertion.
    This will be reallocated later.
  */
  bool is_varlen_alloc()
  {
    return is_varlen_type(type);
  }
} st_buffer_size_type;


/* {{{ allocate_buffer_for_field() -I- */
static st_buffer_size_type
allocate_buffer_for_field(const MYSQL_FIELD * const field, BOOL outparams)
{
  st_buffer_size_type result(NULL, 0, field->type);

  switch (field->type)
  {
    case MYSQL_TYPE_NULL:
      break;

    case MYSQL_TYPE_TINY:
      result.size= 1;
      break;

    case MYSQL_TYPE_SHORT:
      result.size=2;
      break;

    case MYSQL_TYPE_INT24:
    case MYSQL_TYPE_LONG:
      result.size=4;
      break;

    /*
      For consistency with results obtained through MYSQL_ROW
      we must fetch FLOAT and DOUBLE as character data
    */
    case MYSQL_TYPE_DOUBLE:
    case MYSQL_TYPE_FLOAT:
      result.size=24;
      result.type = MYSQL_TYPE_STRING;
      break;

    case MYSQL_TYPE_LONGLONG:
      result.size=8;
      break;

    case MYSQL_TYPE_YEAR:
      result.size=2;
      break;

    case MYSQL_TYPE_TIMESTAMP:
    case MYSQL_TYPE_DATE:
    case MYSQL_TYPE_TIME:
    case MYSQL_TYPE_DATETIME:
      result.size=sizeof(MYSQL_TIME);
      break;

    case MYSQL_TYPE_TINY_BLOB:
    case MYSQL_TYPE_MEDIUM_BLOB:
    case MYSQL_TYPE_LONG_BLOB:
    case MYSQL_TYPE_BLOB:
    case MYSQL_TYPE_STRING:
    case MYSQL_TYPE_VAR_STRING:
    case MYSQL_TYPE_JSON:
      /* We will get length with fetch and then fetch column */
      if (field->length > 0 && field->length < 1025)
        result.size= field->length + 1;
      else
      {
        // This is to keep mysqlclient library happy.
        // The buffer will be reallocated later.
        result.size= 1024;
      }
      break;

    case MYSQL_TYPE_DECIMAL:
    case MYSQL_TYPE_NEWDECIMAL:
      result.size=64;
      break;

    #if A1
    case MYSQL_TYPE_TIMESTAMP:
    case MYSQL_TYPE_YEAR:
      result.size= 10;
      break;
    #endif
    #if A0
    // There two are not sent over the wire
    case MYSQL_TYPE_ENUM:
    case MYSQL_TYPE_SET:
    #endif
    case MYSQL_TYPE_BIT:
      result.type= (enum_field_types)MYSQL_TYPE_BIT;
      if (outparams)
      {
        /* For out params we surprisingly get it as string representation of a
           number representing those bits. Allocating buffer to accommodate
           largest string possible - 8 byte number + NULL terminator.
           We will need to terminate the string to convert it to a number */
        result.size= 30;
      }
      else
      {
        result.size= (field->length + 7)/8;
      }

      break;
    case MYSQL_TYPE_GEOMETRY:
    default:
      /* Error? */
      1;
  }

  if (result.size > 0)
  {
    result.buffer= (char*)myodbc_malloc(result.size, MYF(0));
  }

  return result;
}
/* }}} */


static MYSQL_ROW fetch_varlength_columns(STMT *stmt, MYSQL_ROW values)
{
  const size_t num_fields = stmt->field_count();
  unsigned int i;
  uint desc_index= ~0L, stream_column= ~0L;

  // Only do something if values have not been fetched yet
  if (values)
    return values;

  if (stmt->out_params_state == OPS_STREAMS_PENDING)
  {
    desc_find_outstream_rec(stmt, &desc_index, &stream_column);
  }

  bool reallocated_buffers = false;
  for (i= 0; i < num_fields; ++i)
  {

    if (i == stream_column)
    {
      /* Skipping this column */
      desc_find_outstream_rec(stmt, &desc_index, &stream_column);
    }
    else
    {
      if (!(*stmt->result_bind[i].is_null) &&
          is_varlen_type(stmt->result_bind[i].buffer_type) &&
          stmt->result_bind[i].buffer_length < *stmt->result_bind[i].length)
      {
        /* TODO Realloc error proc */
        stmt->array[i]= (char*)myodbc_realloc(stmt->array[i],
          *stmt->result_bind[i].length);

        stmt->lengths[i]= *stmt->result_bind[i].length;
        stmt->result_bind[i].buffer_length = *stmt->result_bind[i].length;
        reallocated_buffers = true;
      }

      // Result bind buffer should already be freed by now.
      stmt->result_bind[i].buffer = stmt->array[i];

      if (stmt->lengths[i] > 0)
      {
       // For fixed-length types we should not set zero length
        stmt->result_bind[i].buffer_length = stmt->lengths[i];
      }

      if (reallocated_buffers)
        mysql_stmt_fetch_column(stmt->ssps, &stmt->result_bind[i], i, 0);

    }
  }

  // Result buffers must be set again after reallocating
  if (reallocated_buffers)
    mysql_stmt_bind_result(stmt->ssps, stmt->result_bind);

  fill_ird_data_lengths(stmt->ird, stmt->result_bind[0].length,
                                  stmt->result->field_count);

  return stmt->array;
}


char *STMT::extend_buffer(char *to, size_t len)
{
  return tempbuf.extend_buffer(to, len);
}

char *STMT::extend_buffer(size_t len)
{
  return tempbuf.extend_buffer(len);
}

char *STMT::add_to_buffer(const char *from, size_t len)
{
  return tempbuf.add_to_buffer(from, len);
}

void STMT::free_lengths()
{
  lengths.reset();
}

void STMT::alloc_lengths(size_t num)
{
  lengths.reset(new unsigned long[num]());
}

void STMT::reset_setpos_apd()
{
  setpos_apd.reset();
}

bool STMT::is_dynamic_cursor()
{
  return stmt_options.cursor_type == SQL_CURSOR_DYNAMIC;
}


void STMT::free_unbind()
{
  ard->reset();
}


/*
  Do only a "light" reset
*/
void STMT::reset()
{
  buf_set_pos(0);

  // If data existed before invalidating the result array does not need freeing
  if (m_row_storage.invalidate())
    result_array.reset();
}

void STMT::free_reset_out_params()
{
  if (out_params_state == OPS_STREAMS_PENDING)
  {
    /* Magical out params fetch */
    mysql_stmt_fetch(ssps);
  }
  out_params_state = OPS_UNKNOWN;
  apd->free_paramdata();
  /* reset data-at-exec state */
  dae_type = 0;
  scroller.reset();
}

void STMT::free_reset_params()
{
  if (ssps)
  {
    // mysql_stmt_reset(ssps);
  }
  /* remove all params and reset count to 0 (per spec) */
  /* http://msdn2.microsoft.com/en-us/library/ms709284.aspx */
  // NOTE: check if this breaks anything
  apd->records2.clear();
}

void STMT::free_fake_result(bool clear_all_results)
{
  if (!fake_result)
  {
    if (clear_all_results)
    {
      /* We seiously CLOSEing statement for preparing handle object for
         new query */
      while (!next_result(this))
      {
        get_result_metadata(this, TRUE);
      }
    }
  }
  else
  {
    if (result && result->field_alloc
#if (!MYSQL8)
      && result->field_alloc->pre_alloc
#endif
      )
    {
      result->field_alloc->Clear();
    }

    stmt_result_free(this);
  }

}


// Clear and free buffers bound in query_attr_bind
void STMT::clear_query_attr_bind()
{
  for (auto bind : query_attr_bind) {
    x_free(bind.buffer);
  }
  query_attr_bind.clear();
}

// Reset result array in case when the row storage is not valid.
// The result data, which was not in the row storage must be cleared
// before filling it with the row storage data.
void STMT::reset_result_array()
{
  if (!m_row_storage.is_valid())
    result_array.reset();
}

STMT::~STMT()
{
  // Create a local mutex in the destructor.
  std::unique_lock<std::recursive_mutex> slock(lock);

  free_lengths();

  if (ssps != NULL)
  {
    mysql_stmt_close(ssps);
    ssps = NULL;
  }

  reset_setpos_apd();

  LOCK_DBC(dbc);
  dbc->stmt_list.remove(this);
  clear_query_attr_bind();
  for (auto bind : param_bind) {
    if (bind.buffer != nullptr)
      x_free(bind.buffer);
  }

}

void STMT::reset_getdata_position()
{
  getdata.column = (uint)~0L;
  getdata.source = NULL;
  getdata.dst_bytes = (ulong)~0L;
  getdata.dst_offset = (ulong)~0L;
  getdata.src_offset = (ulong)~0L;
  getdata.latest_bytes = getdata.latest_used = 0;
}

SQLRETURN STMT::set_error(myodbc_errid errid, const char *errtext,
                         SQLINTEGER errcode)
{
  error = MYERROR(errid, errtext, errcode, dbc->st_error_prefix);
  return error.retcode;
}

SQLRETURN STMT::set_error(myodbc_errid errid)
{
  return set_error(errid, mysql_error(dbc->mysql), mysql_errno(dbc->mysql));
}

SQLRETURN STMT::set_error(const char *state, const char *msg,
                          SQLINTEGER errcode)
{
    error = MYERROR(state, msg, errcode, dbc->st_error_prefix);
    return error.retcode;
}

SQLRETURN STMT::set_error(const char *state)
{
  return set_error(state, mysql_error(dbc->mysql), mysql_errno(dbc->mysql));
}


long STMT::compute_cur_row(unsigned fFetchType, SQLLEN irow)
{
  long cur_row = 0;
  long max_row = (long)num_rows(this);

  switch (fFetchType)
  {
  case SQL_FETCH_NEXT:
    cur_row = (current_row < 0 ? 0 : current_row + rows_found_in_set);
    break;
  case SQL_FETCH_PRIOR:
    cur_row = (current_row <= 0 ? -1 :
      (long)(current_row - ard->array_size));
    break;
  case SQL_FETCH_FIRST:
    cur_row = 0L;
    break;
  case SQL_FETCH_LAST:
    cur_row = max_row - (long)ard->array_size;
    break;
  case SQL_FETCH_ABSOLUTE:
    if (irow < 0)
    {
      /* Fetch from end of result set */
      if (max_row + irow < 0 && -irow <= (long)ard->array_size)
      {
        /*
          | FetchOffset | > LastResultRow AND
          | FetchOffset | <= RowsetSize
        */
        cur_row = 0;     /* Return from beginning */
      }
      else
        cur_row = max_row + (long)irow;     /* Ok if max_row <= -irow */
    }
    else
      cur_row = (long)irow - 1;
    break;

  case SQL_FETCH_RELATIVE:
    cur_row = current_row + (long)irow;
    if (current_row > 0 && cur_row < 0 &&
      (long)-irow <= (long)ard->array_size)
    {
      cur_row = 0;
    }
    break;

  case SQL_FETCH_BOOKMARK:
  {
    cur_row = (long)irow;
    if (cur_row < 0 && (-(long)irow) <= (long)ard->array_size)
    {
      cur_row = 0;
    }
  }
  break;

  default:
    set_error(MYERR_S1106, "Fetch type out of range", 0);
    throw error;
  }

  if (cur_row < 0)
  {
    current_row = -1;  /* Before first row */
    rows_found_in_set = 0;
    data_seek(this, 0L);
    throw MYERROR(SQL_NO_DATA_FOUND);
  }
  if (cur_row > max_row)
  {
    if (scroller_exists(this))
    {
      while (cur_row > (max_row = (long)scroller_move(this)));

      switch (scroller_prefetch(this))
      {
      case SQL_NO_DATA:
        throw MYERROR(SQL_NO_DATA_FOUND);
      case SQL_ERROR:
        set_error(MYERR_S1000, mysql_error(dbc->mysql), 0);
        throw error;
      }
    }
    else
    {
      cur_row = max_row;
    }
  }

  if (!result_array && !if_forward_cache(this))
  {
    /*
      If Dynamic, it loses the stmt->end_of_set, so
      seek to desired row, might have new data or
      might be deleted
    */
    if (stmt_options.cursor_type != SQL_CURSOR_DYNAMIC &&
      cur_row && cur_row == (long)(current_row + rows_found_in_set))
      row_seek(this, this->end_of_set);
    else
      data_seek(this, cur_row);
  }
  current_row = (long)cur_row;
  return current_row;

}

int STMT::ssps_bind_result()
{
  const size_t num_fields = field_count();
  unsigned int        i;

  if (num_fields == 0)
  {
    return 0;
  }

  if (!result_bind)
  {
    rb_is_null.reset(new my_bool[num_fields]());
    rb_err.reset(new my_bool[num_fields]());
    rb_len.reset(new unsigned long[num_fields]());
    my_bool *is_null = rb_is_null.get();
    my_bool *err = rb_err.get();
    unsigned long *len = rb_len.get();

    /*TODO care about memory allocation errors */
    result_bind=  (MYSQL_BIND*)myodbc_malloc(sizeof(MYSQL_BIND)*num_fields,
                                             MYF(MY_ZEROFILL));
    array.set_size(sizeof(char*)*num_fields);

    for (i= 0; i < num_fields; ++i)
    {
      MYSQL_FIELD    *field= mysql_fetch_field_direct(result, i);
      st_buffer_size_type p= allocate_buffer_for_field(field,
                                                      IS_PS_OUT_PARAMS(this));

      result_bind[i].buffer_type  = p.type;
      result_bind[i].buffer       = p.buffer;
      result_bind[i].buffer_length= (unsigned long)p.size;
      result_bind[i].length       = &len[i];
      result_bind[i].is_null      = &is_null[i];
      result_bind[i].error        = &err[i];
      result_bind[i].is_unsigned  = (field->flags & UNSIGNED_FLAG)? 1: 0;

      array[i]= p.buffer;

      /*
        Marking that there are columns that will require buffer (re) allocating
       */
      if ( p.is_varlen_alloc())
      {
        fix_fields= fetch_varlength_columns;

        /* Need to alloc it only once*/
        if (lengths == NULL)
          alloc_lengths(num_fields);
      }
    }

    int rc = mysql_stmt_bind_result(ssps, result_bind);
    if (rc)
    {
      const char *err = mysql_stmt_error(ssps);
      set_error("HY000", err, 0);
    }
    return rc;
  }

  return 0;
}

SQLRETURN STMT::bind_query_attrs(bool use_ssps)
{
  if (use_ssps)
  {
    set_error(MYERR_01000,
              "Query attributes for prepared statements are not supported",
              0);
    return SQL_SUCCESS_WITH_INFO;
  }

  uint rcount = (uint)apd->rcount();
  if (rcount == param_count)
  {
    // Nothing to do
    return SQL_SUCCESS;
  }
  else if (rcount < param_count)
  {
    set_error( MYERR_07001,
              "The number of parameter markers is larger "
              "than he number of parameters provided",0);
    return SQL_ERROR;
  }
  else if (!dbc->has_query_attrs)
  {
    set_error(MYERR_01000,
              "The server does not support query attributes",
              0);
    return SQL_SUCCESS_WITH_INFO;
  }

  uint num = param_count;
  clear_query_attr_bind();
  query_attr_bind.reserve(rcount - param_count);
  query_attr_names.clear();
  query_attr_names.reserve(rcount - param_count);

  while(num < rcount)
  {
    DESCREC *aprec = desc_get_rec(apd, num, false);
    DESCREC *iprec = desc_get_rec(ipd, num, false);

    /*
      IPREC and APREC should both be not NULL if query attributes
      or parameters are set.
    */
    if (!aprec || !iprec)
      return SQL_SUCCESS; // Nothing to do

    query_attr_bind.emplace_back(MYSQL_BIND{});
    MYSQL_BIND *bind = &query_attr_bind.back();

    query_attr_names.emplace_back(iprec->par.val());

    // This will just fill the bind structure and do the param data conversion
    if(insert_param(this, bind, apd, aprec, iprec, 0) == SQL_ERROR)
    {
      set_error(MYERR_01000,
                "The number of attributes is larger than the "
                "number of attribute values provided",
                0);
      return SQL_ERROR;
    }
    ++num;
  }

  MYSQL_BIND *bind = query_attr_bind.data();
  const char** names = (const char**)query_attr_names.data();

  if (mysql_bind_param(dbc->mysql, rcount - param_count,
                        query_attr_bind.data(),
                        (const char**)query_attr_names.data()))
  {
    set_error("HY000");
    return SQL_SUCCESS_WITH_INFO;
  }

  return SQL_SUCCESS;
}

/*
  Function determines if the buffers need to be extended
*/
BOOL ssps_buffers_need_extending(STMT *stmt)
{
  const size_t num_fields = stmt->field_count();
  unsigned int i;

  for (i= 0; i < num_fields; ++i)
  {
    if (*stmt->result_bind[i].error != 0
      && stmt->result_bind[i].buffer_length < (*stmt->result_bind[i].length))
    {
      return TRUE;
    }
  }

  return FALSE;
}


/* --------------- Type conversion functions -------------- */

#define ALLOC_IFNULL(buff,size) ((buff)==NULL?(char*)myodbc_malloc(size,MYF(0)):buff)

/* {{{ ssps_get_string() -I- */
/* caller should care to make buffer long enough,
   if buffer is not null function allocates memory and that is caller's duty to clean it
 */
char * ssps_get_string(STMT *stmt, ulong column_number, char *value, ulong *length,
                       char * buffer)
{
  MYSQL_BIND *col_rbind= &stmt->result_bind[column_number];

  if (*col_rbind->is_null)
  {
    return NULL;
  }

  switch (col_rbind->buffer_type)
  {
    case MYSQL_TYPE_TIMESTAMP:
    case MYSQL_TYPE_DATETIME:
    {
      MYSQL_TIME * t = (MYSQL_TIME *)(col_rbind->buffer);

      buffer= ALLOC_IFNULL(buffer, 30);
      myodbc_snprintf(buffer, 20, "%04u-%02u-%02u %02u:%02u:%02u",
                      t->year, t->month, t->day, t->hour, t->minute, t->second);

      *length= 19;

      if (t->second_part > 0)
      {
        myodbc_snprintf(buffer+*length, 8, ".%06lu", t->second_part);
        *length= 26;
      }

      return buffer;
    }
    case MYSQL_TYPE_DATE:
    {
      MYSQL_TIME * t = (MYSQL_TIME *)(col_rbind->buffer);

      buffer= ALLOC_IFNULL(buffer, 12);
      myodbc_snprintf(buffer, 11, "%04u-%02u-%02u", t->year, t->month, t->day);
      *length= 10;

      return buffer;
    }
    case MYSQL_TYPE_TIME:
    {
      MYSQL_TIME * t = (MYSQL_TIME *)(col_rbind->buffer);

      buffer= ALLOC_IFNULL(buffer, 20);
      myodbc_snprintf(buffer, 10, "%s%02u:%02u:%02u", t->neg? "-":"", t->hour,
                                              t->minute, t->second);
      *length= t->neg ? 9 : 8;

      if (t->second_part > 0)
      {
        myodbc_snprintf(buffer+*length, 8, ".%06lu", t->second_part);
        *length+= 7;
      }
      return buffer;
    }
    case MYSQL_TYPE_BIT:
    case MYSQL_TYPE_YEAR:  // fetched as a SMALLINT
    case MYSQL_TYPE_TINY:
    case MYSQL_TYPE_SHORT:
    case MYSQL_TYPE_INT24:
    case MYSQL_TYPE_LONG:
    case MYSQL_TYPE_LONGLONG:
    {
      buffer= ALLOC_IFNULL(buffer, 30);

      if (col_rbind->is_unsigned)
      {
        my_ul_to_a(buffer, 29,
          ssps_get_int64<unsigned long long>(stmt, column_number, value, *length));
      }
      else
      {
        my_l_to_a(buffer, 29,
          ssps_get_int64<long long>(stmt, column_number, value, *length));
      }

      *length = (ulong)strlen(buffer);
      return buffer;
    }
    case MYSQL_TYPE_FLOAT:
    case MYSQL_TYPE_DOUBLE:
    {
      buffer= ALLOC_IFNULL(buffer, 50);
      myodbc_d2str(ssps_get_double(stmt, column_number, value, *length),
        buffer, 49);

      *length = (ulong)strlen(buffer);
      return buffer;
    }

    case MYSQL_TYPE_DECIMAL:
    case MYSQL_TYPE_NEWDECIMAL:
    case MYSQL_TYPE_STRING:
    case MYSQL_TYPE_LONG_BLOB:
    case MYSQL_TYPE_BLOB:
    case MYSQL_TYPE_VARCHAR:
    case MYSQL_TYPE_VAR_STRING:
    case MYSQL_TYPE_JSON:
      *length= *col_rbind->length;
      return (char *)(col_rbind->buffer);
    default:
      break;
    // TODO : Geometry? default ?
  }

  /* Basically should be prevented by earlied tests of
    conversion possibility */
  return (char*)col_rbind->buffer;
}
/* }}} */


double ssps_get_double(STMT *stmt, ulong column_number, char *value, ulong length)
{
  MYSQL_BIND *col_rbind= &stmt->result_bind[column_number];
  double ret = 0;

  if (*col_rbind->is_null)
  {
    return 0.0;
  }

  switch (col_rbind->buffer_type) {
    case MYSQL_TYPE_BIT:
    case MYSQL_TYPE_YEAR:  // fetched as a SMALLINT
    case MYSQL_TYPE_TINY:
    case MYSQL_TYPE_SHORT:
    case MYSQL_TYPE_INT24:
    case MYSQL_TYPE_LONG:
    case MYSQL_TYPE_LONGLONG:
    {
      BOOL is_it_unsigned = col_rbind->is_unsigned != 0;

      if (is_it_unsigned)
      {
        unsigned long long ival = ssps_get_int64<unsigned long long>(stmt, column_number, value, length);
        ret = (double)(ival);
      }
      else
      {
        long long ival = ssps_get_int64<long long>(stmt, column_number, value, length);
        ret = (double)(ival);
      }

      return ret;
    }
    case MYSQL_TYPE_DECIMAL:
    case MYSQL_TYPE_NEWDECIMAL:
    case MYSQL_TYPE_TIMESTAMP:
    case MYSQL_TYPE_DATETIME:
    case MYSQL_TYPE_DATE:
    case MYSQL_TYPE_TIME:
    case MYSQL_TYPE_STRING:
    case MYSQL_TYPE_BLOB:
    case MYSQL_TYPE_VARCHAR:
    case MYSQL_TYPE_VAR_STRING:
    {
      char buf[50];
      ret = myodbc_strtod(ssps_get_string(stmt, column_number, value,
                          &length, buf), length);
      return ret;
    }

    case MYSQL_TYPE_FLOAT:
    {
      ret = !*col_rbind->is_null ? *(float *)(col_rbind->buffer) : 0.;
      return ret;
    }

    case MYSQL_TYPE_DOUBLE:
    {
      ret = !*col_rbind->is_null ? *(double *)(col_rbind->buffer) : 0.;
      return ret;
    }

    /* TODO : Geometry? default ? */
  }

  /* Basically should be prevented by earlied tests of
     conversion possibility */
  return .0;
}


template <typename T>
T binary2numeric(char* src, uint64 srcLen)
{
  T dst = 0;

  while (srcLen)
  {
    /* if source binary data is longer than 8 bytes(size of long long)
       we consider only minor 8 bytes */
    if (srcLen > sizeof(T))
      continue;
    dst += ((T)(0xff & *src)) << (--srcLen) * 8;
    ++src;
  }

  return dst;
}

long long binary2ll(char* src, uint64 srcLen)
{
  return binary2numeric<long long>(src, srcLen);
}

unsigned long long binary2ull(char* src, uint64 srcLen)
{
  return binary2numeric<unsigned long long>(src, srcLen);
}

template <typename T>
T ssps_get_int64(STMT *stmt, ulong column_number, char *value, ulong length)
{
  MYSQL_BIND *col_rbind= &stmt->result_bind[column_number];

  switch (col_rbind->buffer_type)
  {
    case MYSQL_TYPE_FLOAT:
    case MYSQL_TYPE_DOUBLE:

      return (T)ssps_get_double(stmt, column_number, value, length);

    case MYSQL_TYPE_DECIMAL:
    case MYSQL_TYPE_NEWDECIMAL:
    case MYSQL_TYPE_TIMESTAMP:
    case MYSQL_TYPE_DATETIME:
    case MYSQL_TYPE_DATE:
    case MYSQL_TYPE_TIME:
    case MYSQL_TYPE_STRING:
    case MYSQL_TYPE_BLOB:
    case MYSQL_TYPE_VARCHAR:
    case MYSQL_TYPE_VAR_STRING:
    {
      char buf[30];
      return strtoll(ssps_get_string(stmt, column_number, value, &length, buf),
                      NULL, 10);
    }
    case MYSQL_TYPE_BIT:
    {
      /* This length is in bytes, on the contrary to what can be seen in mysql_resultset.cpp where the Meta is used */
      return binary2numeric<T>((char*)col_rbind->buffer, (uint64)(*col_rbind->length));
    }

    case MYSQL_TYPE_YEAR:  // fetched as a SMALLINT
    case MYSQL_TYPE_TINY:
    case MYSQL_TYPE_SHORT:
    case MYSQL_TYPE_INT24:
    case MYSQL_TYPE_LONG:
    case MYSQL_TYPE_LONGLONG:
    {
      // MYSQL_TYPE_YEAR is fetched as a SMALLINT, thus should not be in the switch
      T ret;
      BOOL is_it_null = *col_rbind->is_null != 0;
      BOOL is_it_unsigned = col_rbind->is_unsigned != 0;

      switch (col_rbind->buffer_length)
      {
        case 1:
          if (is_it_unsigned)
          {
            ret = !is_it_null? ((char *)col_rbind->buffer)[0]:0;
          }
          else
          {
            ret = !is_it_null? *(char *)(col_rbind->buffer):0;
          }
          break;

        case 2:

          if (is_it_unsigned)
          {
            ret = !is_it_null? *(unsigned short *)(col_rbind->buffer):0;
          }
          else
          {
            ret = !is_it_null? *(short *)(col_rbind->buffer):0;
          }
          break;

        case 4:

          if (is_it_unsigned)
          {
            ret =  !is_it_null? *(unsigned int *)(col_rbind->buffer):0;
          }
          else
          {
            ret =  !is_it_null? *(int *)(col_rbind->buffer):0;
          }
          break;

        case 8:

          if (is_it_unsigned)
          {
            ret =  !is_it_null? *(unsigned long long *)col_rbind->buffer:0;

#if WE_WANT_TO_SEE_MORE_FAILURES_IN_UNIT_RESULTSET
            if (cutTooBig &&
              ret &&
              *(unsigned long long *)(col_rbind->buffer) > UL64(9223372036854775807))
            {
              ret = UL64(9223372036854775807);
            }
#endif
          }
          else
          {
            ret =  !is_it_null? *(long long *)(col_rbind->buffer):0;
          }
          break;
        default:
          return 0;
      }
      return ret;
    }
    default:
      break;/* Basically should be prevented by earlier tests of
                       conversion possibility */
    /* TODO : Geometry? default ? */
  }

  return 0; // fool compilers
}


/* {{{ ssps_send_long_data () -I- */
SQLRETURN ssps_send_long_data(STMT *stmt, unsigned int param_number, const char *chunk,
                            unsigned long length)
{
  if ( mysql_stmt_send_long_data(stmt->ssps, param_number, chunk, length))
  {
    uint err= mysql_stmt_errno(stmt->ssps);
    switch (err)
    {
      case CR_INVALID_BUFFER_USE:
      /* We can fall back to assembling parameter's value on client */
        return SQL_SUCCESS_WITH_INFO;
      case CR_SERVER_GONE_ERROR:
        return stmt->set_error("08S01", mysql_stmt_error(stmt->ssps), err);
      case CR_COMMANDS_OUT_OF_SYNC:
      case CR_UNKNOWN_ERROR:
        return stmt->set_error("HY000", mysql_stmt_error( stmt->ssps), err);
      case CR_OUT_OF_MEMORY:
        return stmt->set_error("HY001", mysql_stmt_error(stmt->ssps), err);
      default:
        return stmt->set_error("HY000", "unhandled error from mysql_stmt_send_long_data", 0 );
    }
  }

  return SQL_SUCCESS;
}
/* }}} */


MYSQL_BIND * get_param_bind(STMT *stmt, unsigned int param_number, int reset)
{
  MYSQL_BIND *bind = &stmt->param_bind[param_number];

  if (reset)
  {
    bind->is_null_value= 0;
    bind->is_unsigned=   0;

    /* as far as looked - this trick is safe */
    bind->is_null= &bind->is_null_value;
    bind->length=  &bind->length_value;
  }

  return bind;
}
