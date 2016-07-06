<?php

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Routing\Router;
use Mockery as m;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Model\GatewayConfig;
use Recca0120\LaravelPayum\PayumBuilder;
use Recca0120\LaravelPayum\ServiceProvider;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class ServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_register()
    {
        $tokenStorage = m::mock(StorageInterface::class);

        $registry = m::mock(StorageRegistryInterface::class);

        $config = m::mock(ConfigContract::class)
            ->shouldReceive('get')->andReturn([])
            ->shouldReceive('set')
            ->mock();

        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class)
            ->shouldReceive('singleton')->with(PayumBuilder::class, m::type(Closure::class))->andReturnUsing(function ($className, $closure) {
                $closure(m::self());
            })
            ->shouldReceive('singleton')->with(Payum::class, m::type(Closure::class))
            ->shouldReceive('make')->with(PayumBuilder::class)
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->mock();

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();
        $serviceProvider->provides();
    }

    public function test_boot()
    {
        $config = m::mock(ConfigContract::class)
            ->shouldReceive('get')->andReturn([])
            ->shouldReceive('set')
            ->mock();

        $viewFactory = m::mock(ViewFactory::class)
            ->shouldReceive('addNamespace')->with('payum', m::any())
            ->mock();

        $router = m::mock(Router::class)
            ->shouldReceive('group')->with(m::any(), m::type(Closure::class))
            ->mock();

        $payumBuilder = m::mock(PayumBuilder::class)
            ->shouldReceive('addDefaultStorages')->once()->andReturnSelf()
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('routesAreCached')->andReturn(false)
            ->mock();

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->boot($payumBuilder, $viewFactory, $router, $config);
    }

    public function test_boot_with_eloquent()
    {
        $gatewayFactoryInterface = m::mock(GatewayFactoryInterface::class);

        $config = m::mock(ConfigContract::class)
            ->shouldReceive('get')->with('payum.storage.token')->andReturn('database')
            ->shouldReceive('get')->with('payum.storage.gatewayConfig')->andReturn('database')
            ->shouldReceive('get')->with('payum.gatewayConfigs')->andReturn([
                'customFactoryName' => [
                    'gatewayName' => 'customGatewayName',
                    'config'      => [],
                ],
            ])
            ->shouldReceive('get')->with('payum.gatewayFactories')->andReturn([
                'customFactoryName' => 'customFactoryClass',
            ])
            ->shouldReceive('get')
            ->shouldReceive('set')
            ->mock();

        $viewFactory = m::mock(ViewFactory::class)
            ->shouldReceive('addNamespace')->with('payum', m::any())
            ->mock();

        $router = m::mock(Router::class)
            ->shouldReceive('group')->with(m::any(), m::type(Closure::class))
            ->mock();

        $payumBuilder = m::mock(PayumBuilder::class)
            ->shouldReceive('addEloquentStorages')->once()->andReturnSelf()
            ->shouldReceive('setGatewayConfigStorage')->once()->andReturnSelf()
            ->shouldReceive('addGatewayFactory')->with('customFactoryName', m::type(Closure::class))->once()->andReturnUsing(function ($gatewayName, $closure) use ($gatewayFactoryInterface) {
                $closure([], $gatewayFactoryInterface);
            })
            ->shouldReceive('addGateway')
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('routesAreCached')->andReturn(false)
            ->shouldReceive('make')->with(EloquentStorage::class, [
                'modelClass' => GatewayConfig::class,
            ])
            ->shouldReceive('make')->with('customFactoryClass', m::any())
            ->mock();

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->boot($payumBuilder, $viewFactory, $router, $config);
    }
}

function config_path()
{
}

function base_path()
{
}
