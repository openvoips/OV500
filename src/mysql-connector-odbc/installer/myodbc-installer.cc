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

/*!
  @brief  This program will aid installers when installing/uninstalling
          MyODBC.

          This program can register/deregister a myodbc driver and create
          a sample dsn. The key thing here is that it does this with
          cross-platform code - thanks to the ODBC installer API. This is
          most useful to those creating installers (apps using myodbc or
          myodbc itself).

          For example, this program is used in the postinstall script
          of the MyODBC for Mac OS X installer package.
*/

/*
 * Installer utility application. Handles installing/removing/listing, etc
 * driver and data source entries in the system. See usage text for details.
 */

#include "MYODBC_MYSQL.h"
#include "installer.h"
#include "stringutil.h"

#include <stdio.h>
#include <stdlib.h>
#include <iostream>
#include <iomanip>

const char usage[] =
"+---                                                                   \n"
"| myodbc-installer v" MYODBC_VERSION "                                 \n"
"+---                                                                   \n"
"|                                                                      \n"
"| Description                                                          \n"
"|                                                                      \n"
"|    This program is for managing MySQL Connector/ODBC configuration   \n"
"|    options at the command prompt. It can be used to register or      \n"
"|    unregister an ODBC driver, and to create, edit, or remove DSN.    \n"
"|                                                                      \n"
"|    The program has been designed to work across all platforms        \n"
"|    supported by Connector/ODBC, including MS Windows and             \n"
"|    various Unix-like systems.                                        \n"
"|                                                                      \n"
#ifndef _WIN32
#ifndef USE_IODBC
"|    The program requires that unixODBC version 2.2.14 or newer        \n"
"|    has already been installed on the system.                         \n"
"|    UnixODBC can be downloaded from here:                             \n"
"|    http://www.unixodbc.org/                                          \n"
"|                                                                      \n"
#else
"|    The program requires that iODBC version 3.52.12 or newer          \n"
"|    has already been installed on the system.                         \n"
"|    iODBC can be downloaded from here:                                \n"
"|    http://www.iodbc.org/                                             \n"
"|                                                                      \n"
#endif
#endif
"| Syntax                                                               \n"
"|                                                                      \n"
"|    myodbc-installer <Object> <Action> [Options]                      \n"
"|                                                                      \n"
"| Object                                                               \n"
"|                                                                      \n"
"|     -d driver                                                        \n"
"|     -s datasource                                                    \n"
"|                                                                      \n"
"| Action                                                               \n"
"|                                                                      \n"
"|     -l list                                                          \n"
"|     -a add (add/update for data source)                              \n"
"|     -r remove                                                        \n"
"|     -h display this help page and exit                               \n"
"|                                                                      \n"
"| Options                                                              \n"
"|                                                                      \n"
"|     -n <name>                                                        \n"
"|     -t <attribute string>                                            \n"
"|        if used for handling data source names the <attribute string> \n"
"|        can contain ODBC options for establishing connections to MySQL\n"
"|        Server. The full list of ODBC options can be obtained from    \n"
"|        http://dev.mysql.com/doc/connector-odbc/en/                   \n"
/* Note: These scope values line up with the SQLSetConfigMode constants */
"|     -c0 add as both a user and a system data source                  \n"
"|     -c1 add as a user data source                                    \n"
"|     -c2 add as a system data source (default)                        \n"
"|                                                                      \n"
"| Examples                                                             \n"
"|                                                                      \n"
"|    List drivers                                                      \n"
"|    shell> myodbc-installer -d -l                                     \n"
"|                                                                      \n"
#ifndef _WIN32
"|    Register a Unicode driver (UNIX example)                          \n"
"|    shell> myodbc-installer -d -a -n \"MySQL ODBC " MYODBC_STRSERIES " Unicode Driver\" \\ \n"
"|              -t \"DRIVER=/path/to/driver/libmyodbc8w.so;SETUP=/path/to/gui/myodbc8S.so\"\n"
"|                                                                      \n"
"|      Note                                                            \n"
"|         * The /path/to/driver is /usr/lib for 32-bit systems and     \n"
"|           some 64-bit systems, and /usr/lib64 for most 64-bit systems\n"
"|                                                                      \n"
"|         * driver_name is libmyodbc8a.so for the ANSI version and     \n"
"|           libmyodbc8w.so for the Unicode version of MySQL ODBC Driver\n"
"|                                                                      \n"
"|         * The SETUP parameter is optional; it provides location of   \n"
"|           the GUI module (libmyodbc8S.so) for DSN setup, which       \n"
"|           is not supported on Solaris and Mac OSX systems            \n"
"|                                                                      \n"
#else
"|    Register a Unicode driver (Windows example)                       \n"
"|    shell> myodbc-installer -d -a -n \"MySQL ODBC " MYODBC_STRSERIES " Unicode Driver\" \\ \n"
"|              -t \"DRIVER=myodbc8w.dll;SETUP=myodbc8S.dll\"\n"
"|                                                                      \n"
"|      Note                                                            \n"
"|         * driver_name is myodbc8a.dll for the ANSI version and       \n"
"|           myodbc8w.dll for the Unicode version of MySQL ODBC Driver  \n"
"|                                                                      \n"
#endif
"|    Add a new system data source name for Unicode driver              \n"
"|    shell> myodbc-installer -s -a -c2 -n \"test\" \\                  \n"
"|              -t \"DRIVER=MySQL ODBC " MYODBC_STRSERIES " Unicode Driver;SERVER=localhost;DATABASE=test;UID=myid;PWD=mypwd\"\n"
"|                                                                      \n"
"|    List data source name attributes for 'test'                       \n"
"|    shell> myodbc-installer -s -l -c2 -n \"test\"                    \n"
"+---                                                                   \n";

