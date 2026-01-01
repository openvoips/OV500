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
DECLARE_TEST(my_dynamic_pos_cursor)
{
    SQLRETURN   rc;
    SQLLEN      nRowCount;
    SQLHSTMT    hstmt_pos;
    SQLINTEGER  nData = 500;
    SQLCHAR     szData[255]={0};

    /* initialize data */
    ok_sql(hstmt, "drop table if exists my_dynamic_cursor");

    ok_sql(hstmt, "create table my_dynamic_cursor(id int, name varchar(30))");

    ok_sql(hstmt, "insert into my_dynamic_cursor values(100,'venu')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(200,'monty')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    /* create new statement handle */
    rc = SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt_pos);
    mycon(hdbc, rc);

    /* set the cursor name as 'mysqlcur' on hstmt */
    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"mysqlcur", SQL_NTS);
    mystmt(hstmt, rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,15,NULL);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY ,(SQLPOINTER)SQL_CONCUR_ROWVER , 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)1 , 0);
    mystmt(hstmt, rc);

    /* Open the resultset of table 'my_demo_cursor' */
    ok_sql(hstmt, "SELECT * FROM my_dynamic_cursor");
    mystmt(hstmt,rc);

    /* goto the last row */
    rc = SQLFetchScroll(hstmt, SQL_FETCH_LAST, 1L);
    mystmt(hstmt,rc);

    /* now update the name field to 'update' using positioned cursor */
    ok_sql(hstmt_pos, "UPDATE my_dynamic_cursor SET id=300, name='updated' WHERE CURRENT OF mysqlcur");

    rc = SQLRowCount(hstmt_pos, &nRowCount);
    mystmt(hstmt_pos, rc);

    printMessage(" total rows updated:%d\n",nRowCount);
    is_num(nRowCount, 1);

    /* Now delete the newly updated record */
    strcpy((char*)szData,"updated");
    nData = 300;

    rc = SQLSetPos(hstmt,1,SQL_DELETE,SQL_LOCK_UNLOCK);
    mystmt_err(hstmt,rc==SQL_ERROR,rc);

    rc = SQLSetPos(hstmt,1,SQL_DELETE,SQL_LOCK_EXCLUSIVE);
    mystmt_err(hstmt,rc==SQL_ERROR,rc);

    rc = SQLSetPos(hstmt,1,SQL_DELETE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt, &nRowCount);
    mystmt(hstmt, rc);

    printMessage(" total rows deleted:%d\n",nRowCount);
    is_num(nRowCount, 1);

    /* Free statement cursor resorces */
    rc = SQLFreeStmt(hstmt, SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt_pos, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* commit the transaction */
    rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);

    /* Free the statement 'hstmt_pos' */
    rc = SQLFreeHandle(SQL_HANDLE_STMT, hstmt_pos);
    mystmt(hstmt_pos,rc);

    /* Now fetch and verify the data */
    ok_sql(hstmt, "SELECT * FROM my_dynamic_cursor");

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);
    is_num(nData, 100);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,szData,50,NULL);
    mystmt(hstmt,rc);
    is_str(szData,"venu", 5);

    rc = SQLFetch(hstmt);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_dynamic_cursor");

  return OK;
}


