<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 06.01.16
 * Time: 23:00
 */

namespace app\components\TransitionMatrix;

use yii\base\Component;
use yii\base\Object;

class Matrix extends Object
{
    const FLATTEN_MATRIX_BY_INDEX = 1;
    const FLATTEN_MATRIX_BY_ID = 2;

    /** @var \yii\db\ActiveQuery Query must return column of window_id */
    public $query;

    public $clusterizationClass = '\app\components\TransitionMatrix\MclClusterization';

    /** @var array 2D-array */
    protected $matrix = [];

    /** @var array  List of unique windows id*/
    protected $windowIds = [];

    public function init()
    {
        parent::init();

        // Query data
        $data = $this->query->createCommand()->queryColumn();

        // Construct matrix (2D-array)
        $this->matrix = [];
        $prevWindow = array_shift($data);
        foreach ($data as $windowId){
            if (!in_array($windowId, $this->windowIds)){
                $this->windowIds[] = $windowId;
            }
            if (!isset($this->matrix[$prevWindow])){
                $this->matrix[$prevWindow] = [];
            }

            if (!isset($this->matrix[$prevWindow][$windowId])){
                $this->matrix[$prevWindow][$windowId] = 0;
            }

            $this->matrix[$prevWindow][$windowId]++;

            $prevWindow = $windowId;
        }
    }

    public function getWindowIds()
    {
        return $this->windowIds;
    }

    public function getMatrix()
    {
        return $this->matrix;
    }

    public function clusterization()
    {
        return \Yii::createObject([
            'class'  => $this->clusterizationClass,
            'matrix' => $this,
        ]);
    }

    public function flatten($flags = self::FLATTEN_MATRIX_BY_INDEX)
    {
        $list = [];

        foreach ($this->matrix as $k=>$row){
            foreach ($row as $j => $value){
                switch ($flags){
                    case self::FLATTEN_MATRIX_BY_INDEX:
                        $source = array_search($k, $this->windowIds);
                        $target = array_search($j, $this->windowIds);
                        break;
                    case self::FLATTEN_MATRIX_BY_ID:
                        $source = $k;
                        $target = $j;
                        break;
                    default:
                        throw new Exception('Wrong flags');

                }
                $list[] = [
                    'source' => $source,
                    'target' => $target,
                    'value'  => $value
                ];
            }
        }
        return $list;
    }
}