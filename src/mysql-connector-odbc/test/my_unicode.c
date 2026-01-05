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

#include <wchar.h>
#include "odbctap.h"
#include <sqlucode.h>

DECLARE_TEST(emojibug)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];
  SQLLEN nLen = 0;

  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
                               NULL, "CHARSET=utf8mb4");

  ok_con(hdbc, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS test_emoji");
  ok_sql(hstmt1, "CREATE TABLE test_emoji (col1 varchar(32) character set utf8mb4)");
  ok_sql(hstmt1, "INSERT INTO test_emoji VALUES (0xf09f988a)");

  ok_stmt(hstmt1, SQLPrepareW(hstmt1, W(L"SELECT * FROM test_emoji"), SQL_NTS));

  ok_stmt(hstmt1, SQLExecute(hstmt1));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_WCHAR, wbuff, MAX_ROW_DATA_LEN + 1, &nLen));
  printf("Fetched %d bytes (%d, %d)\n", (int)nLen, wbuff[0], wbuff[1]);
  SQLWCHAR smile[2] = { 0xD83D, 0xDE0A };
  is_num(smile[0], wbuff[0]);
  is_num(smile[1], wbuff[1]);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


DECLARE_TEST(sqlconnect)
{
  HDBC hdbc1;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1,
                            WC(mydsn), SQL_NTS,
                            WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlprepare)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLINTEGER data;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1,
                            WC(mydsn), SQL_NTS,
                            WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLPrepareW(hstmt1,
                              W(L"SELECT '\x30a1' FROM DUAL WHERE 1 = ?"),
                              SQL_NTS));

  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                   SQL_INTEGER, 0, 0, &data, 0, NULL));
  data= 0;
  ok_stmt(hstmt1, SQLExecute(hstmt1));

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  data= 1;
  ok_stmt(hstmt1, SQLExecute(hstmt1));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 1), L"\x30a1", 1);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  /*
    Some driver managers (like iODBC) will always do the character conversion
    themselves.
  */
  if (!using_dm(hdbc))
  {
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    /* Now try ANSI SQLPrepare. */
    ok_stmt(hstmt1, SQLPrepare(hstmt1,
                               (SQLCHAR *)"SELECT '\xe3' FROM DUAL WHERE 1 = ?",
                               SQL_NTS));

    data= 0;
    ok_stmt(hstmt1, SQLExecute(hstmt1));

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

    data= 1;
    ok_stmt(hstmt1, SQLExecute(hstmt1));

    ok_stmt(hstmt1, SQLFetch(hstmt1));

    is_wstr(my_fetch_wstr(hstmt1, wbuff, 1), L"\x00e3", 1);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


/**
  Test calling SQLPrepareW() on an ANSI connection. Only tested when
  we're not using a driver manager, otherwise the driver manager does
  Unicode to ANSI translations.
*/
DECLARE_TEST(sqlprepare_ansi)
{
  SQLINTEGER data;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  if (using_dm(hdbc))
    skip("not relevant when using driver manager");

  /* Now try SQLPrepareW with an ANSI connection. */
  ok_stmt(hstmt, SQLPrepareW(hstmt,
                             W(L"SELECT '\x00e3' FROM DUAL WHERE 1 = ?"),
                             SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &data, 0, NULL));
  data= 0;
  ok_stmt(hstmt, SQLExecute(hstmt));

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  data= 1;
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_wstr(my_fetch_wstr(hstmt, wbuff, 1), L"\x00e3", 1);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Now try SQLPrepareW with a character that doesn't translate. */
  expect_stmt(hstmt, SQLPrepareW(hstmt,
                                 W(L"SELECT '\x30a1' FROM DUAL WHERE 1 = ?"),
                                 SQL_NTS),
              SQL_ERROR);
  is(check_sqlstate(hstmt, "22018") == OK);

  return OK;
}


DECLARE_TEST(sqlchar)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR data[]= "S\xe3o Paolo", buff[30];
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];
  wchar_t wcdata[]= L"S\x00e3o Paolo";

  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
                               NULL, "");

  ok_con(hdbc, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLPrepareW(hstmt1, W(L"SELECT CAST(? AS BINARY)"), SQL_NTS));

  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                   SQL_WVARCHAR, 0, 0, data, sizeof(data),
                                   NULL));
  ok_stmt(hstmt1, SQLExecute(hstmt1));

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_str(my_fetch_str(hstmt1, buff, 1), data, sizeof(data));

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Do it again so we can try as SQLWCHAR */
  ok_stmt(hstmt1, SQLExecute(hstmt1));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 1), wcdata, 9);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


