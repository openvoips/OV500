// Copyright (c) 2012, 2024, Oracle and/or its affiliates.
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
#include "../VersionInfo.h"


/*
  Bug #32420 - Don't cache results and SQLExtendedFetch work badly together
*/
DECLARE_TEST(t_bug32420)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLINTEGER nData[4];
  SQLCHAR szData[4][16];
  SQLUSMALLINT rgfRowStatus[4];

  SET_DSN_OPTION(1048576);

  alloc_basic_handles(&henv1, &hdbc1, &hstmt1);

  ok_sql(hstmt1, "drop table if exists bug32420");
  ok_sql(hstmt1, "CREATE TABLE bug32420 ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt1, "INSERT INTO bug32420 VALUES "\
                "(100, 'string 1'),"\
                "(200, 'string 2'),"\
                "(300, 'string 3'),"\
                "(400, 'string 4')");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_ROWSET_SIZE, 4));

  ok_sql(hstmt1, "select * from bug32420 order by 1");
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));
  ok_stmt(hstmt1, SQLExtendedFetch(hstmt1, SQL_FETCH_NEXT, 0, NULL,
                                   rgfRowStatus));

  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_sql(hstmt1, "drop table if exists bug32420");

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  SET_DSN_OPTION(1048576);

  return OK;
}


/**
 Bug #34575: SQL_C_CHAR value type and numeric parameter type causes trouble
*/
DECLARE_TEST(t_bug34575)
{
  SQLCHAR buff[10];
  SQLLEN len= 0;

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *) "SELECT ?", SQL_NTS));
  strcpy((char *)buff, "2.0");
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_DECIMAL, 10, 0, buff, sizeof(buff),
                                  &len));

  /* Note: buff has '2.0', but len is still 0! */
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 1), "", 1);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  strcpy((char *)buff, "2.0");
  len= 3;

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 1), "2.0", 4);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


/*
Bug #24131 SHOW CREATE TABLE result truncated with mysql 3.23 and ODBC driver 3.51.12.00
*/
DECLARE_TEST(t_bug24131)
{
  SQLCHAR buff[1024];
  SQLLEN boundLen= 0;
  SQLULEN count;
  UWORD status;
  SQLULEN colSize;

  ok_sql(hstmt, "drop table if exists bug24131");

  /* Table definition should be long enough. */
  ok_sql(hstmt, "CREATE TABLE `bug24131` ("
    "`Codigo` int(10) unsigned NOT NULL auto_increment,"
    "`Nombre` varchar(255) default NULL,"
    "`Telefono` varchar(255) default NULL,"
    "`Observaciones` longtext,"
    "`Direccion` varchar(255) default NULL,"
    "`Dni` varchar(255) default NULL,"
    "`CP` int(11) default NULL,"
    "`Provincia` varchar(255) default NULL,"
    "`Poblacion` varchar(255) default NULL,"
    "PRIMARY KEY  (`Codigo`)"
    ") ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"show create table bug24131", SQL_NTS));

  ok_stmt(hstmt, SQLBindCol(hstmt,2,SQL_C_BINARY, buff, 1024, &boundLen));

  /* Note: buff has '2.0', but len is still 0! */
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 2, buff, sizeof(buff), NULL, NULL,
                                &colSize, NULL, NULL));

  ok_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, &count, &status));

  if (sizeof(SQLLEN) == 4)
    printMessage("colSize: %lu, boundLen: %ld", colSize, boundLen);
  else
    printMessage("colSize: %llu, boundLen: %lld", colSize, boundLen);
  is(colSize >= (SQLULEN)boundLen);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists bug24131");

  return OK;
}


/*
  Bug #36069 - SQLProcedures followed by a SQLFreeStmt causes a crash
 */
DECLARE_TEST(t_bug36069)
{
  SQLSMALLINT size;

  ok_stmt(hstmt, SQLProcedures(hstmt, NULL, 0, NULL, 0,
                               (SQLCHAR *)"non-existing", SQL_NTS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &size));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"select ?", SQL_NTS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &size));

  return OK;
}


/*
  Bug #41942 - SQLDescribeCol() segfault with non-zero name length
  and null buffer
*/
DECLARE_TEST(t_bug41942)
{
  SQLSMALLINT len;
  ok_sql(hstmt, "select 1 as name");
  ok_stmt(hstmt, SQLDescribeCol(hstmt, 1, NULL, 10, &len,
                                NULL, NULL, NULL, NULL));
  is_num(len, 4);
  return OK;
}


/*
  Bug 39644 - Binding SQL_C_BIT to an integer column is not working
 */
DECLARE_TEST(t_bug39644)
{
  char col1 = 0x3f;
  char col2 = 0xff;
  char col3 = 0x0;
  char col4 = 0x1;

  ok_sql(hstmt, "drop table if exists t_bug39644");
  ok_sql(hstmt, "create table t_bug39644(col1 INT, col2 INT,"\
              "col3 BIT, col4 BIT)");

  ok_sql(hstmt, "insert into t_bug39644 VALUES (5, 0, 1, 0)");

  /* Do SELECT */
  ok_sql(hstmt, "SELECT * from t_bug39644");

  /* Now bind buffers */
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_BIT, &col1, sizeof(char), 0));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_BIT, &col2, sizeof(char), 0));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_BIT, &col3, sizeof(char), 0));
  ok_stmt(hstmt, SQLBindCol(hstmt, 4, SQL_C_BIT, &col4, sizeof(char), 0));

  /* Fetch and check results */
  ok_stmt(hstmt, SQLFetch(hstmt));

  is( col1 == 1 );
  is( col2 == 0 );
  is( col3 == 1 );
  is( col4 == 0 );

  /* Clean-up */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table t_bug39644");

  return OK;
}


