<?php
$result["player_pairs"] = rg_query_player_pairs(
  $conn,
  $result['players_summary'],
  $result['random']['matches_total'],
  $limiter,
  null
);


if($lg_settings['ana']['player_pairs_matches']) {
  $result["player_pairs_matches"] = rg_query_player_pairs_matches($conn, $result["player_pairs"]);
}
?>
