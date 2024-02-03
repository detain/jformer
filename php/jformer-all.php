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




require_once('JFormElement.php');
require_once('JFormPage.php');
require_once('JFormSection.php');
require_once('JFormComponent.php');
require_once('JFormComponentSingleLineText.php');
require_once('JFormComponentMultipleChoice.php');
require_once('JFormComponentDropDown.php');
require_once('JFormComponentTextArea.php');
require_once('JFormComponentDate.php');
require_once('JFormComponentFile.php');
require_once('JFormComponentName.php');
require_once('JFormComponentHidden.php');
require_once('JFormComponentAddress.php');
require_once('JFormComponentCreditCard.php');
require_once('JFormComponentLikert.php');
require_once('JFormComponentHtml.php');

if (!function_exists('is_empty')) {
    function is_empty($string)
    {
        $string = trim($string);
        if (!is_numeric($string)) {
            return empty($string);
        }
        return false;
    }
}


class JFormer
{

    // General settings
    public $id;
    public $class = 'jFormer container-fluid';
    public $action;
    public $form_type = 'horizontal';
    public $style;
    public $jFormPageArray = [];
    public $jFormerId;
    public $onSubmitFunctionServerSide = 'onSubmit';
    public $disableAnalytics = false;
    public $setupPageScroller = true;
    public $data;
    // Title, description, and submission button
    public $title = '';
    public $titleClass = 'jFormerTitle';
    public $description = '';
    public $descriptionClass = 'jFormerDescription';
    public $submitButtonText = 'Submit';
    public $submitProcessingButtonText = 'Processing...';
    public $afterControl = '';
    public $cancelButton = false;
    public $cancelButtonOnClick = '';
    public $cancelButtonText = 'Cancel';
    public $cancelButtonClass = 'cancelButton';
    // Form options
    public $alertsEnabled = true;
    public $clientSideValidation = true;
    public $debugMode = false;
    public $validationTips = true;
    public $useIframeTarget = true; // use hidden iframe for form processing, normal form post if false
    // Page navigator
    public $pageNavigatorEnabled = false;
    public $pageNavigator = [];
    // Splash page
    public $splashPageEnabled = false;
    public $splashPage = [];
    // Animations
    public $animationOptions = null;
    // Custom script execution before form submission
    public $onSubmitStartClientSide = '';
    public $onSubmitFinishClientSide = '';
    // Essential class variables
    public $status = ['status' => 'processing', 'response' => 'Form initialized.'];
    // Validation
    public $validationResponse = [];
    public $validationPassed = null;
    // Required Text
    public $requiredText = ' *';

    /**
     * Constructor
     */
    public function __construct($id, $optionArray = [], $jFormPageArray = [])
    {
        // Set the id
        $this->id = $id;

        // Set the action dynamically
        $callingFile = debug_backtrace();
        $callingFile = str_replace("\\", "/", $callingFile[0]['file']);
        $this->action = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($callingFile));

