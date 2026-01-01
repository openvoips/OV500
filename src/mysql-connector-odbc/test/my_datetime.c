// Copyright (c) 2007, 2024, Oracle and/or its affiliates.
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


#include <time.h>
#include "odbctap.h"


DECLARE_TEST(my_ts)
{
  SQLCHAR          szTs[50];
  TIMESTAMP_STRUCT ts;
  SQLLEN           len;
  int is_fraction_capable = mysql_min_version(hdbc, "5.6.", 4);

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_ts");

  if(is_fraction_capable)
  {
    ok_sql(hstmt, "CREATE TABLE my_ts (ts TIMESTAMP(6))");
  }
  else
  {
    ok_sql(hstmt, "CREATE TABLE my_ts (ts TIMESTAMP)");
  }

  /* insert using SQL_C_CHAR to SQL_TIMESTAMP */
  strcpy((char *)szTs, "2002-01-07 10:20:49.06");
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                                  SQL_C_CHAR, SQL_TIMESTAMP,
                                  0, 0, szTs, sizeof(szTs), NULL));
  ok_sql(hstmt, "INSERT INTO my_ts (ts) VALUES (?)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_KEYSET_DRIVEN, 0));

  /* insert using SQL_C_TIMESTAMP to SQL_TIMESTAMP */
  ts.year= 2002;
  ts.month= 1;
  ts.day= 7;
  ts.hour= 19;
  ts.minute= 47;
  ts.second= 59;
  ts.fraction= is_fraction_capable ? 123456000 : 0;

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                                  SQL_C_TIMESTAMP, SQL_TIMESTAMP,
                                  0, 0, &ts, sizeof(ts), NULL));

  ok_sql(hstmt, "INSERT INTO my_ts (ts) VALUES (?)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  /* now fetch and verify the results .. */
  ok_sql(hstmt, "SELECT * FROM my_ts");

  /* now fetch first row */
  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 1));

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szTs, sizeof(szTs), &len));

  is_str(szTs, is_fraction_capable ? "2002-01-07 10:20:49.060000" :
                                     "2002-01-07 10:20:49", len);

  printf("# row1 using SQL_C_CHAR: %s (%ld)\n", szTs, (long)len);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 1));

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_TIMESTAMP, &ts, sizeof(ts), &len));
  is_num(ts.year,  2002);
  is_num(ts.month, 1);
  is_num(ts.day,   7);
  is_num(ts.hour,  10);
  is_num(ts.minute,20);
  is_num(ts.second,49);
  is_num(ts.fraction, is_fraction_capable ? 60000000 : 0);

  printf("# row1 using SQL_C_TIMESTAMP: %d-%d-%d %d:%d:%d.%d (%ld)\n",
         ts.year, ts.month,ts.day, ts.hour, ts.minute, ts.second, ts.fraction,
         (long)len);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 2));

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szTs, sizeof(szTs), &len));
  is_str(szTs, is_fraction_capable ? "2002-01-07 19:47:59.123456" :
                                      "2002-01-07 19:47:59", len);
  printf("# row2 using SQL_C_CHAR: %s(%ld)\n", szTs, (long)len);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 2));

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_TIMESTAMP, &ts, sizeof(ts), &len));
  is_num(ts.year,  2002);
  is_num(ts.month, 1);
  is_num(ts.day,   7);
  is_num(ts.hour,  19);
  is_num(ts.minute,47);
  is_num(ts.second,59);
  is_num(ts.fraction, is_fraction_capable ? 123456000 : 0);

  printf("# row2 using SQL_C_TIMESTAMP: %d-%d-%d %d:%d:%d.%d (%ld)\n",
         ts.year, ts.month,ts.day, ts.hour, ts.minute, ts.second, ts.fraction,
         (long)len);


  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 3),
              SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_UNBIND));
  ok_stmt(hstmt,  SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_ts");

  /* Test of 2-digits year(YYMMDD) format */
  ok_sql(hstmt, "select \"910825\"");
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_TIMESTAMP, &ts, sizeof(ts), &len));

  is_num(1991, ts.year);
  is_num(8, ts.month);
  is_num(25, ts.day);

  return OK;
}


