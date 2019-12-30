<?php
$result["hero_positions"] = array ();

echo "[S] Requested data for HERO POSITIONS\n";

$result["hero_positions"] = rg_query_hero_positions($conn, null, null);

if ($lg_settings['ana']['hero_positions_matches']) {
  #   include matchids
  $result["hero_positions_matches"] = rg_query_hero_positions_matches($conn, $result["hero_positions"]);
}
?>
