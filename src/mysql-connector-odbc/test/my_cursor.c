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


/* perform positioned update and delete */
DECLARE_TEST(my_positioned_cursor)
{
  SQLLEN      nRowCount;
  SQLHSTMT    hstmt_pos;
  SQLCHAR     data[10];

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_demo_cursor");
  ok_sql(hstmt, "CREATE TABLE my_demo_cursor (id INT, name VARCHAR(20))");
  ok_sql(hstmt, "INSERT INTO my_demo_cursor VALUES (0,'MySQL0'),(1,'MySQL1'),"
         "(2,'MySQL2'),(3,'MySQL3'),(4,'MySQL4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_DYNAMIC,0));

  /* set the cursor name as 'mysqlcur' on hstmt */
  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"mysqlcur", SQL_NTS));

  /* Open the resultset of table 'my_demo_cursor' */
  ok_sql(hstmt,"SELECT * FROM my_demo_cursor");

  /* goto the last row */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_LAST, 1L));

  /* create new statement handle */
  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt_pos));

  /* now update the name field to 'updated' using positioned cursor */
  ok_sql(hstmt_pos, "UPDATE my_demo_cursor SET name='updated' "
         "WHERE CURRENT OF mysqlcur");

  ok_stmt(hstmt, SQLRowCount(hstmt_pos, &nRowCount));
  is_num(nRowCount, 1);

  ok_stmt(hstmt_pos, SQLFreeStmt(hstmt_pos, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Now delete 2nd row */
  ok_sql(hstmt, "SELECT * FROM my_demo_cursor");

  /* goto the second row */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 2L));

  /* now delete the current row */
  ok_sql(hstmt_pos, "DELETE FROM my_demo_cursor WHERE CURRENT OF mysqlcur");

  ok_stmt(hstmt, SQLRowCount(hstmt_pos, &nRowCount));
  is_num(nRowCount, 1);

  /* free the statement cursor */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Free the statement 'hstmt_pos' */
  ok_stmt(hstmt_pos, SQLFreeHandle(SQL_HANDLE_STMT, hstmt_pos));

  /* Now fetch and verify the data */
  ok_sql(hstmt, "SELECT * FROM my_demo_cursor");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 0);
  is_str(my_fetch_str(hstmt, data, 2), "MySQL0", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 2);
  is_str(my_fetch_str(hstmt, data, 2), "MySQL2", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 3);
  is_str(my_fetch_str(hstmt, data, 2), "MySQL3", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 4);
  is_str(my_fetch_str(hstmt, data, 2), "updated", 7);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_demo_cursor");

  return OK;
}


/* perform delete and update using SQLSetPos */
DECLARE_TEST(my_setpos_cursor)
{
  SQLLEN      nRowCount;
  SQLINTEGER  id;
  SQLCHAR     name[50];

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_demo_cursor");
  ok_sql(hstmt, "CREATE TABLE my_demo_cursor (id INT, name VARCHAR(20))");
  ok_sql(hstmt, "INSERT INTO my_demo_cursor VALUES (0,'MySQL0'),(1,'MySQL1'),"
         "(2,'MySQL2'),(3,'MySQL3'),(4,'MySQL4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "SELECT * FROM my_demo_cursor");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &id, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, name, sizeof(name),NULL));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1L));

  strcpy((char *)name, "first-row");

  /* now update the name field to 'first-row' using SQLSetPos */
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(nRowCount, 1);

  /* position to second row and delete it ..*/
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 2L));

  /* now delete the current, second row */
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(nRowCount, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Now fetch and verify the data */
  ok_sql(hstmt, "SELECT * FROM my_demo_cursor");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 0);
  is_str(my_fetch_str(hstmt, name, 2), "first-row", 9);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 2);
  is_str(my_fetch_str(hstmt, name, 2), "MySQL2", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 3);
  is_str(my_fetch_str(hstmt, name, 2), "MySQL3", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 4);
  is_str(my_fetch_str(hstmt, name, 2), "MySQL4", 6);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE my_demo_cursor");

  return OK;
}


/**
 Bug #5853: Using Update with 'WHERE CURRENT OF' with binary data crashes
*/
DECLARE_TEST(t_bug5853)
{
  SQLRETURN rc;
  SQLHSTMT  hstmt_pos;
  SQLCHAR   nData[4];
  SQLLEN    nLen= SQL_DATA_AT_EXEC;
  int       i= 0;

  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt_pos));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug5853");

  ok_sql(hstmt,"CREATE TABLE t_bug5853 (id INT AUTO_INCREMENT PRIMARY KEY, a VARCHAR(3))");

  ok_sql(hstmt,"INSERT INTO t_bug5853 (a) VALUES ('abc'),('def')");

  ok_stmt(hstmt_pos,
          SQLPrepare(hstmt_pos, (SQLCHAR *)
                     "UPDATE t_bug5853 SET a = ? WHERE CURRENT OF bug5853",
                     SQL_NTS));

  ok_stmt(hstmt_pos, SQLBindParameter(hstmt_pos, 1, SQL_PARAM_INPUT,
                                      SQL_C_CHAR, SQL_VARCHAR, 0, 0, NULL,
                                      0, &nLen));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_DYNAMIC,0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"bug5853", SQL_NTS));

  ok_sql(hstmt, "SELECT * FROM t_bug5853 ORDER BY id");

  while ((rc= SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0)) != SQL_NO_DATA_FOUND)
  {
    char data[2][3] = { "uvw", "xyz" };

    expect_stmt(hstmt_pos, SQLExecute(hstmt_pos), SQL_NEED_DATA);
    rc= SQL_NEED_DATA;

    while (rc == SQL_NEED_DATA)
    {
      SQLPOINTER token;
      rc= SQLParamData(hstmt_pos, &token);
      if (rc == SQL_NEED_DATA)
      {
        ok_stmt(hstmt_pos, SQLPutData(hstmt_pos, data[i++ % 2],
                                      sizeof(data[0])));
      }
    }
  }

  ok_stmt(hstmt_pos, SQLFreeStmt(hstmt_pos, SQL_CLOSE));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt,"SELECT * FROM t_bug5853 ORDER BY id");

  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, nData, sizeof(nData), &nLen));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_str(nData, "uvw", 3);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_str(nData, "xyz", 3);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0),
              SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug5853");

  return OK;
}


DECLARE_TEST(t_setpos_del_all)
{
  SQLINTEGER nData[4];
  SQLCHAR szData[4][10];
  SQLUSMALLINT rgfRowStatus[4];
  SQLLEN nlen;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_del_all");
  ok_sql(hstmt, "CREATE TABLE t_setpos_del_all (a INT NOT NULL PRIMARY KEY,"
         "b VARCHAR(20))");
  ok_sql(hstmt, "INSERT INTO t_setpos_del_all VALUES (100,'MySQL1'),"
         "(200,'MySQL2'),(300,'MySQL3'),(400,'MySQL4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 4));

  ok_sql(hstmt, "SELECT * FROM t_setpos_del_all ORDER BY a ASC");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_FIRST, 1, NULL,
                                  rgfRowStatus));

  is_num(nData[0], 100);
  is_str(szData[0], "MySQL1", 6);
  is_num(nData[1], 200);
  is_str(szData[1], "MySQL2", 6);
  is_num(nData[2], 300);
  is_str(szData[2], "MySQL3", 6);
  is_num(nData[3], 400);
  is_str(szData[3], "MySQL4", 6);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_setpos_del_all");

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 1));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_del_all");

  return OK;
}


DECLARE_TEST(t_setpos_upd_decimal)
{
  SQLINTEGER   rec;
  SQLUSMALLINT status;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_upd_decimal");
  ok_sql(hstmt,
         "CREATE TABLE t_setpos_upd_decimal (record DECIMAL(3,0),"
         "num1 FLOAT, num2 DECIMAL(6,0), num3 DECIMAL(10,3))");
  ok_sql(hstmt, "INSERT INTO t_setpos_upd_decimal VALUES (1,12.3,134,0.100)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT record FROM t_setpos_upd_decimal");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &rec, 0, NULL));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL, &status));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  rec= 100;

  expect_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE),
              SQL_ERROR);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_upd_decimal");

  return OK;
}


DECLARE_TEST(t_setpos_position)
{
  SQLINTEGER nData;
  SQLLEN nlen;
  SQLCHAR szData[255];
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_position");
  ok_sql(hstmt, "CREATE TABLE t_setpos_position (a INT, b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO t_setpos_position VALUES (100,'MySQL1'),"
         "(200,'MySQL2'),(300,'MySQL3')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0));

  ok_sql(hstmt, "SELECT * FROM t_setpos_position");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &nlen));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, &pcrow,
                                  &rgfRowStatus));

  is_num(nData, 100);
  is_num(nlen, 6);
  is_str(szData, "MySQL1", 6);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  nData= 1000;
  strcpy((char *)szData, "updated");
  nlen= 7;

  expect_stmt(hstmt, SQLSetPos(hstmt, 3, SQL_UPDATE, SQL_LOCK_NO_CHANGE),
              SQL_ERROR);

  expect_stmt(hstmt, SQLSetPos(hstmt, 2, SQL_UPDATE, SQL_LOCK_NO_CHANGE),
              SQL_ERROR);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_setpos_position");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1000);
  is_str(my_fetch_str(hstmt, szData, 2), "updated", 7);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 200);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL2", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 300);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL3", 6);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DELETE FROM t_setpos_position WHERE b = 'updated'");

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_setpos_position");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 200);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL2", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 300);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL3", 6);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_position");

  return OK;
}


