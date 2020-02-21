<?php

namespace Nmc9\Uploader;

use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;

class CompositeId
{

	private $idFields;

	public static function make($idFields){
		return new self($idFields);
	}

	public function __construct($idFields){
		$this->idFields = $idFields;
	}

	public function get($record,$index = -1){
		$matcherFields = [];
		foreach ($this->idFields as $idField) {
			if(!isset($record[$idField])){
				$this->throwException($idField,$index);
			}
			$matcherFields[$idField] = $record[$idField];
		}
		return $matcherFields;
	}

	private function throwException($idField,$index){
		throw new NoMatchingIdKeysException($idField,$index);
	}
}
