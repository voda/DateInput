<?php

use Nette\Forms\Form;
use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(function() { // no value and valid value
	$form = new Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'date');

	$control->validate();
	Assert::false($control->hasErrors());

	$control->setValue('2014-01-01T12:00:00');
	$control->validate();
	Assert::false($control->hasErrors());
});

test(function() { // no value and valid value for required input
	$form = new Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'date');
	$control->setRequired();

	$control->validate();
	Assert::true($control->hasErrors());

	$control->setValue('2014-01-01T12:00:00');
	$control->validate();
	Assert::false($control->hasErrors());
});

test(function() { // invalid value
	$form = new Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'input');

	$control->setValue('invalid value');
	$control->validate();
	Assert::false($control->hasErrors());
});

test(function() { // range condition
	$form = new Form();
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$control->addRule(Form::RANGE, 'invalid range', array(new DateTime('2014-01-01'), new DateTime('2014-12-31')));
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
