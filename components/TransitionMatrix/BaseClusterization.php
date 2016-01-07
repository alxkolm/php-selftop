<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 07.01.16
 * Time: 17:29
 */

namespace app\components\TransitionMatrix;


use yii\base\Object;

abstract class BaseClusterization extends Object
{
    /** @var  Matrix */
    public $matrix;

    /** @var array Map window_id => clusterNumber */
    protected $clusters;

    public function getClusters()
    {
        if ($this->clusters === null) {
            $this->clusters = $this->clusterization();
        }
        return $this->clusters;
    }

    public function mapToClusters(array $windows_id)
    {
        $clusters = $this->getClusters();
        return array_map(function ($win_id) use ($clusters) {
            return isset($clusters[$win_id]) ? (int) $clusters[$win_id] : -1;
        },
            $windows_id);
    }

    /**
     * @return mixed
     */
    abstract protected function clusterization();
}