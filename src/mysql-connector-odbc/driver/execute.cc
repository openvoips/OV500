// Copyright (c) 2000, 2024, Oracle and/or its affiliates.
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
  @file  execute.c
  @brief Statement execution functions.
*/

#include "driver.h"
#include <locale.h>


/*
  @type    : myodbc3 internal
  @purpose : internal function to execute query and return result
  frees query if query != stmt->query
*/
SQLRETURN do_query(STMT *stmt, std::string query)
{
    int error= SQL_ERROR, native_error= 0;
    SQLULEN query_length = query.length();
    assert(stmt);
    LOCK_STMT_DEFER(stmt);

    if (query.empty())
    {
      /* Probably error from insert_param */
      goto exit;
    }

    if(!SQL_SUCCEEDED(set_sql_select_limit(stmt->dbc,
                      stmt->stmt_options.max_rows, TRUE)))
    {
      /* The error is set for DBC, copy it into STMT */
      stmt->set_error(stmt->dbc->error.sqlstate.c_str(),
                     stmt->dbc->error.message.c_str(),
                     stmt->dbc->error.native_error);

      /* if setting sql_select_limit fails, the query will probably fail anyway too */
      goto exit;
    }

    MYLOG_QUERY(stmt, query.c_str());
    DO_LOCK_STMT();
    if ( check_if_server_is_alive( stmt->dbc ) )
    {
      stmt->set_error("08S01" /* "HYT00" */,
                      mysql_error(stmt->dbc->mysql),
                      mysql_errno(stmt->dbc->mysql));

      translate_error((char*)stmt->error.sqlstate.c_str(), MYERR_08S01 /* S1000 */,
                      mysql_errno(stmt->dbc->mysql));
      goto exit;
    }

    /* Simplifying task so far - we will do "LIMIT" scrolling forward only
     * and when no musltiple statements is allowed - we can't now parse query
     * that well to detect multiple queries.
     */
    if (stmt->dbc->ds.opt_PREFETCH > 0
        && !stmt->dbc->ds.opt_MULTI_STATEMENTS
        && stmt->stmt_options.cursor_type == SQL_CURSOR_FORWARD_ONLY
        && scrollable(stmt, query.c_str(), query.c_str() + query_length)
        && !ssps_used(stmt))
    {
      /* we might want to read primary key info at this point, but then we have to
         know if we have a select from a single table...
       */
      ssps_close(stmt);
      stmt->scroller.reset();

      stmt->scroller.row_count= calc_prefetch_number(
                                      stmt->dbc->ds.opt_PREFETCH,
                                      stmt->ard->array_size,
                                      stmt->stmt_options.max_rows);

      scroller_create(stmt, query.c_str(), query_length);
      scroller_move(stmt);
      MYLOG_QUERY(stmt, stmt->scroller.query);

      native_error = mysql_real_query(stmt->dbc->mysql, stmt->scroller.query,
                                  (unsigned long)stmt->scroller.query_len);
    }
      /* Not using ssps for scroller so far. Relaxing a bit condition
       if MULTI_STATEMENTS option selected by primitive check if
       this is a batch of queries */
    else if (ssps_used(stmt))
    {
      native_error = 0;
      if (stmt->param_bind.size() && stmt->param_count)
        native_error = mysql_stmt_bind_param(stmt->ssps, &stmt->param_bind[0]);

      if (native_error == 0)
      {
        native_error= mysql_stmt_execute(stmt->ssps);
      }
      else
      {
        stmt->set_error("HY000",
                       mysql_stmt_error(stmt->ssps),
                       mysql_stmt_errno(stmt->ssps));

        /* For some errors - translating to more appropriate status */
        translate_error((char*)stmt->error.sqlstate.c_str(), MYERR_S1000,
                        mysql_stmt_errno(stmt->ssps));
        goto exit;
      }
      MYLOG_QUERY(stmt, "ssps has been executed");
    }
    else
    {
      MYLOG_QUERY(stmt, "Using direct execution");
      /* Need to close ps handler if it is open as our relsult will be generated
         by direct execution. and ps handler may create some chaos */
      ssps_close(stmt);

      native_error = stmt->bind_query_attrs(false);
      if (native_error == SQL_ERROR)
      {
        error = SQL_ERROR;
        goto exit;
      }

      native_error= mysql_real_query(stmt->dbc->mysql, query.c_str(),
        (unsigned long)query_length);
    }

    MYLOG_QUERY(stmt, "query has been executed");

    if (native_error)
    {
      MYLOG_QUERY(stmt, mysql_error(stmt->dbc->mysql));
      stmt->set_error("HY000");

      /* For some errors - translating to more appropriate status */
      translate_error((char*)stmt->error.sqlstate.c_str(), MYERR_S1000,
                      mysql_errno(stmt->dbc->mysql));
      goto exit;
    }

    if (!get_result_metadata(stmt, FALSE))
    {
      /* Query was supposed to return result, but result is NULL*/
      if (returned_result(stmt))
      {
        stmt->set_error(MYERR_S1000);
        goto exit;
      }
      else /* Query was not supposed to return a result */
      {
        error= SQL_SUCCESS;     /* no result set */
        stmt->state= ST_EXECUTED;
        update_affected_rows(stmt);
        goto exit;
      }
    }

    if (bind_result(stmt) || get_result(stmt))
    {
        stmt->set_error(MYERR_S1000);
        goto exit;
    }
    /* Caching row counts for queries returning resultset as well */
    //update_affected_rows(stmt);
    fix_result_types(stmt);

    /* If the only resultset is OUT params, then we can only detect
       corresponding server_status right after execution.
       If the RS is OUT params - we do not need to do store_result obviously */
    if (IS_PS_OUT_PARAMS(stmt))
    {
      /* This status(SERVER_PS_OUT_PARAMS) can be only if we used PS */
      ssps_get_out_params(stmt);

      if (stmt->out_params_state == OPS_STREAMS_PENDING)
      {
        error= SQL_PARAM_DATA_AVAILABLE;
        goto exit;
      }
    }


    error= SQL_SUCCESS;

exit:

    /*
      If the original query was modified, we reset stmt->query so that the
      next execution re-starts with the original query.
    */
    if (GET_QUERY(&stmt->orig_query))
    {
      stmt->query = stmt->orig_query;
      stmt->orig_query.reset(NULL, NULL, NULL);
    }

    return error;
}


/*
  @type    : myodbc3 internal
  @purpose : insert sql params at parameter positions
  @param[in]      stmt        Statement
  @param[in]      row         Parameters row
  @param[in,out]  finalquery  if NULL, final query is not copied
  @param[in,out]  length      Length of the query. Pointed value is used as initial offset
  @comment : it allocates and modifies finalquery (when finalquery!=NULL),
             so passing stmt->query->query can lead to memory leak.
*/

