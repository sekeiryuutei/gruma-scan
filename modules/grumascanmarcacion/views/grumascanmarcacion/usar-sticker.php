<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\Bodegas;

/** @var $this yii\web\View */
/** @var $model app\models\forms\GrumascanMarcacionUseForm */

$this->title = 'Asignar marcación';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="grumascan-usar-sticker">

    <h3><?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin([
        'method' => 'post',
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'codigo')->textInput([
                'autofocus' => true,
                'placeholder' => 'Escanee el sticker...',
            ]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'idbodega')->dropDownList(
                Bodegas::getListaData(),
                ['prompt' => 'Bodega...', 'required' => true]
            ) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'ubicacion')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'seccion')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group" style="margin-top:10px;">
        <?= Html::submitButton('Asignar', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>