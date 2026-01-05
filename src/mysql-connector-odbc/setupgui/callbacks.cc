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


/* TODO no L"" */
#include "setupgui.h"
#include "stringutil.h"
#include "windows/resource.h"
#include <driver.h>
#include <error.h>

#include <codecvt>
#include <locale>

SQLWCHAR **errorMsgs= NULL;

SQLHDBC hDBC= SQL_NULL_HDBC;


/*
  Tests if it is possible to establish connection using given DS
  returns message text. user is responsible to free that text after use.
*/
SQLWSTRING mytest(HWND hwnd, DataSource *params)
{
  SQLWSTRING msg;
  SQLWCHAR tmpbuf[1024];

  SQLHENV hEnv = nullptr;
  SQLHDBC hDbc = nullptr;
  /*
    In case of file data source we do not want it to be created
    when clicking the Test button
  */
  myodbc::HENV henv;
  auto preservedSavefile = params->opt_SAVEFILE;
  params->opt_SAVEFILE.set_default(nullptr);

  try
  {
    myodbc::HDBC hdbc(henv, params);
    msg = _W(L"Connection successful");
  }
  catch(MYERROR &e)
  {
    // Use the ability of optionStr to convert MBchar -> Wchar
    optionStr e_msg;
    e_msg = e.message;
    optionStr e_sqlstate;
    e_sqlstate = e.sqlstate;

    msg = _W(L"Connection failed with the following error:\n");
    msg.append((const SQLWSTRING &)e_msg);
    msg.append(_W(L"["));
    msg.append((const SQLWSTRING &)e_sqlstate);
    msg.append(_W(L"]"));
  }

  /* Restore savefile parameter */
  params->opt_SAVEFILE = preservedSavefile;
  return msg;
}


BOOL mytestaccept(HWND hwnd, DataSource* params)
{
  /* TODO validation */
  return TRUE;
}


std::vector<SQLWSTRING> mygetdatabases(HWND hwnd, DataSource* params)
{
  SQLRETURN   ret;
  SQLWCHAR    catalog[MYODBC_DB_NAME_MAX];
  SQLLEN      n_catalog;
  auto   preserved_database = params->opt_DATABASE;
  auto   preserved_no_catalog = params->opt_NO_CATALOG;
  std::vector<SQLWSTRING> result;
  result.reserve(20);

  /*
    In case of file data source we do not want it to be created
    when clicking the Test button
  */
  auto preserved_savefile = params->opt_SAVEFILE;

  params->opt_SAVEFILE.set_default(nullptr);
  params->opt_DATABASE.set_default(nullptr);
  params->opt_NO_CATALOG.set_default(false);

try {
    myodbc::HENV henv;
    myodbc::HDBC hdbc(henv, params);

    params->opt_SAVEFILE = preserved_savefile;
    params->opt_DATABASE = preserved_database;
    params->opt_NO_CATALOG = preserved_no_catalog;

    myodbc::HSTMT hstmt(hdbc);

    SQLWCHAR tmpbuf[1024];
    SQLWCHAR empty = 0;
    SQLWCHAR *w_all = _W(L"%");

    ret = SQLTablesW(hstmt, w_all, SQL_NTS,
                     &empty, 0, &empty, 0,
                     &empty, 0);

    if (!SQL_SUCCEEDED(ret)) return result;

    ret = SQLBindCol(hstmt, 1, SQL_C_WCHAR, catalog, MYODBC_DB_NAME_MAX,
                     &n_catalog);
    if (!SQL_SUCCEEDED(ret)) return result;

    while (true) {
      if (result.size() % 20) result.reserve(result.size() + 20);

      if (SQL_SUCCEEDED(SQLFetch(hstmt)))
        result.emplace_back(catalog);
      else
        break;
    }

  } catch (...) {
  }

  return result;
}


