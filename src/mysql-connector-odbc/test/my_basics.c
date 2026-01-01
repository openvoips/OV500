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

DECLARE_TEST(my_basics)
{
  SQLLEN nRowCount;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_basic, t_basic_2");

  /* create the table 'myodbc3_demo_result' */
  ok_sql(hstmt,
         "CREATE TABLE t_basic (id INT PRIMARY KEY, name VARCHAR(20))");

  /* insert 3 rows of data */
  ok_sql(hstmt, "INSERT INTO t_basic VALUES (1,'foo'),(2,'bar'),(3,'baz')");

  /* update second row */
  ok_sql(hstmt, "UPDATE t_basic SET name = 'bop' WHERE id = 2");

  /* get the rows affected by update statement */
  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(nRowCount, 1);

  /* delete third row */
  ok_sql(hstmt, "DELETE FROM t_basic WHERE id = 3");

  /* get the rows affected by delete statement */
  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(nRowCount, 1);

  /* alter the table 't_basic' to 't_basic_2' */
  ok_sql(hstmt,"ALTER TABLE t_basic RENAME t_basic_2");

  /*
    drop the table with the original table name, and it should
    return error saying 'table not found'
  */
  expect_sql(hstmt, "DROP TABLE t_basic", SQL_ERROR);

 /* now drop the table, which is altered..*/
  ok_sql(hstmt, "DROP TABLE t_basic_2");

  /* free the statement cursor */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


DECLARE_TEST(t_max_select)
{
  SQLINTEGER num;
  SQLCHAR    szData[20];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_max_select");

  ok_sql(hstmt, "CREATE TABLE t_max_select (a INT, b VARCHAR(30))");

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"INSERT INTO t_max_select VALUES (?,?)",
                            SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &num, 0, NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_CHAR, 0, 0, szData, sizeof(szData),
                                  NULL));

  for (num= 1; num <= 1000; num++)
  {
    sprintf((char *)szData, "MySQL%d", (int)num);
    ok_stmt(hstmt, SQLExecute(hstmt));
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_max_select");

  is_num(myrowcount(hstmt), 1000);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_max_select");

  return OK;
}


/* Simple function to do basic ops with MySQL */
DECLARE_TEST(t_basic)
{
  SQLINTEGER nRowCount= 0, nInData= 1, nOutData;
  SQLCHAR szOutData[31];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_myodbc");

  ok_sql(hstmt, "CREATE TABLE t_myodbc (a INT, b VARCHAR(30))");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* DIRECT INSERT */
  ok_sql(hstmt, "INSERT INTO t_myodbc VALUES (10, 'direct')");

  /* PREPARE INSERT */
  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)
                            "INSERT INTO t_myodbc VALUES (?, 'param')",
                            SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &nInData, 0, NULL));

  for (nInData= 20; nInData < 100; nInData= nInData+10)
  {
    ok_stmt(hstmt, SQLExecute(hstmt));
  }

  /* FREE THE PARAM BUFFERS */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* FETCH RESULT SET */
  ok_sql(hstmt, "SELECT * FROM t_myodbc");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nOutData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szOutData, sizeof(szOutData),
                            NULL));

  nInData= 10;
  while (SQLFetch(hstmt) == SQL_SUCCESS)
  {
    is_num(nOutData, nInData);
    is_str(szOutData, nRowCount++ ? "param" : "direct", 5);
    nInData += 10;
  }

  is_num(nRowCount, (nInData - 10) / 10);

  /* FREE THE OUTPUT BUFFERS */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_myodbc");

  return OK;
}


DECLARE_TEST(t_nativesql)
{
  SQLCHAR    out[128], in[]= "SELECT * FROM venu";
  SQLINTEGER len;

  ok_con(hdbc, SQLNativeSql(hdbc, in, SQL_NTS, out, sizeof(out), &len));
  is_num(len, (SQLINTEGER) sizeof(in) - 1);

  /*
   The second call is to make sure the first didn't screw up the stack.
   (Bug #28758)
  */

  ok_con(hdbc, SQLNativeSql(hdbc, in, SQL_NTS, out, sizeof(out), &len));
  is_num(len, (SQLINTEGER) sizeof(in) - 1);

  return OK;
}


/**
  This just tests that we can connect, disconnect and connect a few times
  without anything blowing up.
*/
DECLARE_TEST(t_reconnect)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  long i;

  for (i= 0; i < 10; i++)
  {
    is(OK == alloc_basic_handles(&henv1, &hdbc1, &hstmt1));

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  return OK;
}


/**
  Bug #19823: SQLGetConnectAttr with SQL_ATTR_CONNECTION_TIMEOUT works
  incorrectly
*/
DECLARE_TEST(t_bug19823)
{
  SQLHDBC hdbc1;
  SQLINTEGER timeout;

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));

  /*
   This first connect/disconnect is just to work around a bug in iODBC's
   implementation of SQLSetConnectAttr. It is fixed in 3.52.6, but
   Debian/Ubuntu still ships 3.52.5 as of 2007-12-06.
  */
  ok_con(hdbc1, SQLConnect(hdbc1, mydsn, SQL_NTS, myuid, SQL_NTS,
                           mypwd, SQL_NTS));
  ok_con(hdbc1, SQLDisconnect(hdbc1));

  ok_con(hdbc1, SQLSetConnectAttr(hdbc1, SQL_ATTR_LOGIN_TIMEOUT,
                                  (SQLPOINTER)17, 0));
  ok_con(hdbc1, SQLSetConnectAttr(hdbc1, SQL_ATTR_CONNECTION_TIMEOUT,
                                  (SQLPOINTER)12, 0));

  ok_con(hdbc1, SQLConnect(hdbc1, mydsn, SQL_NTS, myuid, SQL_NTS,
                           mypwd, SQL_NTS));

  ok_con(hdbc1, SQLGetConnectAttr(hdbc1, SQL_ATTR_LOGIN_TIMEOUT,
                                  &timeout, 0, NULL));
  is_num(timeout, 17);

#ifndef USE_IODBC
  /*
    SQL_ATTR_CONNECTION_TIMEOUT is always 0, because the driver does not
    support it and the driver just silently swallows any value given for it.
    Also, iODBC returns error for SQL_ATTR_CONNECTION_TIMEOUT requested if
    the connection is established.
  */
  ok_con(hdbc1, SQLGetConnectAttr(hdbc1, SQL_ATTR_CONNECTION_TIMEOUT,
                                  &timeout, 0, NULL));
  is_num(timeout, 0);
