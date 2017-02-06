<?php

namespace Recca0120\LaravelPayum\Tests\Storage;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Storage\EloquentStorage;

class EloquentStorageTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCreateModel()
    {
        $storage = new EloquentStorage('stdClass');
        $model = $storage->create();

        $this->assertTrue(is_object($model));
        $this->assertInstanceOf('stdClass', $model);
    }

    public function testUpdateModel()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('save')->once();
        $storage->setModelResolver(function () use ($model) {
            return $model;
        });
        $storage->update($model);
    }

    public function testFindIdWhenIdIsString()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('find')->once()->with($id = uniqid());
        $storage->setModelResolver(function () use ($model) {
            return $model;
        });
        $storage->find($id);
    }

    public function testFindIdWhenIdIsIdentity()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $identity = m::mock('Payum\Core\Storage\IdentityInterface');
        $identity->shouldReceive('getClass')->once()->andReturn(get_parent_class($model));
        $identity->shouldReceive('getId')->andReturn($id = uniqid());
        $model->shouldReceive('find')->once()->with($id);
        $storage->setModelResolver(function () use ($model) {
            return $model;
        });
        $storage->find($identity);
    }

    public function testDeleteModel()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('delete')->once();
        $storage->setModelResolver(function () use ($model) {
            return $model;
        });
        $storage->delete($model);
    }

    public function testIdentifyModel()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('getKey')->andReturn($key = uniqid());
        $identify = $storage->identify($model);
        $this->assertSame($key, $identify->getId());
    }

    public function testFindByCriteria()
    {
        $storage = new EloquentStorage('Illimunate\Database\Eloquent\Model');
        $model = m::mock('Illimunate\Database\Eloquent\Model');
        $model->shouldReceive('newQuery')->once()->andReturn($builder = m::mock('Illuminate\Database\Eloquent\Builder'));
        $builder->shouldReceive('where')->once()->with('foo', '=', 'bar')->andReturnSelf();
        $builder->shouldReceive('get->all')->once()->andReturn($result = ['foo', 'bar']);
        $storage->setModelResolver(function () use ($model) {
            return $model;
        });
        $this->assertSame($result, $storage->findBy($criteria = ['foo' => 'bar']));
    }
}
