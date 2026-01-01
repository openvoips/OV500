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
 * Installer wrapper implementations.
 *
 * How to add a data-source parameter:
 *    Search for DS_PARAM, and follow the code around it
 *    Add an extra field to the DataSource struct (installer.h)
 *    Add a default value in ds_new()
 *    Initialize/destroy them in ds_new/ds_delete
 *    Print the field in myodbc3i.c
 *    Add to the configuration GUIs
 *
 */

#include <vector>
#include "stringutil.h"
#include "installer.h"

/*
   SQLGetPrivateProfileStringW is buggy in all releases of unixODBC
   as of 2007-12-03, so always use our replacement.
*/
#if USE_UNIXODBC
# define SQLGetPrivateProfileStringW MySQLGetPrivateProfileStringW
int INSTAPI
MySQLGetPrivateProfileStringW(const MyODBC_LPCWSTR lpszSection, const MyODBC_LPCWSTR lpszEntry,
                              const MyODBC_LPCWSTR lpszDefault, LPWSTR lpszRetBuffer,
                              int cbRetBuffer, const MyODBC_LPCWSTR lpszFilename);

#endif


/*
   Most of the installer API functions in iODBC incorrectly reset the
   config mode, so we need to save and restore it whenever we call those
   functions. These macros reduce the clutter a little bit.
*/
#if USE_IODBC
# define SAVE_MODE() UWORD config_mode= config_get();
# define RESTORE_MODE() config_set(config_mode);
#else
# define SAVE_MODE()
# define RESTORE_MODE()
#endif


/* ODBC Installer Config Wrapper */

/* a few constants */
static SQLWCHAR W_EMPTY[]= {0};
static SQLWCHAR W_ODBCINST_INI[]=
  {'O', 'D', 'B', 'C', 'I', 'N', 'S', 'T', '.', 'I', 'N', 'I', 0};
static SQLWCHAR W_ODBC_INI[]= {'O', 'D', 'B', 'C', '.', 'I', 'N', 'I', 0};
static SQLWCHAR W_CANNOT_FIND_DRIVER[]= {'C', 'a', 'n', 'n', 'o', 't', ' ',
                                         'f', 'i', 'n', 'd', ' ',
                                         'd', 'r', 'i', 'v', 'e', 'r', 0};

static SQLWCHAR W_DSN[]= {'D', 'S', 'N', 0};
static SQLWCHAR W_DRIVER[] = {'D', 'R', 'I', 'V', 'E', 'R', 0};
static SQLWCHAR W_Driver[] = {'D', 'r', 'i', 'v', 'e', 'r', 0};
static SQLWCHAR W_DESCRIPTION[] =
  {'D', 'E', 'S', 'C', 'R', 'I', 'P', 'T', 'I', 'O', 'N', 0};
static SQLWCHAR W_SERVER[]= {'S', 'E', 'R', 'V', 'E', 'R', 0};
static SQLWCHAR W_UID[]= {'U', 'I', 'D', 0};
static SQLWCHAR W_USER[]= {'U', 'S', 'E', 'R', 0};
static SQLWCHAR W_PWD[]= {'P', 'W', 'D', 0};
static SQLWCHAR W_PASSWORD[]= {'P', 'A', 'S', 'S', 'W', 'O', 'R', 'D', 0};
#if MFA_ENABLED
static SQLWCHAR W_PWD1[]= {'P', 'W', 'D', '1', 0};
static SQLWCHAR W_PASSWORD1[]= {'P', 'A', 'S', 'S', 'W', 'O', 'R', 'D', '1', 0};
static SQLWCHAR W_PWD2[]= {'P', 'W', 'D', '2', 0};
static SQLWCHAR W_PASSWORD2[]= {'P', 'A', 'S', 'S', 'W', 'O', 'R', 'D', '2', 0};
static SQLWCHAR W_PWD3[]= {'P', 'W', 'D', '3', 0};
static SQLWCHAR W_PASSWORD3[]= {'P', 'A', 'S', 'S', 'W', 'O', 'R', 'D', '3', 0};
#endif
static SQLWCHAR W_DB[]= {'D', 'B', 0};
static SQLWCHAR W_DATABASE[]= {'D', 'A', 'T', 'A', 'B', 'A', 'S', 'E', 0};
static SQLWCHAR W_SOCKET[]= {'S', 'O', 'C', 'K', 'E', 'T', 0};
static SQLWCHAR W_INITSTMT[]= {'I', 'N', 'I', 'T', 'S', 'T', 'M', 'T', 0};
static SQLWCHAR W_OPTION[]= {'O', 'P', 'T', 'I', 'O', 'N', 0};
static SQLWCHAR W_CHARSET[]= {'C', 'H', 'A', 'R', 'S', 'E', 'T', 0};
static SQLWCHAR W_SSLKEY[]= {'S', 'S', 'L', 'K', 'E', 'Y', 0}; //Old One Replace by:
static SQLWCHAR W_SSL_KEY[]= {'S', 'S', 'L', '-', 'K', 'E', 'Y', 0};
static SQLWCHAR W_SSLCERT[]= {'S', 'S', 'L', 'C', 'E', 'R', 'T', 0}; //Old One Replace by:
static SQLWCHAR W_SSL_CERT[]= {'S', 'S', 'L', '-', 'C', 'E', 'R', 'T', 0};
static SQLWCHAR W_SSLCA[]= {'S', 'S', 'L', 'C', 'A', 0}; //Old One Replace by:
static SQLWCHAR W_SSL_CA[]= {'S', 'S', 'L', '-', 'C', 'A', 0};
static SQLWCHAR W_SSLCAPATH[]=
  {'S', 'S', 'L', 'C', 'A', 'P', 'A', 'T', 'H', 0}; //Old One Replace by:
