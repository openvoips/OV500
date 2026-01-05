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

#ifndef MYODBC_ODBC_H
#define MYODBC_ODBC_H

#ifndef ODBCVER
# define ODBCVER 0x0380
#endif

#ifdef _UNIX_
# include <ctype.h>
# ifdef HAVE_LIBDL
#  include <dlfcn.h>
# endif
# include <sql.h>
# include <sqlext.h>
# ifdef USE_IODBC
#  include <iodbcinst.h>
# else
#  include <odbcinst.h>
# endif

# ifndef SYSTEM_ODBC_INI
#  define BOTH_ODBC_INI ODBC_BOTH_DSN
#  define USER_ODBC_INI ODBC_USER_DSN
#  define SYSTEM_ODBC_INI ODBC_SYSTEM_DSN
# endif

/* If SQL_API is not defined, define it, unixODBC doesn't have this */
# if !defined(SQL_API)
#  define SQL_API
# endif
#else
# include <windows.h>
# ifndef RC_INVOKED
#  pragma pack(1)
# endif

# include <sql.h>
# include <sqlext.h>
# include <odbcinst.h>
#endif

#endif /* !MYODBC_ODBC_H */
