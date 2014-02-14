<?php
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

use Nette\Forms\IControl,
	Nette\Forms\Controls\BaseControl;

/**
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class DateInput extends BaseControl  {

	const TYPE_DATETIME = 'datetime',
			TYPE_DATETIME_LOCAL = 'datetime-local',
			TYPE_DATE = 'date',
			TYPE_MONTH = 'month',
			TYPE_TIME = 'time',
			TYPE_WEEK = 'week';

	/** @var string */
	protected $type;

	/** @var array */
	protected $range = array('min' => null, 'max' => null);

	/** @var mixed */
	protected $submitedValue = null;

	private static $formats = array(
		self::TYPE_DATETIME => 'Y-m-d\TH:i:se',
		self::TYPE_DATETIME_LOCAL => 'Y-m-d\TH:i:s',
		self::TYPE_DATE => 'Y-m-d',
		self::TYPE_MONTH => 'Y-m',
		self::TYPE_TIME => 'H:i:s',
		self::TYPE_WEEK => 'o-\WW'
	);

	public static function register() {
		$class = __CLASS__;
		\Nette\Forms\Container::extensionMethod('addDate', function (\Nette\Forms\Container $form, $name, $label = null, $type = 'datetime-local') use ($class) {
			$component = new $class($label, $type);
			$form->addComponent($component, $name);
			return $component;
		});
		\Nette\Forms\Rules::$defaultMessages[__CLASS__.'::validateDateInputRange'] = \Nette\Forms\Rules::$defaultMessages[\Nette\Forms\Form::RANGE];
		\Nette\Forms\Rules::$defaultMessages[__CLASS__.'::validateDateInputValid'] = 'Please enter a valid date.';
	}

	/**
	 * @param string
	 * @param string
	 * @throws \InvalidArgumentException
	 */
	public function __construct($label = null, $type = self::TYPE_DATETIME_LOCAL) {
		if (!isset(self::$formats[$type])) {
			throw new \InvalidArgumentException("invalid type '$type' given.");
		}
		parent::__construct($label);
		$this->control->type = $this->type = $type;
		$this->control->data('dateinput-type', $type);
	}

	public function setValue($value = null) {
		if ($value === null || $value instanceof \DateTime) {
			$this->value = $value;
			$this->submitedValue = null;
		} elseif ($value instanceof \DateInterval) {
			$this->value = \DateTime::createFromFormat(self::$formats[self::TYPE_TIME], $value->format("%H:%I:%S"));
			$this->submitedValue = null;
		} elseif (is_string($value)) {
			if ($value === '') {
				$this->value = null;
				$this->submitedValue = null;
			} else {
				$this->value = $this->parseValue($value);
				if ($this->value !== false) {
					$this->submitedValue = null;
				} else {
					$this->value = null;
					$this->submitedValue = $value;
				}
			}
		} else {
			$this->submitedValue = $value;
			throw new \InvalidArgumentException("Invalid type for \$value.");
		}
		return $this;
	}

	public function getControl() {
		$control = parent::getControl();
		$format = self::$formats[$this->type];
		if ($this->value !== null) {
			$control->value = $this->value->format($format);
		}
		if ($this->submitedValue !== null && is_string($this->submitedValue)) {
			$control->value = $this->submitedValue;
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
		if ($operation === \Nette\Forms\Form::RANGE) {
			$this->range['min'] = $this->normalizeDate($arg[0]);
			$this->range['max'] = $this->normalizeDate($arg[1]);
			$operation = __CLASS__.'::validateDateInputRange';
			$arg[0] = $this->formatDate($arg[0]);
			$arg[1] = $this->formatDate($arg[1]);
		} elseif ($operation === \Nette\Forms\Form::VALID) {
			$operation = __CLASS__.'::validateDateInputValid';
		}
		return parent::addRule($operation, $message, $arg);
	}

	public static function validateFilled(IControl $control) {
		if (!$control instanceof self) {
			throw new \InvalidArgumentException("Cant't validate control '".\get_class($control)."'.");
		}
		return ($control->value !== null || $control->submitedValue !== null);
	}

	/**
	 * Valid validator: is control valid?
	 * @param  IControl
	 * @return bool
	 */
	public static function validateDateInputValid(IControl $control) {
		return self::validateValid($control);
	}

	public static function validateValid(IControl $control) {
		if (!$control instanceof self) {
			throw new \InvalidArgumentException("Cant't validate control '".\get_class($control)."'.");
		}
		return $control->submitedValue === null;
	}

	/**
	 * @param self $control
	 * @param array $args
	 * @return bool
	 */
	public static function validateDateInputRange(self $control) {
		if ($control->range['min'] !== null) {
			if ($control->range['min'] > $control->value) {
				return false;
			}
		}
		if ($control->range['max'] !== null) {
			if ($control->range['max'] < $control->value) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $value
	 * @return \DateTime
	 */
	private function parseValue($value) {
		$date = null;
		if ($this->type === self::TYPE_WEEK) {
			try {
				$date = new \DateTime($value."1");
			} catch (\Exception $e) {
				$date = false;
			}
		} else {
			$date = \DateTime::createFromFormat('!'.self::$formats[$this->type], $value);
		}
		return $date;
	}

	/**
	 * @param \DateTime $value
	 * @return string
	 */
	private function formatDate(\DateTime $value = null) {
		if ($value) {
			$value = $value->format(self::$formats[$this->type]);
		}
		return $value;
	}

	/**
	 * @param \DateTime
	 * @return \DateTime
	 */
	private function normalizeDate(\DateTime $value = null) {
		if ($value) {
			$value = $this->formatDate($value);
			$value = $this->parseValue($value);
		}
		return $value;
	}
}
