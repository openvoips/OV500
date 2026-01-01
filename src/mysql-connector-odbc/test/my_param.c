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

typedef struct {
  long            length;
  unsigned char data[4096];
} T_LOB;

DECLARE_TEST(my_param_data)
{
  int   i, rcnt, c1 = 1, c2 = 4096;
  T_LOB c3, *pt;
  c3.length = c2;
  SQLRETURN rc = 0;
  SQLLEN c1_len_or_ind = 0, c2_len_or_ind = 0, c3_len_or_ind = 0;

  unsigned char *org_data[10];
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  /* Connect with SSPS enabled */
  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
    NULL, "NO_SSPS=1");

  SQLExecDirect(hstmt, "DROP TABLE IF EXISTS test_param_data", SQL_NTS);

  ok_sql(hstmt, "CREATE TABLE test_param_data"\
                "(c1 int, c2 int, c3 longblob)");

  for (c1 = 1; c1 < 11; ++c1)
  {
    c3.length = (c1 % 2) ? 944 : 3312;
    org_data[c1 - 1] = calloc(c3.length + 100, 1);

    SQLLEN indic1 = sizeof(long);
    SQLLEN indic2 = sizeof(long);
    SQLLEN indic3 = SQL_LEN_DATA_AT_EXEC(c3.length);

    for (i = 0; i < c3.length; i++)
    {
      org_data[c1 - 1][i] = c3.data[i] = i % 256;
    }

    ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT,
            SQL_C_LONG, SQL_INTEGER, sizeof(long), 0, &c1,
            sizeof(long), &indic1));

    ok_stmt(hstmt1, SQLBindParameter(hstmt1, 2, SQL_PARAM_INPUT,
            SQL_C_LONG, SQL_INTEGER, sizeof(long), 0, &c2,
            sizeof(long), &indic2));

    ok_stmt(hstmt1, SQLBindParameter(hstmt1, 3, SQL_PARAM_INPUT,
            SQL_C_BINARY, SQL_LONGVARBINARY, sizeof(T_LOB), 0, &c3,
            sizeof(T_LOB), &indic3));

    expect_stmt(hstmt1, SQLExecDirect(hstmt1, "INSERT INTO test_param_data(c1, c2, c3)"\
                "VALUES (?, ?, ?)", SQL_NTS),
                SQL_NEED_DATA);


    expect_stmt(hstmt1, SQLParamData(hstmt1, (SQLPOINTER)&pt),
                SQL_NEED_DATA);

    ok_stmt(hstmt1, SQLPutData(hstmt1, pt->data, pt->length));

    ok_stmt(hstmt1, SQLParamData(hstmt1, (SQLPOINTER)&pt));

  }

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_RESET_PARAMS));
  ok_sql(hstmt1, "SELECT * FROM test_param_data ORDER BY c1");

  ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_ULONG, &c1, sizeof(c1), &c1_len_or_ind));
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 2, SQL_C_ULONG, &c2, sizeof(c2), &c2_len_or_ind));
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 3, SQL_C_BINARY, c3.data, 4000, &c3_len_or_ind));

  rcnt = 1;
  while (1)
  {
    int expected_length = (rcnt % 2) ? 944 : 3312;
    memset(c3.data, 0, 4000);
    c1 = 0;
    c2 = 0;
    if (SQLFetch(hstmt1) != SQL_SUCCESS)
      break;
    is_num(c1, rcnt);
    is_num(c2, 4096);
    is_num(c3_len_or_ind, expected_length);
    is(memcmp(c3.data, org_data[rcnt-1], expected_length) == 0);
    ++rcnt;
  }

  is_num(rcnt, 11);

  for(i = 0; i < 10; ++i)
    free(org_data[i]);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}

DECLARE_TEST(t_bug31373948)
{
  char *buf;
  char foo[16] = "foo ";
  char *ins_query = "INSERT INTO t_bug31373948 (name, largedata) VALUES (?,?)";
  char *sel_query = "SELECT id, largedata FROM t_bug31373948 WHERE name LIKE ? "\
                    "ORDER BY id";
  SQLUINTEGER id = 0;
  unsigned long rnum = 0;
  SQLLEN idLenOrInd = 0;
  SQLLEN largeLenOrInd = 0;
  SQLRETURN rcc = 0;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug31373948");
  ok_sql(hstmt, "CREATE TABLE t_bug31373948 (id int primary key auto_increment,"\
                "name VARCHAR(45), largedata VARCHAR(16000))");
  buf = malloc(100*1000);

  ok_stmt(hstmt, SQLPrepare(hstmt, ins_query, SQL_NTS));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR, 0,
                                  0, foo, 5, NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR, 0,
                                  0, buf, 16000, NULL));

  for (char i = 'A'; i < 'Q'; ++i)
    memset(buf + (i - 'A') * 1000, i, 1000);

  for (char c = '0'; c <= '9'; ++c)
  {
    buf[0] = c;
    foo[3] = c;
    ok_stmt(hstmt, SQLExecute(hstmt));
  }
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  ok_stmt(hstmt, SQLPrepare(hstmt, sel_query, SQL_NTS));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR, 0,
    0, foo, 5, NULL));
  foo[3] = '%';
  ok_stmt(hstmt, SQLExecute(hstmt));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_ULONG, &id, sizeof(id), &idLenOrInd));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, buf, 17000, &largeLenOrInd));

  memset(buf, 0, 16000);

  while (SQLFetch(hstmt) == SQL_SUCCESS)
  {

    printMessage("id: %d, Len 1: %ld, Len 2: %ld", id, idLenOrInd, largeLenOrInd);
    is(id == (rnum + 1));

    idLenOrInd = 0;
    is(largeLenOrInd == 16000);
    largeLenOrInd = 0;

    is(buf[0] == '0' + rnum);

    for (unsigned i = 1; i < 16000; ++i)
    {
      is (buf[i] == 'A' + i / 1000);
    }

    ++rnum;
    memset(buf, 0, 16000);
  }
  is(rnum == 10);
  free(buf);
  return OK;
}

DECLARE_TEST(t_bug30428851)
{
  SQLRETURN rc = 0;
#define NROW 2
  SQLINTEGER par = NROW;
  int i = 0;
  SQLLEN    len = 0, offset = 0;
  typedef struct data_fetch
  {
    SQLINTEGER id;
    SQLCHAR strVal[32];
    SQLINTEGER intVal;
    SQLLEN     len1;
    SQLLEN     len2;
    SQLLEN     len3;
  }df;

  df data;
  df src[NROW] = {{1, "Test1", 10, 4, 5, 4},
                  {2, "Test2", 20, 4, 5, 4}};

  memset(&data, 0, sizeof(data));
  ok_sql(hstmt, "DROP TABLE IF EXISTS bug30428851");

  ok_sql(hstmt, "CREATE TABLE bug30428851( " \
    "`id` int(11) NOT NULL AUTO_INCREMENT, " \
    "`strVal` varchar(45) DEFAULT NULL, " \
    "`intVal` int(11) DEFAULT NULL, " \
    "`txtVal` longtext, " \
    " PRIMARY KEY(`id`))");

  for (i = 0; i < NROW; ++i)
  {
    char buf[512] = {0};
    snprintf(buf, 512, "INSERT INTO bug30428851 (id, strVal, intVal) VALUES (%d, '%s', %d)",
    src[i].id, src[i].strVal, src[i].intVal);
    ok_stmt(hstmt, SQLExecDirect(hstmt, buf, SQL_NTS));
  }

  ok_stmt(hstmt, SQLPrepare(hstmt,
    "SELECT * FROM `bug30428851` WHERE id = ? LIMIT 1;", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                                  SQL_C_SLONG, SQL_INTEGER, 10, 0, &par,
                                  0, &len));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_ARRAY_SIZE,
                                (SQLPOINTER)1, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_TYPE,
                                (SQLPOINTER)sizeof(df), 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_BIND_OFFSET_PTR,
                                (SQLPOINTER)&offset, 0));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG,
                            (SQLPOINTER)&data.id,
                            sizeof(SQLINTEGER),
                            (SQLLEN*)&data.len1));

  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR,
                            (SQLPOINTER)data.strVal,
                            sizeof(data.strVal),
                            (SQLLEN*)&data.len2));

  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_LONG,
                            (SQLPOINTER)&data.intVal,
                            sizeof(SQLINTEGER),
                            (SQLLEN*)&data.len3));

  while(SQLFetch(hstmt) == SQL_SUCCESS);

  printf("ROW # %i: %d %s %d \n", i, data.id, data.strVal, data.intVal);
  is_num(src[NROW-1].id, data.id);
  is_str(src[NROW-1].strVal, data.strVal, src[NROW-1].len2);
  is_num(src[NROW-1].intVal, data.intVal);

  is_num(src[NROW-1].len1, data.len1);
  is_num(src[NROW-1].len2, data.len2);
  is_num(src[NROW-1].len3, data.len3);

  return OK;
}


/********************************************************
* initialize tables                                     *
*********************************************************/
DECLARE_TEST(my_init_table)
{
  SQLRETURN   rc;

  ok_sql(hstmt, "DROP TABLE if exists my_demo_param");

  /* commit the transaction */
  rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
  mycon(hdbc,rc);

  /* create the table 'my_demo_param' */
  ok_sql(hstmt, "CREATE TABLE my_demo_param(\
                            id   int,\
                            auto int primary key auto_increment,\
                            name varchar(20),\
                            timestamp timestamp)");

  return OK;
}


DECLARE_TEST(my_param_insert)
{
  SQLRETURN   rc;
  SQLINTEGER  id;
  char        name[50];

  /* prepare the insert statement with parameters */
  rc = SQLPrepare(hstmt, (SQLCHAR *)"INSERT INTO my_demo_param(id,name) VALUES(?,?)",SQL_NTS);
  mystmt(hstmt,rc);

  /* now supply data to parameter 1 and 2 */
  rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                        SQL_C_LONG, SQL_INTEGER, 0,0,
                        &id, 0, NULL);
  mystmt(hstmt,rc);

  rc = SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT,
                        SQL_C_CHAR, SQL_CHAR, 0,0,
                        name, sizeof(name), NULL);
  mystmt(hstmt,rc);

  /* now insert 10 rows of data */
  for (id = 0; id < 10; id++)
  {
      sprintf(name,"MySQL%d",id);

      rc = SQLExecute(hstmt);
      mystmt(hstmt,rc);
  }

  /* Free statement param resorces */
  rc = SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
  mystmt(hstmt,rc);

  /* Free statement cursor resorces */
  rc = SQLFreeStmt(hstmt, SQL_CLOSE);
  mystmt(hstmt,rc);

  /* commit the transaction */
  rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
  mycon(hdbc,rc);

  /* Now fetch and verify the data */
  ok_sql(hstmt, "SELECT * FROM my_demo_param");

  is(10 == myresult(hstmt));

  return OK;
}


