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
use Recca0120\LaravelPayum\Contracts\PaymentStatus;

class UpdatePaymentStatusExtension implements ExtensionInterface
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

        $this->dispatch($context, $request);
    }

    protected function dispatch(Context $context, $request)
    {
        $payment = $request->getFirstModel();
        if (($payment instanceof PaymentInterface) === false) {
            return;
        }

        $status = $this->getStatus($context, $payment);
        $this->events->fire(new PaymentStatusChanged($status, $payment));
    }

    protected function getStatus(Context $context, $payment)
    {
        $status = new GetHumanStatus($payment);
        $context->getGateway()->execute($status);

        if ($payment instanceof PaymentStatus) {
            $payment->setStatus($status->getValue());
        }

        return $status;
    }
}
