<?php

/**
 * A FormPage object contains FormSection objects and belongs to a Form object
 */
class BFormPage {
    
    // General settings
    var $id;
    var $class = 'bFormPage';
    var $style = '';
    var $bFormer;
    var $bFormSectionArray = array();
    var $onBeforeScrollTo; // array('function', 'notificationHtml')
    var $data;
    var $anonymous = false;

    // Title, description, submit instructions
    var $title = '';
    var $titleClass = 'bFormPageTitle';
    var $description = '';
    var $descriptionClass = 'bFormPageDescription';
    var $submitInstructions = '';
    var $submitInstructionsClass = 'bFormPageSubmitInstructions';

    // Validation
    var $errorMessageArray = array();

    // Options
    var $dependencyOptions = null;

    /*
     * Constructor
     */
    function __construct($id, $optionArray = array(), $bFormSectionArray = array()) {
        // Set the id
        $this->id = $id;

        // Use the options hash to update object variables
        if(is_array($optionArray)) {
            foreach($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Add the sections from the constructor
        foreach($bFormSectionArray as $bFormSection) {
            $this->addBFormSection($bFormSection);
        }

        return $this;
    }

    function addBFormSection($bFormSection) {
        $bFormSection->parentBFormPage = $this;
        $this->bFormSectionArray[$bFormSection->id] = $bFormSection;
        return $this;
    }

    function addBFormSections($bFormSections) {
        if (is_array($bFormSections)) {
            foreach ($bFormSections as $bFormSection) {
                $bFormSection->parentBFormPage = $this;
                $this->bFormSectionArray[$bFormSection->id] = $bFormSection;
            }
        }
        $bFormSection->parentBFormPage = $this;
        $this->bFormSectionArray[$bFormSection->id] = $bFormSection;
        return $this;
    }
    
    // Convenience method, no need to create a section to get components on the page
    function addBFormComponent($bFormComponent) {
        // Create an anonymous section if necessary
        if(empty($this->bFormSectionArray)) {
            $this->addBFormSection(new BFormSection($this->id.'_section1', array('anonymous' => true)));
        }

        // Get the last section in the page
        $lastBFormSection = end($this->bFormSectionArray);

        // If the last section exists and is anonymous, add the component to it
        if(!empty($lastBFormSection) && $lastBFormSection->anonymous) {
            $lastBFormSection->addBFormComponent($bFormComponent);
        }
        // If the last section in the page does not exist or is not anonymous, add a new anonymous section and add the component to it
        else {
            // Create an anonymous section
            $anonymousSection = new BFormSection($this->id.'_section'.(sizeof($this->bFormSectionArray) + 1), array('anonymous' => true));

            // Add the anonymous section to the page
            $this->addBFormSection($anonymousSection->addBFormComponent($bFormComponent));
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
        foreach($this->bFormSectionArray as $bFormSectionKey => $bFormSection) {
            $this->data[$bFormSectionKey] = $bFormSection->getData();
        }
        return $this->data;
    }

    function setData($bFormPageData) {
        foreach($bFormPageData as $bFormSectionKey => $bFormSectionData) {
            $this->bFormSectionArray[$bFormSectionKey]->setData($bFormSectionData);
        }
    }

    function clearData() {
        foreach($this->bFormSectionArray as $bFormSection) {
            $bFormSection->clearData();
        }
        $this->data = null;
    }

    function validate() {
        // Clear the error message array
        $this->errorMessageArray = array();

        // Validate each section
        foreach($this->bFormSectionArray as $bFormSection) {
            $this->errorMessageArray[$bFormSection->id] = $bFormSection->validate();
        }

        return $this->errorMessageArray;
    }

    function getOptions() {
        $options = array();
        $options['options'] = array();
        $options['bFormSections'] = array();

        foreach($this->bFormSectionArray as $bFormSection) {
            $options['bFormSections'][$bFormSection->id] = $bFormSection->getOptions();
        }

        if(!empty($this->onScrollTo)) {
            $options['options']['onScrollTo'] = $this->onScrollTo;
        }
        
        // Dependencies
        if(!empty($this->dependencyOptions)) {
            // Make sure the dependentOn key is tied to an array
            if(isset($this->dependencyOptions['dependentOn']) && !is_array($this->dependencyOptions['dependentOn'])) {
                $this->dependencyOptions['dependentOn'] = array($this->dependencyOptions['dependentOn']);
            }
            $options['options']['dependencyOptions'] = $this->dependencyOptions;
        }

        if(empty($options['options'])) {
            unset($options['options']);
        }

        return $options;
    }

    function updateRequiredText($requiredText) {
        foreach($this->bFormSectionArray as $bFormSection) {
            $bFormSection->updateRequiredText($requiredText);
        }
    }

    /**
     *
     * @return string
     */
    function __toString() {
        // Page div
        $bFormPageDiv = new BFormElement('div', array(
            'id' => $this->id,
            'class' => $this->class
        ));

        // Set the styile
        if(!empty($this->style)) {
            $bFormPageDiv->addToAttribute('style', $this->style);
        }

        // Add a title to the page
        if(!empty($this->title)) {
            $title = new BFormElement('div', array(
                'class' => $this->titleClass
            ));
            $title->update($this->title);
            $bFormPageDiv->insert($title);
        }

        // Add a description to the page
        if(!empty($this->description)) {
            $description = new BFormElement('div', array(
                'class' => $this->descriptionClass
            ));
            $description->update($this->description);
            $bFormPageDiv->insert($description);
        }

        // Add the form sections to the page
        foreach($this->bFormSectionArray as $bFormSection) {
            $bFormPageDiv->insert($bFormSection);
        }

        // Submit instructions
        if(!empty($this->submitInstructions)) {
            $submitInstruction = new BFormElement('div', array(
                'class' => $this->submitInstructionsClass
            ));
            $submitInstruction->update($this->submitInstructions);
            $bFormPageDiv->insert($submitInstruction);
        }

        return $bFormPageDiv->__toString();
    }
}
?>