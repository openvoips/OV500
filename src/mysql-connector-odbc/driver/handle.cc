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
  @file  handle.c
  @brief Allocation and freeing of handles.
*/

/***************************************************************************
 * The following ODBC APIs are implemented in this file:		   *
 *									   *
 *   SQLAllocHandle	 (ISO 92)					   *
 *   SQLFreeHandle	 (ISO 92)					   *
 *   SQLAllocEnv	 (ODBC, Deprecated)				   *
 *   SQLAllocConnect	 (ODBC, Deprecated)				   *
 *   SQLAllocStmt	 (ODBC, Deprecated)				   *
 *   SQLFreeEnv		 (ODBC, Deprecated)				   *
 *   SQLFreeConnect	 (ODBC, Deprecated)				   *
 *   SQLFreeStmt	 (ISO 92)					   *
 *									   *
****************************************************************************/

#include "driver.h"
#include <mutex>

thread_local long thread_count = 0;

std::mutex g_lock;

void ENV::add_dbc(DBC* dbc)
{
  LOCK_ENV(this);
  conn_list.emplace_back(dbc);
}

void ENV::remove_dbc(DBC* dbc)
{
  LOCK_ENV(this);
  conn_list.remove(dbc);
}

bool ENV::has_connections()
{
  return conn_list.size() > 0;
}

DBC::DBC(ENV *p_env)  : env(p_env), mysql(nullptr),
                        txn_isolation(DEFAULT_TXN_ISOLATION),
                        last_query_time((time_t) time((time_t*) 0))
{
  //mysql->net.vio = nullptr;
  myodbc_ov_init(env->odbc_ver);
  env->add_dbc(this);
}

void DBC::add_desc(DESC* desc)
{
  desc_list.emplace_back(desc);
}

void DBC::remove_desc(DESC* desc)
{
  desc_list.remove(desc);
}


void DBC::free_explicit_descriptors()
{

  /* free any remaining explicitly allocated descriptors */
  for (auto it = desc_list.begin(); it != desc_list.end();)
  {
    DESC *desc = *it;
    it = desc_list.erase(it);
    delete desc;
  }
}

void DBC::close()
{
  if (mysql)
    mysql_close(mysql);
  mysql = nullptr;
}

DBC::~DBC()
{
  if (env)
    env->remove_dbc(this);

  free_explicit_descriptors();
}


SQLRETURN DBC::set_error(char * state, const char * message, uint errcode)
{
  error.sqlstate = state ? state : "";
  error.message = std::string(MYODBC_ERROR_PREFIX) + message;
  error.native_error= errcode;
  return SQL_ERROR;
}


SQLRETURN DBC::set_error(char * state)
{
  return set_error(state, mysql_error(mysql), mysql_errno(mysql));
}


SQLRETURN DBC::set_error(myodbc_errid errid, const char* errtext,
  SQLINTEGER errcode)
{
  error = MYERROR(errid, errtext, errcode, MYODBC_ERROR_PREFIX);
  return error.retcode;
}


/*
  @type    : myodbc3 internal
  @purpose : to allocate the environment handle and to maintain
       its list
*/

SQLRETURN SQL_API my_SQLAllocEnv(SQLHENV *phenv)
{
  ENV *env;

  std::lock_guard<std::mutex> env_guard(g_lock);
  myodbc_init(); // This will call mysql_library_init()

#ifndef USE_IODBC
    env = new ENV(SQL_OV_ODBC3_80);
#else
    env = new ENV(SQL_OV_ODBC3);
#endif
  *phenv = (SQLHENV)env;
  return SQL_SUCCESS;
}


/*
  @type    : ODBC 1.0 API
  @purpose : to allocate the environment handle
*/

SQLRETURN SQL_API SQLAllocEnv(SQLHENV *phenv)
{
  CHECK_ENV_OUTPUT(phenv);

  return my_SQLAllocEnv(phenv);
}


/*
  @type    : myodbc3 internal
  @purpose : to free the environment handle
*/

SQLRETURN SQL_API my_SQLFreeEnv(SQLHENV henv)
{
    ENV *env= (ENV *) henv;
    delete env;
#ifndef _UNIX_
#else
    myodbc_end();
#endif /* _UNIX_ */
    return(SQL_SUCCESS);
}


