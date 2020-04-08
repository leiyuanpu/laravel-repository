<?php
/**
 * Date: 2020/4/8
 * Time: 9:58
 */

namespace App\Repositories\Contracts;

interface RepositoryInterface
{

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = array('*'));

    /**
     * @param int    $perPage
     * @param array  $columns
     * @param string $pageName
     * @param null   $page
     *
     * @return mixed
     */
    public function paginate($perPage = 1, $columns = array('*'), $pageName = 'page', $page = null);

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * @param array $data
     *
     * @return bool
     */
    public function saveModel(array $data);

    /**
     * @param array $data
     * @param       $id
     *
     * @return mixed
     */
    public function update(array $data, $id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function delete($id);

    /**
     * @param      $where
     * @param bool $or
     *
     * @return mixed
     */
    public function deleteWhere($where, $or = false);

    /**
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = array('*'));

    /**
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = array('*'));

    /**
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findAllBy($field, $value, $columns = array('*'));

    /**
     * @param       $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere($where, $columns = array('*'));
}
