// Copyright (c) 2011, 2024, Oracle and/or its affiliates.
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

/**
 @file  odbcdialogparams.c
 @brief Defines the entry point for the GUI shared library
*/

#include <stdio.h>
#include "../setupgui.h"
#include "pixmaps/connector_odbc_header.xpm"

#if GTK_MAJOR_VERSION >= 3
#include "ui_xml.h"
#else
#include "ui_xml_v2.h"
#endif

#include "stringutil.h"
#include <thread>
#include <chrono>

static DataSource *pParams = NULL;
static gchar*      pCaption= NULL;
static int         OkPressed= 0;

static int         mod= 1;
static BOOL        flag= FALSE;
static BOOL        BusyIndicator= FALSE;

/*
 We need flags to prevent GtkCombobox from re-initializing.
 Otherwise it might render incorrectly or miss the click event.
*/
static BOOL        db_popped_up= FALSE;
static BOOL        cs_popped_up= FALSE;

/* Whether we are in SQLDriverConnect() prompt mode (used to disable fields) */
static BOOL        g_isPrompt;
/* Variable to keep IDC of control where default value were put. It's reset if
   user changes value. Used to verify if we can reset that control's value.
   It won't work if for more than 1 field, but we have only one in visible future. */
static long        controlWithDefValue= 0;
static GtkWidget  *dsnEditDialog;
static GtkWidget  *show_details;
static GtkWidget  *hide_details;
static GtkWidget  *details_note;
static GtkBuilder *builder;

void FillParameters(HWND hwnd, DataSource *params);

static SQLWCHAR out_buf[1024];


void
on_show_details_clicked(GtkButton *button, gpointer user_data)
{
  gtk_widget_show(details_note);
  gtk_widget_hide(show_details);
  gtk_widget_show(hide_details);
}


void
on_hide_details_clicked(GtkButton *button, gpointer user_data)
{
  gtk_widget_hide(details_note);
  gtk_widget_hide(hide_details);
  gtk_widget_show(show_details);
}


void
on_ok_clicked(GtkButton *button, gpointer user_data)
{
  FillParameters((HWND)NULL, pParams);

  if(mytestaccept((HWND)NULL, pParams))
  {
    OkPressed= 1;
    g_object_unref (G_OBJECT (builder));
    gtk_widget_destroy(dsnEditDialog);
    gtk_main_quit();
  }
}


void
on_cancel_clicked(GtkButton *button, gpointer user_data)
{
  OkPressed= 0;
  g_object_unref (G_OBJECT (builder));
  gtk_widget_destroy(dsnEditDialog);
  gtk_main_quit();
}


void on_help_clicked(GtkButton *button, gpointer user_data)
{
  g_spawn_command_line_async ("xdg-open https://dev.mysql.com/doc/connector-odbc"
                              "/en/connector-odbc-configuration.html",
                              NULL);
}


void
on_use_tcp_ip_server_toggled(GtkButton *button, gpointer user_data)
{
  SET_SENSITIVE(server, TRUE);
  SET_SENSITIVE(PORT, TRUE);
  SET_SENSITIVE(SOCKET, FALSE);
  SET_SENSITIVE(ENABLE_DNS_SRV,TRUE);
  SET_SENSITIVE(MULTI_HOST,TRUE);
}


void
on_use_socket_file_toggled(GtkButton *button, gpointer user_data)
{
  SET_SENSITIVE(server, FALSE);
  SET_SENSITIVE(PORT, FALSE);
  SET_SENSITIVE(SOCKET, TRUE);
  SET_SENSITIVE(ENABLE_DNS_SRV,FALSE);
  SET_SENSITIVE(MULTI_HOST,FALSE);
}

void
on_enable_DNS_SRV_toggled(GtkButton *button, gpointer user_data)
{
  if (gtk_toggle_button_get_active (GTK_TOGGLE_BUTTON(button)))
    SET_SENSITIVE(PORT, FALSE);
  else
    SET_SENSITIVE(PORT, TRUE);
}

