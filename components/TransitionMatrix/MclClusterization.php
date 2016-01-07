<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 06.01.16
 * Time: 23:27
 */

namespace app\components\TransitionMatrix;


use yii\base\Object;
use yii\helpers\FileHelper;

class MclClusterization extends BaseClusterization
{
    protected function clusterization()
    {
        $flattenTransitionMatrix = $this->matrix->flatten(Matrix::FLATTEN_MATRIX_BY_ID);
        $path = \Yii::getAlias('@runtime/transition_cluster');
        FileHelper::createDirectory($path);
        /** @var string $filename Temp file */
        $filename    = tempnam($path, 'data_');
        $filenameMci = tempnam($path, 'mci_');
        $filenameTab = tempnam($path, 'tab_');
        $filenameClusterNative = tempnam($path, 'clstrn_');
        $filenameCluster = tempnam($path, 'clstr_');

        // Write strings to file
        $f = fopen($filename, 'w');
        foreach ($flattenTransitionMatrix as $key => $value){
            fwrite($f, $value['source'] . "\t" . $value['target'] . "\t" . $value['value'] . PHP_EOL);
        }
//        // matrix diagonal cells for consistent output
//        foreach ($windows as $key => $value){
//            fwrite($f, $key . "\t" . $key . "\t" . '0' . PHP_EOL);
//        }

        fclose($f);
        chmod($filename, 0666);

        // make clusters
        $cmd = "mcxload --stream-mirror -abc {$filename} -o {$filenameMci} -write-tab {$filenameTab}"
            ." && mcl {$filenameMci} -o {$filenameClusterNative}"
            ." && mcxdump -icl {$filenameClusterNative} -tabr {$filenameTab} -o {$filenameCluster}";
        $exitCode = 0;
        $result = exec($cmd, $output, $exitCode);
        if ($exitCode != 0){
//            return [];
            throw new Exception('Can\'t run cluster command. ' . $result);
        }
        chmod($filename, 0666);
        chmod($filenameMci, 0666);
        chmod($filenameTab, 0666);
        chmod($filenameClusterNative, 0666);
        chmod($filenameCluster, 0666);

        $clustersLines = array_map('trim', file($filenameCluster));
        $winIdCluster = array_map(function($line, $clusterId){
            $wids = explode("\t", $line);
            return array_combine($wids, array_fill(0,count($wids), $clusterId));
        }, $clustersLines, array_keys($clustersLines));

        $winIdCluster = array_reduce($winIdCluster, function($r,$v){
            return $r + $v;
        }, []);


        unlink($filename);
        unlink($filenameMci);
        unlink($filenameTab);
        unlink($filenameClusterNative);
        unlink($filenameCluster);
        return $winIdCluster;
    }
}