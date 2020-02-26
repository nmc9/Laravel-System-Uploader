<?php

namespace Nmc9\Uploader\Test\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Database\Models\BrokenBalance;
use Nmc9\Uploader\Database\Models\CustomerBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableBrokenBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableCustomerBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableDummy;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Method\UploadMethodOnDuplicate;
use Nmc9\Uploader\Tests\MySqlOnlyTestCase;
use Nmc9\Uploader\Uploader;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use \Error;
use \Mockery;

class UploaderOnDuplicateTest extends MySqlOnlyTestCase
{
    public $runMigrations = true;

    public function test_uploader_can_insert_package_records_with_on_duplicator(){
        $uploaderData = [
            new UploaderRecord([
                "company_id" => 1,
                'customer_id' => 1,
                "balance" => 123
            ]),
            new UploaderRecord([
                "company_id" => 1,
                'customer_id' => 2,
                "balance" => 300
            ]),
        ];

        $uploaderPackage = new UploaderPackage(new UploadableCustomerBalance(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate());
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => 1,
            'customer_id' => 1,
            "balance" => 123,
        ]);

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => 1,
            'customer_id' => 2,
            "balance" => 300,
        ]);
    }

    public function test_uploader_can_update_package_records_using_on_duplicator(){

        $cb1 = factory(CustomerBalance::class)->create();
        $cb2 = factory(CustomerBalance::class)->create();


        $uploaderData = [
            new UploaderRecord([
                "company_id" => $cb1->company_id,
                'customer_id' => $cb1->customer_id,
                "balance" => 123
            ]),
            new UploaderRecord([
                "company_id" => $cb2->company_id,
                'customer_id' => $cb2->customer_id,
                "balance" => 300
            ]),
        ];

        $uploaderPackage = new UploaderPackage(new UploadableCustomerBalance(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate());
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => $cb1->company_id,
            'customer_id' => $cb1->customer_id,
            "balance" => 123,
        ]);

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => $cb2->company_id,
            'customer_id' => $cb2->customer_id,
            "balance" => 300,
        ]);
    }

    public function test_uploader_returns_false_when_there_is_no_data_in_package(){

        $cb1 = factory(CustomerBalance::class)->create();
        $cb2 = factory(CustomerBalance::class)->create();


        $uploaderData = [
        ];

        $uploaderPackage = new UploaderPackage(new UploadableCustomerBalance(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate());
        $this->assertFalse($uploader->upload());
    }

    public function test_uploader_throws_missing_key_error_when_key_field_is_missing(){

        $cb1 = factory(CustomerBalance::class)->create();
        $cb2 = factory(CustomerBalance::class)->create();


        $uploaderData = [
            new UploaderRecord([
                "company_id" => $cb1->company_id,
                "balance" => 123
            ]),
            new UploaderRecord([
                "company_id" => $cb2->company_id,
                "balance" => 300
            ]),
        ];

        $this->expectException(NoMatchingIdKeysException::class);
        $this->expectExceptionMessage('Data is Missing in the Id Field ["customer_id"]');


        $uploaderPackage = new UploaderPackage(new UploadableCustomerBalance(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate());
        $this->assertFalse($uploader->upload());
    }

    public function test_uploader_throws_query_exception_when_unused_field_is_passed(){

        $cb1 = factory(CustomerBalance::class)->create();
        $cb2 = factory(CustomerBalance::class)->create();


        $uploaderData = [
            new UploaderRecord([
                "company_id" => $cb1->company_id,
                'customer_id' => $cb1->customer_id,
                "junk" => 123
            ]),
            new UploaderRecord([
                "company_id" => $cb2->company_id,
                'customer_id' => $cb2->customer_id,
                "junk" => 300
            ]),
        ];

        $this->expectException(QueryException::class);


        $uploaderPackage = new UploaderPackage(new UploadableCustomerBalance(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate());
        $this->assertFalse($uploader->upload());
    }

    public function test_uploader_throws_missing_unique_contraint_exception_when_the_table_is_missing_contraints(){

        $cb1 = factory(BrokenBalance::class)->create();
        $cb2 = factory(BrokenBalance::class)->create();


        $uploaderData = [
            new UploaderRecord([
                "company_id" => $cb1->company_id,
                "balance" => 123
            ]),
            new UploaderRecord([
                "company_id" => $cb2->company_id,
                "balance" => 300
            ]),
        ];

        $this->expectException(MissingUniqueContraintException::class);
        $this->expectExceptionMessage('Expected Unique Constraint ["company_id","customer_id"] but only ["company_id"] is in the database');


        $uploaderPackage = new UploaderPackage(new UploadableBrokenBalance(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate());
        $this->assertFalse($uploader->upload());
    }

    public function test_can_override_the_check(){

        $cb1 = factory(BrokenBalance::class)->create();
        $cb2 = factory(BrokenBalance::class)->create();


        $uploaderData = [
            new UploaderRecord([
                "company_id" => $cb1->company_id,
                'customer_id' => $cb1->customer_id,
                "balance" => 123
            ]),
            new UploaderRecord([
                "company_id" => $cb2->company_id,
                'customer_id' => $cb2->customer_id,
                "balance" => 300
            ]),
        ];

        $uploaderPackage = new UploaderPackage(new UploadableBrokenBalance(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate(false));
        $this->assertTrue($uploader->upload());
    }

    public function test_no_id_on_table(){

        $uploaderData = [
            new UploaderRecord([
                "dummy_id" => 1,
                "data" => "data",
            ]),
            new UploaderRecord([
                "dummy_id" => 2,
                "data" => "data",
            ]),
        ];
        $uploaderPackage = new UploaderPackage(new UploadableDummy(),$uploaderData);
        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicate());
        $this->assertTrue($uploader->upload());


    }


}
