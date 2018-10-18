<?php

function rg_generator_pvp_unwrap_data($context, $context_wrs, $heroes_flag = true) {
  if(!sizeof($context)) return [];

  $pvp = [];
  $id = $heroes_flag ? "heroid" : "playerid";
  $sid = $heroes_flag ? "h" : "p";

  if(empty($context_wrs)) $nodiff = true;
  else {
    $nodiff = false;
    $wr_id = $heroes_flag ? "winrate_picked" : "winrate";
  }

  foreach($context as $line) {
    if( !isset($pvp[ $line[$id.'1'] ]) )
      $pvp[ $line[$id.'1'] ] = [];
    if( !isset($pvp[ $line[$id.'2'] ]) )
      $pvp[ $line[$id.'2'] ] = [];

    $pvp[ $line[$id.'1'] ][ $line[$id.'2'] ] = [
      "winrate" => $line[$sid.'1winrate'],
      "matches" => $line['matches'],
      "won" => $line[$sid.'1won'],
      "lost" => $line['matches']-$line[$sid.'1won']
    ];
    if(!$nodiff) $pvp[ $line[$id.'1'] ][ $line[$id.'2'] ]['diff'] = $line[$sid.'1winrate']-$context_wrs[$line[$id.'1']][$wr_id];

    $pvp[ $line[$id.'2'] ][ $line[$id.'1'] ] = [
      "winrate" => 1-$line[$sid.'1winrate'],
      "matches" => $line['matches'],
      "won" => $line['matches']-$line[$sid.'1won'],
      "lost" => $line[$sid.'1won']
    ];
    if(!$nodiff) $pvp[ $line[$id.'2'] ][ $line[$id.'1'] ]['diff'] = 1-$line[$sid.'1winrate']-$context_wrs[$line[$id.'2']][$wr_id];
  }

  return $pvp;
}

?>