SQLRETURN insert_params(STMT *stmt, SQLULEN row, std::string &finalquery)
{
  assert(stmt);
  const char *query= GET_QUERY(&stmt->query);
  uint i,length, had_info= 0;
  SQLRETURN rc= SQL_SUCCESS;

  LOCK_DBC(stmt->dbc);

  adjust_param_bind_array(stmt);

  for ( i= 0; i < stmt->param_count; ++i )
  {
    DESCREC *aprec= desc_get_rec(stmt->apd, i, FALSE);
    DESCREC *iprec= desc_get_rec(stmt->ipd, i, FALSE);
    const char *pos;
    MYSQL_BIND * bind;

    if (stmt->dummy_state != ST_DUMMY_PREPARED &&
        (!aprec || !aprec->par.real_param_done))
    {
      rc= stmt->set_error( MYERR_07001,
          "The number of parameter markers is not equal "
          "to the number of parameters provided",0);
      goto error;
    }

    assert(iprec);

    if (ssps_used(stmt))
    {
      bind= get_param_bind(stmt, i, TRUE);

      rc = insert_param(stmt, bind, stmt->apd, aprec, iprec, row);
    }
    else
    {
      pos = stmt->query.get_param_pos(i);
      length= (uint) (pos-query);

      if (stmt->add_to_buffer(query, length) == NULL)
      {
        goto memerror;
      }

      query= pos+1;  /* Skip '?' */

      rc= insert_param(stmt, NULL, stmt->apd, aprec, iprec, row);
    }

    if (!SQL_SUCCEEDED(rc))
    {
      goto error;
    }
    else
    {
      if (rc == SQL_SUCCESS_WITH_INFO)
      {
        had_info= 1;
      }
    }
  }

  /* if any ofr parameters return SQL_SUCCESS_WITH_iNFO - returning it
     SQLSTATE corresponds to last SQL_SUCCESS_WITH_iNFO
  */
  if (had_info)
  {
    rc= SQL_SUCCESS_WITH_INFO;
  }

  if (!ssps_used(stmt))
  {
    length= (uint) (GET_QUERY_END(&stmt->query) - query);

    if (stmt->add_to_buffer(query, length) == NULL)
    {
      goto memerror;
    }

    finalquery = std::string(stmt->buf(), stmt->buf_pos());
  }

  return rc;

memerror:      /* Too much data */
  rc= stmt->set_error(MYERR_S1001,NULL,4001);

error:
  return rc;
}

static
void put_null_param(STMT *stmt, MYSQL_BIND *bind)
{
  if (bind != NULL && ssps_used(stmt))
  {
    bind->is_null_value= '\1';
  }
  else
  {
    stmt->add_to_buffer("NULL", 4);
  }
}


static
void put_default_value(STMT *stmt, MYSQL_BIND *bind)
{
  if (bind != NULL && ssps_used(stmt))
  {
    /* That is actually wrong, but i can't see a good way */
    bind->is_null_value= '\1';
  }
  else
  {
    stmt->add_to_buffer("DEFAULT", 7);
  }
}


static
BOOL allocate_param_buffer(MYSQL_BIND *bind, unsigned long length)
{
  if (bind == NULL)
    return FALSE;

  /* have to be very careful with that. it is probably better to put into
       a separate data structure. and free right after use */
  if (bind->buffer == NULL)
  {
    bind->buffer= myodbc_malloc(length, MYF(0));
    bind->buffer_length= length;
  }
  else if(bind->buffer_length < length)
  {
    bind->buffer= myodbc_realloc(bind->buffer, length);
    bind->buffer_length= length;
  }

  if (bind->buffer == NULL)
  {
    return TRUE;
  }

  return FALSE;
}


/* Buffer has to be allocated - no checks */
static
unsigned long add2param_value(MYSQL_BIND *bind, unsigned long pos,
                              const char *value, unsigned long length)
{
  memcpy((char*)bind->buffer + pos, value, length);

  bind->length_value= pos + length;
  return pos + length;
}


static
BOOL bind_param(MYSQL_BIND *bind, const char *value, unsigned long length,
                enum enum_field_types buffer_type)
{
  if (bind->buffer == (void*)value)
  {
    return FALSE;
  }

  if (allocate_param_buffer(bind, length))
  {
    return TRUE;
  }

  memcpy(bind->buffer, value, length);
  bind->buffer_type= buffer_type;
  bind->length_value= length;

  return FALSE;
}

/* TRUE - on memory allocation error */
static
BOOL put_param_value(STMT *stmt, MYSQL_BIND *bind,
                     const char * value, unsigned long length)
{
  if (bind)
  {
    return bind_param(bind, value, length, MYSQL_TYPE_STRING);
  }
  else
  {
    stmt->add_to_buffer(value, length);
  }

  return FALSE;
}


SQLRETURN check_c2sql_conversion_supported(STMT *stmt, DESCREC *aprec, DESCREC *iprec)
{
  if (aprec->type == SQL_DATETIME && iprec->type == SQL_INTERVAL
   || aprec->type == SQL_INTERVAL && iprec->type == SQL_DATETIME)
  {
    return stmt->set_error("07006", "Conversion is not supported", 0);
  }

  switch (aprec->concise_type)
  {
    /* Currently we do not support those types */
    case SQL_C_INTERVAL_YEAR:
    case SQL_C_INTERVAL_MONTH:
    case SQL_C_INTERVAL_DAY:
    case SQL_C_INTERVAL_HOUR:
    case SQL_C_INTERVAL_MINUTE:
    case SQL_C_INTERVAL_SECOND:
    case SQL_C_INTERVAL_YEAR_TO_MONTH:
    case SQL_C_INTERVAL_DAY_TO_HOUR:
    case SQL_C_INTERVAL_DAY_TO_MINUTE:
    case SQL_C_INTERVAL_DAY_TO_SECOND:
    case SQL_C_INTERVAL_MINUTE_TO_SECOND:
      return stmt->set_error("07006", "Conversion is not supported by driver", 0);
  }

  return SQL_SUCCESS;
}


