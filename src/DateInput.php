<?php
declare(strict_types=1);
/*
 * Copyright (c) 2011, Ondřej Vodáček
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Ondřej Vodáček nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Ondřej Vodáček BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Vodacek\Forms\Controls;

use DateTimeInterface;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Forms\Validator;

/**
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class DateInput extends BaseControl  {

	public const
			TYPE_DATETIME_LOCAL = 'datetime-local',
			TYPE_DATE = 'date',
			TYPE_MONTH = 'month',
			TYPE_TIME = 'time',
			TYPE_WEEK = 'week';

	/** @var string */
	protected $type;

	/** @var array */
	protected $range = ['min' => null, 'max' => null];

	/** @var mixed */
	protected $submittedValue = null;

	/** @var string */
	private $dateTimeClass = \DateTime::class;

	public static $defaultValidMessage = 'Please enter a valid date.';

	private static $formats = [
		self::TYPE_DATETIME_LOCAL => 'Y-m-d\TH:i:s',
		self::TYPE_DATE => 'Y-m-d',
		self::TYPE_MONTH => 'Y-m',
		self::TYPE_TIME => 'H:i:s',
		self::TYPE_WEEK => 'o-\WW'
	];

	public static function register($immutable = true, $methodName = 'addDate'): void {
		Container::extensionMethod($methodName, static function (
			Container $form,
			string $name,
			string $label = null,
			string $type = self::TYPE_DATETIME_LOCAL
		) use ($immutable) {
			$component = new self($label, $type, $immutable);
			$form->addComponent($component, $name);
			$component->setRequired(false);
			$component->addRule([__CLASS__, 'validateValid'], self::$defaultValidMessage);
			return $component;
		});
		Validator::$messages[__CLASS__.'::validateDateInputRange'] = Validator::$messages[Form::RANGE];
	}

	/**
	 * @param string|null $label
	 * @param string $type
	 * @param bool $immutable
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $label = null, string $type = self::TYPE_DATETIME_LOCAL, bool $immutable = true) {
		if (!isset(self::$formats[$type])) {
			throw new \InvalidArgumentException("invalid type '$type' given.");
		}
		parent::__construct($label);
		$this->control->type = $this->type = $type;
		$this->control->data('dateinput-type', $type);

		if ($immutable) {
			$this->dateTimeClass = \DateTimeImmutable::class;
		}
	}

	public function setValue($value = null) {
		if ($value === null || $value instanceof DateTimeInterface) {
			$this->value = $value;
			$this->submittedValue = null;
		} elseif ($value instanceof \DateInterval) {
			$this->value = $this->createFromFormat(self::$formats[self::TYPE_TIME], $value->format('%H:%I:%S'));
			$this->submittedValue = null;
		} elseif (is_string($value)) {
			if ($value === '') {
				$this->value = null;
				$this->submittedValue = null;
			} else {
				$this->value = $this->parseValue($value);
				if ($this->value !== null) {
					$this->submittedValue = null;
				} else {
					$this->value = null;
					$this->submittedValue = $value;
				}
			}
		} else {
			$this->submittedValue = $value;
			throw new \InvalidArgumentException("Invalid type for $value.");
		}
		return $this;
	}

	public function getControl() {
		$control = parent::getControl();
		$format = self::$formats[$this->type];
		if ($this->value !== null) {
			$control->value = $this->value->format($format);
		}
		if ($this->submittedValue !== null && is_string($this->submittedValue)) {
			$control->value = $this->submittedValue;
		}
		if ($this->range['min'] !== null) {
			$control->min = $this->range['min']->format($format);
		}
		if ($this->range['max'] !== null) {
			$control->max = $this->range['max']->format($format);
		}
		return $control;
	}

	public function addRule($operation, $message = null, $arg = null) {
		if ($operation === Form::RANGE) {
			$this->range['min'] = $this->normalizeDate($arg[0]);
			$this->range['max'] = $this->normalizeDate($arg[1]);
			$operation = __CLASS__.'::validateDateInputRange';
			$arg[0] = $this->formatDate($arg[0]);
			$arg[1] = $this->formatDate($arg[1]);
		}
		return parent::addRule($operation, $message, $arg);
	}

	public static function validateFilled(IControl $control): bool {
		if (!$control instanceof self) {
			throw new \InvalidArgumentException("Cant't validate control '".\get_class($control)."'.");
		}
		return ($control->value !== null || $control->submittedValue !== null);
	}

	public static function validateValid(IControl $control): bool {
		if (!$control instanceof self) {
			throw new \InvalidArgumentException("Cant't validate control '".\get_class($control)."'.");
		}
		return $control->submittedValue === null;
	}

	public static function validateDateInputRange(self $control): bool {
		if (($control->range['min'] !== null) && $control->range['min'] > $control->value) {
			return false;
		}

		if (($control->range['max'] !== null) && $control->range['max'] < $control->value) {
			return false;
		}

		return true;
	}

	private function parseValue(string $value): ?DateTimeInterface {
		$date = null;
		if ($this->type === self::TYPE_WEEK) {
			try {
				$date = $this->createDateTime($value. '1');
			} catch (\Exception $e) {
				$date = null;
			}
		} else {
			$date = $this->createFromFormat('!'.self::$formats[$this->type], $value);
		}
		return $date;
	}

	private function formatDate(?DateTimeInterface $value = null): ?string {
		if ($value === null) {
			return null;
		}

		return $value->format(self::$formats[$this->type]);
	}

	private function normalizeDate(?DateTimeInterface $value): ?DateTimeInterface {
		if ($value === null) {
			return null;
		}

		return $this->parseValue($this->formatDate($value));
	}

	private function createDateTime(string $string): DateTimeInterface
	{
		return new $this->dateTimeClass($string);
	}

	private function createFromFormat(string $string): ?DateTimeInterface
	{
		$val = call_user_func_array([$this->dateTimeClass, 'createFromFormat'], func_get_args());
		return $val === false ? null : $val;
	}
}
