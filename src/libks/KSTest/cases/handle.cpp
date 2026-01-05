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

#include "KSTest.hpp"
#include "libks/ks_atomic.h"
#include "libks/internal/ks_handle.h"
#include "catch/catch.hpp"

#define KS_HANDLE_GROUP_TEST	KS_HANDLE_USER_GROUP_START

typedef enum {
    KS_HANDLE_TYPE_TEST_1 =  KS_HANDLE_MAKE_TYPE(TEST, 1),
    KS_HANDLE_TYPE_TEST_2 =  KS_HANDLE_MAKE_TYPE(TEST, 2),
    KS_HANDLE_TYPE_TEST_3 =  KS_HANDLE_MAKE_TYPE(TEST, 3),
    KS_HANDLE_TYPE_TEST_4 =  KS_HANDLE_MAKE_TYPE(TEST, 4),
} swclt_handle_types;

#define KS_HANDLE_TYPE_TEST KS_HANDLE_TYPE_TEST_1

using namespace signalwire::pal;
using namespace signalwire::pal::async;

TEST_CASE("handle_dword_macros")
{
	auto val = KS_HANDLE_MAKE_DWORD(0x1234, 0x5678);
	REQUIRE(val == 0x12345678);
	val = KS_HANDLE_MAKE_DWORD(0x5678, 0x1234);
	REQUIRE(val == 0x56781234);
}

TEST_CASE("handle_qword_macros")
{
	auto val = KS_HANDLE_MAKE_QWORD(0x01234567, 0x89ABCDEF);
	REQUIRE(val == 0x0123456789ABCDEFull);

	val = KS_HANDLE_MAKE_QWORD(0x89ABCDEF, 0x01234567);
	REQUIRE(val == 0x89ABCDEF01234567ull);
}

TEST_CASE("handle_type_macros")
{
	auto val = KS_HANDLE_MAKE_TYPE(TEST, 10);
	REQUIRE(KS_HANDLE_GROUP_FROM_TYPE(val) == KS_HANDLE_GROUP_TEST);
	REQUIRE(KS_HANDLE_GROUP_INDEX_FROM_TYPE(val) == 10);
}

TEST_CASE("handle_handle_macros")
{
	auto val = KS_HANDLE_MAKE_HANDLE(KS_HANDLE_TYPE_TEST, 512, 8);
	REQUIRE(KS_HANDLE_GROUP_FROM_TYPE(KS_HANDLE_TYPE_TEST) == KS_HANDLE_GROUP_TEST);
	REQUIRE(KS_HANDLE_SLOT_INDEX_FROM_HANDLE(val) == 8);
	REQUIRE(KS_HANDLE_SLOT_SEQUENCE_FROM_HANDLE(val) == 512);
	REQUIRE(KS_HANDLE_GROUP_FROM_HANDLE(val) == KS_HANDLE_GROUP_TEST);
	REQUIRE(KS_HANDLE_GROUP_INDEX_FROM_HANDLE(val) == KS_HANDLE_GROUP_INDEX_FROM_TYPE(KS_HANDLE_TYPE_TEST));
	REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(val) == KS_HANDLE_TYPE_TEST);
}

static void __fake_destroy(void *bobo)
{
}


