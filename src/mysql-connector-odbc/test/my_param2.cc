// Copyright (c) 2021, 2024, Oracle and/or its affiliates.
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

#include <string>
#include <vector>
#include <fstream>
#include <iostream>
#include <sstream>
#include <cstdio>
#include <map>
#include <thread>
#include <stdlib.h>

#include "odbc_util.h"

#ifdef _WIN32
#pragma warning (disable : 4996)
// warning C4996: 'mkdir': The POSIX name for this item is deprecated. Instead, use the ISO C and C++ conformant name: _mkdir.

#pragma warning (disable : 4566)
// warning C4566: character represented by universal-character-name
// '\u....' cannot be represented in the current code page

#include <direct.h>
#else
#include <sys/stat.h>
#endif

/*
  Bug 32552965
  MYSQL ODBC CONNECTOR *8.0.23* 'MALFORMED COMMUNICATION PACKET' WITH ADO.NET DATA
*/
DECLARE_TEST(t_bug32552965)
{
  try
  {
    odbc::table tab(hstmt, "t_bug32552965",
      "col1 MEDIUMBLOB NOT NULL, col2 TINYINT DEFAULT NULL");
    odbc::stmt_prepare(hstmt, "INSERT INTO " + tab.table_name + " VALUES(?,?)");

    odbc::xbuf buff(20000);
    buff.setval('Q', buff.size - 1);
    SQLLEN len1 = buff.size - 1;
    SQLLEN len2 = -1; // INSERT NULL !!!
    SQLINTEGER int_data = 0;

    ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
          SQL_C_CHAR, SQL_LONGVARCHAR, 0, 0, buff, buff.size, &len1));
    ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT,
          SQL_C_LONG, SQL_INTEGER, 0, 0, &int_data, 0, &len2));
    ok_stmt(hstmt, SQLExecute(hstmt));
  }
  ENDCATCH;
}


/*
  Bug 27499789
  ODBC PARAMETER NO_CACHE LEAD TO FUNCTION CALL ERROR NOT RETURN
*/
DECLARE_TEST(t_bug27499789)
{
  for (int no_cache = 0; no_cache < 2; ++no_cache)
  try
  {
    odbc::xstring opts = ";NO_CACHE=" + odbc::xstring(no_cache);
    odbc::connection con(nullptr, nullptr, nullptr, nullptr, opts);

    SQLHSTMT hstmt = con.hstmt;
    odbc::stmt_prepare(hstmt, "SELECT 1 + 9223372036854775807;");
    is_err(SQLExecute(hstmt));
  }
  ENDCATCH;
}


/*
  Bug #32763378
  SQLSETPOS(SQL_DELETE) DOES NOT PRODUCE DIAGNOSTIC FOR DUPLICATE KEY
*/
DECLARE_TEST(t_bug32763378)
{
  try
  {
    odbc::table tab(hstmt, "bug32763378", "A VARCHAR(10) UNIQUE, B VARCHAR(10)");
    odbc::stmt_prepare(hstmt, "INSERT INTO " + tab.table_name + " VALUES(?,?)");

    SQLLEN rows_fetched = 0;
    SQLCHAR buffers[2][10];
    SQLLEN lengths[2] = { 0, 0 };
    memset(buffers, 0, sizeof(buffers));
    odbc::xbuf buff(SQL_MAX_MESSAGE_LENGTH);
    buff.setval("A");
    ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
	    SQL_VARCHAR, 0, 0, buff, buff.size, NULL));
    ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
	    SQL_VARCHAR, 0, 0, buff, buff.size, NULL));
    odbc::stmt_execute(hstmt);
    buff.setval("B");
    odbc::stmt_execute(hstmt);
    odbc::stmt_reset(hstmt);
    odbc::stmt_close(hstmt);

    odbc::stmt_prepare(hstmt, "SELECT A, B FROM " + tab.table_name + " WHERE A='A' FOR UPDATE");
    ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, buffers[0], 10, &lengths[0]));
    ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, buffers[1], 10, &lengths[1]));
    odbc::stmt_execute(hstmt);
    ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
    buffers[0][0] = 'B';
    is_err(SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

    SQLCHAR sqlstate[6] = { 0, 0, 0, 0, 0, 0 };
    SQLINTEGER  native_error = 0;
    SQLSMALLINT length = 0;
    ok_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate, &native_error,
                     buff, (SQLSMALLINT)buff.size - 1, &length));
    std::cout << "Expected error message: " << buff.get_str() << std::endl;
  }
  ENDCATCH;
}


