<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Mockery as m;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Model\Token as PayumToken;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Recca0120\LaravelPayum\Model\Token as EloquentToken;
use Recca0120\LaravelPayum\PayumBuilder;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Recca0120\LaravelPayum\Storage\FilesystemStorage;

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

        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class);
        $payumBuilder = new PayumBuilder($app);
        $payumTokenStorageInterface = m::mock(StorageInterface::class);
        $payumPaymentStorageInterface = m::mock(StorageInterface::class);
        $payumArrayObjectStorageInterface = m::mock(StorageInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app->shouldReceive('make')->with(FilesystemStorage::class, ['modelClass' => PayumToken::class, 'idProperty' => 'hash'])->once()->andReturn($payumTokenStorageInterface)
            ->shouldReceive('make')->with(FilesystemStorage::class, ['modelClass' => PayumPayment::class, 'idProperty' => 'number'])->once()->andReturn($payumPaymentStorageInterface)
            ->shouldReceive('make')->with(FilesystemStorage::class, ['modelClass' => ArrayObject::class])->once()->andReturn($payumArrayObjectStorageInterface);

        $payumBuilder->addDefaultStorages();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame($payumTokenStorageInterface, 'tokenStorage', $payumBuilder);
        $this->assertAttributeSame([
            PayumPayment::class => $payumPaymentStorageInterface,
            ArrayObject::class => $payumArrayObjectStorageInterface,
        ], 'storages', $payumBuilder);
    }

    public function test_add_eloquent_storages()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $app = m::mock(ApplicationContract::class.','.ArrayAccess::class);
        $payumBuilder = new PayumBuilder($app);
        $payumTokenStorageInterface = m::mock(StorageInterface::class);
        $payumPaymentStorageInterface = m::mock(StorageInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app->shouldReceive('make')->with(EloquentStorage::class, ['modelClass' => EloquentToken::class])->once()->andReturn($payumTokenStorageInterface)
            ->shouldReceive('make')->with(EloquentStorage::class, ['modelClass' => EloquentPayment::class])->once()->andReturn($payumPaymentStorageInterface);

        $payumBuilder->addEloquentStorages();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame($payumTokenStorageInterface, 'tokenStorage', $payumBuilder);
        $this->assertAttributeSame([
            EloquentPayment::class => $payumPaymentStorageInterface,
        ], 'storages', $payumBuilder);
    }
}
