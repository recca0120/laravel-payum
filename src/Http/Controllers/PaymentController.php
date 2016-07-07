<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Recca0120\LaravelPayum\Http\Controllers\Traits\AuthorizeTrait;
use Recca0120\LaravelPayum\Http\Controllers\Traits\CaptureTrait;
use Recca0120\LaravelPayum\Http\Controllers\Traits\NotifyTrait;
use Recca0120\LaravelPayum\Http\Controllers\Traits\PayoutTrait;
use Recca0120\LaravelPayum\Http\Controllers\Traits\RefundTrait;
use Recca0120\LaravelPayum\Http\Controllers\Traits\SyncTrait;

class PaymentController extends BaseController
{
    use AuthorizeTrait,
        CaptureTrait,
        NotifyTrait,
        PayoutTrait,
        RefundTrait,
        SyncTrait;
}
