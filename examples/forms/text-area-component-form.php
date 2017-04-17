<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$textAreaComponentForm = new BFormer('textAreaComponentForm', array(
    'title' => '<h1>Text Area Component</h1>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$textAreaComponentForm->addBFormComponentArray(array(
    new BFormComponentTextArea('textArea1', 'Text area:', array(
        'tip' => '<p>This is a tip on a text area component.</p>',
    )),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$textAreaComponentForm->processRequest();
?>