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

SQLRETURN rc;

/* Basic prepared statements - binary protocol test */
DECLARE_TEST(t_prep_basic)
{
    SQLINTEGER id;
    SQLLEN length1, length2, pcrow;
    char       name[20];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_basic");

  ok_sql(hstmt, "create table t_prep_basic(a int, b char(4))");

  ok_stmt(hstmt,
          SQLPrepare(hstmt,
                     (SQLCHAR *)"insert into t_prep_basic values(?,'venu')",
                     SQL_NTS));

    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &id, 0, NULL);
    mystmt(hstmt,rc);

    id = 100;
    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt, &pcrow);
    mystmt(hstmt,rc);

    printMessage( "affected rows: %ld\n", pcrow);
    myassert(pcrow == 1);

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    ok_sql(hstmt, "select * from t_prep_basic");

    rc = SQLBindCol(hstmt, 1, SQL_C_LONG, &id, 0, &length1);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt, 2, SQL_C_CHAR, name, 5, &length2);
    mystmt(hstmt,rc);

    id = 0;
    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage( "outdata: %d(%d), %s(%d)\n",id,length1,name,length2);
    myassert(id == 100 && length1 == sizeof(SQLINTEGER));
    myassert(strcmp(name,"venu")==0 && length2 == 4);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA_FOUND);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_basic");

  return OK;
}


/* to test buffer length */
DECLARE_TEST(t_prep_buffer_length)
{
  SQLLEN length;
  SQLCHAR buffer[20];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_buffer_length");

  ok_sql(hstmt, "create table t_prep_buffer_length(a varchar(20))");

  ok_stmt(hstmt,
          SQLPrepare(hstmt,
                     (SQLCHAR *)"insert into t_prep_buffer_length values(?)",
                     SQL_NTS));

  length= 0;
  strcpy((char *)buffer, "abcdefghij");

    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR, 15, 10, buffer, 4, &length);
    mystmt(hstmt,rc);

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    length= 3;

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    length= 10;

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    length= 9;

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    length= SQL_NTS;

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    ok_sql(hstmt, "select * from t_prep_buffer_length");

    rc = SQLBindCol(hstmt, 1, SQL_C_CHAR, buffer, 15, &length);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage( "outdata: %s (%ld)\n", buffer, length);
    is_num(length, 0);
    is_num(buffer[0], '\0');

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("outdata: %s (%ld)\n", buffer, length);
    is_num(length, 3);
    is_str(buffer, "abc", 10);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("outdata: %s (%ld)\n", buffer, length);
    is_num(length, 10);
    is_str(buffer, "abcdefghij", 10);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("outdata: %s (%ld)\n", buffer, length);
    is_num(length, 9);
    is_str(buffer, "abcdefghi", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("outdata: %s (%ld)\n", buffer, length);
    is_num(length, 10);
    is_str(buffer, "abcdefghij", 10);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA_FOUND);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_buffer_length");

  return OK;
}


/* For data truncation */
DECLARE_TEST(t_prep_truncate)
{
    SQLLEN length, length1, pcrow;
    SQLCHAR    name[20], bin[10];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_truncate");

  ok_sql(hstmt, "create table t_prep_truncate(a int, b char(4), c binary(4))");

  ok_stmt(hstmt,
          SQLPrepare(hstmt,
                     (SQLCHAR *)"insert into t_prep_truncate "
                     "values(500,'venu','venu')", SQL_NTS));

    strcpy((char *)name,"venu");
    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR, 10, 10, name, 5, NULL);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_BINARY,SQL_BINARY, 10, 10, name, 5, NULL);
    mystmt(hstmt,rc);

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLRowCount(hstmt, &pcrow);
    mystmt(hstmt,rc);

    printMessage( "affected rows: %ld\n", pcrow);
    myassert(pcrow == 1);

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    ok_sql(hstmt, "select b,c from t_prep_truncate");

    rc = SQLBindCol(hstmt, 1, SQL_C_CHAR, name, 2, &length);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt, 2, SQL_C_BINARY, bin, 4, &length1);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage("str outdata: %s(%d)\n",name,length);
    is_num(length, 4);
    is_str(bin, "v", 1);

    bin[4]='M';
    printMessage("bin outdata: %s(%d)\n",bin,length1);
    is_num(length, 4);
    is_str(bin, "venuM", 5);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA_FOUND);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_truncate");

  return OK;
}


