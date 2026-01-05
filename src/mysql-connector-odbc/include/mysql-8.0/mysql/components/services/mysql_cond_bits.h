/* Copyright (c) 2008, 2025, Oracle and/or its affiliates.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2.0,
as published by the Free Software Foundation.

This program is designed to work with certain software (including
but not limited to OpenSSL) that is licensed under separate terms,
as designated in a particular file or component or in included license
documentation.  The authors of MySQL hereby grant you an additional
permission to link the program and your derivative works with the
separately licensed software that they have either included with
the program or referenced in the documentation.

Without limiting anything contained in the foregoing, this file,
which is part of ODBC Driver for MySQL (Connector/ODBC), is also subject to the
Universal FOSS Exception, version 1.0, a copy of which can be found at
http://oss.oracle.com/licenses/universal-foss-exception.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License, version 2.0, for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301  USA */

#ifndef COMPONENTS_SERVICES_MYSQL_COND_BITS_H
#define COMPONENTS_SERVICES_MYSQL_COND_BITS_H

/**
  @file
  Instrumentation helpers for conditions.
*/

#include "mysql/components/services/thr_cond_bits.h"

/**
  @defgroup psi_api_cond Cond Instrumentation (API)
  @ingroup psi_api
  @{
*/

/**
  An instrumented cond structure.
  @c mysql_cond_t is a drop-in replacement for @c native_cond_t.
  @sa mysql_cond_init
  @sa mysql_cond_wait
  @sa mysql_cond_timedwait
  @sa mysql_cond_signal
  @sa mysql_cond_broadcast
  @sa mysql_cond_destroy
*/
struct mysql_cond_t {
  /** The real condition */
  native_cond_t m_cond;
  /**
    The instrumentation hook.
    Note that this hook is not conditionally defined,
    for binary compatibility of the @c mysql_cond_t interface.
  */
  struct PSI_cond *m_psi;
};

/** @} (end of group psi_api_cond) */

#endif /* COMPONENTS_SERVICES_MYSQL_COND_BITS_H */
