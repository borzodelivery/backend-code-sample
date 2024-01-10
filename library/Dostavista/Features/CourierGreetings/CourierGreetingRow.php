<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings;

use Dostavista\Core\Changelogs\ChangelogTable;
use Dostavista\Core\Changelogs\ChangelogTargetsEnum;
use Dostavista\Core\Changelogs\ChangelogTypesEnum;
use Dostavista\Framework\Database\TableRowAbstract;

/**
 * Personal greeting of couriers in mobile applications.
 *
 * @property int    $courier_greeting_id         Courier Greeting ID.
 * @property string $greeting_template           Template with greeting text
 * @property string $allowed_to_show_start_time  Allowed to show interval start time. Default is 00:00:00.
 * @property string $allowed_to_show_finish_time Allowed to show interval finish time. Default is 23:59:59.
 * @property bool   $is_deleted                  Has the entry been deleted?
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
