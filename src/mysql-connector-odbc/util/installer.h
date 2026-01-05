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

/*
 * Function prototypes and structures for installer-wrapper functionality.
 */

#ifndef _INSTALLER_H
#define _INSTALLER_H

#include "../MYODBC_CONF.h"
#include "../MYODBC_ODBC.h"
#include <map>
#include <vector>

/* the different modes used when calling MYODBCSetupDataSourceConfig */
#define CONFIG_ADD 1
#define CONFIG_EDIT 2
#define CONFIG_VIEW 3
#define CONFIG_DRIVER_CONNECT 4

UWORD config_get();
UWORD config_set(UWORD mode);

#ifdef __cplusplus
extern "C" {
#endif
// Some forward declarations to avoid including stringutil.h
void sqlwcharfromul(SQLWCHAR *wstr, unsigned long v);
unsigned long sqlwchartoul(const SQLWCHAR *wstr);
#ifdef __cplusplus
}
#endif

using SQLWSTRING = std::basic_string<SQLWCHAR, std::char_traits<SQLWCHAR>,
                                     std::allocator<SQLWCHAR>>;

#ifdef BOOL
// Prevent error in enum opt_type
#undef BOOL
typedef int BOOL;
#endif  // BOOL

class optionBase {
  public:
  enum class opt_type { STRING, INT, BOOL };

  protected:

  // Flag that the value is set
  bool m_is_set = false;

  // Flag that the value is set to default.
  // Used when writing options to INI. Default values are omitted.
  bool m_is_default = false;
  opt_type m_type;

  const char *err_msg_not_set = "Option is used without being set";

  public:

  optionBase() {}
  optionBase(opt_type t) : m_type(t) {}

  bool is_set() { return m_is_set; }
  bool is_default() { return m_is_default; }
  opt_type get_type() { return m_type; }
  virtual operator SQLWSTRING() const = 0;
  virtual const optionBase &operator=(const SQLWSTRING &val) {
    m_is_set = true;
    m_is_default = false;
    return *this;
  };
};

template<typename T>
class optionVal : public optionBase {
  T m_val;

  public:

  constexpr optionVal() {
     m_type = std::is_same<T, int>::value ? opt_type::INT : opt_type::BOOL;
     m_val = (T)0;
  }

  virtual operator SQLWSTRING() const {
    SQLWCHAR numbuf[64];
    if (!m_is_set)
      throw err_msg_not_set;

    sqlwcharfromul(numbuf, (unsigned long)m_val);
    return SQLWSTRING(numbuf);
  }

  virtual operator T() const {
    if (m_is_set)
      return m_val;
    throw err_msg_not_set;
  }

  const optionBase &operator=(const T &val) {
    m_val = val;
    m_is_set = true;
    m_is_default = false;
    return *this;
  }

  virtual const optionBase &operator=(const SQLWSTRING &val) {
    m_val = (T)sqlwchartoul(val.c_str());
    return optionBase::operator=(val);
  }

  void set_default(T val) {
    m_val = val;
    m_is_set = true;
    m_is_default = true;
  }

};

using optionBool = optionVal<bool>;
using optionInt = optionVal<int>;

class optionStr : public optionBase {

  const char *err_msg_null = "Option value is nullptr";

  SQLWSTRING m_wstr;
  std::string m_str;
  bool m_is_null = false;

  void set(const SQLWSTRING &val, bool is_default);
  void set(const std::string &val, bool is_default);

  const SQLCHAR *get() const {
    if (m_is_set)
      return (const SQLCHAR *)(m_is_null ? nullptr : m_str.c_str());
    throw err_msg_not_set;
  }

  const SQLWCHAR *getw() const {
    if (m_is_set)
      return (const SQLWCHAR *)(m_is_null ? nullptr : m_wstr.c_str());
    throw err_msg_not_set;
  }

  virtual void set_null() {
    m_is_set = true;
    m_is_null = true;
    m_is_default = false;
    m_wstr.clear();
    m_str.clear();
  }

 public:

  optionStr() : optionBase(opt_type::STRING) { }

  void set_default(std::nullptr_t) {
    set_null();
    m_is_default = true;
  }

  void set_default(const SQLWCHAR *val) {
    if (val)
      set(val, true);
    else
      set_default(nullptr);
  }

  void set_default(const SQLCHAR *val) {
    if (val)
      set((const char*)val, true);
    else
      set_default(nullptr);
  }

