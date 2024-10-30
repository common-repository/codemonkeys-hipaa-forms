<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 11/29/2017
 * Time: 12:36 PM
 * Updated: 10/03/2022 by Dan
 */

//* ENQUEUE ADMIN SCRIPTS AND STYLES
function cm_hipaa_enqueue_admin_scripts() {
    // ENQUEUE GOOGLE FONTS
    $gFonts = array(
        "Roboto+Condensed:300,300i,400,400i,700,700i",
        "Roboto:100,100i,200,200i,300,300i,400,400i,500,500i,700,700i,900,900i"
    );

    $gf_args = array(
        'family' => urlencode(implode("|",$gFonts)),
        'subset' => 'latin,latin-ext'
    );

    // ONLY ENQUEUE STYLES WHEN ON HIPAA FORMS ADMIN PAGE
    if ( isset($_GET['page']) ) {
        global $pagenow;
        if( in_array( $pagenow, array('admin.php') ) && ( $_GET['page'] == 'hipaa-forms.php' ) ) {
            wp_enqueue_style( 'google_fonts', add_query_arg($gf_args, "//fonts.googleapis.com/css"), array(), null);

            // ENQUEUE GOOGLE MATERIAL.IO ICONS
            wp_enqueue_style( 'materialIcons', 'https://fonts.googleapis.com/icon?family=Material+Icons' );

            // ENQUEUE GRAVITY CSS
            wp_enqueue_style( 'cmHipaaGravityAdminBasicStyle', plugin_dir_url(__FILE__) . '/css/gravity-basic.min.css' );
            wp_enqueue_style( 'cmHipaaGravityAdminCustomStyle', plugin_dir_url(__FILE__) . '/css/gravity-admin.css' );
            wp_enqueue_style( 'cmHipaaGravityAdminPrintStyle', plugin_dir_url(__FILE__) . '/css/print.css' );

            // ENQUEUE MAIN PLUGIN ADMIN CSS
            wp_enqueue_style( 'cmHipaaAdminStyle', plugin_dir_url(__FILE__) . '/css/admin-style.css' );

            // ENQUEUE SCRIPT
            wp_enqueue_script( 'jquery-form' );
            wp_enqueue_script( 'cmHipaaAdminBuggyFill', plugin_dir_url(__FILE__) . 'js/viewport-units-buggyfill.js', array('jquery'), '3.0.5', true );
            wp_enqueue_script( 'cmHipaaAdminBuggyFillHack', plugin_dir_url(__FILE__) . 'js/viewport-units-buggyfill.hacks.js', array('jquery'), '3.0.5', true );
            wp_enqueue_script( 'cmHipaaAdminScript', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), '3.0.5', true );
            wp_enqueue_script( 'cm-hipaa-signature', plugin_dir_url(__FILE__) . 'js/jSignature/jSignature.min.noconflict.js', array('jquery'), '3.0.5', true);
            wp_enqueue_script( 'cm-hipaa-jquery-print', plugin_dir_url(__FILE__) . 'js/printThis.js', array('jquery'), '3.0.5', true);
        };
    };

    // PASS PHP DATA TO SCRIPT FILE
    global $post;
    wp_localize_script('cmHipaaAdminScript', 'hipaaScript', array(
        'pluginUrl' => plugin_dir_url(__FILE__),
        'siteUrl' =>  get_site_url(),
        'contentUrl' => WP_CONTENT_URL,
        'nonce' => wp_create_nonce('cm-hipaa-admin-nonce'),
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));

    // LOCALIZE CUSTOM JS FILE TO USE WITH AJAX
//    wp_localize_script( 'cmHipaaAdminScript', 'ajax', array(
//        'ajax_url' => admin_url( 'admin-ajax.php' )
//    ));
}
add_action( 'admin_enqueue_scripts', 'cm_hipaa_enqueue_admin_scripts' );