DECLARE_TEST(t_pos_column_ignore)
{
  SQLCHAR szData[20];
  SQLINTEGER nData;
  SQLLEN  pcbValue, nlen;
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_column_ignore");
  ok_sql(hstmt,
         "CREATE TABLE t_pos_column_ignore "
         "(col1 INT NOT NULL PRIMARY KEY, col2 VARCHAR(30))");
  ok_sql(hstmt,
         "INSERT INTO t_pos_column_ignore VALUES (10,'venu'),(100,'MySQL')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_KEYSET_DRIVEN, 0));

  /* ignore all columns */
  ok_sql(hstmt, "SELECT * FROM t_pos_column_ignore ORDER BY col1 ASC");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, &pcbValue));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &pcbValue));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, &pcrow,
                                  &rgfRowStatus));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  nData= 99;
  strcpy((char *)szData , "updated");

  pcbValue= SQL_COLUMN_IGNORE;
  expect_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE),
              SQL_ERROR);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_pos_column_ignore ORDER BY col1 ASC");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 10);
  is_str(my_fetch_str(hstmt, szData, 2), "venu", 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* ignore only one column */
  ok_sql(hstmt, "SELECT * FROM t_pos_column_ignore ORDER BY col1 ASC");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &pcbValue));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, &pcrow,
                                  &rgfRowStatus));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  nData= 99;
  strcpy((char *)szData , "updated");

  pcbValue= SQL_COLUMN_IGNORE;
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_pos_column_ignore ORDER BY col1 ASC");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 99);
  is_str(my_fetch_str(hstmt, szData, 2), "venu", 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_column_ignore");

  return OK;
}


DECLARE_TEST(t_pos_datetime_delete)
{
  SQLHSTMT     hstmt1;
  SQLINTEGER   int_data;
  SQLLEN       row_count;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_datetime_delete");
  ok_sql(hstmt, "CREATE TABLE t_pos_datetime_delete (a INT NOT NULL DEFAULT 0,"
         "b VARCHAR(20) NOT NULL DEFAULT '', c DATETIME NOT NULL DEFAULT '2000-01-01')");
  ok_sql(hstmt, "INSERT INTO t_pos_datetime_delete VALUES"
         "(1,'venu','2003-02-10 14:45:39')");
  ok_sql(hstmt, "INSERT INTO t_pos_datetime_delete (b) VALUES ('')");
  ok_sql(hstmt, "INSERT INTO t_pos_datetime_delete (a) VALUES (2)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu_cur", 8));

  ok_sql(hstmt, "SELECT * FROM t_pos_datetime_delete");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &int_data, 0, NULL));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL,
                                  &rgfRowStatus));
  is_num(int_data, 1);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));
  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_CONCURRENCY,
                                 (SQLPOINTER)SQL_CONCUR_ROWVER, 0));
  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_CURSOR_TYPE,
                                 (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0));

  ok_sql(hstmt1, "DELETE FROM t_pos_datetime_delete WHERE CURRENT OF venu_cur");

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &row_count));
  is_num(row_count, 1);

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL,
                                  &rgfRowStatus));
  is_num(int_data, 0);

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL, NULL));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_sql(hstmt1, "DELETE FROM t_pos_datetime_delete WHERE CURRENT OF venu_cur");

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &row_count));
  is_num(row_count, 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_pos_datetime_delete");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 0);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_datetime_delete");

  return OK;
}


DECLARE_TEST(t_pos_datetime_delete1)
{
  SQLRETURN rc;
  SQLHSTMT hstmt1;
  SQLINTEGER int_data;
  SQLLEN row_count, cur_type;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_delete");

    rc = SQLAllocStmt(hdbc,&hstmt1);
    mycon(hdbc,rc);

    rc = tmysql_exec(hstmt,"create table t_pos_delete(id int not null default '0',\
                                                      name varchar(20) NOT NULL default '',\
                                                      created datetime NOT NULL default '2000-01-01')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_pos_delete values(1,'venu','2003-02-10 14:45:39')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_pos_delete(name) values('')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_pos_delete(id) values(2)");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_pos_delete(id) values(3)");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_pos_delete(id) values(4)");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_pos_delete(id) values(5)");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"select * from t_pos_delete");
    mystmt(hstmt,rc);

    my_assert(6 == myresult(hstmt));

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY, (SQLPOINTER) SQL_CONCUR_ROWVER, 0);
    SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER) SQL_CURSOR_DYNAMIC, 0);
    SQLSetStmtOption(hstmt,SQL_SIMULATE_CURSOR,SQL_SC_NON_UNIQUE);

    SQLSetStmtAttr(hstmt1, SQL_ATTR_CONCURRENCY, (SQLPOINTER) SQL_CONCUR_ROWVER, 0);
    SQLSetStmtAttr(hstmt1, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER) SQL_CURSOR_DYNAMIC, 0);
    SQLSetStmtOption(hstmt1,SQL_SIMULATE_CURSOR,SQL_SC_NON_UNIQUE);

    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"venu_cur",8);
    mystmt(hstmt,rc);

    rc = SQLGetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, &cur_type, 0, NULL);
    mystmt(hstmt,rc);

    ok_sql(hstmt,"select * from t_pos_delete");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&int_data,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_ABSOLUTE,3,NULL,&rgfRowStatus);
    mystmt(hstmt,rc);
    fprintf(stdout,"current_row: %d\n", int_data);
    myassert(int_data == 2);

    rc = SQLSetPos(hstmt,1,SQL_POSITION,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    ok_sql(hstmt1,"DELETE FROM t_pos_delete WHERE CURRENT OF venu_cur");

    rc = SQLRowCount(hstmt1,&row_count);
    mystmt(hstmt1,rc);
    fprintf(stdout, "rows affected: %d\n", (int)row_count);
    myassert(row_count == 1);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_NEXT,1,NULL,&rgfRowStatus);
    mystmt(hstmt,rc);
    fprintf(stdout,"current_row: %d\n", (int)int_data);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_NEXT,1,NULL,&rgfRowStatus);
    mystmt(hstmt,rc);
    fprintf(stdout,"current_row: %d\n", int_data);

    /*rc = SQLExtendedFetch(hstmt,SQL_FETCH_NEXT,1,NULL,NULL);
    mystmt(hstmt,rc);*/

    rc = SQLSetPos(hstmt,1,SQL_POSITION,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    ok_sql(hstmt1,"DELETE FROM t_pos_delete WHERE CURRENT OF venu_cur");

    rc = SQLRowCount(hstmt1,&row_count);
    mystmt(hstmt1,rc);
    fprintf(stdout, "rows affected: %d\n", (int)row_count);
    myassert(row_count == 1);

    SQLFreeStmt(hstmt,SQL_UNBIND);
    SQLFreeStmt(hstmt,SQL_CLOSE);
    SQLFreeStmt(hstmt1,SQL_CLOSE);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = tmysql_exec(hstmt,"select * from t_pos_delete");
    mystmt(hstmt,rc);

    my_assert(4 == myresult(hstmt));

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt1,SQL_DROP);
    mystmt(hstmt1,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_delete");

  return OK;
}


DECLARE_TEST(t_getcursor)
{
  SQLRETURN rc;
  SQLHSTMT hstmt1,hstmt2,hstmt3;
  SQLCHAR curname[50];
  SQLSMALLINT nlen;

    rc = SQLAllocHandle(SQL_HANDLE_STMT,hdbc,&hstmt1);
    mycon(hdbc, rc);
    rc = SQLAllocHandle(SQL_HANDLE_STMT,hdbc,&hstmt2);
    mycon(hdbc, rc);
    rc = SQLAllocHandle(SQL_HANDLE_STMT,hdbc,&hstmt3);
    mycon(hdbc, rc);

    rc = SQLGetCursorName(hstmt1,curname,50,&nlen);
    if (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
      fprintf(stdout,"default cursor name  : %s(%d)\n",curname,nlen);
      is_num(nlen, 8);
      is_str(curname,"SQL_CUR0", 9);

      rc = SQLGetCursorName(hstmt3,curname,50,&nlen);
      mystmt(hstmt1,rc);
      fprintf(stdout,"default cursor name  : %s(%d)\n",curname,nlen);

      rc = SQLGetCursorName(hstmt1,curname,4,&nlen);
      mystmt_err(hstmt1,rc == SQL_SUCCESS_WITH_INFO, rc);
      fprintf(stdout,"truncated cursor name: %s(%d)\n",curname,nlen);
      is_num(nlen, 8);
      is_str(curname, "SQL", 4);

      rc = SQLGetCursorName(hstmt1,curname,0,&nlen);
      mystmt_err(hstmt1,rc == SQL_SUCCESS_WITH_INFO, rc);
      fprintf(stdout,"untouched cursor name: %s(%d)\n",curname,nlen);
      myassert(nlen == 8);

      expect_stmt(hstmt1, SQLGetCursorName(hstmt1, curname, 8, &nlen),
                  SQL_SUCCESS_WITH_INFO);
      fprintf(stdout,"truncated cursor name: %s(%d)\n",curname,nlen);
      is_num(nlen, 8);
      is_str(curname, "SQL_CUR", 8);

      rc = SQLGetCursorName(hstmt1,curname,9,&nlen);
      fprintf(stdout,"full cursor name     : %s(%d)\n",curname,nlen);
      is_num(nlen, 8);
      is_str(curname, "SQL_CUR0", 9);
    }

    rc = SQLSetCursorName(hstmt1, (SQLCHAR *)"venucur123",7);
    mystmt(hstmt1,rc);

    rc = SQLGetCursorName(hstmt1,curname,8,&nlen);
    mystmt(hstmt1,rc);
    is_num(nlen, 7);
    is_str(curname, "venucur", 8);

    rc = SQLFreeHandle(SQL_HANDLE_STMT,hstmt1);
    mystmt(hstmt1,rc);

  return OK;
}


DECLARE_TEST(t_getcursor1)
{
  SQLRETURN rc;
  SQLHSTMT hstmt1;
  SQLCHAR curname[50];
  SQLSMALLINT nlen,index;

  for(index=0; index < 100; index++)
  {
    rc = SQLAllocHandle(SQL_HANDLE_STMT,hdbc,&hstmt1);
    mycon(hdbc, rc);

    rc = SQLGetCursorName(hstmt1,curname,50,&nlen);
    if (rc != SQL_SUCCESS)
      break;
    fprintf(stdout,"%s(%d) \n",curname,nlen);

    rc = SQLFreeHandle(SQL_HANDLE_STMT,hstmt1);
    mystmt(hstmt1,rc);
  }

  return OK;
}


