<?php

namespace Recca0120\LaravelPayum\Extension;

use Payum\Core\Request\Generic;
use Payum\Core\Extension\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Payum\Core\Extension\ExtensionInterface;
use Recca0120\LaravelPayum\Events\PaymentStatusChanged;

class PaymentStatusExtension implements ExtensionInterface
{
    /**
     * $events.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * __construct.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
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
        if (false === $request instanceof Generic || $request instanceof GetStatusInterface) {
            return;
        }

        $payment = $request->getFirstModel();

        if (($payment instanceof PaymentInterface) === false) {
            return;
        }

        $context->getGateway()->execute($status = new GetHumanStatus($payment));

        $this->events->fire(new PaymentStatusChanged($status, $payment));
    }
}