/* command line args */
#define OBJ_DRIVER 'd'
#define OBJ_DATASOURCE 's'

#define ACTION_LIST 'l'
#define ACTION_ADD 'a'
#define ACTION_REMOVE 'r'
#define ACTION_HELP 'h'

#define OPT_DSN 'n'
#define OPT_ATTR 't'
#define OPT_SCOPE 'c'

static char obj= 0;
static char action= 0;
static UWORD scope= ODBC_SYSTEM_DSN; /* default = system */

/* these two are 'paired' and the char data is converted to the wchar buf.
 * we check the char * to see if the arg was given then use the wchar in the
 * installer api. */
static char *name= NULL;
static SQLWCHAR *wname;

static char *attrstr= NULL;
static SQLWCHAR *wattrs;


void object_usage()
{
  fprintf(stderr,
          "[ERROR] Object must be either driver (d) or data source (s)\n");
}


void action_usage()
{
  fprintf(stderr, "[ERROR] One action must be specified\n");
}


void main_usage(FILE *out_file)
{
  fprintf(out_file, usage);
}


/*
 * Print an error retrieved from SQLInstallerError.
 */
void print_installer_error()
{
  int msgno;
  DWORD errcode;
  char errmsg[256];
  for(msgno= 1; msgno < 9; ++msgno)
  {
    if (SQLInstallerError(msgno, &errcode, errmsg, 256, NULL) != SQL_SUCCESS)
      return;
    fprintf(stderr, "[ERROR] SQLInstaller error %d: %s\n", errcode, errmsg);
  }
}


/*
 * Print a general ODBC error (non-odbcinst).
 */
void print_odbc_error(SQLHANDLE hnd, SQLSMALLINT type)
{
  SQLCHAR sqlstate[20];
  SQLCHAR errmsg[1000];
  SQLINTEGER nativeerr;

  fprintf(stderr, "[ERROR] ODBC error");

  if(SQL_SUCCEEDED(SQLGetDiagRec(type, hnd, 1, sqlstate, &nativeerr,
                                 errmsg, 1000, NULL)))
    printf(": [%s] %d -> %s\n", sqlstate, nativeerr, errmsg);
  printf("\n");
}


/*
 * Handler for "list driver" command (-d -l -n drivername)
 */
int list_driver_details(Driver *driver)
{
  SQLWCHAR buf[50000];
  SQLWCHAR *entries= buf;
  int rc;

  /* lookup the driver */
  if ((rc = driver->lookup()) < 0)
  {
    fprintf(stderr, "[ERROR] Driver not found '%s'\n",
            (const char*)driver->name);
    return 1;
  }
  else if (rc > 0)
  {
    print_installer_error();
    return 1;
  }

  /* print driver details */
  if (driver->name)
    printf("FriendlyName: %s\n", (const char *)driver->name);

  if (driver->lib)
    printf("DRIVER      : %s\n", (const char *)driver->lib);

  if (driver->setup_lib)
    printf("SETUP       : %s\n", (const char *)driver->setup_lib);

  return 0;
}


/*
 * Handler for "list drivers" command (-d -l)
 */