/* perform positioned update and delete */
DECLARE_TEST(my_dynamic_pos_cursor1)
{
    SQLRETURN   rc;
    SQLLEN      nRowCount;
    SQLHSTMT    hstmt_pos;
    SQLINTEGER  i,nData[15];
    char        data[30],szData[15][10]={0};

    /* initialize data */
    ok_sql(hstmt, "drop table if exists my_dynamic_cursor");

    ok_sql(hstmt, "create table my_dynamic_cursor(id int, name varchar(30))");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(1,'MySQL1')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(2,'MySQL2')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(3,'MySQL3')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(4,'MySQL4')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(5,'MySQL5')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(6,'MySQL6')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(7,'MySQL7')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(8,'MySQL8')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(9,'MySQL9')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(10,'MySQL10')");

    SQLFreeStmt(hstmt,SQL_CLOSE);

    /* create new statement handle */
    rc = SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt_pos);
    mycon(hdbc, rc);

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

    /* set the cursor name as 'mysqlcur' on hstmt */
    rc = SQLSetCursorName(hstmt, (SQLCHAR *)"mysqlcur", SQL_NTS);
    mystmt(hstmt, rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,20,NULL);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)3,0);
    mystmt(hstmt,rc);

    /* Open the resultset of table 'my_demo_cursor' */
    ok_sql(hstmt, "SELECT * FROM my_dynamic_cursor");

    /* goto the last row */
    rc = SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 5L);
    mystmt(hstmt,rc);

    /*rc = SQLSetPos(hstmt,SQL_POSITION,2,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc); */

    /* now update the name field to 'update' using positioned cursor */
    ok_sql(hstmt_pos, "UPDATE my_dynamic_cursor SET id=999, name='updated' WHERE CURRENT OF mysqlcur");

    rc = SQLRowCount(hstmt_pos, &nRowCount);
    mystmt(hstmt_pos, rc);

    printMessage(" total rows updated:%d\n",nRowCount);
    is_num(nRowCount, 1);
    strcpy(szData[1],"updated");
    nData[1] = 999;

    rc = SQLSetPos(hstmt,2,SQL_DELETE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt, &nRowCount);
    mystmt(hstmt, rc);

    printMessage(" total rows deleted:%d\n",nRowCount);
    is_num(nRowCount, 1);

    /* Free statement cursor resorces */
    rc = SQLFreeStmt(hstmt, SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt_pos, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* commit the transaction */
    rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);

    /* Free the statement 'hstmt_pos' */
    rc = SQLFreeHandle(SQL_HANDLE_STMT, hstmt_pos);
    mystmt(hstmt_pos,rc);

    /* Now fetch and verify the data */
    rc = SQLSetStmtAttr(hstmt,SQL_ATTR_ROW_ARRAY_SIZE,(SQLPOINTER)1,0);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM my_dynamic_cursor");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&i,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR, data,20,NULL);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,4L);
    mystmt(hstmt,rc);

    is_num(i, 4);
    is_str(data,"MySQL4", 7);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,1L);
    mystmt(hstmt,rc);

    is_num(i, 999);
    is_str(data, "updated", 8);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,1L);
    mystmt(hstmt,rc);

    is_num(i, 7);
    is_str(data, "MySQL7", 7);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,10L);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_dynamic_cursor");

  return OK;
}


/* CURSOR POSITION - rowset size 1 */
DECLARE_TEST(my_position)
{
    SQLRETURN rc;
    SQLLEN    nlen;
    char      szData[255]= {0};
    SQLINTEGER nData;
    SQLLEN    nrow;

    ok_sql(hstmt, "drop table if exists my_position");
    ok_sql(hstmt, "create table my_position(col1 int, col2 varchar(30))");

    ok_sql(hstmt, "insert into my_position values(100,'MySQL1')");
    ok_sql(hstmt, "insert into my_position values(200,'MySQL2')");
    ok_sql(hstmt, "insert into my_position values(300,'MySQL3')");
    ok_sql(hstmt, "insert into my_position values(400,'MySQL4')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY ,(SQLPOINTER)SQL_CONCUR_ROWVER , 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)1 , 0);
    mystmt(hstmt, rc);

    ok_sql(hstmt,"select * from my_position");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,&nrow);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,10,&nlen);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,3);
    mystmt(hstmt,rc);

    nData = 999; nrow = SQL_COLUMN_IGNORE;
    strcpy(szData,"update");

    rc = SQLSetPos(hstmt,1,SQL_POSITION,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLSetPos(hstmt,1,SQL_UPDATE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt,&nlen);
    mystmt(hstmt,rc);

    printMessage(" rows affected:%d\n",nlen);
    is_num(nlen, 1);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from my_position");
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);
    rc = SQLGetData(hstmt,2,SQL_C_CHAR,szData,10,NULL);
    mystmt(hstmt,rc);
    is_num(nData, 300);
    is_str(szData, "update", 7);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_position");

  return OK;
}


