<?php

namespace app\controllers;

use app\models\Record;
use app\models\RecordTask;
use Yii;
use app\models\Window;
use app\models\WindowCrudSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WindowController implements the CRUD actions for Window model.
 */
class WindowController extends Controller
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
     * Lists all Window models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WindowCrudSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Window model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $query = Record::find();
        $recordsDataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->andWhere(['window_id' => $id]);
        $query->orderBy('id DESC');

        return $this->render('view', [
            'model' => $this->findModel($id),
            'recordsDataProvider' => $recordsDataProvider,
        ]);
    }

    /**
     * Creates a new Window model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Window();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Window model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Window model.
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
     * Finds the Window model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Window the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Window::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionAssignTask($id){
        $records = Record::find()->where(['window_id' => $id])->select(['id'])->column();
        $transaction = Yii::$app->db->beginTransaction();
        foreach ($records as $record_id) {
            $link            = new RecordTask();
            $link->record_id = $record_id;
            $link->task_id   = Yii::$app->request->getBodyParam('task_id');
            $link->save();
        }
        $transaction->commit();
        $this->redirect(['view', 'id' => $id]);
    }
    public function actionClearTask($id){
        // TODO дописать запрос на удаление
        $sql = 'DELETE FROM {{record_task}}
                WHERE record_id IN (SELECT id FROM record WHERE window_id = :window_id)';
        Yii::$app->db->createCommand($sql, [':window_id' => $id])->execute();
        $this->redirect(['view', 'id' => $id]);
    }
}
