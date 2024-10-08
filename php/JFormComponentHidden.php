<?php

class JFormComponentHidden extends JFormComponent
{
    /*
     * Constructor
     */
    public function __construct($id, $value, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentHidden';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);

        // Prevent the value from being overwritten
        $this->value = $value;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Generate the component div without a label
        $div = $this->generateComponentDiv(false);
        $div->addToAttribute('style', 'display: none;');

        // Input tag
        $input = new JFormElement('input', [
            'type' => 'hidden',
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
        ]);
        $div->insert($input);

        return $div->__toString();
    }
}
