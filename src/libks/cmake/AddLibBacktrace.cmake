# Creates a target LibBacktrace that you can link to
# and adds this project as an external one.
# It will install to:
# 	${PROJECT_SOURCE_DIR}/external/LibBackTrace/install

if (KS_PLAT_WIN)
	message(FATAL "LibBackTrace is only available on Gnu platforms")
endif()

include(ExternalProject)
find_package(Git REQUIRED)

ExternalProject_Add(
	Project_LibBacktrace
	PREFIX ${PROJECT_SOURCE_DIR}/external/LibBacktrace
	GIT_REPOSITORY https://github.com/ianlancetaylor/libbacktrace
	TIMEOUT 10
	UPDATE_COMMAND ""
	CONFIGURE_COMMAND ${PROJECT_SOURCE_DIR}/external/LibBacktrace/src/Project_LibBacktrace/configure --enable-host-shared --prefix=${PROJECT_SOURCE_DIR}/external/LibBacktrace/install --enable-shared=no --enable-static=yes
	BUILD_COMMAND make
	INSTALL_COMMAND make install
	BUILD_IN_SOURCE 1
)

# Not done yet we have to make a target that is depndent on this so we can use it easily
add_library(LibBacktrace STATIC IMPORTED GLOBAL)
set_target_properties(LibBacktrace PROPERTIES IMPORTED_LOCATION "${CMAKE_CURRENT_SOURCE_DIR}/external/LibBacktrace/install/lib/libbacktrace.a")
set_target_properties(LibBacktrace PROPERTIES INCLUDE_DIRECTORIES "${CMAKE_CURRENT_SOURCE_DIR}/external/LibBacktrace/install/include")

add_dependencies(LibBacktrace Project_LibBacktrace)
