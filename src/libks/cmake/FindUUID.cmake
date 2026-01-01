# UUID_FOUND
# UUID_LIBRARIES
# UUID_LIBRARY_DIRS
# UUID_LDFLAGS
# UUID_INCLUDE_DIRS
# UUID_CFLAGS

if (NOT KS_PLAT_WIN)
	include(FindPkgConfig)

	if (NOT PKG_CONFIG_FOUND)
		message("Failed to locate pkg-config" FATAL)
	endif()

	pkg_check_modules(UUID uuid REQUIRED)

	if(UUID_FOUND)
		find_library(UUID_ABS_LIB_PATH
			NAMES ${UUID_LIBRARIES}
			HINTS ${UUID_LIBRARY_DIRS}
			PATHS ${UUID_LIBRARY_DIRS}
		)
		if(NOT TARGET LIBUUID::LIBUUID)
		  add_library(LIBUUID::LIBUUID UNKNOWN IMPORTED)
		  set_target_properties(LIBUUID::LIBUUID PROPERTIES
			IMPORTED_LOCATION ${UUID_ABS_LIB_PATH}
			INTERFACE_INCLUDE_DIRECTORIES "${UUID_INCLUDE_DIRS}")
		endif()

		message("Found UUID setup target at imported location: ${UUID_ABS_LIB_PATH}")
	endif()
endif()
