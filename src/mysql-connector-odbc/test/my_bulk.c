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

#define MAX_INSERT_COUNT 800
#define MAX_BM_INS_COUNT 20

DECLARE_TEST(t_bulk_insert)
{
  SQLINTEGER i, id[MAX_INSERT_COUNT+1];
  SQLCHAR    name[MAX_INSERT_COUNT][40],
             txt[MAX_INSERT_COUNT][60],
             ltxt[MAX_INSERT_COUNT][70];
  SQLDOUBLE  dt, dbl[MAX_INSERT_COUNT];
  SQLLEN     name_len[MAX_INSERT_COUNT],
             txt_len[MAX_INSERT_COUNT],
             ltxt_len[MAX_INSERT_COUNT];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bulk_insert");
  ok_sql(hstmt, "CREATE TABLE t_bulk_insert (id INT, v VARCHAR(100),"
         "txt TEXT, ft FLOAT(10), ltxt LONG VARCHAR)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  dt= 0.23456;

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)MAX_INSERT_COUNT, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, id, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, name, sizeof(name[0]), &name_len[0]));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_CHAR, txt, sizeof(txt[0]), &txt_len[0]));
  ok_stmt(hstmt, SQLBindCol(hstmt, 4, SQL_C_DOUBLE, dbl, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 5, SQL_C_CHAR, ltxt, sizeof(ltxt[0]), &ltxt_len[0]));

  ok_sql(hstmt, "SELECT id, v, txt, ft, ltxt FROM t_bulk_insert");

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0),
              SQL_NO_DATA_FOUND);

  for (i= 0; i < MAX_INSERT_COUNT; i++)
  {
    id[i]= i;
    dbl[i]= i + dt;
    sprintf((char *)name[i], "Varchar%d", i);
    sprintf((char *)txt[i],  "Text%d", i);
    sprintf((char *)ltxt[i], "LongText, id row:%d", i);
    name_len[i] = strlen(name[i]);
    txt_len[i] = strlen(txt[i]);
    ltxt_len[i] = strlen(ltxt[i]);
  }

  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));
  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_sql(hstmt, "SELECT * FROM t_bulk_insert");
  is_num(myrowcount(hstmt), MAX_INSERT_COUNT * 2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bulk_insert");

  return OK;
}


DECLARE_TEST(t_mul_pkdel)
{
  SQLINTEGER nData;
  SQLLEN nlen;
  SQLULEN pcrow;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_mul_pkdel");
  ok_sql(hstmt, "CREATE TABLE t_mul_pkdel (a INT NOT NULL, b INT,"
         "c VARCHAR(30) NOT NULL, PRIMARY KEY(a, c))");
  ok_sql(hstmt, "INSERT INTO t_mul_pkdel VALUES (100,10,'MySQL1'),"
         "(200,20,'MySQL2'),(300,20,'MySQL3'),(400,20,'MySQL4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_sql(hstmt, "SELECT a, c FROM t_mul_pkdel");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, &pcrow, NULL));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_mul_pkdel");

  is_num(myrowcount(hstmt), 3);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_mul_pkdel");

  return OK;
}


/**
  Bug #24306: SQLBulkOperations always uses indicator varables' values from
  the first record
*/
DECLARE_TEST(t_bulk_insert_indicator)
{
  SQLINTEGER id[4], nData;
  SQLLEN     indicator[4], nLen;

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_bulk");
  ok_sql(hstmt, "CREATE TABLE my_bulk (id int default 5)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt,
          SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE, (SQLPOINTER)3, 0));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, id, 0, indicator));

  ok_sql(hstmt, "SELECT id FROM my_bulk");

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0),
              SQL_NO_DATA_FOUND);

  id[0]= 1; indicator[0]= SQL_COLUMN_IGNORE;
  id[1]= 2; indicator[1]= SQL_NULL_DATA;
  id[2]= 3; indicator[2]= 0;

  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_sql(hstmt, "SELECT id FROM my_bulk");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, &nLen));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(nData, 5);
  my_assert(nLen != SQL_NULL_DATA);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(nLen, SQL_NULL_DATA);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(nData, 3);
  my_assert(nLen != SQL_NULL_DATA);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0),
              SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_bulk");

  return OK;
}


/**
  Simple structure for a row (just one element) plus an indicator column.
*/
typedef struct {
  SQLINTEGER val;
  SQLLEN     ind;
} row;


