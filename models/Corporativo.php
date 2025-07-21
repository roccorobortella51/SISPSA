<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\RmClinica;
use app\models\User;
use app\models\CorporativoClinica;
use app\models\CorporativoUser;
use app\models\RmEstado;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;

/**
 * This is the model class for table "corporativos".
 *
 * @property int $id
 * @property string $nombre
 * @property string|null $email
 * @property string|null $telefono
 * @property string|null $rif
 * @property string|null $estado
 * @property string|null $municipio
 * @property string|null $parroquia
 * @property string|null $ciudad
 * @property string|null $direccion
 * @property string|null $codigo_asesor
 * @property string|null $lugar_registro
 * @property string|null $fecha_registro_mercantil
 * @property string|null $tomo_registro
 * @property string|null $folio_registro
 * @property string|null $domicilio_fiscal
 * @property string|null $contacto_nombre
 * @property string|null $contacto_cedula
 * @property string|null $contacto_telefono
 * @property string|null $contacto_cargo
 * @property string $estatus
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property CorporativoClinica[] $corporativoClinicas
 * @property RmClinica[] $clinicas
 * @property CorporativoUser[] $corporativoUsers
 * @property User[] $users
 *
 * @property array $clinicas_ids
 * @property array $users_ids
 */
class Corporativo extends \yii\db\ActiveRecord
{
    public $clinicas_ids;
    public $users_ids;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'corporativos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Campos requeridos
            [['nombre', 'estatus', 'rif', 'telefono', 'email', 'lugar_registro', 'fecha_registro_mercantil', 'tomo_registro', 'folio_registro', 'direccion', 
            'domicilio_fiscal', 'contacto_nombre', 'contacto_cedula', 'contacto_telefono', 'estado', 'municipio', 'parroquia', 'ciudad'], 'required'],


            [['direccion', 'domicilio_fiscal'], 'string'],
            

            [['fecha_registro_mercantil', 'created_at', 'updated_at', 'deleted_at', 'ciudad'], 'safe'],

            [['nombre', 'email', 'lugar_registro', 'contacto_nombre'], 'string', 'max' => 255],
            [['rif'], 'string', 'max' => 12],

            // validacion del codigo del telefono 
            [['telefono', 'contacto_telefono'], 'string', 'max' => 12], // La longitud máxima de (9999) 999-9999 es 14, pero 15 por si acaso
            [['telefono', 'contacto_telefono'], 'match',
                'pattern' => '/^(0416|0426|0414|0424|0412|0212|0261|0241|0243|0251|0274|0276|0286|0291|0293)\d{7}$/',
                'message' => 'El número de teléfono debe ser venezolano y tener el formato correcto (ej. 04121234567).'],
        
            // Validaciones de cedula
            [['contacto_cedula'], 'string', 'max' => 11, 'message' => 'El formato de la cédula es incorrecto (máx. 11 caracteres).'],

            
            [['estado', 'municipio', 'parroquia', 'contacto_cargo', 'ciudad'], 'string', 'max' => 100],

            [['codigo_asesor', 'tomo_registro', 'folio_registro', 'estatus'], 'string', 'max' => 50],
            [['nombre'], 'unique'],

            // Validaciones de correo
            [['email'], 'email'],
            [['email'], 'unique','message' => 'Este correo electrónico ya está registrado.'],

