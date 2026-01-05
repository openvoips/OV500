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
#include "../VersionInfo.h"

DECLARE_TEST(my_columns_null)
{
  SQLLEN rowCount= 0;
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };
  /* initialize data */
  ok_sql(hstmt, "drop table if exists my_column_null");

  ok_sql(hstmt, "create table my_column_null(id int not null, name varchar(30))");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  for (int i = 0; i < 2; ++i)
  {
    int idx = 0;
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, SQL_NTS, NULL, SQL_NTS,
    (SQLCHAR *)"my_column_null", SQL_NTS,
    NULL, SQL_NTS));

    ok_stmt(hstmt1, SQLRowCount(hstmt1, &rowCount));

    is_num(rowCount, 2);

    is_num(2, my_print_non_format_result(hstmt1));

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  ok_sql(hstmt, "DROP TABLE IF EXISTS my_column_null");

  return OK;
}


DECLARE_TEST(my_drop_table)
{
  ok_sql(hstmt, "drop table if exists my_drop_table");
  ok_sql(hstmt, "create table my_drop_table(id int not null)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLColumns(hstmt, NULL, 0, NULL, 0,
                            (SQLCHAR *)"my_drop_table", SQL_NTS, NULL, 0));

  is_num(1, my_print_non_format_result(hstmt));

  ok_sql(hstmt, "drop table my_drop_table");

  return OK;
}


#define TODBC_BIND_CHAR(n,buf) SQLBindCol(hstmt,n,SQL_C_CHAR,&buf,sizeof(buf),NULL);


DECLARE_TEST(my_table_dbs)
{
    SQLCHAR    database[100];
    SQLRETURN  rc;
    SQLINTEGER nrows= 0 ;
    SQLLEN lenOrNull, rowCount= 0;


    ok_sql(hstmt, "DROP DATABASE IF EXISTS my_all_db_test1");
    ok_sql(hstmt, "DROP DATABASE IF EXISTS my_all_db_test2");
    ok_sql(hstmt, "DROP DATABASE IF EXISTS my_all_db_test3");
    ok_sql(hstmt, "DROP DATABASE IF EXISTS my_all_db_test4");

    /* This call caused problems when database names returned as '%' */
    ok_stmt(hstmt, SQLTables(hstmt,(SQLCHAR*)SQL_ALL_CATALOGS,1,NULL,0,NULL,0,NULL,0));

    while (SQLFetch(hstmt) == SQL_SUCCESS)
    {
      ++nrows;
      ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, database,
                               sizeof(database), NULL));
      /* the table catalog in the results must not be '%' */
      is(database[0] != '%');
    }
    /* we should have got rows... */
    is(nrows>0);

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

    ok_stmt(hstmt, SQLTables(hstmt,(SQLCHAR *)SQL_ALL_CATALOGS,1,"",0,"",0,NULL,0));

    /* Added calls to SQLRowCount just to have tests of it with SQLTAbles. */
    ok_stmt(hstmt, SQLRowCount(hstmt, &rowCount));
    nrows = my_print_non_format_result(hstmt);

    is_num(rowCount, nrows);
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLTables(hstmt,(SQLCHAR *)SQL_ALL_CATALOGS,SQL_NTS,"",0,"",0,
                   NULL,0);
    mystmt(hstmt,rc);

    is_num(nrows, my_print_non_format_result(hstmt));
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLTables(hstmt,(SQLCHAR *)"test",4,NULL,0,NULL,0,NULL,0);
    mystmt(hstmt,rc);

    ok_stmt(hstmt, SQLRowCount(hstmt, &rowCount));
    is_num(rowCount, my_print_non_format_result(hstmt));

    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* test fails on Win2003 x86 w/DM if len=5, SQL_NTS is used instead */
    rc = SQLTables(hstmt,(SQLCHAR *)"mysql",SQL_NTS,NULL,0,NULL,0,NULL,0);
    mystmt(hstmt,rc);

    is(my_print_non_format_result(hstmt) != 0);
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* Must return non-NULL catalogs */
    rc = SQLTables(hstmt,(SQLCHAR *)SQL_ALL_CATALOGS,1,"",0,"",0,NULL,0);
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    memset(database,0,100);
    rc = SQLGetData(hstmt,1,SQL_C_CHAR,database,100,NULL);
    mystmt(hstmt,rc);
    printMessage("catalog: %s", database);

    memset(database,0,100);
    rc = SQLGetData(hstmt,2,SQL_C_CHAR,database,100,&lenOrNull);
    mystmt(hstmt,rc);
    printMessage("schema: %s", database);
    myassert(lenOrNull == SQL_NULL_DATA);

    memset(database,0,100);
    rc = SQLGetData(hstmt,3,SQL_C_CHAR,database,100,&lenOrNull);
    mystmt(hstmt,rc);
    printMessage("table: %s", database);
    myassert(lenOrNull == SQL_NULL_DATA);

    memset(database,0,100);
    rc = SQLGetData(hstmt,4,SQL_C_CHAR,database,100,&lenOrNull);
    mystmt(hstmt,rc);
    printMessage("type: %s", database);
    myassert(lenOrNull == SQL_NULL_DATA);

    memset(database,0,100);
    rc = SQLGetData(hstmt,5,SQL_C_CHAR, database,100,&lenOrNull);
    mystmt(hstmt,rc);
    printMessage("database remark: %s", database);
    myassert(lenOrNull == SQL_NULL_DATA);

    SQLFreeStmt(hstmt,SQL_UNBIND);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    ok_sql(hstmt, "CREATE DATABASE my_all_db_test1");
    ok_sql(hstmt, "CREATE DATABASE my_all_db_test2");
    ok_sql(hstmt, "CREATE DATABASE my_all_db_test3");
    ok_sql(hstmt, "CREATE DATABASE my_all_db_test4");

    rc = SQLTables(hstmt, (SQLCHAR *)"%", 1, "", 0, "", 0, "", 0);
    mystmt(hstmt,rc);

    nrows += 4;
    is_num(nrows, my_print_non_format_result(hstmt));
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLTables(hstmt,(SQLCHAR *)SQL_ALL_CATALOGS, SQL_NTS,
                   "", 0, "", 0, "", 0);
    mystmt(hstmt,rc);

    is_num(my_print_non_format_result(hstmt), nrows);
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLTables(hstmt, (SQLCHAR *)"my_all_db_test", SQL_NTS,
                   "", 0, "", 0, "", 0);
    mystmt(hstmt,rc);

    ok_stmt(hstmt, SQLRowCount(hstmt, &rowCount));
    is_num(rowCount, 0);

    is_num(my_print_non_format_result(hstmt), 0);
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = SQLTables(hstmt, (SQLCHAR *)"my_all_db_test%", SQL_NTS,
                   "", 0, "", 0, NULL, 0);
    mystmt(hstmt,rc);

    is_num(my_print_non_format_result(hstmt), 4);
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* unknown table should be empty */
    rc = SQLTables(hstmt, (SQLCHAR *)"my_all_db_test%", SQL_NTS,
                   NULL, 0, (SQLCHAR *)"xyz", SQL_NTS, NULL, 0);
    mystmt(hstmt,rc);

    is_num(my_print_non_format_result(hstmt), 0);
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP DATABASE my_all_db_test1");
  ok_sql(hstmt, "DROP DATABASE my_all_db_test2");
  ok_sql(hstmt, "DROP DATABASE my_all_db_test3");
  ok_sql(hstmt, "DROP DATABASE my_all_db_test4");

  return OK;
}


