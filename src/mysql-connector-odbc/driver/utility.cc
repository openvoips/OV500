// Copyright (c) 2007, 2024, Oracle and/or its affiliates.
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
  @file  utility.c
  @brief Utility functions
*/

#include "driver.h"
#include "errmsg.h"
#include <ctype.h>
#include <iostream>
#include <map>

#define DATETIME_DIGITS 14

const SQLULEN sql_select_unlimited= (SQLULEN)-1;

/**
  Execute a SQL statement with setting sql_select_limit for each
  execution as SQL_ATTR_MAX_ROWS applies to all result sets on
  the statement and not connection.

  @param[in] dbc            The database connection
  @param[in] query          The query to execute
  @param[in] query_length   The length of query to execute
  @param[in] req_lock       The flag if dbc->lock thread lock should be used
                            when executing a query
*/
SQLRETURN exec_stmt_query(STMT *stmt, const char *query,
                          SQLULEN query_length, my_bool req_lock)
{
  SQLRETURN rc;
  if(!SQL_SUCCEEDED(rc= set_sql_select_limit(stmt->dbc,
                          stmt->stmt_options.max_rows, req_lock)))
  {
    /* if setting sql_select_limit fails, the query will probably fail anyway too */
    return rc;
  }
  stmt->buf_set_pos(0);
  return stmt->dbc->execute_query(query, query_length, req_lock);
}


SQLRETURN exec_stmt_query_std(STMT *stmt, const std::string &query,
                              bool req_lock)
{
  SQLRETURN rc;
  if(!SQL_SUCCEEDED(rc= set_sql_select_limit(stmt->dbc,
                          stmt->stmt_options.max_rows, req_lock)))
  {
    /* if setting sql_select_limit fails, the query will probably fail anyway too */
    return rc;
  }
  stmt->buf_set_pos(0);
  return stmt->dbc->execute_query(query.c_str(), query.size(), req_lock);
}


/**
  Link a list of fields to the current statement result.

  @todo This is a terrible idea. We need to purge this.

  @param[in] stmt        The statement to modify
  @param[in] fields      The fields to attach to the statement
  @param[in] field_count The number of fields
*/
void myodbc_link_fields(STMT *stmt, MYSQL_FIELD *fields, uint field_count)
{
    MYSQL_RES *result;
    assert(stmt);
    LOCK_DBC(stmt->dbc);
    result= stmt->result;
    result->fields= fields;
    result->field_count= field_count;
    result->current_field= 0;
    fix_result_types(stmt);
}


/**
Fills STMT's lengths array for given row. Makes use of myodbc_link_fields a bit
less terrible.

@param[in,out] stmt     The statement to modify
@param[in] fix_rules    Describes how to calculate lengths. For each element value
                        N > 0 - length is taken of field #N from original results
                                (counting from 1)
                        N <=0 - constant length (-N)
@param[in] row          Row for which to fix lengths
@param[in] field_count  The number of fields
*/
void fix_row_lengths(STMT *stmt, const long* fix_rules, uint row, uint field_count)
{
  unsigned long* orig_lengths, *row_lengths;
  uint i;

  if (!stmt->lengths)
    return;

  row_lengths =  stmt->lengths.get() + row * field_count;
  orig_lengths= mysql_fetch_lengths(stmt->result);

  for (i= 0; i < field_count; ++i)
  {
    if (fix_rules[i] > 0)
      row_lengths[i]= orig_lengths[fix_rules[i] - 1];
    else
      row_lengths[i]= -fix_rules[i];
  }
}


/**
  Figure out the ODBC result types for each column in the result set.

  @param[in] stmt The statement with result types to be fixed.
*/
void fix_result_types(STMT *stmt)
{
  uint i;
  MYSQL_RES *result= stmt->result;
  DESCREC *irrec;
  MYSQL_FIELD *field;
  int capint32= stmt->dbc->ds.opt_COLUMN_SIZE_S32 ? 1 : 0;

  stmt->state= ST_EXECUTED;  /* Mark set found */

  /* Populate the IRD records */
  size_t f_count = stmt->field_count();
  for (i= 0; i < f_count; ++i)
  {
    irrec= desc_get_rec(stmt->ird, i, TRUE);
    /* TODO function for this */
    field= result->fields + i;

    irrec->row.field= field;
    irrec->type= get_sql_data_type(stmt, field, NULL);
    irrec->concise_type= get_sql_data_type(stmt, field,
                                           (char *)irrec->row.type_name);
    switch (irrec->concise_type)
    {
    case SQL_DATE:
    case SQL_TYPE_DATE:
    case SQL_TIME:
    case SQL_TYPE_TIME:
    case SQL_TIMESTAMP:
    case SQL_TYPE_TIMESTAMP:
      irrec->type= SQL_DATETIME;
      break;
    default:
      irrec->type= irrec->concise_type;
      break;
    }
    irrec->datetime_interval_code=
      get_dticode_from_concise_type(irrec->concise_type);
    irrec->type_name= (SQLCHAR *) irrec->row.type_name;
    irrec->length= get_column_size(stmt, field);
    /* prevent overflowing of result when ADO multiplies the length
       by sizeof(SQLWCHAR) */
    if (capint32 && irrec->length == INT_MAX32 &&
        irrec->concise_type == SQL_WLONGVARCHAR)
      irrec->length /= sizeof(SQL_WCHAR);
    irrec->octet_length= get_transfer_octet_length(stmt, field);
    irrec->display_size= get_display_size(stmt, field);
    /* According ODBC specs(http://msdn.microsoft.com/en-us/library/ms713558%28v=VS.85%29.aspx)
      "SQL_DESC_OCTET_LENGTH ... For variable-length character or binary types,
      this is the maximum length in bytes. This value does not include the null
      terminator" Thus there is no need to add 1 to octet_length for char types */
    irrec->precision= 0;
    /* Set precision for all non-char/blob types */
    switch (irrec->type)
    {
    case SQL_BINARY:
    case SQL_BIT:
    case SQL_CHAR:
    case SQL_WCHAR:
    case SQL_VARBINARY:
    case SQL_VARCHAR:
    case SQL_WVARCHAR:
    case SQL_LONGVARBINARY:
    case SQL_LONGVARCHAR:
    case SQL_WLONGVARCHAR:
      break;
    default:
      irrec->precision= (SQLSMALLINT) irrec->length;
      break;
    }
    irrec->scale= myodbc_max(0, get_decimal_digits(stmt, field));
    if ((field->flags & NOT_NULL_FLAG) &&
        !(field->type == MYSQL_TYPE_TIMESTAMP) &&
        !(field->flags & AUTO_INCREMENT_FLAG))
      irrec->nullable= SQL_NO_NULLS;
    else
      irrec->nullable= SQL_NULLABLE;
    irrec->table_name= (SQLCHAR *)field->table;
    irrec->name= (SQLCHAR *)field->name;
    irrec->label= (SQLCHAR *)field->name;
    if (field->flags & AUTO_INCREMENT_FLAG)
      irrec->auto_unique_value= SQL_TRUE;
    else
      irrec->auto_unique_value= SQL_FALSE;
    /* We need support from server, when aliasing is there */
    irrec->base_column_name= (SQLCHAR *)field->org_name;
    irrec->base_table_name= (SQLCHAR *)field->org_table;
    if (field->flags & BINARY_FLAG) /* TODO this doesn't cut it anymore */
      irrec->case_sensitive= SQL_TRUE;
    else
      irrec->case_sensitive= SQL_FALSE;

    if (field->db && *field->db)
    {
        irrec->catalog_name= (SQLCHAR *)field->db;
    }
    else
    {
      irrec->catalog_name= (SQLCHAR *)(stmt->dbc->database.c_str());
    }

    irrec->fixed_prec_scale= SQL_FALSE;
    switch (field->type)
    {
    case MYSQL_TYPE_LONG_BLOB:
    case MYSQL_TYPE_TINY_BLOB:
    case MYSQL_TYPE_MEDIUM_BLOB:
    case MYSQL_TYPE_BLOB:
    case MYSQL_TYPE_VAR_STRING:
    case MYSQL_TYPE_STRING:
    case MYSQL_TYPE_JSON:
      if (field->charsetnr == BINARY_CHARSET_NUMBER)
      {
        irrec->literal_prefix= (SQLCHAR *) "0x";
        irrec->literal_suffix= (SQLCHAR *) "";
        // The charset number must be only changed for JSON
        if (field->type == MYSQL_TYPE_JSON)
          field->charsetnr = UTF8_CHARSET_NUMBER;
        break;
      }
      /* FALLTHROUGH */

    case MYSQL_TYPE_DATE:
    case MYSQL_TYPE_DATETIME:
    case MYSQL_TYPE_NEWDATE:
    case MYSQL_TYPE_TIMESTAMP:
    case MYSQL_TYPE_TIME:
    case MYSQL_TYPE_YEAR:
      irrec->literal_prefix= (SQLCHAR *) "'";
      irrec->literal_suffix= (SQLCHAR *) "'";
      break;

    default:
      irrec->literal_prefix= (SQLCHAR *) "";
      irrec->literal_suffix= (SQLCHAR *) "";
    }
    switch (field->type) {
    case MYSQL_TYPE_SHORT:
    case MYSQL_TYPE_LONG:
    case MYSQL_TYPE_LONGLONG:
    case MYSQL_TYPE_INT24:
    case MYSQL_TYPE_TINY:
    case MYSQL_TYPE_DECIMAL:
      irrec->num_prec_radix= 10;
      break;

    /* overwrite irrec->precision set above */
    case MYSQL_TYPE_FLOAT:
      irrec->num_prec_radix= 2;
      irrec->precision= 23;
      break;

    case MYSQL_TYPE_DOUBLE:
      irrec->num_prec_radix= 2;
      irrec->precision= 53;
      break;

    default:
      irrec->num_prec_radix= 0;
      break;
    }
    irrec->schema_name= (SQLCHAR *) "";
    /*
      We limit BLOB/TEXT types to SQL_PRED_CHAR due an oversight in ADO
      causing problems with updatable cursors.
    */
    switch (irrec->concise_type)
    {
      case SQL_LONGVARBINARY:
      case SQL_LONGVARCHAR:
      case SQL_WLONGVARCHAR:
        irrec->searchable= SQL_PRED_CHAR;
        break;
      default:
        irrec->searchable= SQL_SEARCHABLE;
        break;
    }
    irrec->unnamed= SQL_NAMED;
    if (field->flags & UNSIGNED_FLAG)
      irrec->is_unsigned= SQL_TRUE;
    else
      irrec->is_unsigned= SQL_FALSE;
    if (field->table && *field->table)
      irrec->updatable= SQL_ATTR_READWRITE_UNKNOWN;
    else
      irrec->updatable= SQL_ATTR_READONLY;
  }
}


/**
  Change a string with a length to a NUL-terminated string.

  @param[in,out] to      A buffer to write the string into, which must be at
                         at least length + 1 bytes long.
  @param[in]     from    A pointer to the beginning of the source string.
  @param[in]     length  The length of the string, or SQL_NTS if it is
                         already NUL-terminated.

  @return A pointer to a NUL-terminated string.
*/
char *fix_str(char *to, const char *from, int length)
{
    if ( !from )
        return "";
    if ( length == SQL_NTS )
        return (char *)from;
    strmake(to,from,length);
    return to;
}


/*
  Copy a field to a byte string.

  @param[in]     stmt         Pointer to statement
  @param[out]    result       Buffer for result
  @param[in]     result_bytes Size of result buffer (in bytes)
  @param[out]    avail_bytes  Pointer to buffer for storing number of bytes
                              available as result
  @param[in]     field        Field being stored
  @param[in]     src          Source data for result
  @param[in]     src_bytes    Length of source data (in bytes)

  @return Standard ODBC result code
*/
SQLRETURN
copy_binary_result(STMT *stmt,
                   SQLCHAR *result, SQLLEN result_bytes, SQLLEN *avail_bytes,
                   MYSQL_FIELD *field __attribute__((unused)),
                   char *src, unsigned long src_bytes)
{
  SQLRETURN rc= SQL_SUCCESS;
  ulong copy_bytes;

  if (!result_bytes)
    result= 0;       /* Don't copy anything! */

  assert(stmt);
  /* Apply max length to source data, if one was specified. */
  if (stmt->stmt_options.max_length &&
      src_bytes > stmt->stmt_options.max_length)
    src_bytes = (unsigned long)stmt->stmt_options.max_length;

  /* Initialize the source offset */
  if (!stmt->getdata.source)
    stmt->getdata.source= src;
  else
  {
    src_bytes -= (unsigned long)(stmt->getdata.source - src);
    src= stmt->getdata.source;

    /* If we've already retrieved everything, return SQL_NO_DATA_FOUND */
    if (src_bytes == 0)
      return SQL_NO_DATA_FOUND;
  }

  copy_bytes= myodbc_min((unsigned long)result_bytes, src_bytes);

  if (result && stmt->stmt_options.retrieve_data)
    memcpy(result, src, copy_bytes);

  if (avail_bytes && stmt->stmt_options.retrieve_data)
    *avail_bytes= src_bytes;

  stmt->getdata.source+= copy_bytes;

  if (src_bytes > (unsigned long)result_bytes)
  {
    stmt->set_error("01004", NULL, 0);
    rc= SQL_SUCCESS_WITH_INFO;
  }

  return rc;
}


