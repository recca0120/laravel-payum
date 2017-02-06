<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Storage\FilesystemStorage;
use Recca0120\LaravelPayum\PayumBuilderWrapper;
use Recca0120\LaravelPayum\Security\TokenFactory;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;

class PayumBuilderWrapperTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testSetTokenFactory()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setTokenFactory')->once()->with(m::on(function ($closure) {
            return $closure(
                $storageInterface = m::mock('Payum\Core\Storage\StorageInterface'),
                $storageRegistryInterface = m::mock('Payum\Core\Registry\StorageRegistryInterface')
            ) instanceof TokenFactory;
        }));
        $payumBuilderWrapper->setTokenFactory(
            $urlGenerator = m::mock('Illuminate\Contracts\Routing\UrlGenerator')
        );
    }

    public function testSetHttpRequestVerifer()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setHttpRequestVerifier')->once()->with(m::on(function ($closure) {
            return $closure(
                $storageInterface = m::mock('Payum\Core\Storage\StorageInterface')
            ) instanceof HttpRequestVerifier;
        }));
        $payumBuilderWrapper->setHttpRequestVerifier();
    }

    public function testSetCoreGatewayFactory()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setCoreGatewayFactory')->once()->with(m::on(function ($closure) {
            return $closure(
                $defaultConfig = []
            ) instanceof CoreGatewayFactory;
        }));
        $payumBuilderWrapper->setCoreGatewayFactory();
    }

    public function testSetCoreGatewayFactoryConfig()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = []
        );
        $payumBuilder->shouldReceive('setCoreGatewayFactoryConfig')->once()->with($coreGatewayFactoryConfig = ['foo' => 'bar']);
        $payumBuilderWrapper->setCoreGatewayFactoryConfig($coreGatewayFactoryConfig);
    }

    public function testSetGenericTokenFactoryPaths()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['route.as' => 'foo']
        );
        $payumBuilder->shouldReceive('setGenericTokenFactoryPaths')->once()->with([
            'authorize' => $config['route.as'].'authorize',
            'capture' => $config['route.as'].'capture',
            'notify' => $config['route.as'].'notify',
            'payout' => $config['route.as'].'payout',
            'refund' => $config['route.as'].'refund',
            'cancel' => $config['route.as'].'cancel',
            'sync' => $config['route.as'].'sync',
            'done' => $config['route.as'].'done',
        ]);
        $payumBuilderWrapper->setGenericTokenFactoryPaths();
    }

    public function testSetEloquentStorage()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['storage.token' => 'eloquent']
        );
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $payumBuilder->shouldReceive('setTokenStorage')->once()->with(m::on(function ($storage) {
            $this->assertAttributeSame('Recca0120\LaravelPayum\Model\Token', 'modelClass', $storage);

            return $storage instanceof EloquentStorage;
        }))->andReturnSelf();
        $payumBuilder->shouldReceive('addStorage')->once()->with('Recca0120\LaravelPayum\Model\Payment', m::on(function ($storage) {
            $this->assertAttributeSame('Recca0120\LaravelPayum\Model\Payment', 'modelClass', $storage);

            return $storage instanceof EloquentStorage;
        }));
        $payumBuilderWrapper->setStorage($filesystem);
    }

    public function testSetFilesystemStorage()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['storage.token' => 'filesystem', 'path' => 'foo']
        );
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $filesystem->shouldReceive('isDirectory')->with($config['path'])->andReturn(false);
        $filesystem->shouldReceive('makeDirectory')->with($config['path'], 0777, true)->andReturn(false);
        $payumBuilder->shouldReceive('setTokenStorage')->once()->with(m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\Token', 'modelClass', $storage);
            $this->assertAttributeSame('hash', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf();
        $payumBuilder->shouldReceive('addStorage')->once()->with('Payum\Core\Model\Payment', m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\Payment', 'modelClass', $storage);
            $this->assertAttributeSame('number', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf();
        $payumBuilder->shouldReceive('addStorage')->once()->with('Payum\Core\Model\ArrayObject', m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\ArrayObject', 'modelClass', $storage);
            $this->assertAttributeSame('payum_id', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf();
        $payumBuilder->shouldReceive('addStorage')->once()->with('Payum\Core\Model\Payout', m::on(function ($storage) use ($config) {
            $this->assertAttributeSame($config['path'], 'storageDir', $storage);
            $this->assertAttributeSame('Payum\Core\Model\Payout', 'modelClass', $storage);
            $this->assertAttributeSame('payum_id', 'idProperty', $storage);

            return $storage instanceof FilesystemStorage;
        }))->andReturnSelf();
        $payumBuilderWrapper->setStorage($filesystem);
    }

    public function testSetGatewayConfig()
    {
        $payumBuilderWrapper = new PayumBuilderWrapper(
            $payumBuilder = m::mock('Payum\Core\PayumBuilder'),
            $config = ['gatewayConfig' => [], 'storage.gatewayConfig' => 'eloquent']
        );
        $payumBuilder->shouldReceive('setGatewayConfigStorage')->once()->with(m::on(function ($storage) {
            $storage->setModelResolver(function () {
                $model = m::mock('stdClass');
                $model->shouldReceive('newQuery')->once()->andReturnSelf();
                $gatewayConfig = m::mock('Recca0120\LaravelPayum\Model\GatewayConfig');
                $gatewayConfig->shouldReceive('getGatewayName')->once()->andReturn('gatewayName');
                $gatewayConfig->shouldReceive('getFactoryName')->once()->andReturn('factoryName');
                $gatewayConfig->shouldReceive('getConfig')->once()->andReturn(['factory' => 'gatewayName', 'foo' => 'bar']);
                $model->shouldReceive('get->all')->andReturn([$gatewayConfig]);

                return $model;
            });

            return $storage instanceof EloquentStorage;
        }));
        $payumBuilder->shouldReceive('addGatewayFactory')->with('gatewayName', m::on(function ($closure) {
            $gatewayFactory = m::mock('Payum\Core\GatewayFactoryInterface');

            return $closure([], $gatewayFactory) instanceof gatewayName;
        }));
        $payumBuilder->shouldReceive('addGateway')->with('gatewayName', ['factory' => 'gatewayName', 'foo' => 'bar']);
        $payumBuilderWrapper->setGatewayConfig();
    }

    public function testGetBuilder()
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
