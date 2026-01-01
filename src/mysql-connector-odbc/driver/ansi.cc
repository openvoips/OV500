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
  @file  ansi.c
  @brief Entry points for ANSI versions of ODBC functions
*/

#include "driver.h"


#define NOT_IMPLEMENTED \
  return SQL_ERROR


SQLRETURN SQL_API
SQLColAttributeImpl(SQLHSTMT hstmt, SQLUSMALLINT column,
                    SQLUSMALLINT field, SQLPOINTER char_attr,
                    SQLSMALLINT char_attr_max, SQLSMALLINT *char_attr_len,
                    SQLLEN *num_attr);
SQLRETURN SQL_API
SQLGetConnectAttrImpl(SQLHDBC hdbc, SQLINTEGER attribute, SQLPOINTER value,
                      SQLINTEGER value_max, SQLINTEGER *value_len);
SQLRETURN SQL_API
SQLGetDiagRecImpl(SQLSMALLINT handle_type, SQLHANDLE handle,
                  SQLSMALLINT record, SQLCHAR *sqlstate,
                  SQLINTEGER *native_error, SQLCHAR *message,
                  SQLSMALLINT message_max, SQLSMALLINT *message_len);
SQLRETURN SQL_API
SQLPrepareImpl(SQLHSTMT hstmt, SQLCHAR *str, SQLINTEGER str_len,
               bool force_prepare);

SQLRETURN SQL_API
SQLSetConnectAttrImpl(SQLHDBC hdbc, SQLINTEGER attribute,
                      SQLPOINTER value, SQLINTEGER value_len);


SQLRETURN SQL_API
SQLColAttribute(SQLHSTMT hstmt, SQLUSMALLINT column,
                SQLUSMALLINT field, SQLPOINTER char_attr,
                SQLSMALLINT char_attr_max, SQLSMALLINT *char_attr_len,
#ifdef USE_SQLCOLATTRIBUTE_SQLLEN_PTR
                SQLLEN *num_attr
#else
                SQLPOINTER num_attr
#endif
               )
{
  LOCK_STMT(hstmt);
  return SQLColAttributeImpl(hstmt, column, field, char_attr, char_attr_max,
                             char_attr_len, num_attr);
}


SQLRETURN SQL_API
SQLColAttributeImpl(SQLHSTMT hstmt, SQLUSMALLINT column,
                    SQLUSMALLINT field, SQLPOINTER char_attr,
                    SQLSMALLINT char_attr_max, SQLSMALLINT *char_attr_len,
                    SQLLEN *num_attr)
{
  STMT *stmt= (STMT *)hstmt;
  SQLCHAR *value= NULL;
  SQLINTEGER len= SQL_NTS;
  SQLRETURN rc= MySQLColAttribute(hstmt, column, field, &value, num_attr);

  if (value)
  {
    SQLCHAR *old_value= value;
    len = (SQLINTEGER)strlen((char *)value);

    /* We set the error only when the result is intented to be returned */
    if ((char_attr || num_attr) && len > char_attr_max - 1)
      rc= stmt->set_error(MYERR_01004, NULL, 0);

    if (char_attr && char_attr_max > 1)
      strmake((char *)char_attr, (char *)value, char_attr_max - 1);

    if (char_attr_len)
      *char_attr_len= (SQLSMALLINT)len;

  }

  return rc;
}


SQLRETURN SQL_API
SQLColAttributes(SQLHSTMT hstmt, SQLUSMALLINT column, SQLUSMALLINT field,
                 SQLPOINTER char_attr, SQLSMALLINT char_attr_max,
                 SQLSMALLINT *char_attr_len, SQLLEN *num_attr)
{
  LOCK_STMT(hstmt);
  return SQLColAttributeImpl(hstmt, column, field, char_attr, char_attr_max,
                             char_attr_len, num_attr);
}


SQLRETURN SQL_API
SQLColumnPrivileges(SQLHSTMT hstmt,
                    SQLCHAR *catalog, SQLSMALLINT catalog_len,
                    SQLCHAR *schema, SQLSMALLINT schema_len,
                    SQLCHAR *table, SQLSMALLINT table_len,
                    SQLCHAR *column, SQLSMALLINT column_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLColumnPrivileges(hstmt, catalog, catalog_len, schema, schema_len,
                            table, table_len, column, column_len);

  return rc;
}


