<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteo $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="grumascanconteo-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idmarcacion')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
