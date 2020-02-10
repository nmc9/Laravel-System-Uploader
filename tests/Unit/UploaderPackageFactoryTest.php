<?php

namespace Tests\Uploader\Unit;

use Nmc9\Uploader\Contract\AbstractUploadableModel;
use Nmc9\Uploader\Contract\UploaderModelContract;
use Nmc9\Uploader\Factory\UploaderPackageFactory;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Mockery;

class UploaderPackageFactoryTest extends TestCase
{

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_create_an_uploader_packge()
    {
        $data = [
            Mockery::mock(UploaderRecord::class),
        ];
        $model = Mockery::mock(AbstractUploadableModel::class);
        $package = UploaderPackageFactory::create($model,$data);

        $this->assertInstanceOf(UploaderPackage::class,$package);
    }

}
