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

/**
  @file  desc.c
  @brief Functions for handling descriptors.
*/

/***************************************************************************
 * The following ODBC APIs are implemented in this file:                   *
 *   SQLSetDescField     (ISO 92)                                          *
 *   SQLGetDescField     (ISO 92)                                          *
 *   SQLCopyDesc         (ISO 92)                                          *
 ****************************************************************************/

#include "driver.h"
#include <algorithm>

/* Utility macros for defining descriptor fields */
#define HDR_FLD(field, perm, type) \
  static desc_field HDR_##field= \
    {(perm), (type), DESC_HDR, offsetof(DESC, field)}
    /* parens around field in offsetof() confuse GCC */


#define REC_FLD(field, perm, type) \
  static desc_field REC_##field= \
    {(perm), (type), DESC_REC, offsetof(DESCREC, field)}


/*
 * Allocate a new descriptor.
 * Should be used to allocate 'implicit' descriptors on the statement
 * or 'explicit' user-requested descriptors.
 */
DESC *desc_alloc(STMT *stmt, SQLSMALLINT alloc_type,
                 desc_ref_type ref_type, desc_desc_type desc_type)
{
  DESC *desc= new DESC(stmt, alloc_type, ref_type, desc_type);
  if (!desc)
    return NULL;

  return desc;
}


/*
  Free a descriptor.
*/
void desc_free(DESC *desc)
{
  delete desc;
}


void DESC::reset()
{
  records2.clear();
}

void DESC::free_paramdata()
{
  for (auto &r : records2)
    r.par.reset();
}


/*
 * Initialize APD
 */
void DESCREC::desc_rec_init_apd()
{
  /* ODBC defaults */
  concise_type= SQL_C_DEFAULT;
  data_ptr= NULL;
  indicator_ptr= NULL;
  octet_length_ptr= NULL;
  type= SQL_C_DEFAULT;

  /* internal */
  par.reset();
}


/*
 * Initialize IPD
 */
void DESCREC::desc_rec_init_ipd()
{
  /* ODBC defaults */
  fixed_prec_scale= SQL_TRUE;
  local_type_name= (SQLCHAR *)"";
  nullable= SQL_NULLABLE;
  parameter_type= SQL_PARAM_INPUT;
  type_name= (SQLCHAR *)"VARCHAR";
  is_unsigned= SQL_FALSE;

  /* driver defaults */
  name= (SQLCHAR *)"";
}


/*
 * Initialize ARD
 */
void DESCREC::desc_rec_init_ard()
{
  /* ODBC defaults */
  concise_type= SQL_C_DEFAULT;
  data_ptr= NULL;
  indicator_ptr= NULL;
  octet_length_ptr= NULL;
  type= SQL_C_DEFAULT;
}


/*
 * Initialize IRD
 */
void DESCREC::desc_rec_init_ird()
{
  /* ODBC defaults */
  /* driver defaults */
  auto_unique_value= SQL_FALSE;
  case_sensitive= SQL_TRUE;
  concise_type= SQL_VARCHAR;
  display_size= 100;/*?*/
  fixed_prec_scale= SQL_TRUE;
  length= 100;/*?*/
  nullable= SQL_NULLABLE_UNKNOWN;
  type= SQL_VARCHAR;
  type_name= (SQLCHAR *)"VARCHAR";
  unnamed= SQL_UNNAMED;
  is_unsigned= SQL_FALSE;
}

void DESCREC::reset_to_defaults()
{
  par.reset();
  row.reset();

  if (m_desc_type == DESC_PARAM && m_ref_type == DESC_APP)
    desc_rec_init_apd();
  else if (m_desc_type == DESC_PARAM && m_ref_type == DESC_IMP)
    desc_rec_init_ipd();
  else if (m_desc_type == DESC_ROW && m_ref_type == DESC_APP)
    desc_rec_init_ard();
  else if (m_desc_type == DESC_ROW && m_ref_type == DESC_IMP)
    desc_rec_init_ird();

}

void DESCREC::par_struct::add_param_data(const char *chunk,
                                         unsigned long length)
{
    tempbuf.add_to_buffer(chunk, length);

    // TODO: do the memory errors
    //  return stmt->set_error(MYERR_S1001, NULL, 4001);
}

/*
 * Get a record from the descriptor.
 *
 * @param desc Descriptor
 * @param recnum 0-based record number
 * @param expand Whether to expand the descriptor to include the given
 *               recnum.
 * @return The requested record of NULL if it doesn't exist
 *         (and isn't created).
 */
