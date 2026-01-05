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
 @file  odbcdialogparams.cpp
 @brief Defines the entry point for the DLL application.
*/

#define WIN32_LEAN_AND_MEAN

#include <windows.h>
#include <windowsx.h>
#include <commctrl.h>
#include <stdio.h>
#include "resource.h"
#include "TabCtrl.h"
#include <assert.h>
#include <commdlg.h>
#include <shlobj.h>
#include <xstring>
#include <shellapi.h>
#include <winsock2.h>

#include "../setupgui.h"
#include <error.h>
#include "odbcdialogparams.h"

#include "stringutil.h"
#include "driver.h"

extern HINSTANCE ghInstance;

static DataSource* pParams= NULL;
static PWCHAR      pCaption= NULL;
static int         OkPressed= 0;

static int         mod= 1;
static bool        flag= false;
static bool        BusyIndicator= false;

static TABCTRL     TabCtrl_1;
/* Whether we are in SQLDriverConnect() prompt mode (used to disable fields) */
static BOOL        g_isPrompt;
/* Variable to keep IDC of control where default value were put. It's reset if
   user changes value. Used to verify if we can reset that control's value.
   It won't work if for more than 1 field, but we have only one in visible future. */
static long        controlWithDefValue= 0;
static SQLWCHAR out_buf[1024];

HelpButtonPressedCallbackType* gHelpButtonPressedCallback = NULL;


/* Function that enables/disables groups of controls */
#define SWITCHED_GROUPS 2
#define MAX_GROUP_COTROLS 2
void SwitchTcpOrPipe(HWND hwnd, BOOL usePipe)
{
  /* groups of fields to enable/disable*/
  const int switchedFields[SWITCHED_GROUPS][MAX_GROUP_COTROLS]=
                                    {{IDC_EDIT_SERVER, IDC_EDIT_PORT},
                                     {IDC_EDIT_SOCKET, 0}};

  /* Default value for enabled empty field */
  const LPCWSTR defaultValues[SWITCHED_GROUPS][MAX_GROUP_COTROLS]=
                                    {{NULL, NULL}, {L"MySQL", NULL}};
  /* Can't be sure that usePipe contains 1 as TRUE*/
  long activeIndex= usePipe ? 1L : 0L;

  for (long i= 0; i < SWITCHED_GROUPS; ++i)
    for (long j= 0; j < MAX_GROUP_COTROLS && switchedFields[i][j] != 0; ++j)
    {
      HWND control= GetDlgItem(hwnd, switchedFields[i][j]);
      EnableWindow(control, i == activeIndex);

      if (defaultValues[i][j] != NULL)
      {
        if (i == activeIndex)
        {
          if (Edit_GetTextLength(control) == 0)
          {
            /* remember that we set that value */
            Edit_SetText(control, defaultValues[i][j]);
            controlWithDefValue= switchedFields[i][j];
          }
        }
        else if (controlWithDefValue == switchedFields[i][j])
        {
          /* we don't want to store the value we set instead of user */
          Edit_SetText(control,L"");
        }
      }
    }
}
#undef SWITCHED_GROUPS
#undef MAX_GROUP_COTROLS


void InitStaticValues()
{
  BusyIndicator= true;
  pParams      = NULL;
  pCaption     = NULL;
  OkPressed    = 0;

  mod          = 1;
  flag         = false;
  BusyIndicator= false;

  gHelpButtonPressedCallback= NULL;
}


#define Refresh(A) RedrawWindow(A,NULL,NULL,RDW_ERASE|RDW_INVALIDATE|RDW_ALLCHILDREN|RDW_UPDATENOW);

BOOL FormMain_DlgProc (HWND, UINT, WPARAM, LPARAM);


void DoEvents (void)
{
  MSG Msg;
  while (PeekMessage(&Msg,NULL,0,0,PM_REMOVE))
  {
    TranslateMessage(&Msg);
    DispatchMessage(&Msg);
  }
}


VOID OnWMNotify(WPARAM wParam, LPARAM lParam);