  virtual const optionBase& operator=(const SQLWSTRING &val);
  const optionBase& operator=(const SQLWCHAR *val);

  // Assigning nullptr is equivalent to clearing the option
  const optionStr &operator=(std::nullptr_t) { set_null(); return *this; }
  const optionStr &operator=(const std::string &val);
  operator const SQLCHAR *() const { return get(); }
  operator SQLCHAR *() const { return (SQLCHAR *)get(); }
  operator const char *() const { return (const char*)get(); }
  operator const SQLWCHAR *() const { return getw(); }

  virtual operator bool() const { return (m_is_set && !m_is_null && !m_wstr.empty()); }
  virtual operator SQLWSTRING() const {
    if (m_is_null)
      throw err_msg_null;
    return m_wstr;
  }
  virtual operator const SQLWSTRING&() const {
    if (m_is_null)
      throw err_msg_null;
    return m_wstr;
  }
  void set_remove_brackets(const SQLWCHAR *val_str, SQLINTEGER len);
};

#define DECLARE_OPTIONS(X) optionStr X;

#define DRIVER_OPTIONS_LIST(X) \
  X(name) X(lib) X(setup_lib)

class Driver {

  public:
   DRIVER_OPTIONS_LIST(DECLARE_OPTIONS)

  /*
   * Lookup a driver given only the filename of the driver. This is used:
   *
   * 1. When prompting for additional DSN info upon connect when the
   *    driver uses an external setup library.
   *
   * 2. When testing a connection when adding/editing a DSN.
   */
  int lookup_name();

  /*
   * Lookup a driver in the system. The driver name is read from the given
   * object. If greater-than zero is returned, additional information
   * can be obtained from SQLInstallerError(). A less-than zero return code
   * indicates that the driver could not be found.
   */
  int lookup();

  /*
   * Read the semi-colon delimited key-value pairs the attributes
   * necessary to popular the driver object.
   */
  int from_kvpair_semicolon(const SQLWCHAR *attrs);

  /*
   * Write the attributes of the driver object into key-value pairs
   * separated by single NULL chars.
   */
  int to_kvpair_null(SQLWCHAR *attrs, size_t attrslen);
  Driver();
  ~Driver();
};

/* SQL_MAX_OPTION_STRING_LENGTH = 256, should be ok */
#define ODBCDRIVER_STRLEN SQL_MAX_OPTION_STRING_LENGTH
#define ODBCDATASOURCE_STRLEN SQL_MAX_OPTION_STRING_LENGTH

#if MFA_ENABLED
#define MFA_OPTS(X) X(PWD1) X(PWD2) X(PWD3)
#else
#define MFA_OPTS(X)
#endif

#define STR_OPTIONS_LIST(X)                                                \
  X(DSN)                                                                   \
  X(DRIVER) X(DESCRIPTION) X(SERVER) X(UID) X(PWD) MFA_OPTS(X) X(DATABASE) \
      X(SOCKET) X(INITSTMT) X(CHARSET) X(SSL_KEY) X(SSL_CERT) X(SSL_CA)    \
          X(SSL_CAPATH) X(SSL_CIPHER) X(SSL_MODE) X(RSAKEY) X(SAVEFILE)    \
              X(PLUGIN_DIR) X(DEFAULT_AUTH) X(LOAD_DATA_LOCAL_DIR)         \
                  X(OCI_CONFIG_FILE) X(OCI_CONFIG_PROFILE)                 \
                      X(AUTHENTICATION_KERBEROS_MODE) X(TLS_VERSIONS)      \
                           X(SSL_CRL) X(SSL_CRLPATH) X(SSLVERIFY)

#define INT_OPTIONS_LIST(X)                                         \
  X(PORT)                                                           \
  X(READTIMEOUT) X(WRITETIMEOUT) X(CLIENT_INTERACTIVE)              \
      X(PREFETCH)

