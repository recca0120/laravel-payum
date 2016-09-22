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

Route::any('capture/{payumToken?}', [
    'as' => 'capture',
    'uses' => 'PaymentController@capture',
]);

Route::any('authorize/{payumToken}', [
    'as' => 'authorize',
    'uses' => 'PaymentController@authorize',
]);

Route::any('notify/{payumToken}', [
    'as' => 'notify',
    'uses' => 'PaymentController@notify',
]);

Route::any('notify/unsafe/{gatewayName}', [
    'as' => 'notify.unsafe',
    'uses' => 'PaymentController@notifyUnsafe',
]);

Route::any('cancel/{payumToken}', [
    'as' => 'cancel',
    'uses' => 'PaymentController@cancel',
]);

Route::any('refund/{payumToken}', [
    'as' => 'refund',
    'uses' => 'PaymentController@refund',
]);

Route::any('payout/{payumToken}', [
    'as' => 'payout',
    'uses' => 'PaymentController@payout',
]);

Route::any('sync/{payumToken}', [
    'as' => 'sync',
    'uses' => 'PaymentController@sync',
]);
