<?php

namespace Uploader\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Example\CustomerBalance;
use Nmc9\Uploader\Kfir\OnDuplicateGenerator;
use PHPUnit\Framework\TestCase;
use Nmc9\Uploader\UploaderRecord;
use \Mockery;


class OnDuplicateTest extends \Orchestra\Testbench\TestCase
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


    public function test_on_duplicator_can_upload(){
        $data = [
            new UploaderRecord([
                'customer_id' => 1,
                'company_id' => 1,
                'balance' => 100,
            ]),new UploaderRecord([
                'customer_id' => 1,
                'company_id' => 2,
                'balance' => 200,
            ])
        ];
        $bulk = (new OnDuplicateGenerator())->generate('customer_balances',$data);

        \DB::statement($bulk->getQuery(),$bulk->getBindings());

        $this->assertDatabaseHas('customer_balances',$data[0]->get());
        $this->assertDatabaseHas('customer_balances',$data[1]->get());

    }

    public function test_on_duplicator_can_update(){
        $id1 = factory(CustomerBalance::class)->create([
            'customer_id' => 3,
            'company_id' => 10
        ])->id;
        $id2 = factory(CustomerBalance::class)->create([
            'customer_id' => 4,
            'company_id' => 10
        ])->id;

        $data = [
            new UploaderRecord([
                'customer_id' => 3,
                'company_id' => 10,
                'balance' => 500,
            ]),new UploaderRecord([
                'customer_id' => 4,
                'company_id' => 10,
                'balance' => 600,
            ])
        ];

        $bulk = (new OnDuplicateGenerator())->generate('customer_balances',$data);

        \DB::statement($bulk->getQuery(),$bulk->getBindings());

        $this->assertDatabaseHas('customer_balances',[
            "id" => $id1,
            'customer_id' => 3,
            'company_id' => 10,
            'balance' => 500,
        ]);
        $this->assertDatabaseHas('customer_balances',[
            "id" => $id2,
            'customer_id' => 4,
            'company_id' => 10,
            'balance' => 600,
        ]);

    }

    public function test_on_duplicator_can_insert_and_update(){
        $id1 = factory(CustomerBalance::class)->create([
            'customer_id' => 3,
            'company_id' => 10
        ])->id;
        $id2 = factory(CustomerBalance::class)->create([
            'customer_id' => 4,
            'company_id' => 10
        ])->id;

        $data = [
            new UploaderRecord([
                'customer_id' => 3,
                'company_id' => 10,
                'balance' => 500,
            ]),new UploaderRecord([
                'customer_id' => 1,
                'company_id' => 1,
                'balance' => 100,
            ]),new UploaderRecord([
                'customer_id' => 4,
                'company_id' => 10,
                'balance' => 600,
            ]),new UploaderRecord([
                'customer_id' => 1,
                'company_id' => 2,
                'balance' => 200,
            ])
        ];

        $bulk = (new OnDuplicateGenerator())->generate('customer_balances',$data);

        \DB::statement($bulk->getQuery(),$bulk->getBindings());

        $this->assertDatabaseHas('customer_balances',[
            'customer_id' => 3,
            'company_id' => 10,
            'balance' => 500,
            'id' => $id1
        ]);
        $this->assertDatabaseHas('customer_balances',$data[1]->get());
        $this->assertDatabaseHas('customer_balances',[
            'customer_id' => 4,
            'company_id' => 10,
            'balance' => 600,
            "id" => $id2
        ]);
        $this->assertDatabaseHas('customer_balances',$data[3]->get());

    }

}