static SQLWCHAR W_SSL_CAPATH[]=
  {'S', 'S', 'L', '-', 'C', 'A', 'P', 'A', 'T', 'H', 0};
static SQLWCHAR W_SSLCIPHER[]=
  {'S', 'S', 'L', 'C', 'I', 'P', 'H', 'E', 'R', 0}; //Old One Replace by:
static SQLWCHAR W_SSL_CIPHER[]=
  {'S', 'S', 'L', '-', 'C', 'I', 'P', 'H', 'E', 'R', 0};
static SQLWCHAR W_SSLVERIFY[]=
  {'S', 'S', 'L', 'V', 'E', 'R', 'I', 'F', 'Y', 0};
static SQLWCHAR W_RSAKEY[]= {'R', 'S', 'A', 'K', 'E', 'Y', 0};
static SQLWCHAR W_PORT[]= {'P', 'O', 'R', 'T', 0};
static SQLWCHAR W_SETUP[]= {'S', 'E', 'T', 'U', 'P', 0};
static SQLWCHAR W_READTIMEOUT[]=
  {'R','E','A','D','T','I','M','E','O','U','T',0};
static SQLWCHAR W_WRITETIMEOUT[]=
  {'W','R','I','T','E','T','I','M','E','O','U','T',0};
static SQLWCHAR W_FOUND_ROWS[]=
  {'F','O','U','N','D','_','R','O','W','S',0};
static SQLWCHAR W_BIG_PACKETS[]=
  {'B','I','G','_','P','A','C','K','E','T','S',0};
static SQLWCHAR W_NO_PROMPT[]=
  {'N','O','_','P','R','O','M','P','T',0};
static SQLWCHAR W_DYNAMIC_CURSOR[]=
  {'D','Y','N','A','M','I','C','_','C','U','R','S','O','R',0};
static SQLWCHAR W_NO_DEFAULT_CURSOR[]=
  {'N','O','_','D','E','F','A','U','L','T','_','C','U','R','S','O','R',0};
static SQLWCHAR W_NO_LOCALE[]=
  {'N','O','_','L','O','C','A','L','E',0};
static SQLWCHAR W_PAD_SPACE[]=
  {'P','A','D','_','S','P','A','C','E',0};
static SQLWCHAR W_FULL_COLUMN_NAMES[]=
  {'F','U','L','L','_','C','O','L','U','M','N','_','N','A','M','E','S',0};
static SQLWCHAR W_COMPRESSED_PROTO[]=
  {'C','O','M','P','R','E','S','S','E','D','_','P','R','O','T','O',0};
static SQLWCHAR W_IGNORE_SPACE[]=
  {'I','G','N','O','R','E','_','S','P','A','C','E',0};
static SQLWCHAR W_NAMED_PIPE[]=
  {'N','A','M','E','D','_','P','I','P','E',0};
static SQLWCHAR W_NO_BIGINT[]=
  {'N','O','_','B','I','G','I','N','T',0};
static SQLWCHAR W_NO_CATALOG[]=
  {'N','O','_','C','A','T','A','L','O','G',0};
static SQLWCHAR W_NO_SCHEMA[]=
  {'N','O','_','S','C','H','E','M','A',0};
static SQLWCHAR W_USE_MYCNF[]=
  {'U','S','E','_','M','Y','C','N','F',0};
static SQLWCHAR W_SAFE[]=
  {'S','A','F','E',0};
static SQLWCHAR W_NO_TRANSACTIONS[]=
  {'N','O','_','T','R','A','N','S','A','C','T','I','O','N','S',0};
static SQLWCHAR W_LOG_QUERY[]=
  {'L','O','G','_','Q','U','E','R','Y',0};
static SQLWCHAR W_NO_CACHE[]=
  {'N','O','_','C','A','C','H','E',0};
static SQLWCHAR W_FORWARD_CURSOR[]=
  {'F','O','R','W','A','R','D','_','C','U','R','S','O','R',0};
static SQLWCHAR W_AUTO_RECONNECT[]=
  {'A','U','T','O','_','R','E','C','O','N','N','E','C','T',0};
static SQLWCHAR W_ENABLE_DNS_SRV[]=
  {'E','N','A','B','L','E','_','D','N','S','_','S','R','V',0};
static SQLWCHAR W_MULTI_HOST[]=
  {'M','U','L','T','I','_','H','O','S','T',0};
static SQLWCHAR W_AUTO_IS_NULL[]=
  {'A','U','T','O','_','I','S','_','N','U','L','L',0};
static SQLWCHAR W_ZERO_DATE_TO_MIN[]=
  {'Z','E','R','O','_','D','A','T','E','_','T','O','_','M','I','N',0};
static SQLWCHAR W_MIN_DATE_TO_ZERO[]=
  {'M','I','N','_','D','A','T','E','_','T','O','_','Z','E','R','O',0};
static SQLWCHAR W_MULTI_STATEMENTS[]=
  {'M','U','L','T','I','_','S','T','A','T','E','M','E','N','T','S',0};
static SQLWCHAR W_COLUMN_SIZE_S32[]=
  {'C','O','L','U','M','N','_','S','I','Z','E','_','S','3','2',0};