DECLARE_TEST(t_tstotime)
{
  SQL_TIMESTAMP_STRUCT ts, ts1, ts2;
  int is_fraction_capable = mysql_min_version(hdbc, "5.6.", 4);

  ts.day    = 02;
  ts.month  = 8;
  ts.year   = 2001;
  ts.hour   = 18;
  ts.minute = 20;
  ts.second = 45;
  ts.fraction = is_fraction_capable ? 555000 : 0;

  memcpy(&ts1, (void*) &ts, sizeof(SQL_TIMESTAMP_STRUCT));

  memcpy(&ts2, (void*) &ts1, sizeof(SQL_TIMESTAMP_STRUCT));

  /*
    SQL_TIME cannot have the fractional part
    http://msdn.microsoft.com/en-us/library/ms709385%28v=vs.85%29.aspx
  */
  ts1.fraction= 0;

  /* Same for SQL_DATE - time is truncated -> error */
  ts2.fraction= ts2.hour= ts2.minute= ts2.second= 0;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_tstotime");

  ok_con(hdbc, SQLTransact(NULL,hdbc,SQL_COMMIT));

  if(is_fraction_capable)
  {
    ok_sql(hstmt,"create table t_tstotime(col1 date,col2 time, col3 timestamp(6))");
  }
  else
  {
    ok_sql(hstmt,"create table t_tstotime(col1 date ,col2 time, col3 timestamp)");
  }

  ok_con(hdbc, SQLTransact(NULL,hdbc,SQL_COMMIT));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  /* TIMESTAMP TO DATE, TIME and TS CONVERSION */
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"insert into t_tstotime(col1,col2,col3) values(?,?,?)",SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_TIMESTAMP,
                                  SQL_DATE,0,0,&ts2,sizeof(ts2),NULL));

  //ts1.fraction= 0;
  ok_stmt(hstmt, SQLBindParameter(hstmt,2,SQL_PARAM_INPUT,SQL_C_TIMESTAMP,
                                  SQL_TIME,0,0,&ts1,sizeof(ts1),NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt,3,SQL_PARAM_INPUT,SQL_C_TIMESTAMP,
                                  SQL_TIMESTAMP,0,0,&ts,sizeof(ts),NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_RESET_PARAMS));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_con(hdbc, SQLTransact(NULL,hdbc,SQL_COMMIT));

  ok_sql(hstmt,"select * from t_tstotime");

  my_assert( 1 == myresult(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_UNBIND));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_tstotime");

  return OK;
}


DECLARE_TEST(t_tstotime1)
{
  SQLCHAR ts[40]= "2001-08-02 18:20:45.05";

  ok_sql(hstmt,"DROP TABLE IF EXISTS t_tstotime1");

  ok_sql(hstmt,
         "CREATE TABLE t_tstotime1(col1 DATE, col2 TIME, col3 TIMESTAMP)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  /* TIMESTAMP TO DATE, TIME and TS CONVERSION */
  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"INSERT INTO t_tstotime1 VALUES (?,?,?)",
                            SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_DATE, 0, 0, &ts, sizeof(ts), NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_TIME, 0, 0, &ts, sizeof(ts), NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_TIMESTAMP, 0, 0, &ts, sizeof(ts), NULL));

  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);

  is_num(check_sqlstate(hstmt, "22008"), OK);

  /* Taking only date part */
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_DATE, 0, 0, &ts, 10, NULL));

  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);

  is_num(check_sqlstate(hstmt, "22008"), OK);

  /* are not taking fractional part */
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_TIME, 0, 0, &ts, 19, NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_con(hdbc, SQLTransact(NULL,hdbc,SQL_COMMIT));

  ok_sql(hstmt, "SELECT * FROM t_tstotime1");

  my_assert(1 == myresult(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_tstotime1");

  return OK;
}


DECLARE_TEST(t_bug25846)
{
  SQLSMALLINT          column_count;
  SQLLEN               my_time_cb;
  SQLLEN               my_date_cb;
  SQL_TIMESTAMP_STRUCT my_time_ts;
  SQL_TIMESTAMP_STRUCT my_date_ts;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug25846");
  ok_sql(hstmt, "CREATE TABLE t_bug25846 (a TIME, b DATE)");
  ok_sql(hstmt, "INSERT INTO t_bug25846 VALUES ('02:56:30', '1969-07-21')");

  ok_sql(hstmt, "SELECT * FROM t_bug25846");

  ok_stmt(hstmt, SQLNumResultCols(hstmt, &column_count));
  is_num(column_count, 2);

  /* Bind the TIMESTAMP buffer for TIME column */
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_TIMESTAMP, &my_time_ts,
                            sizeof(my_time_ts), &my_time_cb));

  /* Bind the TIMESTAMP buffer for DATE column */
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_TIMESTAMP, &my_date_ts,
                            sizeof(my_date_ts), &my_date_cb));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_time_ts.hour,   2);
  is_num(my_time_ts.minute, 56);
  is_num(my_time_ts.second, 30);

  is_num(my_date_ts.year,   1969);
  is_num(my_date_ts.month,  7);
  is_num(my_date_ts.day,    21);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug25846");

  return OK;
}


