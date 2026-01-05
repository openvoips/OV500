// Copyright (c) 2021, 2024, Oracle and/or its affiliates.
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

#ifndef ODBC_UTIL_H
#define ODBC_UTIL_H

#include <string>
#include <memory>
#include <cstdio>
#include <fstream>
#include <iostream>

#define X(s) odbc::xstring(s)

namespace odbc {
struct SimpleException
{
  std::string m_msg;
  SimpleException() {}
  SimpleException(std::string s) : m_msg(s) {}
  SimpleException(const char* f, size_t line) : m_msg(f)
  {
    std::cout << "Failure in " + m_msg + " line " + std::to_string(line) <<
    std::endl;
  }
};
};

// ODBC test framework will do throw instead of return FAIL
#define CPP_THROW_CLASS odbc::SimpleException
#include "odbctap.h"

#define is_err(A) is(SQL_ERROR == A)
#define is_invalid(A) is(SQL_INVALID_HANDLE == A)
#define is_no_data(A) is(SQL_NO_DATA_FOUND == A)
#define is_xstr(A,B) if (A.compare(B)){\
  odbc::xstring st; \
  st = "String comparison failed: [\"" + A + "\"] != [\"" + B + "\"] in " + \
  __FILE__ + " line " + odbc::xstring((int)__LINE__); \
  throw odbc::Exception(st);\
}

#define err_stmt(hstmt, A) try { ok_stmt(hstmt, A); return FALSE; } catch(...) {}

#define err_con(hdbc, A) try { ok_con(hdbc, A); return FALSE; } catch(...) {}

#define ENDCATCH catch(odbc::Exception &ex)\
  {\
    std::cout << "Test failed: " << ex.msg << std::endl;\
    return FAIL;\
  }\
  catch (...)\
  {\
    return FAIL;\
  }\
  return OK


namespace odbc
{

struct xstring : public std::string
{
  bool m_is_null = false;
  using Base = std::string;

  xstring() : Base() {}

  xstring(std::nullptr_t) : m_is_null(true)
  {}

  xstring(char* s) : m_is_null(s == nullptr),
                  Base(s == nullptr ? "" : std::forward<char*>(s))
  {
    if (m_is_null)
      m_is_null = true;
  }

  xstring(SQLCHAR* s) : xstring((char*)s)
  {}

  xstring(int v) : xstring(std::to_string(v))
  {}

  template <class T>
  xstring(T &&s) : Base(std::forward<T>(s))
  {}

  operator SQLCHAR*() const
  { return m_is_null ? nullptr : (SQLCHAR*)this->c_str(); }

  operator char*() const
  { return m_is_null ? nullptr : (char*)this->c_str(); }

  operator const char*() const
  { return m_is_null ? nullptr : this->c_str(); }

  operator bool() const
  { return !m_is_null; }
};

struct xbuf
{
  std::unique_ptr<char[]> m_buf;
  size_t size;

  void setval(int v, size_t cnt)
  { memset(m_buf.get(), v, cnt); }

  void setval(int v)
  { setval(v, size); }

  void setval(const char* c)
  {
    setval(0);
    strcpy(m_buf.get(), c);
  }

  xbuf(size_t s) : size(s), m_buf(new char[s])
  { setval(0, s); }

  xstring get_str()
  { return xstring(m_buf.get()); }

  template <typename T>
  operator T*() const
  { return (T*)m_buf.get(); }


};

struct Exception
{
  xstring     msg;
  SQLCHAR     sqlstate[6] = {0, 0, 0, 0, 0, 0};
  SQLINTEGER  native_error = 0;
  SQLSMALLINT length = 0;
  SQLSMALLINT handle_type = SQL_HANDLE_STMT;

  Exception() {}
  Exception(xstring s) : msg(s) {}

