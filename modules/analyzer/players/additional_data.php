<?php
$result["players"] = array();
$sql = "SELECT playerid, nickname FROM players";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for PLAYERS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["players"][$row[0]] = $row[1];
}

$query_res->free_result();

$result["players_additional"] = array();

foreach ($result['players'] as $pid => &$name) {
  $result["players_additional"][$pid] = array();

  /*
    team
    matches overall
    won matches
    hero pool size
    positions (matches played, matches won, heroes)
    heroes (hero, matches played, matches won)
    gpm xpm pings
    TODO average fantasy values
  */

  if ($lg_settings['main']['teams']) {
    # TODO player positions based on team roster
    $sql = "SELECT teamid FROM teams_rosters WHERE playerid = $pid;";

    if ($conn->multi_query($sql) === TRUE);
    else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

    $query_res = $conn->store_result();

    $row = $query_res->fetch_row();
    $result["players_additional"][$pid]['team'] = $row[0];
    if(isset($result['teams'][$row[0]]['tag']))
      $name = $result['teams'][$row[0]]['tag'].".".$name;

    $query_res->free_result();
  }

  # matches overall
  $sql = "SELECT count(distinct matchid) FROM matchlines WHERE playerid = $pid GROUP BY playerid;";

  if ($conn->multi_query($sql) === TRUE);
  else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();
  $result["players_additional"][$pid]['matches'] = $row[0];

  $query_res->free_result();

  # wins
  $sql = "SELECT SUM(NOT matches.radiantWin XOR matchlines.isRadiant), SUM(gpm)/SUM(1), SUM(xpm)/SUM(1) FROM matchlines JOIN matches
          ON matches.matchid = matchlines.matchid WHERE playerid = $pid GROUP BY playerid;";

  if ($conn->multi_query($sql) === TRUE);
  else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();
  $result["players_additional"][$pid]['won'] = $row[0];
  $result["players_additional"][$pid]['gpm'] = $row[1];
  $result["players_additional"][$pid]['xpm'] = $row[2];

  $query_res->free_result();

  # hero pool size
  $sql = "SELECT count(distinct heroid) FROM matchlines WHERE playerid = $pid GROUP BY playerid;";

  if ($conn->multi_query($sql) === TRUE);
  else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();
  $result["players_additional"][$pid]['hero_pool_size'] = $row[0];

  $query_res->free_result();

  # heroes
  $sql = "SELECT ml.heroid, COUNT(ml.matchid) matches, SUM(NOT m.radiantWin XOR ml.isRadiant) wins FROM matchlines ml JOIN matches m
          ON m.matchid = ml.matchid WHERE ml.playerid = $pid GROUP BY ml.heroid ORDER BY wins DESC, matches DESC ;";

  if ($conn->multi_query($sql) === TRUE);
  else die("[F] Unexpected problems when requesting database1.\n".$conn->error."\n");

  $query_res = $conn->store_result();
  $result["players_additional"][$pid]['heroes'] = array();

  for ($i=0, $row = $query_res->fetch_row(); $i<4 && $row != null; $i++, $row = $query_res->fetch_row()) {
    $result["players_additional"][$pid]['heroes'][] = array(
      "heroid" => $row[0],
      "matches" => $row[1],
      "wins" => $row[2]
    );
  }

  $query_res->free_result();

  # positions
  $sql = "SELECT aml.lane, COUNT(distinct aml.matchid) matches, SUM(NOT m.radiantWin XOR ml.isRadiant) wins FROM adv_matchlines aml
          JOIN matches m ON m.matchid = aml.matchid
          JOIN matchlines ml ON aml.matchid = ml.matchid  AND aml.playerid = ml.playerid
          WHERE ml.playerid = $pid AND aml.isCore = 1 GROUP BY aml.lane ORDER BY wins DESC, matches DESC;";

  if ($conn->multi_query($sql) === TRUE);
  else die("[F] Unexpected problems when requesting database1.\n".$conn->error."\n");

  $query_res = $conn->store_result();
  $result["players_additional"][$pid]['positions'] = array();

  for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
    $result["players_additional"][$pid]['positions'][] = array(
      "core" => 1,
      "lane" => $row[0],
      "matches" => $row[1],
      "wins" => $row[2]
    );
  }

  $query_res->free_result();

  $sql = "SELECT aml.lane, COUNT(distinct aml.matchid) matches, SUM(NOT m.radiantWin XOR ml.isRadiant) wins FROM adv_matchlines aml
          JOIN matches m ON m.matchid = aml.matchid
          JOIN matchlines ml ON aml.matchid = ml.matchid AND aml.playerid = ml.playerid
          WHERE aml.playerid = $pid AND aml.isCore = 0 GROUP BY aml.isCore ORDER BY wins DESC, matches DESC;";

  if ($conn->multi_query($sql) === TRUE);
  else die("[F] Unexpected problems when requesting database1.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  $row = $query_res->fetch_row();
  if($row != null)
    $result["players_additional"][$pid]['positions'][] = array(
      "core" => 0,
      "lane" => 0,
      "matches" => $row[1],
      "wins" => $row[2]
    );

  uasort($result["players_additional"][$pid]['positions'], function($a, $b) {
    if($a['matches'] == $b['matches']) return 0;
    else return ($a['matches'] < $b['matches']) ? 1 : -1;
  });

  $query_res->free_result();
}
?>
