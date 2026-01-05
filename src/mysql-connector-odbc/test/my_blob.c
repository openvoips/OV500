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

DECLARE_TEST(t_blob)
{
    SQLRETURN rc;
    SQLUINTEGER j= 0;
    SQLINTEGER l;
    SQLLEN cbValue;
    SQLCHAR *blobbuf;
    SQLUINTEGER blobbuf_size = 1024 * 1 * 6L;
    SQLUINTEGER blob_read;
    SQLPOINTER token;
    clock_t start, finish;
    double duration;
    SQLUINTEGER blob_size = 1 * 1024L * 5L;

    rc = SQLSetConnectOption(hdbc, SQL_AUTOCOMMIT, 0L);
    mycon(hdbc,rc);

    ok_sql(hstmt, "DROP TABLE IF EXISTS TBLOB");
    ok_sql(hstmt, "CREATE TABLE TBLOB (I INTEGER NOT NULL PRIMARY KEY,"
           "B LONGBLOB)");

    cbValue = 0;
    ok_stmt(hstmt, SQLPrepare(hstmt,
                              (SQLCHAR *)"INSERT INTO TBLOB VALUES (1, ?)",
                              SQL_NTS));
    ok_stmt(hstmt, SQLBindParameter(hstmt, SQL_PARAM_INPUT, 1, SQL_C_BINARY,
                                    SQL_LONGVARBINARY, blob_size, 0, NULL,
                                    0, &cbValue));
    cbValue = SQL_DATA_AT_EXEC;
    blobbuf = (SQLCHAR *)malloc(blobbuf_size);
    memset(blobbuf, 'A', blobbuf_size);

    start = clock();

    expect_stmt(hstmt, SQLExecute(hstmt), SQL_NEED_DATA);

    expect_stmt(hstmt, SQLParamData(hstmt, &token), SQL_NEED_DATA);
    {
        for (j = 0; j < blob_size; )
        {
            SDWORD s;

            s = (SDWORD)blobbuf_size;
            if (s + j > blob_size)
            {
                s -= (s + j) - blob_size;
                myassert(s + j == blob_size);
            }
            rc = SQLPutData(hstmt, blobbuf, s);
            mystmt(hstmt,rc);
            j += (SQLUINTEGER)s;
        }
        rc = SQLParamData(hstmt, &token);
        mystmt(hstmt,rc);
    }
    finish = clock();

    duration = (finish-start)/CLOCKS_PER_SEC;
    printMessage("j: %d", j);
    myassert(j == blob_size);
    printMessage("Wrote %ld bytes in %3.3lf seconds (%lg bytes/s)",
                 j, duration, duration == 0.0 ? 9.99e99 : j / duration);

    rc = SQLTransact(NULL, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);

    rc = SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    mystmt(hstmt,rc);


    memset(blobbuf, ~0, 100);
    ok_stmt(hstmt, SQLPrepare(hstmt,
                              (SQLCHAR *)"SELECT I, B FROM TBLOB WHERE I = 1",
                              SQL_NTS));

    start = clock();

    rc = SQLExecute(hstmt);
    mystmt(hstmt,rc);
    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);
    rc = SQLGetData(hstmt, 1, SQL_C_LONG, &l, 0L, &cbValue);
    mystmt(hstmt,rc);

    blob_read = 0L;
    do
    {
        rc = SQLGetData(hstmt, 2, SQL_C_BINARY, blobbuf, blobbuf_size, &cbValue);
        myassert(cbValue > 0);
        blob_read += (SQLINTEGER)(cbValue < blobbuf_size ? cbValue : blobbuf_size);
    } while (rc == SQL_SUCCESS_WITH_INFO);
    myassert(rc == SQL_SUCCESS);
    myassert(blob_read == blob_size);
    finish = clock();
    duration = (finish-start)/CLOCKS_PER_SEC;
    printMessage("Read  %ld bytes in %3.3lf seconds (%lg bytes/s)",
                 blob_read, duration, duration == 0.0 ? 9.99e99 :
                 blob_read / duration);

    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);
    free(blobbuf);

  ok_sql(hstmt, "DROP TABLE IF EXISTS TBLOB");

  return OK;
}

