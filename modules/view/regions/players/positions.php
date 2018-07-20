<?php

function rg_view_generate_regions_players_positions($region, $reg_report, $modstr) {
  global $root, $meta, $strings, $unset_module, $mod;
  if($mod == $modstr."positions") $unset_module = true;
  $parent_module = $modstr."positions-";

  $res = [];
  $res["overview"] = "";
  for ($i=1; $i>=0; $i--) {
    for ($j=1; $j<6 && $j>0; $j++) {
      if (!$i) { $j = 0; }
      if(!empty($reg_report['player_positions'][$i][$j]))
        $res["position_$i.$j"]  = "";

      if (!$i) { break; }
    }
  }

  if (check_module($parent_module."overview")) {
    include_once($root."/modules/view/generators/positions_overview.php");
    $res["overview"] = rg_generator_positions_overview("region$region--positions-overview", $reg_report['player_positions'], false);
    $res["overview"] .= "<div class=\"content-text\">".locale_string("desc_players_positions")."</div>";
  }
  {
    include_once($root."/modules/view/generators/summary.php");

    for ($i=1; $i>=0; $i--) {
      for ($j=1; $j<6 && $j>0; $j++) {
        if (!$i) { $j = 0; }

        if (!check_module($parent_module."position_$i.$j") || empty($reg_report['player_positions'][$i][$j])) {
          if (!$i) { break; }
          continue;
        }

        $res["position_$i.$j"] = rg_generator_summary("region$region-players-positions-$i-$j", $reg_report['player_positions'][$i][$j], false);

        $res["position_$i.$j"] .= "<div class=\"content-text\">".locale_string("desc_players_positions")."</div>";
        if (!$i) { break; }
      }
    }
  }

  return $res;
}

?>