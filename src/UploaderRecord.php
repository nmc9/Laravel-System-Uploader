<?php

namespace Nmc9\Uploader;

//refactor to uploader record
class UploaderRecord
{
	private $data;

	public function __construct($json){
		$this->data = $json;
	}

	public function get(){
		return $this->data;
	}

}
