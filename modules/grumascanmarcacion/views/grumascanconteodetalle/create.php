<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Grumascanconteodetalle $model */

$this->title = 'Create Grumascanconteodetalle';
$this->params['breadcrumbs'][] = ['label' => 'Grumascanconteodetalles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanconteodetalle-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
