<?php

$result["regions_data"][$region]["hero_pairs"] = rg_query_hero_pairs(
  $result["regions_data"][$region]['pickban'], 
  $result["regions_data"][$region]['main']['matches'], 
  $result["regions_data"][$region]['settings']['limiter_graph'], 
  $clusters
);

?>
