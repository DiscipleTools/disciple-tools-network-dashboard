<?php
/**
 * This function returns the registered actions for logging.
 *
 * A new action being recorded into the movement_log must registered the action. It not only provides the label for
 * dropdowns and other selectors, it also provides the message_pattern for building the log message on the output.
 *
 *
 * @return array
 */
function dt_network_dashboard_registered_actions() : array {
    return apply_filters( 'dt_network_dashboard_register_actions', [] );
}