<?php

namespace Recca0120\LaravelPayum;

use Payum\Core\Gateway;
use Payum\Core\Bridge\Spl\ArrayObject;
use Illuminate\Contracts\Foundation\Application;
use Payum\Core\CoreGatewayFactory as PayumCoreGatewayFactory;

class CoreGatewayFactory extends PayumCoreGatewayFactory
{
    /**
     * $app.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param array                                        $defaultConfig
     */
    public function __construct(Application $app, array $defaultConfig = [])
    {
        parent::__construct($defaultConfig);
        $this->app = $app;
    }

    /**
     * buildActions.
     *
     * @method buildActions
     *
     * @param \Payum\Core\Gateway                $gateway
     * @param \Payum\Core\Bridge\Spl\ArrayObject $config
     */
    protected function buildActions(Gateway $gateway, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.action') && false == is_object($config[$name])) {
                $config[$name] = $this->app[$config[$name]];
            }
        }

        parent::buildActions($gateway, $config);
    }

    /**
     * buildApis.
     *
     * @method buildApis
     *
     * @param \Payum\Core\Gateway                $gateway
     * @param \Payum\Core\Bridge\Spl\ArrayObject $config
     */
    protected function buildApis(Gateway $gateway, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.api') && false == is_object($config[$name])) {
                $config[$name] = $this->app[$config[$name]];
            }
        }

        parent::buildApis($gateway, $config);
    }

    /**
     * buildExtensions.
     *
     * @method buildExtensions
     *
     * @param \Payum\Core\Gateway                $gateway
     * @param \Payum\Core\Bridge\Spl\ArrayObject $config
     */
    protected function buildExtensions(Gateway $gateway, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.extension') && false == is_object($config[$name])) {
                $config[$name] = $this->app[$config[$name]];
            }
        }

        parent::buildExtensions($gateway, $config);
    }
}
