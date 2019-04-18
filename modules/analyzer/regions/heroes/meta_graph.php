<?php
$result["regions_data"][$region]["heroes_meta_graph"] = [];

foreach($result["regions_data"][$region]["hero_pairs"] as $pair) {
  if($pair['matches']-$pair['expectation'] < $pair['matches']*0.05) //min deviation 10% of total matches
    continue;
    $result["regions_data"][$region]["heroes_meta_graph"][] = [
      "heroid1" => $pair['heroid1'],
      "heroid2" => $pair['heroid2'],
      "matches" => $pair['matches'],
      "wins" => $pair['wins'],
      "dev_pct" => ($pair['matches']-$pair['expectation'])/$pair['matches'],
    ];
}

?>
