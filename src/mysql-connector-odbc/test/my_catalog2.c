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


/*
  Bug #37621 - SQLDescribeCol returns incorrect values of SQLTables data
*/
DECLARE_TEST(t_bug37621)
{
  SQLCHAR szColName[128];
  SQLSMALLINT iName, iType, iScale, iNullable;
  SQLULEN uiDef;

  ok_sql(hstmt, "drop table if exists t_bug37621");
  ok_sql(hstmt, "create table t_bug37621 (x int)");
  ok_stmt(hstmt, SQLTables(hstmt, NULL, 0, NULL, 0,
			   (SQLCHAR *)"t_bug37621", SQL_NTS, NULL, 0));
/*
  Check column properties for the REMARKS column
*/
  ok_stmt(hstmt, SQLDescribeCol(hstmt, 5, szColName, sizeof(szColName),
          &iName, &iType, &uiDef, &iScale, &iNullable));

  is_str(szColName, "REMARKS", 8);
  is_num(iName, 7);
  if (iType != SQL_VARCHAR && iType != SQL_WVARCHAR)
    return FAIL;
  /* This can fail for the same reason as t_bug32864 */
  is_num(uiDef, 80);
  is_num(iScale, 0);
  is_num(iNullable, 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "drop table if exists t_bug37621");

  return OK;
}


/*
Bug #34272 - SQLColumns returned wrong values for (some) TYPE_NAME
and (some) IS_NULLABLE
*/
DECLARE_TEST(t_bug34272)
{
  SQLCHAR dummy[20];
  SQLULEN col6, col18, length;
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  char *conn_opt[] = {"NO_SSPS=0", "NO_SSPS=1"};

  ok_sql(hstmt, "drop table if exists t_bug34272");
  ok_sql(hstmt, "create table t_bug34272 (x int unsigned)");

  for (int i = 0; i < 2; ++i)
  {
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);
    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, SQL_NTS, NULL, SQL_NTS,
      (SQLCHAR *)"t_bug34272", SQL_NTS, NULL, 0));

    ok_stmt(hstmt1, SQLDescribeCol(hstmt1, 6, dummy, sizeof(dummy), NULL, NULL,
      &col6, NULL, NULL));
    ok_stmt(hstmt1, SQLDescribeCol(hstmt1, 18, dummy, sizeof(dummy), NULL, NULL,
      &col18, NULL, NULL));
    ok_stmt(hstmt1, SQLFetch(hstmt1));

    ok_stmt(hstmt1, SQLGetData(hstmt1, 6, SQL_C_CHAR, dummy, col6+1, &length));
    /* Can be "integer unsigned" (16) or "int unsigned" (12) */
    is(length == 16 || length == 3);
    is(strncmp(dummy, "integer unsigned", 16) == 0 ||
       strncmp(dummy, "int", 3) == 0);

    ok_stmt(hstmt1, SQLGetData(hstmt1, 18, SQL_C_CHAR, dummy, col18+1, &length));
    is_num(length,3);
    is_str(dummy, "YES", length+1);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  ok_sql(hstmt, "drop table if exists t_bug34272");

  return OK;
}


/*
  Bug #49660 - constraints with same name for tables with same name but in different db led
  to doubling of results of SQLForeignKeys
*/
DECLARE_TEST(t_bug49660)
{
  SQLLEN rowsCount;

  ok_sql(hstmt, "drop database if exists bug49660");
  ok_sql(hstmt, "drop table if exists t_bug49660");
  ok_sql(hstmt, "drop table if exists t_bug49660_r");

  ok_sql(hstmt, "create database bug49660");
  ok_sql(hstmt, "create table bug49660.t_bug49660_r (id int unsigned not null primary key, name varchar(10) not null) ENGINE=InnoDB");
  ok_sql(hstmt, "create table bug49660.t_bug49660 (id int unsigned not null primary key, refid int unsigned not null,"
                "foreign key t_bug49660fk (id) references bug49660.t_bug49660_r (id)) ENGINE=InnoDB");

  ok_sql(hstmt, "create table t_bug49660_r (id int unsigned not null primary key, name varchar(10) not null) ENGINE=InnoDB");
  ok_sql(hstmt, "create table t_bug49660 (id int unsigned not null primary key, refid int unsigned not null,"
                "foreign key t_bug49660fk (id) references t_bug49660_r (id)) ENGINE=InnoDB");

  ok_stmt(hstmt, SQLForeignKeys(hstmt, NULL, 0, NULL, 0, NULL, 0, NULL, 0,
                                NULL, 0, (SQLCHAR *)"t_bug49660", SQL_NTS));

  ok_stmt(hstmt, SQLRowCount(hstmt, &rowsCount));
  /* is_num(rowsCount, 1); */
  /* Going another way around - sort of more reliable */
  ok_stmt(hstmt, SQLFetch(hstmt));
  expect_stmt(hstmt,SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt,SQL_CLOSE));

  ok_sql(hstmt, "drop database if exists bug49660");
  ok_sql(hstmt, "drop table if exists t_bug49660");
  ok_sql(hstmt, "drop table if exists t_bug49660_r");

  return OK;
}


/*
  Bug #51422 - SQLForeignKeys returned keys pointing to unique fields
*/
DECLARE_TEST(t_bug51422)
{
  SQLLEN rowsCount;

  ok_sql(hstmt, "drop table if exists t_bug51422");
  ok_sql(hstmt, "drop table if exists t_bug51422_r");

  ok_sql(hstmt, "create table t_bug51422_r (id int unsigned not null primary key, ukey int unsigned not null,"
                "name varchar(10) not null, UNIQUE KEY uk(ukey)) ENGINE=InnoDB");
  ok_sql(hstmt, "create table t_bug51422 (id int unsigned not null primary key, refid int unsigned not null,"
                "foreign key t_bug51422fk (id) references t_bug51422_r (ukey))  ENGINE=InnoDB");

  ok_stmt(hstmt, SQLForeignKeys(hstmt, NULL, 0, NULL, 0, NULL, 0, NULL, 0,
                                NULL, 0, (SQLCHAR *)"t_bug51422", SQL_NTS));

  ok_stmt(hstmt, SQLRowCount(hstmt, &rowsCount));
  is_num(rowsCount, 0);

  ok_sql(hstmt, "drop table if exists t_bug51422");
  ok_sql(hstmt, "drop table if exists t_bug51422_r");

  return OK;
}


/*
  Bug #36441 - SQLPrimaryKeys returns mangled strings
*/
DECLARE_TEST(t_bug36441)
{
#define BUF_LEN 24

  const SQLCHAR key_column_name[][14]= {"pk_for_table1", "c1_for_table1"};

  SQLCHAR     catalog[BUF_LEN], schema[BUF_LEN], table[BUF_LEN], column[BUF_LEN];
  SQLLEN      catalog_len, schema_len, table_len, column_len;
  SQLCHAR     keyname[BUF_LEN];
  SQLSMALLINT key_seq, i;
  SQLLEN      keyname_len, key_seq_len, rowCount;

  ok_sql(hstmt, "drop table if exists t_bug36441_0123456789");
  ok_sql(hstmt, "create table t_bug36441_0123456789("
	              "pk_for_table1 integer not null auto_increment,"
	              "c1_for_table1 varchar(128) not null unique,"
	              "c2_for_table1 binary(32) null,"
                "unique_key int unsigned not null unique,"
	              "primary key(pk_for_table1, c1_for_table1))");

  ok_stmt(hstmt, SQLPrimaryKeys(hstmt, NULL, SQL_NTS, NULL, SQL_NTS, "t_bug36441_0123456789", SQL_NTS));

  /* Test of SQLRowCount with SQLPrimaryKeys */
  ok_stmt(hstmt, SQLRowCount(hstmt, &rowCount));
  is_num(rowCount, 2);

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR , catalog, sizeof(catalog), &catalog_len));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR , schema , sizeof(schema) , &schema_len));
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_CHAR , table  , sizeof(table)  , &table_len));
  ok_stmt(hstmt, SQLBindCol(hstmt, 4, SQL_C_CHAR , column , sizeof(column) , &column_len));
  ok_stmt(hstmt, SQLBindCol(hstmt, 5, SQL_C_SHORT,&key_seq, sizeof(key_seq), &key_seq_len));
  ok_stmt(hstmt, SQLBindCol(hstmt, 6, SQL_C_CHAR , keyname, sizeof(keyname), &keyname_len));

  for(i= 0; i < 2; ++i)
  {
    ok_stmt(hstmt, SQLFetch(hstmt));

    is_str(catalog, mydb, strlen(mydb));
    is_num(schema_len, -1);
    is_str(table, "t_bug36441_0123456789", 3);
    is_str(column, key_column_name[i], 4);
    is_num(key_seq, i+1);
    is_str(keyname, "PRIMARY", 6);
  }

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "drop table if exists t_bug36441_0123456789");

  return OK;

