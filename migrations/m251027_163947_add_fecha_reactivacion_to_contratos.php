<?php

use yii\db\Migration;

class m251027_163947_add_fecha_reactivacion_to_contratos extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251027_163947_add_fecha_reactivacion_to_contratos cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251027_163947_add_fecha_reactivacion_to_contratos cannot be reverted.\n";

        return false;
    }
    */
}
