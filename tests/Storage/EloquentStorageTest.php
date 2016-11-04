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
        | Set
        |------------------------------------------------------------
        */
        $exceptedModelClass = 'fooModelClass';
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $eloquentStorage = new EloquentStorage($exceptedModelClass, $app);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $app->shouldReceive('make')->with($exceptedModelClass)->andReturn($exceptedModelClass);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedModelClass, $eloquentStorage->create());
    }

    public function test_do_update_model()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $exceptedModelClass = 'fooModelClass';
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $eloquentStorage = m::mock(new EloquentStorage($exceptedModelClass, $app))
            ->shouldAllowMockingProtectedMethods();
        $model = m::mock('Illuminate\Database\Eloquent\Model');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $model->shouldReceive('save');

        $eloquentStorage->doUpdateModel($model);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
    }

    public function test_do_delete_model()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $exceptedModelClass = 'fooModelClass';
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $eloquentStorage = m::mock(new EloquentStorage($exceptedModelClass, $app))
            ->shouldAllowMockingProtectedMethods();
        $model = m::mock('Illuminate\Database\Eloquent\Model');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $model->shouldReceive('delete');

        $eloquentStorage->doDeleteModel($model);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
    }

    public function test_do_get_identity()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $exceptedModelClass = 'fooModelClass';
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $eloquentStorage = m::mock(new EloquentStorage($exceptedModelClass, $app))
            ->shouldAllowMockingProtectedMethods();
        $model = m::mock('Illuminate\Database\Eloquent\Model');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedKey = 'fooKey';
        $model->shouldReceive('getKey')->andReturn($exceptedKey);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedKey, $eloquentStorage->doGetIdentity($model)->getId());
    }

    public function test_do_find()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $exceptedModelClass = 'fooModelClass';
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $eloquentStorage = m::mock(new EloquentStorage($exceptedModelClass, $app))
            ->shouldAllowMockingProtectedMethods();
        $model = m::mock('Illuminate\Database\Eloquent\Model');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedId = 'fooId';

        $app->shouldReceive('make')->with($exceptedModelClass)->andReturn($model);

        $model->shouldReceive('find')->with($exceptedId)->andReturn($exceptedModelClass);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedModelClass, $eloquentStorage->doFind($exceptedId));
    }

    public function test_find_by()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $exceptedModelClass = 'fooModelClass';
        $app = m::mock('Illuminate\Contracts\Foundation\Application, ArrayAccess');
        $eloquentStorage = new EloquentStorage($exceptedModelClass, $app);
        $model = m::mock('Illuminate\Database\Eloquent\Model');
        $newQuery = m::mock('stdClass');
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $exceptedCriteria = [
            'foo' => 'bar',
            'fuzz' => 'buzz',
        ];

        $app->shouldReceive('make')->with($exceptedModelClass)->andReturn($model);

        $model->shouldReceive('newQuery')->andReturn($newQuery);

        $newQuery->shouldReceive('where')->times(count($exceptedCriteria))->andReturnSelf()
            ->shouldReceive('get->all')->andReturn($exceptedCriteria);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($exceptedCriteria, $eloquentStorage->findBy($exceptedCriteria));
    }
}
