<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings;

use Dostavista\Core\Access\Permissions;
use Dostavista\Core\Changelogs\ChangelogTable;
use Dostavista\Core\Changelogs\ChangelogTargetsEnum;
use Dostavista\Core\Changelogs\ChangelogTypesEnum;
use Dostavista\Core\Users\EmployeeRow;
use Dostavista\Features\CourierGreetings\Views\CourierGreetingChangelogView;
use Dostavista\Features\CourierGreetings\Views\CourierGreetingsAddView;
use Dostavista\Features\CourierGreetings\Views\CourierGreetingsEditView;
use Dostavista\Features\CourierGreetings\Views\CourierGreetingsIndexView;
use Dostavista\Features\Dispatcher\DispatcherControllerAbstract;
use Dostavista\Features\Dispatcher\Views\RedirectView;
use Dostavista\Features\FlashMessages\FlashMessagesTable;
use Dostavista\Framework\Pagination;
use Dostavista\Framework\View\ViewAbstract;

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

    /**
     * History of changes to courier greetings.
     */
    public function changelogAction(): ViewAbstract {
        $greetingId = (int) $this->getRequest()->getParam('id');
        $greeting   = $greetingId ? CourierGreetingsTable::getRowById($greetingId) : null;

        if ($greeting) {
            $where = [
                'target_type_id = ?' => ChangelogTargetsEnum::COURIER_GREETING,
                'target_id = ?'      => $greeting->courier_greeting_id,
            ];
        } else {
            // If ID is not specified, then we show changes for all records.
            $where = [
                'event_type_id IN (?)' => [
                    ChangelogTypesEnum::COURIER_GREETING_CREATED,
                    ChangelogTypesEnum::COURIER_GREETING_CHANGED,
                    ChangelogTypesEnum::COURIER_GREETING_DELETED,
                ],
            ];
        }

        $count = ChangelogTable::getCount($where);

        $pagination = new Pagination($count, static::PAGINATION_PAGE_SIZE);
        $changelog  = ChangelogTable::getRowset($where, ['id DESC'], $pagination->getSqlLimit(), $pagination->getSqlOffset());

        $view = new CourierGreetingChangelogView();

        $view->greeting   = $greeting;
        $view->changelog  = $changelog;
        $view->pagination = $pagination;

        return $view;
    }
}
