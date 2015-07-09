<?php

use yii\db\Schema;
use yii\db\Migration;

class m150709_182608_create_record_task extends Migration
{
    public function up()
    {
        $this->createTable('{{record_task}}', [
            'id'        => 'pk',
            'record_id' => 'INTEGER NOT NULL',
            'task_id'   => 'INTEGER NOT NULL',
            'created'   => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{record_task}}');
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
