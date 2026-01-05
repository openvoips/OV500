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

#ifndef _WIN32
#include "MYODBC_MYSQL.h"
#include "installer.h"
#include "unicode_transcode.h"
#include <sql.h>
#endif

#include "setupgui.h"
#include "stringutil.h"

extern "C" {
BOOL Driver_Prompt(HWND hWnd, SQLWCHAR *instr, SQLUSMALLINT completion,
                   SQLWCHAR *outstr, SQLSMALLINT outmax, SQLSMALLINT *outlen);
}


/*
   Entry point for GUI prompting from SQLDriverConnect().
*/
BOOL Driver_Prompt(HWND hWnd, SQLWCHAR *instr, SQLUSMALLINT completion,
                   SQLWCHAR *outstr, SQLSMALLINT outmax, SQLSMALLINT *outlen)
{
  DataSource ds;
  BOOL rc= FALSE;
  SQLWSTRING out;
  size_t copy_len = 0;

  /*
     parse the attr string, dsn lookup will have already been
     done in the driver
  */
  if (instr && *instr)
  {
    if (ds.from_kvpair(instr, (SQLWCHAR)';'))
      goto exit;
  }

  /* Show the dialog and handle result */
  if (ShowOdbcParamsDialog(&ds, hWnd, TRUE) == 1)
  {
    /* serialize to outstr */
    out = ds.to_kvpair((SQLWCHAR)';');
    size_t len = out.length();
    if (outlen)
      *outlen = (SQLSMALLINT)len;

    if (outstr == nullptr || outmax == 0)
    {
      copy_len = 0;
    }
    else if (len > outmax)
    {
      /* truncated, up to caller to see outmax < *outlen */
      copy_len = outmax;
    }
    else
    {
      copy_len = len;
    }

    if (copy_len)
    {
      memcpy(outstr, out.c_str(), copy_len * sizeof(SQLWCHAR));
      outstr[copy_len] = 0;
    }

    rc = TRUE;
  }

exit:
  return rc;
}


/*
   Add, edit, or remove a Data Source Name (DSN). This function is
   called by "Data Source Administrator" on Windows, or similar
   application on Unix.
*/
BOOL INSTAPI ConfigDSNW(HWND hWnd, WORD nRequest, LPCWSTR pszDriver,
                        LPCWSTR pszAttributes)
{
  DataSource ds;
  BOOL rc= TRUE;
  SQLWSTRING origdsn;

  if (!utf8_charset_info) {
    my_sys_init();
    utf8_charset_info =
      get_charset_by_csname(transport_charset, MYF(MY_CS_PRIMARY), MYF(0));
  }

  if (pszAttributes && *pszAttributes)
  {
    SQLWCHAR delim= ';';

#ifdef _WIN32
    /*
      if there's no ;, then it's most likely null-delimited

     NOTE: the double null-terminated strings are not working
     *      with UnixODBC-GUI-Qt (posted a bug )
    */
    if (!sqlwcharchr(pszAttributes, delim))
      delim= 0;
#endif

    if (ds.from_kvpair(pszAttributes, delim))
    {
      SQLPostInstallerError(ODBC_ERROR_INVALID_KEYWORD_VALUE,
                            W_INVALID_ATTR_STR);
      return FALSE;
    }
    if (ds.lookup() && nRequest != ODBC_ADD_DSN)
    {
      /* ds_lookup() will already set SQLInstallerError */
      return FALSE;
    }
    origdsn = (const SQLWCHAR*)ds.opt_DSN;
  }

  switch (nRequest)
  {
  case ODBC_ADD_DSN:
    {
      Driver driver;
      driver.name = pszDriver;
      if (driver.lookup())
      {
        rc= FALSE;
        break;
      }
      if (hWnd)
      {
        /*
          hWnd means we will at least try to prompt, at which point
          the driver lib will be replaced by the name
        */
        ds.opt_DRIVER = driver.lib;
      }
      else
      {
        /*
          no hWnd is a likely a call from an app w/no prompting so
          we put the driver name immediately
        */
        ds.opt_DRIVER = driver.name;
      }
    }
  case ODBC_CONFIG_DSN:

#ifdef _WIN32
    /*
      for windows, if hWnd is NULL, we try to add the dsn
      with what information was given
    */
    if (!hWnd || ShowOdbcParamsDialog(&ds, hWnd, FALSE) == 1)
#else
    if (ShowOdbcParamsDialog(&ds, hWnd, FALSE) == 1)
#endif
    {
      /* save datasource */
      if (ds.add())
        rc= FALSE;
      /* if the name is changed, remove the old dsn */
      if (!origdsn.empty() && origdsn != (const SQLWSTRING&)ds.opt_DSN)
        SQLRemoveDSNFromIni(origdsn.c_str());
    }
    break;
  case ODBC_REMOVE_DSN:
    if (SQLRemoveDSNFromIni(ds.opt_DSN) != TRUE)
      rc= FALSE;
    break;
  }

  return rc;
}

#ifdef USE_IODBC
BOOL INSTAPI ConfigDSN(HWND hWnd, WORD nRequest, LPCSTR pszDriverA,
                       LPCSTR pszAttributesA)
{
  BOOL rc;

  size_t lenDriver = strlen(pszDriverA);
  size_t lenAttrib = strlen(pszAttributesA);

  /* We will assume using one-byte Latin string as a subset of UTF-8 */
  SQLWCHAR *pszDriverW= (SQLWCHAR *) myodbc_malloc((lenDriver + 1) *
                                                sizeof(SQLWCHAR), MYF(0));
  SQLWCHAR *pszAttributesW= (SQLWCHAR *)myodbc_malloc((lenAttrib + 1) *
                                                  sizeof(SQLWCHAR), MYF(0));

  utf8_as_sqlwchar(pszDriverW, lenDriver, (SQLCHAR* )pszDriverA, lenDriver);
  utf8_as_sqlwchar(pszAttributesW, lenAttrib, (SQLCHAR* )pszAttributesA,
                   lenAttrib);

  rc= ConfigDSNW(hWnd, nRequest, pszDriverW, pszAttributesW);

  x_free(pszDriverW);
  x_free(pszAttributesW);
  return rc;
}
#endif
