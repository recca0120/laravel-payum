<?php

namespace Recca0120\LaravelPayum\Traits;

use InvalidArgumentException;
use Recca0120\LaravelPayum\Payment;

trait PaymentPrepare
{
    /**
     * prepare.
     *
     * @method prepare
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     *
     * @return mixed
     */
    public function prepare(Payment $payment, $gatewayName = null)
    {
        $gatewayName = (empty($gatewayName) === true && empty($this->gatewayName) === false) ?
            $this->gatewayName : $gatewayName;

        if (empty($gatewayName) === true) {
            throw new InvalidArgumentException('Undefined property: '.static::class.'::$gatewayName');
        }

        return $payment->prepare($this->gatewayName, function ($payment, $storage, $payum) {
            return $this->preparePayment($payment);
        });
    }

    /**
     * preparePayment.
     *
     * @method preparePayment
     *
     * @param \Payum\Core\Model\PaymentInterface   $payment
     * @param \Payum\Core\Storage\StorageInterface $storage
     * @param \Payum\Core\Payum
     *
     * @return \Payum\Core\Model\PaymentInterface
     */
    abstract protected function preparePayment($payment, $storage, $payum);
}
