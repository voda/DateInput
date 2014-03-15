<?php

use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(function() { // no value and valid value
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	Assert::false(DateInput::validateFilled($control));
	Assert::true(DateInput::validateValid($control));

	$control->setValue('2014-01-01T12:00:00');
	Assert::true(DateInput::validateFilled($control));
	Assert::true(DateInput::validateValid($control));
});

test(function() { // invalid value
	$form = new \Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$control->addRule(\Nette\Forms\Form::VALID, 'validation message');
	$form->addComponent($control, 'input');

	$control->setValue('invalid value');
	$form->validate();
	Assert::true(DateInput::validateFilled($control));
	Assert::same(array('validation message'), $form->getErrors());
});

test(function() { // range condition
	$form = new \Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$control->addRule(\Nette\Forms\Form::RANGE, 'invalid range', array(new DateTime('2014-01-01'), new DateTime('2014-12-31')));
	$form->addComponent($control, 'input');

	$form->cleanErrors();
	$control->setValue('2013-01-01');
	$form->validate();
	Assert::same(array('invalid range'), $form->getErrors());

	$form->cleanErrors();
	$control->setValue('2015-01-01');
	$form->validate();
	Assert::same(array('invalid range'), $form->getErrors());

	$form->cleanErrors();
	$control->setValue('2014-06-01');
	$form->validate();
	Assert::same(array(), $form->getErrors());
});