  Exception(SQLHANDLE handle, SQLSMALLINT htype, bool print_diag = true)
  {
    odbc::xbuf  buf(SQL_MAX_MESSAGE_LENGTH);
    handle_type = htype;

    SQLRETURN drc = SQLGetDiagRec(handle_type, handle, 1, sqlstate,
                    &native_error, buf, SQL_MAX_MESSAGE_LENGTH - 1, &length);

    msg = buf;

    if (SQL_IS_SUCCESS(drc))
      printf("# [%6s][%d] %*s\n",
             sqlstate, length, native_error, (char*)msg);
    else
      printf("# Did not get expected diagnostics from SQLGetDiagRec() = %d\n",
             drc);

  }
};

void check_stmt(SQLHANDLE handle, int res)
{
  if (res != SQL_SUCCESS && res != SQL_SUCCESS_WITH_INFO)
    throw Exception(handle, SQL_HANDLE_STMT);
}

void check_dbc(SQLHANDLE handle, int res)
{
  if (res != SQL_SUCCESS && res != SQL_SUCCESS_WITH_INFO)
    throw Exception(handle, SQL_HANDLE_DBC);
}

void check_env(SQLHANDLE handle, int res)
{
  if (res != SQL_SUCCESS && res != SQL_SUCCESS_WITH_INFO)
    throw Exception(handle, SQL_HANDLE_ENV);
}

struct tempfile
{
  xstring full_path;

  tempfile(xstring ext = nullptr) :
    tempfile(nullptr, 0, ext)
  {}

  tempfile(const char *buf, size_t datalen, xstring ext = nullptr)
  {
    full_path = std::tmpnam(nullptr);
    if (ext)
      full_path.append(ext);

#ifdef _WIN32
    size_t pos = 0; ;
    while ((pos = full_path.find("\\")) != std::string::npos)
    {
      full_path.replace(pos, 1, "/");
    }
#endif

    std::ofstream infile;
    infile.open(full_path, std::ios::out|std::ios::binary);
    infile.write(buf, datalen);
    infile.close();
  }

  ~tempfile()
  {
    std::remove(full_path);
  }
};

void sql(SQLHSTMT hstmt, const char *query, bool ignore_result = false)
{
  SQLRETURN res = SQLExecDirect(hstmt, (SQLCHAR*)query, SQL_NTS);
  if(!ignore_result && res != SQL_SUCCESS && res != SQL_SUCCESS_WITH_INFO)
  {
    throw odbc::Exception(hstmt, SQL_HANDLE_STMT);
  }
}

void sql(SQLHSTMT hstmt, xstring &&query, bool ignore_result = false)
{
  sql(hstmt, query.c_str(), ignore_result);
}

void stmt_close(SQLHSTMT hstmt)
{
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
}

void stmt_unbind(SQLHSTMT hstmt)
{
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_UNBIND));
}

void stmt_reset(SQLHSTMT hstmt)
{
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_RESET_PARAMS));
}

void stmt_execute(SQLHSTMT hstmt)
{
  ok_stmt(hstmt, SQLExecute(hstmt));
}

void stmt_prepare(SQLHSTMT hstmt, xstring query)
{
  ok_stmt(hstmt, SQLPrepare(hstmt, query, SQL_NTS));
}

int describe_col(SQLHSTMT hstmt, int col, bool print_data = true,
                  SQLCHAR* col_name = nullptr,
                  SQLSMALLINT col_buf_len = 0, SQLSMALLINT *name_len = nullptr,
                  SQLSMALLINT *data_type = nullptr, SQLULEN *col_size = nullptr,
                  SQLSMALLINT *dec_digits = nullptr, SQLSMALLINT *nullable = nullptr)
{
  odbc::xbuf name(255);
  SQLSMALLINT num_buf[4] = { 0, 0, 0, 0};
  SQLULEN ulen_data = 0;

  SQLCHAR* col_name2 = col_name ? col_name : name;
  SQLSMALLINT col_buf_len2 = col_buf_len ? col_buf_len : (SQLSMALLINT)name.size;
  SQLSMALLINT *name_len2 = name_len ? name_len : &num_buf[0];
  SQLSMALLINT *data_type2 = data_type ? data_type : &num_buf[1];
  SQLULEN *col_size2 = col_size ? col_size : &ulen_data;
  SQLSMALLINT *dec_digits2 = dec_digits ? dec_digits : &num_buf[2];
  SQLSMALLINT *nullable2 = nullable ? nullable : &num_buf[3];

  int rc = SQLDescribeCol(hstmt, col, col_name2, col_buf_len2, name_len2,
             data_type2, col_size2, dec_digits2, nullable2);
  if (print_data)
  {
    if (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
      std::cout << "SQLDescribeCol [ Column: " << col << " ][ Name: " <<
        col_name2 << " ][ Name Len: " << *name_len2 << " ][ Data Type: " <<
        *data_type2 << " ][ Col Size: " << *col_size2 << " ][ Dec Digits : " <<
        *dec_digits2 << " ][ Nullable: " << *nullable2 << " ]" << std::endl;
    }
    else
    {
      odbc::xbuf msg(2048);
      print_diag(rc, SQL_HANDLE_STMT, hstmt, msg, __FILE__, __LINE__);
    }
  }

  return rc;
}

