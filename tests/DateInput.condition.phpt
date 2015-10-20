<?php

use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(
	function() {
	$form = new Nette\Forms\Form();
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$form->addComponent($control, 'input');

	$control->addCondition(\Nette\Forms\Form::FILLED)
            ->addRule(\Nette\Forms\Form::VALID, "invalid date");

	$control->setValue('2000-01-01');

	$form->isValid();

	Assert::false($control->hasErrors());
	Assert::type('DateTime', $control->getValue());
});