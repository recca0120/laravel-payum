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
            ->shouldReceive('singleton')->with('Recca0120\LaravelPayum\PayumBuilderManager', m::type('Closure'))->andReturnUsing(function ($className, $closure) use ($app) {
                return $closure($app);
            })
            ->shouldReceive('offsetGet')->with('url')->andReturn($urlGenerator)
            ->shouldReceive('offsetGet')->with('files')->andReturn($filesystem)
            ->shouldReceive('singleton')->with('Payum\Core\PayumBuilder', m::type('Closure'))->andReturnUsing(function ($className, $closure) use ($app) {
                return $closure($app);
            })
            ->shouldReceive('make')->with('Recca0120\LaravelPayum\PayumBuilderManager')->andReturn($payumBuilderManager)
            ->shouldReceive('singleton')->with('Payum\Core\Payum', m::type('Closure'))->andReturnUsing(function ($className, $closure) use ($app) {
                return $closure($app);
            })
            ->shouldReceive('make')->with('Payum\Core\PayumBuilder')->andReturn($payumBuilder);

        $config
            ->shouldReceive('offsetGet')->andReturn([])
            ->shouldReceive('get')->andReturn([])
            ->shouldReceive('set');

        $payumBuilderManager
            ->shouldReceive('getBuilder');

        $serviceProvider = new LaravelPayumServiceProvider($app);
        $serviceProvider->register();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $app->shouldHaveReceived('singleton')->with('Recca0120\LaravelPayum\PayumBuilderManager', m::type('Closure'))->once();
        $app->shouldHaveReceived('offsetGet')->with('url')->once();
        $app->shouldHaveReceived('make')->with('Recca0120\LaravelPayum\Action\GetHttpRequestAction')->once();
        $app->shouldHaveReceived('make')->with('Recca0120\LaravelPayum\Action\ObtainCreditCardAction')->once();
        $app->shouldHaveReceived('make')->with('Recca0120\LaravelPayum\Action\RenderTemplateAction')->once();
        $app->shouldHaveReceived('make')->with('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')->once();
        $app->shouldHaveReceived('make')->with('Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')->once();
        $app->shouldHaveReceived('offsetGet')->with('files')->once();
        $app->shouldHaveReceived('singleton')->with('Payum\Core\PayumBuilder', m::type('Closure'))->once();
        $app->shouldHaveReceived('make')->with('Recca0120\LaravelPayum\PayumBuilderManager')->once();
        $payumBuilderManager->shouldHaveReceived('getBuilder')->once();
        $app->shouldHaveReceived('singleton')->with('Payum\Core\Payum', m::type('Closure'))->once();
        $app->shouldHaveReceived('make')->with('Payum\Core\PayumBuilder')->once();
        $payumBuilder->shouldHaveReceived('getPayum')->once();
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

        $app
            ->shouldReceive('routesAreCached')->andReturn(false)
            ->shouldReceive('runningInConsole')->andReturn(true);

        $serviceProvider = new LaravelPayumServiceProvider($app);
        $serviceProvider->boot($viewFactory, $router);
        $serviceProvider->provides();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $app->shouldHaveReceived('routesAreCached')->once();
        $router->shouldHaveReceived('group')->once();
        $viewFactory->shouldHaveReceived('addNamespace')->once();
        $app->shouldHaveReceived('runningInConsole')->once();
        $app->shouldHaveReceived('configPath')->once();
        $app->shouldHaveReceived('basePath')->once();
        $app->shouldHaveReceived('databasePath')->once();
    }
}

function storage_path()
{
    return '';
}