/*
Bug#32821(it might be duplicate though): Wrong value if bit field is bound to
other than SQL_C_BIT variable
*/
DECLARE_TEST(t_bug32821)
{
  SQLRETURN     rc;
  SQLUINTEGER   b;
  SQLUSMALLINT  c;
  SQLLEN        a_ind, b_ind, c_ind, i, j, k;
  unsigned char a;

  SQL_NUMERIC_STRUCT b_numeric;

  SQLUINTEGER par=  sizeof(SQLUSMALLINT)*8+1;
  SQLUINTEGER beoyndShortBit= 1<<(par-1);
  SQLLEN      sPar= sizeof(SQLUINTEGER);

  /* 131071 = 0x1ffff - all 1 for field c*/
  SQLCHAR * insStmt= "insert into t_bug32821 values (0,0,0),(1,1,1)\
                      ,(1,255,131071),(1,258,?)";
  const unsigned char expected_a[]= {'\0', '\1', '\1', '\1'};
  const SQLUINTEGER   expected_b[]= {0L, 1L, 255L, 258L};
  const SQLUSMALLINT  expected_c[]= {0, 1, 65535, 0};

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug32821");

  ok_stmt(hstmt, SQLPrepare(hstmt, "CREATE TABLE t_bug32821 (a BIT(1), b BIT(16)\
                                   , c BIT(?))", SQL_NTS));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_ULONG
    , SQL_INTEGER, 0, 0, &par, 0, &sPar ));
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLPrepare(hstmt, insStmt, SQL_NTS));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_ULONG
    , SQL_INTEGER, 0, 0, &beoyndShortBit, 0
    , &sPar ));
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "SELECT a,b,c FROM t_bug32821");

  ok_stmt( hstmt, SQLBindCol( hstmt, 1, SQL_C_BIT,    &a, 0, &a_ind ) );
  ok_stmt( hstmt, SQLBindCol( hstmt, 2, SQL_C_ULONG,  &b, 0, &b_ind ) );
  /*ok_stmt( hstmt, SQLBindCol( hstmt, 1, SQL_C_TYPE_DATE,  &d, 0, &b_ind ) );*/
  ok_stmt( hstmt, SQLBindCol( hstmt, 3, SQL_C_USHORT, &c, 0, &c_ind ) );

  i= 0;
  while( (rc= SQLFetchScroll(hstmt, SQL_FETCH_NEXT, 0)) != SQL_NO_DATA_FOUND)
  {
    printMessage("testing row #%d", i+1);
    is_num(a, expected_a[i]);
    is_num(b, expected_b[i]);
    is_num(c, expected_c[i]);

    /* Test of binding to numeric - added later so a bit messy */
    for (k= 1; k < 3; ++k)
    {
      b_ind= sizeof(SQL_NUMERIC_STRUCT);
      SQLGetData(hstmt, (SQLUSMALLINT)k, SQL_C_NUMERIC, &b_numeric, 0, &b_ind);

      b= 0;
      for(j= 0; j < b_numeric.precision; ++j)
      {
        b+= (0xff & b_numeric.val[j]) << 8*j;
      }

      switch (k)
      {
      case 1: is_num(b, expected_a[i]); break;
      case 2: is_num(b, expected_b[i]); break;
      }

    }

    ++i;
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug32821");
  return OK;
}


/*
  Bug #34271 - C/ODBC 5.1 does not list table fields in MSQRY32
*/
DECLARE_TEST(t_bug34271)
{
  SQLINTEGER x1= 0, x2= 0;

  /* execute the query, but bind only the first column */
  ok_sql(hstmt, "select 1,2");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &x1, 0, NULL));
  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(x1, 1);
  is_num(x2, 0);
  x1= 0;

  /* unbind */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));

  /* execute the query, but bind only the second column */
  ok_sql(hstmt, "select 1,2");

  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_LONG, &x2, 0, NULL));
  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(x1, 0);
  is_num(x2, 2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


/*
  Bug#32684 - chunked retrieval of SQL_C_WCHAR fails
*/
DECLARE_TEST(t_bug32684)
{
  SQLWCHAR wbuf[20];
  SQLCHAR abuf[20];
  SQLLEN wlen, alen;
  ok_sql(hstmt, "select repeat('x', 100), repeat('y', 100)");
  ok_stmt(hstmt, SQLFetch(hstmt));

  do
  {
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, abuf,
                              20, &alen));
    printMessage("data= %s, len=%d\n", abuf, alen);
  } while(alen > 20);

  do
  {
    ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_WCHAR, wbuf,
                              20 * sizeof(SQLWCHAR), &wlen));
    wprintf(L"# data= %s, len=%d\n\n", wbuf, (int)wlen);
  } while(wlen > 20 * sizeof(SQLWCHAR));

  return OK;
}


/*
  Bug 55024 - Wrong type returned by SQLColAttribute(SQL_DESC_PRECISION...) in 64-bit Linux
 */
