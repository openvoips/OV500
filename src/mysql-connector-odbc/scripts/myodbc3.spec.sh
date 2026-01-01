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


##############################################################################
#
# mysql-connector-odbc @CONNECTOR_BASE_VERSION@ RPM specification
#
##############################################################################

%global mysql_vendor	Oracle and/or its affiliates

%if 0%{?commercial}
%global license_type	Commercial
%global license_files	LICENSE.txt README.txt
%global product_suffix	-commercial
%else
%global license_type	GPLv2
%global license_files	LICENSE.txt README.txt
%endif

%global cmake3  cmake%{?el6:3}%{?el7:3}

# Use rpmbuild -ba --define 'shared_mysqlclient 1' ... to build shared
%{!?shared_mysqlclient: %global static_mysqlclient 1}

##############################################################################
#
#  Main information section
#
##############################################################################

Summary:	An ODBC @CONNECTOR_BASE_VERSION@ driver for MySQL - driver package
Name:		mysql-connector-odbc%{?product_suffix}
Version:	@CONNECTOR_NODASH_VERSION@
Release:	1%{?fedora:0}%{?commercial:.1}%{?dist}
License:	Copyright (c) 2000, @CURRENT_YEAR@, %{mysql_vendor}. All rights reserved.  Under %{license_type} license as shown in the Description field.
Source0:	http://cdn.mysql.com/Downloads/Connector-ODBC/@CONNECTOR_BASE_VERSION@/%{name}-@CONNECTOR_VERSION@-src.tar.gz
URL:		http://www.mysql.com/
Group:		Applications/Databases
Vendor:		%{mysql_vendor}
Packager:	%{mysql_vendor} Product Engineering Team <mysql-build@oss.oracle.com>
BuildRequires:	cmake
%{?shared_mysqlclient:BuildRequires: mysql-community-devel}
BuildRequires:	unixODBC-devel
BuildRequires:  gtk2-devel
%if 0%{?rhel} != 6 && 0%{?suse_version} != 1315
BuildRequires:  gtk3-devel
%endif

%if 0%{?rhel} != 6
Requires:       unixODBC >= 2.3
%else
# RHEL still has UnixODBC 2.2.14
Requires:       unixODBC >= 2.2.14
%endif

%if 0%{?odbc_gui}
%package setup
Summary:	An ODBC @CONNECTOR_BASE_VERSION@ driver for MySQL - setup library
Group:		Application/Databases
AutoReq:	no
Requires:	%{name} = %{version}-%{release}
%endif

%package test
Summary:	An ODBC @CONNECTOR_BASE_VERSION@ driver for MySQL - tests
Group:		Application/Databases
Requires:	%{name} = %{version}-%{release}

##############################################################################
#
#  Documentation
#
##############################################################################

%description
mysql-connector-odbc is an ODBC (3.51) driver for connecting an ODBC-aware
application to MySQL. mysql-connector-odbc works on Windows XP/Vista/7/8
Windows Server 2003/2008/2012, and most Unix platforms (incl. OSX and Linux).
MySQL is a trademark of %{mysql_vendor}

mysql-connector-odbc @CONNECTOR_BASE_VERSION@ is an enhanced version of mysql-connector-odbc @CONNECTOR_BASE_PREVIOUS@.
The driver comes in 2 flavours - ANSI and Unicode and commonly referred to as
'MySQL ODBC @CONNECTOR_BASE_VERSION@ ANSI Driver' or 'MySQL ODBC @CONNECTOR_BASE_VERSION@ Unicode Driver' respectively.

