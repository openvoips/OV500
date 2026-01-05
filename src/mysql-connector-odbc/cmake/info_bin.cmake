# Copyright (c) 2007, 2024, Oracle and/or its affiliates.
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License, version 2.0, as
# published by the Free Software Foundation.
#
# This program is designed to work with certain software (including
# but not limited to OpenSSL) that is licensed under separate terms, as
# designated in a particular file or component or in included license
# documentation. The authors of MySQL hereby grant you an additional
# permission to link the program and your derivative works with the
# separately licensed software that they have either included with
# the program or referenced in the documentation.
#
# Without limiting anything contained in the foregoing, this file,
# which is part of Connector/ODBC, is also subject to the
# Universal FOSS Exception, version 1.0, a copy of which can be found at
# https://oss.oracle.com/licenses/universal-foss-exception.
#
# This program is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License, version 2.0, for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

##########################################################################

FUNCTION(GENERATE_INFO_BIN)

  IF(DEFINED ENV{PRODUCT_ID} AND "$ENV{PRODUCT_ID}" MATCHES "source-dist")
    MESSAGE("Generating INFO_BIN is skipped for the source package")
    RETURN()
  ENDIF()

  MESSAGE("Generating INFO_BIN")

  #
  # Note: In later versions of cmake, if variable SOURCE_DATE_EPOCH is set
  # in the environment, its value is used as a timestamp instead of the
  # current date. This is used by Debian packaging tools, for example, to
  # achieve "repeatable builds". See also packaging/debian/CMakeLists.txt
  #

  string(TIMESTAMP INFO_DATE "%Y-%m-%d")

  IF (CMAKE_BUILD_TYPE)
    SET(INFO_BUILD_TYPE ${CMAKE_BUILD_TYPE})
  ELSE()
    SET(INFO_BUILD_TYPE "Debug")
  ENDIF()

  IF (MYSQLCLIENT_STATIC_LINKING)
    SET(INFO_LINKING_TYPE "Static")
  ELSE()
    SET(INFO_LINKING_TYPE "Dynamic")
  ENDIF()

  IF (WIN32)
    IF(STATIC_MSVCRT)
      SET(INFO_MSVCRT "msvc-runtime-linkage :  Static (/MT)\n")
    ELSE()
      SET(INFO_MSVCRT "msvc-runtime-linkage :  Dynamic (/MD)\n")
    ENDIF()
  ENDIF()

  IF(DEFINED ENV{INFO_ODBC_MANAGER})
      SET(INFO_MSVCRT "odbc-manager         :  $ENV{INFO_ODBC_MANAGER}\n")
  ENDIF()
  IF(INFO_ODBC_MANAGER)
      SET(INFO_MSVCRT "odbc-manager         :  ${INFO_ODBC_MANAGER}\n")
  ENDIF()

  IF(DEFINED ENV{INFO_SSL_LIBRARY})
      SET(INFO_SSL "ssl-library          :  $ENV{INFO_SSL_LIBRARY}\n")
  ENDIF()
  IF(INFO_SSL_LIBRARY)
      SET(INFO_SSL "ssl-library          :  ${INFO_SSL_LIBRARY}\n")
  ENDIF()

  IF(CMAKE_OSX_DEPLOYMENT_TARGET)
    SET(INFO_MACOS_TARGET "macos-target         :  ${CMAKE_OSX_DEPLOYMENT_TARGET}\n")
  ENDIF()

  IF(APPLE)
    EXECUTE_PROCESS(COMMAND sw_vers -productVersion OUTPUT_VARIABLE MACOS_PRODUCT_VERSION)
    IF(MACOS_PRODUCT_VERSION)
      SET(INFO_MACOS_VERSION "macos-version        :  ${MACOS_PRODUCT_VERSION}")
    ENDIF()
    EXECUTE_PROCESS(COMMAND sw_vers -buildVersion OUTPUT_VARIABLE MACOS_BUILD_VERSION)
    IF(MACOS_BUILD_VERSION)
      SET(INFO_MACOS_BUILD "macos-build          :  ${MACOS_BUILD_VERSION}")
    ENDIF()
  ENDIF()

  IF(GTK2_VERSION OR GTK3_VERSION)
    SET(INFO_GTK "gtk-version          :  ${GTK2_VERSION}${GTK3_VERSION}\n")
  ENDIF()

  CONFIGURE_FILE(INFO_BIN.in "${CMAKE_SOURCE_DIR}/INFO_BIN")
  install(FILES "${CMAKE_SOURCE_DIR}/INFO_BIN" DESTINATION . COMPONENT Readme)
