<?php

function rg_generator_tvt_unwrap_data($context, $context_teams) {
  $tvt = [];

  foreach($context_teams as $tid => $team) {
    $tvt[$tid] = [];
  }

  $team_ids = array_keys($context_teams);

  foreach($tvt as $team_id => $team) {
    foreach($team_ids as $tid) {
      $tvt[$team_id][$tid] = array(
        "winrate" => 0,
        "matches" => 0,
        "won" => 0,
        "lost" => 0
      );
    }
  }

  foreach($team_ids as $tid) {
    for($i=0, $end = sizeof($context); $i<$end; $i++) {
      if($context[$i]['teamid1'] == $tid) {
        $tvt[$tid][$context[$i]['teamid2']] = array(
          "winrate" => ($context[$i]['t1won']/$context[$i]['matches'] ),
          "matches" => $context[$i]['matches'],
          "won" => $context[$i]['t1won'],
          "lost" => $context[$i]['matches'] - $context[$i]['t1won'],
        );
      }
      if($context[$i]['teamid2'] == $tid) {
        $tvt[$tid][$context[$i]['teamid1']] = array(
          "winrate" => ($context[$i]['matches']-$context[$i]['t1won'])/$context[$i]['matches'],
          "matches" => $context[$i]['matches'],
          "won" => $context[$i]['matches'] - $context[$i]['t1won'],
          "lost" => $context[$i]['t1won']
        );
      }
    }
  }

  return $tvt;
}

?>
