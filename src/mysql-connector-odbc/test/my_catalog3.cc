// Copyright (c) 2022, 2024, Oracle and/or its affiliates.
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


DECLARE_TEST(t_bug34355094_sqlcolumns_wchar)
{
  try
  {
    std::string tab_name = "t_bug34355094";
    std::string fields = "id int primary key,"
      "col1 varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,"
      "col2 char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,"
      "col3 LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL"
      ;
    odbc::table tab(hstmt, nullptr, tab_name, fields);

    ok_stmt(hstmt, SQLColumns(hstmt, nullptr, 0, nullptr, 0,
      (SQLCHAR*)tab_name.c_str(), SQL_NTS,
      (SQLCHAR*)"col%", SQL_NTS));

    SQLINTEGER expected_type[2][3] = {
      {SQL_VARCHAR, SQL_CHAR, SQL_LONGVARCHAR},
      {SQL_WVARCHAR, SQL_WCHAR, SQL_WLONGVARCHAR},
    };
    int rnum = 0;
    while (SQL_SUCCESS == SQLFetch(hstmt))
    {
      SQLINTEGER data_type = my_fetch_int(hstmt, 5);
      // The value of unicode_driver is either 0 or 1 and
      // therefore it can be used as an index in the array
      is_num(expected_type[unicode_driver][rnum], data_type);
      ++rnum;
    }
    is_num(3, rnum);
  }
  ENDCATCH;
}

