<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\HomeController;

Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('/child-safety', [HomeController::class, 'childSafety'])->name('child.safety');
Route::get('/terms-and-conditions', [HomeController::class, 'termsAndConditions'])->name('terms.conditions');
Route::get('/delete-account', [HomeController::class, 'deleteAccount'])->name('delete.account');
Route::post('/delete-account', [HomeController::class, 'processDeleteAccount'])->name('delete.account.post');

Route::get('/', function () {
    return redirect(route('admin.login'));
});
Route::get('/login', function () {
    return redirect(route('admin.login'));
});
Route::get('/admin', function () {
    return redirect(route('admin.login'));
});

// Storage Link Workaround for Shared Hosting (symlink disabled)
Route::get('/storage/{path}', function ($path) {
    $path = storage_path('app/public/'.$path);

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('path', '.*');
