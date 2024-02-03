<?php

class JFormElement
{
    private $type;
    private $unaryTagArray = ['input', 'img', 'hr', 'br', 'meta', 'link'];
    private $attributeArray;
    private $innerHtml;

    /**
     * Constructor
     *
     * @param <type> $type
     * @param <type> $attributeArray
     * @param <type> $unaryTagArray
     */
    public function __construct($type, $attributeArray = [])
    {
        $this->type = strtolower($type);

        foreach ($attributeArray as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }

        return $this;
    }

    /**
     * Set an array, can pass an array or a key, value combination
     *
     * @param <type> $attribute
     * @param <type> $value
     */

    public function getAttribute($attribute)
    {
        return $this->attributeArray[$attribute];
    }


    public function setAttribute($attribute, $value = '')
    {
        if (!is_array($attribute)) {
            $this->attributeArray[$attribute] = $value;
        } else {
            $this->attributeArray = array_merge($this->attributeArray, $attribute);
        }

        return $this;
    }

    public function addToAttribute($attribute, $value = '')
    {
        if (isset($this->attributeArray[$attribute])) {
            $currentValue = $this->attributeArray[$attribute];
        } else {
            $currentValue = '';
        }
        $this->attributeArray[$attribute] = $currentValue.$value;

        return $this;
    }

    public function addClassName($className)
    {
        $currentClasses = $this->getAttribute('class');

        // Check to see if the class is already added
        if (!strstr($currentClasses, $className)) {
            $newClasses = $currentClasses.' '.$className;
            $this->setAttribute('class', $newClasses);
        }
    }

    /**
     * Insert an element into the current element
     *
     * @param <type> $object
     */
    public function insert($object)
    {
        if (@get_class($object) == __class__) {
            $this->innerHtml .= $object->build();
        } else {
            $this->innerHtml .= $object;
        }

        return $this;
    }

    /**
     * Set the innerHtml of an element
     *
     * @param <type> $object
     * @return <type>
     */
    public function update($object)
    {
        $this->innerHtml = $object;

        return $this;
    }

    /**
     * Builds the element
     *
     * @return <type>
     */
    public function build()
    {
        // Start the tag
        $element = '<'.$this->type;

        // Add attributes
        if (count($this->attributeArray)) {
            foreach ($this->attributeArray as $key => $value) {
                $element .= ' '.$key.'="'.$value.'"';
            }
        }

        // Close the element
        if (!in_array($this->type, $this->unaryTagArray)) {
            $element.= '>'.$this->innerHtml.'</'.$this->type.'>';
        } else {
            $element.= ' >';
        }

        // Don't format the XML string, saves time
        //return $this->formatXmlString($element);
        return $element;
    }

    /**
     * Echoes out the element
     *
     * @return <type>
     */
    public function __toString()
    {
        return $this->build();
    }
}
