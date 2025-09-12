<?php

namespace app\controllers;

use Yii;
use app\models\UserDatos;
use app\models\User;
use app\models\UserDatosSearch;
use app\models\CorporativoUser;
use app\models\Corporativo;
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
use yii\web\UploadedFile; // Necesario para manejar la subida de archivos
use PhpOffice\PhpSpreadsheet\IOFactory; // Importa la clase principal
use PhpOffice\PhpSpreadsheet\Reader\Exception; // Para manejar excepciones del lector
use DateTime;
use app\models\Cuotas;
use app\models\TasaCambio;
use app\models\AgenteFuerza;


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

    /**
     * Valida los campos del archivo Excel antes de procesarlo
     * @param array $data Los datos del archivo Excel
     * @return array Array con errores encontrados
     */
    private function validateExcelData($data)
    {
        $errors = [];
        $rowNumber = 1; // Comenzamos en 1 para incluir el encabezado

        foreach ($data as $row) {
            $rowNumber++;
            $rowErrors = [];

            // Validar que la fila no esté completamente vacía
            $isEmptyRow = true;
            foreach ($row as $cellValue) {
                if ($cellValue !== null && $cellValue !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                continue; // Saltar filas completamente vacías
            }

            // Validar email (columna A)
            if (empty($row['A'])) {
                $rowErrors[] = 'Email es obligatorio';
            } elseif (!filter_var($row['A'], FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Email no tiene formato válido';
            }

            // Validar teléfono (columna B)
            if (empty($row['B'])) {
                $rowErrors[] = 'Teléfono es obligatorio';
            } elseif (!preg_match('/^[0-9+\-\s\(\)]{7,15}$/', $row['B'])) {
                $rowErrors[] = 'Teléfono no tiene formato válido';
            }

            // Validar nombres (columna C)
            if (empty($row['C'])) {
                $rowErrors[] = 'Nombres es obligatorio';
            } elseif (strlen($row['C']) < 2) {
                $rowErrors[] = 'Nombres debe tener al menos 2 caracteres';
            }

            // Validar apellidos (columna D)
            if (empty($row['D'])) {
                $rowErrors[] = 'Apellidos es obligatorio';
            } elseif (strlen($row['D']) < 2) {
                $rowErrors[] = 'Apellidos debe tener al menos 2 caracteres';
            }

            // Validar tipo de cédula (columna E)
            if (empty($row['E'])) {
                $rowErrors[] = 'Tipo de cédula es obligatorio';
            } elseif (!in_array(strtoupper($row['E']), ['V', 'E', 'P', 'J'])) {
                $rowErrors[] = 'Tipo de cédula debe ser V, E, P o J';
            }

            // Validar cédula (columna F)
            if (empty($row['F']) && !is_numeric($row['F'])) {
                $rowErrors[] = 'Cédula es obligatoria';
            } elseif (!is_numeric($row['F']) || strlen($row['F']) < 6 || strlen($row['F']) > 10) {
                $rowErrors[] = 'Cédula debe ser numérica y tener entre 6 y 10 dígitos';
            }

            // Validar fecha de nacimiento (columna G)
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

            // Validar sexo (columna H)
            if (empty($row['H'])) {
                $rowErrors[] = 'Sexo es obligatorio';
            } elseif (!in_array(strtoupper($row['H']), ['M', 'F', 'MASCULINO', 'FEMENINO'])) {
                $rowErrors[] = 'Sexo debe ser M, F, Masculino o Femenino';
            }

            // Validar tipo de sangre (columna I) - opcional pero si se proporciona debe ser válido
            if (!empty($row['I'])) {
                $tiposSangre = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                if (!in_array(strtoupper($row['I']), $tiposSangre)) {
                    $rowErrors[] = 'Tipo de sangre debe ser uno de: A+, A-, B+, B-, AB+, AB-, O+, O-';
                }
            }

            // Validar estado - municipio - parroquia (columna J) 
            if (empty($row['J'])) {
                $rowErrors[] = 'Estado - municipio - parroquia es obligatorio';
            } elseif (strlen($row['J']) < 2) {
                $rowErrors[] = 'Estado - municipio - parroquia debe tener al menos 2 caracteres';
            }

            // Validar municipio (columna K) - opcional pero si se proporciona debe ser numérico
            if (empty($row['K'])) {
                $rowErrors[] = 'Ciudad es obligatorio';
            } elseif (strlen($row['K']) < 2) {
                $rowErrors[] = 'Ciudad debe tener al menos 2 caracteres';
            }

            // Validar dirección (columna N)
            if (empty($row['L'])) {
                $rowErrors[] = 'Dirección es obligatoria';
            } elseif (strlen($row['L']) < 10) {
                $rowErrors[] = 'Dirección debe tener al menos 10 caracteres';
            }

            // Si hay errores en esta fila, agregarlos al array de errores
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
        $rowNumber = 1; // Comenzamos en 1 para incluir el encabezado

        foreach ($data as $row) {
            $rowNumber++;
            
            // Validar que la fila no esté completamente vacía
            $isEmptyRow = true;
            foreach ($row as $cellValue) {
                if ($cellValue !== null && $cellValue !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                continue; // Saltar filas completamente vacías
            }

            $email = trim($row['A']);
            $cedula = trim($row['F']);
            $tipoCedula = trim($row['E']);
            // Verificar duplicados en el archivo
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

        // Verificar duplicados en la base de datos
        if (!empty($emails)) {
            $existingEmails = UserDatos::find()
                ->where(['email' => $emails])
                ->select('email')
                ->column();

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 porque comenzamos en 1 y tenemos encabezado
                
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
                ->where(['cedula' => array_map(function($cedula) {
                    return explode('-', $cedula)[1] ?? $cedula;
                }, $cedulas)])
                ->select('cedula')
                ->column();

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 porque comenzamos en 1 y tenemos encabezado
                
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
        // Agrupar errores por fila
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
            // Agregar todos los errores de esta fila
            $groupedErrors[$row]['errors'] = array_merge($groupedErrors[$row]['errors'], $error['errors']);
        }
        
        // Ordenar por número de fila
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
        // Crear un nuevo objeto Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Definir los encabezados
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

        // Aplicar encabezados
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Agregar datos de ejemplo
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

        // Aplicar formato a los encabezados
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

        // Autoajustar columnas
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Crear el archivo temporal
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = Yii::getAlias('@runtime/template_afiliados.xlsx');
        $writer->save($tempFile);

        // Enviar el archivo al navegador
        return Yii::$app->response->sendFile($tempFile, 'plantilla_afiliados.xlsx', [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'inline' => false
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) use ($tempFile) {
            // Eliminar el archivo temporal después de enviarlo
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
            // Obtener el archivo subido
            $masivoFiles = UploadedFile::getInstancesByName('UserDatos[masivoFile]');

            if (empty($masivoFiles) || !$masivoFiles[0]->tempName) {
                // Si no se subió ningún archivo o el archivo está vacío
                Yii::$app->session->setFlash('error', 'No se ha subido ningún archivo o el archivo está corrupto.');
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
                ]);
            }

            $uploadedFile = $masivoFiles[0];
            $filePath = Yii::getAlias('@app/web/uploads/masivoFiles/' . $uploadedFile->baseName . '.' . $uploadedFile->extension);

            if (!$uploadedFile->saveAs($filePath)) {
                Yii::$app->session->setFlash('error', 'Error al guardar el archivo subido.');
                return $this->render('masivo', [
                    'model' => $model,
                    'modelContrato' => $modelContrato,
                ]);
            }
            $clinica_id = $model->clinica_id;
            $plan_id = $modelContrato->plan_id;
            $monto = $model->plan->precio;
            $fecha_ini = $modelContrato->fecha_ini;
            $fecha_ven = $modelContrato->fecha_ven;
            $fechaCreacion = date('Y-m-d H:i:s');   
            try {
                // Leer archivo .xlsx
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();

                // Establecer el rango de columnas a leer (de A a L)
                // Usamos getHighestRow() para encontrar la última fila con datos
                $highestRow = $sheet->getHighestDataRow(); // Obtiene la última fila con cualquier dato
                $range = 'A1:L' . $highestRow; // Rango de A1 hasta la columna L de la última fila con datos

                // Obtener los datos del rango especificado
                $sheetData = $sheet->rangeToArray(
                    $range,     // El rango de celdas a leer
                    null,       // No aplicar pre-casteo de valores
                    true,       // Formatear celdas (por ejemplo, fechas)
                    true,       // Incluir celdas nulas (vacías en el rango)
                    true        // Incluir las columnas como claves si TRUE (A, B, C...)
                );

                // Filtrar filas vacías (todas las columnas de A a N están vacías)
                $filteredData = [];
                foreach ($sheetData as $row) {
                    // Revisa si TODAS las celdas en el rango A-N de la fila están vacías
                    $isEmptyRow = true;
                    foreach ($row as $cellValue) {
                        // Si encuentra cualquier valor no nulo o no una cadena vacía, la fila no está vacía
                        if ($cellValue !== null && $cellValue !== '') {
                            $isEmptyRow = false;
                            break;
                        }
                    }
                    if (!$isEmptyRow) {
                        $filteredData[] = $row;
                    }
                }

                // Si la primera fila es un encabezado, la dejamos fuera del array principal de datos
                // y la manejamos por separado si es necesario.
                // Aquí, asumimos que la primera fila podría ser el encabezado y ya fue incluida
                // en $filteredData si tenía datos. Si quieres ignorar el encabezado, puedes:
                if (!empty($filteredData)) {
                    $headers = array_shift($filteredData); // Si la primera fila es el encabezado
                }

                // Validar los datos antes de procesarlos
                $validationErrors = $this->validateExcelData($filteredData);
                $duplicateErrors = $this->validateDuplicates($filteredData);
                
                $allErrors = array_merge($validationErrors, $duplicateErrors);
                
                if (!empty($allErrors)) {
                    // Si hay errores de validación, mostrar el reporte HTML y no procesar
                    $validationReport = $this->generateValidationReport($allErrors, count($filteredData));
                    
                    // Guardar el reporte en la sesión para mostrarlo en la vista
                    Yii::$app->session->setFlash('error', $validationReport);
                    
                    // Eliminar el archivo subido
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    return $this->render('masivo', [
                        'model' => $model,
                        'modelContrato' => $modelContrato,
                    ]);
                }

                // Si no hay errores de validación, proceder con el procesamiento
                // Aquí $filteredData contendrá solo las filas de la A a la N que tienen datos
                foreach ($filteredData as $row) {
                    $contrato = new Contratos();
                    $contrato->clinica_id = $clinica_id;
                    $contrato->plan_id = $plan_id;
                    $contrato->monto = $monto;
                    $contrato->fecha_ini = $fecha_ini;
                    $contrato->fecha_ven = $fecha_ven;
                    $contrato->created_at = $fechaCreacion;
                    $contrato->estatus = 'Creado';
                    $guardadoContrato = $contrato->save();
                    if ($guardadoContrato) {
                        Yii::$app->session->setFlash('success', 'Contrato guardado correctamente.');
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar el contrato.');
                        print_r($contrato->getErrors());
                        exit;
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
                    $guardo = $model->save();
                    if ($guardo) {
                        $contrato->user_id = $model->id;
                        $contrato->save(false);
                        Yii::$app->session->setFlash('success', 'Afiliado guardado correctamente.');
                        $pass = 'sispsa'.$model->cedula;
                        $modelUser = new User();
                        $modelUser->username = $model->email;
                        $modelUser->password_hash = User::setPassword($pass);
                        $modelUser->auth_key = User::generateAuthKey();
                        $modelUser->email = $model->email;
                        $modelUser->status = 1;
                        $guardadoUser = $modelUser->save();
                        if ($guardadoUser) {
                            Yii::$app->session->setFlash('success', 'Usuario guardado correctamente.');
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
                            } else {
                                Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                                print_r($modelUser->getErrors());
                                exit;
                            }
                        } else {
                            Yii::$app->session->setFlash('error', 'Error al guardar el usuario.');
                            print_r($modelUser->getErrors());
                            exit;
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Error al guardar el afiliado.');
                        print_r($model->getErrors());
                        exit;
                    }
                }
                Yii::$app->session->setFlash('success', 'Afiliados guardados correctamente.');
                return $this->redirect(['index']);

            } catch (Exception $e) {
                Yii::error('Error al procesar el archivo Excel: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', 'Error al leer el archivo Excel: ' . $e->getMessage());
                // Asegúrate de eliminar el archivo subido si hubo un error al leerlo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } catch (\Exception $e) { // Captura otras excepciones generales
                Yii::error('Un error inesperado ocurrió: ' . $e->getMessage());
                Yii::$app->session->setFlash('error', 'Un error inesperado ocurrió al procesar el archivo: ' . $e->getMessage());
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
        // Calificar con el nombre de la tabla para evitar ambigüedad (existe join a user_datos como ud_asesor)
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
        

        if($model->estatus_solvente == "" || $model->estatus_solvente == null){
            $model->estatus_solvente = "No";
        }


        //if ($this->request->isPost) {
        if ($model->load($this->request->post()) && $modelContrato->load($this->request->post())) {
            // Procesar grupo familiar
            $grupoFamiliar = $this->request->post('UserDatos')['grupo_familiar'] ?? [];
            if (!empty($grupoFamiliar)) {
                $model->grupo_familiar = json_encode(array_values($grupoFamiliar));
            }
            
            // Procesar datos del contratante si es diferente
            if ($model->tiene_contratante_diferente) {
                // Los datos del contratante ya se cargan automáticamente con load()
            } else {
                // Si no hay contratante diferente, limpiar los campos del contratante
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


                if($model->save()){
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
                                    Yii::$app->session->setFlash('success', 'Identificacion subido con éxito.');
                                } else {
                                    Yii::$app->session->setFlash('error', 'Error al guardar identificacion en la base de datos.');
                                }
                            } else {
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->imagenIdentificacionFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
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
                                    Yii::$app->session->setFlash('success', 'Selfie subido con éxito.');
                                } else {
                                    Yii::$app->session->setFlash('error', 'Error al guardar selfie en la base de datos.');
                                }
                            } else {
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->selfieFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                        }

                    }
            
                    $modelUser->username = $model->email;;
                    $pass = 'sispsa'.$model->cedula;
                    $modelUser->password_hash = User::setPassword($pass);
                    $modelUser->auth_key = User::generateAuthKey();
                    $modelUser->email = $model->email;
                    $modelUser->status = 1;
                    if($modelUser->save()){
                        
                        
                        $modelContrato->user_id = $model->id;
                        $modelContrato->estatus = 'Registrado';
                        $modelContrato->clinica_id = $model->clinica_id;
                        $plan = Planes::find()->where(['id' => $modelContrato->plan_id])->one();
                        $modelContrato->monto = $plan ? $plan->precio : 0;
                        $modelContrato->save();      
                        $modelCuota = new Cuotas();
                        $modelCuota->contrato_id = $modelContrato->id;
                        $modelCuota->fecha_vencimiento = $modelContrato->fecha_ini;
                        $contratoExistente = Contratos::find()->where(['id' => $modelContrato->id])->one();
                        $modelCuota->monto = $contratoExistente ? $contratoExistente->monto : 0;
                        $modelCuota->Estatus = 'pendiente';
                        $tasaCambio = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one();
                        $modelCuota->rate_usd_bs = $tasaCambio ? $tasaCambio->tasa_cambio : 1; // Default to 1 if no record
                        $modelCuota->save();
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

<<<<<<< HEAD
                                // Crear relación con corporativo si es tipo 2 y hay ID de corporativo
                                if ($model->user_datos_type_id == 2 && !empty($model->afiliado_corporativo_id)) {
                                    // Eliminar relación previa si existe para evitar duplicados
                                    CorporativoUser::deleteAll(['user_id' => $model->user_login_id]);

                                    $corporativoUser = new CorporativoUser();
                                    $corporativoUser->corporativo_id = $model->afiliado_corporativo_id;
                                    $corporativoUser->user_id = $model->user_login_id;
                                    $corporativoUser->fecha_vinculacion = date('Y-m-d H:i:s');
                                    if (!$corporativoUser->save()) {
                                        Yii::error('No se pudo guardar la relación en corporativo_user: ' . json_encode($corporativoUser->getErrors()));
=======
                                 $afiliadoCorporativoId = $this->request->post('UserDatos')['afiliado_corporativo_id'] ?? null;
                                if ($afiliadoCorporativoId) {
                                    $modelCorporativoUser = new CorporativoUser();
                                    $modelCorporativoUser->corporativo_id = $afiliadoCorporativoId;
                                    $modelCorporativoUser->user_id = $model->id;
                                    $modelCorporativoUser->fecha_vinculacion = date('Y-m-d H:i:s');
                                    $modelCorporativoUser->rol_en_corporativo = 'afiliado';
                                    
                                    if (!$modelCorporativoUser->save()) {
                                        Yii::error("Error al guardar CorporativoUser: " . json_encode($modelCorporativoUser->errors), __METHOD__);
                                        Yii::$app->session->setFlash('error', 'Error al guardar la relación con el corporativo.');
                                    } else {
                                        Yii::$app->session->setFlash('success', 'Relación con el corporativo guardada con éxito.');
>>>>>>> 8951c50 (ajustes de usuario corporativo)
                                    }
                                } elseif (empty($model->afiliado_corporativo_id)) {
                                    // Si no hay corporativo, eliminar relación existente
                                    CorporativoUser::deleteAll(['user_id' => $model->user_login_id]);
                                }
                                
                            } catch (\Exception $e) {
                                Yii::error("Error al asignar el rol: " . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
                            }
                        } else {
                            Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                        }
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                    else{
                        var_dump($modelUser->errors);
                        exit;
                    }
                }else{
                    var_dump($model->errors);
                    exit;
                }
            }
                
           // }
        /*} else {
            $model->loadDefaultValues();
        }*/

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
   public function actionUpdate($id)
{   
    $model = $this->findModel($id);
    $modelContrato = Contratos::find()->where(['user_id' => $id])->one();
    if ($modelContrato === null) {
        $modelContrato = new Contratos();
        // Puedes asignar otros valores por defecto si es necesario para un nuevo contrato
    }

    if ($this->request->isPost && $model->load($this->request->post()) && $modelContrato->load($this->request->post())) {
        // Debug: Log que se está procesando la actualización
        Yii::info("Iniciando proceso de actualización para UserDatos ID: " . $id, __METHOD__);
        
        // --- PROCESAMIENTO DE DATOS ---
        // Procesar grupo familiar
        $grupoFamiliar = $this->request->post('UserDatos')['grupo_familiar'] ?? [];
        if (!empty($grupoFamiliar)) {
            $model->grupo_familiar = json_encode(array_values($grupoFamiliar));
        } else {
            $model->grupo_familiar = null;
        }
        
        // Procesar datos del contratante si es diferente
        if ($model->tiene_contratante_diferente) {
            // Los datos del contratante ya se cargan automáticamente con load()
        } else {
            // Si no hay contratante diferente, limpiar los campos del contratante
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

        // Lógica de usuario
        if($model->user_login_id == "" || $model->user_login_id == null){
            $modelUser = new User();
            $modelUser->username = $model->email;
            $pass = 'sispsa'.$model->cedula;
            $modelUser->password_hash = User::setPassword($pass);
            $modelUser->auth_key = User::generateAuthKey();
            $modelUser->email = $model->email;
            $modelUser->status = 1;
            $modelUser->save();
            $model->user_login_id = $modelUser->id;
        } else {
            $modelUser = User::findOne($model->user_login_id);
        }

        if($model->estatus_solvente == "" || $model->estatus_solvente == null){
            $model->estatus_solvente = "No";
        }

        $model->role = 'afiliado';
        $model->estatus = 'Registrado';
        $model->updated_at = date('Y-m-d H:i:s');
        
        // --- INTENTAR GUARDAR USERDATOS ---
        if($model->save()){
            Yii::info("UserDatos guardado exitosamente", __METHOD__);

            // --- INTENTAR GUARDAR CONTRATO ---
            $modelContrato->user_id = $id;
            $modelContrato->estatus = 'Creado';
            $modelContrato->clinica_id = $model->clinica_id;
            $plan = Planes::find()->where(['id' => $modelContrato->plan_id])->one();
            $modelContrato->monto = $plan ? $plan->precio : 0;

            if($modelContrato->save()){
                // --- INTENTAR GUARDAR CUOTA Y ASIGNAR ROLES ---
                $cuota = Cuotas::find()->where(['contrato_id' => $modelContrato->id])->orderBy(['fecha_vencimiento' => SORT_ASC])->one();
                if($cuota){
                    $cuota->delete();
                }
                $modelCuota = new Cuotas();
                $modelCuota->contrato_id = $modelContrato->id;
                $modelCuota->fecha_vencimiento = $modelContrato->fecha_ini;
                $contratoExistente = Contratos::find()->where(['id' => $modelContrato->id])->one();
                $modelCuota->monto = $contratoExistente ? $contratoExistente->monto : 0;
                $modelCuota->Estatus = 'pendiente';
                $tasaCambio = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one();
                $modelCuota->rate_usd_bs = $tasaCambio ? $tasaCambio->tasa_cambio : 1;
                $modelCuota->save();
                
                // Asignar roles
                if (isset($modelUser) && $modelUser !== null) {
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

                            // Crear relación con corporativo si es tipo 2 y hay ID de corporativo
                            if ($model->user_datos_type_id == 2 && !empty($model->afiliado_corporativo_id)) {
                                // Eliminar relación previa si existe para evitar duplicados
                                CorporativoUser::deleteAll(['user_id' => $model->user_login_id]);

                                $corporativoUser = new CorporativoUser();
                                $corporativoUser->corporativo_id = $model->afiliado_corporativo_id;
                                $corporativoUser->user_id = $model->user_login_id;
                                $corporativoUser->fecha_vinculacion = date('Y-m-d H:i:s');
                                if (!$corporativoUser->save()) {
                                    Yii::error('No se pudo guardar la relación en corporativo_user: ' . json_encode($corporativoUser->getErrors()));
                                }
                            } elseif (empty($model->afiliado_corporativo_id)) {
                                // Si no hay corporativo, eliminar relación existente
                                CorporativoUser::deleteAll(['user_id' => $model->user_login_id]);
                            }
                        } catch (\Exception $e) {
                            Yii::error("Error al asignar el rol: " . $e->getMessage(), __METHOD__);
                        }
                    } else {
                        Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                    }
                }

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
                                    Yii::$app->session->setFlash('success', 'Identificacion subido con éxito.');
                                } else {
                                    Yii::$app->session->setFlash('error', 'Error al guardar identificacion en la base de datos.');
                                }
                            } else {
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->imagenIdentificacionFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
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
                                    Yii::$app->session->setFlash('success', 'Selfie subido con éxito.');
                                } else {
                                    Yii::$app->session->setFlash('error', 'Error al guardar selfie en la base de datos.');
                                }
                            } else {
                                Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                            }
                        } else {
                            Yii::error("Error al guardar el archivo temporal: " . $model->selfieFile->error, __METHOD__);
                            Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                        }

                    }
                
                // Redirección exitosa después de todo el proceso
                Yii::$app->session->setFlash('success', 'El afiliado fue actualizado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Errores al guardar Contrato
                Yii::error("Error al guardar Contrato: " . json_encode($modelContrato->getErrors()), __METHOD__);
                Yii::$app->session->setFlash('error', 'Error al actualizar el contrato: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $modelContrato->getErrors())));
            }
        } else {
            // Errores al guardar UserDatos
            Yii::error("Error al guardar UserDatos: " . json_encode($model->getErrors()), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error al actualizar los datos del afiliado: ' . implode(', ', array_map(function($errors) {
                return implode(', ', $errors);
            }, $model->getErrors())));
        }
    }

        return $this->render('update', [
            'model' => $model,
            'modelContrato' => $modelContrato,
        ]);
    }