DECLARE_TEST(t_1piecewrite2)
{
    SQLRETURN rc;
    SQLLEN cbValue,cbValue2;
    SQLINTEGER l;
    SQLCHAR* blobbuf;
    size_t i;

    ok_sql(hstmt, "DROP TABLE IF EXISTS TBLOB");
    ok_sql(hstmt, "CREATE TABLE TBLOB (I INTEGER NOT NULL PRIMARY KEY,"
          "B LONG VARCHAR NOT NULL)");

    cbValue = 3510L;

    blobbuf = (SQLCHAR *)malloc((size_t)cbValue + 1);
    for (i = 0; i < (size_t)cbValue; i++)
    {
        blobbuf[i] = (char)((i % ('z' - 'a' + 1)) + 'a');
    }
    blobbuf[i] = '\0';
    l = 1;
    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_LONG, SQL_INTEGER, 0, 0, &l, 0, NULL);
    mystmt(hstmt,rc);
    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_LONGVARCHAR, 0, 0, blobbuf,cbValue, NULL);
    mystmt(hstmt,rc);
    ok_sql(hstmt, "INSERT INTO TBLOB VALUES (1,?)");
    mystmt(hstmt,rc);
    rc = SQLTransact(NULL, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);
    memset(blobbuf, 1, (size_t)cbValue);
    rc = SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    mystmt(hstmt,rc);
    ok_sql(hstmt, "SELECT B FROM TBLOB WHERE I = 1");
    mystmt(hstmt,rc);
    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);
    rc = SQLGetData(hstmt, 1, SQL_C_BINARY, blobbuf, cbValue, &cbValue2);
    mystmt(hstmt,rc);
    myassert(cbValue2 == cbValue);
    for (i = 0; i < (size_t)cbValue; i++)
    {
        myassert(blobbuf[i] == (char)((i % ('z' - 'a' + 1)) + 'a'));
    }
    rc = SQLFreeStmt(hstmt, SQL_CLOSE);
    mystmt(hstmt,rc);
    rc = SQLTransact(NULL, hdbc, SQL_COMMIT);
    mycon(hdbc,rc);
    free(blobbuf);

  ok_sql(hstmt, "DROP TABLE IF EXISTS TBLOB");

  return OK;
}


/* Test for a simple SQLPutData and SQLParamData handling for longtext */
DECLARE_TEST(t_putdata)
{
  SQLRETURN  rc;
  SQLLEN     pcbLength;
  SQLINTEGER c1;
  SQLCHAR    data[255];
  SQLPOINTER token;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata");
  ok_sql(hstmt, "CREATE TABLE t_putdata (c1 INT, c2 LONG VARCHAR)");

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"insert into t_putdata values(?,?)",
                            SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_LONG,
                          SQL_INTEGER,0,0,&c1,0,NULL);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT,SQL_C_CHAR,
                          SQL_LONGVARCHAR,0,0,
                          (SQLPOINTER)1,0,&pcbLength);

    pcbLength =  SQL_LEN_DATA_AT_EXEC(0);

    c1 = 10;
    rc = SQLExecute(hstmt);
    myassert(rc == SQL_NEED_DATA);

    rc = SQLParamData(hstmt, &token);
    myassert(rc == SQL_NEED_DATA);

    strcpy((char *)data,"mysql ab");
    rc = SQLPutData(hstmt,data,6);
    mystmt(hstmt,rc);

    strcpy((char *)data,"- the open source database company");
    rc = SQLPutData(hstmt,data,strlen((char *)data));
    mystmt(hstmt,rc);

    rc = SQLParamData(hstmt, &token);
    mystmt(hstmt,rc);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt, "select c2 from t_putdata where c1= 10");
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    pcbLength= 0;
    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &pcbLength);
    mystmt(hstmt,rc);
    printMessage("data: %s(%ld)", data, pcbLength);
    is_str(data, "mysql - the open source database company", 40);
    myassert(pcbLength == 40);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata");

  return OK;
}


