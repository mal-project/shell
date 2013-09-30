<?php
_wsoRegisterCommand('download');
function commandDownload() {    
    $filename = @basename($_REQUEST['file']);
    $file = $_REQUEST['file'];
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: application/octet-stream");
    @readfile("$file");
    die;
}