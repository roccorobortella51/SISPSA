<?php

namespace app\controllers;

use Yii;
use app\models\UserDatos;
use app\models\User;
use app\models\UserDatosSearch;
use app\models\CorporativoUser;
use app\models\Corporativo;
use app\models\AfiliadosReportSearch;
use app\models\UserDatosType;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\UserHelper;
use app\models\RmMunicipio;
use app\models\RmParroquia;
use app\models\RmCiudad;
use app\models\RmEstado;
use app\models\Contratos;
use app\models\RmClinica;
use app\models\CorporativoClinica;
use app\models\Planes;
use yii\base\Security;
use kartik\mpdf\Pdf;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use DateTime;
use app\models\Cuotas;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use app\models\TasaCambio;
use app\models\AgenteFuerza;
use app\models\Dependientes;
use app\models\Receipt;
use app\models\Agente;
use yii\web\Response;


/**
 * UserDatosController implements the CRUD actions for UserDatos model.
 * 
 * LÓGICA DE FECHAS DE CUOTAS:
 * - La fecha de vencimiento de la primera cuota se calcula como:
 *   "día 7 del mes siguiente a la fecha de inicio del contrato"
 * - Ejemplo: Si el contrato inicia el 15/03/2024, la primera cuota vence el 07/04/2024
 */
