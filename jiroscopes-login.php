<?php 

// https://code.tutsplus.com/tutorials/build-a-custom-wordpress-user-flow-part-3-password-reset--cms-23811

/** 
* Login side of plugin
*
*/

add_action( 'login_form_login', 'redirect_to_custom_login');

// Register a new shortcode: [jiroscopes_login]
add_shortcode( 'jiroscopes_login', 'jiroscopes_login_shortcode' );

// Shortcode callback
function jiroscopes_login_shortcode() {
    ob_start();
    jiroscopes_login_form();

    ob_flush();
    return;
}

function  redirect_to_custom_login() {
    if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
        $redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : null;
     
        // if ( is_user_logged_in() ) {
        //     $this->redirect_logged_in_user( $redirect_to );
        //     exit;
        // }
 
        // The rest are redirected to the login page
        // $login_url = home_url( 'login' );
        // if ( ! empty( $redirect_to ) ) {
        //     $login_url = add_query_arg( 'redirect_to', $redirect_to, $login_url );
        // }
 
        // wp_redirect( $login_url );
        exit;
    }
}

function jiroscopes_login_form() {
    

    $nonce = wp_create_nonce('jiroscopes_register' . get_the_ID());


    echo '
        <form id="form" method="post" class="my-16 flex justify-center">
            <div class="w-2/3 lg:w-1/2">
                <div class="my-4">
                    <label class="block font-Merriweather text-SemiGrey" for="username">Username:</label>
                    <input class="block w-full py-2 px-2 border-SemiGrey border-2 rounded-md my-1" type="text" name="username" value="'. (isset($_POST['username']) ? $_POST['username'] : null) .'" required>
                </div>
                <div class="my-4">
                    <label class="block font-Merriweather text-SemiGrey" for="password">Password:</label>
                    <input class="block w-full py-2 px-2 border-SemiGrey border-2 rounded-md my-1" type="password" name="password" value="'. (isset($_POST['password']) ? $_POST['password'] : null) .'" required>
                </div>
                <div class="my-4 flex justify-center">
                    <button data-nonce="' . $nonce . '" data-post_id="' . get_the_id() . '" name="submit" id="submit" class="bg-transparent hover:bg-DarkGrey text-DarkGrey hover:text-LightGrey px-4 py-2 border-SemiGrey border-2 rounded-md transition-all duration-300" type="submit">Let me in!</button>
                </div>
            </div>
        </form>
    ';
}

function jiroscopes_login_form_validation($username, $password, $post_id) {

    global $form_errors;

    if (!username_exists($username)) {
        $form_errors[] = 'Invalid login';
    }

    if (check_ajax_referer('jiroscopes_register' . $post_id, 'nonce', false) == false) {
        $form_errors[] = 'Invalid form submission. POST ID:' . $post_id;
    }

    // If any errors exist
    if (!empty($form_errors)) {
        return false;
    }

    return true;
}

function jiroscopes_login() {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        global $form_errors;

        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $post_id  = isset($_POST['post_id']) ? $_POST['post_id'] : '';

        if (!jiroscopes_login_form_validation($username, $password, $post_id)) {
            wp_send_json_error(json_encode($form_errors));
            return;
        }
        

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

        return;
    }
}

