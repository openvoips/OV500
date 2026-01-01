// Copyright (c) 2001, 2024, Oracle and/or its affiliates.
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
  @file driver.h
  @brief Definitions needed by the driver
*/

#ifndef __DRIVER_H__
#define __DRIVER_H__

#include "../MYODBC_MYSQL.h"
#include "../MYODBC_CONF.h"
#include "../MYODBC_ODBC.h"
#include "installer.h"

/* Disable _attribute__ on non-gcc compilers. */
#if !defined(__attribute__) && !defined(__GNUC__) && !defined(__clang__)
# define __attribute__(arg)
#endif

#ifdef APSTUDIO_READONLY_SYMBOLS
#define WIN32	/* Hack for rc files */
#endif

/* Needed for offsetof() CPP macro */
#include <stddef.h>

#ifdef RC_INVOKED
#define stdin
#endif

/* Misc definitions for AIX .. */
#ifndef crid_t
 typedef int crid_t;
#endif
#ifndef class_id_t
 typedef unsigned int class_id_t;
#endif


#include "error.h"
#include "parse.h"
#include <vector>
#include <list>
#include <mutex>

#define LOCK_STMT(S) CHECK_HANDLE(S); \
  std::unique_lock<std::recursive_mutex> slock(((STMT*)S)->lock)
#define LOCK_STMT_DEFER(S) CHECK_HANDLE(S); \
  std::unique_lock<std::recursive_mutex> slock(((STMT*)S)->lock, std::defer_lock)
#define DO_LOCK_STMT() slock.lock();

#define LOCK_DBC(D) std::unique_lock<std::recursive_mutex> dlock(((DBC*)D)->lock)
#define LOCK_DBC_DEFER(D) std::unique_lock<std::recursive_mutex> dlock(((DBC*)D)->lock, std::defer_lock)
#define DO_LOCK_DBC() dlock.lock();

#define LOCK_ENV(E) std::unique_lock<std::mutex> elock(E->lock)

// SQL_DRIVER_CONNECT_ATTR_BASE is not defined in all driver managers.
// Therefore use a custom constant until it becomes a standard.
#define MYSQL_DRIVER_CONNECT_ATTR_BASE 0x00004000

#define CB_FIDO_GLOBAL MYSQL_DRIVER_CONNECT_ATTR_BASE + 0x00001000
#define CB_FIDO_CONNECTION MYSQL_DRIVER_CONNECT_ATTR_BASE + 0x00001001

#if defined(_WIN32) || defined(WIN32)
# define INTFUNC  __stdcall
# define EXPFUNC  __stdcall
# if !defined(HAVE_LOCALTIME_R)
#  define HAVE_LOCALTIME_R 1
# endif
#else
# define INTFUNC PASCAL
# define EXPFUNC __export CALLBACK
/* Simple macros to make dl look like the Windows library funcs. */
# define HMODULE void*
# define LoadLibrary(library) dlopen((library), RTLD_GLOBAL | RTLD_LAZY)
# define GetProcAddress(module, proc) dlsym((module), (proc))
# define FreeLibrary(module) dlclose((module))
#endif

#define ODBC_DRIVER	  "ODBC " MYODBC_STRSERIES " Driver"
#define DRIVER_NAME	  "MySQL ODBC " MYODBC_STRSERIES " Driver"
#define DRIVER_NONDSN_TAG "DRIVER={MySQL ODBC " MYODBC_STRSERIES " Driver}"

#if defined(__APPLE__)

#define DRIVER_QUERY_LOGFILE "/tmp/myodbc.sql"

#elif defined(_UNIX_)

#define DRIVER_QUERY_LOGFILE "/tmp/myodbc.sql"

#else

#define DRIVER_QUERY_LOGFILE "myodbc.sql"

#endif

/*
   Internal driver definitions
*/

/* Options for SQLFreeStmt */
#define FREE_STMT_RESET_BUFFERS 1000
#define FREE_STMT_RESET 1001
#define FREE_STMT_CLEAR_RESULT 1
#define FREE_STMT_DO_LOCK 2

#define MYSQL_3_21_PROTOCOL 10	  /* OLD protocol */
#define CHECK_IF_ALIVE	    1800  /* Seconds between queries for ping */

#define MYSQL_MAX_CURSOR_LEN 18   /* Max cursor name length */
#define MYSQL_STMT_LEN 1024	  /* Max statement length */
#define MYSQL_STRING_LEN 1024	  /* Max string length */
#define MYSQL_MAX_SEARCH_STRING_LEN NAME_LEN+10 /* Max search string length */
/* Max Primary keys in a cursor * WHERE clause */
#define MY_MAX_PK_PARTS 32

#ifndef NEAR
#define NEAR
#endif

/* We don't make any assumption about what the default may be. */
#ifndef DEFAULT_TXN_ISOLATION
# define DEFAULT_TXN_ISOLATION 0
#endif

/* For compatibility with old mysql clients - defining error */
#ifndef ER_MUST_CHANGE_PASSWORD_LOGIN
# define ER_MUST_CHANGE_PASSWORD_LOGIN 1820
#endif

#ifndef CR_AUTH_PLUGIN_CANNOT_LOAD_ERROR
# define CR_AUTH_PLUGIN_CANNOT_LOAD_ERROR 2059
#endif

#ifndef SQL_PARAM_DATA_AVAILABLE
# define SQL_PARAM_DATA_AVAILABLE 101
#endif

/* Connection flags to validate after the connection*/
#define CHECK_AUTOCOMMIT_ON	1  /* AUTOCOMMIT_ON */
#define CHECK_AUTOCOMMIT_OFF	2  /* AUTOCOMMIT_OFF */

/* implementation or application descriptor? */
typedef enum { DESC_IMP, DESC_APP } desc_ref_type;

/* parameter or row descriptor? */
typedef enum { DESC_PARAM, DESC_ROW, DESC_UNKNOWN } desc_desc_type;

/* header or record field? (location in descriptor) */
typedef enum { DESC_HDR, DESC_REC } fld_loc;

