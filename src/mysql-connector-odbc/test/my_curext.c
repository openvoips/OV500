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

DECLARE_TEST(my_pcbvalue)
{
    SQLRETURN   rc;
    SQLLEN      nRowCount;
    SQLINTEGER  nData= 500;
    SQLLEN      int_pcbValue, pcbValue, pcbValue1, pcbValue2;
    SQLCHAR     szData[255]={0};

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_pcbValue");

  ok_sql(hstmt, "create table my_pcbValue(id int, name varchar(30),\
                                                       name1 varchar(30),\
                                                       name2 varchar(30))");

  ok_sql(hstmt, "insert into my_pcbValue(id,name) values(100,'venu')");

  ok_sql(hstmt, "insert into my_pcbValue(id,name) values(200,'monty')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,&int_pcbValue);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,15,&pcbValue);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,3,SQL_C_CHAR,szData,3,&pcbValue1);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,4,SQL_C_CHAR,szData,2,&pcbValue2);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY ,(SQLPOINTER)SQL_CONCUR_ROWVER , 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)1 , 0);
    mystmt(hstmt, rc);

    /* Open the resultset of table 'my_demo_cursor' */
    ok_sql(hstmt, "SELECT * FROM my_pcbValue");

    /* goto the last row */
    rc = SQLFetchScroll(hstmt, SQL_FETCH_LAST, 1L);
    mystmt(hstmt,rc);

    /* Now delete the newly updated record */
    strcpy((char*)szData,"updated");
    nData = 99999;

    int_pcbValue=2;
    pcbValue=3;
    pcbValue1=9;
    pcbValue2=SQL_NTS;

    rc = SQLSetPos(hstmt,1,SQL_UPDATE,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt, &nRowCount);
    mystmt(hstmt, rc);

    printMessage(" total rows updated:%d\n",nRowCount);
    is_num(nRowCount, 1);

    /* Free statement cursor resorces */
    rc = SQLFreeStmt(hstmt, SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* commit the transaction */
    rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);

    /* Now fetch and verify the data */
    ok_sql(hstmt, "SELECT * FROM my_pcbValue");

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);
    is_num(nData, 99999);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,szData,50,NULL);
    mystmt(hstmt,rc);
    is_str(szData, "upd", 4);

    rc = SQLGetData(hstmt,3,SQL_C_CHAR,szData,50,NULL);
    mystmt(hstmt,rc);
    is_str(szData, "updated", 8);

    rc = SQLGetData(hstmt,4,SQL_C_CHAR,szData,50,NULL);
    mystmt(hstmt,rc);
    is_str(szData, "updated", 8);

    expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_pcbValue");

  return OK;
}


/* to test the pcbValue on cursor ops **/
DECLARE_TEST(my_pcbvalue_add)
{
    SQLRETURN   rc;
    SQLLEN      nRowCount;
    SQLINTEGER  nData= 500;
    SQLLEN      int_pcbValue, pcbValue, pcbValue1, pcbValue2;
    SQLCHAR     szData[255]={0};

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_pcbValue_add");

  ok_sql(hstmt, "create table my_pcbValue_add(id int, name varchar(30),\
                                                       name1 varchar(30),\
                                                       name2 varchar(30))");

  ok_sql(hstmt,"insert into my_pcbValue_add(id,name) values(100,'venu')");

  ok_sql(hstmt,"insert into my_pcbValue_add(id,name) values(200,'monty')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nData,0,&int_pcbValue);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szData,15,&pcbValue);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,3,SQL_C_CHAR,szData,3,&pcbValue1);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,4,SQL_C_CHAR,szData,2,&pcbValue2);
    mystmt(hstmt,rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE, (SQLPOINTER)SQL_CURSOR_DYNAMIC, 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY ,(SQLPOINTER)SQL_CONCUR_ROWVER , 0);
    mystmt(hstmt, rc);

    rc = SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE  ,(SQLPOINTER)1 , 0);
    mystmt(hstmt, rc);

    /* Open the resultset of table 'my_pcbValue_add' */
    ok_sql(hstmt, "SELECT * FROM my_pcbValue_add");
    mystmt(hstmt,rc);

    /* goto the last row */
    rc = SQLFetchScroll(hstmt, SQL_FETCH_LAST, 1L);
    mystmt(hstmt,rc);

    /* Now delete the newly updated record */
    strcpy((char*)szData,"inserted");
    nData = 99999;

    int_pcbValue=2;
    pcbValue=3;
    pcbValue1=6;
    pcbValue2=SQL_NTS;

    rc = SQLSetPos(hstmt,1,SQL_ADD,SQL_LOCK_NO_CHANGE);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt, &nRowCount);
    mystmt(hstmt, rc);

    printMessage(" total rows updated:%d\n",nRowCount);
    is_num(nRowCount, 1);

    /* Free statement cursor resorces */
    rc = SQLFreeStmt(hstmt, SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* commit the transaction */
    rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);

    /* Now fetch and verify the data */
    ok_sql(hstmt, "SELECT * FROM my_pcbValue_add");
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,1,SQL_C_LONG,&nData,0,NULL);
    mystmt(hstmt,rc);
    is_num(nData, 99999);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,szData,50,NULL);
    mystmt(hstmt,rc);
    is_str(szData, "ins", 4);

    rc = SQLGetData(hstmt,3,SQL_C_CHAR,szData,50,NULL);
    mystmt(hstmt,rc);
    is_str(szData, "insert", 7);

    rc = SQLGetData(hstmt,4,SQL_C_CHAR,szData,50,NULL);
    mystmt(hstmt,rc);
    is_str(szData, "inserted", 9);

    rc = SQLFetch(hstmt);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_pcbValue_add");

  return OK;
}


/* spaces in column names */
DECLARE_TEST(my_columnspace)
{
    SQLRETURN   rc;

  ok_sql(hstmt, "DROP TABLE IF EXISTS TestColNames");

  ok_sql(hstmt, "CREATE TABLE `TestColNames`(`Value One` text, `Value Two` text,`Value Three` text)");

  ok_sql(hstmt, "INSERT INTO TestColNames VALUES ('venu','anuganti','mysql ab')");

  ok_sql(hstmt, "INSERT INTO TestColNames VALUES ('monty','widenius','mysql ab')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "SELECT * FROM `TestColNames`");

  is_num(my_print_non_format_result(hstmt), 2);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "SELECT `Value One`,`Value Two`,`Value Three` FROM `TestColNames`");

  is_num(my_print_non_format_result(hstmt), 2);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS TestColNames");

  return OK;
}


/* to test the empty string returning NO_DATA */
DECLARE_TEST(my_empty_string)
{
    SQLRETURN   rc;
    SQLLEN      pcbValue;
    SQLCHAR     szData[255]={0};

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_empty_string");

  ok_sql(hstmt, "create table my_empty_string(name varchar(30))");

  ok_sql(hstmt, "insert into my_empty_string values('')");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    /* Now fetch and verify the data */
  ok_sql(hstmt, "SELECT * FROM my_empty_string");

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,1,SQL_C_CHAR,szData,50,&pcbValue);
    mystmt(hstmt,rc);
    printMessage("szData:%s(%d)\n",szData,pcbValue);

    rc = SQLFetch(hstmt);
    mystmt_err(hstmt,rc==SQL_NO_DATA_FOUND,rc);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_empty_string");

  return OK;
}


BEGIN_TESTS
  ADD_TEST(my_pcbvalue)
  ADD_TEST(my_pcbvalue_add)
  ADD_TEST(my_columnspace)
  ADD_TEST(my_empty_string)
END_TESTS


RUN_TESTS
