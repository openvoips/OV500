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
  @file  cursor.c
  @brief Client-side cursor functions
*/

/***************************************************************************
 * The following ODBC APIs are implemented in this file:		   *
 *									   *
 *   SQLSetCursorName	 (ISO 92)					   *
 *   SQLGetCursorName	 (ISO 92)					   *
 *   SQLCloseCursor	 (ISO 92)					   *
 *   SQLSetPos		 (ODBC)						   *
 *   SQLBulkOperations	 (ODBC)						   *
 *									   *
 ****************************************************************************/

#include "driver.h"
#include <locale.h>


/* Sets affected rows everewhere where SQLRowCOunt could look for */
void global_set_affected_rows(STMT * stmt, my_ulonglong rows)
{
  stmt->affected_rows= stmt->dbc->mysql->affected_rows= rows;

  /* Dirty hack. But not dirtier than the one above */
  if (ssps_used(stmt))
  {
    stmt->ssps->affected_rows= rows;
  }
}


/*
  @type    : myodbc3 internal
  @purpose : returns the table used by this query and
  ensures that all columns are from the same table
*/
static const char *find_used_table(STMT *stmt)
{
    MYSQL_FIELD  *field, *end;
    char *table_name = nullptr;
    MYSQL_RES *result= stmt->result;

    if ( stmt->table_name.size() )
        return stmt->table_name.c_str();

    for ( field= result->fields, end= field+ result->field_count;
        field < end ; ++field )
    {

      if ( field->org_table )
      {
        if ( !table_name )
            table_name= field->org_table;
        if ( strcmp(field->org_table, table_name) )
        {
            stmt->set_error(MYERR_S1000,
                      "Can't modify a row from a statement that uses more than one table",0);
            return NULL;
        }
      }
    }
    /*
      We have to copy the strings as we may have to re-issue the query
      while using cursors.
    */
    if (table_name)
    {
      stmt->table_name= table_name;
      return stmt->table_name.c_str();
    }

    return nullptr;
}


/**
  Check if a statement involves a positioned cursor using the WHERE CURRENT
  OF syntax.

  @param[in]   pStmt       Handle of the statement
  @param[out]  pStmtCursor Pointer to the statement referred to by the cursor

  @return      Pointer to the beginning of 'WHERE CURRENT OF'
*/
char *check_if_positioned_cursor_exists(STMT *pStmt, STMT **pStmtCursor)
{
  const char * cursorName = pStmt->query.get_cursor_name();

  if (cursorName != NULL)
  {

    DBC  *dbc= (DBC *)pStmt->dbc;
    const char *wherePos = pStmt->query.get_token((uint)pStmt->query.token_count() - 4);

    if (wherePos > GET_QUERY(&pStmt->query))
    {
      /* Decrementing if do not point to the beginning of the string to
         point to a character(?) before "where"*/
      --wherePos;
    }

    /*
      Scan the list of statements for this connection and see if we
      can find the cursor name this statement is referring to - it
      must have a result set to count.
    */

    for (STMT *stmt : dbc->stmt_list)
    {
      *pStmtCursor= stmt;

      /*
        Even if the cursor name matches, the statement must have a
        result set to count.
      */
      if ((*pStmtCursor)->result &&
          (*pStmtCursor)->cursor.name.size() &&
          !myodbc_strcasecmp((*pStmtCursor)->cursor.name.c_str(),
                             cursorName))
      {
        return (char *)wherePos;
      }
    }

    /* Did we run out of statements without finding a viable cursor? */
    {
      char buff[200];
      strxmov(buff,"Cursor '", cursorName,
              "' does not exist or does not have a result set.", NullS);
      pStmt->set_error("34000", buff, ER_INVALID_CURSOR_NAME);
    }

    return (char *)wherePos;
  }

  return NULL;
}


/**
  Check if a field exists in a result set.

  @param[in]  name    Name of the field
  @param[in]  result  Result set to check
*/
static my_bool have_field_in_result(const char *name, MYSQL_RES *result)
{
  MYSQL_FIELD  *field;
  unsigned int ncol;

  for (ncol= 0; ncol < result->field_count; ++ncol)
  {
    field= result->fields + ncol;
    if (myodbc_strcasecmp(name,
#if MYSQL_VERSION_ID >= 40100
                          field->org_name
#else
                          field->name
#endif
                          ) == 0)
      return TRUE;
  }

  return FALSE;
}


/**
  Check if a primary or unique key exists in the table referred to by
  the statement for which all of the component fields are in the result
  set. If such a key exists, the field names are stored in the cursor.

  @param[in]  stmt  Statement

  @return  Whether a usable unique keys exists
*/
static my_bool check_if_usable_unique_key_exists(STMT *stmt)
{
  char buff[NAME_LEN * 2 + 18], /* Possibly escaped name, plus text for query */
       *pos, *table;
  MYSQL_RES *res;
  MYSQL_ROW row;
  int seq_in_index= 0;

  if (stmt->cursor.pk_validated)
    return stmt->cursor.pk_count;

#if MYSQL_VERSION_ID >= 40100
  if (stmt->result->fields->org_table)
    table= stmt->result->fields->org_table;
  else
#endif
    table= stmt->result->fields->table;

  /* Use SHOW KEYS FROM table to check for keys. */
  pos= myodbc_stpmov(buff, "SHOW KEYS FROM `");
  pos+= mysql_real_escape_string(stmt->dbc->mysql, pos, table,
    (unsigned long)strlen(table));
  pos= myodbc_stpmov(pos, "`");

  MYLOG_QUERY(stmt, buff);

  assert(stmt);
  LOCK_DBC(stmt->dbc);
  if (exec_stmt_query(stmt, buff, strlen(buff), FALSE) ||
      !(res= mysql_store_result(stmt->dbc->mysql)))
  {
    stmt->set_error(MYERR_S1000);
    return FALSE;
  }

  while ((row= mysql_fetch_row(res)) &&
         stmt->cursor.pk_count < MY_MAX_PK_PARTS)
  {
    int seq= atoi(row[3]);

    /* If this is a new key, we're done! */
    if (seq <= seq_in_index)
      break;

    /* Unless it is non_unique, it does us no good. */
    if (row[1][0] == '1')
      continue;

    /* If this isn't the next part, this key is no good. */
    if (seq != seq_in_index + 1)
      continue;

    /* Check that we have the key field in our result set. */
    if (have_field_in_result(row[4], stmt->result))
    {
      /* We have a unique key field -- copy it, and increment our count. */
      myodbc_stpmov(stmt->cursor.pkcol[stmt->cursor.pk_count++].name, row[4]);
      seq_in_index= seq;
    }
    else
      /* Forget about any key we had in progress, we didn't have it all. */
      stmt->cursor.pk_count= seq_in_index= 0;
  }
  mysql_free_result(res);

  /* Remember that we've figured this out already. */
  stmt->cursor.pk_validated= 1;

  return stmt->cursor.pk_count > 0;
}


/*
  @type    : myodbc3 internal
  @purpose : positions the data cursor to appropriate row
*/

bool set_current_cursor_data(STMT *stmt, SQLUINTEGER irow)
{
  long       nrow, row_pos;

  /*
    If irow exists, then position the current row to point
    to the rowsetsize+irow, this is needed for positioned
    calls
  */
  row_pos= irow ? (long) (stmt->current_row+irow-1) : stmt->current_row;

  if ( stmt->cursor_row != row_pos )
  {
    if (ssps_used(stmt))
    {
      data_seek(stmt, row_pos);
      MYSQL_ROW r = nullptr;
      IGNORE_THROW(r = stmt->fetch_row());
      if (!r)
        return false;
    }
    else
    {
      MYSQL_ROWS *dcursor;
      MYSQL_RES  *result= stmt->result;

      dcursor= result->data->data;

      if (dcursor)
      {
        for ( nrow= 0; nrow < row_pos; ++nrow )
        {
          dcursor= dcursor->next;
        }
      } else {
        return false;
      }

      result->data_cursor= dcursor;
    }

    stmt->cursor_row= row_pos;
  }
  return true;
}