static BOOL FormMain_OnNotify (HWND hwnd, WPARAM wParam, LPARAM lParam)
{
  OnWMNotify(wParam, lParam);
  int id = (int)wParam;

  switch(id)
  {
    case IDC_TAB1:
    {
      TabControl_Select(&TabCtrl_1); //update internal "this" pointer

      LPNMHDR nm = (LPNMHDR)lParam;
      switch (nm->code)
      {
        case TCN_KEYDOWN:
          TabCtrl_1.OnKeyDown(lParam);

        case TCN_SELCHANGE:
          TabCtrl_1.OnSelChanged();
      }
    }
    break;
  }

  return FALSE;
}


SQLWCHAR * getStrFieldData(HWND hwnd, int idc)
{
  int len = Edit_GetTextLength(GetDlgItem(hwnd,idc));
  if (len > 0)
  {
    Edit_GetText(GetDlgItem(hwnd, idc), out_buf, len + 1);
    return out_buf;
  }
  return nullptr;
}


SQLWCHAR * getStrFieldDataTab(unsigned int framenum, int idc)
{
  assert(TabCtrl_1.hTabPages);
  HWND tab = TabCtrl_1.hTabPages[framenum-1];

  assert(tab);

  return getStrFieldData(tab, idc);
}

void setComboFieldDataTab(const SQLWCHAR *param, unsigned int framenum, int idc)
{
  if ( TabCtrl_1.hTabPages[framenum-1])
  {
    HWND tabHwndMisc = TabCtrl_1.hTabPages[framenum-1];
    HWND charsetCtrl = GetDlgItem(tabHwndMisc, idc);
    ComboBox_SetText(charsetCtrl, param);
  }
}


void setStrFieldData(HWND hwnd, const SQLWCHAR *param, int idc)
{
  Edit_SetText(GetDlgItem(hwnd, idc), param);
}


void setStrFieldDataTab(const SQLWCHAR *param, unsigned int framenum, int idc)
{
  assert(TabCtrl_1.hTabPages);
  HWND tab = TabCtrl_1.hTabPages[framenum-1];

  assert(tab);

  setStrFieldData(tab, param, idc);
}


unsigned int getUnsignedFieldDataTab(unsigned int framenum, int idc )
{
  return getUnsignedFieldData(TabCtrl_1.hTabPages[framenum-1], idc);
}


unsigned int getUnsignedFieldData(HWND hwnd, int idc)
{
  unsigned int param = 0U;
  int len = Edit_GetTextLength(GetDlgItem(hwnd,idc));

  if(len>0)
  {
    std::unique_ptr<SQLWCHAR> buf(new SQLWCHAR[len + 1]);
    Edit_GetText(GetDlgItem(hwnd,idc), buf.get(), len+1);
    param = _wtol(buf.get());
  }
  return param;
}


void setUnsignedFieldData(HWND hwnd, const unsigned int param, int idc)
{
  wchar_t buf[20];
  _itow( param, (wchar_t*)buf, 10 );
  Edit_SetText(GetDlgItem(hwnd,idc), buf);
}


void setUnsignedFieldDataTab(unsigned int framenum, const unsigned int param, int idc)
{
  setUnsignedFieldData(TabCtrl_1.hTabPages[framenum-1], param, idc);
}


HWND getTabCtrlTab(void)
{
  return TabCtrl_1.hTab;
}


HWND getTabCtrlTabPages(unsigned int framenum)
{
  return TabCtrl_1.hTabPages[framenum];
}


my_bool getBoolFieldDataTab(unsigned int framenum, int idc)
{
  assert(TabCtrl_1.hTabPages);
  HWND checkbox = GetDlgItem(TabCtrl_1.hTabPages[framenum-1], idc);

  assert(checkbox);
  if (checkbox)
      return !!Button_GetCheck(checkbox);

  return false;
}


/* this reads non-DSN bool data */
my_bool getBoolFieldData(HWND hwnd, int idc)
{
  HWND checkbox = GetDlgItem(hwnd, idc);

  assert(checkbox);
  if (checkbox)
      return !!Button_GetCheck(checkbox);

  return false;
}


void setBoolFieldData(HWND hwnd, int idc, my_bool state)
{
  HWND checkbox = GetDlgItem(hwnd, idc);
  assert(checkbox);
  if (checkbox)
  Button_SetCheck(checkbox, state);
}


