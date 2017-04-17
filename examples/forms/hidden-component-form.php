<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$hiddenComponentForm = new BFormer('hiddenComponentForm', array(
    'title' => '<h1>Hidden Component</h1>',
    'description' => '<p>There is a hidden component in here.</p>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$hiddenComponentForm->addBFormComponentArray(array(
    new BFormComponentHidden('hidden1', 'Hey there!'),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$hiddenComponentForm->processRequest();
?>