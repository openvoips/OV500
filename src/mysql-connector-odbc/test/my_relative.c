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

/* Testing SQL_FETCH_RELATIVE with row_set_size as 10 */
DECLARE_TEST(t_relative)
{
  SQLUINTEGER i, iarray[15];
  SQLULEN nrows, index;
  SQLCHAR name[21];

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative");
  ok_sql(hstmt, "CREATE TABLE t_relative (id INT, name CHAR(20))");

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"INSERT INTO t_relative VALUES (?,?)",
                            SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_ULONG,
                                  SQL_INTEGER, 0, 0, &i, 0, NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_CHAR, 20, 0, name, 20, NULL));

  for (i= 1; i <= 50; i++)
  {
    sprintf((char *)name, "my%d", i);
    ok_stmt(hstmt, SQLExecute(hstmt));
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* set row size as 10 */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)10, 0));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &nrows, 0));

  ok_sql(hstmt, "SELECT * FROM t_relative");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_ULONG, &iarray, 0, NULL));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0)); /* 1-10 */
  is_num(nrows, 10);

  for (index= 1; index <= nrows; index++)
    is_num(iarray[index - 1], index);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0)); /* 10-20 */
  is_num(nrows, 10);

  for (index= 1; index <= nrows; index++)
    is_num(iarray[index - 1], index + 10);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 0)); /* 1-10 */
  is_num(nrows, 10);

  for (index= 1; index <= nrows; index++)
    is_num(iarray[index - 1], index);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1)); /* 2-11 */
  is_num(nrows, 10);

  for (index= 1; index <= nrows; index++)
    is_num(iarray[index - 1], index + 1);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1)); /* 1-10 */
  is_num(nrows, 10);

  for (index= 1; index <= nrows; index++)
    is_num(iarray[index - 1], index);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 0)); /* 1-10 */
  is_num(nrows, 10);

  for (index= 1; index <= nrows; index++)
    is_num(iarray[index - 1], index);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND); /* BOF */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1)); /* 1-10 */
  is_num(nrows, 10);

  for (index= 1; index <= nrows; index++)
    is_num(iarray[index - 1], index);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* reset row size */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative");

  return OK;
}


/* Testing SQL_FETCH_RELATIVE with row_set_size as 1 */
DECLARE_TEST(t_relative1)
{
  SQLULEN nrows;
  SQLUINTEGER i;
  const SQLUINTEGER max_rows= 10;

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative1");
  ok_sql(hstmt, "CREATE TABLE t_relative1 (id INT)");

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"INSERT INTO t_relative1 VALUES (?)",
                            SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_ULONG,
                                  SQL_INTEGER, 0, 0, &i, 0, NULL));

  for (i= 1; i <= max_rows; i++)
  {
    ok_stmt(hstmt, SQLExecute(hstmt));
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* set row_size as 1 */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &nrows, 0));

  ok_sql(hstmt, "SELECT * FROM t_relative1");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &i, 0, NULL));

  /* row 1 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(i, 1);

  /* Before start */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND);

  /* jump to last row */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, max_rows));
  is_num(i, max_rows);

  /* jump to last row+1 */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1),
              SQL_NO_DATA_FOUND);

  /* goto first row */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1));
  is_num(i, 1);

  /* before start */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND);

  /* goto fifth  row */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 5));
  is_num(i, 5);

  /* goto after end */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, max_rows),
              SQL_NO_DATA_FOUND);

  /* the scenarios from ODBC spec */

  /* CASE 1 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1));
  is_num(i, 1);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND);

  /* BeforeStart AND FetchOffset <= 0 */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -20),
              SQL_NO_DATA_FOUND);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 0),
              SQL_NO_DATA_FOUND);

  /* case 1: Before start AND FetchOffset > 0 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1));
  is_num(i, 1);

  /* CASE 2 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_LAST, 1));
  is_num(i, max_rows);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1),
              SQL_NO_DATA_FOUND);

  /* After end AND FetchOffset >= 0 */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 10),
              SQL_NO_DATA_FOUND);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 20),
              SQL_NO_DATA_FOUND);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1),
              SQL_NO_DATA_FOUND);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 0),
              SQL_NO_DATA_FOUND);

  /* After end AND FetchOffset < 0 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1));
  is_num(i, max_rows);

  /* CASE 3 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1));
  is_num(i, 1);

  /* CurrRowsetStart = 1 AND FetchOffset < 0 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 0));
  is_num(i, 1);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND);

  /* CASE 4 */
  /* CurrRowsetStart > 1 AND CurrRowsetStart + FetchOffset < 1 AND
     | FetchOffset | > RowsetSize
  */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1));
  is_num(i, 1);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 3));
  is_num(i, 4);

  /* the following call satisfies 4 > 1 AND (3-4) < 1 AND |-4| > 1 */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -4),
              SQL_NO_DATA_FOUND);

  /* CASE 5 */
  /* 1 <= CurrRowsetStart + FetchOffset <= LastResultRow */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1));
  is_num(i, 1);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 5));
  is_num(i, 6);

  /* 1 <= 6-2 <= 10 */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -2));
  is_num(i, 4);

  /* CASE 6 */
  /*  CurrRowsetStart > 1 AND CurrRowsetStart + FetchOffset < 1 AND
      | FetchOffset | <= RowsetSize
   */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1));
  is_num(i, 1);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 3));
  is_num(i, 4);

  /* 4 >1 AND 4-4 <1 AND |-4| <=10 */
  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -4),
              SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative1");

  return OK;
}