/*
  @type    : myodbc3 internal
  @purpose : sets the dynamic cursor, when the cursor is not set
  explicitly by the application
*/

static void set_dynamic_cursor_name(STMT *stmt)
{
  stmt->cursor.name = "SQL_CUR" + std::to_string(stmt->dbc->cursor_count++);
}


/*
  @type    : myodbc3 internal
  @purpose : updates the stmt status information
*/

static SQLRETURN update_status(STMT *stmt, SQLUSMALLINT status)
{
    if ( stmt->affected_rows == 0 )
        return stmt->set_error(MYERR_01S03,NULL,0);

    else if ( stmt->affected_rows > 1 )
        return stmt->set_error(MYERR_01S04,NULL,0);

    /*
      This only comes from SQLExecute(), not SQLSetPos() or
      SQLBulkOperations(), so we don't have to worry about the row status
      set by SQLExtendedFetch().
    */
    else if ( stmt->ird->array_status_ptr )
    {
        SQLUSMALLINT *ptr= stmt->ird->array_status_ptr+stmt->current_row;
        SQLUSMALLINT *end= ptr+stmt->affected_rows;

        for ( ; ptr != end ; ++ptr )
            *ptr= status;
    }
    return SQL_SUCCESS;
}


/*
  @type    : myodbc3 internal
  @purpose : updates the SQLSetPos status information
*/

static SQLRETURN update_setpos_status(STMT *stmt, SQLINTEGER irow,
                                      my_ulonglong rows, SQLUSMALLINT status)
{
  global_set_affected_rows(stmt, rows);

  if (irow && rows > 1)
  {
    return stmt->set_error(MYERR_01S04,NULL,0);
  }

  /*
    If all rows successful, then only update status..else
    don't update...just for the sake of performance..
  */
  if (stmt->ird->array_status_ptr)
  {
    SQLUSMALLINT *ptr= stmt->ird->array_status_ptr;
    SQLUSMALLINT *end= ptr+rows;

    for ( ; ptr != end; ++ptr)
        *ptr= status;
  }

  if (stmt->stmt_options.rowStatusPtr_ex)
  {
    SQLUSMALLINT *ptr= stmt->stmt_options.rowStatusPtr_ex;
    SQLUSMALLINT *end= ptr+rows;

    for ( ; ptr != end; ++ptr)
        *ptr= status;
  }

  return SQL_SUCCESS;
}


/*
  @type    : myodbc3 internal
  @purpose : copy row buffers to statement
*/

static SQLRETURN copy_rowdata(STMT *stmt, DESCREC *aprec,
                              DESCREC *iprec)
{
    SQLRETURN rc;
    /* Negative length means either NULL or DEFAULT, so we need 7 chars. */
    size_t length = (*aprec->octet_length_ptr > 0 ?
                         *aprec->octet_length_ptr + 1 : 7);

    if (stmt->extend_buffer(length) == NULL)
        return stmt->set_error(MYERR_S1001,NULL,4001);

    rc= insert_param(stmt, NULL, stmt->apd, aprec, iprec, 0);
    if (!SQL_SUCCEEDED(rc))
        return rc;

    stmt->buf_remove_trail_zeroes();

    /* insert "," */
    if (stmt->add_to_buffer(",", 1) == NULL)
        return stmt->set_error(MYERR_S1001,NULL,4001);

    return(SQL_SUCCESS);
}


/*
  @type    : myodbc3 internal
  @purpose : copies field data to statement
*/

static bool insert_field_std(STMT *stmt, MYSQL_RES *result,
                             std::string &str,
                             SQLUSMALLINT nSrcCol)
{
  DESCREC aprec_(DESC_PARAM, DESC_APP),
          iprec_(DESC_PARAM, DESC_IMP);
  DESCREC *aprec= &aprec_, *iprec= &iprec_;

  MYSQL_FIELD *field= mysql_fetch_field_direct(result,nSrcCol);
  MYSQL_ROW   row_data;
  SQLLEN      length;
  char as_string[50], *dummy;

  if (ssps_used(stmt))
  {
    dummy= get_string(stmt, nSrcCol, NULL, (ulong*)&length, as_string);
    row_data= &dummy;
  }
  else
  {
    row_data= result->data_cursor->data + nSrcCol;
  }

  /* Copy row buffer data to statement */
  iprec->concise_type= get_sql_data_type(stmt, field, 0);
  aprec->concise_type= SQL_C_CHAR;

  if (row_data && *row_data)
  {
    aprec->data_ptr= (SQLPOINTER) *row_data;
    length= strlen(*row_data);

    aprec->octet_length_ptr= &length;
    aprec->indicator_ptr= &length;

    if (!SQL_SUCCEEDED(insert_param(stmt, NULL, stmt->apd,
                                    aprec, iprec, 0)))
      return 1;
    if (stmt->add_to_buffer(" AND ", 5) == NULL)
    {
      return (my_bool)stmt->set_error( MYERR_S1001, NULL, 4001);
    }

    str.append(stmt->buf(), stmt->buf_pos());
    stmt->buf_set_pos(0); // Buffer is used, can be reset
  }
  else
  {
    str.resize(str.length()-1);
    str.append(" IS NULL AND ");
  }
  return 0;
}



/*
  @type    : myodbc3 internal
  @purpose : checks for the existance of pk columns in the resultset,
  if it is, copy that data to query, else we can't find the right row
*/

static SQLRETURN insert_pk_fields_std(STMT *stmt, std::string &str)
{
    MYSQL_RES    *result= stmt->result;
    MYSQL_FIELD  *field;
    SQLUSMALLINT ncol;
    uint      index;
    MYCURSOR     *cursor= &stmt->cursor;
    SQLUINTEGER  pk_count= 0;

    /* Look for primary key columns in the current result set, */
    for (ncol= 0; ncol < result->field_count; ++ncol)
    {
      field= result->fields+ncol;
      for (index= 0; index < cursor->pk_count; ++index)
      {
        if (!myodbc_strcasecmp(cursor->pkcol[index].name, field->org_name))
        {
          /* PK data exists...*/
          myodbc_append_quoted_name_std(str, field->org_name);
          str.append(1, '=');
          if (insert_field_std(stmt, result, str, ncol))
            return SQL_ERROR;
          cursor->pkcol[index].bind_done= TRUE;
          ++pk_count;
          break;
        }
      }
    }

    /*
     If we didn't have data for all the components of the primary key,
     we can't build a correct WHERE clause.
    */
    if (pk_count != cursor->pk_count)
      return stmt->set_error("HY000",
                            "Not all components of primary key are available, "
                            "so row to modify cannot be identified", 0);

    return SQL_SUCCESS;
}


/*
  @type    : myodbc3 internal
  @purpose : generate a WHERE clause based on the fields in the result set
*/


