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
  @file   catalog_no_i_s.c
  @brief  Catalog functions not using I_S.
  @remark All functions suppose that parameters specifying other parameters lenthes can't SQL_NTS.
          caller should take care of that.
*/

#include "driver.h"
#include "catalog.h"


/*
  Helper function to deduce the MySQL database name from catalog,
  schema or current database taking into account NO_CATALOG and NO_SCHEMA
  options.
*/
std::string get_database_name(STMT *stmt,
                              SQLCHAR *catalog, SQLINTEGER catalog_len,
                              SQLCHAR *schema, SQLINTEGER schema_len,
                              bool try_reget)
{
  std::string db;
  if (!stmt->dbc->ds.opt_NO_CATALOG && catalog && catalog_len)
  {
    // Catalog parameter can be used
    db = (catalog_len != SQL_NTS ? std::string((char *)catalog, catalog_len) :
      std::string((char *)catalog));
  }
  else if(!stmt->dbc->ds.opt_NO_SCHEMA && schema && schema_len)
  {
    // Schema parameter can be used
    db = (schema_len != SQL_NTS ? std::string((char*)schema, schema_len) :
      std::string((char *)schema));
  }
  else if (!stmt->dbc->ds.opt_NO_CATALOG || !stmt->dbc->ds.opt_NO_SCHEMA)
  {
    if (!try_reget)
      return db;

    reget_current_catalog(stmt->dbc);

    db = !stmt->dbc->database.empty() ? stmt->dbc->database : "null";
  }
  return db;
}

/*
  @type    : internal
  @purpose : validate for give table type from the list
*/
static my_bool check_table_type(const SQLCHAR *TableType,
                                const char *req_type,
                                uint       len)
{
    char    req_type_quoted[NAME_LEN+2], req_type_quoted1[NAME_LEN+2];
    char    *type, *table_type= (char *)TableType;
    my_bool found= 0;

    if ( !TableType || !TableType[0] )
        return found;

    /*
      Check and return only 'user' tables from current DB and
      don't return any tables when its called with
      SYSTEM TABLES.

      Expected Types:
        "TABLE", "VIEW", "SYSTEM TABLE", "GLOBAL TEMPORARY",
        "LOCAL TEMPORARY", "ALIAS", "SYNONYM",
    */

    type= strstr(table_type,",");
    sprintf(req_type_quoted,"'%s'",req_type);
    sprintf(req_type_quoted1,"`%s`",req_type);
    while ( type )
    {
        while ( isspace(*(table_type)) ) ++table_type;
        if ( !myodbc_casecmp(table_type,req_type,len) ||
             !myodbc_casecmp(table_type,req_type_quoted,len+2) ||
             !myodbc_casecmp(table_type,req_type_quoted1,len+2) )
        {
            found= 1;
            break;
        }
        table_type= ++type;
        type= strstr(table_type,",");
    }
    if ( !found )
    {
        while ( isspace(*(table_type)) ) ++table_type;
        if ( !myodbc_casecmp(table_type,req_type,len) ||
             !myodbc_casecmp(table_type,req_type_quoted,len+2) ||
             !myodbc_casecmp(table_type,req_type_quoted1,len+2) )
            found= 1;
    }
    return found;
}


/*
  @type    : internal
  @purpose : returns columns from a particular table, NULL on error
*/
static MYSQL_RES *server_list_dbkeys(STMT *stmt,
                                     SQLCHAR *catalog,
                                     SQLSMALLINT catalog_len,
                                     SQLCHAR *table,
                                     SQLSMALLINT table_len)
{
    DBC   *dbc = stmt->dbc;
    MYSQL *mysql= dbc->mysql;
    char  tmpbuff[1024];
    std::string query;
    query.reserve(1024);
    size_t cnt = 0;

    query = "SHOW KEYS FROM `";

    if (catalog_len)
    {
      cnt = myodbc_escape_string(stmt, tmpbuff, (ulong)sizeof(tmpbuff),
                                (char *)catalog, catalog_len, 1);
      query.append(tmpbuff, cnt);
      query.append("`.`");
    }

    cnt = myodbc_escape_string(stmt, tmpbuff, (ulong)sizeof(tmpbuff),
                              (char *)table, table_len, 1);
    query.append(tmpbuff, cnt);
    query.append("`");

    MYLOG_DBC_QUERY(dbc, query.c_str());
    if (exec_stmt_query(stmt, query.c_str(), query.length(), FALSE))
        return NULL;
    return mysql_store_result(mysql);
}

/*
@type    : internal
@purpose : returns a table privileges result, NULL on error. Uses mysql pk_db tables
*/
static MYSQL_RES *table_privs_raw_data( STMT *      stmt,
                                        SQLCHAR *   catalog,
                                        SQLSMALLINT catalog_len,
                                        SQLCHAR *   table,
                                        SQLSMALLINT table_len)
{
  DBC *dbc= stmt->dbc;
  MYSQL *mysql= dbc->mysql;
  char   tmpbuff[1024];
  std::string query;
  size_t cnt = 0;

  query.reserve(1024);
  query = "SELECT Db,User,Table_name,Grantor,Table_priv "
          "FROM mysql.tables_priv WHERE Table_name LIKE '";

  cnt = mysql_real_escape_string(mysql, tmpbuff, (char *)table, table_len);
  query.append(tmpbuff, cnt);

  query.append("' AND Db = ");

  if (catalog_len)
  {
    query.append("'");
    cnt = mysql_real_escape_string(mysql, tmpbuff, (char *)catalog, catalog_len);
    query.append(tmpbuff, cnt);
    query.append("'");
  }
  else
    query.append("DATABASE()");

  query.append(" ORDER BY Db, Table_name, Table_priv, User");

  MYLOG_DBC_QUERY(dbc, query.c_str());
  if (exec_stmt_query(stmt, query.c_str(), query.length(), FALSE))
    return NULL;

  return mysql_store_result(mysql);
}


