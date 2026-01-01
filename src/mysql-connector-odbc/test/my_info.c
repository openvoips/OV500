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

#include "odbctap.h"

DECLARE_TEST(t_get_all_info)
{
  SQLUSMALLINT opt[] = {
                        SQL_ACTIVE_ENVIRONMENTS,
                        SQL_AGGREGATE_FUNCTIONS,
                        SQL_ALTER_DOMAIN,
                        SQL_ALTER_TABLE,
#ifndef USE_IODBC
                        SQL_ASYNC_DBC_FUNCTIONS,
#endif
                        SQL_ASYNC_MODE,
                        SQL_BATCH_ROW_COUNT,
                        SQL_BATCH_SUPPORT,
                        SQL_BOOKMARK_PERSISTENCE,
                        SQL_CATALOG_LOCATION,
                        SQL_CATALOG_NAME,
                        SQL_CATALOG_NAME_SEPARATOR,
                        SQL_CATALOG_TERM,
                        SQL_CATALOG_USAGE,
                        SQL_COLLATION_SEQ,
                        SQL_COLUMN_ALIAS,
                        SQL_CONCAT_NULL_BEHAVIOR,
                        SQL_CONVERT_BINARY,
                        SQL_CONVERT_FUNCTIONS,
                        SQL_CORRELATION_NAME,
                        SQL_CREATE_ASSERTION,
                        SQL_CREATE_TABLE,
                        SQL_CREATE_TRANSLATION,
                        SQL_CREATE_VIEW,
                        SQL_CURSOR_SENSITIVITY,
                        SQL_DATA_SOURCE_NAME,
                        SQL_DATA_SOURCE_READ_ONLY,
                        SQL_DATETIME_LITERALS,
                        SQL_DBMS_NAME,
                        SQL_DDL_INDEX,
                        SQL_DEFAULT_TXN_ISOLATION,
                        SQL_DESCRIBE_PARAMETER,
                        SQL_DRIVER_VER,
                        SQL_DROP_ASSERTION,
                        SQL_DROP_TABLE,
                        SQL_DROP_VIEW,
                        SQL_DYNAMIC_CURSOR_ATTRIBUTES1,
                        SQL_DYNAMIC_CURSOR_ATTRIBUTES2,
                        SQL_EXPRESSIONS_IN_ORDERBY,
                        SQL_FILE_USAGE,
                        SQL_FORWARD_ONLY_CURSOR_ATTRIBUTES1,
                        SQL_FORWARD_ONLY_CURSOR_ATTRIBUTES2,
                        SQL_GETDATA_EXTENSIONS,
                        SQL_GROUP_BY,
                        SQL_IDENTIFIER_CASE,
                        SQL_IDENTIFIER_QUOTE_CHAR,
                        SQL_INDEX_KEYWORDS,
                        SQL_INFO_SCHEMA_VIEWS,
                        SQL_INSERT_STATEMENT,
                        SQL_INTEGRITY,
                        SQL_KEYSET_CURSOR_ATTRIBUTES1,
                        SQL_LIKE_ESCAPE_CLAUSE,
                        SQL_MAX_ASYNC_CONCURRENT_STATEMENTS,
                        SQL_MAX_BINARY_LITERAL_LEN,
                        SQL_MAX_CATALOG_NAME_LEN,
                        SQL_MAX_CHAR_LITERAL_LEN,
                        SQL_MAX_COLUMN_NAME_LEN,
                        SQL_MAX_COLUMNS_IN_GROUP_BY,
                        SQL_MAX_COLUMNS_IN_INDEX,
                        SQL_MAX_COLUMNS_IN_ORDER_BY,
                        SQL_MAX_COLUMNS_IN_SELECT,
                        SQL_MAX_COLUMNS_IN_TABLE,
                        SQL_MAX_CONCURRENT_ACTIVITIES,
                        SQL_MAX_CURSOR_NAME_LEN,
                        SQL_MAX_DRIVER_CONNECTIONS,
                        SQL_MAX_IDENTIFIER_LEN,
                        SQL_MAX_INDEX_SIZE,
                        SQL_MAX_PROCEDURE_NAME_LEN,
                        SQL_MAX_ROW_SIZE,
                        SQL_MAX_ROW_SIZE_INCLUDES_LONG,
                        SQL_MAX_SCHEMA_NAME_LEN,
                        SQL_MAX_STATEMENT_LEN,
                        SQL_MAX_TABLE_NAME_LEN,
                        SQL_MAX_TABLES_IN_SELECT,
                        SQL_MAX_USER_NAME_LEN,
                        SQL_MULT_RESULT_SETS,
                        SQL_MULTIPLE_ACTIVE_TXN,
                        SQL_NEED_LONG_DATA_LEN,
                        SQL_NON_NULLABLE_COLUMNS,
                        SQL_NULL_COLLATION,
                        SQL_NUMERIC_FUNCTIONS,
                        SQL_ODBC_API_CONFORMANCE,
                        SQL_ODBC_INTERFACE_CONFORMANCE,
                        SQL_ODBC_SQL_CONFORMANCE,
                        SQL_OJ_CAPABILITIES,
                        SQL_ORDER_BY_COLUMNS_IN_SELECT,
                        SQL_PARAM_ARRAY_ROW_COUNTS,
                        SQL_PARAM_ARRAY_SELECTS,
                        SQL_PROCEDURE_TERM,
                        SQL_PROCEDURES,
                        SQL_POS_OPERATIONS,
                        SQL_QUOTED_IDENTIFIER_CASE,
                        SQL_ROW_UPDATES,
                        SQL_SCHEMA_TERM,
                        SQL_SCHEMA_USAGE,
                        SQL_SCROLL_OPTIONS,
                        SQL_SEARCH_PATTERN_ESCAPE,
                        SQL_SERVER_NAME,
                        SQL_SPECIAL_CHARACTERS,
                        SQL_SQL_CONFORMANCE,
                        SQL_SQL92_DATETIME_FUNCTIONS,
                        SQL_SQL92_FOREIGN_KEY_DELETE_RULE,
                        SQL_SQL92_GRANT,
                        SQL_SQL92_NUMERIC_VALUE_FUNCTIONS,
                        SQL_SQL92_PREDICATES,
                        SQL_SQL92_RELATIONAL_JOIN_OPERATORS,
                        SQL_SQL92_REVOKE,
                        SQL_SQL92_ROW_VALUE_CONSTRUCTOR,
                        SQL_SQL92_STRING_FUNCTIONS,
                        SQL_SQL92_VALUE_EXPRESSIONS,
                        SQL_STANDARD_CLI_CONFORMANCE,
                        SQL_STATIC_CURSOR_ATTRIBUTES1,
                        SQL_STATIC_CURSOR_ATTRIBUTES2,
                        SQL_STRING_FUNCTIONS,
                        SQL_SUBQUERIES,
                        SQL_SYSTEM_FUNCTIONS,
                        SQL_TABLE_TERM,
                        SQL_TIMEDATE_ADD_INTERVALS,
                        SQL_TIMEDATE_FUNCTIONS,
                        SQL_TXN_CAPABLE,
                        SQL_TXN_ISOLATION_OPTION,
                        SQL_UNION,
                        SQL_USER_NAME,
                        SQL_XOPEN_CLI_YEAR,
                        SQL_ACCESSIBLE_PROCEDURES,
                        SQL_LOCK_TYPES,
                        SQL_OUTER_JOINS,
                        SQL_POSITIONED_STATEMENTS,
                        SQL_SCROLL_CONCURRENCY,
                        SQL_STATIC_SENSITIVITY,
                        SQL_FETCH_DIRECTION,
                        SQL_ODBC_SAG_CLI_CONFORMANCE
                      };

    SQLUSMALLINT con_opt[] = {
      SQL_ATTR_CURSOR_SENSITIVITY,
      SQL_ATTR_MAX_LENGTH,
      SQL_ATTR_MAX_ROWS,
      SQL_ATTR_RETRIEVE_DATA,
      SQL_ATTR_SIMULATE_CURSOR,
      SQL_ATTR_FETCH_BOOKMARK_PTR,
      SQL_ATTR_ASYNC_ENABLE,
      SQL_ATTR_CONCURRENCY,
      SQL_KEYSET_SIZE,
      SQL_NOSCAN,
      SQL_ATTR_USE_BOOKMARKS,
      SQL_ATTR_AUTOCOMMIT
    };

    SQLUSMALLINT stmt_opt[] =
    {
      SQL_ATTR_CURSOR_SCROLLABLE,
      SQL_ATTR_AUTO_IPD,
      SQL_ATTR_PARAM_BIND_OFFSET_PTR,
      SQL_ATTR_PARAM_BIND_TYPE,
      SQL_ATTR_PARAM_OPERATION_PTR,
      SQL_ATTR_PARAM_STATUS_PTR,
      SQL_ATTR_PARAMS_PROCESSED_PTR,
      SQL_ATTR_PARAMSET_SIZE,
      SQL_ATTR_ROW_ARRAY_SIZE,
      SQL_ATTR_ROW_BIND_OFFSET_PTR,
      SQL_ATTR_ROW_BIND_TYPE,
      SQL_ATTR_ROW_NUMBER,
      SQL_ATTR_ROW_OPERATION_PTR,
      SQL_ATTR_ROW_STATUS_PTR,
      SQL_ATTR_ROWS_FETCHED_PTR,
      SQL_ATTR_SIMULATE_CURSOR,

    };

    int i= 0;
    char buf[512], conn[4096] = { 0 };
    SQLUSMALLINT pf_exists = 0;
    SQLHDBC hdbc1;
    SQLHSTMT hstmt1;

    sprintf((char *)conn, "DSN=%s;UID=%s;PASSWORD=%s",
            mydsn, myuid, mypwd);
    ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));
    ok_con(hdbc1, SQLDriverConnect(hdbc1, NULL, conn, (SQLSMALLINT)strlen(conn), NULL, 0,
                                   NULL, SQL_DRIVER_NOPROMPT));
    ok_con(hdbc1, SQLAllocHandle(SQL_HANDLE_STMT, hdbc1, &hstmt1));

    printf("** SQLGetInfo START\n");
    for (i= 0; i < sizeof(opt) / sizeof(SQLUSMALLINT); ++i)
    {
      SQLSMALLINT str_len_ptr= 0;
      SQLGetInfo(hdbc1, opt[i], buf, sizeof(buf), &str_len_ptr);
      printf("** SQLGetInfo [%d]\n", (int)opt[i]);
    }

    printf("** SQLGetConnectAttr START\n");
    for (i = 0; i < sizeof(con_opt) / sizeof(SQLUSMALLINT); ++i)
    {
      SQLINTEGER str_len_ptr = 0;
      memset(buf, 0, sizeof(buf));

      SQLSetConnectAttr(hstmt1, con_opt[i], buf, 512);
      printf("** SQLSetConnectAttr [%d]\n", (int)con_opt[i]);
      SQLGetConnectAttr(hstmt1, con_opt[i], buf, 512, &str_len_ptr);
      printf("** SQLGetConnectAttr [%d]\n", (int)con_opt[i]);
    }

    printf("** SQLGetStmtAttr START\n");
    for (i = 0; i < sizeof(stmt_opt) / sizeof(SQLUSMALLINT); ++i)
    {
      SQLINTEGER str_len_ptr = 0;
      SQLULEN data1 = 0;
      SQLUINTEGER data2 = 0;
      SQLPOINTER p = 0;
      size_t size = 0;
      SQLPOINTER p2 = 0;

      memset(buf, 0, sizeof(buf));
      if (stmt_opt[i] == SQL_ATTR_PARAM_BIND_OFFSET_PTR ||
          stmt_opt[i] == SQL_ATTR_PARAM_OPERATION_PTR ||
          stmt_opt[i] == SQL_ATTR_PARAM_STATUS_PTR ||
          stmt_opt[i] == SQL_ATTR_PARAMS_PROCESSED_PTR ||
          stmt_opt[i] == SQL_ATTR_ROW_BIND_OFFSET_PTR ||
          stmt_opt[i] == SQL_ATTR_ROW_OPERATION_PTR ||
          stmt_opt[i] == SQL_ATTR_ROW_STATUS_PTR ||
          stmt_opt[i] == SQL_ATTR_ROWS_FETCHED_PTR ||
          stmt_opt[i] == SQL_ATTR_APP_ROW_DESC ||
          stmt_opt[i] == SQL_ATTR_IMP_ROW_DESC ||
          stmt_opt[i] == SQL_ATTR_APP_PARAM_DESC ||
          stmt_opt[i] == SQL_ATTR_IMP_PARAM_DESC )
      {
        p = (SQLPOINTER)&p2;
        size = sizeof(p2);
      }
      else
      {
        p = (SQLPOINTER)&data2;
        size = sizeof(data2);
      }

      SQLSetStmtAttr(hstmt1, stmt_opt[i], p, (SQLINTEGER)size);
      printf("** SQLSetStmtAttr [%d]\n", (int)stmt_opt[i]);
      SQLGetStmtAttr(hstmt1, stmt_opt[i], p, (SQLINTEGER)size, &str_len_ptr);
      printf("** SQLGetStmtAttr [%d]\n", (int)stmt_opt[i]);
    }

    free_basic_handles(NULL, &hdbc1, &hstmt1);
    return OK;
}

