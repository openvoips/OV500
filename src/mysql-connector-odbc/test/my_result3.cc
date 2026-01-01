// Copyright (c) 2023, 2024, Oracle and/or its affiliates.
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

#include <string>
#include <vector>

#include "odbc_util.h"
#include "../VersionInfo.h"

// Bug #16590994 - ADO updates fail when recordset is adForwardOnly
// and recordset contains bit data.
//
// NOTE: This bug is repeatable VB6 logic when it generates the
//       UPDATE query based on searchability of the column in a corresponding
//       SELECT. The driver converts BIT(N>1) to _binary and the comparison
//       like " WHERE bit_col = _binary'....'" fails the query with the error
//       message: Truncated incorrect DOUBLE value: "xxxxx".
//
//       Marking BIT(N>1) column as not searcheable will exclude it from
//       being added in WHERE clause by ADO/VB6.
//       To check if column is searchable the user needs to call
//       SQLColAttribute(hstmt, col_number, SQL_DESC_SEARCHABLE, ...);
DECLARE_TEST(t_bug16590994_bit_searchable) {
  try
  {
    odbc::table tab(hstmt, "bit_searchable",
                    "id int, ch char(10), b1 bit(1), b16 bit(16)");
    tab.insert("(3, '1234567890', 1, 0xFF00)");
    odbc::sql(hstmt, "SELECT * FROM " + tab.table_name);
    SQLSMALLINT col_count = 0;
    ok_stmt(hstmt, SQLNumResultCols(hstmt, &col_count));
    is_num(4, col_count);

    SQLLEN expected[] = {
      SQL_PRED_SEARCHABLE, // INT column is searchable
      SQL_PRED_SEARCHABLE, // CHAR column is searchable
      SQL_PRED_SEARCHABLE, // BIT(1) column is searchable
      SQL_PRED_NONE        // BIT(16) column is not searchable
    };

    SQLINTEGER expected_val[] = {
      3, 1234567890, 1, 0xFF00
    };

    ok_stmt(hstmt, SQLFetch(hstmt));

    for (int i = 0; i < col_count; ++i) {

      // Checking searchablility
      SQLLEN searchable = -1;
      ok_stmt(hstmt, SQLColAttribute(hstmt, i + 1, SQL_DESC_SEARCHABLE,
        nullptr, 0, nullptr, &searchable));
      is_num(expected[i], searchable);

      // Checking the data
      SQLINTEGER val = 0;
      ok_stmt(hstmt, SQLGetData(hstmt, i + 1, SQL_C_LONG, &val, 0, nullptr));
      is_num(expected_val[i], val);
    }

    is(SQL_NO_DATA == SQLFetch(hstmt));
  }
  ENDCATCH;
}

