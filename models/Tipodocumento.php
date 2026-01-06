<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tipodocumento".
 *
 * @property int $id
 * @property string|null $codigo
 * @property string|null $nombre
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Tipodocumento extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tipodocumento';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['requierePedido','permite_cantidad_manual'], 'boolean'],
            [['codigo'], 'string', 'max' => 5],
            [['nombre'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo' => 'Codigo',
            'nombre' => 'Nombre',
            'created_at' => 'Fecha Graba',
            'created_by' => 'Usuario Graba',
            'updated_at' => 'Fecha Modifica',
            'updated_by' => 'Usuario Modifica',
        ];
    }

    public static function actualizarRegistro($modelTD)
    {

        $model = Tipodocumento::findOne(['codigo' => $modelTD->codigo]);
        if ($model == null) {
            $model = new Tipodocumento();
            $model->codigo = $modelTD->codigo;
            $model->nombre = $modelTD->nombre;
            $model->save();
        }

        return $model->id;
    }

    public static function getListaData()
    {
        $data = Tipodocumento::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function getListaDataCodigo()
    {
        $data = Tipodocumento::find()
            ->select(['id', 'codigo AS nombre'])
            ->orderBy('codigo')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function getListaDataCodigoAgendamiento()
    {
        $data = Tipodocumento::find()
            ->select(['id', 'codigo AS nombre'])
            ->where(['codigo' => ['2CA', '2CM', '2EA', '2EE']])
            ->orderBy('codigo')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function getListaDataCodigo2()
    {
        $data = Tipodocumento::find()
            ->select(['codigo', 'codigo AS nombre'])
            ->orderBy('codigo')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'codigo', 'nombre');
        return $listadata;
    }

    public static function getListaDataCodigoTraspaso()
    {
        $data = Tipodocumento::find()
            ->select(['id', 'codigo AS nombre'])
            ->where(['codigo' => ['3TA', '3TB', 'TRT', 'TRL', '2TL']])
            ->orderBy('codigo')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }
}
