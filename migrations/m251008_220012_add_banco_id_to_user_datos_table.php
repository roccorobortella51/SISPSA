<?php

use yii\db\Migration;

class m251008_220012_add_banco_id_to_user_datos_table extends Migration
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
        echo "m251008_220012_add_banco_id_to_user_datos_table cannot be reverted.\n";

        return false;
    }

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
    $this->addColumn('{{%user_datos}}', 'banco_id', $this->integer()->after('limite_cobertura'));
    // If it's a foreign key, also add the foreign key constraint
    // $this->addForeignKey(
    //     'fk-user_datos-banco_id',
    //     '{{%user_datos}}',
    //     'banco_id',
    //     '{{%banco}}', // Replace with the actual bank table name
    //     'id',
    //     'CASCADE'
    // );
    }

    public function down()
    {
        // $this->dropForeignKey('fk-user_datos-banco_id', '{{%user_datos}}');
        $this->dropColumn('{{%user_datos}}', 'banco_id');
        echo "m251008_220012_add_banco_id_to_user_datos_table cannot be reverted.\n";

        return false;
    }
}
