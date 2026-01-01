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

/***************************************************************************
 * MYUTIL.H								   *
 *									   *
 * @description: Prototype definations needed by the driver		   *
 *									   *
 * @author     : MySQL AB(monty@mysql.com, venu@mysql.com)		   *
 * @date       : 2001-Sep-22						   *
 * @product    : myodbc3						   *
 *									   *
 ****************************************************************************/
#ifndef __MYUTIL_H__
#define __MYUTIL_H__

/*
  Utility macros
*/

#define if_forward_cache(st) ((st)->stmt_options.cursor_type == SQL_CURSOR_FORWARD_ONLY && \
           (st)->dbc->ds.opt_NO_CACHE)
#define is_connected(dbc)    ((dbc)->mysql && (dbc)->mysql->net.vio)
#define trans_supported(db) ((db)->mysql->server_capabilities & CLIENT_TRANSACTIONS)
#define autocommit_on(db) ((db)->mysql->server_status & SERVER_STATUS_AUTOCOMMIT)
#define is_no_backslashes_escape_mode(db) ((db)->mysql->server_status & SERVER_STATUS_NO_BACKSLASH_ESCAPES)
#define reset_ptr(x) {if (x) x= 0;}
#define digit(A) ((int) (A - '0'))

#define MYLOG_QUERY(A,B) {if ((A)->dbc->ds.opt_LOG_QUERY) \
               query_print((A)->dbc->query_log,(char*) B);}

#define MYLOG_DBC_QUERY(A,B) {if((A)->ds.opt_LOG_QUERY) \
               query_print((A)->query_log,(char*) B);}

/* A few character sets we care about. */
#define ASCII_CHARSET_NUMBER  11
#define BINARY_CHARSET_NUMBER 63
#define UTF8_CHARSET_NUMBER   33

/* truncation types in SQL_NUMERIC_STRUCT conversions */
#define SQLNUM_TRUNC_FRAC 1
#define SQLNUM_TRUNC_WHOLE 2

/* Conversion to SQL_TIMESTAMP_STRUCT errors(str_to_ts) */
#define SQLTS_NULL_DATE -1
#define SQLTS_BAD_DATE -2

/* Sizes of buffer for converion of 4 and 8 bytes integer values*/
#define MAX32_BUFF_SIZE 11
#define MAX64_BUFF_SIZE 21

#define my_int2str(val, dst, radix, upcase) \
    myodbc_int10_to_str((val), (dst), (radix))

#if MYSQL_VERSION_ID >= 50100
typedef unsigned char * DYNAMIC_ELEMENT;
#else
typedef char * DYNAMIC_ELEMENT;
#endif

/* Same us MYODBC_FIELD_STRING(name, NAME_LEN, flags) */
# define MYODBC_FIELD_NAME(name, flags) \
  {(char*)(name), (char*)(name), NullS, NullS, NullS, NullS, NullS, NAME_LEN, 0, 0, 0, 0, 0, 0, \
    0, 0, (flags), 0, UTF8_CHARSET_NUMBER, MYSQL_TYPE_VAR_STRING, NULL}
# define MYODBC_FIELD_STRING(name, len, flags) \
  {(char*)(name), (char*)(name), NullS, NullS, NullS, NullS, NullS, (len*SYSTEM_CHARSET_MBMAXLEN), 0, 0, 0, 0, 0, 0, \
    0, 0, (flags), 0, UTF8_CHARSET_NUMBER, MYSQL_TYPE_VAR_STRING, NULL}
# define MYODBC_FIELD_SHORT(name, flags) \
  {(char*)(name), (char*)(name), NullS, NullS, NullS, NullS, NullS, 5, 5, 0, 0, 0, 0, 0, 0, \
    0, (flags), 0, 0, MYSQL_TYPE_SHORT, NULL}
# define MYODBC_FIELD_LONG(name, flags) \
  {(char*)(name), (char*)(name), NullS, NullS, NullS, NullS, NullS, 11, 11, 0, 0, 0, 0, 0, \
    0, 0, (flags), 0, 0, MYSQL_TYPE_LONG, NULL}