/*
  Test that the row is buffered before the actual data is requested by
  SQLGetData.
*/
DECLARE_TEST(t_bug30578291_in_param)
{
  for (int no_cache = 0; no_cache < 2; ++no_cache)
  try
  {
    odbc::xstring opts = ";NO_CACHE=" + odbc::xstring(no_cache);
    odbc::connection con(nullptr, nullptr, nullptr, nullptr, opts);
    SQLHSTMT hstmt = con.hstmt;
    odbc::table tab(hstmt, "bug30578291", "id int, A TEXT");
    odbc::sql(hstmt, "INSERT INTO " + tab.table_name +
      " VALUES(1,'ABC'),(2,'DEF'),(3,'GHI')");

    odbc::stmt_prepare(hstmt, "SELECT * FROM " + tab.table_name + " WHERE 'Z' > ?");

    SQLLEN rows_fetched = 0;
    SQLCHAR buffers[2][10];
    SQLLEN lengths[2] = { 0, 0 };
    memset(buffers, 0, sizeof(buffers));
    odbc::xbuf buff(128);
    buff.setval("A");
    ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
	    SQL_VARCHAR, 0, 0, buff, buff.size, NULL));
    odbc::stmt_execute(hstmt);
    while(SQLFetch(hstmt) == SQL_SUCCESS)
    {
      buff.setval(0);
      int col1 = my_fetch_int(hstmt, 1);
      const char *col2 = my_fetch_str(hstmt, buff, 2);
      std::cout << "[id: " << col1 << "] [A: " << col2 << "]\n";
    }
  }
  ENDCATCH;
}


/*
  Get INOUT parameter from stored procedure with NO_CACHE=1
*/
DECLARE_TEST(t_bug30578291_inout_param)
{
  for (int no_cache = 0; no_cache < 2; ++no_cache)
  try
  {
    odbc::xstring opts = ";NO_CACHE=" + odbc::xstring(no_cache);
    odbc::connection con(nullptr, nullptr, nullptr, nullptr, opts);
    SQLHSTMT hstmt = con.hstmt;
    odbc::procedure proc(hstmt, "proc_bug30578291",
      "(INOUT p1 INT, OUT p2 VARCHAR(32))"
      "BEGIN "
      "  SELECT p1 + 200; "
      "  SET p1 = 400, p2 = 'String from MySQL'; "
      "END");
    odbc::stmt_prepare(hstmt, "CALL " + proc.proc_name + "(?,?)");

    SQLLEN len1 = 0, len2 = 0;
    int p1 = 100;
    odbc::xbuf p2(32);

    ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT,
            SQL_C_LONG, SQL_INTEGER, 0, 0, &p1, 0, &len1));
    ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_OUTPUT,
            SQL_C_CHAR, SQL_CHAR, 0, 0, p2, p2.size, &len2));

    odbc::stmt_execute(hstmt);
    ok_stmt(hstmt, SQLFetch(hstmt));
    is_num(300, my_fetch_int(hstmt, 1));
    is_no_data(SQLFetch(hstmt));
    ok_stmt(hstmt, SQLMoreResults(hstmt));
    is_num(400, p1);
    odbc::xstring str = "String from MySQL";
    is_str((char*)str, (char*)p2, str.length());

    p2.setval(0);
    odbc::stmt_close(hstmt);
    odbc::stmt_execute(hstmt);

    ok_stmt(hstmt, SQLFetch(hstmt));
    is_num(600, my_fetch_int(hstmt, 1));
    is_no_data(SQLFetch(hstmt));
    ok_stmt(hstmt, SQLMoreResults(hstmt));
    is_num(400, p1);
    is_str((char*)str, (char*)p2, str.length());

  }
  ENDCATCH;

}

