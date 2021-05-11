<?php

namespace nemmo\attachments\migrations;

use yii\db\Migration;
use yii\db\Schema;

class m210509_160210_add_classes extends Migration
{
    use \nemmo\attachments\ModuleTrait;

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->addColumn($this->getModule()->tableName, 'class', Schema::TYPE_STRING);
        $this->addColumn($this->getModule()->tableName, 'extraFields', Schema::TYPE_TEXT);

        $this->createIndex('file_class', $this->getModule()->tableName, 'class');
    }

    public function down()
    {
        $this->dropColumn($this->getModule()->tableName, 'extraFields');
        $this->dropColumn($this->getModule()->tableName, 'class');
    }
}
