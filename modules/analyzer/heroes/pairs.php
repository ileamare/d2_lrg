<?php
$result["hero_pairs"] = rg_query_hero_pairs($result['pickban'], $result['random']['matches_total'], $limiter_graph, null);

if ($lg_settings['ana']['hero_pairs_matches']) {
  $result["hero_pairs_matches"] = rg_query_hero_pairs_matches($result["hero_pairs"]);
}

?>