SQLRETURN SQL_API
SQLColumns(SQLHSTMT hstmt, SQLCHAR *catalog, SQLSMALLINT catalog_len,
           SQLCHAR *schema, SQLSMALLINT schema_len,
           SQLCHAR *table, SQLSMALLINT table_len,
           SQLCHAR *column, SQLSMALLINT column_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLColumns(hstmt, catalog, catalog_len, schema, schema_len,
                   table, table_len, column, column_len);

  return rc;
}


SQLRETURN SQL_API
SQLConnect(SQLHDBC hdbc, SQLCHAR *dsn, SQLSMALLINT dsn_len_in,
           SQLCHAR *user, SQLSMALLINT user_len_in,
           SQLCHAR *auth, SQLSMALLINT auth_len_in)
{
  uint errors;
  SQLRETURN rc;
  SQLINTEGER dsn_len= dsn_len_in, user_len= user_len_in,
             auth_len= auth_len_in;

  SQLWCHAR *dsnw=  sqlchar_as_sqlwchar(default_charset_info,
                                       dsn, &dsn_len, &errors);
  SQLWCHAR *userw= sqlchar_as_sqlwchar(default_charset_info,
                                       user, &user_len, &errors);
  SQLWCHAR *authw= sqlchar_as_sqlwchar(default_charset_info,
                                       auth, &auth_len, &errors);

  CHECK_HANDLE(hdbc);

  rc= MySQLConnect(hdbc, dsnw, dsn_len_in, userw, user_len_in,
                   authw, auth_len_in);

  x_free(dsnw);
  x_free(userw);
  x_free(authw);

  return rc;
}


SQLRETURN SQL_API
SQLDescribeCol(SQLHSTMT hstmt, SQLUSMALLINT column,
               SQLCHAR *name, SQLSMALLINT name_max, SQLSMALLINT *name_len,
               SQLSMALLINT *type, SQLULEN *size, SQLSMALLINT *scale,
               SQLSMALLINT *nullable)
{
  STMT *stmt= (STMT *)hstmt;
  SQLCHAR *value= NULL;
  SQLINTEGER len= SQL_NTS;
  SQLSMALLINT free_value= 0;

  SQLRETURN rc;

  LOCK_STMT(hstmt);

  rc= MySQLDescribeCol(hstmt, column, &value, &free_value, type,
                                 size, scale, nullable);

  if (free_value == -1)
  {
    set_mem_error(stmt->dbc->mysql);
    return handle_connection_error(stmt);
  }

  if (value)
  {
    SQLCHAR *old_value= value;
    len = (SQLINTEGER)strlen((char *)value);

    /* We set the error only when the result is intented to be returned */
    if (name && len > name_max - 1)
      rc= stmt->set_error(MYERR_01004, NULL, 0);

    if (name && name_max > 1)
      strmake((char *)name, (char *)value, name_max - 1);

    if (name_len)
      *name_len= (SQLSMALLINT)len;

    if (free_value)
      x_free(value);
  }

  return rc;
}


SQLRETURN SQL_API
SQLDriverConnect(SQLHDBC hdbc, SQLHWND hwnd, SQLCHAR *in, SQLSMALLINT in_len,
                 SQLCHAR *out, SQLSMALLINT out_max, SQLSMALLINT *out_len,
                 SQLUSMALLINT completion)
{
  SQLRETURN rc;
  uint errors;
  SQLWCHAR *outw= NULL;
  SQLINTEGER inw_len;
  SQLWCHAR *inw;
  SQLSMALLINT outw_max, dummy_out;

  CHECK_HANDLE(hdbc);

  if (in_len == SQL_NTS)
    in_len = (SQLSMALLINT)strlen((char *)in);
  if (!out_len)
    out_len= &dummy_out;

  inw_len= in_len;
  inw= sqlchar_as_sqlwchar(utf8_charset_info, in, &inw_len, &errors);

  outw_max= (sizeof(SQLWCHAR) * out_max) / MAX_BYTES_PER_UTF8_CP;

  if (outw_max)
  {
    outw= (SQLWCHAR *)myodbc_malloc(sizeof(SQLWCHAR) * out_max, MYF(0));
    if (!outw)
    {
      rc= ((DBC*)hdbc)->set_error("HY001", NULL, 0);
      goto error;
    }
  }

  rc= MySQLDriverConnect(hdbc, hwnd, inw, in_len,
                         outw, out_max, out_len, completion);

#ifdef WIN32
  /*
    Microsoft ADO/VB? specifies in and out to be the same, but then gives a
    nonsense value for out_max. Just don't do anything in this case.

    @todo verify that this actually happens.
  */
  if ((rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO) && out && in != out && out_max)
#else
  if ((rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO) && out && out_max)
#endif
  {
    uint errors;
    /* Now we have to convert SQLWCHAR back into a SQLCHAR. */
    sqlwchar_as_sqlchar_buf(default_charset_info, out, out_max,
                            outw, *out_len, &errors);
    if (*out_len > out_max - 1)
    {
      ((DBC*)hdbc)->set_error("01004", "String data, right truncated.", 0);
      rc = SQL_SUCCESS_WITH_INFO;
    }
  }

error:
  x_free(outw);
  x_free(inw);

  return rc;
}


