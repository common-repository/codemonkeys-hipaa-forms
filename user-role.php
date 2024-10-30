<?php
/**
 * Created by Code Monkeys LLC
 * http://www.codemonkeysllc.com
 * User: Spencer
 * Date: 12/7/2017
 * Time: 5:56 PM
 */

// Add a custom user role
function cm_hipaa_add_roles_on_init() {
    $hipaaRole = get_role('hipaa_forms');

    if(!$hipaaRole) {
        // SET CAPABILITIES ARRAY
        $caps = array(
            'activate_plugins',
            'create_roles',
            'create_users',
            'delete_documents',
            'delete_others_documents',
            'delete_others_pages',
            'delete_others_posts',
            'delete_pages',
            'delete_plugins',
            'delete_posts',
            'delete_private_documents',
            'delete_private_pages',
            'delete_private_posts',
            'delete_published_documents',
            'delete_published_pages',
            'delete_published_posts',
            'delete_roles',
            'delete_themes',
            'delete_users',
            'edit_dashboard',
            'edit_documents',
            'edit_files',
            'edit_others_documents',
            'edit_others_pages',
            'edit_others_posts',
            'edit_pages',
            'edit_plugins',
            'edit_posts',
            'edit_private_documents',
            'edit_private_pages',
            'edit_private_posts',
            'edit_published_documents',
            'edit_published_pages',
            'edit_published_posts',
            'edit_roles',
            'edit_themes',
            'edit_theme_options',
            'edit_users',
            'export',
            'import',
            'install_plugins',
            'install_themes',
            'list_roles',
            'list_users',
            'manage_categories',
            'manage_links',
            'manage_options',
            'moderate_comments',
            'override_document_lock',
            'promote_users',
            'publish_documents',
            'publish_pages',
            'publish_posts',
            'read_documents',
            'read_document_revisions',
            'read_private_documents',
            'read_private_pages',
            'read_private_posts',
            'remove_users',
            'restrict_content',
            'switch_themes',
            'unfiltered_html',
            'unfiltered_upload',
            'update_core',
            'update_plugins',
            'update_themes',
            'upload_files'
        );

        // SET CAPABILITY TRUE/FALSE
        $capabilities = array('read' => true);
        foreach($caps as $cap) {
            $capabilities[$cap] = false;
        }

        // CREATE USER ROLE AND SET CAPABILITIES
        add_role('hipaa_forms', __( 'HIPAA Forms' ), $capabilities);

        $hipaaRole = get_role('hipaa_forms');
        $hipaaRole->add_cap('read', true);
        $hipaaRole->add_cap('access_hipaa_forms', true);
    }
}
add_action('init', 'cm_hipaa_add_roles_on_init');

/*** ADD HIPAA ACCESS CAPABILITY TO ADMIN AND HIPAA FORMS ROLES ***/
function cm_hipaa_add_role_cap() {
    $adminRole = get_role('administrator');
    $adminRole->add_cap('access_hipaa_forms', true);

    $hipaaRole = get_role('hipaa_forms');
    $hipaaRole->add_cap('read', true);
    $hipaaRole->add_cap('access_hipaa_forms', true);
}
add_action('init', 'cm_hipaa_add_role_cap', 11);