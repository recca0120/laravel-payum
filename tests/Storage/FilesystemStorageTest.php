<?php

use Mockery as m;
use Recca0120\LaravelPayum\Storage\FilesystemStorage;

class FilesystemStorageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_construct()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $exceptedModelClass = 'fooModelClass';
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $filesystem = m::mock('Illuminate\Filesystem\Filesystem');
        $exceptedPath = 'fooStoragePath';
        $config = [
            'payum.path' => $exceptedPath
        ];

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app->shouldReceive('offsetGet')->with('config')->andReturn($config);

        $filesystem->shouldReceive('isDirectory')->with($exceptedPath)->andReturn(false)
            ->shouldReceive('makeDirectory')->with($exceptedPath, 0777, true);

        $filesystemStorage = new FilesystemStorage($app, $filesystem, $exceptedModelClass);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertAttributeSame($exceptedPath, 'storageDir', $filesystemStorage);
    }
}
