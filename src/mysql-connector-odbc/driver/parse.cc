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

/**
  @file  parse.c
  @brief utilities to parse queries
*/

#include "driver.h"

static const MY_QUERY_TYPE query_types_array[]=
{
  /*myqtSelect*/      {'\1', '\1', NULL},
  /*myqtInsert*/      {'\0', '\1', NULL},
  /*myqtUpdate*/      {'\0', '\1', NULL},
  /*myqtCall*/        {'\1', '\1', "5.5.3"},
  /*myqtShow*/        {'\1', '\1', NULL},
  /*myqtUse*/         {'\0', '\0', NULL},
  /*myqtCreateTable*/ {'\0', '\0', NULL},
  /*myqtCreateProc*/  {'\0', '\0', NULL},
  /*myqtCreateFunc*/  {'\0', '\0', NULL},
  /*myqtDropProc*/    {'\0', '\0', NULL},
  /*myqtDropFunc*/    {'\0', '\0', NULL},
  /*myqtOptimize*/    {'\0', '\1', "5.0.23"},/*to check*/
  /*myqtOther*/       {'\0', '\1', NULL},
};

/*static? */
static const MY_STRING escape=       {"\\" , 1, 1};
static const MY_STRING odbc_open=    {"{"  , 1, 1};
static const MY_STRING odbc_close=   {"}"  , 1, 1};
static const MY_STRING param_marker= {"?"  , 1, 1};

static const MY_STRING select_=    {"SELECT"   , 6, 6};
static const MY_STRING insert=     {"INSERT"   , 6, 6};
static const MY_STRING update=     {"UPDATE"   , 6, 6};
static const MY_STRING call=       {"CALL"     , 4, 4};
static const MY_STRING show=       {"SHOW"     , 4, 4};
static const MY_STRING use=        {"USE"      , 3, 3};
static const MY_STRING create=     {"CREATE"   , 6, 6};
static const MY_STRING drop=       {"DROP"     , 4, 4};
static const MY_STRING table=      {"TABLE"    , 5, 5};
static const MY_STRING procedure=  {"PROCEDURE", 9, 9};
static const MY_STRING function=   {"FUNCTION" , 8, 8};
static const MY_STRING where_=     {"WHERE"    , 5, 5};
static const MY_STRING current=    {"CURRENT"  , 7, 7};
static const MY_STRING of=         {"OF"       , 2, 2};
static const MY_STRING limit=      {"LIMIT"    , 5, 5};
static const MY_STRING optimize=   {"OPTIMIZE" , 8, 8};

static const MY_SYNTAX_MARKERS ansi_syntax_markers= {/*quote*/
                                              {
                                                {"'", 1, 1}, {"\"", 1, 1},
                                                {"`", 1, 1}
                                              },
                                              /*query_sep*/
                                              {{";", 1, 1}, {"\\g", 2, 2}},
                                              /*escape*/      &escape,
                                              /*odbc open*/   &odbc_open,
                                              /*odbc close*/  &odbc_close,
                                              /*param marker*/&param_marker,
                                              /*keyword*/
                                              {"#", 1, 1}, /* Comment hash */
                                              {"-- ", 3, 3}, /* Comment double dash */
                                              {"/*", 2, 2},  /* C style comment start */
                                              {"*/", 2, 2}, /* C style comment end  */
                                              {"/*!", 3, 3}, /* Special not a comment syntax */
#ifdef _WIN32
                                              {"\r\n", 2, 2},
#else
                                              {"\n", 1, 1},
#endif
                                              {
                                                &select_, &insert, &update,
                                                &call, &show, &use, &create,
                                                &drop, &table, &procedure,
                                                &function, &where_, &current,
                                                &of, &limit
                                              }
                                             };

static const QUERY_TYPE_RESOLVING func_rule=
{ &function,  1,          4,          myqtCreateFunc,   NULL, NULL };
static const QUERY_TYPE_RESOLVING proc_rule=
{ &procedure, 1,          4,          myqtCreateProc,   NULL, &func_rule };
static const QUERY_TYPE_RESOLVING drop_func_rule=
{ &function,  1,          4,          myqtDropFunc,     NULL, NULL };
static const QUERY_TYPE_RESOLVING drop_proc_rule=
{ &procedure, 1,          4,          myqtDropProc,     NULL, &drop_func_rule };
static const QUERY_TYPE_RESOLVING crt_table_rule=
{ &table,     1,          2,          myqtCreateTable,  NULL, &proc_rule };