/* CURSOR POSITION - rowset size 3 */
DECLARE_TEST(my_position1)
{
  SQLINTEGER nData[15];
  SQLLEN    nlen[15]= {0}, nrow[15]= {0};
  SQLCHAR   szData[15][15]= {0};

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_position");
  ok_sql(hstmt, "CREATE TABLE my_position (col1 INT, col2 VARCHAR(30))");

  ok_sql(hstmt, "INSERT INTO my_position VALUES (1,'MySQL1'), (2,'MySQL2'),"
         "(3,'MySQL3'), (4,'MySQL4'), (5,'MySQL5'), (6,'MySQL6'), (7,'MySQL7'),"
         "(8,'MySQL8'), (9,'MySQL9'), (10,'MySQL10'), (11,'MySQL11'),"
         "(12,'MySQL12')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)3, 0));

  ok_sql(hstmt, "SELECT * FROM my_position");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, nrow));

  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            nlen));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 4));

  nData[0]= 888;
  nData[1]= 999;
  nrow[1]= SQL_COLUMN_IGNORE;
  nData[2]= 1000;

  strcpy((char *)szData[0], "updatex");
  nlen[0]= 15;
  strcpy((char *)szData[1], "updatey");
  nlen[1]= 15;
  strcpy((char *)szData[2], "updatez");
  nlen[2]= 15;

  ok_stmt(hstmt, SQLSetPos(hstmt, 2, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLSetPos(hstmt, 3, SQL_UPDATE, SQL_LOCK_NO_CHANGE));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM my_position");

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 4));

  is_num(nData[0], 4);
  is_str(szData[0], "MySQL4", 6);
  is_num(nData[1], 5);
  is_str(szData[1], "updatey", 7);
  is_num(nData[2], 1000);
  is_str(szData[2], "updatez", 7);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_position");

  return OK;
}


/* IROW VALUE - 0 */
DECLARE_TEST(my_zero_irow_update)
{
    SQLRETURN rc;
    SQLLEN    nlen[15]= {0}, nrow[15]= {0};
    char      szData[15][15]={0};
    SQLINTEGER nData[15];

    ok_sql(hstmt, "drop table if exists my_zero_irow");
    ok_sql(hstmt, "create table my_zero_irow(col1 int, col2 varchar(30))");

    ok_sql(hstmt, "insert into my_zero_irow values(1,'MySQL1')");
    ok_sql(hstmt, "insert into my_zero_irow values(2,'MySQL2')");
    ok_sql(hstmt, "insert into my_zero_irow values(3,'MySQL3')");
    ok_sql(hstmt, "insert into my_zero_irow values(4,'MySQL4')");
    ok_sql(hstmt, "insert into my_zero_irow values(5,'MySQL5')");
    ok_sql(hstmt, "insert into my_zero_irow values(6,'MySQL6')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY ,(SQLPOINTER)SQL_CONCUR_ROWVER , 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)3 , 0);
    mystmt(hstmt, rc);

    ok_sql(hstmt, "select * from my_zero_irow");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,nrow);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,sizeof(szData[0]),nlen);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);
    mystmt(hstmt,rc);

    nData[0] = 888;
    nData[1] = 999; nrow[1] = SQL_COLUMN_IGNORE;
    nData[2] = 1000;

    strcpy(szData[0],"updatex"); nlen[0] = 15;
    strcpy(szData[1],"updatey"); nlen[1] = 15;
    strcpy(szData[2],"updatez"); nlen[2] = 15;

    rc = SQLSetPos(hstmt,0,SQL_UPDATE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from my_zero_irow");
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);
    mystmt(hstmt,rc);

    is_num(nData[0], 888);
    is_str(szData[0], "updatex", 8);
    is_num(nData[1], 3);
    is_str(szData[1], "updatey", 8);
    is_num(nData[2], 1000);
    is_str(szData[2], "updatez", 8);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)1 , 0);
    mystmt(hstmt, rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_zero_irow");

  return OK;
}


