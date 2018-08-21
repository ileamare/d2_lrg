<?php
if(isset($_GET['cat']) && !empty($_GET['cat'])) {
  $cat = $_GET['cat'];
  $linkvars[] = array("cat", $_GET['cat']);
} else $cat = "";

include_once("modules/view/__open_cache.php");
include_once("modules/view/__update_cache.php");

/*
if 0 reports - empty handler
if not 0, but empty cache - create cache file
if cache file exists or created new - check every report, if it's in the file
  check filesizem if match
    check change date
  if not
    open report, get head data, generate for cache, save
if cache changed = save

generate table based on cache table

filesize, report opener - create interface functions for that
cache opener interface
existing modules in cache info
check existing modules when reading report, get requested parts through interface

*/
/*

function: checktag($reportdata, $tag),

  tags:
    tag
    name {localized}
    desc {localized}
    filter

  filters:
    name - mask (preg)
    desc - mask (preg)
    modules - check modules
    dates - check dates range (unix timestamps)
    matchids - check matchids range
    regions - check included regions
    report type (team/player/centric)
    folder
    custom_style
    hidden
*/

$modules = "";

if (sizeof($dir) == 0) {
  $modules .= "<div id=\"content-top\">".
    "<div class=\"content-header\">".locale_string("empty_instance_cap")."</div>".
    "<div class=\"content-text\">".locale_string("empty_instance_desc").".</div>".
  "</div>";
} else {
  $modules .= "<div id=\"content-top\">".
    "<div class=\"content-header\">".locale_string("noleague_cap")."</div>".
    "<div class=\"content-text\">".locale_string("noleague_desc").":</div>".
  "</div>";

  $modules .= "<table id=\"leagues-list\" class=\"list wide\"><tr class=\"thead\">
    <th onclick=\"sortTable(0,'leagues-list');\">".locale_string("league_name")."</th>
    <th onclick=\"sortTableNum(1,'leagues-list');\">".locale_string("league_id")."</th>
    <th>".locale_string("league_desc")."</th>
    <th onclick=\"sortTableNum(3,'leagues-list');\">".locale_string("matches_total")."</th>
    <th onclick=\"sortTableValue(4,'leagues-list');\">".locale_string("start_date")."</th>
    <th onclick=\"sortTableValue(5,'leagues-list');\">".locale_string("end_date")."</th></tr>";

  uasort($cache["reps"], function($a, $b) {
    if($a['last_match']['date'] == $b['last_match']['date']) {
      if($a['first_match']['date'] == $b['first_match']['date']) return 0;
      else return ($a['first_match']['date'] < $b['first_match']['date']) ? -1 : 1;
    } else return ($a['last_match']['date'] < $b['last_match']['date']) ? 1 : -1;
  });

  foreach($cache["reps"] as $report) {
    $modules .= "<tr><td><a href=\"?league=".$report['tag'].(empty($linkvars) ? "" : "&".$linkvars)."\">".$report['name']."</a></td>".
      "<td>".($report['id'] == "null" ? "-" : $report['id'])."</td>".
      "<td>".$report['desc']."</td>".
      "<td>".$report['matches']."</td>".
      "<td value=\"".$report['first_match']['date']."\">".date(locale_string("date_format"), $report['first_match']['date'])."</td>".
      "<td value=\"".$report['last_match']['date']."\">".date(locale_string("date_format"), $report['last_match']['date'])."</td></tr>";
  }

  $modules .= "</table>";
}
?>
