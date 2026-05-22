<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\PlanServicios;
use app\models\Planes;

class ImportPlanServiciosController extends Controller
{
    private $sheetToPlanPattern = [
        'Bronce Individual' => ['BRONCE INDIVIDUAL', 'BRONCE'],
        'Plata Individual' => ['PLATA INDIVIDUAL', 'PLATA'],
        'Oro Individual' => ['ORO INDIVIDUAL', 'ORO'],
        'Esmeralda_Plus Individual' => ['ESMERALDA PLUS INDIVIDUAL', 'ESMERALDA PLUS', 'ESMERALDA'],
        'Bronce Colectivo' => ['BRONCE CORPORATIVO', 'BRONCE COLECTIVO'],
        'Plata Colectivo' => ['PLATA CORPORATIVO', 'PLATA COLECTIVO'],
        'Oro Colectivo' => ['ORO CORPORATIVO', 'ORO COLECTIVO'],
        'Esmeralda_Plus Colectivo' => ['ESMERALDA PLUS CORPORATIVO', 'ESMERALDA PLUS COLECTIVO'],
    ];

    public function actionIndex($filePath = null)
    {
        if ($filePath === null) {
            $this->showHelp();
            return ExitCode::OK;
        }

        $this->stdout("\n============================================\n", Console::FG_CYAN);
        $this->stdout("   IMPORT PLAN SERVICIOS FROM EXCEL\n", Console::FG_CYAN);
        $this->stdout("============================================\n\n");

        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            $this->stderr("ERROR: PhpSpreadsheet is not installed.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $filePath = \Yii::getAlias($filePath);

        if (!file_exists($filePath)) {
            $this->stderr("Error: File not found: {$filePath}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("✓ File found: {$filePath}\n\n");

        $this->stdout("⚠ WARNING: This will DELETE ALL existing data in plan_servicios\n", Console::FG_YELLOW);
        $this->stdout("\nProceed? (y/n): ");
        $confirm = trim(fgets(STDIN));

        if (strtolower($confirm) !== 'y') {
            $this->stdout("Import cancelled.\n");
            return ExitCode::OK;
        }

        // Delete existing data
        $this->stdout("\nDeleting existing data...\n");
        $deleted = PlanServicios::deleteAll();
        $this->stdout("✓ Deleted {$deleted} records from plan_servicios\n");

        // Clear exclusiones
        $cleared = Planes::updateAll(['exclusiones' => null]);
        $this->stdout("✓ Cleared exclusiones for {$cleared} plans\n");

        // Load Excel file
        $this->stdout("\nLoading Excel file...\n");
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $this->stdout("✓ File loaded successfully.\n\n");
        } catch (\Exception $e) {
            $this->stderr("Error loading file: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        // Get ALL plans from database
        $allPlans = Planes::find()->all();
        $this->stdout("Found " . count($allPlans) . " plans in database.\n\n");

        $totalServicesInserted = 0;
        $matchedPlans = 0;
        $unmatchedPlans = [];

        foreach ($allPlans as $plan) {
            $planName = strtoupper(trim($plan->nombre));
            $this->stdout("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n", Console::FG_YELLOW);
            $this->stdout("Processing: {$plan->nombre} (ID: {$plan->id})\n", Console::FG_YELLOW);

            // Find matching sheet
            $matchedSheet = $this->findMatchingSheet($planName);

            if ($matchedSheet === null) {
                $this->stdout("  ⚠ No matching Excel sheet found.\n", Console::FG_YELLOW);
                $unmatchedPlans[] = $plan->nombre;
                continue;
            }

            $matchedPlans++;
            $this->stdout("  ✓ Matched to sheet: {$matchedSheet}\n");

            $sheet = $spreadsheet->getSheetByName($matchedSheet);
            if (!$sheet) {
                $this->stderr("  ✗ Sheet not found.\n", Console::FG_RED);
                continue;
            }

            // Extract services
            $services = $this->extractServicesFromSheet($sheet);
            $exclusions = $this->extractExclusionsFromSheet($sheet);

            $this->stdout("  Found " . count($services) . " services\n");

            // Save exclusions
            if (!empty($exclusions)) {
                $plan->exclusiones = $exclusions;
                $plan->save(false);
                $this->stdout("  ✓ Exclusions saved\n");
            }

            // Insert services
            $inserted = 0;
            foreach ($services as $index => $service) {
                $ps = new PlanServicios();
                $ps->plan_id = $plan->id;
                $ps->clinica_id = null;
                $ps->servicio_nombre = $service['nombre'];
                $ps->plazo_espera = $service['plazo_espera'];
                $ps->descripcion = $service['descripcion'];
                $ps->orden = $index;

                if ($ps->save()) {
                    $inserted++;
                }
            }

            $totalServicesInserted += $inserted;
            $this->stdout("  ✓ Inserted {$inserted} services\n");
        }

        // Summary
        $this->stdout("\n============================================\n", Console::FG_GREEN);
        $this->stdout("              IMPORT SUMMARY\n", Console::FG_GREEN);
        $this->stdout("============================================\n");
        $this->stdout("Total plans in database: " . count($allPlans) . "\n");
        $this->stdout("Plans matched to Excel: {$matchedPlans}\n");
        $this->stdout("Unmatched plans: " . count($unmatchedPlans) . "\n");
        $this->stdout("Total services inserted: {$totalServicesInserted}\n");

        if (!empty($unmatchedPlans)) {
            $this->stdout("\n⚠ Unmatched plans:\n", Console::FG_YELLOW);
            foreach ($unmatchedPlans as $planName) {
                $this->stdout("  - {$planName}\n");
            }
        }

        $this->stdout("\n✅ Import completed!\n", Console::FG_GREEN);

        return ExitCode::OK;
    }

    /**
     * STANDARDIZE PLAN NAMES
     * Usage: php yii import-plan-servicios/standardize
     */
    public function actionStandardize()
    {
        $this->stdout("\n============================================\n", Console::FG_CYAN);
        $this->stdout("        STANDARDIZE PLAN NAMES\n", Console::FG_CYAN);
        $this->stdout("============================================\n\n");

        $this->stdout("This will standardize all plan names to UPPERCASE.\n\n");

        $this->stdout("Proceed? (y/n): ");
        $confirm = trim(fgets(STDIN));

        if (strtolower($confirm) !== 'y') {
            $this->stdout("Cancelled.\n");
            return ExitCode::OK;
        }

        $updated = 0;

        // Individual Plans
        $individualMappings = [
            'BRONCE INDIVIDUAL' => ['%bronce%individual%', '%bronce%'],
            'PLATA INDIVIDUAL' => ['%plata%individual%', '%plata%'],
            'ORO INDIVIDUAL' => ['%oro%individual%', '%oro%'],
            'ESMERALDA PLUS INDIVIDUAL' => ['%esmeralda%individual%', '%esmeralda%'],
        ];

        // Corporativo Plans
        $corporativoMappings = [
            'BRONCE CORPORATIVO' => ['%bronce%colectivo%', '%bronce%corporativo%'],
            'PLATA CORPORATIVO' => ['%plata%colectivo%', '%plata%corporativo%'],
            'ORO CORPORATIVO' => ['%oro%colectivo%', '%oro%corporativo%'],
            'ESMERALDA PLUS CORPORATIVO' => ['%esmeralda%colectivo%', '%esmeralda%corporativo%'],
        ];

        // Process Individual Plans
        foreach ($individualMappings as $standardName => $patterns) {
            $conditions = ['or'];
            foreach ($patterns as $pattern) {
                $conditions[] = ['ilike', 'nombre', $pattern];
            }

            $count = Planes::updateAll(['nombre' => $standardName], $conditions);
            if ($count > 0) {
                $this->stdout("✓ Updated {$count} plan(s) to '{$standardName}'\n");
                $updated += $count;
            }
        }

        // Process Corporativo Plans
        foreach ($corporativoMappings as $standardName => $patterns) {
            $conditions = ['or'];
            foreach ($patterns as $pattern) {
                $conditions[] = ['ilike', 'nombre', $pattern];
            }

            $count = Planes::updateAll(['nombre' => $standardName], $conditions);
            if ($count > 0) {
                $this->stdout("✓ Updated {$count} plan(s) to '{$standardName}'\n");
                $updated += $count;
            }
        }

        // Convert any remaining to UPPERCASE
        $uppercaseCount = Planes::updateAll(['nombre' => new \yii\db\Expression('UPPER(nombre)')]);
        if ($uppercaseCount > 0) {
            $this->stdout("✓ Converted {$uppercaseCount} plan(s) to UPPERCASE\n");
        }

        $this->stdout("\n✅ Standardized {$updated} plan names.\n");

        return ExitCode::OK;
    }

    private function findMatchingSheet($planName)
    {
        foreach ($this->sheetToPlanPattern as $sheetName => $patterns) {
            $patterns = (array)$patterns;
            foreach ($patterns as $pattern) {
                if (strpos($planName, $pattern) !== false || $planName === $pattern) {
                    return $sheetName;
                }
            }
        }
        return null;
    }

    private function extractServicesFromSheet($sheet)
    {
        $services = [];

        for ($row = 31; $row <= 51; $row++) {
            $servicioNombre = $this->getCellValue($sheet, 'B', $row);

            if (empty($servicioNombre)) {
                continue;
            }

            if (
                stripos($servicioNombre, 'DE LAS EXCLUSIONES') !== false ||
                stripos($servicioNombre, 'AFILIADOS') !== false
            ) {
                break;
            }

            $plazoEspera = $this->getCellValue($sheet, 'F', $row);
            $descripcion = $this->getCellValue($sheet, 'H', $row);

            $servicioNombre = preg_replace('/\s+/', ' ', trim($servicioNombre));
            $plazoEspera = preg_replace('/\s+/', ' ', trim($plazoEspera));
            $descripcion = preg_replace('/\s+/', ' ', trim($descripcion));

            $plazoEspera = !empty($plazoEspera) ? $plazoEspera : 'NO APLICA';

            $services[] = [
                'nombre' => $servicioNombre,
                'plazo_espera' => $plazoEspera,
                'descripcion' => $descripcion ?: 'Servicio incluido en el plan',
            ];
        }

        return $services;
    }

    private function extractExclusionsFromSheet($sheet)
    {
        $exclusions = '';

        for ($row = 52; $row <= 53; $row++) {
            $value = $this->getCellValue($sheet, 'B', $row);
            if (!empty($value)) {
                $exclusions .= ' ' . $value;
            }
        }

        return trim(preg_replace('/\s+/', ' ', $exclusions));
    }

    private function getCellValue($sheet, $column, $row)
    {
        try {
            $cell = $sheet->getCell($column . $row);
            $value = $cell->getValue();
            if ($value !== null && $value !== '') {
                return trim((string)$value);
            }
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    private function showHelp()
    {
        $this->stdout("\n============================================\n", Console::FG_CYAN);
        $this->stdout("   IMPORT PLAN SERVICIOS FROM EXCEL\n", Console::FG_CYAN);
        $this->stdout("============================================\n\n");
        $this->stdout("Usage: php yii import-plan-servicios \"path/to/file.xlsx\"\n\n");
        $this->stdout("Available commands:\n");
        $this->stdout("  php yii import-plan-servicios/plans        - List all plans\n");
        $this->stdout("  php yii import-plan-servicios/verify       - Verify imported data\n");
        $this->stdout("  php yii import-plan-servicios/standardize   - Standardize plan names\n");
        $this->stdout("  php yii import-plan-servicios <file>       - Import data\n\n");
    }

    public function actionPlans()
    {
        $this->stdout("\n============================================\n", Console::FG_CYAN);
        $this->stdout("           PLANS IN DATABASE\n", Console::FG_CYAN);
        $this->stdout("============================================\n\n");

        $plans = Planes::find()->orderBy(['nombre' => SORT_ASC])->all();

        foreach ($plans as $plan) {
            $hasServices = PlanServicios::find()->where(['plan_id' => $plan->id])->count();
            $this->stdout("ID: {$plan->id} | {$plan->nombre} | Services: {$hasServices}\n");
        }

        $this->stdout("\nTotal: " . count($plans) . " plans\n\n");
        return ExitCode::OK;
    }

    public function actionVerify()
    {
        $this->stdout("\n============================================\n", Console::FG_CYAN);
        $this->stdout("           VERIFY IMPORTED DATA\n", Console::FG_CYAN);
        $this->stdout("============================================\n\n");

        $stats = PlanServicios::find()
            ->select(['plan_id', 'COUNT(*) as count'])
            ->groupBy('plan_id')
            ->asArray()
            ->all();

        if (empty($stats)) {
            $this->stdout("No data found. Run import first.\n\n");
            return ExitCode::OK;
        }

        foreach ($stats as $stat) {
            $plan = Planes::findOne($stat['plan_id']);
            $planName = $plan ? $plan->nombre : 'Unknown';
            $this->stdout("Plan: {$planName} | Services: {$stat['count']}\n");
        }

        $totalServices = PlanServicios::find()->count();
        $this->stdout("\nTOTAL SERVICES: {$totalServices}\n\n");

        return ExitCode::OK;
    }
}
