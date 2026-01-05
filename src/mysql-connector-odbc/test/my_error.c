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

DECLARE_TEST(t_get_diag_all)
{
  SQLCHAR buf[1024];
  SQLSMALLINT str_len_ptr = 0;
  SQLLEN data1 = 0;
  SQLINTEGER data2 = 0;
  SQLRETURN data3 = 0;

  expect_sql(hstmt, "This is an invalid Query! (odbc test)", SQL_ERROR);
  printf("** Start SQLGetDiagField!\n");

  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_CURSOR_ROW_COUNT, &data1,
                  sizeof(data1), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_DYNAMIC_FUNCTION, buf,
                  sizeof(buf), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_DYNAMIC_FUNCTION_CODE, &data2,
                  sizeof(data2), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_NUMBER, &data2,
                  sizeof(data2), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_RETURNCODE, &data3,
                  sizeof(data3), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_ROW_COUNT, &data1,
                  sizeof(data1), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_CLASS_ORIGIN, buf,
                  sizeof(buf), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_COLUMN_NUMBER, &data2,
                  sizeof(data2), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_CONNECTION_NAME, buf,
                  sizeof(buf), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_MESSAGE_TEXT, buf,
                  sizeof(buf), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_ROW_NUMBER, &data1,
                  sizeof(data1), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_SERVER_NAME, buf,
                  sizeof(buf), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_SQLSTATE, buf,
                  sizeof(buf), &str_len_ptr);
  SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_SUBCLASS_ORIGIN, buf,
                  sizeof(buf), &str_len_ptr);

  return OK;
}


DECLARE_TEST(t_odbc3_error)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLINTEGER ov_version;
  /*SQLCHAR dm_version[6];*/

  is(OK == alloc_basic_handles(&henv1, &hdbc1, &hstmt1));

  /*SQLGetInfo(hdbc1, SQL_DM_VER, (SQLPOINTER)dm_version, 6, 0);*/

  ok_env(henv1, SQLGetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)&ov_version, 0, 0));
  /* "For standards-compliant applications, SQLAllocHandle is mapped to SQLAllocHandleStd
     at compile time. The difference between these two functions is that SQLAllocHandleStd
     sets the SQL_ATTR_ODBC_VERSION environment attribute to SQL_OV_ODBC3 when it is called
     with the HandleType argument set to SQL_HANDLE_ENV. This is done because standards-compliant
     applications are always ODBC 3.x applications." */
  is_num(ov_version, SQL_OV_ODBC3);

  expect_sql(hstmt1, "SELECT * FROM non_existing_table", SQL_ERROR);
  if (check_sqlstate(hstmt1, "42S02") != OK)
    return FAIL;

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_error");
  ok_sql(hstmt1, "CREATE TABLE t_error (id INT)");

  expect_sql(hstmt1, "CREATE TABLE t_error (id INT)", SQL_ERROR);
  if (check_sqlstate(hstmt1, "42S01") != OK)
    return FAIL;

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_error");
  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


DECLARE_TEST(t_odbc2_error)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLINTEGER ov_version;

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, &henv1));
  ok_env(henv1, SQLSetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)SQL_OV_ODBC2, 0));

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_DBC, henv1, &hdbc1));

  ok_env(henv1, SQLGetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)&ov_version, 0, 0));
  is_num(ov_version, SQL_OV_ODBC2);

  ok_con(hdbc1, SQLConnect(hdbc1, mydsn, SQL_NTS, myuid, SQL_NTS,
                           mypwd, SQL_NTS));

  ok_con(hdbc1, SQLAllocHandle(SQL_HANDLE_STMT, hdbc1, &hstmt1));

  expect_sql(hstmt1, "SELECT * FROM non_existing_table", SQL_ERROR);
  if (check_sqlstate(hstmt1, "S0002") != OK)
    return FAIL;

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_error");
  ok_sql(hstmt1, "CREATE TABLE t_error (id INT)");

  expect_sql(hstmt1, "CREATE TABLE t_error (id INT)", SQL_ERROR);
  if (check_sqlstate(hstmt1, "S0001") != OK)
    return FAIL;

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  expect_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_ENABLE_AUTO_IPD,
                                     (SQLPOINTER)SQL_TRUE, 0),
              SQL_ERROR);
  if (check_sqlstate(hstmt1, "S1C00") != OK)
    return FAIL;

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_sql(hstmt1, "DROP TABLE IF EXISTS t_error");

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


