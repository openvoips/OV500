// Copyright (c) 2000, 2024, Oracle and/or its affiliates.
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
  @file  connect.c
  @brief Connection functions.
*/

#include "driver.h"
#include "installer.h"
#include "stringutil.h"

#include <map>
#include <vector>
#include <sstream>
#include <random>

#ifndef _WIN32
#include <netinet/in.h>
#include <resolv.h>
#else
#include <winsock2.h>
#include <windns.h>
#endif

#ifndef CLIENT_NO_SCHEMA
# define CLIENT_NO_SCHEMA      16
#endif

typedef BOOL (*PromptFunc)(SQLHWND, SQLWCHAR *, SQLUSMALLINT,
                           SQLWCHAR *, SQLSMALLINT, SQLSMALLINT *);

const char *my_os_charset_to_mysql_charset(const char *csname);

bool fido_callback_is_set = false;
fido_callback_func global_fido_callback = nullptr;
std::mutex global_fido_mutex;

bool oci_plugin_is_loaded = false;

/**
  Get the connection flags based on the driver options.

  @param[in]  options  Options flags

  @return Client flags suitable for @c mysql_real_connect().
*/
unsigned long get_client_flags(DataSource *ds)
{
  unsigned long flags= CLIENT_MULTI_RESULTS;

  if (ds->opt_SAFE || ds->opt_FOUND_ROWS)
    flags|= CLIENT_FOUND_ROWS;
  if (ds->opt_COMPRESSED_PROTO)
    flags|= CLIENT_COMPRESS;
  if (ds->opt_IGNORE_SPACE)
    flags|= CLIENT_IGNORE_SPACE;
  if (ds->opt_MULTI_STATEMENTS)
    flags|= CLIENT_MULTI_STATEMENTS;
  if (ds->opt_CLIENT_INTERACTIVE)
    flags|= CLIENT_INTERACTIVE;

  return flags;
}


void DBC::set_charset(std::string charset)
{
  // Use SET NAMES instead of mysql_set_character_set()
  // because running odbc_stmt() is thread safe.
  std::string query = "SET NAMES " + charset;
  if (execute_query(query.c_str(), query.length(), true))
  {
    throw MYERROR("HY000", mysql);
  }
}

/**
 If it was specified, set the character set for the connection and
 other internal charset properties.

 @param[in]  dbc      Database connection
 @param[in]  charset  Character set name
*/
SQLRETURN DBC::set_charset_options(const char *charset)
try
{
  if (unicode)
  {
    if (charset && charset[0])
    {
      ansi_charset_info= get_charset_by_csname(charset,
                                               MYF(MY_CS_PRIMARY),
                                               MYF(0));
      if (!ansi_charset_info)
      {
        std::string errmsg = "Wrong character set name ";
        errmsg.append(charset);
        /* This message should help the user to identify the error */
        throw MYERROR("HY000", errmsg);
      }
    }

    charset = transport_charset;
  }

  if (charset && charset[0])
    set_charset(charset);
  else
    set_charset(ansi_charset_info->csname);

  {
    MY_CHARSET_INFO my_charset;
    mysql_get_character_set_info(mysql, &my_charset);
    cxn_charset_info = get_charset(my_charset.number, MYF(0));
  }

  if (!unicode)
    ansi_charset_info = cxn_charset_info;

  /*
    We always set character_set_results to NULL so we can do our own
    conversion to the ANSI character set or Unicode.
  */
  if (execute_query("SET character_set_results = NULL", SQL_NTS, true)
    != SQL_SUCCESS)
  {
    throw error;
  }

  return SQL_SUCCESS;
}
catch(const MYERROR &e)
{
  error = e;
  return e.retcode;
}


SQLRETURN run_initstmt(DBC* dbc, DataSource* dsrc)
try
{
  if (dsrc->opt_INITSTMT)
  {
    /* Check for SET NAMES */
    if (is_set_names_statement(dsrc->opt_INITSTMT))
    {
      throw MYERROR("HY000", "SET NAMES not allowed by driver");
    }

    if (dbc->execute_query(dsrc->opt_INITSTMT, SQL_NTS, true) != SQL_SUCCESS)
    {
      return SQL_ERROR;
    }
  }
  return SQL_SUCCESS;
}
catch (const MYERROR& e)
{
  dbc->error = e;
  return e.retcode;
}

/*
  Retrieve DNS+SRV list.

  @param[in]  hostname  Full DNS+SRV address (ex: _mysql._tcp.example.com)
  @param[out]  total_weight   Retrieve sum of total weight.

  @return multimap containing list of SRV records
*/


struct Prio
{
  uint16_t prio;
  uint16_t weight;
  operator uint16_t() const
  {
    return prio;
  }

  bool operator < (const Prio &other) const
  {
    return prio < other.prio;
  }
};

struct Srv_host_detail
{
  std::string name;
  unsigned int port = MYSQL_PORT;
};


/*
  Parse a comma separated list of hosts, each optionally specifying a port after
  a colon. If port is not specified then the default port is used.
*/
std::vector<Srv_host_detail> parse_host_list(const char* hosts_str,
                                             unsigned int default_port)
{
  std::vector<Srv_host_detail> list;

  //if hosts_str = nullprt will still add empty host, meaning it will use socket
  std::string hosts(hosts_str ? hosts_str : "");

  size_t pos_i = 0;
  size_t pos_f = std::string::npos;

  do
  {
    pos_f = hosts.find_first_of(",:", pos_i);

    Srv_host_detail host_detail;
    host_detail.name = hosts.substr(pos_i, pos_f-pos_i);

    if( pos_f != std::string::npos && hosts[pos_f] == ':')
    {
      //port
      pos_i = pos_f+1;
      pos_f = hosts.find_first_of(',', pos_i);
      std::string tmp_port = hosts.substr(pos_i,pos_f-pos_i);
      long int val = std::atol(tmp_port.c_str());

      /*
        Note: strtol() returns 0 either if the number is 0
        or conversion was not possible. We distinguish two cases
        by cheking if end pointer was updated.
      */

      if ((val == 0 && tmp_port.length()==0) ||
          (val > 65535 || val < 0))
      {
        std::stringstream err;
        err << "Invalid port value in " << hosts;
        throw err.str();
      }

      host_detail.port = static_cast<uint16_t>(val);
    }
    else
    {
      host_detail.port = default_port;
    }

    pos_i = pos_f+1;

    list.push_back(host_detail);
  }while (pos_f < hosts.size());

  return list;
}


