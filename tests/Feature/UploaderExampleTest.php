<?php

namespace Nmc9\Uploader\Test\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Example\UploadableUser;
use Nmc9\Uploader\Example\User;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Test\TestCase;
use Nmc9\Uploader\Uploader;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use \Error;
use \Mockery;

class UploaderExampleTest extends \Orchestra\Testbench\TestCase
{

    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_works()
    {

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

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
    public function test_uploader_works_for_update()
    {

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->withFactories(__DIR__ . '/../../database/factories');

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
}
