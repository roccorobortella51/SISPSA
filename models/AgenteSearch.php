<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Agente;

use app\components\UserHelper;
use yii\db\Expression; // Importamos Expression para la búsqueda de nombre completo


/**
 * AgenteSearch represents the model behind the search form of `app\models\Agente`.
 */
class AgenteSearch extends Agente
{
    public $rif;
    public $propietarioEmail;  
    public $propietarioCedula;
    public $propietarioNombreCompleto; // 👈 Nuevo atributo para buscar por nombre completo

    /**
     * {@inheritdoc}
     */
public function rules()
    {
        return [
            [['id', 'idusuariopropietario'], 'integer'],
            // 👈 Agregamos 'propietarioNombreCompleto' a las reglas 'safe'
            [['nom', 'rif', 'created_at', 'updated_at', 'deleted_at', 'propietarioEmail', 'propietarioCedula', 'propietarioNombreCompleto'], 'safe'],
            [['por_venta', 'por_asesor', 'por_cobranza', 'por_post_venta', 'por_agente', 'por_max'], 'number'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $rol = UserHelper::getMyRol();
        $filtro_gente = ($rol == 'Agente'); 

        // 1. Asignamos alias 't' a la tabla principal 'agente'
        $query = Agente::find()->alias('t');

        // 2. CORRECCIÓN: Usamos un alias explícito ('propietario userDatos') para la tabla 'user_datos' 
        // y luego el join anidado 'propietario.user' para asegurar que las columnas en el SELECT 
        // ('userDatos.cedula' y 'user.email') sean reconocidas.
        // Aseguramos que 'propietario' (UserDatos) y su relación anidada 'propietario.user' (User) estén unidas.
        $query->joinWith(['propietario userDatos', 'propietario.user', 'agenteFuerzas']); 

        // INICIO DE LA OPTIMIZACIÓN (Proyección de Columnas)
        $query->select([
            // Columnas del Agente (t)
            't.id',
            't.nom',
            // 't.rif', 
            't.idusuariopropietario', // Necesario para la relación 'propietario'

            // Columnas de las relaciones:
            'user.email as propietarioEmail',     
            'userDatos.cedula as propietarioCedula', 
            // 👈 Añadimos los nombres y apellidos al SELECT para que el GridView pueda acceder a ellos
            'userDatos.nombres',
            'userDatos.apellidos',
        ]);
        //  FIN DE LA OPTIMIZACIÓN

        if($filtro_gente){
            // Aplicamos el alias 't' a la condición
            $query->andFilterWhere(['t.idusuariopropietario' => UserHelper::getUserDatosId()]); 
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => array_merge(parent::attributeLabels(), [
                    'propietarioEmail' => [
                        'asc' => ['user.email' => SORT_ASC],        
                        'desc' => ['user.email' => SORT_DESC],
                        'label' => 'Correo del Propietario',
                    ],
                    'propietarioCedula' => [
                        'asc' => ['userDatos.cedula' => SORT_ASC], 
                        'desc' => ['userDatos.cedula' => SORT_DESC],
                        'label' => 'Cédula del Propietario',
                    ],
                    // 👈 Habilitamos el ordenamiento por la columna 'propietario'
                    'propietario' => [
                        'asc' => ['userDatos.nombres' => SORT_ASC, 'userDatos.apellidos' => SORT_ASC],
                        'desc' => ['userDatos.nombres' => SORT_DESC, 'userDatos.apellidos' => SORT_DESC],
                        'label' => 'Propietario',
                    ],
                ]),
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            't.id' => $this->id, // Usamos el alias 't'
            't.idusuariopropietario' => $this->idusuariopropietario, // Usamos el alias 't'
            'por_venta' => $this->por_venta,
            'por_asesor' => $this->por_asesor,
            'por_cobranza' => $this->por_cobranza,
            'por_post_venta' => $this->por_post_venta,
            'por_agente' => $this->por_agente,
            'por_max' => $this->por_max,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ]);

        $query->andFilterWhere(['ilike', 't.nom', $this->nom]); // Usamos el alias 't'
        
        $query->andFilterWhere(['ilike', 't.rif', $this->rif]); 
        
        // Aplicamos CAST a userDatos.cedula para permitir la búsqueda ILIKE en una columna INTEGER
        $query->andFilterWhere(['ilike', 'user.email', $this->propietarioEmail])
              ->andFilterWhere(['ilike', 'CAST("userDatos"."cedula" AS TEXT)', $this->propietarioCedula]);

        // 👈 LÓGICA DE FILTRADO POR NOMBRE COMPLETO (Columna 'propietario')
        if (!empty($this->propietarioNombreCompleto)) {
            $search = '%' . strtolower($this->propietarioNombreCompleto) . '%';
            
            // Construimos la expresión de concatenación para PostgreSQL
            $query->andWhere(new Expression("LOWER(\"userDatos\".nombres || ' ' || \"userDatos\".apellidos) LIKE :search", [':search' => $search]));
        }

        return $dataProvider;
    }
}