#ifndef USE_IODBC
/* Testing ability of application to set 3.8 ODBC version */
DECLARE_TEST(t_odbc3_80)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLINTEGER ov_version;

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, &henv1));
  /* ODBC 3.8 applications should use ... SQLSetEnvAttr to set the SQL_ATTR_ODBC_VERSION environment
     attribute to SQL_OV_ODBC_3_80*/
  ok_env(henv1, SQLSetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)SQL_OV_ODBC3_80, 0));

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_DBC, henv1, &hdbc1));

  ok_env(henv1, SQLGetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)&ov_version, 0, 0));
  is_num(ov_version, SQL_OV_ODBC3_80);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}
#endif

DECLARE_TEST(t_diagrec)
{
  SQLCHAR   sqlstate[6]= {0};
  SQLCHAR   message[255]= {0};
  SQLINTEGER native_err= 0;
  SQLSMALLINT msglen= 0;

  expect_sql(hstmt, "DROP TABLE t_odbc3_non_existent_table", SQL_ERROR);

#if UNIXODBC_BUG_FIXED
  /*
   This should report no data found, but unixODBC doesn't even pass this
   down to the driver.
  */
  expect_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 2, sqlstate,
                                   &native_err, message, 0, &msglen),
              SQL_NO_DATA_FOUND);
#endif

  ok_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate,
                               &native_err, message, 255, &msglen));

  /* MSSQL returns SQL_SUCCESS in similar situations */
  expect_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate,
                                   &native_err, message, 0, &msglen),
              SQL_SUCCESS);

  expect_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate,
                                   &native_err, message, 10, &msglen),
              SQL_SUCCESS_WITH_INFO);

  expect_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate,
                                   &native_err, message, -1, &msglen),
              SQL_ERROR);

  return OK;
}


DECLARE_TEST(t_warning)
{
  SQLCHAR    szData[20];
  SQLLEN     pcbValue;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_warning");
  ok_sql(hstmt, "CREATE TABLE t_warning (col2 CHAR(20))");
  ok_sql(hstmt, "INSERT INTO t_warning VALUES ('Venu Anuganti')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CONCURRENCY,
                                (SQLPOINTER)SQL_CONCUR_ROWVER, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_TYPE,
                                (SQLPOINTER)SQL_CURSOR_KEYSET_DRIVEN, 0));

  /* ignore all columns */
  ok_sql(hstmt, "SELECT * FROM t_warning");

  ok_stmt(hstmt, SQLFetch(hstmt));

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szData, 4, &pcbValue),
              SQL_SUCCESS_WITH_INFO);
  is_str(szData, "Ven", 3);
  is_num(pcbValue, 13);

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szData, 4, &pcbValue),
              SQL_SUCCESS_WITH_INFO);
  is_str(szData, "u A", 3);
  is_num(pcbValue, 10);

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szData, 4, &pcbValue),
              SQL_SUCCESS_WITH_INFO);
  is_str(szData, "nug", 3);
  is_num(pcbValue, 7);

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szData, 4, &pcbValue),
              SQL_SUCCESS_WITH_INFO);
  is_str(szData, "ant", 3);
  is_num(pcbValue, 4);

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szData, 4, &pcbValue),
              SQL_SUCCESS);
  is_str(szData, "i", 1);
  is_num(pcbValue, 1);

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_NO_DATA_FOUND);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_warning");

  return OK;
}


DECLARE_TEST(t_bug3456)
{
  SQLINTEGER connection_id;
  char buf[100];
  SQLHENV henv2;
  SQLHDBC  hdbc2;
  SQLHSTMT hstmt2;

  /* Create a new connection that we deliberately will kill */
  alloc_basic_handles(&henv2, &hdbc2, &hstmt2);
  ok_sql(hstmt2, "SELECT connection_id()");
  ok_stmt(hstmt2, SQLFetch(hstmt2));
  connection_id= my_fetch_int(hstmt2, 1);
  ok_stmt(hstmt2, SQLFreeStmt(hstmt2, SQL_CLOSE));

  /* From another connection, kill the connection created above */
  sprintf(buf, "KILL %d", connection_id);
  ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR *)buf, SQL_NTS));

  /* Now check that the connection killed returns the right SQLSTATE */
  expect_sql(hstmt2, "SELECT connection_id()", SQL_ERROR);

  return check_sqlstate(hstmt2, "08S01");
}


