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
  @file  error.c
  @brief Error handling functions.
*/

#include "driver.h"
#include "mysqld_error.h"
#include "errmsg.h"

/*
  @type    : myodbc3 internal error structure
  @purpose : set of internal errors, in the following order
  - SQLSTATE2 (when version is SQL_OV_ODBC2)
  - SQLSTATE3 (when version is SQL_OV_ODBC3)
  - error message text
  - return code
*/

static MYODBC3_ERR_STR myodbc3_errors[]=
{
  {"01000","General warning", SQL_SUCCESS_WITH_INFO},
  {"01004","String data, right truncated", SQL_SUCCESS_WITH_INFO},
  {"01S02","Option value changed", SQL_SUCCESS_WITH_INFO},
  {"01S03","No rows updated/deleted", SQL_SUCCESS_WITH_INFO},
  {"01S04","More than one row updated/deleted", SQL_SUCCESS_WITH_INFO},
  {"01S06","Attempt to fetch before the result set returned the first rowset",
    SQL_SUCCESS_WITH_INFO},
  {"07001","SQLBindParameter not used for all parameters", SQL_ERROR},
  {"07005","Prepared statement not a cursor-specification", SQL_ERROR},
  {"07006","Restricted data type attribute violation", SQL_ERROR},
  {"07009","Invalid descriptor index", SQL_ERROR},
  {"08002","Connection name in use", SQL_ERROR},
  {"08003","Connection does not exist", SQL_ERROR},
  {"24000","Invalid cursor state", SQL_ERROR},
  {"25000","Invalid transaction state", SQL_ERROR},
  {"25S01","Transaction state unknown", SQL_ERROR},
  {"34000","Invalid cursor name", SQL_ERROR},
  {"HYT00","Timeout expired", SQL_ERROR},
  {"HY000","General driver defined error", SQL_ERROR},
  {"HY001","Memory allocation error", SQL_ERROR},
  {"HY002","Invalid column number", SQL_ERROR},
  {"HY003","Invalid application buffer type", SQL_ERROR},
  {"HY004","Invalid SQL data type", SQL_ERROR},
  {"HY007","Associated statement is not prepared", SQL_ERROR},
  {"HY009","Invalid use of null pointer", SQL_ERROR},
  {"HY010","Function sequence error", SQL_ERROR},
  {"HY011","Attribute can not be set now", SQL_ERROR},
  {"HY012","Invalid transaction operation code", SQL_ERROR},
  {"HY013","Memory management error", SQL_ERROR},
  {"HY015","No cursor name available", SQL_ERROR},
  {"HY016","Cannot modify an implementation row descriptor", SQL_ERROR},
  {"HY017","Invalid use of an automatically allocated descriptor handle", SQL_ERROR},
  {"HY024","Invalid attribute value", SQL_ERROR},
  {"HY090","Invalid string or buffer length", SQL_ERROR},
  {"HY091","Invalid descriptor field identifier", SQL_ERROR},
  {"HY092","Invalid attribute/option identifier", SQL_ERROR},
  {"HY093","Invalid parameter number", SQL_ERROR},
  {"HY095","Function type out of range", SQL_ERROR},
  {"HY106","Fetch type out of range", SQL_ERROR},
  {"HY107","Row value out of range", SQL_ERROR},
  {"HY109","Invalid cursor position", SQL_ERROR},
  {"HYC00","Optional feature not implemented", SQL_ERROR},
  /* server related..*/
  {"21S01","Column count does not match value count", SQL_ERROR},
  {"23000","Integrity constraint violation", SQL_ERROR},
  {"42000","Syntax error or access violation", SQL_ERROR},
  {"42S01","Base table or view already exists ", SQL_ERROR},
  {"42S02","Base table or view not found", SQL_ERROR},
  {"42S12","Index not found", SQL_ERROR},
  {"42S21","Column already exists", SQL_ERROR},
  {"42S22","Column not found", SQL_ERROR},
  {"08S01","Communication link failure", SQL_ERROR},
  {"08004","Server rejected the connection", SQL_ERROR},
};


/*
  @type    : myodbc3 internal
  @purpose : If ODBC version is SQL_OV_ODBC2, initialize old SQLSTATEs
*/

