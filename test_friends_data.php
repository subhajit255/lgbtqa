<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) {
    echo "Friends count: " . $user->friends()->count() . "\n";
    echo "Friend requests: " . App\Models\FriendRequest::count() . "\n";
    $req = App\Models\FriendRequest::first();
    if ($req) {
       echo "Req: {$req->user_id} -> {$req->friend_id} (status: {$req->status})\n";
    }
}
