<?php

use App\Http\Controllers\integration\entity\counterparty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('api')->post('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);
Route::middleware('api')->get('integration/entity/counterparty/all/metadata', [counterparty::class, 'metadataStates']);
Route::get('integration/entity/counterparty', [counterparty::class, 'creatingAgent']);