DECLARE_TEST(my_colpriv)
{
  ok_sql(hstmt, "DROP TABLE IF EXISTS test_colprev1");
  ok_sql(hstmt, "DROP TABLE IF EXISTS test_colprev2");
  ok_sql(hstmt, "DROP TABLE IF EXISTS test_colprev3");

  ok_sql(hstmt, "CREATE TABLE test_colprev1(a INT,b INT,c INT, d INT)");
  ok_sql(hstmt, "CREATE TABLE test_colprev2(a INT,b INT,c INT, d INT)");
  ok_sql(hstmt, "CREATE TABLE test_colprev3(a INT,b INT,c INT, d INT)");

  (void)SQLExecDirect(hstmt, (SQLCHAR *)"DROP USER my_colpriv", SQL_NTS);
  ok_sql(hstmt, "CREATE USER my_colpriv");

  ok_sql(hstmt, "GRANT SELECT(a,b),INSERT(d),UPDATE(c) ON test_colprev1 TO my_colpriv");
  ok_sql(hstmt, "GRANT SELECT(c,a),UPDATE(a,b) ON test_colprev3 TO my_colpriv");

  ok_sql(hstmt, "FLUSH PRIVILEGES");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLColumnPrivileges(hstmt,
                                     NULL, SQL_NTS, NULL, SQL_NTS,
                                     (SQLCHAR *)"test_colprev1", SQL_NTS,
                                     (SQLCHAR *)/*NULL*/"%", SQL_NTS));

  printMessage("1) Privileges on all columns from test_colprev1");
  is_num(4, my_print_non_format_result(hstmt));

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLColumnPrivileges(hstmt,
                                     NULL, SQL_NTS, NULL, SQL_NTS,
                                     (SQLCHAR *)"test_colprev1", SQL_NTS,
                                     (SQLCHAR *)"a", SQL_NTS));

  printMessage("2) Privileges on column 'a' from test_colprev1");
  is_num(my_print_non_format_result(hstmt), 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLColumnPrivileges(hstmt,
                                     NULL, SQL_NTS, NULL, SQL_NTS,
                                     (SQLCHAR *)"test_colprev2", SQL_NTS,
                                     (SQLCHAR *)"%", SQL_NTS));

  printMessage("3) Privileges on all columns from test_colprev2");
  is_num(my_print_non_format_result(hstmt), 0);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLColumnPrivileges(hstmt,
                                     NULL, SQL_NTS, NULL, SQL_NTS,
                                     (SQLCHAR *)"test_colprev3", SQL_NTS,
                                     (SQLCHAR *)"%", SQL_NTS));

  printMessage("4) Privileges on all columns from test_colprev3");
  is_num(my_print_non_format_result(hstmt), 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLColumnPrivileges(hstmt,
                                     NULL, SQL_NTS, NULL, SQL_NTS,
                                     (SQLCHAR *)"test_%", SQL_NTS,
                                     (SQLCHAR *)"%", SQL_NTS));

  is_num(my_print_non_format_result(hstmt), 0);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLColumnPrivileges(hstmt,
                                     (SQLCHAR *)"mysql", SQL_NTS, NULL, SQL_NTS,
                                     (SQLCHAR *)"columns_priv", SQL_NTS,
                                     NULL, SQL_NTS));

  my_print_non_format_result(hstmt);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP USER my_colpriv");

  ok_sql(hstmt, "DROP TABLE test_colprev1, test_colprev2, test_colprev3");

  return OK;
}


DECLARE_TEST(t_sqlprocedures)
{
  if (!mysql_min_version(hdbc, "5.0", 3))
    skip("server does not support stored procedures");

  /* avoid errors in case binary log is activated */
  ok_sql(hstmt, "SET GLOBAL log_bin_trust_function_creators = 1");

  ok_sql(hstmt, "DROP FUNCTION IF EXISTS t_sqlproc_func");
  ok_sql(hstmt,
         "CREATE FUNCTION t_sqlproc_func (a INT) RETURNS INT RETURN SQRT(a)");

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_sqlproc_proc");
  ok_sql(hstmt,
         "CREATE PROCEDURE t_sqlproc_proc (OUT a INT) BEGIN"
         " SELECT COUNT(*) INTO a FROM t_sqlproc;"
         "END;");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Try without specifying a catalog. */
  ok_stmt(hstmt, SQLProcedures(hstmt, NULL, 0, NULL, 0,
                               (SQLCHAR *)"t_sqlproc%", SQL_NTS));

  is_num(my_print_non_format_result(hstmt), 2);

  /* And try with specifying a catalog.  */
  ok_stmt(hstmt, SQLProcedures(hstmt, (SQLCHAR *)"test", SQL_NTS, NULL, 0,
                               (SQLCHAR *)"t_sqlproc%", SQL_NTS));

  is_num(my_print_non_format_result(hstmt), 2);

  ok_sql(hstmt, "DROP PROCEDURE t_sqlproc_proc");
  ok_sql(hstmt, "DROP FUNCTION t_sqlproc_func");

  return OK;
}


DECLARE_TEST(t_catalog)
{
  SQLRETURN rc;
  SQLCHAR      name[MYSQL_NAME_LEN+1];
  SQLSMALLINT  ncols, len;

  SQLCHAR colnames[19][20]= {
      "TABLE_CAT","TABLE_SCHEM","TABLE_NAME","COLUMN_NAME",
      "DATA_TYPE","TYPE_NAME","COLUMN_SIZE","BUFFER_LENGTH",
      "DECIMAL_DIGITS","NUM_PREC_RADIX","NULLABLE","REMARKS",
      "COLUMN_DEF","SQL_DATA_TYPE","SQL_DATETIME_SUB",
      "CHAR_OCTET_LENGTH","ORDINAL_POSITION","IS_NULLABLE"
  };
  SQLSMALLINT collengths[18]= {
      9,11,10,11,9,9,11,13,14,14,8,7,10,13,16,17,16,11
  };
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "drop table if exists t_catalog");

  ok_sql(hstmt,"create table t_catalog(abc tinyint, bcdefghijklmno char(4), uifield int unsigned not null)");

  for (int j = 0; j < 2; ++j)
  {
    int idx = 0;
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[j]);

    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
                              (SQLCHAR *)"t_catalog", 9, NULL, 0));

    rc = SQLNumResultCols(hstmt1, &ncols);
    mystmt(hstmt1,rc);

    printMessage("total columns: %d", ncols);
    myassert(ncols == 18);
    myassert(myresult(hstmt1) == 3);

    SQLFreeStmt(hstmt1, SQL_UNBIND);
    SQLFreeStmt(hstmt1, SQL_CLOSE);

    rc = SQLColumns(hstmt1, NULL, 0, NULL, 0,
                    (SQLCHAR *)"t_catalog", 9, NULL, 0);
    mystmt(hstmt1,rc);

    rc = SQLNumResultCols(hstmt1,&ncols);
    mystmt(hstmt1,rc);

    for (int i = 1; i <= (int)ncols; i++)
    {
        rc = SQLDescribeCol(hstmt1, i, name, MYSQL_NAME_LEN+1, &len, NULL, NULL, NULL, NULL);
        mystmt(hstmt1,rc);

        printMessage("column %d: %s (%d)", i, name, len);
        is_num(len, collengths[i - 1]);
        is_str(name, colnames[i - 1], len);
    }
    SQLFreeStmt(hstmt1,SQL_CLOSE);
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_catalog");
  return OK;
}