# define MYODBC_FIELD_LONGLONG(name, flags) \
  {(char*)(name), (char*)(name), NullS, NullS, NullS, NullS, NullS, 20, 20, 0, 0, 0, 0, 0, \
    0, 0, (flags), 0, 0, MYSQL_TYPE_LONGLONG, NULL}

/*
  Utility function prototypes that share among files
*/

SQLRETURN         my_SQLPrepare (SQLHSTMT hstmt, SQLCHAR *szSqlStr,
                                 SQLINTEGER cbSqlStr,
                                 bool reset_select_limit,
                                 bool force_prepare);
SQLRETURN         my_SQLExecute         (STMT * stmt);
SQLRETURN SQL_API my_SQLFreeStmt        (SQLHSTMT hstmt,SQLUSMALLINT fOption);
SQLRETURN SQL_API my_SQLFreeStmtExtended(SQLHSTMT hstmt, SQLUSMALLINT fOption,
                                         SQLUSMALLINT fExtra);
SQLRETURN SQL_API my_SQLAllocStmt       (SQLHDBC hdbc,SQLHSTMT *phstmt);
SQLRETURN         do_query              (STMT *stmt, std::string query);
SQLRETURN         insert_params         (STMT *stmt, SQLULEN row, std::string &finalquery);
void      myodbc_link_fields (STMT *stmt,MYSQL_FIELD *fields,uint field_count);
void      fix_row_lengths   (STMT *stmt, const long* fix_rules, uint row, uint field_count);
void      fix_result_types  (STMT *stmt);
char *    fix_str           (char *to,const char *from,int length);
char *    dupp_str          (char *from,int length);
SQLRETURN my_pos_delete_std (STMT *stmt,STMT *stmtParam,
                        SQLUSMALLINT irow, std::string &str);
SQLRETURN my_pos_update_std (STMT *stmt,STMT *stmtParam,
                        SQLUSMALLINT irow, std::string &str);

char *    check_if_positioned_cursor_exists (STMT *stmt, STMT **stmtNew);
SQLRETURN insert_param  (STMT *stmt, MYSQL_BIND *bind, DESC *apd,
                        DESCREC *aprec, DESCREC *iprec, SQLULEN row);

SQLRETURN set_sql_select_limit(DBC *dbc, SQLULEN new_value, my_bool reqLock);
SQLRETURN exec_stmt_query(STMT *stmt, const char *query, SQLULEN query_length,
                           my_bool reqLock);

SQLRETURN exec_stmt_query_std(STMT *stmt, const std::string &str,
                              bool reqLock);

SQLRETURN
copy_ansi_result(STMT *stmt,
                 SQLCHAR *result, SQLLEN result_bytes, SQLLEN *used_bytes,
                 MYSQL_FIELD *field, char *src, unsigned long src_bytes);
SQLRETURN
copy_binary_result(STMT *stmt,
                   SQLCHAR *result, SQLLEN result_bytes, SQLLEN *used_bytes,
                   MYSQL_FIELD *field, char *src, unsigned long src_bytes);
template <typename T>
SQLRETURN copy_binhex_result(STMT *stmt,
           T *rgbValue, SQLINTEGER cbValueMax,
           SQLLEN *pcbValue, char *src,
           ulong src_length);
SQLRETURN copy_bit_result(STMT *stmt,
                          SQLCHAR *result, SQLLEN result_bytes, SQLLEN *used_bytes,
                          MYSQL_FIELD *field, char *src, unsigned long src_bytes);
SQLRETURN wcopy_bit_result(STMT *stmt,
                          SQLWCHAR *result, SQLLEN result_bytes, SQLLEN *used_bytes,
                          MYSQL_FIELD *field, char *src, unsigned long src_bytes);
SQLRETURN copy_wchar_result(STMT *stmt,
                            SQLWCHAR *rgbValue, SQLINTEGER cbValueMax,
                            SQLLEN *pcbValue, MYSQL_FIELD *field, char *src,
                            long src_length);

SQLRETURN set_desc_error  (DESC *desc, char *state,
                          const char *message, uint errcode);
