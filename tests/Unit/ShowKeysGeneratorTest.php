<?php

namespace Uploader\Tests\Unit;

use Nmc9\Uploader\Kfir\QueryObject;
use Nmc9\Uploader\Kfir\ShowKeysGenerator;
use PHPUnit\Framework\TestCase;

class ShowKeysGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_a_query_object_for_a_given_resource()
    {
        $resources = [
            'customer_number',
            'company_id',
            'balance',
            'something else'
        ];
        $expectedQuery = 'SHOW KEYS FROM `users` WHERE `Column_name` IN (?,?,?,?);';

        $queryObject = (new ShowKeysGenerator)->generate('users', $resources);

        $this->assertInstanceOf(QueryObject::class, $queryObject);
        $this->assertEquals($expectedQuery, $queryObject->getQuery());
        $this->assertEquals($resources, $queryObject->getBindings());
    }

    /** @test */
    public function it_generates_a_query_object_for_a_single_resource()
    {
        $resources = [
            'single',
        ];
        $expectedQuery = 'SHOW KEYS FROM `users` WHERE `Column_name` IN (?);';

        $queryObject = (new ShowKeysGenerator)->generate('users', $resources);

        $this->assertInstanceOf(QueryObject::class, $queryObject);
        $this->assertEquals($expectedQuery, $queryObject->getQuery());
        $this->assertEquals($resources, $queryObject->getBindings());
    }
}