DECLARE_TEST(t_bug28385722)
{
  SQLSMALLINT schema_len = 0;
  SQLUINTEGER schema_usage = 0;
  SQLSMALLINT val_len = 0;

  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);

  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, NULL,
                                        NULL, NULL, NULL, "NO_SCHEMA=0"));

  ok_con(hdbc, SQLGetInfo(hdbc1, SQL_MAX_SCHEMA_NAME_LEN, &schema_len,
                          sizeof(schema_len), &val_len));
  is(schema_len > 63);

  ok_con(hdbc, SQLGetInfo(hdbc1, SQL_SCHEMA_USAGE, &schema_usage,
                          sizeof(schema_usage), &val_len));
  is_num(schema_usage, SQL_SU_DML_STATEMENTS | SQL_SU_PROCEDURE_INVOCATION |
         SQL_SU_TABLE_DEFINITION | SQL_SU_INDEX_DEFINITION |
         SQL_SU_PRIVILEGE_DEFINITION);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}

DECLARE_TEST(t_gettypeinfo)
{
  SQLSMALLINT pccol;

  ok_stmt(hstmt, SQLGetTypeInfo(hstmt, SQL_ALL_TYPES));

  ok_stmt(hstmt, SQLNumResultCols(hstmt, &pccol));
  is_num(pccol, 19);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
}


