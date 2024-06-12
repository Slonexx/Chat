<?php

use App\Http\Controllers\CounterpartyController;
use App\Http\Controllers\CustomerorderController;
use App\Http\Controllers\integration\connectController;
use App\Http\Controllers\integration\entity\counterparty;
use App\Http\Controllers\Setting\sendTemplateController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\webhook\webHookController;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "integration"], function () {
    Route::get('client/connect/get/employee/{accountId}', [connectController::class, 'connectClient']);
});

//Route::middleware('api')->post('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);
Route::middleware('api')->get('integration/entity/counterparty/all/metadata', [counterparty::class, 'metadataStates']);
//Route::get('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);

//Route::get("check", [TestController::class, "check"]);
//Route::post("webhook/yes", [TestController::class, "yes"]);
//Route::get('counterparty/import_dialogs/{accountId}', [CounterpartyController::class, 'importConversationsInNotes']);
//Route::post('counterparty/sendNotes/{accountId}', [CounterpartyController::class, 'sendNotes']);


Route::post('/webhook', [sendTemplateController::class, 'sendTemplate']);

//massFindOrCreate
Route::get('counterparty/create/{accountId}', [CounterpartyController::class, 'massFindOrCreate']);
Route::get('customerorder/create/{accountId}', [CustomerorderController::class, 'massFindOrCreate']);

Route::get('counterparty/notes/check/{accountId}', [CounterpartyController::class, 'checkRate'])->name('checkNotes');
//create by webhook
Route::post("webhook/{accountId}/licenses/{lineId}/messengers/{messengers}", [webHookController::class, "callbackUrls"]);
Route::post('counterparty/notes/create/{accountId}/line/{lineId}/messenger/{messenger}', [CounterpartyController::class, 'createCounterpartyNotes']);
Route::post('customerorder/create/{accountId}/line/{lineId}/messenger/{messenger}', [CustomerorderController::class, 'findOrCreate']);
//create by webhook from integration
Route::post("integration/webhook/counterparty/notes/create", [webHookController::class, "callbackUrlsIntrg"]);
Route::post('integration/counterparty/notes/create', [webHookController::class, 'createCounterpartyNotesIntgr']);
Route::post("integration/webhook/customerorder/create", [webHookController::class, "callbackUrlsCustomerorderIntrg"]);
Route::post('integration/customerorder/create', [webHookController::class, 'createOrderIntgr']);