DECLARE_TEST(my_param_update)
{
    SQLRETURN  rc;
    SQLLEN nRowCount;
    SQLINTEGER id=9;
    char name[]="update";

    /* prepare the insert statement with parameters */
    rc = SQLPrepare(hstmt, (SQLCHAR *)"UPDATE my_demo_param set name = ? WHERE id = ?",SQL_NTS);
    mystmt(hstmt,rc);

    /* now supply data to parameter 1 and 2 */
    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                          SQL_C_CHAR, SQL_CHAR, 0,0,
                          name, sizeof(name), NULL);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT,
                          SQL_C_LONG, SQL_INTEGER, 0,0,
                          &id, 0, NULL);
    mystmt(hstmt,rc);

    /* now execute the update statement */
    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);

    /* check the rows affected by the update statement */
    rc = SQLRowCount(hstmt, &nRowCount);
    mystmt(hstmt,rc);
    printMessage("\n total rows updated:%d\n",nRowCount);
    is( nRowCount == 1);

    /* Free statement param resorces */
    rc = SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    mystmt(hstmt,rc);

    /* Free statement cursor resorces */
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* commit the transaction */
    rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);

    /* Now fetch and verify the data */
    ok_sql(hstmt, "SELECT * FROM my_demo_param");
    mystmt(hstmt,rc);

    is(10 == myresult(hstmt));

  return OK;
}


DECLARE_TEST(my_param_delete)
{
    SQLRETURN  rc;
    SQLINTEGER id;
    SQLLEN nRowCount;

    /* supply data to parameter 1 */
    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                          SQL_C_LONG, SQL_INTEGER, 0,0,
                          &id, 0, NULL);
    mystmt(hstmt,rc);

    /* execute the DELETE STATEMENT to delete 5th row  */
    id = 5;
    ok_sql(hstmt,"DELETE FROM my_demo_param WHERE id = ?");

    /* check the rows affected by the update statement */
    rc = SQLRowCount(hstmt, &nRowCount);
    mystmt(hstmt,rc);
    printMessage(" total rows deleted:%d\n",nRowCount);
    is( nRowCount == 1);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    /* execute the DELETE STATEMENT to delete 8th row  */
    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                          SQL_C_LONG, SQL_INTEGER, 0,0,
                          &id, 0, NULL);
    mystmt(hstmt,rc);

    id = 8;
    ok_sql(hstmt,"DELETE FROM my_demo_param WHERE id = ?");

    /* check the rows affected by the update statement */
    rc = SQLRowCount(hstmt, &nRowCount);
    mystmt(hstmt,rc);
    printMessage(" total rows deleted:%d\n",nRowCount);
    is( nRowCount == 1);

    /* Free statement param resorces */
    rc = SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    mystmt(hstmt,rc);

    /* Free statement cursor resorces */
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);

    /* commit the transaction */
    rc = SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);

    /* Now fetch and verify the data */
    ok_sql(hstmt, "SELECT * FROM my_demo_param");

    is(8 == myresult(hstmt));

    /* drop the table */
    ok_sql(hstmt,"DROP TABLE my_demo_param");

    rc = SQLFreeStmt(hstmt,SQL_CLOSE);
    mystmt(hstmt,rc);

  return OK;
}


/*I really wonder what is this test about */
DECLARE_TEST(tmysql_fix)
{
  SQLRETURN rc;
  ok_sql(hstmt, "set session sql_mode=''");

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_err");

  ok_sql(hstmt,"CREATE TABLE tmysql_err (\
                  td date NOT NULL default '0000-00-00',\
                  node varchar(8) NOT NULL default '',\
                  tag varchar(10) NOT NULL default '',\
                  sqlname varchar(8) default NULL,\
                  fix_err varchar(100) default NULL,\
                  sql_err varchar(255) default NULL,\
                  prog_err varchar(100) default NULL\
                ) ENGINE=MyISAM");

  ok_sql(hstmt,"INSERT INTO tmysql_err VALUES\
                  ('0000-00-00','0','0','0','0','0','0'),\
                  ('2001-08-29','FIX','SQLT2','ins1',\
                  NULL,NULL, 'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2',\
                  'ins1',NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2',\
                  'ins1',NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2','ins1',\
                  NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2',\
                  'ins1',NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2',\
                  'ins1',NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2',\
                  'ins1',NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000!-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2',\
                  'ins1',NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.'),\
                  ('0000-00-00','0','0','0','0','0','0'),('2001-08-29','FIX','SQLT2',\
                  'ins1',NULL,NULL,'Error.  SQL cmd %s is not terminated or too long.')");

  /* trace based */
  {
    SQLSMALLINT pcpar,pccol,pfSqlType,pibScale,pfNullable;
    SQLSMALLINT index;
    SQLCHAR     td[30]="20010830163225";
    SQLCHAR     node[30]="FIX";
    SQLCHAR     tag[30]="SQLT2";
    SQLCHAR     sqlname[30]="ins1";
    SQLCHAR     sqlerr[30]="error";
    SQLCHAR     fixerr[30]= "fixerr";
    SQLCHAR     progerr[30]="progerr";
    SQLULEN     pcbParamDef;

    SQLFreeStmt(hstmt,SQL_CLOSE);
    rc = SQLPrepare(hstmt,
      (SQLCHAR *)"insert into tmysql_err (TD, NODE, TAG, SQLNAME, SQL_ERR,"
                 "FIX_ERR, PROG_ERR) values (?, ?, ?, ?, ?, ?, ?)", 103);
    mystmt(hstmt,rc);

    rc = SQLNumParams(hstmt,&pcpar);
    mystmt(hstmt,rc);

    rc = SQLNumResultCols(hstmt,&pccol);
    mystmt(hstmt,rc);

    for (index=1; index <= pcpar; index++)
    {
      rc = SQLDescribeParam(hstmt,index,&pfSqlType,&pcbParamDef,&pibScale,&pfNullable);
      mystmt(hstmt,rc);

      printMessage("descparam[%d]:%d,%d,%d,%d\n",index,pfSqlType,pcbParamDef,pibScale,pfNullable);
    }

    /* TODO: C and SQL types as numeric consts. Splendid.*/
    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,11,12,0,0,td,100,0);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT,1,12,0,0,node,100,0);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt,3,SQL_PARAM_INPUT,1,12,0,0,tag,100,0);
    mystmt(hstmt,rc);
    rc = SQLBindParameter(hstmt,4,SQL_PARAM_INPUT,1,12,0,0,sqlname,100,0);
    mystmt(hstmt,rc);
    rc = SQLBindParameter(hstmt,5,SQL_PARAM_INPUT,1,12,0,0,sqlerr,0,0);
    mystmt(hstmt,rc);
    rc = SQLBindParameter(hstmt,6,SQL_PARAM_INPUT,1,12,0,0,fixerr,0,0);
    mystmt(hstmt,rc);
    rc = SQLBindParameter(hstmt,7,SQL_PARAM_INPUT,1,12,0,0,progerr,0,0);
    mystmt(hstmt,rc);

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);
  }

  ok_sql(hstmt, "DROP TABLE IF EXISTS tmysql_err");

  return OK;
}


/*
  Test basic handling of SQL_ATTR_PARAM_BIND_OFFSET_PTR
*/
DECLARE_TEST(t_param_offset)
{
  const SQLINTEGER rowcnt= 5;
  SQLINTEGER i;
  struct {
    SQLINTEGER id;
    SQLINTEGER x;
  } rows[25];
  size_t row_size= (sizeof(rows) / 25);
  SQLINTEGER out_id, out_x;
  SQLULEN bind_offset= 20 * row_size;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_param_offset");
  ok_sql(hstmt, "CREATE TABLE t_param_offset (id int not null, x int)");

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_BIND_OFFSET_PTR,
                                &bind_offset, 0));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &rows[0].id, 0, NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &rows[0].x, 0, NULL));

  for (i= 0; i < rowcnt; ++i)
  {
    rows[20+i].id= i * 10;
    rows[20+i].x= (i * 1000) % 97;
    ok_sql(hstmt, "insert into t_param_offset values (?,?)");
    bind_offset+= row_size;
  }

  /* verify the data */

  ok_sql(hstmt, "select id, x from t_param_offset order by 1");

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &out_id, 0, NULL));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_LONG, &out_x, 0, NULL));

  for (i= 0; i < rowcnt; ++i)
  {
    ok_stmt(hstmt, SQLFetch(hstmt));
    is_num(out_id, rows[20+i].id);
    is_num(out_id, i * 10);
    is_num(out_x, rows[20+i].x);
    is_num(out_x, (i * 1000) % 97);
  }

  return OK;
}


