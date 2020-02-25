<?php

namespace Uploader\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\OnDuplicateUploader;
use Nmc9\Uploader\Example\BrokenBalance;
use Nmc9\Uploader\Example\CustomerBalance;
use Nmc9\Uploader\Example\UploadableBrokenBalance;
use Nmc9\Uploader\Example\UploadableCustomerBalance;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Kfir\OnDuplicateGenerator;
use PHPUnit\Framework\TestCase;
use Nmc9\Uploader\UploaderRecord;
use \Mockery;


class OnDuplicateUploadTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    public function setup(): void{
        parent::setup();
        if(env('DB_CONNECTION') !== "mysql"){
            $this->markTestSkipped(
                'Can\'t test this in sqlite'
            );
        }
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->withFactories(__DIR__ . '/../../database/factories');
    }

    public function test_no_data_returns_false_result(){
        factory(CustomerBalance::class)->create([
            'customer_id' => 1,
            'company_id' => 2,
            'balance' => 999,
        ]);
        $data = [
        ];

        $uploader = new OnDuplicateUploader(new UploadableCustomerBalance());

        $result = $uploader->upload($data);

        $this->assertFalse($result);

    }

    public function test_uploader_can_upload(){
        factory(CustomerBalance::class)->create([
            'customer_id' => 1,
            'company_id' => 2,
            'balance' => 999,
        ]);
        $data = [
            new UploaderRecord([
                'customer_id' => 12,
                'company_id' => 2,
                'balance' => 100,
            ]),new UploaderRecord([
                'customer_id' => 1,
                'company_id' => 2,
                'balance' => 200,
            ])
        ];

        $uploader = new OnDuplicateUploader(new UploadableCustomerBalance());

        $result = $uploader->upload($data);

        $this->assertTrue($result);
// dd(CustomerBalance::all());
        $this->assertDatabaseHas('customer_balances',$data[0]->get());
        $this->assertDatabaseHas('customer_balances',$data[1]->get());
    }

    public function test_uploader_throws_exception_when_the_table_is_missing_composite_keys(){
        $data = [
            new UploaderRecord([
                'customer_id' => 12,
                'company_id' => 2,
                'balance' => 100,
            ]),new UploaderRecord([
                'customer_id' => 1,
                'company_id' => 2,
                'balance' => 200,
            ])
        ];

        $this->expectException(MissingUniqueContraintException::class);
        $this->expectExceptionMessage('Expected Unique Constraint ["company_id","customer_id"] but only ["company_id"] is in the database');

        $uploader = new OnDuplicateUploader(new UploadableBrokenBalance());

        $uploader->upload($data);
    }

    public function test_uploader_can_upload_if_the_check_is_toggled_off(){
        factory(BrokenBalance::class)->create([
            'customer_id' => 48,
            'company_id' => 2,
            'balance' => 999,
        ]);
        $data = [
            new UploaderRecord([
                'customer_id' => 17,
                'company_id' => 2,
                'balance' => 100,
            ]),new UploaderRecord([
                'customer_id' => 48,
                'company_id' => 2,
                'balance' => 200,
            ])
        ];

        $uploader = new OnDuplicateUploader(new UploadableBrokenBalance(),false);

        $uploader->upload($data);

        $this->assertDatabaseMissing('broken_balances',$data[0]->get());
        $this->assertDatabaseHas('broken_balances',$data[1]->get());
    }

}
