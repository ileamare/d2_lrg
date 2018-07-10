<?php

$modules['regions'] = array();

include_once("regions/overview.php");

function rg_view_generate_regions() {
  global $modules;
  global $mod;
  global $parent;
  global $unset_module;
  global $report;
  global $meta;
  global $strings;

  foreach ($report['regions_data'] as $region => $reg_report) {
    $modules['regions']["reg_".$region."_rep"] = [];
    $strings['en']["reg_".$region."_rep"] = $meta['regions'][$region];

    if(check_module($parent."reg_".$region."_rep")) {
      if($mod == $parent."reg_".$region."_rep") $unset_module = true;

      if(check_module($parent."reg_".$region."_rep"."-overview")) {
        $modules['regions']["reg_".$region."_rep"]["overview"] = rg_view_generate_regions_heroes_overview($region, $reg_report);
      } else {
        $modules['regions']["reg_".$region."_rep"]["overview"] = "";
      }

      if(isset($reg_report["pickban"])) {
        include_once("regions/heroes/pickban.php");

        if(check_module($parent."reg_".$region."_rep"."-pickban")) {
          $modules['regions']["reg_".$region."_rep"]["pickban"] = rg_view_generate_regions_heroes_pickban($region, $reg_report);
        } else {
          $modules['regions']["reg_".$region."_rep"]["pickban"] = "";
        }
      }
    }
  }
}

?>
