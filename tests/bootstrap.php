<?php
/*
 * Copyright (c) 2014, Ondřej Vodáček
 */

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

if (extension_loaded('xdebug')) {
	xdebug_disable();
	Tester\CodeCoverage\Collector::start(__DIR__ . '/../build/coverage.dat');
}

function test($test) {
	$test();
}
