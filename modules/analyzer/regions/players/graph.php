<?php
$result["regions_data"][$region]["players_parties_graph"] = rg_query_player_graph(
  $conn,
  $result["regions_data"][$region]['players_summary'],
  $result["regions_data"][$region]['main']['matches'],
  $result["regions_data"][$region]['settings']['limiter_graph'],
  $clusters
);
