<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanmarcacion $model */

$this->title = 'Create Grumascanmarcacion';
$this->params['breadcrumbs'][] = ['label' => 'Grumascanmarcacions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanmarcacion-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
