<?php

namespace Tests\Uploader\Unit;

use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;

class UploaderRecordTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_uploader_record()
    {
    	$data = [
    		'data' => "data",
    		'other' => "other",
    	];

    	$UploaderData = new UploaderRecord($data);

    	$this->assertEquals($UploaderData->get(),$data);

    }
}