#endif

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


/**
 Test that we can connect with UTF8 as our charset, and things work right.
*/
DECLARE_TEST(charset_utf8)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR buff1[512], buff2[512];
  SQLLEN len;
  /**
   Bug #19345: Table column length multiplies on size session character set
  */
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug19345");
  ok_sql(hstmt, "CREATE TABLE t_bug19345 (a VARCHAR(10), b VARBINARY(10)) "
                "DEFAULT CHARSET = utf8mb4");
  ok_sql(hstmt, "INSERT INTO t_bug19345 VALUES ('abc','def')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL, "CHARSET=utf8"));

  ok_sql(hstmt1, "SELECT _latin1 0x73E36F207061756C6F");

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_str(my_fetch_str(hstmt1, buff2, 1), "s\xC3\xA3o paulo", 10);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLColumns(hstmt1, (SQLCHAR *)"test", SQL_NTS, NULL, 0,
                             (SQLCHAR *)"t_bug19345", SQL_NTS,
                             (SQLCHAR *)"%", 1));

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 7), 10);
  is_num(my_fetch_int(hstmt1, 8), 40);
  is_num(my_fetch_int(hstmt1, 16), 40);

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 7), 10);
  is_num(my_fetch_int(hstmt1, 8), 10);
  is_num(my_fetch_int(hstmt1, 16), 10);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Big5's 0xA4A4 becomes utf8's 0xE4B8AD */
  ok_sql(hstmt1, "SELECT _big5 0xA4A4");

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, buff1, 2, &len));
  is_num(buff1[0], 0xE4);
  is_num(len, 3);

  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, buff1, 2, &len));
  is_num(buff1[0], 0xB8);
  is_num(len, 2);

  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, buff1, 2, &len));
  is_num(buff1[0], 0xAD);
  is_num(len, 1);

  expect_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, buff1, 2, &len),
              SQL_NO_DATA_FOUND);

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  //ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug19345");

  return OK;
}


/**
 GBK is a fun character set -- it contains multibyte characters that can
 contain 0x5c ('\'). This causes escaping problems if the driver doesn't
 realize that we're using GBK. (Big5 is another character set with a similar
 issue.)
*/
DECLARE_TEST(charset_gbk)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR buff1[512];
  /*
    The fun here is that 0xbf5c is a valid GBK character, and we have 0x27
    as the second byte of an invalid GBK character. mysql_real_escape_string()
    handles this, as long as it knows the character set is GBK.
  */
  SQLCHAR str[]= "\xef\xbb\xbf\x27\xbf\x10";

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL,
                                        NULL, NULL, "CHARSET=gbk"));

  ok_stmt(hstmt1, SQLPrepare(hstmt1, (SQLCHAR *)"SELECT ?", SQL_NTS));
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                   SQL_CHAR, 0, 0, str, sizeof(str),
                                   NULL));

  ok_stmt(hstmt1, SQLExecute(hstmt1));

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_str(my_fetch_str(hstmt1, buff1, 1), str, sizeof(str));

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/**
  Bug #7445: MyODBC still doesn't support batch statements
*/
DECLARE_TEST(t_bug7445)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLLEN nRowCount;

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL,
                                        "MULTI_STATEMENTS=1"));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_bug7445");

  /* create the table 'myodbc3_demo_result' */
  ok_sql(hstmt1,
         "CREATE TABLE t_bug7445(name VARCHAR(20))");

  /* multi statement insert */
  ok_sql(hstmt1, "INSERT INTO t_bug7445 VALUES ('bogdan');"
                 "INSERT INTO t_bug7445 VALUES ('georg');"
                 "INSERT INTO t_bug7445 VALUES ('tonci');"
                 "INSERT INTO t_bug7445 VALUES ('jim')");

  ok_sql(hstmt1, "SELECT COUNT(*) FROM t_bug7445");

  /* get the rows affected by update statement */
  ok_stmt(hstmt1, SQLRowCount(hstmt1, &nRowCount));
  is_num(nRowCount, 1);

  ok_sql(hstmt, "DROP TABLE t_bug7445");

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/**
 Bug #30774: Username argument to SQLConnect used incorrectly
*/
DECLARE_TEST(t_bug30774)
{
  SQLHDBC hdbc1;
  SQLHSTMT hstmt1;
  SQLCHAR username[MAX_ROW_DATA_LEN+1]= {0};

  strcat((char *)username, (char *)myuid);
  strcat((char *)username, "!!!");

  /* No reason to use alloc_basic_handles */
  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  ok_con(hdbc1, SQLConnect(hdbc1, mydsn, SQL_NTS,
                           username, (SQLSMALLINT)strlen((char *)myuid),
                           mypwd, SQL_NTS));
  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  ok_sql(hstmt1, "SELECT USER()");
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  my_fetch_str(hstmt1, username, 1);
  printMessage("username: %s", username);
  is(!strstr((char *)username, "!!!"));

  expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


/**
  Bug #30840: FLAG_NO_PROMPT doesn't do anything
*/
DECLARE_TEST(t_bug30840)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  if (using_dm(hdbc))
    skip("test does not work with all driver managers");

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL,
                                        NULL, NULL, "NO_PROMPT=1"));

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


/**
  Bug #30983: SQL Statements limited to 64k
*/
DECLARE_TEST(t_bug30983)
{
  SQLCHAR buf[(80 * 1024) + 100]; /* ~80k */
  SQLCHAR *bufp = buf;
  SQLLEN buflen;
  int i, j;

  bufp+= sprintf((char *)bufp, "select '");

  /* fill 1k of each value */
  for (i= 0; i < 80; ++i)
    for (j= 0; j < 512; ++j, bufp += 2)
      sprintf((char *)bufp, "%02x", i);

  sprintf((char *)bufp, "' as val");

  ok_stmt(hstmt, SQLExecDirect(hstmt, buf, SQL_NTS));
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, buf, 0, &buflen));
  is_num(buflen, 80 * 1024);
  return OK;
}