/*
  Copy a field to an ANSI result string.

  @param[in]     stmt         Pointer to statement
  @param[out]    result       Buffer for result
  @param[in]     result_bytes Size of result buffer (in bytes)
  @param[out]    avail_bytes  Pointer to buffer for storing number of bytes
                              available as result
  @param[in]     field        Field being stored
  @param[in]     src          Source data for result
  @param[in]     src_bytes    Length of source data (in bytes)

  @return Standard ODBC result code
*/
SQLRETURN
copy_ansi_result(STMT *stmt,
                 SQLCHAR *result, SQLLEN result_bytes, SQLLEN *avail_bytes,
                 MYSQL_FIELD *field, char *src, unsigned long src_bytes)
{
  SQLRETURN rc= SQL_SUCCESS;
  char *src_end;
  SQLCHAR *result_end;
  ulong used_bytes= 0, used_chars= 0, error_count= 0;

  my_bool convert_binary= (field->charsetnr == BINARY_CHARSET_NUMBER ? 1 : 0) &&
                          (field->org_table_length == 0 ? 1 : 0) &&
                          stmt->dbc->ds.opt_NO_BINARY_RESULT;

  CHARSET_INFO *to_cs= stmt->dbc->ansi_charset_info,
               *from_cs= get_charset(field->charsetnr && (!convert_binary) ?
                                     field->charsetnr : UTF8_CHARSET_NUMBER,
                                     MYF(0));

  if (!from_cs)
    return stmt->set_error("07006", "Source character set not "
    "supported by client", 0);

  if (!result_bytes)
    result= 0;       /* Don't copy anything! */

  /*
   If we don't have to do any charset conversion, we can just use
   copy_binary_result() and NUL-terminate the buffer here.
  */
  if (to_cs->number == from_cs->number)
  {
    SQLLEN bytes;
    if (!avail_bytes)
      avail_bytes= &bytes;

    if (!result_bytes && !stmt->getdata.source)
    {
      *avail_bytes= src_bytes;
      stmt->set_error("01004", NULL, 0);
      return SQL_SUCCESS_WITH_INFO;
    }

    if (result_bytes)
      --result_bytes;

    rc= copy_binary_result(stmt, result, result_bytes, avail_bytes,
                           field, src, src_bytes);

    if (SQL_SUCCEEDED(rc) && result && stmt->stmt_options.retrieve_data)
      result[myodbc_min(*avail_bytes, result_bytes)]= '\0';

    return rc;
  }

  result_end= result + result_bytes - 1;
  /*
    Handle when result_bytes is 1 -- we have room for the NUL termination,
    but nothing else.
  */
  if (result == result_end)
  {
    if (stmt->stmt_options.retrieve_data)
      *result= '\0';

    result= 0;
  }

  /* Apply max length to source data, if one was specified. */
  if (stmt->stmt_options.max_length &&
      src_bytes > stmt->stmt_options.max_length)
    src_bytes = (unsigned long)stmt->stmt_options.max_length;
  src_end= src + src_bytes;

  /* Initialize the source offset */
  if (!stmt->getdata.source)
    stmt->getdata.source= src;
  else
    src= stmt->getdata.source;

  /* If we've already retrieved everything, return SQL_NO_DATA_FOUND */
  if (stmt->getdata.dst_bytes != (ulong)~0L &&
      stmt->getdata.dst_offset >= stmt->getdata.dst_bytes)
    return SQL_NO_DATA_FOUND;

  /*
    If we have leftover bytes from an earlier character conversion,
    copy as much as we can into place.
  */
  if (stmt->getdata.latest_bytes)
  {
    int new_bytes = (int)myodbc_min(stmt->getdata.latest_bytes -
                              stmt->getdata.latest_used,
                              result_end - result);
    if (stmt->stmt_options.retrieve_data)
      memcpy(result, stmt->getdata.latest + stmt->getdata.latest_used,
             new_bytes);

    if (new_bytes + stmt->getdata.latest_used == stmt->getdata.latest_bytes)
      stmt->getdata.latest_bytes= 0;

    result+= new_bytes;

    if (result == result_end)
    {
      if (stmt->stmt_options.retrieve_data)
        *result= '\0';
      result= NULL;
    }

    used_bytes+= new_bytes;
    stmt->getdata.latest_used+= new_bytes;
  }

  while (src < src_end)
  {
    /* Find the conversion functions. */
    auto mb_wc = from_cs->cset->mb_wc;
    auto wc_mb = to_cs->cset->wc_mb;
    my_wc_t wc;
    uchar dummy[7]; /* Longer than any single character in our charsets. */
    int to_cnvres;

    int cnvres= (*mb_wc)(from_cs, &wc, (uchar *)src, (uchar *)src_end);
    if (cnvres == MY_CS_ILSEQ)
    {
      ++error_count;
      cnvres= 1;
      wc= '?';
    }
    else if (cnvres < 0 && cnvres > MY_CS_TOOSMALL)
    {
      ++error_count;
      cnvres= abs(cnvres);
      wc= '?';
    }
    else if (cnvres < 0)
      return stmt->set_error("HY000",
                            "Unknown failure when converting character "
                            "from server character set.", 0);

convert_to_out:
    /*
     We always convert into a temporary buffer, so we can properly handle
     characters that are going to get split across requests.
    */
    if (stmt->stmt_options.retrieve_data)
    {
      to_cnvres= (*wc_mb)(to_cs, wc, result ? result : dummy,
                          (result ? result_end : dummy + sizeof(dummy)));
    }
    else
    {
      // If not copying data then pretend all went as planned
      to_cnvres= 1;
    }

    if (to_cnvres > 0)
    {
      used_chars+= 1;
      used_bytes+= to_cnvres;

      if (result)
        result+= to_cnvres;

      src+= cnvres;

      if (result && result == result_end)
      {
        if (stmt->getdata.dst_bytes != (ulong)~0L)
        {
          stmt->getdata.source+= cnvres;
          break;
        }

        if (stmt->stmt_options.retrieve_data)
          *result= '\0';

        result= NULL;
      }
      else if (!result)
        continue;

      stmt->getdata.source+= cnvres;
    }
    else if (result && to_cnvres <= MY_CS_TOOSMALL)
    {
      /*
       If we didn't have enough room for the character, we convert into
       stmt->getdata.latest and copy what we can. The next call to
       SQLGetData() will then copy what it can to the next buffer.
      */
      stmt->getdata.latest_bytes= (*wc_mb)(to_cs, wc, stmt->getdata.latest,
                                           stmt->getdata.latest +
                                           sizeof(stmt->getdata.latest));

      stmt->getdata.latest_used = (int)myodbc_min(stmt->getdata.latest_bytes,
                                            result_end - result);
      memcpy(result, stmt->getdata.latest, stmt->getdata.latest_used);
      result+= stmt->getdata.latest_used;

      if (stmt->stmt_options.retrieve_data)
        *result= '\0';

      result= NULL;

      used_chars+= 1;
      used_bytes+= stmt->getdata.latest_bytes;

      src+= stmt->getdata.latest_bytes;
      stmt->getdata.source+= stmt->getdata.latest_bytes;
    }
    else if (stmt->getdata.latest_bytes == MY_CS_ILUNI && wc != '?')
    {
      ++error_count;
      wc= '?';
      goto convert_to_out;
    }
    else
      return stmt->set_error("HY000",
                            "Unknown failure when converting character "
                            "to result character set.", 0);
  }

  if (result && stmt->stmt_options.retrieve_data)
    *result= 0;

  if (result_bytes && stmt->getdata.dst_bytes == (ulong)~0L)
  {
    stmt->getdata.dst_bytes= used_bytes;
    stmt->getdata.dst_offset= 0;
  }

  if (avail_bytes && stmt->stmt_options.retrieve_data)
  {
    if (stmt->getdata.dst_bytes != (ulong)~0L)
      *avail_bytes= stmt->getdata.dst_bytes - stmt->getdata.dst_offset;
    else
      *avail_bytes= used_bytes;
  }

  stmt->getdata.dst_offset+= myodbc_min((ulong)(result_bytes ?
                                                result_bytes - 1 : 0),
                                        used_bytes);

  /* Did we truncate the data? */
  if (!result_bytes || stmt->getdata.dst_bytes > stmt->getdata.dst_offset)
  {
    stmt->set_error("01004", NULL, 0);
    rc= SQL_SUCCESS_WITH_INFO;
  }

  /* Did we encounter any character conversion problems? */
  if (error_count)
  {
    stmt->set_error("22018", NULL, 0);
    rc= SQL_SUCCESS_WITH_INFO;
  }

  return rc;
}


/**
  Copy a result from the server into a buffer as a SQL_C_WCHAR.

  @param[in]     stmt        Pointer to statement
  @param[out]    result      Buffer for result
  @param[in]     result_len  Size of result buffer (in characters)
  @param[out]    avail_bytes Pointer to buffer for storing amount of data
                             available before this call
  @param[in]     field       Field being stored
  @param[in]     src         Source data for result
  @param[in]     src_bytes   Length of source data (in bytes)

  @return Standard ODBC result code
*/
SQLRETURN
copy_wchar_result(STMT *stmt,
                  SQLWCHAR *result, SQLINTEGER result_len, SQLLEN *avail_bytes,
                  MYSQL_FIELD *field, char *src, long src_bytes)
{
  SQLRETURN rc= SQL_SUCCESS;
  char *src_end;
  SQLWCHAR *result_end;
  ulong used_chars= 0, error_count= 0;
  CHARSET_INFO *from_cs= get_charset(field->charsetnr ? field->charsetnr :
                                     UTF8_CHARSET_NUMBER,
                                     MYF(0));

  if (!from_cs)
    return stmt->set_error("07006", "Source character set not "
    "supported by client", 0);

  if (!result_len)
    result= NULL; /* Don't copy anything! */

  result_end= result + result_len - 1;

  if (result == result_end)
  {
    *result= 0;
    result= 0;
  }

  /* Apply max length to source data, if one was specified. */
  if (stmt->stmt_options.max_length &&
      (ulong)src_bytes > stmt->stmt_options.max_length)
    src_bytes = (long)stmt->stmt_options.max_length;
  src_end= src + src_bytes;

  /* Initialize the source data */
  if (!stmt->getdata.source)
    stmt->getdata.source= src;
  else
    src= stmt->getdata.source;

  /* If we've already retrieved everything, return SQL_NO_DATA_FOUND */
  if (stmt->getdata.dst_bytes != (ulong)~0L &&
      stmt->getdata.dst_offset >= stmt->getdata.dst_bytes)
    return SQL_NO_DATA_FOUND;

  /* We may have a leftover char from the last call. */
  if (stmt->getdata.latest_bytes)
  {
    if (stmt->stmt_options.retrieve_data)
      memcpy(result, stmt->getdata.latest, sizeof(SQLWCHAR));
    ++result;

    if (result == result_end)
    {
      if (stmt->stmt_options.retrieve_data)
        *result= 0;
      result= NULL;
    }

    used_chars+= 1;
    stmt->getdata.latest_bytes= 0;
  }

  while (src < src_end)
  {
    /* Find the conversion functions. */
    auto mb_wc = from_cs->cset->mb_wc;
    auto wc_mb = utf16_charset_info->cset->wc_mb;
    my_wc_t wc = 0;
    UTF16 ubuf[5] = {0, 0, 0, 0, 0};
    int to_cnvres;

    int cnvres= (*mb_wc)(from_cs, &wc, (uchar *)src, (uchar *)src_end);

    if (cnvres == MY_CS_ILSEQ)
    {
      ++error_count;
      cnvres= 1;
      wc= '?';
    }
    else if (cnvres < 0 && cnvres > MY_CS_TOOSMALL)
    {
      ++error_count;
      cnvres= abs(cnvres);
      wc= '?';
    }
    else if (cnvres < 0)
      return stmt->set_error("HY000",
                            "Unknown failure when converting character "
                            "from server character set.", 0);

convert_to_out:
    // SQLWCHAR data should be UTF-16 on all platforms
    to_cnvres = (*wc_mb)(utf16_charset_info,
      wc, (uchar *)ubuf, (uchar *)ubuf + sizeof(ubuf));

    // Get the number of wide chars written
    size_t wchars_written = to_cnvres / 2;

    if (wchars_written > 0)
    {
      src+= cnvres;
      if (result)
      {
        if (stmt->stmt_options.retrieve_data)
          *result = ubuf[0];
        result++;
      }

      used_chars += (ulong)wchars_written;

      if (wchars_written > 1)
      {
        if (result && result != result_end)
        {
          if (stmt->stmt_options.retrieve_data)
            *result = ubuf[1];
          result++;
        }
        else if (result)
        {
          *((SQLWCHAR *)stmt->getdata.latest) = ubuf[1];
          stmt->getdata.latest_bytes = sizeof(SQLWCHAR);
          stmt->getdata.latest_used = 0;
          if (stmt->stmt_options.retrieve_data)
            *result = 0;
          result = NULL;

          if (stmt->getdata.dst_bytes != (ulong)~0L)
          {
            stmt->getdata.source+= cnvres;
            break;
          }
        }
        else
        {
          continue;
        }
      }

      if (result)
        stmt->getdata.source+= cnvres;

      if (result && result == result_end)
      {
        if (stmt->stmt_options.retrieve_data)
          *result= 0;
        result= NULL;
      }
    }
    else if (stmt->getdata.latest_bytes == MY_CS_ILUNI && wc != '?')
    {
      ++error_count;
      wc= '?';
      goto convert_to_out;
    }
    else
      return stmt->set_error("HY000",
                            "Unknown failure when converting character "
                            "to result character set.", 0);
  }

  if (result && stmt->stmt_options.retrieve_data)
    *result= 0;

  if (result_len && stmt->getdata.dst_bytes == (ulong)~0L)
  {
    stmt->getdata.dst_bytes= used_chars * sizeof(SQLWCHAR);
    stmt->getdata.dst_offset= 0;
  }

  if (avail_bytes && stmt->stmt_options.retrieve_data)
  {
    if (result_len)
      *avail_bytes= stmt->getdata.dst_bytes - stmt->getdata.dst_offset;
    else
      *avail_bytes= used_chars * sizeof(SQLWCHAR);
  }

  stmt->getdata.dst_offset+= myodbc_min((ulong)(result_len ?
                                                result_len - 1 : 0),
                                        used_chars) * sizeof(SQLWCHAR);

  /* Did we truncate the data? */
  if (!result_len || stmt->getdata.dst_bytes > stmt->getdata.dst_offset)
  {
    stmt->set_error("01004", NULL, 0);
    rc= SQL_SUCCESS_WITH_INFO;
  }

  /* Did we encounter any character conversion problems? */
  if (error_count)
  {
    stmt->set_error("22018", NULL, 0);
    rc= SQL_SUCCESS_WITH_INFO;
  }

  return rc;
}


/*
  @type    : myodbc internal
  @purpose : is used when converting binary data to hexadecimal
*/
template <typename T>
SQLRETURN copy_binhex_result(STMT *stmt,
                             T *rgbValue, SQLINTEGER cbValueMax,
                             SQLLEN *pcbValue,
                             char *src, ulong src_length)
{
  /** @todo padding of BINARY */
  T *dst = (T*) rgbValue;
  ulong length;
  ulong max_length = (ulong)stmt->stmt_options.max_length;
  ulong *offset = &stmt->getdata.src_offset;
  T NEAR _dig_vec[] = {
    '0', '1', '2', '3', '4', '5', '6', '7',
    '8', '9', 'A', 'B', 'C', 'D', 'E', 'F'
  };

  if (!cbValueMax)
  {
    /* Don't copy anything! */
    dst = 0;
  }

  if (max_length) /* If limit on char lengths */
  {
    set_if_smaller(cbValueMax, (long) max_length + 1);
    set_if_smaller(src_length, (max_length + 1) / 2);
  }

  if (*offset == (ulong) ~0L)
  {
    *offset = 0;   /* First call */
  }
  else if (*offset >= src_length)
  {
    return SQL_NO_DATA_FOUND;
  }

  src += *offset;
  src_length -= *offset;
  length = cbValueMax ? (ulong)(cbValueMax - 1) / 2 : 0;
  length = myodbc_min(src_length, length);
  (*offset) += length;     /* Fix for next call */
  if (pcbValue && stmt->stmt_options.retrieve_data)
    *pcbValue = src_length * 2 * sizeof(T);

  /* Bind allows null pointers */
  if (dst && stmt->stmt_options.retrieve_data)
  {
    ulong i;
    for (i= 0; i < length; ++i)
    {
      *dst ++= _dig_vec[(uchar) *src >> 4];
      *dst ++= _dig_vec[(uchar) *src++ & 15];
    }
    *dst = 0;
  }

  // All data is received when offset is not before
  // the end of the source
  if ( *offset * sizeof(T) >= src_length )
      return SQL_SUCCESS;

  stmt->set_error(MYERR_01004, NULL, 0);
  return SQL_SUCCESS_WITH_INFO;
}

// Need to declare template specializations to avoid linker errors
template
SQLRETURN copy_binhex_result<SQLCHAR>(STMT* stmt,
  SQLCHAR* rgbValue, SQLINTEGER cbValueMax,
  SQLLEN* pcbValue, char* src, ulong src_length);

template
SQLRETURN copy_binhex_result<SQLWCHAR>(STMT* stmt,
  SQLWCHAR* rgbValue, SQLINTEGER cbValueMax,
  SQLLEN* pcbValue, char* src, ulong src_length);

/*
  @type    : myodbc internal
  @purpose : is used when converting a bit a SQL_C_CHAR
*/

template<typename T>
SQLRETURN do_copy_bit_result(STMT *stmt,
                             T *result, SQLLEN result_bytes,
                             SQLLEN *avail_bytes,
                             MYSQL_FIELD *field __attribute__((unused)),
                             char *src, unsigned long src_bytes)
{
  // We need 2 chars, otherwise Don't copy anything! */
  if (result_bytes < 2)
    result = 0;

  /* Apply max length to source data, if one was specified. */
  if (stmt->stmt_options.max_length &&
      src_bytes > stmt->stmt_options.max_length)
    src_bytes = (unsigned long)stmt->stmt_options.max_length;

  /* Initialize the source offset */
  if (!stmt->getdata.source)
  {
    stmt->getdata.source= src;
  }
  else
  {
    src_bytes -= (unsigned long)(stmt->getdata.source - src);
    src= stmt->getdata.source;

    /* If we've already retrieved everything, return SQL_NO_DATA_FOUND */
    if (src_bytes == 0)
    {
      return SQL_NO_DATA_FOUND;
    }
  }

  if (result && stmt->stmt_options.retrieve_data)
  {
    result[0] = *src ? '1' : '0';
    result[1] = '\0';
  }


  if (avail_bytes && stmt->stmt_options.retrieve_data)
  {
    *avail_bytes= sizeof(T);
  }

  stmt->getdata.source++;

  return SQL_SUCCESS;
}

SQLRETURN copy_bit_result(STMT *stmt,
                          SQLCHAR *result, SQLLEN result_bytes, SQLLEN *avail_bytes,
                          MYSQL_FIELD *field __attribute__((unused)),
                          char *src, unsigned long src_bytes)
{
  return do_copy_bit_result<SQLCHAR>(stmt, result, result_bytes, avail_bytes, field,
                            src, src_bytes);
}

SQLRETURN wcopy_bit_result(STMT *stmt,
                          SQLWCHAR *result, SQLLEN result_bytes, SQLLEN *avail_bytes,
                          MYSQL_FIELD *field __attribute__((unused)),
                          char *src, unsigned long src_bytes)
{
  return do_copy_bit_result<SQLWCHAR>(stmt, result, result_bytes, avail_bytes, field,
                            src, src_bytes);
}