typedef void (*fido_callback_func)(const char*);
extern fido_callback_func global_fido_callback;
extern std::mutex global_fido_mutex;

/* permissions - header, and base for record */
#define P_RI 1 /* imp */
#define P_WI 2
#define P_RA 4 /* app */
#define P_WA 8

/* macros to encode the constants above */
#define P_ROW(P) (P)
#define P_PAR(P) ((P) << 4)

#define PR_RIR  P_ROW(P_RI)
#define PR_WIR (P_ROW(P_WI) | PR_RIR)
#define PR_RAR  P_ROW(P_RA)
#define PR_WAR (P_ROW(P_WA) | PR_RAR)
#define PR_RIP  P_PAR(P_RI)
#define PR_WIP (P_PAR(P_WI) | PR_RIP)
#define PR_RAP  P_PAR(P_RI)
#define PR_WAP (P_PAR(P_WA) | PR_RAP)

/* macros to test type */
#define IS_APD(d) ((d)->desc_type == DESC_PARAM && (d)->ref_type == DESC_APP)
#define IS_IPD(d) ((d)->desc_type == DESC_PARAM && (d)->ref_type == DESC_IMP)
#define IS_ARD(d) ((d)->desc_type == DESC_ROW && (d)->ref_type == DESC_APP)
#define IS_IRD(d) ((d)->desc_type == DESC_ROW && (d)->ref_type == DESC_IMP)

/* additional field types needed, but not defined in ODBC */
#define SQL_IS_ULEN (-9)
#define SQL_IS_LEN (-10)

/* check if ARD record is a bound column */
#define ARD_IS_BOUND(d) (d)&&((d)->data_ptr || (d)->octet_length_ptr)

/* get the dbc from a descriptor */
#define DESC_GET_DBC(X) (((X)->alloc_type == SQL_DESC_ALLOC_USER) ? \
                         (X)->dbc : (X)->stmt->dbc)

#define IS_BOOKMARK_VARIABLE(S) if (S->stmt_options.bookmarks != \
                                    SQL_UB_VARIABLE) \
  { \
    stmt->set_error("HY092", "Invalid attribute identifier", 0); \
    return SQL_ERROR; \
  }


/* data-at-exec type */
#define DAE_NORMAL 1 /* normal SQLExecute() */
#define DAE_SETPOS_INSERT 2 /* SQLSetPos() insert */
#define DAE_SETPOS_UPDATE 3 /* SQLSetPos() update */
/* data-at-exec handling done for current SQLSetPos() call */
#define DAE_SETPOS_DONE 10

#define DONT_USE_LOCALE_CHECK(STMT) if (!STMT->dbc->ds.opt_NO_LOCALE)

#if defined _WIN32

  #define DECLARE_LOCALE_HANDLE int loc;

    #define __LOCALE_SET(LOC) \
    { \
      loc = _configthreadlocale(0); \
      _configthreadlocale(_ENABLE_PER_THREAD_LOCALE); \
      setlocale(LC_NUMERIC, LOC);  /* force use of '.' as decimal point */ \
    }

  #define __LOCALE_RESTORE() \
    { \
      setlocale(LC_NUMERIC, default_locale.c_str()); \
      _configthreadlocale(loc); \
    }

#elif defined LC_GLOBAL_LOCALE
  #define DECLARE_LOCALE_HANDLE locale_t nloc;

  #define __LOCALE_SET(LOC) \
    { \
      nloc = newlocale(LC_CTYPE_MASK, LOC, (locale_t)0); \
      uselocale(nloc); \
    }

  #define __LOCALE_RESTORE() \
    { \
      uselocale(LC_GLOBAL_LOCALE); \
      freelocale(nloc); \
    }
#else

  #define DECLARE_LOCALE_HANDLE

  #define __LOCALE_SET(LOC) \
      { \
        setlocale(LC_NUMERIC, LOC);  /* force use of '.' as decimal point */ \
      }

  #define __LOCALE_RESTORE() \
      { \
        setlocale(LC_NUMERIC, default_locale.c_str()); \
      }
#endif

#define C_LOCALE_SET(STMT) \
    DONT_USE_LOCALE_CHECK(STMT) \
    __LOCALE_SET("C")

#define DEFAULT_LOCALE_SET(STMT) \
    DONT_USE_LOCALE_CHECK(STMT) \
    __LOCALE_RESTORE()


typedef struct {
  int perms;
  SQLSMALLINT data_type; /* SQL_IS_SMALLINT, etc */
  fld_loc loc;
  size_t offset; /* offset of field in struct */
} desc_field;

/* descriptor */
struct STMT;

struct DESCREC{
  /* ODBC spec fields */
  SQLINTEGER  auto_unique_value; /* row only */
  SQLCHAR *   base_column_name; /* row only */
  SQLCHAR *   base_table_name; /* row only */
  SQLINTEGER  case_sensitive; /* row only */
  SQLCHAR *   catalog_name; /* row only */
  SQLSMALLINT concise_type;
  SQLPOINTER  data_ptr;
  SQLSMALLINT datetime_interval_code;
  SQLINTEGER  datetime_interval_precision;
  SQLLEN      display_size; /* row only */
  SQLSMALLINT fixed_prec_scale;
  SQLLEN  *   indicator_ptr;
  SQLCHAR *   label; /* row only */
  SQLULEN     length;
  SQLCHAR *   literal_prefix; /* row only */
  SQLCHAR *   literal_suffix; /* row only */
  SQLCHAR *   local_type_name;
  SQLCHAR *   name;
  SQLSMALLINT nullable;
  SQLINTEGER  num_prec_radix;
  SQLLEN      octet_length;
  SQLLEN     *octet_length_ptr;
  SQLSMALLINT parameter_type; /* param only */
  SQLSMALLINT precision;
  SQLSMALLINT rowver;
  SQLSMALLINT scale;
  SQLCHAR *   schema_name; /* row only */
  SQLSMALLINT searchable; /* row only */
  SQLCHAR *   table_name; /* row only */
  SQLSMALLINT type;
  SQLCHAR *   type_name;
  SQLSMALLINT unnamed;
  SQLSMALLINT is_unsigned;
  SQLSMALLINT updatable; /* row only */