/*
   Test the output string after calling SQLDriverConnect
   Note: Windows
   TODO fix this test create a comparable output string
*/
DECLARE_TEST(t_driverconnect_outstring)
{
  HDBC hdbc1;
  SQLCHAR conn[512], conn_out[512];
  SQLSMALLINT conn_out_len, exp_conn_out_len;

  sprintf((char *)conn, "DSN=%s;UID=%s;PWD=%s;CHARSET=utf8",
          mydsn, myuid, mypwd);
  if (mysock != NULL)
  {
    strcat((char *)conn, ";SOCKET=");
    strcat((char *)conn, (char *)mysock);
  }

  ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));

  ok_con(hdbc1, SQLDriverConnect(hdbc1, NULL, conn, SQL_NTS, conn_out,
                                 sizeof(conn_out), &conn_out_len,
                                 SQL_DRIVER_NOPROMPT));

  is_num(conn_out_len, strlen((char*)conn_out));

  /*
  TODO: enable when driver builds the connection string via options

  SQLCHAR exp_out[512];
  sprintf((char *)exp_out, "DSN=%s;UID=%s", mydsn, myuid);
  if (mypwd && *mypwd)
  {
    strcat((char *)exp_out, ";PWD=");
    strcat((char *)exp_out, (char *)mypwd);
  }
  strcat((char *)exp_out, ";DATABASE=");
  ok_con(hdbc1, SQLGetConnectAttr(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                  exp_out + strlen((char *)exp_out), 100,
                                  NULL));

  if (mysock != NULL)
  {
    strcat((char *)exp_out, ";SOCKET=");
    strcat((char *)exp_out, (char *)mysock);
  }
  strcat((char *)exp_out, ";PORT=3306;CHARSET=utf8");

  printMessage("Output connection string: %s", conn_out);
  printMessage("Expected output   string: %s", exp_out);
  // save proper length for later tests
  is_str(conn_out, exp_out, strlen((char *)conn_out));
  */
  exp_conn_out_len = conn_out_len;
  ok_con(hdbc1, SQLDisconnect(hdbc1));

  /* test truncation */
  conn_out_len= 999;
  expect_dbc(hdbc1, SQLDriverConnect(hdbc1, NULL, conn, SQL_NTS, conn_out,
                                     10, &conn_out_len,
                                     SQL_DRIVER_NOPROMPT),
             SQL_SUCCESS_WITH_INFO);

#ifndef USE_IODBC
  // iODBC sets the output length not larger than the buffer size
  // even when the driver reports otherwise.
  is(conn_out_len > 10);
#endif

  is_num(check_sqlstate_ex(hdbc1, SQL_HANDLE_DBC, "01004"), OK);
  ok_con(hdbc1, SQLDisconnect(hdbc1));

  /* test truncation on boundary */
  conn_out_len= 999;
  expect_dbc(hdbc1, SQLDriverConnect(hdbc1, NULL, conn, SQL_NTS, conn_out,
                                     exp_conn_out_len,
                                     &conn_out_len, SQL_DRIVER_NOPROMPT),
             SQL_SUCCESS_WITH_INFO);
#ifndef USE_IODBC
  is_num(conn_out_len, exp_conn_out_len);
#else
  // iODBC sets the output length reserving one byte for
  // the termination character.
  is_num(conn_out_len, exp_conn_out_len - 1);
#endif

  is_num(check_sqlstate_ex(hdbc1, SQL_HANDLE_DBC, "01004"), OK);
  ok_con(hdbc1, SQLDisconnect(hdbc1));

  ok_con(hdbc1, SQLFreeHandle(SQL_HANDLE_DBC, hdbc1));
  return OK;
}


DECLARE_TEST(setnames)
{
  expect_sql(hstmt, "SET NAMES utf8", SQL_ERROR);
  expect_sql(hstmt, "SeT NamES utf8", SQL_ERROR);
  expect_sql(hstmt, "   set names utf8", SQL_ERROR);
  expect_sql(hstmt, "	set names utf8", SQL_ERROR);
  return OK;
}


DECLARE_TEST(setnames_conn)
{
  HDBC hdbc1;

  ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));

  expect_dbc(hdbc1, get_connection(&hdbc1, NULL, NULL, NULL,
             NULL, "INITSTMT={set names utf8}"), SQL_ERROR);

  ok_con(hdbc1, SQLFreeHandle(SQL_HANDLE_DBC, hdbc1));

  return OK;
}


/**
 Bug #15601: SQLCancel does not work to stop a query on the database server
*/
#ifndef THREAD
DECLARE_TEST(sqlcancel)
{
  SQLLEN     pcbLength= SQL_LEN_DATA_AT_EXEC(0);

  ok_stmt(hstmt, SQLPrepare(hstmt, "select ?", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1,SQL_PARAM_INPUT,SQL_C_CHAR,
                          SQL_VARCHAR,0,0,(SQLPOINTER)1,0,&pcbLength));

  expect_stmt(hstmt, SQLExecute(hstmt), SQL_NEED_DATA);

  /* Without SQLCancel we would get "out of sequence" DM error */
  ok_stmt(hstmt, SQLCancel(hstmt));

  ok_stmt(hstmt, SQLPrepare(hstmt, "select 1", SQL_NTS));

  ok_stmt(hstmt, SQLExecute(hstmt));

  return OK;
}
#else

#ifdef WIN32
DWORD WINAPI cancel_in_one_second(LPVOID arg)
{
  HSTMT hstmt= (HSTMT)arg;

  Sleep(1000);

  if (SQLCancel(hstmt) != SQL_SUCCESS)
    printMessage("SQLCancel failed!");

  return 0;
}


DECLARE_TEST(sqlcancel)
{
  HANDLE thread;
  DWORD waitrc;

  thread= CreateThread(NULL, 0, cancel_in_one_second, hstmt, 0, NULL);

  /* SLEEP(n) returns 1 when it is killed. */
  ok_sql(hstmt, "SELECT SLEEP(5)");
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);

  waitrc= WaitForSingleObject(thread, 10000);
  is(!(waitrc == WAIT_TIMEOUT));

  return OK;
}
#else
void *cancel_in_one_second(void *arg)
{
  HSTMT *hstmt= arg;

  sleep(1);

  if (SQLCancel(hstmt) != SQL_SUCCESS)
    printMessage("SQLCancel failed!");

  return NULL;
}

#include <pthread.h>

