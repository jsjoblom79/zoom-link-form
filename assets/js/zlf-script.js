jQuery(document).ready(function($) {
    $('#zlf-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'zlf_submit_form',
            nonce: zlfAjax.nonce,
            email: $('#zlf-email').val(),
            captcha_response: grecaptcha.getResponse()
        };
        
        $.ajax({
            url: zlfAjax.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#zlf-form').hide();
                    $('#zlf-response').html('<p>' + response.data.message + '</p><p><a href="' + response.data.zoom_link + '" target="_blank">Join Zoom Meeting</a></p>').show();
                } else {
                    $('#zlf-response').html('<p>Error: ' + response.data.message + '</p>').show();
                }
            },
            error: function() {
                $('#zlf-response').html('<p>An error occurred. Please try again.</p>').show();
            }
        });
    });
});