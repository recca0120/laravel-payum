<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Mockery as m;
use Recca0120\LaravelPayum\Model\GatewayConfig as EloquentGatewayConfig;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class GatewayConfigTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_update()
    {
        $gatewayName = uniqid();
        $factoryName = uniqid();
        $config = [uniqid()];

        $gatewayConfigClass = EloquentGatewayConfig::class;
        $gatewayConfig = m::mock(new $gatewayConfigClass())
            ->shouldReceive('save')->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($gatewayConfigClass)->once()->andReturn($gatewayConfig)
            ->mock();
        $storage = new EloquentStorage($app, $gatewayConfigClass);
        $gatewayConfig = $storage->create();

        $gatewayConfig->setGatewayName($gatewayName);
        $gatewayConfig->setFactoryName($factoryName);
        $gatewayConfig->setConfig($config);

        $this->assertSame($gatewayConfig->getGatewayName(), $gatewayName);
        $this->assertSame($gatewayConfig->getFactoryName(), $factoryName);
        $this->assertSame($gatewayConfig->getConfig(), $config);

        $storage->update($gatewayConfig);
    }

    public function test_delete()
    {
        $gatewayConfigClass = EloquentGatewayConfig::class;

        $gatewayConfig = m::mock(new $gatewayConfigClass())
            ->shouldReceive('delete')->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($gatewayConfigClass)->once()->andReturn($gatewayConfig)
            ->mock();

        $storage = new EloquentStorage($app, $gatewayConfigClass);
        $gatewayConfig = $storage->create();

        $storage->delete($gatewayConfig);
    }

    public function test_find()
    {
        $gatewayConfigClass = EloquentGatewayConfig::class;

        $gatewayConfig = m::mock(new $gatewayConfigClass())
            ->shouldReceive('find')->with(1)->once()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($gatewayConfigClass)->once()->andReturn($gatewayConfig)
            ->mock();

        $storage = new EloquentStorage($app, $gatewayConfigClass);
        $gatewayConfig = $storage->find(1);
    }

    public function test_identify()
    {
        $hash = uniqid();

        $gatewayConfigClass = EloquentGatewayConfig::class;
        $gatewayConfig = m::mock(new $gatewayConfigClass())
            ->shouldReceive('getKey')->andReturn($hash)
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($gatewayConfigClass)->once()->andReturn($gatewayConfig)
            ->mock();
        $storage = new EloquentStorage($app, $gatewayConfigClass);
        $gatewayConfig = $storage->create();

        $this->assertSame($storage->identify($gatewayConfig)->getId(), $hash);
    }

    public function test_find_by()
    {
        $gatewayName = uniqid();
        $factoryName = uniqid();

        $builder = m::mock(Builder::class)
            ->shouldReceive('where')->with('gatewayName', '=', $gatewayName)->once()->andReturnSelf()
            ->shouldReceive('where')->with('factoryName', '=', $factoryName)->once()->andReturnSelf()
            ->shouldReceive('get')->andReturn(new Collection())
            ->mock();

        $tokenClass = EloquentGatewayConfig::class;
        $token = m::mock(new $tokenClass())
            ->shouldReceive('newQuery')->once()->andReturn($builder)
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with($tokenClass)->once()->andReturn($token)
            ->mock();

        $storage = new EloquentStorage($app, $tokenClass);
        $storage->findBy([
            'gatewayName' => $gatewayName,
            'factoryName' => $factoryName,
        ]);
    }
}