void on_check_cursor_prefetch_toggled(GtkButton *button, gpointer user_data)
{
  SET_SENSITIVE(PREFETCH,
                getBoolFieldData("cursor_prefetch_active"));
}

void on_test_clicked(GtkButton *button, gpointer user_data)
{
  SQLINTEGER len= SQL_NTS;
  GtkWidget *dialog;
  gchar *displayMsg;

  FillParameters((HWND)NULL, pParams);
  SQLWSTRING msg = mytest(NULL, pParams);
  displayMsg= (gchar*)sqlwchar_as_utf8(msg.c_str(), &len);

  dialog= gtk_message_dialog_new ((GtkWindow*)dsnEditDialog,
                                 GTK_DIALOG_DESTROY_WITH_PARENT,
                                 GTK_MESSAGE_INFO,
                                 GTK_BUTTONS_OK,
                                 "%s", displayMsg);
  gtk_dialog_run (GTK_DIALOG (dialog));
  gtk_widget_destroy (dialog);
  x_free(displayMsg);
}

#if GTK_MAJOR_VERSION >= 3
gboolean
on_database_popup (GtkComboBox *widget,
                   GdkEvent  *event,
                   gpointer   user_data)
{
  GtkTreeIter iter;
  std::vector<SQLWSTRING> dbs;

  /* Active item is to be set only once! */
  if(db_popped_up)
  {
    /* reset it for the next popup */
    db_popped_up= FALSE;
    return FALSE;
  }
  db_popped_up= TRUE;

  if(gtk_combo_box_get_active (widget) < 0)
    gtk_combo_box_set_active (GTK_COMBO_BOX (widget), 0);

  gtk_combo_box_text_remove_all(GTK_COMBO_BOX_TEXT (widget));
  FillParameters((HWND)NULL, pParams);
  dbs = mygetdatabases((HWND)NULL, pParams);

  for (SQLWSTRING dbwname : dbs)
  {
    gchar *dbname;
    SQLINTEGER len= SQL_NTS;

    dbname= (gchar*)sqlwchar_as_utf8((SQLWCHAR*)dbwname.c_str(), &len);
    gtk_combo_box_text_append_text (GTK_COMBO_BOX_TEXT (widget), dbname);

    x_free(dbname);
  }

  return FALSE;
}
#else
void on_database_popup (GtkComboBox *widget, gpointer user_data)
{
  GtkListStore *store;
  GtkTreeIter iter;
  std::vector<SQLWSTRING> dbs;

  /* Active item is to be set only once! */
  if(db_popped_up)
  {
    /* reset it for the next popup */
    db_popped_up= FALSE;
    return;
  }
  db_popped_up= TRUE;

  if(gtk_combo_box_get_active (widget) < 0)
    gtk_combo_box_set_active (GTK_COMBO_BOX (widget), 0);

  FillParameters((HWND)NULL, pParams);

  dbs = mygetdatabases((HWND)NULL, pParams);

  store = gtk_list_store_new(1, G_TYPE_STRING);
  for (SQLWSTRING dbwname : dbs)
  {
    gchar *dbname;
    SQLINTEGER len= SQL_NTS;

    dbname = (gchar*)sqlwchar_as_utf8((SQLWCHAR*)dbwname.c_str(), &len);
    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, dbname, -1);

    x_free(dbname);
  }

  gtk_combo_box_set_model(widget, NULL);
  gtk_combo_box_set_model(widget, GTK_TREE_MODEL(store));
  g_object_unref(store);
}

#endif

void on_tab_press (GtkComboBox *widget, GdkEvent *event, gpointer user_data)
{
  GtkWidget *next_widget, *prev_widget;
  GdkEventKey *key= (GdkEventKey *)event;

  if (user_data == NULL)
  {
    next_widget= GTK_WIDGET (gtk_builder_get_object (builder, "test"));
    prev_widget= GTK_WIDGET (gtk_builder_get_object (builder, "pwd"));;
  }
  else
  {
    next_widget= GTK_WIDGET (gtk_builder_get_object (builder, "INITSTMT"));
    prev_widget= GTK_WIDGET (gtk_builder_get_object (builder, "BIG_PACKETS"));;
  }

  switch (key->keyval)
  {
    case GDK_KEY_Up:
      gtk_widget_grab_focus(prev_widget);
      break;

    case GDK_KEY_Down:
      gtk_combo_box_popup(widget);
      break;

    case GDK_KEY_ISO_Left_Tab:
      gtk_widget_grab_focus(prev_widget);
      break;

    case GDK_KEY_Tab:
      gtk_widget_grab_focus(next_widget);
      break;

  }
}

