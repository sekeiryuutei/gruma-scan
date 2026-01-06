<?php

namespace app\modules\grumascanmarcacion\controllers;

use app\models\Grumascanconteo;
use app\models\Grumascanconteodetalle;
use app\models\search\GrumascanconteodetalleSearch;
use app\models\User;
use app\models\Item;
use yii\web\Response;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * GrumascanconteodetalleController implements the CRUD actions for Grumascanconteodetalle model.
 */
class GrumascanconteodetalleController extends Controller
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
                        'procesar-formulario' => ['POST'],
                        'validar-clave-admin' => ['POST'],
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
     * Lists all Grumascanconteodetalle models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new GrumascanconteodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Grumascanconteodetalle model.
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
     * Creates a new Grumascanconteodetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Grumascanconteodetalle();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Grumascanconteodetalle model.
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
     * Deletes an existing Grumascanconteodetalle model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Grumascanconteodetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Grumascanconteodetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Grumascanconteodetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Pantalla principal de conteo (AJAX + Grid + input EAN)
     * Similar a do-traspaso-ajax
     */
    public function actionDoConteoAjax($idgrumascanconteo)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        // ✅ Validación de permiso grumascan
        if ($resp = User::validar()) {
            return $resp;
        }

        $modelConteo = Grumascanconteo::findOne((int)$idgrumascanconteo);
        if (!$modelConteo) {
            Yii::$app->session->setFlash('error', 'El conteo no existe.');
            return $this->redirect(['/grumascanmarcacion/grumascanconteo/index']);
        }

        // Solo permitir trabajar conteos en estado CONTEO (0)
        if ((int)$modelConteo->idestado !== 0) {
            Yii::$app->session->setFlash('warning', 'Este conteo no está activo.');
            return $this->redirect(['/grumascanmarcacion/grumascanconteo/view', 'id' => $modelConteo->id]);
        }

        // Si quieres amarrar el conteo al usuario que lo creó:
        if ((int)$modelConteo->created_by !== (int)Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'No estás autorizado para contar en este conteo.');
            return $this->redirect(['/grumascanmarcacion/grumascanconteo/index']);
        }

        $ultimo_ean = $modelConteo->ultimoean;

        $totalUnidades = (int) Grumascanconteodetalle::find()
            ->alias('gscd')
            ->select([
                'total' => new \yii\db\Expression('SUM(
                            CASE
                                WHEN ue.equivalencia IS NOT NULL THEN gscd.cantidad * ue.equivalencia
                                ELSE gscd.cantidad
                            END
                        )')
            ])
            ->innerJoin('item as it', 'gscd.idItem = it.id')
            ->leftJoin('unidadEmpaque as ue', 'ue.codigo = it.unidadEmpaque')
            ->where(['idgrumascanconteo' => $modelConteo->id])
            ->scalar();

        $totalItems = Grumascanconteodetalle::find()
            ->select(['total_cantidad' => new \yii\db\Expression('SUM(cantidad)')])
            ->where(['idgrumascanconteo' => $modelConteo->id])
            ->scalar();

        // Model “dummy” para el formulario de input (como traspasodetalle)
        $model = new Grumascanconteodetalle();
        $model->idgrumascanconteo = $modelConteo->id;
        $model->cantidad = 1;

        $searchModel = new GrumascanconteodetalleSearch();
        $dataProvider = $searchModel->searchAgrupado($this->request->queryParams, $modelConteo->id);

        return $this->render('_form_ajax', [
            'model' => $model,
            'modelConteo' => $modelConteo,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalUnidades' => $totalUnidades,
            'totalItems' => $totalItems,
            'ultimo_ean' => $ultimo_ean,
        ]);
    }

    /**
     * Procesa el escaneo (JSON)
     * - Busca Item por codigoBarras (EAN)
     * - Suma cantidad en grumascanconteodetalle
     * - Actualiza cabecera (ultimoean, totales)
     */
    public function actionProcesarFormulario($idgrumascanconteo)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        if ($resp = User::validar()) {
            return $resp;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $codigoEAN = trim((string) Yii::$app->request->post('codigoBarras', ''));
        $cantidadEntrada = (int) Yii::$app->request->post('cantidadEntrada', 1);
        if ($cantidadEntrada < 1) {
            $cantidadEntrada = 1;
        }

        if ($codigoEAN === '') {
            return ['success' => false, 'message' => 'EAN vacío.'];
        }

        $conteo = Grumascanconteo::findOne((int)$idgrumascanconteo);
        if (!$conteo) {
            return ['success' => false, 'message' => 'El conteo no existe.'];
        }

        if ((int)$conteo->idestado !== 0) {
            return ['success' => false, 'message' => 'El conteo no está activo.'];
        }

        if ((int)$conteo->created_by !== (int)Yii::$app->user->id) {
            return ['success' => false, 'message' => 'No autorizado para este conteo.'];
        }

        // ✅ Buscar item por EAN en tu tabla item
        $item = Item::find()
            ->where(['codigoBarras' => $codigoEAN])
            ->andWhere(['idEstado' => 'ACTIVO']) // si en tu tabla aplica ese campo
            ->one();

        if (!$item) {
            return [
                'success' => false,
                'message' => "No existe artículo para EAN: {$codigoEAN}",
            ];
        }

        // ✅ Un registro por item dentro del conteo (unique index)
        $detalle = Grumascanconteodetalle::find()
            ->where([
                'idgrumascanconteo' => $conteo->id,
                'iditem' => $item->id,
            ])
            ->one();

        if (!$detalle) {
            $detalle = new Grumascanconteodetalle();
            $detalle->idgrumascanconteo = $conteo->id;
            $detalle->iditem = (int)$item->id;
            $detalle->cantidad = 0;
        }
        $detalle->cantidad += $cantidadEntrada;

        if (!$detalle->save()) {
            return [
                'success' => false,
                'message' => 'No se pudo guardar el detalle.',
                'errors'  => $detalle->getErrors(),
            ];
        }

        // ✅ Actualizar cabecera
        $conteo->ultimoean = $codigoEAN;

        // totales calculados (simple y seguro)
        $conteo->totalunidades = (int) Grumascanconteodetalle::find()
            ->alias('gscd')
            ->select([
                'total' => new \yii\db\Expression('SUM(
                            CASE
                                WHEN ue.equivalencia IS NOT NULL THEN gscd.cantidad * ue.equivalencia
                                ELSE gscd.cantidad
                            END
                        )')
            ])
            ->innerJoin('item as it', 'gscd.idItem = it.id')
            ->leftJoin('unidadEmpaque as ue', 'ue.codigo = it.unidadEmpaque')
            ->where(['idgrumascanconteo' => $conteo->id])
            ->scalar();

        $conteo->totalregistros = Grumascanconteodetalle::find()
            ->select(['total_cantidad' => new \yii\db\Expression('SUM(cantidad)')])
            ->where(['idgrumascanconteo' => $conteo->id])
            ->scalar();

        $conteo->save();

        Yii::trace("Conteo {$conteo->id} EAN {$codigoEAN} item {$item->id} cant {$detalle->cantidad}", __METHOD__);

        return [
            'success' => true,
            'message' => 'OK',
            'totalUnidades' => (int)$conteo->totalunidades,
            'totalItems' => (int)$conteo->totalregistros,
            'ultimo_ean' => $codigoEAN,
        ];
    }
    /**
     * Valida clave de admin para habilitar cambio de cantidad (solo UI).
     * Por ahora lo dejamos simple con un param; luego lo conectamos a usuarios/roles.
     */
    public function actionValidarClaveAdmin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->user->isGuest) {
            return ['success' => false, 'message' => 'No autenticado'];
        }

        // ✅ Validación de permiso grumascan
        if ($resp = User::validar()) {
            // Si validar() retorna Response, acá devolvemos JSON simple
            return ['success' => false, 'message' => 'No autorizado'];
        }

        $clave = trim((string)Yii::$app->request->post('clave', ''));

        if ($clave === '') {
            return ['success' => false, 'message' => 'Ingrese la clave'];
        }

        // Opción A (rápida): una clave en params.php (luego la cambiamos)
        $claveEsperada = (string)Yii::$app->params['claveAdminConteo'] ?? '';

        if ($claveEsperada === '') {
            return ['success' => false, 'message' => 'Clave admin no configurada en params'];
        }

        if (!hash_equals($claveEsperada, $clave)) {
            return ['success' => false, 'message' => 'Clave incorrecta'];
        }

        return ['success' => true, 'message' => 'OK'];
    }


    public function actionEliminarUnidadesSku($idgrumascanconteo)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        if ($resp = User::validar()) {
            return $resp;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $idConteo = (int)$idgrumascanconteo;

        $item = trim((string)Yii::$app->request->post('item', ''));
        $idColor = (int)Yii::$app->request->post('idColor', 0);
        $idTalla = (int)Yii::$app->request->post('idTalla', 0);
        $cantidadEliminar = (int)Yii::$app->request->post('cantidadEliminar', 0);

        if ($item === '' || $cantidadEliminar < 1) {
            return ['success' => false, 'message' => 'Datos inválidos.'];
        }

        $conteo = Grumascanconteo::findOne($idConteo);
        if (!$conteo) return ['success' => false, 'message' => 'El conteo no existe.'];
        if ((int)$conteo->idestado !== 0) return ['success' => false, 'message' => 'El conteo no está activo.'];
        if ((int)$conteo->created_by !== (int)Yii::$app->user->id) {
            return ['success' => false, 'message' => 'No autorizado para este conteo.'];
        }

        $db = Yii::$app->db;

        // Total actual SOLO de ese SKU lógico
        $totalSku = (int)(new Query())
            ->from(['gscd' => 'grumascanconteodetalle'])
            ->innerJoin(['it' => 'item'], 'gscd.idItem = it.id')
            ->where(['gscd.idgrumascanconteo' => $idConteo, 'it.item' => $item])
            ->andWhere(new Expression('ISNULL(it.idColor, 0) = :c', [':c' => $idColor]))
            ->andWhere(new Expression('ISNULL(it.idTalla, 0) = :t', [':t' => $idTalla]))
            ->sum('gscd.cantidad');

        if ($cantidadEliminar > $totalSku) {
            return ['success' => false, 'message' => "No puedes eliminar {$cantidadEliminar}. Este SKU solo tiene {$totalSku}."];
        }

        $tx = $db->beginTransaction();
        try {
            // Traer detalles reales de ese SKU (por idItem/EAN), empezando por el que MENOS tiene
            $detalles = (new Query())
                ->from(['gscd' => 'grumascanconteodetalle'])
                ->innerJoin(['it' => 'item'], 'gscd.idItem = it.id')
                ->where(['gscd.idgrumascanconteo' => $idConteo, 'it.item' => $item])
                ->andWhere(new Expression('ISNULL(it.idColor, 0) = :c', [':c' => $idColor]))
                ->andWhere(new Expression('ISNULL(it.idTalla, 0) = :t', [':t' => $idTalla]))
                ->select([
                    'id' => 'gscd.id',
                    'cantidad' => 'gscd.cantidad',
                ])
                ->orderBy(['gscd.cantidad' => SORT_ASC, 'gscd.id' => SORT_ASC])
                ->all($db);

            $restante = $cantidadEliminar;

            foreach ($detalles as $d) {
                if ($restante <= 0) break;

                $idDetalle = (int)$d['id'];
                $cantDet = (int)$d['cantidad'];

                if ($cantDet <= $restante) {
                    // borrar fila completa
                    \app\models\Grumascanconteodetalle::deleteAll(['id' => $idDetalle]);
                    $restante -= $cantDet;
                } else {
                    // reducir cantidad
                    $db->createCommand()
                        ->update('grumascanconteodetalle', ['cantidad' => $cantDet - $restante], ['id' => $idDetalle])
                        ->execute();
                    $restante = 0;
                }
            }

            // Recalcular cabecera (igual que en procesar)
            $conteo->totalunidades = (int)\app\models\Grumascanconteodetalle::find()
                ->alias('gscd')
                ->select([
                    'total' => new Expression('SUM(
                    CASE
                        WHEN ue.equivalencia IS NOT NULL THEN gscd.cantidad * ue.equivalencia
                        ELSE gscd.cantidad
                    END
                )')
                ])
                ->innerJoin('item as it', 'gscd.idItem = it.id')
                ->leftJoin('unidadEmpaque as ue', 'ue.codigo = it.unidadEmpaque')
                ->where(['idgrumascanconteo' => $idConteo])
                ->scalar();

            $conteo->totalregistros = (int)\app\models\Grumascanconteodetalle::find()
                ->where(['idgrumascanconteo' => $idConteo])
                ->sum('cantidad');

            $conteo->save();

            $tx->commit();

            return [
                'success' => true,
                'message' => 'OK',
                'totalUnidades' => (int)$conteo->totalunidades,
                'totalItems' => (int)$conteo->totalregistros,
                'ultimo_ean' => $conteo->ultimoean,
            ];
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Error al eliminar unidades.'];
        }
    }

    public function actionFinalizarConteo($idgrumascanconteo)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        if ($resp = User::validar()) {
            return $resp;
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $conteo = Grumascanconteo::findOne((int)$idgrumascanconteo);
        if (!$conteo) {
            Yii::$app->response->statusCode = 404;
            return ['success' => false, 'message' => 'El conteo no existe.'];
        }

        if ((int)$conteo->idestado !== 0) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'message' => 'El conteo no está activo.'];
        }

        if ((int)$conteo->created_by !== (int)Yii::$app->user->id) {
            Yii::$app->response->statusCode = 403;
            return ['success' => false, 'message' => 'No autorizado para este conteo.'];
        }

        $tieneDetalles = $conteo->getGrumascanconteodetalles()->exists();
        if (!$tieneDetalles) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => 'No se puede finalizar: el conteo no tiene detalles registrados.'
            ];
        }

        $conteo->idestado = 1; // terminado
        if (!$conteo->save()) {
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'No se pudo finalizar el conteo.'];
        }

        return ['success' => true, 'message' => 'OK'];
    }
}
