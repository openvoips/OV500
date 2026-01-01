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

/*
 * Tests for descriptors.
 */

#include "odbctap.h"


/*
 * Tests for paramset:
 * SQL_ATTR_PARAMSET_SIZE (apd->array_size)
 * SQL_ATTR_PARAM_STATUS_PTR (ipd->array_status_ptr)
 * SQL_ATTR_PARAM_OPERATION_PTR (apd->array_status_ptr)
 * SQL_ATTR_PARAMS_PROCESSED_PTR (apd->rows_processed_ptr)
 */
DECLARE_TEST(t_desc_paramset)
{
  SQLUINTEGER   parsetsize= 4;
  SQLUSMALLINT  parstatus[4];
  SQLUSMALLINT  parop[4]; /* operation */
  SQLULEN       pardone; /* processed */
  SQLHANDLE     ipd, apd;
  SQLINTEGER    params1[4];
  SQLINTEGER    params2[4];

  parop[0]= SQL_PARAM_PROCEED;
  parop[1]= SQL_PARAM_IGNORE;
  parop[2]= SQL_PARAM_IGNORE;
  parop[3]= SQL_PARAM_PROCEED;
  params1[0]= 0;
  params1[1]= 1;
  params1[2]= 2;
  params1[3]= 3;
  params2[0]= 100;
  params2[1]= 101;
  params2[2]= 102;
  params2[3]= 103;

  /* get the descriptors */
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_PARAM_DESC,
                                &apd, SQL_IS_POINTER, NULL));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_IMP_PARAM_DESC,
                                &ipd, SQL_IS_POINTER, NULL));

  /* set the fields */
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMSET_SIZE,
                                (SQLPOINTER)((size_t)parsetsize), 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_STATUS_PTR,
                                parstatus, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAM_OPERATION_PTR,
                                parop, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_PARAMS_PROCESSED_PTR,
                                &pardone, 0));

  /* verify the fields */
  {
    SQLPOINTER x_parstatus, x_parop, x_pardone;
    SQLUINTEGER x_parsetsize;

    ok_desc(apd, SQLGetDescField(apd, 0, SQL_DESC_ARRAY_SIZE,
                                 &x_parsetsize, SQL_IS_UINTEGER, NULL));
    ok_desc(ipd, SQLGetDescField(ipd, 0, SQL_DESC_ARRAY_STATUS_PTR,
                                 &x_parstatus, SQL_IS_POINTER, NULL));
    ok_desc(apd, SQLGetDescField(apd, 0, SQL_DESC_ARRAY_STATUS_PTR,
                                 &x_parop, SQL_IS_POINTER, NULL));
    ok_desc(ipd, SQLGetDescField(ipd, 0, SQL_DESC_ROWS_PROCESSED_PTR,
                                 &x_pardone, SQL_IS_POINTER, NULL));

    is_num(x_parsetsize, parsetsize);
    is(x_parstatus == parstatus);
    is(x_parop == parop);
    is(x_pardone == &pardone);
  }

  ok_sql(hstmt, "drop table if exists t_paramset");
  ok_sql(hstmt, "create table t_paramset(x int, y int)");
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"insert into t_paramset "
                            "values (?, ?)", SQL_NTS));

  ok_stmt(hstmt,
          SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_INTEGER, SQL_C_LONG,
                           0, 0, params1, sizeof(SQLINTEGER), NULL));
  ok_stmt(hstmt,
          SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_INTEGER, SQL_C_LONG,
                           0, 0, params2, sizeof(SQLINTEGER), NULL));
  /*
  ok_stmt(hstmt, SQLExecute(hstmt));
  */
  /* TODO, finish test and implement */

  return OK;
}


/*
 * Test for errors when setting descriptor fields.
 */
