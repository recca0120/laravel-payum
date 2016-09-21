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

Route::get('payment', [
    'as' => 'payment',
    'uses' => 'PaymentController@capture',
]);

Route::any('payment/done/{payumToken}', [
    'as' => 'payment.done',
    'uses' => 'PaymentController@done',
]);