std::vector<SQLWSTRING> mygetcharsets(HWND hwnd, DataSource* params)
{
  SQLRETURN   ret;
  SQLWCHAR    charset[MYODBC_DB_NAME_MAX] = { 0 };
  SQLLEN      n_charset = 0;
  auto        preserved_database = params->opt_DATABASE;
  auto        preserved_no_catalog= params->opt_NO_CATALOG;
  SQLWCHAR tmpbuf[1024];
  std::vector<SQLWSTRING> csl;
  csl.reserve(20);

  /*
    In case of file data source we do not want it to be created
    when clicking the Test button
  */
  auto preserved_savefile = params->opt_SAVEFILE;
  params->opt_SAVEFILE.set_default(nullptr);
  params->opt_DATABASE.set_default(nullptr);
  params->opt_NO_CATALOG.set_default(false);

try {
    myodbc::HENV henv;
    myodbc::HDBC hdbc(henv, params);

    params->opt_SAVEFILE = preserved_savefile;
    params->opt_DATABASE = preserved_database;
    params->opt_NO_CATALOG = preserved_no_catalog;

    myodbc::HSTMT hstmt(hdbc);

#ifdef DRIVER_ANSI
    /* Skip undesired charsets */
    nReturn = SQLExecDirectW(hStmt,
                             _W(L"SHOW CHARACTER SET WHERE "
                                "charset <> 'utf16' AND "
                                "charset <> 'utf32' AND "
                                "charset <> 'ucs2'"),
                             SQL_NTS);
#else
    ret = SQLExecDirectW(hstmt, _W(L"SHOW CHARACTER SET"), SQL_NTS);
#endif

    if (!SQL_SUCCEEDED(ret)) return csl;

    ret = SQLBindCol(hstmt, 1, SQL_C_WCHAR, charset, MYODBC_DB_NAME_MAX,
                     &n_charset);
    if (!SQL_SUCCEEDED(ret)) return csl;

    while (true) {
      if (csl.size() % 20) csl.reserve(csl.size() + 20);

      if (SQL_SUCCEEDED(SQLFetch(hstmt)))
        csl.emplace_back(charset);
      else
        break;
    }

  } catch (...) {

  }
  return csl;
}


/* Init DataSource from the main dialog controls */
void syncData(HWND hwnd, DataSource *params)
{
  GET_STRING(DSN);
  GET_STRING(DESCRIPTION);
  GET_STRING(SERVER);
  GET_STRING(SOCKET);
  GET_UNSIGNED(PORT);
  GET_STRING(UID);
  GET_STRING(PWD);
  GET_COMBO(DATABASE);

#ifdef _WIN32
  /* use this flag exclusively for Windows */
  if (READ_BOOL(hwnd, IDC_RADIO_NAMED_PIPE))
    params->opt_NAMED_PIPE = true;
  else
    params->opt_NAMED_PIPE.set_default(false);
#endif
}


/* Set the main dialog controls using DataSource */
void syncForm(HWND hwnd, DataSource *params)
{
  SET_STRING(DSN);
  SET_STRING(DESCRIPTION);
  SET_STRING(SERVER);
  SET_UNSIGNED(PORT);
  SET_STRING(UID);
  SET_STRING(PWD);
  SET_STRING(SOCKET);
  SET_COMBO(DATABASE);

#ifdef _WIN32
  if (params->opt_NAMED_PIPE)
  {
    SET_RADIO(hwnd, IDC_RADIO_NAMED_PIPE, TRUE);
  }
  else
  {
    SET_RADIO(hwnd, IDC_RADIO_tcp, TRUE);
  }
  SwitchTcpOrPipe(hwnd, params->opt_NAMED_PIPE);
#else
  if (params->opt_SOCKET)
  {
    /* this flag means the socket file in Linux */
    SET_CHECKED(__UNUSED, use_socket_file, TRUE);
    SET_SENSITIVE(SERVER, FALSE);
    SET_SENSITIVE(SOCKET, TRUE);
  }
  else
  {
    SET_CHECKED(__UNUSED, use_tcp_ip_server, TRUE);
    SET_SENSITIVE(SERVER, TRUE);
    SET_SENSITIVE(SOCKET, FALSE);
  }
#endif
}

