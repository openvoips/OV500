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
#include "libks/internal/ks_thread.h"
#include "catch/catch.hpp"

using namespace signalwire::pal;
using namespace signalwire::pal::async;

/**
 * thread_join tests the request_stop feature and how it relates to
 * the thread state management from stop->join->destroy flow.
 */
TEST_CASE("thread_request_stop")
{
	ks_thread_t *thread = nullptr;
	struct State {
		volatile ks_bool_t ran = KS_FALSE;
		ks_spinlock_t block_stop = {0};
	};
	State state;

	auto runLoop = [](ks_thread_t *thread, void *data)->void * {
		auto state = static_cast<State *>(data);
		state->ran = KS_TRUE;

		while (ks_thread_stop_requested(thread) == KS_FALSE) {
			sleep(time::seconds(1));
		}

		// Let caller control our exit here by blocking on their spin lock
		ks_spinlock_acquire(&state->block_stop);

		return nullptr;
	};

	uint32_t active_attached, active_detached;
	ks_thread_stats(&active_attached, &active_detached);
	REQUIRE(active_attached == 0);

	KCHECK(ks_thread_create_ex(&thread, runLoop, &state, 0, 1024 * 1024, KS_PRI_REALTIME, nullptr));

	// Keep the block stop locked so we can force the thread to sit in a mid transition state
	ks_spinlock_acquire(&state.block_stop);

	ks_thread_stats(&active_attached, &active_detached);
	REQUIRE(active_attached == 1);

	// First call should succeed
	KCHECK(ks_thread_request_stop(thread));

	// Second should fail indicating it was already requested
	REQUIRE(ks_thread_request_stop(thread) == KS_STATUS_THREAD_ALREADY_STOPPED);

	// Now even though we requested it stop, we still
	// should be allowed to block on a join
	auto firstJoiner = task::start("First joiner", ks_thread_join, thread);

	// But a second thread should error (first one is hung now)
	auto secondJoiner = task::start("Check block on second", ks_thread_join, thread);
	REQUIRE(secondJoiner->getResult() == KS_STATUS_THREAD_ALREADY_JOINED);

	// Great now let the thread die
	ks_spinlock_release(&state.block_stop);

	// And join on the first joiner
	REQUIRE(firstJoiner->getResult() == KS_STATUS_SUCCESS);

	// Finally should be able to delete the darn thing
	ks_thread_destroy(&thread);

	// And verify the resource numbers line up
	ks_thread_stats(&active_attached, &active_detached);
	REQUIRE(active_attached == 0);

	REQUIRE(state.ran == KS_TRUE);
}

/**
 * thread_destroy tests the synchronization of a single call to destroy
 * properly joining and deleting an attached thread.
 */
TEST_CASE("thread_destroy")
{
	ks_thread_t *thread = nullptr;
	ks_bool_t ran = KS_FALSE;

	auto runLoop = [](ks_thread_t *thread, void *data)->void * {
		*static_cast<ks_bool_t *>(data) = KS_TRUE;

		while (ks_thread_stop_requested(thread) == KS_FALSE) {
			sleep(time::seconds(1));
		}

		// Wait extra to force a block on join to ensure that is working
		sleep(time::seconds(3));

		return nullptr;
	};

	uint32_t active_attached, active_detached;
	ks_thread_stats(&active_attached, &active_detached);
	REQUIRE(active_attached == 0);

	// Spawn an attached thread in the global pool
	KCHECK(ks_thread_create_ex(&thread, runLoop, &ran, 0, 1024 * 1024, KS_PRI_REALTIME, nullptr));

	// Ensure tracking is correct
	ks_thread_stats(&active_attached, &active_detached);
	REQUIRE(active_attached == 1);

	// Destroy this thread should properly block and not crash
	ks_thread_destroy(&thread);

	// Stats should be zero
	ks_thread_stats(&active_attached, &active_detached);
	REQUIRE(active_attached == 0);

	// And it should've ran
	REQUIRE(ran == KS_TRUE);
}

class KSThread {
public:
	KSThread() = default;

	~KSThread()
	{
		LOG(TEST, "Destructing");
		stop();
		destroy();
	}

	void start()
   	{
		auto guard = m_stateLock.lock();

		destroy();

		LOG(TEST, "Starting");

		auto result = ks_thread_create_ex(&m_thread, KSThread::__bootstrap, this, 0, 1024 * 1024, KS_PRI_REALTIME, nullptr);
		REQUIRE(result == KS_STATUS_SUCCESS);
		REQUIRE(m_thread != nullptr);

		LOG(TEST, "Started:", ks_thread_self_id());
	}

	void join()
	{
		auto guard = m_stateLock.lock();

		if (m_joined)
			return;

		auto result = ks_thread_join(m_thread);
		if (result && result != KS_STATUS_THREAD_ALREADY_JOINED) {
			LOG(TEST, "Unexpected ks_status:", result);
		}
		m_joined = true;

		if (m_exception) {
			std::rethrow_exception(m_exception);
		}
	}

	void stop()
	{
		auto guard = m_stateLock.lock();
		auto result = ks_thread_request_stop(m_thread);
		if (result && result != KS_STATUS_THREAD_ALREADY_STOPPED) {
			PAL_THROW(RuntimeError, "Expected success result from ks_thread_request_stop, got:", result);
		}
		guard.release();

		join();
	}

	bool stopRequested() const {
		auto guard = m_stateLock.lock();
		return static_cast<bool>(ks_thread_stop_requested(m_thread));
	}

protected:
	void destroy() {
		if (m_thread) {
			ks_thread_destroy(&m_thread);
		}
	}

	static void *__bootstrap(ks_thread_t *thread, void *data) {
		while (!ks_thread_stop_requested(thread)) {
			char buffer[256 * 1024];

			for (char num = 0; num < 255 && !ks_thread_stop_requested(thread); num++) {
				memset(buffer, num, sizeof(buffer));
			}
		}
		return nullptr;
	}

	ks_thread_t *m_thread = nullptr;
	std::exception_ptr m_exception;
	mutable SpinLock m_stateLock;
	bool m_joined = false;
};

TEST_CASE("thread_stress")
{
return;
	const auto THREAD_COUNT = 5ul;
	const auto THREAD_LOOPS = 10ul;
	const auto THREAD_ITERATIONS = 10ul;

	auto threadBatch = [&](uint32_t iter_count, uint32_t thr_count) {
		for (uint32_t iter = 0; iter < iter_count; iter++) {
			LOG(TEST, "Iteration:", iter);

			std::vector<std::shared_ptr<KSThread>> threads;
			for (unsigned thr = 0; thr < thr_count; thr++) {
				auto thread = std::make_shared<KSThread>();
				thread->start();
				threads.push_back(std::move(thread));
			}

			sleep(time::seconds(1));

			for (auto &thread : threads)
				thread->stop();
		}
	};

	std::vector<task::TaskPtr> loopers;

	for (auto loop_count = 0; loop_count < THREAD_LOOPS; loop_count++) {
		loopers.push_back(
				task::start(
					"Thread starter/stopper",
					threadBatch,	// Closure to execute
				   	THREAD_ITERATIONS,
				   	THREAD_COUNT
				)
			);
	}

	for (auto &looper : loopers) {
		uint32_t active_attached, active_detached;

		// Check our counts should not be greater then the sum of all loopers with all threads
		ks_thread_stats(&active_attached, &active_detached);

		REQUIRE(active_detached == 0);
		REQUIRE(active_attached <= (THREAD_COUNT * THREAD_LOOPS));

		looper->join();
	}
}