/*
Bug 48310 - parameters array support request.
Binding by row test
*/
DECLARE_TEST(paramarray_by_row)
{
#define ROWS_TO_INSERT 3
#define STR_FIELD_LENGTH 255
  typedef struct DataBinding
  {
    SQLCHAR     bData[5];
    SQLINTEGER  intField;
    SQLCHAR     strField[STR_FIELD_LENGTH];
    SQLLEN      indBin;
    SQLLEN      indInt;
    SQLLEN      indStr;
  } DATA_BINDING;

   const SQLCHAR *str[]= {"nothing for 1st", "longest string for row 2", "shortest"  };

  SQLCHAR       buff[50];
  DATA_BINDING  dataBinding[ROWS_TO_INSERT];
  SQLUSMALLINT  paramStatusArray[ROWS_TO_INSERT];
  SQLULEN       paramsProcessed, i, nLen;
  SQLLEN        rowsCount;

  ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS t_bug48310", SQL_NTS));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "CREATE TABLE t_bug48310 (id int primary key auto_increment,"\
    "bData binary(5) NULL, intField int not null, strField varchar(255) not null)", SQL_NTS));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_BIND_TYPE, (SQLPOINTER)sizeof(DATA_BINDING), 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)ROWS_TO_INSERT, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_STATUS_PTR, paramStatusArray, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR, &paramsProcessed, 0));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_BINARY, SQL_BINARY,
    0, 0, dataBinding[0].bData, 0, &dataBinding[0].indBin));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER,
    0, 0, &dataBinding[0].intField, 0, &dataBinding[0].indInt));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR,
    0, 0, dataBinding[0].strField, 0, &dataBinding[0].indStr ));

  memcpy(dataBinding[0].bData, "\x01\x80\x00\x80\x00", 5);
  dataBinding[0].intField= 1;

  memcpy(dataBinding[1].bData, "\x02\x80\x00\x80", 4);
  dataBinding[1].intField= 0;

  memcpy(dataBinding[2].bData, "\x03\x80\x00", 3);
  dataBinding[2].intField= 223322;

  for (i= 0; i < ROWS_TO_INSERT; ++i)
  {
    strcpy(dataBinding[i].strField, str[i]);
    dataBinding[i].indBin= 5 - i;
    dataBinding[i].indInt= 0;
    dataBinding[i].indStr= SQL_NTS;
  }

  /* We don't expect errors in paramsets processing, thus we should get SQL_SUCCESS only*/
  expect_stmt(hstmt, SQLExecDirect(hstmt, "INSERT INTO t_bug48310 (bData, intField, strField) " \
    "VALUES (?,?,?)", SQL_NTS), SQL_SUCCESS);

  is_num(paramsProcessed, ROWS_TO_INSERT);

  ok_stmt(hstmt, SQLRowCount(hstmt, &rowsCount));
  is_num(rowsCount, ROWS_TO_INSERT);

  for (i= 0; i < paramsProcessed; ++i)
    if ( paramStatusArray[i] != SQL_PARAM_SUCCESS
      && paramStatusArray[i] != SQL_PARAM_SUCCESS_WITH_INFO )
    {
      printMessage("Parameter #%u status isn't successful(0x%X)", i+1, paramStatusArray[i]);
      return FAIL;
    }

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)1, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR, NULL, 0));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "SELECT bData, intField, strField\
                                      FROM t_bug48310\
                                      ORDER BY id", SQL_NTS));

  /* Just to make sure RowCount isn't broken */
  ok_stmt(hstmt, SQLRowCount(hstmt, &rowsCount));
  is_num(rowsCount, ROWS_TO_INSERT);

  for (i= 0; i < paramsProcessed; ++i)
  {
    ok_stmt(hstmt, SQLFetch(hstmt));

    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_BINARY, (SQLPOINTER)buff, 50, &nLen));
    is(memcmp((const void*) buff, (const void*)dataBinding[i].bData, 5 - i)==0);
    is_num(my_fetch_int(hstmt, 2), dataBinding[i].intField);
    is_str(my_fetch_str(hstmt, buff, 3), dataBinding[i].strField, strlen(str[i]));
  }

  expect_stmt(hstmt,SQLFetch(hstmt), SQL_NO_DATA_FOUND);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* One more check that RowCount isn't broken. check may get broken if input data
     changes */
  ok_sql(hstmt, "update t_bug48310 set strField='changed' where intField > 1");
  ok_stmt(hstmt, SQLRowCount(hstmt, &rowsCount));
  is_num(rowsCount, 1);

  /* Clean-up */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS bug48310", SQL_NTS));

  return OK;

#undef ROWS_TO_INSERT
#undef STR_FIELD_LENGTH
}


/*
Bug 48310 - parameters array support request.
Binding by column test
*/
DECLARE_TEST(paramarray_by_column)
{
#define ROWS_TO_INSERT 3
#define STR_FIELD_LENGTH 5
  SQLCHAR       buff[50];

  SQLCHAR       bData[ROWS_TO_INSERT][STR_FIELD_LENGTH]={{0x01, 0x80, 0x00, 0x80, 0x03},
                                          {0x02, 0x80, 0x00, 0x02},
                                          {0x03, 0x80, 0x01}};
  SQLLEN        bInd[ROWS_TO_INSERT]= {5,4,3};

  const SQLCHAR strField[ROWS_TO_INSERT][STR_FIELD_LENGTH]= {{'\0'}, {'x','\0'}, {'x','x','x','\0'} };
  SQLLEN        strInd[ROWS_TO_INSERT]= {SQL_NTS, SQL_NTS, SQL_NTS};

  SQLINTEGER    intField[ROWS_TO_INSERT] = {123321, 1, 0};
  SQLLEN        intInd[ROWS_TO_INSERT]= {5,4,3};

  SQLUSMALLINT  paramStatusArray[ROWS_TO_INSERT];
  SQLULEN       paramsProcessed, i, nLen;

  ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS t_bug48310", SQL_NTS));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "CREATE TABLE t_bug48310 (id int primary key auto_increment,"\
    "bData binary(5) NULL, intField int not null, strField varchar(255) not null)", SQL_NTS));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_BIND_TYPE, SQL_PARAM_BIND_BY_COLUMN, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)ROWS_TO_INSERT, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_STATUS_PTR, paramStatusArray, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR, &paramsProcessed, 0));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_BINARY, SQL_BINARY,
    0, 0, bData, 5, bInd));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER,
    0, 0, intField, 0, intInd));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR,
    0, 0, (SQLPOINTER)strField, 5, strInd ));

  /* We don't expect errors in paramsets processing, thus we should get SQL_SUCCESS only*/
  expect_stmt(hstmt, SQLExecDirect(hstmt, "INSERT INTO t_bug48310 (bData, intField, strField) " \
    "VALUES (?,?,?)", SQL_NTS), SQL_SUCCESS);

  is_num(paramsProcessed, ROWS_TO_INSERT);

  for (i= 0; i < paramsProcessed; ++i)
    if ( paramStatusArray[i] != SQL_PARAM_SUCCESS
      && paramStatusArray[i] != SQL_PARAM_SUCCESS_WITH_INFO )
    {
      printMessage("Parameter #%u status isn't successful(0x%X)", i+1, paramStatusArray[i]);
      return FAIL;
    }

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)1, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR, NULL, 0));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "SELECT bData, intField, strField\
                                       FROM t_bug48310\
                                       ORDER BY id", SQL_NTS));

  for (i= 0; i < paramsProcessed; ++i)
  {
    ok_stmt(hstmt, SQLFetch(hstmt));

    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_BINARY, (SQLPOINTER)buff, 50, &nLen));
    if (memcmp((const void*) buff, bData[i], 5 - i)!=0)
    {
      printMessage("Bin data inserted wrongly. Read: 0x%02X%02X%02X%02X%02X Had to be: 0x%02X%02X%02X%02X%02X"
        , buff[0], buff[1], buff[2], buff[3], buff[4]
        , bData[i][0], bData[i][1], bData[i][2], bData[i][3], bData[i][4]);
      return FAIL;
    }
    is_num(my_fetch_int(hstmt, 2), intField[i]);
    is_str(my_fetch_str(hstmt, buff, 3), strField[i], strlen(strField[i]));
  }

  /* Clean-up */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS bug48310", SQL_NTS));

  return OK;

#undef ROWS_TO_INSERT
#undef STR_FIELD_LENGTH
}


/*
Bug 48310 - parameters array support request.
Ignore paramset test
*/
DECLARE_TEST(paramarray_ignore_paramset)
{
#define ROWS_TO_INSERT 4
#define STR_FIELD_LENGTH 5
  SQLCHAR       buff[50];

  SQLCHAR       bData[ROWS_TO_INSERT][STR_FIELD_LENGTH]={{0x01, 0x80, 0x00, 0x80, 0x03},
                                                        {0x02, 0x80, 0x00, 0x02},
                                                        {0x03, 0x80, 0x01}};
  SQLLEN        bInd[ROWS_TO_INSERT]= {5,4,3};

  const SQLCHAR strField[ROWS_TO_INSERT][STR_FIELD_LENGTH]= {{'\0'}, {'x','\0'}, {'x','x','x','\0'} };
  SQLLEN        strInd[ROWS_TO_INSERT]= {SQL_NTS, SQL_NTS, SQL_NTS};

  SQLINTEGER    intField[ROWS_TO_INSERT] = {123321, 1, 0};
  SQLLEN        intInd[ROWS_TO_INSERT]= {5,4,3};

  SQLUSMALLINT  paramOperationArr[ROWS_TO_INSERT]={0,SQL_PARAM_IGNORE,0,SQL_PARAM_IGNORE};
  SQLUSMALLINT  paramStatusArr[ROWS_TO_INSERT];
  SQLULEN       paramsProcessed, i, nLen, rowsInserted= 0;

  ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS t_bug48310", SQL_NTS));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "CREATE TABLE t_bug48310 (id int primary key auto_increment,"\
    "bData binary(5) NULL, intField int not null, strField varchar(255) not null)", SQL_NTS));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_BIND_TYPE, SQL_PARAM_BIND_BY_COLUMN, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)ROWS_TO_INSERT, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_STATUS_PTR, paramStatusArr, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_OPERATION_PTR, paramOperationArr, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR, &paramsProcessed, 0));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_BINARY, SQL_BINARY,
    0, 0, bData, 5, bInd));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER,
    0, 0, intField, 0, intInd));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR,
    0, 0, (SQLPOINTER)strField, 5, strInd ));

  /* We don't expect errors in paramsets processing, thus we should get SQL_SUCCESS only*/
  expect_stmt(hstmt, SQLExecDirect(hstmt, "INSERT INTO t_bug48310 (bData, intField, strField) " \
    "VALUES (?,?,?)", SQL_NTS), SQL_SUCCESS);

  is_num(paramsProcessed, ROWS_TO_INSERT);

  for (i= 0; i < paramsProcessed; ++i)
  {
    if (paramOperationArr[i] == SQL_PARAM_IGNORE)
    {
      is_num(paramStatusArr[i], SQL_PARAM_UNUSED);
    }
    else if ( paramStatusArr[i] != SQL_PARAM_SUCCESS
      && paramStatusArr[i] != SQL_PARAM_SUCCESS_WITH_INFO )
    {
      printMessage("Parameter #%u status isn't successful(0x%X)", i+1, paramStatusArr[i]);
      return FAIL;
    }
  }

  /* Resetting statements attributes */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)1, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR, NULL, 0));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "SELECT bData, intField, strField\
                                      FROM t_bug48310\
                                      ORDER BY id", SQL_NTS));

  i= 0;
  while(i < paramsProcessed)
  {
    if (paramStatusArr[i] == SQL_PARAM_UNUSED)
    {
      ++i;
      continue;
    }

    ok_stmt(hstmt, SQLFetch(hstmt));

    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_BINARY, (SQLPOINTER)buff, 50, &nLen));

    if (memcmp((const void*) buff, bData[i], 5 - i)!=0)
    {
      printMessage("Bin data inserted wrongly. Read: 0x%02X%02X%02X%02X%02X Had to be: 0x%02X%02X%02X%02X%02X"
        , buff[0], buff[1], buff[2], buff[3], buff[4]
      , bData[i][0], bData[i][1], bData[i][2], bData[i][3], bData[i][4]);
      return FAIL;
    }
    is_num(my_fetch_int(hstmt, 2), intField[i]);
    is_str(my_fetch_str(hstmt, buff, 3), strField[i], strlen(strField[i]));

    ++rowsInserted;
    ++i;
  }

  /* Making sure that there is nothing else to fetch ... */
  expect_stmt(hstmt,SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  /* ... and that inserted was less than SQL_ATTR_PARAMSET_SIZE rows */
  is( rowsInserted < ROWS_TO_INSERT);

  /* Clean-up */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS bug48310", SQL_NTS));

  return OK;

#undef ROWS_TO_INSERT
#undef STR_FIELD_LENGTH
}


