<?php

$result["teams"][$id]["hero_triplets"] = rg_query_hero_trios(
  $conn, 
  $result["teams"][$id]['pickban'], 
  $result["teams"][$id]['matches_total'], 
  $limiter_lower,
  null,
  $id
);

?>
