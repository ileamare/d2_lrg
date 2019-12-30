<?php
$result["regions_data"][$region]["player_trios"] = rg_query_player_trios(
  $conn, 
  $result["regions_data"][$region]['players_summary'], 
  $result["regions_data"][$region]['main']['matches'], 
  $result["regions_data"][$region]['settings']['limiter_lower'], 
  $clusters
);

