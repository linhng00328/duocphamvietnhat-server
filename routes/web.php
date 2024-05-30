<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Web Theme
Route::get('/redirect-and-save-ip', 'App\Http\Controllers\Api\Customer\CustomerDynamicLinkController@redirectAndSaveIp');
Route::get('/redirect-to-link', 'App\Http\Controllers\Api\Customer\CustomerDynamicLinkController@redirectToLink');