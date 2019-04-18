<?php

$modules['heroes']['combos'] = [];

function rg_view_generate_heroes_combos() {
  global $report, $parent, $root, $unset_module, $mod;
  if($mod == $parent."combos") $unset_module = true;
  $parent_module = $parent."combos-";
  $res = [];
  include_once($root."/modules/view/generators/combos.php");

  if(isset($report['hero_pairs'])) {
    $res['pairs'] = "";
    if (check_module($parent_module."pairs")) {
      $res['pairs'] =  "<div class=\"content-text\">".locale_string("desc_heroes_pairs", [ "limh"=>$report['settings']['limiter_combograph']+1 ] )."</div>";
      $res['pairs'] .=  rg_generator_combos("hero-pairs",
                                         $report['hero_pairs'],
                                         (isset($report['hero_pairs_matches']) ? $report['hero_pairs_matches'] : [])
                                       );
    }
  }
  if(isset($report['hero_triplets']) && !empty($report['hero_triplets'])) {
    $res['trios'] = "";
    if (check_module($parent_module."trios")) {
      $res['trios'] =  "<div class=\"content-text\">".locale_string("desc_heroes_trios", [ "liml"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
      $res['trios'] .= rg_generator_combos("hero-trios",
                                         $report['hero_triplets'],
                                         (isset($report['hero_triplets_matches']) ? $report['hero_triplets_matches'] : [])
                                       );
    }
  }
  if(isset($report['hero_lane_combos'])) {
    $res['lane_combos'] = "";
    if (check_module($parent_module."lane_combos")) {
      $res['lane_combos'] =  "<div class=\"content-text\">".locale_string("desc_heroes_lane_combos", [ "liml"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
      $res['lane_combos'] .=  rg_generator_combos("hero-lanecombos", $report['hero_lane_combos'], []);
    }
  }

  return $res;
}

?>