  desc_desc_type m_desc_type;
  desc_ref_type m_ref_type;

  /* internal descriptor fields */

  /* parameter-specific */
  struct par_struct {
    /* value, value_length, and alloced are used for data
     * at exec parameters */

    tempBuf tempbuf;
    /*
      this parameter is data-at-exec. this is needed as cursor updates
      in ADO change the bind_offset_ptr between SQLSetPos() and the
      final call to SQLParamData() which makes it impossible for us to
      know any longer it was a data-at-exec param.
    */
    char is_dae;
    //my_bool alloced;
    /* Whether this parameter has been bound by the application
     * (if not, was created by dummy execution) */
    my_bool real_param_done;

    par_struct() : tempbuf(0), is_dae(0), real_param_done(false)
    {}

    par_struct(const par_struct& p) :
      tempbuf(p.tempbuf), is_dae(p.is_dae), real_param_done(p.real_param_done)
    { }

    void add_param_data(const char *chunk, unsigned long length);

    size_t val_length()
    {
      // Return the current position, not the buffer length
      return tempbuf.cur_pos;
    }

    char *val()
    {
      return tempbuf.buf;
    }

    void reset()
    {
      tempbuf.reset();
      is_dae = 0;
    }

  }par;

  /* row-specific */
  struct row_struct{
    MYSQL_FIELD * field; /* Used *only* by IRD */
    ulong datalen; /* actual length, maintained for *each* row */
    /* TODO ugly, but easiest way to handle memory */
    SQLCHAR type_name[40];

    row_struct() : field(nullptr), datalen(0)
    {}

    void reset()
    {
      field = nullptr;
      datalen = 0;
      type_name[0] = 0;
    }
  }row;

  void desc_rec_init_apd();
  void desc_rec_init_ipd();
  void desc_rec_init_ard();
  void desc_rec_init_ird();
  void reset_to_defaults();

  DESCREC(desc_desc_type desc_type, desc_ref_type ref_type)
          : auto_unique_value(0), base_column_name(nullptr), base_table_name(nullptr),
            case_sensitive(0), catalog_name(nullptr), concise_type(0), data_ptr(nullptr),
            datetime_interval_code(0), datetime_interval_precision(0), display_size(0),
            fixed_prec_scale(0), indicator_ptr(nullptr), label(nullptr), length(0),
            literal_prefix(nullptr), literal_suffix(nullptr), local_type_name(nullptr),
            name(nullptr), nullable(0), num_prec_radix(0), octet_length(0),
            octet_length_ptr(nullptr), parameter_type(0), precision(0), rowver(0),
            scale(0), schema_name(nullptr), searchable(0), table_name(nullptr), type(0),
            type_name(nullptr), unnamed(0), is_unsigned(0), updatable(0),
            m_desc_type(desc_type), m_ref_type(ref_type)
  {
    reset_to_defaults();
  }

};

struct STMT;
struct DBC;

struct DESC {
  /* header fields */
  SQLSMALLINT   alloc_type = 0;
  SQLULEN       array_size = 0;
  SQLUSMALLINT *array_status_ptr = nullptr;
  /* NOTE: This field is defined as SQLINTEGER* in the descriptor
   * documentation, but corresponds to SQL_ATTR_ROW_BIND_OFFSET_PTR or
   * SQL_ATTR_PARAM_BIND_OFFSET_PTR when set via SQLSetStmtAttr(). The
   * 64-bit ODBC addendum says that when set via SQLSetStmtAttr(), this
   * is now a 64-bit value. These two are conflicting, so we opt for
   * the 64-bit value.
   */
  SQLULEN      *bind_offset_ptr = nullptr;
  SQLINTEGER    bind_type = 0;
  SQLLEN        count = 0;  // Only used for SQLGetDescField()
  SQLLEN        bookmark_count = 0;
  /* Everywhere(http://msdn.microsoft.com/en-us/library/ms713560(VS.85).aspx
     http://msdn.microsoft.com/en-us/library/ms712631(VS.85).aspx) I found
     it's referred as SQLULEN* */
  SQLULEN      *rows_processed_ptr = nullptr;

  /* internal fields */
  desc_desc_type  desc_type = DESC_PARAM;
  desc_ref_type   ref_type = DESC_IMP;

  std::vector<DESCREC> bookmark2;
  std::vector<DESCREC> records2;

  MYERROR error;
  STMT *stmt;
  DBC *dbc;

  void free_paramdata();
  void reset();
  SQLRETURN set_field(SQLSMALLINT recnum, SQLSMALLINT fldid,
                      SQLPOINTER val, SQLINTEGER buflen);

  DESC(STMT *p_stmt, SQLSMALLINT p_alloc_type,
    desc_ref_type p_ref_type, desc_desc_type p_desc_type);

  size_t rcount()
  {
    count = (SQLLEN)records2.size();
    return count;
  }

  ~DESC()
  {
    //if (desc_type == DESC_PARAM && ref_type == DESC_APP)
    //  free_paramdata();
  }

  /* SQL_DESC_ALLOC_USER-specific */
    std::list<STMT*> stmt_list;


  void stmt_list_remove(STMT *stmt)
  {
    if (alloc_type == SQL_DESC_ALLOC_USER)
      stmt_list.remove(stmt);
  }

  void stmt_list_add(STMT *stmt)
  {
    if (alloc_type == SQL_DESC_ALLOC_USER)
      stmt_list.emplace_back(stmt);
  }

  inline bool is_apd()
  {
    return desc_type == DESC_PARAM && ref_type == DESC_APP;
  }

  inline bool is_ipd()
  {
    return desc_type == DESC_PARAM && ref_type == DESC_IMP;
  }

  inline bool is_ard()
  {
    return desc_type == DESC_ROW && ref_type == DESC_APP;
  }

  inline bool is_ird()
  {
    return desc_type == DESC_ROW && ref_type == DESC_IMP;
  }

  SQLRETURN set_error(char *state, const char *message, uint errcode);

};

/* Statement attributes */