SQLRETURN SQL_API
SQLError(SQLHENV henv, SQLHDBC hdbc, SQLHSTMT hstmt, SQLCHAR *sqlstate,
         SQLINTEGER *native_error, SQLCHAR *message, SQLSMALLINT message_max,
         SQLSMALLINT *message_len)
{
  SQLRETURN rc= SQL_INVALID_HANDLE;

  if (hstmt)
  {
    rc= SQLGetDiagRecImpl(SQL_HANDLE_STMT, hstmt, NEXT_STMT_ERROR(hstmt),
                          sqlstate, native_error, message, message_max,
                          message_len);
  }
  else if (hdbc)
  {
    rc= SQLGetDiagRecImpl(SQL_HANDLE_DBC, hdbc, NEXT_DBC_ERROR(hdbc),
                          sqlstate, native_error, message, message_max,
                          message_len);
  }
  else if (henv)
  {
    rc= SQLGetDiagRecImpl(SQL_HANDLE_ENV, henv, NEXT_ENV_ERROR(henv),
                          sqlstate, native_error, message, message_max,
                          message_len);
  }

  return rc;
}


SQLRETURN SQL_API
SQLExecDirect(SQLHSTMT hstmt, SQLCHAR *str, SQLINTEGER str_len)
{
  int error;

  LOCK_STMT(hstmt);

  if ((error= SQLPrepareImpl(hstmt, str, str_len, false)))
    return error;
  error= my_SQLExecute((STMT *)hstmt);

  return error;
}


SQLRETURN SQL_API
SQLForeignKeys(SQLHSTMT hstmt,
               SQLCHAR *pk_catalog, SQLSMALLINT pk_catalog_len,
               SQLCHAR *pk_schema, SQLSMALLINT pk_schema_len,
               SQLCHAR *pk_table, SQLSMALLINT pk_table_len,
               SQLCHAR *fk_catalog, SQLSMALLINT fk_catalog_len,
               SQLCHAR *fk_schema, SQLSMALLINT fk_schema_len,
               SQLCHAR *fk_table, SQLSMALLINT fk_table_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLForeignKeys(hstmt, pk_catalog, pk_catalog_len,
                       pk_schema, pk_schema_len, pk_table, pk_table_len,
                       fk_catalog, fk_catalog_len, fk_schema, fk_schema_len,
                       fk_table, fk_table_len);

  return rc;
}


SQLRETURN SQL_API
SQLGetConnectAttr(SQLHDBC hdbc, SQLINTEGER attribute, SQLPOINTER value,
                  SQLINTEGER value_max, SQLINTEGER *value_len)
{
  CHECK_HANDLE(hdbc);

  return SQLGetConnectAttrImpl(hdbc, attribute, value, value_max, value_len);
}