#undef CLIENT_INTERACTIVE
/*
 Sets the DataSource fields from the dialog inputs
*/
void syncTabsData(HWND hwnd, DataSource *params)
{  /* 1 - Connection */
  GET_BOOL_TAB(CONNECTION_TAB, BIG_PACKETS);
  GET_BOOL_TAB(CONNECTION_TAB, COMPRESSED_PROTO);
  GET_BOOL_TAB(CONNECTION_TAB, NO_PROMPT);
  GET_BOOL_TAB(CONNECTION_TAB, AUTO_RECONNECT);
  GET_BOOL_TAB(CONNECTION_TAB, MULTI_STATEMENTS);
  GET_BOOL_TAB(CONNECTION_TAB, CLIENT_INTERACTIVE);
  GET_BOOL_TAB(CONNECTION_TAB, CAN_HANDLE_EXP_PWD);
  GET_BOOL_TAB(CONNECTION_TAB, GET_SERVER_PUBLIC_KEY);
  GET_BOOL_TAB(CONNECTION_TAB, ENABLE_DNS_SRV);

  if (params->opt_ENABLE_DNS_SRV.is_set() && params->opt_ENABLE_DNS_SRV)
    params->opt_PORT.set_default(3306);

  GET_BOOL_TAB(CONNECTION_TAB, MULTI_HOST);

  GET_COMBO_TAB(CONNECTION_TAB, CHARSET);
  GET_STRING_TAB(CONNECTION_TAB, INITSTMT);
  GET_STRING_TAB(CONNECTION_TAB, PLUGIN_DIR);

  /* 2 - Authentication */
  GET_BOOL_TAB(AUTH_TAB, ENABLE_CLEARTEXT_PLUGIN);
#ifdef _WIN32
  GET_STRING_TAB(AUTH_TAB, AUTHENTICATION_KERBEROS_MODE);
#endif
  GET_STRING_TAB(AUTH_TAB, DEFAULT_AUTH);
#if MFA_ENABLED
  GET_STRING_TAB(AUTH_TAB, pwd2);
  GET_STRING_TAB(AUTH_TAB, pwd3);
#endif
  GET_STRING_TAB(AUTH_TAB, OCI_CONFIG_FILE);
  GET_STRING_TAB(AUTH_TAB, OCI_CONFIG_PROFILE);

  /* 3 - Metadata*/
  GET_BOOL_TAB(METADATA_TAB, NO_BIGINT);
  GET_BOOL_TAB(METADATA_TAB, NO_BINARY_RESULT);
  GET_BOOL_TAB(METADATA_TAB, FULL_COLUMN_NAMES);
  GET_BOOL_TAB(METADATA_TAB, NO_CATALOG);
  GET_BOOL_TAB(METADATA_TAB, NO_SCHEMA);
  GET_BOOL_TAB(METADATA_TAB, COLUMN_SIZE_S32);

  /* 4 - Cursors/Results */
  GET_BOOL_TAB(CURSORS_TAB, FOUND_ROWS);
  GET_BOOL_TAB(CURSORS_TAB, AUTO_IS_NULL);
  GET_BOOL_TAB(CURSORS_TAB, DYNAMIC_CURSOR);
  GET_BOOL_TAB(CURSORS_TAB, NO_DEFAULT_CURSOR);
  GET_BOOL_TAB(CURSORS_TAB, PAD_SPACE);
  GET_BOOL_TAB(CURSORS_TAB, NO_CACHE);
  GET_BOOL_TAB(CURSORS_TAB, FORWARD_CURSOR);
  GET_BOOL_TAB(CURSORS_TAB, ZERO_DATE_TO_MIN);

  if (READ_BOOL_TAB(CURSORS_TAB, cursor_prefetch_active))
  {
    GET_UNSIGNED_TAB(CURSORS_TAB, PREFETCH);
  }
  else
  {
    params->opt_PREFETCH = 0;
  }
  /* 5 - debug*/
  GET_BOOL_TAB(DEBUG_TAB,LOG_QUERY);

  /* 6 - ssl related */
  GET_STRING_TAB(SSL_TAB, SSL_KEY);
  GET_STRING_TAB(SSL_TAB, SSL_CERT);
  GET_STRING_TAB(SSL_TAB, SSL_CA);
  GET_STRING_TAB(SSL_TAB, SSL_CAPATH);
  GET_STRING_TAB(SSL_TAB, SSL_CIPHER);
  GET_COMBO_TAB(SSL_TAB, SSL_MODE);

  GET_STRING_TAB(SSL_TAB, RSAKEY);
  GET_BOOL_TAB(SSL_TAB, NO_TLS_1_2);
  GET_BOOL_TAB(SSL_TAB, NO_TLS_1_3);
  GET_STRING_TAB(SSL_TAB, TLS_VERSIONS);
  GET_STRING_TAB(SSL_TAB, SSL_CRL);
  GET_STRING_TAB(SSL_TAB, SSL_CRLPATH);

  /* 7 - Misc*/
  GET_BOOL_TAB(MISC_TAB, SAFE);
  GET_BOOL_TAB(MISC_TAB, NO_LOCALE);
  GET_BOOL_TAB(MISC_TAB, IGNORE_SPACE);
  GET_BOOL_TAB(MISC_TAB, USE_MYCNF);
  GET_BOOL_TAB(MISC_TAB, NO_TRANSACTIONS);
  GET_BOOL_TAB(MISC_TAB, MIN_DATE_TO_ZERO);
  GET_BOOL_TAB(MISC_TAB, NO_SSPS);
  GET_BOOL_TAB(MISC_TAB, DFLT_BIGINT_BIND_STR);
  GET_BOOL_TAB(MISC_TAB, NO_DATE_OVERFLOW);
  GET_BOOL_TAB(MISC_TAB, ENABLE_LOCAL_INFILE);
  GET_STRING_TAB(MISC_TAB, LOAD_DATA_LOCAL_DIR);
}