/* Test for a simple SQLPutData and SQLParamData handling for longtext */
DECLARE_TEST(t_putdata1)
{
  SQLRETURN  rc;
  SQLLEN     pcbLength;
  SQLINTEGER c1;
  SQLCHAR    data[255];
  SQLPOINTER token;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata");
  ok_sql(hstmt, "CREATE TABLE t_putdata (c1 INT, c2 LONG VARCHAR)");
  ok_sql(hstmt, "INSERT INTO t_putdata VALUES (10,'venu')");

  ok_stmt(hstmt,
          SQLPrepare(hstmt,
                     (SQLCHAR *)"UPDATE t_putdata SET c2= ? WHERE c1 = ?",
                     SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_CHAR,
                          SQL_LONGVARCHAR,0,0,
                          (SQLPOINTER)1,0,&pcbLength);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT,SQL_C_LONG,
                          SQL_INTEGER,0,0,&c1,0,NULL);

    pcbLength =  SQL_LEN_DATA_AT_EXEC(0);

    c1 = 10;
    rc = SQLExecute(hstmt);
    myassert(rc == SQL_NEED_DATA);

    rc = SQLParamData(hstmt, &token);
    myassert(rc == SQL_NEED_DATA);

    strcpy((char *)data,"mysql ab");
    rc = SQLPutData(hstmt,data,6);
    mystmt(hstmt,rc);

    strcpy((char *)data,"- the open source database company");
    rc = SQLPutData(hstmt,data,strlen((char *)data));
    mystmt(hstmt,rc);

    rc = SQLParamData(hstmt, &token);
    mystmt(hstmt,rc);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt, "select c2 from t_putdata where c1= 10");
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    pcbLength= 0;
    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &pcbLength);
    mystmt(hstmt,rc);
    printMessage("data: %s(%ld)", data, pcbLength);
    is_str(data,"mysql - the open source database company", 40);
    myassert(pcbLength == 40);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata");

  return OK;
}


/* Test for a simple SQLPutData and SQLParamData handling for longtext */
DECLARE_TEST(t_putdata2)
{
  SQLRETURN  rc;
  SQLLEN     pcbLength;
  SQLINTEGER c1;
  SQLCHAR    data[255];
  SQLPOINTER token;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata");
  ok_sql(hstmt, "CREATE TABLE t_putdata (c1 INT, c2 LONG VARCHAR,"
        "c3 LONG VARCHAR)");

  ok_stmt(hstmt, SQLPrepare(hstmt,
                            (SQLCHAR *)"insert into t_putdata values(?,?,?)",
                            SQL_NTS));

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_LONG,
                          SQL_INTEGER,0,0,&c1,0,NULL);

    rc = SQLBindParameter(hstmt,2,SQL_PARAM_INPUT,SQL_C_CHAR,
                          SQL_LONGVARCHAR,0,0,
                          (SQLPOINTER)1,0,&pcbLength);

    rc = SQLBindParameter(hstmt,3,SQL_PARAM_INPUT,SQL_C_CHAR,
                          SQL_LONGVARCHAR,0,0,
                          (SQLPOINTER)1,0,&pcbLength);

    pcbLength =  SQL_LEN_DATA_AT_EXEC(0);

    c1 = 10;
    rc = SQLExecute(hstmt);
    myassert(rc == SQL_NEED_DATA);

    rc = SQLParamData(hstmt, &token);
    myassert(rc == SQL_NEED_DATA);

    strcpy((char *)data,"mysql ab");
    rc = SQLPutData(hstmt,data,6);
    mystmt(hstmt,rc);

    strcpy((char *)data,"- the open source database company");
    rc = SQLPutData(hstmt,data,strlen((char *)data));
    mystmt(hstmt,rc);

    rc = SQLParamData(hstmt, &token);
    myassert(rc == SQL_NEED_DATA);

    strcpy((char *)data,"MySQL AB");
    rc = SQLPutData(hstmt,data, 8);
    mystmt(hstmt,rc);

    rc = SQLParamData(hstmt, &token);
    mystmt(hstmt,rc);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt, "select c2,c3 from t_putdata where c1= 10");
    mystmt(hstmt,rc);

    rc = SQLFetch(hstmt);
    mystmt(hstmt,rc);

    pcbLength= 0;
    rc = SQLGetData(hstmt, 1, SQL_C_CHAR, data, sizeof(data), &pcbLength);
    mystmt(hstmt,rc);
    printMessage("data: %s(%ld)", data, pcbLength);
    is_str(data, "mysql - the open source database company", 40);
    myassert(pcbLength == 40);

    pcbLength= 0;
    rc = SQLGetData(hstmt, 2, SQL_C_CHAR, data, sizeof(data), &pcbLength);
    mystmt(hstmt,rc);
    printMessage("data: %s(%ld)", data, pcbLength);
    is_str(data, "MySQL AB", 8);
    myassert(pcbLength == 8);

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata");

  return OK;
}


