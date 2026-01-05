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

/**
  @file odbctap.h

  A basic interface for writing tests that produces TAP-compliant output.
*/

/* We don't want ansi calls to be mapped to unicode counterparts, but that does not work */
/* #define SQL_NOUNICODEMAP 1*/


#ifdef HAVE_CONFIG_H
# include <myconf.h>
#endif
/* Work around iODBC header bug on Mac OS X 10.3 */
#undef HAVE_CONFIG_H

#include <stdarg.h>
#ifdef WIN32
#  include <windows.h>
#  define sleep(x) Sleep(x*1000)
#  include <crtdbg.h>
#else
#  include <unistd.h>
#  include <signal.h>
#endif

#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#ifdef _WIN32
/* Driver does that - applications should do that too, to have same size of structures */
# ifndef RC_INVOKED
#  pragma pack(push, 1)
# endif
#endif

#include <sql.h>
#include <sqlext.h>
#include <odbcinst.h>

#ifdef _WIN32
# ifndef RC_INVOKED
#  pragma pack(pop)
# endif
#endif

/* for clock() */
#include <time.h>

/* Get routines for helping with Unicode conversion. */
#define ODBCTAP

#include "mysql_version.h"
#include "../util/unicode_transcode.h"


void printMessage(const char *fmt, ...)
{
  va_list ap;
  va_start(ap, fmt);
  fprintf(stdout, "# ");
  vfprintf(stdout, fmt, ap);
  fprintf(stdout, "\n");
  va_end(ap);
}


/* Function that converts wchr_t string to char string for a debug output
   Minimum guaranteed lenght of string it can convert is 2048/12=170. Stops if its
   buffer cannot accomodate more characters from parameter string */
const char * wstr4output(const wchar_t *wstr)
{
  size_t i;
  static char str[2048];
  char dbuf[16], *to= str;

  for (i= 0; i < wcslen(wstr); ++i)
  {
    if (wstr[i] < 0x7f )
    {
      sprintf(dbuf, "%c", wstr[i]);
    }
    else
    {
      sprintf(dbuf, "[0x%x]", wstr[i]);
    }

    if (to - str + strlen(dbuf) + 1 < sizeof(str))
    {
      to+= sprintf(to, "%s", dbuf);
    }
    else
    {
      break;
    }
  }

  return str;
}

#define MAX_NAME_LEN 255
#define MAX_COLUMNS 500
#define MAX_ROW_DATA_LEN 1000
#define MYSQL_NAME_LEN 64
#define MAX_MEM_BLOCK_ELEMENTS 100

#ifdef __WIN__
#define _MY_NEWLINE "\r\n"
#else
#define _MY_NEWLINE "\n"
#endif

SQLCHAR *mydriver= (SQLCHAR *)"{MySQL ODBC 8.0 Driver}";
SQLCHAR *mydsn= (SQLCHAR *)"test";
SQLCHAR *myuid= (SQLCHAR *)"root";
SQLCHAR *mypwd= (SQLCHAR *)"";
SQLCHAR *mysock= NULL;
SQLCHAR *mydns_srv= NULL;
int      myoption= 0, myport= 0, myenable_pooling= 0;
SQLCHAR *my_str_options= (SQLCHAR *)""; /* String for additional connection options */
SQLCHAR *myserver= (SQLCHAR *)"localhost";
SQLCHAR *mydb= (SQLCHAR *)"test";
SQLCHAR *myauth= NULL;
SQLCHAR *myplugindir= NULL;

SQLCHAR *test_db= (SQLCHAR *)"client_odbc_test";
/* Suffix is useful if a testsuite is run more than once */
const SQLCHAR *testname_suffix= (SQLCHAR*)"";
/* -1 means that the fact has to be established, 0 - ansi driver, 1 - unicode */
int     unicode_driver= -1;
#define REQUIRES_UNICODE_DRIVER if (unicode_driver == 0) skip("This testcase is designed for Unicode drivers only")
#define REQUIRES_ANSI_DRIVER if (unicode_driver != 0) skip("This testcase is designed for ANSI drivers only")

int      init_auth_plugin= 0;

#ifndef OK
# define OK 0
#endif
#ifndef FAIL
# define FAIL 1
#endif
#ifndef SKIP
# define SKIP -1
#endif
#ifndef FALSE
# define FALSE 0
#endif
#ifndef TRUE
# define TRUE 1
#endif

char *SKIP_REASON= NULL;

#define USE_DRIVER (char *)-1

typedef int (*test_func)(SQLHDBC, SQLHSTMT, SQLHENV);
static void print_diag(SQLRETURN rc, SQLSMALLINT htype, SQLHANDLE handle,
           const char *text, const char *file, int line);

/* Disable _attribute__ on non-gcc compilers. */
#if !defined(__attribute__) && !defined(__GNUC__)
# define __attribute__(arg)
#endif

/*
 The parameters may be unused. so we add the attribute to stifle warnings.
 They may also still be used, and no warning will be generated.
*/
#define DECLARE_TEST(name) \
  int (name)(SQLHDBC hdbc __attribute__((unused)), \
             SQLHSTMT hstmt __attribute__((unused)), \
             SQLHENV henv __attribute__((unused)))

typedef struct {
  char      *name;
  test_func func;
  int       expect;
  int       required_driver_type;
} my_test;


typedef struct
{
  void *blk[MAX_MEM_BLOCK_ELEMENTS];
  int  counter;
} GCOLL;

GCOLL gc_blk;


#ifdef WIN32
void test_timeout(int signum);
HANDLE halarm= NULL;
DWORD WINAPI win32_alarm(LPVOID arg)
{
  unsigned long long llarg = (unsigned long long)arg;
  DWORD timeout= ((DWORD) llarg) * 1000;
  while (WaitForSingleObject(halarm, timeout) == WAIT_OBJECT_0);
  test_timeout(0);
  return 0;
}
#define ENABLE_ALARMS    int do_alarms= !getenv("DISABLE_TIMEOUT")
#define RUN_TESTS_SIGNAL halarm= CreateEvent(NULL, FALSE, FALSE, NULL); \
                         if (do_alarms) \
                           CreateThread(NULL, 0, win32_alarm, (LPVOID) 1200, 0, NULL); \
                         do_alarms= 0
#define RUN_TESTS_ALARM (void) SetEvent(halarm)
#else
#define ENABLE_ALARMS    int do_alarms= !getenv("DISABLE_TIMEOUT")
#define RUN_TESTS_SIGNAL (void)signal(SIGALRM, test_timeout)
#define RUN_TESTS_ALARM  if (do_alarms) alarm(1200)
#endif

