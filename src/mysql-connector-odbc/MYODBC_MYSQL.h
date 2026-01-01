// Copyright (c) 2009, 2024, Oracle and/or its affiliates.
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

#ifndef MYODBC_MYSQL_H
#define MYODBC_MYSQL_H

#define DONT_DEFINE_VOID

#define my_bool bool
#define mysys_end my_end
#define TRUE 1
#define FALSE 0

#define WIN32_LEAN_AND_MEAN

#if (MYSQLCLIENT_STATIC_LINKING)
#include <my_config.h>
#include <my_sys.h>
#include <mysql.h>
#include <mysqld_error.h>
#include <my_alloc.h>
#include <mysql/service_mysql_alloc.h>
#include <m_ctype.h>
#include <my_io.h>

#else

#include "include/mysql-8.0/my_config.h"
#include "include/mysql-8.0/my_sys.h"
#include <mysql.h>
#include <mysqld_error.h>
#include "include/mysql-8.0/my_alloc.h"
#include "include/mysql-8.0/mysql/service_mysql_alloc.h"
#include "include/mysql-8.0/m_ctype.h"
#include "include/mysql-8.0/my_io.h"

#endif

#ifdef __cplusplus
extern "C"
{
#endif

#define PSI_NOT_INSTRUMENTED 0

#define MIN_MYSQL_VERSION 40100L
#if MYSQL_VERSION_ID < MIN_MYSQL_VERSION
# error "Connector/ODBC requires v4.1 (or later) of the MySQL client library"
#endif

#if !MYSQLCLIENT_STATIC_LINKING || MYSQL_VERSION_ID >= 80018

  /*
    Note: Things no longer defined in client library headers, but still used
    by ODBC code.
  */

  #define set_if_bigger(a, b)   \
    do {                        \
      if ((a) < (b)) (a) = (b); \
    } while (0)

  #define set_if_smaller(a, b)  \
    do {                        \
      if ((a) > (b)) (a) = (b); \
    } while (0)

#endif


#define my_sys_init my_init
#define x_free(A) { void *tmp= (A); if (tmp) free((char *) tmp); }
#define myodbc_malloc(A, B) (B == MY_ZEROFILL ? calloc(A, 1) : malloc(A))
#define myodbc_realloc(A, B) realloc(A, B)
#define myodbc_snprintf snprintf


/* Get rid of defines from my_config.h that conflict with our myconf.h */
#ifdef VERSION
# undef VERSION
#endif
#ifdef PACKAGE
# undef PACKAGE
#endif

#ifdef HAVE_LIBCRYPT
#undef HAVE_LIBCRYPT
#endif

#ifdef PACKAGE_BUGREPORT
#undef PACKAGE_BUGREPORT
#endif

#ifdef PACKAGE_NAME
#undef PACKAGE_NAME
#endif

#ifdef PACKAGE_STRING
#undef PACKAGE_STRING
#endif

#ifdef PACKAGE_TARNAME
#undef PACKAGE_TARNAME
#endif

#ifdef PACKAGE_VERSION
#undef PACKAGE_VERSION
#endif


/*
  It doesn't matter to us what SIZEOF_LONG means to MySQL's headers, but its
  value matters a great deal to unixODBC, which calculates it differently.

  This causes problems where an application linked against unixODBC thinks
  SIZEOF_LONG == 4, and the driver was compiled thinking SIZEOF_LONG == 8,
  such as on Solaris x86_64 using Sun C 5.8.

  This stems from unixODBC's use of silly platform macros to guess
  SIZEOF_LONG instead of just using sizeof(long).
*/
#ifdef SIZEOF_LONG
# undef SIZEOF_LONG
#endif

#ifdef __cplusplus
}
#endif

#endif