void setBoolFieldDataTab(unsigned int framenum, int idc, my_bool state)
{
  assert(TabCtrl_1.hTabPages);
  Button_SetCheck(GetDlgItem(TabCtrl_1.hTabPages[framenum-1],idc), state);

}


void setControlEnabled(unsigned int framenum, int idc, my_bool state)
{
  HWND cursorTab= TabCtrl_1.hTabPages[framenum-1];
  assert(cursorTab);

  if (cursorTab)
  {
    EnableWindow(GetDlgItem(cursorTab, idc), state);
  }
}


void OnDialogClose();


void FormMain_OnClose(HWND hwnd)
{
  //PostQuitMessage(0);// turn off message loop
    //Unhooks hook(s) :)
  OnDialogClose();

  TabControl_Destroy(&TabCtrl_1);
  EndDialog(hwnd, NULL);
}


/****************************************************************************
 *                                                                          *
 * Functions: FormMain_OnCommand related event code                         *
 *                                                                          *
 * Purpose : Handle WM_COMMAND messages: this is the heart of the app.		*
 *                                                                          *
 * History : Date      Reason                                               *
 *           00/00/00  Created                                              *
 *                                                                          *
 ****************************************************************************/
void btnDetails_Click (HWND hwnd)
{
  RECT rect;
  GetWindowRect( hwnd, &rect );
  mod *= -1;
  ShowWindow( GetDlgItem(hwnd,IDC_TAB1), mod > 0? SW_SHOW: SW_HIDE );

  if(!flag && mod==1)
  {
    static PWSTR tabnames[] = {
      L"Connection",
      L"Authentication",
      L"Metadata",
      L"Cursors/Results",
      L"Debug",
      L"SSL",
      L"Misc", 0
    };

    static PWSTR dlgnames[]= {MAKEINTRESOURCE(IDD_TAB1),
                    MAKEINTRESOURCE(IDD_TAB2),
                    MAKEINTRESOURCE(IDD_TAB3),
                    MAKEINTRESOURCE(IDD_TAB4),
                    MAKEINTRESOURCE(IDD_TAB5),
                    MAKEINTRESOURCE(IDD_TAB6),
                    MAKEINTRESOURCE(IDD_TAB7),
                    0};

    New_TabControl( &TabCtrl_1,                 // address of TabControl struct
                    GetDlgItem(hwnd, IDC_TAB1), // handle to tab control
                    tabnames,                   // text for each tab
                    dlgnames,                   // dialog id's of each tab page dialog
                    &FormMain_DlgProc,          // address of main windows proc
                    NULL,                       // address of size function
                    TRUE);                      // stretch tab page to fit tab ctrl
    flag = true;


    HWND ssl_tab = TabCtrl_1.hTabPages[5];
    HWND combo = GetDlgItem(ssl_tab, IDC_EDIT_SSL_MODE);

    ComboBox_ResetContent(combo);

    ComboBox_AddString(combo, L"");
    ComboBox_AddString(combo, LSTR(ODBC_SSL_MODE_DISABLED));
    ComboBox_AddString(combo, LSTR(ODBC_SSL_MODE_PREFERRED));
    ComboBox_AddString(combo, LSTR(ODBC_SSL_MODE_REQUIRED));
    ComboBox_AddString(combo, LSTR(ODBC_SSL_MODE_VERIFY_CA));
    ComboBox_AddString(combo, LSTR(ODBC_SSL_MODE_VERIFY_IDENTITY));

    syncTabs(hwnd, pParams);
  }
  // To increase the height of the window modify the expression:
  int new_height = rect.bottom - rect.top + 355*mod;
  MoveWindow( hwnd, rect.left, rect.top, rect.right - rect.left, new_height, TRUE );
  // Then change the height for IDD_DIALOG1 and SysTabControl32 inside
  // odbcdialogparams.rc
}


void btnOk_Click (HWND hwnd)
{
  FillParameters(hwnd, pParams);

  /* if DS params are valid, close dialog */
  if (mytestaccept(hwnd, pParams))
  {
    OkPressed= 1;
    PostMessage(hwnd, WM_CLOSE, NULL, NULL);
  }
}


void btnCancel_Click (HWND hwnd)
{
  PostMessage(hwnd, WM_CLOSE, NULL, NULL);
}


