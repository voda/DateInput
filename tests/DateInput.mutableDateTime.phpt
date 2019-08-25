<?php

use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(function() { // valid submitted value
	$control = new DateInput('date', DateInput::TYPE_DATE, false);
	$control->setValue('2014-02-14');
	Assert::type(DateTime::class, $control->getValue());
	Assert::equal(new DateTime('2014-02-14 00:00:00'), $control->getValue());
});
