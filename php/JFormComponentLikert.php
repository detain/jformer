<?php

class JFormComponentLikert extends JFormComponent
{
    public $choiceArray = [];
    public $statementArray = [];
    public $showTableHeading = true;
    public $collapseLabelIntoTableHeading = false;

    /**
     * Constructor
     */
    public function __construct($id, $label, $choiceArray, $statementArray, $optionsArray)
    {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentLikert';
        $this->label = $label;

        $this->choiceArray = $choiceArray;
        $this->statementArray = $statementArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionsArray);
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        $statementArray = [];
        foreach ($this->statementArray as $statement) {
            $statementArray[$statement['name']] = [];

            if (!empty($statement['validationOptions'])) {
                $statementArray[$statement['name']]['validationOptions'] = $statement['validationOptions'];
            }

            if (!empty($statement['triggerFunction'])) {
                $statementArray[$statement['name']]['triggerFunction'] = $statement['triggerFunction'];
            }
        }

        $options['options']['statementArray'] = $statementArray;

        // Make sure you have an options array to manipulate
        if (!isset($options['options'])) {
            $options['options']  = [];
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
        $componentDiv = parent::generateComponentDiv(!$this->collapseLabelIntoTableHeading);

        // Create the table
        $table = new JFormElement('table', ['class' => 'jFormComponentLikertTable']);

        // Generate the first row
        if ($this->showTableHeading) {
            $tableHeadingRow = new JFormElement('tr', ['class' => 'jFormComponentLikertTableHeading']);

            $tableHeading = new JFormElement('th', [
                'class' => 'jFormComponentLikertStatementColumn',
            ]);
            // Collapse the label into the heading if the option is set
            if ($this->collapseLabelIntoTableHeading) {
                $tableHeadingLabel = new JFormElement('label', [
                    'class' => 'jFormComponentLikertStatementLabel',
                ]);
                $tableHeadingLabel->update($this->label);
                // Add the required star to the label
                if (in_array('required', $this->validationOptions)) {
                    $labelRequiredStarSpan = new JFormElement('span', [
                        'class' => $this->labelRequiredStarClass
                    ]);
                    $labelRequiredStarSpan->update(' *');
                    $tableHeadingLabel->insert($labelRequiredStarSpan);
                }
                $tableHeading->insert($tableHeadingLabel);
            }
            $tableHeadingRow->insert($tableHeading);

            foreach ($this->choiceArray as $choice) {
                $tableHeadingRow->insert('<th>'.$choice['label'].'</th>');
            }
            $table->insert($tableHeadingRow);
        }

        // Insert each of the statements
        $statementCount = 0;
        foreach ($this->statementArray as $statement) {
            // Set the row style
            if ($statementCount % 2 == 0) {
                $statementRowClass = 'jFormComponentLikertTableRowEven';
            } else {
                $statementRowClass = 'jFormComponentLikertTableRowOdd';
            }

            // Set the statement
            $statementRow = new JFormElement('tr', ['class' => $statementRowClass]);
            $statementColumn = new JFormElement('td', ['class' => 'jFormComponentLikertStatementColumn']);
            $statementLabel = new JFormElement('label', [
                'class' => 'jFormComponentLikertStatementLabel',
                'for' => $statement['name'].'-choice1',
            ]);
            $statementColumn->insert($statementLabel->insert($statement['statement']));

            // Set the statement description (optional)
            if (!empty($statement['description'])) {
                $statementDescription = new JFormElement('div', [
                    'class' => 'jFormComponentLikertStatementDescription',
                ]);
                $statementColumn->insert($statementDescription->update($statement['description']));
            }

            // Insert a tip (optional)
            if (!empty($statement['tip'])) {
                $statementTip = new JFormElement('div', [
                    'class' => 'jFormComponentLikertStatementTip',
                    'style' => 'display: none;',
                ]);
                $statementColumn->insert($statementTip->update($statement['tip']));
            }

            $statementRow->insert($statementColumn);

            $choiceCount = 1;
            foreach ($this->choiceArray as $choice) {
                $choiceColumn = new JFormElement('td');

                $choiceInput = new JFormElement('input', [
                    'id' => $statement['name'].'-choice'.$choiceCount,
                    'type' => 'radio',
                    'value' => $choice['value'],
                    'name' => $statement['name'],
                ]);
                // Set a selected value if defined
                if (!empty($statement['selected'])) {
                    if ($statement['selected'] == $choice['value']) {
                        $choiceInput->setAttribute('checked', 'checked');
                    }
                }
                $choiceColumn->insert($choiceInput);

                // Choice sub labels
                if (!empty($choice['sublabel'])) {
                    $choiceSublabel = new JFormElement('label', [
                        'class' => 'jFormComponentLikertSublabel',
                        'for' => $statement['name'].'-choice'.$choiceCount,
                    ]);
                    $choiceSublabel->update($choice['sublabel']);
                    $choiceColumn->insert($choiceSublabel);
                }

                $statementRow->insert($choiceColumn);
                $choiceCount++;
            }
            $statementCount++;

            $table->insert($statementRow);
        }

        $componentDiv->insert($table);

        // Add any description (optional)
        $componentDiv = $this->insertComponentDescription($componentDiv);

        // Add a tip (optional)
        $componentDiv = $this->insertComponentTip($componentDiv, $this->id.'-div');

        return $componentDiv->__toString();
    }

    // Validation
    public function required($options)
    {
        $errorMessageArray = [];
        foreach ($options['value'] as $key => $statement) {
            if (empty($statement)) {
                //print_r($key);
                //print_r($statement);
                array_push($errorMessageArray, [$key => 'Required.']);
            }
        }

        return sizeof($errorMessageArray) == 0 ? 'success' : $errorMessageArray;
    }
}

class JFormComponentLikertStatement extends JFormComponent
{
    /**
     * Constructor
     */
    public function __construct($id, $label, $choiceArray, $statementArray, $optionsArray)
    {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentLikertStatement';
        $this->label = $label;
        // Initialize the abstract FormComponent object
        $this->initialize($optionsArray);
    }

    public function __toString()
    {
        $componentDiv = $this->generateComponentDiv();
        return $componentDiv->__toString();
        //return '';
    }
}
