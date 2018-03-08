<?php
if(!function_exists("readline")) {
    function readline($prompt = null){
        if($prompt){
            echo $prompt;
        }
        $fp = fopen("php://stdin","r");
        $line = rtrim(fgets($fp, 1024));
        return $line;
    }
}

if(isset($argv)) {
    $options = getopt("l:m:d:f");

    if(isset($options['l'])) {
      $lrg_league_tag = $options['l'];
    }
  }

# global settings

  $lrg_version = array(1, 1, 1, -4, 1);

  $settings = json_decode(file_get_contents("rg_settings.json"), true);
  
  $lrg_sql_host  = $settings['mysql_host'];
  $lrg_sql_user  = $settings['mysql_user'];
  $lrg_sql_pass  = $settings['mysql_pass'];
  $lrg_db_prefix = $settings['mysql_prefix'];
  $stemapikey   = $settings['steamapikey'];
  
  unset($settings);

  if(isset($lrg_league_tag)) {
    $lrg_sql_db   = $lrg_db_prefix."_".$lrg_league_tag;

    $lg_settings = file_get_contents("leagues/".$lrg_league_tag.".json");
    $lg_settings = json_decode($lg_settings, true);
  }
  $lrg_use_cache = true;

  # module-wide functions
  require_once("modules/mod.versions.php");
?>