            [['rif'], 'unique'],
            [['estatus'], 'default', 'value' => 'Activo'],
            // Las IDs para las relaciones Many-to-Many son INTEGER
            [['clinicas_ids', 'users_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre Corporativo',
            'email' => 'Email',
            'telefono' => 'Teléfono',
            'rif' => 'Rif',
            'estado' => 'Estado',
            'municipio' => 'Municipio',
            'parroquia' => 'Parroquia',
            'ciudad' => 'Ciudad',
            'direccion' => 'Dirección',
            'codigo_asesor' => 'Código Asesor',
            'lugar_registro' => 'Lugar de Registro',
            'fecha_registro_mercantil' => 'Fecha de Registro Mercantil',
            'tomo_registro' => 'Tomo de Registro',
            'folio_registro' => 'Folio de Registro',
            'domicilio_fiscal' => 'Domicilio Fiscal',
            'contacto_nombre' => 'Nombre Contacto',
            'contacto_cedula' => 'Cédula Contacto',
            'contacto_telefono' => 'Teléfono Contacto',
            'contacto_cargo' => 'Cargo Contacto',
            'estatus' => 'Estatus',
            'created_at' => 'Creado El',
            'updated_at' => 'Actualizado El',
            'deleted_at' => 'Eliminado El',
            'clinicas_ids' => 'Clínicas Asociadas',
            'users_ids' => 'Empleados Asociados',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporativoClinicas()
    {
        return $this->hasMany(CorporativoClinica::class, ['corporativo_id' => 'id']);
    }

    /**
     * Obtiene las clínicas asociadas a este corporativo a través de la tabla intermedia.
     * @return \yii\db\ActiveQuery
     */
    public function getClinicas()
    {
        return $this->hasMany(RmClinica::class, ['id' => 'clinica_id'])
            ->viaTable('corporativo_clinica', ['corporativo_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporativoUsers()
    {
        return $this->hasMany(CorporativoUser::class, ['corporativo_id' => 'id']);
    }

    /**
     * Obtiene los usuarios (empleados) asociados a este corporativo a través de la tabla intermedia.
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('corporativo_user', ['corporativo_id' => 'id']);
    }

    // Métodos para cargar y guardar las relaciones Many-to-Many
    public function afterFind()
    {
        parent::afterFind();
        $this->clinicas_ids = ArrayHelper::getColumn($this->clinicas, 'id');
        $this->users_ids = ArrayHelper::getColumn($this->users, 'id');
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->saveClinicasRelations();
        $this->saveUsersRelations();
    }

    private function saveClinicasRelations()
    {
        CorporativoClinica::deleteAll(['corporativo_id' => $this->id]);
        if (is_array($this->clinicas_ids) && !empty($this->clinicas_ids)) {
            $batch = [];
            foreach ($this->clinicas_ids as $clinicaId) {
                $batch[] = [$this->id, $clinicaId, new \yii\db\Expression('NOW()')];
            }
            Yii::$app->db->createCommand()
                ->batchInsert(
                    CorporativoClinica::tableName(),
                    ['corporativo_id', 'clinica_id', 'created_at'],
                    $batch
                )->execute();
        }
    }

    private function saveUsersRelations()
    {
        CorporativoUser::deleteAll(['corporativo_id' => $this->id]);
        if (is_array($this->users_ids) && !empty($this->users_ids)) {
            $batch = [];
            foreach ($this->users_ids as $userId) {
                $batch[] = [$this->id, $userId, new \yii\db\Expression('NOW()'), null];
            }
            Yii::$app->db->createCommand()
                ->batchInsert(
                    CorporativoUser::tableName(),
                    ['corporativo_id', 'user_id', 'fecha_vinculacion', 'rol_en_corporativo'],
                    $batch
                )->execute();
        }
    }

    // Relaciones para obtener el objeto de la ubicación (RmEstado, RmMunicipio, etc.)
    // Estas buscarán por el NOMBRE de la columna en el modelo de ubicación,
    // ya que el modelo Corporativo guarda el ID numérico como una cadena de texto.
    public function getRmEstado()
    {
        return $this->hasOne(RmEstado::class, ['id' => 'estado']); // Busca por ID, no por nombre
    }

    public function getRmMunicipio()
    {
        return $this->hasOne(RmMunicipio::class, ['codigo_muni' => 'municipio']); // Busca por codigo_muni, no por nombre
    }

    public function getRmParroquia()
    {
        return $this->hasOne(RmParroquia::class, ['id' => 'parroquia']); // Busca por ID, no por nombre
    }

    public function getRmCiudad()
    {
        return $this->hasOne(RmCiudad::class, ['id' => 'ciudad']); // Busca por ID, no por nombre
    }
}