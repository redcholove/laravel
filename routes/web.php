<?php

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

Route::group(['prefix'=>'api','middleware'=>'throttle:1000,60'],function(){
    Route::get('users',function(){
        return \App\User::all();
    });
});

Route::group(['prefix' => 'api', 'middleware' => 'cors'], function()
{
    Route::post('Register', 'Controller@Register');
    Route::post('Login', 'Controller@Login');
    Route::post('otherpage','Controller@OtherPage');
    Route::post('UploadImg','Controller@UploadImg');
    Route::post('GetForumType','Controller@GetForumType');
    Route::post('PostChooseForum','Controller@PostChooseForum');
    Route::post('PostNewsfeed','Controller@PostNewsfeed');
    Route::post('GetNewsfeed','Controller@GetNewsfeed');
    Route::post('CheckCardToday','Controller@CheckCardToday');
    Route::post('PullCard','Controller@PullCard');
    Route::post('Comment','Controller@Comment');
    Route::post('GetComment','Controller@GetComment');
    Route::post('PostLove','Controller@PostLove');
    Route::post('SendInvite','Controller@SendInvite');
    Route::post('getBell','Controller@getBell');
    Route::post('changeUserName','Controller@changeUserName');
    Route::post('readBell','Controller@readBell');
    Route::post('LookWhoPullMe','Controller@LookWhoPullMe');
    Route::post('AcceptInvite','Controller@AcceptInvite');
    Route::post('getFriendList','Controller@getFriendList');
    Route::post('createchat','Controller@createchat');
    Route::post('getchatroom','Controller@getchatroom');
    Route::post('getUser','Controller@getUser');
    Route::post('getClassSchedule','Controller@getClassSchedule');
    Route::post('getTitle','Controller@getTitle');
    Route::post('getTestScore', 'Controller@getTestScore');
    Route::post('getTestSchedule', 'Controller@getTestSchedule');
    Route::post('GetSearchResult', 'Controller@GetSearchResult');
    Route::post('StoreSearchRecord', 'Controller@StoreSearchRecord');
    Route::post('SearchHistory','Controller@SearchHistory');
    Route::post('Edit_Post', 'Controller@Edit_Post');
});
