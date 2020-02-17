<?php

namespace Nmc9\Uploader;

use Nmc9\Uploader\Contract\AbstractUploadableModel;
use Nmc9\Uploader\Contract\UploadableContract;

class UploaderPackage
{
	/**
	 * Array of UploaderRecords
	 * @var array
	 */
	private $data;

	//Empty Model
	private $uploadable;

	public function __construct(UploadableContract $uploadable, array $data){
		$this->data = $data;
		$this->uploadable = $uploadable;
	}

	private function getValidator(){
		//get validator based on model;
		//Check the data against it
	}

	public function getData(){
		return $this->data;
	}

	public function getUploadable() : UploadableContract{
		return $this->uploadable;
	}

	public function getUploadableModel(){
		return $this->uploadable->getModel();
	}

	public function getIdFields(){
		return $this->uploadable->getUploaderIdFields();
	}

}
