<?php

add_filter('movement_log_note', 'dt_movement_log_note', 10, 2 );
function dt_movement_log_note( $note, $data ) {
    $note = 'This is a note';
    return $note;
}
