/*
 * Copyright (c) 2025 SignalWire, Inc
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

#ifdef HAVE_JSON_SCHEMA_VALIDATOR

#include <memory>
#include <string>
#include <sstream>
#include <nlohmann/json.hpp>
#include <nlohmann/json-schema.hpp>

#include "libks/ks.h"

static const char* JSON_META_SCHEMA_DRAFT_07 = R"({
    "$schema": "http://json-schema.org/draft-07/schema#",
    "$id": "http://json-schema.org/draft-07/schema#",
    "title": "Core schema meta-schema",
    "definitions": {
        "schemaArray": {
            "type": "array",
            "minItems": 1,
            "items": { "$ref": "#" }
        },
        "nonNegativeInteger": {
            "type": "integer",
            "minimum": 0
        },
        "nonNegativeIntegerDefault0": {
            "allOf": [
                { "$ref": "#/definitions/nonNegativeInteger" },
                { "default": 0 }
            ]
        },
        "simpleTypes": {
            "enum": [
                "array",
                "boolean",
                "integer",
                "null",
                "number",
                "object",
                "string"
            ]
        },
        "stringArray": {
            "type": "array",
            "items": { "type": "string" },
            "uniqueItems": true,
            "default": []
        }
    },
    "type": ["object", "boolean"],
    "properties": {
        "$id": {
            "type": "string",
            "format": "uri-reference"
        },
        "$schema": {
            "type": "string",
            "format": "uri"
        },
        "$ref": {
            "type": "string",
            "format": "uri-reference"
        },
        "$comment": {
            "type": "string"
        },
        "title": {
            "type": "string"
        },
        "description": {
            "type": "string"
        },
        "default": true,
        "readOnly": {
            "type": "boolean",
            "default": false
        },
        "writeOnly": {
            "type": "boolean",
            "default": false
        },
        "examples": {
            "type": "array",
            "items": true
        },
        "multipleOf": {
            "type": "number",
            "exclusiveMinimum": 0
        },
        "maximum": {
            "type": "number"
        },
        "exclusiveMaximum": {
            "type": "number"
        },
        "minimum": {
            "type": "number"
        },
        "exclusiveMinimum": {
            "type": "number"
        },
        "maxLength": { "$ref": "#/definitions/nonNegativeInteger" },
        "minLength": { "$ref": "#/definitions/nonNegativeIntegerDefault0" },
        "pattern": {
            "type": "string",
            "format": "regex"
        },
        "additionalItems": { "$ref": "#" },
        "items": {
            "anyOf": [
                { "$ref": "#" },
                { "$ref": "#/definitions/schemaArray" }
            ],
            "default": true
        },
        "maxItems": { "$ref": "#/definitions/nonNegativeInteger" },
        "minItems": { "$ref": "#/definitions/nonNegativeIntegerDefault0" },
        "uniqueItems": {
            "type": "boolean",
            "default": false
        },
        "contains": { "$ref": "#" },
        "maxProperties": { "$ref": "#/definitions/nonNegativeInteger" },
        "minProperties": { "$ref": "#/definitions/nonNegativeIntegerDefault0" },
        "required": { "$ref": "#/definitions/stringArray" },
        "additionalProperties": { "$ref": "#" },
        "definitions": {
            "type": "object",
            "additionalProperties": { "$ref": "#" },
            "default": {}
        },
        "properties": {
            "type": "object",
            "additionalProperties": { "$ref": "#" },
            "default": {}
        },
        "patternProperties": {
            "type": "object",
            "additionalProperties": { "$ref": "#" },
            "propertyNames": { "format": "regex" },
            "default": {}
        },
        "dependencies": {
            "type": "object",
            "additionalProperties": {
                "anyOf": [
                    { "$ref": "#" },
                    { "$ref": "#/definitions/stringArray" }
                ]
            }
        },
        "propertyNames": { "$ref": "#" },
        "const": true,
        "enum": {
            "type": "array",
            "items": true,
            "minItems": 1,
            "uniqueItems": true
        },
        "type": {
            "anyOf": [
                { "$ref": "#/definitions/simpleTypes" },
                {
                    "type": "array",
                    "items": { "$ref": "#/definitions/simpleTypes" },
                    "minItems": 1,
                    "uniqueItems": true
                }
            ]
        },
        "format": { "type": "string" },
        "contentMediaType": { "type": "string" },
        "contentEncoding": { "type": "string" },
        "if": { "$ref": "#" },
        "then": { "$ref": "#" },
        "else": { "$ref": "#" },
        "allOf": { "$ref": "#/definitions/schemaArray" },
        "anyOf": { "$ref": "#/definitions/schemaArray" },
        "oneOf": { "$ref": "#/definitions/schemaArray" },
        "not": { "$ref": "#" }
    },
    "default": true
})";

struct ks_json_schema {
	std::unique_ptr<nlohmann::json_schema::json_validator> validator;
};

// Global cached meta-schema validator
static ks_json_schema_t *g_meta_schema_validator = nullptr;
static bool g_json_schema_initialized = false;

// Forward declaration of internal function
static ks_json_schema_status_t ks_json_schema_create_internal(const char *schema_json, ks_json_schema_t **schema, ks_json_schema_error_t **errors, bool validate_meta_schema);

// URI-reference format checker based on RFC 3986 Section 4
static bool is_valid_uri_reference(const std::string &value)
{
	// URI-reference = URI / relative-ref
	// URI = scheme ":" hier-part [ "?" query ] [ "#" fragment ]
	// relative-ref = relative-part [ "?" query ] [ "#" fragment ]

	if (value.empty()) {
		return true; // Empty string is a valid relative reference
	}

	// Basic character validation - URI-references should only contain:
	// - Unreserved characters: ALPHA / DIGIT / "-" / "." / "_" / "~"
	// - Reserved characters: ":" / "/" / "?" / "#" / "[" / "]" / "@" / "!" / "$" / "&" / "'" / "(" / ")" / "*" / "+" / "," / ";" / "="
	// - Percent-encoded characters: "%" HEXDIG HEXDIG

	for (size_t i = 0; i < value.length(); ++i) {
		char c = value[i];

		// Check for percent-encoded sequences
		if (c == '%') {
			if (i + 2 >= value.length()) {
				return false; // Incomplete percent encoding
			}
			char h1 = value[i + 1];
			char h2 = value[i + 2];
			if (!std::isxdigit(h1) || !std::isxdigit(h2)) {
				return false; // Invalid hex digits
			}
			i += 2; // Skip the hex digits
			continue;
		}

		// Check for valid URI characters
		if (std::isalnum(c) ||
			c == '-' || c == '.' || c == '_' || c == '~' || // unreserved
			c == ':' || c == '/' || c == '?' || c == '#' || c == '[' || c == ']' || c == '@' || // gen-delims
			c == '!' || c == '$' || c == '&' || c == '\'' || c == '(' || c == ')' ||
			c == '*' || c == '+' || c == ',' || c == ';' || c == '=') { // sub-delims
			continue;
		}

		return false; // Invalid character
	}

	// Additional validation: check for proper structure
	// Look for scheme (if present): scheme = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
	size_t colon_pos = value.find(':');
	if (colon_pos != std::string::npos) {
		// If there's a colon, validate the scheme part
		std::string scheme = value.substr(0, colon_pos);
		if (scheme.empty() || !std::isalpha(scheme[0])) {
			// Could be a relative reference with colon in path, which is valid
			// Only reject if it looks like an invalid scheme
			bool has_slash_before_colon = false;
			for (size_t j = 0; j < colon_pos; ++j) {
				if (value[j] == '/') {
					has_slash_before_colon = true;
					break;
				}
			}
			if (!has_slash_before_colon) {
				// This looks like a scheme, validate it
				for (char sc : scheme) {
					if (!std::isalnum(sc) && sc != '+' && sc != '-' && sc != '.') {
						return false;
					}
				}
			}
		}
	}

	return true;
}

static void schema_format_checker(const std::string &format, const std::string &value)
{
	if (format == "uri-reference") {
		if (!is_valid_uri_reference(value)) {
			throw std::invalid_argument("Invalid URI reference format");
		}
	} else {
		// Use default format checker for other formats
		nlohmann::json_schema::default_string_format_check(format, value);
	}
}

// Initialize JSON schema subsystem - internal function
static void ks_json_schema_init_internal(void)
{
	if (g_json_schema_initialized) {
		return; // Already initialized
	}

	// Create and cache the meta-schema validator
	ks_json_schema_error_t *errors = nullptr;
	ks_json_schema_status_t status = ks_json_schema_create_internal(JSON_META_SCHEMA_DRAFT_07, &g_meta_schema_validator, &errors, false);

	if (status != KS_JSON_SCHEMA_STATUS_SUCCESS) {
		// This should never happen with a valid meta-schema, but handle gracefully
		if (errors) {
			ks_json_schema_error_free(&errors);
		}
		g_meta_schema_validator = nullptr;
	}

	g_json_schema_initialized = true;
}

// Cleanup JSON schema subsystem - internal function
static void ks_json_schema_shutdown_internal(void)
{
	if (!g_json_schema_initialized) {
		return; // Not initialized
	}

	if (g_meta_schema_validator) {
		ks_json_schema_destroy(&g_meta_schema_validator);
		g_meta_schema_validator = nullptr;
	}

	g_json_schema_initialized = false;
}

// Internal function to create schema without meta-schema validation (to avoid recursion)
static ks_json_schema_status_t ks_json_schema_create_internal(const char *schema_json, ks_json_schema_t **schema, ks_json_schema_error_t **errors, bool validate_meta_schema)
{
	if (!schema_json || !schema) {
		return KS_JSON_SCHEMA_STATUS_INVALID_PARAM;
	}

	*schema = nullptr;
	if (errors) {
		*errors = nullptr;
	}

	try {
		// Parse the schema JSON
		nlohmann::json schema_json_obj = nlohmann::json::parse(schema_json);

		// Create validator with remote reference support
		auto ks_schema = std::make_unique<ks_json_schema>();
		ks_schema->validator = std::make_unique<nlohmann::json_schema::json_validator>(nullptr, schema_format_checker);

		// Set schema with remote reference loading enabled - this will validate the schema
		ks_schema->validator->set_root_schema(schema_json_obj);

		// Validate against meta-schema only if requested (to avoid recursion)
		if (validate_meta_schema) {
			// Check if JSON schema subsystem is properly initialized
			if (!g_json_schema_initialized || !g_meta_schema_validator) {
				// libks requires ks_init() to be called for proper operation
				return KS_JSON_SCHEMA_STATUS_UNAVAILABLE;
			}

			// Use cached meta-schema validator
			ks_json_schema_error_t *validation_errors = nullptr;
			ks_json_schema_status_t validation_status = ks_json_schema_validate_string(g_meta_schema_validator, schema_json, &validation_errors);

			if (validation_status != KS_JSON_SCHEMA_STATUS_SUCCESS) {
				if (errors) {
					// Prefix error messages to indicate they're schema validation errors
					ks_json_schema_error_t *current = validation_errors;
					while (current) {
						if (current->message) {
							std::string new_msg = "Schema validation error: " + std::string(current->message);
							free(current->message);
							current->message = strdup(new_msg.c_str());
						}
						current = current->next;
					}
					*errors = validation_errors;
				} else {
					ks_json_schema_error_free(&validation_errors);
				}
				return KS_JSON_SCHEMA_STATUS_INVALID_SCHEMA;
			}
		}

		*schema = ks_schema.release();
		return KS_JSON_SCHEMA_STATUS_SUCCESS;
	} catch (const nlohmann::json::parse_error& e) {
		if (errors) {
			auto error = static_cast<ks_json_schema_error_t *>(malloc(sizeof(ks_json_schema_error_t)));
			if (error) {
				std::string msg = "JSON parse error in schema: " + std::string(e.what());
				error->message = strdup(msg.c_str());
				error->path = strdup("");
				error->next = nullptr;
				*errors = error;
			}
		}
		return KS_JSON_SCHEMA_STATUS_INVALID_SCHEMA;
	} catch (const std::exception& e) {
		if (errors) {
			auto error = static_cast<ks_json_schema_error_t *>(malloc(sizeof(ks_json_schema_error_t)));
			if (error) {
				error->message = strdup(e.what());
				error->path = strdup("");
				error->next = nullptr;
				*errors = error;
			}
		}
		return KS_JSON_SCHEMA_STATUS_INVALID_SCHEMA;
	}
}

class ks_error_handler : public nlohmann::json_schema::basic_error_handler
{
public:
	struct validation_error {
		std::string message;
		std::string path;
	};

	std::vector<validation_error> errors;
	static const size_t MAX_ERRORS = 5;

	void error(const nlohmann::json::json_pointer &pointer, const nlohmann::json &instance, const std::string &message) override
	{
		// Stop collecting errors after reaching the limit
		if (errors.size() >= MAX_ERRORS) {
			return;
		}

		validation_error err;
		err.message = message;
		err.path = pointer.to_string();
		errors.push_back(err);

		// Add a truncation message if we hit the limit
		if (errors.size() == MAX_ERRORS) {
			validation_error truncated_err;
			truncated_err.message = "Maximum error limit reached. Additional errors may exist.";
			truncated_err.path = "";
			errors.push_back(truncated_err);
		}

		basic_error_handler::error(pointer, instance, message);
	}
};

#else

#include "libks/ks.h"

#endif

KS_BEGIN_EXTERN_C

KS_DECLARE(const char *) ks_json_schema_status_string(ks_json_schema_status_t status)
{
	switch (status) {
		case KS_JSON_SCHEMA_STATUS_SUCCESS:
			return "Success";
		case KS_JSON_SCHEMA_STATUS_UNAVAILABLE:
			return "JSON Schema validation not enabled";
		case KS_JSON_SCHEMA_STATUS_INVALID_SCHEMA:
			return "Invalid schema";
		case KS_JSON_SCHEMA_STATUS_INVALID_JSON:
			return "Invalid JSON";
		case KS_JSON_SCHEMA_STATUS_VALIDATION_FAILED:
			return "Validation failed";
		case KS_JSON_SCHEMA_STATUS_MEMORY_ERROR:
			return "Memory error";
		case KS_JSON_SCHEMA_STATUS_INVALID_PARAM:
			return "Invalid parameter";
		default:
			return "Unknown error";
	}
}

#ifdef HAVE_JSON_SCHEMA_VALIDATOR

KS_DECLARE(void) ks_json_schema_init(void)
{
	ks_json_schema_init_internal();
}

KS_DECLARE(void) ks_json_schema_shutdown(void)
{
	ks_json_schema_shutdown_internal();
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_create(const char *schema_json, ks_json_schema_t **schema, ks_json_schema_error_t **errors)
{
	// Call internal function with meta-schema validation enabled
	return ks_json_schema_create_internal(schema_json, schema, errors, true);
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_create_from_json(ks_json_t *schema_json, ks_json_schema_t **schema, ks_json_schema_error_t **errors)
{
	if (!schema_json || !schema) {
		return KS_JSON_SCHEMA_STATUS_INVALID_PARAM;
	}

	// Convert JSON object to string and delegate to ks_json_schema_create
	char *schema_string = ks_json_print_unformatted(schema_json);
	if (!schema_string) {
		if (errors) {
			auto error = static_cast<ks_json_schema_error_t *>(malloc(sizeof(ks_json_schema_error_t)));
			if (error) {
				error->message = strdup("Failed to serialize JSON object to string");
				error->path = strdup("");
				error->next = nullptr;
				*errors = error;
			}
		}
		return KS_JSON_SCHEMA_STATUS_INVALID_SCHEMA;
	}

	// Use the main schema creation function
	ks_json_schema_status_t result = ks_json_schema_create(schema_string, schema, errors);

	// Clean up the allocated string
	free(schema_string);

	return result;
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_validate_string(ks_json_schema_t *schema, const char *json_string, ks_json_schema_error_t **errors)
{
	if (!schema || !json_string) {
		return KS_JSON_SCHEMA_STATUS_INVALID_PARAM;
	}

	if (errors) {
		*errors = nullptr;
	}

	try {
		// Parse the JSON string
		nlohmann::json json_doc = nlohmann::json::parse(json_string);

		// Create error handler to collect detailed validation errors
		ks_error_handler error_handler;

		// Validate using json-schema-validator with error handler
		schema->validator->validate(json_doc, error_handler);

		// Check if validation failed
		if (!error_handler.errors.empty()) {
			if (errors) {
				ks_json_schema_error_t *error_list = nullptr;
				ks_json_schema_error_t *last_error = nullptr;

				for (const auto& validation_error : error_handler.errors) {
					auto error = static_cast<ks_json_schema_error_t *>(malloc(sizeof(ks_json_schema_error_t)));
					if (!error) {
						ks_json_schema_error_free(&error_list);
						return KS_JSON_SCHEMA_STATUS_MEMORY_ERROR;
					}

					error->message = strdup(validation_error.message.c_str());
					error->path = strdup(validation_error.path.c_str());
					error->next = nullptr;

					if (!error->message || !error->path) {
						free(error->message);
						free(error->path);
						free(error);
						ks_json_schema_error_free(&error_list);
						return KS_JSON_SCHEMA_STATUS_MEMORY_ERROR;
					}

					if (last_error) {
						last_error->next = error;
					} else {
						error_list = error;
					}
					last_error = error;
				}

				*errors = error_list;
			}
			return KS_JSON_SCHEMA_STATUS_VALIDATION_FAILED;
		}

		return KS_JSON_SCHEMA_STATUS_SUCCESS;
	} catch (const nlohmann::json::parse_error& e) {
		if (errors) {
			auto error = static_cast<ks_json_schema_error_t *>(malloc(sizeof(ks_json_schema_error_t)));
			if (error) {
				std::string msg = "JSON parse error: " + std::string(e.what());
				error->message = strdup(msg.c_str());
				error->path = strdup("");
				error->next = nullptr;
				*errors = error;
			}
		}
		return KS_JSON_SCHEMA_STATUS_INVALID_JSON;
	} catch (const std::exception& e) {
		// Other validation errors
		if (errors) {
			auto error = static_cast<ks_json_schema_error_t *>(malloc(sizeof(ks_json_schema_error_t)));
			if (error) {
				error->message = strdup(e.what());
				error->path = strdup("");
				error->next = nullptr;
				*errors = error;
			}
		}
		return KS_JSON_SCHEMA_STATUS_VALIDATION_FAILED;
	}
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_validate_json(ks_json_schema_t *schema, ks_json_t *json, ks_json_schema_error_t **errors)
{
	if (!schema || !json) {
		return KS_JSON_SCHEMA_STATUS_INVALID_PARAM;
	}

	// Convert JSON object to string and delegate to ks_json_schema_validate_string
	char *json_string = ks_json_print_unformatted(json);
	if (!json_string) {
		if (errors) {
			auto error = static_cast<ks_json_schema_error_t *>(malloc(sizeof(ks_json_schema_error_t)));
			if (error) {
				error->message = strdup("Failed to serialize JSON object to string");
				error->path = strdup("");
				error->next = nullptr;
				*errors = error;
			}
		}
		return KS_JSON_SCHEMA_STATUS_INVALID_JSON;
	}

	// Use the main validation function
	ks_json_schema_status_t result = ks_json_schema_validate_string(schema, json_string, errors);

	// Clean up the allocated string
	free(json_string);

	return result;
}

KS_DECLARE(void) ks_json_schema_destroy(ks_json_schema_t **schema)
{
	if (!schema || !*schema) {
		return;
	}

	delete *schema;
	*schema = nullptr;
}

#else

KS_DECLARE(void) ks_json_schema_init(void)
{
	// No-op when JSON schema validation is disabled
}

KS_DECLARE(void) ks_json_schema_shutdown(void)
{
	// No-op when JSON schema validation is disabled
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_create(const char *schema_json, ks_json_schema_t **schema, ks_json_schema_error_t **errors)
{
	(void)schema_json;
	(void)schema;
	(void)errors;
	return KS_JSON_SCHEMA_STATUS_UNAVAILABLE;
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_create_from_json(ks_json_t *schema_json, ks_json_schema_t **schema, ks_json_schema_error_t **errors)
{
	(void)schema_json;
	(void)schema;
	(void)errors;
	return KS_JSON_SCHEMA_STATUS_UNAVAILABLE;
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_validate_string(ks_json_schema_t *schema, const char *json_string, ks_json_schema_error_t **errors)
{
	(void)schema;
	(void)json_string;
	(void)errors;
	return KS_JSON_SCHEMA_STATUS_UNAVAILABLE;
}

KS_DECLARE(ks_json_schema_status_t) ks_json_schema_validate_json(ks_json_schema_t *schema, ks_json_t *json, ks_json_schema_error_t **errors)
{
	(void)schema;
	(void)json;
	(void)errors;
	return KS_JSON_SCHEMA_STATUS_UNAVAILABLE;
}

KS_DECLARE(void) ks_json_schema_destroy(ks_json_schema_t **schema)
{
	(void)schema;
}

#endif

KS_DECLARE(void) ks_json_schema_error_free(ks_json_schema_error_t **errors)
{
	if (!errors || !*errors) {
		return;
	}

	ks_json_schema_error_t *current = *errors;
	while (current) {
		ks_json_schema_error_t *next = current->next;
		free(current->message);
		free(current->path);
		free(current);
		current = next;
	}

	*errors = nullptr;
}

KS_END_EXTERN_C

/* For Emacs:
 * Local Variables:
 * mode:c++
 * indent-tabs-mode:t
 * tab-width:4
 * c-basic-offset:4
 * End:
 * For VIM:
 * vim:set softtabstop=4 shiftwidth=4 tabstop=4 noet:
 */