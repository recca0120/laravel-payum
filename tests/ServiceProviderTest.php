<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
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
        $serviceProvider = m::mock(new ServiceProvider($app));
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $configData = require __DIR__.'/../config/payum.php';
        $configData['storage']['token'] = 'eloquent';
        $configData['gatewayConfigs'] = [
            'gatewayName' => [
                'factory' => 'factory',
                'username' => 'username',
                'password' => 'password',
            ],
            'gatewayName2' => [
                'factory' => 'stdClass',
                'username' => 'username',
                'password' => 'password',
            ],
        ];
        $configData['storage']['gatewayConfig'] = 'eloquent';
        $eloquentStorage = m::mock('Recca0120\LaravelPayum\Storage\EloquentStorage');

        // registerPayumBuilder
        $builder = m::mock('Recca0120\LaravelPayum\PayumBuilder');
        $tokenStorage = m::mock('Payum\Core\Storage\StorageInterface');
        $registry = m::mock('Payum\Core\Registry\StorageRegistryInterface');
        $gatewayFactory = m::mock('Payum\Core\GatewayFactoryInterface');
        $defaultConfig = new ArrayObject([
            'payum.template.obtain_credit_card' => 'foo.payum.template.obtain_credit_card',
        ]);

        // registerPayum
        $payum = m::mock('Payum\Core\Payum');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $config->shouldReceive('get')->with('payum')->once()->andReturn($configData)
            ->shouldReceive('get')->with('payum', [])->once()->andReturn($configData)
            ->shouldReceive('set')->once();

        $app->shouldReceive('offsetGet')->with('config')->twice()->andReturn($config);

        // registerPayumBuilder
        $app->shouldReceive('bind')->with('payum.converter.reply_to_http_response', 'Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')->once()
            ->shouldReceive('bind')->with('payum.action.get_http_request', 'Recca0120\LaravelPayum\Action\GetHttpRequestAction')->once()
            ->shouldReceive('bind')->with('payum.action.obtain_credit_card', 'Recca0120\LaravelPayum\Action\ObtainCreditCardAction')->once()
            ->shouldReceive('bind')->with('payum.action.render_template', 'Recca0120\LaravelPayum\Action\RenderTemplateAction')->once()
            ->shouldReceive('bind')->with('payum.extension.update_payment_status', 'Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension')->once()
            ->shouldReceive('singleton')->with('payum.builder', m::type('Closure'))->once()->andReturnUsing(function ($name, $closure) use ($app) {
                return $closure($app);
            })
            ->shouldReceive('offsetGet')->with('config')->once()->andReturn($config)
            ->shouldReceive('make')->with('Recca0120\LaravelPayum\PayumBuilder')->once()->andReturn($builder)
            ->shouldReceive('make')->with('Recca0120\LaravelPayum\Security\TokenFactory', [$tokenStorage, $registry])->once()->andReturn($builder)
            ->shouldReceive('make')->with('Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier', [$tokenStorage])->once()->andReturn($builder)
            ->shouldReceive('make')->with('Recca0120\LaravelPayum\CoreGatewayFactory', [$app, $defaultConfig])->once()->andReturn($builder);

        $builder
            ->shouldReceive('setTokenFactory')->once()->andReturnUsing(function ($closure) use ($tokenStorage, $registry) {
                return $closure($tokenStorage, $registry);
            })
            ->shouldReceive('setHttpRequestVerifier')->once()->andReturnUsing(function ($closure) use ($tokenStorage) {
                return $closure($tokenStorage);
            })
            ->shouldReceive('setCoreGatewayFactory')->once()->with(m::type('Closure'))->andReturnUsing(function ($closure) use ($defaultConfig) {
                return $closure($defaultConfig);
            })
            ->shouldReceive('setCoreGatewayFactoryConfig')->once()->andReturnSelf()
            ->shouldReceive('setGenericTokenFactoryPaths')->once()->andReturnSelf()
            // ->shouldReceive('addDefaultStorages')->once()->andReturnSelf()
            ->shouldReceive('addEloquentStorages')->once()->andReturnSelf();

        // gatewayConfigs
        $gatewayConfigs = array_get($configData, 'gatewayConfigs', []);
        foreach ($gatewayConfigs as $factoryName => $config) {
            if (class_exists($config['factory']) === true) {
                $builder->shouldReceive('addGatewayFactory')->with($factoryName, m::type('Closure'))->andReturnUsing(function ($name, $closure) use ($defaultConfig, $gatewayFactory) {
                    return $closure($defaultConfig, $gatewayFactory);
                });
                $app->shouldReceive('make')->with($config['factory'], m::any());
            }

            $config['factory'] = $factoryName;
            $builder->shouldReceive('addGateway')->with($factoryName, $config);
        }

        // storage gatewayConfig
        $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Storage\EloquentStorage', [
            'modelClass' => 'Recca0120\LaravelPayum\Model\GatewayConfig',
        ])->andReturn($eloquentStorage);

        $builder->shouldReceive('setGatewayConfigStorage')->andReturn($eloquentStorage);

        // registerPayum
        $builder->shouldReceive('getPayum')->once()->andReturn($payum);
        $app->shouldReceive('singleton')->with('Payum\Core\Payum', m::type('Closure'))->once()->andReturnUsing(function ($name, $closure) {
            return $closure(m::self());
        })
            ->shouldReceive('make')->with('payum.builder')->once()->andReturn($builder);

        $app->shouldReceive('singleton')->with('Recca0120\LaravelPayum\Service\Payum', 'Recca0120\LaravelPayum\Service\Payum');

        $serviceProvider->register();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
    }

    public function test_boot()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $serviceProvider = m::mock(new ServiceProvider($app))
            ->shouldAllowMockingProtectedMethods();
        $viewFactory = m::mock('Illuminate\Contracts\View\Factory');
        $router = m::mock('Illuminate\Routing\Router');
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $config->shouldReceive('get')->andReturn([])
            ->shouldReceive('set')->andReturnSelf();

        $viewFactory->shouldReceive('addNamespace')->with('payum', m::any());

        $app
            ->shouldReceive('offsetGet')->with('config')->andReturn($config)
            ->shouldReceive('configPath')->andReturn(__DIR__)
            ->shouldReceive('basePath')->andReturn(__DIR__)
            ->shouldReceive('routesAreCached')->andReturn(false);

        $router->shouldReceive('group');

        $serviceProvider->boot($viewFactory, $router);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
    }
}
