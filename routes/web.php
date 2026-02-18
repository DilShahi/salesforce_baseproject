<?php

use App\Http\Controllers\SalesforceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/salesforce/getevents', [SalesforceController::class, 'getevents'])
    ->name('salesforce.getevents');