/* Test for a simple SQLPutData and SQLParamData handling bug #1316 */
DECLARE_TEST(t_putdata3)
{
  SQLRETURN   rc;
  SQLINTEGER  id, id1, id2, id3;
  SQLLEN      resId, resUTimeSec, resUTimeMSec, resDataLen, resData;

  SQLCHAR buffer[]= "MySQL - The worlds's most popular open source database";
  const int MAX_PART_SIZE = 5;

  SQLCHAR data[50];
  int commonLen= 20;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata3");
  ok_sql(hstmt,
         "CREATE TABLE t_putdata3 (id INT, id1 INT, id2 INT, id3 INT, b BLOB)");

  ok_stmt(hstmt, SQLPrepare(hstmt, (SQLCHAR *)
                            "INSERT INTO t_putdata3 VALUES (?, ?, ?, ?, ?)",
                            SQL_NTS));

  id= 1, id1= 2, id2= 3, id3= 4;
  resId= 0;
  resUTimeSec= resUTimeMSec= 0;
  resDataLen= 0;
  resData= SQL_LEN_DATA_AT_EXEC(0);

  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_SLONG,
                                  SQL_INTEGER, 0, 0, &id, 0, &resId));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_SLONG,
                                  SQL_INTEGER, 0, 0, &id1, 0, &resUTimeSec));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_SLONG,
                                  SQL_INTEGER, 0, 0, &id2, 0, &resUTimeMSec));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 4, SQL_PARAM_INPUT, SQL_C_SLONG,
                                  SQL_INTEGER, 0, 0, &id3, 0, &resDataLen));

  ok_stmt(hstmt, SQLBindParameter(hstmt, 5, SQL_PARAM_INPUT, SQL_C_BINARY,
                                  SQL_LONGVARBINARY, 10, 10, (SQLPOINTER)5,
                                  0, &resData));

  rc= SQLExecute(hstmt);
  if (rc == SQL_NEED_DATA)
  {
    SQLPOINTER parameter;

    if (SQLParamData(hstmt, &parameter) == SQL_NEED_DATA &&
        parameter == (SQLPOINTER)5)
    {
      int len= 0, partsize;

      /* storing long data by parts */
      while (len < commonLen)
      {
        partsize= commonLen - len;
        if (partsize > MAX_PART_SIZE)
          partsize= MAX_PART_SIZE;

        ok_stmt(hstmt, SQLPutData(hstmt, buffer + len, partsize));
        len+= partsize;
      }

      if (SQLParamData(hstmt, &parameter) == SQL_ERROR)
      {
      }
    }
  } /* end if (rc == SQL_NEED_DATA) */

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));

  if (mysql_min_version(hdbc, "4.0", 3))
  {
    ok_sql(hstmt, "SELECT id, id1, id2, id3, CONVERT(b, CHAR) FROM t_putdata3");

    ok_stmt(hstmt, SQLFetch(hstmt));

    is_num(my_fetch_int(hstmt, 1), 1);
    is_num(my_fetch_int(hstmt, 2), 2);
    is_num(my_fetch_int(hstmt, 3), 3);
    is_num(my_fetch_int(hstmt, 4), 4);

    is_str(my_fetch_str(hstmt, data, 5), buffer, commonLen);
  }
  else
  {
    ok_sql(hstmt, "SELECT id, id1, id2, id3, b FROM t_putdata3");

    ok_stmt(hstmt, SQLFetch(hstmt));

    is_num(my_fetch_int(hstmt, 1), 1);
    is_num(my_fetch_int(hstmt, 2), 2);
    is_num(my_fetch_int(hstmt, 3), 3);
    is_num(my_fetch_int(hstmt, 4), 4);

    is_str(my_fetch_str(hstmt, data, 5),
           "4D7953514C202D2054686520776F726C64732773", commonLen);
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_putdata3");

  return OK;
}


