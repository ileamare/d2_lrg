<?php
include_once("rg_report_out_settings.php");
include_once("modules/functions/versions.php");
$lg_version = array( 1, 4, 0, -4, 14 );

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
      if(file_exists("reports/report_".$_GET['league'].".json")) {
        $leaguetag = $_GET['league'];
        if($lrg_get_depth > 0) {
          if(isset($_GET['mod'])) $mod = $_GET['mod'];
          else $mod = "";
        }
      } else $leaguetag = "";
    } else $leaguetag = "";

    if(isset($_GET['loc']) && !empty($_GET['loc'])) {
      $locale = $_GET['loc'];
      $linkvars[] = array("loc", $_GET['loc']);
    }
    if(isset($_GET['stow']) && !empty($_GET['stow'])) {
      $override_style = $_GET['stow'];
      $linkvars[] = array("stow", $_GET['stow']);
    }
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
    $output = "";

    $report = file_get_contents("reports/report_".$leaguetag.".json") or die("[F] Can't open $teaguetag, probably no such report\n");
    $report = json_decode($report, true);

    $meta = file_get_contents("res/metadata.json") or die("[F] Can't open metadata\n");
    $meta = json_decode($meta, true);

    include_once("modules/view/__post_load.php");

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

    if (isset($report['matches'])) $modules['matches'] = "";

    if (isset($report['players']))
      include_once("modules/view/participants.php");

    if (isset($report['regions_data']))
      include("modules/view/regions.php");

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
            else if(isset($report['settings']['custom_style']) && file_exists("res/custom_styles/".$report['settings']['custom_style'].".css"))
                echo "<link href=\"res/custom_styles/".$report['settings']['custom_style'].".css\" rel=\"stylesheet\" type=\"text/css\" />";
            else {

              if(empty($leaguetag) && !empty($noleague_style))
                echo "<link href=\"res/custom_styles/".$noleague_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
              else if(!empty($default_style))
                echo "<link href=\"res/custom_styles/".$default_style.".css\" rel=\"stylesheet\" type=\"text/css\" />";
            }
            if(isset($report['settings']['custom_logo']) && file_exists("res/custom_styles/logos/".$report['settings']['custom_logo'].".css"))
                echo "<link href=\"res/custom_styles/logos/".$report['settings']['custom_logo'].".css\" rel=\"stylesheet\" type=\"text/css\" />";
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
            echo '<div class="share-link reddit"><a href="https://www.reddit.com/submit?url='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on Reddit</a></div>';
            echo '<div class="share-link twitter"><a href="https://twitter.com/share?text=League+Report:+'.$leaguetag.'+'.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on Twitter</a></div>';
            echo '<div class="share-link vk"><a href="https://vk.com/share.php?url='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on VK</a></div>';
            echo '<div class="share-link fb"><a href="https://www.facebook.com/sharer/sharer.php?u='.htmlspecialchars('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].
              (empty($_SERVER['QUERY_STRING']) ? "" : '?'.$_SERVER['QUERY_STRING'])
            ).'" target="_blank" rel="noopener">Share on Facebook</a></div>';
          ?>
        </div>
      </header>
      <div id="content-wrapper">
      <?php if (!empty($leaguetag)) { ?>
        <div id="header-image" class="section-header">
        <?php if(empty($report['settings']['custom_logo'])) {?>
          <h1><?php echo $report['league_name']; ?></h1>
          <h2><?php echo $report['league_desc']; ?></h2>
          <h3><?php echo locale_string($h3).": ".$report['random'][$h3]; ?></h3>
        <?php } else { ?>
          <div id="image-logo"></div>
        <?php } ?>
        </div>
        <div id="main-section" class="content-section">
<?php

if (!empty($custom_content)) echo "<br />".$custom_content;

$output = join_selectors($modules, 0);

echo $output;

?>
          </div>
      <?php } else { ?>
        <div id="header-image" class="section-header">
          <h1><?php echo  $instance_name; ?></h1>
        </div>
        <div id="main-section" class="content-section">
          <?php
          $dir = scandir("reports");

          if (sizeof($dir) < 3) {
            echo "<div id=\"content-top\">".
              "<div class=\"content-header\">".locale_string("empty_instance_cap")."</div>".
              "<div class=\"content-text\">".locale_string("empty_instance_desc").".</div>".
            "</div>";
          } else {
            echo "<div id=\"content-top\">".
              "<div class=\"content-header\">".locale_string("noleague_cap")."</div>".
              "<div class=\"content-text\">".locale_string("noleague_desc").":</div>".
            "</div>";

            echo "<table id=\"leagues-list\" class=\"list wide\"><tr class=\"thead\">
              <th onclick=\"sortTable(0,'leagues-list');\">".locale_string("league_name")."</th>
              <th onclick=\"sortTableNum(1,'leagues-list');\">".locale_string("league_id")."</th>
              <th>".locale_string("league_desc")."</th>
              <th onclick=\"sortTableNum(3,'leagues-list');\">".locale_string("matches_total")."</th>
              <th onclick=\"sortTableValue(4,'leagues-list');\">".locale_string("start_date")."</th>
              <th onclick=\"sortTableValue(5,'leagues-list');\">".locale_string("end_date")."</th></tr>";

            $reports = array();

            foreach($dir as $report) {
                if($report[0] == ".")
                    continue;
                $name = str_replace("report_", "", $report);
                $name = str_replace(".json", "", $name);

                $f = fopen("reports/report_".$name.".json","r");
                $file = fread($f, 400);

                $head = json_decode("[\"".preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":(.*)/", "$1", $file)."\"]");
                $desc = json_decode("[\"".preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":\"(.+)\",\"league_id\":(.+),\"league_tag\":(.*)/", "$2", $file)."\"]");

                $reports[] = array(
                  "name" => $name,
                  "head" => array_pop($head),
                  "desc" => array_pop($desc),
                  "id" => preg_replace("/{\"league_name\":\"(.+)\"\,\"league_desc\":\"(.+)\",\"league_id\":(.+),\"league_tag\":(.*)/", "$3", $file),
                  "std" => (int)preg_replace("/(.*)\"first_match\":\{(.*)\"date\":\"(\d+)\"\},\"last_match\"(.*)/i", "$3 ", $file),
                  "end" => (int)preg_replace("/(.*)\"last_match\":\{(.*)\"date\":\"(\d+)\"\},\"random\"(.*)/i", "$3 ", $file),
                  "total" => (int)preg_replace("/(.*)\"random\":\{(.*)\"matches_total\":\"(\d+)\",\"(.*)/i", "$3 ", $file)
                );
            }

            uasort($reports, function($a, $b) {
              if($a['end'] == $b['end']) {
                if($a['std'] == $b['std']) return 0;
                else return ($a['std'] < $b['std']) ? 1 : -1;
              } else return ($a['end'] < $b['end']) ? 1 : -1;
            });

            foreach($reports as $report) {
              echo "<tr><td><a href=\"?league=".$report['name'].(empty($linkvars) ? "" : "&".$linkvars)."\">".$report['head']."</a></td>".
                "<td>".($report['id'] == "null" ? "-" : $report['id'])."</td>".
                "<td>".$report['desc']."</td>".
                "<td>".$report['total']."</td>".
                "<td value=\"".$report['std']."\">".date(locale_string("date_format"), $report['std'])."</td>".
                "<td value=\"".$report['end']."\">".date(locale_string("date_format"), $report['end'])."</td></tr>";
            }

            echo "</table>";
          }
          ?>
        </div>
      <?php } ?>
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