DECLARE_TEST(sqlcancel)
{
  pthread_t thread;

#ifdef IODBC_BUG_SQLCANCEL_FIXED
  pthread_create(&thread, NULL, cancel_in_one_second, hstmt);

  /* SLEEP(n) returns 1 when it is killed. */
  ok_sql(hstmt, "SELECT SLEEP(10)");
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);

  pthread_join(thread, NULL);
#endif // ifdef IODBC_BUG_SQLCANCEL_FIXED

  return OK;
}
#endif  // ifdef WIN32
#endif  // ifndef THREAD


/**
Bug #32014: MyODBC / ADO Unable to open record set using dynamic cursor
*/
DECLARE_TEST(t_bug32014)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLUINTEGER info;
  long        i=0;
  SQLSMALLINT value_len;

  long flags[]= { 0,
                  (131072L << 4)   /*FLAG_FORWARD_CURSOR*/,
                  32               /*FLAG_DYNAMIC_CURSOR*/,
                  (131072L << 4) | 32,
                  0 };

  long expectedInfo[]= { SQL_SO_FORWARD_ONLY|SQL_SO_STATIC,
                         SQL_SO_FORWARD_ONLY,
                         SQL_SO_FORWARD_ONLY|SQL_SO_STATIC|SQL_SO_DYNAMIC,
                         SQL_SO_FORWARD_ONLY };

  long expectedCurType[][4]= {
      {SQL_CURSOR_FORWARD_ONLY, SQL_CURSOR_STATIC,        SQL_CURSOR_STATIC,          SQL_CURSOR_STATIC},
      {SQL_CURSOR_FORWARD_ONLY, SQL_CURSOR_FORWARD_ONLY,  SQL_CURSOR_FORWARD_ONLY,    SQL_CURSOR_FORWARD_ONLY},
      {SQL_CURSOR_FORWARD_ONLY, SQL_CURSOR_STATIC,        SQL_CURSOR_DYNAMIC,         SQL_CURSOR_STATIC},
      {SQL_CURSOR_FORWARD_ONLY, SQL_CURSOR_FORWARD_ONLY,  SQL_CURSOR_FORWARD_ONLY,    SQL_CURSOR_FORWARD_ONLY}};

  do
  {
    SET_DSN_OPTION(flags[i]);
    is(OK == alloc_basic_handles(&henv1, &hdbc1, &hstmt1));

    printMessage("checking %d (%d)", i, flags[i]);

    /*Checking that correct info is returned*/

    ok_stmt(hstmt1, SQLGetInfo(hdbc1, SQL_SCROLL_OPTIONS,
            (SQLPOINTER) &info, sizeof(long), &value_len));
    is_num(info, expectedInfo[i]);

    /*Checking that correct cursor type is set*/

    ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_CURSOR_TYPE
            , SQL_CURSOR_FORWARD_ONLY ));
    ok_stmt(hstmt1, SQLGetStmtOption(hstmt1, SQL_CURSOR_TYPE,
            (SQLPOINTER) &info));
    is_num(info, expectedCurType[i][SQL_CURSOR_FORWARD_ONLY]);

    ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_CURSOR_TYPE,
            SQL_CURSOR_KEYSET_DRIVEN ));
    ok_stmt(hstmt1, SQLGetStmtOption(hstmt1, SQL_CURSOR_TYPE,
            (SQLPOINTER) &info));
    is_num(info, expectedCurType[i][SQL_CURSOR_KEYSET_DRIVEN]);

    ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_CURSOR_TYPE,
            SQL_CURSOR_DYNAMIC ));
    ok_stmt(hstmt1, SQLGetStmtOption(hstmt1, SQL_CURSOR_TYPE,
            (SQLPOINTER) &info));
    is_num(info, expectedCurType[i][SQL_CURSOR_DYNAMIC]);

    ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_CURSOR_TYPE,
            SQL_CURSOR_STATIC ));
    ok_stmt(hstmt1, SQLGetStmtOption(hstmt1, SQL_CURSOR_TYPE,
            (SQLPOINTER) &info));
    is_num(info, expectedCurType[i][SQL_CURSOR_STATIC]);

    free_basic_handles(&henv1, &hdbc1, &hstmt1);

  } while (flags[++i]);

  SET_DSN_OPTION(0);

  return OK;
}


/*
  Bug #10128 Error in evaluating simple mathematical expression
  ADO calls SQLNativeSql with a NULL pointer for the result length,
  but passes a non-NULL result buffer.
*/
DECLARE_TEST(t_bug10128)
{
  SQLCHAR *query= (SQLCHAR *) "select 1,2,3,4";
  SQLCHAR nativesql[1000];
  SQLINTEGER nativelen;
  SQLINTEGER querylen= (SQLINTEGER) strlen((char *)query);

  ok_con(hdbc, SQLNativeSql(hdbc, query, SQL_NTS, NULL, 0, &nativelen));
  is_num(nativelen, querylen);

  ok_con(hdbc, SQLNativeSql(hdbc, query, SQL_NTS, nativesql, 1000, NULL));
  is_str(nativesql, query, querylen + 1);

  return OK;
}


/**
 Bug #32727: Unable to abort distributed transactions enlisted in MSDTC
*/
DECLARE_TEST(t_bug32727)
{
#ifndef USE_IODBC
  /* iODBC does not like that call with 5.3 */
  is_num(SQLSetConnectAttr(hdbc, SQL_ATTR_ENLIST_IN_DTC,
                       (SQLPOINTER)1, SQL_IS_UINTEGER), SQL_ERROR);
#endif
  return OK;
}


/*
  Bug #28820: Varchar Field length is reported as larger than actual
*/
DECLARE_TEST(t_bug28820)
{
  SQLULEN length;
  SQLCHAR dummy[20];
  SQLSMALLINT i;

  ok_sql(hstmt, "drop table if exists t_bug28820");
  ok_sql(hstmt, "create table t_bug28820 ("
                "x varchar(90) character set latin1,"
                "y varchar(90) character set big5,"
                "z varchar(90) character set utf8)");

  ok_sql(hstmt, "select x,y,z from t_bug28820");

  for (i= 0; i < 3; ++i)
  {
    length= 0;
    ok_stmt(hstmt, SQLDescribeCol(hstmt, i+1, dummy, sizeof(dummy), NULL,
                                  NULL, &length, NULL, NULL));
    is_num(length, 90);
  }

  ok_sql(hstmt, "drop table if exists t_bug28820");
  return OK;
}


