<?php

namespace Nmc9\Uploader\Test\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\Models\CustomerBalance;
use Nmc9\Uploader\Database\Models\User;
use Nmc9\Uploader\Database\Uploadables\UploadableCustomerBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableUser;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Method\UploadMethodDuplicateOn;
use Nmc9\Uploader\Method\UploadMethodUpdateOrCreate;
use Nmc9\Uploader\Tests\LaravelTestCase;
use Nmc9\Uploader\Uploader;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use \Error;
use \Mockery;

class UploaderUpdateOrCreateTest extends LaravelTestCase
{

    public $runMigrations = true;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_inserts_uploader_package_records_to_database()
    {

        // Fake Uploader Record
        $uploaderData = [
            new UploaderRecord([
                "user_id" => 1,
                'name' => "Nick",
                "email" => "timcook@gmail.com",
                "password" => "GillBates"
            ])
        ];

        $uploaderPackage = new UploaderPackage(new UploadableUser(),$uploaderData);

        $uploader = new Uploader($uploaderPackage);
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('users',[
            "user_id" => 1,
            'name' => "Nick",
            "email" => "timcook@gmail.com",
            "password" => "GillBates"
        ]);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_updates_uploader_package_records_in_database()
    {
        $user_id = factory(User::class)->create()->user_id;

        // Fake Uploader Record
        $uploaderData = [
            new UploaderRecord([
                "user_id" => $user_id,
                'name' => "Nick",
                "email" => "timcook@gmail.com",
                "password" => "GillBates"
            ])
        ];

        $uploaderPackage = new UploaderPackage(new UploadableUser(),$uploaderData);

        $uploader = new Uploader($uploaderPackage);
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('users',[
            "user_id" => $user_id,
            'name' => "Nick",
            "email" => "timcook@gmail.com",
            "password" => "GillBates"
        ]);
    }
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_inserts_package_records_with_specified_updateOrCreateMethod()
    {
        // Fake Uploader Record
        $uploaderData = [
            new UploaderRecord([
                "user_id" => 1,
                'name' => "Nick",
                "email" => "timcook@gmail.com",
                "password" => "GillBates"
            ])
        ];

        $uploaderPackage = new UploaderPackage(new UploadableUser(),$uploaderData);

        $uploader = new Uploader($uploaderPackage, new UploadMethodUpdateOrCreate());
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('users',[
            "user_id" => 1,
            'name' => "Nick",
            "email" => "timcook@gmail.com",
            "password" => "GillBates"
        ]);
    }

    public function test_uploader_returns_false_when_there_is_no_data_in_package(){
        $uploaderData = [
        ];

        $uploaderPackage = new UploaderPackage(new UploadableUser(),$uploaderData);

        $uploader = new Uploader($uploaderPackage);
        $this->assertFalse($uploader->upload());
    }

    public function test_uploader_throws_query_exception_when_unused_field_is_passed(){
        $uploaderData = [
            new UploaderRecord([
                'user_id' => 1,
                "test" => 1,
            ])
        ];

        $this->expectException(QueryException::class);

        $uploaderPackage = new UploaderPackage(new UploadableUser(),$uploaderData);

        $uploader = new Uploader($uploaderPackage, new UploadMethodUpdateOrCreate());
        $this->assertFalse($uploader->upload());
    }

    public function test_uploader_throws_missing_key_error_when_key_field_is_missing(){
        $uploaderData = [
            new UploaderRecord([
                "test" => 1,
            ])
        ];

        $this->expectException(NoMatchingIdKeysException::class);

        $uploaderPackage = new UploaderPackage(new UploadableUser(),$uploaderData);

        $uploader = new Uploader($uploaderPackage, new UploadMethodUpdateOrCreate());
        $this->assertFalse($uploader->upload());

    }

}
