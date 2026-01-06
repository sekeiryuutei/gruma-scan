<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Grumascanconteodetalle;
use Yii;
use yii\data\SqlDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * GrumascanconteodetalleSearch represents the model behind the search form of `app\models\Grumascanconteodetalle`.
 */
class GrumascanconteodetalleSearch extends Grumascanconteodetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idgrumascanconteo', 'iditem', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Grumascanconteodetalle::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'idgrumascanconteo' => $this->idgrumascanconteo,
            'iditem' => $this->iditem,
            'cantidad' => $this->cantidad,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }
    public function searchAgrupado($params, int $idgrumascanconteo)
    {
        $this->load($params);

        $id = (int)$idgrumascanconteo;

        $q = (new \yii\db\Query())
            ->from(['gscd' => 'grumascanconteodetalle'])
            ->innerJoin(['it' => 'item'], 'gscd.idItem = it.id')
            ->leftJoin(['ue' => 'unidadEmpaque'], 'ue.codigo = it.unidadEmpaque')
            ->leftJoin(['c' => 'color'], 'c.id = it.idColor')
            ->leftJoin(['t' => 'talla'], 't.id = it.idTalla')
            ->where(['gscd.idgrumascanconteo' => $id])
            ->select([
                // SKU lógico
                'item'   => 'it.item',
                'idcolor' => 'it.idColor',
                'idtalla' => 'it.idTalla',

                // display
                'color_nombre' => new \yii\db\Expression("COALESCE(c.nombre, 'NA')"),
                'talla_nombre' => new \yii\db\Expression("COALESCE(t.nombre, 'NA')"),

                // agregados
                'cantidad' => new \yii\db\Expression('SUM(gscd.cantidad)'),

                'total_unidades' => new \yii\db\Expression("
                SUM(
                    CASE
                        WHEN ue.equivalencia IS NOT NULL THEN gscd.cantidad * ue.equivalencia
                        ELSE gscd.cantidad
                    END
                )
            "),

                // ✅ EANs concatenados (SIN usar gscd.idgrumascanconteo para evitar error GROUP BY)
                'eans' => new \yii\db\Expression("
                STUFF((
                    SELECT DISTINCT ', ' + it2.codigoBarras
                    FROM item it2
                    INNER JOIN grumascanconteodetalle g2 ON g2.idItem = it2.id
                    WHERE g2.idgrumascanconteo = {$id}
                      AND it2.item = it.item
                      AND ISNULL(it2.idColor, 0) = ISNULL(it.idColor, 0)
                      AND ISNULL(it2.idTalla, 0) = ISNULL(it.idTalla, 0)
                    FOR XML PATH(''), TYPE
                ).value('.', 'NVARCHAR(MAX)'), 1, 2, '')
            "),
            ])
            ->groupBy([
                'it.item',
                'it.idColor',
                'it.idTalla',
                new \yii\db\Expression("COALESCE(c.nombre, 'NA')"),
                new \yii\db\Expression("COALESCE(t.nombre, 'NA')"),
            ]);

        // filtros opcionales
        if (!empty($this->item)) {
            $q->andWhere(['like', 'it.item', $this->item]);
        }
        if (!empty($this->color)) {
            $q->andWhere(['like', 'c.nombre', $this->color]);
        }
        if (!empty($this->talla)) {
            $q->andWhere(['like', 't.nombre', $this->talla]);
        }

        // count correcto
        $countQ = (new \yii\db\Query())->from(['x' => $q]);
        $totalCount = (int)$countQ->count('*', \Yii::$app->db);

        $sql = $q->createCommand(\Yii::$app->db)->getRawSql();

        return new \yii\data\SqlDataProvider([
            'sql' => $sql,
            'totalCount' => $totalCount,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => [
                    'item',
                    'color_nombre',
                    'talla_nombre',
                    'cantidad',
                    'total_unidades',
                ],
                'defaultOrder' => ['item' => SORT_ASC],
            ],
        ]);
    }
}