DECLARE_TEST(sqldriverconnect)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  wchar_t conn_in[512];
  wchar_t dummy[256];
  SQLWCHAR conn_out[1024];
  SQLSMALLINT conn_out_len;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));

  *conn_in= L'\0';
  wcscat(conn_in, L"DRIVER=");
  mbstowcs(dummy, (char *)mydriver, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";UID=");
  mbstowcs(dummy, (char *)myuid, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";PWD=");
  mbstowcs(dummy, (char *)mypwd, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";DATABASE=");
  mbstowcs(dummy, (char *)mydb, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";SERVER=");
  mbstowcs(dummy, (char *)myserver, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  if (mysock != NULL)
  {
    wcscat(conn_in, L";SOCKET=");
    mbstowcs(dummy, (char *)mysock, sizeof(dummy)/sizeof(wchar_t));
    wcscat(conn_in, dummy);
  }
  if (myport)
  {
    char pbuff[20];
    sprintf(pbuff, ";PORT=%d", myport);
    mbstowcs(dummy, (char *)pbuff, sizeof(dummy)/sizeof(wchar_t));
    wcscat(conn_in, dummy);
  }

  ok_con(hdbc1, SQLDriverConnectW(hdbc1, NULL, WL(conn_in, wcslen(conn_in)),
                                  (SQLSMALLINT)wcslen(conn_in), conn_out,
                                  (SQLSMALLINT)(sizeof(conn_out)/sizeof(SQLWCHAR)),
                                  &conn_out_len, SQL_DRIVER_NOPROMPT));

  ok_con(hdbc, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, W(L"SELECT 1234"), SQL_NTS));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 1234);
  printMessage("\nPrepare to Free STMT");
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  printMessage("\nDONE\nPrepare to Disconnect");
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  printMessage("\nDONE\nPrepare to Free Connect");
  ok_con(hdbc1, SQLFreeConnect(hdbc1));
  printMessage("\nDONE\n");
  return OK;
}