#define MY_MAX_TABPRIV_COUNT 21
#define MY_MAX_COLPRIV_COUNT 3

const char *SQLTABLES_priv_values[]=
{
    NULL,"",NULL,NULL,NULL,NULL,NULL
};

MYSQL_FIELD SQLTABLES_priv_fields[]=
{
  MYODBC_FIELD_NAME("TABLE_CAT", 0),
  MYODBC_FIELD_NAME("TABLE_SCHEM", 0),
  MYODBC_FIELD_NAME("TABLE_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("GRANTOR", 0),
  MYODBC_FIELD_NAME("GRANTEE", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("PRIVILEGE", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("IS_GRANTABLE", 0),
};

const uint SQLTABLES_PRIV_FIELDS = (uint)array_elements(SQLTABLES_priv_values);


/*
****************************************************************************
SQLColumnPrivileges
****************************************************************************
*/
/*
@type    : internal
@purpose : returns a column privileges result, NULL on error
*/
static MYSQL_RES *column_privs_raw_data(STMT *      stmt,
                                        SQLCHAR *   catalog,
                                        SQLSMALLINT catalog_len,
                                        SQLCHAR *   table,
                                        SQLSMALLINT table_len,
                                        SQLCHAR *   column,
                                        SQLSMALLINT column_len)
{
  DBC   *dbc = stmt->dbc;
  MYSQL *mysql = dbc->mysql;

  char tmpbuff[1024];
  std::string query;
  size_t cnt = 0;

  query.reserve(1024);

  query = "SELECT c.Db, c.User, c.Table_name, c.Column_name,"
          "t.Grantor, c.Column_priv, t.Table_priv "
          "FROM mysql.columns_priv AS c, mysql.tables_priv AS t "
          "WHERE c.Table_name = '";

  cnt = mysql_real_escape_string(mysql, tmpbuff, (char *)table, table_len);
  query.append(tmpbuff, cnt);

  query.append("' AND c.Db = ");
  if (catalog_len)
  {
    query.append("'");
    cnt = mysql_real_escape_string(mysql, tmpbuff, (char *)catalog, catalog_len);
    query.append(tmpbuff, cnt);
    query.append("'");
  }
  else
    query.append("DATABASE()");

  query.append("AND c.Column_name LIKE '");
  cnt = mysql_real_escape_string(mysql, tmpbuff, (char *)column, column_len);
  query.append(tmpbuff, cnt);

  query.append("' AND c.Table_name = t.Table_name "
               "ORDER BY c.Db, c.Table_name, c.Column_name, c.Column_priv");

  if (exec_stmt_query(stmt, query.c_str(), query.length(), FALSE))
    return NULL;

  return mysql_store_result(mysql);
}


char *SQLCOLUMNS_priv_values[]=
{
  NULL,"",NULL,NULL,NULL,NULL,NULL,NULL
};

MYSQL_FIELD SQLCOLUMNS_priv_fields[]=
{
  MYODBC_FIELD_NAME("TABLE_CAT", 0),
  MYODBC_FIELD_NAME("TABLE_SCHEM", 0),
  MYODBC_FIELD_NAME("TABLE_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("COLUMN_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("GRANTOR", 0),
  MYODBC_FIELD_NAME("GRANTEE", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("PRIVILEGE", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("IS_GRANTABLE", 0),
};


const uint SQLCOLUMNS_PRIV_FIELDS = (uint)array_elements(SQLCOLUMNS_priv_values);


/**
Get the CREATE TABLE statement for the given table.
Lengths may not be SQL_NTS.

@param[in] stmt           Handle to statement
@param[in] catalog        Catalog (database) of table, @c NULL for current
@param[in] catalog_length Length of catalog name
@param[in] table          Name of table
@param[in] table_length   Length of table name

@return Result of SHOW CREATE TABLE , or NULL if there is an error
or empty result (check mysql_errno(stmt->dbc->mysql) != 0)
*/
MYSQL_RES *server_show_create_table(STMT        *stmt,
                                    SQLCHAR     *catalog,
                                    SQLSMALLINT  catalog_length,
                                    SQLCHAR     *table,
                                    SQLSMALLINT  table_length)
{
  MYSQL *mysql= stmt->dbc->mysql;
  std::string query;
  size_t cnt = 0;

  query.reserve(1024);

  query = "SHOW CREATE TABLE ";
  if (catalog && *catalog)
  {
    query.append(" `").append((char *)catalog).append("`.");
  }

  /* Empty string won't match anything. */
  if (!*table)
    return NULL;

  if (table && *table)
  {
    query.append(" `").append((char *)table).append("`");
  }

  MYLOG_QUERY(stmt, query.c_str());


  if (mysql_real_query(mysql, query.c_str(),(unsigned long)query.length()))
  {
    return NULL;
  }

  return mysql_store_result(mysql);
}


/*
****************************************************************************
SQLForeignKeys
****************************************************************************
*/
MYSQL_FIELD SQLFORE_KEYS_fields[]=
{
  MYODBC_FIELD_NAME("PKTABLE_CAT", 0),
  MYODBC_FIELD_NAME("PKTABLE_SCHEM", 0),
  MYODBC_FIELD_NAME("PKTABLE_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("PKCOLUMN_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("FKTABLE_CAT", 0),
  MYODBC_FIELD_NAME("FKTABLE_SCHEM", 0),
  MYODBC_FIELD_NAME("FKTABLE_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("FKCOLUMN_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT("KEY_SEQ", NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT("UPDATE_RULE", 0),
  MYODBC_FIELD_SHORT("DELETE_RULE", 0),
  MYODBC_FIELD_NAME("FK_NAME", 0),
  MYODBC_FIELD_NAME("PK_NAME", 0),
  MYODBC_FIELD_SHORT("DEFERRABILITY", 0),
};

const uint SQLFORE_KEYS_FIELDS = (uint)array_elements(SQLFORE_KEYS_fields);

/* Multiple array of Struct to store and sort SQLForeignKeys field */
typedef struct SQL_FOREIGN_KEY_FIELD
{
  char PKTABLE_CAT[NAME_LEN + 1];
  char PKTABLE_SCHEM[NAME_LEN + 1];
  char PKTABLE_NAME[NAME_LEN + 1];
  char PKCOLUMN_NAME[NAME_LEN + 1];
  char FKTABLE_CAT[NAME_LEN + 1];
  char FKTABLE_SCHEM[NAME_LEN + 1];
  char FKTABLE_NAME[NAME_LEN + 1];
  char FKCOLUMN_NAME[NAME_LEN + 1];
  int  KEY_SEQ;
  int  UPDATE_RULE;
  int  DELETE_RULE;
  char FK_NAME[NAME_LEN + 1];
  char PK_NAME[NAME_LEN + 1];
  int  DEFERRABILITY;
} MY_FOREIGN_KEY_FIELD;

char *SQLFORE_KEYS_values[]= {
    NULL,"",NULL,NULL,
    NULL,"",NULL,NULL,
    0,0,0,NULL,NULL,0
};


/*
 * Get a record from the array if exists otherwise allocate a new
 * record and return.
 *
 * @param records MY_FOREIGN_KEY_FIELD record
 * @param recnum  0-based record number
 *
 * @return The requested record or NULL if it doesn't exist
 *         (and isn't created).
 */
MY_FOREIGN_KEY_FIELD *fk_get_rec(std::vector<MY_FOREIGN_KEY_FIELD> &records, unsigned int recnum)
{
  while(records.size() <= recnum)
    records.push_back(MY_FOREIGN_KEY_FIELD());

  return &records[recnum];
}


/*
****************************************************************************
SQLPrimaryKeys
****************************************************************************
*/

MYSQL_FIELD SQLPRIM_KEYS_fields[]=
{
  MYODBC_FIELD_NAME("TABLE_CAT", 0),
  MYODBC_FIELD_NAME("TABLE_SCHEM", 0),
  MYODBC_FIELD_NAME("TABLE_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("COLUMN_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT("KEY_SEQ", NOT_NULL_FLAG),
  MYODBC_FIELD_STRING("PK_NAME", 128, 0),
};

const uint SQLPRIM_KEYS_FIELDS = (uint)array_elements(SQLPRIM_KEYS_fields);

const long SQLPRIM_LENGTHS[]= {0, 0, 1, 5, 4, -7};

char *SQLPRIM_KEYS_values[]= {
    NULL,"",NULL,NULL,0,NULL
};

/*
  @purpose : returns the column names that make up the primary key for a table.
       The driver returns the information as a result set. This function
       does not support returning primary keys from multiple tables in
       a single call
*/

SQLRETURN
primary_keys_no_i_s(SQLHSTMT hstmt,
                    SQLCHAR *catalog, SQLSMALLINT catalog_len,
                    SQLCHAR *schema, SQLSMALLINT schema_len,
                    SQLCHAR *table, SQLSMALLINT table_len)
{
    STMT *stmt= (STMT *) hstmt;
    MYSQL_ROW row;
    uint      row_count;

    assert(stmt);
    LOCK_DBC(stmt->dbc);

    auto db = get_database_name(stmt, catalog, catalog_len,
                                schema, schema_len);
    if (!(stmt->result= server_list_dbkeys(stmt, (SQLCHAR*)db.c_str(),
                                           (SQLSMALLINT)db.length(),
                                           table, table_len)))
    {
      SQLRETURN rc= handle_connection_error(stmt);
      return rc;
    }

    // Free if result data was not in row storage.
    stmt->reset_result_array();

    // We will use the ROW_STORAGE here
    stmt->m_row_storage.set_size(stmt->result->row_count,
                                 SQLPRIM_KEYS_FIELDS);
    auto &data = stmt->m_row_storage;

    stmt->alloc_lengths(SQLPRIM_KEYS_FIELDS*(ulong) stmt->result->row_count);

    if (!stmt->lengths)
    {
      set_mem_error(stmt->dbc->mysql);
      return handle_connection_error(stmt);
    }

    row_count= 0;
    while ( (row= mysql_fetch_row(stmt->result)) )
    {
        if ( row[1][0] == '0' )     /* If unique index */
        {
            if ( row_count && !strcmp(row[3],"1") )
                break;    /* Already found unique key */

            fix_row_lengths(stmt, SQLPRIM_LENGTHS, row_count, SQLPRIM_KEYS_FIELDS);

            ++row_count;
            /* TABLE_CAT and TABLE_SCHEMA */
            CAT_SCHEMA_SET(data[0], data[1], db);
            /* TABLE_NAME */
            data[2] = row[0];

            /* COLUMN_NAME */
            data[3] = row[4];

            /* KEY_SEQ  */
            data[4] = row[3];

            /* PK_NAME */
            data[5] = "PRIMARY";

            data.next_row();
        }
    }

    stmt->result_array = (MYSQL_ROW)data.data();
    set_row_count(stmt, row_count);
    myodbc_link_fields(stmt,SQLPRIM_KEYS_fields,SQLPRIM_KEYS_FIELDS);

    return SQL_SUCCESS;
}


/*
****************************************************************************
SQLProcedure Columns
****************************************************************************
*/

char *SQLPROCEDURECOLUMNS_values[]= {
       "", "", NullS, NullS, "", "", "",
       "", "", "", "10", "",
       "MySQL column", "", "", NullS, "",
       NullS, ""
};

/* TODO make LONGLONG fields just LONG if SQLLEN is 4 bytes */
MYSQL_FIELD SQLPROCEDURECOLUMNS_fields[]=
{
  MYODBC_FIELD_NAME("PROCEDURE_CAT",     0),
  MYODBC_FIELD_NAME("PROCEDURE_SCHEM",   0),
  MYODBC_FIELD_NAME("PROCEDURE_NAME",    NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("COLUMN_NAME",       NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT ("COLUMN_TYPE",       NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT ("DATA_TYPE",         NOT_NULL_FLAG),
  MYODBC_FIELD_STRING("TYPE_NAME",         20,       NOT_NULL_FLAG),
  MYODBC_FIELD_LONGLONG("COLUMN_SIZE",       0),
  MYODBC_FIELD_LONGLONG("BUFFER_LENGTH",     0),
  MYODBC_FIELD_SHORT ("DECIMAL_DIGITS",    0),
  MYODBC_FIELD_SHORT ("NUM_PREC_RADIX",    0),
  MYODBC_FIELD_SHORT ("NULLABLE",          NOT_NULL_FLAG),
  MYODBC_FIELD_NAME("REMARKS",           0),
  MYODBC_FIELD_NAME("COLUMN_DEF",        0),
  MYODBC_FIELD_SHORT ("SQL_DATA_TYPE",     NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT ("SQL_DATETIME_SUB",  0),
  MYODBC_FIELD_LONGLONG("CHAR_OCTET_LENGTH", 0),
  MYODBC_FIELD_LONG  ("ORDINAL_POSITION",  NOT_NULL_FLAG),
  MYODBC_FIELD_STRING("IS_NULLABLE",       3,        0),
};

const uint SQLPROCEDURECOLUMNS_FIELDS =
    (uint)array_elements(SQLPROCEDURECOLUMNS_fields);


/*
  @type    : internal
  @purpose : returns procedure params as resultset
*/
static MYSQL_RES *server_list_proc_params(STMT *stmt,
                                          SQLCHAR *catalog,
                                          SQLSMALLINT catalog_len,
                                          SQLCHAR *proc_name,
                                          SQLSMALLINT proc_name_len,
                                          SQLCHAR *par_name,
                                          SQLSMALLINT par_name_len)
{
  DBC   *dbc = stmt->dbc;
  MYSQL *mysql= dbc->mysql;
  char   tmpbuf[1024];
  std::string qbuff;
  qbuff.reserve(2048);

  auto append_escaped_string = [&mysql, &tmpbuf](std::string &outstr,
                                        SQLCHAR* str,
                                        SQLSMALLINT len)
  {
    tmpbuf[0] = '\0';
    outstr.append("'");
    mysql_real_escape_string(mysql, tmpbuf, (char *)str, len);
    outstr.append(tmpbuf).append("'");
  };


  if((is_minimum_version(dbc->mysql->server_version, "5.7")))
  {
    qbuff = "select SPECIFIC_NAME, (IF(ISNULL(PARAMETER_NAME), "
            "concat('OUT RETURN_VALUE ', DTD_IDENTIFIER), "
            "concat(PARAMETER_MODE, ' `', PARAMETER_NAME, '` ', DTD_IDENTIFIER)) "
            ") as PARAMS_LIST, SPECIFIC_SCHEMA, ROUTINE_TYPE, ORDINAL_POSITION "
            "FROM information_schema.parameters "
            "WHERE SPECIFIC_SCHEMA LIKE ";

    if (catalog_len)
      append_escaped_string(qbuff, catalog, catalog_len);
    else
      qbuff.append("DATABASE()");

    if (proc_name_len)
    {
      qbuff.append(" AND SPECIFIC_NAME LIKE ");
      append_escaped_string(qbuff, proc_name, proc_name_len);
    }

    if (par_name_len)
    {
      qbuff.append(" AND (PARAMETER_NAME LIKE ");
      append_escaped_string(qbuff, par_name, par_name_len);
      qbuff.append(" OR ISNULL(PARAMETER_NAME))");
    }

    qbuff.append(" ORDER BY SPECIFIC_SCHEMA, SPECIFIC_NAME, ORDINAL_POSITION ASC");
  }
  else
  {
    qbuff = "SELECT name, CONCAT(IF(length(returns)>0, "
            "CONCAT('RETURN_VALUE ', returns, if(length(param_list)>0, ',', '')),''), param_list),"
            "db, type FROM mysql.proc WHERE Db=";

    if (catalog_len)
      append_escaped_string(qbuff, catalog, catalog_len);
    else
      qbuff.append("DATABASE()");

    if (proc_name_len)
    {
      qbuff.append(" AND name LIKE ");
      append_escaped_string(qbuff, proc_name, proc_name_len);
    }

    qbuff.append(" ORDER BY Db, name");
  }

  MYLOG_DBC_QUERY(dbc, qbuff.c_str());
  if (exec_stmt_query(stmt, qbuff.c_str(), qbuff.length(), FALSE))
    return NULL;

  return mysql_store_result(mysql);
}


/*
  @type    : ODBC 1.0 API
  @purpose : returns the list of input and output parameters, as well as
  the columns that make up the result set for the specified
  procedures. The driver returns the information as a result
  set on the specified statement
*/
SQLRETURN
procedure_columns_no_i_s(SQLHSTMT hstmt,
                         SQLCHAR *catalog, SQLSMALLINT catalog_len,
                         SQLCHAR *schema, SQLSMALLINT schema_len,
                         SQLCHAR *proc, SQLSMALLINT proc_len,
                         SQLCHAR *column, SQLSMALLINT column_len)
{
  STMT *stmt= (STMT *)hstmt;
  SQLRETURN nReturn= SQL_SUCCESS;
  MYSQL_ROW row;
  MYSQL_RES *proc_list_res;
  int params_num= 0, return_params_num= 0;
  unsigned int total_params_num = 0;
  std::string db;

  assert(stmt);
  /* get procedures list */
  LOCK_DBC(stmt->dbc);
  try
  {
    db = get_database_name(stmt, catalog, catalog_len,
                            schema, schema_len, false);

  if (!(proc_list_res= server_list_proc_params(stmt,
      (SQLCHAR*)db.c_str(), (SQLSMALLINT)db.length(), proc, proc_len,
      column, column_len)))
  {
    nReturn= stmt->set_error(MYERR_S1000);
    throw ODBCEXCEPTION();
  }

  // Free if result data was not in row storage.
  stmt->reset_result_array();

  // We will use the ROW_STORAGE here
  stmt->m_row_storage.set_size(proc_list_res->row_count,
                               SQLPROCEDURECOLUMNS_FIELDS);
  auto &data = stmt->m_row_storage;

  while ((row= mysql_fetch_row(proc_list_res)))
  {
    char *token;
    char *param_str;
    char *param_str_end;

    param_str= row[1];
    if(!param_str[0])
      continue;

    param_str_end= param_str + strlen(param_str);

    token = proc_param_tokenize(param_str, &params_num);

    if (params_num == 0)
    {
      throw ODBCEXCEPTION(EXCEPTION_TYPE::EMPTY_SET);
    }

    while (token != NULL)
    {
      SQLSMALLINT  ptype= 0;
      int sql_type_index;
      unsigned int flags= 0;
      SQLCHAR param_name[NAME_LEN]= "\0";
      SQLCHAR param_dbtype[1024]= "\0";
      SQLCHAR param_size_buf[21]= "\0";
      SQLCHAR param_buffer_len[21]= "\0";

      SQLTypeMap *type_map;
      SQLSMALLINT dec;
      SQLULEN param_size= 0;

      token= proc_get_param_type(token, (int)strlen(token), &ptype);
      token= proc_get_param_name(token, (int)strlen(token), (char*)param_name);
      token= proc_get_param_dbtype(token, (int)strlen(token), (char*)param_dbtype);

      /* param_dbtype is lowercased in the proc_get_param_dbtype */
      if (strstr((const char*)param_dbtype, "unsigned"))
        flags |= UNSIGNED_FLAG;

      sql_type_index= proc_get_param_sql_type_index((const char*)param_dbtype, (int)strlen((const char*)param_dbtype));
      type_map= proc_get_param_map_by_index(sql_type_index);

      param_size= proc_get_param_size(param_dbtype, (int)strlen((const char*)param_dbtype), sql_type_index, &dec);

      proc_get_param_octet_len(stmt, sql_type_index, param_size, dec, flags, (char*)param_buffer_len);

      /* PROCEDURE_CAT and PROCEDURE_SCHEMA */
      CAT_SCHEMA_SET(data[mypcPROCEDURE_CAT], data[mypcPROCEDURE_SCHEM], row[2]);

      data[mypcPROCEDURE_NAME] = row[0];
      data[mypcCOLUMN_NAME] = (const char*)param_name;

      /* The ordinal position can start with "0" only for return values */
      if (row[4][0] == '0')
      {
        ptype= SQL_RETURN_VALUE;
      }

      data[mypcCOLUMN_TYPE] = ptype;

      if (!myodbc_strcasecmp((const char*)type_map->type_name, "bit") && param_size > 1)
        data[mypcDATA_TYPE] = SQL_BINARY;
      else
        data[mypcDATA_TYPE] = type_map->sql_type;

      if (!myodbc_strcasecmp((const char*)type_map->type_name, "set") ||
         !myodbc_strcasecmp((const char*)type_map->type_name, "enum"))
      {
        data[mypcTYPE_NAME] = "char";
      }
      else
      {
        data[mypcTYPE_NAME]= (const char*)type_map->type_name;
      }

      proc_get_param_col_len(stmt, sql_type_index, param_size, dec, flags, (char*)param_size_buf);
      data[mypcCOLUMN_SIZE] = (const char*)param_size_buf;
      data[mypcBUFFER_LENGTH] = (const char*)param_buffer_len;

      if (dec != SQL_NO_TOTAL)
      {
        data[mypcDECIMAL_DIGITS] = dec;
        data[mypcNUM_PREC_RADIX] = "10";
      }
      else
      {
        data[mypcDECIMAL_DIGITS] = nullptr;
        data[mypcNUM_PREC_RADIX] = nullptr;
      }
      data[mypcNULLABLE] = "1";  /* NULLABLE */
      data[mypcREMARKS] = "";   /* REMARKS */
      data[mypcCOLUMN_DEF] = nullptr; /* COLUMN_DEF */

      if(type_map->sql_type == SQL_TYPE_DATE ||
         type_map->sql_type == SQL_TYPE_TIME ||
         type_map->sql_type == SQL_TYPE_TIMESTAMP)
      {
        data[mypcSQL_DATA_TYPE] = SQL_DATETIME;
        data[mypcSQL_DATETIME_SUB] = data[mypcDATA_TYPE];
      }
      else
      {
        data[mypcSQL_DATA_TYPE] = data[mypcDATA_TYPE];
        data[mypcSQL_DATETIME_SUB] = nullptr;
      }

      if (is_char_sql_type(type_map->sql_type) || is_wchar_sql_type(type_map->sql_type) ||
          is_binary_sql_type(type_map->sql_type))
      {
        data[mypcCHAR_OCTET_LENGTH] = data[mypcBUFFER_LENGTH];
      }
      else
      {
        data[mypcCHAR_OCTET_LENGTH] = nullptr;
      }

      data[mypcORDINAL_POSITION] = row[4];

      data[mypcIS_NULLABLE] = "YES"; /* IS_NULLABLE */
      ++total_params_num;
      data.next_row();
      token = proc_param_next_token(token, param_str_end);
    }
  }

  stmt->result_array = data.is_valid() ? (MYSQL_ROW)data.data() : nullptr;
  return_params_num = total_params_num;

  stmt->result= proc_list_res;

  set_row_count(stmt, return_params_num);

  myodbc_link_fields(stmt, SQLPROCEDURECOLUMNS_fields, SQLPROCEDURECOLUMNS_FIELDS);

  }
  catch(ODBCEXCEPTION &ex)
  {
    switch(ex.m_type)
    {
      case EXCEPTION_TYPE::EMPTY_SET:
        nReturn = create_empty_fake_resultset(
            (STMT*)hstmt, SQLPROCEDURECOLUMNS_values,
            sizeof(SQLPROCEDURECOLUMNS_values),
            SQLPROCEDURECOLUMNS_fields,
            SQLPROCEDURECOLUMNS_FIELDS);
        mysql_free_result(proc_list_res);
      case EXCEPTION_TYPE::GENERAL:
        break;
    }
  }

  return nReturn;
}



/*
****************************************************************************
SQLStatistics
****************************************************************************
*/

char SS_type[10];
char *SQLSTAT_values[]={NullS,NullS,"","",NullS,"",SS_type,"","","","",NullS,NullS};

MYSQL_FIELD SQLSTAT_fields[]=
{
  MYODBC_FIELD_NAME("TABLE_CAT", 0),
  MYODBC_FIELD_NAME("TABLE_SCHEM", 0),
  MYODBC_FIELD_NAME("TABLE_NAME", NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT("NON_UNIQUE", 0),
  MYODBC_FIELD_NAME("INDEX_QUALIFIER", 0),
  MYODBC_FIELD_NAME("INDEX_NAME", 0),
  MYODBC_FIELD_SHORT("TYPE", NOT_NULL_FLAG),
  MYODBC_FIELD_SHORT("ORDINAL_POSITION", 0),
  MYODBC_FIELD_NAME("COLUMN_NAME", 0),
  MYODBC_FIELD_STRING("ASC_OR_DESC", 1, 0),
  MYODBC_FIELD_LONG("CARDINALITY", 0),
  MYODBC_FIELD_LONG("PAGES", 0),
  MYODBC_FIELD_STRING("FILTER_CONDITION", 10, 0),
};

const uint SQLSTAT_FIELDS = (uint)array_elements(SQLSTAT_fields);

/*
  @purpose : retrieves a list of statistics about a single table and the
       indexes associated with the table. The driver returns the
       information as a result set.
*/

SQLRETURN
statistics_no_i_s(SQLHSTMT hstmt,
                  SQLCHAR *catalog, SQLSMALLINT catalog_len,
                  SQLCHAR *schema, SQLSMALLINT schema_len,
                  SQLCHAR *table, SQLSMALLINT table_len,
                  SQLUSMALLINT fUnique,
                  SQLUSMALLINT fAccuracy)
{
  STMT *stmt= (STMT *)hstmt;
  assert(stmt);

  MYSQL *mysql= stmt->dbc->mysql;
  DBC *dbc= stmt->dbc;
  char *db_val = nullptr;
  std::string db;
  MYSQL_ROW mysql_row;

  LOCK_DBC(stmt->dbc);

  if (table_len)
  {
    db = get_database_name(stmt, catalog, catalog_len, schema, schema_len, false);

    auto mysql_res = server_list_dbkeys(stmt, (SQLCHAR*)db.c_str(),
                                     (SQLSMALLINT)db.length(),
                                     table, table_len);
    if (!mysql_res)
    {
      SQLRETURN rc= handle_connection_error(stmt);
      return rc;
    }

    // Free if result data was not in row storage.
    stmt->reset_result_array();

    size_t rows = mysql_num_rows(mysql_res);
    stmt->m_row_storage.set_size(rows, SQLSTAT_FIELDS);

    auto &data = stmt->m_row_storage;
    data.first_row();
    size_t rnum = 0;

    while ((mysql_row = mysql_fetch_row(mysql_res))) {
      SQLSMALLINT non_unique = (mysql_row[1][0] == '1' ? 1 : 0);

      // Skip non-unique indexes if only unique are requested
      if (fUnique == SQL_INDEX_UNIQUE && non_unique)
        continue;

      CAT_SCHEMA_SET(data[0], data[1], db);
      /* TABLE_NAME */
      data[2] = mysql_row[0];
      /* NON_UNIQUE */
      data[3] = non_unique;
      /* INDEX_QUALIFIER - not supported */
      data[4] = nullptr;
      /* INDEX_NAME */
      data[5] = mysql_row[2];
      /* TYPE */
      SQLSMALLINT idx_type = SQL_INDEX_OTHER;
      data[6] = idx_type;
      /* ORDINAL_POSITION */
      SQLSMALLINT ordinal_pos =
          (SQLSMALLINT)std::strtol(mysql_row[3], nullptr, 10);
      data[7] = ordinal_pos;
      /* COLUMN_NAME */
      data[8] = mysql_row[4];
      /* ASC_OR_DESC */
      if (mysql_row[5])
        data[9] = mysql_row[5];
      else
        data[9] = nullptr;
      /* CARDINALITY */
      SQLINTEGER cardinality =
          (SQLINTEGER)std::strtol(mysql_row[6], nullptr, 10);
      data[10] = cardinality;
      /* PAGES - not supported */
      data[11] = nullptr;
      /* FILTER_CONDITION - not supported */
      data[12] = nullptr;

      if (rnum < rows)
        data.next_row();

      ++rnum;
    }

    if (mysql_res)
      mysql_free_result(mysql_res);

    if (rnum) {
      stmt->result_array = (MYSQL_ROW)data.data();
      create_fake_resultset(stmt, stmt->result_array, SQLSTAT_FIELDS, rnum,
                            SQLSTAT_fields, SQLSTAT_FIELDS, false);
      myodbc_link_fields(stmt, SQLSTAT_fields, SQLSTAT_FIELDS);
      return SQL_SUCCESS;
    }
  }
  return create_empty_fake_resultset(stmt, SQLSTAT_values,
                                     sizeof(SQLSTAT_values),
                                     SQLSTAT_fields, SQLSTAT_FIELDS);
}


/*
****************************************************************************
SQLTables
****************************************************************************
*/

uint SQLTABLES_qualifier_order[]= {0};
const char *SQLTABLES_values[] = {"","",NULL,"TABLE","MySQL table"};
const char *SQLTABLES_qualifier_values[] = {"",NULL,NULL,NULL,NULL};
const char *SQLTABLES_owner_values[] = {NULL,"",NULL,NULL,NULL};
const char *SQLTABLES_type_values[3][5] = {
    {NULL,NULL,NULL,"TABLE",NULL},
    {NULL,NULL,NULL,"SYSTEM TABLE",NULL},
    {NULL,NULL,NULL,"VIEW",NULL},
};

MYSQL_FIELD SQLTABLES_fields[]=
{
  MYODBC_FIELD_NAME("TABLE_CAT",   0),
  MYODBC_FIELD_NAME("TABLE_SCHEM", 0),
  MYODBC_FIELD_NAME("TABLE_NAME",  0),
  MYODBC_FIELD_NAME("TABLE_TYPE",  0),
/*
  Table remark length is 80 characters
*/
  MYODBC_FIELD_STRING("REMARKS",     80, 0),
};


const uint SQLTABLES_FIELDS = (uint)array_elements(SQLTABLES_values);

SQLRETURN
tables_no_i_s(SQLHSTMT hstmt,
              SQLCHAR *catalog, SQLSMALLINT catalog_len,
              SQLCHAR *schema, SQLSMALLINT schema_len,
              SQLCHAR *table, SQLSMALLINT table_len,
              SQLCHAR *type, SQLSMALLINT type_len)
{
    STMT *stmt= (STMT *)hstmt;
    my_bool user_tables, views;

    my_ulonglong row_count= 0;
    MYSQL_ROW db_row = nullptr;
    unsigned long count= 0;
    SQLRETURN rc = SQL_SUCCESS;

    try
    {
      ODBC_RESULTSET db_res;
      assert(stmt);
      LOCK_DBC(stmt->dbc);
      stmt->result = nullptr;

      bool all_dbs =
          ((catalog_len && !schema_len) ||
           (schema_len && !catalog_len)) &&
           !table_len && table && !type_len;

      if (// Empty catalog, empty schema, empty table, type is "%"
                   !catalog_len && catalog && !schema_len && schema &&
                   !table_len && table && type && !strncmp((char *)type, "%", 2))
      {
        /* Return set of TableType qualifiers */
        rc = create_fake_resultset(stmt, (MYSQL_ROW)SQLTABLES_type_values,
                                   sizeof(SQLTABLES_type_values[0]),
                                   array_elements(SQLTABLES_type_values),
                                   SQLTABLES_fields, SQLTABLES_FIELDS,
                                   true);
        return rc;
      }

      /*
        Empty (but non-NULL) schema and table returns catalog list.
        Empty (but non-NULL) catalog and table returns schema list.
      */
      if (all_dbs)
      {
        /* Determine the database */
        std::string db = get_database_name(stmt, catalog, catalog_len,
                                           schema, schema_len, false);
        db_res = db_status(stmt, db);
        if (!db_res)
        {
          return handle_connection_error(stmt);
        }

      }
      else if (!catalog_len && catalog && // Empty catalog, schema and table
               !schema_len && schema &&
               !table_len && table &&
               !type_len && type)
      {
        rc = create_fake_resultset(stmt, (MYSQL_ROW)SQLTABLES_owner_values,
                                   sizeof(SQLTABLES_owner_values),
                                   1, SQLTABLES_fields, SQLTABLES_FIELDS,
                                   true);
        return rc;
      }

      /* any other use of catalog="" or schema="" returns an empty result */
      if ((catalog || schema) && !catalog_len && !schema)
      {
        throw ODBCEXCEPTION(EXCEPTION_TYPE::EMPTY_SET);
      }

      user_tables = check_table_type(type, "TABLE", 5);
      views = check_table_type(type, "VIEW", 4);

      /* If no types specified, we want tables and views. */
      if (!user_tables && !views)
      {
        if (!type_len)
          user_tables = views = 1;
      }

      /* Return empty set if unknown TableType */
      if (type_len && !views && !user_tables)
      {
        throw ODBCEXCEPTION(EXCEPTION_TYPE::EMPTY_SET);
      }

      // Free if result data was not in row storage.
      stmt->reset_result_array();

      auto &data = stmt->m_row_storage;

      /*
        If database result set (catalog_res) was produced loop
        through all database to fetch table list inside database
      */
      while (true)
      {

        if (db_res)
          stmt->result = db_res.release();
        else
        {
          std::string db = db_row ? db_row[3] :
                                    get_database_name(stmt, catalog, catalog_len,
                                                      schema, schema_len, false);
          stmt->result = table_status(stmt, (SQLCHAR*)db.c_str(),
                                      (SQLSMALLINT)db.length(),
                                      table, table_len, TRUE,
                                      user_tables, views);
        }

        if (!stmt->result && mysql_errno(stmt->dbc->mysql))
        {
          /* unknown DB will return empty set from SQLTables */
          switch (mysql_errno(stmt->dbc->mysql))
          {
          case ER_BAD_DB_ERROR:
            throw ODBCEXCEPTION(EXCEPTION_TYPE::EMPTY_SET);
          default:
            return handle_connection_error(stmt);
          }
        }

        if (!stmt->result)
          throw ODBCEXCEPTION(EXCEPTION_TYPE::EMPTY_SET);

        /* assemble final result set */
        {
          MYSQL_ROW row;
          std::string db= "";
          row_count += stmt->result->row_count;

          if (!stmt->result->row_count)
          {
            if (stmt->result)
            {
              mysql_free_result(stmt->result);
              stmt->result = nullptr;
            }

            //if (is_info_schema)
            break;
          }

          // We will use the ROW_STORAGE here
          stmt->m_row_storage.set_size(row_count, SQLTABLES_FIELDS);

          int name_index = 0;
          while ((row= mysql_fetch_row(stmt->result)))
          {
            int type_index = 2;
            int comment_index = 1;
            int cat_index= 3;
            my_bool view;

            db = row[cat_index];

            view= (myodbc_casecmp(row[type_index], "VIEW", 4) == 0);

            if ((view && !views) || (!view && !user_tables))
            {
              --row_count;
              continue;
            }

            // Table Catalog & Schema
            CAT_SCHEMA_SET(data[0], data[1], db);

            // Table Name
            data[2] = row[name_index];

            // Table Type
            data[3] = row[type_index];

            // Comment
            data[4] = row[comment_index];
            data.next_row();
          }

        }
        /*
          If i_s then loop only once to fetch database name,
          as mysql_table_status_i_s handles SQL_ALL_CATALOGS (%)
          functionality and all databases with tables are returned
          in one result set and so no further loops are required.
        */
        //if (is_info_schema)
        break;
      }

      stmt->result_array = (MYSQL_ROW)data.data();

      if (!row_count)
      {
        throw ODBCEXCEPTION(EXCEPTION_TYPE::EMPTY_SET);
      }
    }
    catch(ODBCEXCEPTION &ex)
    {
      switch(ex.m_type)
      {
        case EXCEPTION_TYPE::EMPTY_SET:
          return create_empty_fake_resultset(stmt, (MYSQL_ROW)SQLTABLES_values,
                                             sizeof(SQLTABLES_values),
                                             SQLTABLES_fields,
                                             SQLTABLES_FIELDS);
      }
    }

  set_row_count(stmt, row_count);

  myodbc_link_fields(stmt, SQLTABLES_fields, SQLTABLES_FIELDS);
  return SQL_SUCCESS;
}
