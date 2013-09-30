<?php
function WSOCheckUA() {
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $userAgents = array("Google", "Slurp", "MSNBot", "ia_archiver", "Yandex", "Rambler");
        if(preg_match('/' . implode('|', $userAgents) . '/i', $_SERVER['HTTP_USER_AGENT'])) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
    }
}