#if GTK_MAJOR_VERSION >= 3
gboolean
on_charset_popup (GtkComboBox *widget,
                   GdkEvent  *event,
                   gpointer   user_data)
{
  GtkTreeIter iter;
  std::vector<SQLWSTRING> css;

  /* Active item is to be set only once! */
  if(cs_popped_up)
  {
    /* reset it for the next popup */
    cs_popped_up= FALSE;
    return FALSE;
  }
  cs_popped_up= TRUE;

  if(gtk_combo_box_get_active (widget) < 0)
    gtk_combo_box_set_active (GTK_COMBO_BOX (widget), 0);

  gtk_combo_box_text_remove_all(GTK_COMBO_BOX_TEXT (widget));

  FillParameters((HWND)NULL, pParams);
  css= mygetcharsets((HWND)NULL, pParams);

  for(SQLWSTRING cswname : css)
  {
    gchar *csname;
    SQLINTEGER len = SQL_NTS;

    csname= (gchar*)sqlwchar_as_utf8((SQLWCHAR*)cswname.c_str(), &len);
    gtk_combo_box_text_append_text (GTK_COMBO_BOX_TEXT (widget), csname);

    x_free(csname);

  }

  return FALSE;
}
#else
void on_charset_popup (GtkComboBox *widget, gpointer user_data)
{
  GtkListStore *store;
  GtkTreeIter iter;
  std::vector<SQLWSTRING> css;

  /* Active item is to be set only once! */
  if(cs_popped_up)
  {
    /* reset it for the next popup */
    cs_popped_up= FALSE;
    return;
  }
  cs_popped_up= TRUE;

  if(gtk_combo_box_get_active (widget) < 0)
    gtk_combo_box_set_active (GTK_COMBO_BOX (widget), 0);

  FillParameters((HWND)NULL, pParams);

  css= mygetcharsets((HWND)NULL, pParams);

  store = gtk_list_store_new(1, G_TYPE_STRING);
  for(SQLWSTRING cswname : css)
  {
    gchar *csname;
    SQLINTEGER len= SQL_NTS;

    csname= (gchar*)sqlwchar_as_utf8((SQLWCHAR*)cswname.c_str(), &len);
    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, csname, -1);

    x_free(csname);
  }

  gtk_combo_box_set_model(widget, GTK_TREE_MODEL(store));
  g_object_unref(store);
}
#endif

void on_file_button_clicked(GtkComboBox *widget, gpointer user_data)
{
  GtkWidget *dialog;
  dialog = gtk_file_chooser_dialog_new ("Choose File",
                                        GTK_WINDOW(dsnEditDialog),
                                        GTK_FILE_CHOOSER_ACTION_OPEN,
                                        GTK_STOCK_CANCEL, GTK_RESPONSE_CANCEL,
                                        GTK_STOCK_OPEN, GTK_RESPONSE_ACCEPT,
                                        NULL);
  if (gtk_dialog_run (GTK_DIALOG (dialog)) == GTK_RESPONSE_ACCEPT)
  {
    gchar *filename;
    filename = gtk_file_chooser_get_filename (GTK_FILE_CHOOSER (dialog));
    gtk_entry_set_text(GTK_ENTRY(user_data), filename);
    g_free (filename);
  }
  gtk_widget_destroy (dialog);
}


