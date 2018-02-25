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

# global settings
  $lrg_version = array(1, 1, 1, -4, 0);

# SQL Connection information
  $lrg_sql_host  = "localhost";
  $lrg_sql_user  = "root";
  $lrg_sql_pass  = "";
  $lrg_db_prefix = "d2_league";

  if (file_exists(".steamapikey"))
    $steamapikey  = trim(file_get_contents(".steamapikey"));
  else {
    touch(".steamapikey");
    die("[F] Missing Steam API Key.\n".
        "    Place your Steam API Key to `.steamapikey` file in LRG's working directory.\n".
        "    If you don't have your Steam API Key, you can get one here:\n".
        "    https://steamcommunity.com/dev/apikey \n");
  }

if(isset($argv)) {
    $options = getopt("l:m:d:f");

    if(isset($options['l'])) {
      $lrg_league_tag = $options['l'];
    }
  }


  if(!isset($init)) {
    $lrg_sql_db   = $lrg_db_prefix."_".$lrg_league_tag;

    $lg_settings = file_get_contents("leagues/".$lrg_league_tag.".json");
    $lg_settings = json_decode($lg_settings, true);

    $lrg_use_cache = true;
  }

  # module-wide functions
  require_once("modules/mod.versions.php");
?>