std::map<std::string, int> sql_data_types_map = {
  { "bit", SQL_BIT },
  { "decimal", SQL_DECIMAL },
  { "char", SQL_CHAR },
  { "tinyint", SQL_TINYINT },
  { "smallint", SQL_SMALLINT },
  { "mediumint", SQL_INTEGER },
  { "int", SQL_INTEGER },
  { "bigint", SQL_BIGINT },
  { "float", SQL_REAL },
  { "double", SQL_DOUBLE },
  { "year", SQL_SMALLINT },
  { "timestamp", SQL_TIMESTAMP },
  { "datetime", SQL_TIMESTAMP },
  { "date", SQL_TYPE_DATE },
  { "time", SQL_TIME },
  { "binary", SQL_BINARY },
  { "varbinary", SQL_VARBINARY },
  { "varchar", SQL_VARCHAR },
  { "tinyblob", SQL_LONGVARBINARY },
  { "tinytext", SQL_LONGVARCHAR },
  { "mediumblob", SQL_LONGVARBINARY },
  { "mediumtext", SQL_LONGVARCHAR },
  { "blob", SQL_LONGVARBINARY },
  { "text", SQL_LONGVARCHAR },
  { "longblob", SQL_LONGVARBINARY },
  { "longtext", SQL_LONGVARCHAR },
  { "enum", SQL_CHAR },
  { "set", SQL_CHAR },
  { "geometry", SQL_LONGVARBINARY },
  { "JSON", SQL_LONGVARCHAR },
  { "json", SQL_LONGVARCHAR }
};

SQLSMALLINT get_sql_data_type_from_str(const char *mysql_type_name)
{
  return sql_data_types_map.at(mysql_type_name);
}

SQLSMALLINT compute_sql_data_type(STMT *stmt, SQLSMALLINT sql_type,
  char octet_length, size_t col_size)
{
  switch(sql_type)
  {
    case SQL_TIMESTAMP:
      if (stmt->dbc->env->odbc_ver == SQL_OV_ODBC3)
        sql_type = SQL_TYPE_TIMESTAMP;
      break;
    case SQL_TYPE_DATE:
      if (stmt->dbc->env->odbc_ver < SQL_OV_ODBC3)
        sql_type = SQL_DATE;
      break;
    case SQL_TIME:
      if (stmt->dbc->env->odbc_ver == SQL_OV_ODBC3)
        sql_type = SQL_TYPE_TIME;
      break;
    case SQL_CHAR:
      if (octet_length > '1' && stmt->dbc->unicode)
        sql_type = SQL_WCHAR;
      break;
    case SQL_VARCHAR:
      if (octet_length > '1' && stmt->dbc->unicode)
        sql_type = SQL_WVARCHAR;
      break;
    case SQL_LONGVARCHAR:
      if (octet_length > '1' && stmt->dbc->unicode)
        sql_type = SQL_WLONGVARCHAR;
      break;
    case SQL_BIT:
      if (col_size > 1)
        sql_type = SQL_BINARY;
      break;
  }
  return sql_type;
}


/**
  Get the SQL data type and (optionally) type name for a MYSQL_FIELD.

  @param[in]  stmt
  @param[in]  field
  @param[out] buff

  @return  The SQL data type.
*/
SQLSMALLINT get_sql_data_type(STMT *stmt, MYSQL_FIELD *field, char *buff)
{
  my_bool field_is_binary= (field->charsetnr == BINARY_CHARSET_NUMBER ? 1 : 0) &&
                           ((field->org_table_length > 0 ? 1 : 0) ||
                            !stmt->dbc->ds.opt_NO_BINARY_RESULT);

  switch (field->type) {
  case MYSQL_TYPE_BIT:
    if (buff)
      (void)myodbc_stpmov(buff, "bit");
    /*
      MySQL's BIT type can have more than one bit, in which case we treat
      it as a BINARY field.
    */
    return (field->length > 1) ? SQL_BINARY : SQL_BIT;

  case MYSQL_TYPE_DECIMAL:
  case MYSQL_TYPE_NEWDECIMAL:
    if (buff)
      (void)myodbc_stpmov(buff, "decimal");
    return SQL_DECIMAL;

  case MYSQL_TYPE_TINY:
    /* MYSQL_TYPE_TINY could either be a TINYINT or a single CHAR. */
    if (buff)
    {
      buff= myodbc_stpmov(buff, (field->flags & NUM_FLAG) ? "tinyint" : "char");
      if (field->flags & UNSIGNED_FLAG)
        (void)myodbc_stpmov(buff, " unsigned");
    }
    return (field->flags & NUM_FLAG) ? SQL_TINYINT : SQL_CHAR;

  case MYSQL_TYPE_SHORT:
    if (buff)
    {
      buff= myodbc_stpmov(buff, "smallint");
      if (field->flags & UNSIGNED_FLAG)
        (void)myodbc_stpmov(buff, " unsigned");
    }
    return SQL_SMALLINT;

  case MYSQL_TYPE_INT24:
    if (buff)
    {
      buff= myodbc_stpmov(buff, "mediumint");
      if (field->flags & UNSIGNED_FLAG)
        (void)myodbc_stpmov(buff, " unsigned");
    }
    return SQL_INTEGER;

  case MYSQL_TYPE_LONG:
    if (buff)
    {
      buff= myodbc_stpmov(buff, "integer");
      if (field->flags & UNSIGNED_FLAG)
        (void)myodbc_stpmov(buff, " unsigned");
    }
    return SQL_INTEGER;

  case MYSQL_TYPE_LONGLONG:
    if (buff)
    {
      if (stmt->dbc->ds.opt_NO_BIGINT)
        buff= myodbc_stpmov(buff, "int");
      else
        buff= myodbc_stpmov(buff, "bigint");

      if (field->flags & UNSIGNED_FLAG)
        (void)myodbc_stpmov(buff, " unsigned");
    }

    if (stmt->dbc->ds.opt_NO_BIGINT)
      return SQL_INTEGER;

    return SQL_BIGINT;

  case MYSQL_TYPE_FLOAT:
    if (buff)
    {
      buff= myodbc_stpmov(buff, "float");
      if (field->flags & UNSIGNED_FLAG)
        (void)myodbc_stpmov(buff, " unsigned");
    }
    return SQL_REAL;

  case MYSQL_TYPE_DOUBLE:
    if (buff)
    {
      buff= myodbc_stpmov(buff, "double");
      if (field->flags & UNSIGNED_FLAG)
        (void)myodbc_stpmov(buff, " unsigned");
    }
    return SQL_DOUBLE;

  case MYSQL_TYPE_NULL:
    if (buff)
      (void)myodbc_stpmov(buff, "null");
    return SQL_VARCHAR;

  case MYSQL_TYPE_YEAR:
    if (buff)
      (void)myodbc_stpmov(buff, "year");
    return SQL_SMALLINT;

  case MYSQL_TYPE_TIMESTAMP:
    if (buff)
      (void)myodbc_stpmov(buff, "timestamp");
    if (stmt->dbc->env->odbc_ver == SQL_OV_ODBC3)
      return SQL_TYPE_TIMESTAMP;
    return SQL_TIMESTAMP;

  case MYSQL_TYPE_DATETIME:
    if (buff)
      (void)myodbc_stpmov(buff, "datetime");
    if (stmt->dbc->env->odbc_ver == SQL_OV_ODBC3)
      return SQL_TYPE_TIMESTAMP;
    return SQL_TIMESTAMP;

  case MYSQL_TYPE_NEWDATE:
  case MYSQL_TYPE_DATE:
    if (buff)
      (void)myodbc_stpmov(buff, "date");
    if (stmt->dbc->env->odbc_ver == SQL_OV_ODBC3)
      return SQL_TYPE_DATE;
    return SQL_DATE;

  case MYSQL_TYPE_TIME:
    if (buff)
      (void)myodbc_stpmov(buff, "time");
    if (stmt->dbc->env->odbc_ver == SQL_OV_ODBC3)
      return SQL_TYPE_TIME;
    return SQL_TIME;

  case MYSQL_TYPE_STRING:
    if (buff)
      (void)myodbc_stpmov(buff, field_is_binary ? "binary" : "char");

    return field_is_binary ? SQL_BINARY :
      (stmt->dbc->unicode && field->charsetnr != stmt->dbc->ansi_charset_info->number ?
       SQL_WCHAR : SQL_CHAR);

  /*
    MYSQL_TYPE_VARCHAR is never actually sent, this just silences
    a compiler warning.
  */
  case MYSQL_TYPE_VARCHAR:
  case MYSQL_TYPE_VAR_STRING:
    if (buff)
      (void)myodbc_stpmov(buff, field_is_binary ? "varbinary" : "varchar");

    return field_is_binary ? SQL_VARBINARY :
      (stmt->dbc->unicode && field->charsetnr != stmt->dbc->ansi_charset_info->number ?
       SQL_WVARCHAR : SQL_VARCHAR);

  case MYSQL_TYPE_TINY_BLOB:
    if (buff)
      (void)myodbc_stpmov(buff, field_is_binary ? "tinyblob" : "tinytext");

    return field_is_binary ? SQL_LONGVARBINARY :
      (stmt->dbc->unicode && field->charsetnr != stmt->dbc->ansi_charset_info->number ?
       SQL_WLONGVARCHAR : SQL_LONGVARCHAR);

  case MYSQL_TYPE_BLOB:
    if (buff)
    {
      switch(field->length)
      {
        case 255:
          (void)myodbc_stpmov(buff, field_is_binary ? "tinyblob" : "tinytext");
          break;
        case 16777215:
          (void)myodbc_stpmov(buff, field_is_binary ? "mediumblob" : "mediumtext");
          break;
        case 4294967295UL:
          (void)myodbc_stpmov(buff, field_is_binary ? "longblob" : "longtext");
          break;
        default:
          (void)myodbc_stpmov(buff, field_is_binary ? "blob" : "text");
      }
    }
    return field_is_binary ? SQL_LONGVARBINARY :
      (stmt->dbc->unicode && field->charsetnr != stmt->dbc->ansi_charset_info->number ?
       SQL_WLONGVARCHAR : SQL_LONGVARCHAR);

  case MYSQL_TYPE_MEDIUM_BLOB:
    if (buff)
      (void)myodbc_stpmov(buff, field_is_binary ? "mediumblob" : "mediumtext");

    return field_is_binary ? SQL_LONGVARBINARY :
      (stmt->dbc->unicode && field->charsetnr != stmt->dbc->ansi_charset_info->number ?
       SQL_WLONGVARCHAR : SQL_LONGVARCHAR);

  case MYSQL_TYPE_LONG_BLOB:
    if (buff)
      (void)myodbc_stpmov(buff, field_is_binary ? "longblob" : "longtext");

    return field_is_binary ? SQL_LONGVARBINARY :
      (stmt->dbc->unicode && field->charsetnr != stmt->dbc->ansi_charset_info->number ?
       SQL_WLONGVARCHAR : SQL_LONGVARCHAR);

  case MYSQL_TYPE_ENUM:
    if (buff)
      (void)myodbc_stpmov(buff, "enum");
    return SQL_CHAR;

  case MYSQL_TYPE_SET:
    if (buff)
      (void)myodbc_stpmov(buff, "set");
    return SQL_CHAR;

  case MYSQL_TYPE_GEOMETRY:
    if (buff)
      (void)myodbc_stpmov(buff, "geometry");
    return SQL_LONGVARBINARY;

  case MYSQL_TYPE_JSON:
    if (buff)
      (void)myodbc_stpmov(buff, "json");
    return stmt->dbc->unicode ? SQL_WLONGVARCHAR : SQL_LONGVARCHAR;
  }

  if (buff)
    *buff= '\0';
  return SQL_UNKNOWN_TYPE;
}


void sqlulen_to_str(char *buff, SQLULEN value)
{

}


/**
  Fill the display size buffer accordingly to size of SQLLEN
  @param[in,out]  buff
  @param[in]      stmt
  @param[in]      field

  @return  void
*/
SQLLEN fill_display_size_buff(char *buff, STMT *stmt, MYSQL_FIELD *field)
{
  /* See comment for fill_transfer_oct_len_buff()*/
  SQLLEN size= get_display_size(stmt, field);
  sprintf(buff,size == SQL_NO_TOTAL ? "%d" : (sizeof(SQLLEN) == 4 ? "%lu" : "%lld"), size);

  return size;
}


/**
  Fill the transfer octet length buffer accordingly to size of SQLLEN
  @param[in,out]  buff
  @param[in]      stmt
  @param[in]      field

  @return  void
*/
SQLLEN fill_transfer_oct_len_buff(char *buff, STMT *stmt, MYSQL_FIELD *field)
{
  /* The only possible negative value get_transfer_octet_length can return is SQL_NO_TOTAL
     But it can return value which is greater that biggest signed integer(%ld).
     Thus for other values we use %lu. %lld should fit
     all (currently) possible in mysql values.
  */
  SQLLEN len= get_transfer_octet_length(stmt, field);

  sprintf(buff, len == SQL_NO_TOTAL ? "%d" : (sizeof(SQLLEN) == 4 ? "%lu" : "%lld"), len );

  return len;
}


/**
  Fill the column size buffer accordingly to size of SQLULEN
  @param[in,out]  buff
  @param[in]      stmt
  @param[in]      field

  @return  void
*/
SQLULEN fill_column_size_buff(char *buff, STMT *stmt, MYSQL_FIELD *field)
{
  SQLULEN size= get_column_size(stmt, field);
  sprintf(buff, (size== SQL_NO_TOTAL ? "%d" :
      (sizeof(SQLULEN) == 4 ? "%lu" : "%llu")), size);
  return size;
}


/**
  Capping length value if connection option is set
*/
static SQLLEN cap_length(STMT *stmt, unsigned long real_length)
{
  if (stmt->dbc->ds.opt_COLUMN_SIZE_S32 != 0 && real_length > INT_MAX32)
    return INT_MAX32;

  return real_length;
}


/**
  Getting column size from string number
*/
SQLULEN get_column_size_from_str(STMT *stmt, const char *size_str)
{
  SQLULEN length = size_str ?
    (SQLULEN)std::strtoll(size_str, nullptr, 10) : 0;
  return cap_length(stmt, (unsigned long)length);
}


/**
  Get the column size (in characters) of a field, as defined at:
    http://msdn2.microsoft.com/en-us/library/ms711786.aspx

  @param[in]  stmt
  @param[in]  field

  @return  The column size of the field
*/
SQLULEN get_column_size(STMT *stmt, MYSQL_FIELD *field)
{
  SQLULEN length= field->length;
  /* Work around a bug in some versions of the server. */
  if (field->max_length > field->length)
    length= field->max_length;

  length= cap_length(stmt, (unsigned long)length);

  switch (field->type) {
  case MYSQL_TYPE_TINY:
    return (field->flags & NUM_FLAG) ? 3 : 1;

  case MYSQL_TYPE_SHORT:
    return 5;

  case MYSQL_TYPE_LONG:
    return 10;

  case MYSQL_TYPE_FLOAT:
    return 7;

  case MYSQL_TYPE_DOUBLE:
    return 15;

  case MYSQL_TYPE_NULL:
    return 0;

  case MYSQL_TYPE_LONGLONG:
    if (stmt->dbc->ds.opt_NO_BIGINT)
      return 10; /* same as MYSQL_TYPE_LONG */
    else
      return (field->flags & UNSIGNED_FLAG) ? 20 : 19;

  case MYSQL_TYPE_INT24:
    return 8;

  case MYSQL_TYPE_DATE:
    return 10;

  case MYSQL_TYPE_TIME:
    return 8;

  case MYSQL_TYPE_TIMESTAMP:
  case MYSQL_TYPE_DATETIME:
  case MYSQL_TYPE_NEWDATE:
    return 19;

  case MYSQL_TYPE_YEAR:
    return 4;

  case MYSQL_TYPE_DECIMAL:
  case MYSQL_TYPE_NEWDECIMAL:
    return (length -
            (!(field->flags & UNSIGNED_FLAG) ? 1:  0) - /* sign? */
            (field->decimals ? 1 : 0));             /* decimal point? */

  case MYSQL_TYPE_BIT:
    /*
      We treat a BIT(n) as a SQL_BIT if n == 1, otherwise we treat it
      as a SQL_BINARY, so length is (bits + 7) / 8.
    */
    if (length == 1)
      return 1;
    return (length + 7) / 8;

  case MYSQL_TYPE_ENUM:
  case MYSQL_TYPE_SET:
  case MYSQL_TYPE_VARCHAR:
  case MYSQL_TYPE_VAR_STRING:
  case MYSQL_TYPE_STRING:
    if (field->charsetnr == BINARY_CHARSET_NUMBER)
      return length;
    else
    {
      CHARSET_INFO *charset=  get_charset(field->charsetnr, MYF(0));
      return length / (charset ? charset->mbmaxlen : 1);
    }

  case MYSQL_TYPE_TINY_BLOB:
  case MYSQL_TYPE_MEDIUM_BLOB:
  case MYSQL_TYPE_LONG_BLOB:
  case MYSQL_TYPE_BLOB:
  case MYSQL_TYPE_GEOMETRY:
    return length;
  case MYSQL_TYPE_JSON:
    return UINT32_MAX / 4; // Because JSON is always UTF8MB4
  }

  return SQL_NO_TOTAL;
}


