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


MACRO(_ENV_OR_DEFAULT VAR _default)

  SET(${VAR} $ENV{${VAR}})

  IF(NOT ${VAR})
    SET(${VAR} "${_default}")
  ENDIF(NOT ${VAR})

ENDMACRO(_ENV_OR_DEFAULT VAR _key _default)

MESSAGE(STATUS "Configuring ini files for tests (${BINARY_DIR})")

SET(TEST_DRIVER1 "${DRIVER_LOCATION1}")
SET(TEST_DRIVER2 "${DRIVER_LOCATION2}")
_ENV_OR_DEFAULT(TEST_DATABASE "test")
_ENV_OR_DEFAULT(TEST_SERVER   "localhost")
_ENV_OR_DEFAULT(TEST_UID      "root")
_ENV_OR_DEFAULT(TEST_PASSWORD "")
_ENV_OR_DEFAULT(TEST_SOCKET   "")

IF(WIN32 AND TEST_SOCKET)
  SET(TEST_OPTIONS "NAMED_PIPE=1")
ENDIF(WIN32 AND TEST_SOCKET)

IF(${DRIVERS_COUNT} LESS 2)
  # If configured to build 1 driver only we make 2nd driver/dsn to work with the same driver
  SET(TEST_DRIVER2 "${DRIVER_LOCATION1}")
  SET(CONNECTOR_DRIVER_TYPE2 "${CONNECTOR_DRIVER_TYPE1}")
  SET(CONNECTOR_DRIVER_TYPE_SHORT2 "")
ENDIF(${DRIVERS_COUNT} LESS 2)

SET(DESCRIPTION1 "MySQL ODBC ${CONNECTOR_MAJOR}.${CONNECTOR_MINOR} ${CONNECTOR_DRIVER_TYPE1} Driver test" )
SET(DESCRIPTION2 "MySQL ODBC ${CONNECTOR_MAJOR}.${CONNECTOR_MINOR} ${CONNECTOR_DRIVER_TYPE2} Driver test" )

# Note: protection against parallel runs of this script during parallel
# builds.

if(EXISTS ${BINARY_DIR}/odbc.ini AND EXISTS ${BINARY_DIR}/odbcinst.ini)
  return()
endif()

CONFIGURE_FILE(odbcinst.ini.in ${BINARY_DIR}/odbcinst.ini @ONLY)
CONFIGURE_FILE(odbc.ini.in     ${BINARY_DIR}/odbc.ini @ONLY)

