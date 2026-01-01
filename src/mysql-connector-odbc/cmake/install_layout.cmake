# Copyright (c) 2010, 2024, Oracle and/or its affiliates.
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

# Originally part of MySQL Server and Adapted for Connector/ODBC

# The purpose of this file is to set the default installation layout.
#
# The current choices of installation layout are:
#
#  STANDALONE
#    Build with prefix=/usr/local/mysql, create tarball with install prefix="."
#    and relative links.  Windows zip uses the same tarball layout but without
#    the build prefix.
#
#  RPM, SLES
#    Build as per default RPM layout, with prefix=/usr
#    Note: The layout for ULN RPMs differs, see the "RPM" section.
#
#  DEB
#    Build as per default Debian layout, with prefix=/usr
#    Note: previous layout is now named DEBSRV4
#
#  DEBSRV4
#    Build as per STANDALONE, prefix=/opt/mysql/mysql-router-$major.$minor
#
#  SVR4
#    Solaris package layout suitable for pkg* tools, prefix=/opt/mysql/mysql-router
#
#  FREEBSD, GLIBC, OSX, TARGZ
#    Build with prefix=/usr/local/mysql, create tarball with install prefix="."
#    and relative links.
#
#  WIN
#     Windows zip : same as tarball layout but without the build prefix
#
# To force a directory layout, use -DINSTALL_LAYOUT=<layout>.
#
# The default is STANDALONE.
#
# Note : At present, RPM and SLES layouts are similar. This is also true
#        for layouts like FREEBSD, GLIBC, OSX, TARGZ. However, they provide
#        opportunity to fine-tune deployment for each platform without
#        affecting all other types of deployment.
#
# There is the possibility to further fine-tune installation directories.
# Several variables can be overwritten:
#
# - INSTALL_BINDIR          (directory with myodbc-install)
# - INSTALL_LIBDIR          (directory with ODBC drivers)
# - INSTALL_DOCREADMEDIR    (readme and similar)
# - INSTALL_TESTDIR         (test modules)
#
# When changing this page,  _please_ do not forget to update public Wiki
# http://forge.mysql.com/wiki/CMake#Fine-tuning_installation_paths

IF(NOT INSTALL_LAYOUT)
  SET(DEFAULT_INSTALL_LAYOUT "DEFAULT")
ENDIF()

if(NOT CMAKE_INSTALL_PREFIX_INITIALIZED_TO_DEFAULT)
  SET(DEFAULT_INSTALL_LAYOUT "STANDALONE")
endif()

SET(INSTALL_LAYOUT "${DEFAULT_INSTALL_LAYOUT}"
CACHE STRING "Installation directory layout. Options are: TARGZ (as in tar.gz installer), WIN (as in zip installer), STANDALONE, RPM, DEB, DEBSRV4, SVR4, FREEBSD, GLIBC, OSX, SLES")

message(STATUS "Installation layout set to ${DEFAULT_INSTALL_LAYOUT}")

IF(UNIX)
  IF(INSTALL_LAYOUT STREQUAL "RPM" OR
     INSTALL_LAYOUT STREQUAL "SLES" OR
     INSTALL_LAYOUT STREQUAL "DEB")
    SET(default_prefix "/usr")
  ELSE()
    SET(default_prefix "/usr/local")
  ENDIF()
  IF(CMAKE_INSTALL_PREFIX_INITIALIZED_TO_DEFAULT)
    SET(CMAKE_INSTALL_PREFIX ${default_prefix}
      CACHE PATH "install prefix" FORCE)
  ENDIF()
  SET(VALID_INSTALL_LAYOUTS
      "RPM" "DEB" "DEBSVR4" "SVR4" "FREEBSD" "GLIBC" "OSX" "TARGZ" "SLES" "STANDALONE" "DEFAULT")
  LIST(FIND VALID_INSTALL_LAYOUTS "${INSTALL_LAYOUT}" ind)
  IF(ind EQUAL -1)
    MESSAGE(FATAL_ERROR "Invalid INSTALL_LAYOUT parameter:${INSTALL_LAYOUT}."
    " Choose between ${VALID_INSTALL_LAYOUTS}" )
  ENDIF()
ENDIF()

IF(WIN32)
  SET(VALID_INSTALL_LAYOUTS "TARGZ" "STANDALONE" "WIN")
  LIST(FIND VALID_INSTALL_LAYOUTS "${INSTALL_LAYOUT}" ind)
  IF(ind EQUAL -1)
    MESSAGE(FATAL_ERROR "Invalid INSTALL_LAYOUT parameter:${INSTALL_LAYOUT}."
    " Choose between ${VALID_INSTALL_LAYOUTS}" )
  ENDIF()
ENDIF()

#
# STANDALONE layout
#
SET(INSTALL_BINDIR_STANDALONE           "bin")
SET(INSTALL_LIBDIR_STANDALONE           "lib")
SET(INSTALL_DOCREADMEDIR_STANDALONE     ".")
SET(INSTALL_TESTDIR_STANDALONE          "share/myodbc/test")

