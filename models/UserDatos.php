<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_datos".
 *
 * @property int $id
 * @property string $created_at
 * @property string $user_id
 * @property string|null $nombres
 * @property string|null $fechanac
 * @property string|null $sexo
 * @property string|null $selfie
 * @property string|null $telefono
 * @property string|null $estado
 * @property string|null $role
 * @property string|null $estatus
 * @property string|null $imagen_identificacion
 * @property string|null $qr
 * @property float|null $paso
 * @property string|null $video
 * @property string|null $ciudad
 * @property string|null $municipio
 * @property string|null $parroquia
 * @property string|null $direccion
 * @property string|null $codigoValidacion
 * @property int|null $clinica_id
 * @property int|null $plan_id
 * @property string|null $apellidos
 * @property string|null $email
 * @property int|null $contrato_id
 * @property int|null $asesor_id
 * @property string|null $deleted_at
 * @property string|null $updated_at
 * @property string|null $ver_cedula
 * @property string|null $ver_foto
 * @property string|null $session_id
 * @property int|null $cedula
 * @property string|null $tipo_cedula
 * @property string|null $tipo_sangre
 * @property string|null $estatus_solvente
 * @property int|null $user_login_id
 * @property int|null $user_datos_type_id
 * @property string|null $nacionalidad
 * @property string|null $estado_civil
 * @property string|null $lugar_nacimiento
 * @property string|null $profesion
 * @property string|null $ocupacion
 * @property string|null $actividad_economica
 * @property string|null $ramo_comercial
 * @property string|null $descripcion_actividad
 * @property string|null $ingreso_anual
 * @property string|null $direccion_residencia
 * @property string|null $direccion_oficina
 * @property string|null $telefono_residencia
 * @property string|null $telefono_oficina
 * @property string|null $telefono_celular
 * @property string|null $razon_social
 * @property string|null $rif
 * @property string|null $registro_mercantil
 * @property string|null $tomo
 * @property string|null $fecha_registro
 * @property string|null $actividad_economica_corp
 * @property string|null $direccion_corporativa
 * @property string|null $telefono_corporativo
 * @property string|null $productos_servicios
 * @property string|null $utilidad
 * @property string|null $patrimonio
 * @property string|null $plan_seleccionado
 * @property string|null $moneda
 * @property string|null $deducible
 * @property string|null $limite_cobertura
 * @property bool|null $cobertura_maternidad
 * @property string|null $deducible_maternidad
 * @property string|null $limite_cobertura_maternidadf
 * @property string|null $grupo_familiar
 * @property string|null $nombre_beneficiario
 * @property string|null $cedula_beneficiario
 * @property string|null $parentesco_beneficiario
 * @property string|null $sexo_beneficiario
 * @property string|null $fecha_nacimiento_beneficiario
 * @property string|null $nombre_titular
 * @property string|null $cedula_titular
 * @property string|null $numero_cuenta
 * @property int|null $banco_id
 * @property string|null $tipo_cuenta
 * @property string|null $nombre_declaracion_afiliado
 * @property string|null $cedula_declaracion_afiliado
 * @property string|null $nombre_declaracion_contratante
 * @property string|null $cedula_declaracion_contratante
 * @property string|null $tipo_afiliacion
 * @property string|null $nombre_contratante
 * @property string|null $apellido_contratante
 * @property string|null $tipo_cedula_contratante
 * @property int|null $cedula_contratante
 * @property string|null $fecha_nacimiento_contratante
 * @property string|null $sexo_contratante
 * @property string|null $nacionalidad_contratante
 * @property string|null $estado_civil_contratante
 * @property string|null $lugar_nacimiento_contratante
 * @property string|null $profesion_contratante
 * @property string|null $ocupacion_contratante
 * @property string|null $actividad_economica_contratante
 * @property string|null $descripcion_actividad_contratante
 * @property string|null $ingreso_anual_contratante
 * @property string|null $direccion_residencia_contratante
 * @property string|null $direccion_oficina_contratante
 * @property string|null $direccion_cobro_contratante
 * @property string|null $telefono_residencia_contratante
 * @property string|null $telefono_oficina_contratante
 * @property string|null $telefono_celular_contratante
 * @property string|null $email_contratante
 * @property string|null $nombre_representante_contratante
 * @property string|null $apellido_representante_contratante
 * @property string|null $tipo_cedula_representante_contratante
 * @property int|null $cedula_representante_contratante
 * @property string|null $nacionalidad_representante_contratante
 * @property string|null $estado_civil_representante_contratante
 * @property string|null $lugar_nacimiento_representante_contratante
 * @property string|null $fecha_nacimiento_representante_contratante
 * @property string|null $sexo_representante_contratante
 * @property string|null $profesion_representante_contratante
 * @property string|null $ocupacion_representante_contratante
 * @property string|null $descripcion_actividad_representante_contratante
 * @property string|null $direccion_representante_contratante
 * @property string|null $telefono_representante_contratante
 * @property string|null $nombre_titular_contratante
 * @property string|null $cedula_titular_contratante
 * @property string|null $numero_cuenta_contratante
 * @property string|null $banco_contratante
 * @property string|null $tipo_cuenta_contratante
 * @property bool|null $tiene_contratante_diferente
 * @property string|null $direccion_cobro
 * @property int|null $afiliado_corporativo_id
 * @property int|null $consecutivo_menor
 * @property int|null $agencia_id

 * @property UploadedFile $selfieFile
 * @property UploadedFile $imagenIdentificacionFile
 * @property UploadedFile $videoFile
 * @property UserDatosType $userDatosType
 * 
 * @property Plan $plan
 * @property Agente $asesor
 * @property Contratos $contratos
 * @property User $userLogin
 */