struct STMT_OPTIONS
{
  SQLUINTEGER      cursor_type = 0;
  SQLUINTEGER      simulateCursor = 0;
  SQLULEN          max_length = 0, max_rows = 0;
  SQLULEN          query_timeout = -1;
  SQLUSMALLINT    *rowStatusPtr_ex = nullptr; /* set by SQLExtendedFetch */
  bool            retrieve_data = true;
  SQLUINTEGER     bookmarks = 0;
  void            *bookmark_ptr = nullptr;
  bool            bookmark_insert = false;
};


/* Environment handler */

struct	ENV
{
  SQLINTEGER   odbc_ver;
  std::list<DBC*> conn_list;
  MYERROR      error;
  std::mutex lock;

  ENV(SQLINTEGER ver) : odbc_ver(ver)
  {}

  void add_dbc(DBC* dbc);
  void remove_dbc(DBC* dbc);
  bool has_connections();

  ~ENV()
  {}
};


/* Connection handler */

struct DBC
{
  ENV           *env;
  MYSQL         *mysql;
  std::list<STMT*> stmt_list;
  std::list<DESC*> desc_list; // Explicit descriptors
  STMT_OPTIONS  stmt_options;
  MYERROR       error;
  FILE          *query_log = nullptr;
  char          st_error_prefix[255] = { 0 };
  std::string   database;
  SQLUINTEGER   login_timeout = 0;
  time_t        last_query_time = 0;
  int           txn_isolation = 0;
  uint          port = 0;
  uint          cursor_count = 0;
  ulong         net_buffer_len = 0;
  uint          commit_flag = 0;
  bool          has_query_attrs = false;
  std::recursive_mutex lock;

  // Whether SQL*ConnectW was used
  bool          unicode = false;
  // 'ANSI' charset (SQL_C_CHAR)
  CHARSET_INFO  *ansi_charset_info = nullptr,
  // Connection charset ('ANSI' or utf-8)
                *cxn_charset_info = nullptr;
  MY_SYNTAX_MARKERS *syntax = nullptr;
  // data source used to connect (parsed or stored)
  DataSource    ds;
  // value of the sql_select_limit currently set for a session
  //   (SQLULEN)(-1) if wasn't set
  SQLULEN       sql_select_limit = -1;
  // Connection have been put to the pool
  int           need_to_wakeup = 0;
  fido_callback_func fido_callback = nullptr;

  DBC(ENV *p_env);
  void free_explicit_descriptors();
  void free_connection_stmts();
  void add_desc(DESC* desc);
  void remove_desc(DESC *desc);
  SQLRETURN set_error(char *state, const char *message, uint errcode);
  SQLRETURN set_error(char *state);
  SQLRETURN connect(DataSource *ds);
  void execute_prep_stmt(MYSQL_STMT *pstmt, std::string &query,
    MYSQL_BIND *param_bind, MYSQL_BIND *result_bind);

  inline bool transactions_supported()
  { return mysql->server_capabilities & CLIENT_TRANSACTIONS; }

  inline bool autocommit_is_on()
  { return mysql->server_status & SERVER_STATUS_AUTOCOMMIT; }

  void close();
  ~DBC();

  void set_charset(std::string charset);
  SQLRETURN set_charset_options(const char* charset);
  SQLRETURN set_error(myodbc_errid errid, const char* errtext,
    SQLINTEGER errcode);
  SQLRETURN execute_query(const char *query,
    SQLULEN query_length, my_bool req_lock);
};


/* Statement states */

enum MY_STATE { ST_UNKNOWN = 0, ST_PREPARED, ST_PRE_EXECUTED, ST_EXECUTED };
enum MY_DUMMY_STATE { ST_DUMMY_UNKNOWN = 0, ST_DUMMY_PREPARED, ST_DUMMY_EXECUTED };


struct MY_LIMIT_CLAUSE
{
  unsigned long long  offset;
  unsigned int        row_count;
  const char                *begin, *end;
  MY_LIMIT_CLAUSE(unsigned long long offs, unsigned int rc, char* b, char *e) :
    offset(offs), row_count(rc), begin(b), end(e)
  {}

};

struct MY_LIMIT_SCROLLER
{
   tempBuf buf;
   char *query, *offset_pos;
   unsigned int       row_count;
   unsigned long long start_offset;
   unsigned long long next_offset, total_rows, query_len;

   MY_LIMIT_SCROLLER() : buf(1024), query(buf.buf), offset_pos(query),
                         row_count(0), start_offset(0), next_offset(0),
                         total_rows(0), query_len(0)
   {}

   void extend_buf(size_t new_size) {
     // Calculate the current offset
     size_t offset = offset_pos - query;
     buf.extend_buffer(new_size);

     // Point to the new buffer and calculate the new offset position
     query = buf.buf;
     offset_pos = query + offset;
   }

   void reset() { next_offset = 0; offset_pos = query; }
};

/* Statement primary key handler for cursors */
struct MY_PK_COLUMN
{
  char	      name[NAME_LEN+1];
  my_bool     bind_done;

  MY_PK_COLUMN()
  {
    name[0] = 0;
    bind_done = FALSE;
  }
};


/* Statement cursor handler */
struct MYCURSOR
{
  std::string name;
  uint	       pk_count;
  my_bool      pk_validated;
  MY_PK_COLUMN pkcol[MY_MAX_PK_PARTS];

  MYCURSOR() : pk_count(0), pk_validated(FALSE)
  {}

};

enum OUT_PARAM_STATE
{
  OPS_UNKNOWN= 0,
  OPS_BEING_FETCHED,
  OPS_PREFETCHED,
  OPS_STREAMS_PENDING
};


#define CAT_SCHEMA_SET_FULL(STMT, C, S, V, CZ, SZ, CL, SL) { \
  bool cat_is_set = false; \
  if (!STMT->dbc->ds.opt_NO_CATALOG && (CL || !SL)) \
  { \
    C = V;\
    S = nullptr; \
    cat_is_set = true; \
  } \
  if (!STMT->dbc->ds.opt_NO_SCHEMA && !cat_is_set && SZ) \
  { \
    S = V; \
    C = nullptr; \
  } \
}

