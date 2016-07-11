<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

trait PaymentBehavior
{
    use AuthorizeBehavior,
        CaptureBehavior,
        NotifyBehavior,
        PayoutBehavior,
        RefundBehavior,
        SyncBehavior;
}