DECLARE_TEST(t_acc_crash)
{
  SQLINTEGER  id;
  SQLCHAR     name[20], data[30];
  /* Hasn't that actually to be a SQL_DATE_STRUCT */
  SQL_TIMESTAMP_STRUCT ts;
  SQLLEN      ind_strlen;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_acc_crash");
  ok_sql(hstmt,
         "CREATE TABLE t_acc_crash (a INT NOT NULL AUTO_INCREMENT,"
         "b CHAR(20), c DATE, PRIMARY KEY (a))");
  ok_sql(hstmt,
         "INSERT INTO t_acc_crash (b) VALUES ('venu'),('monty'),('mysql')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 1));

  ok_sql(hstmt, "SELECT * FROM t_acc_crash ORDER BY a ASC");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &id, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, name, sizeof(name), NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_DATE, &ts, 0, &ind_strlen));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 1));

  id= 9;
  strcpy((char *)name, "updated");
  ts.year= 2010;
  ts.month= 9;
  ts.day= 25;

  ind_strlen= 0;

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_acc_crash ORDER BY a DESC");

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 1), 9);
  is_str(my_fetch_str(hstmt, data, 2), "updated", 7);
  is_str(my_fetch_str(hstmt, data, 3), "2010-09-25", 10);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_acc_crash");

  return OK;
}


DECLARE_TEST(tmysql_setpos_del)
{
  SQLINTEGER nData;
  SQLLEN nlen;
  SQLCHAR szData[255];
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_del");
  ok_sql(hstmt, "CREATE TABLE tmysql_setpos_del (a INT, b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO tmysql_setpos_del VALUES (100,'MySQL1'),"
         "(200,'MySQL2'),(300,'MySQL3'),(400,'MySQL4'),(300,'MySQL5'),"
         "(300,'MySQL6'),(300,'MySQL7')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_sql(hstmt, "SELECT * FROM tmysql_setpos_del");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &nlen));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 5, &pcrow,
                                  &rgfRowStatus));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM tmysql_setpos_del");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 100);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL1", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 200);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL2", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 300);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL3", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 400);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL4", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 300);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL6", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 300);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL7", 6);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_del");

  return OK;
}


DECLARE_TEST(tmysql_setpos_del1)
{
  SQLINTEGER nData;
  SQLLEN nlen;
  SQLCHAR szData[255];
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_del1");
  ok_sql(hstmt, "CREATE TABLE tmysql_setpos_del1 (a INT, b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO tmysql_setpos_del1 VALUES (100,'MySQL1'),"
         "(200,'MySQL2'),(300,'MySQL3'),(400,'MySQL4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_sql(hstmt, "SELECT * FROM tmysql_setpos_del1");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &nlen));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 3, &pcrow,
                                  &rgfRowStatus));

  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM tmysql_setpos_del1");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 100);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL1", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 200);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL2", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 400);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL4", 6);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_del1");

  return OK;
}


DECLARE_TEST(tmysql_setpos_upd)
{
    SQLRETURN rc;
    SQLINTEGER nData = 500;
    SQLLEN nlen;
    SQLCHAR szData[255]={0};
    SQLULEN pcrow;
    SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos");
    rc = tmysql_exec(hstmt,"create table tmysql_setpos(col1 int, col2 varchar(30))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_setpos values(100,'MySQL1')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos values(300,'MySQL3')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos values(200,'MySQL2')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos values(300,'MySQL3')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos values(400,'MySQL4')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos values(300,'MySQL3')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"venu",SQL_NTS);

    ok_sql(hstmt,"select * from tmysql_setpos");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,100,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,100,NULL);
    mystmt(hstmt,rc);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_ABSOLUTE,3,&pcrow,&rgfRowStatus);
    mystmt(hstmt,rc);

    printMessage(" pcrow:%d\n",pcrow);

    printMessage(" row1:%d,%s\n",nData,szData);

    rc = SQLSetPos(hstmt,1,SQL_POSITION,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    nData = 1000;
    strcpy((char *)szData , "updated");

    rc = SQLSetPos(hstmt,3,SQL_UPDATE,SQL_LOCK_NO_CHANGE);
    mystmt_err(hstmt,rc== SQL_ERROR,rc);

    rc = SQLSetPos(hstmt,1,SQL_UPDATE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage(" rows affected:%d\n",nlen);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"select * from tmysql_setpos");
    mystmt(hstmt,rc);

    myresult(hstmt);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    ok_sql(hstmt,"DELETE FROM tmysql_setpos WHERE col2 = 'updated'");

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);
    printMessage("\n total rows affceted:%d",nlen);
    my_assert(nlen == 1);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = tmysql_exec(hstmt,"select * from tmysql_setpos");
    mystmt(hstmt,rc);

    my_assert(5 == myresult(hstmt));

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos");

  return OK;
}


DECLARE_TEST(tmysql_setpos_add)
{
    SQLRETURN rc;
    SQLINTEGER nData= 500;
    SQLLEN nlen;
    SQLCHAR szData[255]={0};
    SQLULEN pcrow;
    SQLUSMALLINT rgfRowStatus;

    ok_sql(hstmt, "SET @@sql_mode=''");
    ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_add");
    rc = tmysql_exec(hstmt,"create table tmysql_setpos_add(col1 int, col2 varchar(30))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_setpos_add values(100,'MySQL1')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos_add values(300,'MySQL3')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"venu",SQL_NTS);

    ok_sql(hstmt,"select * from tmysql_setpos_add");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,100,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,100,NULL);
    mystmt(hstmt,rc);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_NEXT,1,&pcrow,&rgfRowStatus);
    mystmt(hstmt,rc);

    nData = 1000;
    strcpy((char *)szData , "insert-new1");

    rc = SQLSetPos(hstmt,3,SQL_ADD,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage("rows affected:%d\n",nlen);

    strcpy((char *)szData , "insert-new2");
    rc = SQLSetPos(hstmt,1,SQL_ADD,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage("rows affected:%d\n",nlen);

    strcpy((char *)szData , "insert-new3");
    rc = SQLSetPos(hstmt,0,SQL_ADD,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage("rows affected:%d\n",nlen);

    strcpy((char *)szData , "insert-new4");
    rc = SQLSetPos(hstmt,10,SQL_ADD,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage("rows affected:%d\n",nlen);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"select * from tmysql_setpos_add");
    mystmt(hstmt,rc);

    myassert(6 == myresult(hstmt));

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_add");

  return OK;
}


DECLARE_TEST(tmysql_pos_delete)
{
  SQLHSTMT hstmt1;
  SQLLEN rows;
  SQLCHAR buff[10];

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_pos_delete");
  ok_sql(hstmt, "CREATE TABLE tmysql_pos_delete (a INT, b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO tmysql_pos_delete VALUES (1,'venu'),(2,'MySQL')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu_cur", SQL_NTS));

  ok_sql(hstmt, "SELECT * FROM tmysql_pos_delete");

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL, NULL));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  expect_sql(hstmt1,
             "   DfffELETE FROM tmysql_pos_delete WHERE CURRENT OF venu_cur",
             SQL_ERROR);

  expect_sql(hstmt1,
             "   DELETE FROM tmysql_pos_delete WHERE CURRENT OF venu_cur curs",
             SQL_ERROR);

  expect_sql(hstmt1,
             "   DELETE FROM tmysql_pos_delete WHERE ONE CURRENT OF venu_cur",
             SQL_ERROR);

  ok_sql(hstmt1, "   DELETE FROM tmysql_pos_delete WHERE CURRENT OF venu_cur");

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rows));
  is_num(rows, 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM tmysql_pos_delete");
  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 1), 2);
  is_str(my_fetch_str(hstmt, buff, 2), "MySQL", 5);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_pos_delete");

  return OK;
}


DECLARE_TEST(t_pos_update)
{
  SQLHSTMT hstmt1;
  SQLCHAR  szData[10];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_update");
  ok_sql(hstmt, "CREATE TABLE t_pos_update (col1 INT, col2 VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO t_pos_update VALUES (100,'venu'),(200,'MySQL')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu_cur", SQL_NTS));

  ok_sql(hstmt, "SELECT * FROM t_pos_update");

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 2, NULL, NULL));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));

  expect_sql(hstmt1,
             "  UPerrDATE t_pos_update SET col1 = 999, col2 = 'update' "
             "WHERE CURRENT OF venu_cur",
             SQL_ERROR);

  expect_sql(hstmt1,
             "  UPDATE t_pos_update SET col1 = 999, col2 = 'update' "
             "WHERE CURRENT OF",
             SQL_ERROR);

  ok_sql(hstmt1,
         "  UPDATE t_pos_update SET col1 = 999, col2 = 'update' "
         "WHERE CURRENT OF venu_cur");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_stmt(hstmt1, SQLFreeHandle(SQL_HANDLE_STMT, hstmt1));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_pos_update");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 100);
  is_str(my_fetch_str(hstmt, szData, 2), "venu", 4);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 999);
  is_str(my_fetch_str(hstmt, szData, 2), "update", 5);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_update");

  return OK;
}


DECLARE_TEST(tmysql_pos_update_ex)
{
  SQLHSTMT hstmt1;
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;
  SQLLEN rows;
  SQLCHAR cursor[30], sql[255], data[]= "tmysql_pos_update_ex";

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex");
  ok_sql(hstmt, "CREATE TABLE t_pos_updex (a INT PRIMARY KEY, b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO t_pos_updex VALUES (100,'venu'),(200,'MySQL')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "SELECT * FROM t_pos_updex");

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 2,
                                  &pcrow, &rgfRowStatus));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLGetCursorName(hstmt, cursor, sizeof(cursor), NULL));

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));

  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                   SQL_CHAR, 0, 0, data, sizeof(data), NULL));

  sprintf((char *)sql,
          "UPDATE t_pos_updex SET a = 999, b = ? WHERE CURRENT OF %s",
          cursor);

  ok_stmt(hstmt1, SQLExecDirect(hstmt1, sql, SQL_NTS));

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rows));
  is_num(rows, 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_pos_updex");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 100);
  is_str(my_fetch_str(hstmt, sql, 2), "venu", 4);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 999);
  is_str(my_fetch_str(hstmt, sql, 2), "tmysql_pos_update_ex", 20);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex");

  return OK;
}


