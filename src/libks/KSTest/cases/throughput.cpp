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
#include "libks/internal/ks_throughput.h"
#include "catch/catch.hpp"

using namespace signalwire::pal;

using ThroughputHandle = ksutil::KSHandle<ks_throughput_ctx_t, (ksutil::KSHandleEnum)ks_handle_types::KS_HTYPE_THROUGHPUT, ks_throughput_t>;

TEST_CASE("throughput")
{
	ThroughputHandle rate;

	KCHECK(ks_throughput_create_ex(&rate, 2, 1));

	// Tell throughput we'll update now by hand and to use 
	rate->static_now_sec = 1000;

    REQUIRE(rate->count_bucket == 0);

	KCHECK(ks_throughput_report(rate, 1024));

	REQUIRE(rate->count_bucket == 0);
	REQUIRE(rate->count_bucket == 0);
	REQUIRE(rate->current_bucket.count == 1);
	REQUIRE(rate->current_bucket.size == 1024);

    // Advance time by one second
	rate->static_now_sec += 1;
    KCHECK(ks_throughput_update(rate));
	REQUIRE(rate->count_bucket == 1);

    REQUIRE(rate->count_bucket  == 1);

	REQUIRE(rate->current_bucket.count == 0);
	REQUIRE(rate->current_bucket.size == 0);
    REQUIRE(rate->buckets[0].count == 1);
    REQUIRE(rate->buckets[0].size == 1024);

    // Now we should have a rate
	ks_throughput_stats_t stats;
	KCHECK(ks_throughput_stats(rate, &stats));
    REQUIRE(stats.rate_count == 1.0);
    REQUIRE(stats.rate_size == 1024.0);

	char rate_str[512];
	std::cout << ks_throughput_stats_render(&stats, rate_str, sizeof(rate_str)) << std::endl;

    // If we report, without advancing the time, the rate shouldn't change but the first
    // bucket should be staged
	KCHECK(ks_throughput_report(rate, 1024));

	KCHECK(ks_throughput_stats(rate, &stats));
	REQUIRE(rate->count_bucket == 1);
    REQUIRE(stats.rate_count == 1.0);
    REQUIRE(stats.rate_size == 1024.0);

    // As the first bucket is still 'uncompleted' but it should be additive
    REQUIRE(rate->current_bucket.count == 1);
    REQUIRE(rate->current_bucket.size == 1024);
    REQUIRE(rate->buckets[0].count == 1);
    REQUIRE(rate->buckets[0].size == 1024);

    // Ok advance the clock by 1 second and update to see if it continues
    // to return the proper rate
    rate->static_now_sec += 1;
    KCHECK(ks_throughput_update(rate));
    KCHECK(ks_throughput_stats(rate, &stats));

    // Should have a new bucket at 0 with 1-2 rolled forward
    REQUIRE(rate->current_bucket.count == 0);
    REQUIRE(rate->current_bucket.size == 0);
    REQUIRE(rate->buckets[0].count == 1);
    REQUIRE(rate->buckets[0].size == 1024);
    REQUIRE(rate->buckets[1].count == 1);
    REQUIRE(rate->buckets[1].size == 1024);

    // Same rate as before
    REQUIRE(stats.rate_size == 1024.0);
    REQUIRE(stats.rate_count == 1.0);

	std::cout << ks_throughput_stats_render(&stats, rate_str, sizeof(rate_str)) << std::endl;

    // Now advance the time without reporting anything, we should start
    // seeing the rate trend downwards
    rate->static_now_sec += 1;
    KCHECK(ks_throughput_update(rate));

    KCHECK(ks_throughput_stats(rate, &stats));

	REQUIRE(rate->current_bucket.count == 0);
	REQUIRE(rate->current_bucket.size == 0);
    REQUIRE(rate->buckets[0].count == 0);
    REQUIRE(rate->buckets[0].size == 0);
    REQUIRE(rate->buckets[1].count == 1);
    REQUIRE(rate->buckets[1].size == 1024);

    // Rate should be cut in half
    REQUIRE(stats.rate_size == 1024.0 / 2);
    REQUIRE(stats.rate_count == 1.0 / 2);

    // Finally verify we can render the stats
	LOG(TEST, stats);
}
