# laravel-repository
repository用于抽象数据层，让我们的应用程序更易于维护。

安装
~~~
composer require lypzz/laravel-repository
~~~

Laravel
~~~
>= larave7
~~~

目录说明
~~~
Contracts : 定义查询的接口
Criteria ：查询条件
Eloquent ：model的映射
Exceptions ：异常处理
~~~

Methods
~~~
LypZz\Repositories\Contracts\RepositoryInterface

public function all($columns = array('*'));
public function paginate($perPage = 1, $columns = array('*'), $pageName = 'page', $page = null);
public function create(array $data);
public function saveModel(array $data);
public function update(array $data, $id);
public function delete($id);
public function deleteWhere($where, $or = false);
public function find($id, $columns = array('*'));
public function findBy($field, $value, $columns = array('*'));
public function findAllBy($field, $value, $columns = array('*'));
public function findWhere($where, $columns = array('*'));
~~~
创建映射：
~~~
在Http里创建一个Repositories/Eloquent文件夹然后创建一个与model映射的Repository并且
继承LypZz\Repositories\Eloquent\Repository 

AsinRepository.php

namespace App\Repositories\Eloquent;

use LypZz\Repositories\Eloquent\Repository;
class AsinRepository extends Repository
{
    public function model() {
        return 'App\Models\Asin';
    }
}
~~~

控制器调用：
~~~
class IndexController extends Controller
{
    /**
     * @var $asinRepository AsinRepository
     */
    protected $asinRepository;

    public function __construct(AsinRepository $asinRepository)
    {

         $this->asinRepository = $asinRepository;
    }

    public function index()
    {
        
        #如果不选择注入的方式可以寻找app('App\Repositories\Eloquent\AsinRepository')这种方式获取
        //$this->asinRepository = app('App\Repositories\Eloquent\AsinRepository');
        #查询一条信息
        $id = 1;
        $this->asinRepository->find($id); //返回一个集合
        
        #查询所有信息
        $this->asinRepository->all();
        
        #分页查询
        $this->asinRepository->paginate(10,['*'],'page',1);
        
        #条件查询
        $this->asinRepository->findWhere(['asin'=>'B01KZ1J2CU','id'=>1],['*'],'or')
        
        #闭包条件查询
        $this->asinRepository->findWhere(['asin'=>function ($query)  {
            $query->where('id', 1);
            $query->orWhere('id', 3);
        }],['*'],'or');
        
        #with方式查询
        $this->asinRepository->with(['asinAddon'])->find(1)              
    }
~~~
Criteria方式查询,在Http里创建一个Repositories/Criteria文件夹并且创建文件IdMoreThanTwo.php
~~~
class IdMoreThanTwo extends Criteria
{
    public function apply($model, Repository $repository)
    {
        $model = $model->where('id','>', 2); #ID大于二的条件
        return $model;
    }
}

class IndexController extends Controller
{
    /**
     * @var $asinRepository AsinRepository
     */
    protected $asinRepository;

    public function __construct(AsinRepository $asinRepository)
    {

         $this->asinRepository = $asinRepository;
    }

    public function index()
    {
        $this->asinRepository = app('App\Repositories\Eloquent\AsinRepository');
        $this->asinRepository->pushCriteria(new IdMoreThanTwo());
        $this->asinRepository->all() #查出ID大于2的所有值
     }
  }
~~~
更新删除：
~~~
$this->asinRepository->update(['asin'=>'B01N909V8I'],564522);

$this->asinRepository->deleteWhere(['id' => 1]);

$this->asinRepository->create(['id' => 1]);

$this->asinRepository->saveModel(['id' => 1]);

~~~

