<?php

namespace Tests\Uploader\Unit;

use Nmc9\Uploader\Contract\AbstractUploadableModel;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Mockery;

class UploaderPackageTest extends TestCase
{

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_package_returns_single_data()
    {
        $data = [
            Mockery::mock(UploaderRecord::class),
        ];
        $model = Mockery::mock(AbstractUploadableModel::class);

        $uploaderPackage = new UploaderPackage($model,$data);
        $this->assertEquals($uploaderPackage->getData(),$data);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_package_returns_mulitple_data()
    {
        $data = [
            Mockery::mock(UploaderRecord::class),
            Mockery::mock(UploaderRecord::class),
        ];
        $model = Mockery::mock(AbstractUploadableModel::class);

        $uploaderPackage = new UploaderPackage($model,$data);
        $this->assertEquals($uploaderPackage->getData(),$data);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_package_returns_model()
    {

        $data = [
            Mockery::mock(UploaderRecord::class),
            Mockery::mock(UploaderRecord::class),
        ];
        $model = Mockery::mock(AbstractUploadableModel::class);

        $uploaderPackage = new UploaderPackage($model,$data);

        $this->assertEquals($uploaderPackage->getModel(),$model);

    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_package_returns_id_fields()
    {

        $data = [
            Mockery::mock(UploaderRecord::class),
            Mockery::mock(UploaderRecord::class),
        ];
        $model = Mockery::mock(AbstractUploadableModel::class)->shouldReceive('getUploaderIdFields')->once()->andReturn(["Company","Test"])->getMock();

        $uploaderPackage = new UploaderPackage($model,$data);

        $this->assertEquals($uploaderPackage->getIdFields(),["Company","Test"]);

    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_package_returns_single_id_field()
    {
        $data = [
            Mockery::mock(UploaderRecord::class),
            Mockery::mock(UploaderRecord::class),
        ];
        $model = Mockery::mock(AbstractUploadableModel::class)->shouldReceive('getUploaderIdFields')->once()->andReturn(["Company"])->getMock();

        $uploaderPackage = new UploaderPackage($model,$data);

        $this->assertEquals($uploaderPackage->getIdFields(),["Company"]);
    }
}
