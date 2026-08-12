<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) {
    echo "User ID: {$user->id}\n";
    $query = $user->friends()->where('id', '!=', $user->id);
    echo $query->toSql() . "\n";
    print_r($query->getBindings());
} else {
    echo "No user found.\n";
}
