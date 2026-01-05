# This function will do the check_library_exists call, and if successful add the definition
# to the target inline.
function(ksutil_target_check_library_exists_hdr TARGET SCOPE LIB API_NAME HEADER_NAME DEFINITION)
	check_library_exists(${LIB} ${API_NAME} ${HEADER_NAME} ${DEFINITION})
	if (${DEFINITION})
		target_compile_definitions(${TARGET} ${SCOPE} "-D${DEFINITION}=1")
	endif()
endfunction()

# This function will do the check_library_exists call, and if successful add the definition
# to the target inline.
function(ksutil_target_check_library_exists TARGET SCOPE LIB API_NAME DEFINITION)
	check_library_exists(${LIB} ${API_NAME} "" ${DEFINITION})
	if (${DEFINITION})
		target_compile_definitions(${TARGET} ${SCOPE} "-D${DEFINITION}=1")
	endif()
endfunction()

# This function will do the check_function_exists call, and if successful add the definition
# to the target inline.
function(ksutil_target_check_function_exists TARGET SCOPE API_NAME DEFINITION)
	check_function_exists(${API_NAME} ${DEFINITION})
	if (${DEFINITION})
		target_compile_definitions(${TARGET} ${SCOPE} "-D${DEFINITION}=1")
	endif()
endfunction()


# This function will do the check_include_file call, and if successful add the definition
# to the target inline.
function(ksutil_target_check_include_file TARGET SCOPE HEADER DEFINITION)
	check_include_file(${HEADER} ${DEFINITION})
	if (${DEFINITION})
		target_compile_definitions(${TARGET} ${SCOPE} "-D${DEFINITION}=1")
	endif()
endfunction()

# This macro helps with adding a unit test with the tap library. The unit test
# name must also exist as a source file e.t. testcon will have testcon.c and tap.c.
macro(ksutil_add_test name)
	add_executable("test${name}" "test${name}.c" tap.c)
	target_link_libraries("test${name}" ks2)
	target_include_directories("test${name}" PUBLIC ${CMAKE_CURRENT_LIST_DIR})
	add_test("test${name}" "test${name}")

	# When debugging on windows, the cwd will be the binary dir (where the config files are)
	set_target_properties("test${name}" PROPERTIES VS_DEBUGGER_WORKING_DIRECTORY ${CMAKE_BINARY_DIR})
endmacro()

macro(ksutil_add_test_extra name extra_deps extra_libs)
	add_executable("test${name}" "test${name}.c" tap.c ${extra_deps})
	target_link_libraries("test${name}" ks2 ${extra_libs})
	target_include_directories("test${name}" PUBLIC ${CMAKE_CURRENT_LIST_DIR})
	add_test("test${name}" "test${name}")

	# When debugging on windows, the cwd will be the binary dir (where the config files are)
	set_target_properties("test${name}" PROPERTIES VS_DEBUGGER_WORKING_DIRECTORY ${CMAKE_BINARY_DIR})
endmacro()