/*
              stmt
              ctype       Input(parameter) value C type
              iprec
    [in,out]  rec         Pointer input and output value
              length      Pointer for result length
              buff        Pointer to a buffer for result value
              buff_max    Size of the buffer

*/
static
SQLRETURN convert_c_type2str(STMT *stmt, SQLSMALLINT ctype, DESCREC *iprec,
                             char **res, long *length, char *buff, uint buff_max)
{
  switch ( ctype )
  {
    case SQL_C_BINARY:
    case SQL_C_CHAR:
        break;

    case SQL_C_WCHAR:
      {
        /* Convert SQL_C_WCHAR (utf-16 or utf-32) to utf-8. */
        int has_utf8_maxlen4= 0;

        /* length is in bytes, we want chars */
        *length= *length / sizeof(SQLWCHAR);

        *res= (char*)sqlwchar_as_utf8_ext((SQLWCHAR*)*res, (SQLINTEGER*)length,
                                          (SQLCHAR*)buff, buff_max,
                                          &has_utf8_maxlen4);


        if (has_utf8_maxlen4 &&
            !is_minimum_version(stmt->dbc->mysql->server_version, "5.5.3"))
        {
          return stmt->set_error("HY000",
                                "Server does not support 4-byte encoded "
                                "UTF8 characters.", 0);
        }
        break;
      }

    case SQL_C_BIT:
    case SQL_C_TINYINT:
    case SQL_C_STINYINT:
        *length = (long)(my_int2str((long)*((signed char *)*res), buff, -10, 0) - buff);
        *res= buff;
        break;
    case SQL_C_UTINYINT:
        *length = (long)(my_int2str((long)*((unsigned char *)*res), buff, -10, 0) - buff);
        *res= buff;
        break;
    case SQL_C_SHORT:
    case SQL_C_SSHORT:
        *length = (long)(my_int2str((long)*((short int *)*res), buff, -10, 0) - buff);
        *res= buff;
        break;
    case SQL_C_USHORT:
        *length = (long)(my_int2str((long)*((unsigned short int *)*res), buff, -10, 0) -
                  buff);
        *res= buff;
        break;
    case SQL_C_LONG:
    case SQL_C_SLONG:
        *length = (long)(my_int2str(*((SQLINTEGER*) *res), buff, -10, 0) - buff);
        *res= buff;
        break;
    case SQL_C_ULONG:
        *length = (long)(my_int2str(*((SQLUINTEGER*) *res), buff, 10, 0) - buff);
        *res= buff;
        break;
    case SQL_C_SBIGINT:
        *length = (long)(myodbc_ll2str(*((longlong*) *res), buff, -10) - buff);
        *res= buff;
        break;
    case SQL_C_UBIGINT:
        *length = (long)(myodbc_ll2str(*((ulonglong*) *res), buff, 10) - buff);
        *res= buff;
        break;
    case SQL_C_FLOAT:
      if ( iprec->concise_type != SQL_NUMERIC && iprec->concise_type != SQL_DECIMAL )
      {
        // Better precision
        myodbc_d2str(*((float *)*res), buff, buff_max);
      }
      else
      {
        // We should perpare this data for string comparison, less precision
        myodbc_d2str(*((float *)*res), buff, buff_max, false);
      }
      *length = (long)strlen(*res= buff);
      break;
    case SQL_C_DOUBLE:
      if ( iprec->concise_type != SQL_NUMERIC && iprec->concise_type != SQL_DECIMAL )
      {
        myodbc_d2str(*((double *)*res), buff, buff_max);
      }
      else
      {
        // We should perpare this data for string comparison, less precision
        myodbc_d2str(*((double*)*res), buff, buff_max, false);
      }
      *length = (long)strlen(*res= buff);
      break;
    case SQL_C_DATE:
    case SQL_C_TYPE_DATE:
      {
        DATE_STRUCT *date= (DATE_STRUCT*) *res;
        if (stmt->dbc->ds.opt_MIN_DATE_TO_ZERO && !date->year
          && (date->month == date->day == 1))
        {
          *length = myodbc_snprintf(buff, buff_max, "0000-00-00");
        }
        else
        {
          *length = myodbc_snprintf(buff, buff_max, "%04d-%02d-%02d",
                                    date->year, date->month, date->day);
        }
        *res= buff;
        break;
      }
    case SQL_C_TIME:
    case SQL_C_TYPE_TIME:
      {
        TIME_STRUCT *time= (TIME_STRUCT*) *res;

        if (time->hour > 23)
        {
          return stmt->set_error("22008", "Not a valid time value supplied", 0);
        }

        *length = myodbc_snprintf(buff, buff_max, "%02d:%02d:%02d",
                                  time->hour, time->minute, time->second);
        *res= buff;
        break;
      }
    case SQL_C_TIMESTAMP:
    case SQL_C_TYPE_TIMESTAMP:
      {
        TIMESTAMP_STRUCT *time= (TIMESTAMP_STRUCT*) *res;

        if (stmt->dbc->ds.opt_MIN_DATE_TO_ZERO &&
            !time->year && (time->month == time->day == 1))
        {
          *length = myodbc_snprintf(buff, buff_max, "0000-00-00 %02d:%02d:%02d",
                                    time->hour, time->minute, time->second);
        }
        else
        {
          *length = myodbc_snprintf(buff, buff_max,
                    "%04d-%02d-%02d %02d:%02d:%02d",
                    time->year, time->month, time->day,
                    time->hour, time->minute, time->second);
        }

        if (time->fraction)
        {
          char *tmp_buf= buff + *length;

          /* Start cleaning from the end */
          int tmp_pos= 9;

          myodbc_snprintf(tmp_buf, buff_max - *length,
                          ".%09d", time->fraction);

          /*
            ODBC specification defines nanoseconds granularity for
            the fractional part of seconds. MySQL only supports
            microseconds for TIMESTAMP, TIME and DATETIME.

            We are trying to remove the trailing zeros because this
            does not really modify the data, but often helps to substitute
            9 digits with only 6.
          */
          while (tmp_pos && tmp_buf[tmp_pos] == '0')
          {
            tmp_buf[tmp_pos--]= 0;
          }

          *length+= tmp_pos + 1;
        }

        *res= buff;

        break;
      }
    case SQL_C_NUMERIC:
      {
        int trunc;
        SQL_NUMERIC_STRUCT *sqlnum= (SQL_NUMERIC_STRUCT *) *res;
        sqlnum_to_str(sqlnum, (SQLCHAR *)(buff + buff_max - 1),
                      (SQLCHAR **) res,
                      (SQLCHAR) iprec->precision,
                      (SQLSCHAR) iprec->scale, &trunc);
        *length = (long)strlen(*res);
        /* TODO no way to return an error here? */
        if (trunc == SQLNUM_TRUNC_FRAC)
        {/* 01S07 SQL_SUCCESS_WITH_INFO */
          stmt->set_error("01S07", "Fractional truncation", 0);
          return SQL_SUCCESS_WITH_INFO;
        }
        else if (trunc == SQLNUM_TRUNC_WHOLE)
        {/* 22003 SQL_ERROR */
          return SQL_ERROR;
        }
        break;
      }

    case SQL_C_INTERVAL_HOUR_TO_MINUTE:
    case SQL_C_INTERVAL_HOUR_TO_SECOND:
      {
        SQL_INTERVAL_STRUCT *interval= (SQL_INTERVAL_STRUCT*)*res;

        if (ctype == SQL_C_INTERVAL_HOUR_TO_MINUTE)
        {
          /* Dirty-hackish */
          if (ssps_used(stmt))
          {
            *length = myodbc_snprintf(buff, buff_max, "%d:%02d:00",
                                      interval->intval.day_second.hour,
                                      interval->intval.day_second.minute);
          }
          else
          {
            *length = myodbc_snprintf(buff, buff_max, "'%d:%02d:00'",
                                      interval->intval.day_second.hour,
                                      interval->intval.day_second.minute);
          }
        }
        else
        {
          if (ssps_used(stmt))
          {
            *length = myodbc_snprintf(buff, buff_max, "%d:%02d:%02d",
                                      interval->intval.day_second.hour,
                                      interval->intval.day_second.minute,
                                      interval->intval.day_second.second);
          }
          else
          {
            *length = myodbc_snprintf(buff, buff_max, "'%d:%02d:%02d'",
                                      interval->intval.day_second.hour,
                                      interval->intval.day_second.minute,
                                      interval->intval.day_second.second);
          }
        }

        *res= buff;
        break;
      }
    /* If we are missing processing of some valid C type. Probably means a bug elsewhere */
    default:
      return stmt->set_error("07006",
               "Conversion is not supported", 0);
  }
  return SQL_SUCCESS;
}

#define TIME_FIELDS_NONZERO(ts) (ts.hour||ts.minute||ts.second||ts.fraction)

