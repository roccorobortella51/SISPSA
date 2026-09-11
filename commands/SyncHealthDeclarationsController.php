<?php
// commands/SyncHealthDeclarationsController.php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\DeclaracionDeSalud;
use app\models\Preexistencias;
use app\models\UserDatos;

/**
 * Synchronize health declarations with pre-existencias.
 * 
 * MAPPING:
 *   - Preexistencias.nombre = User's specific description (p1_especifica, etc.)
 *   - Preexistencias.descripcion = Condition label + User's specific description
 * 
 * Usage:
 *   ./yii sync-health-declarations/index                    # Sync all
 *   ./yii sync-health-declarations/index --user-id=123      # Sync specific user
 *   ./yii sync-health-declarations/index --dry-run=1        # Preview without saving
 *   ./yii sync-health-declarations/index --verbose=1        # Show detailed output
 */
class SyncHealthDeclarationsController extends Controller
{
    /**
     * @var int|string|null User ID to sync. If null, sync all users.
     */
    public $userId = null;

    /**
     * @var bool If true, only preview changes without saving.
     */
    public $dryRun = false;

    /**
     * @var bool If true, show detailed output.
     */
    public $verbose = false;

    /**
     * @var int Batch size for processing records.
     */
    public $batchSize = 100;

    /**
     * @var array Statistics for the sync operation.
     */
    private $stats = [
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'deleted' => 0,
        'errors' => 0,
        'skipped' => 0,
    ];

    /**
     * @var array Error log for failed operations.
     */
    private $errors = [];

