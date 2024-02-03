<?php
class JFormComponentHtml extends JFormComponent
{
    public $html;

    public function __construct($html)
    {
        $this->id = uniqid();
        $this->html = $html;
    }

    public function getOptions()
    {
        return null;
    }

    public function clearValue()
    {
        return null;
    }

    public function validate()
    {
        return null;
    }

    public function getValue()
    {
        return null;
    }

    public function __toString()
    {
        return $this->html;
    }
}
