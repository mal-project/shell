<?php

function wsoWhich($p) {
	$path = wsoEx('which ' . $p);
	if(!empty($path))
		return $path;
	return false;
}
