// Copyright (c) 2014, 2024, Oracle and/or its affiliates.
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

#ifdef UNICODE
/*
 Unicode must be disabled, otherwise we will have lots of compiler errors
 about conflicting declarations in odbcinstext.h and odbcinst.h
*/
#undef UNICODE
#endif

#include <odbcinstext.h>
#include <cstdlib>
#include <cstring>

static const char *MYODBC_OPTIONS[][3] = {
  {"SERVER",            "T", "The host name of the MySQL server"},
  {"USER",              "T", "The user name used to connect to MySQL"},
  {"DATABASE",          "T", "The default database"},
  {"PORT",              "T", "The TCP/IP port if SERVER is not localhost"},
  {"SOCKET",            "T", "The Unix socket file if SERVER=localhost"},
  {"INITSTMT",          "T", "Initial statement executed at the connecting time"},
  {"CHARSET",           "T", "The character set to use for the connection"},
  {"PREFETCH",          "T", "Prefecth from server by N rows at a time"},
  {"READTIMEOUT",       "T", "The timeout in seconds for attempts to read from the server"},
  {"WRITETIMEOUT",      "T", "The timeout in seconds for attempts to write to the server"},
  {"SSLCA",             "F", "The path to a file with a list of trust SSL CAs"},
  {"SSLCAPATH",         "F", "The path to a directory that contains trusted SSL CA certificates in PEM format"},
  {"SSLCERT",           "F", "The name of the SSL certificate file to use for establishing a secure connection"},
  {"SSLKEY",            "F", "The name of the SSL key file to use for establishing a secure connection"},
  {"SSLCIPHER",         "T", "A list of permissible ciphers to use for SSL encryption. The cipher list has the same format as the openssl ciphers command"},
  {"SSLVERIFY",         "C", "If set to 1, the SSL certificate will be verified when used with the MySQL connection"},
  {"FOUND_ROWS",        "C", "Return matched rows instead of affected rows"},
  {"BIG_PACKETS",       "C", "Allow big result set"},
  {"NO_PROMPT",         "C", "Don't prompt when connecting"},
  {"DYNAMIC_CURSOR",    "C", "Enable Dynamic Cursors"},
  {"NO_DEFAULT_CURSOR", "C", "Disable driver-provided cursor support"},
  {"NO_LOCALE",         "C", "Don't use setlocale()"},
  {"PAD_SPACE",         "C", "Pad CHAR to full length with space"},
  {"FULL_COLUMN_NAMES", "C", "Include table name in SQLDescribeCol()"},
  {"COMPRESSED_PROTO",  "C", "Use the compressed client/server protocol."},
  {"IGNORE_SPACE",      "C", "Ignore space after function names"},
  {"NO_BIGINT",         "C", "Treat BIGINT columns as INT columns"},
  {"NO_CATALOG",        "C", "Disable catalog support"},
  {"NO_SCHEMA",         "C", "Disable schema support"},
  {"USE_MYCNF",         "C", "Read options from my.cnf"},
  {"SAFE",              "C", "Add some extra safety checks"},
  {"NO_TRANSACTIONS",   "C", "Disable transaction support"},
  {"LOG_QUERY",         "C", "Log queries to %TEMP%\myodbc.sql"},
  {"NO_CACHE",          "C", "Don't cache results of forward-only cursors"},
  {"FORWARD_CURSOR",    "C", "Force use of forward-only cursors"},
  {"AUTO_RECONNECT",    "C", "Enable automatic reconnect"},
  {"ENABLE_DNS_SRV",    "C", "Enable usage of DNS SRV records"},
  {"MULTI_HOST",        "C", "Enable usage of multiple hosts"},
  {"AUTO_IS_NULL",      "C", "Enable SQL_AUTO_IS_NULL"},
  {"ZERO_DATE_TO_MIN",  "C", "Return SQL_NULL_DATA for zero date"},
  {"MIN_DATE_TO_ZERO",  "C", "Bind minimal date as zero date"},
  {"MULTI_STATEMENTS",  "C", "Allow multiple statements"},
  {"COLUMN_SIZE_S32",   "C", "Limit column size to signed 32-bit range"},
  {"NO_BINARY_RESULT",  "C", "Always handle binary function results as character data"},
  {"DFLT_BIGINT_BIND_STR",    "C", "Causes BIGINT parameters to be bound as strings"},
  {"INTERACTIVE",             "C", "Interactive Client"},
  {"CAN_HANDLE_EXP_PWD",      "C", "Can Handle Expired Password"},
  {"ENABLE_CLEARTEXT_PLUGIN", "C", "Enable Cleartext Authentication"},
  {"NO_SSPS",                 "C", "Prepare statements on the client"},
  {"ENABLE_LOCAL_INFILE",     "C", "Enable LOAD DATA LOCAL INFILE statements"},
  {NULL, NULL, NULL}
};

static const char *paramsOnOff[] = {"0", "1", NULL};

int ODBCINSTGetProperties(HODBCINSTPROPERTY propertyList)
{
  int i= 0;
  do
  {
    /* Allocate next element */
    propertyList->pNext= (HODBCINSTPROPERTY)malloc(sizeof(ODBCINSTPROPERTY));
    propertyList= propertyList->pNext;

    /* Reset everything to zero */
    memset(propertyList, 0, sizeof(ODBCINSTPROPERTY));

    /* copy the option name */
    strncpy( propertyList->szName, MYODBC_OPTIONS[i][0],
             strlen(MYODBC_OPTIONS[i][0]));

    /* We make the value always empty by default */
    propertyList->szValue[0]= '\0';

    switch(MYODBC_OPTIONS[i][1][0])
    {
      /* COMBOBOX */
      case 'C':
        propertyList->nPromptType= ODBCINST_PROMPTTYPE_COMBOBOX;

        /* Prepare data for the combobox */
        propertyList->aPromptData= (char**)malloc(sizeof(paramsOnOff));
        memcpy(propertyList->aPromptData, paramsOnOff, sizeof(paramsOnOff));
      break;

      /* FILE NAME */
      case 'F':
        propertyList->nPromptType= ODBCINST_PROMPTTYPE_FILENAME;
      break;

      /* TEXTBOX */
      case 'T':
      default:
        propertyList->nPromptType= ODBCINST_PROMPTTYPE_TEXTEDIT;
    }

    /* Finally, set the help text */
    propertyList->pszHelp= strdup(MYODBC_OPTIONS[i][2]);

  }while (MYODBC_OPTIONS[++i][0]);

  return 1;
}