/**
  Get the decimal digits of a field, as defined at:
    http://msdn2.microsoft.com/en-us/library/ms709314.aspx

  @param[in]  stmt
  @param[in]  field

  @return  The decimal digits, or @c SQL_NO_TOTAL where it makes no sense

  The function has to return SQLSMALLINT, since it corresponds to SQL_DESC_SCALE
  or SQL_DESC_PRECISION for some data types.
*/
SQLSMALLINT get_decimal_digits(STMT *stmt __attribute__((unused)),
                          MYSQL_FIELD *field)
{
  switch (field->type) {
  case MYSQL_TYPE_DECIMAL:
  case MYSQL_TYPE_NEWDECIMAL:
    return field->decimals;

  /* All exact numeric types. */
  case MYSQL_TYPE_TINY:
  case MYSQL_TYPE_SHORT:
  case MYSQL_TYPE_LONG:
  case MYSQL_TYPE_LONGLONG:
  case MYSQL_TYPE_INT24:
  case MYSQL_TYPE_YEAR:
  case MYSQL_TYPE_TIME:
  case MYSQL_TYPE_TIMESTAMP:
  case MYSQL_TYPE_DATETIME:
    return 0;

  /* We treat MYSQL_TYPE_BIT as an exact numeric type only for BIT(1). */
  case MYSQL_TYPE_BIT:
    if (field->length == 1)
      return 0;

  default:
    /*
       This value is only used in some catalog functions. It's co-erced
       to zero for all descriptor use.
    */
    return SQL_NO_TOTAL;
  }
}


/**
  Get the transfer octet length of a field, as defined at:
    http://msdn2.microsoft.com/en-us/library/ms713979.aspx

  @param[in]  stmt
  @param[in]  field

  @return  The transfer octet length
*/
SQLLEN get_transfer_octet_length(STMT *stmt, MYSQL_FIELD *field)
{
  int capint32= stmt->dbc->ds.opt_COLUMN_SIZE_S32 ? 1 : 0;
  SQLLEN length;
  /* cap at INT_MAX32 due to signed value */
  if (field->length > INT_MAX32)
    length= INT_MAX32;
  else
    length= field->length;

  switch (field->type) {
  case MYSQL_TYPE_TINY:
    return 1;

  case MYSQL_TYPE_SHORT:
    return 2;

  case MYSQL_TYPE_INT24:
    return 3;

  case MYSQL_TYPE_LONG:
    return 4;

  case MYSQL_TYPE_FLOAT:
    return 4;

  case MYSQL_TYPE_DOUBLE:
    return 8;

  case MYSQL_TYPE_NULL:
    return 1;

  case MYSQL_TYPE_LONGLONG:
    return 20;

  case MYSQL_TYPE_DATE:
    return sizeof(SQL_DATE_STRUCT);

  case MYSQL_TYPE_TIME:
    return sizeof(SQL_TIME_STRUCT);

  case MYSQL_TYPE_TIMESTAMP:
  case MYSQL_TYPE_DATETIME:
  case MYSQL_TYPE_NEWDATE:
    return sizeof(SQL_TIMESTAMP_STRUCT);

  case MYSQL_TYPE_YEAR:
    return 1;

  case MYSQL_TYPE_DECIMAL:
  case MYSQL_TYPE_NEWDECIMAL:
    return field->length;

  case MYSQL_TYPE_BIT:
    /*
      We treat a BIT(n) as a SQL_BIT if n == 1, otherwise we treat it
      as a SQL_BINARY, so length is (bits + 7) / 8. field->length has
      the number of bits.
    */
    return (field->length + 7) / 8;

  case MYSQL_TYPE_STRING:
    if (stmt->dbc->ds.opt_PAD_SPACE)
    {
      unsigned int csmaxlen = get_charset_maxlen(field->charsetnr);
      if (!csmaxlen)
        return SQL_NO_TOTAL;

      /*
        We need to find out the real length using the database length and
        the current mode (ANSI or UNICODE)
      */
      length= (field->max_length > field->length ? field->max_length : field->length) / csmaxlen;
      return length;
    }
    /* FALLTHROUGH */

  case MYSQL_TYPE_ENUM:
  case MYSQL_TYPE_SET:
  case MYSQL_TYPE_VARCHAR:
  case MYSQL_TYPE_VAR_STRING:
  case MYSQL_TYPE_TINY_BLOB:
  case MYSQL_TYPE_MEDIUM_BLOB:
  case MYSQL_TYPE_LONG_BLOB:
  case MYSQL_TYPE_BLOB:
  case MYSQL_TYPE_GEOMETRY:
  case MYSQL_TYPE_JSON:
    if (field->charsetnr != stmt->dbc->ansi_charset_info->number &&
        field->charsetnr != BINARY_CHARSET_NUMBER)
      length *= stmt->dbc->ansi_charset_info->mbmaxlen;
    if (capint32 && length > INT_MAX32)
      length= INT_MAX32;
    return length;
  }

  return SQL_NO_TOTAL;
}


/**
  Get the display size of a field, as defined at:
    http://msdn2.microsoft.com/en-us/library/ms713974.aspx

  @param[in]  stmt
  @param[in]  field

  @return  The display size
*/
SQLLEN get_display_size(STMT *stmt __attribute__((unused)),MYSQL_FIELD *field)
{
  int capint32 = stmt->dbc->ds.opt_COLUMN_SIZE_S32 ? 1 : 0;
  CHARSET_INFO *charset= get_charset(field->charsetnr, MYF(0));
  unsigned int mbmaxlen= charset ? charset->mbmaxlen : 1;

  switch (field->type) {
  case MYSQL_TYPE_TINY:
    return 3 + (field->flags & UNSIGNED_FLAG ? 1 : 0);

  case MYSQL_TYPE_SHORT:
    return 5 + (field->flags & UNSIGNED_FLAG ? 1 : 0);

  case MYSQL_TYPE_INT24:
    return 8 + (field->flags & UNSIGNED_FLAG ? 1 : 0);

  case MYSQL_TYPE_LONG:
    return 10 + (field->flags & UNSIGNED_FLAG ? 1 : 0);

  case MYSQL_TYPE_FLOAT:
    return 14;

  case MYSQL_TYPE_DOUBLE:
    return 24;

  case MYSQL_TYPE_NULL:
    return 1;

  case MYSQL_TYPE_LONGLONG:
    return 20;

  case MYSQL_TYPE_DATE:
    return 10;

  case MYSQL_TYPE_TIME:
    return 8;

  case MYSQL_TYPE_TIMESTAMP:
  case MYSQL_TYPE_DATETIME:
  case MYSQL_TYPE_NEWDATE:
    return 19;

  case MYSQL_TYPE_YEAR:
    return 4;

  case MYSQL_TYPE_DECIMAL:
  case MYSQL_TYPE_NEWDECIMAL:
    return field->length;

  case MYSQL_TYPE_BIT:
    /*
      We treat a BIT(n) as a SQL_BIT if n == 1, otherwise we treat it
      as a SQL_BINARY, so display length is (bits + 7) / 8 * 2.
      field->length has the number of bits.
    */
    if (field->length == 1)
      return 1;
    return (field->length + 7) / 8 * 2;

  case MYSQL_TYPE_ENUM:
  case MYSQL_TYPE_SET:
  case MYSQL_TYPE_VARCHAR:
  case MYSQL_TYPE_VAR_STRING:
  case MYSQL_TYPE_STRING:
  case MYSQL_TYPE_TINY_BLOB:
  case MYSQL_TYPE_MEDIUM_BLOB:
  case MYSQL_TYPE_LONG_BLOB:
  case MYSQL_TYPE_BLOB:
  case MYSQL_TYPE_GEOMETRY:
    {
      unsigned long length;
      if (field->charsetnr == BINARY_CHARSET_NUMBER)
        length= field->length * 2;
      else
        length= field->length / mbmaxlen;
      if (capint32 && length > INT_MAX32)
        length= INT_MAX32;
      return length;
    }
  case MYSQL_TYPE_JSON:
    return UINT_MAX32 / 4; // 4 is the character size in UTF8MB4

  }

  return SQL_NO_TOTAL;
}


/*
   Map the concise type (value or param) to the correct datetime or
   interval code.
   See SQLSetDescField()/SQL_DESC_DATETIME_INTERVAL_CODE docs for details.
*/
SQLSMALLINT
get_dticode_from_concise_type(SQLSMALLINT concise_type)
{
  /* figure out SQL_DESC_DATETIME_INTERVAL_CODE from SQL_DESC_CONCISE_TYPE */
  switch (concise_type)
  {
  case SQL_C_TYPE_DATE:
    return SQL_CODE_DATE;
  case SQL_C_TYPE_TIME:
    return SQL_CODE_TIME;
  case SQL_C_TIMESTAMP:
  case SQL_C_TYPE_TIMESTAMP:
    return SQL_CODE_TIMESTAMP;
  case SQL_C_INTERVAL_DAY:
    return SQL_CODE_DAY;
  case SQL_C_INTERVAL_DAY_TO_HOUR:
    return SQL_CODE_DAY_TO_HOUR;
  case SQL_C_INTERVAL_DAY_TO_MINUTE:
    return SQL_CODE_DAY_TO_MINUTE;
  case SQL_C_INTERVAL_DAY_TO_SECOND:
    return SQL_CODE_DAY_TO_SECOND;
  case SQL_C_INTERVAL_HOUR:
    return SQL_CODE_HOUR;
  case SQL_C_INTERVAL_HOUR_TO_MINUTE:
    return SQL_CODE_HOUR_TO_MINUTE;
  case SQL_C_INTERVAL_HOUR_TO_SECOND:
    return SQL_CODE_HOUR_TO_SECOND;
  case SQL_C_INTERVAL_MINUTE:
    return SQL_CODE_MINUTE;
  case SQL_C_INTERVAL_MINUTE_TO_SECOND:
    return SQL_CODE_MINUTE_TO_SECOND;
  case SQL_C_INTERVAL_MONTH:
    return SQL_CODE_MONTH;
  case SQL_C_INTERVAL_SECOND:
    return SQL_CODE_SECOND;
  case SQL_C_INTERVAL_YEAR:
    return SQL_CODE_YEAR;
  case SQL_C_INTERVAL_YEAR_TO_MONTH:
    return SQL_CODE_YEAR_TO_MONTH;
  default:
    return 0;
  }
}


/*
   Map the SQL_DESC_DATETIME_INTERVAL_CODE to the SQL_DESC_CONCISE_TYPE
   for datetime types.

   Constant returned is valid for both param and value types.
*/
SQLSMALLINT get_concise_type_from_datetime_code(SQLSMALLINT dticode)
{
  switch (dticode)
  {
  case SQL_CODE_DATE:
    return SQL_C_TYPE_DATE;
  case SQL_CODE_TIME:
    return SQL_C_TYPE_DATE;
  case SQL_CODE_TIMESTAMP:
    return SQL_C_TYPE_TIMESTAMP;
  default:
    return 0;
  }
}


/*
   Map the SQL_DESC_DATETIME_INTERVAL_CODE to the SQL_DESC_CONCISE_TYPE
   for interval types.

   Constant returned is valid for both param and value types.
*/
SQLSMALLINT get_concise_type_from_interval_code(SQLSMALLINT dticode)
{
  switch (dticode)
  {
  case SQL_CODE_DAY:
    return SQL_C_INTERVAL_DAY;
  case SQL_CODE_DAY_TO_HOUR:
    return SQL_C_INTERVAL_DAY_TO_HOUR;
  case SQL_CODE_DAY_TO_MINUTE:
    return SQL_C_INTERVAL_DAY_TO_MINUTE;
  case SQL_CODE_DAY_TO_SECOND:
    return SQL_C_INTERVAL_DAY_TO_SECOND;
  case SQL_CODE_HOUR:
    return SQL_C_INTERVAL_HOUR;
  case SQL_CODE_HOUR_TO_MINUTE:
    return SQL_C_INTERVAL_HOUR_TO_MINUTE;
  case SQL_CODE_HOUR_TO_SECOND:
    return SQL_C_INTERVAL_HOUR_TO_SECOND;
  case SQL_CODE_MINUTE:
    return SQL_C_INTERVAL_MINUTE;
  case SQL_CODE_MINUTE_TO_SECOND:
    return SQL_C_INTERVAL_MINUTE_TO_SECOND;
  case SQL_CODE_MONTH:
    return SQL_C_INTERVAL_MONTH;
  case SQL_CODE_SECOND:
    return SQL_C_INTERVAL_SECOND;
  case SQL_CODE_YEAR:
    return SQL_C_INTERVAL_YEAR;
  case SQL_CODE_YEAR_TO_MONTH:
    return SQL_C_INTERVAL_YEAR_TO_MONTH;
  default:
    return 0;
  }
}


/*
   Map the concise type to a (possibly) more general type.
*/
SQLSMALLINT get_type_from_concise_type(SQLSMALLINT concise_type)
{
  /* set SQL_DESC_TYPE from SQL_DESC_CONCISE_TYPE */
  switch (concise_type)
  {
  /* datetime data types */
  case SQL_C_TYPE_DATE:
  case SQL_C_TYPE_TIME:
  case SQL_C_TYPE_TIMESTAMP:
    return SQL_DATETIME;
  /* interval data types */
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
  case SQL_C_INTERVAL_HOUR_TO_MINUTE:
  case SQL_C_INTERVAL_HOUR_TO_SECOND:
  case SQL_C_INTERVAL_MINUTE_TO_SECOND:
    return SQL_INTERVAL;
  /* else, set same */
  default:
    return concise_type;
  }
}


/*
  @type    : myodbc internal
  @purpose : returns internal type to C type
*/

int unireg_to_c_datatype(MYSQL_FIELD *field)
{
    switch ( field->type )
    {
        case MYSQL_TYPE_BIT:
            /*
              MySQL's BIT type can have more than one bit, in which case we
              treat it as a BINARY field.
            */
            return (field->length > 1) ? SQL_C_BINARY : SQL_C_BIT;
        case MYSQL_TYPE_TINY:
            return SQL_C_TINYINT;
        case MYSQL_TYPE_YEAR:
        case MYSQL_TYPE_SHORT:
            return SQL_C_SHORT;
        case MYSQL_TYPE_INT24:
        case MYSQL_TYPE_LONG:
            return SQL_C_LONG;
        case MYSQL_TYPE_FLOAT:
            return SQL_C_FLOAT;
        case MYSQL_TYPE_DOUBLE:
            return SQL_C_DOUBLE;
        case MYSQL_TYPE_TIMESTAMP:
        case MYSQL_TYPE_DATETIME:
            return SQL_C_TIMESTAMP;
        case MYSQL_TYPE_NEWDATE:
        case MYSQL_TYPE_DATE:
            return SQL_C_DATE;
        case MYSQL_TYPE_TIME:
            return SQL_C_TIME;
        case MYSQL_TYPE_BLOB:
        case MYSQL_TYPE_TINY_BLOB:
        case MYSQL_TYPE_MEDIUM_BLOB:
        case MYSQL_TYPE_LONG_BLOB:
        case MYSQL_TYPE_JSON:
            return SQL_C_BINARY;
        case MYSQL_TYPE_LONGLONG: /* Must be returned as char */
        default:
            return SQL_C_CHAR;
    }
}


/*
  @type    : myodbc internal
  @purpose : returns default C type for a given SQL type
*/