static const std::string ts_chars{"0123456789-:"};

inline bool is_ts_char(char c)
{
  return std::string::npos != ts_chars.find(c);
}


/*
  Returns the date-time component of ODBC string like
  {dt yyyy-mm-dd hh:mm:ss}
*/
const char *get_date_time_substr(const char *data, long &len)
{
  const char* d_start = data;
  long idx = 0;
  while(len && !is_ts_char(*d_start))
  {
    --len;
    ++d_start;
  }

  if (!len)
    return nullptr;

  const char* d_end = d_start + len - 1;
  while(d_start < d_end && !is_ts_char(*d_end))
  {
    --len;
    --d_end;
  }

  return d_start;
}

/*
  Add the value of parameter to a string buffer.

  @param[in]      mysql
  @param[in,out]  toptr - either pointer to a string where to write
                  parameter value, or a pointer to MYSQL_BIND structure.
  @param[in]      apd The APD of the current statement
  @param[in]      aprec The APD record of the parameter
  @param[in]      iprec The IPD record of the parameter
*/
SQLRETURN insert_param(STMT *stmt, MYSQL_BIND *bind, DESC* apd,
                       DESCREC *aprec, DESCREC *iprec, SQLULEN row)
{
    long length;
    char buff[128], *data= NULL;
    BOOL convert= FALSE, free_data= FALSE;
    DBC *dbc= stmt->dbc;
    SQLLEN *octet_length_ptr= NULL;
    SQLLEN *indicator_ptr= NULL;
    SQLRETURN result= SQL_SUCCESS;

    if (aprec->octet_length_ptr)
    {
      octet_length_ptr= (SQLLEN*)ptr_offset_adjust(aprec->octet_length_ptr,
                                          apd->bind_offset_ptr,
                                          apd->bind_type,
                                          sizeof(SQLLEN), row);
      length = (long)*octet_length_ptr;
    }

    indicator_ptr= (SQLLEN*)ptr_offset_adjust(aprec->indicator_ptr,
                                     apd->bind_offset_ptr,
                                     apd->bind_type,
                                     sizeof(SQLLEN), row);

    if (aprec->data_ptr)
    {
      SQLINTEGER default_size= bind_length(aprec->concise_type,
                                           (ulong)aprec->octet_length);
      data= (char*)ptr_offset_adjust(aprec->data_ptr, apd->bind_offset_ptr,
                              apd->bind_type, default_size, row);
    }

    if (indicator_ptr && *indicator_ptr == SQL_NULL_DATA)
    {
      put_null_param(stmt, bind);

      return SQL_SUCCESS;
    }
    /*
      According to http://msdn.microsoft.com/en-us/library/ms710963%28VS.85%29.aspx

      "... If StrLen_or_IndPtr is a null pointer, the driver assumes that all
      input parameter values are non-NULL and that character and *binary* data
      is null-terminated."
    */
    else if (!octet_length_ptr || *octet_length_ptr == SQL_NTS)
    {
      if (data)
      {
        if (aprec->concise_type == SQL_C_WCHAR)
        {
          length = (long)sqlwcharlen((SQLWCHAR *)data) * sizeof(SQLWCHAR);
        }
        else
        {
          length = (long)strlen(data);
        }

        if (!octet_length_ptr && aprec->octet_length > 0 &&
            aprec->octet_length != SQL_SETPARAM_VALUE_MAX)
        {
          length = (long)myodbc_min(length, aprec->octet_length);
        }
      }
      else
      {
        length= 0;     /* TODO? This is actually an error */
      }
    }
    /*
      We may see SQL_COLUMN_IGNORE from bulk INSERT operations, where we
      may have been told to ignore a column in one particular row. So we
      try to insert DEFAULT, or NULL for really old servers.

      In case there are less parameters than result columns we have to
      insert NULL or DEFAULT.
    */
    else if (*octet_length_ptr == SQL_COLUMN_IGNORE ||
             /* empty values mean it's an unbound column */
             (*octet_length_ptr == 0 &&
              aprec->concise_type == SQL_C_DEFAULT &&
              aprec->par.val() == NULL))
    {
      put_default_value(stmt, bind);
      return SQL_SUCCESS;
    }
    else if (IS_DATA_AT_EXEC(octet_length_ptr))
    {
        length = (long)aprec->par.val_length();
        if ( !(data= aprec->par.val()) )
        {
          put_default_value(stmt, bind);
          return SQL_SUCCESS;
        }
    }

    PUSH_ERROR(check_c2sql_conversion_supported(stmt, aprec, iprec));

    switch ( aprec->concise_type )
    {

      case SQL_C_BINARY:
      case SQL_C_CHAR:
          convert= 1;
          break;

      default:
        switch(convert_c_type2str(stmt, aprec->concise_type, iprec,
                             &data, &length, buff, sizeof(buff)))
        {
        case SQL_ERROR:
          return SQL_ERROR;
        case SQL_SUCCESS_WITH_INFO:
          result= SQL_SUCCESS_WITH_INFO;
        }

        if (data == NULL)
        {
          goto memerror;
        }

        if (!(data >= buff && data < buff + sizeof(buff)))
        {
          free_data= TRUE;
        }
    }

    switch ( iprec->concise_type )
    {
      case SQL_DATE:
      case SQL_TYPE_DATE:
      case SQL_TYPE_TIMESTAMP:
      case SQL_TIMESTAMP:
        if (data[0] == '{')       /* Of type {d date } */
        {
          /* TODO: check if we need to check for truncation here as well? */
          if (bind != NULL)
          {
            /* First the datetime has to be filtered out of the brackets
               and other stuff. The validity of the format is up to the
               user.
            */
            const char *tt = get_date_time_substr(data, length);

            /* The length parameter is changed by get_date_time_substr()
               because it is passed as a reference */
            if (bind_param(bind, tt, length, MYSQL_TYPE_STRING))
            {
              goto memerror;
            }
          }
          else
          {
            stmt->add_to_buffer(data, length);
          }
          goto out;
        }

        if (iprec->concise_type == SQL_DATE
          || iprec->concise_type == SQL_TYPE_DATE)
        {
          TIMESTAMP_STRUCT ts;

          str_to_ts(&ts, data, length, 1, TRUE);

          /* Overflow also possible if converted from other C types
              http://msdn.microsoft.com/en-us/library/ms709385%28v=vs.85%29.aspx
              (if time fields nonzero sqlstate 22008 )
              http://msdn.microsoft.com/en-us/library/aa937531%28v=sql.80%29.aspx
              (Class values other than 01, except for the class IM,
              indicate an error and are accompanied by a return code
              of SQL_ERROR)

              Not sure if fraction should be considered as an overflow.
              In fact specs say about "time fields only"
            */
          if (!stmt->dbc->ds.opt_NO_DATE_OVERFLOW && TIME_FIELDS_NONZERO(ts))
          {
            return stmt->set_error("22008", "Date overflow", 0);
          }
        }
        /* else _binary introducer for binary data */
      case SQL_BINARY:
      case SQL_VARBINARY:
      case SQL_LONGVARBINARY:
        {
          if (bind != NULL)
          {
            /* i guess for ssps we do not need introducer */
            convert= 0;
          }
          else
          {
            if (dbc->cxn_charset_info->number !=
                dbc->ansi_charset_info->number)
            {
              stmt->add_to_buffer("_binary", 7);
            }
          }
          /* We have only added the introducer, data is added below. */
          break;
          }
          /* else treat as a string */
      case SQL_CHAR:
      case SQL_VARCHAR:
      case SQL_LONGVARCHAR:
      case SQL_WCHAR:
      case SQL_WVARCHAR:
      case SQL_WLONGVARCHAR:
        {
          if (aprec->concise_type == SQL_C_WCHAR &&
              dbc->cxn_charset_info->number != UTF8_CHARSET_NUMBER)
          {
            if (bind != NULL)
            {
              if (bind_param(bind, data, length, MYSQL_TYPE_BLOB))
              {
                goto memerror;
              }

              goto out;
            }
          }
          else if (aprec->concise_type != SQL_C_WCHAR &&
                    dbc->cxn_charset_info->number !=
                    dbc->ansi_charset_info->number)
          {
            if (bind != NULL)
            {
              if (bind_param(bind, data, length, MYSQL_TYPE_BLOB))
              {
                goto memerror;
              }

              goto out;
            }
          }
          /* We have only added the introducer, data is added below. */
          break;
        }
      case SQL_INTERVAL_HOUR_TO_MINUTE:
      case SQL_INTERVAL_HOUR_TO_SECOND:
        if (aprec->concise_type != SQL_C_INTERVAL_HOUR_TO_MINUTE &&
            aprec->concise_type != SQL_C_INTERVAL_HOUR_TO_SECOND)
        {
          /* Allow only interval to interval conversion */
          stmt->set_error("07006", "Conversion is not supported", 0);
        }
        else
        {
          if (put_param_value(stmt, bind, buff, length))
          {
            goto memerror;
          }
        }
        goto out;

      case SQL_TIME:
      case SQL_TYPE_TIME:
        if ( aprec->concise_type == SQL_C_TIMESTAMP ||
              aprec->concise_type == SQL_C_TYPE_TIMESTAMP )
        {
          TIMESTAMP_STRUCT *time= (TIMESTAMP_STRUCT*) aprec->data_ptr;

          if (time->hour > 23)
          {
            return stmt->set_error("22008", "Not a valid time value supplied", 0);
          }
          if (time->fraction)
          {
            /* fractional seconds truncated, need to set correct sqlstate 22008
            http://msdn.microsoft.com/en-us/library/ms709385%28v=vs.85%29.aspx */

            return stmt->set_error("22008", "Fractional truncation", 0);
          }

          if (bind != NULL)
          {
            length = myodbc_snprintf(buff, sizeof(buff), "%02d:%02d:%02d",
                                     time->hour, time->minute, time->second);
          }
          else
          {
            length = myodbc_snprintf(buff, sizeof(buff), "'%02d:%02d:%02d'",
                                     time->hour, time->minute, time->second);
          }

          if (put_param_value(stmt, bind, buff, length))
          {
            goto memerror;
          }
        }
        else
        {
          ulong time;
          int hours;
          SQLUINTEGER fraction;

          /* For now it is safer to assume a dot is always a separator */
          /* stmt->dbc->ds->dont_use_set_locale */
          get_fractional_part(data, length, TRUE, &fraction);

          if (fraction)
          {
            /* truncation need SQL_ERROR and sqlstate 22008*/
            return stmt->set_error("22008", "Fractional truncation", 0);
          }

          time= str_to_time_as_long(data,length);
          hours= time/10000;

          if (hours > 23)
          {
            return stmt->set_error("22008", "Not a valid time value supplied", 0);
          }

          if (bind != NULL)
          {
            length = myodbc_snprintf(buff, sizeof(buff), "%02d:%02d:%02d",
                                     hours,
                                     (int) time/100%100,
                                     (int) time%100);
          }
          else
          {
            length = myodbc_snprintf(buff, sizeof(buff), "'%02d:%02d:%02d'",
                                     hours,
                                     (int) time/100%100,
                                     (int) time%100);
          }

          if (put_param_value(stmt, bind, buff, length))
          {
            goto memerror;
          }
        }

        goto out;

      case SQL_BIT:
        {
          if (bind != NULL)
          {
            char bit_val= atoi(data)!= 0 ? 1 : 0;
            /* Generic ODBC supports only BIT(1) */
            bind_param(bind, &bit_val, 1, MYSQL_TYPE_TINY);
          }
          else if (!convert)
          {
            stmt->add_to_buffer(data, 1);
          }
          goto out;
        }
      case SQL_FLOAT:
      case SQL_REAL:
      case SQL_DOUBLE:
        /* If we have string -> float ; Fix locale characters for number */
        if (convert)
        {
          char *to= buff, *from= data;
          char *end= from+length;
          const char *local_thousands_sep = thousands_sep.c_str();
          const char *local_decimal_point = decimal_point.c_str();
          size_t local_thousands_sep_length = thousands_sep.length();
          size_t local_decimal_point_length = decimal_point.length();

          if (!stmt->dbc->ds.opt_NO_LOCALE)
          {
            /* force use of . as decimal point */
            local_thousands_sep= ",";
            local_thousands_sep_length= 1;
            local_decimal_point= ".";
            local_decimal_point_length= 1;
          }

          while ( *from && from < end )
          {
            if ( from[0] == local_thousands_sep[0] && is_prefix(from,local_thousands_sep) )
            {
              from+= local_thousands_sep_length;
            }
            else if ( from[0] == local_decimal_point[0] && is_prefix(from,local_decimal_point) )
            {
              from+= local_decimal_point_length;
              *to++='.';
            }
            else
            {
              *to++= *from++;
            }
          }
          if ( to == buff )
          {
            *to++='0';    /* Fix for empty strings */
          }
          data= buff;
          length= (uint) (to-buff);

          convert= 0;

        }
        /* Fall through */
      default:
        if (!convert)
        {
          put_param_value(stmt, bind, data, length);
          goto out;
        }
    }

    if (bind != NULL && stmt->setpos_op == 0)
    {
      bind_param(bind, data, length, MYSQL_TYPE_STRING);
    }
    else
    {
    /* Convert binary data to hex sequence */
      if(is_no_backslashes_escape_mode(stmt->dbc) &&
       is_binary_sql_type(iprec->concise_type))
      {
        SQLLEN transformed_len = 0;
        stmt->add_to_buffer(" 0x", 3);
        /* Make sure we have room for a fully-escaped string. */
        if (!stmt->extend_buffer(length * 2 + 1))
        {
          goto memerror;
        }

        copy_binhex_result(stmt, (SQLCHAR*)stmt->endbuf(),
          length * 2 + 1, &transformed_len, data, length);
        stmt->buf_add_pos(transformed_len);
      }
      else
      {
        stmt->add_to_buffer("'", 1);
        /* Make sure we have room for a fully-escaped string. */
        if (!(stmt->extend_buffer(length * 2 + 1)))
        {
          goto memerror;
        }

        size_t added = mysql_real_escape_string(dbc->mysql, stmt->endbuf(), data, length);
        stmt->buf_add_pos(added);
        stmt->add_to_buffer("'", 1);
      }
    }

out:
    if (free_data)
    {
      x_free(data);
    }

    return result;

memerror:
    if (free_data)
    {
      x_free(data);
    }
    return stmt->set_error( MYERR_S1001, NULL, 4001);
}


