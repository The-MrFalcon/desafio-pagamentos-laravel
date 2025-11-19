<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\User::count();
echo "User count: $count\n";

if ($count > 0) {
    $user = \App\Models\User::first();
    echo "First user: " . json_encode($user->toArray()) . "\n";
}