SQLRETURN handle_connection_error (STMT *stmt);
my_bool   is_connection_lost      (uint errcode);
void      set_mem_error           (MYSQL *mysql);
void      translate_error         (char *save_state, myodbc_errid errid, uint mysql_err);

SQLSMALLINT get_sql_data_type_from_str(const char *mysql_type_name);
SQLSMALLINT compute_sql_data_type(STMT *stmt, SQLSMALLINT sql_type,
  char octet_length, size_t col_size);
SQLSMALLINT get_sql_data_type           (STMT *stmt, MYSQL_FIELD *field, char *buff);
SQLULEN     get_column_size             (STMT *stmt, MYSQL_FIELD *field);
SQLULEN     get_column_size_from_str    (STMT *stmt, const char *size_str);
SQLULEN     fill_column_size_buff       (char *buff, STMT *stmt, MYSQL_FIELD *field);
SQLSMALLINT get_decimal_digits          (STMT *stmt, MYSQL_FIELD *field);
SQLLEN      get_transfer_octet_length   (STMT *stmt, MYSQL_FIELD *field);
SQLLEN      fill_transfer_oct_len_buff  (char *buff, STMT *stmt, MYSQL_FIELD *field);
SQLLEN      get_display_size            (STMT *stmt, MYSQL_FIELD *field);
SQLLEN      fill_display_size_buff      (char *buff, STMT *stmt, MYSQL_FIELD *field);
SQLSMALLINT get_dticode_from_concise_type       (SQLSMALLINT concise_type);
SQLSMALLINT get_concise_type_from_datetime_code (SQLSMALLINT dticode);
SQLSMALLINT get_concise_type_from_interval_code (SQLSMALLINT dticode);
SQLSMALLINT get_type_from_concise_type          (SQLSMALLINT concise_type);
SQLLEN      get_bookmark_value                  (SQLSMALLINT fCType, SQLPOINTER rgbValue);

#define is_char_sql_type(type) \
  ((type) == SQL_CHAR || (type) == SQL_VARCHAR || (type) == SQL_LONGVARCHAR)
#define is_wchar_sql_type(type) \
  ((type) == SQL_WCHAR || (type) == SQL_WVARCHAR || (type) == SQL_WLONGVARCHAR)
#define is_binary_sql_type(type) \
  ((type) == SQL_BINARY || (type) == SQL_VARBINARY || \
   (type) == SQL_LONGVARBINARY)

#define is_numeric_mysql_type(field) \
  ((field)->type <= MYSQL_TYPE_NULL || (field)->type == MYSQL_TYPE_LONGLONG || \
   (field)->type == MYSQL_TYPE_INT24 || \
   ((field)->type == MYSQL_TYPE_BIT && (field)->length == 1) || \
   (field)->type == MYSQL_TYPE_NEWDECIMAL)

SQLRETURN SQL_API my_SQLBindParameter(SQLHSTMT hstmt,SQLUSMALLINT ipar,
              SQLSMALLINT fParamType,
              SQLSMALLINT fCType, SQLSMALLINT fSqlType,
              SQLULEN cbColDef,
              SQLSMALLINT ibScale,
              SQLPOINTER  rgbValue,
              SQLLEN cbValueMax,
              SQLLEN *pcbValue);
SQLRETURN SQL_API my_SQLExtendedFetch(SQLHSTMT hstmt, SQLUSMALLINT fFetchType,
              SQLLEN irow, SQLULEN *pcrow,
              SQLUSMALLINT *rgfRowStatus, my_bool upd_status);
SQLRETURN SQL_API myodbc_single_fetch( SQLHSTMT hstmt, SQLUSMALLINT fFetchType,
              SQLLEN irow, SQLULEN *pcrow,
              SQLUSMALLINT *rgfRowStatus, my_bool upd_status);
SQLRETURN SQL_API sql_get_data(STMT *stmt, SQLSMALLINT fCType,
              uint column_number, SQLPOINTER rgbValue,
              SQLLEN cbValueMax, SQLLEN *pcbValue,
              char *value, ulong length, DESCREC *arrec);