static SQLWCHAR W_NO_BINARY_RESULT[]=
  {'N','O','_','B','I','N','A','R','Y','_','R','E','S','U','L','T',0};
static SQLWCHAR W_DFLT_BIGINT_BIND_STR[]=
  {'D','F','L','T','_','B','I','G','I','N','T','_','B','I','N','D','_','S','T','R',0};
static SQLWCHAR W_CLIENT_INTERACTIVE[]=
  {'I','N','T','E','R','A','C','T','I','V','E',0};
static SQLWCHAR W_PREFETCH[]= {'P','R','E','F','E','T','C','H',0};
static SQLWCHAR W_NO_SSPS[]= {'N','O','_','S','S','P','S',0};
static SQLWCHAR W_CAN_HANDLE_EXP_PWD[]=
  {'C','A','N','_','H','A','N','D','L','E','_','E','X','P','_','P','W','D',0};
static SQLWCHAR W_ENABLE_CLEARTEXT_PLUGIN[]=
  {'E','N','A','B','L','E','_','C','L','E','A', 'R', 'T', 'E', 'X', 'T', '_','P','L','U','G','I','N',0};
static SQLWCHAR W_GET_SERVER_PUBLIC_KEY[] =
{ 'G','E','T','_','S','E','R','V', 'E', 'R','_','P','U','B','L', 'I', 'C','_','K','E','Y',0 };
static SQLWCHAR W_SAVEFILE[]=  {'S','A','V','E','F','I','L','E',0};
static SQLWCHAR W_PLUGIN_DIR[]=
  {'P','L','U','G','I','N','_','D','I','R',0};
static SQLWCHAR W_DEFAULT_AUTH[]=
  {'D','E','F','A','U','L','T','_','A','U','T', 'H',0};
static SQLWCHAR W_NO_TLS_1_2[] =
{ 'N', 'O', '_', 'T', 'L', 'S', '_', '1', '_', '2', 0 };
static SQLWCHAR W_NO_TLS_1_3[] =
{ 'N', 'O', '_', 'T', 'L', 'S', '_', '1', '_', '3', 0 };
static SQLWCHAR W_SSLMODE[] =
{ 'S', 'S', 'L', 'M', 'O', 'D', 'E', 0 }; //Old One Replace by:
static SQLWCHAR W_SSL_MODE[] =
{ 'S', 'S', 'L', '-' ,'M', 'O', 'D', 'E', 0 };
static SQLWCHAR W_NO_DATE_OVERFLOW[] =
{ 'N', 'O', '_', 'D', 'A', 'T', 'E', '_', 'O', 'V', 'E', 'R', 'F', 'L', 'O', 'W', 0 };
static SQLWCHAR W_ENABLE_LOCAL_INFILE[] =
{ 'E', 'N', 'A', 'B', 'L', 'E', '_', 'L', 'O', 'C', 'A', 'L', '_', 'I', 'N', 'F', 'I', 'L', 'E', 0 };
static SQLWCHAR W_LOAD_DATA_LOCAL_DIR[] =
{ 'L', 'O', 'A', 'D', '_', 'D', 'A', 'T', 'A', '_', 'L', 'O', 'C', 'A', 'L', '_', 'D', 'I', 'R', 0 };
static SQLWCHAR W_OCI_CONFIG_FILE[] =
{ 'O', 'C', 'I', '_', 'C', 'O', 'N', 'F', 'I', 'G', '_', 'F', 'I', 'L', 'E', 0 };
static SQLWCHAR W_OCI_CONFIG_PROFILE[] =
{ 'O', 'C', 'I', '_', 'C', 'O', 'N', 'F', 'I', 'G', '_', 'P', 'R', 'O', 'F', 'I', 'L', 'E', 0 };
static SQLWCHAR W_AUTHENTICATION_KERBEROS_MODE[] =
{ 'A','U','T','H','E','N','T','I','C','A','T','I','O','N','-',
  'K','E','R','B','E','R','O','S','-','M','O','D','E', 0};
static SQLWCHAR W_TLS_VERSIONS[] =
{ 'T', 'L', 'S', '-', 'V', 'E', 'R', 'S', 'I', 'O', 'N', 'S', 0 };
static SQLWCHAR W_SSL_CRL[] =
{ 'S', 'S', 'L', '-', 'C', 'R', 'L', 0 };
static SQLWCHAR W_SSL_CRLPATH[] =
{ 'S', 'S', 'L', '-', 'C', 'R', 'L', 'P', 'A', 'T', 'H', 0};

/* DS_PARAM */
/* externally used strings */
const SQLWCHAR W_DRIVER_PARAM[]= {';', 'D', 'R', 'I', 'V', 'E', 'R', '=', 0};
const SQLWCHAR W_DRIVER_NAME[]= {'M', 'y', 'S', 'Q', 'L', ' ',
                                 'O', 'D', 'B', 'C', ' ', '5', '.', '3', ' ',
                                 'D', 'r', 'i', 'v', 'e', 'r', 0};
const SQLWCHAR W_INVALID_ATTR_STR[]= {'I', 'n', 'v', 'a', 'l', 'i', 'd', ' ',
                                      'a', 't', 't', 'r', 'i', 'b', 'u', 't', 'e', ' ',
                                      's', 't', 'r', 'i', 'n', 'g', 0};

