// Copyright (c) 2000, 2024, Oracle and/or its affiliates.
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

/**
 @file  odbcinstw.c
 @brief Installer API Unicode wrapper functions.

 unixODBC 2.2.11 does not include the Unicode versions of the ODBC
 installer API. As of November 2007, this was still the current version
 shipped with Debian and Ubuntu Linux.

 SQLGetPrivateProfileString() also has a few bugs in unixODBC 2.2.11 that
 our version of SQLGetPrivateProfileStringW() will work around.
*/

#include "stringutil.h"
#include "../MYODBC_CONF.h"
#include "../MYODBC_ODBC.h"

#include <wchar.h>

#define INSTAPI

#ifndef FALSE
# define FALSE 0
#endif

#if !defined(HAVE_SQLGETPRIVATEPROFILESTRINGW) || defined(USE_UNIXODBC)
int INSTAPI
MySQLGetPrivateProfileStringW(const MyODBC_LPCWSTR lpszSection, const MyODBC_LPCWSTR lpszEntry,
                              const MyODBC_LPCWSTR lpszDefault, LPWSTR lpszRetBuffer,
                              int cbRetBuffer, const MyODBC_LPCWSTR lpszFilename)
{
  SQLINTEGER len;
  int rc;
  char *section, *entry, *def, *ret, *filename;

  len= SQL_NTS;
  section= (char *)sqlwchar_as_utf8(lpszSection, &len);
  len= SQL_NTS;
  entry= (char *)sqlwchar_as_utf8(lpszEntry, &len);
  len= SQL_NTS;
  def= (char *)sqlwchar_as_utf8(lpszDefault, &len);
  len= SQL_NTS;
  filename= (char *)sqlwchar_as_utf8(lpszFilename, &len);

  if (lpszRetBuffer && cbRetBuffer)
  {
    ret= (char*)malloc(cbRetBuffer + 1);
    /*
      We cannot rely on getting correct results from the next call of
      SQLGetPrivateProfileString. So, taking precautions against unititialized
      values in the allocated buffer.
    */
    memset(ret, 0, cbRetBuffer + 1);
  }
  else
    ret= NULL;

  /* unixODBC 2.2.11 can't handle NULL for default, so pass "" instead. */
  rc= SQLGetPrivateProfileString(section, entry, def ? def : "", ret,
                                 cbRetBuffer, filename);

  if (rc > 0 && lpszRetBuffer)
  {
    /*
     unixODBC 2.2.11 returns the wrong value from SQLGetPrivateProfileString
     when getting the list of sections or entries in a section, so we have to
     re-calculate the correct length by walking the list of values.
    */
    if (!entry || !section)
    {
      char *pos= ret;
      while (*pos && pos < ret + cbRetBuffer)
        pos+= strlen(pos) + 1;
      rc= pos - ret;
    }

    /** @todo error handling */
    utf8_as_sqlwchar(lpszRetBuffer, cbRetBuffer, (SQLCHAR *)ret, rc);
  }

  x_free(section);
  x_free(entry);
  x_free(def);
  free(ret);
  x_free(filename);

  return rc;
}
#endif


#ifndef HAVE_SQLGETPRIVATEPROFILESTRINGW
int INSTAPI
SQLGetPrivateProfileStringW(const MyODBC_LPCWSTR lpszSection, const MyODBC_LPCWSTR lpszEntry,
                            const MyODBC_LPCWSTR lpszDefault, LPWSTR lpszRetBuffer,
                            int cbRetBuffer, const MyODBC_LPCWSTR lpszFilename)
{
  return MySQLGetPrivateProfileStringW(lpszSection, lpszEntry, lpszDefault,
                                       lpszRetBuffer, cbRetBuffer,
                                       lpszFilename);
}


BOOL INSTAPI
SQLValidDSNW(const MyODBC_LPCWSTR lpszDSN)
{
  BOOL ret;
  SQLINTEGER len= SQL_NTS;
  char *dsn= (char *)sqlwchar_as_utf8(lpszDSN, &len);

  ret= SQLValidDSN(dsn);

  x_free(dsn);

  return ret;
}


BOOL INSTAPI
SQLRemoveDSNFromIniW(const MyODBC_LPCWSTR lpszDSN)
{
  BOOL ret;
  SQLINTEGER len= SQL_NTS;
  char *dsn= (char *)sqlwchar_as_utf8(lpszDSN, &len);

  ret= SQLRemoveDSNFromIni(dsn);

  x_free(dsn);

  return ret;
}


BOOL INSTAPI
SQLWriteDSNToIniW(const MyODBC_LPCWSTR lpszDSN, const MyODBC_LPCWSTR lpszDriver)
{
  BOOL ret;
  SQLINTEGER len;
  char *dsn= NULL, *driver= NULL;

  len= SQL_NTS;
  dsn= (char *)sqlwchar_as_utf8(lpszDSN, &len),

  len= SQL_NTS;
  driver= (char *)sqlwchar_as_utf8(lpszDriver, &len);

  ret= SQLWriteDSNToIni(dsn, driver);

  x_free(dsn);
  x_free(driver);

  return ret;
}


RETCODE INSTAPI
SQLPostInstallerErrorW(DWORD fErrorCode, MyODBC_LPCWSTR szErrorMsg)
{
  RETCODE ret;
  SQLINTEGER len= SQL_NTS;
  char *msg= (char *)sqlwchar_as_utf8(szErrorMsg, &len);

  ret= SQLPostInstallerError(fErrorCode, msg);

  /*
    We have to leak memory here, because iODBC does not make a
    copy of the message.
  */

  return ret;
}


BOOL INSTAPI
SQLWritePrivateProfileStringW(const MyODBC_LPCWSTR lpszSection, const MyODBC_LPCWSTR lpszEntry,
                              const MyODBC_LPCWSTR lpszString,
                              const MyODBC_LPCWSTR lpszFilename)
{
  BOOL ret;
  SQLINTEGER len;
  char *section= NULL, *entry= NULL, *string= NULL, *filename= NULL;

  len= SQL_NTS;
  section= (char *)sqlwchar_as_utf8(lpszSection, &len),
  len= SQL_NTS;
  entry= (char *)sqlwchar_as_utf8(lpszEntry, &len),
  len= SQL_NTS;
  string= (char *)sqlwchar_as_utf8(lpszString, &len),
  len= SQL_NTS;
  filename= (char *)sqlwchar_as_utf8(lpszFilename, &len),

  ret= SQLWritePrivateProfileString(section, entry, string, filename);

  x_free(section);
  x_free(entry);
  x_free(string);
  x_free(filename);

  return ret;
}
#endif