DECLARE_TEST(t_desc_set_error)
{
  SQLHANDLE ird, ard;
  SQLPOINTER array_status_ptr= (SQLPOINTER) 0xc0c0;

  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_IMP_ROW_DESC,
                                &ird, SQL_IS_POINTER, NULL));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                &ard, SQL_IS_POINTER, NULL));

  /* Test bad header field permissions */
  expect_desc(ard, SQLSetDescField(ard, 0, SQL_DESC_ROWS_PROCESSED_PTR,
                                   NULL, SQL_IS_POINTER), SQL_ERROR);
  is(check_sqlstate_ex(ard, SQL_HANDLE_DESC, "HY091") == OK);

  /* Test the HY016 error received when setting any field on an IRD
   * besides SQL_DESC_ARRAY_STATUS_PTR or SQL_DESC_ROWS_PROCESSED_PTR.
   *
   * Windows intercepts this and returns HY091
   */
  ok_desc(ird, SQLSetDescField(ird, 0, SQL_DESC_ARRAY_STATUS_PTR,
                               array_status_ptr, SQL_IS_POINTER));
  expect_desc(ird, SQLSetDescField(ird, 0, SQL_DESC_AUTO_UNIQUE_VALUE,
                                   (SQLPOINTER) 1, SQL_IS_INTEGER),
              SQL_ERROR);
#ifdef _WIN32
  is(check_sqlstate_ex(ird, SQL_HANDLE_DESC, "HY091") == OK);
#else
  is(check_sqlstate_ex(ird, SQL_HANDLE_DESC, "HY016") == OK);
#endif

  /* Test invalid field identifier (will be HY016 on ird, HY091 on others) */
  expect_desc(ard, SQLSetDescField(ard, 0, 999, NULL, SQL_IS_POINTER),
              SQL_ERROR);
  is(check_sqlstate_ex(ard, SQL_HANDLE_DESC, "HY091") == OK);

  /* Test bad data type (SQLINTEGER cant be assigned to SQLPOINTER) */
  expect_desc(ard, SQLSetDescField(ard, 0, SQL_DESC_BIND_OFFSET_PTR,
                                   NULL, SQL_IS_INTEGER), SQL_ERROR);
  is(check_sqlstate_ex(ard, SQL_HANDLE_DESC, "HY015") == OK);

  return OK;
}


/*
   Implicit Resetting of COUNT Field with SQLBindCol()
*/
DECLARE_TEST(t_sqlbindcol_count_reset)
{
  SQLHANDLE ard;
  SQLINTEGER count;
  SQLCHAR *buf[10];

  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                &ard, SQL_IS_POINTER, NULL));

  ok_desc(ard, SQLGetDescField(ard, 0, SQL_DESC_COUNT, &count,
                               SQL_IS_INTEGER, NULL));
  is_num(count, 0);

  ok_sql(hstmt, "select 1,2,3,4,5");

  ok_desc(ard, SQLGetDescField(ard, 0, SQL_DESC_COUNT, &count,
                               SQL_IS_INTEGER, NULL));
  is_num(count, 0);

  /* bind column 3 -> expand to count = 3 */
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_CHAR, buf, 10, NULL));

  ok_desc(ard, SQLGetDescField(ard, 0, SQL_DESC_COUNT, &count,
                               SQL_IS_INTEGER, NULL));
  is_num(count, 3);

  /* unbind column 3 -> contract to count = 0 */
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_DEFAULT, NULL, 0, NULL));

  ok_desc(ard, SQLGetDescField(ard, 0, SQL_DESC_COUNT, &count,
                               SQL_IS_INTEGER, NULL));
  is_num(count, 0);

  /* bind column 2 -> expand to count = 2 */
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_CHAR, buf, 10, NULL));

  ok_desc(ard, SQLGetDescField(ard, 0, SQL_DESC_COUNT, &count,
                               SQL_IS_INTEGER, NULL));
  is_num(count, 2);

  /* bind column 3 -> expand to count = 3 */
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_CHAR, buf, 10, NULL));

  ok_desc(ard, SQLGetDescField(ard, 0, SQL_DESC_COUNT, &count,
                               SQL_IS_INTEGER, NULL));
  is_num(count, 3);

  /* unbind column 3 -> contract to count = 2 */
  ok_stmt(hstmt, SQLBindCol(hstmt, 3, SQL_C_DEFAULT, NULL, 0, NULL));

  ok_desc(ard, SQLGetDescField(ard, 0, SQL_DESC_COUNT, &count,
                               SQL_IS_INTEGER, NULL));
  is_num(count, 2);

  return OK;
}


