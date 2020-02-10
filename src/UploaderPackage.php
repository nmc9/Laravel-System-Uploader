<?php

namespace Nmc9\Uploader;

use Nmc9\Uploader\Contract\AbstractUploadableModel;
use Nmc9\Uploader\Contract\UploaderModelContract;

class UploaderPackage
{
	/**
	 * Array of UploaderRecords
	 * @var array
	 */
	private $data;

	//Empty Model
	private $model;

	public function __construct(AbstractUploadableModel $model, array $data){
		$this->data = $data;
		$this->model = $model;
	}

	private function getValidator(){
		//get validator based on model;
		//Check the data against it
	}

	public function getData(){
		return $this->data;
	}

	public function getModel() : UploaderModelContract{
		return $this->model;
	}

	public function getIdFields(){
		return $this->model->getUploaderIdFields();
	}

}