DECLARE_TEST(sqlgetinfo)
{
  SQLCHAR   rgbValue[100];
  SQLSMALLINT pcbInfo;

  ok_con(hdbc, SQLGetInfo(hdbc, SQL_DRIVER_ODBC_VER, rgbValue,
                          sizeof(rgbValue), &pcbInfo));

  is_num(pcbInfo, 5);
  is_str(rgbValue, "03.80", 5);

  return OK;
}


DECLARE_TEST(t_stmt_attr_status)
{
  SQLUSMALLINT rowStatusPtr[3];
  SQLULEN      rowsFetchedPtr;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_stmtstatus");
  ok_sql(hstmt, "CREATE TABLE t_stmtstatus (id INT, name CHAR(20))");
  ok_sql(hstmt, "INSERT INTO t_stmtstatus VALUES (10,'data1'),(20,'data2')");

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_SCROLLABLE,
                                (SQLPOINTER)SQL_NONSCROLLABLE, 0));

  ok_sql(hstmt, "SELECT * FROM t_stmtstatus");

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR,
                                &rowsFetchedPtr, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_STATUS_PTR,
                                rowStatusPtr, 0));

  expect_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 2), SQL_ERROR);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_CURSOR_SCROLLABLE,
                                (SQLPOINTER)SQL_SCROLLABLE, 0));

  ok_sql(hstmt, "SELECT * FROM t_stmtstatus");

  ok_stmt(hstmt, SQLFetchScroll(hstmt, SQL_FETCH_ABSOLUTE, 2));

  is_num(rowsFetchedPtr, 1);
  is_num(rowStatusPtr[0], 0);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROWS_FETCHED_PTR, NULL, 0));
  ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_ATTR_ROW_STATUS_PTR, NULL, 0));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_stmtstatus");

  return OK;
}


