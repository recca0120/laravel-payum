<?php

namespace Recca0120\LaravelPayum\Events;

use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusChanged
{
    /**
     * $status.
     *
     * @var \Payum\Core\Request\GetStatusInterface
     */
    public $status;

    /**
     * $payment.
     *
     * @var \Payum\Core\Model\PaymentInterface
     */
    public $payment;

    /**
     * __construct.
     *
     * @param \Payum\Core\Request\GetStatusInterface $status
     * @param \Payum\Core\Model\PaymentInterface $payment
     */
    public function __construct(GetStatusInterface $status, PaymentInterface $payment)
    {
        $this->status = $status;
        $this->payment = $payment;
    }
}
