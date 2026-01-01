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
#define CATCH_CONFIG_MAIN	// Let catch do main handling
#include "catch/catch.hpp"

using namespace signalwire::pal;

namespace {
	static log::StreamBuffer s_stream("Catch");
	static std::streambuf * s_oldBuffer = nullptr;
}

util::Scope init(
	[&]()
	{
		// Redirect std::cout rdbuf to go to our logger
		s_oldBuffer = std::cout.rdbuf(&s_stream);

		// Enable testing output in pal
		log::enableLevel("TEST");

		// Initialize ks
		ks_init();
	},
	[&]()
	{
		// Restore the buffer
		std::cout.rdbuf(s_oldBuffer);

		// Deinit ks
		ks_shutdown();
	}
);
