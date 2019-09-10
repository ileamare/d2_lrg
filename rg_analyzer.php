<?php
include_once("head.php");
ini_set('memory_limit', '4000M');

include_once("modules/commons/utf8ize.php");
include_once("modules/commons/quantile.php");
include_once("modules/commons/generate_tag.php");
include_once("modules/commons/metadata.php");

echo("\nConnecting to database...\n");

$conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, $lrg_sql_db);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

$meta = new lrg_metadata;

$result = [];
$result["league_name"]  = $lg_settings['league_name'];
$result["league_desc"] = $lg_settings['league_desc'];
$result['league_id'] = $lg_settings['league_id'];
$result["league_tag"] = $lg_settings['league_tag'];
$avg_limit = $lg_settings['ana']['avg_limit'];

if (isset($lg_settings['teams']))
  $result["teams_interest"] = $lg_settings['teams'];

if(compare_ver($lg_settings['version'], $lrg_version) < 0) {
  if (!file_exists("templates/default.json")) die("[F] No default league template found, exitting.");
  $tmp = json_decode(file_get_contents("templates/default.json"), true);

  if(isset($options['T'])) {
    if (file_exists("templates/".$options['T'].".json"))
      $tmpl = json_decode(file_get_contents("templates/".$options['T'].".json"), true);

    $tmp = array_replace_recursive($tmp, $tmpl);
    unset($tmpl);
  }

  $tmp = array_replace_recursive($tmp, $lg_settings);
  $lg_settings = $tmp;
  unset($tmp);
} else if(isset($options['T'])) {
  if (file_exists("templates/".$options['T'].".json"))
    $tmp = json_decode(file_get_contents("templates/".$options['T'].".json"), true);

  $tmp = array_replace_recursive($tmp, $lg_settings);
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


# Heroes modules
require_once("modules/analyzer/heroes/__main.php");

$result["players_additional"] = [];
// Players Summary
if($lg_settings['ana']['players']) {
  # player summary
  require_once("modules/analyzer/players/summary.php");
}

if ($lg_settings['ana']['players'] && $lg_settings['ana']['avg_players']) {
  # average for players
  require_once("modules/analyzer/players/averages.php");
}

if ($lg_settings['ana']['players'] && $lg_settings['ana']['player_positions']) {
  # players positions stats
  require_once("modules/analyzer/players/positions.php");
}

if($lg_settings['ana']['players'] && $lg_settings['ana']['players_draft']) {
  # player draft
  require_once("modules/analyzer/players/draft.php");
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

  if ($lg_settings['ana']['players'] && $lg_settings['ana']['players_lane_combos']) {
    # pvp lane combos
    require_once("modules/analyzer/pvp/lane_combos.php");
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

$filename = $options['o'] ?? "reports/report_".$lg_settings['league_tag'].".json";
$f = fopen($filename, "w") or die("[F] Couldn't open file to save results.\n");
fwrite($f, $output);
fclose($f);
echo("[S] Recorded results to file `$filename`\n");

?>
