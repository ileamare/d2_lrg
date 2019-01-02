<?php
include_once($root."/modules/view/functions/hero_name.php");
include_once($root."/modules/view/functions/player_name.php");
include_once($root."/modules/view/functions/ranking.php");

function rg_generator_overview_chart($name, $labels, $context) {
  if(!sizeof($context)) return "";
  global $charts_colors;

  $colors = array_slice($charts_colors, 0, sizeof($labels));
  $res = "<div class=\"chart-pie\"><canvas id=\"$name\" width=\"undefined\" height=\"undefined\"></canvas><script>".
                        "var modes_chart_el = document.getElementById('$name'); ".
                        "var modes_chart = new Chart(modes_chart_el, {
                          type: 'pie',
                          data: {
                            labels: [ '".implode($labels,"','")."' ],
                            datasets: [{data: [ ".implode($context,",")." ],
                            borderWidth: 0,
                            backgroundColor:['".implode($colors,"','")."']}]
                          }
                        });</script></div>";

  return $res;
}

function rg_generator_overview_combos($table_id, $caption, $context, $limiter = 10, $heroes_flag = true) {
  $i = 0;
  $id = $heroes_flag ? "heroid" : "playerid";

  if(!isset(array_values($context)[0]))
    return "";

  # Figuring out what kind of context we have here

  $combo = array_values($context)[0];

  if(isset($combo['lane_rate']))
    $lane_rate = true;
  else
    $lane_rate = false;

  if(isset($combo['lane']))
    $lane = true;
  else
    $lane = false;

  if(isset($combo['expectation']))
    $expectation = true;
  else
    $expectation = false;

  if(isset($combo[$id.'3']))
    $trios = true;
  else
    $trios = false;

  unset($combo);

  $res = "<table id=\"$table_id\" class=\"list\"><caption>$caption</caption><thead><tr>".
         (($heroes_flag && !$i++) ? "<th width=\"1%\"></th>" : "").
         "<th>".locale_string($heroes_flag ? "hero" : "player")." 1</th>".
         (($heroes_flag && $i++) ? "<th width=\"1%\"></th>" : "").
         "<th>".locale_string($heroes_flag ? "hero" : "player")." 2</th>".
         (
           $trios ?
           (($heroes_flag && $i++) ? "<th width=\"1%\"></th>" : "").
           "<th>".locale_string($heroes_flag ? "hero" : "player")." 3</th>" :
           ""
           ).
         "<th>".locale_string("matches")."</th>".
         "<th>".locale_string("winrate")."</th>".
         ($expectation ? "<th>".locale_string("pair_expectation")."</th>".
                         "<th>".locale_string("pair_deviation")."</th>".
                         "<th>".locale_string("percentage")."</th>" : "").
         ($lane_rate ? "<th>".locale_string("lane_rate")."</th>" : "").
         ($lane ? "<th>".locale_string("lane")."</th>" : "").
         "</tr></thead>";

  $i = $limiter;

  uasort($context, function($a, $b) {
    $dev_a = $a['matches']-$a['expectation'];
    $dev_b = $b['matches']-$b['expectation'];
    if($dev_a == $dev_b) return 0;
    return ($dev_a < $dev_b) ? 1 : -1;
  });

  foreach($context as $combo) {
    $i--;
    $res .= "<tr>".
                ($heroes_flag ? "<td>".hero_portrait($combo[$id.'1'])."</td>" : "").
                "<td>".($heroes_flag ? hero_name($combo[$id.'1']) : player_name($combo[$id.'1']))."</td>".
                ($heroes_flag ? "<td>".hero_portrait($combo[$id.'2'])."</td>" : "").
                "<td>".($heroes_flag ? hero_name($combo[$id.'2']) : player_name($combo[$id.'2']))."</td>".
                (
                  $trios ?
                  ($heroes_flag ? "<td>".hero_portrait($combo[$id.'3'])."</td>" : "").
                  "<td>".($heroes_flag ? hero_name($combo[$id.'3']) : player_name($combo[$id.'2']))."</td>" :
                  ""
                  ).
                "<td>".$combo['matches']."</td>".
                "<td>".number_format($combo['winrate']*100,2)."%</td>".
                ($expectation ? "<td>".number_format($combo['expectation'], 3)."</td>".
                                "<td>".number_format($combo['matches']-$combo['expectation'], 3)."</td>".
                                "<td>".number_format(($combo['matches']-$combo['expectation'])*100/$combo['matches'], 2)."%</td>" : "").
                ($lane_rate ? "<td>".number_format($combo['lane_rate']*100, 2)."%</td>" : "").
                ($lane ? "<td>".locale_string("lane_".$combo['lane'])."</td>" : "").
            "</tr>";
    if($i < 0) break;
  }
  $res .= "</table>";

  return $res;
}

function rg_generator_pickban_overview($table_id, $context, $context_total_matches, $limiter = 10, $heroes_flag = true) {
  $res =  "<table id=\"$table_id\" class=\"list\"><thead><tr>".
            ($heroes_flag ? "<th width=\"1%\"></th>" : "").
            "<th>".locale_string($heroes_flag ? "hero" : "player")."</th>".
            "<th>".locale_string("matches_total")."</th>".
            "<th class=\"separator\">".locale_string("contest_rate")."</th>".
            "<th>".locale_string("rank")."</th>".
            "<th class=\"separator\">".locale_string("matches_picked")."</th>".
            "<th>".locale_string("winrate")."</th>".
            "<th class=\"separator\">".locale_string("matches_banned")."</th>".
            "<th>".locale_string("winrate")."</th></tr></thead>";

  $ranks = [];
  $context_copy = $context;

  $compound_ranking_sort = function($a, $b) use ($context_total_matches) {
    return compound_ranking_sort($a, $b, $context_total_matches);
  };

  uasort($context, $compound_ranking_sort);

  $increment = 100 / sizeof($context); $i = 0;

  foreach ($context as $id => $el) {
    $ranks[$id] = 100 - $increment*$i++;
  }
  unset($context_copy);

  $i = 0;

  foreach($context as $id => $el) {
    $i++;
    $res .=  "<tr>".
            ($heroes_flag ? "<td>".hero_portrait($id)."</td><td>".hero_name($id)."</td>" : "<td>".player_name($id)."</td>").
            "<td>".$el['matches_total']."</td>".
            "<td class=\"separator\">".number_format($el['matches_total']/$context_total_matches*100,2)."%</td>".
            "<td>".number_format($ranks[$id],2)."</td>".
            "<td class=\"separator\">".$el['matches_picked']."</td>".
            "<td>".number_format($el['winrate_picked']*100,2)."%</td>".
            "<td class=\"separator\">".$el['matches_banned']."</td>".
            "<td>".number_format($el['winrate_banned']*100,2)."%</td></tr>";
    if ($i == $limiter) break;
  }
  unset($oi);
  $res .= "</table>";

  return $res;
}

?>
