<?php

$result["regions_data"][$region]["matches"] = [];

$sql = "SELECT matchid
        FROM matches
        WHERE matches.cluster IN (".implode(",", $clusters).");";

if ($conn->multi_query($sql) === TRUE);# echo "[S] MATCHES LIST.\n";
else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();

for ($row = $query_res->fetch_row();
     $row != null;
     $row = $query_res->fetch_row()) {
  $result["regions_data"][$region]["matches"][$row[0]] = "";
}

?>
