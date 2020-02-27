<?php

namespace Nmc9\Uploader\Exceptions;

use Exception;

class NoMatchingIdKeysException extends Exception
{

	public $index;
	public $missing;

	public function __construct($idField,$index = -1,$message = null, $code = 0, Exception $previous = null) {
		$this->index = $index;
		$this->missing = $idField;
		$message = $message ?? $this->makeMessage($idField,$index);

		parent::__construct($message, $code, $previous);
	}

	private function makeMessage($idField,$index){
		$message = sprintf('Data is Missing in the Id Field ["%s"]', $idField);
		if($index >= 0){
			$message .= sprintf(' at index ["%s"]', $index);
		}
		return $message;
	}

	public function getIndex(){
		return $this->index;
	}

	public function getMissing()
	{
		return $this->missing;
	}
}
