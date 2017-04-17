<?php

require_once('BFormElement.php');
require_once('BFormPage.php');
require_once('BFormSection.php');
require_once('BFormComponent.php');
require_once('BFormComponentSingleLineText.php');
require_once('BFormComponentMultipleChoice.php');
require_once('BFormComponentDropDown.php');
require_once('BFormComponentTextArea.php');
require_once('BFormComponentDate.php');
require_once('BFormComponentFile.php');
require_once('BFormComponentName.php');
require_once('BFormComponentHidden.php');
require_once('BFormComponentAddress.php');
require_once('BFormComponentCreditCard.php');
require_once('BFormComponentLikert.php');
require_once('BFormComponentHtml.php');

if (!function_exists('is_empty'))
{
	function is_empty($string)
	{
		$string = trim($string);
		if (!is_numeric($string))
		{
		   return empty($string);
		}
		return FALSE;
	}
}


class BFormer {

	// General settings
	public $id;
	public $class = 'bFormer container-fluid';
	public $action;
	public $form_type = 'horizontal';
	public $style;
	public $bFormPageArray = array();
	public $bFormerId;
	public $onSubmitFunctionServerSide = 'onSubmit';
	public $disableAnalytics = false;
	public $setupPageScroller = true;
	public $data;
	// Title, description, and submission button
	public $title = '';
	public $titleClass = 'bFormerTitle';
	public $description = '';
	public $descriptionClass = 'bFormerDescription';
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
	public $pageNavigator = array();
	// Splash page
	public $splashPageEnabled = false;
	public $splashPage = array();
	// Animations
	public $animationOptions = null;
	// Custom script execution before form submission
	public $onSubmitStartClientSide = '';
	public $onSubmitFinishClientSide = '';
	// Essential class variables
	public $status = array('status' => 'processing', 'response' => 'Form initialized.');
	// Validation
	public $validationResponse = array();
	public $validationPassed = null;
	// Required Text
	public $requiredText = ' *';

	/**
	 * Constructor
	 */
	function __construct($id, $optionArray = array(), $bFormPageArray = array()) {
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
		} else if ($this->pageNavigator == true) {
			$this->pageNavigator = array(
				'position' => 'top'
			);
		}

		// Set defaults for the splash page
		if (!empty($this->splashPage)) {
			$this->splashPageEnabled = true;
		}

		// Add the pages from the constructor
		foreach ($bFormPageArray as $bFormPage) {
			$this->addBFormPage($bFormPage);
		}

