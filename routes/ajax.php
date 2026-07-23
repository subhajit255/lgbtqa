<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ajax\AjaxController;


Route::controller(AjaxController::class)->as('ajax.')->middleware(['auth'])->group(function () {
    Route::group(['as'  => 'delete.'], function () {
        Route::delete('/delete/data', 'deleteData')->name('delete.data');
    });
    Route::group(['as'  => 'status-change.'], function () {
        Route::post('/status/change', 'statusChange')->name('status.changes');
    });
});