#define CAT_SCHEMA_SET(C, S, V) \
  CAT_SCHEMA_SET_FULL(stmt, C, S, V, catalog, schema, catalog_len, schema_len)


/* Main statement handler */

struct GETDATA{
  uint column;      /* Which column is being used with SQLGetData() */
  char *source;     /* Our current position in the source. */
  uchar latest[7];  /* Latest character to be converted. */
  int latest_bytes; /* Bytes of data in latest. */
  int latest_used;  /* Bytes of latest that have been used. */
  ulong src_offset; /* @todo remove */
  ulong dst_bytes;  /* Length of data once it is all converted (in chars). */
  ulong dst_offset; /* Current offset into dest. (ulong)~0L when not set. */

  GETDATA() : column(0), source(NULL), latest_bytes(0),
              latest_used(0), src_offset(0), dst_bytes(0), dst_offset(0)
  {}
};

struct ODBC_RESULTSET
{
  MYSQL_RES *res = nullptr;
  ODBC_RESULTSET(MYSQL_RES *r = nullptr) : res(r)
  {}

  void reset(MYSQL_RES *r = nullptr)
  { if (res) mysql_free_result(res); res = r; }

  MYSQL_RES *release()
  {
    MYSQL_RES *tmp = res;
    res = nullptr;
    return tmp;
  }

  MYSQL_RES * operator=(MYSQL_RES *r)
  { reset(r); return res; }

  operator MYSQL_RES*() const
  { return res; }

  operator bool() const
  { return res != nullptr; }

  ~ODBC_RESULTSET() { reset(); }
};

/*
  A string capable of being a NULL
*/
struct xstring : public std::string
{
  bool m_is_null = false;

  using Base = std::string;

  xstring(std::nullptr_t) : m_is_null(true)
  {}

  xstring(char* s) : m_is_null(s == nullptr),
                  Base(s == nullptr ? "" : std::forward<char*>(s))
  {}

  template <class T>
  xstring(T &&s) : Base(std::forward<T>(s))
  {}

  xstring(SQLULEN v) : Base(std::to_string(v))
  {}

  xstring(long long v) : Base(std::to_string(v))
  {}

  xstring(SQLSMALLINT v) : Base(std::to_string(v))
  {}

  xstring(long int v) : Base(std::to_string(v))
  {}

  xstring(int v) : Base(std::to_string(v))
  {}

  xstring(unsigned int v) : Base(std::to_string(v))
  {}


  const char *c_str() const { return m_is_null ? nullptr : Base::c_str(); }
  size_t size() const { return m_is_null ? 0 : Base::size(); }

  bool is_null() { return m_is_null; }

};

struct ROW_STORAGE
{

  typedef std::vector<xstring> vstr;
  typedef std::vector<const char*> pstr;
  size_t m_rnum = 0, m_cnum = 0, m_cur_row = 0, m_cur_col = 0;
  bool m_eof = true;

  /*
    Data and pointers are in separate containers because for the pointers
    we will need to get the sequence of pointers returned by vector::data()
  */
  vstr m_data;
  pstr m_pdata;

  /*
    Setting zero for rows or columns makes the storage object invalid
  */
  size_t set_size(size_t rnum, size_t cnum);

  /*
    Invalidate the data array.
    Returns true if the data actually existed and was invalidated. False otherwise.
  */
  bool invalidate()
  {
    bool was_invalidated = is_valid();
    m_eof = true;
    set_size(0, 0);
    return was_invalidated;
  }

  /* Returns true if current row was last */
  bool eof() { return m_eof; }

  bool is_valid() { return m_rnum * m_cnum > 0; }

  bool next_row();

  /* Set the row counter to the first row */
  void first_row() { m_cur_row = 0; m_eof = m_rnum == 0; }

  xstring& operator[](size_t idx);

  void set_data(size_t idx, void *data, size_t size)
  {
    if (data)
      m_data[m_cur_row * m_cnum + idx].assign((const char*)data, size);
    else
      m_data[m_cur_row * m_cnum + idx] = nullptr;
    m_eof = false;
  }

  /* Copy row data from bind buffer into the storage one row at a time */
  void set_data(MYSQL_BIND *bind)
  {
    for(size_t i = 0; i < m_cnum; ++i)
    {
      if (!(*bind[i].is_null))
        set_data(i, bind[i].buffer, *(bind[i].length));
      else
        set_data(i, nullptr, 0);
    }
  }

  /* Copy data from the storage into the bind buffer one row at a time */
  void fill_data(MYSQL_BIND *bind)
  {
    if (m_cur_row >= m_rnum || m_eof)
      return;

    for(size_t i = 0; i < m_cnum; ++i)
    {
      auto &data = m_data[m_cur_row * m_cnum + i];
      *(bind[i].is_null) = data.is_null();
      *(bind[i].length) = (unsigned long)(data.is_null() ? -1 : data.length());
      if (!data.is_null())
      {
        size_t copy_zero = bind[i].buffer_length > *(bind[i].length) ? 1 : 0;
        memcpy(bind[i].buffer, data.data(), *(bind[i].length) + copy_zero);
      }
    }
    // Set EOF if the last row was filled
    m_eof = (m_rnum <= (m_cur_row + 1));
    // Increment row counter only if not EOF
    m_cur_row += m_eof ? 0 : 1;
  }

  const xstring & operator=(const xstring &val);

  ROW_STORAGE()
  { set_size(0, 0); }

  ROW_STORAGE(size_t rnum, size_t cnum) : m_rnum(rnum), m_cnum(cnum)
  { set_size(rnum, cnum); }

  const char **data();

};

enum class EXCEPTION_TYPE {EMPTY_SET, CONN_ERR, GENERAL};

struct ODBCEXCEPTION
{
  EXCEPTION_TYPE m_type = EXCEPTION_TYPE::GENERAL;
  std::string m_msg;

  ODBCEXCEPTION(EXCEPTION_TYPE t = EXCEPTION_TYPE::GENERAL) : m_type(t)
  {}

