<?php
// Include the jFormer PHP (use an good path in your code)
if (file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
} elseif (file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$dateComponentForm = new JFormer('dateComponentForm', [
    'title' => '<h1>Date Component</h1>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$dateComponentForm->addJFormComponentArray([
    new JFormComponentDate('date1', 'Date:', [
        'tip' => '<p>This is a tip on a date component.</p>',
    ]),
]);

// Set the function for a successful form submission
function onSubmit($formValues)
{
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$dateComponentForm->processRequest();
