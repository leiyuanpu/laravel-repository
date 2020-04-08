<?php
/**
 * Date: 2020/4/8
 * Time: 10:00
 */

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\CriteriaInterface;
use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Criteria\Criteria;
use App\Repositories\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

abstract class Repository implements RepositoryInterface, CriteriaInterface
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    protected $newModel;

    /**
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Prevents from overwriting same criteria in chain usage
     * @var bool
     */
    protected $preventCriteriaOverwriting = true;


    /**
     * Repository constructor.
     *
     * @param \Illuminate\Support\Facades\App $app
     * @param \Illuminate\Support\Collection  $collection
     *
     * @throws \App\Repositories\Exceptions\RepositoryException
     */
    public function __construct(App $app, Collection $collection)
    {
        $this->app = $app;
        $this->criteria = $collection;
        $this->resetScope();
        $this->makeModel();
    }

    /**
     * 指定模型类名称
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * 获取所有
     *
     * @param array $columns 字段
     *
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->get($columns);
    }

    /**
     * ORM关系
     *
     * @param array $relations
     *
     * @return $this
     */
    public function with(array $relations)
    {
        $this->model = $this->model->with($relations);
        return $this;
    }

    /**
     * 分页
     *
     * @param int    $perPage
     * @param array  $columns
     * @param string $pageName
     * @param null   $page
     *
     * @return mixed
     */
    public function paginate($perPage = 25, $columns = array('*'), $pageName = 'page', $page = null)
    {
        $this->applyCriteria();
        return $this->model->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * 创建
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * save a model without massive assignment
     *
     * @param array $data
     *
     * @return bool
     */
    public function saveModel(array $data)
    {
        foreach ($data as $k => $v) {
            $this->model->$k = $v;
        }
        return $this->model->save();
    }

    /**
     * @param array  $data
     * @param        $id
     * @param string $attribute
     *
     * @return mixed
     */
    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param  array $data
     * @param        $id
     *
     * @return mixed
     */
    public function updateRich(array $data, $id)
    {
        if (!($model = $this->model->find($id))) {
            return false;
        }

        return $model->fill($data)->save();
    }

    /**
     * 更加ID删除
     *
     * @param $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }


    /**
     * 根据where条件删除
     *
     * @param      $where
     * @param bool $or
     *
     * @return bool|null
     * @throws \Exception
     */
    public function deleteWhere($where, $or = false)
    {
        $this->applyCriteria();

        $model = $this->model;

        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }
        return $model->delete();
    }

    /**
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->find($id, $columns);
    }

    /**
     * @param       $attribute
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @param       $attribute
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findAllBy($attribute, $value, $columns = array('*'))
    {
        $this->applyCriteria();
        return $this->model->where($attribute, '=', $value)->get($columns);
    }

    /**
     * Find a collection of models by the given query conditions.
     *
     * @param array $where
     * @param array $columns
     * @param bool  $or
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findWhere($where, $columns = ['*'], $or = false)
    {
        $this->applyCriteria();

        $model = $this->model;

        foreach ($where as $field => $value) {
            if ($value instanceof \Closure) {
                $model = (!$or)
                    ? $model->where($value)
                    : $model->orWhere($value);
            } elseif (is_array($value)) {
                if (count($value) === 3) {
                    list($field, $operator, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, $operator, $search)
                        : $model->orWhere($field, $operator, $search);
                } elseif (count($value) === 2) {
                    list($field, $search) = $value;
                    $model = (!$or)
                        ? $model->where($field, '=', $search)
                        : $model->orWhere($field, '=', $search);
                }
            } else {
                $model = (!$or)
                    ? $model->where($field, '=', $value)
                    : $model->orWhere($field, '=', $value);
            }
        }
        return $model->get($columns);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \App\Repositories\Exceptions\RepositoryException
     */
    public function makeModel()
    {
        return $this->setModel($this->model());
    }

    /**
     * Set Eloquent Model to instantiate
     *
     * @param $eloquentModel
     *
     * @return Model
     * @throws RepositoryException
     */
    public function setModel($eloquentModel)
    {
        $this->newModel = $this->app::make($eloquentModel);

        if (!$this->newModel instanceof Model) {
            throw new RepositoryException("Class {$this->newModel} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $this->newModel;
    }

    /**
     * @return $this
     */
    public function resetScope()
    {
        $this->skipCriteria(false);
        return $this;
    }

    /**
     * @param bool $status
     *
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     *
     * @return $this
     */
    public function getByCriteria(Criteria $criteria)
    {
        $this->model = $criteria->apply($this->model, $this);
        return $this;
    }

    /**
     * @param Criteria $criteria
     *
     * @return $this
     */
    public function pushCriteria(Criteria $criteria)
    {
        if ($this->preventCriteriaOverwriting) {
            // Find existing criteria
            $key = $this->criteria->search(function ($item) use ($criteria) {
                return (is_object($item) && (get_class($item) == get_class($criteria)));
            });

            // Remove old criteria
            if (is_int($key)) {
                $this->criteria->offsetUnset($key);
            }
        }

        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * @param \App\Repositories\Criteria\Criteria $criteria
     *
     * @return $this
     */
    public function popCriteria(Criteria $criteria)
    {
        $allCriteria = $this->getCriteria();
        $this->criteria = $allCriteria->filter(function ($item) use ($criteria) {
            if (get_class($item) == get_class($criteria)) {
                return;
            }
        });

        return $this;
    }

    /**
     * @return $this
     */
    public function applyCriteria()
    {
        if ($this->skipCriteria === true) {
            return $this;
        }

        foreach ($this->getCriteria() as $criteria) {
            if ($criteria instanceof Criteria) {
                $this->model = $criteria->apply($this->model, $this);
            }
        }

        return $this;
    }
}
