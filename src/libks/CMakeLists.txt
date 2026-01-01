cmake_minimum_required(VERSION 3.7.2)

option(KS_DEBUG_MUTEX "Debug mutexes, only works on linux for now" OFF)
option(KS_DEBUG_SPINLOCK "Debug spinlocks" OFF)
option(KS_DEBUG_POOL "Track pool allocations and provide hooks for rendering them" OFF)
option(KS_DEBUG_HANDLE "Track pool allocations and provide hooks for rendering them" OFF)
option(KS_STATIC "Build ks statically" OFF)
option(WITH_KS_TEST "Enable KsTest c++ harness" OFF)
option(WITH_LIBBACKTRACE "Enables linking to libbacktrace as an external project, used in abort areas in libks" OFF)
option(WITH_JSON_VALIDATION "Enable JSON schema validation" OFF)
option(WITH_PACKAGING "Enable packaging" ON)


# Must include cotire before anything else for auto pch setup
#include(cmake/cotire.cmake)

# Load our common utility api and setup the platfomrm and build
include(cmake/ksutil.cmake)
ksutil_setup_platform()

# Find stuff we need for packaging on UNIX
if(KS_PLAT_LIN AND WITH_PACKAGING)
	find_package(Git)
	find_program(GZIP_CMD gzip)
	find_program(DATE_CMD date)
endif()

# Fixes build on older gcc, Debian Jessie
if(KS_PLAT_LIN)
	set(CMAKE_C_STANDARD 99)
endif()

if (KS_PLAT_WIN OR WITH_KS_TEST)
	# Setup hunter gate to auto grab our 3rd party dependencies
	# (may be included by our super project so check HUNTER_WIKI)
	if (NOT HUNTER_WIKI)
		include(cmake/HunterGate.cmake)
		HunterGate(
			URL "https://github.com/ruslo/hunter/archive/v0.20.39.tar.gz"
			SHA1 "b49c4b58e17c1473e822f8b21fcde92fee25791d"
		)
	endif()
endif()

# Declare our project, libks2
project(LibKS2 VERSION 2.0.8 LANGUAGES C CXX)
message("LibKS2 Version ${PROJECT_VERSION_MAJOR}.${PROJECT_VERSION_MINOR}")

# Set package version
set(CPACK_PACKAGE_VERSION_MAJOR ${PROJECT_VERSION_MAJOR})
set(CPACK_PACKAGE_VERSION_MINOR ${PROJECT_VERSION_MINOR})
set(CPACK_PACKAGE_VERSION_PATCH ${PROJECT_VERSION_PATCH})

# Fetch Catch2 testing framework
if (WITH_KS_KSTEST)
	hunter_add_package(Catch)
	find_package(Catch2 CONFIG REQUIRED)
endif()

# This setting defers the rpath generation to be set at install time, allowing
# us to run the apps from the build dir without installing (come install time
# the binary is re-linked with an rpath matching that of the install prefix)
set(SKIP_BUILD_RPATH TRUE)
set(CMAKE_INSTALL_RPATH "${CMAKE_INSTALL_PREFIX}/lib")

# Now detect a bunch of platform stuff
include(CheckFunctionExists)
include(CheckLibraryExists)
include(CheckIncludeFile)
include(TestBigEndian)

# Set package name
string(TOLOWER "${CMAKE_PROJECT_NAME}" PACKAGE_NAME)

# Set package version
set(PACKAGE_VERSION ${PROJECT_VERSION})

# Set package contact
set(CPACK_PACKAGE_CONTACT "support@signalwire.com")

# Detect architecture
if(NOT CPACK_SYSTEM_NAME)
	set(CPACK_SYSTEM_NAME ${CMAKE_SYSTEM_PROCESSOR})
	# sanity check
	if(CPACK_SYSTEM_NAME STREQUAL x86_64)
		set(CPACK_SYSTEM_NAME amd64)
	endif()
