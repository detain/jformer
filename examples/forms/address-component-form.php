<?php
// Include the jFormer PHP (use an good path in your code)
if (file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
} elseif (file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$addressComponentForm = new JFormer('addressComponentForm', [
    'title' => '<h1>Address Component</h1>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$addressComponentForm->addJFormComponentArray([
    new JFormComponentAddress('address1', 'Standard address:', [
        'tip' => '<p>This is a tip on an address component.</p>',
    ]),
    new JFormComponentAddress('address2', 'Address without second line:', [
        'addressLine2Hidden' => true,
    ]),
    new JFormComponentAddress('address3', 'Address for United States only:', [
        'unitedStatesOnly' => true,
    ]),
    new JFormComponentAddress('address3', 'Address for United States only:', [
        'unitedStatesOnly' => true,
    ]),
    new JFormComponentAddress('address4', 'Address with a selected country:', [
        'selectedCountry' => 'US',
    ]),
]);

// Set the function for a successful form submission
function onSubmit($formValues)
{
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$addressComponentForm->processRequest();