/*
 Sets the options in the dialog tabs using DataSource
*/
void syncTabs(HWND hwnd, DataSource *params)
{
  /* 1 - Connection */
  SET_BOOL_TAB(CONNECTION_TAB, BIG_PACKETS);
  SET_BOOL_TAB(CONNECTION_TAB, COMPRESSED_PROTO);
  SET_BOOL_TAB(CONNECTION_TAB, NO_PROMPT);
  SET_BOOL_TAB(CONNECTION_TAB, AUTO_RECONNECT);
  SET_BOOL_TAB(CONNECTION_TAB, ENABLE_DNS_SRV);
  SET_BOOL_TAB(CONNECTION_TAB, MULTI_STATEMENTS);
  SET_BOOL_TAB(CONNECTION_TAB, CLIENT_INTERACTIVE);
  SET_BOOL_TAB(CONNECTION_TAB, CAN_HANDLE_EXP_PWD);
  SET_BOOL_TAB(CONNECTION_TAB, GET_SERVER_PUBLIC_KEY);
  SET_BOOL_TAB(CONNECTION_TAB, ENABLE_DNS_SRV);
  SET_BOOL_TAB(CONNECTION_TAB, MULTI_HOST);

#ifdef _WIN32
  if ( getTabCtrlTabPages(CONNECTION_TAB-1))
#endif
  {
    SET_COMBO_TAB(CONNECTION_TAB, CHARSET);
    SET_STRING_TAB(CONNECTION_TAB, INITSTMT);
    SET_STRING_TAB(CONNECTION_TAB, PLUGIN_DIR);
  }

  /* 2 - Authentication */
  SET_BOOL_TAB(AUTH_TAB, ENABLE_CLEARTEXT_PLUGIN);
#ifdef _WIN32
  SET_STRING_TAB(AUTH_TAB, AUTHENTICATION_KERBEROS_MODE);
#endif
  SET_STRING_TAB(AUTH_TAB, DEFAULT_AUTH);
#if MFA_ENABLED
  SET_STRING_TAB(AUTH_TAB, PWD2);
  SET_STRING_TAB(AUTH_TAB, PWD3);
#endif
  SET_STRING_TAB(AUTH_TAB, OCI_CONFIG_FILE);
  SET_STRING_TAB(AUTH_TAB, OCI_CONFIG_PROFILE);

  /* 3 - Metadata*/
  SET_BOOL_TAB(METADATA_TAB, NO_BIGINT);
  SET_BOOL_TAB(METADATA_TAB, NO_BINARY_RESULT);
  SET_BOOL_TAB(METADATA_TAB, FULL_COLUMN_NAMES);
  SET_BOOL_TAB(METADATA_TAB, NO_CATALOG);
  SET_BOOL_TAB(METADATA_TAB, NO_SCHEMA);
  SET_BOOL_TAB(METADATA_TAB, COLUMN_SIZE_S32);

  /* 4 - Cursors/Results */
  SET_BOOL_TAB(CURSORS_TAB, FOUND_ROWS);
  SET_BOOL_TAB(CURSORS_TAB, AUTO_IS_NULL);
  SET_BOOL_TAB(CURSORS_TAB, DYNAMIC_CURSOR);
  SET_BOOL_TAB(CURSORS_TAB, NO_DEFAULT_CURSOR);
  SET_BOOL_TAB(CURSORS_TAB, PAD_SPACE);
  SET_BOOL_TAB(CURSORS_TAB, NO_CACHE);
  SET_BOOL_TAB(CURSORS_TAB, FORWARD_CURSOR);
  SET_BOOL_TAB(CURSORS_TAB, ZERO_DATE_TO_MIN);

  if(params->opt_PREFETCH > 0)
  {
#ifdef _WIN32
    SET_ENABLED(CURSORS_TAB, IDC_EDIT_PREFETCH, TRUE);
#endif
    SET_CHECKED_TAB(CURSORS_TAB, cursor_prefetch_active, TRUE);
    SET_UNSIGNED_TAB(CURSORS_TAB, PREFETCH);
  }

  /* 5 - debug*/
  SET_BOOL_TAB(DEBUG_TAB,LOG_QUERY);

  /* 6 - ssl related */
#ifdef _WIN32
  if ( getTabCtrlTabPages(SSL_TAB-1) )
#endif
  {
    if(params->opt_SSL_KEY)
      SET_STRING_TAB(SSL_TAB, SSL_KEY);

    if (params->opt_SSL_CERT)
      SET_STRING_TAB(SSL_TAB, SSL_CERT);

    if (params->opt_SSL_CA)
      SET_STRING_TAB(SSL_TAB, SSL_CA);

    if (params->opt_SSL_CAPATH)
      SET_STRING_TAB(SSL_TAB, SSL_CAPATH);

    if (params->opt_SSL_CIPHER)
      SET_STRING_TAB(SSL_TAB, SSL_CIPHER);

    if (params->opt_SSL_MODE)
      SET_COMBO_TAB(SSL_TAB, SSL_MODE);

    if (params->opt_RSAKEY)
      SET_STRING_TAB(SSL_TAB, RSAKEY);

    if (params->opt_SSL_CRL)
      SET_STRING_TAB(SSL_TAB, SSL_CRL);

    if (params->opt_SSL_CRLPATH)
      SET_STRING_TAB(SSL_TAB, SSL_CRLPATH);

    SET_BOOL_TAB(SSL_TAB, NO_TLS_1_2);
    SET_BOOL_TAB(SSL_TAB, NO_TLS_1_3);

    SET_STRING_TAB(SSL_TAB, TLS_VERSIONS);
  }

  /* 7 - Misc*/
  SET_BOOL_TAB(MISC_TAB, SAFE);
  SET_BOOL_TAB(MISC_TAB, NO_LOCALE);
  SET_BOOL_TAB(MISC_TAB, IGNORE_SPACE);
  SET_BOOL_TAB(MISC_TAB, USE_MYCNF);
  SET_BOOL_TAB(MISC_TAB, NO_TRANSACTIONS);
  SET_BOOL_TAB(MISC_TAB, MIN_DATE_TO_ZERO);
  SET_BOOL_TAB(MISC_TAB, NO_SSPS);
  SET_BOOL_TAB(MISC_TAB, DFLT_BIGINT_BIND_STR);
  SET_BOOL_TAB(MISC_TAB, NO_DATE_OVERFLOW);
  SET_BOOL_TAB(MISC_TAB, ENABLE_LOCAL_INFILE);
  SET_STRING_TAB(MISC_TAB, LOAD_DATA_LOCAL_DIR);
}

void FillParameters(HWND hwnd, DataSource *params)
{
  syncData(hwnd, params );
#ifdef _WIN32
  if(getTabCtrlTab())
#endif
    syncTabsData(hwnd, params);
}
