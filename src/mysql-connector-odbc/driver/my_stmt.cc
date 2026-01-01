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
  @file  my_stmt.c
  @brief Some "methods" for STMT "class" - functions that dispatch a call to either
         prepared statement version or to regular statement version(i.e. using mysql_stmt_*
         of mysql_* functions of API, depending of that has been used in that STMT.
         also contains "mysql_*" versions of "methods".
*/

#include "driver.h"

BOOL ssps_used(STMT *stmt)
{
  return (stmt->ssps != NULL);
}


/* Errors processing? */
BOOL returned_result(STMT *stmt)
{
  if (ssps_used(stmt))
  {
    /* Basically at this point we are supposed to get result already */
    MYSQL_RES *temp_res= NULL;

    if ((stmt->result != NULL) ||
        (temp_res= mysql_stmt_result_metadata(stmt->ssps)) != NULL)
    {
      /* mysql_free_result checks for NULL, so we can always call it */
      mysql_free_result(temp_res);
      return TRUE;
    }
    return FALSE;
  }
  else
  {
    return mysql_field_count(stmt->dbc->mysql) > 0 ;
  }
}


my_bool free_current_result(STMT *stmt)
{
  my_bool res= 0;
  if (stmt->result)
  {
    if (ssps_used(stmt))
    {
      free_result_bind(stmt);
      res= mysql_stmt_free_result(stmt->ssps);
    }
    /* We need to always free stmt->result because SSPS keep metadata there */
    stmt_result_free(stmt);
    stmt->result= NULL;
  }
  return res;
}


/* Name may be misleading, the idea is stmt - for directly executed statements,
   i.e using mysql_* part of api, ssps - prepared on server, using mysql_stmt
 */
static
MYSQL_RES * stmt_get_result(STMT *stmt, BOOL force_use)
{
  /* We can't use USE_RESULT because SQLRowCount will fail in this case! */
  if (if_forward_cache(stmt) || force_use)
  {
    return mysql_use_result(stmt->dbc->mysql);
  }
  else
  {
    return mysql_store_result(stmt->dbc->mysql);
  }
}


/* For text protocol this get result itself as well. Besides for text protocol
   we need to use/store each resultset of multiple resultsets */
MYSQL_RES * get_result_metadata(STMT *stmt, BOOL force_use)
{
  /* just a precaution, mysql_free_result checks for NULL anywat */
  mysql_free_result(stmt->result);

  if (ssps_used(stmt))
  {
    stmt->result= mysql_stmt_result_metadata(stmt->ssps);
  }
  else
  {
    stmt->result= stmt_get_result(stmt, force_use);
  }

  return stmt->result;
}


int bind_result(STMT *stmt)
{
  if (ssps_used(stmt))
  {
    return stmt->ssps_bind_result();
  }

  return 0;
}

int get_result(STMT *stmt)
{
  if (ssps_used(stmt))
  {
    return ssps_get_result(stmt);
  }
  /* Nothing to do here for text protocol */

  return 0;
}


size_t STMT::field_count()
{
  if (ssps)
  {
    return mysql_stmt_field_count(ssps);
  }
  else
  {
    return result && result->field_count > 0 ?
      result->field_count :
      mysql_field_count(dbc->mysql);
  }
}


my_ulonglong affected_rows(STMT *stmt)
{
  if (ssps_used(stmt))
  {
    return mysql_stmt_affected_rows(stmt->ssps);
  }
  else
  {
    /* In some cases in c/odbc it cannot be used instead of mysql_num_rows */
    return mysql_affected_rows(stmt->dbc->mysql);
  }
}

my_ulonglong update_affected_rows(STMT *stmt)
{
  my_ulonglong last_affected;

  last_affected= affected_rows(stmt);

  stmt->affected_rows+= last_affected;

  return last_affected;
}


my_ulonglong num_rows(STMT *stmt)
{
  my_ulonglong offset= scroller_exists(stmt) && stmt->scroller.next_offset > 0 ?
    stmt->scroller.next_offset - stmt->scroller.row_count : 0;

  if (ssps_used(stmt))
  {
    return  offset + mysql_stmt_num_rows(stmt->ssps);
  }
  else
  {
    return offset + mysql_num_rows(stmt->result);
  }
}


MYSQL_ROW STMT::fetch_row(bool read_unbuffered)
{
  if (ssps)
  {
    if (ssps_bind_result())
    {
      return nullptr;
    }
    int err = 0;

    if (read_unbuffered || m_row_storage.eof())
    {
      /* Reading results from network */
      err = mysql_stmt_fetch(ssps);
    }
    else
    {
      /* Row is already buffered in row storage, use the row storage */
      m_row_storage.fill_data(result_bind);
    }

    switch (err)
    {
      case 1:
        set_error("HY000", mysql_stmt_error(ssps),
          mysql_stmt_errno(ssps));
        throw error;
      case MYSQL_NO_DATA:
        return nullptr;
    }

    if (fix_fields)
      return fix_fields(this, nullptr); // it returns stmt->array

    return array;
  }
  else
  {
    return mysql_fetch_row(result);
  }
}


unsigned long* fetch_lengths(STMT *stmt)
{
  if (ssps_used(stmt))
  {
    return stmt->result_bind[0].length;
  }
  else
  {
    return mysql_fetch_lengths(stmt->result);
  }
}


MYSQL_ROW_OFFSET row_seek(STMT *stmt, MYSQL_ROW_OFFSET offset)
{
  if (ssps_used(stmt))
  {
    return mysql_stmt_row_seek(stmt->ssps, offset);
  }
  else
  {
    return mysql_row_seek(stmt->result, offset);
  }
}


void data_seek(STMT *stmt, my_ulonglong offset)
{
  if (ssps_used(stmt))
  {
    mysql_stmt_data_seek(stmt->ssps, offset);
  }
  else
  {
    mysql_data_seek(stmt->result, offset);
  }
}


MYSQL_ROW_OFFSET row_tell(STMT *stmt)
{
  if (ssps_used(stmt))
  {
    return mysql_stmt_row_tell(stmt->ssps);
  }
  else
  {
    return mysql_row_tell(stmt->result);
  }
}


int next_result(STMT *stmt)
{
  free_current_result(stmt);

  if (ssps_used(stmt))
  {
    return mysql_stmt_next_result(stmt->ssps);
  }
  else
  {
    return mysql_next_result(stmt->dbc->mysql);
  }
}


/* --- Data conversion methods --- */
int get_int(STMT *stmt, ulong column_number, char *value, ulong length)
{
  if (ssps_used(stmt))
  {
     return (int)ssps_get_int64<long long>(stmt, column_number, value, length);
  }
  else
  {
    return (int)strtol(value, NULL, 10);
  }
}


unsigned int get_uint(STMT* stmt, ulong column_number, char* value, ulong length)
{
  if (ssps_used(stmt))
  {
    return (int)ssps_get_int64<unsigned long long>(stmt, column_number, value, length);
  }
  else
  {
    return (unsigned int)strtoul(value, NULL, 10);
  }
}


long long get_int64(STMT *stmt, ulong column_number, char *value, ulong length)
{
  if (ssps_used(stmt))
  {
     return ssps_get_int64<long long>(stmt, column_number, value, length);
  }
  else
  {
    return strtoll(value, NULL, 10);
  }
}


unsigned long long get_uint64(STMT* stmt, ulong column_number, char* value, ulong length)
{
  if (ssps_used(stmt))
  {
    return ssps_get_int64<unsigned long long>(stmt, column_number, value, length);
  }
  else
  {
    return strtoull(value, NULL, 10);
  }
}


char * get_string(STMT *stmt, ulong column_number, char *value, ulong *length,
                  char * buffer)
{
  if (ssps_used(stmt))
  {
     return ssps_get_string(stmt, column_number, value, length, buffer);
  }
  else
  {
    return value;
  }
}


double get_double(STMT *stmt, ulong column_number, char *value,
                  ulong length)
{
  if (ssps_used(stmt))
  {
    return ssps_get_double(stmt, column_number, value, length);
  }
  else
  {
    return myodbc_strtod(value, length);
  }
}


BOOL is_null(STMT *stmt, ulong column_number, char *value)
{
  if (ssps_used(stmt))
  {
    return *stmt->result_bind[column_number].is_null;
  }
  else
  {
    return value == NULL;
  }
}

/* Prepares statement depending on connection option either on a client or
   on a server. Returns SQLRETURN result code since preparing on client or
   server can produce errors, memory allocation to name one.  */
SQLRETURN prepare(STMT *stmt, char * query, SQLINTEGER query_length,
          bool reset_sql_limit, bool force_prepare)
{
  assert(stmt);
  /* TODO: I guess we always have to have query length here */
  if (query_length <= 0)
  {
    query_length = query ? (SQLINTEGER)strlen(query) : 0;
  }

  stmt->query.reset(query, query + query_length,
                    stmt->dbc->cxn_charset_info);
  /* Tokenising string, detecting and storing parameters placeholders, removing {}
     So far the only possible error is memory allocation. Thus setting it here.
     If that changes we will need to make "parse" to set error and return rc */
  if (parse(&stmt->query))
  {
    return stmt->set_error( MYERR_S1001, NULL, 4001);
  }

  ssps_close(stmt);
  stmt->param_count = (uint)PARAM_COUNT(stmt->query);
  /* Trusting our parsing we are not using prepared statments unsless there are
     actually parameter markers in it */
  if (!stmt->dbc->ds.opt_NO_SSPS && (PARAM_COUNT(stmt->query) || force_prepare)
    && !IS_BATCH(&stmt->query) &&
      stmt->query.preparable_on_server(stmt->dbc->mysql->server_version))
  {
    MYLOG_QUERY(stmt, "Using prepared statement");
    ssps_init(stmt);

    /* If the query is in the form of "WHERE CURRENT OF" - we do not need to prepare
       it at the moment */
    if (!stmt->query.get_cursor_name())
    {
     LOCK_DBC(stmt->dbc);

     if (reset_sql_limit)
        set_sql_select_limit(stmt->dbc, 0, false);

     int prep_res = mysql_stmt_prepare(stmt->ssps, query, query_length);

     if (prep_res)
      {
        MYLOG_QUERY(stmt, mysql_error(stmt->dbc->mysql));

        stmt->set_error("HY000");
        translate_error((char*)stmt->error.sqlstate.c_str(), MYERR_S1000,
                        mysql_errno(stmt->dbc->mysql));

        return SQL_ERROR;
      }

      stmt->param_count= mysql_stmt_param_count(stmt->ssps);

      /* make sure we free the result from the previous time */
      if (stmt->result)
      {
        mysql_free_result(stmt->result);
        stmt->result = NULL;
      }

      /* Getting result metadata */
      stmt->fake_result = false;  // reset in case it was set before
      if ((stmt->result= mysql_stmt_result_metadata(stmt->ssps)))
      {
        /*stmt->state= ST_SS_PREPARED;*/
        fix_result_types(stmt);
       /*Should we reset stmt->result?*/
      }
    }
  }

  {
    /* Creating desc records for each parameter */
    uint i;
    for (i= 0; i < stmt->param_count; ++i)
    {
      DESCREC *aprec= desc_get_rec(stmt->apd, i, TRUE);
      DESCREC *iprec= desc_get_rec(stmt->ipd, i, TRUE);
    }
  }

  /* Reset current_param so that SQLParamData starts fresh. */
  stmt->current_param= 0;
  stmt->state= ST_PREPARED;

  return SQL_SUCCESS;
}

SQLRETURN send_long_data (STMT *stmt, unsigned int param_num, DESCREC * aprec, const char *chunk,
                          unsigned long length)
{
#ifdef WE_CAN_SEND_LONG_DATA_PROPERLY
  if (ssps_used(stmt))
  {
    /* If we haven't already started to do that on client and parameter is binary */
    if (aprec->par.value == NULL && aprec->concise_type == SQL_C_BINARY)
    {
      SQLRETURN result= ssps_send_long_data(stmt, param_num, chunk, length);

      /* A bit ugly */
      if (result == SQL_SUCCESS_WITH_INFO)
      {
        return append2param_value(stmt, aprec, chunk, length);
      }

      return result;
    }
    else
    {
      return append2param_value(stmt, aprec, chunk, length);
    }
  }
  else
#endif
  {
    aprec->par.add_param_data(chunk, length);
    return SQL_SUCCESS;
  }
}


/*------------------- Scrolled cursor related stuff -------------------*/

/* @param[in]     selected  - prefetch value in datatsource selected by user
   @param[in]     app_fetchs- how many rows app fetchs at a time,
                              i.e. stmt->ard->array_size
   @param[in]     max_rows  - limit for maximal number of rows to fetch
 */
unsigned int calc_prefetch_number(unsigned int selected, SQLULEN app_fetchs,
                                  SQLULEN max_rows)
{
  unsigned int result= selected;

  if (selected == 0)
  {
    return 0;
  }

  if (app_fetchs > 1)
  {
    if (app_fetchs > selected)
    {
      result = (unsigned int)app_fetchs;
    }

    if (selected % app_fetchs > 0)
    {
      result = (unsigned int)(app_fetchs * (selected/app_fetchs + 1));
    }
  }

  if (max_rows > 0 && max_rows < result)
  {
    return (unsigned int)max_rows;
  }

  return result;
}

BOOL scroller_exists(STMT * stmt)
{
  return stmt->scroller.offset_pos > stmt->scroller.query;
}

/* Initialization of a scroller */
void scroller_create(STMT * stmt, const char *query, SQLULEN query_len)
{
  /* MAX32_BUFF_SIZE includes place for terminating null, which we do not need
     and will use for comma */
  const size_t len2add = 7/*" LIMIT "*/ + MAX64_BUFF_SIZE/*offset*/ /*- 1*/ + MAX32_BUFF_SIZE;
  MY_LIMIT_CLAUSE limit = find_position4limit(stmt->dbc->ansi_charset_info,
                                            query, query + query_len);

  stmt->scroller.start_offset= limit.offset;
  stmt->scroller.total_rows= myodbc_max(stmt->stmt_options.max_rows, 0);

  if (limit.begin != limit.end)
  {
    // This has to be recalculated only if limit is specified
    stmt->scroller.total_rows= stmt->scroller.total_rows > 0 ?
      myodbc_min(limit.row_count, stmt->scroller.total_rows) :
      limit.row_count;
  }

  if ((stmt->scroller.row_count > stmt->scroller.total_rows) &&
      (limit.begin != limit.end))
    // Only set row cound if LIMIT exists in the original query
    stmt->scroller.row_count = (unsigned int)stmt->scroller.total_rows;

  stmt->scroller.next_offset= myodbc_max(limit.offset, 0);

  stmt->scroller.query_len= query_len + len2add;
  stmt->scroller.extend_buf((size_t)stmt->scroller.query_len + 1);
  memset(stmt->scroller.query, ' ', (size_t)stmt->scroller.query_len);
  memcpy(stmt->scroller.query, query, limit.begin - query);

  /* Forgive me - now limit.begin points to beginning of limit in scroller's
     copy of the query */
  char *limptr = stmt->scroller.query + (limit.begin - query);
  limit.begin = limptr;
  strncpy(limptr, " LIMIT ", 7);

  /* That is where we will update offset */
  stmt->scroller.offset_pos = limptr + 7;

  /* putting row count in place. normally should not change or only once */
  myodbc_snprintf(stmt->scroller.offset_pos + MAX64_BUFF_SIZE - 1, MAX32_BUFF_SIZE + 1,
    ",%*u", MAX32_BUFF_SIZE-1, stmt->scroller.row_count);
  /* cpy'ing end of query from original query - not sure if we will allow to
     have one */
  memcpy(stmt->scroller.offset_pos + MAX64_BUFF_SIZE + MAX32_BUFF_SIZE - 1, limit.end,
          query + query_len - limit.end);
  *(stmt->scroller.query + stmt->scroller.query_len)= '\0';
}


/* Returns next offset/maxrow for current fetch*/
unsigned long long scroller_move(STMT * stmt)
{
  myodbc_snprintf(stmt->scroller.offset_pos, MAX64_BUFF_SIZE, "%*llu", MAX64_BUFF_SIZE - 1,
    stmt->scroller.next_offset);
  stmt->scroller.offset_pos[MAX64_BUFF_SIZE - 1]=',';

  stmt->scroller.next_offset+= stmt->scroller.row_count;

  return stmt->scroller.next_offset;
}


SQLRETURN scroller_prefetch(STMT * stmt)
{
  assert(stmt);
  if (stmt->scroller.total_rows > 0
      && stmt->scroller.next_offset >= (stmt->scroller.total_rows + stmt->scroller.start_offset))
  {
    /* (stmt->scroller.next_offset - stmt->scroller.row_count) - current offset,
       0 minimum. scroller initialization makes impossible row_count to be >
       stmt's max_rows */
     long long count= stmt->scroller.total_rows -
      (stmt->scroller.next_offset - stmt->scroller.row_count - stmt->scroller.start_offset);

    if (count > 0)
    {
      myodbc_snprintf(stmt->scroller.offset_pos + MAX64_BUFF_SIZE, MAX32_BUFF_SIZE,
              "%*u", MAX32_BUFF_SIZE - 1, (unsigned long)count);
      stmt->scroller.offset_pos[MAX64_BUFF_SIZE + MAX32_BUFF_SIZE - 1] = ' ';
    }
    else
    {
      return SQL_NO_DATA;
    }
  }

  MYLOG_QUERY(stmt, stmt->scroller.query);

  LOCK_DBC(stmt->dbc);

  if (exec_stmt_query(stmt, stmt->scroller.query,
                        (unsigned long)stmt->scroller.query_len, FALSE))
  {
    return SQL_ERROR;
  }
  get_result_metadata(stmt, FALSE);
  return SQL_SUCCESS;
}



bool scrollable(STMT * stmt, const char * query, const char * query_end)
{
  if (!stmt->query.is_select_statement())
  {
    return FALSE;
  }

  /* FOR UPDATE*/
  {
    const char *before_token= query_end;
    const char *last= mystr_get_prev_token(stmt->dbc->ansi_charset_info,
                                                &before_token,
                                                query);
    const char *prev= mystr_get_prev_token(stmt->dbc->ansi_charset_info,
                                                &before_token,
                                                query);

    /* we have to tokens - nothing to do*/
    if (prev == query)
    {
      return FALSE;
    }

    before_token= prev - 1;
    /* FROM can be only token before a last one at most
       no need to scroll if there is no FROM clause
     */
    if ( myodbc_casecmp(prev,"FROM", 4)
      && !find_token(stmt->dbc->ansi_charset_info, query, before_token, "FROM"))
    {
      return FALSE;
    }
  }

  return TRUE;
}
