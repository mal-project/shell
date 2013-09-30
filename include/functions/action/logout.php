<?php
function actionLogout() {
    setcookie(md5($_SERVER['HTTP_HOST']), '', time() - 3600);
	die;
}