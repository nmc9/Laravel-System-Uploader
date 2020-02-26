<?php

namespace Nmc9\Uploader\Tests;

abstract class MySqlOnlyTestCase extends LaravelTestCase
{

	public function setup(): void{
		parent::setup();
		$this->checkMysql();
	}

	private function checkMysql(){
		if(env('DB_CONNECTION') !== "mysql"){
			$this->markTestSkipped(
				'This test can only be run in MySQL'
			);
		}
	}

}