/*
  @type    : ODBC 1.0 API
  @purpose : to free the environment handle
*/
SQLRETURN SQL_API SQLFreeEnv(SQLHENV henv)
{
    CHECK_HANDLE(henv);

    return my_SQLFreeEnv(henv);
}

#ifndef _UNIX_
SQLRETURN my_GetLastError(ENV *henv)
{
    SQLRETURN ret;
    LPVOID    msg;

    FormatMessage(FORMAT_MESSAGE_ALLOCATE_BUFFER | FORMAT_MESSAGE_FROM_SYSTEM,
                  NULL,
                  GetLastError(),
                  MAKELANGID(LANG_NEUTRAL, SUBLANG_DEFAULT),
                  (LPTSTR) &msg,
                  0,
                  NULL );

    ret = set_env_error(henv,MYERR_S1001,(const char*)msg,0);
    LocalFree(msg);

    return ret;
}
#endif

/*
  @type    : myodbc3 internal
  @purpose : to allocate the connection handle and to
       maintain the connection list
*/

SQLRETURN SQL_API my_SQLAllocConnect(SQLHENV henv, SQLHDBC *phdbc)
{
    DBC *dbc;
    ENV *penv= (ENV *) henv;

    if (!thread_count)
      mysql_thread_init();

    ++thread_count;

    if (mysql_get_client_version() < MIN_MYSQL_VERSION)
    {
        char buff[255];
        myodbc_snprintf(buff, sizeof(buff),
          "Wrong libmysqlclient library version: %ld. "
          "MyODBC needs at least version: %ld",
          mysql_get_client_version(), MIN_MYSQL_VERSION);

        return(set_env_error((ENV*)henv, MYERR_S1000, buff, 0));
    }

    if (!penv->odbc_ver)
    {
        return set_env_error((ENV*)henv, MYERR_S1010,
                             "Can't allocate connection "
                             "until ODBC version specified.", 0);
    }

    try
    {
      dbc = new DBC(penv);
      *phdbc = (SQLHDBC)dbc;
    }
    catch(...)
    {
      *phdbc = nullptr;
      return SQL_ERROR;
    }

    return(SQL_SUCCESS);
}


/*
  @type    : ODBC 1.0 API
  @purpose : to allocate the connection handle and to
       maintain the connection list
*/

SQLRETURN SQL_API SQLAllocConnect(SQLHENV henv, SQLHDBC *phdbc)
{
  /* Checking only henv because phdbc will be checked later */
  CHECK_HANDLE(henv);
  CHECK_DBC_OUTPUT(henv, phdbc);

  return my_SQLAllocConnect(henv, phdbc);
}



/* ODBC specs suggest(and that actually makes sense) to do jobs that require communication with server
   when connection is taken from pool, i.e. at "wakeup" time */
int reset_connection(DBC *dbc)
{
  dbc->free_connection_stmts();
  dbc->free_explicit_descriptors();

  return 0;
}


int wakeup_connection(DBC *dbc)
{
  DataSource &ds = dbc->ds;

#if MFA_ENABLED
  if(ds.opt_PWD1)
  {
    int fator = 2;
    mysql_options4(dbc->mysql, MYSQL_OPT_USER_PASSWORD,
                   &fator, (const char*)ds.opt_PWD1);
  }

  if (ds.opt_PWD2)
  {
    ds_get_utf8attr(ds->pwd2, &ds->pwd28);
    int fator = 2;
    mysql_options4(dbc->mysql, MYSQL_OPT_USER_PASSWORD,
                   &fator,
                   (const char *)ds.opt_PWD2);
  }

  if (ds.opt_PWD3)
  {
    ds_get_utf8attr(ds->pwd3, &ds->pwd38);
    int fator = 3;
    mysql_options4(dbc->mysql, MYSQL_OPT_USER_PASSWORD,
                   &fator,
                   (const char *)ds.opt_PWD3);
  }
#endif

  if (mysql_change_user(dbc->mysql, ds.opt_UID,
      ds.opt_PWD, ds.opt_DATABASE))
  {
    return 1;
  }

  dbc->need_to_wakeup= 0;
  return 0;
}