#define BOOL_OPTIONS_LIST(X)                                                   \
  X(FOUND_ROWS)                                                                \
  X(BIG_PACKETS) X(COMPRESSED_PROTO) X(NO_BIGINT) X(SAFE) X(AUTO_RECONNECT)    \
      X(AUTO_IS_NULL) X(NO_BINARY_RESULT) X(CAN_HANDLE_EXP_PWD)                \
          X(ENABLE_CLEARTEXT_PLUGIN) X(GET_SERVER_PUBLIC_KEY) X(NO_PROMPT)     \
          X(DYNAMIC_CURSOR) X(NO_DEFAULT_CURSOR) X(NO_LOCALE) X(PAD_SPACE)     \
              X(NO_CACHE) X(FULL_COLUMN_NAMES) X(IGNORE_SPACE) X(NAMED_PIPE)   \
                  X(NO_CATALOG) X(NO_SCHEMA) X(USE_MYCNF) X(NO_TRANSACTIONS)   \
                      X(FORWARD_CURSOR) X(MULTI_STATEMENTS) X(COLUMN_SIZE_S32) \
                          X(MIN_DATE_TO_ZERO) X(ZERO_DATE_TO_MIN)              \
                              X(DFLT_BIGINT_BIND_STR) X(LOG_QUERY) X(NO_SSPS)  \
                                  X(NO_TLS_1_2) X(NO_TLS_1_3)                  \
                                      X(NO_DATE_OVERFLOW)                      \
                                          X(ENABLE_LOCAL_INFILE)               \
                                              X(ENABLE_DNS_SRV) X(MULTI_HOST)

#define FULL_OPTIONS_LIST(X) \
  STR_OPTIONS_LIST(X) INT_OPTIONS_LIST(X) BOOL_OPTIONS_LIST(X)

// List of options that are represented as bits in numeric OPTION value
#define BIT_OPTIONS_LIST(X)                                                  \
  X(FOUND_ROWS)                                                              \
  X(BIG_PACKETS)                                                             \
  X(NO_PROMPT)                                                               \
  X(DYNAMIC_CURSOR) X(NO_DEFAULT_CURSOR) X(NO_LOCALE) X(PAD_SPACE)           \
      X(FULL_COLUMN_NAMES) X(COMPRESSED_PROTO) X(IGNORE_SPACE) X(NAMED_PIPE) \
          X(NO_BIGINT) X(NO_CATALOG) X(USE_MYCNF) X(SAFE) X(NO_TRANSACTIONS) \
              X(LOG_QUERY) X(NO_CACHE) X(FORWARD_CURSOR) X(AUTO_RECONNECT)   \
                  X(AUTO_IS_NULL) X(ZERO_DATE_TO_MIN) X(MIN_DATE_TO_ZERO)    \
                      X(MULTI_STATEMENTS) X(COLUMN_SIZE_S32)                 \
                          X(NO_BINARY_RESULT) X(DFLT_BIGINT_BIND_STR)


#if MFA_ENABLED
#define MFA_ALIASES(X) X(PWD1, PASSWORD1) X(PWD2, PASSWORD2) X(PWD3, PASSWORD3)
#else
#define MFA_ALIASES(X)
#endif

// Pairs of option names that have to be treated as the same
#define ALIAS_OPTIONS_LIST(X) \
  X(UID, USER) X(PWD, PASSWORD) MFA_ALIASES(X) X(DATABASE, DB) \
  X(SSL_KEY, SSLKEY) X(SSL_CERT, SSLCERT) X(SSL_CA, SSLCA) \
  X(SSL_CAPATH, SSLCAPATH) X(SSL_CIPHER, SSLCIPHER) X(SSL_MODE, SSLMODE)

// Options that are not directly written to DSN
#define SKIP_OPTIONS_LIST(X) \
  X(DRIVER) X(DSN)

class DataSource {
  private:
    // Map containing options, the key is SQLWSTRING for easier search
    std::map<SQLWSTRING, optionBase &> m_opt_map;

    // List of option aliases, they will not be written in INI files
    std::vector<SQLWSTRING> m_alias_list;
  public:

#define DECLARE_STR_OPT(X) optionStr opt_##X;
  STR_OPTIONS_LIST(DECLARE_STR_OPT);

#define DECLARE_INT_OPT(X) optionInt opt_##X;
  INT_OPTIONS_LIST(DECLARE_INT_OPT);

#define DECLARE_BOOL_OPT(X) optionBool opt_##X;
  BOOL_OPTIONS_LIST(DECLARE_BOOL_OPT);

  DataSource();

  SQLWSTRING to_kvpair(SQLWCHAR delim);

  void reset();
  int add();
  bool write_opt(const SQLWCHAR *name, const SQLWCHAR *val);
  bool exists();
  void set_numeric_options(unsigned long options);
  void set_val(SQLWCHAR *name, SQLWCHAR *val);
  optionBase *get_opt(SQLWCHAR *name);
  unsigned long get_numeric_options();
  int lookup();
  int from_kvpair(const SQLWCHAR *str, SQLWCHAR delim);
};

