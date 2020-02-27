<?php

namespace Uploader\Tests\Unit;

use Nmc9\Uploader\Kfir\OnDuplicateGenerator;
use Nmc9\Uploader\Kfir\QueryObject;
use Nmc9\Uploader\UploaderRecord;
use PHPUnit\Framework\TestCase;

class OnDuplicateGeneratorTest extends TestCase
{

    public function test_it_generates_a_query_object_for_a_given_resource()
    {
        $resources = $this->getTestResources();
        $excludedColumnsFromUpdate = ['customer_number', 'password', 'created_at', 'password_valid_until'];
        $expectedQuery = 'INSERT INTO `users` (`customer_number`,`email`,`password`,`name`,`active`,`tax_exempt`,`address`,`phone`,`password_valid_until`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?,?,?,?),(?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE `email`=VALUES(`email`),`name`=VALUES(`name`),`active`=VALUES(`active`),`tax_exempt`=VALUES(`tax_exempt`),`address`=VALUES(`address`),`phone`=VALUES(`phone`),`updated_at`=VALUES(`updated_at`);';
        $expectedBindings = [
            1148, 'john@example.com', '$2y$10$umonN4rhJkJFOk3nwH34/eok5yRsx5mUFUQE2.VK92P1RyxdDB9bm', 'Super John', true, false, '70 Bowman St. South Windsor, CT 06074', '202-555-0116', '2018-09-13', '2017-09-13', '2017-09-13',
            1150, 'jane@example.com', '$2y$10$umonN4rhJkJFOk3nwH34/eok5yRsx5mUFUQE2.VK92P1RyxdDB9bm', 'Wonder Jane', true, false, '4 Goldfield Rd. Honolulu, HI 96815', '202-555-0143', '2018-06-18', '2017-06-18', '2017-06-18',
        ];

        $queryObject = (new OnDuplicateGenerator)->generateRaw('users', $resources, $excludedColumnsFromUpdate);

        $this->assertInstanceOf(QueryObject::class, $queryObject);
        $this->assertEquals($expectedQuery, $queryObject->getQuery());
        $this->assertEquals($expectedBindings, $queryObject->getBindings());
    }

    public function test_it_generates_a_null_when_there_are_no_rows(){
        $this->assertEquals(OnDuplicateGenerator::make()->generate('users',[]),null);
    }

    public function test_it_generates_a_query_object_for_an_array_with_one_record(){
        $expectedQuery = "INSERT INTO `teeth` (`bite`) VALUES (?) ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`);";
        $expectedBindings = [1];
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateGenerator::make()->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }



    public function test_it_generates_a_query_object_for_an_array_of_records(){
        $expectedQuery = "INSERT INTO `gums` (`chew`) VALUES (?),(?) ON DUPLICATE KEY UPDATE `chew`=VALUES(`chew`);";
        $expectedBindings = [1,2];
        $rows = [
            new UploaderRecord([
                "chew" => 1,
            ]),
            new UploaderRecord([
                "chew" => 2,
            ]),
        ];
        $query = OnDuplicateGenerator::make()->generate('gums',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function test_it_generates_a_query_with_timestamps_if_timestamps_are_added(){
        //CREATED_AT should not be updated
        $expectedQuery = "INSERT INTO `teeth` (`bite`,`updated_at`,`created_at`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`),`updated_at`=VALUES(`updated_at`);";
        $expectedBindings = [1,"2018-06-18 10:00:00","2018-06-18 10:00:00"];
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateGenerator::make()->setTimestamps("2018-06-18 10:00:00","updated_at","created_at")->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function test_it_generates_a_query_without_timestamps_if_there_are_none(){
        //CREATED_AT should not be updated
        $expectedQuery = "INSERT INTO `teeth` (`bite`) VALUES (?) ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`);";
        $expectedBindings = [1];
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateGenerator::make()->setTimestamps("2018-06-18 10:00:00",null,null)->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function test_it_generates_a_query_with_just_updated_at(){
        //CREATED_AT should not be updated
        $expectedQuery = "INSERT INTO `teeth` (`bite`,`LastModified`) VALUES (?,?) ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`),`LastModified`=VALUES(`LastModified`);";
        $expectedBindings = [1,"2018-06-18 10:00:00"];
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateGenerator::make()->setTimestamps("2018-06-18 10:00:00","LastModified",null)->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function test_it_generates_a_query_with_just_created_at(){
        //CREATED_AT should not be updated
        $expectedQuery = "INSERT INTO `teeth` (`bite`,`date_this_shit_was_made`) VALUES (?,?) ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`);";
        $expectedBindings = [1,"2018-06-18 10:00:00"];
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
        ];
        $query = OnDuplicateGenerator::make()->setTimestamps("2018-06-18 10:00:00",null,"date_this_shit_was_made")->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function test_it_generates_a_query_with_multiple_timestamps(){
        $expectedQuery = "INSERT INTO `teeth` (`bite`,`updated_at`,`created_at`) VALUES (?,?,?),(?,?,?) ON DUPLICATE KEY UPDATE `bite`=VALUES(`bite`),`updated_at`=VALUES(`updated_at`);";
        $expectedBindings = [1,"2018-06-18 10:00:00","2018-06-18 10:00:00",2,"2018-06-18 10:00:00","2018-06-18 10:00:00"];
        $rows = [
            new UploaderRecord([
                "bite" => 1,
            ]),
            new UploaderRecord([
                "bite" => 2,
            ]),
        ];
        $query = OnDuplicateGenerator::make()->setTimestamps("2018-06-18 10:00:00","updated_at","created_at")->generate('teeth',$rows);
        $this->assertEquals($expectedQuery, $query->getQuery());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function test_it_generates_a_null_when_there_are_no_rows_even_with_timestamps(){
        $this->assertEquals(OnDuplicateGenerator::make()->setTimestamps("2018-06-18 10:00:00","updated_at","created_at")->generate('users',[]),null);
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
