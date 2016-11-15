<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('login');
});

Route::get('login', 'View\MemberController@toLogin');
Route::get('register', 'View\MemberController@toRegister');
Route::get('category', 'View\BookController@toCategory');

Route::get('/product/category_id/{category_id}', 'View\BookController@toProduct');
Route::get('/product/{product_id}', 'View\BookController@toPdtContent');

Route::group(['prefix' => 'service', 'namespace' => 'Service'], function () {
    Route::get('validate_code/create', 'ValidateController@create');
    Route::post('validate_phone/send', 'ValidateController@sendSMS');
    Route::any('validate_email', 'ValidateController@validateEmail');

    Route::get('category/parent_id/{parent_id}', 'BookController@getCategoryByParentId');

    Route::post('register', 'MemberController@register');
    Route::post('login', 'MemberController@login');
});

