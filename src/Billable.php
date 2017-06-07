<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Support\Str;
use Payum\Core\Model\PaymentInterface;

trait Billable
{
    public function charge($amount, $options = [], $driver = null)
    {
        $payumManager = $this->getPayumManager();
        $driver = $driver ?: $payumManager->getGatewayName();

        return $payumManager->capture(function (PaymentInterface $payment) use ($amount, $options, $driver) {
            $method = sprintf(
                'charge%s',
                Str::studly($driver)
            );

            return call_user_func_array([$this, $method], [$payment, $amount, $options]);
        });
    }

    public function getPayumManager()
    {
        return app(PayumManager::class);
    }
}
