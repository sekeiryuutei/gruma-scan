<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteo $model */

$this->title = 'Update Grumascanconteo: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Grumascanconteos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="grumascanconteo-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