SQLRETURN SQL_API sql_get_bookmark_data(STMT *stmt, SQLSMALLINT fCType,
              uint column_number, SQLPOINTER rgbValue,
              SQLLEN cbValueMax, SQLLEN *pcbValue,
              char *value, ulong length, DESCREC *arrec);

void fix_padded_length(STMT *stmt, SQLSMALLINT fCType,
              SQLLEN cbValueMax, SQLLEN *pcbValue, ulong data_len,
              DESCREC *irrec);

SQLRETURN SQL_API my_SQLSetPos(SQLHSTMT hstmt, SQLSETPOSIROW irow,
                               SQLUSMALLINT fOption, SQLUSMALLINT fLock);
int       unireg_to_c_datatype  (MYSQL_FIELD *field);
int       default_c_type        (int sql_data_type);
ulong     bind_length           (int sql_data_type,ulong length);
my_bool   str_to_date           (SQL_DATE_STRUCT *rgbValue, const char *str,
                                uint length, int zeroToMin);
int       str_to_ts             (SQL_TIMESTAMP_STRUCT *ts, const char *str, int len,
                                int zeroToMin, BOOL dont_use_set_locale);
my_bool str_to_time_st          (SQL_TIME_STRUCT *ts, const char *str);
ulong str_to_time_as_long       (const char *str,uint length);
void  init_getfunctions         (void);
void  myodbc_init               (void);
void  myodbc_ov_init            (SQLINTEGER odbc_version);
void  myodbc_sqlstate2_init     (void);
void  myodbc_sqlstate3_init     (void);
int   check_if_server_is_alive  (DBC *dbc);
MYSQL *new_mysql                (void);
bool   myodbc_append_quoted_name_std(std::string &str, const char *name);

SQLRETURN set_handle_error          (SQLSMALLINT HandleType, SQLHANDLE handle,
                                    myodbc_errid errid, const char *errtext, SQLINTEGER errcode);
SQLRETURN set_env_error (ENV * env,myodbc_errid errid, const char *errtext,
                        SQLINTEGER errcode);
SQLRETURN copy_str_data (SQLSMALLINT HandleType, SQLHANDLE Handle,
                        SQLCHAR *rgbValue, SQLSMALLINT cbValueMax,
                        SQLSMALLINT *pcbValue,char *src);
SQLRETURN SQL_API my_SQLAllocEnv      (SQLHENV * phenv);
SQLRETURN SQL_API my_SQLAllocConnect  (SQLHENV henv, SQLHDBC *phdbc);
SQLRETURN SQL_API my_SQLFreeConnect   (SQLHDBC hdbc);
SQLRETURN SQL_API my_SQLFreeEnv       (SQLHENV henv);

void myodbc_end();
my_bool set_dynamic_result        (STMT *stmt);
bool    set_current_cursor_data   (STMT *stmt,SQLUINTEGER irow);
my_bool is_minimum_version        (const char *server_version,const char *version);
int     myodbc_strcasecmp         (const char *s, const char *t);
int     myodbc_casecmp            (const char *s, const char *t, uint len);
int     reget_current_catalog     (DBC *dbc);

ulong   myodbc_escape_string      (STMT *stmt, char *to, ulong to_length,
                                  const char *from, ulong length, int escape_id);

DESCREC*  desc_get_rec            (DESC *desc, int recnum, my_bool expand);

DESC*     desc_alloc              (STMT *stmt, SQLSMALLINT alloc_type,
                                  desc_ref_type ref_type, desc_desc_type desc_type);
void      desc_free               (DESC *desc);
int       desc_find_dae_rec       (DESC *desc);
DESCREC * desc_find_outstream_rec (STMT *stmt, uint *recnum, uint *res_col_num);
SQLRETURN
stmt_SQLSetDescField      (STMT *stmt, DESC *desc, SQLSMALLINT recnum,
                          SQLSMALLINT fldid, SQLPOINTER val, SQLINTEGER buflen);
