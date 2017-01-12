<?php

use Mockery as m;
use Recca0120\LaravelPayum\LaravelPayumServiceProvider;

class LaravelPayumServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_register()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $config = m::spy('Illuminate\Contracts\Config\Repository, ArrayAccess');

        $urlGenerator = m::spy('Illuminate\Contracts\Routing\UrlGenerator');
        $filesystem = m::spy('Illuminate\Filesystem\Filesystem');
        $payumBuilderManager = m::spy('Recca0120\LaravelPayum\PayumBuilderManager');
        $payumBuilder = m::spy('Payum\Core\PayumBuilder');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->shouldReceive('offsetGet')->with('url')->andReturn($urlGenerator)
            ->shouldReceive('offsetGet')->with('files')->andReturn($filesystem)
            ->shouldReceive('make')->with('Recca0120\LaravelPayum\PayumBuilderManager', m::any())->andReturn($payumBuilderManager)
            ->shouldReceive('make')->with('payum.builder')->andReturn($payumBuilder);

        $config
            ->shouldReceive('offsetGet')->andReturn([])
            ->shouldReceive('get')->andReturn([])
            ->shouldReceive('set');

        $payumBuilderManager
            ->shouldReceive('setTokenFactory')->andReturnSelf()
            ->shouldReceive('setCoreGatewayFactoryConfig')->andReturnSelf()
            ->shouldReceive('setStorage')->andReturnSelf();

        $serviceProvider = new LaravelPayumServiceProvider($app);
        $serviceProvider->register();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */
        $app->shouldHaveReceived('singleton')->with('payum.builder', m::on(function($closure) use ($app) {
            $closure($app);

            return true;
        }));

        $app->shouldHaveReceived('singleton')->with('Payum\Core\Payum', m::on(function($closure) use ($app) {
            $closure($app);

            return true;
        }));

        $app->shouldHaveReceived('singleton')->with('Recca0120\LaravelPayum\Service\PayumService', 'Recca0120\LaravelPayum\Service\PayumService');
    }

    public function test_boot()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $viewFactory = m::spy('Illuminate\Contracts\View\Factory');
        $router = m::spy('Illuminate\Routing\Router');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app->shouldReceive('routesAreCached')->andReturn(false);

        $serviceProvider = new LaravelPayumServiceProvider($app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $serviceProvider->boot($viewFactory, $router);
        $serviceProvider->provides();
    }

    public function test_running_in_console()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $viewFactory = m::spy('Illuminate\Contracts\View\Factory');
        $router = m::spy('Illuminate\Routing\Router');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app->shouldReceive('runningInConsole')->andReturn(true);

        $serviceProvider = new LaravelPayumServiceProvider($app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $serviceProvider->boot($viewFactory, $router);
        $serviceProvider->provides();
    }

    public function xboot()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $serviceProvider = new LaravelPayumServiceProvider($app);
        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $router = m::mock('Illuminate\Routing\Router');
        $config = m::mock('Illuminate\Contracts\Config\Repository, ArrayAccess');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $config
            ->shouldReceive('offsetGet')->with('payum')->andReturn([])
            ->shouldReceive('set')->andReturnSelf();

        $viewFactory->shouldReceive('addNamespace')->with('payum', m::any());

        $app
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->shouldReceive('runningInConsole')->andReturn(true)
            ->shouldReceive('configPath')->andReturn(__DIR__)
            ->shouldReceive('basePath')->andReturn(__DIR__)
            ->shouldReceive('routesAreCached')->andReturn(false);

        $router->shouldReceive('group');

        $serviceProvider->boot($viewFactory, $router);
        $serviceProvider->provides();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
    }
}

function storage_path()
{
    return '';
}
