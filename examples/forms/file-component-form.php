<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$fileComponentForm = new BFormer('fileComponentForm', array(
    'title' => '<h1>File Component</h1>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$fileComponentForm->addBFormComponentArray(array(
    new BFormComponentFile('file1', 'File:', array(
        'tip' => '<p>This is a tip on a file component.</p>',
    )),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$fileComponentForm->processRequest();
?>