class UserDatos extends ActiveRecord
{
    public $selfieFile;
    public $imagenIdentificacionFile;
    public $videoFile;
    public $codigoAsesor;
    public $masivoFile;
    public $cobertura_maternidad;

    /**
     * @var string Propiedad temporal para manejar la cédula con el formato completo (ej. V-12345678)
     * como se ingresa en el formulario con MaskedInput.
     * Esta propiedad NO existe como columna en la tabla 'user_datos' de la base de datos.
     */
    public $cedulaFormatted;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.user_datos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // 1. Campos obligatorios - CÉDULA AHORA ES OBLIGATORIA
            [[
                'nombres',
                'apellidos',
                'fechanac',
                'sexo',
                'telefono',
                'email',
                'estado',
                'direccion'
            ], 'required', 'message' => 'Este campo es obligatorio.'],

            // 2. Campos obligatorios adicionales - NUEVOS CAMPOS REQUERIDOS
            [
                ['user_datos_type_id', 'clinica_id', 'plan_id'],
                'required',
                'when' => function ($model) {
                    return $model->user_datos_type_id == 1;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#user_datos_type_id_field').val() == '1';
                }",
                'message' => 'Este campo es obligatorio para afiliados individuales.'
            ],

            // 3. Validación condicional para afiliado_corporativo_id
            [
                'afiliado_corporativo_id',
                'required',
                'when' => function ($model) {
                    return $model->user_datos_type_id == 2;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#user_datos_type_id_field').val() == '2';
                }",
                'message' => 'El afiliado corporativo es obligatorio cuando el tipo es corporativo.'
            ],

            // 4. Valores por defecto
            [['paso'], 'default', 'value' => 0.0],
            [['user_login_id', 'contrato_id'], 'default', 'value' => null],
            [['qr', 'video', 'codigoValidacion', 'deleted_at'], 'default', 'value' => null],
            [['ver_cedula', 'ver_foto'], 'default', 'value' => '0'],

            [['user_id', 'session_id', 'estatus_solvente'], 'string'],

            // 5. Validación de tipos de datos y longitud
            [['telefono'], 'string', 'max' => 15],
            [
                ['telefono'],
                'match',
                'pattern' => '/^(0416|0422|0426|0414|0424|0412|0212|0261|0241|0243|0251|0274|0276|0286|0291|0293)\d{7}$/',
                'message' => 'El número de teléfono debe ser venezolano y tener el formato correcto (ej. 04121234567).'
            ],

            [['nombres', 'apellidos', 'direccion', 'codigoValidacion', 'telefono', 'email', 'estatus'], 'string', 'max' => 255],
            [['sexo', 'estado', 'ciudad', 'municipio', 'parroquia', 'role', 'tipo_sangre'], 'string', 'max' => 255],
            [['nombres', 'apellidos', 'direccion', 'email', 'telefono'], 'trim'],

            [['cedulaFormatted'], 'string', 'max' => 11, 'message' => 'El formato de la cédula es incorrecto (máx. 11 caracteres).'],