DECLARE_TEST(t_msdev_bug)
{
  SQLCHAR    catalog[30];
  SQLINTEGER len;

  ok_con(hdbc, SQLGetConnectOption(hdbc, SQL_CURRENT_QUALIFIER, catalog));
  is_str(catalog, "test", 4);

  ok_con(hdbc, SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, catalog,
                                 sizeof(catalog), &len));
  is_num(len, 4);
  is_str(catalog, "test", 4);

  return OK;
}


/**
  Bug #28657: ODBC Connector returns FALSE on SQLGetTypeInfo with DATETIME (wxWindows latest)
*/
DECLARE_TEST(t_bug28657)
{
#ifdef WIN32
  /*
   The Microsoft Windows ODBC driver manager automatically maps a request
   for SQL_DATETIME to SQL_TYPE_DATE, which means our little workaround to
   get all of the SQL_DATETIME types at once does not work on there.
  */
  skip("test doesn't work with Microsoft Windows ODBC driver manager");
#else
  ok_stmt(hstmt, SQLGetTypeInfo(hstmt, SQL_DATETIME));

  is(myresult(hstmt) > 1);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  return OK;
#endif
}


DECLARE_TEST(t_bug14639)
{
  SQLINTEGER connection_id;
  SQLUINTEGER is_dead;
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

  /* Check that connection is alive */
  ok_con(hdbc2, SQLGetConnectAttr(hdbc2, SQL_ATTR_CONNECTION_DEAD, &is_dead,
                                 sizeof(is_dead), 0));
  is_num(is_dead, SQL_CD_FALSE);

  /* From another connection, kill the connection created above */
  sprintf(buf, "KILL %d", connection_id);
  ok_stmt(hstmt, SQLExecDirect(hstmt, (SQLCHAR *)buf, SQL_NTS));

  /* Now check that the connection killed returns the right state */
  ok_con(hdbc, SQLGetConnectAttr(hdbc2, SQL_ATTR_CONNECTION_DEAD, &is_dead,
                                 sizeof(is_dead), 0));
  is_num(is_dead, SQL_CD_TRUE);

  return OK;
}


