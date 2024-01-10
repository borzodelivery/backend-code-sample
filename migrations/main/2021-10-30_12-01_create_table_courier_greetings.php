<?php declare(strict_types=1);

use Dostavista\Framework\Database\Migrations\CreateTableMysqlMigrationAbstract;

return new class() extends CreateTableMysqlMigrationAbstract {
    protected function getCreateTableSql(): string {
        return "
            CREATE TABLE courier_greetings (
                courier_greeting_id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Courier Greeting ID',
                greeting_template VARCHAR(1024) NOT NULL COMMENT 'Template with greeting text',
                allowed_to_show_start_time TIME NOT NULL DEFAULT '00:00:00' COMMENT 'Allowed to show interval start time',
                allowed_to_show_finish_time TIME NOT NULL DEFAULT '23:59:59' COMMENT 'Allowed to show interval finish time',
                is_deleted TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Has the entry been deleted?',
                INDEX idx_allowed_to_show_time (allowed_to_show_start_time, allowed_to_show_finish_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Personal greetings for couriers in mobile applications';
        ";
    }
};
