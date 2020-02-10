<?php

namespace Tests\Uploader\Unit;

use Nmc9\Uploader\Factory\UploaderRecordFactory;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Mockery;

class UploaderRecordFactoryTest extends TestCase
{

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_create_an_uploader_record()
    {
        $data = [
            'data' => "data",
            'other' => "other",
        ];

        $uploaderData = UploaderRecordFactory::create($data);

        $this->assertInstanceOf(UploaderRecord::class,$uploaderData);
    }

}