		return $this;
	}

	function addBFormPage($bFormPage) {
		$bFormPage->bFormer = $this;
		$this->bFormPageArray[$bFormPage->id] = $bFormPage;
		return $this;
	}

	function addBFormPages($bFormPages) {
		if (is_array($bFormPages)) {
			foreach ($bFormPages as $bFormPage) {
				$bFormPage->bFormer = $this;
				$this->bFormPageArray[$bFormPage->id] = $bFormPage;
			}
		}
		$bFormPage->bFormer = $this;
		$this->bFormPageArray[$bFormPage->id] = $bFormPage;
		return $this;
	}

	// Convenience method, no need to create a page or section to get components on the form
	function addBFormComponent($bFormComponent) {
		// Create an anonymous page if necessary
		if (empty($this->bFormPageArray)) {
			$this->addBFormPage(new BFormPage($this->id . '_page1', array('anonymous' => true)));
		}

		// Get the first page in the bFormPageArray
		$currentBFormPage = current($this->bFormPageArray);

		// Get the last section in the page
		$lastBFormSection = end($currentBFormPage->bFormSectionArray);

		// If the last section exists and is anonymous, add the component to it
		if (!empty($lastBFormSection) && $lastBFormSection->anonymous) {
			$lastBFormSection->addBFormComponent($bFormComponent);
		}
		// If the last section in the page does not exist or is not anonymous, add a new anonymous section and add the component to it
		else {
			// Create an anonymous section
			$anonymousSection = new BFormSection($currentBFormPage->id . '_section' . (sizeof($currentBFormPage->bFormSectionArray) + 1), array('anonymous' => true));

			// Add the anonymous section to the page
			$currentBFormPage->addBFormSection($anonymousSection->addBFormComponent($bFormComponent));
		}

		return $this;
	}

	function addBFormComponentArray($bFormComponentArray) {
		foreach ($bFormComponentArray as $bFormComponent) {
			$this->addBFormComponent($bFormComponent);
		}
		return $this;
	}

	// Convenience method, no need to create a to get a section on the form
	function addBFormSection($bFormSection) {
		// Create an anonymous page if necessary
		if (empty($this->bFormPageArray)) {
			$this->addBFormPage(new BFormPage($this->id . '_page1', array('anonymous' => true)));
		}

		// Get the first page in the bFormPageArray
		$currentBFormPage = current($this->bFormPageArray);

		// Add the section to the first page
		$currentBFormPage->addBFormSection($bFormSection);

		return $this;
	}

	function setStatus($status, $response) {
		$this->status = array('status' => $status, 'response' => $response);
		return $this->status;
	}

	function resetStatus() {
		$this->status = array('status' => 'processing', 'response' => 'Form status reset.');
		return $this->status;
	}

	function getStatus() {
		return $this->status;
	}

	function validate() {
		// Update the form status
		$this->setStatus('processing', 'Validating component values.');

		// Clear the validation response
		$this->validationResponse = array();

		// Validate each page
		foreach ($this->bFormPageArray as $bFormPage) {
			$this->validationResponse[$bFormPage->id] = $bFormPage->validate();
		}
		// Walk through all of the pages to see if there are any errors
		$this->validationPassed = true;

		foreach ($this->validationResponse as $bFormPageKey => $bFormPage) {
			foreach ($bFormPage as $bFormSectionKey => $bFormSection) {
				// If there are section instances
				if ($bFormSection != null && array_key_exists(0, $bFormSection) && is_array($bFormSection[0])) {
					foreach ($bFormSection as $bFormSectionInstanceIndex => $bFormSectionInstance) {
						foreach ($bFormSectionInstance as $bFormComponentKey => $bFormComponentErrorMessageArray) {
							// If there are component instances
							if ($bFormComponentErrorMessageArray != null && array_key_exists(0, $bFormComponentErrorMessageArray) && is_array($bFormComponentErrorMessageArray[0])) {
								foreach ($bFormComponentErrorMessageArray as $bFormComponentInstanceErrorMessageArray) {
									// If the first value is not empty, the component did not pass validation
									if (!empty($bFormComponentInstanceErrorMessageArray[0]) || sizeof($bFormComponentInstanceErrorMessageArray) > 1) {
										$this->validationPassed = false;
									}
								}
							} else {
								if (!empty($bFormComponentErrorMessageArray)) {
									$this->validationPassed = false;
								}
							}
						}
					}
				}
				// No section instances
				else {
					foreach ($bFormSection as $bFormComponentErrorMessageArray) {
						// Component instances
						if ($bFormComponentErrorMessageArray != null && array_key_exists(0, $bFormComponentErrorMessageArray) && is_array($bFormComponentErrorMessageArray[0])) {
							foreach ($bFormComponentErrorMessageArray as $bFormComponentInstanceErrorMessageArray) {
								// If the first value is not empty, the component did not pass validation
								if (!empty($bFormComponentInstanceErrorMessageArray[0]) || sizeof($bFormComponentInstanceErrorMessageArray) > 1) {
									$this->validationPassed = false;
								}
							}
						} else {
							if (!empty($bFormComponentErrorMessageArray)) {
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

	function getData() {
		$this->data = array();

		foreach ($this->bFormPageArray as $bFormPageKey => $bFormPage) {
			if (!$bFormPage->anonymous) {
				$this->data[$bFormPageKey] = $bFormPage->getData();
			} else {
				foreach ($bFormPage->bFormSectionArray as $bFormSectionKey => $bFormSection) {
					if (!$bFormSection->anonymous) {
						$this->data[$bFormSectionKey] = $bFormSection->getData();
					} else {
						foreach ($bFormSection->bFormComponentArray as $bFormComponentKey => $bFormComponent) {
							if (get_class($bFormComponent) != 'BFormComponentHtml') { // Don't include HTML components
								$this->data[$bFormComponentKey] = $bFormComponent->getValue();
							}
						}
					}
				}
			}
		}
		return json_decode(json_encode($this->data));
	}

	function updateRequiredText($requiredText) {
		foreach($this->bFormPageArray as $bFormPage) {
			$bFormPage->updateRequiredText($requiredText);
		}
	}

	function setInitialValues($formValues) {
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

	function setData($data, $fileArray = array()) {
		// Get the form data as an object, handle apache auto-add slashes on post requests
		$bFormerData = json_decode(urldecode($data));
		if (!is_object($bFormerData)) {
			$bFormerData = json_decode(urldecode(stripslashes($data)));
		}

		// Clear all of the component values
		$this->clearData();

		//print_r($bFormerData); exit();
		//print_r($fileArray);
		// Update the form status
		$this->setStatus('processing', 'Setting component values.');

		// Assign all of the received JSON values to the form
		foreach ($bFormerData as $bFormPageKey => $bFormPageData) {
			$this->bFormPageArray[$bFormPageKey]->setData($bFormPageData);
		}

		// Handle files
		if (!empty($fileArray)) {
			foreach ($fileArray as $bFormComponentId => $fileDataArray) {
				preg_match('/(-section([0-9])+)?(-instance([0-9])+)?:([A-Za-z0-9_-]+):([A-Za-z0-9_-]+)/', $bFormComponentId, $fileIdInfo);

				$bFormComponentId = str_replace($fileIdInfo[0], '', $bFormComponentId);
				$bFormPageId = $fileIdInfo[5];
				$bFormSectionId = $fileIdInfo[6];

				// Inside section instances
				if ($fileIdInfo[1] != null || ($fileIdInfo[1] == null && array_key_exists(0, $this->bFormPageArray[$bFormPageId]->bFormSectionArray[$bFormSectionId]->bFormComponentArray))) {
					// section instance
					// set the instance index
					if ($fileIdInfo[1] != null) {
						$bFormSectionInstanceIndex = $fileIdInfo[2] - 1;
					} else {
						// prime instance
						$bFormSectionInstanceIndex = 0;
					}
					// check to see if there is a component instance
					if ($fileIdInfo[3] != null || ($fileIdInfo[3] == null && is_array($this->bFormPageArray[$bFormPageId]->bFormSectionArray[$bFormSectionId]->bFormComponentArray[$bFormSectionInstanceIndex][$bFormComponentId]->value))) {
						// set the component instance index inside of a  section instance
						if ($fileIdInfo[3] == null) {
							$bFormComponentInstanceIndex = 0;
						} else {
							$bFormComponentInstanceIndex = $fileIdInfo[4] - 1;
						}
						// set the value with a section and a component instance
						$this->bFormPageArray[$bFormPageId]->bFormSectionArray[$bFormSectionId]->bFormComponentArray[$bFormSectionInstanceIndex][$bFormComponentId]->value[$bFormComponentInstanceIndex] = $fileDataArray;
					} else {
						// set the value with a section instance
						$this->bFormPageArray[$bFormPageId]->bFormSectionArray[$bFormSectionId]->bFormComponentArray[$bFormSectionInstanceIndex][$bFormComponentId]->value = $fileDataArray;
					}
				}

				// Not section instances
				else {
					// has component instances
					if ($fileIdInfo[3] != null || ($fileIdInfo[3] == null && is_array($this->bFormPageArray[$bFormPageId]->bFormSectionArray[$bFormSectionId]->bFormComponentArray[$bFormComponentId]->value))) {
						// set component  instance index
						if ($fileIdInfo[3] == null) {
							$bFormComponentInstanceIndex = 0;
						} else {
							$bFormComponentInstanceIndex = $fileIdInfo[4] - 1;
						}
						$this->bFormPageArray[$bFormPageId]->bFormSectionArray[$bFormSectionId]->bFormComponentArray[$bFormComponentId]->value[$bFormComponentInstanceIndex] = $fileDataArray;
					} else {
						// no instances
						$this->bFormPageArray[$bFormPageId]->bFormSectionArray[$bFormSectionId]->bFormComponentArray[$bFormComponentId]->value = $fileDataArray;
					}
				}
			}
		}

		return $this;
	}

	function clearData() {
		foreach ($this->bFormPageArray as $bFormPage) {
			$bFormPage->clearData();
		}
		$this->data = null;
	}

	function clearAllComponentValues() {
		// Clear all of the components in the form
		foreach ($this->bFormPageArray as $bFormPage) {
			foreach ($bFormPage->bFormSectionArray as $bFormSection) {
				foreach ($bFormSection->bFormComponentArray as $bFormComponent) {
					$bFormComponent->value = null;
				}
			}
		}
	}

	function select($id) {
		foreach ($this->bFormPageArray as $bFormPageId => &$bFormPage) {
			if ($id === $bFormPageId) {
				return $bFormPage;
			}
			foreach ($bFormPage->bFormSectionArray as $bFormSectionId => &$bFormSection) {
				if ($id === $bFormSectionId) {
					return $bFormSection;
				}
				foreach ($bFormSection->bFormComponentArray as $bFormComponentId => &$bFormComponent) {
					if (is_array($bFormComponent)) {
						foreach ($bFormComponent as $sectionInstanceComponentId => &$sectionInstanceComponent) {
							if ($id === $sectionInstanceComponentId) {
								return $sectionInstanceComponent;
							}
						}
					}
					if ($id === $bFormComponentId) {
						return $bFormComponent;
					}
				}
			}
		}
		return false;
	}

	function remove($id) {
		foreach ($this->bFormPageArray as $bFormPageId => &$bFormPage) {
			if ($id === $bFormPageId) {
				$this->bFormPageArray[$bFormPageId] = null;
				array_filter($this->bFormPageArray);
				return true;
			}
			foreach ($bFormPage->bFormSectionArray as $bFormSectionId => &$bFormSection) {
				if ($id === $bFormSectionId) {
					$bFormPage->bFormSectionArray[$bFormSectionId] = null;
					array_filter($bFormPage->bFormSectionArray);
					return true;
				}
				foreach ($bFormSection->bFormComponentArray as $bFormComponentId => &$bFormComponent) {
					if ($id === $bFormComponentId) {
						$bFormSection->bFormComponentArray[$bFormComponentId] = null;
						array_filter($bFormSection->bFormComponentArray);
						return true;
					}
				}
			}
		}
		return false;
	}

	function processRequest($silent = false) {
		// Are they trying to post a file that is too large?
		if (isset($_SERVER['CONTENT_LENGTH']) && empty($_POST)) {
			$this->setStatus('success', array('failureNoticeHtml' => 'Your request (' . round($_SERVER['CONTENT_LENGTH'] / 1024 / 1024, 1) . 'M) was too large for the server to handle. ' . ini_get('post_max_size') . ' is the maximum request size.'));
			echo '
				<script type="text/javascript" language="javascript">
					parent.' . $this->id . 'Object.handleFormSubmissionResponse(' . json_encode($this->getStatus()) . ');
				</script>
			';
			exit();
		}

		// Are they trying to post something to the form?
		if (isset($_POST['bFormer']) && $this->id == $_POST['bFormerId'] || isset($_POST['bFormerTask'])) {
			// Process the form, get the form state, or display the form
			if (isset($_POST['bFormer'])) {
				//echo json_encode($_POST);
				$onSubmitErrorMessageArray = array();

				// Set the form components and validate the form
				$this->setData($_POST['bFormer'], $_FILES);

				//print_r($this->getData());
				// Run validation
				$this->validate();
				if (!$this->validationPassed) {
					$this->setStatus('failure', array('validationFailed' => $this->validationResponse));
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
						$this->setStatus('failure', array('failureHtml' => $onSubmitErrorMessageArray));
					}
				}
				if($this->useIframeTarget){
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
			else if (isset($_POST['bFormerTask']) && $_POST['bFormerTask'] == 'getFormStatus') {
				$onSubmitResponse = $this->getStatus();
				echo json_encode($onSubmitResponse);
				$this->resetStatus();
				exit();
			}
		}
		// If they aren't trying to post something to the form
		else if (!$silent) {
			$this->outputHtml();
		}
	}

	function getOptions() {
		$options = array();
		$options['options'] = array();
		$options['bFormPages'] = array();

		// Get all of the pages
		foreach ($this->bFormPageArray as $bFormPage) {
			$options['bFormPages'][$bFormPage->id] = $bFormPage->getOptions();
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

	function outputHtml() {
		echo $this->getHtml();
	}

	function __toString() {
		$element = $this->getHtml();
		return $element->__toString();
	}

	function getHtml() {
		$this->updateRequiredText($this->requiredText);
		// Create the form
		$target = $this->useIframeTarget ? $this->id . '-iframe' : '';
		$bFormElement = new BFormElement('form', array(
					'id' => $this->id,
					'target' => $target,
					'enctype' => 'multipart/form-data',
					'method' => 'post',
					'class' => $this->class . ' form-'.$this->form_type,
					'action' => $this->action,
				));
		if (!empty($this->onMouseOver)) {
			$formBFormElement->attr('onmouseover', $this->onMouseOver);
		}

		if (!empty($this->onMouseOut)) {
			$formBFormElement->attr('onmouseout', $this->onMouseOut);
		}

		// Set the style
		if (!empty($this->style)) {
			$bFormElement->addToAttribute('style', $this->style);
		}

		// Global messages
		if ($this->alertsEnabled) {
			$bFormerAlertWrapperDiv = new BFormElement('div', array(
						'class' => 'bFormerAlertWrapper',
						'style' => 'display: none;',
					));
			$alertDiv = new BFormElement('div', array(
						'class' => 'bFormerAlert',
					));
			$bFormerAlertWrapperDiv->insert($alertDiv);
			$bFormElement->insert($bFormerAlertWrapperDiv);
		}

		// If a splash is enabled
		if ($this->splashPageEnabled) {
			// Create a splash page div
			$splashPageDiv = new BFormElement('div', array(
						'id' => $this->id . '-splash-page',
						'class' => 'bFormerSplashPage bFormPage',
					));

			// Set defaults if they aren't set
			if (!isset($this->splashPage['content'])) {
				$this->splashPage['content'] = '';
			}
			if (!isset($this->splashPage['splashButtonText'])) {
				$this->splashPage['splashButtonText'] = 'Begin';
			}

			$splashPageDiv->insert('<div class="bFormerSplashPageContent">' . $this->splashPage['content'] . '</div>');

			// Create a splash button if there is no custom button ID
			if (!isset($this->splashPage['customButtonId'])) {
				$splashLi = new BFormElement('li', array('class' => 'splashLi'));
				$splashButton = new BFormElement('button', array('class' => 'splashButton'));
				$splashButton->update($this->splashPage['splashButtonText']);
				$splashLi->insert($splashButton);
			}
		}

		// Add a title to the form
		if (!empty($this->title)) {
			$title = new BFormElement('div', array(
						'class' => $this->titleClass
					));
			$title->update($this->title);
			$bFormElement->insert($title);
		}

		// Add a description to the form
		if (!empty($this->description)) {
			$description = new BFormElement('div', array(
						'class' => $this->descriptionClass
					));
			$description->update($this->description);
			$bFormElement->insert($description);
		}

		// Add the page navigator if enabled
		if ($this->pageNavigatorEnabled) {
			$pageNavigatorDiv = new BFormElement('div', array(
						'class' => 'bFormPageNavigator',
					));
			if (isset($this->pageNavigator['position']) && $this->pageNavigator['position'] == 'right') {
				$pageNavigatorDiv->addToAttribute('class', ' bFormPageNavigatorRight');
			} else {
				$pageNavigatorDiv->addToAttribute('class', ' bFormPageNavigatorTop');
			}

			$pageNavigatorUl = new BFormElement('ul', array(
					));

			$bFormPageArrayCount = 0;
			foreach ($this->bFormPageArray as $bFormPageKey => $bFormPage) {
				$bFormPageArrayCount++;

				$pageNavigatorLabel = new BFormElement('li', array(
							'id' => 'navigatePage' . $bFormPageArrayCount,
							'class' => 'bFormPageNavigatorLink',
						));

				// If the label is numeric
				if (isset($this->pageNavigator['label']) && $this->pageNavigator['label'] == 'numeric') {
					$pageNavigatorLabelText = 'Page ' . $bFormPageArrayCount;
				} else {
					// Add a link prefix if there is a title
					if (!empty($bFormPage->title)) {
						$pageNavigatorLabelText = '<span class="bFormNavigatorLinkPrefix">' . $bFormPageArrayCount . '</span> ' . strip_tags($bFormPage->title);
					} else {
						$pageNavigatorLabelText = 'Page ' . $bFormPageArrayCount;
					}
				}
				$pageNavigatorLabel->update($pageNavigatorLabelText);

				if ($bFormPageArrayCount != 1) {
					$pageNavigatorLabel->addToAttribute('class', ' bFormPageNavigatorLinkLocked');
				} else {
					$pageNavigatorLabel->addToAttribute('class', ' bFormPageNavigatorLinkUnlocked bFormPageNavigatorLinkActive');
				}

				$pageNavigatorUl->insert($pageNavigatorLabel);
			}

			// Add the page navigator ul to the div
			$pageNavigatorDiv->insert($pageNavigatorUl);

			$bFormElement->insert($pageNavigatorDiv);
		}

		// Add the bFormerControl UL
		$bFormerControlUl = new BFormElement('ul', array(
					'class' => 'bFormerControl col-xs-offset-4 col-xs-8',
			'style' => 'list-style-type: none;',
				));

		// Create the cancel button
		if ($this->cancelButton) {
			$cancelButtonLi = new BFormElement('li', array('class' => 'cancelLi'));
			$cancelButton = new BFormElement('button', array('class' => $this->cancelButtonClass));
			$cancelButton->update($this->cancelButtonText);

			if (!empty($this->cancelButtonOnClick)) {
				$cancelButton->attr('onclick', $this->cancelButtonOnClick);
			}

			$cancelButtonLi->append($cancelButton);
		}

		// Create the previous button
		$previousButtonLi = new BFormElement('li', array('class' => 'previousLi', 'style' => 'display: none;'));
		$previousButton = new BFormElement('button', array('class' => 'previousButton'));
		$previousButton->update('Previous');
		$previousButtonLi->insert($previousButton);

		// Create the next button
		$nextButtonLi = new BFormElement('li', array('class' => 'nextLi'));
		$nextButton = new BFormElement('button', array('class' => 'btn btn-primary nextButton'));
		$nextButton->update($this->submitButtonText);
		// Don't show the next button
		if ($this->splashPageEnabled) {
			$nextButtonLi->setAttribute('style', 'display: none;');
		}
		$nextButtonLi->insert($nextButton);

		// Add a splash page button if it exists
		if (isset($splashLi)) {
			$bFormerControlUl->insert($splashLi);
		}

		// Add the previous and next buttons
		$bFormerControlUl->insert($previousButtonLi);

		if ($this->cancelButton && $this->cancelButtonLiBeforeNextButtonLi) {
			echo 'one';
			$bFormerControlUl->insert($cancelButtonLi);
			$bFormerControlUl->insert($nextButtonLi);
		} else if ($this->cancelButton) {
			echo 'two';
			$bFormerControlUl->insert($nextButtonLi);
			$bFormerControlUl->insert($cancelButtonLi);
		} else {
			$bFormerControlUl->insert($nextButtonLi);
		}

		// Create the page wrapper and scrollers
		$bFormPageWrapper = new BFormElement('div', array('class' => 'bFormPageWrapper'));
		$bFormPageScroller = new BFormElement('div', array('class' => 'bFormPageScroller'));

		// Add a splash page if it exists
		if (isset($splashPageDiv)) {
			$bFormPageScroller->insert($splashPageDiv);
		}

		// Add the form pages to the form
		$bFormPageCount = 0;
		foreach ($this->bFormPageArray as $bFormPage) {
			// Hide everything but the first page
			if ($bFormPageCount != 0 || ($bFormPageCount == 0 && ($this->splashPageEnabled))) {
				$bFormPage->style .= 'display: none;';
			}

			$bFormPageScroller->insert($bFormPage);
			$bFormPageCount++;
		}

		// Page wrapper wrapper
		$pageWrapperContainer = new BFormElement('div', array('class' => 'bFormWrapperContainer'));

		// Insert the page wrapper and the bFormerControl UL to the form
		$bFormElement->insert($pageWrapperContainer->insert($bFormPageWrapper->insert($bFormPageScroller) . '<div class="form-group" style="padding-top: 10px;">'.$bFormerControlUl.'</div>'));

		// Create a script tag to initialize bFormer JavaScript
		$script = new BFormElement('script', array(
					'type' => 'text/javascript',
					'language' => 'javascript'
				));

		// Update the script tag
		$script->update('$(document).ready(function () { ' . $this->id . 'Object = new BFormer(\'' . $this->id . '\', ' . json_encode($this->getOptions()) . '); });');
		$bFormElement->insert($script);

		// Add a hidden iframe to handle the form posts
		$iframe = new BFormElement('iframe', array(
					'id' => $this->id . '-iframe',
					'name' => $this->id . '-iframe',
					'class' => 'bFormerIFrame',
					'frameborder' => 0,
					'height' => '0px;',
					'src' => (defined('URLDIR') ? URLDIR : '') . '/js/bformer-detain-git/php/BFormer.php?iframe=true',
					//'src' => '/empty.html',
						//'src' => str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__).'?iframe=true',
				));

		if ($this->debugMode) {
			$iframe->addToAttribute('style', 'display:block;');
		}

		$bFormElement->insert($iframe);


		// After control
		if (!empty($this->afterControl)) {
			$subSubmitInstructions = new BFormElement('div', array('class' => 'bFormerAfterControl'));
			$subSubmitInstructions->update($this->afterControl);
			$bFormElement->insert($subSubmitInstructions);
		}

		return $bFormElement;
	}

}

// Handle any requests that come to this file
if (isset($_GET['iframe'])) {
	echo '';
}
?>
