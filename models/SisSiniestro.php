<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sis_siniestro".
 *
 * @property int $id
 * @property int $idclinica
 * @property string $fecha
 * @property string $hora
 * @property int $idbaremo
 * @property int $atendido
 * @property string|null $fecha_atencion
 * @property string|null $hora_atencion
 * @property int $iduser
 * @property string|null $descripcion
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property Baremo $idbaremo0
 * @property RmClinica $idclinica0
 * @property SisConsulta[] $sisConsultas
 */
class SisSiniestro extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sis_siniestro';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fecha_atencion', 'hora_atencion', 'descripcion', 'updated_at', 'deleted_at'], 'default', 'value' => null],
            [['atendido'], 'default', 'value' => 0],
            [['idclinica', 'fecha', 'hora', 'idbaremo', 'iduser', 'descripcion'], 'required'],
            [['costo_total'], 'number'],
            [['idclinica', 'atendido', 'iduser'], 'default', 'value' => null],
            [['idbaremo'], 'default', 'value' => ''],
            [['idclinica', 'atendido', 'iduser'], 'integer'],
            [['idbaremo'], 'safe'], // Aceptamos cualquier valor y lo manejamos en beforeValidate
            [['fecha', 'fecha_atencion', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['descripcion'], 'string'],
            [['hora', 'hora_atencion'], 'string', 'max' => 10],
            [['idclinica'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['idclinica' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValidate()
    {
        // Si es un array, validar cada elemento y convertirlo a string
        if (is_array($this->idbaremo)) {
            // Filtrar valores vacíos
            $this->idbaremo = array_filter($this->idbaremo);
            // Si no hay valores, establecer como string vacío
            $this->idbaremo = !empty($this->idbaremo) ? implode(',', $this->idbaremo) : '';
        } 
        // Si es string vacío, asegurarse de que sea un string vacío
        elseif (empty($this->idbaremo)) {
            $this->idbaremo = '';
        }
        
        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idclinica' => 'Idclinica',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'idbaremo' => 'Baremo',
            'atendido' => 'Atendido',
            'fecha_atencion' => 'Fecha Atencion',
            'hora_atencion' => 'Hora Atencion',
            'iduser' => 'Iduser',
            'descripcion' => 'Descripcion',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Gets query for [[SisSiniestroBaremos]] (relación con la tabla intermedia)
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisSiniestroBaremos()
    {
        return $this->hasMany(SisSiniestroBaremo::class, ['siniestro_id' => 'id']);
    }
    
    /**
     * Gets query for [[Baremos]] (relación con múltiples baremos)
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaremos()
    {
        return $this->hasMany(Baremo::class, ['id' => 'baremo_id'])
            ->viaTable('sis_siniestro_baremo', ['siniestro_id' => 'id']);
    }
    
    /**
     * Obtiene los IDs de los baremos como array
     * @return array
     */
    public function getBaremoIds()
    {
        return !empty($this->idbaremo) ? explode(',', $this->idbaremo) : [];
    }
    
    /**
     * @deprecated Mantenido por compatibilidad
     */
    public function getIdbaremo0()
    {
        $id = !empty($this->idbaremo) ? explode(',', $this->idbaremo)[0] : null;
        return $this->hasOne(Baremo::class, ['id' => 'id'])->where(['id' => $id]);
    }

    /**
     * Gets query for [[Idclinica0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'idclinica']);
    }

    /**
     * Gets query for [[SisConsultas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSisConsultas()
    {
        return $this->hasMany(SisConsulta::class, ['idsiniestro' => 'id']);
    }

    public function getAfiliado()
    {
        return $this->hasOne(UserDatos::class, ['id' => 'user_id']);
    }
    
    /**
     * Guarda la relación con los baremos
     * @param array $baremoIds Array de IDs de baremos a guardar
     * @return bool
     */
    public function saveBaremos($baremoIds)
    {
        if (!is_array($baremoIds)) {
            $baremoIds = [];
        }
        
        // Eliminar las relaciones existentes
        SisSiniestroBaremo::deleteAll(['siniestro_id' => $this->id]);
        
        // Agregar las nuevas relaciones
        foreach ($baremoIds as $baremoId) {

            $baremocosto = Baremo::find()->where(['id' => $baremoId])->one()->precio;

            if (!empty($baremoId)) {
                $relacion = new SisSiniestroBaremo([
                    'siniestro_id' => $this->id,
                    'baremo_id' => $baremoId,
                    'costo' => $baremocosto
                ]);
                
                if (!$relacion->save()) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
