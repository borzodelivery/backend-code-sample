<?php

namespace Dostavista\Features\CourierGreetings;

use Dostavista\Core\Couriers\CourierRow;
use Dostavista\Framework\Database\TableAbstract;

/**
 * Таблица с личными приветствиями курьеров в мобильных приложениях.
 *
 * @method static CourierGreetingRow|null getRow(array $where = [], string[] $order = [], int|null $offset = null)
 * @method static CourierGreetingRow|null getRowById(int|null $id)
 * @method static CourierGreetingRow|null getRowFromSql(string $sql, array $params = [])
 * @method static CourierGreetingRow|null getRowByForeignKey(string $fieldName, int $foreignKeyId)
 * @method static CourierGreetingRow requireRowById(int $id, string|null $errorMessage = null)
 * @method static CourierGreetingRow getOrCreateRowById(int $id)
 * @method static CourierGreetingRow getOrMakeUnsavedRowById(int $id)
 * @method static CourierGreetingRow makeUnsavedRow(array $data = [])
 * @method static CourierGreetingRow createFromArray(array $data = [])
 * @method static CourierGreetingRow[] getRowset(array $where = [], string[] $order = [], int|null $count = null, int|null $offset = null)
 * @method static CourierGreetingRow[] getRowsetByIds(int[] $ids, string[] $order = [], int|null $count = null, int|null $offset = null)
 * @method static CourierGreetingRow[] getRowsetFromSql(string $sql, array $params = [])
 * @method static CourierGreetingRow[] getRowsetByForeignKeys(string $fieldName, int[] $foreignKeyIds)
 * @method static CourierGreetingRow[] warmupGetRowByIdCache(int[] $ids)
 * @method static CourierGreetingRow[] warmupGetRowsetByForeignKeysCache(string $fieldName, int[] $foreignKeyIds)
 */
class CourierGreetingsTable extends TableAbstract {
    protected string $name     = 'courier_greetings';
    protected string $rowClass = CourierGreetingRow::class;

    /**
     * Возвращает текст случайного сообщения для курьера.
     */
    public static function getRandomGreetingMessage(CourierRow $courier): ?string {
        // Определяем местное время в регионе курьера
        $region    = $courier->getRegion();
        $localTime = $region->getLocalDateTime();

        // Выбираем все подходящие приветствия
        $greetings = [];
        foreach (static::getRowset(['is_deleted = 0']) as $greeting) {
            $startTime  = $region->getLocalDateTime($greeting->allowed_to_show_start_time);
            $finishTime = $region->getLocalDateTime($greeting->allowed_to_show_finish_time);
            if ($startTime <= $localTime && $localTime <= $finishTime) {
                $greetings[] = $greeting;
            }
        }

        if (empty($greetings)) {
            return null;
        }

        // Подставляем имя курьера в шаблон
        $greeting = $greetings[array_rand($greetings)];
        return str_ireplace('%name%', $courier->user_name, $greeting->greeting_template);
    }
}
