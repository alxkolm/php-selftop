<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 10.12.15
 * Time: 21:42
 */

namespace app\components;


class AlchemyHelper
{
    public static function buildData($transitionMatrix, $windows, $winIdCluster = null)
    {
        $graph = [
            'nodes' => [],
            'edges' => [],
        ];

        foreach ($windows as $win){
            $node = [
                'id'      => $win['id'],
                'caption' => $win['title'],
            ];
            if ($winIdCluster !== null){
                $node['cluster'] = $winIdCluster[$win['id']];
            }
            $graph['nodes'][] = $node;
        }

        foreach ($transitionMatrix as $k=>$row){
            foreach ($row as $j => $value){
                $graph['edges'][] = [
                    'source' => $k,
                    'target' => $j,
                ];
            }
        }
        return $graph;
    }
}