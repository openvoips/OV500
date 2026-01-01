; Copyright (c) 2001, 2024, Oracle and/or its affiliates.
;
; This program is free software; you can redistribute it and/or modify
; it under the terms of the GNU General Public License, version 2.0, as
; published by the Free Software Foundation.
;
; This program is designed to work with certain software (including
; but not limited to OpenSSL) that is licensed under separate terms, as
; designated in a particular file or component or in included license
; documentation. The authors of MySQL hereby grant you an additional
; permission to link the program and your derivative works with the
; separately licensed software that they have either included with
; the program or referenced in the documentation.
;
; Without limiting anything contained in the foregoing, this file,
; which is part of Connector/ODBC, is also subject to the
; Universal FOSS Exception, version 1.0, a copy of which can be found at
; https://oss.oracle.com/licenses/universal-foss-exception.
;
; This program is distributed in the hope that it will be useful, but
; WITHOUT ANY WARRANTY; without even the implied warranty of
; MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
; See the GNU General Public License, version 2.0, for more details.
;
; You should have received a copy of the GNU General Public License
; along with this program; if not, write to the Free Software Foundation, Inc.,
; 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
;
;/* @EDIT_WARNING_MESSAGE@ */
;
LIBRARY myodbc8@CONNECTOR_DRIVER_TYPE_SHORT@.DLL
VERSION 0@CONNECTOR_MAJOR@.@CONNECTOR_MINOR_PADDED@
;DESCRIPTION "MySQL ODBC @CONNECTOR_MAJOR@.@CONNECTOR_MINOR@ @DRIVER_TYPE@ Driver, Copyright (c) 1995, 2018, Oracle and/or its affiliates"

;CODE MOVEABLE DISCARDABLE
;DATA MOVEABLE MULTIPLE
;PROTMODE
;SEGMENTS
;DLL_TEXT PRELOAD
;INIT_TEXT PRELOAD
;DATA PRELOAD
;HEAPSIZE 10000
; 
; Export definitions
EXPORTS
;
SQLAllocConnect
SQLAllocEnv
SQLAllocStmt
SQLAllocHandle
SQLBindCol
SQLBindParameter
SQLBrowseConnect@WIDECHARCALL@
SQLBulkOperations
SQLCancel
SQLCancelHandle
SQLCloseCursor
SQLColAttribute@WIDECHARCALL@
SQLColAttributes@WIDECHARCALL@
SQLColumnPrivileges@WIDECHARCALL@
SQLColumns@WIDECHARCALL@
SQLConnect@WIDECHARCALL@
SQLCopyDesc
SQLDescribeCol@WIDECHARCALL@
SQLDescribeParam
SQLDisconnect
SQLDriverConnect@WIDECHARCALL@
SQLEndTran
SQLError@WIDECHARCALL@
SQLExecDirect@WIDECHARCALL@
SQLExecute
SQLExtendedFetch
SQLFetch
SQLFetchScroll
SQLFreeConnect
SQLFreeEnv
SQLFreeStmt
SQLFreeHandle
SQLForeignKeys@WIDECHARCALL@
SQLGetConnectAttr@WIDECHARCALL@
SQLGetConnectOption@WIDECHARCALL@
SQLGetCursorName@WIDECHARCALL@
SQLGetDescField@WIDECHARCALL@
SQLGetDescRec@WIDECHARCALL@
SQLGetDiagField@WIDECHARCALL@
SQLGetDiagRec@WIDECHARCALL@
SQLGetData
SQLGetEnvAttr
SQLGetFunctions
SQLGetInfo@WIDECHARCALL@
SQLGetStmtAttr@WIDECHARCALL@
SQLGetStmtOption
SQLGetTypeInfo@WIDECHARCALL@
SQLMoreResults
SQLNativeSql@WIDECHARCALL@
SQLNumParams
SQLNumResultCols
SQLParamData
SQLParamOptions
SQLPrepare@WIDECHARCALL@
SQLPrimaryKeys@WIDECHARCALL@
SQLProcedureColumns@WIDECHARCALL@
SQLProcedures@WIDECHARCALL@
SQLPutData
SQLRowCount
SQLSetCursorName@WIDECHARCALL@
SQLSetDescField@WIDECHARCALL@
SQLSetDescRec@WIDECHARCALL@
SQLSetEnvAttr
SQLSetConnectAttr@WIDECHARCALL@
SQLSetConnectOption@WIDECHARCALL@
SQLSetParam
SQLSetPos
SQLSetScrollOptions
SQLSetStmtAttr@WIDECHARCALL@
SQLSetStmtOption
SQLSpecialColumns@WIDECHARCALL@
SQLStatistics@WIDECHARCALL@
SQLTables@WIDECHARCALL@
SQLTablePrivileges@WIDECHARCALL@
SQLTransact
;
DllMain
LoadByOrdinal
;_DllMainCRTStartup
;




