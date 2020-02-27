<?php

namespace Nmc9\Uploader\Test\Unit\Method;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Database\Models\BrokenBalance;
use Nmc9\Uploader\Database\Models\CustomerBalance;
use Nmc9\Uploader\Database\Models\JustCreatedAt;
use Nmc9\Uploader\Database\Models\JustUpdatedAt;
use Nmc9\Uploader\Database\Models\NoTimestamp;
use Nmc9\Uploader\Database\Uploadables\UploadableBrokenBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableCustomerBalance;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Method\UploadMethodOnDuplicate;
use Nmc9\Uploader\Tests\LaravelTestCase;
use Nmc9\Uploader\Tests\MySqlOnlyTestCase;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Error;
use \Illuminate\Support\Facades\DB;
use \Mockery;

class MethodOnDuplicateTest extends LaravelTestCase{

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

        \DB::shouldReceive('statement')->once()->andReturn(true);

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

    public function test_model_with_timestamps_sets_timestamps(){
        $method = new UploadMethodOnDuplicate();

        $model = Mockery::mock(CustomerBalance::class);
        $method->setTimestamps($model);
        $this->assertEquals($method->getTimestamps(),[
            "updated_at" => "updated_at",
            "created_at" => "created_at"
        ]);
    }

    public function test_model_with_no_timestamps_sets_null(){
        $method = new UploadMethodOnDuplicate();

        $model = Mockery::mock(NoTimestamp::class);
        $method->setTimestamps($model);
        $this->assertEquals($method->getTimestamps(),[
            "updated_at" => null,
            "created_at" => null
        ]);
    }

    public function test_model_with_named_created_at_just_has_created_at(){
        $method = new UploadMethodOnDuplicate();

        $model = Mockery::mock(JustCreatedAt::class);
        $method->setTimestamps($model);
        $this->assertEquals($method->getTimestamps(),[
            "updated_at" => null,
            "created_at" => "created_at"
        ]);
    }

    public function test_model_with_named_updated_at_just_has_updated_at(){
        $method = new UploadMethodOnDuplicate();

        $model = Mockery::mock(JustUpdatedAt::class);
        $method->setTimestamps($model);
        $this->assertEquals($method->getTimestamps(),[
            "updated_at" => "LastModified",
            "created_at" => null
        ]);
    }

    public function test_good_data_without_timestamps_returns_true(){
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

        \DB::shouldReceive('statement')->once()->andReturn(true);

        $method = (new UploadMethodOnDuplicate())->turnOffTimestamps();
        $this->assertTrue($method->handle($uploadable,$data));

    }

    public function test_good_data_with_just_updated_at_returns_true(){
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
        $model = Mockery::mock(JustUpdatedAt::class);
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

        \DB::shouldReceive('statement')->once()->andReturn(true);

        $method = new UploadMethodOnDuplicate();
        $this->assertTrue($method->handle($uploadable,$data));

    }

    public function test_good_data_with_just_created_at_returns_true(){
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
        $model = Mockery::mock(JustCreatedAt::class);
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

        \DB::shouldReceive('statement')->once()->andReturn(true);

        $method = new UploadMethodOnDuplicate();
        $this->assertTrue($method->handle($uploadable,$data));

    }

    public function test_good_data_with_no_timestamp_model_returns_true(){
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
        $model = Mockery::mock(NoTimestamp::class);
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

        \DB::shouldReceive('statement')->once()->andReturn(true);

        $method = new UploadMethodOnDuplicate();
        $this->assertTrue($method->handle($uploadable,$data));

    }


}
