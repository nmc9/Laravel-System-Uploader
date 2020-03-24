<?php

namespace Uploader\Tests\Unit;

use Nmc9\Uploader\Kfir\OnDuplicateRawGenerator;
use Nmc9\Uploader\Kfir\QueryObject;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;

class OnDuplicateRawGeneratorTest extends TestCase
{

    public function test_it_generates_a_null_when_there_are_no_rows(){
        $this->assertEquals(OnDuplicateRawGenerator::make()->generate('users',[]),null);
    }

    public function test_it_generates_a_query_object_for_an_array_with_one_record(){
        $expectedQuery = 'INSERT INTO `teeth` (`bite`) VALUES ("1") ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`);';
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_query_object_for_an_array_with_null_records(){
        $expectedQuery = 'INSERT INTO `gums` (`chew`) VALUES (NULL),("NULL") ON DUPLICATE KEY UPDATE `chew`=VALUES(`chew`);';
        $rows = [
            new UploaderRecord([
                "chew" => null,
            ]),
            new UploaderRecord([
                "chew" => "NULL",
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->generate('gums',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }



    public function test_it_generates_a_query_object_for_an_array_of_records(){
        $expectedQuery = 'INSERT INTO `gums` (`chew`) VALUES ("1"),("2") ON DUPLICATE KEY UPDATE `chew`=VALUES(`chew`);';
        $rows = [
            new UploaderRecord([
                "chew" => 1,
            ]),
            new UploaderRecord([
                "chew" => 2,
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->generate('gums',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_query_with_timestamps_if_timestamps_are_added(){
        //CREATED_AT should not be updated
        $expectedQuery = 'INSERT INTO `teeth` (`bite`,`updated_at`,`created_at`) VALUES ("1","2018-06-18 10:00:00","2018-06-18 10:00:00") ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`),`updated_at`=VALUES(`updated_at`);';
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->setTimestamps("2018-06-18 10:00:00","updated_at","created_at")->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_query_without_timestamps_if_there_are_none(){
        //CREATED_AT should not be updated
        $expectedQuery = 'INSERT INTO `teeth` (`bite`) VALUES ("1") ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`);';
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->setTimestamps("2018-06-18 10:00:00",null,null)->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_query_with_just_updated_at(){
        //CREATED_AT should not be updated
        $expectedQuery = 'INSERT INTO `teeth` (`bite`,`LastModified`) VALUES ("1","2018-06-18 10:00:00") ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`),`LastModified`=VALUES(`LastModified`);';
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->setTimestamps("2018-06-18 10:00:00","LastModified",null)->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_query_with_just_created_at(){
        //CREATED_AT should not be updated
        $expectedQuery = 'INSERT INTO `teeth` (`bite`,`date_this_shit_was_made`) VALUES ("1","2018-06-18 10:00:00") ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`);';
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->setTimestamps("2018-06-18 10:00:00",null,"date_this_shit_was_made")->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_query_with_multiple_timestamps(){
        $expectedQuery = 'INSERT INTO `teeth` (`bite`,`updated_at`,`created_at`) VALUES ("1","2018-06-18 10:00:00","2018-06-18 10:00:00"),("2","2018-06-18 10:00:00","2018-06-18 10:00:00") ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`),`updated_at`=VALUES(`updated_at`);';
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
            new UploaderRecord([
                "bite" => 2,
            ]),
        ];
        $query = OnDuplicateRawGenerator::make()->setTimestamps("2018-06-18 10:00:00","updated_at","created_at")->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_query_with_multiple_timestamps_from_raw(){
        $expectedQuery = 'INSERT INTO `teeth` (`bite`,`updated_at`,`created_at`) VALUES ("1","2018-06-18 10:00:00","2018-06-18 10:00:00"),("2","2018-06-18 10:00:00","2018-06-18 10:00:00") ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`),`updated_at`=VALUES(`updated_at`);';
        $rows = [
            [
                "bite" => 1,
            ],
            [
                "bite" => 2,
            ],
        ];
        $query = OnDuplicateRawGenerator::make()->setTimestamps("2018-06-18 10:00:00","updated_at","created_at")->generateRaw('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertNull($query->getBindings());
    }

    public function test_it_generates_a_null_when_there_are_no_rows_even_with_timestamps(){
        $this->assertEquals(OnDuplicateRawGenerator::make()->setTimestamps("2018-06-18 10:00:00","updated_at","created_at")->generate('users',[]),null);
    }

    protected function getTestResources()
    {
        return [
            [
                'customer_number'      => 1148,
                'email'                => 'john@example.com',
                'password'             => '$2y$10$umonN4rhJkJFOk3nwH34/eok5yRsx5mUFUQE2.VK92P1RyxdDB9bm',
                'name'                 => 'Super John',
                'active'               => true,
                'tax_exempt'           => false,
                'address'              => '70 Bowman St. South Windsor, CT 06074',
                'phone'                => '202-555-0116',
                'password_valid_until' => '2018-09-13',
                'created_at'           => '2017-09-13',
                'updated_at'           => '2017-09-13',
            ],
            [
                'customer_number'      => 1150,
                'email'                => 'jane@example.com',
                'password'             => '$2y$10$umonN4rhJkJFOk3nwH34/eok5yRsx5mUFUQE2.VK92P1RyxdDB9bm',
                'name'                 => 'Wonder Jane',
                'active'               => true,
                'tax_exempt'           => false,
                'address'              => '4 Goldfield Rd. Honolulu, HI 96815',
                'phone'                => '202-555-0143',
                'password_valid_until' => '2018-06-18',
                'created_at'           => '2017-06-18',
                'updated_at'           => '2017-06-18',
            ],
        ];
    }
}
