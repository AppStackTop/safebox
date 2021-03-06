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

$factory->define(\App\Models\User::class, function (Faker\Generator $faker)
{
    static $password;

    return [
        'name'           => $faker->name,
        'email'          => $faker->unique()->safeEmail,
        'password'       => $password ?: $password = bcrypt('secret'),
        'api_token'      => str_random(60),
        'remember_token' => str_random(10),
    ];
});

$factory->define(\App\Models\Client::class, function (\Faker\Generator $faker)
{
    return [
        'name'  => $faker->firstname . ' ' . $faker->lastName,
        'email' => $faker->safeEmail,
        'phone' => $faker->e164PhoneNumber,
    ];
});

$factory->define(\App\Models\Site::class, function (\Faker\Generator $faker)
{
    return [
        'url'       => 'http://' . $faker->domainName,
        'name'      => $faker->domainWord,
        'client_id' => $faker->numberBetween(1, 30),
    ];
});