DECLARE_TEST(t_bug35316630_sqlstatistics) {
  try {
    std::string tab_name = "t_bug35316630";
    std::string fields =
        "`id` INT NOT NULL,"
        "`k1` INT NULL,"
        "`k2` CHAR(8) NULL,"
        "`u3` VARCHAR(15) NULL,"
        "`txt` TEXT NULL,"
        "PRIMARY KEY(`id`),"
        "INDEX `IDX1` (`k1` ASC, `k2` ASC),"
        "UNIQUE INDEX `u3_UNIQUE` (`u3` ASC),"
        "FULLTEXT INDEX `txt_FT` (`txt`)";

    odbc::table tab(hstmt, nullptr, tab_name, fields);

    SQLSMALLINT ttype = 300;
    SQLLEN len = 0;
    struct expected_vals {
      const char *tab_name;
      SQLSMALLINT unique;
      const char *idx_name;
      SQLSMALLINT idx_type;
      SQLSMALLINT ordinal_pos;
      const char *col_name;
      char asc_desc;
      SQLSMALLINT cardinality;
    };

    expected_vals exp_vals[] = {
        {tab_name.c_str(), SQL_FALSE, "PRIMARY", SQL_INDEX_OTHER, 1, "id", 'A',
         0},
        {tab_name.c_str(), SQL_FALSE, "u3_UNIQUE", SQL_INDEX_OTHER, 1, "u3",
         'A', 0},
        {tab_name.c_str(), SQL_TRUE, "IDX1", SQL_INDEX_OTHER, 1, "k1", 'A', 0},
        {tab_name.c_str(), SQL_TRUE, "IDX1", SQL_INDEX_OTHER, 2, "k2", 'A', 0},
        {tab_name.c_str(), SQL_TRUE, "txt_FT", SQL_INDEX_OTHER, 1, "txt", '\0',
         0},
    };

    int step = 0;
    int rnum_exp_array[][2] = {
      // INDEX_TYPE, ROWS_NUMBER
      {SQL_INDEX_ALL, 5},
      {SQL_INDEX_UNIQUE, 2}};

    for (auto rnum_exp : rnum_exp_array)
    {
      int rnum = 0;
      ok_stmt(hstmt, SQLStatistics(hstmt, nullptr, 0, nullptr, 0,
                                (SQLCHAR *)tab_name.c_str(), SQL_NTS,
                                rnum_exp[0], SQL_QUICK));
      ++step;

      while (SQL_SUCCESS == SQLFetch(hstmt)) {
        SQLSMALLINT num_val = 0;
        odbc::xbuf buf(128);
        // TABLE_NAME
        const char *char_data = odbc::my_fetch_str(hstmt, buf, 3);
        is_str(exp_vals[rnum].tab_name, char_data, tab_name.length());
        // NON_UNIQUE
        ok_stmt(hstmt, SQLGetData(hstmt, 4, SQL_C_SHORT, &num_val,
                                  sizeof(num_val), &len));
        is_num(exp_vals[rnum].unique, num_val);
        // INDEX_QUALIFIER
        buf.setval("");
        ok_stmt(hstmt, SQLGetData(hstmt, 5, SQL_C_CHAR, buf,
                                  buf.size, &len));
        // char_data is already pointing to the data buffer.
        // We can use it without assigning from buf again.
        is_num(0, char_data[0]);
        is_num(-1, len);
        // INDEX_NAME
        char_data = odbc::my_fetch_str(hstmt, buf, 6);
        is_str(exp_vals[rnum].idx_name, char_data,
               strlen(exp_vals[rnum].idx_name));
        // TYPE
        ok_stmt(hstmt, SQLGetData(hstmt, 7, SQL_C_SHORT, &num_val,
                                  sizeof(num_val), &len));
        is_num(exp_vals[rnum].idx_type, num_val);
        // ORDINAL_POSITION
        ok_stmt(hstmt, SQLGetData(hstmt, 8, SQL_C_SHORT, &num_val,
                                  sizeof(num_val), &len));
        is_num(exp_vals[rnum].ordinal_pos, num_val);
        // COLUMN_NAME
        char_data = odbc::my_fetch_str(hstmt, buf, 9);
        is_str(exp_vals[rnum].col_name, char_data,
               strlen(exp_vals[rnum].col_name));
        // ASC_OR_DESC
        buf.setval("");
        ok_stmt(hstmt, SQLGetData(hstmt, 10, SQL_C_CHAR, buf,
                                  buf.size, &len));
        is_num(exp_vals[rnum].asc_desc, char_data[0]);
        // CARDINALITY
        num_val = 0;
        len = 0;
        ok_stmt(hstmt, SQLGetData(hstmt, 11, SQL_C_SHORT, &num_val,
                                  sizeof(num_val), &len));
        is_num(exp_vals[rnum].cardinality, num_val);
        // PAGES
        num_val = 0;
        len = 0;
        ok_stmt(hstmt, SQLGetData(hstmt, 12, SQL_C_SHORT, &num_val,
                                  sizeof(num_val), &len));
        is_num(0, num_val);
        is_num(-1, len);
        // FILTER_CONDITION
        buf.setval("");
        len = 0;
        ok_stmt(hstmt, SQLGetData(hstmt, 13, SQL_C_CHAR, buf, buf.size, &len));
        is_num(0, char_data[0]);
        is_num(-1, len);

        ++rnum;
      }
      // Expecting a different number of rows for all and unique indexes.
      is_num(rnum_exp[1], rnum);
      odbc::stmt_close(hstmt);
    }
  }
  ENDCATCH;
}

DECLARE_TEST(t_sqlmode_sqlcolumns) {
  try {
    odbc::table t(hstmt, "sqlmode_sqlcolumns", "id_col int");
    odbc::sql(hstmt, "SET SESSION sql_mode='ANSI'");
    ok_stmt(hstmt, SQLColumns(hstmt, nullptr, 0, nullptr, 0,
      (SQLCHAR*)t.table_name.c_str(), SQL_NTS, (SQLCHAR*)"id_col", SQL_NTS));
    int rnum = 0;
    odbc::xbuf buf(128);

    while (SQL_SUCCESS == SQLFetch(hstmt)) {
      // Test db name
      const char *char_data = my_fetch_str(hstmt, buf, 1);
      is_str(mydb, char_data, strlen(char_data));

      // Test tab name
      char_data = my_fetch_str(hstmt, buf, 3);
      is_str(t.table_name.c_str(), char_data, t.table_name.length());

      // Test col name
      char_data = my_fetch_str(hstmt, buf, 4);
      is_str("id_col", char_data, 6);
      ++rnum;
    }
    is_num(1, rnum);
  }
  ENDCATCH;
}


BEGIN_TESTS
  ADD_TEST(t_sqlmode_sqlcolumns)
  ADD_TEST(t_bug35316630_sqlstatistics)
  ADD_TEST(t_bug34355094_sqlcolumns_wchar)
END_TESTS


RUN_TESTS
