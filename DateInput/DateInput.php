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

use Nette\Forms\IFormControl,
	Nette\Forms\FormControl;

/**
 * @author Ondřej Vodáček <ondrej.vodacek@gamil.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class DateInput extends FormControl {

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
		self::TYPE_WEEK => 'Y-\WW'
	);

	public static function register() {
		$class = __CLASS__;
		\Nette\Forms\FormContainer::extensionMethod('addDate', function (\Nette\Forms\FormContainer $form, $name, $label = null, $type = 'datetime-local') use ($class) {
			$component = new $class($label, $type);
			$form->addComponent($component, $name);
			return $component;
		});
	}

	public function __construct($label = null, $type = self::TYPE_DATETIME_LOCAL) {
		if (!isset(self::$formats[$type])) {
			throw new \InvalidArgumentException("invalid type '$type' given.");
		}
		parent::__construct($label);
		$this->control->type = $this->type = $type;
		$this->control->{'data-dateinput-type'} = $type;
	}

	/**
	 * Sets control's value.
	 * @param  mixed
	 * @return BaseControl  provides a fluent interface
	 */
	public function setValue($value = null) {
		if ($value === null || $value instanceof \DateTime) {
			$this->value = $value;
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

	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getControl() {
		$control = parent::getControl();
		if ($this->value !== null) {
			$control->value = $this->value->format(self::$formats[$this->type]);
		}
		if ($this->submitedValue !== null && is_string($this->submitedValue)) {
			$control->value = $this->submitedValue;
		}
		if ($this->range['min'] !== null) {
			$control->min = $this->range['min']->format(self::$formats[$this->type]);
		}
		if ($this->range['max'] !== null) {
			$control->max = $this->range['max']->format(self::$formats[$this->type]);
		}
		return $control;
	}

	/**
	 * Adds a validation rule.
	 * @param  mixed      rule type
	 * @param  string     message to display for invalid data
	 * @param  mixed      optional rule arguments
	 * @return BaseControl  provides a fluent interface
	 */
	public function addRule($operation, $message = NULL, $arg = NULL) {
		if ($operation === \Nette\Forms\Form::RANGE) {
			$this->range['min'] = $arg[0];
			$this->range['max'] = $arg[1];
			$this->addRule(function(DateInput $control, $range) {
				if ($range['min'] !== null) {
					if ($range['min'] > $control->getValue()) {
						return false;
					}
				}
				if ($range['max'] !== null) {
					if ($range['max'] < $control->getValue()) {
						return false;
					}
				}
				return true;
			}, $message, $this->range);
			return $this;
		}
		return parent::addRule($operation, $message, $arg);
	}



	/**
	 * Filled validator: is control filled?
	 * @param  IControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control) {
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
	public static function validateValid(IFormControl $control) {
		if (!$control instanceof self) {
			throw new \InvalidArgumentException("Cant't validate control '".\get_class($control)."'.");
		}
		return $control->submitedValue === null;
	}

	/**
	 *
	 * @param self $control
	 * @param array $args
	 * @return bool
	 */
	public static function validateRange(self $control) {
		if ($control->range['min'] !== null) {
			if ($control->range['min'] > $control->getValue()) {
				return false;
			}
		}
		if ($control->range['max'] !== null) {
			if ($control->range['max'] < $control->getValue()) {
				return false;
			}
		}
		return true;
	}

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
}