/* List of all DSN params, used when serializing to string */
static const
SQLWCHAR *dsnparams[]= {W_DSN, W_DRIVER, W_DESCRIPTION, W_SERVER,
                        W_UID, W_PWD,
#if MFA_ENABLED
                        W_PWD1, W_PWD2, W_PWD3,
#endif
                        W_DATABASE, W_SOCKET, W_INITSTMT,
                        W_PORT, W_OPTION, W_CHARSET, W_SSLKEY, W_SSL_KEY,
                        W_SSLCERT, W_SSLCERT, W_SSLCA, W_SSL_CA,
                        W_SSLCAPATH,W_SSL_CAPATH, W_SSLCIPHER, W_SSL_CIPHER,
                        W_SSLVERIFY, W_READTIMEOUT, W_WRITETIMEOUT,
                        W_FOUND_ROWS, W_BIG_PACKETS, W_NO_PROMPT,
                        W_DYNAMIC_CURSOR, W_NO_DEFAULT_CURSOR,
                        W_NO_LOCALE, W_PAD_SPACE, W_FULL_COLUMN_NAMES,
                        W_COMPRESSED_PROTO, W_IGNORE_SPACE, W_NAMED_PIPE,
                        W_NO_BIGINT, W_NO_CATALOG, W_USE_MYCNF, W_SAFE,
                        W_NO_TRANSACTIONS, W_LOG_QUERY, W_NO_CACHE,
                        W_FORWARD_CURSOR, W_AUTO_RECONNECT, W_AUTO_IS_NULL,
                        W_ZERO_DATE_TO_MIN, W_MIN_DATE_TO_ZERO,
                        W_MULTI_STATEMENTS, W_COLUMN_SIZE_S32,
                        W_NO_BINARY_RESULT, W_DFLT_BIGINT_BIND_STR,
                        W_CLIENT_INTERACTIVE, W_PREFETCH, W_NO_SSPS,
                        W_CAN_HANDLE_EXP_PWD, W_ENABLE_CLEARTEXT_PLUGIN,
                        W_GET_SERVER_PUBLIC_KEY, W_ENABLE_DNS_SRV, W_MULTI_HOST,
                        W_SAVEFILE, W_RSAKEY, W_PLUGIN_DIR, W_DEFAULT_AUTH,
                        W_NO_TLS_1_2, W_NO_TLS_1_3,
                        W_SSLMODE, W_NO_DATE_OVERFLOW, W_LOAD_DATA_LOCAL_DIR,
                        W_OCI_CONFIG_FILE, W_OCI_CONFIG_PROFILE, W_AUTHENTICATION_KERBEROS_MODE,
                        W_TLS_VERSIONS, W_SSL_CRL, W_SSL_CRLPATH};
static const
int dsnparamcnt= sizeof(dsnparams) / sizeof(SQLWCHAR *);
/* DS_PARAM */

const unsigned int default_cursor_prefetch= 100;

/* convenience macro to append a single character */
#define APPEND_SQLWCHAR(buf, ctr, c) {\
  if (ctr) { \
    *((buf)++)= (c); \
    if (--(ctr)) \
        *(buf)= 0; \
  } \
}


/*
 * Check whether a parameter value needs escaping.
 */
static int value_needs_escaped(const SQLWSTRING s)
{
  const SQLWCHAR *str = s.c_str();
  SQLWCHAR c;
  while (str && (c= *str++))
  {
    if (c >= '0' && c <= '9')
      continue;
    else if (c >= 'a' && c <= 'z')
      continue;
    else if (c >= 'A' && c <= 'Z')
      continue;
    /* other non-alphanumeric characters that don't need escaping */
    switch (c)
    {
    case '_':
    case ' ':
    case '.':
      continue;
    }
    return 1;
  }
  return 0;
}


/*
 * Convenience function to get the current config mode.
 */
UWORD config_get()
{
  UWORD mode;
  SQLGetConfigMode(&mode);
  return mode;
}


/*
 * Convenience function to set the current config mode. Returns the
 * mode in use when the function was called.
 */
UWORD config_set(UWORD mode)
{
  UWORD current= config_get();
  SQLSetConfigMode(mode);
  return current;
}

void optionStr::set(const SQLWSTRING& val, bool is_default = false) {
  SQLCHAR out[1024];
  m_wstr = val;
  SQLINTEGER len = (SQLINTEGER)val.length();
  char *converted = (char *)sqlwchar_as_utf8_ext(val.c_str(), &len, out, sizeof(out), nullptr);
  m_str = std::string(converted, len);
  m_is_set = true;
  m_is_null = false;
  m_is_default = is_default;
}

void optionStr::set(const std::string &val, bool is_default = false) {
  m_str = val;
  SQLINTEGER len = (SQLINTEGER)val.length();
  SQLWCHAR *converted = sqlchar_as_sqlwchar(default_charset_info, (SQLCHAR*)val.c_str(), &len, nullptr);
  m_wstr = SQLWSTRING(converted, len);
  x_free(converted);
  m_is_set = true;
  m_is_null = false;
  m_is_default = is_default;
}

const optionBase& optionStr::operator=(const SQLWSTRING &val) {
  set(const_cast<SQLWSTRING&>(val));
  return *this;
}

const optionBase &optionStr::operator=(const SQLWCHAR *val) {
  // Setting nullptr will clear the option
  if (val)
    set(SQLWSTRING(val));
  else
    set_null();
  return *this;
}

const optionStr &optionStr::operator=(const std::string &val) {
  set(const_cast<std::string&>(val));
  return *this;
}


/* ODBC Installer Driver Wrapper */

