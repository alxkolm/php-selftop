<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 07.01.16
 * Time: 17:25
 */

namespace app\components\TransitionMatrix;

use yii\helpers\FileHelper;

class AffinityClusterization extends BaseClusterization
{
    protected function clusterization()
    {
        $flattenTransitionMatrix = $this->matrix->flatten(Matrix::FLATTEN_MATRIX_BY_INDEX);
        $path = \Yii::getAlias('@runtime/transition_cluster');
        FileHelper::createDirectory($path);
        /** @var string $filename Temp file */
        $filename = tempnam($path, 'tmp');


        // Write strings to file
        $f = fopen($filename, 'w');
        foreach ($flattenTransitionMatrix as $key => $value) {
            fwrite($f, $value['source'] . "\t" . $value['target'] . "\t" . $value['value'] . PHP_EOL);
        }
        // matrix diagonal cells for consistent output
        foreach ($this->matrix->getWindowIds() as $key => $winId) {
            fwrite($f, $key . "\t" . $key . "\t" . '0' . PHP_EOL);
        }

        fclose($f);
        chmod($filename, 0666);

        // make clusters
        $cmdPath    = \Yii::getAlias('@app/scikit/transition-cluster.py');
        $cmd        = "python {$cmdPath} {$filename}";
        $clusterRaw = [];
        $exitCode   = 0;
        $result     = exec($cmd, $clusterRaw, $exitCode);
        if ($exitCode != 0) {
            return [];
//            throw new Exception('Can\'t run cluster command. ' . $result);
        }
        unlink($filename);
        $winIdCluster = [];
        foreach ($this->matrix->getWindowIds() as $key => $winId) {
            $winIdCluster[$winId] = $clusterRaw[$key];
        }
        return $winIdCluster;
    }
}