/*
 * Bug #16224: Calling SQLGetDiagField with RecNumber 0,DiagIdentifier
 *             NOT 0 returns SQL_ERROR
 */
DECLARE_TEST(t_bug16224)
{
  SQLINTEGER diagcnt;

  expect_sql(hstmt, "This is an invalid Query! (odbc test)", SQL_ERROR);

  ok_stmt(hstmt, SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 0,
                                 SQL_DIAG_NUMBER, &diagcnt,
                                 SQL_IS_INTEGER, NULL));
  is_num(diagcnt, 1);

  return OK;
}


/*
 * Test that binding invalid column returns the appropriate error
 */
DECLARE_TEST(bind_invalidcol)
{
  SQLCHAR dummy[10];
  ok_sql(hstmt, "select 1,2,3,4");

  /* test out of range column number */
  expect_stmt(hstmt, SQLBindCol(hstmt, 10, SQL_C_CHAR, "", 4, NULL), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "07009"), OK);

  /* test (unsupported) bookmark column number */
  expect_stmt(hstmt, SQLBindCol(hstmt, 0, SQL_C_BOOKMARK, "", 4, NULL),
              SQL_ERROR);
  is_num(check_sqlstate(hstmt, "07009"), OK);

  /* SQLDescribeCol() */
  expect_stmt(hstmt, SQLDescribeCol(hstmt, 0, dummy, sizeof(dummy), NULL, NULL,
                                    NULL, NULL, NULL), SQL_ERROR);
  /* Older versions of iODBC return the wrong result (S1002) here. */
  is(check_sqlstate(hstmt, "07009") == OK ||
     check_sqlstate(hstmt, "S1002") == OK);

  expect_stmt(hstmt, SQLDescribeCol(hstmt, 5, dummy, sizeof(dummy), NULL,
                                    NULL, NULL, NULL, NULL), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "07009"), OK);

  /* SQLColAttribute() */
  expect_stmt(hstmt, SQLColAttribute(hstmt, 0, SQL_DESC_NAME, NULL, 0,
                                     NULL, NULL), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "07009"), OK);

  expect_stmt(hstmt, SQLColAttribute(hstmt, 7, SQL_DESC_NAME, NULL, 0,
                                     NULL, NULL), SQL_ERROR);
  is_num(check_sqlstate(hstmt, "07009"), OK);

  return OK;
}


/*
 * Test the error given when not enough params are bound to execute
 * the statement.
 */
DECLARE_TEST(bind_notenoughparam1)
{
  SQLINTEGER i= 0;
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"select ?, ?", SQL_NTS));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &i, 0, NULL));
  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);
  return check_sqlstate(hstmt, "07001");
}


/*
 * Test the error given when not enough params are bound to execute
 * the statement, also given that a pre-execute happens (due to calling
 * SQLNumResultCols).
 */
DECLARE_TEST(bind_notenoughparam2)
{
  SQLINTEGER i= 0;
  SQLSMALLINT cols= 0;
  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)"select ?, ?", SQL_NTS));

  /* trigger pre-execute */
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &cols));
  is_num(cols, 2);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_LONG,
                                  SQL_INTEGER, 0, 0, &i, 0, NULL));
  expect_stmt(hstmt, SQLExecute(hstmt), SQL_ERROR);
  return check_sqlstate(hstmt, "07001");
}


/*
 * Test that calling SQLGetData() without a nullind ptr
 * when the data is null returns an error.
 */
DECLARE_TEST(getdata_need_nullind)
{
  SQLINTEGER i;
  ok_sql(hstmt, "select 1 as i , null as j ");
  ok_stmt(hstmt, SQLFetch(hstmt));

  /* that that nullind ptr is ok when data isn't null */
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_LONG, &i, 0, NULL));

  /* now that it's an error */
  expect_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_LONG, &i, 0, NULL),
              SQL_ERROR);
  return check_sqlstate(hstmt, "22002");
}