struct database
{
  SQLHSTMT hstmt = nullptr;
  xstring db = nullptr;

  void drop()
  {
    sql(hstmt, "DROP DATABASE IF EXISTS `" + db + "`");
  }

  database(SQLHSTMT h, xstring dbname) : hstmt(h), db(dbname)
  {
    drop();
    sql(hstmt, "CREATE DATABASE `" + db + "`");
  }

  operator SQLCHAR*() const
  {
    return db;
  }

  ~database()
  {
    drop();
  }
};

struct table
{
  SQLHSTMT hstmt = nullptr;
  xstring database = nullptr;
  xstring table_name;
  xstring qualifier;
  xstring table_extra = nullptr;

  void drop()
  {
    sql(hstmt, "DROP TABLE IF EXISTS `" + qualifier + "`");
  }

  void create(xstring def)
  {
    sql(hstmt, "CREATE TABLE `" + qualifier + "`(" + def + ")" + table_extra);
  }

  void insert(xstring vals)
  {
    sql(hstmt, "INSERT INTO `" + qualifier + "` VALUES " + vals);
  }

  table(SQLHSTMT stmt, xstring db, xstring tab, xstring def,
        xstring extra = nullptr) : hstmt(stmt),
      database(db), table_name(tab), table_extra(extra)
  {
    qualifier = (!database.empty() ? database + "`.`" : "" ) + table_name;
    drop();
    create(def);
  }

  table(SQLHSTMT stmt, xstring tab, xstring def) : table(stmt, nullptr, tab, def)
  {}

  ~table()
  {
    stmt_close(hstmt);
    stmt_reset(hstmt);
    drop();
  }
};


struct view
{
  SQLHSTMT hstmt = nullptr;
  xstring database = nullptr;
  xstring view_name;

  void drop()
  {
    xstring qualifier = (!database.empty() ? database + "`.`" : "" ) + view_name;
    sql(hstmt, "DROP VIEW IF EXISTS `" + qualifier + "`");
  }

  void create(xstring def)
  {
    xstring qualifier = (!database.empty() ? database + "`.`" : "" ) + view_name;
    sql(hstmt, "CREATE VIEW `" + qualifier + "`" + def);
  }


  view(SQLHSTMT stmt, xstring db, xstring tab, xstring def) : hstmt(stmt),
      database(db), view_name(tab)
  {
    drop();
    create(def);
  }

  view(SQLHSTMT stmt, xstring tab, xstring def) : view(stmt, nullptr, tab, def)
  {}

  ~view()
  {
    stmt_close(hstmt);
    stmt_reset(hstmt);
    drop();
  }
};


struct procedure
{
  SQLHSTMT hstmt = nullptr;
  xstring database = nullptr;
  xstring proc_name;

  void drop()
  {
    xstring qualifier = (!database.empty() ? database + "`.`" : "" ) + proc_name;
    sql(hstmt, "DROP PROCEDURE IF EXISTS `" + qualifier + "`");
  }

  void create(xstring def)
  {
    xstring qualifier = (!database.empty() ? database + "`.`" : "" ) + proc_name;
    sql(hstmt, "CREATE PROCEDURE `" + qualifier + "`" + def);
  }


  procedure(SQLHSTMT stmt, xstring db, xstring proc, xstring def) : hstmt(stmt),
      database(db), proc_name(proc)
  {
    drop();
    create(def);
  }

  procedure(SQLHSTMT stmt, xstring proc, xstring def) : procedure(stmt, nullptr, proc, def)
  {}

  ~procedure()
  {
    stmt_close(hstmt);
    stmt_reset(hstmt);
    drop();
  }
};


struct connection
{
  DECLARE_BASIC_HANDLES(henv, hdbc, hstmt);

  std::vector<SQLHANDLE> hstmt_list;