int default_c_type(int sql_data_type)
{
    switch ( sql_data_type )
    {
        case SQL_CHAR:
        case SQL_VARCHAR:
        case SQL_LONGVARCHAR:
        case SQL_DECIMAL:
        case SQL_NUMERIC:
        default:
            return SQL_C_CHAR;
        case SQL_BIGINT:
            return SQL_C_SBIGINT;
        case SQL_BIT:
            return SQL_C_BIT;
        case SQL_TINYINT:
            return SQL_C_TINYINT;
        case SQL_SMALLINT:
            return SQL_C_SHORT;
        case SQL_INTEGER:
            return SQL_C_LONG;
        case SQL_REAL:
        case SQL_FLOAT:
            return SQL_C_FLOAT;
        case SQL_DOUBLE:
            return SQL_C_DOUBLE;
        case SQL_BINARY:
        case SQL_VARBINARY:
        case SQL_LONGVARBINARY:
            return SQL_C_BINARY;
        case SQL_DATE:
        case SQL_TYPE_DATE:
            return SQL_C_DATE;
        case SQL_TIME:
        case SQL_TYPE_TIME:
            return SQL_C_TIME;
        case SQL_TIMESTAMP:
        case SQL_TYPE_TIMESTAMP:
            return SQL_C_TIMESTAMP;
    }
}


/*
  @type    : myodbc internal
  @purpose : returns bind length
*/

ulong bind_length(int sql_data_type,ulong length)
{
    switch ( sql_data_type )
    {
        case SQL_C_BIT:
        case SQL_C_TINYINT:
        case SQL_C_STINYINT:
        case SQL_C_UTINYINT:
            return 1;
        case SQL_C_SHORT:
        case SQL_C_SSHORT:
        case SQL_C_USHORT:
            return 2;
        case SQL_C_LONG:
        case SQL_C_SLONG:
        case SQL_C_ULONG:
            return sizeof(SQLINTEGER);
        case SQL_C_FLOAT:
            return sizeof(float);
        case SQL_C_DOUBLE:
            return sizeof(double);
        case SQL_C_DATE:
        case SQL_C_TYPE_DATE:
            return sizeof(DATE_STRUCT);
        case SQL_C_TIME:
        case SQL_C_TYPE_TIME:
            return sizeof(TIME_STRUCT);
        case SQL_C_TIMESTAMP:
        case SQL_C_TYPE_TIMESTAMP:
            return sizeof(TIMESTAMP_STRUCT);
        case SQL_C_SBIGINT:
        case SQL_C_UBIGINT:
            return sizeof(longlong);
        case SQL_C_NUMERIC:
          return sizeof(SQL_NUMERIC_STRUCT);
        default:                  /* For CHAR, VARCHAR, BLOB, DEFAULT...*/
            return length;
    }
}


/**
  Get bookmark value from SQL_ATTR_FETCH_BOOKMARK_PTR buffer

  @param[in]  fCType      ODBC C type to return data as
  @param[out] rgbValue    Pointer to buffer for returning data
*/
SQLLEN get_bookmark_value(SQLSMALLINT fCType, SQLPOINTER rgbValue)
{
  switch (fCType)
  {
  case SQL_C_CHAR:
  case SQL_C_BINARY:
    return atol((const char*) rgbValue);

  case SQL_C_WCHAR:
    return sqlwchartoul((SQLWCHAR *)rgbValue);

  case SQL_C_TINYINT:
  case SQL_C_STINYINT:
  case SQL_C_UTINYINT:
  case SQL_C_SHORT:
  case SQL_C_SSHORT:
  case SQL_C_USHORT:
  case SQL_C_LONG:
  case SQL_C_SLONG:
  case SQL_C_ULONG:
  case SQL_C_FLOAT:
  case SQL_C_DOUBLE:
  case SQL_C_SBIGINT:
  case SQL_C_UBIGINT:
    return *((SQLLEN *) rgbValue);
  }
   return 0;
}


/*
  @type    : myodbc internal
  @purpose : convert a possible string to a timestamp value
*/

int str_to_ts(SQL_TIMESTAMP_STRUCT *ts, const char *str, int len, int zeroToMin,
              BOOL dont_use_set_locale)
{
    uint year, length;
    char buff[DATETIME_DIGITS + 1], *to;
    const char *end;
    SQL_TIMESTAMP_STRUCT tmp_timestamp;
    SQLUINTEGER fraction;

    if ( !ts )
    {
      ts= (SQL_TIMESTAMP_STRUCT *) &tmp_timestamp;
    }

    /* SQL_NTS is (naturally) negative and is caught as well */
    if (len < 0)
    {
      len = (int)strlen(str);
    }

    /* We don't wan to change value in the out parameter directly
       before we know that string is a good datetime */
    end= get_fractional_part(str, len, dont_use_set_locale, &fraction);

    if (end == NULL || end > str + len)
    {
      end= str + len;
    }

    for ( to= buff; str < end; ++str )
    {
      if ( isdigit(*str) )
      {
        if (to < buff+sizeof(buff)-1)
        {
          *to++= *str;
        }
        else
        {
          /* We have too many numbers in the string and we not gonna tolerate it */
          return SQLTS_BAD_DATE;
        }
      }
    }

    length= (uint) (to-buff);

    if ( length == 6 || length == 12 )  /* YYMMDD or YYMMDDHHMMSS */
    {
      memmove(buff+2, buff, length);

      if ( buff[0] <= '6' )
      {
          buff[0]='2';
          buff[1]='0';
      }
      else
      {
          buff[0]='1';
          buff[1]='9';
      }

      length+= 2;
      to+= 2;
    }

    if (length < DATETIME_DIGITS)
    {
      strfill(buff + length, DATETIME_DIGITS - length, '0');
    }
    else
    {
      *to= 0;
    }

    year= (digit(buff[0])*1000+digit(buff[1])*100+digit(buff[2])*10+digit(buff[3]));

    if (!strncmp(&buff[4], "00", 2) || !strncmp(&buff[6], "00", 2))
    {
      if (!zeroToMin) /* Don't convert invalid */
        return SQLTS_NULL_DATE;

      /* convert invalid to min allowed */
      if (!strncmp(&buff[4], "00", 2))
        buff[5]= '1';
      if (!strncmp(&buff[6], "00", 2))
        buff[7]= '1';
    }

    ts->year=     year;
    ts->month=    digit(buff[4])*10+digit(buff[5]);
    ts->day=      digit(buff[6])*10+digit(buff[7]);
    ts->hour=     digit(buff[8])*10+digit(buff[9]);
    ts->minute=   digit(buff[10])*10+digit(buff[11]);
    ts->second=   digit(buff[12])*10+digit(buff[13]);
    ts->fraction= fraction;

    return 0;
}

/*
  @type    : myodbc internal
  @purpose : convert a possible string to a time value
*/

my_bool str_to_time_st(SQL_TIME_STRUCT *ts, const char *str)
{
    char buff[24],*to, *tokens[3] = {0, 0, 0};
    int num= 0, int_hour=0, int_min= 0, int_sec= 0;
    SQL_TIME_STRUCT tmp_time;

    if ( !ts )
        ts= (SQL_TIME_STRUCT *) &tmp_time;

    /* remember the position of the first numeric string */
    tokens[0]= buff;

    for ( to= buff ; *str && to < buff+sizeof(buff)-1 ; ++str )
    {
        if (isdigit(*str))
            *to++= *str;
        else if (num < 2)
        {
          /*
            terminate the string and remember the beginning of the
            new one only if the time component number is not out of
            range
          */
          *to++= 0;
          tokens[++num]= to;
        }
        else
          /* We can leave the loop now */
          break;
    }
    /* Put the final termination character */
    *to= 0;

    int_hour= tokens[0] ? atoi(tokens[0]) : 0;
    int_min=  tokens[1] ? atoi(tokens[1]) : 0;
    int_sec=  tokens[2] ? atoi(tokens[2]) : 0;

    /* Convert seconds into minutes if necessary */
    if (int_sec > 59)
    {
      int_min+= int_sec / 60;
      int_sec= int_sec % 60;
    }

    /* Convert minutes into hours if necessary */
    if (int_min > 59)
    {
      int_hour+= int_min / 60;
      int_min= int_min % 60;
    }

    ts->hour   = (SQLUSMALLINT)(int_hour < 65536 ? int_hour : 65535);
    ts->minute = (SQLUSMALLINT)int_min;
    ts->second = (SQLUSMALLINT)int_sec;

    return 0;
}

/*
  @type    : myodbc internal
  @purpose : convert a possible string to a data value. if
             zeroToMin is specified, YEAR-00-00 dates will be
             converted to the min valid ODBC date
*/

my_bool str_to_date(SQL_DATE_STRUCT *rgbValue, const char *str,
                    uint length, int zeroToMin)
{
    uint field_length,year_length,digits,i,date[3];
    const char *pos;
    const char *end= str+length;
    for ( ; !isdigit(*str) && str != end ; ++str ) ;
    /*
      Calculate first number of digits.
      If length= 4, 8 or >= 14 then year is of format YYYY
      (YYYY-MM-DD,  YYYYMMDD)
    */
    for ( pos= str; pos != end && isdigit(*pos) ; ++pos ) ;
    digits= (uint) (pos-str);
    year_length= (digits == 4 || digits == 8 || digits >= 14) ? 4 : 2;
    field_length= year_length-1;

    for ( i= 0 ; i < 3 && str != end; ++i )
    {
        uint tmp_value= (uint) (uchar) (*str++ - '0');
        while ( str != end && isdigit(str[0]) && field_length-- )
        {
            tmp_value= tmp_value*10 + (uint) (uchar) (*str - '0');
            ++str;
        }
        date[i]= tmp_value;
        while ( str != end && !isdigit(*str) )
            ++str;
        field_length= 1;   /* Rest fields can only be 2 */
    }
    if (i <= 1 || (i > 1 && !date[1]) || (i > 2 && !date[2]))
    {
      if (!zeroToMin) /* Convert? */
        return 1;

      rgbValue->year=  date[0];
      rgbValue->month= (i > 1 && date[1]) ? date[1] : 1;
      rgbValue->day=   (i > 2 && date[2]) ? date[2] : 1;
    }
    else
    {
      while ( i < 3 )
        date[i++]= 1;

      rgbValue->year=  date[0];
      rgbValue->month= date[1];
      rgbValue->day=   date[2];
    }
    return 0;
}


/*
  @type    : myodbc internal
  @purpose : convert a time string to a (ulong) value.
  At least following formats are recogniced
  HHMMSS HHMM HH HH.MM.SS  {t HH:MM:SS }
  @return  : HHMMSS
*/

ulong str_to_time_as_long(const char *str, uint length)
{
    uint i,date[3];
    const char *end= str+length;

    if ( length == 0 )
        return 0;

    for ( ; !isdigit(*str) && str != end ; ++str ) --length;

    for ( i= 0 ; i < 3 && str != end; ++i )
    {
        uint tmp_value= (uint) (uchar) (*str++ - '0');
        --length;

        while ( str != end && isdigit(str[0]) )
        {
            tmp_value= tmp_value*10 + (uint) (uchar) (*str - '0');
            ++str;
            --length;
        }
        date[i]= tmp_value;
        while ( str != end && !isdigit(*str) )
        {
            ++str;
            --length;
        }
    }
    if ( length && str != end )
        return str_to_time_as_long(str, length);/* timestamp format */

    if ( date[0] > 10000L || i < 3 )    /* Properly handle HHMMSS format */
        return(ulong) date[0];

    return(ulong) date[0] * 10000L + (ulong) (date[1]*100L+date[2]);
}


/*
  @type    : myodbc internal
  @purpose : if there was a long time since last question, check that
  the server is up with mysql_ping (to force a reconnect)
*/

int check_if_server_is_alive( DBC *dbc )
{
    time_t seconds= (time_t) time( (time_t*)0 );
    int result= 0;

    if ( (ulong)(seconds - dbc->last_query_time) >= CHECK_IF_ALIVE )
    {
        if ( mysql_ping( dbc->mysql ) )
        {
            /*  BUG: 14639

                A. The 4.1 doc says when mysql_ping() fails we can get one
                of the following errors from mysql_errno();

                    CR_COMMANDS_OUT_OF_SYNC
                    CR_SERVER_GONE_ERROR
                    CR_UNKNOWN_ERROR

                But if you do a mysql_ping() after bringing down the server
                you get CR_SERVER_LOST.

                PAH - 9.MAR.06
            */

            if (is_connection_lost(mysql_errno( dbc->mysql )))
                result = 1;
        }
    }
    dbc->last_query_time = seconds;

    return result;
}


/*
  @type    : myodbc3 internal
  @purpose : appends quoted string to dynamic string
*/

bool myodbc_append_quoted_name_std(std::string &str, const char *name)
{
  size_t namelen = strlen(name);
  str.reserve(str.length() + namelen + 4);
  str.append(1, '`').append(name).append(1, '`');
  return false;
}


/*
  @type    : myodbc3 internal
  @purpose : reset the db name to current_database()
*/

int reget_current_catalog(DBC *dbc)
{
    dbc->database.clear();

    if (dbc->execute_query("select database()", SQL_NTS, true))
    {
        return 1;
    }
    else
    {
        MYSQL_RES *res;
        MYSQL_ROW row;

        if ( (res= mysql_store_result(dbc->mysql)) &&
             (row= mysql_fetch_row(res)) )
        {
/*            if (cmp_database(row[0], dbc->database)) */
            {
                if ( row[0] )
                {
                    dbc->database = row[0];
                }
            }
        }
        mysql_free_result(res);
    }

    return 0;
}


/*
  @type    : myodbc internal
  @purpose : compare strings without regarding to case
*/

int myodbc_strcasecmp(const char *s, const char *t)
{
  if (!s && !t)
  {
    return 0;
  }

  if (!s || !t)
  {
    return 1;
  }

  while (toupper((uchar) *s) == toupper((uchar) *t++))
    if (!*s++)
      return 0;
  return((int) toupper((uchar) s[0]) - (int) toupper((uchar) t[-1]));
}


/*
  @type    : myodbc internal
  @purpose : compare strings without regarding to case
*/

int myodbc_casecmp(const char *s, const char *t, uint len)
{
  if (!s && !t)
  {
    return 0;
  }

  if ((!s && t) || (s && !t))
    return 1;

  if (!s || !t)
  {
    return (int)len + 1;
  }

  while (len-- != 0 && toupper(*s++) == toupper(*t++))
    ;
  return (int)len + 1;
}


/*
  @type    : myodbc3 internal
  @purpose : logs the queries sent to server
*/

void query_print(FILE *log_file,char *query)
{
    if ( log_file && query )
    {
      /*
        because of bug 68201 we bring the result of time() call
        to 64-bits in any case
      */
      long long time_now= time(NULL);

      fprintf(log_file, "%lld:%s;\n", time_now, query);
    }
}


FILE *init_query_log(void)
{
    FILE *query_log;
#ifdef _WIN32
    char filename[MAX_PATH];
    size_t buffsize;

    getenv_s(&buffsize, filename, sizeof(filename), "TEMP");

    if (buffsize)
    {
      sprintf(filename + buffsize - 1, "\\%s", DRIVER_QUERY_LOGFILE);
    }
    else
    {
      sprintf(filename, "c:\\%s", DRIVER_QUERY_LOGFILE);
    }

    if ( (query_log= fopen(filename, "a+")) )
#else
    if ( (query_log= fopen(DRIVER_QUERY_LOGFILE, "a+")) )
#endif
    {
        fprintf(query_log,"-- Query logging\n");
        fprintf(query_log,"--\n");
        fprintf(query_log,"--  Driver name: %s  Version: %s\n",DRIVER_NAME,
                DRIVER_VERSION);
#ifdef HAVE_LOCALTIME_R
        {
            time_t now= time(NULL);
            struct tm start;
            localtime_r(&now,&start);

            fprintf(query_log,"-- Timestamp: %02d%02d%02d %2d:%02d:%02d\n",
                    start.tm_year % 100,
                    start.tm_mon+1,
                    start.tm_mday,
                    start.tm_hour,
                    start.tm_min,
                    start.tm_sec);
        }
#endif /* HAVE_LOCALTIME_R */
      fprintf(query_log,"\n");
    }
    return query_log;
}


void end_query_log(FILE *query_log)
{
  if ( query_log )
  {
      fclose(query_log);
      query_log= 0;
  }
}


my_bool is_minimum_version(const char *server_version,const char *version)
{
  /*
    Variables have to be initialized if we don't want to get random
    values after sscanf
  */
  uint major1= 0, major2= 0, minor1= 0, minor2= 0, build1= 0, build2= 0;

  sscanf(server_version, "%u.%u.%u", &major1, &minor1, &build1);
  sscanf(version, "%u.%u.%u", &major2, &minor2, &build2);

  if ( major1 > major2 ||
      major1 == major2 && (minor1 > minor2 ||
                          minor1 ==  minor2 && build1 >= build2))
  {
    return TRUE;
  }
  return FALSE;
}


