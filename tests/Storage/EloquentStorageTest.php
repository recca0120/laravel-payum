<?php

use Mockery as m;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class EloquentStorageTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $model = m::spy('Illuminate\Database\Eloquent\Model');
        $className = get_class($model);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('make')->with($className)->andReturn($model);

        $eloquentStorage = new EloquentStorage($className, $app);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $this->assertSame($model, $eloquentStorage->create());
        $app->shouldHaveReceived('make')->with($className)->once();
    }

    public function test_update()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $model = m::spy('Illuminate\Database\Eloquent\Model');
        $className = get_class($model);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $eloquentStorage = new EloquentStorage($className, $app);
        $eloquentStorage->update($model);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $model->shouldHaveReceived('save')->once();
    }

    public function test_delete()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $model = m::spy('Illuminate\Database\Eloquent\Model');
        $className = get_class($model);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $eloquentStorage = new EloquentStorage($className, $app);
        $eloquentStorage->delete($model);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $model->shouldHaveReceived('delete')->once();
    }

    public function test_identity()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $model = m::spy('Illuminate\Database\Eloquent\Model');
        $className = get_class($model);
        $key = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $model
            ->shouldReceive('getKey')->andReturn($key);

        $eloquentStorage = new EloquentStorage($className, $app);
        $eloquentStorage->identify($model);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $model->shouldHaveReceived('getKey')->once();
    }

    public function test_find()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $model = m::spy('Illuminate\Database\Eloquent\Model');
        $className = get_class($model);
        $id = uniqid();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('make')->with($className)->andReturn($model);

        $eloquentStorage = new EloquentStorage($className, $app);
        $eloquentStorage->find($id);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $app->shouldHaveReceived('make')->with($className)->once();
        $model->shouldHaveReceived('find')->with($id)->once();
    }

    public function test_find_by()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $app = m::spy('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $model = m::spy('Illuminate\Database\Eloquent\Model');
        $className = get_class($model);
        $criteria = [
            'foo' => 'bar',
            'fuzz' => 'buzz',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $app
            ->shouldReceive('make')->with($className)->andReturn($model);

        $model
            ->shouldReceive('newQuery')->andReturnSelf()
            ->shouldReceive('where')->andReturnSelf()
            ->shouldReceive('get->all')->andReturnSelf();

        $eloquentStorage = new EloquentStorage($className, $app);
        $eloquentStorage->findBy($criteria);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $app->shouldHaveReceived('make')->with($className)->once();
        $model->shouldHaveReceived('newQuery')->once();
        $model->shouldHaveReceived('where')->twice();
        $model->shouldHaveReceived('get')->once();
    }
}
