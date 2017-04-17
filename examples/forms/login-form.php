<?php
// Include the bFormer PHP (use an good path in your code)
if(file_exists('../php/BFormer.php')) {
    require_once('../php/BFormer.php');
}
else if(file_exists('../../php/BFormer.php')) {
    require_once('../../php/BFormer.php');
}

// Create the form
$loginForm = new BFormer('loginForm', array(
    'title' => '<h1>Login</h1>',
    'submitButtonText' => 'Login',
    'requiredText' => ' (required)'
));

// Add components to the section
$loginForm->addBFormComponentArray(array(
    new BFormComponentSingleLineText('username', 'Username:', array(
        'validationOptions' => array('required', 'username'),
        'tip' => '<p>The <a href="/">demo</a> login is <b>admin</b>.</p>',
        'persistentTip' => true
    )),
    new BFormComponentSingleLineText('password', 'Password:', array(
        'type' => 'password',
        'validationOptions' => array('required', 'password'),
        'tip' => '<p>Password is 12345</p>',
    )),
    new BFormComponentMultipleChoice('rememberMe', '',
        array(
            array('label' => 'Remember me'),
        ),
        array(
            'tip' => '<p>If a cookie is set you can have this checked by default.</p>',
        )
    ),
));

// Set the function for a successful form submission
function onSubmit($formValues) {
    // Server side checks go here
    if($formValues->username == 'admin' && $formValues->password == '12345') {
        // If they want to be remembered
        if(!empty($formValues->rememberMe)) {
            // Let them know they successfully logged in
            $response = array('successPageHtml' => '
                <h2>Login Successful</h2>
                <p>We will keep you logged in on this computer.</p>
            ');
            // Alternatively, you could also do a redirect
            //return array('redirect' => 'http://www.bformer.com');
        }
        // If they do not want to be remembered
        else {
            $response = array('successPageHtml' => '
                <h2>Login Successful</h2>
                <p>We will not keep you logged in on this computer.</p>
            ');
        }
    }
    // If login fails, give a failure notice
    else {
        $response = array(
            'failureNoticeHtml' => 'Invalid username or password.',
            'failureJs' => "$('#password').val('').focus();",  // You can pass a JavaScript callback to run if it fails
        );
    }

    return $response;
}

// Process any request to the form
$loginForm->processRequest();
?>