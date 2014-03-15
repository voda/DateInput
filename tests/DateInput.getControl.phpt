<?php

use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(function() {
	$form = new Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'input');

	Assert::type('Nette\Utils\Html', $control->getControl());
	Assert::same('input', $control->getControl()->getName());
	Assert::contains('type="datetime-local"', (string)$control->getControl());
	Assert::contains('data-dateinput-type="datetime-local"', (string)$control->getControl());
});

test(function() { // min & max attributes
	$form = new Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'd');

	$control->addRule(Nette\Forms\Form::RANGE, 'message', array(new DateTime('2014-01-01 12:00:00'), new DateTime('2014-12-31 12:00:00')));
	Assert::contains('min="2014-01-01T12:00:00"', (string)$control->getControl());
	Assert::contains('max="2014-12-31T12:00:00"', (string)$control->getControl());
});

test(function() { // valid & invalid value
	$form = new Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'd');

	$control->setValue(new DateTime('2014-06-01 12:13:14'));
	Assert::contains('value="2014-06-01T12:13:14"', (string)$control->getControl());

	$control->setValue('fooo');
	Assert::contains('value="fooo"', (string)$control->getControl());
});
