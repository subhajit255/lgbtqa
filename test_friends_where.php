<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) {
    $q = $user->friends()->where('id', '!=', $user->id)->where('user_type', 3)->where('is_active', 1)->where('is_blocked', 0);
    echo $q->toSql() . "\n";
    print_r($q->getBindings());
}
