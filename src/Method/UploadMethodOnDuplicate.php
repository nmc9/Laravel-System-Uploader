<?php

namespace Nmc9\Uploader\Method;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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

	private $updated_at_key = null;
	private $created_at_key = null;
	private $runTimestamps = true;

	public function __construct($check = true){
		$this->check = $check;
	}

	public function turnOffCheck(){
		$this->check = false;
		return $this;
	}

	public function turnOnCheck(){
		$this->check = true;
		return $this;
	}

	public function turnOffTimestamps(){
		$this->runTimestamps = false;
		return $this;
	}

	public function turnOnTimestamps(){
		$this->runTimestamps = true;
		return $this;
	}

	public function handle(UploadableContract $uploadable,$data){
		$this->setUploadable($uploadable);
		$this->setData($data);
		return $this->upload();
	}


	public function setUploadable(UploadableContract $uploadable){
		$model = $uploadable->getModel();
		$this->table = $model->getTable();
		$this->setTimestamps($model);
		$this->idFields = $uploadable->getUploaderIdFields();
	}

	public function setTimestamps(Model $model){
		if($model->timestamps){
			$this->updated_at_key = $model::UPDATED_AT;
			$this->created_at_key = $model::CREATED_AT;
		}
	}

	public function setData($data){
		$this->records = $data;
	}

	public function getTimestamps(){
		return [
			"updated_at" => $this->updated_at_key,
			"created_at" => $this->created_at_key
		];
	}

	public function checkIdFields(){
		if($this->check){
			$this->checkKeys();
			$this->checkMissingIdFields();
		}
	}

	public function upload(){
		$this->checkIdFields();
		$generator = OnDuplicateGenerator::make();
		if($this->runTimestamps()){
			$generator = $this->setGeneratorTimestamps($generator);
		}

		$bulk = $generator->generate($this->table,$this->records);

		return $bulk != null ?
		DB::statement($bulk->getQuery(),$bulk->getBindings()) :
		false;
	}

	protected function getNow(){
		return Carbon::now();
	}

	protected function runTimestamps(){
		return $this->runTimestamps;
	}

	private function setGeneratorTimestamps(OnDuplicateGenerator $generator){
		return $generator->setTimestamps($this->getNow(),$this->getTimestamps()["updated_at"],$this->getTimestamps()["created_at"]);
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
