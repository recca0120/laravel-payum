<?php

use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\View\Factory;
use Illuminate\Routing\Router;
use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Payum;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Action\GetHttpRequestAction;
use Recca0120\LaravelPayum\Action\ObtainCreditCardAction;
use Recca0120\LaravelPayum\Action\RenderTemplateAction;
use Recca0120\LaravelPayum\CoreGatewayFactory;
use Recca0120\LaravelPayum\Extension\UpdatePaymentStatusExtension;
use Recca0120\LaravelPayum\Model\GatewayConfig;
use Recca0120\LaravelPayum\PayumBuilder;
use Recca0120\LaravelPayum\Security\TokenFactory;
use Recca0120\LaravelPayum\Service\Payum as PayumService;
use Recca0120\LaravelPayum\ServiceProvider;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

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

        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class);
        $serviceProvider = m::mock(new ServiceProvider($app));
        $config = m::mock(ConfigContract::class);
        $configData = require __DIR__.'/../config/payum.php';
        $configData['storage']['token'] = 'database';
        $configData['gatewayConfigs'] = [
            'gatewayName' => [
                'factory' => 'factory',
                'username' => 'username',
                'password' => 'password',
            ],
            'gatewayName2' => [
                'factory' => stdClass::class,
                'username' => 'username',
                'password' => 'password',
            ],
        ];
        $configData['storage']['gatewayConfig'] = 'database';
        $eloquentStorage = m::mock(EloquentStorage::class);

        // registerPayumBuilder
        $builder = m::mock(PayumBuilder::class);
        $tokenStorage = m::mock(StorageInterface::class);
        $registry = m::mock(StorageRegistryInterface::class);
        $gatewayFactory = m::mock(GatewayFactoryInterface::class);
        $defaultConfig = new ArrayObject([
            'payum.template.obtain_credit_card' => 'foo.payum.template.obtain_credit_card',
        ]);

        // registerPayum
        $payum = m::mock(Payum::class);

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
        $app->shouldReceive('bind')->with('payum.converter.reply_to_http_response', ReplyToSymfonyResponseConverter::class)->once()
            ->shouldReceive('bind')->with('payum.action.get_http_request', GetHttpRequestAction::class)->once()
            ->shouldReceive('bind')->with('payum.action.obtain_credit_card', ObtainCreditCardAction::class)->once()
            ->shouldReceive('bind')->with('payum.action.render_template', RenderTemplateAction::class)->once()
            ->shouldReceive('bind')->with('payum.extension.update_payment_status', UpdatePaymentStatusExtension::class)->once()
            ->shouldReceive('singleton')->with('payum.builder', m::type(Closure::class))->once()->andReturnUsing(function ($name, $closure) use ($app) {
                return $closure($app);
            })
            ->shouldReceive('offsetGet')->with('config')->once()->andReturn($config)
            ->shouldReceive('make')->with(PayumBuilder::class)->once()->andReturn($builder)
            ->shouldReceive('make')->with(TokenFactory::class, [$tokenStorage, $registry])->once()->andReturn($builder)
            ->shouldReceive('make')->with(HttpRequestVerifier::class, [$tokenStorage])->once()->andReturn($builder)
            ->shouldReceive('make')->with(CoreGatewayFactory::class, [$app, $defaultConfig])->once()->andReturn($builder);

        $builder
            ->shouldReceive('setTokenFactory')->once()->andReturnUsing(function ($closure) use ($tokenStorage, $registry) {
                return $closure($tokenStorage, $registry);
            })
            ->shouldReceive('setHttpRequestVerifier')->once()->andReturnUsing(function ($closure) use ($tokenStorage) {
                return $closure($tokenStorage);
            })
            ->shouldReceive('setCoreGatewayFactory')->once()->with(m::type(Closure::class))->andReturnUsing(function ($closure) use ($defaultConfig) {
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
                $builder->shouldReceive('addGatewayFactory')->with($factoryName, m::type(Closure::class))->andReturnUsing(function ($name, $closure) use ($defaultConfig, $gatewayFactory) {
                    return $closure($defaultConfig, $gatewayFactory);
                });
                $app->shouldReceive('make')->with($config['factory'], m::any());
            }

            $config['factory'] = $factoryName;
            $builder->shouldReceive('addGateway')->with($factoryName, $config);
        }

        // storage gatewayConfig
        $app->shouldReceive('make')->with(EloquentStorage::class, [
            'modelClass' => GatewayConfig::class,
        ])->andReturn($eloquentStorage);

        $builder->shouldReceive('setGatewayConfigStorage')->andReturn($eloquentStorage);

        // registerPayum
        $builder->shouldReceive('getPayum')->once()->andReturn($payum);
        $app->shouldReceive('singleton')->with(Payum::class, m::type(Closure::class))->once()->andReturnUsing(function ($name, $closure) {
            return $closure(m::self());
        })
            ->shouldReceive('make')->with('payum.builder')->once()->andReturn($builder);

        $app->shouldReceive('singleton')->with(PayumService::class, PayumService::class);

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

        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class);
        $serviceProvider = m::mock(new ServiceProvider($app))
            ->shouldAllowMockingProtectedMethods();
        $viewFactory = m::mock(Factory::class);
        $router = m::mock(Router::class);
        $config = m::mock(ConfigContract::class);

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