/* For scrolling */
DECLARE_TEST(t_prep_scroll)
{
  SQLINTEGER i, data, max_rows= 5;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_scroll");
  ok_sql(hstmt, "CREATE TABLE t_prep_scroll (a TINYINT)");
  ok_sql(hstmt, "INSERT INTO t_prep_scroll VALUES (1),(2),(3),(4),(5)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));

  ok_sql(hstmt, "SELECT * FROM t_prep_scroll");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &data, 0, NULL));

  for (i= 1; ; i++)
  {
    SQLRETURN rc= SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0);
    if (rc == SQL_NO_DATA)
        break;

    is_num(i, data);
  }

  is_num(i, max_rows + 1);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 3));
  is_num(data, 3);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 3));
  is_num(data, 2);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 3));
  is_num(data, 1);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_PREV, 3), SQL_NO_DATA);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -2),
              SQL_NO_DATA);

  ok_stmt(hstmt,  SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 2));
  is_num(data, 2);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_LAST, 3));
  is_num(data, 5);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -2));
  is_num(data, 3);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, 3),
              SQL_NO_DATA);

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 3),
              SQL_NO_DATA);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_RELATIVE, -2));
  is_num(data, 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_scroll");

  return OK;
}


/* For SQLGetData */
DECLARE_TEST(t_prep_getdata)
{
    SQLCHAR    name[10];
    SQLINTEGER data;
    SQLLEN     length;
    SQLCHAR    tiny;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_getdata");

  ok_sql(hstmt, "create table t_prep_getdata(a tinyint, b int, c char(4))");

  ok_stmt(hstmt,
          SQLPrepare(hstmt,
                     (SQLCHAR *)"insert into t_prep_getdata values(?,?,?)",
                     SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_LONG,SQL_TINYINT,
                          0,0,&data,0,NULL);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT,SQL_C_LONG,SQL_INTEGER,
                          0,0,&data,0,NULL);

    rc = SQLBindParameter(hstmt,3,SQL_PARAM_INPUT,SQL_C_CHAR,SQL_CHAR,
                          10,10,name,6,NULL);
    mystmt(hstmt,rc);

    sprintf((char *)name,"venu"); data = 10;

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    data= 0;
    ok_sql(hstmt, "select * from t_prep_getdata");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt, 1,SQL_C_TINYINT, &tiny, 0, NULL);
    mystmt(hstmt, rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt, rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt, rc);

    printMessage("record 1 : %d\n", tiny);
    myassert( tiny == 10);

    rc = SQLGetData(hstmt,2,SQL_C_LONG,&data,0,NULL);
    mystmt(hstmt,rc);

    printMessage("record 2 : %ld\n", data);
    myassert( data == 10);

    name[0]= '\0';
    rc = SQLGetData(hstmt,3,SQL_C_CHAR,name,5,&length);
    mystmt(hstmt,rc);

    printMessage("record 3 : %s(%ld)\n", name, (long)length);

    is_num(length, 4);
    is_str(name, "venu", 4);

    data = 0;
    rc = SQLGetData(hstmt,1,SQL_C_LONG,&data,0,NULL);
    mystmt(hstmt,rc);

    printMessage("record 1 : %ld", data);
    myassert( data == 10);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_getdata");

  return OK;
}


