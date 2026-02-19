<?php

use App\Http\Controllers\SalesforceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user/list', [UserController::class, 'userlist'])->name('salesforce.userlist');
Route::get('/salesforce/getevents', [SalesforceController::class, 'getevents'])
    ->name('salesforce.getevents');