/*
  Bug #31959 - Allows dirty reading with SQL_TXN_READ_COMMITTED
               isolation through ODBC
*/
DECLARE_TEST(t_bug31959)
{
  SQLCHAR level[50] = "uninitialized";
  SQLINTEGER i;
  SQLINTEGER levelid[] = {SQL_TXN_SERIALIZABLE, SQL_TXN_REPEATABLE_READ,
                          SQL_TXN_READ_COMMITTED, SQL_TXN_READ_UNCOMMITTED};
  SQLCHAR *levelname[] = {(SQLCHAR *)"SERIALIZABLE",
                          (SQLCHAR *)"REPEATABLE-READ",
                          (SQLCHAR *)"READ-COMMITTED",
                          (SQLCHAR *)"READ-UNCOMMITTED"};

  if (mysql_min_version(hdbc, "8.0", 3))
    ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"select @@transaction_isolation", SQL_NTS));
  else
    ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"select @@tx_isolation", SQL_NTS));


  /* check all 4 valid isolation levels */
  for(i = 3; i >= 0; --i)
  {
    size_t p = (size_t)levelid[i];
    ok_con(hdbc, SQLSetConnectAttr(hdbc, SQL_ATTR_TXN_ISOLATION,
                                   (SQLPOINTER)p, 0));
    ok_stmt(hstmt, SQLExecute(hstmt));
    ok_stmt(hstmt, SQLFetch(hstmt));
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, level, 50, NULL));
    is_str(level, levelname[i], strlen((char *)levelname[i]));
    printMessage("Level = %s\n", level);
    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  }

  /* check invalid value (and corresponding SQL state) */
  is_num(SQLSetConnectAttr(hdbc, SQL_ATTR_TXN_ISOLATION, (SQLPOINTER)999, 0),
     SQL_ERROR);
  {
  SQLCHAR     sql_state[6];
  SQLINTEGER  err_code= 0;
  SQLCHAR     err_msg[SQL_MAX_MESSAGE_LENGTH]= {0};
  SQLSMALLINT err_len= 0;

  memset(err_msg, 'C', SQL_MAX_MESSAGE_LENGTH);
  SQLGetDiagRec(SQL_HANDLE_DBC, hdbc, 1, sql_state, &err_code, err_msg,
                SQL_MAX_MESSAGE_LENGTH - 1, &err_len);

  is_str(sql_state, (SQLCHAR *)"HY024", 5);
  }

  return OK;
}


/*
  Bug #41256 - NULL parameters don't work correctly with ADO.
  The null indicator pointer can be set separately through the
  descriptor field. This wasn't being checked separately.
*/
DECLARE_TEST(t_bug41256)
{
  SQLHANDLE apd;
  SQLINTEGER val= 40;
  SQLLEN vallen= 19283;
  SQLLEN ind= SQL_NULL_DATA;
  SQLLEN reslen= 40;
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_PARAM_DESC,
                                &apd, SQL_IS_POINTER, NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_INTEGER,
                                  SQL_C_LONG, 0, 0, &val, 0, &vallen));
  ok_desc(apd, SQLSetDescField(apd, 1, SQL_DESC_INDICATOR_PTR,
                               &ind, SQL_IS_POINTER));
  ok_sql(hstmt, "select ?");
  val= 80;
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_LONG, &val, 0, &reslen));
  is_num(SQL_NULL_DATA, reslen);
  is_num(80, val);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  return OK;
}


DECLARE_TEST(t_bug44971)
{
/*  ok_sql(hstmt, "drop database if exists bug44971");
  ok_sql(hstmt, "create database bug44971");
  ok_con(hdbc, SQLSetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, "bug44971xxx", 8));
  ok_sql(hstmt, "drop database if exists bug44971");*/
  return OK;
}


DECLARE_TEST(t_bug48603)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLINTEGER timeout, interactive, diff= 1000;
  SQLCHAR conn[512], query[53];

  ok_sql(hstmt, "select @@wait_timeout, @@interactive_timeout");
  ok_stmt(hstmt,SQLFetch(hstmt));

  timeout=      my_fetch_int(hstmt, 1);
  interactive=  my_fetch_int(hstmt, 2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  if (timeout == interactive)
  {
    printMessage("Changing interactive timeout globally as it is equal to wait_timeout");
    /* Changing globally interactive timeout to be able to test
       if INTERACTIVE option works */
    sprintf((char *)query, "set GLOBAL interactive_timeout=%d", timeout + diff);

    if (!SQL_SUCCEEDED(SQLExecDirect(hstmt, query, SQL_NTS)))
    {
      printMessage("Don't have rights to change interactive timeout globally - so can't really test if option INTERACTIVE works");
      // Let the testcase does not fail
      diff= 0;
      //return FAIL;
    }

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  }
  else
  {
    printMessage("Interactive: %d, wait: %d", interactive, timeout);
    diff= interactive - timeout;
  }

  /* INITSTMT={set @@wait_timeout=%d} */
  sprintf((char *)conn, "CHARSET=utf8;INITSTMT=set @@interactive_timeout=%d;" \
                        "INTERACTIVE=1", timeout+diff);
  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL,
                                        NULL, NULL, conn));


  ok_sql(hstmt1, "select @@wait_timeout");
  ok_stmt(hstmt1,SQLFetch(hstmt1));

  {
    SQLINTEGER cur_timeout= my_fetch_int(hstmt1, 1);

    free_basic_handles(&henv1, &hdbc1, &hstmt1);

    if (timeout == interactive)
    {
      /* setting global interactive timeout back if we changed it */
      sprintf((char *)query, "set GLOBAL interactive_timeout=%d", timeout);
      ok_stmt(hstmt, SQLExecDirect(hstmt, query, SQL_NTS));
    }

    is_num(timeout + diff, cur_timeout);
  }

  return OK;
}


/*
  Bug#45378 - spaces in connection string aren't removed
*/
DECLARE_TEST(t_bug45378)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR buff1[512], buff2[512];

  sprintf((char *)buff1, " {%s} ", myuid);
  sprintf((char *)buff2, " %s ", mypwd);

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        buff1, buff2, NULL, NULL));

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


