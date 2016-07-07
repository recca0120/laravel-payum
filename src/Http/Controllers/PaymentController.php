<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Recca0120\LaravelPayum\Traits\AuthorizePayment;
use Recca0120\LaravelPayum\Traits\CapturePayment;
use Recca0120\LaravelPayum\Traits\NotifyPayment;
use Recca0120\LaravelPayum\Traits\PayoutPayment;
use Recca0120\LaravelPayum\Traits\RefundPayment;
use Recca0120\LaravelPayum\Traits\SyncPayment;

class PaymentController extends BaseController
{
    use AuthorizePayment,
        CapturePayment,
        NotifyPayment,
        PayoutPayment,
        RefundPayment,
        SyncPayment;
}
