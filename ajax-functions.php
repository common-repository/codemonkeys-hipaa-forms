<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 11/27/2017
 * Time: 4:33 PM
 * Updated: 07/14/2023 by Dan
 *
 * LEGACY PHP 5 SUPPORT WILL BE DEPRECATED SOON AND WILL REQUIRE PHP 7+
 *
 * THIS PLUGIN IS SIMPLY AN INTERFACE TO THE HIPAA FORMS SERVICE.  THE ONLY THINGS FROM THIS PLUGIN THAT GETS SAVED TO THE USER'S DATABASE ARE THE TOP 4 FIELDS IN THE SETTINGS TAB (FROM INCLUDES/OPTIONS.PHP - LICENCE KEY, NOTIFICATION EMAIL, SELECTED FORM BUILDER AND TIMEZONE.  EVERYTHING ELSE IS SIMPLY PUSHED OR PULLED FROM THE HIPAA FORMS SERVICE API.
 */


/** SITE WIDE AJAX FUNCTIONS */
/* VALIDATE HIPAA FORMS SERVICE ACCOUNT */
function cm_hipaa_validate_account() {
    if (isset($_REQUEST)) {
        $nononce = '';
        $nonce = '';
        if(isset($_REQUEST['nononce'])) {
            $nononce = sanitize_text_field($_REQUEST['nononce']);
        }
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }
        if($nononce !== '1' && !wp_verify_nonce($nonce, 'cm-hipaa-forms-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->validateAccount();
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_validate_account', 'cm_hipaa_validate_account' );
add_action( 'wp_ajax_nopriv_cm_hipaa_validate_account', 'cm_hipaa_validate_account' );

/*** ADMIN AJAX FUNCTION ***/
/* GET SUBMITTED FORMS LIST */
function cm_hipaa_get_submitted_forms_list() {
    if (isset($_REQUEST)) {
        $location = '';
        if(isset($_REQUEST['location'])){
            $location = ($_REQUEST['location']);
        }
        $formName = '';
        if(isset($_REQUEST['form_name'])) {
            $formName = sanitize_text_field($_REQUEST['form_name']);
        }
        $customStatus = '';
        if(isset($_REQUEST['custom_status'])) {
            $customStatus = sanitize_text_field($_REQUEST['custom_status']);
        }
        $firstName = '';
        if(isset($_REQUEST['first_name'])) {
            $firstName = sanitize_text_field($_REQUEST['first_name']);
        }
        $lastName = '';
        if(isset($_REQUEST['last_name'])) {
            $lastName = sanitize_text_field($_REQUEST['last_name']);
        }
        $phone = '';
        if(isset($_REQUEST['phone'])) {
            $phone = sanitize_text_field($_REQUEST['phone']);
        }
        $email = '';
        if(isset($_REQUEST['email'])) {
            $email = sanitize_text_field($_REQUEST['email']);
        }
        $status = '';
        if(isset($_REQUEST['status'])) {
            $status = sanitize_text_field($_REQUEST['status']);
        }
        $assignedTo = '';
        if(isset($_REQUEST['assigned_to'])) {
            $assignedTo = intval($_REQUEST['assigned_to']);
        }
        $assignedToMe = '';
        if(isset($_REQUEST['assigned_to_me'])) {
            $assignedToMe = intval($_REQUEST['assigned_to_me']);
        }
        $dateFrom = '';
        if(isset($_REQUEST['date_from'])) {
            $dateFrom = sanitize_text_field($_REQUEST['date_from']);
        }
        $dateTo = '';
        if(isset($_REQUEST['date_to'])) {
            $dateTo = sanitize_text_field($_REQUEST['date_to']);
        }
        $limit = '';
        if(isset($_REQUEST['limit'])) {
            $limit = intval($_REQUEST['limit']);
        }
        $page = '';
        if(isset($_REQUEST['page'])) {
            $page = intval($_REQUEST['page']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORMS FROM CODEMONKEYS HIPAA API
            $security = wp_create_nonce('submittedFormList');
            $cmSubmittedForms = new cmHipaaForms;
            echo $cmSubmittedForms->getSubmittedFormsList($location, $formName, $customStatus, $firstName, $lastName, $phone, $email, $status, $assignedTo, $assignedToMe, $dateFrom, $dateTo, $limit, $page, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_submitted_forms_list', 'cm_hipaa_get_submitted_forms_list' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_submitted_forms_list', 'cm_hipaa_get_submitted_forms_list' );

/* GET SUBMITTED FORM */
function cm_hipaa_get_submitted_form() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORM FROM API
            $security = wp_create_nonce('submittedForm');
            $cmSubmittedForms = new cmHipaaForms;
            echo $cmSubmittedForms->getSubmittedForm($formId, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_submitted_form', 'cm_hipaa_get_submitted_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_submitted_form', 'cm_hipaa_get_submitted_form' );

/* SUBMIT FORM NOTE */
function cm_hipaa_submit_note() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        $userId = '';
        if(isset($_REQUEST['user_id'])) {
            $userId = intval($_REQUEST['user_id']);
        }
        $name = '';
        if(isset($_REQUEST['name'])) {
            $name = sanitize_text_field($_REQUEST['name']);
        }
        $email = '';
        if(isset($_REQUEST['email'])) {
            $email = sanitize_email($_REQUEST['email']);
        }
        $note = '';
        if(isset($_REQUEST['note'])) {
            $note = sanitize_text_field($_REQUEST['note']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORMS FROM CODEMONKEYS HIPAA API
            $security = wp_create_nonce('submitNote');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->submitNote($formId, $userId, $name, $email, $note, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_submit_note', 'cm_hipaa_submit_note' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_submit_note', 'cm_hipaa_submit_note' );

/* SUBMIT FORM NOTE */
function cm_hipaa_update_custom_status_select() {
    if (isset($_REQUEST)) {
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORMS FROM CODEMONKEYS HIPAA API
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->updateCustomStatusOptions();
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_update_custom_status_select', 'cm_hipaa_update_custom_status_select' );

/* GET FORM NOTES */
function cm_hipaa_get_notes() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORMS FROM CODEMONKEYS HIPAA API
            $security = wp_create_nonce('formNotes');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->getFormNotes($formId, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_notes', 'cm_hipaa_get_notes' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_notes', 'cm_hipaa_get_notes' );

/* GET FORM HISTORY */
function cm_hipaa_get_form_history() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        if(isset($_REQUEST['page'])) {
            $page = intval($_REQUEST['page']);
        }
        if(isset($_REQUEST['limit'])) {
            $limit = intval($_REQUEST['limit']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET FORM HISTORY FROM API
            $security = wp_create_nonce('formHistory');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->getFormHistory($formId, $page, $limit, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_form_history', 'cm_hipaa_get_form_history' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_form_history', 'cm_hipaa_get_form_history' );

/* CREATE SELECTED USER MODAL */
function cm_hipaa_selected_users_modal() {
    $formId = '';
    if(isset($_REQUEST['form_id'])) {
        $formId = sanitize_text_field($_REQUEST['form_id']);
    }
    $selectedUser = '';
    if(isset($_REQUEST['selected_user'])) {
        $selectedUser = sanitize_text_field($_REQUEST['selected_user']);
    }
    $nonce = '';
    if(isset($_REQUEST['nonce'])) {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
    }

    if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
        echo 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator';
    } else {
        // GET SUBMITTED FORMS FROM CODEMONKEYS HIPAA API
        $security = wp_create_nonce('selectedUsersModal');
        $cmHipaaForms = new cmHipaaForms;
        echo $cmHipaaForms->createSelectedUsersModal($selectedUser, $formId, $security);
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_selected_users_modal', 'cm_hipaa_selected_users_modal' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_selected_users_modal', 'cm_hipaa_selected_users_modal' );

/* REASSIGN SELECTED USER */
function cm_hipaa_reassign_selected_user() {
    $formId = '';
    if(isset($_REQUEST['form_id'])) {
        $formId = sanitize_text_field($_REQUEST['form_id']);
    }
    $selectedUser = '';
    if(isset($_REQUEST['selected_user'])) {
        $selectedUser = sanitize_text_field($_REQUEST['selected_user']);
    }
    $nonce = '';
    if(isset($_REQUEST['nonce'])) {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
    }

    if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
        echo 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator';
    } else {
        // GET SUBMITTED FORMS FROM CODEMONKEYS HIPAA API
        $security = wp_create_nonce('reassignSelectedUser');
        $cmHipaaForms = new cmHipaaForms;
        echo $cmHipaaForms->reassignSelectedUser($selectedUser, $formId, $security);
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_reassign_selected_user', 'cm_hipaa_reassign_selected_user' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_reassign_selected_user', 'cm_hipaa_reassign_selected_user' );

/* GENERATE PDF */
function cm_hipaa_generate_pdf() {
    if(isset($_REQUEST)) {
        $form_id = '';
        if(isset($_REQUEST['form_id'])) {
            $form_id = intval($_REQUEST['form_id']);
        }
        $pdf_password = '';
        if(isset($_REQUEST['pdf_password'])) {
            $pdf_password = sanitize_text_field($_REQUEST['pdf_password']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('generatePdf');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->generatePdf($form_id, $pdf_password, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_generate_pdf', 'cm_hipaa_generate_pdf' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_generate_pdf', 'cm_hipaa_generate_pdf' );

/* DELETE PDF */
function cm_hipaa_delete_pdf() {
    if(isset($_REQUEST)) {
        $url = '';
        if(isset($_REQUEST['url'])) {
            $url = esc_url($_REQUEST['url']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }
        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('deletePdf');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->deletePdf($url, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_delete_pdf', 'cm_hipaa_delete_pdf' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_delete_pdf', 'cm_hipaa_delete_pdf' );

/* ARCHIVE FORM */
function cm_hipaa_archive_form() {
    if(isset($_REQUEST)) {
        $licenseKey = esc_attr(get_option('license_key'));
        $form_id = '';
        if(isset($_REQUEST['form_id'])) {
            $form_id = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('archiveForm');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->archiveForm($licenseKey, $form_id, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_archive_form', 'cm_hipaa_archive_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_archive_form', 'cm_hipaa_archive_form' );

/* RESTORE ARCHIVED FORM */
function cm_hipaa_restore_form() {
    if(isset($_REQUEST)) {
        $licenseKey = esc_attr(get_option('license_key'));
        $form_id = '';
        if(isset($_REQUEST['form_id'])) {
            $form_id = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('restoreForm');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->restoreForm($licenseKey, $form_id, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_restore_form', 'cm_hipaa_restore_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_restore_form', 'cm_hipaa_restore_form' );

/* DESTROY FORM */
function cm_hipaa_destroy_form() {
    if(isset($_REQUEST)) {
        $licenseKey = esc_attr(get_option('license_key'));
        $form_id = '';
        if(isset($_REQUEST['form_id'])) {
            $form_id = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('destroyForm');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->destroyForm($licenseKey, $form_id, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_destroy_form', 'cm_hipaa_destroy_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_destroy_form', 'cm_hipaa_destroy_form' );

/* PRINT FORM */
function cm_hipaa_print_form() {
    if(isset($_REQUEST)) {
        $licenseKey = esc_attr(get_option('license_key'));
        $form_id = '';
        if(isset($_REQUEST['form_id'])) {
            $form_id = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('printForm');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->printForm($licenseKey, $form_id, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_print_form', 'cm_hipaa_print_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_print_form', 'cm_hipaa_print_form' );

/* GET ACCESS LOGS */
function cm_hipaa_get_logs() {
    if (isset($_REQUEST)) {
        $startDate = '';
        if(isset($_REQUEST['start_date'])) {
            $startDate = sanitize_text_field($_REQUEST['start_date']);
        }
        $endDate = '';
        if(isset($_REQUEST['end_date'])) {
            $endDate = sanitize_text_field($_REQUEST['end_date']);
        }
        $limit = '';
        if(isset($_REQUEST['limit'])) {
            $limit = intval($_REQUEST['limit']);
        }
        $page = '';
        if(isset($_REQUEST['page'])) {
            $page = intval($_REQUEST['page']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET LOGS FROM CODEMONKEYS HIPAA API
            $security = wp_create_nonce('accessLogs');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->getAccessLogs($startDate, $endDate, $limit, $page, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_logs', 'cm_hipaa_get_logs' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_logs', 'cm_hipaa_get_logs' );

/* GET BAA FORM */
function cm_hipaa_get_baa_form() {
    if (isset($_REQUEST)) {
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('getBaaForm');
            // GET BAA FROM CODEMONKEYS HIPAA API
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->getBaaForm($security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_baa_form', 'cm_hipaa_get_baa_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_baa_form', 'cm_hipaa_get_baa_form' );

/* SUBMIT BAA FORM */
function cm_hipaa_submit_baa_form() {
    if (isset($_REQUEST)) {
        $form = '';
        if(isset($_REQUEST['form'])) {
            $form = $_REQUEST['form'];
        }
        $signature = '';
        if(isset($_REQUEST['signature'])) {
            $signature = $_REQUEST['signature'];
        }
        $signersName = '';
        if(isset($_REQUEST['signers_name'])) {
            $signersName = sanitize_text_field($_REQUEST['signers_name']);
        }
        $companyName = '';
        if(isset($_REQUEST['company_name'])) {
            $companyName = sanitize_text_field($_REQUEST['company_name']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // SUBMIT BAA FORM TO CODEMONKEYS HIPAA API
            $security = wp_create_nonce('submitBaaForm');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->submitBaaForm($form, $signature, $signersName, $companyName, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_submit_baa_form', 'cm_hipaa_submit_baa_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_submit_baa_form', 'cm_hipaa_submit_baa_form' );

/* GET BAA PDF */
function cm_hipaa_get_baa_pdf() {
    if (isset($_REQUEST)) {
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET BAA PDF URL FROM CODEMONKEYS HIPAA API
            $security = wp_create_nonce('getBaaPdf');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->getBaaPdf($security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_baa_pdf', 'cm_hipaa_get_baa_pdf' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_baa_pdf', 'cm_hipaa_get_baa_pdf' );

/* GET SUPPORT TICKETS */
function cm_hipaa_get_support_tickets() {
    if (isset($_REQUEST)) {
        $status = '';
        if(isset($_REQUEST['status'])) {
            $status = sanitize_text_field($_REQUEST['status']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUPPORT TICKETS FROM CODEMONKEYS SUPPORT TICKETS API
            $security = wp_create_nonce('getSupportTickets');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->getSupportTickets($status, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_support_tickets', 'cm_hipaa_get_support_tickets' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_get_support_tickets', 'cm_hipaa_get_support_tickets' );

/* SUBMIT SUPPORT TICKETS */
function cm_hipaa_submit_support_ticket() {
    if (isset($_REQUEST)) {
        $priority = sanitize_text_field($_REQUEST['priority']);
        $channel = sanitize_text_field($_REQUEST['channel']);
        $subject = sanitize_text_field($_REQUEST['subject']);
        $message = sanitize_textarea_field($_REQUEST['message']);
        $parentId = intval($_REQUEST['parent_id']);
        $nonce = sanitize_text_field($_REQUEST['nonce']);

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // POST TICKET TO CODEMONKEYS SUPPORT TICKET API
            $security = wp_create_nonce('submitSupportTicket');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->submitSupportTicket($priority, $channel, $subject, $message, $parentId, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_submit_support_ticket', 'cm_hipaa_submit_support_ticket' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_submit_support_ticket', 'cm_hipaa_submit_support_ticket' );

/* CLOSE SUPPORT TICKETS */
function cm_hipaa_close_support_ticket() {
    if (isset($_REQUEST)) {
        $ticketId = intval($_REQUEST['ticket_id']);
        $nonce = sanitize_text_field($_REQUEST['nonce']);

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // CLOSE TICKET IN CODEMONKEYS SUPPORT TICKET API
            $security = wp_create_nonce('closeSupportTicket');
            $cmHipaaForms = new cmHipaaForms;
            echo $cmHipaaForms->closeSupportTicket($ticketId, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_close_support_ticket', 'cm_hipaa_close_support_ticket' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_close_support_ticket', 'cm_hipaa_close_support_ticket' );


/* UPDATE AVAILABLE FORMS */
function cm_hipaa_update_available_forms() {
    if (isset($_REQUEST)) {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        $formBuilder = sanitize_text_field($_REQUEST['form_builder']);

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator';
        } else {
            // GET LIST OF GRAVITY FORMS
            $hipaaForms = new cmHipaaForms;

            if($formBuilder == 'caldera') {
                $enabledForms = get_option('enabled_forms_settings'); // NEW JSON VERSION WITH FORM SETTINGS
                $formsList = $hipaaForms->getCalderaForms($enabledForms);
            } else if($formBuilder == 'gravity') {
                $enabledForms = get_option('enabled_forms_settings'); // NEW JSON VERSION WITH FORM SETTINGS
                $formsList = $hipaaForms->getGravityForms($enabledForms);
            } else {
                $formsList = 'The selected form not form';
            }

            $results = $formsList;
        }

        echo $results;
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_update_available_forms', 'cm_hipaa_update_available_forms' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_update_available_forms', 'cm_hipaa_update_available_forms' );

/* UPDATE HIPAA USER ROLE CAPABILITIES */
function cm_hipaa_update_user_role() {
    if (isset($_REQUEST)) {
        $nonce = sanitize_text_field($_REQUEST['nonce']);
        $capabilities = sanitize_text_field($_REQUEST['capabilities']);

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('updateHipaaCapabilities');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->updateHipaaCapabilities($capabilities, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_update_user_role', 'cm_hipaa_update_user_role' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_update_user_role', 'cm_hipaa_update_user_role' );

/* GET FILE UPLOAD URL */
function cm_hipaa_get_file_upload_url() {
    if (isset($_REQUEST)) {
        $nononce = '';
        $nonce = '';
        if(isset($_REQUEST['nononce'])) {
            $nononce = sanitize_text_field($_REQUEST['nononce']);
        }
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }
        $fileName = '';
        if(isset($_REQUEST['file_name'])) {
            $fileName = sanitize_text_field($_REQUEST['file_name']);
        }

        if($nononce !== '1' && !wp_verify_nonce($nonce, 'cm-hipaa-forms-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );
        } else {
            // GET SUPPORT TICKETS FROM CODEMONKEYS SUPPORT TICKETS API
            $security = wp_create_nonce('getFileUploadUrl');
            $cmHipaaForms = new cmHipaaForms;
            $results = $cmHipaaForms->getFileUploadUrl($fileName, $security);
        }

        echo json_encode($results);
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_get_file_upload_url', 'cm_hipaa_get_file_upload_url' );
add_action( 'wp_ajax_nopriv_cm_hipaa_get_file_upload_url', 'cm_hipaa_get_file_upload_url' );

/* REBUILD AND SAVE MISSING RAW FIELD ARRAY */
function cm_hipaa_rebuild_form_fields() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }

        $sanitizedFormFields = array();
        if (isset($_REQUEST['form_fields'])) {
            $formFields = $_REQUEST['form_fields']; // ARRAY OF ENTIRE FIELDS

            // SANITIZE ARRAY
            foreach($formFields as $formField) {
                $formFieldItem = array();
                foreach($formField as $key => $value) {
                    $formFieldKey = sanitize_text_field($key);
                    $formFieldValue = sanitize_text_field($value);
                    $formFieldItem[$formFieldKey] = $formFieldValue;
                }

                array_push($sanitizedFormFields, $formFieldItem);
            }
        }

        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            $security = wp_create_nonce('rebuildFormFields');
            $hipaaForms = new cmHipaaForms;
            echo $hipaaForms->rebuildFormFields($formId, $sanitizedFormFields, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_rebuild_form_fields', 'cm_hipaa_rebuild_form_fields' );
add_action( 'wp_ajax_nopriv_cm_hipaa_rebuild_form_fields', 'cm_hipaa_rebuild_form_fields' );

/* EXPORT FORM */
function cm_hipaa_export_form() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        $includeNotes = '';
        if(isset($_REQUEST['include_notes'])) {
            $includeNotes = sanitize_text_field($_REQUEST['include_notes']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORM FROM API
            $security = wp_create_nonce('exportForm');
            $cmSubmittedForms = new cmHipaaForms;
            echo $cmSubmittedForms->exportForm($formId, $includeNotes, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_export_form', 'cm_hipaa_export_form' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_export_form', 'cm_hipaa_export_form' );

/* BULK EXPORT FORMS */
function cm_hipaa_bulk_export_forms() {
    if (isset($_REQUEST)) {
        $formIds = '';
        if(isset($_REQUEST['form_ids'])) {
            $formIds = sanitize_text_field($_REQUEST['form_ids']);
        }
        $exportOptions = '';
        if(isset($_REQUEST['export_options'])) {
            $exportOptions = sanitize_text_field($_REQUEST['export_options']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORM FROM API
            $security = wp_create_nonce('bulkExportForm');
            $cmSubmittedForms = new cmHipaaForms;
            echo $cmSubmittedForms->bulkExportForm($formIds, $exportOptions, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_bulk_export_forms', 'cm_hipaa_bulk_export_forms' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_bulk_export_forms', 'cm_hipaa_bulk_export_forms' );

/* EXPORT FORM NOTES */
function cm_hipaa_export_form_notes() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORM FROM API
            $security = wp_create_nonce('exportFormNotes');
            $cmSubmittedForms = new cmHipaaForms;
            echo $cmSubmittedForms->exportFormNotes($formId, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_export_form_notes', 'cm_hipaa_export_form_notes' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_export_form_notes', 'cm_hipaa_export_form_notes' );

/* EXPORT FORM HISTORY */
function cm_hipaa_export_form_history() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORM FROM API
            $security = wp_create_nonce('exportFormHistory');
            $cmSubmittedForms = new cmHipaaForms;
            echo $cmSubmittedForms->exportFormHistory($formId, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_export_form_history', 'cm_hipaa_export_form_history' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_export_form_history', 'cm_hipaa_export_form_history' );

/* UPDATE CUSTOM STATUS */
function cm_hipaa_update_custom_status() {
    if (isset($_REQUEST)) {
        $formId = '';
        if(isset($_REQUEST['form_id'])) {
            $formId = intval($_REQUEST['form_id']);
        }
        $customStatus = '';
        if(isset($_REQUEST['custom_status'])) {
            $customStatus = sanitize_text_field($_REQUEST['custom_status']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }

        if(!wp_verify_nonce($nonce, 'cm-hipaa-admin-nonce')) {
            $results = array(
                'error' => 'Nonce expired, please refresh the page.  If the error persists please contact the site administrator'
            );

            echo json_encode($results);
        } else {
            // GET SUBMITTED FORM FROM API
            $security = wp_create_nonce('updateCustomStatus');
            $cmSubmittedForms = new cmHipaaForms;
            echo $cmSubmittedForms->updateCustomStatus($formId, $customStatus, $security);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_update_custom_status', 'cm_hipaa_update_custom_status' );
//add_action( 'wp_ajax_nopriv_cm_hipaa_update_custom_status', 'cm_hipaa_update_custom_status' );

/*** FRONT END AJAX FUNCTION ***/
/* SUBMIT CALDERA FORM */
function cm_hipaa_submit_caldera_form() {
    if (isset($_REQUEST)) {
        // GET SAVED PLUGIN OPTIONS
        $licenseKey = esc_attr(get_option('license_key'));
        $defaultNotificationEmailFromName = esc_attr(get_option('hipaa_notification_from_name'));
        $defaultNotificationEmailFromEmail = esc_attr(get_option('hipaa_notification_from_email'));
        $defaultNotificationEmailSubject = esc_attr(get_option('hipaa_notification_email_subject'));
        $defaultNotificationEmailMessage = get_option('hipaa_notification_email_message');
        $defaultNotificationEmail = esc_attr(get_option('notification_email'));
        $limitDefaultNotificationEmail = esc_attr(get_option('limit_notification_email'));
        $formBuilder = esc_attr(get_option('form_builder'));
        $enabledFormsSettings = get_option('enabled_forms_settings');
        $decodedSettings = json_decode($enabledFormsSettings);
        $fields = array();

        // GET WP VALUES
        $adminEmail = get_bloginfo('admin_email');
        $siteName = $notificationFromName = get_bloginfo('name');

        $formId = '';
        if(isset($_REQUEST['formId'])) {
            $formId = sanitize_text_field($_REQUEST['formId']);
        }

        // GET FORM OPTIONS FOR CURRENT FORM
        $formOptions = '';
        if(is_array($decodedSettings)) {
            foreach ($decodedSettings as $decodedSetting) {
                if($decodedSetting->form_builder == 'caldera' && $decodedSetting->enabled == 'yes' && $decodedSetting->id === $formId){
                    $formOptions = $decodedSetting;
                }
            }
        }
        //var_dump($formOptions);

        // GET AJAX VALUES
        $location = '';
        if(isset($_REQUEST['location'])) {
            $location = stripslashes(sanitize_text_field($_REQUEST['location']));
        }
        $locationEmail = '';
        if(isset($_REQUEST['locationEmail'])) {
            $locationEmail = sanitize_text_field($_REQUEST['locationEmail']);
        }
        $firstName = '';
        if(isset($_REQUEST['firstName'])) {
            $firstName = sanitize_text_field($_REQUEST['firstName']);
        }
        $lastName = '';
        if(isset($_REQUEST['lastName'])) {
            $lastName = sanitize_text_field($_REQUEST['lastName']);
        }
        $email = '';
        if(isset($_REQUEST['email'])) {
            $email = sanitize_text_field($_REQUEST['email']);
        }
        $phone = '';
        if(isset($_REQUEST['phone'])) {
            $phone = sanitize_text_field($_REQUEST['phone']);
        }
        $formFields = '';
        if(isset($_REQUEST['formFields'])) {
            $formFields = $_REQUEST['formFields']; // ARRAY OF ENTIRE FIELDS
        }
        $formHtml = '';
        if(isset($_REQUEST['formHtml'])) {
            $formHtml = $_REQUEST['formHtml'];
        }
        $signature = '';
        if(isset($_REQUEST['signature'])) {
            $signature = sanitize_text_field($_REQUEST['signature']);
        }
        $nononce = '';
        $nonce = '';
        if(isset($_REQUEST['nononce'])) {
            $nononce = sanitize_text_field($_REQUEST['nononce']);
        }
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }
        $selectedUserSlug = '';
        if(isset($_REQUEST['selectedUserSlug'])) {
            $selectedUserSlug = sanitize_text_field($_REQUEST['selectedUserSlug']);
        }
        $formNotificationOption = '';
        if(isset($_REQUEST['notification_option'])) {
            $formNotificationOption = sanitize_text_field($_REQUEST['notification_option']);
        }
        $files = '';
        if(isset($_REQUEST['files'])) {
            $files = $_REQUEST['files'];
        }

        // SET EMAIL VALUES, NO LONGER IN AJAX CALL
        $formNotificationFromName = '';
        if(isset($_REQUEST['notification_from_name'])) {
            $formNotificationFromName = sanitize_text_field($_REQUEST['notification_from_name']);
        }elseif(!empty($formOptions->notification_from_name)){
            $formNotificationFromName = $formOptions->notification_from_name;
        }

        $formNotificationFromEmail = '';
        if(isset($_REQUEST['notification_from_email'])) {
            $formNotificationFromEmail = sanitize_text_field($_REQUEST['notification_from_email']);
        }elseif(!empty($formOptions->notification_from_email)){
            $formNotificationFromEmail = $formOptions->notification_from_email;
        }

        $formNotificationSendTo = '';
        if(isset($_REQUEST['notification_sendto'])) {
            $formNotificationSendTo = sanitize_text_field($_REQUEST['notification_sendto']);
        }elseif(!empty($formOptions->notification_sendto)){
            $formNotificationSendTo = $formOptions->notification_sendto;
        }

        $formNotificationSubject = '';
        if(isset($_REQUEST['notification_subject'])) {
            $formNotificationSubject = sanitize_text_field($_REQUEST['notification_subject']);
        }elseif(!empty($formOptions->notification_subject)){
            $formNotificationSubject = $formOptions->notification_subject;
        }

        $formNotificationMessage = '';
        if(isset($_REQUEST['notification_message'])) {
            $formNotificationMessage = $_REQUEST['notification_message'];
        }elseif(!empty($formOptions->notification_message)){
            $formNotificationMessage = $formOptions->notification_message;
        }

        $formFound = false;
        if(isset($_REQUEST['formFound']) && ($_REQUEST['formFound'] === 'true')){
            $formFound = true;
        }

        if($formFound !== true){
            // Get current max input vars setting
            $max_input_vars = ini_get('max_input_vars');
            // Find the totals of max input vars being used
            $total_vars = count($_GET, COUNT_RECURSIVE) + count($_POST, COUNT_RECURSIVE) + count($_COOKIE, COUNT_RECURSIVE);

            if ($total_vars >= ini_get('max_input_vars')) {
                echo "<div class='cm-hipaa-notice-error'>
                          <p style='display: none'>The current maximum input variables is set at ". $max_input_vars  . ", it is advised to set it greater than " . $total_vars . "</p>
                          <p>The maximum input variables limit has been reached!</p>
                          <p>Please contact the site administrator.</p>
                      </div>";
            }else{
                echo "<div class='cm-hipaa-notice-error'><p>Ajax Error: Form not Found</p></div>";
            }
        }elseif($nononce !== '1' && !wp_verify_nonce($nonce, 'cm-hipaa-forms-nonce')) {
            echo "<div class='cm-hipaa-notice-error'><p>Nonce expired, please refresh the page.</p><p>If the error persists please contact the site administrator</p></div>";
        } else {
            // GET CALDERA FORM DATA
            $calderaFormData = Caldera_Forms_Forms::get_form($formId);

            $calderaFormName = '';
            if(isset($calderaFormData['name'])) {
                $calderaFormName = $calderaFormData['name'];
            }

            $calderaFormDataMailer = '';
            if(isset($calderaFormData['mailer'])) {
                $calderaFormDataMailer = $calderaFormData['mailer'];
            }

            $calderaFormSenderName = '';
            if(isset($calderaFormDataMailer['sender_name'])) {
                $calderaFormSenderName = $calderaFormDataMailer['sender_name'];
            }

            $calderaFormSenderEmail = '';
            if(isset($calderaFormDataMailer['sender_email'])) {
                $calderaFormSenderEmail = $calderaFormDataMailer['sender_email'];
            }

            $calderaFormRecipients = '';
            if(isset($calderaFormDataMailer['recipients'])) {
                $calderaFormRecipients = $calderaFormDataMailer['recipients'];
            }

            $calderaFormBccTo = '';
            if(isset($calderaFormDataMailer['bcc_to'])) {
                $calderaFormBccTo = $calderaFormDataMailer['bcc_to'];
            }

            $calderaFormSubject = '';
            if(isset($calderaFormDataMailer['email_subject'])) {
                $calderaFormSubject = $calderaFormDataMailer['email_subject'];
            }

            $fieldsData = '';
            if(isset($calderaFormData['fields'])) {
                $fieldsData = $calderaFormData['fields'];
            }

            // SET NOTIFICATION EMAIL FROM NAME
            $notificationFromName = '';
            if($formNotificationOption == 'custom' && $formNotificationFromName) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & FROM NAME SET IN FORM SHOW THAT VALUE
                $notificationFromName = $formNotificationFromName;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailFromName) || ($formNotificationOption == 'custom' && !$formNotificationFromName && $defaultNotificationEmailFromName)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO FROM NAME VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationFromName = $defaultNotificationEmailFromName;
            } else if($calderaFormSenderName) {
                // IF FORM NOTIFICATION OPTION NOT SET REVERT TO CALDERA FORM OPTIONS FOR BACKWARDS COMPATIBILITY
                $notificationFromName = $calderaFormSenderName;
            } else {
                // IF NO FROM NAME SET ANYWHERE USE WP SITE NAME AS FALLBACK
                $notificationFromName = $siteName;
            }

            // SET NOTIFICATION FROM EMAIL ADDRESS
            $notificationFromEmail = '';
            if($formNotificationOption == 'custom' && $formNotificationFromEmail) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & FROM EMAIL SET IN FORM SHOW THAT VALUE
                $notificationFromEmail = $formNotificationFromEmail;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailFromEmail) || ($formNotificationOption == 'custom' && !$formNotificationFromEmail && $defaultNotificationEmailFromEmail)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO FROM EMAIL VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationFromEmail = $defaultNotificationEmailFromEmail;
            } else if($calderaFormSenderEmail) {
                // IF FORM NOTIFICATION OPTION NOT SET REVERT TO CALDERA FORM OPTIONS FOR BACKWARDS COMPATIBILITY
                $notificationFromEmail = $calderaFormSenderEmail;
            } else {
                // IF NO FROM EMAIL SET ANYWHERE USE WP ADMIN EMAIL AS FALLBACK
                $notificationFromEmail = $adminEmail;
            }

            // SET NOTIFICATION EMAIL RECIPIENTS
            $formRecipients = '';
            if($formNotificationOption == 'custom' && $formNotificationSendTo && $limitDefaultNotificationEmail == 'on') {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & EMAIL SET IN FORM & DEFAULT EMAIL SET TO FALLBACK ONLY JUST USE EMAIL SET IN FORM
                $formRecipients = $formNotificationSendTo;
            } else if($formNotificationOption == 'custom' && $formNotificationSendTo && $limitDefaultNotificationEmail !== 'on') {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & EMAIL SET IN FORM & DEFAULT EMAIL NOT SET TO FALLBACK ONLY USE BOTH EMAILS
                $formRecipients = $formNotificationSendTo . ', ' . $defaultNotificationEmail;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmail) || ($formNotificationOption == 'custom' && !$formNotificationSendTo)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO CUSTOM EMAIL SET IN FORM USE DEFAULT EMAIL
                $formRecipients = $defaultNotificationEmail;
            } else if($calderaFormRecipients && $limitDefaultNotificationEmail == 'on') {
                // IF FORM NOTIFICATION OPTION VALUE DOESN'T EXIST REVERT TO CALDERA FORM OPTION FOR BACKWARDS COMPATIBILITY & DEFAULT EMAIL SET TO FALLBACK ONLY JUST USE EMAIL SET IN CALDERA FORM
                $formRecipients = $calderaFormRecipients;
            } else if($calderaFormRecipients && $limitDefaultNotificationEmail !== 'on') {
                // IF FORM NOTIFICATION OPTION VALUE DOESN'T EXIST REVERT TO CALDERA FORM OPTION FOR BACKWARDS COMPATIBILITY & DEFAULT EMAIL NOT SET TO FALLBACK ONLY USE BOTH CALDERA FORM OPTION & DEFAULT EMAIL
                $formRecipients = $calderaFormRecipients . ', ' . $defaultNotificationEmail;
            } else {
                // IF NO EMAIL FOUND ANYWHERE THEN SET WP ADMIN EMAIL AS A CATCH-ALL
                $formRecipients = $adminEmail;
            }

            // SET NOTIFICATION EMAIL SUBJECT
            $notificationEmailSubject = '';
            if($formNotificationOption == 'custom' && $formNotificationSubject) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & SUBJECT SET IN FORM SHOW THAT VALUE
                $notificationEmailSubject = $formNotificationSubject;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailSubject) || ($formNotificationOption == 'custom' && !$formNotificationSubject && $defaultNotificationEmailSubject)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO SUBJECT VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationEmailSubject = $defaultNotificationEmailSubject;
            } else if($calderaFormSubject) {
                // IF FORM NOTIFICATION OPTION NOT SET REVERT TO CALDERA FORM OPTIONS FOR BACKWARDS COMPATIBILITY
                $notificationEmailSubject = $calderaFormSubject;
            } else {
                // IF NO SUBJECT SET ANYWHERE CREATE ONE AS FALLBACK
                $notificationEmailSubject = 'HIPAA Form Submission {location}';
            }

            // SET NOTIFICATION EMAIL MESSAGE
            $notificationEmailMessage = '';
            if($formNotificationOption == 'custom' && $formNotificationMessage) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & SUBJECT SET IN FORM SHOW THAT VALUE
                $notificationEmailMessage = $formNotificationMessage;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailMessage) || ($formNotificationOption == 'custom' && !$formNotificationMessage && $defaultNotificationEmailMessage) || (!$formNotificationOption && $defaultNotificationEmailMessage)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO SUBJECT VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationEmailMessage = $defaultNotificationEmailMessage;
            } else {
                // IF NO SUBJECT SET ANYWHERE CREATE ONE AS FALLBACK
                $notificationEmailMessage = '<table width="600px" style="border-collapse:collapse;">
                        <tbody>
                            <tr>
                                <th><b>{formname} HIPAA Form Submission</b></th>
                            </tr>
                            <tr>
                                <td style="height:30px"></td>
                            </tr>
                            <tr>
                                <td>
                                    Location: {location}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    First Name: {firstname}
                                </td>
                            </tr>
                            <tr>
                                <td style="height:15px"></td>
                            </tr>
                            <tr>
                                <td>
                                    Last Name: {lastname}
                                </td>
                            </tr>
                            <tr>
                                <td style="height:15px"></td>
                            </tr>
                            <tr>
                                <td>
                                    Email: {email}
                                </td>
                            </tr>
                            <tr>
                                <td style="height:15px"></td>
                            </tr>
                            <tr>
                                <td>
                                    Phone: {phone}
                                </td>
                            </tr>
                            <tr>
                                <td style="height:30px"></td>
                            </tr>
                            <tr>
                                <td>
                                    Please log into the admin dashboard to view the form.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Please do not reply to this email
                                </td>
                            </tr>
                        </tbody>
                    </table>';
            }

            $location = '';
            $locationEmail = '';
            $firstName = '';
            $lastName = '';
            $email = '';
            $phone = '';
            $selectedUser = '';
            foreach ($formFields as $key => $value) {
                $fieldId = '';
                if(isset($value['field_id'])) {
                    $fieldId = $value['field_id'];
                }
                $fieldLabel = '';
                if(isset($value['label'])) {
                    $fieldLabel = strip_tags($value['label']);
                }
                $optionLabel = '';
                if(isset($value['option_label'])) {
                    $optionLabel = strip_tags($value['option_label']);
                }
                $optionValue = '';
                if(isset($value['option_value'])) {
                    $optionValue = $value['option_value'];
                }
                $dataField = '';
                if(isset($value['data_field'])) {
                    $dataField = $value['data_field'];
                }
                $fieldValue = '';
                if(isset($value['value'])) {
                    $fieldValue = $value['value'];
                }
                $optionText = '';
                if(isset($value['option_text'])) {
                    $optionText = trim($value['option_text']);
                }

                // SET THE FIELD ID TO THE DATA_FIELD VALUE, IF DOESN'T EXIST SET TO ACTUAL ID "id_1"
                if ($dataField) {
                    $fieldIdMain = $dataField;
                } else {
                    $fieldIdMain = $fieldId;
                }

                // GET CALDERA FIELD DATA FOR THIS FIELD
                $fieldData = '';
                if(isset($fieldsData[$fieldIdMain])) {
                    $fieldData = $fieldsData[$fieldIdMain];
                }

                // GET FIELD TYPE
                $fieldType = '';
                if(isset($fieldData['type'])) {
                    $fieldType = $fieldData['type'];
                }

                // GET CALDERA FIELD SLUG
                $fieldSlug = '';
                if(isset($fieldData['slug'])) {
                    $fieldSlug = $fieldData['slug'];
                }

                // SET PRIMARY FIELDS
                if($fieldSlug == 'office_location') {
                    $location = $optionText;
                    $locationEmail = $fieldValue;
                } else if($fieldSlug == 'first_name') {
                    $firstName = $fieldValue;
                } else if($fieldSlug == 'last_name') {
                    $lastName = $fieldValue;
                } else if($fieldSlug == 'email') {
                    $email = $fieldValue;
                } else if($fieldSlug == 'phone') {
                    $phone = $fieldValue;
                } else if($selectedUserSlug !== '' && $fieldSlug == $selectedUserSlug) {
                    $selectedUser = $fieldValue;
                }

                // PUSH FIELD TO ARRAY
                $fields[] = array(
                    'field_id' => $fieldId,
                    'label' => $fieldLabel,
                    'option_label' => $optionLabel,
                    'option_value' => $optionValue,
                    'option_text' => $optionText,
                    'value' => $fieldValue,
                    'type' => $fieldType
                );
            }

            // JSON ENCODE FIELDS OBJECT ARRAY
            $fields = json_encode($fields, JSON_UNESCAPED_SLASHES);

            // SUBMIT FORM TO CODEMONKEYS HIPAA API
            $cmSubmitForm = new cmHipaaForms;
            echo $cmSubmitForm->sendForm($licenseKey, $formNotificationOption, $notificationFromName, $notificationFromEmail, $formRecipients, '', $notificationEmailSubject, $notificationEmailMessage, $formId, $calderaFormName, $location, $locationEmail, $firstName, $lastName, $email, $phone, $fields, $formHtml, $signature, $selectedUser, $files, $formFound, $nonce);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_submit_caldera_form', 'cm_hipaa_submit_caldera_form' );
add_action( 'wp_ajax_nopriv_cm_hipaa_submit_caldera_form', 'cm_hipaa_submit_caldera_form' );

/* SUBMIT GRAVITY FORM */
function cm_hipaa_submit_gravity_form() {
    if (isset($_REQUEST)) {
        // GET SAVED PLUGIN OPTIONS
        $licenseKey = esc_attr(get_option('license_key'));
        $defaultNotificationEmailFromName = esc_attr(get_option('hipaa_notification_from_name'));
        $defaultNotificationEmailFromEmail = esc_attr(get_option('hipaa_notification_from_email'));
        $defaultNotificationEmailSubject = esc_attr(get_option('hipaa_notification_email_subject'));
        $defaultNotificationEmailMessage = get_option('hipaa_notification_email_message');
        $defaultNotificationEmail = esc_attr(get_option('notification_email'));
        $limitDefaultNotificationEmail = esc_attr(get_option('limit_notification_email'));
        $formBuilder = esc_attr(get_option('form_builder'));
        $enabledFormsSettings = get_option('enabled_forms_settings');
        $decodedSettings = json_decode($enabledFormsSettings);

        // GET WP VALUES
        $adminEmail = get_bloginfo('admin_email');
        $siteName = get_bloginfo('name');

        // GET FORM ID
        $formId = '';
        if(isset($_REQUEST['formId'])) {
            $formId = sanitize_text_field($_REQUEST['formId']);
        }
        $formIdStripped = str_replace('gform_', '', $formId);

        // GET FORM OPTIONS FOR CURRENT FORM
        $formOptions = '';
        if(is_array($decodedSettings)) {
            foreach ($decodedSettings as $decodedSetting) {
                if($decodedSetting->form_builder == 'gravity' && $decodedSetting->enabled == 'yes' && $decodedSetting->id === $formIdStripped){
                    $formOptions = $decodedSetting;
                }
            }
        }
        //var_dump($formOptions);

        // GET AJAX VALUES
        $gravityClass = '';
        if(isset($_REQUEST['gravityClass'])) {
            $gravityClass = sanitize_text_field($_REQUEST['gravityClass']);
        }
        $location = '';
        if(isset($_REQUEST['location'])) {
            $location = stripslashes(sanitize_text_field($_REQUEST['location']));
        }
        $locationEmail = '';
        if(isset($_REQUEST['locationEmail'])) {
            $locationEmail = sanitize_text_field($_REQUEST['locationEmail']);
        }
        $firstName = '';
        if(isset($_REQUEST['firstName'])) {
            $firstName = sanitize_text_field($_REQUEST['firstName']);
        }
        $lastName = '';
        if(isset($_REQUEST['lastName'])) {
            $lastName = sanitize_text_field($_REQUEST['lastName']);
        }
        $email = '';
        if(isset($_REQUEST['email'])) {
            $email = sanitize_text_field($_REQUEST['email']);
        }
        $phone = '';
        if(isset($_REQUEST['phone'])) {
            $phone = sanitize_text_field($_REQUEST['phone']);
        }

        $sanitizedFormFields = array();
        if (isset($_REQUEST['formFields'])) {
            $formFields = $_REQUEST['formFields']; // ARRAY OF ENTIRE FIELDS

            // SANITIZE ARRAY
            foreach($formFields as $formField) {
                $formFieldItem = array();
                foreach($formField as $key => $value) {
                    $formFieldKey = sanitize_text_field($key);
                    $formFieldValue = sanitize_text_field($value);
                    $formFieldItem[$formFieldKey] = $formFieldValue;
                }

                array_push($sanitizedFormFields, $formFieldItem);
            }
        }

        $formHtml = '';
        if(isset($_REQUEST['formHtml'])) {
            $formHtml = wp_kses_normalize_entities($_REQUEST['formHtml']);  // FULL HTML FORM
        }
        $signature = '';
        if(isset($_REQUEST['signature'])) {
            $signature = sanitize_text_field($_REQUEST['signature']);
        }
        $nononce = '';
        if(isset($_REQUEST['nononce'])) {
            $nononce = sanitize_text_field($_REQUEST['nononce']);
        }
        $nonce = '';
        if(isset($_REQUEST['nonce'])) {
            $nonce = sanitize_text_field($_REQUEST['nonce']);
        }
        $selectedUser = '';
        if(isset($_REQUEST['selectedUser'])) {
            $selectedUser = sanitize_text_field($_REQUEST['selectedUser']);
        }
        $formNotificationOption = '';
        if(isset($_REQUEST['notification_option'])) {
            $formNotificationOption = sanitize_text_field($_REQUEST['notification_option']);
        }
        $files = '';
        if(isset($_REQUEST['files'])) {
            $files = $_REQUEST['files'];
        }

        // SET EMAIL VALUES, NO LONGER IN AJAX CALL
        $formNotificationFromName = '';
        if(isset($_REQUEST['notification_from_name'])) {
            $formNotificationFromName = sanitize_text_field($_REQUEST['notification_from_name']);
        }elseif(!empty($formOptions->notification_from_name)){
            $formNotificationFromName = $formOptions->notification_from_name;
        }

        $formNotificationFromEmail = '';
        if(isset($_REQUEST['notification_from_email'])) {
            $formNotificationFromEmail = sanitize_text_field($_REQUEST['notification_from_email']);
        }elseif(!empty($formOptions->notification_from_email)){
            $formNotificationFromEmail = $formOptions->notification_from_email;
        }

        $formNotificationSendTo = '';
        if(isset($_REQUEST['notification_sendto'])) {
            $formNotificationSendTo = sanitize_text_field($_REQUEST['notification_sendto']);
        }elseif(!empty($formOptions->notification_sendto)){
            $formNotificationSendTo = $formOptions->notification_sendto;
        }

        $formNotificationSubject = '';
        if(isset($_REQUEST['notification_subject'])) {
            $formNotificationSubject = sanitize_text_field($_REQUEST['notification_subject']);
        }elseif(!empty($formOptions->notification_subject)){
            $formNotificationSubject = $formOptions->notification_subject;
        }

        $formNotificationMessage = '';
        if(isset($_REQUEST['notification_message'])) {
            $formNotificationMessage = wp_kses_normalize_entities($_REQUEST['notification_message']);
        }elseif(!empty($formOptions->notification_message)){
            $formNotificationMessage = $formOptions->notification_message;
        }

        $formFound = false;
        if(isset($_REQUEST['formFound']) && ($_REQUEST['formFound'] === 'true')){
            $formFound = true;
        }

        if($formFound !== true){
            // Get current max input vars setting
            $max_input_vars = ini_get('max_input_vars');
            // Find the totals of max input vars being used
            $total_vars = count($_GET, COUNT_RECURSIVE) + count($_POST, COUNT_RECURSIVE) + count($_COOKIE, COUNT_RECURSIVE);

            if ($total_vars >= ini_get('max_input_vars')) {
                echo "<div class='cm-hipaa-notice-error'>
                          <p style='display: none'>The current maximum input variables is set at ". $max_input_vars  . ", it is advised to set it greater than " . $total_vars . "</p>
                          <p>The maximum input variables limit has been reached!</p>
                          <p>Please contact the site administrator.</p>
                      </div>";
            }else{
                echo "<div class='cm-hipaa-notice-error'><p>Ajax Error: Form not Found</p></div>";
            }

        }elseif($nononce !== '1' && !wp_verify_nonce($nonce, 'cm-hipaa-forms-nonce')) {
            echo "<div class='cm-hipaa-notice-error'><p>Nonce expired, please refresh the page.</p><p>If the error persists please contact the site administrator</p></div>";
        } else {
            $gravityFormMeta = RGFormsModel::get_form_meta($formIdStripped);

            $gravityFormName = '';
            if(isset($gravityFormMeta['title'])) {
                $gravityFormName = $gravityFormMeta['title'];
            }

            $gravityFormNotifications = '';
            if(isset($gravityFormMeta['notifications'])) {
                $gravityFormNotifications = $gravityFormMeta['notifications'];
            }

            $gravityNotificationRecipients = '';
            $gravityNotificationSenderName = '';
            $gravityNotificationSenderEmail = '';
            $gravityNotificationBccTo = '';
            $gravityNotificationSubject = '';

            if(is_array($gravityFormNotifications)) {
                // GET GRAVITY NOTIFICATION VALUES
                foreach($gravityFormNotifications as $gravityFormNotification) {
                    if(isset($gravityFormNotification['to'])) {
                        $gravityNotificationRecipients = $gravityFormNotification['to'];
                    }
                    if(isset($gravityFormNotification['fromName'])) {
                        $gravityNotificationSenderName = $gravityFormNotification['fromName'];
                    }
                    if(isset($gravityFormNotification['from'])) {
                        $gravityNotificationSenderEmail = $gravityFormNotification['from'];
                    }
                    if(isset($gravityFormNotification['bcc'])) {
                        $gravityNotificationBccTo = $gravityFormNotification['bcc'];
                    }
                    if(isset($gravityFormNotification['subject'])) {
                        $gravityNotificationSubject = $gravityFormNotification['subject'];
                    }

                    // REPLACE MERGE TAGS IF USED
                    if($gravityNotificationRecipients == '{admin_email}') {
                        $gravityNotificationRecipients = $adminEmail;
                    }

                    if($gravityNotificationSenderEmail == '{admin_email}') {
                        $gravityNotificationSenderEmail = $adminEmail;
                    }

                    if($gravityNotificationBccTo == '{admin_email}') {
                        $gravityNotificationBccTo = $adminEmail;
                    }
                }
            }

            // SET NOTIFICATION EMAIL FROM NAME
            $notificationFromName = '';
            if($formNotificationOption == 'custom' && $formNotificationFromName) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & FROM NAME SET IN FORM SHOW THAT VALUE
                $notificationFromName = $formNotificationFromName;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailFromName) || ($formNotificationOption == 'custom' && !$formNotificationFromName && $defaultNotificationEmailFromName)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO FROM NAME VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationFromName = $defaultNotificationEmailFromName;
            } else if($gravityNotificationSenderName) {
                // IF FORM NOTIFICATION OPTION NOT SET REVERT TO CALDERA FORM OPTIONS FOR BACKWARDS COMPATIBILITY
                $notificationFromName = $gravityNotificationSenderName;
            } else {
                // IF NO FROM NAME SET ANYWHERE USE WP SITE NAME AS FALLBACK
                $notificationFromName = $siteName;
            }

            // SET NOTIFICATION FROM EMAIL ADDRESS
            $notificationFromEmail = '';
            if($formNotificationOption == 'custom' && $formNotificationFromEmail) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & FROM EMAIL SET IN FORM SHOW THAT VALUE
                $notificationFromEmail = $formNotificationFromEmail;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailFromEmail) || ($formNotificationOption == 'custom' && !$formNotificationFromEmail && $defaultNotificationEmailFromEmail)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO FROM EMAIL VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationFromEmail = $defaultNotificationEmailFromEmail;
            } else if($gravityNotificationSenderEmail) {
                // IF FORM NOTIFICATION OPTION NOT SET REVERT TO CALDERA FORM OPTIONS FOR BACKWARDS COMPATIBILITY
                $notificationFromEmail = $gravityNotificationSenderEmail;
            } else {
                // IF NO FROM EMAIL SET ANYWHERE USE WP ADMIN EMAIL AS FALLBACK
                $notificationFromEmail = $adminEmail;
            }

            // SET NOTIFICATION EMAIL RECIPIENTS
            $notificationEmailRecipients = '';
            if($formNotificationOption == 'custom' && $formNotificationSendTo && $limitDefaultNotificationEmail == 'on') {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & EMAIL SET IN FORM & DEFAULT EMAIL SET TO FALLBACK ONLY JUST USE EMAIL SET IN FORM
                $notificationEmailRecipients = $formNotificationSendTo;
            } else if($formNotificationOption == 'custom' && $formNotificationSendTo && $limitDefaultNotificationEmail !== 'on') {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & EMAIL SET IN FORM & DEFAULT EMAIL NOT SET TO FALLBACK ONLY USE BOTH EMAILS
                $notificationEmailRecipients = $formNotificationSendTo . ', ' . $defaultNotificationEmail;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmail) || ($formNotificationOption == 'custom' && !$formNotificationSendTo)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO CUSTOM EMAIL SET IN FORM USE DEFAULT EMAIL
                $notificationEmailRecipients = $defaultNotificationEmail;
            } else if($gravityNotificationRecipients && $limitDefaultNotificationEmail == 'on') {
                // IF FORM NOTIFICATION OPTION VALUE DOESN'T EXIST REVERT TO CALDERA FORM OPTION FOR BACKWARDS COMPATIBILITY & DEFAULT EMAIL SET TO FALLBACK ONLY JUST USE EMAIL SET IN CALDERA FORM
                $notificationEmailRecipients = $gravityNotificationRecipients;
            } else if($gravityNotificationRecipients && $limitDefaultNotificationEmail !== 'on') {
                // IF FORM NOTIFICATION OPTION VALUE DOESN'T EXIST REVERT TO CALDERA FORM OPTION FOR BACKWARDS COMPATIBILITY & DEFAULT EMAIL NOT SET TO FALLBACK ONLY USE BOTH CALDERA FORM OPTION & DEFAULT EMAIL
                $notificationEmailRecipients = $gravityNotificationRecipients . ', ' . $defaultNotificationEmail;
            } else {
                // IF NO EMAIL FOUND ANYWHERE THEN SET WP ADMIN EMAIL AS A CATCH-ALL
                $notificationEmailRecipients = $adminEmail;
            }

            // SET NOTIFICATION EMAIL SUBJECT
            $notificationEmailSubject = '';
            if($formNotificationOption == 'custom' && $formNotificationSubject) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & SUBJECT SET IN FORM SHOW THAT VALUE
                $notificationEmailSubject = $formNotificationSubject;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailSubject) || ($formNotificationOption == 'custom' && !$formNotificationSubject && $defaultNotificationEmailSubject)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO SUBJECT VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationEmailSubject = $defaultNotificationEmailSubject;
            } else if($gravityNotificationSubject) {
                // IF FORM NOTIFICATION OPTION NOT SET REVERT TO CALDERA FORM OPTIONS FOR BACKWARDS COMPATIBILITY
                $notificationEmailSubject = $gravityNotificationSubject;
            } else {
                // IF NO SUBJECT SET ANYWHERE CREATE ONE AS FALLBACK
                $notificationEmailSubject = 'HIPAA Form Submission {location}';
            }

            // SET NOTIFICATION EMAIL MESSAGE
            $notificationEmailMessage = '';
            if($formNotificationOption == 'custom' && $formNotificationMessage) {
                // IF FORM NOTIFICATION OPTION SET TO CUSTOM & SUBJECT SET IN FORM SHOW THAT VALUE
                $notificationEmailMessage = $formNotificationMessage;
            } else if(($formNotificationOption == 'default' && $defaultNotificationEmailMessage) || ($formNotificationOption == 'custom' && !$formNotificationMessage && $defaultNotificationEmailMessage) || (!$formNotificationOption && $defaultNotificationEmailMessage)) {
                // IF FORM NOTIFICATION OPTION SET TO DEFAULT OR IF SET TO CUSTOM BUT NO SUBJECT VALUE EXISTS IN FORM SHOW DEFAULT IF EXISTS
                $notificationEmailMessage = $defaultNotificationEmailMessage;
            } else {
                // IF NO SUBJECT SET ANYWHERE CREATE ONE AS FALLBACK
                $notificationEmailMessage = '<table width="600px" style="border-collapse:collapse;">
                    <tbody>
                        <tr>
                            <th><b>{formname} HIPAA Form Submission</b></th>
                        </tr>
                        <tr>
                            <td style="height:30px"></td>
                        </tr>
                        <tr>
                            <td>
                                Location: {location}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                First Name: {firstname}
                            </td>
                        </tr>
                        <tr>
                            <td style="height:15px"></td>
                        </tr>
                        <tr>
                            <td>
                                Last Name: {lastname}
                            </td>
                        </tr>
                        <tr>
                            <td style="height:15px"></td>
                        </tr>
                        <tr>
                            <td>
                                Email: {email}
                            </td>
                        </tr>
                        <tr>
                            <td style="height:15px"></td>
                        </tr>
                        <tr>
                            <td>
                                Phone: {phone}
                            </td>
                        </tr>
                        <tr>
                            <td style="height:30px"></td>
                        </tr>
                        <tr>
                            <td>
                                Please log into the admin dashboard to view the form.
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Please do not reply to this email
                            </td>
                        </tr>
                    </tbody>
                </table>';
            }

            $fields = array();
            foreach ($sanitizedFormFields as $key => $value) {
                $fieldId = '';
                if(isset($value['field_id'])) {
                    $fieldId = $value['field_id'];
                }

                $fieldLabel = '';
                if(isset($value['label'])) {
                    $fieldLabel = strip_tags($value['label']);
                }

                $optionLabel = '';
                if(isset($value['option_label'])) {
                    $optionLabel = strip_tags($value['option_label']);
                }

                $optionValue = '';
                if(isset($value['option_value'])) {
                    $optionValue = $value['option_value'];
                }

                $fieldValue = '';
                if(isset($value['value'])) {
                    $fieldValue = $value['value'];
                }

                $optionText = '';
                if(isset($value['option_text'])) {
                    $optionText = trim($value['option_text']);
                }

                $fieldType = '';
                if(isset($value['field_type'])) {
                    $fieldType = $value['field_type'];
                }

                // PUSH FIELD TO ARRAY
                $fields[] = array(
                    'field_id' => $fieldId,
                    'label' => $fieldLabel,
                    'option_label' => $optionLabel,
                    'option_value' => $optionValue,
                    'option_text' => $optionText,
                    'value' => $fieldValue,
                    'type' => $fieldType
                );
            }


            $fields = json_encode($fields, JSON_UNESCAPED_SLASHES);

            // SUBMIT FORM TO CODE MONKEYS HIPAA API
            $cmSubmitForm = new cmHipaaForms;
            echo $cmSubmitForm->sendForm($licenseKey, $formNotificationOption, $notificationFromName, $notificationFromEmail, $notificationEmailRecipients, '', $notificationEmailSubject, $notificationEmailMessage, $formId, $gravityFormName, $location, $locationEmail, $firstName, $lastName, $email, $phone, $fields, $formHtml, $signature, $selectedUser, $files, $formFound, $nonce);
        }
    }

    die();
}
add_action( 'wp_ajax_cm_hipaa_submit_gravity_form', 'cm_hipaa_submit_gravity_form' );
add_action( 'wp_ajax_nopriv_cm_hipaa_submit_gravity_form', 'cm_hipaa_submit_gravity_form' );