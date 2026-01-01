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
 @file TabCtrl.h
 @brief Tab Control Enhanced.

 Make Creating and modifying Tab Control Property pages a snap.

 (c) 2006 David MacDermot

 This module is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/** Structs *****************************************************************/

typedef struct TabControl
{
	HWND hTab;
	HWND* hTabPages;
	PWSTR *tabNames;
	PWSTR *dlgNames;
	int tabPageCount;
	BOOL blStretchTabs;

	// Function pointer to Parent Dialog Proc
	BOOL (*ParentProc)(HWND, UINT, WPARAM, LPARAM);

	// Pointers to shared functions
	BOOL (*OnSelChanged)(VOID);
	BOOL (*OnKeyDown)(LPARAM);
	BOOL (*StretchTabPage) (HWND);
	BOOL (*CenterTabPage) (HWND);

} TABCTRL, *LPTABCTRL;

/** Prototypes **************************************************************/


//typedef BOOL CALLBACK (ParentProcType)(HWND, UINT, WPARAM, LPARAM);

void New_TabControl(LPTABCTRL,
					HWND,
					PWSTR*,
					PWSTR*,
					BOOL (*ParentProc)(HWND, UINT, WPARAM, LPARAM),
					VOID (*TabPage_OnSize)(HWND, UINT, int, int),
					BOOL fStretch);

void TabControl_Select(LPTABCTRL);
void TabControl_Destroy(LPTABCTRL);