class dbc_guard
{
  DBC *m_dbc;
  bool m_success = false;
  public:

  dbc_guard(DBC *dbc) : m_dbc(dbc) {}
  void set_success(bool success) { m_success = success; }
  ~dbc_guard() { if (!m_success) m_dbc->close(); }
};


MYSQL *new_mysql()
{
  static struct init
  {
    init(){ mysql_library_init(0, nullptr, nullptr); }
  }
  do_init;

  return mysql_init(nullptr);
}


/**
  Try to establish a connection to a MySQL server based on the data source
  configuration.

  @param[in]  ds   Data source information

  @return Standard SQLRETURN code. If it is @c SQL_SUCCESS or @c
  SQL_SUCCESS_WITH_INFO, a connection has been established.
*/
SQLRETURN DBC::connect(DataSource *dsrc)
{
  SQLRETURN rc = SQL_SUCCESS;
  unsigned long flags;
  /* Use 'int' and fill all bits to avoid alignment Bug#25920 */
  unsigned int opt_ssl_verify_server_cert = ~0;
  const my_bool on = 1;
  unsigned int on_int = 1;
  unsigned long max_long = ~0L;
  bool initstmt_executed = false;

  dbc_guard guard(this);

#ifdef WIN32
  /*
   Detect if we are running with ADO present, and force on the
   FLAG_COLUMN_SIZE_S32 option if we are.
  */
  if (GetModuleHandle("msado15.dll") != NULL)
    dsrc->opt_COLUMN_SIZE_S32 = true;

  /* Detect another problem specific to MS Access */
  if (GetModuleHandle("msaccess.exe") != NULL)
    dsrc->opt_DFLT_BIGINT_BIND_STR = true;

  /* MS SQL Likes when the CHAR columns are padded */
  if (GetModuleHandle("sqlservr.exe") != NULL)
    dsrc->opt_PAD_SPACE = true;

#endif

  mysql = new_mysql();

  if (!mysql)
    return set_error("HY001", "Memory allocation error", MYERR_S1001);

  flags = get_client_flags(dsrc);

  /* Set other connection options */

  if (dsrc->opt_BIG_PACKETS || dsrc->opt_SAFE)
#if MYSQL_VERSION_ID >= 50709
    mysql_options(mysql, MYSQL_OPT_MAX_ALLOWED_PACKET, &max_long);
#else
    /* max_allowed_packet is a magical mysql macro. */
    max_allowed_packet = ~0L;
#endif

  if (dsrc->opt_NAMED_PIPE)
    mysql_options(mysql, MYSQL_OPT_NAMED_PIPE, NullS);

  if (dsrc->opt_USE_MYCNF)
    mysql_options(mysql, MYSQL_READ_DEFAULT_GROUP, "odbc");

  if (login_timeout)
    mysql_options(mysql, MYSQL_OPT_CONNECT_TIMEOUT, (char *)&login_timeout);

  if (dsrc->opt_READTIMEOUT) {
    int timeout = dsrc->opt_READTIMEOUT;
    mysql_options(mysql, MYSQL_OPT_READ_TIMEOUT,
                  &timeout);
  }

  if (dsrc->opt_WRITETIMEOUT) {
    int timeout = dsrc->opt_WRITETIMEOUT;
    mysql_options(mysql, MYSQL_OPT_WRITE_TIMEOUT,
                  &timeout);
  }

/*
  Pluggable authentication was introduced in mysql 5.5.7
*/
#if MYSQL_VERSION_ID >= 50507
  if (dsrc->opt_PLUGIN_DIR)
  {
    mysql_options(mysql, MYSQL_PLUGIN_DIR,
                  (const char*)dsrc->opt_PLUGIN_DIR);
  }

#ifdef WIN32
  else
  {
    /*
      If plugin directory is not set we can use the dll location
      for a better chance of finding plugins.
    */
    mysql_options(mysql, MYSQL_PLUGIN_DIR, default_plugin_location.c_str());
  }
#endif

  if (dsrc->opt_DEFAULT_AUTH)
  {
    mysql_options(mysql, MYSQL_DEFAULT_AUTH,
                  (const char*)dsrc->opt_DEFAULT_AUTH);
  }

  /*
   * If fido callback is used the lock must remain till the end of
   * the connection process.
   */
  std::unique_lock<std::mutex> fido_lock(global_fido_mutex);
  /*
  * Set callback even if the value is NULL, but only to reset the previously
  * installed callback.
  */
  fido_callback_func fido_func = fido_callback;

  if(!fido_func && global_fido_callback)
    fido_func = global_fido_callback;

  if (fido_func || fido_callback_is_set)
  {
    struct st_mysql_client_plugin* plugin =
      mysql_client_find_plugin(mysql,
        "authentication_fido_client",
        MYSQL_CLIENT_AUTHENTICATION_PLUGIN);

    if (!plugin)
    {
      return set_error("HY000", "Couldn't load plugin authentication_fido_client", 0);
    }

    if (mysql_plugin_options(plugin, "fido_messages_callback", (const void*)fido_func))
    {
      return set_error("HY000", "Failed to set a FIDO authentication callback function", 0);
    }
    fido_callback_is_set = fido_func;
  }
  else
  {
    // No need to keep the lock if callback is not used.
    fido_lock.unlock();
  }

  bool oci_config_file_set = dsrc->opt_OCI_CONFIG_FILE;
  bool oci_config_profile_set = dsrc->opt_OCI_CONFIG_PROFILE;

  if(oci_config_file_set || oci_config_profile_set || oci_plugin_is_loaded)
  {
    /* load client authentication plugin if required */
    struct st_mysql_client_plugin *plugin =
        mysql_client_find_plugin(mysql,
                                 "authentication_oci_client",
                                 MYSQL_CLIENT_AUTHENTICATION_PLUGIN);

    if(!plugin)
    {
      return set_error("HY000", "Couldn't load plugin authentication_oci_client", 0);
    }

    oci_plugin_is_loaded = true;

    // Set option value or reset by giving nullptr to prevent
    // re-using the value set by another connect (plugin does not
    // reset its options automatically).
    const void *val_to_set = oci_config_file_set ?
      (const char*)dsrc->opt_OCI_CONFIG_FILE :
      nullptr;

    if (mysql_plugin_options(plugin, "oci-config-file", val_to_set) &&
        val_to_set)
    {
      // Error should only be returned if setting non-null option value.
      return set_error("HY000",
          "Failed to set config file for authentication_oci_client plugin", 0);
    }

    val_to_set = oci_config_profile_set ?
      (const char*)dsrc->opt_OCI_CONFIG_PROFILE :
      nullptr;

    if (mysql_plugin_options(plugin, "authentication-oci-client-config-profile",
      val_to_set) && val_to_set)
    {
      return set_error("HY000",
          "Failed to set config profile for authentication_oci_client plugin", 0);
    }
  }

  if (dsrc->opt_AUTHENTICATION_KERBEROS_MODE)
  {
#ifdef WIN32
    /* load client authentication plugin if required */
    struct st_mysql_client_plugin* plugin =
      mysql_client_find_plugin(mysql,
        "authentication_kerberos_client",
        MYSQL_CLIENT_AUTHENTICATION_PLUGIN);

    if (!plugin)
    {
      return set_error("HY000", mysql_error(mysql), 0);
    }

    if (mysql_plugin_options(plugin, "plugin_authentication_kerberos_client_mode",
      (const char*)dsrc->opt_AUTHENTICATION_KERBEROS_MODE))
    {
      return set_error("HY000",
        "Failed to set mode for authentication_kerberos_client plugin", 0);
    }
#else
    if (myodbc_strcasecmp("GSSAPI",
      (const char *)dsrc->opt_AUTHENTICATION_KERBEROS_MODE))
    {
      return set_error("HY000",
        "Invalid value for authentication-kerberos-mode. "
        "Only GSSAPI is supported.", 0);
    }
#endif
  }

#endif

  /* set SSL parameters */
  mysql_ssl_set(mysql,
                dsrc->opt_SSL_KEY,
                dsrc->opt_SSL_CERT,
                dsrc->opt_SSL_CA,
                dsrc->opt_SSL_CAPATH,
                dsrc->opt_SSL_CIPHER);

#if MYSQL_VERSION_ID < 80003
  if (dsrc->SSLVERIFY)
    mysql_options(mysql, MYSQL_OPT_SSL_VERIFY_SERVER_CERT,
                  (const char *)&opt_ssl_verify_server_cert);
#endif

#if MYSQL_VERSION_ID >= 50660
  if (dsrc->opt_RSAKEY)
  {
    /* Read the public key on the client side */
    mysql_options(mysql, MYSQL_SERVER_PUBLIC_KEY,
      (const char*)dsrc->opt_RSAKEY);
  }
#endif
#if MYSQL_VERSION_ID >= 50710
  {
    std::string tls_options;

    if (dsrc->opt_TLS_VERSIONS)
    {
      // If tls-versions is used the NO_TLS_X options are deactivated
      tls_options = (const char*)dsrc->opt_TLS_VERSIONS;
    }
    else
    {
      std::map<std::string, bool> opts = {
        { "TLSv1.2", !dsrc->opt_NO_TLS_1_2 },
        { "TLSv1.3", !dsrc->opt_NO_TLS_1_3 },
      };

      for (auto &opt : opts)
      {
        if (!opt.second)
          continue;

        if (!tls_options.empty())
           tls_options.append(",");
        tls_options.append(opt.first);
      }
    }

    if (!tls_options.length() ||
        mysql_options(mysql, MYSQL_OPT_TLS_VERSION, tls_options.c_str()))
    {
      return set_error("HY000",
        "SSL connection error: No valid TLS version available", 0);
    }
  }
#endif

  if (dsrc->opt_SSL_CRL)
  {
    if (mysql_options(mysql, MYSQL_OPT_SSL_CRL, (const char *)dsrc->opt_SSL_CRL))
    {
      return set_error("HY000", "Failed to set the certificate revocation list file", 0);
    }
  }

  if (dsrc->opt_SSL_CRLPATH)
  {
    if (mysql_options(mysql, MYSQL_OPT_SSL_CRLPATH,
                      (const char *)dsrc->opt_SSL_CRLPATH))
    {
      return set_error("HY000", "Failed to set the certificate revocation list path", 0);
    }
  }

#if MYSQL_VERSION_ID >= 80004
  if (dsrc->opt_GET_SERVER_PUBLIC_KEY)
  {
    /* Get the server public key */
    mysql_options(mysql, MYSQL_OPT_GET_SERVER_PUBLIC_KEY, (const void*)&on);
  }
#endif

  if (unicode)
  {
    /*
      Get the ANSI charset info before we change connection to UTF-8.
    */
    MY_CHARSET_INFO my_charset;
    mysql_get_character_set_info(mysql, &my_charset);
    ansi_charset_info= get_charset(my_charset.number, MYF(0));
    /*
      We always use utf8 for the connection, and change it afterwards if needed.
    */
    mysql_options(mysql, MYSQL_SET_CHARSET_NAME, transport_charset);
    cxn_charset_info= utf8_charset_info;
  }
  else
  {
#ifdef _WIN32
    char cpbuf[64];
    const char *client_cs_name= NULL;

    myodbc_snprintf(cpbuf, sizeof(cpbuf), "cp%u", GetACP());
    client_cs_name= my_os_charset_to_mysql_charset(cpbuf);

    if (client_cs_name)
    {
      mysql_options(mysql, MYSQL_SET_CHARSET_NAME, client_cs_name);
      ansi_charset_info= cxn_charset_info= get_charset_by_csname(client_cs_name, MYF(MY_CS_PRIMARY), MYF(0));
    }
#else
    MY_CHARSET_INFO my_charset;
    mysql_get_character_set_info(mysql, &my_charset);
    ansi_charset_info= get_charset(my_charset.number, MYF(0));
#endif
}

#if MYSQL_VERSION_ID >= 50610
  if (dsrc->opt_CAN_HANDLE_EXP_PWD)
  {
    mysql_options(mysql, MYSQL_OPT_CAN_HANDLE_EXPIRED_PASSWORDS, (char *)&on);
  }
#endif

#if (MYSQL_VERSION_ID >= 50527 && MYSQL_VERSION_ID < 50600) || MYSQL_VERSION_ID >= 50607
  if (dsrc->opt_ENABLE_CLEARTEXT_PLUGIN)
  {
    mysql_options(mysql, MYSQL_ENABLE_CLEARTEXT_PLUGIN, (char *)&on);
  }
#endif

  if (dsrc->opt_ENABLE_LOCAL_INFILE)
  {
    mysql_options(mysql, MYSQL_OPT_LOCAL_INFILE, &on_int);
  }

  if (dsrc->opt_LOAD_DATA_LOCAL_DIR)
  {
    mysql_options(mysql, MYSQL_OPT_LOAD_DATA_LOCAL_DIR,
                  (const char*)dsrc->opt_LOAD_DATA_LOCAL_DIR);
  }

  // Set the connector identification attributes.
  std::string attr_list[][2] = {
    {"_connector_license", MYODBC_LICENSE},
    {"_connector_name", "mysql-connector-odbc"},
    {"_connector_type", MYODBC_STRDRIVERTYPE},
    {"_connector_version", MYODBC_CONN_ATTR_VER}
  };

  for (auto &val : attr_list)
  {
    mysql_options4(mysql, MYSQL_OPT_CONNECT_ATTR_ADD,
      val[0].c_str(), val[1].c_str());
  }

#if MFA_ENABLED
  if(dsrc->pwd1 && dsrc->pwd1[0])
  {
    ds_get_utf8attr(dsrc->pwd1, &dsrc->pwd18);
    int fator = 1;
    mysql_options4(mysql, MYSQL_OPT_USER_PASSWORD,
                   &fator,
                   dsrc->pwd18);
  }

  if(dsrc->pwd2 && dsrc->pwd2[0])
  {
    ds_get_utf8attr(dsrc->pwd2, &dsrc->pwd28);
    int fator = 2;
    mysql_options4(mysql, MYSQL_OPT_USER_PASSWORD,
                   &fator,
                   dsrc->pwd28);
  }

  if(dsrc->pwd3 && dsrc->pwd3[0])
  {
    ds_get_utf8attr(dsrc->pwd3, &dsrc->pwd38);
    int fator = 3;
    mysql_options4(mysql, MYSQL_OPT_USER_PASSWORD,
                   &fator,
                   dsrc->pwd38);
  }
#endif
#if MYSQL_VERSION_ID >= 50711
  if (dsrc->opt_SSL_MODE)
  {
    unsigned int mode = 0;
    if (!myodbc_strcasecmp(ODBC_SSL_MODE_DISABLED, dsrc->opt_SSL_MODE))
      mode = SSL_MODE_DISABLED;
    if (!myodbc_strcasecmp(ODBC_SSL_MODE_PREFERRED, dsrc->opt_SSL_MODE))
      mode = SSL_MODE_PREFERRED;
    if (!myodbc_strcasecmp(ODBC_SSL_MODE_REQUIRED, dsrc->opt_SSL_MODE))
      mode = SSL_MODE_REQUIRED;
    if (!myodbc_strcasecmp(ODBC_SSL_MODE_VERIFY_CA, dsrc->opt_SSL_MODE))
      mode = SSL_MODE_VERIFY_CA;
    if (!myodbc_strcasecmp(ODBC_SSL_MODE_VERIFY_IDENTITY, dsrc->opt_SSL_MODE))
      mode = SSL_MODE_VERIFY_IDENTITY;

    // Don't do anything if there is no match with any of the available modes
    if (mode)
      mysql_options(mysql, MYSQL_OPT_SSL_MODE, &mode);
  }
#endif

  uint16_t total_weight = 0;

  std::vector<Srv_host_detail> hosts;
  try {
    hosts = parse_host_list(dsrc->opt_SERVER, dsrc->opt_PORT);

  } catch (std::string &e)
  {
    return set_error("HY000", e.c_str(), 0);
  }

  if(!dsrc->opt_MULTI_HOST && hosts.size() > 1)
  {
    return set_error("HY000", "Missing option MULTI_HOST=1", 0);
  }

  if(dsrc->opt_ENABLE_DNS_SRV && hosts.size() > 1)
  {
    return set_error("HY000", "Specifying multiple hostnames with DNS SRV look up is not allowed.", 0);
  }

  if(dsrc->opt_ENABLE_DNS_SRV && dsrc->opt_PORT)
  {
    return set_error("HY000", "Specifying a port number with DNS SRV lookup is not allowed.", 0);
  }

  if(dsrc->opt_ENABLE_DNS_SRV)
  {
    if(dsrc->opt_SOCKET)
    {
      return set_error("HY000",
#ifdef _WIN32
                    "Using Named Pipes with DNS SRV lookup is not allowed.",
#else
                    "Using Unix domain sockets with DNS SRV lookup is not allowed",
#endif
                    0);
    }

    if(hosts.empty())
    {
      std::stringstream err;
      err << "Unable to locate any hosts for " << (const char*)dsrc->opt_SERVER;
      return set_error("HY000", err.str().c_str(), 0);
    }

  }

  auto do_connect = [this,&dsrc,&flags](
                    const char *host,
                    unsigned int port
                    ) -> short
  {
    int protocol;
    if(dsrc->opt_SOCKET)
    {
#ifdef _WIN32
      protocol = MYSQL_PROTOCOL_PIPE;
#else
      protocol = MYSQL_PROTOCOL_SOCKET;
#endif
    } else
    {
      protocol = MYSQL_PROTOCOL_TCP;
    }
    mysql_options(mysql, MYSQL_OPT_PROTOCOL, &protocol);

    //Setting server and port
    dsrc->opt_SERVER = host;
    dsrc->opt_PORT = port;

    MYSQL *connect_result = dsrc->opt_ENABLE_DNS_SRV ?
                            mysql_real_connect_dns_srv(mysql,
                              host,
                              dsrc->opt_UID,
                              dsrc->opt_PWD,
                              dsrc->opt_DATABASE,
                              flags)
                            :
                            mysql_real_connect(mysql,
                              host,
                              dsrc->opt_UID,
                              dsrc->opt_PWD,
                              dsrc->opt_DATABASE,
                              port,
                              dsrc->opt_SOCKET,
                              flags);
    if (!connect_result)
    {
      unsigned int native_error= mysql_errno(mysql);

      /* Before 5.6.11 error returned by server was ER_MUST_CHANGE_PASSWORD(1820).
       In 5.6.11 it changed to ER_MUST_CHANGE_PASSWORD_LOGIN(1862)
       We must to change error for old servers in order to set correct sqlstate */
      if (native_error == 1820 && ER_MUST_CHANGE_PASSWORD_LOGIN != 1820)
      {
        native_error= ER_MUST_CHANGE_PASSWORD_LOGIN;
      }

#if MYSQL_VERSION_ID < 50610
      /* In that special case when the driver was linked against old version of libmysql*/
      if (native_error == ER_MUST_CHANGE_PASSWORD_LOGIN
          && dsrc->CAN_HANDLE_EXP_PWD)
      {
        /* The password has expired, application said it knows how to deal with
         that, but the driver was linked  that
         does not support this option. Thus we change native error. */
        /* TODO: enum/defines for driver specific errors */
        return set_conn_error(dbc, MYERR_08004,
                              "Your password has expired, but underlying library doesn't support "
                              "this functionlaity", 0);
      }
#endif
      set_error("HY000", mysql_error(mysql), native_error);

      translate_error((char*)error.sqlstate.c_str(), MYERR_S1000, native_error);

      return SQL_ERROR;
    }

    return SQL_SUCCESS;
  };

  //Connect loop
  {
    bool connected = false;
    std::random_device rd;
    std::mt19937 generator(rd()); // seed the generator


    while(!hosts.empty() && !connected)
    {
      std::uniform_int_distribution<int> distribution(
            0, (int)hosts.size() - 1); // define the range of random numbers

      int pos = distribution(generator);
      auto el = hosts.begin();

      std::advance(el, pos);

      if(do_connect(el->name.c_str(), el->port) == SQL_SUCCESS)
      {
        connected = true;
        break;
      }
      else
      {
        switch (mysql_errno(mysql))
        {
        case ER_CON_COUNT_ERROR:
        case CR_SOCKET_CREATE_ERROR:
        case CR_CONNECTION_ERROR:
        case CR_CONN_HOST_ERROR:
        case CR_IPSOCK_ERROR:
        case CR_UNKNOWN_HOST:
          //On Network errors, continue
          break;
        default:
          //If SQLSTATE not 08xxx, which is used for network errors
          if(strncmp(mysql_sqlstate(mysql), "08", 2) != 0)
          {
            //Return error and do not try another host
            return SQL_ERROR;
          }
        }
      }
      hosts.erase(el);
    }

    if(!connected)
    {
      if(dsrc->opt_ENABLE_DNS_SRV)
      {
        std::string err =
          std::string("Unable to connect to any of the hosts of ") +
          (const char*)dsrc->opt_SERVER + " SRV";
        set_error("HY000", err.c_str(), 0);
      }
      else if (dsrc->opt_MULTI_HOST && hosts.size() > 1) {
        set_error("HY000", "Unable to connect to any of the hosts", 0);
      }
      //The others will retrieve the error from connect
      return SQL_ERROR;
    }
  }

  has_query_attrs = mysql->server_capabilities & CLIENT_QUERY_ATTRIBUTES;

  if (!is_minimum_version(mysql->server_version, "4.1.1"))
  {
    close();
    return set_error("08001", "Driver does not support server versions under 4.1.1", 0);
  }

  rc = set_charset_options(dsrc->opt_CHARSET);

  // It could be an error with expired password in which case we
  // still try to execute init statements and retry below.
  if (rc == SQL_ERROR && error.native_error != ER_MUST_CHANGE_PASSWORD)
    return SQL_ERROR;

  // Try running INITSTMT.
  if (!SQL_SUCCEEDED(run_initstmt(this, dsrc)))
    return error.retcode;

  // If we had expired password error at the beginning
  // try setting charset options again.
  // NOTE: charset name is converted to a single-byte
  //       charset by previous call to ds_get_utf8attr().
  if (rc == SQL_ERROR)
    rc = set_charset_options((const char*)dsrc->opt_CHARSET);

  if (!SQL_SUCCEEDED(rc))
  {
    return SQL_ERROR;
  }

  /*
    The MySQL server has a workaround for old versions of Microsoft Access
    (and possibly other products) that is no longer necessary, but is
    unfortunately enabled by default. We have to turn it off, or it causes
    other problems.
  */
  if (!dsrc->opt_AUTO_IS_NULL &&
      execute_query("SET SQL_AUTO_IS_NULL = 0", SQL_NTS, true) != SQL_SUCCESS)
  {
    return SQL_ERROR;
  }

  ds = *dsrc;
  /* init all needed UTF-8 strings */
  const char *opt_db = ds.opt_DATABASE;
  database = opt_db ? opt_db : "";

  if (ds.opt_LOG_QUERY && !query_log)
    query_log = init_query_log();

  /* Set the statement error prefix based on the server version. */
  strxmov(st_error_prefix, MYODBC_ERROR_PREFIX, "[mysqld-",
          mysql->server_version, "]", NullS);

  /* This needs to be set after connection, or it doesn't stick.  */
  if (ds.opt_AUTO_RECONNECT)
  {
    mysql_options(mysql, MYSQL_OPT_RECONNECT, (char *)&on);
  }

  /* Make sure autocommit is set as configured. */
  if (commit_flag == CHECK_AUTOCOMMIT_OFF)
  {
    if (!transactions_supported() || ds.opt_NO_TRANSACTIONS)
    {
      commit_flag = CHECK_AUTOCOMMIT_ON;
      rc = set_error(MYERR_01S02,
             "Transactions are not enabled, option value "
             "SQL_AUTOCOMMIT_OFF changed to SQL_AUTOCOMMIT_ON",
             SQL_SUCCESS_WITH_INFO);
    }
    else if (autocommit_is_on() && mysql_autocommit(mysql, FALSE))
    {
      /** @todo set error */
      return SQL_ERROR;
    }
  }
  else if ((commit_flag == CHECK_AUTOCOMMIT_ON) &&
           transactions_supported() && !autocommit_is_on())
  {
    if (mysql_autocommit(mysql, TRUE))
    {
      /** @todo set error */
      return SQL_ERROR;
    }
  }

  /* Set transaction isolation as configured. */
  if (txn_isolation != DEFAULT_TXN_ISOLATION)
  {
    char buff[80];
    const char *level;

    if (txn_isolation & SQL_TXN_SERIALIZABLE)
      level= "SERIALIZABLE";
    else if (txn_isolation & SQL_TXN_REPEATABLE_READ)
      level= "REPEATABLE READ";
    else if (txn_isolation & SQL_TXN_READ_COMMITTED)
      level= "READ COMMITTED";
    else
      level= "READ UNCOMMITTED";

    if (transactions_supported())
    {
      sprintf(buff, "SET SESSION TRANSACTION ISOLATION LEVEL %s", level);
      if (execute_query(buff, SQL_NTS, true) != SQL_SUCCESS)
      {
        return SQL_ERROR;
      }
    }
    else
    {
      txn_isolation = SQL_TXN_READ_UNCOMMITTED;
      rc = set_error(MYERR_01S02,
             "Transactions are not enabled, so transaction isolation "
             "was ignored.", SQL_SUCCESS_WITH_INFO);
    }
  }

  mysql_get_option(mysql, MYSQL_OPT_NET_BUFFER_LENGTH, &net_buffer_len);

  guard.set_success(rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO);
  return rc;
}