SQLRETURN
stmt_SQLGetDescField      (STMT *stmt, DESC *desc, SQLSMALLINT recnum,
                          SQLSMALLINT fldid, SQLPOINTER valptr,
                          SQLINTEGER buflen, SQLINTEGER *strlen);
SQLRETURN stmt_SQLCopyDesc(STMT *stmt, DESC *src, DESC *dest);

void sqlnum_from_str      (const char *numstr, SQL_NUMERIC_STRUCT *sqlnum,
                          int *overflow_ptr);
void sqlnum_to_str        (SQL_NUMERIC_STRUCT *sqlnum, SQLCHAR *numstr,
                          SQLCHAR **numbegin, SQLCHAR reqprec, SQLSCHAR reqscale,
                          int *truncptr);
void *ptr_offset_adjust   (void *ptr, SQLULEN *bind_offset,
                          SQLINTEGER bind_type, SQLINTEGER default_size,
                          SQLULEN row);

/* Functions used when debugging */
void query_print          (FILE *log_file,char *query);
FILE *init_query_log      (void);
void end_query_log        (FILE *query_log);

enum enum_field_types map_sql2mysql_type(SQLSMALLINT sql_type);

/* proc_* functions - used to parse prcedures headers in SQLProcedureColumns */
char *      proc_param_tokenize   (char *str, int *params_num);
char *      proc_get_param_type   (char *proc, int len, SQLSMALLINT *ptype);
char *    proc_get_param_name   (char *proc, int len, char *cname);
char *      proc_get_param_dbtype (char *proc, int len, char *ptype);
SQLUINTEGER proc_get_param_size   (SQLCHAR *ptype, int len, int sql_type_index,
                                  SQLSMALLINT *dec);
SQLLEN      proc_get_param_octet_len  (STMT *stmt, int sql_type_index,
                                      SQLULEN col_size, SQLSMALLINT decimal_digits,
                                      unsigned int flags, char * str_buff);
SQLLEN      proc_get_param_col_len    (STMT *stmt, int sql_type_index, SQLULEN col_size,
                                      SQLSMALLINT decimal_digits, unsigned int flags,
                                      char * str_buff);
int         proc_get_param_sql_type_index (const char*ptype, int len);
SQLTypeMap *proc_get_param_map_by_index   (int index);
char *      proc_param_next_token         (char *str, char *str_end);

void        set_row_count         (STMT * stmt, my_ulonglong rows);
const char *get_fractional_part   (const char * str, int len,
                                  BOOL dont_use_set_locale,
                                  SQLUINTEGER * fraction);
/* Convert MySQL timestamp to full ANSI timestamp format. */
char *          complete_timestamp  (const char * value, ulong length, char buff[21]);
BOOL            myodbc_isspace      (CHARSET_INFO* cs, const char * begin, const char *end);
BOOL            myodbc_isnum        (CHARSET_INFO* cs, const char * begin, const char *end);

#define NO_OUT_PARAMETERS         0
#define GOT_OUT_PARAMETERS        1
#define GOT_OUT_STREAM_PARAMETERS 2

int           got_out_parameters  (STMT *stmt);
const char    get_identifier_quote(STMT *stmt);
SQLULEN get_query_timeout(STMT *stmt);
SQLRETURN set_query_timeout(STMT *stmt, SQLULEN new_value);
int get_session_variable(STMT *stmt, const char *var, char *result,
                         size_t buf_len);

/* handle.c*/
BOOL          allocate_param_bind     (DYNAMIC_ARRAY **param_bind, uint elements);
int           adjust_param_bind_array (STMT *stmt);
/* Actions taken when connection is put to the pool. Used in connection freeing as well */
int           reset_connection        (DBC *dbc);
/* Actions taken when connection is taken from the pool */
int           wakeup_connection       (DBC *dbc);
#define WAKEUP_CONN_IF_NEEDED(dbc) if (dbc->need_to_wakeup && wakeup_connection(dbc)) return SQL_ERROR

long long binary2ll(char* src, uint64 srcLen);
unsigned long long binary2ull(char* src, uint64 srcLen);

void fill_ird_data_lengths (DESC *ird, ulong *lengths, uint fields);

