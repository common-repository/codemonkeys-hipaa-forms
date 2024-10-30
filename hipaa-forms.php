<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 11/27/2017
 * Time: 4:33 PM
 *
 * Plugin Name: HIPAA Forms
 * Plugin URI: https://www.hipaaforms.online
 * Description: HIPAA Compliant Forms
 * Version: 3.0.5
 * Author: Code Monkeys LLC
 * Author URI: https://www.codemonkeysllc.com
 * License: GPL2
 */

//* REQUIRE ENQUEUE FILE
require(plugin_dir_path(__FILE__) . 'enqueue.php');

//* REQUIRE HIPAA FORMS OPTIONS
require(plugin_dir_path(__FILE__) . 'includes/options.php');

//* REQUIRE CLASS FILE
require(plugin_dir_path(__FILE__) . 'includes/class-cm-hipaa.php');

//* REQUIRE AJAX FUNCTIONS
require(plugin_dir_path(__FILE__) . 'ajax-functions.php');

//* REQUIRE USER ROLE
require(plugin_dir_path(__FILE__) . 'user-role.php');

//* REQUIRE ADMIN PAGE
require(plugin_dir_path(__FILE__) . 'admin-page.php');

//* GET PLUGIN VERSION
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_version = $plugin_data['Version'];
define ( 'HIPAAFORMS_CURRENT_VERSION', $plugin_version );

// GET ENABLED FORMS
$calderaEnabledForms = json_encode(explode(',', esc_attr(get_option('caldera_enabled_form_ids'))));
$gravityEnabledForms = json_encode(explode(',', esc_attr(get_option('gravity_enabled_form_ids'))));
$enabledFormsSettings = get_option('enabled_forms_settings'); // NEW JSON VERSION WITH FORM SETTINGS

$decodedSettings = json_decode($enabledFormsSettings);
if(is_array($decodedSettings)) {
    foreach($decodedSettings as $decodedSetting) {
        if($decodedSetting->form_builder == 'gravity' && $decodedSetting->enabled == 'yes') {
            // REMOVE GRAVITY SUBMIT BUTTON FOR ENABLED FORMS
            add_filter( 'gform_submit_button_' . $decodedSetting->id, '__return_false', 9999 );

            // CATCH ANY HIPAA FORM SUBMITTED TO GRAVITY VALIDATION (THIS IS A FALLBACK, AND SHOULD NEVER ACTUALLY RUN)
            add_filter( 'gform_validation_' . $decodedSetting->id, function($validation_result){
                $form  = $validation_result['form'];

                // a hipaa form is validated through the HIPAA Forms plugin and should never be validated by gravity
                $validation_result['is_valid'] = false;


                add_filter( 'gform_validation_message', function($message, $form){

                    $message = "<div class='validation_error'><p>This form submission is being overridden be the HIPAA Forms Plugin and could not be submitted!</p></div>";
                    return $message;

                },9999,2);

                //Assign modified $form object back to the validation result
                $validation_result['form'] = $form;

                return $validation_result;
            }, 9999 );

        }
    }
}