/* Test the bug when blob size > 8k */
DECLARE_TEST(t_blob_bug)
{
  SQLRETURN  rc;
  SQLCHAR    *data;
  SQLINTEGER i, val;
  SQLLEN     length;
  const SQLINTEGER max_blob_size=1024*100;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_blob");
  ok_sql(hstmt, "CREATE TABLE t_blob (blb LONG VARBINARY)");

  ok_stmt(hstmt,
          SQLPrepare(hstmt,
                     (SQLCHAR *)"INSERT INTO t_blob  VALUES (?)",SQL_NTS));

    if (!(data = (SQLCHAR *)calloc(max_blob_size,sizeof(SQLCHAR))))
    {
      SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
      SQLFreeStmt(hstmt,SQL_CLOSE);
      return FAIL;
    }

    rc = SQLBindParameter(hstmt,1,SQL_PARAM_INPUT,SQL_C_CHAR,SQL_VARBINARY,
                          0,0,data,0,&length);
    mystmt(hstmt,rc);

    memset(data,'X',max_blob_size);

    for (length=1024; length <= max_blob_size; length+= 1024)
    {
      rc = SQLExecute(hstmt);
      mystmt(hstmt,rc);
    }

    SQLFreeStmt(hstmt,SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    ok_sql(hstmt, "SELECT length(blb) FROM t_blob");

    rc = SQLBindCol(hstmt,1,SQL_C_LONG,&val,0,NULL);
    mystmt(hstmt,rc);

    for (i= 1; i <= max_blob_size/1024; i++)
    {
      rc = SQLFetch(hstmt);
      mystmt(hstmt,rc);

      printMessage("row %d length: %d", i, val);
      myassert(val == i * 1024);
    }
    rc = SQLFetch(hstmt);
    myassert(rc == SQL_NO_DATA);

    free(data);

    SQLFreeStmt(hstmt,SQL_UNBIND);
    SQLFreeStmt(hstmt,SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_blob");

  return OK;
}


#define TEST_ODBC_TEXT_LEN 3000
DECLARE_TEST(t_text_fetch)
{
  SQLRETURN  rc;
  SQLINTEGER i;
  SQLLEN     row_count, length;
  SQLCHAR    data[TEST_ODBC_TEXT_LEN+1];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_text_fetch");
  ok_sql(hstmt, "SET @@sql_mode=''");
  ok_sql(hstmt, "CREATE TABLE t_text_fetch(t1 tinytext,"
         "t2 text, t3 mediumtext, t4 longtext)");

  ok_stmt(hstmt,
          SQLPrepare(hstmt,
                     (SQLCHAR *)"insert into t_text_fetch values(?,?,?,?)",
                     SQL_NTS));

    rc = SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR,
                          0,0,(char *)data, TEST_ODBC_TEXT_LEN/3, NULL);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt, 2, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR,
                          0,0,(char *)data, TEST_ODBC_TEXT_LEN/2, NULL);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt, 3, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR,
                          0,0,(char *)data,
                          (SQLINTEGER)(TEST_ODBC_TEXT_LEN/1.5), NULL);
    mystmt(hstmt,rc);

    rc = SQLBindParameter(hstmt, 4, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR,
                          0,0,(char *)data, TEST_ODBC_TEXT_LEN-1, NULL);
    mystmt(hstmt,rc);

    memset(data,'A',TEST_ODBC_TEXT_LEN);
    data[TEST_ODBC_TEXT_LEN]='\0';

    for (i=0; i < 10; i++)
    {
      rc = SQLExecute(hstmt);
      mystmt(hstmt,rc);
    }

    SQLFreeStmt(hstmt, SQL_RESET_PARAMS);
    SQLFreeStmt(hstmt, SQL_CLOSE);

    ok_sql(hstmt, "SELECT * FROM t_text_fetch");

    row_count= 0;
    rc = SQLFetch(hstmt);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
       printf("# row '%ld' (lengths:", (long)row_count);
       rc = SQLGetData(hstmt,1,SQL_C_CHAR,(char *)data,TEST_ODBC_TEXT_LEN,&length);
       mystmt(hstmt,rc);
       printf("%ld", (long)length);
       myassert(length == 255);

       rc = SQLGetData(hstmt,2,SQL_C_CHAR,(char *)data,TEST_ODBC_TEXT_LEN,&length);
       mystmt(hstmt,rc);
       printf(",%ld", (long)length);
       myassert(length == TEST_ODBC_TEXT_LEN/2);

       rc = SQLGetData(hstmt,3,SQL_C_CHAR,(char *)data,TEST_ODBC_TEXT_LEN,&length);
       mystmt(hstmt,rc);
       printf(",%ld", (long)length);
       myassert(length == (SQLINTEGER)(TEST_ODBC_TEXT_LEN/1.5));

       rc = SQLGetData(hstmt,4,SQL_C_CHAR,(char *)data,TEST_ODBC_TEXT_LEN,&length);
       mystmt(hstmt,rc);
       printf(",%ld)\n", (long)length);
       myassert(length == TEST_ODBC_TEXT_LEN-1);
       row_count++;

       rc = SQLFetch(hstmt);
    }
    printMessage("total rows: %ld", (long)row_count);
    myassert(row_count == i);

    SQLFreeStmt(hstmt, SQL_UNBIND);
    SQLFreeStmt(hstmt, SQL_CLOSE);

  ok_sql(hstmt, "DROP TABLE t_text_fetch");

  return OK;
}


