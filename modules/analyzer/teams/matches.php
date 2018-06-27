<?php
$result["teams"][$id]["matches"] = array();

$sql = "SELECT matchid
        FROM teams_matches
        WHERE teamid = ".$id.";";

if ($conn->multi_query($sql) === TRUE) echo "[S] MATCHES LIST.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row();
     $row != null;
     $row = $query_res->fetch_row()) {
  $result["teams"][$id]["matches"][$row[0]] = 0;
}

$query_res->free_result();
?>