/*
  Get rows with parametrized query when NO_CACHE=1
*/
DECLARE_TEST(t_bug30578291_just_rows)
{
  for (int no_cache = 0; no_cache < 2; ++no_cache)
  try
  {
    odbc::xstring opts = ";NO_CACHE=" + odbc::xstring(no_cache);
    odbc::connection con(nullptr, nullptr, nullptr, nullptr, opts);
    SQLHSTMT hstmt = con.hstmt;
    odbc::table tab(hstmt, "tab30578291", "id int, vc varchar(32)");
    tab.insert("(100, 'MySQL 100'),(200, 'MySQL 200'),(300, 'MySQL 300'),(400, 'MySQL 400'),(500, 'MySQL 500')");
    odbc::stmt_prepare(hstmt, "SELECT * FROM " + tab.table_name + " WHERE id > ? ORDER BY id");
    SQLLEN len1 = 0;
    int p1 = -1;
    ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT,
            SQL_C_LONG, SQL_INTEGER, 0, 0, &p1, 0, &len1));

    odbc::stmt_execute(hstmt);
    for (int i = 100; i < 600; i += 100)
    {
      ok_stmt(hstmt, SQLFetch(hstmt));
      odbc::xstring str = "MySQL " + odbc::xstring(i);
      odbc::xbuf buf(32);
      is_num(i, my_fetch_int(hstmt, 1));
      is_str((char*)str, my_fetch_str(hstmt, buf, 2), str.length());
    }
    is_no_data(SQLFetch(hstmt));


  }
  ENDCATCH;
}

int cancel_func(SQLHSTMT hstmt)
{
  std::cout << "[Entered cancel_func()]\n" << std::flush;
  SQLRETURN sqlReturn = SQLCancel(hstmt);
  std::string outstr = "SQLCancel() result " + std::to_string(sqlReturn) + "]\n";
  std::cout << outstr << std::flush;
  return 0;
}

int select_func(SQLHSTMT hstmt)
 {
  std::cout << "[Entered select_func()]\n" << std::flush;
  SQLUINTEGER buffer[10000] = { 0 };
  std::cout << "[Before SQLBindCol()]\n" << std::flush;
  SQLBindCol(hstmt, 1, SQL_C_ULONG, &buffer[0], 1, NULL);
  std::cout << "[After SQLBindCol()]\n" << std::flush;
  std::cout << "[Before SQLFetchScroll()]\n" << std::flush;
  SQLRETURN rc2 = SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0);
  std::string outstr = "[SQLFetchScroll() result " + std::to_string(rc2) + "]\n";
  std::cout << outstr << std::flush;
  return 0;
}


DECLARE_TEST(t_stmt_thread)
{
  {
    odbc::HSTMT hstmt2(hdbc);
    odbc::table tab(hstmt2, "stmt_crash",
      "id int primary key auto_increment, data int");

    std::string data;
    data.reserve(100000);
    data.append("(NULL,444)");
    for (int i = 0; i < 3000; ++i)
    {
      data.append(",(NULL,111),(NULL,222),(NULL,333)");
    }
    tab.insert(data);

    ok_stmt(hstmt, SQLSetStmtAttr(hstmt2, SQL_ATTR_ROW_ARRAY_SIZE,
      (SQLPOINTER)9000, 0));

    for (int i = 0; i < 30; ++i)
    {
      std::cout << "Iteration " << i << std::endl;
      odbc::sql(hstmt2, "SELECT data FROM stmt_crash");
      std::cout << "SELECT is executed..." << std::endl;

      std::this_thread::sleep_for(std::chrono::milliseconds(10));
      std::thread select_thd(select_func, (SQLHSTMT)hstmt2);
      std::thread cancel_thd(cancel_func, (SQLHSTMT)hstmt2);
      std::thread select_thd2(select_func, (SQLHSTMT)hstmt2);

      select_thd.join();
      cancel_thd.join();
      select_thd2.join();
      std::cout << "Threads finished..." << std::endl;
      ok_stmt(hstmt2, SQLFreeStmt(hstmt2, SQL_CLOSE));
      std::cout << "Statement closed..." << std::endl;
    }
  }
  return OK;
}

#ifndef _WIN32
#include <stdio.h>
#include <execinfo.h>
#include <signal.h>
#include <stdlib.h>
#include <unistd.h>

void sig_handler(int sig) {
  void* array[10];
  size_t size;

  // get void*'s for all entries on the stack
  size = backtrace(array, 10);
  // print out all the frames to stderr
  fprintf(stderr, "Error: signal %d:\n", sig);
  backtrace_symbols_fd(array, size, STDERR_FILENO);
  exit(1);
}
#endif

BEGIN_TESTS
  // TODO: enable test when the problem is fixed
  // ADD_TEST(t_stmt_thread)
  ADD_TEST(t_bug30578291_inout_param)
  ADD_TEST(t_bug30578291_in_param)
  ADD_TEST(t_bug30578291_just_rows)
  ADD_TEST(t_bug27499789)
  ADD_TEST(t_bug32763378)
  ADD_TEST(t_bug32552965)

  END_TESTS

#ifndef _WIN32
  signal(SIGSEGV, sig_handler);
#endif

RUN_TESTS