DESCREC *desc_get_rec(DESC *desc, int recnum, my_bool expand)
{
  DESCREC *rec= NULL;

  if (recnum == -1 && desc->stmt->stmt_options.bookmarks == SQL_UB_VARIABLE)
  {
    if (expand)
    {
      if (!desc->bookmark_count)
      {
        desc->bookmark2.emplace_back(desc->desc_type, desc->ref_type);
        rec = &desc->bookmark2.back();

        ++desc->bookmark_count;
      }
    }

    rec= &desc->bookmark2.back();
  }
  else if (recnum < 0)
  {
    desc->stmt->set_error("07009", "Invalid descriptor index", MYERR_07009);
    return NULL;
  }
  else
  {
    assert(recnum >= 0);
    /* expand if needed */
    if (expand)
    {
      for (size_t i = desc->rcount(); expand && i <= recnum; ++i)
      {
        desc->records2.emplace_back(desc->desc_type, desc->ref_type);
        rec = &desc->records2.back();
        rec->reset_to_defaults();
      }
    }
    if (recnum < desc->rcount())
      rec = &desc->records2[recnum];
  }

  if (expand)
    assert(rec);
  return rec;
}


/*
 * Check with the given descriptor contains any data-at-exec
 * records. Return the record number or -1 if none are found.
 */
int desc_find_dae_rec(DESC *desc)
{
  int i;
  DESCREC *rec;
  SQLLEN *octet_length_ptr;
  for (i= 0; i < desc->rcount(); ++i)
  {
    rec= desc_get_rec(desc, i, FALSE);
    assert(rec);
    octet_length_ptr= (SQLLEN*)ptr_offset_adjust(rec->octet_length_ptr,
                                        desc->bind_offset_ptr,
                                        desc->bind_type,
                                        sizeof(SQLLEN), /*row*/0);
    if (IS_DATA_AT_EXEC(octet_length_ptr))
      return i;
  }
  return -1;
}


/*
 * Check with the given descriptor contains any output streams
 * @param recnum[in,out] - pointer to 0-based record number to begin search from
                           and to store found record number
 * @param res_col_num[in,out] - pointer to 0-based column number in output parameters resultset
 * Returns the found record or NULL.
 */
DESCREC * desc_find_outstream_rec(STMT *stmt, uint *recnum, uint *res_col_num)
{
  int i, start= recnum != NULL ? *recnum + 1 : 0;
  DESCREC *rec;
  uint column= *res_col_num;

/* No streams in iODBC */
#ifndef USE_IODBC
  for (i= start; i < stmt->ipd->rcount(); ++i)
  {
    rec= desc_get_rec(stmt->ipd, i, FALSE);
    assert(rec);

    if (rec->parameter_type == SQL_PARAM_INPUT_OUTPUT_STREAM
     || rec->parameter_type == SQL_PARAM_OUTPUT_STREAM)
    {
      if (recnum != NULL)
      {
        *recnum= i;
      }
      *res_col_num= ++column;
      /* Valuable information is in apd */
      return desc_get_rec(stmt->apd, i, FALSE);
    }
    else if (rec->parameter_type == SQL_PARAM_INPUT_OUTPUT
          || rec->parameter_type == SQL_PARAM_OUTPUT)
    {
      ++column;
    }
  }
#endif

  return NULL;
}


/*
 * Apply the actual value to the descriptor field.
 *
 * @param dest Pointer to descriptor field to be set.
 * @param dest_type Type of descriptor field (same type constants as buflen).
 * @param src Value to be set.
 * @param buflen Length of value (as specified by SQLSetDescField).
 */