  ODBCEXCEPTION(std::string msg, EXCEPTION_TYPE t = EXCEPTION_TYPE::GENERAL) :
    m_type(t), m_msg(msg)
  {}

};

struct ODBC_STMT
{
  MYSQL_STMT *m_stmt = nullptr;

  ODBC_STMT(MYSQL *mysql) {
    if (mysql)
    m_stmt = mysql_stmt_init(mysql);
  }
  operator MYSQL_STMT*() { return m_stmt; }
  ~ODBC_STMT() {
    if (m_stmt)
    mysql_stmt_close(m_stmt);
  }
};


class charPtrBuf {
  private:

  std::vector<char*> m_buf;
  MYSQL_ROW m_external_val = nullptr;

  public:

  void reset() {
     m_buf.clear();
     m_external_val = nullptr;
   }

   void set_size(size_t size) {
     m_buf.resize(size);
     m_external_val = nullptr;
   }

  void set(const void* data, size_t size) {
    set_size(size);
    char *buf = (char*)data;
    std::vector<char*> tmpVec(size, buf);
    m_buf = tmpVec;
  }

  operator MYSQL_ROW() const {
    if (m_external_val || m_buf.size())
      return (MYSQL_ROW)(m_external_val ? m_external_val : m_buf.data());
    return nullptr;
  }

  charPtrBuf &operator=(const MYSQL_ROW external_val) {
    reset();
    m_external_val = external_val;
    return *this;
  }
};

struct STMT
{
  DBC               *dbc;
  MYSQL_RES         *result;
  my_bool           fake_result;
  charPtrBuf        array;
  charPtrBuf        result_array;
  MYSQL_ROW         current_values;
  MYSQL_ROW         (*fix_fields)(STMT *stmt, MYSQL_ROW row);
  MYSQL_FIELD	      *fields;
  MYSQL_ROW_OFFSET  end_of_set;
  tempBuf           tempbuf;
  ROW_STORAGE       m_row_storage;

  MYCURSOR          cursor;
  MYERROR           error;
  STMT_OPTIONS      stmt_options;
  std::string       table_name;
  std::string       catalog_name;

  MY_PARSED_QUERY	query, orig_query;
  std::vector<MYSQL_BIND> param_bind;
  std::vector<MYSQL_BIND> query_attr_bind;
  std::vector<char*>      query_attr_names;

  std::unique_ptr<my_bool[]> rb_is_null;
  std::unique_ptr<my_bool[]> rb_err;
  std::unique_ptr<unsigned long[]> rb_len;
  std::unique_ptr<unsigned long[]> lengths;

  my_ulonglong      affected_rows;
  long              current_row;
  long              cursor_row;
  char              dae_type; /* data-at-exec type */

  GETDATA           getdata;

  uint		param_count, current_param, rows_found_in_set;

  enum MY_STATE state;
  enum MY_DUMMY_STATE dummy_state;

  /* APD for data-at-exec on SQLSetPos() */
  std::unique_ptr<DESC> setpos_apd;
  DESC *setpos_apd2;
  SQLSETPOSIROW setpos_row;
  SQLUSMALLINT setpos_lock;
  SQLUSMALLINT setpos_op;

  MYSQL_STMT *ssps;
  MYSQL_BIND *result_bind;

  MY_LIMIT_SCROLLER scroller;

  enum OUT_PARAM_STATE out_params_state;

  DESC m_ard, *ard;
  DESC m_ird, *ird;
  DESC m_apd, *apd;
  DESC m_ipd, *ipd;

  /* implicit descriptors */
  DESC *imp_ard;
  DESC *imp_apd;

  std::recursive_mutex lock;

  int ssps_bind_result();

  char* extend_buffer(char *to, size_t len);
  char* extend_buffer(size_t len);

  char* add_to_buffer(const char *from, size_t len);
  char* buf() { return tempbuf.buf; }
  char* endbuf() { return tempbuf.buf + tempbuf.cur_pos; }
  size_t buf_pos() { return tempbuf.cur_pos; }
  size_t buf_len() { return tempbuf.buf_len; }
  size_t field_count();
  MYSQL_ROW fetch_row(bool read_unbuffered = false);
  void buf_set_pos(size_t pos) { tempbuf.cur_pos = pos; }
  void buf_add_pos(size_t pos) { tempbuf.cur_pos += pos; }
  void buf_remove_trail_zeroes() { tempbuf.remove_trail_zeroes(); }
  void alloc_lengths(size_t num);
  void free_lengths();
  void reset_getdata_position();
  void reset_setpos_apd();
  void allocate_param_bind(uint elements);
  long compute_cur_row(unsigned fFetchType, SQLLEN irow);

  SQLRETURN bind_query_attrs(bool use_ssps);
  void reset();

  void free_unbind();
  void free_reset_out_params();
  void free_reset_params();
  void free_fake_result(bool clear_all_results);

  bool is_dynamic_cursor();

  SQLRETURN set_error(myodbc_errid errid, const char *errtext, SQLINTEGER errcode);
  SQLRETURN set_error(const char *state, const char *errtext, SQLINTEGER errcode);

  /*
    Error message and errno is taken from dbc->mysql
  */
  SQLRETURN set_error(myodbc_errid errid);

  /*
    Error message and errno is taken from dbc->mysql
  */
  SQLRETURN set_error(const char *state);

  STMT(DBC *d) : dbc(d), result(NULL), fake_result(false), array(), result_array(),
    current_values(NULL), fields(NULL), end_of_set(NULL),
    tempbuf(),
    stmt_options(dbc->stmt_options), lengths(nullptr), affected_rows(0),
    current_row(0), cursor_row(0), dae_type(0),
    param_count(0), current_param(0),
    rows_found_in_set(0),
    state(ST_UNKNOWN), dummy_state(ST_DUMMY_UNKNOWN),
    setpos_row(0), setpos_lock(0), setpos_op(0),
    ssps(NULL), result_bind(NULL), out_params_state(OPS_UNKNOWN),

