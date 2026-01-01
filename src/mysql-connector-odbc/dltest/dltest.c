// Copyright (c) 2006, 2024, Oracle and/or its affiliates.
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

#include <stdio.h>
#include <stdlib.h>

#ifdef WIN32
    #include <windows.h>
#else
    #include <dlfcn.h>
#endif

const char szSyntax[] =
"\n" \
"**********************************************\n" \
"* dltest                                     *\n" \
"**********************************************\n" \
"* Syntax                                     *\n" \
"*                                            *\n" \
"*      dltest libName Symbol                 *\n" \
"*      dltest lib:libName... sym:Symbol...   *\n" \
"*                                            *\n" \
"* libName                                    *\n" \
"*                                            *\n" \
"*      Full path + file name of share to test*\n" \
"*                                            *\n" \
"* Symbol                                     *\n" \
"*                                            *\n" \
"*      ie a function name in the share       *\n" \
"*                                            *\n" \
"* Notes                                      *\n" \
"*                                            *\n" \
"*      This can be placed into a makefile    *\n" \
"*      to throw an error if test fails.      *\n" \
"*                                            *\n" \
"*      If this segfaults you probably have an*\n" \
"*      unresolved symbol in the lib. This is *\n" \
"*      not caught since dltest started using *\n" \
"*      libtool. Linux users can refer to the *\n" \
"*      man page for dlopen to create a       *\n" \
"*      better test.                          *\n" \
"*                                            *\n" \
"*                                            *\n" \
"* Examples                                   *\n" \
"*                                            *\n" \
"*      dltest /usr/lib/libMy.so MyFunc       *\n" \
"*                                            *\n" \
"**********************************************\n\n";

#ifdef WIN32
typedef HMODULE DLTestModule;
#else
typedef void *  DLTestModule;
#endif

static void dltest_dlinit(void);
static DLTestModule dltest_dlopen(const char *);
static void dltest_dlsym(DLTestModule, const char *);
static void dltest_dlclose(DLTestModule);


int main( int argc, char *argv[] )
{
  DLTestModule hModule = NULL;

  if ( argc < 2 )
  {
      printf( szSyntax );
      exit( 1 );
  }

  /* At least one argument, a library path */

  dltest_dlinit();

  if ( strncmp(argv[1],"lib:",4) == 0 || strncmp(argv[1],"sym:",4) == 0 )
  {
    /* Alternative API, can handle multiple libs and symbols, in any mix */
    int i;
    for (i = 1; i < argc; i++)
    {
      if (strncmp(argv[i],"lib:",4) == 0)
      {
        hModule = dltest_dlopen(argv[i]+4);     /* Open a new module */
      }
      else if (strncmp(argv[i],"sym:",4) == 0)
      {
        dltest_dlsym(hModule,argv[i]+4);
      }
    }

    /*
      Why close at all, and if we load libraries dependent on each
      other, closing will prevent a later opened lib from accessing
      symbols from the previous one, at least on AIX 5.2
    */
  }
  else
  {
    /* Old API */
    hModule = dltest_dlopen(argv[1]);
    if ( argc > 2 )
      dltest_dlsym(hModule,argv[2]);
    dltest_dlclose(hModule);
  }

  return(0);
}


static void dltest_dlinit(void)
{
#ifndef WIN32
/*
  if ( lt_dlinit() )
  {
    printf( "[%s][%d] ERROR: Failed to lt_dlinit()\n", __FILE__, __LINE__ );
    exit( 1 );
  }
*/
#endif
}

static DLTestModule dltest_dlopen(const char *path)
{
#ifdef WIN32
  DLTestModule hModule = LoadLibrary((LPCSTR)path);
  if ( !hModule )
  {
    LPVOID pszMsg;
    FormatMessage(FORMAT_MESSAGE_ALLOCATE_BUFFER | FORMAT_MESSAGE_FROM_SYSTEM,
                  NULL,
                  GetLastError(),
                  MAKELANGID(LANG_NEUTRAL, SUBLANG_DEFAULT),
                  (LPTSTR) &pszMsg,
                  0,
                  NULL);
    printf("[%s][%d] ERROR LoadLibrary(): %s\n", __FILE__, __LINE__, (char*)pszMsg);
    LocalFree(pszMsg);
    exit(1);
  }
#else
  DLTestModule hModule = dlopen(path, RTLD_GLOBAL | RTLD_NOW);
  if ( !hModule )
  {
    printf("[%s][%d] ERROR dlopen(): %s\n", __FILE__, __LINE__, dlerror());
    exit(1);
  }
#endif /* WIN32 */

  printf("[%s][%d] SUCCESS: Loaded %s\n", __FILE__, __LINE__, path);
  return hModule;
}


static void dltest_dlclose(DLTestModule hModule)
{
#ifdef WIN32
  FreeLibrary(hModule);
#else
  dlclose(hModule);
#endif /* WIN32 */
}


static void dltest_dlsym(DLTestModule hModule, const char *sym)
{
  void (*pFunc)();
#ifdef WIN32
  pFunc = (void (*)())GetProcAddress(hModule,sym);
  if ( !pFunc )
  {
    LPVOID pszMsg;
    FormatMessage(FORMAT_MESSAGE_ALLOCATE_BUFFER | FORMAT_MESSAGE_FROM_SYSTEM,
                  NULL,
                  GetLastError(),
                  MAKELANGID(LANG_NEUTRAL,SUBLANG_DEFAULT),
                  (LPTSTR) &pszMsg,
                  0,
                  NULL);
    printf("[%s][%d] ERROR: Could not find %s. %s\n",__FILE__,__LINE__,sym, (char*)pszMsg);
    LocalFree(pszMsg);
    FreeLibrary(hModule);
    exit(1);
  }
#else
  pFunc = (void (*)())dlsym(hModule,sym);
/* PAH - dlerror() is not a good indicator of success    */
/*		if ( (pError = dlerror()) != NULL )              */
  if ( !pFunc )
  {
    const char *pError;
    if ( (pError = dlerror()) != NULL )
      printf("[%s][%d] ERROR: %s\n Could not find %s\n",__FILE__,__LINE__,pError,sym);
    else
      printf("[%s][%d] ERROR: Could not find %s\n",__FILE__,__LINE__,sym);
    exit(1);
  }
#endif
  printf("[%s][%d] SUCCESS: Found %s\n",__FILE__,__LINE__,sym);
}
