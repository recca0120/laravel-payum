<?php

use Mockery as m;
use Recca0120\LaravelPayum\PayumBuilder;

class PayumBuilderTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_add_default_storages()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $path = __DIR__.'/';
        $payumBuilder = new PayumBuilder($filesystem, $app, [
            'path' => $path,
        ]);
        $payumTokenStorageInterface = m::mock('Payum\Core\Storage\StorageInterface');
        $payumPaymentStorageInterface = m::mock('Payum\Core\Storage\StorageInterface');
        $payumArrayObjectStorageInterface = m::mock('Payum\Core\Storage\StorageInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $filesystem
            ->shouldReceive('isDirectory')->with($path)->andReturn(false)
            ->shouldReceive('makeDirectory')->with($path, 0777, true);

        $app
            ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
                'path' => $path,
                'modelClass' => 'Payum\Core\Model\Token',
                'idProperty' => 'hash',
            ])->once()->andReturn($payumTokenStorageInterface)
            ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
                'path' => $path,
                'modelClass' => 'Payum\Core\Model\Payment',
                'idProperty' => 'number',
            ])->once()->andReturn($payumPaymentStorageInterface)
            ->shouldReceive('make')->with('Payum\Core\Storage\FilesystemStorage', [
                'path' => $path,
                'modelClass' => 'Payum\Core\Model\ArrayObject',
            ])->once()->andReturn($payumArrayObjectStorageInterface);

        $payumBuilder->addDefaultStorages();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame($payumTokenStorageInterface, 'tokenStorage', $payumBuilder);
        $this->assertAttributeSame([
            'Payum\Core\Model\Payment' => $payumPaymentStorageInterface,
            'Payum\Core\Model\ArrayObject' => $payumArrayObjectStorageInterface,
        ], 'storages', $payumBuilder);
    }

    public function test_add_eloquent_storages()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $payumBuilder = new PayumBuilder($filesystem, $app);
        $payumTokenStorageInterface = m::mock('Payum\Core\Storage\StorageInterface');
        $payumPaymentStorageInterface = m::mock('Payum\Core\Storage\StorageInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app->shouldReceive('make')->with('Recca0120\LaravelPayum\Storage\EloquentStorage', ['modelClass' => 'Recca0120\LaravelPayum\Model\Token'])->once()->andReturn($payumTokenStorageInterface)
            ->shouldReceive('make')->with('Recca0120\LaravelPayum\Storage\EloquentStorage', ['modelClass' => 'Recca0120\LaravelPayum\Model\Payment'])->once()->andReturn($payumPaymentStorageInterface);

        $payumBuilder->addEloquentStorages();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame($payumTokenStorageInterface, 'tokenStorage', $payumBuilder);
        $this->assertAttributeSame([
            'Recca0120\LaravelPayum\Model\Payment' => $payumPaymentStorageInterface,
        ], 'storages', $payumBuilder);
    }
}
