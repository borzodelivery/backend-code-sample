<?php

use Dostavista\Core\Super;
use Dostavista\Framework\Database\Migrations\MysqlMigrationAbstract;

return new class() extends MysqlMigrationAbstract {
    protected function execute(): void {
        Super::getDb()->query("
            CREATE TABLE courier_greetings (
                courier_greeting_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Идентификатор приветствия курьера',
                greeting_template VARCHAR(1024) NOT NULL COMMENT 'Шаблон с текстом приветствия',
                allowed_to_show_start_time TIME NOT NULL DEFAULT '00:00:00' COMMENT 'Допустимое время начала показа приветствия',
                allowed_to_show_finish_time TIME NOT NULL DEFAULT '23:59:59' COMMENT 'Допустимое время окончания показа приветствия',
                is_deleted TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Удалена ли запись',
                INDEX idx_allowed_to_show_time (allowed_to_show_start_time, allowed_to_show_finish_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Личные приветствия курьеров в мобильных приложениях'
        ");
    }
};
