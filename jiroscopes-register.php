<?php

/*
Plugin Name: Jiroscopes-Register
Plugin URI: 
description: A register form for jiroscopes.com
Version: 1.1
Author: Steven Popick
Author URI: midtwenty.com
License:
*/

// Register a new shortcode: [cr_custom_registration]
add_shortcode( 'jiroscopes_register', 'jiroscopes_register_shortcode' );

// Shortcode callback
function jiroscopes_register_shortcode() {
    ob_start();
    jiroscopes_register_form();

    ob_flush();
    return;
}

// Outputs the form
function jiroscopes_register_form() {

    $nonce = wp_create_nonce('jiroscopes_register' . get_the_ID());

    echo '
        <form method="post" id="form" class="my-16 flex justify-center">
            <div class="w-2/3 lg:w-1/2">
                <div class="my-4">
                    <label class="block font-Merriweather text-SemiGrey" for="username">Username:</label>
                    <input class="block w-full py-2 px-2 border-SemiGrey border-2 rounded-md my-1" type="text" name="username" value="'. (isset($_POST['username']) ? $_POST['username'] : null) .'" required>
                </div>
                <div class="my-4">
                    <label class="block font-Merriweather text-SemiGrey" for="email">Email:</label>
                    <input id="email" class="block w-full py-2 px-2 border-SemiGrey border-2 rounded-md my-1" type="email" name="email" value="'. (isset($_POST['email']) ? $_POST['email'] : null) .'" required>
                </div>
                <div class="my-4">
                    <label class="block font-Merriweather text-SemiGrey" for="password">Password:</label>
                    <input class="block w-full py-2 px-2 border-SemiGrey border-2 rounded-md my-1" type="password" name="password" value="'. (isset($_POST['password']) ? $_POST['password'] : null) .'" required>
                </div>
                <div class="my-4">
                    <label class="block font-Merriweather text-SemiGrey" for="confirm_password">Confirm Password:</label>
                    <input class="block w-full py-2 px-2 border-SemiGrey border-2 rounded-md my-1" type="password" name="confirm_password" value="'. (isset($_POST['confirm_password']) ? $_POST['confirm_password'] : null) .'" required>
                </div>
                <div class="my-4 flex justify-center">
                    <button data-nonce="' . $nonce . '" data-post_id="' . get_the_id() . '" name="submit" id="submit" class="bg-transparent hover:bg-DarkGrey text-DarkGrey hover:text-LightGrey px-4 py-2 border-SemiGrey border-2 rounded-md transition-all duration-300" type="submit">Lets Go!</button>
                </div>
            </div>
        </form>
    ';

}

/**
* Form handling
*/

add_action( 'wp_enqueue_scripts', 'jiroscopes_load_scripts' );

add_action( 'wp_ajax_jiroscopes_register', 'jiroscopes_register');
add_action( 'wp_ajax_nopriv_jiroscopes_register', 'jiroscopes_register');

add_action( 'wp_ajax_jiroscopes_login', 'jiroscopes_login');
add_action( 'wp_ajax_nopriv_jiroscopes_login', 'jiroscopes_login');

// Load the JS
function jiroscopes_load_scripts() {
    wp_enqueue_script( 'form', plugin_dir_url( __FILE__ ) . 'js/form.js', array( 'jquery' ), null, true );

    // set variables for script
    wp_localize_script('form', 'settings', [
        'ajaxurl'    => admin_url( 'admin-ajax.php' ),
        'error'      => __( 'Sorry, something went wrong. Please try again', 'jiroscopesregister' )
    ]);
}

$form_errors = [];

function jiroscopes_register() {
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        global $form_errors;

        $username       = isset($_POST['username']) ? $_POST['username'] : '';
        $email          = isset($_POST['email']) ? $_POST['email'] : '';
        $password       = isset($_POST['password']) ? $_POST['password'] : '';
        $post_id          = isset($_POST['post_id']) ? $_POST['post_id'] : '';
        $confirm_pass   = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';


        if (!jiroscopes_register_form_validation($username, $email, $password, $confirm_pass)) {
            wp_send_json_error(json_encode($form_errors));
            return;
        }

        // Everything has been validated by this point https://developer.wordpress.org/reference/functions/wp_create_user/
        wp_create_user($username, $password, $email);
        
        $creds = [ 
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        ];

        $user = wp_signon( $creds, false );
 
        if ( is_wp_error( $user ) ) {
            wp_send_json_error($user->get_error_message());
            return;
        }

        wp_send_json_success();
        // $_POST['redirect_home'] = 'true';

        return;
    }
}

function jiroscopes_register_form_validation($username, $email, $password, $confirm_password, $post_id) {

    global $form_errors;

    if (strlen($username) < 6)  {
        $form_errors[] = 'Username must be at least 6 characters';
    }

    if (!validate_username($username)) {
        $form_errors[] = 'Invalid username';
    }

    if (username_exists($username)) {
        $form_errors[] = 'Username already taken';
    }

    if (!is_email($email)) {
        $form_errors[] = 'Invalid email';
    }

    if (email_exists($email)) {
        $form_errors[] = 'Email already in use';
    }

    if ($password != $confirm_password)  {
        $form_errors[] = 'Passwords do not match';
    }
    
    if (strlen($password) < 8)  {
        $form_errors[] = 'Password must be at least 8 characters';
    }

    // Verify Nonce https://developer.wordpress.org/themes/theme-security/using-nonces/
    if (check_ajax_referer('jiroscopes_register' . $post_id, 'nonce', false) == false) {
        $form_errors[] = 'Invalid form submission';
    }

    // If any errors exist
    if (!empty($form_errors)) {
        return false;
    }

    return true;
}

// Login Part

require('jiroscopes-login.php');
require('jiroscopes-reset.php');