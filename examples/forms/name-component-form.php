<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$nameComponentForm = new BFormer('nameComponentForm', array(
    'title' => '<h1>Name Component</h1>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$nameComponentForm->addBFormComponentArray(array(
    new BFormComponentName('name1', 'Name:', array(
        'tip' => '<p>This is a tip on a name component.</p>',
    )),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$nameComponentForm->processRequest();
?>