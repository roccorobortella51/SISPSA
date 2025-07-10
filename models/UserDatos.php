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
 *
 * // ... (Tus @property para las relaciones get...())
 * @property UploadedFile $selfieFile
 * @property UploadedFile $imagenIdentificacionFile
 * @property UploadedFile $videoFile
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

    /**
     * @var string Propiedad temporal para manejar la cédula con el formato completo (ej. V-12345678)
     * como se ingresa en el formulario con MaskedInput.
     * Esta propiedad NO existe como columna en la tabla 'user_datos' de la base de datos.
     */
    public $cedulaFormatted; // <-- ¡ESTO ES NUEVO Y CLAVE!

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_datos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // 1. Campos obligatorios
            // CAMBIO: Ahora 'cedulaFormatted' es el campo requerido, no 'cedula' directamente,
            // porque el usuario lo ingresa con el formato completo.
            [['nombres', 'apellidos', 'fechanac', 'sexo',
              'telefono', 'email', 'estado','direccion'], 'required', 'message' => 'Este campo es obligatorio.'],

            // 2. Valores por defecto (se mantienen igual)
            [['paso'], 'default', 'value' => 0.0],
            [['user_login_id', 'contrato_id'], 'default', 'value' => null],
            [['qr', 'video', 'codigoValidacion', 'deleted_at'], 'default', 'value' => null],
            [['ver_cedula', 'ver_foto', 'estatus_solvente'], 'default', 'value' => '0'],

            [['user_id', 'session_id'], 'string'],
            
            // 3. Validación de tipos de datos y longitud
            [['telefono'], 'string', 'max' => 15], // La longitud máxima de (9999) 999-9999 es 14, pero 15 por si acaso
            [['telefono'], 'match',
                'pattern' => '/^(0416|0426|0414|0424|0412|0212|0261|0241|0243|0251|0274|0276|0286|0291|0293)\d{7}$/',
                'message' => 'El número de teléfono debe ser venezolano y tener el formato correcto (ej. 04121234567).'],

            [['nombres', 'apellidos', 'direccion', 'codigoValidacion', 'telefono', 'email'], 'string', 'max' => 255],
            [['sexo', 'estado', 'ciudad', 'municipio', 'parroquia', 'role', 'estatus', 'tipo_sangre'], 'string', 'max' => 50],
            [['nombres', 'apellidos', 'direccion', 'email', 'telefono'], 'trim'],

            // VALIDACIÓN DE CÉDULA:
            // Estos son los CAMBIOS MÁS IMPORTANTES en rules()
            // --- CÓDIGO ORIGINAL QUE DEBES ELIMINAR O COMENTAR ---
            // [['cedula'], 'integer', 'message' => 'La cédula debe contener solo números.'],
            // [['cedula'], 'string', 'max' => 9, 'min' => 7], // Estas reglas ya no aplican directamente al input del usuario.
            // --- FIN CÓDIGO ORIGINAL ---

            // NUEVAS REGLAS para 'cedulaFormatted':
            // 3.1. Valida que 'cedulaFormatted' sea un string y tenga la longitud esperada (V-999999999 es 11 caracteres).
            [['cedulaFormatted'], 'string', 'max' => 11, 'message' => 'El formato de la cédula es incorrecto (máx. 11 caracteres).'],
            // 3.2. Valida el patrón exacto: Una letra (V, E, J, G), un guion, y de 7 a 9 dígitos.
            //[['cedulaFormatted'], 'match', 'pattern' => '/^[VEJG]-\d{7,9}$/', 'message' => 'El formato debe ser V-XXXXXXXX, E-XXXXXXXX, J-XXXXXXXX o G-XXXXXXXX.'],
            
            // Regla de unicidad para 'cedula' (el número entero en la DB).
            // Esta validación se ejecuta *después* de que 'beforeSave()' haya separado el número del formato.
            /*['cedula', 'unique', 'targetClass' => UserDatos::class, 'message' => 'Esta cédula ya está registrada.', 'when' => function($model) {
                // Solo verifica la unicidad si es un nuevo registro O si el valor numérico de la cédula ha cambiado.
                return $model->isNewRecord || $model->isAttributeDirty('cedula');
            }],*/
            
            [['paso'], 'number'],
            [['plan_id', 'contrato_id', 'asesor_id', 'user_login_id'], 'integer'],

            // 4. Validaciones específicas de contenido (se mantienen igual)
            [['email'], 'email'],
            [['email'], 'unique', 'targetClass' => UserDatos::class, 'message' => 'Este correo electrónico ya está registrado.'],

            //[['fechanac'], 'date', 'format' => 'yyyy-MM-dd', 'message' => 'El formato de la fecha de nacimiento debe ser YYYY-MM-DD.'],
            //[['fechanac'], 'compare', 'compareValue' => date('Y-m-d'), 'operator' => '<=', 'type' => 'date', 'message' => 'La fecha de nacimiento no puede ser en el futuro.'],

            // Validaciones para campos de selección (TEXT en DB) (se mantienen igual, pero la de tipo_cedula es redundante si se deriva)
            [['sexo'], 'in', 'range' => ['Masculino', 'Femenino', 'Otro'], 'message' => 'El sexo seleccionado no es válido.'],
            
            //validaciones para roles 
            [['role'], 'in',
            'range' => \yii\helpers\ArrayHelper::getColumn(
                \Yii::$app->authManager->getRoles(), 'name'
            ),
            'message' => 'El rol seleccionado no es válido.'
            ],
            [['estatus'], 'in', 'range' => ['Activo', 'Inactivo', 'Pendiente'], 'message' => 'El estatus seleccionado no es válido.'],
            [['tipo_sangre'], 'in', 'range' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], 'message' => 'Tipo de sangre no válido.'],
            // Si el 'tipo_cedula' SIEMPRE se deriva de 'cedulaFormatted' en beforeSave(),
            // esta regla 'in' es redundante para el flujo normal, pero puede servir como un doble chequeo
            // o si en algún momento 'tipo_cedula' se puede setear de otra forma.
            [['tipo_cedula'], 'in', 'range' => ['V', 'E', 'J', 'G'], 'message' => 'Tipo de cédula no válido.'], 

            [['ver_cedula', 'ver_foto', 'estatus_solvente'], 'in', 'range' => ['0', '1'], 'message' => 'Valor no válido para el campo de verificación.'],

            // 6. Validaciones para carga de archivos (se mantienen igual)
            [['selfieFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024 * 2, 'tooBig' => 'El archivo selfie no debe exceder 2MB.'],
            [['imagenIdentificacionFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxSize' => 1024 * 1024 * 5, 'tooBig' => 'La imagen de identificación no debe exceder 5MB.'],
            [['videoFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'mp4, mov', 'maxSize' => 1024 * 1024 * 20, 'tooBig' => 'El video no debe exceder 20MB.'],

            // 7. Campos que almacenan la ruta de los archivos (TEXT en DB) (se mantienen igual)
            [['selfie', 'imagen_identificacion', 'video', 'qr'], 'string', 'max' => 255],

            // 8. Campos seguros (timestamps)
            // CAMBIO: 'cedula' se marca como 'safe'. Esto le dice a Yii que está bien si el valor de 'cedula'
            // se modifica programáticamente (en 'beforeSave()') y no directamente desde un input del formulario.
            [['created_at', 'updated_at', 'deleted_at', 'cedula', 'fechanac','clinica_id','asesor_id'], 'safe'], // <-- ¡'cedula' AHORA ESTÁ AQUÍ!
            [['codigoAsesor'], 'safe'],
            
            // 9. Validaciones de Existencia (Claves Foráneas) (se mantienen igual)
            [['clinica_id'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['clinica_id' => 'id'], 'message' => 'La clínica seleccionada no existe.'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Planes::class, 'targetAttribute' => ['plan_id' => 'id'], 'message' => 'El plan seleccionado no existe.'],
            [['asesor_id'], 'exist', 'skipOnError' => true, 'targetClass' => AgenteFuerza::class, 'targetAttribute' => ['asesor_id' => 'idusuario'], 'message' => 'El asesor seleccionado no existe.',
                'when' => function ($model) {
                    return $model->asesor_id !== null;
                }   
            ],
            //[['asesor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Agente::class, 'targetAttribute' => ['asesor_id' => 'id'], 'message' => 'El asesor seleccionado no existe.'],
            [['contrato_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contratos::class, 'targetAttribute' => ['contrato_id' => 'id'], 'message' => 'El contrato seleccionado no existe.'],
            [['user_login_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_login_id' => 'id'], 'message' => 'El usuario de login no existe.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    /*public function attributeLabels()
    {
        // CAMBIO: Añadimos una etiqueta amigable para 'cedulaFormatted'
        // Esto hará que el campo en el formulario se muestre con "Cédula de Identidad"
        // en lugar de "Cedula Formatted".
        return array_merge(parent::attributeLabels(), [
            'cedulaFormatted' => 'Cédula de Identidad',
        ]);
    }*/

    /**
     * Este método se ejecuta AUTOMÁTICAMENTE después de que un registro del modelo
     * es cargado desde la base de datos (por ejemplo, al editar un usuario).
     *
     * Su propósito es reconstruir el formato completo de la cédula (V-12345678)
     * a partir del número entero ('cedula') y el prefijo ('tipo_cedula')
     * que están almacenados en la base de datos.
     * Esto es para que el formulario de edición muestre la cédula con el formato esperado.
     */

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



    /**public function afterFind()
    {
        parent::afterFind(); // Siempre llama al método padre.

        // Si tenemos un número de cédula y un tipo de cédula en el modelo
        // (lo que debería ser cierto si los datos provienen de la DB),
        // los concatenamos y asignamos a la propiedad temporal 'cedulaFormatted'.
        if ($this->cedula !== null && $this->tipo_cedula !== null) {
            $this->cedulaFormatted = $this->tipo_cedula . '-' . $this->cedula;
        }
    }*/

    /**
     * Este método se ejecuta AUTOMÁTICAMENTE ANTES de que el modelo sea guardado
     * en la base de datos (tanto para creación como para actualización).
     *
     * Su función principal es tomar el valor de 'cedulaFormatted' (ej. "V-12345678")
     * que viene del formulario, y separarlo en sus dos componentes:
     * 1. El prefijo (ej. "V") para la columna 'tipo_cedula' (TEXT).
     * 2. El número (ej. "12345678") para la columna 'cedula' (INTEGER).
     */
    /*public function beforeSave($insert)
    {
        // Siempre llama al método padre. Si el padre retorna false, detenemos el guardado.
        if (parent::beforeSave($insert)) {
            // Solo procedemos si la propiedad 'cedulaFormatted' tiene un valor.
            // Esto asegura que no intentamos procesar una cédula vacía o nula.
            if ($this->cedulaFormatted !== null) {
                // Paso 1: Extraer el prefijo (V, E, J, G) de 'cedulaFormatted'.
                // 'preg_match' busca un patrón. '^([VEJG])' busca una de esas letras al inicio.
                preg_match('/^([VEJG])/', $this->cedulaFormatted, $matches);
                if (isset($matches[1])) {
                    // Si se encuentra un prefijo, lo asignamos a la columna 'tipo_cedula' del modelo.
                    $this->tipo_cedula = $matches[1];
                } else {
                    // Si no se encuentra un prefijo válido (aunque la validación 'match'
                    // en rules() debería evitar esto), puedes establecer un valor por defecto
                    // o manejar este error según tu lógica de negocio.
                    $this->tipo_cedula = ''; // Valor por defecto si no se puede extraer.
                }

                // Paso 2: Extraer solo los números de 'cedulaFormatted' y convertirlos a entero.
                // 'preg_replace('/[^0-9]/', '', $this->cedulaFormatted)' elimina todos los caracteres
                // que NO sean dígitos (0-9). Por ejemplo, de "V-12345678", resultará "12345678".
                // '(int)' convierte ese string de números en un valor de tipo entero, listo para la DB.
                $this->cedula = (int)preg_replace('/[^0-9]/', '', $this->cedulaFormatted);
            }
            
            // Retorna true para permitir que el proceso de guardado en la base de datos continúe.
            return true;
        }
        // Si la validación o alguna condición en el método padre falla, retorna false
        // para abortar el guardado.
        return false;
    }*/

    // --- RELACIONES (MÉTODOS GET) ---
    // Estos métodos de relación no necesitan cambios y se mantienen tal cual.
    public function getClinica() { return $this->hasOne(RmClinica::class, ['id' => 'clinica_id']); }
    public function getPlan() { return $this->hasOne(Planes::class, ['id' => 'plan_id']); }
    public function getAsesor() { return $this->hasOne(Agente::class, ['id' => 'asesor_id']); }
    public function getContrato() { return $this->hasOne(Contratos::class, ['id' => 'contrato_id']); }
    public function getUserLogin() { return $this->hasOne(User::class, ['id' => 'user_login_id']); }
}
