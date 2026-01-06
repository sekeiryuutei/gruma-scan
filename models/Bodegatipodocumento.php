<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "bodegatipodocumento".
 *
 * @property int $id
 * @property int $idBodega
 * @property int $idTipoDocumento
 */
class Bodegatipodocumento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bodegatipodocumento';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idBodega', 'idTipoDocumento'], 'required'],
            [['idBodega', 'idTipoDocumento'], 'integer'],
        ];
    }

    public function getTipodocumento()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumento']);
    }

    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodega']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idBodega' => 'Id Bodega',
            'idTipoDocumento' => 'Id Tipo Documento',
        ];
    }

    public static function getListaData()
    {
        $data = Bodegatipodocumento::find()
            ->alias('btd')
            ->select(['btd.id', "(bo.codigo + ' - ' + LTRIM(RTRIM(bo.nombre)) + ' - ' + td.codigo) AS nombre"])
            ->join('INNER JOIN', 'tipodocumento td', 'btd.idTipoDocumento = td.id')
            ->join('INNER JOIN', 'bodegas bo', 'btd.idBodega = bo.id')
            ->orderBy('bo.codigo')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

}
