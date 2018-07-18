<?php

function rg_view_generate_teams_profiles($context, $context_mod) {
  global $mod, $root, $strings, $unset_module, $report;
  $res = [];
  if($mod == substr($context_mod, 0, strlen($context_mod)-1)) $unset_module = true;

  foreach ($context as $tid => $team) {
    $res['team'.$tid] = [];
    $strings['en']["team".$tid] = team_name($tid);

    if(check_module($context_mod."team".$tid)) {
      if($mod == $context_mod."team".$tid) $unset_module = true;

      if (isset($context[$tid]['averages'])) {
        $res["team".$tid]['overview'] = "";

        if(check_module($context_mod."team".$tid."-overview")) {
          $res["team".$tid]['overview'] .= "<div class=\"content-cards\">".team_card($tid)."</div>";
          $res["team".$tid]['overview'] .= "<table id=\"teams-$tid-avg-table\" class=\"list\"> ";
          foreach ($context[$tid]['averages'] as $key => $value) {
            $res["team".$tid]['overview'] .= "<tr><td>".locale_string( $key )."</td><td>".number_format($value, 2)."</td></tr>";
          }
          $res["team".$tid]['overview'] .= "</table>";
          $res["team".$tid]['overview'] .= "<div class=\"content-text\">".locale_string("desc_teams")."</div>";
        }
      }

      if (isset($context[$tid]['draft'])) {
        $res["team".$tid]['draft'] = "";

        if(check_module($context_mod."team".$tid."-draft")) {
          include_once("$root/modules/view/generators/draft.php");
          $res["team".$tid]['draft'] = rg_generator_draft("team$tid-draft", $context[$tid]['pickban'], $context[$tid]['draft'], $context[$tid]['matches_total']);
        }
      }
      if (isset($context[$tid]['draft_vs'])) {
        $res["team".$tid]['vsdraft'] = "";

        if(check_module($context_mod."team".$tid."-vsdraft")) {
          include_once("$root/modules/view/generators/draft.php");
          $res["team".$tid]['vsdraft'] = rg_generator_draft("team$tid-vsdraft", $context[$tid]['pickban_vs'], $context[$tid]['draft_vs'], $context[$tid]['matches_total']);
        }
      }

      $res["team".$tid]['heroes'] = [];
      if ($mod == $context_mod."team".$tid."-heroes") $unset_module = true;

      if (isset($context[$tid]['hero_positions'])) {
        $res["team".$tid]['heroes']['positions'] = [];

        if(check_module($context_mod."team".$tid."-heroes-positions")) {
          $parent_module = $context_mod."team".$tid."-heroes-positions-";
          if ($mod == $context_mod."team".$tid."-heroes-positions") $unset_module = true;
          $res["team".$tid]['heroes']['positions']['overview'] = "";
          for ($i=1; $i>=0; $i--) {
            for ($j=1; $j<6 && $j>0; $j++) {
              if (!$i) { $j = 0; }
              if(!empty($context[$tid]['hero_positions'][$i][$j]))
                $res["team".$tid]['heroes']['positions']["position_$i.$j"]  = "";

              if (!$i) { break; }
            }
          }

          if (check_module($parent_module."overview")) {
            generate_positions_strings();
            include_once($root."/modules/view/generators/positions_overview.php");
            $res["team".$tid]['heroes']['positions']["overview"] = rg_generator_positions_overview("team$tid-heroes-positions-overview", $context[$tid]['hero_positions']);
            $res["team".$tid]['heroes']['positions']["overview"] .= "<div class=\"content-text\">".locale_string("desc_heroes_positions")."</div>";
          }
          {
            include_once($root."/modules/view/generators/summary.php");

            for ($i=1; $i>=0; $i--) {
              for ($j=1; $j<6 && $j>0; $j++) {
                if (!$i) { $j = 0; }

                if (!check_module($parent_module."position_$i.$j") || empty($context[$tid]['hero_positions'][$i][$j])) {
                  if (!$i) { break; }
                  continue;
                }

                $res["team".$tid]['heroes']['positions']["position_$i.$j"] = rg_generator_summary("team$tid-heroes-positions-$i-$j", $context[$tid]['hero_positions'][$i][$j]);
                $res["team".$tid]['heroes']['positions']["position_$i.$j"] .= "<div class=\"content-text\">".locale_string("desc_heroes_positions")."</div>";
                if (!$i) { break; }
              }
            }
          }
        }
      }
      if (isset($context[$tid]['hero_graph']) && $report['settings']['heroes_combo_graph']) {
        $res["team".$tid]['heroes']['meta_graph'] = "";
        if(check_module($context_mod."team".$tid."-heroes-meta_graph")) {
          include_once("$root/modules/view/generators/meta_graph.php");
          $locale_settings = ["lim" => $report['settings']['limiter_triplets']+1,
              "per" => "35%"
          ];
          $res["team".$tid]['heroes']['meta_graph'] = "<div class=\"content-text\">".locale_string("desc_meta_graph", $locale_settings)."</div>";
          $res["team".$tid]['heroes']['meta_graph'] .= rg_generator_meta_graph("team$tid-heroes-meta-graph", $context[$tid]['hero_graph'], $context[$tid]['pickban']);
          $res["team".$tid]['heroes']['meta_graph'] .= "<div class=\"content-text\">".locale_string("desc_meta_graph_add", $locale_settings)."</div>";
        }
      }
      if ((isset($context[$tid]['hero_pairs']) && !empty($context[$tid]['hero_pairs'])) ||
          (isset($context[$tid]['hero_triplets']) && !empty($context[$tid]['hero_triplets']))) {
        $res["team".$tid]['heroes']['combos'] = [];

        if(check_module($context_mod."team".$tid."-heroes-combos")) {
          $parent_module = $context_mod."team".$tid."-heroes-combos-";
          if ($mod == $context_mod."team".$tid."-heroes-combos") $unset_module = true;

          if(isset($context[$tid]['hero_pairs'])) {
            $res["team".$tid]['heroes']['combos']['pairs'] = "";
            if (check_module($parent_module."pairs")) {
              include_once($root."/modules/view/generators/pairs.php");
              $res["team".$tid]['heroes']['combos']['pairs'] =  "<div class=\"content-text\">".locale_string("desc_heroes_pairs", [ "limh"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
              $res["team".$tid]['heroes']['combos']['pairs'] .=  rg_generator_pairs("hero-pairs",
                                                 $context[$tid]['hero_pairs'],
                                                 (isset($context[$tid]['hero_pairs_matches']) ? $context[$tid]['hero_pairs_matches'] : [])
                                               );

            }
          }
          if(isset($context[$tid]['hero_triplets']) && !empty($context[$tid]['hero_triplets'])) {
            $res["team".$tid]['heroes']['combos']['trios'] = "";
            if (check_module($parent_module."trios")) {
              include_once($root."/modules/view/generators/trios.php");
              $res["team".$tid]['heroes']['combos']['trios'] =  "<div class=\"content-text\">".locale_string("desc_heroes_trios", [ "liml"=>$report['settings']['limiter_triplets']+1 ] )."</div>";
              $res["team".$tid]['heroes']['combos']['trios'] .= rg_generator_trios("hero-trios",
                                                 $context[$tid]['hero_triplets'],
                                                 (isset($context[$tid]['hero_triplets_matches']) ? $context[$tid]['hero_triplets_matches'] : [])
                                               );
            }
          }
        }
      }

      if (isset($context[$tid]['players_draft'])) {
        $res["team".$tid]['players'] = [];
        if ($mod == $context_mod."team".$tid."-players") $unset_module = true;

        if (isset($context[$tid]['players_draft'])) {
            $res["team".$tid]['players']['draft'] = "";

            if(check_module($context_mod."team".$tid."-players-draft")) {
              $res["team".$tid]['players']['draft'] = rg_generator_draft("team$tid-players-draft",
                                                                          $context[$tid]['players_draft_pb'],
                                                                          $context[$tid]['players_draft'],
                                                                          $context[$tid]['matches_total'],
                                                                        false);
            }
        }
      }



      if (isset($context[$tid]['matches']) && isset($report['matches'])) {
        $res["team".$tid]['matches'] = "";

        if(check_module($context_mod."team".$tid."-matches")) {
          $res["team".$tid]['matches'] = "<div class=\"content-text\">".locale_string("desc_matches")."</div>";
          $res["team".$tid]['matches'] .= "<div class=\"content-cards\">";
          foreach($context[$tid]['matches'] as $matchid => $match) {
            $res["team".$tid]['matches'] .= match_card($matchid);
          }
          $res["team".$tid]['matches'] .= "</div>";
        }
      }
      if (isset($report['players'])) {
        $res["team".$tid]['roster'] = "";

        if(check_module($context_mod."team".$tid."-roster")) {
          generate_positions_strings();
          $res["team".$tid]['roster'] = "<div class=\"content-text\">".locale_string("desc_roster")."</div>";
          $res["team".$tid]['roster'] .= "<div class=\"content-cards\">";
          foreach($context[$tid]['active_roster'] as $player) {
            $res["team".$tid]['roster'] .= player_card($player);
          }
          $res["team".$tid]['roster'] .= "</div>";
        }
      }
    }
  }

  return $res;
}

?>