DECLARE_TEST(tmysql_specialcols)
{
  SQLRETURN rc;

    tmysql_exec(hstmt,"drop table tmysql_specialcols");
    rc = tmysql_exec(hstmt,"create table tmysql_specialcols(col1 decimal(10, 4) primary key, col2 varchar(30), col3 int)");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"create index tmysql_ind1 on tmysql_specialcols(col1)");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_specialcols values(100,'venu',1)");
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"insert into tmysql_specialcols values(200,'MySQL',2)");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"select * from tmysql_specialcols");
    mystmt(hstmt,rc);

    myresult(hstmt);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    ok_stmt(hstmt, SQLSpecialColumns(hstmt, SQL_BEST_ROWID, NULL,0, NULL,0,
                                     (SQLCHAR *)"tmysql_specialcols",SQL_NTS,
                                     SQL_SCOPE_SESSION, SQL_NULLABLE));

    ok_stmt(hstmt, SQLFetch(hstmt));

    {
      SQLCHAR buf[128] = {'\0'};
      // SCOPE
      is_num(SQL_SCOPE_SESSION, my_fetch_int(hstmt, 1));
      // COLUMN_NAME
      is_str("col1", my_fetch_str(hstmt, buf, 2), 4);
      // DATA_TYPE
      is_num(SQL_DECIMAL, my_fetch_int(hstmt, 3));
      // TYPE_NAME
      is_str("decimal(10,4)", my_fetch_str(hstmt, buf, 4), 13);
      // COLUMN_SIZE (for DECIMAL(10,4) it is 10)
      is_num(10, my_fetch_int(hstmt, 5));
      // BUFFER_LENGTH (10 + 4 + 1(sign) + 1(dot))
      is_num(16, my_fetch_int(hstmt, 6));
      // DECIMAL DIGITS (for DECIMAL(10,4) it is 4)
      is_num(4, my_fetch_int(hstmt, 7));
      // PSEUDO_COLUMN
      is_num(SQL_PC_NOT_PSEUDO, my_fetch_int(hstmt, 8));
    }

    expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"drop table tmysql_specialcols");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

  return OK;
}


/* To test SQLColumns misc case */
DECLARE_TEST(t_columns)
{
  SQLSMALLINT   NumPrecRadix, DataType, Nullable, DecimalDigits;
  SQLLEN        cbColumnSize, cbDecimalDigits, cbNumPrecRadix,
                cbDataType, cbNullable;
  SQLINTEGER    cbDatabaseName;
  SQLUINTEGER   ColumnSize, i;
  SQLUINTEGER   ColumnCount= 7;
  SQLCHAR       ColumnName[MAX_NAME_LEN], DatabaseName[MAX_NAME_LEN];
  SQLINTEGER    Values[7][5][2]=
  {
    { {5,2},  {5,4}, {0,2},  {10,2},  {1,2}},
    { {1,2},  {5,4},  {0,-1}, {10,-1}, {1,2}},
    { {12,2}, {20,4}, {0,-1}, {10,-1}, {0,2}},
    { {3,2},  {10,4}, {2,2},  {10,2},  {1,2}},
    { {-6,2},  {3,4}, {0,2},  {10,2},  {0,2}},
    { {4,2}, {10,4}, {0,2},  {10,2},  {0,2}},
    { {-6,2}, {3,4}, {0,2},  {10,2},  {1,2}}
  };
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_columns");

  ok_sql(hstmt,
         "CREATE TABLE t_columns (col0 SMALLINT,"
         "col1 CHAR(5), col2 VARCHAR(20) NOT NULL, col3 DECIMAL(10,2),"
         "col4 TINYINT NOT NULL, col5 INTEGER PRIMARY KEY,"
         "col6 TINYINT NOT NULL UNIQUE AUTO_INCREMENT) CHARSET latin1");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  for (int j = 0; j < 2; ++j)
  {
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[j]);

    ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_METADATA_ID,
                                  (SQLPOINTER)SQL_FALSE, SQL_IS_UINTEGER));

    ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_CURSOR_TYPE,
                                  (SQLPOINTER)SQL_CURSOR_STATIC, 0));

    ok_con(hdbc, SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG,
                                   DatabaseName, MAX_NAME_LEN,
                                   &cbDatabaseName)); /* Current Catalog */

    for (i= 0; i < ColumnCount; i++)
    {
      sprintf((char *)ColumnName, "col%d", (int)i);
      printMessage("checking column `%s`", (char *)ColumnName);

      ok_stmt(hstmt1, SQLColumns(hstmt1,
                                DatabaseName, (SQLSMALLINT)cbDatabaseName,
                                (SQLCHAR *)"", SQL_NTS,
                                (SQLCHAR *)"t_columns", SQL_NTS,
                                ColumnName, SQL_NTS));

      /* 5 -- Data type */
      ok_stmt(hstmt1, SQLBindCol(hstmt1, 5, SQL_C_SSHORT, &DataType, 0,
                                &cbDataType));

      /* 7 -- Column Size */
      ok_stmt(hstmt1, SQLBindCol(hstmt1, 7, SQL_C_ULONG, &ColumnSize, 0,
                                &cbColumnSize));

      /* 9 -- Decimal Digits */
      ok_stmt(hstmt1, SQLBindCol(hstmt1, 9, SQL_C_SSHORT, &DecimalDigits, 0,
                                &cbDecimalDigits));

      /* 10 -- Num Prec Radix */
      ok_stmt(hstmt1, SQLBindCol(hstmt1, 10, SQL_C_SSHORT, &NumPrecRadix, 0,
                                &cbNumPrecRadix));

      /* 11 -- Nullable */
      ok_stmt(hstmt1, SQLBindCol(hstmt1, 11, SQL_C_SSHORT, &Nullable, 0,
                                &cbNullable));

      ok_stmt(hstmt1, SQLFetch(hstmt1));

	  /* if you get -8 for col1 here - that's fine. depends on setup. the test probably needs
	     to be changed accordingly */
      is_num(DataType,   Values[i][0][0]);
      is_num(cbDataType, Values[i][0][1]);

      is_num(ColumnSize,   Values[i][1][0]);
      is_num(cbColumnSize, Values[i][1][1]);

      is_num(DecimalDigits,   Values[i][2][0]);
      is_num(cbDecimalDigits, Values[i][2][1]);

      is_num(NumPrecRadix,   Values[i][3][0]);
      is_num(cbNumPrecRadix, Values[i][3][1]);

      is_num(Nullable,   Values[i][4][0]);
      is_num(cbNullable, Values[i][4][1]);

      expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA);

      ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_UNBIND));
      ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    }
  }
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_columns");

  return OK;
}


