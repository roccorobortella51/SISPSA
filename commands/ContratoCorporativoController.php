<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\components\ContractHelper;

class ContratoCorporativoController extends Controller
{
    /** @var bool Only log what would change. */
    public $dryRun = false;

    /** @var int|null Restrict to one corporate. */
    public $corpId = null;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['dryRun', 'corpId']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['d' => 'dryRun', 'c' => 'corpId']);
    }

    public function actionSyncStatuses()
    {
        $this->stdout("Syncing corporate contract statuses...\n", Console::FG_CYAN);
        if ($this->dryRun) {
            $this->stdout("DRY RUN — no changes will be persisted.\n", Console::FG_YELLOW);
        }

        $result = ContractHelper::syncCorporateContractStatuses($this->corpId, $this->dryRun);

        foreach ($result['details'] as $line) {
            $this->stdout("  {$line}\n", Console::FG_YELLOW);
        }

        $this->stdout("\nSummary:\n", Console::FG_CYAN);
        $this->stdout("  Activated parents : {$result['activated']}\n");
        $this->stdout("  Expired parents   : {$result['expired']}\n");
        $this->stdout("  Children updated  : {$result['children']}\n");

        return ExitCode::OK;
    }
}
