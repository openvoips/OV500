// Copyright (c) 2000, 2024, Oracle and/or its affiliates.
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
  @file  dll.c
  @brief Library initialization functions.
*/

#include "driver.h"
#include <locale.h>

std::string thousands_sep, decimal_point, default_locale;
static int myodbc_inited=0;
static int mysys_inited=0;

std::string current_dll_location;
std::string default_plugin_location;


/*
  Sigpipe handler
*/

#if !defined(__WIN__) && !defined(SKIP_SIGPIPE_HANDLER)

#include <signal.h>

static void
myodbc_pipe_sig_handler(int sig __attribute__((unused)))
{
  /* Do nothing */
}

#endif

/*
  @type    : myodbc3 internal
  @purpose : initializations
*/

void myodbc_init(void)
{
#if !defined(__WIN__) && !defined(SKIP_SIGPIPE_HANDLER)
   /*
     sigaction will block other signals from coming when handler is working
   */
   struct sigaction action;
   action.sa_handler = myodbc_pipe_sig_handler;
   sigemptyset(&action.sa_mask);
   action.sa_flags = 0;
   sigaction(SIGPIPE, &action, NULL);
#endif

  ++myodbc_inited;

  if (myodbc_inited > 1)
    return;

  if(!mysys_inited)
  {
    my_sys_init();
    mysys_inited = 1;
  }

  {
    struct lconv *tmp;
    DECLARE_LOCALE_HANDLE

    init_getfunctions();
    default_locale = setlocale(LC_NUMERIC,NullS);

    __LOCALE_SET("")

    tmp=localeconv();
    decimal_point = tmp->decimal_point;
    thousands_sep = tmp->thousands_sep;

    __LOCALE_RESTORE()

    utf8_charset_info= get_charset_by_csname(transport_charset, MYF(MY_CS_PRIMARY),
                                             MYF(0));

#ifdef IS_BIG_ENDIAN
    utf16_charset_info = get_charset_by_csname("utf16", MYF(MY_CS_PRIMARY),
                                             MYF(0));
#else
    utf16_charset_info = get_charset_by_csname("utf16le", MYF(MY_CS_PRIMARY),
                                             MYF(0));
#endif
  }
}


/*
  @type    : myodbc3 internal
  @purpose : clean all resources while unloading..
*/
void myodbc_end()
{
  if (!myodbc_inited)
    return;

  --myodbc_inited;

  if (!myodbc_inited)
  {

    /* my_thread_end_wait_time was added in 5.1.14 and 5.0.32 */
#if !defined(NONTHREADSAFE) && \
    (MYSQL_VERSION_ID >= 50114 || \
    (MYSQL_VERSION_ID >= 50032 && MYSQL_VERSION_ID < 50100)) && \
    MYSQL_VERSION_ID < 50701
    /*
       This eliminates the delay when mysys_end() is called and other threads
       have been initialized but not ended.
    */
    my_thread_end_wait_time= 0;
#endif

    mysql_library_end();
  }
}


/*
  @type    : myodbc3 internal
  @purpose : main entry point
*/

#ifdef _WIN32

/*
  Initialize a global variable with the current location of DLL.
  It will be used for setting plugin and dll directory if needed.
*/
void dll_location_init(HMODULE inst)
{
  const size_t buflen = 4096;
  std::string dll_loc;
  dll_loc.reserve(buflen);

  GetModuleFileNameA((HMODULE)inst, dll_loc.data(), buflen);
  current_dll_location = dll_loc.data();
  auto bs_pos = current_dll_location.find_last_of('\\');
  if (bs_pos != std::string::npos)
  {
    current_dll_location = current_dll_location.substr(0, bs_pos + 1);
    default_plugin_location = current_dll_location + "plugin\\";
  }
}

static int inited= 0;
int APIENTRY LibMain(HANDLE inst, DWORD ul_reason_being_called,
		     LPVOID lpReserved)
{

  switch (ul_reason_being_called) {
  case DLL_PROCESS_ATTACH:  /* case of libentry call in win 3.x */
    if (!inited++)
    {
      dll_location_init((HMODULE)inst);
      myodbc_init();
    }
    break;
  case DLL_PROCESS_DETACH:  /* case of wep call in win 3.x */
    if (!--inited)
    {
      // Process is about to detach. All has to be deinited to avoid
      // memory leaks even if initialized multiple times (myodbc_inited > 1).
      myodbc_inited = 1;
      myodbc_end();
    }
    break;

  /*
     We don't explicitly call my_thread_init() to avoid initialization in
     threads that may not even make ODBC calls. my_thread_init() will be
     called implicitly when mysys calls are made from the thread.
  */
  case DLL_THREAD_ATTACH:
    break;
  case DLL_THREAD_DETACH:
#ifdef THREAD
    mysql_thread_end();
#endif
    break;

  default:
    break;
  }

  return TRUE;

  UNREFERENCED_PARAMETER(lpReserved);
}


/*
  @type    : myodbc3 internal
  @purpose : entry point for the DLL
*/

int __stdcall DllMain(HANDLE hInst,DWORD ul_reason_being_called,
		      LPVOID lpReserved)
{
  return LibMain(hInst,ul_reason_being_called,lpReserved);
}

#elif defined(__WIN__)

/***************************************************************************
  This routine is called by LIBSTART.ASM at module load time.  All it
  does in this sample is remember the DLL module handle.  The module
  handle is needed if you want to do things like load stuff from the
  resource file (for instance string resources).
***************************************************************************/

int _export PASCAL libmain(HANDLE hModule,short cbHeapSize,
	     SQLCHAR *lszCmdLine)
{
  myodbc_init();
  return TRUE;
}

#endif /* __WIN__ */

#ifdef _WIN32
void __declspec(dllexport) PASCAL LoadByOrdinal(void);
/* Entry point to cause DM to load using ordinals */
void __declspec(dllexport) PASCAL LoadByOrdinal(void)
{}
#endif
