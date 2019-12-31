<?php 

include_once("rg_report_out_settings.php");
include_once("modules/commons/versions.php");
$lg_version = [ 2, 4, 0, 2, 0 ];

include_once("modules/commons/locale_strings.php");
include_once("modules/commons/merge_mods.php");
include_once("modules/commons/metadata.php");

# FUNCTIONS
include_once("modules/view/functions/modules.php");

// FIXME: include_once("modules/webapi/functions/player_card.php");
// FIXME: include_once("modules/view/functions/team_card.php");
// FIXME: include_once("modules/view/functions/match_card.php");

include_once("modules/view/functions/join_selectors.php");
include_once("modules/view/functions/links.php");
include_once("modules/view/functions/join_matches.php");

include_once("modules/view/functions/team_name.php");
include_once("modules/view/functions/player_name.php");
include_once("modules/view/functions/hero_name.php");

include_once("modules/view/functions/has_pair.php");

# PRESETS
include_once("modules/view/__preset.php");

/* INITIALISATION */

$root = dirname(__FILE__);

$linkvars = [];

for($i=0,$sz=sizeof($linkvars); $i<$sz; $i++)
  $linkvars[$i] = implode($linkvars[$i], "=");
$linkvars = implode($linkvars, "&");

if (!empty($leaguetag)) {
  if(file_exists($reports_dir."/".$report_mask_search[0].$leaguetag.$report_mask_search[1])) {
    $report = file_get_contents($reports_dir."/".$report_mask_search[0].$leaguetag.$report_mask_search[1])
        or die("[F] Can't open $teaguetag, probably no such report\n");
    $report = json_decode($report, true);
  } else {
    $lightcache = true;
    include_once("modules/view/__open_cache.php");
    if(isset($cache['reps'][$leaguetag]['file'])) {
      $report = file_get_contents($reports_dir."/".$cache['reps'][$leaguetag]['file'])
          or die("[F] Can't open $leaguetag, probably no such report\n");
      $report = json_decode($report, true);
    } else $leaguetag = "";
  }
}