/* perhaps that is a good idea to have const ds object with defaults */
extern const unsigned int default_cursor_prefetch;

typedef struct{
  SQLCHAR *type_name;
  SQLINTEGER name_length;
  SQLSMALLINT sql_type;
  SQLSMALLINT mysql_type;
  SQLUINTEGER type_length;
  BOOL binary;
}SQLTypeMap;

#define TYPE_MAP_SIZE 32

int ds_set_strnattr(SQLWCHAR **attr, const SQLWCHAR *val, size_t charcount);
char *ds_get_utf8attr(SQLWCHAR *attrw, SQLCHAR **attr8);

extern const SQLWCHAR W_DRIVER_PARAM[];
extern const SQLWCHAR W_DRIVER_NAME[];
extern const SQLWCHAR W_INVALID_ATTR_STR[];

/*
 * Deprecated connection parameters
 */
#define FLAG_FOUND_ROWS		2   /* Access can't handle affected_rows */
#define FLAG_BIG_PACKETS	8   /* Allow BIG packets. */
#define FLAG_NO_PROMPT		16  /* Don't prompt on connection */
#define FLAG_DYNAMIC_CURSOR	32  /* Enables the dynamic cursor */
#define FLAG_NO_DEFAULT_CURSOR	128 /* No default cursor */
#define FLAG_NO_LOCALE		256  /* No locale specification */
#define FLAG_PAD_SPACE		512  /* Pad CHAR:s with space to max length */
#define FLAG_FULL_COLUMN_NAMES	1024 /* Extends SQLDescribeCol */
#define FLAG_COMPRESSED_PROTO	2048 /* Use compressed protocol */
#define FLAG_IGNORE_SPACE	4096 /* Ignore spaces after function names */
#define FLAG_NAMED_PIPE		8192 /* Force use of named pipes */
#define FLAG_NO_BIGINT		16384	/* Change BIGINT to INT */
#define FLAG_NO_CATALOG		32768	/* No catalog support */
#define FLAG_USE_MYCNF		65536L	/* Read my.cnf at start */
#define FLAG_SAFE		131072L /* Try to be as safe as possible */
#define FLAG_NO_TRANSACTIONS  (FLAG_SAFE << 1) /* Disable transactions */
#define FLAG_LOG_QUERY	      (FLAG_SAFE << 2) /* Query logging, debug */
#define FLAG_NO_CACHE	      (FLAG_SAFE << 3) /* Don't cache the resultset */
 /* Force use of forward-only cursors */
#define FLAG_FORWARD_CURSOR   (FLAG_SAFE << 4)
 /* Force auto-reconnect */
#define FLAG_AUTO_RECONNECT   (FLAG_SAFE << 5)
#define FLAG_AUTO_IS_NULL     (FLAG_SAFE << 6) /* 8388608 Enables SQL_AUTO_IS_NULL */
#define FLAG_ZERO_DATE_TO_MIN (1 << 24) /* Convert XXXX-00-00 date to ODBC min date on results */
#define FLAG_MIN_DATE_TO_ZERO (1 << 25) /* Convert ODBC min date to 0000-00-00 on query */
#define FLAG_MULTI_STATEMENTS (1 << 26) /* Allow multiple statements in a query */
#define FLAG_COLUMN_SIZE_S32 (1 << 27) /* Limit column size to a signed 32-bit value (automatically set for ADO) */
#define FLAG_NO_BINARY_RESULT (1 << 28) /* Disables charset 63 for columns with empty org_table */
/*
  When binding SQL_BIGINT as SQL_C_DEFAULT, treat it as a string
  (automatically set for MS Access) see bug#24535
*/
#define FLAG_DFLT_BIGINT_BIND_STR (1 << 29)
/*
  Use SHOW TABLE STATUS instead Information_Schema DB for table metadata
*/
#define ODBC_SSL_MODE_DISABLED           "DISABLED"
#define ODBC_SSL_MODE_PREFERRED          "PREFERRED"
#define ODBC_SSL_MODE_REQUIRED           "REQUIRED"
#define ODBC_SSL_MODE_VERIFY_CA          "VERIFY_CA"
#define ODBC_SSL_MODE_VERIFY_IDENTITY    "VERIFY_IDENTITY"

#define LPASTE(X) L ## X
#define LSTR(X) LPASTE(X)

#endif /* _INSTALLER_H */

