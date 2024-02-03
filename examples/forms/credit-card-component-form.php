<?php
// Include the jFormer PHP (use an good path in your code)
if (file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
} elseif (file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$creditCardComponentForm = new JFormer('creditCardComponentForm', [
    'title' => '<h1>Credit Card Component</h1>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$creditCardComponentForm->addJFormComponentArray([
    new JFormComponentCreditCard('creditCard1', 'Credit card:', [
        'tip' => '<p>This is a tip on a credit card component.</p>',
    ]),
]);

// Set the function for a successful form submission
function onSubmit($formValues)
{
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$creditCardComponentForm->processRequest();