DECLARE_TEST(tmysql_pos_update_ex1)
{
  SQLHSTMT hstmt1;
  SQLULEN pcrow;
  SQLLEN rows;
  SQLUSMALLINT rgfRowStatus;
  SQLCHAR cursor[30], sql[100], data[]= "tmysql_pos_update_ex1";

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex1");
  ok_sql(hstmt, "CREATE TABLE t_pos_updex1  (a INT, b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO t_pos_updex1 VALUES (100,'venu'),(200,'MySQL')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "SELECT * FROM t_pos_updex1");

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 2, &pcrow,
                                  &rgfRowStatus));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLGetCursorName(hstmt, cursor, sizeof(cursor), NULL));

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));

  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                   SQL_CHAR, 0, 0, data, sizeof(data), NULL));

  sprintf((char *)sql,
          "UPDATE t_pos_updex1 SET a = 999, b = ? WHERE CURRENT OF %s", cursor);

  ok_stmt(hstmt1, SQLExecDirect(hstmt1, sql, SQL_NTS));

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rows));
  is_num(rows, 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_pos_updex1");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 100);
  is_str(my_fetch_str(hstmt, sql, 2), "venu", 4);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 999);
  is_str(my_fetch_str(hstmt, sql, 2), "tmysql_pos_update_ex1", 21);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex1");

  return OK;
}


DECLARE_TEST(tmysql_pos_update_ex3)
{
  SQLHSTMT hstmt1;
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;
  SQLCHAR cursor[30], sql[255];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex3");
  ok_sql(hstmt, "CREATE TABLE t_pos_updex3 (a INT NOT NULL PRIMARY KEY,"
        " b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO t_pos_updex3 VALUES (100,'venu'),(200,'MySQL')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt,  "SELECT a, b FROM t_pos_updex3");

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 2, &pcrow,
                                  &rgfRowStatus));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLGetCursorName(hstmt, cursor, sizeof(cursor), NULL));

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));

  sprintf((char *)sql,
          "UPDATE t_pos_updex3 SET a = 999, b = ? WHERE CURRENT OF %s", cursor);

  expect_stmt(hstmt1, SQLExecDirect(hstmt1, sql, SQL_NTS), SQL_ERROR);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex3");

  return OK;
}


DECLARE_TEST(tmysql_pos_update_ex4)
{
  SQLULEN pcrow;
  SQLLEN nlen= SQL_NTS;
  SQLCHAR data[]= "venu", szData[20];
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex4");
  ok_sql(hstmt, "CREATE TABLE t_pos_updex4 (a VARCHAR(20) NOT NULL,"
         "b VARCHAR(20) NOT NULL, c VARCHAR(5), PRIMARY KEY (b))");
  ok_sql(hstmt, "INSERT INTO t_pos_updex4 (a,b) VALUES ('Monty','Widenius')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "SELECT * FROM t_pos_updex4");

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 1, &pcrow,
                                  &rgfRowStatus));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &nlen));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT a FROM t_pos_updex4");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, szData, 1), "venu", 4);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_pos_updex4");

  return OK;
}


DECLARE_TEST(tmysql_pos_dyncursor)
{
  SQLHSTMT hstmt1;
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;
  SQLCHAR buff[100];
  SQLLEN rows;

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_pos_dyncursor");
  ok_sql(hstmt, "CREATE TABLE tmysql_pos_dyncursor (a INT, b VARCHAR(30))");
  ok_sql(hstmt, "INSERT INTO tmysql_pos_dyncursor VALUES (1,'foo'),(2,'bar')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu_cur", SQL_NTS));

  ok_sql(hstmt, "SELECT * FROM tmysql_pos_dyncursor");

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 2, &pcrow,
                                  &rgfRowStatus));

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));

  ok_sql(hstmt1, "UPDATE tmysql_pos_dyncursor SET a = 9, b = 'update' "
         "WHERE CURRENT OF venu_cur");

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rows));
  is_num(rows, 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM tmysql_pos_dyncursor");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);
  is_str(my_fetch_str(hstmt, buff, 2), "foo", 3);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 9);
  is_str(my_fetch_str(hstmt, buff, 2), "update", 6);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_pos_dyncursor");

  return OK;
}


DECLARE_TEST(tmysql_mtab_setpos_del)
{
    SQLRETURN rc;
    SQLINTEGER nData= 500;
    SQLLEN nlen;
    SQLCHAR szData[255]={0};
    SQLULEN pcrow;
    SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_t1, tmysql_t2");
    rc = tmysql_exec(hstmt,"create table tmysql_t1(col1 int, col2 varchar(20))");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"create table tmysql_t2(col1 int, col2 varchar(20))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_t1 values(1,'t1_one')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_t1 values(2,'t1_two')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_t1 values(3,'t1_three')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_t2 values(2,'t2_one')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_t2 values(3,'t2_two')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_t2 values(4,'t2_three')");
    mystmt(hstmt,rc);


    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"venu",SQL_NTS);
    mystmt(hstmt,rc);

    /* FULL JOIN */
    ok_sql(hstmt,"select tmysql_t1.*,tmysql_t2.* from tmysql_t1,tmysql_t2");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,100,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,100,&nlen);
    mystmt(hstmt,rc);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_ABSOLUTE,3,&pcrow,&rgfRowStatus);
    mystmt(hstmt,rc);

    printMessage(" pcrow:%d\n",pcrow);

    printMessage(" row1:%d,%s\n",nData,szData);

    rc = SQLSetPos(hstmt,1,SQL_POSITION,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    /* not yet supported..*/
    rc = SQLSetPos(hstmt,2,SQL_DELETE,SQL_LOCK_NO_CHANGE);
    mystmt_r(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_t1, tmysql_t2");

  return OK;
}


DECLARE_TEST(tmysql_setpos_pkdel)
{
    SQLRETURN rc;
    SQLINTEGER nData= 500;
    SQLLEN nlen;
    SQLCHAR szData[255]={0};
    SQLULEN pcrow;
    SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos1");

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = tmysql_exec(hstmt,"create table tmysql_setpos1(col1 int primary key, col2 varchar(30))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_setpos1 values(100,'MySQL1')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos1 values(200,'MySQL2')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos1 values(300,'MySQL3')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into tmysql_setpos1 values(400,'MySQL4')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"venu",SQL_NTS);
    mystmt(hstmt,rc);

    ok_sql(hstmt,"select * from tmysql_setpos1");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,100,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,100,&nlen);
    mystmt(hstmt,rc);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_ABSOLUTE,4,&pcrow,&rgfRowStatus);
    mystmt(hstmt,rc);

    printMessage(" pcrow:%d\n",pcrow);

    printMessage(" row1:%d,%s\n",nData,szData);

    rc = SQLSetPos(hstmt,1,SQL_POSITION,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLSetPos(hstmt,1,SQL_DELETE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage(" rows affected:%d\n",nlen);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"select * from tmysql_setpos1");
    mystmt(hstmt,rc);

    my_assert( 3 == myresult(hstmt));

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos1");

  return OK;
}


DECLARE_TEST(t_alias_setpos_pkdel)
{
  SQLINTEGER nData= 500;
  SQLLEN nlen;
  SQLCHAR szData[255]= {0};
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_alias_setpos_pkdel");

  ok_con(hdbc, SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT));

  ok_sql(hstmt,
         "CREATE TABLE t_alias_setpos_pkdel (col1 INT PRIMARY KEY,"
        " col2 VARCHAR(30))");

  ok_sql(hstmt, "INSERT INTO t_alias_setpos_pkdel VALUES (100, 'MySQL1')");
  ok_sql(hstmt, "INSERT INTO t_alias_setpos_pkdel VALUES (200, 'MySQL2')");
  ok_sql(hstmt, "INSERT INTO t_alias_setpos_pkdel VALUES (300, 'MySQL3')");
  ok_sql(hstmt, "INSERT INTO t_alias_setpos_pkdel VALUES (400, 'MySQL4')");

  ok_con(hdbc, SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_sql(hstmt,"SELECT col1 AS id, col2 AS name FROM t_alias_setpos_pkdel");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &nlen));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 1,
                                  &pcrow, &rgfRowStatus));

  printMessage("pcrow:%d, rgfRowStatus:%d", pcrow, rgfRowStatus);
  printMessage(" row1:%d, %s", nData, szData);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));

  printMessage("rows affected:%d",nlen);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_alias_setpos_pkdel");

  my_assert(3 == myresult(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_alias_setpos_pkdel");

  return OK;
}


DECLARE_TEST(t_alias_setpos_del)
{
  SQLINTEGER nData= 500;
  SQLLEN nlen;
  SQLCHAR szData[255]= {0};
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_alias_setpos_del");

  ok_con(hdbc, SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT));

  ok_sql(hstmt, "CREATE TABLE t_alias_setpos_del (col1 INT, col2 VARCHAR(30))");

  ok_sql(hstmt, "INSERT INTO t_alias_setpos_del VALUES (100, 'MySQL1')");
  ok_sql(hstmt, "INSERT INTO t_alias_setpos_del VALUES (200, 'MySQL2')");
  ok_sql(hstmt, "INSERT INTO t_alias_setpos_del VALUES (300, 'MySQL3')");
  ok_sql(hstmt, "INSERT INTO t_alias_setpos_del VALUES (400, 'MySQL4')");

  ok_con(hdbc, SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_sql(hstmt,"SELECT col1 AS id, col2 AS name FROM t_alias_setpos_del");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &nlen));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 1,
                                  &pcrow, &rgfRowStatus));

  printMessage("pcrow:%d, rgfRowStatus:%d", pcrow, rgfRowStatus);
  printMessage(" row1:%d, %s", nData, szData);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));

  printMessage("rows affected:%d",nlen);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_alias_setpos_del");

  my_assert(3 == myresult(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_alias_setpos_del");

  return OK;
}


DECLARE_TEST(tmysql_setpos_pkdel2)
{
  SQLINTEGER nData= 500;
  SQLLEN nlen;
  SQLCHAR szData[255]= {0};
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_pkdel2");
  ok_sql(hstmt, "CREATE TABLE tmysql_setpos_pkdel2 (a INT, b INT,"
         "c VARCHAR(30) PRIMARY KEY)");
  ok_sql(hstmt, "INSERT INTO tmysql_setpos_pkdel2 VALUES (100,10,'MySQL1'),"
         "(200,20,'MySQL2'),(300,30,'MySQL3'),(400,40,'MySQL4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_sql(hstmt, "SELECT b,c FROM tmysql_setpos_pkdel2");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            &nlen));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, 4,
                                  &pcrow, &rgfRowStatus));
  is_num(pcrow, 1);
  is_num(nData, 40);
  is_str(szData, "MySQL4", 6);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM tmysql_setpos_pkdel2");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 100);
  is_num(my_fetch_int(hstmt, 2), 10);
  is_str(my_fetch_str(hstmt, szData, 3), "MySQL1", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 200);
  is_num(my_fetch_int(hstmt, 2), 20);
  is_str(my_fetch_str(hstmt, szData, 3), "MySQL2", 6);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 300);
  is_num(my_fetch_int(hstmt, 2), 30);
  is_str(my_fetch_str(hstmt, szData, 3), "MySQL3", 6);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_setpos_pkdel2");

  return OK;
}


