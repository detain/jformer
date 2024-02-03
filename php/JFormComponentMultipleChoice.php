<?php

class JFormComponentMultipleChoice extends JFormComponent
{
    public $multipleChoiceType = 'checkbox'; // radio, checkbox
    public $multipleChoiceClass = 'choice';
    public $multipleChoiceLabelClass = 'choiceLabel';
    public $multipleChoiceArray = [];
    public $showMultipleChoiceTipIcons = true;

    /**
     * Constructor
     */
    public function __construct($id, $label, $multipleChoiceArray, $optionArray = [])
    {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentMultipleChoice';
        $this->label = $label;
        $this->multipleChoiceArray = $multipleChoiceArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function hasInstanceValues()
    {
        if ($this->multipleChoiceType == 'radio') {
            return is_array($this->value);
        } else {
            if (!empty($this->value)) {
                return is_array($this->value[0]);
            }
        }
        return false;
    }

    /**
    * MultipleChoice Specific Instance Handling for validation
    *
    */
    public function validateComponent()
    {
        $this->passedValidation = true;
        $this->errorMessageArray = [];

        if (is_array($this->value[0])) {
            foreach ($this->value as $value) {
                $this->errorMessageArray[] = $this->validate($value);
            }
        } else {
            $this->errorMessageArray = $this->validate($this->value);
        }
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        // Make sure you have an options array to manipulate
        if (!isset($options['options'])) {
            $options['options']  = [];
        }

        // Set the multiple choice type
        $options['options']['multipleChoiceType'] = $this->multipleChoiceType;

        return $options;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Generate the component div
        if (sizeof($this->multipleChoiceArray) > 1) {
            $div = parent::generateComponentDiv();
        } else {
            $div = parent::generateComponentDiv(false);
        }

        // Case
        // array(array('value' => 'option1', 'label' => 'Option 1', 'checked' => 'checked', 'tip' => 'This is a tip'))
        $multipleChoiceCount = 0;
        foreach ($this->multipleChoiceArray as $multipleChoice) {
            $multipleChoiceValue = $multipleChoice['value'] ?? '';
            $multipleChoiceLabel =  $multipleChoice['label'] ?? '';
            $multipleChoiceChecked =  $multipleChoice['checked'] ?? false;
            $multipleChoiceTip =  $multipleChoice['tip'] ?? '';
            $multipleChoiceDisabled =  $multipleChoice['disabled'] ?? '';
            $multipleChoiceInputHidden =  $multipleChoice['inputHidden'] ?? '';

            $multipleChoiceCount++;

            $div->insert($this->getMultipleChoiceWrapper($multipleChoiceValue, $multipleChoiceLabel, $multipleChoiceChecked, $multipleChoiceTip, $multipleChoiceDisabled, $multipleChoiceInputHidden, $multipleChoiceCount));
        }

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div, $this->id.'-div');

        return $div->__toString();
    }

    //function to insert tips onto the wrappers

    public function getMultipleChoiceWrapper($multipleChoiceValue, $multipleChoiceLabel, $multipleChoiceChecked, $multipleChoiceTip, $multipleChoiceDisabled, $multipleChoiceInputHidden, $multipleChoiceCount)
    {
        // Make a wrapper div for the input and label
        $multipleChoiceWrapperDiv = new JFormElement('div', [
            'id' => $this->id.'-choice'.$multipleChoiceCount.'-wrapper',
            'class' => $this->multipleChoiceClass.'Wrapper',
        ]);

        // Input tag
        $input = new JFormElement('input', [
            'type' => $this->multipleChoiceType,
            'id' => $this->id.'-choice'.$multipleChoiceCount,
            'name' => $this->name,
            'value' => $multipleChoiceValue,
            'class' => $this->multipleChoiceClass,
            'style' => 'display: inline;',
        ]);
        if ($multipleChoiceChecked == 'checked') {
            $input->setAttribute('checked', 'checked');
        }
        if ($multipleChoiceDisabled) {
            $input->setAttribute('disabled', 'disabled');
        }
        if ($multipleChoiceInputHidden) {
            $input->setAttribute('style', 'display: none;');
        }
        $multipleChoiceWrapperDiv->insert($input);

        // Multiple choice label
        $multipleChoiceLabelElement = new JFormElement('label', [
            'for' => $this->id.'-choice'.$multipleChoiceCount,
            'class' => $this->multipleChoiceLabelClass,
            'style' => 'display: inline;',
        ]);
        // Add an image to the label if there is a tip
        if (!empty($multipleChoiceTip) && $this->showMultipleChoiceTipIcons) {
            $multipleChoiceLabelElement->update($multipleChoiceLabel.' <span class="jFormComponentMultipleChoiceTipIcon">&nbsp;</span>');
        } else {
            $multipleChoiceLabelElement->update($multipleChoiceLabel);
        }
        // Add a required star if there is only one multiple choice option and it is required
        if (sizeof($this->multipleChoiceArray) == 1) {
            // Add the required star to the label
            if (in_array('required', $this->validationOptions)) {
                $labelRequiredStarSpan = new JFormElement('span', [
                    'class' => $this->labelRequiredStarClass
                ]);
                $labelRequiredStarSpan->update(' *');
                $multipleChoiceLabelElement->insert($labelRequiredStarSpan);
            }
        }
        $multipleChoiceWrapperDiv->insert($multipleChoiceLabelElement);

        // Multiple choice tip
        if (!empty($multipleChoiceTip)) {
            $multipleChoiceTipDiv = new JFormElement('div', [
                'id' => $this->id.'-'.$multipleChoiceValue.'-tip',
                'style' => 'display: none;',
                'class' => 'jFormComponentMultipleChoiceTip'
            ]);
            $multipleChoiceTipDiv->update($multipleChoiceTip);
            $multipleChoiceWrapperDiv->insert($multipleChoiceTipDiv);
        }

        return $multipleChoiceWrapperDiv;
    }


    // Validations
    public function required($options)
    {
        $errorMessageArray = ['Required.'];
        return  sizeof($options['value']) > 0 ? 'success' : $errorMessageArray;
    }
    public function minOptions($options)
    {
        $errorMessageArray = ['You must select more than '. $options['minOptions'] .' options'];
        return sizeof($options['value']) == 0 || sizeof($options['value']) > $options['minOptions'] ? 'success' : $errorMessageArray;
    }
    public function maxOptions($options)
    {
        $errorMessageArray = ['You may select up to '. $options['maxOptions'] .' options. You have selected '. sizeof($options['value']) . '.'];
        return sizeof($options['value']) == 0 || sizeof($options['value']) <= $options['maxOptions'] ? 'success' : $errorMessageArray;
    }
}
