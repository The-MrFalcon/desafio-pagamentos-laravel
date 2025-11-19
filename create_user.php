<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::create([
    'id' => 1,
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'subadquirente' => 'subadq_a',
]);

echo 'User created';
