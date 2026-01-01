/* Copyright (c) 2016, 2025, Oracle and/or its affiliates.

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


#ifndef SCOPE_GUARD_H
#define SCOPE_GUARD_H

template <typename TLambda>
class Scope_guard {
 public:
  Scope_guard(const TLambda &rollback_lambda)
      : m_committed(false), m_rollback_lambda(rollback_lambda) {}
  Scope_guard(const Scope_guard<TLambda> &) = delete;
  Scope_guard(Scope_guard<TLambda> &&moved)
      : m_committed(moved.m_committed),
        m_rollback_lambda(moved.m_rollback_lambda) {
    /* Set moved guard to "invalid" state, the one in which the rollback lambda
      will not be executed. */
    moved.m_committed = true;
  }
  ~Scope_guard() {
    if (!m_committed) {
      m_rollback_lambda();
    }
  }

  inline void commit() { m_committed = true; }

  inline void rollback() {
    if (!m_committed) {
      m_rollback_lambda();
      m_committed = true;
    }
  }

 private:
  bool m_committed;
  const TLambda m_rollback_lambda;
};

template <typename TLambda>
Scope_guard<TLambda> create_scope_guard(const TLambda rollback_lambda) {
  return Scope_guard<TLambda>(rollback_lambda);
}

#endif /* SCOPE_GUARD_H */