static const QUERY_TYPE_RESOLVING rule[]=
{ /*keyword*/ /*pos_from*/ /*pos_thru*/ /*query_type*/ /*and_rule*/ /*or_rule*/
  { &select_,   0,          0,          myqtSelect,     NULL,       NULL},
  { &call,      0,          0,          myqtCall,       NULL,       NULL},
  { &insert,    0,          0,          myqtInsert,     NULL,       NULL},
  { &update,    0,          0,          myqtUpdate,     NULL,       NULL},
  { &show,      0,          0,          myqtShow,       NULL,       NULL},
  { &create,    0,          0,          myqtOther,      &crt_table_rule, NULL},
  { &drop,      0,          0,          myqtOther,      &drop_proc_rule, NULL},
  { &use,       0,          0,          myqtUse,        NULL,       NULL},
  { &optimize,  0,          0,          myqtOptimize,   NULL,       NULL},
  {NULL, 0, 0, myqtOther, NULL, NULL}
};

MY_PARSED_QUERY::MY_PARSED_QUERY() : buf(1024) {
    query =      NULL;
    query_end =  NULL;
    last_char =  NULL;
    is_batch =   NULL;

    query_type= myqtOther;
    token2.reserve(20);
    param_pos.reserve(20);
}


void MY_PARSED_QUERY::reset(char * query, char * query_end, CHARSET_INFO *cs)
{
  token2.clear();
  param_pos.clear();
  last_char = nullptr;
  is_batch = nullptr;
  query_type = myqtOther;
  buf.reset();

  if (query == nullptr)
  {
    this->query = nullptr;
    this->cs = nullptr;
    this->query_end = nullptr;
  }
  else
  {
    this->cs = cs;
    size_t query_len = 0;
    if (query_end) {
      query_len = query_end - query;
    } else if (query) {
      query_len = strlen(query);
    }
    buf.add_to_buffer(query, query_len + 1);
    buf.buf[query_len] = '\0';
    this->query = buf.buf;
    this->query_end = this->query + query_len;
  }
}


MY_PARSED_QUERY::~MY_PARSED_QUERY()
{ }


MY_PARSED_QUERY &MY_PARSED_QUERY::operator=(const MY_PARSED_QUERY &src)
{
  if (this == &src)
    return *this;

  buf = src.buf;
  reset(buf.buf, buf.buf + GET_QUERY_LENGTH(&src), cs);
  if (src.last_char != NULL)
  {
    last_char = query + (src.last_char - src.query);
  }

  if (src.is_batch != NULL)
  {
    is_batch = query + (src.is_batch - src.query);
  }

  query_type = src.query_type;
  token2 = src.token2;
  param_pos = src.param_pos;
  return *this;
}


MY_PARSER * init_parser(MY_PARSER * parser, MY_PARSED_QUERY *pq)
{
  parser->query=  pq;
  parser->pos=    GET_QUERY(pq);
  parser->quote=  NULL;

  get_ctype(parser);

  /* TODO: loading it in required encoding */
  parser->syntax= &ansi_syntax_markers;

  return parser;
}


const char *MY_PARSED_QUERY::get_token(uint index) {
  if (index < token2.size())
  {
    return query + token2[index];
  }

  return nullptr;
}


const char *MY_PARSED_QUERY::get_param_pos(uint index) {
  if (index < param_pos.size())
  {
    return query + param_pos[index];
  }

  return NULL;
}


bool MY_PARSED_QUERY::returns_result() {
  return query_types_array[query_type].returns_rs;
}


bool MY_PARSED_QUERY::preparable_on_server(const char *server_version) {
  if (query_types_array[query_type].preparable_on_server)
  {
    return query_types_array[query_type].server_version == NULL
        || is_minimum_version(server_version,
                              query_types_array[query_type].server_version);
  }

  return FALSE;
}


