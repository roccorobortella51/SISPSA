<?php
// app/models/AfiliadosReportSearch.php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AfiliadosReportSearch represents the model behind the report form.
 */
class AfiliadosReportSearch extends Model
{
    public $clinica_id;
    public $user_datos_type_id;
    // REMOVED: public $estatus_solvente;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['clinica_id', 'user_datos_type_id'], 'integer'],
            // REMOVED: [['estatus_solvente'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'clinica_id' => 'Clínica',
            'user_datos_type_id' => 'Tipo de Afiliado',
            // REMOVED: 'estatus_solvente' => 'Estatus Solvente',
        ];
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
        $query = UserDatos::find()
            ->alias('ud')
            ->innerJoinWith(['clinica'])
            ->leftJoin('user_datos_type udt', 'ud.user_datos_type_id = udt.id')
            ->where(['ud.role' => 'afiliado'])  // Only affiliates
            ->andWhere(['IS', 'ud.deleted_at', null]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        // Apply default order
        $query->orderBy(['rm_clinica.nombre' => SORT_ASC, 'ud.nombres' => SORT_ASC, 'ud.apellidos' => SORT_ASC]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Apply filters (REMOVED estatus_solvente filter)
        if (!empty($this->clinica_id)) {
            $query->andWhere(['ud.clinica_id' => $this->clinica_id]);
        }

        if (!empty($this->user_datos_type_id)) {
            $query->andWhere(['ud.user_datos_type_id' => $this->user_datos_type_id]);
        }

        // REMOVED: estatus_solvente filter

        return $dataProvider;
    }

    /**
     * Get all affiliates for export (without pagination)
     */
    public function getAllAffiliates($params)
    {
        $this->load($params);

        $query = UserDatos::find()
            ->alias('ud')
            ->innerJoinWith(['clinica'])
            ->leftJoin('user_datos_type udt', 'ud.user_datos_type_id = udt.id')
            ->where(['ud.role' => 'afiliado'])  // Only affiliates
            ->andWhere(['IS', 'ud.deleted_at', null])
            ->orderBy(['rm_clinica.nombre' => SORT_ASC, 'ud.nombres' => SORT_ASC, 'ud.apellidos' => SORT_ASC]);

        // Apply filters (REMOVED estatus_solvente filter)
        if (!empty($this->clinica_id)) {
            $query->andWhere(['ud.clinica_id' => $this->clinica_id]);
        }

        if (!empty($this->user_datos_type_id)) {
            $query->andWhere(['ud.user_datos_type_id' => $this->user_datos_type_id]);
        }

        // REMOVED: estatus_solvente filter

        return $query->all();
    }
}