DECLARE_TEST(sqlnativesql)
{
  HDBC hdbc1;
  SQLWCHAR    out[128];
  wchar_t in[]= L"SELECT * FROM venu";
  SQLINTEGER len;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1,
                            WC(mydsn), SQL_NTS,
                            WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLNativeSqlW(hdbc1, W(in), SQL_NTS, out, sizeof(out), &len));
  is_num(len, sizeof(in) / sizeof(wchar_t) - 1);
  is_wstr(sqlwchar_to_wchar_t(out), in, sizeof(in) / sizeof(wchar_t) - 1);

  ok_con(hdbc1, SQLNativeSqlW(hdbc1, W(in), SQL_NTS, out, 8, &len));
  is_num(len, sizeof(in) / sizeof(wchar_t) - 1);
  is_wstr(sqlwchar_to_wchar_t(out), in, 7);
  is(out[7] == 0);

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlsetcursorname)
{
  HDBC hdbc1;
  HSTMT hstmt1, hstmt_pos;
  SQLLEN  nRowCount;
  SQLCHAR data[10];

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_demo_cursor");
  ok_sql(hstmt, "CREATE TABLE my_demo_cursor (id INT, name VARCHAR(20))");
  ok_sql(hstmt, "INSERT INTO my_demo_cursor VALUES (0,'MySQL0'),(1,'MySQL1'),"
         "(2,'MySQL2'),(3,'MySQL3'),(4,'MySQL4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLSetStmtAttrW(hstmt1, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0));

  ok_stmt(hstmt1, SQLSetCursorNameW(hstmt1, W(L"a\x00e3b"), SQL_NTS));

  /* Open the resultset of table 'my_demo_cursor' */
  ok_sql(hstmt1, "SELECT * FROM my_demo_cursor");

  /* goto the last row */
  ok_stmt(hstmt1, SQLFetchScroll(hstmt1, SQL_FETCH_LAST, 1L));

  /* create new statement handle */
  ok_con(hdbc1, SQLAllocHandle(SQL_HANDLE_STMT, hdbc1, &hstmt_pos));

  /* now update the name field to 'updated' using positioned cursor */
  ok_stmt(hstmt_pos,
          SQLExecDirectW(hstmt_pos,
                         W(L"UPDATE my_demo_cursor SET name='updated' "
                           L"WHERE CURRENT OF a\x00e3b"), SQL_NTS));

  ok_stmt(hstmt_pos, SQLRowCount(hstmt_pos, &nRowCount));
  is_num(nRowCount, 1);

  ok_stmt(hstmt_pos, SQLFreeStmt(hstmt_pos, SQL_CLOSE));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Now delete 2nd row */
  ok_sql(hstmt1, "SELECT * FROM my_demo_cursor");

  /* goto the second row */
  ok_stmt(hstmt1, SQLFetchScroll(hstmt1, SQL_FETCH_ABSOLUTE, 2L));

  /* now delete the current row */
  ok_stmt(hstmt_pos,
          SQLExecDirectW(hstmt_pos,
                         W(L"DELETE FROM my_demo_cursor "
                           L"WHERE CURRENT OF a\x00e3b"), SQL_NTS));

  ok_stmt(hstmt_pos, SQLRowCount(hstmt_pos, &nRowCount));
  is_num(nRowCount, 1);

  /* free the statement cursor */
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Free the statement 'hstmt_pos' */
  ok_stmt(hstmt_pos, SQLFreeHandle(SQL_HANDLE_STMT, hstmt_pos));

  /* Now fetch and verify the data */
  ok_sql(hstmt1, "SELECT * FROM my_demo_cursor");

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 0);
  is_str(my_fetch_str(hstmt1, data, 2), "MySQL0", 6);

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 2);
  is_str(my_fetch_str(hstmt1, data, 2), "MySQL2", 6);

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 3);
  is_str(my_fetch_str(hstmt1, data, 2), "MySQL3", 6);

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 4);
  is_str(my_fetch_str(hstmt1, data, 2), "updated", 7);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_demo_cursor");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlgetcursorname)
{
  SQLRETURN rc;
  HDBC hdbc1;
  SQLHSTMT hstmt1,hstmt2,hstmt3;
  SQLWCHAR curname[50];
  SQLSMALLINT nlen;


  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));
  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt2));
  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt3));

  rc= SQLGetCursorNameW(hstmt1, curname, 50, &nlen);
  if (SQL_SUCCEEDED(rc))
  {
    is_num(nlen, 8);
    is_wstr(sqlwchar_to_wchar_t(curname), L"SQL_CUR0", 8);

    ok_stmt(hstmt3,  SQLGetCursorNameW(hstmt3, curname, 50, &nlen));

    expect_stmt(hstmt3,  SQLGetCursorNameW(hstmt1, curname, 4, &nlen),
                SQL_SUCCESS_WITH_INFO);
    is_num(nlen, 8);
    is_wstr(sqlwchar_to_wchar_t(curname), L"SQL", 4);

    expect_stmt(hstmt3,  SQLGetCursorNameW(hstmt1, curname, 0, &nlen),
                SQL_SUCCESS_WITH_INFO);
    rc = SQLGetCursorNameW(hstmt1, curname, 0, &nlen);
    mystmt_err(hstmt1,rc == SQL_SUCCESS_WITH_INFO, rc);
    is_num(nlen, 8);

    expect_stmt(hstmt1, SQLGetCursorNameW(hstmt1, curname, 8, &nlen),
                SQL_SUCCESS_WITH_INFO);
    is_num(nlen, 8);
    is_wstr(sqlwchar_to_wchar_t(curname), L"SQL_CUR", 8);

    ok_stmt(hstmt1, SQLGetCursorNameW(hstmt1, curname, 9, &nlen));
    is_num(nlen, 8);
    is_wstr(sqlwchar_to_wchar_t(curname), L"SQL_CUR0", 8);
  }

  ok_stmt(hstmt1,  SQLSetCursorNameW(hstmt1, W(L"venucur123"), 7));

  ok_stmt(hstmt1,  SQLGetCursorNameW(hstmt1, curname, 8, &nlen));
  is_num(nlen, 7);
  is_wstr(sqlwchar_to_wchar_t(curname), L"venucur", 8);

  ok_stmt(hstmt3, SQLFreeStmt(hstmt3, SQL_DROP));
  ok_stmt(hstmt2, SQLFreeStmt(hstmt2, SQL_DROP));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlcolattribute)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];
  SQLSMALLINT len;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_colattrib");
  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"CREATE TABLE t_colattrib (a\x00e3g INT)"),
                                 SQL_NTS));

  ok_sql(hstmt1, "SELECT * FROM t_colattrib AS b");

  ok_stmt(hstmt1, SQLColAttributeW(hstmt1, 1, SQL_DESC_NAME,
                                   wbuff, sizeof(wbuff), &len, NULL));
  is_num(len, 3 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(wbuff), L"a\x00e3g", 4);

  expect_stmt(hstmt1, SQLColAttributeW(hstmt1, 1, SQL_DESC_BASE_TABLE_NAME,
                                       wbuff, 5 * sizeof(SQLWCHAR), &len, NULL),
              SQL_SUCCESS_WITH_INFO);
  is_num(len, 11 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(wbuff), L"t_co", 5);

  ok_stmt(hstmt1, SQLColAttributeW(hstmt1, 1, SQL_DESC_TYPE_NAME,
                                   wbuff, sizeof(wbuff), &len, NULL));
  is_num(len, 7 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(wbuff), L"integer", 8);

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_colattrib");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqldescribecol)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];
  SQLSMALLINT len;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_desc");
  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"CREATE TABLE t_desc (a\x00e3g INT)"),
                                 SQL_NTS));

  ok_sql(hstmt1, "SELECT * FROM t_desc");

  ok_stmt(hstmt1, SQLDescribeColW(hstmt1, 1, wbuff,
                                  sizeof(wbuff) / sizeof(wbuff[0]), &len,
                                  NULL, NULL, NULL, NULL));
  is_num(len, 3);
  is_wstr(sqlwchar_to_wchar_t(wbuff), L"a\x00e3g", 4);

  expect_stmt(hstmt1, SQLDescribeColW(hstmt1, 1, wbuff, 3, &len,
                                      NULL, NULL, NULL, NULL),
              SQL_SUCCESS_WITH_INFO);
  is_num(len, 3);
  is_wstr(sqlwchar_to_wchar_t(wbuff), L"a\x00e3", 3);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_desc");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlgetconnectattr)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];
  SQLINTEGER len;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLGetConnectAttrW(hdbc1, SQL_ATTR_CURRENT_CATALOG, wbuff,
                                     sizeof(wbuff), &len));
  is_num(len, 4 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(wbuff), L"test", 5);

  expect_stmt(hstmt1, SQLGetConnectAttrW(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                         wbuff, 3 * sizeof(SQLWCHAR), &len),
              SQL_SUCCESS_WITH_INFO);
  is_num(len, 4 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(wbuff), L"te", 3);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlgetdiagrec)
{
  SQLWCHAR   sqlstate[6]= {0};
  SQLWCHAR   message[255]= {0};
  SQLINTEGER native_err= 0;
  SQLSMALLINT msglen= 0;
  HDBC hdbc1;
  HSTMT hstmt1;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));


  expect_sql(hstmt1, "DROP TABLE t_odbc3_non_existent_table", SQL_ERROR);

#if UNIXODBC_BUG_FIXED
  /*
   This should report no data found, but unixODBC doesn't even pass this
   down to the driver.
  */
  expect_stmt(hstmt1, SQLGetDiagRecW(SQL_HANDLE_STMT, hstmt1, 2, sqlstate,
                                     &native_err, message, 0, &msglen),
              SQL_NO_DATA_FOUND);
