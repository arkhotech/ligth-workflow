<?php

use Faker\Generator as Faker;

$factory->define(App\Process::class, function (Faker $faker) {
    return [
        //
        "name" => $faker->name
    ];
});