/* Testing SQL_FETCH_RELATIVE with row_set_size as 2 */
DECLARE_TEST(t_relative2)
{
  SQLRETURN rc;
  SQLULEN nrows;
  SQLUINTEGER i, iarray[15];
  const SQLUINTEGER max_rows=10;

    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative2");

  ok_sql(hstmt, "create table t_relative2(id int)");

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *) "insert into t_relative2 values(?)",
                            SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT, SQL_C_ULONG,
                          SQL_INTEGER,0,0,&i,0,NULL);
    mystmt(hstmt,rc);

    for ( i = 1; i <= max_rows; i++ )
    {
        rc = SQLExecute(hstmt);
        mystmt(hstmt,rc);
    }

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    rc = SQLEndTran(SQL_HANDLE_DBC,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    /* set row_size as 2 */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)2,0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROWS_FETCHED_PTR,&nrows,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from t_relative2");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&iarray,0,NULL);
    mystmt(hstmt,rc);

    /* row 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);/* 1 */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);


    /* Before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* jump to last row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,max_rows);/* last row */
    mystmt(hstmt,rc);
    my_assert(nrows == 1);
    my_assert(iarray[0]==max_rows);

    /* jump to last row+1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* after last */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto first row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* 1 */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    /* before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto fifth  row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,5);/* 5 */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==5);
    my_assert(iarray[1]==6);

    /* goto after end */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,max_rows);/* after last */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /*
       the scenarios from ODBC spec
    */

    /* CASE 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* 1 */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* BeforeStart AND FetchOffset <= 0 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-20);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,0);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* case 1: Before start AND FetchOffset > 0 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* 1 */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    /* CASE 2 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_LAST,1);/* last row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==max_rows-1);
    my_assert(iarray[1]==max_rows);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* last row */
    mystmt(hstmt,rc);
    my_assert(nrows == 1);
    my_assert(iarray[0]==max_rows);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* after last row */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* After end AND FetchOffset >= 0 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,10);/* after end */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,20);/* after end */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* after end */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,0);/* after end */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* After end AND FetchOffset < 0 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* last row */
    mystmt(hstmt,rc);
    my_assert(nrows == 1);
    my_assert(iarray[0]==max_rows);


    /* CASE 3 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    /* CurrRowsetStart = 1 AND FetchOffset < 0 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,0);/* first row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 4 */
    /* CurrRowsetStart > 1 AND CurrRowsetStart + FetchOffset < 1 AND
       | FetchOffset | > RowsetSize
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,3);/* fourth row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==4);
    my_assert(iarray[1]==5);

    /* the following call satisfies 4 > 1 AND (3-4) < 1 AND |-4| > 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-4);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 5 */
    /* 1 <= CurrRowsetStart + FetchOffset <= LastResultRow */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,5);/* sixth row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==6);
    my_assert(iarray[1]==7);

    /* 1 <= 6-2 <= 10 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-2);/* fourth row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==4);
    my_assert(iarray[1]==5);

    /* CASE 6 */
    /*  CurrRowsetStart > 1 AND CurrRowsetStart + FetchOffset < 1 AND
        | FetchOffset | <= RowsetSize
     */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,3);/* fourth row */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==4);
    my_assert(iarray[1]==5);

    /* 4 >1 AND 4-4 <1 AND |-4| <=10 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-4);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);


    SQLFreeStmt(hstmt,SQL_UNBIND);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative2");

  return OK;
}


DECLARE_TEST(t_rows_fetched_ptr)
{
    SQLRETURN    rc;
    SQLULEN rowsFetched, rowsSize;
    long         i;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_rows_fetched_ptr");

  ok_sql(hstmt,"create table t_rows_fetched_ptr(a int)");

  ok_sql(hstmt,"insert into t_rows_fetched_ptr values(0)");
  ok_sql(hstmt,"insert into t_rows_fetched_ptr values(1)");
  ok_sql(hstmt,"insert into t_rows_fetched_ptr values(2)");
  ok_sql(hstmt,"insert into t_rows_fetched_ptr values(3)");
  ok_sql(hstmt,"insert into t_rows_fetched_ptr values(4)");
  ok_sql(hstmt,"insert into t_rows_fetched_ptr values(5)");

    rowsSize= 1;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");

    i= 0;
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
        printMessage("\n total rows fetched: %ld", rowsFetched);
        myassert(rowsFetched == rowsSize);
        i++; rowsFetched= 0;
        rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    }
    myassert( i == 6);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    rowsSize= 2;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");
    mystmt(hstmt,rc);

    i= 0;
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
        printMessage("\n total rows fetched: %ld", rowsFetched);
        myassert(rowsFetched == rowsSize);
        i++;rowsFetched= 0;
        rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    }
    myassert( i == 3);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    rowsSize= 3;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");
    mystmt(hstmt,rc);

    i= 0;
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
        printMessage("\n total rows fetched: %ld", rowsFetched);
        myassert(rowsFetched == rowsSize);
        i++;rowsFetched= 0;
        rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    }
    myassert( i == 2);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    rowsSize= 4;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("\n total rows fetched: %ld", rowsFetched);
    myassert(rowsFetched == rowsSize);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("\n total rows fetched: %ld", rowsFetched);
    myassert(rowsFetched == 2);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    SQLFreeStmt(hstmt, SQL_CLOSE);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)1, 0);/* reset */
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, NULL, 0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_rows_fetched_ptr");

  return OK;
}


