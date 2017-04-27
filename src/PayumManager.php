<?php

namespace Recca0120\LaravelPayum;

use Illuminate\Support\Manager;

class PayumManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['payum']['default'];
    }

    /**
     * createDriver.
     *
     * @param string $driver
     * @return \Recca0120\LaravelPayum\PayumWrapper
     */
    protected function createDriver($driver)
    {
        return new PayumDecorator($this->app['payum'], $driver);
    }
}
