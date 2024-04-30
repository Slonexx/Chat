<?php

use App\Http\Controllers\CounterpartyController;
use App\Http\Controllers\CustomerorderController;
use App\Http\Controllers\integration\connectController;
use App\Http\Controllers\integration\entity\counterparty;
use App\Http\Controllers\Setting\AutomatizationController;
use App\Http\Controllers\Setting\sendTemplateController;
use App\Http\Controllers\TestController;
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
Route::get('counterparty/import_dialogs/{accountId}', [CounterpartyController::class, 'importConversationsInNotes']);
Route::post('counterparty/sendNotes/{accountId}', [CounterpartyController::class, 'sendNotes']);
Route::get('counterparty/notes/check/{accountId}', [CounterpartyController::class, 'checkRate']);

Route::get("check", [TestController::class, "check"]);