        // Use the options array to update the form variables
        if (is_array($optionArray)) {
            foreach ($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Set defaults for the page navigator
        if (!empty($this->pageNavigator)) {
            $this->pageNavigatorEnabled = true;
        } elseif ($this->pageNavigator == true) {
            $this->pageNavigator = [
                'position' => 'top'
            ];
        }

        // Set defaults for the splash page
        if (!empty($this->splashPage)) {
            $this->splashPageEnabled = true;
        }

        // Add the pages from the constructor
        foreach ($jFormPageArray as $jFormPage) {
            $this->addJFormPage($jFormPage);
        }

        return $this;
    }

    public function addJFormPage($jFormPage)
    {
        $jFormPage->jFormer = $this;
        $this->jFormPageArray[$jFormPage->id] = $jFormPage;
        return $this;
    }

    public function addJFormPages($jFormPages)
    {
        if (is_array($jFormPages)) {
            foreach ($jFormPages as $jFormPage) {
                $jFormPage->jFormer = $this;
                $this->jFormPageArray[$jFormPage->id] = $jFormPage;
            }
        }
        $jFormPage->jFormer = $this;
        $this->jFormPageArray[$jFormPage->id] = $jFormPage;
        return $this;
    }

    // Convenience method, no need to create a page or section to get components on the form
    public function addJFormComponent($jFormComponent)
    {
        // Create an anonymous page if necessary
        if (empty($this->jFormPageArray)) {
            $this->addJFormPage(new JFormPage($this->id . '_page1', ['anonymous' => true]));
        }

        // Get the first page in the jFormPageArray
        $currentJFormPage = current($this->jFormPageArray);

        // Get the last section in the page
        $lastJFormSection = end($currentJFormPage->jFormSectionArray);

        // If the last section exists and is anonymous, add the component to it
        if (!empty($lastJFormSection) && $lastJFormSection->anonymous) {
            $lastJFormSection->addJFormComponent($jFormComponent);
        }
        // If the last section in the page does not exist or is not anonymous, add a new anonymous section and add the component to it
        else {
            // Create an anonymous section
            $anonymousSection = new JFormSection($currentJFormPage->id . '_section' . (sizeof($currentJFormPage->jFormSectionArray) + 1), ['anonymous' => true]);

            // Add the anonymous section to the page
            $currentJFormPage->addJFormSection($anonymousSection->addJFormComponent($jFormComponent));
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

    // Convenience method, no need to create a to get a section on the form
    public function addJFormSection($jFormSection)
    {
        // Create an anonymous page if necessary
        if (empty($this->jFormPageArray)) {
            $this->addJFormPage(new JFormPage($this->id . '_page1', ['anonymous' => true]));
        }

        // Get the first page in the jFormPageArray
        $currentJFormPage = current($this->jFormPageArray);

        // Add the section to the first page
        $currentJFormPage->addJFormSection($jFormSection);

        return $this;
    }

    public function setStatus($status, $response)
    {
        $this->status = ['status' => $status, 'response' => $response];
        return $this->status;
    }

    public function resetStatus()
    {
        $this->status = ['status' => 'processing', 'response' => 'Form status reset.'];
        return $this->status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function validate()
    {
        // Update the form status
        $this->setStatus('processing', 'Validating component values.');

        // Clear the validation response
        $this->validationResponse = [];

        // Validate each page
        foreach ($this->jFormPageArray as $jFormPage) {
            $this->validationResponse[$jFormPage->id] = $jFormPage->validate();
        }
        // Walk through all of the pages to see if there are any errors
        $this->validationPassed = true;

        foreach ($this->validationResponse as $jFormPageKey => $jFormPage) {
            foreach ($jFormPage as $jFormSectionKey => $jFormSection) {
                // If there are section instances
                if ($jFormSection != null && array_key_exists(0, $jFormSection) && is_array($jFormSection[0])) {
                    foreach ($jFormSection as $jFormSectionInstanceIndex => $jFormSectionInstance) {
                        foreach ($jFormSectionInstance as $jFormComponentKey => $jFormComponentErrorMessageArray) {
                            // If there are component instances
                            if ($jFormComponentErrorMessageArray != null && array_key_exists(0, $jFormComponentErrorMessageArray) && is_array($jFormComponentErrorMessageArray[0])) {
                                foreach ($jFormComponentErrorMessageArray as $jFormComponentInstanceErrorMessageArray) {
                                    // If the first value is not empty, the component did not pass validation
                                    if (!empty($jFormComponentInstanceErrorMessageArray[0]) || sizeof($jFormComponentInstanceErrorMessageArray) > 1) {
                                        $this->validationPassed = false;
                                    }
                                }
                            } else {
                                if (!empty($jFormComponentErrorMessageArray)) {
                                    $this->validationPassed = false;
                                }
                            }
                        }
                    }
                }
                // No section instances
                else {
                    foreach ($jFormSection as $jFormComponentErrorMessageArray) {
                        // Component instances
                        if ($jFormComponentErrorMessageArray != null && array_key_exists(0, $jFormComponentErrorMessageArray) && is_array($jFormComponentErrorMessageArray[0])) {
                            foreach ($jFormComponentErrorMessageArray as $jFormComponentInstanceErrorMessageArray) {
                                // If the first value is not empty, the component did not pass validation
                                if (!empty($jFormComponentInstanceErrorMessageArray[0]) || sizeof($jFormComponentInstanceErrorMessageArray) > 1) {
                                    $this->validationPassed = false;
                                }
                            }
                        } else {
                            if (!empty($jFormComponentErrorMessageArray)) {
                                $this->validationPassed = false;
                            }
                        }
                    }
                }
            }
        }

        // Update the form status
        $this->setStatus('processing', 'Validation complete.');

        return $this->validationResponse;
    }

    public function getData()
    {
        $this->data = [];

        foreach ($this->jFormPageArray as $jFormPageKey => $jFormPage) {
            if (!$jFormPage->anonymous) {
                $this->data[$jFormPageKey] = $jFormPage->getData();
            } else {
                foreach ($jFormPage->jFormSectionArray as $jFormSectionKey => $jFormSection) {
                    if (!$jFormSection->anonymous) {
                        $this->data[$jFormSectionKey] = $jFormSection->getData();
                    } else {
                        foreach ($jFormSection->jFormComponentArray as $jFormComponentKey => $jFormComponent) {
                            if (get_class($jFormComponent) != 'JFormComponentHtml') { // Don't include HTML components
                                $this->data[$jFormComponentKey] = $jFormComponent->getValue();
                            }
                        }
                    }
                }
            }
        }
        return json_decode(json_encode($this->data));
    }

    public function updateRequiredText($requiredText)
    {
        foreach ($this->jFormPageArray as $jFormPage) {
            $jFormPage->updateRequiredText($requiredText);
        }
    }

    public function setInitialValues($formValues)
    {
        // Make sure we are always working with an object
        if (!is_object($formValues)) {
            $formValues = json_decode(urldecode($formValues));
            if (!is_object($formValues)) {
                $formValues = json_decode(urldecode(stripslashes($data)));
            }
        }

        // Walk through the form object and apply initial values
        foreach ($formValues as $formPageKey => $formPageData) {
            $this->formPageArray[$formPageKey]->setInitialValues($formPageData);
        }
    }

    public function setData($data, $fileArray = [])
    {
        // Get the form data as an object, handle apache auto-add slashes on post requests
        $jFormerData = json_decode(urldecode($data));
        if (!is_object($jFormerData)) {
            $jFormerData = json_decode(urldecode(stripslashes($data)));
        }

        // Clear all of the component values
        $this->clearData();

        //print_r($jFormerData); exit();
        //print_r($fileArray);
        // Update the form status
        $this->setStatus('processing', 'Setting component values.');

        // Assign all of the received JSON values to the form
        foreach ($jFormerData as $jFormPageKey => $jFormPageData) {
            $this->jFormPageArray[$jFormPageKey]->setData($jFormPageData);
        }

        // Handle files
        if (!empty($fileArray)) {
            foreach ($fileArray as $jFormComponentId => $fileDataArray) {
                preg_match('/(-section([0-9])+)?(-instance([0-9])+)?:([A-Za-z0-9_-]+):([A-Za-z0-9_-]+)/', $jFormComponentId, $fileIdInfo);

                $jFormComponentId = str_replace($fileIdInfo[0], '', $jFormComponentId);
                $jFormPageId = $fileIdInfo[5];
                $jFormSectionId = $fileIdInfo[6];

                // Inside section instances
                if ($fileIdInfo[1] != null || ($fileIdInfo[1] == null && array_key_exists(0, $this->jFormPageArray[$jFormPageId]->jFormSectionArray[$jFormSectionId]->jFormComponentArray))) {
                    // section instance
                    // set the instance index
                    if ($fileIdInfo[1] != null) {
                        $jFormSectionInstanceIndex = $fileIdInfo[2] - 1;
                    } else {
                        // prime instance
                        $jFormSectionInstanceIndex = 0;
                    }
                    // check to see if there is a component instance
                    if ($fileIdInfo[3] != null || ($fileIdInfo[3] == null && is_array($this->jFormPageArray[$jFormPageId]->jFormSectionArray[$jFormSectionId]->jFormComponentArray[$jFormSectionInstanceIndex][$jFormComponentId]->value))) {
                        // set the component instance index inside of a  section instance
                        if ($fileIdInfo[3] == null) {
                            $jFormComponentInstanceIndex = 0;
                        } else {
                            $jFormComponentInstanceIndex = $fileIdInfo[4] - 1;
                        }
                        // set the value with a section and a component instance
                        $this->jFormPageArray[$jFormPageId]->jFormSectionArray[$jFormSectionId]->jFormComponentArray[$jFormSectionInstanceIndex][$jFormComponentId]->value[$jFormComponentInstanceIndex] = $fileDataArray;
                    } else {
                        // set the value with a section instance
                        $this->jFormPageArray[$jFormPageId]->jFormSectionArray[$jFormSectionId]->jFormComponentArray[$jFormSectionInstanceIndex][$jFormComponentId]->value = $fileDataArray;
                    }
                }

                // Not section instances
                else {
                    // has component instances
                    if ($fileIdInfo[3] != null || ($fileIdInfo[3] == null && is_array($this->jFormPageArray[$jFormPageId]->jFormSectionArray[$jFormSectionId]->jFormComponentArray[$jFormComponentId]->value))) {
                        // set component  instance index
                        if ($fileIdInfo[3] == null) {
                            $jFormComponentInstanceIndex = 0;
                        } else {
                            $jFormComponentInstanceIndex = $fileIdInfo[4] - 1;
                        }
                        $this->jFormPageArray[$jFormPageId]->jFormSectionArray[$jFormSectionId]->jFormComponentArray[$jFormComponentId]->value[$jFormComponentInstanceIndex] = $fileDataArray;
                    } else {
                        // no instances
                        $this->jFormPageArray[$jFormPageId]->jFormSectionArray[$jFormSectionId]->jFormComponentArray[$jFormComponentId]->value = $fileDataArray;
                    }
                }
            }
        }

        return $this;
    }

    public function clearData()
    {
        foreach ($this->jFormPageArray as $jFormPage) {
            $jFormPage->clearData();
        }
        $this->data = null;
    }

    public function clearAllComponentValues()
    {
        // Clear all of the components in the form
        foreach ($this->jFormPageArray as $jFormPage) {
            foreach ($jFormPage->jFormSectionArray as $jFormSection) {
                foreach ($jFormSection->jFormComponentArray as $jFormComponent) {
                    $jFormComponent->value = null;
                }
            }
        }
    }

    public function select($id)
    {
        foreach ($this->jFormPageArray as $jFormPageId => &$jFormPage) {
            if ($id === $jFormPageId) {
                return $jFormPage;
            }
            foreach ($jFormPage->jFormSectionArray as $jFormSectionId => &$jFormSection) {
                if ($id === $jFormSectionId) {
                    return $jFormSection;
                }
                foreach ($jFormSection->jFormComponentArray as $jFormComponentId => &$jFormComponent) {
                    if (is_array($jFormComponent)) {
                        foreach ($jFormComponent as $sectionInstanceComponentId => &$sectionInstanceComponent) {
                            if ($id === $sectionInstanceComponentId) {
                                return $sectionInstanceComponent;
                            }
                        }
                    }
                    if ($id === $jFormComponentId) {
                        return $jFormComponent;
                    }
                }
            }
        }
        return false;
    }

    public function remove($id)
    {
        foreach ($this->jFormPageArray as $jFormPageId => &$jFormPage) {
            if ($id === $jFormPageId) {
                $this->jFormPageArray[$jFormPageId] = null;
                array_filter($this->jFormPageArray);
                return true;
            }
            foreach ($jFormPage->jFormSectionArray as $jFormSectionId => &$jFormSection) {
                if ($id === $jFormSectionId) {
                    $jFormPage->jFormSectionArray[$jFormSectionId] = null;
                    array_filter($jFormPage->jFormSectionArray);
                    return true;
                }
                foreach ($jFormSection->jFormComponentArray as $jFormComponentId => &$jFormComponent) {
                    if ($id === $jFormComponentId) {
                        $jFormSection->jFormComponentArray[$jFormComponentId] = null;
                        array_filter($jFormSection->jFormComponentArray);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function processRequest($silent = false)
    {
        // Are they trying to post a file that is too large?
        if (isset($_SERVER['CONTENT_LENGTH']) && empty($_POST)) {
            $this->setStatus('success', ['failureNoticeHtml' => 'Your request (' . round($_SERVER['CONTENT_LENGTH'] / 1024 / 1024, 1) . 'M) was too large for the server to handle. ' . ini_get('post_max_size') . ' is the maximum request size.']);
            echo '
				<script type="text/javascript" language="javascript">
					parent.' . $this->id . 'Object.handleFormSubmissionResponse(' . json_encode($this->getStatus()) . ');
				</script>
			';
            exit();
        }

        // Are they trying to post something to the form?
        if (isset($_POST['jFormer']) && $this->id == $_POST['jFormerId'] || isset($_POST['jFormerTask'])) {
            // Process the form, get the form state, or display the form
            if (isset($_POST['jFormer'])) {
                //echo json_encode($_POST);
                $onSubmitErrorMessageArray = [];

                // Set the form components and validate the form
                $this->setData($_POST['jFormer'], $_FILES);

                //print_r($this->getData());
                // Run validation
                $this->validate();
                if (!$this->validationPassed) {
                    $this->setStatus('failure', ['validationFailed' => $this->validationResponse]);
                } else {
                    try {
                        $onSubmitResponse = call_user_func($this->onSubmitFunctionServerSide, $this->getData());
                    } catch (Exception $exception) {
                        $onSubmitErrorMessageArray[] = $exception->getTraceAsString();
                    }

                    // Make sure you actually get a callback response
                    if (empty($onSubmitResponse)) {
                        $onSubmitErrorMessageArray[] = '<p>The function <b>' . $this->onSubmitFunctionServerSide . '</b> did not return a valid response.</p>';
                    }

                    // If there are no errors, it is a successful response
                    if (empty($onSubmitErrorMessageArray)) {
                        $this->setStatus('success', $onSubmitResponse);
                    } else {
                        $this->setStatus('failure', ['failureHtml' => $onSubmitErrorMessageArray]);
                    }
                }
                if ($this->useIframeTarget) {
                    echo '
						<script type="text/javascript" language="javascript">
							parent.' . $this->id . 'Object.handleFormSubmissionResponse(' . json_encode($this->getStatus()) . ');
						</script>
					';
                }
                //echo json_encode($this->getValues());

                exit();
            }
            // Get the form's status
            elseif (isset($_POST['jFormerTask']) && $_POST['jFormerTask'] == 'getFormStatus') {
                $onSubmitResponse = $this->getStatus();
                echo json_encode($onSubmitResponse);
                $this->resetStatus();
                exit();
            }
        }
        // If they aren't trying to post something to the form
        elseif (!$silent) {
            $this->outputHtml();
        }
    }

    public function getOptions()
    {
        $options = [];
        $options['options'] = [];
        $options['jFormPages'] = [];

        // Get all of the pages
        foreach ($this->jFormPageArray as $jFormPage) {
            $options['jFormPages'][$jFormPage->id] = $jFormPage->getOptions();
        }

        // Set form options
        if (!$this->clientSideValidation) {
            $options['options']['clientSideValidation'] = $this->clientSideValidation;
        }
        if ($this->debugMode) {
            $options['options']['debugMode'] = $this->debugMode;
        }
        if (!$this->validationTips) {
            $options['options']['validationTips'] = $this->validationTips;
        }
        if (!$this->setupPageScroller) {
            $options['options']['setupPageScroller'] = $this->setupPageScroller;
        }
        if ($this->animationOptions !== null) {
            $options['options']['animationOptions'] = $this->animationOptions;
        }
        if ($this->pageNavigatorEnabled) {
            $options['options']['pageNavigator'] = $this->pageNavigator;
        }
        if ($this->splashPageEnabled) {
            $options['options']['splashPage'] = $this->splashPage;
            unset($options['options']['splashPage']['content']);
        }
        if (!empty($this->onSubmitStartClientSide)) {
            $options['options']['onSubmitStart'] = $this->onSubmitStartClientSide;
        }
        if (!empty($this->onSubmitFinishClientSide)) {
            $options['options']['onSubmitFinish'] = $this->onSubmitFinishClientSide;
        }
        if (!$this->alertsEnabled) {
            $options['options']['alertsEnabled'] = false;
        }
        if ($this->submitButtonText != 'Submit') {
            $options['options']['submitButtonText'] = $this->submitButtonText;
        }
        if ($this->submitProcessingButtonText != 'Processing...') {
            $options['options']['submitProcessingButtonText'] = $this->submitProcessingButtonText;
        }

        if (empty($options['options'])) {
            unset($options['options']);
        }

        return $options;
    }

    public function outputHtml()
    {
        echo $this->getHtml();
    }

    public function __toString()
    {
        $element = $this->getHtml();
        return $element->__toString();
    }

    public function getHtml()
    {
        $this->updateRequiredText($this->requiredText);
        // Create the form
        $target = $this->useIframeTarget ? $this->id . '-iframe' : '';
        $jFormElement = new JFormElement('form', [
                    'id' => $this->id,
                    'target' => $target,
                    'enctype' => 'multipart/form-data',
                    'method' => 'post',
                    'class' => $this->class . ' form-'.$this->form_type,
                    'action' => $this->action,
                ]);
        if (!empty($this->onMouseOver)) {
            $formJFormElement->attr('onmouseover', $this->onMouseOver);
        }

        if (!empty($this->onMouseOut)) {
            $formJFormElement->attr('onmouseout', $this->onMouseOut);
        }

        // Set the style
        if (!empty($this->style)) {
            $jFormElement->addToAttribute('style', $this->style);
        }

        // Global messages
        if ($this->alertsEnabled) {
            $jFormerAlertWrapperDiv = new JFormElement('div', [
                        'class' => 'jFormerAlertWrapper',
                        'style' => 'display: none;',
                    ]);
            $alertDiv = new JFormElement('div', [
                        'class' => 'jFormerAlert',
                    ]);
            $jFormerAlertWrapperDiv->insert($alertDiv);
            $jFormElement->insert($jFormerAlertWrapperDiv);
        }

        // If a splash is enabled
        if ($this->splashPageEnabled) {
            // Create a splash page div
            $splashPageDiv = new JFormElement('div', [
                        'id' => $this->id . '-splash-page',
                        'class' => 'jFormerSplashPage jFormPage',
                    ]);

            // Set defaults if they aren't set
            if (!isset($this->splashPage['content'])) {
                $this->splashPage['content'] = '';
            }
            if (!isset($this->splashPage['splashButtonText'])) {
                $this->splashPage['splashButtonText'] = 'Begin';
            }

            $splashPageDiv->insert('<div class="jFormerSplashPageContent">' . $this->splashPage['content'] . '</div>');

            // Create a splash button if there is no custom button ID
            if (!isset($this->splashPage['customButtonId'])) {
                $splashLi = new JFormElement('li', ['class' => 'splashLi']);
                $splashButton = new JFormElement('button', ['class' => 'splashButton']);
                $splashButton->update($this->splashPage['splashButtonText']);
                $splashLi->insert($splashButton);
            }
        }

        // Add a title to the form
        if (!empty($this->title)) {
            $title = new JFormElement('div', [
                        'class' => $this->titleClass
                    ]);
            $title->update($this->title);
            $jFormElement->insert($title);
        }

        // Add a description to the form
        if (!empty($this->description)) {
            $description = new JFormElement('div', [
                        'class' => $this->descriptionClass
                    ]);
            $description->update($this->description);
            $jFormElement->insert($description);
        }

        // Add the page navigator if enabled
        if ($this->pageNavigatorEnabled) {
            $pageNavigatorDiv = new JFormElement('div', [
                        'class' => 'jFormPageNavigator',
                    ]);
            if (isset($this->pageNavigator['position']) && $this->pageNavigator['position'] == 'right') {
                $pageNavigatorDiv->addToAttribute('class', ' jFormPageNavigatorRight');
            } else {
                $pageNavigatorDiv->addToAttribute('class', ' jFormPageNavigatorTop');
            }

            $pageNavigatorUl = new JFormElement('ul', [
                    ]);

            $jFormPageArrayCount = 0;
            foreach ($this->jFormPageArray as $jFormPageKey => $jFormPage) {
                $jFormPageArrayCount++;

                $pageNavigatorLabel = new JFormElement('li', [
                            'id' => 'navigatePage' . $jFormPageArrayCount,
                            'class' => 'jFormPageNavigatorLink',
                        ]);

                // If the label is numeric
                if (isset($this->pageNavigator['label']) && $this->pageNavigator['label'] == 'numeric') {
                    $pageNavigatorLabelText = 'Page ' . $jFormPageArrayCount;
                } else {
                    // Add a link prefix if there is a title
                    if (!empty($jFormPage->title)) {
                        $pageNavigatorLabelText = '<span class="jFormNavigatorLinkPrefix">' . $jFormPageArrayCount . '</span> ' . strip_tags($jFormPage->title);
                    } else {
                        $pageNavigatorLabelText = 'Page ' . $jFormPageArrayCount;
                    }
                }
                $pageNavigatorLabel->update($pageNavigatorLabelText);

                if ($jFormPageArrayCount != 1) {
                    $pageNavigatorLabel->addToAttribute('class', ' jFormPageNavigatorLinkLocked');
                } else {
                    $pageNavigatorLabel->addToAttribute('class', ' jFormPageNavigatorLinkUnlocked jFormPageNavigatorLinkActive');
                }

                $pageNavigatorUl->insert($pageNavigatorLabel);
            }

            // Add the page navigator ul to the div
            $pageNavigatorDiv->insert($pageNavigatorUl);

            $jFormElement->insert($pageNavigatorDiv);
        }

        // Add the jFormerControl UL
        $jFormerControlUl = new JFormElement('ul', [
                    'class' => 'jFormerControl offset-sm-4 col-sm-8',
            'style' => 'list-style-type: none;',
                ]);

        // Create the cancel button
        if ($this->cancelButton) {
            $cancelButtonLi = new JFormElement('li', ['class' => 'cancelLi']);
            $cancelButton = new JFormElement('button', ['class' => $this->cancelButtonClass]);
            $cancelButton->update($this->cancelButtonText);

            if (!empty($this->cancelButtonOnClick)) {
                $cancelButton->attr('onclick', $this->cancelButtonOnClick);
            }

            $cancelButtonLi->append($cancelButton);
        }

        // Create the previous button
        $previousButtonLi = new JFormElement('li', ['class' => 'previousLi', 'style' => 'display: none;']);
        $previousButton = new JFormElement('button', ['class' => 'previousButton']);
        $previousButton->update('Previous');
        $previousButtonLi->insert($previousButton);

        // Create the next button
        $nextButtonLi = new JFormElement('li', ['class' => 'nextLi']);
        $nextButton = new JFormElement('button', ['class' => 'btn btn-secondary nextButton']);
        $nextButton->update($this->submitButtonText);
        // Don't show the next button
        if ($this->splashPageEnabled) {
            $nextButtonLi->setAttribute('style', 'display: none;');
        }
        $nextButtonLi->insert($nextButton);

        // Add a splash page button if it exists
        if (isset($splashLi)) {
            $jFormerControlUl->insert($splashLi);
        }

        // Add the previous and next buttons
        $jFormerControlUl->insert($previousButtonLi);

        if ($this->cancelButton && $this->cancelButtonLiBeforeNextButtonLi) {
            echo 'one';
            $jFormerControlUl->insert($cancelButtonLi);
            $jFormerControlUl->insert($nextButtonLi);
        } elseif ($this->cancelButton) {
            echo 'two';
            $jFormerControlUl->insert($nextButtonLi);
            $jFormerControlUl->insert($cancelButtonLi);
        } else {
            $jFormerControlUl->insert($nextButtonLi);
        }

        // Create the page wrapper and scrollers
        $jFormPageWrapper = new JFormElement('div', ['class' => 'jFormPageWrapper']);
        $jFormPageScroller = new JFormElement('div', ['class' => 'jFormPageScroller']);

        // Add a splash page if it exists
        if (isset($splashPageDiv)) {
            $jFormPageScroller->insert($splashPageDiv);
        }

        // Add the form pages to the form
        $jFormPageCount = 0;
        foreach ($this->jFormPageArray as $jFormPage) {
            // Hide everything but the first page
            if ($jFormPageCount != 0 || ($jFormPageCount == 0 && ($this->splashPageEnabled))) {
                $jFormPage->style .= 'display: none;';
            }

            $jFormPageScroller->insert($jFormPage);
            $jFormPageCount++;
        }

        // Page wrapper wrapper
        $pageWrapperContainer = new JFormElement('div', ['class' => 'jFormWrapperContainer']);

        // Insert the page wrapper and the jFormerControl UL to the form
        $jFormElement->insert($pageWrapperContainer->insert($jFormPageWrapper->insert($jFormPageScroller) . '<div class="form-group" style="padding-top: 10px;">'.$jFormerControlUl.'</div>'));

        // Create a script tag to initialize jFormer JavaScript
        $script = new JFormElement('script', [
                    'type' => 'text/javascript',
                    'language' => 'javascript'
                ]);

        // Update the script tag
        $script->update('$(document).ready(function () { ' . $this->id . 'Object = new JFormer(\'' . $this->id . '\', ' . json_encode($this->getOptions()) . '); });');
        $jFormElement->insert($script);

        // Add a hidden iframe to handle the form posts
        $iframe = new JFormElement('iframe', [
                    'id' => $this->id . '-iframe',
                    'name' => $this->id . '-iframe',
                    'class' => 'jFormerIFrame',
                    'frameborder' => 0,
                    'height' => '0px;',
                    'src' => (defined('URLDIR') ? URLDIR : '') . '/js/jformer-detain-git/php/JFormer.php?iframe=true',
                    //'src' => '/empty.html',
                        //'src' => str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__).'?iframe=true',
                ]);

        if ($this->debugMode) {
            $iframe->addToAttribute('style', 'display:block;');
        }

        $jFormElement->insert($iframe);


        // After control
        if (!empty($this->afterControl)) {
            $subSubmitInstructions = new JFormElement('div', ['class' => 'jFormerAfterControl']);
            $subSubmitInstructions->update($this->afterControl);
            $jFormElement->insert($subSubmitInstructions);
        }

        return $jFormElement;
    }
}

// Handle any requests that come to this file
if (isset($_GET['iframe'])) {
    echo '';
}



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


/**
 * A FormSection object contains FormComponent objects and belongs to a FormPage object
 */
class JFormSection
{

    // General settings
    public $id;
    public $class = 'jFormSection';
    public $style = '';
    public $parentJFormPage;
    public $jFormComponentArray = [];
    public $data;
    public $anonymous = false;

    // Title, description, submit instructions
    public $title = '';
    public $titleClass = 'jFormSectionTitle';
    public $description = '';
    public $descriptionClass = 'jFormSectionDescription';

    // Options
    public $instanceOptions = null;
    public $dependencyOptions = null;

    // Validation
    public $errorMessageArray = [];

    /*
     * Constructor
     */
    public function __construct($id, $optionArray = [], $jFormComponentArray = [])
    {
        // Set the id
        $this->id = $id;

        // Use the options hash to update object variables
        if (is_array($optionArray)) {
            foreach ($optionArray as $option => $value) {
                $this->{$option} = $value;
            }
        }

        // Add the components from the constructor
        $this->addJFormComponentArray($jFormComponentArray);

        return $this;
    }

    public function addJFormComponent($jFormComponent)
    {
        $jFormComponent->parentJFormSection = $this;
        $this->jFormComponentArray[$jFormComponent->id] = $jFormComponent;

        return $this;
    }

    public function addJFormComponents($jFormComponents)
    {
        if (is_array($jFormComponents)) {
            foreach ($jFormComponentArray as $jFormComponent) {
                $jFormComponent->parentJFormSection = $this;
                $this->addJFormComponent($jFormComponent);
            }
        } else {
            $jFormComponent->parentJFormSection = $this;
            $this->jFormComponentArray[$jFormComponent->id] = $jFormComponent;
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

        // Check to see if jFormComponent array contains instances
        if (array_key_exists(0, $this->jFormComponentArray) && is_array($this->jFormComponentArray[0])) {
            foreach ($this->jFormComponentArray as $jFormComponentArrayInstanceIndex => $jFormComponentArrayInstance) {
                foreach ($jFormComponentArrayInstance as $jFormComponentKey => $jFormComponent) {
                    if (get_class($jFormComponent) != 'JFormComponentHtml') { // Don't include HTML components
                        $this->data[$jFormComponentArrayInstanceIndex][$jFormComponentKey] = $jFormComponent->getValue();
                    }
                }
            }
        }
        // If the section does not have instances
        else {
            foreach ($this->jFormComponentArray as $jFormComponentKey => $jFormComponent) {
                if (get_class($jFormComponent) != 'JFormComponentHtml') { // Don't include HTML components
                    $this->data[$jFormComponentKey] = $jFormComponent->getValue();
                }
            }
        }

        return $this->data;
    }

    public function setData($jFormSectionData)
    {
        // Handle multiple instances
        if (is_array($jFormSectionData)) {
            $newJFormComponentArray = [];

            // Go through each section instance
            foreach ($jFormSectionData as $jFormSectionIndex => $jFormSection) {
                // Create a clone of the jFormComponentArray
                $newJFormComponentArray[$jFormSectionIndex] = unserialize(serialize($this->jFormComponentArray));

                // Go through each component in the instanced section
                foreach ($jFormSection as $jFormComponentKey => $jFormComponentValue) {
                    // Set the value of the clone
                    $newJFormComponentArray[$jFormSectionIndex][$jFormComponentKey]->setValue($jFormComponentValue);
                }
            }
            $this->jFormComponentArray = $newJFormComponentArray;
        }
        // Single instance
        else {
            // Go through each component
            foreach ($jFormSectionData as $jFormComponentKey => $jFormComponentValue) {
                if (!is_null($this->jFormComponentArray[$jFormComponentKey])) {
                    $this->jFormComponentArray[$jFormComponentKey]->setValue($jFormComponentValue);
                }
            }
        }
    }

    public function clearData()
    {
        // Check to see if jFormComponent array contains instances
        if (array_key_exists(0, $this->jFormComponentArray) && is_array($this->jFormComponentArray[0])) {
            foreach ($this->jFormComponentArray as $jFormComponentArrayInstanceIndex => $jFormComponentArrayInstance) {
                foreach ($jFormComponentArrayInstance as $jFormComponentKey => $jFormComponent) {
                    $jFormComponent->clearValue();
                }
            }
        }
        // If the section does not have instances
        else {
            foreach ($this->jFormComponentArray as $jFormComponent) {
                $jFormComponent->clearValue();
            }
        }
        $this->data = null;
    }

    public function validate()
    {
        // Clear the error message array
        $this->errorMessageArray = [];

        // If we have instances, return an array
        if (array_key_exists(0, $this->jFormComponentArray) && is_array($this->jFormComponentArray[0])) {
            foreach ($this->jFormComponentArray as $jFormComponentArrayInstanceIndex => $jFormComponentArrayInstance) {
                foreach ($jFormComponentArrayInstance as $jFormComponentKey => $jFormComponent) {
                    $this->errorMessageArray[$jFormComponentArrayInstanceIndex][$jFormComponent->id] = $jFormComponent->validate();
                }
            }
        }
        // If the section does not have instances, return an single dimension array
        else {
            foreach ($this->jFormComponentArray as $jFormComponent) {
                $this->errorMessageArray[$jFormComponent->id] = $jFormComponent->validate();
            }
        }

        return $this->errorMessageArray;
    }

    public function updateRequiredText($requiredText)
    {
        foreach ($this->jFormComponentArray as $jFormComponent) {
            $jFormComponent->updateRequiredText($requiredText);
        }
    }

    public function getOptions()
    {
        $options = [];
        $options['options'] = [];
        $options['jFormComponents'] = [];

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

        // Dependencies
        if (!empty($this->dependencyOptions)) {
            // Make sure the dependentOn key is tied to an array
            if (isset($this->dependencyOptions['dependentOn']) && !is_array($this->dependencyOptions['dependentOn'])) {
                $this->dependencyOptions['dependentOn'] = [$this->dependencyOptions['dependentOn']];
            }
            $options['options']['dependencyOptions'] = $this->dependencyOptions;
        }

        // Get options for each of the jFormComponents
        foreach ($this->jFormComponentArray as $jFormComponent) {
            // Don't get options for JFormComponentHtml objects
            if (get_class($jFormComponent) != 'JFormComponentHtml') {
                $options['jFormComponents'][$jFormComponent->id] = $jFormComponent->getOptions();
            }
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
        // Section fieldset
        $jFormSectionDiv = new JFormElement('div', [
            'id' => $this->id,
            'class' => $this->class
        ]);

        // This causes issues with things that are dependent and should display by default
        // If the section has dependencies and the display type is hidden, hide by default
        //if($this->dependencyOptions !== null && isset($this->dependencyOptions['display']) && $this->dependencyOptions['display'] == 'hide') {
        //    $jFormSectionDiv->setAttribute('style', 'display: none;');
        //}

        // Set the style
        if (!empty($this->style)) {
            $jFormSectionDiv->addToAttribute('style', $this->style);
        }

        // Add a title to the page
        if (!empty($this->title)) {
            $title = new JFormElement('div', [
                'class' => $this->titleClass
            ]);
            $title->update($this->title);
            $jFormSectionDiv->insert($title);
        }

        // Add a description to the page
        if (!empty($this->description)) {
            $description = new JFormElement('div', [
                'class' => $this->descriptionClass
            ]);
            $description->update($this->description);
            $jFormSectionDiv->insert($description);
        }

        // Add the form sections to the page
        foreach ($this->jFormComponentArray as $jFormComponentArray) {
            $jFormSectionDiv->insert($jFormComponentArray);
        }

        return $jFormSectionDiv->__toString();
    }
}


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




class JFormComponentCreditCard extends JFormComponent
{
    public $emptyValues = null; // cardNumber, securityCode
    public $showSublabels = true;
    public $showCardType = true;
    public $showSecurityCode = true;
    public $creditCardProviders = ['visa' => 'Visa', 'masterCard' => 'MasterCard', 'americanExpress' => 'American Express', 'discover' => 'Discover'];
    public $showMonthName = true;
    public $showLongYear = true;

    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'jFormComponentCreditCard';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);

        // Set the empty values with a boolean
        if ($this->emptyValues === true) {
            $this->emptyValues = ['cardNumber' => 'Card Number', 'securityCode' => 'CSC/CVV'];
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
            $options['options']['emptyValues'] = $this->emptyValues;
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

        // Add the card type select tag
        if ($this->showCardType) {
            $cardTypeDiv = new JFormElement('div', [
                'class' => 'cardTypeDiv',
            ]);
            $cardType = new JFormElement('select', [
                'id' => $this->id.'-cardType',
                'name' => $this->name.'-cardType',
                'class' => 'cardType',
            ]);
            // Have a default value the drop down list if there isn't a sublabel
            if ($this->showSublabels == false) {
                $cardType->insert($this->getOption('', 'Card Type', true, true));
            }
            // Add the card types
            foreach ($this->creditCardProviders as $key => $value) {
                $cardType->insert($this->getOption($key, $value, false, false));
            }
            $cardTypeDiv->insert($cardType);
        }

        // Add the card number input tag
        $cardNumberDiv = new JFormElement('div', [
            'class' => 'cardNumberDiv',
        ]);
        $cardNumber = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-cardNumber',
            'name' => $this->name.'-cardNumber',
            'class' => 'cardNumber',
            'maxlength' => '16',
        ]);
        $cardNumberDiv->insert($cardNumber);

        // Add the expiration month select tag
        $expirationDateDiv = new JFormElement('div', [
            'class' => 'expirationDateDiv',
        ]);
        $expirationMonth = new JFormElement('select', [
            'id' => $this->id.'-expirationMonth',
            'name' => $this->name.'-expirationMonth',
            'class' => 'expirationMonth',
        ]);
        // Have a default value the drop down list if there isn't a sublabel
        if ($this->showSublabels == false) {
            $expirationMonth->insert($this->getOption('', 'Month', true, true));
        }
        // Add the months
        foreach (JFormComponentDropDown::getMonthArray() as $dropDownOption) {
            $optionValue = $dropDownOption['value'] ?? '';
            $optionLabel = $dropDownOption['label'] ?? '';
            $optionSelected = $dropDownOption['selected'] ?? false;
            $optionDisabled = $dropDownOption['disabled'] ?? false;
            $optionOptGroup = $dropDownOption['optGroup'] ?? '';

            if ($this->showMonthName) {
                $expirationMonth->insert($this->getOption($optionValue, $optionValue.' - '.$optionLabel, $optionSelected, $optionDisabled));
                $expirationMonth->addClassName('long');
            } else {
                $expirationMonth->insert($this->getOption($optionValue, $optionValue, $optionSelected, $optionDisabled));
            }
        }
        $expirationDateDiv->insert($expirationMonth);
        // Add the expiration year select tag
        $expirationYear = new JFormElement('select', [
            'id' => $this->id.'-expirationYear',
            'name' => $this->name.'-expirationYear',
            'class' => 'expirationYear',
        ]);
        // Add years
        if ($this->showLongYear) {
            $startYear = Date('Y');
            $expirationYear->addClassName('long');
        } else {
            $startYear = Date('y');
            if (!$this->showMonthName) {
                $expirationDateDiv->insert('<span class="expirationDateSeparator">/</span>');
            }
        }
        if ($this->showSublabels == false) {
            $expirationYear->insert($this->getOption('', 'Year', true, true));
        }
        foreach (range($startYear, $startYear+11) as $year) {
            $expirationYear->insert($this->getOption($year, $year, false, false));
        }
        $expirationDateDiv->insert($expirationYear);

        // Add the security code input tag
        $securityCodeDiv = new JFormElement('div', [
            'class' => 'securityCodeDiv',
        ]);
        $securityCode = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-securityCode',
            'name' => $this->name.'-securityCode',
            'class' => 'securityCode',
            'maxlength' => '4',
        ]);
        $securityCodeDiv->insert($securityCode);

        // Set the empty values if they are enabled
        if (!empty($this->emptyValues)) {
            foreach ($this->emptyValues as $emptyValueKey => $emptyValue) {
                if ($emptyValueKey == 'cardNumber') {
                    $cardNumber->setAttribute('value', $emptyValue);
                    $cardNumber->addClassName('defaultValue');
                }
                if ($emptyValueKey == 'securityCode') {
                    $securityCode->setAttribute('value', $emptyValue);
                    $securityCode->addClassName('defaultValue');
                }
            }
        }

        // Put the sublabels in if the option allows for it
        if ($this->showSublabels) {
            if ($this->showCardType) {
                $cardTypeDiv->insert('<div class="jFormComponentSublabel"><p>Card Type</p></div>');
            }
            $cardNumberDiv->insert('<div class="jFormComponentSublabel"><p>Card Number</p></div>');
            $expirationDateDiv->insert('<div class="jFormComponentSublabel"><p>Expiration Date</p></div>');
            if ($this->showSecurityCode) {
                $securityCodeDiv->insert('<div class="jFormComponentSublabel"><p>Security Code</p></div>');
            }
        }

        // Insert the components
        if ($this->showCardType) {
            $componentDiv->insert($cardTypeDiv);
        }
        $componentDiv->insert($cardNumberDiv);
        $componentDiv->insert($expirationDateDiv);
        if ($this->showSecurityCode) {
            $componentDiv->insert($securityCodeDiv);
        }

        // Add any description (optional)
        $componentDiv = $this->insertComponentDescription($componentDiv);

        // Add a tip (optional)
        $componentDiv = $this->insertComponentTip($componentDiv);

        return $componentDiv->__toString();
    }

    // Credit card validations
    public function required($options)
    {
        $errorMessageArray = [];
        if ($this->showCardType && empty($options['value']->cardType)) {
            array_push($errorMessageArray, ['Card type is required.']);
        }
        if (empty($options['value']->cardNumber)) {
            array_push($errorMessageArray, ['Card number is required.']);
        } else {
            if (preg_match('/[^\d]/', $options['value']->cardNumber)) {
                array_push($errorMessageArray, ['Card number may only contain numbers.']);
            }
            if (mb_strlen($options['value']->cardNumber) > 16 || mb_strlen($options['value']->cardNumber) < 13) {
                array_push($errorMessageArray, ['Card number must contain 13 to 16 digits.']);
            }
        }
        if (empty($options['value']->expirationMonth)) {
            array_push($errorMessageArray, ['Expiration month is required.']);
        }
        if (empty($options['value']->expirationYear)) {
            array_push($errorMessageArray, ['Expiration year is required.']);
        }
        if ($this->showSecurityCode && empty($options['value']->securityCode)) {
            array_push($errorMessageArray, ['Security code is required.']);
        } elseif ($this->showSecurityCode) {
            if (preg_match('/[^\d]/', $options['value']->securityCode)) {
                array_push($errorMessageArray, ['Security code may only contain numbers.']);
            }
            if (mb_strlen($options['value']->securityCode) > 4 || mb_strlen($options['value']->securityCode) < 3) {
                array_push($errorMessageArray, ['Security code must contain 3 or 4 digits.']);
            }
        }
        return sizeof($errorMessageArray) < 1 ? 'success' : $errorMessageArray;
    }
}




class JFormComponentDate extends JFormComponentSingleLineText
{
    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'jFormComponentDate';

        // Input options
        $this->initialValue = '';
        $this->type = 'text';
        $this->disabled = false;
        $this->readOnly = false;
        $this->maxLength = '';
        $this->styleWidth = '';
        $this->mask = '9?9/9?9/9999';
        $this->emptyValue = '';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Generate the component div
        $div = parent::__toString();

        return $div;
    }

    // Date validations
    public function required($options)
    {
        $errorMessageArray = [];
        if ($options['value']->month == '' || $options['value']->day == '' || $options['value']->year == '' || $options['value'] == null) {
            array_push($errorMessageArray, 'Required.');
            return $errorMessageArray;
        }

        $month = intval($options['value']->month);
        $day = intval($options['value']->day);
        $year = intval($options['value']->year);
        $badDay = false;
        if ($options['value']->month == '' || $options['value']->day == '' || $options['value']->year == '') {
            return true;
        }

        if (!preg_match('/[\d]{4}/', $year)) {
            array_push($errorMessageArray, 'You must enter a valid year.');
        }
        if ($month < 1 || $month > 12) {
            array_push($errorMessageArray, 'You must enter a valid month.');
        }
        if ($month==4 || $month==6 || $month==9 || $month==11) {
            if ($day > 30) {
                $badDay = true;
            }
        } elseif ($month==2) {
            $days = (($year % 4 == 0) && ((!($year % 100 == 0)) || ($year % 400 == 0))) ? 29 : 28;
            if ($day > $days) {
                $badDay = true;
            }
        }
        if ($day > 31 || $day < 1) {
            $badDay = true;
        }
        if ($badDay) {
            array_push($errorMessageArray, 'You must enter a valid day.');
        }

        return sizeof($errorMessageArray) < 1 ? 'success' : $errorMessageArray;
    }
    public function minDate($options)
    {
        $errorMessageArray = [];
        $month = intval($options['value']->month);
        $day = intval($options['value']->day);
        $year = intval($options['value']->year);
        $error = false;
        if (!empty($year) && !empty($month) && !empty($day)) {
            if (strtotime($year.'-'.$month.'-'.$day) < strtotime($options['minDate'])) {
                $error = true;
            }
        }
        // If they did not provide a date, validate true
        else {
            return 'success';
        }

        if ($error) {
            array_push($errorMessageArray, 'Date must be on or after '.date('F j, Y', strtotime($options['minDate'])).'.');
        }

        return sizeof($errorMessageArray) < 1 ? 'success' : $errorMessageArray;
    }
    public function maxDate($options)
    {
        $errorMessageArray = [];
        $month = intval($options['value']->month);
        $day = intval($options['value']->day);
        $year = intval($options['value']->year);
        $error = false;
        if (!empty($year) && !empty($month) && !empty($day)) {
            if (strtotime($year.'-'.$month.'-'.$day) > strtotime($options['maxDate'])) {
                $error = true;
            }
        }
        // If they did not provide a date, validate true
        else {
            return 'success';
        }

        if ($error) {
            array_push($errorMessageArray, 'Date must be on or before '.date('F j, Y', strtotime($options['maxDate'])).'.');
        }

        return sizeof($errorMessageArray) < 1 ? 'success' : $errorMessageArray;
    }
    public function teenager($options)
    {
        $errorMessageArray = [];
        $month = intval($options['value']->month);
        $day = intval($options['value']->day);
        $year = intval($options['value']->year);
        $error = false;
        if (!empty($year) && !empty($month) && !empty($day)) {
            if (strtotime($year.'-'.$month.'-'.$day) > strtotime('-13 years')) {
                $error = true;
            }
        }
        // If they did not provide a date, validate true
        else {
            return 'success';
        }

        if ($error) {
            array_push($errorMessageArray, 'You must be at least 13 years old to use this site.');
        }

        return sizeof($errorMessageArray) < 1 ? 'success' : $errorMessageArray;
    }
}



class JFormComponentDropDown extends JFormComponent
{
    public $dropDownOptionArray = [];

    public $disabled = false;
    public $multiple = false;
    public $size = null;
    public $width = null;

    /**
     * Constructor
     */
    public function __construct($id, $label, $dropDownOptionArray, $optionArray = [])
    {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentDropDown';
        $this->label = $label;
        $this->dropDownOptionArray = $dropDownOptionArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
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

    public static function getCountryArray($selectedCountry = null)
    {
        $countryArray = [['value' => '', 'label'  => 'Select a Country', 'disabled' => true], ['value' => 'US', 'label'  => 'United States of America'], ['value' => 'AF', 'label'  => 'Afghanistan'], ['value' => 'AL', 'label'  => 'Albania'], ['value' => 'DZ', 'label'  => 'Algeria'], ['value' => 'AS', 'label'  => 'American Samoa'], ['value' => 'AD', 'label'  => 'Andorra'], ['value' => 'AO', 'label'  => 'Angola'], ['value' => 'AI', 'label'  => 'Anguilla'], ['value' => 'AQ', 'label'  => 'Antarctica'], ['value' => 'AG', 'label'  => 'Antigua and Barbuda'], ['value' => 'AR', 'label'  => 'Argentina'], ['value' => 'AM', 'label'  => 'Armenia'], ['value' => 'AW', 'label'  => 'Aruba'], ['value' => 'AU', 'label'  => 'Australia'], ['value' => 'AT', 'label'  => 'Austria'], ['value' => 'AZ', 'label'  => 'Azerbaijan'], ['value' => 'BS', 'label'  => 'Bahamas'], ['value' => 'BH', 'label'  => 'Bahrain'], ['value' => 'BD', 'label'  => 'Bangladesh'], ['value' => 'BB', 'label'  => 'Barbados'], ['value' => 'BY', 'label'  => 'Belarus'], ['value' => 'BE', 'label'  => 'Belgium'], ['value' => 'BZ', 'label'  => 'Belize'], ['value' => 'BJ', 'label'  => 'Benin'], ['value' => 'BM', 'label'  => 'Bermuda'], ['value' => 'BT', 'label'  => 'Bhutan'], ['value' => 'BO', 'label'  => 'Bolivia'], ['value' => 'BA', 'label'  => 'Bosnia and Herzegovina'], ['value' => 'BW', 'label'  => 'Botswana'], ['value' => 'BV', 'label'  => 'Bouvet Island'], ['value' => 'BR', 'label'  => 'Brazil'], ['value' => 'IO', 'label'  => 'British Indian Ocean Territory'], ['value' => 'BN', 'label'  => 'Brunei'], ['value' => 'BG', 'label'  => 'Bulgaria'], ['value' => 'BF', 'label'  => 'Burkina Faso'], ['value' => 'BI', 'label'  => 'Burundi'], ['value' => 'KH', 'label'  => 'Cambodia'], ['value' => 'CM', 'label'  => 'Cameroon'], ['value' => 'CA', 'label'  => 'Canada'], ['value' => 'CV', 'label'  => 'Cape Verde'], ['value' => 'KY', 'label'  => 'Cayman Islands'], ['value' => 'CF', 'label'  => 'Central African Republic'], ['value' => 'TD', 'label'  => 'Chad'], ['value' => 'CL', 'label'  => 'Chile'], ['value' => 'CN', 'label'  => 'China'], ['value' => 'CX', 'label'  => 'Christmas Island'], ['value' => 'CC', 'label'  => 'Cocos (Keeling) Islands'], ['value' => 'CO', 'label'  => 'Columbia'], ['value' => 'KM', 'label'  => 'Comoros'], ['value' => 'CG', 'label'  => 'Congo'], ['value' => 'CK', 'label'  => 'Cook Islands'], ['value' => 'CR', 'label'  => 'Costa Rica'], ['value' => 'CI', 'label'  => 'Cote D\'Ivorie (Ivory Coast)'], ['value' => 'HR', 'label'  => 'Croatia (Hrvatska)'], ['value' => 'CU', 'label'  => 'Cuba'], ['value' => 'CY', 'label'  => 'Cyprus'], ['value' => 'CZ', 'label'  => 'Czech Republic'], ['value' => 'CD', 'label'  => 'Democratic Republic of Congo (Zaire)'], ['value' => 'DK', 'label'  => 'Denmark'], ['value' => 'DJ', 'label'  => 'Djibouti'], ['value' => 'DM', 'label'  => 'Dominica'], ['value' => 'DO', 'label'  => 'Dominican Republic'], ['value' => 'TP', 'label'  => 'East Timor'], ['value' => 'EC', 'label'  => 'Ecuador'], ['value' => 'EG', 'label'  => 'Egypt'], ['value' => 'SV', 'label'  => 'El Salvador'], ['value' => 'GQ', 'label'  => 'Equatorial Guinea'], ['value' => 'ER', 'label'  => 'Eritrea'], ['value' => 'EE', 'label'  => 'Estonia'], ['value' => 'ET', 'label'  => 'Ethiopia'], ['value' => 'FK', 'label'  => 'Falkland Islands (Malvinas)'], ['value' => 'FO', 'label'  => 'Faroe Islands'], ['value' => 'FJ', 'label'  => 'Fiji'], ['value' => 'FI', 'label'  => 'Finland'], ['value' => 'FR', 'label'  => 'France'], ['value' => 'FX', 'label'  => 'France), Metropolitanarray('], ['value' => 'GF', 'label'  => 'French Guinea'], ['value' => 'PF', 'label'  => 'French Polynesia'], ['value' => 'TF', 'label'  => 'French Southern Territories'], ['value' => 'GA', 'label'  => 'Gabon'], ['value' => 'GM', 'label'  => 'Gambia'], ['value' => 'GE', 'label'  => 'Georgia'], ['value' => 'DE', 'label'  => 'Germany'], ['value' => 'GH', 'label'  => 'Ghana'], ['value' => 'GI', 'label'  => 'Gibraltar'], ['value' => 'GR', 'label'  => 'Greece'], ['value' => 'GL', 'label'  => 'Greenland'], ['value' => 'GD', 'label'  => 'Grenada'], ['value' => 'GP', 'label'  => 'Guadeloupe'], ['value' => 'GU', 'label'  => 'Guam'], ['value' => 'GT', 'label'  => 'Guatemala'], ['value' => 'GN', 'label'  => 'Guinea'], ['value' => 'GW', 'label'  => 'Guinea-Bissau'], ['value' => 'GY', 'label'  => 'Guyana'], ['value' => 'HT', 'label'  => 'Haiti'], ['value' => 'HM', 'label'  => 'Heard and McDonald Islands'], ['value' => 'HN', 'label'  => 'Honduras'], ['value' => 'HK', 'label'  => 'Hong Kong'], ['value' => 'HU', 'label'  => 'Hungary'], ['value' => 'IS', 'label'  => 'Iceland'], ['value' => 'IN', 'label'  => 'India'], ['value' => 'ID', 'label'  => 'Indonesia'], ['value' => 'IR', 'label'  => 'Iran'], ['value' => 'IQ', 'label'  => 'Iraq'], ['value' => 'IE', 'label'  => 'Ireland'], ['value' => 'IL', 'label'  => 'Israel'], ['value' => 'IT', 'label'  => 'Italy'], ['value' => 'JM', 'label'  => 'Jamaica'], ['value' => 'JP', 'label'  => 'Japan'], ['value' => 'JO', 'label'  => 'Jordan'], ['value' => 'KZ', 'label'  => 'Kazakhstan'], ['value' => 'KE', 'label'  => 'Kenya'], ['value' => 'KI', 'label'  => 'Kiribati'], ['value' => 'KW', 'label'  => 'Kuwait'], ['value' => 'KG', 'label'  => 'Kyrgyzstan'], ['value' => 'LA', 'label'  => 'Laos'], ['value' => 'LV', 'label'  => 'Latvia'], ['value' => 'LB', 'label'  => 'Lebanon'], ['value' => 'LS', 'label'  => 'Lesotho'], ['value' => 'LR', 'label'  => 'Liberia'], ['value' => 'LY', 'label'  => 'Libya'], ['value' => 'LI', 'label'  => 'Liechtenstein'], ['value' => 'LT', 'label'  => 'Lithuania'], ['value' => 'LU', 'label'  => 'Luxembourg'], ['value' => 'ME', 'label' => 'Montenegro'], ['value' => 'MO', 'label'  => 'Macau'], ['value' => 'MK', 'label'  => 'Macedonia'], ['value' => 'MG', 'label'  => 'Madagascar'], ['value' => 'MW', 'label'  => 'Malawi'], ['value' => 'MY', 'label'  => 'Malaysia'], ['value' => 'MV', 'label'  => 'Maldives'], ['value' => 'ML', 'label'  => 'Mali'], ['value' => 'MT', 'label'  => 'Malta'], ['value' => 'MH', 'label'  => 'Marshall Islands'], ['value' => 'MQ', 'label'  => 'Martinique'], ['value' => 'MR', 'label'  => 'Mauritania'], ['value' => 'MU', 'label'  => 'Mauritius'], ['value' => 'YT', 'label'  => 'Mayotte'], ['value' => 'MX', 'label'  => 'Mexico'], ['value' => 'FM', 'label'  => 'Micronesia'], ['value' => 'MD', 'label'  => 'Moldova'], ['value' => 'MC', 'label'  => 'Monaco'], ['value' => 'MN', 'label'  => 'Mongolia'], ['value' => 'MS', 'label'  => 'Montserrat'], ['value' => 'MA', 'label'  => 'Morocco'], ['value' => 'MZ', 'label'  => 'Mozambique'], ['value' => 'MM', 'label'  => 'Myanmar (Burma)'], ['value' => 'NA', 'label'  => 'Namibia'], ['value' => 'NR', 'label'  => 'Nauru'], ['value' => 'NP', 'label'  => 'Nepal'], ['value' => 'NL', 'label'  => 'Netherlands'], ['value' => 'AN', 'label'  => 'Netherlands Antilles'], ['value' => 'NC', 'label'  => 'New Caledonia'], ['value' => 'NZ', 'label'  => 'New Zealand'], ['value' => 'NI', 'label'  => 'Nicaragua'], ['value' => 'NE', 'label'  => 'Niger'], ['value' => 'NG', 'label'  => 'Nigeria'], ['value' => 'NU', 'label'  => 'Niue'], ['value' => 'NF', 'label'  => 'Norfolk Island'], ['value' => 'KP', 'label'  => 'North Korea'], ['value' => 'MP', 'label'  => 'Northern Mariana Islands'], ['value' => 'NO', 'label'  => 'Norway'], ['value' => 'OM', 'label'  => 'Oman'], ['value' => 'PK', 'label'  => 'Pakistan'], ['value' => 'PW', 'label'  => 'Palau'], ['value' => 'PA', 'label'  => 'Panama'], ['value' => 'PG', 'label'  => 'Papua New Guinea'], ['value' => 'PY', 'label'  => 'Paraguay'], ['value' => 'PE', 'label'  => 'Peru'], ['value' => 'PH', 'label'  => 'Philippines'], ['value' => 'PN', 'label'  => 'Pitcairn'], ['value' => 'PL', 'label'  => 'Poland'], ['value' => 'PT', 'label'  => 'Portugal'], ['value' => 'PR', 'label'  => 'Puerto Rico'], ['value' => 'QA', 'label'  => 'Qatar'], ['value' => 'RE', 'label'  => 'Reunion'], ['value' => 'RO', 'label'  => 'Romania'], ['value' => 'RS', 'label' => 'Serbia'], ['value' => 'RU', 'label'  => 'Russia'], ['value' => 'RW', 'label'  => 'Rwanda'], ['value' => 'SH', 'label'  => 'Saint Helena'], ['value' => 'KN', 'label'  => 'Saint Kitts and Nevis'], ['value' => 'LC', 'label'  => 'Saint Lucia'], ['value' => 'PM', 'label'  => 'Saint Pierre and Miquelon'], ['value' => 'VC', 'label'  => 'Saint Vincent and The Grenadines'], ['value' => 'SM', 'label'  => 'San Marino'], ['value' => 'ST', 'label'  => 'Sao Tome and Principe'], ['value' => 'SA', 'label'  => 'Saudi Arabia'], ['value' => 'SN', 'label'  => 'Senegal'], ['value' => 'SC', 'label'  => 'Seychelles'], ['value' => 'SL', 'label'  => 'Sierra Leone'], ['value' => 'SG', 'label'  => 'Singapore'], ['value' => 'SK', 'label'  => 'Slovak Republic'], ['value' => 'SI', 'label'  => 'Slovenia'], ['value' => 'SB', 'label'  => 'Solomon Islands'], ['value' => 'SO', 'label'  => 'Somalia'], ['value' => 'ZA', 'label'  => 'South Africa'], ['value' => 'GS', 'label'  => 'South Georgia'], ['value' => 'KR', 'label'  => 'South Korea'], ['value' => 'ES', 'label'  => 'Spain'], ['value' => 'LK', 'label'  => 'Sri Lanka'], ['value' => 'SD', 'label'  => 'Sudan'], ['value' => 'SR', 'label'  => 'Suriname'], ['value' => 'SJ', 'label'  => 'Svalbard and Jan Mayen'], ['value' => 'SZ', 'label'  => 'Swaziland'], ['value' => 'SE', 'label'  => 'Sweden'], ['value' => 'CH', 'label'  => 'Switzerland'], ['value' => 'SY', 'label'  => 'Syria'], ['value' => 'TW', 'label'  => 'Taiwan'], ['value' => 'TJ', 'label'  => 'Tajikistan'], ['value' => 'TZ', 'label'  => 'Tanzania'], ['value' => 'TH', 'label'  => 'Thailand'], ['value' => 'TG', 'label'  => 'Togo'], ['value' => 'TK', 'label'  => 'Tokelau'], ['value' => 'TO', 'label'  => 'Tonga'], ['value' => 'TT', 'label'  => 'Trinidad and Tobago'], ['value' => 'TN', 'label'  => 'Tunisia'], ['value' => 'TR', 'label'  => 'Turkey'], ['value' => 'TM', 'label'  => 'Turkmenistan'], ['value' => 'TC', 'label'  => 'Turks and Caicos Islands'], ['value' => 'TV', 'label'  => 'Tuvalu'], ['value' => 'UG', 'label'  => 'Uganda'], ['value' => 'UA', 'label'  => 'Ukraine'], ['value' => 'AE', 'label'  => 'United Arab Emirates'], ['value' => 'UK', 'label'  => 'United Kingdom'], ['value' => 'US', 'label'  => 'United States of America'], ['value' => 'UM', 'label'  => 'United States Minor Outlying Islands'], ['value' => 'UY', 'label'  => 'Uruguay'], ['value' => 'UZ', 'label'  => 'Uzbekistan'], ['value' => 'VU', 'label'  => 'Vanuatu'], ['value' => 'VA', 'label'  => 'Vatican City (Holy See)'], ['value' => 'VE', 'label'  => 'Venezuela'], ['value' => 'VN', 'label'  => 'Vietnam'], ['value' => 'VG', 'label'  => 'Virgin Islands (British)'], ['value' => 'VI', 'label'  => 'Virgin Islands (US)'], ['value' => 'WF', 'label'  => 'Wallis and Futuna Islands'], ['value' => 'EH', 'label'  => 'Western Sahara'], ['value' => 'WS', 'label'  => 'Western Samoa'], ['value' => 'YE', 'label'  => 'Yemen'], ['value' => 'YU', 'label'  => 'Yugoslavia'], ['value' => 'ZM', 'label'  => 'Zambia'], ['value' => 'ZW', 'label'  => 'Zimbabwe']];

        if (!empty($selectedCountry)) {
            foreach ($countryArray as &$countryOption) {
                if ($countryOption['value'] == $selectedCountry) {
                    $countryOption['selected'] = true;
                    break;
                }
            }
        } else {
            $countryArray[0]['selected'] = true;
        }

        return $countryArray;
    }

    public static function getStateArray($selectedState = null)
    {
        $stateArray = [['value' => '', 'label'  => 'Select a State', 'disabled' => true], ['value' => 'AL', 'label'  => 'Alabama'], ['value' => 'AK', 'label'  => 'Alaska'], ['value' => 'AZ', 'label'  => 'Arizona'], ['value' => 'AR', 'label'  => 'Arkansas'], ['value' => 'CA', 'label'  => 'California'], ['value' => 'CO', 'label'  => 'Colorado'], ['value' => 'CT', 'label'  => 'Connecticut'], ['value' => 'DE', 'label'  => 'Delaware'], ['value' => 'DC', 'label'  => 'District of Columbia'], ['value' => 'FL', 'label'  => 'Florida'], ['value' => 'GA', 'label'  => 'Georgia'], ['value' => 'HI', 'label'  => 'Hawaii'], ['value' => 'ID', 'label'  => 'Idaho'], ['value' => 'IL', 'label'  => 'Illinois'], ['value' => 'IN', 'label'  => 'Indiana'], ['value' => 'IA', 'label'  => 'Iowa'], ['value' => 'KS', 'label'  => 'Kansas'], ['value' => 'KY', 'label'  => 'Kentucky'], ['value' => 'LA', 'label'  => 'Louisiana'], ['value' => 'ME', 'label'  => 'Maine'], ['value' => 'MD', 'label'  => 'Maryland'], ['value' => 'MA', 'label'  => 'Massachusetts'], ['value' => 'MI', 'label'  => 'Michigan'], ['value' => 'MN', 'label'  => 'Minnesota'], ['value' => 'MS', 'label'  => 'Mississippi'], ['value' => 'MO', 'label'  => 'Missouri'], ['value' => 'MT', 'label'  => 'Montana'], ['value' => 'NE', 'label'  => 'Nebraska'], ['value' => 'NV', 'label'  => 'Nevada'], ['value' => 'NH', 'label'  => 'New Hampshire'], ['value' => 'NJ', 'label'  => 'New Jersey'], ['value' => 'NM', 'label'  => 'New Mexico'], ['value' => 'NY', 'label'  => 'New York'], ['value' => 'NC', 'label'  => 'North Carolina'], ['value' => 'ND', 'label'  => 'North Dakota'], ['value' => 'OH', 'label'  => 'Ohio'], ['value' => 'OK', 'label'  => 'Oklahoma'], ['value' => 'OR', 'label'  => 'Oregon'], ['value' => 'PA', 'label'  => 'Pennsylvania'], ['value' => 'RI', 'label'  => 'Rhode Island'], ['value' => 'SC', 'label'  => 'South Carolina'], ['value' => 'SD', 'label'  => 'South Dakota'], ['value' => 'TN', 'label'  => 'Tennessee'], ['value' => 'TX', 'label'  => 'Texas'], ['value' => 'UT', 'label'  => 'Utah'], ['value' => 'VT', 'label'  => 'Vermont'], ['value' => 'VA', 'label'  => 'Virginia'], ['value' => 'WA', 'label'  => 'Washington'], ['value' => 'WV', 'label'  => 'West Virginia'], ['value' => 'WI', 'label'  => 'Wisconsin'], ['value' => 'WY', 'label'  => 'Wyoming']];

        if (!empty($selectedState)) {
            foreach ($stateArray as &$stateOption) {
                if ($stateOption['value'] == $selectedState) {
                    $stateOption['selected'] = true;
                    break;
                }
            }
        } else {
            $stateArray[0]['selected'] = true;
        }

        return $stateArray;
    }

    public static function getMonthArray()
    {
        return [['value' => '01', 'label'  => 'January'], ['value' => '02', 'label'  => 'February'], ['value' => '03', 'label'  => 'March'], ['value' => '04', 'label'  => 'April'], ['value' => '05', 'label'  => 'May'], ['value' => '06', 'label'  => 'June'], ['value' => '07', 'label'  => 'July'], ['value' => '08', 'label'  => 'August'], ['value' => '09', 'label'  => 'September'], ['value' => '10', 'label'  => 'October'], ['value' => '11', 'label'  => 'November'], ['value' => '12', 'label'  => 'December']];
    }

    public static function getYearArray($minYear, $maxYear)
    {
        $yearArray = [];
        for ($i = $maxYear - $minYear; $i > 0; $i--) {
            $yearArray[] = ['value' => $i + $minYear, 'label' => $i + $minYear];
        }
        return $yearArray;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Div tag contains everything about the component
        $div = parent::generateComponentDiv();

        // Select tag
        $select = new JFormElement('select', [
            'id' => $this->id,
            'name' => $this->name,
            'class' => $this->class . ' form-control',
        ]);

        // Only use if disabled is set, otherwise will throw an error
        if ($this->disabled) {
            $select->setAttribute('disabled', 'disabled');
        }
        if ($this->multiple) {
            $select->setAttribute('multiple', 'multiple');
        }
        if ($this->size != null) {
            $select->setAttribute('size', $this->size);
        }
        if ($this->width != null) {
            $select->setAttribute('style', 'width:'.$this->width);
        }

        // Check for any opt groups
        $optGroupArray = [];
        foreach ($this->dropDownOptionArray as $dropDownOption) {
            if (isset($dropDownOption['optGroup']) && !empty($dropDownOption['optGroup'])) {
                $optGroupArray[] = $dropDownOption['optGroup'];
            }
        }
        $optGroupArray = array_unique($optGroupArray);

        // Create the optgroup elements
        foreach ($optGroupArray as $optGroup) {
            ${$optGroup} = new JFormElement('optgroup', ['label' => $optGroup]);
        }

        // Add any options to their appropriate optgroup
        foreach ($this->dropDownOptionArray as $dropDownOption) {
            if (isset($dropDownOption['optGroup']) && !empty($dropDownOption['optGroup'])) {
                $optionValue = $dropDownOption['value'] ?? '';
                $optionLabel =  $dropDownOption['label'] ?? '';
                $optionSelected =  $dropDownOption['selected'] ?? false;
                $optionDisabled =  $dropDownOption['disabled'] ?? false;
                $optionOptGroup =  $dropDownOption['optGroup'] ?? '';

                ${$dropDownOption['optGroup']}->insert($this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled));
            }
        }

        // Add any options that are not in an opt group to the select
        foreach ($this->dropDownOptionArray as $dropDownOption) {
            // Handle optgroup addition - only add the group if you haven't seen it yet
            if (isset($dropDownOption['optGroup']) && !empty($dropDownOption['optGroup']) && !isset(${$dropDownOption['optGroup'].'Added'})) {
                $select->insert(${$dropDownOption['optGroup']});
                ${$dropDownOption['optGroup'].'Added'} = true;
            }
            // Add any other elements
            elseif (!isset($dropDownOption['optGroup'])) {
                $optionValue = $dropDownOption['value'] ?? '';
                $optionLabel =  $dropDownOption['label'] ?? '';
                $optionSelected =  $dropDownOption['selected'] ?? false;
                $optionDisabled =  $dropDownOption['disabled'] ?? false;
                $optionOptGroup =  $dropDownOption['optGroup'] ?? '';

                $select->insert($this->getOption($optionValue, $optionLabel, $optionSelected, $optionDisabled));
            }
        }

        // Add the select box to the div
        $div->insert('<div class="col-sm-8">'.$select.'</div>');

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div, $this->id.'-div');

        return $div->__toString();
    }
}




class JFormComponentFile extends JFormComponent
{
    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentFile';
        $this->label = $label;
        $this->inputClass = 'file';

        //style hacking
        $this->customStyle = true;

        // Input options
        $this->type = 'file';
        $this->disabled = false;
        $this->maxLength = '';
        $this->styleWidth = '';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function hasInstanceValues()
    {
        return isset($this->value[0]);
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        if ($this->customStyle) {
            $options['options']['customStyle'] = true;
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
        $div = $this->generateComponentDiv();

        // Add the input tag
        $pseudoFileWrapper = new JFormElement('div', [
            'class' => 'pseudoFile',
            'style' => 'position:absolute;'
        ]);

        $pseudoFileInput = new JFormElement('input',  [
           'type'=> 'text',
           'disabled' => 'disabled',
        ]);

        $pseudoFileButton = new JFormElement('button',  [
           'onclick' => 'return false;',
           'disabled' => 'disabled'
        ]);
        $pseudoFileButton->update('Browse...');
        $pseudoFileWrapper->insert($pseudoFileInput);
        $pseudoFileWrapper->insert($pseudoFileButton);

        $input = new JFormElement('input', [
            'type' => $this->type,
            'id' => $this->id,
            'name' => $this->name,
            'class' => $this->inputClass,
            'size'=> 15,
        ]);
        if (!empty($this->styleWidth)) {
            $input->setAttribute('style', 'width: '.$this->styleWidth.';');
        }
        if (!empty($this->maxLength)) {
            $input->setAttribute('maxlength', $this->maxLength);
        }
        if ($this->disabled) {
            $input->setAttribute('disabled', 'disabled');
        }
        if ($this->customStyle) {
            $input->addClassName('hidden');
            $div->insert($pseudoFileWrapper);
        }
        $div->insert($input);

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div);

        return $div->__toString();
    }
    public function required($options)
    {
        $messageArray = ['Required.'];
        return !empty($options['value']) ? 'success' : $messageArray;
    }

    public function extension($options)
    {
        $messageArray = ['Must have the .'.$options->extension.' extension.'];
        $extensionRegex = '/\.'.options.extension.'$/';
        return $options['value']['name'] == '' || preg_match($extensionRegex , $options['value']['name']) ? 'success' : $messageArray;
    }

    public function extensionType($options)
    {
        $extensionType;
        $messageArray = ['Incorrect file type.'];

        if (is_array($options['extensionType'])) {
            $extensionType = '/\.('.implode('|', $options['extensionType']).')/';
        } else {
            $extensionObject = new stdClass();
            $extensionObject->image = '/\.(bmp|gif|jpg|png|psd|psp|thm|tif)$/i';
            $extensionObject->document = '/\.(doc|docx|log|msg|pages|rtf|txt|wpd|wps)$/i';
            $extensionObject->audio = '/\.(aac|aif|iff|m3u|mid|midi|mp3|mpa|ra|wav|wma)$/i';
            $extensionObject->video = '/\.(3g2|3gp|asf|asx|avi|flv|mov|mp4|mpg|rm|swf|vob|wmv)$/i';
            $extensionObject->web = '/\.(asp|css|htm|html|js|jsp|php|rss|xhtml)$/i';
            $extensionType = $extensionObject->$options['extensionType'];
            $messageArray = ['Must be an '.$options['extensionType'].' file type.'];
        }
        return empty($options['value']) || preg_match($extensionType , $options['value']['name']) ? 'success' : $messageArray;
    }
    public function size($options)
    {
        if (empty($options['value'])) {
            return 'success';
        }
        // they will give filesize in kb
        $fileSizeInKb = $this->value['size'] / 1024;
        return $fileSizeInKb <= $options['size'] ? 'success' : ['File must be smaller then ' . $options['size'].'kb. File is '.round($fileSizeInKb, 2). 'kb.'];
    }
    public function imageDimensions($options)
    {
        if (empty($options['value'])) {
            return 'success';
        }
        $imageInfo = getimagesize($this->value['tmp_name']);

        // Check to see if the file is an image
        if (!$imageInfo) {
            return ["File is not a valid image file."];
        } else {
            $errorMessageArray = [];
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            if ($width > $options['width']) {
                $errorMessageArray[] = ['The image must be less then '.$options['width'].'px wide. File is '.$width. 'px.'];
            }
            if ($height > $options['height']) {
                $errorMessageArray[] = ['The image must be less then '.$options['height'].'px tall. File is '.$height. 'px.'];
            }
        }
        return empty($errorMessageArray) ? 'success' : $errorMessageArray;
    }

    public function minImageDimensions($options)
    {
        if (empty($options['value'])) {
            return 'success';
        }
        $imageInfo = getimagesize($this->value['tmp_name']);

        // Check to see if the file is an image
        if (!$imageInfo) {
            return ["File is not a valid image file."];
        } else {
            $errorMessageArray = [];
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            if ($width < $options['width']) {
                $errorMessageArray[] = ['The image must at least then '.$options['width'].'px wide. File is '.$width. 'px.'];
            }
            if ($height < $options['height']) {
                $errorMessageArray[] = ['The image must at least then '.$options['height'].'px tall. File is '.$height. 'px.'];
            }
        }
        return empty($errorMessageArray) ? 'success' : $errorMessageArray;
    }
}




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
        return;
    }
}




class JFormComponentMultipleChoice extends JFormComponent
{
    public $multipleChoiceType = 'checkbox'; // radio, checkbox
    public $multipleChoiceClass = 'choice';
    public $multipleChoiceLabelClass = 'choiceLabel';
    public $multipleChoiceArray = [];
    public $showMultipleChoiceTipIcons = true;

    /**
     * Constructor
     */
    public function __construct($id, $label, $multipleChoiceArray, $optionArray = [])
    {
        // General settings
        $this->id = $id;
        $this->name = $this->id;
        $this->class = 'jFormComponentMultipleChoice';
        $this->label = $label;
        $this->multipleChoiceArray = $multipleChoiceArray;

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function hasInstanceValues()
    {
        if ($this->multipleChoiceType == 'radio') {
            return is_array($this->value);
        } else {
            if (!empty($this->value)) {
                return is_array($this->value[0]);
            }
        }
        return false;
    }

    /**
    * MultipleChoice Specific Instance Handling for validation
    *
    */
    public function validateComponent()
    {
        $this->passedValidation = true;
        $this->errorMessageArray = [];

        if (is_array($this->value[0])) {
            foreach ($this->value as $value) {
                $this->errorMessageArray[] = $this->validate($value);
            }
        } else {
            $this->errorMessageArray = $this->validate($this->value);
        }
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        // Make sure you have an options array to manipulate
        if (!isset($options['options'])) {
            $options['options']  = [];
        }

        // Set the multiple choice type
        $options['options']['multipleChoiceType'] = $this->multipleChoiceType;

        return $options;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        // Generate the component div
        if (sizeof($this->multipleChoiceArray) > 1) {
            $div = parent::generateComponentDiv();
        } else {
            $div = parent::generateComponentDiv(false);
        }

        // Case
        // array(array('value' => 'option1', 'label' => 'Option 1', 'checked' => 'checked', 'tip' => 'This is a tip'))
        $multipleChoiceCount = 0;
        foreach ($this->multipleChoiceArray as $multipleChoice) {
            $multipleChoiceValue = $multipleChoice['value'] ?? '';
            $multipleChoiceLabel =  $multipleChoice['label'] ?? '';
            $multipleChoiceChecked =  $multipleChoice['checked'] ?? false;
            $multipleChoiceTip =  $multipleChoice['tip'] ?? '';
            $multipleChoiceDisabled =  $multipleChoice['disabled'] ?? '';
            $multipleChoiceInputHidden =  $multipleChoice['inputHidden'] ?? '';

            $multipleChoiceCount++;

            $div->insert($this->getMultipleChoiceWrapper($multipleChoiceValue, $multipleChoiceLabel, $multipleChoiceChecked, $multipleChoiceTip, $multipleChoiceDisabled, $multipleChoiceInputHidden, $multipleChoiceCount));
        }

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div, $this->id.'-div');

        return $div->__toString();
    }

    //function to insert tips onto the wrappers

    public function getMultipleChoiceWrapper($multipleChoiceValue, $multipleChoiceLabel, $multipleChoiceChecked, $multipleChoiceTip, $multipleChoiceDisabled, $multipleChoiceInputHidden, $multipleChoiceCount)
    {
        // Make a wrapper div for the input and label
        $multipleChoiceWrapperDiv = new JFormElement('div', [
            'id' => $this->id.'-choice'.$multipleChoiceCount.'-wrapper',
            'class' => $this->multipleChoiceClass.'Wrapper',
        ]);

        // Input tag
        $input = new JFormElement('input', [
            'type' => $this->multipleChoiceType,
            'id' => $this->id.'-choice'.$multipleChoiceCount,
            'name' => $this->name,
            'value' => $multipleChoiceValue,
            'class' => $this->multipleChoiceClass,
            'style' => 'display: inline;',
        ]);
        if ($multipleChoiceChecked == 'checked') {
            $input->setAttribute('checked', 'checked');
        }
        if ($multipleChoiceDisabled) {
            $input->setAttribute('disabled', 'disabled');
        }
        if ($multipleChoiceInputHidden) {
            $input->setAttribute('style', 'display: none;');
        }
        $multipleChoiceWrapperDiv->insert($input);

        // Multiple choice label
        $multipleChoiceLabelElement = new JFormElement('label', [
            'for' => $this->id.'-choice'.$multipleChoiceCount,
            'class' => $this->multipleChoiceLabelClass,
            'style' => 'display: inline;',
        ]);
        // Add an image to the label if there is a tip
        if (!empty($multipleChoiceTip) && $this->showMultipleChoiceTipIcons) {
            $multipleChoiceLabelElement->update($multipleChoiceLabel.' <span class="jFormComponentMultipleChoiceTipIcon">&nbsp;</span>');
        } else {
            $multipleChoiceLabelElement->update($multipleChoiceLabel);
        }
        // Add a required star if there is only one multiple choice option and it is required
        if (sizeof($this->multipleChoiceArray) == 1) {
            // Add the required star to the label
            if (in_array('required', $this->validationOptions)) {
                $labelRequiredStarSpan = new JFormElement('span', [
                    'class' => $this->labelRequiredStarClass
                ]);
                $labelRequiredStarSpan->update(' *');
                $multipleChoiceLabelElement->insert($labelRequiredStarSpan);
            }
        }
        $multipleChoiceWrapperDiv->insert($multipleChoiceLabelElement);

        // Multiple choice tip
        if (!empty($multipleChoiceTip)) {
            $multipleChoiceTipDiv = new JFormElement('div', [
                'id' => $this->id.'-'.$multipleChoiceValue.'-tip',
                'style' => 'display: none;',
                'class' => 'jFormComponentMultipleChoiceTip'
            ]);
            $multipleChoiceTipDiv->update($multipleChoiceTip);
            $multipleChoiceWrapperDiv->insert($multipleChoiceTipDiv);
        }

        return $multipleChoiceWrapperDiv;
    }


    // Validations
    public function required($options)
    {
        $errorMessageArray = ['Required.'];
        return  sizeof($options['value']) > 0 ? 'success' : $errorMessageArray;
    }
    public function minOptions($options)
    {
        $errorMessageArray = ['You must select more than '. $options['minOptions'] .' options'];
        return sizeof($options['value']) == 0 || sizeof($options['value']) > $options['minOptions'] ? 'success' : $errorMessageArray;
    }
    public function maxOptions($options)
    {
        $errorMessageArray = ['You may select up to '. $options['maxOptions'] .' options. You have selected '. sizeof($options['value']) . '.'];
        return sizeof($options['value']) == 0 || sizeof($options['value']) <= $options['maxOptions'] ? 'success' : $errorMessageArray;
    }
}




class JFormComponentName extends JFormComponent
{
    public $middleInitialHidden = false;
    public $emptyValues = null;
    public $showSublabels = true;

    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'jFormComponentName form-group';

        // Input options
        $this->initialValues = ['firstName' => '', 'middleInitial' => '', 'lastName' => ''];

        if ($this->emptyValues === true) {
            $this->emptyValues = ['firstName' => 'First Name', 'middleInitial' => 'M','lastName' => 'Last Name'];
        }
        //$this->mask = '';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function hasInstanceValues()
    {
        return is_array($this->value);
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        if (!empty($this->emptyValues)) {
            $options['options']['emptyValue'] = $this->emptyValues;
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
        $div = $this->generateComponentDiv();


        $firstNameDiv = new JFormElement('div', [
            'class' => 'firstNameDiv form-group',
        ]);
        // Add the first name input tag
        $firstName = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-firstName',
            'name' => $this->name.'-firstName',
            'class' => 'firstName singleLineText form-control',
            'placeholder' => 'First Name',
            'value' => $this->initialValues['firstName'],
        ]);
        $firstNameDiv->insert($firstName);

        // Add the middle initial input tag
        $middleInitialDiv = new JFormElement('div', [
            'class' => 'middleInitialDiv form-group',
        ]);
        $middleInitial = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-middleInitial',
            'name' => $this->name.'-middleInitial',
            'class' => 'middleInitial singleLineText form-control',
            'maxlength' => '1',
            'value' => ($this->initialValues['middleInitial'] ?? ''),
        ]);
        if ($this->middleInitialHidden) {
            $middleInitial->setAttribute('style', 'display: none;');
            $middleInitialDiv->setAttribute('style', 'display: none;');
        }
        $middleInitialDiv->insert($middleInitial);