DECLARE_TEST(t_bug55024)
{
  SQLSMALLINT len;
  SQLLEN      res;

  ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS t_test55024", SQL_NTS));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "CREATE TABLE t_test55024(col01 LONGTEXT, "\
                                                                  "col02 BINARY(16),"\
                                                                  "col03 VARBINARY(16),"\
                                                                  "col04 LONGBLOB,"\
                                                                  "col05 BIGINT,"\
                                                                  "col06 TINYINT,"\
                                                                  "col07 BIT, col08 DOUBLE"\
                                                                  ") CHARSET latin1", SQL_NTS));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "INSERT INTO t_test55024 VALUES ('a', 'b', 'c', 'd', 999, 111, 1, 3.1415)", SQL_NTS));


  ok_stmt(hstmt, SQLExecDirect(hstmt, "SELECT * FROM t_test55024", SQL_NTS));

  ok_stmt(hstmt, SQLColAttribute(hstmt, 1, SQL_DESC_TYPE, NULL, 0, &len, &res));
  is_num(res, SQL_LONGVARCHAR);

  ok_stmt(hstmt, SQLColAttribute(hstmt, 2, SQL_DESC_TYPE, NULL, 0, &len, &res));
  is_num(res, SQL_BINARY);

  ok_stmt(hstmt, SQLColAttribute(hstmt, 3, SQL_DESC_TYPE, NULL, 0, &len, &res));
  is_num(res, SQL_VARBINARY);

  ok_stmt(hstmt, SQLColAttribute(hstmt, 4, SQL_DESC_TYPE, NULL, 0, &len, &res));
  is_num(res, SQL_LONGVARBINARY);

  ok_stmt(hstmt, SQLColAttribute(hstmt, 5, SQL_DESC_TYPE, NULL, 0, &len, &res));
  is_num(res, SQL_BIGINT);

  ok_stmt(hstmt, SQLColAttribute(hstmt, 6, SQL_DESC_TYPE, NULL, 0, &len, &res));
  is_num(res, SQL_TINYINT);

  ok_stmt(hstmt, SQLColAttribute(hstmt, 7, SQL_DESC_TYPE, NULL, 0, &len, &res));
  is_num(res, SQL_BIT);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug55024");
  return OK;
}


/*
Bug #56677 - SQLNumResultCols() causes the driver to return
only first row in the resultset
*/
DECLARE_TEST(t_bug56677)
{
  SQLINTEGER  nData;
  SQLCHAR     szData[16];
  SQLSMALLINT colCount;

  ok_sql(hstmt, "drop table if exists bug56677");
  ok_sql(hstmt, "CREATE TABLE bug56677 ("\
    "tt_int INT PRIMARY KEY auto_increment,"\
    "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt, "INSERT INTO bug56677 VALUES "\
    "(100, 'string 1'),"\
    "(200, 'string 2'),"\
    "(300, 'string 3'),"\
    "(400, 'string 4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLPrepare(hstmt, "select * from bug56677", SQL_NTS));
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &colCount));

  is_num(colCount, 2);

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
    NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(nData, 100);
  is_str(szData, "string 1", 8);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(nData, 200);
  is_str(szData, "string 2", 8);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(nData, 300);
  is_str(szData, "string 3", 8);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(nData, 400);
  is_str(szData, "string 4", 8);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists bug56677");

  return OK;
}


/* Test of SQLDescribeCol and SQLColAttribute if they are called before SQLExecute.
   Bug#56717 */
DECLARE_TEST(t_desccol_before_exec)
{
  SQLINTEGER  nData= 200;
  SQLCHAR     szData[128];
  SQLSMALLINT colCount;
  char        colname[MYSQL_NAME_LEN];
  SQLULEN     collen;
  SQLLEN      coltype;

  ok_sql(hstmt, "drop table if exists desccol_before_exec");
  ok_sql(hstmt, "CREATE TABLE desccol_before_exec ("\
    "tt_int INT PRIMARY KEY auto_increment,"\
    "tt_varchar VARCHAR(128) CHARACTER SET latin1 NOT NULL)");
  ok_sql(hstmt, "INSERT INTO desccol_before_exec VALUES "\
    "(100, 'string 1'),"\
    "(200, 'string 2'),"\
    "(300, 'string 3'),"\
    "(400, 'string 4')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLPrepare(hstmt, "select tt_varchar from desccol_before_exec where tt_int > ?", SQL_NTS));

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 1, colname, sizeof(colname), NULL,
    NULL, &collen, NULL, NULL));

  is_str(colname, "tt_varchar", 11);
  is_num(collen, 128);

  /* Just to make sure that SQLNumResultCols still works fine */
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &colCount));

  is_num(colCount, 1);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, szData, sizeof(szData),
    NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(szData, "string 3", 8);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(szData, "string 4", 8);

  /* Now doing all the same things with SQLColAttribute */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLPrepare(hstmt, "select tt_int, tt_varchar "
                                   "from desccol_before_exec "
                                   "where tt_int <= ?", SQL_NTS));

  ok_stmt(hstmt, SQLColAttribute(hstmt, 2, SQL_DESC_TYPE, NULL, 0, NULL, &coltype));
  is_num(coltype, SQL_VARCHAR);

  /* Just to make sure that SQLNumResultCols still works fine */
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &colCount));

  is_num(colCount, 2);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData),
    NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(szData, "string 1", 8);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(szData, "string 2", 8);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_sql(hstmt, "drop table if exists desccol_before_exec");

  return OK;
}