int list_drivers()
{
  char buf[50000];
  char *drivers= buf;

  if (!SQLGetInstalledDrivers(buf, 50000, NULL))
  {
    print_installer_error();
    return 1;
  }

  while (*drivers)
  {
    printf("%s\n", drivers);
    drivers += strlen(drivers) + 1;
  }

  return 0;
}


#if !defined(HAVE_SQLGETPRIVATEPROFILESTRINGW) && !defined(_WIN32)
/**
  The version of iODBC that shipped with Mac OS X 10.4 does not implement
  the Unicode versions of various installer API functions, so we have to
  do it ourselves. This function is only needed in this module, so we do
  not define it on the global level to avoid conflicts
*/

BOOL INSTAPI
SQLInstallDriverExW(const MyODBC_LPCWSTR lpszDriver, const MyODBC_LPCWSTR lpszPathIn,
                    LPWSTR lpszPathOut, WORD cbPathOutMax, WORD *pcbPathOut,
                    WORD fRequest, LPDWORD lpdwUsageCount)
{
  const SQLWCHAR *pos;
  SQLINTEGER len;
  BOOL rc;
  char *driver, *pathin, *pathout;
  WORD out;

  if (!pcbPathOut)
    pcbPathOut= &out;

  /* Calculate length of double-\0 terminated string */
  pos= lpszDriver;
  while (*pos)
    pos+= sqlwcharlen(pos) + 1;
  len= pos - lpszDriver + 1;
  driver= (char *)sqlwchar_as_utf8(lpszDriver, &len);

  len= SQL_NTS;
  pathin= (char *)sqlwchar_as_utf8(lpszPathIn, &len);

  if (cbPathOutMax > 0)
    pathout= (char *)myodbc_malloc(cbPathOutMax * 4 + 1, MYF(0)); /* 4 = max utf8 charlen */

  rc= SQLInstallDriverEx(driver, pathin, pathout, cbPathOutMax * 4,
                         pcbPathOut, fRequest, lpdwUsageCount);

  if (rc == TRUE && cbPathOutMax)
    *pcbPathOut= utf8_as_sqlwchar(lpszPathOut, cbPathOutMax,
                                  (SQLCHAR *)pathout, *pcbPathOut);

  x_free(driver);
  x_free(pathin);
  x_free(pathout);

  return rc;
}


BOOL INSTAPI
SQLRemoveDriverW(const MyODBC_LPCWSTR lpszDriver, BOOL fRemoveDSN,
                 LPDWORD lpdwUsageCount)
{
  BOOL ret;
  SQLINTEGER len= SQL_NTS;
  char *driver= (char *)sqlwchar_as_utf8(lpszDriver, &len);

  ret= SQLRemoveDriver(driver, fRemoveDSN, lpdwUsageCount);

  x_free(driver);

  return ret;
}
#endif


/*
 * Handler for "add driver" command (-d -a -n drivername -t attrs)
 */
int add_driver(Driver *driver, const SQLWCHAR *attrs)
{
  DWORD usage_count;
  SQLWCHAR attrs_null[4096]; /* null-delimited pairs */
  SQLWCHAR prevpath[256];

  /* read driver attributes into object */
  if (driver->from_kvpair_semicolon(attrs))
  {
    fprintf(stderr,
            "[ERROR] Could not parse key-value pair attribute string\n");
    return 1;
  }

  /* convert to null-delimited for installer API */
  if (driver->to_kvpair_null(attrs_null, 4096))
  {
    fprintf(stderr,
            "[ERROR] Could not create new key-value pair attribute string\n");
    return 1;
  }

  if (SQLInstallDriverExW(attrs_null, NULL, prevpath, 256, NULL,
                          ODBC_INSTALL_COMPLETE, &usage_count) != TRUE)
  {
    print_installer_error();
    return 1;
  }

  printf("Success: Usage count is %d\n", usage_count);

  return 0;
}


/*
 * Handler for "remove driver" command (-d -a -n drivername)
 */
int remove_driver(Driver *driver)
{
  DWORD usage_count;

  if (SQLRemoveDriverW(driver->name, FALSE, &usage_count) != TRUE)
  {
    print_installer_error();
    return 1;
  }

  printf("Success: Usage count is %d\n", usage_count);

  return 0;
}


/*
 * Handle driver actions. We setup a driver object to be used
 * for all actions.
 */