/*
  @type    : myodbc3 internal
  @purpose : positioned cursor update/delete
*/


SQLRETURN do_my_pos_cursor_std( STMT *pStmt, STMT *pStmtCursor )
{
  const char *    pszQuery= GET_QUERY(&pStmt->query);
  std::string     query;
  SQLRETURN       nReturn;

  if ( pStmt->error.native_error == ER_INVALID_CURSOR_NAME )
  {
      return pStmt->set_error("HY000", "ER_INVALID_CURSOR_NAME", 0 );
  }

  while ( isspace( *pszQuery ) )
      ++pszQuery;

  query = pszQuery;

  if ( !myodbc_casecmp( pszQuery, "delete", 6 ) )
  {
      nReturn = my_pos_delete_std( pStmtCursor, pStmt, 1, query );
  }
  else if ( !myodbc_casecmp( pszQuery, "update", 6 ) )
  {
      nReturn = my_pos_update_std( pStmtCursor, pStmt, 1, query );
  }
  else
  {
      nReturn = pStmt->set_error(MYERR_S1000, "Specified SQL syntax is not supported", 0 );
  }

  if ( SQL_SUCCEEDED( nReturn ) )
      pStmt->state = ST_EXECUTED;

  return( nReturn );
}


/*
  @type    : ODBC 1.0 API
  @purpose : executes a prepared statement, using the current values
  of the parameter marker variables if any parameter markers
  exist in the statement
*/

