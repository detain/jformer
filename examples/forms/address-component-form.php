<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$addressComponentForm = new BFormer('addressComponentForm', array(
    'title' => '<h1>Address Component</h1>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$addressComponentForm->addBFormComponentArray(array(
    new BFormComponentAddress('address1', 'Standard address:', array(
        'tip' => '<p>This is a tip on an address component.</p>',
    )),
    new BFormComponentAddress('address2', 'Address without second line:', array(
        'addressLine2Hidden' => true,
    )),
    new BFormComponentAddress('address3', 'Address for United States only:', array(
        'unitedStatesOnly' => true,
    )),
    new BFormComponentAddress('address3', 'Address for United States only:', array(
        'unitedStatesOnly' => true,
    )),
    new BFormComponentAddress('address4', 'Address with a selected country:', array(
        'selectedCountry' => 'US',
    )),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$addressComponentForm->processRequest();
?>