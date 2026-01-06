<?php

namespace app\models\forms;

use yii\base\Model;

class GrumascanMarcacionPrintForm extends Model
{
    public $printer_id;
    public $cantidad;
    public $idbodega;
    public $ubicacion;
    public $seccion;

    public function rules()
    {
        return [
            [['printer_id', 'cantidad'], 'required'],
            [['printer_id', 'cantidad', 'idbodega'], 'integer'],
            [['cantidad'], 'integer', 'min' => 1, 'max' => 500], // ajusta límite
            [['ubicacion', 'seccion'], 'string', 'max' => 50],
            [['idbodega', 'ubicacion', 'seccion'], 'default', 'value' => null],
        ];
    }
}
