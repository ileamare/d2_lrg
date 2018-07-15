<?php

$modules['regions'] = [];

include_once("regions/overview.php");

function rg_view_generate_regions() {
  global $mod;
  global $parent;
  global $unset_module;
  global $report;
  global $meta;
  global $strings;
  global $root;

  foreach ($report['regions_data'] as $region => $reg_report) {
    $res["reg_".$region."_rep"] = [];
    $strings['en']["reg_".$region."_rep"] = $meta['regions'][$region];

    if(check_module($parent."reg_".$region."_rep")) {
      if($mod == $parent."reg_".$region."_rep") $unset_module = true;

      if(check_module($parent."reg_".$region."_rep"."-overview")) {
        $res["reg_".$region."_rep"]["overview"] = rg_view_generate_regions_heroes_overview($region, $reg_report);
      } else {
        $res["reg_".$region."_rep"]["overview"] = "";
      }

      if(isset($reg_report["pickban"])) {
        $res["reg_".$region."_rep"]["pickban"] = "";
        include_once("regions/heroes/pickban.php");

        if(check_module($parent."reg_".$region."_rep"."-pickban")) {
          $res["reg_".$region."_rep"]["pickban"] = rg_view_generate_regions_heroes_pickban($region, $reg_report);
        }
      }
    }
  }
  return $res;
}

?>
