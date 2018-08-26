<?php

$modules['heroes']['combos'] = [];

function rg_view_generate_regions_players_combos($region, $reg_report, $modstr) {
  global $root, $unset_module, $mod;
  if($mod == $modstr."combos") $unset_module = true;
  $parent_module = $modstr."combos-";
  $res = [];
  include_once($root."/modules/view/generators/combos.php");

  if(isset($reg_report['player_pairs'])) {
    $res['pairs'] = "";
    if (check_module($parent_module."pairs")) {
      $res['pairs'] =  "<div class=\"content-text\">".
                        locale_string("desc_players_pairs", [ "limh"=>$reg_report['settings']['limiter_higher']+1 ] )."</div>";
      $res['pairs'] .=  rg_generator_combos("region$region-player-pairs",
                                         $reg_report['player_pairs'],
                                         [],
                                         false
                                       );
    }
  }
  if(isset($reg_report['player_trios']) && !empty($reg_report['player_trios'])) {
    $res['trios'] = "";
    if (check_module($parent_module."trios")) {
      include_once($root."/modules/view/generators/trios.php");
      $res['trios'] =  "<div class=\"content-text\">".
                        locale_string("desc_players_trios", [ "liml"=>$reg_report['settings']['limiter_lower']+1 ] )."</div>";
      $res['trios'] .= rg_generator_combos("region$region-player-trios",
                                         $reg_report['player_trios'],
                                         [],
                                         false
                                       );
    }
  }
  if(isset($reg_report['player_lane_combos'])) {
    $res['lane_combos'] = "";
    if (check_module($parent_module."lane_combos")) {
      include_once($root."/modules/view/generators/pairs.php");
      $res['lane_combos'] =  "<div class=\"content-text\">".
                              locale_string("desc_players_lane_combos", [ "liml"=>$reg_report['settings']['limiter_lower']+1 ] )."</div>";
      $res['lane_combos'] .=  rg_generator_combos("region$region-player-lanecombos", $reg_report['player_lane_combos'], [], false);
    }
  }

  return $res;
}

?>