#endif

  ok_stmt(hstmt, SQLGetDiagRecW(SQL_HANDLE_STMT, hstmt1, 1, sqlstate,
                                &native_err, message, 255, &msglen));

  /* it has to comply to the bugfix for bug 14285620  */
  expect_stmt(hstmt1, SQLGetDiagRecW(SQL_HANDLE_STMT, hstmt1, 1, sqlstate,
                                     &native_err, message, 0, &msglen),
                                     SQL_SUCCESS);

  expect_stmt(hstmt1, SQLGetDiagRecW(SQL_HANDLE_STMT, hstmt1, 1, sqlstate,
                                     &native_err, message, 10, &msglen),
                                     SQL_SUCCESS_WITH_INFO);

  expect_stmt(hstmt1, SQLGetDiagRecW(SQL_HANDLE_STMT, hstmt1, 1, sqlstate,
                                     &native_err, message, -1, &msglen),
                                     SQL_ERROR);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlgetdiagfield)
{
  SQLWCHAR   message[255]= {0};
  SQLSMALLINT len;
  SQLINTEGER data;
  HDBC hdbc1;
  HSTMT hstmt1;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));


  expect_sql(hstmt1, "DROP TABLE t_odbc3_non_existent_table", SQL_ERROR);

  ok_stmt(hstmt, SQLGetDiagFieldW(SQL_HANDLE_STMT, hstmt1, 0,
                                  SQL_DIAG_NUMBER, &data, 0, NULL));
  is_num(data, 1);

  ok_stmt(hstmt, SQLGetDiagFieldW(SQL_HANDLE_STMT, hstmt1, 1,
                                  SQL_DIAG_CLASS_ORIGIN, message,
                                  sizeof(message), &len));
  is_num(len, 8 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(message), L"ISO 9075", 9);

  expect_stmt(hstmt, SQLGetDiagFieldW(SQL_HANDLE_STMT, hstmt1, 1,
                                      SQL_DIAG_SQLSTATE, message,
                                      4 * sizeof(SQLWCHAR), &len),
              SQL_SUCCESS_WITH_INFO);
  is_num(len, 5 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(message), L"42S", 4);

  ok_stmt(hstmt, SQLGetDiagFieldW(SQL_HANDLE_STMT, hstmt1, 1,
                                  SQL_DIAG_SUBCLASS_ORIGIN, message,
                                  sizeof(message), &len));
  is_num(len, 8 * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(message), L"ODBC 3.0", 9);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlcolumns)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_columns");
  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"CREATE TABLE t_columns (a\x00e3g INT)"),
                                 SQL_NTS));

  ok_stmt(hstmt1, SQLColumnsW(hstmt1, NULL, 0, NULL, 0,
                              W(L"t_columns"), SQL_NTS,
                              W(L"a\x00e3g"), SQL_NTS));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 4), L"a\x00e3g", 4);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_columns");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqltables)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"DROP TABLE IF EXISTS t_a\x00e3g"),
                                 SQL_NTS));
  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"CREATE TABLE t_a\x00e3g (a INT)"),
                                 SQL_NTS));

  ok_stmt(hstmt1, SQLTablesW(hstmt1, NULL, 0, NULL, 0,
                             W(L"t_a\x00e3g"), SQL_NTS,
                             NULL, 0));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 3), L"t_a\x00e3g", 6);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"DROP TABLE IF EXISTS t_a\x00e3g"),
                                 SQL_NTS));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlspecialcolumns)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_spec");
  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"CREATE TABLE t_spec (a\x00e3g INT PRIMARY KEY)"),
                         SQL_NTS));

  ok_stmt(hstmt1, SQLSpecialColumnsW(hstmt1, SQL_BEST_ROWID, NULL, 0, NULL, 0,
                             W(L"t_spec"), SQL_NTS, SQL_SCOPE_SESSION,
                             SQL_NULLABLE));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 2), L"a\x00e3g", 4);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_spec");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlforeignkeys)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  /** @todo re-enable this test when I_S based SQLForeignKeys is done. */
  if (mysql_min_version(hdbc, "5.1", 3))
    skip("can't test foreign keys with 5.1 or later yet");

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"DROP TABLE IF EXISTS t_fk_\x00e5, t_fk_\x00e3"),
                         SQL_NTS));
  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"CREATE TABLE t_fk_\x00e3 (a INT PRIMARY KEY) "
                           L"ENGINE=InnoDB"), SQL_NTS));
  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"CREATE TABLE t_fk_\x00e5 (b INT, parent_id INT,"
                         L"                       FOREIGN KEY (parent_id)"
                         L"                        REFERENCES"
                         L"                          t_fk_\x00e3(a)"
                         L"                        ON DELETE SET NULL)"
                         L" ENGINE=InnoDB"), SQL_NTS));

  ok_stmt(hstmt1, SQLForeignKeysW(hstmt1, NULL, 0, NULL, 0, NULL, 0, NULL, 0,
                                  NULL, 0, W(L"t_fk_\x00e5"), SQL_NTS));

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_wstr(my_fetch_wstr(hstmt1, wbuff, 3), L"t_fk_\x00e3", 7);
  is_wstr(my_fetch_wstr(hstmt1, wbuff, 4), L"a", 2);
  is_wstr(my_fetch_wstr(hstmt1, wbuff, 7), L"t_fk_\x00e5", 7);
  is_wstr(my_fetch_wstr(hstmt1, wbuff, 8), L"parent_id", 10);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"DROP TABLE IF EXISTS t_fk_\x00e5, t_fk_\x00e3"),
                         SQL_NTS));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlprimarykeys)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, W(L"DROP TABLE IF EXISTS t_a\x00e3g"),
                                 SQL_NTS));
  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"CREATE TABLE t_a\x00e3g (a INT PRIMARY KEY)"),
                         SQL_NTS));

  ok_stmt(hstmt1, SQLPrimaryKeysW(hstmt1, NULL, 0, NULL, 0,
                                  W(L"t_a\x00e3g"), SQL_NTS));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 3), L"t_a\x00e3g", 6);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, W(L"DROP TABLE IF EXISTS t_a\x00e3g"),
                                 SQL_NTS));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