/*
  Bug#63844 - Crash in SQLSetConnectAttr
*/
DECLARE_TEST(t_bug63844)
{
  SQLHDBC hdbc1;
  SQLCHAR *DatabaseName = mydb;

  /*
    We are not going to use alloc_basic_handles() for a special purpose:
    SQLSetConnectAttr() is to be called before the connection is made
  */
  ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));

  ok_con(hdbc1, SQLSetConnectAttr(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                  DatabaseName, (SQLINTEGER)strlen(DatabaseName)));

  /* The driver crashes here on getting connected */
  ok_con(hdbc1, get_connection(&hdbc1, NULL, NULL, NULL, NULL, NULL));

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  return OK;
}


/*
  Bug#52996 - DSN connection parameters override those specified in the
  connection string
*/
DECLARE_TEST(t_bug52996)
{
  int res= OK;

  /* TODO: remove #ifdef _WIN32 when Linux and MacOS setup is released */
#ifdef _WIN32
  size_t i, len;
  SQLCHAR attrs[8192];
  SQLCHAR drv[128];
  SQLLEN row_count= 0;
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug52996");
  ok_sql(hstmt, "CREATE TABLE bug52996 (id int primary key, c1 int)");
  ok_sql(hstmt, "INSERT INTO bug52996 (id, c1) VALUES "\
                 "(1,1),(2,2),(3,3)");

  /*
    Use ';' as separator because sprintf doesn't work after '\0'
    The last attribute in the list must end with ';'
  */

  sprintf((char*)attrs, "DSN=bug52996dsn;SERVER=%s;USER=%s;PASSWORD=%s;"
                          "DATABASE=%s;FOUND_ROWS=1;",
                          myserver, myuid, mypwd, mydb);

  len= strlen(attrs);

  /* replacing ';' by '\0' */
  for (i= 0; i < len; ++i)
  {
    if (attrs[i] == ';')
      attrs[i]= '\0';
  }

  /* Adding the extra string termination to get \0\0 */
  attrs[i]= '\0';

  if (mydriver[0] == '{')
  {
    /* We need to remove {} in the driver name or it will not register */
    len= strlen(mydriver);
    memcpy(drv, mydriver+1, sizeof(SQLCHAR)*(len-2));
    drv[len-2]= '\0';
  }
  else
  {
    memcpy(drv, mydriver, sizeof(SQLCHAR)*len);
    drv[len]= '\0';
  }

  /*
    Trying to remove the DSN if it is left from the previous run,
    no need to check the result
  */
  SQLConfigDataSource(NULL, ODBC_REMOVE_DSN, drv, "DSN=bug52996dsn\0\0");

  /* Create the DSN */
  ok_install(SQLConfigDataSource(NULL, ODBC_ADD_DSN, drv, attrs));

  /* Connect using the new DSN and override FOUND_ROWS option in DSN */
  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
                               "bug52996dsn",
                               NULL, NULL, NULL, "FOUND_ROWS=0"));

  /* Check the affected tows */
  ok_sql(hstmt1, "UPDATE bug52996 SET c1=3 WHERE id < 4 ");
  ok_stmt(hstmt1, SQLRowCount(hstmt1, &row_count));

  if(row_count != 2)
  {
    /* We don't want to return immediately before clean up DSN and table */
    res= FAIL;
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  ok_sql(hstmt, "DROP TABLE bug52996");

  ok_install(SQLConfigDataSource(NULL, ODBC_REMOVE_DSN, drv, "DSN=bug52996dsn\0\0"));
#endif

  return res;
}


DECLARE_TEST(t_tls_opts)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR buf[1024] = { 0 };
  SQLLEN len = 0;

  ok_sql(hstmt, "SHOW VARIABLES LIKE 'tls_version'");
  ok_stmt(hstmt, SQLFetch(hstmt));
  my_fetch_str(hstmt, buf, 2);
  printf("TLS Versions supported by the server: %s\n", buf);
  is(SQL_NO_DATA == SQLFetch(hstmt));

#define VERSION_COUNT 2

  // supported TLS versions, starting from the highest one.
  // Tests below assume that ver[] covers all versions supported
  // by both server and connector and ver[0] is the highest supported one.
  //
  // Note: this means that these tests will fail when tested against
  // a server that supports a new version beyond ver[0].

  char *ver[VERSION_COUNT] = { "TLSv1.3", "TLSv1.2" };

  // Corresponding NO_TLS_X options

  char *opts[VERSION_COUNT] = { "NO_TLS_1_3=1;", "NO_TLS_1_2=1;" };

  // Info about versions actually supported by server and client

  unsigned supported_versions = 0;

  for (unsigned i = 0; i < VERSION_COUNT; ++i)
  {
      if (strstr(buf, ver[i]) != NULL)
      {
        char connstr[512] = "SOCKET=;SSLMODE=REQUIRED;TLS-VERSIONS=";
        strncat(connstr, ver[i], sizeof(connstr) - strlen(connstr));
        printf("Connection options: %s\n", connstr);

        // The version is marked as supported only when client
        // supports it as well as the server.
        if (OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
          NULL, NULL, NULL, connstr))
        {
          supported_versions |= (1u << i);
        }
        free_basic_handles(&henv1, &hdbc1, &hstmt1);
      }
  }

  // Test disabling each of supported versions, the last iteration is
  // testing a scenario where nothing is disabled.

  for (int i = 0; i < VERSION_COUNT+1; ++i)
  {
    unsigned ver_bit = 1u << i;

    // Skip testing NO_TLS_X options if server does not support any good TLS
    // version - in this case only the last iteration is performed.

    if (i < VERSION_COUNT && !supported_versions)
      continue;

    // Note: Empty SOCKET option forces use of TCP connection even for
    // localhost

    char connstr[512] = "SOCKET=;";
    if (i < VERSION_COUNT)
      strncat(connstr, opts[i], sizeof(connstr)-9);
    printf("Connection options: %s\n", connstr);

    int ret = alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
      NULL, NULL, NULL, connstr);

    // We accept a failed connection only if server does not support any
    // other versions except the one being disabled.

    if (OK != ret)
    {
      is(0 == (supported_versions & ~ver_bit));
      continue;
    }

    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_version'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), &len));

    printf("SSL Version: %s\n\n", buf);

    // Note: Check that actual version does not equal the disabled one

    if (i < VERSION_COUNT)
    {
      is(strcmp(buf, ver[i]) != 0);
    }
    else
    {
      /*
        If no version was disabled, check that the highest available one
        was selected.

        But first check that server supports at lest one of the TLS
        versions, bacause otherwise if connection was accepted then something
        is wrong: either old server picked old TLS version that we do not
        support or new server picked a new TLS version we are not aware of
        and this test should be updated.
      */

      is(supported_versions);

      int j = 0;

      for (; j < VERSION_COUNT; ++j)
        if (supported_versions & (1u << j))
          break;

      is(j < VERSION_COUNT);
      is(strcmp(buf, ver[j]) == 0);
    }

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }


  // Test disabling all supported TLS versions.

  {
    /*
      Disabling all TLS versions should lead to error. This is true also for
      old servers that do not support any of the versions in ver[]. However,
      a new server that supports a version beyond ver[0] might accept the
      connection and this test will fail. In that case we need to update the
      test to cover the new TLS version.
    */

    char connstr[512] = "SOCKET=;";
    for (int i = 0; i < VERSION_COUNT; ++i)
      strncat(connstr, opts[i], sizeof(connstr));
    printf("Connection options: %s\n", connstr);

    is(FAIL == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
      NULL, NULL, NULL, connstr));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  return OK;
}


