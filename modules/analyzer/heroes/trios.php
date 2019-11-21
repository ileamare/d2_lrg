<?php

$result["hero_triplets"] = rg_query_hero_trios(
  $result['pickban'], 
  $result['random']['matches_total'], 
  $limiter_lower,
  null,
  null
);

if ($lg_settings['ana']['hero_triplets_matches']) {
  $result["hero_triplets_matches"] = rg_query_hero_trios_matches($result["hero_triplets"]);
}

?>