/*
  Bug 48310 - parameters array support request.
  Select statement.
*/
DECLARE_TEST(paramarray_select)
{
#define STMTS_TO_EXEC 3

  SQLINTEGER    intField[STMTS_TO_EXEC] = {3, 1, 2};
  SQLLEN        intInd[STMTS_TO_EXEC]= {5,4,3};

  SQLUSMALLINT  paramStatusArray[STMTS_TO_EXEC];
  SQLULEN       paramsProcessed, i;


  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_BIND_TYPE, SQL_PARAM_BIND_BY_COLUMN, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)STMTS_TO_EXEC, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_STATUS_PTR, paramStatusArray, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR, &paramsProcessed, 0));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER,
    0, 0, intField, 0, intInd));

  /* We don't expect errors in paramsets processing, thus we should get SQL_SUCCESS only*/
  expect_stmt(hstmt, SQLExecDirect(hstmt, "SELect ?,'So what'", SQL_NTS), SQL_SUCCESS);
  is_num(paramsProcessed, STMTS_TO_EXEC);

  for (i= 0; i < paramsProcessed; ++i)
  {
    if ( paramStatusArray[i] != SQL_PARAM_SUCCESS
      && paramStatusArray[i] != SQL_PARAM_SUCCESS_WITH_INFO )
    {
      printMessage("Parameter #%u status isn't successful(0x%X)", i+1, paramStatusArray[i]);
      return FAIL;
    }

    ok_stmt(hstmt, SQLFetch(hstmt));

    is_num(my_fetch_int(hstmt, 1), intField[i]);
  }

  /* Clean-up */
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;

#undef STMTS_TO_EXEC
}

/*
  Bug #31678876/100329 ODBC Driver overwriting memory causes process dead
  when parameterized query on
*/
DECLARE_TEST(t_bug31678876)
{
  int i = 0;
  char buf2[128] = {'0','1','2','3','4','5','6','7','8','9','a','b','c','d','e'};
  char buf3[128] = {'0','1','2','3','4','5','6','7','8','9','A','B','C','D','E',
                    'F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T'};
  ok_sql(hstmt, "DROP TABLE IF EXISTS bug31678876");
  ok_sql(hstmt, "CREATE TABLE bug31678876 (id int primary key auto_increment,"\
                "col2 VARCHAR(255), col3 VARCHAR(255)) "\
                "DEFAULT CHARACTER SET = utf8;");

  ok_stmt(hstmt, SQLPrepare(hstmt, "INSERT INTO bug31678876 "\
                                   "(col2, col3) VALUES (?,?)", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                                  SQL_C_CHAR, SQL_CHAR, 0, 0, &buf2,
                                  15, NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT,
                                  SQL_C_CHAR, SQL_CHAR, 0, 0, &buf3,
                                  32, NULL));

  for (i = 0; i < 100; ++i)
    ok_stmt(hstmt, SQLExecute(hstmt));

  wchar_t sel[] = L"select distinct `pa11`.`id` `id`,"\
  "CONCAT(`a12`.`col3`, `a12`.`col2`) `CustCol_12`,"\
  "CONCAT((Case when `pa11`.`MYCOL` = ? then ? else ? end), `pa11`.`MYCOL`) `MYCOL`"\
  "from (select `a11`.`id` `id`,"\
  " max(CONCAT(`a11`.`col3`, `a11`.`col2`)) `MYCOL`"\
  " from `bug31678876` `a11`"\
  " group by `a11`.`id`"\
  " ) `pa11`"\
  " join `bug31678876` `a12`"\
  " on (`pa11`.`id` = `a12`.`id`)";

  SQLWCHAR p1[] = {0x014e, 0xe452, 0xc56b};
  SQLWCHAR p2[] = {0x1f77};
  SQLWCHAR p3[] = {0x4750};

  SQLULEN len1 = sizeof(p1);
  SQLULEN len2 = sizeof(p2);
  SQLULEN len3 = sizeof(p3);

  ok_stmt(hstmt, SQLPrepareW(hstmt, W(sel), SQL_NTS));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
                                  SQL_C_WCHAR, SQL_WCHAR, 0, 0, &p1,
                                  sizeof(p1), &len1));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT,
                                  SQL_C_WCHAR, SQL_WCHAR, 0, 0, &p2,
                                  sizeof(p2), &len2));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT,
                                  SQL_C_WCHAR, SQL_WCHAR, 0, 0, &p3,
                                  sizeof(p3), &len3));

  ok_stmt(hstmt, SQLExecute(hstmt));
  int rnum = 0;

  while(SQLFetch(hstmt) == SQL_SUCCESS)
  {
    int col1;
    SQLULEN col1_len = 0, col2_len = 0, col3_len = 0;
    SQLWCHAR col2[128];
    SQLWCHAR col3[128];
    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_LONG, &col1, sizeof(int), &col1_len));
    ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_WCHAR, &col2, sizeof(col2), &col2_len));
    ok_stmt(hstmt, SQLGetData(hstmt, 3, SQL_C_WCHAR, &col3, sizeof(col3), &col3_len));
    ++rnum;
    // Crash happens when the local buffers go out of scope
  }
  printf("Fetched %d rows\n", rnum);
  return OK;
}


/*
  Bug #49029 - Server with sql mode NO_BACKSLASHES_ESCAPE obviously
  can work incorrectly (at least) with binary parameters
*/
DECLARE_TEST(t_bug49029)
{
  const SQLCHAR bData[6]= "\x01\x80\x00\x80\x01";
  SQLCHAR buff[6];
  SQLULEN len= 5;

  ok_stmt(hstmt, SQLExecDirect(hstmt, "set @@session.sql_mode='NO_ENGINE_SUBSTITUTION,NO_BACKSLASH_ESCAPES'", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_BINARY, SQL_BINARY,
    0, 0, (SQLPOINTER)bData, 0, &len));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "select ?", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_BINARY, (SQLPOINTER)buff, 6, &len));
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  is(memcmp((const void*) buff, (const void*)bData, 5)==0);

  SQLCHAR *bData128K = (SQLCHAR*)calloc(128000, 1);
  SQLCHAR *rData128K = (SQLCHAR*)calloc(128000, 1);
  SQLULEN b_len= 1020, r_len = 0;

  memcpy(bData128K, "\x01\x80\x00\x80\x01", 5);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_BINARY, SQL_BINARY,
    0, 0, (SQLPOINTER)bData128K, 0, &b_len));

  ok_stmt(hstmt, SQLExecDirect(hstmt, "select ?", SQL_NTS));

  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_BINARY, (SQLPOINTER)rData128K, 80000, &r_len));

  is(memcmp((const void*)bData128K, (const void*)rData128K, b_len)==0);
  free(bData128K);
  free(rData128K);
  return OK;
}



/*
  Bug #56804 - Server with sql mode NO_BACKSLASHES_ESCAPE obviously
  can work incorrectly (at least) with binary parameters
*/
DECLARE_TEST(t_bug56804)
{
#define PARAMSET_SIZE		10

  SQLINTEGER	len 	= 1;
  int i;

  SQLINTEGER	c1[PARAMSET_SIZE]=      {0, 1, 2, 3, 4, 5, 1, 7, 8, 9};
  SQLINTEGER	c2[PARAMSET_SIZE]=      {9, 8, 7, 6, 5, 4, 3, 2, 1, 0};
  SQLLEN      d1[PARAMSET_SIZE]=      {4, 4, 4, 4, 4, 4, 4, 4, 4, 4};
  SQLLEN      d2[PARAMSET_SIZE]=      {4, 4, 4, 4, 4, 4, 4, 4, 4, 4};
  SQLUSMALLINT status[PARAMSET_SIZE]= {0, 0, 0, 0, 0, 0, 0, 0, 0, 0};

  SQLSMALLINT	paramset_size	= PARAMSET_SIZE;

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug56804");
  ok_sql(hstmt, "create table bug56804 (c1 int primary key not null, c2 int)");
  ok_sql(hstmt, "insert into bug56804 values( 1, 1 ), (9, 9009)");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"insert into bug56804 values( ?,? )", SQL_NTS));

  ok_stmt(hstmt, SQLSetStmtAttr( hstmt, SQL_ATTR_PARAMSET_SIZE,
    (SQLPOINTER)paramset_size, SQL_IS_UINTEGER ));

  ok_stmt(hstmt, SQLSetStmtAttr( hstmt, SQL_ATTR_PARAM_STATUS_PTR,
    status, SQL_IS_POINTER ));

  ok_stmt(hstmt, SQLBindParameter( hstmt, 1, SQL_PARAM_INPUT, SQL_C_SLONG,
    SQL_DECIMAL, 4, 0, c1, 4, d1));

  ok_stmt(hstmt, SQLBindParameter( hstmt, 2, SQL_PARAM_INPUT, SQL_C_SLONG,
    SQL_DECIMAL, 4, 0, c2, 4, d2));

  expect_stmt(hstmt, SQLExecute(hstmt), SQL_SUCCESS_WITH_INFO);

  /* Following tests are here to ensure that driver works how it is currently
     expected to work, and they need to be changed if driver changes smth in the
     way how it reports errors in paramsets and diagnostics */
  for(i = 0; i < PARAMSET_SIZE; ++i )
  {
    printMessage("Paramset #%d (%d, %d)", i, c1[i], c2[i]);
    switch (i)
    {
    case 1:
    case 6:
      /* all errors but last have SQL_PARAM_DIAG_UNAVAILABLE */
      is_num(status[i], SQL_PARAM_DIAG_UNAVAILABLE);
      break;
    case 9:
      /* Last error -  we are supposed to get SQL_PARAM_ERROR for it */
      is_num(status[i], SQL_PARAM_ERROR);
      break;
    default:
      is_num(status[i], SQL_PARAM_SUCCESS);
    }
  }

  {
    SQLCHAR     sqlstate[6]= {0};
    SQLCHAR     message[255]= {0};
    SQLINTEGER  native_err= 0;
    SQLSMALLINT msglen= 0;

    i= 0;
    while(SQL_SUCCEEDED(SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, ++i, sqlstate,
      &native_err, message, sizeof(message), &msglen)))
    {
      printMessage("%d) [%s] %s %d", i, sqlstate, message, native_err);
    }

    /* just to make sure we got 1 diagnostics record ... */
    is_num(i, 2);
    /* ... and what the record is for the last error */
    is(strstr(message, "Duplicate entry '9'"));
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "DROP TABLE IF EXISTS bug56804");

  return OK;
