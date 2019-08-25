<?php
declare(strict_types=1);

use Nette\Forms\Form;
use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(static function() {
	$form = new Form();
	$control = new DateInput('date', DateInput::TYPE_DATE);
	$form->addComponent($control, 'input');

	$control->addCondition(Form::FILLED);

	$control->setValue('2000-01-01');

	$form->isValid();

	Assert::false($control->hasErrors());
	Assert::type(DateTimeImmutable::class, $control->getValue());
});