/*
   Handle-specific tests for env and dbc diagnostics
*/
DECLARE_TEST(t_handle_err)
{
  SQLHANDLE henv1, hdbc1;

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_ENV, NULL, &henv1));
  ok_env(henv1, SQLSetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)SQL_OV_ODBC3, SQL_IS_INTEGER));
  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_DBC, henv1, &hdbc1));

  /* we have to connect for the DM to pass the calls to the driver */
  ok_con(hdbc1, SQLConnect(hdbc1, mydsn, SQL_NTS, myuid, SQL_NTS,
                           mypwd, SQL_NTS));

  expect_env(henv1, SQLSetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
                                  (SQLPOINTER)SQL_OV_ODBC3, 0), SQL_ERROR);
  is_num(check_sqlstate_ex(henv1, SQL_HANDLE_ENV, "HY010"), OK);

  expect_dbc(hdbc1, SQLSetConnectAttr(hdbc1, SQL_ATTR_ASYNC_ENABLE,
                                      (SQLPOINTER)SQL_ASYNC_ENABLE_ON,
                                      SQL_IS_INTEGER), SQL_SUCCESS_WITH_INFO);
  is_num(check_sqlstate_ex(hdbc1, SQL_HANDLE_DBC, "01S02"), OK);

  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeHandle(SQL_HANDLE_DBC, hdbc1));
  ok_env(henv1, SQLFreeHandle(SQL_HANDLE_ENV, henv1));

  return OK;
}


DECLARE_TEST(sqlerror)
{
  SQLCHAR message[SQL_MAX_MESSAGE_LENGTH + 1];
  SQLCHAR sqlstate[SQL_SQLSTATE_SIZE + 1];
  SQLINTEGER error;
  SQLSMALLINT len;

  expect_sql(hstmt, "SELECT * FROM tabledoesnotexist", SQL_ERROR);

  ok_stmt(hstmt, SQLError(henv, hdbc, hstmt, sqlstate, &error,
                          message, sizeof(message), &len));

  /* Message has been consumed. */
  expect_stmt(hstmt, SQLError(henv, hdbc, hstmt, sqlstate, &error,
                              message, sizeof(message), &len),
              SQL_NO_DATA_FOUND);

  /* But should still be available using SQLGetDiagRec. */
  ok_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate, &error,
                               message, sizeof(message), &len));

  return OK;
}


/**
 Bug #27158: MyODBC 3.51/ ADO cmd option adCmdUnknown won't work for tables - regression
*/
DECLARE_TEST(t_bug27158)
{
  expect_sql(hstmt, "{ CALL test.does_not_exist }", SQL_ERROR);
  return check_sqlstate(hstmt, "42000");
}


DECLARE_TEST(t_bug13542600)
{
  SQLINTEGER i;

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "select 1 as i , null as j ");
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_LONG, &i, 0, NULL));

  expect_stmt(hstmt, SQLFetch(hstmt), SQL_ERROR);

  return check_sqlstate(hstmt, "22002");
}


DECLARE_TEST(t_bug14285620)
{
  SQLSMALLINT data_type, dec_digits, nullable, cblen;
  SQLUINTEGER info;
  SQLULEN col_size;
  SQLINTEGER timeout= 20, cbilen;
  SQLCHAR szData[255]={0};
  SQLRETURN rc = 0;

  /* Numeric attribute */
  expect_dbc(hdbc, SQLGetConnectAttr(hdbc, SQL_ATTR_LOGIN_TIMEOUT, NULL, 0, NULL), SQL_SUCCESS);
  expect_dbc(hdbc, SQLGetConnectAttr(hdbc, SQL_ATTR_LOGIN_TIMEOUT, &timeout, 0, NULL), SQL_SUCCESS);
  is_num(timeout, 0);

  /* Character attribute */
  /*
    In this particular case MSSQL always returns SQL_SUCCESS_WITH_INFO
    apparently because the driver manager always provides the buffer even
    when the client application passes NULL
  */
#ifdef _WIN32
  /*
    This check is only relevant to Windows Driver Manager
    Just make sure it doesn't crash
  */
  rc = SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, NULL, 0, NULL);
  if (rc != SQL_SUCCESS && rc != SQL_SUCCESS_WITH_INFO)
    return FAIL;