/**
  This is related to the fix for Bug #24306 -- handling of row-wise binding,
  plus handling of SQL_ATTR_ROW_BIND_OFFSET_PTR, within the context of
  SQLBulkOperations(hstmt, SQL_ADD).
*/
DECLARE_TEST(t_bulk_insert_rows)
{
  row        rows[3];
  SQLINTEGER nData;
  SQLLEN     nLen, offset;

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_bulk");
  ok_sql(hstmt, "CREATE TABLE my_bulk (id int default 5)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)3, 0));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_TYPE,
                                (SQLPOINTER)sizeof(row), 0));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &rows[0].val, 0,
                            &rows[0].ind));

  ok_sql(hstmt, "SELECT id FROM my_bulk");

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0),
              SQL_NO_DATA_FOUND);

  rows[0].val= 1; rows[0].ind= SQL_COLUMN_IGNORE;
  rows[1].val= 2; rows[1].ind= SQL_NULL_DATA;
  rows[2].val= 3; rows[2].ind= 0;

  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));

  /* Now re-insert the last row using SQL_ATTR_ROW_BIND_OFFSET_PTR */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_OFFSET_PTR,
                                (SQLPOINTER)&offset, 0));

  offset= 2 * sizeof(row);

  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));

  /* Remove SQL_ATTR_ROW_BIND_OFFSET_PTR */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_OFFSET_PTR,
                                NULL, 0));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT id FROM my_bulk");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, &nLen));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(nData, 5);
  my_assert(nLen != SQL_NULL_DATA);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(nLen, SQL_NULL_DATA);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(nData, 3);
  my_assert(nLen != SQL_NULL_DATA);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(nData, 3);
  my_assert(nLen != SQL_NULL_DATA);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0),
              SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_bulk");

  return OK;
}


DECLARE_TEST(t_bulk_insert_bookmark)
{
  SQLINTEGER i, id[MAX_BM_INS_COUNT + 1];
  SQLCHAR    name[MAX_BM_INS_COUNT][40],
             buff[100];
  SQLCHAR bData[MAX_BM_INS_COUNT][10];
  SQLSMALLINT length;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bulk_insert");
  ok_sql(hstmt, "CREATE TABLE t_bulk_insert (id INT, v VARCHAR(100))");
  ok_sql(hstmt, "INSERT INTO t_bulk_insert (id, v) VALUES "
                "(1, \"test1\"), "
                "(2, \"test2\"), "
                "(3, \"test3\"), "
                "(4, \"test4\")"
        );

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)MAX_BM_INS_COUNT, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_USE_BOOKMARKS,
                                (SQLPOINTER) SQL_UB_VARIABLE, 0));

  ok_sql(hstmt, "SELECT id, v FROM t_bulk_insert");

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));

  ok_stmt(hstmt, SQLBindCol(hstmt, 0, SQL_C_VARBOOKMARK, bData,
                            sizeof(bData[0]), NULL));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, id, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, name,
                            sizeof(name[0]), NULL));

  for (i= 0; i < MAX_BM_INS_COUNT; i++)
  {
    memset(bData[i], 0, 10);
    id[i]= i + 5;
    memset(name[i], 0, 40);
    sprintf((char *)name[i], "Varchar%d", i + 5);
  }

  /*
    If an application binds column 0 before it calls SQLBulkOperations with an
    Operation argument of SQL_ADD, the driver will update the bound column 0
    buffers with the bookmark values for the newly inserted row.
  */
  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  for (i= 0; i < MAX_BM_INS_COUNT; i++)
  {
    is_num(atol(bData[i]), i + 1);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT id, v FROM t_bulk_insert");
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  for (i= 0; i < MAX_BM_INS_COUNT; i++)
  {
    is_num(atol(bData[i]), i + 1);
    is_num(id[i], i + 1);
    if (i < 4)
    {
      length= sprintf((char *)buff, "test%d", i + 1);
      is_str(name[i], buff, length);
    }
    else
    {
      length= sprintf((char *)buff, "Varchar%d", i + 1);
      is_str(name[i], buff, length);
    }
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bulk_insert");

  return OK;
}


/**
  SQL Bookmark Update using SQLBulkOperations SQL_UPDATE_BY_BOOKMARK operation
  and verify updated data by fetching data using SQL_FETCH_BY_BOOKMARK
*/
DECLARE_TEST(t_bookmark_update)
{
  SQLLEN len= 0;
  SQLUSMALLINT rowStatus[4];
  SQLULEN numRowsFetched;
  SQLINTEGER nData[4], i;
  SQLCHAR szData[4][16];
  SQLCHAR bData[4][10];
  SQLLEN nRowCount;

  ok_sql(hstmt, "drop table if exists t_bookmark");
  ok_sql(hstmt, "CREATE TABLE t_bookmark ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt, "INSERT INTO t_bookmark VALUES "\
                "(1, 'string 1'),"\
                "(2, 'string 2'),"\
                "(3, 'string 3'),"\
                "(4, 'string 4'),"\
                "(5, 'string 5')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_USE_BOOKMARKS,
                                (SQLPOINTER) SQL_UB_VARIABLE, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_STATUS_PTR,
                                (SQLPOINTER)rowStatus, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR,
                                &numRowsFetched, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 4));

  ok_sql(hstmt, "select * from t_bookmark order by 1");
  ok_stmt(hstmt, SQLBindCol(hstmt, 0, SQL_C_VARBOOKMARK, bData,
	                        sizeof(bData[0]), NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  is_num(nData[0], 1);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 2);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 3);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 4);
  is_str(szData[3], "string 4", 8);

  for (i= 0; i < 4; ++i)
  {
    strcpy(szData[i], "xxxxxxxx");
  }

  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 2));
  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_UPDATE_BY_BOOKMARK));
  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(nRowCount, 2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 4));
  ok_sql(hstmt, "select * from t_bookmark order by 1");
  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_FETCH_BY_BOOKMARK));

  is_num(nData[0], 1);
  is_str(szData[0], "xxxxxxxx", 8);
  is_num(nData[1], 2);
  is_str(szData[1], "xxxxxxxx", 8);
  is_num(nData[2], 3);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 4);
  is_str(szData[3], "string 4", 8);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_bookmark");

  return OK;
}


