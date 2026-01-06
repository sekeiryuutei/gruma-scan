<?php

use app\models\Grumascanconteo;
use app\models\Grumascanconteodetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\GrumascanconteoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Grumascanconteos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanconteo-index">

    <p>
        <?= Html::a('Registrar', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    // echo $this->render('_search', ['model' => $searchModel]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->bodega ? $model->marcacion->bodega->nombre : 'sin marcacion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->ubicacion ?? 'sin ubicacion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->seccion ?? 'sin seccion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'label' => 'consecutivo marcacion',
                'value' => function ($model) {
                    return $model->idmarcacion;
                },
            ],
            [
                'attribute' => 'idestado',
                'value' => function ($model) {
                    return $model->estado->nombre;
                },
            ],
            'ultimoean',
            'totalregistros',
            'totalunidades',
            [
                'attribute' => 'created_by',
                'label' => 'Usuario',
                'contentOptions' => ['data-cellvalue' => 'Usuario'],
                'value' => function ($model) {
                    return $model->usuario ? $model->usuario->username : 'Sin nombre de usuario';
                },
            ],
            'created_at',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '18%'],
                'contentOptions' => ['data-cellvalue' => 'Acciones'],
                'template' => '{detalleAjax} {update} {anular} {delete} {view}',

                'buttons' => [

                    // ✅ Ir a pantalla AJAX (contar)
                    'detalleAjax' => function ($url, $model) {
                        return Html::a(
                            '<i class="fa fa-barcode"></i>',
                            ['/grumascanmarcacion/grumascanconteodetalle/do-conteo-ajax', 'idgrumascanconteo' => $model->id],
                            [
                                'title' => 'Ir a conteo (AJAX)',
                                'class' => 'btn btn-default btn_detalleajax',
                                'data-pjax' => '0',
                            ]
                        );
                    },

                    // ✅ Editar (solo si está en conteo)
                    'update' => function ($url, $model) {
                        return Html::a(
                            '<i class="fa fa-edit"></i>',
                            ['update', 'id' => $model->id],
                            [
                                'title' => 'Actualizar conteo',
                                'class' => 'btn btn-default btn_update',
                                'data-pjax' => '0',
                            ]
                        );
                    },

                    // ✅ Anular (permite aunque tenga detalles)
                    'anular' => function ($url, $model) {
                        return Html::a(
                            '<i class="fa fa-ban"></i>',
                            ['anular', 'id' => $model->id],
                            [
                                'title' => 'Anular conteo',
                                'class' => 'btn btn-default btn_anular',
                                'data' => [
                                    'confirm' => '¿Seguro que deseas ANULAR este conteo? (No se elimina, solo cambia de estado)',
                                    'method' => 'post',
                                ],
                            ]
                        );
                    },

                    // ✅ Eliminar SOLO si NO tiene detalles (y está en conteo)
                    'delete' => function ($url, $model) {

                        $tieneDetalles = Grumascanconteodetalle::find()
                            ->where(['idgrumascanconteo' => (int)$model->id])
                            ->exists();

                        if ($tieneDetalles) {
                            // Si quieres ocultarlo totalmente, puedes retornar '' aquí.
                            // Yo lo muestro "bloqueado" para que se entienda por qué no se puede.
                            return Html::a(
                                '<i class="fa fa-lock"></i>',
                                'javascript:void(0);',
                                [
                                    'title' => 'No se puede eliminar: tiene detalles',
                                    'class' => 'btn btn-default disabled',
                                    'style' => 'pointer-events:none; opacity:.6;',
                                ]
                            );
                        }

                        return Html::a(
                            '<i class="fa fa-trash"></i>',
                            ['delete', 'id' => $model->id],
                            [
                                'title' => 'Eliminar conteo',
                                'class' => 'btn btn-default btn_delete',
                                'data' => [
                                    'confirm' => '¿Seguro que deseas ELIMINAR este conteo?',
                                    'method' => 'post',
                                ],
                            ]
                        );
                    },

                    // ✅ Ver
                    'view' => function ($url, $model) {
                        return Html::a(
                            '<i class="fa fa-eye"></i>',
                            ['view', 'id' => $model->id],
                            [
                                'title' => 'Ver conteo',
                                'class' => 'btn btn-default btn_view',
                                'data-pjax' => '0',
                            ]
                        );
                    },
                ],

                // ✅ TU ESTILO: visibleButtons
                'visibleButtons' => [
                    'detalleAjax' => function ($model, $key, $index) {
                        return (int)$model->idestado === 0; // solo conteo activo
                    },
                    'update' => function ($model, $key, $index) {
                        return (int)$model->idestado === 0; // solo conteo activo
                    },
                    'anular' => function ($model, $key, $index) {
                        return (int)$model->idestado != 2; // solo conteo activo
                    },
                    'delete' => function ($model, $key, $index) {
                        // solo mostrar delete si está activo (0)
                        return (int)$model->idestado === 0;
                    },
                    'view' => function ($model, $key, $index) {
                        return true;
                    },
                ],
            ],
        ],
    ]); ?>


</div>