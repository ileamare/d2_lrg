<?php

include_once($root."/modules/view/generators/pvp_unwrap_data.php");
include_once($root."/modules/view/generators/pvp_profile.php");

$res["region".$region]['players']["pvp"] = [];
if(!check_module($parent_mod."pvp")) return 0;

if($mod == $parent_mod."pvp") $unset_module = true;
$parent_module = $parent_mod."pvp-";

$winrates = [];
if (isset($report['players_additional'])) {
  foreach($reg_report['players_summary'] as $id => $player) {
    $winrates[$id]['winrate'] = $report['players_additional'][$id]['won']/$report['players_additional'][$id]['matches'];
  }
}

if(isset($reg_report['pvp'])) {
  $pvp = rg_generator_pvp_unwrap_data($reg_report['pvp'], $winrates, false);
} else {
  $tmp = $report['pvp'];

  foreach($report['pvp'] as $lid => $line) {
    if (!isset($reg_report['players_summary'][ $line['playerid1'] ]) ||
        !isset($reg_report['players_summary'][ $line['playerid2'] ]))
      unset($tmp[$lid]);
  }

  $pvp = rg_generator_pvp_unwrap_data($tmp, $winrates, false);
}

$names = $report['players'];
foreach($names as $pid => $name) {
  if(!isset($reg_report['players_summary'][$pid]))
    unset($names[$pid]);
}

uasort($names, function($a, $b) {
  if($a == $b) return 0;
  else return ($a > $b) ? 1 : -1;
});

if(!isset($reg_report['settings']['pvp_grid'])) $reg_report['settings']['pvp_grid'] = false;

if($reg_report['settings']['pvp_grid']) {
  $res["region".$region]['players']["pvp"]['grid'] = "";
  if(check_module($parent_module."grid")) {
    include_once($root."/modules/view/generators/pvp_grid.php");
    $res["region".$region]['players']["pvp"]['grid'] = rg_generator_pvp_grid("players-pvp-grid", $report['players'], $pvp);
    $res["region".$region]['players']["pvp"]['grid'] .= "<div class=\"content-text\">".locale_string("desc_players_pvp_grid")."</div>";
  }
  if(check_module($parent_module."profiles")) {
    if($mod == $parent_module."profiles") $unset_module = true;
  }
}
$out = [];


foreach($names as $id => $name) {
  $strings['en']["playerid".$id] = player_name($id);
  $out["playerid".$id] = "";

  if(check_module($parent_module.($reg_report['settings']['pvp_grid'] ? "profiles-" : "")."playerid".$id)) {
    $out["playerid".$id] = "<div class=\"content-text\">".locale_string("desc_players_pvp")."</div>";
    $out["playerid".$id] .= rg_generator_pvp_profile("player-pvp-$id", $pvp[$id], false);
  }
}

if($reg_report['settings']['pvp_grid']) {
  $res["region".$region]['players']["pvp"]['profiles'] = $out;
} else {
  $res["region".$region]['players']["pvp"] = $out;
}

?>
