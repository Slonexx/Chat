<?php

use App\Http\Controllers\Entity\PopapController;
use App\Http\Controllers\Entity\widgetController;
use App\Http\Controllers\initialization\indexController;
use App\Http\Controllers\Setting\CreateAuthTokenController;
use App\Http\Controllers\Setting\organizationController;
use App\Http\Controllers\Setting\templateController;
use App\Http\Controllers\vendor\vendorEndpoint;
use Illuminate\Support\Facades\Route;

//main windows
Route::get('/', [indexController::class, 'initialization']);
Route::get('/{accountId}/', [indexController::class, 'index'])->name('main');
Route::get('error/{accountId}/', [indexController::class, 'error'])->name('error');

//Setting get Employee
Route::get('/Setting/createToken/{accountId}', [CreateAuthTokenController::class, 'getCreateAuthToken'])->name('creatEmployee');
Route::post('/Setting/createToken/{accountId}', [CreateAuthTokenController::class, 'postCreateAuthToken']);

Route::get('/Setting/get/employee/{accountId}', [CreateAuthTokenController::class, 'getEmployee']);
Route::get('/Setting/create/employee/{accountId}', [CreateAuthTokenController::class, 'createEmployee']);
Route::get('/Setting/delete/employee/{accountId}', [CreateAuthTokenController::class, 'deleteEmployee']);

//Setting for
Route::get('/Setting/organization/{accountId}', [organizationController::class, 'getCreate'])->name('creatOrganization');
Route::post('/Setting/organization/{accountId}', [organizationController::class, 'postCreate']);

Route::get('/Setting/organization/get/Licenses/{accountId}', [organizationController::class, 'getLicenses']);
Route::get('/Setting/organization/create/Licenses/{accountId}', [organizationController::class, 'createLicenses']);
Route::get('/Setting/organization/delete/Licenses/{accountId}', [organizationController::class, 'deleteLicenses']);

Route::get('/Setting/template/{accountId}', [templateController::class, 'getCreate'])->name('template');
Route::post('/Setting/template/{accountId}', [templateController::class, 'postCreate']);
Route::get('/Setting/template/get/attributes/{accountId}', [templateController::class, 'getAttributes']);
Route::get('/Setting/template/create/poles/{accountId}', [templateController::class, 'getCreatePoles']);
Route::get('/Setting/template/nameuid/poles/{accountId}', [templateController::class, 'getNameUIDPoles']);
Route::get('/Setting/template/delete/poles/{accountId}', [templateController::class, 'deletePoles']);
//Widget
Route::get('/widget/{object}', [widgetController::class, 'widgetObject']);
Route::get('/widget/get/Data', [widgetController::class, 'widgetGetData']);

//Popup
Route::get('/Popup/{object}', [PopapController::class, 'Popup']);
Route::get('/Popup/template/message/Show', [PopapController::class, 'template']);
Route::get('/Popup/template/message/get/All', [PopapController::class, 'getTemplate']);
Route::get('/Popup/template/message/get/where/name', [PopapController::class, 'searchTemplate']);
Route::get('/Popup/template/message/get/information/messenger', [PopapController::class, 'messenger']);
Route::get('/Popup/template/message/get/information/chatapp', [PopapController::class, 'information']);
Route::get('/Popup/template/message/get/send/message', [PopapController::class, 'sendMessage']);

//Install or delete web app
Route::put('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'put']);
Route::delete('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'delete']);
