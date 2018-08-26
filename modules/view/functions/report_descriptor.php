<?php

function get_report_descriptor($report) {
  //echo  $report['league_tag']."<br />";

  $desc = [
    "tag" => $report['league_tag'],
    "name" => $report['league_name'],
    "desc" => $report['league_desc'],
    "id" => $report['league_id'],
    "first_match" => $report['first_match'],
    "last_match" => $report['last_match'],
    "matches" => $report['random']['matches_total'],
    "ver" => $report['ana_version']
  ];

  if(isset($report['teams'])) {
    $desc["tvt"] = true;
    $desc["teams"] = [];
    foreach($report['teams'] as $tid => $team)
      $desc["teams"][] = $tid;
  } else {
    $desc["tvt"] = false;
    if(isset($report['players'])) {
      $desc["players"] = [];
      foreach($report['players'] as $pid => $player)
        $desc["players"][] = $pid;
    }
  }
  if(isset($report['regions_data'])) {
    $desc["regions"] = [];
    foreach($report['regions_data'] as $rid => $regions)
      $desc["regions"][] = $rid;
  }

  if(isset($report['settings']['custom_style'])) {
    $desc["style"] = $report['settings']['custom_style'];
  }
  if(isset($report['settings']['custom_logo'])) {
    $desc["logo"] = $report['settings']['custom_logo'];
  }

  return $desc;
}

?>
