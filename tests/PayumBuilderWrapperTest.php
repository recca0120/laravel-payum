<?php

use Mockery as m;
use Recca0120\LaravelPayum\PayumBuilderWrapper;
use Recca0120\LaravelPayum\Security\TokenFactory;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\CoreGatewayFactory;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Payum\Core\Storage\FilesystemStorage;

class PayumBuilderWrapperTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_set_token_factory()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setTokenFactory')->with(m::on(function($closure) {
            return $closure(
                $storageInterface = m::mock('Payum\Core\Storage\StorageInterface'),
                $storageRegistryInterface = m::mock('Payum\Core\Registry\StorageRegistryInterface')
            ) instanceof TokenFactory;
        }))->once();
        $payumBuilderWrapper->setTokenFactory(
            $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
        );
    }

    public function test_set_http_request_verifer()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setHttpRequestVerifier')->with(m::on(function($closure) {
            return $closure(
                $storageInterface = m::mock('Payum\Core\Storage\StorageInterface')
            ) instanceof HttpRequestVerifier;
        }))->once();
        $payumBuilderWrapper->setHttpRequestVerifier();
    }

    public function test_set_core_gateway_factory()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setCoreGatewayFactory')->with(m::on(function($closure) {
            return $closure(
                $defaultConfig = []
            ) instanceof CoreGatewayFactory;
        }))->once();
        $payumBuilderWrapper->setCoreGatewayFactory();
    }

    public function test_set_core_gateway_factory_config()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setCoreGatewayFactoryConfig')->with($coreGatewayFactoryConfig = ['foo' => 'bar'])->once();
        $payumBuilderWrapper->setCoreGatewayFactoryConfig($coreGatewayFactoryConfig);
    }

    public function test_set_generic_token_factory_paths()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['route.as' => 'foo']
        );
        $payumBuilder->shouldReceive('setGenericTokenFactoryPaths')->with([
            'authorize' => $config['route.as'].'authorize',
            'capture' => $config['route.as'].'capture',
            'notify' => $config['route.as'].'notify',
            'payout' => $config['route.as'].'payout',
            'refund' => $config['route.as'].'refund',
            'cancel' => $config['route.as'].'cancel',
            'sync' => $config['route.as'].'sync',
            'done' => $config['route.as'].'done',
        ])->once();
        $payumBuilderWrapper->setGenericTokenFactoryPaths();
    }

    public function test_set_eloquent_storage()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['storage.token' => 'eloquent']
        );
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $payumBuilder->shouldReceive('setTokenStorage')->with(m::on(function ($storage) {
            $this->assertAttributeSame('Recca0120\LaravelPayum\Model\Token', 'modelClass', $storage);

            return $storage instanceof EloquentStorage;
        }))->andReturnSelf()->once();
        $payumBuilder->shouldReceive('addStorage')->with('Recca0120\LaravelPayum\Model\Payment', m::on(function ($storage) {
            $this->assertAttributeSame('Recca0120\LaravelPayum\Model\Payment', 'modelClass', $storage);

            return $storage instanceof EloquentStorage;
        }))->once();
        $payumBuilderWrapper->setStorage($filesystem);
    }

    public function test_set_filesystem_storage()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['storage.token' => 'filesystem' , 'path' => 'foo']
        );
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $filesystem->shouldReceive('isDirectory')->with($config['path'])->andReturn(false);
        $filesystem->shouldReceive('makeDirectory')->with($config['path'], 0777, true)->andReturn(false);
        $payumBuilder->shouldReceive('setTokenStorage')->with(m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\Token', 'modelClass', $storage);
            $this->assertAttributeSame('hash', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf()->once();
        $payumBuilder->shouldReceive('addStorage')->with('Payum\Core\Model\Payment', m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\Payment', 'modelClass', $storage);
            $this->assertAttributeSame('number', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf()->once();
        $payumBuilder->shouldReceive('addStorage')->with('Payum\Core\Model\ArrayObject', m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\ArrayObject', 'modelClass', $storage);
            $this->assertAttributeSame('payum_id', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf()->once();
        $payumBuilder->shouldReceive('addStorage')->with('Payum\Core\Model\Payout', m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\Payout', 'modelClass', $storage);
            $this->assertAttributeSame('payum_id', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf()->once();
        $payumBuilderWrapper->setStorage($filesystem);
    }

    public function test_set_gateway_config()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['gatewayConfig' => [], 'storage.gatewayConfig' => 'eloquent']
        );
        $payumBuilder->shouldReceive('setGatewayConfigStorage')->with(m::on(function ($storage) {
            $storage->setModelResolver(function () {
                $model = m::mock('stdClass');
                $model->shouldReceive('newQuery')->andReturnSelf()->once();
                $gatewayConfig = m::mock('Recca0120\LaravelPayum\Model\GatewayConfig');
                $gatewayConfig->shouldReceive('getGatewayName')->andReturn('gatewayName')->once();
                $gatewayConfig->shouldReceive('getFactoryName')->andReturn('factoryName')->once();
                $gatewayConfig->shouldReceive('getConfig')->andReturn(['factory' => 'gatewayName', 'foo' => 'bar'])->once();
                $model->shouldReceive('get->all')->andReturn([$gatewayConfig]);

                return $model;
            });

            return $storage instanceof EloquentStorage;
        }))->once();
        $payumBuilder->shouldReceive('addGatewayFactory')->with('gatewayName', m::on(function ($closure) {
            $gatewayFactory = m::mock('Payum\Core\GatewayFactoryInterface');

            return $closure([], $gatewayFactory) instanceof gatewayName;
        }));
        $payumBuilder->shouldReceive('addGateway')->with('gatewayName', ['factory' => 'gatewayName', 'foo' => 'bar']);
        $payumBuilderWrapper->setGatewayConfig();
    }

    public function test_get_builder()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['gatewayConfig' => [], 'storage.gatewayConfig' => 'eloquent']
        );
        $this->assertSame($payumBuilder, $payumBuilderWrapper->getBuilder());
    }
}

class gatewayName
{
}

class gatewayName2
{
}