int handle_driver_action()
{
  int rc= 0;
  UWORD config_mode= config_set(scope);
  Driver driver;

  /* check name is given if needed */
  switch (action)
  {
  case ACTION_ADD:
  case ACTION_REMOVE:
    if (!name)
    {
      fprintf(stderr, "[ERROR] Name missing to add/remove driver\n");
      rc= 1;
      goto end;
    }
  }

  /* set the driver name */
  if (name && wname)
    driver.name = wname;

  /* perform given action */
  switch (action)
  {
  case ACTION_LIST:
    if (name)
      rc= list_driver_details(&driver);
    else
      rc= list_drivers();
    break;
  case ACTION_ADD:
    if (attrstr)
      rc= add_driver(&driver, wattrs);
    else
    {
      fprintf(stderr, "[ERROR] Attribute string missing to add driver\n");
      rc= 1;
    }
    break;
  case ACTION_REMOVE:
    rc= remove_driver(&driver);
    break;
  }

end:
  config_set(config_mode);
  return rc;
}


/*
 * Handler for "list data source" command (-s -l -n drivername)
 */
int list_datasource_details(DataSource *ds)
{
  int rc;

  if ((rc= ds->lookup()) < 0)
  {
    fprintf(stderr, "[ERROR] Data source not found '%s'\n",
            (const char*)ds->opt_DSN);
    return 1;
  }
  else if (rc > 0)
  {
    print_installer_error();
    return 1;
  }

  typedef std::pair<std::string, optionBase*> option_param;
  std::vector<option_param> params;

  #define OPTION_ADD_TO_MAP(X) params.push_back(std::make_pair(#X, &ds->opt_##X));
  FULL_OPTIONS_LIST(OPTION_ADD_TO_MAP)

  #define OPTION_CUSTOM_OUTPUT(OPT, STR) { \
    std::string keyname = #OPT; \
    auto it = std::find_if( \
        params.begin(), params.end(), \
        [keyname](const option_param &op) { return op.first == keyname; }); \
    if (it != params.end()) { it->first = STR; } \
  }


  OPTION_CUSTOM_OUTPUT(DSN, "Name");
  OPTION_CUSTOM_OUTPUT(DRIVER, "Driver");
  OPTION_CUSTOM_OUTPUT(UID, "Uid");
  OPTION_CUSTOM_OUTPUT(DATABASE, "Database");
  OPTION_CUSTOM_OUTPUT(DESCRIPTION, "Description");
  OPTION_CUSTOM_OUTPUT(SERVER, "Server");
  OPTION_CUSTOM_OUTPUT(SOCKET, "Socket");
  OPTION_CUSTOM_OUTPUT(PORT, "Port");
  OPTION_CUSTOM_OUTPUT(INITSTMT, "Initial Statement");
  OPTION_CUSTOM_OUTPUT(CHARSET, "Character Set");
  OPTION_CUSTOM_OUTPUT(SSL_KEY, "SSL Key");
  OPTION_CUSTOM_OUTPUT(SSL_CERT, "SSL Cert");
  OPTION_CUSTOM_OUTPUT(SSL_CA, "SSL CA");
  OPTION_CUSTOM_OUTPUT(SSL_CAPATH, "SSL CA Path");
  OPTION_CUSTOM_OUTPUT(SSL_CIPHER, "SSL Cipher");
  OPTION_CUSTOM_OUTPUT(SSL_MODE, "SSL Mode");
  OPTION_CUSTOM_OUTPUT(SSLVERIFY, "Verify SSL Cert");
  OPTION_CUSTOM_OUTPUT(RSAKEY, "RSA Public Key");
  OPTION_CUSTOM_OUTPUT(TLS_VERSIONS, "TLS Versions");
  OPTION_CUSTOM_OUTPUT(SSL_CRL, "SSL CRL");
  OPTION_CUSTOM_OUTPUT(SSL_CRLPATH, "SSL CRL Path");
  OPTION_CUSTOM_OUTPUT(PLUGIN_DIR, "Plugin Directory");
  OPTION_CUSTOM_OUTPUT(DEFAULT_AUTH, "Default Authentication Library");
  OPTION_CUSTOM_OUTPUT(OCI_CONFIG_FILE, "OCI Config File");
  OPTION_CUSTOM_OUTPUT(OCI_CONFIG_PROFILE, "OCI Config Profile");
  OPTION_CUSTOM_OUTPUT(AUTHENTICATION_KERBEROS_MODE, "Kerberos Authentication Mode");

  bool bool_mode = false;

  for (const auto &v : params) {
    if (!v.second->is_set() || v.second->is_default())
      continue;

    std::string out_str = v.first;

    switch (v.second->get_type()) {
      case optionBase::opt_type::STRING: {
        out_str.append(":");
        std::cout << std::setw(21) << std::left << out_str;

        optionStr *str_opt = (optionStr *)v.second;
        std::cout << (const char*)(*str_opt);
        break;
      }
      case optionBase::opt_type::INT: {
        out_str.append(":");
        std::cout << std::setw(21) << std::left << out_str;

        optionInt *int_opt = (optionInt *)v.second;
        std::cout << (int)(*int_opt);
        break;
      }
      case optionBase::opt_type::BOOL: {
        if (!bool_mode) {
          // On first go with bool options print this
          std::cout << "Options:" << std::endl;
          bool_mode = true;
        }

        std::cout << "        " << out_str;
        optionBool *bool_opt = (optionBool *)v.second;
        break;
      }
    }
    std::cout << std::endl;
  }

  return 0;
}


