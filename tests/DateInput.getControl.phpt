<?php
declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Vodacek\Forms\Controls\DateInput;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';

test(static function() {
	$form = new Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'input');

	Assert::type(Html::class, $control->getControl());
	Assert::same('input', $control->getControl()->getName());
	Assert::contains('type="datetime-local"', (string)$control->getControl());
	Assert::contains('data-dateinput-type="datetime-local"', (string)$control->getControl());
});

test(static function() { // min & max attributes
	$form = new Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'd');

	$control->addRule(Nette\Forms\Form::RANGE, 'message', [new DateTimeImmutable('2014-01-01 12:00:00'), new DateTimeImmutable('2014-12-31 12:00:00')]);
	Assert::contains('min="2014-01-01T12:00:00"', (string)$control->getControl());
	Assert::contains('max="2014-12-31T12:00:00"', (string)$control->getControl());
});

test(static function() { // valid & invalid value
	$form = new Form();
	$control = new DateInput('date', DateInput::TYPE_DATETIME_LOCAL);
	$form->addComponent($control, 'd');

	$control->setValue(new DateTimeImmutable('2014-06-01 12:13:14'));
	Assert::contains('value="2014-06-01T12:13:14"', (string)$control->getControl());

	$control->setValue('fooo');
	Assert::contains('value="fooo"', (string)$control->getControl());
});
