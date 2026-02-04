<?php

use yii\db\Migration;

/**
 * Class m251107_XXXXXX_add_registro_sudeaseg_to_agente_fuerza
 */
class m251107_143949_add_registro_sudeaseg_to_agente_fuerza extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add the new column since it doesn't exist in the database
        $this->addColumn('agente_fuerza', 'registro_corredor_actividad_aseguradora', 'VARCHAR(50) NULL');
        
        echo "Column 'registro_corredor_actividad_aseguradora' added successfully.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove the column when rolling back the migration
        $this->dropColumn('agente_fuerza', 'registro_corredor_actividad_aseguradora');
        
        echo "Column 'registro_corredor_actividad_aseguradora' removed successfully.\n";
    }
}