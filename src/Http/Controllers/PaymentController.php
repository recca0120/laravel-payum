<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Recca0120\LaravelPayum\Http\Controllers\Behavior\PaymentBehavior;

class PaymentController extends BaseController
{
    use PaymentBehavior;
}
