// Copyright (c) 2007, 2024, Oracle and/or its affiliates.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License, version 2.0, as
// published by the Free Software Foundation.
//
// This program is designed to work with certain software (including
// but not limited to OpenSSL) that is licensed under separate terms, as
// designated in a particular file or component or in included license
// documentation. The authors of MySQL hereby grant you an additional
// permission to link the program and your derivative works with the
// separately licensed software that they have either included with
// the program or referenced in the documentation.
//
// Without limiting anything contained in the foregoing, this file,
// which is part of Connector/ODBC, is also subject to the
// Universal FOSS Exception, version 1.0, a copy of which can be found at
// https://oss.oracle.com/licenses/universal-foss-exception.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU General Public License, version 2.0, for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software Foundation, Inc.,
// 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

#include <stdio.h>
#include "..\..\VersionInfo.h"

int main(int argc, char *argv[])
{
	FILE *fp;
	int v1, v2, v3;

	if (argc ==1)
		exit (1);

	if (!(fp = fopen("myodbc_version.wxs",  "w")))
		exit (2);

	fprintf(fp, "<Include xmlns=\"http://wixtoolset.org/schemas/v4/wxs\">\n");

	sscanf(SETUP_VERSION, "%d.%d.%d", &v1, &v2, &v3);

	fprintf(fp, "<?define odbc_ver_short=\"%d.%d\" ?>\n", v1, v2);
	fprintf(fp, "<?define odbc_ver_long=\"%d.%d.%d\" ?>\n", v1, v2, v3);
        if (v3 == 0) {
          if (v2 == 0)
            fprintf(fp, "<?define odbc_ver_prev=\"%d.%d.%d\" ?>\n", v1-1, 999, 999);
          else
            fprintf(fp, "<?define odbc_ver_prev=\"%d.%d.%d\" ?>\n", v1, v2-1, 999);
        } else {
          fprintf(fp, "<?define odbc_ver_prev=\"%d.%d.%d\" ?>\n", v1, v2, v3-1);
        }
	fprintf(fp, "<?define odbc_ver_next=\"%d.%d.%d\" ?>\n", v1, v2, v3+1);
	fprintf(fp, "<?define odbc_driver_type=\""MYODBC_STRDRIVERTYPE"\" ?>\n");
	fprintf(fp, "<?define odbc_driver_type_suffix=\""MYODBC_STRTYPE_SUFFIX"\" ?>\n");
	fprintf(fp, "<?define odbc_driver_series=\""MYODBC_STRDRIVERID"\" ?>\n");
	fprintf(fp, "<?define odbc_ver_num=\"%d%02d%02d\" ?>\n", v1, v2, v3);

	fclose(fp);

	
	if (!(fp = fopen("myodbc_version.cmake",  "w")))
		exit (2);

	fprintf(fp, "SET(ODBC_VERSION \"%d.%d.%d\")\n", v1,v2,v3);
	fprintf(fp, "SET(ODBC_VERSION_SUFFIX \"%s\")", MYODBC_STRTYPE_SUFFIX);

	fclose(fp);
}