/* Test the bug SQLTables */
typedef struct t_table_bug
{
  SQLCHAR     szColName[MAX_NAME_LEN];
  SQLSMALLINT pcbColName;
  SQLSMALLINT pfSqlType;
  SQLUINTEGER pcbColDef;
  SQLSMALLINT pibScale;
  SQLSMALLINT pfNullable;
} t_describe_col;


t_describe_col t_tables_bug_data_unicode[5] =
{
  {"TABLE_CAT",   9, SQL_WVARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"TABLE_SCHEM",11, SQL_WVARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"TABLE_NAME", 10, SQL_WVARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"TABLE_TYPE", 10, SQL_WVARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"REMARKS",     7, SQL_WVARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
};

t_describe_col t_tables_bug_data_ansi[5] =
{
  {"TABLE_CAT",   9, SQL_VARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"TABLE_SCHEM",11, SQL_VARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"TABLE_NAME", 10, SQL_VARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"TABLE_TYPE", 10, SQL_VARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},
  {"REMARKS",     7, SQL_VARCHAR, MYSQL_NAME_LEN, 0, SQL_NULLABLE},

};


DECLARE_TEST(t_tables_bug)
{
  SQLSMALLINT i, ColumnCount, pcbColName, pfSqlType, pibScale, pfNullable;
  SQLULEN     pcbColDef;
  SQLCHAR     szColName[MAX_NAME_LEN];
  t_describe_col *t_tables_bug_data= unicode_driver ? t_tables_bug_data_unicode
                                                    : t_tables_bug_data_ansi;

  ok_stmt(hstmt,  SQLTables(hstmt, NULL, 0, NULL, 0, NULL, 0,
                            (SQLCHAR *)"'TABLE'", SQL_NTS));

  ok_stmt(hstmt, SQLNumResultCols(hstmt, &ColumnCount));
  is_num(ColumnCount, 5);

  for (i= 1; i <= ColumnCount; ++i)
  {
    ok_stmt(hstmt, SQLDescribeCol(hstmt, (SQLUSMALLINT)i, szColName,
                                 MAX_NAME_LEN, &pcbColName, &pfSqlType,
                                 &pcbColDef, &pibScale, &pfNullable));

    printMessage("Column '%d':", i);
    printMessage(" Column Name   : %s", szColName);
    printMessage(" NameLengh     : %d", pcbColName);
    printMessage(" DataType      : %d", pfSqlType);
    printMessage(" ColumnSize    : %d", pcbColDef);
    printMessage(" DecimalDigits : %d", pibScale);
    printMessage(" Nullable      : %d", pfNullable);

    is_str(t_tables_bug_data[i-1].szColName, szColName, pcbColName);
    is_num(t_tables_bug_data[i-1].pcbColName, pcbColName);
    is_num(t_tables_bug_data[i-1].pfSqlType, pfSqlType);
    /* This depends on NAME_LEN in mysql_com.h */
    #if UNRELIABLE_TEST
    is(t_tables_bug_data[i-1].pcbColDef == pcbColDef ||
      t_tables_bug_data[i-1].pcbColDef == pcbColDef / 3);
    #endif
    is_num(t_tables_bug_data[i-1].pibScale, pibScale);
    is_num(t_tables_bug_data[i-1].pfNullable, pfNullable);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


DECLARE_TEST(t_current_catalog_unicode)
{
  SQLWCHAR    db[255];
  wchar_t     cur_db[255];
  SQLRETURN   rc;
  SQLINTEGER  len;

  REQUIRES_UNICODE_DRIVER;

  rc = SQLFreeStmt(hstmt,SQL_CLOSE);
  mystmt(hstmt,rc);

  rc = SQLGetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, db, sizeof(db), &len);
  mycon(hdbc,rc);

  is_num(len, strlen("test") * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(db), L"test", 5);

  rc = SQLSetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, db, SQL_NTS);
  mycon(hdbc,rc);

  ok_sql(hstmt, "DROP DATABASE IF EXISTS test_odbc_current");

  wcscpy(cur_db, L"test_odbc_current");
  rc = SQLSetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, W(cur_db), SQL_NTS);
  mycon_r(hdbc,rc);

  ok_sql(hstmt, "CREATE DATABASE test_odbc_current");
  rc = SQLFreeStmt(hstmt,SQL_CLOSE);

  wcscpy(cur_db, L"test_odbc_current");
  rc = SQLSetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, W(cur_db), SQL_NTS);
  mycon(hdbc,rc);

  rc = SQLGetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, db, 255, &len);
  mycon(hdbc,rc);

  is_num(len, strlen("test_odbc_current") * sizeof(SQLWCHAR));
  is_wstr(sqlwchar_to_wchar_t(db), cur_db, 18);

  wcscpy(cur_db, L"test_odbc_current_12455");
  rc = SQLSetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, W(cur_db), SQL_NTS);
  mycon_r(hdbc,rc);

#ifdef _WIN32
  rc = SQLSetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, W(cur_db), len);
#else
  rc = SQLSetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, W(cur_db), len / sizeof(SQLWCHAR));
#endif
  mycon(hdbc,rc);

  /* reset for further tests */
  wcscpy(cur_db, L"test");
  rc = SQLSetConnectAttrW(hdbc, SQL_ATTR_CURRENT_CATALOG, W(cur_db), SQL_NTS);
  mycon(hdbc,rc);

  ok_sql(hstmt, "DROP DATABASE test_odbc_current");

  return OK;
}


DECLARE_TEST(t_current_catalog_ansi)
{
  SQLCHAR     cur_db[255], db[255];
  SQLRETURN   rc;
  SQLINTEGER len;

  REQUIRES_ANSI_DRIVER;

  rc = SQLFreeStmt(hstmt,SQL_CLOSE);
  mystmt(hstmt,rc);

  rc = SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, db, sizeof(db), &len);
  mycon(hdbc,rc);

  is_num(len, 4);
  is_str(db, "test", 5);

  rc = SQLSetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, db, SQL_NTS);
  mycon(hdbc,rc);

  ok_sql(hstmt, "DROP DATABASE IF EXISTS test_odbc_current");

  strcpy((char *)cur_db, "test_odbc_current");
  rc = SQLSetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, cur_db, SQL_NTS);
  mycon_r(hdbc,rc);

  ok_sql(hstmt, "CREATE DATABASE test_odbc_current");
  rc = SQLFreeStmt(hstmt,SQL_CLOSE);

  strcpy((char *)cur_db, "test_odbc_current");
  rc = SQLSetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, cur_db, SQL_NTS);
  mycon(hdbc,rc);

  rc = SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, db, 255, &len);
  mycon(hdbc,rc);

  is_num(len, 17);
  is_str(db, cur_db, 18);

  strcpy((char *)cur_db, "test_odbc_current_12455");
  rc = SQLSetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, cur_db, SQL_NTS);
  mycon_r(hdbc,rc);

  rc = SQLSetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, cur_db, len);
  mycon(hdbc,rc);

  /* reset for further tests */
  rc = SQLSetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, (SQLCHAR *)"test", SQL_NTS);
  mycon(hdbc,rc);

  ok_sql(hstmt, "DROP DATABASE test_odbc_current");

  return OK;
}


