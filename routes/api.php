<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('/events', 'EventsController@index');
Route::post('/events/store', 'EventsController@store');
Route::put('/events/{event_id}/update', 'EventsController@update');
Route::put('/events/{event_id}/mark/completed', 'EventsController@markCompleted');
Route::delete('/events/{event_id}/destroy', 'EventsController@destroy');

Route::get('/doctors', 'EventsController@getDoctors');
Route::get('/patients', 'EventsController@getPatients');
Route::get('/live-sessions/{patient_id}', 'EventsController@getPatientSessions');
