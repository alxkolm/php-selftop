<?php

use yii\db\Schema;
use yii\db\Migration;

class m150806_175853_add_column_record_task extends Migration
{
    public function up()
    {
        $this->addColumn('{{record_task}}', 'is_prediction', $this->boolean()->notNull()->defaultValue(0));
        $this->createIndex('is_prediction', '{{record_task}}', 'is_prediction');
    }

    public function down()
    {
        $this->dropColumn('{{record_task}}', 'is_prediction');
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
