<?php

namespace Recca0120\LaravelPayum\Events;

use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusChanged
{
    public $status;

    public $payment;

    public function __construct(GetStatusInterface $status, PaymentInterface $payment)
    {
        $this->status = $status;
        $this->payment = $payment;
    }
}