DECLARE_TEST(t_time)
{
  SQLRETURN       rc;
  SQL_TIME_STRUCT tm;
  SQLCHAR         str[20];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_time");
  rc = tmysql_exec(hstmt,"create table t_time(tm time, ts timestamp)");
  mystmt(hstmt,rc);

  // Disable strict mode and no zero dates
  ok_sql(hstmt, "set session sql_mode=''");

  ok_con(hdbc, SQLTransact(NULL,hdbc,SQL_COMMIT));

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"insert into t_time values (?,?)",
                            SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter( hstmt, 1, SQL_PARAM_INPUT, SQL_C_TIME,
                         SQL_TIME, 0, 0, &tm, 0, NULL ));

  ok_stmt(hstmt, SQLBindParameter( hstmt, 2, SQL_PARAM_INPUT, SQL_C_TIME,
                         SQL_TIMESTAMP, 0, 0, &tm, 15, NULL ));

  tm.hour = 20;
  tm.minute = 59;
  tm.second = 45;

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_con(hdbc, SQLTransact(NULL,hdbc,SQL_COMMIT));

  ok_sql(hstmt, "select tm from t_time");

  ok_stmt(hstmt, SQLFetch(hstmt));

  ok_stmt(hstmt, SQLGetData(hstmt,1,SQL_C_CHAR,&str,100,NULL));
  is_str(str, "20:59:45", 9);

  rc = SQLFetch(hstmt);
  my_assert(rc == SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_UNBIND));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_time");

  return OK;
}


/* Test for a simple time struct */
DECLARE_TEST(t_time1)
{
  SQLRETURN       rc;
  SQL_TIME_STRUCT tt;
  SQLCHAR         data[30];
  SQLLEN          length;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_time");
  ok_sql(hstmt, "create table t_time(t time, t1 timestamp, t2 datetime, t3 date)");

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"insert into t_time(t) values(?)",
                            SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_TYPE_TIME,
                          SQL_TIME,0,0,&tt,0,NULL);


    tt.hour= 00;
    tt.minute= 00;
    tt.second= 03;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 01;
    tt.minute= 00;
    tt.second= 00;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 19;
    tt.minute= 00;
    tt.second= 00;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 01;
    tt.minute= 01;
    tt.second= 00;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 01;
    tt.minute= 00;
    tt.second= 01;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 00;
    tt.minute= 01;
    tt.second= 00;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 00;
    tt.minute= 11;
    tt.second= 12;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 01;
    tt.minute= 01;
    tt.second= 01;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 00;
    tt.minute= 00;
    tt.second= 00;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    tt.hour= 10;
    tt.minute= 11;
    tt.second= 12;

    rc = SQLExecute(hstmt);
    mystmt(hstmt, rc);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt, "select t from t_time");

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "00:00:03", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "01:00:00", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "19:00:00", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "01:01:00", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "01:00:01", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "00:01:00", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "00:11:12", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "01:01:01", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "00:00:00", 9);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &length);
    mystmt(hstmt,rc);
    is_num(length, 8);
    is_str(data, "10:11:12", 9);

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt,"delete from t_time");

    ok_sql(hstmt, "insert into t_time(t1) values('2003-05-12 10:11:12')");

    ok_sql(hstmt, "select t1 from t_time");

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_TIME, &tt, sizeof(tt), &length);
    mystmt(hstmt,rc);
    is_num(tt.hour, 10);
    is_num(tt.minute, 11);
    is_num(tt.second, 12);
    is_num(length, sizeof(SQL_TIME_STRUCT));

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt,"delete from t_time");
    ok_sql(hstmt,"insert into t_time(t2) values('03-12-28 05:59:59')");
    ok_sql(hstmt,"select t2 from t_time");

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_TIME, &tt, sizeof(tt), &length);
    mystmt(hstmt,rc);
    is_num(tt.hour, 05);
    is_num(tt.minute, 59);
    is_num(tt.second, 59);
    is_num(length, sizeof(SQL_TIME_STRUCT));

    rc = SQLFetch(hstmt);
    is_num(rc, SQL_NO_DATA);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt,"delete from t_time");

    ok_sql(hstmt,"insert into t_time(t3) values('2003-05-12 10:11:12')");

    ok_sql(hstmt,"select t3 from t_time");

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    rc = SQLGetData(hstmt, 1, SQL_C_TIME, &tt, sizeof(tt), &length);
    mystmt(hstmt,rc);
    is(tt.hour == 00 || tt.minute == 00 || tt.second == 00);
    is_num(length, sizeof(SQL_TIME_STRUCT));

    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_time");

  return OK;
}


/**
 Bug #12520: DATETIME Default Value 0000-00-00 00:00:00 not returning
 correct thru ODBC
*/
DECLARE_TEST(t_bug12520)
{
  SQL_TIMESTAMP_STRUCT my_time_ts;
  SQLLEN len, my_time_cb;
  SQLCHAR datetime[50];

  // Disable the strict SQL mode and zero date values
  ok_sql(hstmt, "set session sql_mode='' ");

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug12520");
  ok_sql(hstmt,
         "CREATE TABLE t_bug12520 (a DATETIME DEFAULT '0000-00-00 00:00',"
         "b DATETIME DEFAULT '0000-00-00 00:00', c INT)");

  ok_sql(hstmt, "INSERT INTO t_bug12520 (c) VALUES (1)");

  ok_sql(hstmt, "SELECT a, b FROM t_bug12520");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, datetime, sizeof(datetime),
                            &len));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_TIMESTAMP, &my_time_ts, 0,
                            &my_time_cb));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(datetime, "0000-00-00 00:00:00", 19);
  is_num(my_time_cb, SQL_NULL_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug12520");

  return OK;
}