SQLRETURN SQL_API
SQLGetConnectAttrImpl(SQLHDBC hdbc, SQLINTEGER attribute, SQLPOINTER value,
                      SQLINTEGER value_max, SQLINTEGER *value_len)
{
  DBC *dbc= (DBC *)hdbc;
  SQLCHAR *char_value= NULL;
  SQLRETURN rc= 0;

  /*
    for numeric attributes value_max can be 0, so we must check for
    the valid output buffer to prevent crashes
  */
  if (value)
    rc= MySQLGetConnectAttr(hdbc, attribute, &char_value, value);

  if (char_value)
  {
    SQLINTEGER len = (SQLINTEGER)strlen((char *)char_value);

    /*
      This check is inside the statement, which does not
      execute if output buffer is NULL
      see: "if (char_value)"
    */
    if (len > value_max - 1)
      rc = dbc->set_error(MYERR_01004, NULL, 0);

    if (value && value_max > 1)
      strmake((char *)value, (char *)char_value, value_max - 1);

    if (value_len)
      *value_len= len;

  }

  return rc;
}


SQLRETURN SQL_API
SQLGetConnectOption(SQLHDBC hdbc, SQLUSMALLINT option, SQLPOINTER value)
{
  CHECK_HANDLE(hdbc);

  return SQLGetConnectAttrImpl(hdbc, option, value,
                               ((option == SQL_ATTR_CURRENT_CATALOG) ?
                                SQL_MAX_OPTION_STRING_LENGTH : 0), NULL);
}


SQLRETURN SQL_API
SQLGetCursorName(SQLHSTMT hstmt, SQLCHAR *cursor, SQLSMALLINT cursor_max,
                 SQLSMALLINT *cursor_len)
{
  STMT *stmt= (STMT *)hstmt;
  SQLCHAR *name;

  LOCK_STMT(stmt);
  CLEAR_STMT_ERROR(stmt);

  if (cursor_max < 0)
    return stmt->set_error(MYERR_S1090, NULL, 0);

  name= MySQLGetCursorName(hstmt);
  SQLINTEGER len = (SQLINTEGER)strlen((char *)name);

  if (cursor && cursor_max > 1)
    strmake((char *)cursor, (char *)name, cursor_max - 1);

  if (cursor_len)
    *cursor_len= (SQLSMALLINT)len;

  /* We set the error only when the result is intented to be returned */
  if (cursor && len > cursor_max - 1)
    return stmt->set_error(MYERR_01004, NULL, 0);

  return SQL_SUCCESS;
}


SQLRETURN SQL_API
SQLGetDiagField(SQLSMALLINT handle_type, SQLHANDLE handle,
                SQLSMALLINT record, SQLSMALLINT field,
                SQLPOINTER info, SQLSMALLINT info_max,
                SQLSMALLINT *info_len)
{
  DBC *dbc;
  SQLCHAR *value= NULL;

  SQLRETURN rc= SQL_SUCCESS;

  CHECK_HANDLE(handle);

  rc= MySQLGetDiagField(handle_type, handle, record, field,
                        &value, info);

  switch (handle_type) {
  case SQL_HANDLE_DBC:
    dbc= (DBC *)handle;
    break;
  case SQL_HANDLE_STMT:
    dbc= ((STMT *)handle)->dbc;
    break;
  case SQL_HANDLE_DESC:
    dbc= DESC_GET_DBC((DESC *)handle);
    break;
  case SQL_HANDLE_ENV:
  default:
    dbc= NULL;
  }

  if (value)
  {
    size_t len = strlen((char *)value);

    /* We set the error only when the result is intented to be returned */
    if (info && len > info_max - 1)
      rc = dbc->set_error(MYERR_01004, NULL, 0);

    if (info_len)
      *info_len= (SQLSMALLINT)len;

    if (info && info_max > 1)
      strmake((char *)info, (char *)value, info_max - 1);
  }

  return rc;
}


SQLRETURN SQL_API
SQLGetDiagRec(SQLSMALLINT handle_type, SQLHANDLE handle,
              SQLSMALLINT record, SQLCHAR *sqlstate,
              SQLINTEGER *native_error, SQLCHAR *message,
              SQLSMALLINT message_max, SQLSMALLINT *message_len)
{
  CHECK_HANDLE(handle);

  return SQLGetDiagRecImpl(handle_type, handle, record, sqlstate, native_error,
                           message, message_max, message_len);
}


