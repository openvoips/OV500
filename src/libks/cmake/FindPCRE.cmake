# PCRE_FOUND
# PCRE_LIBRARIES
# PCRE_LIBRARY_DIRS
# PCRE_LDFLAGS
# PCRE_INCLUDE_DIRS
# PCRE_CFLAGS

if (NOT KS_PLAT_WIN)
	include(FindPkgConfig)

	if (NOT PKG_CONFIG_FOUND)
		message("Failed to locate pkg-config" FATAL)
	endif()

	pkg_check_modules(PCRE libpcre REQUIRED)
else()
	# On windows we expect the build env to have a setup target already
	# as we just created a libpcre repo with the pre-build libs and includes
	if (NOT TARGET pcre)
		message(FATAL_ERROR "libpcre target not loaded")
	endif()

	# We just have to declare the library, since we use the interface systme
	# all include paths and link rules will be inherited automatically
	set(PCRE_LIBRARIES pcre)

	get_target_property(PCRE_INCLUDE_DIRS pcre SOURCE_DIR)

	set(PCRE_CFLAGS -DPCRE_STATIC)
endif()
