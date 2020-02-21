<?php

namespace Nmc9\Uploader\Exceptions;

use Exception;

class NoMatchingIdKeysException extends Exception
{

	public $index;
	public $missing;

	public function __construct($idField,$index,$message = null, $code = 0, Exception $previous = null) {
		$this->index = $index;
		$this->missing = $idField;
		$message = $message ?? sprintf('Data is Missing in the Id Field ["%s"] at index ["%s"]', $idField, $index);

		parent::__construct($message, $code, $previous);
	}

	public function getIndex(){
		return $this->index;
	}

	public function getMissing()
	{
		return $this->missing;
	}
}
