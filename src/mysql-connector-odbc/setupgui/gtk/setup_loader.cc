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

#include <sql.h>
#include <link.h>
#include <dlfcn.h>
#include <iostream>
#include <string.h>
#include <odbcinst.h>


struct SubmoduleLoader
{

  BOOL (*odbc_driver_prompt)(HWND hWnd, SQLWCHAR *instr, SQLUSMALLINT completion,
                     SQLWCHAR *outstr, SQLSMALLINT outmax,
                     SQLSMALLINT *outlen) = nullptr;

  BOOL (*odbc_config_dsnw)(HWND hWnd, WORD nRequest, LPCWSTR pszDriver,
                          LPCWSTR pszAttributes) = nullptr;

  BOOL (*odbc_config_dsn)(HWND hWnd, WORD nRequest, LPCSTR pszDriverA,
                         LPCSTR pszAttributesA) = nullptr;

  bool loaded = false;

  void *dlhandle = nullptr;

  SubmoduleLoader()
  {

    switch(check_major_gtk_version())
    {
      case 2:
        dlhandle = dlopen("libmyodbc8S-gtk2.so", RTLD_NOW);
        break;
      case 3:
        dlhandle = dlopen("libmyodbc8S-gtk3.so", RTLD_NOW);
        break;
    }

    if (!dlhandle)
      return;

    odbc_driver_prompt = (BOOL (*)(HWND, SQLWCHAR*, SQLUSMALLINT,
                   SQLWCHAR*, SQLSMALLINT, SQLSMALLINT*))dlsym(dlhandle,
                                                        "Driver_Prompt");
    odbc_config_dsnw = (BOOL (*)(HWND, WORD, LPCWSTR,
                   LPCWSTR))dlsym(dlhandle, "ConfigDSNW");

    odbc_config_dsn = (BOOL (*)(HWND, WORD, LPCSTR,
                   LPCSTR))dlsym(dlhandle, "ConfigDSN");
  }

  ~SubmoduleLoader()
  {
    if(dlhandle)
      dlclose(dlhandle);
  }

  int check_major_gtk_version()
  {

    struct link_map* lmap = nullptr;

    void *dlhandle = dlopen(nullptr, RTLD_NOW);
    int res = dlinfo(dlhandle, RTLD_DI_LINKMAP, &lmap);

    if (res != 0)
      return 3;  // If something is not right try using GTK+3

    while(NULL != lmap)
    {
      if (strstr(lmap->l_name, "gtk-2.0") ||
          strstr(lmap->l_name, "gtk-x11-2")
      )
        return 2;
      lmap = lmap->l_next;
    }
    return 3;
  }

};

SubmoduleLoader module;

/*
   Entry point for GUI prompting from SQLDriverConnect().
*/
BOOL Driver_Prompt(HWND hWnd, SQLWCHAR *instr, SQLUSMALLINT completion,
                   SQLWCHAR *outstr, SQLSMALLINT outmax, SQLSMALLINT *outlen)
{
  if(module.odbc_driver_prompt)
    return module.odbc_driver_prompt(hWnd, instr, completion, outstr, outmax, outlen);

  std::cout << "GUI dialog could not be loaded" << std::endl;
  return false;
}

/*
   Add, edit, or remove a Data Source Name (DSN). This function is
   called by "Data Source Administrator" on Windows, or similar
   application on Unix.
*/
BOOL INSTAPI ConfigDSNW(HWND hWnd, WORD nRequest, LPCWSTR pszDriver,
                        LPCWSTR pszAttributes)
{
  if(module.odbc_config_dsnw)
    return module.odbc_config_dsnw(hWnd, nRequest, pszDriver, pszAttributes);

  std::cout << "GUI dialog could not be loaded" << std::endl;
  return false;
}

#ifdef USE_IODBC
BOOL INSTAPI ConfigDSN(HWND hWnd, WORD nRequest, LPCSTR pszDriverA,
                       LPCSTR pszAttributesA)
{
  if(module.odbc_config_dsn)
    return module.odbc_config_dsn(hWnd, nRequest, pszDriverA, pszAttributesA);

  std::cout << "GUI dialog could not be loaded" << std::endl;
  return false;
}
#endif