/**
 Bug #15773: Wrong between results
*/
DECLARE_TEST(t_bug15773)
{
  SQL_DATE_STRUCT a,b,c,d;
  SQLLEN len1;

  // Disable the strict SQL mode and zero date values
  ok_sql(hstmt, "set session sql_mode='' ");

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug15773");
  ok_sql(hstmt, "CREATE TABLE t_bug15773("
         "`a` varchar(255) NOT NULL default '',"
         "`b` datetime NOT NULL default '0000-00-00 00:00:00',"
         "`c` datetime NOT NULL default '0000-00-00 00:00:00'"
         ") ENGINE=InnoDB DEFAULT CHARSET=latin1");
  ok_sql(hstmt, "INSERT INTO t_bug15773 VALUES ('a', '2005-12-24 00:00:00', '2008-05-12 00:00:00')");
  ok_sql(hstmt, "INSERT INTO t_bug15773 VALUES ('b', '2004-01-01 00:00:00', '2005-01-01 00:00:00')");
  ok_sql(hstmt, "INSERT INTO t_bug15773 VALUES ('c', '2004-12-12 00:00:00', '2005-12-12 00:00:00')");

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"SELECT * FROM t_bug15773"
                           " WHERE (?) BETWEEN b AND c", SQL_NTS));

  d.day= 15;
  d.month= 12;
  d.year = 2005;

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TYPE_DATE,
                                  SQL_TYPE_DATE, 0, 0, &d, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, &a, 255, &len1));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_TYPE_DATE, &b, 0, &len1));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_TYPE_DATE, &c, 0, &len1));

  ok_stmt(hstmt, SQLExecute(hstmt));

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug15773");
  return OK;
}


/**
 Bug #9927: Updating datetime columns
*/
DECLARE_TEST(t_bug9927)
{
  SQLCHAR col[10];

  // Disable the strict SQL mode and zero date values
  ok_sql(hstmt, "set session sql_mode='' ");

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug9927");
  ok_sql(hstmt,
         "CREATE TABLE t_bug9927 (a TIMESTAMP DEFAULT 0,"
        "b TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");

  ok_stmt(hstmt, SQLSpecialColumns(hstmt,SQL_ROWVER,  NULL, 0,
                                   NULL, 0, (SQLCHAR *)"t_bug9927", SQL_NTS,
                                   0, SQL_NO_NULLS));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, col, 2), "b", 1);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug9927");

  return OK;
}


/**
 Bug #30081: Can't distinguish between auto-set TIMESTAMP and auto-updated
 TIMESTAMP
*/
DECLARE_TEST(t_bug30081)
{
  if (!mysql_min_version(hdbc, "5.1.23", 6))
    skip("necessary feature added in MySQL 5.1.23");

  // Disable the strict SQL mode and zero date values
  ok_sql(hstmt, "set session sql_mode='' ");

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug30081");
  ok_sql(hstmt,
         "CREATE TABLE t_bug30081 (a TIMESTAMP DEFAULT 0,"
        "b TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

  ok_stmt(hstmt, SQLSpecialColumns(hstmt,SQL_ROWVER,  NULL, 0,
                                   NULL, 0, (SQLCHAR *)"t_bug30081", SQL_NTS,
                                   0, SQL_NO_NULLS));

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug30081");

  return OK;
}


/**
  Verify that we get correct data for SQL_DATA_TYPE and SQL_DATETIME_SUB
  from SQLColumns(). Also check SQL_DESC_TYPE from SQLColAttribute().
*/
DECLARE_TEST(t_datecolumns)
{
  SQLCHAR col[10];
  SQLLEN type;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_datecolumns");
  ok_sql(hstmt,
         "CREATE TABLE t_datecolumns(a TIMESTAMP, b DATETIME, c DATE, d TIME)");

  ok_stmt(hstmt, SQLColumns(hstmt, NULL, 0, NULL, 0,
                            (SQLCHAR *)"t_datecolumns", SQL_NTS, NULL, 0));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, col, 4), "a", 1);
  is_num(my_fetch_int(hstmt, 5), SQL_TYPE_TIMESTAMP);
  is_num(my_fetch_int(hstmt, 14), SQL_DATETIME);
  is_num(my_fetch_int(hstmt, 15), SQL_TYPE_TIMESTAMP);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, col, 4), "b", 1);
  is_num(my_fetch_int(hstmt, 5), SQL_TYPE_TIMESTAMP);
  is_num(my_fetch_int(hstmt, 14), SQL_DATETIME);
  is_num(my_fetch_int(hstmt, 15), SQL_TYPE_TIMESTAMP);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, col, 4), "c", 1);
  is_num(my_fetch_int(hstmt, 5), SQL_TYPE_DATE);
  is_num(my_fetch_int(hstmt, 14), SQL_DATETIME);
  is_num(my_fetch_int(hstmt, 15), SQL_TYPE_DATE);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, col, 4), "d", 1);
  is_num(my_fetch_int(hstmt, 5), SQL_TYPE_TIME);
  is_num(my_fetch_int(hstmt, 14), SQL_DATETIME);
  is_num(my_fetch_int(hstmt, 15), SQL_TYPE_TIME);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT * FROM t_datecolumns");

  ok_stmt(hstmt, SQLColAttribute(hstmt, 1, SQL_DESC_TYPE, NULL, 0, NULL,
                                 &type));
  is_num(type, SQL_DATETIME);
  ok_stmt(hstmt, SQLColAttribute(hstmt, 2, SQL_DESC_TYPE, NULL, 0, NULL,
                                 &type));
  is_num(type, SQL_DATETIME);
  ok_stmt(hstmt, SQLColAttribute(hstmt, 3, SQL_DESC_TYPE, NULL, 0, NULL,
                                 &type));
  is_num(type, SQL_DATETIME);
  ok_stmt(hstmt, SQLColAttribute(hstmt, 4, SQL_DESC_TYPE, NULL, 0, NULL,
                                 &type));
  is_num(type, SQL_DATETIME);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_datecolumns");
  return OK;
}