DECLARE_TEST(t_setpos_upd_bug1)
{
    SQLRETURN rc;
    SQLINTEGER id;
    SQLLEN len,id_len,f_len,l_len,ts_len;
    SQLCHAR fname[21],lname[21],szTable[256];
    SQL_TIMESTAMP_STRUCT ts;
    SQLSMALLINT pccol;
    SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_upd_bug1");
    rc = tmysql_exec(hstmt,"create table t_setpos_upd_bug1(id int(11) NOT NULL auto_increment,\
                                                           fname char(20) NOT NULL default '',\
                                                           lname char(20) NOT NULL default '',\
                                                           last_modi timestamp,\
                                                           PRIMARY KEY(id)) ENGINE=MyISAM");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_setpos_upd_bug1(fname,lname) values('joshua','kugler')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into t_setpos_upd_bug1(fname,lname) values('monty','widenius')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into t_setpos_upd_bug1(fname,lname) values('mr.','venu')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

    rc = tmysql_exec(hstmt,"select * from t_setpos_upd_bug1 order by id asc");
    mystmt(hstmt,rc);

    rc = SQLNumResultCols(hstmt,&pccol);
    mystmt(hstmt,rc);

    printMessage(" total columns:%d\n",pccol);

    rc = SQLBindCol(hstmt,1,SQL_C_SLONG,&id,4,&id_len);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,fname,6,&f_len);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,3,SQL_C_CHAR,lname,20,&l_len);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,4,SQL_C_TIMESTAMP,&ts,21,&ts_len);
    mystmt(hstmt,rc);

    rc = SQLColAttribute(hstmt,1,SQL_COLUMN_TABLE_NAME,szTable,sizeof(szTable),NULL,NULL);
    mystmt(hstmt,rc);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_FIRST,0,NULL,&rgfRowStatus);
    mystmt(hstmt,rc);

    rc = SQLSetStmtOption(hstmt,SQL_QUERY_TIMEOUT,30);
    mystmt(hstmt,rc);

    strcpy((char *)fname , "updated");
    strcpy((char *)lname , "updated01234567890");

    rc = SQLSetPos(hstmt,1,SQL_UPDATE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&len);
    mystmt(hstmt,rc);

    printMessage(" rows affected:%d\n",len);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"select * from t_setpos_upd_bug1");
    mystmt(hstmt,rc);

    myresult(hstmt);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    ok_sql(hstmt,"DELETE FROM t_setpos_upd_bug1 WHERE fname = 'update'");

    rc = SQLRowCount(hstmt,&len);
    mystmt(hstmt,rc);
    printMessage("\n total rows affceted:%d",len);
    my_assert(len == 1);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = tmysql_exec(hstmt,"select * from t_setpos_upd_bug1");
    mystmt(hstmt,rc);

    my_assert(2 == myresult(hstmt));

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_setpos_upd_bug1");

  return OK;
}


DECLARE_TEST(my_setpos_upd_pk_order)
{
    SQLRETURN rc;
    SQLINTEGER nData= 500;
    SQLLEN nlen;
    SQLCHAR szData[255]={0};
    SQLULEN pcrow;
    SQLUSMALLINT rgfRowStatus;

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_setpos_upd_pk_order");

    rc = tmysql_exec(hstmt,"create table my_setpos_upd_pk_order(col1 int not null, col2 varchar(30) NOT NULL, primary key(col2,col1))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into my_setpos_upd_pk_order values(100,'MySQL1')");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into my_setpos_upd_pk_order values(200,'MySQL2')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"venu",SQL_NTS);
    mystmt(hstmt,rc);

    ok_sql(hstmt,"select * from my_setpos_upd_pk_order");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,sizeof(szData),NULL);
    mystmt(hstmt,rc);

    rc = SQLExtendedFetch(hstmt,SQL_FETCH_ABSOLUTE,2,&pcrow,&rgfRowStatus);
    mystmt(hstmt,rc);

    printMessage(" row1:%d,%s\n",nData,szData);

    nData = 1000;
    strcpy((char *)szData , "updated");

    rc = SQLSetPos(hstmt,1,SQL_UPDATE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage(" rows affected:%d\n",nlen);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"select * from my_setpos_upd_pk_order");
    mystmt(hstmt,rc);

    myresult(hstmt);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "DELETE FROM my_setpos_upd_pk_order WHERE col2 = 'updated'");

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);
    printMessage("\n total rows affceted:%d",nlen);
    my_assert(nlen == 1);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_setpos_upd_pk_order");

  return OK;
}


/**
  In this test, we prove that we can update a row in a table with a
  multi-part primary key even though we're only updating two parts of
  the key.
 */
DECLARE_TEST(my_setpos_upd_pk_order1)
{
  SQLINTEGER nData;
  SQLCHAR szData[255];
  SQLULEN pcrow;
  SQLUSMALLINT rgfRowStatus;
  SQLLEN rows;

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_setpos_upd_pk_order1");
  ok_sql(hstmt, "CREATE TABLE my_setpos_upd_pk_order1 (a INT NOT NULL,"
         "b VARCHAR(30) NOT NULL, c INT NOT NULL, PRIMARY KEY (a,b,c))");
  ok_sql(hstmt, "INSERT INTO my_setpos_upd_pk_order1 VALUES (100,'MySQL1',1),"
         "(200,'MySQL2',2)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"venu", SQL_NTS));

  ok_sql(hstmt, "SELECT * FROM my_setpos_upd_pk_order1");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
                            NULL));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, &pcrow,
                                  &rgfRowStatus));
  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, &pcrow,
                                  &rgfRowStatus));

  nData= 1000;
  strcpy((char *)szData, "updated");

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLRowCount(hstmt, &rows));
  is_num(rows, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM my_setpos_upd_pk_order1");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 100);
  is_str(my_fetch_str(hstmt, szData, 2), "MySQL1", 6);
  is_num(my_fetch_int(hstmt, 3), 1);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1000);
  is_str(my_fetch_str(hstmt, szData, 2), "updated", 7);
  is_num(my_fetch_int(hstmt, 3), 2);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_setpos_upd_pk_order1");

  return OK;
}


DECLARE_TEST(tmy_cursor1)
{
  SQLCHAR getCurName[20];
  SQLSMALLINT getLen;

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"MYSQL", 5));
  ok_stmt(hstmt, SQLGetCursorName(hstmt, getCurName, 20, &getLen));
  is_str(getCurName, "MYSQL", 5);

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"MYSQL", 10));
  ok_stmt(hstmt, SQLGetCursorName(hstmt, getCurName, 20, &getLen));
  is_str(getCurName, "MYSQL", 5);

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"MYSQL", 2));
  ok_stmt(hstmt, SQLGetCursorName(hstmt, getCurName, 20, &getLen));
  is_str(getCurName, "MY", 2);

  return OK;
}


DECLARE_TEST(tmy_cursor2)
{
  SQLCHAR     getCursor[50]= {0};
  SQLSMALLINT getLen;

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"MYODBC", 6));

  expect_stmt(hstmt, SQLGetCursorName(hstmt, getCursor, 0, &getLen),
              SQL_SUCCESS_WITH_INFO);
  is_str(getCursor, "", 1);
  is_num(getLen, 6);

  expect_stmt(hstmt, SQLGetCursorName(hstmt, getCursor, -1, &getLen),
              SQL_ERROR);

  expect_stmt(hstmt, SQLGetCursorName(hstmt, getCursor, 4, &getLen),
              SQL_SUCCESS_WITH_INFO);
  is_str(getCursor, "MYO", 4);
  is_num(getLen, 6);

  expect_stmt(hstmt, SQLGetCursorName(hstmt, getCursor, 6, &getLen),
              SQL_SUCCESS_WITH_INFO);
  is_str(getCursor, "MYODB", 6);
  is_num(getLen, 6);

  ok_stmt(hstmt, SQLGetCursorName(hstmt, getCursor, 7, &getLen));
  is_str(getCursor, "MYODBC", 7);
  is_num(getLen, 6);

  return OK;
}


DECLARE_TEST(tmy_cursor3)
{
#if IODBC_BUG_FIXED
  /*
    iODBC has a bug that forces the ODBCv2 behavior of throwing an error
    when SQLSetCursorName() has not bee called and there is no open cursor.
  */
  SQLCHAR     getCursor[50];
  SQLSMALLINT getLen= -1;
  SQLHSTMT    hstmt1;

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"MYSQLODBC", 9));

  /* New statement should get its own (generated) cursor name. */
  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));
  ok_stmt(hstmt1, SQLGetCursorName(hstmt1, getCursor, 20, &getLen));
  is_str(getCursor, "SQL_CUR", 7);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
#endif

  return OK;
}