// Bug#18641963 SEGFAULT IN SQLBULKOPERATIONS(SQL_DELETE_BY_BOOKMARK)
// WHEN SELECT RETURNS 0 REC
DECLARE_TEST(t_bug18641963_bulk) {
  try {
    for (int id : {0, 3})
    {
      for (odbc::xstring opt : { "NO_SSPS=0", "NO_SSPS=1" })
      {
        odbc::connection con(nullptr, nullptr, nullptr, nullptr, opt);
        odbc::HSTMT hstmt1(con);

        SQLULEN num_rows_fetched = 0;
        const SQLULEN rcnt = 5;
        SQLLEN n_rcnt = 0;
        SQLINTEGER n_data[rcnt] = {};
        char sz_data[rcnt][16] = {};
        char b_data[rcnt][10] = {};
        SQLLEN length[rcnt] = {};
        SQLUSMALLINT row_status[rcnt] = {};
        SQLRETURN rc = 0;

        odbc::table tab(hstmt1, "bug18641963", "id INT, c VARCHAR(128) NOT NULL");
        tab.insert("(1, 'String 1'), (2, 'String 2'), "
                  "(3, 'String 3'), (4, 'String 4'), "
                  "(5, 'String 5')");
        odbc::stmt_close(hstmt1);

        ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_USE_BOOKMARKS,
                (SQLPOINTER) SQL_UB_VARIABLE, 0));

        ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_ROW_STATUS_PTR,
                &row_status[0], 0));

        ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_ROWS_FETCHED_PTR,
                &num_rows_fetched, 0));

        ok_stmt(hstmt1, SQLSetStmtAttr(hstmt1, SQL_ATTR_CURSOR_TYPE,
          (SQLPOINTER)SQL_CURSOR_STATIC, 0));

        ok_stmt(hstmt1, SQLSetStmtOption(hstmt1, SQL_ROWSET_SIZE, rcnt));

        odbc::xstring sql = "select id,c from bug18641963 where id=" +
          std::to_string(id) + " order by 2 desc";

        odbc::stmt_prepare(hstmt1, sql);
        odbc::stmt_execute(hstmt1);

        ok_stmt(hstmt1, SQLBindCol(hstmt1, 0, SQL_C_VARBOOKMARK, b_data,
                sizeof(b_data[0]), NULL));

        ok_stmt(hstmt1, SQLBindCol(hstmt1, 1, SQL_C_LONG, n_data, 0, NULL));

        ok_stmt(hstmt1, SQLBindCol(hstmt1, 2, SQL_C_CHAR, sz_data,
          sizeof(sz_data[0]), &length[0]));

        rc = SQLFetchScroll(hstmt1, SQL_FETCH_BOOKMARK, 0);
        is_num(id == 0 ? SQL_NO_DATA : SQL_SUCCESS, rc);

        rc = SQLBulkOperations(hstmt1, SQL_DELETE_BY_BOOKMARK);
        is_num(id == 0 ? SQL_NO_DATA : SQL_SUCCESS, rc);

        ok_stmt(hstmt1, SQLRowCount(hstmt1, &n_rcnt));
        is_num(id == 0 ? 0 : 1, n_rcnt);
      }
    }
  }
  ENDCATCH;
}

DECLARE_TEST(t_bug36945554_large_query) {
  odbc::table tab(hstmt, "bug36945554", "id int, mycolumn varchar(32)");
  // Data in the table does not matter, number of rows can be anything.
  tab.insert("(1, 'String 1'),(2, 'String 2'),(3, 'String 3')");

  odbc::xstring long_query =
    "select id,mycolumn,'";

  odbc::xstring long_val;
  long_val.reserve(10000);

  for (int i = 0; i < 500; ++i)
    long_val.append("0123456789");

  long_query.append(long_val + "' from bug36945554 ORDER BY id");

  try{
    for (odbc::xstring opt_ssps : { "NO_SSPS=1;", "NO_SSPS=0;" })
    {
      for (odbc::xstring opt_prefetch : { "PREFETCH=2;", "" })
      {
        odbc::xstring opt = opt_ssps + opt_prefetch;

        odbc::connection con(nullptr, nullptr, nullptr, nullptr, opt);

        SQLHSTMT hstmt1 = con.hstmt;
        odbc::sql(hstmt1, long_query);

        int row_num = 0;
        odbc::xbuf buf(10000);

        while(SQL_SUCCESS == SQLFetch(hstmt1)) {
          ++row_num;
          is_num(row_num, my_fetch_int(hstmt1, 1));

          odbc::xstring mycol = "String " + std::to_string(row_num);
          odbc::my_fetch_str(hstmt1, buf, 2);
          is_str(mycol.c_str(), (const char*)buf, mycol.length());

          odbc::my_fetch_str(hstmt1, buf, 3);
          is_str(long_val.c_str(), (const char*)buf, long_val.length());
        }

        // NOTE: With any value for PREFETCH the number of rows stays the same.
        is_num(3, row_num);
      }
    }
  }
  ENDCATCH;
}


BEGIN_TESTS
  ADD_TEST(t_bug36945554_large_query)
  ADD_TEST(t_bug18641963_bulk)
  ADD_TEST(t_bug16590994_bit_searchable)
END_TESTS


RUN_TESTS
