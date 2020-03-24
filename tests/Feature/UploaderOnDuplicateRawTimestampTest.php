<?php

namespace Nmc9\Uploader\Test\Feature;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Database\Models\BrokenBalance;
use Nmc9\Uploader\Database\Models\CustomerBalance;
use Nmc9\Uploader\Database\Models\JustCreatedAt;
use Nmc9\Uploader\Database\Models\JustUpdatedAt;
use Nmc9\Uploader\Database\Uploadables\UploadableBrokenBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableCustomerBalance;
use Nmc9\Uploader\Database\Uploadables\UploadableDummy;
use Nmc9\Uploader\Database\Uploadables\UploadableJustCreatedAt;
use Nmc9\Uploader\Database\Uploadables\UploadableJustUpdatedAt;
use Nmc9\Uploader\Exceptions\MissingUniqueContraintException;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use Nmc9\Uploader\Method\UploadMethodOnDuplicateRaw;
use Nmc9\Uploader\Tests\MySqlOnlyTestCase;
use Nmc9\Uploader\Uploader;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use \Error;
use \Mockery;

class UploaderOnDuplicateRawTimestampTest extends MySqlOnlyTestCase
{
    public $runMigrations = true;

    public function test_uploader_can_insert_package_with_timestamps(){

        Carbon::setTestNow('2001-7-22 10:04:00');
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
        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicateRaw());
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => 1,
            'customer_id' => 1,
            "balance" => 123,
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
        ]);

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => 1,
            'customer_id' => 2,
            "balance" => 300,
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now(),
        ]);
    }

    public function test_uploader_can_update_package_with_timestamps(){
        Carbon::setTestNow('2001-7-22 10:04:00');

        $cb1 = factory(CustomerBalance::class)->create([
            "updated_at" => '1990-1-1 11:11:11',
            "created_at" => '1990-1-1 11:11:11'
        ]);
        $cb2 = factory(CustomerBalance::class)->create([
            "updated_at" => '1990-1-1 11:11:11',
            "created_at" => '1990-1-1 11:11:11'
        ]);


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

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicateRaw());
        $this->assertTrue($uploader->upload());
        $this->assertDatabaseHas('customer_balances',[
            "company_id" => $cb1->company_id,
            'customer_id' => $cb1->customer_id,
            "balance" => 123,
            "updated_at" => Carbon::now(),
            "created_at" => $cb1->created_at
        ]);

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => $cb2->company_id,
            'customer_id' => $cb2->customer_id,
            "balance" => 300,
            "updated_at" => Carbon::now(),
            "created_at" => $cb2->created_at,
        ]);
    }

    public function test_uploader_can_insert_package_with_timestamps_turned_off(){

        Carbon::setTestNow('2001-7-22 10:04:00');
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

        $uploader = new Uploader($uploaderPackage,(new UploadMethodOnDuplicateRaw())->turnOffTimestamps());
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => 1,
            'customer_id' => 1,
            "balance" => 123,
            'updated_at' => null,
            'created_at' => null,
        ]);

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => 1,
            'customer_id' => 2,
            "balance" => 300,
            'updated_at' => null,
            'created_at' => null,
        ]);
    }

    public function test_uploader_can_update_package_with_timestamps_turned_off(){
        Carbon::setTestNow('2001-7-22 10:04:00');

        $cb1 = factory(CustomerBalance::class)->create([
            "updated_at" => '1990-1-1 11:11:11',
            "created_at" => '1990-1-1 11:11:11'
        ]);
        $cb2 = factory(CustomerBalance::class)->create([
            "updated_at" => '1990-1-1 11:11:11',
            "created_at" => '1990-1-1 11:11:11'
        ]);


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

        $uploader = new Uploader($uploaderPackage,(new UploadMethodOnDuplicateRaw())->turnOffTimestamps());
        $this->assertTrue($uploader->upload());
        $this->assertDatabaseHas('customer_balances',[
            "company_id" => $cb1->company_id,
            'customer_id' => $cb1->customer_id,
            "balance" => 123,
            "updated_at" => $cb1->updated_at,
            "created_at" => $cb1->created_at
        ]);

        $this->assertDatabaseHas('customer_balances',[
            "company_id" => $cb2->company_id,
            'customer_id' => $cb2->customer_id,
            "balance" => 300,
            "updated_at" => $cb2->updated_at,
            "created_at" => $cb2->created_at,
        ]);
    }

    public function test_uploader_can_insert_package_with_just_updated_at(){

        Carbon::setTestNow('2001-7-22 10:04:00');
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

        $uploaderPackage = new UploaderPackage(new UploadableJustUpdatedAt(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicateRaw());
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('just_updated_at',[
            "company_id" => 1,
            'customer_id' => 1,
            "balance" => 123,
            'LastModified' => Carbon::now(),
        ]);

        $this->assertDatabaseHas('just_updated_at',[
            "company_id" => 1,
            'customer_id' => 2,
            "balance" => 300,
            'LastModified' => Carbon::now(),
        ]);
    }

    public function test_uploader_can_update_package_with_just_updated_at(){
        Carbon::setTestNow('2001-7-22 10:04:00');

        $cb1 = factory(JustUpdatedAt::class)->create([
            "LastModified" => '1990-1-1 11:11:11',
        ]);
        $cb2 = factory(JustUpdatedAt::class)->create([
            "LastModified" => null,
        ]);


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

        $uploaderPackage = new UploaderPackage(new UploadableJustUpdatedAt(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicateRaw());
        $this->assertTrue($uploader->upload());
        $this->assertDatabaseHas('just_updated_at',[
            "company_id" => $cb1->company_id,
            'customer_id' => $cb1->customer_id,
            "balance" => 123,
            "LastModified" => Carbon::now(),
        ]);

        $this->assertDatabaseHas('just_updated_at',[
            "company_id" => $cb2->company_id,
            'customer_id' => $cb2->customer_id,
            "balance" => 300,
            "LastModified" => Carbon::now(),
        ]);
    }

    public function test_uploader_can_insert_package_with_just_created_at(){

        Carbon::setTestNow('2001-7-22 10:04:00');
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

        $uploaderPackage = new UploaderPackage(new UploadableJustCreatedAt(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicateRaw());
        $this->assertTrue($uploader->upload());

        $this->assertDatabaseHas('just_created_at',[
            "company_id" => 1,
            'customer_id' => 1,
            "balance" => 123,
            'created_at' => Carbon::now(),
        ]);

        $this->assertDatabaseHas('just_created_at',[
            "company_id" => 1,
            'customer_id' => 2,
            "balance" => 300,
            'created_at' => Carbon::now(),
        ]);
    }

    public function test_uploader_can_update_package_with_just_created_at(){
        Carbon::setTestNow('2001-7-22 10:04:00');

        $cb1 = factory(JustCreatedAt::class)->create([
            "created_at" => '1990-1-1 11:11:11',
        ]);
        $cb2 = factory(JustCreatedAt::class)->create([
            "created_at" => null,
        ]);

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

        $uploaderPackage = new UploaderPackage(new UploadableJustCreatedAt(),$uploaderData);

        $uploader = new Uploader($uploaderPackage,new UploadMethodOnDuplicateRaw());
        $this->assertTrue($uploader->upload());
        $this->assertDatabaseHas('just_created_at',[
            "company_id" => $cb1->company_id,
            'customer_id' => $cb1->customer_id,
            "balance" => 123,
            "created_at" => '1990-1-1 11:11:11',
        ]);

        $this->assertDatabaseHas('just_created_at',[
            "company_id" => $cb2->company_id,
            'customer_id' => $cb2->customer_id,
            "balance" => 300,
            "created_at" => null,
        ]);
    }

}