/*
  Test that if no type is given to SQLSetDescField(), that the
  correct default is used. See Bug#31720.
*/
DECLARE_TEST(t_desc_default_type)
{
  SQLHANDLE ard, apd;
  SQLINTEGER inval= 20, outval= 0;

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"select ?", SQL_NTS));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_PARAM_DESC,
                                &apd, 0, NULL));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                &ard, 0, NULL));

  ok_desc(apd, SQLSetDescField(apd, 1, SQL_DESC_CONCISE_TYPE,
                               (SQLPOINTER) SQL_C_LONG, 0));
  ok_desc(apd, SQLSetDescField(apd, 1, SQL_DESC_DATA_PTR, &inval, 0));

  ok_desc(ard, SQLSetDescField(ard, 1, SQL_DESC_CONCISE_TYPE,
                               (SQLPOINTER) SQL_C_LONG, 0));
  ok_desc(ard, SQLSetDescField(ard, 1, SQL_DESC_DATA_PTR, &outval, 0));

  ok_stmt(hstmt, SQLExecute(hstmt));
  ok_stmt(hstmt, SQLFetch(hstmt));

  is_num(outval, inval);

  return OK;
}


/*
  Basic use of explicitly allocated descriptor
*/
DECLARE_TEST(t_basic_explicit)
{
  SQLHANDLE expapd;
  SQLINTEGER result;
  SQLINTEGER impparam= 2;
  SQLINTEGER expparam= 999;

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *) "select ?", SQL_NTS));
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &result, 0, NULL));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &impparam, 0, NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  result= 0;
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(result, impparam);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* setup a new descriptor */
  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_DESC, hdbc, &expapd));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_APP_PARAM_DESC, expapd, 0));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &expparam, 0, NULL));

  ok_stmt(hstmt, SQLExecute(hstmt));

  result= 0;
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(result, expparam);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* free the descriptor, will set apd back to original on hstmt */
  ok_desc(expapd, SQLFreeHandle(SQL_HANDLE_DESC, expapd));

  ok_stmt(hstmt, SQLExecute(hstmt));

  result= 0;
  ok_stmt(hstmt, SQLFetch(hstmt));
  is_num(result, impparam);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


/*
  Test the various error scenarios possible with
  explicitly allocated descriptors
*/
DECLARE_TEST(t_explicit_error)
{
  SQLHANDLE desc1, desc2;
  SQLHANDLE expapd;
  SQLHANDLE hstmt2;

  /* TODO using an exp from a different dbc */

  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt2));
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                &desc1, 0, NULL));
  ok_stmt(hstmt2, SQLGetStmtAttr(hstmt2, SQL_ATTR_APP_ROW_DESC,
                                 &desc2, 0, NULL));
  printf("LINE:%d\n",__LINE__);

#ifndef SQL_ATTR_UNIXODBC_VERSION
  /* This call is checked by the driver manager and sometimes UnixODBC
     crashes in there.
  */

  /* can't set implicit ard from a different statement */
  expect_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                    desc2, 0), SQL_ERROR);
  is(check_sqlstate(hstmt, "HY017") == OK);
#endif
  /* can set it to the same statement */
  printf("LINE:%d\n",__LINE__);
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC, desc1, 0));
  printf("LINE:%d\n",__LINE__);

  /* can't set implementation descriptors */
  expect_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_IMP_ROW_DESC,
                                    desc1, 0), SQL_ERROR);
  printf("LINE:%d\n",__LINE__);
  is(check_sqlstate(hstmt, "HY024") == OK ||
     check_sqlstate(hstmt, "HY017") == OK);
  printf("LINE:%d\n",__LINE__);

  /*
    can't free implicit descriptors
    This crashes unixODBC 2.2.11, as it improperly frees the descriptor,
    and again tries to when freeing the statement handle.
  */
#ifdef SQL_ATTR_UNIXODBC_VERSION
    printMessage("free implicit descriptor test skipped under unixODBC 2.2.11");
#else
    expect_desc(desc1, SQLFreeHandle(SQL_HANDLE_DESC, desc1), SQL_ERROR);
    is(check_sqlstate_ex(desc1, SQL_HANDLE_DESC, "HY017") == OK);
