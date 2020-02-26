<?php

namespace Nmc9\Uploader\Test\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\Models\BrokenBalance;
use Nmc9\Uploader\Database\Models\CustomerBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableBrokenBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableCustomerBalance;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Method\UploadMethodOnDuplicate;
use Nmc9\Uploader\Tests\MySqlOnlyTestCase;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Error;
use \Illuminate\Support\Facades\DB;
use \Mockery;

class MethodOnDuplicateTest extends MySqlOnlyTestCase{

    public function test_good_data_passes_the_id_fields_check(){
        $data = [
            new UploaderRecord([
                "company_id" => 1,
                "customer_id" => 2,
            ])
        ];
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->once()->andReturn('customer_balances');
        $uploadable = Mockery::mock(UploadableContract::class);
        $uploadable->shouldReceive('getModel')->once()->andReturn($model);
        $uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['company_id', 'customer_id']);

        \DB::shouldReceive('select')->once()->andReturn([
            [
                "Column_name" => "company_id",
                "other" => "junk",
            ],[
                "Column_name" => "customer_id",
                "other" => "junk",
            ]
        ]);

        $method = new UploadMethodOnDuplicate();

        $method->setUploadable($uploadable);
        $method->setData($data);

        $this->assertNull($method->checkIdFields());

    }

    public function test_table_without_keys_fails_check_with_missing_key_exceptions(){
        $data = [
            new UploaderRecord([
                "company_id" => 1,
                "customer_id" => 2,
            ])
        ];
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->once()->andReturn('customer_balances');
        $uploadable = Mockery::mock(UploadableContract::class);
        $uploadable->shouldReceive('getModel')->once()->andReturn($model);
        $uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['company_id', 'customer_id']);

        \DB::shouldReceive('select')->once()->andReturn([
            [
                "Column_name" => "company_id",
                "other" => "junk",
            ]
        ]);

        $method = new UploadMethodOnDuplicate();

        $method->setUploadable($uploadable);
        $method->setData($data);

        $this->expectException(MissingUniqueContraintException::class);
        $this->expectExceptionMessage('Expected Unique Constraint ["company_id","customer_id"] but only ["company_id"] is in the database');

        $method->checkIdFields();
    }

    public function test_bad_records_fails_check_with_no_id_fields_exception(){
        $data = [
            new UploaderRecord([
                "x" => 1,
                "customer_id" => 2,
            ])
        ];
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->once()->andReturn('customer_balances');
        $uploadable = Mockery::mock(UploadableContract::class);
        $uploadable->shouldReceive('getModel')->once()->andReturn($model);
        $uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['company_id', 'customer_id']);

        \DB::shouldReceive('select')->once()->andReturn([
            [
                "Column_name" => "company_id",
                "other" => "junk",
            ],[
                "Column_name" => "customer_id",
                "other" => "junk",
            ]
        ]);

        $method = new UploadMethodOnDuplicate();

        $method->setUploadable($uploadable);
        $method->setData($data);

        $this->expectException(NoMatchingIdKeysException::class);
        $this->expectExceptionMessage('Data is Missing in the Id Field ["company_id"]');

        $method->checkIdFields();
    }




    public function test_no_data_returns_false(){
        $data = [
        ];

        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->once()->andReturn('customer_balances');
        $uploadable = Mockery::mock(UploadableContract::class);
        $uploadable->shouldReceive('getModel')->once()->andReturn($model);
        $uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['company_id', 'customer_id']);

        \DB::shouldReceive('select')->once()->andReturn([
            [
                "Column_name" => "company_id",
                "other" => "junk",
            ],[
                "Column_name" => "customer_id",
                "other" => "junk",
            ]
        ]);

        $method = new UploadMethodOnDuplicate();

        $this->assertFalse($method->handle($uploadable,$data));
    }

    public function test_good_data_returns_true(){
        $data = [
            new UploaderRecord([
                "company_id" => 1,
                "customer_id" => 2,
            ]),
            new UploaderRecord([
                "company_id" => 1,
                "customer_id" => 3,
            ])
        ];
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->once()->andReturn('customer_balances');
        $uploadable = Mockery::mock(UploadableContract::class);
        $uploadable->shouldReceive('getModel')->once()->andReturn($model);
        $uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['company_id', 'customer_id']);

        \DB::shouldReceive('select')->once()->andReturn([
            [
                "Column_name" => "company_id",
                "other" => "junk",
            ],[
                "Column_name" => "customer_id",
                "other" => "junk",
            ]
        ]);

        \DB::shouldReceive('statement')->once()->with("INSERT INTO `customer_balances` (`company_id`,`customer_id`) VALUES (?,?),(?,?) ON DUPLICATE KEY UPDATE `company_id`=VALUES(`company_id`),`customer_id`=VALUES(`customer_id`);",
            [1,2,1,3])->andReturn(true);

        $method = new UploadMethodOnDuplicate();
        $this->assertTrue($method->handle($uploadable,$data));

    }

    public function test_table_without_keys_fails_upload(){
        $data = [
            new UploaderRecord([
                "company_id" => 1,
                "customer_id" => 2,
            ])
        ];
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->once()->andReturn('customer_balances');
        $uploadable = Mockery::mock(UploadableContract::class);
        $uploadable->shouldReceive('getModel')->once()->andReturn($model);
        $uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['company_id', 'customer_id']);

        \DB::shouldReceive('select')->once()->andReturn([
            [
                "Column_name" => "company_id",
                "other" => "junk",
            ]
        ]);

        $this->expectException(MissingUniqueContraintException::class);
        $this->expectExceptionMessage('Expected Unique Constraint ["company_id","customer_id"] but only ["company_id"] is in the database');

        $method = new UploadMethodOnDuplicate();
        $method->handle($uploadable,$data);
    }

    public function test_bad_records_fails_upload(){
        $data = [
            new UploaderRecord([
                "x" => 1,
                "customer_id" => 2,
            ])
        ];
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->once()->andReturn('customer_balances');
        $uploadable = Mockery::mock(UploadableContract::class);
        $uploadable->shouldReceive('getModel')->once()->andReturn($model);
        $uploadable->shouldReceive('getUploaderIdFields')->once()->andReturn(['company_id', 'customer_id']);

        \DB::shouldReceive('select')->once()->andReturn([
            [
                "Column_name" => "company_id",
                "other" => "junk",
            ],[
                "Column_name" => "customer_id",
                "other" => "junk",
            ]
        ]);

        $this->expectException(NoMatchingIdKeysException::class);
        $this->expectExceptionMessage('Data is Missing in the Id Field ["company_id"]');

        $method = new UploadMethodOnDuplicate();
        $method->handle($uploadable,$data);
    }

}