static SQLRETURN append_all_fields_std(STMT *stmt, std::string &str)
{
  MYSQL_RES    *result= stmt->result;
  MYSQL_RES    *presultAllColumns;
  std::string  select;
  unsigned int  i,j;
  BOOL          found_field;

  assert(stmt);
  /*
    Get the base table name. If there was more than one table underlying
    the result set, this will fail, and we couldn't build a suitable
    list of fields.
  */
  if (!(find_used_table(stmt)))
    return SQL_ERROR;

  /*
    Get the list of all of the columns of the underlying table by using
    SELECT * FROM <table> LIMIT 0.
  */

  select = "SELECT * FROM `" + stmt->table_name + "` LIMIT 0";
  MYLOG_QUERY(stmt, select.c_str());
  LOCK_DBC(stmt->dbc);
  if (exec_stmt_query_std(stmt, select, false) ||
      !(presultAllColumns= mysql_store_result(stmt->dbc->mysql)))
  {
    stmt->set_error(MYERR_S1000);
    return SQL_ERROR;
  }

  /*
    If the number of fields in the underlying table is not the same as
    our result set, we bail out -- we need them all!
  */
  if (mysql_num_fields(presultAllColumns) != mysql_num_fields(result))
  {
    mysql_free_result(presultAllColumns);
    return SQL_ERROR;
  }

  /*
    Now we walk through the list of columns in the underlying table,
    appending them to the query along with the value from the row at the
    current cursor position.
  */
  for (i= 0; i < presultAllColumns->field_count; ++i)
  {
    MYSQL_FIELD *table_field= presultAllColumns->fields + i;

    /*
      We also can't handle floating-point fields because their comparison
      is inexact.
    */
    if (table_field->type == MYSQL_TYPE_FLOAT ||
        table_field->type == MYSQL_TYPE_DOUBLE ||
        table_field->type == MYSQL_TYPE_DECIMAL)
    {
      stmt->set_error(MYERR_S1000,
                "Invalid use of floating point comparision in positioned operations",0);
      mysql_free_result(presultAllColumns);
      return SQL_ERROR;
    }

    found_field= FALSE;
    for (j= 0; j < result->field_count; ++j)
    {
      MYSQL_FIELD *cursor_field= result->fields + j;
      if (cursor_field->org_name &&
          !strcmp(cursor_field->org_name, table_field->name))
      {
        myodbc_append_quoted_name_std(str, table_field->name);
        str.append("=");
        if (insert_field_std(stmt, result, str, j))
        {
          mysql_free_result(presultAllColumns);
          return SQL_ERROR;
        }
        found_field= TRUE;
        break;
      }
    }

    /*
      If we didn't find the field, we have failed.
    */
    if (!found_field)
    {
      mysql_free_result(presultAllColumns);
      return SQL_ERROR;
    }
  }

  mysql_free_result(presultAllColumns);
  return SQL_SUCCESS;
}


/*
  @type    : myodbc3 internal
  @purpose : build the where clause
*/

static SQLRETURN build_where_clause_std( STMT * pStmt,
                                     std::string &str,
                                     SQLUSMALLINT     irow )
{
    /* set our cursor to irow - we call assuming irow is valid */
    if (!set_current_cursor_data( pStmt, irow ))
    {
      // In this case we don't have data to do update/delete
      // because the resultset is empty.
      pStmt->set_error(MYERR_01S03);
      return SQL_NO_DATA;
    }
    /* simply append WHERE to our statement */
    str.append(" WHERE ");

    /*
      If a suitable key exists, then we'll use those columns, otherwise
      we'll try to use all of the columns.
    */
    if (check_if_usable_unique_key_exists(pStmt))
    {
      if (insert_pk_fields_std(pStmt, str) != SQL_SUCCESS)
        return SQL_ERROR;
    }
    else
    {
      if (append_all_fields_std(pStmt, str) != SQL_SUCCESS)
        return pStmt->set_error("HY000",
                              "Build WHERE -> insert_fields() failed.",
                              0);
    }

    /* Remove the trailing ' AND ' */
    size_t sz = str.size();
    if (sz > 5)
      str.erase(sz - 5);

    /* IF irow = 0 THEN delete all rows in the rowset ELSE specific (as in one) row */
    if ( irow == 0 )
    {
        str.append(" LIMIT ").append(std::to_string((size_t)pStmt->ard->array_size));
    }
    else
    {
        str.append(" LIMIT 1");
    }


    return SQL_SUCCESS;
}


/*
  @type    : myodbc3 internal
  @purpose : set clause building..
*/

static SQLRETURN build_set_clause_std(STMT *stmt, SQLULEN irow,
                                      std::string &query)
{
    DESCREC aprec_(DESC_PARAM, DESC_APP),
            iprec_(DESC_PARAM, DESC_IMP);
    DESCREC *aprec = &aprec_, *iprec = &iprec_;
    SQLLEN        length= 0;
    uint          ncol, ignore_count= 0;
    MYSQL_FIELD *field;
    MYSQL_RES   *result= stmt->result;
    DESCREC *arrec, *irrec;

    query.append(" SET ");

    /*
      To make sure, it points to correct row in the
      current rowset..
    */
    irow= irow ? irow-1: 0;
    for ( ncol= 0; ncol < stmt->result->field_count; ++ncol )
    {
        SQLLEN *pcbValue;
        SQLCHAR *to = (SQLCHAR*)stmt->buf();

        field= mysql_fetch_field_direct(result,ncol);
        arrec= desc_get_rec(stmt->ard, ncol, FALSE);
        irrec= desc_get_rec(stmt->ird, ncol, FALSE);

        if (!irrec)
        {
          return SQL_ERROR; // The error info is already set inside desc_get_rec()
        }
        assert(irrec->row.field);

        if (stmt->setpos_apd)
          aprec= desc_get_rec(stmt->setpos_apd.get(), ncol, FALSE);

        if (!arrec || !ARD_IS_BOUND(arrec) || !irrec->row.field)
        {
          ++ignore_count;
          continue;
        }

        if ( arrec->octet_length_ptr )
        {
            pcbValue= (SQLLEN*)ptr_offset_adjust(arrec->octet_length_ptr,
                                        stmt->ard->bind_offset_ptr,
                                        stmt->ard->bind_type,
                                        sizeof(SQLLEN), irow);
            /*
              If the pcbValue is SQL_COLUMN_IGNORE, then ignore the
              column in the SET clause
            */
            if ( *pcbValue == SQL_COLUMN_IGNORE )
            {
                ++ignore_count;
                continue;
            }
            length= *pcbValue;
        }
        else
        {
            /* set SQL_NTS only if its a string */
            switch (arrec->concise_type)
            {
                case SQL_CHAR:
                case SQL_VARCHAR:
                case SQL_LONGVARCHAR:
                    length= SQL_NTS;
                    break;
            }
        }

        myodbc_append_quoted_name_std(query, field->org_name);
        query.append("=");

        iprec->concise_type= get_sql_data_type(stmt, field, NULL);
        aprec->concise_type= arrec->concise_type;
	/* copy prec and scale - needed for SQL_NUMERIC values */
        iprec->precision= arrec->precision;
        iprec->scale= arrec->scale;
        if (stmt->dae_type && aprec->par.is_dae)
          aprec->data_ptr= aprec->par.val();
        else
          aprec->data_ptr= ptr_offset_adjust(arrec->data_ptr,
                                             stmt->ard->bind_offset_ptr,
                                             stmt->ard->bind_type,
                                             bind_length(arrec->concise_type,
                                                         (ulong)arrec->octet_length),
                                             irow);
        aprec->octet_length= arrec->octet_length;
        if (length == SQL_NTS)
            length= strlen((const char*)aprec->data_ptr);

        aprec->octet_length_ptr= &length;
        aprec->indicator_ptr= &length;

        if ( copy_rowdata(stmt,aprec,iprec) != SQL_SUCCESS )
            return(SQL_ERROR);

        query.append(stmt->buf(), stmt->buf_pos());
        stmt->buf_set_pos(0); // Buffer is used, can be reset
    }

    if (ignore_count == result->field_count)
      return ER_ALL_COLUMNS_IGNORED;

    query.erase(query.size()-1);
    return SQL_SUCCESS;
}