/* Bug #62657 	A failure on one stmt causes another stmt to fail */
DECLARE_TEST(t_bug62657)
{
  SQLHSTMT hstmt1;

  ok_sql(hstmt, "DROP table IF EXISTS b62657");

  ok_sql(hstmt, "CREATE table b62657(i int)");

  ok_sql(hstmt, "insert into b62657 values(1),(2)");


  ok_stmt(hstmt, SQLExecDirect(hstmt, "select * from b62657", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));

  /* Any failing  query would do the job here */
  ok_con(hstmt1, SQLAllocStmt(hdbc, &hstmt1));

  expect_sql(hstmt1, "select * from some_ne_rubbish", SQL_ERROR);

  /* Error of other query before all rows fetched causes next fetch
     to fail */
  ok_stmt(hstmt, SQLFetch(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP table b62657");

  return OK;
}


DECLARE_TEST(t_row_status)
{
  SQLHANDLE ird, ard;
  SQLUSMALLINT arr1[2], arr2[2], i, j;
  const SQLUSMALLINT expectedRow1[]= {SQL_ROW_SUCCESS, SQL_ROW_NOROW},
    expectedRow2[][2]= { {SQL_ROW_SUCCESS, SQL_ROW_SUCCESS},
                      {SQL_ROW_SUCCESS_WITH_INFO, SQL_ROW_ERROR}
                    },
  expectedFunction2[2]= {SQL_SUCCESS, SQL_SUCCESS_WITH_INFO};

  SQLCHAR res[5*2];

  ok_sql(hstmt, "DROP table IF EXISTS b_row_status");

  ok_sql(hstmt, "CREATE table b_row_status(i int)");

  ok_sql(hstmt, "insert into b_row_status values(4),(2),(1),(NULL)");

  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_IMP_ROW_DESC,
                                &ird, SQL_IS_POINTER, NULL));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                &ard, SQL_IS_POINTER, NULL));

  ok_desc(ird, SQLSetDescField(ird, 0, SQL_DESC_ARRAY_STATUS_PTR,
                                (SQLPOINTER)arr1, SQL_IS_POINTER));
  ok_desc(ird, SQLSetDescField(ard, 0, SQL_DESC_ARRAY_SIZE,
                                (SQLPOINTER)2, SQL_IS_INTEGER));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "select * from b_row_status\
                                       where i=1", SQL_NTS));

  /* it has to be SQL_SUCCESS here */
  expect_stmt(hstmt, SQLExtendedFetch(hstmt, SQL_FETCH_NEXT, 1, NULL,
                                  (SQLUSMALLINT*)&arr2), SQL_SUCCESS);

  /*expect_stmt(hstmt, SQLFetch(hstmt), SQL_SUCCESS);*/
  for (i= 0; i<2; ++i)
  {
    printMessage("Row %d, Desc %d, Parameter %d", i+1, arr1[i], arr2[i]);
    is_num(expectedRow1[i], arr1[i]);
    is_num(arr1[i], arr2[i]);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "select if(i is NULL,NULL,repeat(char(64+i),8/i))\
                                       from b_row_status\
                                       order by i desc", SQL_NTS));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, res, 5, NULL));

  for (i= 0; i<2; ++i)
  {
    expect_stmt(hstmt, SQLFetch(hstmt), expectedFunction2[i]);
    for (j= 0; j<2; ++j)
    {
      printMessage("Set %d Row %d, desc %d, parameter %d", i+1, j+1, arr1[j],
                    arr2[j]);
      is_num(expectedRow2[i][j], arr1[j]);
    }
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP table b_row_status");

  return OK;
}


DECLARE_TEST(t_prefetch)
{
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL, "PREFETCH=5"));

    ok_sql(hstmt, "DROP table IF EXISTS b_prefecth");
    ok_sql(hstmt, "CREATE table b_prefecth(i int)");

    ok_sql(hstmt, "insert into b_prefecth values(1),(2),(3),(4),(5),(6),(7)");

    ok_stmt(hstmt1, SQLPrepare(hstmt1, "select* from b_prefecth;    ", SQL_NTS));
    ok_stmt(hstmt1, SQLExecute(hstmt1));

    free_basic_handles(&henv1, &hdbc1, &hstmt1);

    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL, "MULTI_STATEMENTS=1"));

    ok_sql(hstmt1, "select* from b_prefecth;\
                   select * from b_prefecth where i < 7; ");

    is_num(7, myrowcount(hstmt1));

    ok_stmt(hstmt1, SQLMoreResults(hstmt1));

    is_num(6, myrowcount(hstmt1));

    expect_stmt(hstmt1, SQLMoreResults(hstmt1), SQL_NO_DATA);

    free_basic_handles(&henv1, &hdbc1, &hstmt1);

    ok_sql(hstmt, "DROP table IF EXISTS b_prefecth");

    return OK;
}

DECLARE_TEST(t_bug17386788)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLINTEGER nidata=7;

  ok_sql(hstmt, "DROP table IF EXISTS b_bug17386788");
  ok_sql(hstmt, "CREATE table b_bug17386788(i int)");

  ok_sql(hstmt, "insert into b_bug17386788 values(1),(2),(3),(4),(5),(6),(7)");

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL, "PREFETCH=5"));

  ok_stmt(hstmt1,
          SQLPrepare(hstmt1,
                     (SQLCHAR *)"select * from b_bug17386788 where i < ? ",
                     SQL_NTS)
          );


  ok_stmt(hstmt1,
          SQLBindParameter(hstmt1,1,SQL_PARAM_INPUT, SQL_C_LONG,SQL_INTEGER,
                           0,0,&nidata,20,NULL)
          );

  ok_stmt(hstmt1, SQLExecute(hstmt1));

  is_num(6, myrowcount(hstmt1));

  expect_stmt(hstmt1, SQLMoreResults(hstmt1), SQL_NO_DATA);

  ok_sql(hstmt, "DROP table IF EXISTS b_bug17386788");

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;

}


