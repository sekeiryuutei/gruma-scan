<?php

use common\widgets\Alert;
use app\models\Bodegas;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var $this yii\web\View */
/** @var $model app\models\forms\GrumascanMarcacionPrintForm */
/** @var $printers array */

$this->title = 'Imprimir stickers de marcación';
$this->params['breadcrumbs'][] = $this->title;

$printerItems = $printers; // ya viene listo: [id => label]

?>

<div class="marcacion-print-index">

    <?= Alert::widget() ?>

    <?php $form = ActiveForm::begin([
        'action' => ['print'],
        'method' => 'post',
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'printer_id')->dropDownList(
                $printerItems,
                ['prompt' => 'Seleccione una impresora...']
            ) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'cantidad')->input('number', [
                'min' => 1,
                'max' => 500,
                'value' => $model->cantidad ?: 1,
            ]) ?>
        </div>
    </div>

    <hr>

    <!-- <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'idbodega')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => ' Bodega Origen ... ',
                    'id' => 'id-bodega-origen',
                    'required' => true
                ]
            )
            ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'ubicacion')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'seccion')->textInput(['maxlength' => true]) ?>
        </div>
    </div> -->

    <div class="form-group" style="margin-top: 10px;">
        <?= Html::submitButton('Imprimir', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <div style="margin-top: 15px;">
        <p><strong>Formato impreso:</strong></p>
        <pre style="background:#f7f7f7; padding:10px; border:1px solid #ddd;">
| --------------------------------------------------------------|
|   codigo de barras (id grumascanMarcacion)                     |
| Unidades :__________                                           |
| Usuario  :__________                                           |
| --------------------------------------------------------------|
        </pre>
    </div>

</div>