SQLRETURN SQL_API SQLExecute(SQLHSTMT hstmt)
{
  LOCK_STMT(hstmt);

  return my_SQLExecute((STMT *)hstmt);
}


BOOL map_error_to_param_status( SQLUSMALLINT *param_status_ptr, SQLRETURN rc)
{
  if (param_status_ptr)
  {
    switch (rc)
    {
    case SQL_SUCCESS:
      *param_status_ptr= SQL_PARAM_SUCCESS;
      break;
    case SQL_SUCCESS_WITH_INFO:
      *param_status_ptr= SQL_PARAM_SUCCESS_WITH_INFO;
      break;

    default:
      /* SQL_PARAM_ERROR is set at the end of processing for last erroneous paramset
         so we have diagnostics for it */
      *param_status_ptr= SQL_PARAM_DIAG_UNAVAILABLE;
      return TRUE;
    }
  }

  return FALSE;
}


/*
  @type    : myodbc3 internal
  @purpose : executes a prepared statement, using the current values
  of the parameter marker variables if any parameter markers
  exist in the statement
*/

SQLRETURN my_SQLExecute( STMT *pStmt )
{
  std::string query;
  char *cursor_pos;
  int         dae_rec, one_of_params_not_succeded= 0;
  bool is_select_stmt;
  int         connection_failure= 0;
  STMT       *pStmtCursor = pStmt;
  SQLRETURN   rc = 0;
  SQLULEN     row, length= 0;

  SQLUSMALLINT *param_operation_ptr= NULL, *param_status_ptr= NULL, *lastError= NULL;

  /* need to have a flag indicating if all parameters failed */
  int all_parameters_failed= pStmt->apd->array_size > 1 ? 1 : 0;

  if ( !pStmt )
      return SQL_ERROR;

  CLEAR_STMT_ERROR( pStmt );

  if (!GET_QUERY(&pStmt->query))
      return pStmt->set_error( MYERR_S1010,
                       "No previous SQLPrepare done", 0);

  if (is_set_names_statement(GET_QUERY(&pStmt->query)))
  {
    return pStmt->set_error( MYERR_42000,
                     "SET NAMES not allowed by driver", 0);
  }

  if ((cursor_pos= check_if_positioned_cursor_exists(pStmt, &pStmtCursor)))
  {
    /* Save a copy of the query, because we're about to modify it. */
    pStmt->orig_query = pStmt->query;

    /* Cursor statement use mysql_use_result - thus any operation
       will couse commands out of sync */
    if (if_forward_cache(pStmtCursor))
    {
      return pStmt->set_error(MYERR_S1010,NULL,0);
    }

    /* Chop off the 'WHERE CURRENT OF ...' - doing it a hard way...*/
    *cursor_pos= '\0';

    return do_my_pos_cursor_std(pStmt, pStmtCursor);
  }

  my_SQLFreeStmt((SQLHSTMT)pStmt,FREE_STMT_RESET_BUFFERS);

  query= GET_QUERY(&pStmt->query);

  is_select_stmt = pStmt->query.is_select_statement();

  /* if ssps is used for select query then convert it to non ssps
   single statement using UNION
  */
  if(is_select_stmt && ssps_used(pStmt) && pStmt->apd->array_size > 1)
  {
    ssps_close(pStmt);
  }

  if ( pStmt->ipd->rows_processed_ptr )
  {
    *pStmt->ipd->rows_processed_ptr= (SQLULEN)0;
  }

  LOCK_DBC(pStmt->dbc);

  for (row= 0; row < pStmt->apd->array_size; ++row)
  {
    if ( pStmt->param_count )
    {
      /* "The SQL_DESC_ROWS_PROCESSED_PTR field of the APD points to a buffer
      that contains the number of sets of parameters that have been processed,
      including error sets."
      "If SQL_NEED_DATA is returned, the value pointed to by the SQL_DESC_ROWS_PROCESSED_PTR
      field of the APD is set to the set of parameters that is being processed".
      And actually driver may continue to process paramsets after error.
      We need to decide do we want that.
      (http://msdn.microsoft.com/en-us/library/ms710963%28VS.85%29.aspx
      see "Using Arrays of Parameters")
      */
      if ( pStmt->ipd->rows_processed_ptr )
        *pStmt->ipd->rows_processed_ptr+= 1;

      param_operation_ptr= (SQLUSMALLINT*)ptr_offset_adjust(pStmt->apd->array_status_ptr,
                                            NULL,
                                            0/*SQL_BIND_BY_COLUMN*/,
                                            sizeof(SQLUSMALLINT), row);
      param_status_ptr= (SQLUSMALLINT*)ptr_offset_adjust(pStmt->ipd->array_status_ptr,
                                            NULL,
                                            0/*SQL_BIND_BY_COLUMN*/,
                                            sizeof(SQLUSMALLINT), row);

      if ( param_operation_ptr
        && *param_operation_ptr == SQL_PARAM_IGNORE)
      {
        /* http://msdn.microsoft.com/en-us/library/ms712631%28VS.85%29.aspx
           - comments for SQL_ATTR_PARAM_STATUS_PTR */
        if (param_status_ptr)
          *param_status_ptr= SQL_PARAM_UNUSED;

        continue;
      }

      /*
       * If any parameters are required at execution time, cannot perform the
       * statement. It will be done through SQLPutData() and SQLParamData().
       */
      if ((dae_rec= desc_find_dae_rec(pStmt->apd)) > -1)
      {
        if (pStmt->apd->array_size > 1)
        {
          rc= pStmt->set_error("HYC00", "Parameter arrays "
                              "with data at execution are not supported", 0);
          lastError= param_status_ptr;

          one_of_params_not_succeded= 1;

          /* For other errors we continue processing of paramsets
             So this creates some inconsistency. But I guess that's better
             that user see diagnostics for this type of error */
          break;
        }

        pStmt->current_param= dae_rec;
        pStmt->dae_type= DAE_NORMAL;

        return SQL_NEED_DATA;
      }

      /* Making copy of the built query if that is not last paramset for select
         query. */
      if (is_select_stmt && row < pStmt->apd->array_size - 1)
      {
        // Just ignore the dummy query
        std::string dummy;
        rc= insert_params(pStmt, row, dummy);
      }
      else
      {
        rc= insert_params(pStmt, row, query);
      }

      /* Setting status for this paramset*/
      if (map_error_to_param_status( param_status_ptr, rc))
      {
        lastError= param_status_ptr;
      }

      if (rc != SQL_SUCCESS)
      {
        one_of_params_not_succeded= 1;
      }

      if (!SQL_SUCCEEDED(rc))
      {
        continue/*return rc*/;
      }

      /* For "SELECT" statement constructing single statement using
         "UNION ALL" */
      if (pStmt->apd->array_size > 1 && is_select_stmt)
      {
        if (row < pStmt->apd->array_size - 1)
        {
          const char * stmtsBinder= " UNION ALL ";
          const size_t binderLength= strlen(stmtsBinder);

          pStmt->add_to_buffer(stmtsBinder, binderLength);
          length+= binderLength;
        }
      }
    }

    if (!is_select_stmt || row == pStmt->apd->array_size-1)
    {
      if (!connection_failure)
      {
        rc = do_query(pStmt, query);
      }
      else
      {
        /*
          If the original query was modified, we reset stmt->query so that the
          next execution re-starts with the original query.
        */
        if (GET_QUERY(&pStmt->orig_query))
        {
          pStmt->query = pStmt->orig_query;
          pStmt->orig_query.reset(NULL, NULL, NULL);
        }

        /* with broken connection we always return error for all next queries */
        rc= SQL_ERROR;
      }

      if (is_connection_lost(pStmt->error.native_error)
        && handle_connection_error(pStmt))
      {
        connection_failure= 1;
      }

      if (map_error_to_param_status(param_status_ptr, rc))
      {
        lastError= param_status_ptr;
      }

      /* if we have anything but not SQL_SUCCESS for any paramset, we return SQL_SUCCESS_WITH_INFO
         as the whole operation result */
      if (rc != SQL_SUCCESS)
      {
        one_of_params_not_succeded= 1;
      }
      else
      {
        all_parameters_failed= 0;
      }

      length= 0;
    }
  }

  /* Changing status for last detected error to SQL_PARAM_ERROR as we have
     diagnostics for it */
  if (lastError != NULL)
  {
    *lastError= SQL_PARAM_ERROR;
  }

  /* Setting not processed paramsets status to SQL_PARAM_UNUSED
     this is needed if we stop paramsets processing on error.
  */
  if (param_status_ptr != NULL)
  {
    while (++row < pStmt->apd->array_size)
    {
      param_status_ptr= (SQLUSMALLINT*)ptr_offset_adjust(pStmt->ipd->array_status_ptr,
                                          NULL,
                                          0/*SQL_BIND_BY_COLUMN*/,
                                          sizeof(SQLUSMALLINT), row);

      *param_status_ptr= SQL_PARAM_UNUSED;
    }
  }

  if (pStmt->dummy_state == ST_DUMMY_PREPARED)
      pStmt->dummy_state= ST_DUMMY_EXECUTED;

  if (pStmt->apd->array_size > 1)
  {
    if (all_parameters_failed)
    {
      return SQL_ERROR;
    }
    else if (one_of_params_not_succeded != 0)
    {
      return SQL_SUCCESS_WITH_INFO;
    }
  }

  return rc;
}