DECLARE_TEST(tmysql_pcbvalue)
{
  SQLCHAR    szdata[20], sztdata[100];
  SQLINTEGER nodata;
  SQLLEN     nlen, slen, tlen;
  SQLUSMALLINT rgfRowStatus[20];

  ok_sql(hstmt, "SET @@sql_mode=''");
  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_pcbvalue");

  ok_sql(hstmt,
         "CREATE TABLE tmysql_pcbvalue (col1 INT PRIMARY KEY,"
         "                              col2 VARCHAR(1), col3 TEXT)");
  ok_sql(hstmt, "INSERT INTO tmysql_pcbvalue VALUES (100,'venu','mysql')");
  ok_sql(hstmt, "INSERT INTO tmysql_pcbvalue VALUES (200,'monty','mysql')");

  ok_con(hdbc, SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt,"SELECT * FROM tmysql_pcbvalue");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nodata, 0, &nlen));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szdata, sizeof(szdata),
                            &slen));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_CHAR, sztdata, sizeof(sztdata),
                            &tlen));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_FIRST, 1, NULL,
                                  rgfRowStatus));

  printMessage("row1: %d(%d), %s(%d),%s(%d)\n",
               nodata, nlen, szdata, slen, sztdata, tlen);

  strcpy((char *)szdata, "updated-one");

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL, rgfRowStatus));

  printMessage("row2: %d(%d), %s(%d),%s(%d)\n",
               nodata, nlen, szdata, slen, sztdata, tlen);

  expect_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL,
                                      rgfRowStatus),
              SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_con(hdbc, SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT));

  ok_sql(hstmt, "SELECT * FROM tmysql_pcbvalue");

  ok_stmt(hstmt, SQLFetch(hstmt));

  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_CHAR, szdata, sizeof(szdata),
                            &slen));

  printMessage("updated data:%s(%d)\n",szdata,slen);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_pcbvalue");

  ok_con(hdbc, SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT));

  return OK;
}


/**
 Bug #28255: Cursor operations on result sets containing only part of a key
 are incorrect
*/
DECLARE_TEST(t_bug28255)
{
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug28255");
  ok_sql(hstmt, "CREATE TABLE t_bug28255 (a INT, b INT, PRIMARY KEY (a,b))");
  ok_sql(hstmt, "INSERT INTO t_bug28255 VALUES (1,3),(1,4),(1,5)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"bug", SQL_NTS));

  ok_sql(hstmt, "SELECT a FROM t_bug28255 WHERE b > 3");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));
  expect_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE),
              SQL_ERROR);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_bug28255");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);
  is_num(my_fetch_int(hstmt, 2), 3);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);
  is_num(my_fetch_int(hstmt, 2), 4);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);
  is_num(my_fetch_int(hstmt, 2), 5);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug28255");

  return OK;
}


/**
 Bug #10563: Update using multicolumn primary key with duplicate indexes fails
*/
DECLARE_TEST(bug10563)
{
  SQLLEN nlen;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug10563");
  ok_sql(hstmt, "CREATE TABLE t_bug10563 (a INT, b INT, PRIMARY KEY (a,b), UNIQUE (b))");
  ok_sql(hstmt, "INSERT INTO t_bug10563 VALUES (1,3),(1,4)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetCursorName(hstmt, (SQLCHAR *)"bug", SQL_NTS));

  ok_sql(hstmt, "SELECT b FROM t_bug10563 WHERE b > 3");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 4);

  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_POSITION, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nlen));
  is_num(nlen, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_bug10563");

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);
  is_num(my_fetch_int(hstmt, 2), 3);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug10563");

  return OK;
}


/*
 * Bug 6741 - SQL_ATTR_ROW_BIND_OFFSET_PTR is not supported
 * It was supported for use in some batch operations, but not
 * standard cursor operations.
 */
#define BUG6741_VALS 5

DECLARE_TEST(bug6741)
{
  int i;
  SQLLEN offset;
  struct {
    SQLINTEGER xval;
    SQLLEN ylen;
  } results[BUG6741_VALS];

  ok_sql(hstmt, "drop table if exists t_bug6741");
  ok_sql(hstmt, "create table t_bug6741 (x int, y int)");

  ok_sql(hstmt, "insert into t_bug6741 values (0,0),(1,NULL),(2,2),(3,NULL),(4,4)");
  ok_sql(hstmt, "select x,y from t_bug6741 order by x");

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_OFFSET_PTR,
          &offset, SQL_IS_POINTER));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &results[0].xval, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_LONG, NULL, 0, &results[0].ylen));

  /* fetch all the data */
  for(i = 0; i < BUG6741_VALS; ++i)
  {
    offset = i * sizeof(results[0]);
    ok_stmt(hstmt, SQLFetch(hstmt));
  }
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  /* verify it */
  for(i = 0; i < BUG6741_VALS; ++i)
  {
    printf("xval[%d] = %d\n", i, (int)results[i].xval);
    printf("ylen[%d] = %ld\n", i, (long)results[i].ylen);
    is_num(results[i].xval, i);
    if(i % 2)
    {
      is_num(results[i].ylen, SQL_NULL_DATA);
    }
    else
    {
      is_num(results[i].ylen, sizeof(SQLINTEGER));
    }
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "drop table if exists t_bug6741");

  return OK;
}


/*
  Test that the ARD (bound) type is used for the update and not
  the IRD (server-given) type.
*/
DECLARE_TEST(t_update_type)
{
  SQLUSMALLINT *val= malloc(sizeof(SQLUSMALLINT));

  ok_sql(hstmt, "drop table if exists t_update_no_strlen");
  ok_sql(hstmt, "create table t_update_no_strlen (x int not null)");
  ok_sql(hstmt, "insert into t_update_no_strlen values (0xaaaa)");
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "select * from t_update_no_strlen");
  /* server will use SQL_C_LONG, but we use short */
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_USHORT, val, 0, NULL));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(*val, 0xaaaa);

  *val= 0xcccc;
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* verify the right value was updated */
  *val= 0;
  ok_sql(hstmt, "select * from t_update_no_strlen");
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(*val, 0xcccc);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_update_no_strlen");

  return OK;
}


/*
  Test bind offset ptr and bind type for cursor update operations.
*/
DECLARE_TEST(t_update_offsets)
{
  SQLINTEGER rowcnt= 3;
  SQLINTEGER row_offset1= 5;
  /*
    TODO we should prob allow changing SQL_ATTR_ROW_BIND_OFFSET_PTR
    between SQLFetch() and SQLSetPos(). Setting a different value
    here will fail. (must be lower than row_offset1 anyways)
  */
  SQLINTEGER row_offset2= 5;
  struct {
    SQLINTEGER id;
    SQLCHAR name[24];
    SQLLEN namelen;
  } rows[8];
  size_t row_size= sizeof(rows[0]);
  SQLLEN bind_offset= -100000;
  SQLINTEGER i;
  SQLCHAR buf[50];

  ok_sql(hstmt, "drop table if exists t_update_offsets");
  ok_sql(hstmt, "create table t_update_offsets (id int not null, "
                "name varchar(50), primary key (id))");
  ok_sql(hstmt, "insert into t_update_offsets values "
                "(0, 'name0'),(1,'name1'),(2,'name2')");
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)((size_t)rowcnt), 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_TYPE,
                                (SQLPOINTER)row_size, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_OFFSET_PTR,
                                &bind_offset, 0));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &rows[0].id, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR,
                            &rows[0].name, 24, &rows[0].namelen));

  /* get the first block and verify it */
  ok_sql(hstmt, "select id,name from t_update_offsets order by id");

  bind_offset= row_size * row_offset1;
  ok_stmt(hstmt, SQLFetch(hstmt));

  for (i= 0; i < rowcnt; ++i)
  {
    sprintf((char *)buf, "name%d", i);
    is_num(rows[row_offset1+i].id, i);
    is_str(rows[row_offset1+i].name, buf, strlen((char *)buf) + 1);
    is_num(rows[row_offset1+i].namelen, strlen((char *)buf));

    /* change the values here */
    rows[row_offset2+i].id= i * 10;
    sprintf((char *)rows[row_offset2+i].name, "name_%d_%d", i, i * 10);
    rows[row_offset2+i].namelen= strlen((char *)rows[row_offset2+i].name);
  }

  /* update all rows */
  bind_offset= row_size * row_offset2;
  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_UPDATE, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* verify updates */
  memset(rows, 0, sizeof(rows));
  is_num(rows[0].id, 0);
  ok_sql(hstmt, "select id,name from t_update_offsets order by id");

  bind_offset= row_size;
  ok_stmt(hstmt, SQLFetch(hstmt));

  for (i= 0; i < rowcnt; ++i)
  {
    sprintf((char *)buf, "name_%d_%d", i, i * 10);
    is_num(rows[i+1].id, i * 10);
    is_str(rows[i+1].name, buf, strlen((char *)buf) + 1);
    is_num(rows[i+1].namelen, strlen((char *)buf));
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_update_offsets");

  return OK;
}


/**
 Bug #6157: BUG in the alias use with ADO's Object
*/
DECLARE_TEST(t_bug6157)
{
  SQLINTEGER data;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug6157");
  ok_sql(hstmt, "CREATE TABLE t_bug6157(a INT)");
  ok_sql(hstmt, "INSERT INTO t_bug6157 VALUES (1)");

  ok_sql(hstmt, "SELECT a AS b FROM t_bug6157");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &data, 0, NULL));

  ok_stmt(hstmt, SQLFetch(hstmt));

  data= 6157;
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  data= 9999;
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_ADD, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT a FROM t_bug6157 ORDER BY a");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &data, 0, NULL));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(data, 6157);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(data, 9999);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug6157");

  return OK;
}