/*
  @type    : myodbc3 internal
  @purpose : to allocate the connection handle and to
       maintain the connection list
*/

SQLRETURN SQL_API my_SQLFreeConnect(SQLHDBC hdbc)
{
    DBC *dbc= (DBC *) hdbc;
    delete dbc;

    if (thread_count)
    {
      /* Last connection deallocated, supposedly the thread is finishing */
      if (--thread_count ==0)
        mysql_thread_end();
    }

    return SQL_SUCCESS;
}


/*
  @type    : ODBC 1.0 API
  @purpose : to allocate the connection handle and to
       maintain the connection list
*/
SQLRETURN SQL_API SQLFreeConnect(SQLHDBC hdbc)
{
  CHECK_HANDLE(hdbc);

  return my_SQLFreeConnect(hdbc);
}


/* Allocates memory for parameter binds in vector.
 */
void STMT::allocate_param_bind(uint elements)
{
  if (dbc->ds.opt_NO_SSPS)
    return;

  if (param_bind.capacity() < elements)
  {
    param_bind.reserve(elements);
    while(elements > param_bind.size())
      param_bind.push_back(MYSQL_BIND{});
  }
}


int adjust_param_bind_array(STMT *stmt)
{
  stmt->allocate_param_bind(stmt->param_count);
  /* Need to init newly allocated area with 0s */
  //  memset(stmt->param_bind->buffer + sizeof(MYSQL_BIND)*prev_max_elements, 0,
  //    sizeof(MYSQL_BIND) * (stmt->param_bind->max_element - prev_max_elements));

  return 0;
}


/*
  @type    : myodbc3 internal
  @purpose : allocates the statement handle
*/
SQLRETURN SQL_API my_SQLAllocStmt(SQLHDBC hdbc, SQLHSTMT *phstmt)
{
  std::unique_ptr<STMT> stmt;
  DBC   *dbc= (DBC*) hdbc;

  /* In fact it should be awaken when DM checks whether connection is alive before taking it from pool.
    Keeping the check here to stay on the safe side */
  WAKEUP_CONN_IF_NEEDED(dbc);

  try
  {
    stmt.reset(new STMT(dbc));
  }
  catch (...)
  {
    return dbc->set_error( "HY001", "Memory allocation error", MYERR_S1001);
  }

  *phstmt = (SQLHSTMT*)stmt.release();
  return SQL_SUCCESS;
}


/*
  @type    : ODBC 1.0 API
  @purpose : allocates the statement handle
*/

SQLRETURN SQL_API SQLAllocStmt(SQLHDBC hdbc,SQLHSTMT *phstmt)
{
  CHECK_HANDLE(hdbc);
  CHECK_STMT_OUTPUT(hdbc, phstmt);

    return my_SQLAllocStmt(hdbc,phstmt);
}


/*
  @type    : ODBC 1.0 API
  @purpose : stops processing associated with a specific statement,
       closes any open cursors associated with the statement,
       discards pending results, or, optionally, frees all
       resources associated with the statement handle
*/

SQLRETURN SQL_API SQLFreeStmt(SQLHSTMT hstmt, SQLUSMALLINT fOption)
{
  CHECK_HANDLE(hstmt);
  return my_SQLFreeStmt(hstmt, fOption);
}


/*
  @type    : myodbc3 internal
  @purpose : stops processing associated with a specific statement,
       closes any open cursors associated with the statement,
       discards pending results, or, optionally, frees all
       resources associated with the statement handle
*/

SQLRETURN SQL_API my_SQLFreeStmt(SQLHSTMT hstmt, SQLUSMALLINT f_option)
{
  return my_SQLFreeStmtExtended(hstmt, f_option,
    FREE_STMT_CLEAR_RESULT | FREE_STMT_DO_LOCK);
}

/*
  @type    : myodbc3 internal
  @purpose : stops processing associated with a specific statement,
       closes any open cursors associated with the statement,
       discards pending results, or, optionally, frees all
       resources associated with the statement handle
*/