DECLARE_TEST(t_rows_fetched_ptr1)
{
  SQLRETURN   rc;
  SQLULEN     rowsFetched, rowsSize;
  SQLINTEGER  i;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_rows_fetched_ptr");

  ok_sql(hstmt, "create table t_rows_fetched_ptr(a int)");
  ok_sql(hstmt, "insert into t_rows_fetched_ptr values(0)");
  ok_sql(hstmt, "insert into t_rows_fetched_ptr values(1)");
  ok_sql(hstmt, "insert into t_rows_fetched_ptr values(2)");
  ok_sql(hstmt, "insert into t_rows_fetched_ptr values(3)");
  ok_sql(hstmt, "insert into t_rows_fetched_ptr values(4)");
  ok_sql(hstmt, "insert into t_rows_fetched_ptr values(5)");

    rowsSize= 1;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");
    mystmt(hstmt,rc);

    i= 0;
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
      fprintf(stdout,"total rows fetched: %ld\n", (long)rowsFetched);
      myassert(rowsFetched == rowsSize);
      i++; rowsFetched= 0;
      rc = SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0);
    }
    myassert( i == 6);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    rowsSize= 2;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");
    mystmt(hstmt,rc);

    i= 0;
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
      fprintf(stdout,"total rows fetched: %ld\n", (long)rowsFetched);
      myassert(rowsFetched == rowsSize);
      i++;rowsFetched= 0;
      rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    }
    myassert( i == 3);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    rowsSize= 3;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");
    mystmt(hstmt,rc);

    i= 0;
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
      printMessage("total rows fetched: %ld\n", rowsFetched);
      myassert(rowsFetched == rowsSize);
      i++;rowsFetched= 0;
      rc = SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0);
    }
    myassert( i == 2);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    rowsSize= 4;
    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)rowsSize, 0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, &rowsFetched, 0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_rows_fetched_ptr");
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("total rows fetched: %ld\n", rowsFetched);
    myassert(rowsFetched == rowsSize);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("total rows fetched: %ld\n", rowsFetched);
    myassert(rowsFetched == 2);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    SQLFreeStmt(hstmt, SQL_CLOSE);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)1, 0);/* reset */
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, NULL, 0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_rows_fetched_ptr");

  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_relative)
  ADD_TEST(t_relative1)
  ADD_TEST(t_relative2)
  ADD_TEST(t_rows_fetched_ptr)
  ADD_TEST(t_rows_fetched_ptr1)
END_TESTS


RUN_TESTS