endif()

# Get UNIX type
if(KS_PLAT_LIN)
	find_file(DEBIAN_FOUND debian_version debconf.conf
		PATHS /etc
	)

	find_file(CENTOS_FOUND centos-release
		PATHS /etc
	)

	if(DEBIAN_FOUND)
		set(CMAKE_OS_NAME "Debian" CACHE STRING "Operating system name" FORCE)
	endif()

	if(CENTOS_FOUND)
		set(CMAKE_OS_NAME "Centos" CACHE STRING "Operating system name" FORCE)
	endif()
endif()

# Centos packaging
if("${CMAKE_OS_NAME}" STREQUAL "Centos" AND WITH_PACKAGING)

	# Enable component install
	set(CPACK_RPM_COMPONENT_INSTALL ON)

	# Find stuff we need for packaging on Centos
	find_program(ARCH_CMD arch)
	find_program(RPM_CMD rpm)

	# Set package architecture
	if(ARCH_CMD)
		execute_process(COMMAND ${ARCH_CMD} OUTPUT_VARIABLE CENTOS_ARCH OUTPUT_STRIP_TRAILING_WHITESPACE ERROR_QUIET)
		set(CPACK_RPM_RUNTIME_PACKAGE_ARCHITECTURE ${CENTOS_ARCH})
		set(CPACK_SYSTEM_NAME ${CENTOS_ARCH})
		unset(CENTOS_ARCH)
	endif()

	# Get RPM DIST tag, rpm --eval '%{dist}'
	if(RPM_CMD)
		execute_process(COMMAND ${RPM_CMD} --eval %{dist}
			OUTPUT_VARIABLE DIST_TAG
			OUTPUT_STRIP_TRAILING_WHITESPACE
			ERROR_STRIP_TRAILING_WHITESPACE)
	endif()

	# https://gitlab.kitware.com/cmake/cmake/issues/16517
	set(CPACK_COMPONENTS_ALL runtime)

	# Set package type to build
	set(CPACK_GENERATOR RPM)

	# Set RPM package name
	set(CPACK_RPM_RUNTIME_PACKAGE_NAME "libks2")

	# Set RPM package dependencies
	set(CPACK_RPM_RUNTIME_PACKAGE_REQUIRES "libuuid-devel")

	# Set version release from environment variable
	if (NOT "$ENV{PACKAGE_RELEASE}" STREQUAL "")
		set(CPACK_RPM_PACKAGE_RELEASE "$ENV{PACKAGE_RELEASE}")
	else()
		if(DATE_CMD)
			execute_process(COMMAND ${DATE_CMD} +%Y%m%d OUTPUT_VARIABLE DATE_YMD)
			set(CPACK_RPM_PACKAGE_RELEASE ${DATE_YMD})
		else()
			set(CPACK_RPM_PACKAGE_RELEASE "1")
		endif()
	endif()

	# Set RPM version
	set(CPACK_RPM_PACKAGE_VERSION ${PROJECT_VERSION})

	# Set OS DIST tag
	set(CPACK_RPM_PACKAGE_RELEASE_DIST ${DIST_TAG})

	# Set RPM filename
	set(CPACK_RPM_RUNTIME_FILE_NAME "${CPACK_RPM_RUNTIME_PACKAGE_NAME}-${PROJECT_VERSION}-${CPACK_RPM_PACKAGE_RELEASE}${DIST_TAG}.${CENTOS_ARCH}rpm")

	# Set license
	set(CPACK_RPM_PACKAGE_LICENSE "MIT")

	# Set RPM group
	set(CPACK_RPM_PACKAGE_GROUP "Development/Libraries/C and C++")

	# Set RPM vendor
	set(CPACK_RPM_PACKAGE_VENDOR "SignalWire")

	# Set RPM URL
	set(CPACK_RPM_RUNTIME_PACKAGE_URL "https://signalwire.com/")

	# Set RPM Description
	set(CPACK_RPM_PACKAGE_DESCRIPTION "SignalWire LibKitchenSink and Headers")

	# Set RPM package summary
	set(CPACK_RPM_PACKAGE_SUMMARY "SignalWire's LibKitchenSink")

	# Set auto dependency detection
	set(CPACK_RPM_PACKAGE_AUTOREQPROV "yes")

	# Create changelog
	string(TIMESTAMP NOW "%a %b %d %Y" UTC)
	FILE(WRITE ${CMAKE_CURRENT_LIST_DIR}/changelog "* ${NOW} ${CPACK_RPM_PACKAGE_VENDOR} <${CPACK_PACKAGE_CONTACT}> ${PROJECT_VERSION}-${CPACK_RPM_PACKAGE_RELEASE}${DIST_TAG}\n")
	FILE(APPEND ${CMAKE_CURRENT_LIST_DIR}/changelog "- autogenerated RPM package\n")
	unset(NOW)
	set(CPACK_RPM_CHANGELOG_FILE "${CMAKE_CURRENT_LIST_DIR}/changelog")

