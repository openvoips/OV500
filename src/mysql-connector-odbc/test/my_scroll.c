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

/* Testing basic scrolling feature */
DECLARE_TEST(t_scroll)
{
  SQLUINTEGER i;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_scroll");
  ok_sql(hstmt, "CREATE TABLE t_scroll (col1 INT)");
  ok_sql(hstmt, "INSERT INTO t_scroll VALUES (1),(2),(3),(4),(5)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "SELECT * FROM t_scroll");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_ULONG, &i, 0, NULL));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_LAST, 0)); /* 5 */
  is_num(i, 5);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 0));/* 4 */
  is_num(i, 4);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -3));/* 1 */
  is_num(i, 1);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND); /* 0 */

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 1),
              SQL_NO_DATA_FOUND); /* 0 */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, -1));/* 1 */
  is_num(i, 1);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 4));/* 4 */
  is_num(i, 4);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 2),
              SQL_NO_DATA_FOUND); /* 6 */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 2));/* last */
  is_num(i, 5);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 2),
              SQL_NO_DATA_FOUND); /* last + 1 */

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, -7),
              SQL_NO_DATA_FOUND); /* 0 */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 2));/* 1 */
  is_num(i, 1);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 2),
              SQL_NO_DATA_FOUND); /* 0 */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));/* 1 */
  is_num(i, 1);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 0),
              SQL_NO_DATA_FOUND); /* 0 */

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND); /* 0 */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1));/* 1 */
  is_num(i, 1);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -1),
              SQL_NO_DATA_FOUND); /* 0 */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1));/* 1 */
  is_num(i, 1);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 1));/* 1 */
  is_num(i, 2);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -2),
              SQL_NO_DATA_FOUND); /* 0 */

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 6),
              SQL_NO_DATA_FOUND); /* last + 1 */

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 6));/* 1 */
  is_num(i, 5);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_scroll");

  return OK;
}


/* Testing SQL_FETCH_RELATIVE with row_set_size as 10 */
DECLARE_TEST(t_array_relative_10)
{
    SQLRETURN rc;
    SQLINTEGER iarray[15];
    SQLLEN   nrows, index;
    SQLUINTEGER i;
    char name[21];

    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_array_relative_10");

    ok_sql(hstmt, "create table t_array_relative_10(id int,name char(20))");

    rc = SQLPrepare(hstmt,(SQLCHAR *)"insert into t_array_relative_10 values(?,?)",SQL_NTS);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT, SQL_C_ULONG,
                          SQL_INTEGER,0,0,&i,0,NULL);
    mystmt(hstmt,rc);
    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT, SQL_C_CHAR,
                          SQL_CHAR,20,0,name,20,NULL);
    mystmt(hstmt,rc);

    for ( i = 1; i <= 50; i++ )
    {
        sprintf(name,"my%d",i);
        rc = SQLExecute(hstmt);
        mystmt(hstmt,rc);
    }

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    rc = SQLEndTran(SQL_HANDLE_DBC,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    /* set row size as 10 */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)10,0);
    mystmt(hstmt,rc);

    /* According to http://support.microsoft.com/kb/298678, the storage
       pointed to if SQL_ATTR_ROWS_FETCHED_PTR should be SQLLEN */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROWS_FETCHED_PTR,&nrows,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from t_array_relative_10");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,iarray,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);/* 1-10 */
    mystmt(hstmt,rc);

    printMessage("1-10, total rows:%ld\n",(long)nrows);

    for (index=1; index<=nrows; index++)
    {
        printMessage(" %d ",iarray[index-1]);
        myassert(iarray[index-1] == index);
    }    

    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);/* 10-20 */
    mystmt(hstmt,rc);

    printMessage("\n10-20, total rows:%ld\n",(long)nrows);

    for (index=1; index<=nrows; index++)
    {
        printMessage(" %d ",iarray[index-1]);
        myassert(iarray[index-1] == index+10);
    }    

    rc = SQLFetchScroll(hstmt,SQL_FETCH_PREV,0);/* 1-10 */
    mystmt(hstmt,rc);

    printMessage("\n1-10, total rows:%ld\n",(long)nrows);

    for (index=1; index<=nrows; index++)
    {
        printMessage(" %d ",iarray[index-1]);
        myassert(iarray[index-1] == index);
    }    

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* 2-11 */
    mystmt(hstmt,rc);

    printMessage("\n2-12, total rows:%ld\n",(long)nrows);

    for (index=1; index<=nrows; index++)
    {
        printMessage(" %d ",iarray[index-1]);
        myassert(iarray[index-1] == index+1);
    } 

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* 1-10 */
    mystmt(hstmt,rc);

    printMessage("\n1-10, total rows:%ld\n",(long)nrows);

    for (index=1; index<=nrows; index++)
    {
        printMessage(" %d",iarray[index-1]);
        myassert(iarray[index-1] == index);
    }       

    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,0);/* 1-10 */
    mystmt(hstmt,rc);

    printMessage("\n1-10, total rows:%ld\n",(long)nrows);

    for (index=1; index<=nrows; index++)
    {
        printMessage(" %d",iarray[index-1]);
        myassert(iarray[index-1] == index);
    }      

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* BOF */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* 1-10 */
    mystmt(hstmt,rc);

    printMessage("\n1-10, total rows:%ld\n",(long)nrows);

    for (index=1; index<=nrows; index++)
    {
        printMessage(" %d",iarray[index-1]);
        myassert(iarray[index-1] == index);
    } 

    SQLFreeStmt(hstmt,SQL_UNBIND);    
    SQLFreeStmt(hstmt,SQL_CLOSE);          

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_array_relative_10");

  return OK;
}


