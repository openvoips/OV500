#!/usr/bin/perl
# Copyright (c) 2003, 2024, Oracle and/or its affiliates.
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License, version 2.0, as
# published by the Free Software Foundation.
#
# This program is designed to work with certain software (including
# but not limited to OpenSSL) that is licensed under separate terms, as
# designated in a particular file or component or in included license
# documentation. The authors of MySQL hereby grant you an additional
# permission to link the program and your derivative works with the
# separately licensed software that they have either included with
# the program or referenced in the documentation.
#
# Without limiting anything contained in the foregoing, this file,
# which is part of Connector/ODBC, is also subject to the
# Universal FOSS Exception, version 1.0, a copy of which can be found at
# https://oss.oracle.com/licenses/universal-foss-exception.
#
# This program is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License, version 2.0, for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA


use Test::Harness qw(&runtests $verbose);
use File::Find;

use strict;

sub run_cmd (@);

my %dispatch = (
    "run" => \&run_cmd,
);

=head1 NAME

unit - Run unit tests in directory

=head1 SYNOPSIS

  unit run

=cut

my $cmd = shift;

if (defined $cmd && exists $dispatch{$cmd}) {
    $dispatch{$cmd}->(@ARGV);
} else {
    print "Unknown command", (defined $cmd ? " $cmd" : ""), ".\n";
    print "Available commands are: ", join(", ", keys %dispatch), "\n";
}

=head2 run

Run all unit tests in the current directory and all subdirectories.

=cut


sub _find_test_files (@) {
    my @dirs = @_;
    my @files;
    find sub { 
        $File::Find::prune = 1 if /^SCCS$/;
        push(@files, $File::Find::name) if -x _ && /-t\z/;
    }, @dirs;
    return @files;
}

sub run_cmd (@) {
    my @files;

    # If no directories were supplied, we add all directories in the
    # current directory except 'mytap' since it is not part of the
    # test suite.
    if (@_ == 0) {
      # Ignore these directories
      my @ignore = qw(mytap SCCS);

      # Build an expression from the directories above that tests if a
      # directory should be included in the list or not.
      my $ignore = join(' && ', map { '$_ ne ' . "'$_'"} @ignore);

      # Open and read the directory. Filter out all files, hidden
      # directories, and directories named above.
      opendir(DIR, ".") or die "Cannot open '.': $!\n";
      @_ = grep { -d $_ && $_ !~ /^\..*/ && eval $ignore } readdir(DIR);
      closedir(DIR);
    }

    print "Running tests: @_\n";

    foreach my $name (@_) {
        push(@files, _find_test_files $name) if -d $name;
        push(@files, $name) if -f $name;
    }

    if (@files > 0) {
        # Removing the first './' from the file names
        foreach (@files) { s!^!./! }
        $ENV{'HARNESS_PERL_SWITCHES'} .= q" -e 'exec @ARGV'";
        runtests @files;
    }
}