#undef BUF_LEN
}


/*
  Bug #53235 - SQLColumns returns wrong transfer octet length
*/
DECLARE_TEST(t_bug53235)
{
  int col_size, buf_len;

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "drop table if exists t_bug53235");
  ok_sql(hstmt, "create table t_bug53235 (x decimal(10,3), y decimal(8,4) unsigned, z integer)");

  for (int i = 0; i < 2; ++i)
  {
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
			     (SQLCHAR *)"t_bug53235", SQL_NTS, NULL, 0));
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    col_size= my_fetch_int(hstmt1, 7);
    buf_len= my_fetch_int(hstmt1, 8);
    is_num(col_size, 10);
    is_num(buf_len, 15);

    ok_stmt(hstmt1, SQLFetch(hstmt1));
    col_size= my_fetch_int(hstmt1, 7);
    buf_len= my_fetch_int(hstmt1, 8);
    is_num(col_size, 8);
    is_num(buf_len, 13);

    ok_stmt(hstmt1, SQLFetch(hstmt1));
    col_size= my_fetch_int(hstmt1, 7);
    buf_len= my_fetch_int(hstmt1, 8);
    is_num(col_size, 10);
    is_num(buf_len, 4);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "drop table if exists t_bug53235");

  return OK;
}


/*
  Bug #50195 - SQLTablePrivileges requires select priveleges
*/
DECLARE_TEST(t_bug50195)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  const char  expected_privs[][12]= {"ALTER", "CREATE", "CREATE VIEW", "DELETE", "DROP", "INDEX",
                                    "INSERT", "REFERENCES", "SHOW VIEW", "TRIGGER", "UPDATE"};
  int         i;
  SQLCHAR     priv[12];
  SQLLEN      len;

  /*
    The patch for this issue can only work with Information_Schema.
    Therefore skipping it for NO_IS=1 case
  */
  if (myoption && (1 << 30))
    return OK;

  (void)SQLExecDirect(hstmt, (SQLCHAR *)"DROP USER `bug50195`@`%`", SQL_NTS);
  (void)SQLExecDirect(hstmt, (SQLCHAR *)"DROP USER `bug50195`@`127.0.0.1`", SQL_NTS);
  (void)SQLExecDirect(hstmt, (SQLCHAR *)"DROP USER `bug50195`@`localhost`", SQL_NTS);

  ok_sql(hstmt, "CREATE USER `bug50195`@`%` IDENTIFIED BY 'a'");
  ok_sql(hstmt, "CREATE USER `bug50195`@`127.0.0.1` IDENTIFIED BY 'a'");
  ok_sql(hstmt, "CREATE USER `bug50195`@`localhost` IDENTIFIED BY 'a'");
  ok_sql(hstmt, "grant all on *.* to `bug50195`@`%`");
  ok_sql(hstmt, "grant all on *.* to `bug50195`@`127.0.0.1`");
  ok_sql(hstmt, "grant all on *.* to `bug50195`@`localhost`");

  ok_sql(hstmt, "revoke select on *.* from bug50195@`%`");
  ok_sql(hstmt, "revoke select on *.* from bug50195@`127.0.0.1`");
  ok_sql(hstmt, "revoke select on *.* from bug50195@`localhost`");

  /* revoking "global" select is enough, but revoking smth from mysql.tables_priv
     to have not empty result of SQLTablePrivileges */
  ok_sql(hstmt, "grant all on mysql.tables_priv to bug50195@`%`");
  ok_sql(hstmt, "grant all on mysql.tables_priv to bug50195@`127.0.0.1`");
  ok_sql(hstmt, "grant all on mysql.tables_priv to bug50195@`localhost`");
  ok_sql(hstmt, "revoke select on mysql.tables_priv from bug50195@`%`");
  ok_sql(hstmt, "revoke select on mysql.tables_priv from bug50195@`127.0.0.1`");
  ok_sql(hstmt, "revoke select on mysql.tables_priv from bug50195@`localhost`");

  ok_sql(hstmt, "FLUSH PRIVILEGES");

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        "bug50195", "a", NULL, NULL));

  ok_stmt(hstmt1, SQLTablePrivileges(hstmt1, "mysql", SQL_NTS, 0, 0, "tables_priv", SQL_NTS));

  /* Testing SQLTablePrivileges a bit, as we don't have separate test of it */

  for(i= 0; i < 11; ++i)
  {
    ok_stmt(hstmt1, SQLFetch(hstmt1));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 6, SQL_C_CHAR, priv, sizeof(priv), &len));
    is_str(priv, expected_privs[i], len);
  }

  expect_stmt(hstmt1, SQLFetch(hstmt1),100);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  ok_sql(hstmt, "DROP USER bug50195@`%`");
  ok_sql(hstmt, "DROP USER bug50195@`127.0.0.1`");
  ok_sql(hstmt, "DROP USER bug50195@`localhost`");

  return OK;
}