//* ENQUEUE FRONT END AJAX JAVASCRIPT
function enqueue_cm_hipaa_scripts() {
    // ENQUEUE GOOGLE MATERIAL.IO ICONS
    wp_enqueue_style( 'materialIcons', 'https://fonts.googleapis.com/icon?family=Material+Icons' );

    // ENQUEUE MAIN PLUGIN CSS
    wp_enqueue_style( 'cmHipaaAdminStyle', plugin_dir_url(__FILE__) . '/css/style.css' );

    // ENQUEUE CUSTOM JS
    wp_enqueue_script( 'cmHipaaBuggyFill', plugin_dir_url(__FILE__) . 'js/viewport-units-buggyfill.js', array('jquery'), '3.0.5', true );
    wp_enqueue_script( 'cmHipaaBuggyFillHack', plugin_dir_url(__FILE__) . 'js/viewport-units-buggyfill.hacks.js', array('jquery'), '3.0.5', true );
    wp_enqueue_script('cm-hipaa-script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '3.0.5', true);
    wp_enqueue_script('cm-hipaa-signature', plugin_dir_url(__FILE__) . 'js/jSignature/jSignature.min.noconflict.js', array('jquery'), '3.0.5', true);

    // CHECK IF HOMEPAGE
    if (is_front_page()) {
        $frontpage = 1;
    } else {
        $frontpage = 0;
    }

    // CHECK IF USING SSL
    if (is_ssl()) {
        $ssl = 1;
    } else {
        $ssl = 0;
    }

    // GET ENABLED FORMS
    $calderaEnabledForms = json_encode(explode(',', esc_attr(get_option('caldera_enabled_form_ids'))));
    $gravityEnabledForms = json_encode(explode(',', esc_attr(get_option('gravity_enabled_form_ids'))));
    $enabledFormsSettings = get_option('enabled_forms_settings'); // NEW JSON VERSION WITH FORM SETTINGS

    $newDecodedSettings = [];
    $decodedSettings = json_decode($enabledFormsSettings);
    $gravityVersion = '';
    if(is_array($decodedSettings)) {
        foreach($decodedSettings as $decodedSetting) {
            // REMOVE EMAILS AND ANY OTHER DATA NOT WANTED IN THE WEB BROWSER
            $newDecodedSetting = [];
            //var_dump($decodedSetting);
            foreach($decodedSetting as $key => $value){
                if($key !== "notification_from_name" && $key !== "notification_from_email" && $key !== "notification_sendto" && $key !== "notification_subject" && $key !== "notification_message"){
                    $newDecodedSetting[$key] = $value;
                }
            }
            $newDecodedSettings[] = $newDecodedSetting;

            if($decodedSetting->form_builder == 'gravity' && $decodedSetting->enabled == 'yes') {
                wp_enqueue_style(
                    'custom-gravity-style',
                    plugin_dir_url(__FILE__) . '/css/style.css'
                );
                $custom_css = '
                        #gform_' . $decodedSetting->id . ' .gform_fileupload_multifile {
                            display: none;
                        }
                    ';
                wp_add_inline_style( 'custom-gravity-style', $custom_css );

                // CHECK GRAVITY VERSION (TO FIRST DECIMAL)
                $gravityVersion = substr(get_file_data(plugin_dir_path( __DIR__ ) . '/gravityforms/gravityforms.php', array('Version'), 'plugin')[0], 0, 3);
            }
        }
    }

    // TESTING PURPOSE ONLY
//    if(is_array($newDecodedSettings)) {
//        foreach($newDecodedSettings as $newDecodedSetting) {
//            echo '<div><h2>New Decoded Settings</h2>';
//            foreach($newDecodedSetting as $key => $value) {
//                echo '<div>key=' . $key . '</div>';
//                echo '<div>value=' . $value . '</div>';
//                echo '</div>';
//            }
//        }
//    }


    // PASS PHP DATA TO SCRIPT FILE (use example: cmHipaaScript.siteUrl)
    wp_localize_script('cm-hipaa-script', 'cmHipaaScript', array(
        'pluginUrl' => plugin_dir_url(__FILE__),
        'siteUrl' => get_site_url(),
        'frontPage' => $frontpage,
        'formBuilder' => esc_attr(get_option('form_builder')),
        'calderaEnabledForms' => $calderaEnabledForms,
        'gravityEnabledForms' => $gravityEnabledForms,
        //'enabledFormsSettings' => $enabledFormsSettings, OLD FORMSETTINGS
        'enabledFormsSettings' => json_encode($newDecodedSettings),
        'privacyNoticeMethod' => esc_attr(get_option('privacy_notice_method')),
        'privacyNoticeLabel' => esc_attr(get_option('privacy_notice_label')),
        'privacyNoticeCopy' => esc_attr(get_option('privacy_notice_copy')),
        'privacyNoticeLink' => esc_attr(get_option('privacy_notice_link')),
        'ssl' => $ssl,
        'gravityVersion' => $gravityVersion,
        'nonce' => wp_create_nonce('cm-hipaa-forms-nonce'),
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    // LOCALIZE CUSTOM JS FILE TO USE WITH AJAX
//    wp_localize_script('cm-hipaa-script', 'ajax', array(
//        'ajax_url' => admin_url('admin-ajax.php')
//    ));
}
add_action('wp_enqueue_scripts', 'enqueue_cm_hipaa_scripts');