DECLARE_TEST(t_outparams)
{
  SQLSMALLINT ncol, i;
  SQLINTEGER par[3]= {10, 20, 30}, val;
  SQLLEN len = 0;

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS p_outparams");
  ok_sql(hstmt, "CREATE PROCEDURE p_outparams("
                "  IN p_in INT, "
                "  OUT p_out INT, "
                "  INOUT p_inout INT) "
                "BEGIN "
                "  SELECT p_in, p_out, p_inout; "
                "  SET p_in = 100, p_out = 200, p_inout = 300; "
                "  SELECT p_inout, p_in, p_out;"
                "END");


  for (i=0; i < sizeof(par)/sizeof(SQLINTEGER); ++i)
  {
    ok_stmt(hstmt, SQLBindParameter(hstmt, i+1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0,
      0, &par[i], 0, NULL));
  }

  ok_sql(hstmt, "CALL p_outparams(?, ?, ?)");

  /* rs-1 */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 3);

  is_num(my_fetch_int(hstmt, 1), 10);
  /* p_out does not have value at the moment */
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_INTEGER, &val, 0, &len));
  is_num(len, SQL_NULL_DATA);
  is_num(my_fetch_int(hstmt, 3), 30);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  /* rs-2 */
  ok_stmt(hstmt, SQLMoreResults(hstmt));

  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 3);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 300);
  is_num(my_fetch_int(hstmt, 2), 100);
  is_num(my_fetch_int(hstmt, 3), 200);

  /* rs-3 out params */
  ok_stmt(hstmt, SQLMoreResults(hstmt));
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 2);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 200);
  is_num(my_fetch_int(hstmt, 2), 300);
  /* Only 1 row always */
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  /* SP execution status */
  ok_stmt(hstmt, SQLMoreResults(hstmt));
  expect_stmt(hstmt, SQLMoreResults(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  ok_sql(hstmt, "DROP PROCEDURE p_outparams");
  return OK;
}


/*
  Bug #11766437: Incorrect increment(increments in multiple of SQLLEN) of
  pointer to the length/indicator buffer(last parameter of SQLBindCol),
  which gives incorrrect result when SQL_ATTR_ROW_BIND_TYPE is set to
  size of data inserted which is not not multiple of 8 on 64 bit
  system where sizeof SQLLEN is 8.
  Tests for data fetched with SQL_ATTR_ROW_BIND_TYPE size set to
  multiple of 2, binded buffers are checked for proper data fetch.
*/
DECLARE_TEST(t_bug11766437)
{
  SQLINTEGER rowcnt= 3;
  SQLINTEGER i, incr;
  SQLCHAR tbuf[50];
  char *ptr;
  char rows[500]= {0};
  SQLINTEGER MAX_CHAR_SIZE= 7; /*max size for character name*/

  ok_sql(hstmt, "drop table if exists t_bug11766437");
  ok_sql(hstmt, "create table t_bug11766437 (id int not null, "
                "name varchar(7))");
  ok_sql(hstmt, "insert into t_bug11766437 values "
                "(0, 'name0'),(1,'name1'),(2,'name2')");
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)((size_t)rowcnt), 0));

  /*
    With same text inserted we change binding orientation
    to verify our changes
  */
  for (incr= 0; incr <= 24; incr += 2)
  {
    size_t row_size= sizeof(SQLINTEGER) + sizeof(SQLLEN) +
              sizeof(SQLLEN) + MAX_CHAR_SIZE + incr;

    /*
      Set SQL_ATTR_ROW_BIND_TYPE to the size of the data inserted
      with multiple of 2 increment
    */
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_TYPE,
                                  (SQLPOINTER)row_size, 0));

    /*
      Binding all parameters with same buffer to test proper
      increment of last parameter of SQLBindCol
    */
    ptr= rows;
    ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG,
      (SQLPOINTER) ptr,
      (SQLLEN) sizeof(SQLINTEGER),
      (SQLLEN *) (ptr + sizeof(SQLINTEGER))));

    /*
      Incrementing pointer position by sizeof(SQLINTEGER) i.e. size of id
      and sizeof(SQLLEN) bytes required to store length of id
    */
    ptr += sizeof(SQLINTEGER) + sizeof(SQLLEN);
    ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR,
      (SQLPOINTER) ptr,
      (SQLLEN) MAX_CHAR_SIZE,
      (SQLLEN *) (ptr + MAX_CHAR_SIZE)));

    ok_sql(hstmt, "select id,name from t_bug11766437 order by id");

    ok_stmt(hstmt, SQLFetch(hstmt));

    ptr= rows;
    for (i= 0; i < rowcnt; ++i)
    {
      /* Verifying inserted id field */
      is_num(*((SQLINTEGER *)ptr), i);
      /* Incrementing ptr by sizeof(SQLINTEGER) i.e. size of id column */
      ptr += sizeof(SQLINTEGER);

      /* Verifying length of id field which should be sizeof(SQLINTEGER) */
      is_num(*((SQLLEN *)ptr), sizeof(SQLINTEGER));
      /* Incrementing ptr by sizeof(SQLLEN) last parameter of SQLBindCol  */
      ptr += sizeof(SQLLEN);

      sprintf((char *)tbuf, "name%d", i);
      /* Verifying inserted name field */
      is_str(ptr, tbuf, strlen(tbuf));
      /* Incrementing ptr by MAX_CHAR_SIZE (max size kept for name column) */
      ptr+=MAX_CHAR_SIZE;

      /* Verifying length of name field */
      is_num(*((SQLLEN *)ptr), strlen(tbuf));
      /* Incrementing ptr by sizeof(SQLLEN) last parameter of SQLBindCol  */
      ptr += sizeof(SQLLEN);

      /* Incrementing ptr by incr to test multiples of 2 */
      ptr += incr;
    }
    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_bug11766437");

  return OK;
}


