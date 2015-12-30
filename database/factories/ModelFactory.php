<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'gender' => $faker->randomElement(array('Male', 'Female')),
        'school_id' => $faker->randomElement(array(1,2,3,4)),
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'bio' => str_random(100),
    ];
});

$factory->define(App\Vote::class, function (Faker\Generator $faker) {
    $usrArray = DB::table('users')->lists('id');
    $numberArr = [1,2,3,4,5,6,7,8,9,10];
    return [
        'subject_id' => $faker->randomElement($usrArray),
        'object_id' => $faker->randomElement($usrArray),
        'rate' => $faker->randomElement($numberArr),
    ];
});