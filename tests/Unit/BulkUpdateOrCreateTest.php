<?php

namespace Uploader\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nmc9\Uploader\Contract\UploadableContract;
use Nmc9\Uploader\Contract\UploaderModelContract;
use Nmc9\Uploader\Example\CustomerBalance;
use Nmc9\Uploader\Example\User;
use Nmc9\Uploader\Factory\UploaderPackageFactory;
use Nmc9\Uploader\UploaderPackage;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;
use \Mockery;
use \Nmc9\Uploader\Database\BulkUpdateOrCreate;


class BulkUpdateOrCreateTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_data()
    {

        $model = Mockery::mock(User::class);
        $records = [
            new UploaderRecord([
                'user_id' => 1,
                'name' => "nick",
                'email' => "nick@test.com",
                'password' => "nick",
            ]),
            new UploaderRecord([
                'user_id' => 1,
                'name' => "nicker",
                'email' => "nickx@test.com",
                'password' => "nick",
            ])
        ];

        $bulk = BulkUpdateOrCreate::model($model)->setRecords($records);

        $this->assertEquals($bulk->getData(),collect([
            [
                'user_id' => 1,
                'name' => "nick",
                'email' => "nick@test.com",
                'password' => "nick",
            ],
            [
                'user_id' => 1,
                'name' => "nicker",
                'email' => "nickx@test.com",
                'password' => "nick",
            ]
        ]));


    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_model()
    {

        $model = Mockery::mock(User::class);
        $bulk = BulkUpdateOrCreate::model($model);

        $this->assertEquals($bulk->getModel(),$model);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_id_fields()
    {

        $model = Mockery::mock(User::class);
        $idFields = ['user_id','name'];

        $bulk = BulkUpdateOrCreate::model($model)->setIdFields($idFields);

        $this->assertEquals($bulk->getIdFields(),$idFields);


    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_all_composite_ids()
    {
        $model = Mockery::mock(User::class);
        $idFields = ['user_id','name'];
        $records = [
            new UploaderRecord([
                'user_id' => 1,
                'name' => "nick",
                'email' => "nick@test.com",
                'password' => "nick",
            ]),
            new UploaderRecord([
                'user_id' => 1,
                'name' => "nicker",
                'email' => "nickx@test.com",
                'password' => "nick",
            ])
        ];

        $ids = BulkUpdateOrCreate::model($model)->set($records,$idFields)->getAllCompositeIds();

        $this->assertEquals($ids,collect([
            [
                'user_id' => 1,
                'name' => "nick"
            ],
            [
                'user_id' => 1,
                'name' => "nicker"
            ]
        ]));
    }

}
