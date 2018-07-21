<?php

$modules['regions'] = [];

include_once("regions/overview.php");

function rg_view_generate_regions() {
  global $mod, $parent, $unset_module, $report, $meta, $strings, $root;

  if($mod == "regions") $unset_module = true;
  $parent = "regions-";

  foreach ($report['regions_data'] as $region => $reg_report) {
    $modstr = $parent."region".$region;
    $res["region".$region] = [];
    $strings['en']["region".$region] = $meta['regions'][$region];

    if(check_module($modstr)) {
      if($mod == $modstr) $unset_module = true;

      if(check_module($modstr."-overview")) {
        $res["region".$region]["overview"] = rg_view_generate_regions_overview($region, $reg_report);
      } else {
        $res["region".$region]["overview"] = "";
      }

      include_once("regions/heroes.php");

      if(isset($report['players']))
        include_once("regions/players.php");

      if(isset($report['players']))
        include_once("regions/participants.php");

      if(isset($report['matches']))
        include_once("regions/matches.php");
    }
  }
  return $res;
}

?>
