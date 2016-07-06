<?php

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Filesystem\Filesystem;
use Mockery as m;
use Recca0120\LaravelPayum\Storage\FilesystemStorage;

class FilesystemStorageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_storage()
    {
        $app = m::mock(ApplicationContract::class)
            ->shouldReceive('storagePath')->once()->andReturn(__DIR__)
            ->mock();

        $filesystem = m::mock(Filesystem::class)
            ->shouldReceive('isDirectory')->with(__DIR__.'/payum/')->once()->andReturn(false)
            ->shouldReceive('makeDirectory')->with(__DIR__.'/payum/', 0777, true)->once()->andReturn(false)
            ->mock();
        $storage = new FilesystemStorage($app, $filesystem, stdClass::class, 'test');
    }
}
