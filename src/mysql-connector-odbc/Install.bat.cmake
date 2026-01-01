@ECHO OFF
REM Copyright (c) 2007, 2024, Oracle and/or its affiliates.
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
REM \brief  Register Connector/ODBC driver with ODBC system
REM   
REM         This exists for those working with the Windows 
REM         source distribution or with installer-less 
REM         binary distribution.
REM
REM         Name under which the driver should be registered
REM         can be specified as first parameter. It should be
REM         a single word (no spaces).
REM
REM \sa     INSTALL.win
REM
REM #########################################################

REM # SETLOCAL prevents the variables set in this script to
REM # be exported to the environment and pollute it
SETLOCAL

SET    driver_name=%*
SET    driver_lib=myodbc8
SET    driver_lib_setup=myodbc8S
SET    installer=myodbc-installer
SET    do_pause=no

Net session >nul 2>&1

IF %ERRORLEVEL% EQU 0 (
  ECHO Running as Admin
  IF "%CD%" == "%WINDIR%\system32" (
    REM When running as admin the script is in a wrong directory
    ECHO Changing the directory...
    %~d0
    CD %~p0
    SET do_pause=yes
  )
  GOTO :doProcessing
) ELSE (
  ECHO Requesting Admin
  IF "%driver_name%" == "" (
    PowerShell start-process -FilePath '%~0' -verb runas
  ) else (
    PowerShell start-process -FilePath '%~0' -ArgumentList {"%driver_name%"} -verb runas
  )
)

EXIT /b 1

:doProcessing
IF "%~1" == "" GOTO :doFindDriver
SET  driver_name=%*

IF NOT @DRIVERS_COUNT@ == 1 ECHO NOTE: " ANSI" or " Unicode" will be added to the driver name

:doFindDriver
REM # Find driver location
SET libdir=none
FOR %%G IN (. lib lib\release lib\relwithdebinfo lib\debug) DO CALL :subCheckLibDir %%G

IF "%libdir%" == "none" GOTO :errorNoDrivers
REM ECHO "libdir = %libdir%"

REM # Find the installer utility

REM # Try to find it in the build location
CALL :subFindBinDir "%libdir%"
SET myodbc_installer=%bindir%\%installer%.exe
IF EXIST "%myodbc_installer%" GOTO :register

REM # Try some other reasonable locations
SET myodbc_installer=bin\%installer%.exe
IF EXIST "%myodbc_installer%" GOTO :register
SET myodbc_installer=.\%installer%.exe
IF EXIST "%myodbc_installer%" GOTO :register

REM # Try if it is in the path
SET myodbc_installer=%installer%.exe
%myodbc_installer% >nul 2>nul
REM # "Command not found" generates error 9009
IF NOT ERRORLEVEL 9000 GOTO :doRegister

GOTO :errorNoInstaller

:register
REM ECHO myodbc_installer: %myodbc_installer%

REM # Abort if driver is already registered
FOR %%d IN (@CONNECTOR_DRIVER_TYPE@) DO CALL :registerIfNotFound %%d

REM # All is well if we got here
goto :doSuccess

:registerIfNotFound

ECHO Registering %1 driver

IF "%driver_name%" == "" SET name="MySQL ODBC @CONNECTOR_MAJOR@.@CONNECTOR_MINOR@ %1 Driver"
IF NOT "%driver_name%" == "" IF NOT @DRIVERS_COUNT@ == 1 SET name="%driver_name% %1"

IF %1 == Unicode SET lib=%driver_lib%w.dll
IF %1 == ANSI    SET lib=%driver_lib%a.dll

ECHO Checking if %name% is not already registered

%myodbc_installer% -d -l -n %name% 2>nul:

IF NOT ERRORLEVEL 1 GOTO :errorDriverInstalled

ECHO Registering %name%
%myodbc_installer% -d -a -n %name% -t "DRIVER=%libdir%\%lib%;SETUP=%libdir%\%driver_lib_setup%.dll"
REM # If we have error on registering 1 driver - 99.9% we will have it with other as well
IF ERRORLEVEL 1 GOTO :errorRegisterDriver

goto :eof

