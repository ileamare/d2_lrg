<?php
# open metadata
# decide, what clusters are part of a region
if(!isset($metadata)) {
  $metadata = json_decode(file_get_contents("res/metadata.json"), true);
}

$regions = [];
foreach($metadata['clusters'] as $cluster => $region) {
  if(!isset($regions[$region])) $regions[$region] = [];
  $regions[$region][] = $cluster;
}

if(sizeof($result["regions"]) == 1 ||
    (sizeof($result["regions"]) == 2 && min($result["regions"]) < $limiter*2) )
  unset($lg_settings['ana']['regions']);

$reg_matches = [];
foreach($result["regions"] as $clid => $matches) {
  if(!isset($reg_matches[ $metadata['clusters'][$clid] ]))
    $reg_matches[ $metadata['clusters'][$clid] ] = $matches;
  else
    $reg_matches[ $metadata['clusters'][$clid] ] += $matches;
}

if(sizeof($reg_matches) == 1 ||
    (sizeof($reg_matches) == 2 && min($result["regions"]) <= $limiter_graph) ) {
  unset($lg_settings['ana']['regions']);
  return false;
}

foreach ($regions as $region => $clusters) {
    # main stats
    $err = include("overview.php");
    if ($err) continue;

    # pickbans
    include("heroes/pickban.php");

    $picks = [];
    foreach($result["regions_data"][$region]["pickban"] as $hero)
        $picks[] = $hero["matches_total"];
    $median = calculate_median($picks);
    unset($picks);

    $result["regions_data"][$region]['settings'] = [];

    $result["regions_data"][$region]['settings']['limiter_higher'] = (int)ceil($median/6);
    $result["regions_data"][$region]['settings']['limiter_graph'] = (int)ceil($median/3);
    if($lg_settings['main']['teams'] && $result["regions_data"][$region]['main']['teams_on_event']) {
        $result["regions_data"][$region]['settings']['limiter_lower'] = (int)ceil($result["regions_data"][$region]['main']['matches']/
                                                              ($result["regions_data"][$region]['main']['teams_on_event']*4));
    } else {
        $result["regions_data"][$region]['settings']['limiter_lower'] = (int)ceil($median/20);
    }

    # records
    if (isset($lg_settings['ana']['regions']['records'])) {
      require("records.php");
    }

    # heroes
    if (isset($lg_settings['ana']['regions']['heroes'])) {
      if (isset($lg_settings['ana']['regions']['heroes']['haverages']) && $lg_settings['ana']['regions']['heroes']['haverages']) {
        require("heroes/haverages.php");
      }
      if (isset($lg_settings['ana']['regions']['heroes']['draft']) && $lg_settings['ana']['regions']['heroes']['draft']) {
        require("heroes/draft.php");
      }
      if (isset($lg_settings['ana']['regions']['heroes']['meta_graph']) && $lg_settings['ana']['regions']['heroes']['meta_graph']) {
        require("heroes/meta_graph.php");
      }
      if (isset($lg_settings['ana']['regions']['heroes']['pairs']) && $lg_settings['ana']['regions']['heroes']['pairs']) {
        require("heroes/pairs.php");
      }
      if (isset($lg_settings['ana']['regions']['heroes']['trios']) && $lg_settings['ana']['regions']['heroes']['trios']) {
        require("heroes/trios.php");
      }
      if (isset($lg_settings['ana']['regions']['heroes']['lane_combos']) && $lg_settings['ana']['regions']['heroes']['lane_combos']) {
        require("heroes/lane_combos.php");
      }
      if (isset($lg_settings['ana']['regions']['heroes']['positions']) && $lg_settings['ana']['regions']['heroes']['positions']) {
        require("heroes/positions.php");
      }
      if (isset($lg_settings['ana']['regions']['heroes']['summary']) && $lg_settings['ana']['regions']['heroes']['summary']) {
        require("heroes/summary.php");
      }
    }

    # players
    if (isset($lg_settings['ana']['regions']['players']) && $lg_settings['ana']['players']) {
      require("players/summary.php");

      if (isset($lg_settings['ana']['regions']['players']['haverages']) && $lg_settings['ana']['regions']['players']['haverages']) {
        require("players/haverages.php");
      }
      if (isset($lg_settings['ana']['regions']['players']['draft']) && $lg_settings['ana']['regions']['players']['draft']) {
        require("players/draft.php");
      }
      if (!$lg_settings['main']['teams'] && isset($lg_settings['ana']['regions']['players']['parties_graph']) && $lg_settings['ana']['regions']['players']['parties_graph']) {
        require("players/graph.php");
      }
      if (!$lg_settings['main']['teams'] && isset($lg_settings['ana']['regions']['players']['pairs']) && $lg_settings['ana']['regions']['players']['pairs']) {
        require("players/pairs.php");
      }
      if (!$lg_settings['main']['teams'] && isset($lg_settings['ana']['regions']['players']['trios']) && $lg_settings['ana']['regions']['players']['trios']) {
        require("players/trios.php");
      }
      if (!$lg_settings['main']['teams'] && isset($lg_settings['ana']['regions']['players']['lane_combos']) && $lg_settings['ana']['regions']['players']['lane_combos']) {
        require("players/lane_combos.php");
      }
      if (isset($lg_settings['ana']['regions']['players']['positions']) && $lg_settings['ana']['regions']['players']['positions']) {
        require("players/positions.php");
      }
    }

/*
"teams": {
  "summary": true,
  "cards": true
},
"matches": true,
"participants": true
*/

    # teams summary
    if ($lg_settings['main']['teams']) {
      require("teams.php");
    }

    # matches
    if (isset($lg_settings['ana']['matchlist']) && $lg_settings['ana']['matchlist']) {
      require("matches.php");
    }
}

?>
