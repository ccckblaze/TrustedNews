<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Sub-domains

Route::group(array('domain' => 'mp.{domain}.com'), function() {
    Route::any('/', 'UserController\MPController@show');
});

// Domain

Route::get('/', function () {
    return view('welcome', ['author' =>  'Steve']);
});

Route::get('/search', 'UserController\SearchController@search');

// test
Route::get('/login', function () {
    return "login page";
});

Route::get('/admin', ['middleware' => 'auth', function () {
    return view('welcome', ['author' =>  'Administrator']);
}]);

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});
