<?php
if (file_exists($cats_file)) {
  $cats = file_get_contents($cats_file);
  $cats = json_decode($cats, true);
}

include_once("modules/view/__open_cache.php");
include_once("modules/view/__update_cache.php");

/*
function: checktag($reportdata, $tag),
  tags:
    tag
    name {localized}
    desc {localized}
    filters (|| or &&)
    custom_style
    hidden
*/

if(!empty($cats)) {
  include_once("modules/view/functions/check_filters.php");

  if (isset($cat) && isset($cats[$cat])) {
    $reps = [];
    foreach($cache["reps"] as $tag => $rep) {
      if(check_filters($rep, $cats[$cat]['filters']))
        $reps[$tag] = $rep;
    }
    if(isset($cats[$cat]['custom_style']) && file_exists("res/custom_styles/".$cats[$cat]['custom_style'].".css"))
      $custom_style = $cats[$cat]['custom_style'];
    if(isset($cats[$cat]['custom_logo']) && file_exists("res/custom_styles/logos/".$cats[$cat]['custom_logo'].".css"))
      $custom_logo = $cats[$cat]['custom_logo'];

    if(isset($cats[$cat]['names_locales'][$locale])) $head_name = $cats[$cat]['names_locales'][$locale];
    else $head_name = $cats[$cat]['name'];

    if(isset($cats[$cat]['desc_locales'][$locale])) $head_desc = $cats[$cat]['desc_locales'][$locale];
    else if(isset($cats[$cat]['desc'])) $head_desc = $cats[$cat]['desc'];
  } else if(!isset($cat) || $cat == "main") {
    $head_name = $instance_name;
    if(isset($cats[$hidden_cat])) {
      foreach($cache["reps"] as $tag => $rep) {
        if(!check_filters($rep, $cats[$hidden_cat]['filters']))
          $reps[$tag] = $rep;
      }
    } else {
      $reps = $cache["reps"];
    }
  } else if ($cat == "all") {
    $head_name = $instance_name;
    $reps = $cache["reps"];
  } else {
    $head_name = $cat;
    $reps = [];
  }
} else {
  $head_name = $instance_name;
  $reps = $cache["reps"];
}

$modules = "";
$modules .= "<div id=\"content-top\">";

if(isset($cats) && !empty($cats)) {
  if(isset($cat)) {
    $modules .= "<div class=\"content-text tagsshow\"><a class=\"category\">".locale_string("show_tags")."</a></div>";
  }

  $modules .= "<div class=\"content-text tagslist ".(isset($cat) ? "hidden" : "")."\" ".(isset($cat) ? " style=\"display:none;\"" : "").">";

  $modules .= "<a class=\"category".(isset($cat) && "main" == $cat ? " active" : "").
              "\" href=\"?cat=main".(empty($linkvars) ? "" : "&".$linkvars).
              "\">".locale_string("main_reports")."</a>";

  foreach($cats as $tag => $desc) {
    if($tag == $hidden_cat || (isset($desc['hidden']) && $desc['hidden'])) continue;

    $modules .= "<a class=\"category".(isset($cat) && $tag == $cat ? " active" : "")."\" ".
                "href=\"?cat=".$tag.(empty($linkvars) ? "" : "&".$linkvars)."\" ".
                (isset($desc['desc_locales'][$locale]) ? "title=\"".$desc['desc_locales'][$locale]."\"" :
                  (isset($desc['desc']) ? "title=\"".$desc['desc']."\"" : "")
                ).
                ">".
                (isset($desc['names_locales'][$locale]) ? $desc['names_locales'][$locale] :
                  (isset($desc['name']) ? $desc['name'] : $tag)
                ).
                "</a>";
  }

  if(isset($cats[$hidden_cat]))
    $modules .= "<a class=\"category".(isset($cat) && "all" == $cat ? " active" : "").
                "\" href=\"?cat=all".(empty($linkvars) ? "" : "&".$linkvars).
                "\">".locale_string("all_reports")."</a>";

  $modules .= "</div>";
}

if (sizeof($cache['reps']) === 0) {
  $modules .= "<div class=\"content-header\">".locale_string("empty_instance_cap")."</div>".
    "<div class=\"content-text\">".locale_string("empty_instance_desc").".</div>".
  "</div>";
} else if (sizeof($reps) === 0) {
  $modules .= "<div class=\"content-header\">".locale_string("noreports")."</div>".
    "<div class=\"content-text\">".locale_string("noreports_desc").".</div>".
  "</div>";
} else {
  if (!empty($ads_block_main)) $modules .= "<div class=\"ads-block-main\">$ads_block_main</div>";

  if(!isset($cat) && $index_list < sizeof($reps))
    $modules .= "<div class=\"content-header\">".locale_string("noleague_cap")."</div>";
  $modules .= "</div>";

  $modules .= "<table id=\"leagues-list\" class=\"list wide ".(isset($cat) ? "sortable" : "")."\"><thead><tr>";
  $modules .= "<th>".locale_string("league_name")."</th>".
    "<th>".locale_string("league_id")."</th>".
    "<th>".locale_string("league_desc")."</th>".
    "<th>".locale_string("matches_total")."</th>".
    "<th>".locale_string("days")."</th>".
    "<th>".locale_string("participants")."</th>".
    "<th>".locale_string("type")."</th>".
    "<th>".locale_string("regions")."</th>".
    "<th>".locale_string("start_date")."</th>".
    "<th>".locale_string("end_date")."</th>".
    "</tr></thead>";

  uasort($reps, function($a, $b) {
    if($a['last_match']['date'] == $b['last_match']['date']) {
      if($a['first_match']['date'] == $b['first_match']['date']) return 0;
      else return ($a['first_match']['date'] < $b['first_match']['date']) ? -1 : 1;
    } else return ($a['last_match']['date'] < $b['last_match']['date']) ? 1 : -1;
  });

  foreach($reps as $report) {
    if ($report['short_fname'][0] == '!') continue;
    if(!isset($cat) && $index_list < sizeof($reps)) {
      if(!$index_list) break;
      $index_list--;
    }

    $event_type = $report['tvt'] ? 'tvt' : (
      isset($report['players']) ? 'pvp' : 'ranked'
    );

    $participants = isset($report['teams']) ? sizeof($report['teams']) : (
      isset($report['players']) ? sizeof($report['players']) : '-'
    );

    $modules .= "<tr><td><a href=\"?league=".$report['tag'].(empty($linkvars) ? "" : "&".$linkvars)."\">".$report['name']."</a></td>".
      "<td>".($report['id'] == "" ? "-" : $report['id'])."</td>".
      "<td>".$report['desc']."</td>".
      "<td>".$report['matches']."</td>".
      "<td>".$report['days']."</td>".
      "<td>".$participants."</td>".
      "<td>".locale_string($event_type)."</td>".
      "<td>".(isset($report['regions']) ? sizeof($report['regions']) : ' - ')."</td>".
      "<td value=\"".$report['first_match']['date']."\" data-matchid=\"".$report['first_match']['mid']."\">".date(locale_string("date_format"), $report['first_match']['date'])."</td>".
      "<td value=\"".$report['last_match']['date']."\" data-matchid=\"".$report['last_match']['mid']."\">".date(locale_string("date_format"), $report['last_match']['date'])."</td></tr>";
  }
  if(!$index_list ) {
    $modules .= "<tr><td></td><td></td><td>...</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
  }

  $modules .= "</table>";
}
?>
