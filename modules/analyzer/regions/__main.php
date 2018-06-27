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
    $err = require("overview.php");
    if ($err) continue;
    # pickbans
    require("heroes/pickban.php");


    # records

    # heroes
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
