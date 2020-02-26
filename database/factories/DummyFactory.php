<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */


use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Nmc9\Uploader\Database\Models\Dummy;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Dummy::class, function (Faker $faker) {
	return [
		"dummy_id" => $faker->randomNumber(),
		'data' => $faker->name,
    ];
});