/**
  Test retrieving the length of a field with a non-null zero-length buffer.
  This is how ADO does it for long-type fields.
*/
DECLARE_TEST(getdata_lenonly)
{
  SQLLEN     len;
  SQLCHAR    buf[1];

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_getdata_lenonly");
  ok_sql(hstmt, "CREATE TABLE t_getdata_lenonly (a CHAR(4))");
  ok_sql(hstmt, "INSERT INTO t_getdata_lenonly VALUES ('venu')");

  ok_sql(hstmt, "SELECT a FROM t_getdata_lenonly");
  ok_stmt(hstmt, SQLFetch(hstmt));

  expect_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, buf, 0, &len),
              SQL_SUCCESS_WITH_INFO);
  is_num(len, 4);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_getdata_lenonly");

  return OK;
}


/**
  Bug #9781: returned SQL_Type on WKB query
*/
DECLARE_TEST(t_bug9781)
{
  SQLSMALLINT name_length, data_type, decimal_digits, nullable;
  SQLCHAR column_name[SQL_MAX_COLUMN_NAME_LEN];
  SQLULEN column_size;

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug9781");
  ok_sql(hstmt, "CREATE TABLE t_bug9781 (g GEOMETRY)");

  if (mysql_min_version(hdbc, "8.0", 3))
  {
    ok_sql(hstmt, "INSERT INTO t_bug9781 VALUES (St_GeomFromText('POINT(0 0)'))");
    ok_sql(hstmt, "SELECT St_AsBinary(g) FROM t_bug9781");

  }
  else
  {
    ok_sql(hstmt, "INSERT INTO t_bug9781 VALUES (GeomFromText('POINT(0 0)'))");
    ok_sql(hstmt, "SELECT AsBinary(g) FROM t_bug9781");
  }

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 1, column_name, sizeof(column_name),
                                &name_length, &data_type, &column_size,
                                &decimal_digits, &nullable));

  is_num(data_type, SQL_LONGVARBINARY);

  ok_sql(hstmt, "DROP TABLE IF EXISTS t_bug9781");
  return OK;
}