  bool m_savefile = false;
  bool m_alloc_connect = false;

  xstring m_connout;
  xstring m_connstr = nullptr;

  xstring m_dsn;
  xstring m_uid;
  xstring m_pwd;
  xstring m_db;
  xstring m_options;
  xstring m_driver;
  xstring m_socket;
  xstring m_auth;
  xstring m_plugindir;
  int m_port = 0;

  /*
    dsn - DSN name, nullptr to use the default DSN, "" to use DRIVER
  */
  connection(
             xstring dsn, xstring uid, xstring pwd, xstring db,
             xstring options = nullptr, int port = 0, xstring sock = nullptr,
             xstring auth = nullptr, xstring plugindir = nullptr
             ) : m_alloc_connect(true)
  {
    m_driver = mydriver;
    m_dsn = dsn ? dsn : xstring(mydsn);
    m_uid = uid ? uid : xstring(myuid);
    m_pwd = pwd ? pwd : xstring(mypwd);
    m_db = db ? db : xstring(mydb);
    m_options = options;
    m_socket = sock ? sock : xstring(mysock);
    m_auth = auth ? auth : xstring(myauth);
    m_plugindir = plugindir ? plugindir : xstring(myplugindir);
    m_port = port ? port : myport;

    alloc_and_connect();
  }

  connection() : connection(nullptr, nullptr, nullptr, nullptr, nullptr)
  { }

  connection(bool do_connect) : m_alloc_connect(do_connect)
  {
    if (do_connect)
    {
      connection();
    }
    else
    {
      m_dsn = xstring(mydsn);
      m_uid = xstring(myuid);
      m_pwd = xstring(mypwd);
      m_db = xstring(mydb);
      m_socket = xstring(mysock);
      m_auth = xstring(myauth);
      m_plugindir = xstring(myplugindir);
      m_connstr = make_conn_str();
    }
  }

  connection(xstring connstr) : m_connstr(connstr), m_alloc_connect(true)
  {
    alloc_and_connect();
  }

  SQLHSTMT add_stmt()
  {
    SQLHSTMT local_hstmt = nullptr;
    ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &local_hstmt));
    hstmt = local_hstmt;
    hstmt_list.push_back(hstmt);
    return hstmt;
  }

  void alloc_and_connect()
  {
    if (!m_alloc_connect)
      return;

    ok_env(henv, SQLAllocHandle(SQL_HANDLE_ENV, nullptr, &henv));
    ok_env(henv, SQLSetEnvAttr(henv, SQL_ATTR_ODBC_VERSION,
                                (SQLPOINTER)SQL_OV_ODBC3, 0));

    ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc));
    check_dbc(hdbc, get_connection());
    if(!m_savefile)
      add_stmt();
  }

  xstring make_conn_str(bool use_driver = false, bool hide_password = false)
  {
    // If connection string is already given we will use only what is in there
    if (m_connstr)
      return m_connstr;

    xstring connstr;
    if (!use_driver)
      connstr.append("DSN=" + m_dsn);
    else
      connstr.append("DRIVER=" + m_driver);

    if (m_uid.size()) connstr.append(";UID=" + m_uid);
    if (m_pwd.size()) connstr.append(";PWD=" + (hide_password ? xstring("******") : m_pwd));
    if (m_db.size()) connstr.append(";DATABASE=" + m_db);
    if (m_socket.size()) connstr.append(";SOCKET=" + m_socket);
    if (m_port) connstr.append(";PORT=" + std::to_string(m_port));
    if (m_auth.size()) connstr.append(";DEFAULTAUTH=" + m_auth);
    if (m_plugindir.size()) connstr.append(";PLUGINDIR=" + m_plugindir);
    if (m_options.size()) connstr.append(";" + m_options);

    return connstr;
  }

  int get_connection()
  {
    bool use_driver = m_dsn && m_dsn.empty();
    xstring connIn = make_conn_str(use_driver, false);
    xbuf connOut(1024), buf(32);
    SQLSMALLINT len = 0;
    SQLRETURN rc = SQL_SUCCESS;

    rc = SQLDriverConnect(hdbc, NULL, connIn, SQL_NTS, connOut,
                          1024, &len, SQL_DRIVER_NOPROMPT);
    m_connout = connOut;
    m_savefile = (m_connout.find("SAVEFILE=") != std::string::npos);

    if (!SQL_IS_SUCCESS(rc))
    {
      /* re-build and print the connection string with hidden password */
      std::cout << "# Connection failed with the following Connection string:"
                << std::endl << make_conn_str(use_driver, true) << std::endl;
      return rc;
    }

    rc = SQLSetConnectAttr(hdbc, SQL_ATTR_AUTOCOMMIT,
                           (SQLPOINTER)SQL_AUTOCOMMIT_ON, 0);
    if (!SQL_IS_SUCCESS(rc))
      return rc;

    if (unicode_driver < 0)
    {
      rc= SQLGetInfo(hdbc, SQL_DRIVER_NAME, buf, 32, NULL);

      if (SQL_IS_SUCCESS(rc))
      {
        /* UNICODE driver file name contains 8w.{dll|so} */
        xstring driver_name = buf;
        unicode_driver = driver_name.find("8w.") ? 1 : 0;
      }
    }

    return rc;
  }

  ~connection()
  {
    if(!m_alloc_connect)
      return;

    try
    {
      for (auto el_hstmt : hstmt_list)
      {
        stmt_close(el_hstmt);
        stmt_reset(el_hstmt);
        ok_stmt(el_hstmt, SQLFreeStmt(el_hstmt, SQL_DROP));
      }
      (void)SQLEndTran(SQL_HANDLE_DBC, hdbc, SQL_COMMIT);
      ok_con(hdbc, SQLDisconnect(hdbc));
      ok_con(hdbc, SQLFreeConnect(hdbc));
      ok_env(henv, SQLFreeEnv(henv));
    }
    catch(...)
    {}
  }

  operator SQLHDBC() const
  {
    return hdbc;
  }
};