/**
 Bug #31055: Uninitiated memory returned by SQLGetFunctions() with
 SQL_API_ODBC3_ALL_FUNCTION
*/
DECLARE_TEST(t_bug31055)
{
  SQLUSMALLINT funcs[SQL_API_ODBC3_ALL_FUNCTIONS_SIZE];

  /*
     The DM will presumably return true for all functions that it
     can satisfy in place of the driver. This test will only work
     when linked directly to the driver.
  */
  if (using_dm(hdbc))
    return OK;

  memset(funcs, 0xff, sizeof(SQLUSMALLINT) * SQL_API_ODBC3_ALL_FUNCTIONS_SIZE);

  ok_con(hdbc, SQLGetFunctions(hdbc, SQL_API_ODBC3_ALL_FUNCTIONS, funcs));

  is_num(SQL_FUNC_EXISTS(funcs, SQL_API_SQLALLOCHANDLESTD), 0);

  return OK;
}


/*
   Bug 3780, reading or setting ADODB.Connection.DefaultDatabase
   is not supported
*/
DECLARE_TEST(t_bug3780)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR   rgbValue[MAX_NAME_LEN];
  SQLSMALLINT pcbInfo;
  SQLINTEGER attrlen;

  /* The connection string must not include DATABASE. */
  is(OK == alloc_basic_handles_with_opt(&henv1, &hdbc1, &hstmt1, USE_DRIVER,
                                        NULL, NULL, "", NULL));

  ok_con(hdbc1, SQLGetInfo(hdbc1, SQL_DATABASE_NAME, rgbValue,
                           MAX_NAME_LEN, &pcbInfo));

  is_num(pcbInfo, 4);
  is_str(rgbValue, "null", pcbInfo);

  ok_con(hdbc1, SQLGetConnectAttr(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                  rgbValue, MAX_NAME_LEN, &attrlen));

  is_num(attrlen, 4);
  is_str(rgbValue, "null", attrlen);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);

  return OK;
}


/*
  Bug#16653
  MyODBC 3 / truncated UID when performing Data Import in MS Excel
*/
DECLARE_TEST(t_bug16653)
{
  SQLHANDLE hdbc1;
  SQLCHAR buf[50];

  /*
    Driver managers handle SQLGetConnectAttr before connection in
    inconsistent ways.
  */
  if (using_dm(hdbc))
    return OK;

  ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc1));

  /* this would cause a crash if we arent connected */
  ok_con(hdbc1, SQLGetConnectAttr(hdbc1, SQL_ATTR_CURRENT_CATALOG,
                                  buf, 50, NULL));
  is_str(buf, "null", 1);

  ok_con(hdbc1, SQLFreeHandle(SQL_HANDLE_DBC, hdbc1));

  return OK;
}


