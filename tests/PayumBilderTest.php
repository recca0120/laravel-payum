<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Mockery as m;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Token;
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

    public function test_default_storages()
    {
        $storageInterface = m::mock(StorageInterface::class);
        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with(FilesystemStorage::class, [
                'modelClass' => Token::class,
                'idProperty' => 'hash',
            ])->once()->andReturn($storageInterface)
            ->shouldReceive('make')->with(FilesystemStorage::class, [
                'modelClass' => Payment::class,
                'idProperty' => 'number',
            ])->once()->andReturn($storageInterface)
            ->shouldReceive('make')->with(FilesystemStorage::class, [
                'modelClass' => ArrayObject::class,
            ])->once()->andReturn($storageInterface)
            ->mock();

        $payumBuilder = (new PayumBuilder($app))
            ->addDefaultStorages();
    }

    public function test_eloquent_storate()
    {
        $storageInterface = m::mock(StorageInterface::class);
        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('make')->with(EloquentStorage::class, [
                'modelClass' => EloquentToken::class,
            ])->once()->andReturn($storageInterface)
            ->shouldReceive('make')->with(EloquentStorage::class, [
                'modelClass' => EloquentPayment::class,
            ])->once()->andReturn($storageInterface)
            ->mock();

        $payumBuilder = (new PayumBuilder($app))
            ->addEloquentStorages();
    }
}