struct HENV
{
  SQLHENV henv;

  HENV(unsigned long v)
  {
    size_t ver = v;
    ok_env(henv, SQLAllocHandle(SQL_HANDLE_ENV, nullptr, &henv));
    ok_env(henv, SQLSetEnvAttr(henv, SQL_ATTR_ODBC_VERSION,
                                (SQLPOINTER)ver, 0));
  }

  HENV() : HENV(SQL_OV_ODBC3)
  {}

  operator SQLHENV() const
  {
    return henv;
  }

  ~HENV()
  {
    SQLFreeHandle(SQL_HANDLE_ENV, henv);
  }
};

struct HDBC
{
  SQLHDBC hdbc = nullptr;
  SQLHENV henv;
  xstring connout;
  connection con;

  void connect(xstring opts = nullptr)
  {
    xbuf buf(4096);
    xstring cstr = con.m_connstr;
    cstr.append(opts);
    ok_con(hdbc, SQLDriverConnect(hdbc, NULL, cstr, SQL_NTS, buf,
                                  512, nullptr, SQL_DRIVER_NOPROMPT));
    connout = buf;
  }

  HDBC(SQLHENV h, bool do_connect = true) : henv(h), con(false)
  {
    ok_env(henv, SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc));
    if (do_connect)
      connect();
    ok_con(hdbc, SQLSetConnectAttr(hdbc, SQL_ATTR_AUTOCOMMIT,
                                  (SQLPOINTER)SQL_AUTOCOMMIT_ON, 0));
  }

  operator SQLHDBC() const
  {
    return hdbc;
  }

  ~HDBC()
  {
    SQLDisconnect(hdbc);
    SQLFreeHandle(SQL_HANDLE_DBC, hdbc);
  }
};

struct HSTMT
{
  SQLHDBC hdbc;
  SQLHSTMT hstmt = nullptr;
  HSTMT(SQLHDBC dbc) : hdbc(dbc)
  {
    ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt));
  }

  operator SQLHSTMT() const
  {
    return hstmt;
  }

  ~HSTMT()
  {
    SQLFreeHandle(SQL_HANDLE_STMT, hstmt);
  }
};


struct HDESC
{
  SQLHDBC hdbc;
  SQLHDESC hdesc;
  HDESC(SQLHDBC dbc) : hdbc(dbc)
  {
    ok_con(hdbc, SQLAllocHandle(SQL_HANDLE_DESC, hdbc, &hdesc));
  }

  operator SQLHDESC() const
  {
    return hdesc;
  }

