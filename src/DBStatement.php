<?php

namespace Nmc9\Uploader;

use Illuminate\Support\Facades\DB;

class DBStatement
{


	public static function execute($bulk, $connection = null){
		$pdo = DB::connection($connection)->getPdo();
		$pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		return $pdo->prepare($bulk->getQuery())->execute($bulk->getBindings());
	}

	public static function select($query,$bind,$connection = null){
		return DB::connection($connection)->select($query,$bind);
	}
}
