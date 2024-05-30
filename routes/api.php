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

Route::middleware('api')->post('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);
Route::middleware('api')->get('integration/entity/counterparty/all/metadata', [counterparty::class, 'metadataStates']);
Route::get('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);

Route::post('/webhook', [sendTemplateController::class, 'sendTemplate']);

//createCounterparty
Route::get('counterparty/create/{accountId}', [CounterpartyController::class, 'create']);
Route::get('customerorder/create/{accountId}', [CustomerorderController::class, 'create']);
//Route::get('counterparty/import_dialogs/{accountId}', [CounterpartyController::class, 'importConversationsInNotes']);
//Route::post('counterparty/sendNotes/{accountId}', [CounterpartyController::class, 'sendNotes']);
Route::get('counterparty/notes/check/{accountId}', [CounterpartyController::class, 'checkRate'])->name('checkNotes');


//Route::get("check", [TestController::class, "check"]);
Route::post("webhook/yes", [TestController::class, "yes"]);
Route::post("webhook/{accountId}/licenses/{lineId}/messengers/{messengers}", [webHookController::class, "callbackUrls"]);
Route::post('counterparty/notes/create/{accountId}/line/{lineId}/messenger/{messenger}', [webHookController::class, 'createCounterpartyNotes']);

Route::post("integration/webhook/counterparty/notes/create", [webHookController::class, "callbackUrlsIntrg"]);
Route::post('integration/counterparty/notes/create', [webHookController::class, 'createCounterpartyNotesIntgr']);

