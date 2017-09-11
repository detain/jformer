<?php

/**
 * A FormSection object contains FormComponent objects and belongs to a FormPage object
 */
class BFormSection {

    // General settings
    var $id;
    var $class = 'bFormSection';
    var $style = '';
    var $parentBFormPage;
    var $bFormComponentArray = array();
    var $data;
    var $anonymous = false;

    // Title, description, submit instructions
    var $title = '';
    var $titleClass = 'bFormSectionTitle';
    var $description = '';
    var $descriptionClass = 'bFormSectionDescription';

    // Options
    var $instanceOptions = null;
    var $dependencyOptions = null;

    // Validation
    var $errorMessageArray = array();

    /*
     * Constructor
     */
    function __construct($id, $optionArray = array(), $bFormComponentArray = array()) {
        // Set the id
        $this->id = $id;
     
        // Use the options hash to update object variables
        if(is_array($optionArray)) {
            foreach($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Add the components from the constructor
        $this->addBFormComponentArray($bFormComponentArray);

        return $this;
    }

    function addBFormComponent($bFormComponent) {
        $bFormComponent->parentBFormSection = $this;
        $this->bFormComponentArray[$bFormComponent->id] = $bFormComponent;

        return $this;
    }

    function addBFormComponents($bFormComponents) {
        if (is_array($bFormComponents)) {
            foreach ($bFormComponentArray as $bFormComponent) {
                $bFormComponent->parentBFormSection = $this;
                $this->addBFormComponent($bFormComponent);
            }
        } else {
            $bFormComponent->parentBFormSection = $this;
            $this->bFormComponentArray[$bFormComponent->id] = $bFormComponent;
        }
        return $this;
    }

    function addBFormComponentArray($bFormComponentArray) {
        foreach($bFormComponentArray as $bFormComponent) {
            $this->addBFormComponent($bFormComponent);
        }
        return $this;
    }

    function getData() {
        $this->data = array();

        // Check to see if bFormComponent array contains instances
        if(array_key_exists(0, $this->bFormComponentArray) && is_array($this->bFormComponentArray[0])) {
            foreach($this->bFormComponentArray as $bFormComponentArrayInstanceIndex => $bFormComponentArrayInstance) {
                foreach($bFormComponentArrayInstance as $bFormComponentKey => $bFormComponent) {
                    if(get_class($bFormComponent) != 'BFormComponentHtml') { // Don't include HTML components
                        $this->data[$bFormComponentArrayInstanceIndex][$bFormComponentKey] = $bFormComponent->getValue();
                    }
                }
            }
        }
        // If the section does not have instances
        else {
            foreach($this->bFormComponentArray as $bFormComponentKey => $bFormComponent) {
                if(get_class($bFormComponent) != 'BFormComponentHtml') { // Don't include HTML components
                    $this->data[$bFormComponentKey] = $bFormComponent->getValue();
                }
            }
        }

        return $this->data;
    }

    function setData($bFormSectionData) {
        // Handle multiple instances
        if(is_array($bFormSectionData)) {
            $newBFormComponentArray = array();
            
            // Go through each section instance
            foreach($bFormSectionData as $bFormSectionIndex => $bFormSection) {
                // Create a clone of the bFormComponentArray
                $newBFormComponentArray[$bFormSectionIndex] = unserialize(serialize($this->bFormComponentArray));

                // Go through each component in the instanced section
                foreach($bFormSection as $bFormComponentKey => $bFormComponentValue) {
                    // Set the value of the clone
                    $newBFormComponentArray[$bFormSectionIndex][$bFormComponentKey]->setValue($bFormComponentValue);
                }
            }
            $this->bFormComponentArray = $newBFormComponentArray;
        }
        // Single instance
        else {
            // Go through each component
            foreach($bFormSectionData as $bFormComponentKey => $bFormComponentValue) {
                if (!is_null($this->bFormComponentArray[$bFormComponentKey])) {
                    $this->bFormComponentArray[$bFormComponentKey]->setValue($bFormComponentValue);
                }
            }
        }
    }

    function clearData() {
        // Check to see if bFormComponent array contains instances
        if(array_key_exists(0, $this->bFormComponentArray) && is_array($this->bFormComponentArray[0])) {
            foreach($this->bFormComponentArray as $bFormComponentArrayInstanceIndex => $bFormComponentArrayInstance) {
                foreach($bFormComponentArrayInstance as $bFormComponentKey => $bFormComponent) {
                    $bFormComponent->clearValue();
                }
            }
        }
        // If the section does not have instances
        else {
            foreach($this->bFormComponentArray as $bFormComponent) {
                $bFormComponent->clearValue();
            }
        }
        $this->data = null;
    }

    function validate() {
        // Clear the error message array
        $this->errorMessageArray = array();

        // If we have instances, return an array
        if(array_key_exists(0, $this->bFormComponentArray) && is_array($this->bFormComponentArray[0])) {
            foreach($this->bFormComponentArray as $bFormComponentArrayInstanceIndex => $bFormComponentArrayInstance) {
                foreach($bFormComponentArrayInstance as $bFormComponentKey => $bFormComponent) {
                    $this->errorMessageArray[$bFormComponentArrayInstanceIndex][$bFormComponent->id] = $bFormComponent->validate();
                }
            }
        }
        // If the section does not have instances, return an single dimension array
        else {
            foreach($this->bFormComponentArray as $bFormComponent) {
                $this->errorMessageArray[$bFormComponent->id] = $bFormComponent->validate();
            }
        }

        return $this->errorMessageArray;
    }

    function updateRequiredText($requiredText) {
        foreach($this->bFormComponentArray as $bFormComponent) {
            $bFormComponent->updateRequiredText($requiredText);
        }
    }

    function getOptions() {
        $options = array();
        $options['options'] = array();
        $options['bFormComponents'] = array();
        
        // Instances
        if(!empty($this->instanceOptions)) {
            $options['options']['instanceOptions'] = $this->instanceOptions;
            if(!isset($options['options']['instanceOptions']['addButtonText'])) {
                $options['options']['instanceOptions']['addButtonText'] = 'Add Another';
            }
            if(!isset($options['options']['instanceOptions']['removeButtonText'])) {
                $options['options']['instanceOptions']['removeButtonText'] = 'Remove';
            }
        }

        // Dependencies
        if(!empty($this->dependencyOptions)) {
            // Make sure the dependentOn key is tied to an array
            if(isset($this->dependencyOptions['dependentOn']) && !is_array($this->dependencyOptions['dependentOn'])) {
                $this->dependencyOptions['dependentOn'] = array($this->dependencyOptions['dependentOn']);
            }
            $options['options']['dependencyOptions'] = $this->dependencyOptions;
        }

        // Get options for each of the bFormComponents
        foreach($this->bFormComponentArray as $bFormComponent) {
            // Don't get options for BFormComponentHtml objects
            if(get_class($bFormComponent) != 'BFormComponentHtml') {
                $options['bFormComponents'][$bFormComponent->id] = $bFormComponent->getOptions();
            }
        }

        if(empty($options['options'])) {
            unset($options['options']);
        }

        return $options;
    }

    /**
     *
     * @return string
     */
    function __toString() {
        // Section fieldset
        $bFormSectionDiv = new BFormElement('div', array(
            'id' => $this->id,
            'class' => $this->class
        ));

        // This causes issues with things that are dependent and should display by default
        // If the section has dependencies and the display type is hidden, hide by default
        //if($this->dependencyOptions !== null && isset($this->dependencyOptions['display']) && $this->dependencyOptions['display'] == 'hide') {
        //    $bFormSectionDiv->setAttribute('style', 'display: none;');
        //}

        // Set the style
        if(!empty($this->style)) {
            $bFormSectionDiv->addToAttribute('style', $this->style);
        }

        // Add a title to the page
        if(!empty($this->title)) {
            $title = new BFormElement('div', array(
                'class' => $this->titleClass
            ));
            $title->update($this->title);
            $bFormSectionDiv->insert($title);
        }

        // Add a description to the page
        if(!empty($this->description)) {
            $description = new BFormElement('div', array(
                'class' => $this->descriptionClass
            ));
            $description->update($this->description);
            $bFormSectionDiv->insert($description);
        }

        // Add the form sections to the page
        foreach($this->bFormComponentArray as $bFormComponentArray) {
            $bFormSectionDiv->insert($bFormComponentArray);
        }
        
        return $bFormSectionDiv->__toString();
    }
}
?>