DECLARE_TEST(sqlstatistics)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"DROP TABLE IF EXISTS t_a\x00e3g"),
                                 SQL_NTS));
  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"CREATE TABLE t_a\x00e3g (a INT PRIMARY KEY)"),
                         SQL_NTS));

  ok_stmt(hstmt1, SQLStatisticsW(hstmt1, NULL, 0, NULL, 0,
                                 W(L"t_a\x00e3g"), SQL_NTS,
                                 SQL_INDEX_UNIQUE, SQL_QUICK));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 3), L"t_a\x00e3g", 6);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1,
                                 W(L"DROP TABLE IF EXISTS t_a\x00e3g"),
                                 SQL_NTS));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


/**
 Bug #32161: SQLDescribeColW returns UTF-8 column as SQL_VARCHAR instead of
 SQL_WVARCHAR
*/
DECLARE_TEST(t_bug32161)
{
  HDBC hdbc1;
  HSTMT hstmt1;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];
  SQLSMALLINT nlen;
  SQLSMALLINT ctype;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnectW(hdbc1, WC(mydsn), SQL_NTS, WC(myuid), SQL_NTS,
                            WC(mypwd), SQL_NTS));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_bug32161");
  ok_sql(hstmt1, "CREATE TABLE t_bug32161 ("
                 "col1 varchar(32),"
                 "col2 char(32),"
                 "col3 tinytext,"
                 "col4 mediumtext,"
                 "col5 text,"
                 "col6 longtext"
                 ") DEFAULT CHARSET=utf8");

  /* Greek word PSARO - FISH */
  ok_stmt(hstmt1,
          SQLExecDirectW(hstmt1,
                         W(L"INSERT INTO t_bug32161 VALUES ("
                         L"\"\x03A8\x0391\x03A1\x039F 1\","
                         L"\"\x03A8\x0391\x03A1\x039F 2\","
                         L"\"\x03A8\x0391\x03A1\x039F 3\","
                         L"\"\x03A8\x0391\x03A1\x039F 4\","
                         L"\"\x03A8\x0391\x03A1\x039F 5\","
                         L"\"\x03A8\x0391\x03A1\x039F 6\")"),
                         SQL_NTS));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_sql(hstmt1, "SELECT * FROM t_bug32161");
  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 1), L"\x03A8\x0391\x03A1\x039F 1", 4);
  ok_stmt(hstmt1, SQLDescribeColW(hstmt1, 1, wbuff, MAX_ROW_DATA_LEN, &nlen,
                                  &ctype, NULL, NULL, NULL));
  is_num(ctype, SQL_WVARCHAR);

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 2), L"\x03A8\x0391\x03A1\x039F 2", 4);
  ok_stmt(hstmt1, SQLDescribeColW(hstmt1, 2, wbuff, MAX_ROW_DATA_LEN, &nlen,
                                  &ctype, NULL, NULL, NULL));
  is_num(ctype, SQL_WCHAR);

  /* All further calls of SQLDescribeColW should return SQL_WLONGVARCHAR */
  is_wstr(my_fetch_wstr(hstmt1, wbuff, 3), L"\x03A8\x0391\x03A1\x039F 3", 4);
  ok_stmt(hstmt1, SQLDescribeColW(hstmt1, 3, wbuff, MAX_ROW_DATA_LEN, &nlen,
                                  &ctype, NULL, NULL, NULL));
  is_num(ctype, SQL_WLONGVARCHAR);

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 4), L"\x03A8\x0391\x03A1\x039F 4", 4);
  ok_stmt(hstmt1, SQLDescribeColW(hstmt1, 4, wbuff, MAX_ROW_DATA_LEN, &nlen,
                                  &ctype, NULL, NULL, NULL));
  is_num(ctype, SQL_WLONGVARCHAR);

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 5), L"\x03A8\x0391\x03A1\x039F 5", 4);
  ok_stmt(hstmt1, SQLDescribeColW(hstmt1, 5, wbuff, MAX_ROW_DATA_LEN, &nlen,
                                  &ctype, NULL, NULL, NULL));
  is_num(ctype, SQL_WLONGVARCHAR);

  is_wstr(my_fetch_wstr(hstmt1, wbuff, 6), L"\x03A8\x0391\x03A1\x039F 6", 4);
  ok_stmt(hstmt1, SQLDescribeColW(hstmt1, 6, wbuff, MAX_ROW_DATA_LEN, &nlen,
                                  &ctype, NULL, NULL, NULL));
  is_num(ctype, SQL_WLONGVARCHAR);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_bug32161");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