DECLARE_TEST(t_sqlprocedurecolumns)
{
  SQLRETURN rc= 0;
  SQLCHAR szName[255]= {0};

  typedef struct
  {
    char *c01_procedure_cat;
    char *c02_procedure_schem;
    char *c03_procedure_name;
    char *c04_column_name;
    short c05_column_type;
    short c06_data_type;
    char *c07_type_name;
    unsigned long c08_column_size;
    unsigned long c09_buffer_length;
    short c10_decimal_digits;
    short c11_num_prec_radix;
    short c12_nullable;
    char *c13_remarks;
    char *c14_column_def;
    short c15_sql_data_type;
    short c16_sql_datetime_sub;
    unsigned long c17_char_octet_length;
    int c18_ordinal_position;
    char *c19_is_nullable;
  }sqlproccol;

  int total_params= 0, iter= 0;

  sqlproccol data_to_check[] = {
    /*cat    schem  proc_name                  col_name     col_type         data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param1", SQL_PARAM_INPUT, SQL_TINYINT, "tinyint",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
     1,    1,       0,  10,    SQL_NULLABLE, "", 0,  SQL_TINYINT,  0,  0,    1,  "YES"},

    /*cat    schem  proc_name                  col_name     col_type          data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param2", SQL_PARAM_OUTPUT, SQL_SMALLINT, "smallint",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
     5,    2,       0,  10,    SQL_NULLABLE, "", 0,  SQL_SMALLINT,  0,  0,    2,  "YES"},

    /*cat    schem  proc_name                  col_name     col_type          data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param3", SQL_PARAM_INPUT,  SQL_INTEGER, "mediumint",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
     8,    3,       0,  10,    SQL_NULLABLE, "", 0,  SQL_INTEGER,  0,  0,    3,  "YES"},

    /*cat    schem  proc_name                  col_name      col_type                data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param 4", SQL_PARAM_INPUT_OUTPUT, SQL_INTEGER, "int",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
     10,     4,       0,  10,  SQL_NULLABLE, "", 0,  SQL_INTEGER,  0,  0,    4,  "YES"},

    /*cat    schem  proc_name                  col_name     col_type          data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param5", SQL_PARAM_OUTPUT, SQL_BIGINT, "bigint",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
     19,     20,      0,  10,  SQL_NULLABLE, "", 0,  SQL_BIGINT,   0,  0,    5,  "YES"},

    /*cat    schem  proc_name                  col_name     col_type         data_type  type_name */
    {"test", 0,     "procedure_columns_test1", "re_param6", SQL_PARAM_INPUT, SQL_REAL,  "float",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      7,   4,       0,  0,     SQL_NULLABLE, "", 0,  SQL_REAL,     0,  0,    6,  "YES"},

    /*cat    schem  proc_name                  col_name     col_type          data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param7", SQL_PARAM_OUTPUT, SQL_DOUBLE,  "double",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      15,  8,       0,  0,     SQL_NULLABLE, "", 0,  SQL_DOUBLE,   0,  0,    7,  "YES"},

    /*cat    schem  proc_name                  col_name     col_type          data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param8", SQL_PARAM_INPUT, SQL_DECIMAL,  "decimal",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      10,  11,       3,  10,    SQL_NULLABLE, "", 0,  SQL_DECIMAL,   0,  0,    8,  "YES"},

    /*cat    schem  proc_name                  col_name     col_type          data_type type_name */
    {"test", 0,     "procedure_columns_test1", "re_param9", SQL_PARAM_INPUT, SQL_CHAR,  "char",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      32,  32,      0,  0,     SQL_NULLABLE, "", 0,  SQL_CHAR,     0,  32,    9,  "YES"},

    /*cat    schem  proc_name                  col_name      col_type           data_type    type_name */
    {"test", 0,     "procedure_columns_test1", "re_param10", SQL_PARAM_OUTPUT, SQL_VARCHAR, "varchar",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      64,  64,      0,  0,     SQL_NULLABLE, "", 0,  SQL_VARCHAR,  0,  64,   10, "YES"},

    /*cat    schem  proc_name                  col_name      col_type         data_type          type_name */
    {"test", 0,     "procedure_columns_test1", "re_param11", SQL_PARAM_INPUT, SQL_LONGVARBINARY, "mediumblob",
    /*size      buf_len   dec radix  nullable      rem def sql_data_type       sub octet     pos nullable*/
      16777215, 16777215, 0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARBINARY,  0,  16777215, 12, "YES"},

    /*cat    schem  proc_name                  col_name     col_type         data_type          type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramA", SQL_PARAM_INPUT, SQL_LONGVARBINARY, "blob",
    /*size      buf_len   dec radix  nullable      rem def sql_data_type       sub octet     pos nullable*/
      65535,    65535,    0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARBINARY,  0,  65535,    1, "YES"},

    /*cat    schem  proc_name                  col_name     col_type         data_type          type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramB", SQL_PARAM_INPUT, SQL_LONGVARBINARY, "longblob",
    /*size        buf_len      dec radix  nullable      rem def sql_data_type       sub octet        pos nullable*/
     4294967295L, 2147483647L, 0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARBINARY,  0,  2147483647L, 2, "YES"},

    /*cat    schem  proc_name                  col_name     col_type               data_type          type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramC", SQL_PARAM_INPUT_OUTPUT, SQL_LONGVARBINARY, "tinyblob",
    /*size   buf_len dec radix  nullable      rem def sql_data_type       sub octet pos nullable*/
     255,    255,    0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARBINARY,  0,  255,  3, "YES"},

    /*cat    schem  proc_name                  col_name     col_type         data_type          type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramD", SQL_PARAM_INPUT, SQL_LONGVARBINARY, "mediumblob",
    /*size     buf_len   dec radix  nullable      rem def sql_data_type       sub octet     pos nullable*/
     16777215, 16777215, 0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARBINARY,  0,  16777215, 4, "YES"},

     /*cat    schem  proc_name                  col_name    col_type        data_type          type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramE", SQL_PARAM_INPUT, SQL_VARBINARY, "varbinary",
    /*size   buf_len dec radix  nullable      rem def sql_data_type   sub octet pos nullable*/
     128,    128,    0,  0,     SQL_NULLABLE, "", 0,  SQL_VARBINARY,  0,  128,  5, "YES"},

     /*cat    schem  proc_name                  col_name    col_type          data_type   type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramF", SQL_PARAM_OUTPUT, SQL_BINARY, "binary",
    /*size   buf_len dec radix  nullable      rem def sql_data_type   sub octet pos nullable*/
     1,      1,      0,  0,     SQL_NULLABLE, "", 0,  SQL_BINARY,     0,  1,    6, "YES"},

     /*cat    schem  proc_name                 col_name     col_type          data_type   type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramG", SQL_PARAM_INPUT,  SQL_BINARY, "binary",
    /*size   buf_len dec radix  nullable      rem def sql_data_type   sub octet pos nullable*/
     8,      8,      0,  0,     SQL_NULLABLE, "", 0,  SQL_BINARY,     0,  8,    7, "YES"},

    /*cat    schem  proc_name                  col_name      col_type          data_type          type_name */
    {"test", 0,     "procedure_columns_test2", "re_param H", SQL_PARAM_INPUT, SQL_LONGVARCHAR, "mediumtext",
    /*size     buf_len   dec radix  nullable      rem def sql_data_type       sub octet     pos nullable*/
     16777215, 16777215, 0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARCHAR,  0,  16777215, 8, "YES"},

    /*cat    schem  proc_name                  col_name      col_type         data_type       type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramI", SQL_PARAM_INPUT, SQL_LONGVARCHAR, "text",
    /*size      buf_len   dec radix  nullable      rem def sql_data_type    sub octet  pos nullable*/
      65535,    65535,    0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARCHAR, 0,  65535,  9, "YES"},

    /*cat    schem  proc_name                  col_name     col_type         data_type       type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramJ", SQL_PARAM_INPUT, SQL_LONGVARCHAR, "mediumtext",
    /*size     buf_len   dec radix  nullable      rem def sql_data_type    sub octet     pos nullable*/
     16777215, 16777215, 0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARCHAR, 0,  16777215, 10, "YES"},

     /*cat    schem  proc_name                  col_name    col_type              data_type        type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramK", SQL_PARAM_INPUT_OUTPUT, SQL_LONGVARCHAR, "longtext",
    /*size        buf_len      dec radix  nullable      rem def sql_data_type    sub octet        pos nullable*/
     4294967295L, 2147483647L, 0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARCHAR, 0,  2147483647L, 11, "YES"},

    /*cat    schem  proc_name                  col_name     col_type         data_type        type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramL", SQL_PARAM_INPUT, SQL_LONGVARCHAR, "tinytext",
    /*size   buf_len dec radix  nullable      rem def sql_data_type    sub octet pos nullable*/
     255,    255,    0,  0,     SQL_NULLABLE, "", 0,  SQL_LONGVARCHAR, 0,  255,  12, "YES"},

    /*cat    schem  proc_name                  col_name     col_type         data_type    type_name */
    {"test", 0,     "procedure_columns_test2", "re_paramM", SQL_PARAM_INPUT, SQL_DECIMAL,  "decimal",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      8,   10,      2,  10,    SQL_NULLABLE, "", 0,  SQL_DECIMAL,   0,  0,   13,  "YES"},

    /*cat    schem  proc_name                  col_name       col_type         data_type     type_name */
    {"test", 0,     "procedure_columns_test3", "re_param_00", SQL_PARAM_INPUT, SQL_TYPE_TIMESTAMP, "datetime",
    /*size buf_len  dec radix  nullable      rem def sql_data_type  sub                  octet pos nullable*/
      19,  16,      0,  10,     SQL_NULLABLE, "", 0, SQL_DATETIME,  SQL_TYPE_TIMESTAMP,  0,    1,  "YES"},

    /*cat    schem  proc_name                  col_name       col_type          data_type      type_name */
    {"test", 0,     "procedure_columns_test3", "re_param_01", SQL_PARAM_OUTPUT, SQL_TYPE_DATE, "date",
    /*size buf_len  dec radix  nullable      rem def sql_data_type  sub octet pos nullable*/
      10,  6,       0,  0,     SQL_NULLABLE, "", 0,  SQL_DATETIME,  SQL_TYPE_DATE,  0,    2,  "YES"},

    /*cat    schem  proc_name                  col_name       col_type          data_type      type_name */
    {"test", 0,     "procedure_columns_test3", "re_param_02", SQL_PARAM_OUTPUT, SQL_TYPE_TIME, "time",
    /*size buf_len  dec radix  nullable      rem def sql_data_type  sub octet pos nullable*/
      8,   6,       0,  0,     SQL_NULLABLE, "", 0,  SQL_DATETIME,  SQL_TYPE_TIME,  0,    3,  "YES"},

    /*cat    schem  proc_name                  col_name       col_type                data_type           type_name */
    {"test", 0,     "procedure_columns_test3", "re_param_03", SQL_PARAM_INPUT_OUTPUT, SQL_TYPE_TIMESTAMP, "timestamp",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub                  octet pos nullable*/
      19,  16,      0,  0,     SQL_NULLABLE, "", 0,  SQL_DATETIME, SQL_TYPE_TIMESTAMP,  0,    4,  "YES"},

    /*cat    schem  proc_name                  col_name       col_type         data_type     type_name */
    {"test", 0,     "procedure_columns_test3", "re_param_04", SQL_PARAM_INPUT, SQL_SMALLINT, "year",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      4,   1,       0,  10,    SQL_NULLABLE, "", 0,  SQL_SMALLINT, 0,  0,    5,  "YES"},

    /*cat    schem  proc_name                       col_name        col_type          data_type    type_name */
    {"test", 0,     "procedure_columns_test4_func", "RETURN_VALUE", SQL_RETURN_VALUE, SQL_VARCHAR, "varchar",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      32,  32,      0,  0,     SQL_NULLABLE, "", 0,  SQL_VARCHAR,  0,  32,   0, "YES"},

     /*cat    schem  proc_name                       col_name    col_type         data_type    type_name */
    {"test", 0,     "procedure_columns_test4_func", "re_paramF", SQL_PARAM_INPUT, SQL_INTEGER, "int",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
     10,     4,       0,  10,  SQL_NULLABLE, "", 0,  SQL_INTEGER,  0,  0,    1,  "YES"},

    /*cat    schem  proc_name                               col_name        col_type          data_type    type_name */
    {"test", 0,     "procedure_columns_test4_func_noparam", "RETURN_VALUE", SQL_RETURN_VALUE, SQL_VARCHAR, "varchar",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      32,  32,      0,  0,     SQL_NULLABLE, "", 0,  SQL_VARCHAR,  0,  32,   0, "YES"},

    /*cat    schem  proc_name                  col_name           col_type         data_type type_name */
    {"test", 0,     "procedure_columns_test5", "re_param_set_01", SQL_PARAM_INPUT, SQL_CHAR,  "char",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      14,  14,      0,  0,     SQL_NULLABLE, "", 0,  SQL_CHAR,     0,  14,    1,  "YES"},

    /*cat    schem  proc_name                  col_name            col_type          data_type type_name */
    {"test", 0,     "procedure_columns_test5", "re_param_enum_02", SQL_PARAM_OUTPUT, SQL_CHAR,  "char",
    /*size buf_len  dec radix  nullable      rem def sql_data_type sub octet pos nullable*/
      7,   7,       0,  0,     SQL_NULLABLE, "", 0,  SQL_CHAR,     0,  7,    2,  "YES"},
  };

  ok_sql(hstmt, "drop procedure if exists procedure_columns_test1");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test2");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test2_noparam");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test3");
  ok_sql(hstmt, "drop function if exists procedure_columns_test4_func");
  ok_sql(hstmt, "drop function if exists procedure_columns_test4_func_noparam");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test5");

  ok_sql(hstmt, "create procedure procedure_columns_test1(IN re_param1 TINYINT, OUT re_param2 SMALLINT," \
                "re_param3 MEDIUMINT, INOUT `re_param 4` INT UNSIGNED, OUT re_param5 BIGINT, re_param6 FLOAT(4,2)," \
                "OUT re_param7 DOUBLE(5, 3), IN re_param8 DECIMAL(10,3) unSIGned, re_param9 CHAR(32)," \
                "Out re_param10 VARCHAR(64) charset utf8, ignore_param INT, re_param11 MEDIUMBLOB)" \
                "begin end;"
                );
  ok_sql(hstmt, "create procedure procedure_columns_test2(IN re_paramA bloB," \
                "IN re_paramB LONGBLOB, inout re_paramC TinyBlob, re_paramD mediumblob, IN re_paramE varbinary(128)," \
                "OUT re_paramF binary, re_paramG binary(8), `re_param H` MEDIUMTEXT, IN re_paramI TEXT," \
                "re_paramJ mediumtext, INOUT re_paramK longtext, re_paramL tinytext, re_paramM numeric(8,2))" \
                "begin end;"
                );
  ok_sql(hstmt, "create procedure procedure_columns_test2_noparam()"\
                "begin end;"
                );

  ok_sql(hstmt, "create procedure procedure_columns_test3(IN re_param_00 datetime,"\
                "OUT re_param_01 date, OUT re_param_02 time, INOUT re_param_03 timestamp,"\
                "re_param_04 year)" \
                "begin end;"
                );

  ok_sql(hstmt, "create function procedure_columns_test4_func(re_paramF int) returns varchar(32) deterministic "\
                "begin return CONCAT('abc', paramF); end;"
                );

  ok_sql(hstmt, "create function procedure_columns_test4_func_noparam() returns varchar(32) deterministic "\
                "begin return 'abc'; end;"
                );

  ok_sql(hstmt, "create procedure procedure_columns_test5(IN re_param_set_01 SET('', \"one\", 'two', 'three'),"\
                "OUT re_param_enum_02 ENUM('', \"one\", 'tw)o', 'three m'))" \
                "begin end;"
                );

  ok_stmt(hstmt, SQLProcedureColumns(hstmt, NULL, 0, NULL, 0,
                                     "procedure_columns_test%", SQL_NTS,
                                     "re_%", SQL_NTS));

  while(SQLFetch(hstmt) == SQL_SUCCESS)
  {
    SQLCHAR buff[255] = {0}, *param_cat, *param_name;
    SQLUINTEGER col_size, buf_len, octet_len;
    printf("\nRow %d\n", (iter+1));

    param_cat= (SQLCHAR*)my_fetch_str(hstmt, buff, 1);
    is_str(param_cat, data_to_check[iter].c01_procedure_cat,
           strlen(data_to_check[iter].c01_procedure_cat) + 1);

    is_str(my_fetch_str(hstmt, buff, 3),
           data_to_check[iter].c03_procedure_name,
           strlen(data_to_check[iter].c03_procedure_name) + 1);

    param_name= (SQLCHAR*)my_fetch_str(hstmt, buff, 4);
    is_str(param_name, data_to_check[iter].c04_column_name,
           strlen(data_to_check[iter].c04_column_name) + 1);

    is_num(my_fetch_int(hstmt, 5), data_to_check[iter].c05_column_type);

    is_num(my_fetch_int(hstmt, 6), data_to_check[iter].c06_data_type);

    is_str(my_fetch_str(hstmt, buff, 7),
           data_to_check[iter].c07_type_name,
           strlen(data_to_check[iter].c07_type_name) + 1);

    col_size= my_fetch_uint(hstmt, 8);
    is_num(col_size, data_to_check[iter].c08_column_size);

    buf_len= my_fetch_uint(hstmt, 9);
    is_num(buf_len, data_to_check[iter].c09_buffer_length);

    is_num(my_fetch_int(hstmt, 10), data_to_check[iter].c10_decimal_digits);

    is_num(my_fetch_int(hstmt, 11), data_to_check[iter].c11_num_prec_radix);

    is_num(my_fetch_int(hstmt, 15), data_to_check[iter].c15_sql_data_type);

    is_num(my_fetch_int(hstmt, 16), data_to_check[iter].c16_sql_datetime_sub);

    octet_len= my_fetch_uint(hstmt, 17);
    is_num(octet_len, data_to_check[iter].c17_char_octet_length);

    is_num(my_fetch_int(hstmt, 18), data_to_check[iter].c18_ordinal_position);

    is_str(my_fetch_str(hstmt, buff, 19),
           data_to_check[iter].c19_is_nullable,
           strlen(data_to_check[iter].c19_is_nullable + 1));

    ++iter;
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "drop procedure if exists procedure_columns_test1");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test2");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test2_noparam");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test3");
  ok_sql(hstmt, "drop function if exists procedure_columns_test4_func");
  ok_sql(hstmt, "drop function if exists procedure_columns_test4_func_noparam");
  ok_sql(hstmt, "drop procedure if exists procedure_columns_test5");


  return OK;
}