/* Functions to work with prepared and regular statements  */
#define IS_PS_OUT_PARAMS(_stmt) ((_stmt)->dbc->mysql->server_status & SERVER_PS_OUT_PARAMS)
/* my_stmt.c */
BOOL              ssps_used           (STMT *stmt);
BOOL              returned_result     (STMT *stmt);
my_bool           free_current_result (STMT *stmt);
MYSQL_RES *       get_result_metadata (STMT *stmt, BOOL force_use);
int               bind_result         (STMT *stmt);
int               get_result          (STMT *stmt);
my_ulonglong      affected_rows       (STMT *stmt);
my_ulonglong      update_affected_rows(STMT *stmt);
my_ulonglong      num_rows            (STMT *stmt);
unsigned long*    fetch_lengths       (STMT *stmt);
MYSQL_ROW_OFFSET  row_seek            (STMT *stmt, MYSQL_ROW_OFFSET offset);
void              data_seek           (STMT *stmt, my_ulonglong offset);
MYSQL_ROW_OFFSET  row_tell            (STMT *stmt);
int               next_result         (STMT *stmt);
SQLRETURN         send_long_data      (STMT *stmt, unsigned int param_num, DESCREC * aprec,
                                      const char *chunk, unsigned long length);

#define IGNORE_THROW(A) try{ A; }catch(...){}

int           get_int     (STMT *stmt, ulong column_number, char *value,
                          ulong length);
unsigned int  get_uint    (STMT *stmt, ulong column_number, char *value,
                          ulong length);
long long     get_int64   (STMT *stmt, ulong column_number, char *value,
                          ulong length);
unsigned long long get_uint64(STMT * stmt, ulong column_number, char* value,
                          ulong length);
char *        get_string  (STMT *stmt, ulong column_number, char *value,
                          ulong *length, char * buffer);
double        get_double  (STMT *stmt, ulong column_number, char *value,
                          ulong length);
BOOL          is_null     (STMT *stmt, ulong column_number, char *value);
SQLRETURN     prepare     (STMT *stmt, char * query, SQLINTEGER query_length,
                           bool reset_sql_limit, bool force_prepare);


inline
void stmt_result_free(STMT * stmt)
{
  if (!stmt->result)
    return;

  if (stmt->fake_result)
  {
    x_free(stmt->result);
  }
  else
    mysql_free_result(stmt->result);

  stmt->result = NULL;
}


/* scroller-related functions */
unsigned int  calc_prefetch_number(unsigned int selected, SQLULEN app_fetchs,
                                   SQLULEN max_rows);
BOOL          scroller_exists     (STMT * stmt);
void          scroller_create     (STMT * stmt, const char *query, SQLULEN len);

unsigned long long  scroller_move (STMT * stmt);

SQLRETURN     scroller_prefetch   (STMT * stmt);
bool          scrollable          (STMT * stmt, const char * query,
                                  const char * query_end);

/* my_prepared_stmt.c */
void        ssps_init             (STMT *stmt);
BOOL        ssps_get_out_params   (STMT *stmt);
int         ssps_get_result       (STMT *stmt);
void        ssps_close            (STMT *stmt);
SQLRETURN   ssps_fetch_chunk      (STMT *stmt, char *dest, unsigned long dest_bytes,
                                  unsigned long *avail_bytes);
void        free_result_bind      (STMT *stmt);
BOOL        ssps_buffers_need_extending(STMT *stmt);

template <typename T>
T ssps_get_int64 (STMT *stmt, ulong column_number, char *value, ulong length);
double ssps_get_double            (STMT *stmt, ulong column_number, char *value,
                                  ulong length);
char *      ssps_get_string       (STMT *stmt, ulong column_number, char *value,
                                  ulong *length, char * buffer);
SQLRETURN   ssps_send_long_data   (STMT *stmt, unsigned int param_num, const char *chunk,
                                  unsigned long length);
MYSQL_BIND * get_param_bind       (STMT *stmt, unsigned int param_number, int reset);

bool is_varlen_type(enum enum_field_types type);

