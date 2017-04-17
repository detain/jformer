<?php

class BFormComponentLikert extends BFormComponent {
    var $choiceArray = array();
    var $statementArray = array();
    var $showTableHeading = true;
    var $collapseLabelIntoTableHeading = false;

    /**
     * Constructor
     */
    function __construct($id, $label, $choiceArray, $statementArray, $optionsArray) {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'bFormComponentLikert';
        $this->label = $label;

        $this->choiceArray = $choiceArray;
        $this->statementArray = $statementArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionsArray);
    }

    function getOptions() {
        $options = parent::getOptions();

        $statementArray = array();
        foreach($this->statementArray as $statement) {
            $statementArray[$statement['name']] = array();

            if(!empty($statement['validationOptions'])) {
                $statementArray[$statement['name']]['validationOptions'] = $statement['validationOptions'];
            }

            if(!empty($statement['triggerFunction'])) {
                $statementArray[$statement['name']]['triggerFunction'] = $statement['triggerFunction'];
            }
        }

        $options['options']['statementArray'] = $statementArray;

        // Make sure you have an options array to manipulate
        if(!isset($options['options'])) {
            $options['options']  = array();
        }

        return $options;
    }

    /**
     *
     * @return string
     */
    function __toString() {
        // Generate the component div
        $componentDiv = parent::generateComponentDiv(!$this->collapseLabelIntoTableHeading);

        // Create the table
        $table = new BFormElement('table', array('class' => 'bFormComponentLikertTable'));

        // Generate the first row
        if($this->showTableHeading) {
            $tableHeadingRow = new BFormElement('tr', array('class' => 'bFormComponentLikertTableHeading'));

            $tableHeading = new BFormElement('th', array(
                'class' => 'bFormComponentLikertStatementColumn',
            ));
            // Collapse the label into the heading if the option is set
            if($this->collapseLabelIntoTableHeading) {
                $tableHeadingLabel = new BFormElement('label', array(
                    'class' => 'bFormComponentLikertStatementLabel',
                ));
                $tableHeadingLabel->update($this->label);
                // Add the required star to the label
                if(in_array('required', $this->validationOptions)) {
                    $labelRequiredStarSpan = new BFormElement('span', array(
                        'class' => $this->labelRequiredStarClass
                    ));
                    $labelRequiredStarSpan->update(' *');
                    $tableHeadingLabel->insert($labelRequiredStarSpan);
                }
                $tableHeading->insert($tableHeadingLabel);
            }
            $tableHeadingRow->insert($tableHeading);

            foreach($this->choiceArray as $choice) {
                $tableHeadingRow->insert('<th>'.$choice['label'].'</th>');
            }
            $table->insert($tableHeadingRow);
        }
        
        // Insert each of the statements
        $statementCount = 0;
        foreach($this->statementArray as $statement) {
            // Set the row style
            if($statementCount % 2 == 0) {
                $statementRowClass = 'bFormComponentLikertTableRowEven';
            }
            else {
                $statementRowClass = 'bFormComponentLikertTableRowOdd';
            }

            // Set the statement
            $statementRow = new BFormElement('tr', array('class' => $statementRowClass));
            $statementColumn = new BFormElement('td', array('class' => 'bFormComponentLikertStatementColumn'));
            $statementLabel = new BFormElement('label', array(
                'class' => 'bFormComponentLikertStatementLabel',
                'for' => $statement['name'].'-choice1',
            ));
            $statementColumn->insert($statementLabel->insert($statement['statement']));

            // Set the statement description (optional)
            if(!empty($statement['description'])) {
                $statementDescription = new BFormElement('div', array(
                    'class' => 'bFormComponentLikertStatementDescription',
                ));
                $statementColumn->insert($statementDescription->update($statement['description']));
            }

            // Insert a tip (optional)
            if(!empty($statement['tip'])) {
                $statementTip = new BFormElement('div', array(
                    'class' => 'bFormComponentLikertStatementTip',
                    'style' => 'display: none;',
                ));
                $statementColumn->insert($statementTip->update($statement['tip']));
            }

            $statementRow->insert($statementColumn);

            $choiceCount = 1;
            foreach($this->choiceArray as $choice) {
                $choiceColumn = new BFormElement('td');

                $choiceInput = new BFormElement('input', array(
                    'id' => $statement['name'].'-choice'.$choiceCount,
                    'type' => 'radio',
                    'value' => $choice['value'],
                    'name' => $statement['name'],
                ));
                // Set a selected value if defined
                if(!empty($statement['selected'])) {
                    if($statement['selected'] == $choice['value']) {
                        $choiceInput->setAttribute('checked', 'checked');
                    }
                }
                $choiceColumn->insert($choiceInput);

                // Choice sub labels
                if(!empty($choice['sublabel'])) {
                    $choiceSublabel = new BFormElement('label', array(
                        'class' => 'bFormComponentLikertSublabel',
                        'for' => $statement['name'].'-choice'.$choiceCount,
                    ));
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
    public function required($options) {
        $errorMessageArray = array();
        foreach($options['value'] as $key => $statement) {
            if(empty($statement)) {
                //print_r($key);
                //print_r($statement);
                array_push($errorMessageArray, array($key => 'Required.'));
            }
        }

        return sizeof($errorMessageArray) == 0 ? 'success' : $errorMessageArray;
    }
}

class BFormComponentLikertStatement extends BFormComponent {
    /**
     * Constructor
     */
    function __construct($id, $label, $choiceArray, $statementArray, $optionsArray) {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'bFormComponentLikertStatement';
        $this->label = $label;
        // Initialize the abstract FormComponent object
        $this->initialize($optionsArray);
    }

    function  __toString() {
        return;
    }
}

?>
