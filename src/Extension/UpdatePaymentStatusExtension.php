<?php

namespace Recca0120\LaravelPayum\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;

class UpdatePaymentStatusExtension implements ExtensionInterface
{
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
        if ($payment instanceof PaymentInterface && method_exists($payment, 'setStatus')) {
            /* @var Payment $payment */
            $status = new GetHumanStatus($payment);
            $context->getGateway()->execute($status);
            $payment->setStatus($status->getValue());
        }
    }
}
