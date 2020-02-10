<?php

namespace Tests\Uploader\Unit;

use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Contract\UploaderModelContract;
use Nmc9\Uploader\Uploader;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Error;
use \Mockery;
class UploaderTest extends TestCase
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
        $uploaderModel = Mockery::mock(UploaderModelContract::class);
        $uploaderModel->shouldReceive('updateOrCreate')->once();


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getModel')->once()->andReturn($uploaderModel);
        $uploaderPackage->shouldReceive('getIdFields')->once()->andReturn(["company_id","customer_id"]);


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
        $uploaderModel = Mockery::mock(UploaderModelContract::class);


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getModel')->once()->andReturn($uploaderModel);
        $uploaderPackage->shouldReceive('getIdFields')->once()->andReturn(["company_id","customer_id"]);


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
        $uploaderModel = Mockery::mock(UploaderModelContract::class);
        // $uploaderModel->shouldReceive('updateOrCreate')->once();


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getModel')->once()->andReturn($uploaderModel);
        $uploaderPackage->shouldReceive('getIdFields')->once()->andReturn(["nu","na"]);

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
        $uploaderModel = Mockery::mock(UploaderModelContract::class);


        //Fake Package
        $uploaderPackage = Mockery::mock(UploaderPackage::class);
        $uploaderPackage->shouldReceive('getData')->once()->andReturn($uploaderData);
        $uploaderPackage->shouldReceive('getModel')->once()->andReturn($uploaderModel);
        $uploaderPackage->shouldReceive('getIdFields')->once()->andReturn(["company_id","na"]);

        $this->expectException(Error::class);

        $uploader = new Uploader($uploaderPackage);
        $uploader->upload();
    }
}
