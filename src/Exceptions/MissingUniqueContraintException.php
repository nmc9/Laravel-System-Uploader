<?php

namespace Nmc9\Uploader\Exceptions;

use Exception;

class MissingUniqueContraintException extends Exception
{

	public $expected;
	public $inDatabase;

	public function __construct($expected,$inDatabase,$message = null, $code = 0, Exception $previous = null) {
		$this->expected = $expected;
		$this->inDatabase = $inDatabase;

		$message = $message ?? sprintf('Expected Unique Constraint [%s] but only [%s] %s in the database',
			$this->reduce($expected),
			$this->reduce($inDatabase),
			count($inDatabase) > 1 ? "are" : "is"
		);

		parent::__construct($message, $code, $previous);
	}

	public function getExpected(){
		return $this->expected;
	}

	public function getInDatabase()
	{
		return $this->inDatabase;
	}

	private function reduce($array_reduce){
		return trim(array_reduce($array_reduce,function($group,$item){
			return $group . ',"' . $item . '"';
		}),",");
	}
}
