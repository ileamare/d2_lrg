<?php
# team competitions placeholder
$result['teams'] = [];

$sql = "SELECT teamid, name, tag FROM teams;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for TEAMS LIST.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result['teams'][$row[0]] = array(
    "name" => $row[1],
    "tag"  => $row[2]
  );
}

$query_res->free_result();

foreach($result['teams'] as $id => $team) {
  # main information
  require("overview.php");
  # averages
  require("averages.php");
  # heroes pickbans
  require("pickban.php");

  if ($lg_settings['ana']['teams']['draft']) {
    # heroes draft
    require("heroes/draft.php");
  }

  if ($lg_settings['ana']['teams']['draft_against']) {
    # heroes draft againt team
    require("heroes/draft_vs.php");
  }

  if ($lg_settings['ana']['teams']['heropos']) {
    # heroes positions
    require("heroes/positions.php");
  }

  if ($lg_settings['ana']['teams']['hero_graph']) {
    # heroes graph
    require("heroes/graph.php");
  }

  if ($lg_settings['ana']['teams']['pairs']) {
    # heroes pairs
    require("heroes/pairs.php");
  }

  if ($lg_settings['ana']['teams']['triplets']) {
    # heroes trios
    require("heroes/trios.php");
  }

  if ($lg_settings['ana']['teams']['players_draft']) {
    # players draft
    require("players/draft.php");
  }

  if ($lg_settings['ana']['teams']['matches']) {
    # matches
    require("matches.php");
  }
}
?>
