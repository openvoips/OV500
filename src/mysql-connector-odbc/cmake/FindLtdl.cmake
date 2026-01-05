# Copyright (c) 2011, 2024, Oracle and/or its affiliates.
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

SET(LTDL_LIBS "ltdl")

IF(LTDL_PATH)

  SET(LTDL_INCLUDES "${LTDL_PATH}/include")
  SET(LTDL_LIB_DIR "${LTDL_PATH}/lib")
  SET(LTDL_LFLAGS "-L${LTDL_LIB_DIR}")

  IF(NOT LTDL_LINK_DYNAMIC AND EXISTS "${LTDL_LIB_DIR}/libltdl.a")
    SET(LTDL_LIBS "${LTDL_LIB_DIR}/libltdl.a")
  ENDIF(NOT LTDL_LINK_DYNAMIC AND EXISTS "${LTDL_LIB_DIR}/libltdl.a")

ELSE(LTDL_PATH)

  INCLUDE (CheckIncludeFiles)
  CHECK_INCLUDE_FILES(ltdl.h HAVE_LTDL_H)

  IF(NOT HAVE_LTDL_H)
    MESSAGE(FATAL_ERROR "ltdl.h could not be found")
  ENDIF(NOT HAVE_LTDL_H)

  INCLUDE (CheckLibraryExists)
  CHECK_LIBRARY_EXISTS(ltdl lt_dlopen "" HAVE_LTDL_LIB)

  IF(NOT HAVE_LTDL_LIB)
    MESSAGE(FATAL_ERROR "ltdl lib could not be found")
  ENDIF(NOT HAVE_LTDL_LIB)

ENDIF(LTDL_PATH)

TRY_COMPILE(COMPILE_RESULT ${CMAKE_SOURCE_DIR}/cmake/CMakeTmp ${CMAKE_SOURCE_DIR}/cmake/needdl.c
            CMAKE_FLAGS -DINCLUDE_DIRECTORIES=${LTDL_INCLUDES}
            -DLINK_DIRECTORIES=${LTDL_LIB_DIR}
            -DLINK_LIBRARIES=${LTDL_LIBS}
            OUTPUT_VARIABLE     COMPILE_OUTPUT)

# Perhaps to add try_compile with dl would be safer
IF(COMPILE_RESULT) 
  MESSAGE(STATUS "Checking if need to link dl - FALSE")
ElSE(COMPILE_RESULT) 
  MESSAGE(STATUS "Checking if need to link dl - TRUE")
  SET(LTDL_LIBS ${LTDL_LIBS} "dl")
ENDIF(COMPILE_RESULT)
