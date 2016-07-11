<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\Payment;

trait PrepareBehavior
{
    /**
     * prepare.
     *
     * @method prepare
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param string                          $gatewayName
     *
     * @return mixed
     */
    public function prepare(Payment $payment, $gatewayName = null)
    {
        if (empty($gatewayName) === true && property_exists($this, 'gatewayName') === true) {
            $gatewayName = $this->gatewayName;
        }

        return $payment->prepare($gatewayName, function ($payment, $gatewayName, $storage, $payum) {
            return $this->onPrepare($payment, $gatewayName, $storage, $payum);
        });
    }

    /**
     * Prepare you payment.
     *
     * @method onPrepare
     *
     * @param \Payum\Core\Model\PaymentInterface   $payment
     * @param string                               $gatewayName
     * @param \Payum\Core\Storage\StorageInterface $storage
     * @param \Payum\Core\Payum
     *
     * @return \Payum\Core\Model\PaymentInterface
     */
    abstract protected function onPrepare($payment, $gatewayName, $storage, $payum);
}
