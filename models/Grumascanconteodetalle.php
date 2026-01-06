<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "grumascanconteodetalle".
 *
 * @property int $id
 * @property int $idgrumascanconteo
 * @property int $iditem
 * @property int $cantidad
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Grumascanconteo $idgrumascanconteo0
 * @property Item $iditem0
 */
class Grumascanconteodetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'grumascanconteodetalle';
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
            [['idgrumascanconteo', 'iditem', 'cantidad'], 'required'],
            [['idgrumascanconteo', 'iditem', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['iditem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['iditem' => 'id']],
            [['idgrumascanconteo'], 'exist', 'skipOnError' => true, 'targetClass' => Grumascanconteo::class, 'targetAttribute' => ['idgrumascanconteo' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idgrumascanconteo' => 'Idgrumascanconteo',
            'iditem' => 'Iditem',
            'cantidad' => 'Cantidad',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Idgrumascanconteo0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdgrumascanconteo0()
    {
        return $this->hasOne(Grumascanconteo::class, ['id' => 'idgrumascanconteo']);
    }

    /**
     * Gets query for [[Iditem0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getitem()
    {
        return $this->hasOne(Item::class, ['id' => 'iditem']);
    }
}
