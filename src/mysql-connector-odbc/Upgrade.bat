@ECHO OFF
REM Copyright (c) 2006, 2024, Oracle and/or its affiliates.
REM
REM This program is free software; you can redistribute it and/or modify
REM it under the terms of the GNU General Public License, version 2.0, as
REM published by the Free Software Foundation.
REM
REM This program is designed to work with certain software (including
REM but not limited to OpenSSL) that is licensed under separate terms, as
REM designated in a particular file or component or in included license
REM documentation. The authors of MySQL hereby grant you an additional
REM permission to link the program and your derivative works with the
REM separately licensed software that they have either included with
REM the program or referenced in the documentation.
REM
REM Without limiting anything contained in the foregoing, this file,
REM which is part of Connector/ODBC, is also subject to the
REM Universal FOSS Exception, version 1.0, a copy of which can be found at
REM https://oss.oracle.com/licenses/universal-foss-exception.
REM
REM This program is distributed in the hope that it will be useful, but
REM WITHOUT ANY WARRANTY; without even the implied warranty of
REM MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
REM See the GNU General Public License, version 2.0, for more details.
REM
REM You should have received a copy of the GNU General Public License
REM along with this program; if not, write to the Free Software Foundation, Inc.,
REM 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

REM #########################################################
REM 
REM \brief  Upgrade an existing install.
REM
REM         Often when testing you want to Uninstall and Install. This
REM         script does this.
REM
REM         Use this upgrade an existing install. This just
REM         calls Uninstall/Install so it has nothing to do
REM         with MS installed software thingy in Control Panel.
REM
REM \sa     README.win
REM
REM #########################################################

CALL Uninstall.bat %1
CALL Install.bat %1

