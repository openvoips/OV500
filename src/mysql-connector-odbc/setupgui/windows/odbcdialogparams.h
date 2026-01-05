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

#ifndef __ODBCPARAMS_H__
#define __ODBCPARAMS_H__

#ifdef __cplusplus
extern "C" {
#endif

// four callbacks:
// called when [Help] pressed
typedef void HelpButtonPressedCallbackType(HWND dialog);
// called when [Test] pressed - show any messages to user here
//typedef const wchar_t * TestButtonPressedCallbackType(HWND dialog, DataSource* params);
// called when [OK] pressed - show errors here (if any) and return false to prevent dialog close
typedef BOOL AcceptParamsCallbackType(HWND dialog, DataSource* params);
// called when DataBase combobox Drops Down
//typedef const WCHAR** DatabaseNamesCallbackType(HWND dialog, DataSource* params);


#ifdef __cplusplus
}
#endif

#endif