DECLARE_TEST(tmysql_showkeys)
{
    SQLRETURN rc;

    tmysql_exec(hstmt,"drop table tmysql_spk");

    rc = tmysql_exec(hstmt,"create table tmysql_spk(col1 int primary key)");
    mystmt(hstmt,rc);

    rc = SQLTransact(NULL,hdbc,SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

    rc = tmysql_exec(hstmt,"SHOW KEYS FROM tmysql_spk");
    mystmt(hstmt,rc);

    my_assert(1 == myresult(hstmt));

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_spk");

  return OK;
}


DECLARE_TEST(t_sqltables)
{
  SQLRETURN   r;
  SQLINTEGER  rows;
  SQLLEN      rowCount;

  ok_stmt(hstmt, SQLTables(hstmt,NULL,0,NULL,0,NULL,0,NULL,0));

  myresult(hstmt);

  r = SQLFreeStmt(hstmt, SQL_CLOSE);
  mystmt(hstmt,r);

  r  = SQLTables(hstmt, NULL, 0, NULL, 0, NULL, 0,
                 (SQLCHAR *)"'system table'", SQL_NTS);
  mystmt(hstmt,r);

  is_num(myresult(hstmt), 0);

  r = SQLFreeStmt(hstmt, SQL_CLOSE);
  mystmt(hstmt,r);

  r  = SQLTables(hstmt, NULL, 0, NULL, 0, NULL, 0,
                 (SQLCHAR *)"TABLE", SQL_NTS);
  ok_stmt(hstmt, SQLRowCount(hstmt, &rowCount));

  mystmt(hstmt,r);

  is_num(myresult(hstmt), rowCount);

  r = SQLFreeStmt(hstmt, SQL_CLOSE);
  mystmt(hstmt,r);

  r  = SQLTables(hstmt, (SQLCHAR *)"TEST", SQL_NTS,
                        (SQLCHAR *)"", SQL_NTS, NULL, 0,
                        (SQLCHAR *)"TABLE", SQL_NTS);
  mystmt(hstmt,r);

  myresult(hstmt);

  r = SQLFreeStmt(hstmt, SQL_CLOSE);
  mystmt(hstmt,r);

  r = SQLTables(hstmt, (SQLCHAR *)"%", SQL_NTS, NULL, 0, NULL, 0, NULL, 0);
  mystmt(hstmt,r);

  myresult(hstmt);

  r = SQLFreeStmt(hstmt, SQL_CLOSE);
  mystmt(hstmt,r);

  //r = SQLTables(hstmt, NULL, 0, (SQLCHAR *)"%", SQL_NTS, NULL, 0, NULL, 0);
  //mystmt(hstmt,r);

  //myresult(hstmt);

  //r = SQLFreeStmt(hstmt, SQL_CLOSE);
  //mystmt(hstmt,r);

  r = SQLTables(hstmt, "", 0, "", 0, "", 0, (SQLCHAR *)"%", SQL_NTS);
  mystmt(hstmt,r);

  rows= myresult(hstmt);
  is_num(rows, 3);

  r = SQLFreeStmt(hstmt, SQL_CLOSE);
  mystmt(hstmt,r);

  return OK;
}


DECLARE_TEST(my_information_schema)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  ok_sql(hstmt, "DROP DATABASE IF EXISTS istest__");
  ok_sql(hstmt, "DROP DATABASE IF EXISTS istest_1");
  ok_sql(hstmt, "DROP DATABASE IF EXISTS istest_2");

  ok_sql(hstmt, "CREATE DATABASE istest__");
  ok_sql(hstmt, "CREATE DATABASE istest_1");
  ok_sql(hstmt, "CREATE DATABASE istest_2");

  ok_sql(hstmt, "CREATE TABLE istest__.istab_(a INT,b INT,c INT, d INT)");
  ok_sql(hstmt, "CREATE TABLE istest_1.istab1(a INT,b INT,c INT, d INT)");
  ok_sql(hstmt, "CREATE TABLE istest_2.istab2(a INT,b INT,c INT, d INT)");

  /* We need to have istest__ as the default DB */
  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, "istest__", NULL));

  ok_stmt(hstmt1, SQLTables(hstmt1, "istest__", SQL_NTS, "", 0, "istab%",
                            SQL_NTS, NULL, 0));

  /* all tables from all databases should be displayed */
  is_num(my_print_non_format_result(hstmt1), 3);
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLTables(hstmt1, NULL, 0, NULL, 0, "istab%", SQL_NTS, NULL, 0));

  is_num(my_print_non_format_result(hstmt1), 1);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


/**
 Bug #4518: SQLForeignKeys returns too many foreign key
 Bug #27723: SQLForeignKeys does not escape _ and % in the table name arguments

 The original test case was extended to have a table that would inadvertently
 get included because of the poor escaping.
*/
DECLARE_TEST(t_bug4518)
{
  SQLCHAR buff[255];
  SQLLEN len;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug4518_c, t_bug4518_c2, t_bug4518ac, "
                "                     t_bug4518_p");
  ok_sql(hstmt, "CREATE TABLE t_bug4518_p (id INT PRIMARY KEY) ENGINE=InnoDB");
  ok_sql(hstmt, "CREATE TABLE t_bug4518_c (id INT, parent_id INT,"
                "                          FOREIGN KEY (parent_id)"
                "                           REFERENCES"
                "                             t_bug4518_p(id)"
                "                           ON DELETE SET NULL)"
                " ENGINE=InnoDB");
  ok_sql(hstmt, "CREATE TABLE t_bug4518_c2 (id INT, parent_id INT,"
                "                           FOREIGN KEY (parent_id)"
                "                            REFERENCES"
                "                              t_bug4518_p(id)"
                "                          )"
                " ENGINE=InnoDB");
  ok_sql(hstmt, "CREATE TABLE t_bug4518ac (id INT, parent_id INT,"
                "                          FOREIGN KEY (parent_id)"
                "                           REFERENCES"
                "                             t_bug4518_p(id)"
                "                            ON DELETE CASCADE"
                "                            ON UPDATE NO ACTION"
                "                          )"
                " ENGINE=InnoDB");

  ok_stmt(hstmt, SQLForeignKeys(hstmt, NULL, 0, NULL, 0, NULL, 0, NULL, 0,
                                NULL, 0, (SQLCHAR *)"t_bug4518_c", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug4518_p", 11);
  is_str(my_fetch_str(hstmt, buff, 4), "id", 2);
  is_str(my_fetch_str(hstmt, buff, 7), "t_bug4518_c", 11);
  is_str(my_fetch_str(hstmt, buff, 8), "parent_id", 9);
  if (mysql_min_version(hdbc, "5.1", 3))
  {
    is_num(my_fetch_int(hstmt, 10), SQL_NO_ACTION);
    is_num(my_fetch_int(hstmt, 11), SQL_SET_NULL);
  }
  else
  {
    is_num(my_fetch_int(hstmt, 10), SQL_RESTRICT);
    is_num(my_fetch_int(hstmt, 11), SQL_RESTRICT);
  }

  /* For Bug #19923: Test that schema columns are NULL. */
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_CHAR, buff, sizeof(buff), &len));
  is_num(len, SQL_NULL_DATA);
  ok_stmt(hstmt, SQLGetData(hstmt, 6, SQL_C_CHAR, buff, sizeof(buff), &len));
  is_num(len, SQL_NULL_DATA);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLForeignKeys(hstmt, NULL, 0, NULL, 0, NULL, 0, NULL, 0,
                                NULL, 0, (SQLCHAR *)"t_bug4518ac", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug4518_p", 11);
  is_str(my_fetch_str(hstmt, buff, 4), "id", 2);
  is_str(my_fetch_str(hstmt, buff, 7), "t_bug4518ac", 11);
  is_str(my_fetch_str(hstmt, buff, 8), "parent_id", 9);
  if (mysql_min_version(hdbc, "5.1", 3))
  {
    is_num(my_fetch_int(hstmt, 10), SQL_NO_ACTION);
    is_num(my_fetch_int(hstmt, 11), SQL_CASCADE);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt,
         "DROP TABLE t_bug4518_c, t_bug4518_c2, t_bug4518ac, t_bug4518_p");

  return OK;
}


