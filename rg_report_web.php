<?php
include_once("rg_report_out_settings.php");
include_once("modules/functions/versions.php");
$lg_version = array( 2, 0, 0, 0, 0 );

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

  if (isset($report['players']))
    include_once("modules/view/participants.php");

  if (isset($report['matches'])) $modules['matches'] = "";


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
    krsort($report['matches']);
    $modules['matches'] = "<div class=\"content-text\">".locale_string("desc_matches")."</div>";
    $modules['matches'] .= "<div class=\"content-cards\">";
    foreach($report['matches'] as $matchid => $match) {
      $modules['matches'] .= match_card($matchid);
    }
    $modules['matches'] .= "</div>";
  }

  # participants
  if(isset($modules['participants']) && check_module("participants")) {
    merge_mods($modules['participants'], rg_view_generate_participants());
  }
} else {
  include_once("modules/view/index.php");
}
?>
<!DOCTYPE html>
<html>
  <head>
    <!--
      League Report Generator
      Spectral Alliance
      leamare/d2_lrg on github
     -->
    <?php
       if(file_exists("favicon.ico")) echo "<link rel=\"shortcut icon\" href=\"favicon.ico\" />";
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php
      echo $instance_title;
      if (!empty($leaguetag))
          echo " - ".$report['league_name'];
      ?></title>
    <link href="res/valve_mimic.css" rel="stylesheet" type="text/css" />
    <link href="res/reports.css" rel="stylesheet" type="text/css" />
    <?php
          if(isset($override_style) && file_exists("res/custom_styles/".$override_style.".css"))
              echo "<link href=\"res/custom_styles/".$override_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
          else if(isset($custom_style))
              echo "<link href=\"res/custom_styles/".$custom_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
          else {
            if(empty($leaguetag) && !empty($noleague_style))
              echo "<link href=\"res/custom_styles/".$noleague_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
            else if(!empty($default_style))
              echo "<link href=\"res/custom_styles/".$default_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
          }
          if(isset($custom_logo))
              echo "<link href=\"res/custom_styles/logos/".$custom_logo.".css\" rel=\"stylesheet\" type=\"text/css\" />";
          if($use_graphjs) {
            echo "<script type=\"text/javascript\" src=\"res/dependencies/Chart.bundle.min.js\"></script>";
          }
          if($use_visjs) {
            echo "<script type=\"text/javascript\" src=\"res/dependencies/vis.min.js\"></script>";
            echo "<script type=\"text/javascript\" src=\"res/dependencies/vis-network.min.js\"></script>";
            echo "<link href=\"res/dependencies/vis.min.css\" rel=\"stylesheet\" type=\"text/css\" />";
            echo "<link href=\"res/dependencies/vis-network.min.css\" rel=\"stylesheet\" type=\"text/css\" />";
          }

     if (!empty($custom_head)) echo $custom_head; ?>
  </head>
  <body>
    <?php if (!empty($custom_body)) echo $custom_body; ?>
    <header class="navBar">
      <!-- these shouldn't be spans, but I was mimicking Valve pro circuit style in everything, so I copied that too. -->
      <span class="navItem dotalogo"><a href="<?php echo $main_path; ?>"></a></span>
      <span class="navItem"><a href=".<?php if(!empty($linkvars)) echo "?".$linkvars; ?>" title="Dota 2 League Reports"><?php echo locale_string("leag_reports")?></a></span>
      <?php
        foreach($title_links as $link) {
          echo "<span class=\"navItem\"><a href=\"".$link['link']."\" target=\"_blank\" rel=\"noopener\" title=\"".$link['title']."\">".$link['text']."</a></span>";
        }
       ?>
      <div class="share-links">
        <?php
          echo '<div class="share-link reddit"><a href="https://www.reddit.com/submit?url='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).
            '" target="_blank" rel="noopener">Share on Reddit</a></div>';
          echo '<div class="share-link twitter"><a href="https://twitter.com/share?text=League+Report:+'.$leaguetag.'+'.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).
            '" target="_blank" rel="noopener">Share on Twitter</a></div>';
          echo '<div class="share-link vk"><a href="https://vk.com/share.php?url='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).
            '" target="_blank" rel="noopener">Share on VK</a></div>';
          echo '<div class="share-link fb"><a href="https://www.facebook.com/sharer/sharer.php?u='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).
            '" target="_blank" rel="noopener">Share on Facebook</a></div>';
        ?>
      </div>
      <div class="locale-changer">
        <?php
        $link = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], "/")+1);
          echo '<select onchange="select_modules_link(this);" class="select-locale">';
          foreach($locales as $loc => $lname) {
            $loc = str_replace(".json", "", $loc);
            if($loc == $locale)
             echo '<option selected>'.$lname.'</option>';
            else
             echo '<option value="'.(
                 empty($link) ? "?loc=$loc" : (
                     preg_match("/([\&\?])loc=(.+?)/", $link) ?
                     str_replace("loc=$locale", "loc=$loc", $link) :
                     $link."&loc=$loc"
                   )
               ).'">'.$lname.'</option>';
          }
          echo '</select>';

        ?>
      </div>
    </header>
    <div id="content-wrapper">
      <div id="header-image" class="section-header">
    <?php if (!empty($leaguetag)) { ?>
      <?php if(!isset($custom_logo)) {?>
        <h1><?php echo $report['league_name']; ?></h1>
        <h2><?php echo $report['league_desc']; ?></h2>
        <h3><?php echo locale_string($h3).": ".$report['random'][$h3]; ?></h3>
      <?php } else { ?>
        <div id="image-logo"></div>
      <?php } ?>
    <?php } else { ?>
        <?php if(!isset($custom_logo)) {?>
          <h1><?php echo $head_name; ?></h1>
          <h2><?php if(isset($head_desc)) echo $head_desc; ?></h2>
        <?php } else { ?>
          <div id="image-logo"></div>
        <?php } ?>
    <?php } ?>
    </div>
      <div id="main-section" class="content-section">
        <?php
          if (!empty($custom_content)) echo "<br />".$custom_content;

          $output = join_selectors($modules, 0);

          echo $output;
        ?>
      </div>
    </div>
      <footer>
        <a href="https://dota2.com" target="_blank" rel="noopener">Dota 2</a> is a registered trademark of <a href="https://valvesoftware.com" target="_blank" rel="noopener">Valve Corporation.</a>
        Match replay data analyzed by <a href="https://opendota.com" target="_blank" rel="noopener">OpenDota</a>.<br />
        Graphs are made with <a href="https://visjs.org" target="_blank" rel="noopener">vis.js</a> and <a href="http://www.chartjs.org/" target="_blank" rel="noopener">chart.js</a>.<br />
        Made by <a href="https://spectralalliance.ru" target="_blank" rel="noopener">Spectral Alliance</a>
        with support of <a href="https://vk.com/thecybersport" target="_blank" rel="noopener">TheCyberSport</a>. Klozi is a registered trademark of Grafensky.<br />
        <?php if (!empty($custom_footer)) echo $custom_footer."<br />";
          echo "LRG web version: <a>".parse_ver($lg_version)."</a>. ";
        ?>
        All changes can be discussed on Spectral Alliance discord channel and on <a href="https://github.com/leamare/d2_lrg" target="_blank" rel="noopener">github</a>.
      </footer>
      <div class="modal" id="modal-box">
        <div class="modal-content">
          <div class="modal-header"></div>
          <div id="modal-text" class="modal-text"></div>
          <div id="modal-sublevel" class="modal-sublevel"></div>
        </div>
      </div>
      <script type="text/javascript" src="res/reports.js"></script>
    </body>
  </html>
