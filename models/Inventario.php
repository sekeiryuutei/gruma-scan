<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "inventario".
 *
 * @property int $id
 * @property string $codigoBarras
 * @property int $idItem
 * @property string $codigoBodega
 * @property float $existencia
 * @property string $fechaUltimaActualizacion
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $codigoBodega0
 * @property Item $idItem0
 */
class Inventario extends \yii\db\ActiveRecord
{
    public $talla;
    public $color;
    // public $item;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigoBarras', 'idItem', 'codigoBodega', 'existencia', 'fechaUltimaActualizacion', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idItem', 'created_by', 'updated_by'], 'integer'],
            [['existencia'], 'number'],
            [['existencia'], 'compare', 'compareValue' => 0, 'operator' => '>=', 'message' => 'La existencia no puede ser negativa.'],
            [['fechaUltimaActualizacion', 'created_at', 'updated_at', 'talla', 'color'], 'safe'],
            [['codigoBarras'], 'string', 'max' => 50],
            [['codigoBodega'], 'string', 'max' => 5],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
            [['codigoBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['codigoBodega' => 'codigo']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigoBarras' => 'Codigo Barras',
            'idItem' => 'Id Item',
            'codigoBodega' => 'Codigo Bodega',
            'existencia' => 'Existencia',
            'fechaUltimaActualizacion' => 'Fecha Ultima Actualizacion',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[CodigoBodega0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoBodega()
    {
        return $this->hasOne(Bodegas::class, ['codigo' => 'codigoBodega']);
    }

    /**
     * Gets query for [[IdItem0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    public static function getotalExistenciasGruma($codigoBodega = null)
    {
        $query = self::find();

        if ($codigoBodega !== null && trim($codigoBodega) !== '') {
            $query->andWhere(['codigoBodega' => trim($codigoBodega)]);
        }


        return $query->sum('existencia');
    }

    public static function getTotalExistenciasSiesa($codigoBodega = null)
    {
        $codigoBodega == '' && $codigoBodega = null;

        $codigosLocales = self::getCodigosBodegaUnicos();

        // Si no hay códigos, retornar 0 directamente
        if (empty($codigosLocales)) {
            return 0;
        }

        // Crear placeholders para IN (...)
        $placeholders = [];
        foreach ($codigosLocales as $index => $codigo) {
            $placeholders[] = ":id{$index}";
        }

        $sql = "
        SELECT SUM(t400.f400_cant_existencia_1) 
        FROM t400_cm_existencia t400
        INNER JOIN t150_mc_bodegas t150 ON t400.f400_rowid_bodega = t150.f150_rowid
        LEFT JOIN t131_mc_items_barras t131 ON t400.f400_rowid_item_ext = t131.f131_rowid_item_ext
        WHERE (
            (:codigoBodega IS NULL AND f150_id IN (" . implode(',', $placeholders) . ")) 
            OR f150_id = :codigoBodega
        )";

        $command = \Yii::$app->dbSiesa->createCommand($sql);

        // Parámetro principal
        if ($codigoBodega !== null) {
            $command->bindValue(':codigoBodega', $codigoBodega, \PDO::PARAM_INT);
        } else {
            $command->bindValue(':codigoBodega', null, \PDO::PARAM_NULL);
        }

        // Agregar dinámicamente los valores para IN (...)
        foreach ($codigosLocales as $index => $codigo) {
            $command->bindValue(":id{$index}", $codigo, \PDO::PARAM_STR);
        }

        $existencia = $command->queryScalar();

        return $existencia !== false ? $existencia : 0;
    }


    public static function getCodigosBodegaUnicos()
    {
        return self::find()
            ->select('codigoBodega')
            ->distinct()
            ->column();
    }


}
