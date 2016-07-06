<?php

namespace Recca0120\LaravelPayum\Model;

use Illuminate\Database\Eloquent\Model;
use Payum\Core\Model\GatewayConfigInterface;

class GatewayConfig extends Model implements GatewayConfigInterface
{
    /**
     * @var string
     */
    protected $table = 'payum_gateway_configs';

    /**
     * getGatewayName.
     *
     * @method getGatewayName
     *
     * @return string
     */
    public function getGatewayName()
    {
        return $this->getAttribute('gatewayName');
    }

    /**
     * setGatewayName.
     *
     * @method setGatewayName
     *
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->setAttribute('gatewayName', $gatewayName);
    }

    /**
     * getFactoryName.
     *
     * @method getFactoryName
     *
     * @return string
     */
    public function getFactoryName()
    {
        return $this->getAttribute('factoryName');
    }

    /**
     * setFactoryName.
     *
     * @method setFactoryName
     *
     * @param string $name
     */
    public function setFactoryName($name)
    {
        $this->setAttribute('factoryName', $name);
    }

    /**
     * getConfig.
     *
     * @method getConfig
     *
     * @return array
     */
    public function getConfig()
    {
        return json_decode($this->getAttribute('config') ?: '{}', true);
    }

    /**
     * setConfig.
     *
     * @method setConfig
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->setAttribute('config', json_encode($config));
    }
}
