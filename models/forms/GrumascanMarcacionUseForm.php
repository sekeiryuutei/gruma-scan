<?php

namespace app\models\forms;

use yii\base\Model;

class GrumascanMarcacionUseForm extends Model
{
    public $codigo;     // id escaneado (barcode)
    public $idbodega;
    public $ubicacion;
    public $seccion;

    public function rules()
    {
        return [
            [['codigo', 'idbodega'], 'required'],
            [['codigo', 'idbodega'], 'integer'],
            [['ubicacion', 'seccion'], 'string', 'max' => 100],
        ];
    }
}
