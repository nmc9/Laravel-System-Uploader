<?php

namespace Uploader\Tests\Unit;

use Nmc9\Kfir\QueryObject;
use Nmc9\Kfir\ReplaceGenerator;
use PHPUnit\Framework\TestCase;

class ReplaceGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_a_query_object_for_a_given_resource()
    {
        $resources = $this->getTestResources();
        $expectedQuery = 'REPLACE INTO `users` (`customer_number`,`email`,`password`,`name`,`active`,`tax_exempt`,`address`,`phone`,`password_valid_until`,`created_at`,`updated_at`) VALUES (?,?,?,?,?,?,?,?,?,?,?),(?,?,?,?,?,?,?,?,?,?,?);';
        $expectedBindings = [
            1148, 'john@example.com', '$2y$10$umonN4rhJkJFOk3nwH34/eok5yRsx5mUFUQE2.VK92P1RyxdDB9bm', 'Super John', true, false, '70 Bowman St. South Windsor, CT 06074', '202-555-0116', '2018-09-13', '2017-09-13', '2017-09-13',
            1150, 'jane@example.com', '$2y$10$umonN4rhJkJFOk3nwH34/eok5yRsx5mUFUQE2.VK92P1RyxdDB9bm', 'Wonder Jane', true, false, '4 Goldfield Rd. Honolulu, HI 96815', '202-555-0143', '2018-06-18', '2017-06-18', '2017-06-18',
        ];

        $queryObject = (new ReplaceGenerator)->generate('users', $resources);

        $this->assertInstanceOf(QueryObject::class, $queryObject);
        $this->assertEquals($expectedQuery, $queryObject->getQuery());
        $this->assertEquals($expectedBindings, $queryObject->getBindings());
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