TEST_CASE("handle_enum")
{
	void *handle_test_data_1;
	ks_handle_t handle_test_handle_1;
	void *handle_test_data_2;
	ks_handle_t handle_test_handle_2;
	void *handle_test_data_3;
	ks_handle_t handle_test_handle_3;
	void *handle_test_data_4_1;
	ks_handle_t handle_test_handle_4_1;
	void *handle_test_data_4_2;
	ks_handle_t handle_test_handle_4_2;

	ks_handle_alloc(KS_HANDLE_TYPE_TEST_1, sizeof(ks_handle_base_t), &handle_test_data_1, &handle_test_handle_1, __fake_destroy);
	REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(handle_test_handle_1) == KS_HANDLE_TYPE_TEST_1);
	ks_handle_set_ready(handle_test_handle_1);
	ks_handle_alloc(KS_HANDLE_TYPE_TEST_2, sizeof(ks_handle_base_t), &handle_test_data_2, &handle_test_handle_2, __fake_destroy);
	REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(handle_test_handle_2) == KS_HANDLE_TYPE_TEST_2);
	ks_handle_set_ready(handle_test_handle_2);
	ks_handle_alloc(KS_HANDLE_TYPE_TEST_3, sizeof(ks_handle_base_t), &handle_test_data_3, &handle_test_handle_3, __fake_destroy);
	REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(handle_test_handle_3) == KS_HANDLE_TYPE_TEST_3);
	ks_handle_set_ready(handle_test_handle_3);
	ks_handle_alloc(KS_HANDLE_TYPE_TEST_4, sizeof(ks_handle_base_t), &handle_test_data_4_1, &handle_test_handle_4_1, __fake_destroy);
	REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(handle_test_handle_4_1) == KS_HANDLE_TYPE_TEST_4);
	ks_handle_set_ready(handle_test_handle_4_1);
	ks_handle_alloc(KS_HANDLE_TYPE_TEST_4, sizeof(ks_handle_base_t), &handle_test_data_4_2, &handle_test_handle_4_2, __fake_destroy);
	REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(handle_test_handle_4_2) == KS_HANDLE_TYPE_TEST_4);
	ks_handle_set_ready(handle_test_handle_4_2);

	ks_handle_t next = 0;
	uint32_t total_type_1 = 0, total_type_2 = 0, total_type_3 = 0, total_type_4_1 = 0, total_type_4_2 = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_1, &next)) {
		REQUIRE(next == handle_test_handle_1);
		REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(next) == KS_HANDLE_TYPE_TEST_1);
		total_type_1++;
	}

	next = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_2, &next)) {
		REQUIRE(next == handle_test_handle_2);
		REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(next) == KS_HANDLE_TYPE_TEST_2);
		total_type_2++;
	}

	next = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_3, &next)) {
		REQUIRE(next == handle_test_handle_3);
		REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(next) == KS_HANDLE_TYPE_TEST_3);
		total_type_3++;
	}

	next = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_4, &next)) {
		REQUIRE(KS_HANDLE_TYPE_FROM_HANDLE(next) == KS_HANDLE_TYPE_TEST_4);
		if (next == handle_test_handle_4_2) 
			total_type_4_2++;
		else if (next == handle_test_handle_4_1) 
			total_type_4_1++;
		else
			PAL_FATALITYM("Unexpected handle:", next);
	}

	REQUIRE(total_type_1 == 1);
	REQUIRE(total_type_2 == 1);
	REQUIRE(total_type_3 == 1);
	REQUIRE(total_type_4_1 == 1);
	REQUIRE(total_type_4_2 == 1);

	ks_handle_destroy(&handle_test_handle_1);
	ks_handle_destroy(&handle_test_handle_2);
	ks_handle_destroy(&handle_test_handle_3);
	ks_handle_destroy(&handle_test_handle_4_1);
	ks_handle_destroy(&handle_test_handle_4_2);

	next = 0;
	total_type_1 = 0, total_type_2 = 0, total_type_3 = 0, total_type_4_1 = 0, total_type_4_2 = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_1, &next)) {
		REQUIRE(!"Error, should not have enumerated test 1 handle");
	}

	next = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_2, &next)) {
		REQUIRE(!"Error, should not have enumerated test 2 handle");
	}

	next = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_3, &next)) {
		REQUIRE(!"Error, should not have enumerated test 3 handle");
	}

	next = 0;
	while (!ks_handle_enum_type(KS_HANDLE_TYPE_TEST_4, &next)) {
		REQUIRE(!"Error, should not have enumerated test 4 handle");
	}

	REQUIRE(total_type_1 == 0);
	REQUIRE(total_type_2 == 0);
	REQUIRE(total_type_3 == 0);
	REQUIRE(total_type_4_1 == 0);
	REQUIRE(total_type_4_2 == 0);
}

static std::atomic<uint32_t> g_context_deinit_count = 0;
static std::atomic<uint32_t> g_expected_deinit_count = 0;

