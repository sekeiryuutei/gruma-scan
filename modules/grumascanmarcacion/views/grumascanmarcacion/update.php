<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanmarcacion $model */

$this->title = 'Update Grumascanmarcacion: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Grumascanmarcacions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="grumascanmarcacion-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
