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

Route::any('capture/{payum_token?}', [
    'as' => 'capture',
    'uses' => 'WebhookController@handleCapture',
]);

Route::any('authorize/{payum_token}', [
    'as' => 'authorize',
    'uses' => 'WebhookController@handleAuthorize',
]);

Route::any('notify/{payum_token}', [
    'as' => 'notify',
    'uses' => 'WebhookController@handleNotify',
]);

Route::any('notify/unsafe/{gatewayName}', [
    'as' => 'notify.unsafe',
    'uses' => 'WebhookController@handleNotifyUnsafe',
]);

Route::any('cancel/{payum_token}', [
    'as' => 'cancel',
    'uses' => 'WebhookController@handleCancel',
]);

Route::any('refund/{payum_token}', [
    'as' => 'refund',
    'uses' => 'WebhookController@handleRefund',
]);

Route::any('payout/{payum_token}', [
    'as' => 'payout',
    'uses' => 'WebhookController@handlePayout',
]);

Route::any('sync/{payum_token}', [
    'as' => 'sync',
    'uses' => 'WebhookController@handleSync',
]);