static void __context_deinit(void *data)
{
	g_context_deinit_count++;
}

TEST_CASE("handle_basic_api")
{
	auto testRun = [&]() {
		ks_handle_t hThread;
		ks_handle_base_t *thread = nullptr, *_thread = nullptr;

		auto checkref = [&](uint32_t ref) {
			uint32_t current_ref;
			KCHECK(ks_handle_refcount(hThread, &current_ref));
			PAL_ASSERT(ref == current_ref);
		};

		// Test alloc/ready
		KCHECK(ks_handle_alloc(KS_HANDLE_TYPE_TEST, 30, &thread, &hThread, __context_deinit));
		KCHECK(ks_handle_set_ready(hThread));

		// Test gets
		KCHECK(ks_handle_get(KS_HANDLE_TYPE_TEST, hThread, &_thread));
		checkref(1);
		KCHECK(ks_handle_get(KS_HANDLE_TYPE_TEST, hThread, &_thread));
		checkref(2);
		KCHECK(ks_handle_get(KS_HANDLE_TYPE_TEST, hThread, &_thread));
		checkref(3);

		// Test puts
		_thread = thread;
		KCHECK(ks_handle_put(KS_HANDLE_TYPE_TEST, &_thread));
		checkref(2);
		_thread = thread;
		KCHECK(ks_handle_put(KS_HANDLE_TYPE_TEST, &_thread));
		checkref(1);
		_thread = thread;
		KCHECK(ks_handle_put(KS_HANDLE_TYPE_TEST, &_thread));
		checkref(0);

		KCHECK(ks_handle_get(KS_HANDLE_TYPE_TEST, hThread, &_thread));
		checkref(1);
		PAL_ASSERT(_thread == thread);

		KCHECK(ks_handle_put(KS_HANDLE_TYPE_TEST, &_thread));
		checkref(0);

		ks_handle_destroy(&hThread);
		g_expected_deinit_count++;
	};

	std::vector<task::TaskPtr> tasks;

	for (auto i = 0; i < 50; i++) {
		LOG(task, "Spawning handle basic iteration:", i);
		tasks.emplace_back(task::start(string::toString("Handle basic test:", i), testRun));
	}

	for (auto &task : tasks) {
		LOG(task, "Joining on test task:", task->getName());
		task->join();
	}

	REQUIRE(g_context_deinit_count == g_expected_deinit_count);
}

TEST_CASE("handle_threaded_stress")
{
	auto checkref = [&](ks_handle_t handle, uint32_t expectedRefCount) {
		uint32_t current_ref;
		if (!ks_handle_refcount(handle, &current_ref))
			REQUIRE(expectedRefCount == current_ref);
	};

	std::atomic<uint64_t> checkoutCount = {0};

	// This function just loops on a check out/check in until the check out fails
	auto checkouter = [&](ks_handle_t handle) {
		void *thread;

		forever {
			if (ks_handle_get(KS_HANDLE_TYPE_TEST, handle, &thread)) {
				return;
			}
			checkref(handle, 1);
			ks_handle_put(KS_HANDLE_TYPE_TEST, &thread);
			checkoutCount++;
		}
	};

	// Create a handle
	void *thread;
	ks_handle_t handle;
	KCHECK(ks_handle_alloc(KS_HANDLE_TYPE_TEST, sizeof(ks_handle_base_t), &thread, &handle, __context_deinit));
	KCHECK(ks_handle_set_ready(handle));

	// Kick off a thread that just checks out and checks in like crazy
	Thread checkoutThread("Checkouter", checkouter, handle);
	checkoutThread.start();

	// Let the checkout counts build up a bit
	while (checkoutCount < 100) {
		sleep(time::seconds(1));
	}

	// Now mark the handle not ready
	KCHECK(ks_handle_set_notready(KS_HANDLE_TYPE_TEST, handle, &thread));

	// The checkout count should be stopped now
	sleep(time::seconds(1));
	REQUIRE(checkoutThread.isStopped());

	// Should be able to destroy it now
	KCHECK(ks_handle_destroy(&handle));
}
