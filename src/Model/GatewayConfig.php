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
     * @return string
     */
    public function getGatewayName()
    {
        return $this->getAttribute('gateway_name');
    }

    /**
     * setGatewayName.
     *
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->setAttribute('gateway_name', $gatewayName);
    }

    /**
     * getFactoryName.
     *
     * @return string
     */
    public function getFactoryName()
    {
        return $this->getAttribute('factory_name');
    }

    /**
     * setFactoryName.
     *
     * @param string $name
     */
    public function setFactoryName($name)
    {
        $this->setAttribute('factory_name', $name);
    }

    /**
     * getConfig.
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
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->setAttribute('config', json_encode($config));
    }
}