/**
  Bind column 0 with SQL_C_VARBOOKMARK data type and retrieve bookmark
  using FetchOrientation SQL_FETCH_BOOKMARK.
  Set position in rowset using SQLSetPos and retrieve bookmark for that
  position using SQLGetData and save that particular bookmark position
  using SQL_ATTR_FETCH_BOOKMARK_PTR. Now fetch using using
  FetchOrientation SQL_FETCH_BOOKMARK with retrieve data from that position.
*/
DECLARE_TEST(t_varbookmark)
{
  SQLLEN len= 0;
  SQLCHAR abookmark[20];
  SQLLEN outlen;
  SQLUSMALLINT rowStatus[11];
  SQLUINTEGER numRowsFetched;
  SQLINTEGER nData[11];
  SQLCHAR szData[11][16];
  SQLCHAR bData[11][10];

  ok_sql(hstmt, "drop table if exists t_bookmark");
  ok_sql(hstmt, "CREATE TABLE t_bookmark ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt, "INSERT INTO t_bookmark VALUES "\
                "(100, 'string 1'),"\
                "(200, 'string 2'),"\
                "(300, 'string 3'),"\
                "(400, 'string 4'),"\
                "(500, 'string 5'),"\
                "(600, 'string 6'),"\
                "(700, 'string 7'),"\
                "(800, 'string 8'),"\
                "(900, 'string 9'),"\
                "(1000, 'string 10'),"\
                "(1100, 'string 11')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_USE_BOOKMARKS,
                                (SQLPOINTER) SQL_UB_VARIABLE, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_STATUS_PTR,
                                (SQLPOINTER)rowStatus, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR,
                                &numRowsFetched, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_STATIC, 0));


  ok_stmt(hstmt, SQLSetStmtOption(hstmt, SQL_ROWSET_SIZE, 11));

  ok_sql(hstmt, "select * from t_bookmark order by 1");
  ok_stmt(hstmt, SQLBindCol(hstmt, 0, SQL_C_VARBOOKMARK, bData,
                          sizeof(bData[0]), NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 0));

  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(atol(bData[0]), 1);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(atol(bData[1]), 2);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(atol(bData[2]), 3);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);
  is_num(atol(bData[3]), 4);

  is_num(nData[4], 500);
  is_str(szData[4], "string 5", 8);
  is_num(atol(bData[4]), 5);
  is_num(nData[5], 600);
  is_str(szData[5], "string 6", 8);
  is_num(atol(bData[5]), 6);
  is_num(nData[6], 700);
  is_str(szData[6], "string 7", 8);
  is_num(atol(bData[6]), 7);
  is_num(nData[7], 800);
  is_str(szData[7], "string 8", 8);
  is_num(atol(bData[7]), 8);

  is_num(nData[8], 900);
  is_str(szData[8], "string 9", 8);
  is_num(atol(bData[8]), 9);
  is_num(nData[9], 1000);
  is_str(szData[9], "string 10", 8);
  is_num(atol(bData[9]), 10);
  is_num(nData[10], 1100);
  is_str(szData[10], "string 11", 8);
  is_num(atol(bData[10]), 11);

  ok_stmt(hstmt, SQLSetPos(hstmt, 2, SQL_POSITION, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLGetData(hstmt, 0, SQL_C_VARBOOKMARK, abookmark, 255, &outlen));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_FETCH_BOOKMARK_PTR,
                         (SQLPOINTER) abookmark, 0));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  is_num(nData[0], 200);
  is_str(szData[0], "string 2", 8);
  is_num(atol(bData[0]), 2);
  is_num(nData[1], 300);
  is_str(szData[1], "string 3", 8);
  is_num(atol(bData[1]), 3);
  is_num(nData[2], 400);
  is_str(szData[2], "string 4", 8);
  is_num(atol(bData[2]), 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_bookmark");

  return OK;
}


/**
  Bind column 0 with SQL_C_BOOKMARK data type and retrieve bookmark
  using FetchOrientation SQL_FETCH_BOOKMARK.
  Set position in rowset using SQLSetPos and retrieve bookmark for that
  position using SQLGetData and save that particular bookmark position
  using SQL_ATTR_FETCH_BOOKMARK_PTR. Now fetch using using
  FetchOrientation SQL_FETCH_BOOKMARK with retrieve data from that position.
*/
#ifdef _UNIX_
DECLARE_TEST(t_bookmark)
{
  SQLLEN len= 0;
  SQLINTEGER abookmark[4];
  SQLINTEGER outlen;
  SQLUSMALLINT rowStatus[4];
  SQLUINTEGER numRowsFetched;
  SQLINTEGER nData[4];
  SQLCHAR szData[4][16];
  SQLINTEGER bData[4];

  ok_sql(hstmt, "drop table if exists t_bookmark");
  ok_sql(hstmt, "CREATE TABLE t_bookmark ("\
                "tt_int INT PRIMARY KEY auto_increment,"\
                "tt_varchar VARCHAR(128) NOT NULL)");
  ok_sql(hstmt, "INSERT INTO t_bookmark VALUES "\
                "(100, 'string 1'),"\
                "(200, 'string 2'),"\
                "(300, 'string 3'),"\
                "(400, 'string 4')");

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
  ok_stmt(hstmt, SQLBindCol(hstmt, 0, SQL_C_BOOKMARK, bData,
                          sizeof(bData[0]), NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, nData, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, szData, sizeof(szData[0]),
                            NULL));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_FIRST, 0));

  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  is_num(nData[0], 100);
  is_str(szData[0], "string 1", 8);
  is_num(bData[0], 1);
  is_num(nData[1], 200);
  is_str(szData[1], "string 2", 8);
  is_num(bData[1], 2);
  is_num(nData[2], 300);
  is_str(szData[2], "string 3", 8);
  is_num(bData[2], 3);
  is_num(nData[3], 400);
  is_str(szData[3], "string 4", 8);
  is_num(bData[3], 4);

  ok_stmt(hstmt, SQLSetPos(hstmt, 3, SQL_POSITION, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLGetData(hstmt, 0, SQL_C_BOOKMARK, abookmark, 255, &outlen));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_FETCH_BOOKMARK_PTR,
                         (SQLPOINTER) abookmark, 0));

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_BOOKMARK, 0));

  is_num(nData[0], 300);
  is_str(szData[0], "string 3", 8);
  is_num(bData[0], 3);
  is_num(nData[1], 400);
  is_str(szData[1], "string 4", 8);
  is_num(bData[1], 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_bookmark");

  return OK;
}
#endif


