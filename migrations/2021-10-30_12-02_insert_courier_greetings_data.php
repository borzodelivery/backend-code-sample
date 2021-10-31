<?php

use Dostavista\Framework\Database\Migrations\MysqlMigrationAbstract;

return new class() extends MysqlMigrationAbstract {
    /**
     * @return string[]
     */
    public function getChangedTables(): array {
        return ['courier_greetings'];
    }

    protected function execute(): void {
        if (Super::getConfig()->isRussia()) {
            $this->getDb()->query("
                INSERT INTO courier_greetings (greeting_template, allowed_to_show_start_time, allowed_to_show_finish_time)
                VALUES
                    ('Привет, %name%!', '00:00:00', '23:59:59'),
                    ('С возвращением!', '00:00:00', '23:59:59'),
                    ('Доброй ночи, %name%!', '00:00:00', '04:59:59'),
                    ('Доброе утро, %name%!', '05:00:00', '09:59:59'),
                    ('Добрый день, %name%!', '10:00:00', '17:59:59'),
                    ('Добрый вечер, %name%!', '18:00:00', '23:59:59')
            ");
        } else {
            $this->getDb()->query("
                INSERT INTO courier_greetings (greeting_template, allowed_to_show_start_time, allowed_to_show_finish_time)
                VALUES
                    ('Hello, %name%!', '00:00:00', '23:59:59'),
                    ('Welcome back!', '00:00:00', '23:59:59'),
                    ('Good night %name%!', '00:00:00', '04:59:59'),
                    ('Good morning %name%!', '05:00:00', '09:59:59'),
                    ('Good afternoon %name%!', '10:00:00', '17:59:59'),
                    ('Good evening %name%!', '18:00:00', '23:59:59')
            ");
        }
    }
};
