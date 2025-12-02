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
 * @property int|null $cedula // ¡Sigue siendo INTEGER en DB, solo números!
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
 * @property string|null $limite_cobertura_maternidad
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
 *
 * // ... (Tus @property para las relaciones get...())
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
    //public $tiene_contratante_diferente;
    public $cobertura_maternidad;
    
    // NOTA: Solo propiedades públicas para campos que NO están en la base de datos
    // Todos los campos de la tabla están documentados en @property arriba

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
            [['nombres', 'apellidos', 'fechanac', 'sexo',
              'telefono', 'email', 'estado','direccion'], 'required', 'message' => 'Este campo es obligatorio.'],

            // 2. Campos obligatorios adicionales - NUEVOS CAMPOS REQUERIDOS
            [['user_datos_type_id', 'clinica_id', 'plan_id'], 'required', 
                'when' => function($model) {
                    return $model->user_datos_type_id == 1;
                }, 
                'whenClient' => "function (attribute, value) {
                    return $('#user_datos_type_id_field').val() == '1';
                }", 
                'message' => 'Este campo es obligatorio para afiliados individuales.'
            ],

            // 3. Validación condicional para afiliado_corporativo_id
            ['afiliado_corporativo_id', 'required', 
                'when' => function($model) {
                    return $model->user_datos_type_id == 2;
                }, 
                'whenClient' => "function (attribute, value) {
                    return $('#user_datos_type_id_field').val() == '2';
                }", 
                'message' => 'El afiliado corporativo es obligatorio cuando el tipo es corporativo.'
            ],

            // 4. Valores por defecto (se mantienen igual)
            [['paso'], 'default', 'value' => 0.0],
            [['user_login_id', 'contrato_id'], 'default', 'value' => null],
            [['qr', 'video', 'codigoValidacion', 'deleted_at'], 'default', 'value' => null],
            [['ver_cedula', 'ver_foto'], 'default', 'value' => '0'],

            [['user_id', 'session_id', 'estatus_solvente'], 'string'],
            
            // 5. Validación de tipos de datos y longitud
            [['telefono'], 'string', 'max' => 15], // La longitud máxima de (9999) 999-9999 es 14, pero 15 por si acaso
            [['telefono'], 'match',
                'pattern' => '/^(0416|0422|0426|0414|0424|0412|0212|0261|0241|0243|0251|0274|0276|0286|0291|0293)\d{7}$/',
                'message' => 'El número de teléfono debe ser venezolano y tener el formato correcto (ej. 04121234567).'],

            [['nombres', 'apellidos', 'direccion', 'codigoValidacion', 'telefono', 'email', 'estatus'], 'string', 'max' => 255],
            [['sexo', 'estado', 'ciudad', 'municipio', 'parroquia', 'role', 'tipo_sangre'], 'string', 'max' => 255],
            [['nombres', 'apellidos', 'direccion', 'email', 'telefono'], 'trim'],

        
            [['cedulaFormatted'], 'string', 'max' => 11, 'message' => 'El formato de la cédula es incorrecto (máx. 11 caracteres).'],
           
            // 6. Validaciones específicas de contenido (se mantienen igual)
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => UserDatos::class, 'message' => 'Este correo electrónico ya está registrado.'],

            [['paso'], 'number'],
            [['plan_id', 'contrato_id', 'asesor_id', 'user_login_id', 'user_datos_type_id', 'afiliado_corporativo_id'], 'integer'],

            // 7. Validaciones para campos de selección (TEXT en DB) (se mantienen igual, pero la de tipo_cedula es redundante si se deriva)
            [['sexo'], 'in', 'range' => ['Masculino', 'Femenino', 'Otro'], 'message' => 'El sexo seleccionado no es válido.'],
            
            //validaciones para roles 
            [['role'], 'in',
            'range' => \yii\helpers\ArrayHelper::getColumn(
                \Yii::$app->authManager->getRoles(), 'name'
            ),
            'message' => 'El rol seleccionado no es válido.'
            ],

            [['tipo_sangre'], 'in', 'range' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], 'message' => 'Tipo de sangre no válido.'],
            [['tipo_cedula'], 'in', 'range' => ['V', 'E', 'J', 'G'], 'message' => 'Tipo de cédula no válido.'], 

        
            // 8. Validaciones para carga de archivos (se mantienen igual)
            [['selfieFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024 * 2, 'tooBig' => 'El archivo selfie no debe exceder 2MB.'],
            [['imagenIdentificacionFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024 * 5, 'tooBig' => 'La imagen de identificación no debe exceder 5MB.'],
            [['videoFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'mp4, mov', 'maxSize' => 1024 * 1024 * 20, 'tooBig' => 'El video no debe exceder 20MB.'],

            // 9. Campos que almacenan la ruta de los archivos (TEXT en DB) (se mantienen igual)
            [['selfie', 'imagen_identificacion', 'video', 'qr'], 'string', 'max' => 255],

            // 10. Campos seguros (timestamps)
            [['created_at', 'updated_at', 'deleted_at', 'fechanac','clinica_id'], 'safe'],
            
            // 11. Validación específica para cédula - debe ser numérica y ahora es obligatoria
            [['cedula'], 'integer', 'message' => 'La cédula debe ser un número entero.'],
            [['cedula'], 'integer', 'max' => 9999999999, 'message' => 'La cédula no puede tener más de 10 dígitos.'],
            [['codigoAsesor'], 'safe'],
            
            // 12. Validaciones de Existencia (Claves Foráneas) (se mantienen igual)
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id'], 'message' => 'La clínica seleccionada no existe.'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id'], 'message' => 'El plan seleccionado no existe.'],
            [['contrato_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contratos::class, 'targetAttribute' => ['contrato_id' => 'id'], 'message' => 'El contrato seleccionado no existe.'],
            [['user_login_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_login_id' => 'id'], 'message' => 'El usuario de login no existe.'],

            [['banco_id'], 'integer'],
            [['banco_id'], 'exist', 'skipOnError' => true, 'targetClass' => Banco::class, 'targetAttribute' => ['banco_id' => 'id'], 'message' => 'El banco seleccionado no existe.'],

            // 13. Validaciones de fecha
            [['fechanac'], 'date', 'format' => 'yyyy-MM-dd'], // Valida el formato de fecha
            [['fechanac'], 'compare', 'compareValue' => date('Y-m-d'), 'operator' => '<=', 'type' => 'date',
                'message' => 'La fecha de nacimiento no puede ser mayor a la fecha actual.'],

            // 14. Validaciones para campos de texto (VARCHAR/TEXT)
            [['nacionalidad', 'estado_civil', 'lugar_nacimiento', 'profesion', 'ocupacion',
              'actividad_economica', 'ramo_comercial', 'descripcion_actividad', 'ingreso_anual',
              'direccion_residencia', 'direccion_oficina', 'telefono_residencia', 'telefono_oficina',
              'telefono_celular', 'plan_seleccionado', 'moneda', 'deducible', 'limite_cobertura',
              'deducible_maternidad', 'limite_cobertura_maternidad', 'nombre_beneficiario',
              'cedula_beneficiario', 'parentesco_beneficiario', 'sexo_beneficiario',
              'nombre_titular', 'cedula_titular', 'numero_cuenta', 'tipo_cuenta',
              'nombre_declaracion_afiliado', 'cedula_declaracion_afiliado', 'nombre_declaracion_contratante',
              'cedula_declaracion_contratante', 'tipo_afiliacion', 'nombre_contratante',
              'apellido_contratante', 'tipo_cedula_contratante', 'sexo_contratante',
              'nacionalidad_contratante', 'estado_civil_contratante', 'lugar_nacimiento_contratante',
              'profesion_contratante', 'ocupacion_contratante', 'actividad_economica_contratante',
              'descripcion_actividad_contratante', 'ingreso_anual_contratante', 'direccion_residencia_contratante',
              'direccion_oficina_contratante', 'direccion_cobro_contratante', 'telefono_residencia_contratante',
              'telefono_oficina_contratante', 'telefono_celular_contratante', 'email_contratante',
              'nombre_representante_contratante', 'apellido_representante_contratante',
              'tipo_cedula_representante_contratante', 'nacionalidad_representante_contratante',
              'estado_civil_representante_contratante', 'lugar_nacimiento_representante_contratante',
              'sexo_representante_contratante', 'profesion_representante_contratante',
              'ocupacion_representante_contratante', 'descripcion_actividad_representante_contratante',
              'direccion_representante_contratante', 'telefono_representante_contratante',
              'nombre_titular_contratante', 'cedula_titular_contratante', 'numero_cuenta_contratante',
              'banco_contratante', 'tipo_cuenta_contratante', 'direccion_cobro'], 'string', 'max' => 255],

            // 15. Validaciones para campos de fecha
            [['fecha_nacimiento_contratante',
              'fecha_nacimiento_representante_contratante',
              'fecha_nacimiento_beneficiario'], 'date', 'format' => 'yyyy-MM-dd'],

            // 16. Validaciones para campos booleanos
            [['cobertura_maternidad', 'tiene_contratante_diferente'], 'boolean'],
            [['cobertura_maternidad', 'tiene_contratante_diferente'], 'default', 'value' => false],

            // 17. Validaciones para campos enteros
            [['cedula_contratante', 'cedula_representante_contratante'], 'integer'],

            // 18. Validaciones para campos de texto largo (para JSON)
            [['grupo_familiar'], 'string'],
            [['grupo_familiar'], 'safe'],

            [['tipo_cedula', 'cedula'], 'required', 'when' => function ($model) {
            // Solo es requerido si 'tiene_contratante_diferente' es falso/no está marcado.
            // Si el valor del checkbox es 1 (marcado), 'when' evalúa a falso y omite la regla.
            return !$model->tiene_contratante_diferente;
            }, 'whenClient' => "function (attribute, value) {
                // Lógica del lado del cliente (JS)
                return !$('#userdatos-tiene_contratante_diferente').is(':checked');
            }"],
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
            'cedulaFormatted' => 'Cédula de Identidad',
            'direccion_cobro' => 'Dirección de Cobro',
            'user_datos_type_id' => 'Tipo de Afiliado',
            'clinica_id' => 'Clínica',
            'plan_id' => 'Plan',
            'afiliado_corporativo_id' => 'Afiliado Corporativo',
        ]);
    }

    // --- INICIO: Validador personalizado para el campo 'telefono' ---
    /**
     * Valida que el número de teléfono sea venezolano y tenga un prefijo válido.
     * Este método es llamado por la regla de validación definida en `rules()`.
     *
     * @param string $attribute El nombre del atributo que se está validando (ej. 'telefono').
     * @param array $params Parámetros adicionales para la validación.
     */
    public function validateVenezuelanPhoneNumber($attribute, $params)
    {
        // Si ya hay errores en el atributo (ej. 'required'), no seguimos validando.
        if ($this->hasErrors($attribute)) {
            return;
        }

        // Limpiamos el formato del número de teléfono (quita paréntesis, espacios, guiones).
        $cleanedPhone = str_replace(['(', ')', ' ', '-'], '', $this->$attribute);

        // Define los prefijos venezolanos válidos.
        $validPrefixes = [
            '0416', '0426', '0414', '0424', '0412',
            '0212', '0261', '0241', '0243', '0251',
            '0274', '0276', '0286', '0291', '0293'
        ];

        // 1. Valida la longitud total del número limpio.
        if (strlen($cleanedPhone) !== 11) {
            $this->addError($attribute, 'El número de teléfono debe tener 11 dígitos.');
            return; // Si la longitud es incorrecta, no continuamos con la validación de prefijo.
        }

        // 2. Extrae el prefijo (los primeros 4 dígitos).
        $prefix = substr($cleanedPhone, 0, 4);

        // 3. Valida si el prefijo está en la lista de prefijos válidos.
        if (!in_array($prefix, $validPrefixes)) {
            $this->addError($attribute, 'El prefijo del número de teléfono no es válido en Venezuela.');
        }

        // 4. (Opcional pero recomendado) Valida que el resto del número sean solo dígitos.
        if (!preg_match('/^\d{11}$/', $cleanedPhone)) {
             $this->addError($attribute, 'El número de teléfono debe contener solo dígitos.');
        }
    }
    // --- FIN: Validador personalizado para el campo 'telefono' ---

    /**
     * Valida que la fecha de nacimiento no corresponda a una persona menor de 18 años.
     * Este es un método de validación personalizado.
     *
     * @param string $attribute el nombre del atributo a validar (ej. 'fechanac')
     * @param array $params parámetros adicionales (no usados aquí)
     */
    public function validateAge($attribute, $params)
    {
        // Solo valida si el campo tiene un valor. Las reglas 'required' y 'date'
        // ya deberían asegurar que el valor no esté vacío y tenga un formato de fecha.
        if (!empty($this->$attribute)) {
            try {
                // Crea objetos DateTime para la fecha de nacimiento y la fecha actual
                $birthDate = new \DateTime($this->$attribute);
                $today = new \DateTime(); // La fecha y hora actual (ej. 2025-07-12)

                // Calcula la diferencia en años
                $age = $birthDate->diff($today)->y;

                // Si la edad calculada es menor de 18, añade un error
                if ($age < 18) {
                    $this->addError($attribute, 'Debe tener al menos 18 años para registrarse.');
                }
            } catch (\Exception $e) {
                // Captura cualquier error si el valor de la fecha es inesperadamente inválido
                $this->addError($attribute, 'Formato de fecha de nacimiento inválido.');
            }
        }
    }

    // --- RELACIONES (MÉTODOS GET) ---
    public function getClinica() { return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']); }
    public function getPlan() { return $this->hasOne(Planes::class, ['id' => 'plan_id']); }
    public function getAsesor() { return $this->hasOne(AgenteFuerza::class, ['id' => 'asesor_id']); }
    public function getContrato() { return $this->hasOne(Contratos::class, ['id' => 'contrato_id']); }
    public function getContratos()
    {
        return $this->hasMany(Contratos::class, ['user_id' => 'id']);
    }
    public function getUserLogin() { return $this->hasOne(User::class, ['id' => 'user_login_id']); }
    public function getUserDatosType(){return $this->hasOne(UserDatosType::class, ['id' => 'user_datos_type_id']);}
    public function getUser() { return $this->hasOne(User::class, ['id' => 'user_login_id']); }
    public function getBanco() {return $this->hasOne(Banco::class, ['id' => 'banco_id']); }

   
    public function getCorporativo()
    {
        return $this->hasOne(Corporativo::class, ['id' => 'afiliado_corporativo_id']);
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        // Solución de emergencia: Convertir el valor a booleano estricto.
        // Esto es necesario para PostgreSQL cuando el campo es 'boolean'.
        if ($this->hasAttribute('tiene_contratante_diferente')) {
            // Si el valor es el entero 1 o 0 (que es lo que envían el controlador/form), 
            // lo convertimos a TRUE o FALSE antes de la validación final.
            $this->tiene_contratante_diferente = (bool)$this->tiene_contratante_diferente;
        }
        
        return true;
    }
}