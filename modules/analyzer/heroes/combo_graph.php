<?php
$result["hero_combos_graph"] = [];

foreach($result["hero_pairs"] as $pair) {
  if($pair['matches']-$pair['expectation'] < $pair['matches']*0.05) //min deviation 10% of total matches
    continue;
  $result["hero_combos_graph"][] = [
    "heroid1" => $pair['heroid1'],
    "heroid2" => $pair['heroid2'],
    "matches" => $pair['matches'],
    "wins" => $pair['wins'],
    "dev_pct" => ($pair['matches']-$pair['expectation'])/$pair['matches'],
  ];
}

?>
