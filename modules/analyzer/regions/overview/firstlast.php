<?php

/* first and last match */
$sql = "SELECT matchid, start_date
        FROM matches
        WHERE cluster IN (".implode(",", $clusters).")
        ORDER BY start_date ASC;";

if ($conn->multi_query($sql) !== TRUE) die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$row = $query_res->fetch_row();

$result["regions_data"][$region]['overview']["first_match"] = array( "mid" => $row[0], "date" => $row[1] );

$query_res->free_result();

$sql = "SELECT matchid, start_date
        FROM matches
        WHERE cluster IN (".implode(",", $clusters).")
        ORDER BY start_date DESC;";

if ($conn->multi_query($sql) !== TRUE) die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

$query_res = $conn->store_result();
$row = $query_res->fetch_row();

$result["regions_data"][$region]['overview']["last_match"] = array( "mid" => $row[0], "date" => $row[1] );

$query_res->free_result();

?>
