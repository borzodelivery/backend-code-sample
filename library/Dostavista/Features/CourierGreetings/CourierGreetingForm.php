<?php

namespace Dostavista\Features\CourierGreetings;

use Dostavista\Framework\Form\Filter\DateTimeFilter;
use Dostavista\Framework\Form\Filter\StringTrimFilter;
use Dostavista\Framework\Form\Filter\StripTagsFilter;
use Dostavista\Framework\Form\FormAbstract;
use Dostavista\Framework\Form\Validator\StringLengthValidator;
use Dostavista\Framework\Form\Validator\TimeValidator;

class CourierGreetingForm extends FormAbstract {
    public function init(): void {
        parent::init();

        // Шаблон с текстом приветствия
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

        // Допустимое время начала показа приветствия
        $this->addText('allowed_to_show_start_time', [
            'label'      => 'Allowed to show interval start time',
            'value'      => '00:00:00',
            'class'      => 'js-input-time',
            'required'   => true,
            'filters'    => [new DateTimeFilter(null, 'H:i')],
            'validators' => [new TimeValidator()],
        ]);

        // Допустимое время окончания показа приветствия
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