SQLRETURN SQL_API my_SQLFreeStmtExtended(SQLHSTMT hstmt, SQLUSMALLINT f_option,
  SQLUSMALLINT f_extra)
{
    STMT *stmt= (STMT *) hstmt;
    uint i;
    LOCK_STMT_DEFER(stmt);
    if (f_extra & FREE_STMT_DO_LOCK)
    {
      DO_LOCK_STMT();
    }

    stmt->reset();

    if (f_option == SQL_UNBIND)
    {
      stmt->free_unbind();
      return SQL_SUCCESS;
    }

    stmt->free_reset_out_params();

    if (f_option == SQL_RESET_PARAMS)
    {
      stmt->free_reset_params();
      return SQL_SUCCESS;
    }

    stmt->free_fake_result((bool)(f_extra & FREE_STMT_CLEAR_RESULT));

    x_free(stmt->fields);   // TODO: Looks like STMT::fields is not used anywhere
    stmt->result= 0;
    stmt->fake_result= 0;
    stmt->fields= 0;
    stmt->free_lengths();
    stmt->current_values= 0;   /* For SQLGetData */
    stmt->fix_fields= 0;
    stmt->affected_rows= 0;
    stmt->current_row= stmt->rows_found_in_set= 0;
    stmt->cursor_row= -1;
    stmt->dae_type= 0;
    stmt->ird->reset();

    if (f_option == FREE_STMT_RESET_BUFFERS)
    {
      free_result_bind(stmt);
      stmt->array.reset();

      return SQL_SUCCESS;
    }

    stmt->state= ST_UNKNOWN;

    stmt->table_name.clear();
    stmt->dummy_state= ST_DUMMY_UNKNOWN;
    stmt->cursor.pk_validated= FALSE;
    stmt->reset_setpos_apd();

    for (i= stmt->cursor.pk_count; i--;)
    {
      stmt->cursor.pkcol[i].bind_done= 0;
    }
    stmt->cursor.pk_count= 0;

    if (f_option == SQL_CLOSE)
        return SQL_SUCCESS;

    if (f_extra & FREE_STMT_CLEAR_RESULT)
    {
      stmt->array.reset();

      ssps_close(stmt);
      if (stmt->ssps != NULL)
      {
        free_result_bind(stmt);
      }
    }

    /* At this point, only FREE_STMT_RESET and SQL_DROP left out */
    stmt->orig_query.reset(NULL, NULL, NULL);
    stmt->query.reset(NULL, NULL, NULL);

    stmt->param_count= 0;

    reset_ptr(stmt->apd->rows_processed_ptr);
    reset_ptr(stmt->ard->rows_processed_ptr);
    reset_ptr(stmt->ipd->array_status_ptr);
    reset_ptr(stmt->ird->array_status_ptr);
    reset_ptr(stmt->apd->array_status_ptr);
    reset_ptr(stmt->ard->array_status_ptr);
    reset_ptr(stmt->stmt_options.rowStatusPtr_ex);

    if (f_option == FREE_STMT_RESET)
    {
      return SQL_SUCCESS;
    }

    /* explicitly allocated descriptors are affected up until this point */
    stmt->apd->stmt_list_remove(stmt);
    stmt->ard->stmt_list_remove(stmt);

    // Unlock before destroying STMT to prevent unowned mutex.
    // See inside ~STMT()
    if (slock)
    {
      slock.unlock();
    }

    delete stmt;
    return SQL_SUCCESS;
}


/*
  Explicitly allocate a descriptor.
*/
SQLRETURN my_SQLAllocDesc(SQLHDBC hdbc, SQLHANDLE *pdesc)
{
  DBC *dbc= (DBC *) hdbc;
  std::unique_ptr<DESC> desc(new DESC(NULL, SQL_DESC_ALLOC_USER,
                                      DESC_APP, DESC_UNKNOWN));

  LOCK_DBC(dbc);

  if (!desc)
    return dbc->set_error( "HY001", "Memory allocation error", MYERR_S1001);

  desc->dbc= dbc;

  /* add to this connection's list of explicit descriptors */
  dbc->add_desc(desc.get());

  *pdesc= desc.release();
  return SQL_SUCCESS;
}