void btnTest_Click (HWND hwnd)
{
  FillParameters(hwnd, pParams);
  SQLWSTRING msg = mytest(hwnd, pParams);
  MessageBoxW(hwnd, msg.c_str(), L"Test Result", MB_OK);
}


void btnHelp_Click (HWND hwnd)
{
  ShellExecute(NULL, L"open",
         L"https://dev.mysql.com/doc/connector-odbc/en/connector-odbc-configuration-dsn-windows.html",
         NULL, NULL, SW_SHOWNORMAL);
}


void chooseFile( HWND parent, int hostCtlId )
{
  OPENFILENAMEW	dialog;

  HWND			hostControl = GetDlgItem( parent, hostCtlId );

  wchar_t			szFile[MAX_PATH];    // buffer for file name

  Edit_GetText( hostControl, szFile, sizeof(szFile) );
  // Initialize OPENFILENAME
  ZeroMemory(&dialog, sizeof(dialog));

  dialog.lStructSize			= sizeof(dialog);
  dialog.lpstrFile			= szFile;

  dialog.lpstrTitle			= L"Select File";
  dialog.nMaxFile				= sizeof(szFile);
  dialog.lpstrFileTitle		= NULL;
  dialog.nMaxFileTitle		= 0;
  dialog.lpstrInitialDir		= NULL;
  dialog.Flags				= OFN_PATHMUSTEXIST | OFN_FILEMUSTEXIST ;
  dialog.hwndOwner			= parent;
  dialog.lpstrCustomFilter	= L"All Files\0*.*\0PEM\0*.pem\0";
  dialog.nFilterIndex			= 2;

  if ( GetOpenFileNameW( &dialog ) )
  {
    Edit_SetText( hostControl, dialog.lpstrFile );
  }
}


void choosePath( HWND parent, int hostCtlId )
{
  HWND			hostControl = GetDlgItem( parent, hostCtlId );

  BROWSEINFOW		dialog;
  wchar_t			path[MAX_PATH];    // buffer for file name

  Edit_GetText( hostControl, path, sizeof(path) );

  ZeroMemory(&dialog,sizeof(dialog));

  dialog.lpszTitle		= L"Pick a CA Path";
  dialog.hwndOwner		= parent;
  dialog.pszDisplayName	= path;

  LPITEMIDLIST pidl = SHBrowseForFolder ( &dialog );

  if ( pidl )
  {
    SHGetPathFromIDList ( pidl, path );

    Edit_SetText( hostControl, path );

    IMalloc * imalloc = 0;
    if ( SUCCEEDED( SHGetMalloc ( &imalloc )) )
    {
      imalloc->Free ( pidl );
      imalloc->Release ( );
    }
  }
}

#ifndef MAX_VISIBLE_CB_ITEMS
#define MAX_VISIBLE_CB_ITEMS 20
#endif


/**
   Adjusting height of dropped list of cbHwnd combobox to fit
   itemsCount items, but not more than MAX_VISIBLE_CB_ITEMS
   ComboBox_SetMinVisible not used because it was introduced in XP.
*/
int adjustDropdownHeight(HWND cbHwnd, unsigned int itemsCount)
{
  COMBOBOXINFO  dbcbinfo;
  RECT          ddRect;
  int           newHeight = 0;

  dbcbinfo.cbSize= sizeof(COMBOBOXINFO);
  ComboBox_GetDroppedControlRect(cbHwnd, &ddRect);
  newHeight= ddRect.bottom - ddRect.top;

  if ( GetComboBoxInfo(cbHwnd, &dbcbinfo) )
  {
    itemsCount= itemsCount < 1 ? 1 : (itemsCount > MAX_VISIBLE_CB_ITEMS
                                      ? MAX_VISIBLE_CB_ITEMS
                                      : itemsCount );

    /* + (itemsCount - 1) - 1 pixel spaces between list items */
    newHeight= itemsCount*ComboBox_GetItemHeight(cbHwnd) + (itemsCount - 1);
    MoveWindow(dbcbinfo.hwndList, ddRect.left, ddRect.top, ddRect.right-ddRect.left, newHeight, FALSE);
  }

  return newHeight;
}