/* Testing SQL_FETCH_RELATIVE with row_set_size as 1 */
DECLARE_TEST(t_relative_1)
{
    SQLRETURN rc;
    SQLLEN nrows;
    SQLUINTEGER i;
    const SQLUINTEGER max_rows=10;

    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative_1");

  ok_sql(hstmt, "create table t_relative_1(id int)");

    rc = SQLPrepare(hstmt, (SQLCHAR *)"insert into t_relative_1 values(?)",SQL_NTS);
    mystmt(hstmt,rc);

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

    /* set row_size as 1 */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROWS_FETCHED_PTR,&nrows,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from t_relative_1");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&i,0,NULL);
    mystmt(hstmt,rc);

    /* row 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);/* 1 */
    mystmt(hstmt,rc);
    my_assert(i==1);

    /* Before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* jump to last row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,max_rows);/* last row */
    mystmt(hstmt,rc);
    my_assert(i==max_rows);

    /* jump to last row+1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* after last */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto first row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    /* before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto fifth  row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,5);/* 5 */    
    mystmt(hstmt,rc);
    my_assert(i==5);

    /* goto after end */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,max_rows);/* after last */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* 
       the scenarios from ODBC spec     
    */    

    /* CASE 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(i==1);

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
    my_assert(i==1);

    /* CASE 2 */    
    rc = SQLFetchScroll(hstmt,SQL_FETCH_LAST,1);/* last row */    
    mystmt(hstmt,rc);
    my_assert(i==max_rows);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,1);/* after end */    
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
    my_assert(i==max_rows);


    /* CASE 3 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    /* CurrRowsetStart = 1 AND FetchOffset < 0 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,0);/* first row */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-1);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 4 */
    /* CurrRowsetStart > 1 AND CurrRowsetStart + FetchOffset < 1 AND
       | FetchOffset | > RowsetSize
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,3);/* fourth row */    
    mystmt(hstmt,rc);
    my_assert(i==4);

    /* the following call satisfies 4 > 1 AND (3-4) < 1 AND |-4| > 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-4);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 5 */
    /* 1 <= CurrRowsetStart + FetchOffset <= LastResultRow */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,5);/* sixth row */    
    mystmt(hstmt,rc);
    my_assert(i==6);

    /* 1 <= 6-2 <= 10 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-2);/* fourth row */    
    mystmt(hstmt,rc);
    my_assert(i==4);

    /* CASE 6 */
    /*  CurrRowsetStart > 1 AND CurrRowsetStart + FetchOffset < 1 AND
        | FetchOffset | <= RowsetSize
     */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* first row */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,3);/* fourth row */    
    mystmt(hstmt,rc);
    my_assert(i==4);

    /* 4 >1 AND 4-4 <1 AND |-4| <=10 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-4);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);


    SQLFreeStmt(hstmt,SQL_UNBIND);    
    SQLFreeStmt(hstmt,SQL_CLOSE);          

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_relative_1");

  return OK;
}


/* Testing SQL_FETCH_RELATIVE with row_set_size as 2 */
DECLARE_TEST(t_array_relative_2)
{
    SQLRETURN rc;
    SQLUINTEGER i;
    SQLLEN nrows;
    SQLINTEGER iarray[15];
    const SQLUINTEGER max_rows=10;

    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_array_relative_2");

  ok_sql(hstmt,"create table t_array_relative_2(id int)");

    rc = SQLPrepare(hstmt, (SQLCHAR *)"insert into t_array_relative_2 values(?)",SQL_NTS);
    mystmt(hstmt,rc);

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

    ok_sql(hstmt, "select * from t_array_relative_2");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,iarray,0,NULL);
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

    /***
      for rowset_size > max_rows...
    */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)25,0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROWS_FETCHED_PTR,&nrows,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from t_array_relative_2");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,iarray,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);/* 2 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows-1);
    my_assert(iarray[0]==2);
    my_assert(iarray[max_rows-2]==10);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,1);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows);
    my_assert(iarray[0]==1);
    my_assert(iarray[max_rows-1]==max_rows);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,5);/* 5 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows-4);
    my_assert(iarray[0]==5);
    my_assert(iarray[max_rows-5]==max_rows);


    /* CurrRowsetStart > 1 AND 
       CurrRowsetStart + FetchOffset < 1 AND
       | FetchOffset | > RowsetSize

      ==> before start
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-30);/* 1 */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);/* 2 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows-1);
    my_assert(iarray[0]==2);
    my_assert(iarray[max_rows-2]==10);

    /* CurrRowsetStart > 1 AND 
       CurrRowsetStart + FetchOffset < 1 AND
       | FetchOffset | <= RowsetSize

      ==> 1
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_RELATIVE,-13);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows);
    my_assert(iarray[0]==1);
    my_assert(iarray[max_rows-1]==max_rows);

    SQLFreeStmt(hstmt,SQL_UNBIND);    
    SQLFreeStmt(hstmt,SQL_CLOSE); 

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_array_relative_2");

  return OK;
}


/* Testing SQL_FETCH_ABSOLUTE with row_set_size as 1 */
DECLARE_TEST(t_absolute_1)
{
    SQLRETURN rc;
    SQLLEN nrows;
    SQLUINTEGER i;
    const SQLUINTEGER max_rows=10;

    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_absolute_1");

  ok_sql(hstmt, "create table t_absolute_1(id int)");

    rc = SQLPrepare(hstmt, (SQLCHAR *)"insert into t_absolute_1 values(?)",SQL_NTS);
    mystmt(hstmt,rc);

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

    /* set row_size as 1 */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROWS_FETCHED_PTR,&nrows,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from t_absolute_1");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&i,0,NULL);
    mystmt(hstmt,rc);

    /* row 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);/* 1 */
    mystmt(hstmt,rc);
    my_assert(i==1);

    /* Before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-12);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* jump to last row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows);/* last row */
    mystmt(hstmt,rc);
    my_assert(i==max_rows);

    /* jump to last row+1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+1);/* after last */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto first row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,1);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-15);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto fifth  row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,5);/* 5 */    
    mystmt(hstmt,rc);
    my_assert(i==5);

    /* goto after end */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+5);/* after last */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* 
       the scenarios from ODBC spec     
    */    

    /* CASE 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_FIRST,1);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(i==1);

    /* FetchOffset < 0 AND | FetchOffset | <= LastResultRow ,
       ==> should yield LastResultRow + FetchOffset + 1 
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-1);
    mystmt(hstmt,rc);
    my_assert(i==(max_rows-1+1));

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-4);
    mystmt(hstmt,rc);
    my_assert(i==(max_rows-4+1));

    /* CASE 2 :
      FetchOffset < 0 AND
      | FetchOffset | > LastResultRow AND
      | FetchOffset | > RowsetSize

      ==> Before start
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-11);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 4:
    
      FetchOffset = 0
     
      ==> before start
    */  
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 5: 
       1 <= FetchOffset <= LastResultRow

      ==> FetchOffset
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);/* 2 */    
    mystmt(hstmt,rc);
    my_assert(i==2);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,9);/* 9 */    
    mystmt(hstmt,rc);
    my_assert(i==9);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,6);/* 6 */    
    mystmt(hstmt,rc);
    my_assert(i==6);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* BOF */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 6:
      FetchOffset > LastResultRow

      ==> after end
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows);/* last row */    
    mystmt(hstmt,rc);
    my_assert(i==max_rows);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+1);/* after end */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+max_rows);/* after end */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,5);/* 5 */    
    mystmt(hstmt,rc);
    my_assert(i==5);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,12);/* 5 */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    SQLFreeStmt(hstmt,SQL_UNBIND);    
    SQLFreeStmt(hstmt,SQL_CLOSE);          

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_absolute_1");

  return OK;
}