/* For SQLGetData in truncation */
DECLARE_TEST(t_prep_getdata1)
{
    SQLCHAR     data[11];
    SQLLEN length;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_getdata");

  ok_sql(hstmt, "create table t_prep_getdata(a char(10), b int)");

  ok_sql(hstmt, "insert into t_prep_getdata values('abcdefghij',12345)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "select * from t_prep_getdata");

    rc = SQLFetch(hstmt);
    mystmt(hstmt, rc);

    data[0]= 'M'; data[1]= '\0';
    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,0,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 10);
    is_str(data, "M", 1);

    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,4,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 10);
    is_str(data, "abc", 3);

    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,4,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 7);
    is_str(data, "def", 3);

    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,4,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 4);
    is_str(data, "ghi", 3);

    data[0]= 'M';
    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,0,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 1);
    is_str(data, "M", 1);

    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,1,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 1);
    is_num(data[0], '\0');

    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,2,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 1);
    is_str(data, "j", 1);

    rc = SQLGetData(hstmt,1,SQL_C_CHAR,data,2,&length);
    myassert(rc == SQL_NO_DATA);

    data[0]= 'M'; data[1]= '\0';
    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,0,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 5);
    is_str(data, "M", 2);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,3,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 5);
    is_str(data, "12", 2);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,2,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 3);
    is_str(data, "3", 1);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,2,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 2);
    is_str(data, "4", 1);

    data[0]= 'M';
    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,0,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 1);
    is_str(data, "M", 1);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,1,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    myassert(data[0] == '\0' && length == 1);
    is_num(length, 1);
    is_num(data[0], '\0');

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,2,&length);
    mystmt(hstmt,rc);

    printMessage("data: %s (%ld)\n", data, length);
    is_num(length, 1);
    is_str(data, "5", 1);

    rc = SQLGetData(hstmt,2,SQL_C_CHAR,data,2,&length);
    myassert(rc == SQL_NO_DATA);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_getdata");

  return OK;
}


DECLARE_TEST(t_prep_catalog)
{
    SQLCHAR     table[20];
    SQLLEN      length;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_catalog");

  ok_sql(hstmt, "create table t_prep_catalog(a int default 100)");

    rc = SQLTables(hstmt,NULL,0,NULL,0,(SQLCHAR *)"t_prep_catalog",14,
                   (SQLCHAR *)"TABLE",5);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,3,SQL_C_CHAR,table,0,&length);
    mystmt(hstmt,rc);
    myassert(length == 14);

    rc = SQLGetData(hstmt,3,SQL_C_CHAR,table,15,&length);
    mystmt(hstmt,rc);
    is_num(length, 14);
    is_str(table, "t_prep_catalog", 14);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLColumns(hstmt,NULL,0,NULL,0,(SQLCHAR *)"t_prep_catalog",14,NULL,0);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,3,SQL_C_CHAR,table,15,&length);
    mystmt(hstmt,rc);
    is_num(length, 14);
    is_str(table, "t_prep_catalog", 14);

    rc = SQLGetData(hstmt,4,SQL_C_CHAR,table,0,&length);
    mystmt(hstmt,rc);
    myassert(length == 1);

    rc = SQLGetData(hstmt,4,SQL_C_CHAR,table,2,&length);
    mystmt(hstmt,rc);
    is_num(length, 1);
    is_str(table, "a", 1);

    rc = SQLGetData(hstmt,13,SQL_C_CHAR,table,10,&length);
    mystmt(hstmt,rc);
    printMessage("table: %s(%d)\n", table, length);
    is_num(length, 3);
    is_str(table, "100", 3);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prep_catalog");

  return OK;
}


DECLARE_TEST(t_sps)
{
    SQLINTEGER a, a1;
    SQLLEN length, length1;
    char b[]= "abcdefghij", b1[10];

  if (!mysql_min_version(hdbc, "5.0", 3))
    skip("server does not support stored procedures");

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_tabsp");
  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_sp");

  ok_sql(hstmt, "create table t_tabsp(a int, b varchar(10))");

  ok_sql(hstmt,"create procedure t_sp(x int, y char(10)) "
         "begin insert into t_tabsp values(x, y); end;");

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"call t_sp(?,?)", SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_LONG,SQL_INTEGER,
                          0,0,&a,0,NULL);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT,SQL_C_CHAR,SQL_CHAR,
                          0,0,b,0,&length);


    for (length= 0, a= 0; a < 10; a++, length++)
    {
        rc = SQLExecute(hstmt);
        mystmt(hstmt, rc);
    }

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt, "select * from t_tabsp");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&a,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,b1,11,&length);
    mystmt(hstmt,rc);

    for (length1= 0, a1= 0; a1 < 10; a1++, length1++)
    {
        rc = SQLFetch(hstmt);
        mystmt(hstmt, rc);

        printMessage( "data: %d, %s(%d)\n", a, b1, length);
        myassert( a == a1);
        myassert(strncmp(b1,b,length1) == 0 && length1 == length);
    }

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_sp");
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_tabsp");

  return OK;
}


