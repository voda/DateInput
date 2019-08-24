<?php

use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

DateInput::register(true);

test(function() { // valid submitted value
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$control->setValue('2014-02-14');
	Assert::type(DateTimeImmutable::class, $control->getValue());
	Assert::equal(new DateTimeImmutable('2014-02-14 00:00:00'), $control->getValue());
});
