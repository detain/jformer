<?php
// Include the jFormer PHP (use an good path in your code)
if (file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
} elseif (file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$htmlComponentForm = new JFormer('htmlComponentForm', [
    'title' => '<h1>HTML Component</h1>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$htmlComponentForm->addJFormComponentArray([
    new JFormComponentHtml('<p>This is an HTML component. It records no input and has no validation. Use it to insert HTML inbetween your components.</p>'),
]);

// Set the function for a successful form submission
function onSubmit($formValues)
{
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$htmlComponentForm->processRequest();
