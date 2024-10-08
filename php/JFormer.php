<?php

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