DECLARE_TEST(t_bug57182)
{
  SQLLEN nRowCount;
  SQLCHAR buff[24];

  ok_sql(hstmt, "drop procedure if exists bug57182");
  ok_sql(hstmt, "CREATE DEFINER=`adb`@`%` PROCEDURE `bug57182`(in id int, in name varchar(20)) "
    "BEGIN"
    "  insert into simp values (id, name);"
    "END");

  ok_sql(hstmt, "select SPECIFIC_NAME, GROUP_CONCAT(IF(ISNULL(PARAMETER_NAME), concat('RETURN_VALUE ', DTD_IDENTIFIER), concat(PARAMETER_MODE, ' ', PARAMETER_NAME, ' ', DTD_IDENTIFIER)) ORDER BY ORDINAL_POSITION ASC SEPARATOR ', ') PARAMS_LIST, SPECIFIC_SCHEMA, ROUTINE_TYPE FROM information_schema.parameters WHERE SPECIFIC_SCHEMA = 'test' AND SPECIFIC_NAME = 'bug57182' GROUP BY SPECIFIC_NAME, SPECIFIC_SCHEMA, ROUTINE_TYPE");

  my_print_non_format_result(hstmt);

  ok_stmt(hstmt, SQLProcedureColumns(hstmt, "test", SQL_NTS, NULL, 0,
    "bug57182", SQL_NTS,
    NULL, 0));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(2, nRowCount);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, buff, 3), "bug57182", 9);
  is_str(my_fetch_str(hstmt, buff, 4), "id", 3);
  is_str(my_fetch_str(hstmt, buff, 7), "int", 4);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, buff, 3), "bug57182", 9);
  is_str(my_fetch_str(hstmt, buff, 4), "name", 5);
  is_str(my_fetch_str(hstmt, buff, 7), "varchar", 8);
  is_num(my_fetch_int(hstmt, 8), 20);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Almost the same thing but with column specified */
  ok_stmt(hstmt, SQLProcedureColumns(hstmt, "test", SQL_NTS, NULL, 0,
    "bug57182", SQL_NTS,
    "id", SQL_NTS));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(1, nRowCount);

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, buff, 3), "bug57182", 9);
  is_str(my_fetch_str(hstmt, buff, 4), "id", 3);
  is_str(my_fetch_str(hstmt, buff, 7), "int", 4);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* And testing impossible column condition - expecting to get no rows */
   ok_stmt(hstmt, SQLProcedureColumns(hstmt, "test", SQL_NTS, NULL, 0,
    "bug57182", SQL_NTS,
    "non_existing_column%", SQL_NTS));

  ok_stmt(hstmt, SQLRowCount(hstmt, &nRowCount));
  is_num(0, nRowCount);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "drop procedure if exists bug57182");
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  return OK;
}