/*
  Bug #30626 - No result record for SQLGetTypeInfo for TIMESTAMP
*/
DECLARE_TEST(t_bug30626)
{
  DECLARE_BASIC_HANDLES(henv1, hdbc1, hstmt1);
  SQLCHAR conn[512];

  /* odbc 3 */
  ok_stmt(hstmt, SQLGetTypeInfo(hstmt, SQL_TYPE_TIMESTAMP));
  is_num(myresult(hstmt), 2);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLGetTypeInfo(hstmt, SQL_TYPE_TIME));
  is_num(myresult(hstmt), 1);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_stmt(hstmt, SQLGetTypeInfo(hstmt, SQL_TYPE_DATE));
  is_num(myresult(hstmt), 1);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* odbc 2 */
  sprintf((char *)conn, "DRIVER=%s;SERVER=%s;UID=%s;PASSWORD=%s",
          mydriver, myserver, myuid, mypwd);
  if (mysock != NULL)
  {
    strcat((char *)conn, ";SOCKET=");
    strcat((char *)conn, (char *)mysock);
  }
  if (myport)
  {
    char pbuff[20];
    sprintf(pbuff, ";PORT=%d", myport);
    strcat((char *)conn, pbuff);
  }

  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, &henv1));
  ok_env(henv1, SQLSetEnvAttr(henv1, SQL_ATTR_ODBC_VERSION,
			      (SQLPOINTER) SQL_OV_ODBC2, SQL_IS_INTEGER));
  ok_env(henv1, SQLAllocHandle(SQL_HANDLE_DBC, henv1, &hdbc1));
  ok_con(hdbc1, SQLDriverConnect(hdbc1, NULL, conn, (SQLSMALLINT)strlen(conn), NULL, 0,
				 NULL, SQL_DRIVER_NOPROMPT));
  ok_con(hdbc1, SQLAllocHandle(SQL_HANDLE_STMT, hdbc1, &hstmt1));

  ok_stmt(hstmt1, SQLGetTypeInfo(hstmt1, SQL_TIMESTAMP));
  is_num(myresult(hstmt1), 2);
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLGetTypeInfo(hstmt1, SQL_TIME));
  is_num(myresult(hstmt1), 1);
  ok_stmt(hstmt1, SQLFreeStmt(hstmt1, SQL_CLOSE));

  ok_stmt(hstmt1, SQLGetTypeInfo(hstmt1, SQL_DATE));
  is_num(myresult(hstmt1), 4);

  free_basic_handles(&henv1, &hdbc1, &hstmt1);
  return OK;
}


/*
  Bug #43855 - conversion flags not complete
*/
DECLARE_TEST(t_bug43855)
{
  SQLUINTEGER convFlags;
  SQLSMALLINT pcbInfo;

  /*
    TODO: add other convert checks, we are only interested in CHAR now
  */
  ok_con(hdbc, SQLGetInfo(hdbc, SQL_CONVERT_CHAR, &convFlags,
                          sizeof(convFlags), &pcbInfo));

  is_num(pcbInfo, 4);
  is((convFlags & SQL_CVT_CHAR) && (convFlags & SQL_CVT_NUMERIC) &&
     (convFlags & SQL_CVT_DECIMAL) && (convFlags & SQL_CVT_INTEGER) &&
     (convFlags & SQL_CVT_SMALLINT) && (convFlags & SQL_CVT_FLOAT) &&
     (convFlags & SQL_CVT_REAL) && (convFlags & SQL_CVT_DOUBLE) &&
     (convFlags & SQL_CVT_VARCHAR) && (convFlags & SQL_CVT_LONGVARCHAR) &&
     (convFlags & SQL_CVT_BIT) && (convFlags & SQL_CVT_TINYINT) &&
     (convFlags & SQL_CVT_BIGINT) && (convFlags & SQL_CVT_DATE) &&
     (convFlags & SQL_CVT_TIME) && (convFlags & SQL_CVT_TIMESTAMP) &&
     (convFlags & SQL_CVT_WCHAR) && (convFlags &SQL_CVT_WVARCHAR) &&
     (convFlags & SQL_CVT_WLONGVARCHAR));

  return OK;
}


/*
Bug#46910
MyODBC 5 - calling SQLGetConnectAttr before getting all results of "CALL ..." statement
*/
DECLARE_TEST(t_bug46910)
{
	SQLCHAR     catalog[30];
	SQLINTEGER  len, i;

	SQLCHAR * initStmt[]= {"DROP PROCEDURE IF EXISTS `spbug46910_1`",
		"CREATE PROCEDURE `spbug46910_1`()\
		BEGIN\
		SELECT 1 AS ret;\
		END"};

	SQLCHAR * cleanupStmt= "DROP PROCEDURE IF EXISTS `spbug46910_1`;";

	for (i= 0; i < 2; ++i)
		ok_stmt(hstmt, SQLExecDirect(hstmt, initStmt[i], SQL_NTS));

	SQLExecDirect(hstmt, "CALL spbug46910_1()", SQL_NTS);
	/*
	SQLRowCount(hstmt, &i);
	SQLNumResultCols(hstmt, &i);*/

	SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, catalog,
		sizeof(catalog), &len);
	//is_num(len, 4);
	//is_str(catalog, "test", 4);

	/*ok_con(hdbc, */
	SQLGetConnectAttr(hdbc, SQL_ATTR_CURRENT_CATALOG, catalog,
		sizeof(catalog), &len);

	ok_stmt(hstmt, SQLExecDirect(hstmt, cleanupStmt, SQL_NTS));

	return OK;
}


