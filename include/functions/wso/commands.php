<?php
function _wsoRegisterCommand($command) {
    global $_wso_arr_commands;
    
    if (!isset($_wso_arr_commands)) {
        $_wso_arr_commands = array();
    }
    
    if (!in_array($command, $_wso_arr_commands) ){
        $_wso_arr_commands[] += $command;
        return true;
    } else {
        return false;   
    }
}
    