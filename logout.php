<?php
    if($_SESSION['log_out_user']= true): 
        session_unset(); // unsets session_id
        session_destroy(); // destroy session data in storage
    endif;   
    header('Location: login.php'); // No active user. Re-direct to login page
?>