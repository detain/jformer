<?php

class JFormComponentName extends JFormComponent
{
    public $middleInitialHidden = false;
    public $emptyValues = null;
    public $showSublabels = true;

    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'jFormComponentName form-group';

        // Input options
        $this->initialValues = ['firstName' => '', 'middleInitial' => '', 'lastName' => ''];

        if ($this->emptyValues === true) {
            $this->emptyValues = ['firstName' => 'First Name', 'middleInitial' => 'M','lastName' => 'Last Name'];
        }
        //$this->mask = '';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function hasInstanceValues()
    {
        return is_array($this->value);
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        if (!empty($this->emptyValues)) {
            $options['options']['emptyValue'] = $this->emptyValues;
        }

        if (empty($options['options'])) {
            unset($options['options']);
        }

        return $options;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Generate the component div
        $div = $this->generateComponentDiv();


        $firstNameDiv = new JFormElement('div', [
            'class' => 'firstNameDiv form-group',
        ]);
        // Add the first name input tag
        $firstName = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-firstName',
            'name' => $this->name.'-firstName',
            'class' => 'firstName singleLineText form-control',
            'placeholder' => 'First Name',
            'value' => $this->initialValues['firstName'],
        ]);
        $firstNameDiv->insert($firstName);

        // Add the middle initial input tag
        $middleInitialDiv = new JFormElement('div', [
            'class' => 'middleInitialDiv form-group',
        ]);
        $middleInitial = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-middleInitial',
            'name' => $this->name.'-middleInitial',
            'class' => 'middleInitial singleLineText form-control',
            'maxlength' => '1',
            'value' => ($this->initialValues['middleInitial'] ?? ''),
        ]);
        if ($this->middleInitialHidden) {
            $middleInitial->setAttribute('style', 'display: none;');
            $middleInitialDiv->setAttribute('style', 'display: none;');
        }
        $middleInitialDiv->insert($middleInitial);


        // Add the last name input tag
        $lastNameDiv = new JFormElement('div', [
            'class' => 'lastNameDiv form-group',
        ]);
        $lastName = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-lastName',
            'name' => $this->name.'-lastName',
            'class' => 'lastName singleLineText form-control',
            'placeholder' => 'Last Name',
            'value' => $this->initialValues['lastName'],
        ]);
        $lastNameDiv->insert($lastName);

        if (!empty($this->emptyValues)) {
            $this->emptyValues = ['firstName' => 'First Name', 'middleInitial' => 'M','lastName' => 'Last Name'];
            foreach ($this->emptyValues as $key => $value) {
                if (!isset($this->initialValues[$key]) || $this->initialValues[$key] == '') {
                    if ($key == 'firstName') {
                        $firstName->setAttribute('value', $value);
                        $firstName->addClassName('defaultValue');
                    }
                    if ($key == 'middleInitial') {
                        $middleInitial->setAttribute('value', $value);
                        $middleInitial->addClassName('defaultValue');
                    }
                    if ($key == 'lastName') {
                        $lastName->setAttribute('value', $value);
                        $lastName->addClassName('defaultValue');
                    }
                }
            }
        }

        if ($this->showSublabels) {
            $firstNameDiv->insert('<div class="jFormComponentSublabel"><p>First Name</p></div>');
            $middleInitialDiv->insert('<div class="jFormComponentSublabel"><p>MI</p></div>');
            $lastNameDiv->insert('<div class="jFormComponentSublabel"><p>Last Name</p></div>');
        }

        $div->insert($firstNameDiv);
        $div->insert($middleInitialDiv);
        $div->insert($lastNameDiv);

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div);

        return $div->__toString();
    }

    public function required($options)
    {
        $errorMessageArray = [];
        if ($options['value']->firstName == '') {
            array_push($errorMessageArray, ['First name is required.']);
        }
        if ($options['value']->lastName == '') {
            array_push($errorMessageArray, ['Last name is required.']);
        }
        return sizeof($errorMessageArray) == 0 ? 'success' : $errorMessageArray;
    }
}
