<?php

namespace Recca0120\LaravelPayum\Storage;

use Illuminate\Contracts\Foundation\Application;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;

class EloquentStorage extends AbstractStorage
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $app;

    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param string                                       $modelClass
     */
    public function __construct($modelClass, Application $app)
    {
        parent::__construct($modelClass);
        $this->app = $app;
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
        return $this->app->make($this->modelClass);
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
        return $this->app->make($this->modelClass)->find($id);
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
        $model = $this->app->make($this->modelClass);
        $query = $model->newQuery();
        foreach ($criteria as $name => $value) {
            $query = $query->where($name, '=', $value);
        }

        // return iterator_to_array($query->get());
        return $query->get()->all();
    }
}