            // 6. Validaciones específicas de contenido
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => UserDatos::class, 'message' => 'Este correo electrónico ya está registrado.'],

            [['paso'], 'number'],
            [['plan_id', 'contrato_id', 'asesor_id', 'user_login_id', 'user_datos_type_id', 'afiliado_corporativo_id'], 'integer'],

            // 7. Validaciones para campos de selección
            [['sexo'], 'in', 'range' => ['Masculino', 'Femenino', 'Otro'], 'message' => 'El sexo seleccionado no es válido.'],

            [
                ['role'],
                'in',
                'range' => \yii\helpers\ArrayHelper::getColumn(
                    \Yii::$app->authManager->getRoles(),
                    'name'
                ),
                'message' => 'El rol seleccionado no es válido.'
            ],

            [['tipo_sangre'], 'in', 'range' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], 'message' => 'Tipo de sangre no válido.'],
            [['tipo_cedula'], 'in', 'range' => ['V', 'E', 'J', 'G', 'P', 'Menor Sin Cédula', 'Afiliado Otras Clínicas'], 'message' => 'Tipo de cédula no válido.'],

            // 8. Validaciones para carga de archivos
            [['selfieFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024 * 2, 'tooBig' => 'El archivo selfie no debe exceder 2MB.'],
            [['imagenIdentificacionFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024 * 5, 'tooBig' => 'La imagen de identificación no debe exceder 5MB.'],
            [['videoFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'mp4, mov', 'maxSize' => 1024 * 1024 * 20, 'tooBig' => 'El video no debe exceder 20MB.'],

            // 9. Campos que almacenan la ruta de los archivos
            [['selfie', 'imagen_identificacion', 'video', 'qr'], 'string', 'max' => 255],

            // 10. Campos seguros (timestamps)
            [['created_at', 'updated_at', 'deleted_at', 'fechanac', 'clinica_id'], 'safe'],

            // 11. Validación específica para cédula
            [['cedula'], 'integer', 'message' => 'La cédula debe ser un número entero.'],
            [['cedula'], 'integer', 'max' => 9999999999, 'message' => 'La cédula no puede tener más de 10 dígitos.'],
            [['codigoAsesor'], 'safe'],
            // ============================================
            // CEDULA VALIDATION - COMPLETE SECTION
            // ============================================

            // 1. Required validation
            [
                'cedula',
                'required',
                'when' => function ($model) {
                    return $model->tipo_cedula !== 'Menor Sin Cédula' && !$model->tiene_contratante_diferente;
                },
                'whenClient' => "function (attribute, value) {
        var tipoCedula = $('#userdatos-tipo_cedula').val();
        return tipoCedula !== 'Menor Sin Cédula' && !$('#userdatos-tiene_contratante_diferente').is(':checked');
    }",
                'message' => 'Este campo es obligatorio.'
            ],
            // 2. Integer validation - SKIP for Passport, Menor Sin Cédula, Afiliado Otras Clínicas
            [
                'cedula',
                'integer',
                'when' => function ($model) {
                    $skipTypes = ['P', 'Menor Sin Cédula', 'Afiliado Otras Clínicas'];
                    return !in_array($model->tipo_cedula, $skipTypes);
                },
                'whenClient' => "function (attribute, value) {
        var tipoCedula = $('#userdatos-tipo_cedula').val();
        var skipTypes = ['P', 'Menor Sin Cédula', 'Afiliado Otras Clínicas'];
        return skipTypes.indexOf(tipoCedula) === -1;
    }",
                'message' => 'Cédula debe ser un número entero.'
            ],
            // 3. Max length validation - SKIP for Passport, Menor Sin Cédula, Afiliado Otras Clínicas
            [
                'cedula',
                'integer',
                'max' => 9999999999,
                'when' => function ($model) {
                    $skipTypes = ['P', 'Menor Sin Cédula', 'Afiliado Otras Clínicas'];
                    return !in_array($model->tipo_cedula, $skipTypes);
                },
                'whenClient' => "function (attribute, value) {
        var tipoCedula = $('#userdatos-tipo_cedula').val();
        var skipTypes = ['P', 'Menor Sin Cédula', 'Afiliado Otras Clínicas'];
        return skipTypes.indexOf(tipoCedula) === -1;
    }",
                'message' => 'La cédula no puede tener más de 10 dígitos.'
            ],
            // 4. Passport validation - numbers only
            [
                'cedula',
                'match',
                'pattern' => '/^[0-9]+$/',
                'when' => function ($model) {
                    return $model->tipo_cedula === 'P' && !$model->tiene_contratante_diferente;
                },
                'whenClient' => "function (attribute, value) {
        var tipoCedula = $('#userdatos-tipo_cedula').val();
        return tipoCedula === 'P' && !$('#userdatos-tiene_contratante_diferente').is(':checked');
    }",
                'message' => 'Pasaporte debe ser un número entero. Ingrese solo el número.'
            ],

            // 12. Validaciones de Existencia (Claves Foráneas)
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id'], 'message' => 'La clínica seleccionada no existe.'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id'], 'message' => 'El plan seleccionado no existe.'],
            [['contrato_id'], 'validateContratoId', 'skipOnEmpty' => true, 'skipOnError' => true],
            [['user_login_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_login_id' => 'id'], 'message' => 'El usuario de login no existe.'],

            [['banco_id'], 'integer'],
            [['banco_id'], 'exist', 'skipOnError' => true, 'targetClass' => Banco::class, 'targetAttribute' => ['banco_id' => 'id'], 'message' => 'El banco seleccionado no existe.'],

            // 13. Validaciones de fecha
            [['fechanac'], 'date', 'format' => 'yyyy-MM-dd'],
            [
                ['fechanac'],
                'compare',
                'compareValue' => date('Y-m-d'),
                'operator' => '<=',
                'type' => 'date',
                'message' => 'La fecha de nacimiento no puede ser mayor a la fecha actual.'
            ],

            // 14. Validaciones para campos de texto
            [[
                'nacionalidad',
                'estado_civil',
                'lugar_nacimiento',
                'profesion',
                'ocupacion',
                'actividad_economica',
                'ramo_comercial',
                'descripcion_actividad',
                'ingreso_anual',
                'direccion_residencia',
                'direccion_oficina',
                'telefono_residencia',
                'telefono_oficina',
                'telefono_celular',
                'plan_seleccionado',
                'moneda',
                'deducible',
                'limite_cobertura',
                'deducible_maternidad',
                'limite_cobertura_maternidad',
                'nombre_beneficiario',
                'cedula_beneficiario',
                'parentesco_beneficiario',
                'sexo_beneficiario',
                'nombre_titular',
                'cedula_titular',
                'numero_cuenta',
                'tipo_cuenta',
                'nombre_declaracion_afiliado',
                'cedula_declaracion_afiliado',
                'nombre_declaracion_contratante',
                'cedula_declaracion_contratante',
                'tipo_afiliacion',
                'nombre_contratante',
                'apellido_contratante',
                'tipo_cedula_contratante',
                'sexo_contratante',
                'nacionalidad_contratante',
                'estado_civil_contratante',
                'lugar_nacimiento_contratante',
                'profesion_contratante',
                'ocupacion_contratante',
                'actividad_economica_contratante',
                'descripcion_actividad_contratante',
                'ingreso_anual_contratante',
                'direccion_residencia_contratante',
                'direccion_oficina_contratante',
                'direccion_cobro_contratante',
                'telefono_residencia_contratante',
                'telefono_oficina_contratante',
                'telefono_celular_contratante',
                'email_contratante',
                'nombre_representante_contratante',
                'apellido_representante_contratante',
                'tipo_cedula_representante_contratante',
                'nacionalidad_representante_contratante',
                'estado_civil_representante_contratante',
                'lugar_nacimiento_representante_contratante',
                'sexo_representante_contratante',
                'profesion_representante_contratante',
                'ocupacion_representante_contratante',
                'descripcion_actividad_representante_contratante',
                'direccion_representante_contratante',
                'telefono_representante_contratante',
                'nombre_titular_contratante',
                'cedula_titular_contratante',
                'numero_cuenta_contratante',
                'banco_contratante',
                'tipo_cuenta_contratante',
                'direccion_cobro'
            ], 'string', 'max' => 255],

            // 15. Validaciones para campos de fecha
            [[
                'fecha_nacimiento_contratante',
                'fecha_nacimiento_representante_contratante',
                'fecha_nacimiento_beneficiario'
            ], 'date', 'format' => 'yyyy-MM-dd'],

            // 16. Validaciones para campos booleanos
            [['cobertura_maternidad', 'tiene_contratante_diferente'], 'boolean'],
            [['cobertura_maternidad', 'tiene_contratante_diferente'], 'default', 'value' => false],

            // 17. Validaciones para campos enteros
            [['cedula_contratante', 'cedula_representante_contratante'], 'integer'],

            // 18. Validaciones para campos de texto largo (para JSON)
            [['grupo_familiar'], 'string'],
            [['grupo_familiar'], 'safe'],

            [['tipo_cedula', 'cedula'], 'required', 'when' => function ($model) {
                return !$model->tiene_contratante_diferente;
            }, 'whenClient' => "function (attribute, value) {
                return !$('#userdatos-tiene_contratante_diferente').is(':checked');
            }"],
            [
                ['consecutivo_menor'],
                'integer',
                'min' => 1,
                'max' => 20,
                'when' => function ($model) {
                    return $model->tipo_cedula === 'Menor Sin Cédula';
                },
                'whenClient' => "function (attribute, value) {
                    return $('#userdatos-tipo_cedula').val() === 'Menor Sin Cédula';
                }",
                'message' => 'El consecutivo debe ser un número entre 1 y 20.'
            ],

            [
                ['consecutivo_menor'],
                'required',
                'when' => function ($model) {
                    return $model->tipo_cedula === 'Menor Sin Cédula';
                },
                'whenClient' => "function (attribute, value) {
                    return $('#userdatos-tipo_cedula').val() === 'Menor Sin Cédula';
                }",
                'message' => 'El número consecutivo es obligatorio para menores sin cédula.'
            ],
            [['agencia_id'], 'integer'],
            [['agencia_id'], 'exist', 'skipOnError' => true, 'targetClass' => Agente::class, 'targetAttribute' => ['agencia_id' => 'id']],

            // 19. DUPLICATE VALIDATION - Check if affiliate already exists
            ['cedula', 'validateNoDuplicate'],
        ];
    }

    /**
     * Custom validator for corporativo field
     */
    public function validateCorporativoRequired($attribute, $params)
    {
        if ($this->user_datos_type_id == 2 && empty($this->$attribute)) {
            $this->addError($attribute, 'El afiliado corporativo es obligatorio cuando el tipo es corporativo.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'nacionalidad',
            'estado_civil',
            'lugar_nacimiento',
            'profesion',
            'ocupacion',
            'actividad_economica',
            'ramo_comercial',
            'descripcion_actividad',
            'ingreso_anual',
            'direccion_residencia',
            'direccion_oficina',
            'telefono_residencia',
            'telefono_oficina',
            'telefono_celular',
            'razon_social',
            'rif',
            'registro_mercantil',
            'tomo',
            'fecha_registro',
            'actividad_economica_corp',
            'direccion_corporativa',
            'telefono_corporativo',
            'productos_servicios',
            'utilidad',
            'patrimonio',
            'plan_seleccionado',
            'moneda',
            'deducible',
            'limite_cobertura',
            'cobertura_maternidad',
            'deducible_maternidad',
            'limite_cobertura_maternidad',
            'grupo_familiar',
            'nombre_beneficiario',
            'cedula_beneficiario',
            'parentesco_beneficiario',
            'sexo_beneficiario',
            'fecha_nacimiento_beneficiario',
            'nombre_titular',
            'cedula_titular',
            'numero_cuenta',
            'banco_id',
            'tipo_cuenta',
            'nombre_declaracion_afiliado',
            'cedula_declaracion_afiliado',
            'nombre_declaracion_contratante',
            'cedula_declaracion_contratante',
            'tipo_afiliacion',
            'nombre_contratante',
            'apellido_contratante',
            'tipo_cedula_contratante',
            'cedula_contratante',
            'fecha_nacimiento_contratante',
            'sexo_contratante',
            'nacionalidad_contratante',
            'estado_civil_contratante',
            'lugar_nacimiento_contratante',
            'profesion_contratante',
            'ocupacion_contratante',
            'actividad_economica_contratante',
            'descripcion_actividad_contratante',
            'ingreso_anual_contratante',
            'direccion_residencia_contratante',
            'direccion_oficina_contratante',
            'direccion_cobro_contratante',
            'telefono_residencia_contratante',
            'telefono_oficina_contratante',
            'telefono_celular_contratante',
            'email_contratante',
            'nombre_representante_contratante',
            'apellido_representante_contratante',
            'tipo_cedula_representante_contratante',
            'cedula_representante_contratante',
            'nacionalidad_representante_contratante',
            'estado_civil_representante_contratante',
            'lugar_nacimiento_representante_contratante',
            'fecha_nacimiento_representante_contratante',
            'sexo_representante_contratante',
            'profesion_representante_contratante',
            'ocupacion_representante_contratante',
            'descripcion_actividad_representante_contratante',
            'direccion_representante_contratante',
            'telefono_representante_contratante',
            'nombre_titular_contratante',
            'cedula_titular_contratante',
            'numero_cuenta_contratante',
            'banco_contratante',
            'tipo_cuenta_contratante',
            'tiene_contratante_diferente',
            'direccion_cobro',
            'afiliado_corporativo_id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'cedulaFormatted' => 'Cédula de Identidad / Pasaporte',
            'direccion_cobro' => 'Dirección de Cobro',
            'user_datos_type_id' => 'Tipo de Afiliado',
            'clinica_id' => 'Clínica',
            'plan_id' => 'Plan',
            'consecutivo_menor' => 'Número Consecutivo',
            'afiliado_corporativo_id' => 'Afiliado Corporativo',
            'agencia_id' => 'Agencia Asociada',
        ]);
    }

    // ============================================
    // VALIDACIÓN PERSONALIZADA PARA TELÉFONO
    // ============================================

    /**
     * Valida que el número de teléfono sea venezolano y tenga un prefijo válido.
     *
     * @param string $attribute El nombre del atributo que se está validando (ej. 'telefono').
     * @param array $params Parámetros adicionales para la validación.
     */
    public function validateVenezuelanPhoneNumber($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $cleanedPhone = str_replace(['(', ')', ' ', '-'], '', $this->$attribute);

        $validPrefixes = [
            '0416',
            '0426',
            '0414',
            '0424',
            '0412',
            '0212',
            '0261',
            '0241',
            '0243',
            '0251',
            '0274',
            '0276',
            '0286',
            '0291',
            '0293'
        ];

        if (strlen($cleanedPhone) !== 11) {
            $this->addError($attribute, 'El número de teléfono debe tener 11 dígitos.');
            return;
        }

        $prefix = substr($cleanedPhone, 0, 4);

        if (!in_array($prefix, $validPrefixes)) {
            $this->addError($attribute, 'El prefijo del número de teléfono no es válido en Venezuela.');
        }

        if (!preg_match('/^\d{11}$/', $cleanedPhone)) {
            $this->addError($attribute, 'El número de teléfono debe contener solo dígitos.');
        }
    }

    // ============================================
    // VALIDACIÓN PERSONALIZADA PARA EDAD
    // ============================================

    /**
     * Valida que la fecha de nacimiento no corresponda a una persona menor de 18 años.
     *
     * @param string $attribute el nombre del atributo a validar (ej. 'fechanac')
     * @param array $params parámetros adicionales (no usados aquí)
     */
    public function validateAge($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            try {
                $birthDate = new \DateTime($this->$attribute);
                $today = new \DateTime();
                $age = $birthDate->diff($today)->y;

                if ($age < 18) {
                    $this->addError($attribute, 'Debe tener al menos 18 años para registrarse.');
                }
            } catch (\Exception $e) {
                $this->addError($attribute, 'Formato de fecha de nacimiento inválido.');
            }
        }
    }

    // ============================================
    // VALIDACIÓN PERSONALIZADA PARA DUPLICADOS
    // ============================================

    /**
     * Comprehensive duplicate validation for affiliates
     * 
     * @param bool $excludeCurrent If true, exclude current record when checking duplicates
     * @return bool Returns true if duplicate found, false otherwise
     */
    public function checkAffiliateDuplicate($excludeCurrent = false)
    {
        // Only check for 'afiliado' role
        if ($this->role !== 'afiliado') {
            return false;
        }

        // For underage (Menor Sin Cédula), we use a different duplicate logic
        if ($this->tipo_cedula === 'Menor Sin Cédula') {
            return $this->checkUnderageDuplicate($excludeCurrent);
        }

        // Regular duplicate check for normal affiliates
        return $this->checkRegularDuplicate($excludeCurrent);
    }

    /**
     * Check duplicates for regular affiliates (non-underage)
     * 
     * @param bool $excludeCurrent
     * @return bool
     */
    private function checkRegularDuplicate($excludeCurrent = false)
    {
        // Normalize names for comparison (trim, remove multiple spaces)
        $nombres = preg_replace('/\s+/', ' ', trim($this->nombres));
        $apellidos = preg_replace('/\s+/', ' ', trim($this->apellidos));

        $query = self::find()
            ->where(['role' => 'afiliado'])
            ->andWhere(['tipo_cedula' => $this->tipo_cedula])
            ->andWhere(['cedula' => $this->cedula])
            ->andWhere(['IS', 'deleted_at', null]);

        // ============================================
        // MODIFIED: Only check cross-type for non-"Afiliado Otras Clínicas"
        // "Afiliado Otras Clínicas" is treated as a separate valid type
        // ============================================
        if ($this->tipo_cedula !== 'Afiliado Otras Clínicas') {
            // For regular types (V, E, J, etc.), check if this cedula exists 
            // with "Afiliado Otras Clínicas" type
            $crossQuery = self::find()
                ->where(['role' => 'afiliado'])
                ->andWhere(['cedula' => $this->cedula])
                ->andWhere(['IS', 'deleted_at', null])
                ->andWhere(['tipo_cedula' => 'Afiliado Otras Clínicas']);

            if ($excludeCurrent && !$this->isNewRecord) {
                $crossQuery->andWhere(['!=', 'id', $this->id]);
            }

            if ($crossQuery->count() > 0) {
                return true;
            }
        }

        // Also check: If current is "Afiliado Otras Clínicas", check if it exists
        // with the SAME tipo (should be the only one)
        if ($this->tipo_cedula === 'Afiliado Otras Clínicas') {
            // Only check for exact match on tipo_cedula for this type
            // No cross-type checking for "Afiliado Otras Clínicas"
            // This allows "Afiliado Otras Clínicas" to coexist with V, E, J, etc.
            // with the same cedula number
            if ($excludeCurrent && !$this->isNewRecord) {
                $query->andWhere(['!=', 'id', $this->id]);
            }
        } else {
            if ($excludeCurrent && !$this->isNewRecord) {
                $query->andWhere(['!=', 'id', $this->id]);
            }
        }

        $count = $query->count();

        if ($count > 0) {
            if ($count == 1) {
                $existing = $query->one();
                if ($existing) {
                    if (
                        strcasecmp($existing->nombres, $nombres) === 0 &&
                        strcasecmp($existing->apellidos, $apellidos) === 0
                    ) {
                        return true;
                    }
                    return true;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Check duplicates for underage affiliates (Menor Sin Cédula)
     * These can share the same parent/tutor cedula
     * 
     * @param bool $excludeCurrent
     * @return bool
     */
    private function checkUnderageDuplicate($excludeCurrent = false)
    {
        $nombres = preg_replace('/\s+/', ' ', trim($this->nombres));
        $apellidos = preg_replace('/\s+/', ' ', trim($this->apellidos));

        $query = self::find()
            ->where(['role' => 'afiliado'])
            ->andWhere(['tipo_cedula' => 'Menor Sin Cédula'])
            ->andWhere(['cedula' => $this->cedula])
            ->andWhere(['IS', 'deleted_at', null]);

        if ($excludeCurrent && !$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        $existing = $query->all();

        if (empty($existing)) {
            return false;
        }

        $nombresLower = strtolower($nombres);
        $apellidosLower = strtolower($apellidos);

        foreach ($existing as $record) {
            $recordNombres = strtolower(preg_replace('/\s+/', ' ', trim($record->nombres)));
            $recordApellidos = strtolower(preg_replace('/\s+/', ' ', trim($record->apellidos)));

            if ($recordNombres === $nombresLower && $recordApellidos === $apellidosLower) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get duplicate details for error reporting
     * 
     * @param bool $excludeCurrent
     * @return array|null Returns array with duplicate record details or null if none
     */
    public function getDuplicateDetails($excludeCurrent = false)
    {
        if ($this->tipo_cedula === 'Menor Sin Cédula') {
            return $this->getUnderageDuplicateDetails($excludeCurrent);
        }

        return $this->getRegularDuplicateDetails($excludeCurrent);
    }

    /**
     * Get regular affiliate duplicate details
     * 
     * @param bool $excludeCurrent
     * @return array|null Returns array with 'record' and 'type' keys, or null
     */
    private function getRegularDuplicateDetails($excludeCurrent = false)
    {
        $query = self::find()
            ->where(['role' => 'afiliado'])
            ->andWhere(['tipo_cedula' => $this->tipo_cedula])
            ->andWhere(['cedula' => $this->cedula])
            ->andWhere(['IS', 'deleted_at', null]);

        if ($excludeCurrent && !$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        $record = $query->one();

        if ($record) {
            return [
                'record' => $record,
                'type' => 'regular'
            ];
        }

        return null;
    }

    /**
     * Get underage affiliate duplicate details
     * 
     * @param bool $excludeCurrent
     * @return array|null Returns array with 'record' and 'type' keys, or null
     */
    private function getUnderageDuplicateDetails($excludeCurrent = false)
    {
        $nombres = preg_replace('/\s+/', ' ', trim($this->nombres));
        $apellidos = preg_replace('/\s+/', ' ', trim($this->apellidos));

        $query = self::find()
            ->where(['role' => 'afiliado'])
            ->andWhere(['tipo_cedula' => 'Menor Sin Cédula'])
            ->andWhere(['cedula' => $this->cedula])
            ->andWhere(['IS', 'deleted_at', null]);

        if ($excludeCurrent && !$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        $nombresLower = strtolower($nombres);
        $apellidosLower = strtolower($apellidos);

        foreach ($query->all() as $record) {
            $recordNombres = strtolower(preg_replace('/\s+/', ' ', trim($record->nombres)));
            $recordApellidos = strtolower(preg_replace('/\s+/', ' ', trim($record->apellidos)));

            if ($recordNombres === $nombresLower && $recordApellidos === $apellidosLower) {
                return [
                    'record' => $record,
                    'type' => 'underage'
                ];
            }
        }

        return null;
    }

    /**
     * Validate that there is no duplicate affiliate
     */
    public function validateNoDuplicate($attribute, $params)
    {
        if ($this->role !== 'afiliado') {
            return;
        }

        $excludeCurrent = !$this->isNewRecord;

        if ($this->checkAffiliateDuplicate($excludeCurrent)) {
            $duplicateInfo = $this->getDuplicateDetails($excludeCurrent);

            $message = 'Este afiliado ya se encuentra registrado en el sistema.';

            if ($duplicateInfo && isset($duplicateInfo['record'])) {
                $record = $duplicateInfo['record'];
                $message .= ' Registro existente: ' . $record->nombres . ' ' . $record->apellidos;
                $message .= ' (Cédula: ' . $record->tipo_cedula . '-' . $record->cedula . ')';

                if ($this->tipo_cedula === 'Menor Sin Cédula') {
                    $message .= ' | Consecutivo: ' . ($record->consecutivo_menor ?? 'N/A');
                }
            }

            $this->addError($attribute, $message);
        }
    }

    // ============================================
    // VALIDACIÓN PERSONALIZADA PARA CONTRATO ID
    // ============================================

    /**
     * Custom validator for contrato_id
     * Only validates if the contrato_id is not empty AND we're not in update mode
     */
    public function validateContratoId($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            $contrato = Contratos::findOne($this->$attribute);
            if ($contrato === null) {
                if ($this->isNewRecord) {
                    $this->addError($attribute, 'El contrato seleccionado no existe.');
                } else {
                    $this->$attribute = null;
                }
            }
        }
    }

    // ============================================
    // BEFORE VALIDATE
    // ============================================

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->hasAttribute('tiene_contratante_diferente')) {
            $this->tiene_contratante_diferente = (bool)$this->tiene_contratante_diferente;
        }

        return true;
    }

    // ============================================
    // RELACIONES (MÉTODOS GET)
    // ============================================

    public function getClinica()
    {
        return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']);
    }

    public function getPlan()
    {
        return $this->hasOne(Planes::class, ['id' => 'plan_id']);
    }

    public function getAsesor()
    {
        return $this->hasOne(AgenteFuerza::class, ['id' => 'asesor_id']);
    }

    public function getContrato()
    {
        return $this->hasOne(Contratos::class, ['id' => 'contrato_id']);
    }

    public function getContratos()
    {
        return $this->hasMany(Contratos::class, ['user_id' => 'id']);
    }

    public function getUserLogin()
    {
        return $this->hasOne(User::class, ['id' => 'user_login_id']);
    }

    public function getUserDatosType()
    {
        return $this->hasOne(UserDatosType::class, ['id' => 'user_datos_type_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_login_id']);
    }

    public function getBanco()
    {
        return $this->hasOne(Banco::class, ['id' => 'banco_id']);
    }

    public function getCorporativo()
    {
        return $this->hasOne(Corporativo::class, ['id' => 'afiliado_corporativo_id']);
    }

    public function getAgencia()
    {
        return $this->hasOne(Agente::class, ['id' => 'agencia_id']);
    }

    // ============================================
    // MÉTODOS UTILITARIOS
    // ============================================

    /**
     * Get the full location as a formatted string
     * 
     * @return string Location in format "Estado, Municipio, Ciudad"
     */
    public function getFullLocation()
    {
        $parts = [];
        if (!empty($this->estado)) $parts[] = $this->estado;
        if (!empty($this->municipio)) $parts[] = $this->municipio;
        if (!empty($this->ciudad)) $parts[] = $this->ciudad;
        return implode(', ', $parts);
    }

    /**
     * Get the SUDEASEG location code for this user
     * 
     * @return string Location code in format "XX-YY"
     */
    public function getSudeasegLocationCode()
    {
        if (empty($this->estado)) {
            return '';
        }

        if (!empty($this->municipio)) {
            $code = EventoReportSearch::getLocationCode($this->estado, $this->municipio);
            if (!empty($code)) return $code;
        }

        if (!empty($this->ciudad)) {
            $code = EventoReportSearch::getLocationCode($this->estado, $this->ciudad);
            if (!empty($code)) return $code;
        }

        if (!empty($this->parroquia)) {
            $code = EventoReportSearch::getLocationCode($this->estado, $this->parroquia);
            if (!empty($code)) return $code;
        }

        $stateCode = EventoReportSearch::getStateCode($this->estado);
        if ($stateCode) {
            return $stateCode . '-00';
        }

        return '';
    }
    /**
     * Gets query for [[Preexistencias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreexistencias()
    {
        return $this->hasMany(Preexistencias::class, ['user_id' => 'id'])
            ->where(['IS', 'deleted_at', null])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Gets query for [[DeclaracionDeSalud]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeclaracionDeSalud()
    {
        return $this->hasOne(DeclaracionDeSalud::class, ['user_id' => 'id'])
            ->where(['IS', 'deleted_at', null])
            ->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * Check if the affiliate has a health declaration
     *
     * @return bool
     */
    public function hasHealthDeclaration()
    {
        return $this->getDeclaracionDeSalud()->exists();
    }
}
