<?php
//if(!function_exists("readline")) {
    function readline_rg($prompt = null){
        if($prompt){
            echo $prompt;
        }
        $fp = fopen("php://stdin","r");
        $line = rtrim(fgets($fp, 1024));
        return $line;
    }
//} else {
//  function readline_rg($prompt = null){
//      return readline($prompt);
//  }
//}
?>