void mem_debug_init()
{
#ifdef _WIN32
  int dbg = _CrtSetDbgFlag(_CRTDBG_REPORT_FLAG);
  dbg |= _CRTDBG_ALLOC_MEM_DF;
  dbg |= _CRTDBG_CHECK_ALWAYS_DF;
  dbg |= _CRTDBG_DELAY_FREE_MEM_DF;
  dbg |= _CRTDBG_LEAK_CHECK_DF;
  _CrtSetDbgFlag(dbg);
#endif
}

/*
  Initialize garbage collector
  counter (or position)
*/
void mem_gc_init()
{
  gc_blk.counter= 0;
}

#define SQL_IS_SUCCESS(R) ((R == SQL_SUCCESS) || (R == SQL_SUCCESS_WITH_INFO))

#define DECLARE_BASIC_HANDLES(E, C, S) SQLHENV E= NULL; \
  SQLHDBC C= NULL; \
  SQLHSTMT S= NULL

#define BEGIN_TESTS my_test tests[]= {
#define ADD_TEST(name)          { #name, name, OK  , -1 },
#define ADD_TEST_UNICODE(name)  { #name, name, OK  ,  1 },
#define ADD_TEST_ANSI(name)     { #name, name, OK  ,  0 },
#define ADD_TODO(name)          { #name, name, FAIL, -1 },
#ifdef EXPOSE_TOFIX_TESTS
# define ADD_TOFIX(name) { #name, name, OK,   -1 },
#else
# define ADD_TOFIX(name) { #name, name, FAIL, -1 },
#endif
#define END_TESTS }; \
void test_timeout(int signum __attribute__((unused))) \
{ \
  printf("Bail out! Timeout.\n"); \
  exit(1); \
} \
\
int main(int argc, char **argv) \
{ \
  SQLHENV  henv; \
  SQLHDBC  hdbc; \
  SQLHSTMT hstmt = NULL; \
  int      i, num_tests, failcnt= 0; \
  ENABLE_ALARMS; \
\
  mem_debug_init(); \
  mem_gc_init(); \
\
  /* Set from environment, possibly overrided by command line */ \
  if (getenv("TEST_DSN")) \
    mydsn=  (SQLCHAR *)getenv("TEST_DSN"); \
  if (getenv("TEST_DRIVER")) \
    mydriver=  (SQLCHAR *)getenv("TEST_DRIVER"); \
  if (getenv("TEST_UID")) \
    myuid=  (SQLCHAR *)getenv("TEST_UID"); \
  if (getenv("TEST_PASSWORD")) \
    mypwd=  (SQLCHAR *)getenv("TEST_PASSWORD"); \
  if (getenv("TEST_SOCKET")) \
    mysock= (SQLCHAR *)getenv("TEST_SOCKET"); \
  if (getenv("TEST_SERVER")) \
    myserver= (SQLCHAR *)getenv("TEST_SERVER"); \
  if (getenv("TEST_PORT")) \
    myport= atoi(getenv("TEST_PORT")); \
  if (getenv("TEST_ENABLE_POOLING")) \
    myenable_pooling= atoi(getenv("TEST_ENABLE_POOLING")); \
  if (getenv("TEST_DEFAULTAUTH")) \
    myauth=  (SQLCHAR *)getenv("TEST_DEFAULTAUTH"); \
  if (getenv("TEST_PLUGINDIR")) \
    myplugindir=  (SQLCHAR *)getenv("TEST_PLUGINDIR"); \
  if (getenv("TEST_DNS_SRV")) \
    mydns_srv= (SQLCHAR *)getenv("TEST_DNS_SRV"); \
\
  if (argc > 1) \
    mydsn= (SQLCHAR *)argv[1]; \
  if (argc > 2) \
    myuid= (SQLCHAR *)argv[2]; \
  if (argc > 3) \
    mypwd= (SQLCHAR *)argv[3]; \
  if (argc > 4) \
    mysock= (SQLCHAR *)argv[4];

#define SET_DSN_OPTION(x) \
  myoption= (x);

#define RUN_TESTS_ONCE \
  setbuf(stdout, NULL); \
  num_tests= sizeof(tests) / sizeof(tests[0]); \
  printf("1..%d\n", num_tests); \
\
  RUN_TESTS_SIGNAL; \
\
\
  for (i= 0; i < num_tests; i++ ) \
  { \
    int rc; \
    if (tests[i].required_driver_type > -1 && tests[i].required_driver_type != unicode_driver) \
    { \
      printf("ok %d # SKIP This testcase is designed for %s drivers only\n", i+1, \
             unicode_driver == 0 ? "Unicode" : "ANSI" ); \
    } \
    else \
    { \
      if (alloc_basic_handles(&henv, &hdbc, &hstmt) != OK) \
        exit(1); \
      RUN_TESTS_ALARM; \
      rc= tests[i].func(hdbc, hstmt, henv); \
      printf("%s %d - %s%s %s%s\n", \
           (rc == OK || rc == SKIP) ? "ok" : "not ok", \
           i + 1, \
           tests[i].name, testname_suffix, \
           (tests[i].expect == FAIL ? "# TODO" : \
            rc == SKIP ? "# SKIP " : ""), \
           SKIP_REASON ? SKIP_REASON : ""); \
      if ((rc == FAIL) && (FAIL != tests[i].expect)) \
        ++failcnt; \
      SKIP_REASON= NULL; /* Reset SKIP_REASON */ \
      (void)free_basic_handles(&henv, &hdbc, &hstmt); \
      mem_gc_flush(); \
    } \
  }

#define RUN_TESTS \
  RUN_TESTS_ONCE \
  mem_gc_flush(); \
  exit(failcnt); \
}


/**
 Skip a test, giving a reason.
*/
#define skip(reason) \
  do { \
    SKIP_REASON= reason; \
    return SKIP; \
  } while (0)

#ifdef CPP_THROW_CLASS
#define TEST_RETURN_FAIL throw CPP_THROW_CLASS(__FILE__, __LINE__);
#else
#define TEST_RETURN_FAIL return FAIL;
#endif

/**
  Execute an SQL statement and bail out if the execution does not return
  SQL_SUCCESS or SQL_SUCCESS_WITH_INFO.

  @param statement Handle for statement object
  @param query     The query to execute
*/
#define ok_sql(statement, query) \
do { \
  SQLRETURN rc= SQLExecDirect((statement), (SQLCHAR *)(query), SQL_NTS); \
  print_diag(rc, SQL_HANDLE_STMT, (statement), \
             "SQLExecDirect(" #statement ", \"" query "\", SQL_NTS)",\
             __FILE__, __LINE__); \
  if (rc != SQL_SUCCESS && rc != SQL_SUCCESS_WITH_INFO) \
    TEST_RETURN_FAIL; \
} while (0)