/*
  @type    : myodbc3 internal
  @purpose : deletes the positioned cursor row
*/


SQLRETURN my_pos_delete_std(STMT *stmt, STMT *stmtParam,
                        SQLUSMALLINT irow, std::string &str)
{
    SQLRETURN nReturn;

    /* Delete only the positioned row, by building where clause */
    nReturn = build_where_clause_std( stmt, str, irow );
    if ( !SQL_SUCCEEDED( nReturn ) )
        return nReturn;

    /* DELETE the row(s) */
    nReturn= exec_stmt_query_std(stmt, str, false);
    if ( nReturn == SQL_SUCCESS || nReturn == SQL_SUCCESS_WITH_INFO )
    {
        stmtParam->affected_rows= mysql_affected_rows(stmt->dbc->mysql);
        nReturn= update_status(stmtParam,SQL_ROW_DELETED);
    }
    return nReturn;
}


/*
  @type    : myodbc3 internal
  @purpose : updates the positioned cursor row
*/



SQLRETURN my_pos_update_std( STMT *             pStmtCursor,
                         STMT *             pStmt,
                         SQLUSMALLINT       nRow,
                         std::string        &query)
{
    SQLRETURN   rc;
    SQLHSTMT    hStmtTemp;
    STMT        * pStmtTemp;

    rc = build_where_clause_std( pStmtCursor, query, nRow );
    if ( !SQL_SUCCEEDED( rc ) )
        return rc;

    /*
      Prepare and check if parameters exists in set clause..
      this happens with WHERE CURRENT OF statements ..
    */
    if ( my_SQLAllocStmt( pStmt->dbc, &hStmtTemp ) != SQL_SUCCESS )
    {
        return pStmt->set_error("HY000", "my_SQLAllocStmt() failed.", 0 );
    }

    pStmtTemp = (STMT *)hStmtTemp;

    if (my_SQLPrepare(pStmtTemp, (SQLCHAR *)query.c_str(),
                      (SQLINTEGER)query.size(),
                      true, false) != SQL_SUCCESS)
    {
        my_SQLFreeStmt( pStmtTemp, SQL_DROP );
        return pStmt->set_error("HY000", "my_SQLPrepare() failed.", 0 );
    }
    if ( pStmtTemp->param_count )      /* SET clause has parameters */
    {
        if (!SQL_SUCCEEDED(rc= stmt_SQLCopyDesc(pStmt, pStmt->apd,
                                                pStmtTemp->apd)))
          return rc;
        if (!SQL_SUCCEEDED(rc= stmt_SQLCopyDesc(pStmt, pStmt->ipd,
                                                pStmtTemp->ipd)))
          return rc;
    }

    rc = my_SQLExecute( pStmtTemp );
    if ( SQL_SUCCEEDED( rc ) )
    {
        pStmt->affected_rows = mysql_affected_rows( pStmtTemp->dbc->mysql );
        rc = update_status( pStmt, SQL_ROW_UPDATED );
    }
    else if (rc == SQL_NEED_DATA)
    {
      /*
        Re-prepare the statement, which will leave us with a prepared
        statement that is a non-positioned update.
        To check: do we really need that?
      */
      if (my_SQLPrepare(pStmt, (SQLCHAR *)query.c_str(),
                        (SQLINTEGER)query.size(),
                        true, false) != SQL_SUCCESS)
        return SQL_ERROR;
      pStmt->dae_type= DAE_NORMAL;
    }

    my_SQLFreeStmt( pStmtTemp, SQL_DROP );

    return rc;
}


/*
  @type    : myodbc3 internal
  @purpose : fetches the specified bookmark rowset of data from the result set
  and returns data for all bound columns.
*/

static SQLRETURN fetch_bookmark(STMT *stmt)
{
  size_t rowset_pos, rowset_end;
  SQLRETURN    nReturn= SQL_SUCCESS;
  DESCREC *arrec;
  SQLPOINTER TargetValuePtr= NULL;
  long curr_bookmark_index= 0;
  size_t tmp_array_size= 0;

  IS_BOOKMARK_VARIABLE(stmt);
  arrec= desc_get_rec(stmt->ard, -1, FALSE);

  if (!ARD_IS_BOUND(arrec))
  {
      stmt->set_error("21S02",
                     "Degree of derived table does not match column list",
                     0);
      return SQL_ERROR;
  }

  rowset_pos= 1;
  rowset_end= stmt->ard->array_size;
  tmp_array_size= stmt->ard->array_size;
  stmt->ard->array_size= 1;
  do
  {
    data_seek(stmt, (my_ulonglong)rowset_pos);
    if (arrec->data_ptr)
    {
      TargetValuePtr= ptr_offset_adjust(arrec->data_ptr,
                                        stmt->ard->bind_offset_ptr,
                                        stmt->ard->bind_type,
                                        (SQLINTEGER)arrec->octet_length,
                                        rowset_pos - 1);
    }

    curr_bookmark_index= atol((const char*) TargetValuePtr);

    nReturn= myodbc_single_fetch(stmt, SQL_FETCH_ABSOLUTE,
                                curr_bookmark_index,
                                stmt->ird->rows_processed_ptr,
                                stmt->stmt_options.rowStatusPtr_ex ?
                                stmt->stmt_options.rowStatusPtr_ex :
                                stmt->ird->array_status_ptr, 0);
    if (nReturn != SQL_SUCCESS)
      break;
  } while ( ++rowset_pos <= rowset_end );

  stmt->ard->array_size= tmp_array_size;
  stmt->rows_found_in_set = (uint)rowset_pos - 1;

  return nReturn;
}


/*
  @type    : myodbc3 internal
  @purpose : deletes the positioned cursor row for bookmark in bound array
*/

static SQLRETURN setpos_delete_bookmark_std(STMT *stmt, std::string &query)
{
  size_t rowset_pos,rowset_end;
  my_ulonglong affected_rows= 0;
  SQLRETURN    nReturn= SQL_SUCCESS;
  size_t        query_length;
  const char   *table_name;
  DESCREC *arrec;
  SQLPOINTER TargetValuePtr= NULL;
  long curr_bookmark_index= 0;

  /*
     we want to work with base table name -
     we expect call to fail if more than one base table involved
  */
  if (!(table_name= find_used_table(stmt)))
  {
    return SQL_ERROR;
  }

  /* appened our table name to our DELETE statement */
  myodbc_append_quoted_name_std(query, table_name);
  query_length = query.size();

  IS_BOOKMARK_VARIABLE(stmt);
  arrec= desc_get_rec(stmt->ard, -1, FALSE);

  if (!ARD_IS_BOUND(arrec))
  {
    stmt->set_error("21S02",
                   "Degree of derived table does not match column list",
                   0);
    return SQL_ERROR;
  }

  rowset_pos= 0;
  rowset_end= stmt->ard->array_size;

  /* fetch all bookmark rows in the rowset to delete */
  while (rowset_pos < rowset_end)
  {
    if (arrec->data_ptr)
    {
      TargetValuePtr= ptr_offset_adjust(arrec->data_ptr,
                                        stmt->ard->bind_offset_ptr,
                                        stmt->ard->bind_type,
                                        (SQLINTEGER)arrec->octet_length,
                                        rowset_pos);
    }

    curr_bookmark_index= atol((const char*) TargetValuePtr);
    query.erase(query_length);

    /* append our WHERE clause to our DELETE statement */
    nReturn = build_where_clause_std(stmt, query, (SQLUSMALLINT)curr_bookmark_index);
    if (!SQL_SUCCEEDED( nReturn ))
    {
      return nReturn;
    }

    /* execute our DELETE statement */
    if (!(nReturn= exec_stmt_query_std(stmt, query, false)))
    {
      affected_rows+= stmt->dbc->mysql->affected_rows;
    }
    if (stmt->stmt_options.rowStatusPtr_ex)
    {
      stmt->stmt_options.rowStatusPtr_ex[curr_bookmark_index]= SQL_ROW_DELETED;
    }
    if (stmt->ird->array_status_ptr)
    {
      stmt->ird->array_status_ptr[curr_bookmark_index]= SQL_ROW_DELETED;
    }
    ++rowset_pos;
  }

  global_set_affected_rows(stmt, affected_rows);
  /* fix-up so fetching next rowset is correct */
  if (stmt->is_dynamic_cursor())
  {
    stmt->rows_found_in_set-= (uint) affected_rows;
  }

  return nReturn;
}