/* SQLRowCount() doesn't work with SQLTables and other functions
   Testing of that with SQLTables, SQLColumn is incorporated in other testcases
*/
DECLARE_TEST(t_bug55870)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLLEN  rowCount;
  SQLCHAR query[256];

  if (strcmp(myserver, "localhost"))
    return OK;

  ok_sql(hstmt, "drop table if exists bug55870r");
  ok_sql(hstmt, "drop table if exists bug55870_2");
  ok_sql(hstmt, "drop table if exists bug55870");
  ok_sql(hstmt, "create table bug55870(a int not null primary key, "
    "b varchar(20) not null, c varchar(100) not null, INDEX(b)) ENGINE=InnoDB");

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL,
                                        NULL, "", ""));


  sprintf(query, "grant Insert, Select on bug55870 to '%s'@'localhost'", myuid);
  ok_stmt(hstmt, SQLExecDirect(hstmt, query, SQL_NTS));
  sprintf(query, "grant Insert (c), Select (c), Update (c) on bug55870 to '%s'@'localhost'", myuid);
  ok_stmt(hstmt, SQLExecDirect(hstmt, query, SQL_NTS));

  ok_stmt(hstmt1, SQLStatistics(hstmt1, NULL, 0, NULL, 0,
                                   "bug55870", SQL_NTS,
                                   SQL_INDEX_UNIQUE, SQL_QUICK));
  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rowCount));
  is_num(rowCount, 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLTablePrivileges(hstmt1, "test", SQL_NTS, 0, 0, "bug55870",
                                    SQL_NTS));

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rowCount));
  is_num(rowCount, my_print_non_format_result(hstmt1));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLColumnPrivileges(hstmt1, "test", SQL_NTS, 0, 0, "bug55870",
                                      SQL_NTS, "c", SQL_NTS));

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rowCount));
  is_num(rowCount, my_print_non_format_result(hstmt1));

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt, "create table bug55870_2 (id int not null primary key, value "
                "varchar(255) not null) ENGINE=InnoDB");
  ok_sql(hstmt, "create table bug55870r (id int unsigned not null primary key,"
                "refid int not null, refid2 int not null,"
                "somevalue varchar(20) not null,  foreign key b55870fk1 (refid) "
                "references bug55870 (a), foreign key b55870fk2 (refid2) "
                "references bug55870_2 (id)) ENGINE=InnoDB");

  /* actually... looks like no-i_s version of SQLForeignKeys is broken on latest
     server versions. comment in "show table status..." contains nothing */
  ok_stmt(hstmt1, SQLForeignKeys(hstmt1, NULL, 0, NULL, 0, NULL, 0, NULL, 0,
    NULL, 0, (SQLCHAR *)"bug55870r", SQL_NTS));

  ok_stmt(hstmt1, SQLRowCount(hstmt1, &rowCount));
  is_num(rowCount, my_print_non_format_result(hstmt1));

  /** surprise-surprise - just removing table is not enough to remove related
      records from tables_priv and columns_priv
  */
  sprintf(query, "revoke select,insert on bug55870 from '%s'@'localhost'", myuid);
  ok_stmt(hstmt, SQLExecDirect(hstmt, query, SQL_NTS));

  sprintf(query, "revoke select (c),insert (c),update (c) on bug55870 from '%s'@'localhost'", myuid);
  ok_stmt(hstmt, SQLExecDirect(hstmt, query, SQL_NTS));


  ok_sql(hstmt, "drop table if exists bug55870r");
  ok_sql(hstmt, "drop table if exists bug55870_2");
  ok_sql(hstmt, "drop table if exists bug55870");

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/**
 Bug#31067 test. Testing only part of the patch, as the report itself
 is about Access and can't be tested automatically. The test checks
 if SQLColumns returns correct default value if table's catalog specified.
*/
DECLARE_TEST(t_bug31067)
{
  SQLCHAR    buff[512];
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP DATABASE IF EXISTS bug31067");
  ok_sql(hstmt, "CREATE DATABASE bug31067");
  ok_sql(hstmt, "CREATE TABLE bug31067.a (a varchar(10) not null default 'bug31067')");

  for (int i = 0; i < 2; ++i)
  {
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    /* Get the info from just one table.  */
    ok_stmt(hstmt1, SQLColumns(hstmt1, (SQLCHAR *)"bug31067", SQL_NTS, NULL, SQL_NTS,
                               (SQLCHAR *)"a", SQL_NTS, NULL, 0));

    ok_stmt(hstmt1, SQLFetch(hstmt1));

    const char *c = my_fetch_str(hstmt1, buff, 13);
    is_str(c, "'bug31067'", 10);

    expect_stmt(hstmt1, SQLFetch(hstmt1), SQL_NO_DATA_FOUND);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  ok_sql(hstmt, "DROP DATABASE bug31067");

  return OK;
}


/* Some catalog functions can return only one row of result if previous prepare
   statement pre-execution has failed */
DECLARE_TEST(bug12824839)
{
  SQLLEN      row_count;
  SQLSMALLINT col_count;
  SQLRETURN rc;
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE IF EXISTS b12824839");
  ok_sql(hstmt, "DROP TABLE IF EXISTS b12824839a");
  ok_sql(hstmt, "CREATE TABLE b12824839 "
                "(id int primary key, vc_col varchar(32), int_col int)");
  ok_sql(hstmt, "CREATE TABLE b12824839a "
                "(id int, vc_col varchar(32) UNIQUE, int_col int,"
                "primary key(id,int_col))");

  for (int i = 0; i < 2; ++i)
  {
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    rc = SQLPrepare(hstmt1, "SELECT * from any_non_existing_table", SQL_NTS);

    if (i == 0)
      is_num(SQL_ERROR, rc); // This is true onfly when SSPS are used.

    /* this will fail */
    expect_stmt(hstmt1, SQLNumResultCols(hstmt1, &col_count), SQL_ERROR);

    ok_stmt(hstmt1, SQLColumns(hstmt1, "test", SQL_NTS, NULL, 0, "b12824839",
                              SQL_NTS, NULL, 0));

    ok_stmt(hstmt1, SQLRowCount(hstmt1, &row_count));
    is_num(3, row_count);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    ok_stmt(hstmt1, SQLPrimaryKeys(hstmt1, NULL, SQL_NTS, NULL, SQL_NTS,
                                  "b12824839a", SQL_NTS));

    ok_stmt(hstmt1, SQLRowCount(hstmt1, &row_count));
    is_num(2, row_count);

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

    /* SQLColumns was not completely fixed */
    ok_stmt(hstmt1, SQLColumns(hstmt1, "test", SQL_NTS, NULL, 0, NULL,
                              SQL_NTS, NULL, 0));
    ok_stmt(hstmt1, SQLRowCount(hstmt1, &row_count));

    /* There should be records at least for those 2 tables we've created */
    is(row_count >= 6);
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "DROP TABLE IF EXISTS b12824839");
  ok_sql(hstmt, "DROP TABLE IF EXISTS b12824839a");

  return OK;
}


/* If no default database is selected for the connection, call of SQLColumns
   will cause error "Unknown database 'null'" */
DECLARE_TEST(sqlcolumns_nodbselected)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR conn_in[512];
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  /* Just to make sure we have at least one table in our test db */
  ok_sql(hstmt, "DROP TABLE IF EXISTS sqlcolumns_nodbselected");
  ok_sql(hstmt, "CREATE TABLE sqlcolumns_nodbselected (id int)");

  for (int i = 0; i < 2; ++i)
  {
    is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      USE_DRIVER, NULL, NULL, "", conn_opt[i]));

    ok_con(hdbc1, SQLGetInfo(hdbc1, SQL_DATABASE_NAME,
              (SQLPOINTER) conn_in, sizeof(conn_in), NULL));
    is_str("null", conn_in, 5);

    ok_stmt(hstmt1, SQLColumns(hstmt1, mydb, SQL_NTS, NULL, 0, NULL,
                              0, NULL, 0));

    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  ok_sql(hstmt, "DROP TABLE IF EXISTS sqlcolumns_nodbselected");

  return OK;
}


