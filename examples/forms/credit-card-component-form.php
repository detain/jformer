<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$creditCardComponentForm = new BFormer('creditCardComponentForm', array(
    'title' => '<h1>Credit Card Component</h1>',
    'submitButtonText' => 'Test',
));

// Add components to the form
$creditCardComponentForm->addBFormComponentArray(array(
    new BFormComponentCreditCard('creditCard1', 'Credit card:', array(
        'tip' => '<p>This is a tip on a credit card component.</p>',
    )),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return array('failureNoticeHtml' => json_encode($formValues));
}

// Process any request to the form
$creditCardComponentForm->processRequest();
?>