static SQLRETURN select_dae_param_desc(STMT *stmt, DESC **apd, unsigned int *param_count)
{
  *param_count= stmt->param_count;
  /* get the correct APD for the dae type we're handling */
  switch (stmt->dae_type)
  {
    case DAE_NORMAL:
      *apd= stmt->apd;
      break;
    case DAE_SETPOS_INSERT:
    case DAE_SETPOS_UPDATE:
      *apd= stmt->setpos_apd.get();
      *param_count = (unsigned int)stmt->ard->rcount();
      break;
    default:
      return stmt->set_error("HY010",
        "Invalid data at exec state", 0);
  }

  return SQL_SUCCESS;
}


/* Looks for next DAE parameter and returns true if finds it */
static SQLRETURN find_next_dae_param(STMT *stmt,  SQLPOINTER *token)
{
  unsigned int i, param_count;
  DESC *apd;

  PUSH_ERROR(select_dae_param_desc(stmt, &apd, &param_count));

  for (i= stmt->current_param; i < param_count; ++i)
  {
    DESCREC *aprec= desc_get_rec(apd, i, FALSE);
    SQLLEN *octet_length_ptr;

    assert(aprec);
    octet_length_ptr= (SQLLEN*)ptr_offset_adjust(aprec->octet_length_ptr,
                                        apd->bind_offset_ptr,
                                        apd->bind_type,
                                        sizeof(SQLLEN), 0);

    /* get the "placeholder" pointer the application bound */
    if (IS_DATA_AT_EXEC(octet_length_ptr))
    {
      SQLINTEGER default_size= bind_length(aprec->concise_type,
                                          (ulong)aprec->octet_length);
      stmt->current_param= i + 1;
      if (token)
      {
        *token= ptr_offset_adjust(aprec->data_ptr,
                                      apd->bind_offset_ptr,
                                      apd->bind_type,
                                      default_size, 0);
      }
      aprec->par.reset();
      aprec->par.is_dae= 1;

      return SQL_NEED_DATA;
    }
  }

  return SQL_SUCCESS;
}


static SQLRETURN execute_dae(STMT *stmt)
{
  SQLRETURN rc;
  std::string query;

  switch (stmt->dae_type)
  {
  case DAE_NORMAL:
    query = GET_QUERY(&stmt->query);
    if (!SQL_SUCCEEDED(rc= insert_params(stmt, 0, query)))
      break;
    rc= do_query(stmt, query);
    break;
  case DAE_SETPOS_INSERT:
    stmt->dae_type= DAE_SETPOS_DONE;
    rc= my_SQLSetPos((HSTMT)stmt, stmt->setpos_row, SQL_ADD, stmt->setpos_lock);
    stmt->setpos_apd.reset();
    break;
  case DAE_SETPOS_UPDATE:
    stmt->dae_type= DAE_SETPOS_DONE;
    rc= my_SQLSetPos((HSTMT)stmt, stmt->setpos_row, SQL_UPDATE, stmt->setpos_lock);
    stmt->setpos_apd.reset();
    break;
  }

  stmt->dae_type= 0;

  return rc;
}


