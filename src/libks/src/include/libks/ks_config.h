/*
 * Copyright (c) 2018-2023 SignalWire, Inc
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * @defgroup config Config File Parser
 * @ingroup config
 * This module implements a basic interface and file format parser
 * 
 * <pre>
 *
 * EXAMPLE 
 * 
 * [category1]
 * var1 => val1
 * var2 => val2
 * \# lines that begin with \# are comments
 * \#var3 => val3
 * </pre>
 * @{
 */

#ifndef KS_CONFIG_H
#define KS_CONFIG_H

KS_BEGIN_EXTERN_C

#include "ks.h"

#define KS_URL_SEPARATOR "://"

#ifdef WIN32
#define KS_PATH_SEPARATOR "\\"
#ifndef KS_CONFIG_DIR
#define KS_CONFIG_DIR "c:\\openks"
#endif
#define ks_is_file_path(file) (*(file +1) == ':' || *file == '/' || strstr(file, SWITCH_URL_SEPARATOR))
#else
#define KS_PATH_SEPARATOR "/"
#ifndef KS_CONFIG_DIR
#define KS_CONFIG_DIR "/etc/openks"
#endif
#define ks_is_file_path(file) ((*file == '/') || strstr(file, SWITCH_URL_SEPARATOR))
#endif

/*!
  \brief Evaluate the truthfullness of a string expression
  \param expr a string expression
  \return true or false 
*/
#define ks_true(expr)\
(expr && ( !strcasecmp(expr, "yes") ||\
!strcasecmp(expr, "on") ||\
!strcasecmp(expr, "true") ||\
!strcasecmp(expr, "enabled") ||\
!strcasecmp(expr, "active") ||\
!strcasecmp(expr, "allow") ||\
atoi(expr))) ? 1 : 0

/*!
  \brief Evaluate the falsefullness of a string expression
  \param expr a string expression
  \return true or false 
*/
#define ks_false(expr)\
(expr && ( !strcasecmp(expr, "no") ||\
!strcasecmp(expr, "off") ||\
!strcasecmp(expr, "false") ||\
!strcasecmp(expr, "disabled") ||\
!strcasecmp(expr, "inactive") ||\
!strcasecmp(expr, "disallow") ||\
!atoi(expr))) ? 1 : 0

typedef struct ks_config ks_config_t;

/*! \brief A simple file handle representing an open configuration file **/
	struct ks_config {
		/*! FILE stream buffer to the opened file */
		FILE *file;
		/*! path to the file */
		char path[512];
		/*! current category */
		char category[256];
		/*! current section */
		char section[256];
		/*! buffer of current line being read */
		char buf[1024];
		/*! current line number in file */
		int lineno;
		/*! current category number in file */
		int catno;
		/*! current section number in file */
		int sectno;

		int lockto;
	};

/*!
  \brief Open a configuration file
  \param cfg (ks_config_t *) config handle to use
  \param file_path path to the file
  \return 1 (true) on success 0 (false) on failure
*/
	          KS_DECLARE(int) ks_config_open_file(ks_config_t *cfg, const char *file_path);

/*!
  \brief Close a previously opened configuration file
  \param cfg (ks_config_t *) config handle to use
*/
	     KS_DECLARE(void) ks_config_close_file(ks_config_t *cfg);

/*!
  \brief Retrieve next name/value pair from configuration file
  \param cfg (ks_config_t *) config handle to use
  \param var pointer to aim at the new variable name
  \param val pointer to aim at the new value
*/
	            KS_DECLARE(int) ks_config_next_pair(ks_config_t *cfg, char **var, char **val);

/*!
  \brief Retrieve the CAS bits from a configuration string value
  \param strvalue pointer to the configuration string value (expected to be in format whatever:xxxx)
  \param outbits pointer to aim at the CAS bits
*/
	     KS_DECLARE(int) ks_config_get_cas_bits(char *strvalue, unsigned char *outbits);


/** @} */

KS_END_EXTERN_C
#endif							/* defined(KS_CONFIG_H) */
/* For Emacs:
 * Local Variables:
 * mode:c
 * indent-tabs-mode:t
 * tab-width:4
 * c-basic-offset:4
 * End:
 * For VIM:
 * vim:set softtabstop=4 shiftwidth=4 tabstop=4 noet:
 */
