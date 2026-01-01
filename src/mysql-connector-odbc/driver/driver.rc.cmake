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

/////////////////////////////////////////////////////////////////////////////
// \brief       Resource file for MS Windows builds.
//
// \note        Hand crafted - do not overwrite and save to source repo!
//

// @EDIT_WARNING_MESSAGE@

#include <windows.h>

/////////////////////////////////////////////////////////////////////////////
//
// \brief       English (U.S.) resources
//
#if !defined(AFX_RESOURCE_DLL) || defined(AFX_TARG_ENU)
LANGUAGE LANG_ENGLISH, SUBLANG_ENGLISH_US
#pragma code_page(1252)
MYSQL_LOGO              BITMAP  DISCARDABLE     "..\mysql.bmp"
#endif    // English (U.S.) resources

/////////////////////////////////////////////////////////////////////////////
//
// \brief       Version information
//
// \note        We actually share our version with others (ie setup lib) so
//              use the common VersionInfo.h file.
//
#include "..\VersionInfo.h"
VS_VERSION_INFO VERSIONINFO
  FILEVERSION MYODBC_FILEVER
  PRODUCTVERSION MYODBC_PRODUCTVER
  FILEFLAGSMASK 0x3L
#ifdef _DEBUG
 FILEFLAGS 0x29L
#else
 FILEFLAGS 0x28L
#endif
 FILEOS 0x40004L
 FILETYPE 0x2L
 FILESUBTYPE 0x0L
BEGIN
    BLOCK "StringFileInfo"
    BEGIN
        BLOCK "040904e4"
        BEGIN
            VALUE "Comments", "provides core driver functionality\0"
            VALUE "CompanyName", "Oracle Corporation\0"
	          VALUE "FileDescription", "MySQL ODBC @CONNECTOR_MAJOR@.@CONNECTOR_MINOR@ @DRIVER_TYPE@ Driver\0"
            VALUE "FileVersion", MYODBC_STRFILEVER
            VALUE "InternalName", "myodbc8@CONNECTOR_DRIVER_TYPE_SHORT@\0"
            VALUE "LegalCopyright", "Copyright (c) 1995, 2018, Oracle and/or its affiliates.\0"
            VALUE "LegalTrademarks", "MySQL, MyODBC, Connector/ODBC are trademarks of Oracle Corporation\0"
            VALUE "OriginalFilename", "myodbc8@CONNECTOR_DRIVER_TYPE_SHORT@.dll\0"
            VALUE "PrivateBuild", "Production\0"
	          VALUE "ProductName", "Connector/ODBC @CONNECTOR_MAJOR@.@CONNECTOR_MINOR@\0"
            VALUE "ProductVersion", MYODBC_STRPRODUCTVER
	          VALUE "SpecialBuild", "@CONNECTOR_QUALITY@ release\0"
        END
    END
    BLOCK "VarFileInfo"
    BEGIN
        VALUE "Translation", 0x409, 1252
    END
END



