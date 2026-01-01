// Copyright (c) 2003, 2024, Oracle and/or its affiliates.
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

SQLINTEGER my_max_rows= 100;


/* making use of mysql_use_result */
DECLARE_TEST(t_use_result)
{
  SQLINTEGER i, row_count= 0;
  SQLCHAR    ch[]= "MySQL AB";
  SQLRETURN  rc;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_use_result");
  ok_sql(hstmt, "CREATE TABLE t_use_result (id INT, name CHAR(10))");

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)
                            "INSERT INTO t_use_result VALUES (?,?)", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &i, 0, NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_CHAR, 0, 0, ch, sizeof(ch), NULL));

  for (i= 1; i <= my_max_rows; i++)
    ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_use_result");

  rc= SQLFetch(hstmt);
  while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
  {
    row_count++;
    rc= SQLFetch(hstmt);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  is_num(row_count, my_max_rows);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_use_result");

  return OK;
}


/**
 Bug #4657: "Don't Cache Results" crashes when using catalog functions
*/
DECLARE_TEST(t_bug4657)
{
  SQLCHAR     name[10];
  SQLSMALLINT column_count;
  SQLLEN      name_length;

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_FORWARD_ONLY, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug4657");
  ok_sql(hstmt, "CREATE TABLE t_bug4657 (a INT)");

  ok_stmt(hstmt, SQLTables(hstmt, (SQLCHAR *)"", SQL_NTS,
                           (SQLCHAR *)"", SQL_NTS,
                           (SQLCHAR *)"", SQL_NTS,
                           (SQLCHAR *)"UNKNOWN", SQL_NTS));

  ok_stmt(hstmt, SQLNumResultCols(hstmt, &column_count));
  is_num(column_count, 5);

  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_CHAR, name, sizeof(name),
                            &name_length));
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug4657");

  return OK;
}


/**
 Bug #39878: No error signaled if timeout during fetching data.
*/
DECLARE_TEST(t_bug39878)
{
  int         i;
  SQLINTEGER  row_count= 0;
  SQLRETURN   rc;
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_FORWARD_ONLY, 0));

  printMessage("Creating table t_bug39878");

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug39878");
  ok_sql(hstmt, "CREATE TABLE t_bug39878 (a INT)");

  // Fill table with data
  printMessage("Filling table with data...");

  ok_sql(hstmt, "INSERT INTO t_bug39878 VALUES (0), (1)");

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)
                            "INSERT INTO t_bug39878 SELECT a+? FROM t_bug39878", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &row_count, 0, NULL));

  for (i=1, row_count= 2; i < 14; ++i, row_count *= 2)
    ok_stmt(hstmt, SQLExecute(hstmt));

  printMessage("inserted %d rows.", row_count);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  printMessage("Setting net_write_timeout to 1");
  ok_sql(hstmt, "SET net_write_timeout=1");

  // Table scan 

  ok_sql(hstmt, "SELECT * FROM t_bug39878");
  printMessage("Started table scan, sleeping 3sec ...");
  sleep(3);

  printMessage("Fetching rows...");

  while (SQL_SUCCEEDED(rc= SQLFetch(hstmt)))
  {
    row_count--;
  }

  print_diag(rc, SQL_HANDLE_STMT, hstmt, "SQLFetch()", __FILE__, __LINE__);
  printMessage("Scan interrupted, %d rows left in the table.", row_count);

  {
    char *rc_name;
    switch(rc)
    {
    case SQL_SUCCESS:           rc_name= "SQL_SUCCESS";           break;
    case SQL_SUCCESS_WITH_INFO: rc_name= "SQL_SUCCESS_WITH_INFO"; break;
    case SQL_NO_DATA:           rc_name= "SQL_NO_DATA";           break;
    case SQL_STILL_EXECUTING:   rc_name= "SQL_STILL_EXECUTING";   break;
    case SQL_ERROR:             rc_name= "SQL_ERROR";             break;
    case SQL_INVALID_HANDLE:    rc_name= "SQL_INVALID_HANDLE";    break;
    default:                    rc_name= "<unknown>";             break;
    }
    printMessage("Last SQLFetch() returned: %s", rc_name);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  is(row_count == 0 || rc == SQL_ERROR);

  // We re-connect to drop the table (as connection might be broken)
  alloc_basic_handles(&henv1, &hdbc1, &hstmt1);
  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_bug39878");
  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_use_result)
  ADD_TEST(t_bug4657)
  ADD_TEST(t_bug39878)
END_TESTS


SET_DSN_OPTION(1048576);


RUN_TESTS