/**
  Bug #14414: SQLColumn() does not return timestamp nullable attribute correctly
*/
DECLARE_TEST(t_bug14414)
{
  SQLCHAR col[10];
  SQLSMALLINT nullable;

  if (!mysql_min_version(hdbc, "8.0.0", 5))
    skip("MySQL 8.0 or newer required");

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug14414");
  ok_sql(hstmt, "CREATE TABLE t_bug14414(a TIMESTAMP, b TIMESTAMP NOT NULL,"
        "c TIMESTAMP NULL)");

  ok_stmt(hstmt, SQLColumns(hstmt, NULL, 0, NULL, 0,
                            (SQLCHAR *)"t_bug14414", SQL_NTS, NULL, 0));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, col, 4), "a", 1);
  is_num(my_fetch_int(hstmt, 11), SQL_NULLABLE);
  is_str(my_fetch_str(hstmt, col, 18), "YES", 3);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, col, 4), "b", 1);
  is_num(my_fetch_int(hstmt, 11), SQL_NULLABLE);
  is_str(my_fetch_str(hstmt, col, 18), "YES", 3);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, col, 4), "c", 1);
  is_num(my_fetch_int(hstmt, 11), SQL_NULLABLE);
  is_str(my_fetch_str(hstmt, col, 18), "YES", 3);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /**
    Bug #26108  MyODBC ADO field attributes reporting adFldMayBeNull for
    not null columns
  */
  ok_sql(hstmt, "SELECT * FROM t_bug14414");

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 1, col, sizeof(col), NULL, NULL, NULL,
                                NULL, &nullable));
  is_num(nullable, SQL_NULLABLE);

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 2, col, sizeof(col), NULL, NULL, NULL,
                                NULL, &nullable));
  is_num(nullable, SQL_NULLABLE);

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 3, col, sizeof(col), NULL, NULL, NULL,
                                NULL, &nullable));
  is_num(nullable, SQL_NULLABLE);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug14414");
  return OK;
}


/**
 Bug #30939: SQLGetTypeInfo returns 6 instead of 8 for COLUMN_SIZE for
 SQL_TYPE_TIME
*/
DECLARE_TEST(t_bug30939)
{
  ok_stmt(hstmt, SQLGetTypeInfo(hstmt, SQL_TYPE_TIME));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 3), 8);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  return OK;
}


/**
 Bug #31009: Wrong SQL_DESC_LITERAL_PREFIX for date-time types
*/
DECLARE_TEST(t_bug31009)
{
  SQLCHAR data[20];
  SQLSMALLINT len;
  SQLLEN dlen;

  ok_sql(hstmt, "SELECT CAST('2007-01-13' AS DATE) AS col1");

  ok_stmt(hstmt, SQLColAttribute(hstmt, 1, SQL_DESC_LITERAL_PREFIX,
                                 data, sizeof(data), &len, NULL));
  is_num(len, 1);
  is_str(data, "'", 2);

  ok_stmt(hstmt, SQLColAttribute(hstmt, 1, SQL_DESC_LITERAL_SUFFIX,
                                 data, sizeof(data), &len, NULL));
  is_num(len, 1);
  is_str(data, "'", 2);

  ok_stmt(hstmt, SQLFetch(hstmt));

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &dlen));
  is_num(dlen, 10);
  is_str(data, "2007-01-13", 11);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  return OK;
}


