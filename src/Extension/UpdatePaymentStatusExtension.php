<?php

namespace Recca0120\LaravelPayum\Extension;

use Payum\Core\Request\Generic;
use Payum\Core\Extension\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Payum\Core\Extension\ExtensionInterface;
use Recca0120\LaravelPayum\Events\StatusChanged;
use Recca0120\LaravelPayum\Model\StatusInterface;
use Recca0120\LaravelPayum\Contracts\PaymentStatus;

class UpdatePaymentStatusExtension implements ExtensionInterface
{
    protected $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function onPreExecute(Context $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onExecute(Context $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onPostExecute(Context $context)
    {
        if ($context->getPrevious()) {
            return;
        }
        /** @var Generic $request */
        $request = $context->getRequest();
        if (false == $request instanceof Generic || $request instanceof GetStatusInterface) {
            return;
        }

        $payment = $request->getFirstModel();
        if ($payment instanceof PaymentInterface) {
            /* @var Payment $payment */
            $status = new GetHumanStatus($payment);
            $context->getGateway()->execute($status);
            if ($payment instanceof PaymentStatus) {
                $payment->setStatus($status->getValue());
            }
            $this->events->fire(new StatusChanged($status, $payment));
        }
    }
}