#endif
  rc = SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, szData, 0, NULL);
  if (rc != SQL_SUCCESS && rc != SQL_SUCCESS_WITH_INFO)
    return FAIL;

  /*
  MSDN Says about the last parameter &cblen for SQLGetInfo,
  functions:

     If InfoValuePtr is NULL, StringLengthPtr will still return the
     total number of bytes (excluding the null-termination character
     for character data) available to return in the buffer pointed
     to by InfoValuePtr.

     http://msdn.microsoft.com/en-us/library/ms710297%28v=vs.85%29
  */

  expect_dbc(hdbc, SQLGetInfo(hdbc, SQL_AGGREGATE_FUNCTIONS, NULL, 0, NULL), SQL_SUCCESS);
  expect_dbc(hdbc, SQLGetInfo(hdbc, SQL_AGGREGATE_FUNCTIONS, &info, 0, &cblen), SQL_SUCCESS);
  is_num(cblen, 4);

  is_num(info, (SQL_AF_ALL | SQL_AF_AVG | SQL_AF_COUNT | SQL_AF_DISTINCT |
             SQL_AF_MAX | SQL_AF_MIN | SQL_AF_SUM));

  /* Get database name for further checks */
  expect_dbc(hdbc, SQLGetInfo(hdbc, SQL_DATABASE_NAME, szData, sizeof(szData), NULL), SQL_SUCCESS);
  expect_dbc(hdbc, SQLGetInfo(hdbc, SQL_DATABASE_NAME, NULL, 0, &cblen), SQL_SUCCESS);

  if (cblen != strlen(szData)*sizeof(SQLWCHAR) && cblen != strlen(szData))
    return FAIL;

  expect_dbc(hdbc, SQLGetInfo(hdbc, SQL_DATABASE_NAME, szData, 0, NULL), SQL_SUCCESS);

  /* Get the native string for further checks */
  expect_dbc(hdbc, SQLNativeSql(hdbc, "SELECT 10", SQL_NTS, szData, sizeof(szData), NULL), SQL_SUCCESS);
  expect_dbc(hdbc, SQLNativeSql(hdbc, "SELECT 10", SQL_NTS, NULL, 0, &cbilen), SQL_SUCCESS);

  /* Do like MSSQL, which does calculate as char_count*sizeof(SQLWCHAR) */
  is_num(cbilen, strlen(szData));

  rc = SQLNativeSql(hdbc, "SELECT 10", SQL_NTS, szData, 0, NULL);
  if (rc != SQL_SUCCESS && rc != SQL_SUCCESS_WITH_INFO)
    return FAIL;

  /* Get the cursor name for further checks */
  expect_stmt(hstmt, SQLGetCursorName(hstmt, szData, sizeof(szData), NULL), SQL_SUCCESS);
  expect_stmt(hstmt, SQLGetCursorName(hstmt, NULL, 0, &cblen), SQL_SUCCESS);

  /* Do like MSSQL, which does calculate as char_count*sizeof(SQLWCHAR) */
  is_num(cblen, strlen(szData));

  expect_stmt(hstmt, SQLGetCursorName(hstmt, szData, 0, NULL), SQL_SUCCESS_WITH_INFO);

  expect_stmt(hstmt, SQLExecDirect(hstmt, "ERROR SQL QUERY", SQL_NTS), SQL_ERROR);

  expect_stmt(hstmt, SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 1, SQL_DIAG_SQLSTATE, NULL, 0, NULL), SQL_SUCCESS);

  {
    SQLCHAR sqlstate[30], message[255];
    SQLINTEGER native_error= 0;
    SQLSMALLINT text_len= 0;
    /* try with the NULL pointer for Message */
    expect_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate,
                                    &native_error, NULL, 0, &cblen), SQL_SUCCESS);
    /* try with the non-NULL pointer for Message */
    expect_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt, 1, sqlstate,
                                    &native_error, message, 0, NULL), SQL_SUCCESS);
  }

  SQLExecDirect(hstmt, "drop table bug14285620", SQL_NTS);

  ok_stmt(hstmt, SQLExecDirect(hstmt, "CREATE TABLE bug14285620 (id INT)", SQL_NTS));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "INSERT INTO bug14285620 (id) VALUES (1)", SQL_NTS));
  ok_stmt(hstmt, SQLExecDirect(hstmt, "SELECT * FROM bug14285620", SQL_NTS));

  expect_stmt(hstmt, SQLDescribeCol(hstmt, 1, NULL, 0, NULL, &data_type, &col_size, &dec_digits, &nullable), SQL_SUCCESS);
  expect_stmt(hstmt, SQLDescribeCol(hstmt, 1, szData, 0, &cblen, &data_type, &col_size, &dec_digits, &nullable), SQL_SUCCESS_WITH_INFO);

  expect_stmt(hstmt, SQLColAttribute(hstmt,1, SQL_DESC_TYPE, NULL, 0, NULL, NULL), SQL_SUCCESS);
  expect_stmt(hstmt, SQLColAttribute(hstmt,1, SQL_DESC_TYPE, &data_type, 0, NULL, NULL), SQL_SUCCESS);

  expect_stmt(hstmt, SQLColAttribute(hstmt,1, SQL_DESC_TYPE_NAME, NULL, 0, NULL, NULL), SQL_SUCCESS);
  expect_stmt(hstmt, SQLColAttribute(hstmt,1, SQL_DESC_TYPE_NAME, szData, 0, NULL, NULL), SQL_SUCCESS_WITH_INFO);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}

