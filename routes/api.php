<?php

use App\Http\Controllers\integration\connectController;
use App\Http\Controllers\integration\entity\counterparty;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(["prefix" => "integration"], function () {
    Route::get('client/connect/get/employee/{accountId}', [connectController::class, 'connectClient']);
});

Route::middleware('api')->post('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);
Route::middleware('api')->get('integration/entity/counterparty/all/metadata', [counterparty::class, 'metadataStates']);
Route::get('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);

Route::get("check", [TestController::class, "check"]);