void myodbc_sqlstate2_init(void)
{
    /*
      As accessing the SQLSTATE2 is very rare, set this to
      global when required .. one time ..for quick processing
      in set_error/set_conn_error
    */
    uint  i;
    for ( i= MYERR_S1000; i <= MYERR_S1C00; ++i )
    {
        myodbc3_errors[i].sqlstate[0]= 'S';
        myodbc3_errors[i].sqlstate[1]= '1';
    }
    myodbc_stpmov(myodbc3_errors[MYERR_07005].sqlstate,"24000");
    myodbc_stpmov(myodbc3_errors[MYERR_42000].sqlstate,"37000");
    myodbc_stpmov(myodbc3_errors[MYERR_42S01].sqlstate,"S0001");
    myodbc_stpmov(myodbc3_errors[MYERR_42S02].sqlstate,"S0002");
    myodbc_stpmov(myodbc3_errors[MYERR_42S12].sqlstate,"S0012");
    myodbc_stpmov(myodbc3_errors[MYERR_42S21].sqlstate,"S0021");
    myodbc_stpmov(myodbc3_errors[MYERR_42S22].sqlstate,"S0022");
}


/*
  @type    : myodbc3 internal
  @purpose : If ODBC version is SQL_OV_ODBC3, initialize to original SQLSTATEs
*/

void myodbc_sqlstate3_init(void)
{
    uint i;

    for ( i= MYERR_S1000; i <= MYERR_S1C00; ++i )
    {
        myodbc3_errors[i].sqlstate[0]= 'H';
        myodbc3_errors[i].sqlstate[1]= 'Y';
    }
    myodbc_stpmov(myodbc3_errors[MYERR_07005].sqlstate,"07005");
    myodbc_stpmov(myodbc3_errors[MYERR_42000].sqlstate,"42000");
    myodbc_stpmov(myodbc3_errors[MYERR_42S01].sqlstate,"42S01");
    myodbc_stpmov(myodbc3_errors[MYERR_42S02].sqlstate,"42S02");
    myodbc_stpmov(myodbc3_errors[MYERR_42S12].sqlstate,"42S12");
    myodbc_stpmov(myodbc3_errors[MYERR_42S21].sqlstate,"42S21");
    myodbc_stpmov(myodbc3_errors[MYERR_42S22].sqlstate,"42S22");
}

MYERROR::MYERROR(myodbc_errid errid, const char* errtext, SQLINTEGER errcode,
  const char* prefix)
{
  std::string errmsg;

  errmsg = (errtext ? errtext : myodbc3_errors[errid].message);
  native_error = errcode ? (myodbc_errid)errcode : errid + MYODBC_ERROR_CODE_START;

  retcode = myodbc3_errors[errid].retcode;
  sqlstate = myodbc3_errors[errid].sqlstate;
  message = prefix + errmsg;
}

MYERROR::MYERROR(const char* state, const char* msg, SQLINTEGER errcode,
  const char* prefix)
{
  sqlstate = state ? state : "";
  message = std::string(prefix ? prefix : MYODBC_ERROR_PREFIX) +
    (msg ? msg : "");
  native_error = errcode;
  retcode = SQL_ERROR;
}

/*
  @type    : myodbc3 internal
  @purpose : sets the descriptor level errors
*/

SQLRETURN set_desc_error(DESC *        desc,
                         char *        state,
                         const char *  message,
                         uint          errcode)
{
  desc->error = MYERROR(state, message, errcode, MYODBC_ERROR_PREFIX);
  return SQL_ERROR;
}


/*
  @type    : myodbc3 internal
  @purpose : translates SQL error to ODBC error
*/

