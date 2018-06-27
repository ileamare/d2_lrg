<?php
include_once("head.php");

include_once("modules/functions/utf8ize.php");
include_once("modules/functions/migrate_params.php");
include_once("modules/functions/calc_median.php");

  echo("\nConnecting to database...\n");

  $conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);

  if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

  $result = array ();
  $result["league_name"]  = $lg_settings['league_name'];
  $result["league_desc"] = $lg_settings['league_desc'];
  $result['league_id'] = $lg_settings['league_id'];
  $result["league_tag"] = $lrg_league_tag;

  if(compare_ver($lg_settings['version'], $lrg_version) < 0) {
    if (!file_exists("templates/default.json")) die("[F] No default league template found, exitting.");
    $tmp = json_decode(file_get_contents("templates/default.json"), true);
    migrate_params($tmp, $lg_settings);
    $lg_settings = $tmp;
    unset($tmp);
  }

  /* first and last match */ {
    $sql = "SELECT matchid, start_date
            FROM matches
            ORDER BY start_date ASC;";

    if ($conn->multi_query($sql) !== TRUE) die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();
    $row = $query_res->fetch_row();

    $result["first_match"] = array( "mid" => $row[0], "date" => $row[1] );

    $query_res->free_result();

    $sql = "SELECT matchid, start_date
            FROM matches
            ORDER BY start_date DESC;";

    if ($conn->multi_query($sql) !== TRUE) die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();
    $row = $query_res->fetch_row();

    $result["last_match"] = array( "mid" => $row[0], "date" => $row[1] );

    $query_res->free_result();
  }

  # Random stats
  require_once("modules/analyzer/main/overview.php");

  # pick/ban heroes stats
  require_once("modules/analyzer/heroes/pickban.php");

  # limiters
  require_once("modules/analyzer/main/limiters.php");

  if ($lg_settings['ana']['records']) {
    require_once("modules/analyzer/main/records.php");
  }

  # game versions
  require_once("modules/analyzer/main/versions.php");
  # game modes
  require_once("modules/analyzer/main/modes.php");
  # game modes
  require_once("modules/analyzer/main/regions.php");

  # league days
  require_once("modules/analyzer/main/days.php");

  // Players Summary
  if($lg_settings['ana']['players']) {
    # player summary
    require_once("modules/analyzer/players/summary.php");
  }

  if ($lg_settings['ana']['avg_heroes']) {
    # average for heroes
    require_once("modules/analyzer/heroes/averages.php");
  }

  if ($lg_settings['ana']['players'] && $lg_settings['ana']['avg_players']) {
    # average for players
    require_once("modules/analyzer/players/averages.php");
  }

  if ($lg_settings['ana']['players'] && $lg_settings['ana']['player_positions']) {
    # players positions stats
    require_once("modules/analyzer/players/positions.php");
  }


  if ($lg_settings['ana']['draft_stages']) {
    # pick/ban draft stages stats
    require_once("modules/analyzer/heroes/draft.php");
  }

  if ($lg_settings['ana']['hero_positions']) {
    # heroes on positions
    require_once("modules/analyzer/heroes/positions.php");
  }

  if ($lg_settings['ana']['hero_sides']) {
    # heroes factions
    require_once("modules/analyzer/heroes/sides.php");
  }

  if ($lg_settings['ana']['hero_combos_graph']) {
    # heroes combo graph
    require_once("modules/analyzer/heroes/combo_graph.php");
  }

  if ($lg_settings['ana']['hero_pairs']) {
    # heroes pairs
    require_once("modules/analyzer/heroes/pairs.php");
  }

  if ($lg_settings['ana']['hero_triplets']) {
    # heroes trios
    require_once("modules/analyzer/heroes/trios.php");
  }

  if ($lg_settings['ana']['hero_vs_hero']) {
    # hero vs hero
    require_once("modules/analyzer/heroes/versus_hero.php");
  }

  if ($lg_settings['ana']['hero_summary']) {
    # heroes summary
    require_once("modules/analyzer/heroes/summary.php");
  }

  if ($lg_settings['main']['teams']) {
    require_once("modules/analyzer/teams/__main.php");

    if ($lg_settings['ana']['teams']['team_vs_team'])
      require_once("modules/analyzer/team_vs_team.php");
  } else {
    echo "[ ] Working for players competition...\n";

    if ($lg_settings['ana']['players'] && $lg_settings['ana']['pvp']) {
      # pvp grid
      require_once("modules/analyzer/pvp/pvp.php");
    }

    if ($lg_settings['ana']['players'] && $lg_settings['ana']['players_combo_graph']) {
      # pvp graph
      require_once("modules/analyzer/pvp/graph.php");
    }

    if ($lg_settings['ana']['players'] && $lg_settings['ana']['player_pairs']) {
      # pvp pairs
      require_once("modules/analyzer/pvp/pairs.php");
    }

    if ($lg_settings['ana']['players'] && $lg_settings['ana']['player_triplets']) {
      # pvp trios
      require_once("modules/analyzer/pvp/trios.php");
    }
  }

  if (isset($lg_settings['ana']['regions']) && is_array($lg_settings['ana']['regions'])) {
    # regions
    require_once("modules/analyzer/regions/__main.php");
  }

  if ($lg_settings['ana']['matchlist']) {
    # matches information
    require_once("modules/analyzer/matchlist.php");
  }

  # players metadata
  if ($lg_settings['ana']['players'])  {
    require_once("modules/analyzer/players/additional_data.php");
  }

 $result['settings'] = $lg_settings['web'];
 $result['settings']['limiter'] = $limiter;
 $result['settings']['limiter_triplets'] = $limiter_lower;
 $result['settings']['limiter_combograph'] = $limiter_graph;
 $result['ana_version'] = $lrg_version;

 echo("[ ] Encoding results to JSON\n");
 $output = json_encode(utf8ize($result));
 //$output = json_encode($result);

 $filename = "reports/report_".$lrg_league_tag.".json";
 $f = fopen($filename, "w") or die("[F] Couldn't open file to save results. Check working directory for `reports` folder.\n");
 fwrite($f, $output);
 fclose($f);
 echo("[S] Recorded results to file `reports/report_$lrg_league_tag.json`\n");

 ?>