/*
  Bug#11749093: SQLDESCRIBECOL RETURNS EXCESSIVLY LONG COLUMN NAMES
  Maximum size of column length returned by SQLDescribeCol should be
  same as what SQLGetInfo() for SQL_MAX_COLUMN_NAME_LEN returns.
*/
DECLARE_TEST(t_bug11749093)
{
  char        colName[512];
  SQLSMALLINT colNameLen;
  SQLSMALLINT maxColLen;

  ok_stmt(hstmt, SQLExecDirect(hstmt,
              "SELECT 1234567890+2234567890+3234567890"
              "+4234567890+5234567890+6234567890+7234567890+"
              "+8234567890+9234567890+1034567890+1234567890+"
              "+1334567890+1434567890+1534567890+1634567890+"
              "+1734567890+1834567890+1934567890+2034567890+"
              "+2134567890+2234567890+2334567890+2434567890+"
              "+2534567890+2634567890+2734567890+2834567890"
              , SQL_NTS));

  ok_stmt(hstmt, SQLGetInfo(hdbc, SQL_MAX_COLUMN_NAME_LEN, &maxColLen, 255, NULL));

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 1, colName, sizeof(colName), &colNameLen,
                    NULL, NULL, NULL, NULL));

  is_str(colName, "1234567890+2234567890+3234567890"
              "+4234567890+5234567890+6234567890+7234567890+"
              "+8234567890+9234567890+1034567890+1234567890+"
              "+1334567890+1434567890+1534567890+1634567890+"
              "+1734567890+1834567890+1934567890+2034567890+"
              "+2134567890+2234567890+2334567890+2434567890+"
              "+2534567890+2634567890+2734567890+2834567890", maxColLen);
  is_num(colNameLen, maxColLen);

  return OK;
}


/* Make sure MySQL 5.5 and 5.6 reserved words are returned correctly  */
DECLARE_TEST(t_getkeywordinfo)
{
  SQLSMALLINT pccol;
  SQLCHAR keywords[8192];

  ok_con(hdbc, SQLGetInfo(hdbc, SQL_KEYWORDS, keywords,
                          sizeof(keywords), &pccol));

  /* We do not check versions older than 5.7 */
  if (mysql_min_version(hdbc, "8.0.22", 6))
  {
    is(strstr(keywords, "SOURCE_HEARTBEAT_PERIOD"));
    is(strstr(keywords, "SOURCE_BIND"));
  }
  else if (mysql_min_version(hdbc, "5.7", 3))
  {
    is(strstr(keywords, "NONBLOCKING"));
    is(strstr(keywords, "GET"));
    is(strstr(keywords, "IO_AFTER_GTIDS"));
    is(strstr(keywords, "IO_BEFORE_GTIDS"));
    is(strstr(keywords, "ONE_SHOT"));
    is(strstr(keywords, "PARTITION"));
    is(strstr(keywords, "SQL_AFTER_GTIDS"));
    is(strstr(keywords, "SQL_BEFORE_GTIDS"));
    is(strstr(keywords, "GENERAL"));
    is(strstr(keywords, "IGNORE_SERVER_IDS"));
    is(strstr(keywords, "MAXVALUE"));
    is(strstr(keywords, "RESIGNAL"));
    is(strstr(keywords, "SIGNAL"));
    is(strstr(keywords, "SLOW"));
    is(strstr(keywords, "\x4D\x41\x53\x54\x45\x52_HEARTBEAT_PERIOD"));
    is(strstr(keywords, "\x4D\x41\x53\x54\x45\x52_BIND"));
  }

  return OK;
}


