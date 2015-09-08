<?php

namespace app\controllers;

use app\models\RecordTask;
use Yii;
use app\models\Record;
use app\models\RecordSearch;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RecordController implements the CRUD actions for Record model.
 */
class RecordController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Record models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RecordSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Record model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Record model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Record();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Record model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $transaction = Yii::$app->db->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            RecordTask::deleteAll([
                'record_id' => $model->id,
            ]);
            if (is_array($model->tasksForm)) {
                foreach ($model->tasksForm as $task_id){
                    $recordTask = new RecordTask([
                        'record_id' => $model->id,
                        'task_id'   => $task_id,
                    ]);
                    $recordTask->save();
                }
            }
            $transaction->commit();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Record model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Record model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Record the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Record::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAssign()
    {
        $taskId    = Yii::$app->request->getBodyParam('task');
        $windowId  = Yii::$app->request->getBodyParam('window');
        $processId = Yii::$app->request->getBodyParam('process');

        $transaction = Yii::$app->db->beginTransaction();
        $timezone = new \DateTimeZone(Yii::$app->timeZone);
        $from = new \DateTime('today', $timezone);
        $to = new \DateTime('tomorrow', $timezone);
        try {
            $ids = Record::find()
                ->with('window')
                ->select(['id'])
                ->andFilterWhere(['window_id' => $windowId, 'window.process_id' => $processId])
                ->andWhere(['>=', 'start', $from->format('Y-m-d H:i:s')])
                ->andWhere(['<', 'end', $to->format('Y-m-d H:i:s')])
                ->createCommand()
                ->queryColumn();
            RecordTask::deleteAll(['record_id' => $ids]);

            $values = array_map(function($id) use($taskId){
                return [$id, $taskId];
            }, $ids);
            Yii::$app->db
                ->createCommand()
                ->batchInsert('{{record_task}}', ['record_id', 'task_id'], $values)
                ->execute();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
        }
    }
}
