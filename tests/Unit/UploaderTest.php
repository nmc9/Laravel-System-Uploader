<?php

namespace Tests\Uploader\Unit;

use Illuminate\Database\Eloquent\Model;
use Nmc9\Uploader\Contract\UploadMethodContract;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\UploadMethodDuplicateOn;
use Nmc9\Uploader\Uploader;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Error;
use \Mockery;
class UploaderTest extends \Orchestra\Testbench\TestCase
{
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

        //Fake Model
        $model = Mockery::mock(Model::class)->shouldReceive('updateOrCreate')->once()->getMock();
        //Fake Uploadable
        $uploadable = Mockery::mock(UploadableContract::class)->shouldReceive('getModel')->andReturn($model)->getMock();
        $uploadable->shouldReceive('getUploaderIdFields')->andReturn(["company_id","customer_id"]);


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getUploadable')->once()->andReturn($uploadable);


        $uploader = new Uploader($uploaderPackage);
        $this->assertTrue($uploader->upload());
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_no_data_returns_false()
    {

        // Fake Uploader Record
        $uploaderData = [
        ];

        //Fake Model
        $model = Mockery::mock(Model::class);

        $uploadable = Mockery::mock(UploadableContract::class)->shouldReceive('getModel')->andReturn($model)->getMock();
        $uploadable->shouldReceive('getUploaderIdFields')->andReturn(["company_id","customer_id"]);


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getUploadable')->once()->andReturn($uploadable);


        $uploader = new Uploader($uploaderPackage);
        $this->assertFalse($uploader->upload());
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_bad_id_fields_throws_exception()
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

        //Fake Model
        $uploadable = Mockery::mock(UploadableContract::class)->shouldReceive('getModel')->andReturn(Mockery::mock(Model::class))->getMock();
        $uploadable->shouldReceive('getUploaderIdFields')->andReturn(["company_id","na"]);


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getUploadable')->once()->andReturn($uploadable);

        $this->expectException(NoMatchingIdKeysException::class);

        $uploader = new Uploader($uploaderPackage);
        $uploader->upload();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_bad_uploader_data_throws_exception()
    {
        // Fake Uploader Record
        $uploaderData = [
            [
                "company_id" => 1,
                "customer_id" => 1,
                "foo" => "bar"
            ]
        ];

        //Fake Model
        $uploadable = Mockery::mock(UploadableContract::class)->shouldReceive('getModel')->andReturn(Mockery::mock(Model::class))->getMock();
        $uploadable->shouldReceive('getUploaderIdFields')->andReturn(["company_id","na"]);


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getUploadable')->once()->andReturn($uploadable);

        $this->expectException(Error::class);

        $uploader = new Uploader($uploaderPackage);
        $uploader->upload();
    }

    public function test_uploader_works_with_on_duplicator_method(){
        $uploaderRecord = Mockery::mock(UploaderRecord::class);
        $uploaderData = [
            $uploaderRecord
        ];


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getUploadable')->once()->andReturn(Mockery::mock(UploadableContract::class));

        $method = Mockery::mock(UploadMethodDuplicateOn::class)->shouldReceive('handle')->once()->andReturn(true)->getMock();
        $uploader = new Uploader($uploaderPackage, $method);
        $this->assertTrue($uploader->upload());

    }
}
