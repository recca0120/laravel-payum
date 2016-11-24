<?php

use Mockery as m;
use Recca0120\LaravelPayum\ServiceProvider;

class ServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function test_register()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $config = m::mock('Illuminate\Contracts\Config\Repository, ArrayAccess');
        $payumBuilderManager = m::mock('Recca0120\LaravelPayum\PayumBuilderManager');
        $payumBuilder = m::mock('Payum\Core\PayumBuilder');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $config
            ->shouldReceive('offsetGet')->with('payum')->once()->andReturn([])
            ->shouldReceive('get')->with('payum', [])->once()->andReturn([])
            ->shouldReceive('set')->once();

        $app
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->shouldReceive('bind')->with('payum.converter.reply_to_http_response', 'Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')->once()
            ->shouldReceive('bind')->with('payum.action.get_http_request', 'Recca0120\LaravelPayum\Action\GetHttpRequestAction')->once()
            ->shouldReceive('bind')->with('payum.action.obtain_credit_card', 'Recca0120\LaravelPayum\Action\ObtainCreditCardAction')->once()
            ->shouldReceive('bind')->with('payum.action.render_template', 'Recca0120\LaravelPayum\Action\RenderTemplateAction')->once()
            ->shouldReceive('bind')->with('payum.extension.update_payment_status', 'Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')->once()
            ->shouldReceive('singleton')->with('payum.builder', m::type('Closure'))->once()->andReturnUsing(function ($className, $closure) use ($app) {
                return $closure($app);
            })
            ->shouldReceive('make')->with('Recca0120\LaravelPayum\PayumBuilderManager', ['config' => []])->once()->andReturn($payumBuilderManager)
            ->shouldReceive('singleton')->with('Payum\Core\Payum', m::type('Closure'))->once()->andReturnUsing(function ($className, $closure) use ($app) {
                return $closure($app);
            })
            ->shouldReceive('singleton')->with('Recca0120\LaravelPayum\Service\PayumService', 'Recca0120\LaravelPayum\Service\PayumService')->once()
            ->shouldReceive('make')->with('payum.builder')->once()->andReturn($payumBuilder);

        $payumBuilderManager->shouldReceive('getBuilder')->once();
        $payumBuilder->shouldReceive('getPayum');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $serviceProvider = new ServiceProvider($app);
        $serviceProvider->register();
        $this->assertTrue(class_exists('\Recca0120\LaravelPayum\Service\Payum'));
    }

    public function test_boot()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $serviceProvider = new ServiceProvider($app);
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
