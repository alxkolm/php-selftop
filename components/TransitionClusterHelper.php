<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 09.12.15
 * Time: 23:08
 */

namespace app\components;


use yii\helpers\FileHelper;

class TransitionClusterHelper
{
    public static function clusterizeMatrix($flattenTransitionMatrix)
    {
        $path = \Yii::getAlias('@runtime/transition_cluster');
        FileHelper::createDirectory($path);
        /** @var string $filename Temp file */
        $filename = tempnam($path, 'tmp');


        // Write strings to file
        $f = fopen($filename, 'w');
        foreach ($flattenTransitionMatrix as $key => $value){
            fwrite($f, $value['source'] . "\t" . $value['target'] . "\t" . $value['value'] . PHP_EOL);
        }

        fclose($f);
        chmod($filename, 0666);

        // make clusters
        $cmdPath = \Yii::getAlias('@app/scikit/transition-cluster.py');
        $cmd = "python {$cmdPath} {$filename}";
        $clusterRaw = [];
        $exitCode = 0;
        $result = exec($cmd, $clusterRaw, $exitCode);
        if ($exitCode != 0){
            return [];
//            throw new Exception('Can\'t run cluster command. ' . $result);
        }
        unlink($filename);
        return $clusterRaw;
    }
}