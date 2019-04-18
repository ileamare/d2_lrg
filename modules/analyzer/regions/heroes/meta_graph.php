<?php
$result["regions_data"][$region]["heroes_meta_graph"] = [];

foreach($result["regions_data"][$region]["hero_pairs"] as $pair) {
  if($pair['matches']-$pair['expectation'] < $pair['matches']*0.05) //min deviation 10% of total matches
    continue;
    $result["regions_data"][$region]["heroes_meta_graph"][] = $pair;
}

?>