/*
  Bug#34672 - Unable to insert surrogate pairs into or fetch surrogate pairs
              from unicode column
*/
DECLARE_TEST(t_bug34672)
{
  SQLWCHAR chars[3];
  SQLINTEGER inchars, i;
  SQLWCHAR result[3];
  SQLLEN reslen;

  if (sizeof(SQLWCHAR) == 2)
  {
    chars[0]= 0xd802;
    chars[1]= 0xdc60;
    chars[2]= 0;
    inchars= 2;
  }
  else
  {
    chars[0]= (SQLWCHAR) 0x10860;
    chars[1]= 0;
    inchars= 1;
  }

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_WCHAR,
                                  SQL_WCHAR, 0, 0, chars,
                                  inchars * sizeof(SQLWCHAR), NULL));

  if (mysql_min_version(hdbc, "6.0.4", 5))
  {
    ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR *) "select ?", SQL_NTS));
    ok_stmt(hstmt, SQLFetch(hstmt));
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_WCHAR, result,
                              sizeof(result), &reslen));
    is_num(result[2], 0);
    for (i= 0; i < inchars; ++i)
      is_num(result[i], chars[i]);
    is_num(reslen, inchars * sizeof(SQLWCHAR));
  }
  else
  {
    expect_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR *) "select ?", SQL_NTS),
                SQL_ERROR);
    return check_sqlstate(hstmt, "HY000");
  }

  return OK;
}

/*
Bug#28168 odbc, non 7-bit password, connection failed
*/
DECLARE_TEST(t_bug28168)
{
  SQLHANDLE hdbc1, hdbc2;
  SQLHANDLE hstmt1;

  wchar_t conn_in[512]= {0}, work_conn_in[512]= {0};
  wchar_t dummy[256]= {0};
  wchar_t *wstr;
  SQLWCHAR errmsgtxt[256]= {0}, sqlstate[6]= {0};

  SQLWCHAR *dropQuery= W(L"DROP USER IF EXISTS "
    L"'\x03A8\x0391\x03A1\x039F uid'@localhost");
  SQLWCHAR *dropQuery2= W(L"DROP USER IF EXISTS "
    L"'\x03A8\x0391\x03A1\x039F uid'@'%'");

  SQLWCHAR *createQuery= W(L"CREATE USER "
    L"'\x03A8\x0391\x03A1\x039F uid'@"
    L"localhost identified by "
    L"'\x03A8\x0391\x03A1\x039F pwd'");
  SQLWCHAR *createQuery2= W(L"CREATE USER "
    L"'\x03A8\x0391\x03A1\x039F uid'@"
    L"'%' identified by "
    L"'\x03A8\x0391\x03A1\x039F pwd'");
  SQLSMALLINT errmsglen;
  SQLINTEGER native_error= 0;

  /* Create tables to give permissions */
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug28168");
  ok_sql(hstmt, "CREATE TABLE t_bug28168 (x int)");

  *work_conn_in= L'\0';
  wcscat(work_conn_in, L"DSN=");
  mbstowcs(dummy, (char *)mydsn, sizeof(dummy)/sizeof(wchar_t));
  wcscat(work_conn_in, dummy);
  wcscat(work_conn_in, L";UID=");
  mbstowcs(dummy, (char *)myuid, sizeof(dummy)/sizeof(wchar_t));
  wcscat(work_conn_in, dummy);
  wcscat(work_conn_in, L";PWD=");
  mbstowcs(dummy, (char *)mypwd, sizeof(dummy)/sizeof(wchar_t));
  wcscat(work_conn_in, dummy);

  ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));

  /* Connect using UTF8 as transport to avoid server bug with user names */
  ok_con(hdbc1, SQLDriverConnectW(hdbc1, NULL, WL(work_conn_in,
                                  wcslen(work_conn_in)),
                                  (SQLSMALLINT)wcslen(work_conn_in), NULL, 0,
                                  0, SQL_DRIVER_NOPROMPT));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  /*
    Grant for localhost and for all other hosts if the test server
    runs remotely
  */
  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, dropQuery, SQL_NTS));
  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, dropQuery2, SQL_NTS));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, createQuery, SQL_NTS));
  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, createQuery2, SQL_NTS));

  *conn_in= L'\0';
  wcscat(conn_in, L"DRIVER=");
  mbstowcs(dummy, (char *)mydriver, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";UID=");
  wcscat(conn_in, L"{\x03A8\x0391\x03A1\x039F uid}");
  wcscat(conn_in, L";PWD=");
  wcscat(conn_in, L"{\x03A8\x0391\x03A1\x039F pwd}");
  wcscat(conn_in, L";DATABASE=");
  mbstowcs(dummy, (char *)mydb, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";SERVER=");
  mbstowcs(dummy, (char *)myserver, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  if (mysock != NULL)
  {
    wcscat(conn_in, L";SOCKET=");
    mbstowcs(dummy, (char *)mysock, sizeof(dummy)/sizeof(wchar_t));
    wcscat(conn_in, dummy);
  }

  if (myport)
  {
    char pbuff[20];
    sprintf(pbuff, ";PORT=%d", myport);
    mbstowcs(dummy, (char *)pbuff, sizeof(dummy)/sizeof(wchar_t));
    wcscat(conn_in, dummy);
  }

  ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc2));
  ok_con(hdbc2, SQLDriverConnectW(hdbc2, NULL, W(conn_in), SQL_NTS, NULL, 0,
    NULL, SQL_DRIVER_NOPROMPT));
  ok_con(hdbc2, SQLDisconnect(hdbc2));

  /* we change the password in the connection string to test the error msg */
  wstr= wcsstr(conn_in, L" pwd}") - 4;
  *wstr++= 'x';

  expect_dbc(hdbc2, SQLDriverConnectW(hdbc2, NULL, W(conn_in), SQL_NTS,
                  (SQLWCHAR*)NULL, 0, NULL, SQL_DRIVER_NOPROMPT), SQL_ERROR);

  ok_con(hdbc2, SQLGetDiagRecW(SQL_HANDLE_DBC, hdbc2, 1,
                               sqlstate, &native_error, errmsgtxt,
                               256 * sizeof(SQLWCHAR), &errmsglen));
  ok_con(hdbc2, SQLFreeHandle(SQL_HANDLE_DBC, hdbc2));

  /*
    The returned error message has to contain the substring
    with the username
  */
   wstr= sqlwchar_to_wchar_t(errmsgtxt);
   is(wcsstr(wstr,
             L"Access denied for user '\x03A8\x0391\x03A1\x039F uid'@") != NULL);
  ok_stmt(hstmt1,SQLExecDirectW(hstmt1,
    W(L"DROP USER "
    L"'\x03A8\x0391\x03A1\x039F uid'@localhost"), SQL_NTS));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeHandle(SQL_HANDLE_DBC, hdbc1));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug28168");

  return OK;
}