DECLARE_TEST(t_ssl_mode)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR buf[1024] = { 0 };
  for(int i = 0; i < 2; ++i)
  {
    if(i == 0)
    {
      is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                            NULL, NULL, NULL, "SSLMODE=DISABLED"));
    }
    else
    {
      is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                            NULL, NULL, NULL, "ssl-mode=DISABLED"));
    }

    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_cipher'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
    is(buf[0] == '\0');

    free_basic_handles(&henv1, &hdbc1, &hstmt1);

    is(OK == alloc_basic_handles_with_opt(
         &henv1, &hdbc1, &hstmt1, NULL,
         NULL, NULL, NULL,
         i == 0 ? "SSLMODE=REQUIRED" : "ssl-mode=REQUIRED"));


    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_cipher'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
    is(buf[0] != '\0');

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  return OK;
}

DECLARE_TEST(t_ssl_align)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR buf[1024] = { 0 };

  char* ssl_opts[] =
  {"ssl-ca", "ssl-capath", "ssl-cert", "ssl-cipher", "ssl-key",
   "SSLCA","SSLCAPATH", "SSLCERT", "SSLCIPHER", "SSLKEY"};

  for(unsigned int i = 0; i < sizeof(ssl_opts)/sizeof(char*); ++i)
  {
    snprintf(buf, sizeof(buf),
             i%2 ? "SSLMODE=DISABLED;%s=foo" : "ssl-mode=DISABLED;%s=foo",
             ssl_opts[i]);

    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                            NULL, NULL, NULL, buf));


    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_cipher'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
    is(buf[0] == '\0');

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  //Defined twice is not an error

  {

    is(OK == alloc_basic_handles_with_opt(
         &henv1, &hdbc1, &hstmt1, NULL,
         NULL, NULL, NULL, "SSLMODE=DISABLED;SSLMODE=REQUIRED;"));


    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_cipher'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
    is(buf[0] != '\0');

    free_basic_handles(&henv1, &hdbc1, &hstmt1);


    is(OK == alloc_basic_handles_with_opt(
         &henv1, &hdbc1, &hstmt1, NULL,
         NULL, NULL, NULL, "SSLMODE=REQUIRED;SSLMODE=DISABLED;"));


    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_cipher'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
    is(buf[0] == '\0');

    free_basic_handles(&henv1, &hdbc1, &hstmt1);

    is(OK == alloc_basic_handles_with_opt(
         &henv1, &hdbc1, &hstmt1, NULL,
         NULL, NULL, NULL, "ssl-mode=DISABLED;ssl-mode=REQUIRED;"));


    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_cipher'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
    is(buf[0] != '\0');

    free_basic_handles(&henv1, &hdbc1, &hstmt1);


    is(OK == alloc_basic_handles_with_opt(
         &henv1, &hdbc1, &hstmt1, NULL,
         NULL, NULL, NULL, "ssl-mode=REQUIRED;ssl-mode=DISABLED;"));


    /* Check the affected tows */
    ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_cipher'");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
    is(buf[0] == '\0');

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  return OK;
}

const char* make_tls_str(const char *str, char *out, char *expected, char ssl_disabled)
{
  if (ssl_disabled)
  {
    sprintf(out, "SSLMODE=DISABLED;TLS-VERSIONS=%s", str);
    *expected = 0;
    return str;
  }

  char ver[16];
  int num;
  do
  {
    ver[0] = 0;
    num = 0;
    // Skip a leading comma
    if (*str == ',')
      ++str;

    while (str && *str && *str != ',')
    {
      ver[num] = *str;
      ++num;
      ++str;
    }
    ver[num] = 0;
    // Skip TLSv1 (len < 6) and TLSv1.1
  } while (*str && (strlen((const char*)ver) < 6 ||
                    strncmp((const char*)ver, "TLSv1.1", 7) == 0));

  sprintf(out, "SSLMODE=REQUIRED;TLS-VERSIONS=%s;NO_TLS_%c_%c=1",
          ver, ver[num - 3], ver[num - 1]);
  memcpy(expected, ver, num + 1);

  if (*str == ',')
    return ++str;

  return NULL;
}

DECLARE_TEST(t_tls_versions)
{
  ok_sql(hstmt, "SHOW VARIABLES LIKE 'tls_version'");
  ok_stmt(hstmt, SQLFetch(hstmt));
  char tls_versions_list[255] = { 0 };

  my_fetch_str(hstmt, tls_versions_list, 2);
  SQLFetch(hstmt);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  char ssl_disabled = 1;
  char con_str[255];
  char exp_result[16];
  const char *list = tls_versions_list;
  while((list = make_tls_str(list, con_str, exp_result, ssl_disabled)))
  {
    SQLCHAR buf[1024] = { 0 };
    ssl_disabled = 0;
    int connect_res = alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                          NULL, NULL, NULL, con_str);
    if (connect_res == SQL_SUCCESS)
    {
      ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_version'");

      ok_stmt(hstmt1, SQLFetch(hstmt1));
      ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
      is_str(exp_result, buf, strlen(exp_result));
    }
    else
    {
      is(OK == check_errmsg_ex(hdbc1, SQL_HANDLE_DBC,
        "SSL connection error: No valid TLS version available"))
    }

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  return OK;
}

