<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanestado $model */

$this->title = 'Update Grumascanestado: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Grumascanestados', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="grumascanestado-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
