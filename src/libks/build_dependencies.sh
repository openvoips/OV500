#!/bin/bash
git clone https://github.com/pboettch/json-schema-validator.git
cd json-schema-validator

# Configure build with shared library
mkdir cmake-build-release
cmake -S . -B cmake-build-release -DCMAKE_BUILD_TYPE=Release -DBUILD_SHARED_LIBS=ON

# Build and install the library
cmake --build cmake-build-release
cd cmake-build-release && make install

# Update dynamic linker cache
ldconfig