/*
 * Handler for "list data sources" command (-s -l)
 */
int list_datasources()
{
  SQLHANDLE env;
  SQLRETURN rc;
  SQLUSMALLINT dir= 0; /* SQLDataSources fetch direction */
  SQLCHAR name[256];
  SQLCHAR description[256];

  /* determine 'direction' to pass to SQLDataSources */
  switch (scope)
  {
  case ODBC_BOTH_DSN:
    dir= SQL_FETCH_FIRST;
    break;
  case ODBC_USER_DSN:
    dir= SQL_FETCH_FIRST_USER;
    break;
  case ODBC_SYSTEM_DSN:
    dir= SQL_FETCH_FIRST_SYSTEM;
    break;
  }

  /* allocate handle and set ODBC API v3 */
  if ((rc= SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE,
                          &env)) != SQL_SUCCESS)
  {
    fprintf(stderr, "[ERROR] Failed to allocate env handle\n");
    return 1;
  }

  if ((rc= SQLSetEnvAttr(env, SQL_ATTR_ODBC_VERSION, (SQLPOINTER)SQL_OV_ODBC3,
                         SQL_IS_INTEGER)) != SQL_SUCCESS)
  {
    print_odbc_error(env, SQL_HANDLE_ENV);
    SQLFreeHandle(SQL_HANDLE_ENV, env);
    return 1;
  }

  /* retrieve and print data source */
  while ((rc= SQLDataSources(env, dir, name, 256, NULL, description,
                             256, NULL)) == SQL_SUCCESS)
  {
    printf("%-20s - %s\n", name, description);
    dir= SQL_FETCH_NEXT;
  }

  if (rc != SQL_NO_DATA)
  {
    print_odbc_error(env, SQL_HANDLE_ENV);
    SQLFreeHandle(SQL_HANDLE_ENV, env);
    return 1;
  }

  SQLFreeHandle(SQL_HANDLE_ENV, env);

  return 0;
}


/*
 * Handler for "add data source" command (-s -a -n drivername -t attrs)
 */
int add_datasource(DataSource *ds, const SQLWCHAR *attrs)
{
  /* read datasource object from attributes */
  if (ds->from_kvpair(attrs, ';'))
  {
    fprintf(stderr,
            "[ERROR] Could not parse key-value pair attribute string\n");
    return 1;
  }

  /* validate */
  if (!ds->opt_DRIVER)
  {
    fprintf(stderr, "[ERROR] Driver must be specified for a data source\n");
    return 1;
  }

  /* Add it */
  if (ds->add())
  {
    print_installer_error();
    fprintf(stderr,
            "[ERROR] Data source entry failed, remove or try again\n");
    return 1;
  }

  printf("Success\n");
  return 0;
}


/*
 * Handler for "remove data source" command (-s -r -n drivername)
 */
int remove_datasource(DataSource *ds)
{
  /*
     First check that it exists, because SQLRemoveDSNFromIni
     returns true, even if it doesn't.
   */
  if (ds->exists())
  {
    fprintf(stderr, "[ERROR] Data source doesn't exist\n");
    return 1;
  }

  if (SQLRemoveDSNFromIniW(ds->opt_DSN) != TRUE)
  {
    print_installer_error();
    return 1;
  }

  printf("Success\n");

  return 0;
}


/*
 * Handle driver actions. We setup a data source object to be used
 * for all actions. Config/scope set+restore is wrapped around the
 * action.
 */