        // Add the last name input tag
        $lastNameDiv = new JFormElement('div', [
            'class' => 'lastNameDiv form-group',
        ]);
        $lastName = new JFormElement('input', [
            'type' => 'text',
            'id' => $this->id.'-lastName',
            'name' => $this->name.'-lastName',
            'class' => 'lastName singleLineText form-control',
            'placeholder' => 'Last Name',
            'value' => $this->initialValues['lastName'],
        ]);
        $lastNameDiv->insert($lastName);

        if (!empty($this->emptyValues)) {
            $this->emptyValues = ['firstName' => 'First Name', 'middleInitial' => 'M','lastName' => 'Last Name'];
            foreach ($this->emptyValues as $key => $value) {
                if (!isset($this->initialValues[$key]) || $this->initialValues[$key] == '') {
                    if ($key == 'firstName') {
                        $firstName->setAttribute('value', $value);
                        $firstName->addClassName('defaultValue');
                    }
                    if ($key == 'middleInitial') {
                        $middleInitial->setAttribute('value', $value);
                        $middleInitial->addClassName('defaultValue');
                    }
                    if ($key == 'lastName') {
                        $lastName->setAttribute('value', $value);
                        $lastName->addClassName('defaultValue');
                    }
                }
            }
        }

        if ($this->showSublabels) {
            $firstNameDiv->insert('<div class="jFormComponentSublabel"><p>First Name</p></div>');
            $middleInitialDiv->insert('<div class="jFormComponentSublabel"><p>MI</p></div>');
            $lastNameDiv->insert('<div class="jFormComponentSublabel"><p>Last Name</p></div>');
        }