endif()

# Debian packaging
if("${CMAKE_OS_NAME}" STREQUAL "Debian" AND WITH_PACKAGING)

	# Find stuff we need for packaging on Debian
	find_program(DPKG_CMD dpkg)
	find_program(LSB_RELEASE_CMD lsb_release)

	# Get distro codename
	if(NOT LSB_RELEASE_CMD)
		message(WARNING "DEB Generator: Can't find lsb_release in your path. Setting DISTRO_CODENAME to unknown.")
		set(DISTRO_CODENAME unknown)
	else()
		execute_process(COMMAND ${LSB_RELEASE_CMD} -cs
			OUTPUT_VARIABLE DISTRO_CODENAME
			OUTPUT_STRIP_TRAILING_WHITESPACE)
	endif()

	# https://gitlab.kitware.com/cmake/cmake/issues/16517
	set(CPACK_COMPONENTS_ALL runtime)
	set(CPACK_DEB_COMPONENT_INSTALL "ON")

	# Set package name
	set(CPACK_DEBIAN_PACKAGE_NAME "libks2")

	# Abuse Cmake runtime component name
	set(CPACK_DEBIAN_RUNTIME_PACKAGE_NAME ${CPACK_DEBIAN_PACKAGE_NAME})

	# Set package type to build
	set(CPACK_GENERATOR DEB)

	# Set package maintainer
	set(CPACK_DEBIAN_PACKAGE_MAINTAINER ${CPACK_PACKAGE_CONTACT})

	# Manual package dependencies
	set(CPACK_DEBIAN_PACKAGE_DEPENDS "")

	# Detect package dependencies
	set(CPACK_DEBIAN_PACKAGE_SHLIBDEPS ON)
	set(CPACK_DEBIAN_PACKAGE_GENERATE_SHLIBS ON)
	set(CPACK_DEBIAN_PACKAGE_GENERATE_SHLIBS_POLICY ">=")

	# Set package description
	set(CPACK_DEBIAN_PACKAGE_DESCRIPTION "Foundational support for signalwire C products")

	# Set package section
	set(CPACK_DEBIAN_PACKAGE_SECTION "devel")

	# Set package priority
	set(CPACK_DEBIAN_PACKAGE_PRIORITY "optional")

	# Set package architecture
	if(DPKG_CMD)
		execute_process(COMMAND ${DPKG_CMD} --print-architecture OUTPUT_VARIABLE DEB_ARCH OUTPUT_STRIP_TRAILING_WHITESPACE ERROR_QUIET)
		set(CPACK_DEBIAN_PACKAGE_ARCHITECTURE ${DEB_ARCH})
		set(CPACK_SYSTEM_NAME ${DEB_ARCH})
		unset(DEB_ARCH)
	endif()

	# Enforce Debian policy permission rules
	set(CPACK_DEBIAN_PACKAGE_CONTROL_STRICT_PERMISSION ON)

	# Set a Debian compliant changelog footer
	if(DATE_CMD)
		execute_process(COMMAND ${DATE_CMD} -R OUTPUT_VARIABLE RFC2822_TIMESTAMP)
		# Two spaces between maintainer and timestamp is NOT a mistake
		set(CHANGELOG_FOOTER " -- ${CPACK_DEBIAN_PACKAGE_MAINTAINER}  ${RFC2822_TIMESTAMP}")
	endif()

	# Guess version release from environment variable
	# (usage of CPACK_DEBIAN_PACKAGE_RELEASE breaks ability to set CPACK_DEBIAN_PACKAGE_VERSION properly)
	if (NOT "$ENV{PACKAGE_RELEASE}" STREQUAL "")
		set(PACKAGE_RELEASE "$ENV{PACKAGE_RELEASE}")
	else()
		if(DATE_CMD)
			execute_process(COMMAND ${DATE_CMD} +%Y%m%d OUTPUT_VARIABLE DATE_YMD)
			set(PACKAGE_RELEASE ${DATE_YMD})
		else()
			set(PACKAGE_RELEASE "1")
		endif()
	endif()

	# Set package version
	set(CPACK_DEBIAN_PACKAGE_VERSION ${PROJECT_VERSION}-${PACKAGE_RELEASE}~${DISTRO_CODENAME})

	# Set debian file name format
	set(CPACK_DEBIAN_FILE_NAME "${PACKAGE_NAME}_${PROJECT_VERSION}-${PACKAGE_RELEASE}_${DISTRO_CODENAME}_${CPACK_DEBIAN_PACKAGE_ARCHITECTURE}.deb")

	# Set a Debian compliant changelog header
	set(CHANGELOG_HEADER "libks (${CPACK_DEBIAN_PACKAGE_VERSION}) ${DISTRO_CODENAME}\; urgency=${CPACK_DEBIAN_PACKAGE_PRIORITY}")

	# Generate a Debian compliant changelog
	if(GIT_FOUND AND GZIP_CMD AND DATE_CMD)
		# Get git log info
		message("Detecting last git tag to generate a Debian complian changelog.")
		execute_process(COMMAND ${GIT_EXECUTABLE} describe --tags --abbrev=0
			OUTPUT_VARIABLE LAST_TAG
			WORKING_DIRECTORY .
			OUTPUT_STRIP_TRAILING_WHITESPACE)

		# Commits count since last tag
		execute_process(COMMAND ${GIT_EXECUTABLE} rev-list ${LAST_TAG}.. --count
			OUTPUT_VARIABLE COMMITS_COUNT_SINCE_LAST_TAG
			WORKING_DIRECTORY .
			OUTPUT_STRIP_TRAILING_WHITESPACE)
		message("Found last git tag: ${LAST_TAG}")

		# Check if we have commits since last tag
		if (COMMITS_COUNT_SINCE_LAST_TAG STREQUAL "0")
			# if not, find second tag so we could have a changelog
			message("No commits since git tag '${LAST_TAG}' to generate a changelog, looking for a previous tag")
			execute_process(COMMAND ${GIT_EXECUTABLE} describe --tags --abbrev=0 ${LAST_TAG}^
				OUTPUT_VARIABLE LAST_TAG
				WORKING_DIRECTORY .
				OUTPUT_STRIP_TRAILING_WHITESPACE)
		endif()

		message("Generating a changelog using commits since git tag: ${LAST_TAG}")
		execute_process(COMMAND ${GIT_EXECUTABLE} log --no-merges --pretty=format:"%n  [%an]%n   * %s" ${LAST_TAG}..HEAD
			OUTPUT_VARIABLE CHANGELOG
			WORKING_DIRECTORY .
			OUTPUT_STRIP_TRAILING_WHITESPACE)
		string(REPLACE "\"" "" CHANGELOG ${CHANGELOG})

		# Create changelog
		file(WRITE changelog.Debian ${CHANGELOG_HEADER}\n${CHANGELOG}\n\n${CHANGELOG_FOOTER})
		execute_process(COMMAND ${GZIP_CMD} -f -9 -n changelog.Debian)

		# Install changelog
		install(FILES "${CMAKE_BINARY_DIR}/changelog.Debian.gz" COMPONENT "runtime" DESTINATION "share/doc/libks2")

		# Delete changelog related variables
		unset(CHANGELOG_HEADER)
		unset(CHANGELOG_FOOTER)
		unset(RFC2822_TIMESTAMP)
	else()
		message(WARNING "DEB Generator: Can't find git and/or gzip and/or date in your path. DEB packages will be missing changelog.Debian.gz.")
	endif()

