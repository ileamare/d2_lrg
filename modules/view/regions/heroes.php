<?php

$res["region".$region]["heroes"] = [];

if($mod == $modstr."-heroes") $unset_module = true;
$parent_mod = $modstr."-heroes-";

if(isset($reg_report['haverages_heroes'])) {
  $res["region".$region]['heroes']["haverages"] = "";
  include_once("heroes/haverages.php");

  if(check_module($parent_mod."haverages")) {
    $res["region".$region]['heroes']['haverages'] = rg_view_generate_regions_heroes_haverages($region, $reg_report);
  }
}

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

if(isset($reg_report['heroes_meta_graph'])) {
  $res["region".$region]['heroes']["meta_graph"] = "";
  include_once("heroes/meta_graph.php");

  if(check_module($parent_mod."meta_graph")) {
    $res["region".$region]['heroes']['meta_graph'] = rg_view_generate_regions_heroes_meta_graph($region, $reg_report);
  }
}

?>
