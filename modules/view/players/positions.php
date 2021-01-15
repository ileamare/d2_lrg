<?php

$modules['players']['positions'] = [];

function rg_view_generate_players_positions() {
  global $report, $parent, $root, $meta, $strings, $unset_module, $mod;
  if($mod == $parent."positions") $unset_module = true;
  $parent_module = $parent."positions-";

  $res = [];
  $res["overview"] = "";
  for ($i=1; $i>=0; $i--) {
    for ($j=0; $j<6 && $j>=0; $j++) {
      // if (!$i) { $j = 0; }
      if(!empty($report['player_positions'][$i][$j]))
        $res["position_$i.$j"]  = "";

      // if (!$i) { break; }
    }
  }

  if (check_module($parent_module."overview")) {
    include_once($root."/modules/view/generators/positions_overview.php");
    $res["overview"] = rg_generator_positions_overview("players-positions-overview", $report['player_positions'], false);
    $res["overview"] .= "<div class=\"content-text\">".locale_string("desc_players_positions")."</div>";
  }
  {
    include_once($root."/modules/view/generators/summary.php");

    for ($i=1; $i>=0; $i--) {
      for ($j=0; $j<6 && $j>=0; $j++) {
        // if (!$i) { $j = 0; }

        if (!check_module($parent_module."position_$i.$j") || empty($report['player_positions'][$i][$j])) {
          // if (!$i) { break; }
          continue;
        }
        if(isset($report['player_positions_matches'])) {
          foreach($report['player_positions_matches'][$i][$j] as $id => $matches) {
            $report['player_positions'][$i][$j][$id]['matchlinks'] = "<a onclick=\"showModal('".
                htmlspecialchars(join_matches($matches)).
                "', '".locale_string("matches")." - ".player_name($id)." - ".locale_string("position_$i.$j")."');\">".
                locale_string("matches")."</a>";
          }
        }

        $res["position_$i.$j"] = rg_generator_summary("players-positions-$i-$j", $report['player_positions'][$i][$j], false);

        $res["position_$i.$j"] .= "<div class=\"content-text\">".locale_string("desc_players_positions")."</div>";
        // if (!$i) { break; }
      }
    }
  }

  return $res;
}

?>