/*
  Bug #17311065: ASSERT FAILURE IN SQLDESCRIBECOL()
                 IF THE COLUMN NUMBER GIVEN IS LARGE
  Assert failure in case of prepared statments and SQLDESCRIBECOL() called
  with column number given larger then actual parameter marker in query.
*/
DECLARE_TEST(t_bug17311065)
{
  SQLCHAR     message[SQL_MAX_MESSAGE_LENGTH + 1];
  SQLCHAR     sqlstate[SQL_SQLSTATE_SIZE + 1];
  char        colname[MYSQL_NAME_LEN];
  SQLULEN     collen;
  SQLINTEGER  error;
  SQLSMALLINT len;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug17311065");
  ok_sql(hstmt, "CREATE TABLE t_bug17311065("\
                "id INT , val VARCHAR(20))");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLPrepare(hstmt, "select * from t_bug17311065 where id > ?", SQL_NTS));

  expect_stmt(hstmt, SQLDescribeCol(hstmt, 3, colname, sizeof(colname), NULL,
    NULL, &collen, NULL, NULL), SQL_ERROR);

  ok_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate, &error,
                               message, sizeof(message), &len));
  is(strstr((char *)message, "Invalid descriptor index"));
  is_str(sqlstate, "07009", 5);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug17311065");

  return OK;
}


#define RESTORE_SERVER_LOGGING ok_sql(hstmt, "SET GLOBAL log_output=@old_log_output, general_log=@old_general_log")

#define ok_stmt2(S,C) do { \
  SQLRETURN rc2 = (C); \
  if (rc2 != SQL_SUCCESS && rc2 != SQL_SUCCESS_WITH_INFO) \
  { \
    RESTORE_SERVER_LOGGING; \
    ok_stmt(S, rc2); \
  } \
} while (0)

#define is_num2(a, b) \
do { \
  long long a1= (a), a2= (b); \
  if (a1 != a2) { \
    RESTORE_SERVER_LOGGING; \
    printf("# %s (%lld) != %lld in %s on line %d\n", \
           #a, a1, a2, __FILE__, __LINE__); \
    return FAIL; \
  } \
} while (0)


DECLARE_TEST(t_prefetch_bug)
{
    int param_1 = 1, param_2 = 20, i = 0;
    long con_id = 0;

    const char *queries[] = {
      "select 'Q-001', id from b_prefecth WHERE id > ? ORDER BY id",
      "select 'Q-002', id from b_prefecth WHERE id > ? ORDER BY id LIMIT 7",
      "select 'Q-003', id from b_prefecth WHERE id > ? ORDER BY id LIMIT 5, 3",
      "select 'Q-004', id from b_prefecth WHERE id > ? ORDER BY id LIMIT 5, 100",
      "select 'Q-005', id from b_prefecth WHERE id > ? ORDER BY id FOR UPDATE",
      "select 'Q-006', id from b_prefecth WHERE id > ? ORDER BY id LOCK IN SHARE MODE",
      "select 'Q-007', id from b_prefecth WHERE id > ? ORDER BY id LIMIT 2, 20 FOR UPDATE",
      "select 'Q-008', id from b_prefecth WHERE id > ? ORDER BY id LIMIT 2, 20 LOCK IN SHARE MODE",
      "select 'Q-009', id from b_prefecth WHERE id > ? ORDER BY id LIMIT 2, ? FOR UPDATE",
    };

    const char *check_for[] = {
      "",       //"select 'Q-001' ... ORDER BY id",
      "",       //"select 'Q-002' ... ORDER BY id LIMIT 7",
      "",       //"select 'Q-003' ... ORDER BY id LIMIT 5, 3",
      "",       //"select 'Q-004' ... ORDER BY id LIMIT 5, 100",
      // "select 'Q-005' ... ORDER BY id FOR UPDATE"
      " AND argument LIKE '%%FOR UPDATE%%'",
      // "select 'Q-006' ... ORDER BY id LOCK IN SHARE MODE",
      " AND argument LIKE '%%LOCK IN SHARE MODE%%'",
      // "select 'Q-007' ... ORDER BY id FOR UPDATE"
      " AND argument LIKE '%%FOR UPDATE%%'",
      // "select 'Q-008' ... ORDER BY id LOCK IN SHARE MODE",
      " AND argument LIKE '%%LOCK IN SHARE MODE%%'",
      // "select 'Q-009' ... ORDER BY id FOR UPDATE"
      " AND argument LIKE '%%FOR UPDATE%%'"
    };

    int expected_num[]     = {15, 7, 3, 10, 15, 15, 13, 13, 13};
    int expected_queries[] = { 4, 2, 1,  3,  4,  4,  4,  4, 4 };
    char check_query[512];
    int query_rows = 0;

    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

    is(OK == alloc_basic_handles_with_opt(
             &henv1, &hdbc1, &hstmt1, NULL,
             NULL, NULL, NULL, "PREFETCH=5;NO_SSPS=1"));

    ok_sql(hstmt, "DROP table IF EXISTS b_prefecth");
    ok_sql(hstmt, "CREATE table b_prefecth(id int primary key)");

    ok_sql(hstmt, "insert into b_prefecth values(1),(2),(3),(4)," \
          "(5),(6),(7),(8),(9),(10),(11),(12),(13),(14),(15),(16)");

    // Save the old values for
    ok_sql(hstmt1, "SELECT CONNECTION_ID()");
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_LONG, &con_id, 0, NULL));
    printf("\nConnection ID: %d",con_id);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    ok_sql(hstmt, "SET @old_log_output=@@log_output, @old_general_log=@@general_log");
    ok_sql(hstmt, "SET GLOBAL log_output='TABLE', general_log=1");

    for (i = 0; i < 9; ++i)
    {
      ok_stmt2(hstmt1, SQLPrepare(hstmt1, (SQLCHAR*)queries[i], SQL_NTS));
      ok_stmt2(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT,
        SQL_C_LONG, SQL_INTEGER, 0, 0, &param_1, 0, NULL));

      if (i == 8) // Bind 2nd param for LIMIT
        ok_stmt2(hstmt1, SQLBindParameter(hstmt1, 2, SQL_PARAM_INPUT,
        SQL_C_LONG, SQL_INTEGER, 0, 0, &param_2, 0, NULL));

      ok_stmt2(hstmt1, SQLExecute(hstmt1));
      is_num2(expected_num[i], my_print_non_format_result(hstmt1));
      sprintf(check_query,
                "SELECT CAST(argument as CHAR(128)) arg FROM " \
                "mysql.general_log WHERE thread_id = %d AND " \
                "argument LIKE '%% LIMIT %%' AND " \
                "argument LIKE '%%Q-%03d%%' %s",
                con_id, (i + 1), check_for[i]);

      ok_stmt2(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)check_query, SQL_NTS));
      printf("\nLOGGED QUERIES:\n");
      query_rows = my_print_non_format_result(hstmt);

      // Check if the logging actually happened
      if (query_rows == 0)
      {
        int logged_rows = 0;
        sprintf(check_query, "SELECT CAST(argument as CHAR(128)) arg FROM " \
                "mysql.general_log WHERE thread_id = %d", con_id);
        ok_stmt2(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)check_query, SQL_NTS));
        logged_rows = my_print_non_format_result(hstmt);
        RESTORE_SERVER_LOGGING;
        if (logged_rows > 0)
        {
          printf("\nUnexpected logged rows!");
          return FAIL;
        }
        else
        {
          printf("\nWarning: General query log table is empty");
          return OK;
        }
      }

      if (expected_queries[i] < query_rows ||
          (expected_queries[i] - 1) > query_rows )
      {
        RESTORE_SERVER_LOGGING;
        return FAIL;
      }
    }

    RESTORE_SERVER_LOGGING;
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
    ok_sql(hstmt, "DROP table IF EXISTS b_prefecth");

    return OK;
}