/**
  Establish a connection to a data source.

  @param[in]  hdbc    Connection handle
  @param[in]  szDSN   Data source name (in connection charset)
  @param[in]  cbDSN   Length of data source name in bytes or @c SQL_NTS
  @param[in]  szUID   User identifier (in connection charset)
  @param[in]  cbUID   Length of user identifier in bytes or @c SQL_NTS
  @param[in]  szAuth  Authentication string (password) (in connection charset)
  @param[in]  cbAuth  Length of authentication string in bytes or @c SQL_NTS

  @return  Standard ODBC success codes

  @since ODBC 1.0
  @since ISO SQL 92
*/
SQLRETURN SQL_API MySQLConnect(SQLHDBC   hdbc,
                               SQLWCHAR *szDSN, SQLSMALLINT cbDSN,
                               SQLWCHAR *szUID, SQLSMALLINT cbUID,
                               SQLWCHAR *szAuth, SQLSMALLINT cbAuth)
{
  SQLRETURN rc;
  DBC *dbc= (DBC *)hdbc;
  DataSource ds;

#ifdef NO_DRIVERMANAGER
  return ((DBC*)dbc)->set_error("HY000",
                       "SQLConnect requires DSN and driver manager", 0);
#else

  /* Can't connect if we're already connected. */
  if (is_connected(dbc))
    return ((DBC*)hdbc)->set_error(MYERR_08002, NULL, 0);

  /* Reset error state */
  CLEAR_DBC_ERROR(dbc);

  if (szDSN && !szDSN[0])
  {
    return ((DBC*)hdbc)->set_error(MYERR_S1000,
      "Invalid connection parameters", 0);
  }

  ds.opt_DSN.set_remove_brackets(szDSN, cbDSN);
  ds.opt_UID.set_remove_brackets(szUID, cbUID);
  ds.opt_PWD.set_remove_brackets(szAuth, cbAuth);

  ds.lookup();

  rc= dbc->connect(&ds);

  return rc;
#endif
}


