<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteo $model */

$this->title = 'Create Grumascanconteo';
$this->params['breadcrumbs'][] = ['label' => 'Grumascanconteos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanconteo-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