/**
  SQL Bookmark Delete using SQLBulkOperations SQL_DELETE_BY_BOOKMARK operation
*/
DECLARE_TEST(t_bookmark_delete)
{
  SQLLEN len= 0;
  SQLUSMALLINT rowStatus[4];
  SQLULEN numRowsFetched;
  SQLINTEGER nData[4];
  SQLCHAR szData[4][16];
  SQLCHAR bData[4][10];
  SQLLEN nRowCount;

  ok_sql(hstmt, "drop table if exists t_bookmark");
  ok_sql(hstmt, "CREATE TABLE t_bookmark ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt, "INSERT INTO t_bookmark VALUES "\
                "(1, 'string 1'),"\
                "(2, 'string 2'),"\
                "(3, 'string 3'),"\
                "(4, 'string 4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_USE_BOOKMARKS,
                                (SQLPOINTER) SQL_UB_VARIABLE, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_STATUS_PTR,
                                (SQLPOINTER)rowStatus, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR,
                                &numRowsFetched, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 4));

  ok_sql(hstmt, "select * from t_bookmark order by 1");
  ok_stmt(hstmt, SQLBindCol(hstmt, 0, SQL_C_VARBOOKMARK, bData,
	                        sizeof(bData[0]), NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  is_num(nData[0], 1);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 2);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 3);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 4);
  is_str(szData[3], "string 4", 8);

  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 2));
  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_DELETE_BY_BOOKMARK));
  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(nRowCount, 2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 4));
  ok_sql(hstmt, "select * from t_bookmark order by 1");
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 0));

  is_num(nData[0], 3);
  is_str(szData[0], "string 3", 8);
  is_num(nData[1], 4);
  is_str(szData[1], "string 4", 8);

  memset(nData, 0, sizeof(nData));
  memset(szData, 'x', sizeof(szData));
  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 4));
  expect_stmt(hstmt, SQLBulkOperations(hstmt, SQL_FETCH_BY_BOOKMARK),
              SQL_SUCCESS_WITH_INFO);

  is_num(nData[0], 3);
  is_str(szData[0], "string 3", 8);
  is_num(nData[1], 4);
  is_str(szData[1], "string 4", 8);
  is_num(nData[2], 0);
  is_str(szData[2], "xxxxxxxx", 8);
  is_num(nData[3], 0);
  is_str(szData[3], "xxxxxxxx", 8);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_bookmark");

  return OK;
}


/**
  Segmentation Fault in SQLBulkOperations() (Bug #17714290)
*/
DECLARE_TEST(t_bug17714290)
{

  SQLINTEGER nData[4] = {1, 2, 3, 4};
  SQLCHAR szData[4][16] = {{"xxxxxxxx"},{"xxxxxxxx"},{"xxxxxxxx"},{"xxxxxxxx"}};

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug17714290");
  ok_sql(hstmt, "CREATE TABLE bug17714290 ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");

  ok_sql(hstmt, "INSERT INTO bug17714290 VALUES "\
                "(1, 'str 1'),(2, 'str 2'),(3, 'str 3'),(4, 'str 4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 4));

  ok_sql(hstmt, "SELECT * FROM bug17714290");

  /* We are not setting any bookmark options and not binding bookmark column */
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData[0]), NULL));

  /* This cannot be success, we just don't want to crash */
  SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0);
  SQLBulkOperations(hstmt, SQL_UPDATE_BY_BOOKMARK);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "DROP TABLE bug17714290");
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_bulk_insert)
  ADD_TEST(t_mul_pkdel)
  ADD_TEST(t_bulk_insert_indicator)
  ADD_TEST(t_bulk_insert_rows)
  ADD_TEST(t_bulk_insert_bookmark)
  ADD_TEST(t_bookmark_update)
  ADD_TEST(t_bookmark_delete)
  ADD_TEST(t_bug17714290)
END_TESTS


RUN_TESTS
