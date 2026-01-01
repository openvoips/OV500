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

add_definitions(-DUNICODE -D_UNICODE)

if(UNICODE OR NOT ANSI)
  add_definitions(-DDRIVER_UNICODE)
else(UNICODE OR NOT ANSI)
  add_definitions(-DDRIVER_ANSI)
endif(UNICODE OR NOT ANSI)

# Sources

if(WIN32)

  set(SETUP_SRCS
    ConfigDSN.cc
    callbacks.cc
    setupgui.h
    utils.cc
    myodbc8S.def
    windows/main.cpp
    windows/odbcdialogparams.cpp
    windows/odbcdialogparams.h
    windows/odbcdialogparams.rc
    windows/resource.h
    windows/TabCtrl.cpp
    windows/TabCtrl.h
    windows/tooltip.cpp
    windows/connector_odbc_header.bmp
  )

  set(PLATFORM_LIBS comctl32 legacy_stdio_definitions.lib)

else(WIN32)

  # Sources for the main setup module
  SET(SETUP_SRCS gtk/setup_loader.cc
                 gtk/ODBCINSTGetProperties.cc)

  # Sources for the GTK setup module
  set(SETUP_SRCS_GTK
    ConfigDSN.cc
    callbacks.cc
    setupgui.h
    utils.cc
    gtk/odbcdialogparams.cc
  )

  set(PLATFORM_LIBS dl)
  set(CMAKE_SHARED_LINKER_FLAGS "${CMAKE_SHARED_LINKER_FLAGS} ${ODBC_LINK_FLAGS}")

ENDIF(WIN32)

if(NOT APPLE AND NOT WIN32)
  set(use_gtk true)
endif()


include_directories(${CMAKE_SOURCE_DIR})
include_directories(${CMAKE_SOURCE_DIR}/util)
include_directories(${CMAKE_SOURCE_DIR}/driver)

add_compile_options(${PLATFORM_CFLAGS})


# The main GUI dialog module
#
# Note: MODULE will not produce import library (.lib) on Windows

add_library(myodbc8S MODULE ${SETUP_SRCS})

target_link_libraries(myodbc8S ${ODBCLIB} ${ODBCINSTLIB})

# Note: When using GTK+, these libraries will be linked to -gtk modules.

if(NOT use_gtk)
  target_link_libraries(myodbc8S
    myodbc-util ${MYSQL_CLIENT_LIBS} ${PLATFORM_LIBS}
  )
endif()

if(MYSQL_CXX_LINKAGE)
  set_target_properties(myodbc8S PROPERTIES LINKER_LANGUAGE CXX)
endif()

if(WITH_NODEFAULTLIB)
  set_target_properties(myodbc8S PROPERTIES
    LINK_FLAGS_DEBUG "/NODEFAULTLIB:${WITH_NODEFAULTLIB}"
    LINK_FLAGS_RELWITHDEBINFO "/NODEFAULTLIB:${WITH_NODEFAULTLIB}"
    LINK_FLAGS_RELEASE "/NODEFAULTLIB:${WITH_NODEFAULTLIB}"
  )
endif()


# GTK GUI modules
# Note: These are dlopened by the main myodbc8S module after detecting
# GTK+ version in use (see gtk/setup_loader.cc)

if(use_gtk)

  set(GTK3_target myodbc8S-gtk3)
  set(GTK3_pkg_name gtk+-3.0)

  set(GTK2_target myodbc8S-gtk2)
  set(GTK2_pkg_name gtk+-2.0)

  include(FindPkgConfig)

  foreach(gtk GTK3 GTK2)

    pkg_check_modules(${gtk} ${${gtk}_pkg_name})

    if(NOT ${gtk}_INCLUDE_DIRS)
      continue()
    endif()

    # Extract only the top-level library, ignore its further dependencies

    set(${gtk}_LIBS)
    foreach(lib IN LISTS ${gtk}_LIBRARIES)
      if(lib MATCHES "^gtk-")
        list(APPEND ${gtk}_LIBS ${lib})
      endif()
    endforeach()

    # Sanity check

    try_compile(RESULT_VAR
      ${PROJECT_BINARY_DIR}
      SOURCES ${PROJECT_SOURCE_DIR}/setupgui/gtk/try.cc
      CMAKE_FLAGS -DLINK_DIRECTORIES=${${gtk}_LIBRARY_DIRS}
      LINK_LIBRARIES ${${gtk}_LIBS}
    )

    if(NOT RESULT_VAR)
      continue()
    endif()

    # Add GUI module

    message("-- Adding target: ${${gtk}_target}")
    add_library(${${gtk}_target} SHARED ${SETUP_SRCS_GTK})

    target_link_libraries(${${gtk}_target} PRIVATE
       ${${gtk}_LIBS} myodbc-util ${ODBCLIB} ${MYSQL_CLIENT_LIBS} ${PLATFORM_LIBS}
    )

    target_include_directories(${${gtk}_target} PRIVATE ${${gtk}_INCLUDE_DIRS})

    if(${gtk}_LIBRARY_DIRS)
      target_link_directories(${${gtk}_target} PRIVATE ${${gtk}_LIBRARY_DIRS})
    endif()

    #set_target_properties(${${gtk}_target} PROPERTIES LINKER_LANGUAGE CXX)

  endforeach(gtk)

  if(NOT TARGET ${GTK2_target} AND NOT TARGET ${GTK3_target})
    message(FATAL_ERROR
        "Could not find GTK+2 or GTK+3 required to build the configuration GUI,"
        " please install them on your system or disable building the GUI module"
        " (DISABLE_GUI=1)"
    )
  endif()

endif(use_gtk)


# Install specs
set_property(TARGET myodbc8S APPEND PROPERTY INSTALL_RPATH "$ORIGIN")

if(use_gtk)
  install(TARGETS myodbc8S DESTINATION ${LIB_SUBDIR})

  if(TARGET ${GTK3_target})
    install(TARGETS  ${GTK3_target} DESTINATION ${LIB_SUBDIR})
  endif()

  if(TARGET ${GTK2_target})
    install(TARGETS  ${GTK2_target} DESTINATION ${LIB_SUBDIR})
  endif()
else()
  install(TARGETS myodbc8S
    CONFIGURATIONS Release RelWithDebInfo
    DESTINATION ${LIB_SUBDIR})

  install(TARGETS myodbc8S
    CONFIGURATIONS Debug
    DESTINATION "${LIB_SUBDIR}/Debug")
endif()