/*
  @type    : myodbc3 internal
  @purpose : deletes the positioned cursor row - will del all rows in rowset if irow = 0
*/

static SQLRETURN setpos_delete_std(STMT *stmt, SQLUSMALLINT irow,
                                   std::string &query)
{
  SQLUINTEGER  rowset_pos,rowset_end;
  my_ulonglong affected_rows= 0;
  SQLRETURN    nReturn= SQL_SUCCESS;
  size_t       query_length;
  const char   *table_name;

  /* we want to work with base table name - we expect call to fail if more than one base table involved */
  if (!(table_name= find_used_table(stmt)))
  {
    return SQL_ERROR;
  }

  /* appened our table name to our DELETE statement */
  myodbc_append_quoted_name_std(query, table_name);
  query_length= query.size();

  /* IF irow == 0 THEN delete all rows in the current rowset ELSE specific (as in one) row */
  if ( irow == 0 )
  {
    rowset_pos= 1;
    rowset_end= stmt->rows_found_in_set;
  }
  else
  {
    rowset_pos= rowset_end= irow;
  }

  /* process all desired rows in the rowset - we assume rowset_pos is valid */
  do
  {
    /* Each time we need a string without WHERE */
    query.erase(query_length);
    /* append our WHERE clause to our DELETE statement */
    nReturn = build_where_clause_std( stmt, query, (SQLUSMALLINT)rowset_pos );
    if (!SQL_SUCCEEDED( nReturn ))
    {
      return nReturn;
    }

    /* execute our DELETE statement */
    if (!(nReturn= exec_stmt_query_std(stmt, query, false)))
    {
      affected_rows+= stmt->dbc->mysql->affected_rows;
    }

  } while ( ++rowset_pos <= rowset_end );

  if (nReturn == SQL_SUCCESS)
  {
    nReturn= update_setpos_status(stmt, irow, affected_rows, SQL_ROW_DELETED);
  }

  /* fix-up so fetching next rowset is correct */
  if (stmt->is_dynamic_cursor())
  {
    stmt->rows_found_in_set-= (uint) affected_rows;
  }

  return nReturn;
}



/*
@type    : myodbc3 internal
@purpose : updates the positioned cursor row for bookmark in bound array
*/
static SQLRETURN setpos_update_bookmark_std(STMT *stmt, std::string &query)
{
  size_t rowset_pos,rowset_end;
  my_ulonglong affected= 0;
  SQLRETURN    nReturn= SQL_SUCCESS;
  size_t        query_length;
  const char   *table_name;
  DESCREC *arrec;
  SQLPOINTER TargetValuePtr= NULL;
  long curr_bookmark_index= 0;

  if ( !(table_name= find_used_table(stmt)))
  {
    return SQL_ERROR;
  }

  myodbc_append_quoted_name_std(query, table_name);
  query_length= query.size();

  IS_BOOKMARK_VARIABLE(stmt);
  arrec= desc_get_rec(stmt->ard, -1, FALSE);

  if (!ARD_IS_BOUND(arrec))
  {
    stmt->set_error("21S02",
                   "Degree of derived table does not match column list",
                   0);
    return SQL_ERROR;
  }

  rowset_pos= 0;
  rowset_end= stmt->ard->array_size;

  /* fetch all bookmark rows in the rowset to update */
  while (rowset_pos < rowset_end )
  {
    if (arrec->data_ptr)
    {
      TargetValuePtr= ptr_offset_adjust(arrec->data_ptr,
                                        stmt->ard->bind_offset_ptr,
                                        stmt->ard->bind_type,
                                        (SQLINTEGER)arrec->octet_length,
                                        rowset_pos);
    }

    curr_bookmark_index= atol((const char*) TargetValuePtr);

    query.erase(query_length);
    nReturn= build_set_clause_std(stmt, curr_bookmark_index, query);
    if (nReturn == ER_ALL_COLUMNS_IGNORED)
    {
      stmt->set_error("21S02",
                     "Degree of derived table does not match column list",
                     0);
      return SQL_ERROR;
    }
    else if (nReturn == SQL_ERROR)
    {
      return SQL_ERROR;
    }
    nReturn= build_where_clause_std(stmt, query,
                                   (SQLUSMALLINT)curr_bookmark_index);
    if (!SQL_SUCCEEDED(nReturn))
      return nReturn;

    if (!(nReturn= exec_stmt_query_std(stmt, query, false)))
    {
      affected+= mysql_affected_rows(stmt->dbc->mysql);
    }
    if (stmt->stmt_options.rowStatusPtr_ex)
    {
      stmt->stmt_options.rowStatusPtr_ex[curr_bookmark_index]= SQL_ROW_UPDATED;
    }
    if (stmt->ird->array_status_ptr)
    {
      stmt->ird->array_status_ptr[curr_bookmark_index]= SQL_ROW_UPDATED;
    }

    ++rowset_pos;
  }

  global_set_affected_rows(stmt, affected);
  return nReturn;
}


/*
@type    : myodbc3 internal
@purpose : updates the positioned cursor row.
*/

static SQLRETURN setpos_update_std(STMT *stmt, SQLUSMALLINT irow,
                                   std::string &query)
{
  SQLUINTEGER  rowset_pos,rowset_end;
  my_ulonglong affected= 0;
  SQLRETURN    nReturn= SQL_SUCCESS;
  size_t       query_length;
  const char   *table_name;

  if ( !(table_name= find_used_table(stmt)) )
      return SQL_ERROR;

  myodbc_append_quoted_name_std(query, table_name);
  query_length= query.size();

  if ( !irow )
  {
      /*
        If irow == 0, then update all rows in the current rowset
      */
      rowset_pos= 1;
      rowset_end= stmt->rows_found_in_set;
  }
  else
      rowset_pos= rowset_end= irow;

  do /* UPDATE, irow from current row set */
  {
    /* Each time we need a string without WHERE */
      query.erase(query_length);
      nReturn= build_set_clause_std(stmt, rowset_pos, query);
      if (nReturn == ER_ALL_COLUMNS_IGNORED)
      {
        /*
          If we're updating more than one row, having all columns ignored
          is fine. If it's just one row, that's an error.
        */
        if (!irow)
        {
          nReturn= SQL_SUCCESS;
          continue;
        }
        else
        {
          stmt->set_error("21S02",
                         "Degree of derived table does not match column list",
                         0);
          return SQL_ERROR;
        }
      }
      else if (nReturn == SQL_ERROR)
        return SQL_ERROR;

      nReturn= build_where_clause_std(stmt, query, (SQLUSMALLINT)rowset_pos);
      if (!SQL_SUCCEEDED(nReturn))
        return nReturn;

      if (!(nReturn= exec_stmt_query_std(stmt, query, false)))
      {
        affected+= mysql_affected_rows(stmt->dbc->mysql);
      }
      else if (!SQL_SUCCEEDED(nReturn))
      {
        stmt->error = stmt->dbc->error;
        return nReturn;
      }

  } while ( ++rowset_pos <= rowset_end );

  if (nReturn == SQL_SUCCESS)
      nReturn= update_setpos_status(stmt, irow, affected, SQL_ROW_UPDATED);

  return nReturn;
}