/**
  An alternative to SQLConnect that allows specifying more of the connection
  parameters, and whether or not to prompt the user for more information
  using the setup library.

  NOTE: The prompting done in this function using MYODBCUTIL_DATASOURCE has
        been observed to cause an access violation outside of our code
        (specifically in Access after prompting, before table link list).
        This has only been seen when setting a breakpoint on this function.

  @param[in]  hdbc  Handle of database connection
  @param[in]  hwnd  Window handle. May be @c NULL if no prompting will be done.
  @param[in]  szConnStrIn  The connection string
  @param[in]  cbConnStrIn  The length of the connection string in characters
  @param[out] szConnStrOut Pointer to a buffer for the completed connection
                           string. Must be at least 1024 characters.
  @param[in]  cbConnStrOutMax Length of @c szConnStrOut buffer in characters
  @param[out] pcbConnStrOut Pointer to buffer for returning length of
                            completed connection string, in characters
  @param[in]  fDriverCompletion Complete mode, one of @cSQL_DRIVER_PROMPT,
              @c SQL_DRIVER_COMPLETE, @c SQL_DRIVER_COMPLETE_REQUIRED, or
              @cSQL_DRIVER_NOPROMPT

  @return Standard ODBC return codes

  @since ODBC 1.0
  @since ISO SQL 92
*/
SQLRETURN SQL_API MySQLDriverConnect(SQLHDBC hdbc, SQLHWND hwnd,
                                     SQLWCHAR *szConnStrIn,
                                     SQLSMALLINT cbConnStrIn,
                                     SQLWCHAR *szConnStrOut,
                                     SQLSMALLINT cbConnStrOutMax,
                                     SQLSMALLINT *pcbConnStrOut,
                                     SQLUSMALLINT fDriverCompletion)
{
  SQLRETURN rc= SQL_SUCCESS;
  DBC *dbc= (DBC *)hdbc;
  DataSource ds;
  /* We may have to read driver info to find the setup library. */
  Driver driver;
  /* We never know how many new parameters might come out of the prompt */
  SQLWCHAR prompt_outstr[4096];
  BOOL bPrompt= FALSE;
  HMODULE hModule= NULL;
  SQLWSTRING conn_str_in, conn_str_out;
  SQLWSTRING prompt_instr;

  if (cbConnStrIn != SQL_NTS)
    conn_str_in = SQLWSTRING(szConnStrIn, cbConnStrIn);
  else
    conn_str_in = szConnStrIn;

  /* Parse the incoming string */
  if (ds.from_kvpair(conn_str_in.c_str(), (SQLWCHAR)';'))
  {
    rc= dbc->set_error( "HY000",
                      "Failed to parse the incoming connect string.", 0);
    goto error;
  }

#ifndef NO_DRIVERMANAGER
  /*
   If the connection string contains the DSN keyword, the driver retrieves
   the information for the specified data source (and merges it into the
   connection info with the provided connection info having precedence).

   This also allows us to get pszDRIVER (if not already given).
  */
  if (ds.opt_DSN)
  {
     ds.lookup();

    /*
      If DSN is used:
      1 - we want the connection string options to override DSN options
      2 - no need to check for parsing erros as it was done before
    */
     ds.from_kvpair(conn_str_in.c_str(), (SQLWCHAR)';');
  }
#endif

  /* If FLAG_NO_PROMPT is not set, force prompting off. */
  if (ds.opt_NO_PROMPT)
    fDriverCompletion= SQL_DRIVER_NOPROMPT;

  /*
    We only prompt if we need to.

    A not-so-obvious gray area is when SQL_DRIVER_COMPLETE or
    SQL_DRIVER_COMPLETE_REQUIRED was specified along without a server or
    password - particularly password. These can work with defaults/blank but
    many callers expect prompting when these are blank. So we compromise; we
    try to connect and if it works we say its ok otherwise we go to
    a prompt.
  */
  switch (fDriverCompletion)
  {
  case SQL_DRIVER_PROMPT:
    bPrompt= TRUE;
    break;

  case SQL_DRIVER_COMPLETE:
  case SQL_DRIVER_COMPLETE_REQUIRED:
    rc = dbc->connect(&ds);
    if (rc == SQL_SUCCESS || rc == SQL_SUCCESS_WITH_INFO)
      goto connected;
    bPrompt= TRUE;
    break;

  case SQL_DRIVER_NOPROMPT:
    bPrompt= FALSE;
    break;

  default:
    rc= dbc->set_error("HY110", "Invalid driver completion.", 0);
    goto error;
  }

#ifdef __APPLE__
  /*
    We don't support prompting on Mac OS X.
  */
  if (bPrompt)
  {
    rc= dbc->set_error("HY000",
                       "Prompting is not supported on this platform. "
                       "Please provide all required connect information.",
                       0);
    goto error;
  }
#endif

  if (bPrompt)
  {
    PromptFunc pFunc;

    /*
     We can not present a prompt unless we can lookup the name of the setup
     library file name. This means we need a DRIVER. We try to look it up
     using a DSN (above) or, hopefully, get one in the connection string.

     This will, when we need to prompt, trump the ODBC rule where we can
     connect with a DSN which does not exist. A possible solution would be to
     hard-code some fall-back value for ds->pszDRIVER.
    */
    if (!ds.opt_DRIVER)
    {
      char szError[1024];
      sprintf(szError,
              "Could not determine the driver name; "
              "could not lookup setup library. DSN=(%s)\n",
              (const char*)ds.opt_DSN);
      rc= dbc->set_error("HY000", szError, 0);
      goto error;
    }

#ifndef NO_DRIVERMANAGER
    /* We can not present a prompt if we have a null window handle. */
    if (!hwnd)
    {
      rc= dbc->set_error("IM008", "Invalid window handle", 0);
      goto error;
    }

    /* if given a named DSN, we will have the path in the DRIVER field */
    if (ds.opt_DSN)
      driver.lib = ds.opt_DRIVER;
    /* otherwise, it's the driver name */
    else
      driver.name = ds.opt_DRIVER;

    if (driver.lookup())
    {
      char sz[1024];
      sprintf(sz, "Could not find driver '%s' in system information.",
              (const char*)ds.opt_DRIVER);

      rc= dbc->set_error("IM003", sz, 0);
      goto error;
    }

    if (!driver.setup_lib)
#endif
    {
      rc= dbc->set_error("HY000",
                        "Could not determine the file name of setup library.",
                        0);
      goto error;
    }

    /*
     We dynamically load the setup library so the driver itself does not
     depend on GUI libraries.
    */
#ifndef WIN32
/*
    lt_dlinit();
*/
#endif

    if (!(hModule= LoadLibrary(driver.setup_lib)))
    {
      char sz[1024];
      sprintf(sz, "Could not load the setup library '%s'.",
              (const char *)driver.setup_lib);
      rc= dbc->set_error("HY000", sz, 0);
      goto error;
    }

    pFunc= (PromptFunc)GetProcAddress(hModule, "Driver_Prompt");

    if (pFunc == NULL)
    {
#ifdef WIN32
      LPVOID pszMsg;

      FormatMessage(FORMAT_MESSAGE_ALLOCATE_BUFFER | FORMAT_MESSAGE_FROM_SYSTEM,
                    NULL, GetLastError(),
                    MAKELANGID(LANG_NEUTRAL, SUBLANG_DEFAULT),
                    (LPTSTR)&pszMsg, 0, NULL);
      rc= dbc->set_error("HY000", (char *)pszMsg, 0);
      LocalFree(pszMsg);
#else
      rc= dbc->set_error("HY000", dlerror(), 0);
#endif
      goto error;
    }

    /* create a string for prompting, and add driver manually */
    prompt_instr = ds.to_kvpair(';');
    prompt_instr.append(W_DRIVER_PARAM);
    SQLWSTRING drv = (const SQLWSTRING&)ds.opt_DRIVER;
    prompt_instr.append(drv);

    /*
      In case the client app did not provide the out string we use our
      inner buffer prompt_outstr
    */
    if (!pFunc(hwnd, (SQLWCHAR*)prompt_instr.c_str(), fDriverCompletion,
               prompt_outstr, sizeof(prompt_outstr), pcbConnStrOut))
    {
      dbc->set_error("HY000", "User cancelled.", 0);
      rc= SQL_NO_DATA;
      goto error;
    }

    /* refresh our DataSource */
    ds.reset();
    if (ds.from_kvpair(prompt_outstr, ';'))
    {
      rc= dbc->set_error( "HY000",
                        "Failed to parse the prompt output string.", 0);
      goto error;
    }

    /*
      We don't need prompt_outstr after the new DataSource is created.
      Copy its contents into szConnStrOut if possible
    */
    if (szConnStrOut)
    {
      *pcbConnStrOut= (SQLSMALLINT)myodbc_min(cbConnStrOutMax, *pcbConnStrOut);
      memcpy(szConnStrOut, prompt_outstr, (size_t)*pcbConnStrOut*sizeof(SQLWCHAR));
      /* term needed if possibly truncated */
      szConnStrOut[*pcbConnStrOut - 1] = 0;
    }

  }

  rc = dbc->connect(&ds);
  if (rc != SQL_SUCCESS && rc != SQL_SUCCESS_WITH_INFO)
  {
    goto error;
  }

  if (ds.opt_SAVEFILE)
  {
    /* We must disconnect if File DSN is created */
    dbc->close();
  }

connected:

  /* copy input to output if connected without prompting */
  if (!bPrompt)
  {
    size_t copylen;
    conn_str_out = conn_str_in;
    if (ds.opt_SAVEFILE)
    {
      SQLWSTRING pwd_temp = (const SQLWSTRING &)ds.opt_PWD;

      /* make sure the password does not go into the output buffer */
      ds.opt_PWD = nullptr;

      conn_str_out = ds.to_kvpair(';');

      /* restore old values */
      ds.opt_PWD = pwd_temp;
    }

    size_t inlen = conn_str_out.length();
    copylen = myodbc_min((size_t)cbConnStrOutMax, inlen + 1) * sizeof(SQLWCHAR);

    if (szConnStrOut && copylen)
    {
      memcpy(szConnStrOut, conn_str_out.c_str(), copylen);
      /* term needed if possibly truncated */
      szConnStrOut[(copylen / sizeof(SQLWCHAR)) - 1] = 0;
    }

    /*
      Even if the buffer for out connection string is NULL
      it will still return the total number of characters
      (excluding the null-termination character for character data)
      available to return in the buffer pointed to by
      OutConnectionString.
      https://learn.microsoft.com/en-us/sql/odbc/reference/syntax/sqldriverconnect-function?view=sql-server-ver16
    */
    if (pcbConnStrOut)
      *pcbConnStrOut = (SQLSMALLINT)inlen;
  }

  /* return SQL_SUCCESS_WITH_INFO if truncated output string */
  if (pcbConnStrOut && cbConnStrOutMax &&
      cbConnStrOutMax - sizeof(SQLWCHAR) <= *pcbConnStrOut * sizeof(SQLWCHAR))
  {
    dbc->set_error("01004", "String data, right truncated.", 0);
    rc= SQL_SUCCESS_WITH_INFO;
  }

error:
  if (hModule)
    FreeLibrary(hModule);

  return rc;
}


