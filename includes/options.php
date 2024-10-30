<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 11/28/2017
 * Time: 5:57 PM
 */

/*** CREATE ADMIN SETTING MENU ITEM - REMOVED, ALL SETTINGS HANDLED FROM WITHIN PLUGIN
function cm_hipaa_menu() {
    add_options_page('HIPAA Forms Options', 'HIPAA Forms Options', 'administrator', __FILE__, 'cm_hipaa_settings_page', '');
}
add_action( 'admin_menu', 'cm_hipaa_menu' );
***/

/*** REGISTER SETTINGS OPTIONS ***/
function register_cm_hipaa_settings() {
    register_setting( 'cm-hipaa-settings-group', 'notification_email' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_disable_email_notifications' );
    register_setting( 'cm-hipaa-settings-group', 'limit_notification_email' );
    register_setting( 'cm-hipaa-settings-group', 'license_key' );
    register_setting( 'cm-hipaa-settings-group', 'form_builder' );
    register_setting( 'cm-hipaa-settings-group', 'time_zone' );
    register_setting( 'cm-hipaa-settings-group', 'caldera_enabled_form_ids' );
    register_setting( 'cm-hipaa-settings-group', 'gravity_enabled_form_ids' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_read_posts_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_edit_posts_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_delete_posts_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_install_plugins_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_activate_plugins_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_edit_plugins_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_update_plugins_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_delete_plugins_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_manage_options_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_manage_categories_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_upload_files_cap' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_role_capabilities' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_notification_from_name' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_notification_from_email' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_notification_email_subject' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_notification_email_message' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_form_css' );
    register_setting( 'cm-hipaa-settings-group', 'enabled_forms_settings' );
    register_setting( 'cm-hipaa-settings-group', 'privacy_notice_method' );
    register_setting( 'cm-hipaa-settings-group', 'privacy_notice_label' );
    register_setting( 'cm-hipaa-settings-group', 'privacy_notice_copy' );
    register_setting( 'cm-hipaa-settings-group', 'privacy_notice_link' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_custom_status_enabled' );
    register_setting( 'cm-hipaa-settings-group', 'hipaa_custom_status_options' );
}
add_action( 'admin_init', 'register_cm_hipaa_settings' );

/*** CREATE SETTINGS PAGE ***/
function cm_hipaa_settings_page() {
    // GET FORM BUILDER PLUGIN FIELD VALUE
    $formBuilder = esc_attr( get_option('form_builder') );
    if($formBuilder == 'caldera') {
        $calderaSelected = 'selected="selected"';
    } else if($formBuilder == 'gravity') {
        $gravitySelected = 'selected="selected"';
    }

    // GET PREFERRED TIME ZONE
    $timeZone = esc_attr(get_option('time_zone'));
    $tzAlaskaSelected = '';
    $tzCentralSelected = '';
    $tzEasternSelected = '';
    $tzHawaiiSelected = '';
    $tzHawaiiNoDstSelected = '';
    $tzMountainSelected = '';
    $tzMountainNoDstSelected = '';
    $tzPacificSelected = '';
    if($timeZone == 'alaska') {
        $tzAlaskaSelected = 'selected="selected"';
    } else if($timeZone == 'central') {
        $tzCentralSelected = 'selected="selected"';
    } else if($timeZone == 'eastern') {
        $tzEasternSelected = 'selected="selected"';
    } else if($timeZone == 'hawaii') {
        $tzHawaiiSelected = 'selected="selected"';
    } else if($timeZone == 'hawaii_no_dst') {
        $tzHawaiiNoDstSelected = 'selected="selected"';
    } else if($timeZone == 'mountain') {
        $tzMountainSelected = 'selected="selected"';
    } else if($timeZone == 'mountain_no_dst') {
        $tzMountainNoDstSelected = 'selected="selected"';
    } else if($timeZone == 'pacific') {
        $tzPacificSelected = 'selected="selected"';
    }

    // CHECK IF NOTIFICATIONS DISABLED
    $disableNotificationsChecked = '';
    if (esc_attr(get_option('hipaa_disable_email_notifications')) == 'on') {
        $disableNotificationsChecked = ' checked="checked"';
    }

    // GET LIMIT DEFAULT EMAIL NOTICES
    $limitDefaultEmailChecked = '';
    if(esc_attr(get_option('limit_notification_email')) == 'on') {
        $limitDefaultEmailChecked = ' checked="checked"';
    }
    ?>
    <div class="wrap">
        <h1>HIPAA Forms Settings</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'cm-hipaa-settings-group' );
            do_settings_sections( 'cm-hipaa-settings-group' );
            ?>
            <table class="form-table">
                <tr>
                    <td>
                        <table>
                            <tr valign="top">
                                <th scope="row">License Key</th>
                                <td><input type="text" name="license_key" placeholder="License Key" value="<?php echo esc_attr( get_option('license_key') ); ?>" /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    Notification Email (receives notices when forms are submitted).  Check "fallback only" if you only want notices sent to this email if not set from the form settings.
                                </th>
                                <td>
                                    <input type="text" name="notification_email" placeholder="Notification Email" value="<?php echo esc_attr(get_option('notification_email')); ?>"/><br />
                                    <input type="checkbox" name="limit_notification_email"<?php echo $limitDefaultEmailChecked; ?> /> Fallback Only.
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Select a Form Builder</th>
                                <td>
                                    <select name="form_builder">
                                        <option value="caldera"<?php echo $calderaSelected; ?>>Caldera</option>
                                        <option value="gravity"<?php echo $gravitySelected; ?>>Gravity</option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Preferred Timezone</th>
                                <td>
                                    <select name="time_zone">
                                        <option value="alaska"<?php echo $tzAlaskaSelected; ?>>Alaska</option>
                                        <option value="central"<?php echo $tzCentralSelected; ?>>Central</option>
                                        <option value="eastern"<?php echo $tzEasternSelected; ?>>Eastern</option>
                                        <option value="hawaii"<?php echo $tzHawaiiSelected; ?>>Hawaii</option>
                                        <option value="hawaii_no_dst"<?php echo $tzHawaiiNoDstSelected; ?>>Hawaii No DST</option>
                                        <option value="mountain"<?php echo $tzMountainSelected; ?>>Mountain</option>
                                        <option value="mountain_no_dst"<?php echo $tzMountainNoDstSelected; ?>>Mountain No DST</option>
                                        <option value="pacific"<?php echo $tzPacificSelected; ?>>Pacific</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="cm-hipaa-forms-setting-css-wrapper">
                        <textarea name="hipaa_form_css" title="Custom Css for Submitted Forms"><?php echo esc_attr(get_option('hipaa_form_css')); ?></textarea>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="caldera_enabled_form_ids" value="<?php echo esc_attr( get_option('caldera_enabled_form_ids') ); ?>" />
            <input type="hidden" name="gravity_enabled_form_ids" value="<?php echo esc_attr( get_option('gravity_enabled_form_ids') ); ?>" />
            <input type="hidden" name="hipaa_form_css" value="<?php echo esc_attr(get_option('hipaa_form_css')); ?>"/>
            <input type="hidden" name="enabled_forms_settings" value="<?php echo esc_attr(get_option('enabled_forms_settings')); ?>"/>
            <input type="hidden" name="privacy_notice_method" value="<?php echo esc_attr(get_option('privacy_notice_method')); ?>"/>
            <input type="hidden" name="privacy_notice_label" value="<?php echo esc_attr(get_option('privacy_notice_label')); ?>"/>
            <input type="hidden" name="privacy_notice_copy" value="<?php echo esc_attr(get_option('privacy_notice_copy')); ?>"/>
            <input type="hidden" name="privacy_notice_link" value="<?php echo esc_attr(get_option('privacy_notice_link')); ?>"/>
            <input type="hidden" name="hipaa_role_capabilities" value="<?php echo esc_attr(get_option('hipaa_role_capabilities')); ?>" />
            <input type="hidden" name="hipaa_notification_email_subject" value="<?php echo esc_attr(get_option('hipaa_notification_email_subject')); ?>" />
            <input type="hidden" name="hipaa_notification_email" value="<?php echo esc_attr(get_option('hipaa_notification_email')); ?>" />
            <input type="hidden" name="hipaa_custom_status_enabled" value="<?php echo esc_attr(get_option('hipaa_custom_status_enabled')); ?>" />
            <input type="hidden" name="hipaa_custom_status_options" value="<?php echo esc_attr(get_option('hipaa_custom_status_options')); ?>" />

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}