    m_ard(this, SQL_DESC_ALLOC_AUTO, DESC_APP, DESC_ROW),
    ard(&m_ard),
    m_ird(this, SQL_DESC_ALLOC_AUTO, DESC_IMP, DESC_ROW),
    ird(&m_ird),
    m_apd(this, SQL_DESC_ALLOC_AUTO, DESC_APP, DESC_PARAM),
    apd(&m_apd),
    m_ipd(this, SQL_DESC_ALLOC_AUTO, DESC_IMP, DESC_PARAM),
    ipd(&m_ipd),
    imp_ard(ard), imp_apd(apd)
  {
    allocate_param_bind(10);

    LOCK_DBC(dbc);
    dbc->stmt_list.emplace_back(this);
  }

  ~STMT();
  void clear_query_attr_bind();
  void reset_result_array();
};

namespace myodbc {
  struct HENV
  {
    SQLHENV henv = nullptr;

    HENV(unsigned long v)
    {
      size_t ver = v;
      SQLAllocHandle(SQL_HANDLE_ENV, nullptr, &henv);

      if (SQLSetEnvAttr(henv, SQL_ATTR_ODBC_VERSION, (SQLPOINTER)ver, 0) != SQL_SUCCESS)
      {
        throw MYERROR(SQL_HANDLE_ENV, henv, SQL_ERROR);
      }
    }

    HENV() : HENV(SQL_OV_ODBC3)
    {}

    operator SQLHENV() const
    {
      return henv;
    }

    ~HENV()
    {
      SQLFreeHandle(SQL_HANDLE_ENV, henv);
    }
  };

  struct HDBC
  {
    SQLHDBC hdbc = nullptr;
    SQLHENV henv;
    xstring connout = nullptr;
    SQLCHAR ch_out[512] = { 0 };

    HDBC(SQLHENV env, DataSource *params) : henv(env)
    {
      SQLWSTRING  string_connect_in;

      assert((bool)params->opt_DRIVER);
      params->opt_DSN.set_default(nullptr);
      string_connect_in = params->to_kvpair(';');
      if (SQLAllocHandle(SQL_HANDLE_DBC, henv, &hdbc) != SQL_SUCCESS)
      {
        throw MYERROR(SQL_HANDLE_ENV, henv, SQL_ERROR);
      }

      if (SQLDriverConnectW(hdbc, NULL, (SQLWCHAR*)string_connect_in.c_str(),
        SQL_NTS, NULL, 0, NULL, SQL_DRIVER_NOPROMPT) != SQL_SUCCESS)
      {
        throw MYERROR(SQL_HANDLE_DBC, hdbc, SQL_ERROR);
      }

    }

    operator SQLHDBC() const
    {
      return hdbc;
    }

    ~HDBC()
    {
      SQLDisconnect(hdbc);
      SQLFreeHandle(SQL_HANDLE_DBC, hdbc);
    }
  };


  struct HSTMT
  {
    SQLHDBC hdbc;
    SQLHSTMT hstmt = nullptr;
    HSTMT(SQLHDBC dbc) : hdbc(dbc)
    {
      if (SQLAllocHandle(SQL_HANDLE_STMT, hdbc, &hstmt) != SQL_SUCCESS)
      {
        throw MYERROR(SQL_HANDLE_STMT, hstmt, SQL_ERROR);
      }
    }

    operator SQLHSTMT() const
    {
      return hstmt;
    }

    ~HSTMT()
    {
      SQLFreeHandle(SQL_HANDLE_STMT, hstmt);
    }
  };
};

extern std::string thousands_sep, decimal_point, default_locale;
#ifndef _UNIX_
extern HINSTANCE NEAR s_hModule;  /* DLL handle. */
#endif

#ifdef WIN32
extern std::string current_dll_location;
extern std::string default_plugin_location;
#endif

/*
  Resource defines for "SQLDriverConnect" dialog box
*/
#define ID_LISTBOX  100
#define CONFIGDSN 1001
#define CONFIGDEFAULT 1002
#define EDRIVERCONNECT	1003

/* New data type definitions for compatibility with MySQL 5 */
#ifndef MYSQL_TYPE_NEWDECIMAL
# define MYSQL_TYPE_NEWDECIMAL 246
#endif
#ifndef MYSQL_TYPE_BIT
# define MYSQL_TYPE_BIT 16
#endif

MY_LIMIT_CLAUSE find_position4limit(CHARSET_INFO* cs, const char *query, const char * query_end);

#include "myutil.h"
#include "stringutil.h"


SQLRETURN SQL_API MySQLColAttribute(SQLHSTMT hstmt, SQLUSMALLINT column,
                                    SQLUSMALLINT attrib, SQLCHAR **char_attr,
                                    SQLLEN *num_attr);
SQLRETURN SQL_API MySQLColumnPrivileges(SQLHSTMT hstmt,
                                        SQLCHAR *catalog,
                                        SQLSMALLINT catalog_len,
                                        SQLCHAR *schema, SQLSMALLINT schema_len,
                                        SQLCHAR *table, SQLSMALLINT table_len,
                                        SQLCHAR *column,
                                        SQLSMALLINT column_len);
SQLRETURN SQL_API MySQLColumns(SQLHSTMT hstmt,
                               SQLCHAR *catalog, SQLSMALLINT catalog_len,
                               SQLCHAR *schema, SQLSMALLINT schema_len,
                               SQLCHAR *sztable, SQLSMALLINT table_len,
                               SQLCHAR *column, SQLSMALLINT column_len);
SQLRETURN SQL_API MySQLConnect(SQLHDBC hdbc, SQLWCHAR *szDSN, SQLSMALLINT cbDSN,
                               SQLWCHAR *szUID, SQLSMALLINT cbUID,
                               SQLWCHAR *szAuth, SQLSMALLINT cbAuth);
SQLRETURN SQL_API MySQLDescribeCol(SQLHSTMT hstmt, SQLUSMALLINT column,
                                   SQLCHAR **name, SQLSMALLINT *need_free,
                                   SQLSMALLINT *type, SQLULEN *def,
                                   SQLSMALLINT *scale, SQLSMALLINT *nullable);