/**
 Tests the non-error code paths in catalog.c that return an empty set to
 make sure the resulting empty result sets at least indicate the right
 number of columns.
*/
DECLARE_TEST(empty_set)
{
  SQLSMALLINT columns;

  /* SQLTables(): no known table types. */
  ok_stmt(hstmt, SQLTables(hstmt, NULL, SQL_NTS, NULL, SQL_NTS, NULL, SQL_NTS,
                           (SQLCHAR *)"UNKNOWN", SQL_NTS));
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &columns));
  is_num(columns, 5);
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* SQLTables(): no tables found. */
  ok_stmt(hstmt, SQLTables(hstmt, NULL, SQL_NTS, NULL, SQL_NTS,
                           (SQLCHAR *)"no_such_table", SQL_NTS, NULL, SQL_NTS));
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &columns));
  is_num(columns, 5);
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* SQLTables(): empty catalog with existing table */
  ok_sql(hstmt, "drop table if exists t_sqltables_empty");
  ok_sql(hstmt, "create table t_sqltables_empty (x int)");
  ok_stmt(hstmt, SQLTables(hstmt, "", SQL_NTS, NULL, 0,
			   (SQLCHAR *) "t_sqltables_empty", SQL_NTS,
			   NULL, SQL_NTS));
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &columns));
  is_num(columns, 5);
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_sqltables_empty");

  return OK;
}


/**
 Bug #23031: SQLTables returns inaccurate catalog information on views
*/
DECLARE_TEST(t_bug23031)
{
  SQLCHAR buff[255];

  ok_sql(hstmt, "DROP VIEW IF EXISTS t_bug23031_v");
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug23031_t");
  ok_sql(hstmt, "CREATE TABLE t_bug23031_t (a INT) COMMENT 'Whee!'");
  ok_sql(hstmt, "CREATE VIEW t_bug23031_v AS SELECT * FROM t_bug23031_t");

  /* Get both the table and view. */
  ok_stmt(hstmt, SQLTables(hstmt, NULL, SQL_NTS, NULL, SQL_NTS,
                           (SQLCHAR *)"t_bug23031%", SQL_NTS, NULL, SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug23031_t", 12);
  is_str(my_fetch_str(hstmt, buff, 4), "TABLE", 5);
  is_str(my_fetch_str(hstmt, buff, 5), "Whee!", 5);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug23031_v", 12);
  is_str(my_fetch_str(hstmt, buff, 4), "VIEW", 4);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Get just the table. */
  ok_stmt(hstmt, SQLTables(hstmt, NULL, SQL_NTS, NULL, SQL_NTS,
                           (SQLCHAR *)"t_bug23031%", SQL_NTS,
                           (SQLCHAR *)"TABLE", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug23031_t", 12);
  is_str(my_fetch_str(hstmt, buff, 4), "TABLE", 5);
  is_str(my_fetch_str(hstmt, buff, 5), "Whee!", 5);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Get just the view. */
  ok_stmt(hstmt, SQLTables(hstmt, NULL, SQL_NTS, NULL, SQL_NTS,
                           (SQLCHAR *)"t_bug23031%", SQL_NTS,
                           (SQLCHAR *)"'VIEW'", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug23031_v", 12);
  is_str(my_fetch_str(hstmt, buff, 4), "VIEW", 4);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP VIEW IF EXISTS t_bug23031_v");
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug23031_t");

  return OK;
}


/**
 Bug #15713: null pointer when use the table qualifier in SQLColumns()
*/
DECLARE_TEST(bug15713)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR   conn[512];
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug15713");
  ok_sql(hstmt, "CREATE TABLE t_bug15713 (a INT)");

  for (int i = 0; i < 2; ++i)
  {
    /* The connection strings must not include DATABASE. */
    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
      NULL, NULL, "", conn_opt[i]));


    ok_stmt(hstmt1, SQLColumns(hstmt1, (SQLCHAR*)"test", SQL_NTS,
      NULL, 0, (SQLCHAR*)"t_bug15713", SQL_NTS,
      NULL, 0));

    ok_stmt(hstmt1, SQLFetch(hstmt1));
    is_str(my_fetch_str(hstmt1, conn, 1), "test", 4);
    is_str(my_fetch_str(hstmt1, conn, 3), "t_bug15713", 10);
    is_str(my_fetch_str(hstmt1, conn, 4), "a", 1);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

    free_basic_handles(&henv1, &hdbc1, &hstmt1);

  }
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug15713");
  return OK;
}


/**
 Bug #28316: Fatal crash with Crystal Reports and MySQL Server
*/
DECLARE_TEST(t_bug28316)
{
  ok_stmt(hstmt, SQLProcedures(hstmt, NULL, SQL_NTS, NULL, SQL_NTS, NULL, SQL_NTS));

  return OK;
}


/**
 Bug #8860: Generic SQLColumns not supported?
*/
DECLARE_TEST(bug8860)
{
  SQLCHAR buff[512];
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug8860, `t_bug8860_a'b`");
  ok_sql(hstmt, "CREATE TABLE t_bug8860 (a INT)");
  ok_sql(hstmt, "CREATE TABLE `t_bug8860_a'b` (b INT)");

  for (int i = 0; i < 2; ++i)
  {
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);
    /*
      Specifying nothing gets us columns from all of the tables in the
      current database.
    */
    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0, NULL, 0, NULL, 0));

    /* We should have at least two rows. There may be more. */
    is(myrowcount(hstmt1) >= 2);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    if (!using_dm(hdbc))
    {
      /* Specifying "" as the table name gets us nothing. */
      /* But iODBC, for one, will convert our "" into a NULL. */
      ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0, (SQLCHAR*)"", SQL_NTS,
        NULL, 0));

      is_num(myrowcount(hstmt1), 0);
    }

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    /* Get the info from just one table.  */
    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
      (SQLCHAR*)"t_bug8860", SQL_NTS,
      NULL, 0));

    ok_stmt(hstmt1, SQLFetch(hstmt1));

    is_str(my_fetch_str(hstmt1, buff, 3), "t_bug8860", 9);
    is_str(my_fetch_str(hstmt1, buff, 4), "a", 1);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    /* Get the info from just one table with a funny name.  */
    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
      (SQLCHAR*)"t_bug8860_a'b", SQL_NTS,
      NULL, 0));

    ok_stmt(hstmt1, SQLFetch(hstmt1));

    is_str(my_fetch_str(hstmt1, buff, 3), "t_bug8860_a'b", 13);
    is_str(my_fetch_str(hstmt1, buff, 4), "b", 1);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "DROP TABLE t_bug8860, `t_bug8860_a'b`");
  return OK;
}

