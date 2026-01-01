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

INCLUDE_DIRECTORIES(${CMAKE_SOURCE_DIR}/test)

# put the test exe's in test/
SET(EXECUTABLE_OUTPUT_PATH "${CMAKE_BINARY_DIR}/test")

IF(EXPOSE_TOFIX_TESTS)
  ADD_DEFINITIONS(-DEXPOSE_TOFIX_TESTS)
ENDIF(EXPOSE_TOFIX_TESTS)


ENABLE_TESTING()

IF(NOT WIN32)
  SET(CMAKE_EXE_LINKER_FLAGS "${CMAKE_EXE_LINKER_FLAGS} ${ODBC_LINK_FLAGS}")
ENDIF(NOT WIN32)

FOREACH(FN my_basics.c my_blob.c my_bulk.c my_catalog1.c my_catalog2.c
    my_crash.c my_curext.c my_cursor.c my_datetime.c my_desc.c
    my_dyn_cursor.c my_error.c my_info.c my_keys.c my_param.c
    my_prepare.c my_relative.c my_result1.c my_result2.c my_scroll.c
    my_setup.c my_tran.c my_types.c my_unicode.c my_unixodbc.c
    my_use_result.c my_bug13766.c my_pooling.c my_auth.cc my_data.cc
    my_catalog3.cc my_options.cc my_param2.cc my_result3.cc)

  GET_FILENAME_COMPONENT(T ${FN} NAME_WE)

  IF(WIN32)
    ADD_EXECUTABLE(${T} ${FN} odbctap.h)
  ELSE(WIN32)
    ADD_EXECUTABLE(${T} ${FN})
  ENDIF(WIN32)

  add_dependencies(${T} generate_test_config_files)

  set_target_properties(${T} PROPERTIES FOLDER Tests)

  IF(MYSQL_CXX_LINKAGE)
    SET_TARGET_PROPERTIES(${T} PROPERTIES
          LINKER_LANGUAGE CXX
          LINK_FLAGS "${MYSQLODBCCONN_LINK_FLAGS_ENV} ${MYSQL_LINK_FLAGS}"
          COMPILE_FLAGS "${MYSQLODBCCONN_COMPILE_FLAGS_ENV} ${MYSQL_CXXFLAGS}")
  ENDIF(MYSQL_CXX_LINKAGE)

  INSTALL(TARGETS ${T} DESTINATION test COMPONENT tests)
  SET_TARGET_PROPERTIES(${T} PROPERTIES
      LINK_FLAGS "${MYSQLODBCCONN_LINK_FLAGS_ENV} ${MYSQL_LINK_FLAGS}")

  IF(WIN32)
    IF (WITH_NODEFAULTLIB)
      SET_TARGET_PROPERTIES(${T} PROPERTIES
        LINK_FLAGS_DEBUG "/NODEFAULTLIB:${WITH_NODEFAULTLIB}"
        LINK_FLAGS_RELWITHDEBINFO "/NODEFAULTLIB:${WITH_NODEFAULTLIB}"
        LINK_FLAGS_RELEASE "/NODEFAULTLIB:${WITH_NODEFAULTLIB}")
    ENDIF ()

  ENDIF(WIN32)

  IF(WIN32)
    IF(MYSQL8)
      TARGET_LINK_LIBRARIES(${T} ${ODBCLIB} ${ODBCINSTLIB} legacy_stdio_definitions.lib myodbc-util)
    ELSE(MYSQL8)
      TARGET_LINK_LIBRARIES(${T} ${ODBCLIB} ${ODBCINSTLIB} myodbc-util)
    ENDIF(MYSQL8)
  ELSE(WIN32)
    TARGET_LINK_LIBRARIES(${T} ${ODBC_LINK_FLAGS} ${ODBCINSTLIB} myodbc-util)
  ENDIF(WIN32)
  ADD_TEST(${T} ${T})

if(APPLE)
  set_property(TARGET ${T} PROPERTY BUILD_WITH_INSTALL_RPATH ON)
  set_property(TARGET ${T} APPEND PROPERTY INSTALL_RPATH "@loader_path")
elseif(NOT WIN32)
  set_property(TARGET ${T} PROPERTY BUILD_WITH_INSTALL_RPATH ON)
  set_property(TARGET ${T} APPEND PROPERTY INSTALL_RPATH "$ORIGIN")
endif()

ENDFOREACH(FN)

# Adding testsuites for testing driver without DM. May be useful to test behaviors that normally are initiated by DM only
# Separate testsuites for ansi and unicode driver are created - we can't use same test for both since there is no DM to convert
# data and to map calls. Excluding OS X so far, since direct linking there is problematic

set(skip_no_dm "1")

IF(NOT skip_no_dm)

