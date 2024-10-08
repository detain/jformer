<?php

class JFormComponentAddress extends JFormComponent
{
    public $selectedCountry = null;
    public $selectedState = null;
    public $stateDropDown = false;
    public $emptyValues = null;
    public $showSublabels = true;
    public $unitedStatesOnly = false;
    public $addressLine2Hidden = false;

    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'jFormComponentAddress';

        $this->initialValues = ['addressLine1' => '', 'addressLine2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => ''];

        // Set the empty values with a boolean
        if ($this->emptyValues === true) {
            $this->emptyValues = ['addressLine1' => 'Street Address', 'addressLine2' => 'Address Line 2', 'city' => 'City', 'state' => 'State / Province / Region', 'zip' => 'Postal / Zip Code'];
        }

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);

        $this->selectedState = $this->initialValues['state'];
        $this->selectedCountry = $this->initialValues['country'];

        // United States only switch
        if ($this->unitedStatesOnly) {
            $this->stateDropDown = true;
            $this->selectedCountry = 'US';
        }
    }

    public function getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled)
    {
        $option = new JFormElement('option', ['value' => $optionValue]);
        $option->update($optionLabel);

        if ($optionSelected) {
            $option->setAttribute('selected', 'selected');
        }

        if ($optionDisabled) {
            $option->setAttribute('disabled', 'disabled');
        }

        return $option;
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        if (!empty($this->emptyValues)) {
            $options['options']['emptyValue'] = $this->emptyValues;
        }

        if ($this->stateDropDown) {
            $options['options']['stateDropDown'] = true;
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
        $componentDiv = $this->generateComponentDiv();

        // Add the Address Line 1 input tag
        $addressLine1Div = new JFormElement('div', [
            'class' => 'addressLine1Div',
        ]);
        $addressLine1 = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-addressLine1',
            'name' => $this->name.'-addressLine1',
            'class' => 'addressLine1',
            'placeholder' => 'Enter street address',
            'value' => $this->initialValues['addressLine1'],
        ]);
        $addressLine1Div->insert($addressLine1);

        // Add the Address Line 2 input tag
        $addressLine2Div = new JFormElement('div', [
            'class' => 'addressLine2Div',
        ]);
        $addressLine2 = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-addressLine2',
            'name' => $this->name.'-addressLine2',
            'class' => 'addressLine2',
            'placeholder' => 'Enter address line 2',
            'value' => $this->initialValues['addressLine2'],
        ]);
        $addressLine2Div->insert($addressLine2);

        // Add the city input tag
        $cityDiv = new JFormElement('div', [
            'class' => 'cityDiv',
        ]);
        $city = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-city',
            'name' => $this->name.'-city',
            'class' => 'city',
            'maxlength' => '25',
            'placeholder' => 'Enter city',
            'value' => $this->initialValues['city'],
        ]);
        $cityDiv->insert($city);

        // Add the State input tag
        $stateDiv = new JFormElement('div', [
            'class' => 'stateDiv',
        ]);
        if ($this->stateDropDown) {
            $state = new JFormElement('select', [
                'id' => $this->id.'-state',
                'name' => $this->name.'-state',
                'class' => 'state',
                'value' => $this->initialValues['state'],
            ]);
            // Add any options that are not in an opt group to the select
            foreach (JFormComponentDropDown::getStateArray($this->selectedState) as $dropDownOption) {
                $optionValue = $dropDownOption['value'] ?? '';
                $optionLabel = $dropDownOption['label'] ?? '';
                $optionSelected = $dropDownOption['selected'] ?? false;
                $optionDisabled = $dropDownOption['disabled'] ?? false;
                $optionOptGroup = $dropDownOption['optGroup'] ?? '';

                $state->insert($this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled));
            }
        } else {
            $state = new JFormElement('input', [
                'type' => 'text',
                'id' => $this->id.'-state',
                'name' => $this->name.'-state',
                'class' => 'state',
                'placeholder' => 'Enter state/province/region',
                'value' => $this->initialValues['state'],
            ]);
        }
        $stateDiv->insert($state);

        // Add the Zip input tag
        $zipDiv = new JFormElement('div', [
            'class' => 'zipDiv',
        ]);
        $zip = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-zip',
            'name' => $this->name.'-zip',
            'class' => 'zip',
            'maxlength' => '10',
            'placeholder' => 'Enter postal/zip code',
            'value' => $this->initialValues['zip'],
        ]);
        $zipDiv->insert($zip);

        // Add the country input tag
        $countryDiv = new JFormElement('div', [
            'class' => 'countryDiv',
        ]);
        // Don't built a select list if you are United States only
        if ($this->unitedStatesOnly) {
            $country = new JFormElement('input', [
                'type' => 'hidden',
                'id' => $this->id.'-country',
                'name' => $this->name.'-country',
                'class' => 'country',
                'value' => 'US',
                'style' => 'display: none;',
            ]);
        } else {
            $country = new JFormElement('select', [
                'id' => $this->id.'-country',
                'name' => $this->name.'-country',
                'class' => 'country',
                'value' => $this->initialValues['country'],
            ]);
            // Add any options that are not in an opt group to the select
            foreach (JFormComponentDropDown::getCountryArray($this->selectedCountry) as $dropDownOption) {
                $optionValue = $dropDownOption['value'] ?? '';
                $optionLabel =  $dropDownOption['label'] ?? '';
                $optionSelected = $dropDownOption['selected'] ?? false;
                $optionDisabled = $dropDownOption['disabled'] ?? false;
                $optionOptGroup = $dropDownOption['optGroup'] ?? '';

                $country->insert($this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled));
            }
        }
        $countryDiv->insert($country);

        // Set the empty values if they are enabled
        if (!empty($this->emptyValues)) {
            foreach ($this->emptyValues as $empyValueKey => $emptyValue) {
                if (!isset($this->initialValues[$empyValueKey]) || $this->initialValues[$empyValueKey] == '') {
                    if ($empyValueKey == 'addressLine1') {
                        $addressLine1->setAttribute('value', $emptyValue);
                        $addressLine1->addClassName('defaultValue');
                    }
                    if ($empyValueKey == 'addressLine2') {
                        $addressLine2->setAttribute('value', $emptyValue);
                        $addressLine2->addClassName('defaultValue');
                    }
                    if ($empyValueKey == 'city') {
                        $city->setAttribute('value', $emptyValue);
                        $city->addClassName('defaultValue');
                    }
                    if ($empyValueKey == 'state' && !$this->stateDropDown) {
                        $state->setAttribute('value', $emptyValue);
                        $state->addClassName('defaultValue');
                    }
                    if ($empyValueKey == 'zip') {
                        $zip->setAttribute('value', $emptyValue);
                        $zip->addClassName('defaultValue');
                    }
                }
            }
        }


        // Put the sublabels in if the option allows for it
        if ($this->showSublabels) {
            $addressLine1Div->insert('<div class="jFormComponentSublabel"><p>Street Address</p></div>');
            $addressLine2Div->insert('<div class="jFormComponentSublabel"><p>Address Line 2</p></div>');
            $cityDiv->insert('<div class="jFormComponentSublabel"><p>City</p></div>');

            if ($this->unitedStatesOnly) {
                $stateDiv->insert('<div class="jFormComponentSublabel"><p>State</p></div>');
            } else {
                $stateDiv->insert('<div class="jFormComponentSublabel"><p>State / Province / Region</p></div>');
            }

            if ($this->unitedStatesOnly) {
                $zipDiv->insert('<div class="jFormComponentSublabel"><p>Zip Code</p></div>');
            } else {
                $zipDiv->insert('<div class="jFormComponentSublabel"><p>Postal / Zip Code</p></div>');
            }

            $countryDiv->insert('<div class="jFormComponentSublabel"><p>Country</p></div>');
        }

        // United States only switch
        if ($this->unitedStatesOnly) {
            $countryDiv->setAttribute('style', 'display: none;');
        }

        // Hide address line 2
        if ($this->addressLine2Hidden) {
            $addressLine2Div->setAttribute('style', 'display: none;');
        }

        // Insert the address components
        $componentDiv->insert($addressLine1Div);
        $componentDiv->insert($addressLine2Div);
        $componentDiv->insert($cityDiv);
        $componentDiv->insert($stateDiv);
        $componentDiv->insert($zipDiv);
        $componentDiv->insert($countryDiv);

        // Add any description (optional)
        $componentDiv = $this->insertComponentDescription($componentDiv);

        // Add a tip (optional)
        $componentDiv = $this->insertComponentTip($componentDiv);

        return $componentDiv->__toString();
    }

    // Address validations
    public function required($options)
    {
        $errorMessageArray = [];
        if ($options['value']->addressLine1 == '') {
            array_push($errorMessageArray, ['Street Address is required.']);
        }
        if ($options['value']->city == '') {
            array_push($errorMessageArray, ['City is required.']);
        }
        if ($options['value']->state == '') {
            array_push($errorMessageArray, ['State is required.']);
        }
        if ($options['value']->zip == '') {
            array_push($errorMessageArray, ['Zip is required.']);
        }
        if ($options['value']->country == '') {
            array_push($errorMessageArray, ['Country is required.']);
        }
        return sizeof($errorMessageArray) < 1 ? 'success' : $errorMessageArray;
    }
}
