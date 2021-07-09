<?php

namespace Dostavista\Features\CourierGreetings;

use Dostavista\Core\Changelogs\ChangelogTable;
use Dostavista\Core\Changelogs\ChangelogTargetsEnum;
use Dostavista\Core\Changelogs\ChangelogTypesEnum;
use Dostavista\Framework\Database\TableRowAbstract;

/**
 * Личное приветствие курьеров в мобильных приложениях.
 *
 * @property int    $courier_greeting_id         Идентификатор приветствия курьера.
 * @property string $greeting_template           Шаблон с текстом приветствия.
 * @property string $allowed_to_show_start_time  Допустимое время начала показа приветствия. По умолчанию 00:00:00.
 * @property string $allowed_to_show_finish_time Допустимое время окончания показа приветствия. По умолчанию 23:59:59.
 * @property bool   $is_deleted                  Удалена ли запись.
 */
class CourierGreetingRow extends TableRowAbstract {
    protected function postInsert(): void {
        parent::postInsert();

        ChangelogTable::buildEvent(ChangelogTypesEnum::COURIER_GREETING_CREATED)
            ->fillTableRowCreated(ChangelogTargetsEnum::COURIER_GREETING, $this)
            ->create();
    }

    protected function postUpdate(TableRowAbstract $rowBeforeSave): void {
        parent::postUpdate($rowBeforeSave);

        if ($this->hasDiff($rowBeforeSave)) {
            ChangelogTable::buildEvent(ChangelogTypesEnum::COURIER_GREETING_CHANGED)
                ->fillTableRowChanged(ChangelogTargetsEnum::COURIER_GREETING, $this, $rowBeforeSave)
                ->create();
        }
    }

    protected function postDelete(): void {
        parent::postDelete();

        ChangelogTable::buildEvent(ChangelogTypesEnum::COURIER_GREETING_DELETED)
            ->fillTableRowDeleted(ChangelogTargetsEnum::COURIER_GREETING, $this)
            ->create();
    }
}