int handle_datasource_action()
{
  int rc= 0;
  UWORD config_mode= config_set(scope);
  DataSource ds;

  /* check name is given if needed */
  switch (action)
  {
  case ACTION_ADD:
  case ACTION_REMOVE:
    if (!name)
    {
      fprintf(stderr, "[ERROR] Name missing to add/remove data source\n");
      rc= 1;
      goto end;
    }
  }

  /* set name if given */
  if (name)
    ds.opt_DSN = wname;

  /* perform given action */
  switch (action)
  {
  case ACTION_LIST:
    if (name)
      rc= list_datasource_details(&ds);
    else
      rc= list_datasources();
    break;
  case ACTION_ADD:
    if (scope == ODBC_BOTH_DSN)
    {
      fprintf(stderr, "[ERROR] Adding data source must be either "
                      "user or system scope (not both)\n");
      rc= 1;
    }
    else if (!attrstr)
    {
      fprintf(stderr, "[ERROR] Attribute string missing to add data source\n");
      rc= 1;
    }
    else
    {
      rc= add_datasource(&ds, wattrs);
    }
    break;
  case ACTION_REMOVE:
    rc= remove_datasource(&ds);
    break;
  }

end:
  config_set(config_mode);
  return rc;
}


/*
 * Entry point, parse args and do first set of validation.
 */
int main(int argc, char **argv)
{
  char *arg;
  int i;
  SQLINTEGER convlen;

  /* minimum number of args to do anything useful */
  if (argc < 3)
  {
    if (argc == 2)
    {
      arg= argv[1]; /* check for help option */
      if (*arg == '-' && *(arg + 1) == ACTION_HELP)
      {
        main_usage(stdout);
        return 1;
      }
    }

    fprintf(stderr, "[ERROR] Not enough arguments given\n");
    main_usage(stderr);
    return 1;
  }

  /* parse args */
  for(i= 1; i < argc; ++i)
  {
    arg= argv[i];
    if (*arg == '-')
      arg++;

    /* we should be left with a single character option (except scope)
     * all strings are skipped by the respective option below */
    if (*arg != OPT_SCOPE && *(arg + 1))
    {
      fprintf(stderr, "[ERROR] Invalid command line option: %s\n", arg);
      return 1;
    }

    switch (*arg)
    {
    case OBJ_DRIVER:
    case OBJ_DATASOURCE:
      /* make sure we haven't already assigned the object */
      if (obj)
      {
        object_usage();
        return 1;
      }
      obj= *arg;
      break;
    case ACTION_LIST:
    case ACTION_ADD:
    case ACTION_REMOVE:
      if (action)
      {
        action_usage();
        return 1;
      }
      action= *arg;
      break;
    case OPT_DSN:
      if (i + 1 == argc || *argv[i + 1] == '-')
      {
        fprintf(stderr, "[ERROR] Missing name\n");
        return 1;
      }
      name= argv[++i];
      break;
    case OPT_ATTR:
      if (i + 1 == argc || *argv[i + 1] == '-')
      {
        fprintf(stderr, "[ERROR] Missing attribute string\n");
        return 1;
      }
      attrstr= argv[++i];
      break;
    case OPT_SCOPE:
      /* convert to integer */
      scope= *(++arg) - '0';
      if (scope > 2 || *(arg + 1) /* another char exists */)
      {
        fprintf(stderr, "[ERROR] Invalid scope: %s\n", arg);
        return 1;
      }
      break;
    case ACTION_HELP:
      /* print usage if -h is given anywhere */
      main_usage(stdout);
      return 1;
    default:
      fprintf(stderr, "[ERROR] Invalid command line option: %s\n", arg);
      return 1;
    }
  }

  if (!action)
  {
    action_usage();
    return 1;
  }

  /* init libmysqlclient */
  my_sys_init();
  utf8_charset_info= get_charset_by_csname(transport_charset, MYF(MY_CS_PRIMARY),
                                           MYF(0));

  /* convert to SQLWCHAR for installer API */
  convlen= SQL_NTS;
  if (name && !(wname= sqlchar_as_sqlwchar(default_charset_info,
                                           (SQLCHAR *)name, &convlen, NULL)))
  {
    fprintf(stderr, "[ERROR] Name is invalid\n");
    return 1;
  }

  convlen= SQL_NTS;
  if (attrstr && !(wattrs= sqlchar_as_sqlwchar(default_charset_info,
                                               (SQLCHAR *)attrstr, &convlen,
                                               NULL)))
  {
    fprintf(stderr, "[ERROR] Attribute string is invalid\n");
    return 1;
  }

  /* handle appropriate action */
  switch (obj)
  {
  case OBJ_DRIVER:
    return handle_driver_action();
  case OBJ_DATASOURCE:
    return handle_datasource_action();
  default:
    object_usage();
    return 1;
  }
}