#endif

  /* can't set apd as ard (and vice-versa) */
  printf("LINE:%d\n",__LINE__);
  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_DESC, hdbc, &expapd));
  printf("LINE:%d\n",__LINE__);
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_APP_PARAM_DESC,
                                expapd, 0)); /* this makes expapd an apd */

  printf("LINE:%d\n",__LINE__);
  expect_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                    expapd, 0), SQL_ERROR);
  printf("LINE:%d\n",__LINE__);
  is(check_sqlstate(hstmt, "HY024") == OK);

  /*
    this exposes a bug in unixODBC (2.2.12 and current as of 2007-12-14).
    Even though the above call failed, unixODBC saved this value internally
    and returns it. desc1 should *not* be the same as the explicit apd
  */
  printf("LINE:%d\n",__LINE__);
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                &desc1, 0, NULL));
  printf("LINE:%d\n",__LINE__);
  printMessage("explicit apd: %x, stmt's ard: %x", expapd, desc1);
  printf("LINE:%d\n",__LINE__);

  return OK;
}


/*
  Test that statements are handled correctly when freeing an
  explicit descriptor associated with multiple statements.
*/
DECLARE_TEST(t_mult_stmt_free)
{
#define mult_count 3
  SQLHANDLE expard, expapd;
#ifndef _WIN32
  SQLHANDLE desc;
#endif
  SQLHANDLE stmt[mult_count];
  SQLINTEGER i;
  SQLINTEGER imp_params[mult_count];
  SQLINTEGER imp_results[mult_count];
  SQLINTEGER exp_param;
  SQLINTEGER exp_result;

  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_DESC, hdbc, &expapd));
  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_DESC, hdbc, &expard));

  for (i= 0; i < mult_count; ++i)
  {
    ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &stmt[i]));
    /* we bind these now, but use at the end */
    imp_params[i]= 900 + i;
    ok_stmt(stmt[i], SQLBindParameter(stmt[i], 1, SQL_PARAM_INPUT, SQL_INTEGER,
                                      SQL_C_LONG, 0, 0, &imp_params[i], 0, NULL));
    ok_stmt(stmt[i], SQLBindCol(stmt[i], 1, SQL_C_LONG, &imp_results[i], 0, NULL));
    ok_stmt(stmt[i], SQLSetStmtAttr(stmt[i], SQL_ATTR_APP_ROW_DESC, expard, 0));
    ok_stmt(stmt[i], SQLSetStmtAttr(stmt[i], SQL_ATTR_APP_PARAM_DESC, expapd, 0));
  }

  /* this will work for all */
  ok_stmt(stmt[0], SQLBindParameter(stmt[0], 1, SQL_PARAM_INPUT, SQL_INTEGER,
                                    SQL_C_LONG, 0, 0, &exp_param, 0, NULL));
  ok_stmt(stmt[0], SQLBindCol(stmt[0], 1, SQL_C_LONG, &exp_result, 0, NULL));

  /* check that the explicit ard and apd are working */
  for (i= 0; i < mult_count; ++i)
  {
    exp_param= 200 + i;
    ok_stmt(stmt[i], SQLExecDirect(stmt[i], (SQLCHAR *)"select ?", SQL_NTS));
    ok_stmt(stmt[i], SQLFetch(stmt[i]));
    is_num(exp_result, exp_param);
    ok_stmt(stmt[i], SQLFreeStmt(stmt[i], SQL_CLOSE));
  }

  /*
    Windows ODBC DM has a bug that crashes when using a statement
    after free-ing an explicitly allocated that was associated with
    it. (This exact test was run against SQL Server and crashed in
    the exact same spot.) Tested on Windows 2003 x86 and
    Windows XP x64 both w/MDAC 2.8.
   */
