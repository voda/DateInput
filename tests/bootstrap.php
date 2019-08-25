<?php
declare(strict_types=1);
/*
 * Copyright (c) 2014, Ondřej Vodáček
 */

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

function test(callable $test) {
	$test();
}
