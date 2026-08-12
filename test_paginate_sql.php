<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) {
    DB::enableQueryLog();
    try {
        $users = $user->friends()->paginate(10);
        print_r(DB::getQueryLog());
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
