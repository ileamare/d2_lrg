<?php
include_once("rg_report_out_settings.php");
include_once("modules/functions/versions.php");
$lg_version = array( 2, 1, 0, 0, 0 );

include_once("modules/functions/locale_strings.php");
include_once("modules/functions/get_language_code_iso6391.php");
include_once("modules/functions/merge_mods.php");

# FUNCTIONS
include_once("modules/view/functions/modules.php");

include_once("modules/view/functions/player_card.php");
include_once("modules/view/functions/team_card.php");
include_once("modules/view/functions/match_card.php");

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

if ($lrg_use_get) {
  $locale = GetLanguageCodeISO6391();

  if(isset($_GET['league']) && !empty($_GET['league'])) {
    $leaguetag = $_GET['league'];
    if($lrg_get_depth > 0) {
      if(isset($_GET['mod'])) $mod = $_GET['mod'];
      else $mod = "";
    }
  } else $leaguetag = "";

  if(isset($_GET['loc']) && !empty($_GET['loc'])) {
    $locale = $_GET['loc'];
    $linkvars[] = array("loc", $_GET['loc']);
  }
  if(isset($_GET['stow']) && !empty($_GET['stow'])) {
    $override_style = $_GET['stow'];
    $linkvars[] = array("stow", $_GET['stow']);
  }
  if(isset($_GET['cat']) && !empty($_GET['cat'])) {
    $cat = $_GET['cat'];
    //$linkvars[] = array("cat", $_GET['cat']);
  } //else $cat = "";
}

require_once('locales/en.php');
if(strtolower($locale) != "en" && file_exists('locales/'.$locale.'.php'))
  require_once('locales/'.$locale.'.php');
else $locale = "en";

$use_visjs = false;
$use_graphjs = false;

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
          or die("[F] Can't open $teaguetag, probably no such report\n");
      $report = json_decode($report, true);
    }
  }
}

if (isset($report)) {
  $output = "";

  $meta = file_get_contents("res/metadata.json") or die("[F] Can't open metadata\n");
  $meta = json_decode($meta, true);

  include_once("modules/view/__post_load.php");

  if(isset($report['settings']['custom_style']) && file_exists("res/custom_styles/".$report['settings']['custom_style'].".css"))
    $custom_style = $report['settings']['custom_style'];
  if(isset($report['settings']['custom_logo']) && file_exists("res/custom_styles/logos/".$report['settings']['custom_logo'].".css"))
    $custom_logo = $report['settings']['custom_logo'];

  $modules = [];
  # module => array or ""
  include_once("modules/view/overview.php");
  if (isset($report['records']))
    include_once("modules/view/records.php");

  if (isset($report['averages_heroes']) || isset($report['pickban']) || isset($report['draft']) || isset($report['hero_positions']) ||
      isset($report['hero_sides']) || isset($report['hero_pairs']) || isset($report['hero_triplets']))
        include_once("modules/view/heroes.php");

  if (isset($report['players']))
    include_once("modules/view/players.php");

  if (isset($report['teams'])) {
    include_once("modules/view/teams.php");
  }

  if (isset($report['regions_data']))
    include_once("modules/view/regions.php");

  if (isset($report['matches']))
    include_once("modules/view/matches.php");

  if (isset($report['players']))
    include_once("modules/view/participants.php");


  if(empty($mod)) $unset_module = true;
  else $unset_module = false;

  $h3 = array_rand($report['random']);


  # overview
  if (check_module("overview")) {
    merge_mods($modules['overview'], rg_view_generate_overview());
  }

  # records
  if (isset($modules['records']) && check_module("records")) {
    merge_mods($modules['records'], rg_view_generate_records());
  }

  # heroes
  if (isset($modules['heroes']) && check_module("heroes")) {
    merge_mods($modules['heroes'], rg_view_generate_heroes());
  }

  # players
  if (isset($modules['players']) && check_module("players")) {
    merge_mods($modules['players'], rg_view_generate_players());
  }

  # teams
  if (isset($modules['teams']) && check_module("teams")) {
    merge_mods($modules['teams'], rg_view_generate_teams());
  }

  if (isset($modules['regions']) && check_module("regions")) {
    merge_mods($modules['regions'], rg_view_generate_regions());
  }

  # matches
  if (isset($modules['matches']) && check_module("matches")) {
    merge_mods($modules['matches'], rg_view_generate_matches());
  }

  # participants
  if(isset($modules['participants']) && check_module("participants")) {
    merge_mods($modules['participants'], rg_view_generate_participants());
  }
} else {
  include_once("modules/view/index.php");
}

include_once("modules/view/__template.php");
?>
