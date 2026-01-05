// Copyright (c) 2001, 2024, Oracle and/or its affiliates.
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
  @file  transact.c
  @brief Transaction processing functions.
*/

#include "driver.h"

/**
  Commit or roll back the transactions associated with a particular
  database connection.

  @param[in] hdbc            Handle of database connection
  @param[in] CompletionType  How to complete the transactions,
                             @c SQL_COMMIT or @c SQL_ROLLBACK
*/
static SQLRETURN my_transact(SQLHDBC hdbc, SQLSMALLINT CompletionType)
{
  SQLRETURN result= SQL_SUCCESS;
  DBC *dbc= (DBC *)hdbc;
  const char *query;
  uint	length;

  if (dbc && !dbc->ds.opt_NO_TRANSACTIONS)
  {
    switch(CompletionType) {
    case SQL_COMMIT:
      query= "COMMIT";
      length= 6;
      break;

    case SQL_ROLLBACK:
      if (!trans_supported(dbc))
      {
	      return ((DBC*)hdbc)->set_error(MYERR_S1C00,
			    "Underlying server does not support transactions, "
          "upgrade to version >= 3.23.38", 0);
      }
      query= "ROLLBACK";
      length= 8;
      break;

    default:
      return ((DBC*)hdbc)->set_error(MYERR_S1012, NULL, 0);
    }

    MYLOG_DBC_QUERY(dbc, query);

    LOCK_DBC(dbc);
    if (check_if_server_is_alive(dbc) ||
	mysql_real_query(dbc->mysql,query,length))
    {
      result = ((DBC*)hdbc)->set_error(MYERR_S1000,
			     mysql_error(dbc->mysql),
			     mysql_errno(dbc->mysql));
    }
  }
  return(result);
}


/**
  Commit or roll back the transactions associated with a particular
  database connection, or all connections in an environment.

  @param[in] HandleType      Type of @a Handle, @c SQL_HANDLE_ENV or
                             @c SQL_HANDLE_DBC
  @param[in] Handle          Handle to database connection or environment
  @param[in] CompletionType  How to complete the transactions,
                             @c SQL_COMMIT or @c SQL_ROLLBACK
*/
static SQLRETURN SQL_API
end_transaction(SQLSMALLINT HandleType,
	   SQLHANDLE   Handle,
	   SQLSMALLINT CompletionType)
{
  SQLRETURN result= SQL_SUCCESS;
  ENV *henv;
  DBC *hdbc;

  switch (HandleType) {
  case SQL_HANDLE_ENV:
  {
    henv= (ENV *)Handle;
    LOCK_ENV(henv);
    for (DBC *dbc : henv->conn_list)
    {
        my_transact(dbc, CompletionType);
    }
    break;
  }
  case SQL_HANDLE_DBC:
  {
    hdbc= (DBC*)Handle;

#ifndef _WIN32
    LOCK_DBC(hdbc);
#endif
    result= my_transact(hdbc, CompletionType);
    break;
  }
  default:
    result= SQL_ERROR;
    ((STMT*)Handle)->set_error(MYERR_S1092,NULL,0);
    break;
  }
  return result;
}


/**
  Commit or roll back the transactions associated with a particular
  database connection, or all connections in an environment.

  @param[in] HandleType      Type of @a Handle, @c SQL_HANDLE_ENV or
                             @c SQL_HANDLE_DBC
  @param[in] Handle          Handle to database connection or environment
  @param[in] CompletionType  How to complete the transactions,
                             @c SQL_COMMIT or @c SQL_ROLLBACK

  @since ODBC 3.0
  @since ISO SQL 92
*/
SQLRETURN SQL_API
SQLEndTran(SQLSMALLINT HandleType,
           SQLHANDLE   Handle,
           SQLSMALLINT CompletionType)
{
  CHECK_HANDLE(Handle);

  return end_transaction(HandleType, Handle, CompletionType);
}


/**
  Commit or roll back the transactions associated with a particular
  database connection, or all connections in an environment.

  @deprecated This function is deprecated, SQLEndTran() should be used instead.

  @param[in] henv            Handle to database environment
  @param[in] hdbc            Handle to database connection
  @param[in] fType           How to complete the transactions,
                             @c SQL_COMMIT or @c SQL_ROLLBACK

  @since ODBC 1.0
*/
SQLRETURN SQL_API SQLTransact(SQLHENV henv,
			      SQLHDBC hdbc,
			      SQLUSMALLINT fType)
{
  if(henv == NULL && hdbc == NULL)
    return SQL_INVALID_HANDLE;

  return end_transaction(hdbc ? SQL_HANDLE_DBC : SQL_HANDLE_ENV,
                         hdbc ? hdbc : henv, fType);
}
