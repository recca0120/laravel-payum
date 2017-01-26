<?php

use Mockery as m;
use Recca0120\LaravelPayum\Model\GatewayConfig;

class GatewayConfigEloquentTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_gateway_name()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName($gatewayName = 'foo');
        $this->assertSame($gatewayName, $gatewayConfig->getGatewayName());
    }

    public function test_set_factory_name()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setFactoryName($factoryName = 'foo');
        $this->assertSame($factoryName, $gatewayConfig->getFactoryName());
    }

    public function test_set_config()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = ['foo' => 'bar']);
        $this->assertSame($config, $gatewayConfig->getConfig());
    }
}
