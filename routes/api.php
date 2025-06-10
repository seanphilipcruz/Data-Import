<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('customers')->group(function () {
    Route::get('', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::post('import', [App\Http\Controllers\CustomerController::class, 'import'])->name('customers.import');
    Route::post('update', [App\Http\Controllers\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('delete', [App\Http\Controllers\CustomerController::class, 'destroy'])->name('customers.delete');
});