SQLRETURN SQL_API MySQLDriverConnect(SQLHDBC hdbc, SQLHWND hwnd,
                                     SQLWCHAR *in, SQLSMALLINT in_len,
                                     SQLWCHAR *out, SQLSMALLINT out_max,
                                     SQLSMALLINT *out_len,
                                     SQLUSMALLINT completion);
SQLRETURN SQL_API MySQLForeignKeys(SQLHSTMT hstmt,
                                   SQLCHAR *pkcatalog,
                                   SQLSMALLINT pkcatalog_len,
                                   SQLCHAR *pkschema, SQLSMALLINT pkschema_len,
                                   SQLCHAR *pktable, SQLSMALLINT pktable_len,
                                   SQLCHAR *fkcatalog,
                                   SQLSMALLINT fkcatalog_len,
                                   SQLCHAR *fkschema, SQLSMALLINT fkschema_len,
                                   SQLCHAR *fktable, SQLSMALLINT fktable_len);
SQLCHAR *MySQLGetCursorName(HSTMT hstmt);
SQLRETURN SQL_API MySQLGetInfo(SQLHDBC hdbc, SQLUSMALLINT fInfoType,
                               SQLCHAR **char_info, SQLPOINTER num_info,
                               SQLSMALLINT *value_len);
SQLRETURN SQL_API MySQLGetConnectAttr(SQLHDBC hdbc, SQLINTEGER attrib,
                                      SQLCHAR **char_attr, SQLPOINTER num_attr);
SQLRETURN MySQLGetDescField(SQLHDESC hdesc, SQLSMALLINT recnum,
                            SQLSMALLINT fldid, SQLPOINTER valptr,
                            SQLINTEGER buflen, SQLINTEGER *strlen);
SQLRETURN SQL_API MySQLGetDiagField(SQLSMALLINT handle_type, SQLHANDLE handle,
                                    SQLSMALLINT record, SQLSMALLINT identifier,
                                    SQLCHAR **char_value, SQLPOINTER num_value);
SQLRETURN SQL_API MySQLGetDiagRec(SQLSMALLINT handle_type, SQLHANDLE handle,
                                  SQLSMALLINT record, SQLCHAR **sqlstate,
                                  SQLINTEGER *native, SQLCHAR **message);
SQLRETURN SQL_API MySQLGetStmtAttr(SQLHSTMT hstmt, SQLINTEGER Attribute,
                                   SQLPOINTER ValuePtr,
                                   SQLINTEGER BufferLength
                                     __attribute__((unused)),
                                  SQLINTEGER *StringLengthPtr);
SQLRETURN SQL_API MySQLGetTypeInfo(SQLHSTMT hstmt, SQLSMALLINT fSqlType);
SQLRETURN SQL_API MySQLPrepare(SQLHSTMT hstmt, SQLCHAR *query, SQLINTEGER len,
                               bool reset_select_limit,
                               bool force_prepare);
SQLRETURN SQL_API MySQLPrimaryKeys(SQLHSTMT hstmt,
                                   SQLCHAR *catalog, SQLSMALLINT catalog_len,
                                   SQLCHAR *schema, SQLSMALLINT schema_len,
                                   SQLCHAR *table, SQLSMALLINT table_len);
SQLRETURN SQL_API MySQLProcedureColumns(SQLHSTMT hstmt,
                                        SQLCHAR *catalog,
                                        SQLSMALLINT catalog_len,
                                        SQLCHAR *schema,
                                        SQLSMALLINT schema_len,
                                        SQLCHAR *proc,
                                        SQLSMALLINT proc_len,
                                        SQLCHAR *column,
                                        SQLSMALLINT column_len);
SQLRETURN SQL_API MySQLProcedures(SQLHSTMT hstmt,
                                  SQLCHAR *catalog, SQLSMALLINT catalog_len,
                                  SQLCHAR *schema, SQLSMALLINT schema_len,
                                  SQLCHAR *proc, SQLSMALLINT proc_len);
SQLRETURN SQL_API MySQLSetConnectAttr(SQLHDBC hdbc, SQLINTEGER Attribute,
                                      SQLPOINTER ValuePtr,
                                      SQLINTEGER StringLengthPtr);
SQLRETURN SQL_API MySQLSetCursorName(SQLHSTMT hstmt, SQLCHAR *name,
                                     SQLSMALLINT len);
SQLRETURN SQL_API MySQLSetStmtAttr(SQLHSTMT hstmt, SQLINTEGER attribute,
                                   SQLPOINTER value, SQLINTEGER len);
SQLRETURN SQL_API MySQLSpecialColumns(SQLHSTMT hstmt, SQLUSMALLINT type,
                                      SQLCHAR *catalog, SQLSMALLINT catalog_len,
                                      SQLCHAR *schema, SQLSMALLINT schema_len,
                                      SQLCHAR *table, SQLSMALLINT table_len,
                                      SQLUSMALLINT scope,
                                      SQLUSMALLINT nullable);
SQLRETURN SQL_API MySQLStatistics(SQLHSTMT hstmt,
                                  SQLCHAR *catalog, SQLSMALLINT catalog_len,
                                  SQLCHAR *schema, SQLSMALLINT schema_len,
                                  SQLCHAR *table, SQLSMALLINT table_len,
                                  SQLUSMALLINT unique, SQLUSMALLINT accuracy);
SQLRETURN SQL_API MySQLTablePrivileges(SQLHSTMT hstmt,
                                       SQLCHAR *catalog,
                                       SQLSMALLINT catalog_len,
                                       SQLCHAR *schema, SQLSMALLINT schema_len,
                                       SQLCHAR *table, SQLSMALLINT table_len);
SQLRETURN SQL_API MySQLTables(SQLHSTMT hstmt,
                              SQLCHAR *catalog, SQLSMALLINT catalog_len,
                              SQLCHAR *schema, SQLSMALLINT schema_len,
                              SQLCHAR *table, SQLSMALLINT table_len,
                              SQLCHAR *type, SQLSMALLINT type_len);

#endif /* __DRIVER_H__ */
