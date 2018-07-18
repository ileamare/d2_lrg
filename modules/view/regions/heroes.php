<?php

$res["region".$region]["heroes"] = [];

if($mod == $modstr."-heroes") $unset_module = true;
$parent_mod = $modstr."-heroes-";

$res["region".$region]['heroes']["pickban"] = "";
include_once("heroes/pickban.php");

if(check_module($parent_mod."pickban")) {
  $res["region".$region]['heroes']['pickban'] = rg_view_generate_regions_heroes_pickban($region, $reg_report);
}

if(isset($reg_report['draft'])) {
  $res["region".$region]['heroes']["draft"] = "";
  include_once("heroes/draft.php");

  if(check_module($parent_mod."draft")) {
    $res["region".$region]['heroes']['draft'] = rg_view_generate_regions_heroes_draft($region, $reg_report);
  }
}

?>