/**
 Bug#14085211 test. LONG TABLE NAMES CRASH OBDC DRIVER
 We will try creating databases, tables and columns with the
 maximum allowed length of 64 symbols and also try to give
 the driver very long (>1024 symbols) names to make it crash.
*/
DECLARE_TEST(t_bug14085211_part1)
{
  SQLCHAR  buff[8192];
  SQLCHAR  db_64_name[65]  = "database_64_symbols_long_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
  SQLCHAR  tab_64_name[65] = "table____64_symbols_long_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
  SQLCHAR  col_64_name[65] = "column___64_symbols_long_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

  SQLCHAR  tab_1024_name[1025] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"\
                             "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";

  sprintf(buff, "DROP DATABASE IF EXISTS %s", db_64_name);
  ok_stmt(hstmt, SQLExecDirect(hstmt, buff, SQL_NTS));

  sprintf(buff, "CREATE DATABASE %s", db_64_name);
  ok_stmt(hstmt, SQLExecDirect(hstmt, buff, SQL_NTS));

  sprintf(buff, "CREATE TABLE %s.%s(%s varchar(10))", db_64_name, tab_64_name, col_64_name);
  ok_stmt(hstmt, SQLExecDirect(hstmt, buff, SQL_NTS));

  /* Lets check if SQLTables can get these long names  */
  ok_stmt(hstmt, SQLTables(hstmt, (SQLCHAR *)db_64_name, SQL_NTS, NULL, SQL_NTS,
                                  (SQLCHAR *)tab_64_name, SQL_NTS,
                                  "TABLE,VIEW", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  /* check the database name */
  is_str(my_fetch_str(hstmt, buff, 1), db_64_name, 64);

  /* check the table name */
  is_str(my_fetch_str(hstmt, buff, 3), tab_64_name, 64);

  /* only one db/table match, so nothing should be in the results */
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Lets check if SQLTables can ignore 1024-characters for table name */
  expect_stmt(hstmt, SQLTables(hstmt, (SQLCHAR *)tab_1024_name, SQL_NTS, NULL, SQL_NTS,
                                  (SQLCHAR *)tab_1024_name, SQL_NTS,
                                  "TABLE,VIEW", SQL_NTS), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "HY090"), OK);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  sprintf(buff, "DROP DATABASE IF EXISTS %s", db_64_name);
  ok_stmt(hstmt, SQLExecDirect(hstmt, buff, SQL_NTS));

  return OK;
}


DECLARE_TEST(t_bug14085211_part2)
{
  /*
    TODO: test all catalog functions for extreme lengths of
          database, table and column names
  */
  return OK;
}


/* Bug#14338051 SQLCOLUMNS WORKS INCORRECTLY IF CALLED AFTER A STATEMENT
                RETURNING RESULT
   I expect that some other catalog function can be vulnerable, too */
DECLARE_TEST(t_sqlcolumns_after_select)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE if exists b14338051");
  ok_sql(hstmt, "CREATE TABLE b14338051("
    "blob_field BLOB, text_field TEXT )");
  ok_sql(hstmt, "insert into b14338051 "
    "set blob_field= 'blob', text_field= 'text'; ");

  for (int i = 0; i < 2; ++i)
  {
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    ok_sql(hstmt1, "SELECT 'blob', 'text'");

    while (SQL_SUCCEEDED(SQLFetch(hstmt1)))
    { }

    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0,
                            (SQLCHAR *)"b14338051",
                            (SQLSMALLINT)strlen("b14338051"), NULL, 0));

    is_num(myresult(hstmt1), 2);
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }
  return OK;
}


/* Bug #14555713 USING ADO, ODBC DRIVER RETURNS WRONG TYPE AND VALUE FOR BIT(>1)
                 FIELD.
   Parameters datatypes returned for SP bit(n) parameters are inconsistent with
   those retruned for corresponding column types.
 */