    /**
     * @var array Log of all operations performed.
     */
    private $operationLog = [];

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'userId',
            'dryRun',
            'verbose',
            'batchSize',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'u' => 'userId',
            'd' => 'dryRun',
            'v' => 'verbose',
            'b' => 'batchSize',
        ]);
    }

    /**
     * Main action to synchronize health declarations with pre-existencias.
     * 
     * @return int Exit code
     */
    public function actionIndex()
    {
        $this->stdout("\n", Console::FG_GREEN);
        $this->stdout("╔═══════════════════════════════════════════════════════════════╗\n", Console::FG_GREEN);
        $this->stdout("║         S Y N C   H E A L T H   D E C L A R A T I O N S      ║\n", Console::FG_GREEN);
        $this->stdout("╚═══════════════════════════════════════════════════════════════╝\n", Console::FG_GREEN);
        $this->stdout("\n");

        $this->log("Starting health declaration synchronization...", 'info');
        $this->log("Mode: " . ($this->dryRun ? "DRY RUN (Preview only)" : "LIVE (Saving changes)"), 'info');
        $this->log("User ID: " . ($this->userId ?: "All users"), 'info');
        $this->log("Batch size: {$this->batchSize}\n", 'info');

        try {
            // Build the query
            $query = DeclaracionDeSalud::find()
                ->where(['deleted_at' => null])
                ->andWhere(['not', ['user_id' => null]])
                ->with(['user']);

            if ($this->userId) {
                $query->andWhere(['user_id' => (int) $this->userId]);

                // Check if user exists
                $user = UserDatos::findOne($this->userId);
                if (!$user) {
                    $this->log("❌ User ID {$this->userId} not found.", 'error');
                    return ExitCode::UNSPECIFIED_ERROR;
                }
                $this->log("Processing user: {$user->nombres} {$user->apellidos} (ID: {$user->id})", 'info');
            }

            $totalDeclarations = $query->count();
            $this->log("Found {$totalDeclarations} health declarations to process.\n", 'info');

            if ($totalDeclarations === 0) {
                $this->log("✅ No health declarations to process.", 'success');
                return ExitCode::OK;
            }

            // Process in batches
            $offset = 0;
            $processedBatches = 0;

            $this->log("Starting batch processing (batch size: {$this->batchSize})...\n", 'info');

            while (true) {
                // FIXED: Use a new query each time instead of clone()
                $declarations = DeclaracionDeSalud::find()
                    ->where(['deleted_at' => null])
                    ->andWhere(['not', ['user_id' => null]])
                    ->with(['user']);

                if ($this->userId) {
                    $declarations->andWhere(['user_id' => (int) $this->userId]);
                }

                $declarations = $declarations
                    ->orderBy(['user_id' => SORT_ASC, 'created_at' => SORT_ASC])
                    ->offset($offset)
                    ->limit($this->batchSize)
                    ->all();

                if (empty($declarations)) {
                    break;
                }

                $processedBatches++;
                $this->log("\n📦 Processing batch #{$processedBatches} (records {$offset} to " . ($offset + count($declarations) - 1) . ")", 'info');

                if ($this->verbose) {
                    $this->stdout("  Declarations in this batch: " . count($declarations) . "\n", Console::FG_YELLOW);
                }

                // Process each declaration
                foreach ($declarations as $declaration) {
                    $this->processDeclaration($declaration);
                }

                $offset += $this->batchSize;

                // Show progress
                $progress = min(100, round(($offset / $totalDeclarations) * 100));
                $this->showProgress($progress);
            }

            // Clean up orphaned pre-existencias (only in live mode)
            if (!$this->dryRun) {
                $this->stdout("\n", Console::FG_YELLOW);
                $this->log("🧹 Cleaning up orphaned pre-existencias...", 'info');
                $deletedOrphaned = $this->cleanupOrphanedPreexistencias();
                $this->stats['deleted'] += $deletedOrphaned;
            }

            // Show final summary
            $this->showSummary();

            // If this was a dry run, show the operations that would be performed
            if ($this->dryRun && !empty($this->operationLog)) {
                $this->stdout("\n", Console::FG_YELLOW);
                $this->log("📋 DRY RUN SUMMARY - Operations that would be performed:", 'info');
                foreach ($this->operationLog as $log) {
                    $this->stdout("  {$log}\n", Console::FG_YELLOW);
                }
            }

            if ($this->stats['errors'] > 0) {
                $this->stdout("\n", Console::FG_RED);
                $this->log("❌ ERRORS ENCOUNTERED:", 'error');
                foreach ($this->errors as $error) {
                    $this->stdout("  • {$error}\n", Console::FG_RED);
                }
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->stdout("\n", Console::FG_GREEN);
            $this->log("✅ Synchronization completed successfully!", 'success');
            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("\n");
            $this->stderr("❌ Fatal error: " . $e->getMessage() . "\n", Console::FG_RED);
            $this->stderr("  File: " . $e->getFile() . ":" . $e->getLine() . "\n", Console::FG_RED);
            $this->stderr("  Trace:\n" . $e->getTraceAsString() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Process a single health declaration.
     * 
     * @param DeclaracionDeSalud $declaration
     */
    private function processDeclaration($declaration)
    {
        $this->stats['processed']++;

        try {
            // Get the user info for logging
            $user = $declaration->user;
            $userName = $user ? "{$user->nombres} {$user->apellidos} (ID: {$user->id})" : "Unknown User (ID: {$declaration->user_id})";

            if ($this->verbose) {
                $this->stdout("\n  Processing declaration #{$declaration->id} for {$userName}\n", Console::FG_CYAN);
            }

            // Get "Yes" answers
            $yesAnswers = $declaration->getYesAnswers();

            if (empty($yesAnswers)) {
                if ($this->verbose) {
                    $this->log("    No 'Yes' answers found - skipping", 'info');
                }
                $this->stats['skipped']++;
                return;
            }

            if ($this->verbose) {
                $this->log("    Found " . count($yesAnswers) . " 'Yes' answers", 'info');
            }

            // Get the fecha_diagnostico from the declaration's created_at
            $fechaDiagnostico = $declaration->created_at ? date('Y-m-d', strtotime($declaration->created_at)) : null;

            // Process each "Yes" answer
            foreach ($yesAnswers as $key => $answer) {
                $label = $answer['label'];
                $especifica = trim($answer['especifica']);

                // Build the values
                if (!empty($especifica)) {
                    $nombre = $especifica;
                    $descripcion = $label . ': ' . $especifica;
                } else {
                    $nombre = 'Diagnosticado con ' . $label;
                    $descripcion = $label . ': ' . 'Diagnosticado con ' . $label;
                }

                if ($this->verbose) {
                    $this->log("    - {$label}: '{$nombre}' (Fecha diagnóstico: {$fechaDiagnostico})", 'info');
                }

                // Check if pre-existencia exists
                $existing = Preexistencias::find()
                    ->where([
                        'user_id' => $declaration->user_id,
                        'nombre' => $nombre,
                        'deleted_at' => null
                    ])
                    ->one();

                if ($existing) {
                    // Update existing
                    $this->log("    ⚡ Updating pre-existencia ID: {$existing->id} for '{$label}'", 'info');

                    if (!$this->dryRun) {
                        $existing->descripcion = $descripcion;
                        $existing->fecha_diagnostico = $fechaDiagnostico;
                        $existing->estatus = Preexistencias::ESTATUS_ACTIVO;
                        $existing->updated_at = date('Y-m-d H:i:s');

                        if ($existing->save()) {
                            $this->stats['updated']++;
                            $this->operationLog[] = "Updated pre-existencia ID: {$existing->id} for user {$declaration->user_id} - {$label} (Fecha: {$fechaDiagnostico})";
                        } else {
                            throw new \Exception("Failed to update pre-existencia: " . implode(', ', $existing->getErrorSummary(true)));
                        }
                    } else {
                        $this->stats['updated']++;
                        $this->operationLog[] = "[DRY RUN] Would update pre-existencia ID: {$existing->id} for user {$declaration->user_id} - {$label} (Fecha: {$fechaDiagnostico})";
                    }
                } else {
                    // Create new
                    $this->log("    ➕ Creating new pre-existencia for '{$label}'", 'info');

                    if (!$this->dryRun) {
                        $pre = new Preexistencias();
                        $pre->user_id = $declaration->user_id;
                        $pre->nombre = $nombre;
                        $pre->descripcion = $descripcion;
                        $pre->fecha_diagnostico = $fechaDiagnostico;
                        $pre->estatus = Preexistencias::ESTATUS_ACTIVO;
                        $pre->created_at = date('Y-m-d H:i:s');

                        if ($pre->save()) {
                            $this->stats['created']++;
                            $this->operationLog[] = "Created pre-existencia ID: {$pre->id} for user {$declaration->user_id} - {$label} (Fecha: {$fechaDiagnostico})";
                        } else {
                            throw new \Exception("Failed to create pre-existencia: " . implode(', ', $pre->getErrorSummary(true)));
                        }
                    } else {
                        $this->stats['created']++;
                        $this->operationLog[] = "[DRY RUN] Would create pre-existencia for user {$declaration->user_id} - {$label} (Fecha: {$fechaDiagnostico})";
                    }
                }
            }

            // Track processed pre-existencias for this user
            $this->trackProcessedPreexistencias($declaration->user_id, $yesAnswers);
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $errorMsg = "Error processing declaration #{$declaration->id} for user {$declaration->user_id}: " . $e->getMessage();
            $this->errors[] = $errorMsg;
            $this->stderr("\n  ❌ " . $errorMsg . "\n", Console::FG_RED);
        }
    }

    /**
     * Track processed pre-existencias for a user.
     * 
     * @param int $userId
     * @param array $yesAnswers
     */
    private function trackProcessedPreexistencias($userId, $yesAnswers)
    {
        // Get all pre-existencias for this user
        $allPreexistencias = Preexistencias::find()
            ->where(['user_id' => $userId, 'deleted_at' => null])
            ->all();

        // If there are no pre-existencias, nothing to track
        if (empty($allPreexistencias)) {
            return;
        }

        // Build list of current pre-existencia names from Yes answers
        $currentNames = [];
        foreach ($yesAnswers as $answer) {
            $especifica = trim($answer['especifica']);
            if (!empty($especifica)) {
                $currentNames[] = $especifica;
            } else {
                $currentNames[] = 'Diagnosticado con ' . $answer['label'];
            }
        }

        // Flag pre-existencias that are no longer valid
        foreach ($allPreexistencias as $pre) {
            if (!in_array($pre->nombre, $currentNames)) {
                if (!$this->dryRun) {
                    $pre->deleted_at = date('Y-m-d H:i:s');
                    if ($pre->save()) {
                        $this->stats['deleted']++;
                        $this->operationLog[] = "Soft-deleted pre-existencia ID: {$pre->id} for user {$userId} - {$pre->nombre}";
                    }
                } else {
                    $this->stats['deleted']++;
                    $this->operationLog[] = "[DRY RUN] Would soft-delete pre-existencia ID: {$pre->id} for user {$userId} - {$pre->nombre}";
                }
            }
        }
    }

    /**
     * Clean up orphaned pre-existencias that don't have corresponding health declarations.
     * 
     * @return int Number of deleted records
     */
    private function cleanupOrphanedPreexistencias()
    {
        $deletedCount = 0;

        // Find all pre-existencias that don't have a corresponding health declaration
        $orphaned = Preexistencias::find()
            ->alias('p')
            ->leftJoin('declaracion_de_salud d', 'd.user_id = p.user_id AND d.deleted_at IS NULL')
            ->where(['p.deleted_at' => null])
            ->andWhere(['d.id' => null])
            ->all();

        if ($this->verbose) {
            $this->log("Found " . count($orphaned) . " orphaned pre-existencias", 'info');
        }

        foreach ($orphaned as $pre) {
            if ($this->verbose) {
                $this->log("  Deleting orphaned pre-existencia ID: {$pre->id} for user {$pre->user_id} - {$pre->nombre}", 'info');
            }

            $pre->deleted_at = date('Y-m-d H:i:s');
            if ($pre->save()) {
                $deletedCount++;
                $this->operationLog[] = "Deleted orphaned pre-existencia ID: {$pre->id} for user {$pre->user_id}";
            }
        }

        return $deletedCount;
    }

    /**
     * Show a progress bar.
     * 
     * @param int $percentage
     */
    private function showProgress($percentage)
    {
        $barLength = 50;
        $filled = (int) round(($percentage / 100) * $barLength);
        $empty = $barLength - $filled;

        $bar = str_repeat('█', $filled) . str_repeat('░', $empty);

        $this->stdout("\r  Progress: [{$bar}] {$percentage}%", Console::FG_CYAN);
    }

    /**
     * Show the final summary.
     */
    private function showSummary()
    {
        $this->stdout("\n\n", Console::FG_GREEN);
        $this->stdout("╔═══════════════════════════════════════════════════════════════╗\n", Console::FG_GREEN);
        $this->stdout("║                      S Y N C   S U M M A R Y                 ║\n", Console::FG_GREEN);
        $this->stdout("╚═══════════════════════════════════════════════════════════════╝\n", Console::FG_GREEN);
        $this->stdout("\n");

        $this->stdout(sprintf("  %-20s : %10d\n", "Declarations Processed", $this->stats['processed']), Console::FG_YELLOW);
        $this->stdout(sprintf("  %-20s : %10d\n", "Pre-existencias Created", $this->stats['created']), Console::FG_GREEN);
        $this->stdout(sprintf("  %-20s : %10d\n", "Pre-existencias Updated", $this->stats['updated']), Console::FG_BLUE);
        $this->stdout(sprintf("  %-20s : %10d\n", "Pre-existencias Deleted", $this->stats['deleted']), Console::FG_RED);
        $this->stdout(sprintf("  %-20s : %10d\n", "Skipped (No 'Yes' Answers)", $this->stats['skipped']), Console::FG_YELLOW);
        $this->stdout(sprintf("  %-20s : %10d\n", "Errors", $this->stats['errors']), Console::FG_RED);
        $this->stdout("\n");

        if ($this->dryRun) {
            $this->stdout("  ⚠️  DRY RUN - No changes were saved to the database.\n", Console::FG_YELLOW);
        } else {
            $this->stdout("  ✅ Live mode - Changes were saved to the database.\n", Console::FG_GREEN);
        }
        $this->stdout("\n");
    }

    /**
     * Log a message with timestamp.
     * 
     * @param string $message
     * @param string $type info|success|warning|error
     */
    private function log($message, $type = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $prefix = match ($type) {
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌',
            default => 'ℹ️',
        };

        $color = match ($type) {
            'success' => Console::FG_GREEN,
            'warning' => Console::FG_YELLOW,
            'error' => Console::FG_RED,
            default => Console::FG_CYAN,
        };

        $this->stdout("  {$prefix} [{$timestamp}] {$message}\n", $color);
    }

    /**
     * Validate a user exists.
     * 
     * @param int $userId
     * @return bool
     */
    private function validateUser($userId)
    {
        return UserDatos::find()->where(['id' => $userId])->exists();
    }
}
