<?php

namespace app\models;

use common\models\user;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "impresoraspaxarbodega".
 *
 * @property int $id
 * @property int $bodega_id
 * @property string $tipo
 * @property string $ip
 * @property int|null $puerto
 * @property string|null $recurso
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Bodegas $bodega
 * @property User $updatedBy
 */
class Impresoraspaxarbodega extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'impresoraspaxarbodega';
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
            [['bodega_id', 'tipo', 'ip'], 'required'],
            [['bodega_id', 'puerto', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['tipo'], 'string', 'max' => 50],
            [['ip'], 'string', 'max' => 15],
            [['recurso'], 'string', 'max' => 255],
            [['bodega_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['bodega_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bodega_id' => 'Bodega ID',
            'tipo' => 'Tipo',
            'ip' => 'Ip',
            'puerto' => 'Puerto',
            'recurso' => 'Recurso',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Bodega]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'bodega_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public function getCreatedby()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public static  function  getListaData(){
        $data = Impresoraspaxarbodega::find()
            ->select([
                'imp.id', // ID del registro Usertraspaso
                "CONCAT(bod.nombre, ' - ', imp.recurso, ' - ', imp.tipo) AS nombre"
            ])
            ->alias('imp')
            ->innerJoin('bodegas bod', 'imp.bodega_id = bod.id')
            ->orderBy('bod.nombre')
            ->asArray()
            ->all();

        // Mapear los resultados para crear un array usable en formularios
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function imprimirxip ($impresora, $contenido){
        $socket = fsockopen($impresora->ip, $impresora->puerto, $error_code, $error_message, 10);
        if ($socket) {
            fwrite($socket, $contenido);
            fclose($socket);

            return ['mensaje' => 'Operación exitosa', 'error_code' => 0, 'error_message' => '', 'ok' => true];
        } else {
            return ['mensaje' => 'Operación Con Error', 'error_code' => $error_code, 'error_message' => $error_message, 'ok' => false];
        }
    }

    public static function imprimirxrecurso ($impresora, $contenido){

        $tempDir = Yii::getAlias('@app') . '/temp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true); // Crear con permisos recursivos
        }

        // Crear el archivo temporal dentro de ./app/temp
        $tempFile = $tempDir . '/zpl_' . uniqid() . '.tmp';
        file_put_contents($tempFile, $contenido);

            // Construir el comando con la IP primero y luego el recurso compartido
        $command = sprintf(
                'print %s /D:%s "%s"',
                $impresora->ip, // La dirección IP (sin escapar, ya es válida)
                '\\\\' . str_replace('\\', '\\\\', ltrim($impresora->recurso, '\\')), // Recurso compartido (doble \\ inicial)
                $tempFile // El archivo temporal, ahora entre comillas
        );
        // Ejecutar el comando
        exec($command, $output, $returnVar);

        unlink($tempFile);

        if ($returnVar !== 0) {
            $errorMessage = "Error al imprimir en $impresora->recurso: " . implode("\n", $output);
            return ['mensaje' => 'Operación Con Error', 'error_code' => $returnVar, 'error_message' => $errorMessage, 'ok' => false];
        }else{
            return ['mensaje' => "Impresión enviada a $impresora->recurso, correctamente!", 'error_code' => 0, 'error_message' => '', 'ok' => true];
        }
    }

        public static  function  getListaDataTermica(){
        $data = Impresoraspaxarbodega::find()
            ->select([
                'imp.id', // ID del registro Usertraspaso
                "CONCAT(bod.nombre, ' - ', imp.recurso, ' - ', imp.tipo) AS nombre"
            ])
            ->where(['tipo'=> 'termica'])
            ->alias('imp')
            ->innerJoin('bodegas bod', 'imp.bodega_id = bod.id')
            ->orderBy('bod.nombre')
            ->asArray()
            ->all();

        // Mapear los resultados para crear un array usable en formularios
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }
}
