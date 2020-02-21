<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Company;
use App\Models\Customer;
use Faker\Generator as Faker;
use Nmc9\Uploader\Example\CustomerBalance;

$factory->define(CustomerBalance::class, function (Faker $faker) {
    return [
        'company_id' => $faker->unique()->randomNumber,
        'customer_id' => $faker->unique()->randomNumber,
        'balance' => $faker->randomNumber(3),
    ];
});