/*
  WL 7991 Implement SQL_ATTR_QUERY_TIMEOUT statement attribute

  TODO: Fix bug 19157465 ODBC Driver returns HY000 SQL status
        instead of HYT00 on query timeout
*/
DECLARE_TEST(t_query_timeout)
{
  if (mysql_min_version(hdbc, "5.7.8", 5))
  {
    SQLULEN q_timeout1= 10;
    time_t t1, t2;
    SQLCHAR *large_buf;
    SQLCHAR iquery[1024]= {0};
    int i= 0;

    ok_sql(hstmt, "DROP TABLE if exists t_query_timeout1");
    ok_sql(hstmt, "CREATE TABLE t_query_timeout1(c11 varchar(512), c12 varchar(512), c13 varchar(512))");

    /* 3Mb should be enough */
    large_buf=gc_alloc(3000000);

    large_buf[0]= 0;
    strcpy(large_buf, "INSERT INTO t_query_timeout1 VALUES ('a', 'b', 'c')");

    for (i= 1; i < 200; i++)
    {
      sprintf(iquery, ",('col 1 tab 1 val %d', 'col 2 tab 1 val %d', 'col 3 tab 1 val %d')", i, i, i);
      strcat(large_buf, iquery);
    }

    ok_stmt(hstmt, SQLExecDirect(hstmt, large_buf, SQL_NTS));

    ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_QUERY_TIMEOUT, (SQLPOINTER) &q_timeout1,
                                  sizeof(SQLULEN), NULL));
    is_num(q_timeout1, SQL_QUERY_TIMEOUT_DEFAULT);
    ok_stmt(hstmt, SQLSetStmtAttr(hstmt, SQL_QUERY_TIMEOUT, (SQLPOINTER)2, 0));

    ok_stmt(hstmt, SQLGetStmtAttr(hstmt, SQL_QUERY_TIMEOUT, (SQLPOINTER) &q_timeout1,
                                  sizeof(SQLULEN), NULL));
    is_num(q_timeout1, 2);

    t1= time(NULL);

    expect_stmt(hstmt, SQLExecDirect(hstmt, "SELECT t1.c11 FROM t_query_timeout1 t1, t_query_timeout1 t2, t_query_timeout1 t3, t_query_timeout1 t4, t_query_timeout1 t5 WHERE (substring(t2.c13, -3) IN (select substring(concat(tt5.c11,tt4.c13,tt3.c11,tt2.c12), instr(tt5.c12, 'val '), 3) FROM t_query_timeout1 tt5, t_query_timeout1 tt4, t_query_timeout1 tt3, t_query_timeout1 tt2)) LIMIT 100", SQL_NTS),
                                            SQL_ERROR);
    t2= time(NULL);

    /* We check only for SQL_ERROR and SQLSTATE */
    is(check_sqlstate(hstmt, "HY000") == OK);

    ok_sql(hstmt, "DROP TABLE if exists t_query_timeout1");

    /* Just in case there is a delay in the network or somewhere else */
    is(t2 - t1 < 5);
  }
  return OK;
}

// Bug#34916959  - ODBC driver reports incorrect number of active
// statements per connection
DECLARE_TEST(t_bug34916959_active_statements) {
  SQLSMALLINT stmt_count = 0, out_count = 0;
  ok_stmt(hstmt, SQLGetInfo(hdbc, SQL_ACTIVE_STATEMENTS, &stmt_count,
                            2, &out_count));

  // Driver supports only one ACTIVE statement per connection.
  // Even if several statements are created in the same connection
  // they cannot be active at the same time (like simultaneous getting rows
  // from several stmt's that belong to the same connection)
  is_num(1, stmt_count);
  is_num(2, out_count);

  stmt_count = 0;
  out_count = 0;
  ok_stmt(hstmt, SQLGetInfo(hdbc, SQL_MAX_CONCURRENT_ACTIVITIES, &stmt_count,
                            2, &out_count));
  is_num(1, stmt_count);
  is_num(2, out_count);
  return OK;
}


BEGIN_TESTS
  /* Query timeout should go first */
  ADD_TEST(t_get_all_info)
  ADD_TEST(t_query_timeout)
  // ADD_TEST(t_bug34916959_active_statements) TODO: Fix
  ADD_TEST(t_bug28385722)
  ADD_TEST(sqlgetinfo)
  ADD_TEST(t_gettypeinfo)
  ADD_TEST(t_stmt_attr_status)
  ADD_TEST(t_msdev_bug)
  ADD_TEST(t_bug28657)
  ADD_TEST(t_bug30626)
  ADD_TEST(t_bug14639)
#ifndef USE_IODBC
  ADD_TEST(t_getkeywordinfo)
#endif
  ADD_TEST(t_bug31055)
  ADD_TEST(t_bug3780)
  ADD_TEST(t_bug16653)
  ADD_TEST(t_bug43855)
  ADD_TEST(t_bug46910)
  // ADD_TOFIX(t_bug11749093)  TODO: Fix
END_TESTS


RUN_TESTS
