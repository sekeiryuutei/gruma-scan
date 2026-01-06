<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "grumascanconteo".
 *
 * @property int $id
 * @property int $idmarcacion
 * @property int $idestado
 * @property string|null $ultimoean
 * @property int $totalregistros
 * @property int $totalunidades
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Grumascanconteodetalle[] $grumascanconteodetalles
 * @property Grumascanestado $idestado0
 * @property Grumascanmarcacion $idmarcacion0
 */
class Grumascanconteo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'grumascanconteo';
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
            [['idmarcacion', 'idestado', 'totalregistros', 'totalunidades'], 'required'],
            [['idmarcacion', 'idestado', 'totalregistros', 'totalunidades', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['ultimoean'], 'string', 'max' => 30],
            [['idmarcacion'], 'exist', 'skipOnError' => true, 'targetClass' => Grumascanmarcacion::class, 'targetAttribute' => ['idmarcacion' => 'id']],
            [['idestado'], 'exist', 'skipOnError' => true, 'targetClass' => Grumascanestado::class, 'targetAttribute' => ['idestado' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idmarcacion' => 'Idmarcacion',
            'idestado' => 'Idestado',
            'ultimoean' => 'Ultimoean',
            'totalregistros' => 'Totalregistros',
            'totalunidades' => 'Totalunidades',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Grumascanconteodetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGrumascanconteodetalles()
    {
        return $this->hasMany(Grumascanconteodetalle::class, ['idgrumascanconteo' => 'id']);
    }

    /**
     * Gets query for [[Idestado0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getestado()
    {
        return $this->hasOne(Grumascanestado::class, ['id' => 'idestado']);
    }

    /**
     * Gets query for [[Idmarcacion0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getmarcacion()
    {
        return $this->hasOne(Grumascanmarcacion::class, ['id' => 'idmarcacion']);
    }
    public function getUsuario()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
