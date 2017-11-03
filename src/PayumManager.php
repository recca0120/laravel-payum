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
     * @return \Recca0120\LaravelPayum\Gateway
     */
    protected function createDriver($driver)
    {
        return new Gateway($this->app['payum'], $this->app['request'], $driver);
    }
}
