<?php

use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(function() { // valid submitted value
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$control->setValue('2014-02-14');
	Assert::equal(new DateTime('2014-02-14 00:00:00'), $control->getValue());
});

test(function() { // null value
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$control->setValue(null);
	Assert::equal(null, $control->getValue());
});

test(function() { // no value
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$control->setValue('');
	Assert::equal(null, $control->getValue());
});

test(function() { // DateTime & DateInterval values
	$control = new DateInput('date', DateInput::TYPE_TIME);
	$control->setValue(new DateTime('1970-01-01 12:13:14'));
	Assert::equal('12:13:14', $control->getValue()->format('H:i:s'));
	$control->setValue(new DateInterval('PT12H13M14S'));
	Assert::equal('12:13:14', $control->getValue()->format('H:i:s'));
});