/*
 * Create a new driver object. All string data is pre-allocated.
 */
Driver::Driver() { }


/*
 * Delete an existing driver object.
 */
Driver::~Driver()
{ }


#ifdef _WIN32
/*
 * Utility function to duplicate path and remove "(x86)" chars.
 */
SQLWCHAR *remove_x86(const SQLWCHAR *path, const SQLWCHAR *loc)
{
  /* Program Files (x86)
   *           13^   19^
   */
  size_t chars = sqlwcharlen(loc) - 18 /* need +1 for sqlwcharncat2() */;
  SQLWCHAR *news= sqlwchardup(path, SQL_NTS);
  news[(loc-path)+13] = 0;
  sqlwcharncat2(news, loc + 19, &chars);
  return news;
}


/*
 * Compare two library paths taking into account different
 * locations of "Program Files" for 32 and 64 bit applications
 * on Windows. This is done by removing the "(x86)" from "Program
 * Files" if present in either path.
 *
 * Note: wcs* functions are used as this only needs to support
 *       Windows where SQLWCHAR is wchar_t.
 */
int Win64CompareLibs(const SQLWCHAR *lib1, const SQLWCHAR *lib2)
{
  int free1= 0, free2= 0;
  int rc;
  SQLWCHAR *llib1, *llib2;

  /* perform necessary transformations */
  if (llib1= (SQLWCHAR*)wcsstr(lib1, L"Program Files (x86)"))
  {
    llib1= remove_x86(lib1, llib1);
    free1= 1;
  }
  else
    llib1 = (SQLWCHAR*)lib1;

  if (llib2 = (SQLWCHAR*)wcsstr(lib2, L"Program Files (x86)"))
  {
    llib2= remove_x86(lib2, llib2);
    free2= 1;
  }
  else
    llib2 = (SQLWCHAR*)lib2;

  /* perform the comparison */
  rc= sqlwcharcasecmp(llib1, llib2);

  if (free1)
    x_free(llib1);
  if (free2)
    x_free(llib2);
  return rc;
}
#endif /* _WIN32 */


int Driver::lookup_name()
{
  SQLWCHAR drivers[16384];
  SQLWCHAR *pdrv= drivers;
  SQLWCHAR driverinfo[1024];
  int len;
  SAVE_MODE()

  /* get list of drivers */
#ifdef _WIN32
  WORD slen; /* WORD needed for windows */
  if (!SQLGetInstalledDriversW(pdrv, 16383, &slen) || !(len = slen))
#else
  if (!(len = SQLGetPrivateProfileStringW(NULL, NULL, W_EMPTY, pdrv, 16383,
                                          W_ODBCINST_INI)))
#endif
    return -1;

  RESTORE_MODE()

  /* check the lib of each driver for one that matches the given lib name */
  while (len > 0)
  {
    if (SQLGetPrivateProfileStringW(pdrv, W_DRIVER, W_EMPTY, driverinfo,
                                    1023, W_ODBCINST_INI))
    {
      RESTORE_MODE()

#ifdef _WIN32
      if (!Win64CompareLibs(driverinfo, (const SQLWCHAR*)lib))
#else
      /* Trying to match the driver lib or section name in odbcinst.ini */
      if (!sqlwcharcasecmp(driverinfo, lib) ||
          !sqlwcharcasecmp(pdrv, lib))
#endif
      {
        name = pdrv;
        return 0;
      }
    }

    RESTORE_MODE()

    len -= (int)sqlwcharlen(pdrv) + 1;
    pdrv += sqlwcharlen(pdrv) + 1;
  }

  return -1;
}


int Driver::lookup()
{
  SQLWCHAR buf[4096];
  SQLWCHAR *entries= buf;
  SQLWCHAR dest[ODBCDRIVER_STRLEN];
  SAVE_MODE()

  /* if only the filename is given, we must get the driver's name */
  if (!name.is_set() && lib.is_set())
  {
    if (lookup_name())
      return -1;
  }

  /* get entries and make sure the driver exists */
  if (SQLGetPrivateProfileStringW(name,
                                  NULL, W_EMPTY, buf, 4096,
                                  W_ODBCINST_INI) < 1)
  {
    SQLPostInstallerErrorW(ODBC_ERROR_INVALID_NAME, W_CANNOT_FIND_DRIVER);
    return -1;
  }

  RESTORE_MODE()

  /* read the needed driver attributes */
  while (*entries)
  {
    /* get the value if it's one we're looking for */
    if (SQLGetPrivateProfileStringW(name,
                                    entries, W_EMPTY,
                                    dest, ODBCDRIVER_STRLEN,
                                    W_ODBCINST_INI) < 0)
    {
      RESTORE_MODE()
      return 1;
    }
    if (!sqlwcharcasecmp(W_DRIVER, entries))
      lib = dest;
    else if (!sqlwcharcasecmp(W_SETUP, entries))
      setup_lib = dest;
    else { /* unknown/unused entry */
    }


    RESTORE_MODE()

    entries += sqlwcharlen(entries) + 1;
  }

  return 0;
}


