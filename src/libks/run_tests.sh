#!/bin/bash
apt-get update && apt-get install -yq build-essential autotools-dev lsb-release pkg-config automake autoconf libtool-bin clang-tools-11
apt-get install -yq cmake uuid-dev libssl-dev colorized-logs git
./build_dependencies.sh
git config --global --add safe.directory `pwd`
sed -i '/cotire/d' ./CMakeLists.txt
mkdir -p scan-build
scan-build-11 -o ./scan-build/ cmake . -DWITH_JSON_VALIDATION=on -DWITH_PACKAGING=off
mkdir -p tests/unit/logs
make -j`nproc --all` |& tee ./unit-tests-build-result.txt
exitstatus=${PIPESTATUS[0]}
echo $exitstatus > ./build-status.txt
echo 0 > tests/unit/run-tests-status.txt
export TEST_ARTIFACT_FILE=/__w/libks/libks/tests/unit/logs/artifacts.html
env CTEST_OUTPUT_ON_FAILURE=1 make test |& tee >(ansi2html > $TEST_ARTIFACT_FILE)
exitstatus=${PIPESTATUS[0]}
ls -al
ls -al tests/unit/logs

echo "Exist status is $exitstatus"

if [ "$exitstatus" != "0" ]]; then
  echo "TEST_ARTIFACT_FILE=$TEST_ARTIFACT_FILE" >> $GITHUB_OUTPUT
fi

exit $exitstatus
