<?php
// Include the jFormer PHP (use an good path in your code)
if(file_exists('../php/JFormer.php')) {
    require_once('../php/JFormer.php');
}
else if(file_exists('../../php/JFormer.php')) {
    require_once('../../php/JFormer.php');
}

// Create the form
$dropDownComponentForm = new JFormer('dropDownComponentForm', [
    'title' => '<h1>Drop Down Component</h1>',
    'submitButtonText' => 'Test',
]);

// Add components to the form
$dropDownComponentForm->addJFormComponentArray([
    new JFormComponentDropDown('dropDown1', 'Drop down:',
        [
            ['label' => 'Choice 1', 'value' => '1'],
            ['label' => 'Choice 2', 'value' => '2'],
            ['label' => 'Choice 3', 'value' => '3'],
            ['label' => 'Choice 4', 'value' => '4'],
            ['label' => 'Choice 5', 'value' => '5'],
        ],
        [
            'tip' => '<p>This is a tip on a drop down component.</p>',
        ]
    ),
]);

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Return a simple debug response
    return ['failureNoticeHtml' => json_encode($formValues)];
}

// Process any request to the form
$dropDownComponentForm->processRequest();
?>