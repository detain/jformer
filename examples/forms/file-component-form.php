<?php
// Include the jFormer PHP (use an good path in your code)
if (file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
} elseif (file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$fileComponentForm = new JFormer('fileComponentForm', [
    'title' => '<h1>File Component</h1>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$fileComponentForm->addJFormComponentArray([
    new JFormComponentFile('file1', 'File:', [
        'tip' => '<p>This is a tip on a file component.</p>',
    ]),
]);

// Set the function for a successful form submission
function onSubmit($formValues)
{
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$fileComponentForm->processRequest();
