<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$htmlComponentForm = new BFormer('htmlComponentForm', array(
    'title' => '<h1>HTML Component</h1>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$htmlComponentForm->addBFormComponentArray(array(
    new BFormComponentHtml('<p>This is an HTML component. It records no input and has no validation. Use it to insert HTML inbetween your components.</p>'),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$htmlComponentForm->processRequest();
?>