SQLRETURN SQL_API
SQLGetDiagRecImpl(SQLSMALLINT handle_type, SQLHANDLE handle,
                  SQLSMALLINT record, SQLCHAR *sqlstate,
                  SQLINTEGER *native_error, SQLCHAR *message,
                  SQLSMALLINT message_max, SQLSMALLINT *message_len)
{
  SQLRETURN rc;
  DBC *dbc;
  SQLCHAR *msg_value= NULL, *sqlstate_value= NULL;

  if (handle == NULL)
  {
    return SQL_INVALID_HANDLE;
  }

  switch (handle_type) {
  case SQL_HANDLE_DBC:
    dbc= (DBC *)handle;
    break;
  case SQL_HANDLE_STMT:
    dbc= ((STMT *)handle)->dbc;
    break;
  case SQL_HANDLE_DESC:
    dbc= DESC_GET_DBC((DESC *)handle);
    break;
  case SQL_HANDLE_ENV:
  default:
    dbc= NULL;
  }

  if (message_max < 0)
    return SQL_ERROR;

  rc= MySQLGetDiagRec(handle_type, handle, record, &sqlstate_value,
                      native_error, &msg_value);

  if (rc == SQL_NO_DATA_FOUND)
  {
    return SQL_NO_DATA_FOUND;
  }

  if (msg_value)
  {
    size_t len= strlen((char *)msg_value);

    /*
      We set the error only when the result is intented to be returned
      and message_max is greaater than 0
    */
    if (message && message_max && len > message_max - 1)
      rc = dbc->set_error(MYERR_01004, NULL, 0);

    if (message_len)
      *message_len= (SQLSMALLINT)len;

    if (message && message_max > 1)
      strmake((char *)message, (char *)msg_value, message_max - 1);
  }

  if (sqlstate && sqlstate_value)
  {
    strmake((char *)sqlstate,
            sqlstate_value ? (char *)sqlstate_value : "00000", 5);
  }

  return rc;
}


SQLRETURN SQL_API
SQLGetInfo(SQLHDBC hdbc, SQLUSMALLINT type, SQLPOINTER value,
            SQLSMALLINT value_max, SQLSMALLINT *value_len)
{
  DBC *dbc= (DBC *)hdbc;
  SQLCHAR *char_value= NULL;

  CHECK_HANDLE(hdbc);

  SQLRETURN rc = MySQLGetInfo(hdbc, type, &char_value, value, value_len);

  if (char_value)
  {
    size_t len = strlen((char *)char_value);

    /*
      MSSQL implementation does not return the truncation warning if the
      value is not NULL and value_max is 0
     */
    if (value && value_max && len > value_max - 1)
      rc = dbc->set_error(MYERR_01004, NULL, 0);

    if (value && value_max > 1)
      strmake((char *)value, (char *)char_value, value_max - 1);

    if (value_len)
      *value_len= (SQLSMALLINT)len;
  }

  return rc;
}


SQLRETURN SQL_API
SQLGetStmtAttr(SQLHSTMT hstmt, SQLINTEGER attribute, SQLPOINTER value,
                SQLINTEGER value_max, SQLINTEGER *value_len)
{
  LOCK_STMT(hstmt);

  /* Nothing special to do, since we don't have any string stmt attribs */
  return MySQLGetStmtAttr(hstmt, attribute, value, value_max, value_len);
}


SQLRETURN SQL_API
SQLGetTypeInfo(SQLHSTMT hstmt, SQLSMALLINT type)
{
  LOCK_STMT(hstmt);

  return MySQLGetTypeInfo(hstmt, type);
}


SQLRETURN SQL_API
SQLNativeSql(SQLHDBC hdbc, SQLCHAR *in, SQLINTEGER in_len,
             SQLCHAR *out, SQLINTEGER out_max, SQLINTEGER *out_len)
{
  SQLRETURN rc= SQL_SUCCESS;

  LOCK_DBC(hdbc);

  if (in_len == SQL_NTS)
  {
    in_len = (SQLINTEGER)strlen((char *)in);
  }

  if (out_len)
  {
    *out_len= in_len;
  }

  if (out && in_len >= out_max)
  {
    rc = ((DBC *)hdbc)->set_error(MYERR_01004, NULL, 0);
  }

  if(out_max > 0)
  {
    if (in_len > out_max - 1)
    {
      in_len= out_max - 1;
    }

    (void)memcpy((char *)out, (const char *)in, in_len);
    out[in_len]= '\0';
  }

  return rc;
}