/**
 Escapes a string that may contain wildcard characters (%, _) and other
 problematic characters (", ', \n, etc). Like mysql_real_escape_string() but
 also including % and _. Can be used with an identified by passing escape_id.

 @param[in]   mysql         Pointer to MYSQL structure
 @param[out]  to            Buffer for escaped string
 @param[in]   to_length     Length of destination buffer, or 0 for "big enough"
 @param[in]   from          The string to escape
 @param[in]   length        The length of the string to escape
 @param[in]   escape_id     Escaping an identified that will be quoted

*/
ulong myodbc_escape_string(STMT *stmt,
                           char *to, ulong to_length,
                           const char *from, ulong length, int escape_id)
{
  const char *to_start= to;
  const char *end, *to_end=to_start + (to_length ? to_length-1 : 2*length);
  my_bool overflow= FALSE;
  /*get_charset_by_csname(charset,
                        MYF(MY_CS_PRIMARY),
                        MYF(0));*/
  CHARSET_INFO *charset_info= stmt->dbc->cxn_charset_info;
  my_bool use_mb_flag= use_mb(charset_info);
  for (end= from + length; from < end; ++from)
  {
    char escape= 0;
    int tmp_length;
    if (use_mb_flag && (tmp_length= my_ismbchar(charset_info, from, end)))
    {
      if (to + tmp_length > to_end)
      {
        overflow= TRUE;
        break;
      }
      while (tmp_length--)
        *to++= *from++;
      --from;
      continue;
    }
    /*
     If the next character appears to begin a multi-byte character, we
     escape that first byte of that apparent multi-byte character. (The
     character just looks like a multi-byte character -- if it were actually
     a multi-byte character, it would have been passed through in the test
     above.)

     Without this check, we can create a problem by converting an invalid
     multi-byte character into a valid one. For example, 0xbf27 is not
     a valid GBK character, but 0xbf5c is. (0x27 = ', 0x5c = \)
    */
    if (use_mb_flag && (tmp_length= my_mbcharlen(charset_info, *from)) > 1)
      escape= *from;
    else
    switch (*from) {
    case 0:         /* Must be escaped for 'mysql' */
      escape= '0';
      break;
    case '\n':      /* Must be escaped for logs */
      escape= 'n';
      break;
    case '\r':
      escape= 'r';
      break;
    case '\\':
    case '\'':
    case '"':       /* Better safe than sorry */
    case '_':
    case '%':
      escape= *from;
      break;
    case '\032':    /* This gives problems on Win32 */
      escape= 'Z';
      break;
    }
    /* if escaping an id, only handle back-tick */
    if (escape_id)
    {
      if (*from == '`')
        escape= *from;
      else
        escape= 0;
    }
    if (escape)
    {
      if (to + 2 > to_end)
      {
        overflow= TRUE;
        break;
      }
      *to++= escape != '`' ? '\\' : '`';
      *to++= escape;
    }
    else
    {
      if (to + 1 > to_end)
      {
        overflow= TRUE;
        break;
      }
      *to++= *from;
    }
  }
  *to= 0;
  return overflow ? (ulong)~0 : (ulong) (to - to_start);
}


/**
  Scale an int[] representing SQL_C_NUMERIC

  @param[in] ary   Array in little endian form
  @param[in] s     Scale
*/
void sqlnum_scale(unsigned *ary, int s)
{
  /* multiply out all pieces */
  while (s--)
  {
    ary[0] *= 10;
    ary[1] *= 10;
    ary[2] *= 10;
    ary[3] *= 10;
    ary[4] *= 10;
    ary[5] *= 10;
    ary[6] *= 10;
    ary[7] *= 10;
  }
}


/**
  Unscale an int[] representing SQL_C_NUMERIC. This
  leaves the last element (0) with the value of the
  last digit.

  @param[in] ary   Array in little endian form
*/
static void sqlnum_unscale_le(unsigned *ary)
{
  int i;
  for (i= 7; i > 0; --i)
  {
    ary[i - 1] += (ary[i] % 10) << 16;
    ary[i] /= 10;
  }
}


/**
  Unscale an int[] representing SQL_C_NUMERIC. This
  leaves the last element (7) with the value of the
  last digit.

  @param[in] ary   Array in big endian form
*/
static void sqlnum_unscale_be(unsigned *ary, int start)
{
  int i;
  for (i= start; i < 7; ++i)
  {
    ary[i + 1] += (ary[i] % 10) << 16;
    ary[i] /= 10;
  }
}


/**
  Perform the carry to get all elements below 2^16.
  Should be called right after sqlnum_scale().

  @param[in] ary   Array in little endian form
*/
static void sqlnum_carry(unsigned *ary)
{
  int i;
  /* carry over rest of structure */
  for (i= 0; i < 7; ++i)
  {
    ary[i+1] += ary[i] >> 16;
    ary[i] &= 0xffff;
  }
}


/**
  Retrieve a SQL_NUMERIC_STRUCT from a string. The requested scale
  and precesion are first read from sqlnum, and then updated values
  are written back at the end.

  @param[in] numstr       String representation of number to convert
  @param[in] sqlnum       Destination struct
  @param[in] overflow_ptr Whether or not whole-number overflow occurred.
                          This indicates failure, and the result of sqlnum
                          is undefined.
*/

void sqlnum_from_str(const char *numstr, SQL_NUMERIC_STRUCT *sqlnum,
                     int *overflow_ptr)
{
  /*
     We use 16 bits of each integer to convert the
     current segment of the number leaving extra bits
     to multiply/carry
  */
  unsigned build_up[8], tmp_prec_calc[8];
  /* current segment as integer */
  unsigned int curnum;
  /* current segment digits copied for strtoul() */
  char curdigs[5];
  /* number of digits in current segment */
  int usedig;
  int i;
  int len;
  char *decpt= strchr((char*)numstr, '.');
  int overflow= 0;
  SQLSCHAR reqscale= sqlnum->scale;
  SQLCHAR reqprec= sqlnum->precision;

  memset(&sqlnum->val, 0, sizeof(sqlnum->val));
  memset(build_up, 0, sizeof(build_up));

  /* handle sign */
  if (!(sqlnum->sign= !(*numstr == '-')))
    ++numstr;

  len= (int) strlen(numstr);
  sqlnum->precision= len;
  sqlnum->scale= 0;

  /* process digits in groups of <=4 */
  for (i= 0; i < len; i += usedig)
  {
    if (i + 4 < len)
      usedig= 4;
    else
      usedig= len - i;
    /*
       if we have the decimal point, ignore it by setting it to the
       last char (will be ignored by strtoul)
    */
    if (decpt && decpt >= numstr + i && decpt < numstr + i + usedig)
    {
      usedig = (int) (decpt - (numstr + i) + 1);
      sqlnum->scale= len - (i + usedig);
      --sqlnum->precision;
      decpt= NULL;
    }

    if (overflow)
      goto end;

    /* grab just this piece, and convert to int */
    memcpy(curdigs, numstr + i, usedig);
    curdigs[usedig]= 0;
    curnum= strtoul(curdigs, NULL, 10);
    if (curdigs[usedig - 1] == '.')
      sqlnum_scale(build_up, usedig - 1);
    else
      sqlnum_scale(build_up, usedig);
    /* add the current number */
    build_up[0] += curnum;
    sqlnum_carry(build_up);
    if (build_up[7] & ~0xffff)
      overflow= 1;
  }

  /* scale up to SQL_DESC_SCALE */
  if (reqscale > 0 && reqscale > sqlnum->scale)
  {
    while (reqscale > sqlnum->scale)
    {
      sqlnum_scale(build_up, 1);
      sqlnum_carry(build_up);
      ++sqlnum->scale;
    }
  }
  /* scale back, truncating decimals */
  else if (reqscale < sqlnum->scale)
  {
    while (reqscale < sqlnum->scale && sqlnum->scale > 0)
    {
      sqlnum_unscale_le(build_up);

      // Value 2 of overflow indicates truncation, not critical
      if (build_up[0] % 10)
        overflow = 2;

      build_up[0] /= 10;
      --sqlnum->precision;
      --sqlnum->scale;
    }
  }

  /* scale back whole numbers while there's no significant digits */
  if (reqscale < 0)
  {
    memcpy(tmp_prec_calc, build_up, sizeof(build_up));
    while (reqscale < sqlnum->scale)
    {
      sqlnum_unscale_le(tmp_prec_calc);
      if (tmp_prec_calc[0] % 10)
      {
        overflow= 1;
        goto end;
      }
      sqlnum_unscale_le(build_up);
      tmp_prec_calc[0] /= 10;
      build_up[0] /= 10;
      --sqlnum->precision;
      --sqlnum->scale;
    }
  }

  /* calculate minimum precision */
  memcpy(tmp_prec_calc, build_up, sizeof(build_up));

  {
    SQLCHAR temp_precision = sqlnum->precision;

    do
    {
      sqlnum_unscale_le(tmp_prec_calc);
      i= tmp_prec_calc[0] % 10;
      tmp_prec_calc[0] /= 10;
      if (i == 0)
        --temp_precision;
    } while (i == 0 && temp_precision > 0);

    /* detect precision overflow */
    if (temp_precision > reqprec)
      overflow= 1;
  }

  /* compress results into SQL_NUMERIC_STRUCT.val */
  for (i= 0; i < 8; ++i)
  {
    int elem= 2 * i;
    sqlnum->val[elem]= build_up[i] & 0xff;
    sqlnum->val[elem+1]= (build_up[i] >> 8) & 0xff;
  }

end:
  if (overflow_ptr)
    *overflow_ptr= overflow;
}


/**
  Convert a SQL_NUMERIC_STRUCT to a string. Only val and sign are
  read from the struct. precision and scale will be updated on the
  struct with the final values used in the conversion.

  @param[in] sqlnum       Source struct
  @param[in] numstr       Buffer to convert into string. Note that you
                          MUST use numbegin to read the result string.
                          This should point to the LAST byte available.
                          (We fill in digits backwards.)
  @param[in,out] numbegin String pointer that will be set to the start of
                          the result string.
  @param[in] reqprec      Requested precision
  @param[in] reqscale     Requested scale
  @param[in] truncptr     Pointer to set the truncation type encountered.
                          If SQLNUM_TRUNC_WHOLE, this indicates a failure
                          and the contents of numstr are undefined and
                          numbegin will not be written to.
*/
void sqlnum_to_str(SQL_NUMERIC_STRUCT *sqlnum, SQLCHAR *numstr,
                   SQLCHAR **numbegin, SQLCHAR reqprec, SQLSCHAR reqscale,
                   int *truncptr)
{
  unsigned expanded[8];
  int i, j;
  int max_space= 0;
  int calcprec= 0;
  int trunc= 0; /* truncation indicator */

  *numstr--= 0;

  /*
     it's expected to have enough space
     (~at least min(39, max(prec, scale+2)) + 3)
  */

  /*
     expand the packed sqlnum->val so we have space to divide through
     expansion happens into an array in big-endian form
  */
  for (i= 0; i < 8; ++i)
    expanded[7 - i]= (sqlnum->val[(2 * i) + 1] << 8) | sqlnum->val[2 * i];

  /* max digits = 39 = log_10(2^128)+1 */
  for (j= 0; j < 39; ++j)
  {
    /* skip empty prefix */
    while (!expanded[max_space])
      ++max_space;
    /* if only the last piece has a value, it's the end */
    if (max_space >= 7)
    {
      i= 7;
      if (!expanded[7])
      {
        /* special case for zero, we'll end immediately */
        if (!*(numstr + 1))
        {
          *numstr--= '0';
          calcprec= 1;
        }
        break;
      }
    }
    else
    {
      /* extract the next digit */
      sqlnum_unscale_be(expanded, max_space);
    }
    *numstr--= '0' + (expanded[7] % 10);
    expanded[7] /= 10;
    ++calcprec;
    if (j == reqscale - 1)
      *numstr--= '.';
  }

  sqlnum->scale= reqscale;

  /* add <- dec pt */
  if (calcprec < reqscale)
  {
    while (calcprec < reqscale)
    {
      *numstr--= '0';
      --reqscale;
    }
    *numstr--= '.';
    *numstr--= '0';
  }

  /* handle fractional truncation */
  if (calcprec > reqprec && reqscale > 0)
  {
    SQLCHAR *end= numstr + strlen((char *)numstr) - 1;
    while (calcprec > reqprec && reqscale)
    {
      *end--= 0;
      --calcprec;
      --reqscale;
    }
    if (calcprec > reqprec && reqscale == 0)
    {
      trunc= SQLNUM_TRUNC_WHOLE;
      goto end;
    }
    if (*end == '.')
    {
      *end--= '\0';
    }
    else
    {
      /* move the dec pt-- ??? */
      /*
      char c2, c= numstr[calcprec - reqscale];
      numstr[calcprec - reqscale]= '.';
      while (reqscale)
      {
        c2= numstr[calcprec + 1 - reqscale];
        numstr[calcprec + 1 - reqscale]= c;
        c= c2;
        --reqscale;
      }
      */
    }
    trunc= SQLNUM_TRUNC_FRAC;
  }

  /* add zeros for negative scale */
  if (reqscale < 0)
  {
    int i;
    reqscale *= -1;
    for (i= 1; i <= calcprec; ++i)
      *(numstr + i - reqscale)= *(numstr + i);
    numstr -= reqscale;
    memset(numstr + calcprec + 1, '0', reqscale);
  }

  sqlnum->precision= calcprec;

  /* finish up, handle auxilary fix-ups */
  if (!sqlnum->sign)
  {
    *numstr--= '-';
  }
  ++numstr;
  *numbegin= numstr;

end:
  if (truncptr)
    *truncptr= trunc;
}


/**
  Adjust a pointer based on bind offset and bind type.

  @param[in] ptr The base pointer
  @param[in] bind_offset_ptr The bind offset ptr (can be NULL).
             (SQL_ATTR_PARAM_BIND_OFFSET_PTR, SQL_ATTR_ROW_BIND_OFFSET_PTR,
              SQL_DESC_BIND_OFFSET_PTR)
  @param[in] bind_type The bind type. Should be SQL_BIND_BY_COLUMN (0) or
             the length of a row for row-wise binding. (SQL_ATTR_PARAM_BIND_TYPE,
             SQL_ATTR_ROW_BIND_TYPE, SQL_DESC_BIND_TYPE)
  @param[in] default_size The column size if bind type = SQL_BIND_BY_COLUMN.
  @param[in] row The row number.

  @return The base pointer with the offset added. If the base pointer is
          NULL, NULL is returned.
 */
void *ptr_offset_adjust(void *ptr, SQLULEN *bind_offset_ptr,
                        SQLINTEGER bind_type, SQLINTEGER default_size,
                        SQLULEN row)
{
  size_t offset= 0;
  if (bind_offset_ptr)
    offset= (size_t) *bind_offset_ptr;

  if (bind_type == SQL_BIND_BY_COLUMN)
    offset+= default_size * row;
  else
    offset+= bind_type * row;

  return ptr ? ((SQLCHAR *) ptr) + offset : NULL;
}


/**
  Sets the value of @@sql_select_limit

  @param[in]  dbc         dbc handler
  @param[in]  new_value   Value to set @@sql_select_limit.
  @param[in]  req_lock    The flag if dbc->lock thread lock should be used
                          when executing a query

  Returns new_value if operation was successful, -1 otherwise
 */
SQLRETURN set_sql_select_limit(DBC *dbc, SQLULEN lim_value, my_bool req_lock)
{
  char query[44];
  SQLRETURN rc;

  /* Both 0 and max(SQLULEN) value mean no limit and sql_select_limit to DEFAULT */
  if (lim_value == dbc->sql_select_limit
   || lim_value == sql_select_unlimited && dbc->sql_select_limit == 0)
    return SQL_SUCCESS;

  if (lim_value > 0 && lim_value < sql_select_unlimited)
    myodbc_snprintf(query, sizeof(query), "set @@sql_select_limit=%lu",
                    (unsigned long)lim_value);
  else
  {
    strcpy(query, "set @@sql_select_limit=DEFAULT");
    lim_value= 0;
  }

  if (SQL_SUCCEEDED(rc = dbc->execute_query(query, SQL_NTS, req_lock)))
  {
    dbc->sql_select_limit= lim_value;
  }

  return rc;
}