/*
  Bug49466: SQLMoreResults does not set statement errors correctly
  Wrong error message returned from SQLMoreResults
*/
DECLARE_TEST(t_bug49466)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR message[SQL_MAX_MESSAGE_LENGTH + 1];
  SQLCHAR sqlstate[SQL_SQLSTATE_SIZE + 1];
  SQLINTEGER error;
  SQLSMALLINT len;

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL,
                                        "OPTION=67108864"));//;NO_SSPS=1"));

  ok_stmt(hstmt1, SQLExecDirect(hstmt1, "SELECT 100; CALL t_bug49466proc()", SQL_NTS));

  ok_stmt(hstmt1, SQLFetch(hstmt1));
  is_num(my_fetch_int(hstmt1, 1), 100);

  SQLMoreResults(hstmt1);

  ok_stmt(hstmt, SQLGetDiagRec(SQL_HANDLE_STMT, hstmt1, 1, sqlstate, &error,
                               message, sizeof(message), &len));
  is_num(error, 1305);
  is(strstr((char *)message, "t_bug49466proc does not exist"));

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


DECLARE_TEST(t_passwordexpire)
{
  SQLHDBC hdbc1;
  SQLHSTMT hstmt1;

  if (!mysql_min_version(hdbc, "5.6.6", 5))
  {
    skip("The server does not support tested functionality(expired password)");
  }

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_password_expire");
  SQLExecDirect(hstmt, (SQLCHAR *)"DROP USER IF EXISTS t_pwd_expire", SQL_NTS);

  ok_sql(hstmt, "CREATE USER t_pwd_expire IDENTIFIED BY 'foo'");
  ok_sql(hstmt, "GRANT ALL ON *.* TO t_pwd_expire");
  ok_sql(hstmt, "ALTER USER t_pwd_expire PASSWORD EXPIRE");

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));

  /* Expecting error without OPT_CAN_HANDLE_EXPIRED_PASSWORDS */
  expect_dbc(hdbc1, get_connection(&hdbc1, NULL, "t_pwd_expire", "foo",
             NULL, NULL), SQL_ERROR);

  {
    SQLCHAR sql_state[6];
    SQLINTEGER  err_code= 0;
    SQLCHAR     err_msg[SQL_MAX_MESSAGE_LENGTH]= {0};
    SQLSMALLINT err_len= 0;

    SQLGetDiagRec(SQL_HANDLE_DBC, hdbc1, 1, sql_state, &err_code, err_msg,
                  SQL_MAX_MESSAGE_LENGTH - 1, &err_len);

    /* ER_MUST_CHANGE_PASSWORD = 1820, ER_MUST_CHANGE_PASSWORD_LOGIN = 1862 */
    if (strncmp(sql_state, "08004", 5) != 0 || !(err_code == 1820 || err_code == 1862))
    {
      printMessage("%s %d %s", sql_state, err_code, err_msg);
      is(FALSE);
    }
  }

  /* Expecting error as password has not been reset */
  ok_con(hdbc1, get_connection(&hdbc1, NULL, "t_pwd_expire", "foo",
                                NULL, "CAN_HANDLE_EXP_PWD=1;INITSTMT={set password='bar'}"));

  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  /* Just to verify that we got normal connection */
  ok_sql(hstmt1, "select 1");

  ok_con(hdbc1, SQLFreeStmt(hstmt1, SQL_DROP));

  ok_con(hdbc1, SQLDisconnect(hdbc1));

  /* Checking we can get connection with new credentials */
  ok_con(hdbc1, get_connection(&hdbc1, mydsn, "t_pwd_expire", "bar", NULL,
                               NULL));
  ok_con(hdbc1, SQLAllocStmt(hdbc1, &hstmt1));

  /* Also verifying that we got normal connection */
  ok_sql(hstmt1, "select 1");

  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_DROP));
  ok_con(hdbc1, SQLDisconnect(hdbc1));
  ok_con(hdbc1, SQLFreeConnect(hdbc1));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_password_expire");
  ok_sql(hstmt, "DROP USER t_pwd_expire");

  return OK;
}

