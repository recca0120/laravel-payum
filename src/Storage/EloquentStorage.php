<?php

namespace Recca0120\LaravelPayum\Storage;

use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Illuminate\Contracts\Foundation\Application;

class EloquentStorage extends AbstractStorage
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @param string                                       $modelClass
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($modelClass, Application $app = null)
    {
        parent::__construct($modelClass);
        $this->app = $app;
    }

    /**
     * makeModel.
     *
     * @method makeModel
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeModel()
    {
        $class = $this->modelClass;

        return is_null($this->app) === true ? new $class : $this->app->make($class);
    }

    /**
     * create.
     *
     * @method create
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create()
    {
        return $this->makeModel();
    }

    /**
     * doUpdateModel.
     *
     * @method doUpdateModel
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function doUpdateModel($model)
    {
        $model->save();
    }

    /**
     * doDeleteModel.
     *
     * @method doDeleteModel
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function doDeleteModel($model)
    {
        $model->delete();
    }

    /**
     * doGetIdentity.
     *
     * @method doGetIdentity
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return IdentityInterface
     */
    protected function doGetIdentity($model)
    {
        return new Identity($model->getKey(), $model);
    }

    /**
     * doFind.
     *
     * @method doFind
     *
     * @param mixed $id
     *
     * @return object|null
     */
    protected function doFind($id)
    {
        return $this->makeModel()->find($id);
    }

    /**
     * findBy.
     *
     * @method findBy
     *
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria)
    {
        $model = $this->makeModel();
        $query = $model->newQuery();
        foreach ($criteria as $name => $value) {
            $query = $query->where($name, '=', $value);
        }

        // return iterator_to_array($query->get());
        return $query->get()->all();
    }
}
