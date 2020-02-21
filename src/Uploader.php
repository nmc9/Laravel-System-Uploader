<?php

namespace Nmc9\Uploader;

use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Contract\UploadMethodContract;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Contract\UploaderContract;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\UploadMethodUpdateOrCreate;
use Nmc9\Uploader\UploaderPackage;

class Uploader implements UploaderContract
{

	private $data;
	private $uploadable;
	private $method;


	public function __construct(UploaderPackage $uploaderPackage,UploadMethodContract $method = null){
		$this->data = $uploaderPackage->getData();
		$this->uploadable = $uploaderPackage->getUploadable();
		$this->method = $method ?? new UploadMethodUpdateOrCreate();
	}


	public function dump(){
		dd($this->data);
	}


	public function upload(){
		return $this->method->handle($this->uploadable,$this->data);
	}

}