/*
  Bug#16445091: CLEARTEXT AUTHENTICATION NOT PRESENT IN ODBC
*/
DECLARE_TEST(t_cleartext_password)
{
  SQLHDBC hdbc1;
  SQLCHAR sql_state[6];
  SQLINTEGER  err_code= 0;
  SQLCHAR     err_msg[SQL_MAX_MESSAGE_LENGTH]= {0};
  SQLSMALLINT err_len= 0;
  unsigned int major1= 0, minor1= 0, build1= 0;

  if (!mysql_min_version(hdbc, "5.5.16", 6) )
  {
    skip("The server does not support tested functionality(Cleartext Auth)");
  }

  SQLExecDirect(hstmt, (SQLCHAR *)"DROP USER 't_ct_user'@'%'", SQL_NTS);

  if (!SQL_SUCCEEDED(SQLExecDirect(hstmt,
            "GRANT ALL ON *.* TO "
            "'t_ct_user'@'%' IDENTIFIED WITH "
            "'authentication_pam'", SQL_NTS)))
  {
    skip("The authentication_pam plugin not loaded");
  }

  ok_env(henv, SQLAllocConnect(henv, &hdbc1));

  /*
    Expecting error CR_AUTH_PLUGIN_CANNOT_LOAD_ERROR
    without option ENABLE_CLEARTEXT_PLUGIN
  */
  if(!SQL_SUCCEEDED(get_connection(&hdbc1, mydsn, "t_ct_user", "t_ct_pass",
                        mydb, NULL)))
  {
    SQLGetDiagRec(SQL_HANDLE_DBC, hdbc1, 1, sql_state, &err_code, err_msg,
                  SQL_MAX_MESSAGE_LENGTH - 1, &err_len);

    printMessage("%s %d %s", sql_state, err_code, err_msg);
    if ((strncmp(sql_state, "08004", 5) != 0 || err_code != 2059))
    {
      return FAIL;
    }
  }

  /*
    Expecting error other then CR_AUTH_PLUGIN_CANNOT_LOAD_ERROR
    as option ENABLE_CLEARTEXT_PLUGIN is used
  */
  if(!SQL_SUCCEEDED(get_connection(&hdbc1, mydsn, "t_ct_user", "t_ct_pass",
                        mydb, "ENABLE_CLEARTEXT_PLUGIN=1")))
  {
    SQLGetDiagRec(SQL_HANDLE_DBC, hdbc1, 1, sql_state, &err_code, err_msg,
                  SQL_MAX_MESSAGE_LENGTH - 1, &err_len);
    printMessage("%s %d %s", sql_state, err_code, err_msg);

    if ((strncmp(sql_state, "08004", 5) == 0 && err_code == 2059))
    {
      return FAIL;
    }
  }

  ok_sql(hstmt, "DROP USER 't_ct_user'@'%'");

  return OK;
}


