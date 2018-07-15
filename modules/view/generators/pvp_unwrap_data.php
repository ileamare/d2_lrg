<?php

function rg_generator_pvp_unwrap_data($context, $context_pickban, $heroes_flag = true) {
  $pvp = [];
  $id = $heroes_flag ? "heroid" : "playerid";
  $sid = $heroes_flag ? "h" : "p";

  foreach($context as $line) {
    if( !isset($pvp[ $line[$id.'1'] ]) )
      $pvp[ $line[$id.'1'] ] = [];
    if( !isset($pvp[ $line[$id.'2'] ]) )
      $pvp[ $line[$id.'2'] ] = [];

    $pvp[ $line[$id.'1'] ][ $line[$id.'2'] ] = [
      "winrate" => $line[$sid.'1winrate'],
      "diff" => $line[$sid.'1winrate']-$context_pickban[$line[$id.'1']]['winrate_picked'],
      "matches" => $line['matches'],
      "won" => $line[$sid.'1won'],
      "lost" => $line['matches']-$line[$sid.'1won']
    ];

    $pvp[ $line[$id.'2'] ][ $line[$id.'1'] ] = [
      "winrate" => 1-$line[$sid.'1winrate'],
      "diff" => 1-$line[$sid.'1winrate']-$context_pickban[$line[$id.'2']]['winrate_picked'],
      "matches" => $line['matches'],
      "won" => $line['matches']-$line[$sid.'1won'],
      "lost" => $line[$sid.'1won']
    ];
  }

  return $pvp;
}

?>
