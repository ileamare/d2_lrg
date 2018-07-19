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
    if($lg_settings['main']['teams']) {
        $result["regions_data"][$region]['settings']['limiter_lower'] = (int)ceil($result["regions_data"][$region]['main']['matches']/
                                                              ($result["regions_data"][$region]['main']['teams_on_event']*4));
    } else {
        $result["regions_data"][$region]['settings']['limiter_lower'] = (int)ceil($median/16);
    }

    # records
    if (isset($lg_settings['ana']['regions']['records'])) {

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
    }

/*
  "heroes": {
    "pairs": true,
    "trios": true,
    "lane_combos": true,
    "positions": true,
    "summary": true
  },
 */


    if (isset($lg_settings['ana']['regions']['heroes_avg'])) {

    }
    if (isset($lg_settings['ana']['regions']['drafts'])) {

    }
    if (isset($lg_settings['ana']['regions']['hero_pairs'])) {

    }
    if (isset($lg_settings['ana']['regions']['hero_graph'])) {

    }
    if (isset($lg_settings['ana']['regions']['hero_positions'])) {

    }
    if (isset($lg_settings['ana']['regions']['hero_overview'])) {

    }

    # players
    if (isset($lg_settings['ana']['regions']['player_pairs'])) {

    }
    if (isset($lg_settings['ana']['regions']['player_graph'])) {

    }
    if (isset($lg_settings['ana']['regions']['players_avg'])) {

    }
    if (isset($lg_settings['ana']['regions']['players_positions'])) {

    }
    if (isset($lg_settings['ana']['regions']['players_overview'])) {

    }

    # teams summary
    if (isset($lg_settings['ana']['regions']['teams_summary'])) {

    }

    # participants and stuff
    if (isset($lg_settings['ana']['regions']['matches_list'])) {

    }
    if (isset($lg_settings['ana']['regions']['players'])) {

    }
    if (isset($lg_settings['ana']['regions']['teams'])) {

    }
}


# pickbans
# drafts
# pairs
# hero_graph
# hero_pos

# participants / players / teams
# matches list
# heroes_avg
# players_avg
?>