The MySQL software has Dual Licensing, which means you can use the
MySQL software free of charge under the GNU General Public License
(http://www.gnu.org/licenses/). You can also purchase commercial MySQL
licenses from %{mysql_vendor} if you do not wish to be bound by the
terms of the GPL. See the chapter "Licensing and Support" in the
manual for further info.

The MySQL web site (http://www.mysql.com/) provides the latest news
and information about the MySQL software. Also please see the
documentation and the manual for more information.

%if 0%{?odbc_gui}
%description setup
The setup library for the MySQL ODBC package, handles the optional GUI
dialog for configuring the driver.
%endif

%description test
The test suite for MySQL ODBC.

##############################################################################
#
#  Build
#
##############################################################################

%prep
%setup -q -n %{name}-@CONNECTOR_VERSION@-src

%build
mkdir release
pushd release
export CFLAGS="%{optflags}"
%{cmake3} -G "Unix Makefiles"   \
    -DRPM_BUILD=1           \
    -DCMAKE_INSTALL_PREFIX=%{_prefix} \
%if 0%{?odbc_gui}
%else
    -DDISABLE_GUI=1         \
%endif
%if 0%{?unixodbc}
    -DWITH_UNIXODBC=1       \
%endif
    %{?cmake_opt_extra}      \
    ..

# Note that the ".." needs to be last, in case some arguments expands to
# the empty string, and then "cmake" thinks is "current directory"

make  %{?_smp_mflags} VERBOSE=1
popd

##############################################################################
#
#  Cleanup
#
##############################################################################

%clean
rm -rf %{buildroot}

##############################################################################
#
#  Install and deinstall scripts
#
##############################################################################

# ----------------------------------------------------------------------
# Install, but remove the doc files.
# The way %doc <file-without-path> works, we can't
# have these files installed
# ----------------------------------------------------------------------

%install
pushd release
make DESTDIR=%{buildroot} install VERBOSE=1
rm -vf  %{buildroot}%{_prefix}/{ChangeLog,README*,LICENSE*.*,INFO_SRC,INFO_BIN}
mkdir -p %{buildroot}%{_libdir}/mysql-connector-odbc
mv %{buildroot}%{_prefix}/test %{buildroot}%{_libdir}/mysql-connector-odbc/
mv bin/dltest                  %{buildroot}%{_libdir}/mysql-connector-odbc/
popd

# ----------------------------------------------------------------------
# REGISTER DRIVER
# Note that "-e" is not working for drivers currently, so we have to
# deinstall before reinstall to change anything
# ----------------------------------------------------------------------

%post
if [ -x /usr/bin/myodbc-installer ]; then
    /usr/bin/myodbc-installer -a -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ Unicode Driver" -t "DRIVER=%{_libdir}/libmyodbc8w.so"
    /usr/bin/myodbc-installer -a -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ ANSI Driver"    -t "DRIVER=%{_libdir}/libmyodbc8a.so"
fi

%if 0%{?odbc_gui}
%post setup
/usr/bin/myodbc-installer -r -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ Unicode Driver"
/usr/bin/myodbc-installer -r -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ ANSI Driver"
/usr/bin/myodbc-installer -a -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ Unicode Driver" -t "DRIVER=%{_libdir}/libmyodbc8w.so;SETUP=%{_libdir}/libmyodbc8S.so"
/usr/bin/myodbc-installer -a -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ ANSI Driver"    -t "DRIVER=%{_libdir}/libmyodbc8a.so;SETUP=%{_libdir}/libmyodbc8S.so"
%endif

# ----------------------------------------------------------------------
# DEREGISTER DRIVER
# ----------------------------------------------------------------------

# Removing the driver package, we simply orphan any related DSNs
%preun
myodbc-installer -r -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ Unicode Driver"
myodbc-installer -r -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ ANSI Driver"

# Removing the setup RPM, downgrade the registration
%if 0%{?odbc_gui}
%preun setup
if [ "$1" = 0 ]; then
    if [ -x %{_bindir}/myodbc-installer ]; then
        %{_bindir}/myodbc-installer -r -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ Unicode Driver" > /dev/null 2>&1 || :
        %{_bindir}/myodbc-installer -r -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ ANSI Driver"    > /dev/null 2>&1 || :
        %{_bindir}/myodbc-installer -a -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ Unicode Driver" -t "DRIVER=%{_libdir}/libmyodbc8w.so" > /dev/null 2>&1 || :
        %{_bindir}/myodbc-installer -a -d -n "MySQL ODBC @CONNECTOR_BASE_VERSION@ ANSI Driver"    -t "DRIVER=%{_libdir}/libmyodbc8a.so" > /dev/null 2>&1 || :
    fi
fi
%endif

##############################################################################
#
#  Listing of files to be in the package
#
##############################################################################

%files
%defattr(-, root, root, -)
%{_bindir}/myodbc-installer
%{_libdir}/libmyodbc8w.so
%{_libdir}/libmyodbc8a.so
%doc %{license_files}
%doc ChangeLog INFO_SRC INFO_BIN

%if 0%{?odbc_gui}
%files setup
%defattr(-, root, root, -)
%doc %{license_files}
%doc INFO_SRC INFO_BIN
%{_libdir}/libmyodbc8S.so
%{_libdir}/libmyodbc8S-gtk*.so
%endif

%files test
%defattr(-, root, root, -)
%doc %{license_files}
%doc INFO_SRC INFO_BIN
%attr(-, root, root)   %{_libdir}/mysql-connector-odbc/test
%attr(755, root, root) %{_libdir}/mysql-connector-odbc/dltest

##############################################################################