void on_folder_button_clicked(GtkComboBox *widget, gpointer user_data)
{
  GtkWidget *dialog;
  dialog = gtk_file_chooser_dialog_new ("Choose Directory",
                                        GTK_WINDOW(dsnEditDialog),
                                        GTK_FILE_CHOOSER_ACTION_SELECT_FOLDER,
                                        GTK_STOCK_CANCEL, GTK_RESPONSE_CANCEL,
                                        GTK_STOCK_OPEN, GTK_RESPONSE_ACCEPT,
                                        NULL);
  if (gtk_dialog_run (GTK_DIALOG (dialog)) == GTK_RESPONSE_ACCEPT)
  {
    gchar *foldername;
    foldername = gtk_file_chooser_get_filename (GTK_FILE_CHOOSER (dialog));
    gtk_entry_set_text(GTK_ENTRY(user_data), foldername);
    g_free (foldername);
  }
  gtk_widget_destroy (dialog);
}


gboolean getBoolFieldData(gchar *widget_name)
{
  GtkToggleButton *widget= GTK_TOGGLE_BUTTON(gtk_builder_get_object (builder,
                                             widget_name));
  assert(widget);
  return gtk_toggle_button_get_active(widget);
}


void setBoolFieldData(gchar *widget_name, gboolean checked)
{
  GtkToggleButton *widget= GTK_TOGGLE_BUTTON(gtk_builder_get_object (builder,
                                             widget_name));
  if (widget)
  {
    assert(widget);
    gtk_toggle_button_set_active(widget, checked);
  }
}


SQLWCHAR* getStrFieldData(gchar *widget_name)
{
  int len= 0;
  GtkEntry *widget= GTK_ENTRY(gtk_builder_get_object (builder, widget_name));
  assert(widget);


  len= gtk_entry_get_text_length(widget);
  out_buf[0] = 0;

  if(len>0)
  {
    const gchar *entry_text= gtk_entry_get_text(widget);
    /* copy the value for using in DataSource */
    utf8_as_sqlwchar(out_buf, (len + 1) * sizeof (SQLWCHAR), (SQLCHAR*)entry_text,
                       strlen((char*)entry_text));
  }
  return out_buf;
}


void setStrFieldData(gchar *widget_name, SQLCHAR *param)
{
  GtkEntry *widget= GTK_ENTRY(gtk_builder_get_object (builder, widget_name));
  assert(widget);
  if(param && *param)
    gtk_entry_set_text(widget, (gchar*)(param));
}


void setComboFieldData(gchar *widget_name, SQLCHAR *param)
{
  GtkComboBox *widget= GTK_COMBO_BOX(gtk_builder_get_object (builder,
                                                             widget_name));

  GtkEntry *entry= (GtkEntry*)gtk_bin_get_child(GTK_BIN(widget));
  assert(widget);
  if(param && *param)
    gtk_entry_set_text(entry, (gchar*)(param));
}


SQLWCHAR *getComboFieldData(gchar *widget_name)
{
  int len= 0;
  GtkEntry *entry;
  GtkComboBox *widget= GTK_COMBO_BOX(gtk_builder_get_object (builder,
                                                                  widget_name));

  assert(widget);
  entry= (GtkEntry*)gtk_bin_get_child(GTK_BIN(widget));

  len= gtk_entry_get_text_length(entry);
  out_buf[0] = 0;

  if(len>0)
  {
      const gchar *entry_text= gtk_entry_get_text(entry);
      /* copy the value for using in DataSource */
      utf8_as_sqlwchar(out_buf, (len + 1) * sizeof (SQLWCHAR), (SQLCHAR*)entry_text,
                         strlen((char*)entry_text));
  }
  return out_buf;
}


void setSensitive(gchar *widget_name, gboolean state)
{
  GtkWidget *widget= GTK_WIDGET(gtk_builder_get_object (builder, widget_name));
  assert(widget);
  gtk_widget_set_sensitive(widget, state);
}


unsigned int getUnsignedFieldData(gchar *widget_name)
{
  int len= 0;
  GtkSpinButton *widget= GTK_SPIN_BUTTON(gtk_builder_get_object (builder,
                                                                 widget_name));
  assert(widget);
  return (unsigned int) gtk_spin_button_get_value_as_int(widget);
}


