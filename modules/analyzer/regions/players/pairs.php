<?php
$result["regions_data"][$region]["player_pairs"] = rg_query_player_pairs(
  $conn,
  $result["regions_data"][$region]['players_summary'],
  $result["regions_data"][$region]['main']['matches'],
  $result["regions_data"][$region]['settings']['limiter_higher'],
  $clusters
);
