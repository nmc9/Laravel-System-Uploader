<?php

namespace Nmc9\Uploader;

use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Contract\UploadMethodContract;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\OnDuplicateUploader;

class UploadMethodDuplicateOn implements UploadMethodContract
{

	private $check;

	public function __construct($check = true){
		$this->check = $check;
	}

	private function turnOffCheck(){
		$this->check = false;
	}

	private function turnOnCheck(){
		$this->check = true;
	}


	public function handle(UploadableContract $uploadable,$data){
		$uploader = new OnDuplicateUploader($uploadable,$this->check);

		return $uploader->upload($data);
	}

}
