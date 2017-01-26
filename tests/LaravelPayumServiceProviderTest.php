<?php

use Mockery as m;
use Recca0120\LaravelPayum\LaravelPayumServiceProvider;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

class LaravelPayumServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_register_service_provider()
    {
        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $app->shouldReceive('offsetGet')->with('config')->andReturn($config = m::mock('stdClass'))->twice();
        $config->shouldReceive('get')->with('payum', [])->andReturn([]);
        $config->shouldReceive('set')->with('payum', m::type('array'))->andReturn([]);
        $app->shouldReceive('singleton')->with('Payum\Core\PayumBuilder', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->with('config')->andReturn(['payum' => ['path' => 'foo']])->once();
            $app->shouldReceive('offsetGet')->with('url')->andReturn(
                $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
            )->once();
            $files = m::mock('Illuminate\Filesystem\Filesystem');
            $files->shouldReceive('isDirectory')->andReturn(true);
            $app->shouldReceive('offsetGet')->with('files')->andReturn($files);
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Action\GetHttpRequestAction')->once();
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Action\ObtainCreditCardAction')->once();
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Action\RenderTemplateAction')->once();
            $app->shouldReceive('make')->with('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')->once();
            $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')->once();

            return $closure($app) instanceof PayumBuilder;
        }))->once();
        $app->shouldReceive('singleton')->with('Payum\Core\Payum', m::on(function ($closure) use ($app) {
            $app->shouldReceive('make')->with('Payum\Core\PayumBuilder')->andReturn($payumBuilder = m::mock('Payum\Core\PayumBuilder'));
            $payumBuilder->shouldReceive('getPayum')->andReturn($payum = m::mock('Payum\Core\Payum'))->once();

            return $closure($app) instanceof Payum;
        }))->once();
        $app->shouldReceive('singleton')->with('Recca0120\LaravelPayum\Service\PayumService', 'Recca0120\LaravelPayum\Service\PayumService')->once();
        $serviceProvider->register();
    }

    public function test_boot_service_provider()
    {
        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $router = m::mock('Illuminate\Routing\Router');
        $app->shouldReceive('offsetGet')->with('config')->andReturn(['payum' => []]);
        $app->shouldReceive('routesAreCached')->andReturn(false);
        $router->shouldReceive('group')->with([
            'prefix' => 'payment',
            'as' => 'payment.',
            'namespace' => 'Recca0120\LaravelPayum\Http\Controllers',
            'middleware' => ['web'],
        ], m::type('Closure'))->once();
        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $viewFactory->shouldReceive('addNamespace')->with('payum', m::on(function($path) {
            return is_null(realpath($path)) === false;
        }))->once();
        $app->shouldReceive('runningInConsole')->andReturn(true)->once();
        $app->shouldReceive('configPath');
        $app->shouldReceive('basePath');
        $app->shouldReceive('databasePath');
        $serviceProvider->boot($router, $viewFactory);
        $this->assertSame([
            'Payum\Core\PayumBuilder',
            'Payum\Core\Payum',
            'Recca0120\LaravelPayum\Service\PayumService',
        ], $serviceProvider->provides());
    }
}

function storage_path()
{
    return '';
}
