<?php

use yii\db\Schema;
use yii\db\Migration;

class m150709_182556_create_task extends Migration
{
    public function up()
    {
        $this->createTable('{{task}}', [
            'id'      => 'pk',
            'name'    => Schema::TYPE_STRING,
            'created' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
        ]);
    }

    public function down()
    {
        $this->dropTable('{{task}}');
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
