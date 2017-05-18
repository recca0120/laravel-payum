<?php

namespace Recca0120\LaravelPayum\Tests\Model;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Model\GatewayConfig;

class GatewayConfigEloquentTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testSetGatewayName()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setGatewayName($gatewayName = 'foo');
        $this->assertSame($gatewayName, $gatewayConfig->getGatewayName());
    }

    public function testSetFactoryName()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setFactoryName($factoryName = 'foo');
        $this->assertSame($factoryName, $gatewayConfig->getFactoryName());
    }

    public function testSetConfig()
    {
        $gatewayConfig = new GatewayConfig();
        $gatewayConfig->setConfig($config = ['foo' => 'bar']);
        $this->assertSame($config, $gatewayConfig->getConfig());
    }
}