SQLRETURN SQL_API
SQLPrepare(SQLHSTMT hstmt, SQLCHAR *str, SQLINTEGER str_len)
{
  LOCK_STMT(hstmt);

  return SQLPrepareImpl(hstmt, str, str_len, true);
}


SQLRETURN SQL_API
SQLPrepareImpl(SQLHSTMT hstmt, SQLCHAR *str, SQLINTEGER str_len,
               bool force_prepare)
{
  STMT *stmt= (STMT *)hstmt;

  /*
    If the ANSI character set is the same as the connection character set,
    we can pass it straight through. Otherwise it needs to be converted to
    the connection character set (probably utf-8).
  */
  return MySQLPrepare(hstmt, str, str_len, false, force_prepare);
}


SQLRETURN SQL_API
SQLPrimaryKeys(SQLHSTMT hstmt,
               SQLCHAR *catalog, SQLSMALLINT catalog_len,
               SQLCHAR *schema, SQLSMALLINT schema_len,
               SQLCHAR *table, SQLSMALLINT table_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLPrimaryKeys(hstmt, catalog, catalog_len, schema, schema_len,
                       table, table_len);

  return rc;
}


SQLRETURN SQL_API
SQLProcedureColumns(SQLHSTMT hstmt,
                    SQLCHAR *catalog, SQLSMALLINT catalog_len,
                    SQLCHAR *schema, SQLSMALLINT schema_len,
                    SQLCHAR *proc, SQLSMALLINT proc_len,
                    SQLCHAR *column, SQLSMALLINT column_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLProcedureColumns(hstmt, catalog, catalog_len,
                            schema, schema_len, proc, proc_len,
                            column, column_len);

  return rc;
}


SQLRETURN SQL_API
SQLProcedures(SQLHSTMT hstmt,
              SQLCHAR *catalog, SQLSMALLINT catalog_len,
              SQLCHAR *schema, SQLSMALLINT schema_len,
              SQLCHAR *proc, SQLSMALLINT proc_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLProcedures(hstmt, catalog, catalog_len, schema, schema_len,
                      proc, proc_len);
  // Remove parameters
  ((STMT *)hstmt)->free_reset_params();

  return rc;
}


SQLRETURN SQL_API
SQLSetConnectAttr(SQLHDBC hdbc, SQLINTEGER attribute,
                  SQLPOINTER value, SQLINTEGER value_len)
{
  CHECK_HANDLE(hdbc);

  return SQLSetConnectAttrImpl(hdbc, attribute, value, value_len);
}


SQLRETURN SQL_API
SQLSetConnectAttrImpl(SQLHDBC hdbc, SQLINTEGER attribute,
                      SQLPOINTER value, SQLINTEGER value_len)
{
  SQLRETURN rc;
  DBC *dbc= (DBC *)hdbc;
  rc= MySQLSetConnectAttr(hdbc, attribute, value, value_len);
  return rc;
}


SQLRETURN SQL_API
SQLSetConnectOption(SQLHDBC hdbc, SQLUSMALLINT option, SQLULEN param)
{
  SQLINTEGER value_len= 0;

  CHECK_HANDLE(hdbc);

  if (option == SQL_ATTR_CURRENT_CATALOG)
    value_len= SQL_NTS;

  return SQLSetConnectAttrImpl(hdbc, option, (SQLPOINTER)param, value_len);
}


SQLRETURN SQL_API
SQLSetCursorName(SQLHSTMT hstmt, SQLCHAR *name, SQLSMALLINT name_len)
{
  STMT *stmt= (STMT *)hstmt;
  SQLINTEGER len= name_len;
  uint errors= 0;

  LOCK_STMT(hstmt);
  return MySQLSetCursorName(hstmt, name, name_len);
}


SQLRETURN SQL_API
SQLSetStmtAttr(SQLHSTMT hstmt, SQLINTEGER attribute,
               SQLPOINTER value, SQLINTEGER value_len)
{
  LOCK_STMT(hstmt);

  /* Nothing special to do, since we don't have any string stmt attribs */
  return MySQLSetStmtAttr(hstmt, attribute, value, value_len);
}