int Driver::from_kvpair_semicolon(const SQLWCHAR *attrs)
{
  const SQLWCHAR *split;
  const SQLWCHAR *end;
  SQLWCHAR attribute[100];

  while (*attrs)
  {
    optionStr *dest = nullptr;
    /* invalid key-value pair if no equals */
    if ((split= sqlwcharchr(attrs, '=')) == NULL)
      return 1;

    /* get end of key-value pair */
    if ((end= sqlwcharchr(attrs, ';')) == NULL)
      end= attrs + sqlwcharlen(attrs);

    /* check the attribute length (must not be longer than the buffer size) */
    if (split - attrs >= 100)
    {
      return 1;
    }

    /* pull out the attribute name */
    memcpy(attribute, attrs, (split - attrs) * sizeof(SQLWCHAR));
    attribute[split - attrs]= 0; /* add null term */
    ++split;

    /* if its one we want, copy it over */
    if (!sqlwcharcasecmp(W_DRIVER, attribute))
      dest = &lib;
    else if (!sqlwcharcasecmp(W_SETUP, attribute))
      dest = &setup_lib;
    else
      { /* unknown/unused attribute */ }

    if (dest)
    {
      if (end - split >= ODBCDRIVER_STRLEN)
      {
        /*
          The value is longer than the allocated buffer length
          for driver->lib or driver->setup_lib
        */
        return 1;
      }
       *dest = SQLWSTRING(split, end);
    }

    /* advanced to next attribute */
    attrs= end;
    if (*end)
      ++attrs;
  }

  return 0;
}


int Driver::to_kvpair_null(SQLWCHAR *attrs, size_t attrslen)
{
  *attrs= 0;
  attrs+= sqlwcharncat2(attrs, name, &attrslen);

  /* append NULL-separator */
  APPEND_SQLWCHAR(attrs, attrslen, 0);

#if USE_IODBC
  // iODBC wants "Driver", not "DRIVER"
  attrs+= sqlwcharncat2(attrs, W_Driver, &attrslen);
#else
  attrs += sqlwcharncat2(attrs, W_DRIVER, &attrslen);
#endif
  APPEND_SQLWCHAR(attrs, attrslen, '=');
  attrs+= sqlwcharncat2(attrs, lib, &attrslen);

  /* append NULL-separator */
  APPEND_SQLWCHAR(attrs, attrslen, 0);

  if (setup_lib.is_set())
  {
    attrs+= sqlwcharncat2(attrs, W_SETUP, &attrslen);
    APPEND_SQLWCHAR(attrs, attrslen, '=');
    attrs+= sqlwcharncat2(attrs, setup_lib, &attrslen);

    /* append NULL-separator */
    APPEND_SQLWCHAR(attrs, attrslen, 0);
  }
  if (attrslen--)
    *attrs= 0;
  return !(attrslen > 0);
}


/* ODBC Installer Data Source Wrapper */

/*
 * Same as ds_set_strattr, but allows truncating the given string. If
 * charcount is 0 or SQL_NTS, it will act the same as ds_set_strattr.
 */
void optionStr::set_remove_brackets(const SQLWCHAR *val_char,
                                    SQLINTEGER len) {
  SQLWCHAR out[1024] = { 0 };

  if (!val_char) {
    set_null();
    return;
  }

  SQLWSTRING val_str =
    (len != SQL_NTS ? SQLWSTRING(val_char, len) : SQLWSTRING(val_char));

  size_t charcount = val_str.length();
  if (charcount)
  {
    const SQLWCHAR *val = val_str.c_str();
    SQLWCHAR *pos = out;
    while (charcount)
    {
      *pos = *val;
      if (charcount > 1 && ((*val == '}' && *(val + 1) == '}')))
      {
        ++val;
        --charcount;
      }

      ++pos;
      ++val;
      --charcount;
    }
    *pos = 0; // Terminate the string
  }

  m_wstr = out;
  // Re-use existing buffer, just as another type
  SQLCHAR *c_out = reinterpret_cast<SQLCHAR *>(out);
  len = (SQLINTEGER)val_str.length();
  char *result = (char *)sqlwchar_as_utf8_ext(m_wstr.c_str(), &len,
    c_out, sizeof(out), nullptr);
  m_str = std::string(result, len);
  m_is_set = true;
  m_is_default = false;
  m_is_null = false;
}


/*
 * Lookup a data source in the system. The name will be read from
 * the object and the rest of the details will be populated.
 *
 * If greater-than zero is returned, additional information
 * can be obtained from SQLInstallerError(). A less-than zero return code
 * indicates that the driver could not be found.
 */
int DataSource::lookup()
{
#define DS_BUF_LEN 8192
  SQLWCHAR buf[DS_BUF_LEN];
  SQLWCHAR *entries= buf;
  SQLWCHAR val[256];
  int size;
  int rc= 0;
  UWORD config_mode= config_get();
  /* No need for SAVE_MODE() because we always call config_get() above. */

  memset(buf, 0xff, sizeof(buf));

  /* get entries and check if data source exists */
  if ((size= SQLGetPrivateProfileStringW(opt_DSN, NULL, W_EMPTY,
                                         buf, DS_BUF_LEN, W_ODBC_INI)) < 1)
  {
    rc= -1;
    goto end;
  }

  RESTORE_MODE()

#ifdef _WIN32
  /*
   * In Windows XP, there is a bug in SQLGetPrivateProfileString
   * when mode is ODBC_BOTH_DSN and we are looking for a system
   * DSN. In this case SQLGetPrivateProfileString will find the
   * system dsn but return a corrupt list of attributes.
   *
   * See debug code above to print the exact data returned.
   * See also: http://support.microsoft.com/kb/909122/
   */
  if (config_mode == ODBC_BOTH_DSN &&
      /* two null chars or a null and some bogus character */
      *(entries + sqlwcharlen(entries)) == 0 &&
      (*(entries + sqlwcharlen(entries) + 1) > 0x7f ||
       *(entries + sqlwcharlen(entries) + 1) == 0))
  {
    /* revert to system mode and try again */
    config_set(ODBC_SYSTEM_DSN);
    if ((size= SQLGetPrivateProfileStringW(opt_DSN, NULL, W_EMPTY,
                                           buf, DS_BUF_LEN, W_ODBC_INI)) < 0)
    {
      rc= -1;
      goto end;
    }
  }
#endif

  for (size_t used = 0;
       used < DS_BUF_LEN && entries[0];
       used += sqlwcharlen(entries) + 1,
       entries += sqlwcharlen(entries) + 1)
  {
    int valsize = SQLGetPrivateProfileStringW(opt_DSN, entries, W_EMPTY,
                                              val, ODBCDATASOURCE_STRLEN,
                                              W_ODBC_INI);
    if (valsize < 0)
    {
      rc = 1;
      goto end;
    } else if (!valsize) {
      /* skip blanks */;
    } else if (!sqlwcharcasecmp(W_OPTION, entries)) {
      set_numeric_options(get_numeric_options() | sqlwchartoul(val));
    } else {
      set_val(entries, val);
    }

    RESTORE_MODE()
  }

end:
  config_set(config_mode);
  return rc;
}


