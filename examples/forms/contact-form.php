<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$contactForm = new BFormer('contactForm', array(
    'submitButtonText' => 'Send Message',
    'title' => '<h1>Contact Us</h1>',
));

// Add components to the form
$contactForm->addBFormComponentArray(array(
    new BFormComponentName('name', 'Name:', array(
        'validationOptions' => array('required'),
        'tip' => '<p>Please enter your full name.</p>'
    )),
    new BFormComponentSingleLineText('email', 'E-mail address:', array(
        'validationOptions' => array('required', 'email'),
    )),
    new BFormComponentSingleLineText('subject', 'Subject:', array(
        'validationOptions' => array('required'),
    )),
    new BFormComponentTextArea('message', 'Message:', array(
        'validationOptions' => array('required'),
    )),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    
    // Concatenate the name
    if(!empty($formValues->name->middleInitial)) {
        $name = $formValues->name->firstName . ' ' . $formValues->name->middleInitial . ' ' . $formValues->name->lastName;
    }
    else {
        $name = $formValues->name->firstName . ' ' . $formValues->name->lastName;
    }

    // Prepare the variables for sending the mail
    $toAddress = 'noreply@bformer.com';
    $fromAddress = $formValues->email;
    $fromName = $name;
    $subject = $formValues->subject.' from '.$fromName;
    $message = $formValues->message;

    // Use the PHP mail function
    $mail = mail($toAddress, $subject, $message, 'From: '.$fromAddress."\r\n".'Reply-To: '.$fromAddress."\r\n".'X-Mailer: PHP/'.phpversion());

    // Send the message
    if($mail) {
        $response['successPageHtml'] = '
            <h1>Thanks for Contacting Us</h1>
            <p>Your message has been successfully sent.</p>
        ';
    }
    else {
        $response['failureNoticeHtml'] = '
            There was a problem sending your message.
        ';
    }

    return $response;
}

// Process any request to the form
$contactForm->processRequest();
?>