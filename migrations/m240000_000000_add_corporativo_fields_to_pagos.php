<?php
// migrations/m240000_000000_add_corporativo_fields_to_pagos.php

use yii\db\Migration;

class m240000_000000_add_corporativo_fields_to_pagos extends Migration
{
    public function safeUp()
    {
        // PostgreSQL doesn't support 'AFTER' in ADD COLUMN
        // We'll add the columns and then reorder if needed
        
        // Add corporativo_id
        $this->addColumn('pagos', 'corporativo_id', $this->integer());
        
        // Add pago_corporativo_id
        $this->addColumn('pagos', 'pago_corporativo_id', $this->integer());
        
        // Add tipo_pago
        $this->addColumn('pagos', 'tipo_pago', $this->string(50));
        
        // Add foreign keys
        $this->addForeignKey(
            'fk-pagos-corporativo_id',
            'pagos',
            'corporativo_id',
            'corporativos',
            'id',
            'SET NULL'
        );
        
        $this->addForeignKey(
            'fk-pagos-pago_corporativo_id',
            'pagos',
            'pago_corporativo_id',
            'pagos',
            'id',
            'SET NULL'
        );
        
        // Create indexes
        $this->createIndex('idx-pagos-tipo_pago', 'pagos', 'tipo_pago');
        $this->createIndex('idx-pagos-corporativo_id', 'pagos', 'corporativo_id');
        
        // Optional: Reorder columns (PostgreSQL requires more complex operation)
        // $this->execute("ALTER TABLE pagos ALTER COLUMN corporativo_id SET NOT NULL"); // Example
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-pagos-corporativo_id', 'pagos');
        $this->dropForeignKey('fk-pagos-pago_corporativo_id', 'pagos');
        $this->dropIndex('idx-pagos-tipo_pago', 'pagos');
        $this->dropIndex('idx-pagos-corporativo_id', 'pagos');
        $this->dropColumn('pagos', 'tipo_pago');
        $this->dropColumn('pagos', 'pago_corporativo_id');
        $this->dropColumn('pagos', 'corporativo_id');
    }
}