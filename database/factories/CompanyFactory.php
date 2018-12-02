<?php

use Faker\Generator as Faker;

$factory->define(App\Company::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'quota_bytes' => $faker->numberBetween($min = 100, $max = 1000000000000000000)
    ];
});