/**
 Bug #37342: ODBC TIMESTAMP string format not handled properly by ODBC driver
*/
DECLARE_TEST(t_bug37342)
{
  SQLCHAR *date= (SQLCHAR *)"{dt '2007-01-13'}";
  SQLCHAR *time= (SQLCHAR *)"194759";
  SQLCHAR out[30];
  TIMESTAMP_STRUCT ts;
  SQLLEN len= SQL_NTS;

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_TIMESTAMP, 0, 0, date, 0, &len));

  ok_sql(hstmt, "SELECT ? AS foo");

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, out, 1), "2007-01-13", 11);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_TYPE_TIME, 0, 0, time, 0, &len));

  ok_sql(hstmt, "SELECT ? AS foo");

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, out, 1), "19:47:59", 9);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TIMESTAMP,
                                  SQL_TYPE_TIME, 0, 0, &ts, sizeof(ts), NULL));

  ts.hour= 19;
  ts.minute= 47;
  ts.second= 59;
  ts.fraction= 4000;

  /* Fractional truncation */
  expect_sql(hstmt, "SELECT ? AS foo", SQL_ERROR);
  ts.fraction= 0;

  ok_sql(hstmt, "SELECT ? AS foo");

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, out, 1), "19:47:59", 9);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


/**
  Bug#60646 - Fractions of seconds ignored in results
*/
DECLARE_TEST(t_bug60646)
{
  SQLCHAR buff[128];
  TIMESTAMP_STRUCT ts;
  SQLLEN len;
  const char *expected= "2012-01-01 01:01:01.000001";

  ok_sql(hstmt,
        "SELECT timestamp('2012-01-01 01:01:01.000001')"            /*1*/
        " ,timestamp('2012-01-01 01:01:01.100002')"                 /*2*/
        " ,'2011-07-29 17:52:15.0000000009'"                        /*3*/
        " ,'1000-01-01 12:00:00.000000001'"                         /*4*/
        " ,time('2011-12-31 23:59:59.999999')"                      /*5*/
        " ,ADDTIME('9999-12-31 23:59:59.999999', '1 1:1:1.000002')" /*6*/
        );
  ok_stmt(hstmt, SQLFetch(hstmt));

  /* Fields 1-4 checking conversions from date as a string
  /* 1) just to be sure that everything is fine with string */
  is_str(my_fetch_str(hstmt, buff, 1), expected, sizeof(expected));

  /* 2) testing if fractional part is converted to nanoseconds correctly */
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_TYPE_TIMESTAMP, &ts, sizeof(ts),
                            &len));

  is_num(ts.fraction, 100002000);

  /* 3) fractional part is less than we care (less than nanosecond).
        Test using string as MySQL does not support units less than a microsecond */
  ok_stmt(hstmt, SQLGetData(hstmt, 3, SQL_C_TYPE_TIMESTAMP, &ts, sizeof(ts),
                            &len));
  is_num(ts.fraction, 0);

  /* 4) testing if min fraction detected
        Again - mysql supports microseconds only. thus using string
   */
  ok_stmt(hstmt, SQLGetData(hstmt, 4, SQL_C_TYPE_TIMESTAMP, &ts, sizeof(ts),
                            &len));
  is_num(ts.fraction, 1);

  /* 5) if time is converted to timestamp - checking if current date is set
        and if fractional part in place. former can actually fail if day is
        being changed */

  {
    time_t sec_time= time(NULL);
    struct tm * cur_tm;

    ok_stmt(hstmt, SQLGetData(hstmt, 5, SQL_C_TYPE_TIMESTAMP, &ts, sizeof(ts),
                              &len));
    cur_tm= localtime(&sec_time);

    is_num(ts.year, 1900 + cur_tm->tm_year);
    is_num(ts.month, 1 + cur_tm->tm_mon);
    is_num(ts.day, cur_tm->tm_mday);
  }

  is_num(ts.fraction, 999999000);

  /* 6) Expecting an error because of longer date
        At the moment ADDTIME('9999-12-31 23:59:59.999999', '1 1:1:1.000002')
        will give you 10000-01-02 01:01:01.000001
        Fixed in 5.6
   */

  if (!mysql_min_version(hdbc, "5.6.", 4))
  {
    expect_stmt(hstmt, SQLGetData(hstmt, 6, SQL_C_TYPE_TIMESTAMP, &ts, sizeof(ts),
                              &len), SQL_ERROR);

    if (check_sqlstate(hstmt, "22018") != OK)
    {
      return FAIL;
    }
  }

  /* 5th col once again This time we get it in time struct. Thus we are
     loosing fractioanl part. Thus the state has to be 01S07 and
     SQL_SUCCESS_WITH_INFO returned */
  {
    SQL_TIME_STRUCT timestruct;

    expect_stmt(hstmt, SQLGetData(hstmt, 5, SQL_C_TYPE_TIME, &timestruct,
                            sizeof(timestruct), &len), SQL_SUCCESS_WITH_INFO);

    if (check_sqlstate(hstmt, "01S07") != OK)
    {
      return FAIL;
    }
  }

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  return OK;
}