void translate_error(char *save_state, myodbc_errid errid, uint mysql_err)
{
    const char *state= myodbc3_errors[errid].sqlstate;

    switch (mysql_err)
    {
        case ER_WRONG_VALUE_COUNT:
            state= "21S01";
            break;
        case ER_DUP_ENTRY:
        case ER_DUP_KEY:
            state= "23000";
            break;
        case ER_NO_DB_ERROR:
            state= "3D000";
            break;
        case ER_PARSE_ERROR:
#ifdef ER_SP_DOES_NOT_EXIST
        case ER_SP_DOES_NOT_EXIST:
#endif
            state= myodbc3_errors[MYERR_42000].sqlstate;
            break;
        case ER_TABLE_EXISTS_ERROR:
            state= myodbc3_errors[MYERR_42S01].sqlstate;
            break;
        case ER_FILE_NOT_FOUND:
        case ER_NO_SUCH_TABLE:
        case ER_CANT_OPEN_FILE:
        case ER_BAD_TABLE_ERROR:
            state= myodbc3_errors[MYERR_42S02].sqlstate;
            break;
        case ER_NO_SUCH_INDEX:
        case ER_CANT_DROP_FIELD_OR_KEY:
            state= myodbc3_errors[MYERR_42S12].sqlstate;
            break;
        case ER_DUP_FIELDNAME:
            state= myodbc3_errors[MYERR_42S21].sqlstate;
            break;
        case ER_BAD_FIELD_ERROR:
            state= myodbc3_errors[MYERR_42S22].sqlstate;
            break;
        case CR_SERVER_HANDSHAKE_ERR:
        case CR_CONNECTION_ERROR:
        case CR_SERVER_GONE_ERROR:
        case CR_SERVER_LOST:
#if MYSQL_VERSION_ID > 80023
        case ER_CLIENT_INTERACTION_TIMEOUT:
#endif
            state= "08S01";
            break;
        case ER_MUST_CHANGE_PASSWORD_LOGIN:
        case CR_AUTH_PLUGIN_CANNOT_LOAD_ERROR:
            state= "08004"; /* Server rejected the connection */
                            /* The data source rejected the establishment of the
                               connection for implementation-defined reasons. */
            break;
        default: break;
    }
    myodbc_stpmov(save_state, state);
}


/*
  @type    : myodbc3 internal
  @purpose : sets the error information to environment handle.
*/

SQLRETURN set_env_error(ENV *env, myodbc_errid errid, const char *errtext,
                        SQLINTEGER errcode)
{
  env->error = MYERROR(errid, errtext, errcode, MYODBC_ERROR_PREFIX);
  return env->error.retcode;
}


/*
  @type    : myodbc3 internal
  @purpose : sets a myodbc_malloc() failure on a MYSQL* connection
*/
void set_mem_error(MYSQL *mysql)
{
  mysql->net.last_errno= CR_OUT_OF_MEMORY;
  myodbc_stpmov(mysql->net.last_error, "Memory allocation failed");
  myodbc_stpmov(mysql->net.sqlstate, "HY001");
}


/**
  Handle a connection-related error.

  @param[in]  stmt  Statement
*/
SQLRETURN handle_connection_error(STMT *stmt)
{
  unsigned int err= mysql_errno(stmt->dbc->mysql);
  switch (err) {
  case 0:  /* no error */
    return SQL_SUCCESS;
  case CR_SERVER_GONE_ERROR:
  case CR_SERVER_LOST:
#if MYSQL_VERSION_ID > 80023
  case ER_CLIENT_INTERACTION_TIMEOUT:
#endif
    return stmt->set_error("08S01", mysql_error(stmt->dbc->mysql), err);
  case CR_OUT_OF_MEMORY:
    return stmt->set_error("HY001", mysql_error(stmt->dbc->mysql), err);
  case CR_COMMANDS_OUT_OF_SYNC:
  case CR_UNKNOWN_ERROR:
  default:
    return stmt->set_error("HY000", mysql_error(stmt->dbc->mysql), err);
  }
}


/*
  Little helper to check if error code means that we have lost connection
  @param[in]  errcode  code of the last error
*/
my_bool is_connection_lost(uint errcode)
{
  if (errcode==CR_SERVER_GONE_ERROR || errcode==CR_SERVER_LOST
#if MYSQL_VERSION_ID > 80023
    || errcode==ER_CLIENT_INTERACTION_TIMEOUT
#endif
  )
  {
    return 1;
  }

  return 0;
}

/*
  @type    : myodbc3 internal
  @purpose : sets the error information to appropriate handle.
  it also sets the SQLSTATE based on the ODBC VERSION
*/

SQLRETURN set_handle_error(SQLSMALLINT HandleType, SQLHANDLE handle,
                           myodbc_errid errid, const char *errtext,
                           SQLINTEGER errcode)
{
  switch ( HandleType )
  {
    case SQL_HANDLE_ENV:
      return set_env_error((ENV*)handle, errid, errtext, errcode);
    case SQL_HANDLE_DBC:
      return ((DBC*)handle)->set_error(errid, errtext, errcode);
    case SQL_HANDLE_STMT:
    {
      STMT *stmt = (STMT*)handle;
      stmt->error = MYERROR(errid, errtext, errcode, stmt->dbc->st_error_prefix);
      return stmt->error.retcode;
    }
    case SQL_HANDLE_DESC:
    {
      DESC* desc = (DESC*)handle;
      desc->error = MYERROR(errid, errtext, errcode, desc->stmt->dbc->st_error_prefix);
      return desc->error.retcode;
    }
    default:
      return SQL_INVALID_HANDLE;
  }
}


