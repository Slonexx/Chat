<?php

use App\Http\Controllers\initialization\indexController;
use App\Http\Controllers\Setting\CreateAuthTokenController;
use App\Http\Controllers\vendor\vendorEndpoint;
use Illuminate\Support\Facades\Route;

//main windows
Route::get('/', [indexController::class, 'initialization']);
Route::get('/{accountId}/', [indexController::class, 'index'])->name('main');


Route::get('/Setting/createToken/{accountId}', [CreateAuthTokenController::class, 'getCreateAuthToken']);
Route::post('/Setting/createToken/{accountId}', [CreateAuthTokenController::class, 'postCreateAuthToken']);


//Install or delete web app
Route::put('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'put']);
Route::delete('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'delete']);