/*!
    \brief  Insert 1 or more rows.

            This function has been created to support SQLSetPos where
            SQL_ADD. For each row it will complete the given INSERT
            statement (ext_query) and call exec_stmt_query() to execute.

    \note   We have a limited capacity to shove data/sql across the wire. We try
            to handle this. see break_insert.

    \param  stmt            Viable statement.
    \param  irow            Position of the row in the rowset on which to perform the operation.
                            If RowNumber is 0, the operation applies to every row in the rowset.
    \param  ext_query       The INSERT statement up to and including the VALUES. So something
                            like; "INSERT .... VALUES"

    \return SQLRETURN

    \retval SQLERROR        Something went wrong.
    \retval SQL_SUCCESS     Success!
*/

static SQLRETURN batch_insert_std( STMT *stmt, SQLULEN irow, std::string &query )
{
    MYSQL_RES    *result= stmt->result;     /* result set we are working with */
    SQLULEN      insert_count= 1;           /* num rows to insert - will be real value when row is 0 (all)  */
    SQLULEN      count= 0;                  /* current row */
    SQLLEN       length;
    SQLUSMALLINT ncol;
    long i;
    size_t       query_length= 0;           /* our original query len so we can reset pos if break_insert   */
    my_bool      break_insert= FALSE;       /* true if we are to exceed max data size for transmission
                                               but this seems to be misused */
    DESCREC aprec_(DESC_PARAM, DESC_APP),
            iprec_(DESC_PARAM, DESC_IMP);

    DESCREC *aprec= &aprec_, *iprec= &iprec_;
    SQLRETURN res;

    stmt->stmt_options.bookmark_insert= FALSE;

    /* determine the number of rows to insert when irow = 0 */
    if ( !irow && stmt->ard->array_size > 1 ) /* batch wise */
    {
        insert_count= stmt->ard->array_size;
        query_length= query.size();
    }

    do
    {
        /* Have we called exec_stmt_query() as a result of exceeding data size for transmission? If
           so then we need to reset the pos. and start building a new statement. */
        if ( break_insert )
        {
            query.erase(query_length);
        }

        /* For each row, build the value list from its columns */
        while (count < insert_count)
        {
            /* Append values for each column. */
            query.append("(");
            for ( ncol= 0; ncol < result->field_count; ++ncol )
            {
                MYSQL_FIELD *field= mysql_fetch_field_direct(result, ncol);
                DESCREC     *arrec;
                SQLLEN       ind_or_len= 0;

                arrec= desc_get_rec(stmt->ard, ncol, FALSE);
                /* if there's a separate APD for this (dae), use it */
                if (stmt->setpos_apd)
                  aprec= desc_get_rec(stmt->setpos_apd.get(), ncol, FALSE);
                else
                  aprec->reset_to_defaults();

                if (arrec)
                {
                  if (aprec->par.is_dae)
                    ind_or_len= aprec->par.val_length();
                  else if (arrec->octet_length_ptr)
                    ind_or_len= *(SQLLEN *)
                                ptr_offset_adjust(arrec->octet_length_ptr,
                                                  stmt->ard->bind_offset_ptr,
                                                  stmt->ard->bind_type,
                                                  sizeof(SQLLEN), count);
                  else
                    ind_or_len= arrec->octet_length;

                  iprec->concise_type= get_sql_data_type(stmt, field, NULL);
                  aprec->concise_type= arrec->concise_type;
                  aprec->type= get_type_from_concise_type(aprec->concise_type);

                  /* If column buffer type is interval - making sql type interval too. Making it for 2 supported interval types so far */
                  if (aprec->type == SQL_INTERVAL && (aprec->concise_type == SQL_C_INTERVAL_HOUR_TO_SECOND || aprec->concise_type == SQL_C_INTERVAL_HOUR_TO_MINUTE)
                    && (iprec->concise_type == SQL_TYPE_TIME || iprec->concise_type == SQL_TIME))
                  {
                    iprec->type= aprec->type;
                    iprec->concise_type= aprec->concise_type;
                  }

                  /* copy prec and scale - needed for SQL_NUMERIC values */
                  iprec->precision= arrec->precision;
                  iprec->scale= arrec->scale;

                  if (stmt->dae_type && aprec->par.is_dae)
                    /* arrays or offsets are not supported for data-at-exec */
                    aprec->data_ptr= aprec->par.val();
                  else
                    aprec->data_ptr= ptr_offset_adjust(arrec->data_ptr,
                                                       stmt->ard->bind_offset_ptr,
                                                       stmt->ard->bind_type,
                                                       bind_length(arrec->concise_type,
                                                                   (ulong)arrec->octet_length),
                                                       count);
                }

                switch (ind_or_len) {
                case SQL_NTS:
                  if (aprec->data_ptr)
                    length= strlen((const char*)aprec->data_ptr);
                  break;

                /*
                  We pass through SQL_COLUMN_IGNORE and SQL_NULL_DATA,
                  because the insert_data() that is eventually called knows
                  how to deal with them.
                */
                case SQL_COLUMN_IGNORE:
                case SQL_NULL_DATA:
                default:
                  length= ind_or_len;
                }

                aprec->octet_length_ptr= &length;
                aprec->indicator_ptr= &length;

                if (copy_rowdata(stmt, aprec, iprec) != SQL_SUCCESS)
                  return SQL_ERROR;

            } /* END OF for (ncol= 0; ncol < result->field_count; ++ncol) */

            length = stmt->buf_pos();
            query.append(stmt->buf(), length - 1);
            stmt->buf_set_pos(0); // Buffer is used, can be reset
            query.append("),");
            ++count;

            /*
              We have a limited capacity to shove data across the wire, but
              we handle this by sending in multiple calls to exec_stmt_query()
            */
            if (query.size() + length >= (SQLULEN) stmt->buf_len())
            {
                break_insert= TRUE;
                break;
            }

        }  /* END OF while(count < insert_count) */

        query.erase(query.size() - 1);
        if ( exec_stmt_query_std(stmt, query, false) !=
             SQL_SUCCESS )
            return(SQL_ERROR);

    } while ( break_insert && count < insert_count );


    if (stmt->stmt_options.bookmarks == SQL_UB_VARIABLE)
    {
      ulong copy_bytes= 0;
      DESCREC *arrec;
      long max_row;

      arrec= desc_get_rec(stmt->ard, -1, FALSE);
      max_row= (long) num_rows(stmt);

      if (ARD_IS_BOUND(arrec))
      {
        SQLLEN *pcbValue= NULL;
        SQLPOINTER TargetValuePtr= NULL;

        for (i= max_row; i < (SQLINTEGER)insert_count; ++i)
        {
          pcbValue= NULL;
          TargetValuePtr= NULL;

          stmt->reset_getdata_position();

          if (arrec->data_ptr)
          {
            TargetValuePtr= ptr_offset_adjust(arrec->data_ptr,
                                              stmt->ard->bind_offset_ptr,
                                              stmt->ard->bind_type,
                                              (SQLINTEGER)arrec->octet_length,
                                              i);
          }

          if (arrec->octet_length_ptr)
          {
            pcbValue= (SQLLEN*)ptr_offset_adjust(arrec->octet_length_ptr,
                                          (SQLULEN*)stmt->ard->bind_offset_ptr,
                                          stmt->ard->bind_type,
                                          sizeof(SQLLEN), i);
          }
          std::string sval = std::to_string(i + 1);
          res = sql_get_bookmark_data(stmt, arrec->concise_type, (uint)0,
                                TargetValuePtr, arrec->octet_length,
                                pcbValue, (char*)sval.data(),
                                (ulong)sval.length(), arrec);

          if (!SQL_SUCCEEDED(res))
          {
            return SQL_ERROR;
          }
        }

        stmt->ard->array_size= insert_count;
        stmt->stmt_options.bookmark_insert= TRUE;
      }
    }

    global_set_affected_rows(stmt, insert_count);

    /* update row status pointer(s) */
    if (stmt->ird->array_status_ptr)
    {
      for (count= insert_count; count--; )
        stmt->ird->array_status_ptr[count]= SQL_ROW_ADDED;
    }
    if (stmt->stmt_options.rowStatusPtr_ex)
    {
      for (count= insert_count; count--; )
        stmt->stmt_options.rowStatusPtr_ex[count]= SQL_ROW_ADDED;
    }

    return SQL_SUCCESS;
}