DECLARE_TEST(t_prepare)
{
  SQLRETURN rc;
  SQLINTEGER nidata= 200, nodata;
  SQLLEN    nlen;
  char      szodata[20],szidata[20]="MySQL";
  SQLSMALLINT pccol;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prepare");

    rc = tmysql_exec(hstmt,"create table t_prepare(col1 int primary key, col2 varchar(30), col3 set(\"one\", \"two\"))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_prepare values(100,'venu','one')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_prepare values(200,'MySQL','two')");
    mystmt(hstmt,rc);

    ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"select * from t_prepare "
                              "where col2 = ? AND col1 = ?",SQL_NTS));

    rc = SQLNumResultCols(hstmt,&pccol);
    mystmt(hstmt,rc);
    is_num(pccol, 3);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nodata,0,&nlen);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szodata,200,&nlen);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT, SQL_C_CHAR,SQL_VARCHAR,
                          0,0,szidata,20,&nlen);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT, SQL_C_LONG,SQL_INTEGER,
                          0,0,&nidata,20,NULL);
    mystmt(hstmt,rc);

    nlen= strlen(szidata);
    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    fprintf(stdout," outdata: %d, %s(%ld)\n", nodata,szodata,(long)nlen);
    my_assert(nodata == 200);

    rc = SQLFetch(hstmt);
    my_assert(rc == SQL_NO_DATA_FOUND);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prepare");

  return OK;
}


DECLARE_TEST(t_prepare1)
{
  SQLRETURN rc;
  SQLINTEGER nidata= 1000;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prepare1");

    rc = tmysql_exec(hstmt,"create table t_prepare1(col1 int primary key, col2 varchar(30))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_prepare1 values(100,'venu')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_prepare1 values(200,'MySQL')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_prepare(hstmt,"insert into t_prepare1(col1) values(?)");
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT, SQL_C_LONG,SQL_INTEGER,
                          0,0,&nidata,0,NULL);
    mystmt(hstmt,rc);

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    mystmt(hstmt,rc);

    ok_sql(hstmt, "SELECT * FROM t_prepare1");

    myassert(3 == myresult(hstmt));/* unless prepare is supported..*/

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_prepare1");

  return OK;
}


DECLARE_TEST(tmysql_bindcol)
{
    SQLRETURN rc;
    SQLINTEGER nodata, nidata = 200;
    SQLLEN     nlen;
    SQLCHAR   szodata[20],szidata[20]="MySQL";

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_bindcol");

    rc = tmysql_exec(hstmt,"create table tmysql_bindcol(col1 int primary key, col2 varchar(30))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_bindcol values(100,'venu')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_bindcol values(200,'MySQL')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_prepare(hstmt,"select * from tmysql_bindcol where col2 = ? AND col1 = ?");
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nodata,0,&nlen);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szodata,200,&nlen);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT, SQL_C_CHAR,SQL_VARCHAR,
                          0,0,szidata,5,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT, SQL_C_LONG,SQL_INTEGER,
                          0,0,&nidata,20,NULL);
    mystmt(hstmt,rc);

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage(" outdata: %d, %s(%d)\n", nodata,szodata,nlen);
    my_assert(nodata == 200);

    rc = SQLFetch(hstmt);

    my_assert(rc == SQL_NO_DATA_FOUND);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_bindcol");

  return OK;
}


