<?php
// Activity Logger Function

// Prevent double inclusion
if (defined('LOG_ACTIVITY_LOADED')) {
    return;
}
define('LOG_ACTIVITY_LOADED', true);

function log_activity($action_type, $action_description, $target_type = null, $target_id = null) {
    global $conn;
    
    if (!isset($_SESSION['admin_user'])) {
        return false;
    }
    
    $admin_username = $_SESSION['admin_user'];
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $conn->prepare("
        INSERT INTO activity_logs 
        (admin_username, action_type, action_description, target_type, target_id, ip_address) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param('ssssis', 
        $admin_username, 
        $action_type, 
        $action_description, 
        $target_type, 
        $target_id, 
        $ip_address
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

// Action type constants
if (!defined('LOG_AUTH')) define('LOG_AUTH', 'authentication');
if (!defined('LOG_PROGRAMME')) define('LOG_PROGRAMME', 'programme');
if (!defined('LOG_APPLICATION')) define('LOG_APPLICATION', 'application');
if (!defined('LOG_QUERY')) define('LOG_QUERY', 'query');
if (!defined('LOG_ADMIN')) define('LOG_ADMIN', 'admin_management');
if (!defined('LOG_SETTINGS')) define('LOG_SETTINGS', 'settings');
if (!defined('LOG_CALCULATOR')) define('LOG_CALCULATOR', 'cost_calculator');

// ========== AUTO-LOGGING HELPERS ==========

// Auto-log login
function log_login($username, $success = true) {
    if ($success) {
        log_activity(LOG_AUTH, "Admin '$username' logged in successfully", 'admin', null);
    } else {
        log_activity(LOG_AUTH, "Failed login attempt for '$username'", 'admin', null);
    }
}

// Auto-log logout
function log_logout() {
    $username = $_SESSION['admin_user'] ?? 'Unknown';
    log_activity(LOG_AUTH, "Admin '$username' logged out", 'admin', null);
}

// Auto-log programme actions
function log_programme_created($programme_id, $title) {
    log_activity(LOG_PROGRAMME, "Created new programme: '$title'", 'programme', $programme_id);
}

function log_programme_updated($programme_id, $title) {
    log_activity(LOG_PROGRAMME, "Updated programme: '$title'", 'programme', $programme_id);
}

function log_programme_deleted($programme_id, $title) {
    log_activity(LOG_PROGRAMME, "Deleted programme: '$title'", 'programme', $programme_id);
}

// Auto-log application actions
function log_application_status_changed($app_id, $applicant_name, $old_status, $new_status) {
    log_activity(LOG_APPLICATION, "Changed application status for '$applicant_name' from '$old_status' to '$new_status'", 'application', $app_id);
}

function log_application_viewed($app_id, $applicant_name) {
    log_activity(LOG_APPLICATION, "Viewed application details for '$applicant_name'", 'application', $app_id);
}

function log_application_deleted($app_id, $applicant_name) {
    log_activity(LOG_APPLICATION, "Deleted application for '$applicant_name'", 'application', $app_id);
}

// Auto-log query actions
function log_query_status_changed($query_id, $user_name, $old_status, $new_status) {
    log_activity(LOG_QUERY, "Changed query status from '$old_status' to '$new_status' for user '$user_name'", 'query', $query_id);
}

function log_query_assigned($query_id, $user_name, $assigned_to) {
    log_activity(LOG_QUERY, "Assigned query from '$user_name' to admin '$assigned_to'", 'query', $query_id);
}

function log_query_resolved($query_id, $user_name) {
    log_activity(LOG_QUERY, "Resolved query from '$user_name'", 'query', $query_id);
}

function log_query_deleted($query_id, $user_name) {
    log_activity(LOG_QUERY, "Deleted query from '$user_name'", 'query', $query_id);
}

// Auto-log admin management (super admin only)
function log_admin_created($new_admin_username, $is_super) {
    $type = $is_super ? 'Super Admin' : 'Regular Admin';
    log_activity(LOG_ADMIN, "Created new $type account: '$new_admin_username'", 'admin', null);
}

function log_admin_deleted($deleted_admin_username) {
    log_activity(LOG_ADMIN, "Deleted admin account: '$deleted_admin_username'", 'admin', null);
}

function log_admin_password_changed($target_username) {
    log_activity(LOG_ADMIN, "Changed password for admin: '$target_username'", 'admin', null);
}

function log_admin_promoted($target_username) {
    log_activity(LOG_ADMIN, "Promoted '$target_username' to Super Admin", 'admin', null);
}

function log_admin_demoted($target_username) {
    log_activity(LOG_ADMIN, "Demoted '$target_username' to Regular Admin", 'admin', null);
}

// Auto-log newsletter actions
function log_newsletter_created($newsletter_id, $title) {
    log_activity(LOG_SETTINGS, "Created new newsletter: '$title'", 'newsletter', $newsletter_id);
}

function log_newsletter_updated($newsletter_id, $title) {
    log_activity(LOG_SETTINGS, "Updated newsletter: '$title'", 'newsletter', $newsletter_id);
}

function log_newsletter_deleted($newsletter_id, $title) {
    log_activity(LOG_SETTINGS, "Deleted newsletter: '$title'", 'newsletter', $newsletter_id);
}

function log_cost_calculator_saved($record_id, $calc_name) {
    log_activity(LOG_CALCULATOR, "Saved cost calculator record: '$calc_name'", 'cost_calculator', $record_id);
}

function log_cost_calculator_updated($record_id, $calc_name) {
    log_activity(LOG_CALCULATOR, "Updated cost calculator record: '$calc_name'", 'cost_calculator', $record_id);
}

function log_cost_calculator_deleted($record_id, $calc_name) {
    log_activity(LOG_CALCULATOR, "Deleted cost calculator record: '$calc_name'", 'cost_calculator', $record_id);
}
?>