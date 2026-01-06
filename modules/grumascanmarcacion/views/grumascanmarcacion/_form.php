<?php

use app\models\Bodegas;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Grumascanmarcacion $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="grumascanmarcacion-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">

        <div class="col-md-3">
            <?= $form->field($model, 'idbodega')->dropDownList(
                Bodegas::getListaData(),
                [
                    'prompt' => 'Bodega...',
                    'required' => true
                ]
            ) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'ubicacion')->textInput(['maxlength' => true]) ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($model, 'seccion')->textInput(['maxlength' => true]) ?>
        </div>
    </div>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>