/*
  Setup a data-at-execution for SQLSetPos() on the current
  statement.
*/
static SQLRETURN setpos_dae_check_and_init(STMT *stmt, SQLSETPOSIROW irow,
                                           SQLSMALLINT fLock, char dae_type)
{
  int dae_rec;
  SQLRETURN rc;

  /*
    If this statement hasn't already had the dae params set,
    check if there are any and begin the SQLParamData() sequence.
  */
  if (stmt->dae_type != DAE_SETPOS_DONE &&
      (dae_rec= desc_find_dae_rec(stmt->ard)) > -1)
  {
    if (!irow && stmt->ard->array_size > 1)
      return stmt->set_error("HYC00", "Multiple row insert "
                            "with data at execution not supported",
                            0);

    /* create APD, and copy ARD to it */
    stmt->setpos_apd.reset(new DESC(stmt, SQL_DESC_ALLOC_AUTO,
                               DESC_APP, DESC_PARAM));
    if (!stmt->setpos_apd)
      return stmt->set_error("S1001", "Not enough memory",
                            4001);
    if(rc= stmt_SQLCopyDesc(stmt, stmt->ard, stmt->setpos_apd.get()))
      return rc;

    stmt->current_param= dae_rec;
    stmt->dae_type= dae_type;
    stmt->setpos_row= irow;
    stmt->setpos_lock= fLock;
    return SQL_NEED_DATA;
  }

  return SQL_SUCCESS;
}


/*!
    \brief  Shadow function for SQLSetPos.

            Sets the cursor position in a rowset and allows an application
            to refresh data in the rowset or to update or delete data in
            the result set.

    \param  hstmt   see SQLSetPos
    \param  irow    see SQLSetPos
    \param  fOption see SQLSetPos
    \param  fLock   see SQLSetPos

    \return SQLRETURN   see SQLSetPos

    \sa     SQLSetPos
*/

static const char *alloc_error= "Driver Failed to set the internal dynamic result";


SQLRETURN SQL_API my_SQLSetPos(SQLHSTMT hstmt, SQLSETPOSIROW irow,
                               SQLUSMALLINT fOption, SQLUSMALLINT fLock)
{
  STMT *stmt = (STMT *) hstmt;
  assert(stmt);

  SQLRETURN ret = SQL_SUCCESS;
  MYSQL_RES *result = stmt->result;

  try
  {
    CLEAR_STMT_ERROR(stmt);

    if ( !result )
        return stmt->set_error(MYERR_S1010,NULL,0);

    /* With mysql_use_reslt we cannot do anything but move cursor
       forward. additional connection?
       besides http://msdn.microsoft.com/en-us/library/windows/desktop/ms713507%28v=vs.85%29.aspx
       "The cursor associated with the StatementHandle was defined as
        forward-only, so the cursor could not be positioned within the rowset.
        The Operation argument was SQL_UPDATE, SQL_DELETE, or SQL_REFRESH, and
        the row identified by the RowNumber argument had been deleted or had not
        been fetched." So error is more or less in accordance with specs */
    if ( if_forward_cache(stmt) )
    {
      if (fOption != SQL_POSITION)
      {
        /* HY109. Perhaps 24000 Invalid cursor state is a better fit*/
        return stmt->set_error( MYERR_S1109,NULL, 0);
      }
      /* We can't go back with forwrd only cursor */
      else if (irow < stmt->current_row)
      {
        /* Same HY109 Invalid cursor position*/
        return stmt->set_error( MYERR_S1109,NULL, 0);
      }
    }

    /* If irow > maximum rows in the resultset. for forwrd only row_count is 0
     */
    if ( fOption != SQL_ADD && irow > num_rows(stmt))
        return stmt->set_error(MYERR_S1107,NULL,0);

    /* Not a valid lock type ..*/
    if ( fLock != SQL_LOCK_NO_CHANGE )
        return stmt->set_error(MYERR_S1C00,NULL,0);

    switch ( fOption )
    {
        case SQL_POSITION:
            {
                if ( irow == 0 )
                    return stmt->set_error(MYERR_S1109,NULL,0);

                if ( irow > stmt->rows_found_in_set )
                    return stmt->set_error(MYERR_S1107,NULL,0);

                /* If Dynamic cursor, fetch the latest resultset */
                if ( stmt->is_dynamic_cursor() && set_dynamic_result(stmt) )
                {
                    return stmt->set_error(MYERR_S1000, alloc_error, 0);
                }

                LOCK_DBC(stmt->dbc);
                --irow;
                ret= SQL_SUCCESS;
                stmt->cursor_row= (long)(stmt->current_row+irow);
                data_seek(stmt, (my_ulonglong)stmt->cursor_row);
                stmt->current_values = stmt->fetch_row();
                stmt->reset_getdata_position();
                if ( stmt->fix_fields )
                    stmt->current_values= (*stmt->fix_fields)(stmt,stmt->current_values);
                /*
                 The call to mysql_fetch_row() moved stmt->result's internal
                 cursor, but we don't want that. We seek back to this row
                 so the MYSQL_RES is in the state we expect.
                */
                data_seek(stmt, (my_ulonglong)stmt->cursor_row);
                break;
            }

        case SQL_DELETE:
            {
                if ( irow > stmt->rows_found_in_set )
                    return stmt->set_error(MYERR_S1107,NULL,0);

                /* IF dynamic cursor THEN rerun query to refresh resultset */
                if ( stmt->is_dynamic_cursor() && set_dynamic_result(stmt) )
                    return stmt->set_error(MYERR_S1000, alloc_error, 0);

                /* start building our DELETE statement */
                std::string del_query("DELETE FROM ");
                del_query.reserve(1024);

                ret = setpos_delete_std( stmt, (SQLUSMALLINT)irow, del_query );
                break;
            }

        case SQL_UPDATE:
            {
                if ( irow > stmt->rows_found_in_set )
                    return stmt->set_error(MYERR_S1107,NULL,0);

                /* IF dynamic cursor THEN rerun query to refresh resultset */
                if (!stmt->dae_type && stmt->is_dynamic_cursor() &&
                    set_dynamic_result(stmt))
                  return stmt->set_error(MYERR_S1000, alloc_error, 0);

                if (ret = setpos_dae_check_and_init(stmt, irow, fLock,
                                                  DAE_SETPOS_UPDATE))
                  return ret;

                std::string upd_query("UPDATE ");
                upd_query.reserve(1024);

                ret = setpos_update_std(stmt, (SQLUSMALLINT)irow, upd_query);
                break;
            }

        case SQL_ADD:
            {
                const char  *   table_name;
                unsigned int    nCol        = 0;

                if (!stmt->dae_type && stmt->is_dynamic_cursor() &&
                    set_dynamic_result(stmt))
                  return stmt->set_error(MYERR_S1000, alloc_error, 0);
                result= stmt->result;

                if ( !(table_name= find_used_table(stmt)) )
                    return SQL_ERROR;

                if (ret = setpos_dae_check_and_init(stmt, irow, fLock,
                                                  DAE_SETPOS_INSERT))
                  return ret;

                std::string ins_query("INSERT INTO ");
                ins_query.reserve(1024);

                /* Append the table's DB name if exists */
                if (result->fields && result->fields[0].db_length)
                {
                  myodbc_append_quoted_name_std(ins_query, result->fields[0].db);
                  ins_query.append(".");
                }

                myodbc_append_quoted_name_std(ins_query, table_name);
                ins_query.append("(");

                /* build list of column names */
                for (nCol= 0; nCol < result->field_count; ++nCol)
                {
                    MYSQL_FIELD *field= mysql_fetch_field_direct(result, nCol);
                    myodbc_append_quoted_name_std(ins_query, field->org_name);
                    if (nCol + 1 < result->field_count)
                      ins_query.append(",");
                }

                ins_query.append(") VALUES ");

                /* process row(s) using our INSERT as base */
                ret= batch_insert_std(stmt, irow, ins_query);

                break;
            }

        case SQL_REFRESH:
            {
                /*
                  Bug ...SQL_REFRESH is not suppose to fetch any
                  new rows, instead it needs to refresh the positioned
                  buffers
                */
                ret= my_SQLExtendedFetch(hstmt, SQL_FETCH_ABSOLUTE, irow,
                                            stmt->ird->rows_processed_ptr,
                                            stmt->stmt_options.rowStatusPtr_ex ?
                                            stmt->stmt_options.rowStatusPtr_ex :
                                            stmt->ird->array_status_ptr, 0);
                break;
            }

        default:
            return stmt->set_error(MYERR_S1009,NULL,0);
    }
  }
  catch(MYERROR &e)
  {
    ret = e.retcode;
  }
  return ret;
}