# Adds a submodule subdirectory to be included in cmake. Will automatically
# init it if its empty, and can optionally checkout any tag/branch if specified.
macro(ksutil_add_submodule_directory PATH)
	set(oneValueArgs TAG RENAME)

	cmake_parse_arguments(OPTS "${options}" "${oneValueArgs}" "" ${ARGN})

	set(__MOD_UPDATED 0)

    # See if the dir is empty or not
	file(GLOB RESULT ${PATH}/*)
    list(LENGTH RESULT RES_LEN)
    if(RES_LEN EQUAL 0)
        # Empty so update it
		message("Adding submodule dir ${CMAKE_CURRENT_LIST_DIR}/${PATH}")
		execute_process(COMMAND git submodule update --init ${PATH} WORKING_DIRECTORY ${CMAKE_CURRENT_LIST_DIR} RESULT_VARIABLE CLONE_RESULT)
		if (CLONE_RESULS GREATER 0)
			message("Failed to update submodule path ${PATH}" FATAL)
		endif()
		set(__MOD_UPDATED 1)
    endif()

	# And check out the right tag
	if (OPTS_TAG AND ${__MOD_UPDATED} EQUAL 1)
		message("Checking out tag ${OPTS_TAG} for submodule dir ${CMAKE_CURRENT_LIST_DIR}/${PATH}")
		execute_process(COMMAND git checkout ${OPTS_TAG} WORKING_DIRECTORY ${CMAKE_CURRENT_LIST_DIR}/${PATH} RESULT_VARIABLE CHECKOUT_RESULT)
		if (CLONE_RESULS GREATER 0)
			message("Failed to checkout ${OPTS_TAG} for submodule ${PATH}" FATAL)
		endif()
	endif()

	message("Adding subdirectory ${PATH}")
	add_subdirectory(${PATH})
endmacro()

# This sets up the core build config and platform definitions in the cmake global scope
# used everywhere to setup basic setup for all signalwire native projects.
macro(ksutil_setup_platform)
	if (APPLE)
		message("Platform is mac")
		set(KS_PLAT_MAC 1 CACHE INTERNAL "Platform definition" FORCE)
		add_compile_options("$<$<CONFIG:Release>:-O2>")
		add_compile_options("$<$<CONFIG:Release>:-g>")
		add_compile_options("$<$<CONFIG:Release>:-Wno-parentheses>")
		add_compile_options("$<$<CONFIG:Release>:-Wno-pointer-sign>")
		add_compile_options("$<$<CONFIG:Release>:-Wno-switch>")

		add_compile_options("$<$<CONFIG:Debug>:-O0>")
		add_compile_options("$<$<CONFIG:Debug>:-g>")
		add_compile_options("$<$<CONFIG:Debug>:-DKS_BUILD_DEBUG=1>")
		add_compile_options("$<$<CONFIG:Debug>:-Wno-parentheses>")
		add_compile_options("$<$<CONFIG:Debug>:-Wno-pointer-sign>")
		add_compile_options("$<$<CONFIG:Debug>:-Wno-switch>")

		set(CMAKE_POSITION_INDEPENDENT_CODE YES)
		add_definitions("-DKS_PLAT_MAC=1")
		set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -std=c11")
	elseif (WIN32)
		message("Platform is windows")
		set(KS_PLAT_WIN 1 CACHE INTERNAL "Platform definition" FORCE)
		add_definitions("-D_WIN32_WINNT=0x0600")
		add_definitions("-D_WINSOCK_DEPRECATED_NO_WARNINGS=1")
		add_definitions("-DWIN32_LEAN_AND_MEAN=1")
		add_definitions("-DKS_PLAT_WIN=1")
		add_definitions("-DNOMAXMIN=1")
		add_definitions("-D_CRT_SECURE_NO_WARNINGS=1")
		add_definitions("/bigobj")

		add_compile_options("$<$<CONFIG:Debug>:-DKS_BUILD_DEBUG=1>")

		set(CMAKE_ARCHIVE_OUTPUT_DIRECTORY ${CMAKE_BINARY_DIR})
		set(CMAKE_LIBRARY_OUTPUT_DIRECTORY ${CMAKE_BINARY_DIR})
		set(CMAKE_RUNTIME_OUTPUT_DIRECTORY ${CMAKE_BINARY_DIR})
	else()
		message("Platform is linux")
		set(KS_PLAT_LIN 1 CACHE INTERNAL "Platform definition" FORCE)
		set(CMAKE_POSITION_INDEPENDENT_CODE YES)

		add_compile_options("$<$<CONFIG:Release>:-O2>")
		add_compile_options("$<$<CONFIG:Release>:-g>")

		add_compile_options("$<$<CONFIG:Debug>:-O0>")
		add_compile_options("$<$<CONFIG:Debug>:-g>")
		add_compile_options("$<$<CONFIG:Debug>:-DKS_BUILD_DEBUG=1>")

		add_compile_options("$<$<CONFIG:Sanitize>:-O0>")
		add_compile_options("$<$<CONFIG:Sanitize>:-g>")
		add_compile_options("$<$<CONFIG:Sanitize>:-fsanitize=address>")
		add_compile_options("$<$<CONFIG:Sanitize>:-DKS_BUILD_DEBUG=1>")

		add_definitions("-DKS_PLAT_LIN=1")
		set(CMAKE_C_FLAGS "${CMAKE_C_FLAGS} -std=c11")

		# Select a default install prefix of /usr
		if (NOT CMAKE_INSTALL_PREFIX)
			set(CMAKE_INSTALL_PREFIX "/usr" CACHE INTERNAL "Prefix prepended to install directories" FORCE)
			set(CMAKE_PREFIX_PATH "/usr" CACHE INTERNAL "Prefix search path" FORCE)
		endif()
	endif()

	# Default to debug if not specified
	if (NOT CMAKE_BUILD_TYPE)
		set(CMAKE_BUILD_TYPE "Debug" CACHE INTERNAL "Build config setting" FORCE)
	endif()

	message("Build type: ${CMAKE_BUILD_TYPE} CXX Flags: ${CMAKE_CXX_FLAGS_SANITIZE}")

	message("Install prefix: ${CMAKE_INSTALL_PREFIX}")
endmacro()