static SQLRETURN find_next_out_stream(STMT *stmt, SQLPOINTER *token)
{
  DESCREC *rec= desc_find_outstream_rec(stmt, &stmt->current_param, &stmt->getdata.column);

  if (rec != NULL)
  {
    uint cur_column_number= stmt->getdata.column;
    stmt->reset_getdata_position();
    stmt->getdata.column= cur_column_number;
    stmt->getdata.src_offset= 0;

    if (token)
    {
      SQLINTEGER default_size= bind_length(rec->concise_type,
                                          (ulong)rec->octet_length);
      *token= ptr_offset_adjust(rec->data_ptr,
                                    stmt->ipd->bind_offset_ptr,
                                    stmt->ipd->bind_type,
                                    default_size, 0);
    }

    return SQL_PARAM_DATA_AVAILABLE;
  }
  else
  {
    /* Magical out params fetch */
    mysql_stmt_fetch(stmt->ssps);
    stmt->out_params_state = OPS_PREFETCHED;

    return SQL_SUCCESS;
  }
}


/*
  @type    : ODBC 1.0 API
  @purpose : is used in conjunction with SQLPutData to supply parameter
  data at statement execution time
*/

SQLRETURN SQL_API SQLParamData(SQLHSTMT hstmt, SQLPOINTER *prbgValue)
{
  STMT *stmt= (STMT *) hstmt;
  SQLRETURN rc= SQL_SUCCESS;

  /* We only check hstmt here */
  CHECK_HANDLE(hstmt);

  if (stmt->out_params_state != OPS_STREAMS_PENDING)
  {
    PUSH_ERROR(find_next_dae_param(stmt, prbgValue));

     /* Making sure that param_bind array is big enough - might be needed for send_long_data
       I guess there is a better place for this though */
    adjust_param_bind_array(stmt);

#ifdef WE_CAN_SEND_LONG_DATA_PROPERLY
    /*
      * Course of actions has to be following:
      * 1) We skip DAE parameters that are good for mysql_send_long_data(i.e. binary params) - specs
      *    allow us to do that. But we are memorizing we have them(first such parameter index)
      * 2) After we done with regular dae parameters we do insert_params/mysql_stmt_result_bind
      * 3) SQLParamData loops thru those parameters now skipping "regular" dae params
      * 4) After last parameter done - do_query as it is now, but it should not call
      *    mysql_stmt_bind_param
      */

    if (stmt->send_data_param > 0)
    {

      if (mysql_stmt_bind_param(stmt->ssps, (MYSQL_BIND*)stmt->param_bind->buffer))
      {
        stmt->set_error("HY000", mysql_stmt_error(stmt->ssps),
                      mysql_stmt_errno(stmt->ssps));

        /* For some errors - translating to more appropriate status */
        translate_error(stmt->error.sqlstate, MYERR_S1000,
                      mysql_stmt_errno(stmt->ssps));
        return SQL_ERROR;
      }

      /* Do all stuff for stmt->send_data_param as a dae */
      return SQL_NEED_DATA;
    }
#endif

    /* all data-at-exec params are complete. continue execution */
    PUSH_ERROR_UNLESS_EXT(rc, execute_dae(stmt), SQL_PARAM_DATA_AVAILABLE);
  }

  /* We could have got out streams just now */
  if (stmt->out_params_state == OPS_STREAMS_PENDING)
  {
    return find_next_out_stream(stmt, prbgValue);
  }

  /* We should not normally get here */
  return rc;
}


/*
  @type    : ODBC 1.0 API
  @purpose : allows an application to send data for a parameter or column to
  the driver at statement execution time. This function can be used
  to send character or binary data values in parts to a column with
  a character, binary, or data source specific data type.
*/

SQLRETURN SQL_API SQLPutData( SQLHSTMT      hstmt,
                              SQLPOINTER    rgbValue,
                              SQLLEN        cbValue )
{
  STMT *stmt= (STMT *) hstmt;
  DESCREC *aprec;

  CHECK_HANDLE(hstmt);
  CHECK_DATA_POINTER(stmt, rgbValue, cbValue);
  CHECK_STRLEN_OR_IND(stmt, rgbValue, cbValue);

  if (stmt->dae_type == DAE_NORMAL)
  {
    aprec= desc_get_rec(stmt->apd, stmt->current_param - 1, FALSE);
  }
  else
  {
    aprec= desc_get_rec(stmt->setpos_apd.get(), stmt->current_param - 1, FALSE);
  }

  if (!aprec)
  {
    return SQL_ERROR; // The error info is already set inside desc_get_rec()
  }

  if ( cbValue == SQL_NTS )
  {
    if (aprec->concise_type == SQL_C_WCHAR)
    {
      cbValue= sqlwcharlen((SQLWCHAR *)rgbValue) * sizeof(SQLWCHAR);
    }
    else
    {
      cbValue= strlen((const char*)rgbValue);
    }
  }

  if ( cbValue == SQL_NULL_DATA )
  {
    aprec->par.reset();
    return SQL_SUCCESS;
  }

  return send_long_data(stmt, stmt->current_param - 1, aprec, (const char *)rgbValue, (unsigned long)cbValue);
}


/**
  Cancel the query by opening another connection and using KILL when called
  from another thread while the query lock is being held. Otherwise, treat as
  SQLFreeStmt(hstmt, SQL_CLOSE).

  @param[in]  hstmt  Statement handle

  @return Standard ODBC result code
*/
SQLRETURN SQL_API SQLCancel(SQLHSTMT hstmt)
{
  MYSQL *second= NULL;
  DBC *dbc;
  STMT *stmt = (STMT *)hstmt;

  dbc= stmt->dbc;

  LOCK_DBC_DEFER(dbc); // implicitly declares dlock variable without locking

  /* If there's no query going on, just close the statement. */
  if (dlock.try_lock())
  {
    /*
      DBC lock can be released.
      STMT lock will be acquired inside my_SQLFreeStmtExtended.
    */
    dlock.unlock();
    return my_SQLFreeStmtExtended(hstmt, SQL_CLOSE,
      FREE_STMT_CLEAR_RESULT | FREE_STMT_DO_LOCK);
  }

  /*
    If the mutex was locked, we need to make a new connection and KILL the
    ongoing query. The mutex will be unlocked later automatically.

    This is an entirely new connection and it should not
    interfere with the existing one. Therefore, locking is not needed in
    the following block.
  */
  second = new_mysql();

  if (!second)
  {
    /*
      TODO: Check that ODBC spec indeed require no SQLSTATE.
      We do not set the SQLSTATE here, per the ODBC spec.
    */
    return SQL_ERROR;
  }

  /** @todo need to preserve and use ssl params */

  if (!mysql_real_connect(second, dbc->ds.opt_SERVER, dbc->ds.opt_UID,
                          dbc->ds.opt_PWD, NULL, dbc->ds.opt_PORT,
                          dbc->ds.opt_SOCKET, 0))
  {
    /* We do not set the SQLSTATE here, per the ODBC spec. */
    return SQL_ERROR;
  }

  {
    char buff[40];
    /* buff is always big enough because max length of %lu is 15 */
    myodbc_snprintf(buff, sizeof(buff), "KILL /*!50000 QUERY */ %lu", mysql_thread_id(dbc->mysql));
    if (mysql_real_query(second, buff, (unsigned long)strlen(buff)))
    {
      mysql_close(second);
      /* We do not set the SQLSTATE here, per the ODBC spec. */
      return SQL_ERROR;
    }
  }

  mysql_close(second);

  return SQL_SUCCESS;
}