/**
  Detects the parameter type.

  @param[in]  proc        procedure parameter string
  @param[in]  len         param string length
  @param[out] ptype       pointer where to write the param type

  Returns position in the param string after parameter type
*/
char *proc_get_param_type(char *proc, int len, SQLSMALLINT *ptype)
{
  while (isspace(*proc) && (len--))
    ++proc;

  if (len >= 6 && !myodbc_casecmp(proc, "INOUT ", 6))
  {
    *ptype= (SQLSMALLINT) SQL_PARAM_INPUT_OUTPUT;
    return proc + 6;
  }

  if (len >= 4 && !myodbc_casecmp(proc, "OUT ", 4))
  {
    *ptype= (SQLSMALLINT) SQL_PARAM_OUTPUT;
    return proc + 4;
  }

  if (len >= 3 && !myodbc_casecmp(proc, "IN ", 3))
  {
    *ptype= (SQLSMALLINT) SQL_PARAM_INPUT;
    return proc + 3;
  }

  *ptype= (SQLSMALLINT)SQL_PARAM_INPUT;
  return proc;
}


/**
  Detects the parameter name

  @param[in]  proc        procedure parameter string
  @param[in]  len         param string length
  @param[out] cname       pointer where to write the param name

  Returns position in the param string after parameter name
*/
char* proc_get_param_name(char *proc, int len, char *cname)
{
  char quote_symbol= '\0';

  while (isspace(*proc) && (len--))
    ++proc;

  /* can be '"' if ANSI_QUOTE is enabled */
  if (*proc == '`' || *proc == '"')
  {
    quote_symbol= *proc;
    ++proc;
  }

  while ((len--) && (quote_symbol != '\0' ? *proc != quote_symbol : !isspace(*proc)))
    *(cname++)= *(proc++);

  return quote_symbol ? proc + 1 : proc;
}


/**
  Detects the parameter data type

  @param[in]  proc        procedure parameter string
  @param[in]  len         param string length
  @param[out] cname       pointer where to write the param type name

  Returns position in the param string after parameter type name
*/
char* proc_get_param_dbtype(char *proc, int len, char *ptype)
{
  char *trim_str, *start_pos= ptype;

  while (isspace(*proc) && (len--))
    ++proc;

  while (*proc && (len--) )
    *(ptype++)= *(proc++);

  /* remove the character set definition */
  if (trim_str= strstr( myodbc_strlwr(start_pos, (size_t)-1),
                        " charset "))
  {
    ptype= trim_str;
    (*ptype)= 0;
  }

  /* trim spaces from the end */
  ptype-=1;
  while (isspace(*(ptype)))
  {
    *ptype= 0;
    --ptype;
  }

  return proc;
}

SQLTypeMap SQL_TYPE_MAP_values[TYPE_MAP_SIZE]=
{
  /* SQL_BIT= -7 */
  {(SQLCHAR*)"bit", 3, SQL_BIT, MYSQL_TYPE_BIT, 1, 1},
  {(SQLCHAR*)"bool", 4, SQL_BIT, MYSQL_TYPE_BIT, 1, 1},

  /* SQL_TINY= -6 */
  {(SQLCHAR*)"tinyint", 7, SQL_TINYINT, MYSQL_TYPE_TINY, 1, 1},

  /* SQL_BIGINT= -5 */
  {(SQLCHAR*)"bigint", 6, SQL_BIGINT, MYSQL_TYPE_LONGLONG, 20, 1},

  /* SQL_LONGVARBINARY= -4 */
  {(SQLCHAR*)"long varbinary", 14, SQL_LONGVARBINARY, MYSQL_TYPE_MEDIUM_BLOB, 16777215, 1},
  {(SQLCHAR*)"blob", 4, SQL_LONGVARBINARY, MYSQL_TYPE_BLOB, 65535, 1},
  {(SQLCHAR*)"longblob", 8, SQL_LONGVARBINARY, MYSQL_TYPE_LONG_BLOB, 4294967295UL, 1},
  {(SQLCHAR*)"tinyblob", 8, SQL_LONGVARBINARY, MYSQL_TYPE_TINY_BLOB, 255, 1},
  {(SQLCHAR*)"mediumblob", 10, SQL_LONGVARBINARY, MYSQL_TYPE_MEDIUM_BLOB, 16777215,1 },

  /* SQL_VARBINARY= -3 */
  {(SQLCHAR*)"varbinary", 9, SQL_VARBINARY, MYSQL_TYPE_VAR_STRING, 0, 1},

  /* SQL_BINARY= -2 */
  {(SQLCHAR*)"binary", 6, SQL_BINARY, MYSQL_TYPE_STRING, 0, 1},

  /* SQL_LONGVARCHAR= -1 */
  {(SQLCHAR*)"long varchar", 12, SQL_LONGVARCHAR, MYSQL_TYPE_MEDIUM_BLOB, 16777215, 0},
  {(SQLCHAR*)"text", 4, SQL_LONGVARCHAR, MYSQL_TYPE_BLOB, 65535, 0},
  {(SQLCHAR*)"mediumtext", 10, SQL_LONGVARCHAR, MYSQL_TYPE_MEDIUM_BLOB, 16777215, 0},
  {(SQLCHAR*)"longtext", 8, SQL_LONGVARCHAR, MYSQL_TYPE_LONG_BLOB, 4294967295UL, 0},
  {(SQLCHAR*)"tinytext", 8, SQL_LONGVARCHAR, MYSQL_TYPE_TINY_BLOB, 255, 0},

  /* SQL_CHAR= 1 */
  {(SQLCHAR*)"char", 4, SQL_CHAR, MYSQL_TYPE_STRING, 0, 0},
  {(SQLCHAR*)"enum", 4, SQL_CHAR, MYSQL_TYPE_STRING, 0, 0},
  {(SQLCHAR*)"set", 3, SQL_CHAR, MYSQL_TYPE_STRING, 0, 0},

  /* SQL_NUMERIC= 2 */
  {(SQLCHAR*)"numeric", 7, SQL_NUMERIC, MYSQL_TYPE_DECIMAL, 0, 1},

  /* SQL_DECIMAL= 3 */
  {(SQLCHAR*)"decimal", 7, SQL_DECIMAL, MYSQL_TYPE_DECIMAL, 0, 1},

  /* SQL_INTEGER= 4 */
  {(SQLCHAR*)"int", 3, SQL_INTEGER, MYSQL_TYPE_LONG, 10, 1},
  {(SQLCHAR*)"mediumint", 9, SQL_INTEGER, MYSQL_TYPE_INT24, 8, 1},

  /* SQL_SMALLINT= 5 */
  {(SQLCHAR*)"smallint", 8, SQL_SMALLINT, MYSQL_TYPE_SHORT, 5, 1},

  /* SQL_REAL= 7 */
  {(SQLCHAR*)"float", 5, SQL_REAL, MYSQL_TYPE_FLOAT, 7, 1},

  /* SQL_DOUBLE= 8 */
  {(SQLCHAR*)"double", 6, SQL_DOUBLE, MYSQL_TYPE_DOUBLE, 15, 1},

  /* SQL_DATETIME= 9 */
  {(SQLCHAR*)"datetime", 8, SQL_TYPE_TIMESTAMP, MYSQL_TYPE_DATETIME, 19, 1},

  /* SQL_VARCHAR= 12 */
  {(SQLCHAR*)"varchar", 7, SQL_VARCHAR, MYSQL_TYPE_VARCHAR, 0, 0},

  /* SQL_TYPE_DATE= 91 */
  {(SQLCHAR*)"date", 4, SQL_TYPE_DATE, MYSQL_TYPE_DATE, 10, 1},

  /* YEAR - SQL_SMALLINT */
  {(SQLCHAR*)"year", 4, SQL_SMALLINT, MYSQL_TYPE_YEAR, 2, 1},

  /* SQL_TYPE_TIMESTAMP= 93 */
  {(SQLCHAR*)"timestamp", 9, SQL_TYPE_TIMESTAMP, MYSQL_TYPE_TIMESTAMP, 19, 1},

  /* SQL_TYPE_TIME= 92 */
  {(SQLCHAR*)"time", 4, SQL_TYPE_TIME, MYSQL_TYPE_TIME, 8, 1}

};


enum enum_field_types map_sql2mysql_type(SQLSMALLINT sql_type)
{
  int i;
  for (i= 0; i < TYPE_MAP_SIZE; ++i)
  {
    if (SQL_TYPE_MAP_values[i].sql_type == sql_type)
    {
      return (enum_field_types)SQL_TYPE_MAP_values[i].mysql_type;
    }
  }

  return MYSQL_TYPE_BLOB;
}

/**
  Gets the parameter index in the type map array

  @param[in]  ptype       procedure parameter type name
  @param[in]  len         param string length

  Returns position in the param string after parameter type name
*/
int proc_get_param_sql_type_index(const char *ptype, int len)
{
  int i;
  for (i= 0; i < TYPE_MAP_SIZE; ++i)
  {
    if (len >= SQL_TYPE_MAP_values[i].name_length &&
        (!myodbc_casecmp(ptype, (const char*)SQL_TYPE_MAP_values[i].type_name,
         SQL_TYPE_MAP_values[i].name_length)))
      return i;
  }

  return 16; /* "char" */
}


/**
  Gets the parameter info array from the map using index

  @param[in]  index       index in the param info array

  Pointer to the structure that contains parameter info
*/
SQLTypeMap *proc_get_param_map_by_index(int index)
{
  return &SQL_TYPE_MAP_values[index];
}


/**
  Parses parameter size and decimal digits

  @param[in]  ptype       parameter type name
  @param[in]  len         type string length
  @param[out] dec         pointer where to write decimal digits

  Returns parsed size
*/
SQLUINTEGER proc_parse_sizes(SQLCHAR *ptype, int len, SQLSMALLINT *dec)
{
  int parsed= 0;
  SQLUINTEGER param_size= 0;

  if (ptype == NULL)
  {
    /* That shouldn't happen though */
    return 0;
  }

  while ((len > 0) && (*ptype!= ')') && (parsed < 2))
  {
    int n_index= 0;
    char number_to_parse[16]= "\0";

    /* skip all non-digit characters */
    while (!isdigit(*ptype) && (len-- >= 0) && (*ptype!= ')'))
      ++ptype;

    /* add digit characters to the buffer for parsing */
    while (isdigit(*ptype) && (len-- >= 0))
    {
      number_to_parse[n_index++]= *ptype;
      ++ptype;
    }

    /* 1st number is column size, 2nd is decimal digits */
    if (!parsed)
      param_size= atoi(number_to_parse);
    else
      *dec= (SQLSMALLINT)atoi(number_to_parse);

    ++parsed;
  }

  return param_size;
}


/**
  Determines the length of ENUM/SET

  @param[in]  ptype       parameter type name
  @param[in]  len         type string length
  @param[in]  is_enum     flag to treat string as ENUM
                          instead of SET

  Returns size of ENUM/SET
*/
SQLUINTEGER proc_parse_enum_set(SQLCHAR *ptype, int len, BOOL is_enum)
{
  SQLUINTEGER total_len= 0, elem_num= 0, max_len= 0, cur_len= 0;
  char quote_symbol= '\0';

  /* theoretically ')' can be inside quotes as part of enum value */
  while ((len > 0) && (quote_symbol != '\0' || *ptype!= ')'))
  {
    if (*ptype == quote_symbol)
    {
      quote_symbol= '\0';
      max_len= myodbc_max(cur_len, max_len);
    }
    else if (*ptype == '\'' || *ptype == '"')
    {
      quote_symbol= *ptype;
      cur_len= 0;
      ++elem_num;
    }
    else if (quote_symbol)
    {
      ++cur_len;
      ++total_len;
    }

    ++ptype;
    --len;
  }

  return is_enum ? max_len : total_len + elem_num - 1;
}


/**
  Returns parameter size and decimal digits

  @param[in]  ptype          parameter type name
  @param[in]  len            type string length
  @param[in]  sql_type_index index in the param info array
  @param[out] dec            pointer where to write decimal digits

  Returns parameter size
*/
SQLUINTEGER proc_get_param_size(SQLCHAR *ptype, int len, int sql_type_index, SQLSMALLINT *dec)
{
  SQLUINTEGER param_size= SQL_TYPE_MAP_values[sql_type_index].type_length;
  SQLCHAR *start_pos= (SQLCHAR*)strchr((const char*)ptype, '(');
  SQLCHAR *end_pos= (SQLCHAR*)strrchr((const char*)ptype, ')');

  /* no decimal digits by default */
  *dec= SQL_NO_TOTAL;

  switch (SQL_TYPE_MAP_values[sql_type_index].mysql_type)
  {
    /* these type sizes need to be parsed */
    case MYSQL_TYPE_DECIMAL:
      param_size = proc_parse_sizes(start_pos, (int)(end_pos - start_pos), dec);
      if(!param_size)
        param_size= 10; /* by default */
      break;

    case MYSQL_TYPE_YEAR:
      *dec= 0;
      param_size= proc_parse_sizes(start_pos, (int)(end_pos - start_pos), dec);
      if(!param_size)
        param_size= 4; /* by default */
      break;

    case MYSQL_TYPE_VARCHAR:
    case MYSQL_TYPE_VAR_STRING:
    case MYSQL_TYPE_STRING:
      if (!myodbc_strcasecmp((const char*)(SQL_TYPE_MAP_values[sql_type_index].type_name), "set"))
      {
        param_size = proc_parse_enum_set(start_pos, (int)(end_pos - start_pos), FALSE);
      }
      else if (!myodbc_strcasecmp((const char*)(SQL_TYPE_MAP_values[sql_type_index].type_name), "enum"))
      {
        param_size = proc_parse_enum_set(start_pos, (int)(end_pos - start_pos), TRUE);
      }
      else /* just normal character type */
      {
        param_size = proc_parse_sizes(start_pos, (int)(end_pos - start_pos), dec);
        if (param_size == 0 &&
            SQL_TYPE_MAP_values[sql_type_index].sql_type == SQL_BINARY)
           param_size= 1;
      }

      break;
    case MYSQL_TYPE_BIT:
      param_size = proc_parse_sizes(start_pos, (int)(end_pos - start_pos), dec);

      /* fall through*/
    case MYSQL_TYPE_DATETIME:

    case MYSQL_TYPE_TINY:
    case MYSQL_TYPE_SHORT:
    case MYSQL_TYPE_INT24:
    case MYSQL_TYPE_LONG:
    case MYSQL_TYPE_LONGLONG:
      *dec= 0;
      break;

  }

  return param_size;
}


/**
Gets parameter columns size

@param[in]  stmt           statement
@param[in]  sql_type_index index in the param info array
@param[in]  col_size       parameter size
@param[in]  decimal_digits write decimal digits
@param[in]  flags          field flags

Returns parameter octet length
*/
SQLLEN proc_get_param_col_len(STMT *stmt, int sql_type_index, SQLULEN col_size,
                              SQLSMALLINT decimal_digits, unsigned int flags, char * str_buff)
{
  MYSQL_FIELD temp_fld;

  temp_fld.length= (unsigned long)col_size +
    (SQL_TYPE_MAP_values[sql_type_index].mysql_type == MYSQL_TYPE_DECIMAL ?
    1 + (flags & UNSIGNED_FLAG ? 0 : 1) : 0); /* add 1for sign, if needed, and 1 for decimal point */

  temp_fld.max_length = (unsigned long)col_size;
  temp_fld.decimals= decimal_digits;
  temp_fld.flags= flags;
  temp_fld.charsetnr= stmt->dbc->ansi_charset_info->number;
  temp_fld.type= (enum_field_types)(SQL_TYPE_MAP_values[sql_type_index].mysql_type);

  if (str_buff != NULL)
  {
    return fill_column_size_buff(str_buff, stmt, &temp_fld);
  }
  else
  {
    return get_column_size( stmt, &temp_fld);
  }
}


/**
  Gets parameter octet length

  @param[in]  stmt           statement
  @param[in]  sql_type_index index in the param info array
  @param[in]  col_size       parameter size
  @param[in]  decimal_digits write decimal digits
  @param[in]  flags          field flags

  Returns parameter octet length
*/
SQLLEN proc_get_param_octet_len(STMT *stmt, int sql_type_index, SQLULEN col_size,
                                SQLSMALLINT decimal_digits, unsigned int flags, char * str_buff)
{
  MYSQL_FIELD temp_fld;

  temp_fld.length= (unsigned long)col_size +
    (SQL_TYPE_MAP_values[sql_type_index].mysql_type == MYSQL_TYPE_DECIMAL ?
    1 + (flags & UNSIGNED_FLAG ? 0 : 1) : 0); /* add 1for sign, if needed, and 1 for decimal point */

  temp_fld.max_length = (unsigned long)col_size;
  temp_fld.decimals= decimal_digits;
  temp_fld.flags= flags;
  temp_fld.charsetnr= stmt->dbc->ansi_charset_info->number;
  temp_fld.type= (enum_field_types)(SQL_TYPE_MAP_values[sql_type_index].mysql_type);

  if (str_buff != NULL)
  {
    return fill_transfer_oct_len_buff(str_buff, stmt, &temp_fld);
  }
  else
  {
    return get_transfer_octet_length(stmt, &temp_fld);
  }
}


