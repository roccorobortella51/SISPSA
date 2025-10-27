<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "sis_siniestro".
 *
 * @property int $id
 * @property int $idclinica
 * @property string $fecha
 * @property string $hora
 * @property string $idbaremo
 * @property int $atendido
 * @property string|null $fecha_atencion
 * @property string|null $hora_atencion
 * @property int $iduser
 * @property string|null $descripcion
 * @property int $es_cita (0=Siniestro, 1=Cita)
 * @property float|null $costo_total
 * @property string|null $imagen_recipe
 * @property string|null $imagen_informe
 * @property string $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 *
 * @property RmClinica $clinica
 * @property UserDatos $afiliado
 * @property Baremo[] $baremos
 * @property SisSiniestroBaremo[] $sisSiniestroBaremos
 * @property SisConsulta[] $sisConsultas
 */
class SisSiniestro extends \yii\db\ActiveRecord
{

    public $imagenRecipeFile;
    public $imagenInformeFile;


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
            [['atendido', 'es_cita'], 'default', 'value' => 0],
            [['idclinica', 'fecha', 'hora', 'iduser', 'descripcion'], 'required'],
            [['costo_total'], 'number'],
            [['idclinica', 'atendido', 'iduser', 'es_cita'], 'default', 'value' => null],
            [['idbaremo'], 'default', 'value' => ''],
            [['idclinica', 'atendido', 'iduser', 'es_cita'], 'integer'],
            [['idbaremo'], 'safe'], // Aceptamos cualquier valor y lo manejamos en beforeValidate
            [['fecha', 'fecha_atencion', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['descripcion'], 'string'],
            [['hora', 'hora_atencion'], 'string', 'max' => 10],
            [['idclinica'], 'exist', 'skipOnError' => true, 'targetClass' => RmClinica::class, 'targetAttribute' => ['idclinica' => 'id']],

            [['imagen_recipe', 'imagen_informe'], 'string', 'max' => 255],

           [['imagenRecipeFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxSize' => 1024 * 1024 * 2, 'tooBig' => 'El archivo no debe exceder 2MB.'],
           [['imagenInformeFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, pdf', 'maxSize' => 1024 * 1024 * 5, 'tooBig' => 'La imagen no debe exceder 5MB.'],

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
            'idclinica' => 'Clínica',
            'fecha' => 'Fecha',
            'hora' => 'Hora',
            'idbaremo' => 'Baremo(s)',
            'atendido' => 'Atendido',
            'fecha_atencion' => 'Fecha Atención',
            'hora_atencion' => 'Hora Atención',
            'iduser' => 'ID Usuario',
            'descripcion' => 'Descripción',
            'es_cita' => 'Es Cita',
            'costo_total' => 'Costo Total',
            'imagen_recipe' => 'URL Receta Médica',
            'imagen_informe' => 'URL Informe Médico',
            'created_at' => 'Creado El',
            'updated_at' => 'Actualizado El',
            'deleted_at' => 'Eliminado El',
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
        return $this->hasOne(UserDatos::class, ['id' => 'iduser']);
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
    
    /**
     * Valida los baremos seleccionados contra las restricciones del plan
     * @param array $baremoIds Array de IDs de baremos a validar
     * @param int $userId ID del usuario/afiliado
     * @param int $esCita 0=Siniestro, 1=Cita (Permite omitir ciertas validaciones)
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validarBaremosConPlan($baremoIds, $userId, $esCita = 0)
    {
        $errors = [];
        
        if (empty($baremoIds) || !is_array($baremoIds)) {
            return ['valid' => true, 'errors' => []];
        }
        
        // Obtener datos del afiliado
        $afiliado = UserDatos::findOne($userId);
        if (!$afiliado || !$afiliado->plan_id) {
            $errors[] = 'No se pudo obtener la información del plan del afiliado.';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Obtener el contrato del afiliado para la fecha de inicio
        $contrato = Contratos::find()
            ->where(['user_id' => $userId])
            ->andWhere(['estatus' => 'Activo'])
            ->orderBy(['created_at' => SORT_DESC])
            ->one();
        
        if (!$contrato) {
            // Nota: Podrías relajar esta validación si una Cita puede hacerse sin contrato activo, 
            // pero por ahora, la mantenemos como crítica.
            $errors[] = 'No se encontró un contrato activo para el afiliado.';
            return ['valid' => false, 'errors' => $errors];
        }
        
        $fechaInicioContrato = new \DateTime($contrato->fecha_ini);
        $fechaActual = new \DateTime();
        
        // Validar cada baremo
        foreach ($baremoIds as $baremoId) {
            if (empty($baremoId)) continue;
            
            // Obtener la configuración del baremo en el plan
            $planItemCobertura = PlanesItemsCobertura::find()
                ->where(['plan_id' => $afiliado->plan_id, 'baremo_id' => $baremoId])
                ->one();
            
            if (!$planItemCobertura) {
                $baremo = Baremo::findOne($baremoId);
                $nombreBaremo = $baremo ? $baremo->nombre_servicio : "ID: $baremoId";
                $errors[] = "El baremo '$nombreBaremo' no está configurado en el plan del afiliado.";
                continue;
            }
            
            $baremo = Baremo::findOne($baremoId);
            $nombreBaremo = $baremo ? $baremo->nombre_servicio : "ID: $baremoId";
            
            // ----------------------------------------------------------------------------------
            // APLICACIÓN DEL MODO CITA (NUEVO)
            // Si el registro es una Cita (es_cita = 1), omitimos todas las validaciones de 
            // Plazo y Límite de uso. El propósito de la cita es reservar el servicio.
            // ----------------------------------------------------------------------------------
            if ($esCita == 1) {
                continue; 
            }
            
            // ----------------------------------------------------------------------------------
            // LÓGICA DE VALIDACIÓN EXISTENTE (SOLO PARA SINIESTRO: es_cita = 0)
            // ----------------------------------------------------------------------------------

            // VALIDACIÓN: Cantidad límite de uso (ANUAL) y Plazo de Espera después del límite
            if ($planItemCobertura->cantidad_limite !== null && $planItemCobertura->cantidad_limite > 0) {
                // Calcular el período anual actual desde la fecha de afiliación
                $anioActual = self::calcularAnioVigencia($fechaInicioContrato, $fechaActual);
                
                // Calcular las fechas de inicio y fin del año de vigencia actual
                $inicioAnioVigencia = clone $fechaInicioContrato;
                $inicioAnioVigencia->modify("+{$anioActual} years");
                
                $finAnioVigencia = clone $inicioAnioVigencia;
                $finAnioVigencia->modify("+1 year -1 day");
                
                // Contar cuántas veces se ha usado este baremo en el año de vigencia actual
                // Solo contamos SisSiniestro que NO son citas (es_cita = 0)
                $siniestrosUsados = SisSiniestroBaremo::find()
                    ->joinWith(['siniestro' => function($query) {
                        $query->andWhere(['sis_siniestro.es_cita' => 0]); // <-- Filtro añadido
                    }])
                    ->where(['sis_siniestro_baremo.baremo_id' => $baremoId])
                    ->andWhere(['sis_siniestro.iduser' => $userId])
                    ->andWhere(['IS', 'sis_siniestro.deleted_at', null])
                    ->andWhere(['>=', 'sis_siniestro.fecha', $inicioAnioVigencia->format('Y-m-d')])
                    ->andWhere(['<=', 'sis_siniestro.fecha', $finAnioVigencia->format('Y-m-d')])
                    ->orderBy(['sis_siniestro.fecha' => SORT_DESC])
                    ->all();
                
                $vecesUsado = count($siniestrosUsados);
                
                // Si alcanzó el límite, verificar el plazo de espera
                if ($vecesUsado >= $planItemCobertura->cantidad_limite) {
                    // Si hay plazo de espera configurado
                    if (!empty($planItemCobertura->plazo_espera)) {
                        $plazoEspera = self::parsePlazoEspera($planItemCobertura->plazo_espera);
                        
                        if ($plazoEspera > 0 && !empty($siniestrosUsados)) {
                            // Obtener la fecha del último uso
                            $ultimoSiniestro = $siniestrosUsados[0]->siniestro;
                            $fechaUltimoUso = new \DateTime($ultimoSiniestro->fecha);
                            
                            // Calcular cuándo se puede volver a usar
                            $fechaHabilitacion = clone $fechaUltimoUso;
                            $fechaHabilitacion->modify("+{$plazoEspera} months");
                            
                            // Si aún no ha pasado el plazo de espera
                            if ($fechaActual < $fechaHabilitacion) {
                                $diasRestantes = $fechaActual->diff($fechaHabilitacion)->days;
                                $errors[] = "El baremo '$nombreBaremo' ha alcanzado su límite de uso "
                                          . "({$planItemCobertura->cantidad_limite} veces en el año actual). "
                                          . "Último uso: " . $fechaUltimoUso->format('d/m/Y') . ". "
                                          . "Debe esperar {$planItemCobertura->plazo_espera} desde el último uso. "
                                          . "Podrá utilizarlo nuevamente a partir del " . $fechaHabilitacion->format('d/m/Y') 
                                          . " (faltan $diasRestantes días).";
                            }
                            // Si ya pasó el plazo, se puede usar de nuevo (no hay error)
                        } else {
                            // Tiene límite pero no tiene plazo de espera
                            $errors[] = "El baremo '$nombreBaremo' ha alcanzado su límite anual de uso "
                                      . "({$planItemCobertura->cantidad_limite} veces). "
                                      . "Ya se ha utilizado $vecesUsado veces en el período. "
                                      . "Se renovará el " . $finAnioVigencia->modify('+1 day')->format('d/m/Y') . ".";
                        }
                    } else {
                        // Tiene límite pero no tiene plazo de espera
                        $errors[] = "El baremo '$nombreBaremo' ha alcanzado su límite anual de uso "
                                  . "({$planItemCobertura->cantidad_limite} veces). "
                                  . "Ya se ha utilizado $vecesUsado veces en el período. "
                                  . "Se renovará el " . $finAnioVigencia->modify('+1 day')->format('d/m/Y') . ".";
                    }
                }
                // Si no ha alcanzado el límite, puede usar libremente
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Parsea el plazo de espera en formato texto a número de meses
     * @param string $plazoEspera Ej: "4 meses", "1 mes", "6 months"
     * @return int Número de meses
     */
    private static function parsePlazoEspera($plazoEspera)
    {
        if (empty($plazoEspera)) {
            return 0;
        }
        
        // Extraer el número del texto
        preg_match('/\d+/', $plazoEspera, $matches);
        
        if (!empty($matches)) {
            return (int)$matches[0];
        }
        
        return 0;
    }
    
    /**
     * Calcula en qué año de vigencia se encuentra el afiliado
     * @param \DateTime $fechaInicio Fecha de inicio del contrato
     * @param \DateTime $fechaActual Fecha actual
     * @return int Año de vigencia (0 = primer año, 1 = segundo año, etc.)
     */
    private static function calcularAnioVigencia($fechaInicio, $fechaActual)
    {
        $diferencia = $fechaInicio->diff($fechaActual);
        return $diferencia->y; // Retorna el número de años completos
    }
}