/*
  Bug#14363601 MY_ISSPACE CALLS CAUSE ODBC DRIVER CRASHES
*/
DECLARE_TEST(t_bug14363601)
{
  HENV henv1;
  HDBC hdbc1;
  HSTMT hstmt1;
  int i;
  wchar_t conn_in[512];
  wchar_t dummy[256];

  SQLWCHAR conn_out[1024];
  SQLSMALLINT conn_out_len;
  SQLLEN strlen_or_ind= 10;
  SQLINTEGER col_id= 1234;
  SQLWCHAR *col_vc= W(L"abcdefg\x30a1"), col_vc_res[30];
  double col_dc= 12345.678, col_dc_res= 0;
  unsigned char col_bc[10]= {0, 1, 2, 3, 4, 5, 6, 7, 8, 9}, col_bc_res[10];

  ok_env(henv1, SQLAllocEnv(&henv1));
  ok_env(henv1, SQLAllocConnect(henv1, &hdbc1));

  *conn_in= L'\0';
  wcscat(conn_in, L"DRIVER=");
  mbstowcs(dummy, (char *)mydriver, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";UID=");
  mbstowcs(dummy, (char *)myuid, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";PWD=");
  mbstowcs(dummy, (char *)mypwd, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";DATABASE=");
  mbstowcs(dummy, (char *)mydb, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  wcscat(conn_in, L";SERVER=");
  mbstowcs(dummy, (char *)myserver, sizeof(dummy)/sizeof(wchar_t));
  wcscat(conn_in, dummy);
  if (mysock != NULL)
  {
    wcscat(conn_in, L";SOCKET=");
    mbstowcs(dummy, (char *)mysock, sizeof(dummy)/sizeof(wchar_t));
    wcscat(conn_in, dummy);
  }
  if (myport)
  {
    char pbuff[20];
    sprintf(pbuff, ";PORT=%d", myport);
    mbstowcs(dummy, (char *)pbuff, sizeof(dummy)/sizeof(wchar_t));
    wcscat(conn_in, dummy);
  }
  wcscat(conn_in, L";CHARSET=utf16");

  ok_con(hdbc1, SQLDriverConnectW(hdbc1, NULL, WL(conn_in, wcslen(conn_in)),
                                  (SQLSMALLINT)wcslen(conn_in), conn_out,
                                  (SQLSMALLINT)(sizeof(conn_out)/sizeof(SQLWCHAR)),
                                  &conn_out_len, SQL_DRIVER_NOPROMPT));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS bug14363601");
  ok_sql(hstmt1, "CREATE TABLE bug14363601("
                 "id INT, vc VARCHAR(32),"
                 "dc DOUBLE, bc BLOB)CHARSET=UTF8");

  ok_stmt(hstmt1, SQLPrepareW(hstmt1,
        W(L"INSERT INTO bug14363601 (id, vc, dc, bc) "
                      L"VALUES (?, ?, ?, ?)"), SQL_NTS));

  /* Bind 1st INT param */
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &col_id, 0, NULL));

  /* Bind 2nd VARCHAR param */
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 2, SQL_PARAM_INPUT, SQL_C_WCHAR,
           SQL_WCHAR, 10, 0, col_vc,
                                  10*sizeof(SQLWCHAR), NULL));

  /* Bind 3rd DECIMAL param */
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 3, SQL_PARAM_INPUT, SQL_C_DOUBLE,
                                  SQL_DOUBLE, 0, 0, &col_dc, sizeof(col_dc),
                                  NULL));

  /* Bind 4th BLOB param */
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 4, SQL_PARAM_INPUT, SQL_C_BINARY,
                                  SQL_BINARY, (SQLULEN)sizeof(col_bc), 0, &col_bc,
                                  sizeof(col_bc), &strlen_or_ind));

  ok_stmt(hstmt1, SQLExecute(hstmt1));

  ok_stmt(hstmt1, SQLExecDirectW(hstmt1, W(L"SELECT * FROM bug14363601"),
                                 SQL_NTS));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_num(my_fetch_int(hstmt1, 1), col_id);

  ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_WCHAR, col_vc_res,
                             sizeof(col_vc_res), NULL));

  /* we want to compare SQLWCHAR instead of wchar_t */
  for (i= 0; i < 8; i++)
  {
    is(col_vc[i] == col_vc_res[i]);
  }

  ok_stmt(hstmt1, SQLGetData(hstmt1, 3, SQL_C_DOUBLE, &col_dc_res,
                             sizeof(col_dc_res), NULL));
  is(col_dc == col_dc_res);

  ok_stmt(hstmt1, SQLGetData(hstmt1, 4, SQL_C_BINARY, &col_bc_res,
                             sizeof(col_bc_res), NULL));

  /* check the binary buffer byte by byte */
  for (i= 0; i < sizeof(col_bc_res); i++)
  {
    is_num(col_bc[i], col_bc_res[i]);
  }

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS bug14363601");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));
  ok_env(henv1, SQLFreeEnv(henv1));

  /* OK if it has not crashed */
  return OK;
}