<<<<<<< HEAD
    /* * Deletes an existing UserDatos model.
=======
    if ($this->request->isPost && $model->load($this->request->post()) && $modelContrato->load($this->request->post())) {
        Yii::info("Iniciando proceso de actualización para UserDatos ID: " . $id, __METHOD__);
        
        $grupoFamiliar = $this->request->post('UserDatos')['grupo_familiar'] ?? [];
        if (!empty($grupoFamiliar)) {
            $model->grupo_familiar = json_encode(array_values($grupoFamiliar));
        } else {
            $model->grupo_familiar = null;
        }
        
        if (!$model->tiene_contratante_diferente) {
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

        if($model->user_login_id == "" || $model->user_login_id == null){
            $modelUser = new User();
            $modelUser->username = $model->email;
            $pass = 'sispsa'.$model->cedula;
            $modelUser->password_hash = User::setPassword($pass);
            $modelUser->auth_key = User::generateAuthKey();
            $modelUser->email = $model->email;
            $modelUser->status = 1;
            $modelUser->save();
            $model->user_login_id = $modelUser->id;
        } else {
            $modelUser = User::findOne($model->user_login_id);
        }

        if($model->estatus_solvente == "" || $model->estatus_solvente == null){
            $model->estatus_solvente = "No";
        }

        $model->role = 'afiliado';
        $model->estatus = 'Registrado';
        $model->updated_at = date('Y-m-d H:i:s');
        
        // Aquí es donde se limpia el afiliado_corporativo_id antes de guardar si el tipo es 'Simple'
        if ($model->user_datos_type_id == 1) {
             $model->afiliado_corporativo_id = null;
        }
        
        if($model->save()){
            Yii::info("UserDatos guardado exitosamente", __METHOD__);

            $imagenIdentificacionFiles = UploadedFile::getInstancesByName('UserDatos[imagenIdentificacionFile]');
            $selfieFiles = UploadedFile::getInstancesByName('UserDatos[selfieFile]');

            $model->imagenIdentificacionFile = !empty($imagenIdentificacionFiles) ? reset($imagenIdentificacionFiles) : null;
            $model->selfieFile = !empty($selfieFiles) ? reset($selfieFiles) : null;

            if (!empty($imagenIdentificacionFiles) && $imagenIdentificacionFiles[0]->size > 0) {
                $folder = 'documentos';
                $fileName = uniqid('imagen_identificacion_') . '.' . $model->imagenIdentificacionFile->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                if ($model->imagenIdentificacionFile->saveAs($tempFilePath)) {
                    $fileKeyInBucket = $fileName;
                    $publicUrl = UserHelper::uploadFileToSupabaseApi($tempFilePath, $model->imagenIdentificacionFile->type, $fileKeyInBucket, $folder);
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                    if ($publicUrl) {
                        $model->imagen_identificacion = $publicUrl;
                        if ($model->save(false)) {
                            Yii::$app->session->setFlash('success', 'Identificacion subido con éxito.');
                        } else {
                            Yii::$app->session->setFlash('error', 'Error al guardar identificacion en la base de datos.');
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                    }
                } else {
                    Yii::error("Error al guardar el archivo temporal: " . $model->imagenIdentificacionFile->error, __METHOD__);
                    Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                }
            }
            if (!empty($selfieFiles) && $selfieFiles[0]->size > 0) {
                $folder = 'FotoPerfil';
                $fileName = uniqid('selfie_') . '.' . $model->selfieFile->extension;
                $tempFilePath = Yii::getAlias('@runtime') . '/' . $fileName;
                if ($model->selfieFile->saveAs($tempFilePath)) {
                    $fileKeyInBucket = $fileName;
                    $publicUrl = UserHelper::uploadFileToSupabaseApi($tempFilePath, $model->selfieFile->type, $fileKeyInBucket, $folder);
                    if (file_exists($tempFilePath)) {
                        unlink($tempFilePath);
                    }
                    if ($publicUrl) {
                        $model->selfie = $publicUrl;
                        if ($model->save(false)) {
                            Yii::$app->session->setFlash('success', 'Selfie subido con éxito.');
                        } else {
                            Yii::$app->session->setFlash('error', 'Error al guardar selfie en la base de datos.');
                        }
                    } else {
                        Yii::$app->session->setFlash('error', 'Fallo la subida a Supabase Storage.');
                    }
                } else {
                    Yii::error("Error al guardar el archivo temporal: " . $model->selfieFile->error, __METHOD__);
                    Yii::$app->session->setFlash('error', 'Error al guardar el archivo temporal en el servidor.');
                }
            }

            // --- INICIO DEL CÓDIGO CORREGIDO PARA LA RELACIÓN CON EL CORPORATIVO ---
            // Usamos el valor del modelo, ya que pudo haber sido limpiado arriba
            $afiliadoCorporativoId = $model->afiliado_corporativo_id;
            $userId = $model->id;
            var_dump($afiliadoCorporativoId)."hola <br>";
            $modelCorporativoUser = CorporativoUser::findOne(['user_id' => $userId]);
            if ($afiliadoCorporativoId) {
                if (!$modelCorporativoUser) {
                    $modelCorporativoUser = new CorporativoUser();
                    $modelCorporativoUser->user_id = $userId;
                }
                $modelCorporativoUser->corporativo_id = $afiliadoCorporativoId;
                $modelCorporativoUser->fecha_vinculacion = date('Y-m-d H:i:s');
                $modelCorporativoUser->rol_en_corporativo = 'afiliado';
                
                if (!$modelCorporativoUser->save()) {
                    Yii::error("Error al guardar o actualizar CorporativoUser: " . json_encode($modelCorporativoUser->errors), __METHOD__);
                    Yii::$app->session->setFlash('error', 'Error al actualizar la relación con el corporativo.');
                    echo "MODEL NOT SAVED";
                    //print_r($modelCorporativoUser->getAttributes());
                    //print_r($modelCorporativoUser->getErrors());
                    exit;

                }
            } else {
                if ($modelCorporativoUser) {
                    if (!$modelCorporativoUser->delete()) {
                        Yii::error("Error al eliminar CorporativoUser: " . json_encode($modelCorporativoUser->errors), __METHOD__);
                        Yii::$app->session->setFlash('error', 'Error al eliminar la relación con el corporativo.');
                    } else {
                        Yii::$app->session->setFlash('success', 'Relación con el corporativo eliminada.');
                    }
                }
            }
            // --- FIN DEL CÓDIGO CORREGIDO ---

            $modelContrato->user_id = $id;
            $modelContrato->estatus = 'Creado';
            $modelContrato->clinica_id = $model->clinica_id;
            $plan = Planes::find()->where(['id' => $modelContrato->plan_id])->one();
            $modelContrato->monto = $plan ? $plan->precio : 0;

            if($modelContrato->save()){
                $cuota = Cuotas::find()->where(['contrato_id' => $modelContrato->id])->orderBy(['fecha_vencimiento' => SORT_ASC])->one();
                if($cuota){
                    $cuota->delete();
                }
                $modelCuota = new Cuotas();
                $modelCuota->contrato_id = $modelContrato->id;
                $modelCuota->fecha_vencimiento = $modelContrato->fecha_ini;
                $contratoExistente = Contratos::find()->where(['id' => $modelContrato->id])->one();
                $modelCuota->monto = $contratoExistente ? $contratoExistente->monto : 0;
                $modelCuota->Estatus = 'pendiente';
                $tasaCambio = TasaCambio::find()->where(['fecha' => date('Y-m-d')])->one();
                $modelCuota->rate_usd_bs = $tasaCambio ? $tasaCambio->tasa_cambio : 1;
                $modelCuota->save();
                
                if (isset($modelUser) && $modelUser !== null) {
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
                            Yii::error("Error al asignar el rol: " . $e->getMessage(), __METHOD__);
                        }
                    } else {
                        Yii::$app->session->setFlash('warning', "El rol '$roleName' no existe. Usuario creado, pero el rol no pudo ser asignado.");
                    }
                }
                
                Yii::$app->session->setFlash('success', 'El afiliado fue actualizado exitosamente.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error("Error al guardar Contrato: " . json_encode($modelContrato->getErrors()), __METHOD__);
                Yii::$app->session->setFlash('error', 'Error al actualizar el contrato: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $modelContrato->getErrors())));
            }
        } else {
            Yii::error("Error al guardar UserDatos: " . json_encode($model->getErrors()), __METHOD__);
            Yii::$app->session->setFlash('error', 'Error al actualizar los datos del afiliado: ' . implode(', ', array_map(function($errors) {
                return implode(', ', $errors);
            }, $model->getErrors())));
        }
    }

    return $this->render('update', [
        'model' => $model,
        'modelContrato' => $modelContrato,
    ]);
}
    /**
     * Deletes an existing UserDatos model.
>>>>>>> 8951c50 (ajustes de usuario corporativo)
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

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

    public function actionIndexByAfiliado($asesor_id = "")
    {
        $searchModel = new UserDatosSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['=', 'user_datos.asesor_id', $asesor_id]);
    
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


public function actionGenerarContratov($id)
{
    $model = $this->findModel($id);

    // Inicializar variables para datos del corporativo
    $corporativo = null;
    $hasCorporateRelation = false;

    // Verificar si el afiliado tiene relación con un corporativo
    if (!empty($model->afiliado_corporativo_id)) {
        // Buscar la relación corporativo-user
        $corporativoUser = CorporativoUser::find()
            ->where(['corporativo_id' => $model->afiliado_corporativo_id, 'user_id' => $model->user_login_id])
            ->one();

        if ($corporativoUser) {
            // Obtener los datos del corporativo
            $corporativo = $corporativoUser->corporativo;
            $hasCorporateRelation = true;
        }
    }

    // Obtener los IDs de ubicación del modelo
    $estadoId = (int) $model->estado;
    $municipioId = (int) $model->municipio;
    $parroquiaId = (int) $model->parroquia;
    $ciudadId = (int) $model->ciudad;

    // Buscar los nombres correspondientes a los IDs
    $estadoNombre = RmEstado::findOne($estadoId)->nombre ?? '';
    $municipioNombre = RmMunicipio::findOne($municipioId)->nombre ?? '';
    $parroquiaNombre = RmParroquia::findOne($parroquiaId)->nombre ?? '';
    $ciudadNombre = RmCiudad::findOne($ciudadId)->nombre ?? '';

    // Construir la dirección de residencia completa
    $residenceAddressParts = [];
    if (!empty($model->direccion)) $residenceAddressParts[] = $model->direccion;
    if (!empty($parroquiaNombre)) $residenceAddressParts[] = $parroquiaNombre;
    if (!empty($municipioNombre)) $residenceAddressParts[] = $municipioNombre;
    if (!empty($ciudadNombre)) $residenceAddressParts[] = $ciudadNombre;
    if (!empty($estadoNombre)) $residenceAddressParts[] = $estadoNombre;
    $fullResidenceAddress = implode(', ', array_filter($residenceAddressParts));

    // Process family group to map Spanish keys to English
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
    
    // Process family group to map Spanish keys to English
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
    
    // 💡 CÓDIGO PARA OBTENER LOS DATOS DEL ASESOR 💡
    // -------------------------------------------------------------------------------------------------

        $agenteFuerza = null;
        $asesorUserDatos = null;
        $agente = null; 

     
        if (!empty($model->asesor_id)) {
            $agenteFuerza = AgenteFuerza::findOne($model->asesor_id);
            
            // Si encontramos el registro de AgenteFuerza, usamos su relación para obtener
            // los datos del usuario (UserDatos) y del agente (Agente).
            if ($agenteFuerza) {
                $asesorUserDatos = $agenteFuerza->userDatos;
                $agente = $agenteFuerza->agente;
            }
}
    // -------------------------------------------------------------------------------------------------

    // 💡 CÓDIGO PARA GENERAR EL NÚMERO DE CONTRATO 💡
        $contractNumber = '';
        if ($model->user_datos_type_id == 1) {
            // Si es tipo simple (1), usa el prefijo 'CI'
            $contractNumber = 'CI-' . $model->contrato_id;
        } elseif ($model->user_datos_type_id == 2) {
            // Si es tipo corporativo (2), usa el prefijo 'CO'
            $contractNumber = 'CO-' . $model->contrato_id;
        }
            
    // Preparar los datos para el PDF
    $data = [
        // Datos del Afiliado Propuesto
        'contract_number' => $contractNumber,
        'affiliation_type' => $model->userDatosType ? $model->userDatosType->nombre : '',
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
        
        // DATOS DEL ASESOR
        // -------------------------------------------------------------------------------------------------
        'intermediary_name' => $asesorUserDatos ? $asesorUserDatos->nombres . ' ' . $asesorUserDatos->apellidos : '',
        'intermediary_code' => $agente ? $agente->sudeaseg : '',
        'intermediary_ci' => $asesorUserDatos ? $asesorUserDatos->tipo_cedula . '-' . $asesorUserDatos->cedula : '',
        // -------------------------------------------------------------------------------------------------

        // Datos de la Parte Contratante (se dejan vacíos si no hay campos en UserDatos)
        'contracting_party_name' => ($model->nombre_contratante ?? '') . " " . ($model->apellido_contratante ?? ''),
        'contracting_party_ci' => ($model->tipo_cedula_contratante ?? '') . "-" . ($model->cedula_contratante ?? ''),
        'contracting_party_nationality' => $model->nacionalidad_contratante,
        'contracting_party_marital_status' => $model->estado_civil_contratante,
        'contracting_party_birthplace' => $model->lugar_nacimiento_contratante,
        'contracting_party_birthdate' => $model->fecha_nacimiento_contratante ? Yii::$app->formatter->asDate($model->fecha_nacimiento_contratante, 'yyyy-MM-dd') : '',
        'contracting_party_sex' => $model->sexo_contratante,
        'contracting_party_profession' => $model->profesion_contratante,
        'contracting_party_occupation' => $model->ocupacion_contratante,
        'contracting_party_economic_activity' => $model->actividad_economica_contratante,
        'contracting_party_activity_description' => $model->descripcion_actividad_contratante,
        'contracting_party_annual_income' => $model->ingreso_anual_contratante,
        'contracting_party_residence_address' => $model->direccion_residencia_contratante,
        'contracting_party_phone_residence' => $model->telefono_residencia_contratante,
        'contracting_party_office_address' => $model->direccion_oficina_contratante,
        'contracting_party_phone_office' => $model->telefono_oficina_contratante,
        'contracting_party_cell_phone' => $model->telefono_celular_contratante,
        'contracting_party_email' => $model->email_contratante,
        'contracting_party_billing_address' => $model->direccion_cobro_contratante ?: ($model->direccion_residencia_contratante ?: ''),

        // Representante Legal (del contratante si no hay corporativo, del corporativo si existe)
        'legal_representative_name' => $hasCorporateRelation
            ? ($corporativo->nombre_representante ?? '')
            : (($model->nombre_representante ?? '') . " " . ($model->apellido_representante ?? '')),
        'legal_representative_ci' => $hasCorporateRelation
            ? ($corporativo->cedula_representante ?? '')
            : (($model->tipo_cedula_representante ?? '') . "-" . ($model->cedula_representante ?? '')),
        'legal_representative_nationality' => $hasCorporateRelation
            ? ($corporativo->nacionalidad_representante ?? '')
            : ($model->nacionalidad_representante ?? ''),
        'legal_representative_marital_status' => $hasCorporateRelation
            ? ($corporativo->estado_civil_representante ?? '')
            : ($model->estado_civil_representante ?? ''),
        'legal_representative_birthplace' => $hasCorporateRelation
            ? ($corporativo->lugar_nacimiento_representante ?? '')
            : ($model->lugar_nacimiento_representante ?? ''),
        'legal_representative_birthdate' => $hasCorporateRelation
            ? ($corporativo->fecha_nacimiento_representante ? Yii::$app->formatter->asDate($corporativo->fecha_nacimiento_representante, 'yyyy-MM-dd') : '')
            : ($model->fecha_nacimiento_representante_contratante ? Yii::$app->formatter->asDate($model->fecha_nacimiento_representante_contratante, 'yyyy-MM-dd') : ''),
        'legal_representative_sex' => $hasCorporateRelation
            ? ($corporativo->sexo_representante ?? '')
            : ($model->sexo_representante ?? ''),
        'legal_representative_profession' => $hasCorporateRelation
            ? ($corporativo->profesion_representante ?? '')
            : ($model->profesion_representante ?? ''),
        'legal_representative_occupation' => $hasCorporateRelation
            ? ($corporativo->ocupacion_representante ?? '')
            : ($model->ocupacion_representante ?? ''),
        'legal_representative_activity_description' => $hasCorporateRelation
            ? ($corporativo->descripcion_actividad_representante ?? '')
            : ($model->descripcion_actividad_representante ?? ''),
        'legal_representative_address' => $hasCorporateRelation
            ? ($corporativo->direccion_representante ?? '')
            : ($model->direccion_representante ?? ''),
        'legal_representative_phone' => $hasCorporateRelation
            ? ($corporativo->telefono_representante ?? '')
            : ($model->telefono_representante ?? ''),

        // Datos del Plan (se usan del modelo Plan relacionado)
        'plan_selected' => $model->plan ? $model->plan->nombre : '',
        'plan_currency' => $model->moneda,
        'plan_deductible' => $model->deducible,
        'plan_coverage_limit' => $model->limite_cobertura,
        'maternity_coverage' => $model->cobertura_maternidad,
        'maternity_deductible' => $model->deducible_maternidad,
        'maternity_coverage_limit' => $model->limite_cobertura_maternidad,

        // Grupo Familiar (se deja array vacío si no hay tabla o relación específica)
        'family_group' => (function() use ($model) {
            if (!$model->grupo_familiar) return [];
            $grupoFamiliar = json_decode($model->grupo_familiar, true) ?: [];
            $family_group = [];
            foreach ($grupoFamiliar as $member) {
                $family_group[] = [
                    'name' => $member['nombre'] ?? '',
                    'ci' => $member['cedula'] ?? '',
                    'relationship' => $member['parentesco'] ?? '',
                    'sex' => $member['sexo'] ?? '',
                    'birthdate' => $member['fecha_nacimiento'] ?? '',
                ];
            }
            return $family_group;
        })(),

        // Beneficiario (se dejan vacíos si no hay campos en UserDatos)
        'beneficiary_name' => $model->nombre_beneficiario,
        'beneficiary_ci' => $model->cedula_beneficiario,
        'beneficiary_relationship' => $model->parentesco_beneficiario,
        'beneficiary_sex' => $model->sexo_beneficiario,
        'beneficiary_birthdate' => $model->fecha_nacimiento_beneficiario ? Yii::$app->formatter->asDate($model->fecha_nacimiento_beneficiario, 'yyyy-MM-dd') : '',

        // Cuenta Bancaria (se dejan vacíos si no hay campos en UserDatos)
        'bank_account_holder_name' => $model->nombre_titular,
        'bank_account_ci' => $model->cedula_titular,
        'bank_account_number' => $model->numero_cuenta,
        'bank_name' => $model->banco ? $model->banco->nombre : '',
        'bank_account_type' => $model->tipo_cuenta,

        // Declaración
        'declaration_proposed_affiliate_name' => $model->nombres . " " . $model->apellidos,
        'declaration_proposed_affiliate_ci' => $model->tipo_cedula . "-" . $model->cedula,
        'declaration_contracting_party_name' => ($model->nombre_contratante ?? '') . " " . ($model->apellido_contratante ?? ''),
        'declaration_contracting_party_ci' => ($model->tipo_cedula_contratante ?? '') . "-" . ($model->cedula_contratante ?? ''),
        'declaration_place' => $ciudadNombre,
        'declaration_date' => date('d/m/Y'),

        // Datos del Corporativo (solo si tiene relación)
        'has_corporate_relation' => $hasCorporateRelation,
        'corporate_name' => $corporativo ? $corporativo->nombre : '',
        'corporate_rif' => $corporativo ? $corporativo->rif : '',
        'corporate_mercantile_register' => $corporativo ? $corporativo->tomo_registro . ' ' . $corporativo->folio_registro : '',
        'corporate_registration_date' => $corporativo && $corporativo->fecha_registro_mercantil ? Yii::$app->formatter->asDate($corporativo->fecha_registro_mercantil, 'dd/MM/yyyy') : '',
        'corporate_address' => $corporativo ? $corporativo->direccion : '',
        'corporate_phone' => $corporativo ? $corporativo->telefono : '',
        'corporate_email' => $corporativo ? $corporativo->email : '',
        'corporate_economic_activity' => $corporativo ? $corporativo->actividad_economica : '',
        'corporate_products_services' => $corporativo ? $corporativo->productos_servicios : '',
        'corporate_profit' => $corporativo ? $corporativo->utilidad_ejercicio_anterior : '',
        'corporate_equity' => $corporativo ? $corporativo->patrimonio : '',

        // Datos del Representante Legal del Corporativo
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

    $logo = Yii::getAlias('@webroot/img/sispsalogo.jpg');
    $firmas = Yii::getAlias('@webroot/img/firmas.png');

    // Render the HTML content for the PDF
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
                ->where(['user_datos_type_id' => 2]) // Asume que ID 2 es 'Corporativo'
                ->andFilterWhere(['ilike', 'nombres', $q])
                ->orFilterWhere(['ilike', 'apellidos', $q])
                ->limit(20); // Limita los resultados

            $command = $query->createCommand();
            $data = $command->queryAll();

            $out['results'] = array_values(ArrayHelper::map($data, 'id', function($item) {
                return $item['nombres'] . ' ' . $item['apellidos'] . ' (' . $item['cedula'] . ')';
            }));
        }
        return $out;
    }

    public function actionClinicas(){
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $tipo = $parents[0];
                $corporativo = $parents[1];

                $clinica = [];

                if ($tipo == 1) {
                    // Para tipo 1, los datos ya son del modelo RmClinica
                    $clinica = RmClinica::find()->select(['id', 'nombre'])->all();
                }

                if ($tipo == 2) {
                    $clinica = CorporativoClinica::find()
                        ->joinWith('clinica') // Usa el nombre de tu relación
                        ->where(['corporativo_id' => $corporativo])
                        ->all();
                }

                // Cambiar la forma de acceder a los datos dentro del foreach
                foreach ($clinica as $cli) {
                    if ($tipo == 1) {
                        $out[] = [
                            'id' => $cli->id,
                            'name' => $cli->nombre,
                        ];
                    } elseif ($tipo == 2) {
                        // Acceder a la clínica a través del nombre de la relación 'clinica'
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

    public function actionDatosdelplan(){

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $datos = Yii::$app->getRequest()->post();

            $plan_id = $datos['id'];

            $plan = Planes::find()->where(['id' => $plan_id])->one();
   
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




    
}
