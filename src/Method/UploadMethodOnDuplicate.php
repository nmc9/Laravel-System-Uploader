<?php

namespace Nmc9\Uploader\Method;

use Illuminate\Support\Facades\DB;
use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Contract\UploadMethodContract;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Kfir\OnDuplicateGenerator;
use Nmc9\Uploader\Kfir\ShowKeysGenerator;
use Nmc9\Uploader\OnDuplicateUploader;

class UploadMethodOnDuplicate implements UploadMethodContract
{

	private $check;
	private $table;
	private $idFields;
	private $records;

	public function __construct($check = true){
		$this->check = $check;
	}

	public function turnOffCheck(){
		$this->check = false;
	}

	public function turnOnCheck(){
		$this->check = true;
	}


	public function handle(UploadableContract $uploadable,$data){
		$this->setUploadable($uploadable);
		$this->setData($data);
		return $this->upload();
	}
	public function doDB(){
		\DB::select("SELECT * FROM users");
	}

	public function setUploadable(UploadableContract $uploadable){
		$this->table = $uploadable->getModel()->getTable();
		$this->idFields = $uploadable->getUploaderIdFields();
	}

	public function setData($data){
		$this->records = $data;
	}

	public function upload(){
		$this->checkIdFields();
		$bulk = OnDuplicateGenerator::make()->generate($this->table,$this->records);
		// dd($bulk->getQuery(),$bulk->getBindings());
		return $bulk != null ?
		DB::statement($bulk->getQuery(),$bulk->getBindings()) :
		false;
	}

	public function checkIdFields(){
		if($this->check){
			$this->checkKeys();
			$this->checkMissingIdFields();
		}
	}

	private function checkKeys(){
		if(empty($this->idFields)){
			return;
		}
		$keysList = $this->showKeys($this->table,$this->idFields)->unique("Column_name");
		if($keysList->count() < count($this->idFields)){
			$this->throwMissingUniqueContraintException($keysList,$this->idFields);
		}
	}

	private function showKeys($table,$idFields){
		$show = ShowKeysGenerator::make()->generate($table,$idFields);
		return collect(\DB::select($show->getQuery(),$show->getBindings()));
	}

	private function throwMissingUniqueContraintException($keysList,$idFields){
		$received = $keysList->pluck("Column_name")->toArray();
		throw new MissingUniqueContraintException($idFields,$received);
	}

	private function throwNoMatchingIdKeysException($missing){
		throw new NoMatchingIdKeysException($missing);
	}

	private function checkMissingIdFields(){
		if(isset($this->records[0])){
			$rowFields = array_keys($this->records[0]->get());
			foreach(array_diff($this->idFields,$rowFields) as $value) {
				$this->throwNoMatchingIdKeysException($value);
				return;
			}
		}
	}

}
