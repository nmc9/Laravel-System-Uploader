<?php

namespace Nmc9\Uploader\Exceptions;

use Exception;

class InvalidDatabaseException extends Exception
{

	public $expected;
	public $actual;

	public function __construct($actual,$expected,$message = null, $code = 0, Exception $previous = null) {
		$this->expected = $expected;
		$this->actual = $actual;

		$message = $message ?? $this->makeMessage($expected,$actual);

		parent::__construct($message, $code, $previous);
	}

	private function makeMessage($expected,$actual){
		return sprintf('Invalid Database: (Excepted [%s] but the driver is [%s])',
			$this->expected,
			$this->actual
		);
	}

	public function getExpected(){
		return $this->expected;
	}

	public function getActual()
	{
		return $this->actual;
	}
}
