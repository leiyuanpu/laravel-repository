<?php
/**
 * Date: 2020/4/8
 * Time: 10:00
 */

namespace LypZz\Repositories\Criteria;

use LypZz\Repositories\Eloquent\Repository;

abstract class Criteria
{
    /**
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    abstract public function apply($model, Repository $repository);
}
