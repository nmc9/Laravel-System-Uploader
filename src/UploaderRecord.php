<?php

namespace Nmc9\Uploader;

class UploaderRecord
{
	private $data;

	public function __construct($json){
		$this->data = $json;
	}

	public function get(){
		return $this->data;
	}

	public function __toString(){
		return $this->data;
	}

}
