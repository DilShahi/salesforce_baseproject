<?php

use App\Http\Controllers\SalesforceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/oauth/salesforce/redirect', [SalesforceController::class, 'redirect'])->name('sf.redirect');
Route::get('/oauth/salesforce/callback', [SalesforceController::class, 'callback'])->name('sf.callback');
Route::post('/oauth/salesforce/logout', [SalesforceController::class, 'logout'])->name('sf.logout');

Route::get('/user/list', [UserController::class, 'userlist'])->name('salesforce.userlist');
Route::get('/user/{userId}/mitoco', [UserController::class, 'userevent'])->name('salesforce.user.event');
Route::post('/user/mitoco/{userId}/summary', [UserController::class, 'eventsummary'])->name('salesforce.user.event.summary');
Route::get('/salesforce/getevents', [SalesforceController::class, 'getevents'])
    ->name('salesforce.getevents');