ENDFUNCTION()


FUNCTION(GENERATE_INFO_SRC)
  MESSAGE("Generating INFO_SRC")

  IF (NOT EXISTS INFO_SRC)
    SET(INFO_VERSION ${CONNECTOR_NUMERIC_VERSION})

    find_program(GIT_FOUND NAMES git)

    IF(GIT_FOUND AND IS_DIRECTORY "${PROJECT_SOURCE_DIR}/.git")
      execute_process(
        COMMAND git symbolic-ref --short HEAD
        ERROR_QUIET
        OUTPUT_VARIABLE GIT_BRANCH_NAME
        RESULT_VARIABLE _result_code3
        OUTPUT_STRIP_TRAILING_WHITESPACE
      )

      IF (GIT_BRANCH_NAME)
        execute_process(
          COMMAND git log -1 --format=%cd --date=short
          ERROR_QUIET
          OUTPUT_VARIABLE GIT_DATE
          RESULT_VARIABLE _result_code4
          OUTPUT_STRIP_TRAILING_WHITESPACE
        )

        IF(GIT_DATE)
          SET(INFO_DATE "date                 :  ${GIT_DATE}\n")
        ENDIF()

        IF(GIT_BRANCH_NAME)
          SET(INFO_BRANCH "branch               :  ${GIT_BRANCH_NAME}\n")
        ENDIF()


        execute_process(
          COMMAND git rev-parse HEAD
          ERROR_QUIET
          OUTPUT_VARIABLE GIT_COMMIT
          RESULT_VARIABLE _result_code5
          OUTPUT_STRIP_TRAILING_WHITESPACE
        )

        IF(GIT_COMMIT)
          SET(INFO_COMMIT "commit               :  ${GIT_COMMIT}\n")
        ENDIF()

        execute_process(
          COMMAND git rev-parse --short HEAD
          ERROR_QUIET
          OUTPUT_VARIABLE GIT_SHORT
          RESULT_VARIABLE _result_code6
          OUTPUT_STRIP_TRAILING_WHITESPACE
        )

        IF(GIT_SHORT)
          SET(INFO_SHORT "short                :  ${GIT_SHORT}\n")
        ENDIF()
      ENDIF()
    ELSE()
      # git local repository does not exist, but the env variables might be set
      IF(DEFINED ENV{PUSH_REVISION})
          SET(INFO_COMMIT "commit               :  $ENV{PUSH_REVISION}\n")
          STRING(SUBSTRING $ENV{PUSH_REVISION} 0 8 GIT_SHORT)
          SET(INFO_SHORT "short                :  ${GIT_SHORT}\n")
      ENDIF()

      IF(DEFINED ENV{BRANCH_SOURCE})
          STRING(REGEX MATCH " [^ ]+" GIT_BRANCH_NAME $ENV{BRANCH_SOURCE})
          SET(INFO_BRANCH "branch               : ${GIT_BRANCH_NAME}\n")
      ENDIF()
    ENDIF()

    CONFIGURE_FILE(INFO_SRC.in "${CMAKE_SOURCE_DIR}/INFO_SRC")
  ENDIF()
  install(FILES "${CMAKE_SOURCE_DIR}/INFO_SRC" DESTINATION . COMPONENT Readme)
ENDFUNCTION()

GENERATE_INFO_SRC()
GENERATE_INFO_BIN()