/**
 Bug #26934: SQLTables behavior has changed
*/
DECLARE_TEST(t_bug26934)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  is(OK == alloc_basic_handles(&henv1, &hdbc1, &hstmt1));

  ok_sql(hstmt1, "SET @@wait_timeout = 1");
  sleep(2);
  expect_stmt(hstmt1, SQLTables(hstmt1, (SQLCHAR *)"%", 1, NULL, SQL_NTS,
                                NULL, SQL_NTS, NULL, SQL_NTS), SQL_ERROR);
  if (check_sqlstate(hstmt1, "08S01") != OK)
    return FAIL;

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/**
 Bug #29888: Crystal wizard throws error on including tables
*/
DECLARE_TEST(t_bug29888)
{
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug29888");
  ok_sql(hstmt, "CREATE TABLE t_bug29888 (a INT, b INT)");

  for (int i = 0; i < 2; ++i)
  {
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);
    ok_stmt(hstmt1, SQLColumns(hstmt1, mydb, SQL_NTS, NULL, SQL_NTS,
      (SQLCHAR*)"t_bug29888", SQL_NTS,
      (SQLCHAR*)"%", SQL_NTS));

    is_num(myrowcount(hstmt1), 2);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug29888");

  return OK;
}


/**
  Bug #14407: SQLColumns gives wrong information of not nulls
*/
DECLARE_TEST(t_bug14407)
{
  SQLCHAR col[10];
  SQLSMALLINT nullable;
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug14407");
  ok_sql(hstmt,
         "CREATE TABLE t_bug14407(a INT NOT NULL AUTO_INCREMENT PRIMARY KEY)");

  for (int i = 0; i < 2; ++i)
  {
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
      (SQLCHAR*)"t_bug14407", SQL_NTS, NULL, 0));

    ok_stmt(hstmt1, SQLFetch(hstmt1));
    is_str(my_fetch_str(hstmt1, col, 4), "a", 1);
    is_num(my_fetch_int(hstmt1, 11), SQL_NULLABLE);
    is_str(my_fetch_str(hstmt1, col, 18), "YES", 3);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    /**
      Bug #26108  MyODBC ADO field attributes reporting adFldMayBeNull for
      not null columns
    */
    ok_sql(hstmt1, "SELECT * FROM t_bug14407");

    ok_stmt(hstmt1, SQLDescribeCol(hstmt1, 1, col, sizeof(col), NULL, NULL, NULL,
      NULL, &nullable));
    is_num(nullable, SQL_NULLABLE);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug14407");
  return OK;
}


/**
 Bug #19923: MyODBC Foreign key retrieval broken in multiple ways
 Two issues to test: schema columns should be NULL, not just an empty string,
 and more than one constraint should be returned.
*/
DECLARE_TEST(t_bug19923)
{
  SQLCHAR buff[255];
  SQLLEN len;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug19923c, t_bug19923b, t_bug19923a");
  ok_sql(hstmt, "CREATE TABLE t_bug19923a (a INT PRIMARY KEY) ENGINE=InnoDB");
  ok_sql(hstmt, "CREATE TABLE t_bug19923b (b INT PRIMARY KEY) ENGINE=InnoDB");
  ok_sql(hstmt, "CREATE TABLE t_bug19923c (a INT, b INT, UNIQUE(a), UNIQUE(b),"
                "CONSTRAINT `first_constraint` FOREIGN KEY (`b`) REFERENCES `t_bug19923b` (`b`),"
                "CONSTRAINT `second_constraint` FOREIGN KEY (`a`) REFERENCES `t_bug19923a` (`a`)"
                ") ENGINE=InnoDB");

  ok_stmt(hstmt, SQLForeignKeys(hstmt, NULL, 0, NULL, 0, NULL, 0, NULL, 0,
                                NULL, 0, (SQLCHAR *)"t_bug19923c", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug19923a", 11);
  is_str(my_fetch_str(hstmt, buff, 4), "a", 1);
  is_str(my_fetch_str(hstmt, buff, 7), "t_bug19923c", 11);
  is_str(my_fetch_str(hstmt, buff, 8), "a", 1);
  is_str(my_fetch_str(hstmt, buff, 12), "second_constraint", 17);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 3), "t_bug19923b", 11);
  is_str(my_fetch_str(hstmt, buff, 4), "b", 1);
  is_str(my_fetch_str(hstmt, buff, 7), "t_bug19923c", 11);
  is_str(my_fetch_str(hstmt, buff, 8), "b", 1);
  is_str(my_fetch_str(hstmt, buff, 12), "first_constraint", 16);
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_CHAR, buff, sizeof(buff), &len));
  is_num(len, SQL_NULL_DATA);
  ok_stmt(hstmt, SQLGetData(hstmt, 6, SQL_C_CHAR, buff, sizeof(buff), &len));
  is_num(len, SQL_NULL_DATA);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug19923c, t_bug19923b, t_bug19923a");
  return OK;
}


/*
  Bug #32864 MyODBC /Crystal Reports truncated table names,
             lost fields when names > 21chars
  Fails if built with c/c and probably >=5.5 (with everything
  where SYSTEM_CHARSET_MBMAXLEN != mbmaxlen of UTF8_CHARSET_NUMBER charset)
*/
DECLARE_TEST(t_bug32864)
{
  SQLLEN dispsize= 0;
  SQLULEN colsize= 0;
  SQLCHAR dummy[20];

  ok_stmt(hstmt, SQLTables(hstmt, (SQLCHAR *)"%", SQL_NTS, NULL, 0, NULL, 0,
                           NULL, 0));
  ok_stmt(hstmt, SQLColAttribute(hstmt, 3, SQL_COLUMN_DISPLAY_SIZE, NULL, 0,
                                 NULL, &dispsize));

  is_num(dispsize, 64);

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 3, dummy, sizeof(dummy), NULL, NULL,
                                &colsize, NULL, NULL));

  is_num(colsize, 64);

  return OK;
}