REM ######
REM # A subroutine to check if given location
REM # (relative to working dir) contains the drivers.
REM ######
:subCheckLibDir
REM # Skip check if a good libdir was already found
IF NOT "%libdir%" == "none" GOTO :eof
SET libdir=%CD%\%1
IF "%1" == "." (
  SET libdir=%CD%
) ELSE (
   SET libdir=%CD%\%1
)
IF NOT EXIST "%libdir%\%driver_lib%a.dll"      GOTO :wrongLibDir
IF NOT EXIST "%libdir%\%driver_lib%w.dll"      GOTO :wrongLibDir
IF NOT EXIST "%libdir%\%driver_lib_setup%.dll" GOTO :wrongLibDir
REM ECHO Libdir (%libdir%) is OK.
GOTO :eof
:wrongLibDir
REM ECHO Libdir (%libdir%) is wrong.
SET libdir=none
GOTO :eof

REM ######
REM # A subroutine to compute bindir of the form 
REM # C:\current\working\directory\bin\XXX where XXX is 
REM # the last component of libdir, such as Release, Debug etc.
REM # The libdir should be given as the first argument %1.
REM # Construct %~n1 is used which returns the last component 
REM # ("file name") of the path stored in %1.
REM ######
:subFindBinDir
SET bindir=%CD%\bin\%~n1
GOTO :eof

:doSuccess
ECHO ^+-----------------------------------------------------^+
ECHO ^| DONE                                                ^|
ECHO ^+-----------------------------------------------------^+
ECHO ^|                                                     ^|
ECHO ^| Hopefully things went well; the Connector/ODBC      ^|
ECHO ^| driver has been registered.                         ^|
ECHO ^|                                                     ^|
ECHO ^| Connector/ODBC is ready to use.                     ^|
ECHO ^|                                                     ^|
ECHO ^| The most common thing to do next is to go to the    ^|
ECHO ^| Control Panel and find the ODBC Administrator -     ^|
ECHO ^| then use it to create a Data Source Name (DSN)      ^|
ECHO ^| so you (and your application) can connect to a      ^|
ECHO ^| MySQL server.                                       ^|
ECHO ^|                                                     ^|
ECHO ^| Alternatively you can use the MyODBC Installer      ^|
ECHO ^| utility to define data sources.                     ^|
ECHO ^|                                                     ^|
ECHO ^+-----------------------------------------------------^+
IF %do_pause% == yes PAUSE
EXIT /B 0

:errorNoDrivers
ECHO ^+-----------------------------------------------------^+
ECHO ^| ERROR                                               ^|
ECHO ^+-----------------------------------------------------^+
ECHO ^|                                                     ^|
ECHO ^| Could not find Connector/ODBC drivers. Have you run ^|
ECHO ^| this script from the installation directory?        ^|
ECHO ^|                                                     ^|
ECHO ^+-----------------------------------------------------^+
IF %do_pause% == yes PAUSE
EXIT /B 1

:errorNoInstaller
ECHO ^+-----------------------------------------------------^+
ECHO ^| ERROR                                               ^|
ECHO ^+-----------------------------------------------------^+
ECHO ^|                                                     ^|
ECHO ^| Could not find the MyODBC Installer utility. Run    ^|
ECHO ^| this script from the installation directory.        ^|
ECHO ^|                                                     ^|
ECHO ^+-----------------------------------------------------^+
IF %do_pause% == yes PAUSE
EXIT /B 1

:errorDriverInstalled
ECHO ^+-----------------------------------------------------^+
ECHO ^| ERROR                                               ^|
ECHO ^+-----------------------------------------------------^+
ECHO ^|                                                     ^|
ECHO ^| There is already driver registered with such name   ^|
ECHO ^|                                                     ^|
ECHO ^+-----------------------------------------------------^+
IF %do_pause% == yes PAUSE
EXIT 1

:errorRegisterDriver
ECHO ^+-----------------------------------------------------^+
ECHO ^| ERROR                                               ^|
ECHO ^+-----------------------------------------------------^+
ECHO ^|                                                     ^|
ECHO ^| Could not register the driver.                      ^|
ECHO ^|                                                     ^|
ECHO ^+-----------------------------------------------------^+
IF %do_pause% == yes PAUSE
EXIT 1