#undef PARAMSET_SIZE
}


/*
  Bug 59772 - Column parameter binding makes SQLExecute not to return
  SQL_ERROR on disconnect
*/
DECLARE_TEST(t_bug59772)
{
#define ROWS_TO_INSERT 3

    SQLRETURN rc;
    SQLCHAR   buf_kill[50];

    SQLINTEGER    intField[ROWS_TO_INSERT] = {123321, 1, 0};
    SQLLEN        intInd[ROWS_TO_INSERT]= {5,4,3};

    SQLUSMALLINT  paramStatusArray[ROWS_TO_INSERT];
    SQLULEN       paramsProcessed, i;

    SQLINTEGER connection_id;

    SQLHENV henv2;
    SQLHDBC  hdbc2;
    SQLHSTMT hstmt2;

    int overall_result= OK;

    /* Create a new connection that we deliberately will kill */
    alloc_basic_handles(&henv2, &hdbc2, &hstmt2);

    ok_sql(hstmt2, "SELECT connection_id()");
    ok_stmt(hstmt2, SQLFetch(hstmt2));
    connection_id= my_fetch_int(hstmt2, 1);
    ok_stmt(hstmt2, SQLFreeStmt(hstmt2, SQL_CLOSE));

    ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS t_bug59772", SQL_NTS));
    ok_stmt(hstmt, SQLExecDirect(hstmt, "CREATE TABLE t_bug59772 (id int primary key auto_increment,"\
      "intField int)", SQL_NTS));

    ok_stmt(hstmt2, SQLSetStmtAttr(hstmt2, SQL_ATTR_PARAM_BIND_TYPE, SQL_PARAM_BIND_BY_COLUMN, 0));
    ok_stmt(hstmt2, SQLSetStmtAttr(hstmt2, SQL_ATTR_PARAMSET_SIZE, (SQLPOINTER)ROWS_TO_INSERT, 0));
    ok_stmt(hstmt2, SQLSetStmtAttr(hstmt2, SQL_ATTR_PARAM_STATUS_PTR, paramStatusArray, 0));
    ok_stmt(hstmt2, SQLSetStmtAttr(hstmt2, SQL_ATTR_PARAMS_PROCESSED_PTR, &paramsProcessed, 0));

    ok_stmt(hstmt2, SQLPrepare(hstmt2, "INSERT INTO t_bug59772 (intField) VALUES (?)", SQL_NTS));

    ok_stmt(hstmt2, SQLBindParameter(hstmt2, 1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER,
      0, 0, intField, 0, intInd));

    /* From another connection, kill the connection created above */
    sprintf(buf_kill, "KILL %d", connection_id);
    ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR *)buf_kill, SQL_NTS));

    rc= SQLExecute(hstmt2);

    /* The result should be SQL_ERROR */
    if (rc != SQL_ERROR)
      overall_result= FAIL;

    is (paramsProcessed == ROWS_TO_INSERT);

    for (i= 0; i < paramsProcessed; ++i)

      /* We expect error statuses for all parameters */
      if ( paramStatusArray[i] != ((i + 1 < ROWS_TO_INSERT) ?
            SQL_PARAM_DIAG_UNAVAILABLE : SQL_PARAM_ERROR) )
      {
        printMessage("Parameter #%u status isn't successful(0x%X)", i+1, paramStatusArray[i]);
        overall_result= FAIL;
      }

    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
    ok_stmt(hstmt, SQLExecDirect(hstmt, "DROP TABLE IF EXISTS t_bug59772", SQL_NTS));

    SQLFreeHandle(SQL_HANDLE_STMT, hstmt2);
    SQLDisconnect(hdbc2);
    SQLFreeHandle(SQL_HANDLE_DBC, hdbc2);
    SQLFreeHandle(SQL_HANDLE_ENV, henv2);

    return overall_result;
#undef ROWS_TO_INSERT
}


DECLARE_TEST(t_odbcoutparams)
{
  SQLSMALLINT ncol, i;
  SQLINTEGER  par[]= {10, 20, 30}, val;
  SQLLEN      len;
  SQLSMALLINT type[]= {SQL_PARAM_INPUT, SQL_PARAM_OUTPUT, SQL_PARAM_INPUT_OUTPUT};
  SQLCHAR     str[20]= "initial value", buff[20];

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_odbcoutparams");
  ok_sql(hstmt, "CREATE PROCEDURE t_odbcoutparams("
                "  IN p_in INT, "
                "  OUT p_out INT, "
                "  INOUT p_inout INT) "
                "BEGIN "
                "  SET p_in = p_in*10, p_out = (p_in+p_inout)*10, p_inout = p_inout*10; "
                "END");

  for (i=0; i < sizeof(par)/sizeof(SQLINTEGER); ++i)
  {
    ok_stmt(hstmt, SQLBindParameter(hstmt, i+1, type[i], SQL_C_LONG, SQL_INTEGER, 0,
      0, &par[i], 0, NULL));
  }

  ok_sql(hstmt, "CALL t_odbcoutparams(?, ?, ?)");

  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 2);

  is_num(par[1], 1300);
  is_num(par[2], 300);

  /* Only 1 row always - we still can get them as a result */
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 1300);
  is_num(my_fetch_int(hstmt, 2), 300);
  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE t_odbcoutparams");
  ok_sql(hstmt, "CREATE PROCEDURE t_odbcoutparams("
                "  IN p_in INT, "
                "  OUT p_out INT, "
                "  INOUT p_inout INT) "
                "BEGIN "
                "  SELECT p_in, p_out, p_inout; "
                "  SET p_in = 300, p_out = 100, p_inout = 200; "
                "END");
  ok_sql(hstmt, "CALL t_odbcoutparams(?, ?, ?)");
  /* rs-1 */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 3);

  is_num(my_fetch_int(hstmt, 1), 10);
  /* p_out does not have value at the moment */
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_INTEGER, &val, 0, &len));
  is_num(len, SQL_NULL_DATA);
  is_num(my_fetch_int(hstmt, 3), 300);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);
  ok_stmt(hstmt, SQLMoreResults(hstmt));

  is_num(par[1], 100);
  is_num(par[2], 200);

  /* SP execution status */
  ok_stmt(hstmt, SQLMoreResults(hstmt));

  expect_stmt(hstmt, SQLMoreResults(hstmt), SQL_NO_DATA);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  ok_sql(hstmt, "DROP PROCEDURE t_odbcoutparams");
  ok_sql(hstmt, "CREATE PROCEDURE t_odbcoutparams("
                "  OUT p_out VARCHAR(19), "
                "  IN p_in INT, "
                "  INOUT p_inout INT) "
                "BEGIN "
                "  SET p_in = 300, p_out := 'This is OUT param', p_inout = 200; "
                "  SELECT p_inout, p_in, substring(p_out, 9);"
                "END");

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_OUTPUT, SQL_C_CHAR, SQL_VARCHAR, 0,
      0, str, sizeof(str)/sizeof(SQLCHAR), NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0,
      0, &par[0], 0, NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT_OUTPUT, SQL_C_LONG,
      SQL_INTEGER, 0, 0, &par[1], 0, NULL));

  ok_sql(hstmt, "CALL t_odbcoutparams(?, ?, ?)");
  /* rs-1 */
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 3);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(my_fetch_int(hstmt, 1), 200);
  is_num(my_fetch_int(hstmt, 2), 300);
  is_str(my_fetch_str(hstmt, buff, 3), "OUT param", 10);

  ok_stmt(hstmt, SQLMoreResults(hstmt));
  is_str(str, "This is OUT param", 18);
  is_num(par[1], 200);

  ok_stmt(hstmt, SQLFetch(hstmt));
  is_str(my_fetch_str(hstmt, buff, 1), "This is OUT param", 18);
  is_num(my_fetch_int(hstmt, 2), 200);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  ok_sql(hstmt, "DROP PROCEDURE t_odbcoutparams");

  return OK;
}


DECLARE_TEST(t_bug14501952)
{
  SQLSMALLINT ncol;
  SQLLEN      len= 0;
  SQLCHAR     blobValue[50]= "initial value", buff[100];

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS bug14501952");
  ok_sql(hstmt, "CREATE PROCEDURE bug14501952 (INOUT param1 BLOB)\
                  BEGIN\
                    SET param1= 'this is blob value from SP ';\
                  END;");



  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT,
    SQL_C_BINARY, SQL_LONGVARBINARY, 50, 0, &blobValue, sizeof(blobValue),
    &len));

  ok_sql(hstmt, "CALL bug14501952(?)");

  is_str(blobValue, "this is blob value from SP ", 27);
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 1);

  /* Only 1 row always - we still can get them as a result */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_BINARY, buff, sizeof(buff),
                            &len));
  is_str(buff, blobValue, 27);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE bug14501952");

  return OK;
}


/* Bug#14563386 More than one BLOB(or any big data types) OUT param caused crash
 */
DECLARE_TEST(t_bug14563386)
{
  SQLSMALLINT ncol;
  SQLLEN      len= 0, len1= 0;
  SQLCHAR     blobValue[50]= "initial value", buff[100],
              binValue[50]= "varbinary init value";

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS b14563386");
  ok_sql(hstmt, "CREATE PROCEDURE b14563386 (INOUT blob_param \
                        BLOB, INOUT bin_param LONG VARBINARY)\
                  BEGIN\
                    SET blob_param = ' BLOB! ';\
                    SET bin_param = ' LONG VARBINARY ';\
                  END;");



  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT,
    SQL_C_BINARY, SQL_LONGVARBINARY, 50, 0, &blobValue, sizeof(blobValue),
    &len));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT_OUTPUT,
    SQL_C_BINARY, SQL_LONGVARBINARY, 50, 0, &binValue, sizeof(binValue),
    &len1));
  ok_sql(hstmt, "CALL b14563386(?, ?)");

  is_str(blobValue, " BLOB! ", 7);
  is_str(binValue, " LONG VARBINARY ", 16);
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 2);

  /* Only 1 row always - we still can get them as a result */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_BINARY, buff, sizeof(buff),
                            &len));
  is_str(buff, blobValue, 7);
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_BINARY, buff, sizeof(buff),
                            &len));
  is_str(buff, binValue, 16);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE b14563386");

  return OK;
}