/*
  Bug #32989 - Crystal Reports fails if a field has a single quote
*/
DECLARE_TEST(t_bug32989)
{
  SQLCHAR name[20];
  SQLLEN len;
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "drop table if exists t_bug32989");
  ok_sql(hstmt, "create table t_bug32989 (`doesn't work` int)");

  for (int i = 0; i < 2; ++i)
  {
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    ok_stmt(hstmt1, SQLColumns(hstmt1, (SQLCHAR*)"test", SQL_NTS, NULL, 0,
      (SQLCHAR*)"t_bug32989", SQL_NTS, NULL, 0));
    ok_stmt(hstmt1, SQLFetch(hstmt1));

    ok_stmt(hstmt1, SQLGetData(hstmt1, 4, SQL_C_CHAR, name, 20, &len));
    is_num(len, 12);
    is_str(name, "doesn't work", 13);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "drop table if exists t_bug32989");
  return OK;
}


/**
 Bug #33298: ADO returns wrong parameter count in the query (always 0)
*/
DECLARE_TEST(t_bug33298)
{
  SQLRETURN rc= 0;
  expect_stmt(hstmt, SQLProcedureColumns(hstmt, NULL, 0, NULL, 0,
                                         NULL, 0, NULL, 0),
                                         SQL_SUCCESS);
  rc= SQLFetch(hstmt);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  if (rc == SQL_ERROR)
    return FAIL;

  return OK;
}

/**
 Bug #12805: ADO failed to retrieve the length of LONGBLOB columns
*/
DECLARE_TEST(t_bug12805)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR     dummy[10];
  SQLULEN     length;
  SQLUINTEGER len2;
  char* conn_opt[] = {
    "COLUMN_SIZE_S32=1;NO_SSPS=0",
    "COLUMN_SIZE_S32=1;NO_SSPS=1"
  };

  for (int i = 0; i < 2; i++)
  {
    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
      NULL, NULL, NULL, conn_opt[i]));

    ok_sql(hstmt1, "DROP TABLE IF EXISTS bug12805");
    ok_sql(hstmt1, "CREATE TABLE bug12805("\
      "id INT PRIMARY KEY auto_increment,"\
      "longdata LONGBLOB NULL)");

    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
      (SQLCHAR*)"bug12805", SQL_NTS,
      (SQLCHAR*)"longdata", SQL_NTS));

    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 7, SQL_C_ULONG, &len2,
      0, NULL));
    is_num(len2, 2147483647);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    length = 0;
    ok_sql(hstmt1, "SELECT * FROM bug12805");
    ok_stmt(hstmt1, SQLDescribeCol(hstmt1, 2, dummy, sizeof(dummy) - 1, NULL,
      NULL, &length, NULL, NULL));
    is_num(length, 2147483647);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  char* conn_opt2[] = { "NO_SSPS=0", "NO_SSPS=1" };
  /* Check without the 32-bit signed flag */
  for (int i = 0; i < 2; i++)
  {
    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
      NULL, NULL, NULL, conn_opt2[i]));

    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
      (SQLCHAR*)"bug12805", SQL_NTS,
      (SQLCHAR*)"longdata", SQL_NTS));

    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 7, SQL_C_ULONG, &len2,
      0, NULL));
    is_num(len2, 4294967295UL);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    length = 0;
    ok_sql(hstmt1, "SELECT * FROM bug12805");
    ok_stmt(hstmt1, SQLDescribeCol(hstmt1, 2, dummy, sizeof(dummy), NULL, NULL,
      &length, NULL, NULL));
    is_num(length, 4294967295UL);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "DROP TABLE bug12805");

  return OK;
}


/**
 Bug #30770: Access Violation in myodbc3.dll
*/
DECLARE_TEST(t_bug30770)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR    buff[512];
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug30770");
  ok_sql(hstmt, "CREATE TABLE bug30770 (a INT)");

  for (int i = 0; i < 2; i++)
  {
    /* Connect with no default daabase */
    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, USE_DRIVER,
      NULL, NULL, "", conn_opt[i]));

    sprintf((char*)buff, "USE %s;", mydb);
    ok_stmt(hstmt1, SQLExecDirect(hstmt1, buff, SQL_NTS));

    /* Get the info from just one table.  */
    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, SQL_NTS, NULL, SQL_NTS,
      (SQLCHAR*)"bug30770", SQL_NTS, NULL, 0));

    ok_stmt(hstmt1, SQLFetch(hstmt1));

    is_str(my_fetch_str(hstmt1, buff, 3), "bug30770", 9);
    is_str(my_fetch_str(hstmt1, buff, 4), "a", 1);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);
    free_basic_handles(&henv1, &hdbc1, &hstmt1);

  }
  ok_sql(hstmt, "DROP TABLE bug30770");
  return OK;
}


/**
 Bug #36275: SQLTables buffer overrun
*/
DECLARE_TEST(t_bug36275)
{
  expect_stmt(hstmt, SQLTables(hstmt, NULL, 0, NULL, 0, NULL, 0,
                           (SQLCHAR *)
/* Just a really long literal to blow out the buffer. */
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789"
"0123456789012345678901234567890123456789012345678901234567890123456789",
                           SQL_NTS), SQL_ERROR);

  return OK;
}


/*
  Bug #39957 - NULL catalog did not return correct catalog in result
*/
DECLARE_TEST(t_bug39957)
{
  SQLCHAR buf[50];
  ok_sql(hstmt, "drop table if exists t_bug39957");
  ok_sql(hstmt, "create table t_bug39957 (x int)");
  ok_stmt(hstmt, SQLTables(hstmt, NULL, 0, NULL, 0,
			   (SQLCHAR *)"t_bug39957", SQL_NTS, NULL, 0));
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buf, 3), "t_bug39957", 11);
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_bug39957");

  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_columns)
  ADD_TEST(t_catalog)
  ADD_TEST(t_sqlprocedures)
  ADD_TEST(my_columns_null)
  ADD_TEST(my_drop_table)
  ADD_TEST(my_table_dbs)
  ADD_TEST(my_colpriv)
  ADD_TEST(tmysql_specialcols)
  ADD_TEST(t_tables_bug)
  ADD_TEST(t_current_catalog_unicode)
  ADD_TEST(t_current_catalog_ansi)
  ADD_TEST(t_sqltables)
  ADD_TEST(bug15713)
  ADD_TEST(t_bug29888)
  ADD_TEST(t_bug32989)
  ADD_TEST(t_bug12805)
  ADD_TEST(tmysql_showkeys)
  ADD_TEST(my_information_schema)
  ADD_TEST(t_bug4518)
  ADD_TEST(empty_set)
  ADD_TEST(t_bug23031)
  ADD_TEST(t_bug28316)
  ADD_TEST(bug8860)
  ADD_TEST(t_bug26934)
  ADD_TEST(t_bug14407)
  ADD_TEST(t_bug19923)
  ADD_TEST(t_bug32864)
  ADD_TEST(t_bug33298)
  ADD_TEST(t_bug30770)
  ADD_TEST(t_bug36275)
  ADD_TEST(t_bug39957)
END_TESTS

RUN_TESTS
