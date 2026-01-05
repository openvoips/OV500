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

/*
 * Tests for Bug #13766 - transforming dates to/from zero/invalid dates.
 * This is a separate file so the DSN OPTION doesn't affect other tests.
 */

#include "odbctap.h"

/*
 * Test the FLAG_ZERO_DATE_TO_MIN transforms the date correctly.
 * NULL test is in my_datetime.c:t_bug12520()
 */
DECLARE_TEST(bug13766_result)
{
#undef EL_NUM
#define EL_NUM 6
  int i;
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
                               NULL, "FLAG_ZERO_DATE_TO_MIN=1");
  SQL_DATE_STRUCT xdate[EL_NUM];
  SQL_TIMESTAMP_STRUCT xts[EL_NUM];
  SQLLEN isNull[12];

  memset(xdate, 0, sizeof(SQL_DATE_STRUCT)*EL_NUM);
  memset(xts, 0, sizeof(SQL_TIMESTAMP_STRUCT)*EL_NUM);

  ok_sql(hstmt1, "SET @@SESSION.SQL_MODE=''");

  ok_sql(hstmt1, "select cast('0000-00-00' as date), "
      "cast('0000-10-00' as date), "
      "cast('0000-00-10' as date), "
      "cast('2007-00-00' as date), "
      "cast('2007-10-00' as date), "
      "cast('2007-00-10' as date), "
      "cast('0000-00-00 00:00:00' as datetime), "
      "cast('0000-10-00 00:00:00' as datetime), "
      "cast('0000-00-10 00:00:00' as datetime), "
      "cast('2007-00-00 00:00:00' as datetime), "
      "cast('2007-10-00 00:00:00' as datetime), "
      "cast('2007-00-10 00:00:00' as datetime) ");
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  for (i= 0; i < EL_NUM; ++i)
  {
    ok_stmt(hstmt1, SQLGetData(hstmt1, i+1, SQL_C_TYPE_DATE,
                              &xdate[i], 0, &isNull[i]));
  }
  for (i= 0; i < EL_NUM; ++i)
  {
    ok_stmt(hstmt1, SQLGetData(hstmt1, EL_NUM+i+1, SQL_C_TYPE_TIMESTAMP,
                               &xts[i], 0, &isNull[EL_NUM+i]));
  }

  i= 0;
  /* 0000-00-00 */
  is_num(xdate[i].year, 0);
  is_num(xdate[i].month, 1);
  is_num(xdate[i].day, 1);
  is_num(xts[i].year, 0);
  is_num(xts[i].month, 1);
  is_num(xts[i].day, 1);
  i++;

  /*
    the server is not consistent in how it handles 0000-xx-xx, it changed
    within the 5.0 and 5.1 series
  */
  if (isNull[i] == SQL_NULL_DATA)
  {
    is_num(isNull[i], SQL_NULL_DATA);
    is_num(isNull[6+i], SQL_NULL_DATA);
    i++;
    is_num(isNull[i], SQL_NULL_DATA);
    is_num(isNull[6+i], SQL_NULL_DATA);
    i++;
  }
  else
  {
    /* 0000-10-00 */
    is_num(xdate[i].year, 0);
    is_num(xdate[i].month, 10);
    is_num(xdate[i].day, 1);
    is_num(xts[i].year, 0);
    is_num(xts[i].month, 10);
    is_num(xts[i].day, 1);
    i++;
    /* 0000-00-10 */
    is_num(xdate[i].year, 0);
    is_num(xdate[i].month, 1);
    is_num(xdate[i].day, 10);
    is_num(xts[i].year, 0);
    is_num(xts[i].month, 1);
    is_num(xts[i].day, 10);
    i++;
  }

  /* 2007-00-00 */
  is_num(xdate[i].year, 2007);
  is_num(xdate[i].month, 1);
  is_num(xdate[i].day, 1);
  is_num(xts[i].year, 2007);
  is_num(xts[i].month, 1);
  is_num(xts[i].day, 1);
  i++;
  /* 2007-10-00 */
  is_num(xdate[i].year, 2007);
  is_num(xdate[i].month, 10);
  is_num(xdate[i].day, 1);
  is_num(xts[i].year, 2007);
  is_num(xts[i].month, 10);
  is_num(xts[i].day, 1);
  i++;
  /* 2007-00-10 */
  is_num(xdate[i].year, 2007);
  is_num(xdate[i].month, 1);
  is_num(xdate[i].day, 10);
  is_num(xts[i].year, 2007);
  is_num(xts[i].month, 1);
  is_num(xts[i].day, 10);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


/*
 * Bug #13766 - Test the FLAG_MIN_DATE_TO_ZERO transforms the date
 * correctly.
 */
DECLARE_TEST(bug13766_query)
{
  SQL_DATE_STRUCT xdate;
  SQL_TIMESTAMP_STRUCT xts;
  char result[50];

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
                               NULL, "FLAG_MIN_DATE_TO_ZERO=1");

  ok_sql(hstmt1, "SET @@SESSION.SQL_MODE=''");

  ok_stmt(hstmt1, SQLPrepare(hstmt1, (SQLCHAR *)"select ?", SQL_NTS));
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_TYPE_DATE,
                                  SQL_TYPE_DATE, 0, 0, &xdate, 0, NULL));

  /* Test that we fix min date -> zero date */
  xdate.year= 0;
  xdate.month= 1;
  xdate.day= 1;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-00-00", 10);

  /* we dont touch dates that are not 0000-01-01 */

  xdate.year= 0;
  xdate.month= 0;
  xdate.day= 0;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-00-00", 10);

  xdate.year= 1;
  xdate.month= 0;
  xdate.day= 0;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0001-00-00", 10);

  xdate.year= 0;
  xdate.month= 1;
  xdate.day= 0;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-01-00", 10);

  xdate.year= 0;
  xdate.month= 0;
  xdate.day= 1;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-00-01", 10);

  /* same stuff for timestamps */

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_RESET_PARAMS));
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_TYPE_TIMESTAMP,
                                  SQL_TYPE_TIMESTAMP, 0, 0, &xts, 0, NULL));
  xts.hour = 19;
  xts.minute = 22;
  xts.second = 25;

  xts.year= 0;
  xts.month= 1;
  xts.day= 1;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-00-00 19:22:25", 19);

  xts.year= 0;
  xts.month= 0;
  xts.day= 0;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-00-00 19:22:25", 19);

  xts.year= 1;
  xts.month= 0;
  xts.day= 0;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0001-00-00 19:22:25", 19);

  xts.year= 0;
  xts.month= 1;
  xts.day= 0;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-01-00 19:22:25", 19);

  xts.year= 0;
  xts.month= 0;
  xts.day= 1;
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_CHAR, result, 50, NULL));
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  is_str(result, "0000-00-01 19:22:25", 19);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


BEGIN_TESTS
  ADD_TEST(bug13766_result)
  ADD_TEST(bug13766_query)
END_TESTS


/* FLAG_ZERO_DATE_TO_MIN & FLAG_MIN_DATE_TO_ZERO */
SET_DSN_OPTION((1 << 24) | (1 << 25));


RUN_TESTS

