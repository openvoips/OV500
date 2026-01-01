// Copyright (c) 2013, 2024, Oracle and/or its affiliates.
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

#include "odbctap.h"


/* Since atm it does not look like we can make a reliable test for automated testing, this is just
   a helper program to test manually putting/reusing (of) connection to/from the pool. The pooling in
   the DM you use should be turned on for MySQL driver */

DECLARE_TEST(t_reset_connection)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  int i;
  SQLINTEGER len;
  const int rows= 10;
  char dbase[32];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_reset_connection");
  ok_sql(hstmt, "DROP DATABASE IF EXISTS t_reset_connection");
  ok_sql(hstmt, "CREATE DATABASE t_reset_connection");
  ok_sql(hstmt, "CREATE TABLE t_reset_connection(a int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT)");

  for (i= 0; i < rows ; ++i)
  {
    ok_sql(hstmt, "INSERT INTO t_reset_connection values()");
  }

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));
  ok_con(hdbc1, SQLConnect(hdbc1, mydsn, (SQLSMALLINT)strlen(mydsn),
                           myuid, (SQLSMALLINT)strlen(myuid),
                           mypwd, (SQLSMALLINT)strlen(mypwd)));

  ok_con(hdbc1, SQLAllocHandle(SQL_HANDLE_STMT, hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1,SQL_ATTR_MAX_ROWS,(SQLPOINTER)5,0));

  ok_sql(hstmt1, "select a from t_reset_connection");
  is_num(myrowcount(hstmt1), 5);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_con(hdbc1, SQLSetConnectAttr(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                  "t_reset_connection",
                                  (SQLINTEGER)strlen("t_reset_connection")));
  ok_con(hdbc1, SQLGetConnectAttr(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                  dbase, sizeof(dbase), &len));

  is_str(dbase, "t_reset_connection", len);
  SQLDisconnect(hdbc1);
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  hdbc1= NULL;
  hstmt1= NULL;

  sleep(2);

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));

  /* Here the connection is supposed to be taken from the pool */
  ok_con(hdbc1, SQLConnect(hdbc1, mydsn, (SQLSMALLINT)strlen(mydsn),
                           myuid, (SQLSMALLINT)strlen(myuid),
                           mypwd, (SQLSMALLINT)strlen(mypwd)));

  ok_con(hdbc1, SQLGetConnectAttr(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                  dbase, sizeof(dbase), &len));
  is_str(dbase, mydb, len);

  ok_con(hdbc1, SQLAllocHandle(SQL_HANDLE_STMT, hdbc1, &hstmt1));

  ok_sql(hstmt1, "select a from t_reset_connection");
  is_num(myrowcount(hstmt1), rows);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  SQLDisconnect(hdbc1);
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_reset_connection");
  ok_sql(hstmt, "DROP DATABASE IF EXISTS t_reset_connection");

  return OK;
}


DECLARE_TEST(t_dummy_test)
{
  return OK;
}

BEGIN_TESTS
#ifdef WIN32
  ADD_TEST(t_reset_connection)
#endif
  ADD_TEST(t_dummy_test)
END_TESTS

myenable_pooling= 1;

RUN_TESTS
