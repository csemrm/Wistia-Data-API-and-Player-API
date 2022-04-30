<?php
/*
  Plugin Name: Wistia Player
  Plugin URI: #/
  Description: wistia-player test
  Author: Mostafizur Rahman
  Version: 1.6
  Author URI: http://www.csemrm.com/
 */

defined('ABSPATH') || exit;

define('wistia_ajax_plugin_path', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/');

function wistia_player() {
    ?>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <script charset="ISO-8859-1" src="//fast.wistia.com/assets/external/E-v1.js" async></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <div class="wistia_embed wistia_async_rix2lsqgb5" style="width:640px;height:360px;">&nbsp;</div>

    <div id="dialog-form" title="User Login">
        <?php wistia_get_data_ajax_settings(); ?>

    </div>

    <button id="toggle_play" style="display: none"> Me </button>
    <script>
        $(function () {
            var dialog;
            var is_user_logged_in = <?php echo is_user_logged_in() ? 1 : 0; ?>;

            console.log("is_user_logged_in " + is_user_logged_in);
            window._wq = window._wq || [];

            _wq.push({id: "rix2lsqgb5",
                onReady: function (video) {


                    video.bind('play', function () {
                        console.log("video played", video.name());
                        video.time(0);
                        return video.unbind;
                    });
                    video.bind('secondchange', function (s) {
                        if (s === 60 && !is_user_logged_in) {
                            video.pause();
                            console.log("is_user_logged_in " + is_user_logged_in);
                            $("#login").show();

                        }
                    });
                    $("#toggle_play").click(function () {
                        if (video.state() === "playing") {
                            video.pause();
                        } else {
                            video.play();
                        }
                    });

                }});
        });
    </script>
    <?php
}

add_action('wistia_player', 'wistia_player', 10, 3);

function wistia_auth_user_login($user_login, $password, $login) {
    $info = array();
    $info['user_login'] = $user_login;
    $info['user_password'] = $password;
    $info['remember'] = true;

    $user_signon = wp_signon($info, false);
    if (is_wp_error($user_signon)) {
        echo json_encode(array('loggedin' => false, 'message' => __('Wrong username or password.')));
    } else {
        wp_set_current_user($user_signon->ID);
        echo json_encode(array('loggedin' => true, 'message' => __($login . ' successful, redirecting...')));
    }

    die();
}

function wistia_ajax_login() {
    // First check the nonce, if it fails the function will break
    check_ajax_referer('ajax-login-nonce', 'security');

    // Nonce is checked, get the POST data and sign user on
    // Call wistia_auth_user_login
    wistia_auth_user_login($_POST['username'], $_POST['password'], 'Login');

    die();
}

add_action('wp_ajax_nopriv_ajaxlogin', 'wistia_ajax_login');

function wistia_get_data_ajax_settings() {
    ?>
    <form style="margin-top:0px;" id="login" class="ajax-auth" action="javascript:void(0);" method="post">
        <h1><?php echo esc_attr(__('Login')); ?></h1>
        <p class="status"></p>
        <?php wp_nonce_field('ajax-login-nonce', 'security'); ?>
        <label for="username"><?php echo esc_attr(__('Username')); ?></label>
        <input id="username" type="text" class="required" name="username" palceholder>
        <label for="password"><?php echo esc_attr(__('Password')); ?></label>
        <input id="password" type="password" class="required" name="password">
        <a class="text-link" href="<?php echo wp_lostpassword_url(); ?>"><?php echo esc_attr(__('Lost Password?')); ?></a>
        <input class="submit_button" type="submit" value="LOGIN">
        <a class="close" href="javascript:void(0);"><img class="cancel" src="<?php echo wistia_ajax_plugin_path . 'img/cancel.png'; ?>" /></a>
    </form>
    <?php
}

function wistia_ajax_auth_init() {
    wp_register_style('wistia-style', wistia_ajax_plugin_path . 'css/wistia.css');
    wp_enqueue_style('wistia-style');

    wp_register_script('validate-script', wistia_ajax_plugin_path . 'js/jquery.validate.js', array('jquery'));
    wp_enqueue_script('validate-script');

    wp_register_script('ajax-auth-script', wistia_ajax_plugin_path . 'js/ajax-auth-script.js', array('jquery'));
    wp_enqueue_script('ajax-auth-script');

    wp_localize_script('ajax-auth-script', 'ajax_auth_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Sending user info, please wait...')
    ));

    add_action('wp_ajax_nopriv_ajaxlogin', 'wistia_ajax_login');
}

// Execute the action only if the user isn't logged in
add_action('init', 'wistia_ajax_auth_init');