IF(NOT ${CMAKE_SYSTEM_NAME} MATCHES "Darwin")

  FOREACH(CONNECTOR_DRIVER_TYPE_SHORT ${CONNECTOR_DRIVER_TYPES_SHORT})

    # Skipping unicode driver so far
    IF (NOT ${CONNECTOR_DRIVER_TYPE_SHORT} STREQUAL "w")
      SET(T "my_nodm${CONNECTOR_DRIVER_TYPE_SHORT}")

      IF(WIN32)
        ADD_EXECUTABLE(${T} ${T}.c odbctap.h)
      ELSE(WIN32)
        ADD_EXECUTABLE(${T} ${T}.c)
      ENDIF(WIN32)

      IF(MYSQL_CXX_LINKAGE)
        SET_TARGET_PROPERTIES(${T} PROPERTIES
              LINKER_LANGUAGE CXX
              LINK_FLAGS "${MYSQLODBCCONN_LINK_FLAGS_ENV} ${MYSQL_LINK_FLAGS}"
              COMPILE_FLAGS "${MYSQLODBCCONN_COMPILE_FLAGS_ENV} ${MYSQL_CXXFLAGS}")
      ENDIF(MYSQL_CXX_LINKAGE)

      INSTALL(TARGETS ${T} DESTINATION test COMPONENT tests)
      SET_TARGET_PROPERTIES(${T} PROPERTIES
          LINK_FLAGS "${MYSQLODBCCONN_LINK_FLAGS_ENV} ${MYSQL_LINK_FLAGS}")

      IF(WIN32)
        IF (MYSQLCLIENT_STATIC_LINKING)
          SET_TARGET_PROPERTIES(${T} PROPERTIES
            LINK_FLAGS_DEBUG "/NODEFAULTLIB:LIBCMTD /NODEFAULTLIB:LIBCMT"
            LINK_FLAGS_RELWITHDEBINFO "/NODEFAULTLIB:LIBCMT"
            LINK_FLAGS_RELEASE "/NODEFAULTLIB:LIBCMT")
        ENDIF (MYSQLCLIENT_STATIC_LINKING)
      ENDIF(WIN32)

      # Not quite clear atm whether ${ODBCINSTLIB} is needed
      TARGET_LINK_LIBRARIES(${T} myodbc8${CONNECTOR_DRIVER_TYPE_SHORT})
      ADD_TEST(${T} ${T})
    ENDIF (NOT ${CONNECTOR_DRIVER_TYPE_SHORT} STREQUAL "w")

  ENDFOREACH(CONNECTOR_DRIVER_TYPE_SHORT)

ENDIF(NOT ${CMAKE_SYSTEM_NAME} MATCHES "Darwin")

ENDIF(NOT skip_no_dm)

TARGET_LINK_LIBRARIES(my_basics ${CMAKE_THREAD_LIBS_INIT})


#
# Custom command and target generate_test_config_files to generate
# ODBC configuration files which define drivers and data sources used
# by our tests. These files are generated by
# cmake/generateinifiles.cmake script run at build time (only then
# the locations of the drivers are known). Here we prepare input variables
# for the script.
#

set(drivers_options)

foreach(idx RANGE 0 1)

  list(GET CONNECTOR_DRIVER_TYPE_LIST ${idx} driver_type)
  list(GET CONNECTOR_DRIVER_TYPES_SHORT ${idx} driver_type_short)
  set(driver_tgt "myodbc8${driver_type_short}")

  if(TARGET ${driver_tgt})

    math(EXPR DRIVERS_COUNT "${idx}+1")

    list(APPEND drivers_options
      -DDRIVER_LOCATION${DRIVERS_COUNT}=$<TARGET_FILE:${driver_tgt}>
      -DCONNECTOR_DRIVER_TYPE${DRIVERS_COUNT}=${driver_type}
      -DCONNECTOR_DRIVER_TYPE_SHORT${DRIVERS_COUNT}=${driver_type_short}
    )

  endif()

endforeach()

add_custom_command(
  OUTPUT "odbcinst.ini" "odbc.ini"
  COMMAND ${CMAKE_COMMAND}
    -DBINARY_DIR=${CMAKE_CURRENT_BINARY_DIR}
    -DCONNECTOR_MAJOR=${CONNECTOR_MAJOR}
    -DCONNECTOR_MINOR=${CONNECTOR_MINOR}
    -DDRIVERS_COUNT=${DRIVERS_COUNT}
    ${drivers_options}
    -P ${CMAKE_CURRENT_SOURCE_DIR}/cmake/generateinifiles.cmake
  WORKING_DIRECTORY ${CMAKE_CURRENT_SOURCE_DIR}
)

add_custom_target(generate_test_config_files
  DEPENDS "odbcinst.ini" "odbc.ini"
)

set_target_properties(generate_test_config_files PROPERTIES FOLDER Misc)


INSTALL(FILES
	${CMAKE_CURRENT_BINARY_DIR}/CTestTestfile.cmake
	${CMAKE_CURRENT_BINARY_DIR}/odbc.ini
	${CMAKE_CURRENT_BINARY_DIR}/odbcinst.ini
        DESTINATION test COMPONENT tests)

INSTALL(DIRECTORY
       ${PROJECT_BINARY_DIR}/test/Debug/
       DESTINATION test
       CONFIGURATIONS Debug
       FILES_MATCHING PATTERN *.pdb
)
