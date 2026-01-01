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

# At least in CMake 2.8.12.2 the file "Dawin.cmake" defines
# CMAKE_C_CREATE_SHARED_LIBRARY without honoring
# CMAKE_SHARED_LINKER_FLAGS. I also failed to modify
# CMAKE_C_CREATE_SHARED_LIBRARY to include it. So as a
# work-around we add ODBC_LIB_DIR to the link directories.

IF(CMAKE_SYSTEM_NAME MATCHES "Darwin")
  LINK_DIRECTORIES(${ODBC_LIB_DIR})
  IF(ODBCINST_LIB_DIR)
    LINK_DIRECTORIES(${ODBCINST_LIB_DIR})
  ENDIF()
ENDIF()

SET(DRIVER_INDEX 0)
SET(DRIVER_LOCATION2 "")
SET(CONNECTOR_DRIVER_TYPE2 "")
SET(CONNECTOR_DRIVER_TYPE_SHORT2 "")

WHILE(${DRIVER_INDEX} LESS ${DRIVERS_COUNT})

  LIST(GET IS_UNICODE_DRIVER ${DRIVER_INDEX} UNICODE)
  LIST(GET CONNECTOR_DRIVER_TYPES_SHORT ${DRIVER_INDEX} CONNECTOR_DRIVER_TYPE_SHORT)
  LIST(GET CONNECTOR_DRIVER_TYPE_LIST ${DRIVER_INDEX} DRIVER_TYPE)

  SET(DRIVER_NAME "myodbc8${CONNECTOR_DRIVER_TYPE_SHORT}")

  SET(DRIVER_SRCS
    catalog.cc catalog_no_i_s.cc connect.cc cursor.cc desc.cc dll.cc error.cc execute.cc
    handle.cc info.cc driver.cc options.cc parse.cc prepare.cc results.cc transact.cc
    my_prepared_stmt.cc my_stmt.cc utility.cc)

  IF(UNICODE)
    SET(DRIVER_SRCS ${DRIVER_SRCS} unicode.cc)
    SET(WIDECHARCALL "W")
  ELSE(UNICODE)
    SET(DRIVER_SRCS ${DRIVER_SRCS} ansi.cc)
    SET(WIDECHARCALL "")
  ENDIF(UNICODE)

  INCLUDE_DIRECTORIES(../util)

  IF(WIN32)
    # Headers added for convenience of VS users
    CONFIGURE_FILE(${CMAKE_SOURCE_DIR}/driver/driver.def.cmake ${CMAKE_SOURCE_DIR}/driver/driver${CONNECTOR_DRIVER_TYPE_SHORT}.def @ONLY)
    CONFIGURE_FILE(${CMAKE_SOURCE_DIR}/driver/driver.rc.cmake ${CMAKE_SOURCE_DIR}/driver/driver${CONNECTOR_DRIVER_TYPE_SHORT}.rc @ONLY)
    SET(DRIVER_SRCS ${DRIVER_SRCS} driver${CONNECTOR_DRIVER_TYPE_SHORT}.def driver${CONNECTOR_DRIVER_TYPE_SHORT}.rc catalog.h driver.h
                                   error.h myutil.h parse.h ../MYODBC_MYSQL.h ../MYODBC_CONF.h ../MYODBC_ODBC.h)
  ENDIF(WIN32)

  # Note: We build driver as a MODULE, because this is what it really is
  # -- it is not a shared library meant to be linked to the user code
  # but a dynamic module that will be loaded by ODBC manager. One
  # consequence of this is that on Windows import libraries will not
  # be generated nor installed.

  ADD_LIBRARY(${DRIVER_NAME} MODULE ${DRIVER_SRCS})
  
  ADD_COVERAGE(${DRIVER_NAME})

  IF(WIN32)
    INSTALL(TARGETS ${DRIVER_NAME}
      CONFIGURATIONS Release RelWithDebInfo
      DESTINATION ${LIB_SUBDIR})

    INSTALL(TARGETS ${DRIVER_NAME}
      CONFIGURATIONS Debug
      DESTINATION "${LIB_SUBDIR}/debug")
  ELSE()
    INSTALL(TARGETS ${DRIVER_NAME} DESTINATION ${LIB_SUBDIR})
  ENDIF()

  IF(NOT ${CMAKE_SYSTEM_NAME} MATCHES "Darwin")
    SET_TARGET_PROPERTIES(${DRIVER_NAME} PROPERTIES
          LINK_FLAGS "${MYSQLODBCCONN_LINK_FLAGS_ENV} ${MYSQL_LINK_FLAGS}")
  ELSE(NOT ${CMAKE_SYSTEM_NAME} MATCHES "Darwin")
    SET_TARGET_PROPERTIES(${DRIVER_NAME} PROPERTIES
          LINK_FLAGS "${MYSQLODBCCONN_LINK_FLAGS_ENV} ${MYSQL_LINK_FLAGS}") # -flto
  ENDIF()

  IF(UNICODE)
    SET_TARGET_PROPERTIES(${DRIVER_NAME} PROPERTIES
      COMPILE_DEFINITIONS MYODBC_UNICODEDRIVER)
  ENDIF(UNICODE)

  IF(WIN32)
    IF(CMAKE_BUILD_TYPE STREQUAL "Debug")
      LINK_DIRECTORIES(${MYSQL_DIR}/lib/debug)
      LINK_DIRECTORIES(${MYSQL_DIR}/lib)
    ELSEIF(CMAKE_BUILD_TYPE STREQUAL "")
      LINK_DIRECTORIES(${MYSQL_DIR}/lib/opt)
      LINK_DIRECTORIES(${MYSQL_DIR}/lib)
    ENDIF(CMAKE_BUILD_TYPE STREQUAL "Debug")

    IF (WITH_NODEFAULTLIB)
      SET_TARGET_PROPERTIES(${DRIVER_NAME} PROPERTIES
        LINK_FLAGS_DEBUG "/NODEFAULTLIB:${WITH_NODEFAULTLIB}"
        LINK_FLAGS_RELWITHDEBINFO "/NODEFAULTLIB:${WITH_NODEFAULTLIB}"
        LINK_FLAGS_RELEASE "/NODEFAULTLIB:${WITH_NODEFAULTLIB}")
    ENDIF ()

    IF(MYSQL8)
      TARGET_LINK_LIBRARIES(${DRIVER_NAME} myodbc-util
            ${MYSQL_CLIENT_LIBS} ws2_32 ${ODBCINSTLIB} ${SECURE32_LIB} legacy_stdio_definitions.lib)
    ELSE(MYSQL8)
      TARGET_LINK_LIBRARIES(${DRIVER_NAME} myodbc-util
            ${MYSQL_CLIENT_LIBS} ws2_32 ${ODBCINSTLIB} ${SECURE32_LIB})
    ENDIF(MYSQL8)

    TARGET_LINK_LIBRARIES(${DRIVER_NAME} Dnsapi)

  ELSE(WIN32)

    IF(DEV_STUDIO_RUNTIME_DIR)
      FOREACH(__runtime_dir IN LISTS DEV_STUDIO_RUNTIME_DIR)
        SET(CMAKE_SHARED_LINKER_FLAGS "${CMAKE_SHARED_LINKER_FLAGS} -R${__runtime_dir}")
      ENDFOREACH()
    ENDIF()

    SET(CMAKE_SHARED_LINKER_FLAGS "${CMAKE_SHARED_LINKER_FLAGS} ${ODBC_DRIVER_LINK_FLAGS}")
    TARGET_LINK_LIBRARIES(${DRIVER_NAME}
                          ${MYSQL_CLIENT_LIBS} ${CMAKE_THREAD_LIBS_INIT} m)
    TARGET_LINK_LIBRARIES(${DRIVER_NAME} myodbc-util)

    IF(NOT ${CMAKE_SYSTEM_NAME} MATCHES "FreeBSD")
      TARGET_LINK_LIBRARIES(${DRIVER_NAME} resolv)
    ENDIF(NOT ${CMAKE_SYSTEM_NAME} MATCHES "FreeBSD")


    INCLUDE_DIRECTORIES(${DL_INCLUDES})

  ENDIF(WIN32)

  IF (MYSQL_CXX_LINKAGE)
    SET_TARGET_PROPERTIES(${DRIVER_NAME} PROPERTIES
          LINKER_LANGUAGE CXX
          COMPILE_FLAGS "${MYSQLODBCCONN_COMPILE_FLAGS_ENV} ${MYSQL_CXXFLAGS}")
  ENDIF (MYSQL_CXX_LINKAGE)

  set_property(TARGET ${DRIVER_NAME} PROPERTY BUILD_WITH_INSTALL_RPATH ON)

  if(APPLE)
    set_property(TARGET ${DRIVER_NAME} APPEND PROPERTY INSTALL_RPATH "@loader_path")
    set_property(TARGET ${DRIVER_NAME} APPEND PROPERTY INSTALL_RPATH "@loader_path/private")
    set_property(TARGET ${DRIVER_NAME} APPEND PROPERTY INSTALL_RPATH "/usr/local/iODBC/lib")
    set_property(TARGET ${DRIVER_NAME} APPEND PROPERTY INSTALL_RPATH "/usr/local/var/homebrew/linked/libiodbc/lib")
    mangle_osx_rpaths(${DRIVER_NAME})
  elseif(NOT WIN32)
    set_property(TARGET ${DRIVER_NAME} APPEND PROPERTY INSTALL_RPATH "$ORIGIN")
    set_property(TARGET ${DRIVER_NAME} APPEND PROPERTY INSTALL_RPATH "$ORIGIN/private")
  endif()

  # FIXME: RPATH not set anywhere...
  #MESSAGE("Setting RPATH for ${DRIVER_NAME}")

  MATH(EXPR DRIVER_INDEX "${DRIVER_INDEX} + 1")

ENDWHILE(${DRIVER_INDEX} LESS ${DRIVERS_COUNT})