  ~HDESC()
  {
    SQLFreeHandle(SQL_HANDLE_DESC, hdesc);
  }
};


SQLSMALLINT num_result_cols(SQLHSTMT hstmt)
{
  SQLSMALLINT cnum = 0;
  ok_stmt(hstmt, SQLNumResultCols(hstmt, &cnum));
  return cnum;
}

SQLLEN num_result_rows(SQLHSTMT hstmt)
{
  SQLLEN rnum = 0;
  ok_stmt(hstmt, SQLRowCount(hstmt, &rnum));
  return rnum;
}

const char* _my_fetch_data(SQLHSTMT hstmt, xbuf &buf, SQLUSMALLINT icol,
                          SQLSMALLINT target_type,
                          bool print_data = true, int len = 128)
{
    SQLLEN nLen;

    SQLGetData(hstmt, icol, target_type, (SQLCHAR*)buf, buf.size, &nLen);
    /* If Null value - putting down smth meaningful. also that allows caller to
       better/(in more easy way) test the value */
    if (nLen < 0)
    {
      strcpy(buf, "(Null)");
    }
    if (print_data)
      printMessage(" my_fetch_str: %.*s(%ld bytes)",
                  strlen(buf) > len ? len : strlen(buf), (char*)buf, nLen);
    else
      printMessage(" my_fetch_str: [skipped data] (%ld bytes)", nLen);
    return buf;
}

const char* my_fetch_str(SQLHSTMT hstmt, xbuf &buf, SQLUSMALLINT icol,
                         bool print_data = true, int len = 128)
{
  return _my_fetch_data(hstmt, buf, icol, SQL_C_CHAR, print_data, len);
}

const char* my_fetch_data(SQLHSTMT hstmt, xbuf &buf, SQLUSMALLINT icol,
                          bool print_data = true, int len = 128)
{
  return _my_fetch_data(hstmt, buf, icol, SQL_C_BINARY, print_data, len);
}

void select_one_str(SQLHSTMT hstmt, const char* query, xbuf &buf,
                           SQLSMALLINT icol)
{
  sql(hstmt, query);
  is(SQLFetch(hstmt) == SQL_SUCCESS);
  my_fetch_str(hstmt, buf, icol, true, (int)buf.size);
  is(SQLFetch(hstmt) == SQL_NO_DATA);
  ok_stmt(hstmt, SQLFreeStmt(hstmt, SQL_CLOSE));
}

struct temp_user
{
  SQLHSTMT hstmt = nullptr;
  odbc::xstring username;  // full username@host
  odbc::xstring name;      // username without host
  odbc::xstring pass;
  odbc::xbuf buf;

  temp_user(SQLHSTMT stmt, odbc::xstring pwd) : hstmt(stmt), buf(1024), pass(pwd)
  {
    odbc::select_one_str(hstmt, "SELECT USER()", buf, 1);

    username = odbc::xstring(buf);
    size_t pos = username.find_first_of('@');

    name = "temp_user_" + username.substr(0, pos);

    // Note: Host part in quotes because, e.g. in OCI there are host names like
    // `foo-172-20-0-2-1c1be116-ff67...` which would lead to SQL syntax error
    // if not quotted.

    username = "`" + name + "`@`" + username.substr(pos+1) + "`";

    odbc::sql(hstmt, "DROP USER IF EXISTS " + username);
    odbc::sql(hstmt, "CREATE USER " + username +
                      " IDENTIFIED BY '" + pass + "'");
  }

  temp_user(SQLHSTMT stmt) : temp_user(stmt, "temp_pass")
  { }

  ~temp_user()
  {
    odbc::sql(hstmt, "DROP USER " + username, true);
  }
};


struct expire_user : public temp_user
{
  odbc::xstring db;        // database

  expire_user(SQLHSTMT stmt) : temp_user(stmt)
  {
    odbc::select_one_str(hstmt, "SELECT DATABASE()", buf, 1);
    db = buf;
    odbc::sql(hstmt, "GRANT ALL on `" + db + "`.* to " + username);
    name = username.substr(0, username.find('@', 0));
    odbc::sql(hstmt, "ALTER USER " + username +
                      " PASSWORD EXPIRE");
  }
};

}; // namespace odbc

#endif // !ODBC_UTIL_H
