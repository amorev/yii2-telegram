<?php

namespace Zvinger\Telegram\migrations;

use yii\db\Migration;

/**
 * Class M180125134858TelegramData
 */
class M180125134858TelegramData extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
         * -- auto-generated definition
CREATE TABLE telegram_user_id_connection
(
  id           INT AUTO_INCREMENT
    PRIMARY KEY,
  user_id      INT         NULL,
  telegram_id  VARCHAR(40) NULL,
  confirm_code VARCHAR(6)  NULL,
  created_at   INT         NULL,
  updated_at   INT         NULL,
  status       INT         NULL,
  CONSTRAINT `FK_telegram_user_id_connection-user_id`
  FOREIGN KEY (user_id) REFERENCES user (id)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

CREATE INDEX `FK_telegram_user_id_connection-user_id`
  ON telegram_user_id_connection (user_id);
         */

        $tableOptions = NULL;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{telegram_user_id_connection}}', [
            'id'           => $this->primaryKey(),
            'user_id'      => $this->integer(),
            'telegram_id'  => $this->string(40),
            'confirm_code' => $this->string(6),
            'created_at'   => $this->integer(),
            'updated_at'   => $this->integer(),
            'status'       => $this->integer(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "M180125134858TelegramData cannot be reverted.\n";

        return FALSE;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180125134858TelegramData cannot be reverted.\n";

        return false;
    }
    */
}
