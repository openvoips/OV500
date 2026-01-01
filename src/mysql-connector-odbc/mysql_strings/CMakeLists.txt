# Copyright (c) 2006, 2018, Oracle and/or its affiliates. All rights reserved.
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License, version 2.0, as
# published by the Free Software Foundation.
#
# This program is also distributed with certain software (including
# but not limited to OpenSSL) that is licensed under separate terms,
# as designated in a particular file or component or in included license
# documentation. The authors of MySQL hereby grant you an
# additional permission to link the program and your derivative works
# with the separately licensed software that they have included with
# MySQL.
#
# Without limiting anything contained in the foregoing, this file,
# which is part of MySQL Connector/ODBC, is also subject to the
# Universal FOSS Exception, version 1.0, a copy of which can be found at
# http://oss.oracle.com/licenses/universal-foss-exception.
#
# This program is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License, version 2.0, for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

SET_PROPERTY(DIRECTORY PROPERTY INCLUDE_DIRECTORIES ${CMAKE_SOURCE_DIR} ${CMAKE_SOURCE_DIR}/include/mysql-8.0)

SET(STRINGS_SOURCES
  ctype-big5.cc
  ctype-bin.cc
  ctype.cc
  ctype-cp932.cc
  ctype-czech.cc
  ctype-eucjpms.cc
  ctype-euc_kr.cc
  ctype-extra.cc
  ctype-gb18030.cc
  ctype-gb2312.cc
  ctype-gbk.cc
  ctype-latin1.cc
  ctype-mb.cc
  ctype-simple.cc
  ctype-sjis.cc
  ctype-tis620.cc
  ctype-uca.cc
  ctype-ucs2.cc
  ctype-ujis.cc
  ctype-utf8.cc
  ctype-win1250ch.cc
  decimal.cc
  dtoa.cc
  int2str.cc
  my_strchr.cc
  my_strtoll10.cc
  str2int.cc
  str_alloc.cc
  strcont.cc
  strmake.cc
  strxmov.cc
  strxnmov.cc
  xml.cc
)

SET(CMAKE_POSITION_INDEPENDENT_CODE ON)

IF(WIN32)
  ADD_DEFINITIONS(-DWIN32_LEAN_AND_MEAN)
  ADD_DEFINITIONS(-DNOMINMAX)
ENDIF()

IF(CMAKE_CXX_COMPILER_ID MATCHES "Clang" AND
    CMAKE_CXX_COMPILER_VERSION VERSION_EQUAL "4.0")
  ADD_COMPILE_FLAGS(dtoa.cc COMPILE_FLAGS "-fno-strict-aliasing")
ENDIF()

# Generation of uca900 tables

ADD_EXECUTABLE(uca9dump uca9-dump.cc)
set_target_properties(uca9dump PROPERTIES FOLDER "Misc")

SET(ZH_HANS_SRC_FILE ${CMAKE_CURRENT_SOURCE_DIR}/lang_data/zh_hans.txt)
SET(ZH_HANS_DST_FILE ${CMAKE_CURRENT_BINARY_DIR}/uca900_zh_tbls.cc)
SET(JA_HANS_SRC_FILE ${CMAKE_CURRENT_SOURCE_DIR}/lang_data/ja_hans.txt)
SET(JA_HANS_DST_FILE ${CMAKE_CURRENT_BINARY_DIR}/uca900_ja_tbls.cc)
ADD_CUSTOM_COMMAND(OUTPUT ${ZH_HANS_DST_FILE}
                          ${JA_HANS_DST_FILE}
                   COMMAND uca9dump zh
                     --in_file=${ZH_HANS_SRC_FILE}
                     --out_file=${ZH_HANS_DST_FILE}
                   COMMAND uca9dump ja
                     --in_file=${JA_HANS_SRC_FILE}
                     --out_file=${JA_HANS_DST_FILE}
                   DEPENDS uca9dump ${ZH_HANS_SRC_FILE} ${JA_HANS_SRC_FILE}
                  )

SET_SOURCE_FILES_PROPERTIES(
  ${JA_HANS_DST_FILE} ${ZH_HANS_DST_FILE}
  PROPERTIES GENERATED TRUE
  )

LIST(APPEND STRINGS_SOURCES ${JA_HANS_DST_FILE} ${ZH_HANS_DST_FILE})

# Avoid dependencies on perschema data defined in mysys
ADD_DEFINITIONS(-DDISABLE_MYSQL_THREAD_H)
ADD_LIBRARY(mysql_strings ${STRINGS_SOURCES})
set_target_properties(mysql_strings PROPERTIES FOLDER "Misc")

IF(NOT WIN32)
  SET_TARGET_PROPERTIES(mysql_strings PROPERTIES COMPILE_FLAGS ${CMAKE_SHARED_LIBRARY_C_FLAGS})
ENDIF()
