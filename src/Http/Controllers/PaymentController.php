<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Recca0120\LaravelPayum\Traits\PaymentAuthorize;
use Recca0120\LaravelPayum\Traits\PaymentCapture;
use Recca0120\LaravelPayum\Traits\PaymentNotify;
use Recca0120\LaravelPayum\Traits\PaymentPayout;
use Recca0120\LaravelPayum\Traits\PaymentRefund;
use Recca0120\LaravelPayum\Traits\PaymentSync;

class PaymentController extends BaseController
{
    use PaymentAuthorize,
        PaymentCapture,
        PaymentNotify,
        PaymentPayout,
        PaymentRefund,
        PaymentSync;
}