/* Bug#14551229(could not repeat) Procedure with signed out parameter */
DECLARE_TEST(t_bug14551229)
{
  SQLINTEGER param, value;

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS b14551229");
  ok_sql(hstmt, "CREATE PROCEDURE b14551229 (OUT param INT)\
                  BEGIN\
                    SELECT -1 into param from dual;\
                  END;");



  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_OUTPUT,
    SQL_C_SLONG, SQL_INTEGER, 50, 0, &param, 0, 0));

  ok_sql(hstmt, "CALL b14551229(?)");

  is_num(param, -1);

  /* Only 1 row always - we still can get them as a result */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_SLONG, &value, 0, 0));
  is_num(value, -1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE b14551229");

  return OK;
}


/* Bug#14560916 ASSERT for INOUT parameter of BIT(10) type */
DECLARE_TEST(t_bug14560916)
{
  char        param[2]={1,1};
  SQLINTEGER  value;
  SQLLEN      len= 0;

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS b14560916");

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug14560916");
  ok_sql(hstmt, "CREATE TABLE bug14560916 (a BIT(10))");
  ok_sql(hstmt, "INSERT INTO bug14560916  values(b'1001000001')");

  ok_sql(hstmt, "CREATE PROCEDURE b14560916 (INOUT param bit(10))\
                  BEGIN\
                    SELECT a INTO param FROM bug14560916;\
                  END;");



  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT,
    SQL_C_CHAR, SQL_CHAR, 0, 0, &param, sizeof(param), &len));

  /* Parameter is used to make sure that ssps will be used */
  ok_sql(hstmt, "select a from bug14560916 where ? OR 1");
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_SLONG, &value, 0, 0));
  is_num(value, 577);
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_BINARY, &param, sizeof(param),
                            &len));
  is_num(len, 2);
  is_str(param, "\2A", 2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLPrepare(hstmt, "CALL b14560916(?)", SQL_NTS));
  ok_stmt(hstmt, SQLExecute(hstmt));

  len = 0;
  param[0] = 0;

  /* Only 1 row always - we still can get them as a result */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_SLONG, &value, 0, 0));
  is_num(value, 577);
  param[0]= param[1]= 1;
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_BINARY, &param, sizeof(param),
                            &len));
  is_num(len, 2);
  is_str(param, "\2A", 2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  len = 0;
  param[0] = 0;
  ok_stmt(hstmt, SQLMoreResults(hstmt));
  is_num(len, 2);
  is_str(param, "\2A", 2);

  ok_sql(hstmt, "DROP PROCEDURE b14560916");

  return OK;
}


/* Bug#14586094 Crash while executing SP having blob and varchar OUT parameters
  (could not repeat)
 */
DECLARE_TEST(t_bug14586094)
{
  SQLSMALLINT ncol;
  SQLLEN      len= SQL_NTS, len1= SQL_NTS;
  SQLCHAR     blobValue[50]= {0}/*"initial value"*/, buff[101],
              vcValue[101]= "varchar init value";

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS b14586094");
  ok_sql(hstmt, "CREATE PROCEDURE b14586094 (INOUT blob_param \
                    BLOB(50), INOUT vc_param VARCHAR(100))\
                  BEGIN\
                    SET blob_param = ' BLOB! ';\
                    SET vc_param = 'varchar';\
                  END;");



  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT,
    SQL_C_BINARY, SQL_LONGVARBINARY, 50, 0, &blobValue, sizeof(blobValue),
    &len));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT_OUTPUT,
    SQL_C_CHAR, SQL_VARCHAR, 50, 0, &vcValue, sizeof(vcValue), &len1));
  ok_sql(hstmt, "CALL b14586094(?, ?)");

  is_str(blobValue, " BLOB! ", 7);
  is_str(vcValue, "varchar", 9);
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 2);

  /* Only 1 row always - we still can get them as a result */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_BINARY, buff, sizeof(buff),
                            &len));
  is_str(buff, blobValue, 7);
  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_CHAR, buff, sizeof(buff),
                            &len));
  is_str(buff, vcValue, 9);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE b14586094");

  return OK;
}


DECLARE_TEST(t_longtextoutparam)
{
  SQLSMALLINT ncol;
  SQLLEN      len= 0;
  SQLCHAR     blobValue[50]= "initial value", buff[100];

  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_longtextoutparam");
  ok_sql(hstmt, "CREATE PROCEDURE t_longtextoutparam (INOUT param1 LONGTEXT)\
                  BEGIN\
                    SET param1= 'this is LONGTEXT value from SP ';\
                  END;");



  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT,
    SQL_C_BINARY, SQL_LONGVARBINARY, 50, 0, &blobValue, sizeof(blobValue),
    &len));

  ok_sql(hstmt, "CALL t_longtextoutparam(?)");

  is_str(blobValue, "this is LONGTEXT value from SP ", 32);
  ok_stmt(hstmt, SQLNumResultCols(hstmt,&ncol));
  is_num(ncol, 1);

  /* Only 1 row always - we still can get them as a result */
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, buff, sizeof(buff),
                            &len));
  is_str(buff, blobValue, 32);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE t_longtextoutparam");

  return OK;
}


/*
  Bug# 16613308/53891 ODBC driver not parsing comments correctly
*/
DECLARE_TEST(t_bug53891)
{
  int c1= 2;

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  /* Connect with SSPS enabled */
  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
                               NULL, "NO_SSPS=0");
  /* Try a simple query without parameters */
  ok_stmt(hstmt1, SQLPrepare(hstmt1,
                             "/* a question mark ? must be ignored */"\
                             " SELECT 1", SQL_NTS));

  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Try a query with parameters */
  ok_stmt(hstmt1, SQLPrepare(hstmt1,
                             "/* a question mark ? must be ignored */"\
                             " SELECT ?", SQL_NTS));

  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &c1, 0, NULL));
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), c1);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  /* Connect with SSPS disabled */
  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
                               NULL, "NO_SSPS=1");
  /* Try a simple query without parameters */
  ok_stmt(hstmt1, SQLPrepare(hstmt1,
                             "/* a question mark ? must be ignored */"\
                             " SELECT 1", SQL_NTS));

  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 1);

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Try a query with parameters */
  ok_stmt(hstmt1, SQLPrepare(hstmt1,
                             "/* a question mark ? must be ignored */"\
                             " SELECT ?", SQL_NTS));

  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &c1, 0, NULL));
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), c1);
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Try a simple query without parameters */
  ok_stmt(hstmt1, SQLPrepare(hstmt1,
                             "SELECT 1 -- a question mark ? must be ignored " _MY_NEWLINE
                             " + 1", SQL_NTS));
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 2);
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* Try a simple query without parameters */
  ok_stmt(hstmt1, SQLPrepare(hstmt1,
                             "SELECT 1 # a question mark ? must be ignored " _MY_NEWLINE
                             " + 2", SQL_NTS));
  ok_stmt(hstmt1, SQLExecute(hstmt1));
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 3);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


/* We need this for compilation */
#ifndef SQL_PARAM_DATA_AVAILABLE
# define SQL_PARAM_DATA_AVAILABLE 101
#endif

#ifndef USE_IODBC
DECLARE_TEST(t_odbc_outstream_params)
{
  SQLLEN      len= 0, len2= SQL_NTS, bytes, chunk_size;
  SQLCHAR     blobValue[50], chunk[8], *ptr= blobValue;
  SQLCHAR     inout[32]= "Input";
  SQLPOINTER  token;
  SQLRETURN   rc;

#ifndef _WIN32
  skip("At the moment the feature is not supported by the DM being used");
#endif
  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_odbcoutstreamparams");
  ok_sql(hstmt, "CREATE PROCEDURE t_odbcoutstreamparams (OUT   param1 LONGTEXT,\
                                                         INOUT param2 varchar(100))\
                  BEGIN\
                    SET param1= 'this is LONGTEXT value from SP ';\
                    SET param2= concat(param2, ' - changed!');\
                  END;");



  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_OUTPUT_STREAM,
          SQL_C_BINARY, SQL_VARBINARY, 0, 0,
          (SQLPOINTER)123, /* Application-defined token. Using ordinal position or a pointer to some data structure
                             for it would be a better idea */
          0,               /* Buffer length is ignored for streamed parameter */
          &len));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT_OUTPUT,
          SQL_C_CHAR, SQL_VARCHAR, 0, 0,
          (SQLPOINTER)inout,
          sizeof(inout)   ,
          &len2));


  expect_stmt(hstmt, SQLExecDirect(hstmt, "CALL t_odbcoutstreamparams(?, ?)", SQL_NTS), SQL_PARAM_DATA_AVAILABLE);

  is_num(len, 31);
  is_num(len2, 16);

  is_str(inout, "Input - changed!", len2);
  expect_stmt(hstmt, SQLParamData(hstmt, &token), SQL_PARAM_DATA_AVAILABLE);

  /* Checking if right token */
  is_num((SQLLEN)token, 123);

  /* We support binary streams only so far - checking if error returned and correct sqlstate set */
  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, chunk, sizeof(chunk), &bytes), SQL_ERROR);
  check_sqlstate(hstmt, "HYC00");
  do
  {
    rc= SQLGetData(hstmt, 1, SQL_C_BINARY, chunk, sizeof(chunk), &bytes);
    is(SQL_SUCCEEDED(rc));
    chunk_size= bytes < sizeof(chunk) ? bytes : sizeof(chunk);
    memcpy(ptr, chunk, chunk_size);
    ptr+= chunk_size;
    is(ptr < blobValue + sizeof(blobValue));
    /* Bug #17814768/70946 - in length pointer driver should always return total number of bytes left */
    is_num(bytes, len);
    len-= chunk_size;
  } while(rc == SQL_SUCCESS_WITH_INFO);

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_BINARY, chunk, sizeof(chunk), &bytes), SQL_NO_DATA);
  /* Bug #17814768/70946 - last call should put into length ptr total number of bytes, and not 0 or SQL_NO_TOTAL */
  is_num(bytes, 31);

  is_str(blobValue, "this is LONGTEXT value from SP ", 31);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Just to check that connecton is still usable after our streamed parameter */
  ok_sql(hstmt, "SELECT 3");

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 1), 3);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE t_odbcoutstreamparams");

  return OK;
}


