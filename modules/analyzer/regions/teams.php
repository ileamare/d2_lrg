<?php

$result["regions_data"][$region]["teams"] = [];

$sql = "SELECT teams_matches.teamid, COUNT(distinct matches.matchid)
        FROM matches JOIN teams_matches ON teams_matches.matchid = matches.matchid
        WHERE matches.cluster IN (".implode(",", $clusters).")
        GROUP BY teams_matches.teamid;";

if ($conn->multi_query($sql) === TRUE);# echo "[S] MATCHES LIST.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["regions_data"][$region]["teams"][$row[0]] = $row[1];

  if(!isset($result['teams'][$row[0]]['regions']))
    $result['teams'][$row[0]]['regions'] = [];

  $result['teams'][$row[0]]['regions'][$region] = $row[1];
}

?>
