<?php
/**
 * An abstract FormComponent object, cannot be instantiated
 */
abstract class JFormComponent
{
    // General settings
    public $id;
    public $class = null;
    public $value = null;
    public $style = null;
    public $parentJFormSection;
    public $anonymous = false;

    // Label
    public $label = null;  // Must be implemented by child class
    public $labelClass = 'jFormComponentLabel';
    public $labelRequiredStarClass = 'jFormComponentLabelRequiredStar';
    public $requiredText = ' *'; // can be overridden at the form level;

    // Helpers
    public $tip = null;
    public $tipClass = 'jFormComponentTip';
    public $description = null;
    public $descriptionClass = 'jFormComponentDescription';

    // Options
    public $instanceOptions = null;
    public $triggerFunction = null;
    public $enterSubmits = false;

    // Dependencies
    public $dependencyOptions = null;

    // Validation
    public $validationOptions = [];
    public $errorMessageArray = null;
    public $passedValidation = null;
    public $showErrorTipOnce = false;
    public $persistentTip = false;

    /**
     * Initialize
     */
    public function initialize($optionArray = [])
    {
        // Use the options hash to update object variables
        if (is_array($optionArray)) {
            foreach ($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Allow users to pass a string into validation options
        if (is_string($this->validationOptions)) {
            $this->validationOptions = [$this->validationOptions];
        }

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function clearValue()
    {
        $this->value = null;
    }

    public function validate()
    {
        // Clear the error message array
        $this->errorMessageArray = [];

        // Only validate if the value isn't null - this is so dependencies aren't validated before they are unlocked
        if ($this->value !== null) {
            // Perform the validation
            $this->reformValidations();

            // If you have instance values
            if ($this->hasInstanceValues()) {
                // Walk through each of the instance values
                foreach ($this->value as $instanceKey => $instanceValue) {
                    foreach ($this->validationOptions as $validationType => $validationOptions) {
                        $validationOptions['value'] = $instanceValue;

                        // Get the validation response
                        $validationResponse = $this->$validationType($validationOptions);

                        // Make sure you have an array to work with
                        if (!isset($this->errorMessageArray[$instanceKey])) {
                            $this->errorMessageArray[$instanceKey] = [];
                        }

                        if ($validationResponse != 'success') {
                            $this->passedValidation = false;

                            if (is_array($validationResponse)) {
                                $this->errorMessageArray[$instanceKey] = array_merge($this->errorMessageArray[$instanceKey], $validationResponse);
                            } else {
                                if (is_string($validationResponse)) {
                                    $this->errorMessageArray[$instanceKey] = array_merge($this->errorMessageArray[$instanceKey], [$validationResponse]);
                                } else {
                                    $this->errorMessageArray[$instanceKey] = array_merge($this->errorMessageArray[$instanceKey], ['There was a problem validating this component on the server.']);
                                }
                            }
                        }
                        // Use an empty array as a placeholder for instances that have passed validation
                        else {
                            if (sizeof($this->errorMessageArray[$instanceKey]) == 0) {
                                $this->errorMessageArray[$instanceKey] = [''];
                            }
                        }
                    }
                }
            }
            // If there are no instance values
            else {
                foreach ($this->validationOptions as $validationType => $validationOptions) {
                    $validationOptions['value'] = $this->value;

                    // Get the validation response
                    $validationResponse = $this->$validationType($validationOptions);
                    if ($validationResponse != 'success') {
                        $this->passedValidation = false;

                        if (is_array($validationResponse)) {
                            $this->errorMessageArray = array_merge($validationResponse, $this->errorMessageArray);
                        } else {
                            if (is_string($validationResponse)) {
                                $this->errorMessageArray = array_merge([$validationResponse], $this->errorMessageArray);
                            } else {
                                $this->errorMessageArray = array_merge(['There was a problem validating this component on the server.'], $this->errorMessageArray);
                            }
                        }
                    }
                }
            }

            return $this->errorMessageArray;
        }
    }

    public function reformValidations()
    {
        $reformedValidations = [];
        foreach ($this->validationOptions as $validationType => $validationOptions) {
            // Check to see if the name of the function is actually an array index
            if (is_int($validationType)) {
                // The function is not an index, it becomes the name of the option with the value of an empty object
                $reformedValidations[$validationOptions] =  [];
            }
            // If the validationOptions is a string
            elseif (!is_array($validationOptions)) {
                $reformedValidations[$validationType] = [];
                $reformedValidations[$validationType][$validationType] = $validationOptions;
            }
            // If validationOptions is an object
            elseif (is_array($validationOptions)) {
                if (isset($validationOptions[0])) {
                    $reformedValidations[$validationType] = [];
                    $reformedValidations[$validationType][$validationType] = $validationOptions;
                } else {
                    $reformedValidations[$validationType] = $validationOptions;
                }
            }
        }
        $this->validationOptions = $reformedValidations;
    }

    public function getOptions()
    {
        $options = [];
        $options['options'] = [];
        $options['type'] = get_class($this);

        // Validation options
        if (!empty($this->validationOptions)) {
            $options['options']['validationOptions'] = $this->validationOptions;
        }
        if ($this->showErrorTipOnce) {
            $options['options']['showErrorTipOnce'] = $this->showErrorTipOnce;
        }

        if ($this->persistentTip) {
            $options['options']['persistentTip'] = $this->persistentTip;
        }

        // Instances
        if (!empty($this->instanceOptions)) {
            $options['options']['instanceOptions'] = $this->instanceOptions;
            if (!isset($options['options']['instanceOptions']['addButtonText'])) {
                $options['options']['instanceOptions']['addButtonText'] = 'Add Another';
            }
            if (!isset($options['options']['instanceOptions']['removeButtonText'])) {
                $options['options']['instanceOptions']['removeButtonText'] = 'Remove';
            }
        }


        // Trigger
        if (!empty($this->triggerFunction)) {
            $options['options']['triggerFunction'] = $this->triggerFunction;
        }

        // Dependencies
        if (!empty($this->dependencyOptions)) {
            // Make sure the dependentOn key is tied to an array
            if (isset($this->dependencyOptions['dependentOn']) && !is_array($this->dependencyOptions['dependentOn'])) {
                $this->dependencyOptions['dependentOn'] = [$this->dependencyOptions['dependentOn']];
            }
            $options['options']['dependencyOptions'] = $this->dependencyOptions;
        }

        // Clear the options key if there is nothing in it
        if (empty($options['options'])) {
            unset($options['options']);
        }

        return $options;
    }

    /**
     * Generates the HTML for the FormComponent
     * @return string
     */
    abstract public function __toString();

    public function hasInstanceValues()
    {
        return is_array($this->value);
    }

    public function generateComponentDiv($includeLabel = true)
    {
        // Div tag contains everything about the component
        $componentDiv = new JFormElement('div', [
            'id' => $this->id.'-wrapper',
            'class' => 'form-group jFormComponent '.$this->class,
        ]);

        // This causes issues with things that are dependent and should display by default
        // If the component has dependencies and the display type is hidden, hide by default
        //if($this->dependencyOptions !== null && isset($this->dependencyOptions['display']) && $this->dependencyOptions['display'] == 'hide') {
        //    $componentDiv->setAttribute('style', 'display: none;');
        //}

        // Style
        if (!empty($this->style)) {
            $componentDiv->addToAttribute('style', $this->style);
        }

        // Label tag
        if ($includeLabel) {
            $label = $this->generateComponentLabel();
            $componentDiv->insert($label);
        }

        return $componentDiv;
    }

    public function updateRequiredText($requiredText)
    {
        $this->requiredText = $requiredText;
    }

    public function generateComponentLabel()
    {
        if (empty($this->label)) {
            return '';
        }

        $label = new JFormElement('label', [
            'id' => $this->id.'-label',
            'for' => $this->id,
            'class' => $this->labelClass . ' col-form-label col-sm-4'
        ]);
        $label->update($this->label);
        // Add the required star to the label
        if (in_array('required', $this->validationOptions)) {
            $labelRequiredStarSpan = new JFormElement('span', [
                'class' => $this->labelRequiredStarClass
            ]);
            $labelRequiredStarSpan->update($this->requiredText);
            $label->insert($labelRequiredStarSpan);
        }

        return $label;
    }

    public function insertComponentDescription($div)
    {
        // Description
        if (!empty($this->description)) {
            $description = new JFormElement('div', [
                'id' => $this->id.'-description',
                'class' => $this->descriptionClass
            ]);
            $description->update($this->description);

            $div->insert($description);
        }

        return $div;
    }

    public function insertComponentTip($div)
    {
        // Create the tip div if not empty
        if (!empty($this->tip)) {
            $tipDiv = new JFormElement('div', [
                'id' => $this->id.'-tip',
                'style' => 'display: none;',
                'class' => $this->tipClass,
            ]);
            $tipDiv->update($this->tip);
            $div->insert($tipDiv);
        }

        return $div;
    }

    // Generic validations

    public function required($options)
    { // Just override this if necessary
        $messageArray = ['Required.'];
        //return empty($options['value']) ? 'success' : $messageArray; // Break validation on purpose
        return !empty($options['value']) || $options['value'] == '0' ? 'success' : $messageArray;
    }
}
