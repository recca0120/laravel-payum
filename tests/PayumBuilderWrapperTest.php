<?php

use Mockery as m;
use Recca0120\LaravelPayum\PayumBuilderWrapper;

class PayumBuilderWrapperTest extends PHPUnit_Framework_TestCase
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
        $config = [];
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $urlGenerator = m::spy('Illuminate\Contracts\Routing\UrlGenerator');
        $storageInterface = m::spy('Payum\Core\Storage\StorageInterface');
        $storageRegistryInterface = m::spy('Payum\Core\Registry\StorageRegistryInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setTokenFactory($urlGenerator);

        $payumBuilder->shouldHaveReceived('setTokenFactory')->with(m::on(function ($closure) use ($urlGenerator, $storageInterface, $storageRegistryInterface) {
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
        $config = [];
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $storageInterface = m::spy('Payum\Core\Storage\StorageInterface');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setHttpRequestVerifier();

        $payumBuilder->shouldHaveReceived('setHttpRequestVerifier')->with(m::on(function ($closure) use ($storageInterface) {
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
        $config = [];
        $app = m::spy('Illuminate\Contracts\Foundation\Application');
        $defaultConfig = [];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setCoreGatewayFactory();

        $payumBuilder->shouldHaveReceived('setCoreGatewayFactory')->with(m::on(function ($closure) use ($defaultConfig) {
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
        $config = [];
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $coreGatewayFactoryConfig = [];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setCoreGatewayFactoryConfig($coreGatewayFactoryConfig);

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
        $config = [
            'route.as' => 'payment',
        ];
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);

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
        $config = [];
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $payumBuilder->shouldReceive('setTokenStorage')->andReturnSelf();

        $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);

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
        $config = [
            'path' => 'foo.path',
        ];
        $app = m::spy('Illuminate\Contracts\Foundation\Application');

        $filesystem = m::spy('Illuminate\Filesystem\Filesystem');

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $filesystem->shouldReceive('isDirectory')->andReturn(false);

        $payumBuilder
            ->shouldReceive('setTokenStorage')->andReturnSelf()
            ->shouldReceive('addStorage')->andReturnSelf();

        $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $manager->setFilesystemStorage($filesystem);

        $filesystem->shouldHaveReceived('isDirectory')->with($config['path'])->once();
        $filesystem->shouldHaveReceived('makeDirectory')->with($config['path'], 0777, true)->once();
        $payumBuilder->shouldHaveReceived('setTokenStorage')->once();
        $payumBuilder->shouldHaveReceived('addStorage')->times(3);
    }

    // public function test_load_gateway_configs()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Arrange
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::spy('Payum\Core\PayumBuilder');
    //     $config = [];
    //     $app = m::spy('Illuminate\Contracts\Foundation\Application');
    //
    //     $gatewayConfig = m::spy('Recca0120\LaravelPayum\Model\GatewayConfig');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Act
    //     |------------------------------------------------------------
    //     */
    //
    //     $app->shouldReceive('make')->andReturn($gatewayConfig);
    //
    //     $gatewayConfig
    //         ->shouldReceive('newQuery')->andReturnSelf()
    //         ->shouldReceive('get')->andReturnSelf()
    //         ->shouldReceive('all')->andReturn([$gatewayConfig])
    //         ->shouldReceive('getGatewayName')->once()->andReturn('fooGateway')
    //         ->shouldReceive('getFactoryName')->once()->andReturn('fooFactoryName')
    //         ->shouldReceive('getConfig')->once()->andReturn([
    //             'foo' => 'bar',
    //         ]);
    //
    //     $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assert
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->loadGatewayConfigs();
    //
    //     $app->shouldHaveReceived('make')->once();
    //     $gatewayConfig->shouldHaveReceived('newQuery')->once();
    //     $gatewayConfig->shouldHaveReceived('get')->once();
    //     $gatewayConfig->shouldHaveReceived('all')->once();
    // }

    // public function test_set_gateway_config()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Arrange
    //     |------------------------------------------------------------
    //     */
    //
    //     $payumBuilder = m::spy('Payum\Core\PayumBuilder');
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
    //         'storage.gatewayConfig' => 'eloquent',
    //     ];
    //     $app = m::spy('Illuminate\Contracts\Foundation\Application');
    //
    //     $gatewayConfig = m::spy('Recca0120\LaravelPayum\Model\GatewayConfig');
    //     $gatewayFactory = m::spy('Payum\Core\GatewayFactoryInterface');
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Act
    //     |------------------------------------------------------------
    //     */
    //
    //     $app
    //         ->shouldReceive('make')->andReturn($gatewayConfig);
    //
    //     $gatewayConfig
    //         ->shouldReceive('newQuery')->andReturnSelf()
    //         ->shouldReceive('get')->andReturnSelf()
    //         ->shouldReceive('all')->andReturn([$gatewayConfig])
    //         ->shouldReceive('getGatewayName')->once()->andReturn('fooGateway')
    //         ->shouldReceive('getFactoryName')->once()->andReturn('fooFactoryName')
    //         ->shouldReceive('getConfig')->once()->andReturn([
    //             'foo' => 'bar',
    //         ]);
    //
    //     $manager = new PayumBuilderWrapper($payumBuilder, $config, $app);
    //
    //     /*
    //     |------------------------------------------------------------
    //     | Assert
    //     |------------------------------------------------------------
    //     */
    //
    //     $manager->setGatewayConfig();
    //
    //     foreach ($config['gatewayConfigs'] as $gatewayName => $gatewayConfig) {
    //         $payumBuilder->shouldReceive('addGatewayFactory')->with($gatewayName, m::on(function ($closure) use ($gatewayName, $gatewayConfig, $gatewayFactory) {
    //             $this->assertInstanceOf($gatewayName, $closure($gatewayConfig, $gatewayFactory));
    //
    //             return true;
    //         }));
    //     }
    // }
}

class gatewayName
{
}

class gatewayName2
{
}