const char *MY_PARSED_QUERY::get_cursor_name() {
  size_t tcount = token_count();
  if (tcount > 4)
  {
    if (case_compare(this, get_token((uint)tcount - 4), &where_) &&
        case_compare(this, get_token((uint)tcount - 3), &current) &&
        case_compare(this, get_token((uint)tcount - 2), &of))
    {
      return get_token((uint)tcount - 1);
    }
  }

  return NULL;
}

size_t MY_PARSED_QUERY::token_count() { return token2.size();}

/* But returns bytes in current character. not sure that is needed though */
int  get_ctype(MY_PARSER *parser)
{
  if (END_NOT_REACHED(parser))
  {
    int byte_count = parser->query->cs->cset->ctype(parser->query->cs,
                                      &parser->ctype, (const uchar*)parser->pos,
                                      (const uchar*) parser->query->query_end);
    // We need to get the byte count after conversion.
    // The libmysql ctype() function can return negated
    // byte count for characters that do not have a
    // corresponding Unicode endpoint.
    parser->bytes_at_pos = (byte_count < 0 ? -byte_count : byte_count);
  }
  else
  {
    parser->bytes_at_pos= 0;
  }

  return parser->bytes_at_pos;
}


const char *mystr_get_prev_token(CHARSET_INFO *charset,
                                        const char **query, const char *start)
{
  const char *pos= *query, *end= *query;

  do
  {
    if (pos == start)
      return (*query = start);     /* Return start of string */
    --pos;
  } while (*pos < 0 || !myodbc_isspace(charset, pos, end)) ;

  *query= pos;      /* Remember pos to space */

  return pos + 1;   /* Return found token */
}


/*TODO test it*/
const char *mystr_get_next_token(CHARSET_INFO *charset,
                                        const char **query, const char *end)
{
  const char *pos= *query;

  do
  {
    if (pos == end)
      return (*query = end);     /* Return start of string */
    ++pos;
  } while (*pos > 0 && myodbc_isspace(charset, pos, pos + 1)) ;

  /* Looking for space after token */
  *query= pos + 1;

  while (*query != end && (**query < 0 || !myodbc_isspace(charset, *query, end)))
    ++*query;

  return pos;   /* Return found token */
}


const char * find_token(CHARSET_INFO *charset, const char * begin,
                        const char * end, const char * target)
{
  const char * token, *before= end;

  /* we will not check 1st token in the string - no need at the moment */
  while ((token= mystr_get_prev_token(charset,&before, begin)) != begin)
  {
    if (!myodbc_casecmp(token, target, (uint)strlen(target)))
    {
      return token;
    }
  }

  return NULL;
}


const char * find_first_token(CHARSET_INFO *charset, const char * begin,
                        const char * end, const char * target)
{
  const char * token, *before= end;

  while ((token= mystr_get_next_token(charset, &begin, end)) != end)
  {
    if (!myodbc_casecmp(token, target, (uint)strlen(target)))
    {
      return token;
    }
  }

  return NULL;
}


const char * skip_leading_spaces(const char *str)
{
  while (str && isspace(*str))
    ++str;

  return str;
}

/* TODO: We can't have a separate function for detecting of
         each type of a query */
/**
 Detect if a statement is a SET NAMES statement.
*/
int is_set_names_statement(const char *query)
{
  query= skip_leading_spaces(query);
  return myodbc_casecmp(query, "SET NAMES", 9) == 0;
}


/**
Detect if a statement is a SELECT statement.
*/
bool MY_PARSED_QUERY::is_select_statement()
{
  return query_type == myqtSelect;
}


/* These functions expect that leasding spaces have been skipped */
BOOL is_drop_procedure(const char* query)
{
  if (myodbc_casecmp(query, "DROP", 4) == 0 && *(query+4) != '\0'
    && isspace(*(query+4)))
  {
    query= skip_leading_spaces(query+5);
    return myodbc_casecmp(query, "PROCEDURE", 9) == 0;
  }

  return FALSE;
}


BOOL is_drop_function(const char* query)
{
  if (myodbc_casecmp(query, "DROP", 4) == 0 && *(query+4) != '\0'
    && isspace(*(query+4)))
  {
    query= skip_leading_spaces(query+5);
    return myodbc_casecmp(query, "FUNCTION", 8) == 0;
  }

  return FALSE;
}


