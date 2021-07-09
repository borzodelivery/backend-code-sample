<?php

namespace Dostavista\Features\CourierGreetings\Views;

use Dostavista\Core\Changelogs\ChangelogRow;
use Dostavista\Core\Changelogs\Views\ChangelogTableViewBlockAbstract;
use Dostavista\Features\CourierGreetings\CourierGreetingsTable;
use Dostavista\Framework\FrameworkUtils;

class CourierGreetingChangelogTableViewBlock extends ChangelogTableViewBlockAbstract {
    public function getTargetColumnName(): string {
        return 'Courier greeting';
    }

    public function getTargetLinkHtml(ChangelogRow $changelogRow): string {
        $greetingId = $changelogRow->target_id;

        $greeting = CourierGreetingsTable::getRowById($greetingId);
        if ($greeting) {
            return '<a href="' . urlHtml('/dispatcher/courier-greetings/edit', $greeting->courier_greeting_id) . '">#' . html($greeting->courier_greeting_id) . '</a>';
        }

        if ($greetingId) {
            return 'Courier greeting #' . html($greetingId);
        }

        return 'Unknown courier greeting';
    }

    /**
     * @param ChangelogRow[] $changelog
     */
    protected static function warmupCache(array $changelog): void {
        $greetingIds = FrameworkUtils::getIntValues($changelog, 'target_id');
        CourierGreetingsTable::warmupGetRowByIdCache($greetingIds);
    }
}
