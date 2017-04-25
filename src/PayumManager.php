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
     * @param string $driver [description]
     * @return \Recca0120\LaravelPayum\PayumWrapper
     */
    protected function createDriver($driver)
    {
        return new PayumWrapper($this->app['payum'], $driver);
    }
}
