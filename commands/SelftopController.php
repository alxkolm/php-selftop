<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 08.07.15
 * Time: 21:22
 */

namespace app\commands;


use app\components\StatsHelper;
use app\models\Record;
use app\models\RecordTask;
use app\models\Window;
use yii\base\Exception;
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
        $titles = $this->getTitleRecord();
        array_walk(
            $titles,
            function ($a) { echo $a['id'] . ' ' . trim($a['title']) . PHP_EOL; }
        );
    }

    /**
     * Output window.titles
     */
    public function actionTitles()
    {
        $titles = Window::find()
            ->select([
                'title'
            ])
            ->distinct(true)
            ->orderBy('title')
            ->createCommand()
            ->queryAll();
        array_walk(
            array_filter($titles, function ($a) {return trim($a['title']) != '';}),
            function ($a) { echo trim($a['title']) . PHP_EOL; }
        );
    }

    /**
     * Output window.titles today
     */
    public function actionTitlesToday()
    {
        $titles = Record::find()
            ->joinWith('window')
            ->select([
                'window.title'
            ])
            ->distinct(true)
            ->where('start >= :from AND start < :to', [':from' => date('c', strtotime('today')), ':to' => date('c', strtotime('tomorrow'))])
            ->orderBy('window.title')
            ->createCommand()
            ->queryAll();
        array_walk(
            array_filter($titles, function ($a) {return trim($a['title']) != '';}),
            function ($a) { echo trim($a['title']) . PHP_EOL; }
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
                $tasks = $record->getRecordTasks()->andWhere('is_prediction = 0')->all();
                if ($tasks){
                    $out = $trainFile;
                    $label = $tasks[0]->id;
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

    public function actionTrainData2()
    {
        $titleFile = fopen('titles.txt', 'w');

        // Generate titles
        $titles = $this->getTitleRecord();
        array_walk($titles, function ($title) use ($titleFile){
            fwrite($titleFile, $title['title'].PHP_EOL);
        });
        fclose($titleFile);
        echo 'Titles successfully generated.'.PHP_EOL;

        // Run sally command
        $sallyOptions = implode(' ', [
            '--input_format lines',
            '--output_format text',
            '--ngram_len 1',
            '--granularity tokens',
            '--vect_embed tfidf',
            '--vect_norm none',
            '--token_delim "%0a%0d%20%22.,:;!?"',
        ]);
        $sallyCommand = "sally {$sallyOptions} titles.txt titles.features.txt";
        echo "Run: {$sallyCommand}\n";
        exec($sallyCommand);

        // Read sally output and generate train set
        $trainFile    = fopen('titles.train.svm', 'w');
        $testFile     = fopen('titles.test.svm', 'w');
        $featuresFile = fopen('titles.features.txt', 'r');
        $i = 0;
        $testIds = [];
        while(($line = fgets($featuresFile)) !== false){
            $line = trim($line);
            if ($line[0] == '#'){
                continue;
            }

            // remove comment
            $posComment = strpos($line, '#');

            $rest = trim(substr($line, 0, -(strlen($line) - $posComment)));
            if ($rest){
                $featureTokens = explode(',', $rest);

                $features = [];
                foreach ($featureTokens as $token) {
                    list($dimension, $weight) = explode('::', $token);
                    $features[$dimension] = (float)$weight;
                }

                // find record
                /** @var Record $record */
                $record = Record::findOne(['id' => $titles[$i]['id']]);
                $i++;
                $out = $testFile;
                $label = 0;
                $tasks = $record->getRecordTasks()->andWhere('is_prediction = 0')->all();
                if ($tasks){
                    $out = $trainFile;
                    $label = $tasks[0]->task_id;
                } else {
                    $testIds[] = $record->id;
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

        fclose($trainFile);
        fclose($testFile);

        // train libsvm model
        // best c=0.5, g=0.0078125
        // best c=128.0, g=0.001953125, rate=67.3947
        // best c=0.5, g=0.0078125, rate=99.5342
        echo 'Run svm-train'.PHP_EOL;
        $trainCommnad = '/home/alx/soft/libsvm-3.20/svm-train -b 1 -c "0.5" -g "0.0078125" titles.train.svm svm.model';
        exec($trainCommnad);

        // predict
        echo 'Run svm-predict'.PHP_EOL;
        $predictCommand = '/home/alx/soft/libsvm-3.20/svm-predict -b 1 -q titles.test.svm svm.model title.predict.txt';
        exec($predictCommand);

        $predictionFile = fopen('title.predict.txt', 'r');
        $i = 0;
        $transaction = \Yii::$app->db->beginTransaction();
        // skip first line
        $dummyLine = fgets($predictionFile);
        $skiped = 0;
        while (($line = fgets($predictionFile)) !== false){
            $line = trim($line);
            if (!isset($testIds[$i])){
                throw new Exception('Что-то не сходится!');
            }
            $recordId = $testIds[$i];
            $i++;
            // Remove all predictions for this record
            RecordTask::deleteAll([
                'record_id' => $recordId,
                'is_prediction' => 1
            ]);
            $parts = explode(' ', $line);
            $predictTask = (int)$parts[0];
            $probability = array_slice($parts, 1);
            $max = max(array_map('floatval', $probability));
            if ($max < 0.75) {
//                echo "skip (with {$max})".PHP_EOL;
                $skiped++;
                continue;
            }
            // Save new prediction
            $model = new RecordTask();
            $model->task_id = $predictTask;
            $model->record_id = $recordId;
            $model->is_prediction = 1;
            $model->save(false);
        }
        $transaction->commit();
        echo "Skiped {$skiped} records\n";
    }

    public function actionTransitionMatrix()
    {
        $from          = strtotime('today');
        $to            = strtotime('tomorrow');
        $matrix        = StatsHelper::transitionMatrix($from, $to);
        $windows       = StatsHelper::windows($from, $to);
        $flattenMatrix = StatsHelper::flattenTransitionMatrix($matrix, $windows);
        foreach ($flattenMatrix as $key => $value){
            echo $value['source'] . "\t" . $value['target'] . "\t" . $value['value'] . PHP_EOL;
        }
    }

    /**
     * @return array
     */
    public function getTitleRecord()
    {
        $titles = Record::find()
            ->joinWith('window')
            ->select(['title', '{{record}}.id'])
            ->orderBy('{{record}}.id')
            ->createCommand()
            ->queryAll();

        return $titles;
    }
}