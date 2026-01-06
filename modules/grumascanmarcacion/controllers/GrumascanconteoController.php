<?php

namespace app\modules\grumascanmarcacion\controllers;

use app\models\Grumascanconteo;
use app\models\Grumascanconteodetalle;
use app\models\Grumascanmarcacion;
use app\models\search\GrumascanconteoSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GrumascanconteoController implements the CRUD actions for Grumascanconteo model.
 */
class GrumascanconteoController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'anular' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'], // solo logueados
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Grumascanconteo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GrumascanconteoSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grumascanconteo model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Grumascanconteo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = new Grumascanconteo();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $userId = (int)Yii::$app->user->id;
            // 1) Validar que exista la marcación
            $marcacion = Grumascanmarcacion::findOne((int)$model->idmarcacion);
            if (!$marcacion) {
                $model->addError('idmarcacion', 'La marcación no existe.');
                return $this->render('create', ['model' => $model]);
            }

            // 1.1) Validar marcación completa
            if (empty($marcacion->ubicacion) || empty($marcacion->seccion) || empty($marcacion->idbodega)) {
                $model->addError('idmarcacion', 'La marcación no está completa (bodega / ubicación / sección).');
                return $this->render('create', ['model' => $model]);
            }

            // 2) Regla: SOLO 1 conteo REAL por marcación (estado 0 o 1)
            $conteoReal = Grumascanconteo::find()
                ->where(['idmarcacion' => (int)$model->idmarcacion])
                ->andWhere(['in', 'idestado', [0, 1]]) // 0=activo, 1=terminado (bloquean)
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if ($conteoReal) {

                // Si está ACTIVO (0)
                if ((int)$conteoReal->idestado === 0) {

                    // ✅ Solo el mismo usuario puede continuar
                    if ((int)$conteoReal->created_by === $userId) {
                        Yii::$app->session->setFlash(
                            'warning',
                            "Ya tienes un conteo activo para esta marcación (conteo #{$conteoReal->id})."
                        );

                        return $this->redirect([
                            '/grumascanmarcacion/grumascanconteodetalle/do-conteo-ajax',
                            'idgrumascanconteo' => $conteoReal->id,
                        ]);
                    }

                    // ❌ Otro usuario lo tiene activo
                    $nombre = $conteoReal->usuario ? $conteoReal->usuario->username : 'otro usuario';
                    Yii::$app->session->setFlash(
                        'error',
                        "Esta marcación ya está siendo contada por {$nombre} (conteo #{$conteoReal->id}). No puedes iniciar otro conteo."
                    );

                    return $this->redirect(['index']);
                }

                // Si está TERMINADO (1), NO permitir crear otro
                Yii::$app->session->setFlash(
                    'error',
                    "Esta marcación ya tiene un conteo terminado (conteo #{$conteoReal->id}). No se puede crear otro."
                );

                return $this->redirect(['index']);
            }


            // 3) (Opcional) Aviso si hubo anulados (NO bloquean)
            $ultimoAnulado = Grumascanconteo::find()
                ->where([
                    'idmarcacion' => (int)$model->idmarcacion,
                    'idestado' => 2,
                ])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if ($ultimoAnulado) {
                Yii::$app->session->setFlash(
                    'info',
                    "Esta marcación tiene conteos anulados previos (último anulado #{$ultimoAnulado->id}). Se creará un nuevo conteo."
                );
            }

            // 4) Campos manejados por código
            $model->idestado = 0;
            $model->totalregistros = 0;
            $model->totalunidades  = 0;

            if ($model->save()) {
                return $this->redirect([
                    '/grumascanmarcacion/grumascanconteodetalle/do-conteo-ajax',
                    'idgrumascanconteo' => $model->id,
                ]);
            }

            Yii::error(['Grumascanconteo_save_errors' => $model->getErrors()], __METHOD__);
            Yii::$app->session->setFlash(
                'error',
                'No se pudo guardar el conteo: ' . json_encode($model->getFirstErrors())
            );

            return $this->render('create', ['model' => $model]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * Updates an existing Grumascanconteo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Grumascanconteo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // ✅ Validación: si tiene detalles, no permitir eliminar
        $tieneDetalles = Grumascanconteodetalle::find()
            ->where(['idgrumascanconteo' => (int)$model->id])
            ->exists();

        if ($tieneDetalles) {
            Yii::$app->session->setFlash('error', 'No se puede eliminar: el conteo tiene detalles registrados.');
            return $this->redirect(['index']);
        }

        try {
            if ($model->delete() === false) {
                Yii::$app->session->setFlash('error', 'No se pudo eliminar el conteo.');
            } else {
                Yii::$app->session->setFlash('success', 'Conteo eliminado correctamente.');
            }
        } catch (\Throwable $e) {
            Yii::error(['delete_conteo_error' => $e->getMessage()], __METHOD__);
            Yii::$app->session->setFlash('error', 'Error al eliminar el conteo.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Grumascanconteo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Grumascanconteo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grumascanconteo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAnular($id)
    {
        $model = $this->findModel($id);

        // Solo anular si está activo
        if ((int)$model->idestado == 0 &&  (int)$model->idestado == 1) {
            Yii::$app->session->setFlash('error', 'Solo puedes anular conteos en estado CONTEO.');
            return $this->redirect(['index']);
        }
        $tieneDetalles = Grumascanconteodetalle::find()
            ->where(['idgrumascanconteo' => (int)$model->id])
            ->exists();

        if (!$tieneDetalles) {
            Yii::$app->session->setFlash('error', 'No se puede Anular: el conteo No tiene detalles registrados.');
            return $this->redirect(['index']);
        }
        $model->idestado = 2; // ANULADO

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Conteo anulado correctamente.');
        } else {
            Yii::error(['anular_errors' => $model->getErrors()], __METHOD__);
            Yii::$app->session->setFlash('error', 'No se pudo anular: ' . json_encode($model->getFirstErrors()));
        }

        return $this->redirect(['index']);
    }
}