DECLARE_TEST(t_bug14555713)
{
  ok_sql(hstmt, "drop procedure if exists b14555713");

  ok_sql(hstmt, "create procedure b14555713(OUT p1 bit(1), OUT p2 bit(9)) \
                begin\
                 set p1= 1;\
                 set p2= b'100100001';\
                end");

  ok_stmt(hstmt, SQLProcedureColumns(hstmt, NULL, 0, NULL, 0,
                                     "b14555713", SQL_NTS,
                                     "p%", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 6), SQL_BIT);
  is_num(my_fetch_int(hstmt, 8), 1);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 8), 2);
  is_num(my_fetch_int(hstmt, 6), SQL_BINARY);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "drop procedure if exists b14555713");
  return OK;
}


/*
  Bug #69448 MySQL ODBC drivers incorrectly quotes TableName when
  calling SQLPrimaryKeys
*/
DECLARE_TEST(t_bug69448)
{

  SQLCHAR buff[255];

  ok_sql(hstmt, "DROP TABLE IF EXISTS `table``69448`");

  ok_sql(hstmt, "CREATE TABLE `table``69448`(id int primary key)");

  ok_stmt(hstmt, SQLPrimaryKeys(hstmt, NULL, SQL_NTS, NULL, SQL_NTS,
                               "table`69448", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));

  /* check the table name and PK name */
  is_str(my_fetch_str(hstmt, buff, 3), "table`69448", 11);
  is_str(my_fetch_str(hstmt, buff, 4), "id", 2);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS `table``69448`");

  /* free the statement cursor */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  return OK;
}


/*
  Bug #69554: SQL_ATTR_MAX_ROWS applies to all result sets on the statement,
  and not connection, which is currently there as
  'set @@sql_select_limit' for whole session.
*/
DECLARE_TEST(t_bug69554)
{
  int num_rows= 0;
  SQLHSTMT hstmt1;

  ok_con(hdbc, SQLAllocStmt(hdbc, &hstmt1));
  ok_sql(hstmt, "DROP TABLE IF EXISTS table69554a");
  ok_sql(hstmt, "DROP TABLE IF EXISTS table69554b");
  ok_sql(hstmt, "DROP TABLE IF EXISTS table69554c");
  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS proc69554a");
  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS proc69554b");

  ok_sql(hstmt, "CREATE TABLE table69554a(id int, id2 int, id3 int, PRIMARY KEY(id, id2))");
  ok_sql(hstmt, "GRANT SELECT, UPDATE, DELETE on table69554a to 'root'@'localhost'");
  ok_sql(hstmt, "GRANT INSERT (id, id2, id3) on table69554a to 'root'@'localhost'");
  ok_sql(hstmt, "CREATE TABLE table69554b(id int, id2 int, id3 int)");
  ok_sql(hstmt, "CREATE TABLE table69554c(id int, id2 int, id3 int)");

  ok_sql(hstmt, "CREATE PROCEDURE proc69554a(IN p1 INT, IN p2 INT) begin end;");
  ok_sql(hstmt, "CREATE PROCEDURE proc69554b(IN p1 INT, IN p2 INT) begin end;");

  ok_sql(hstmt, "INSERT INTO table69554a(id, id2) VALUES (1, 1), (2, 2), (3, 3)");

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_MAX_ROWS,
                                (SQLPOINTER)1, 0));

  ok_sql(hstmt, "SELECT * FROM table69554a");
  is_num(my_print_non_format_result(hstmt), 1);
  ok_stmt(hstmt1, SQLPrimaryKeys(hstmt1, NULL, 0, NULL, 0, "table69554a", SQL_NTS));
  is_num(my_print_non_format_result(hstmt1), 2);

  ok_sql(hstmt, "SELECT * FROM table69554a");
  is_num(my_print_non_format_result(hstmt), 1);
  ok_stmt(hstmt1, SQLTables(hstmt1, NULL, 0, NULL, 0,
                           "table69554%", SQL_NTS, NULL, 0));
  is_num(my_print_non_format_result(hstmt1), 3);

  ok_sql(hstmt, "SELECT * FROM table69554a");
  is_num(my_print_non_format_result(hstmt), 1);
  ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0, "table69554a", SQL_NTS, NULL, 0));
  is_num(my_print_non_format_result(hstmt1), 3);

  ok_sql(hstmt, "SELECT * FROM table69554a");
  is_num(my_print_non_format_result(hstmt), 1);
  ok_stmt(hstmt1, SQLColumnPrivileges(hstmt1, NULL, 0, NULL, 0, "table69554a", SQL_NTS, "%", SQL_NTS));
  is(my_print_non_format_result(hstmt1) > 1);

  ok_sql(hstmt, "SELECT * FROM table69554a");
  is_num(my_print_non_format_result(hstmt), 1);
  ok_stmt(hstmt1, SQLTablePrivileges(hstmt1, NULL, 0, NULL, 0, "table69554a", SQL_NTS));
  is(my_print_non_format_result(hstmt1) > 1);

  ok_sql(hstmt, "SELECT * FROM table69554a");
  is_num(my_print_non_format_result(hstmt), 1);
  ok_stmt(hstmt1, SQLProcedures(hstmt1, NULL, 0, NULL, 0, "proc69554%", SQL_NTS));
  is_num(my_print_non_format_result(hstmt1), 2);

  ok_sql(hstmt, "SELECT * FROM table69554a");
  is_num(my_print_non_format_result(hstmt), 1);
  ok_stmt(hstmt1, SQLProcedureColumns(hstmt1, NULL, 0, NULL, 0, "proc69554a", SQL_NTS, "%", SQL_NTS));
  is_num(my_print_non_format_result(hstmt1), 2);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
  ok_sql(hstmt1, "DROP TABLE IF EXISTS table69554a");
  ok_sql(hstmt1, "DROP TABLE IF EXISTS table69554b");
  ok_sql(hstmt1, "DROP TABLE IF EXISTS table69554c");
  ok_sql(hstmt1, "DROP PROCEDURE IF EXISTS proc69554a");
  ok_sql(hstmt1, "DROP PROCEDURE IF EXISTS proc69554b");

  return OK;
}


