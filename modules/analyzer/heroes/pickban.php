<?php
$sql = "SELECT draft.hero_id hero_id, SUM(1) matches, SUM(NOT matches.radiantWin XOR draft.is_radiant)/SUM(1) winrate
	   FROM draft JOIN matches ON draft.matchid = matches.matchid
	   WHERE is_pick = true
	   GROUP BY draft.hero_id;";

$result["pickban"] = array();

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["pickban"][$row[0]] = array (
    "matches_total"   => $row[1],
    "matches_picked"  => $row[1],
    "winrate_picked"  => $row[2],
    "matches_banned"  => 0,
    "winrate_banned"  => 0
  );
}

$query_res->free_result();

$sql = "SELECT draft.hero_id hero_id, SUM(1) matches, SUM(NOT matches.radiantWin XOR draft.is_radiant)/SUM(1) winrate
	   FROM draft JOIN matches ON draft.matchid = matches.matchid
	   WHERE is_pick = false
	   GROUP BY draft.hero_id
ORDER BY winrate DESC, matches DESC;";

if ($conn->multi_query($sql) === TRUE);
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if(isset($result["pickban"][$row[0]])) {
    $result["pickban"][$row[0]] = array (
      "matches_total"   => ($result["pickban"][$row[0]]["matches_total"]+$row[1]),
      "matches_picked"  => $result["pickban"][$row[0]]["matches_picked"],
      "winrate_picked"  => $result["pickban"][$row[0]]["winrate_picked"],
      "matches_banned"  => $row[1],
      "winrate_banned"  => $row[2]
    );
  } else
    $result["pickban"][$row[0]] = array (
      "matches_total"   => $row[1],
      "matches_picked"  => 0,
      "winrate_picked"  => 0,
      "matches_banned"  => $row[1],
      "winrate_banned"  => $row[2]
    );
}

$query_res->free_result();
?>
