<?php


# global settings
  $lrg_version = array(1, 0, 1, 0, getlastmod()); # 1.0.0-r0

# SQL Connection information

  $lrg_sql_host = "localhost";
  $lrg_sql_user = "root";
  $lrg_sql_pass = "";
  $steamapikey  = "766BB2E9B3343EF6D94851890EDADD1C";
  $lrg_db_prefix= "d2_league";

#TODO settings prefix
if(isset($argv)) {
    $options = getopt("l:m:d:f");

    if(isset($options['l'])) {
      $lrg_league_tag = $options['l'];
    }
  }
  if(!isset($lrg_league_tag))
  #$lrg_league_tag = "test";
  #$lrg_league_desc = "Test Test Test";
  #$lrg_league_tag = "sl_ileague_s3_minor_oct_2017";
  $lrg_league_tag = "workshop_bots_707";
  #$lrg_league_tag = "fpl_sept_2017";


  if(!isset($init)) {
    $lrg_sql_db   = $lrg_db_prefix."_".$lrg_league_tag;

    $lg_settings = file_get_contents("leagues/".$lrg_league_tag.".json");
    $lg_settings = json_decode($lg_settings, true);

    $lrg_use_cache = true;
  }

  # module-wide functions
  require_once("modules/mod_versions.php");
?>