/**
  Verify that the result of an SQL statement call matches an expected
  result, such as SQL_ERROR.

  @param statement Handle for statement object
  @param query     The query to execute
  @param expect    The expected result
*/
#define expect_sql(statement, query, expect) \
do { \
  SQLRETURN rc= SQLExecDirect((statement), (SQLCHAR *)(query), SQL_NTS); \
  if (rc != (expect)) \
  { \
    print_diag(rc, SQL_HANDLE_STMT, (statement), \
               "SQLExecDirect(" #statement ", \"" query "\", SQL_NTS)",\
               __FILE__, __LINE__); \
    printf("# Expected %d, but got %d in %s on line %d\n", expect, rc, \
           __FILE__, __LINE__); \
    TEST_RETURN_FAIL; \
  } \
} while (0)


/**
  Verify that the results of an ODBC function call on a statement handle was
  SQL_SUCCESS or SQL_SUCCESS_WITH_INFO.

  @param statement Handle for statement object
  @param call      The function call
*/
#define ok_stmt(statement, call) \
do { \
  SQLRETURN _rc= (call); \
  print_diag(_rc, SQL_HANDLE_STMT, (statement), #call, __FILE__, __LINE__); \
  if (_rc != SQL_SUCCESS && _rc != SQL_SUCCESS_WITH_INFO) \
    TEST_RETURN_FAIL; \
} while (0)


/**
  Verify that the results of an ODBC function call on a descriptor handle was
  SQL_SUCCESS or SQL_SUCCESS_WITH_INFO.

  @param desc Handle for descriptor object
  @param call The function call
*/
#define ok_desc(desc, call) \
do { \
  SQLRETURN rc= (call); \
  print_diag(rc, SQL_HANDLE_DESC, (desc), #call, __FILE__, __LINE__); \
  if (rc != SQL_SUCCESS && rc != SQL_SUCCESS_WITH_INFO) \
    TEST_RETURN_FAIL; \
} while (0)


/**
  Verify that the result of an ODBC function call matches an expected
  result, such as SQL_ERROR or SQL_NO_DATA_FOUND.

  @param hnd       Handle for object
  @param type      Type of handle
  @param call      The function call
  @param expect    The expected result
*/
#define expect_odbc(hnd, type, call, expect) \
do { \
  SQLRETURN rc= (call); \
  if (rc != (expect)) \
  { \
    print_diag(rc, (type), (hnd), #call, __FILE__, __LINE__); \
    printf("# Expected %d, but got %d in %s on line %d\n", (expect), rc, \
           __FILE__, __LINE__); \
    TEST_RETURN_FAIL; \
  } \
} while (0)


#define expect_env(env, call, expect) \
  expect_odbc((env), SQL_HANDLE_STMT, (call), (expect))


#define expect_dbc(dbc, call, expect) \
  expect_odbc((dbc), SQL_HANDLE_STMT, (call), (expect))


#define expect_stmt(statement, call, expect) \
  expect_odbc((statement), SQL_HANDLE_STMT, (call), (expect))


#define expect_desc(desc, call, expect) \
  expect_odbc((desc), SQL_HANDLE_DESC, (call), (expect))


/**
  Verify that the results of an ODBC function call on an environment handle
  was SQL_SUCCESS or SQL_SUCCESS_WITH_INFO.

  @param environ Handle for environment
  @param call    The function call
*/
#define ok_env(environ, call) \
do { \
  SQLRETURN _rc= (call); \
  print_diag(_rc, SQL_HANDLE_ENV, (environ), #call, __FILE__, __LINE__); \
  if (_rc != SQL_SUCCESS && _rc != SQL_SUCCESS_WITH_INFO) \
    TEST_RETURN_FAIL; \
} while (0)


/**
  Verify that the results of an ODBC function call on a connection handle
  was SQL_SUCCESS or SQL_SUCCESS_WITH_INFO.

  @param con   Handle for database connection
  @param call  The function call
*/
#define ok_con(con, call) \
do { \
  SQLRETURN _rc= (call); \
  print_diag(_rc, SQL_HANDLE_DBC, (con), #call, __FILE__, __LINE__); \
  if (_rc != SQL_SUCCESS && _rc != SQL_SUCCESS_WITH_INFO) \
    TEST_RETURN_FAIL; \
} while (0)


/**
  Verify that the results of an ODBC function call on an environment handle
  was SQL_SUCCESS or SQL_SUCCESS_WITH_INFO.

  @param call    The function call
*/
#define ok_install(call) \
do { \
  BOOL _rc= (call); \
  print_diag_installer(_rc, #call, __FILE__, __LINE__); \
  if (!_rc) \
    TEST_RETURN_FAIL; \
} while (0)


/**
  Verify that a Boolean expression is true.
  It's recommended to use is_num, is_str, etc macros instead of "is(a==b)",
  since those will show values being compared, in a log.

  @param a     The expression to check
*/
#define is(a) \
do { \
  if (!(a)) { \
    printf("# !(%s) in %s on line %d\n", \
           #a, __FILE__, __LINE__); \
    TEST_RETURN_FAIL; \
  } \
} while (0);


#define myassert(a) is(a)
#define my_assert(a) is(a)


/**
  Verify that a string (char *) matches an expected value.

  @param a     The string to compare
  @param b     The string to compare against
  @param c     The number of characters to compare
*/
#define is_str(a, b, c) \
do { \
  char *val_a= (char *)(a), *val_b= (char *)(b); \
  int val_len= (int)(c) == SQL_NTS ? (int)strlen(val_a) : (int)(c); \
  if (strncmp(val_a, val_b, val_len) != 0) { \
    printf("# %s ('%*s') != '%*s' in %s on line %d\n", \
           #a, val_len, val_a, val_len, val_b, __FILE__, __LINE__); \
    TEST_RETURN_FAIL; \
  } \
} while (0);


/**
  Verify that a wide string (wchar_t *) matches an expected value.

  @param a     The string to compare
  @param b     The string to compare against
  @param c     The number of characters to compare
*/
#define is_wstr(a, b, c) \
do { \
  SQLWCHAR *val_a= (a), *val_b= (b); \
  int val_len= (int)(c); \
  if (memcmp(val_a, val_b, val_len * sizeof(SQLWCHAR)) != 0) { \
    printf("# %s ('%*ls') != '%*ls' in %s on line %d\n", \
           #a, val_len, val_a, val_len, val_b, __FILE__, __LINE__); \
    TEST_RETURN_FAIL; \
  } \
} while (0);


/**
  Verify that a number (long integer) matches an expected value.

  @param a     The number to compare
  @param b     The number to compare against
*/
#define is_num(a, b) \
do { \
  long long a1= (long long)(a), a2= (long long)(b); \
  if (a1 != a2) { \
    printf("# %s (%lld) != %lld in %s on line %d\n", \
           #a, a1, a2, __FILE__, __LINE__); \
    TEST_RETURN_FAIL; \
  } \
} while (0)


/* convenience macro for using statement */
#define check_sqlstate(stmt, sqlstate) \
  check_sqlstate_ex((stmt), SQL_HANDLE_STMT, (sqlstate))


int check_sqlstate_ex(SQLHANDLE hnd, SQLSMALLINT hndtype, char *sqlstate)
{
  SQLCHAR     sql_state[6];
  SQLINTEGER  err_code= 0;
  SQLCHAR     err_msg[SQL_MAX_MESSAGE_LENGTH]= {0};
  SQLSMALLINT err_len= 0;

  memset(err_msg, 'C', SQL_MAX_MESSAGE_LENGTH);
  SQLGetDiagRec(hndtype, hnd, 1, sql_state, &err_code, err_msg,
                SQL_MAX_MESSAGE_LENGTH - 1, &err_len);

  is_str(sql_state, (SQLCHAR *)sqlstate, 5);

  return OK;
}


int check_errmsg_ex(SQLHANDLE hnd, SQLSMALLINT hndtype, const char *msg)
{
  SQLCHAR     sql_state[6];
  SQLINTEGER  err_code= 0;
  SQLCHAR     err_msg[SQL_MAX_MESSAGE_LENGTH]= {0};
  SQLSMALLINT err_len= 0;

  memset(err_msg, 'C', SQL_MAX_MESSAGE_LENGTH);
  SQLGetDiagRec(hndtype, hnd, 1, sql_state, &err_code, err_msg,
                SQL_MAX_MESSAGE_LENGTH - 1, &err_len);

  is(strstr((char*)err_msg, msg) != NULL);

  return OK;
}


/**
  Print error and diagnostic information for ODBC API functions that did not
  finish with SQL_SUCCESS(_WITH_INFO) result
*/
static void print_diag(SQLRETURN rc, SQLSMALLINT htype, SQLHANDLE handle,
           const char *text, const char *file, int line)
{
  if (rc != SQL_SUCCESS)
  {
    SQLCHAR     sqlstate[6], message[SQL_MAX_MESSAGE_LENGTH];
    SQLINTEGER  native_error;
    SQLSMALLINT length;
    SQLRETURN   drc;

    /** @todo map rc to SQL_SUCCESS_WITH_INFO, etc */
    printf("# %s = %d\n", text, rc);

    /** @todo Handle multiple diagnostic records. */
    drc= SQLGetDiagRec(htype, handle, 1, sqlstate, &native_error,
                       message, SQL_MAX_MESSAGE_LENGTH - 1, &length);

    if (SQL_IS_SUCCESS(drc))
      printf("# [%6s] %*s in %s on line %d\n",
             sqlstate, length, message, file, line);
    else
      printf("# Did not get expected diagnostics from SQLGetDiagRec() = %d"
             " in file %s on line %d\n", drc, file, line);
  }
}


/**
  Print error and diagnostic information for ODBC INSTALLER API functions
  that did not return TRUE (1)
*/
static void print_diag_installer(BOOL is_success, const char *text,
                                 const char *file, int line)
{
  if (!is_success)
  {
    DWORD  error_code;
    WORD length;
    char     message[SQL_MAX_MESSAGE_LENGTH];
    SQLRETURN   drc;
    drc= SQLInstallerError(1, &error_code, message, SQL_MAX_MESSAGE_LENGTH - 1, &length);
    if (SQL_IS_SUCCESS(drc))
      printf("# [%s] %s in %s on line %d\n",
             text, message, file, line);
    else
      printf("# Did not get expected diagnostics from SQLInstallerError() = %d"
             " in file %s on line %d\n", drc, file, line);
  }
}


/* UTILITY MACROS */
#define myenv(henv,r)  \
  do { \
    print_diag(r, SQL_HANDLE_ENV, (henv), "myenv(henv,r)", \
               __FILE__, __LINE__); \
    if (r != SQL_SUCCESS && r != SQL_SUCCESS_WITH_INFO) \
      return FAIL; \
  } while (0)

#define myenv_r(henv,r)  \
  do { \
    print_diag(r, SQL_HANDLE_ENV, (henv), "myenv(henv_r,r)", \
               __FILE__, __LINE__); \
    if (r != SQL_ERROR) \
      return FAIL; \
  } while (0)

#define myenv_err(henv,r,rc)  \
  do { \
    print_diag(rc, SQL_HANDLE_ENV, (henv), "myenv_err(henv,r)",\
               __FILE__, __LINE__); \
    if (!r) \
      return FAIL; \
  } while (0)

#define mycon(hdbc,r)  \
  do { \
    print_diag(r, SQL_HANDLE_DBC, (hdbc), "mycon(hdbc,r)", \
               __FILE__, __LINE__); \
    if (r != SQL_SUCCESS && r != SQL_SUCCESS_WITH_INFO) \
      return FAIL; \
  } while (0)

#define mycon_r(hdbc,r)  \
  do { \
    print_diag(r, SQL_HANDLE_DBC, (hdbc), "mycon_r(hdbc,r)", \
               __FILE__, __LINE__); \
    if (r != SQL_ERROR) \
      return FAIL; \
  } while (0)

#define mycon_err(hdbc,r,rc)  \
  do { \
    print_diag(rc, SQL_HANDLE_DBC, (hdbc), "mycon_err(hdbc,r)", \
               __FILE__, __LINE__); \
    if (!r) \
      return FAIL; \
  } while (0)

#define mystmt(hstmt,r)  \
  do { \
    print_diag(r, SQL_HANDLE_STMT, (hstmt), "mystmt(hstmt,r)", \
               __FILE__, __LINE__); \
    if (r != SQL_SUCCESS && r != SQL_SUCCESS_WITH_INFO) \
      return FAIL; \
  } while (0)

#define mystmt_r(hstmt,r)  \
  do { \
    print_diag(r, SQL_HANDLE_STMT, (hstmt), "mystmt_r(hstmt,r)", \
               __FILE__, __LINE__); \
    if (r != SQL_ERROR) \
      return FAIL; \
  } while (0)

#define mystmt_err(hstmt,r,rc)  \
  do { \
    print_diag(rc, SQL_HANDLE_STMT, (hstmt), "mystmt_err(hstmt,r)", \
               __FILE__, __LINE__); \
    if (!r) \
      return FAIL; \
  } while (0)


/**
  Print resultset dashes
*/
int my_print_dashes(SQLHSTMT hstmt, SQLSMALLINT nCol)
{
    SQLLEN     disp_size, nullable;
    SQLCHAR    ColName[MAX_NAME_LEN+1];
    SQLUSMALLINT field_count, i, j;
    SQLSMALLINT  col_len;

    field_count= (SQLUSMALLINT)nCol;
    fprintf(stdout, "# ");
    fprintf(stdout, "+");

    for (i=1; i<= field_count; i++)
    {
        nullable = 0;
        ok_stmt(hstmt, SQLColAttribute(hstmt, i, SQL_DESC_BASE_COLUMN_NAME,
                                       &ColName, MAX_NAME_LEN, &col_len, NULL));
        ok_stmt(hstmt, SQLColAttribute(hstmt, i, SQL_DESC_DISPLAY_SIZE,
                                       NULL, 0, NULL, &disp_size));
        ok_stmt(hstmt, SQLColAttribute(hstmt, i, SQL_DESC_NULLABLE,
                                       NULL, 0, NULL, &nullable));

        if (disp_size < col_len)
            disp_size = col_len;
        if (disp_size < 4 && nullable)
            disp_size = 4;
        /*
          We cap disp_size to avoid problems when we have problems with
          it being very large, such as 64-bit issues in the driver.
        */
        disp_size= (disp_size > 512) ? 512 : disp_size;
        for (j=1; j < disp_size+3; j++)
          fprintf(stdout, "-");
        fprintf(stdout, "+");
    }
    fprintf(stdout, "\n");

    return OK;
}


static int my_print_data(SQLHSTMT hstmt, SQLUSMALLINT index,
                         SQLCHAR *data, SQLLEN length)
{
    SQLLEN     disp_size, nullable= 0;
    SQLCHAR    ColName[MAX_NAME_LEN+1];
    SQLSMALLINT col_len;

    nullable= 0;
    ok_stmt(hstmt, SQLColAttribute(hstmt, index, SQL_DESC_BASE_COLUMN_NAME,
                                   &ColName, MAX_NAME_LEN, &col_len, NULL));
    ok_stmt(hstmt, SQLColAttribute(hstmt, index, SQL_DESC_DISPLAY_SIZE,
                                   NULL, 0, NULL, &disp_size));
    ok_stmt(hstmt, SQLColAttribute(hstmt, index, SQL_DESC_NULLABLE,
                                   NULL, 0, NULL, &nullable));

    if (disp_size < length)
        disp_size = length;
    if (disp_size < col_len)
        disp_size = col_len;
    if (disp_size < 4 && nullable)
        disp_size = 4;
    /*
      We cap disp_size to avoid problems when we have problems with
      it being very large, such as 64-bit issues in the driver.
    */
    disp_size= (disp_size > 512) ? 512 : disp_size;
    if (length == SQL_NULL_DATA)
        fprintf(stdout, "%-*s  |", (int)disp_size, "NULL");
    else
        fprintf(stdout, "%-*s  |", (int)disp_size, data);

    return OK;
}


/* If we just return FAIL from my_print_non_format_result - it will
   considered as 1 row by caller */
#define mystmt_rows(hstmt,r, row)  \
  do { \
  print_diag(r, SQL_HANDLE_STMT, (hstmt), "mystmt_row(hstmt,r,row)", \
  __FILE__, __LINE__); \
  if (!SQL_IS_SUCCESS(r)) \
    return row; \
  } while (0)
/**
RESULT SET
*/
int my_print_non_format_result(SQLHSTMT hstmt)
{
    SQLRETURN   rc;
    SQLUINTEGER nRowCount=0;
    SQLULEN     pcColDef;
    SQLCHAR     szColName[MAX_NAME_LEN+1];
    SQLCHAR     szData[MAX_COLUMNS][MAX_ROW_DATA_LEN]={{0}};
    SQLSMALLINT nIndex,ncol= 0,pfSqlType, pcbScale, pfNullable;
    SQLLEN      ind_strlen;

    rc = SQLNumResultCols(hstmt,&ncol);

    mystmt_rows(hstmt,rc,-1);

    for (nIndex = 1; nIndex <= ncol; ++nIndex)
    {
        rc = SQLDescribeCol(hstmt,nIndex,szColName, MAX_NAME_LEN, NULL,
                            &pfSqlType,&pcColDef,&pcbScale,&pfNullable);
        /* Returning in case of an error -nIndex we will see in the log column# */
        mystmt_rows(hstmt,rc,-nIndex);


        fprintf(stdout, "%s\t", szColName);

        rc = SQLBindCol(hstmt,nIndex, SQL_C_CHAR, szData[nIndex-1],
                        MAX_ROW_DATA_LEN+1,&ind_strlen);
        mystmt_rows(hstmt,rc,-nIndex);
    }

    fprintf(stdout,"\n");

    rc = SQLFetch(hstmt);
    while (SQL_IS_SUCCESS(rc))
    {
        ++nRowCount;
        for (nIndex=0; nIndex< ncol; ++nIndex)
            fprintf(stdout, "%s\t", szData[nIndex]);

        fprintf(stdout, "\n");
        rc = SQLFetch(hstmt);
    }

    SQLFreeStmt(hstmt,SQL_UNBIND);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    fprintf(stdout, "# Total rows fetched: %d\n", (int)nRowCount);

    return nRowCount;
}

/**
  RESULT SET
*/
SQLINTEGER myresult(SQLHSTMT hstmt)
{
    SQLRETURN   rc;
    SQLUINTEGER nRowCount;
    SQLCHAR     ColName[MAX_NAME_LEN+1];
    SQLCHAR     Data[MAX_ROW_DATA_LEN+1];
    SQLLEN      pcbLength;
    SQLUSMALLINT nIndex;
    SQLSMALLINT  ncol;

    rc = SQLNumResultCols(hstmt,&ncol);
    mystmt(hstmt,rc);

    if (ncol > 10)
        return my_print_non_format_result(hstmt);

    if (my_print_dashes(hstmt, ncol) != OK)
      return -1;
    fprintf(stdout, "# |");

    for (nIndex = 1; nIndex <= ncol; ++nIndex)
    {
        ok_stmt(hstmt, SQLColAttribute(hstmt, nIndex, SQL_DESC_BASE_COLUMN_NAME,
                                       ColName, MAX_NAME_LEN, NULL, NULL));
        if (my_print_data(hstmt, nIndex, ColName, 0) != OK)
          return -1;
    }
    fprintf(stdout, "\n");
    if (my_print_dashes(hstmt, ncol) != OK)
      return -1;

    nRowCount= 0;

    rc = SQLFetch(hstmt);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
        nRowCount++;
        fprintf(stdout, "# |");

        for (nIndex=1; nIndex<= ncol; ++nIndex)
        {
            rc = SQLGetData(hstmt, nIndex, SQL_C_CHAR, Data,
                            MAX_ROW_DATA_LEN,&pcbLength);
            mystmt(hstmt,rc);
            if (my_print_data(hstmt, nIndex, Data, pcbLength) != OK)
              return -1;
        }
        fprintf(stdout,"\n");
        rc = SQLFetch(hstmt);
    }
    if (my_print_dashes(hstmt, ncol) != OK)
      return -1;
    SQLFreeStmt(hstmt,SQL_UNBIND);
    SQLFreeStmt(hstmt,SQL_CLOSE);

    fprintf(stdout, "# Total rows fetched: %d\n", (int)nRowCount);
    return nRowCount;
}

/**
  ROWCOUNT
*/
SQLUINTEGER myrowcount(SQLHSTMT hstmt)
{
    SQLRETURN   rc;
    SQLUINTEGER nRowCount= 0;

    rc = SQLFetch(hstmt);
    while (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
    {
        ++nRowCount;
        rc = SQLFetch(hstmt);
    }
    SQLFreeStmt(hstmt,SQL_UNBIND);
    fprintf(stdout, "# Total rows fetched: %d\n", (int)nRowCount);
    return(nRowCount);
}
/**
  SQLExecDirect
*/
SQLRETURN tmysql_exec(SQLHSTMT hstmt, char *sql_stmt)
{
    return(SQLExecDirect(hstmt,(SQLCHAR *)sql_stmt,SQL_NTS));
}
/**
  SQLPrepare
*/
SQLRETURN tmysql_prepare(SQLHSTMT hstmt, char *sql_stmt)
{
    return(SQLPrepare(hstmt, (SQLCHAR *)sql_stmt, SQL_NTS));
}


/**
  return integer data by fetching it
*/
SQLINTEGER my_fetch_int(SQLHSTMT hstmt, SQLUSMALLINT icol)
{
    SQLINTEGER nData;
    SQLLEN len;

    SQLGetData(hstmt, icol, SQL_INTEGER, &nData, 0, &len);
    if (len == SQL_NULL_DATA)
    {
      printMessage("my_fetch_int: NULL");
    }
    else
    {
      printMessage("my_fetch_int: %ld (%ld)", (long int)nData, len);
      return nData;
    }
    return 0;
}


/**
  return unsigned integer data by fetching it
*/
SQLUINTEGER my_fetch_uint(SQLHSTMT hstmt, SQLUSMALLINT icol)
{
    SQLUINTEGER nData;
    SQLLEN len;

    SQLGetData(hstmt, icol, SQL_C_ULONG, &nData, 0, &len);
    printMessage("my_fetch_uint: %lu (%ld)", (long unsigned int)nData, len);
    return (len != SQL_NULL_DATA) ? nData : 0;
}


/**
  return string data, by fetching it
*/
const char *my_fetch_str(SQLHSTMT hstmt, SQLCHAR *szData,SQLUSMALLINT icol)
{
    SQLLEN nLen;

    SQLGetData(hstmt,icol,SQL_CHAR,szData,MAX_ROW_DATA_LEN+1,&nLen);
    /* If Null value - putting down smth meaningful. also that allows caller to
       better/(in more easy way) test the value */
    if (nLen < 0)
    {
      strcpy((char*)szData, "(Null)");
    }
    printMessage(" my_fetch_str: %s(%ld)", szData, nLen);
    return((const char *)szData);
}


/**
  Convert a SQLWCHAR to wchar_t
*/
wchar_t *sqlwchar_to_wchar_t(SQLWCHAR *in)
{
  static wchar_t buff[2048];
  wchar_t *to= buff;

  if (sizeof(wchar_t) == sizeof(SQLWCHAR))
    return (wchar_t *)in;

  for ( ; *in && to < buff + sizeof(buff) - 2; in++)
    to+= utf16toutf32((UTF16 *)in, (UTF32 *)to);

  *to= L'\0';

  return buff;
}


/**
  return wide string data, by fetching it and possibly converting it to wchar_t
*/
wchar_t *my_fetch_wstr(SQLHSTMT hstmt, SQLWCHAR *buffer, SQLUSMALLINT icol)
{
  SQLRETURN rc;
  SQLLEN nLen;

  rc= SQLGetData(hstmt, icol, SQL_WCHAR, buffer, MAX_ROW_DATA_LEN + 1, &nLen);
  if (!SQL_IS_SUCCESS(rc))
    return L"";

  buffer[nLen/sizeof(SQLWCHAR)]= 0;

  return sqlwchar_to_wchar_t(buffer);
}


/*
  Check if driver supports SQLSetPos
*/
int driver_supports_setpos(SQLHDBC hdbc)
{
  SQLRETURN    rc;
  SQLUSMALLINT status= TRUE;

  rc = SQLGetFunctions(hdbc, SQL_API_SQLSETPOS, &status);
  mycon(hdbc,rc);

  if (!status)
    return FALSE;
  return TRUE;
}

/*
  Check for minimal MySQL version
*/
int mysql_min_version(SQLHDBC hdbc, char *min_version, unsigned int length)
{
  char server_version[MYSQL_NAME_LEN+1];
  SQLRETURN rc;

  rc = SQLGetInfo(hdbc,SQL_DBMS_VER,server_version,MYSQL_NAME_LEN,NULL);
  mycon(hdbc, rc);

  {
    unsigned int major1= 0, major2= 0, minor1= 0, minor2= 0, build1= 0, build2= 0;

    sscanf(server_version, "%u.%u.%u", &major1, &minor1, &build1);
    sscanf(min_version, "%u.%u.%u", &major2, &minor2, &build2);

    if ( major1 > major2 ||
      major1 == major2 && (minor1 > minor2 ||
                          minor1 ==  minor2 && build1 >= build2))
    {
      return TRUE;
    }
  }

  return FALSE;;
}

/*
  Check if server supports transactions
*/
int server_supports_trans(SQLHDBC hdbc)
{
  SQLSMALLINT trans;
  SQLRETURN   rc;

  rc = SQLGetInfo(hdbc,SQL_TXN_CAPABLE,&trans,0,NULL);
  mycon(hdbc,rc);

  if (trans != SQL_TC_NONE)
      return TRUE;
  return FALSE;
}


/**
  DRV CONNECTION
*/
int mydrvconnect(SQLHENV *henv, SQLHDBC *hdbc, SQLHSTMT *hstmt, SQLCHAR *connIn)
{
  SQLCHAR   connOut[MAX_NAME_LEN+1];
  SQLSMALLINT len;

  ok_env(*henv, SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, henv));

  ok_env(*henv, SQLSetEnvAttr(*henv, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)SQL_OV_ODBC3, 0));

  ok_env(*henv, SQLAllocHandle(SQL_HANDLE_DBC, *henv,  hdbc));

  ok_con(*hdbc, SQLDriverConnect(*hdbc, NULL, connIn, SQL_NTS, connOut,
                                 MAX_NAME_LEN, &len, SQL_DRIVER_NOPROMPT));

  ok_con(*hdbc, SQLSetConnectAttr(*hdbc, SQL_ATTR_AUTOCOMMIT,
                                  (SQLPOINTER)SQL_AUTOCOMMIT_ON, 0));

  ok_con(*hdbc, SQLAllocHandle(SQL_HANDLE_STMT, *hdbc, hstmt));

  return OK;
}


/*
  Helper function to make the connection string.
*/
SQLCHAR *make_conn_str(const SQLCHAR *dsn, const SQLCHAR *uid,
                       const SQLCHAR *pwd, const SQLCHAR *db,
                       const SQLCHAR *options, int hide_password)
{
  static SQLCHAR connIn[4096]= {0};
  SQLCHAR dsn_buf[MAX_NAME_LEN]= {0};
  SQLCHAR socket_buf[MAX_NAME_LEN]= {0};
  SQLCHAR server_buf[MAX_NAME_LEN] = {0};
  /* ";database="+ we make buffer bigger for one certain test */
  SQLCHAR     db_buf[4096]= {0};

  /* Should fit 8 byte + ";port=" */
  SQLCHAR     port_buf[32]= {0};
  BOOL skip_socket = 0;

  /* We never set the custom DSN, but sometimes use DRIVER instead */
  if (dsn == NULL)
    snprintf((char *)dsn_buf, sizeof(dsn_buf), "DSN=%s", (char *)mydsn);
  else if (dsn == (const SQLCHAR*)USE_DRIVER)
    snprintf((char *)dsn_buf, sizeof(dsn_buf), "DRIVER=%s", (char *)mydriver);
  else
    snprintf((char *)dsn_buf, sizeof(dsn_buf), "DSN=%s", (char *)dsn);

  if (uid     == NULL) uid=     myuid;
  if (pwd     == NULL) pwd=     mypwd;
  if (db      == NULL) db=      mydb;
  if (options == NULL) options= my_str_options;

  if (hide_password)
    pwd = (SQLCHAR*)"*************";

  snprintf((char *)connIn, sizeof(connIn), "%s;UID=%s;PWD=%s;OPTION=%d;",
          (char *)dsn_buf, (char *)uid, (char *)pwd, myoption);

  if (strstr((const char*)options, "SOCKET=;") &&
      strcmp((const char*)myserver, "localhost") == 0)
  {
    skip_socket = 1;
    char *port = NULL;
    if ((port = getenv("TEST_PORT")) ||
        (port = getenv("MYSQL_PORT")))
    {
      myport = atoi(port);
    }
    strncat((char *)connIn, (char*)";SERVER=127.0.0.1;", sizeof(connIn) - 1);
  }
  else
  {
    snprintf((char *)server_buf, sizeof(server_buf), ";SERVER=%s", myserver);
    strncat((char *)connIn, (char *)server_buf, sizeof(connIn) - 1);
  }

  if (mysock && mysock[0] && skip_socket == 0)
  {
    snprintf((char *)socket_buf, sizeof(socket_buf), ";SOCKET=%s", (char *)mysock);
    strncat((char *)connIn, (char*)socket_buf, sizeof(connIn) - 1);
  }
  if (db && db[0])
  {
    snprintf((char *)db_buf, sizeof(db_buf), ";DATABASE=%s", (char *)db);
    strncat((char *)connIn, (char *)db_buf, sizeof(connIn) - 1);
  }
  if (myport)
  {
    snprintf((char*)port_buf, sizeof(port_buf), ";PORT=%d", myport);
    strncat((char *)connIn, (char*)port_buf, sizeof(connIn) - 1);
  }

  if (options != NULL && options[0] > 0)
  {
    strncat((char*)connIn, ";", sizeof(connIn));
    strncat((char*)connIn, (char*)options, sizeof(connIn) - 1);
  }

#if MYSQL_VERSION_ID >= 50507
  if (init_auth_plugin)
  {
    init_auth_plugin= 0; /* reset the plugin init flag */
    if (myauth && myauth[0])
    {
      strncat((char *)connIn, ";DEFAULTAUTH=", sizeof(connIn));
      strncat((char *)connIn, (char *)myauth, sizeof(connIn) - 1);
    }
    if (myplugindir && myplugindir[0])
    {
      strncat((char *)connIn, ";PLUGINDIR=", sizeof(connIn));
      strncat((char *)connIn, (char *)myplugindir, sizeof(connIn) - 1);
    }
  }
#endif
  return connIn;
}


/* Helper function for tests to get (additional) connection
   If dsn, uid, pwd or options is null - they defualt to mydsn, myuid, mypwd
   and my_str_options, respectively.
   myoption, mysock and myport values are used. */
int get_connection(SQLHDBC *hdbc, const SQLCHAR *dsn, const SQLCHAR *uid,
                   const SQLCHAR *pwd, const SQLCHAR *db,
                   const SQLCHAR *options)
{
  /* Buffers have to be large enough to contain SSL options and long names */
  SQLCHAR     connOut[4096];
  SQLSMALLINT len;
  SQLRETURN   rc;
  SQLCHAR     driver_name[16]; /* Should be enough for myodbc library file name */
  SQLCHAR     *connIn;

  connIn = make_conn_str(dsn, uid, pwd, db, options, 0);
  rc= SQLDriverConnect(*hdbc, NULL, connIn, SQL_NTS, connOut,
                       MAX_NAME_LEN, &len, SQL_DRIVER_NOPROMPT);

  if (!SQL_IS_SUCCESS(rc))
  {
    /* re-build and print the connection string with hidden password */
    printf("# Connection failed with the following Connection string: " \
           "\n%s\n", (char*)make_conn_str(dsn, uid, pwd, db, options, 1));
    return rc;
  }

  /* Let's neglect possibility that error returned by get_connection() can be
     in fact error of turning autocommit on */
  rc= SQLSetConnectAttr(*hdbc, SQL_ATTR_AUTOCOMMIT,
                                  (SQLPOINTER)SQL_AUTOCOMMIT_ON, 0);
  if (!SQL_IS_SUCCESS(rc))
  {
    return rc;
  }

  if (unicode_driver < 0)
  {
    rc= SQLGetInfo(*hdbc, SQL_DRIVER_NAME, driver_name, sizeof(driver_name), NULL);

    if (SQL_IS_SUCCESS(rc))
    {
      /* ANSI driver file name contains 5a.{dll|so} */
      unicode_driver= strstr((char*)driver_name, "a.") == NULL ? 1 : 0;
    }
  }

  return rc;
}


int alloc_basic_handles_with_opt(SQLHENV *henv, SQLHDBC *hdbc,
                                 SQLHSTMT *hstmt,  const SQLCHAR *dsn,
                                 const SQLCHAR *uid, const SQLCHAR *pwd,
                                 const SQLCHAR *db, const SQLCHAR *options)
{
  if (myenable_pooling)
  {
    SQLSetEnvAttr(NULL, SQL_ATTR_CONNECTION_POOLING,
                 (SQLPOINTER)SQL_CP_ONE_PER_DRIVER, 0);
  }

  ok_env(*henv, SQLAllocHandle(SQL_HANDLE_ENV, SQL_NULL_HANDLE, henv));

  ok_env(*henv, SQLSetEnvAttr(*henv, SQL_ATTR_ODBC_VERSION,
                              (SQLPOINTER)SQL_OV_ODBC3, 0));
  if (myenable_pooling)
  {
    SQLRETURN rc= SQLSetEnvAttr(*henv, SQL_ATTR_CP_MATCH, (SQLPOINTER)SQL_CP_STRICT_MATCH, 0);

    if( !SQL_IS_SUCCESS(rc))
    {
      /* No DM case? */
      is_num(check_sqlstate_ex(*henv, SQL_HANDLE_ENV, "HYC00"), OK);
    }
  }

  ok_env(*henv, SQLAllocHandle(SQL_HANDLE_DBC, *henv, hdbc));

  /* ok_con(*hdbc, SQLConnect(*hdbc, mydsn, strlen(mydsn), myuid, strlen(myuid), mypwd, strlen(mypwd))); */
  ok_con(*hdbc, get_connection(hdbc, dsn, uid, pwd, db, options));

  ok_con(*hdbc, SQLAllocHandle(SQL_HANDLE_STMT, *hdbc, hstmt));

  return OK;
}


int alloc_basic_handles(SQLHENV *henv, SQLHDBC *hdbc, SQLHSTMT *hstmt)
{
  return alloc_basic_handles_with_opt(henv, hdbc, hstmt, (SQLCHAR *)mydsn,
                                      (SQLCHAR *)myuid, (SQLCHAR *)mypwd,
                                      (SQLCHAR *)mydb,
                                      (SQLCHAR *)my_str_options);
}


int free_basic_handles(SQLHENV *henv,SQLHDBC *hdbc, SQLHSTMT *hstmt)
{

  if(hstmt && *hstmt)
  {
    ok_stmt(*hstmt, SQLFreeStmt(*hstmt, SQL_DROP));
    *hstmt = NULL;
  }

  if(hdbc && *hdbc)
  {
    /* We don't care if this succeeds, the connection may have gone away. */
    (void)SQLEndTran(SQL_HANDLE_DBC, *hdbc, SQL_COMMIT);
    ok_con(*hdbc, SQLDisconnect(*hdbc));
    ok_con(*hdbc, SQLFreeConnect(*hdbc));
    *hdbc = NULL;
  }

  if(henv && *henv)
  {
    ok_env(*henv, SQLFreeEnv(*henv));
    *henv = NULL;
  }

  return OK;
}


/*
  Allocate size bytes and return a pointer
  to allocated memory with storing in
  garbage collector
*/
void *gc_alloc(size_t len)
{
  if (gc_blk.counter >= MAX_MEM_BLOCK_ELEMENTS || len <= 0)
  {
    printf("# GC Memory reached maximum limit counter: %d " \
        "or length:%d less then equal to 0 in %s on line %d\n",
        gc_blk.counter, (int)len, __FILE__, __LINE__); \
    return NULL;
  }

  gc_blk.blk[gc_blk.counter]= malloc(len);
  return gc_blk.blk[gc_blk.counter++];
}


/*
  Free allocated memory collected by
  garbage collector
*/
int mem_gc_flush()
{
  int i= 0;
  if (gc_blk.counter <= 0)
  {
    return FAIL;
  }

  while (i < gc_blk.counter)
  {
    free(gc_blk.blk[i]);
    gc_blk.blk[i++]= 0;
  }

  return OK;
}


/**
 Helper for possibly converting a (wchar_t *) to a (SQLWCHAR *).
 TODO: [almost?] all uses of those macros in tests lead to memore leak,
       as resulting pointer is not remembered and thus memory never freed
*/
#define W(string) dup_wchar_t_as_sqlwchar((string), \
                                          sizeof(string) / sizeof(wchar_t))
#define WL(string, len) dup_wchar_t_as_sqlwchar((string), (len))


/**
  Convert a wchar_t * to a SQLWCHAR * if a wchar_t is not the same as
  SQLWCHAR. New space is allocated and never freed. Because this is used in
  short-lived test programs, this is okay, if not ideal.
*/
SQLWCHAR *dup_wchar_t_as_sqlwchar(wchar_t *from, size_t len)
{
  if (sizeof(wchar_t) == sizeof(SQLWCHAR))
  {
    SQLWCHAR *to= (SQLWCHAR*)gc_alloc(len * sizeof(SQLWCHAR));
    memcpy(to, from, len * sizeof(wchar_t));
    return to;
  }
  else
  {
    size_t i;
    SQLWCHAR *to= (SQLWCHAR*)gc_alloc(2 * len * sizeof(SQLWCHAR));
    SQLWCHAR *out= to;
    for (i= 0; i < len; i++)
      to+= utf32toutf16((UTF32)from[i], (UTF16 *)to);
    *to= 0;
    return out;
  }
}


/**
 Helper for converting a (char *) to a (SQLWCHAR *)
*/
#define WC(string) dup_char_as_sqlwchar((string))


/**
  Convert a char * to a SQLWCHAR *. New space is allocated and never freed.
  Because this is used in short-lived test programs, this is okay, if not
  ideal.
*/
SQLWCHAR *dup_char_as_sqlwchar(SQLCHAR *from)
{
  SQLWCHAR *to= (SQLWCHAR*)gc_alloc((strlen((char *)from) + 1) * sizeof(SQLWCHAR));
  SQLWCHAR *out= to;
  while (from && *from)
    *(to++)= (SQLWCHAR)*(from++);
  *to= 0;
  return out;
}


/**
  Check if we are using a driver manager for testing.

  @param[in] hdbc  Connection handle

  @return 0 if the connection is using a driver manager, 1 if not.
*/
int using_dm(HDBC hdbc)
{
  SQLCHAR val[20];
  SQLSMALLINT len;

  if (SQLGetInfo(hdbc, SQL_DM_VER, val, sizeof(val), &len) == SQL_ERROR)
    return 0;

  return 1;
}

/*
  Check if we are using the unixODBC version specified
*/
int using_unixodbc_version(SQLHANDLE henv, SQLCHAR *ver)
{
#ifdef SQL_ATTR_UNIXODBC_VERSION
  SQLCHAR buf[10];
  if(SQLGetEnvAttr(henv, SQL_ATTR_UNIXODBC_VERSION, buf, 10, NULL) != SQL_SUCCESS)
    return 0;
  if(!strcmp((char*)buf, (char*)ver))
    return 1;
#endif /* SQL_ATTR_UNIXODBC_VERSION */
  return 0;
}

