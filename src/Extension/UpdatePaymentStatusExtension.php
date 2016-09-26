<?php

namespace Recca0120\LaravelPayum\Extension;

use Illuminate\Contracts\Events\Dispatcher;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Recca0120\LaravelPayum\Events\StatusChanged;

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
            $this->events->fire(new StatusChanged($status, $payment));

            if (method_exists($payment, 'setStatus') === true) {
                $payment->setStatus($status->getValue());
            }
        }
    }
}
