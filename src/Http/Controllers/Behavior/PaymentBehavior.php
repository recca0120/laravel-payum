<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

trait PaymentBehavior
{
    use AuthorizeBehavior;
    use CaptureBehavior;
    use CancelBehavior;
    use NotifyBehavior;
    use PayoutBehavior;
    use RefundBehavior;
    use SyncBehavior;
}
