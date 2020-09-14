<?php
/**
 * Globally set the permissions to check
 */

/**
 * If a person has permissions, the it returns false
 * All other cases it returns a true
 * @param null $type
 *
 * @return bool
 */
function dt_network_dashboard_denied( $type = null ): bool {

    switch ( $type ) {
        // add other cases if necessary
        default:
            if ( current_user_can( 'view_any_contacts' ) || current_user_can( 'view_project_metrics' ) ) {
                return false;
            }
            break;
    }
    return true;

}