SQLRETURN SQL_API
MySQLSetCursorName(SQLHSTMT hstmt, SQLCHAR *name, SQLSMALLINT len)
{
  STMT *stmt= (STMT *) hstmt;

  CLEAR_STMT_ERROR(stmt);

  if (!name)
    return stmt->set_error( MYERR_S1009, NULL, 0);

  if (len == SQL_NTS)
    len = (SQLSMALLINT)strlen((char *)name);

  if (len < 0)
    return stmt->set_error( MYERR_S1009, NULL, 0);

  /** @todo use charset-aware casecmp */
  if (len == 0 ||
      len > MYSQL_MAX_CURSOR_LEN ||
      myodbc_casecmp((char *)name, "SQLCUR", 6) == 0 ||
      myodbc_casecmp((char *)name, "SQL_CUR", 7) == 0)
    return stmt->set_error( MYERR_34000, NULL, 0);

  stmt->cursor.name= std::string((char*)name, len);
  return SQL_SUCCESS;
}


/**
  Get the current cursor name, generating one if necessary.

  @param[in]   hstmt  Statement handle

  @return  Pointer to cursor name (terminated with '\0')
*/
SQLCHAR *MySQLGetCursorName(HSTMT hstmt)
{
  STMT *stmt= (STMT *)hstmt;

  if (stmt->cursor.name.empty())
    set_dynamic_cursor_name(stmt);

  return (SQLCHAR *)stmt->cursor.name.c_str();
}


/*
  @type    : ODBC 1.0 API
  @purpose : sets the cursor position in a rowset and allows an application
  to refresh data in the rowset or to update or delete data in
  the result set
*/

SQLRETURN SQL_API SQLSetPos(SQLHSTMT hstmt, SQLSETPOSIROW irow,
                            SQLUSMALLINT fOption, SQLUSMALLINT fLock)
{
    SQLRETURN rc = SQL_SUCCESS;
    CHECK_HANDLE(hstmt);
    STMT *stmt = (STMT*)hstmt;

    // This operation number has to prevent using SSPS when SQLSetPos()
    // has to generate its own query different from the current statement.
    stmt->setpos_op = fOption;

    rc = my_SQLSetPos(hstmt, irow, fOption, fLock);
    stmt->setpos_op = 0;
    return rc;
}

/*
  @type    : ODBC 1.0 API
  @purpose : performs bulk insertions and bulk bookmark operations,
  including update, delete, and fetch by bookmark
*/

SQLRETURN SQL_API SQLBulkOperations(SQLHSTMT  Handle, SQLSMALLINT Operation)
{
  STMT *stmt= (STMT *) Handle;
  SQLRETURN sqlRet= SQL_SUCCESS;
  MYSQL_RES *result= stmt->result;
  SQLRETURN rc;
  SQLSETPOSIROW irow= 0;
  LOCK_STMT(stmt);

  CLEAR_STMT_ERROR(stmt);

  if ( !result )
      return stmt->set_error(MYERR_S1010,NULL,0);

  stmt->stmt_options.bookmark_insert= FALSE;

  switch (Operation)
  {
  case SQL_ADD:
    return my_SQLSetPos(Handle, 0, SQL_ADD, SQL_LOCK_NO_CHANGE);

  case SQL_UPDATE_BY_BOOKMARK:
    {
      /* If no rows provided for update return with SQL_SUCCESS. */
      if (stmt->rows_found_in_set == 0)
      {
        return SQL_SUCCESS;
      }

      /* IF dynamic cursor THEN rerun query to refresh resultset */
      if (!stmt->dae_type && stmt->is_dynamic_cursor() &&
          set_dynamic_result(stmt))
      {
        return stmt->set_error(MYERR_S1000, alloc_error, 0);
      }

      if (rc= setpos_dae_check_and_init(stmt, irow, SQL_LOCK_NO_CHANGE,
                                        DAE_SETPOS_UPDATE))
      {
        return rc;
      }


      std::string upd_query("UPDATE ");
      upd_query.reserve(1024);
      sqlRet= setpos_update_bookmark_std(stmt, upd_query);
      break;
    }
  case SQL_DELETE_BY_BOOKMARK:
    {

      /* IF dynamic cursor THEN rerun query to refresh resultset */
      if ( stmt->is_dynamic_cursor() && set_dynamic_result(stmt) )
          return stmt->set_error(MYERR_S1000, alloc_error, 0);

      /* start building our DELETE statement */
      std::string del_query("DELETE FROM ");
      del_query.reserve(1024);

      sqlRet = setpos_delete_bookmark_std(stmt, del_query);
      break;
    }
  case SQL_FETCH_BY_BOOKMARK:
    {
      sqlRet= fetch_bookmark(stmt);
      break;
    }
  default:
    return ((STMT*)(Handle))->set_error(MYERR_S1C00,NULL,0);
  }

  return sqlRet;
}


/*
  @type    : ODBC 3.0 API
  @purpose : closes a cursor that has been opened on a statement
  and discards any pending results
*/

SQLRETURN SQL_API SQLCloseCursor(SQLHSTMT Handle)
{
    CHECK_HANDLE(Handle);

    return  my_SQLFreeStmt(Handle, SQL_CLOSE);
}
