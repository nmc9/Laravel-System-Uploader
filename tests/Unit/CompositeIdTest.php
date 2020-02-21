<?php

namespace Uploader\Tests\Unit;


use Nmc9\Uploader\CompositeId;
use Nmc9\Uploader\Exceptions\NoMatchingIdKeysException;
use PHPUnit\Framework\TestCase;


class CompositeIdTest extends TestCase
{


    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_a_single_id()
    {
        $ids = CompositeId::make(["field_1"])->get([
            "field_1" => 1,
            "field_2" => 2,
        ]);

        $this->assertEquals($ids,[
            "field_1" => 1
        ]);

    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_a_single_id_with_index()
    {
        $ids = CompositeId::make(["field_1"])->get([
            "field_1" => 1,
            "field_2" => 2,
        ],7);

        $this->assertEquals($ids,[
            "field_1" => 1
        ]);

    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_get_multiple_ids()
    {
        $ids = CompositeId::make(["field_1","field_2","field_3"])->get([
            "field_1" => 1,
            "field_2" => 2,
            "field_3" => 3,
            "field_4" => 4,
            "field_5" => 5,
            "field_6" => 6,

        ],7);

        $this->assertEquals($ids,[
            "field_1" => 1,
            "field_2" => 2,
            "field_3" => 3,
        ]);

    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_missing_id_throws_a_NoMatchingIdKeysException()
    {

        $this->expectException(NoMatchingIdKeysException::class);
        $this->expectExceptionMessage("Data is Missing in the Id Field [\"field_2\"] at index [\"7\"]");

        $ids = CompositeId::make(["field_1","field_2","field_3"])->get([
            "field_1" => 1,
            "field_3" => 3,
            "field_4" => 4,
            "field_5" => 5,
            "field_6" => 6,

        ],7);

    }

}