/**
*/
SQLRETURN SQL_API
MySQLGetDiagRec(SQLSMALLINT handle_type, SQLHANDLE handle, SQLSMALLINT record,
                SQLCHAR **sqlstate, SQLINTEGER *native, SQLCHAR **message)
{
  MYERROR *error;
  SQLINTEGER tmp_native;

  if (!native)
    native= &tmp_native;

  if (!handle || record <= 0)
    return SQL_ERROR;

  /*
    Currently we are not supporting error list, so
    if RecNumber > 1, return no data found
  */
  if (record > 1)
    return SQL_NO_DATA_FOUND;

  if (handle_type == SQL_HANDLE_STMT)
    error= &((STMT *)handle)->error;
  else if (handle_type == SQL_HANDLE_DBC)
    error= &((DBC *)handle)->error;
  else if (handle_type == SQL_HANDLE_ENV)
    error= &((ENV *)handle)->error;
  else if (handle_type == SQL_HANDLE_DESC)
    error= &((DESC *)handle)->error;
  else
    return SQL_INVALID_HANDLE;

  if (error->message.empty())
  {
    *message= (SQLCHAR *)"";
    *sqlstate= (SQLCHAR *)"00000";
    *native= 0;
    return SQL_NO_DATA_FOUND;
  }

  *message= (SQLCHAR *)error->message.c_str();
  *sqlstate= (SQLCHAR *)error->sqlstate.c_str();
  *native= error->native_error;

  return SQL_SUCCESS;
}


bool is_odbc3_subclass(std::string sqlstate)
{
  const char *states[]= { "01S00", "01S01", "01S02", "01S06", "01S07", "07S01",
    "08S01", "21S01", "21S02", "25S01", "25S02", "25S03", "42S01", "42S02",
    "42S11", "42S12", "42S21", "42S22", "HY095", "HY097", "HY098", "HY099",
    "HY100", "HY101", "HY105", "HY107", "HY109", "HY110", "HY111", "HYT00",
    "HYT01", "IM001", "IM002", "IM003", "IM004", "IM005", "IM006", "IM007",
    "IM008", "IM010", "IM011", "IM012"};
  size_t i;

  if (sqlstate.empty())
    return false;

  for (i= 0; i < sizeof(states) / sizeof(states[0]); ++i)
    if (sqlstate.compare(states[i]) == 0)
      return true;

  return false;
}


