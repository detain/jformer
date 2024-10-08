<?php
// Include the jFormer PHP (use an good path in your code)
if (file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
} elseif (file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$textAreaComponentForm = new JFormer('textAreaComponentForm', [
    'title' => '<h1>Text Area Component</h1>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$textAreaComponentForm->addJFormComponentArray([
    new JFormComponentTextArea('textArea1', 'Text area:', [
        'tip' => '<p>This is a tip on a text area component.</p>',
    ]),
]);

// Set the function for a successful form submission
function onSubmit($formValues)
{
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$textAreaComponentForm->processRequest();