/**
   Processing commands for dbname combobox (hwndCtl).
*/
void processDbCombobox(HWND hwnd, HWND hwndCtl, UINT codeNotify)
{
  switch(codeNotify)
  {
    /* Loading list and adjust its height if button clicked and on user input */
    case(CBN_DROPDOWN):
    {
      FillParameters(hwnd, pParams);
      std::vector<SQLWSTRING> dbs;

      dbs = mygetdatabases(hwnd, pParams);

      ComboBox_ResetContent(hwndCtl);

      adjustDropdownHeight(hwndCtl, (unsigned int)dbs.size());

      for (SQLWSTRING dbname : dbs)
        ComboBox_AddString(hwndCtl, (SQLWCHAR *)dbname.c_str());

      ComboBox_SetText(hwndCtl, pParams->opt_DATABASE);

      break;
    }
  }
}


/**
Processing commands for charset combobox (hwndCtl).
*/
void processCharsetCombobox(HWND hwnd, HWND hwndCtl, UINT codeNotify)
{
  switch(codeNotify)
  {
    /* Loading list and adjust its height if button clicked and on user input */
    case(CBN_DROPDOWN):
    {
      //FillParameters(hwnd, *pParams);
      auto csl = mygetcharsets(hwnd, pParams);

      ComboBox_ResetContent(hwndCtl);

      adjustDropdownHeight(hwndCtl, (unsigned int)csl.size());

      for (SQLWSTRING csname : csl)
        ComboBox_AddString(hwndCtl, (SQLWCHAR *)csname.c_str());

      ComboBox_SetText(hwndCtl, pParams->opt_CHARSET);

      break;
    }
  }
}


void FormMain_OnCommand(HWND hwnd, int id, HWND hwndCtl, UINT codeNotify)
{
  if (controlWithDefValue != 0 && id == controlWithDefValue
    && codeNotify==EN_CHANGE)
    controlWithDefValue= 0;

  switch (id)
  {
    case IDOK:
      btnOk_Click(hwnd); break;
    case IDCANCEL:
      btnCancel_Click(hwnd); break;
    case IDC_BUTTON_DETAILS:
      btnDetails_Click(hwnd); break;
    case IDC_BUTTON_HELP:
      btnHelp_Click(hwnd); break;
    case IDC_BUTTON_TEST:
      btnTest_Click(hwnd); break;
    case IDC_SSLKEYCHOOSER:
      chooseFile(hwnd, IDC_EDIT_SSL_KEY); break;
    case IDC_SSLCERTCHOOSER:
      chooseFile(hwnd, IDC_EDIT_SSL_CERT); break;
    case IDC_SSLCACHOOSER:
      chooseFile(hwnd, IDC_EDIT_SSL_CA); break;
    case IDC_SSLCAPATHCHOOSER:
      choosePath(hwnd, IDC_EDIT_SSL_CAPATH); break;
    case IDC_RSAKEYCHOOSER:
      chooseFile(hwnd, IDC_EDIT_RSAKEY); break;
    case IDC_SSLCRLCHOOSER:
      chooseFile(hwnd, IDC_EDIT_SSL_CRL); break;
    case IDC_SSLCRLPATHCHOOSER:
      choosePath(hwnd, IDC_EDIT_SSL_CRLPATH); break;
    case IDC_CHOOSER_PLUGIN_DIR:
      choosePath(hwnd, IDC_EDIT_PLUGIN_DIR); break;
    case IDC_CHOOSER_LOAD_DATA_LOCAL_DIR:
      choosePath(hwnd, IDC_EDIT_LOAD_DATA_LOCAL_DIR); break;
    case IDC_CHOOSER_OCI_CONFIG_FILE:
      chooseFile(hwnd, IDC_EDIT_OCI_CONFIG_FILE); break;
    case IDC_RADIO_tcp:
    case IDC_RADIO_NAMED_PIPE:
      SwitchTcpOrPipe(hwnd, !!Button_GetCheck(GetDlgItem(hwnd, IDC_RADIO_NAMED_PIPE)));
      break;
    case IDC_CHECK_cursor_prefetch_active:
      {
        HWND cursorTab= TabCtrl_1.hTabPages[CURSORS_TAB-1];
        assert(cursorTab);
        HWND prefetch= GetDlgItem(cursorTab, IDC_EDIT_PREFETCH);
        assert(prefetch);

        EnableWindow(prefetch, !!Button_GetCheck(GetDlgItem(cursorTab,
                                            IDC_CHECK_cursor_prefetch_active)));

        if (Edit_GetTextLength(prefetch) == 0)
        {
          setUnsignedFieldData(cursorTab, default_cursor_prefetch,
                              IDC_EDIT_PREFETCH);
        }
      }
      break;
    case IDC_EDIT_DSN:
    {
      if (codeNotify==EN_CHANGE)
      {
        int len = Edit_GetTextLength(GetDlgItem(hwnd,IDC_EDIT_DSN));
        Button_Enable(GetDlgItem(hwnd,IDOK), len > 0);
        Button_Enable(GetDlgItem(hwnd,IDC_BUTTON_TEST), len > 0);
        RedrawWindow(hwnd,NULL,NULL,RDW_INVALIDATE);
      }
      break;
    }

    case IDC_EDIT_dbname:
      processDbCombobox(hwnd, hwndCtl, codeNotify);
    break;

    case IDC_EDIT_CHARSET:
      processCharsetCombobox(hwnd, hwndCtl, codeNotify);
  }

  return;
}