void setUnsignedFieldData(gchar *widget_name, unsigned int param)
{
  int len= 0;
  GtkSpinButton *widget= GTK_SPIN_BUTTON(gtk_builder_get_object (builder,
                                                                 widget_name));
  assert(widget);
  gtk_spin_button_set_value(widget, param);
}


/*
   Display the DSN dialog

   @param params DataSource struct, should be pre-populated
   @param ParentWnd Parent dsnEditDialog handle
   @return 1 if the params were correctly populated and OK was pressed
           0 if the dialog was closed or cancelled
*/
int ShowOdbcParamsDialog(DataSource* params, HWND ParentWnd, BOOL isPrompt)
{
  GtkWidget  *dummy;
  GtkEntry   *entry;
  GError     *error= NULL;
  GdkPixbuf  *pixbuf;
  SQLINTEGER len= SQL_NTS;
  int i = 0;

  db_popped_up= FALSE;
  cs_popped_up= FALSE;

  assert(!BusyIndicator);

  pParams= params;
  g_isPrompt= isPrompt;

  gtk_init(nullptr, nullptr);
  /*
     If prompting (with a DSN name), or not prompting (add/edit DSN),
     we translate the lib path to the actual driver name.
  */
  if (params->opt_DSN || !isPrompt)
  {
    Driver driver;
    if (params->opt_DRIVER)
      driver.lib = params->opt_DRIVER;

    if (driver.lookup_name())
    {
      GtkWidget *msg_box;

      msg_box= gtk_message_dialog_new(NULL, GTK_DIALOG_MODAL, GTK_MESSAGE_ERROR, GTK_BUTTONS_CLOSE,
                                      "Failure to lookup driver entry at path '%s'('%s')",
                                      (const char*)driver.lib, params->opt_DSN);

      gtk_dialog_run (GTK_DIALOG (msg_box));
      gtk_widget_hide(msg_box);
      gtk_widget_destroy(msg_box);

      return 0;
    }

    params->opt_DRIVER = driver.name;
  }

  dummy= gtk_vbox_new(0,0);
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_image_new();
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_frame_new(0);
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_table_new(0,0,0);
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_label_new(NULL);
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_entry_new();
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_spin_button_new(0, 0., 0);
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_hseparator_new();
  g_object_ref_sink(G_OBJECT (dummy));
  dummy= gtk_combo_box_new();
  g_object_ref_sink(G_OBJECT (dummy));

#if GTK_MAJOR_VERSION >= 3
  dummy= gtk_combo_box_text_new();
  g_object_ref_sink(G_OBJECT (dummy));
#endif
  builder = gtk_builder_new ();
  /* add GUI definitions from ui_xml.h */
  gtk_builder_add_from_string(builder, ui_xml, -1 /* NULL-terminated */,
                              &error);

  if (error)
  {
    g_error ("ERROR: %s\n", error->message);
    return 0;
  }

  pixbuf= gdk_pixbuf_new_from_xpm_data((const char **)connector_odbc_header_xpm);
  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "header"));
  g_object_set(dummy, "pixbuf", pixbuf, NULL);

  dsnEditDialog= GTK_WIDGET (gtk_builder_get_object (builder, "odbcdialog"));

  details_note= GTK_WIDGET (gtk_builder_get_object (builder, "details_note"));
  show_details= GTK_WIDGET (gtk_builder_get_object (builder, "show_details"));
  hide_details= GTK_WIDGET (gtk_builder_get_object (builder, "hide_details"));

  g_signal_connect ((gpointer) show_details, "clicked",
                    G_CALLBACK (on_show_details_clicked), NULL);

  g_signal_connect ((gpointer) hide_details, "clicked",
                      G_CALLBACK (on_hide_details_clicked), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "ok"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_ok_clicked), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "cancel"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_cancel_clicked), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "help"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_help_clicked), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "test"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_test_clicked), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "DATABASE"));

#if GTK_MAJOR_VERSION >= 3
  g_signal_connect ((gpointer) dummy, "set-focus-child",
                    G_CALLBACK (on_database_popup), NULL);