static void
apply_desc_val(void *dest, SQLSMALLINT dest_type, void *src, SQLINTEGER buflen)
{
  switch (buflen)
  {
  case SQL_IS_SMALLINT:
  case SQL_IS_INTEGER:
  case SQL_IS_LEN:
    if (dest_type == SQL_IS_SMALLINT)
      *(SQLSMALLINT *)dest= (SQLSMALLINT)((SQLLEN)src);
    else if (dest_type == SQL_IS_USMALLINT)
      *(SQLUSMALLINT *)dest = (SQLUSMALLINT)((SQLLEN)src);
    else if (dest_type == SQL_IS_INTEGER)
      *(SQLINTEGER *)dest = (SQLINTEGER)((SQLLEN)src);
    else if (dest_type == SQL_IS_UINTEGER)
      *(SQLUINTEGER *)dest = (SQLUINTEGER)((SQLLEN)src);
    else if (dest_type == SQL_IS_LEN)
      *(SQLLEN *)dest = (SQLLEN)src;
    else if (dest_type == SQL_IS_ULEN)
      *(SQLULEN *)dest = (SQLULEN)src;
    break;

  case SQL_IS_USMALLINT:
  case SQL_IS_UINTEGER:
  case SQL_IS_ULEN:
    if (dest_type == SQL_IS_SMALLINT)
      *(SQLSMALLINT *)dest = (SQLSMALLINT)((SQLULEN)src);
    else if (dest_type == SQL_IS_USMALLINT)
      *(SQLUSMALLINT *)dest = (SQLUSMALLINT)((SQLULEN)src);
    else if (dest_type == SQL_IS_INTEGER)
      *(SQLINTEGER *)dest = (SQLINTEGER)((SQLULEN)src);
    else if (dest_type == SQL_IS_UINTEGER)
      *(SQLUINTEGER *)dest = (SQLUINTEGER)((SQLULEN)src);
    else if (dest_type == SQL_IS_LEN)
      *(SQLLEN *)dest= (SQLULEN)src;
    else if (dest_type == SQL_IS_ULEN)
      *(SQLULEN *)dest= (SQLULEN)src;
    break;

  case SQL_NTS:
  case SQL_IS_POINTER:
    *(SQLPOINTER *)dest= src;
    break;

  default:
    /* TODO it's an actual data length */
    /* free/malloc to the field and copy it */
    /* TODO .. check for 22001 - String data, right truncated
     * The FieldIdentifier argument was SQL_DESC_NAME,
     * and the BufferLength argument was a value larger
     * than SQL_MAX_IDENTIFIER_LEN.
     */
    break;
  }
}


/*
 * Get a descriptor field based on the constant.
 */