#ifndef _WIN32
  /*
    now free the explicit apd+ard and the stmts should go back to
    their implicit descriptors
  */
  ok_desc(expard, SQLFreeHandle(SQL_HANDLE_DESC, expard));
  ok_desc(expapd, SQLFreeHandle(SQL_HANDLE_DESC, expapd));

  /* check that the original values worked */
  for (i= 0; i < mult_count; ++i)
  {
    ok_stmt(stmt[i], SQLExecDirect(stmt[i], (SQLCHAR *)"select ?", SQL_NTS));
    ok_stmt(stmt[i], SQLFetch(stmt[i]));
    ok_stmt(stmt[i], SQLFreeStmt(stmt[i], SQL_CLOSE));
  }

  for (i= 0; i < mult_count; ++i)
  {
    is_num(imp_results[i], imp_params[i]);
  }

  /*
    bug in unixODBC - it still returns the explicit descriptor
    These should *not* be the same
  */
  ok_stmt(hstmt, SQLGetStmtAttr(stmt[0], SQL_ATTR_APP_ROW_DESC,
                               &desc, SQL_IS_POINTER, NULL));
  printMessage("explicit ard = %x, stmt[0]'s implicit ard = %x", expard, desc);
#endif

  return OK;
#undef mult_count
}


/*
  Test that when we set a stmt's ard from an explicit descriptor to
  null, it uses the implicit descriptor again. Also the statement
  will disassociate itself from the explicit descriptor.
*/
DECLARE_TEST(t_set_null_use_implicit)
{
  SQLHANDLE expard, hstmt1;
  SQLINTEGER imp_result= 0, exp_result= 0;

  if(using_unixodbc_version(henv, "2.2.11"))
    skip("unixODBC 2.2.11 doesn't handle resetting statement "
         "descriptors correctly");

  /*
    we use a separate statement handle to test that it correctly
    disassociates itself from the descriptor
  */
  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt1));

  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_DESC, hdbc, &expard));

  /* this affects the implicit ard */
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_LONG, &imp_result, 0, NULL));

  /* set the explicit ard */
  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_APP_ROW_DESC, expard, 0));

  /* this affects the expard */
  ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_LONG, &exp_result, 0, NULL));

  /* set it to null, getting rid of the expard */
  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_APP_ROW_DESC,
                                SQL_NULL_HANDLE, 0));

  ok_sql(hstmt1, "select 1");
  ok_stmt(hstmt1, SQLFetch(hstmt1));

  is_num(exp_result, 0);
  is_num(imp_result, 1);

  ok_stmt(hstmt1, SQLFreeHandle(SQL_HANDLE_STMT, hstmt1));

  /* if stmt disassociation failed, this will crash */
  ok_desc(expard, SQLFreeHandle(SQL_HANDLE_DESC, expard));

  return OK;
}


/*
  Test free-ing a statement that has an explicitely allocated
  descriptor associated with it. If this test fails, it will
  crash due to the statement not being disassociated with the
  descriptor correctly.
*/
DECLARE_TEST(t_free_stmt_with_exp_desc)
{
  SQLHANDLE expard, hstmt1;
  SQLINTEGER imp_result= 0, exp_result= 0;

  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_DESC, hdbc, &expard));
  ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt1));

  /* set the explicit ard */
  ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_APP_ROW_DESC, expard, 0));

  /* free the statement, THEN the descriptors */
  ok_stmt(hstmt1, SQLFreeHandle(SQL_HANDLE_STMT, hstmt1));
  ok_desc(expard, SQLFreeHandle(SQL_HANDLE_DESC, expard));

  return OK;
}


/*
  Bug #41081, Unable to retreive null SQL_NUMERIC with ADO.
  It's setting SQL_DESC_PRECISION after SQLBindCol(). We were
  "unbinding" incorrectly, by not only clearing data_ptr, but
  also octet_length_ptr and indicator_ptr.
*/
DECLARE_TEST(t_bug41081)
{
  SQLHANDLE ard;
  SQLINTEGER res;
  SQLLEN ind;
  SQLPOINTER data_ptr, octet_length_ptr;
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
                                &ard, SQL_IS_POINTER, NULL));
  ok_sql(hstmt, "select 1");
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_LONG, &res, 0, &ind));
  /* cause to unbind */
  ok_desc(ard, SQLSetDescField(ard, 1, SQL_DESC_PRECISION, (SQLPOINTER) 10,
                               SQL_IS_SMALLINT));
  /* check proper unbinding */
  ok_desc(ard, SQLGetDescField(ard, 1, SQL_DESC_DATA_PTR,
                               &data_ptr, SQL_IS_POINTER, NULL));
  ok_desc(ard, SQLGetDescField(ard, 1, SQL_DESC_OCTET_LENGTH_PTR,
                               &octet_length_ptr, SQL_IS_POINTER, NULL));
  is(data_ptr == NULL);
  is(octet_length_ptr == &ind);
  return OK;
}


