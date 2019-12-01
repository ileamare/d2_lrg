<?php

$result["regions_data"][$region]["hero_trios"] = rg_query_hero_trios(
  $conn, 
  $result["regions_data"][$region]['pickban'], 
  $result["regions_data"][$region]['main']['matches'], 
  $limiter_lower,
  $clusters,
  null
);

?>
