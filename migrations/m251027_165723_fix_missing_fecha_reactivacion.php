<?php

use yii\db\Migration;

/**
 * Class m251027_170000_fix_missing_fecha_reactivacion
 */
class m251027_170000_fix_missing_fecha_reactivacion extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->stdout("Adding missing fecha_reactivacion column to contratos table...\n");
        
        // Check if column already exists (double safety)
        $tableSchema = Yii::$app->db->getTableSchema('contratos');
        if ($tableSchema && in_array('fecha_reactivacion', $tableSchema->getColumnNames())) {
            $this->stdout("✅ fecha_reactivacion column already exists. No action needed.\n");
            return true;
        }
        
        // Add the column
        $this->addColumn('contratos', 'fecha_reactivacion', $this->date()->null());
        $this->stdout("✅ Successfully added fecha_reactivacion column to contratos table.\n");
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contratos', 'fecha_reactivacion');
        $this->stdout("❌ Dropped fecha_reactivacion column from contratos table.\n");
        
        return true;
    }
}