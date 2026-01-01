// Copyright (c) 2012, 2024, Oracle and/or its affiliates.
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

static char *ui_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" \
"<!-- Generated with glade 3.22.2 -->\n" \
"<interface>\n" \
  "<requires lib=\"gtk+\" version=\"2.24\"/>\n" \
  "<object class=\"GtkAdjustment\" id=\"adjustment1\">\n" \
    "<property name=\"upper\">65535</property>\n" \
    "<property name=\"value\">3306</property>\n" \
    "<property name=\"step_increment\">1</property>\n" \
    "<property name=\"page_increment\">10</property>\n" \
  "</object>\n" \
  "<object class=\"GtkAdjustment\" id=\"adjustment2\">\n" \
    "<property name=\"upper\">1000000</property>\n" \
    "<property name=\"value\">100</property>\n" \
    "<property name=\"step_increment\">1</property>\n" \
    "<property name=\"page_increment\">10</property>\n" \
  "</object>\n" \
  "<object class=\"GtkWindow\" id=\"odbcdialog\">\n" \
    "<property name=\"visible\">True</property>\n" \
    "<property name=\"can_focus\">False</property>\n" \
    "<property name=\"title\">MySQL Connector/ODBC Data Source</property>\n" \
    "<property name=\"resizable\">False</property>\n" \
    "<property name=\"modal\">True</property>\n" \
    "<property name=\"window_position\">center-on-parent</property>\n" \
    "<property name=\"destroy_with_parent\">True</property>\n" \
    "<child>\n" \
      "<object class=\"GtkVBox\" id=\"vbox1\">\n" \
        "<property name=\"visible\">True</property>\n" \
        "<property name=\"can_focus\">False</property>\n" \
        "<child>\n" \
          "<object class=\"GtkImage\" id=\"header\">\n" \
            "<property name=\"width_request\">568</property>\n" \
            "<property name=\"height_request\">63</property>\n" \
            "<property name=\"visible\">True</property>\n" \
            "<property name=\"can_focus\">False</property>\n" \
          "</object>\n" \
          "<packing>\n" \
            "<property name=\"expand\">False</property>\n" \
            "<property name=\"fill\">True</property>\n" \
            "<property name=\"position\">0</property>\n" \
          "</packing>\n" \
        "</child>\n" \
        "<child>\n" \
          "<object class=\"GtkVBox\" id=\"vbox2\">\n" \
            "<property name=\"visible\">True</property>\n" \
            "<property name=\"can_focus\">False</property>\n" \
            "<property name=\"border_width\">12</property>\n" \
            "<property name=\"spacing\">8</property>\n" \
            "<child>\n" \
              "<object class=\"GtkFrame\" id=\"frame1\">\n" \
                "<property name=\"visible\">True</property>\n" \
                "<property name=\"can_focus\">False</property>\n" \
                "<property name=\"label_xalign\">0</property>\n" \
                "<child>\n" \
                  "<object class=\"GtkTable\" id=\"table1\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"border_width\">8</property>\n" \
                    "<property name=\"n_rows\">9</property>\n" \
                    "<property name=\"n_columns\">4</property>\n" \
                    "<property name=\"column_spacing\">8</property>\n" \
                    "<property name=\"row_spacing\">8</property>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label2\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">Data Source Name:</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label3\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">Description:</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">1</property>\n" \
                        "<property name=\"bottom_attach\">2</property>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label5\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">Username:</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">5</property>\n" \
                        "<property name=\"bottom_attach\">6</property>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label6\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">Password:</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">6</property>\n" \
                        "<property name=\"bottom_attach\">7</property>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SERVER\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"invisible_char\">●</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">3</property>\n" \
                        "<property name=\"bottom_attach\">4</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"UID\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"invisible_char\">●</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">5</property>\n" \
                        "<property name=\"bottom_attach\">6</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"PWD\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"visibility\">False</property>\n" \
                        "<property name=\"invisible_char\">●</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">6</property>\n" \
                        "<property name=\"bottom_attach\">7</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label7\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">Database:</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">7</property>\n" \
                        "<property name=\"bottom_attach\">8</property>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label8\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">Port:</property>\n" \
                        "<property name=\"xalign\">0</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"top_attach\">3</property>\n" \
                        "<property name=\"bottom_attach\">4</property>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkSpinButton\" id=\"PORT\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                        "<property name=\"adjustment\">adjustment1</property>\n" \
                        "<property name=\"climb_rate\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">3</property>\n" \
                        "<property name=\"right_attach\">4</property>\n" \
                        "<property name=\"top_attach\">3</property>\n" \
                        "<property name=\"bottom_attach\">4</property>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"DSN\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">Sets the name or location of a specific socket or Windows pipe to use when communicating with MySQL.</property>\n" \
                        "<property name=\"invisible_char\">●</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">4</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"DESCRIPTION\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">Enter some text to help identify the connection.</property>\n" \
                        "<property name=\"invisible_char\">●</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">4</property>\n" \
                        "<property name=\"top_attach\">1</property>\n" \
                        "<property name=\"bottom_attach\">2</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkHSeparator\" id=\"hseparator1\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"right_attach\">4</property>\n" \
                        "<property name=\"top_attach\">2</property>\n" \
                        "<property name=\"bottom_attach\">3</property>\n" \
                        "<property name=\"x_options\">GTK_FILL</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"socket\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"has_tooltip\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">Sets the path of a specific local socket file to use when communicating with MySQL</property>\n" \
                        "<property name=\"invisible_char\">●</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">4</property>\n" \
                        "<property name=\"bottom_attach\">5</property>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"test\">\n" \
                        "<property name=\"label\" translatable=\"yes\">_Test</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                        "<property name=\"use_underline\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">3</property>\n" \
                        "<property name=\"right_attach\">4</property>\n" \
                        "<property name=\"top_attach\">7</property>\n" \
                        "<property name=\"bottom_attach\">8</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkRadioButton\" id=\"use_tcp_ip_server\">\n" \
                        "<property name=\"label\" translatable=\"yes\">TCP/IP Server:</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                        "<property name=\"active\">True</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">3</property>\n" \
                        "<property name=\"bottom_attach\">4</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkRadioButton\" id=\"use_socket_file\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Socket:</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                        "<property name=\"active\">True</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                        "<property name=\"group\">use_tcp_ip_server</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">4</property>\n" \
                        "<property name=\"bottom_attach\">5</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkComboBox\" id=\"DATABASE\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"events\">GDK_BUTTON_MOTION_MASK | GDK_BUTTON_PRESS_MASK | GDK_STRUCTURE_MASK | GDK_FOCUS_CHANGE_MASK</property>\n" \
                        "<property name=\"button_sensitivity\">on</property>\n" \
                        "<property name=\"has_entry\">True</property>\n" \
                        "<property name=\"entry_text_column\">0</property>\n" \
                        "<child internal-child=\"entry\">\n" \
                          "<object class=\"GtkEntry\" id=\"DATABASE_entry\">\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"editable\">True</property>\n" \
                          "</object>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">7</property>\n" \
                        "<property name=\"bottom_attach\">8</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                "</child>\n" \
                "<child type=\"label\">\n" \
                  "<object class=\"GtkLabel\" id=\"label1\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">ODBC Connection Parameters</property>\n" \
                  "</object>\n" \
                "</child>\n" \
              "</object>\n" \
              "<packing>\n" \
                "<property name=\"expand\">False</property>\n" \
                "<property name=\"fill\">True</property>\n" \
                "<property name=\"position\">0</property>\n" \
              "</packing>\n" \
            "</child>\n" \
            "<child>\n" \
              "<object class=\"GtkNotebook\" id=\"details_note\">\n" \
                "<property name=\"can_focus\">True</property>\n" \
                "<property name=\"no_show_all\">True</property>\n" \
                "<child>\n" \
                  "<object class=\"GtkVBox\" id=\"vboxConnection\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"border_width\">8</property>\n" \
                    "<child>\n" \
                      "<object class=\"GtkTable\" id=\"table4\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"n_rows\">6</property>\n" \
                        "<property name=\"n_columns\">2</property>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"BIG_PACKETS\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Allow Big Results</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">5</property>\n" \
                            "<property name=\"bottom_attach\">6</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"COMPRESSED_PROTO\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Use Compression</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">4</property>\n" \
                            "<property name=\"bottom_attach\">5</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"AUTO_RECONNECT\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Enable Automatic Reconnect</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">3</property>\n" \
                            "<property name=\"bottom_attach\">4</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"NO_PROMPT\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Don't Prompt Upon Connect</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">2</property>\n" \
                            "<property name=\"bottom_attach\">3</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"MULTI_STATEMENTS\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Allow Multiple Statements</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"CLIENT_INTERACTIVE\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Interactive Client</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"CAN_HANDLE_EXP_PWD\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Can Handle Expired Password</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"MULTI_HOST\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Multi Host</property>\n" \
                            "<property name=\"use_action_appearance\">True</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"ENABLE_DNS_SRV\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Use DNS SRV records</property>\n" \
                            "<property name=\"use_action_appearance\">True</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"top_attach\">2</property>\n" \
                            "<property name=\"bottom_attach\">3</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"GET_SERVER_PUBLIC_KEY\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Get Server Public Key</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"top_attach\">3</property>\n" \
                            "<property name=\"bottom_attach\">4</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">0</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkTable\" id=\"connection_input_table\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"border_width\">8</property>\n" \
                        "<property name=\"n_rows\">5</property>\n" \
                        "<property name=\"n_columns\">2</property>\n" \
                        "<property name=\"column_spacing\">8</property>\n" \
                        "<property name=\"row_spacing\">5</property>\n" \
                        "<child>\n" \
                          "<object class=\"GtkLabel\" id=\"label12\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"label\" translatable=\"yes\">Character Set:</property>\n" \
                            "<property name=\"xalign\">1</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"x_options\">GTK_EXPAND | GTK_SHRINK | GTK_FILL</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkLabel\" id=\"charsetlbl\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"label\" translatable=\"yes\">Initial Statement:</property>\n" \
                            "<property name=\"xalign\">1</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                            "<property name=\"x_options\">GTK_FILL</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkEntry\" id=\"INITSTMT\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"invisible_char\">●</property>\n" \
                            "<property name=\"primary_icon_activatable\">False</property>\n" \
                            "<property name=\"secondary_icon_activatable\">False</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkLabel\" id=\"labelPluginDir\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"label\" translatable=\"yes\">Plugin directory:</property>\n" \
                            "<property name=\"justify\">right</property>\n" \
                            "<property name=\"width_chars\">10</property>\n" \
                            "<property name=\"max_width_chars\">10</property>\n" \
                            "<property name=\"xalign\">1</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">2</property>\n" \
                            "<property name=\"bottom_attach\">3</property>\n" \
                            "<property name=\"x_options\">GTK_EXPAND | GTK_SHRINK | GTK_FILL</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkHBox\" id=\"hbox_plugin\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<child>\n" \
                              "<object class=\"GtkEntry\" id=\"PLUGIN_DIR\">\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">True</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">0</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                            "<child>\n" \
                              "<object class=\"GtkButton\" id=\"plugindir_button\">\n" \
                                "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                                "<property name=\"receives_default\">True</property>\n" \
                                "<property name=\"relief\">none</property>\n" \
                                "<property name=\"xalign\">0.46000000834465027</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">False</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">1</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"top_attach\">2</property>\n" \
                            "<property name=\"bottom_attach\">3</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkComboBox\" id=\"CHARSET\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"events\">GDK_BUTTON_MOTION_MASK | GDK_BUTTON_PRESS_MASK | GDK_STRUCTURE_MASK | GDK_FOCUS_CHANGE_MASK</property>\n" \
                            "<property name=\"button_sensitivity\">on</property>\n" \
                            "<property name=\"has_entry\">True</property>\n" \
                            "<property name=\"entry_text_column\">0</property>\n" \
                            "<child internal-child=\"entry\">\n" \
                              "<object class=\"GtkEntry\" id=\"CHARSET_entry\">\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                                "<property name=\"editable\">True</property>\n" \
                              "</object>\n" \
                            "</child>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">False</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">1</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                "</child>\n" \
                "<child type=\"tab\">\n" \
                  "<object class=\"GtkLabel\" id=\"lTabConnection\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"xpad\">5</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">Connection</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"tab_fill\">False</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkVBox\" id=\"vboxAuthentication\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"border_width\">8</property>\n" \
                    "<child>\n" \
                      "<object class=\"GtkTable\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"n_rows\">3</property>\n" \
                        "<property name=\"n_columns\">2</property>\n" \
                        "<property name=\"column_spacing\">8</property>\n" \
                        "<property name=\"row_spacing\">5</property>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"ENABLE_CLEARTEXT_PLUGIN\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Enable Cleartext Authentication</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkLabel\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"label\" translatable=\"yes\">Authentication Library:</property>\n" \
                            "<property name=\"xalign\">1</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkEntry\" id=\"DEFAULT_AUTH\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkHBox\" id=\"hbox_oci_config\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<child>\n" \
                              "<object class=\"GtkEntry\" id=\"OCI_CONFIG_FILE\">\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">True</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">0</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                            "<child>\n" \
                              "<object class=\"GtkButton\" id=\"OCI_CONFIG_FILE_button\">\n" \
                                "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                                "<property name=\"receives_default\">True</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">False</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">1</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"top_attach\">2</property>\n" \
                            "<property name=\"bottom_attach\">3</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkLabel\" id=\"labelOCIConfigFile\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"label\" translatable=\"yes\">OCI Config File:</property>\n" \
                            "<property name=\"justify\">right</property>\n" \
                            "<property name=\"width_chars\">10</property>\n" \
                            "<property name=\"max_width_chars\">10</property>\n" \
                            "<property name=\"xalign\">1</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">2</property>\n" \
                            "<property name=\"bottom_attach\">3</property>\n" \
                            "<property name=\"x_options\">GTK_EXPAND | GTK_SHRINK | GTK_FILL</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkLabel\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"label\" translatable=\"yes\">OCI Config Profile:</property>\n" \
                            "<property name=\"xalign\">1</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">3</property>\n" \
                            "<property name=\"bottom_attach\">4</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkEntry\" id=\"OCI_CONFIG_PROFILE\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                            "<property name=\"top_attach\">3</property>\n" \
                            "<property name=\"bottom_attach\">4</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">False</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">0</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">1</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child type=\"tab\">\n" \
                  "<object class=\"GtkLabel\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">Authentication</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">1</property>\n" \
                    "<property name=\"tab_fill\">False</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkVBox\" id=\"vboxMetadata\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"border_width\">8</property>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"NO_BIGINT\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Treat BIGINT Columns as INT columns</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"has_tooltip\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">Change BIGINT columns to INT  columns (some applications can't handle BIGINT).</property>\n" \
                        "<property name=\"use_underline\">True</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">0</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"NO_BINARY_RESULT\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Always handle Binary Function Results as Character Data</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">1</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"FULL_COLUMN_NAMES\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Return Table Names for SQLDescribeCol</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"has_tooltip\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">SQLDescribeCol() returns fully qualified column names.</property>\n" \
                        "<property name=\"use_underline\">True</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">3</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"NO_CATALOG\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Disable Catalog Support</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"has_tooltip\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">Forces results from the catalog functions, such as SQLTables, to always return NULL and the driver to report that catalogs are not supported.</property>\n" \
                        "<property name=\"use_underline\">True</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">4</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"NO_SCHEMA\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Disable Schema Support</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"has_tooltip\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">Forces results from the catalog functions, such as SQLTables, to always return NULL and the driver to report that schemas are not supported.</property>\n" \
                        "<property name=\"use_underline\">True</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">5</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"COLUMN_SIZE_S32\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Limit Column Size to Signed 32-bit Range</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">6</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">2</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child type=\"tab\">\n" \
                  "<object class=\"GtkLabel\" id=\"lTabMetadata\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"xpad\">5</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">Metadata</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">2</property>\n" \
                    "<property name=\"tab_fill\">False</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkVBox\" id=\"vboxCursorsResults\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"spacing\">2</property>\n" \
                    "<child>\n" \
                      "<object class=\"GtkVBox\" id=\"vbox_cursors_results\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"border_width\">8</property>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"DYNAMIC_CURSOR\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Enable dynamic cursors</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Enable or disable the dynamic cursor support. (Not allowed in Connector/ODBC 2.50.)</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">0</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"NO_DEFAULT_CURSOR\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Disable driver-provided cursor support</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Force use of ODBC manager cursors (experimental).</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">1</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"NO_CACHE\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Don't cache results of forward-only cursors</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Do not cache the results locally in the driver, instead read from server (mysql_use_result()). This works only for forward-only cursors. This option is very important in dealing with large tables when you don't want the driver to cache the entire result set.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"FORWARD_CURSOR\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Force use of forward-only cursors</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Force the use of Forward-only cursor type. In case of applications setting the default static/dynamic cursor type, and one wants the driver to use non-cache result sets, then this option ensures the forward-only cursor behavior.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">3</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkHBox\" id=\"hbox2\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<child>\n" \
                              "<object class=\"GtkCheckButton\" id=\"cursor_prefetch_active\">\n" \
                                "<property name=\"label\" translatable=\"yes\">Prefetch from server by</property>\n" \
                                "<property name=\"use_action_appearance\">False</property>\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                                "<property name=\"receives_default\">False</property>\n" \
                                "<property name=\"xalign\">0.5</property>\n" \
                                "<property name=\"draw_indicator\">True</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">True</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">0</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                            "<child>\n" \
                              "<object class=\"GtkSpinButton\" id=\"PREFETCH\">\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"sensitive\">False</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                                "<property name=\"invisible_char\">•</property>\n" \
                                "<property name=\"truncate_multiline\">True</property>\n" \
                                "<property name=\"primary_icon_activatable\">False</property>\n" \
                                "<property name=\"secondary_icon_activatable\">False</property>\n" \
                                "<property name=\"adjustment\">adjustment2</property>\n" \
                                "<property name=\"numeric\">True</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">True</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">1</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                            "<child>\n" \
                              "<object class=\"GtkLabel\" id=\"label9\">\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">False</property>\n" \
                                "<property name=\"label\" translatable=\"yes\">rows at a time</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">True</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">2</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">4</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"FOUND_ROWS\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Return matching rows instead of affected rows</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">The client can't handle that MySQL returns the true value of affected rows. If this flag is set, MySQL returns “found rows” instead. You must have MySQL 3.21.14 or newer to get this to work.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">5</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"AUTO_IS_NULL\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Enable SQL__AUTO__IS__NULL</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">When set, this option causes the connection to set the SQL_AUTO_IS_NULL option to 1. This disables the standard behavior, but may enable older applications to correctly identify AUTO_INCREMENT values.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">6</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"PAD_SPACE\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Pad CHAR to full length with space</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Pad CHAR columns to full column length.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">7</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"ZERO_DATE_TO_MIN\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Return SQL__NULL__DATA for zero date</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Translates zero dates (XXXX-00-00) into the minimum date values supported by ODBC, XXXX-01-01. This resolves an issue where some statement swill not work because the date returned and the minimumd ODBC date value are incompatible.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"yalign\">0.56000000238418579</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"expand\">True</property>\n" \
                            "<property name=\"fill\">True</property>\n" \
                            "<property name=\"position\">8</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">0</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">3</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child type=\"tab\">\n" \
                  "<object class=\"GtkLabel\" id=\"lTabCursorsResults\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"xpad\">5</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">Cursors/Results</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">3</property>\n" \
                    "<property name=\"tab_fill\">False</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkVBox\" id=\"vboxDebug\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"border_width\">8</property>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"LOG_QUERY\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Log queries to /tmp/myodbc.sql</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"has_tooltip\">True</property>\n" \
                        "<property name=\"tooltip_text\" translatable=\"yes\">Make a debug log in /tmp/myodbc.log.</property>\n" \
                        "<property name=\"use_underline\">True</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">False</property>\n" \
                        "<property name=\"fill\">False</property>\n" \
                        "<property name=\"position\">0</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">4</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child type=\"tab\">\n" \
                  "<object class=\"GtkLabel\" id=\"label14\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"xpad\">5</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">Debug</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"menu_label\">lTabDebug</property>\n" \
                    "<property name=\"position\">4</property>\n" \
                    "<property name=\"tab_fill\">False</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkTable\" id=\"table3\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"border_width\">8</property>\n" \
                    "<property name=\"n_rows\">10</property>\n" \
                    "<property name=\"n_columns\">3</property>\n" \
                    "<property name=\"column_spacing\">8</property>\n" \
                    "<property name=\"row_spacing\">8</property>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SSL_KEY\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"invisible_char\">•</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SSL_CERT\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"invisible_char\">•</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">1</property>\n" \
                        "<property name=\"bottom_attach\">2</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SSL_CA\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"invisible_char\">•</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">2</property>\n" \
                        "<property name=\"bottom_attach\">3</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"SSL_KEY_button\">\n" \
                        "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"x_options\"/>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"SSL_CERT_button\">\n" \
                        "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"top_attach\">1</property>\n" \
                        "<property name=\"bottom_attach\">2</property>\n" \
                        "<property name=\"x_options\"/>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"SSL_CA_button\">\n" \
                        "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"top_attach\">2</property>\n" \
                        "<property name=\"bottom_attach\">3</property>\n" \
                        "<property name=\"x_options\"/>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"SSL_CAPATH_button\">\n" \
                        "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"top_attach\">3</property>\n" \
                        "<property name=\"bottom_attach\">4</property>\n" \
                        "<property name=\"x_options\"/>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SSL_CIPHER\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"invisible_char\">•</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">4</property>\n" \
                        "<property name=\"bottom_attach\">5</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label17\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL Cipher</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">4</property>\n" \
                        "<property name=\"bottom_attach\">5</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label16\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL CA Path</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">3</property>\n" \
                        "<property name=\"bottom_attach\">4</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label15\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL Certificate Authority</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">2</property>\n" \
                        "<property name=\"bottom_attach\">3</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label13\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL Certificate</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">1</property>\n" \
                        "<property name=\"bottom_attach\">2</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label10\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL Key</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SSL_CAPATH\">\n" \
                        "<property name=\"width_request\">250</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"invisible_char\">•</property>\n" \
                        "<property name=\"primary_icon_activatable\">False</property>\n" \
                        "<property name=\"secondary_icon_activatable\">False</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">3</property>\n" \
                        "<property name=\"bottom_attach\">4</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label_SSL_MODE\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL Mode</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">6</property>\n" \
                        "<property name=\"bottom_attach\">7</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label_RSAKEY\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">RSA Public Key</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">5</property>\n" \
                        "<property name=\"bottom_attach\">6</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"RSAKEY\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">5</property>\n" \
                        "<property name=\"bottom_attach\">6</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"RSAKEY_button\">\n" \
                        "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"top_attach\">5</property>\n" \
                        "<property name=\"bottom_attach\">6</property>\n" \
                        "<property name=\"x_options\"/>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkComboBox\" id=\"SSL_MODE\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"events\">GDK_BUTTON_PRESS_MASK | GDK_STRUCTURE_MASK</property>\n" \
                        "<property name=\"button_sensitivity\">on</property>\n" \
                        "<property name=\"has_entry\">True</property>\n" \
                        "<property name=\"entry_text_column\">0</property>\n" \
                        "<child internal-child=\"entry\">\n" \
                          "<object class=\"GtkEntry\" id=\"SSL_MODE_entry\">\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"editable\">False</property>\n" \
                          "</object>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">6</property>\n" \
                        "<property name=\"bottom_attach\">7</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label_TLS_VERSIONS\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">TLS Versions</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">7</property>\n" \
                        "<property name=\"bottom_attach\">8</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"TLS_VERSIONS\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">7</property>\n" \
                        "<property name=\"bottom_attach\">8</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label_SSL_CRL\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL CRL</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">8</property>\n" \
                        "<property name=\"bottom_attach\">9</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SSL_CRL\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">8</property>\n" \
                        "<property name=\"bottom_attach\">9</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"SSL_CRL_button\">\n" \
                        "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"top_attach\">8</property>\n" \
                        "<property name=\"bottom_attach\">9</property>\n" \
                        "<property name=\"x_options\"/>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkLabel\" id=\"label_SSL_CRLPATH\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"label\" translatable=\"yes\">SSL CRL Path</property>\n" \
                        "<property name=\"xalign\">1</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"top_attach\">9</property>\n" \
                        "<property name=\"bottom_attach\">10</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkEntry\" id=\"SSL_CRLPATH\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">1</property>\n" \
                        "<property name=\"right_attach\">2</property>\n" \
                        "<property name=\"top_attach\">9</property>\n" \
                        "<property name=\"bottom_attach\">10</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkButton\" id=\"SSL_CRLPATH_button\">\n" \
                        "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                        "<property name=\"use_action_appearance\">False</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">2</property>\n" \
                        "<property name=\"right_attach\">3</property>\n" \
                        "<property name=\"top_attach\">9</property>\n" \
                        "<property name=\"bottom_attach\">10</property>\n" \
                        "<property name=\"x_options\"/>\n" \
                        "<property name=\"y_options\"/>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"NO_TLS_1_2\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Disable TLS Version 1.2</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"xalign\">0.5</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">4</property>\n" \
                        "<property name=\"right_attach\">5</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<placeholder/>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkCheckButton\" id=\"NO_TLS_1_3\">\n" \
                        "<property name=\"label\" translatable=\"yes\">Disable TLS Version 1.3</property>\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">True</property>\n" \
                        "<property name=\"receives_default\">False</property>\n" \
                        "<property name=\"xalign\">0</property>\n" \
                        "<property name=\"draw_indicator\">True</property>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"left_attach\">4</property>\n" \
                        "<property name=\"right_attach\">5</property>\n" \
                        "<property name=\"top_attach\">1</property>\n" \
                        "<property name=\"bottom_attach\">2</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">5</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child type=\"tab\">\n" \
                  "<object class=\"GtkLabel\" id=\"lTabSsl\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"xpad\">5</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">SSL</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">5</property>\n" \
                    "<property name=\"tab_fill\">False</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkVBox\" id=\"vboxMisc\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"border_width\">8</property>\n" \
                    "<child>\n" \
                      "<object class=\"GtkTable\" id=\"table5\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"n_rows\">8</property>\n" \
                        "<property name=\"n_columns\">2</property>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<placeholder/>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"SAFE\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Enable Safe Options (see documentation)</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Add some extra safety checks (should not be needed but...).</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0.5</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"DFLT_BIGINT_BIND_STR\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Bind BIGINT parameters as strings</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Causes BIGINT parameters to be bound as strings</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"NO_DATE_OVERFLOW\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Disable Date Overflow error</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"NO_LOCALE\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Don't Use setlocale()</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Disable the use of extended fetch (experimental).</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">1</property>\n" \
                            "<property name=\"bottom_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"IGNORE_SPACE\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Ignore space after function names</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Tell server to ignore space after function name and before ‘(’ (needed by PowerBuilder). This makes all function names keywords.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">2</property>\n" \
                            "<property name=\"bottom_attach\">3</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"USE_MYCNF\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Read options from my.cnf</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Read parameters from the [client] and [odbc] groups from my.cnf.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">3</property>\n" \
                            "<property name=\"bottom_attach\">4</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"NO_TRANSACTIONS\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Disable transaction support</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Disable transactions.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">4</property>\n" \
                            "<property name=\"bottom_attach\">5</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"MIN_DATE_TO_ZERO\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Bind minimal date as zero date</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"has_tooltip\">True</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Translates the minimum ODBC date value (XXXX-01-01) to the zero date format supported by MySQL (XXXX-00-00). This resolves an issue where some statement swill not work because the date returned and the minimumd ODBC date value are incompatible.</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">5</property>\n" \
                            "<property name=\"bottom_attach\">6</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"NO_SSPS\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Prepare statements on the client</property>\n" \
                            "<property name=\"use_action_appearance\">False</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"tooltip_text\" translatable=\"yes\">Do not use the Server-side prepared statements and prepare them on the client</property>\n" \
                            "<property name=\"use_underline\">True</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">6</property>\n" \
                            "<property name=\"bottom_attach\">7</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkCheckButton\" id=\"ENABLE_LOCAL_INFILE\">\n" \
                            "<property name=\"label\" translatable=\"yes\">Enable LOAD DATA LOCAL INFILE statements</property>\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">True</property>\n" \
                            "<property name=\"receives_default\">False</property>\n" \
                            "<property name=\"xalign\">0</property>\n" \
                            "<property name=\"draw_indicator\">True</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"top_attach\">7</property>\n" \
                            "<property name=\"bottom_attach\">8</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">True</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">0</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                    "<child>\n" \
                      "<object class=\"GtkTable\" id=\"table6\">\n" \
                        "<property name=\"visible\">True</property>\n" \
                        "<property name=\"can_focus\">False</property>\n" \
                        "<property name=\"border_width\">8</property>\n" \
                        "<property name=\"n_columns\">2</property>\n" \
                        "<property name=\"column_spacing\">8</property>\n" \
                        "<property name=\"row_spacing\">5</property>\n" \
                        "<child>\n" \
                          "<object class=\"GtkLabel\" id=\"labelPluginDir1\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<property name=\"label\" translatable=\"yes\">LOAD DATA LOCAL directory:</property>\n" \
                            "<property name=\"justify\">right</property>\n" \
                            "<property name=\"width_chars\">10</property>\n" \
                            "<property name=\"max_width_chars\">10</property>\n" \
                            "<property name=\"xalign\">1</property>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"x_options\">GTK_EXPAND | GTK_SHRINK | GTK_FILL</property>\n" \
                            "<property name=\"y_options\"/>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                        "<child>\n" \
                          "<object class=\"GtkHBox\" id=\"hbox_plugin1\">\n" \
                            "<property name=\"visible\">True</property>\n" \
                            "<property name=\"can_focus\">False</property>\n" \
                            "<child>\n" \
                              "<object class=\"GtkEntry\" id=\"LOAD_DATA_LOCAL_DIR\">\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">True</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">0</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                            "<child>\n" \
                              "<object class=\"GtkButton\" id=\"LOAD_DATA_LOCAL_DIR_button\">\n" \
                                "<property name=\"label\" translatable=\"yes\">...</property>\n" \
                                "<property name=\"visible\">True</property>\n" \
                                "<property name=\"can_focus\">True</property>\n" \
                                "<property name=\"receives_default\">True</property>\n" \
                              "</object>\n" \
                              "<packing>\n" \
                                "<property name=\"expand\">False</property>\n" \
                                "<property name=\"fill\">True</property>\n" \
                                "<property name=\"position\">1</property>\n" \
                              "</packing>\n" \
                            "</child>\n" \
                          "</object>\n" \
                          "<packing>\n" \
                            "<property name=\"left_attach\">1</property>\n" \
                            "<property name=\"right_attach\">2</property>\n" \
                          "</packing>\n" \
                        "</child>\n" \
                      "</object>\n" \
                      "<packing>\n" \
                        "<property name=\"expand\">False</property>\n" \
                        "<property name=\"fill\">True</property>\n" \
                        "<property name=\"position\">1</property>\n" \
                      "</packing>\n" \
                    "</child>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">6</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child type=\"tab\">\n" \
                  "<object class=\"GtkLabel\" id=\"lTabMisc\">\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">False</property>\n" \
                    "<property name=\"xpad\">5</property>\n" \
                    "<property name=\"label\" translatable=\"yes\">Misc</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"position\">6</property>\n" \
                    "<property name=\"tab_fill\">False</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
              "</object>\n" \
              "<packing>\n" \
                "<property name=\"expand\">True</property>\n" \
                "<property name=\"fill\">True</property>\n" \
                "<property name=\"position\">1</property>\n" \
              "</packing>\n" \
            "</child>\n" \
            "<child>\n" \
              "<object class=\"GtkHBox\" id=\"hbox1\">\n" \
                "<property name=\"visible\">True</property>\n" \
                "<property name=\"can_focus\">False</property>\n" \
                "<property name=\"spacing\">8</property>\n" \
                "<property name=\"homogeneous\">True</property>\n" \
                "<child>\n" \
                  "<object class=\"GtkButton\" id=\"show_details\">\n" \
                    "<property name=\"label\" translatable=\"yes\">_Details &gt;&gt;</property>\n" \
                    "<property name=\"use_action_appearance\">False</property>\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">True</property>\n" \
                    "<property name=\"receives_default\">False</property>\n" \
                    "<property name=\"use_underline\">True</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"expand\">False</property>\n" \
                    "<property name=\"fill\">True</property>\n" \
                    "<property name=\"position\">0</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkButton\" id=\"hide_details\">\n" \
                    "<property name=\"label\" translatable=\"yes\">_Details &lt;&lt;</property>\n" \
                    "<property name=\"use_action_appearance\">False</property>\n" \
                    "<property name=\"can_focus\">True</property>\n" \
                    "<property name=\"receives_default\">False</property>\n" \
                    "<property name=\"no_show_all\">True</property>\n" \
                    "<property name=\"use_underline\">True</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"expand\">False</property>\n" \
                    "<property name=\"fill\">True</property>\n" \
                    "<property name=\"position\">1</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkButton\" id=\"help\">\n" \
                    "<property name=\"label\">_Help</property>\n" \
                    "<property name=\"use_action_appearance\">False</property>\n" \
                    "<property name=\"can_focus\">True</property>\n" \
                    "<property name=\"receives_default\">False</property>\n" \
                    "<property name=\"use_underline\">True</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"expand\">False</property>\n" \
                    "<property name=\"fill\">True</property>\n" \
                    "<property name=\"pack_type\">end</property>\n" \
                    "<property name=\"position\">2</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkButton\" id=\"cancel\">\n" \
                    "<property name=\"label\" translatable=\"yes\">_Cancel</property>\n" \
                    "<property name=\"use_action_appearance\">False</property>\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">True</property>\n" \
                    "<property name=\"receives_default\">False</property>\n" \
                    "<property name=\"use_underline\">True</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"expand\">False</property>\n" \
                    "<property name=\"fill\">True</property>\n" \
                    "<property name=\"pack_type\">end</property>\n" \
                    "<property name=\"position\">3</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
                "<child>\n" \
                  "<object class=\"GtkButton\" id=\"ok\">\n" \
                    "<property name=\"label\" translatable=\"yes\">_OK</property>\n" \
                    "<property name=\"use_action_appearance\">False</property>\n" \
                    "<property name=\"visible\">True</property>\n" \
                    "<property name=\"can_focus\">True</property>\n" \
                    "<property name=\"receives_default\">False</property>\n" \
                    "<property name=\"use_underline\">True</property>\n" \
                  "</object>\n" \
                  "<packing>\n" \
                    "<property name=\"expand\">False</property>\n" \
                    "<property name=\"fill\">True</property>\n" \
                    "<property name=\"pack_type\">end</property>\n" \
                    "<property name=\"position\">4</property>\n" \
                  "</packing>\n" \
                "</child>\n" \
              "</object>\n" \
              "<packing>\n" \
                "<property name=\"expand\">False</property>\n" \
                "<property name=\"fill\">True</property>\n" \
                "<property name=\"position\">2</property>\n" \
              "</packing>\n" \
            "</child>\n" \
          "</object>\n" \
          "<packing>\n" \
            "<property name=\"expand\">True</property>\n" \
            "<property name=\"fill\">True</property>\n" \
            "<property name=\"position\">1</property>\n" \
          "</packing>\n" \
        "</child>\n" \
      "</object>\n" \
    "</child>\n" \
  "</object>\n" \
"</interface>\n";