/*
  Free an explicitly allocated descriptor. This resets all statements
  that it was associated with to their implicitly allocated descriptors.
*/
SQLRETURN my_SQLFreeDesc(SQLHANDLE hdesc)
{
  DESC *desc= (DESC *) hdesc;
  DBC *dbc= desc->dbc;

  LOCK_DBC(dbc);

  if (!desc)
    return SQL_ERROR;
  if (desc->alloc_type != SQL_DESC_ALLOC_USER)
    return set_desc_error(desc, "HY017", "Invalid use of an automatically "
                          "allocated descriptor handle.", MYERR_S1017);

  /* remove from DBC */
  dbc->remove_desc(desc);

  for (auto s : desc->stmt_list)
  {
    if (IS_APD(desc))
      s->apd= s->imp_apd;
    else if (IS_ARD(desc))
      s->ard= s->imp_ard;
  }

  delete desc;
  return SQL_SUCCESS;
}


/*
  @type    : ODBC 3.0 API
  @purpose : allocates an environment, connection, statement, or
       descriptor handle
*/

SQLRETURN SQL_API SQLAllocHandle(SQLSMALLINT HandleType,
                                 SQLHANDLE   InputHandle,
                                 SQLHANDLE   *OutputHandlePtr)
{
    SQLRETURN error= SQL_ERROR;

    switch (HandleType)
    {
        case SQL_HANDLE_ENV:
            CHECK_ENV_OUTPUT(OutputHandlePtr);
            error= my_SQLAllocEnv(OutputHandlePtr);
            break;

        case SQL_HANDLE_DBC:
            CHECK_HANDLE(InputHandle);
            CHECK_DBC_OUTPUT(InputHandle, OutputHandlePtr);
            error= my_SQLAllocConnect(InputHandle,OutputHandlePtr);
            break;

        case SQL_HANDLE_STMT:
            CHECK_HANDLE(InputHandle);
            CHECK_STMT_OUTPUT(InputHandle, OutputHandlePtr);
            error= my_SQLAllocStmt(InputHandle,OutputHandlePtr);
            break;

        case SQL_HANDLE_DESC:
            CHECK_HANDLE(InputHandle);
            CHECK_DESC_OUTPUT(InputHandle, OutputHandlePtr);
            error= my_SQLAllocDesc(InputHandle, OutputHandlePtr);
            break;

        default:
            return ((DBC*)InputHandle)->set_error(MYERR_S1C00, NULL, 0);
    }

    return error;
}


/*
  @type    : ODBC 3.8
  @purpose : Mapped to SQLCancel if HandleType is
*/
SQLRETURN SQL_API SQLCancelHandle(SQLSMALLINT  HandleType,
                          SQLHANDLE    Handle)
{
  CHECK_HANDLE(Handle);

  switch (HandleType)
  {
  case SQL_HANDLE_DBC:
    {
      DBC *dbc= (DBC*)Handle;
      return dbc->set_error( "IM001", "Driver does not support this function", 0);
    }
  /* Normally DM should map such call to SQLCancel */
  case SQL_HANDLE_STMT:
    return SQLCancel((SQLHSTMT) Handle);
  }

  return SQL_SUCCESS;
}


/*
  @type    : ODBC 3.0 API
  @purpose : frees resources associated with a specific environment,
       connection, statement, or descriptor handle
*/

SQLRETURN SQL_API SQLFreeHandle(SQLSMALLINT HandleType,
                                SQLHANDLE   Handle)
{
    SQLRETURN error= SQL_ERROR;

    CHECK_HANDLE(Handle);

    switch (HandleType)
    {
        case SQL_HANDLE_ENV:
            error= my_SQLFreeEnv((ENV *)Handle);
            break;

        case SQL_HANDLE_DBC:
            error= my_SQLFreeConnect((DBC *)Handle);
            break;

        case SQL_HANDLE_STMT:
            error= my_SQLFreeStmt((STMT *)Handle, SQL_DROP);
            break;

        case SQL_HANDLE_DESC:
            error= my_SQLFreeDesc((DESC *) Handle);
            break;


        default:
            break;
    }

    return error;
}