/**
  tokenize the string by putting \0 bytes to separate params

  @param[in]  str        parameters string
  @param[out] params_num number of detected parameters

  Returns pointer to the first param
*/
char *proc_param_tokenize(char *str, int *params_num)
{
  BOOL bracket_open= 0;
  char *str_begin= str, quote_symbol='\0';
  int len = (int)strlen(str);

  *params_num= 0;

  /* if no params at all */
  while (len > 0 && isspace(*str))
  {
    ++str;
    --len;
  }

  if (len && *str && *str != ')')
    *params_num= 1;

  while (len > 0)
  {
    /* Making sure that a bracket is not inside quotes. that's possible for SET
       or ENUM values */
    if (quote_symbol == '\0')
    {
      if (!bracket_open && *str == ',')
      {
        *str= '\0';
        ++(*params_num);
      }
      else if (*str == '(')
      {
        bracket_open= 1;
      }
      else if (*str == ')')
      {
        bracket_open= 0;
      }
      else if (*str == '"' || *str == '\'')
      {
        quote_symbol= *str;
      }
    }
    else if( *str == quote_symbol)
    {
      quote_symbol= '\0';
    }

    ++str;
    --len;
  }
  return str_begin;
}


/**
  goes to the next token in \0-terminated string sequence

  @param[in]  str        parameters string
  @param[in]  str_end    end of the sequence

  Returns pointer to the next token in sequence
*/
char *proc_param_next_token(char *str, char *str_end)
{
  size_t end_token = strlen(str);

  /* return the next string after \0 byte */
  if (str + end_token + 1 < str_end)
    return (char*)(str + end_token + 1);

  return 0;
}


/**
   Sets row_count in STMT's MYSQL_RES and affected rows property MYSQL object. Primary use is to set
   number of affected rows for constructed resulsets. Setting mysql.affected_rows
   is required for SQLRowCount to return correct data for such resultsets.
*/
void set_row_count(STMT *stmt, my_ulonglong rows)
{
  if (stmt != NULL && stmt->result != NULL)
  {
    stmt->result->row_count= rows;
    stmt->dbc->mysql->affected_rows= rows;
  }
}

/**
   Gets fractional time of a second from datetime or time string.

   @param[in]  value                (date)time string
   @param[in]  len                  length of value buffer
   @param[in]  dont_use_set_locale  use dot as decimal part separator
   @param[out] fraction             buffer where to put fractional part
                                    in nanoseconds

   Returns pointer to decimal point in the string
*/
const char *
get_fractional_part(const char * str, int len, BOOL dont_use_set_locale,
                    SQLUINTEGER * fraction)
{
  const char *decptr= NULL, *end;
  size_t decpoint_len= 1;

  if (len < 0)
  {
    len = (int)strlen(str);
  }

  end= str + len;

  if (dont_use_set_locale)
  {
    decptr= strchr(str, '.');
  }
  else
  {
    decpoint_len = decimal_point.length();
    const char *locale_decimal_point = decimal_point.c_str();
    while (*str && str < end)
    {
      if (str[0] == locale_decimal_point[0] &&
          is_prefix(str, locale_decimal_point))
      {
        decptr= str;
        break;
      }

      ++str;
    }
  }

  /* If decimal point is the last character - we don't have fractional part */
  if (decptr && decptr < end - decpoint_len)
  {
    char buff[10], *ptr;

    strfill(buff, sizeof(buff)-1, '0');
    str= decptr + decpoint_len;

    for (ptr= buff; str < end && ptr < buff + sizeof(buff); ++ptr)
    {
      /* there actually should not be anything that is not a digit... */
      if (isdigit(*str))
      {
        *ptr= *str++;
      }
    }

    buff[9]= 0;
    *fraction= atoi(buff);
  }
  else
  {
    *fraction= 0;
    decptr= NULL;
  }

  return decptr;
}

/* Convert MySQL timestamp to full ANSI timestamp format. */
char * complete_timestamp(const char * value, ulong length, char buff[21])
{
  char *pos;
  uint i;

  if (length == 6 || length == 10 || length == 12)
  {
    /* For two-digit year, < 60 is considered after Y2K */
    if (value[0] <= '6')
    {
      buff[0]= '2';
      buff[1]= '0';
    }
    else
    {
      buff[0]= '1';
      buff[1]= '9';
    }
  }
  else
  {
    buff[0]= value[0];
    buff[1]= value[1];
    value+= 2;
    length-= 2;
  }

  buff[2]= *value++;
  buff[3]= *value++;
  buff[4]= '-';

  if (value[0] == '0' && value[1] == '0')
  {
    /* Month was 0, which ODBC can't handle. */
    return NULL;
  }

  pos= buff+5;
  length&= 30;  /* Ensure that length is ok */

  for (i= 1, length-= 2; (int)length > 0; length-= 2, ++i)
  {
    *pos++= *value++;
    *pos++= *value++;
    *pos++= i < 2 ? '-' : (i == 2) ? ' ' : ':';
  }
  for ( ; pos != buff + 20; ++i)
  {
    *pos++= '0';
    *pos++= '0';
    *pos++= i < 2 ? '-' : (i == 2) ? ' ' : ':';
  }

  return buff;
}


tempBuf::tempBuf(size_t size) : buf(nullptr), buf_len(0), cur_pos(0)
{
  if (size)
    extend_buffer(size);
}

tempBuf::tempBuf(const tempBuf& b)
{
  *this = b;
}

tempBuf::tempBuf(const char* src, size_t len) {
  add_to_buffer(src, len);
}

char *tempBuf::extend_buffer(size_t len)
{
  if (cur_pos > buf_len)
    throw "Position is outside of buffer";

  if (len > buf_len - cur_pos)
  {
    buf = (char*)realloc(buf, buf_len + len);

    // TODO: smarter processing for Out-of-Memory
    if (buf == NULL)
      throw "Not enough memory for buffering";
    buf_len += len;
  }

  return buf + cur_pos; // Return position in the new buffer
}


char *tempBuf::extend_buffer(char *to, size_t len)
{
  cur_pos = to - buf;

  return extend_buffer(len);
}

char *tempBuf::add_to_buffer(const char *from, size_t len)
{
  if (cur_pos > buf_len)
    throw "Position is outside of buffer";

  size_t extend_by = buf_len - cur_pos >= len ? 0 :
    buf_len - cur_pos + len;

  extend_buffer(extend_by);
  memcpy(buf + cur_pos, from, len);
  cur_pos += len;
  return buf + cur_pos;
}

char *tempBuf::add_to_buffer(char *to, const char *from, size_t len)
{
  cur_pos = to - buf;
  if (cur_pos > buf_len)
    throw "Position is outside of buffer";

  return add_to_buffer(from, len);
}


void tempBuf::remove_trail_zeroes()
{
  while (cur_pos && buf[cur_pos-1] == '\0') --cur_pos;
}

void tempBuf::reset()
{
  cur_pos = 0;
}

tempBuf::~tempBuf()
{
  if (buf_len && buf)
  {
    free(buf);
  }
}

tempBuf::operator bool()
{
  return buf != nullptr;
}

void tempBuf::operator=(const tempBuf& b)
{
  buf_len = 0;
  cur_pos = 0;
  buf = nullptr;
  if (b.buf_len)
  {
    extend_buffer(b.buf_len);
    memcpy(buf, b.buf, b.cur_pos);
  }
  // setting to the same position as the original
  cur_pos = b.cur_pos;
}

/*
  Get the offset and row numbers from a string with LIMIT

  @param[in] cs        charset
  @param[in] query     query
  @param[in] query_end end of query
  @param[out] offs_out output buffer for offset
  @param[out] rows_out output buffer for rows

  @return the position where LIMIT OFFS, ROWS is ending
*/
const char* get_limit_numbers(CHARSET_INFO* cs, const char *query, const char * query_end,
                       unsigned long long *offs_out, unsigned int *rows_out)
{
  char digit_buf[30];
  int index_pos = 0;

  // Skip spaces after LIMIT
  while ((query_end > query) && myodbc_isspace(cs, query, query_end))
    ++query;

  // Collect all numbers for the offset
  while ((query_end > query) && myodbc_isnum(cs, query, query_end))
  {
    digit_buf[index_pos] = *query;
    ++index_pos;
    ++query;
  }

  if (!index_pos)
  {
    // Something went wrong, the limit numbers are not found
    return query;
  }

  digit_buf[index_pos] = '\0';
  *offs_out = (unsigned long long)atoll(digit_buf);

  // Find the row number for "LIMIT offset, row_number"
  while ((query_end > query) && !myodbc_isnum(cs, query, query_end))
    ++query;

  if (query == query_end)
  {
    // It was "LIMIT row_number" without offset
    // What we thought was offset is in fact the number of rows
    *rows_out = (unsigned long)*offs_out;
    *offs_out = 0;
    return query;
  }

  index_pos = 0; // reset index to use with another number

  // Collect all numbers for the row number
  while ((query_end > query) && myodbc_isnum(cs, query, query_end))
  {
    digit_buf[index_pos] = *query;
    ++index_pos;
    ++query;
  }

  digit_buf[index_pos] = '\0';
  *rows_out = (unsigned long)atol(digit_buf);
  return query;
}

/*
  Check if SELECT query requests the row locking

  @param[in] cs          charset
  @param[in] query       query
  @param[in] query_end   query end
  @param[in] is_share    flag to check the share mode otherwise for update

  @return position of "FOR UPDATE" or "LOCK IN SHARE MODE" inside a query.
          Otherwise returns NULL.
*/
const char* check_row_locking(CHARSET_INFO* cs, const char * query, const char * query_end, BOOL is_share_mode)
{
  const char *before_token= query_end;
  const char *token= NULL;
  int i = 0;
  const char *for_update[2] = { "UPDATE", "FOR" };
  const char *lock_in_share_mode[4] = { "MODE", "SHARE", "IN", "LOCK" };
  const char **check = for_update;
  int index_max = 2;

  if (is_share_mode)
  {
    check = lock_in_share_mode;
    index_max = 4;
  }

  for (i = 0; i < index_max; ++i)
  {
    token = mystr_get_prev_token(cs, &before_token, query);
    if (myodbc_casecmp(token, check[i], (uint)strlen(check[i])))
      return NULL;
  }
  return token;
}


MY_LIMIT_CLAUSE find_position4limit(CHARSET_INFO* cs, const char *query, const char * query_end)
{
  MY_LIMIT_CLAUSE result(0,0,NULL,NULL);
  char *limit_pos = NULL;

  result.begin= result.end= query_end;

  assert(query && query_end && query_end >= query);

  if ((limit_pos = (char*)find_token(cs, query, query_end, "LIMIT")))
  {
    // Found LIMIT in the query
    result.end = get_limit_numbers(cs, limit_pos + 5, query_end,
                                   &result.offset, &result.row_count);
    // We will start again from the position of LIMIT to simplify the logic
    result.begin = limit_pos;
  }
  else // No LIMIT in SELECT
  {
    const char *locking_pos = NULL;

    if ((locking_pos = check_row_locking(cs, query, query_end, FALSE)) ||
        (locking_pos = check_row_locking(cs, query, query_end, TRUE)))
    {
      // FOR UPDATE or LOCK IN SHARE MODE was detected
      result.begin= result.end = (char*)locking_pos - 1; // With a previous space
    }
    else
    {
      while(query_end > query && (!*query_end ||
                myodbc_isspace(cs, query_end, result.end)))
      {
        --query_end;
      }

      if (*query_end==';')
      {
        result.begin= result.end= query_end;
      }
    }
  }

  return result;
}


BOOL myodbc_isspace(CHARSET_INFO* cs, const char * begin, const char *end)
{
  int ctype;
  cs->cset->ctype(cs, &ctype, (const uchar*) begin, (const uchar*) end);

  return ctype & _MY_SPC;
}

BOOL myodbc_isnum(CHARSET_INFO* cs, const char * begin, const char *end)
{
  int ctype;
  cs->cset->ctype(cs, &ctype, (const uchar*)begin, (const uchar*)end);

  return ctype & _MY_NMR;
}


int got_out_parameters(STMT *stmt)
{
  uint i;
  DESCREC *iprec;
  int result= NO_OUT_PARAMETERS;

  for(i= 0; i < stmt->param_count; ++i)
  {
    iprec= desc_get_rec(stmt->ipd, i, '\0');

    if (iprec)
    {
      if(iprec->parameter_type == SQL_PARAM_INPUT_OUTPUT
                || iprec->parameter_type == SQL_PARAM_OUTPUT)
      {
        result|= GOT_OUT_PARAMETERS;
      }
#ifndef USE_IODBC
      else if (iprec->parameter_type == SQL_PARAM_INPUT_OUTPUT_STREAM
                || iprec->parameter_type == SQL_PARAM_OUTPUT_STREAM)
      {
        result|= GOT_OUT_STREAM_PARAMETERS;
      }
#endif
    }
  }

  return result;
}


int get_session_variable(STMT *stmt, const char *var, char *result)
{
  char buff[255+4*NAME_CHAR_LEN], *to;
  MYSQL_RES *res;
  MYSQL_ROW row;

  if (var)
  {
    to= myodbc_stpmov(buff, "SHOW SESSION VARIABLES LIKE '");
    to= myodbc_stpmov(to, var);
    to= myodbc_stpmov(to, "'");
    *to= '\0';

    if (!SQL_SUCCEEDED(stmt->dbc->execute_query(buff, SQL_NTS, TRUE)))
    {
      return 0;
    }

    res= mysql_store_result(stmt->dbc->mysql);
    if (!res)
      return 0;

    row= mysql_fetch_row(res);
    if (row)
    {
      strcpy(result, row[1]);
      mysql_free_result(res);
      return (int)strlen(result);
    }

    mysql_free_result(res);
  }

  return 0;
}


/**
  Sets the value of @@max_execution_time

  @param[in]  stmt        stmt handler
  @param[in]  new_value   Value to set @@max_execution_time.

  Returns new_value if operation was successful, -1 otherwise
 */
SQLRETURN set_query_timeout(STMT *stmt, SQLULEN new_value)
{
  char query[44];
  SQLRETURN rc= SQL_SUCCESS;

  if (new_value == stmt->stmt_options.query_timeout ||
      !is_minimum_version(stmt->dbc->mysql->server_version, "5.7.8"))
  {
    /* Do nothing if setting same timeout or MySQL server older than 5.7.8 */
    return SQL_SUCCESS;
  }

  if (new_value > 0)
  {
    unsigned long long msec_value= (unsigned long long)new_value * 1000;
    myodbc_snprintf(query, sizeof(query),
                    "set @@max_execution_time=%llu", msec_value);
  }
  else
  {
    strcpy(query, "set @@max_execution_time=DEFAULT");
    new_value= 0;
  }

  if (SQL_SUCCEEDED(rc = stmt->dbc->execute_query(query, SQL_NTS, true)))
  {
    stmt->stmt_options.query_timeout= new_value;
  }

  return rc;
}


SQLULEN get_query_timeout(STMT *stmt)
{
  SQLULEN query_timeout= SQL_QUERY_TIMEOUT_DEFAULT; /* 0 */

  if (is_minimum_version(stmt->dbc->mysql->server_version, "5.7.8"))
  {
    /* Be cautious with very long values even if they don't make sense */
    char query_timeout_char[32]= {0};
    uint length= get_session_variable(stmt, "MAX_EXECUTION_TIME",
                                      (char*)query_timeout_char);
    /* Terminate the string just in case */
    query_timeout_char[length]= 0;
    /* convert */
    query_timeout= (SQLULEN)(atol(query_timeout_char) / 1000);
  }
  return query_timeout;
}


const char get_identifier_quote(STMT *stmt)
{
  const char tick= '`', quote= '"', empty= ' ';

  if (is_minimum_version(stmt->dbc->mysql->server_version, "3.23.06"))
  {
    /*
      The full list of all SQL modes takes over 512 symbols, so we reserve
      some for the future
     */
    char sql_mode[2048]= " ";
    /*
      The token finder skips the leading space and starts
      with the first non-space value. Thus (sql_mode+1).
    */
    uint length= get_session_variable(stmt, "SQL_MODE", (char*)(sql_mode+1));

    const char *end=  sql_mode + length;
    if (find_first_token(stmt->dbc->ansi_charset_info, sql_mode, end, "ANSI_QUOTES"))
    {
      return quote;
    }
    else
    {
      return tick;
    }
  }
  return empty;
}

