<?php

$modules['players']['combos'] = [];

function rg_view_generate_players_combos() {
  global $report, $parent, $root, $unset_module, $mod;
  if($mod == $parent."combos") $unset_module = true;
  $parent_module = $parent."combos-";
  $res = [];

  if(isset($report['player_pairs'])) {
    $res['pairs'] = "";
    if (check_module($parent_module."pairs")) {
      include_once($root."/modules/view/generators/pairs.php");
      $res['pairs'] =  "<div class=\"content-text\">".locale_string("desc_players_pairs", [ "limh"=>$report['settings']['limiter']+1 ] )."</div>";
      $res['pairs'] .=  rg_generator_pairs("player-pairs",
                                         $report['player_pairs'],
                                         (isset($report['player_pairs_matches']) ? $report['player_pairs_matches'] : []),
                                         false
                                       );

    }
  }
  if(isset($report['player_triplets']) && !empty($report['player_triplets'])) {
    $res['trios'] = "";
    if (check_module($parent_module."trios")) {
      include_once($root."/modules/view/generators/trios.php");
      $res['trios'] =  "<div class=\"content-text\">".locale_string("desc_players_trios", [ "liml"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
      $res['trios'] .= rg_generator_trios("player-trios",
                                         $report['player_triplets'],
                                         (isset($report['player_triplets_matches']) ? $report['player_triplets_matches'] : []),
                                         false
                                       );
    }
  }
  if(isset($report['player_lane_combos'])) {
    $res['lane_combos'] = "";
    if (check_module($parent_module."lane_combos")) {
      include_once($root."/modules/view/generators/pairs.php");
      $res['lane_combos'] =  "<div class=\"content-text\">".locale_string("desc_players_lane_combos", [ "liml"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
      $res['lane_combos'] .=  rg_generator_pairs("player-lanecombos", $report['player_lane_combos'], [], false);
    }
  }

  return $res;
}

?>
