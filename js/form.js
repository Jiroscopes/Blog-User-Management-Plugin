( function( $ ) {
    $(document).ready(() => {

        $('#submit').on('click', (e) => {

            e.preventDefault();

            $(this).html('...');
            $(this).prop('disabled', 'true');
            
            // Delete the errors already showing
            $('#error').remove();

            // Load data from form into array
            let values = {};
            $.each($('#form').serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });


            // Data sent to backend depends on page. Email is only on the register page
            let data = {};
            if ($('#email').length) {
                data = {
                    'action'           : 'jiroscopes_register',
                    'username'         : values['username'],
                    'email'            : values['email'],
                    'nonce'            : $('#submit').data('nonce'),
                    'post_id'          : $('#submit').data('post_id'),
                    'password'         : values['password'],
                    'confirm_password' : values['confirm_password']
                };
            } else {
                data = {
                    'action' : 'jiroscopes_login',
                    'username'         : values['username'],
                    'password'         : values['password'],
                    'nonce'            : $('#submit').data('nonce'),
                    'post_id'          : $('#submit').data('post_id'),
                };

            }

            $.post( settings.ajaxurl, data, function( response ) {               
                if (response.success == true) {
                    console.log('Success', response); 
                    window.location.replace("http://jiroscopes.com");
                } else {
                    let respData = JSON.parse(response.data);
                    let html = '<div id="error" class="w-2/3 lg:w-1/2 mx-auto mt-16">';

                    $.each(respData, (key, val) => {
                        html += '<h1 class="font-Merriweather text-Errors block">' +  '&#9679 ' + val + '</h1>';
                    });

                    html += '</div>';

                    $('#form').before(html);
                }
            } );
        })
    
    })
})( jQuery );

// <h1 class="font-Merriweather text-Errors inline"> ERR </h1>