<?php

namespace Nmc9\Uploader\Database;

use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Example\User;

class BulkUpdateOrCreate{

	private $model;


	// array of Records
	private $data;

	private $updates;
	private $idFields;

	public function __construct(Model $model){
		$this->model = $model;
	}

	public static function model(Model $model){
		return new self($model);
	}

	public function set(array $records,array $idFields){
		$this->data = collect($this->strip($records));
		$this->idFields = $idFields;
		return $this;
	}

	public function setRecords(array $records){
		$this->data = collect($this->strip($records));

		return $this;
	}

	public function setIdFields(array $idFields){
		$this->idFields = $idFields;
		return $this;
	}

	public function getIdFields(){
		return $this->idFields;
	}

	public function getModel(){
		return $this->model;
	}

	public function getData(){
		return $this->data;
	}


	public function whichOnesExistInTheDatabase(){

		dd(\DB::statement('
			INSERT INTO users(user_id,name,email,password)
			VALUES(1,"nick","email@email.com","password")
			ON DUPLICATE KEY UPDATE
			user_id = VALUES(user_id),
			name = VALUES(name),
			email = VALUES(email),
			password = VALUES(password),
			'));
		// $select = "SELECT * FROM " .$this->model->getTable();
		// dd([
		// 	[
		// 		'user_id' => 1,
		// 		'name' => "nick"
		// 	],
		// 	[
		// 		'user_id' => 1,
		// 		'name' => "nicker"
		// 	]
		// ]);
		// return "SELECT * FROM " . $this->model->getTable();
		// $model->where();
	}


	public function getAllCompositeIds(){
		return $this->data->map(function($record,$index){
			return CompositeId::make($this->idFields)->get($record,$index);
		});
	}

	public function bulkInsert(){
		$table = $this->model->getTable();

		\DB::table($table)->insert($this->data);
		dd(User::all());
	}

	private function strip($records){
		return collect($records)->map(function($record){
			return $record->get();
		});
	}

}
