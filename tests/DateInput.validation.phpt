<?php

use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(function() { // no value and valid value
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$control->validate();
	Assert::false($control->isFilled());
	Assert::same(array(), $control->getErrors());

	$control->setValue('2014-01-01T12:00:00');
	$control->validate();
	Assert::true($control->isFilled());
	Assert::same(array(), $control->getErrors());
});

test(function() { // invalid value
	$form = new \Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$control->addRule(\Nette\Forms\Form::VALID, 'validation message');
	$form->addComponent($control, 'input');

	$control->setValue('invalid value');
	$control->validate();
	Assert::false($control->isFilled());
	Assert::same(array('validation message'), $control->getErrors());
});

test(function() { // range condition
	$form = new \Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$control->addRule(\Nette\Forms\Form::RANGE, 'invalid range', array(new DateTime('2014-01-01'), new DateTime('2014-12-31')));
	$form->addComponent($control, 'input');

	$control->setValue('2013-01-01');
	$control->validate();
	Assert::same(array('invalid range'), $control->getErrors());

	$control->setValue('2015-01-01');
	$control->validate();
	Assert::same(array('invalid range'), $control->getErrors());

	$control->setValue('2014-06-01');
	$control->validate();
	Assert::same(array(), $control->getErrors());
});
