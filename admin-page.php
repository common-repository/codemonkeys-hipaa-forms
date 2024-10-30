<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 12/1/2017
 * Time: 10:35 AM
 */

//* ADD MENU ITEM
function hipaa_admin_menu() {
    add_menu_page( 'HIPAA Forms', 'HIPAA Forms', 'access_hipaa_forms', 'hipaa-forms.php', 'hipaa_admin_page', 'dashicons-lock', 6  );
}
add_action( 'admin_menu', 'hipaa_admin_menu' );


//* CREATE ADMIN PAGE
function hipaa_admin_page(){
    // INSTANTIATE CLASS
    $hipaaForms = new cmHipaaForms;

    // GET USER ROLES AND SET APPROVED/NOT APPROVED
    $user = wp_get_current_user();

    $user_id = $user->ID;
    $user_name = $user->user_login;
    $user_display_name = $user->display_name;
    $user_first_name = $user->first_name;
    $user_last_name = $user->last_name;
    $name_pieces = explode(" ", $user_display_name);
    if(!$user_first_name && count($name_pieces) >= 1) {
        $user_first_name = $name_pieces[0];
    }
    if(!$user_last_name && count($name_pieces) >= 2) {
        $user_last_name = $name_pieces[1];
    }
    $user_email = $user->user_email;
    $user_roles = $user->roles;
    $approved = 0;

    /*** SET ADMINISTRATOR OR HIPAA USER ROLE ***/
    if(in_array('administrator', $user_roles)) {
        $approved = 1;
        $role = 'administrator';
    } else if(in_array('hipaa_forms', $user_roles)) {
        $approved = 1;
        $role = 'hipaa';
    }

    if($approved == 0) {
        echo 'YOU DO NOT HAVE PERMISSION TO VIEW THIS SECTION!';

        // LOG USER ACCESS
        echo $hipaaForms->accessLog($user_id, $user_name, $user_first_name, $user_last_name, $user_email, $user_roles, $approved);
    } else {
        // GET FORM BUILDER PLUGIN FIELD VALUE
        $formBuilder = esc_attr(get_option('form_builder'));
        $formsList = '';

        // VALIDATE ACCOUNT
        $validateAccount = json_decode($hipaaForms->validateAccount());
        $product = '';
        if (isset($validateAccount->product)) {
            $product = $validateAccount->product;
        }
        $fileUploadEnabled = false;
        if (isset($validateAccount->add_ons)) {
            $addOns = explode(',', $validateAccount->add_ons);

            if (in_array('fileupload', $addOns)) {
                $fileUploadEnabled = true;
            }
        }
        $licenseStatus = '';
        if (isset($validateAccount->license_status)) {
            $licenseStatus = $validateAccount->license_status;
        }
        $licenseStatusMessage = 'No License Found';
        if (isset($validateAccount->license_status_message)) {
            $licenseStatusMessage = $validateAccount->license_status_message;
        }
        $daysToDisable = '';
        if (isset($validateAccount->days_to_disable)) {
            $daysToDisable = $validateAccount->days_to_disable;
        }

        // SET UPGRADE BUTTON & NOTICE
        $subscriptionNoticeButton = '';
        $subscriptionNotice = '';
        if ($product == 'basic') {
            $subscriptionNoticeButton = '<a class="cm-button" href="https://www.hipaaforms.online/my-account/subscriptions/" target="_blank"><i class="material-icons">cloud_upload</i> UPGRADE</a>';
            $subscriptionNotice = '<div class="cm-hipaa-upgrade-subscription-notice">Upgrade your subscription for unlimited forms & form submissions! ' . $subscriptionNoticeButton . '<div class="clearfix"></div></div>';
        } else {
            if ($product == 'standard' && $licenseStatus == 'grace') {
                $subscriptionNoticeButton = '<a class="cm-button" href="https://www.hipaaforms.online/my-account/" target="_blank"><i class="material-icons">account_circle</i> UPDATE ACCOUNT</a>';
                $subscriptionNotice = '<div class="cm-hipaa-upgrade-subscription-notice">Your subscription has expired & will temporarily be dropped to the free basic plan in ' . $daysToDisable . ' days. ' . $subscriptionNoticeButton . '.<div class="clearfix"></div></div>';
            } else {
                if ($product == 'standard' && $licenseStatus == 'expired') {
                    $subscriptionNoticeButton = '<a class="cm-button" href="https://www.hipaaforms.online/my-account/" target="_blank"><i class="material-icons">account_circle</i> UPDATE ACCOUNT</a>';
                    $subscriptionNotice = '<div class="cm-hipaa-upgrade-subscription-notice">Your subscription has expired & dropped to the free basic plan. ' . $subscriptionNoticeButton . '<div class="clearfix"></div></div>';
                }
            }
        }

        // SET FORM BUILDER SELECT OPTIONS
        $formBuilderOptions = array();

        // CHECK IF CALDERA IS INSTALLED
        if (method_exists('Caldera_Forms_Forms', 'get_forms')) {
            $calderaSelected = '';
            if ($formBuilder == 'caldera') {
                // SET FORM SELECT FIELD AS CALDERA SELECTED
                $calderaSelected = 'selected="selected"';

                // GET LIST OF CALDERA FORMS
                $enabledFormsSettings = get_option('enabled_forms_settings'); // NEW JSON VERSION WITH FORM SETTINGS
                $formsList = $hipaaForms->getCalderaForms($enabledFormsSettings);
            }

            $formBuilderOptions[] = '<option value="caldera"' . $calderaSelected . '>Caldera</option>';
        }

        // CHECK IF GRAVITY ACTIVE
        if (method_exists('RGFormsModel', 'get_forms')) {
            $gravitySelected = '';
            if ($formBuilder == 'gravity') {
                // SET FORM SELECT FIELD AS GRAVITY SELECTED
                $gravitySelected = 'selected="selected"';

                // GET LIST OF GRAVITY FORMS
                //$gravityEnabledForms = explode(',', esc_attr(get_option('gravity_enabled_form_ids'))); -- DEPRECATED
                $gravityEnabledForms = get_option('enabled_forms_settings'); // NEW JSON VERSION WITH FORM SETTINGS
                $formsList = $hipaaForms->getGravityForms($gravityEnabledForms);
            }

            $formBuilderOptions[] = '<option value="gravity"' . $gravitySelected . '>Gravity</option>';
        } else {
            // OLD WAY OF CHECKING IF GRAVITY ACTIVE JUST TO BE SAFE
            if (!function_exists('is_plugin_active') || !function_exists('is_plugin_active_for_network')) {
                require_once(ABSPATH . '/wp-admin/includes/plugin.php');
            }
            if (is_multisite()) {
                $gravityEnabled = (is_plugin_active_for_network('gravityforms/gravityforms.php') || is_plugin_active('gravityforms/gravityforms.php'));
            } else {
                $gravityEnabled = is_plugin_active('gravityforms/gravityforms.php');
            }
            if ($gravityEnabled == 1) {
                $gravitySelected = '';
                if ($formBuilder == 'gravity') {
                    // SET FORM SELECT FIELD AS GRAVITY SELECTED
                    $gravitySelected = 'selected="selected"';

                    // GET LIST OF GRAVITY FORMS
                    //$gravityEnabledForms = explode(',', esc_attr(get_option('gravity_enabled_form_ids'))); -- DEPRECATED
                    $gravityEnabledForms = get_option('enabled_forms_settings'); // NEW JSON VERSION WITH FORM SETTINGS
                    $formsList = $hipaaForms->getGravityForms($gravityEnabledForms);
                }

                $formBuilderOptions[] = '<option value="gravity"' . $gravitySelected . '>Gravity</option>';
            }
        }

        // SET FORM BUILDER SELECT OPTIONS
        if (count($formBuilderOptions)) {
            $formBuilderSelect = '
                <select id="cm-hipaa-form-builder-select">
                    <option value="">None</option>
                    ' . implode('', $formBuilderOptions) . '
                </select>
            ';
        } else {
            $formBuilderSelect = '
                <div class="cm-hipaa-forms-no-form-builder">
                    No Form Builder Detected - Please install & activate either <a href="https://calderaforms.com/?thnx=116" target="_blank">Caldera Forms (free plugin)</a> or <a href="https://www.gravityforms.com/" target="_blank">Gravity Forms (paid plugin)</a>
                </div>';
        }

        // GET PREFERRED TIME ZONE TODO: REWORK HOW TIMEZONES ARE SET AND HANDLED
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
        if (esc_attr(get_option('limit_notification_email')) == 'on') {
            $limitDefaultEmailChecked = ' checked="checked"';
        }

        // GET LOCATIONS FROM SUBMITTED FORMS
        $getLocations = $hipaaForms->getLocations();
        $locationSelect = '';
        $locations = '';
        $locationsError = '';
        if(isset(json_decode($getLocations)->content)) {
            $locations = json_decode($getLocations)->content;
        }
        if(isset(json_decode($getLocations)->error)) {
            $locationsError = json_decode($getLocations)->error;
        }

        if($locationsError) {
            echo $locationsError;
        } else {
            $locationOptions = array();
            if ($locations) {
                $locations = stripcslashes(stripcslashes($locations));
                $locations = explode(';', $locations);
                $locations = array_unique($locations);

                // SET LOCATION OPTIONS
                foreach ($locations as $location) {
                    $locationOptions[] = '
                <option value="' . $location . '">' . $location . '</option>
            ';
                }
            }

            // SET LOCATION SELECT
            if (!empty($locationOptions)) {
                $locationSelect = '
                <div class="cm-submitted-form-filter cm_hipaa_col_25">
                    <select id="cm-submitted-form-filter-location">
                        <option value="">-- Office Location --</option>
                        ' . implode('', $locationOptions) . '
                    </select> <i class="material-icons cm-hipaa-setting-info" data-content="office-location-info" title="More Information">info</i>
                </div>
            ';
            }
        }

        // GET FORM NAMES FROM SUBMITTED FORMS
        $getFormNames = $hipaaForms->getFormNames();
        $formNames = json_decode($getFormNames)->content;
        $formNameOptions = array();
        if ($formNames) {
            $formNames = explode(',', $formNames);

            // SET LOCATION OPTIONS
            foreach ($formNames as $formName) {
                $formNameOptions[] = '
                    <option value="' . $formName . '">' . $formName . '</option>
                ';
            }
        }

        // SET FORM NAME SELECT
        $formNameSelect = '';
        if (!empty($formNameOptions)) {
            $formNameSelect = '
                <div class="cm-submitted-form-filter cm_hipaa_col_25">
                    <select id="cm-submitted-form-filter-form-name">
                        <option value="">-- Form Name --</option>
                        ' . implode('', $formNameOptions) . '
                    </select>
                </div>
            ';
        }

        // SET USER FILTER
        $userFilter = '';
        if($role == 'administrator') {
            $userFilter = $hipaaForms->getApprovedUserSelect();
        } else if($role == 'hipaa') {
            $userFilter = '
                <div class="cm-submitted-form-filter cm_hipaa_col_25">
                    <input type="checkbox" id="cm-submitted-form-filter-assigned-to-me" value="' . $user_id . '" /> Assigned to Me
                </div>
            ';
        }

        // GET HIPAA USER ROLE CAPABILITIES
        $hipaaRoleCapabilities = $hipaaForms->getHipaaCapabilities();

        // GET NOTIFICATION EMAIL SUBJECT
        $hipaaNotificationEmailSubject = esc_attr(get_option('hipaa_notification_email_subject'));
        if (!$hipaaNotificationEmailSubject || $hipaaNotificationEmailSubject == '') {
            $hipaaNotificationEmailSubject = 'HIPAA Form Submission {location}';
        }

        // GET NOTIFICATION EMAIL
        $hipaaNotificationEmail = esc_attr(get_option('hipaa_notification_email_message'));
        if (!$hipaaNotificationEmail || $hipaaNotificationEmail == '') {
            $hipaaNotificationEmail = '<table width="600px" style="border-collapse:collapse;"><tbody><tr><th><b>HIPAA Form Submission</b></th></tr><tr><td style="height:30px"></td></tr><tr><td>Location: {location}</td></tr><tr><td>First Name: {firstname}</td></tr><tr><td style="height:15px"></td></tr><tr><td>Last Name: {lastname}</td></tr><tr><td style="height:15px"></td></tr><tr><td>Email: {email}</td></tr><tr><td style="height:15px"></td></tr><tr><td>Phone: {phone}</td></tr><tr><td style="height:30px"></td></tr><tr><td>Please log into the admin dashboard to view the form.</td></tr><tr><td>Please do not reply to this email</td></tr></tbody></table>';
        }

        /*** SET PRIVACY NOTICE OPTIONS ***/
        $privacyNoticeMethodModalChecked = '';
        $privacyNoticeMethodLinkChecked = '';
        if (esc_attr(get_option('privacy_notice_method')) == 'link') {
            $privacyNoticeMethodLinkChecked = 'checked="checked"';
        } else {
            $privacyNoticeMethodModalChecked = 'checked="checked"';
        }

        $privacyNoticeLink = '';
        if (esc_attr(get_option('privacy_notice_link'))) {
            $privacyNoticeLink = esc_attr(get_option('privacy_notice_link'));
        }

        $privacyNoticeLabel = '';
        if (esc_attr(get_option('privacy_notice_label'))) {
            $privacyNoticeLabel = esc_attr(get_option('privacy_notice_label'));
        } else {
            $privacyNoticeLabel = 'I agree to the HIPAA FORMS Service Privacy Statement';
        }

        $privacyNoticeCopy = '';
        if (esc_attr(get_option('privacy_notice_copy'))) {
            $privacyNoticeCopy = esc_attr(get_option('privacy_notice_copy'));
        } else {
            $privacyNoticeCopy = '<p>
This "Notice of Information/Privacy Practices" is used to inform website visitors regarding our policies with the collection, use, and disclosure of Personal Information if anyone decided to use our Service.
</p>
<p>
If you choose to use our Service, then you agree to the collection and use of information in relation with this policy. The Personal Information that we collect are used for providing and improving the Service. We will not use or share your information with anyone except as described in this Privacy Policy.
</p>
<p>
<h3>Information Collection and Use</h3>
For a better experience while using our Service, we may require you to provide us with certain personally identifiable information, including but not limited to your name, phone number, and postal address. The information that we collect will be used to contact or identify you.
</p>
<p>
<h3>Log Data</h3>
We want to inform you that whenever you visit our Service, we collect information that your browser sends to us that is called Log Data. This Log Data may include information such as your computer’s Internet Protocol ("IP") address, browser version, pages of our Service that you visit, the time and date of your visit, the time spent on those pages, and other statistics.
</p>
<p>
<h3>Cookies</h3>
Cookies are files with small amount of data that is commonly used an anonymous unique identifier. These are sent to your browser from the website that you visit and are stored on your computer’s hard drive.
</p>
<p>
Our website uses these "cookies" to collection information and to improve our Service. You have the option to either accept or refuse these cookies, and know when a cookie is being sent to your computer. If you choose to refuse our cookies, you may not be able to use some portions of our Service.
</p>
<p>
<h3>Service Providers</h3>
We may employ third-party companies and individuals due to the following reasons:
<ul>
<li>To facilitate our Service;</li>
<li>To provide the Service on our behalf;</li>
<li>To perform Service-related services; or</li>
<li>To assist us in analyzing how our Service is used.</li>
<p>
We want to inform our Service users that these third parties have access to your Personal Information. The reason is to perform the tasks assigned to them on our behalf. However, they are obligated not to disclose or use the information for any other purpose.
</p>
</p>
<p>
<h3>Security</h3>
We value your trust in providing us your Personal Information, thus we are striving to use commercially acceptable means of protecting it. But remember that no method of transmission over the internet, or method of electronic storage is 100% secure and reliable, and we cannot guarantee its absolute security.
</p>
<p>
<h3>Links to Other Sites</h3>
Our Service may contain links to other sites. If you click on a third-party link, you will be directed to that site. Note that these external sites are not operated by us. Therefore, we strongly advise you to review the Privacy Policy of these websites. We have no control over, and assume no responsibility for the content, privacy policies, or practices of any third-party sites or services.
</p>
<p>
<h3>Changes to This Privacy Policy</h3>
We may update our Privacy Policy from time to time. Thus, we advise you to review this page periodically for any changes. We will notify you of any changes by posting the new Privacy Policy on this page. These changes are effective immediately, after they are posted on this page.
</p>';
        }

        // GET CUSTOM STATUS ENABLED OPTION
        $customStatusEnabledInput = '<input type="checkbox" id="cm-hipaa-forms-setting-enable-status" />';
        if (esc_attr(get_option('hipaa_custom_status_enabled'))) {
            $customStatusEnabled = esc_attr(get_option('hipaa_custom_status_enabled'));
            if ($customStatusEnabled == 'yes') {
                $customStatusEnabledInput = '<input type="checkbox" id="cm-hipaa-forms-setting-enable-status" checked="checked" />';
            }
        }

        // GET CUSTOM STATUS OPTIONS & SET CUSTOM STATUS FILTER
        $customStatusInputs = '';
        $customStatusFilterSelect = '';
        if(esc_attr(get_option('hipaa_custom_status_options'))) {
            $customStatusOptions = esc_attr(get_option('hipaa_custom_status_options'));
            if ($customStatusOptions) {
                $customStatusOptions = explode(',', $customStatusOptions);
                $customStatusOptionInputs = array();
                $customStatusFilterOptions = array();

                foreach ($customStatusOptions as $customStatusOption) {
                    $customStatusOptionInputs[] = '
                        <div class="cm-hipaa-forms-setting-status-option-wrapper"><input type="text" class="cm-hipaa-forms-setting-status-option" placeholder="Status Option..." value="' . $customStatusOption . '" /> <i class="material-icons cm-hipaa-form-settings-remove-input">remove_circle_outline</i></div>
                    ';

                    $customStatusFilterOptions[] = '
                        <option value="' . $customStatusOption . '">' . $customStatusOption . '</option>
                    ';
                }

                $customStatusInputs = implode('', $customStatusOptionInputs);

                $customStatusFilterSelect = '
                    <div class="cm-submitted-form-filter cm_hipaa_col_25">
                        <select id="cm-submitted-form-filter-custom-status">
                            <option value="">-- Custom Status --</option>
                            ' . implode('', $customStatusFilterOptions) . '
                        </select>
                    </div>
                ';
            }
        }

        // SET RECENT UPDATE NOTICE
        $updateNotice = '
            <div class="cm-hipaa-forms-update-notice">
                <strong>DID YOU KNOW</strong><br />
                1. You can customize the look of your submitted forms under settings -> forms css?<br />
                2. You can generate regular PDFs by expanding the form, clicking the print icon at the top right & selecting Adobe PDF?
            </div>
        ';

        ?>
        <div class="wrap">
        <input type="hidden" id="selected-form-builder" value="<?php echo $formBuilder; ?>" />
        <h2>HIPAA Forms</h2>
        <?php echo $subscriptionNotice; ?>
        <?php echo $updateNotice; ?>
        <div class="cm-hipaa-tabs-wrapper">
            <div class="cm-hipaa-tabs">
                <div class="cm-hipaa-tab active" data="tab-1">
                    Submitted Forms
                </div>
                <?php if($role == 'administrator') { ?>
                    <div class="cm-hipaa-tab" data="tab-2">
                        Settings
                    </div>
                    <div class="cm-hipaa-tab" data="tab-3">
                        Log
                    </div>
                <?php } ?>
                <div class="cm-hipaa-tab" data="tab-4">
                    Support
                </div>
            </div>
            <div class="cm-hipaa-tab-content-wrapper">
                <!-- SUBMITTED FORMS TAB -->
                <div class="cm-hipaa-tab-content" data="tab-1">
                    <h1>Submitted Forms</h1>
                    <div class="cm-submitted-forms-filters-wrapper">
                        <div class="cm-submitted-forms-filters">
                            <div class="cm-submitted-form-date-wrapper">
                                <div class="cm-submitted-form-filter">
                                    <label for="cm-submitted-form-start-date">Submitted Between:</label>
                                    <input type="date" id="cm-submitted-form-start-date" title="Start Date" /> and <input type="date" id="cm-submitted-form-end-date" title="End Date" value="<?php echo date("Y-m-j"); ?>" />
                                </div>
                            </div>
                            <div class="cm_hipaa_grid_row">
                                <div class="cm-submitted-form-filter cm_hipaa_col_15">
                                    <select id="cm-submitted-form-filter-status">
                                        <option value="">-- Status --</option>
                                        <option value="all">All</option>
                                        <option value="0">Archived</option>
                                        <option value="1">Not Archived</option>
                                    </select>
                                </div>
                                <?php echo $locationSelect; ?>
                                <?php echo $formNameSelect; ?>
                                <?php echo $customStatusFilterSelect; ?>
                            </div>
                            <div class="cm_hipaa_grid_row">
                                <div class="cm-submitted-form-filter cm_hipaa_col_14">
                                    <input type="text" id="cm-submitted-form-filter-first-name" placeholder="First Name"/>
                                </div>
                                <div class="cm-submitted-form-filter cm_hipaa_col_14">
                                    <input type="text" id="cm-submitted-form-filter-last-name" placeholder="Last Name"/>
                                </div>
                                <div class="cm-submitted-form-filter cm_hipaa_col_14">
                                    <input type="text" id="cm-submitted-form-filter-phone" placeholder="Phone #"/>
                                </div>
                                <div class="cm-submitted-form-filter cm_hipaa_col_14">
                                    <input type="text" id="cm-submitted-form-filter-email" placeholder="Email"/>
                                </div>
                                <div class="cm-submitted-form-filter cm_hipaa_col_14">
                                    Per Page: <select id="cm-submitted-form-filter-limit">
                                        <option value="10" selected="selected">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                        <option value="500">500</option>
                                    </select>
                                </div>
                                <div class="cm-submitted-form-filter cm_hipaa_col_14">
                                    <div id="cm-hipaa-submitted-forms-search" class="cm-button">
                                        SEARCH
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="cm-submitted-form-filter-page" value="0"/>
                            <input type="hidden" id="cm-submitted-form-filter-total" value=""/>
                        </div>
                    </div>
                    <div id="cm-submitted-forms-results">
                    </div>
                </div>
                <?php if($role == 'administrator') { ?>
                    <!-- PLUGIN SETTINGS TAB -->
                    <div class="cm-hipaa-tab-content" data="tab-2">
                        <div class="cm_hipaa_grid_row_nogap cm_hipaa_tour_tab_wrapper">
                            <div class="cm-hipaa-tour-tab-menu cm_hipaa_col_25">
                                <ul>
                                    <li data="tour-tab-settings" class="cm-hipaa-tour-tab-link cm-hipaa-active-tour-tab-link">
                                        <div class="cm-hipaa-tour-tab-link-inner">
                                            <i class="material-icons">settings</i> <span class="cm-hipaa-tab-name">PLUGIN SETTINGS</span>
                                        </div>
                                    </li>
                                    <li data="tour-tab-selected-forms" class="cm-hipaa-tour-tab-link">
                                        <div class="cm-hipaa-tour-tab-link-inner">
                                            <i class="material-icons">note_add</i> <span class="cm-hipaa-tab-name">FORM SETTINGS</span>
                                        </div>
                                    </li>
                                    <li data="tour-tab-user-role" class="cm-hipaa-tour-tab-link">
                                        <div class="cm-hipaa-tour-tab-link-inner">
                                            <i class="material-icons">person</i> <span class="cm-hipaa-tab-name">USER PERMISSIONS</span>
                                        </div>
                                    </li>
                                    <li data="tour-tab-notifications" class="cm-hipaa-tour-tab-link">
                                        <div class="cm-hipaa-tour-tab-link-inner">
                                            <i class="material-icons">mail</i> <span class="cm-hipaa-tab-name">NOTIFICATIONS</span>
                                        </div>
                                    </li>
                                    <li data="tour-tab-privacy" class="cm-hipaa-tour-tab-link">
                                        <div class="cm-hipaa-tour-tab-link-inner">
                                            <i class="material-icons">visibility</i> <span class="cm-hipaa-tab-name">PRIVACY STATEMENT</span>
                                        </div>
                                    </li>
                                    <li data="tour-tab-form-status" class="cm-hipaa-tour-tab-link">
                                        <div class="cm-hipaa-tour-tab-link-inner">
                                            <i class="material-icons">star_border</i> <span class="cm-hipaa-tab-name">FORMS STATUS</span>
                                        </div>
                                    </li>
                                    <li data="tour-tab-form-css" class="cm-hipaa-tour-tab-link">
                                        <div class="cm-hipaa-tour-tab-link-inner">
                                            <i class="material-icons">developer_mode</i> <span class="cm-hipaa-tab-name">FORMS CSS</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="cm-hipaa-tour-tab-content-wrapper cm_hipaa_col_75">
                                <!-- PLUGIN SETTINGS -->
                                <section data="tour-tab-settings" class="cm-hipaa-accordion-tab-link cm-hipaa-active-accordion-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">settings</i> <span class="cm-hipaa-tab-name">PLUGIN SETTINGS</span>
                                    </div>
                                </section>
                                <div data="tour-tab-settings" class="cm-hipaa-tour-tab-content">
                                    <form id="cm_hipaa_forms_options" method="post" action="options.php">
                                        <?php
                                        settings_fields('cm-hipaa-settings-group');
                                        do_settings_sections('cm-hipaa-settings-group');
                                        ?>
                                        <table class="form-table">
                                            <tr valign="top">
                                                <td style="vertical-align: top;">
                                                    <table>
                                                        <tr valign="top">
                                                            <th scope="row">Subscription Type</th>
                                                            <td><?php echo $product; ?></td>
                                                            <td><?php echo $subscriptionNoticeButton; ?></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <th scope="row">License Key</th>
                                                            <td><input type="text" name="license_key" placeholder="License Key"
                                                                       value="<?php echo esc_attr(get_option('license_key')); ?>"/></td>
                                                            <td><i class="material-icons cm-hipaa-setting-info" data-content="license-key-info" title="More Information">info</i></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <th scope="row">License Status</th>
                                                            <td><?php echo $licenseStatusMessage; ?></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <th scope="row">Preferred Timezone</th>
                                                            <td>
                                                                <!-- TODO: REWORK TO PASS ACTUAL TIMEZONE VALUE -->
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
                                                            <td><i class="material-icons cm-hipaa-setting-info" data-content="timezone-info" title="More Information">info</i></td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <th scope="row">BAA Agreement</th>
                                                            <td id="cm-hipaa-forms-signed-baa"><div class="cm-sign-baa-button">You must sign the BAA Agreement!</div></td>
                                                            <td><i class="material-icons cm-hipaa-setting-info" data-content="baa-info" title="More Information">info</i></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="form_builder" value="<?php echo esc_attr(get_option('form_builder')); ?>" />
                                                    <input type="hidden" name="caldera_enabled_form_ids" value="<?php echo esc_attr(get_option('caldera_enabled_form_ids')); ?>" />
                                                    <input type="hidden" name="gravity_enabled_form_ids" value="<?php echo esc_attr(get_option('gravity_enabled_form_ids')); ?>" />
                                                    <input type="hidden" name="hipaa_form_css" value="<?php echo esc_attr(get_option('hipaa_form_css')); ?>"/>
                                                    <input type="hidden" name="enabled_forms_settings" value="<?php echo esc_attr(get_option('enabled_forms_settings')); ?>" />
                                                    <input type="hidden" name="privacy_notice_method" value="<?php echo esc_attr(get_option('privacy_notice_method')); ?>" />
                                                    <input type="hidden" name="privacy_notice_label" value="<?php echo esc_attr(get_option('privacy_notice_label')); ?>" />
                                                    <input type="hidden" name="privacy_notice_copy" value="<?php echo esc_attr(get_option('privacy_notice_copy')); ?>" />
                                                    <input type="hidden" name="privacy_notice_link" value="<?php echo esc_attr(get_option('privacy_notice_link')); ?>" />
                                                    <input type="hidden" name="hipaa_role_capabilities" value="<?php echo esc_attr(get_option('hipaa_role_capabilities')); ?>" />
                                                    <input type="hidden" name="hipaa_notification_from_name" value="<?php echo esc_attr(get_option('hipaa_notification_from_name')); ?>" />
                                                    <input type="hidden" name="hipaa_notification_from_email" value="<?php echo esc_attr(get_option('hipaa_notification_from_email')); ?>" />
                                                    <input type="hidden" name="notification_email" value="<?php echo esc_attr(get_option('notification_email')); ?>" />
                                                    <input type="hidden" name="hipaa_disable_email_notifications" value="<?php echo esc_attr(get_option('hipaa_disable_email_notifications')); ?>" />
                                                    <input type="hidden" name="limit_notification_email" value="<?php echo esc_attr(get_option('limit_notification_email')); ?>" />
                                                    <input type="hidden" name="hipaa_notification_email_subject" value="<?php echo esc_attr(get_option('hipaa_notification_email_subject')); ?>" />
                                                    <input type="hidden" name="hipaa_notification_email_message" value="<?php echo esc_attr(get_option('hipaa_notification_email_message')); ?>" />
                                                    <input type="hidden" name="hipaa_custom_status_enabled" value="<?php echo esc_attr(get_option('hipaa_custom_status_enabled')); ?>" />
                                                    <input type="hidden" name="hipaa_custom_status_options" value="<?php echo esc_attr(get_option('hipaa_custom_status_options')); ?>" />

                                                    <?php submit_button(); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                                <!-- FORM SETTINGS -->
                                <section data="tour-tab-selected-forms" class="cm-hipaa-accordion-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">note_add</i> <span class="cm-hipaa-tab-name">FORM SETTINGS</span>
                                    </div>
                                </section>
                                <div data="tour-tab-selected-forms" class="cm-hipaa-tour-tab-content">
                                    <h1>Current Form Plugin: <?php echo $formBuilder; ?></h1>
                                    <div class="cm-hipaa-form-builder-select-wrapper">
                                        Select a Form Builder: <?php echo $formBuilderSelect; ?> <i class="material-icons cm-hipaa-setting-info" data-content="form-builder-info" title="More Information">info</i>
                                        <div id="cm-hipaa-form-builder-select-notice"></div>
                                    </div>
                                    <div id="cm-hipaa-forms-available-forms-wrapper">
                                        <?php echo $formsList; ?>
                                    </div>
                                </div>
                                <!-- HIPAA USER CAPABILITIES -->
                                <section data="tour-tab-user-role" class="cm-hipaa-accordion-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">person</i> <span class="cm-hipaa-tab-name">USER PERMISSIONS</span>
                                    </div>
                                </section>
                                <div data="tour-tab-user-role" class="cm-hipaa-tour-tab-content cm-hipaa-user-role-settings">
                                    <h1>HIPAA USER CAPABILITIES</h1><i class="material-icons cm-hipaa-setting-info" data-content="user-role-info" title="More Information">info</i>
                                    <div class="cm-hipaa-role-capabilities-wrapper cm_hipaa_grid_row">
                                        <?php echo $hipaaRoleCapabilities; ?>
                                    </div>
                                    <div id="cm-hipaa-role-capabilities-submit" class="cm-button">
                                        SAVE
                                    </div>
                                    <div id="cm-hipaa-role-capabilities-notice"></div>
                                    <div class="cm-hipaa-forms-settings-css-tips">
                                        * If Woocommerce is active on your site you must enable the "Edit Posts" capability to allow users with the "HIPAA Forms" user role access to the admin dashboard.
                                    </div>
                                </div>
                                <!-- NOTIFICATION SETTINGS -->
                                <section data="tour-tab-notifications" class="cm-hipaa-accordion-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">mail</i> <span class="cm-hipaa-tab-name">NOTIFICATIONS</span>
                                    </div>
                                </section>
                                <div data="tour-tab-notifications" class="cm-hipaa-tour-tab-content cm-hipaa-notification-settings">
                                    <h1>EMAIL NOTIFICATIONS</h1><i class="material-icons cm-hipaa-setting-info" data-content="notification-email-editor-info" title="More Information">info</i>

                                    <div class="cm-hipaa-notification-input-wrapper">
                                        <input type="checkbox" id="cm-hipaa-disable-notifications-input"<?php echo $disableNotificationsChecked; ?> /> DISABLE EMAIL NOTIFICATIONS
                                    </div>

                                    <div class="cm-hipaa-notification-input-wrapper cm_hipaa_grid_row_nogap">
                                        <div class="cm_hipaa_col_50">
                                            <label for="cm-hipaa-notification-from-name-input">From Name:</label>
                                            <input type="text" id="cm-hipaa-notification-from-name-input" placeholder="Default name email displays from..." value="<?php echo esc_attr(get_option('hipaa_notification_from_name')); ?>" />
                                        </div>
                                        <div class="cm_hipaa_col_50">
                                            <label for="cm-hipaa-notification-from-email-input">From Email:</label>
                                            <input type="text" id="cm-hipaa-notification-from-email-input" placeholder="Default address email is from..." value="<?php echo esc_attr(get_option('hipaa_notification_from_email')); ?>" />
                                        </div>
                                    </div>
                                    <div class="cm-hipaa-notification-input-wrapper cm_hipaa_grid_row_nogap">
                                        <div class="cm_hipaa_col_50">
                                            <label for="cm-hipaa-notification-from-sendto-input">Send Notifications To:</label>
                                            <input type="text" id="cm-hipaa-notification-sendto-input" placeholder="Default email to send submission notifications to..." value="<?php echo esc_attr(get_option('notification_email')); ?>" />
                                        </div>
                                        <div class="cm_hipaa_col_50">
                                            <label for="cm-hipaa-notification-email-fallback-input">Fallback Only?:</label>
                                            <input type="checkbox" id="cm-hipaa-notification-email-fallback-input"<?php echo $limitDefaultEmailChecked; ?> /> Yes. <i class="material-icons cm-hipaa-setting-info" data-content="notification-email-info" title="More Information">info</i>
                                        </div>
                                    </div>
                                    <div class="cm-hipaa-notification-input-wrapper">
                                        <label for="cm-hipaa-notification-from-subject-input">Subject:</label>
                                        <input type="text" id="cm-hipaa-notification-subject-input" placeholder="Default Notification Email Subject..." value="<?php echo $hipaaNotificationEmailSubject; ?>" />
                                    </div>
                                    <div class="cm-hipaa-notification-input-wrapper">
                                        <label for="cm-hipaa-notification-input">Notification Email Message:</label>
                                        <textarea id="cm-hipaa-notification-input" placeholder="Default Notification Email Message..."><?php echo $hipaaNotificationEmail; ?></textarea>
                                    </div>
                                    <div id="cm-hipaa-notification-email-submit" class="cm-button">
                                        SAVE
                                    </div>
                                    <div id="cm-hipaa-notification-email-notice"></div>
                                    <div class="cm-hipaa-notification-email-variable-info">
                                        The following tags for non-health information can be used to dynamically pull data from the form being submitted:
                                    </div>
                                    <div class="cm-hipaa-notification-email-variable-key">
                                        Form Name: {formname}<br />
                                        First Name: {firstname}<br />
                                        Last Name: {lastname}<br />
                                        Email: {email}<br />
                                        Phone: {phone}<br />
                                        Location: {location}
                                    </div>
                                    <div class="cm-hipaa-notification-email-variable-info">
                                        <p>*NOTE: Adding your form's name could be considered PHI if it implies a request for service.</p>
                                        <p>Example of form name considered PHI is "New Patient Registration"</p>
                                        <p>Example of form name NOT considered PHI is "Contact Us"</p>
                                    </div>
                                </div>
                                <!-- PRIVACY STATEMENT SETTINGS -->
                                <section data="tour-tab-privacy" class="cm-hipaa-accordion-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">visibility</i> <span class="cm-hipaa-tab-name">PRIVACY STATEMENT</span>
                                    </div>
                                </section>
                                <div data="tour-tab-privacy" class="cm-hipaa-tour-tab-content cm-hipaa-privacy-notice-options">
                                    <h1>PRIVACY STATEMENT SETTINGS</h1> <i class="material-icons cm-hipaa-setting-info" data-content="privacy-info" title="More Information">info</i>
                                    <div class="cm-hipaa-forms-setting-privacy-wrapper">
                                        <div class="cm-hipaa-forms-setting-privacy-description">
                                            <p>
                                                HIPAA requires that all persons you collect medical information from either directly or indirectly (such as by filling a prescription) be notified of their rights to privacy and receive a "Notice of Privacy Practices" or "Notice of Information Practices".
                                            </p>
                                        </div>
                                        <div class="cm-hipaa-forms-privacy-field-wrapper">
                                            <input type="radio" name="cm-hipaa-forms-privacy-type" value="modal" <?php echo $privacyNoticeMethodModalChecked; ?> /> Modal
                                            <input type="radio" name="cm-hipaa-forms-privacy-type" value="link" <?php echo $privacyNoticeMethodLinkChecked; ?> /> Link
                                        </div>
                                        <div class="cm-hipaa-forms-privacy-field-wrapper">
                                            <input type="text" id="cm-hipaa-forms-privacy-label" value="<?php echo $privacyNoticeLabel; ?>" placeholder="Privacy Statement Checkbox Label" />
                                        </div>
                                        <div class="cm-hipaa-forms-privacy-field-wrapper">
                                            <textarea id="cm-hipaa-forms-privacy-copy" placeholder="Privacy Statement"><?php echo $privacyNoticeCopy; ?></textarea>
                                        </div>
                                        <div class="cm-hipaa-forms-privacy-field-wrapper">
                                            <input type="text" id="cm-hipaa-forms-privacy-link" value="<?php echo $privacyNoticeLink; ?>" placeholder="Page Link" />
                                        </div>
                                        <div id="cm-hipaa-forms-privacy-submit" class="cm-button">
                                            SAVE
                                        </div>
                                        <div id="cm-hipaa-forms-privacy-feedback"></div>
                                    </div>
                                </div>

                                <!-- STATUS SETTINGS -->
                                <section data="tour-tab-form-status" class="cm-hipaa-accordion-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">star_border</i> <span class="cm-hipaa-tab-name">FORMS STATUS</span>
                                    </div>
                                </section>
                                <div data="tour-tab-form-status" class="cm-hipaa-tour-tab-content cm-hipaa-form-status-options">
                                    <h1>FORM STATUS OPTIONS</h1> <i class="material-icons cm-hipaa-setting-info" data-content="status-options" title="More Information">info</i>
                                    <div class="cm-hipaa-forms-setting-status-wrapper">
                                        <div class="cm-hipaa-forms-setting-enable-status-wrapper">
                                            <?php echo $customStatusEnabledInput; ?> Enable Status
                                        </div>
                                        <div class="cm-hipaa-forms-setting-status-options">
                                            <div class="cm-hipaa-forms-setting-status-option-wrapper">
                                                <?php echo $customStatusInputs; ?>
                                            </div>
                                        </div>
                                        <div class="cm-hipaa-form-settings-add-input cm-button">
                                            ADD NEW
                                        </div>
                                        <div id="cm-hipaa-forms-custom-status-submit" class="cm-button">
                                            SAVE
                                        </div>
                                        <div id="cm-hipaa-custom-status-feedback"></div>
                                    </div>
                                </div>

                                <!-- CSS SETTINGS -->
                                <section data="tour-tab-form-css" class="cm-hipaa-accordion-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">developer_mode</i> <span class="cm-hipaa-tab-name">FORMS CSS</span>
                                    </div>
                                </section>
                                <div data="tour-tab-form-css" class="cm-hipaa-tour-tab-content">
                                    <div class="cm-hipaa-forms-setting-css-wrapper">
                                        <label for="hipaa_form_css">Custom Submitted Form CSS</label>
                                        <textarea name="hipaa_form_css_visible" id="hipaa-form-css-visible" title="Custom CSS for Submitted Forms"><?php echo esc_attr(get_option('hipaa_form_css')); ?></textarea>
                                        <div id="cm-hipaa-forms-css-submit" class="cm-button">
                                            SAVE
                                        </div>
                                        <div class="cm-hipaa-forms-settings-css-tips">
                                            <p>
                                                <strong><i>CUSTOMIZE THE LOOK OF SUBMITTED FORMS USING CSS</i></strong><br /><br />
                                                We've purposely added very little styling to submitted forms "out of the box" to make it easier to customize the look through CSS yourself.<br /><br />
                                                This CSS can apply to the web view version you see when you toggle a submitted form, the generated encrypted password protected PDF version as well as the print view version of the form.  This allows you to control how your submitted forms look in all 3 formats.
                                            </p>
                                            <ul>
                                                <li>To apply specific styles to the <strong>web version only</strong> prepend your styles with .cm-hipaa-submitted-form-fields.</li>
                                                <li>To apply specific styles to the <strong>PDF version only</strong> prepend your styles with .pdf-body.</li>
                                                <li>To apply specific styles to the <strong>print version only</strong> wrap your styles in an @media print {}.</li>
                                            </ul>
                                            <p>
                                                <strong>TIP:</strong><br />
                                                Right click on an element you want to change the style of on the web view version of your form and select "inspect element".  This will open the inspector window in order to see what classes/ids/tags/etc to hook your CSS into as well as allow you to see how your changes may look prior to changing them.
                                            </p>
                                            <p class="cm-hipaa-forms-settings-css-notice">
                                                <strong>STYLING PASSWORD-PROTECTED PDF'S</strong><br />
                                                * MPDF (Password-Protected PDF Generator) does not recognize the label tag & are replaced with spans with ".label" class in the PDF version.<br />
                                                * Some styles may not apply quite as expected in the PDF version, for documentation on CSS for the PDF version see the <a href="https://mpdf.github.io/css-stylesheets/supported-css.html" target="_blank">MPDF CSS DOCUMENTION</a><br /><br />
                                                * <i><u>NOTE:</u></i><br />
                                                If you DO NOT WANT password-protected encrypted PDF's you can click the print icon at the top right of the web-view version of the form, select Adobe PDF instead of your printer and save the regular PDF that way.  You should NOT do this if you do not have an encrypted hard drive and of course should NEVER be sent via unsecured non-HIPAA compliant email.
                                            <p>
                                            <p class="cm-hipaa-forms-settings-css-notice">
                                                <strong>STYLING FIELDS DEPENDING ON IF THEY HAVE A VALUE</strong><br />
                                                Each field on submitted forms has a custom class added to it's wrapping element indicating if the field is EMPTY or NOT EMPTY allowing you to customize the style depending on if a field has a value or not.<br /><br />
                                                * <u>EMPTY FIELDS</u> have the class .cm_hipaa_forms_field_empty<br />
                                                * <u>FIELDS WITH A VALUE</u> have the class .cm_hipaa_forms_field_not_empty.
                                            </p>
                                            <p class="cm-hipaa-forms-settings-css-notice">
                                                <strong>EXAMPLES:</strong><br />
                                                <ul>
                                                    <li>
                                                        <i>Hide fields with no value in web view version of submitted form:</i><br />
                                                        .cm-hipaa-submitted-form-fields .cm_hipaa_forms_field_empty {
                                                        display: none;
                                                        }
                                                    </li>
                                                    <li>
                                                        <i>Hide fields with no value in password-protected PDF version of submitted form:</i><br />
                                                        .pdf-body .cm_hipaa_forms_field_empty {
                                                        display: none;
                                                        }
                                                    </li>
                                                    <li>
                                                        <i>Hide fields with no value in printed version of submitted form:</i><br />
                                                        @media print {
                                                        .cm_hipaa_forms_field_empty {
                                                        display: none;
                                                        }
                                                        }
                                                    </li>
                                                    <li>
                                                        <i>Make labels bold in password-protected PDF version of submitted form:</i><br />
                                                        .pdf-body .label {
                                                        font-weight: bold;
                                                        }
                                                    </li>
                                                </ul>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="cm-hipaa-tab-content" data="tab-3">
                    <h1>Access Logs</h1>
                    <div class="cm-hipaa-forms-logs-filters-wrapper">
                        <div class="cm-hipaa-forms-logs-filters cm_hipaa_grid_row">
                            <div class="cm-hipaa-forms-logs-filter cm_hipaa_col_16">
                                <input type="text" id="cm-hipaa-forms-logs-filter-start-date" placeholder="Start Date"/>
                            </div>
                            <div class="cm-hipaa-forms-logs-filter cm_hipaa_col_16">
                                <input type="text" id="cm-hipaa-forms-logs-filter-end-date" placeholder="End Date"/>
                            </div>
                            <div class="cm-hipaa-forms-logs-filter cm_hipaa_col_16">
                                Results Per Page: <select id="cm-hipaa-forms-logs-filter-limit">
                                    <option value="5">5</option>
                                    <option value="10" selected="selected">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <div class="cm-hipaa-forms-logs-filter cm_hipaa_col_16">
                                <div id="cm-hipaa-forms-logs-search" class="cm-button">
                                    SEARCH
                                </div>
                            </div>
                            <input type="hidden" id="cm-hipaa-forms-logs-filter-page" value="0"/>
                            <input type="hidden" id="cm-hipaa-forms-logs-filter-total" value=""/>
                        </div>
                    </div>
                    <div id="cm-hipaa-forms-logs-results">
                    </div>
                </div>
                <div class="cm-hipaa-tab-content" data="tab-4">
                    <div class="cm_hipaa_grid_row_nogap cm_hipaa_tour_tab_wrapper">
                        <div class="cm-hipaa-tour-tab-menu cm_hipaa_col_25">
                            <ul>
                                <li data="tour-tab-1" class="cm-hipaa-tour-tab-link cm-hipaa-active-tour-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">school</i> <span class="cm-hipaa-tab-name">INSTRUCTIONS</span>
                                    </div>
                                </li>
                                <li data="tour-tab-2" class="cm-hipaa-tour-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">help_outline</i> <span class="cm-hipaa-tab-name">FAQ'S</span>
                                    </div>
                                </li>
                                <li data="tour-tab-3" class="cm-hipaa-tour-tab-link">
                                    <div class="cm-hipaa-tour-tab-link-inner">
                                        <i class="material-icons">message</i> <span class="cm-hipaa-tab-name">TICKETS</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="cm-hipaa-tour-tab-content-wrapper cm_hipaa_col_75">
                            <section data="tour-tab-1" class="cm-hipaa-accordion-tab-link cm-hipaa-active-accordion-tab-link">
                                <div class="cm-hipaa-tour-tab-link-inner">
                                    <i class="material-icons">school</i> <span class="cm-hipaa-tab-name">INSTRUCTIONS</span>
                                </div>
                            </section>
                            <div data="tour-tab-1" class="cm-hipaa-tour-tab-content">
                                <div class="cm-hipaa-forms-instructions-wrapper">
                                    <embed height="640" width="100%" src="https://codemonkeysllchelp.freshdesk.com/support/solutions/folders/48000273332" />
                                    <!--<div class="cm-hipaa-forms-insturctions">
                                        <h1 class="cm-hipaa-forms-instructions-title">HOW DOES THIS WORK?</h1>
                                        <ol class="cm-hipaa-forms-instructions-content">
                                            <li>
                                                <h4>GET A LICENSE KEY</h4>
                                                <p>
                                                    First you must subscribe to the HIPAA FORMS Service API.
                                                </p>
                                                <p>
                                                    A free option is available but is limited to 1 form and up to 25 submissions per month, no credit card is required and you can upgrade to the standard option for unlimited forms & submissions at any time for $55/mo.
                                                </p>
                                                <p>
                                                    This service is what handles the storage and PDF generation of the forms and forms CAN NOT be submitted without a subscription.
                                                </p>
                                                <p>
                                                    Once you subscribe to the service a license key will be generated and emailed to you.  Visit <a href="https://www.hipaaforms.online/" target="_blank">HIPAA FORMS Service</a> to get a license key.</p>
                                            </li>
                                            <li>
                                                <h4>INSTALL A FORM BUILDER</h4>
                                                <p>
                                                    Next make sure you have Caldera Forms or Gravity Forms installed and active.
                                                </p>
                                                <p>
                                                    Caldera is a free form builder plugin that you can download <a href="https://calderaforms.com/?thnx=116" target="_blank">HERE</a> or install from plugins->add new & search for "Caldera". No additional extensions are needed or supported.
                                                </p>
                                                <p>
                                                    Gravity Forms is a premium paid form builder plugin that can be purchased <a href="https://www.gravityforms.com/" target="_blank">HERE</a> No additional extensions are needed or supported.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>UPDATE SETTINGS</h4>
                                                <p>
                                                    Now that you have your license key and Caldera or Gravity Forms installed and activated, go to the "Settings" tab & add your license key.  Also select your preferred compatible form builder plugin (ie. Caldera or Gravity) & set your preferred time zone.
                                                </p>
                                                <p>
                                                    Next click on the "user permissions" sub-tab (we'll come back to the "form settings" sub-tab later).  You have the option to give users a HIPAA user role if you have staff that you want to give access to the submitted forms.  We highly recommend giving them the HIPAA user role as opposed to making them administrators.  From this tab you can check which permissions your staff with the HIPAA user role should have beyond just accessing the submitted forms.
                                                </p>
                                                <p>
                                                    In order for users with the hipaa_user role to access the admin dashboard and the HIPAA Forms interface you MUST give them at least one permission.  We recommend checking the "Read Documents" option which will
                                                </p>
                                                <p>
                                                    *NOTE: Users with administrator user roles will always see all forms and settings.  Only users with the hipaa_user role are unable to access the plugin settings and can be limited to only seeing specific forms.
                                                </p>
                                                <p>
                                                    Next click on the "notifications" sub-tab.  This is where you set a default notification email that will be sent to you or your staff whenever a form is submitted.  We highly recommend using a "from" email address with the same @domain as your website to help prevent emails from going to spam.  You'll notice the email message box is pre-populated with an HTML table structure and we highly recommend leaving this table structure in place.  Email clients often do not render HTML & CSS very well so a table structure with very limited CSS will give you the best layout across multiple Email clients.
                                                </p>
                                                <p>
                                                    You'll also notice that you can add a few "magic tags" to your notification email subject and message.  This allows you to dynamically pull some non-PHI field values from the submitted forms as these magic tags are replaced by the submitted form's associated values when submitted.
                                                </p>
                                                <p>
                                                    Finally you should click on the "privacy statement" sub-tab.  From here you can customize your privacy statement that must be agreed to before someone submits a form.
                                                </p>
                                                <p>
                                                    The last "forms css" sub-tab allows you to customize the styling of your submitted forms both for the web view as well as the PDF versions.  This isn't something you need to be concerened with yet and only for those that know CSS.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>SIGN THE BAA</h4>
                                                <p>After you save the settings with your valid license key you'll then be prompted to sign the BAA agreement.  This is an agreement between Code Monkeys LLC & your company explaining how we will handle protected health information (E-PHI) and is required by HIPAA in order to be compliant.  If your company has it's own BAA that you would rather put in place instead of our default agreement let us know and in most cases we can use yours in place of ours.</p>
                                                <p>
                                                    If you are a web designer or digital marketer working on behalf of your end-client you may sign the BAA with Code Monkeys LLC but you must also in turn have a BAA in place with your end-client.  To satisfy HIPAA regulations there must be an "unbroken chain" of BAA agreements in place where the end-client is considered the "Covered Entity", you would be considered the "Business Associate" and Code Monkeys LLC would be considered a "Sub-Contractor" of you, the Business Associate.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>CREATE A FORM</h4>
                                                <p>
                                                    *NOTE*: It's very common for medical forms to be long but be aware that extremely long forms can cause problems.  Not only does it increase the chance that the person filling out the form will not complete it, but it can also result in technical issues.  If your forms include multiple pages and have a lot of fields in them you may need to increase your max_input_vars to at least 3000 in your PHP.ini settings to prevent a "nonce expired" error or receiving only part of the form during submission.  We have more information on how to address these issues in the FAQ section.
                                                </p>
                                                <p>
                                                    Once the plugin settings are saved & the BAA is signed you need to create a form.  You must have 4 required fields in the form which are needed in order to filter/search the submitted forms. These fields are:
                                                </p>

                                                <ol>
                                                    <li>
                                                        <p>
                                                            First Name: Must have a text input<br />
                                                            Caldera: Must have the slug "first_name"<br />
                                                            Gravity: Must have the class "hipaa_forms_first_name"
                                                            (If Using Gravity Advanced Name Field: Must have the class "hipaa_forms_name" with first name enabled)
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            Last Name: Must have a text input<br />
                                                            Caldera: Must have the slug "last_name"<br />
                                                            Gravity: Must have the class "hipaa_forms_last_name"
                                                            (If Using Gravity Advanced Name Field: Must have the class "hipaa_forms_name" with last name enabled)
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            Phone: Must have a text or phone input<br />
                                                            Caldera: Must have the slug "phone"<br />
                                                            Gravity: Must have the class "hipaa_forms_phone"
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            Email: Must have a text or email input<br />
                                                            Caldera: Must have the slug "email"<br />
                                                            Gravity: Must have the class "hipaa_forms_email"
                                                        </p>
                                                    </li>
                                                </ol>

                                                <p>
                                                    There is an optional select field you can choose to use if you have multiple locations.
                                                </p>
                                                <p>
                                                    This field will allow you to set a specific office location and associated email address.
                                                </p>
                                                <p>
                                                    When this multi-location field is set the form will save with the name of the office location you specify and allow you to filter the submitted forms by office location.
                                                </p>
                                                <p>
                                                    The associated email address you specify for the office location within the field will be appended to the "send to" emails set within the form builder to ensure that someone from that specific office gets an email notification when a form is submitted.
                                                </p>
                                                <ol>
                                                    <li>
                                                        <p>
                                                            The location field must be a select input
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            The option label value must be the name of your office location
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            The option value must be the email address you want to receive the notification emails for that office location.
                                                        </p>
                                                    </li>
                                                    <li>
                                                        <p>
                                                            Caldera: Must have the slug "office_location"<br />
                                                            Gravity: Must have the class "hipaa_forms_office_location"
                                                        </p>
                                                    </li>
                                                </ol>

                                                <p>
                                                    Here is a screenshot of how to set this field in Caldera & Gravity:<br />
                                                    <img src="<?php echo plugin_dir_url(__FILE__); ?>/images/location-field-screen.jpg" alt="Multi-Location Field Caldera" /><img src="<?php echo plugin_dir_url(__FILE__); ?>/images/location-field-screen-gravity.jpg" alt="Multi-Location Field Gravity" />
                                                </p>
                                                <p>
                                                    There is another optional select field you can set in a form if you want someone filling out a form to select a specific doctor/user and have that specific user receive the notification email and that form only be viewable by that user.  NOTE: This only applies to users with the hipaa user role, users with the administrator user role can always see all submitted forms.  The use case scenario for using this option is if you have multiple doctors/dentists/etc and would like your patients to select their specific doctor and then limit that submitted form to that specific doctor/dentist/etc.
                                                </p>
                                                <p>
                                                    In order to use this select option you must set the user id as the select option value for the doctors/dentists/etc that you want to have available.  To find the user's id navigate to the users section in the admin dashboard and put your mouse over the user you want to add, at the bottom left of the browser you should see a URL appear with a user_id= in it.  The user id is the number directly after the user_id= part, Example: user_id=30 where 30 is the user id number.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>UNDERSTANDING HOW THE HIPAA FORMS PLUGIN WORKS WITH CALDERA/GRAVITY</h4>
                                                <p>
                                                    NOTE: The HIPAA Web Forms plugin does not use the default submission process built into Caldera or Gravity as this would not be HIPAA/PIPEDA compliant.  Because of this some of the default functionality and add-ons aren't compatible with our plugin.
                                                </p>
                                                <p>
                                                    One common point of confusion is how email notifications are handled and how to set them.  While you can add an email to the form notification settings of your Caldera or Gravity form, you can not use magic tags (variables that use the {} characters) or build your notification message in Caldera or Gravity.
                                                </p>
                                                <p>
                                                    This is restricted by design as it would open up the ability to pass E-PHI from the form to the email notification instantly making your form non-compliant.  Instead you need to build your email notification message from the HIPAA Web Forms plugin under the settings tab->notifications sub-tab or within the selected form settings.  From this interface we do allow a few magic tags restricted to non-health data like form name, name, phone, email, location, etc.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>SELECTING FORMS TO BE HIPAA COMPLIANT</h4>
                                                <p>
                                                    Once your form is created click on the "Selected Forms" tab.
                                                </p>
                                                <p>
                                                    If a form has all of the required fields then you will have the option to select it by checking the box at the left of the form. If the form does NOT have all of the required fields a warning icon will show next to the form. Clicking on the warning/toggle icon will display what fields are missing.  Clicking the toggle icon will show all of the forms fields and slugs/classes.
                                                </p>
                                                <p>
                                                    Once you have checked the box next to a form it should now be HIPAA Compliant, it's really as simple as that.
                                                </p>
                                                <p>
                                                    By toggling the form by clicking the triangle icon on the left you can set if you want the "drag 'n draw" signature to be added to the form, set what should happen once a form is submitted such as display a message on the form or redirect to another page and set who can view the submitted forms with the hipaa user role such as "all users", "only specific users" or "selected users only" which allows you to list specific users in a drop down field that a patient can select when filling out the form.
                                                </p>
                                                <p>
                                                    You can also choose whether to use the default notification email settings set under settings->notifications or use a custom notification email message for this specific form.  If you click on the "custom" option the notification options will be displayed and your notification emails for this specific form will use this custom message and settings instead of the default settings.
                                                </p>
                                                <p>
                                                    To verify the form is now compliant go to where the form is rendered on the page and you should now see an additional section at the bottom of the form right above the submit button showing a checkbox to agree to the HIPAA privacy agreement, a badge showing the form is encrypted and HIPAA compliant and the signature field which a user can sign by left clicking and dragging their mouse or by simply using their finger or stylus if on a touch screen.  You should also see a padlock on the submit button indicating that the form submit function is secure.  If you do NOT see these something is not correct and the form will NOT be HIPAA compliant and you should submit a support ticket so our support staff can troubleshoot the issue before attempting to use the form.  It is your responsibility to ensure forms are in a compliant state before allowing patients to submit private protected health information and failure to do so could result in fines.
                                                </p>
                                                <p>
                                                    Since the default submit button is removed and replaced with the secure submit button by Javascript after the page loads you may see the original button for a second during the page load.  If using Gravity Forms we recommend setting a display: none in your css on the default submit button for your specific HIPAA compliant forms to prevent this from appearing during load as you do not have an option to not include the submit button.  If you're using Caldera we recommend just not including the submit button in the form.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>WHAT HAPPENS WHEN A FORM IS SUBMITTED</h4>
                                                <p>
                                                    When someone submits the HIPAA Compliant form the default form submit button is replaced with a custom button (indicated by the padlock icon) and instead the form will be encrypted and an API call is made to the HIPAA FORMS API and saved into a HIPAA Compliant database storage solution. The only fields that are not encrypted are the required first name, last name, phone # and email in order to allow searching/filtering the forms in the "Submitted Forms" tab. The form itself (and all fields within it) are encrypted at the time of submission in order to protect the data in transit as well as at rest within the HIPAA FORMS Service data solution. If you tried to look at the form data at this point you would just see a long string of nonsense letters, numbers and characters and would not be able to see any of the actual information.
                                                </p>
                                                <p>
                                                    The only way the form data can be viewed at this point is by logging into the Wordpress admin panel with valid username and password credentials for an account with the appropriate user role associated to it (administrator or hipaaforms) and opening the "Submitted Forms" tab within the HIPAA FORMS interface. Here another API request is sent to the HIPAA FORMS API and the submitted forms data is pulled. Once the data is returned from the API the associated encryption keys are then used to decrypt the form data and then display that data on the screen. While you can view this data within this tab the actual data is never stored anywhere on your server, it simply pulls it from the HIPAA FORMS Service API and the plugin decrypts and displays it. The only way for this data to actually ever leave our secure system is to generate an encrypted PDF file with password protection.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>GENERATING AN ENCRYPTED PASSWORD PROTECTED PDF VERSION OF A FORM</h4>
                                                <p>
                                                    To generate a PDF version of the form click the "Generate PDF" button next to the form. This will bring up a modal window (popup) asking you to set a password. Once a password is provided click generate. The modal window should update with a link to open the newly created PDF, once you enter the password the form will load and you will be able to view the form, print it or save it to your computer.  If you do not keep track of the password you will NOT be able to view the PDF file.
                                                </p>
                                                <p>
                                                    The PDF file must be encrypted and password protected to ensure that the form can never be intercepted and read as it's transferred between the HIPAA FORMS Service and you. This should also help keep you more compliant internally as the file can not be read without the correct password once it's saved to your computer. While ensuring that the PDF files are encrypted and password protected should keep the data safe and compliant we HIGHLY recommend that any computer you download the PDFs to have encrypted hard drives to be safe and ensure compliance. Once you print or download the PDF to your computer Code Monkeys LLC and all associates covered within the BAA agreement bear no liability for the handling of the data.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>GENERATING A NON-ENCRYPTED PDF VERSION OF A FORM WITHOUT PASSWORD PROTECTION</h4>
                                                <p>
                                                    *NOTE - This should only be done if your device is HIPAA compliant meaning your device has an encrypted hard drive (using something like bitlocker) and uses a strong password according to your organization's strong password rules set in your organization's HIPAA compliance documentation.  Code Monkeys LLC has no control over and is not liable for E-PHI once it has left our system.
                                                </p>
                                                <p>
                                                    To generate a non-encrypted non-password protected PDF version of a form click on the submitted forms tab and expand the form you wish to create a PDF for by clicking the triangle icon at the left of the form in the list view.
                                                </p>
                                                <p>
                                                    The form will be pulled through our API, decrypted and then displayed in your browser.  Once displayed a print icon will be visible at the top right of the form.
                                                </p>
                                                <p>
                                                    Clicking on the print icon will display the print options window.  At the left of the print interface change the destination to "Save as PDF" and then click the "save" button.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>PRINTING A FORM</h4>
                                                <p>
                                                    *NOTE - It is your responsibility to follow your organization's HIPAA compliance guidelines and to ensure PHI is handled correctly and secured.  Code Monkeys LLC has no control over and is not liable for PHI once it has left our system.
                                                </p>
                                                <p>
                                                    To print a form click on the submitted forms tab and expand the form you wish to print by clicking the triangle icon at the left of the form in the list view.
                                                </p>
                                                <p>
                                                    The form will be pulled through our API, decrypted and then displayed in your browser.  Once displayed a print icon will be visible at the top right of the form.
                                                </p>
                                                <p>
                                                    Clicking on the print icon will display the print options window.  Select your printer in the "destination" select box and click the print button.
                                                </p>
                                            </li>
                                            <li>
                                                <h4>UNDERSTANDING HIPAA</h4>
                                                <p>
                                                    We strongly recommend that you keep up with HIPAA regulation changes and that you work with a qualified attorney and/or HIPAA Compliance professional to ensure compliance.
                                                </p>
                                                <p>
                                                    A great resource for both business associated (web designers and developers) and covered entities is the <a href="https://ece88010.isrefer.com/go/wordpress-forms/a671/" target="_blank">HIPAA Compliancy Group</a>.  They can walk you through each step of the process to ensure you're able to achieve, illustrate and maintain HIPAA compliant.
                                                </p>
                                            </li>
                                        </ol>
                                    </div>-->
                                </div>
                            </div>
                            <section data="tour-tab-2" class="cm-hipaa-accordion-tab-link cm-hipaa-active-accordion-tab-link">
                                <div class="cm-hipaa-tour-tab-link-inner">
                                    <i class="material-icons">help_outline</i> <span class="cm-hipaa-tab-name">FAQ'S</span>
                                </div>
                            </section>
                            <div data="tour-tab-2" class="cm-hipaa-tour-tab-content">
                                <div class="cm-hipaa-forms-faqs-wrapper">
                                    <embed height="640" width="100%" src="https://codemonkeysllchelp.freshdesk.com/support/solutions/folders/48000356345" />
                                    <!--<div class="cm-hipaa-forms-faqs">
                                        <h2>REQUIREMENTS</h2>
                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">YOU MUST HAVE CALDERA OR GRAVITY FORMS INSTALLED & ACTIVE</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    Currently the HIPAA Forms plugins is only integrated with Caldera & Gravity Forms.
                                                </p>
                                                <p>
                                                    Caldera Forms is a free form builder plugin and can be installed by searching for it in the Wordpress plugin repository (plugins->add new).
                                                </p>
                                                <p>
                                                    Gravity Forms is a premium paid form builder plugin that can be purchased <a href="https://www.gravityforms.com/" target="_blank">HERE</a> No additional extensions are needed.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">YOU MUST HAVE SSL ENABLED</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    The HIPAA FORMS plugin checks to ensure SSL (https) is enabled and being
                                                    used.
                                                </p>
                                                <p>
                                                    Any forms set as HIPAA Compliant will be deactivated if the url does not
                                                    start with https://.
                                                </p>
                                                <p>
                                                    If you're unable to setup SSL with your current host or if your current
                                                    host's cost is too expensive consider a managed hosting (and optional
                                                    Wordpress maintenance package) from Code Monkeys. We automatically issue
                                                    free SSL certificates to all of our hosting customers. <a
                                                            href="https://www.codemonkeysllc.com/hosting" target="_blank">CLICK
                                                        HERE FOR DETAILS</a>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">YOU MUST HAVE A VALID LICENSE KEY</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    You can purchase a license key from <a href="https://www.hipaaforms.online" target="_blank">hipaaforms.online</a> as a monthly or annual subscription basis.
                                                </p>
                                                <p>
                                                    While this plugin is free to install and use, the HIPAA FORMS plugin is integrated with a paid monthly subscription
                                                    service and you must have an active account with a valid license key to use
                                                    this service. If your license key expires your forms designated as HIPAA
                                                    Compliant will become deactivated until you renew your license key.
                                                </p>
                                                <p>
                                                    Why do we have to charge for this service?<br/>
                                                    The encrypted form data is submitted to our HIPAA Forms API and stored on a
                                                    HIPAA COMPLIANT data storage solution in order to make the form submissions
                                                    compliant with HIPAA regulations which we incur a cost for both the
                                                    bandwidth and the data storage usage. In addition to the bandwidth and
                                                    storage costs involved we must also maintain the system and keep up with any
                                                    changes that may occur in the HIPAA REGULATIONS.
                                                </p>
                                                <p>
                                                    Finally, given the nature of working with private health information and securing this information according to the HIPAA guidlines we incur a certain amount of risk and liability and must in turn carry additional liability insurance coverage and do everything within our power to ensure that this data remains secure in all stages of the information's life cycle as well as keep a continuous log of access to this data.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">YOU CAN ONLY SUBMIT & VIEW FORMS FROM YOUR ASSOCIATED DOMAIN</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    Forms can only be submitted and viewed from the domain you added to your HIPAA FORMS Service subscription account at the time of checkout.
                                                </p>
                                                <p>
                                                    When a request is made to the HIPAA FORMS Service API it does a check against your license key, domain and if a BAA agreement has been signed.  If any of those things are not valid the API request is denied and an error will be returned specifying what the issue is.
                                                </p>
                                                <p>
                                                    Only one license key and domain is allowed per subscription meaning you can NOT use the same license or domain on more than one website.
                                                </p>
                                                <p>
                                                    This is done as an additional security measure to ensure that even if a license key is stolen form data would not be accessible.
                                                </p>
                                                <p>
                                                    If you need to change the domain associated with your license key you can do so by logging in at <a href="https://www.hipaaforms.online/my-account" target="_blank">https://www.hipaaforms.online/my-account</a>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">YOU MUST SIGN THE BAA AGREEMENT</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    A Business Associate Agreement (BAA) typically is required for companies that are subject to the Health Insurance Portability and Accountability Act (HIPAA) to ensure that protected health information (PHI) is appropriately safeguarded. Failure to manage data privacy risks with non-business associate vendors may lead to both violations of HIPAA and state privacy laws.
                                                </p>
                                                <p>
                                                    The BAA agreement is in place for your protection and forms can not be submitted or viewed until it is in place.
                                                </p>
                                                <p>
                                                    We also recommend that you have a BAA in place with your web designer if they work on the site as a 3rd party contractor.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">REQUIRED FIELDS</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    In order to have the ability to filter the submitted forms by name, email, phone or office location you must set some specific fields using the correct slugs in Caldera or classes in Gravity.  This allows these specific non-phi fields to be broken out of the encrypted submission which allows you to search by those criteria.
                                                </p>
                                                <p>
                                                    First name, last name, email address and phone number are required fields and must be added to every form you wish to set as HIPAA compliant forms.  You will be unable to select a form from the "selected forms" tab if a form does not have these fields set.
                                                </p>
                                                <p>
                                                    There is an additional "office location" field that you may set optionally if you have multiple office locations.  This field must be a select option and the name of the office location must be set as the option label with an associated email address specific to that office location.  This will allow the specific office location email address to receive the notification email when a form is submitted with that location selected in addition to the "send to" recipient email addresses set in the form builder settings.  This will also allow you to filter the submitted forms by office location name.
                                                </p>
                                                <p>
                                                    See the "Instructions" tab for more information on setting required fields.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">SETTING MULTIPLE OFFICE LOCATIONS</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    The Office Location field will allow you to set a specific office location and associated email address as a select field in your forms.  When this multi-location field is set the form will save with the name of the office location you specify and allow you to filter the submitted forms by office location.  The associated email address you specify for the office location within the field will be appended to the "send to" emails set within the form builder to ensure that someone from that specific office gets an email notification when a form is submitted.
                                                </p>
                                                <ol>
                                                    <li>
                                                        The location field must be a select input
                                                    </li>
                                                    <li>
                                                        The option label value must be the name of your office location
                                                    </li>
                                                    <li>
                                                        The option value must be the email address you want to receive the notification emails for that office location.
                                                    </li>
                                                    <li>
                                                        Caldera: Must have the slug "office_location"<br />
                                                        Gravity: Must have the class "hipaa_forms_office_location"
                                                    </li>
                                                </ol>

                                                <p>
                                                    Here is a screenshot of how to set this field in Caldera & Gravity:<br />
                                                    <img src="<?php echo plugin_dir_url(__FILE__); ?>/images/location-field-screen.jpg" alt="Multi-Location Field Caldera" /><img src="<?php echo plugin_dir_url(__FILE__); ?>/images/location-field-screen-gravity.jpg" alt="Multi-Location Field Gravity" />
                                                </p>
                                                <p>
                                                    See the "Instructions" tab for more information on setting the office location select field.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">FILE UPLOADS</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    In order to upload files with your forms you will need to first add the file upload add-on option to your HIPAA Forms API subscription.
                                                </p>
                                                <p>
                                                    You can upgrade your subscription by logging into your account at <a href="https://www.hipaaforms.online/my-account" target="_blank">https://www.hipaaforms.online/my-account</a>.
                                                </p>
                                            </div>
                                        </div>

                                        <h2>COMMON ISSUES</h2>
                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Email Notices Going to Spam</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    Default Wordpress emails get sent through your host's domain which often
                                                    times will be flagged as spam.
                                                </p>
                                                <p>
                                                    We highly recommend installing an email SMTP plugin for Wordpress and using
                                                    the SMTP settings for a legit email address. This will allow Wordpress to
                                                    send emails from the SMTP server instead of from your host.
                                                </p>
                                                <p>
                                                    Some hosting providers do not allow outgoing SMTP connections for whatever reason.  If this is the case you may need to setup something like MailGun which allows the first 10,000 emails for free but will need to move to a paid account at some point.  SendGrid is another option similar to MailGun and allows up to 40,000 emails for free within their 30 day free trial but will also require a paid subscription after the trial is over.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Forms Are Disabled / No HIPAA Compliant Badge
                                                Appears</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    If you do NOT see the additional section at the bottom of the form with the
                                                    HIPAA compliant badge then there is an issue somewhere and the form will NOT
                                                    be disabled as it will not be HIPAA compliant. A common reason this might
                                                    happen is if you do NOT have SSL (https://) enabled or if the user is
                                                    viewing the http:// version of the page. We strongly recommend that you
                                                    setup a redirect in your .htaccess file or by using a plugin to ensure all
                                                    pages are served the https:// version of the page. If this is the case the
                                                    form will be disabled and you should see a warning notice at the bottom of
                                                    the form instead of the badge.
                                                </p>
                                                <p>
                                                    Another common reason you might not see this section is if your license key
                                                    has expired. If this is the case you should see a warning notice at the
                                                    bottom of the form and the form will be disabled. Reactivating your license
                                                    key will solve the issue and your form will be enabled again.
                                                </p>
                                                <p>
                                                    A less common reason for this would be if another plugin is causing a
                                                    Javascript/jQuery error or conflict.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Nonce expired error</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    This is usually an issue with caching (or sometimes the max_input_vars setting in your php.ini if your form is exceptionally long with many fields).
                                                </p>
                                                <p>
                                                    *NOTE: Usually if the max_input_vars is the problem the form will be over-ridden as it should (HIPAA badge appears on the form) but the nonce error is returned after hitting the submit button.
                                                </p>
                                                <p>
                                                    Wordpress uses a nonce (number used once) to help secure your site during things like form submissions and AJAX calls, although its not really a "number used once" in the traditional sense.  Instead this is a hash token that can be used multiple times within a 12 or 24 hour period at which point the nonce will expire.  What happens is if your cache expiration is set beyond 12 hours the nonce will also be cached resulting in a validation error as that nonce will have expired.
                                                </p>
                                                <p>
                                                    There are 3 things to look at to solve this problem.  The first is to check any caching plugins you may be using such as W3TC, Super Cache, Rocket Cache, etc.  Go through the settings and ensure your cache expiration times are set under 12 hours.  A good way to ensure this error is due to a caching plugin is to simply deactivate the cache plugin, clear your browser cache, reload the page and try submitting a form again.  If it works with your cache plugin deactivated then you know that's the issue and its a matter of simply setting the expiration times lower.
                                                </p>
                                                <p>
                                                    The second thing to check is your max_input_vars setting in your php.ini settings especially if you have long forms with a lot of fields in them.  Since our plugin overrides the default form builder submission process and instead encrypts your entire form and submits it through our API a low max input setting will cause the data to be cut off at whatever max you have set.  The reason this results in a "nonce expired" error is because the data string gets cut off before the nonce can be passed so it's like no nonce is passed at all.  If the nonce error didn't stop the submission the form would still submit but only part of the form would actually be saved.  We recommend increasing this max_input_vars value to at least 3000 and testing again, if you have an extremely long form you may need to set it even higher.  If you don't have access to your php.ini file or you're not sure where to find or change it there are free Wordpress plugins available in the plugin repository that will allow you to change these settings from your Wordpress admin dashboard, a common one we've used many times is the "PHP settings" plugin from Askupa Software which you can find by going to plugins->add new from the left menu and searching for "php settings".
                                                </p>
                                                <p>
                                                    If you have no cache plugin or your caching plugin is deactivated, your browser cache has been cleared, you've increased your max_input_vars and you still receive the nonce error then its almost certainly an issue with your host's server-side caching or in some cases mod_security preventing large amounts of data being passed via AJAX.  Cheap hosting solutions especially can have over-aggressive server-side caching as a way to keep resource usage down and keep those ridiculously low prices.  These types of hosting solutions will usually not allow you to change either the server-side cache or mod_security settings as these effect all of the websites running on your shared node.
                                                </p>
                                                <p>
                                                    If your host is unable or not willing to disable or reduce the expiration of your server-side caching or change/disable mod_security then our only recommendation is to shorten your forms if a mod_security issue, move to a VPS plan or another host.  When it comes to hosting solutions it really is true that you get what you pay for and if you're paying less than $10/month you'll most likely have performance issues and a difficult time getting anything solved from their customer support.  Cheap hosting solutions are fine for a small static personal website but if you use Wordpress & rely on your website for your business then hosting is not something you want to bargain shop for.  We recommend a simple VPS solution from a reputable hosting company & always recommend against trying to use these ultra-cheap under $10/month shared hosting options.  If you're using this plugin then your time is extremely valuable & the time you'll waste dealing with hosting issues will be much more than the added cost of a good hosting solution, not to mention the potential business that can be lost from a slow or broken website.
                                                </p>
                                                <p>
                                                    Code Monkeys tries to do everything within our power to make this plugin work for everyone but a low-end hosting solution is something we have no control over & can't support.  Our only other option would be to bypass the Wordpress nonce system but given the nature of what our plugin does security is something we can't sacrifice just to make it work with cut-rate hosts.
                                                </p>
                                                <p>
                                                    Here are some common cache plugins that might cause the nonce to be cached longer than 12 hours:
                                                    <ul>
                                                        <li>W3TC Total Cache</li>
                                                        <li>WP Super Cache</li>
                                                        <li>WP Rocket Cache</li>
                                                        <li>Autoptimize</li>
                                                        <li>WP Fastest Cache</li>
                                                        <li>Comet Cache</li>
                                                        <li>Cloudflare (CDN)</li>
                                                        <li>Other CDN plugins</li>
                                                        <li>Some themes & frameworks also include their own cache</li>
                                                    </ul>
                                                </p>
                                                <p>
                                                    To troubleshoot what might be causing the nonce issue, disable each plugin one by one starting with any of the above and check to see if the nonce error goes away.
                                                </p>
                                                <p>
                                                    One you determine which plugin is causing the error you can then (usually) tweak the settings within that plugin so the cache expires before 12 hours.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Long Forms Submit But Doesn't Show Form Data in Submitted Forms View</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    This is most likely due to the size of the form exceeding your max_input_vars size set in your PHP.ini file.  Increasing the value to 3000 or higher should solve this issue.
                                                </p>
                                                <p>
                                                    If you are not allowed access to your PHP.ini setting on your hosting account or are unsure how to change these settings there are some free 3rd party plugins available in the Wordpress plugin repository that will allow you to change these settings from the Wordpress admin dashboard.
                                                </p>
                                                <p>
                                                    If increasing you max_input_vars size does not solve the issue please submit a support ticket.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Form Submits Successfully but Give Email Error Notice</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    This is usually an issue of a malformed email address set either in the HIPAA Forms settings or in the Caldera or Gravity form notification settings.
                                                </p>
                                                <p>
                                                    A common reason you get this is if you're trying to use a magic tag to dynamically set the email (ie. {email:7}).  Magic tags are not supported by our plugin currently except for the default {admin} tag.
                                                </p>
                                                <p>
                                                    If you're not using a magic tag and getting this error double check to ensure the email is properly formatted (Example: test@test.com).
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Submitted Forms Don't Load</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    A common reason for this is if you have extremely long forms and are on a cheap shared hosting account that is running mod_security blocking large data from being passed via AJAX.  We've seen this with users on GoDaddy's shared hosting packages and even upgrading to their top plan will not solve this as mod_security will continue blocking the large data set and they are unable to disable or adjust the mod_security settings for you as this would effect all of the other websites being hosted on your node.  This shouldn't be an issue however on a VPS hosting package or if it is GoDaddy should be able to adjust the mod_security settings for you on a VPS.
                                                </p>
                                                <p>
                                                    A good way to test if this is the case is to change the "per page" option to 1 and then hit the search button.  If a form loads fine with only 1 per page but not at 5 per page then this is most likely the problem.
                                                </p>
                                                <p>
                                                    Our first recommendation is to shorten or split your forms.  If your forms are so long that they trigger mod_security when you try to load 5 of them then most likely they are extremely painful for your patients to fill out.  If you can't shorten the number of fields in your form then consider splitting the form up into multiple separate forms.  If you split your form up you can set the form to automatically redirect to the next form after submission.  This will not only reduce the amount of data that needs to be passed for each form but will also make things easier on your patients and increase the chances that they'll at least submit the first form or two before they get distracted or tired of it.  They can always come back and fill out the remaining forms if needed without having to re-complete what they've already submitted.
                                                </p>
                                                <p>
                                                    If shortening or splitting up your form isn't an option our second recommendation would be to move to a VPS hosting solution so you can have more control over your hosting settings.  An economy VPS package from GoDaddy is $29.99/mo which is the same price as their business shared hosting package.
                                                </p>
                                                <p>
                                                    If your submitted forms don't even load when the per-page is set to 1 then please submit a support ticket.  We will most likely also need a temporary admin login in this case to debug what is causing the issue.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Caldera/Gravity Add-On's Not Working</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    Unfortunately many of the Caldera & Gravity add-on's won't work with the HIPAA Forms plugin.
                                                </p>
                                                <p>
                                                    The reason for this is due to how the HIPAA Forms plugin works by over-riding the default form submission process in order to make your forms HIPAA compliant.
                                                </p>
                                                <p>
                                                    The default Caldera & Gravity submission process is designed to either email or save the form data on your hosting server, both would be a HIPAA violation in most cases.  Instead, our plugin replaces the default submit button with our own and completely bypasses the default process entirely, encrypts your form and passes it to our API to be stored on our HIPAA compliant database.
                                                </p>
                                                <p>
                                                    Since most add-on's rely on the default submission process they simply won't work with our plugin.
                                                </p>
                                                <p>
                                                    In some instances there is a work-around but requires you to split your form into two separate forms, the first being your HIPAA compliant form that takes the protected health information and the second being a non-HIPAA compliant form that does whatever else you need to do using your add-on.  You can then set either your HIPAA compliant form's success handler as either a redirect to your second form or use a more advanced callback option in order to pass non-PHI values from the first form to the second.
                                                </p>
                                                <p>
                                                    One very common use case for this workaround is if you need to take a payment with your form.  Since the add-on won't work with a HIPAA compliant form you can redirect to your payment form on submit success.
                                                </p>
                                                <p>
                                                    We know this two-form UI isn't ideal and we're currently exploring ways to integrate payment processing but for now this is really the only way we know of in order to accept payments in conjunction with a HIPAA compliant form.
                                                </p>
                                                <p>
                                                    For more information on how to create callback function see "How Do I Create a Callback" under the Common Questions section below.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">File Upload Issues</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    The default file upload functionality from Caldera or Gravity Forms uploads files directly to your hosting server unsecurely which of course is a HIPAA violation.
                                                </p>
                                                <p>
                                                    We released a secure HIPAA compliant file upload add-on option to our HIPAA Forms plugin in early 2019 which over-rides the default file upload behavior and instead pushes the files directly from the form submitter's browser to our secure file storage solution.  This completely bypasses your hosting server ensuring no files containing PHI are ever stored on your server.
                                                </p>
                                                <p>
                                                    You must have the file upload option added to your API subscription in order to use the file upload feature.  You can upgrade your subscription by logging into your account at https://www.hipaaforms.online/my-account.
                                                </p>
                                                <p>
                                                    Both Caldera & Gravity have different file upload field options which include single file and multi-file uploads.
                                                </p>
                                                <p>
                                                    Our file upload add-on is designed to work with both field types however it's important to note that we completely replace the multi-file upload field with our own in order to securely push the file to our API.  This means that the fancy drag 'n drop functionality will not be displayed with forms selected as HIPAA compliant.
                                                </p>
                                                <p>
                                                    When a form is submitted with a file the file is uploaded first and the form will display a message under the submit button displaying the submission status as "uploading files".  Once the files complete the upload process the status will then display "submitting form".  Once the form completes the submission process the selected success handler set in your form settings will fire showing the success message, redirect to another URL or fire a callback function.
                                                </p>
                                                <p>
                                                    To view the uploaded files with your form in the admin dashboard expand the submitted form from the list and click on the "files" tab.  This will display a list of links for all of the files submitted with the form.  These links are dynamically generated when you load the file and are set to expire after 1 hour to help further secure access to these files.  If the links have expired simply refresh the page, re-open the form and new access links will be generated.
                                                </p>
                                                <p>
                                                    File upload issues are usually presented one of two ways.  The first being the form submission gets stuck on "uploading files" and never submits, or the form submits without errors but the files are not displayed in the admin interface usually indicating the files did not upload at all.
                                                </p>
                                                <p>
                                                    If you experience issues uploading files you should submit a support ticket or give us a call at 715.941.1040.  This is usually due to something not getting set correctly on our end and not due to any type of configuration or error on your part.
                                                </p>
                                                <p>
                                                    <strong>* CALDERA USERS:</strong> Caldera recently released a new advanced file upload 2.0 field which has a completely different structure and behavior than it's non-advanced version and presents some challenges to how we can over-ride it.
                                                </p>
                                                <p>
                                                    The advanced 2.0 file upload field is appended to the form AFTER the page loads via Javascript which is also how our plugin over-rides the form.  This opens up the possibility for their script to run a little slower than ours and in turn append their field AFTER ours has already appended it.
                                                </p>
                                                <p>
                                                    While our plugin SHOULD over-ride their advanced file upload 2.0 field, there is always that possibility that their field will still be appended as well which in turn opens up the possibility for a person submitting the form to upload a file unsecurely directly to your hosting server.
                                                </p>
                                                <p>
                                                    We HIGHLY recommend NOT using the advance file upload 2.0 field on forms set as HIPAA compliant.  The traditional field type still allows single or multi-file upload capability and does not present the potential danger the new advanced field type does.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Forms Don't Submit, Progress Bar Never Stops - Wordfence</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    We've had reports that Wordfence blocks the HIPAA Form submissions in some instances.
                                                </p>
                                                <p>
                                                    Since this blocks the API call no data is ever returned and the progress bar will continue loading forever.
                                                </p>
                                                <p>
                                                    If you log in as an administrator and submit the form a popup should come up from Wordfence asking if you would like to whitelist the action.  Clicking "yes" should resolve the problem.
                                                </p>
                                                <p>
                                                    In some cases the popup is never shown, most likely due popup blockers enabled in the browser.  If you're unable to get the popup placing wordfence into learning mode should resolve the issue.
                                                </p>
                                                <p>
                                                    If you're still unable to fix the problem please disable Wordfence and confirm if the form is able to be submitted or not.  If the form still does not submit with Wordfence disabled please submit a support ticket and indicate that disabling Wordfence does not resolve the issue.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">HIPAA User Role Unable to Access Admin Dashboard</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    By default the hipaa_user role has no permissions set.
                                                </p>
                                                <p>
                                                    In order to access the admin dashboard you MUST set at least one permission.
                                                </p>
                                                <p>
                                                    From the HIPAA Forms plugin interface click on the settings tab and then select the "user permissions" sub-tab.
                                                </p>
                                                <p>
                                                    In most cases where you only want the users with the hipaa_user role to access the HIPAA Forms interface and nothing else you can check the "Read Documents" permission.
                                                </p>
                                                <p>
                                                    This will allow them to access the admin dashboard and the HIPAA Forms interface but will not get access to other options such as plugins, posts and pages.
                                                </p>
                                                <p>
                                                    *NOTE: Since we have no control over how other plugin developers set their user permissions some 3rd party plugins may be visible in the left menu.
                                                </p>
                                            </div>
                                        </div>

                                        <h2>COMMON QUESTIONS</h2>
                                        <div class="cm-hipaa-forms-faqs-item">
                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">What is a BAA?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    A Business Associate Agreement (BAA) typically is required for companies that are subject to the Health Insurance Portability and Accountability Act (HIPAA) to ensure that protected health information (PHI) is appropriately safeguarded. Failure to manage data privacy risks with non-business associate vendors may lead to both violations of HIPAA and state privacy laws.
                                                </p>
                                                <p>
                                                    You will be unable to use the HIPAA FORMS Service until you have signed the BAA with Code Monkeys LLC (the developers of the service) and will receive a notice to do so within the "submitted forms" tab as well as in the settings tab until it has been signed.
                                                </p>
                                                <p>
                                                    We HIGHLY recommend that you have a BAA in place with your web designer as well if you use a 3rd party contractor for web design service.
                                                </p>
                                            </div>

                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Can We Modify/Extend This Plugin?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    WE STRONGLY RECOMMEND NOT MODIFYING THE FUNCTIONALITY OF THIS PLUGIN!
                                                </p>
                                                <p>
                                                    This plugin is released under the GPL license and is open source allowing you to modify the plugin however we strongly recommend against attempting to modify the core functionality of the plugin.  The plugin simply acts as an interface to the API service where most of the "under the hood" functionality lives however some functionality such as encryption prior to sending the form data to the API happens within the plugin.  Breaking or disabling this encryption process could result in non-encrypted private protected sensitive health information being submitted which would be a HIPAA violation and may lead to both violations of HIPAA and state privacy laws.
                                                </p>
                                                <p>
                                                    While we recommend not modifying the core functionality of the plugin changing the CSS/Styles is totally fine and recommended.
                                                </p>
                                            </div>

                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Can Images/Files be Attached to Forms?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    Yes.
                                                </p>
                                                <p>
                                                    In order to upload files with your forms you will need to have the file upload add-on option added to your HIPAA Forms API subscription.
                                                </p>
                                                <p>
                                                    You can upgrade your subscription by logging into your account at <a href="https://www.hipaaforms.online/my-account" target="_blank">https://www.hipaaforms.online</a>.
                                                </p>
                                            </div>

                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Why is it Logged Every Time Someone Goes to the HIPAA FORMS Admin Dashboard?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    A part of the HIPAA guidelines is that access logs are kept each time someone has access to protected health information.
                                                </p>
                                                <p>
                                                    This allows you to look back through the logs to see who accessed the information during a specific time period in case you suspect a violation of policy or data breach.
                                                </p>
                                                <p>
                                                    This log data is saved in the HIPAA FORMS Service database to ensure the integrity of the data and may be shared with investigators if requested by authorities if a potential data breach or violation is suspected.
                                                </p>
                                            </div>

                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">How Do I Create a Callback?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    Sometimes you need something more than just showing a success message or redirecting to another page after a form is submitted.
                                                </p>
                                                <p>
                                                    A common scenario where a callback is needed is if you want to redirect to another form AND pass some values from the first form to prepopulate the second.  This is especially useful if you need to take a payment from someone after the first form is submitted and you want to pre-populate the payment form with the submitter's information already entered in the first form.
                                                </p>
                                                <p>
                                                    There are a few important things to keep in mind if you decide to use the callback option:
                                                </p>
                                                <p>
                                                    1. You need to have at least a basic understanding of Javascript/jQuery<br /><br />
                                                    2. Your callback functions should be created in a Javascript file within your THEME, not from the plugin.  If you edit a file in the plugin it will be overwitten when you update the plugin.<br /><br />
                                                    3. <b>NEVER pass field values from fields that may contain PHI!</b>  While this plugin has been designed to keep your forms HIPAA compliant, this option is one area where you could ultimately expose your form data and in turn violate HIPAA regulations.  Passing identifier values through this method such as name, email, phone, address, etc is fine as well as other non-health related values but never pass any value that contains health information.<br /><br />While simply passing PHI from one form to another through the URI while under SSL may not be a HIPAA violation, the fact that you're passing it to another insecure form means that data could ultimately end up being emailed or stored on your hosting server when that form is submitted which most likely would be a HIPAA violation.<br /><br /><b>It is your responsibility to ensure you're follwing HIPAA guidelines when using this option!</b>
                                                </p>
                                                <p>
                                                    A basic example would look something like this...
                                                </p>
                                                <p>
                                                    From a js file in your theme:
                                                </p>
                                                <p>
                                                    function myFormCallback() {<br />
                                                        var valueOne = jQuery('#fieldid1').val(); // GET FIELD1 VALUE FROM FORM<br />
                                                        var valueTwo = jQuery('#fieldid2').val(); // GET FIELD2 VALUE FROM FORM<br />
                                                        var valueThree = jQuery('#fieldid3').val(); // GET FIELD3 VALUE FROM FORM<br /><br />
                                                        // REDIRECT TO FORM PAGE WITH PARAMS<br />
                                                        window.location.href('https://www.mysecondformpage.com?field1=' + valueOne + '&field2=' + valueTwo + '&field3=' + valueThree);<br />
                                                    }
                                                </p>
                                                <p>
                                                    From the HIPAA Forms plugin form settings you would set the submit success handler as "success callback" and in the first field for function name put myFormCallback.  In this example you're not passing any parameters to the function so you can just leave the second input empty.
                                                </p>
                                                <p>
                                                    If you wanted to make the function more ambiguous and reusable from different forms you could pass the field id's as params in that second input and then from your function add them like myFormCallback(fieldId1, fieldId2, etc) and then get your values dynamically from within the function like var valueOne = jQuery('#' + fieldId1).val();.
                                                </p>
                                            </div>

                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">How Do I Style Submitted Forms?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    You can style both the web and PDF versions of your submitted forms by using the CSS editor built into the HIPAA Forms plugin.
                                                </p>
                                                <p>
                                                    Click on the "Settings" tab above and then click on the "Forms CSS" sub-tab.
                                                </p>
                                                <p>
                                                    Below the CSS editor there are some basic instructions on how to apply CSS to either the web view or the PDF version of the submitted forms.
                                                </p>
                                                <p>
                                                    Remember, these CSS styles <b>ONLY APPLY TO SUBMITTED FORMS</b>.  This is not intended to style the front-end patient facing form that your users actually submit, those styles should be handled from within your theme's stylesheet.
                                                </p>
                                                <p>
                                                    The HIPAA Forms plugin saves your entire form, including HTML.  It simply replaces your inputs with the values, otherwise whatever HTML structure, classes, ID's, etc you have in your front-facing form will be saved and then displayed in your submitted form view.
                                                </p>
                                                <p>
                                                    Saving your forms this way gives you the ability to hook into whatever classes, ID's, etc in your form from the CSS editor in order to style the forms however you want.
                                                </p>
                                                <p>
                                                    An easy way to view your submitted form's HTML structure is to right click on the web view version of your submitted form and select "inspect element".  Most modern browsers have this ability.  From within this element inspector view you can easily find what classes and ID's are associated with different parts of your form which you can then use within the CSS editor to change the style of those elements.
                                                </p>
                                                <p>
                                                    You'll notice that within the element inspector is two sections.  One section displays the HTML of your form while the other displays the CSS for the specific element selected from the HTML view.  You can change the CSS values directly from the inspector to see how different styles would look as changes will appear on your page.  Remember that changing the CSS styles from your inspector does not actually change how the form looks for anyone else and can not be saved from the inspector.  Once you refresh the page any changes you've made in the inspector will be lost and your form will look like it did before.
                                                </p>
                                                <p>
                                                    The ability to edit styles from the element inspector in your browser is only a way to test how changes would look.  However, once you have your form looking the way you want from the inspector you can then copy/paste those changes into the HIPAA Forms CSS editor and save them.
                                                </p>
                                                <p>
                                                    If you need more granular control over the look of specific fields or elements you can add custom HTML, classes and ID's to your form from the Caldera or Gravity form builder interface.
                                                </p>
                                                <p>
                                                    Styling the PDF version of the submitted forms is a little trickier than styling the web view version.  The HIPAA Forms plugin relies on MPDF to convert the HTML into a PDF and while MPDF does use CSS not all CSS works as you would expect.  Unfortunately styling your PDF version of your form will most likely take some back and forth of making a CSS change and then creating a PDF to see how it looks.  Unlike the web view version, there is no way to inspect the element of your PDF or to test CSS changes from the browser.  We've included a link to the MPDF documentation below the CSS editor for more information on what you can and can't do with CSS in MPDF.
                                                </p>
                                                <p style="font-style:italic;">
                                                    If you don't know how to work with CSS and you don't currently have a web designer that you can work with, Code Monkeys does offer a form design service.  Please call us at <a href="tel:7159411040" target="_blank">715.941.1040</a> or message us online at <a href="https://www.hipaaforms.online/support" target="_blank">hipaaforms.online/support</a> to request a quote.  Pricing varies depending on the size and complexity of the needed form design.
                                                </p>
                                            </div>

                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">Why Can't Form Data be Emailed?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    Sending E-PHI (protected health information) via standard email is not secure and is a HIPAA violation.
                                                </p>
                                                <p>
                                                    Even if your email uses TLS it's not enough to be HIPAA compliant.  Considering TLS is a protocol, both mail servers need to have TLS in order for the encryption process to work and there's not absolute way of know for sure if the person you're emailing is using TLS or not.  Even if you were 100% positive that the person's email server you're sending to has TLS it can still fail leaving your E-PHI vulnerable to middle-man exploits.
                                                </p>
                                                <p>
                                                    We can not stress enough that emailing E-PHI is very dangerous and insecure.  This is one of the most common HIPAA violations we see and one of the easiest ways to get yourself into trouble.
                                                </p>
                                                <p>
                                                    Also remember that any 3rd party entity that could potentially handle or access PHI is required to have a BAA (business associates agreement) in place.  This means whatever company not only your email server is with but also the person's email server that you're sending to should have a BAA in place.  This would mean trying to get a BAA in place with companies such as Google, Microsoft, Amazon, GoDaddy and thousands of other smaller companies that you or the person you're emailing to uses.  Obviously this isn't realistic (or probably even possible in many cases).
                                                </p>
                                                <p>
                                                    Another concern we have with emailing E-PHI is the fact that many of us have poor email habits and many of us leave our email clients running constantly on our computers.  Even if everything else is secure you could still end up with a HIPAA violation if the person you emailed leaves that email open and someone gains access to the computer.  This is actually a concern with any medium containing PHI, even paper forms but we're more likely to leave an email client open on our computer in the open then we are to leave a paper patient form out in the open.
                                                </p>
                                                <p>
                                                    DO NOT EMAIL PATIENT HEALTH INFORMATION.  Simple as that.
                                                </p>
                                                <p>
                                                    There are special email options available that are HIPAA compliant such as a system using Pretty Good Privacy (PGP) data encryption which requires a key for every single person you contact.  Since every person needs an encryption key PGP systems are unrealistic outside of an internal staff solution.  That said, we are looking into adding the ability to include a PGP encryption key and integrate email capability into the HIPAA Forms service for those already using a PGP email solution but we don't know when or if this will be implemented.
                                                </p>
                                                <p>
                                                    Another special email option is to use a self-contained portal system but this type of solution requires the person you're emailing to follow a link from a generic email in order to register and then log into your portal in order to access the actual information.  This is basically how the HIPAA Forms service already works by sending you a generic email notification when a form is submitted but requiring you to log into your WP admin dashboard in order to view the secured form.
                                                </p>
                                                <p>
                                                    <strong>* IMPORTANT!</strong>
                                                </p>
                                                <p>
                                                    While we give you the ability to pass the first name, last name, phone, email and form name from the form to your email notification, it's not always a good idea to pass all of those things together.  In fact, in some cases submitting all of these together could potentially be seen as an intent for service and in ture itself be considered PHI.
                                                </p>
                                                <p>
                                                    <strong>BAD EXAMPLE:</strong><br />
                                                    Form Name: New Patient Form<br />
                                                    Name: John Smith<br />
                                                    Email: jsmith@email.com
                                                </p>
                                                <p>
                                                    The bad example above indicates that John Smith is a new patient and is in fact seeking/receiving treatment.  This could be a HIPAA violation.
                                                </p>
                                                <p>
                                                    <strong>GOOD EXAMPLE:</strong><br />
                                                    Form Name: Contact Form<br />
                                                    Name: John Smith<br />
                                                    Email: jsmith@email.com
                                                </p>
                                                <p>
                                                    The good example above simply shows that John Smith has contacted you without any context as to why so would not be considered to show intent for treatment.
                                                </p>
                                                <p>
                                                    Of course the safest way to handle this is to not pass the identifiers with the form name at all.
                                                </p>
                                                <p>
                                                    At the end of the day the email notification is just a way of alerting you that a form has been submitted and that you should log into your dashboard to view the actual form data.  While we understand that having some information in that notification can be handy and that clients are used to this and may want this, it's probably more of a want than a necessity.  We've found that once you explain why passing this information into a notification email is a bad idea and a potential HIPAA violation, almost everyone gets it and is perfectly fine without it.
                                                </p>
                                                <p>
                                                    A final thought on this is to remember that our HIPAA Forms plugin and API service is a tool to help keep yourself or your clients HIPAA compliant no different than a hammer is a tool to drive nails into wood.  Even a hammer used incorrectly can lead to trouble so you still need to use common sense and educate yourself on how to use a tool properly.
                                                </p>
                                            </div>

                                            <h4 class="cm-toggle cm-hipaa-forms-faqs-title">What Other Features are You Working On?</h4>
                                            <div class="cm-toggle-content cm-hipaa-forms-faqs-content">
                                                <p>
                                                    1. File Upload Capability
                                                </p>
                                                <p>
                                                    2. Save for Later Capability
                                                </p>
                                                <p>
                                                    3. Integrated Appointment Management
                                                </p>
                                                <p>
                                                    4. A patient portal to allow your patients to register and log into a secure portal on your website with real-time two-way communication between the provider and patient as well as the ability to view secure documents and forms submitted by the patient.
                                                </p>
                                                <p>
                                                    5. We're exploring integrating a secure HIPAA compliant 2-way video platform into the patient portal to enable healthcare providers to do real-time video visits/screenings.
                                                </p>
                                                <p>
                                                    6. We're exploring ways to integrate the HIPAA Forms plugin and service with other EHR management systems.
                                                </p>
                                                <p>
                                                    7. We plan to release a stand-alone SaaS platform to enable providers that do not use Wordpress to use our service as well providing an alternative method of accessing forms to existing Wordpress users other than logging into their Wordpress admin dashboard.
                                                </p>
                                                <p>
                                                    Our goal is to have many if not all of these things released in 2019.
                                                </p>
                                            </div>
                                        </div>
                                    </div>-->
                                </div>
                            </div>
                            <section data="tour-tab-3" class="cm-hipaa-accordion-tab-link cm-hipaa-active-accordion-tab-link">
                                <div class="cm-hipaa-tour-tab-link-inner">
                                    <i class="material-icons">message</i> <span class="cm-hipaa-tab-name">TICKETS</span>
                                </div>
                            </section>
                            <div data="tour-tab-3" class="cm-hipaa-tour-tab-content">
                                <!--
                                <div class="cm-hipaa-forms-tickets-wrapper cm_hipaa_grid_row">
                                    <div class="cm_hipaa_col_33">
                                        <h2>Submit a Ticket</h2>
                                        <div class="cm-hipaa-forms-submit-tickets">
                                            <div class="cm-hipaa-forms-submit-ticket-input">
                                                <select id="cm-hipaa-forms-submit-ticket-priority">
                                                    <option value="">PRIORITY</option>
                                                    <option value="low">Low</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="high">High</option>
                                                </select>
                                            </div>
                                            <div class="cm-hipaa-forms-submit-ticket-input">
                                                <select id="cm-hipaa-forms-submit-ticket-channel">
                                                    <option value="">REASON</option>
                                                    <option value="Help">Help</option>
                                                    <option value="Bug">Bug</option>
                                                    <option value="Feature Request">Feature Request</option>
                                                </select>
                                            </div>
                                            <div class="cm-hipaa-forms-submit-ticket-input">
                                                <input type="text" id="cm-hipaa-forms-submit-ticket-subject" placeholder="Subject..." />
                                            </div>
                                            <div class="cm-hipaa-forms-submit-ticket-input">
                                                <textarea id="cm-hipaa-forms-submit-ticket-message" placeholder="Message..."></textarea>
                                            </div>
                                            <div id="cm-hipaa-forms-submit-ticket" class="cm-button">
                                                SUBMIT
                                            </div>
                                            <div id="cm-hipaa-forms-submit-ticket-notice"></div>
                                        </div>
                                    </div>
                                    <div class="cm_hipaa_col_33">
                                        <h2>Open Tickets</h2>
                                        <div id="cm-hipaa-forms-open-tickets"></div>
                                    </div>
                                    <div class="cm_hipaa_col_33">
                                        <h2>Closed Tickets</h2>
                                        <div id="cm-hipaa-forms-closed-tickets"></div>
                                    </div>
                                </div>
                                -->
                                <script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
                                <style type="text/css" media="screen, projection">
                                    @import url(https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.css);
                                </style>
                                <iframe title="Feedback Form" class="freshwidget-embedded-form" id="freshwidget-embedded-form" src="https://codemonkeysllchelp.freshdesk.com/widgets/feedback_widget/new?&widgetType=embedded&screenshot=No&captcha=yes" scrolling="no" height="600px" width="100%" frameborder="0" >
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SETTINGS INFO MODALS -->
            <div class="cm-hipaa-setting-info-modal" data-content="license-key-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>LICENSE KEY FIELD</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            This plugin relies on the HIPAA Web Forms API which requires a subscription license key.  You can purchase a license key <a href="https://www.hipaaforms.online" target="_blank">HERE</a>.
                        </p>
                        <p>
                            If you have already purchased a subscription your license key should have been emailed to you.  If you have not received the email with your license key please submit a support ticket from the support tab or call 715.941.1040.
                        </p>
                        <p>
                            Once you add your license key click "Save Changes" below, the page will refresh & will then ask for you to open and sign the BAA agreement.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="notification-email-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>NOTIFICATION EMAIL FIELD</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            This sets a "master" email to receive form submission notifications.
                        </p>
                        <p>
                            If "fallback only" is NOT checked then this email will receive submission notifications IN ADDITION to any emails set as the "send to" within your forms.
                        </p>
                        <p>
                            If "fallback only" IS checked then this email will act as a fallback & only receive form submission notifications if no email is set as the "send to" email within your form or if there was a problem with the email address set in your form.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="form-builder-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>FORM BUILDER FIELD</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            This plugin allows you to build your forms within Caldera or Gravity forms just as you would any other form.
                        </p>
                        <p>
                            If you do not have either of these form builders you can get Caldera Forms for free <a href="https://calderaforms.com/?thnx=116" target="_blank">HERE</a>.
                        </p>
                        <p>
                            Once either Caldera or Gravity Forms is installed and activated it should appear in this select list, simply select it and hit save.
                        </p>
                        <p>
                            After a form builder has been selected all of the forms that have been created will appear within the "Form Settings" tab below where you can select which forms you wish to be HIPAA compliant as well as set some form specific options.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="timezone-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>TIMEZONE FIELD</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            Form submission & support ticket times are saved as UTC time so this field allows you to display times within your preferred timezone.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="user-role-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>HIPAA USER ROLE</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            An additional "HIPAA" user role has been created to allow approved staff to access the submitted HIPAA forms without allowing them to change the plugin settings or access other things within the Wordpress admin dashboard that you don't want accessible.
                        </p>
                        <p>
                            By checking these options you are allowing users with the HIPAA role to do these things within the Wordpress admin dashboard.
                        </p>
                        <p>
                            Remember that this only applies to users with the HIPAA user role & does not effect users with an administrator role.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="baa-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>BAA</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            HIPAA regulations require that a BAA be in place between you (the "covered entity") & Code Monkeys (the "business associate"), or if you're a web designer working on behalf of a client the you (the "business associate") & Code Monkeys (the "business associate subcontractor).
                        </p>
                        <p>
                            This is designed to ensure that you obtain satisfactory assurances from Code Monkeys LLC that we will appropriately safeguard the protected health information on behalf of you.
                        </p>
                        <p>
                            The included BAA is a standard agreement modeled after the BAA we have in place with Amazon AWS which provides the HIPAA compliant databases used with our API.
                        </p>
                        <p>
                            If you are a web designer implementing this plugin & API service on behalf of your client you can sign this BAA with us but you must also have a BAA in place between yourself & your client.  There must be an unbroken chain from the actual covered entity all the way through.
                        </p>
                        <p>
                            If your company has it's own BAA that it would rather use instead of the one provided you can email it to us to review & sign instead.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="signature-settings-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>SIGNATURE FIELD</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            Check this field if you wish to display the integrated "drag 'n draw" signature field at the end of your form.
                        </p>
                        <p>
                            If the signature field is checked it will be a required field and will not allow the form to be submitted until signed.
                        </p>
                    </div>
                </div>
            </div>
            <!-- submit-button-text-info -->
            <div class="cm-hipaa-setting-info-modal" data-content="submit-button-text-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>SUBMIT BUTTON TEXT</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            By changing this text you will overwrite the default submit button icon and text. You may enter plain text or HTML into the text box, be aware that entering HTML may have unexpected results.
                        </p>
                        <p>
                            Icons may be added to the submit button in the following format:<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&#60;i class='material-icons'>name_of_icon&#60;/i&#62; Submit_text<br>
                            <br>
                            Examples:<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&#60;i class='material-icons'>lock&#60;/i&#62; Submit &nbsp;&nbsp; = &nbsp;&nbsp; <i class='material-icons'>lock</i> Submit <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&#60;i class='material-icons'>payment&#60;/i&#62; Continue to Payment &nbsp;&nbsp; = &nbsp;&nbsp; <i class='material-icons'>payment</i> Continue to Payment <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;&#60;i class='material-icons'>check_circle_outline&#60;/i&#62; Finish &nbsp;&nbsp; = &nbsp;&nbsp; <i class='material-icons'>check_circle_outline</i> Finish
                        </p>
                        <p>
                            The 'material-icons' class is the only class that is currently supported with-in the plugin.<br>
                            To add an icon use the 'Filled' icons from the following page <a href="https://fonts.google.com/icons" target="_blank">https://fonts.google.com/icons</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="success-handler-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>SUCCESS HANDLER</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            You can set what a form does after it has been submitted here.
                        </p>
                        <p>
                            By default the form will display a message at the bottom of the form & can be customized to say whatever you put in this field.
                        </p>
                        <p>
                            The second option is to redirect to another page.  To do this select the "Success Redirect" option and put the url of the page you would like to redirect to in the field.
                        </p>
                        <p>
                            The third option is to specify a Javascript callback function.  To use this select the "Success Callback" option & add the function name to the first input & any paramaters that should be passed into the 2nd input.  This should only be used by a developer & tested well before being used in a live site.
                        </p>
                        <p>
                            SEE "HOW DO I CREATE A CALLBACK?" IN THE FAQ SECTION UNDER THE SUPPORT TAB FOR HOW TO USE THIS
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="user-specific-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>WHO CAN VIEW</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            By default everyone with the administrator & HIPAA user roles can view a form.
                        </p>
                        <p>
                            This can be changed to "Only Specific Users" & then only the users that should see this submitted form can be checked.
                        </p>
                        <p>
                            You can also change this to "Selected User".  This enables you to create a select field on your form to allow someone to select a specific doctor.
                        </p>
                        <p>
                            To setup a "Selected User" field add a select field to your form & add the doctor's names as the labels & their user ids as the values & give it a specific slug if using Caldera or a specific class if using Gravity.  Then from the form settings here add the slug or class to the input field.
                        </p>
                        <p>
                            Once the field is setup and saved within the forms settings here only the selected doctor will see the submitted form.  Remember this only applies to users with the HIPAA role, administrators can still see all forms.  If you need to reassign a form to someone else an administrator can do so from the submitted forms view.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="privacy-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>PRIVACY NOTICE</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            This plugin will append a required checkbox field before the submit button and must be checked for a user to submit the form.
                        </p>
                        <p>
                            We've included a default generic HIPAA privacy notice below to help you get started however this should not be used on your live site.  You should create your own privacy notice here instead.  You should include your company name & full address, terminology definitions & how you will handle the collection, use, security, destruction, etc of their private information.
                        </p>
                        <p>
                            While this plugin requires you set a HIPAA privacy notice & requires a user agree to it before submitting a form, Code Monkeys LLC is not responsible for the content of your privacy notice.  It is up to you to ensure the privacy notice satisfies all state & federal regulations including HIPAA (or PIPEDA if in Canada).
                        </p>
                        <p>
                            By default this statement is designed to open in a popup modal window when the checkbox field link is clicked, however you can change this to open another page in a new browser tab instead.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="notification-email-editor-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>NOTIFICATION EMAIL</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            This is where you customize the master notification email settings for the notification email sent after a form is submitted.
                        </p>
                        <p>
                            These master settings can be over-ridden from a specific form's settings options under the "Form Settings" sub-tab above & clicking on the triangle icon to expand the form setting options.
                        </p>
                        <p>
                            If you do NOT want notification emails sent at all check the "disable email notifications" option.  This will disable email notifications entirely.
                        </p>
                        <p>
                            E-PHI can not be passed via email, at all, ever!  This is why we can't enable using the default form builder notification options as private health information could be passed.
                        </p>
                        <p>
                            We do however enable you to pass a few non-PHI values such as form name, first name, last name, email & phone as well as the option location value.  The placeholders to use for these values can be found under the text area field.
                        </p>
                        <p>
                            *IMPORTANT! Passing identifier fields along with your form name could potentially be considered an intent for service & in turn be a HIPAA violation.<br /><br />
                            A form name such as "contact us" is perfectly fine as that would not be perceived as an intent for service.<br /><br />
                            A form name such as "New Patient Registration" would be perceived as an intent for service and in turn be a HIPAA violation.<br /><br />
                            We recommend passing as little information as possible in your notification emails.
                        </p>
                        <p>
                            HTML added here should be fine however you should make sure your HTML will render well across multiple email platforms such as gMail, Outlook, Thunderbird, etc.  You should always use traditional tables & we recommend testing in something like Litmus to ensure the email will render as expected.
                        </p>
                        <p>
                            DO NOT USE MAGIC TAGS (ie {email}) in the from name, from email & send notification to fields!  You must use a valid email format such as me@mydomain.com or else the mail function will break.
                        </p>
                        <p>
                            We recommend using an email address in the "from email" field with the same domain as your site to prevent the email being seen as phishing.  If you must use an email address with a different domain (such as @gmail.com) we receommend installing a 3rd party SMTP plugin and setting up your email to connect via SMTP to ensure deliverability.  Note however that some hosting providers do not allow outgoing SMTP connections so this may not be an option on your particular hosting environment.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="form-specific-notification-info">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>CUSTOM NOTIFICATION EMAIL</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            This field enables you to select if you want the email notification after a form is submitted to use the default email subject and message or if you would rather set a custom notification email for this specific form.
                        </p>
                        <p>
                            E-PHI can not be passed via email, at all, ever!  This is why we can't enable using the default form builder notification options as private health information could be passed.
                        </p>
                        <p>
                            We do however enable you to pass a few non-PHI values such as form name, first name, last name, email & phone as well as the option location value.  The placeholders to use for these values can be found under the text area field.
                        </p>
                        <p>
                            HTML added here should be fine however you should make sure your HTML will render well across multiple email platforms such as gMail, Outlook, Thunderbird, etc.  You should always use traditional tables & we recommend testing in something like Litmus to ensure the email will render as expected.
                        </p>
                    </div>
                </div>
            </div>
            <div class="cm-hipaa-setting-info-modal" data-content="status-options">
                <div class="cm-hipaa-setting-info-modal-inner">
                    <div class="cm-hipaa-setting-info-modal-top">
                        <div class="cm-hipaa-setting-info-modal-title"><h3>FORM STATUS OPTIONS</h3></div>
                        <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="cm-hipaa-setting-info-modal-body">
                        <p>
                            Enabling this option allows you to set a custom status message on your submitted forms visible above the left icons in the list view.
                        </p>
                        <p>
                            This can be useful for those with multiple staff working with the submitted forms that need to quickly see what the status of the form is without expanding the form.
                        </p>
                        <p>
                            This option was designed in a way that allows you to add whatever custom status options you want in order to fit into your workflow.
                        </p>
                        <p>
                            Once you check the box to enable this feature and add your custom status options there will be a select box under the sub-tabs in the expanded view of the form.
                        </p>
                        <p>
                            To update the form status simply change the option in this select field and the form will update with the new status.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="cm-hipaa-setting-info-modal" data-content="office-location-info">
            <div class="cm-hipaa-setting-info-modal-inner">
                <div class="cm-hipaa-setting-info-modal-top">
                    <div class="cm-hipaa-setting-info-modal-title"><h3>OFFICE LOCATION FILTER</h3></div>
                    <div class="cm-hipaa-setting-info-modal-close"><i class="material-icons">cancel</i></div>
                    <div class="clearfix"></div>
                </div>
                <div class="cm-hipaa-setting-info-modal-body">
                    <p>
                        The office location filter lets you filter forms by a specific office location.
                    </p>
                    <p>
                        *NOTE:<br />You may not see all of your locations in the select options, only locations with submitted forms associated with them will be displayed.
                    </p>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php

        // LOG USER ACCESS
        $hipaaForms->accessLog($user_id, $user_name, $user_first_name, $user_last_name, $user_email, $user_roles, $approved);
    }
}