DECLARE_TEST(t_odbc_inoutstream_params)
{
  SQLLEN      len= 0, len2= SQL_LEN_DATA_AT_EXEC(16), bytes, chunk_size;
  SQLCHAR     blobValue[50], chunk[8], *ptr= blobValue, c, inout[32]= "input";
  SQLINTEGER  intParam= 4;
  SQLPOINTER  token;
  SQLRETURN   rc;

#ifndef _WIN32
  skip("At the moment the feature is not supported by the DM being used");
#endif
  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_odbcInOutstreamparams");
  ok_sql(hstmt, "CREATE PROCEDURE t_odbcInOutstreamparams (IN    param1 INT,\
                                                           INOUT param2 LONGTEXT,\
                                                           INOUT param3 VARCHAR(100),\
                                                           OUT   param4 INT)\
                  BEGIN\
                    SELECT param2;\
                    SET param2= concat('a', param2, 'z');\
                    SET param3= concat(param3, ' - output!');\
                    SET param4= param1 + 7;\
                  END;");

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT,
          SQL_C_LONG, SQL_INTEGER, 0, 0, &intParam, 0, NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT_OUTPUT_STREAM,
          SQL_C_BINARY, SQL_VARBINARY, 0, 0,
          (SQLPOINTER)2,  /* Application-defined token. Using ordinal position or a pointer to some
                            data structure are good ideas here */
          0,             /* Buffer length is ignored for streamed parameter */
          &len));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT_OUTPUT,
          SQL_C_CHAR, SQL_VARCHAR, 0, 0,
          inout,
          sizeof(inout),
          &len2));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 4, SQL_PARAM_OUTPUT,
          SQL_C_LONG, SQL_INTEGER, 0, 0, &intParam, 0, NULL));

  len= SQL_LEN_DATA_AT_EXEC(26);

  expect_stmt(hstmt, SQLExecDirect(hstmt, "CALL t_odbcInOutstreamparams(?, ?, ?, ?)", SQL_NTS), SQL_NEED_DATA);

  expect_stmt(hstmt, SQLParamData(hstmt, &token), SQL_NEED_DATA);
  is_num((SQLLEN)token, 2);

  ok_stmt(hstmt, SQLPutData(hstmt, " ", 1));
  for (c= 'b'; c < 'z'; ++c)
  {
    ok_stmt(hstmt, SQLPutData(hstmt, &c, 1));
  }

  ok_stmt(hstmt, SQLPutData(hstmt, " ", 1));

  expect_stmt(hstmt, SQLParamData(hstmt, &token), SQL_NEED_DATA);

  for (c=0; c < strlen(inout); ++c)
  {
    ok_stmt(hstmt, SQLPutData(hstmt, &inout[c], 1));
  }
  /* After sending last chunk on next SQLParamData the query is supposed to be executed. If we did
     not have a resultset, SQLParamData would return SQL_PARAM_DATA_AVAILABLE*/
  ok_stmt(hstmt, SQLParamData(hstmt, &token));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(my_fetch_str(hstmt, blobValue, 1), " bcdefghijklmnopqrstuvwxy ", 26);

  expect_stmt(hstmt, SQLMoreResults(hstmt), SQL_PARAM_DATA_AVAILABLE);

  /* Out parameters are available already */
  is_num(intParam, 11);
  /* We have full length of the stream */
  is_num(len, 28);
  token= 0;
  /* Now we are getting the stream */
  expect_stmt(hstmt, SQLParamData(hstmt, &token), SQL_PARAM_DATA_AVAILABLE);
  /* Checking if right token */
  is_num((SQLLEN)token, 2);

  /* We support binary streams only so far - checking if error returned and correct sqlstate set.
     For retrieving parameter data 2nd argument is ordinal of parameter, not column in result */
  expect_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_CHAR, chunk, sizeof(chunk), &bytes), SQL_ERROR);
  is(check_sqlstate(hstmt, "HYC00") == OK);
  do
  {
    rc= SQLGetData(hstmt, 2, SQL_C_BINARY, chunk, sizeof(chunk), &bytes);
    is(SQL_SUCCEEDED(rc));
    chunk_size= bytes < sizeof(chunk) ? bytes : sizeof(chunk);
    memcpy(ptr, chunk, chunk_size);
    ptr+= chunk_size;
    is(ptr < blobValue + sizeof(blobValue));
    /* Bug #17814768/70946 - in length pointer driver should always return total number of bytes left */
    is_num(bytes, len);
    len-= chunk_size;
  } while(rc == SQL_SUCCESS_WITH_INFO);

  is_str(blobValue, "a bcdefghijklmnopqrstuvwxy z", 28);

  is_num(len2, 15);
  is_str(inout, "input - output!", len2);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Just to check that connecton is still usable after our streamed parameter */
  ok_sql(hstmt, "SELECT 3");

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 1), 3);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE t_odbcInOutstreamparams");

  return OK;
}


/* Bug #17842966 - SQLGETDATA RETURNING ERROR BEING CALLED AFTER SQLPARAMDATA
   If query uses in and out stream and no resultset, additional SQLParamData would be
   required to get out stream token */
DECLARE_TEST(t_inoutstream17842966)
{
  SQLLEN      len= 0, len2= SQL_LEN_DATA_AT_EXEC(16), bytes, chunk_size;
  SQLCHAR     blobValue[50], chunk[8], *ptr= blobValue, c, inout[32]= "input";
  SQLINTEGER  intParam= 4;
  SQLPOINTER  token;
  SQLRETURN   rc;

#ifndef _WIN32
  skip("At the moment the feature is not supported by the DM being used");
#endif
  ok_sql(hstmt, "DROP PROCEDURE IF EXISTS t_inoutstream17842966");
  ok_sql(hstmt, "CREATE PROCEDURE t_inoutstream17842966 (INOUT param2 LONGTEXT)\
                  BEGIN\
                    SET param2= concat('a', param2, 'z');\
                  END;");

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT_STREAM,
          SQL_C_BINARY, SQL_VARBINARY, 0, 0,
          (SQLPOINTER)1,  /* Application-defined token. Using ordinal position or a pointer to some
                            data structure are good ideas here */
          0,             /* Buffer length is ignored for streamed parameter */
          &len));

  len= SQL_LEN_DATA_AT_EXEC(26);

  expect_stmt(hstmt, SQLExecDirect(hstmt, "CALL t_inoutstream17842966(?)", SQL_NTS), SQL_NEED_DATA);

  expect_stmt(hstmt, SQLParamData(hstmt, &token), SQL_NEED_DATA);
  is_num((SQLLEN)token, 1);

  ok_stmt(hstmt, SQLPutData(hstmt, " ", 1));
  for (c= 'b'; c < 'z'; ++c)
  {
    ok_stmt(hstmt, SQLPutData(hstmt, &c, 1));
  }

  ok_stmt(hstmt, SQLPutData(hstmt, " ", 1));

  token= 0;
  /* DAE is done, query shoud be executed and we are getting the stream - this where the bug occured
    (token did not get correct value) */
  expect_stmt(hstmt, SQLParamData(hstmt, &token), SQL_PARAM_DATA_AVAILABLE);

  /* Checking if right token */
  is_num((SQLLEN)token, 1);

  /* We support binary streams only so far - checking if error returned and correct sqlstate set.
     For retrieving parameter data 2nd argument is ordinal of parameter, not column in result */
  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, chunk, sizeof(chunk), &bytes), SQL_ERROR);
  is(check_sqlstate(hstmt, "HYC00") == OK);
  do
  {
    rc= SQLGetData(hstmt, 1, SQL_C_BINARY, chunk, sizeof(chunk), &bytes);
    is(SQL_SUCCEEDED(rc));
    chunk_size= bytes < sizeof(chunk) ? bytes : sizeof(chunk);
    memcpy(ptr, chunk, chunk_size);
    ptr+= chunk_size;
    is(ptr < blobValue + sizeof(blobValue));
    /* Bug #17814768/70946 - in length pointer driver should always return total number of bytes left */
    is_num(bytes, len);
    len-= chunk_size;
  } while(rc == SQL_SUCCESS_WITH_INFO);

  is_str(blobValue, "a bcdefghijklmnopqrstuvwxy z", 28);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Just to check that connecton is still usable after our streamed parameter */
  ok_sql(hstmt, "SELECT 3");

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(my_fetch_int(hstmt, 1), 3);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP PROCEDURE t_inoutstream17842966");

  return OK;
}

#endif /* #ifndef USE_IODBC */

DECLARE_TEST(t_bug28175772)
{
  char sql_create_proc[256] = { 0 };
  char sql_drop_proc[100] = { 0 };
  char sql_select[100] = { 0 };
  SQLLEN iSize = SQL_NTS, iSize1 = SQL_NTS;
  char blobValue[200] = { 0 }, binValue[200] = { 0 }, expcValue[200] = { 0 };

  // connection strings
  strcpy(blobValue, "test data");
  strcpy(binValue, "test data");
  strcpy(expcValue, "test databar");

  sprintf(sql_create_proc, "%s", "CREATE PROCEDURE inoutproc (INOUT param1 "\
          "LONGBLOB, INOUT param2 LONG VARBINARY)"\
          " BEGIN"\
          " SET param1 := CONCAT(param1, 'bar'); "\
          " SET param2 := CONCAT(param2, 'bar'); "\
          " END; ");
  sprintf(sql_drop_proc, "%s", "DROP PROCEDURE if exists inoutproc");
  sprintf(sql_select, "%s", "call inoutproc(?, ?)");
  //drop proc if already exists
  ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)sql_drop_proc, SQL_NTS));
  //create proc
  ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)sql_create_proc, SQL_NTS));
  //prepare the call statement
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR*)sql_select, SQL_NTS));
  //bind the call statement 1
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT_OUTPUT, SQL_C_BINARY,
                                  SQL_LONGVARBINARY, 0, 0, blobValue, 200, &iSize));
  //bind the call statement 1
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT_OUTPUT, SQL_C_BINARY,
                                  SQL_LONGVARBINARY, 0, 0, binValue, 200, &iSize1));
  // execute
  ok_stmt(hstmt, SQLExecute(hstmt));

  if (memcmp(binValue, expcValue, 12) != 0)
  {
    printf("OuT parameter does not match : Retrieved Value : %s\n",
           binValue);
    return FAIL;
  }
  if (memcmp(blobValue, expcValue, 12) != 0)
  {
    printf("OuT parameter does not match : Retrieved Value : %s\n",
           blobValue);
    return FAIL;
  }

  return OK;
}


DECLARE_TEST(t_sp_return)
{
  char sql_select[100] = { 0 };
  SQLLEN iSize = SQL_NTS, iSize1 = SQL_NTS;
  char val1[200] = { 0 }, val2[200] = { 0 };
  SQLLEN len = 0;
  SQLSMALLINT   i;
  char strVal[255];


  // connection strings
  strcpy(val1, "test data 1");
  strcpy(val2, "test data 2");

  ok_sql(hstmt, "DROP PROCEDURE if exists simpleproc");
  ok_sql(hstmt, "CREATE PROCEDURE simpleproc (param1 VARCHAR(100), " \
          " param2 VARCHAR(100))"\
          " BEGIN"\
          " SELECT param1 UNION SELECT param2 UNION SELECT 'ABC'; "\
          " END; ");

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR*)"call simpleproc(?, ?)", SQL_NTS));
  //bind the call statement 1
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_CHAR, 0, 0, val1, 200, &iSize));
  //bind the call statement 1
  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT_OUTPUT, SQL_C_CHAR,
                                  SQL_CHAR, 0, 0, val2, 200, &iSize1));
  // execute
  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR,
    (SQLPOINTER)strVal, sizeof(strVal), &len));

  memset(strVal, 0, sizeof(strVal));
  i = 0;
  while (SQL_SUCCESS == SQLFetch(hstmt))
  {
    switch (i)
    {
      case 0:
        is_str("test data 1", strVal, 11); break;
      case 1:
        is_str("test data 2", strVal, 11); break;
      case 2:
        is_str("ABC", strVal, 3); break;
    }
    ++i;
  }
  is_num(3, i);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}

