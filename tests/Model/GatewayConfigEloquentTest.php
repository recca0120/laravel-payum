<?php

use Mockery as m;
use Recca0120\LaravelPayum\Model\GatewayConfig;

class GatewayConfigEloquentTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_attributes()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $gateConfig = m::mock(new GatewayConfig());

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedGatewayName = 'fooGatewayName';
        $exceptedFactoryName = 'fooFactoryName';
        $exceptedGetConfig = [
            'foo',
            'bar',
        ];
        $gateConfig->setGatewayName($exceptedGatewayName);
        $gateConfig->setFactoryName($exceptedFactoryName);
        $gateConfig->setConfig($exceptedGetConfig);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedGatewayName, $gateConfig->getGatewayName());
        $this->assertSame($exceptedFactoryName, $gateConfig->getFactoryName());
        $this->assertSame($exceptedGetConfig, $gateConfig->getConfig());
    }
}
