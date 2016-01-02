<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/user', function () {
    $user = \App\User::where("id", "<", "10")->get();
    return Response::json(['user'=>$user],200);
});



//Route::get('/test', function(){
//    $header = \Illuminate\Support\Facades\Request::header('token');
//    return Response::json(['token' => $header], 200);
//});

Route::post('register', 'ShineController@register');
Route::post('login', 'ShineController@login');
Route::post('facebooklogin', 'ShineController@facebookLogin');
Route::post('update', 'ShineController@update');
Route::get('schools', 'ShineController@getSchools');
Route::get('user', 'ShineController@getUser')->middleware(['AccessTokenMiddleware']);
Route::get('users', 'ShineController@getUsers')->middleware(['AccessTokenMiddleware']);
Route::get('photos', 'ShineController@getPhotos')->middleware(['AccessTokenMiddleware']);
Route::get('/test', 'ShineController@test');
Route::POST('CheckUser', 'ShineController@checkUser');
Route::get('fetchCurrentUser', 'ShineController@fetchCurrentUser')->middleware(['AccessTokenMiddleware']);
Route::get('getTopSchools', 'ShineController@getTopSchools');
Route::get('getTopStudents', 'ShineController@getTopStudents');
Route::post('vote', 'ShineController@vote')->middleware(['AccessTokenMiddleware']);
Route::get('notif', 'ShineController@notif');