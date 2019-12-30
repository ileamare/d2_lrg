<?php
$result["player_triplets"] = rg_query_player_trios(
  $conn, 
  $result['players_summary'], 
  $result['random']['matches_total'], 
  $limiter_lower, 
  null
);


if($lg_settings['ana']['player_triplets_matches']) {
  $result["player_triplets_matches"] = rg_query_player_trios_matches($conn, $result["player_triplets"]);
}
?>
