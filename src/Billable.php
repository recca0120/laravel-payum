<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Support\Str;
use Payum\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait Billable
{
    /**
     * authorize.
     *
     * @param  array  $options
     * @param  string $driver
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function authorize($options = [], $driver = null)
    {
        return $this->payum('authorize', $options, $driver);
    }

    /**
     * capture.
     *
     * @param  array  $options
     * @param  string $driver
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function capture($options = [], $driver = null)
    {
        return $this->payum('capture', $options, $driver);
    }

    /**
     * payum.
     *
     * @param  array  $options
     * @param  string $driver
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function payum($method, $options = [], $driver = null)
    {
        $payumDecorator = $this->getPayumDecorator($driver);
        $driver = $payumDecorator->driver();
        $payum = $payumDecorator->getPayum();

        return new RedirectResponse(call_user_func_array([$payum, $method], [function (PaymentInterface $payment) use ($method, $options, $driver) {
            $method = sprintf('%s%s', $method, Str::studly($driver));

            return call_user_func_array([$this, $method], [$payment, $options]);
        }]));
    }

    /**
     * getPayumDecorator.
     *
     * @param  string $driver
     * @return \Recca0120\LaravelPayum\PayumDecorator
     */
    protected function getPayumDecorator($driver = null)
    {
        return $this->getPayumManager()->driver($driver);
    }

    /**
     * getPayumManager.
     *
     * @return \Recca0120\LaravelPayum\PayumManager
     */
    protected function getPayumManager()
    {
        return app(PayumManager::class);
    }
}