/* In fact this function catches all CREATE queries with DEFINER as well.
   But so far we are fine with that and even are using that.*/
BOOL is_create_procedure(const char* query)
{
  if (myodbc_casecmp(query, "CREATE", 6) == 0 && *(query+6) != '\0'
    && isspace(*(query+6)))
  {
    query= skip_leading_spaces(query+7);

    if (myodbc_casecmp(query, "DEFINER", 7) == 0)
    {
      return TRUE;
    }

    return myodbc_casecmp(query, "PROCEDURE", 9) == 0;
  }

  return FALSE;
}


BOOL is_create_function(const char* query)
{
  if (myodbc_casecmp(query, "CREATE", 6) == 0 && *(query+6) != '\0'
    && isspace(*(query+6)))
  {
    query= skip_leading_spaces(query+7);
    return myodbc_casecmp(query, "FUNCTION", 8) == 0;
  }

  return FALSE;
}


BOOL is_use_db(const char* query)
{
  if (myodbc_casecmp(query, "USE", 3) == 0 && *(query+3) != '\0'
    && isspace(*(query+3)))
  {
    return TRUE;
  }

  return FALSE;
}


BOOL is_call_procedure(const MY_PARSED_QUERY * query)
{
  return query->query_type == myqtCall;
}


/*!
    \brief  Returns true if we are dealing with a statement which
            is likely to result in reading only (SELECT || SHOW).

            Some ODBC calls require knowledge about a statement
            which we can not determine until we have executed
            the statement. This is because we do not parse the SQL
            - the server does.

            However if we silently execute a pending statement we
            may insert rows.

            So we do a very crude check of the SQL here to reduce
            the chance of a problem.

    \sa     BUG 5778
*/
BOOL stmt_returns_result(const MY_PARSED_QUERY *query)
{
  if (query->query_type <= myqtOther)
  {
    return query_types_array[query->query_type].returns_rs;
  }
  return FALSE;
}


/* TRUE if end has been reached */
BOOL skip_spaces(MY_PARSER *parser)
{
  while(END_NOT_REACHED(parser) && (IS_SPACE(parser) || IS_SPL_CHAR(parser)))
  {
    step_char(parser);
  }

  return !END_NOT_REACHED(parser);
}


BOOL skip_comment(MY_PARSER *parser)
{
  while(END_NOT_REACHED(parser) &&
        ((parser->hash_comment &&
            !parser_compare(parser, &parser->syntax->new_line_end)) ||
          (parser->dash_comment &&
            !parser_compare(parser, &parser->syntax->new_line_end)) ||
          (parser->c_style_comment &&
            !parser_compare(parser, &parser->syntax->c_style_close_comment))))
  {
    step_char(parser);
  }

  return !END_NOT_REACHED(parser);
}


void add_token(MY_PARSER *parser)
{
  if (END_NOT_REACHED(parser))
  {

    uint offset = (uint)(parser->pos - GET_QUERY(parser->query));
    auto& tok = parser->query->token2;

    // Reserve more elements if needed
    if (tok.capacity() == tok.size())
      tok.reserve(tok.capacity() + 10);

    tok.push_back(offset);
  }
}


BOOL is_escape(MY_PARSER *parser)
{
  return parser->bytes_at_pos == parser->syntax->escape->bytes
      && memcmp(parser->pos, parser->syntax->escape->str,
                parser->bytes_at_pos) == 0;
}

const MY_STRING * is_quote(MY_PARSER *parser)
{
  int i;

  for (i=0; i < sizeof(parser->syntax->quote)/sizeof(MY_STRING); ++i)
  {
    if (parser->bytes_at_pos == parser->syntax->quote[i].bytes
      && memcmp(parser->pos, parser->syntax->quote[i].str,
                parser->bytes_at_pos) == 0)
    {
      return &parser->syntax->quote[i];
    }
  }

  return NULL;
}


