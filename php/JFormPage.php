<?php

/**
 * A FormPage object contains FormSection objects and belongs to a Form object
 */
class JFormPage
{

    // General settings
    public $id;
    public $class = 'jFormPage';
    public $style = '';
    public $jFormer;
    public $jFormSectionArray = [];
    public $onBeforeScrollTo; // array('function', 'notificationHtml')
    public $data;
    public $anonymous = false;

    // Title, description, submit instructions
    public $title = '';
    public $titleClass = 'jFormPageTitle';
    public $description = '';
    public $descriptionClass = 'jFormPageDescription';
    public $submitInstructions = '';
    public $submitInstructionsClass = 'jFormPageSubmitInstructions';

    // Validation
    public $errorMessageArray = [];

    // Options
    public $dependencyOptions = null;

    /*
     * Constructor
     */
    public function __construct($id, $optionArray = [], $jFormSectionArray = [])
    {
        // Set the id
        $this->id = $id;

        // Use the options hash to update object variables
        if (is_array($optionArray)) {
            foreach ($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Add the sections from the constructor
        foreach ($jFormSectionArray as $jFormSection) {
            $this->addJFormSection($jFormSection);
        }

        return $this;
    }

    public function addJFormSection($jFormSection)
    {
        $jFormSection->parentJFormPage = $this;
        $this->jFormSectionArray[$jFormSection->id] = $jFormSection;
        return $this;
    }

    public function addJFormSections($jFormSections)
    {
        if (is_array($jFormSections)) {
            foreach ($jFormSections as $jFormSection) {
                $jFormSection->parentJFormPage = $this;
                $this->jFormSectionArray[$jFormSection->id] = $jFormSection;
            }
        }
        $jFormSection->parentJFormPage = $this;
        $this->jFormSectionArray[$jFormSection->id] = $jFormSection;
        return $this;
    }

    // Convenience method, no need to create a section to get components on the page
    public function addJFormComponent($jFormComponent)
    {
        // Create an anonymous section if necessary
        if (empty($this->jFormSectionArray)) {
            $this->addJFormSection(new JFormSection($this->id.'_section1', ['anonymous' => true]));
        }

        // Get the last section in the page
        $lastJFormSection = end($this->jFormSectionArray);

        // If the last section exists and is anonymous, add the component to it
        if (!empty($lastJFormSection) && $lastJFormSection->anonymous) {
            $lastJFormSection->addJFormComponent($jFormComponent);
        }
        // If the last section in the page does not exist or is not anonymous, add a new anonymous section and add the component to it
        else {
            // Create an anonymous section
            $anonymousSection = new JFormSection($this->id.'_section'.(sizeof($this->jFormSectionArray) + 1), ['anonymous' => true]);

            // Add the anonymous section to the page
            $this->addJFormSection($anonymousSection->addJFormComponent($jFormComponent));
        }

        return $this;
    }
    public function addJFormComponentArray($jFormComponentArray)
    {
        foreach ($jFormComponentArray as $jFormComponent) {
            $this->addJFormComponent($jFormComponent);
        }
        return $this;
    }

    public function getData()
    {
        $this->data = [];
        foreach ($this->jFormSectionArray as $jFormSectionKey => $jFormSection) {
            $this->data[$jFormSectionKey] = $jFormSection->getData();
        }
        return $this->data;
    }

    public function setData($jFormPageData)
    {
        foreach ($jFormPageData as $jFormSectionKey => $jFormSectionData) {
            $this->jFormSectionArray[$jFormSectionKey]->setData($jFormSectionData);
        }
    }

    public function clearData()
    {
        foreach ($this->jFormSectionArray as $jFormSection) {
            $jFormSection->clearData();
        }
        $this->data = null;
    }

    public function validate()
    {
        // Clear the error message array
        $this->errorMessageArray = [];

        // Validate each section
        foreach ($this->jFormSectionArray as $jFormSection) {
            $this->errorMessageArray[$jFormSection->id] = $jFormSection->validate();
        }

        return $this->errorMessageArray;
    }

    public function getOptions()
    {
        $options = [];
        $options['options'] = [];
        $options['jFormSections'] = [];

        foreach ($this->jFormSectionArray as $jFormSection) {
            $options['jFormSections'][$jFormSection->id] = $jFormSection->getOptions();
        }

        if (!empty($this->onScrollTo)) {
            $options['options']['onScrollTo'] = $this->onScrollTo;
        }

        // Dependencies
        if (!empty($this->dependencyOptions)) {
            // Make sure the dependentOn key is tied to an array
            if (isset($this->dependencyOptions['dependentOn']) && !is_array($this->dependencyOptions['dependentOn'])) {
                $this->dependencyOptions['dependentOn'] = [$this->dependencyOptions['dependentOn']];
            }
            $options['options']['dependencyOptions'] = $this->dependencyOptions;
        }

        if (empty($options['options'])) {
            unset($options['options']);
        }

        return $options;
    }

    public function updateRequiredText($requiredText)
    {
        foreach ($this->jFormSectionArray as $jFormSection) {
            $jFormSection->updateRequiredText($requiredText);
        }
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Page div
        $jFormPageDiv = new JFormElement('div', [
            'id' => $this->id,
            'class' => $this->class
        ]);

        // Set the styile
        if (!empty($this->style)) {
            $jFormPageDiv->addToAttribute('style', $this->style);
        }

        // Add a title to the page
        if (!empty($this->title)) {
            $title = new JFormElement('div', [
                'class' => $this->titleClass
            ]);
            $title->update($this->title);
            $jFormPageDiv->insert($title);
        }

        // Add a description to the page
        if (!empty($this->description)) {
            $description = new JFormElement('div', [
                'class' => $this->descriptionClass
            ]);
            $description->update($this->description);
            $jFormPageDiv->insert($description);
        }

        // Add the form sections to the page
        foreach ($this->jFormSectionArray as $jFormSection) {
            $jFormPageDiv->insert($jFormSection);
        }

        // Submit instructions
        if (!empty($this->submitInstructions)) {
            $submitInstruction = new JFormElement('div', [
                'class' => $this->submitInstructionsClass
            ]);
            $submitInstruction->update($this->submitInstructions);
            $jFormPageDiv->insert($submitInstruction);
        }

        return $jFormPageDiv->__toString();
    }
}