        $div->insert($firstNameDiv);
        $div->insert($middleInitialDiv);
        $div->insert($lastNameDiv);

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div);

        return $div->__toString();
    }

    public function required($options)
    {
        $errorMessageArray = [];
        if ($options['value']->firstName == '') {
            array_push($errorMessageArray, ['First name is required.']);
        }
        if ($options['value']->lastName == '') {
            array_push($errorMessageArray, ['Last name is required.']);
        }
        return sizeof($errorMessageArray) == 0 ? 'success' : $errorMessageArray;
    }
}




class JFormComponentSingleLineText extends JFormComponent
{
    public $sublabel;

    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'jFormComponentSingleLineText';
        $this->widthArray = ['shortest' => '2em', 'short' => '6em', 'mediumShort' => '9em', 'medium' => '12em', 'mediumLong' => '15em', 'long' => '18em', 'longest' => '24em'];

        // Input options
        $this->initialValue = '';
        $this->type = 'text'; // text, password, hidden
        $this->disabled = false;
        $this->readOnly = false;
        $this->maxLength = '';
        $this->width = '';
        $this->mask = '';
        $this->emptyValue = '';
        $this->placeholder = '';

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function hasInstanceValues()
    {
        return is_array($this->value);
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        // Make sure you have an options array to manipulate
        if (!isset($options['options'])) {
            $options['options']  = [];
        }

        // Mask
        if (!empty($this->mask)) {
            $options['options']['mask'] = $this->mask;
        }

        // Empty value
        if (!empty($this->emptyValue)) {
            $options['options']['emptyValue'] = $this->emptyValue;
        }

        // Clear the options key if there is nothing in it
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
        $div = $this->generateComponentDiv();

        // Add the input tag
        $input = new JFormElement('input', [
            'type' => $this->type,
            'id' => $this->id,
            'name' => $this->name,
        ]);
        if (!empty($this->width)) {
            if (array_key_exists($this->width, $this->widthArray)) {
                $input->setAttribute('style', 'width: '.$this->widthArray[$this->width].';');
            } else {
                $input->setAttribute('style', 'width: '.$this->width.';');
            }
        }
        if (isset($this->initialValue)) {
            $input->setAttribute('value', $this->initialValue);
        }
        if (!empty($this->maxLength)) {
            $input->setAttribute('maxlength', $this->maxLength);
        }
        if (!empty($this->placeholder)) {
            $input->setAttribute('placeholder', $this->placeholder);
        }
        if (!empty($this->mask)) {
            $this->formComponentMeta['options']['mask']= $this->mask;
        }
        if ($this->disabled) {
            $input->setAttribute('disabled', 'disabled');
        }
        if ($this->readOnly) {
            $input->setAttribute('readonly', 'readonly');
        }
        $input->addToAttribute('class', ' form-control');
        if ($this->enterSubmits) {
            $input->addToAttribute('class', ' jFormComponentEnterSubmits');
        }
        $div->insert('<div class="col-sm-8">'.$input.'</div>');

        if (!empty($this->sublabel)) {
            $div->insert('<div class="jFormComponentSublabel">'.$this->sublabel.'</div>');
        }

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div);