BOOL is_comment(MY_PARSER *parser)
{
  parser->hash_comment= FALSE;
  parser->dash_comment= FALSE;
  parser->c_style_comment= FALSE;

  if (parser_compare(parser, &parser->syntax->hash_comment))
  {
    parser->hash_comment= TRUE;
    return TRUE;
  }
  else if (parser_compare(parser, &parser->syntax->dash_comment))
  {
    parser->dash_comment= TRUE;
    return TRUE;
  }
  /* C style comment variant which is consided not as comment */
  else if (parser_compare(parser, &parser->syntax->c_var_open_comment))
  {
    return FALSE;
  }
  else if (parser_compare(parser, &parser->syntax->c_style_open_comment))
  {
    parser->c_style_comment= TRUE;
    return TRUE;
  }

  return FALSE;
}


/*static?*/
BOOL is_closing_quote(MY_PARSER *parser)
{
  return  parser->bytes_at_pos == parser->quote->bytes
      && memcmp(parser->pos, parser->quote->str,
                parser->bytes_at_pos) == 0;
}


/* Installs position on the character next after closing quote */
const char * find_closing_quote(MY_PARSER *parser)
{
  const char *closing_quote = NULL;
  while(END_NOT_REACHED(parser))
  {
    if (is_escape(parser))
    {
      step_char(parser);
    }
    else if (is_closing_quote(parser))
    {
      closing_quote= parser->pos;

      step_char(parser);

      /* if end of atr or not a new quote
         Basically that does not have to be the same quote type - mysql will
         concat them */
      if (!get_ctype(parser) || !open_quote(parser, is_quote(parser)))
      {
        break;
      }
    }

    step_char(parser);
  }

  return closing_quote;
}


BOOL is_param_marker(MY_PARSER *parser)
{
  return parser->bytes_at_pos == parser->syntax->param_marker->bytes
      && memcmp(parser->pos, parser->syntax->param_marker->str,
                parser->bytes_at_pos) == 0;

}


void add_parameter(MY_PARSER *parser)
{
  uint offset= (uint)(parser->pos - GET_QUERY(parser->query));
  auto &ppos = parser->query->param_pos;

  // Reserve more elements if needed
  if (ppos.capacity() == ppos.size())
    ppos.reserve(ppos.capacity() + 10);

  ppos.push_back(offset);
}


void step_char(MY_PARSER *parser)
{
  /* We must step forward at least one byte */
  parser->pos+= parser->bytes_at_pos ? parser->bytes_at_pos : 1;

  if (END_NOT_REACHED(parser))
  {
    get_ctype(parser);
  }
}


BOOL open_quote(MY_PARSER *parser, const MY_STRING * quote)
{
  if (quote != NULL)
  {
    parser->quote= quote;
    return TRUE;
  }

  return FALSE;
}


BOOL is_query_separator(MY_PARSER *parser)
{
  int i;

  for (i=0; i < sizeof(parser->syntax->query_sep)/sizeof(MY_STRING); ++i)
  {
    if (parser_compare(parser, &parser->syntax->query_sep[i]))
    {
      parser->pos+= parser->syntax->query_sep[i].bytes;
      get_ctype(parser);

      return TRUE;
    }
  }

  return FALSE;
}


/* Perhaps it can be just int(failed/succeeded) */
BOOL tokenize(MY_PARSER *parser)
{
  /* TODO: token info should contain length of a token */

  skip_spaces(parser);
  /* 1st token - otherwise we lose it if it is on 0 position without spaces
     ahead of it */
  add_token(parser);

  while(END_NOT_REACHED(parser))
  {
    if (parser->quote)
    {
      parser->query->last_char= find_closing_quote(parser);
      /*assert(parser->last_char!=NULL); /* no closing quote? */

      CLOSE_QUOTE(parser);
      /* find_closing_quote puts cursor after the closing quote
         thus we need to "continue" and not to lose that character */
      continue;
    }
    else
    {
      if (IS_SPACE(parser))
      {
        step_char(parser);

        if (skip_spaces(parser))
        {
          continue;
        }

        /* adding token after spaces */
        add_token(parser);
      }

      /* is_query_separator moves position to the 1st char of the next query */
      if (is_query_separator(parser))
      {
        skip_spaces(parser);
        add_token(parser);

        continue;
      }

      parser->query->last_char= parser->pos;

      if (open_quote(parser, is_quote(parser)))
      {
        /* Separate token for a quote (mind select"a")*/
        add_token(parser);
      }
      else if (is_comment(parser))
      {
        skip_comment(parser);
        continue;
      }
      else if (is_param_marker(parser))
      {
        add_parameter(parser);
      }
    }

    step_char(parser);
  }

  return FALSE;
}