/*
  Bug 30591722
  Only a single value is being inserted instead of the array with
  SQLParamOptions
*/
DECLARE_TEST(t_bug30591722)
{
  #undef RCNT
  #define RCNT 100

  int cnt = 0;

  SQLUINTEGER lval[RCNT] = { 0 };
  SQLUINTEGER uintval1[RCNT] = { 0 };
  SQLUINTEGER uintval2[RCNT] = { 0 };

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  /* Connect with SSPS enabled */
  printf("\nCreating a new connection NO_SSPS=1\n");
  alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL, NULL, NULL,
    "test", "NO_SSPS=1");

  printf("Creating a table\n");
  ok_sql(hstmt1, "DROP TABLE IF EXISTS bug30591722");
  ok_sql(hstmt1, "CREATE TABLE bug30591722(id INT, id2 INT)");

  ok_stmt(hstmt1, SQLParamOptions(hstmt1, RCNT, (SQLULEN*)&lval));

  for (int j = 0; j < RCNT; j++)
  {
    uintval1[j] = j;
    uintval2[j] = 100 + j;
  }

  printf("SQLPrepare\n");
  ok_stmt(hstmt1, SQLPrepare(hstmt1,
    "INSERT INTO bug30591722 VALUES (?+1, ?+100)", SQL_NTS));

  printf("Bind Parameters\n");
  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 1,
    SQL_PARAM_INPUT, SQL_C_ULONG, SQL_NUMERIC, 4, 0, &uintval1, 0, NULL));

  ok_stmt(hstmt1, SQLBindParameter(hstmt1, 2,
    SQL_PARAM_INPUT, SQL_C_ULONG, SQL_NUMERIC, 4, 0, &uintval2, 0, NULL));

  printf("SQLExecute\n");
  ok_stmt(hstmt1, SQLExecute(hstmt1));

  printf("Checking the result using SELECT\n");
  ok_stmt(hstmt1, SQLExecDirect(hstmt1,
    (SQLCHAR*)"select * from bug30591722 order by id", SQL_NTS));

  while (SQLFetch(hstmt1) == SQL_SUCCESS)
  {
    SQLUINTEGER id1 = 0, id2 = 0;
    ok_stmt(hstmt1, SQLGetData(hstmt1, 1, SQL_C_ULONG, &id1, 0, NULL));
    ok_stmt(hstmt1, SQLGetData(hstmt1, 2, SQL_C_ULONG, &id2, 0, NULL));
    is_num(cnt + 1, id1);
    is_num(cnt + 200, id2);
    ++cnt;
  }
  is_num(100, cnt);
  printf("Freeing handles\n");
  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  printf("Handles are freed\n");
  return OK;
}


DECLARE_TEST(t_wl14217)
{
  SQLINTEGER p1_int = 50;
  char *p2_char = "char attribute";
  SQLHANDLE ipd = NULL;
  char query[1024];
  const char* name1 = "my_attr_name1";
  const char* name2 = "my_attr_name2";
  const char* name3 = "my_attr_name3";
  const char* no_val_name = "no_value";
  int p_select = 1;
  SQLINTEGER v_select = 0;
  SQLUINTEGER v1_int = 0;
  SQLLEN len1 = 0, len2 = 0, len3 = 0, len4 = 0;

  TIMESTAMP_STRUCT p3_ts;
  /* insert using SQL_C_TIMESTAMP to SQL_TIMESTAMP */
  p3_ts.year= 2020;
  p3_ts.month= 12;
  p3_ts.day= 21;
  p3_ts.hour= 15;
  p3_ts.minute= 34;
  p3_ts.second= 59;
  p3_ts.fraction= 0;

  if (!mysql_min_version(hdbc, "8.0.23", 6))
    skip("This feature is supported by MySQL 8.0.23 or newer");

  /*
    If component is already installed the query will return error.
    Ignoring the result.
  */
  SQLExecDirect(hstmt,
                "INSTALL COMPONENT \"file://component_query_attributes\"",
                SQL_NTS);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &p1_int, 0, NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR,
                                  SQL_CHAR, 0, 0, p2_char, (SQLLEN)strlen(p2_char),
                                  NULL));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT,
                                  SQL_C_TIMESTAMP, SQL_TIMESTAMP,
                                  0, 0, &p3_ts, (SQLLEN)sizeof(p3_ts), NULL));


  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_IMP_PARAM_DESC, &ipd, 0, 0));
  ok_stmt(hstmt, SQLSetDescField(ipd, 1, SQL_DESC_NAME, (SQLPOINTER)name1, SQL_NTS));
  ok_stmt(hstmt, SQLSetDescField(ipd, 2, SQL_DESC_NAME, (SQLPOINTER)name2, SQL_NTS));
  ok_stmt(hstmt, SQLSetDescField(ipd, 3, SQL_DESC_NAME, (SQLPOINTER)name3, SQL_NTS));
  ok_stmt(hstmt, SQLSetDescField(ipd, 4, SQL_DESC_NAME, (SQLPOINTER)no_val_name, SQL_NTS));

  while(p_select < 4)
  {
    v_select = 0;
    SQLCHAR v2_char[32];
    TIMESTAMP_STRUCT v3_ts;
    SQLCHAR v4_char[32];
    v1_int = 0;
    len1 = 0;  len2 = 0;  len3 = 0;  len4 = 0;
    SQLLEN exp_len2 = strlen(p2_char);
    SQLLEN exp_len3 = sizeof(v3_ts);


    memset(&v2_char, 0, sizeof(v2_char));
    memset(&v3_ts, 0, sizeof(v3_ts));
    memset(&v4_char, 0, sizeof(v4_char));

    snprintf(query, sizeof(query),
      "SELECT %d, mysql_query_attribute_string('%s'),"
      "mysql_query_attribute_string('%s'),"
      "mysql_query_attribute_string('%s'),"
      "mysql_query_attribute_string('%s')"
      , p_select, name1, name2, name3, no_val_name);

    // On 3rd run we should reset STMT and remove statement attributes
    if (p_select == 3)
    {
      ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
      p1_int = 0;
      p2_char = "";
      exp_len2 = -1;

      memset(&p3_ts, 0, sizeof(p3_ts));
      exp_len3 = -1;

    }

    printf("Running query, iteration number: %d\n", p_select);
    ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)query, SQL_NTS));
    ok_stmt(hstmt, SQLFetch(hstmt));

    ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_ULONG, &v_select, 0, NULL));
    ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_ULONG, &v1_int, 0, &len1));
    ok_stmt(hstmt, SQLGetData(hstmt, 3, SQL_C_CHAR, &v2_char, sizeof(v2_char), &len2));
    ok_stmt(hstmt, SQLGetData(hstmt, 4, SQL_C_TIMESTAMP, &v3_ts, sizeof(v3_ts), &len3));

    // Attribute with name only, but no data
    ok_stmt(hstmt, SQLGetData(hstmt, 5, SQL_C_CHAR, &v4_char, sizeof(v4_char), &len4));

    printf("Attributes data: [%d][%d][%s][%d-%d-%d %d:%d:%d]\n",
           v_select, v1_int, v2_char,
           v3_ts.year, v3_ts.month, v3_ts.day,
           v3_ts.hour, v3_ts.minute, v3_ts.second);

    is_num(p_select, v_select);
    is_num(p1_int, v1_int);
    is_num(exp_len2, len2);
    is_str(p2_char, v2_char, strlen(p2_char));

    is_num(exp_len3, len3);
    is_num(0, memcmp(&p3_ts, &v3_ts, sizeof(p3_ts)));

    is_num(-1, len4);
    expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA);
    ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

    ++p_select;
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  // Bind just a parameter, no name
  p1_int = 99;
  len1 = 0;
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                SQL_INTEGER, 0, 0, &p1_int, 0, NULL));

  ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)"SELECT 98", SQL_NTS));
  ok_stmt(hstmt, SQLFetch(hstmt));

  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_ULONG, &v_select, 0, NULL));
  expect_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_ULONG, &v1_int, 0, &len1), SQL_ERROR);

  is_num(98, v_select);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  // Bind parameters with gaps
  p1_int = 199;
  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_LONG,
                                SQL_INTEGER, 0, 0, &p1_int, 0, NULL));

  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_IMP_PARAM_DESC, &ipd, 0, 0));
  ok_stmt(hstmt, SQLSetDescField(ipd, 3, SQL_DESC_NAME, (SQLPOINTER)name3, SQL_NTS));

  expect_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR*)query, SQL_NTS),
              SQL_ERROR);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_odbcoutparams)
  ADD_TEST(t_bug14501952)
  ADD_TEST(t_bug14563386)
  ADD_TEST(t_bug28175772)
  ADD_TEST(t_bug14551229)
  //ADD_TEST(t_bug14560916)
  ADD_TEST(t_bug14586094)
  ADD_TEST(t_longtextoutparam)

  ADD_TEST(t_wl14217)
  ADD_TEST(tmysql_fix)
  ADD_TEST(my_param_data)
  ADD_TEST(t_bug31373948)
  ADD_TEST(t_bug30591722)
  ADD_TEST(t_sp_return)
  ADD_TEST(t_bug30428851)
  ADD_TEST(my_init_table)
  ADD_TEST(t_bug59772)
  ADD_TEST(my_param_insert)
  ADD_TEST(my_param_update)
  ADD_TEST(my_param_delete)
  ADD_TEST(paramarray_by_row)
  ADD_TEST(paramarray_by_column)
  ADD_TEST(paramarray_ignore_paramset)
  ADD_TEST(paramarray_select)
#ifndef USE_IODBC
  ADD_TEST(t_bug56804)
#endif
  ADD_TEST_UNICODE(t_bug31678876)
  ADD_TEST(t_param_offset)
  ADD_TEST(t_bug49029)
  ADD_TEST(t_bug53891)
#if USE_UNIXODBC
  ADD_TEST(t_odbc_outstream_params)
  ADD_TEST(t_odbc_inoutstream_params)
  ADD_TEST(t_inoutstream17842966)
#endif
END_TESTS


RUN_TESTS