/* Testing SQL_FETCH_ABSOLUTE with row_set_size as 2 */
DECLARE_TEST(t_absolute_2)
{
    SQLRETURN rc;
    SQLLEN nrows;
    SQLINTEGER iarray[15];
    const SQLUINTEGER max_rows=10;
    SQLUINTEGER i;

    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_absolute_2");

  ok_sql(hstmt, "create table t_absolute_2(id int)");

    rc = SQLPrepare(hstmt, (SQLCHAR *)"insert into t_absolute_2 values(?)",SQL_NTS);
    mystmt(hstmt,rc);

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

    /* set row_size as 1 */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)2,0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROWS_FETCHED_PTR,&nrows,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from t_absolute_2");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,iarray,0,NULL);
    mystmt(hstmt,rc);

    /* row 1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,0);/* 1 */
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    /* Before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-12);/* before start */
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* jump to last row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows);/* last row */
    mystmt(hstmt,rc);
    my_assert(nrows == 1);
    my_assert(iarray[0]==max_rows);


    /* jump to last row+1 */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+1);/* after last */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto first row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,1);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==1);
    my_assert(iarray[1]==2);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* before start */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-15);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* goto fifth  row */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,5);/* 5 */    
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==5);
    my_assert(iarray[1]==6);

    /* goto after end */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+5);/* after last */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
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

    /* FetchOffset < 0 AND | FetchOffset | <= LastResultRow ,
       ==> should yield LastResultRow + FetchOffset + 1 
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-1);
    mystmt(hstmt,rc);
    my_assert(nrows == 1);
    my_assert(iarray[0]==(max_rows-1+1));

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-4);
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==(max_rows-4+1));
    my_assert(iarray[1]==(max_rows-4+1+1));

    /* CASE 2 :
      FetchOffset < 0 AND
      | FetchOffset | > LastResultRow AND
      | FetchOffset | > RowsetSize

      ==> Before start
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-11);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 4:
    
      FetchOffset = 0
     
      ==> before start
    */  
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 5: 
       1 <= FetchOffset <= LastResultRow

      ==> FetchOffset
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);/* 2 */    
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==2);
    my_assert(iarray[1]==3);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,9);/* 9 */    
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==9);
    my_assert(iarray[1]==10);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,6);/* 6 */    
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==6);
    my_assert(iarray[1]==7);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* BOF */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    /* CASE 6:
      FetchOffset > LastResultRow

      ==> after end
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows);/* last row */    
    mystmt(hstmt,rc);
    my_assert(nrows == 1);
    my_assert(iarray[0]==max_rows);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+1);/* after end */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,max_rows+max_rows);/* after end */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,5);/* 5 */    
    mystmt(hstmt,rc);
    my_assert(nrows == 2);
    my_assert(iarray[0]==5);
    my_assert(iarray[1]==6);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,12);/* 5 */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,0);/* before start */    
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    SQLFreeStmt(hstmt,SQL_UNBIND);    
    SQLFreeStmt(hstmt,SQL_CLOSE);          

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

    /* for rowset_size > max_rows...*/
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)25,0);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROWS_FETCHED_PTR,&nrows,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from t_absolute_2");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,iarray,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);/* 2 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows-1);
    my_assert(iarray[0]==2);
    my_assert(iarray[max_rows-2]==10);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,1);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows);
    my_assert(iarray[0]==1);
    my_assert(iarray[max_rows-1]==max_rows);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,5);/* 5 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows-4);
    my_assert(iarray[0]==5);
    my_assert(iarray[max_rows-5]==max_rows);


    /* FetchOffset < 0 AND
      | FetchOffset | > LastResultRow AND
      | FetchOffset | <= RowsetSize
    */
    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,-13);/* 1 */    
    mystmt(hstmt,rc);
    my_assert(nrows == max_rows);
    my_assert(iarray[0]==1);
    my_assert(iarray[max_rows-1]==max_rows);

    SQLFreeStmt(hstmt,SQL_UNBIND);    
    SQLFreeStmt(hstmt,SQL_CLOSE); 

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_absolute_2");

  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_scroll)
  ADD_TEST(t_array_relative_10)
  ADD_TEST(t_array_relative_2)
  ADD_TEST(t_relative_1)
  ADD_TEST(t_absolute_1)
  ADD_TEST(t_absolute_2)
END_TESTS


RUN_TESTS
