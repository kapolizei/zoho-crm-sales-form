<?php
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesController;

use Illuminate\Support\Facades\Route;

Route::post('/items', [ItemController::class, 'store']);
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show']);

Route::post('/sales', [SalesController::class, 'store']);

Route::post('/contacts', [ContactsController::class, 'store']);
Route::get('/contacts', [ContactsController::class, 'index']);

Route::post('/purchase', [PurchaseController::class, 'store']);


Route::get('/crm/status', function () {
    $token = \App\Models\ZohoToken::first();
    return response()->json(['authorized' => (bool) $token]);
});