#else
  g_signal_connect ((gpointer) dummy, "notify::popup-shown",
                    G_CALLBACK (on_database_popup), NULL);
#endif

  /* Work around the keyboard-trapping bug in GTKComboBox */
  g_signal_connect ((gpointer) dummy, "key-press-event",
                    G_CALLBACK (on_tab_press), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "CHARSET"));
#if GTK_MAJOR_VERSION >= 3
  g_signal_connect ((gpointer) dummy, "set-focus-child",
                    G_CALLBACK (on_charset_popup), NULL);
#else
  g_signal_connect ((gpointer) dummy, "notify::popup-shown",
                    G_CALLBACK (on_charset_popup), NULL);
#endif
  /* Work around the keyboard-trapping bug in GTKComboBox */
  g_signal_connect ((gpointer) dummy, "key-press-event",
                    G_CALLBACK (on_tab_press), (gpointer)1);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "use_tcp_ip_server"));
  g_signal_connect ((gpointer) dummy, "toggled",
                    G_CALLBACK (on_use_tcp_ip_server_toggled), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "use_socket_file"));
  g_signal_connect ((gpointer) dummy, "toggled",
                    G_CALLBACK (on_use_socket_file_toggled), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "ENABLE_DNS_SRV"));
  g_signal_connect ((gpointer) dummy, "toggled",
                    G_CALLBACK (on_enable_DNS_SRV_toggled), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "SSL_KEY_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "SSL_KEY"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_file_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "SSL_CERT_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "SSL_CERT"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_file_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "SSL_CA_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "SSL_CA"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_file_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "SSL_CAPATH_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "SSL_CAPATH"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_folder_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "RSAKEY_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "RSAKEY"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_file_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "SSL_CRL_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "SSL_CRL"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_file_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "SSL_CRLPATH_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "SSL_CRLPATH"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_folder_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "cursor_prefetch_active"));
  g_signal_connect ((gpointer) dummy, "toggled",
                    G_CALLBACK (on_check_cursor_prefetch_toggled), NULL);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "plugindir_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "PLUGIN_DIR"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_folder_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "LOAD_DATA_LOCAL_DIR_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "LOAD_DATA_LOCAL_DIR"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_folder_button_clicked), entry);

  dummy= GTK_WIDGET (gtk_builder_get_object (builder, "OCI_CONFIG_FILE_button"));
  entry= GTK_ENTRY (gtk_builder_get_object (builder, "OCI_CONFIG_FILE"));
  g_signal_connect ((gpointer) dummy, "clicked",
                    G_CALLBACK (on_file_button_clicked), entry);

  gtk_builder_connect_signals(builder, NULL);

  gtk_widget_hide(hide_details);

  {
    GtkListStore *store;
    GtkTreeIter iter;
    GtkComboBox *ssl_mode_combo;

    ssl_mode_combo = GTK_COMBO_BOX(gtk_builder_get_object(builder, "SSL_MODE"));
    store = gtk_list_store_new(1, G_TYPE_STRING);

    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, "", -1);

    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, ODBC_SSL_MODE_DISABLED, -1);

    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, ODBC_SSL_MODE_PREFERRED, -1);

    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, ODBC_SSL_MODE_REQUIRED, -1);

    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, ODBC_SSL_MODE_VERIFY_CA, -1);

    gtk_list_store_append(store, &iter);
    gtk_list_store_set(store, &iter, 0, ODBC_SSL_MODE_VERIFY_IDENTITY, -1);


    gtk_combo_box_set_model(ssl_mode_combo, NULL);
    gtk_combo_box_set_model(ssl_mode_combo, GTK_TREE_MODEL(store));
    g_object_unref(store);

  }

  syncForm(ParentWnd, params);
  syncTabs(ParentWnd, params);

  gtk_widget_grab_focus(GTK_WIDGET (dsnEditDialog));

  gtk_widget_show_all(dsnEditDialog);

  gtk_main();

  BusyIndicator= FALSE;

  return OkPressed;
}
