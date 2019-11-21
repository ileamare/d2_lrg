<?php 

include_once("../rg_report_out_settings.php");
include_once("../modules/commons/versions.php");
$lg_version = array( 2, 2, 0, 1, 0 );

// TODO: libs

/* INITIALISATION */

$root = dirname(__FILE__);

$linkvars = [];

if ($lrg_use_get) {
  if(isset($_GET['league']) && !empty($_GET['league'])) {
    $leaguetag = $_GET['league'];
    if($lrg_get_depth > 0) {
      if(isset($_GET['mod'])) $mod = $_GET['mod'];
      else $mod = "";
    }
  } else $leaguetag = "";

  if(isset($_GET['cat']) && !empty($_GET['cat'])) {
    $cat = $_GET['cat'];
    //$linkvars[] = array("cat", $_GET['cat']);
  } //else $cat = "";
}

for($i=0,$sz=sizeof($linkvars); $i<$sz; $i++)
  $linkvars[$i] = implode($linkvars[$i], "=");
$linkvars = implode($linkvars, "&");


if (!empty($leaguetag)) {
  if(file_exists($reports_dir."/".$report_mask_search[0].$leaguetag.$report_mask_search[1])) {
    $report = file_get_contents($reports_dir."/".$report_mask_search[0].$leaguetag.$report_mask_search[1])
        or die("[F] Can't open $leaguetag, probably no such report\n");
    $report = json_decode($report, true);
  } else {
    $lightcache = true;
    include_once("../modules/view/__open_cache.php");
    if(isset($cache['reps'][$leaguetag]['file'])) {
      $report = file_get_contents($reports_dir."/".$cache['reps'][$leaguetag]['file'])
          or die("[F] Can't open $leaguetag, probably no such report\n");
      $report = json_decode($report, true);
    } else $leaguetag = "";
  }
}

if (isset($report)) {
  $output = "";

  $meta = new lrg_metadata;

  // overview: generator
  // records: plain
  // heroes: []
    // combos
    // draft
    // summary
    // haverages
    // hero_vs_hero
    // meta_graph
    // sides
  // players : []
    // combos
    // draft
    // haverages
    // party_graph
    // positions
    // pvp
    // summary
  // regions: []
  // teams: []
    // grid
    // profiles
      // overview
      // heroes draft
      // heroes positions
      // players draft
      // ...
  // matches: []
  // participants: []
  // raw
}