void DataSource::set_val(SQLWCHAR* name, SQLWCHAR* val) {
  if (auto *opt = get_opt(name)) {
    *opt = val;
  }
}

optionBase* DataSource::get_opt(SQLWCHAR* name) {
  SQLWSTRING wname = name;
  std::transform(wname.begin(), wname.end(), wname.begin(), ::toupper);
  auto el = m_opt_map.find(wname);
  if (el != m_opt_map.end()) {
    return &el->second;
  }
  return nullptr;
}
    /*
 * Read an attribute list (key/value pairs) into a data source
 * object. Delimiter should probably be 0 or ';'.
 */
int DataSource::from_kvpair(const SQLWCHAR *str, SQLWCHAR delim)
{
  const SQLWCHAR *split;
  const SQLWCHAR *end;
  SQLWCHAR attribute[1000];
  size_t len;

  while (*str)
  {
    if ((split= sqlwcharchr(str, (SQLWCHAR)'=')) == NULL)
      return 1;

    /* remove leading spaces on attribute */
    while (*str == ' ')
      ++str;
    len = split - str;

    // The attribute length must not be out of buf
    if (len >= sizeof(attribute)/sizeof(SQLWCHAR))
    {
      return 1;
    }

    memcpy(attribute, str, len * sizeof(SQLWCHAR));
    attribute[len]= 0;
    /* remove trailing spaces on attribute */
    --len;
    while (attribute[len] == ' ')
    {
      attribute[len] = 0;
      --len;
    }

    /* remove leading and trailing spaces on value */
    while (*(++split) == ' ');

    auto find_bracket_end = [](const SQLWCHAR *wstr) {
      const SQLWCHAR *e = wstr;
      do
      {
        e = sqlwcharchr(e, '}');
        if (e == nullptr || *(e + 1) == 0)
          break;

        // Found escape }}, skip to the next bracket
        if (*(e + 1) == '}')
          e += 2;
        else
          break;

      } while (*e);
      return e;
    };

    /* check for an "escaped" value */
    if ((*split == '{' && (end = find_bracket_end(str)) == NULL) ||
        /* or a delimited value */
        (*split != '{' && (end= sqlwcharchr(str, delim)) == NULL))
      /* otherwise, take the rest of the string */
      end= str + sqlwcharlen(str);

    /* remove trailing spaces on value (not escaped part) */
    len = end - split - 1;
    while (end > split && split[len] == ' ' && split[len+1] != '}')
    {
      --len;
      --end;
    }

    /* handle deprecated options as an exception */
    if (!sqlwcharcasecmp(W_OPTION, attribute))
    {
      set_numeric_options(sqlwchartoul(split));
    }
    else
    {
      if (optionBase *opt = get_opt(attribute)) {

        if (opt->get_type() == optionBase::opt_type::STRING) {
          optionStr *str_opt = dynamic_cast<optionStr*>(opt);
          if (*split == '{' && *end == '}') {
            str_opt->set_remove_brackets(split + 1, (SQLINTEGER)(end - split - 1));
            ++end;
          } else {
            str_opt->set_remove_brackets(split, (SQLINTEGER)(end - split));
          }
        } else {
          *opt = split;
        }
      }
    }

    str= end;
    /* If delim is NULL then double-NULL is the end of key-value pairs list */
    while ((delim && *str == delim) ||
           (!delim && !*str && *(str+1)) ||
           *str == ' ')
      ++str;
  }
  return 0;
}