DECLARE_TEST(tmysql_bindparam)
{
    SQLRETURN rc;
    SQLINTEGER nodata, nidata= 200;
    SQLLEN    nlen;
    SQLCHAR   szodata[20],szidata[20]="MySQL";
    SQLSMALLINT pccol;

    tmysql_exec(hstmt,"drop table tmysql_bindparam");

    rc = tmysql_exec(hstmt,"create table tmysql_bindparam(col1 int primary key, col2 varchar(30))");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_bindparam values(100,'venu')");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_bindparam values(200,'MySQL')");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_prepare(hstmt,"select * from tmysql_bindparam where col2 = ? AND col1 = ?");
    mystmt(hstmt,rc);

    rc = SQLNumResultCols(hstmt,&pccol);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&nodata,0,&nlen);
    mystmt(hstmt,rc);

    rc = SQLBindCol(hstmt,2,SQL_C_CHAR,szodata,200,&nlen);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT, SQL_C_CHAR,SQL_VARCHAR,
                          0,0,szidata,5,NULL);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT, SQL_C_LONG,SQL_INTEGER,
                          0,0,&nidata,20,NULL);
    mystmt(hstmt,rc);

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    printMessage(" outdata: %d, %s(%d)\n", nodata,szodata,nlen);
    my_assert(nodata == 200);

    rc = SQLFetch(hstmt);

    my_assert(rc == SQL_NO_DATA_FOUND);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_UNBIND);
    mystmt(hstmt,rc);

    rc = SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"drop table tmysql_bindparam");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

  return OK;
}


DECLARE_TEST(t_acc_update)
{
    SQLRETURN rc;
    SQLINTEGER id,id1;
    SQLLEN pcrow;
    SQLHSTMT hstmt1;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_acc_update");

    rc = tmysql_exec(hstmt,"create table t_acc_update(id int)");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into t_acc_update values(1)");
    mystmt(hstmt,rc);
    rc = tmysql_exec(hstmt,"insert into t_acc_update values(2)");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);

    mystmt(hstmt,rc);

    ok_stmt(hstmt,
            SQLPrepare(hstmt,
                       (SQLCHAR *)"select id from t_acc_update where id = ?",
                       SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_DEFAULT,SQL_INTEGER,11,0,&id,0,NULL);
    mystmt(hstmt,rc);

    id = 2;
    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt,1,SQL_C_LONG,&id1,512,NULL);
    mystmt(hstmt,rc);
    printMessage("outdata:%d\n",id1);

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_UNBIND);
    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);


    rc = SQLSetConnectOption(hdbc,SQL_AUTOCOMMIT,0L);
    mycon(hdbc,rc);

    rc = SQLAllocStmt(hdbc,&hstmt1);
    mycon(hdbc,rc);

    id = 2;
    id1=2;
    rc = SQLBindParameter(hstmt1,1,SQL_PARAM_INPUT,SQL_C_LONG,SQL_INTEGER,10,0,&id,0,NULL);
    mystmt(hstmt1,rc);

    rc = SQLBindParameter(hstmt1,2,SQL_PARAM_INPUT,SQL_C_DEFAULT,SQL_INTEGER,11,0,&id1,0,NULL);
    mystmt(hstmt1,rc);

    ok_sql(hstmt1, "UPDATE t_acc_update SET id = ?  WHERE id = ?");

    rc = SQLRowCount(hstmt1,&pcrow);
    mystmt(hstmt1,rc);
    printMessage("rows affected:%d\n",pcrow);

    SQLFreeStmt(hstmt1,SQL_RESET_PARAMS);
    rc = SQLFreeStmt(hstmt1,SQL_DROP);
    mystmt(hstmt1,rc);

    rc = SQLTransact(NULL,hdbc,0);
    mycon(hdbc,rc);

    rc = SQLSetConnectOption(hdbc,SQL_AUTOCOMMIT,1L);
    mycon(hdbc,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_acc_update");

  return OK;
}


/**
  Bug #29871: MyODBC problem with MS Query ('Memory allocation error')
*/
DECLARE_TEST(t_bug29871)
{
  SQLCHAR *param= (SQLCHAR *)"1";

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug29871");
  ok_sql(hstmt, "CREATE TABLE t_bug29871 (a INT)");

  /* The bug is related to calling deprecated SQLSetParam */
  ok_stmt(hstmt, SQLSetParam(hstmt, 1, SQL_C_CHAR, SQL_INTEGER, 10, 0,
                             param, 0));
  ok_sql(hstmt, "INSERT INTO t_bug29871 VALUES (?)");
  ok_stmt(hstmt, SQLSetParam(hstmt, 1, SQL_C_CHAR, SQL_INTEGER, 10, 0,
                             param, 0));
  ok_sql(hstmt, "SELECT * FROM t_bug29871 WHERE a=?");
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_sql(hstmt, "DROP TABLE t_bug29871");
  return OK;
}


