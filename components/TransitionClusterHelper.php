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
    public static function clusterizeMatrix($transitionMatrix, $windows)
    {
        $flattenTransitionMatrix = StatsHelper::flattenTransitionMatrix($transitionMatrix, $windows);
        $path = \Yii::getAlias('@runtime/transition_cluster');
        FileHelper::createDirectory($path);
        /** @var string $filename Temp file */
        $filename = tempnam($path, 'tmp');


        // Write strings to file
        $f = fopen($filename, 'w');
        foreach ($flattenTransitionMatrix as $key => $value){
            fwrite($f, $value['source'] . "\t" . $value['target'] . "\t" . $value['value'] . PHP_EOL);
        }
        // matrix diagonal cells for consistent output
        foreach ($windows as $key => $value){
            fwrite($f, $key . "\t" . $key . "\t" . '0' . PHP_EOL);
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
        $winIdCluster = [];
        foreach ($windows as $key => $window){
            $winIdCluster[$window['id']] = $clusterRaw[$key];
        }
        return [$clusterRaw, $winIdCluster];
    }
    public static function clusterizeMatrixMcl($transitionMatrix, $windows)
    {
        $flattenTransitionMatrix = StatsHelper::flattenTransitionMatrix($transitionMatrix, $windows);
        $path = \Yii::getAlias('@runtime/transition_cluster');
        FileHelper::createDirectory($path);
        /** @var string $filename Temp file */
        $filename    = tempnam($path, 'data');
        $filenameMci = tempnam($path, 'mci');
        $filenameTab = tempnam($path, 'tab');
        $filenameClusterNative = tempnam($path, 'clstrn');
        $filenameCluster = tempnam($path, 'clstr');



        // Write strings to file
        $f = fopen($filename, 'w');
        foreach ($flattenTransitionMatrix as $key => $value){
            fwrite($f, $value['source'] . "\t" . $value['target'] . "\t" . $value['value'] . PHP_EOL);
        }
        // matrix diagonal cells for consistent output
        foreach ($windows as $key => $value){
            fwrite($f, $key . "\t" . $key . "\t" . '0' . PHP_EOL);
        }

        fclose($f);
        chmod($filename, 0666);

        // make clusters
        // mcxload --stream-mirror -abc scikit/matrix-norm.csv -o data.mci -write-tab data.tab
        $cmd = "mcxload --stream-mirror -abc {$filename} -o {$filenameMci} -write-tab {$filenameTab}"
            ." && mcl {$filenameMci} -o {$filenameClusterNative}"
            ." && mcxdump -icl {$filenameClusterNative} -tabr {$filenameTab} -o {$filenameCluster}";
        $exitCode = 0;
        $result = exec($cmd, $clusterRaw, $exitCode);
        if ($exitCode != 0){
//            return [];
            throw new Exception('Can\'t run cluster command. ' . $result);
        }
        chmod($filename, 0666);
        chmod($filenameMci, 0666);
        chmod($filenameTab, 0666);
        chmod($filenameClusterNative, 0666);
        chmod($filenameCluster, 0666);


        $winIdCluster = [];
        foreach ($windows as $key => $window){
            $winIdCluster[$window['id']] = $clusterRaw[$key];
        }
        unlink($filename);
        unlink($filenameMci);
        unlink($filenameTab);
        unlink($filenameClusterNative);
        unlink($filenameCluster);
        return [$clusterRaw, $winIdCluster];
    }
}