DECLARE_TEST(t_bug107307)
{
  SQLRETURN rc;
  double value = 0;
  SQLLEN ind = 0;

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL,
    NULL, NULL, "NO_CACHE=1");

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_bug107307");
  ok_sql(hstmt1, "CREATE TABLE t_bug107307 (num0 DOUBLE)");

  double expected[] = {12.3, -12.3, 15.7, -15.7, 3.5, -3.5, 0, 0, 10};
  ok_sql(hstmt1, "INSERT INTO t_bug107307 VALUES (12.3), (-12.3), (15.7),"
    "(-15.7), (3.5), (-3.5), (0), (NULL), (10)");

  ok_stmt(hstmt1, SQLPrepare(hstmt1, "SELECT * FROM t_bug107307", SQL_NTS));
  ok_stmt(hstmt, SQLExecute(hstmt1));

  ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_DOUBLE, (SQLPOINTER)&value, 0, &ind));

  for (int i = 0; ; i++)
  {
    rc = SQLFetch(hstmt1);
    if (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
      if (ind <= 0)
      {
        printf("Record %d: %s\n", i + 1, "NULL");
      }
      else
      {
        printf("Record %d: %f\n", i + 1, value);
      }
      is(value == expected[i]);
    }
    else
    {
      if (rc != SQL_NO_DATA)
      {
        printf("Error\n");
      }
      else
      {
        break;
      }
    }
  }

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_bug107307");
  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}

/*
  WL #14880 - New ODBC connection options to specify CRL and
  CRL Path for SSL
*/
DECLARE_TEST(t_ssl_crl)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  char *conn_strs[][3] = {
    // Connection string, return value (1 - error, 0 - success), Encryption
    {"ssl-mode=REQUIRED;ssl-crl=non-existing.pem", "\1", "\0"},
    {"ssl-mode=REQUIRED;ssl-crlpath=invalid-path", "\0", "\1"},
    {"ssl-mode=DISABLED;ssl-crl=non-existing.pem", "\0", "\0"},
    {"ssl-mode=DISABLED;ssl-crlpath=invalid-path", "\0", "\0"}
  };

  for (int i = 0; i < 4; ++i)
  {
    SQLCHAR buf[1024] = { 0 };
    int connect_res = alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
      NULL, NULL, NULL, conn_strs[i][0]);

    is_num(connect_res, conn_strs[i][1][0]);

    if (connect_res == SQL_SUCCESS)
    {
      // If connected check if encryption is used.
      ok_sql(hstmt1, "SHOW STATUS LIKE 'Ssl_version'");
      ok_stmt(hstmt1, SQLFetch(hstmt1));
      ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_CHAR, buf, sizeof(buf), NULL));
      if(conn_strs[i][2][0])
      {
        // Encryption is used
        is(strlen(buf) > 0);
      }
      else
      {
        // Encryption is not used
        is(strlen(buf) == 0);
      }
    }
    else
    {
      // If not connected check the error message
      is(OK == check_errmsg_ex(hdbc1, SQL_HANDLE_DBC,
        "SSL connection error: SSL_CTX_set_default_verify_paths failed"));
    }

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  return OK;
}

/*
  Bug #34786939 - Adding ODBC ANSI 64-bit System DSN connection causes
  error 01004
*/
DECLARE_TEST(t_bug34786939_out_trunc)
{
  SQLHDBC hdbc1 = NULL;
  ok_env(henv, SQLAllocConnect(henv, &hdbc1));
  SQLCHAR *connstr = make_conn_str(NULL, NULL, NULL, NULL, NULL, 0);
  SQLSMALLINT out_str_len = -1;

  // Give NULL for out string, but non-NULL for out string length.
  SQLRETURN rc = SQLDriverConnect(hdbc1, NULL, connstr, SQL_NTS,
    NULL, 0, &out_str_len, SQL_DRIVER_NOPROMPT);
  // It must not return anything except SQL_SUCCESS.
  is_num(rc, SQL_SUCCESS);

  SQLDisconnect(hdbc1);

  SQLCHAR out_str[10] = { 0 };

  // Give a very small buffer out string and non-NULL for out string length.
  rc = SQLDriverConnect(hdbc1, NULL, connstr, SQL_NTS,
    out_str, 10, &out_str_len, SQL_DRIVER_NOPROMPT);
  // It must not return anything except SQL_SUCCESS_WITH_INFO.
  is_num(rc, SQL_SUCCESS_WITH_INFO);

  SQLDisconnect(hdbc1);
  SQLFreeConnect(hdbc1);

  return OK;
}

BEGIN_TESTS
  ADD_TEST(t_driverconnect_outstring)
  ADD_TEST(t_bug34786939_out_trunc)
  ADD_TEST(t_ssl_crl)
  ADD_TEST(t_bug107307)
  ADD_TEST(t_tls_versions)
  ADD_TEST(t_tls_opts)
  ADD_TEST(t_ssl_mode)
  ADD_TEST(my_basics)
  ADD_TEST(t_max_select)
  ADD_TEST(t_basic)
  ADD_TEST(t_nativesql)
#ifndef NO_DRIVERMANAGER
  ADD_TEST(t_reconnect)
  ADD_TEST(t_bug19823)
#endif
  ADD_TEST(charset_utf8)
  // ADD_TEST(charset_gbk) TODO: fix
  ADD_TEST(t_bug7445)
  ADD_TEST(t_bug30774)
  ADD_TEST(t_bug30840)
  ADD_TEST(t_bug30983)
  ADD_TEST(setnames)
  ADD_TEST(setnames_conn)
  ADD_TEST(sqlcancel)
  ADD_TEST(t_bug32014)
  ADD_TEST(t_bug10128)
  ADD_TEST(t_bug32727)
  ADD_TEST(t_bug28820)
  ADD_TEST(t_bug31959)
  ADD_TEST(t_bug41256)
  ADD_TEST(t_bug44971)
  ADD_TEST(t_bug48603)
  ADD_TEST(t_bug45378)
  ADD_TEST(t_bug63844)
  ADD_TEST(t_bug52996)
  ADD_TEST(t_ssl_align)
  END_TESTS


RUN_TESTS