/*
  Bug #44576 - SQL_DESC_DATETIME_INTERVAL_CODE not set on descriptor
*/
DECLARE_TEST(t_bug44576)
{
  SQLSMALLINT interval_code;
  SQLSMALLINT concise_type;
  SQLHANDLE ird;
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_IMP_ROW_DESC, &ird, 0, NULL));
  ok_sql(hstmt, "select cast('2000-10-10' as date)");
  ok_desc(ird, SQLGetDescField(ird, 1, SQL_DESC_CONCISE_TYPE, &concise_type,
                               SQL_IS_SMALLINT, NULL));
  ok_desc(ird, SQLGetDescField(ird, 1, SQL_DESC_DATETIME_INTERVAL_CODE,
                               &interval_code, SQL_IS_SMALLINT, NULL));
  is_num(concise_type, SQL_TYPE_DATE);
  is_num(interval_code, SQL_CODE_DATE);
  return OK;
}


/* If no default database is selected for the connection, call of SQLColumns
   causes error "Unknown database 'null'" */
DECLARE_TEST(t_desc_curcatalog)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR conn_in[512];
  SQLHANDLE ird;

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, USE_DRIVER,
                                        NULL, NULL, "", NULL));

  ok_sql(hstmt1, "select 10 AS no_catalog_column");

  ok_stmt(hstmt1, SQLGetStmtAttr(hstmt1, SQL_ATTR_IMP_ROW_DESC, &ird, 0, NULL));
  ok_desc(ird, SQLGetDescField(ird, 1, SQL_DESC_CATALOG_NAME, conn_in,
                               sizeof(conn_in), NULL));


  is(conn_in==NULL);
  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/*
  Bug #18636600 - SQLGetData with invalid column number crashes
*/
DECLARE_TEST(t_bug18636600)
{
  char buf[255];
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_USE_BOOKMARKS, (SQLPOINTER)SQL_UB_VARIABLE, 0));
  ok_sql(hstmt, "select 1");
  ok_stmt(hstmt, SQLFetch(hstmt));
  expect_stmt(hstmt, SQLGetData(hstmt, -1, SQL_C_VARBOOKMARK, buf, sizeof(buf), NULL),
              SQL_ERROR);
  return OK;
}


/*
Bug #18641633 - Assert failure in SQLSetDescField() API
*/
DECLARE_TEST(t_bug18641633)
{
  char buf[255];
  SQLHANDLE ard = NULL;
  ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_ATTR_APP_ROW_DESC,
    &ard, SQL_IS_POINTER, NULL));
  ok_sql(hstmt, "select 1");
  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, buf, sizeof(buf), NULL));
  /* Result does not matter, it should not crash */
  SQLSetDescField(ard, 1, SQL_DESC_COUNT, (SQLPOINTER)0, SQL_IS_SMALLINT);
  return OK;
}


DECLARE_TEST(dummy_test)
{
  return OK;
}

BEGIN_TESTS
  ADD_TODO(t_desc_paramset)
  ADD_TEST(t_desc_set_error)
  ADD_TEST(t_sqlbindcol_count_reset)
  ADD_TEST(t_desc_default_type)
  ADD_TEST(t_basic_explicit)
#ifndef USE_IODBC
  ADD_TEST(t_explicit_error)
#endif
  ADD_TEST(t_mult_stmt_free)
  ADD_TEST(t_set_null_use_implicit)
  ADD_TEST(t_free_stmt_with_exp_desc)
  ADD_TEST(t_bug41081)
  ADD_TEST(t_bug44576)
  ADD_TEST(t_bug18641633)
  ADD_TEST(t_bug18636600)
  // ADD_TODO(t_desc_curcatalog) TODO: Fix
  ADD_TEST(dummy_test)
END_TESTS


RUN_TESTS


