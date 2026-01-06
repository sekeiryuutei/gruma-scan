<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->registerCss('

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

');

/** @var yii\web\View $this */
/** @var app\models\search\GrumascanconteoSearch $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="grumascanconteo-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-2">
            <?= $form->field($model, 'id') ?>
        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'idmarcacion') ?>
        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'idestado') ?>
        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'ultimoean') ?>
        </div>
        <div class="col-lg-2">
            <?= $form->field($model, 'totalregistros') ?>
        </div>
        <div class="col-lg-2">
            <?php echo $form->field($model, 'totalunidades') ?>
        </div>
    </div>

    <?php // echo $form->field($model, 'created_at') 
    ?>

    <?php // echo $form->field($model, 'created_by') 
    ?>

    <?php // echo $form->field($model, 'updated_at') 
    ?>

    <?php // echo $form->field($model, 'updated_by') 
    ?>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        <!-- <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary btn-lg btn-create']) ?> -->
    </div>

    <?php ActiveForm::end(); ?>

</div>