#
# DEFAULT layout
#
SET(INSTALL_BINDIR_DEFAULT           "bin")
SET(INSTALL_LIBDIR_DEFAULT           "lib")
SET(INSTALL_DOCREADMEDIR_DEFAULT     ".")
SET(INSTALL_TESTDIR_DEFAULT          "share/myodbc/test")

#
# WIN layout
#
SET(INSTALL_BINDIR_WIN           "bin")
SET(INSTALL_LIBDIR_WIN           "lib")
SET(INSTALL_DOCREADMEDIR_WIN     ".")
SET(INSTALL_TESTDIR_WIN          "test")

#
# FREEBSD layout
#
SET(INSTALL_BINDIR_FREEBSD           "bin")
SET(INSTALL_LIBDIR_FREEBSD           "lib")
SET(INSTALL_DOCREADMEDIR_FREEBSD     ".")
SET(INSTALL_TESTDIR_FREEBSD          "test")

#
# GLIBC layout
#
SET(INSTALL_BINDIR_GLIBC           "bin")
SET(INSTALL_LIBDIR_GLIBC           "lib")
SET(INSTALL_DOCREADMEDIR_GLIBC     ".")
SET(INSTALL_TESTDIR_GLIBC          "test")

#
# OSX layout
#
SET(INSTALL_BINDIR_OSX           "bin")
SET(INSTALL_LIBDIR_OSX           "lib")
SET(INSTALL_DOCREADMEDIR_OSX     ".")
SET(INSTALL_TESTDIR_OSX          "test")

#
# TARGZ layout
#
SET(INSTALL_BINDIR_TARGZ           "bin")
SET(INSTALL_LIBDIR_TARGZ           "lib")
SET(INSTALL_DOCREADMEDIR_TARGZ     ".")
SET(INSTALL_TESTDIR_TARGZ          "test")

#
# RPM layout
#
# See "packaging/rpm-uln/mysql-5.5-libdir.patch" for the differences
# which apply to RPMs in ULN (Oracle Linux), that patch file will
# be applied at build time via "rpmbuild".
#
SET(INSTALL_BINDIR_RPM                  "bin")
IF(ARCH_64BIT)
  SET(INSTALL_LIBDIR_RPM                "lib64")
ELSE()
  SET(INSTALL_LIBDIR_RPM                "lib")
ENDIF()
#
#SET(INSTALL_DOCREADMEDIR_RPM           unset - installed directly by RPM)
SET(INSTALL_TESTDIR_RPM                 "share/myodbc/test")

#
# SLES layout
#
SET(INSTALL_BINDIR_SLES                  "bin")
IF(ARCH_64BIT)
  SET(INSTALL_LIBDIR_SLES                "lib64")
ELSE()
  SET(INSTALL_LIBDIR_SLES                "lib")
ENDIF()
#SET(INSTALL_DOCREADMEDIR_SLES           unset - installed directly by SLES)
SET(INSTALL_TESTDIR_SLES                 "share/myodbc/test")

#
# DEB layout
#
SET(INSTALL_BINDIR_DEB                  "bin")
IF(ARCH_64BIT)
  SET(INSTALL_LIBDIR_DEB                "lib/x86_64-linux-gnu")
ELSE()
  SET(INSTALL_LIBDIR_DEB                "lib/i386-linux-gnu")
ENDIF()
SET(INSTALL_DOCREADMEDIR_DEB            "share/myodbc/docs")
SET(INSTALL_TESTDIR_DEB                 "share/myodbc/test")

#
# DEBSVR4 layout
#
SET(INSTALL_BINDIR_DEBSVR4              "bin")
SET(INSTALL_LIBDIR_DEBSVR4              "lib")
SET(INSTALL_DOCREADMEDIR_DEBSVR4        ".")
SET(INSTALL_TESTDIR_DEBSVR4             "test")

#
# SVR4 layout
#
SET(INSTALL_BINDIR_SVR4                 "bin")
SET(INSTALL_LIBDIR_SVR4                 "lib")
SET(INSTALL_DOCREADMEDIR_SVR4           ".")
SET(INSTALL_TESTDIR_SVR4                "test")

# Clear cached variables if install layout was changed
IF(OLD_INSTALL_LAYOUT)
  IF(NOT OLD_INSTALL_LAYOUT STREQUAL INSTALL_LAYOUT)
    SET(FORCE FORCE)
  ENDIF()
ENDIF()
SET(OLD_INSTALL_LAYOUT ${INSTALL_LAYOUT} CACHE INTERNAL "")

# Set INSTALL_FOODIR variables for chosen layout (for example, INSTALL_BINDIR
# will be defined  as ${INSTALL_BINDIR_STANDALONE} by default if STANDALONE
# layout is chosen)
FOREACH(var BIN LIB DOCREADME TEST)
  SET(INSTALL_${var}DIR  ${INSTALL_${var}DIR_${INSTALL_LAYOUT}}
  CACHE STRING "${var} installation directory" ${FORCE})
  MARK_AS_ADVANCED(INSTALL_${var}DIR)
ENDFOREACH()

