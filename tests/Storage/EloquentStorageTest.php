<?php

namespace Recca0120\LaravelPayum\Tests\Storage;

use Mockery as m;
use Recca0120\LaravelPayum\Storage\EloquentStorage;
use Payum\Core\Model\Identity;

class EloquentStorageTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create_model()
    {
        $storage = new EloquentStorage('stdClass');
        $model = $storage->create();

        $this->assertTrue(is_object($model));
        $this->assertInstanceOf('stdClass', $model);
    }

    public function test_update_model()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('save')->once();
        $storage->setModelResolver(function() use ($model) {
            return $model;
        });
        $storage->update($model);
    }

    public function test_find_id_when_id_is_string()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('find')->with($id = uniqid())->once();
        $storage->setModelResolver(function() use ($model) {
            return $model;
        });
        $storage->find($id);
    }

    public function test_find_id_when_id_is_identity()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $identity = m::mock('Payum\Core\Storage\IdentityInterface');
        $identity->shouldReceive('getClass')->andReturn(get_parent_class($model))->once();
        $identity->shouldReceive('getId')->andReturn($id = uniqid());
        $model->shouldReceive('find')->with($id)->once();
        $storage->setModelResolver(function() use ($model) {
            return $model;
        });
        $storage->find($identity);
    }

    public function test_delete_model()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('delete')->once();
        $storage->setModelResolver(function() use ($model) {
            return $model;
        });
        $storage->delete($model);
    }

    public function test_identify_model()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('getKey')->andReturn($key = uniqid());
        $identify = $storage->identify($model);
        $this->assertSame($key, $identify->getId());
    }

    public function test_find_by_criteria() {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('newQuery')->andReturn($builder = m::mock('Illuminate\Database\Eloquent\Builder'))->once();
        $builder->shouldReceive('where')->with('foo', '=', 'bar')->andReturnSelf()->once();
        $builder->shouldReceive('get->all')->andReturn($result = ['foo', 'bar'])->once();
        $storage->setModelResolver(function() use ($model) {
            return $model;
        });
        $this->assertSame($result, $storage->findBy($criteria = ['foo' => 'bar']));
    }
}
