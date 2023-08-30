<?php

use App\Http\Controllers\vendor\vendorEndpoint;
use Illuminate\Support\Facades\Route;

Route::put('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'Put']);