/* IROW VALUE - 0 - DELETE */
DECLARE_TEST(my_zero_irow_delete)
{
    SQLRETURN rc;
    SQLLEN    nlen[15]= {0}, nrow[15]= {0};
    char      szData[15][15]={0};
    SQLINTEGER nData[15];

    ok_sql(hstmt, "drop table if exists my_zero_irow");
    ok_sql(hstmt, "create table my_zero_irow(col1 int, col2 varchar(30))");
    ok_sql(hstmt, "insert into my_zero_irow values(1,'MySQL1')");
    ok_sql(hstmt, "insert into my_zero_irow values(2,'MySQL2')");
    ok_sql(hstmt, "insert into my_zero_irow values(3,'MySQL3')");
    ok_sql(hstmt, "insert into my_zero_irow values(4,'MySQL4')");
    ok_sql(hstmt, "insert into my_zero_irow values(5,'MySQL5')");
    ok_sql(hstmt, "insert into my_zero_irow values(6,'MySQL6')");


    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY ,(SQLPOINTER)SQL_CONCUR_ROWVER , 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)3 , 0);
    mystmt(hstmt, rc);

    ok_sql(hstmt,"select * from my_zero_irow");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,nrow);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,sizeof(szData[0]),nlen);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,2);
    mystmt(hstmt,rc);

    rc = SQLSetPos(hstmt,0,SQL_DELETE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "select * from my_zero_irow");

    rc = SQLFetchScroll(hstmt,SQL_FETCH_ABSOLUTE,1);
    mystmt(hstmt,rc);

    is_num(nData[0], 1);
    is_str(szData[0], "MySQL1", 7);
    is_num(nData[1], 5);
    is_str(szData[1], "MySQL5", 7);
    is_num(nData[2], 6);
    is_str(szData[2], "MySQL6", 7);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,1);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)1 , 0);
    mystmt(hstmt, rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_zero_irow");

  return OK;
}


/* DYNAMIC CURSOR TESTING */
DECLARE_TEST(my_dynamic_cursor)
{
    SQLRETURN rc;
    SQLLEN nlen;
    SQLINTEGER nData = 500;
    SQLCHAR szData[255]={0};

    /* initialize data */
    ok_sql(hstmt, "drop table if exists my_dynamic_cursor");

    ok_sql(hstmt, "create table my_dynamic_cursor(col1 int, col2 varchar(30))");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(100,'venu')");
    ok_sql(hstmt, "insert into my_dynamic_cursor values(200,'monty')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY , (SQLPOINTER)SQL_CONCUR_ROWVER , 0);
    mystmt(hstmt, rc);

    /* Now, add a row of data */
    ok_sql(hstmt, "select * from my_dynamic_cursor");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,15,NULL);
    mystmt(hstmt,rc);

    rc = SQLFetchScroll(hstmt,SQL_FETCH_NEXT,1);
    mystmt(hstmt,rc);

    nData = 300;
    strcpy((char *)szData , "mysql");

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

    ok_sql(hstmt, "select * from my_dynamic_cursor");
    mystmt(hstmt,rc);

    is_num(myresult(hstmt), 6);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_dynamic_cursor");

  return OK;
}


BEGIN_TESTS
  ADD_TEST(my_dynamic_pos_cursor)
  ADD_TEST(my_dynamic_pos_cursor1)
  ADD_TEST(my_position)
  ADD_TEST(my_position1)
  ADD_TEST(my_zero_irow_update)
  ADD_TEST(my_zero_irow_delete)
  ADD_TEST(my_dynamic_cursor)
END_TESTS

SET_DSN_OPTION(35);

RUN_TESTS
