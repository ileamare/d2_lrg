<!DOCTYPE html>
<html lang="<?php echo $locale; ?>">
  <head>
    <!--
      League Report Generator
      Spectral Alliance
      leamare/d2_lrg on github
     -->
    <?php
       if(file_exists("res/favicon.ico")) echo "<link rel=\"shortcut icon\" href=\"res/favicon.ico\" />";
    ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Description" content="Dota 2 Statistics hub at Spectral.GG, made by Leamare<?php
      if (isset($report['league_name'])) 
        echo ", ".$report['league_name']." report"; ?>">
    <title><?php
      echo $instance_title;
      if (!empty($leaguetag))
          echo " - ".$report['league_name'];
      ?></title>
    <link href="res/valve_mimic.css" rel="stylesheet" type="text/css" />
    <link href="res/reports.css" rel="stylesheet" type="text/css" />
    <?php
      if($use_graphjs) {
        echo "<script type=\"text/javascript\" src=\"res/dependencies/Chart.bundle.min.js\"></script>";
      }
      if($use_visjs) {
        echo "<script type=\"text/javascript\" src=\"res/dependencies/vis.min.js\"></script>";
        echo "<script type=\"text/javascript\" src=\"res/dependencies/vis-network.min.js\"></script>";
        echo "<link href=\"res/dependencies/vis.min.css\" rel=\"stylesheet\" type=\"text/css\" />";
        echo "<link href=\"res/dependencies/vis-network.min.css\" rel=\"stylesheet\" type=\"text/css\" />";
      }

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
     if (!empty($custom_head)) echo $custom_head; ?>
  </head>
  <body>
    <?php if (!empty($custom_body)) echo $custom_body; ?>
    <header class="navBar">
      <div class="navLinks">
        <div class="navItem dotalogo"><a href="<?php echo $main_path; ?>"></a></div>
        <div class="navItem bold"><a href=".<?php if(!empty($linkvars)) echo "?".$linkvars; ?>" title="Dota 2 League Reports"><?php echo locale_string("leag_reports")?></a></div>
        <?php
          foreach($title_links as $link) {
            echo "<div class=\"navItem\"><a href=\"".$link['link']."\" target=\"_blank\" rel=\"noopener\" title=\"".$link['title']."\">".$link['text']."</a></div>";
          }
         ?>
      </div>
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
          echo '<label><select onchange="select_modules_link(this);" class="select-locale">';
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
          echo '</select></label>';

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
        Match replay data provided by <a href="https://stratz.com/" target="_blank" rel="noopener">STRATZ</a> and <a href="https://opendota.com" target="_blank" rel="noopener">OpenDota</a>.<br />
        Graphs are made with <a href="https://visjs.org" target="_blank" rel="noopener">vis.js</a> and <a href="http://www.chartjs.org/" target="_blank" rel="noopener">chart.js</a>.<br />
        Made by Leamare @ <a href="https://spectral.gg" target="_blank" rel="noopener">Spectral.GG</a>
        with support of <a href="https://vk.com/thecybersport" target="_blank" rel="noopener">TheCyberSport</a>. Klozi is a registered trademark of Grafensky.<br />
        <?php if (!empty($custom_footer)) echo $custom_footer."<br />";
          echo "LRG web version: <a>".parse_ver($lg_version)."</a>. ";
        ?>
      </footer>
      <div class="modal" id="modal-box">
        <div class="modal-content">
          <div class="modal-header"></div>
          <div id="modal-text" class="modal-text"></div>
          <div id="modal-sublevel" class="modal-sublevel"></div>
        </div>
      </div>
      <script type="text/javascript" src="res/dependencies/jquery.min.js"></script>
      <script type="text/javascript" src="res/dependencies/jquery.tablesorter.min.js"></script>
      <script type="text/javascript" src="res/reports.js"></script>
    </body>
  </html>
