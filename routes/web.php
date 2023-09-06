<?php

use App\Http\Controllers\Entity\PopapController;
use App\Http\Controllers\Entity\widgetController;
use App\Http\Controllers\initialization\indexController;
use App\Http\Controllers\Setting\CreateAuthTokenController;
use App\Http\Controllers\vendor\vendorEndpoint;
use Illuminate\Support\Facades\Route;

//main windows
Route::get('/', [indexController::class, 'initialization']);
Route::get('/{accountId}/', [indexController::class, 'index'])->name('main');

//Setting get Employee
Route::get('/Setting/createToken/{accountId}', [CreateAuthTokenController::class, 'getCreateAuthToken']);
Route::post('/Setting/createToken/{accountId}', [CreateAuthTokenController::class, 'postCreateAuthToken']);

Route::get('/Setting/get/employee/{accountId}', [CreateAuthTokenController::class, 'getEmployee']);
Route::get('/Setting/create/employee/{accountId}', [CreateAuthTokenController::class, 'createEmployee']);
Route::get('/Setting/delete/employee/{accountId}', [CreateAuthTokenController::class, 'deleteEmployee']);

//Widget
Route::get('/widget/{object}', [widgetController::class, 'widgetObject']);
Route::get('/widget/get/Data', [widgetController::class, 'widgetGetData']);
Route::get('LOG/widget/Info/Attributes', [widgetController::class, 'LOG_widgetInfoAttributes']);

//Popup
Route::get('/Popup/{object}', [PopapController::class, 'Popup']);

//Install or delete web app
Route::put('/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'put']);
Route::delete('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'delete']);
