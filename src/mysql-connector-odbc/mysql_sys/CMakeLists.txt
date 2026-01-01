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

SET(SYS_SOURCES array.cc charset-def.cc charset.cc errors.cc list.cc
                  mf_dirname.cc mf_pack.cc my_access.cc my_alloc.cc my_file.cc
                  my_error.cc my_fstream.cc my_getwd.cc my_init.cc my_lib.cc my_malloc.cc my_mess.cc
                  my_once.cc my_open.cc my_read.cc my_static.cc my_thread.cc my_thr_init.cc
                  psi_noop.cc sql_chars.cc my_string.cc thr_cond.cc thr_mutex.cc
                  my_time.cc my_systime.cc
                  #my_getsystime.cc
)

IF (WIN32)
  LIST(APPEND SYS_SOURCES
    my_winerr.cc
    my_winfile.cc
  )
ENDIF()

SET(CMAKE_POSITION_INDEPENDENT_CODE ON)

ADD_LIBRARY(mysql_sys ${SYS_SOURCES})
set_target_properties(mysql_sys PROPERTIES FOLDER "Misc")

IF(WIN32)
  ADD_DEFINITIONS(-DWIN32_LEAN_AND_MEAN)
  ADD_DEFINITIONS(-DNOMINMAX)
ENDIF()

# Need explicit pthread for gcc -fsanitize=address
IF(CMAKE_USE_PTHREADS_INIT AND CMAKE_C_FLAGS MATCHES "-fsanitize=")
  TARGET_LINK_LIBRARIES(sys pthread)
ENDIF()