endif()

if (WITH_PACKAGING)
	# Enable packaging module
	include(CPack)
endif()

# Include our libks cmake utils
include(cmake/ksutil.cmake)

# Load our source and header files with a glob
file(GLOB_RECURSE KsDeps [LIST_DIRECTORIES false]
	${CMAKE_CURRENT_LIST_DIR}/src/*.c
	${CMAKE_CURRENT_LIST_DIR}/src/*.cpp
	${CMAKE_CURRENT_LIST_DIR}/src/include/libks/*.h
	${CMAKE_CURRENT_LIST_DIR}/src/include/crypt/*.c
	${CMAKE_CURRENT_LIST_DIR}/src/include/crypt/*.h
)

if (KS_PLAT_WIN)
	source_group(TREE ${CMAKE_CURRENT_LIST_DIR} FILES ${KsDeps})
endif()

if (KS_STATIC)
	add_library(ks2 STATIC ${KsDeps})
	target_compile_definitions(ks2 PUBLIC -DKS_DECLARE_STATIC=1)
	set_target_properties(ks2 PROPERTIES VERSION ${PROJECT_VERSION})
else()
	add_library(ks2 SHARED ${KsDeps})
	set_target_properties(ks2 PROPERTIES SOVERSION ${PROJECT_VERSION_MAJOR})
endif()
set_target_properties(ks2 PROPERTIES DEBUG_POSTFIX "")

if (KS_PLAT_LIN AND "${CMAKE_BUILD_TYPE}" STREQUAL "Sanitize")
	target_link_libraries(ks2 PUBLIC asan ubsan)
endif()

# Use libbacktrace if requested
if (NOT KS_PLAT_WIN AND WITH_LIBBACKTRACE)
	include(cmake/AddLibBacktrace.cmake)
	target_link_libraries(ks2 PRIVATE LibBacktrace)
	target_compile_definitions(ks2 PUBLIC -DHAVE_LIBBACKTRACE=1)
endif()

if (NOT KS_PLAT_WIN)
	# Now auto detect our dependencies
	ksutil_target_check_library_exists_hdr(ks2 PUBLIC rt clock_gettime time.h HAVE_CLOCK_GETTIME)
	ksutil_target_check_library_exists_hdr(ks2 PUBLIC rt clock_getres time.h HAVE_CLOCK_GETRES)
	ksutil_target_check_library_exists_hdr(ks2 PUBLIC rt clock_nanosleep time.h HAVE_CLOCK_NANOSLEEP)
	ksutil_target_check_library_exists_hdr(ks2 PUBLIC rt strftime time.h HAVE_STRFTIME)
	ksutil_target_check_library_exists(ks2 PUBLIC c sched_setscheduler HAVE_SCHED_SETSCHEDULER)
	ksutil_target_check_library_exists(ks2 PUBLIC c malloc HAVE_MALLOC)
	ksutil_target_check_library_exists(ks2 PUBLIC c usleep HAVE_USLEEP)
	ksutil_target_check_function_exists(ks2 PUBLIC pthread_attr_setschedparam HAVE_PTHREAD_ATTR_SETSCHEDPARAM)
	ksutil_target_check_function_exists(ks2 PUBLIC memmem HAVE_MEMMEM)
	ksutil_target_check_include_file(ks2 PUBLIC stdlib.h HAVE_STDLIB_H)
	ksutil_target_check_include_file(ks2 PUBLIC sys/types.h HAVE_SYS_TYPES_H)
	ksutil_target_check_include_file(ks2 PUBLIC sys/stat.h HAVE_SYS_STAT_H)
	ksutil_target_check_include_file(ks2 PUBLIC string.h HAVE_STRING_H)
	ksutil_target_check_include_file(ks2 PUBLIC unistd.h HAVE_UNISTD_H)
	ksutil_target_check_include_file(ks2 PUBLIC memory.h HAVE_MEMORY_H)
	ksutil_target_check_include_file(ks2 PUBLIC strings.h HAVE_STRINGS_H)
	ksutil_target_check_include_file(ks2 PUBLIC inttypes.h HAVE_INTTYPES_H)
	ksutil_target_check_include_file(ks2 PUBLIC stdint.h HAVE_STDINT_H)
	ksutil_target_check_include_file(ks2 PUBLIC dlfcn.h HAVE_DLFCN_H)
	ksutil_target_check_include_file(ks2 PUBLIC sched.h HAVE_SCHED_H)
	ksutil_target_check_include_file(ks2 PUBLIC byteswap.h HAVE_BYTESWAP_H)
	ksutil_target_check_include_file(ks2 PUBLIC dirent.h HAVE_DIRENT_H)
endif()

# Check endianess for our swap macros
TEST_BIG_ENDIAN(IS_BIG_ENDIAN)

# Need libm on linux
if (KS_PLAT_LIN)
	include(cmake/FindLibM.cmake)
endif()

if (KS_PLAT_WIN)
	# We use RpcString on windows
	target_link_libraries(ks2 PUBLIC Rpcrt4.lib)
else()
	# Locate libuuid on non-win platforms
	include(cmake/FindUUID.cmake)
endif()

# Locate ssl
if (KS_PLAT_MAC)
	set(OPENSSL_ROOT_DIR /usr/local/opt/openssl)
endif()

include(FindOpenSSL)
find_package(OpenSSL REQUIRED Crypto SSL)

if (NOT KS_PLAT_WIN AND WITH_JSON_VALIDATION)
	# Find nlohmann_json_schema_validator
	find_package(nlohmann_json_schema_validator QUIET)
	if(nlohmann_json_schema_validator_FOUND)
		message(STATUS "Found nlohmann_json_schema_validator via CMake config")
		
		# Set C++ standard (json-schema-validator requires C++11)
		set(CMAKE_CXX_STANDARD 11)
		set(CMAKE_CXX_STANDARD_REQUIRED ON)

		# Compiler flags
		set(CMAKE_CXX_FLAGS "${CMAKE_CXX_FLAGS} -Wall -Wextra")

		target_compile_definitions(ks2 PUBLIC -DHAVE_JSON_SCHEMA_VALIDATOR=1)
		target_link_libraries(ks2 PRIVATE nlohmann_json_schema_validator)

		message(STATUS "JSON schema validation enabled with json-schema-validator")
	else()
		message(WARNING "nlohmann_json_schema_validator not found - JSON schema validation will be disabled")
	endif()
else()
	message(STATUS "JSON schema validation disabled (set WITH_JSON_VALIDATION=ON to enable)")
endif()

if (NOT KS_PLAT_WIN)
	# Use pthreads
	set(THREADS_PREFER_PTHREAD_FLAG ON)
	find_package(Threads REQUIRED)
endif()

set_target_properties(ks2 PROPERTIES DEFINE_SYMBOL KS_EXPORTS)

# Setup ks core definitions, these are private defintions
# meaning they won't be exported to public linkers
target_compile_definitions(ks2 PRIVATE
	-DPACKAGE_NAME="libks2"
	-DPACKAGE_TARNAME="libks2"
	-DPACKAGE_VERSION="${PROJECT_VERSION}"
	-DPACKAGE_STRING="libks2 ${PROJECT_VERSION}"
	-DPACKAGE_BUGREPORT="bugs@freeswitch.org"
	-DPACKAGE_URL=""
	-DVERSION="${PROJECT_VERSION}"
	-D_GNU_SOURCE=1
	-DCJSON_HIDE_SYMBOLS=1
)

# Setup our public facing definitions
target_compile_definitions(ks2 PUBLIC
	-DSTDC_HEADERS=1
	-DTIME_WITH_SYS_TIME=1
	-DRETSIGTYPE=void
	-DHAVE_LIBCRYPTO=1
	-DHAVE_LIBSSL=1
	-D_REENTRANT=1
	${PublicHaves}
)

# Handle debug mode
if (KS_DEBUG_HANDLE)
	target_compile_definitions(ks2 PUBLIC
		-DKS_DEBUG_HANDLE=1
	)
endif()

# JSON debug mode
if (KS_DEBUG_JSON)
	target_compile_definitions(ks2 PUBLIC
		-DKS_DEBUG_JSON=1
	)
endif()

# Pool debug mode
if (KS_DEBUG_POOL)
	target_compile_definitions(ks2 PUBLIC
		-DKS_DEBUG_POOL=1
	)
endif()

# Spinlock debug mode
if (KS_DEBUG_SPINLOCK)
	target_compile_definitions(ks2 PUBLIC
		-DKS_DEBUG_SPINLOCK=1
	)
endif()

# Mutex debug mode
if (KS_DEBUG_MUTEX)
	target_compile_definitions(ks2 PUBLIC
		-DKS_DEBUG_MUTEX=1
	)
endif()

if (NOT KS_PLAT_WIN)
	include(CheckCXXCompilerFlag)

	# All symbols are hidden by default, unless exposed explicitly
	check_cxx_compiler_flag(-fvisibility=hidden COMPILER_HAS_HIDDEN_VISIBILITY)
	if (COMPILER_HAS_HIDDEN_VISIBILITY)
		message("Compiler supports visibility")
		set_target_properties(ks2 PROPERTIES CXX_VISIBILITY_PRESET hidden)
		set_target_properties(ks2 PROPERTIES C_VISIBILITY_PRESET hidden)
		target_compile_definitions(ks2 PRIVATE
			-DKS_API_VISIBILITY=1
		)
	else()
		message("Compiler does not support visibility")
	endif()
endif()

if (IS_BIG_ENDIAN)
	target_compile_definitions(ks2 PUBLIC
		-D__BYTE_ORDER=__BIG_ENDIAN
	)
else()
	target_compile_definitions(ks2 PUBLIC
		-D__BYTE_ORDER=__LITTLE_ENDIAN
	)
endif()

# Setup ks core dependent linkages
target_link_libraries(
	ks2 PUBLIC
   	OpenSSL::SSL
	OpenSSL::Crypto
)

if (NOT KS_PLAT_WIN)
	target_link_libraries(
		ks2 PRIVATE
		LIBUUID::LIBUUID
		Threads::Threads
	)
endif()

if (KS_PLAT_LIN)
	target_link_libraries(ks2 PRIVATE LIBM::LIBM)
	target_link_libraries(ks2 PUBLIC rt atomic)
endif()

# Include key paths
target_include_directories(
	ks2 PUBLIC
	$<INSTALL_INTERFACE:$<INSTALL_PREFIX>/include/libks2>		# Include both include paths in the install config
	$<INSTALL_INTERFACE:$<INSTALL_PREFIX>/include>				# so we don't break any includes in installed areas

	$<BUILD_INTERFACE:${CMAKE_CURRENT_LIST_DIR}/src/include>	# Within the build tree however, encforce the root
	$<BUILD_INTERFACE:${CMAKE_CURRENT_LIST_DIR}>				# include path only
)

target_include_directories(
	ks2 PRIVATE
	${OPENSSL_INCLUDE_DIR}
)

# Setup a useful export file for inclusion in other projects
export(TARGETS ks2
	NAMESPACE ${PROJECT_NAME}
	FILE LibKS2Config.cmake
	EXPORT_LINK_INTERFACE_LIBRARIES
)

if (NOT KS_PLAT_WIN)

	# Set install targets
	install(TARGETS ks2 COMPONENT "runtime" EXPORT LibKS2Config DESTINATION lib)
	install(DIRECTORY src/include/libks COMPONENT "runtime" DESTINATION include/libks2 PATTERN src/include/libks/internal EXCLUDE)

	# Set path for pkg-config based on ARCH and distro type
	if("${CMAKE_OS_NAME}" STREQUAL "Centos") # pkg-config --variable pc_path pkg-config
		if(${CPACK_SYSTEM_NAME} MATCHES "x86_64")
			install(FILES ${PROJECT_BINARY_DIR}/libks2.pc COMPONENT "runtime" DESTINATION lib64/pkgconfig)
		else()
			install(FILES ${PROJECT_BINARY_DIR}/libks2.pc COMPONENT "runtime" DESTINATION lib/pkgconfig)
		endif()
	else()
		install(FILES ${PROJECT_BINARY_DIR}/libks2.pc COMPONENT "runtime" DESTINATION lib/pkgconfig)
	endif()

	install(FILES cmake/ksutil.cmake COMPONENT "runtime" DESTINATION include/libks2/libks/cmake)
	install(FILES cmake/FindPCRE.cmake COMPONENT "runtime" DESTINATION include/libks2/libks/cmake)
	install(FILES cmake/AddLibBacktrace.cmake COMPONENT "runtime" DESTINATION include/libks2/libks/cmake)
	install(FILES ${PROJECT_SOURCE_DIR}/copyright COMPONENT "runtime" DESTINATION share/doc/libks2)
	install(EXPORT LibKS2Config COMPONENT "runtime" DESTINATION include/libks2/libks/cmake)

	# Set uninstall target
	if(NOT TARGET uninstall)
		configure_file(
			"${CMAKE_CURRENT_SOURCE_DIR}/uninstall.cmake.in"
			"${CMAKE_CURRENT_BINARY_DIR}/uninstall.cmake"
			IMMEDIATE @ONLY)

		add_custom_target(uninstall
			COMMAND ${CMAKE_COMMAND} -P ${CMAKE_CURRENT_BINARY_DIR}/uninstall.cmake)
	endif()

	# Create a package config for registration with the system
	# prefix=@PC_PREFIX@
	# exec_prefix=@PC_EXEC_PREFIX@
	# libdir=@PC_LIB_DIR@
	# includedir=@PC_INCLUDE_DIR@
	# definitions=@PC_DEFINITIONS@
	# cmakedir=@PC_CMAKE_DIR@
	# Name: @PACKAGE_NAME@
	# Version: @PACKAGE_VERSION@
	set(PC_PREFIX ${CMAKE_INSTALL_PREFIX})
	set(PACKAGE_VERSION ${PROJECT_VERSION})
	get_property(PC_DEFINITIONS TARGET ks2 PROPERTY INTERFACE_COMPILE_DEFINITIONS)
	configure_file("${CMAKE_CURRENT_LIST_DIR}/libks.pc.in" "${PROJECT_BINARY_DIR}/libks2.pc" @ONLY)
endif()

#cotire(ks)

# Add tests
enable_testing()
add_subdirectory(tests)

if (WITH_KS_TEST)
	enable_testing()
	add_subdirectory(KSTest)
endif()
