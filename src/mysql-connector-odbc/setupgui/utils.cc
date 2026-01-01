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

#include "setupgui.h"

#include "driver.h"
#include "stringutil.h"


extern SQLHDBC hDBC;
extern SQLWCHAR **errorMsgs;


void FreeEnvHandle(SQLHENV hEnv)
{
  if (hDBC == SQL_NULL_HDBC)
    SQLFreeHandle(SQL_HANDLE_ENV, hEnv);
}


void Disconnect(SQLHDBC hDbc, SQLHENV hEnv)
{
  SQLDisconnect(hDbc);

  if (hDBC == SQL_NULL_HDBC)
    SQLFreeHandle(SQL_HANDLE_DBC, hDbc);

  FreeEnvHandle(hEnv);
}


SQLRETURN Connect(SQLHDBC *hDbc, SQLHENV *hEnv, DataSource *params)
{
  SQLRETURN   nReturn;
  SQLWSTRING  string_connect_in;

  assert(params->opt_DRIVER);

  /* Blank out DSN name, otherwise it will pull the info from the registry */
  params->opt_DSN = nullptr;

  string_connect_in = params->to_kvpair(';');

  if (hDBC == SQL_NULL_HDBC)
  {
    nReturn= SQLAllocHandle(SQL_HANDLE_ENV, NULL, hEnv);

    if (nReturn != SQL_SUCCESS)
      ShowDiagnostics(nReturn, SQL_HANDLE_ENV, NULL);

    if (!SQL_SUCCEEDED(nReturn))
      return nReturn;

    nReturn= SQLSetEnvAttr(*hEnv, SQL_ATTR_ODBC_VERSION,
                           (SQLPOINTER)SQL_OV_ODBC3, 0);

    if (nReturn != SQL_SUCCESS)
      ShowDiagnostics(nReturn, SQL_HANDLE_ENV, NULL);

    if (!SQL_SUCCEEDED(nReturn))
    {
      return nReturn;
    }

    nReturn= SQLAllocHandle(SQL_HANDLE_DBC, *hEnv, hDbc);
    if (nReturn != SQL_SUCCESS)
      ShowDiagnostics(nReturn, SQL_HANDLE_ENV, *hEnv);
    if (!SQL_SUCCEEDED(nReturn))
    {
      return nReturn;
    }
  }

  nReturn= SQLDriverConnectW(*hDbc, NULL, (SQLWCHAR*)string_connect_in.c_str(),
                             SQL_NTS, NULL, 0, NULL, SQL_DRIVER_NOPROMPT);

  if (nReturn != SQL_SUCCESS)
    ShowDiagnostics(nReturn, SQL_HANDLE_DBC, *hDbc);

  return nReturn;
}


void ShowDiagnostics(SQLRETURN nReturn, SQLSMALLINT nHandleType, SQLHANDLE h)
{
  BOOL        bDiagnostics= FALSE;
  SQLSMALLINT nRec= 1;
  SQLWCHAR     szSQLState[6];
  SQLINTEGER  nNative;
  SQLWCHAR     szMessage[SQL_MAX_MESSAGE_LENGTH];
  SQLSMALLINT nMessage;

  if (h)
  {
    *szSQLState= '\0';
    *szMessage= '\0';

    while (SQL_SUCCEEDED(SQLGetDiagRecW(nHandleType,
      h,
      nRec,
      szSQLState,
      &nNative,
      szMessage,
      SQL_MAX_MESSAGE_LENGTH,
      &nMessage)))
    {
      szSQLState[5]= '\0';
      szMessage[SQL_MAX_MESSAGE_LENGTH - 1]= '\0';


      /*add2list(errorMsgs, szMessage);*/

      bDiagnostics= TRUE;
      nRec++;

      *szSQLState= '\0';
      *szMessage= '\0';
    }
  }

  switch (nReturn)
  {
  case SQL_ERROR:
    /*strAssign(popupMsg, L"Request returned with SQL_ERROR.");*/
    break;
  case SQL_SUCCESS_WITH_INFO:
    /*strAssign(popupMsg, L"Request return with SQL_SUCCESS_WITH_INFO.");*/
    break;
  case SQL_INVALID_HANDLE:
    /*strAssign(popupMsg, L"Request returned with SQL_INVALID_HANDLE.");*/
    break;
  default:
    /*strAssign(popupMsg, L"Request did not return with SQL_SUCCESS.");*/
    break;
  }
}