/* Bug#60648 ODBC prepared statements ignore fractional part of temporal data
   types */
DECLARE_TEST(t_bug60648)
{
  SQL_TIMESTAMP_STRUCT param, result;

  param.year=     2011;
  param.month=    8;
  param.day=      6;
  param.hour=     1;
  param.minute=   2;
  param.second=   3;
  param.fraction= 1000;
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"select ?", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TYPE_TIMESTAMP,
    SQL_TYPE_DATE, 0, 0, &param, 0, NULL));

  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "22008"), OK);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TYPE_TIMESTAMP,
    SQL_TYPE_TIME, 0, 0, &param, 0, NULL));

  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "22008"), OK);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TYPE_TIMESTAMP,
    SQL_TYPE_TIMESTAMP, 0, 0, &param, 0, NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_TYPE_TIMESTAMP, &result, 0,
                            NULL));

  is_num(1000, result.fraction);

  return OK;
}


DECLARE_TEST(t_b13975271)
{
  if (!mysql_min_version(hdbc, "5.6.", 4))
  {
    skip("Necessary feature added in MySQL 5.6.*");
  }
  else
  {
    SQLCHAR ts[27];
    SQLLEN len;

    ok_sql(hstmt, "DROP TABLE IF EXISTS t_b13975271");
    ok_sql(hstmt, "CREATE TABLE t_b13975271 (ts TIMESTAMP(6), dt DATETIME(6),\
                    t TIME(6))");

    strcpy((char *)ts, "2012-04-25 10:20:49.0194");

    ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                    SQL_TIMESTAMP,0, 0, ts, sizeof(ts), NULL));
    ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
                                    SQL_CHAR,0, 0, ts, sizeof(ts), NULL));
    ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_CHAR,
                                    SQL_CHAR,0, 0, ts, sizeof(ts), NULL));
    ok_stmt(hstmt, SQLPrepare(hstmt, "INSERT INTO t_b13975271(ts,dt,t) \
                                      VALUES (?,?,?)",
                              SQL_NTS));
    ok_stmt(hstmt, SQLExecute(hstmt));

    ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));
    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

    /* now fetch and verify the results .. */
    ok_sql(hstmt, "SELECT * FROM t_b13975271");

    ok_stmt(hstmt, SQLFetch(hstmt));
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, ts, sizeof(ts), &len));
    is_str(ts, "2012-04-25 10:20:49.019400", 26);

    /*To make sure that for next test we compare not the data from prev call */
    ts[0]='\0';
    ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_CHAR, ts, sizeof(ts), &len));
    is_str(ts, "2012-04-25 10:20:49.0194", 24);
    ok_stmt(hstmt, SQLGetData(hstmt, 3, SQL_C_CHAR, ts, sizeof(ts), &len));
    is_str(ts, "10:20:49.0194", 13);

    ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

    ok_sql(hstmt, "DROP TABLE IF EXISTS t_b13975271");
  }

  return OK;
}


DECLARE_TEST(t_17613161)
{
  SQL_TIME_STRUCT ts, result;
  SQL_INTERVAL_STRUCT h2s, interval;

  h2s.intval.day_second.hour=   ts.hour  = 100;
  h2s.intval.day_second.minute= ts.minute= 20;
  h2s.intval.day_second.second= ts.second= 45;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_17613161");

  ok_sql(hstmt, "CREATE TABLE t_17613161(col1 time)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"INSERT INTO t_17613161 "
                            "(col1) VALUES (?)",SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TIME,
                                  SQL_TIME, 0, 0, &ts, sizeof(ts), NULL));
  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "22008"), OK);

  /* Such conversion is not supported */
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TIME,
                                  SQL_INTERVAL_HOUR_TO_SECOND, 0, 0, &ts, sizeof(ts), NULL));
 expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);
 is_num(check_sqlstate(hstmt, "07006"), OK);

  /* For interval types big hours should work fine */
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_INTERVAL_HOUR_TO_SECOND,
                                  SQL_INTERVAL_HOUR_TO_SECOND, 0, 0, &h2s, sizeof(h2s), NULL));
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  ok_sql(hstmt,"SELECT * FROM t_17613161");

  ok_stmt(hstmt, SQLFetch(hstmt));

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_TYPE_TIME, &result, 0,
                            NULL), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "22007"), OK);

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_INTERVAL_HOUR_TO_MINUTE, &interval, 0,
                        NULL), SQL_SUCCESS_WITH_INFO);
  is_num(check_sqlstate(hstmt, "01S07"), OK);

  is_num(interval.intval.day_second.second, 0);
  is_num(interval.intval.day_second.minute, 20);
  is_num(interval.intval.day_second.hour, 100);

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_INTERVAL_HOUR_TO_SECOND, &interval, 0,
                        NULL));

  is_num(interval.intval.day_second.second, 45);
  is_num(interval.intval.day_second.minute, 20);
  is_num(interval.intval.day_second.hour, 100);
  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_17613161");

  return OK;
}