static desc_field *
getfield(SQLSMALLINT fldid)
{
  /* all field descriptions are immutable */
  /* See: SQLSetDescField() documentation
   * http://msdn2.microsoft.com/en-us/library/ms713560.aspx */
  HDR_FLD(alloc_type        , P_RI|P_RA          , SQL_IS_SMALLINT);
  HDR_FLD(array_size        , P_RA|P_WA          , SQL_IS_ULEN    );
  HDR_FLD(array_status_ptr  , P_RI|P_WI|P_RA|P_WA, SQL_IS_POINTER );
  HDR_FLD(bind_offset_ptr   , P_RA|P_WA          , SQL_IS_POINTER );
  HDR_FLD(bind_type         , P_RA|P_WA          , SQL_IS_INTEGER );
  HDR_FLD(count             , P_RI|P_WI|P_RA|P_WA, SQL_IS_LEN     );
  HDR_FLD(rows_processed_ptr, P_RI|P_WI          , SQL_IS_POINTER );

  REC_FLD(auto_unique_value, PR_RIR                     , SQL_IS_INTEGER);
  REC_FLD(base_column_name , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(base_table_name  , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(case_sensitive   , PR_RIR|PR_RIP              , SQL_IS_INTEGER);
  REC_FLD(catalog_name     , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(concise_type     , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_SMALLINT);
  REC_FLD(data_ptr         , PR_WAR|PR_WAP              , SQL_IS_POINTER);
  REC_FLD(display_size     , PR_RIR                     , SQL_IS_LEN);
  REC_FLD(fixed_prec_scale , PR_RIR|PR_RIP              , SQL_IS_SMALLINT);
  REC_FLD(indicator_ptr    , PR_WAR|PR_WAP              , SQL_IS_POINTER);
  REC_FLD(label            , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(length           , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_ULEN);
  REC_FLD(literal_prefix   , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(literal_suffix   , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(local_type_name  , PR_RIR|PR_RIP              , SQL_IS_POINTER);
  REC_FLD(name             , PR_RIR|PR_WIP              , SQL_NTS);
  REC_FLD(nullable         , PR_RIR|PR_RIP              , SQL_IS_SMALLINT);
  REC_FLD(num_prec_radix   , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_INTEGER);
  REC_FLD(octet_length     , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_LEN);
  REC_FLD(octet_length_ptr , PR_WAR|PR_WAP              , SQL_IS_POINTER);
  REC_FLD(parameter_type   , PR_WIP                     , SQL_IS_SMALLINT);
  REC_FLD(precision        , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_SMALLINT);
  REC_FLD(rowver           , PR_RIR|PR_RIP              , SQL_IS_SMALLINT);
  REC_FLD(scale            , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_SMALLINT);
  REC_FLD(schema_name      , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(searchable       , PR_RIR                     , SQL_IS_SMALLINT);
  REC_FLD(table_name       , PR_RIR                     , SQL_IS_POINTER);
  REC_FLD(type             , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_SMALLINT);
  REC_FLD(type_name        , PR_RIR|PR_RIP              , SQL_IS_POINTER);
  REC_FLD(unnamed          , PR_RIR|PR_WIP              , SQL_IS_SMALLINT);
  REC_FLD(is_unsigned      , PR_RIR|PR_RIP              , SQL_IS_SMALLINT);
  REC_FLD(updatable        , PR_RIR                     , SQL_IS_SMALLINT);
  REC_FLD(datetime_interval_code      , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_SMALLINT);
  REC_FLD(datetime_interval_precision , PR_WAR|PR_WAP|PR_RIR|PR_WIP, SQL_IS_INTEGER);

  /* match 'field' names above */
  switch(fldid)
  {
  case SQL_DESC_ALLOC_TYPE:
    return &HDR_alloc_type;
  case SQL_DESC_ARRAY_SIZE:
    return &HDR_array_size;
  case SQL_DESC_ARRAY_STATUS_PTR:
    return &HDR_array_status_ptr;
  case SQL_DESC_BIND_OFFSET_PTR:
    return &HDR_bind_offset_ptr;
  case SQL_DESC_BIND_TYPE:
    return &HDR_bind_type;
  case SQL_DESC_COUNT:
    return &HDR_count;
  case SQL_DESC_ROWS_PROCESSED_PTR:
    return &HDR_rows_processed_ptr;

  case SQL_DESC_AUTO_UNIQUE_VALUE:
    return &REC_auto_unique_value;
  case SQL_DESC_BASE_COLUMN_NAME:
    return &REC_base_column_name;
  case SQL_DESC_BASE_TABLE_NAME:
    return &REC_base_table_name;
  case SQL_DESC_CASE_SENSITIVE:
    return &REC_case_sensitive;
  case SQL_DESC_CATALOG_NAME:
    return &REC_catalog_name;
  case SQL_DESC_CONCISE_TYPE:
    return &REC_concise_type;
  case SQL_DESC_DATA_PTR:
    return &REC_data_ptr;
  case SQL_DESC_DISPLAY_SIZE:
    return &REC_display_size;
  case SQL_DESC_FIXED_PREC_SCALE:
    return &REC_fixed_prec_scale;
  case SQL_DESC_INDICATOR_PTR:
    return &REC_indicator_ptr;
  case SQL_DESC_LABEL:
    return &REC_label;
  case SQL_DESC_LENGTH:
    return &REC_length;
  case SQL_DESC_LITERAL_PREFIX:
    return &REC_literal_prefix;
  case SQL_DESC_LITERAL_SUFFIX:
    return &REC_literal_suffix;
  case SQL_DESC_LOCAL_TYPE_NAME:
    return &REC_local_type_name;
  case SQL_DESC_NAME:
    return &REC_name;
  case SQL_DESC_NULLABLE:
    return &REC_nullable;
  case SQL_DESC_NUM_PREC_RADIX:
    return &REC_num_prec_radix;
  case SQL_DESC_OCTET_LENGTH:
    return &REC_octet_length;
  case SQL_DESC_OCTET_LENGTH_PTR:
    return &REC_octet_length_ptr;
  case SQL_DESC_PARAMETER_TYPE:
    return &REC_parameter_type;
  case SQL_DESC_PRECISION:
    return &REC_precision;
  case SQL_DESC_ROWVER:
    return &REC_rowver;
  case SQL_DESC_SCALE:
    return &REC_scale;
  case SQL_DESC_SCHEMA_NAME:
    return &REC_schema_name;
  case SQL_DESC_SEARCHABLE:
    return &REC_searchable;
  case SQL_DESC_TABLE_NAME:
    return &REC_table_name;
  case SQL_DESC_TYPE:
    return &REC_type;
  case SQL_DESC_TYPE_NAME:
    return &REC_type_name;
  case SQL_DESC_UNNAMED:
    return &REC_unnamed;
  case SQL_DESC_UNSIGNED:
    return &REC_is_unsigned;
  case SQL_DESC_UPDATABLE:
    return &REC_updatable;
  case SQL_DESC_DATETIME_INTERVAL_CODE:
    return &REC_datetime_interval_code;
  case SQL_DESC_DATETIME_INTERVAL_PRECISION:
    return &REC_datetime_interval_precision;
  }
  return NULL;
}


/*
  @type    : ODBC 3.0 API
  @purpose : Get a field of a descriptor.
 */
SQLRETURN
MySQLGetDescField(SQLHDESC hdesc, SQLSMALLINT recnum, SQLSMALLINT fldid,
                  SQLPOINTER valptr, SQLINTEGER buflen, SQLINTEGER *outlen)
{
  DESC *desc= (DESC *)hdesc;
  desc_field *fld = getfield(fldid);
  void *src_struct;
  void *src;

  if (desc == NULL)
  {
    return SQL_INVALID_HANDLE;
  }

  CLEAR_DESC_ERROR(desc);

  if (IS_IRD(desc) && desc->stmt->state < ST_PREPARED)
    /* TODO if it's prepared is the IRD still ok to access?
     * or must we pre-execute it */
    return set_desc_error(desc, "HY007",
              "Associated statement is not prepared",
              MYERR_S1007);

  if ((fld == NULL) ||
      /* header permissions check */
      (fld->loc == DESC_HDR &&
         (desc->ref_type == DESC_APP && (~fld->perms & P_RA)) ||
         (desc->ref_type == DESC_IMP && (~fld->perms & P_RI))))
  {
    return set_desc_error(desc, "HY091",
              "Invalid descriptor field identifier",
              MYERR_S1091);
  }
  else if (fld->loc == DESC_REC)
  {
    int perms= 0; /* needed perms to access */

    if (desc->ref_type == DESC_APP)
      perms= P_RA;
    else if (desc->ref_type == DESC_IMP)
      perms= P_RI;

    if (desc->desc_type == DESC_PARAM)
      perms= P_PAR(perms);
    else if (desc->desc_type == DESC_ROW)
      perms= P_ROW(perms);

    if ((~fld->perms & perms) == perms)
      return set_desc_error(desc, "HY091",
                "Invalid descriptor field identifier",
                MYERR_S1091);
  }

  /* get the src struct */
  if (fld->loc == DESC_HDR)
    src_struct= desc;
  else
  {
    if (recnum < 1 || recnum > desc->rcount())
      return set_desc_error(desc, "07009",
                "Invalid descriptor index",
                MYERR_07009);
    src_struct= desc_get_rec(desc, recnum - 1, FALSE);
    assert(src_struct);
  }

  if (fldid == SQL_DESC_COUNT)
  {
    /*
      This requires special treatment because the field is dynamically
      obtained from the function
    */
    desc->rcount();
  }

  src= ((char *)src_struct) + fld->offset;

  /* TODO checks when strings? */
  if ((fld->data_type == SQL_IS_POINTER && buflen != SQL_IS_POINTER) ||
      (fld->data_type != SQL_IS_POINTER && buflen == SQL_IS_POINTER))
    return set_desc_error(desc, "HY015",
                          "Invalid parameter type",
                          MYERR_S1015);

  switch (buflen)
  {
  case SQL_IS_SMALLINT:
    if (fld->data_type == SQL_IS_SMALLINT)
      *(SQLSMALLINT *)valptr= *(SQLSMALLINT *)src;
    else if (fld->data_type == SQL_IS_USMALLINT)
      *(SQLSMALLINT *)valptr= *(SQLUSMALLINT *)src;
    else if (fld->data_type == SQL_IS_INTEGER)
      *(SQLSMALLINT *)valptr= (SQLSMALLINT)(*(SQLINTEGER *)src);
    else if (fld->data_type == SQL_IS_UINTEGER)
      *(SQLSMALLINT *)valptr = (SQLSMALLINT)(*(SQLUINTEGER *)src);
    else if (fld->data_type == SQL_IS_LEN)
      *(SQLSMALLINT *)valptr = (SQLSMALLINT)(*(SQLLEN *)src);
    else if (fld->data_type == SQL_IS_ULEN)
      *(SQLSMALLINT *)valptr = (SQLSMALLINT)(*(SQLULEN *)src);
    break;

  case SQL_IS_USMALLINT:
    if (fld->data_type == SQL_IS_SMALLINT)
      *(SQLUSMALLINT *)valptr= *(SQLSMALLINT *)src;
    else if (fld->data_type == SQL_IS_USMALLINT)
      *(SQLUSMALLINT *)valptr= *(SQLUSMALLINT *)src;
    else if (fld->data_type == SQL_IS_INTEGER)
      *(SQLUSMALLINT *)valptr = (SQLUSMALLINT)(*(SQLINTEGER *)src);
    else if (fld->data_type == SQL_IS_UINTEGER)
      *(SQLUSMALLINT *)valptr = (SQLUSMALLINT)(*(SQLUINTEGER *)src);
    else if (fld->data_type == SQL_IS_LEN)
      *(SQLUSMALLINT *)valptr = (SQLUSMALLINT)(*(SQLLEN *)src);
    else if (fld->data_type == SQL_IS_ULEN)
      *(SQLUSMALLINT *)valptr = (SQLUSMALLINT)(*(SQLULEN *)src);
    break;

  case SQL_IS_INTEGER:
    if (fld->data_type == SQL_IS_SMALLINT)
      *(SQLINTEGER *)valptr= *(SQLSMALLINT *)src;
    else if (fld->data_type == SQL_IS_USMALLINT)
      *(SQLINTEGER *)valptr= *(SQLUSMALLINT *)src;
    else if (fld->data_type == SQL_IS_INTEGER)
      *(SQLINTEGER *)valptr= *(SQLINTEGER *)src;
    else if (fld->data_type == SQL_IS_UINTEGER)
      *(SQLINTEGER *)valptr= *(SQLUINTEGER *)src;
    else if (fld->data_type == SQL_IS_LEN)
      *(SQLINTEGER *)valptr = (SQLINTEGER)(*(SQLLEN *)src);
    else if (fld->data_type == SQL_IS_ULEN)
      *(SQLINTEGER *)valptr = (SQLINTEGER)(*(SQLULEN *)src);
    break;

  case SQL_IS_UINTEGER:
    if (fld->data_type == SQL_IS_SMALLINT)
      *(SQLUINTEGER *)valptr= *(SQLSMALLINT *)src;
    else if (fld->data_type == SQL_IS_USMALLINT)
      *(SQLUINTEGER *)valptr= *(SQLUSMALLINT *)src;
    else if (fld->data_type == SQL_IS_INTEGER)
      *(SQLUINTEGER *)valptr= *(SQLINTEGER *)src;
    else if (fld->data_type == SQL_IS_UINTEGER)
      *(SQLUINTEGER *)valptr= *(SQLUINTEGER *)src;
    else if (fld->data_type == SQL_IS_LEN)
      *(SQLUINTEGER *)valptr = (SQLUINTEGER)(*(SQLLEN *)src);
    else if (fld->data_type == SQL_IS_ULEN)
      *(SQLUINTEGER *)valptr = (SQLUINTEGER)(*(SQLULEN *)src);
    break;

  case SQL_IS_LEN:
    if (fld->data_type == SQL_IS_SMALLINT)
      *(SQLLEN *)valptr= *(SQLSMALLINT *)src;
    else if (fld->data_type == SQL_IS_USMALLINT)
      *(SQLLEN *)valptr= *(SQLUSMALLINT *)src;
    else if (fld->data_type == SQL_IS_INTEGER)
      *(SQLLEN *)valptr= *(SQLINTEGER *)src;
    else if (fld->data_type == SQL_IS_UINTEGER)
      *(SQLLEN *)valptr= *(SQLUINTEGER *)src;
    else if (fld->data_type == SQL_IS_LEN)
      *(SQLLEN *)valptr= *(SQLLEN *)src;
    else if (fld->data_type == SQL_IS_ULEN)
      *(SQLLEN *)valptr= *(SQLULEN *)src;
    break;

  case SQL_IS_ULEN:
    if (fld->data_type == SQL_IS_SMALLINT)
      *(SQLULEN *)valptr= *(SQLSMALLINT *)src;
    else if (fld->data_type == SQL_IS_USMALLINT)
      *(SQLULEN *)valptr= *(SQLUSMALLINT *)src;
    else if (fld->data_type == SQL_IS_INTEGER)
      *(SQLULEN *)valptr= *(SQLINTEGER *)src;
    else if (fld->data_type == SQL_IS_UINTEGER)
      *(SQLULEN *)valptr= *(SQLUINTEGER *)src;
    else if (fld->data_type == SQL_IS_LEN)
      *(SQLULEN *)valptr= *(SQLLEN *)src;
    else if (fld->data_type == SQL_IS_ULEN)
      *(SQLULEN *)valptr= *(SQLULEN *)src;
    break;

  case SQL_IS_POINTER:
    *(SQLPOINTER *)valptr= *(SQLPOINTER *)src;
    break;

  default:
    /* TODO it's an actual data length */
    /* free/malloc to the field and copy it, etc, etc */
    break;
  }

  return SQL_SUCCESS;
}


SQLRETURN DESC::set_error(char *state, const char *message, uint errcode)
{
  error.sqlstate = state ? state : "";
  error.message = std::string(stmt->dbc->st_error_prefix) + message;
  error.native_error = errcode;

  return SQL_ERROR;
}

DESC::DESC(STMT *p_stmt, SQLSMALLINT p_alloc_type,
    desc_ref_type p_ref_type, desc_desc_type p_desc_type) :
    alloc_type(p_alloc_type), array_size(1), array_status_ptr(nullptr),
    bind_offset_ptr(nullptr), bind_type(SQL_BIND_BY_COLUMN),
    count(0), bookmark_count(0),
    rows_processed_ptr(nullptr), desc_type(p_desc_type), ref_type(p_ref_type),
    stmt(p_stmt), dbc(nullptr)
{
  if(stmt != nullptr)
    dbc = p_stmt->dbc;
}


/*
  @type    : ODBC 3.0 API
  @purpose : Set a field of a descriptor.
 */
SQLRETURN DESC::set_field(SQLSMALLINT recnum, SQLSMALLINT fldid,
                      SQLPOINTER val, SQLINTEGER buflen)
{
  desc_field *fld = getfield(fldid);
  void *dest_struct;
  void *dest;

  error.clear();

  /* check for invalid IRD modification */
  if (is_ird())
  {
    switch (fldid)
    {
      case SQL_DESC_ARRAY_STATUS_PTR:
      case SQL_DESC_ROWS_PROCESSED_PTR:
        break;
      default:
      return set_error("HY016",
                      "Cannot modify an implementation row descriptor",
                      MYERR_S1016);
    }
  }

  if ((fld == NULL) ||
      /* header permissions check */
      (fld->loc == DESC_HDR &&
         ((ref_type == DESC_APP && (~fld->perms & P_WA)) ||
          (ref_type == DESC_IMP && (~fld->perms & P_WI)))))
  {
    return set_error("HY091",
                     "Invalid descriptor field identifier",
                     MYERR_S1091);
  }
  else if (fld->loc == DESC_REC)
  {
    int perms= 0; /* needed perms to access */

    if (ref_type == DESC_APP)
      perms= P_WA;
    else if (ref_type == DESC_IMP)
      perms= P_WI;

    if (desc_type == DESC_PARAM)
      perms= P_PAR(perms);
    else if (desc_type == DESC_ROW)
      perms= P_ROW(perms);

    if ((~fld->perms & perms) == perms)
      return set_error("HY091",
                       "Invalid descriptor field identifier",
                       MYERR_S1091);
  }

  /* get the dest struct */
  if (fld->loc == DESC_HDR)
    dest_struct= this;
  else
  {
    if (recnum < 1 && stmt->stmt_options.bookmarks == SQL_UB_OFF)
      return set_error("07009",
                       "Invalid descriptor index",
                       MYERR_07009);
    else
      dest_struct= desc_get_rec(this, recnum - 1, TRUE);
  }

  dest= ((char *)dest_struct) + fld->offset;

  /* some applications and even MSDN examples don't give a correct constant */
  if (buflen == 0)
    buflen= fld->data_type;

  /* TODO checks when strings? */
  if ((fld->data_type == SQL_IS_POINTER && buflen != SQL_IS_POINTER) ||
      (fld->data_type != SQL_IS_POINTER && buflen == SQL_IS_POINTER))
    return set_error("HY015",
                     "Invalid parameter type",
                     MYERR_S1015);

  /* per-field checks/functionality */
  switch (fldid)
  {
  case SQL_DESC_COUNT:
    /* we just force the descriptor record count to expand */
    (void)desc_get_rec(this, (int)((size_t)val - 1), TRUE);
    break;
  case SQL_DESC_NAME:
    {
      // dest_struct already points to the correct DESCREC*
      DESCREC *name_rec = (DESCREC*)dest_struct;
      // Add name as parameter data and zero terminating character
      name_rec->par.add_param_data((char*)val, (unsigned long)strlen((char*)val) + 1);
      // Get a pointer to allocated copy of the name
      val = name_rec->par.val();
    }
    break;
    /* We don't support named parameters, values stay as initialized */
    //return set_desc_error(desc, "01S01",
    //                      "Option value changed",
    //                      MYERR_01S02);
  case SQL_DESC_UNNAMED:
    if ((size_t)val == SQL_NAMED)
      return set_error("HY092",
                       "Invalid attribute/option identifier",
                       MYERR_S1092);
  }

  /* We have to unbind the value if not setting a buffer */
  switch (fldid)
  {
  case SQL_DESC_DATA_PTR:
  case SQL_DESC_OCTET_LENGTH_PTR:
  case SQL_DESC_INDICATOR_PTR:
    break;
  default:
    if (fld->loc == DESC_REC)
    {
      DESCREC *rec= (DESCREC *) dest_struct;
      rec->data_ptr= NULL;
    }
  }

  apply_desc_val(dest, fld->data_type, val, buflen);

  /* post-set responsibilities */
  /*http://msdn.microsoft.com/en-us/library/ms710963%28v=vs.85%29.aspx
    "ParameterType Argument" sectiosn - basically IPD has to be heres as well with same rules
    C and SQL types match. Thus we can use same function for calculation of type and dti code.
   */
  if ((is_ard() || is_apd() || is_ipd()) && fld->loc == DESC_REC)
  {
    DESCREC *rec= (DESCREC *) dest_struct;
    switch (fldid)
    {
    case SQL_DESC_TYPE:
      rec->concise_type= rec->type;
      rec->datetime_interval_code= 0;
      break;
    case SQL_DESC_CONCISE_TYPE:
      rec->type= get_type_from_concise_type(rec->concise_type);
      rec->datetime_interval_code=
        get_dticode_from_concise_type(rec->concise_type);
      break;
    case SQL_DESC_DATETIME_INTERVAL_CODE: /* TODO validation for this value? */
      /* SQL_DESC_TYPE has to have already been set */
      if (rec->type == SQL_DATETIME)
        rec->concise_type=
          get_concise_type_from_datetime_code(rec->datetime_interval_code);
      else
        rec->concise_type=
          get_concise_type_from_interval_code(rec->datetime_interval_code);
      break;
    }

    switch (fldid)
    {
    case SQL_DESC_TYPE:
    case SQL_DESC_CONCISE_TYPE:
      /* setup type specific defaults (TODO others besides SQL_C_NUMERIC)? */
      if (is_ard() && rec->type == SQL_C_NUMERIC)
      {
        rec->precision= 38;
        rec->scale= 0;
      }
    }
  }

  /*
    Set "real_param_done" for parameters if all fields needed to bind
    a parameter are set.
  */
  if (is_apd() && val != NULL && fld->loc == DESC_REC)
  {
    DESCREC *rec= (DESCREC *) dest_struct;
    switch (fldid)
    {
    case SQL_DESC_DATA_PTR:
    case SQL_DESC_OCTET_LENGTH_PTR:
    case SQL_DESC_INDICATOR_PTR:
      rec->par.real_param_done= TRUE;
      break;
    }
  }

  return SQL_SUCCESS;
}


/*
  @type    : ODBC 3.0 API
  @purpose : Copy descriptor information from one descriptor to another.
             Errors are placed in the TargetDescHandle.
 */
SQLRETURN MySQLCopyDesc(SQLHDESC SourceDescHandle, SQLHDESC TargetDescHandle)
{
  DESC *src= (DESC *)SourceDescHandle;
  DESC *dest= (DESC *)TargetDescHandle;

  CLEAR_DESC_ERROR(dest);

  if (IS_IRD(dest))
    return set_desc_error(dest, "HY016",
                          "Cannot modify an implementation row descriptor",
                          MYERR_S1016);

  if (IS_IRD(src) && src->stmt->state < ST_PREPARED)
    return set_desc_error(dest, "HY007",
              "Associated statement is not prepared",
              MYERR_S1007);

  /* copy the records, copy constructors should take care of everything */
  *dest = *src;

  /* TODO consistency check on target, if needed (apd) */

  return SQL_SUCCESS;
}


/*
 * Call SQLGetDescField in the "context" of a statement. This will copy
 * any error from the descriptor to the statement.
 */
SQLRETURN
stmt_SQLGetDescField(STMT *stmt, DESC *desc, SQLSMALLINT recnum,
                     SQLSMALLINT fldid, SQLPOINTER valptr,
                     SQLINTEGER buflen, SQLINTEGER *outlen)
{
  SQLRETURN rc;
  if ((rc= MySQLGetDescField((SQLHANDLE)desc, recnum, fldid,
                             valptr, buflen, outlen)) != SQL_SUCCESS)
    stmt->error = desc->error;
  return rc;
}


/*
 * Call SQLSetDescField in the "context" of a statement. This will copy
 * any error from the descriptor to the statement.
 */
SQLRETURN
stmt_SQLSetDescField(STMT *stmt, DESC *desc, SQLSMALLINT recnum,
                     SQLSMALLINT fldid, SQLPOINTER val, SQLINTEGER buflen)
{
  SQLRETURN rc;
  CHECK_HANDLE(desc);

  if ((rc= desc->set_field(recnum, fldid, val, buflen)) != SQL_SUCCESS)
    stmt->error = desc->error;
  return rc;
}


/*
 * Call SQLCopyDesc in the "context" of a statement. This will copy
 * any error from the descriptor to the statement.
 */
SQLRETURN stmt_SQLCopyDesc(STMT *stmt, DESC *src, DESC *dest)
{
  SQLRETURN rc;
  if ((rc= MySQLCopyDesc((SQLHANDLE)src, (SQLHANDLE)dest)) != SQL_SUCCESS)
    stmt->error = dest->error;
  return rc;
}


SQLRETURN SQL_API
SQLCopyDesc(SQLHDESC SourceDescHandle, SQLHDESC TargetDescHandle)
{
  CHECK_HANDLE(SourceDescHandle);
  CHECK_HANDLE(TargetDescHandle);

  return MySQLCopyDesc(SourceDescHandle, TargetDescHandle);
}

