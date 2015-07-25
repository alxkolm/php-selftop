<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 08.07.15
 * Time: 21:22
 */

namespace app\commands;


use app\models\Record;
use app\models\Window;
use yii\console\Controller;

class SelftopController extends Controller
{
    /**
     * Output window titles.
     */
    public function actionTitle()
    {
        $titles = Window::find()
            ->select(['title'])
            ->orderBy('title')
            ->createCommand()
            ->queryColumn();
        array_walk(
            array_filter(
                $titles,
                function ($a) { return !empty(trim($a)); }
            ),
            function ($a) { echo $a.PHP_EOL; }
        );
    }

    /**
     * Output window titles and process_id.
     */
    public function actionTitleProcess()
    {
        $titles = Window::find()
            ->select(['title', 'process_id'])
            ->orderBy('title')
            ->createCommand()
            ->queryAll();
        array_walk(
            array_filter(
                $titles,
                function ($a) { return !empty(trim($a['title'])); }
            ),
            function ($a) { echo $a['process_id']. ' ' .$a['title'].PHP_EOL; }
        );
    }
    /**
     * Output window.title and window.id columns
     */
    public function actionTitleWindow()
    {
        $titles = Window::find()
            ->select(['title', 'id'])
            ->orderBy('title')
            ->createCommand()
            ->queryAll();
        array_walk(
//            array_filter(
//                $titles,
//                function ($a) { return !empty(trim($a['title'])); }
//            ),
            $titles,
            function ($a) { echo $a['id'] . ' ' . trim($a['title']) . PHP_EOL; }
        );
    }
    /**
     * Output window.title and record.id columns
     */
    public function actionTitleRecord()
    {
        $titles = Record::find()
            ->joinWith('window')
            ->select(['title', '{{record}}.id'])
            ->orderBy('{{record}}.id')
            ->createCommand()
            ->queryAll();
        array_walk(
            $titles,
            function ($a) { echo $a['id'] . ' ' . trim($a['title']) . PHP_EOL; }
        );
    }

    public function actionTrainData()
    {
        $trainFile = fopen('train.svm', 'w');
        $testFile = fopen('test.svm', 'w');

        while($line = fgets(STDIN)){
            if ($line[0] == '#'){
                continue;
            }
            $line = trim($line);
            // take label
            $posLabel = strpos($line, ' ');
            $label = substr($line, 0, $posLabel);

            // remove comment
            $posComment = strpos($line, '#');

            $rest = trim(substr($line, $posLabel + 1, $posComment - $posLabel - 1));
            if ($rest){
                $featureTokens = explode(',', $rest);

                $features = [];
                foreach ($featureTokens as $token) {
                    list($dimension, $weight) = explode('::', $token);
                    $features[$dimension] = (float)$weight;
                }

                // find record
                $record = Record::findOne(['id' => $label]);
                $out = $testFile;
                $label = 0;
                if ($record->tasks){
                    $out = $trainFile;
                    $label = $record->tasks[0]->id;
                }
                fwrite($out, $label. ' ');
                $parts = [
                    '1:'.$record->window_id,
                    '2:'.$record->window->process_id,
                ];
                foreach ($features as $dim => $weight){
                    $parts[] = $dim.':'.$weight;
                }
                fwrite($out, implode(' ', $parts));
                fwrite($out, PHP_EOL);
            }

        }
    }
}