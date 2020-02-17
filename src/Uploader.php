<?php

namespace Nmc9\Uploader;

use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Contract\UploaderContract;
use Nmc9\Uploader\UploaderPackage;

class Uploader implements UploaderContract
{

	private $data;
	private $model;
	private $idFields;


	public function __construct(UploaderPackage $uploaderPackage){
		$this->data = $uploaderPackage->getData();
		$this->model = $uploaderPackage->getUploadableModel();
		$this->idFields = $uploaderPackage->getIdFields();
	}


	public function dump(){
		dd($this->data);
	}


	public function upload(){
		foreach ($this->data as $uploaderData) {
			//This insures proper formatting
			$record = $uploaderData->get();
			$query = $this->queryFields($record);
			$this->model->updateOrCreate(
				$query,$record
			);
		}
		return !!$this->data;
	}

	private function queryFields($record){
		$matcherFields = [];
		foreach ($this->idFields as $idField) {
			if(!isset($record[$idField])){
				throw new NoMatchingIdKeysException("Data is Missing in the Id Field [\"" . $idField . "\"]");
			}
			$matcherFields[$idField] = $record[$idField];
		}
		return $matcherFields;
	}

}