/*
  @type    : ODBC 3.0 API
  @purpose : returns the current value of a field of a record of the
  diagnostic data structure (associated with a specified handle)
  that contains error, warning, and status information
*/
SQLRETURN SQL_API
MySQLGetDiagField(SQLSMALLINT handle_type, SQLHANDLE handle, SQLSMALLINT record,
                  SQLSMALLINT identifier, SQLCHAR **char_value,
                  SQLPOINTER num_value)
{
  SQLLEN num;

  /* Handle may not be these types, but this saves lots of casts below. */
  STMT *stmt= (STMT *)handle;
  DBC *dbc= (DBC *)handle;
  DESC *desc= (DESC *)handle;
  MYERROR *error;

  if (!num_value)
    num_value= &num;

  if (!handle)
    return SQL_ERROR;

  if (handle_type == SQL_HANDLE_DESC)
    error= &desc->error;
  else if (handle_type == SQL_HANDLE_STMT)
    error= &stmt->error;
  else if (handle_type == SQL_HANDLE_DBC)
    error= &dbc->error;
  else if (handle_type == SQL_HANDLE_ENV)
    error= &((ENV *)handle)->error;
  else
    return SQL_ERROR;

  if (record > 1)
    return SQL_NO_DATA_FOUND;

  switch (identifier)
  {
  /*  Header fields */
  case SQL_DIAG_CURSOR_ROW_COUNT:
    if (handle_type != SQL_HANDLE_STMT)
      return SQL_ERROR;
    if (!stmt->result)
      *(SQLLEN *)num_value= 0;
    else
      *(SQLLEN *)num_value= (SQLLEN) mysql_num_rows(stmt->result);
    return SQL_SUCCESS;

  case SQL_DIAG_DYNAMIC_FUNCTION:
    if (handle_type != SQL_HANDLE_STMT)
      return SQL_ERROR;
    *char_value= (SQLCHAR *)"";
    return SQL_SUCCESS;

  case SQL_DIAG_DYNAMIC_FUNCTION_CODE:
    if (handle_type != SQL_HANDLE_STMT)
      return SQL_ERROR;
    *(SQLINTEGER *)num_value= 0;
    return SQL_SUCCESS;

  case SQL_DIAG_NUMBER:
    *(SQLINTEGER *)num_value= 1;
    return SQL_SUCCESS;

  case SQL_DIAG_RETURNCODE:
    *(SQLRETURN *)num_value= error->retcode;
    return SQL_SUCCESS;

  case SQL_DIAG_ROW_COUNT:
    if (handle_type != SQL_HANDLE_STMT)
      return SQL_ERROR;
    *(SQLLEN *)num_value= (SQLLEN)stmt->affected_rows;
    return SQL_SUCCESS;

  /* Record fields */
  case SQL_DIAG_CLASS_ORIGIN:
    {
      if (record <= 0)
        return SQL_ERROR;
      auto &sqlstate = error->sqlstate;

      if (!sqlstate.empty() && sqlstate[0] == 'I' && sqlstate[1] == 'M')
        *char_value= (SQLCHAR *)"ODBC 3.0";
      else
        *char_value= (SQLCHAR *)"ISO 9075";
    }
    return SQL_SUCCESS;

  case SQL_DIAG_COLUMN_NUMBER:
    if (record <= 0)
      return SQL_ERROR;
    *(SQLINTEGER *)num_value= SQL_COLUMN_NUMBER_UNKNOWN;
    return SQL_SUCCESS;

  case SQL_DIAG_CONNECTION_NAME:
  {
    DataSource *ds;
    if (record <= 0)
      return SQL_ERROR;

    if (handle_type == SQL_HANDLE_DESC)
      ds= &desc->stmt->dbc->ds;
    else if (handle_type == SQL_HANDLE_STMT)
      ds= &stmt->dbc->ds;
    else if (handle_type == SQL_HANDLE_DBC)
      ds= &dbc->ds;
    else
      *char_value= (SQLCHAR*)"";

    if (ds)
      *char_value = ds->opt_DSN;
    return SQL_SUCCESS;
  }

  case SQL_DIAG_MESSAGE_TEXT:
    if (record <= 0)
      return SQL_ERROR;
    *char_value = (SQLCHAR *)error->message.c_str();
    return SQL_SUCCESS;

  case SQL_DIAG_NATIVE:
    *(SQLINTEGER *)num_value= error->native_error;
    return SQL_SUCCESS;

  case SQL_DIAG_ROW_NUMBER:
    if (record <= 0)
      return SQL_ERROR;
    *(SQLLEN *)num_value= SQL_ROW_NUMBER_UNKNOWN;
    return SQL_SUCCESS;

  case SQL_DIAG_SERVER_NAME:
  {
    DataSource *ds;
    if (record <= 0)
      return SQL_ERROR;
    if (handle_type == SQL_HANDLE_DESC)
      ds = &desc->stmt->dbc->ds;
    else if (handle_type == SQL_HANDLE_STMT)
      ds = &stmt->dbc->ds;
    else if (handle_type == SQL_HANDLE_DBC)
      ds = &dbc->ds;
    else
      *char_value= (SQLCHAR *)"";

    if (ds)
      *char_value= ds->opt_SERVER;

    return SQL_SUCCESS;
  }

  case SQL_DIAG_SQLSTATE:
    if (record <= 0)
      return SQL_ERROR;
    *char_value= (SQLCHAR *)error->sqlstate.c_str();
    return SQL_SUCCESS;

  case SQL_DIAG_SUBCLASS_ORIGIN:
    if (record <= 0)
      return SQL_ERROR;
    {
      if (record <= 0)
        return SQL_ERROR;

      if (is_odbc3_subclass((char*)error->sqlstate.c_str()))
        *char_value= (SQLCHAR *)"ODBC 3.0";
      else
        *char_value= (SQLCHAR *)"ISO 9075";
    }
    return SQL_SUCCESS;

  default:
    return SQL_ERROR;
  }
}