/**
 Bug #32420: Don't cache results and SQLExtendedFetch ignore SQL_ROWSET_SIZE
 option
*/
DECLARE_TEST(t_bug32420)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  SQLINTEGER nData[4];
  SQLCHAR szData[4][16];
  SQLUSMALLINT rgfRowStatus[4];
  SQLULEN row_count;

  /* Don't cache result option in the connection string */
  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, USE_DRIVER,
                                        NULL, NULL, NULL, "NO_CACHE=1"));

  ok_sql(hstmt1, "drop table if exists bug32420");
  ok_sql(hstmt1, "CREATE TABLE bug32420 ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt1, "INSERT INTO bug32420 VALUES "\
                "(100, 'string 1'),"\
                "(200, 'string 2'),"\
                "(300, 'string 3'),"\
                "(400, 'string 4'),"\
                "(500, 'string 5'),"\
                "(600, 'string 6'),"\
                "(700, 'string 7'),"\
                "(800, 'string 8'),"\
                "(900, 'string 9'),"\
                "(910, 'string A'),"\
                "(920, 'string B')");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_ROWSET_SIZE, 4));

  ok_sql(hstmt1, "select * from bug32420");
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_NEXT, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 4);
  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);

  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_NEXT, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 4);
  is_num(nData[0], 500);
  is_str(szData[0], "string 5", 8);
  is_num(nData[1], 600);
  is_str(szData[1], "string 6", 8);
  is_num(nData[2], 700);
  is_str(szData[2], "string 7", 8);
  is_num(nData[3], 800);
  is_str(szData[3], "string 8", 8);

  /*
     Now checking the last records when the result is shorter than
     ROWSET_SIZE
  */
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_NEXT, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 3);
  is_num(nData[0], 900);
  is_str(szData[0], "string 9", 8);
  is_num(nData[1], 910);
  is_str(szData[1], "string A", 8);
  is_num(nData[2], 920);
  is_str(szData[2], "string B", 8);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_sql(hstmt1, "drop table if exists bug32420");

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  /*
     Result cache is enabled. Need to check that cached results are not
     broken
  */
  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, USE_DRIVER,
                                        NULL, NULL, NULL, NULL));

  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_CURSOR_TYPE,
                                 (SQLPOINTER) SQL_CURSOR_DYNAMIC, 0));
  ok_sql(hstmt1, "drop table if exists bug32420");
  ok_sql(hstmt1, "CREATE TABLE bug32420 ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt1, "INSERT INTO bug32420 VALUES "\
                "(100, 'string 1'),"\
                "(200, 'string 2'),"\
                "(300, 'string 3'),"\
                "(400, 'string 4'),"\
                "(500, 'string 5'),"\
                "(600, 'string 6'),"\
                "(700, 'string 7'),"\
                "(800, 'string 8'),"\
                "(900, 'string 9'),"\
                "(910, 'string A'),"\
                "(920, 'string B')");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_ROWSET_SIZE, 4));

  ok_sql(hstmt1, "select * from bug32420");
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_NEXT, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 4);
  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);

  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_NEXT, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 4);
  is_num(nData[0], 500);
  is_str(szData[0], "string 5", 8);
  is_num(nData[1], 600);
  is_str(szData[1], "string 6", 8);
  is_num(nData[2], 700);
  is_str(szData[2], "string 7", 8);
  is_num(nData[3], 800);
  is_str(szData[3], "string 8", 8);

  /*
     Now checking the last records when the result is shorter than
     ROWSET_SIZE
  */
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_NEXT, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 3);
  is_num(nData[0], 900);
  is_str(szData[0], "string 9", 8);
  is_num(nData[1], 910);
  is_str(szData[1], "string A", 8);
  is_num(nData[2], 920);
  is_str(szData[2], "string B", 8);

  /* Dynamic cursor allows fetching first records */
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_FIRST, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 4);
  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);

  /* Fetching last records */
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_LAST, 0, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 4);
  is_num(nData[0], 800);
  is_str(szData[0], "string 8", 8);
  is_num(nData[1], 900);
  is_str(szData[1], "string 9", 8);
  is_num(nData[2], 910);
  is_str(szData[2], "string A", 8);
  is_num(nData[3], 920);
  is_str(szData[3], "string B", 8);

  /* Fetching with absolute offset */
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_ABSOLUTE, 3, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 4);
  is_num(nData[0], 300);
  is_str(szData[0], "string 3", 8);
  is_num(nData[1], 400);
  is_str(szData[1], "string 4", 8);
  is_num(nData[2], 500);
  is_str(szData[2], "string 5", 8);
  is_num(nData[3], 600);
  is_str(szData[3], "string 6", 8);

  /* Fetching with relative offset */
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_RELATIVE, 2, &row_count,
                                   rgfRowStatus));

  is_num(row_count, 3);
  is_num(nData[0], 900);
  is_str(szData[0], "string 9", 8);
  is_num(nData[1], 910);
  is_str(szData[1], "string A", 8);
  is_num(nData[2], 920);
  is_str(szData[2], "string B", 8);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_sql(hstmt1, "drop table if exists bug32420");

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/*
  Shared between t_cursor_pos_static and t_cursor_pos_dynamic.
  Tests all the cursor position handling.
  Cursor type is setup by caller.
*/
int t_cursor_pos(SQLHANDLE hstmt)
{
  SQLINTEGER i;
  SQLINTEGER x[3];
  SQLINTEGER y[3];
  SQLINTEGER remaining_rows[]= {1, 5, 6, 7, 8};
  SQLINTEGER remaining_row_count= 5;

  ok_sql(hstmt, "drop table if exists t_cursor_pos");
  ok_sql(hstmt, "create table t_cursor_pos (x int not null, "
                "y int, primary key (x))");
  ok_sql(hstmt, "insert into t_cursor_pos values (0,0),(1,1),"
                "(2,2),(3,3),(4,4),  (5,5),(6,6),(7,7),  (8,8)");

  ok_sql(hstmt, "select x,y from t_cursor_pos order by 1");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, x, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_LONG, y, 0, NULL));
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));

  /* this covers bug#29765 and bug#33388 */
  is_num(x[0], 0);
  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(x[0], 1);

  y[0]++;
  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(x[0], 2);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PRIOR, 0));
  is_num(x[0], 1);

  /* and rowset tests */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)3, 0));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  for (i= 0; i < 3; ++i)
    is_num(x[i], 2 + i);

  /* delete 2,3,4 */
  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_DELETE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  for (i= 0; i < 3; ++i)
  {
    is_num(x[i], 5 + i);
    y[i]++;
  }

  /* update 5,6,7 */
  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  /* set rowset_size back to 1 */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_num(x[0], 8);
  y[0]++;
  ok_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  /* check all rows were updated correctly */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "select x,y from t_cursor_pos order by 1");
  for (i= 0; i < remaining_row_count; ++i)
  {
    ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
    is_num(x[0], remaining_rows[i]);
    is_num(y[0], x[0] + 1);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_cursor_pos");
  return OK;
}


/*
  Wrapper for t_cursor_pos using static cursor.
*/
DECLARE_TEST(t_cursor_pos_static)
{
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  return t_cursor_pos(hstmt);
}


/*
  Wrapper for t_cursor_pos using dynamic cursor.
*/
DECLARE_TEST(t_cursor_pos_dynamic)
{
  SQLHANDLE henv1, hdbc1, hstmt1;
  SET_DSN_OPTION(32);
  alloc_basic_handles(&henv1, &hdbc1, &hstmt1);
  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_CURSOR_TYPE,
                                 (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0));
  is_num(t_cursor_pos(hstmt1), OK);
  (void) free_basic_handles(&henv1, &hdbc1, &hstmt1);
  SET_DSN_OPTION(0);
  return OK;
}


/*
  Bug#11846 - DIAG [S1T00] Driver Failed to set the internal dynamic result
  Dynamic cursors on statements with parameters wasn't supported.
*/
DECLARE_TEST(t_bug11846)
{
  SQLINTEGER val_in= 4, val_out= 99;
  SQLHANDLE henv1, hdbc1, hstmt1;
  SET_DSN_OPTION(32);
  alloc_basic_handles(&henv1, &hdbc1, &hstmt1);

  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_CURSOR_TYPE,
                                 (SQLPOINTER)SQL_CURSOR_DYNAMIC,0));
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                   SQL_INTEGER, 0, 0, &val_in, 0, NULL));
  ok_sql(hstmt1, "select ?");

  ok_stmt(hstmt1, SQLFetch(hstmt1));

  ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_LONG, &val_out, 0, NULL));
  is_num(val_out, val_in);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  SET_DSN_OPTION(0);
  return OK;
}


/*
  Basic test of data-at-exec with SQLSetPos() insert.
*/
typedef struct {
  SQLINTEGER x, z;
  SQLCHAR y[11];
  SQLLEN ylen;
} t_dae_row;
DECLARE_TEST(t_dae_setpos_insert)
{
  SQLPOINTER holder= (SQLPOINTER)((size_t)0xcfcdcecc);
  SQLPOINTER paramptr;
  SQLLEN offset= 0;
  t_dae_row data[2];
  memset(data, 0, 2 * sizeof(t_dae_row));
  data[1].x= 20;
  data[1].z= 40;
  sprintf(data[1].y, "1234567890");
  data[1].ylen= SQL_LEN_DATA_AT_EXEC(10);

  ok_sql(hstmt, "drop table if exists t_dae");
  ok_sql(hstmt, "create table t_dae (x int not null, y varchar(5000), z int, "
                "primary key (x) )");
  ok_sql(hstmt, "select x, y, z from t_dae");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &data[0].x, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, holder, 10, &data[0].ylen));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_LONG, &data[0].z, 0, NULL));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_OFFSET_PTR,
          &offset, SQL_IS_POINTER));

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0), SQL_NO_DATA);

  offset= sizeof(t_dae_row);
  expect_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_ADD, SQL_LOCK_NO_CHANGE),
              SQL_NEED_DATA);
  expect_stmt(hstmt, SQLParamData(hstmt, &paramptr), SQL_NEED_DATA);
  is_num(paramptr, ((SQLCHAR *)holder + offset));
  ok_stmt(hstmt, SQLPutData(hstmt, data[1].y, 10));
  ok_stmt(hstmt, SQLParamData(hstmt, &paramptr));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  offset= 0;
  ok_sql(hstmt, "select x, y, z from t_dae");

  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, data[0].y, 11, &data[0].ylen));
  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(20, data[0].x);
  is_num(40, data[0].z);
  is_str(data[0].y, data[1].y, 11);
  is_num(10, data[0].ylen);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_dae");
  return OK;
}


