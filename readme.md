
# Borzo Backend Code Sample

🇷🇺 [Switch to Russian](readme-ru.md)

Let's go step by step through a simple task, how we create new features at Borzo (aka Dostavista).

The code here is just for the sake of example, it won't work, but you can read it.

**Yeah, and come work for us! It's exciting.**
<br>🔥 [Our vacancies on HeadHunter](https://hh.ru/employer/3730831).


## Task

In the courier mobile application, you need to make a daily greeting for the courier.

How it will work: the courier opens the app for the first time in a day and sees the greeting for a few seconds.

![](.github/task.png)

1. The text of the greeting is random each time.<br>
   Examples: «Hello, Igor», «Good morning, Andrew!», «Buenos dias, amigo!».
2. Some variants («Good morning», «Good evening») can only be shown at a certain time interval.<br>
   For example, «Good morning» can be shown only from 5:00 to 10:00.
3. The list of possible greeting variants and the time to display them can be customized in the admin area.
   

## Migrations

To store the possible variants of greetings, let's create a `courier_greetings` table in the database.
<br>To do this, let's add a new migration file in the [migrations](https://github.com/borzodelivery/backend-code-sample/tree/master/migrations) directory. 

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

In a separate migration, let's populate our table with data.

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

> ☝️ **Note**<br>
> Actually in the real project we don't do this manually, but use a special console script that creates migration files.


## The directory with the feature files.

We don't mix all the code into one heap, and we try to neatly put features into separate folders.

So for our new feature, let's create a directory [library/Dostavista/Features/CourierGreetings](https://github.com/borzodelivery/backend-code-sample/tree/master/library/Dostavista/Features/CourierGreetings).

All classes and files related to this feature will be created in this directory.


## Classes for working with a DB table

To work with the new table, you need to create two classes: `CourierGreetingRow` и `CourierGreetingsTable`.

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

Now you can get data from the database anywhere in the code.
For example, by calling the `CourierGreetingsTable::getRowById(1)` method you can get a greeting with ID = 1.

> ☝️ **Note**<br>
> In fact, in the real project we have a console script that creates all these files automatically.


## CRUD in the admin panel

Let's create a simple [CRUD](https://ru.wikipedia.org/wiki/CRUD) in the admin panel,
to be able to add, edit and delete greeting variants.
For this we will need to make a form and a controller.

Let's create a form:

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

The form will allow you to create and edit greetings. This is how it will look like in the admin panel:

![](.github/form.png)

Now let's create a controller and allow only employees with `Permissions::PERM_GROUP_CONTENT_MANAGER` permissions to access it:

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

We'll get a page like this in the admin panel:

![](.github/index.png)

> ☝️ **Note**<br>
> In fact, in the real project we have a console script that creates all these files automatically.


## Business logic

It's time to describe the business logic of our new feature. Since the logic here is very simple, we will create
a method directly in the `CourierGreetingsTable` class. We often do thisso that we don't have to create abstractions for growth.
When the logic becomes more complex, only at this point we separate it into a separate class `CourierGreetingManager`.

Also do not forget that our couriers work in different time zones, so we calculate the local time in the region of the courier.

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

In order for mobile apps to get the welcome text, let's add a new method to the Courier API:

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

Apps will now be able to invoke this method with an authorized courier session and receive a random greeting.

> ☝️ **Note**<br>
> We have API Design Guidelines in development that we follow to create a good API.


## API Schema

In order for mobile developers to have documentation, let's add a new method to the API schema.
To do this, let's create the following file:

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

A [new page](https://raw.githubusercontent.com/dostavista/backend-code-sample/master/.github/api-doc.png) describing the API method will now appear in the courier documentation.


## Tests

To allow the new Courier API method to be called from the tests, let's add it to `CourierApiHelper`.

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

Now let's write a couple of tests for the new Courier API method. Create a new class `CourierApiRandomGreetingTest`.

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

> ☝️ **Note**<br>
> We have Coding Guidelines in development that we follow to write good tests.

That's all.
<br>All that remains is to push the branch, make sure that Teamcity passes all the tests, and create a pull request.
<br>And soon the feature will be released in production.
