<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/leagues', 'LeagueController@index');
Route::post('/league/create', 'LeagueController@createLeague');
Route::post('/league/{leagueId}/reset', 'LeagueController@resetLeague');
Route::get('/league/{leagueId}/games', 'LeagueController@getLeagueGames');
Route::get('/league/{leagueId}/week/{week}/games', 'LeagueController@getWeekGames');
Route::post('/league/{leagueId}/play', 'LeagueController@play');
Route::post('/league/{leagueId}/playAll', 'LeagueController@playAll');
Route::get('/league/{leagueId}/standings', 'LeagueController@standings');
