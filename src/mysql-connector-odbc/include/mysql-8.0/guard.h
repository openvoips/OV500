#ifndef INCLUDE_GUARD_H_
#define INCLUDE_GUARD_H_
/* Copyright (c) 2019, 2025, Oracle and/or its affiliates.

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


/**
  @file guard.h RAII classes for locking/unlocking, inspired by the guard
  class of Boost fame.
*/

#include <mysql/psi/mysql_mutex.h>
#include <mysql/psi/mysql_rwlock.h>
#include <mysql/psi/mysql_thread.h>

/// Guards a mutex.
class Mutex_guard {
 public:
  Mutex_guard(const Mutex_guard &lock) = delete;

  /**
    Object construction that locks specified mutex.

    @param mutex Mutex pointer to be locked.
  */
  explicit Mutex_guard(mysql_mutex_t *mutex) : m_mutex(mutex) {
    mysql_mutex_lock(m_mutex);
  }

  /// Destroys object and unlocks the mutex.
  ~Mutex_guard() { mysql_mutex_unlock(m_mutex); }

 private:
  mysql_mutex_t *m_mutex;
};

#endif  // INCLUDE_GUARD_H_