        return $div->__toString();
    }

    // Validations

    public function alpha($options)
    {
        $messageArray = ['Must only contain letters.'];
        return preg_match('/^[a-z_\s]+$/i', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function alphaDecimal($options)
    {
        $messageArray = ['Must only contain letters, numbers, or periods.'];
        return preg_match('/^\w+$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function alphaNumeric($options)
    {
        $messageArray = ['Must only contain letters or numbers.'];
        return preg_match('/^[a-z0-9_\s]+$/i', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function blank($options)
    {
        $messageArray = ['Must be blank.'];
        return mb_strlen(trim($options['value'])) == 0 ? 'success' : $messageArray;
    }

    public function canadianPostal($options)
    {
        $messageArray = ['Must be a valid Canadian postal code.'];
        return preg_match('/^[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function custom_regexp()
    {
        $messageArray = [$options['custom_regexp']['custom_message']];
        return preg_match($options['custom_regexp']['regexp'], $options['value']) ? 'success' : $messageArray;
    }

    public function date($options)
    {
        $messageArray = ['Must be a date in the mm/dd/yyyy format.'];
        return preg_match('/^(0?[1-9]|1[012])[\- \/.](0?[1-9]|[12][0-9]|3[01])[\- \/.](19|20)[0-9]{2}$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function dateTime($options)
    {
        $messageArray = ['Must be a date in the mm/dd/yyyy hh:mm:ss tt format. ss and tt are optional.'];
        return preg_match('/^(0?[1-9]|1[012])[\- \/.](0?[1-9]|[12][0-9]|3[01])[\- \/.](19|20)?[0-9]{2} [0-2]?\d:[0-5]\d(:[0-5]\d)?( ?(a|p)m)?$/i', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function decimal($options)
    {
        // Can be negative and have a decimal value
        // Do not accept commas in value as the DB does not accept them
        $messageArray = ['Must be a number without any commas. Decimal is optional.'];
        return preg_match('/^-?((\d+(\.\d+)?)|(\.\d+))$/', $options['value']) ? 'success' : $messageArray;
    }

    public function decimalNegative($options)
    {
        // Must be negative and have a decimal value
        $messageArray = ['Must be a negative number without any commas. Decimal is optional.'];
        //isDecimal = self.validations.decimal($options);
        return ($this->decimal($options) == 'success' && (floatval($options['value']) < 0)) ? 'success' : $messageArray;
    }

    public function decimalPositive($options)
    {
        // Must be positive and have a decimal value
        $messageArray = ['Must be a positive number without any commas. Decimal is optional.'];
        //isDecimal = self.validations.decimal($options);
        return ($this->decimal($options) == 'success' && (floatval($options['value']) > 0)) ? 'success' : $messageArray;
    }

    public function decimalZeroNegative($options)
    {
        // Must be negative and have a decimal value
        $messageArray = ['Must be zero or a negative number without any commas. Decimal is optional.'];
        //isDecimal = self.validations.decimal($options);
        return ($this->decimal($options) == 'success' && (floatval($options['value']) <= 0)) ? 'success' : $messageArray;
    }

    public function decimalZeroPositive($options)
    {
        // Must be positive and have a decimal value
        $messageArray = ['Must be zero or a positive number without any commas. Decimal is optional.'];
        //isDecimal = self.validations.decimal($options);
        return ($this->decimal($options) == 'success' && (floatval($options['value']) >= 0)) ? 'success' : $messageArray;
    }

    public function email($options)
    {
        $messageArray = ['Must be a valid e-mail address.'];
        return preg_match('/^[A-Z0-9._%-\+]+@(?:[A-Z0-9\-]+\.)+[A-Z]{2,4}$/i', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function integer($options)
    {
        $messageArray = ['Must be a whole number.'];
        return preg_match('/^-?\d+$/', $options['value']) ? 'success' : $messageArray;
    }

    public function integerNegative($options)
    {
        $messageArray = ['Must be a negative whole number.'];
        //isInteger = preg_match('/^-?\d+$/', $options['value']);
        return ($this->integer($options) && (intval($options['value'], 10) < 0)) ? 'success' : $messageArray;
    }

    public function integerPositive($options)
    {
        $messageArray = ['Must be a positive whole number.'];
        //isInteger = preg_match('/^-?\d+$/', $options['value']);
        return ($this->integer($options) && (intval($options['value'], 10) > 0)) ? 'success' : $messageArray;
    }

    public function integerZeroNegative($options)
    {
        $messageArray = ['Must be zero or a negative whole number.'];
        //isInteger = preg_match('/^-?\d+$/', $options['value']);
        return ($this->integer($options) && (intval($options['value'], 10) <= 0)) ? 'success' : $messageArray;
    }

    public function integerZeroPositive($options)
    {
        $messageArray = ['Must be zero or a positive whole number.'];
        //isInteger = preg_match('/^-?\d+$/', $options['value']);
        return ($this->integer($options) && (intval($options['value'], 10) >= 0)) ? 'success' : $messageArray;
    }

    public function isbn($options)
    {
        //Match an ISBN
        $errorMessageArray = ['Must be a valid ISBN and consist of either ten or thirteen characters.'];
        //For ISBN-10
        if (preg_match('/^(?=.{13}$)\d{1,5}([\- ])\d{1,7}\1\d{1,6}\1(\d|X)$/', $options['value'])) {
            $errorMessageArray = 'sucess';
        }
        if (preg_match('/^\d{9}(\d|X)$/', $options['value'])) {
            $errorMessageArray = 'sucess';
        }
        //For ISBN-13
        if (preg_match('/^(?=.{17}$)\d{3}([\- ])\d{1,5}\1\d{1,7}\1\d{1,6}\1(\d|X)$/' , $options['value'])) {
            $errorMessageArray = 'sucess';
        }
        if (preg_match('/^\d{3}[\- ]\d{9}(\d|X)$/', $options['value'])) {
            $errorMessageArray = 'sucess';
        }
        //ISBN-13 without starting delimiter (Not a valid ISBN but less strict validation was requested)
        if (preg_match('/^\d{12}(\d|X)$/', $options['value'])) {
            $errorMessageArray = 'sucess';
        }
        return $errorMessageArray;
    }

    public function length($options)
    {
        $messageArray = ['Must be exactly ' . $options['length'] .' characters long. Current value is '.mb_strlen($options['value']).' characters.'];
        return mb_strlen($options['value']) == $options['length'] || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function matches($options)
    {
        $componentToMatch = $this->parentJFormSection->parentJFormPage->jFormer->select($options['matches']);
        if ($componentToMatch && $componentToMatch->value == $options['value']) {
            return 'success';
        } else {
            return ['Does not match.'];
        }
    }

    public function maxLength($options)
    {
        $messageArray = ['Must be less than ' . $options['maxLength'] . ' characters long. Current value is '.mb_strlen($options['value']).' characters.'];
        return mb_strlen($options['value']) <= $options['maxLength'] || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function maxFloat($options)
    {
        $messageArray = ['Must be numeric and cannot have more than ' . $options['maxFloat'] . ' decimal place(s).'];
        return preg_match('^-?((\\d+(\\.\\d{0,'+ $options['maxFloat'] +'})?)|(\\.\\d{0,' . $options['maxFloat'] . '}))$', $options['value'])  ? 'success' : $messageArray;
    }

    public function maxValue($options)
    {
        $messageArray = ['Must be numeric with a maximum value of ' . $options['maxValue'] . '.'];
        return $options['maxValue'] >= $options['value'] ? 'success' : $messageArray;
    }

    public function minLength($options)
    {
        $messageArray = ['Must be at least ' . $options['minLength'] . ' characters long. Current value is '.mb_strlen($options['value']).' characters.'];
        return mb_strlen($options['value']) >= $options['minLength'] || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function minValue($options)
    {
        $messageArray = ['Must be numeric with a minimum value of ' . $options['minValue'] . '.'];
        return $options['minValue'] <= $options['value'] ? 'success' : $messageArray;
    }

    public function money($options)
    {
        $messageArray = ['Must be a valid dollar value.'];
        return preg_match('/^\$?[1-9][0-9]{0,2}(,?[0-9]{3})*(\.[0-9]{2})?$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function moneyNegative($options)
    {
        $messageArray = ['Must be a valid negative dollar value.'];
        return preg_match('/^((-?\$)|(\$-?)|(-))?((\d+(\.\d{2})?)|(\.\d{2}))$/', $options['value'], $matches) && $matches[0] < 0 || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function moneyPositive($options)
    {
        $messageArray = ['Must be a valid positive dollar value.'];
        return preg_match('/^((-?\$)|(\$-?)|(-))?((\d+(\.\d{2})?)|(\.\d{2}))$/', $options['value'], $matches) && $matches[0] > 0 || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function moneyZeroNegative($options)
    {
        $messageArray = ['Must be zero or a valid negative dollar value.'];
        return preg_match('/^((-?\$)|(\$-?)|(-))?((\d+(\.\d{2})?)|(\.\d{2}))$/', $options['value'], $matches) && $matches[0] <= 0 ? 'success' : $messageArray;
    }

    public function moneyZeroPositive($options)
    {
        $messageArray = ['Must be zero or a valid positive dollar value.'];
        return preg_match('/^((-?\$)|(\$-?)|(-))?((\d+(\.\d{2})?)|(\.\d{2}))$/', $options['value'], $matches) && $matches[0]= 0 || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function password($options)
    {
        $messageArray = ['Must be between 4 and 32 characters.'];
        return preg_match('/^.{4,32}$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function phone($options)
    {
        //$messageArray = array('Must be a 10 digit phone number.');
        //return preg_match('/^(1[\-. ]?)?\(?[0-9]{3}\)?[\-. ]?[0-9]{3}[\-. ]?[0-9]{4}$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray ;
        $messageArray = ['Must be a US or International Phone Number'];
        return preg_match('/^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function postalZip($options)
    {
        $messageArray = ['Must be a valid United States zip code, Canadian postal code, or United Kingdom postal code.'];
        $postal = false;
        if (this.zip($options) == 'success') {
            $postal = true;
        }
        if (this.canadianPostal($options) == 'success') {
            $postal = true;
        }
        if (this.ukPostal($options) == 'success') {
            $postal = true;
        }
        return postal ? 'success' : $messageArray;
    }

    public function required($options)
    {
        $messageArray = ['Required.'];
        //return empty($options['value']) ? 'success' : $messageArray; // Break validation on purpose
        return !empty($options['value']) || $options['value'] == '0' ? 'success' : $messageArray;
    }

    public function serverSide($options)
    {
        // Handle empty values
        if (empty($options['value'])) {
            return 'success';
        }

        $messageArray = [];

        // Perform the server side check with a scrape
        $serverSideResponse = getUrlContent($options['url'].'&value='.$options['value']);

        // Can't read the URL
        if ($serverSideResponse['status'] != 'success') {
            $messageArray[] = 'This component could not be validated.';
        }
        // Read the URL
        else {
            $serverSideResponse = json_decode($serverSideResponse['response']);
            if ($serverSideResponse->status == 'success') {
                $messageArray == 'success';
            } else {
                $messageArray = $serverSideResponse->response;
            }
        }

        return $messageArray;

        function getUrlContent($url, $postData = null)
        {
            // Handle objects and arrays
            $curlHandler = curl_init();
            curl_setopt($curlHandler, CURLOPT_URL, $url);
            curl_setopt($curlHandler, CURLOPT_FAILONERROR, 1);
            curl_setopt($curlHandler, CURLOPT_TIMEOUT, 20); // Time out in seconds
            curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
            if ($postData != null) {
                foreach ($postData as $key => &$value) {
                    if (is_object($value) || is_array($value)) {
                        $value = json_encode($value);
                    }
                }
                curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $postData);
            }
            $request = curl_exec($curlHandler);

            if (!$request) {
                $response = ['status' => 'failure', 'response' => 'CURL error ' . curl_errno($curlHandler) . ': ' . curl_error($curlHandler)];
            } else {
                $response = ['status' => 'success', 'response' => $request];
            }

            return $response;
        }
    }

    public function ssn($options)
    {
        $messageArray = ['Must be a valid United States social security number.'];
        return preg_match('/^\d{3}-?\d{2}-?\d{4}$/i', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function teenager($options)
    {
        $messageArray = ['Must be at least 13 years old.'];
        if ($this->date($options) == 'success') {
            $oldEnough = strtotime($options['value']) - strtotime('-13 years');
        } else {
            return false;
        }
        return $oldEnough >= 0  ? 'success' : $messageArray;
    }

    public function time($options)
    {
        $messageArray = ['Must be a time in the hh:mm:ss tt format. ss and tt are optional.'];
        return preg_match('/^[0-2]?\d:[0-5]\d(:[0-5]\d)?( ?(a|p)m)?$/i', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function ukPostal($options)
    {
        $messageArray = ['Must be a valid United Kingdom postal code.'];
        return preg_match('/^[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function url($options)
    {
        $messageArray = ['Must be a valid Internet address.'];
        return preg_match('/^((ht|f)tp(s)?:\/\/|www\.)?([\-A-Z0-9.]+)(\.[a-zA-Z]{2,4})(\/[\-A-Z0-9+&@#\/%=~_|!:,.;]*)?(\?[\-A-Z0-9+&@#\/%=~_|!:,.;]*)?$/i', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function username($options)
    {
        $messageArray = ['Must use 4 to 32 characters and start with a letter.'];
        return preg_match('/^[A-Za-z](?=[A-Za-z0-9_.]{3,31}$)[a-zA-Z0-9_]*\.?[a-zA-Z0-9_]*$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }

    public function zip($options)
    {
        $messageArray = ['Must be a valid United States zip code.'];
        return preg_match('/^[0-9]{5}(?:-[0-9]{4})?$/', $options['value']) || $options['value'] == '' ? 'success' : $messageArray;
    }
}



class JFormComponentTextArea extends JFormComponent
{
    /*
     * Constructor
     */
    public function __construct($id, $label, $optionArray = [])
    {
        // Class variables
        $this->id = $id;
        $this->name = $this->id;
        $this->label = $label;
        $this->class = 'jFormComponentTextArea';
        $this->inputClass = 'textArea';
        $this->widthArray = ['shortest' => '5em', 'short' => '10em', 'medium' => '20em', 'long' => '30em', 'longest' => '40em'];
        $this->heightArray = ['short' => '6em', 'medium' => '12em', 'tall' => '18em'];

        // Input options
        $this->initialValue = '';
        $this->disabled = false;
        $this->readOnly = false;
        $this->wrap = ''; // hard, off
        $this->width = '';
        $this->height = '';
        $this->style = '';
        $this->allowTabbing = false;
        $this->emptyValue = '';
        $this->autoGrow = false;

        // Initialize the abstract FormComponent object
        $this->initialize($optionArray);
    }

    public function hasInstanceValues()
    {
        return is_array($this->value);
    }

    public function getOptions()
    {
        $options = parent::getOptions();

        // Tabbing
        if ($this->allowTabbing) {
            $options['options']['allowTabbing'] = true;
        }

        // Empty value
        if (!empty($this->emptyValue)) {
            $options['options']['emptyValue'] = $this->emptyValue;
        }

        // Auto grow
        if ($this->autoGrow) {
            $options['options']['autoGrow'] = $this->autoGrow;
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
        $div = $this->generateComponentDiv();

        // Add the input tag
        $textArea = new JFormElement('textarea', [
            'id' => $this->id,
            'name' => $this->name,
            'class' => $this->inputClass,
        ]);
        if (!empty($this->width)) {
            if (array_key_exists($this->width, $this->widthArray)) {
                $textArea->setAttribute('style', 'width: '.$this->widthArray[$this->width].';');
            } else {
                $textArea->setAttribute('style', 'width: '.$this->width.';');
            }
        }
        if (!empty($this->height)) {
            if (array_key_exists($this->height, $this->heightArray)) {
                $textArea->addToAttribute('style', 'height: '.$this->heightArray[$this->height].';');
            } else {
                $textArea->addToAttribute('style', 'height: '.$this->height.';');
            }
        }
        if (!empty($this->style)) {
            $textArea->addToAttribute('style', $this->style);
        }
        if ($this->disabled) {
            $textArea->setAttribute('disabled', 'disabled');
        }
        if ($this->readOnly) {
            $textArea->setAttribute('readonly', 'readonly');
        }
        if ($this->wrap) {
            $textArea->setAttribute('wrap', $this->wrap);
        }
        if (isset($this->initialValue)) {
            $textArea->update($this->initialValue);
        }
        $div->insert($textArea);

        // Add any description (optional)
        $div = $this->insertComponentDescription($div);

        // Add a tip (optional)
        $div = $this->insertComponentTip($div);

        return $div->__toString();
    }
}