/*
 * Bug #10562 - Large blobs fail in a cursor
 */
DECLARE_TEST(t_bug10562)
{
  SQLLEN bsize = 12 * 1024;
  /* Test to just insert 12k blob */
  SQLCHAR *blob = malloc(bsize);
  SQLCHAR *blobcheck = malloc(bsize);
  int result= OK;
  memset(blob, 'X', bsize);

  ok_sql(hstmt, "drop table if exists t_bug10562");
  ok_sql(hstmt, "create table t_bug10562 ( id int not null primary key DEFAULT 0, mb longblob )");
  ok_sql(hstmt, "insert into t_bug10562 (mb) values ('zzzzzzzzzz')");

  ok_sql(hstmt, "select id, mb from t_bug10562");
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_BINARY, blob, bsize, &bsize));
  ok_stmt(hstmt, SQLSetPos(hstmt, 1, SQL_UPDATE, SQL_LOCK_NO_CHANGE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  /* Get the data back out to verify */
  ok_sql(hstmt, "select mb from t_bug10562");
  ok_stmt(hstmt, SQLFetch(hstmt));
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_BINARY, blobcheck, bsize, NULL));
  if (memcmp(blob, blobcheck, bsize))
  {
    result= FAIL;
  }

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "drop table if exists t_bug10562");
  free(blob);
  free(blobcheck);
  return result;
}


