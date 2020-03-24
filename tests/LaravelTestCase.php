<?php

namespace Nmc9\Uploader\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class LaravelTestCase extends BaseTestCase
{
	public $runMigrations = false;

	public function setup(): void{
		parent::setup();
		if($this->runMigrations){
			$this->runMigrations();
		}
	}

	public function runMigrations(){
		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
		$this->withFactories(__DIR__ . '/../database/factories');
	}
}
