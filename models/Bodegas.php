<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "Bodegas".
 *
 * @property int $int
 * @property string $codigo
 * @property string|null $nombre
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Bodegas extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bodegas';
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

    public function getTipodocumento()
    {
        return $this->hasOne(Bodegatipodocumento::class, ['idBodega' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo', 'nombre'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by', 'cedi'], 'integer'],
            [['codigo'], 'string', 'max' => 5],
            [['nombre'], 'string', 'max' => 50],
            ['nombre', 'unique', 'message' => 'Nombre Bodega ya está registrado.'],
            ['codigo', 'unique', 'message' => 'Código Bodega ya está registrado.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo' => 'Código',
            'nombre' => 'Nombre',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'cedi' => 'Es CEDI',
        ];
    }
    public static function getListaDataCodigo()
    {
        $data = Bodegas::find()
            ->select(['codigo', "(codigo + ' - ' + nombre) AS nombre"])
            ->asArray()->all();
        $listadata = ArrayHelper::map($data, 'codigo' , 'nombre');
        return $listadata;
    }
    public static function getListaData()
    {
        $data = Bodegas::find()
            ->select(['id', "(codigo + ' - ' + nombre) AS nombre"])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

   /* public static function getListaDataCEDI()
    {
        $data = Bodegas::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')->asArray()->all()
            ->where(['cedi' => 1]);
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }*/

    public static function getListaDataId($allowedCodes = [])
    {
        $query = Bodegas::find()->select(['id', 'nombre'])->orderBy('nombre');

        if (!empty($allowedCodes)) {
            $query->andWhere(['IN', 'codigo', $allowedCodes]);
        }

        $data = $query->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');

        return $listadata;
    }

    public function getImpresorapaxar()
    {
        return $this->hasOne(Impresoraspaxarbodega::class, ['bodega_id' => 'id']);
    }
}