/**
  Bug #67340: Memory leak in 5.2.2(w) ODBC driver
              causes Prepared_stmt_count to grow
*/
DECLARE_TEST(t_bug67340)
{
  SQLCHAR *param= (SQLCHAR *)"1";
  SQLCHAR     data[255]= "abcdefg";
  SQLLEN paramlen= 7;
  int i, stmt_count= 0;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug67340");
  ok_sql(hstmt, "CREATE TABLE t_bug67340(id INT AUTO_INCREMENT PRIMARY KEY,"\
                "vc VARCHAR(32))");

  /* get the initial numnber of Prepared_stmt_count */
  ok_sql(hstmt, "SHOW STATUS LIKE 'Prepared_stmt_count'");
  ok_stmt(hstmt, SQLFetch(hstmt));
  stmt_count= my_fetch_int(hstmt, 2);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  for(i=0; i < 100; i++)
  {
    ok_stmt(hstmt, SQLPrepare(hstmt, "INSERT INTO t_bug67340(id, vc) "\
                                     "VALUES (NULL, ?)", SQL_NTS));
    ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                    SQL_CHAR, 0, 0, data, sizeof(data),
                                    &paramlen));
    ok_stmt(hstmt, SQLExecute(hstmt));

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  }

  /* get the new numnber of Prepared_stmt_count */
  ok_sql(hstmt, "SHOW STATUS LIKE 'Prepared_stmt_count'");
  ok_stmt(hstmt, SQLFetch(hstmt));

  /* check how much Prepared_stmt_count has increased */
  is(!(my_fetch_int(hstmt, 2) - stmt_count > 1));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "DROP TABLE t_bug67340");
  return OK;
}


/**
  Bug #67702: Problem with BIT columns in MS Access
*/
DECLARE_TEST(t_bug67702)
{
  SQLCHAR data1[5]= "abcd";
  char c1 = 1, c2= 0;
  int id= 1;

  SQLLEN paramlen= 0;

  ok_stmt(hstmt, SQLExecDirect(hstmt, "drop table if exists bug67702",
                                      SQL_NTS));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "create table bug67702"\
                                      "(id int auto_increment primary key,"\
                                      "vc varchar(32), yesno bit(1))",
                                      SQL_NTS));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "INSERT INTO bug67702(id, vc, yesno)"\
                                      "VALUES (1, 'abcd', 1)",
                                      SQL_NTS));

  /* Set parameter values here to make it clearer where each one goes */
  c1= 0;
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_BIT,
                                  SQL_BIT, 1, 0, &c1, 0, NULL));

  id= 1;
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, sizeof(id), 0, &id, 0, NULL));

  paramlen= 4;
  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_VARCHAR, 4, 0, data1, 0, &paramlen));

  c2= 1;
  ok_stmt(hstmt, SQLBindParameter(hstmt, 4, SQL_PARAM_INPUT, SQL_C_BIT,
                                  SQL_BIT, 1, 0, &c2, 0, NULL));

  /* The prepared query looks exactly as MS Access does it */
  ok_stmt(hstmt, SQLExecDirect(hstmt, "UPDATE `bug67702` SET `yesno`=?  "\
                                      "WHERE `id` = ? AND `vc` = ? AND "\
                                      "`yesno` = ?", SQL_NTS));
  SQLFreeStmt(hstmt, SQL_CLOSE);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  /* Now check the result of update the bit field should be set to 0 */
  ok_stmt(hstmt, SQLExecDirect(hstmt, "SELECT `yesno` FROM `bug67702`", SQL_NTS));
  ok_stmt(hstmt, SQLFetch(hstmt));
  is(my_fetch_int(hstmt, 1) == 0);

  SQLFreeStmt(hstmt, SQL_CLOSE);
  ok_stmt(hstmt, SQLExecDirect(hstmt, "drop table if exists bug67702", SQL_NTS));
  return OK;
}


