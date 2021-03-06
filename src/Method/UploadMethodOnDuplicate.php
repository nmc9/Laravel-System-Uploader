<?php

namespace Nmc9\Uploader\Method;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Contract\UploadMethodContract;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\DBStatement;
use Nmc9\Uploader\Exceptions\InvalidDatabaseException;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Exceptions\UploaderQueryException;
use Nmc9\Uploader\Kfir\OnDuplicateGenerator;
use Nmc9\Uploader\Kfir\ShowKeysGenerator;
use Nmc9\Uploader\OnDuplicateUploader;

class UploadMethodOnDuplicate implements UploadMethodContract
{

	private $check;
	private $connection;

	private $table;
	private $idFields;
	private $records;

	private $chunk_size = 250;

	private $updated_at_key = null;
	private $created_at_key = null;
	private $runTimestamps = true;

	public function __construct($check = true, $connection = null){
		$this->check = $check;
		$this->connection = $connection;
		$this->checkMysql();
	}

	private function checkMysql(){
		$database = $this->connection == null ? config('database.default') : $this->connection;
		$driver = config("database.connections." . $database . ".driver");
		if($driver !== "mysql"){
			throw new InvalidDatabaseException($driver,"mysql");
		}
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
		// var_dump("run upload" . \Carbon\Carbon::now()->toDateTimeString());
		$this->checkIdFields();
		$generator = OnDuplicateGenerator::make();
		if($this->runTimestamps()){
			$generator = $this->setGeneratorTimestamps($generator);
		}
		$success = false;
		// $i = 0;
		foreach (array_chunk($this->records,$this->chunk_size) as $chunk) {
			$bulk = $generator->generate($this->table,$chunk);
			if($bulk == null){
				return false;
			}
			try{
				$success = DBStatement::execute($bulk,$this->connection);
				if(!$success){
					return false;
				}

				// if($i++ % 10 == 0){
					// var_dump($i * $this->chunk_size);
				// }
			}catch(QueryException $e){
				$previous = $e->getPrevious();
				$message = $previous != null ? $previous->getMessage() : "There was a problem running the query";
				throw new UploaderQueryException($message);
			}catch(\PDOException $e){
				throw new UploaderQueryException($e->getMessage());
			}
		}
		return $success;
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
		return collect(DBStatement::select($show->getQuery(),$show->getBindings(),$this->connection));
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
