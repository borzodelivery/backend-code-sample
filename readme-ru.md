
# Borzo Backend Code Sample

🇬🇧 [Switch to English](readme.md)

Давайте по шагам разберём на простой задаче, как мы создаём новые фичи в Борзо (aka Достависте).

Код здесь только ради примера, его не получится запустить, но его можно почитать.

**Да, и приходите работать к нам! У нас интересно.**
<br>🔥 [Наши вакансии на HeadHunter](https://hh.ru/employer/3730831).


## Задача

В курьерском мобильном приложении требуется сделать ежедневное приветствие курьера.

Как это будет работать: курьер первый раз за день открывает приложение и несколько секунд видит приветствие.

![](.github/task.png)

1. Текст приветствия каждый раз случайный.<br>
   Примеры: «Привет, Игорь», «Доброе утро, Андрей!», «Буэнос диас, амиго!».
2. Некоторые варианты («Доброе утро», «Добрый вечер») можно показывать только в определенный интервал времени.<br>
   Например, «Доброе утро» пишем только с 5:00 до 10:00.
3. Список возможных вариантов приветствий и время для их показа можно настраивать в админке.


## Миграции

Чтобы хранить возможные варианты приветствий, создадим таблицу `courier_greetings` в базе данных.
<br>Для этого добавим новый файл с миграцией в директории [migrations](https://github.com/borzodelivery/backend-code-sample/tree/master/migrations). 

**[migrations/main/2021-10-30_12-01_create_table_courier_greetings.php](https://github.com/borzodelivery/backend-code-sample/blob/master/migrations/main/2021-10-30_12-01_create_table_courier_greetings.php)**

```php
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
```

В отдельной миграции наполним нашу таблицу данными.

**[migrations/main/2021-10-30_12-02_insert_courier_greetings_data.php](https://github.com/borzodelivery/backend-code-sample/blob/master/migrations/main/2021-10-30_12-02_insert_courier_greetings_data.php)**

```php
<?php declare(strict_types=1);

use Dostavista\Core\Super;
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
            Super::getDb()->query("
                INSERT INTO courier_greetings
                    (greeting_template, allowed_to_show_start_time, allowed_to_show_finish_time)
                VALUES 
                    ('Привет, %name%!', '00:00:00', '23:59:59'),
                    ('С возвращением!', '00:00:00', '23:59:59'),
                    ('Доброй ночи, %name%!', '00:00:00', '04:59:59'),
                    ('Доброе утро, %name%!', '05:00:00', '09:59:59'),
                    ('Добрый день, %name%!', '10:00:00', '17:59:59'),
                    ('Добрый вечер, %name%!', '18:00:00', '23:59:59')
            ");
        } else {
            Super::getDb()->query("
                INSERT INTO courier_greetings
                    (greeting_template, allowed_to_show_start_time, allowed_to_show_finish_time)
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
```

> ☝️ **Примечание**<br>
> На самом деле в боевом проекте мы не делаем это вручную, а используем специальный консольный скрипт, создающий файлы с миграциями.


## Директория с файлами фичи

Мы не смешиваем весь код в одну кучу, и стараемся аккуратно раскладывать фичи по отдельным папочкам.

Поэтому для нашей новой фичи создадим директорию [library/Dostavista/Features/CourierGreetings](https://github.com/borzodelivery/backend-code-sample/tree/master/library/Dostavista/Features/CourierGreetings).

Все классы и файлы, относящиеся к этой фиче, будем создавать в этой директории.


## Классы для работы с таблицей

Чтобы работать с новой таблицей, нужно создать два класса: `CourierGreetingRow` и `CourierGreetingsTable`.

**[library/Dostavista/Features/CourierGreetings/CourierGreetingsTable.php](https://github.com/borzodelivery/backend-code-sample/blob/master/library/Dostavista/Features/CourierGreetings/CourierGreetingsTable.php)**

```php
<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings;

use Dostavista\Framework\Database\TableAbstract;

/**
 * Table with personal greetings for couriers in mobile applications.
 *
 * @method static CourierGreetingRow|null getRow(array $where = [], string[] $order = [], int|null $offset = null)
 * @method static CourierGreetingRow|null getRowById(int|null $id)
 * @method static CourierGreetingRow|null getRowFromSql(string $sql, array $params = [])
 * @method static CourierGreetingRow|null getRowByForeignKey(string $fieldName, int $foreignKeyId)
 * @method static CourierGreetingRow requireRowById(int $id, string|null $errorMessage = null)
 * @method static CourierGreetingRow getOrCreateRowById(int $id)
 * @method static CourierGreetingRow getOrMakeUnsavedRowById(int $id)
 * @method static CourierGreetingRow makeUnsavedRow()
 * @method static CourierGreetingRow createFromArray(array $data = [])
 * @method static CourierGreetingRow[] getRowset(array $where = [], string[] $order = [], int|null $count = null, int|null $offset = null)
 * @method static CourierGreetingRow[] getRowsetByIds(int[] $ids, string[] $order = [], int|null $count = null, int|null $offset = null)
 * @method static CourierGreetingRow[] getRowsetFromSql(string $sql, array $params = [])
 * @method static CourierGreetingRow[] getRowsetByForeignKeys(string $fieldName, int[] $foreignKeyIds)
 * @method static CourierGreetingRow[] warmupGetRowByIdCache(int[] $ids)
 * @method static CourierGreetingRow[] warmupGetRowsetByForeignKeysCache(string $fieldName, int[] $foreignKeyIds)
 */
class CourierGreetingsTable extends TableAbstract {
    public static function getTableName(): string {
        return 'courier_greetings';
    }

    public static function getRowClass(): string {
        return CourierGreetingRow::class;
    }
}
```

**[library/Dostavista/Features/CourierGreetings/CourierGreetingRow.php](https://github.com/borzodelivery/backend-code-sample/blob/master/library/Dostavista/Features/CourierGreetings/CourierGreetingRow.php)**

```php
<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings;

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
}
```


Теперь в любом месте кода можно получить данные из базы.
Например, с помощью вызова метода `CourierGreetingsTable::getRowById(1)` можно получить приветствие с ID = 1.

> ☝️ **Примечание**<br>
> На самом деле в боевом проекте у нас есть консольный скрипт, создающий все эти файлы автоматически.


## CRUD в админке

Создадим простой [CRUD](https://ru.wikipedia.org/wiki/CRUD) в админке,
чтобы можно было добавлять, редактировать и удалять варианты приветствий.
Для этого нам понадобится сделать форму и контроллер.

Создадим форму:

**[library/Dostavista/Features/CourierGreetings/CourierGreetingForm.php](https://github.com/borzodelivery/backend-code-sample/blob/master/library/Dostavista/Features/CourierGreetings/CourierGreetingForm.php)**

```php
<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings;

// ...

class CourierGreetingForm extends FormAbstract {
    public function init(): void {
        parent::init();

        // Greeting text template
        $this->addText('greeting_template', [
            'label'       => 'Greeting template',
            'description' => 'Variable %name% is allowed. Example: Hello, %name%!',
            'required'    => true,
            'maxlength'   => 1024,
            'filters'     => [
                new StringTrimFilter(),
                new StripTagsFilter(),
            ],
            'validators' => [new StringLengthValidator(1, 1024)],
        ]);

        // Allowed to show interval start time
        $this->addText('allowed_to_show_start_time', [
            'label'      => 'Allowed to show interval start time',
            'value'      => '00:00:00',
            'class'      => 'js-input-time',
            'required'   => true,
            'filters'    => [new DateTimeFilter(null, 'H:i')],
            'validators' => [new TimeValidator()],
        ]);

        // Allowed to show interval finish time
        $this->addText('allowed_to_show_finish_time', [
            'label'      => 'Allowed to show interval finish time',
            'value'      => '23:59:59',
            'class'      => 'js-input-time',
            'required'   => true,
            'filters'    => [new DateTimeFilter(null, 'H:i')],
            'validators' => [new TimeValidator()],
        ]);

        $this->addSubmit('Save');
    }

    public function setCourierGreetingData(CourierGreetingRow $greeting): void {
        $values = $this->getValuesMapped();

        $greeting->greeting_template           = $values['greeting_template'];
        $greeting->allowed_to_show_start_time  = $values['allowed_to_show_start_time'];
        $greeting->allowed_to_show_finish_time = $values['allowed_to_show_finish_time'];
    }
}
```

Форма позволит создавать и редактировать приветствия. Вот так это будет выглядеть в админке:

![](.github/form.png)

Теперь создадим контроллер и разрешим к нему доступ только сотрудникам с правами `Permissions::PERM_GROUP_CONTENT_MANAGER`:

**[library/Dostavista/Features/CourierGreetings/CourierGreetingsDispatcherController.php](https://github.com/borzodelivery/backend-code-sample/blob/master/library/Dostavista/Features/CourierGreetings/CourierGreetingsDispatcherController.php)**

```php
<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings;

// ...

/**
 * Controller for the page with courier greetings in the admin panel.
 */
class CourierGreetingsDispatcherController extends DispatcherControllerAbstract {
    public static function isActionPermitted(string $action, ?EmployeeRow $user = null): bool {
        return Permissions::hasAccess(Permissions::PERM_GROUP_CONTENT_MANAGER, $user);
    }

    /**
     * List of courier greetings.
     */
    public function indexAction(): CourierGreetingsIndexView {
        $view = new CourierGreetingsIndexView();

        $view->greetings = CourierGreetingsTable::getRowset(['is_deleted = 0'], ['courier_greeting_id']);

        return $view;
    }

    /**
     * Creating a new courier greeting.
     */
    public function addAction(): ViewAbstract {
        $form = new CourierGreetingForm();

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $greeting = CourierGreetingsTable::makeUnsavedRow();
            $form->setCourierGreetingData($greeting);
            $greeting->save();

            FlashMessagesTable::addFlashMessage("New courier greeting #{$greeting->courier_greeting_id} was created");
            return RedirectView::createInternalRedirect('/dispatcher/courier-greetings');
        }

        $view = new CourierGreetingsAddView();

        $view->form = $form;

        return $view;
    }

    /**
     * Editing courier greetings.
     */
    public function editAction(): ViewAbstract {
        $greetingId = (int) $this->getRequest()->getParam('id');

        $greeting = CourierGreetingsTable::getRowById($greetingId);
        if (!$greeting) {
            FlashMessagesTable::addFlashMessage("Error! Courier greeting #{$greetingId} not found");
            return RedirectView::createInternalRedirect($this->getRequest()->getReferer(), '/dispatcher/courier-greetings');
        }

        $form = new CourierGreetingForm();
        $form->setDefaults($greeting->toArray());

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $form->setCourierGreetingData($greeting);
            $greeting->save();

            FlashMessagesTable::addFlashMessage("Courier greeting #{$greetingId} was changed");
            return RedirectView::createInternalRedirect('/dispatcher/courier-greetings');
        }

        $view = new CourierGreetingsEditView();

        $view->greeting = $greeting;
        $view->form     = $form;

        return $view;
    }

    /**
     * Deleting courier greetings.
     */
    public function deleteAction(): ViewAbstract {
        $this->requirePost();

        $greetingId = (int) $this->getRequest()->getParam('id');

        $greeting = CourierGreetingsTable::getRowById($greetingId);
        if (!$greeting) {
            FlashMessagesTable::addFlashMessage("Error! Courier greeting #{$greetingId} not found");
            return RedirectView::createInternalRedirect($this->getRequest()->getReferer(), '/dispatcher/courier-greetings');
        }

        $greeting->is_deleted = true;
        $greeting->save();

        FlashMessagesTable::addFlashMessage("Courier greeting #{$greetingId} was deleted");
        return RedirectView::createInternalRedirect($this->getRequest()->getReferer(), '/dispatcher/courier-greetings');
    }
}
```

Получим вот такую страницу в админке:

![](.github/index.png)

> ☝️ **Примечание**<br>
> На самом деле в боевом проекте у нас есть консольный скрипт, создающий все эти файлы автоматически.


## Бизнес-логика

Самое время описать бизнес-логику нашей новой фичи. Так как тут у нас логика очень простая,
то создадим метод прямо в классе `CourierGreetingsTable`. Мы часто так делаем, чтобы не создавать абстракций на вырост.
Когда логика станет сильно сложнее, только в этот момент мы выделяем её в отдельный класс `CourierGreetingManager`.

Также не забываем, что наши курьеры работают в разных часовых поясах, поэтому вычисляем местное время в регионе курьера.

**[library/Dostavista/Features/CourierGreetings/CourierGreetingsTable.php](https://github.com/borzodelivery/backend-code-sample/blob/master/library/Dostavista/Features/CourierGreetings/CourierGreetingsTable.php)**

```php
<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings;

// ...

class CourierGreetingsTable extends TableAbstract {

    // ...

    /**
     * Returns the text of a random message for the courier.
     */
    public static function getRandomGreetingMessage(CourierRow $courier): ?string {
        // Determining the local time in the courier's region
        $region    = $courier->getRegion();
        $localTime = $region->getLocalDateTime();

        // Selecting all suitable greetings
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

        // Substitute the courier name into the template
        $greeting = $greetings[array_rand($greetings)];
        return str_ireplace('%name%', $courier->user_name, $greeting->greeting_template);
    }
}
```


## API

Чтобы мобильные приложения смогли получить текст приветствия, добавим новый метод в Courier API:

**[library/Dostavista/Features/CourierApi/CourierApiController.php](https://github.com/borzodelivery/backend-code-sample/blob/master/library/Dostavista/Features/CourierApi/CourierApiController.php#L251-L263)**

```php
<?php declare(strict_types=1);

namespace Dostavista\Features\CourierApi;

// ...

class CourierApiController extends ModernApiControllerAbstract {
    /**
     * Returns a randomly selected greeting for an authorized courier.
     */
    public function randomGreetingAction(): JsonView {
        $this->requireGet();
        $courier = $this->requireAuthCourier();

        $greetingText = CourierGreetingsTable::getRandomGreetingMessage($courier);

        return $this->makeResponse([
            'greeting_text' => TypeCaster::stringOrNull($greetingText),
        ]);
    }
    
    // ...
}
```

Теперь приложения смогут обратиться к этому методу с авторизованной сессией курьера и получить случайное приветствие.

> ☝️ **Примечание**<br>
> У нас в разработке есть API Design Guidelines, которым мы следуем, чтобы создавать хорошее API.


## API Schema

Чтобы у мобильных разработчиков была документация, добавим новый метод в схему API.
Для этого создадим следующий файл:

**[library/Dostavista/Features/CourierApi/api-schema/methods/random-greeting.php](https://github.com/borzodelivery/backend-code-sample/blob/master/library/Dostavista/Features/CourierApi/api-schema/methods/random-greeting.php)**

```php
<?php declare(strict_types=1);

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Framework\ApiSchema\ApiDoc;

return [
    'title'       => 'Greeting',
    'description' => 'Returns a randomly selected greeting for the courier',

    /** @see CourierApiController::randomGreetingAction() */
    'path'          => '/random-greeting',
    'http_method'   => ApiDoc::GET,
    'auth_required' => true,

    'parameters' => [],

    'response' => [
        'properties' => [
            'greeting_text' => [
                'description' => 'Greeting text',
                'type'        => ApiDoc::STRING,
                'nullable'    => true,
                'example'     => 'Hello, Igor!',
            ],
        ],
    ],
];
```

Теперь в курьерской документации появится [новая страница](https://raw.githubusercontent.com/dostavista/backend-code-sample/master/.github/api-doc.png) с описанием метода API.


## Тесты

Чтобы из тестов можно было вызывать новый метод Courier API, добавим его в `CourierApiHelper`.

**[tests/Dostavista/TestUtils/CourierApiHelper.php](https://github.com/borzodelivery/backend-code-sample/blob/master/tests/Dostavista/TestUtils/CourierApiHelper.php#L54-L66)**

```php
<?php declare(strict_types=1);

namespace Dostavista\TestUtils\Api;

// ...

class CourierApiHelper {
    /**
     * @see CourierApiController::randomGreetingAction()
     */
    public function getRandomGreeting(?CourierRow $courier = null): ModernApiClient {
        return $this->buildGetRequest('random-greeting', $courier);
    }
}
```

Теперь напишем пару тестов на новый метод Courier API. Создадим новый класс `CourierApiRandomGreetingTest`.

**[tests/Dostavista/Tests/CourierApi/CourierApiRandomGreetingTest.php](https://github.com/borzodelivery/backend-code-sample/blob/master/tests/Dostavista/Tests/CourierApi/CourierApiRandomGreetingTest.php)**

```php
<?php declare(strict_types=1);

namespace Dostavista\Tests\CourierApi;

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Features\CourierGreetings\CourierGreetingsTable;
use Dostavista\Tests\TestCaseAbstract;

/**
 * Courier greeting tests.
 * @see CourierApiController::randomGreetingAction()
 */
class CourierApiRandomGreetingTest extends TestCaseAbstract {
    /**
     * Checks the case when there are no suitable greetings for the courier in the database.
     * @see CourierApiController::randomGreetingAction()
     */
    public function testRandomGreetingEmpty(): void {
        $courier = $this->getCourierProvider()->getApprovedCourier();

        $json = $this->getCourierApiHelper()->getRandomGreeting($courier)->getJson();
        assertNull($json['greeting_text']);
    }

    /**
     * Checks successful getting of a simple greeting for the courier.
     * @see CourierApiController::randomGreetingAction()
     */
    public function testRandomGreetingSimple(): void {
        // Add a greeting that can be shown at any time.
        $greeting = CourierGreetingsTable::makeUnsavedRow();

        $greeting->greeting_template = 'Hello, %name%!';
        $greeting->save();

        $courier = $this->getCourierProvider()->getApprovedCourier();

        $json = $this->getCourierApiHelper()->getRandomGreeting($courier)->getJson();
        assertSame('Hello, Courier!', $json['greeting_text']);
    }
}
```

> ☝️ **Примечание**<br>
> У нас в разработке есть Coding Guidelines, которым мы следуем, чтобы писать хорошие тесты.

Вот и всё.
<br>Остаётся запушить ветку, убедиться, что в Teamcity пройдут все тесты, и создать пулл-риквест.
<br>И вскоре фича попадёт на продакшн.
