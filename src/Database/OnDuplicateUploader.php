<?php

namespace Nmc9\Uploader\Database;

use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Example\User;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Kfir\OnDuplicateGenerator;
use Nmc9\Uploader\Kfir\ShowKeysGenerator;


class OnDuplicateUploader{

	private $table;
	private $idFields;

	public function __construct(UploadableContract $uploadable,$checkIdFields = true){
		$this->table = $uploadable->getModel()->getTable();
		$this->idFields = $uploadable->getUploaderIdFields();
		$this->check = $checkIdFields;
	}

	public function upload($records){

		$this->checkIdFields();

		$bulk = (new OnDuplicateGenerator())->generate($this->table,$records);
		return \DB::statement($bulk->getQuery(),$bulk->getBindings());
	}

	public function checkIdFields(){
		if($this->check){
			$keysList = $this->showKeys($this->table,$this->idFields)->unique("Column_name");
			if($keysList->count() < count($this->idFields)){
				$this->throwMissingUniqueContraintException($keysList,$this->idFields);
			}
		}
	}

	private function showKeys($table,$idFields){
		$show =  ShowKeysGenerator::make()->generate($table,$idFields);
		return collect(\DB::select($show->getQuery(),$show->getBindings()));
	}

	private function throwMissingUniqueContraintException($keysList,$idFields){
		$received = $keysList->pluck("Column_name")->toArray();
		throw new MissingUniqueContraintException($idFields,$received);
	}
}