void AlignWindowToBottom(HWND hwnd, int dY);
void AdjustLayout(HWND hwnd);


void FormMain_OnSize(HWND hwnd, UINT state, int cx, int cy)
{
  AdjustLayout(hwnd);
}

static int yCurrentScroll = 0;   // current vertical scroll value

void FormMain_OnScroll(HWND hwnd, HWND hCtrl, UINT code, int pos)
{
  SCROLLINFO si;
  si.cbSize = sizeof(si);
  si.fMask = SIF_POS;
  int yNewPos;    // new position
  switch (code)
  {
      // User clicked the scroll bar shaft above the scroll box.
    case SB_PAGEUP:
      yNewPos = yCurrentScroll - 50;
      break;

      // User clicked the scroll bar shaft below the scroll box.
    case SB_PAGEDOWN:
      yNewPos = yCurrentScroll + 50;
      break;

      // User clicked the top arrow.
    case SB_LINEUP:
      yNewPos = yCurrentScroll - 5;
      break;

      // User clicked the bottom arrow.
    case SB_LINEDOWN:
      yNewPos = yCurrentScroll + 5;
      break;

      // User dragged the scroll box.
    case SB_THUMBPOSITION:
      yNewPos = pos;
      break;

    default:
      yNewPos = yCurrentScroll;
  }

  si.nPos = yNewPos;

  ScrollWindowEx(hwnd, 0, -50,
    NULL, NULL, NULL, NULL, SW_INVALIDATE);
  UpdateWindow(hwnd);

  SetScrollInfo(hwnd, SB_VERT, &si, TRUE);
  yCurrentScroll = yNewPos;
}

void AdjustLayout(HWND hwnd)
{
  RECT  rc;
    GetClientRect(hwnd,&rc);

  BOOL Visible = (mod==-1)?0:1;

  if(TabCtrl_1.hTab)
  {
    EnableWindow( TabCtrl_1.hTab, Visible );
    ShowWindow( TabCtrl_1.hTab, Visible );
  }

  PCWSTR pButtonCaption = Visible? L"Details <<" : L"Details >>";
  SetWindowText( GetDlgItem(hwnd,IDC_BUTTON_DETAILS), pButtonCaption );
  const int dY = 20;
  AlignWindowToBottom( GetDlgItem(hwnd,IDC_BUTTON_DETAILS), dY);
  AlignWindowToBottom( GetDlgItem(hwnd,IDOK), dY);
  AlignWindowToBottom( GetDlgItem(hwnd,IDCANCEL), dY);
  AlignWindowToBottom( GetDlgItem(hwnd,IDC_BUTTON_HELP), dY);

  Refresh(hwnd);
}