/**
  Bug#14005343: SQLTABLES PATCH (BUG 13914518) HAS TO BE EXTENDED
  FOR NO_I_S CASE
  Verify database returned should not be '%' for SQL_ALL_CATALOGS input.
  Also verify total tables in all databases in case of no information schema
  and in case of information schema should match.
*/
DECLARE_TEST(t_bug_14005343)
{
  SQLCHAR    database[100];
  SQLINTEGER nrows= 0;

  /* Test for all catalogs (SQL_ALL_CATALOGS) with table type table and view */
  ok_stmt(hstmt, SQLTables(hstmt, SQL_ALL_CATALOGS, 1, 0, 0, 0, 0, "TABLE,VIEW", SQL_NTS));
  while (SQLFetch(hstmt) == SQL_SUCCESS)
  {
    ++nrows;
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, database,
                             sizeof(database), NULL));
    printf("%s\n", database);
    /* the table catalog in the results must not be '%' */
    is(database[0] != '%');
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "SELECT TABLE_NAME, TABLE_COMMENT, TABLE_TYPE, TABLE_SCHEMA "
                " FROM (SELECT *  FROM INFORMATION_SCHEMA.TABLES )TABLES "
  	      		  "	WHERE TABLE_TYPE='BASE TABLE' OR TABLE_TYPE='VIEW' ");
  is_num(myrowcount(hstmt), nrows);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));


  /* Test for all catalogs (%) with no table type */
  nrows= 0;
  ok_stmt(hstmt, SQLTables(hstmt, (SQLCHAR*) "%", SQL_NTS, NULL, 0, NULL, 0, NULL,0));
  while (SQLFetch(hstmt) == SQL_SUCCESS)
  {
    ++nrows;
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, database,
                             sizeof(database), NULL));
    /* the table catalog in the results must not be '%' */
    is(database[0] != '%');
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "SELECT TABLE_NAME, TABLE_COMMENT, TABLE_TYPE, TABLE_SCHEMA "
                " FROM (SELECT *  FROM INFORMATION_SCHEMA.TABLES ) TABLES "
				        " WHERE TABLE_TYPE='BASE TABLE' OR TABLE_TYPE='VIEW' ");
  is_num(myrowcount(hstmt), nrows);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Test for no catalog (NULL) should return all tables in current database */
  nrows= 0;
	ok_stmt(hstmt, SQLTables(hstmt, NULL, 0, NULL, 0, NULL, 0, NULL,0));
  while (SQLFetch(hstmt) == SQL_SUCCESS)
  {
    ++nrows;
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, database,
                             sizeof(database), NULL));
    /* the table catalog in the results must not be '%' */
    is(database[0] != '%');
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "SELECT TABLE_NAME, TABLE_COMMENT, TABLE_TYPE, TABLE_SCHEMA "
                " FROM (SELECT *  FROM INFORMATION_SCHEMA.TABLES ) TABLES "
                " WHERE TABLE_SCHEMA = DATABASE() AND "
 				        " (TABLE_TYPE='BASE TABLE' OR TABLE_TYPE='VIEW')");
  is_num(myrowcount(hstmt), nrows);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));


  /* Test for all tables in test catalog */
  nrows= 0;
  ok_stmt(hstmt, SQLTables(hstmt, "test", SQL_NTS, NULL, 0, NULL, 0, NULL, 0));
  while (SQLFetch(hstmt) == SQL_SUCCESS)
  {
    ++nrows;
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, database,
                             sizeof(database), NULL));
    /* the table catalog in the results must not be '%' */
    is(database[0] != '%');
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "SELECT TABLE_NAME, TABLE_COMMENT, TABLE_TYPE, TABLE_SCHEMA "
                " FROM (SELECT *  FROM INFORMATION_SCHEMA.TABLES ) TABLES "
                " WHERE TABLE_SCHEMA = 'test' AND (TABLE_TYPE='BASE TABLE' "
                " OR TABLE_TYPE='VIEW')");
  is_num(myrowcount(hstmt), nrows);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Test for all catalogs (SQL_ALL_CATALOGS) with table type table */
  nrows= 0;
  ok_stmt(hstmt, SQLTables(hstmt, SQL_ALL_CATALOGS, 1, 0, 0, 0, 0, "TABLE", SQL_NTS));
  while (SQLFetch(hstmt) == SQL_SUCCESS)
  {
    ++nrows;
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, database,
                             sizeof(database), NULL));
    /* the table catalog in the results must not be '%' */
    is(database[0] != '%');
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "SELECT TABLE_NAME, TABLE_COMMENT, TABLE_TYPE, TABLE_SCHEMA "
                " FROM (SELECT *  FROM INFORMATION_SCHEMA.TABLES ) TABLES "
                " WHERE TABLE_TYPE='BASE TABLE' ");
  is_num(myrowcount(hstmt), nrows);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}

/*
  SQLPROCEDURECOLUMNS FUNCTION RETURNS INCOMPLETE RESULTS
*/
DECLARE_TEST(t_bug32504915)
{
  char *create_query = malloc(32000);
  int i, col_num;
  int max_cols = 50;

  create_query[0] = '\0';

  ok_sql(hstmt, "drop procedure if exists t_bug32504915");
  strcat(create_query, "create procedure t_bug32504915(");

  for(i = 0; i < max_cols; ++i)
  {
    char snum[10];
    if (i)
      strcat(create_query, ",");

    strcat(create_query, "in input_parameter_0000000");
    snprintf(snum, sizeof(snum), "%d", i);
    strcat(create_query, snum);
    strcat(create_query, " varchar(45)");
  }
  strcat(create_query, ")begin end;");
  ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)create_query, SQL_NTS));

  ok_stmt(hstmt, SQLProcedureColumns(hstmt, NULL, 0, NULL, 0,
                                     "t_bug32504915", SQL_NTS,
                                     "%", SQL_NTS));

  col_num = my_print_non_format_result(hstmt);
  is_num(50, col_num);

  free(create_query);
  return OK;
}

/*
  SQLColumns() reports wrong type name with column length
*/
DECLARE_TEST(t_bug33599093)
{
  char *type_list[] =
  {
    "char", "varchar", "decimal", "bit", "binary", "varbinary", "tinyint"
  };
  char* conn_opt[] = { "NO_SSPS=0", "NO_SSPS=1" };

  ok_sql(hstmt, "DROP TABLE IF EXISTS tab33599093");
  ok_sql(hstmt, "CREATE TABLE tab33599093 (c1 char(16), c2 varchar(32),"
                "c3 decimal(5,3), c4 bit(32), c5 binary(8), c6 varbinary(16),"
                "c7 tinyint unsigned)");

  for (int i = 0; i < 2; ++i)
  {
    int idx = 0;
    DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
    alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1,
      NULL, NULL, NULL, NULL, conn_opt[i]);

    ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, SQL_NTS, NULL, SQL_NTS,
      (SQLCHAR *)"tab33599093", SQL_NTS, NULL, 0));

    while(SQL_SUCCESS == SQLFetch(hstmt1))
    {
      SQLCHAR type_name[32] = { 0 };

      const char *c1 = my_fetch_str(hstmt1, type_name, 6);
      is_str(c1, type_list[idx], SQL_NTS);
      is_num(strlen(type_name), strlen(type_list[idx]));
      ++idx;
    }
    ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));
    free_basic_handles(&henv1, &hdbc1, &hstmt1);
  }

  ok_sql(hstmt, "DROP TABLE IF EXISTS tab33599093");
  return OK;
}


/*
  Bug #33788407 -  Unable to add mysql views to ms access.
  (Cannot define Field more than once)
*/

DECLARE_TEST(t_bug33788407)
{
  ok_sql(hstmt, "DROP DATABASE IF EXISTS db_33788407_01");
  ok_sql(hstmt, "DROP DATABASE IF EXISTS db_33788407_02");

  ok_sql(hstmt, "CREATE DATABASE db_33788407_01");
  ok_sql(hstmt, "CREATE DATABASE db_33788407_02");

  ok_sql(hstmt, "CREATE TABLE db_33788407_01.test_table_a (id INT)");
  ok_sql(hstmt, "CREATE TABLE db_33788407_02.test_table_a (id INT)");

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, "db_33788407_02", NULL));

  ok_stmt(hstmt1, SQLColumns(hstmt1, NULL, 0, NULL, 0, "test_table_a", SQL_NTS,
    NULL, 0));

  // The result should have one and only one row.
  is(1 == my_print_non_format_result(hstmt1));

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_bug33788407)
  ADD_TEST(t_bug32504915)
  ADD_TEST(t_sqlprocedurecolumns)
  ADD_TEST(t_bug57182)
  ADD_TEST(t_bug_14005343)
  ADD_TEST(t_bug69554)
  ADD_TEST(t_bug37621)
  ADD_TEST(t_bug34272)
  ADD_TEST(t_bug49660)
  ADD_TEST(t_bug36441)
  ADD_TEST(t_bug53235)
  ADD_TEST(t_bug50195)
  // ADD_TEST(t_bug51422) Fix NO_IS when FK table is given, it should only
  //                      return keys that are PRIMARY, not UNIQUE
  ADD_TEST(t_bug55870)
  ADD_TEST(t_bug31067)
  ADD_TEST(bug12824839)
  ADD_TEST(sqlcolumns_nodbselected)
  ADD_TEST(t_bug14085211_part1)
  ADD_TODO(t_bug14085211_part2)
  ADD_TEST(t_sqlcolumns_after_select)
  ADD_TEST(t_bug14555713)
  ADD_TEST(t_bug69448)
  ADD_TEST(t_bug33599093)
END_TESTS

RUN_TESTS
