<?php
$result["versions"] = array();

$sql = "SELECT version, count(distinct matchid) matches
        FROM matches
        GROUP BY version
        ORDER BY matches DESC;";

if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for VERSIONS.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
  if ($row[0] < 100)
    $result["versions"][$row[0]*100] = $row[1];
  else
    $result["versions"][$row[0]] = $row[1];
}

$query_res->free_result();
?>
