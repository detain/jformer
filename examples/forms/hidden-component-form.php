<?php
// Include the jFormer PHP (use an good path in your code)
if (file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
} elseif (file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$hiddenComponentForm = new JFormer('hiddenComponentForm', [
    'title' => '<h1>Hidden Component</h1>',
    'description' => '<p>There is a hidden component in here.</p>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$hiddenComponentForm->addJFormComponentArray([
    new JFormComponentHidden('hidden1', 'Hey there!'),
]);

// Set the function for a successful form submission
function onSubmit($formValues)
{
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$hiddenComponentForm->processRequest();
