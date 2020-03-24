<?php

namespace Nmc9\Uploader\Test\Unit\Method;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Method\UploadMethodUpdateOrCreate;
use Nmc9\Uploader\Tests\LaravelTestCase;
use Nmc9\Uploader\UploaderRecord;
use \Error;
use \Mockery;

class MethodUpdateOrCreateTest extends LaravelTestCase
{

	// use RefreshDatabase;

	public function test_handler_can_return_true_with_good_data(){
		$data = [
			new UploaderRecord([
				"field_1" => 1,
				"field_2" => 2,
			])
		];
		$model = Mockery::mock(Model::class)->shouldReceive('updateOrCreate')->once()->getMock();
		$uploadable = Mockery::mock(UploadableContract::class);
		$uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['field_1']);
		$uploadable->shouldReceive('getModel')->once()->andReturn($model);

		$method = new UploadMethodUpdateOrCreate();
		$this->assertTrue($method->handle($uploadable,$data));
	}

	public function test_handler_can_return_false_with_no_data(){
		$data = [
		];
		$uploadable = Mockery::mock(UploadableContract::class);

		$method = new UploadMethodUpdateOrCreate();
		$this->assertFalse($method->handle($uploadable,$data));
	}

	public function test_handler_throws_error_when_uploader_fields_are_missing(){
		$data = [
			new UploaderRecord([
				"field_x" => 1,
				"field_2" => 2,
			])
		];

		$this->expectException(NoMatchingIdKeysException::class);

		$uploadable = Mockery::mock(UploadableContract::class);
		$uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['field_1']);

		$method = new UploadMethodUpdateOrCreate();
		$this->assertTrue($method->handle($uploadable,$data));
	}
}