void DBC::free_connection_stmts()
{
  for (auto it = stmt_list.begin(); it != stmt_list.end(); )
  {
      STMT *stmt = *it;
      it = stmt_list.erase(it);
      my_SQLFreeStmt((SQLHSTMT)stmt, SQL_DROP);
  }
  stmt_list.clear();
}


/**
  Disconnect a connection.

  @param[in]  hdbc   Connection handle

  @return  Standard ODBC return codes

  @since ODBC 1.0
  @since ISO SQL 92
*/
SQLRETURN SQL_API SQLDisconnect(SQLHDBC hdbc)
{
  DBC *dbc= (DBC *) hdbc;

  CHECK_HANDLE(hdbc);

  dbc->free_connection_stmts();

  dbc->close();

  if (dbc->ds.opt_LOG_QUERY)
    end_query_log(dbc->query_log);

  /* free allocated packet buffer */

  dbc->database.clear();
  return SQL_SUCCESS;
}


void DBC::execute_prep_stmt(MYSQL_STMT *pstmt, std::string &query,
  MYSQL_BIND *param_bind, MYSQL_BIND *result_bind)
{
  if (
    mysql_stmt_prepare(pstmt, query.c_str(), (unsigned long)query.length()) ||
    (param_bind && mysql_stmt_bind_param(pstmt, param_bind)) ||
    mysql_stmt_execute(pstmt) ||
    (result_bind && mysql_stmt_bind_result(pstmt, result_bind))
  )
  {
    set_error("HY000");
    throw error;
  }

  if (

    (result_bind && mysql_stmt_store_result(pstmt))
  )
  {
    set_error("HY000");
    throw error;
  }

}

SQLRETURN DBC::execute_query(const char* query,
  SQLULEN query_length, my_bool req_lock)
{
  SQLRETURN result = SQL_SUCCESS;
  LOCK_DBC_DEFER(this);

  if (req_lock)
  {
    DO_LOCK_DBC();
  }

  if (query_length == SQL_NTS)
  {
    query_length = strlen(query);
  }

  if (check_if_server_is_alive(this) ||
    mysql_real_query(mysql, query, (unsigned long)query_length))
  {
    result = set_error(MYERR_S1000, mysql_error(mysql),
      mysql_errno(mysql));
  }

  return result;

}
