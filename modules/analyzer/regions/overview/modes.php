<?php
$result["regions_data"][$region]['overview']["modes"] = [];

$sql = "SELECT modeID, count(distinct matchid) matches
        FROM matches
        WHERE cluster IN (".implode(",", $clusters).")
        GROUP BY modeID
        ORDER BY matches DESC;";

if ($conn->multi_query($sql) === TRUE);# echo "[S] Requested data for GAME MODES.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  $result["regions_data"][$region]['overview']["modes"][$row[0]] = $row[1];
}

$query_res->free_result();
?>