DECLARE_TEST(t_17613161_bookmark)
{
  SQLCHAR bData[2][10];
  SQL_TIME_STRUCT tm[2]= {0};
  SQL_INTERVAL_STRUCT h2s[2];

  h2s[0].intval.day_second.hour=   tm[0].hour   = 11;
  h2s[0].intval.day_second.minute= tm[0].minute = 02;
  h2s[0].intval.day_second.second= tm[0].second = 19;

  h2s[1].intval.day_second.hour=   tm[1].hour  = 100;
  h2s[1].intval.day_second.minute= tm[1].minute= 20;
  h2s[1].intval.day_second.second= tm[1].second= 45;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_17613161_bookmark");

  ok_sql(hstmt,"CREATE TABLE t_17613161_bookmark(col1 time)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)2, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_USE_BOOKMARKS,
                                (SQLPOINTER) SQL_UB_VARIABLE, 0));

  ok_sql(hstmt, "SELECT * FROM t_17613161_bookmark");

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0),
              SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLBindCol(hstmt, 0, SQL_C_VARBOOKMARK, bData,
                            sizeof(bData[0]), NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_TYPE_TIME, tm,
                            sizeof(tm[0]), NULL));

  expect_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "22008"), OK);

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_INTERVAL_HOUR_TO_SECOND, h2s,
                            sizeof(h2s[0]), NULL));
  ok_stmt(hstmt, SQLBulkOperations(hstmt, SQL_ADD));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));

  ok_sql(hstmt, "SELECT * FROM t_17613161_bookmark");
  is_num(myrowcount(hstmt), 2);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  is_num(atol(bData[0]), 1);
  is_num(tm[0].hour, 11);
  is_num(tm[0].minute, 02);
  is_num(tm[0].second, 19);

  is_num(atol(bData[1]), 2);
  is_num(tm[1].hour, 100);
  is_num(tm[1].minute, 20);
  is_num(tm[1].second, 45);

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_17613161_bookmark");

  return OK;
}


/**
 Bug #25386024: Error "Date Overflow"
*/
DECLARE_TEST(t_date_overflow)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
           NULL, NULL, NULL, "NO_DATE_OVERFLOW=1"));

  char *query_prep = "INSERT INTO t_date_overflow (a) VALUES (?)";
  SQL_TIMESTAMP_STRUCT ts;

  ts.day = 14;
  ts.month = 2;
  ts.year = 2017;
  ts.hour = 16;
  ts.minute = 11;
  ts.second = 45;
  ts.fraction = 0;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_date_overflow");
  ok_sql(hstmt, "CREATE TABLE t_date_overflow (a DATE)");

  /* By default the INSERT should end with the error */
  ok_stmt(hstmt, SQLPrepare(hstmt, query_prep, SQL_NTS));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_TIMESTAMP,
                                  SQL_DATE, 0, 0, &ts, sizeof(ts), NULL));

  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  /* Empty result is expected */
  ok_sql(hstmt, "SELECT * FROM t_date_overflow");
  is_num(my_print_non_format_result(hstmt), 0);

  /* The INSERT with NO_DATE_OVERFLOW=1 should be successful */
  ok_stmt(hstmt1, SQLPrepare(hstmt1, query_prep, SQL_NTS));
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_TIMESTAMP,
                                  SQL_DATE, 0, 0, &ts, sizeof(ts), NULL));

  ok_stmt(hstmt1, SQLExecute(hstmt1));

  /* One row is expected */
  ok_sql(hstmt, "SELECT * FROM t_date_overflow");
  is_num(my_print_non_format_result(hstmt), 1);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_date_overflow");
  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}



BEGIN_TESTS
  // ADD_TEST(t_bug60646)
  ADD_TEST(t_bug37342)
  ADD_TEST(t_bug14414)
  ADD_TEST(t_bug30081)
  ADD_TEST(t_bug9927)
  ADD_TEST(t_bug15773)
  ADD_TEST(t_bug12520)
  ADD_TEST(t_time)
  ADD_TEST(t_date_overflow)
  ADD_TEST(my_ts)
  ADD_TEST(t_tstotime)
  ADD_TEST(t_tstotime1)
  ADD_TEST(t_datecolumns)
  ADD_TEST(t_bug31009)
  ADD_TEST(t_17613161)
#ifndef USE_IODBC
  ADD_TEST(t_17613161_bookmark)
#endif
  ADD_TEST(t_bug25846)
  ADD_TEST(t_time1)
  ADD_TEST(t_bug30939)
  ADD_TEST(t_bug60648)
  ADD_TEST(t_b13975271)
END_TESTS


RUN_TESTS
