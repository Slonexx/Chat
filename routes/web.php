<?php

use App\Http\Controllers\vendor\vendorEndpoint;
use Illuminate\Support\Facades\Route;

Route::get('Config/vendor-endpoint/', [vendorEndpoint::class, 'Put']);
