<?php
session_start();

$_SESSION['db_host'] = 'localhost';
$_SESSION['db_login'] = 'root';
$_SESSION['db_pw'] = '';
$_SESSION['db_table'] = 'chore_tracker';

function encryptPassword($pw) {
    $options = ['cost' => 10]; // Increase cost for higher security (requires greater processing time)
    $pw_hash = password_hash($pw, PASSWORD_DEFAULT, $options);
    // echo "<script>alert('Cipher PW: {$pw_hash}')</script>";
    return $pw_hash;
}

function formatDateForSQL($timestamp) {
    return date("Y-m-d", (int)($timestamp));
}

function formatDateTimeForSQL($timestamp) {
    return date("Y-m-d H:i:s", (int)($timestamp));
}

function  get_formdata($conn, $var) {
    return $conn->real_escape_string($_REQUEST[$var]); // escapes quotes to mitigate database hacking
}

function  sanitizeString($var) {
    if(get_magic_quotes_gpc())
        $var = stripslashes($var);
        $var = strip_tags($var);
        $var = htmlentities($var);
    return $var;
}

function sanitizeMySQL($conn, $var) {
    $conn->real_escape_string($var);
    $var = sanitizeString($var);
    return $var;
}
?>