/* Returns TRUE if the rule has succeded and type has been identified */
static
BOOL process_rule(MY_PARSER *parser, const QUERY_TYPE_RESOLVING *rule)
{
  uint i;
  const char *token;

  for (i= rule->pos_from;
       i <= myodbc_min(rule->pos_thru > 0 ? rule->pos_thru : rule->pos_from,
                      parser->query->token_count() - 1);
       ++i)
  {
    token = parser->query->get_token(i);

    if (parser->pos && case_compare(parser->query, token, rule->keyword))
    {
      if (rule->and_rule)
      {
        return process_rule(parser, rule->and_rule);
      }
      else
      {
        parser->query->query_type= rule->query_type;
        return TRUE;
      }
    }
  }

  if (rule->or_rule)
  {
    return process_rule(parser, rule->or_rule);
  }

  return FALSE;
}


QUERY_TYPE_ENUM detect_query_type(MY_PARSER *parser,
                                  const QUERY_TYPE_RESOLVING *rule)
{
  while (rule->keyword != NULL)
  {
    if (process_rule(parser, rule))
    {
      return parser->query->query_type;
    }

    ++rule;
  }

  return myqtOther;
}

BOOL parser_compare(MY_PARSER *parser, const MY_STRING *str)
{
  if (str && BYTES_LEFT(parser->query, parser->pos) >= (int)str->bytes)
  {
    return memcmp(parser->pos, str->str, str->bytes) == 0;
  }

  return FALSE;
}


BOOL case_compare(MY_PARSED_QUERY *pq, const char *pos, const MY_STRING *str)
{
  if (str && BYTES_LEFT(pq, pos) >= (int)str->bytes)
  {
    /* to check: if myodbc_casecmp suits */
    return myodbc_casecmp(pos, str->str, str->bytes) == 0;
  }

  return FALSE;
}


BOOL parse(MY_PARSED_QUERY *pq)
{
  MY_PARSER parser;

  init_parser(&parser, pq);

  if (tokenize(&parser))
  {
    return TRUE;
  }

  /* If the query is embrased in curly braces - we just need to remove them
   or server won't understand us */
  remove_braces(&parser);

  detect_query_type(&parser, rule);

  return FALSE;
}


/* Removes qurly braces off embraced query. Query has to be parsed
   Returns TRUE if braces were removed */
BOOL remove_braces(MY_PARSER *parser)
{
  /* To remove brace we need to parse the query to the end anyway */
  /* parse(parser);*/

  /* TODO: multibyte case */
  if (parser->query->token2.size() > 0)
  {
    // Buffer in std::string will be modified
    char *token = (char*)parser->query->get_token(0);

    /* TODO: what about batch of queries - do we need to care? */
    /* only doing our unthankful job if we have both opening and closing braces
       on 1st ans last position.*/
    if (token && token[0] == parser->syntax->odbc_escape_open->str[0]
      && parser->query->last_char
      && *parser->query->last_char == parser->syntax->odbc_escape_close->str[0])
    {
      token[0]= ' ';
      const_cast<char*>(parser->query->last_char)[0] = ' ';

      parser->pos= token;

      get_ctype(parser);

      /* If next character after opening brace is space - then we have erased
         1st token and need to delete it */
      if (IS_SPACE(parser))
      {
        parser->query->token2.erase(parser->query->token2.begin());
      }

      /* If we had "{}" - we would have erase the only token */
      if (parser->query->token_count())
      {
        if (parser->query->last_char ==
            parser->query->get_token((uint)parser->query->token_count() - 1))
        {
          parser->query->token2.pop_back();
        }
      }

      /* Not the last char form now on */
      parser->query->last_char= NULL;

      return TRUE;
    }
  }

  return FALSE;
}
