<?php

namespace Tests\Uploader\Feature;

use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Uploader;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\Example\User;
use Nmc9\Uploader\Example\UploadableUser;
use \Error;
use \Mockery;
class UploaderExampleTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_works()
    {
        // Fake Uploader Record
        $uploaderRecord = Mockery::mock(UploaderRecord::class);
        $uploaderRecord->shouldReceive('get')->once()->andReturn([
            "company_id" => 1,
            "customer_id" => 1,
            "foo" => "bar"
        ]);
        $uploaderData = [
            $uploaderRecord
        ];

        $uploaderPackage = new UploaderPackage(new UploadableUser(),$uploaderData);

        $uploader = new Uploader($uploaderPackage);
        $this->assertTrue($uploader->upload());
    }

}
