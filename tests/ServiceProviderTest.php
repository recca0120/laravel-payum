<?php

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Routing\Router;
use Mockery as m;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Model\GatewayConfig;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\Payment;
use Recca0120\LaravelPayum\PayumBuilder;
use Recca0120\LaravelPayum\ServiceProvider;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Recca0120\LaravelPayum\Storage\FilesystemStorage;

class ServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_register()
    {
        $payumBuilder = m::mock(PayumBuilder::class)
            ->shouldReceive('getPayum')->once()
            ->mock();

        $storage = m::mock(StorageInterface::class);

        $config = m::mock(ConfigContract::class)
            ->shouldReceive('get')->andReturn([])
            ->shouldReceive('set')
            ->mock();

        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class)
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->shouldReceive('singleton')->with(PayumBuilder::class, m::type(Closure::class))->once()->andReturnUsing(function ($className, $closure) {
                return $closure(m::self());
            })
            ->shouldReceive('singleton')->with(Payum::class, m::type(Closure::class))->once()->andReturnUsing(function ($className, $closure) {
                return $closure(m::self());
            })
            ->shouldReceive('singleton')->with(Payment::class, Payment::class)->once()
            ->shouldReceive('make')->with(FilesystemStorage::class, [
                'modelClass' => Token::class,
                'idProperty' => 'hash',
            ])->once()->andReturn($storage)
            ->shouldReceive('make')->with(FilesystemStorage::class, [
                'modelClass' => PayumPayment::class,
                'idProperty' => 'number',
            ])->once()->andReturn($storage)
            ->shouldReceive('make')->with(FilesystemStorage::class, [
                'modelClass' => ArrayObject::class,
            ])->once()->andReturn($storage)
            ->shouldReceive('make')->with(PayumBuilder::class)->andReturn($payumBuilder)
            ->mock();

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();
    }

    public function test_register_with_eloquent()
    {
        $payumBuilder = m::mock(PayumBuilder::class)
            ->shouldReceive('getPayum')->once()
            ->mock();

        $storage = m::mock(StorageInterface::class);

        $config = m::mock(ConfigContract::class)
            ->shouldReceive('get')->with('payum')->once()->andReturn([
                'storage' => [
                    'token'         => 'database',
                    'gatewayConfig' => 'database',
                ],
                'gatewayConfigs' => [
                    'customFactoryName' => [
                        'gatewayName' => 'customGatewayName',
                        'config'      => [],
                    ],
                ],
                'gatewayFactories' => [
                    'customFactoryName' => 'customFactoryClass',
                ],
            ])
            ->shouldReceive('get')->andReturn([])
            ->shouldReceive('set')
            ->mock();

        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class)
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->shouldReceive('singleton')->with(PayumBuilder::class, m::type(Closure::class))->once()->andReturnUsing(function ($className, $closure) {
                return $closure(m::self());
            })
            ->shouldReceive('singleton')->with(Payum::class, m::type(Closure::class))->once()->andReturnUsing(function ($className, $closure) {
                return $closure(m::self());
            })
            ->shouldReceive('singleton')->with(Payment::class, Payment::class)->once()
            ->shouldReceive('make')->with(EloquentStorage::class, [
                'modelClass' => EloquentToken::class,
            ])->once()->andReturn($storage)
            ->shouldReceive('make')->with(EloquentStorage::class, [
                'modelClass' => EloquentPayment::class,
            ])->once()->andReturn($storage)
            ->shouldReceive('make')->with(EloquentStorage::class, [
                'modelClass' => GatewayConfig::class,
            ])->once()->andReturn($storage)
            ->shouldReceive('make')->with(PayumBuilder::class)->andReturn($payumBuilder)
            ->mock();

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();
    }

    public function test_boot()
    {
        $config = m::mock(ConfigContract::class)
            ->shouldReceive('get')->with('payum.router')->andReturn([])
            ->mock();

        $viewFactory = m::mock(ViewFactory::class)
            ->shouldReceive('addNamespace')->with('payum', m::any())
            ->mock();

        $router = m::mock(Router::class)
            ->shouldReceive('group')->with(m::any(), m::type(Closure::class))
            ->mock();

        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('routesAreCached')->andReturn(false)
            ->mock();

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->boot($viewFactory, $router, $config);
    }
}

function config_path()
{
}

function base_path()
{
}
