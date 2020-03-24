<?php

namespace Nmc9\Uploader\Test\Feature;

use Nmc9\Uploader\Exceptions\InvalidDatabaseException;
use Nmc9\Uploader\Method\UploadMethodOnDuplicate;
use Nmc9\Uploader\Method\UploadMethodOnDuplicateRaw;
use Nmc9\Uploader\Tests\LaravelTestCase;
use \Error;
use \Mockery;

class OnDuplicateMysqlTest extends LaravelTestCase
{
    public $runMigrations = false;

    public function test_non_mysql_driver_throws_exception(){

        config()->set('database.default',"sqlite");
        $this->expectException(InvalidDatabaseException::class);
        $this->expectExceptionMessage("Invalid Database: (Excepted [mysql] but the driver is [sqlite])");
        new UploadMethodOnDuplicate();
    }

    public function test_non_mysql_driver_throws_exception_on_other_connection(){
        config()->set('database.connections.fakection',[
            'driver' => 'mongodb',
        ]);
        $this->expectException(InvalidDatabaseException::class);
        $this->expectExceptionMessage("Invalid Database: (Excepted [mysql] but the driver is [mongodb])");
        new UploadMethodOnDuplicate(true,'fakection');
    }

    public function test_non_mysql_driver_throws_exception_raw(){

        config()->set('database.default',"sqlite");
        $this->expectException(InvalidDatabaseException::class);
        $this->expectExceptionMessage("Invalid Database: (Excepted [mysql] but the driver is [sqlite])");
        new UploadMethodOnDuplicateRaw();
    }

}