DECLARE_TEST(t_bug11750296)
{
  SQLLEN rowCount;

  ok_sql(hstmt, "DROP TABLE IF EXISTS bug11750296");
  ok_sql(hstmt, "CREATE TABLE bug11750296(Col Integer)");
  ok_sql(hstmt, "INSERT INTO bug11750296 VALUES(1),(2),(3)");

  ok_stmt(hstmt, SQLGetDiagField(SQL_HANDLE_STMT, hstmt, 0,
                                 SQL_DIAG_ROW_COUNT, &rowCount,
                                 0, NULL));
  is_num(rowCount, 3);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_sql(hstmt, "DROP TABLE bug11750296");

  return OK;
}


DECLARE_TEST(t_bug25671389)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  SQLINTEGER connection_id;
  SQLCHAR buf[255];
  SQLHSTMT hstmt2;

  SQLRETURN rc = SQL_ERROR;
  SQLCHAR sqlState[6] = { '\0' };
  SQLCHAR eMsg[SQL_MAX_MESSAGE_LENGTH] = { '\0' };
  SQLINTEGER nError = 0;
  SQLSMALLINT msgLen = 0;

  is(OK == alloc_basic_handles(&henv1, &hdbc1, &hstmt1));
  /* The bug is repeatable when limit is set and two STMTs exist */
  ok_con(hdbc1, SQLAllocHandle(SQL_HANDLE_STMT, hdbc1, &hstmt2));
  ok_stmt(hstmt2, SQLSetStmtAttr(hstmt2, SQL_ATTR_MAX_ROWS, (SQLPOINTER)100, 0));

  ok_sql(hstmt1, "SELECT connection_id()");
  ok_stmt(hstmt1, SQLFetch(hstmt1));
  connection_id = my_fetch_int(hstmt1, 1);
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  /* From another connection, kill the connection created above */
  sprintf(buf, "KILL %d", connection_id);
  ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR *)buf, SQL_NTS));

  /* Now check that the connection killed returns the right SQLSTATE */
  expect_sql(hstmt2, "SELECT connection_id()", SQL_ERROR);

  rc = SQLGetDiagRec(SQL_HANDLE_STMT, hstmt2, 1, sqlState, &nError, eMsg, sizeof(eMsg), &msgLen);
  /* Check that the error has been properly reported */
  is(rc == SQL_SUCCESS);
  is(nError > 0);
  is(strlen(sqlState) > 0);
  is(strlen(eMsg) > 0);
  is(msgLen > 0);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


BEGIN_TESTS
  ADD_TEST(t_passwordexpire)
  ADD_TEST(t_bug13542600)
  ADD_TEST(t_get_diag_all)
#ifndef NO_DRIVERMANAGER
#ifndef USE_IODBC
 ADD_TEST(t_odbc2_error)
#endif
  ADD_TEST(t_odbc3_error)
#endif

#ifdef _WIN32
  ADD_TEST(t_odbc3_80)  // ODBC 3.8 is consistenly supported in Windows only
#endif

  ADD_TEST(t_diagrec)
  ADD_TEST(t_warning)
  ADD_TEST(t_bug3456)
  ADD_TEST(t_bug16224)
  ADD_TEST(t_bug25671389)
#ifndef USE_IODBC
  ADD_TEST(t_bug14285620)
  ADD_TEST(t_bug11750296)
#endif
  ADD_TEST(bind_invalidcol)
  ADD_TEST(t_handle_err)
  ADD_TEST(bind_notenoughparam1)
  ADD_TEST(bind_notenoughparam2)
  ADD_TEST(getdata_need_nullind)
  ADD_TEST(sqlerror)
  ADD_TEST(t_bug27158)
  // ADD_TOFIX(t_bug49466) TODO: Fix
  // ADD_TEST(t_cleartext_password) TODO: Fix Segfault
END_TESTS

RUN_TESTS
