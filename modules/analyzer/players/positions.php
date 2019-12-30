<?php
$result["player_positions"] = rg_query_player_positions($conn, null, null);
echo "[S] Requested data for PLAYER POSITIONS.\n";


if($lg_settings['ana']['player_positions_matches']) {
  $result["player_positions_matches"][$core][$lane] = rg_query_player_positions_matches($conn, $result["player_positions"]);
}
?>
