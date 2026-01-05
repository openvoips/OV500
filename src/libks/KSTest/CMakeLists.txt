# Our core file list
file(GLOB KSTestDeps [LIST_DIRECTORIES false]
	${CMAKE_CURRENT_LIST_DIR}/*.h
	${CMAKE_CURRENT_LIST_DIR}/*.hpp
	${CMAKE_CURRENT_LIST_DIR}/*.cpp
	${CMAKE_CURRENT_LIST_DIR}/cases/*.cpp
	${CMAKE_CURRENT_LIST_DIR}/cases/*.hpp
)

source_group(TREE ${CMAKE_CURRENT_LIST_DIR} FILES ${KSTestDeps})

add_executable(
	KSTest
	${KSTestDeps}
	${KSTestExpDeps}
)

# Fetch Catch2 testing framework
hunter_add_package(Catch)
find_package(Catch2 CONFIG REQUIRED)

if (NOT TARGET LibPal)
	message(FATAL_ERROR "LibPal not found")
endif()
if (NOT TARGET Catch2::Catch)
	message(FATAL_ERROR "Catch2 not found")
endif()

# Link to ks/catch/LibPal
target_link_libraries(KSTest ks2 LibPal Catch2::Catch)

# Register our tests with cmake
add_test(KSTest KSTest)

# Inlclude our root
target_include_directories(
	KSTest
	PUBLIC
	${CMAKE_CURRENT_LIST_DIR}
)

# Turn on the latest c++ features
target_compile_features(KSTest PUBLIC cxx_std_17)

# For boost on linux
if (PAL_PLAT_LIN)
	target_compile_definitions(KSTest PRIVATE
		-DBOOST_STACKTRACE_GNU_SOURCE_NOT_REQUIRED=1
	)
endif()

# When debugging on windows, the cwd will be the binary dir (where the config files are)
set_target_properties(KSTest PROPERTIES VS_DEBUGGER_WORKING_DIRECTORY ${CMAKE_BINARY_DIR})
set(COTIRE_MINIMUM_NUMBER_OF_TARGET_SOURCES 1)

set_target_properties(KSTest PROPERTIES COTIRE_ADD_UNITY_BUILD FALSE)
set_target_properties(KSTest PROPERTIES COTIRE_ENABLE_PRECOMPILED_HEADER TRUE)
set_target_properties(KSTest PROPERTIES COTIRE_CXX_PREFIX_HEADER_INIT "KSTest.hpp")
cotire(KSTest)
