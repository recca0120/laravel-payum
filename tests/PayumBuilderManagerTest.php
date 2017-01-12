<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Recca0120\LaravelPayum\PayumBuilderManager;

class PayumBuilderManagerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_token_factory()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $urlGenerator = m::spy('Illuminate\Contracts\Routing\UrlGenerator');
        $storageInterface = m::spy('Payum\Core\Storage\StorageInterface');
        $storageRegistryInterface = m::spy('Payum\Core\Registry\StorageRegistryInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderManager($payumBuilder, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setTokenFactory($urlGenerator);

        $payumBuilder->shouldHaveReceived('setTokenFactory')->with(m::on(function($closure) use ($urlGenerator, $storageInterface, $storageRegistryInterface) {
            $this->assertInstanceOf('Recca0120\LaravelPayum\Security\TokenFactory', $closure($storageInterface, $storageRegistryInterface, $urlGenerator));

            return true;
        }))->once();
    }

    public function test_set_http_request_verifier()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $storageInterface = m::spy('Payum\Core\Storage\StorageInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderManager($payumBuilder, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setHttpRequestVerifier();

        $payumBuilder->shouldHaveReceived('setHttpRequestVerifier')->with(m::on(function($closure) use ($storageInterface) {
            $this->assertInstanceOf('Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier', $closure($storageInterface));

            return true;
        }))->once();
    }

    public function test_set_core_gateway_factory()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');
        $defaultConfig = [];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderManager($payumBuilder, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setCoreGatewayFactory();

        $payumBuilder->shouldHaveReceived('setCoreGatewayFactory')->with(m::on(function($closure) use ($defaultConfig) {
            $this->assertInstanceOf('Payum\Core\CoreGatewayFactory', $closure($defaultConfig));

            return true;
        }))->once();
    }

    public function test_set_core_gateway_factory_config()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');
        $config = [];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderManager($payumBuilder, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setCoreGatewayFactoryConfig($config);

        $payumBuilder->shouldHaveReceived('setCoreGatewayFactoryConfig')->with($config)->once();
    }

    public function test_set_generic_token_factory_paths()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');
        $config = [
            'route.as' => 'payment',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderManager($payumBuilder, $app, $config);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setGenericTokenFactoryPaths();

        $payumBuilder->shouldHaveReceived('setGenericTokenFactoryPaths')->with([
            'authorize' => $config['route.as'].'authorize',
            'capture' => $config['route.as'].'capture',
            'notify' => $config['route.as'].'notify',
            'payout' => $config['route.as'].'payout',
            'refund' => $config['route.as'].'refund',
            'cancel' => $config['route.as'].'cancel',
            'sync' => $config['route.as'].'sync',
            'done' => $config['route.as'].'done',
        ])->once();
    }

    public function test_set_eloquent_storage()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumBuilder->shouldReceive('setTokenStorage')->andReturnSelf();

        $manager = new PayumBuilderManager($payumBuilder, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setEloquentStorage();

        $payumBuilder->shouldHaveReceived('setTokenStorage')->once();
        $payumBuilder->shouldHaveReceived('addStorage')->once();
    }

    public function test_set_filesystem_storage()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $filesystem = m::spy('Illuminate\Filesystem\Filesystem');
        $config = [
            'path' => 'foo.path'
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $filesystem->shouldReceive('isDirectory')->andReturn(false);

        $payumBuilder
            ->shouldReceive('setTokenStorage')->andReturnSelf()
            ->shouldReceive('addStorage')->andReturnSelf();

        $manager = new PayumBuilderManager($payumBuilder, $app, $config);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setFilesystemStorage($filesystem);

        $filesystem->shouldHaveReceived('isDirectory')->with($config['path'])->once();
        $filesystem->shouldHaveReceived('makeDirectory')->with($config['path'], 0777, true)->once();
        $payumBuilder->shouldHaveReceived('setTokenStorage')->once();
        $payumBuilder->shouldHaveReceived('addStorage')->twice();
    }

    public function test_load_gateway_configs()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $gatewayConfig = m::spy('Recca0120\LaravelPayum\Model\GatewayConfig');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app->shouldReceive('make')->andReturn($gatewayConfig);

        $gatewayConfig
            ->shouldReceive('newQuery')->andReturnSelf()
            ->shouldReceive('get')->andReturnSelf()
            ->shouldReceive('all')->andReturn([$gatewayConfig])
            ->shouldReceive('getGatewayName')->once()->andReturn('fooGateway')
            ->shouldReceive('getFactoryName')->once()->andReturn('fooFactoryName')
            ->shouldReceive('getConfig')->once()->andReturn([
                'foo' => 'bar',
            ]);;

        $manager = new PayumBuilderManager($payumBuilder, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->loadGatewayConfigs();

        $app->shouldHaveReceived('make')->once();
        $gatewayConfig->shouldHaveReceived('newQuery')->once();
        $gatewayConfig->shouldHaveReceived('get')->once();
        $gatewayConfig->shouldHaveReceived('all')->once();
    }

    public function test_set_gateway_config()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $payumBuilder = m::spy('Payum\Core\PayumBuilder');
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $gatewayConfig = m::spy('Recca0120\LaravelPayum\Model\GatewayConfig');

        $config = [
            'gatewayConfigs' => [
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
            ],
            'storage.gatewayConfig' => 'eloquent'
        ];

        $gatewayFactory = m::spy('Payum\Core\GatewayFactoryInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app->shouldReceive('make')->andReturn($gatewayConfig);

        $gatewayConfig
            ->shouldReceive('newQuery')->andReturnSelf()
            ->shouldReceive('get')->andReturnSelf()
            ->shouldReceive('all')->andReturn([$gatewayConfig])
            ->shouldReceive('getGatewayName')->once()->andReturn('fooGateway')
            ->shouldReceive('getFactoryName')->once()->andReturn('fooFactoryName')
            ->shouldReceive('getConfig')->once()->andReturn([
                'foo' => 'bar',
            ]);;

        $manager = new PayumBuilderManager($payumBuilder, $app, $config);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setGatewayConfig();

        foreach ($config['gatewayConfigs'] as $gatewayName => $gatewayConfig) {
            $payumBuilder->shouldReceive('addGatewayFactory')->with($gatewayName, m::on(function($closure) use ($gatewayConfig, $gatewayFactory) {
                $closure($gatewayConfig, $gatewayFactory);

                return true;
            }));
        }
    }

    // public function test_create_token_factory()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [];
    //     $manager = new PayumBuilderManager($payumBuilder, $filesystem, $app, $config);
    //
    //     $storageInterface = m::mock('Payum\Core\Storage\StorageInterface');
    //     $storageRegistryInterface = m::mock('Payum\Core\Registry\StorageRegistryInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Security\TokenFactory', [
    //         $storageInterface,
    //         $storageRegistryInterface,
    //     ])->once();
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->createTokenFactory($storageInterface, $storageRegistryInterface);
    // }
    //
    // public function test_create_http_request_verifier()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [];
    //     $manager = new PayumBuilderManager($payumBuilder, $filesystem, $app, $config);
    //
    //     $storageInterface = m::mock('Payum\Core\Storage\StorageInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app->shouldReceive('make')->with('Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier', [
    //         $storageInterface,
    //     ])->once();
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->createHttpRequestVerifier($storageInterface);
    // }
    //
    // public function test_create_core_gateway_factory_config()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [];
    //     $manager = new PayumBuilderManager($payumBuilder, $filesystem, $app, $config);
    //
    //     $storageInterface = m::mock('Payum\Core\Storage\StorageInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app->shouldReceive('make')->with('Recca0120\LaravelPayum\CoreGatewayFactory', [
    //         'defaultConfig' => [],
    //     ])->once();
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->createCoreGatewayFactoryConfig([]);
    // }
    //
    // public function test_create_generic_token_factory_paths()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'route.as' => 'payum.',
    //     ];
    //     $manager = new PayumBuilderManager($payumBuilder, $filesystem, $app, $config);
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $this->assertSame([
    //         'authorize' => $config['route.as'].'authorize',
    //         'capture' => $config['route.as'].'capture',
    //         'notify' => $config['route.as'].'notify',
    //         'payout' => $config['route.as'].'payout',
    //         'refund' => $config['route.as'].'refund',
    //         'cancel' => $config['route.as'].'cancel',
    //         'sync' => $config['route.as'].'sync',
    //         'done' => $config['route.as'].'done',
    //     ], $manager->createGenericTokenFactoryPaths());
    // }
    //
    // public function test_create_eloquent_storage()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'route.as' => 'payum.',
    //     ];
    //     $manager = new PayumBuilderManager($payumBuilder, $filesystem, $app, $config);
    //
    //     $modelClass = 'fooClass';
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Storage\EloquentStorage', [
    //         $modelClass,
    //         $app,
    //     ])->once();
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //      $manager->createEloquentStorage($modelClass);
    // }
    //
    // public function test_create_filesystem_storage()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'path' => 'fooPath',
    //     ];
    //     $manager = new PayumBuilderManager($payumBuilder, $filesystem, $app, $config);
    //
    //     $modelClass = 'fooClass';
    //     $idProperty = 'fooId';
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
    //         $config['path'],
    //         $modelClass,
    //         $idProperty,
    //     ])->once();
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->createFilesystemStorage($modelClass, $idProperty);
    // }
    //
    // public function test_set_eloquent_storage()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'storage.token' => 'eloquent',
    //     ];
    //     $manager = m::mock(new PayumBuilderManager($payumBuilder, $filesystem, $app, $config))
    //         ->shouldAllowMockingProtectedMethods();
    //
    //     $storageInterface = m::mock('Payum\Core\Storage\StorageInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app
    //         ->shouldReceive('make')->with('Recca0120\LaravelPayum\Storage\EloquentStorage', [
    //             'Recca0120\LaravelPayum\Model\Token',
    //             $app,
    //         ])->once()->andReturn($storageInterface)
    //         ->shouldReceive('make')->with('Recca0120\LaravelPayum\Storage\EloquentStorage', [
    //             'Recca0120\LaravelPayum\Model\Payment',
    //             $app,
    //         ])->once()->andReturn($storageInterface);
    //
    //     $payumBuilder
    //         ->shouldReceive('setTokenStorage')->with($storageInterface)->once()->andReturnSelf()
    //         ->shouldReceive('addStorage')->with('Recca0120\LaravelPayum\Model\Payment', $storageInterface)->once()->andReturnSelf();
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->setStorage();
    // }
    //
    // public function test_set_filesystem_storage()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'path' => 'fooPath',
    //         'storage.token' => 'filesystem',
    //     ];
    //     $manager = m::mock(new PayumBuilderManager($payumBuilder, $filesystem, $app, $config))
    //         ->shouldAllowMockingProtectedMethods();
    //
    //     $storageInterface = m::mock('Payum\Core\Storage\StorageInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app
    //         ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
    //             $config['path'],
    //             'Payum\Core\Model\Token',
    //             'hash',
    //         ])->once()->andReturn($storageInterface)
    //         ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
    //             $config['path'],
    //             'Payum\Core\Model\Payment',
    //             'number',
    //         ])->once()->andReturn($storageInterface)
    //         ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
    //             $config['path'],
    //             'Payum\Core\Model\ArrayObject',
    //             'payum_id',
    //         ])->once()->andReturn($storageInterface);
    //
    //     $filesystem
    //         ->shouldReceive('isDirectory')->with($config['path'])->andReturn(false)
    //         ->shouldReceive('makeDirectory')->with($config['path'], 0777, true)->andReturn(true);
    //
    //     $payumBuilder
    //         ->shouldReceive('setTokenStorage')->with($storageInterface)->andReturnSelf()
    //         ->shouldReceive('addStorage')->with('Payum\Core\Model\Payment', $storageInterface)->andReturnSelf()
    //         ->shouldReceive('addStorage')->with('Payum\Core\Model\ArrayObject', $storageInterface)->andReturnSelf();
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->setStorage();
    // }
    //
    // public function test_set_gateway_config_storage()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'storage.gatewayConfig' => 'eloquent',
    //     ];
    //     $manager = m::mock(new PayumBuilderManager($payumBuilder, $filesystem, $app, $config))
    //         ->shouldAllowMockingProtectedMethods();
    //
    //     $storageInterface = m::mock('Payum\Core\Storage\StorageInterface');
    //
    //     $eloquentGatewayConfig = m::mock('GatewayConfigRecca0120\LaravelPayum\Model\GatewayConfig');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app
    //         ->shouldReceive('make')->with('Recca0120\LaravelPayum\Storage\EloquentStorage', [
    //             'Recca0120\LaravelPayum\Model\GatewayConfig',
    //             $app,
    //         ])->once()->andReturn($storageInterface);
    //
    //     $payumBuilder
    //         ->shouldReceive('setGatewayConfigStorage')->with($storageInterface)->once();
    //
    //     $eloquentGatewayConfig
    //         ->shouldReceive('getGatewayName')->once()->andReturn('fooGateway')
    //         ->shouldReceive('getFactoryName')->once()->andReturn('fooFactoryName')
    //         ->shouldReceive('getConfig')->once()->andReturn([
    //             'foo' => 'bar',
    //         ]);
    //
    //     $storageInterface
    //         ->shouldReceive('findBy')->with([])->andReturn([$eloquentGatewayConfig]);
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->setGatewayConfigStorage();
    //
    //     $this->assertSame([
    //         'fooGateway' => [
    //             'factory' => 'fooFactoryName',
    //             'foo' => 'bar',
    //         ],
    //     ], $manager->getGatewayConfigs());
    // }
    //
    // public function test_set_gateway_config()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'gatewayConfigs' => [
    //             'gatewayName' => [
    //                 'factory' => 'factory',
    //                 'username' => 'username',
    //                 'password' => 'password',
    //             ],
    //             'gatewayName2' => [
    //                 'factory' => 'stdClass',
    //                 'username' => 'username',
    //                 'password' => 'password',
    //             ],
    //         ],
    //     ];
    //     $manager = m::mock(new PayumBuilderManager($payumBuilder, $filesystem, $app, $config))
    //         ->shouldAllowMockingProtectedMethods();
    //     $defaultConfig = new ArrayObject([
    //         'payum.template.obtain_credit_card' => 'foo.payum.template.obtain_credit_card',
    //     ]);
    //     $gatewayFactory = m::mock('Payum\Core\GatewayFactoryInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $gatewayConfigs = $config['gatewayConfigs'];
    //     foreach ($gatewayConfigs as $gatewayName => $gatewayConfig) {
    //         if (class_exists($gatewayConfig['factory']) === true) {
    //             $payumBuilder->shouldReceive('addGatewayFactory')->with($gatewayName, m::type('Closure'))->andReturnUsing(function ($name, $closure) use ($defaultConfig, $gatewayFactory) {
    //                 return $closure($defaultConfig, $gatewayFactory);
    //             });
    //             $app->shouldReceive('make')->with($gatewayConfig['factory'], m::any());
    //         }
    //
    //         $gatewayConfig['factory'] = $gatewayName;
    //         $payumBuilder->shouldReceive('addGateway')->with($gatewayName, $gatewayConfig);
    //     }
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->setGatewayConfig();
    // }

    // public function test_get_builder()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Set
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::mock('Payum\Core\PayumBuilder');
    //     $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
    //     $app = m::mock('Illuminate\Contracts\Foundation\Application');
    //     $config = [
    //         'path' => 'fooPath',
    //     ];
    //     $manager = new PayumBuilderManager($payumBuilder, $filesystem, $app, $config);
    //
    //     $storageInterface = m::mock('Payum\Core\Storage\StorageInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Expectation
    //     |------------------------------------------------------------
    //     */
    //
    //     $app
    //         ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
    //             $config['path'],
    //             'Payum\Core\Model\Token',
    //             'hash',
    //         ])->once()->andReturn($storageInterface)
    //         ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
    //             $config['path'],
    //             'Payum\Core\Model\Payment',
    //             'number',
    //         ])->once()->andReturn($storageInterface)
    //         ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
    //             $config['path'],
    //             'Payum\Core\Model\ArrayObject',
    //             'payum_id',
    //         ])->once()->andReturn($storageInterface);
    //
    //     $filesystem
    //         ->shouldReceive('isDirectory')->with($config['path'])->andReturn(false)
    //         ->shouldReceive('makeDirectory')->with($config['path'], 0777, true)->andReturn(true);
    //
    //     $payumBuilder
    //         ->shouldReceive('setTokenFactory')->with([$manager, 'createTokenFactory'])->once()
    //         ->shouldReceive('setHttpRequestVerifier')->with([$manager, 'createHttpRequestVerifier'])->once()
    //         ->shouldReceive('setCoreGatewayFactory')->with([$manager, 'createCoreGatewayFactoryConfig'])->once()
    //         ->shouldReceive('setCoreGatewayFactoryConfig')->with([
    //             'payum.action.obtain_credit_card' => 'payum.action.obtain_credit_card',
    //             'payum.action.render_template' => 'payum.action.render_template',
    //             'payum.extension.update_payment_status' => 'payum.extension.update_payment_status',
    //         ])->once()
    //         ->shouldReceive('setGenericTokenFactoryPaths')
    //         ->shouldReceive('setTokenStorage')->with($storageInterface)->andReturnSelf()
    //         ->shouldReceive('addStorage')->with('Payum\Core\Model\Payment', $storageInterface)->andReturnSelf()
    //         ->shouldReceive('addStorage')->with('Payum\Core\Model\ArrayObject', $storageInterface)->andReturnSelf();
    //     /*
    //     |------------------------------------------------------------
    //     | Assertion
    //     |------------------------------------------------------------
    //     */
    //
    //     $this->assertSame($payumBuilder, $manager->getBuilder());
    // }
}
