jQuery(document).ready(function ($) {

    // Perform AJAX login/register on form submit
    $('.submit_button').on('click', function (e) {
        $('p.status').show().text(ajax_auth_object.loadingmessage);
        action = 'ajaxlogin';
        username = $('#username').val();
        password = $('#password').val();
        email = '';
        security = $('#security').val();

        ctrl = $(this);
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajax_auth_object.ajaxurl,
            data: {
                'action': action,
                'username': username,
                'password': password,
                'email': email,
                'security': security
            },
            success: function (data) {
                $('p.status').text(data.message);
                if (data.loggedin == true) {
                    $("#toggle_play").trigger('click');
                    $('form#login').fadeOut(500);
                }
            }
        });
        e.preventDefault();
    });
    $(document).on('click', '.close, .cancel', function () {
        $('form#login').fadeOut(500);
        return false;
    });
});