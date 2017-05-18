<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use Illuminate\Container\Container;
use Payum\Core\PayumBuilder;
use Payum\Core\GatewayFactory;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use Recca0120\LaravelPayum\PayumManager;
use Recca0120\LaravelPayum\LaravelPayumServiceProvider;

class LaravelPayumServiceProviderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $container = m::mock(new Container);
        $container->instance('path.config', __DIR__);
        $container->instance('path.storage', __DIR__);
        $container->shouldReceive('basePath')->andReturn(__DIR__);
        $container->shouldReceive('databasePath')->andReturn(__DIR__);
        Container::setInstance($container);
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testRegister()
    {
        if (! class_exists('Twig_Environment')) {
            $this->markTestSkipped('Twig is not available.');

            return;
        }

        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );

        $app->shouldReceive('offsetGet')->times(3)->with('config')->andReturn(
            $config = m::mock('ArrayAccess')
        );

        $config->shouldReceive('get')->andReturn([]);
        $config->shouldReceive('set');

        $app->shouldReceive('singleton')->once()->with('payum.builder', m::on(function ($closure) use ($app, $config) {
            $config->shouldReceive('offsetGet')->with('payum')->andReturn([
                'storage' => [
                    'token' => 'eloquent',
                    'path' => $path = 'foo',
                ],
                'drivers' => [
                    'test' => [
                        'factory' => TestGatewayFactory::class,
                    ],
                ],
            ]);

            $app->shouldReceive('make')->once()->with('Illuminate\Filesystem\Filesystem')->andReturn(
                $files = m::mock('Illuminate\Filesystem\Filesystem')
            );

            $files->shouldReceive('isDirectory')->once()->with($path)->andReturn(false);
            $files->shouldReceive('makeDirectory')->once()->with($path, 0777, true)->andReturn(false);

            $app->shouldReceive('make')->once()->with('Illuminate\Contracts\Routing\UrlGenerator')->andReturn(
                $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
            );

            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Action\GetHttpRequestAction')->andReturn(
                m::mock('Recca0120\LaravelPayum\Action\GetHttpRequestAction')
            );

            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Action\ObtainCreditCardAction')->andReturn(
                m::mock('Recca0120\LaravelPayum\Action\ObtainCreditCardAction')
            );

            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Action\RenderTemplateAction')->andReturn(
                m::mock('Recca0120\LaravelPayum\Action\RenderTemplateAction')
            );

            $app->shouldReceive('make')->once()->with('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')->andReturn(
                m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')
            );

            $app->shouldReceive('make')->once()->with('Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')->andReturn(
                m::mock('Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')
            );

            $builder = $closure($app);
            $payum = $builder->addDefaultStorages()->getPayum();
            $this->assertInstanceOf('Payum\Core\Payum', $payum);
            $this->assertInstanceOf('Payum\Core\Storage\FilesystemStorage', $payum->getStorage('Payum\Core\Model\Payment'));
            $this->assertInstanceOf('Payum\Core\Gateway', $payum->getGateway('test'));

            return $builder instanceof PayumBuilder;
        }));

        $app->shouldReceive('singleton')->once()->with('Payum\Core\Payum', m::on(function ($closure) use ($app) {
            $app->shouldReceive('offsetGet')->once()->with('payum.builder')->andReturn(
                $builder = m::mock('Payum\Core\PayumBuilder')
            );

            $builder->shouldReceive('getPayum')->once()->andReturn(
                $payum = m::mock('Payum\Core\Payum')
            );

            return $closure($app) === $payum;
        }));

        $app->shouldReceive('alias')->once()->with('Payum\Core\Payum', 'payum');

        $app->shouldReceive('singleton')->once()->with('Recca0120\LaravelPayum\PayumManager', m::on(function ($closure) use ($app) {
            return $closure($app) instanceof PayumManager;
        }));

        $serviceProvider->register();
    }

    public function testBoot()
    {
        $serviceProvider = new LaravelPayumServiceProvider(
            $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess')
        );
        $router = m::mock('Illuminate\Routing\Router');
        $app->shouldReceive('offsetGet')->with('config')->andReturn(['payum' => []]);
        $app->shouldReceive('routesAreCached')->andReturn(false);
        $router->shouldReceive('group')->once()->with([
            'prefix' => 'payum',
            'as' => 'payum.',
            'namespace' => 'Recca0120\LaravelPayum\Http\Controllers',
            'middleware' => ['web'],
        ], m::type('Closure'));
        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $viewFactory->shouldReceive('addNamespace')->once()->with('payum', m::on(function ($path) {
            return is_null(realpath($path)) === false;
        }));
        $app->shouldReceive('runningInConsole')->once()->andReturn(true);

        $this->assertNull($serviceProvider->boot($router, $viewFactory));
    }
}

class TestGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
    }
}