/*
  Bug #28098219: MYSQL ODBC CAUSES WRITE ACCESS VIOLATION WHEN USING RECORDSET.MOVE
*/
DECLARE_TEST(t_bug28098219)
{
  SQLULEN retrieve = 0;
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug28098219");
  ok_sql(hstmt, "CREATE TABLE t_bug28098219("\
                "val VARCHAR(20))");
  ok_sql(hstmt, "INSERT INTO t_bug28098219 VALUES ('012345')");

  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_RETRIEVE_DATA,
                                &retrieve, sizeof(retrieve), NULL));
  is_num(SQL_RD_ON, retrieve);

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_RETRIEVE_DATA, (SQLPOINTER)SQL_RD_OFF, 0));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_RETRIEVE_DATA,
                                &retrieve, sizeof(retrieve), NULL));
  is_num(SQL_RD_OFF, retrieve);

  ok_sql(hstmt, "SELECT * FROM t_bug28098219");
  while (SQL_SUCCESS == SQLFetch(hstmt))
  {
    char buf[128] = "ABCDEF";
    SQLLEN len = 0;
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, buf, sizeof(buf), &len));
    is_str(buf, "ABCDEF", 6);

  }
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_RETRIEVE_DATA, (SQLPOINTER)SQL_RD_ON, 0));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_RETRIEVE_DATA,
                                &retrieve, sizeof(retrieve), NULL));
  is_num(SQL_RD_ON, retrieve);

  ok_sql(hstmt, "SELECT * FROM t_bug28098219");
  while (SQL_SUCCESS == SQLFetch(hstmt))
  {
    char buf[128] = "ABCDEF";
    SQLLEN len = 0;
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, buf, sizeof(buf), &len));
    is_str(buf, "012345", 6);

  }
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug28098219");

  return OK;
}

BEGIN_TESTS
  // ADD_TEST(t_bug11766437) TODO: fix Solaris Sparc
  ADD_TEST(t_bug32420)
  ADD_TEST(t_bug34575)
  ADD_TEST(t_bug24131)
  ADD_TEST(t_bug36069)
  ADD_TEST(t_bug41942)
  ADD_TEST(t_bug39644)
  // ADD_TEST(t_bug32821) TODO: Fix when SQL_INTEGER is a parameter for BIT column
  ADD_TEST(t_bug34271)
  ADD_TEST(t_bug32684)
  ADD_TEST(t_bug55024)
  ADD_TEST(t_bug56677)
  ADD_TEST(t_desccol_before_exec)
  ADD_TEST(t_bug62657)
  ADD_TEST(t_row_status)
  ADD_TEST(t_prefetch)
  ADD_TEST(t_bug17386788)
  ADD_TOFIX(t_outparams)
  // ADD_TEST(t_varbookmark) TODO: Fix
#ifdef _UNIX_
  // ADD_TEST(t_bookmark) TODO: Fix
#endif
  ADD_TEST(t_bug17311065)
  ADD_TEST(t_prefetch_bug)
  ADD_TEST(t_bug28098219)
END_TESTS


RUN_TESTS
