<?php
declare(strict_types=1);

namespace Vodacek\Forms\Controls;

use Nette;

class Extension extends Nette\DI\CompilerExtension
{


	/**
	 * @param Nette\PhpGenerator\ClassType $class
	 * @return void
	 */
	public function afterCompile(Nette\PhpGenerator\ClassType $class){
		parent::afterCompile($class);
		$init = $class->methods['initialize'];
		/** @see DateInput::register() */
		$init->addBody('Vodacek\Forms\Controls\DateInput::register();');
	}

}
