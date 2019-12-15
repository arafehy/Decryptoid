<?php
    // Function for sanitizing user input
    function mysql_entities_fix_string($conn, $string) {
        return htmlentities(mysql_fix_string($conn, $string));
    }

    // Function for sanitizing user input
    function mysql_fix_string($conn, $string) {
        if (get_magic_quotes_gpc()) $string = stripslashes($string);
        return $conn->real_escape_string($string);
    }

    function different_user() {
        destroy_session_and_data();
    }

    function destroy_session_and_data() {
        $_SESSION = array();
        setcookie(session_name(), '', time()-2592000, '/');
        session_destroy();
    }

    // Function for handling errors when connecting or obtaining data from table
    function mysql_fatal_error() {
	    echo <<< _END
            We are sorry, but it was not possible to complete
            the requested task.
_END;
    }
?>