DataSource::DataSource() {
  #define ADD_OPTION_TO_MAP(X) m_opt_map.emplace(W_##X, opt_##X);
  FULL_OPTIONS_LIST(ADD_OPTION_TO_MAP);

  #define ADD_ALIAS_TO_MAP(X, Y)       \
    m_opt_map.emplace(W_##Y, opt_##X); \
    m_alias_list.push_back(W_##Y);

  ALIAS_OPTIONS_LIST(ADD_ALIAS_TO_MAP);
  reset();
}

void DataSource::reset() {
#define SET_DEFAULT_STR_OPTION(X) opt_##X.set_default(nullptr);
  STR_OPTIONS_LIST(SET_DEFAULT_STR_OPTION);

#define SET_DEFAULT_INT_OPTION(X) opt_##X.set_default(0);
  INT_OPTIONS_LIST(SET_DEFAULT_INT_OPTION);

#define SET_DEFAULT_BOOL_OPTION(X) opt_##X.set_default(false);
  BOOL_OPTIONS_LIST(SET_DEFAULT_BOOL_OPTION);

  opt_PORT.set_default(3306);
  opt_NO_SCHEMA = 1;
}

SQLWSTRING DataSource::to_kvpair(SQLWCHAR delim) {
  SQLWSTRING attrs;

  bool name_is_set = !m_opt_map.find(W_DSN)->second.is_default();
  for (const auto &el : m_opt_map)
  {
    auto &k = el.first;
    auto &v = el.second;
    // Skip the option, which wasn't set.
    // Skip DRIVER if DSN (NAME) was set.
    if (!v.is_set() || v.is_default() ||
        (name_is_set && !sqlwcharcasecmp(W_DRIVER, k.c_str()))
    )
      continue;

    // Append OPTION=
    attrs.append(k);
    attrs.append({(SQLWCHAR)'='});

    bool do_escape = v.get_type() == optionBase::opt_type::STRING && value_needs_escaped(v);
    if (do_escape)
      attrs.append({(SQLWCHAR)'{'});

    attrs.append(escape_brackets(v, false));

    if (do_escape)
      attrs.append({(SQLWCHAR)'}'});

    attrs.append({delim});
  }
  return attrs;
}


bool DataSource::write_opt(const SQLWCHAR *name, const SQLWCHAR *val) {
  // don't write if its not set
  if (name && *name)
  {
    int rc;
    SAVE_MODE()
    rc = SQLWritePrivateProfileStringW(opt_DSN,
      name, val, W_ODBC_INI);
    if (rc)
      RESTORE_MODE()
    return !rc;
  }

  return false;
}


/*
 * Add the given datasource to system. Call SQLInstallerError() to get
 * further error details if non-zero is returned. ds->driver should be
 * the driver name.
 */
int DataSource::add() {
  Driver driver;

  int rc = 1;
  SAVE_MODE()

  /* Validate data source name */
  if (!SQLValidDSNW(opt_DSN)) return rc;

  RESTORE_MODE()

  /* remove if exists, FYI SQLRemoveDSNFromIni returns true
   * even if the dsn isnt found, false only if there is a failure */
  const SQLWCHAR *name = opt_DSN;
  if (!SQLRemoveDSNFromIniW(name)) {
      int msgno;
      DWORD errcode;
      char errmsg[256];
      for (msgno = 1; msgno < 9; ++msgno) {
        if (SQLInstallerError(msgno, &errcode, errmsg, 256, NULL) !=
            SQL_SUCCESS)
          return rc;
        fprintf(stderr, "[ERROR] SQLInstaller error %d: %s\n", errcode, errmsg);
      }
      return rc;
  }

  RESTORE_MODE()

  driver.name = this->opt_DRIVER;

  if (driver.lookup()) {
    SQLPostInstallerErrorW(ODBC_ERROR_INVALID_KEYWORD_VALUE,
                           W_CANNOT_FIND_DRIVER);
    return rc;
  }

  /* "Create" section for data source */
  if (!SQLWriteDSNToIniW(opt_DSN, driver.name)) {
    return rc;
  }

  RESTORE_MODE()

#ifdef _WIN32
  /* Windows driver manager allows writing lib into the DRIVER parameter */
  if (write_opt(W_DRIVER, driver.lib))
    return rc;
#else
  /*
   If we write driver->lib into the DRIVER parameter with iODBC/UnixODBC
   the next time GUI will not load because it loses the relation to
   odbcinst.ini
   */
  if (write_opt(W_DRIVER, driver.name))
    return rc;
#endif

#define MFA_COND(X) || k == W_##X
#define SKIP_COND(X) || k == W_##X


  for (const auto &el : m_opt_map) {
    auto &k = el.first;
    auto &v = el.second;
    // Skip non-set options, default values and aliases
    if (!v.is_set() SKIP_OPTIONS_LIST(SKIP_COND) ||
        v.is_default() ||
        std::find(m_alias_list.begin(), m_alias_list.end(), k) != m_alias_list.end())
      continue;

    SQLWSTRING val = v;
    if (k == W_PWD MFA_OPTS(MFA_COND)) {
      // Escape the password(s)
      val = escape_brackets(v, false);
    }

    if (write_opt(k.c_str(), val.c_str()))
      return rc;
  }

  rc = 0;
  return rc;
}


/*
 * Convenience method to check if data source exists. Set the dsn
 * scope before calling this to narrow the check.
 */
bool DataSource::exists()
{
  SQLWCHAR buf[100];
  SAVE_MODE()

  /* get entries and check if data source exists */
  if (SQLGetPrivateProfileStringW(opt_DSN, NULL, W_EMPTY, buf, 100, W_ODBC_INI))
    return 0;

  RESTORE_MODE()

  return 1;
}


void DataSource::set_numeric_options(unsigned long options) {

#define CHECK_BIT_OPT(X) opt_##X = ((options & FLAG_##X) > 0);
  BIT_OPTIONS_LIST(CHECK_BIT_OPT);
}


unsigned long DataSource::get_numeric_options()
{
  unsigned long options = 0;

#define SET_BIT_OPT(X) if(opt_##X) options |= FLAG_##X;
  BIT_OPTIONS_LIST(SET_BIT_OPT);

  return options;
}