/* connect.c */
void free_connection_stmts(DBC *dbc);

#ifdef __WIN__
#define cmp_database(A,B) myodbc_strcasecmp((const char *)(A),(const char *)(B))
#else
#define cmp_database(A,B) strcmp((A),(B))
#endif

/*
  Check if an octet_length_ptr is a data-at-exec field.
  WARNING: This macro evaluates the argument multiple times.
*/
#define IS_DATA_AT_EXEC(X) ((X) && \
                            (*(X) == SQL_DATA_AT_EXEC || \
                             *(X) <= SQL_LEN_DATA_AT_EXEC_OFFSET))

/* Macro evaluates SQLRETURN expression and in case of error pushes it up in callstack */
#define PUSH_ERROR(sqlreturn_expr) do\
                                   {\
                                     SQLRETURN lrc= (sqlreturn_expr);\
                                     if (!SQL_SUCCEEDED(lrc))\
                                       return lrc;\
                                   } while(0)

/* Same as previous, but remembers to given var2rec SQL_SUCCESS_WITH_INFO */
#define PUSH_ERROR_EXT(var2rec,sqlreturn_expr) do\
                                               {\
                                                 SQLRETURN lrc= (sqlreturn_expr);\
                                                 if (!SQL_SUCCEEDED(lrc))\
                                                   return lrc;\
                                                 else if (lrc!=SQL_SUCCESS)\
                                                        var2rec= lrc;\
                                               } while(0)

/* Same as PUSH_ERROR but does not break execution in case of specified 'error' */
#define PUSH_ERROR_UNLESS(sqlreturn_expr, error) do\
                                                 {\
                                                   SQLRETURN lrc= (sqlreturn_expr);\
                                                   if (!SQL_SUCCEEDED(lrc)&&lrc!=error)\
                                                     return lrc;\
                                                 } while(0)

/* Same as PUSH_ERROR_UNLESS but remembers to given var2rec SQL_SUCCESS_WITH_INFO */
#define PUSH_ERROR_UNLESS_EXT(var2rec, sqlreturn_expr, error) do\
                                                 {\
                                                   SQLRETURN lrc= (sqlreturn_expr);\
                                                   if (!SQL_SUCCEEDED(lrc)&&lrc!=error)\
                                                     return lrc;\
                                                   else if (lrc!=SQL_SUCCESS)\
                                                          var2rec= lrc;\
                                                 } while(0)

#define GET_NAME_LEN(S, N, L) L = (L == SQL_NTS ? (N ? (SQLSMALLINT)strlen((char *)N) : 0) : L); \
  if (L > NAME_LEN) \
    return S->set_error("HY090", \
           "One or more parameters exceed the maximum allowed name length", 0);

#define CHECK_HANDLE(h) if (h == NULL) return SQL_INVALID_HANDLE

#define CHECK_DATA_POINTER(S, D, C) if (D == NULL && C != 0 && C != SQL_DEFAULT_PARAM && C != SQL_NULL_DATA) \
                                   return S->set_error("HY009", "Invalid use of NULL pointer", 0);

#define CHECK_STRLEN_OR_IND(S, D, C) if (D != NULL && C < 0 && C != SQL_NTS && C != SQL_NULL_DATA) \
                                   return S->set_error("HY090", "Invalid string or buffer length", 0);

#define CHECK_ENV_OUTPUT(e) if(e == NULL) return SQL_ERROR

#define CHECK_DBC_OUTPUT(e, d) if(d == NULL) return set_env_error((ENV *)e, MYERR_S1009, NULL, 0)

#define CHECK_STMT_OUTPUT(d, s) if(s == NULL) return ((DBC *)d)->set_error(MYERR_S1009, NULL, 0)

#define CHECK_DESC_OUTPUT(d, s) CHECK_STMT_OUTPUT(d, s)

#define CHECK_DATA_OUTPUT(s, d) if(d == NULL) return ((STMT *)s)->set_error(MYERR_S1000, "Invalid output buffer", 0)

#define IF_NOT_NULL(v, x) if (v != NULL) x

#endif /* __MYUTIL_H__ */
