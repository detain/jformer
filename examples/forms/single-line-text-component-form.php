<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$singleLineTextComponentForm = new BFormer('singleLineTextComponentForm', array(
    'title' => '<h1>Single Line Text Component</h1>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$singleLineTextComponentForm->addBFormComponentArray(array(
    new BFormComponentSingleLineText('singleLineText1', 'Single line text:', array(
        'tip' => '<p>This is a tip on a single line text component.</p>',
    )),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$singleLineTextComponentForm->processRequest();
?>