/*
  Bug#11746572: TEXT FIELDS WITH BINARY COLLATIONS
  Test for text field with latin1_bin and latin1_swedish_ci collation
  Output of text column should contain same input value and not hexadecimal
  value of input.
*/
DECLARE_TEST(t_bug_11746572)
{
  SQLCHAR     szData[MAX_ROW_DATA_LEN+1];
  SQLSMALLINT SqlType;
  SQLCHAR     ColName[MAX_NAME_LEN];

  ok_sql(hstmt, "DROP TABLE if exists bug_11746572");

  /*
    create table 'bug_11746572' with blob column and text columns
    with collation latin1_bin and latin1_swedish_ci.
  */
  ok_sql(hstmt,"CREATE TABLE bug_11746572( blob_field BLOB ,"
    "  text_bin TEXT CHARACTER SET latin1 COLLATE latin1_bin,"
    "  text_def TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci)");

  ok_sql(hstmt, "insert into bug_11746572 "
          " set blob_field= 'blob', text_bin= 'text', "
          " text_def= 'text' ; ");

  ok_sql(hstmt, "SELECT * from bug_11746572");

  ok_stmt(hstmt, SQLFetch(hstmt));

  /*
    Verify inserted data is changed to hexadecimal value for blob field
    and remains unchanged for text field for both binary and non-binary
    collation.
  */
  ok_stmt(hstmt, SQLGetData(hstmt, 1, SQL_C_CHAR, szData, MAX_ROW_DATA_LEN,NULL));
  is_str(szData, "626C6F62", 8);

  ok_stmt(hstmt, SQLGetData(hstmt, 2, SQL_C_CHAR, szData, MAX_ROW_DATA_LEN,NULL));
  is_str(szData, "text", 4);

  ok_stmt(hstmt, SQLGetData(hstmt, 3, SQL_C_CHAR, szData, MAX_ROW_DATA_LEN,NULL));
  is_str(szData, "text", 4);

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 1, ColName, MAX_NAME_LEN,
                        NULL, &SqlType, NULL, NULL, NULL));
  is_num(SqlType, SQL_LONGVARBINARY);

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 2, ColName, MAX_NAME_LEN,
                        NULL, &SqlType, NULL, NULL, NULL));

  is_num(SqlType, unicode_driver ? SQL_WLONGVARCHAR : SQL_LONGVARCHAR);

  ok_stmt(hstmt, SQLDescribeCol(hstmt, 3, ColName, MAX_NAME_LEN,
                        NULL, &SqlType, NULL, NULL, NULL));
  is_num(SqlType, SQL_LONGVARCHAR);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));

  ok_sql(hstmt, "DROP TABLE bug_11746572");

  return OK;
}

DECLARE_TEST(t_bug_29282638)
{
  SQLCHAR     col1_buf[128];
  SQLLEN      col1_cb = 0;
  SQLCHAR     col2_buf[10000];
  SQLLEN      col2_cb = 0;

  SQLCHAR     *param = (SQLCHAR*)"something";

  ok_sql(hstmt, "DROP TABLE if exists bug_29282638");

  ok_sql(hstmt, "CREATE TABLE bug_29282638(col1 VARCHAR(40), col2 BLOB)");

  ok_sql(hstmt, "insert into bug_29282638 VALUES "
         "('something', repeat('abc', 1000))");

  ok_stmt(hstmt, SQLPrepare(hstmt, "SELECT * FROM bug_29282638 WHERE col1 = ?", SQL_NTS));
  ok_stmt(hstmt, SQLBindParameter(hstmt, 1, SQL_PARAM_INPUT, SQL_C_CHAR, SQL_CHAR, 0, 0,
                                  param, strlen(param), NULL));

  ok_stmt(hstmt, SQLBindCol(hstmt, 1, SQL_C_CHAR, col1_buf, sizeof(col1_buf), &col1_cb));
  ok_stmt(hstmt, SQLBindCol(hstmt, 2, SQL_C_BINARY, col2_buf, sizeof(col2_buf), &col2_cb));

  ok_stmt(hstmt, SQLExecute(hstmt));

  ok_stmt(hstmt, SQLFetch(hstmt));

  is_str(col1_buf, param, strlen(param));
  is_num(col1_cb, strlen(param));

  is_str(col2_buf + 2997, "abc", 3);
  is_num(col2_cb, 3000);

  SQLFetch(hstmt);

  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
  ok_sql(hstmt, "DROP TABLE if exists bug_29282638");

  return OK;
}

BEGIN_TESTS
  ADD_TEST(t_bug_29282638)
  ADD_TEST(t_blob)
  ADD_TEST(t_1piecewrite2)
  ADD_TEST(t_putdata)
  ADD_TEST(t_putdata1)
  ADD_TEST(t_putdata2)
  ADD_TEST(t_putdata3)
  ADD_TEST(t_blob_bug)
  ADD_TEST(t_text_fetch)
  ADD_TEST(getdata_lenonly)
  ADD_TEST(t_bug9781)
  ADD_TEST(t_bug10562)
  ADD_TEST(t_bug_11746572)
END_TESTS


RUN_TESTS