SQLRETURN SQL_API
SQLSpecialColumns(SQLHSTMT hstmt, SQLUSMALLINT type,
                  SQLCHAR *catalog, SQLSMALLINT catalog_len,
                  SQLCHAR *schema, SQLSMALLINT schema_len,
                  SQLCHAR *table, SQLSMALLINT table_len,
                  SQLUSMALLINT scope, SQLUSMALLINT nullable)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLSpecialColumns(hstmt, type, catalog, catalog_len, schema, schema_len,
                          table, table_len, scope, nullable);

  return rc;
}


SQLRETURN SQL_API
SQLStatistics(SQLHSTMT hstmt,
              SQLCHAR *catalog, SQLSMALLINT catalog_len,
              SQLCHAR *schema, SQLSMALLINT schema_len,
              SQLCHAR *table, SQLSMALLINT table_len,
              SQLUSMALLINT unique, SQLUSMALLINT accuracy)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;
  rc= MySQLStatistics(hstmt, catalog, catalog_len, schema, schema_len,
                      table, table_len, unique, accuracy);
  return rc;
}


SQLRETURN SQL_API
SQLTablePrivileges(SQLHSTMT hstmt,
                   SQLCHAR *catalog, SQLSMALLINT catalog_len,
                   SQLCHAR *schema, SQLSMALLINT schema_len,
                   SQLCHAR *table, SQLSMALLINT table_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLTablePrivileges(hstmt, catalog, catalog_len, schema, schema_len,
                           table, table_len);

  return rc;
}


SQLRETURN SQL_API
SQLTables(SQLHSTMT hstmt,
          SQLCHAR *catalog, SQLSMALLINT catalog_len,
          SQLCHAR *schema, SQLSMALLINT schema_len,
          SQLCHAR *table, SQLSMALLINT table_len,
          SQLCHAR *type, SQLSMALLINT type_len)
{
  SQLRETURN rc;
  DBC *dbc;

  LOCK_STMT(hstmt);

  dbc= ((STMT *)hstmt)->dbc;

  rc= MySQLTables(hstmt, catalog, catalog_len, schema, schema_len,
                  table, table_len, type, type_len);

  return rc;
}


SQLRETURN SQL_API
SQLGetDescField(SQLHDESC hdesc, SQLSMALLINT record, SQLSMALLINT field,
                SQLPOINTER value, SQLINTEGER value_max, SQLINTEGER *value_len)
{
  CHECK_HANDLE(hdesc);

  return MySQLGetDescField(hdesc, record, field, value, value_max, value_len);
}


SQLRETURN SQL_API
SQLGetDescRec(SQLHDESC hdesc, SQLSMALLINT record, SQLCHAR *name,
              SQLSMALLINT name_max, SQLSMALLINT *name_len, SQLSMALLINT *type,
              SQLSMALLINT *subtype, SQLLEN *length, SQLSMALLINT *precision,
              SQLSMALLINT *scale, SQLSMALLINT *nullable)
{
  NOT_IMPLEMENTED;
}


SQLRETURN SQL_API
SQLSetDescField(SQLHDESC hdesc, SQLSMALLINT record, SQLSMALLINT field,
                SQLPOINTER value, SQLINTEGER value_len)
{
  CHECK_HANDLE(hdesc);
  DESC *desc = (DESC*)hdesc;
  return desc->set_field(record, field, value, value_len);
}


SQLRETURN SQL_API
SQLSetDescRec(SQLHDESC hdesc, SQLSMALLINT record, SQLSMALLINT type,
              SQLSMALLINT subtype, SQLLEN length, SQLSMALLINT precision,
              SQLSMALLINT scale, SQLPOINTER data_ptr,
              SQLLEN *octet_length_ptr, SQLLEN *indicator_ptr)
{
  NOT_IMPLEMENTED;
}


/**
  Discover and enumerate the attributes and attribute values required to
  connect.

  @return Always returns @c SQL_ERROR, because the driver does not support this.

  @since ODBC 1.0
*/
SQLRETURN SQL_API
SQLBrowseConnect(SQLHDBC hdbc, SQLCHAR *in, SQLSMALLINT in_len,
                 SQLCHAR *out, SQLSMALLINT out_max, SQLSMALLINT *out_len)
{
  CHECK_HANDLE(hdbc);

  return ((DBC*)hdbc)->set_error(MYERR_S1000,
    "Driver does not support this API", 0);
}


#ifdef NOT_IMPLEMENTED_YET
//SQLDataSources


//SQLDrivers


#endif