/*
  Bug#14838690: ODBC 5.2.2 CONNECTOR BROKEN FOR ALL SQL SERVERS
  OLDER THAN 5.5.3
  With utf8 byte string, Mysql server version < 5.5.3 failes with
  error message "Server does not support 4-byte encoded UTF8 characters."
*/
DECLARE_TEST(t_bug14838690)
{
  int i;
  SQLINTEGER col_id= 1234;
  SQLWCHAR *col_vc= W(L"abcdefg\x30a1"), col_vc_res[30];

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug14838690");
  ok_sql(hstmt, "CREATE TABLE bug14838690("
                 "id INT, vc VARCHAR(32)"
                 ")CHARSET=UTF8");

  ok_stmt(hstmt, SQLPrepareW(hstmt,
        W(L"INSERT INTO bug14838690 (id, vc) "
                      L"VALUES (?, ?)"), SQL_NTS));

  /* Bind 1st INT param */
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &col_id, 0, NULL));

  /* Bind 2nd VARCHAR param with utf8 byte string */
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_WCHAR,
                                   SQL_WVARCHAR, 10, 0, col_vc,
                                  10*sizeof(SQLWCHAR), NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLExecDirectW(hstmt, W(L"SELECT * FROM bug14838690"),
                                 SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 1), col_id);

  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_WCHAR, col_vc_res,
                             sizeof(col_vc_res), NULL));

  /* we want to compare SQLWCHAR instead of wchar_t */
  for (i= 0; i < 8; i++)
  {
    is(col_vc[i] == col_vc_res[i]);
  }

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug14838690");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_DROP));

  /* OK if it has not crashed */
  return OK;
}

DECLARE_TEST(t_bug28864788)
{
  SQLWCHAR *col_vb = W(L"ABCDEF"), col_vb_res[30];
  SQLLEN buf_len = 0;
  int i = 0;

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug28864788");
  ok_sql(hstmt, "CREATE TABLE bug28864788(id INT, vb VARBINARY(32))");
  ok_sql(hstmt, "INSERT INTO bug28864788(id, vb) VALUES (1, 0xabcdef)");

  ok_stmt(hstmt, SQLExecDirectW(hstmt, W(L"SELECT id, vb FROM bug28864788"),
    SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));

  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_WCHAR, col_vb_res,
          sizeof(col_vb_res), &buf_len));

  // is_num(12, buf_len);

  /* we want to compare SQLWCHAR instead of wchar_t */
  for (i = 0; i < 6; i++)
  {
    is(col_vb[i] == col_vb_res[i]);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "DROP TABLE IF EXISTS bug28864788");

  return OK;
}

BEGIN_TESTS
  ADD_TEST(sqlconnect)
  ADD_TEST_UNICODE(emojibug)
  ADD_TEST_UNICODE(sqlstatistics)
  ADD_TEST_UNICODE(sqlprepare)
  ADD_TEST(sqlprepare_ansi)
  ADD_TEST_UNICODE(sqlchar)
  ADD_TEST_UNICODE(sqldriverconnect)
  // ADD_TEST(sqlnativesql) TODO: Fix
  ADD_TEST_UNICODE(sqlcolattribute)
  ADD_TEST_UNICODE(sqldescribecol)
#ifndef USE_IODBC
  ADD_TEST(sqlsetcursorname)
  ADD_TEST(sqlgetcursorname)
  ADD_TEST(sqlgetconnectattr)
#endif
  ADD_TEST(sqlgetdiagrec)
  // ADD_TEST(sqlgetdiagfield) TODO: Fix
  ADD_TEST_UNICODE(sqlcolumns)
  ADD_TEST_UNICODE(sqltables)
  ADD_TEST_UNICODE(sqlspecialcolumns)
  ADD_TEST(sqlforeignkeys)
  ADD_TEST_UNICODE(sqlprimarykeys)
  ADD_TEST_UNICODE(t_bug32161)
  // ADD_TEST_UNICODE(t_bug34672) TODO: Fix
  // ADD_TEST_UNICODE(t_bug28168)
  // ADD_TEST_UNICODE(t_bug14363601) TODO: Fix
  // ADD_TEST_UNICODE(t_bug14838690) TODO: Fix
  ADD_TEST(t_bug28864788)
END_TESTS


RUN_TESTS