class UserDatosController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    // ============================================
    // PROFESSIONAL FLASH MESSAGE HELPERS
    // ============================================

    /**
     * Generate a professional Microsoft Fluent Design styled error message for duplicates
     * 
     * @param UserDatos $existing The existing duplicate record
     * @param UserDatos $new The new record being created
     * @param string $type 'regular' or 'underage'
     * @return string HTML formatted error message
     */
    private function generateDuplicateErrorMessage($existing, $new, $type = 'regular')
    {
        $isUnderage = ($type === 'underage');
        $title = $isUnderage ? 'Menor Duplicado Detectado' : 'Duplicado de Afiliado Detectado';
        $icon = $isUnderage ? 'fa-child' : 'fa-exclamation-triangle';
        $color = $isUnderage ? '#e97c00' : '#d13438';
        $colorLight = $isUnderage ? '#fef6e8' : '#fef6f6';
        $colorBg = $isUnderage ? '#fde8d0' : '#fde8e8';

        $viewUrl = Yii::$app->urlManager->createUrl(['user-datos/view', 'id' => $existing->id]);
        $createUrl = Yii::$app->urlManager->createUrl(['user-datos/create']);

        $additionalInfo = '';
        if ($isUnderage && !empty($existing->consecutivo_menor)) {
            $additionalInfo = '
            <span style="font-weight: 500; color: #605e5c;">Consecutivo:</span>
            <span style="color: #1a1a1a; font-weight: 500;">' . $existing->consecutivo_menor . '</span>
        ';
        }

        return '
    <div style="
        display: flex;
        justify-content: center;
        width: 100%;
        margin: 16px 0;
    ">
        <div class="duplicate-error-container" style="
            background: linear-gradient(135deg, ' . $colorLight . ' 0%, ' . $colorBg . ' 100%);
            border-left: 6px solid ' . $color . ';
            border-radius: 8px;
            padding: 24px 28px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
            font-family: \'Segoe UI\', \'Microsoft Sans Serif\', -apple-system, BlinkMacSystemFont, sans-serif;
            max-width: 820px;
            width: 100%;
            position: relative;
            transition: all 0.3s ease;
        ">
            <div style="display: flex; align-items: flex-start; gap: 18px;">
                <!-- Icon Container -->
                <div style="
                    background: ' . $color . ';
                    border-radius: 50%;
                    width: 52px;
                    height: 52px;
                    min-width: 52px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
                ">
                    <i class="fas ' . $icon . '" style="color: white; font-size: 24px;"></i>
                </div>
                
                <!-- Content Container -->
                <div style="flex: 1; min-width: 0;">
                    <!-- Header -->
                    <div style="
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        margin-bottom: 12px;
                        flex-wrap: wrap;
                        gap: 8px;
                    ">
                        <div style="
                            font-size: 17px;
                            font-weight: 600;
                            color: #1a1a1a;
                            letter-spacing: 0.2px;
                        ">
                            <span style="color: ' . $color . '; margin-right: 6px;">●</span>
                            ' . $title . '
                        </div>
                        <span style="
                            font-size: 12px;
                            color: #605e5c;
                            background: rgba(0, 0, 0, 0.05);
                            padding: 2px 12px;
                            border-radius: 12px;
                            font-weight: 500;
                        ">
                            <i class="far fa-clock" style="margin-right: 4px;"></i>
                            ' . date('H:i') . '
                        </span>
                    </div>
                    
                    <!-- Message Body -->
                    <div style="
                        font-size: 14px;
                        color: #323130;
                        line-height: 1.6;
                        margin-bottom: 16px;
                        padding: 14px 18px;
                        background: rgba(255, 255, 255, 0.85);
                        border-radius: 6px;
                        border: 1px solid rgba(0, 0, 0, 0.06);
                        backdrop-filter: blur(4px);
                    ">
                        <div style="margin-bottom: 10px; font-weight: 500; color: #1a1a1a;">
                            <i class="fas fa-info-circle" style="color: ' . $color . '; margin-right: 8px;"></i>
                            Ya existe un afiliado registrado con los siguientes datos:
                        </div>
                        <div style="
                            display: grid;
                            grid-template-columns: auto 1fr;
                            gap: 6px 20px;
                            font-size: 14px;
                            padding-left: 4px;
                        ">
                            <span style="font-weight: 500; color: #605e5c;">Nombre completo:</span>
                            <span style="color: #1a1a1a; font-weight: 500;">' . htmlspecialchars($existing->nombres . ' ' . $existing->apellidos) . '</span>
                            
                            <span style="font-weight: 500; color: #605e5c;">Cédula de Identidad:</span>
                            <span style="color: #1a1a1a; font-weight: 500;">' . htmlspecialchars($existing->tipo_cedula . '-' . $existing->cedula) . '</span>
                            
                            ' . $additionalInfo . '
                            
                            <span style="font-weight: 500; color: #605e5c;">ID de Registro:</span>
                            <span style="color: #0078d4; font-weight: 600; font-family: \'Segoe UI Mono\', monospace;">' . $existing->id . '</span>
                            
                            <span style="font-weight: 500; color: #605e5c;">Fecha de Registro:</span>
                            <span style="color: #1a1a1a;">' . Yii::$app->formatter->asDatetime($existing->created_at, 'dd/MM/yyyy HH:mm') . '</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style="
                        display: flex;
                        gap: 12px;
                        margin-top: 4px;
                        flex-wrap: wrap;
                    ">
                        <a href="' . $viewUrl . '" 
                           style="
                               display: inline-flex;
                               align-items: center;
                               gap: 8px;
                               padding: 9px 24px;
                               background: #0078d4;
                               color: white;
                               text-decoration: none;
                               border-radius: 4px;
                               font-size: 14px;
                               font-weight: 500;
                               transition: all 0.15s ease;
                               border: none;
                               cursor: pointer;
                               box-shadow: 0 2px 4px rgba(0, 120, 212, 0.2);
                           "
                           onmouseover="this.style.backgroundColor=\'#106ebe\'; this.style.boxShadow=\'0 4px 8px rgba(0, 120, 212, 0.3)\'"
                           onmouseout="this.style.backgroundColor=\'#0078d4\'; this.style.boxShadow=\'0 2px 4px rgba(0, 120, 212, 0.2)\'">
                            <i class="fas fa-eye" style="font-size: 14px;"></i>
                            Ver Registro Existente
                        </a>
                        <a href="' . $createUrl . '" 
                           style="
                               display: inline-flex;
                               align-items: center;
                               gap: 8px;
                               padding: 9px 24px;
                               background: #f3f2f1;
                               color: #323130;
                               text-decoration: none;
                               border-radius: 4px;
                               font-size: 14px;
                               font-weight: 500;
                               transition: all 0.15s ease;
                               border: 1px solid #d2d0ce;
                               cursor: pointer;
                           "
                           onmouseover="this.style.backgroundColor=\'#e1dfdd\'"
                           onmouseout="this.style.backgroundColor=\'#f3f2f1\'">
                            <i class="fas fa-arrow-left" style="font-size: 14px;"></i>
                            Volver al Formulario
                        </a>
                    </div>
                </div>
                
                <!-- Close Button -->
                <button type="button" 
                        onclick="this.closest(\'.duplicate-error-container\').style.display=\'none\'"
                        style="
                            background: none;
                            border: none;
                            color: #605e5c;
                            font-size: 18px;
                            cursor: pointer;
                            padding: 4px 8px;
                            border-radius: 4px;
                            transition: all 0.15s ease;
                            flex-shrink: 0;
                            line-height: 1;
                        "
                        onmouseover="this.style.backgroundColor=\'rgba(0,0,0,0.06)\'"
                        onmouseout="this.style.backgroundColor=\'transparent\'"
                        aria-label="Cerrar mensaje">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    ';
    }

    /**
     * Generate a professional success message
     * 
     * @param string $title The title of the success message
     * @param string $message The main message content
     * @param string $icon Optional icon class
     * @return string HTML formatted success message
     */
    private function generateSuccessMessage($title, $message, $icon = 'fa-check-circle')
    {
        return '
    <div style="
        display: flex;
        justify-content: center;
        width: 100%;
        margin: 16px 0;
    ">
        <div style="
            background: linear-gradient(135deg, #f0faf0 0%, #e0f0e0 100%);
            border-left: 6px solid #107c10;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(16, 124, 16, 0.10);
            font-family: \'Segoe UI\', \'Microsoft Sans Serif\', sans-serif;
            max-width: 820px;
            width: 100%;
            position: relative;
        ">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                <div style="
                    background: #107c10;
                    border-radius: 50%;
                    width: 44px;
                    height: 44px;
                    min-width: 44px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                ">
                    <i class="fas ' . $icon . '" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="
                        font-size: 16px;
                        font-weight: 600;
                        color: #1a1a1a;
                        margin-bottom: 6px;
                    ">
                        ' . $title . '
                    </div>
                    <div style="
                        font-size: 14px;
                        color: #333333;
                        line-height: 1.5;
                    ">
                        ' . $message . '
                    </div>
                </div>
                <button type="button" 
                        onclick="this.parentElement.parentElement.style.display=\'none\'"
                        style="
                            background: none;
                            border: none;
                            color: #605e5c;
                            font-size: 18px;
                            cursor: pointer;
                            padding: 4px 8px;
                            border-radius: 4px;
                            transition: all 0.15s ease;
                            flex-shrink: 0;
                        "
                        onmouseover="this.style.backgroundColor=\'rgba(0,0,0,0.06)\'"
                        onmouseout="this.style.backgroundColor=\'transparent\'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    ';
    }

    /**
     * Generate a professional warning message
     * 
     * @param string $title The title of the warning message
     * @param string $message The main message content
     * @param string $icon Optional icon class
     * @return string HTML formatted warning message
     */
    private function generateWarningMessage($title, $message, $icon = 'fa-exclamation-circle')
    {
        return '
    <div style="
        display: flex;
        justify-content: center;
        width: 100%;
        margin: 16px 0;
    ">
        <div style="
            background: linear-gradient(135deg, #fffbeb 0%, #fff5cc 100%);
            border-left: 6px solid #e97c00;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(233, 124, 0, 0.10);
            font-family: \'Segoe UI\', \'Microsoft Sans Serif\', sans-serif;
            max-width: 820px;
            width: 100%;
            position: relative;
        ">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                <div style="
                    background: #e97c00;
                    border-radius: 50%;
                    width: 44px;
                    height: 44px;
                    min-width: 44px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                ">
                    <i class="fas ' . $icon . '" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="
                        font-size: 16px;
                        font-weight: 600;
                        color: #1a1a1a;
                        margin-bottom: 6px;
                    ">
                        ' . $title . '
                    </div>
                    <div style="
                        font-size: 14px;
                        color: #333333;
                        line-height: 1.5;
                    ">
                        ' . $message . '
                    </div>
                </div>
                <button type="button" 
                        onclick="this.parentElement.parentElement.style.display=\'none\'"
                        style="
                            background: none;
                            border: none;
                            color: #605e5c;
                            font-size: 18px;
                            cursor: pointer;
                            padding: 4px 8px;
                            border-radius: 4px;
                            transition: all 0.15s ease;
                            flex-shrink: 0;
                        "
                        onmouseover="this.style.backgroundColor=\'rgba(0,0,0,0.06)\'"
                        onmouseout="this.style.backgroundColor=\'transparent\'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    ';
    }

    /**
     * Generate a professional error message
     * 
     * @param string $title The title of the error message
     * @param string $message The main message content
     * @param string $icon Optional icon class
     * @return string HTML formatted error message
     */
    private function generateErrorMessage($title, $message, $icon = 'fa-exclamation-triangle')
    {
        return '
    <div style="
        display: flex;
        justify-content: center;
        width: 100%;
        margin: 16px 0;
    ">
        <div style="
            background: linear-gradient(135deg, #fef6f6 0%, #fde8e8 100%);
            border-left: 6px solid #d13438;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(209, 52, 56, 0.12);
            font-family: \'Segoe UI\', \'Microsoft Sans Serif\', sans-serif;
            max-width: 820px;
            width: 100%;
            position: relative;
        ">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                <div style="
                    background: #d13438;
                    border-radius: 50%;
                    width: 44px;
                    height: 44px;
                    min-width: 44px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                ">
                    <i class="fas ' . $icon . '" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="
                        font-size: 16px;
                        font-weight: 600;
                        color: #1a1a1a;
                        margin-bottom: 6px;
                    ">
                        ' . $title . '
                    </div>
                    <div style="
                        font-size: 14px;
                        color: #333333;
                        line-height: 1.5;
                    ">
                        ' . $message . '
                    </div>
                </div>
                <button type="button" 
                        onclick="this.parentElement.parentElement.style.display=\'none\'"
                        style="
                            background: none;
                            border: none;
                            color: #605e5c;
                            font-size: 18px;
                            cursor: pointer;
                            padding: 4px 8px;
                            border-radius: 4px;
                            transition: all 0.15s ease;
                            flex-shrink: 0;
                        "
                        onmouseover="this.style.backgroundColor=\'rgba(0,0,0,0.06)\'"
                        onmouseout="this.style.backgroundColor=\'transparent\'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    ';
    }

    /**
     * Generate a professional info message
     * 
     * @param string $title The title of the info message
     * @param string $message The main message content
     * @param string $icon Optional icon class
     * @return string HTML formatted info message
     */
    private function generateInfoMessage($title, $message, $icon = 'fa-info-circle')
    {
        return '
    <div style="
        display: flex;
        justify-content: center;
        width: 100%;
        margin: 16px 0;
    ">
        <div style="
            background: linear-gradient(135deg, #f0f6fd 0%, #e5f0fa 100%);
            border-left: 6px solid #0078d4;
            border-radius: 8px;
            padding: 20px 24px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0, 120, 212, 0.10);
            font-family: \'Segoe UI\', \'Microsoft Sans Serif\', sans-serif;
            max-width: 820px;
            width: 100%;
            position: relative;
        ">
            <div style="display: flex; align-items: flex-start; gap: 16px;">
                <div style="
                    background: #0078d4;
                    border-radius: 50%;
                    width: 44px;
                    height: 44px;
                    min-width: 44px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                ">
                    <i class="fas ' . $icon . '" style="color: white; font-size: 20px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="
                        font-size: 16px;
                        font-weight: 600;
                        color: #1a1a1a;
                        margin-bottom: 6px;
                    ">
                        ' . $title . '
                    </div>
                    <div style="
                        font-size: 14px;
                        color: #333333;
                        line-height: 1.5;
                    ">
                        ' . $message . '
                    </div>
                </div>
                <button type="button" 
                        onclick="this.parentElement.parentElement.style.display=\'none\'"
                        style="
                            background: none;
                            border: none;
                            color: #605e5c;
                            font-size: 18px;
                            cursor: pointer;
                            padding: 4px 8px;
                            border-radius: 4px;
                            transition: all 0.15s ease;
                            flex-shrink: 0;
                        "
                        onmouseover="this.style.backgroundColor=\'rgba(0,0,0,0.06)\'"
                        onmouseout="this.style.backgroundColor=\'transparent\'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    ';
    }

    /**
     * Valida los campos del archivo Excel antes de procesarlo
     * @param array $data Los datos del archivo Excel
     * @return array Array con errores encontrados
     */
    private function validateExcelData($data)
    {
        $errors = [];
        $rowNumber = 1;

        foreach ($data as $row) {
            $rowNumber++;
            $rowErrors = [];

            $isEmptyRow = true;
            foreach ($row as $cellValue) {
                if ($cellValue !== null && $cellValue !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                continue;
            }

            if (empty($row['A'])) {
                $rowErrors[] = 'Email es obligatorio';
            } elseif (!filter_var($row['A'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Email no tiene formato válido';
            }

            if (empty($row['B'])) {
                $rowErrors[] = 'Teléfono es obligatorio';
            } elseif (!preg_match('/^[0-9+\-\s\(\)]{7,15}$/', $row['B'])) {
                $rowErrors[] = 'Teléfono no tiene formato válido';
            }

            if (empty($row['C'])) {
                $rowErrors[] = 'Nombres es obligatorio';
            } elseif (strlen($row['C']) < 2) {
                $rowErrors[] = 'Nombres debe tener al menos 2 caracteres';
            }

            if (empty($row['D'])) {
                $rowErrors[] = 'Apellidos es obligatorio';
            } elseif (strlen($row['D']) < 2) {
                $rowErrors[] = 'Apellidos debe tener al menos 2 caracteres';
            }

            if (empty($row['E'])) {
                $rowErrors[] = 'Tipo de cédula es obligatorio';
            } elseif (!in_array(strtoupper($row['E']), ['V', 'E', 'P', 'J'])) {
                $rowErrors[] = 'Tipo de cédula debe ser V, E, P o J';
            }

            if (empty($row['F']) && !is_numeric($row['F'])) {
                $rowErrors[] = 'Cédula es obligatoria';
            } elseif (!is_numeric($row['F']) || strlen($row['F']) < 6 || strlen($row['F']) > 10) {
                $rowErrors[] = 'Cédula debe ser numérica y tener entre 6 y 10 dígitos';
            }

            if (empty($row['G'])) {
                $rowErrors[] = 'Fecha de nacimiento es obligatoria';
            } else {
                $fechaNacimiento = DateTime::createFromFormat('d/m/Y', $row['G']);
                if (!$fechaNacimiento) {
                    $rowErrors[] = 'Fecha de nacimiento debe tener formato DD/MM/YYYY';
                } else {
                    $hoy = new DateTime();
                    $edad = $hoy->diff($fechaNacimiento)->y;
                    if ($edad < 0 || $edad > 120) {
                        $rowErrors[] = 'Fecha de nacimiento no es válida (edad entre 0 y 120 años)';
                    }
                }
            }

            if (empty($row['H'])) {
                $rowErrors[] = 'Sexo es obligatorio';
            } elseif (!in_array(strtoupper($row['H']), ['M', 'F', 'MASCULINO', 'FEMENINO'])) {
                $rowErrors[] = 'Sexo debe ser M, F, Masculino o Femenino';
            }

            if (!empty($row['I'])) {
                $tiposSangre = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                if (!in_array(strtoupper($row['I']), $tiposSangre)) {
                    $rowErrors[] = 'Tipo de sangre debe ser uno de: A+, A-, B+, B-, AB+, AB-, O+, O-';
                }
            }

            if (empty($row['J'])) {
                $rowErrors[] = 'Estado - municipio - parroquia es obligatorio';
            } elseif (strlen($row['J']) < 2) {
                $rowErrors[] = 'Estado - municipio - parroquia debe tener al menos 2 caracteres';
            }

            if (empty($row['K'])) {
                $rowErrors[] = 'Ciudad es obligatorio';
            } elseif (strlen($row['K']) < 2) {
                $rowErrors[] = 'Ciudad debe tener al menos 2 caracteres';
            }

            if (empty($row['L'])) {
                $rowErrors[] = 'Dirección es obligatoria';
            } elseif (strlen($row['L']) < 1) {
                $rowErrors[] = 'Dirección debe tener al menos 10 caracteres';
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'errors' => $rowErrors,
                    'data' => $row
                ];
            }
        }

        return $errors;
    }

    /**
     * Valida que no existan duplicados de email o cédula en el archivo y en la base de datos
     * @param array $data Los datos del archivo Excel
     * @return array Array con errores encontrados
     */
    private function validateDuplicates($data)
    {
        $errors = [];
        $emails = [];
        $cedulas = [];
        $rowNumber = 1;

        foreach ($data as $row) {
            $rowNumber++;

            $isEmptyRow = true;
            foreach ($row as $cellValue) {
                if ($cellValue !== null && $cellValue !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                continue;
            }

            $email = trim($row['A']);
            $cedula = trim($row['F']);
            $tipoCedula = trim($row['E']);

            if (!empty($email)) {
                if (in_array($email, $emails)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['Email duplicado en el archivo'],
                        'data' => $row
                    ];
                } else {
                    $emails[] = $email;
                }
            }

            if (!empty($cedula)) {
                $cedulaCompleta = $tipoCedula . '-' . $cedula;
                if (in_array($cedulaCompleta, $cedulas)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['Cédula duplicada en el archivo'],
                        'data' => $row
                    ];
                } else {
                    $cedulas[] = $cedulaCompleta;
                }
            }
        }

        if (!empty($emails)) {
            $existingEmails = UserDatos::find()
                ->where(['email' => $emails])
                ->select('email')
                ->column();

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;

                $email = trim($row['A']);
                if (!empty($email) && in_array($email, $existingEmails)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['Email ya existe en la base de datos'],
                        'data' => $row
                    ];
                }
            }
        }

        if (!empty($cedulas)) {
            $existingCedulas = UserDatos::find()
                ->where(['cedula' => array_map(function ($cedula) {
                    return explode('-', $cedula)[1] ?? $cedula;
                }, $cedulas)])
                ->select('cedula')
                ->column();

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;

                $cedula = trim($row['F']);
                if (!empty($cedula) && in_array($cedula, $existingCedulas)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => ['Cédula ya existe en la base de datos'],
                        'data' => $row
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * Genera un reporte de validación en formato HTML para mostrar al usuario
     * @param array $errors Array de errores de validación
     * @param int $totalRows Total de filas procesadas
     * @return string HTML del reporte
     */
    private function generateValidationReport($errors, $totalRows)
    {
        $groupedErrors = [];
        foreach ($errors as $error) {
            $row = $error['row'];
            if (!isset($groupedErrors[$row])) {
                $groupedErrors[$row] = [
                    'row' => $row,
                    'errors' => [],
                    'data' => $error['data']
                ];
            }
            $groupedErrors[$row]['errors'] = array_merge($groupedErrors[$row]['errors'], $error['errors']);
        }

        ksort($groupedErrors);

        $validRows = $totalRows - count($groupedErrors);
        $errorRows = count($groupedErrors);

        $html = '<div class="validation-report">';
        $html .= '<h3>Reporte de Validación del Archivo Excel</h3>';
        $html .= '<div class="summary">';
        $html .= '<p><strong>Resumen:</strong></p>';
        $html .= '<ul>';
        $html .= '<li>Total de filas procesadas: ' . $totalRows . '</li>';
        $html .= '<li>Filas válidas: <span style="color: green;">' . $validRows . '</span></li>';
        $html .= '<li>Filas con errores: <span style="color: red;">' . $errorRows . '</span></li>';
        $html .= '<li>Total de errores encontrados: <span style="color: red;">' . count($errors) . '</span></li>';
        $html .= '</ul>';
        $html .= '</div>';

        if (!empty($groupedErrors)) {
            $html .= '<div class="errors">';
            $html .= '<h4>Errores encontrados por fila:</h4>';
            $html .= '<table class="table table-bordered table-striped">';
            $html .= '<thead><tr><th>Fila</th><th>Errores (' . count($errors) . ' total)</th><th>Datos del Registro</th></tr></thead>';
            $html .= '<tbody>';

            foreach ($groupedErrors as $groupedError) {
                $html .= '<tr>';
                $html .= '<td><strong>' . $groupedError['row'] . '</strong></td>';
                $html .= '<td><ul>';
                foreach ($groupedError['errors'] as $fieldError) {
                    $html .= '<li style="color: red;">' . htmlspecialchars($fieldError) . '</li>';
                }
                $html .= '</ul></td>';
                $html .= '<td><small>';
                $html .= 'A: ' . htmlspecialchars($groupedError['data']['A'] ?? '') . '<br>';
                $html .= 'B: ' . htmlspecialchars($groupedError['data']['B'] ?? '') . '<br>';
                $html .= 'C: ' . htmlspecialchars($groupedError['data']['C'] ?? '') . '<br>';
                $html .= 'D: ' . htmlspecialchars($groupedError['data']['D'] ?? '') . '<br>';
                $html .= 'E: ' . htmlspecialchars($groupedError['data']['E'] ?? '') . '<br>';
                $html .= 'F: ' . htmlspecialchars($groupedError['data']['F'] ?? '') . '<br>';
                $html .= 'G: ' . htmlspecialchars($groupedError['data']['G'] ?? '') . '<br>';
                $html .= 'H: ' . htmlspecialchars($groupedError['data']['H'] ?? '') . '<br>';
                $html .= 'I: ' . htmlspecialchars($groupedError['data']['I'] ?? '') . '<br>';
                $html .= 'J: ' . htmlspecialchars($groupedError['data']['J'] ?? '') . '<br>';
                $html .= 'K: ' . htmlspecialchars($groupedError['data']['K'] ?? '') . '<br>';
                $html .= 'L: ' . htmlspecialchars($groupedError['data']['L'] ?? '');
                $html .= '</small></td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Genera y descarga una plantilla Excel de ejemplo
     * @return \yii\web\Response
     */
    public function actionDownloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'A1' => 'Email',
            'B1' => 'Teléfono',
            'C1' => 'Nombres',
            'D1' => 'Apellidos',
            'E1' => 'Tipo Cédula',
            'F1' => 'Cédula',
            'G1' => 'Fecha Nacimiento',
            'H1' => 'Sexo',
            'I1' => 'Tipo Sangre',
            'J1' => 'Estado ID',
            'K1' => 'Municipio ID',
            'L1' => 'Parroquia ID',
            'M1' => 'Ciudad ID',
            'N1' => 'Dirección'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $exampleData = [
            'A2' => 'ejemplo@email.com',
            'B2' => '0412-1234567',
            'C2' => 'Juan',
            'D2' => 'Pérez',
            'E2' => 'V',
            'F2' => '12345678',
            'G2' => '15/03/1990',
            'H2' => 'M',
            'I2' => 'O+',
            'J2' => '1',
            'K2' => '1',
            'L2' => '1',
            'M2' => '1',
            'N2' => 'Av. Principal, Casa #123'
        ];

        foreach ($exampleData as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ];

        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = Yii::getAlias('@runtime/template_afiliados.xlsx');
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, 'plantilla_afiliados.xlsx', [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function ($event) use ($tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        });
    }

    public function actionMasivo()
    {
        $modelContrato = new Contratos();
        $model = new UserDatos();

        if ($this->request->isPost && $model->load($this->request->post()) && $modelContrato->load($this->request->post())) {
            $masivoFiles = UploadedFile::getInstancesByName('UserDatos[masivoFile]');

            if (empty($masivoFiles) || !$masivoFiles[0]->tempName) {
                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                    'Error al Subir el Archivo',
                    'No se ha subido ningún archivo o el archivo está corrupto. Por favor, verifique el archivo e intente nuevamente.'
                ));
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
                ]);
            }

            $uploadedFile = $masivoFiles[0];
            $filePath = Yii::getAlias('@app/web/uploads/masivoFiles/' . $uploadedFile->baseName . '.' . $uploadedFile->extension);

            if (!$uploadedFile->saveAs($filePath)) {
                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                    'Error al Guardar el Archivo',
                    'Ocurrió un error al guardar el archivo subido en el servidor. Por favor, intente nuevamente.'
                ));
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
                ]);
            }

            $clinica_id = $model->clinica_id;
            $plan_id = $modelContrato->plan_id;

            $model->plan_id = $plan_id;

            if ($model->plan === null) {
                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                    'Error de Datos',
                    'No se pudo cargar el precio porque el Plan ID (' . $plan_id . ') asociado al modelo principal no existe. Verifique que el ID del Plan seleccionado sea válido en la tabla "planes".'
                ));
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
                ]);
            }

            $monto = $model->plan->precio;
            $fecha_ini = $modelContrato->fecha_ini;
            $fecha_ven = $modelContrato->fecha_ven;
            $fechaCreacion = date('Y-m-d H:i:s');

            try {
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                $highestRow = $sheet->getHighestDataRow();
                $range = 'A1:L' . $highestRow;

                $sheetData = $sheet->rangeToArray(
                    $range,
                    null,
                    true,
                    true,
                    true
                );

                $filteredData = [];
                foreach ($sheetData as $row) {
                    $isEmptyRow = true;
                    foreach ($row as $cellValue) {
                        if ($cellValue !== null && $cellValue !== '') {
                            $isEmptyRow = false;
                            break;
                        }
                    }
                    if (!$isEmptyRow) {
                        $filteredData[] = $row;
                    }
                }

                if (!empty($filteredData)) {
                    $headers = array_shift($filteredData);
                }

                $validationErrors = $this->validateExcelData($filteredData);
                $duplicateErrors = $this->validateDuplicates($filteredData);

                $allErrors = array_merge($validationErrors, $duplicateErrors);

                if (!empty($allErrors)) {
                    $validationReport = $this->generateValidationReport($allErrors, count($filteredData));

                    Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                        'Errores de Validación en el Archivo',
                        'El archivo contiene errores que deben ser corregidos antes de continuar. Por favor, revise el reporte detallado a continuación.'
                    ) . $validationReport);

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                    return $this->render('masivo', [
                        'model' => $model,
                        'modelContrato' => $modelContrato,
                    ]);
                }

                $successCount = 0;
                $errorCount = 0;
                $errorMessages = [];

                foreach ($filteredData as $row) {
                    $contrato = new Contratos();
                    $contrato->clinica_id = $clinica_id;
                    $contrato->plan_id = $plan_id;
                    $contrato->monto = $monto;
                    $contrato->fecha_ini = $fecha_ini;
                    $contrato->fecha_ven = $fecha_ven;
                    $contrato->created_at = $fechaCreacion;
                    $contrato->estatus = 'Creado';

                    if (!$contrato->save()) {
                        $errorCount++;
                        $errorMessages[] = 'Error al guardar el contrato para la fila ' . ($successCount + $errorCount + 1) . ': ' . implode(', ', $contrato->getErrorSummary(true));
                        continue;
                    }

                    $model = new UserDatos();

                    $model->role = 'afiliado';
                    $model->estatus = 'Creado';
                    $model->user_datos_type_id = 1;
                    $model->email = $row['A'];
                    $model->telefono = $row['B'];
                    $model->nombres = $row['C'];
                    $model->apellidos = $row['D'];
                    $model->tipo_cedula = $row['E'];
                    $model->cedula = $row['F'];
                    $fechaNacimiento = DateTime::createFromFormat('d/m/Y', $row['G']);
                    $model->fechanac = $fechaNacimiento->format('Y-m-d');
                    $model->sexo = $row['H'];
                    $model->tipo_sangre = $row['I'];
                    $explodeEstado = explode(' - ', $row['J']);
                    $model->estado = $explodeEstado[0];
                    $model->municipio = $explodeEstado[1];
                    $model->parroquia = $explodeEstado[2];
                    $explodeCiudad = explode(' - ', $row['K']);
                    $model->ciudad = $explodeCiudad[1];
                    $model->direccion = $row['L'];
                    $model->contrato_id = $contrato->id;
                    $model->clinica_id = $clinica_id;
                    $model->plan_id = $plan_id;
                    $model->created_at = $fechaCreacion;
                    $model->codigoValidacion = UserHelper::getInstance()->generarCodigoValidacion();

                    // Check for duplicates before saving
                    if ($model->checkAffiliateDuplicate()) {
                        $errorCount++;
                        $errorMessages[] = 'El afiliado con cédula ' . $model->tipo_cedula . '-' . $model->cedula . ' ya existe en el sistema. Fila: ' . ($successCount + $errorCount + 1);
                        continue;
                    }

                    if ($model->save()) {
                        $contrato->user_id = $model->id;
                        $contrato->save(false);

                        $pass = 'sispsa' . $model->cedula;
                        $modelUser = new User();
                        $modelUser->username = $model->email;
                        $modelUser->password_hash = User::setPassword($pass);
                        $modelUser->auth_key = User::generateAuthKey();
                        $modelUser->email = $model->email;
                        $modelUser->status = 1;

                        if ($modelUser->save()) {
                            $auth = Yii::$app->authManager;
                            $roleName = 'afiliado';
                            $role = $auth->getRole($roleName);
                            if ($role) {
                                try {
                                    $auth->revokeAll($modelUser->id);
                                    $auth->assign($role, $modelUser->id);
                                    Yii::$app->cache->flush();
                                    $model->user_login_id = $modelUser->id;
                                    $model->save();
                                } catch (\Exception $e) {
                                    Yii::error("Error al asignar el rol: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                                }
                            }
                        }
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errorMessages[] = 'Error al guardar el afiliado: ' . implode(', ', $model->getErrorSummary(true));
                    }
                }

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                if ($successCount > 0 && $errorCount == 0) {
                    Yii::$app->session->setFlash('success', $this->generateSuccessMessage(
                        'Importación Exitosa',
                        'Se han importado <strong>' . $successCount . '</strong> afiliados correctamente.'
                    ));
                } elseif ($successCount > 0 && $errorCount > 0) {
                    Yii::$app->session->setFlash('warning', $this->generateWarningMessage(
                        'Importación Parcial',
                        'Se importaron <strong>' . $successCount . '</strong> afiliados correctamente, pero <strong>' . $errorCount . '</strong> registros presentaron errores.<br><br>' .
                            '<strong>Detalles de los errores:</strong><br>' . implode('<br>', $errorMessages)
                    ));
                } else {
                    Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                        'Error en la Importación',
                        'No se pudo importar ningún afiliado. Se encontraron <strong>' . $errorCount . '</strong> errores.<br><br>' .
                            '<strong>Detalles de los errores:</strong><br>' . implode('<br>', $errorMessages)
                    ));
                }

                return $this->redirect(['index']);
            } catch (Exception $e) {
                Yii::error('Error al procesar el archivo Excel: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                    'Error al Leer el Archivo Excel',
                    'Ocurrió un error al procesar el archivo: ' . $e->getMessage()
                ));
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } catch (\Exception $e) {
                Yii::error('Un error inesperado ocurrió: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                    'Error Inesperado',
                    'Ocurrió un error inesperado al procesar el archivo: ' . $e->getMessage()
                ));
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            return $this->render('masivo', [
                'model' => $model,
                'modelContrato' => $modelContrato,
            ]);
        }

        return $this->render('masivo', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    /**
     * Lists all UserDatos models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['ilike', 'user_datos.role', 'Afiliado']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndexClinicas($clinica_id = "")
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'user_datos.clinica_id', $clinica_id]);
        $dataProvider->query->andFilterWhere(['ilike', 'user_datos.role', 'Afiliado']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserDatos model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $estado = $model->estado;
        $municipio = $model->municipio;
        $parroquia = $model->parroquia;
        $ciudad = $model->ciudad;

        if (!empty($estado) && is_numeric($estado)) {
            $estadoModel = RmEstado::findOne($estado);
            $estado = $estadoModel ? $estadoModel->nombre : $estado;
        }
        if (!empty($municipio) && is_numeric($municipio)) {
            $municipioModel = RmMunicipio::findOne($municipio);
            $municipio = $municipioModel ? $municipioModel->nombre : null;
        }
        if (!empty($parroquia) && is_numeric($parroquia)) {
            $parroquiaModel = RmParroquia::findOne($parroquia);
            $parroquia = $parroquiaModel ? $parroquiaModel->nombre : null;
        }
        if (!empty($ciudad) && is_numeric($ciudad)) {
            $ciudadModel = RmCiudad::findOne($ciudad);
            $ciudad = $ciudadModel ? $ciudadModel->nombre : null;
        }

        return $this->render('view', [
            'model' => $model,
            'estado' => $estado,
            'municipio' => $municipio,
            'parroquia' => $parroquia,
            'ciudad' => $ciudad,
        ]);
    }

    /**
     * Creates a new UserDatos model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $modelUser = new User();
        $model = new UserDatos();
        $modelContrato = new Contratos();
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->codigoValidacion = UserHelper::getInstance()->generarCodigoValidacion();
        $model->role = 'afiliado';
        $model->estatus = 'Creado';

        $rol = UserHelper::getMyRol();
        if ($rol == "Agente") {
            $agenteId = UserHelper::getAgenteId();
            if ($agenteId) {
                $model->agencia_id = $agenteId;
                Yii::info("Automatically set agencia_id = {$agenteId} for Agente user creating affiliate", __METHOD__);
            }
        }

        if ($model->estatus_solvente == "" || $model->estatus_solvente == null) {
            $model->estatus_solvente = "No";
        }

        if ($model->load($this->request->post()) && $modelContrato->load($this->request->post())) {

            if (!empty($modelContrato->fecha_ini)) {
                $fechaIni = new \DateTime($modelContrato->fecha_ini);
                $fechaIni->modify('+1 year');
                $fechaIni->modify('-1 day');
                $modelContrato->fecha_ven = $fechaIni->format('Y-m-d');
                Yii::info("Setting fecha_ven: {$modelContrato->fecha_ven} based on fecha_ini: {$modelContrato->fecha_ini}", 'user-datos');
            }

            $model->tiene_contratante_diferente = (int)($this->request->post('UserDatos')['tiene_contratante_diferente'] ?? 0);

            if ($model->estatus_solvente === "SI" || $model->estatus_solvente === "Sí" || $model->estatus_solvente === 1) {
                $model->estatus_solvente = "Si";
            } elseif ($model->estatus_solvente === "NO" || $model->estatus_solvente === 0) {
                $model->estatus_solvente = "No";
            }

            $grupoFamiliar = $this->request->post('UserDatos')['grupo_familiar'] ?? [];
            if (!empty($grupoFamiliar)) {
                $model->grupo_familiar = json_encode(array_values($grupoFamiliar));
            }

            if ($model->tiene_contratante_diferente) {
                // Datos del contratante ya cargados
            } else {
                $model->nombre_contratante = null;
                $model->apellido_contratante = null;
                $model->tipo_cedula_contratante = null;
                $model->cedula_contratante = null;
                $model->fecha_nacimiento_contratante = null;
                $model->sexo_contratante = null;
                $model->nacionalidad_contratante = null;
                $model->estado_civil_contratante = null;
                $model->lugar_nacimiento_contratante = null;
                $model->profesion_contratante = null;
                $model->ocupacion_contratante = null;
                $model->actividad_economica_contratante = null;
                $model->descripcion_actividad_contratante = null;
                $model->ingreso_anual_contratante = null;
                $model->direccion_residencia_contratante = null;
                $model->direccion_oficina_contratante = null;
                $model->direccion_cobro_contratante = null;
                $model->telefono_residencia_contratante = null;
                $model->telefono_oficina_contratante = null;
                $model->telefono_celular_contratante = null;
                $model->email_contratante = null;
            }

            $model->plan_id = $modelContrato->plan_id;

            if ($model->user_datos_type_id == 1) {
                $model->afiliado_corporativo_id = null;
            }

            // ============================================
            // DUPLICATE CHECK - Validate before saving
            // ============================================
            if ($model->role === 'afiliado') {
                // Check for duplicates using the model's built-in validation
                $duplicateInfo = $model->getDuplicateDetails();

                if ($duplicateInfo && isset($duplicateInfo['record'])) {
                    $existing = $duplicateInfo['record'];

                    // For underage affiliates, check if it's the same child
                    if ($model->tipo_cedula === 'Menor Sin Cédula') {
                        Yii::$app->session->setFlash('error', $this->generateDuplicateErrorMessage($existing, $model, 'underage'));
                        return $this->render('create', [
                            'model' => $model,
                            'modelContrato' => $modelContrato,
                        ]);
                    }

                    // Regular affiliate duplicate
                    Yii::$app->session->setFlash('error', $this->generateDuplicateErrorMessage($existing, $model, 'regular'));
                    return $this->render('create', [
                        'model' => $model,
                        'modelContrato' => $modelContrato,
                    ]);
                }
            }

            if ($model->save()) {
                $imagenIdentificacionFiles = UploadedFile::getInstancesByName('UserDatos[imagenIdentificacionFile]');
                $selfieFiles = UploadedFile::getInstancesByName('UserDatos[selfieFile]');

                $model->imagenIdentificacionFile = !empty($imagenIdentificacionFiles) ? reset($imagenIdentificacionFiles) : null;
                $model->selfieFile = !empty($selfieFiles) ? reset($selfieFiles) : null;

                if (!empty($imagenIdentificacionFiles) && $imagenIdentificacionFiles[0]->size > 0) {
                    $folder = 'documentos';
                    $fileName = uniqid('imagen_identificacion_') . '.' . $model->imagenIdentificacionFile->extension;
                    $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                    if ($model->imagenIdentificacionFile->saveAs($tempFilePath)) {
                        Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                        $fileKeyInBucket = $fileName;

                        Yii::info("Subiendo archivo a Supabase Storage: " . $fileName, __METHOD__);
                        $publicUrl = UserHelper::uploadFileToSupabaseApi(
                            $tempFilePath,
                            $model->imagenIdentificacionFile->type,
                            $fileKeyInBucket,
                            $folder
                        );

                        if (file_exists($tempFilePath)) {
                            unlink($tempFilePath);
                            Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                        }

                        if ($publicUrl) {
                            $model->imagen_identificacion = $publicUrl;
                            if ($model->save(false)) {
                                Yii::$app->session->setFlash('success', $this->generateSuccessMessage(
                                    'Imagen de Identificación Subida',
                                    'La imagen de identificación se ha subido correctamente.'
                                ));
                            } else {
                                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                                    'Error al Guardar la Imagen',
                                    'La imagen se subió pero no se pudo guardar en la base de datos.'
                                ));
                            }
                        } else {
                            Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                                'Error al Subir la Imagen',
                                'Ocurrió un error al subir la imagen a Supabase Storage.'
                            ));
                        }
                    } else {
                        Yii::error("Error al guardar el archivo temporal: " . $model->imagenIdentificacionFile->error, __METHOD__);
                        Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                            'Error al Guardar el Archivo',
                            'No se pudo guardar el archivo temporal en el servidor.'
                        ));
                    }
                }

                if (!empty($selfieFiles) && $selfieFiles[0]->size > 0) {
                    $folder = 'FotoPerfil';
                    $fileName = uniqid('selfie_') . '.' . $model->selfieFile->extension;
                    $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                    if ($model->selfieFile->saveAs($tempFilePath)) {
                        Yii::info("Archivo temporal guardado en: " . $tempFilePath, __METHOD__);

                        $fileKeyInBucket = $fileName;

                        $publicUrl = UserHelper::uploadFileToSupabaseApi(
                            $tempFilePath,
                            $model->selfieFile->type,
                            $fileKeyInBucket,
                            $folder
                        );

                        if (file_exists($tempFilePath)) {
                            unlink($tempFilePath);
                            Yii::info("Archivo temporal eliminado: " . $tempFilePath, __METHOD__);
                        }

                        if ($publicUrl) {
                            $model->selfie = $publicUrl;
                            if ($model->save(false)) {
                                Yii::$app->session->setFlash('success', $this->generateSuccessMessage(
                                    'Selfie Subido',
                                    'La foto de perfil se ha subido correctamente.'
                                ));
                            } else {
                                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                                    'Error al Guardar el Selfie',
                                    'El selfie se subió pero no se pudo guardar en la base de datos.'
                                ));
                            }
                        } else {
                            Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                                'Error al Subir el Selfie',
                                'Ocurrió un error al subir el selfie a Supabase Storage.'
                            ));
                        }
                    } else {
                        Yii::error("Error al guardar el archivo temporal: " . $model->selfieFile->error, __METHOD__);
                        Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                            'Error al Guardar el Archivo',
                            'No se pudo guardar el archivo temporal en el servidor.'
                        ));
                    }
                }

                $modelUser->username = $model->email;
                $pass = 'sispsa' . $model->cedula;
                $modelUser->password_hash = User::setPassword($pass);
                $modelUser->auth_key = User::generateAuthKey();
                $modelUser->email = $model->email;
                $modelUser->status = 1;

                if ($modelUser->save()) {
                    $modelContrato->user_id = $model->id;
                    $modelContrato->estatus = 'Registrado';
                    $modelContrato->clinica_id = $model->clinica_id;
                    $plan = Planes::find()->where(['id' => $modelContrato->plan_id])->one();
                    $modelContrato->monto = $plan ? $plan->precio : 0;

                    if ($modelContrato->save()) {

                        Yii::info("=== START CUOTA GENERATION DEBUG ===", 'debug');
                        Yii::info("Contract ID: " . $modelContrato->id, 'debug');
                        Yii::info("Start Date: " . $modelContrato->fecha_ini, 'debug');
                        Yii::info("Monto: " . $modelContrato->monto, 'debug');

                        if (!method_exists('app\models\Cuotas', 'generateCuotasSimple')) {
                            Yii::error("CRITICAL: generateCuotasSimple method not found!", 'debug');
                        } else {
                            Yii::info("Method exists, calling it now...", 'debug');
                        }

                        $cuotaGeneration = Cuotas::generateCuotasSimple(
                            $modelContrato->id,
                            $modelContrato->fecha_ini,
                            $modelContrato->monto
                        );
                        Yii::info("Generation result: " . print_r($cuotaGeneration, true), 'debug');

                        if (!$cuotaGeneration['success']) {
                            Yii::error("Failed to generate cuotas: " . $cuotaGeneration['error'], 'user-datos');
                            Yii::$app->session->setFlash('warning', $this->generateWarningMessage(
                                'Contrato Creado con Advertencia',
                                'El contrato se creó correctamente, pero hubo un problema al generar las cuotas. Por favor, ejecute el comando de generación manualmente.<br><br>' .
                                    '<strong>Error:</strong> ' . ($cuotaGeneration['error'] ?? 'Error desconocido')
                            ));
                        } else {
                            Yii::info("Successfully generated anniversary-based cuotas for contract #{$modelContrato->id}", 'user-datos');
                            Yii::$app->session->setFlash('success', $this->generateSuccessMessage(
                                'Afiliado Creado Exitosamente',
                                'El afiliado se ha registrado correctamente con <strong>12 cuotas mensuales</strong>.<br><br>' .
                                    'La primera cuota vence hoy, las siguientes el mismo día de cada mes.'
                            ));
                        }

                        $anio_actual = date('Y');
                        $modelContrato->nrocontrato = $model->cedula . '-' . $anio_actual . '-' . $modelContrato->id;
                        $model->contrato_id = $modelContrato->id;
                        $modelContrato->save();

                        $auth = Yii::$app->authManager;
                        $roleName = 'afiliado';
                        $role = $auth->getRole($roleName);
                        if ($role) {
                            try {
                                $auth->revokeAll($modelUser->id);
                                $auth->assign($role, $modelUser->id);
                                Yii::$app->cache->flush();
                                $model->user_login_id = $modelUser->id;
                                $model->save();

                                if ($model->user_datos_type_id == 2 && !empty($model->afiliado_corporativo_id)) {
                                    CorporativoUser::deleteAll(['user_id' => $model->id]);

                                    $corporativoUser = new CorporativoUser();
                                    $corporativoUser->corporativo_id = $model->afiliado_corporativo_id;
                                    $corporativoUser->user_id = $model->id;
                                    $corporativoUser->fecha_vinculacion = date('Y-m-d H:i:s');
                                    if (!$corporativoUser->save()) {
                                        Yii::error('No se pudo guardar la relación en corporativo_user: ' . json_encode($corporativoUser->getErrors()));
                                    }
                                } elseif (empty($model->afiliado_corporativo_id)) {
                                    CorporativoUser::deleteAll(['user_id' => $model->id]);
                                }
                            } catch (\Exception $e) {
                                Yii::error("Error al asignar el rol: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', $this->generateWarningMessage(
                                'Rol No Asignado',
                                'El usuario se creó correctamente, pero el rol "' . $roleName . '" no existe. Por favor, contacte al administrador del sistema.'
                            ));
                        }
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::error("Error saving contract: " . json_encode($modelContrato->errors), __METHOD__);
                        Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                            'Error al Guardar el Contrato',
                            'No se pudo guardar el contrato: ' . implode(', ', $modelContrato->getErrorSummary(true))
                        ));
                    }
                } else {
                    Yii::error("Error saving user: " . json_encode($modelUser->errors), __METHOD__);
                    Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                        'Error al Guardar el Usuario',
                        'No se pudo guardar el usuario: ' . implode(', ', $modelUser->getErrorSummary(true))
                    ));
                }
            } else {
                Yii::error("Error saving UserDatos: " . json_encode($model->errors), __METHOD__);
                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                    'Error al Guardar los Datos del Afiliado',
                    'No se pudieron guardar los datos del afiliado: ' . implode(', ', $model->getErrorSummary(true))
                ));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    /**
     * Updates an existing UserDatos model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $originalAgenciaId = $model->agencia_id;

        if ($model->contrato_id) {
            $existingContract = Contratos::findOne($model->contrato_id);
            if (!$existingContract) {
                Yii::info("Contrato ID {$model->contrato_id} no existe, limpiando referencia para usuario {$id}", __METHOD__);
                $model->contrato_id = null;
            }
        }

        $modelContrato = Contratos::find()
            ->where(['user_id' => $id])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->orderBy(['fecha_ini' => SORT_DESC])
            ->one();

        $isNewContract = false;

        if ($modelContrato === null) {
            $hasAnnulledContracts = Contratos::find()
                ->where(['user_id' => $id])
                ->andWhere(['estatus' => Contratos::STATUS_ANULADO])
                ->exists();

            if ($hasAnnulledContracts) {
                Yii::info("Usuario {$id} tiene contratos pero todos están anulados", __METHOD__);
                Yii::$app->session->setFlash('info', $this->generateInfoMessage(
                    'Contratos Anulados',
                    'Este afiliado tiene contratos, pero todos están anulados. Se creará un nuevo contrato.'
                ));
            }

            $modelContrato = new Contratos();
            $isNewContract = true;

            $modelContrato->fecha_ini = date('Y-m-d');
            $modelContrato->fecha_ven = date('Y-m-d', strtotime('+1 year'));
            $modelContrato->estatus = 'Creado';
        } else {
            $model->contrato_id = $modelContrato->id;
            Yii::info("Contrato activo encontrado para usuario {$id}: ID {$modelContrato->id} con estatus '{$modelContrato->estatus}'", __METHOD__);
        }

        $tipoUsuarioAnterior = $model->user_datos_type_id;
        $corporativoIdAnterior = $model->afiliado_corporativo_id;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $modelContrato->load($this->request->post())) {

                if ($originalAgenciaId && empty($model->agencia_id)) {
                    $model->agencia_id = $originalAgenciaId;
                    Yii::info("Restored agencia_id = {$originalAgenciaId} for user {$id} during update", __METHOD__);
                }

                $model->clearErrors('contrato_id');

                Yii::info("Iniciando proceso de actualización para UserDatos ID: " . $id, __METHOD__);

                $model->tiene_contratante_diferente = (int)($this->request->post('UserDatos')['tiene_contratante_diferente'] ?? 0);

                if ($model->estatus_solvente === "SI" || $model->estatus_solvente === "Sí" || $model->estatus_solvente === 1) {
                    $model->estatus_solvente = "Si";
                } elseif ($model->estatus_solvente === "NO" || $model->estatus_solvente === 0) {
                    $model->estatus_solvente = "No";
                }

                $grupoFamiliar = $this->request->post('UserDatos')['grupo_familiar'] ?? [];
                if (!empty($grupoFamiliar)) {
                    $model->grupo_familiar = json_encode(array_values($grupoFamiliar));
                } else {
                    $model->grupo_familiar = null;
                }

                if (!$model->tiene_contratante_diferente) {
                    $this->clearContratanteFields($model);
                }

                $model->plan_id = $modelContrato->plan_id;

                if (empty($model->estatus_solvente)) {
                    $model->estatus_solvente = "No";
                }

                $model->role = 'afiliado';
                $model->estatus = 'Registrado';
                $model->updated_at = date('Y-m-d H:i:s');

                $tipoUsuarioActual = (int)($this->request->post('UserDatos')['user_datos_type_id'] ?? 0);
                $corporativoIdSeleccionado = (int)($this->request->post('UserDatos')['afiliado_corporativo_id'] ?? null);

                if ($tipoUsuarioAnterior == 2 && $tipoUsuarioActual == 1) {
                    if ($corporativoIdAnterior) {
                        CorporativoUser::deleteAll(['corporativo_id' => $corporativoIdAnterior, 'user_id' => $model->id]);
                    }
                    $model->afiliado_corporativo_id = null;
                } elseif ($tipoUsuarioActual == 2) {
                    if ($corporativoIdAnterior && $corporativoIdAnterior !== $corporativoIdSeleccionado) {
                        CorporativoUser::deleteAll(['corporativo_id' => $corporativoIdAnterior, 'user_id' => $model->id]);
                    }

                    if ($corporativoIdSeleccionado) {
                        $relacionExistente = CorporativoUser::findOne([
                            'corporativo_id' => $corporativoIdSeleccionado,
                            'user_id' => $model->id
                        ]);

                        if (!$relacionExistente) {
                            $nuevaRelacion = new CorporativoUser();
                            $nuevaRelacion->corporativo_id = $corporativoIdSeleccionado;
                            $nuevaRelacion->user_id = $model->id;
                            $nuevaRelacion->fecha_vinculacion = date('Y-m-d H:i:s');
                            $nuevaRelacion->rol_en_corporativo = 'afiliado';
                            $nuevaRelacion->save();
                        }
                        $model->afiliado_corporativo_id = $corporativoIdSeleccionado;
                    }
                } else {
                    $model->afiliado_corporativo_id = null;
                }

                // ============================================
                // DUPLICATE CHECK FOR UPDATE
                // ============================================
                // Only check if we're changing the cedula or names
                $originalModel = UserDatos::findOne($id);
                $isChangingIdentity = false;

                if ($originalModel) {
                    $isChangingIdentity = (
                        $model->tipo_cedula !== $originalModel->tipo_cedula ||
                        $model->cedula !== $originalModel->cedula ||
                        trim($model->nombres) !== trim($originalModel->nombres) ||
                        trim($model->apellidos) !== trim($originalModel->apellidos)
                    );
                }

                if ($isChangingIdentity && $model->role === 'afiliado') {
                    // Check for duplicates excluding the current record
                    $excludeCurrent = true;
                    if ($model->checkAffiliateDuplicate($excludeCurrent)) {
                        $duplicateInfo = $model->getDuplicateDetails($excludeCurrent);

                        if ($duplicateInfo && isset($duplicateInfo['record'])) {
                            $existing = $duplicateInfo['record'];

                            if ($model->tipo_cedula === 'Menor Sin Cédula') {
                                Yii::$app->session->setFlash('error', $this->generateDuplicateErrorMessage($existing, $model, 'underage'));
                            } else {
                                Yii::$app->session->setFlash('error', $this->generateDuplicateErrorMessage($existing, $model, 'regular'));
                            }
                            return $this->render('update', [
                                'model' => $model,
                                'modelContrato' => $modelContrato,
                            ]);
                        }
                    }
                }

                if ($model->save()) {
                    Yii::info("UserDatos guardado exitosamente", __METHOD__);

                    $imagenIdentificacionFiles = UploadedFile::getInstancesByName('UserDatos[imagenIdentificacionFile]');
                    $selfieFiles = UploadedFile::getInstancesByName('UserDatos[selfieFile]');

                    if (!empty($imagenIdentificacionFiles) && $imagenIdentificacionFiles[0]->size > 0) {
                        $model->imagenIdentificacionFile = reset($imagenIdentificacionFiles);
                        $this->uploadFile($model, 'imagenIdentificacionFile', 'documentos', 'imagen_identificacion', 'imagen_identificacion_');
                    }

                    if (!empty($selfieFiles) && $selfieFiles[0]->size > 0) {
                        $model->selfieFile = reset($selfieFiles);
                        $this->uploadFile($model, 'selfieFile', 'FotoPerfil', 'selfie', 'selfie_');
                    }

                    $modelContrato->user_id = $id;
                    $modelContrato->clinica_id = $model->clinica_id;

                    if (empty($modelContrato->estatus)) {
                        $modelContrato->estatus = 'Creado';
                    }

                    if ($modelContrato->plan_id) {
                        $plan = Planes::findOne($modelContrato->plan_id);
                        $modelContrato->monto = $plan ? $plan->precio : 0;
                    } else {
                        $modelContrato->monto = 0;
                    }

                    if ($isNewContract && empty($modelContrato->nrocontrato)) {
                        $anio_actual = date('Y');
                        $tempId = 'TEMP-' . time();
                        $modelContrato->nrocontrato = $model->cedula . '-' . $anio_actual . '-' . $tempId;
                    }

                    if ($modelContrato->save()) {
                        if (strpos($modelContrato->nrocontrato, 'TEMP-') !== false) {
                            $anio_actual = date('Y');
                            $modelContrato->nrocontrato = $model->cedula . '-' . $anio_actual . '-' . $modelContrato->id;
                            $modelContrato->save(false);
                        }

                        if ($model->contrato_id != $modelContrato->id) {
                            $model->contrato_id = $modelContrato->id;
                            $model->save(false);
                        }

                        $cuota = Cuotas::find()->where(['contrato_id' => $modelContrato->id])->orderBy(['fecha_vencimiento' => SORT_ASC])->one();
                        if (!$cuota) {
                            $cuota = new Cuotas();
                            $cuota->contrato_id = $modelContrato->id;
                        }

                        $cuota->fecha_vencimiento = $modelContrato->fecha_ini;
                        $cuota->monto = $modelContrato->monto;
                        $cuota->estatus = 'pendiente';

                        $tasaCambio = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one();
                        $cuota->rate_usd_bs = $tasaCambio ? $tasaCambio->tasa_cambio : 1;

                        if (!$cuota->save()) {
                            Yii::error("Error saving cuota: " . json_encode($cuota->getErrors()), __METHOD__);
                        }

                        if (empty($model->user_login_id)) {
                            $modelUser = new User();
                            $modelUser->username = $model->email;
                            $pass = 'sispsa' . $model->cedula;
                            $modelUser->password_hash = User::setPassword($pass);
                            $modelUser->auth_key = User::generateAuthKey();
                            $modelUser->email = $model->email;
                            $modelUser->status = 1;

                            if ($modelUser->save()) {
                                $model->user_login_id = $modelUser->id;
                                $model->save(false);

                                $auth = Yii::$app->authManager;
                                $roleName = 'afiliado';
                                $role = $auth->getRole($roleName);
                                if ($role) {
                                    try {
                                        $auth->revokeAll($modelUser->id);
                                        $auth->assign($role, $modelUser->id);
                                        Yii::$app->cache->flush();
                                    } catch (\Exception $e) {
                                        Yii::error("Error assigning role: " . $e->getMessage(), __METHOD__);
                                    }
                                }
                            }
                        } else {
                            $modelUser = User::findOne($model->user_login_id);
                            if ($modelUser) {
                                $modelUser->email = $model->email;
                                $modelUser->save(false);
                            }
                        }

                        Yii::$app->session->setFlash('success', $this->generateSuccessMessage(
                            'Afiliado Actualizado Exitosamente',
                            'El afiliado y su contrato han sido actualizados correctamente.'
                        ));
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        Yii::error("Error saving contract: " . json_encode($modelContrato->getErrors()), __METHOD__);
                        Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                            'Error al Guardar el Contrato',
                            'No se pudo guardar el contrato: ' . implode(', ', $modelContrato->getErrorSummary(true))
                        ));
                    }
                } else {
                    Yii::error("Error saving UserDatos: " . json_encode($model->getErrors()), __METHOD__);
                    Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                        'Error al Actualizar los Datos del Afiliado',
                        'No se pudieron actualizar los datos del afiliado: ' . implode(', ', $model->getErrorSummary(true))
                    ));
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Clear contratante fields
     */
    private function clearContratanteFields($model)
    {
        $fieldsToClear = [
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
            'email_contratante'
        ];

        foreach ($fieldsToClear as $field) {
            $model->$field = null;
        }
    }

    /**
     * Handle user creation/retrieval
     */
    private function handleUserCreation($model)
    {
        if (empty($model->user_login_id)) {
            $modelUser = new User();
            $modelUser->username = $model->email;
            $pass = 'sispsa' . $model->cedula;
            $modelUser->password_hash = User::setPassword($pass);
            $modelUser->auth_key = User::generateAuthKey();
            $modelUser->email = $model->email;
            $modelUser->status = 1;

            if ($modelUser->save()) {
                $model->user_login_id = $modelUser->id;
                return $modelUser;
            } else {
                Yii::error("Error creating user: " . json_encode($modelUser->getErrors()), __METHOD__);
                Yii::$app->session->setFlash('error', $this->generateErrorMessage(
                    'Error al Crear el Usuario',
                    'No se pudo crear el usuario: ' . implode(', ', $modelUser->getErrorSummary(true))
                ));
                return false;
            }
        } else {
            return User::findOne($model->user_login_id);
        }
    }

    /**
     * Handle corporativo relations
     */
    private function handleCorporativoRelations($model, $tipoUsuarioAnterior, $corporativoIdAnterior)
    {
        $tipoUsuarioActual = $model->user_datos_type_id;
        $corporativoIdSeleccionado = $model->afiliado_corporativo_id;

        if ($tipoUsuarioAnterior == 2 && $tipoUsuarioActual == 1) {
            if ($corporativoIdAnterior) {
                CorporativoUser::deleteAll(['corporativo_id' => $corporativoIdAnterior, 'user_id' => $model->id]);
                Yii::info("Relación con corporativo eliminada para el usuario " . $model->id, __METHOD__);
            }
            $model->afiliado_corporativo_id = null;
        } elseif ($tipoUsuarioActual == 2) {
            if ($corporativoIdAnterior && $corporativoIdAnterior !== $corporativoIdSeleccionado) {
                CorporativoUser::deleteAll(['corporativo_id' => $corporativoIdAnterior, 'user_id' => $model->id]);
                Yii::info("Relación anterior con corporativo eliminada para el usuario " . $model->id, __METHOD__);
            }

            if ($corporativoIdSeleccionado) {
                $relacionExistente = CorporativoUser::findOne([
                    'corporativo_id' => $corporativoIdSeleccionado,
                    'user_id' => $model->id
                ]);

                if (!$relacionExistente) {
                    $nuevaRelacion = new CorporativoUser();
                    $nuevaRelacion->corporativo_id = $corporativoIdSeleccionado;
                    $nuevaRelacion->user_id = $model->id;
                    $nuevaRelacion->fecha_vinculacion = date('Y-m-d H:i:s');
                    $nuevaRelacion->rol_en_corporativo = 'afiliado';
                    if (!$nuevaRelacion->save()) {
                        Yii::error("Error creando relación corporativo: " . json_encode($nuevaRelacion->getErrors()), __METHOD__);
                    }
                }
                $model->afiliado_corporativo_id = $corporativoIdSeleccionado;
            }
        } else {
            $model->afiliado_corporativo_id = null;
        }
    }

    /**
     * Handle file uploads
     */
    private function handleFileUploads($model)
    {
        $selfieFiles = UploadedFile::getInstancesByName('UserDatos[selfieFile]');
        if (!empty($selfieFiles) && $selfieFiles[0]->size > 0) {
            $model->selfieFile = reset($selfieFiles);
            $this->uploadFile($model, 'selfieFile', 'FotoPerfil', 'selfie', 'selfie_');
        }

        $identificacionFiles = UploadedFile::getInstancesByName('UserDatos[imagenIdentificacionFile]');
        if (!empty($identificacionFiles) && $identificacionFiles[0]->size > 0) {
            $model->imagenIdentificacionFile = reset($identificacionFiles);
            $this->uploadFile($model, 'imagenIdentificacionFile', 'documentos', 'imagen_identificacion', 'imagen_identificacion_');
        }
    }

    /**
     * Upload file helper
     */
    private function uploadFile($model, $fileAttribute, $folder, $dbField, $prefix)
    {
        $tempFilePath = Yii::getAlias('@runtime') . '/' . uniqid($prefix) . '.' . $model->$fileAttribute->extension;

        if ($model->$fileAttribute->saveAs($tempFilePath)) {
            $publicUrl = UserHelper::uploadFileToSupabaseApi(
                $tempFilePath,
                $model->$fileAttribute->type,
                basename($tempFilePath),
                $folder
            );

            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }

            if ($publicUrl) {
                $model->$dbField = $publicUrl;
                $model->save(false);
            }
        }
    }

    /**
     * Save contract
     */
    private function saveContract($userDatos, $contrato, $userId, $isNewContract)
    {
        $contrato->user_id = $userId;
        $contrato->clinica_id = $userDatos->clinica_id;

        if (empty($contrato->estatus)) {
            $contrato->estatus = 'Creado';
        }

        if (empty($contrato->fecha_ini)) {
            $contrato->fecha_ini = date('Y-m-d');
        }
        if (empty($contrato->fecha_ven)) {
            $contrato->fecha_ven = date('Y-m-d', strtotime('+1 year'));
        }

        if ($contrato->plan_id) {
            $plan = Planes::findOne($contrato->plan_id);
            $contrato->monto = $plan ? $plan->precio : 0;
        } else {
            $contrato->monto = 0;
        }

        if ($isNewContract && empty($contrato->nrocontrato)) {
            $anio_actual = date('Y');
            $contrato->nrocontrato = $userDatos->cedula . '-' . $anio_actual . '-' . ($contrato->id ?: 'NEW');
        }

        if ($contrato->save()) {
            Yii::info(($isNewContract ? 'Nuevo contrato creado' : 'Contrato actualizado') . ' ID: ' . $contrato->id, __METHOD__);
            return true;
        } else {
            Yii::error("Error saving contract: " . json_encode($contrato->getErrors()), __METHOD__);
            return false;
        }
    }

    /**
     * Handle cuota creation
     */
    private function handleCuotaCreation($contrato)
    {
        $cuota = Cuotas::find()->where(['contrato_id' => $contrato->id])->orderBy(['fecha_vencimiento' => SORT_ASC])->one();
        if (!$cuota) {
            $cuota = new Cuotas();
            $cuota->contrato_id = $contrato->id;
        }

        $cuota->fecha_vencimiento = $contrato->fecha_ini;
        $cuota->monto = $contrato->monto;
        $cuota->estatus = 'pendiente';

        $tasaCambio = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one();
        $cuota->rate_usd_bs = $tasaCambio ? $tasaCambio->tasa_cambio : 1;

        if (!$cuota->save()) {
            Yii::error("Error saving cuota: " . json_encode($cuota->getErrors()), __METHOD__);
        }
    }

    /**
     * Assign user role
     */
    private function assignUserRole($userModel)
    {
        if ($userModel) {
            $auth = Yii::$app->authManager;
            $roleName = 'afiliado';
            $role = $auth->getRole($roleName);
            if ($role) {
                try {
                    $auth->revokeAll($userModel->id);
                    $auth->assign($role, $userModel->id);
                    Yii::$app->cache->flush();
                } catch (\Exception $e) {
                    Yii::error("Error assigning role: " . $e->getMessage(), __METHOD__);
                }
            }
        }
    }

    /**
     * Get model errors as string
     */
    private function getModelErrorsString($model)
    {
        $errors = [];
        foreach ($model->getErrors() as $attributeErrors) {
            $errors = array_merge($errors, $attributeErrors);
        }
        return implode(', ', $errors);
    }

    /**
     * Deletes an existing UserDatos model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', $this->generateSuccessMessage(
            'Afiliado Eliminado',
            'El afiliado ha sido eliminado correctamente del sistema.'
        ));

        return $this->redirect(['index']);
    }

    /**
     * Finds the UserDatos model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return UserDatos the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserDatos::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Lists all UserDatos models associated with a specific Asesor (AgenteFuerza).
     * ONLY shows affiliates that belong to THIS SPECIFIC asesor person.
     * Does NOT show agency affiliates (agencia_id) because those are not linked to the asesor.
     * 
     * @param int $asesor_id The ID of the AgenteFuerza (asesor/intermediario)
     * @return string
     * @throws NotFoundHttpException if the asesor is not found
     */
    public function actionIndexByAfiliado($asesor_id = "")
    {
        if (empty($asesor_id)) {
            throw new NotFoundHttpException('El ID del asesor no fue proporcionado.');
        }

        $agenteFuerza = AgenteFuerza::findOne($asesor_id);

        if (!$agenteFuerza) {
            throw new NotFoundHttpException('El asesor especificado no existe.');
        }

        $asesorUserDatosId = $agenteFuerza->idusuario;

        $allAgenteFuerzaIdsForThisAsesor = AgenteFuerza::find()
            ->where(['idusuario' => $asesorUserDatosId])
            ->select('id')
            ->column();

        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere(['user_datos.asesor_id' => $allAgenteFuerzaIdsForThisAsesor]);
        $dataProvider->query->andWhere(['user_datos.role' => 'afiliado']);

        $asesorUser = UserDatos::findOne($asesorUserDatosId);
        $asesorName = $asesorUser ? $asesorUser->nombres . ' ' . $asesorUser->apellidos : 'Asesor';

        $this->view->title = 'Afiliados de: ' . $asesorName;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'asesor_id' => $asesor_id,
            'asesor_name' => $asesorName,
        ]);
    }

    /**
     * Generates contract PDF
     * @param int $id UserDatos ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionGenerarContratov($id)
    {
        $model = $this->findModel($id);

        // ============================================
        // DEFINE LOGO AND FIRMAS PATHS FIRST
        // ============================================
        $logo = Yii::getAlias('@webroot/img/sispsalogo.jpg');
        $firmas = Yii::getAlias('@webroot/img/firmas.png');

        $activeContract = Contratos::find()
            ->where(['user_id' => $model->id])
            ->andWhere(['!=', 'estatus', Contratos::STATUS_ANULADO])
            ->orderBy(['fecha_ini' => SORT_DESC])
            ->one();

        $receiptNumber = '';
        $totalCuotas = 0;
        $totalMontoCuotas = 0;

        if ($activeContract) {
            $cuotasList = Cuotas::find()
                ->where(['contrato_id' => $activeContract->id])
                ->orderBy(['numero_cuota' => SORT_ASC])
                ->all();

            $totalCuotas = count($cuotasList);
            if ($totalCuotas == 0) {
                $totalCuotas = 12;
            }

            foreach ($cuotasList as $cuota) {
                $totalMontoCuotas += ($cuota->monto_usd ?: $cuota->monto);
            }

            $receipt = Receipt::find()
                ->where(['contract_id' => $activeContract->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->one();

            if (!$receipt && $model->id) {
                $receipt = Receipt::find()
                    ->where(['user_id' => $model->id])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->one();
            }

            if (!$receipt) {
                $paidCuota = Cuotas::find()
                    ->where(['contrato_id' => $activeContract->id])
                    ->andWhere(['not', ['id_pago' => null]])
                    ->one();

                if ($paidCuota && $paidCuota->id_pago) {
                    $receipt = Receipt::find()
                        ->where(['payment_id' => $paidCuota->id_pago])
                        ->one();
                }
            }

            if (!$receipt && $model->id) {
                $receipt = new Receipt();
                $receipt->contract_id = $activeContract->id;
                $receipt->user_id = $model->id;
                $receipt->amount = $activeContract->monto;
                $receipt->currency = 'USD';
                $receipt->issue_date = date('Y-m-d');
                $receipt->status = Receipt::STATUS_GENERATED;
                $receipt->payment_method = 'Contrato';
                $receipt->receipt_number = Receipt::generateReceiptNumber();

                if ($receipt->save()) {
                    Yii::info("Provisional receipt generated: {$receipt->receipt_number} for contract #{$activeContract->id}", 'contrato-pdf');
                }
            }

            if ($receipt) {
                $receiptNumber = $receipt->receipt_number;
            }
        }

        $corporativo = null;
        $hasCorporateRelation = false;
        $isCorporateAffiliate = ($model->user_datos_type_id == 2);

        // Fetch the corporativo data if this is a corporate affiliate
        if ($isCorporateAffiliate && !empty($model->afiliado_corporativo_id)) {
            $corporativo = Corporativo::findOne($model->afiliado_corporativo_id);
            if ($corporativo) {
                $hasCorporateRelation = true;
            }
        }

        $contractNumber = 'N/A';
        $prefix = '';

        if ($model->user_datos_type_id == 1) {
            $prefix = 'CI-';
        } elseif ($model->user_datos_type_id == 2) {
            $prefix = 'CO-';
        }

        if ($activeContract) {
            if (!empty($activeContract->nrocontrato)) {
                $contractNumber = $prefix . $activeContract->nrocontrato;
            } elseif ($activeContract->id) {
                $anio_actual = date('Y');
                $generatedNumber = $model->cedula . '-' . $anio_actual . '-' . $activeContract->id;

                $activeContract->nrocontrato = $generatedNumber;
                $activeContract->save(false);

                $contractNumber = $prefix . $generatedNumber;
            }
        } else {
            $anyContract = Contratos::find()
                ->where(['user_id' => $model->id])
                ->orderBy(['id' => SORT_DESC])
                ->one();

            if ($anyContract && !empty($anyContract->nrocontrato)) {
                $contractNumber = $prefix . $anyContract->nrocontrato;
            } elseif ($anyContract && $anyContract->id) {
                $anio_actual = date('Y');
                $contractNumber = $prefix . $model->cedula . '-' . $anio_actual . '-' . $anyContract->id;
            } else {
                $anio_actual = date('Y');
                $contractNumber = $prefix . $model->cedula . '-' . $anio_actual . '-' . $model->id;
            }
        }

        Yii::info("Contract Number generated: {$contractNumber} for user {$model->id}", 'contrato-pdf');
        Yii::info("Receipt Number: {$receiptNumber}, Total Cuotas: {$totalCuotas}", 'contrato-pdf');

        $estadoId = (int) $model->estado;
        $municipioId = (int) $model->municipio;
        $parroquiaId = (int) $model->parroquia;
        $ciudadId = (int) $model->ciudad;

        $estadoNombre = RmEstado::findOne($estadoId)->nombre ?? '';
        $municipioNombre = RmMunicipio::findOne($municipioId)->nombre ?? '';
        $parroquiaNombre = RmParroquia::findOne($parroquiaId)->nombre ?? '';
        $ciudadNombre = RmCiudad::findOne($ciudadId)->nombre ?? '';

        $residenceAddressParts = [];
        if (!empty($model->direccion)) $residenceAddressParts[] = $model->direccion;
        if (!empty($parroquiaNombre)) $residenceAddressParts[] = $parroquiaNombre;
        if (!empty($municipioNombre)) $residenceAddressParts[] = $municipioNombre;
        if (!empty($ciudadNombre)) $residenceAddressParts[] = $ciudadNombre;
        if (!empty($estadoNombre)) $residenceAddressParts[] = $estadoNombre;
        $fullResidenceAddress = implode(', ', array_filter($residenceAddressParts));

        $family_group = [];
        if ($model->grupo_familiar) {
            $grupoFamiliar = json_decode($model->grupo_familiar, true) ?: [];
            foreach ($grupoFamiliar as $member) {
                $family_group[] = [
                    'name' => $member['nombre'] ?? '',
                    'ci' => $member['cedula'] ?? '',
                    'relationship' => $member['parentesco'] ?? '',
                    'sex' => $member['sexo'] ?? '',
                    'birthdate' => $member['fecha_nacimiento'] ?? '',
                ];
            }
        }

        // ============================================
        // INTERMEDIARY DATA - From agente_fuerza table
        // ============================================
        $agenteFuerza = null;
        $asesorUserDatos = null;
        $intermediarioNombre = '';
        $intermediarioCodigo = '';
        $intermediarioCi = '';

        if (!empty($model->asesor_id)) {
            // Get the AgenteFuerza record directly
            $agenteFuerza = AgenteFuerza::findOne($model->asesor_id);

            if ($agenteFuerza) {
                // Get the user data for this asesor
                $asesorUserDatos = UserDatos::findOne($agenteFuerza->idusuario);

                if ($asesorUserDatos) {
                    // INTERMEDIARIO: nombres + apellidos from UserDatos
                    $intermediarioNombre = trim($asesorUserDatos->nombres . ' ' . $asesorUserDatos->apellidos);

                    // CÓDIGO: registro_corredor_actividad_aseguradora from AgenteFuerza
                    $intermediarioCodigo = $agenteFuerza->registro_corredor_actividad_aseguradora ?? '';

                    // C.I.: tipo_cedula + cedula from UserDatos
                    $intermediarioCi = ($asesorUserDatos->tipo_cedula ?? '') . '-' . ($asesorUserDatos->cedula ?? '');
                }
            }
        }

        // ============================================
        // PLAN INFORMATION - Get plan data
        // ============================================
        $plan = $model->plan;
        $planCobertura = $plan ? $plan->cobertura : 'N/A';
        // Format the coverage limit with $ sign
        $planCoberturaFormatted = $planCobertura !== 'N/A' ? '$' . number_format($planCobertura, 2, ',', '.') : 'N/A';
        $planDeducible = $plan && !empty($plan->deducible) ? $plan->deducible : 'NO APLICA';
        $planNombre = $plan ? $plan->nombre : 'N/A';
        $planMoneda = $plan && !empty($plan->moneda) ? $plan->moneda : 'USD';
        $planExclusiones = $plan && !empty($plan->exclusiones) ? $plan->exclusiones : '';

        // ============================================
        // PLAN SERVICES - Get services for this plan
        // ============================================
        $planServices = [];
        if ($model->plan_id) {
            $dbServices = \app\models\PlanServicios::getServicesForPlan($model->plan_id, $model->clinica_id);
            foreach ($dbServices as $svc) {
                $planServices[] = [
                    'servicio' => $svc->servicio_nombre ?? '',
                    'espera' => $svc->plazo_espera ?? 'NO APLICA',
                    'descripcion' => $svc->descripcion ?? 'Servicio incluido en el plan',
                ];
            }
        }

        // ============================================
        // CONTRATANTE DATA - Determine source based on type
        // ============================================
        if ($isCorporateAffiliate && $corporativo) {
            // For CORPORATIVO affiliates, use data from Corporativo table
            $contratanteData = [
                'contratante_nombre' => $corporativo->nombre,
                'contratante_apellido' => '',
                'contratante_ci' => $corporativo->rif ?: 'N/A',
                'contratante_nacionalidad' => 'N/A',
                'contratante_estado_civil' => 'N/A',
                'contratante_lugar_nacimiento' => 'N/A',
                'contratante_fecha_nac' => null,
                'contratante_sexo' => 'N/A',
                'contratante_profesion' => 'N/A',
                'contratante_ocupacion' => 'N/A',
                'contratante_actividad_economica' => $corporativo->actividad_economica ?? 'N/A',
                'contratante_descripcion_actividad' => $corporativo->productos_servicios ?? 'N/A',
                'contratante_ingreso_anual' => 'N/A',
                'contratante_direccion_residencia' => $corporativo->direccion ?? 'N/A',
                'contratante_direccion_oficina' => $corporativo->direccion ?? 'N/A',
                'contratante_direccion_cobro' => $corporativo->domicilio_fiscal ?? $corporativo->direccion ?? 'N/A',
                'contratante_telefono_residencia' => $corporativo->telefono ?? 'N/A',
                'contratante_telefono_oficina' => $corporativo->telefono ?? 'N/A',
                'contratante_telefono_celular' => $corporativo->telefono ?? 'N/A',
                'contratante_email' => $corporativo->email ?? 'N/A',
                'contratante_tiene_diferente' => false,
                'tipo_persona_contratante' => 'Jurídica',
                'razon_social_contratante' => $corporativo->nombre ?? 'N/A',
                'contratante_is_corporate' => true,
            ];
        } else {
            // For INDIVIDUAL affiliates, use data from UserDatos
            $contratanteData = [
                'contratante_nombre' => $model->nombre_contratante ?? $model->nombres,
                'contratante_apellido' => $model->apellido_contratante ?? $model->apellidos,
                'contratante_ci' => ($model->tipo_cedula_contratante ?? $model->tipo_cedula) . '-' .
                    ($model->cedula_contratante ?? $model->cedula),
                'contratante_nacionalidad' => $model->nacionalidad_contratante ?? $model->nacionalidad,
                'contratante_estado_civil' => $model->estado_civil_contratante ?? $model->estado_civil,
                'contratante_lugar_nacimiento' => $model->lugar_nacimiento_contratante ?? $model->lugar_nacimiento,
                'contratante_fecha_nac' => $model->fecha_nacimiento_contratante ?? $model->fechanac,
                'contratante_sexo' => $model->sexo_contratante ?? $model->sexo,
                'contratante_profesion' => $model->profesion_contratante ?? $model->profesion,
                'contratante_ocupacion' => $model->ocupacion_contratante ?? $model->ocupacion,
                'contratante_actividad_economica' => $model->actividad_economica_contratante ?? $model->actividad_economica,
                'contratante_descripcion_actividad' => $model->descripcion_actividad_contratante ?? $model->descripcion_actividad,
                'contratante_ingreso_anual' => $model->ingreso_anual_contratante ?? $model->ingreso_anual,
                'contratante_direccion_residencia' => $model->direccion_residencia_contratante ?? $model->direccion_residencia,
                'contratante_direccion_oficina' => $model->direccion_oficina_contratante ?? $model->direccion_oficina,
                'contratante_direccion_cobro' => $model->direccion_cobro_contratante ?? $model->direccion_cobro,
                'contratante_telefono_residencia' => $model->telefono_residencia_contratante ?? $model->telefono_residencia,
                'contratante_telefono_oficina' => $model->telefono_oficina_contratante ?? $model->telefono_oficina,
                'contratante_telefono_celular' => $model->telefono_celular_contratante ?? $model->telefono_celular,
                'contratante_email' => $model->email_contratante ?? $model->email,
                'contratante_tiene_diferente' => (bool)$model->tiene_contratante_diferente,
                'tipo_persona_contratante' => 'Natural',
                'razon_social_contratante' => $model->razon_social ?? 'N/A',
                'contratante_is_corporate' => false,
            ];
        }

        // ============================================
        // BUILD DATA ARRAY WITH ALL INFORMATION
        // ============================================
        $data = [
            // Contract Information
            'contract_number' => $contractNumber,
            'affiliation_type_id' => $model->user_datos_type_id,
            'affiliation_type_name' => ($model->user_datos_type_id == 1) ? 'INDIVIDUAL' : 'COLECTIVO',
            'has_active_contract' => ($activeContract !== null),
            'contract_status' => $activeContract ? $activeContract->estatus : 'No activo',
            'contract_start_date' => $activeContract ? $activeContract->fecha_ini : null,
            'contract_end_date' => $activeContract ? $activeContract->fecha_ven : null,
            'monthly_amount' => $activeContract ? $activeContract->monto : 0,
            'clinica_name' => $model->clinica ? $model->clinica->nombre : 'No asignada',
            'receipt_number' => $receiptNumber,
            'total_cuotas' => 12,
            'total_monto_cuotas' => $totalMontoCuotas,
            'fecha_emision' => date('d/m/Y'),
            'is_corporate_affiliate' => $isCorporateAffiliate,

            // Affiliate Information
            'proposed_affiliate_name' => $model->nombres . " " . $model->apellidos,
            'proposed_affiliate_ci' => $model->tipo_cedula . "-" . $model->cedula,
            'proposed_affiliate_nationality' => $model->nacionalidad,
            'proposed_affiliate_marital_status' => $model->estado_civil,
            'proposed_affiliate_birthplace' => $model->lugar_nacimiento,
            'proposed_affiliate_birthdate' => Yii::$app->formatter->asDate($model->fechanac, 'yyyy-MM-dd'),
            'proposed_affiliate_sex' => $model->sexo,
            'proposed_affiliate_profession' => $model->profesion,
            'proposed_affiliate_occupation' => $model->ocupacion,
            'proposed_affiliate_economic_activity' => $model->actividad_economica,
            'proposed_affiliate_commercial_branch' => $model->ramo_comercial,
            'proposed_affiliate_activity_description' => $model->descripcion_actividad,
            'proposed_affiliate_annual_income' => $model->ingreso_anual,
            'proposed_affiliate_residence_address' => $fullResidenceAddress,
            'proposed_affiliate_phone_residence' => $model->telefono_residencia ?: $model->telefono,
            'proposed_affiliate_office_address' => $model->direccion_oficina,
            'proposed_affiliate_phone_office' => $model->telefono_oficina,
            'proposed_affiliate_billing_address' => $model->direccion_cobro ?: ($model->direccion_residencia ?: $fullResidenceAddress),
            'proposed_affiliate_cell_phone' => $model->telefono_celular ?: $model->telefono,
            'proposed_affiliate_email' => $model->email,

            // ============================================
            // INTERMEDIARY INFORMATION - From agente_fuerza
            // ============================================
            'intermediary_name' => $intermediarioNombre,
            'intermediary_code' => $intermediarioCodigo,
            'intermediary_ci' => $intermediarioCi,

            // ============================================
            // CONTRATANTE DATA
            // ============================================
            'contratante_nombre' => $contratanteData['contratante_nombre'],
            'contratante_apellido' => $contratanteData['contratante_apellido'],
            'contratante_ci' => $contratanteData['contratante_ci'],
            'contratante_nacionalidad' => $contratanteData['contratante_nacionalidad'],
            'contratante_estado_civil' => $contratanteData['contratante_estado_civil'],
            'contratante_lugar_nacimiento' => $contratanteData['contratante_lugar_nacimiento'],
            'contratante_fecha_nac' => $contratanteData['contratante_fecha_nac'],
            'contratante_sexo' => $contratanteData['contratante_sexo'],
            'contratante_profesion' => $contratanteData['contratante_profesion'],
            'contratante_ocupacion' => $contratanteData['contratante_ocupacion'],
            'contratante_actividad_economica' => $contratanteData['contratante_actividad_economica'],
            'contratante_descripcion_actividad' => $contratanteData['contratante_descripcion_actividad'],
            'contratante_ingreso_anual' => $contratanteData['contratante_ingreso_anual'],
            'contratante_direccion_residencia' => $contratanteData['contratante_direccion_residencia'],
            'contratante_direccion_oficina' => $contratanteData['contratante_direccion_oficina'],
            'contratante_direccion_cobro' => $contratanteData['contratante_direccion_cobro'],
            'contratante_telefono_residencia' => $contratanteData['contratante_telefono_residencia'],
            'contratante_telefono_oficina' => $contratanteData['contratante_telefono_oficina'],
            'contratante_telefono_celular' => $contratanteData['contratante_telefono_celular'],
            'contratante_email' => $contratanteData['contratante_email'],
            'contratante_tiene_diferente' => $contratanteData['contratante_tiene_diferente'],
            'tipo_persona_contratante' => $contratanteData['tipo_persona_contratante'],
            'razon_social_contratante' => $contratanteData['razon_social_contratante'],
            'contratante_is_corporate' => $contratanteData['contratante_is_corporate'],

            'tipo_persona_afiliado' => 'Natural',

            // ============================================
            // PLAN INFORMATION
            // ============================================
            'plan_selected' => $planNombre,
            'plan_currency' => $planMoneda,
            'plan_deductible' => $planDeducible,
            'plan_coverage_limit' => $planCoberturaFormatted,

            // ============================================
            // PLAN SERVICES & EXCLUSIONS
            // ============================================
            'plan_services' => $planServices,
            'exclusiones' => $planExclusiones ?: 'Las exclusiones generales aplicables a todos los planes de medicina prepagada según las condiciones generales del contrato.',

            // Maternity Coverage
            'maternity_coverage' => $model->cobertura_maternidad ?? false,
            'maternity_deductible' => $model->deducible_maternidad ?? 'NO APLICA',
            'maternity_coverage_limit' => $model->limite_cobertura_maternidad ?? 'NO APLICA',

            // Family Group
            'family_group' => $family_group,

            // ============================================
            // ENHANCED BENEFICIARY INFORMATION
            // ============================================
            'beneficiary_name' => $model->nombre_beneficiario ?? 'N/A',
            'beneficiary_ci' => $model->cedula_beneficiario ?? 'N/A',
            'beneficiary_relationship' => $model->parentesco_beneficiario ?? 'N/A',
            'beneficiary_sex' => $model->sexo_beneficiario ?? 'N/A',
            'beneficiary_birthdate' => $model->fecha_nacimiento_beneficiario ?? null,

            // ============================================
            // ENHANCED BANKING INFORMATION
            // ============================================
            'bank_account_holder_name' => $model->nombre_titular ?? 'N/A',
            'bank_account_ci' => $model->cedula_titular ?? 'N/A',
            'bank_account_number' => $model->numero_cuenta ?? 'N/A',
            'bank_name' => $model->banco ? $model->banco->nombre : 'N/A',
            'bank_account_type' => $model->tipo_cuenta ?? '',

            // Declaration Information
            'declaration_proposed_affiliate_name' => $model->nombres . " " . $model->apellidos,
            'declaration_proposed_affiliate_ci' => $model->tipo_cedula . "-" . $model->cedula,
            'declaration_contracting_party_name' => ($model->nombre_contratante ?? '') . " " . ($model->apellido_contratante ?? ''),
            'declaration_contracting_party_ci' => ($model->tipo_cedula_contratante ?? '') . "-" . ($model->cedula_contratante ?? ''),
            'declaration_place' => $ciudadNombre,
            'declaration_date' => date('d/m/Y'),

            // Corporate Information
            'has_corporate_relation' => $hasCorporateRelation,
            'corporate_name' => $corporativo ? $corporativo->nombre : '',
            'corporate_rif' => $corporativo ? $corporativo->rif : '',
            'corporate_mercantile_register' => $corporativo ? $corporativo->tomo_registro . ' ' . $corporativo->folio_registro : '',
            'corporate_tome' => $corporativo ? $corporativo->tomo_registro : '',
            'corporate_registration_date' => $corporativo && $corporativo->fecha_registro_mercantil ? Yii::$app->formatter->asDate($corporativo->fecha_registro_mercantil, 'dd/MM/yyyy') : '',
            'corporate_address' => $corporativo ? $corporativo->direccion : '',
            'corporate_phone' => $corporativo ? $corporativo->telefono : '',
            'corporate_email' => $corporativo ? $corporativo->email : '',
            'corporate_economic_activity' => $corporativo ? $corporativo->actividad_economica : '',
            'corporate_products_services' => $corporativo ? $corporativo->productos_servicios : '',
            'corporate_profit' => $corporativo ? $corporativo->utilidad_ejercicio_anterior : '',
            'corporate_equity' => $corporativo ? $corporativo->patrimonio : '',
            'corporate_legal_representative_name' => $corporativo ? $corporativo->nombre_representante : '',
            'corporate_legal_representative_ci' => $corporativo ? $corporativo->cedula_representante : '',
            'corporate_legal_representative_nationality' => $corporativo ? $corporativo->nacionalidad_representante : '',
            'corporate_legal_representative_marital_status' => $corporativo ? $corporativo->estado_civil_representante : '',
            'corporate_legal_representative_birthplace' => $corporativo ? $corporativo->lugar_nacimiento_representante : '',
            'corporate_legal_representative_birthdate' => $corporativo && $corporativo->fecha_nacimiento_representante ? Yii::$app->formatter->asDate($corporativo->fecha_nacimiento_representante, 'dd/MM/yyyy') : '',
            'corporate_legal_representative_sex' => $corporativo ? $corporativo->sexo_representante : '',
            'corporate_legal_representative_profession' => $corporativo ? $corporativo->profesion_representante : '',
            'corporate_legal_representative_occupation' => $corporativo ? $corporativo->ocupacion_representante : '',
            'corporate_legal_representative_activity_description' => $corporativo ? $corporativo->descripcion_actividad_representante : '',
            'corporate_legal_representative_address' => $corporativo ? $corporativo->direccion_representante : '',
            'corporate_legal_representative_phone' => $corporativo ? $corporativo->telefono_representante : '',
        ];

        $content = $this->renderPartial('_contrato_pdf', [
            'data' => $data,
            'logo' => $logo,
            'firmas' => $firmas
        ]);

        $url_css = Yii::getAlias('@webroot') . '/css/affiliation-pdf.css';

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_LETTER,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => $url_css,
            'options' => [
                'title' => 'Solicitud de Afiliación SISPSA',
            ],
            'methods' => [
                'SetHeader' => false,
                'SetFooter' => ['{PAGENO}'],
            ]
        ]);

        return $pdf->render();
    }

    public function actionGetCorporativeAffiliates($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = UserDatos::find()
                ->where(['user_datos_type_id' => 2])
                ->andFilterWhere(['ilike', 'nombres', $q])
                ->orFilterWhere(['ilike', 'apellidos', $q])
                ->limit(20);

            $command = $query->createCommand();
            $data = $command->queryAll();

            $out['results'] = array_values(ArrayHelper::map($data, 'id', function ($item) {
                return $item['nombres'] . ' ' . $item['apellidos'] . ' (' . $item['cedula'] . ')';
            }));
        }
        return $out;
    }

    public function actionClinicas()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $tipo = $parents[0];
                $corporativo = $parents[1];

                $clinica = [];

                if ($tipo == 1) {
                    $clinica = RmClinica::find()->select(['id', 'nombre'])->all();
                }

                if ($tipo == 2) {
                    $clinica = CorporativoClinica::find()
                        ->joinWith('clinica')
                        ->where(['corporativo_id' => $corporativo])
                        ->all();
                }

                foreach ($clinica as $cli) {
                    if ($tipo == 1) {
                        $out[] = [
                            'id' => $cli->id,
                            'name' => $cli->nombre,
                        ];
                    } elseif ($tipo == 2) {
                        $out[] = [
                            'id' => $cli->clinica->id,
                            'name' => $cli->clinica->nombre,
                        ];
                    }
                }
                return ['output' => $out, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }

    public function actionDatosdelplan()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $datos = Yii::$app->getRequest()->post();

            $plan_id = $datos['id'] ?? null;

            if (empty($plan_id) || !is_numeric($plan_id) || $plan_id <= 0) {
                Yii::warning("Intento de consulta de plan con ID inválido o no numérico: {$plan_id}", 'datosdelplan');

                return [
                    'data' => [
                        'comision' => 0,
                        'precio' => 0,
                        'moneda' => "USD",
                        'deducible' => 0,
                        'limite_cobertura' => 0
                    ]
                ];
            }

            $plan = Planes::find()->where(['id' => (int) $plan_id])->one();

            if (!$plan) {
                Yii::warning("Plan no encontrado para ID: {$plan_id}", 'datosdelplan');
                return [
                    'data' => [
                        'comision' => 0,
                        'precio' => 0,
                        'moneda' => "USD",
                        'deducible' => 0,
                        'limite_cobertura' => 0
                    ]
                ];
            }

            return [
                'data' => [
                    'comision' => $plan->comision,
                    'precio' => $plan->precio,
                    'moneda' => "USD",
                    'deducible' => 0,
                    'limite_cobertura' => $plan->cobertura
                ]
            ];
        }

        throw new \yii\web\BadRequestHttpException('Solo se permiten peticiones AJAX para esta acción.');
    }

    /**
     * Returns JSON data for clinicas filtered by type and corporativo.
     * @return array JSON array of [id => name]
     */
    public function actionClinicasJson()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $type = Yii::$app->request->get('type');
        $corporativo_id = Yii::$app->request->get('corporativo');

        if ($type == 2 && $corporativo_id) {
            $clinicas = RmClinica::find()
                ->select(['rm_clinica.id', 'rm_clinica.nombre'])
                ->innerJoin('corporativo_clinica', 'rm_clinica.id = corporativo_clinica.clinica_id')
                ->where(['corporativo_clinica.corporativo_id' => $corporativo_id])
                ->asArray()
                ->all();
        } else {
            $clinicas = RmClinica::find()
                ->select(['id', 'nombre'])
                ->asArray()
                ->all();
        }

        $data = ArrayHelper::map($clinicas, 'id', 'nombre');

        return Json::encode($data);
    }

    /**
     * Search affiliates for dependent assignment
     */
    public function actionSearchAfiliados()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $q = Yii::$app->request->get('q');
        $current_user = Yii::$app->request->get('current_user', 0);

        Yii::info("Searching affiliates: q={$q}, current_user={$current_user}", 'dependientes');

        $query = UserDatos::find()
            ->select([
                'id',
                'nombres',
                'apellidos',
                'cedula',
                'email',
                'telefono',
                'tipo_cedula',
                'CONCAT(nombres, \' \', apellidos) as nombre_completo'
            ])
            ->where(['role' => 'afiliado']);

        if ($current_user) {
            $query->andWhere(['!=', 'id', $current_user]);

            $existingDependientes = Dependientes::find()
                ->select(['dependiente_id'])
                ->where(['activo' => true])
                ->column();

            if (!empty($existingDependientes)) {
                $query->andWhere(['not in', 'id', $existingDependientes]);
            }
        }

        if ($q) {
            $query->andWhere([
                'or',
                ['ilike', 'nombres', $q],
                ['ilike', 'apellidos', $q],
                ['ilike', 'CAST(cedula AS TEXT)', $q],
                ['ilike', 'email', $q],
                ['ilike', 'CONCAT(nombres, \' \', apellidos)', $q]
            ]);
        }

        $afiliados = $query->limit(20)->all();

        $results = [];
        foreach ($afiliados as $afiliado) {
            $tipoCedula = !empty($afiliado->tipo_cedula) ? $afiliado->tipo_cedula : '';
            $cedulaCompleta = $tipoCedula ? $tipoCedula . '-' . $afiliado->cedula : (string)$afiliado->cedula;

            $results[] = [
                'id' => $afiliado->id,
                'text' => $afiliado->nombres . ' ' . $afiliado->apellidos . ' (' . $cedulaCompleta . ')',
                'nombre_completo' => $afiliado->nombres . ' ' . $afiliado->apellidos,
                'cedula' => $cedulaCompleta,
                'email' => $afiliado->email,
                'telefono' => $afiliado->telefono,
            ];
        }

        Yii::info("Search found " . count($results) . " results", 'dependientes');

        return ['results' => $results];
    }

    /**
     * Get affiliate details
     */
    public function actionGetAfiliadoDetails($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $afiliado = UserDatos::findOne($id);

        if (!$afiliado) {
            return ['success' => false, 'message' => 'Afiliado no encontrado'];
        }

        return [
            'success' => true,
            'data' => [
                'id' => $afiliado->id,
                'nombre_completo' => $afiliado->nombres . ' ' . $afiliado->apellidos,
                'cedula' => $afiliado->tipo_cedula . '-' . $afiliado->cedula,
                'email' => $afiliado->email,
                'telefono' => $afiliado->telefono,
            ]
        ];
    }

    /**
     * Get dependents for a titular
     */
    public function actionGetDependientes($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $dependientes = Dependientes::find()
            ->with('dependiente')
            ->where(['titular_id' => $id, 'activo' => true])
            ->all();

        $data = [];
        foreach ($dependientes as $dep) {
            $data[] = [
                'id' => $dep->id,
                'dependiente_id' => $dep->dependiente_id,
                'nombre_completo' => $dep->dependiente->nombres . ' ' . $dep->dependiente->apellidos,
                'cedula' => $dep->dependiente->tipo_cedula . '-' . $dep->dependiente->cedula,
                'email' => $dep->dependiente->email,
                'telefono' => $dep->dependiente->telefono,
                'parentesco' => $dep->parentesco,
                'porcentaje_pago' => $dep->porcentaje_pago,
                'activo' => $dep->activo,
            ];
        }

        return ['success' => true, 'data' => $data];
    }

    /**
     * Get payment summary for titular and dependents
     */
    public function actionGetPaymentSummary($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pendingPayments = Dependientes::getPendingPaymentsForTitular($id);

        $summary = [
            'total_dependientes' => count(Dependientes::getDependientesForTitular($id)),
            'cuotas_titular' => 0,
            'cuotas_dependientes' => 0,
            'monto_total' => 0,
        ];

        foreach ($pendingPayments as $payment) {
            if ($payment['type'] === 'titular') {
                $summary['cuotas_titular']++;
            } else {
                $summary['cuotas_dependientes']++;
            }
            $summary['monto_total'] += $payment['monto'];
        }

        return ['success' => true, 'data' => $summary];
    }

    public function actionCheckDependientes($id)
    {
        $dependientesCount = \app\models\Dependientes::find()
            ->where(['titular_id' => $id])
            ->andWhere(['activo' => 1])
            ->count();

        return $this->asJson([
            'has_dependientes' => $dependientesCount > 0,
            'count' => $dependientesCount
        ]);
    }

    /**
     * Test action to debug search
     */
    public function actionTestSearch($q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = UserDatos::find()
            ->select(['id', 'nombres', 'apellidos', 'cedula', 'tipo_cedula'])
            ->where(['role' => 'afiliado'])
            ->andWhere(['estatus' => 'Registrado'])
            ->limit(5);

        if ($q) {
            $query->andWhere([
                'or',
                ['ilike', 'nombres', $q],
                ['ilike', 'apellidos', $q],
                ['ilike', 'CAST(cedula AS TEXT)', $q],
            ]);
        }

        $results = $query->all();

        $data = [];
        foreach ($results as $result) {
            $tipoCedula = !empty($result->tipo_cedula) ? $result->tipo_cedula : '';
            $cedulaCompleta = $tipoCedula ? $tipoCedula . '-' . $result->cedula : (string)$result->cedula;

            $data[] = [
                'id' => $result->id,
                'nombre' => $result->nombres . ' ' . $result->apellidos,
                'cedula' => $cedulaCompleta,
                'tipo_cedula' => $result->tipo_cedula,
                'cedula_num' => $result->cedula,
            ];
        }

        Yii::info("Test search results: " . print_r($data, true), 'dependientes');
        Yii::info("SQL: " . $query->createCommand()->getRawSql(), 'dependientes');

        return ['success' => true, 'data' => $data];
    }

    // REPORT ACTIONS

    /**
     * Report of all affiliates
     */
    public function actionReporteAfiliados()
    {
        $searchModel = new AfiliadosReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $clinicas = RmClinica::find()
            ->select(['id', 'nombre'])
            ->orderBy(['nombre' => SORT_ASC])
            ->asArray()
            ->all();
        $clinicaList = \yii\helpers\ArrayHelper::map($clinicas, 'id', 'nombre');

        $tipoAfiliadoList = UserDatosType::getList();
        $estatusSolventeList = ['Si' => 'Si', 'No' => 'No'];

        return $this->render('reporte-afiliados', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'clinicaList' => $clinicaList,
            'tipoAfiliadoList' => $tipoAfiliadoList,
            'estatusSolventeList' => $estatusSolventeList,
        ]);
    }

    /**
     * Export affiliates report to Excel
     */
    public function actionExportarExcelAfiliados()
    {
        $searchModel = new AfiliadosReportSearch();
        $affiliates = $searchModel->getAllAffiliates(Yii::$app->request->queryParams);

        $clinicaIds = [];
        foreach ($affiliates as $affiliate) {
            if ($affiliate->clinica_id) {
                $clinicaIds[$affiliate->clinica_id] = true;
            }
        }
        $numeroClinicas = count($clinicaIds);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $spreadsheet->getProperties()
            ->setCreator("SISPSA")
            ->setTitle("Reporte de Afiliados")
            ->setSubject("Listado de Afiliados")
            ->setDescription("Reporte de todos los afiliados del sistema");

        // ============================================
        // UPDATED: Header row now spans A1:H1 (was A1:G1)
        // ============================================
        $sheet->setCellValue('A1', 'REPORTE DE AFILIADOS - SISPSA');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Total Afiliados: ' . count($affiliates) . ' | Número de Clínicas: ' . $numeroClinicas);
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F4FD');

        // ============================================
        // UPDATED: Added "Fecha de Nacimiento" to headers
        // ============================================
        $headers = ['#', 'Nombre Completo', 'Cédula de Identidad', 'Fecha de Nacimiento', 'Fecha de Afiliación', 'Plan', 'Cuotas Pagadas', 'Clínica'];

        $column = 'A';
        $headerRow = 5;
        foreach ($headers as $header) {
            $sheet->setCellValue($column . $headerRow, $header);
            $sheet->getStyle($column . $headerRow)->getFont()->setBold(true);
            $column++;
        }

        $row = 6;
        $counter = 1;
        foreach ($affiliates as $affiliate) {
            $contrato = $affiliate->getContratos()
                ->where(['!=', 'estatus', 'Anulado'])
                ->orderBy(['fecha_ini' => SORT_DESC])
                ->one();

            $fechaAfiliacion = '';
            if ($contrato && !empty($contrato->fecha_ini)) {
                $fechaAfiliacion = date('d/m/Y', strtotime($contrato->fecha_ini));
            } elseif ($contrato && !empty($contrato->created_at)) {
                $fechaAfiliacion = date('d/m/Y', strtotime($contrato->created_at));
            } else {
                $fechaAfiliacion = 'No definida';
            }

            // ============================================
            // NEW: Format birth date
            // ============================================
            $fechaNacimiento = '';
            if (!empty($affiliate->fechanac)) {
                $fechaNacimiento = date('d/m/Y', strtotime($affiliate->fechanac));
            } else {
                $fechaNacimiento = 'No definida';
            }

            $planNombre = $affiliate->plan ? $affiliate->plan->nombre : '';

            $totalPaid = \app\models\Cuotas::find()
                ->innerJoin('contratos', 'contratos.id = cuotas.contrato_id')
                ->where(['contratos.user_id' => $affiliate->id])
                ->andWhere(['!=', 'contratos.estatus', 'Anulado'])
                ->andWhere(['cuotas.estatus' => 'pagada'])
                ->count();

            $sheet->setCellValue('A' . $row, $counter);
            $sheet->setCellValue('B' . $row, $affiliate->nombres . ' ' . $affiliate->apellidos);
            $sheet->setCellValue('C' . $row, $affiliate->tipo_cedula . '-' . $affiliate->cedula);
            // ============================================
            // NEW: Set birth date value
            // ============================================
            $sheet->setCellValue('D' . $row, $fechaNacimiento);
            $sheet->setCellValue('E' . $row, $fechaAfiliacion);
            $sheet->setCellValue('F' . $row, $planNombre);
            $sheet->setCellValue('G' . $row, $totalPaid);
            $sheet->setCellValue('H' . $row, $affiliate->clinica ? $affiliate->clinica->nombre : '');
            $row++;
            $counter++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A5:H' . ($row - 1))->applyFromArray($styleArray);

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A5:H5')->applyFromArray($headerStyle);

        for ($i = 6; $i < $row; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':H' . $i)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFF2F2F2');
            }
        }

        $sheet->getStyle('A6:A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C6:C' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        // ============================================
        // NEW: Center align birth date column
        // ============================================
        $sheet->getStyle('D6:D' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E6:E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F6:F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G6:G' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $summaryRow = $row + 1;
        $sheet->setCellValue('A' . $summaryRow, 'RESUMEN:');
        $sheet->mergeCells('A' . $summaryRow . ':H' . $summaryRow);
        $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($summaryRow + 1), 'Total de Afiliados: ' . count($affiliates));
        $sheet->setCellValue('B' . ($summaryRow + 1), 'Número de Clínicas: ' . $numeroClinicas);
        $sheet->mergeCells('B' . ($summaryRow + 1) . ':H' . ($summaryRow + 1));

        $sheet->getStyle('A' . $summaryRow . ':A' . ($summaryRow + 1))->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F4FD');

        $filename = 'Reporte_Afiliados_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export affiliates report to PDF
     */
    public function actionExportarPdfAfiliados()
    {
        $searchModel = new \app\models\AfiliadosReportSearch();
        $affiliates = $searchModel->getAllAffiliates(Yii::$app->request->queryParams);

        $filtros = [];
        if (!empty(Yii::$app->request->get('AfiliadosReportSearch')['clinica_id'])) {
            $clinica = RmClinica::findOne(Yii::$app->request->get('AfiliadosReportSearch')['clinica_id']);
            $filtros[] = 'Clínica: ' . ($clinica ? $clinica->nombre : '');
        }
        if (!empty(Yii::$app->request->get('AfiliadosReportSearch')['user_datos_type_id'])) {
            $tipo = UserDatosType::findOne(Yii::$app->request->get('AfiliadosReportSearch')['user_datos_type_id']);
            $filtros[] = 'Tipo: ' . ($tipo ? $tipo->nombre : '');
        }

        $clinicaIds = [];
        foreach ($affiliates as $affiliate) {
            if ($affiliate->clinica_id) {
                $clinicaIds[$affiliate->clinica_id] = true;
            }
        }
        $numeroClinicas = count($clinicaIds);

        $logo = Yii::getAlias('@webroot/img/sispsalogo.jpg');

        $content = $this->renderPartial('_reporte_pdf_afiliados', [
            'affiliates' => $affiliates,
            'filtros' => $filtros,
            'total' => count($affiliates),
            'numeroClinicas' => $numeroClinicas,
            'logo' => $logo
        ]);

        $pdf = new \kartik\mpdf\Pdf([
            'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
            'format' => \kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT,
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
            'content' => $content,
            'cssInline' => '
            body {
                font-family: Arial, sans-serif;
                font-size: 10pt;
                margin: 0;
                padding: 15px;
                color: #333;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #2c3e50;
            }
            .title {
                font-size: 16pt;
                font-weight: bold;
                margin-bottom: 5px;
                color: #2c3e50;
            }
            .subtitle {
                font-size: 12pt;
                margin-bottom: 5px;
                color: #7f8c8d;
            }
            .date {
                font-size: 10pt;
                color: #95a5a6;
                margin-bottom: 10px;
            }
            .filters {
                margin-bottom: 15px;
                padding: 10px;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 4px;
                font-size: 9pt;
            }
            .filters strong {
                color: #2c3e50;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 15px;
                font-size: 9pt;
            }
            .table th {
                background-color: #2c3e50;
                color: white;
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
                font-weight: bold;
            }
            .table td {
                border: 1px solid #ddd;
                padding: 6px;
            }
            .table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .summary {
                margin-top: 20px;
                padding: 10px;
                background-color: #e8f4fd;
                border: 1px solid #b6d4fe;
                border-radius: 4px;
                font-size: 10pt;
            }
            .summary strong {
                color: #0d6efd;
            }
            .summary-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
            }
            .summary-item {
                flex: 1;
            }
            .footer {
                margin-top: 20px;
                text-align: center;
                font-size: 9pt;
                color: #6c757d;
                border-top: 1px solid #dee2e6;
                padding-top: 10px;
            }
            .col-num {
                width: 5%;
                text-align: center;
            }
            .col-nombre {
                width: 35%;
            }
            .col-cedula {
                width: 20%;
            }
            .col-clinica {
                width: 40%;
            }
        ',
            'options' => [
                'title' => 'Reporte de Afiliados - SISPSA',
            ],
            'methods' => [
                'SetHeader' => ['SISPSA - Reporte de Afiliados|{DATE j-m-Y}|'],
                'SetFooter' => ['|Página {PAGENO} de {nbpg}|'],
            ]
        ]);

        return $pdf->render();
    }

    public function actionReporteAfiliadosDashboard()
    {
        $searchModel = new AfiliadosReportSearch();
        $params = Yii::$app->request->queryParams;

        $summaryByClinic = $searchModel->getSummaryByClinic($params);
        $summaryByPlan = $searchModel->getSummaryByPlan($params);
        $timelineData = $searchModel->getTimelineData($params);
        $topClinics = $searchModel->getTopClinics(8, $params);
        $totals = $searchModel->getTotals($params);

        $chartData = [
            'clinicLabels' => array_column($summaryByClinic, 'clinica_nombre'),
            'clinicTotals' => array_column($summaryByClinic, 'total_afiliados'),
            'clinicIndividual' => array_column($summaryByClinic, 'tipo_individual'),
            'clinicCorporativo' => array_column($summaryByClinic, 'tipo_corporativo'),
            'clinicActivos' => array_column($summaryByClinic, 'activos'),
            'timelineLabels' => array_column($timelineData, 'month'),
            'timelineTotals' => array_column($timelineData, 'total'),
            'timelineIndividual' => array_column($timelineData, 'individual'),
            'timelineCorporativo' => array_column($timelineData, 'corporativo'),
            'planLabels' => array_column($summaryByPlan, 'plan_category'),
            'planTotals' => array_column($summaryByPlan, 'total_afiliados'),
        ];

        if (UserHelper::hasClinicAccess()) {
            $clinicas = UserHelper::getAccessibleClinicas();
        } else {
            $clinicas = RmClinica::find()
                ->select(['id', 'nombre'])
                ->where(['IS', 'deleted_at', null])
                ->orderBy(['nombre' => SORT_ASC])
                ->asArray()
                ->all();
        }

        $clinicaList = \yii\helpers\ArrayHelper::map($clinicas, 'id', 'nombre');

        $planes = Planes::find()
            ->select(['id', 'nombre'])
            ->where(['estatus' => 'Activo'])
            ->orderBy(['nombre' => SORT_ASC])
            ->asArray()
            ->all();
        $planList = \yii\helpers\ArrayHelper::map($planes, 'id', 'nombre');

        $tipoAfiliadoList = UserDatosType::getList();

        return $this->render('reporte-afiliados-dashboard', [
            'searchModel' => $searchModel,
            'summaryByClinic' => $summaryByClinic,
            'summaryByPlan' => $summaryByPlan,
            'timelineData' => $timelineData,
            'topClinics' => $topClinics,
            'totals' => $totals,
            'clinicaList' => $clinicaList,
            'clinicas' => $clinicas,
            'planList' => $planList,
            'tipoAfiliadoList' => $tipoAfiliadoList,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Export Resumen Detallado por Clínica to Excel with Logo and Meta Field
     */
    public function actionExportarResumenExcel()
    {
        $searchModel = new AfiliadosReportSearch();
        $summary = $searchModel->getSummaryByClinic(Yii::$app->request->queryParams);
        $totals = $searchModel->getTotals(Yii::$app->request->queryParams);

        $filtros = $this->getFilterLabels(Yii::$app->request->get('AfiliadosReportSearch', []));

        $logoPath = Yii::getAlias('@webroot/img/sispsalogo.jpg');
        $logoExists = file_exists($logoPath);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen por Clínica');

        $currentRow = 1;

        if ($logoExists) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo SISPSA');
            $drawing->setDescription('Logo SISPSA');
            $drawing->setPath($logoPath);
            $drawing->setHeight(120);
            $drawing->setCoordinates('A' . $currentRow);
            $drawing->setOffsetX(10);
            $drawing->setWorksheet($sheet);

            $sheet->mergeCells('A' . $currentRow . ':J' . ($currentRow + 5));
            $sheet->getStyle('A' . $currentRow . ':J' . ($currentRow + 5))->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $currentRow += 6;
        }

        $sheet->setCellValue('A' . $currentRow, 'RESUMEN DE AFILIADOS POR CLÍNICA');
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013');
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setSize(10);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'R.I.F.: J-50654922');
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setSize(10);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Generado: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A' . $currentRow . ':J' . $currentRow);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $currentRow)->getFont()->setSize(9);
        $currentRow++;

        $currentRow++;

        if (!empty($filtros)) {
            $sheet->setCellValue('A' . $currentRow, 'Filtros aplicados:');
            $sheet->setCellValue('B' . $currentRow, implode(' | ', $filtros));
            $sheet->mergeCells('B' . $currentRow . ':J' . $currentRow);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
            $currentRow++;
            $currentRow++;
        }

        $headers = [
            'A' => 'Clínica',
            'B' => 'Meta Mensual',
            'C' => 'Total Afiliados',
            'D' => 'Individual',
            'E' => 'Corporativo',
            'F' => 'Activos',
            'G' => 'Suspendidos',
            'H' => 'Anulados',
            'I' => 'Vencidos',
            'J' => 'Registrados',
        ];

        $headerRow = $currentRow;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2c3e50'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->applyFromArray($headerStyle);

        $dataRow = $headerRow + 1;
        foreach ($summary as $clinic) {
            $meta = (int)($clinic['clinica_meta'] ?? 0);
            $metaDisplay = $meta > 0 ? number_format($meta) : 'No definida';

            $totalAfiliadosClinica = $clinic['total_afiliados'];
            $metaPorcentaje = $meta > 0 ? round(($totalAfiliadosClinica / $meta) * 100) : 0;

            $sheet->setCellValue('A' . $dataRow, $clinic['clinica_nombre']);
            $sheet->setCellValue('B' . $dataRow, $metaDisplay);
            $sheet->setCellValue('C' . $dataRow, $clinic['total_afiliados']);
            $sheet->setCellValue('D' . $dataRow, $clinic['tipo_individual']);
            $sheet->setCellValue('E' . $dataRow, $clinic['tipo_corporativo']);
            $sheet->setCellValue('F' . $dataRow, $clinic['contratos_activos'] ?? 0);
            $sheet->setCellValue('G' . $dataRow, $clinic['contratos_suspendidos'] ?? 0);
            $sheet->setCellValue('H' . $dataRow, $clinic['contratos_anulados'] ?? 0);
            $sheet->setCellValue('I' . $dataRow, $clinic['contratos_vencidos'] ?? 0);
            $sheet->setCellValue('J' . $dataRow, $clinic['contratos_registrados'] ?? 0);

            if ($meta > 0) {
                $sheet->getComment('B' . $dataRow)
                    ->getText()
                    ->createTextRun("Cumplimiento: {$metaPorcentaje}%\nAfiliados: {$totalAfiliadosClinica} / {$meta}");
            }

            $dataRow++;
        }

        $totalRow = $dataRow;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL GENERAL');
        $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
        $sheet->setCellValue('B' . $totalRow, '');
        $sheet->setCellValue('C' . $totalRow, $totals['total_afiliados']);
        $sheet->setCellValue('D' . $totalRow, $totals['total_individual']);
        $sheet->setCellValue('E' . $totalRow, $totals['total_corporativo']);
        $sheet->setCellValue('F' . $totalRow, $totals['total_contratos_activos']);
        $sheet->setCellValue('G' . $totalRow, $totals['total_contratos_suspendidos']);
        $sheet->setCellValue('H' . $totalRow, $totals['total_contratos_anulados'] ?? 0);
        $sheet->setCellValue('I' . $totalRow, $totals['total_contratos_vencidos'] ?? 0);
        $sheet->setCellValue('J' . $totalRow, $totals['total_contratos_registrados'] ?? 0);

        $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $totalRow . ':J' . $totalRow)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F4FD');

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A' . $headerRow . ':J' . $totalRow)->applyFromArray($styleArray);

        foreach (range('B', 'J') as $col) {
            $sheet->getStyle($col . $headerRow . ':' . $col . $totalRow)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $totalRow)
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->createSheet();
        $spreadsheet->setActiveSheetIndex(1);
        $sheet2 = $spreadsheet->getActiveSheet();
        $sheet2->setTitle('Resumen General');

        $currentRow2 = 1;

        if ($logoExists) {
            $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing2->setName('Logo SISPSA');
            $drawing2->setDescription('Logo SISPSA');
            $drawing2->setPath($logoPath);
            $drawing2->setHeight(120);
            $drawing2->setCoordinates('A' . $currentRow2);
            $drawing2->setOffsetX(10);
            $drawing2->setWorksheet($sheet2);

            $sheet2->mergeCells('A' . $currentRow2 . ':D' . ($currentRow2 + 5));
            $sheet2->getStyle('A' . $currentRow2 . ':D' . ($currentRow2 + 5))->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $currentRow2 += 6;
        }

        $sheet2->setCellValue('A' . $currentRow2, 'RESUMEN GENERAL DE TODAS LAS CLÍNICAS');
        $sheet2->mergeCells('A' . $currentRow2 . ':D' . $currentRow2);
        $sheet2->getStyle('A' . $currentRow2)->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A' . $currentRow2)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow2++;

        $sheet2->setCellValue('A' . $currentRow2, 'Generado: ' . date('d/m/Y H:i:s'));
        $sheet2->mergeCells('A' . $currentRow2 . ':D' . $currentRow2);
        $sheet2->getStyle('A' . $currentRow2)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow2++;

        if (!empty($filtros)) {
            $sheet2->setCellValue('A' . $currentRow2, 'Filtros aplicados:');
            $sheet2->setCellValue('B' . $currentRow2, implode(' | ', $filtros));
            $sheet2->mergeCells('B' . $currentRow2 . ':D' . $currentRow2);
            $sheet2->getStyle('A' . $currentRow2)->getFont()->setBold(true);
            $currentRow2 += 2;
        } else {
            $currentRow2 += 2;
        }

        $sheet2->setCellValue('A' . $currentRow2, 'META DE AFILIADOS - RESUMEN');
        $sheet2->mergeCells('A' . $currentRow2 . ':D' . $currentRow2);
        $sheet2->getStyle('A' . $currentRow2)->getFont()->setBold(true)->setSize(12);
        $sheet2->getStyle('A' . $currentRow2)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F4FD');
        $currentRow2++;

        $metaTotals = $totals['meta'] ?? [];
        $metaData = [
            ['Clínicas con Meta Definida', number_format($metaTotals['clinicas_con_meta'] ?? 0)],
            ['Clínicas sin Meta Definida', number_format($metaTotals['clinicas_sin_meta'] ?? 0)],
            ['Clínicas que Alcanzaron Meta (≥100%)', number_format($metaTotals['clinicas_que_alcanzaron_meta'] ?? 0)],
            ['Clínicas Cerca de Meta (75-99%)', number_format($metaTotals['clinicas_cerca_meta'] ?? 0)],
            ['Clínicas Lejos de Meta (<75%)', number_format($metaTotals['clinicas_lejos_meta'] ?? 0)],
            ['Total Meta Objetivo (Afiliados)', number_format($metaTotals['total_meta_objetivo'] ?? 0)],
            ['Total Afiliados Actuales', number_format($metaTotals['total_afiliados_actual'] ?? 0)],
            ['Cumplimiento Global', ($metaTotals['porcentaje_global_cumplimiento'] ?? 0) . '%'],
        ];

        foreach ($metaData as $item) {
            $sheet2->setCellValue('A' . $currentRow2, $item[0]);
            $sheet2->setCellValue('B' . $currentRow2, $item[1]);
            $sheet2->getStyle('A' . $currentRow2)->getFont()->setBold(true);
            $currentRow2++;
        }

        $currentRow2 += 2;

        $totalsData = [
            ['TOTAL AFILIADOS', number_format($totals['total_afiliados']), 'en todas las clínicas'],
            ['INDIVIDUALES', number_format($totals['total_individual']), round(($totals['total_individual'] / max($totals['total_afiliados'], 1)) * 100) . '% del total'],
            ['CORPORATIVOS', number_format($totals['total_corporativo']), round(($totals['total_corporativo'] / max($totals['total_afiliados'], 1)) * 100) . '% del total'],
            ['ACTIVOS', number_format($totals['total_contratos_activos']), 'usuarios con contrato activo'],
            ['SUSPENDIDOS', number_format($totals['total_contratos_suspendidos']), 'usuarios con cuotas vencidas'],
            ['ANULADOS', number_format($totals['total_contratos_anulados'] ?? 0), 'usuarios con contratos anulados'],
            ['TOTAL CLÍNICAS', number_format($totals['total_clinicas']), 'con afiliados registrados'],
            ['TOTAL CONTRATOS', number_format($totals['total_contratos'] ?? 0), 'registrados en sistema'],
            ['CONTRATOS ACTIVOS', number_format($totals['total_contratos_activos_count'] ?? 0), 'en vigencia'],
        ];

        foreach ($totalsData as $index => $item) {
            $row = $currentRow2 + ($index * 3);

            $sheet2->setCellValue('A' . $row, $item[0]);
            $sheet2->setCellValue('B' . $row, $item[1]);
            $sheet2->setCellValue('C' . $row, $item[2]);

            $sheet2->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11);
            $sheet2->getStyle('B' . $row)->getFont()->setBold(true)->setSize(14);
            $sheet2->getStyle('B' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKBLUE));

            $sheet2->getStyle('A' . $row . ':C' . $row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF5F5F5');

            $sheet2->getStyle('A' . ($row + 1) . ':C' . ($row + 1))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF5F5F5');
        }

        foreach (range('A', 'C') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Resumen_Afiliados_por_Clinica_' . date('Y-m-d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Resumen Detallado por Clínica to PDF
     */
    public function actionExportarResumenPdf()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $searchModel = new AfiliadosReportSearch();
            $summary = $searchModel->getSummaryByClinic(Yii::$app->request->queryParams);
            $totals = $searchModel->getTotals(Yii::$app->request->queryParams);
            $metaSummary = $searchModel->getMetaAchievementSummary(Yii::$app->request->queryParams);

            $filtros = $this->getFilterLabels(Yii::$app->request->get('AfiliadosReportSearch', []));
            $logo = Yii::getAlias('@webroot/img/sispsalogo.jpg');

            $content = $this->renderPartial('_reporte_resumen_pdf_v2', [
                'summary' => $summary,
                'totals' => $totals,
                'metaSummary' => $metaSummary,
                'filtros' => $filtros,
                'logo' => $logo,
            ]);

            if (empty(trim($content))) {
                throw new \Exception('Generated HTML content is empty');
            }

            Yii::info('PDF Content (first 500 chars): ' . substr($content, 0, 500), 'pdf');

            $pdf = new \kartik\mpdf\Pdf([
                'mode' => \kartik\mpdf\Pdf::MODE_UTF8,
                'format' => \kartik\mpdf\Pdf::FORMAT_A4,
                'orientation' => \kartik\mpdf\Pdf::ORIENT_LANDSCAPE,
                'destination' => \kartik\mpdf\Pdf::DEST_BROWSER,
                'content' => $content,
                'options' => [
                    'title' => 'Resumen de Afiliados por Clínica - SISPSA',
                    'default_font_size' => 10,
                    'default_font' => 'Arial',
                    'autoLangToFont' => true,
                ],
                'methods' => [
                    'SetHeader' => ['SISPSA - Resumen de Afiliados por Clínica|{DATE j-m-Y}|'],
                    'SetFooter' => ['|Página {PAGENO} de {nbpg}|'],
                ]
            ]);

            return $pdf->render();
        } catch (\Exception $e) {
            Yii::error("PDF Generation Error: " . $e->getMessage(), 'pdf');
            Yii::error($e->getTraceAsString(), 'pdf');

            echo "Error generating PDF: " . $e->getMessage();
            exit;
        }
    }

    /**
     * Helper method to get filter labels for report header
     */
    private function getFilterLabels($params)
    {
        $filtros = [];

        if (!empty($params['clinica_ids']) && is_array($params['clinica_ids'])) {
            $clinicas = RmClinica::find()
                ->select(['nombre'])
                ->where(['id' => $params['clinica_ids']])
                ->column();
            if (!empty($clinicas)) {
                $filtros[] = 'Clínicas: ' . implode(', ', $clinicas);
            }
        } elseif (!empty($params['clinica_id'])) {
            $clinica = RmClinica::findOne($params['clinica_id']);
            $filtros[] = 'Clínica: ' . ($clinica ? $clinica->nombre : '');
        }

        if (!empty($params['user_datos_type_id'])) {
            $tipo = UserDatosType::findOne($params['user_datos_type_id']);
            $filtros[] = 'Tipo: ' . ($tipo ? $tipo->nombre : '');
        }

        if (!empty($params['plan_id'])) {
            $plan = Planes::findOne($params['plan_id']);
            $filtros[] = 'Plan: ' . ($plan ? $plan->nombre : '');
        }

        if (!empty($params['estatus'])) {
            $filtros[] = 'Estado: ' . $params['estatus'];
        }

        if (!empty($params['date_from'])) {
            $filtros[] = 'Desde: ' . $params['date_from'];
        }

        if (!empty($params['date_to'])) {
            $filtros[] = 'Hasta: ' . $params['date_to'];
        }

        return $filtros;
    }

    /**
     * Lists all UserDatos models associated with a specific Agente (Agency).
     */
    public function actionIndexByAgente($agente_id)
    {
        $agente = Agente::findOne($agente_id);
        if ($agente === null) {
            throw new NotFoundHttpException('La agencia especificada no existe.');
        }

        $agenteFuerzaIds = AgenteFuerza::find()
            ->where(['agente_id' => $agente_id])
            ->select('id')
            ->column();

        $asesorUserIds = AgenteFuerza::find()
            ->where(['agente_id' => $agente_id])
            ->select('idusuario')
            ->column();

        $allAgenteFuerzaIds = [];
        if (!empty($asesorUserIds)) {
            $allAgenteFuerzaIds = AgenteFuerza::find()
                ->where(['idusuario' => $asesorUserIds])
                ->select('id')
                ->column();
        }

        $filterCondition = ['or'];

        if (!empty($allAgenteFuerzaIds)) {
            $filterCondition[] = ['user_datos.asesor_id' => $allAgenteFuerzaIds];
        }

        $filterCondition[] = ['user_datos.agencia_id' => $agente_id];

        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere($filterCondition);
        $dataProvider->query->andWhere(['user_datos.role' => 'afiliado']);

        $this->view->title = 'Afiliados de la Agencia: ' . $agente->nom;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'agente' => $agente,
            'agente_id' => $agente_id,
        ]);
    }
}