/**
  Bug #68243: Microsoft Access Crashes when Bit field updates
*/
DECLARE_TEST(t_bug68243)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  char c1 = 1, c2= 0;
  int id= 1;

  SQLLEN paramlen= 0;

  ok_stmt(hstmt, SQLExecDirect(hstmt, "drop table if exists bug68243",
                                      SQL_NTS));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "create table bug68243"\
                                      "(id int primary key,"\
                                      "yesno bit(1))",
                                      SQL_NTS));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "INSERT INTO bug68243(id, yesno)"\
                                      "VALUES (1, 1)",
                                      SQL_NTS));

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL, "NO_SSPS=1"));

  /* Set parameter values here to make it clearer where each one goes */
  c1= 0;
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_BIT,
                                  SQL_BIT, 1, 0, &c1, 0, NULL));

  id= 1;
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 2, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, sizeof(id), 0, &id, 0, NULL));

  c2= 1;
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 3, SQL_PARAM_INPUT, SQL_C_BIT,
                                  SQL_BIT, 1, 0, &c2, 0, NULL));

  /* The prepared query looks exactly as MS Access does it */
  ok_stmt(hstmt1, SQLExecDirect(hstmt1, "UPDATE `bug68243` SET `yesno`=?  "\
                                      "WHERE `id` = ? AND `yesno` = ?",
                                      SQL_NTS));
  SQLFreeStmt(hstmt1, SQL_CLOSE);
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_RESET_PARAMS));

  /* Now check the result of update the bit field should be set to 0 */
  ok_stmt(hstmt, SQLExecDirect(hstmt, "SELECT `yesno` FROM `bug68243`", SQL_NTS));
  ok_stmt(hstmt, SQLFetch(hstmt));
  is(my_fetch_int(hstmt, 1) == 0);

  SQLFreeStmt(hstmt, SQL_CLOSE);
  ok_stmt(hstmt, SQLExecDirect(hstmt, "drop table if exists bug68243", SQL_NTS));

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/**
  Bug #67920: Non-compliant behavior of SQLMoreResults
*/
DECLARE_TEST(t_bug67920)
{
  ok_stmt(hstmt, SQLPrepare(hstmt, "SELECT 1", SQL_NTS));

  expect_stmt(hstmt, SQLMoreResults(hstmt), SQL_NO_DATA);

  SQLFreeStmt(hstmt, SQL_CLOSE);
  return OK;
}

/*
  Bug #31667091: ODBC prepared statements broken by changes
  in behavior of @@sql_select_limit
*/
DECLARE_TEST(t_bug31667091)
{
  int id = 10;
  ok_sql(hstmt,"DROP TABLE IF EXISTS bug31667091");
  ok_sql(hstmt,"CREATE TABLE bug31667091 (id int)");
  ok_sql(hstmt,"INSERT INTO bug31667091 VALUES (1), (2), (3)");
  // Set row number limit to 1
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_MAX_ROWS, (SQLPOINTER)1, 0));
  ok_sql(hstmt, "SELECT * FROM bug31667091");
  // Make sure the limit is applied
  is_num(my_print_non_format_result(hstmt), 1);

  ok_stmt(hstmt, SQLPrepare(hstmt, "SELECT * FROM bug31667091 WHERE id < ?",
                            SQL_NTS));
  // Remove row number limit
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_MAX_ROWS, (SQLPOINTER)0, 0));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &id, 0, NULL));
  ok_stmt(hstmt, SQLExecute(hstmt));
  is_num(my_print_non_format_result(hstmt), 3);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_sql(hstmt,"DROP TABLE bug31667091");
  return OK;
}

BEGIN_TESTS
  ADD_TEST(t_prep_basic)
  ADD_TEST(t_prep_buffer_length)
  ADD_TEST(t_prep_truncate)
  ADD_TEST(t_prep_scroll)
  ADD_TEST(t_prep_getdata)
  ADD_TEST(t_prep_getdata1)
  ADD_TEST(t_prep_catalog)
  ADD_TEST(t_sps)
  ADD_TEST(t_prepare)
  ADD_TEST(t_prepare1)
  ADD_TEST(tmysql_bindcol)
  ADD_TEST(tmysql_bindparam)
  ADD_TEST(t_acc_update)
  ADD_TEST(t_bug29871)
  ADD_TEST(t_bug67340)
  ADD_TEST(t_bug67702)
  ADD_TEST(t_bug68243)
  ADD_TEST(t_bug67920)
  ADD_TODO(t_bug31667091)
END_TESTS


RUN_TESTS
