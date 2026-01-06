<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteodetalle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="grumascanconteodetalle-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'idgrumascanconteo')->textInput() ?>

    <?= $form->field($model, 'iditem')->textInput() ?>

    <?= $form->field($model, 'cantidad')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'created_by')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'updated_by')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