/*
  Basic test of data-at-exec with SQLSetPos() update.
*/
DECLARE_TEST(t_dae_setpos_update)
{
  SQLINTEGER x= 20;
  SQLINTEGER z= 40;
  SQLCHAR *yval= (SQLCHAR *) "1234567890";
  SQLCHAR yout[11];
  SQLLEN xlen = 0, ylen= SQL_LEN_DATA_AT_EXEC(10), zlen = 0;
  SQLPOINTER holder= (SQLPOINTER)((size_t)0xcfcdcecc);
  SQLPOINTER paramptr;
  /* setup */
  ok_sql(hstmt, "drop table if exists t_dae");
  ok_sql(hstmt, "create table t_dae (x int not null, y varchar(5000), z int, "
                "primary key (x) )");
  ok_sql(hstmt, "insert into t_dae values (10, '9876', 30)");
  /* create cursor and get first row */
  ok_sql(hstmt, "select x, y, z from t_dae");

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));

  /* bind values for positioned update */
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &x, 0, &xlen));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_BINARY, holder, 10, &ylen));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_LONG, &z, 0, &zlen));

  /* perform update and provide data */
  expect_stmt(hstmt, SQLSetPos(hstmt, 0, SQL_UPDATE, SQL_LOCK_NO_CHANGE),
              SQL_NEED_DATA);
  expect_stmt(hstmt, SQLParamData(hstmt, &paramptr), SQL_NEED_DATA);
  is_num(paramptr, holder);
  ok_stmt(hstmt, SQLPutData(hstmt, yval, 10));
  ok_stmt(hstmt, SQLParamData(hstmt, &paramptr));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  x= 0;
  z= 0;
  ylen= 0;
  ok_sql(hstmt, "select x, y, z from t_dae");

  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, yout, 11, &ylen));
  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(20, x);
  is_num(40, z);
  is_str(yval, yout, 11);
  is_num(10, ylen);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_dae");
  return OK;
}


/*
  Bug #39951 - Positioned update with SQL_C_NUMERIC loses prec/scale values
*/
DECLARE_TEST(t_bug39961)
{
  SQL_NUMERIC_STRUCT num;
  SQLHANDLE ard;
  SQLCHAR buf[10];
  SQLINTEGER id;

  ok_sql(hstmt, "drop table if exists t_bug39961");
  ok_sql(hstmt, "create table t_bug39961(id int not null, m1 decimal(19, 4), "
	 "primary key (id))");
  ok_sql(hstmt, "insert into t_bug39961 values (1, 987)");
  ok_sql(hstmt, "select id, m1 from t_bug39961");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &id, 0, NULL));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC, &ard, 0, NULL));
  ok_stmt(hstmt, SQLSetDescField(ard, 2, SQL_DESC_CONCISE_TYPE,
				 (SQLPOINTER) SQL_C_NUMERIC, SQL_IS_INTEGER));
  ok_stmt(hstmt, SQLSetDescField(ard, 2, SQL_DESC_PRECISION, (SQLPOINTER) 19,
				 SQL_IS_INTEGER));
  ok_stmt(hstmt, SQLSetDescField(ard, 2, SQL_DESC_SCALE, (SQLPOINTER) 2,
				 SQL_IS_INTEGER));
  ok_stmt(hstmt, SQLSetDescField(ard, 2, SQL_DESC_DATA_PTR, &num,
				 SQL_IS_POINTER));
  ok_stmt(hstmt, SQLFetch(hstmt));

  /* set value to .1 */
  num.val[0]= 10;
  num.val[1]= 0;
  num.val[2]= 0;
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  /* add a new row */
  id++;
  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* fetch both rows, they should have the same decimal value */
  ok_sql(hstmt, "select m1 from t_bug39961");
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buf, 1), "0.1000", 4);
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buf, 1), "0.1000", 4);
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "drop table if exists t_bug39961");
  return OK;
}


/*
Bug #41946: Inserting a row using SQLSetPos does not correspond to
DB name in SELECT
*/
DECLARE_TEST(t_bug41946)
{
	SQLINTEGER nData= 500;
	SQLCHAR szData[255]={0};

	ok_sql(hstmt, "DROP DATABASE IF EXISTS other_test_db");
	ok_sql(hstmt, "CREATE DATABASE other_test_db");

	ok_sql(hstmt, "CREATE TABLE other_test_db.t_41946(Id int NOT NULL,\
				  Name varchar(32),\
				  PRIMARY KEY  (Id))");

	ok_sql(hstmt, "select * from other_test_db.t_41946");

	ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 5, NULL));

	ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, 11, NULL));

	expect_stmt(hstmt, SQLFetchScroll(hstmt,SQL_FETCH_NEXT,1), SQL_NO_DATA);

	nData= 33;
	strcpy((char *)szData , "insert-new");

	ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_ADD, SQL_LOCK_NO_CHANGE));

	nData= 0;
	strcpy((char *)szData , "something else");

  /* We have to close the cursor before issuing next sql query */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "select * from other_test_db.t_41946");

	ok_stmt(hstmt, SQLFetch(hstmt));

	is_num(nData, 33);
	is_str(szData, "insert-new", 11);

	ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

	ok_sql(hstmt, "DROP DATABASE IF EXISTS other_test_db");

	return OK;
}


/**
 Bug#14810497: SQLPUTDATA DON'T HANDLE UNICODE STRING WITH SQL_NTS IN
               STRING LENGTH.
*/
DECLARE_TEST(t_sqlputdata)
{
  SQLRETURN rc;
  SQLINTEGER  id, i;
  SQLLEN resId = 0, resData;
  SQLWCHAR wbuff[MAX_ROW_DATA_LEN+1];
  SQLWCHAR *wcdata= W(L"S\x00e3o Paolo");

  ok_sql(hstmt, "drop table if exists t_sqlputdata");
  ok_sql(hstmt, "CREATE TABLE t_sqlputdata( id INT, pdata varchar(50));");

  ok_stmt(hstmt, SQLPrepare(hstmt, "INSERT INTO t_sqlputdata VALUES ( ?, ?)", SQL_NTS));
  id= 1;
  resData = SQL_LEN_DATA_AT_EXEC(0);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_SLONG,
                        SQL_INTEGER, 0, 0, &id, 0, &resId));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT,
                        SQL_C_WCHAR, SQL_WCHAR, 0, 0, (SQLPOINTER)1,
                        0, &resData));

  rc = SQLExecute(hstmt);
  if (rc == SQL_NEED_DATA)
  {
    SQLPOINTER parameter;
    if (SQLParamData(hstmt, &parameter) == SQL_NEED_DATA
        && parameter == (SQLPOINTER)1)
    {
      ok_stmt(hstmt, SQLPutData(hstmt, wcdata,  SQL_NTS));
      ok_stmt(hstmt, SQLParamData(hstmt, &parameter));
    }
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));

  ok_sql(hstmt, "select id, pdata from t_sqlputdata");
  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 1), 1);
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_WCHAR, wbuff,
                             sizeof(wbuff), NULL));
  /* we want to compare SQLWCHAR instead of wchar_t */
  for (i= 0; i < 9; i++)
  {
    is(wbuff[i] == wcdata[i]);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_sqlputdata");

  return OK;
}


/*
  Bug #18805455: VALGRIND: MEMORY LEAK IN SQLSETPOS()
*/
DECLARE_TEST(t_18805455)
{
  SQLCHAR     buff[10];
  SQLCHAR     buff1[10];
  SQLLEN      nRowCount;

  ok_sql(hstmt, "drop table if exists t_18805455");
  ok_sql(hstmt, "create table t_18805455 (val char(10))");
  ok_sql(hstmt, "insert into t_18805455 values ('value11')");

  /* create cursor and get first row */
  ok_stmt(hstmt, SQLPrepare(hstmt, "select * from t_18805455 "
                                   "where val = ?", SQL_NTS));
  strcpy(buff, "value11");
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                        SQL_CHAR, 10, 0, &buff, 10, NULL));
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, buff1, sizeof(buff1), NULL));
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0));
  is_str(buff1, "value11", 7);

  strcpy(buff1, "value12");
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(nRowCount, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  /* Now fetch and verify the data */
  ok_sql(hstmt, "SELECT * FROM t_18805455");
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 1), "value12", 7);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_18805455");
  return OK;
}


BEGIN_TESTS
  ADD_TEST(my_positioned_cursor)
  ADD_TEST(my_setpos_cursor)
  ADD_TEST(t_bug5853)
  ADD_TEST(t_setpos_del_all)
  ADD_TEST(t_setpos_upd_decimal)
  ADD_TEST(t_setpos_position)
  ADD_TEST(t_pos_column_ignore)
  ADD_TEST(t_pos_datetime_delete)
  ADD_TEST(t_getcursor)
  ADD_TEST(t_getcursor1)
  ADD_TEST(t_acc_crash)
  ADD_TEST(tmysql_setpos_del)
  ADD_TEST(tmysql_setpos_del1)
  ADD_TEST(tmysql_pos_delete)
  ADD_TEST(t_pos_update)
  ADD_TEST(tmysql_pos_update_ex)
  ADD_TEST(tmysql_pos_update_ex1)
  ADD_TEST(tmysql_pos_update_ex3)
  ADD_TEST(tmysql_pos_update_ex4)
  ADD_TEST(tmysql_pos_dyncursor)
  ADD_TEST(tmysql_mtab_setpos_del)
  ADD_TEST(tmysql_setpos_pkdel2)
  ADD_TEST(t_pos_datetime_delete1)
  ADD_TEST(tmysql_setpos_upd)
  ADD_TEST(tmysql_setpos_add)
  ADD_TEST(tmysql_setpos_pkdel)
  ADD_TEST(t_alias_setpos_pkdel)
  ADD_TEST(t_alias_setpos_del)
  ADD_TEST(t_setpos_upd_bug1)
  ADD_TEST(my_setpos_upd_pk_order)
  ADD_TEST(my_setpos_upd_pk_order1)
  ADD_TEST(tmy_cursor1)
  ADD_TEST(tmy_cursor2)
  ADD_TEST(tmy_cursor3)
  ADD_TEST(tmysql_pcbvalue)
  ADD_TEST(t_bug28255)
  ADD_TEST(bug10563)
  ADD_TEST(bug6741)
  ADD_TEST(t_update_type)
  ADD_TEST(t_update_offsets)
  ADD_TEST(t_bug6157)
  ADD_TEST(t_cursor_pos_static)
  ADD_TEST(t_cursor_pos_dynamic)
  ADD_TEST(t_bug11846)
  ADD_TEST(t_dae_setpos_insert)
  ADD_TEST(t_dae_setpos_update)
  ADD_TEST(t_bug39961)
  ADD_TEST(t_bug41946)
  ADD_TEST(t_18805455)
  /*ADD_TEST(t_sqlputdata)*/
END_TESTS


RUN_TESTS