void AlignWindowToBottom(HWND hwnd, int dY)
{
  if(!hwnd)
    return;
  RECT rect;
  GetWindowRect( hwnd, &rect );
  int h, w;
  RECT rc;
  GetWindowRect(GetParent(hwnd), &rc);

  h=rect.bottom-rect.top;
  w=rect.right-rect.left;

  rc.top = rc.bottom;
  MapWindowPoints(HWND_DESKTOP, GetParent(hwnd), (LPPOINT)&rect, 2);
  MapWindowPoints(HWND_DESKTOP, GetParent(hwnd), (LPPOINT)&rc, 2);

  MoveWindow(hwnd, rect.left, rc.top -dY-h,w,h,FALSE);
}


HWND g_hwndDlg;
BOOL DoCreateDialogTooltip(void);


BOOL FormMain_OnInitDialog(HWND hwnd, HWND hwndFocus, LPARAM lParam)
{
  g_hwndDlg = hwnd;
  SetWindowText(hwnd, pCaption);
  //----Everything else must follow the above----//
  btnDetails_Click(hwnd);
  AdjustLayout(hwnd);
  //Get the initial Width and height of the dialog
  //in order to fix the minimum size of dialog

  syncForm(hwnd, pParams);

  /* Disable fields if in prompt mode */
  if (g_isPrompt)
  {
    EnableWindow(GetDlgItem(hwnd, IDC_EDIT_DSN), FALSE);
    EnableWindow(GetDlgItem(hwnd, IDC_EDIT_DESCRIPTION), FALSE);
  }

  /* if prompting without DSN, don't disable OK button */
  /* preserved here old logic + enabled OK if data source
     name is not NULL when not prompting. I don't know why it should be disabled
     when prompting and name is not NULL. */
  if (g_isPrompt == (!pParams->opt_DSN))
  {
    Button_Enable(GetDlgItem(hwnd,IDOK), 1);
    Button_Enable(GetDlgItem(hwnd,IDC_BUTTON_TEST), 1);
    RedrawWindow(hwnd,NULL,NULL,RDW_INVALIDATE);
  }

  BOOL b = DoCreateDialogTooltip();
  return 0;
}


BOOL FormMain_DlgProc (HWND hwndDlg, UINT msg, WPARAM wParam, LPARAM lParam)
{
  switch(msg)
  {
    HANDLE_MSG (hwndDlg, WM_CLOSE, FormMain_OnClose);
    HANDLE_MSG (hwndDlg, WM_COMMAND, FormMain_OnCommand);
    HANDLE_MSG (hwndDlg, WM_INITDIALOG, FormMain_OnInitDialog);
    HANDLE_MSG (hwndDlg, WM_SIZE, FormMain_OnSize);
    HANDLE_MSG (hwndDlg, WM_VSCROLL, FormMain_OnScroll);
  // There is no message cracker for WM_NOTIFY so redirect manually
  case WM_NOTIFY:
    return FormMain_OnNotify (hwndDlg,wParam,lParam);

  default: return FALSE;
  }
}


/*
   Display the DSN dialog

   @param params DataSource struct, should be pre-populated
   @param ParentWnd Parent window handle
   @return 1 if the params were correctly populated and OK was pressed
           0 if the dialog was closed or cancelled
*/
extern "C"
int ShowOdbcParamsDialog(DataSource* params, HWND ParentWnd, BOOL isPrompt)
{
  assert(!BusyIndicator);
  InitStaticValues();

  pParams= params;
  pCaption= L"MySQL Connector/ODBC Data Source Configuration";
  g_isPrompt= isPrompt;

  /*
     If prompting (with a DSN name), or not prompting (add/edit DSN),
     we translate the lib path to the actual driver name.
  */
  if (params->opt_DSN || !isPrompt)
  {
    Driver driver;
    if (params->opt_DRIVER)
      driver.lib = params->opt_DRIVER;
    /* TODO Driver lookup is done in driver too, do we really need it there? */
    if (!driver.lib || driver.lookup_name())
    {
      wchar_t msg[256];
      swprintf(msg, 256, L"Failure to lookup driver entry at path '%ls'",
               (const SQLWCHAR*)driver.lib);
      MessageBox(ParentWnd, msg, L"Cannot find driver entry", MB_OK);
      return 0;
    }
    params->opt_DRIVER = driver.name;
  }
  DialogBox(ghInstance, MAKEINTRESOURCE(IDD_DIALOG1), ParentWnd,
            (DLGPROC)FormMain_DlgProc);

  BusyIndicator= false;
  return OkPressed;
}
