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
    'uses' => 'PaymentController@receiveCapture',
]);

Route::any('authorize/{payumToken}', [
    'as' => 'authorize',
    'uses' => 'PaymentController@receiveAuthorize',
]);

Route::any('notify/{payumToken}', [
    'as' => 'notify',
    'uses' => 'PaymentController@receiveNotify',
]);

Route::any('notify/unsafe/{gatewayName}', [
    'as' => 'notify.unsafe',
    'uses' => 'PaymentController@receiveNotifyUnsafe',
]);

Route::any('cancel/{payumToken}', [
    'as' => 'cancel',
    'uses' => 'PaymentController@receiveCancel',
]);

Route::any('refund/{payumToken}', [
    'as' => 'refund',
    'uses' => 'PaymentController@receiveRefund',
]);

Route::any('payout/{payumToken}', [
    'as' => 'payout',
    'uses' => 'PaymentController@receivePayout',
]);

Route::any('sync/{payumToken}', [
    'as' => 'sync',
    'uses' => 'PaymentController@receiveSync',
]);
